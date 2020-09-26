<?php

namespace Armincms\DatabaseLocalization\Tests\Translations;

use Armincms\DatabaseLocalization\Tests\Aggregate;
 

class NamespacedTest extends Aggregate
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
		$this->assertEquals(trans('namespaced::group.Test'), 'This is namespaced test');
	} 

	public function test_database_manipulation()
	{  
		app('translator')->getLoader()->repository()->put(
			'Test', 'This is manipulated namespaced', app()->getLocale(), 'group', 'namespaced'
		); 

		$this->assertEquals(trans('namespaced::group.Test'), 'This is manipulated namespaced');
	} 
}