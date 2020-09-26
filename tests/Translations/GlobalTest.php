<?php

namespace Armincms\DatabaseLocalization\Tests\Translations;

use Armincms\DatabaseLocalization\Tests\Aggregate;
 

class GlobalTest extends Aggregate
{
	/**
	 * Setup the test environment.
	 */
	protected function setUp(): void
	{
	    parent::setUp(); 
	}

	public function test_original()
	{  
		$this->assertEquals(trans('group.Test'), 'This is namespaced test');
	} 

	public function test_database_manipulation()
	{  
		app('translator')->getLoader()->repository()->put(
			'Test', 'This is manipulated namespaced', app()->getLocale(), 'group'
		); 

		$this->assertEquals(trans('group.Test'), 'This is manipulated namespaced');
	} 
}