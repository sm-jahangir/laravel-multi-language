<?php

namespace App\Providers;

use File;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class TranslatServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('translate', function ($key) {
            $key = ucfirst(str_replace('_', ' ', $key));
            if(File::exists(base_path('resources/lang/en.json'))){
                $jsonString = file_get_contents(base_path('resources/lang/en.json'));
                $jsonString = json_decode($jsonString, true);
                if(!isset($jsonString[$key])){
                    $jsonString[$key] = $key;
                    saveJSONFile('en', $jsonString);
                }
            }
            return __($key);
        });
    }
}
