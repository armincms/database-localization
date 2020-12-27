<?php 

namespace Armincms\DatabaseLocalization;

use Illuminate\Database\ConnectionResolverInterface; 
use Illuminate\Support\Arr;


class DatabaseStore implements Store
{ 	
    /**
     * The connection resolver instance.
     *
     * @var \Illuminate\Database\ConnectionResolverInterface
     */
    protected $connection;  

    /**
     * Array of loaded strings.
     *
     * @var array
     */
    protected $loaded = []; 

    /**
     * Create a new database store.
     *
     * @param  \Illuminate\Database\ConnectionResolverInterface  $connection
     * @param  string  $table
     * @param  string  $prefix
     * @return void
     */
    public function __construct(ConnectionResolverInterface $connection)
    {
        $this->connection = $connection;   
    }

    /**
     * Insert the given strings into the storage.
     *          
     * @param  array $strings 
     * @param  string $locale 
     * @param  string $group       
     * @param  string $namespace 
     * @return array            
     */
    public function put(array $strings, string $locale, string $group = '*', string $namespace = '*')
    {
        collect($strings)->each(function($value, $key) use ($locale, $group, $namespace) { 
            is_array($value) 
                ? $this->put($value, $locale, "{$group}.{$key}", $namespace)
                : $this->updateOrCreate($key, $value, $locale, $group, $namespace);
        });

        return $this;
    } 

    /**
     * Create or update a record matching the attributes, and fill it with values.
     *
     * @param  string $key       
     * @param  string $locale    
     * @param  string $value    
     * @param  string $group     
     * @param  string $namespace 
     * @return $this
     */
    public function updateOrCreate(string $key, string $value, string $locale, string $group, string $namespace)
    {
        $string = $this->getString($key, $locale, $group, $namespace);

        if(is_null($string)) { 
            return $this->create($key, $value, $locale, $group, $namespace);
        } elseif ($string !== $value) {
            return $this->update($key, $value, $locale, $group, $namespace);
        }

        return $this;
    }

    public function getString(string $key, string $locale, string $group, string $namespace)
    { 
        return Arr::get($this->get($locale, $group, $namespace), "{$group}.{$key}");
    } 

    /**
     * Update the tranlsations text for the given key.
     *        
     * @param  string $key    
     * @param  string $value 
     * @param  string $locale 
     * @param  string $group       
     * @param  string $namespace   
     * @return void            
     */
    public function update(string $key, string $value, string $locale, string $group = '*', string $namespace = '*')
    {   
        $this->table()->where(compact('namespace', 'group', 'key', 'locale'))->update(compact('value'));

        return $this->forget($locale, $group, $namespace);
    }  

    /**
     * Create the tranlsation text for the given key.
     *        
     * @param  string $key    
     * @param  string $value 
     * @param  string $locale 
     * @param  string $group       
     * @param  string $namespace   
     * @return void            
     */
    public function create(string $key, string $value, string $locale, string $group = '*', string $namespace = '*')
    {   
        $this->table()->insert(compact('key', 'value', 'locale', 'group', 'namespace'));

        return $this->forget($locale, $group, $namespace);
    }


    /**
     * Get the strings from the storage.
     *           
     * @param  string $locale 
     * @param  string $group       
     * @param  string $namespace 
     * @return array            
     */
    public function get(string $locale, string $group = '*', string $namespace = '*'): array
    {
        $this->loadStringsIfNotLoaded($locale, $group, $namespace);

        return (array) data_get($this->loaded, $this->getCacheKey($locale, $group, $namespace), []); 
    }

    /**
     * Get the strings from the storage.
     *           
     * @param  string $locale 
     * @param  string $group       
     * @param  string $namespace 
     * @return array            
     */
    public function loadStringsIfNotLoaded(string $locale, string $group = '*', string $namespace = '*')
    {  
        if(empty($this->loaded)) {
            $start = microtime(true);
            $this->loaded = $this->table()->get()->groupBy(function($result) {
                return $this->getCacheKey($result->locale, $result->group, $result->namespace);
            })->map(function($strings) {
                return $this->handleManyResults($strings->all());
            })->all(); 
        }
        
        return $this;
    }  

    /**
     * Get the strings from the storage.
     *           
     * @param  string $locale 
     * @param  string $group       
     * @param  string $namespace 
     * @return array            
     */
    public function getLoaded(string $locale, string $group = '*', string $namespace = '*')
    {
        return (array) data_get($this->loaded, $this->getCacheKey($locale, $group, $namespace));
    }

    /**
     * Get the strings from the storage.
     *           
     * @param  string $locale 
     * @param  string $group       
     * @param  string $namespace 
     * @return bool            
     */
    public function stringsLoaded(string $locale, string $group = '*', string $namespace = '*')
    {
        return array_key_exists(
            $this->getCacheKey($locale, $group, $namespace), (array) $this->loaded
        );
    }

    /**
     * Filter the avialable data with given locale.
     * 
     * @param  array  $results  
     * @return [type]          
     */
    public function handleManyResults(array $results): array
    { 
        return collect($results)
                ->reduce(function($carry, $result) {   
                    if($result->group !== '*') {
                        return (array) data_set($carry, "{$result->group}.{$result->key}", $result->value);  
                    } 

                    $carry[$result->group][$result->key] = $result->value;

                    return $carry; 
                }, []);
    }

    /**
     * Determine if the strings exists in the storage.
     *           
     * @param  string $locale 
     * @param  string $group       
     * @param  string $namespace 
     * @return bool            
     */
    public function has(string $locale, string $group = '*', string $namespace = '*'): bool
    { 
        $this->loadStringsIfNotLoaded($locale, $group, $namespace);  

        return $this->stringsLoaded($locale, $group, $namespace); 
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
}