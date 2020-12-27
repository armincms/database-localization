<?php

namespace Armincms\DatabaseLocalization;

use Illuminate\Translation\FileLoader; 
use Illuminate\Filesystem\Filesystem;

class DatabaseLoader extends FileLoader
{
    /**
     * The database connectino instance.
     * 
     * @var \Armincms\DatabaseLocalization\Store
     */
    protected $repository;

    /**
     * Create a new file loader instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string  $path
     * @return void
     */
    public function __construct(Store $repository, Filesystem $files, $path)
    { 
        $this->repository = $repository;

        parent::__construct($files, $path);
    }

    /**
     * Load a locale from the given JSON file path.
     *
     * @param  string  $locale
     * @return array
     *
     * @throws \RuntimeException
     */
    protected function loadJsonPaths($locale)
    {  
    	if(! $this->repository->has($locale)) {
    		$this->repository->put((array) parent::loadJsonPaths($locale), $locale);
    	}

    	return $this->repository->get($locale); 
    }  

    /**
     * Load a locale from a given path.
     *
     * @param  string  $path
     * @param  string  $locale
     * @param  string  $group
     * @return array
     */
    protected function loadPath($path, $locale, $group)
    {
        if(! $this->repository->has($locale, $group)) { 
            $lines = (array) parent::loadPath($path, $locale, $group);

            $this->repository->put($lines, $locale, $group);
        } 

        return $this->repository->get($locale, $group); 
    }

    /**
     * Load a namespaced translation group.
     *
     * @param  string  $locale
     * @param  string  $group
     * @param  string  $namespace
     * @return array
     */
    protected function loadNamespaced($locale, $group, $namespace)
    {
        if(! $this->repository->has($locale, $group, $namespace) && isset($this->hints[$namespace])) { 
            $lines = parent::loadPath($this->hints[$namespace], $locale, $group);

            $namespaced = $this->loadNamespaceOverrides($lines, $locale, $group, $namespace);

            $this->repository->put($namespaced, $locale, $group, $namespace);
        }  

        return $this->repository->get($locale, $group, $namespace);
    } 

    /**
     * Get the repository instance.
     * 
     * @return 
     */
    public function repository()
    {
        return $this->repository;
    } 
}
