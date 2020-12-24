<?php 

namespace Armincms\DatabaseLocalization;


interface Cacheable
{ 	  
    /**
     * Clears cache strings for the given locale, group and namesapce.
	 *          
	 * @param  string $locale 
	 * @param  string $group       
	 * @param  string $namespace 
	 * @return array            
	 */
	public function forget(string $locale, string $group = '*', string $namespace = '*');
}