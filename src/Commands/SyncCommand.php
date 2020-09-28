<?php

namespace Armincms\DatabaseLocalization\Commands;

use File;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Contracts\Translation\Translator;

class SyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbl:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert the missing translation strings into the database.';

    /**
     * The translator instance.
     * 
     * @var \Illuminate\Contracts\Translation\Translator
     */
    protected $translator;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->query()->orderBy('id')->chunk(500, function($results) {
            $results->groupBy('namespace')->each(function($namespaced, $namespace) {
                $namespaced->groupBy('group')->each(function($grouped, $group) use ($namespace) {
                    $grouped
                        ->flatMap(function($translation) {
                            return json_decode($translation->text, true);
                        })
                        ->keys()
                        ->unique()
                        ->each(function($locale) use ($grouped, $group, $namespace) {
                            $translations = $grouped->keyBy('key')->map(function($translation) use ($locale) {
                                return data_get(json_decode($translation->text, true), $locale);
                            })
                            ->filter();

                            if($translations->isEmpty()) return;  

                            if($group === '*') {
                                $this->putAsJson(
                                    $this->getTranslationPath($namespace, $group, $locale, true), $translations
                                );
                            } else {  
                                $this->putAsArray(
                                    $this->getTranslationPath($namespace, Str::before($group, '.'), $locale), 
                                    $translations->mapWithKeys(function($value, $key) use ($group) {
                                        $prefix = Str::after($group, '.');

                                        $key = $prefix !== $group ? "{$prefix}.{$key}" : $key;

                                        return [$key => $value];
                                    }) 
                                );
                            }  
                        });  
                }); 
            });

            // $this->query()->whereIn('id', $results->pluck('id')->all())->delete(); 
        });
    }

    public function getTranslationPath($namespace, $group, $locale, $json = false)
    {
        $basePath = resource_path('lang');

        if($namespace != '*') {
            $basePath .= "/vendor/{$namespace}";
        } 

        return tap($basePath . ($json ? "/{$locale}.json" : "/{$locale}/{$group}.php"), function($path) {
            is_dir(dirname($path)) || File::makeDirectory(dirname($path), 0755, true);
        }); 
    }

    public function putAsJson($path, $translations)
    {
        $old = File::exists($path) ? (array) json_decode(File::get($path), true) : []; 

        File::put($path, json_encode(array_merge(
            $old, collect($translations)->all()
        ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function putAsArray($path, $translations)
    { 
        $old = File::exists($path) ? (array) require $path : []; 


        collect($translations)->each(function($value, $key) use (&$old) {
            data_set($old, $key, $value);
        }); 

        ob_start();
        var_export($old);

        File::put($path, '<?php return '. ob_get_clean(). ';');
    }

    /**
     * Get the query builder.
     * 
     * @return 
     */
    public function query()
    {
        return $this->translator->getLoader()->repository()->table();
    }
}
