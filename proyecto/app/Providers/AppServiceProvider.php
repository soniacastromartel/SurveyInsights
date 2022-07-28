<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if(env('APP_DEBUG')) {
            DB::listen(function($query) {
                $bindings = array_map(function($value) {
                    if (is_a($value, 'DateTime')) {
                        return $value->format('Y-m-d H:i:s');
                    }
                    else {
                        return $value;
                    }
                }, $query->bindings);

                File::append(
                    storage_path('/logs/query.log'),
                    $query->sql . ' [' . implode(', ',  $bindings) . ']' . PHP_EOL
               );
            });
        }

    }
}
