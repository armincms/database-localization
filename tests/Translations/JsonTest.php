<?php

namespace Armincms\DatabaseLocalization\Tests\Translations;

use Armincms\DatabaseLocalization\Tests\Aggregate;
 

class JsonTest extends Aggregate
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
		$this->assertEquals(trans('Test'), 'This is test translation');
	} 

	public function test_database_manipulation()
	{  
		app('translator')->getLoader()->repository()->put(
			'Test', 'This is manipulated translation', app()->getLocale()
		);

		$this->assertEquals(trans('Test'), 'This is manipulated translation');
	} 
}