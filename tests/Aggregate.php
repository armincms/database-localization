<?php

namespace Armincms\DatabaseLocalization\Tests;

use Orchestra\Testbench\TestCase;

class Aggregate extends TestCase
{ 
	/**
	 * Setup the test environment.
	 */
	protected function setUp(): void
	{
	    parent::setUp();
 
	    $this->loadMigrationsFrom([
		    '--database' => 'testbench',
		    '--realpath' => realpath(__DIR__.'/../../database/migrations'),
		]); 
	}

	protected function getPackageProviders($app)
	{
	    return [
	    	\Armincms\DatabaseLocalization\ServiceProvider::class,
	    ];
	}

	/**
	 * Define environment setup.
	 *
	 * @param  \Illuminate\Foundation\Application  $app
	 * @return void
	 */
	protected function getEnvironmentSetUp($app)
	{ 
	    // Setup default database to use sqlite :memory:
	    $app['config']->set('app.locale', 'en');
	    $app['config']->set('database.default', 'testbench');
	    $app['config']->set('database.connections.testbench', [
	        'driver'   => 'sqlite',
	        'database' => ':memory:',
	        'prefix'   => '',
	    ]); 

	    $app->bind('path.lang', function() {
	    	return __DIR__.'/resources/lang';
	    });

	    $app->resolving('translator', function($translator) { 
		    $translator->addJsonPath($path = __DIR__.'/resources/lang');
	        $translator->addNamespace('namespaced', $path);  
	        $translator->addNamespace('*', $path);  
	    }); 
	}
}