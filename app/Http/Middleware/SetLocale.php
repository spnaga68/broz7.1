<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use App;
use Config;

class SetLocale
{
    /**
     *
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    { 
        if (Session::has('locale')) {
            $locale = Session::get('locale', Config::get('app.locale'));
            //$locale= getCurrentLangCode();
        } else {
			echo Session::has('locale'); 
			$locale = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);
			 //$locale=getCurrentLangCode();
             if ($locale != 'ar' && $locale != 'en') {
                $locale = 'en';
             }
        }

        if ($locale == 'ar') {
             $localeid = 2;
        }
        if ($locale == 'en') {
             $localeid = 1;
        }
        ///Session::put('locale', $locale);
        App::setLocale($locale);
        return $next($request);
    }
}    
