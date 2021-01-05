<?php 

namespace Armincms\DatabaseLocalization;

use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Contracts\Cache\Factory; 


class DatabaseStore implements Store, Cacheable
{ 	
    /**
     * The connection resolver instance.
     *
     * @var \Illuminate\Database\ConnectionResolverInterface
     */
    protected $connection;

    /**
     * The cache factory instance.
     *
     * @var \Illuminate\Database\ConnectionResolverInterface
     */
    protected $cache; 

    /**
     * Create a new database store.
     *
     * @param  \Illuminate\Database\ConnectionResolverInterface  $connection
     * @param  string  $table
     * @param  string  $prefix
     * @return void
     */
    public function __construct(ConnectionResolverInterface $connection, Factory $cache)
    {
        $this->connection = $connection;  
        $this->cache = $cache;  
    } 

    /**
     * Get the translations text for the given key.
     *          
     * @param  string $locale 
     * @param  string $group       
     * @param  string $namespace    
     * @return array            
     */
    public function translations(string $locale, string $group = '*', string $namespace = '*') : array
    {  
        $callback = function() use ($locale, $group, $namespace) {
            return $this->getStringsFromStorage($locale, $namespace, $group);
        };

        return $this->cache->sear($this->getCacheKey($locale, $group, $namespace), $callback); 
    }

    /**
     * Get translation strings from the database for the givan locale, group and namespace.
     *          
     * @param  string $locale 
     * @param  string $group       
     * @param  string $namespace    
     * @return array            
     */
    public function getStringsFromStorage(string $locale, string $group = '*', string $namespace = '*') : array
    {  
        $results = $this->table()
            ->where('namespace', $namespace) 
            ->when($group == '*', function($query) {
                $query->whereGroup('*');
            })
            ->when($group != '*', function($query) use ($group) {   
                $query
                    ->where('group', $group) 
                    ->orWhere('group', 'regexp', preg_quote($group)."\.(.*)"); 
            })
            ->get(); 

        return $this->handleManyResults($results->all(), $locale);
    }

    /**
     * Filter the avialable data with given locale.
     * 
     * @param  array  $results 
     * @param  string $locale  
     * @return [type]          
     */
    public function handleManyResults(array $results, string $locale): array
    { 
        return collect($results)
                ->reduce(function($carry, $result) use ($locale) {  
                    $value = data_get(json_decode($result->text, true), $locale);
                    $parts = explode('.', $result->group.'.'.$result->key);

                    array_shift($parts);

                    return (array) data_set($carry, implode('.', $parts), $value);  
                }, []);
    }

    /**
     * Check the translations text existence.
     *          
     * @param  string $locale 
     * @param  string $group       
     * @param  string $namespace    
     * @return bool            
     */
    public function has(string $locale, string $group = '*', string $namespace = '*') : bool
    {
        return ! empty($translations = $this->translations($locale, $group, $namespace));
    } 

    /**
     * Insert the tranlsations text for the given keys.
     *        
     * @param  string $values    
     * @param  string $locale 
     * @param  string $group       
     * @param  string $namespace   
     * @return void            
     */
    public function putMany(array $values, string $locale, string $group = '*', string $namespace = '*')
    {
        collect($values)->each(function($text, $key) use ($locale, $group, $namespace) {
            if(is_array($text)) {
                // Handles nested translations
                $this->putMany($text, $locale, "{$group}.{$key}", $namespace);
            } else {
                $this->put($key, $text, $locale, $group, $namespace);
            }
        });

        return $this;
    }

    /**
     * Create the tranlsation text for the given key.
     *        
     * @param  string $key    
     * @param  string $text 
     * @param  string $locale 
     * @param  string $group       
     * @param  string $namespace   
     * @return void            
     */
    public function put(string $key, string $text, string $locale, string $group = '*', string $namespace = '*')
    {
        if($this->table()->where(compact('key', 'group', 'namespace'))->exists()) {
            return $this->update($key, $text, $locale, $group, $namespace);
        } 

        $this->table()->insert([
            'namespace' => $namespace,
            'group' => $group,
            'key'   => $key, 
            'text'  => json_encode([$locale => $text]), 
        ]);

        return $this->forget($locale, $group, $namespace);
    }

    /**
     * Update the tranlsations text for the given key.
     *        
     * @param  string $key    
     * @param  string $text 
     * @param  string $locale 
     * @param  string $group       
     * @param  string $namespace   
     * @return void            
     */
    public function update(string $key, string $text, string $locale, string $group = '*', string $namespace = '*')
    {  
        $this->table()->where(compact('namespace', 'group', 'key'))->update([
            "text->{$locale}"  => $text, 
        ]);

        return $this->forget($locale, $group, $namespace);
    }  

    /**
     * Set the translation text for the given key.
     *      
     * @param  string $key     
     * @param  string $locale 
     * @param  string $group       
     * @param  string $namespace    
     * @return string            
     */
    public function get(string $key, string $locale, string $group = '*', string $namespace = '*') : string
    {
        return data_get($this->tranlsations(), $locale);
    }  
    
    /**
     * Get a query builder for the localization table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
	public function table()
	{
		return $this->connection->table(config('database-localization.database', 'database_localization'));
	} 

    /**
     * Returns cacheKey for the given paramaters.
     *            
     * @return bool            
     */
    protected function getCacheKey()
    {
        return md5(get_called_class().implode('', func_get_args()));
    }

    /**
     * Clears cache strings for the given locale, group and namesapce.
     *          
     * @param  string $locale 
     * @param  string $group       
     * @param  string $namespace 
     * @return array            
     */
    public function forget(string $locale, string $group = '*', string $namespace = '*')
    {
        $this->cache->forget($this->getCacheKey($locale, $group, $namespace));

        return $this;
    }
}