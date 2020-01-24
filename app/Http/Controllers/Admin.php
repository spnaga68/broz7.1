<?php

namespace App\Http\Controllers;

use App;
use App\Http\Controllers\Controller;
use App\Http\Controllers\view;
use App\Model\emailsettings;
use App\Model\imageresizesettings;
use App\Model\settings;
use App\Model\settings_infos;
use App\Model\socialmediasettings;
use App\Model\stores;
use App\Model\users;
use DB;
use File;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Text;
use Illuminate\Support\Facades\Validator;
use Image;
use Mail;
use OpenGraph;
use SEOMeta;
use Session;
use Twitter;
use App\Model\driver_cores;
use App\Model\customer_cores;
use App\Model\core_referrals;
use Yajra\Datatables\Datatables;
use URL;
use App\Model\terms_of_serivce;
use App\Model\customer_promotion;
use App\Model\telr_payment;

class Admin extends Controller {
	const COMMON_MAIL_TEMPLATE = 8;
	const ADMIN_CHANGE_PASSWORD_EMAIL_TEMPLATE = 21;
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->site_name = isset(getAppConfig()->site_name) ? ucfirst(getAppConfig()->site_name) : '';
		$this->middleware('auth');
		SEOMeta::setTitle($this->site_name);
		SEOMeta::setDescription($this->site_name);
		SEOMeta::addKeyword($this->site_name);
		OpenGraph::setTitle($this->site_name);
		OpenGraph::setDescription($this->site_name);
		OpenGraph::setUrl($this->site_name);
		Twitter::setTitle($this->site_name);
		Twitter::setSite('@' . $this->site_name);
		App::setLocale('en');
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		$id = Auth::id();
		$users = Users::find($id);
		$this->loginactivity($users);
		Session::flash('message', 'Logged in successfully');
		return Redirect::to('admin/dashboard');
	}

	public function passwordReset() {
		// $user_details = $this->check_login();
		// SEOMeta::setTitle($this->site_name);
		// SEOMeta::setDescription($this->site_name);
		// SEOMeta::addKeyword($this->site_name);
		// OpenGraph::setTitle($this->site_name);
		// OpenGraph::setDescription($this->site_name);
		// OpenGraph::setUrl(URL::to('/hai'));
		// Twitter::setTitle($this->site_name);
		// Twitter::setSite('@' . $this->site_name);
		return view('auth.passwords.reset');

	}

	public function edit_profile($id) {
		/**
		 * Get session user details.
		 */
		/*echo  Auth::id().Auth::check().Auth::user().Auth::user()->name;*/
		// Section description
		//$id=Auth::id();
		$users = Users::find($id);
		return view('admin.edit_profile')->with('data', $users);
	}

	/**
	 * Update the specified blog in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update_profile(Request $data, $id) {
		// validate
		// read more on validation at http://laravel.com/docs/validation
		$validation = Validator::make($data->all(), array(
			//'title' => 'required',
			'name' => 'required|alpha_num',
			'designation' => 'required|alpha_num',
			'image' => 'mimes:png,jpeg,bmp|max:2024',
			'mobile' => 'max:12|regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
			//mimes:jpeg,bmp,png and for max size max:10000
		));
		// process the validation
		if ($validation->fails()) {
			return Redirect::back()->withErrors($validation);
		} else {

			// store datas in to database
			$users = Users::find($id);
			$usertoken = sha1(uniqid(Text::random('alnum', 32), TRUE));
			if (!$users->user_token) {
				$users->user_token = $usertoken;
			}
			$users->name = $_POST['name'];
			$users->designation = $_POST['designation'];
			$users->mobile = $_POST['mobile'];
			$users->date_of_birth = $_POST['date_of_birth'];
			//$users->social_title      = $_POST['social_title'];
			$users->gender = $_POST['gender'];
			$users->updated_date = date("Y-m-d H:i:s");
			$users->save();
			if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
				$destinationPath = base_path() . '/public/assets/admin/base/images/admin/profile/'; // upload path
				$imageName = $users->id . '.' .
				$data->file('image')->getClientOriginalExtension();
				$data->file('image')->move($destinationPath, $imageName);
				$destinationPath1 = url('/assets/admin/base/images/admin/profile/' . $imageName . '');
				Image::make($destinationPath1)->fit(75, 75)->save(base_path() . '/public/assets/admin/base/images/admin/profile/thumb/' . $imageName)->destroy();
				$users->image = $imageName;
				$users->save();
			}

			// redirect
			Session::flash('message', trans('messages.Profile has been successfully updated'));
			return Redirect::to('admin/editprofile/' . $id);
		}
	}

	public function dashboard() {
		//~ echo '<pre>';print_r(Session::all());echo '</pre>';die;
		/**
		 * Get session user details.
		 */
		/*echo  Auth::id().Auth::check().Auth::user().Auth::user()->name;*/
		// Section description
		$language = getAdminCurrentLang();
		$transaction_query = "SELECT
                    (
                    SELECT COUNT(1)
                    FROM transaction
                    WHERE date_trunc('day', created_date) = date_trunc('day', CURRENT_DATE)) AS day_count,

                    (SELECT COUNT(1)
                    FROM transaction
                    WHERE date_trunc('WEEK', created_date) = date_trunc('WEEK', CURRENT_DATE)) AS week_count,

                    (SELECT COUNT(1)
                    FROM transaction
                    WHERE date_trunc('month', created_date) = date_trunc('month', CURRENT_DATE)) AS month_count,

                    (SELECT COUNT(1)
                    FROM transaction
                    WHERE date_trunc('year', created_date) = date_trunc('year', CURRENT_DATE)) AS year_count,
                    COUNT(1) AS total_count
                    FROM transaction";
		$transaction_period_count = DB::select($transaction_query);

		$users_query = "SELECT
        (
        SELECT COUNT(1)
        FROM users
        WHERE date_trunc('day', created_date) = date_trunc('day', CURRENT_DATE)) AS day_count,

        (SELECT COUNT(1)
        FROM users
        WHERE date_trunc('WEEK', created_date) = date_trunc('WEEK', CURRENT_DATE)) AS week_count,

        (SELECT COUNT(1)
        FROM users
        WHERE date_trunc('month', created_date) = date_trunc('month', CURRENT_DATE)) AS month_count,

        (SELECT COUNT(1)
        FROM users
        WHERE date_trunc('year', created_date) = date_trunc('year', CURRENT_DATE)) AS year_count,
        COUNT(1) AS total_count
        FROM users";
		$users_period_count = DB::select($users_query);

		$outlets_query = "SELECT
        (
        SELECT COUNT(1)
        FROM outlets
        WHERE date_trunc('day', created_date) = date_trunc('day', CURRENT_DATE)) AS day_count,

        (SELECT COUNT(1)
        FROM outlets
        WHERE date_trunc('WEEK', created_date) = date_trunc('WEEK', CURRENT_DATE)) AS week_count,

        (SELECT COUNT(1)
        FROM outlets
        WHERE date_trunc('month', created_date) = date_trunc('month', CURRENT_DATE)) AS month_count,

        (SELECT COUNT(1)
        FROM outlets
        WHERE date_trunc('year', created_date) = date_trunc('year', CURRENT_DATE)) AS year_count,
        COUNT(1) AS total_count
        FROM outlets";
		$outlets_period_count = DB::select($outlets_query);

		$drivers_query = "SELECT
        (
        SELECT COUNT(1)
        FROM drivers
        WHERE date_trunc('day', created_date) = date_trunc('day', CURRENT_DATE)) AS day_count,

        (SELECT COUNT(1)
        FROM drivers
        WHERE date_trunc('WEEK', created_date) = date_trunc('WEEK', CURRENT_DATE)) AS week_count,

        (SELECT COUNT(1)
        FROM drivers
        WHERE date_trunc('month', created_date) = date_trunc('month', CURRENT_DATE)) AS month_count,

        (SELECT COUNT(1)
        FROM drivers
        WHERE date_trunc('year', created_date) = date_trunc('year', CURRENT_DATE)) AS year_count,
        COUNT(1) AS total_count
        FROM drivers";
		$drivers_period_count = DB::select($drivers_query);

		$newsletter_subscribers_query = "SELECT
        (
        SELECT COUNT(1)
        FROM newsletter_subscribers
        WHERE date_trunc('day', created_date) = date_trunc('day', CURRENT_DATE)) AS day_count,

        (SELECT COUNT(1)
        FROM newsletter_subscribers
        WHERE date_trunc('WEEK', created_date) = date_trunc('WEEK', CURRENT_DATE)) AS week_count,

        (SELECT COUNT(1)
        FROM newsletter_subscribers
        WHERE date_trunc('month', created_date) = date_trunc('month', CURRENT_DATE)) AS month_count,

        (SELECT COUNT(1)
        FROM newsletter_subscribers
        WHERE date_trunc('year', created_date) = date_trunc('year', CURRENT_DATE)) AS year_count,
        COUNT(1) AS total_count
        FROM newsletter_subscribers";
		$newsletter_subscribers_period_count = DB::select($newsletter_subscribers_query);

		$outlet_reviews_query = "SELECT
        (SELECT COUNT(1)
        FROM outlet_reviews
        WHERE date_trunc('day', created_date) = date_trunc('day', CURRENT_DATE)) AS day_count,

        (SELECT COUNT(1)
        FROM outlet_reviews
        WHERE date_trunc('WEEK', created_date) = date_trunc('WEEK', CURRENT_DATE)) AS week_count,

        (SELECT COUNT(1)
        FROM outlet_reviews
        WHERE date_trunc('month', created_date) = date_trunc('month', CURRENT_DATE)) AS month_count,

        (SELECT COUNT(1)
        FROM outlet_reviews
        WHERE date_trunc('year', created_date) = date_trunc('year', CURRENT_DATE)) AS year_count,
        COUNT(1) AS total_count
        FROM outlet_reviews";
		$outlet_reviews_query = DB::select($outlet_reviews_query);

		$blogs_query = "SELECT
        (
        SELECT COUNT(1)
        FROM blogs
        WHERE date_trunc('day', created_at) = date_trunc('day', CURRENT_DATE)) AS day_count,

        (SELECT COUNT(1)
        FROM blogs
        WHERE date_trunc('WEEK', created_at) = date_trunc('WEEK', CURRENT_DATE)) AS week_count,

        (SELECT COUNT(1)
        FROM blogs
        WHERE date_trunc('month', created_at) = date_trunc('month', CURRENT_DATE)) AS month_count,

        (SELECT COUNT(1)
        FROM blogs
        WHERE date_trunc('year', created_at) = date_trunc('year', CURRENT_DATE)) AS year_count,
        COUNT(1) AS total_count
        FROM blogs";
		$blogs_count = DB::select($blogs_query);

		//print_r( $order_period_count);exit;

		$outlets = DB::table('outlets')->select('outlets.id')->get();
		//$outlet_managers = DB::table('outlet_managers')->select('outlet_managers.id')->get();
		$products = DB::table('products')->select('products.id')->join('products_infos', 'products.id', '=', 'products_infos.id')->get();
		$drivers = DB::table('drivers')->select('drivers.id')->get();
		$coupons = DB::table('coupons')->select('coupons.id')->get();
		$newsletter_subscribers = DB::table('newsletter_subscribers')->select('newsletter_subscribers.id')->get();
		$query = '"payment_gateways_info"."language_id" = (case when (select count(payment_gateways_info.payment_id) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language . ' and orders.payment_gateway_id = payment_gateways_info.payment_id) > 0 THEN ' . $language . ' ELSE 1 END)';
		$query1 = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language . ' and orders.outlet_id = outlet_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
		$query2 = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language . ' and orders.vendor_id = vendors_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
		$query = '"payment_gateways_info"."language_id" = (case when (select count(payment_gateways_info.payment_id) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language . ' and orders.payment_gateway_id = payment_gateways_info.payment_id) > 0 THEN ' . $language . ' ELSE 1 END)';
		$query1 = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language . ' and orders.outlet_id = outlet_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
		$query2 = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language . ' and orders.vendor_id = vendors_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
		$orders = DB::table('orders')
			->select('orders.id', 'orders.total_amount', 'orders.created_date', 'orders.modified_date', 'orders.delivery_date', 'users.first_name', 'users.last_name', 'order_status.name as status_name', 'order_status.color_code as color_code', 'users.name as user_name', 'transaction.currency_code', 'payment_gateways_info.name as payment_type', 'outlet_infos.outlet_name', 'vendors_infos.vendor_name as vendor_name', 'orders.id', 'outlet_infos.contact_address', 'outlets.latitude as outlet_latitude', 'outlets.longitude as outlet_longitude', 'outlets.id as outlet_id', 'drivers.first_name as driver_name')
			->leftJoin('users', 'users.id', '=', 'orders.customer_id')
			->leftJoin('order_status', 'order_status.id', '=', 'orders.order_status')
			->leftjoin('transaction', 'transaction.order_id', '=', 'orders.id')
			->Join('payment_gateways_info', 'payment_gateways_info.payment_id', '=', 'orders.payment_gateway_id')
			->Join('vendors_infos', 'vendors_infos.id', '=', 'orders.vendor_id')
			->Join('outlet_infos', 'outlet_infos.id', '=', 'orders.outlet_id')
			->Join('outlets', 'outlets.id', '=', 'outlet_infos.id')
			->leftJoin('driver_orders', 'driver_orders.order_id', '=', 'orders.id')
			->leftJoin('drivers', 'drivers.id', '=', 'driver_orders.driver_id')
			->whereRaw($query)->whereRaw($query1)->whereRaw($query2)

			->orderBy('orders.id', 'desc')
			->get(10);

		$orders_query = "SELECT
        (
        SELECT COUNT(1)
        FROM orders
        LEFT JOIN users on users.id = orders.customer_id
        LEFT JOIN order_status  on order_status.id = orders.order_status
        LEFT JOIN transaction on transaction.order_id = orders.id
        JOIN payment_gateways_info on payment_gateways_info.payment_id = orders.payment_gateway_id
        JOIN vendors_infos on vendors_infos.id = orders.vendor_id
        JOIN outlet_infos on outlet_infos.id  = orders.outlet_id
        JOIN outlets on outlets.id = outlet_infos.id
        LEFT JOIN driver_orders on driver_orders.order_id = orders.id
        LEFT JOIN drivers on drivers.id = driver_orders.driver_id

        WHERE date_trunc('day', orders.created_date) = date_trunc('day', CURRENT_DATE) AND $query AND $query1 AND $query2 ) AS day_count,

        (SELECT COUNT(1)
        FROM orders
         LEFT JOIN users on users.id = orders.customer_id
        LEFT JOIN order_status  on order_status.id = orders.order_status
        LEFT JOIN transaction on transaction.order_id = orders.id
        JOIN payment_gateways_info on payment_gateways_info.payment_id = orders.payment_gateway_id
        JOIN vendors_infos on vendors_infos.id = orders.vendor_id
        JOIN outlet_infos on outlet_infos.id  = orders.outlet_id
        JOIN outlets on outlets.id = outlet_infos.id
        LEFT JOIN driver_orders on driver_orders.order_id = orders.id
        LEFT JOIN drivers on drivers.id = driver_orders.driver_id
        WHERE date_trunc('WEEK', orders.created_date) = date_trunc('WEEK', CURRENT_DATE) AND $query AND $query1 AND $query2 ) AS week_count,

        (SELECT COUNT(1)
        FROM orders
         LEFT JOIN users on users.id = orders.customer_id
        LEFT JOIN order_status  on order_status.id = orders.order_status
        LEFT JOIN transaction on transaction.order_id = orders.id
        JOIN payment_gateways_info on payment_gateways_info.payment_id = orders.payment_gateway_id
        JOIN vendors_infos on vendors_infos.id = orders.vendor_id
        JOIN outlet_infos on outlet_infos.id  = orders.outlet_id
        JOIN outlets on outlets.id = outlet_infos.id
        LEFT JOIN driver_orders on driver_orders.order_id = orders.id
        LEFT JOIN drivers on drivers.id = driver_orders.driver_id
        WHERE date_trunc('month', orders.created_date) = date_trunc('month', CURRENT_DATE)AND $query AND $query1 AND $query2 )  AS month_count,

        (SELECT COUNT(1)
        FROM orders
        LEFT JOIN users on users.id = orders.customer_id
        LEFT JOIN order_status  on order_status.id = orders.order_status
        LEFT JOIN transaction on transaction.order_id = orders.id
        JOIN payment_gateways_info on payment_gateways_info.payment_id = orders.payment_gateway_id
        JOIN vendors_infos on vendors_infos.id = orders.vendor_id
        JOIN outlet_infos on outlet_infos.id  = orders.outlet_id
        JOIN outlets on outlets.id = outlet_infos.id
        LEFT JOIN driver_orders on driver_orders.order_id = orders.id
        LEFT JOIN drivers on drivers.id = driver_orders.driver_id

        WHERE date_trunc('year', orders.created_date) = date_trunc('year', CURRENT_DATE) AND $query AND $query1 AND $query2 )  AS year_count,
        COUNT(1) AS total_count
        FROM orders  LEFT JOIN users on users.id = orders.customer_id
        LEFT JOIN order_status  on order_status.id = orders.order_status
        LEFT JOIN transaction on transaction.order_id = orders.id
        JOIN payment_gateways_info on payment_gateways_info.payment_id = orders.payment_gateway_id
        JOIN vendors_infos on vendors_infos.id = orders.vendor_id
        JOIN outlet_infos on outlet_infos.id  = orders.outlet_id
        JOIN outlets on outlets.id = outlet_infos.id
        LEFT JOIN driver_orders on driver_orders.order_id = orders.id
        LEFT JOIN drivers on drivers.id = driver_orders.driver_id WHERE $query AND $query1 AND $query2";
		$order_period_count = DB::select($orders_query);

		$query = 'SELECT (SELECT COUNT(orders.order_status) FROM orders WHERE orders.order_status = ?) AS oreder_initiated, (SELECT COUNT(orders.order_status) FROM orders WHERE orders.order_status = ?) AS oreder_processed, (SELECT COUNT(orders.order_status) FROM orders WHERE orders.order_status = ?) AS oreder_shipped, (SELECT COUNT(orders.order_status) FROM orders WHERE orders.order_status = ?) AS oreder_packed, (SELECT COUNT(orders.order_status) FROM orders WHERE orders.order_status = ?) AS oreder_dispatched FROM orders limit 1';
		$order_status_count = DB::select($query, array(1, 10, 14, 18, 19));

		$query1 = "SELECT to_char(i, 'YYYY') as year_data, to_char(i, 'MM') as month_data, to_char(i, 'Month') as month_string, sum(total_amount) as total_amount FROM generate_series(now() - INTERVAL '1 year', now(), '1 month') as i left join orders on (to_char(i, 'YYYY') = to_char(created_date, 'YYYY') and to_char(i, 'MM') = to_char(created_date, 'MM')) GROUP BY 1,2,3 order by year_data desc, month_data desc limit 12";
		$year_transaction = DB::select($query1);

		$web_user_query = "SELECT to_char(i, 'YYYY') as year_data, to_char(i, 'MM') as month_data, to_char(i, 'Month') as month_string, count(id) as web_total_count FROM generate_series(now() - INTERVAL '1 year', now(), '1 month') as i left join users on (to_char(i, 'YYYY') = to_char(created_date, 'YYYY') and to_char(i, 'MM') = to_char(created_date, 'MM') and login_type = 1) GROUP BY 1,2,3 order by year_data desc, month_data desc limit 12";
		$web_user_count = DB::select($web_user_query);
		$android_user_query = "SELECT to_char(i, 'YYYY') as year_data, to_char(i, 'MM') as month_data, to_char(i, 'Month') as month_string, count(id) as android_total_count FROM generate_series(now() - INTERVAL '1 year', now(), '1 month') as i left join users on (to_char(i, 'YYYY') = to_char(created_date, 'YYYY') and to_char(i, 'MM') = to_char(created_date, 'MM') and login_type = 2) GROUP BY 1,2,3 order by year_data desc, month_data desc limit 12";
		$android_user_count = DB::select($android_user_query);
		$ios_user_query = "SELECT to_char(i, 'YYYY') as year_data, to_char(i, 'MM') as month_data, to_char(i, 'Month') as month_string, count(id) as ios_total_count FROM generate_series(now() - INTERVAL '1 year', now(), '1 month') as i left join users on (to_char(i, 'YYYY') = to_char(created_date, 'YYYY') and to_char(i, 'MM') = to_char(created_date, 'MM') and login_type = 3) GROUP BY 1,2,3 order by year_data desc, month_data desc limit 12";
		$ios_user_count = DB::select($ios_user_query);

		$language_id = getAdminCurrentLang();
		$vendor_language_query = '"vendors_infos"."lang_id" = (case when (select count(id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$store_transaction_query = "SELECT vendors_infos.vendor_name, SUM (total_amount) AS total FROM orders JOIN vendors on vendors.id = orders.vendor_id JOIN vendors_infos on vendors_infos.id = vendors.id where " . $vendor_language_query . " GROUP BY vendor_name ORDER BY total DESC";
		$store_transaction_count = DB::select($store_transaction_query);
		$vendors_query = "SELECT
        (
        SELECT COUNT(1)
        FROM vendors
        JOIN vendors_infos on vendors_infos.id = vendors.id
        WHERE date_trunc('day', created_date) = date_trunc('day', CURRENT_DATE)  AND $vendor_language_query) AS day_count,

        (SELECT COUNT(1)
        FROM vendors
        JOIN vendors_infos on vendors_infos.id = vendors.id
        WHERE date_trunc('WEEK', created_date) = date_trunc('WEEK', CURRENT_DATE) AND $vendor_language_query ) AS week_count,

        (SELECT COUNT(1)
        FROM vendors
        JOIN vendors_infos on vendors_infos.id = vendors.id
        WHERE date_trunc('month', created_date) = date_trunc('month', CURRENT_DATE) AND $vendor_language_query) AS month_count,

        (SELECT COUNT(1)
        FROM vendors
        JOIN vendors_infos on vendors_infos.id = vendors.id
        WHERE date_trunc('year', created_date) = date_trunc('year', CURRENT_DATE) AND $vendor_language_query) AS year_count,
        COUNT(1) AS total_count
        FROM vendors
        JOIN vendors_infos on vendors_infos.id = vendors.id AND $vendor_language_query ";
		$vendors_period_count = DB::select($vendors_query);
		//$currency_symbol = getCurrency();
		//$currency_side   = getCurrencyPosition()->currency_side;

		return view('admin.home')->with('outlets', $outlets)->with('products', $products)->with('drivers', $drivers)->with('coupons', $coupons)->with('newsletter_subscribers', $newsletter_subscribers)->with('order_status_count', $order_status_count)->with('year_transaction', $year_transaction)->with('web_user_count', $web_user_count)->with('android_user_count', $android_user_count)->with('ios_user_count', $ios_user_count)->with('store_transaction_count', $store_transaction_count)->with('orders', $orders)->with('order_period_count', $order_period_count)->with('transaction_period_count', $transaction_period_count)->with('users_period_count', $users_period_count)->with('vendors_period_count', $vendors_period_count)->with('drivers_period_count', $drivers_period_count)->with('newsletter_subscribers_period_count', $newsletter_subscribers_period_count)->with('blogs_count', $blogs_count)->with('outlets_period_count', $outlets_period_count)->with('outlet_reviews_query', $outlet_reviews_query); //->with('outlet_managers', $outlet_managers) ->with('currency_symbol', $currency_symbol)->with('currency_side', $currency_side)
	}

	public function adminlogout() {
		$id = Auth::id();
		$users = Users::find($id);
		$this->logoutactivity($users);
		Auth::logout();
		//$locale=Session::get('locale');
		//App::setLocale($locale);
		//Session::flush();
		return Redirect::to('admin/login');
	}

	public function logoutactivity($obj) {
		$user = $obj;
		$message = "Logged out";
		if ($user->id) {
			userlog($message, $user->id);
		}
	}
	public function loginactivity($obj) {
		$user = $obj;
		$message = "Logged in as administrator in " . ucfirst(Session::get("general")->site_name);
		if ($user->id) {
			userlog($message, $user->id);
		}
	}

	public function general_settings() {
		if (!hasTask('admin/settings/general')) {
			return view('errors.404');
		}
		$id = 1;
		$settings = Settings::find($id);
		$info = new Settings_infos;
		$languages = DB::table('languages')->where('status', '=', 1)->get();
		$countries = DB::select('select c.*, ci.* FROM "countries" AS "c" LEFT JOIN "countries_infos" AS "ci" ON ("ci"."id" = "c"."id" AND "ci"."language_id" = (case when (select count(*) as totalcount from countries_infos as cinfo where cinfo.language_id = 1 and id = ci.id) > 0 THEN 1 ELSE 1 END)) where country_status = 1 order by country_name asc');
		return view('admin.settings.general_settings')->with('settings', $settings)->with('countries', $countries)->with('languages', $languages)->with('infomodel', $info);
	}
	/**
	 * Store updated settings in storage.
	 *
	 * @return Response
	 */
	public function update_general_settings(Request $data, $id) {
		if (!hasTask('admin/settings/general')) {
			return view('errors.404');
		}
		// echo '<pre>' ;print_r($data->all());exit;
		// validate
		// read more on validation at http://laravel.com/docs/validation
		$validation = Validator::make($data->all(), array(
			//'title' => 'required',
			//'site_name' => 'required',
			'site_owner' => 'required|regex:/(^[A-Za-z0-9 ]+$)+/|min:3|max:32',
			'email' => 'required|email',
			//'telephone'=>'required',
			'telephone' => 'required|regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
			'fax' => 'required',
			'geocode' => 'required',
			'min_fund_request' => 'required|regex:/^\d*(\.\d{1,2})?$/',
			'max_fund_request' => 'required|regex:/^\d*(\.\d{1,2})?$/',
			//'meta_title' => 'required',
			//'meta_keywords' => 'required',
			//'meta_description' => 'required',
			'contact_address' => 'required',
			'country_code' => 'required',
			//'default_language' => 'required',
			//'default_country' => 'required',
			// 'copyrights' => 'required',
			'logo' => 'mimes:png,jpeg,bmp|max:2024',
			'favicon' => 'mimes:png,jpeg,bmp,ico|max:2024',
			// 'footer_text' => 'required',
			'theme' => 'required',
			//mimes:jpeg,bmp,png and for max size max:10000
		));
		$site_name = Input::get('site_name');
		foreach ($site_name as $key => $value) {
			$fields['site_name' . $key] = $value;
			$rules['site_name' . '1'] = 'required';
		}
		$copyrights = Input::get('copyrights');
		foreach ($copyrights as $key => $value) {
			$fields['copyrights' . $key] = $value;
			$rules['copyrights' . '1'] = 'required';
		}
		$meta_title = Input::get('meta_title');
		foreach ($meta_title as $key => $value) {
			$fields['meta_title' . $key] = $value;
			$rules['meta_title' . '1'] = 'required';
		}
		$meta_keywords = Input::get('meta_keywords');
		foreach ($meta_keywords as $key => $value) {
			$fields['meta_keywords' . $key] = $value;
			$rules['meta_keywords' . '1'] = 'required';
		}
		$meta_description = Input::get('meta_description');
		foreach ($meta_description as $key => $value) {
			$fields['meta_description' . $key] = $value;
			$rules['meta_description' . '1'] = 'required';
		}
		$footer_text = Input::get('footer_text');
		foreach ($footer_text as $key => $value) {
			$fields['footer_text' . $key] = $value;
			$rules['footer_text' . '1'] = 'required';
		}
		$site_description = Input::get('site_description');
		foreach ($site_description as $key => $value) {
			$fields['site_description' . $key] = $value;
			$rules['site_description' . '1'] = 'required';
		}
		// process the validation
		if ($validation->fails()) {
			return Redirect::back()->withErrors($validation)->withInput();
		} else {
//echo '<pre>';print_r($_POST);die;
			// store datas in to database
			$settings = Settings::find($id);
			// $settings->site_name      = $_POST['site_name'];
			$settings->site_owner = $_POST['site_owner'];
			$settings->email = $_POST['email'];
			$settings->telephone = $_POST['telephone'];
			$settings->fax = $_POST['fax'];
			$settings->min_fund_request = $_POST['min_fund_request'];
			$settings->max_fund_request = $_POST['max_fund_request'];
			$settings->geocode = $_POST['geocode']; //print_r($settings->geocode);exit;
			//$settings->meta_title    = $_POST['meta_title'];
			//$settings->meta_keywords    = $_POST['meta_keywords'];
			//$settings->site_description    = $_POST['site_description'];
			//$settings->meta_description    = $_POST['meta_description'];
			$settings->contact_address = $_POST['contact_address'];
			$settings->country_code = $_POST['country_code'];
			//$settings->default_language    = $_POST['default_language'];
			// $settings->default_country    = $_POST['default_country'];
			//$settings->copyrights    = $_POST['copyrights'];
			$settings->updated_at = date("Y-m-d H:i:s");
			// $settings->footer_text    = $_POST['footer_text'];
			$settings->theme = $_POST['theme'];
			//echo '<pre>'; print_r( $settings);exit;
			$settings->save();

			if (isset($_FILES['logo']['name']) && $_FILES['logo']['name'] != '') {
				$destinationPath = base_path() . '/public/assets/front/' . Session::get("general")->theme . '/images/logo/'; // upload path
				$logoName = 'logo' . '.' .
				$data->file('logo')->getClientOriginalExtension();
				$data->file('logo')->move(base_path() . '/public/assets/front/' . Session::get("general")->theme . '/images/logo/', $logoName);
				$destinationPath1 = url('/assets/front/' . Session::get("general")->theme . '/images/logo/' . $logoName . '');

				Image::make($destinationPath1)->fit(getImageResize('LOGO')['WIDTH'], getImageResize('LOGO')['HEIGHT'])->save(base_path() . '/public/assets/front/' . Session::get("general")->theme . '/images/logo/159_81/' . $logoName)->destroy();
				$settings->logo = $logoName;
				$settings->save();
			}
			if (isset($_FILES['front_logo']['name']) && $_FILES['front_logo']['name'] != '') {
				$destinationPath = base_path() . '/public/assets/front/' . Session::get("general")->theme . '/images/logo/'; // upload path
				$frontlogoName = 'front_logo' . '.' .
				$data->file('front_logo')->getClientOriginalExtension();
				$data->file('front_logo')->move(base_path() . '/public/assets/front/' . Session::get("general")->theme . '/images/logo/', $frontlogoName);
				$destinationPath1 = url('/assets/front/' . Session::get("general")->theme . '/images/logo/' . $frontlogoName . '');
				Image::make($destinationPath1)->fit(199, 133)->save(base_path() . '/public/assets/front/' . Session::get("general")->theme . '/images/logo/159_81/' . $frontlogoName)->destroy();
				$settings->front_logo = $frontlogoName;

				$settings->save();
			}
			if (isset($_FILES['responsive_logo']['name']) && $_FILES['responsive_logo']['name'] != '') {
				$destinationPath = base_path() . '/public/assets/admin/base/images/'; // upload path
				$responsive_logo = 'responsive_logo' . '.' . 'png';
				$data->file('responsive_logo')->move(base_path() . '/public/assets/admin/base/images/', $responsive_logo);
				$destinationPath1 = url('/assets/admin/base/images/' . $responsive_logo . '');
				Image::make($destinationPath1)->fit(50, 50)->save(base_path() . '/public/assets/admin/base/images/logo/159_81/' . $responsive_logo)->destroy();
				$settings->responsive_logo = $responsive_logo;
				$settings->save();
			}
			if (isset($_FILES['favicon']['name']) && $_FILES['favicon']['name'] != '') {
				$destinationPath = base_path() . '/public/assets/front/' . Session::get("general")->theme . '/images/favicon/'; // upload path
				$faviconName = 'favicon' . '.' .
				$data->file('favicon')->getClientOriginalExtension();
				$data->file('favicon')->move(base_path() . '/public/assets/front/' . Session::get("general")->theme . '/images/favicon/', $faviconName);
				$destinationPathfavicon = url('/assets/front/' . Session::get("general")->theme . '/images/favicon/' . $faviconName . '');
				Image::make($destinationPathfavicon)->fit(getImageResize('FAVICON')['WIDTH'], getImageResize('FAVICON')['HEIGHT'])->save(base_path() . '/public/assets/front/' . Session::get("general")->theme . '/images/favicon/16_16/' . $faviconName)->destroy();
				$settings->favicon = $faviconName;
				$settings->save();
			}
			$this->settings_save_after($settings, $_POST);
			Session::flash('message', trans('messages.General settings has been successfully updated'));
			return Redirect::to('admin/settings/general');
		}
	}

	public static function settings_save_after($object, $post) {
		if (isset($post['site_name'])) {

			$site_name = $post['site_name'];
			$meta_title = $post['meta_title'];
			$meta_keywords = $post['meta_keywords'];
			$meta_description = $post['meta_description'];
			$site_description = $post['site_description'];
			$copyrights = $post['copyrights'];
			$footer_text = $post['footer_text'];
			try {
				$data = Settings_infos::find($object->id);

				if (count($data) > 0) {
					$data->delete();
				}
				$languages = DB::table('languages')->where('status', 1)->get();
				$s = 0;
				foreach ($languages as $key => $lang) {
					if ((isset($copyrights[$lang->id]) && $copyrights[$lang->id] != "")) {
						$infomodel = new Settings_infos;
						$infomodel->language_id = $lang->id;
						$infomodel->id = $object->id;
						$infomodel->site_name = $site_name[$lang->id];
						$infomodel->site_description = $site_description[$lang->id]; //echo '<pre>';print_r( $infomodel);exit;
						$infomodel->meta_title = $meta_title[$lang->id];
						$infomodel->meta_keywords = $meta_keywords[$lang->id];
						$infomodel->meta_description = $meta_description[$lang->id];
						$infomodel->footer_text = $footer_text[$lang->id];
						$infomodel->copyrights = $copyrights[$lang->id]; //if($s==1){echo '<pre>' ; print_r($infomodel);exit;}
						$infomodel->save(); //echo"in";exit;
						$s++;
					}
				}
			} catch (Exception $e) {
				Log::Instance()->add(Log::ERROR, $e);
			}
		}
	}
	public function store() {
		if (!hasTask('admin/settings/store')) {
			return view('errors.404');
		}
		$id = 1;
		$settings = Stores::find($id);
		return view('admin.settings.store_settings')->with('settings', $settings);
	}

	/**
	 * Store updated settings in storage.
	 *
	 * @return Response
	 */
	public function update_store(Request $data, $id) {
		if (!hasTask('admin/settings/store')) {
			return view('errors.404');
		}
		//print_r($_POST);exit;
		// validate
		// read more on validation at http://laravel.com/docs/validation
		$validation = Validator::make($data->all(), array(
			'meta_title' => 'required',
			'meta_keywords' => 'required',
			'meta_description' => 'required',
			'template' => 'required',
			//mimes:jpeg,bmp,png and for max size max:10000
		));
		// process the validation
		if ($validation->fails()) {
			return Redirect::back()->withErrors($validation)->withInput();
		} else {
			// store datas in to database
			if ($id) {
				$settings = Stores::find($id);
			} else {
				$settings = new Stores;
			}
			$settings->meta_title = $_POST['meta_title'];
			$settings->meta_keywords = $_POST['meta_keywords'];
			$settings->meta_description = $_POST['meta_description'];
			$settings->template = $_POST['template'];
			$settings->updated_at = date("Y-m-d H:i:s");
			$settings->save();

			//$setting = new Settings;
			$setting = Settings::find(1);
			$setting->theme = $_POST['template'];
			$setting->updated_at = date("Y-m-d H:i:s");
			$setting->save();
			// redirect
			Session::flash('message', trans('messages.Settings has been successfully updated'));
			return Redirect::to('admin/settings/store');
		}
	}

	public function email_settings() {
		if (!hasTask('admin/settings/email')) {
			return view('errors.404');
		}
		$id = 1;
		$settings = Emailsettings::find($id);
		return view('admin.settings.email_settings')->with('settings', $settings);
	}

	/**
	 * Store updated email settings in storage.
	 *
	 * @return Response
	 */
	public function update_email_settings(Request $data, $id) {
		if (!hasTask('admin/settings/email')) {
			return view('errors.404');
		}
		// validate
		// read more on validation at http://laravel.com/docs/validation
		$validation = Validator::make($data->all(), array(
			//'title' => 'required',
			'contact_mail' => 'required|email',
			'support_mail' => 'required|email',
			'mobile_number' => 'required',
			'smtp_host_name' => 'required',
			'smtp_username' => 'required',
			'smtp_password' => 'required',
			'smtp_port' => 'required',
			'smtp_encryption' => 'required',
			'mail_driver' => 'required',
		));
		// process the validation
		if ($validation->fails()) {
			return Redirect::back()->withErrors($validation)->withInput();
		} else {
			// store datas in to database
			$settings = Emailsettings::find($id);
			$settings->contact_mail = $_POST['contact_mail'];
			$settings->support_mail = $_POST['support_mail'];
			$settings->mobile_number = $_POST['mobile_number'];
			$settings->skype = $_POST['skype'];
			$settings->smtp_host_name = $_POST['smtp_host_name'];
			$settings->smtp_username = $_POST['smtp_username'];
			$settings->smtp_password = $_POST['smtp_password'];
			$settings->smtp_port = $_POST['smtp_port'];
			$settings->smtp_encryption = $_POST['smtp_encryption'];
			$settings->smtp_enable = $_POST['smtp_enable'];
			$settings->mail_driver = $_POST['mail_driver'];
			$settings->updated_at = date("Y-m-d H:i:s");
			$settings->save();
			// redirect
			Session::flash('message', trans('messages.Email settings has been successfully updated'));
			return Redirect::to('admin/settings/email');
		}
	}

	public function social_media_settings() {
		if (!hasTask('admin/settings/socialmedia')) {
			return view('errors.404');
		}
		$id = 1;
		$settings = Socialmediasettings::find($id);
		return view('admin.settings.social_media_settings')->with('settings', $settings);
	}

	/**
	 * Store updated social media settings in storage.
	 *
	 * @return Response
	 */
	public function update_media_settings(Request $data, $id) {
		if (!hasTask('admin/settings/socialmedia')) {
			return view('errors.404');
		}
		// validate
		// read more on validation at http://laravel.com/docs/validation
		$validation = Validator::make($data->all(), array(
			'facebook_page' => 'required',
			'twitter_page' => 'required',
			'instagram_page' => 'required',
			'linkedin_page' => 'required',
			'google_plus_page' => 'required',
			'tumblr_page' => 'required',
			'youtube_url' => 'required',
			'android_page' => 'required',
			'iphone_page' => 'required',
			'facebook_app_id' => 'required',
			'facebook_secret_key' => 'required',
			'twitter_api_key' => 'required',
			'twitter_secret_key' => 'required',
			'gmap_api_key' => 'required',
			'analytics_code' => 'required',
		));
		// process the validation
		if ($validation->fails()) {
			return Redirect::back()->withErrors($validation)->withInput();
		} else {
			// store datas in to database
			$settings = Socialmediasettings::find($id);
			$settings->facebook_page = $_POST['facebook_page'];
			$settings->instagram_page = $_POST['instagram_page'];
			$settings->twitter_page = $_POST['twitter_page'];
			$settings->linkedin_page = $_POST['linkedin_page'];
			$settings->google_plus_page = $_POST['google_plus_page'];
			$settings->tumblr_page = $_POST['tumblr_page'];
			$settings->youtube_url = $_POST['youtube_url'];
			$settings->android_page = $_POST['android_page'];
			$settings->iphone_page = $_POST['iphone_page'];
			$settings->facebook_app_id = $_POST['facebook_app_id'];
			$settings->facebook_secret_key = $_POST['facebook_secret_key'];
			$settings->twitter_api_key = $_POST['twitter_api_key'];
			$settings->twitter_secret_key = $_POST['twitter_secret_key'];
			$settings->gmap_api_key = $_POST['gmap_api_key'];
			$settings->analytics_code = $_POST['analytics_code'];
			$settings->updated_at = date("Y-m-d H:i:s");
			$settings->save();
			// redirect
			Session::flash('message', trans('messages.Social media settings has been successfully updated'));
			return Redirect::to('admin/settings/socialmedia');
		}
	}

	public function local() {
		if (!hasTask('admin/settings/local')) {
			return view('errors.404');
		}
		$settings = Settings::find(1);
		return view('admin.settings.local_settings')->with('settings', $settings);
	}

	/**
	 * Store updated email settings in storage.
	 *
	 * @return Response
	 */
	public function indexFaq() {

		$data = DB::table('faq')

			->select('id', 'question', 'answer', 'type', 'created_date', 'updated_date')
			->orderby('id', 'asc')

			->LIMIT('2')
			->paginate(2);
		// 	->get();

		return view('admin.faq.index', compact('data'));

	}


	public function indextrigger() {

		return view('admin.trigger.index');

	}

	// public function pageFaq() {

	// 	$data = DB::table('faq')->paginate(2);

	// 	return view('admin.faq.index', compact('data'));

	// }

	public function editFaq() {

		return view('admin.faq.edit');

	}

	public function deleteFaq() {

		return view('admin.faq.delete');

	}

	public function viewFaq() {

		return view('admin.faq.view');

	}

	public function rFaq() {

		return view('admin.faq.faqans_faq');

	}

	public function insert_faq(Request $data) {

	}

	public function update_local(Request $data, $id) {
		if (!hasTask('admin/settings/local')) {
			return view('errors.404');
		}
		// validate
		// read more on validation at http://laravel.com/docs/validation
		$validation = Validator::make($data->all(), array(
			//'title' => 'required',
			'default_country' => 'required|integer',
			'default_city' => 'required|integer',
			'default_language' => 'required|integer',
			'default_currency' => 'required|integer',
			'currency_side' => 'required|integer',
			'default_weight_class' => 'required|integer',
		));
		// process the validation
		if ($validation->fails()) {
			return Redirect::back()->withErrors($validation)->withInput();
		} else {
			// store datas in to database
			$settings = Settings::find($id);
			$settings->default_country = $_POST['default_country'];
			$settings->default_city = $_POST['default_city'];
			$settings->default_language = $_POST['default_language'];
			$settings->default_currency = $_POST['default_currency'];
			$settings->currency_side = $_POST['currency_side'];
			$settings->default_weight_class = $_POST['default_weight_class'];
			$settings->save();
			// redirect
			Session::flash('message', trans('messages.Local settings has been successfully updated'));
			return Redirect::to('admin/settings/local');
		}
	}

	public function image_settings() {
		if (!hasTask('admin/settings/image')) {
			return view('errors.404');
		}
		$id = 1;
		$common = Imageresizesettings::find(1);
		$store = Imageresizesettings::find(2);
		$product = Imageresizesettings::find(3);
		$banner = Imageresizesettings::find(4);
		$vendor = Imageresizesettings::find(5);
		return view('admin.settings.image_settings')->with('common', $common)->with('store', $store)->with('product', $product)->with('banner', $banner)->with('vendor', $vendor);
	}

	/**
	 * Store updated email settings in storage.
	 *
	 * @return Response
	 */
	public function update_image_settings(Request $data, $id) {
		if (!hasTask('admin/settings/image')) {
			return view('errors.404');
		}
		// validate
		// read more on validation at http://laravel.com/docs/validation
		$validation = Validator::make($data->all(), array(
			//'title' => 'required',
			'logo_width' => 'required|numeric',
			'logo_height' => 'required|numeric',
			'favicon_width' => 'required|numeric',
			'favicon_height' => 'required|numeric',
			'category_width' => 'required|numeric',
			'category_height' => 'required|numeric',
			'store_list_width' => 'required|numeric',
			'store_list_height' => 'required|numeric',
			'store_detail_width' => 'required|numeric',
			'store_detail_height' => 'required|numeric',
			'store_thumb_width' => 'required|numeric',
			'store_thumb_height' => 'required|numeric',
			'product_list_width' => 'required|numeric',
			'product_list_height' => 'required|numeric',
			'product_detail_width' => 'required|numeric',
			'product_detail_height' => 'required|numeric',
			'product_thumb_width' => 'required|numeric',
			'product_thumb_height' => 'required|numeric',
			'banner_list_width' => 'required|numeric',
			'banner_list_height' => 'required|numeric',
		));
		// process the validation
		if ($validation->fails()) {
			return Redirect::back()->withErrors($validation)->withInput();
		} else {

			if ($_POST['common']) {

				$common = Imageresizesettings::find(1);
				$common->list_width = $_POST['logo_width'];
				$common->list_height = $_POST['logo_height'];
				$common->detail_width = $_POST['favicon_width'];
				$common->detail_height = $_POST['favicon_height'];
				$common->thumb_width = $_POST['category_width'];
				$common->thumb_height = $_POST['category_height'];
				$common->type = $_POST['common'];
				$common->updated_at = date("Y-m-d H:i:s");
				$common->save();
			}

			if ($_POST['store']) {
				$store = Imageresizesettings::find(2);
				$store->list_width = $_POST['store_list_width'];
				$store->list_height = $_POST['store_list_height'];
				$store->detail_width = $_POST['store_detail_width'];
				$store->detail_height = $_POST['store_detail_height'];
				$store->thumb_width = $_POST['store_thumb_width'];
				$store->thumb_height = $_POST['store_thumb_height'];
				$store->type = $_POST['store'];
				$store->updated_at = date("Y-m-d H:i:s");
				$store->save();
			}

			if ($_POST['product']) {
				$product = Imageresizesettings::find(3);
				$product->list_width = $_POST['product_list_width'];
				$product->list_height = $_POST['product_list_height'];
				$product->detail_width = $_POST['product_detail_width'];
				$product->detail_height = $_POST['product_detail_height'];
				$product->thumb_width = $_POST['product_thumb_width'];
				$product->thumb_height = $_POST['product_thumb_height'];
				$product->type = $_POST['product'];
				$product->updated_at = date("Y-m-d H:i:s");
				$product->save();
			}

			if ($_POST['banner']) {
				$banner = Imageresizesettings::find(4);
				$banner->list_width = $_POST['banner_list_width'];
				$banner->list_height = $_POST['banner_list_height'];
				$banner->type = $_POST['banner'];
				$banner->updated_at = date("Y-m-d H:i:s");
				$banner->save();
			}
			if ($_POST['vendor']) {
				$vendor = Imageresizesettings::find(5);
				$vendor->list_width = $_POST['vendor_list_width'];
				$vendor->list_height = $_POST['vendor_list_height'];
				$vendor->detail_width = $_POST['vendor_detail_width'];
				$vendor->detail_height = $_POST['vendor_detail_height'];
				$vendor->thumb_width = $_POST['vendor_thumb_width'];
				$vendor->thumb_height = $_POST['vendor_thumb_height'];
				$vendor->type = $_POST['vendor'];
				$vendor->updated_at = date("Y-m-d H:i:s");
				$vendor->save();
			}
			// redirect
			Session::flash('message', trans('messages.Image settings has been successfully updated'));
			return Redirect::to('admin/settings/image');
		}
	}
	/*
		     * Render the change password view
	*/
	public function change_password() {
		if (Auth::guest()) {
			return redirect()->guest('admin/login');
		} else {
			return view('admin.reset');
		}
	}

	/*
		     * Vendor change password request goes here
	*/
	public function change_details(Request $data) {
		if (Auth::guest()) {
			return redirect()->guest('admin/login');
		}
		$datas = Input::all();
		// validate
		// read more on validation at http://laravel.com/docs/validation
		$validation = Validator::make($data->all(), array(
			'old_password' => 'required|min:5|max:16|regex:/(^[A-Za-z0-9 !@#$%]+$)+/',
			'password' => 'required|min:5|max:16|confirmed|regex:/(^[A-Za-z0-9 !@#$%]+$)+/',
			'password_confirmation' => 'required|min:5|max:16|regex:/(^[A-Za-z0-9 !@#$%]+$)+/',
		));
		// process the validation
		if ($validation->fails()) {
			//return redirect('create')->withInput($datas)->withErrors($validation);
			return Redirect::back()->withErrors($validation)->withInput();
		} else {
			//Get new password details from posts
			$old_password = Input::get('old_password');
			$string = Input::get('password');
			$pass_string = Hash::make($string);
			$old_pass_string = Hash::make($old_password);
			$session_userid = Auth::id();
			$users_data = DB::table('users')
				->select('id', 'name', 'email', 'password')
				->where('id', $session_userid)
				->Where(function ($query) {
					$query->orWhere('user_type', 1)
						->orWhere('user_type', 2);
				})
				->first();
			if (count($users_data) > 0 && Hash::check($old_password, $users_data->password) == 1) {
				//Sending the mail to vendors
				$template = DB::table('email_templates')
					->select('from_email', 'from', 'subject', 'template_id', 'content')
					->where('template_id', '=', self::ADMIN_CHANGE_PASSWORD_EMAIL_TEMPLATE)
					->get();
				if (count($template)) {
					$from = $template[0]->from_email;
					$from_name = $template[0]->from;
					$subject = $template[0]->subject;
					if (!$template[0]->template_id) {
						$template = 'mail_template';
						$from = getAppConfigEmail()->contact_email;
						$subject = getAppConfig()->site_name . " New Password Request Updated";
						$from_name = "";
					}
					$content = array("name" => '' . $users_data->name, "email" => '' . $users_data->email, "password" => '' . $string);
					$email = smtp($from, $from_name, $users_data->email, $subject, $content, $template);
				}
				//Update random password to users table to coreesponding admin
				$res = DB::table('users')
					->where('id', $session_userid)
					->update(['password' => $pass_string]);
				//After updating new password details logout the session and redirects to login page
				Session::flash('message', trans('messages.Your Password Changed Successfully.'));
				return Redirect::to('admin/dashboard');
			} else {
				$validation->errors()->add('old_password', 'Old password is incorrect.');
				return Redirect::back()->withErrors($validation)->withInput();
			}
		}
	}

	/* Newsletter Management Start */
	public function newsletter() {
		if (Auth::guest()) {
			return redirect()->guest('admin/login');
		} else {
			if (!hasTask('admin/newsletter')) {
				return view('errors.404');
			}
			SEOMeta::setTitle('Newsletter - ' . $this->site_name);
			SEOMeta::setDescription('Newsletter - ' . $this->site_name);
			return view('admin.newsletter.send');
		}
	}
	public function send_newsletter(Request $data) {
		if (!hasTask('send_newsletter')) {
			return view('errors.404');
		}
		$entity_type = $_POST['entity_type'];
		$fields['users'] = isset($_POST['users']) ? $_POST['users'] : '';
		$fields['subject'] = $_POST['subject'];
		$fields['message'] = $_POST['message'];
		$rules = array(
			'users' => 'required',
			'subject' => 'required',
			'message' => 'required',
		);
		$validator = Validator::make($fields, $rules);
		// process the validation
		if ($validator->fails()) {
			return Redirect::back()->withErrors($validator)->withInput();
		} else {
			try {
				if ($entity_type == 3) {
					$groups = $_POST['users'];
					$users_list = all_customers_list($groups);
					$user_email = array();
					if (count($users_list) > 0) {
						$u = 0;
						foreach ($users_list as $u_l) {
							$user_email[$u] = $u_l->email;
							$u++;
						}
						$email = $user_email;
						$subject = $_POST['subject'];
						$content = $_POST['message'];
						$template = DB::table('email_templates')
							->select('from_email', 'from', 'subject', 'template_id', 'content')
							->where('template_id', '=', self::COMMON_MAIL_TEMPLATE)
							->get();
						if (count($template)) {
							$from = $template[0]->from_email;
							$from_name = $template[0]->from;
							//$subject = $template[0]->subject;
							if (!$template[0]->template_id) {
								$template = 'mail_template';
								$from = getAppConfigEmail()->contact_email;
								$subject = "Welcome to " . getAppConfig()->site_name;
								$from_name = "";
							}
							$content = array("notification" => array('MAIL' => $content));
							$email = smtp($from, $from_name, $email, $subject, $content, $template);
						}
					}
				} else {
					$email = $_POST['users'];
					$subject = $_POST['subject'];
					$content = $_POST['message'];
					$template = DB::table('email_templates')
						->select('from_email', 'from', 'subject', 'template_id', 'content')
						->where('template_id', '=', self::COMMON_MAIL_TEMPLATE)
						->get();
					if (count($template)) {
						$from = $template[0]->from_email;
						$from_name = $template[0]->from;
						//$subject = $template[0]->subject;
						if (!$template[0]->template_id) {
							$template = 'mail_template';
							$from = getAppConfigEmail()->contact_email;
							$subject = "Welcome to " . getAppConfig()->site_name;
							$from_name = "";
						}
						$content = array("notification" => array('MAIL' => $content));
						$email = smtp($from, $from_name, $email, $subject, $content, $template);
					}
				}
				Session::flash('message', trans('messages.The Newsletter has been sent successfully'));
			} catch (Exception $e) {
				Log::Instance()->add(Log::ERROR, $e);
			}
			return Redirect::to('admin/newsletter');
		}
	}
	/* To get the all customers list */
	public function getAllCustomersData(Request $request) {
		if ($request->ajax()) {
			$customers_list = all_customers_list();
			return response()->json([
				'data' => $customers_list,
			]);
		}
	}
	/* To get the all newsletter subscribers list */
	public function getAllSubscribersData(Request $request) {
		if ($request->ajax()) {
			$newsletter_subs_list = all_newsletter_subscribers_list();
			return response()->json([
				'data' => $newsletter_subs_list,
			]);
		}
	}
	/* To get the all customers groups list */
	public function getAllCustomersGroupData(Request $request) {
		if ($request->ajax()) {
			$customers_groups_list = all_customers_groups_list();
			return response()->json([
				'data' => $customers_groups_list,
			]);
		}
	}

	//Get city list for ajax request
	public function getUserData(Request $request) {
		if ($request->ajax()) {
			$entity_type = $request->input('entity_type');
			$users_list = getUserList($entity_type);
			return response()->json([
				'data' => $users_list,
			]);
		}
	}
	/* Newsletter Management End */

	public function driver_core_settings()
    {
    	
        $id=1;
		$driver_core = driver_cores::find($id);
        return view('admin.settings.driver_core')->with('data', $driver_core);
    }
    public function updatedrivercore(Request $data, $id)
    {
    	//echo"<pre>";print_r($data->all());exit;
    	
       if (!hasTask('admin/settings/driver_core_settings')) {
			return view('errors.404');
		}
	
		$validation = Validator::make($data->all(), array(
			'app_name' => 'required|regex:/(^[A-Za-z0-9 ]+$)+/|min:3|max:32',
			//'app_logo' => 'mimes:png,jpeg,bmp|max:2024',
			//'login_log' => 'mimes:png,jpeg,bmp|max:2024',
			'country_code' => 'required|integer',
			'android_key' => 'required',
			'latest_version' => 'required|integer',
			'forceupdate_version' => 'required|integer',
			'update_type' => 'required|integer',
			'update_message' => 'required',

			'ioslatest_version' => 'required|integer',
			'iosforceupdate_version' => 'required|integer',
			'iosupdate_type' => 'required|integer',
			'iosupdate_message' => 'required',

			'no_imageurl' => 'required|regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/',
			'error_reportcase' => 'required',
			'socket_url' => 'required',
			'aboutus' => 'required',
			'terms_condition' => 'required',
		));
		// process the validation
		if ($validation->fails()) {
			return Redirect::back()->withErrors($validation)->withInput();
		} else {
			$_POST =  $data->all();
			// store datas in to database
			$id=1;
			$driver_core = driver_cores::find($id);
			$driver_core->app_name = $_POST['app_name'];
			$driver_core->country_code = $_POST['country_code'];
			$driver_core->android_key = $_POST['android_key'];
			$driver_core->latest_version = $_POST['latest_version'];
			$driver_core->forceupdate_version = $_POST['forceupdate_version'];
			$driver_core->update_type = $_POST['update_type'];
			$driver_core->update_message = $_POST['update_message'];

			$driver_core->ioslatest_version = $_POST['ioslatest_version'];
			$driver_core->iosforceupdate_version = $_POST['iosforceupdate_version'];
			$driver_core->iosupdate_type = $_POST['iosupdate_type'];
			$driver_core->iosupdate_message = $_POST['iosupdate_message'];
			
			$driver_core->no_imageurl = $_POST['no_imageurl'];
			$driver_core->error_reportcase = $_POST['error_reportcase'];
			$driver_core->socket_url = $_POST['socket_url'];
			$driver_core->aboutus = $_POST['aboutus'];
			$driver_core->terms_condition = $_POST['terms_condition'];
			$driver_core->updated_at = date("Y-m-d H:i:s");
			$driver_core->save();	
			Session::flash('message', trans('messages.Driver core settings has been successfully updated'));
			return Redirect::to('admin/settings/driver_core_settings');
		}
    }





    //customer_core_settings:

	public function customers_settings() {
		// if (!hasTask('admin/settings/customer')) {
		// 	return view('errors.404');
		// }
		// $id = 1;
		// $settings = Settings::find($id);
		// $info = new Settings_infos;
		// $languages = DB::table('languages')->where('status', '=', 1)->get();
		// $countries = DB::select('select c.*, ci.* FROM "countries" AS "c" LEFT JOIN "countries_infos" AS "ci" ON ("ci"."id" = "c"."id" AND "ci"."language_id" = (case when (select count(*) as totalcount from countries_infos as cinfo where cinfo.language_id = 1 and id = ci.id) > 0 THEN 1 ELSE 1 END)) where country_status = 1 order by country_name asc');
		$id=1;
		$customer_cores = customer_cores::find($id);
		//print_r($customer_cores);exit;
        return view('admin.settings.customer_core')->with('data', $customer_cores);
		//return view('admin.settings.customer_core')/*->with('settings', $settings)->with('countries', $countries)->with('languages', $languages)->with('infomodel', $info)*/;
	}


	public function updatecustomercore(Request $data, $id)
    {
    	//echo"<pre>";print_r($data->all());exit;
    	
       if (!hasTask('admin/settings/customer')) {
			return view('errors.404');
		}
		$validation = Validator::make($data->all(), array(
			'app_name' => 'required|regex:/(^[A-Za-z0-9 ]+$)+/|min:3|max:32',
			//'app_logo' => 'mimes:png,jpeg,bmp|max:2024',
			//'login_log' => 'mimes:png,jpeg,bmp|max:2024',
			'country_code' => 'required|integer',
			'android_key' => 'required',
			'latest_version' => 'required|integer',
			'forceupdate_version' => 'required|integer',
			'update_type' => 'required|integer',
			'update_message' => 'required',
			'ioslatest_version' => 'required|integer',
			'iosforceupdate_version' => 'required|integer',
			'iosupdate_type' => 'required|integer',
			'iosupdate_message' => 'required',
			'no_imageurl' => 'required|regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/',
			'error_reportcase' => 'required',
			'socket_url' => 'required',
			'aboutus' => 'required',
			'terms_condition' => 'required',

		));
		// process the validation
		if ($validation->fails()) {
			return Redirect::back()->withErrors($validation)->withInput();
		} else {
			$_POST =  $data->all();
			// store datas in to database
			$id=1;
			$driver_core = customer_cores::find($id);
			$driver_core->app_name = $_POST['app_name'];
			$driver_core->country_code = $_POST['country_code'];
			$driver_core->android_key = $_POST['android_key'];
			$driver_core->latest_version = $_POST['latest_version'];
			$driver_core->forceupdate_version = $_POST['forceupdate_version'];
			$driver_core->update_type = $_POST['update_type'];
			$driver_core->update_message = $_POST['update_message'];	

			$driver_core->ioslatest_version = $_POST['ioslatest_version'];
			$driver_core->iosforceupdate_version = $_POST['iosforceupdate_version'];
			$driver_core->iosupdate_type = $_POST['iosupdate_type'];
			$driver_core->iosupdate_message = $_POST['iosupdate_message'];
			$driver_core->no_imageurl = $_POST['no_imageurl'];
			$driver_core->error_reportcase = $_POST['error_reportcase'];
			$driver_core->socket_url = $_POST['socket_url'];
			$driver_core->aboutus = $_POST['aboutus'];
			$driver_core->terms_condition = $_POST['terms_condition'];
			$driver_core->updated_at = date("Y-m-d H:i:s");
			$driver_core->save();

		
		
			Session::flash('message', trans('messages.customer core settings has been successfully updated'));
			return Redirect::to('admin/settings/customer');
		}
    }



   /* public function driveraboutus()
    {
		$data=DB::table('driver_cores')
				->select('*')
				->first();

        //print_r($data);exit;

        return view('front.broz.driverabout',$data);
    }*/

    public function import_products() {
    	/*email test for refund*/

    	//$ddd= $this->getDistanceBetweenPoints(10.7882494, 76.618802, 11.01187, 76.8970233);
    	//$ddd= $this->vincentyGreatCircleDistance(10.7882494, 76.618802, 11.01187, 76.8970233,6371000);
    	//$ddd= $this->distance(10.7882494, 76.618802, 11.01187, 76.8970233,'K');
    	//print_r($ddd);exit;
    	$to = 'athhiraraveendran5@gmail.com';
		$template = DB::table('email_templates')->select('*')->where('template_id', '=',31)->get();
		//print_r($template);exit;
		if (count($template)) {
			$from = $template[0]->from_email;
			$from_name = $template[0]->from;
			if (!$template[0]->template_id) {
				$template = 'mail_template';
				$from = getAppConfigEmail()->contact_mail;
			}
			$subject = 'Test';
			$log_image = url('/assets/admin/email_temp/images/1570903488.jpg');
			$offer_image = url('/assets/admin/email_temp/images/1571292850.jpg');
			$order_id = '111';
			$currency_code = 'AED';
			$refund_amount = '555';
			$content = array("log_image"=>$log_image,"offer_image"=>$offer_image,"order_id"=>$order_id,"currency_code"=>$currency_code,"refund_amount"=>$refund_amount);				    	
			$attachment = "";
			$email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
		}
    	/*email test*/

    	
    	/*email test*/
        return view('admin.products.import_product');
    }



    function getDistanceBetweenPoints($lat1, $lon1, $lat2, $lon2) {
	    $theta = $lon1 - $lon2;
	    $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
	    $miles = acos($miles);
	    $miles = rad2deg($miles);
	    $miles = $miles * 60 * 1.1515;
	    $feet = $miles * 5280;
	    $yards = $feet / 3;
	    $kilometers = $miles * 1.609344;
	    $meters = $kilometers * 1000;
	    return $kilometers; 
	}
    public static function vincentyGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371)
	{
	  // convert from degrees to radians
	  $latFrom = deg2rad($latitudeFrom);
	  $lonFrom = deg2rad($longitudeFrom);
	  $latTo = deg2rad($latitudeTo);
	  $lonTo = deg2rad($longitudeTo);

	  $lonDelta = $lonTo - $lonFrom;
	  $a = pow(cos($latTo) * sin($lonDelta), 2) +
	    pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
	  $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

	  $angle = atan2(sqrt($a), $b);
	  return $angle * $earthRadius;
	}

    public function distance($lat1, $lon1, $lat2, $lon2, $unit) {
		  if (($lat1 == $lat2) && ($lon1 == $lon2)) {
		    return 0;
		  }
		  else {
		    $theta = $lon1 - $lon2;
		    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		    $dist = acos($dist);
		    $dist = rad2deg($dist);
		    $miles = $dist * 60 * 1.1515;
		    $unit = strtoupper($unit);

		    if ($unit == "K") {
		      return ($miles * 1.609344);
		    } else if ($unit == "N") {
		      return ($miles * 0.8684);
		    } else {
		      return $miles;
		    }
		  }
	}




    public function importdriver()
    {
    	print_r("expression");exit;

        return view('admin.products.import_product');
    }


    public function refferal_settings()
    {
    	//print_r("expression");exit;
        $id=1;
		$driver_core = core_referrals::find($id);
        return view('admin.settings.refferal_settings')->with('data', $driver_core);
    }

    public function updaterefferal(Request $data, $id)
    {
    	
       if (!hasTask('admin/settings/refferal_settings')) {
			return view('errors.404');
		}
 
		$validation = Validator::make($data->all(), array(
			'referral_amount' => 'required|integer',
			'referred_amount' => 'required|integer',
			'order_to_complete' => 'required|integer',
		));
		// process the validation
		if ($validation->fails()) {
			return Redirect::back()->withErrors($validation)->withInput();
		} else {
			$_POST =  $data->all();
			// store datas in to database
			$id=1;
			$referral = core_referrals::find($id);
			$referral->referral_amount = $_POST['referral_amount'];
			$referral->referred_amount = $_POST['referred_amount'];
			$referral->order_to_complete = $_POST['order_to_complete'];
			$referral->updated_at = date("Y-m-d H:i:s");
			$referral->save();	
			Session::flash('message', trans('messages.core refferal settings has been successfully updated'));
			return Redirect::to('admin/refferal/refferal_settings');
		}
    }

    public function indexfeedback() {
		return view('admin.feedback.list');

	}
	
    public function anyAjaxfeedbacklist()
    {
        
        $feedback = DB::table('user_feedback')
                    ->select('id', 'user_id', 'user_name', 'mobile', 'email', 'feedback','created_date')
                    ->orderBy('id', 'asc');
        return Datatables::of($feedback)->addColumn('action', function ($feedback) {
           /* if(hasTask('admin/drivers/edit'))
            {
                $html='<div class="btn-group">
                    <a href="'.URL::to("admin/drivers/edit/".$drivers->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                    <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/drivers/view/".$drivers->id).'" class="view-'.$drivers->id.'" title="'.trans("messages.View").'"><i class="fa fa-file-text-o"></i>&nbsp;&nbsp;'.@trans("messages.View").'</a></li>
                        <li><a href="'.URL::to("admin/drivers/delete/".$drivers->id).'" class="delete-'.$drivers->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $( document ).ready(function() {
                        $(".delete-'.$drivers->id.'").on("click", function(){
                            return confirm("'.trans("messages.Are you sure want to delete?").'");
                        });
                    });
                </script>';
                return $html;
            }*/
        })
        /*->addColumn('active_status', function ($drivers) {
            if($drivers->active_status == 0):
                $data = '<span class="label label-warning">'.trans("messages.Inactive").'</span>';
            elseif($drivers->active_status == 1):
                $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
            else:
                $data = '<span class="label label-danger">'.trans("messages.Delete").'</span>';
            endif;
            return $data;
        })*/
       /* ->addColumn('is_verified', function ($drivers) {
            if($drivers->is_verified == 0):
                $data = '<span class="label label-warning">'.trans("messages.Disabled").'</span>';
            elseif($drivers->is_verified == 1):
                $data = '<span class="label label-success">'.trans("messages.Enabled").'</span>';
            endif;
            return $data;
        })*/
        //->editColumn('first_name', '{!! $social_title.ucfirst($first_name)." ".$last_name !!}')
                        ->rawColumns(['action'])

        ->make(true);
    }

    public function terms_of_service()
	{
		return view('admin.terms_service.list');

	}
	public function anyAjaxtermsofserivce()
    {
        
        $termsofservice = DB::table('terms_of_serivce')
                    ->select('id', 'title', 'content','title_const')
                    ->orderBy('id', 'asc');
                   // ->get();

        return Datatables::of($termsofservice)->addColumn('action', function ($termsofservice) {

              $html='<div class="btn-group">
					<a href="'.URL::to("admin/settings/edit/".$termsofservice->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>

				</div>';
				return $html;
        })
      
        ->make(true);
    }
    public function create_termsofserivce()
    {
    	if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('admin/settings/create_termsofserivce'))
            {
                return view('errors.404');
            }
            // load the create form (resources/views/category/create.blade.php)
            return view('admin.terms_service.create');
        }
    }


    public function createtermsofservice(Request $data)
    {
    	//echo"<pre>";print_r($data->all());exit;
		$validation = Validator::make($data->all(), array(
			'title' => 'required|regex:/(^[A-Za-z0-9 ]+$)+/|min:3|max:32',
			'content' => 'required',
		));
		// process the validation
		if ($validation->fails()) {
			return Redirect::back()->withErrors($validation)->withInput();
		} else {
			$_POST =  $data->all();

			$terms      = new Terms_of_serivce;
            $terms->title = $_POST['title'];
            $terms->content = $_POST['content'];
            $terms->save();

			Session::flash('message', trans('messages.Driver core settings has been successfully updated'));
			return Redirect::to('admin/settings/terms_of_service');
		}
    }
    public function updatetermsofservice($id)
    {
    	$details = DB::table('terms_of_serivce')->select('*')->where('id','=',$id)->get();
    	return view('admin.terms_service.edit')->with('details',$details[0]);
    }
    public function updateterms(Request $data)
    {
		$validation = Validator::make($data->all(), array(
			'title' => 'required|regex:/(^[A-Za-z0-9 ]+$)+/|min:3|max:32',
			'content' => 'required',
		));
		// process the validation
		if ($validation->fails()) {
			return Redirect::back()->withErrors($validation)->withInput();
		} else {
			$_POST =  $data->all();
			$terms = Terms_of_serivce::find($_POST['id']);
            $terms->title = $_POST['title'];
            $terms->content = $_POST['content'];
            $terms->title_const = $_POST['title_const'];
            $terms->save();
			Session::flash('message', trans('messages.Driver core settings has been successfully updated'));
			return Redirect::to('admin/settings/terms_of_service');
		}

    }

    public function customer_promotion()
    {
         
		if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('admin/settings/customer_promotion')){
                return view('errors.404');
            }
            SEOMeta::setTitle('Customer Promotion - '.$this->site_name);
            SEOMeta::setDescription('Customer Promotion - '.$this->site_name);
            return view('admin.settings.customerPromotion.list');
        }
    }

    public function anyAjaxCustomerPromotionlist()
    {


        $customer_promotion = DB::table('customer_promotion')
                    ->select('customer_promotion.id', 'customer_promotion.promotion_name','customer_promotion.grocery_wallet', 'customer_promotion.base_amount','customer_promotion.addition_promotion','customer_promotion.active_status', 'customer_promotion.created_at', 'customer_promotion.updated_at', 'customer_promotion.start_date', 'customer_promotion.end_date')
                    ->orderBy('customer_promotion.id', 'desc');
        return Datatables::of($customer_promotion)->addColumn('action', function ($customer_promotion) {
            if(hasTask('admin/customer_promotion/edit'))
            {
                $html='<div class="btn-group">
                    <a href="'.URL::to("admin/customer_promotion/edit/".$customer_promotion->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                    <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/customer_promotion/view/".$customer_promotion->id).'" class="view-'.$customer_promotion->id.'" title="'.trans("messages.View").'"><i class="fa fa-file-text-o"></i>&nbsp;&nbsp;'.@trans("messages.View").'</a></li>
                        <li><a href="'.URL::to("admin/customer_promotion/delete/".$customer_promotion->id).'" class="delete-'.$customer_promotion->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $( document ).ready(function() {
                        $(".delete-'.$customer_promotion->id.'").on("click", function(){
                            return confirm("'.trans("messages.Are you sure want to delete?").'");
                        });
                    });
                </script>';
                return $html;
            }
        })
        ->addColumn('active_status', function ($customer_promotion) {
            if($customer_promotion->active_status == 0):
                $data = '<span class="label label-warning">'.trans("messages.Inactive").'</span>';
            elseif($customer_promotion->active_status == 1):
                $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
            else:
                $data = '<span class="label label-danger">'.trans("messages.Delete").'</span>';
            endif;
            return $data;
        })
        /*->addColumn('is_verified', function ($customer_promotion) {
            if($customer_promotion->is_verified == 0):
                $data = '<span class="label label-warning">'.trans("messages.Disabled").'</span>';
            elseif($customer_promotion->is_verified == 1):
                $data = '<span class="label label-success">'.trans("messages.Enabled").'</span>';
            endif;
            return $data;
        })*/
       ->rawColumns(['active_status','action'])

        ->make(true);
    }
    public function customerPromotion_create()
    {
    	//print_r('customerPromotion_create');exit();
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else{
            if(!hasTask('admin/customer_promotion/create')){
                return view('errors.404');
            }
            $settings = Settings::find(1);
            SEOMeta::setTitle('Create Customer Promotion - '.$this->site_name);
            SEOMeta::setDescription('Create Customer Promotion - '.$this->site_name);
            return view('admin.settings.customerPromotion.create')->with('settings', $settings);
        }
    }
    public function customerPromotion_edit($id)
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('admin/customer_promotion/edit')){
                return view('errors.404');
            }
            //Get driver details
            $customer_promotion = customer_promotion::find($id);
            if(!count($customer_promotion))
            {
                Session::flash('message', 'Invalid customerPromotion Details'); 
                return Redirect::to('admin/settings/customer_promotion');
            }
            $settings = Settings::find(1);
            SEOMeta::setTitle('Edit Customer Promotion - '.$this->site_name);
            SEOMeta::setDescription('Edit Customer Promotion - '.$this->site_name);
            return view('admin.settings.customerPromotion.edit')->with('settings', $settings)->with('data', $customer_promotion);
        }
    }
    public function customerPromotion_delete($id)
    {
    	if(!hasTask('admin/customer_promotion/delete'))
		{
			return view('errors.404');
		}
        $data = customer_promotion::find($id);
        if(!count($data))
        {
            Session::flash('message', 'Invalid Customer Promotion Details'); 
            return Redirect::to('admin/settings/customer_promotion');
        }
        if(file_exists(base_path().'/public/assets/admin/base/images/customerPromotion/'.$data->image) && $data->image != '')
        {
            unlink(base_path().'/public/assets/admin/base/images/customerPromotion/'.$data->image);
        }
        DB::table('customer_promotion')->where('id', '=', $id)->delete();
        $data->delete();
        Session::flash('message', trans('messages.Customer Promotion has been deleted successfully'));
        return Redirect::to('admin/settings/customer_promotion');
    }
 	public function customerPromotion_show($id)
    {
        if(!hasTask('admin/customerPromotion/view')){
            return view('errors.404');
        }
        $customer_promotion = customer_promotion::find($id);
        if(!count($customer_promotion)){
        
            Session::flash('message', 'Invalid customerPromotion Details'); 
            return Redirect::to('admin/settings/customer_promotion');
        }

        SEOMeta::setTitle('View Customer Promotion - '.$this->site_name);
        SEOMeta::setDescription('View Customer Promotion - '.$this->site_name);
       return view('admin.settings.customerPromotion.view')->with('data', $customer_promotion);
    }

    public function customerPromotion_store(Request $data)
    {
        if(!hasTask('create_customerPromotion'))
        {
            return view('errors.404');
        }
        $validation = Validator::make($data->all(), array(
            'promotion_name'     => 'required',
            'base_amount'        => 'required',
            'addition_promotion' => 'required',
            'grocery_wallet'     => 'required',
            'start_date'         => 'required|date',
            'end_date'           => 'required|date',
            'image'     		 => 'mimes:png,jpg,jpeg,bmp|max:2024',
        ));
        // process the validation
        if ($validation->fails())
        {
            return Redirect::back()->withErrors($validation)->withInput();
        }
        else {
         
            $customer_promotion      = new customer_promotion;
            $customer_promotion->promotion_name    = $_POST['promotion_name'];
            $customer_promotion->base_amount     = $_POST['base_amount'];
            $customer_promotion->addition_promotion= $_POST['addition_promotion'];
            $customer_promotion->grocery_wallet = $_POST['grocery_wallet'];
            $customer_promotion->active_status     = isset($_POST['active_status']);
            //$customer_promotion->is_verified       = isset($_POST['is_verified']);
            $customer_promotion->created_at     = date("Y-m-d H:i:s");
            $customer_promotion->updated_at     = date("Y-m-d H:i:s");
            $customer_promotion->start_date       = $_POST['start_date'];
            $customer_promotion->end_date         = $_POST['end_date'];
            $customer_promotion->save();
            if(isset($_FILES['coupon_image']['name']) && $_FILES['coupon_image']['name']!='')
                {
                    $imageName = $id.'.'.$data->file('image')->getClientOriginalExtension();
                    $data->file('image')->move(
                        base_path().'/public/assets/admin/base/images/customerPromotion/', $imageName
                    );
                    $destinationPath1 = url('/assets/admin/base/images/customerPromotion/'.$imageName);
                    Image::make( $destinationPath1 )->fit(300, 300)->save(base_path() .'/public/assets/admin/base/images/customerPromotion/'.$imageName)->destroy();
                    $customer_promotion->image = $imageName;
                    $customer_promotion->save();
                }
            // redirect
            Session::flash('message', trans('messages.Customer Promotion has been created successfully'));
            return Redirect::to('admin/settings/customer_promotion');
        }
    }

    public function update_customer_promotion(Request $data, $id)
    {
        if (!hasTask('admin/settings/customer_promotion')) {
			return view('errors.404');
		}
		$validation = Validator::make($data->all(), array(
            'promotion_name'     => 'required',
            'base_amount'        => 'required',
            'addition_promotion' => 'required',
            'grocery_wallet'     => 'required',
            'start_date'       	 => 'required|date',
            'end_date'         	 => 'required|date',
            'image'     		 => 'mimes:png,jpg,jpeg,bmp|max:2024',
        ));
        if ($validation->fails())
        {
            return Redirect::back()->withErrors($validation)->withInput();
        }
        else {
         
            $customer_promotion      = customer_promotion::find($id);
            $customer_promotion->promotion_name    = $_POST['promotion_name'];
            $customer_promotion->base_amount     = $_POST['base_amount'];
            $customer_promotion->addition_promotion= $_POST['addition_promotion'];
            $customer_promotion->grocery_wallet = $_POST['grocery_wallet'];
            $customer_promotion->active_status     = isset($_POST['active_status']);
            //$customer_promotion->is_verified       = isset($_POST['is_verified']);
            $customer_promotion->created_at     = date("Y-m-d H:i:s");
            $customer_promotion->updated_at     = date("Y-m-d H:i:s");
            $customer_promotion->start_date       = $_POST['start_date'];
            $customer_promotion->end_date         = $_POST['end_date'];
            $customer_promotion->save();
                if(isset($_FILES['image']['name']) && $_FILES['image']['name']!='')
                {
                    $imageName = $id.'.'.$data->file('image')->getClientOriginalExtension();
                    $data->file('image')->fit(300, 300)->move(
                        base_path().'/public/assets/admin/base/images/customerPromotion/', $imageName
                    );

                    $destinationPath1 = url('/assets/admin/base/images/customerPromotion/'.$imageName);
                    // Image::make( $destinationPath1 )->fit(720, 360)->save(base_path() .'/public/assets/admin/base/images/customerPromotion/'.$imageName)->destroy();
                    $customer_promotion->image = $imageName;
                    $customer_promotion->save();
                }
          
			Session::flash('message', trans('messages.customer Promotion has been successfully updated'));
			return Redirect::to('admin/settings/customer_promotion');
		}
    }
     /** telr paymentgateway**/

    public function telr_walletpage()
    {
    	return view('admin.payemntgateway');

    }
    //After the payment success can u please send back the order-ref in the return_auth (url ).is that possible?
    public function paymentgatewaychcek(Request $data)
    {	


    	$telrManager = new \TelrGateway\TelrManager();

		$billingParams = [
		        'first_name' => 'Moustafa Gouda',
		        'sur_name' => 'Bafi',
		        'address_1' => 'Gnaklis',
		        'address_2' => 'Gnaklis 2',
		        'city' => 'Alexandria',
		        'region' => 'San Stefano',
		        'zip' => '11231',
		        'country' => 'EG',
		        'email' => 'example@company.com',
		    ];

		return $telrManager->pay('11231', '150', 'AWM-123-2-1', $billingParams)->redirect();

    	/*$validation = Validator::make($data->all(), array(
			'customer_id' => 'required|integer',
			'promotion_type' => 'required|integer',
		));
		// process the validation
		if ($validation->fails()) {
			return Redirect::back()->withErrors($validation)->withInput();
		} else {
	    	$post =$data->all();
	    	$paymentgateway= getPaymentDetails(30);
	    	$details= getCustPromotiondetails($post['promotion_type']);
	    	$user_id = isset($post['customer_id'])?$post['customer_id']:0;
	    	echo"<pre>";print_r($details);exit();
	    	$cart_id = rand(1000, 9999);
	    	$base_amount = isset($details->base_amount)?$details->base_amount:0;
	    	$addition_promotion = isset($details->addition_promotion)?$details->addition_promotion:0;
	    	$auth_key = isset($paymentgateway->merchant_secret_key)?$paymentgateway->merchant_secret_key:'DRsmq^MG9m@fjX3z';
	    	$store_id = isset($paymentgateway->merchant_key)?$paymentgateway->merchant_key:'21961';
	    	$amount = $base_amount + $addition_promotion;
	    	$currency_code = 'AED';
	    	$desc = "customer wallet money added";

			$params = array(
				'ivp_method'=>'create',
				'ivp_store'=>$store_id,
				'ivp_authkey'=>$auth_key,
				'ivp_cart'=>$cart_id,
				'ivp_test'=>'1',
				'ivp_amount'=>$amount,
				'ivp_currency'=>$currency_code,
				'ivp_desc'=>$desc,
				'return_auth'=>'http://192.168.0.202:8000/admin/payment_sucess',
				'return_can'=>'http://192.168.0.202:8000/admin/payment_cancel',
				'return_decl'=>'http://192.168.0.202:8000/admin/payment_declain'
			);
	    	//echo"<pre>";print_r($params);exit();

			/*$params = array(
				'ivp_method'=>'create',
				'ivp_store'=>'21961',
				'ivp_authkey'=>'DRsmq^MG9m@fjX3z',
				'ivp_cart'=>'1322',
				'ivp_test'=>'1',
				'ivp_amount'=>'1.00',
				'ivp_currency'=>'AED',
				'ivp_desc'=>'Testing',
				'return_auth'=>'http://192.168.0.202:8000/admin/payment_sucess',
				'return_can'=>'http://192.168.0.202:8000/admin/payment_cancel',
				'return_decl'=>'http://192.168.0.202:8000/admin/payment_declain'
			);/
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://secure.telr.com/gateway/order.json");
			curl_setopt($ch, CURLOPT_POST, count($params));
			curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
			$results = curl_exec($ch);
			curl_close($ch);
			$results = json_decode($results,true);
			isset($results['error'])?$error=1:$error =0;
			if($error !=1)
			{
				$ref= trim($results['order']['ref']);
				$url= trim($results['order']['url']);
				$terms      = new telr_payment;
	            $terms->order_ref =$results['order']['ref'];
	            $terms->wallet_unique_id =$cart_id;
	            $terms->customer_id =$user_id;
	            $terms->created_at =date("Y-m-d H:i:s");
	            $terms->updated_at = date("Y-m-d H:i:s");
	            $terms->save();
				if (empty($ref) || empty($url)) {
					echo"<pre>";print_r("fails");exit();
				}else
				{
					return Redirect::to($url);
				}
			}else{
				print_r("sdcfsdfsdf");exit();
			}
		}*/
    }
    public function payment_sucess(Request $data)
    {

    	print_r( $data->all());exit();
    	/*$promotion = telr_payment::find(2);
    	//print_r($promotion->order_ref);exit();
    	$params = array(
				'ivp_method'=>'check',
				'ivp_store'=>'21961',
				'ivp_authkey'=>'DRsmq^MG9m@fjX3z',
				'order_ref'=>$promotion->order_ref,
				'ivp_test'=>'1',
			);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "https://secure.telr.com/gateway/order.json");
				curl_setopt($ch, CURLOPT_POST, count($params));
				curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
				$results = curl_exec($ch);
				curl_close($ch);
				$results = json_decode($results,true);
				echo"<pre>";print_r($results);exit();*/
				
    } 
    public function payment_cancel()
    {
    	print_r("payment_cancel");exit();
    }
    public function payment_declain()
    {
    	print_r("payment_declain");exit();
    } 

    public function offer()
    {
    	//print_r('customerPromotion_create');exit();
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else{
            if(!hasTask('admin/customer_promotion/create')){
                return view('errors.404');
            }
            $settings = Settings::find(1);
            SEOMeta::setTitle('Create Offer - '.$this->site_name);
            SEOMeta::setDescription('Create Offer - '.$this->site_name);
            return view('admin.settings.Offer.create')->with('settings', $settings);
        }
    }
    public function create_offer(Request $data)
    {

    	print_r($data);exit();
        if(!hasTask('create_customerPromotion'))
        {
            return view('errors.404');
        }
        $validation = Validator::make($data->all(), array(
            'promotion_name'     => 'required',
            'base_amount'        => 'required',
            'addition_promotion' => 'required',
            'grocery_wallet'     => 'required',
            'start_date'         => 'required|date',
            'end_date'           => 'required|date',
            'image'     		 => 'mimes:png,jpg,jpeg,bmp|max:2024',
        ));
        // process the validation
        if ($validation->fails())
        {
            return Redirect::back()->withErrors($validation)->withInput();
        }
        else {
         
            $customer_promotion      = new customer_promotion;
            $customer_promotion->promotion_name    = $_POST['promotion_name'];
            $customer_promotion->base_amount     = $_POST['base_amount'];
            $customer_promotion->addition_promotion= $_POST['addition_promotion'];
            $customer_promotion->grocery_wallet = $_POST['grocery_wallet'];
            $customer_promotion->active_status     = isset($_POST['active_status']);
            //$customer_promotion->is_verified       = isset($_POST['is_verified']);
            $customer_promotion->created_at     = date("Y-m-d H:i:s");
            $customer_promotion->updated_at     = date("Y-m-d H:i:s");
            $customer_promotion->start_date       = $_POST['start_date'];
            $customer_promotion->end_date         = $_POST['end_date'];
            $customer_promotion->save();
            if(isset($_FILES['coupon_image']['name']) && $_FILES['coupon_image']['name']!='')
                {
                    $imageName = $id.'.'.$data->file('image')->getClientOriginalExtension();
                    $data->file('image')->move(
                        base_path().'/public/assets/admin/base/images/customerPromotion/', $imageName
                    );
                    $destinationPath1 = url('/assets/admin/base/images/customerPromotion/'.$imageName);
                    Image::make( $destinationPath1 )->fit(300, 300)->save(base_path() .'/public/assets/admin/base/images/customerPromotion/'.$imageName)->destroy();
                    $customer_promotion->image = $imageName;
                    $customer_promotion->save();
                }
            // redirect
            Session::flash('message', trans('messages.Customer Promotion has been created successfully'));
            return Redirect::to('admin/settings/customer_promotion');
        }
    }

}
