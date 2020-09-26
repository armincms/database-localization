<?php 

namespace Armincms\DatabaseLocalization;


interface Store
{ 	
	/**
	 * Get the tranlsation text for the given key.
	 *        
	 * @param  string $key    
	 * @param  string $text 
	 * @param  string $locale 
	 * @param  string $group      
	 * @param  string $namespace 
	 * @return void            
	 */
	public function put(string $key, string $text, string $locale, string $group = '*', string $namespace = '*');

	/**
	 * Set the translation text for the given key.
	 *      
	 * @param  string $key     
	 * @param  string $locale 
	 * @param  string $group       
	 * @param  string $namespace 
	 * @return string            
	 */
	public function get(string $key, string $locale, string $group = '*', string $namespace = '*') : string;

	/**
	 * Get the translations text for the given key.
	 *          
	 * @param  string $locale 
	 * @param  string $group       
	 * @param  string $namespace 
	 * @return array            
	 */
	public function translations(string $locale, string $group = '*', string $namespace = '*') : array;
}