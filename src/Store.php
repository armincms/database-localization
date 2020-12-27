<?php 

namespace Armincms\DatabaseLocalization;


interface Store
{ 	  
	/**
	 * Insert the given strings into the storage.
	 *          
	 * @param  array $strings 
	 * @param  string $locale 
	 * @param  string $group       
	 * @param  string $namespace 
	 * @return array            
	 */
	public function put(array $strings, string $locale, string $group = '*', string $namespace = '*');

	/**
	 * Get the strings from the storage.
	 *           
	 * @param  string $locale 
	 * @param  string $group       
	 * @param  string $namespace 
	 * @return array            
	 */
	public function get(string $locale, string $group = '*', string $namespace = '*'): array;

	/**
	 * Determine if the strings exists in the storage.
	 *           
	 * @param  string $locale 
	 * @param  string $group       
	 * @param  string $namespace 
	 * @return bool            
	 */
	public function has(string $locale, string $group = '*', string $namespace = '*'): bool; 
}