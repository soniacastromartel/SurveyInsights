<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
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
        Carbon::setLocale(config('app.locale'));
        setlocale(LC_ALL,"es_ES");
        
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

        view()->composer('*',function($view){
            $nDays = '';
            $currentDay = date('d');
            $currentMonth = date('m');
            $textMonths = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
            $month = $textMonths[$currentMonth-1]; 

            if($month == "Marzo" || $month == "Diciembre"){
                if($currentDay>=24){
                    $nDays= env ('PARAM_ENDDAY1')- $currentDay;
                }
            }else if($month == "Junio" || $month == "Septiembre"){
                if($currentDay>=23){
                    $nDays= env ('PARAM_ENDDAY2')- $currentDay;
                }
            }

            $view->with('nDays', $nDays >= 0 ? $nDays : '' );
            $view->with('currentMonth', $currentMonth );
        });

    }
}
