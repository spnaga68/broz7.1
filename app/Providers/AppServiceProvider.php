<?php

namespace App\Providers;
use DB;
use App\Model\settings;
use App\Model\settings_infos;
use App\Model\api;
use App\Model\socialmediasettings;
use App\Model\emailsettings;
use App\Model\languages;
use App\Model\stores;
use Illuminate\Support\ServiceProvider;
use View, Mail, Config, App;
use Session;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
		/** get the settings value from db and bind to global access **/
		$api = new Api;
		        \Debugbar::disable();

		view()->share('api', $api);
		$general = Settings::find(1);
		Session::put('general',$general);
		$social = Socialmediasettings::find(1);
		Session::put('social',$social);
		$email = Emailsettings::find(1);
		Session::put('configemail',$email);
		$languages = DB::table('languages')->where('status', 1)->orderby("languages.id","asc")->get();
		Session::put('languages',$languages);
		view()->share('languages', $languages);
		$currentlanguages = DB::table('languages')->where('status', 1)->where('language_code',App::getLocale())->get();
		//~ print_r(App::getLocale());die;
		$current_language_id='';
		if(count($currentlanguages)>0)
		{
			$current_language_id = $currentlanguages[0]->id;
		}
		$general_site = getSettingsLists();
		//print_r($general_site);exit();
        Session::put('general_site',$general_site);
		view()->share('currentlanguage', $current_language_id);
		//~ echo '<pre>';print_r($current_language_id);exit;
		/** settings end **/
		
		/** smtp email  settings get from db and replace to env file **/
		/**
		$email = Emailsettings::find(1);
		$path = base_path('.env');
		if (file_exists($path)) {
			file_put_contents($path, str_replace(
				'MAIL_DRIVER='.Config::get('mail.driver'), 'MAIL_DRIVER='.$email->mail_driver, file_get_contents($path)
			));
			file_put_contents($path, str_replace(
				'MAIL_HOST='.Config::get('mail.host'), 'MAIL_HOST='.$email->smtp_host_name, file_get_contents($path)
			));
			file_put_contents($path, str_replace(
				'MAIL_PORT='.Config::get('mail.port'), 'MAIL_PORT='.$email->smtp_port, file_get_contents($path)
			));
			file_put_contents($path, str_replace(
				'MAIL_USERNAME='.Config::get('mail.username'), 'MAIL_USERNAME='.$email->smtp_username, file_get_contents($path)
			));
			file_put_contents($path, str_replace(
				'MAIL_PASSWORD='.Config::get('mail.password'), 'MAIL_PASSWORD='.$email->smtp_password, file_get_contents($path)
			));
			file_put_contents($path, str_replace(
				'MAIL_ENCRYPTION='.Config::get('mail.encryption'), 'MAIL_ENCRYPTION='.$email->smtp_encryption, file_get_contents($path)
			));

		} **/
		
		/** email settings end **/
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
		
    }
}
