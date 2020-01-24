<?php $_taskslist = array();
/**
 * Contains the most low-level helpers methods in Kohana:
 *
 * - Environment initialization
 * - Locating files within the cascading filesystem
 * - Auto-loading and transparent extension of classes
 * - Variable and path debugging
 *
 * @package    Laravel
 * @category   Base
 * @copyright  Copyright © Taylor Otwell
 * @license    https://laravel.com/license
 */

	use App\Model\users;
	use App\Model\drivers;


	const USERS_FORGOT_PASSWORD_EMAIL_TEMPLATE = 6;
	const USER_CHANGE_PASSWORD_EMAIL_TEMPLATE = 13;
	const DRIVER_SIGNUP_EMAIL_TEMPLATE = 9;
	const DRIVER_WELCOME_EMAIL_TEMPLATE = 10;
	const DRIVER_ORDER_RESPONSE_TEMPLATE = 25;
	const ORDER_STATUS_UPDATE_USER = 18;
	const DRIVER_ORDER__DELIVERED_RESPONSE_ADMIN_TEMPLATE = 27;

/** get current Language **/
function getCurrentLang() {
	$currentlanguages = DB::table('languages')->where('status', 1)->where('language_code', App::getLocale())->get();
	$current_language_id = '';
	if (count($currentlanguages) > 0) {
		$current_language_id = $currentlanguages[0]->id;
	}
	return $current_language_id;
}
/** get current Language **/
function getAdminCurrentLang() {
	/** $currentlanguages = DB::table('languages')->where('status', 1)->where('language_code',App::getLocale())->get();
	$current_language_id='';
	if(count($currentlanguages)>0){
	$current_language_id = $currentlanguages[0]->id;
	}
	return $current_language_id;
	$current_language_id=1;
	if(App::getLocale()=='en'){
	$current_language_id = 1;
	}
	if(App::getLocale()=='ar'){
	$current_language_id = 2;
	}*/
	return 1;
}
//Get balance details for commision and amount
function getBalanceData($user_id, $type = 0) {
	if ($type == 1) {
		//Vendor
		$vdata = DB::table('vendors')->select('current_balance')->where('id', $user_id)->get();
		$data = array('vendor_balance' => $vdata[0]->current_balance, 'admin_balance' => 0);
	} else {
		$vdata = DB::table('vendors')->sum('current_balance');
		$adata = DB::table('users')->select('current_balance')->where('id', 1)->get();
		$data = array('vendor_balance' => $vdata, 'admin_balance' => $adata[0]->current_balance);
	}
	return $data;
}
function getNotificationsList($user_id) {
	$notifications = DB::table('notifications')
		->select('notifications.id', 'notifications.order_id', 'notifications.message', 'notifications.created_date', 'notifications.read_status', 'users.name', 'users.image')
		->leftJoin('users', 'users.id', '=', 'notifications.customer_id')
		->where('read_status', 0);
	if ($user_id != 1) {
		$notifications = $notifications->where('vendor_id', $user_id);
	}
	$notifications = $notifications->orderBy('created_date', 'desc')->get();
	return $notifications;
}
/** get country list **/
function getCountryLists() {
	//print_r(getCurrentLang());exit;
	$country_query = '"countries_infos"."language_id" = (case when (select count(*) as totalcount from countries_infos where countries_infos.language_id = ' . getCurrentLang() . ' and countries.id = countries_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
	$countries = DB::table('countries')
		->select('countries.id', 'countries_infos.country_name')
		->leftJoin('countries_infos', 'countries_infos.id', '=', 'countries.id')
		->whereRaw($country_query)
		->where('country_status', 1)
		->orderBy('country_name', 'asc')
		->get();
	//		print_r($countries);exit;

	$country_list = array();
	if (count($countries) > 0) {
		$country_list = $countries;
	}
	return $country_list;
}

/** get category list **/
function getCategoryLists($category_type) {
	//Get the categories data
	$category_query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = ' . getCurrentLang() . ' and categories.id = categories_infos.category_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
	$categories = DB::table('categories')
		->select('categories.id', 'categories.image', 'categories.category_white_image', 'categories_infos.category_name', 'categories.url_key')
		->leftJoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
		->whereRaw($category_query)
		->where('category_status', 1)
	//->where('parent_id',  0) //getting parent categories only
		->where('category_type', $category_type)
		->orderBy('categories.sort_order', 'asc')
		->get();

	$categories_list = array();
	if (count($categories) > 0) {
		$categories_list = $categories;
	}
	return $categories_list;
}

function getMainCategoryLists($category_id) {

	$categories_list = array();
	if ($category_id != "") {
		//Get the categories data
		$category_query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = 1 and categories.id = categories_infos.category_id) > 0 THEN 1 ELSE 1 END)';
		$categories = DB::table('categories')
			->select('categories.id', 'categories_infos.category_name', 'categories.url_key')
			->leftJoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
			->whereRaw($category_query)
			->where('category_status', 1)
			->where('category_level', '=', 2)
			->where('id', '<>', $category_id)
			->where('parent_id', $category_id) //getting parent categories only
			->orderBy('categories_infos.category_name', 'asc')
			->get();
		//print_r($categories);exit;
		if (count($categories) > 0) {
			$categories_list = $categories;
		}
	}
	return $categories_list;
}

function getMainCategoryLists1() {

	$categories_list = array();
	//Get the categories data
	$category_query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = ' . getCurrentLang() . ' and categories.id = categories_infos.category_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
	$categories = DB::table('categories')
		->select('categories.id', 'categories_infos.category_name', 'categories.url_key')
		->leftJoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
		->whereRaw($category_query)
		->where('category_status', 1)
		->where('category_level', '=', 2)
		->where('category_type', '=', 1)
	//->where('id','<>',$category_id)
	//->where('parent_id', $category_id) //getting parent categories only
		->orderBy('categories_infos.category_name', 'asc')
		->get();
	//print_r($categories);exit;
	if (count($categories) > 0) {
		$categories_list = $categories;
	}

	return $categories_list;
}

function getProductMainCategoryLists($category_id) {

	$categories_list = array();
	if ($category_id != "") {
		$ids = implode(',', $category_id);
		//Get the categories data
		$category_query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = 1 and categories.id = categories_infos.category_id) > 0 THEN 1 ELSE 1 END)';
		$categories = DB::table('categories')
			->select('categories.id', 'categories_infos.category_name', 'categories.url_key')
			->leftJoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
			->whereRaw($category_query)
			->where('category_status', 1)
			->where('category_type', '=', 1)
			->where('category_level', '=', 2)
		//->where('id','<>',$category_id)
			->whereRaw('parent_id IN(' . $ids . ')') //getting parent categories only
			->orderBy('categories_infos.category_name', 'asc')
			->get();
		//print_r($categories);exit;
		if (count($categories) > 0) {
			$categories_list = $categories;
		}
	}
	return $categories_list;
}

/** get category details by category id**/
function getCategoryListsById($category_id) {
	//Get the categories data
	$category_query = '"categories_infos"."language_id" = (case when (select count(category_id) as totalcount from categories_infos where categories_infos.language_id = ' . getCurrentLang() . ' and categories.id = categories_infos.category_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
	$categories = DB::table('categories')
		->select('categories.id', 'categories_infos.category_name', 'categories.url_key')
		->leftJoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
		->whereRaw($category_query)
		->where('categories.id', $category_id)
		->first();
	//$categories_list = array();

	//print_r()
	if (count($categories) > 0) {
		$categories_list = $categories;
		return $categories_list;
	}
	//return $categories_list;
}
/** get category list **/
function getSubCategoryLists($category_type, $parent_id, $category_url = '') {
	//echo $parent_id;exit;
	//Get the categories data
	$category_query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = ' . getCurrentLang() . ' and categories.id = categories_infos.category_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
	$categories = DB::table('categories')
		->select('categories.id', 'categories.url_key', 'categories_infos.category_name')
		->leftJoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
		->whereRaw($category_query)
		->where('category_status', 1);
	if (!empty($parent_id)) {
		$categories = $categories->where('parent_id', $parent_id); //getting parent categories only
	}
	if (!empty($category_url)) {
		$categories = $categories->where('categories.url_key', $category_url); //getting parent categories only
	}
	$categories = $categories->where('category_type', $category_type)
		->where('category_level', 2)
		->orderBy('category_name', 'asc')
		->get();
	$categories_list = array();
	if (count($categories) > 0) {
		$categories_list = $categories;
	}
	return $categories_list;
}

/** get category list **/
function getSubCategoryLists1($category_type, $parent_id, $category_url = '', $language = '') {

	//Get the categories data
	//$category_query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = 1 and categories.id = categories_infos.category_id) > 0 THEN 1 ELSE 1 END)';

	if ($language) {

		$category_query = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';

	} else {
		$category_query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = ' . getCurrentLang() . ' and categories.id = categories_infos.category_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
	}

	$categories = DB::table('categories')
		->select('categories.id', 'categories.url_key', 'categories_infos.category_name')
		->leftJoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
		->whereRaw($category_query)
		->where('category_status', 1);
	if (!empty($parent_id)) {
		$categories = $categories->where('parent_id', $parent_id); //getting parent categories only
	}
	if (!empty($category_url)) {
		$categories = $categories->where('categories.url_key', $category_url); //getting parent categories only
	}
	$categories = $categories->where('category_type', $category_type)
		->orderBy('category_name', 'asc')
		->get();
	$categories_list = array();
	if (count($categories) > 0) {
		$categories_list = $categories;
	}
	return $categories_list;
}

/** get category list **/
function getSubCategoryListsupdated($category_type, $parent_id, $category_url = '', $language = '', $head_category = '') {

	//echo $parent_id;exit;
	//Get the categories data
	//$category_query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = 1 and categories.id = categories_infos.category_id) > 0 THEN 1 ELSE 1 END)';
	if ($language) {

		$category_query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = ' . $language . ' and categories.id = categories_infos.category_id) > 0 THEN ' . $language . ' ELSE 1 END)';

	} else {

		$category_query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = ' . getCurrentLang() . ' and categories.id = categories_infos.category_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
	}

	$categories = DB::table('categories')
		->select('categories.id', 'categories.url_key', 'categories_infos.category_name', 'head_category_ids as parent_id')
		->leftJoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
		->whereRaw($category_query)
		->where('category_status', 1);
	if (!empty($parent_id)) {
		$categories = $categories->where('head_category_ids', $parent_id); //getting parent categories only
		$categories = $categories->where('category_level', 3);
	}
	if (!empty($head_category)) {
		$categories = $categories->where('parent_id', $head_category); //getting parent categories only
	}
	if (!empty($category_url)) {
		$categories = $categories->where('categories.url_key', $category_url); //getting parent categories only
	}
	$categories = $categories->where('category_type', $category_type)
		->orderBy('category_name', 'asc')
		->get();
	$categories_list = array();
	if (count($categories) > 0) {
		$categories_list = $categories;
	}
	return $categories_list;
}

/** get city list **/
function getCityList($country_id) {
	//Get the cities data
	$city_query = '"cities_infos"."language_id" = (case when (select count(*) as totalcount from cities_infos where cities_infos.language_id = ' . getCurrentLang() . ' and cities.id = cities_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
	$cities = DB::table('cities')
		->select('cities.id', 'cities_infos.city_name')
		->leftJoin('cities_infos', 'cities_infos.id', '=', 'cities.id')
		->leftJoin('countries', 'countries.id', '=', 'cities.country_id')
		->whereRaw($city_query)
		->where('active_status', 'A')
		->where('default_status', 1)
		->where('countries.id', $country_id)
		->orderBy('city_name', 'asc')
		->get();
	$cities_list = array();
	if (count($cities) > 0) {
		$cities_list = $cities;
	}
	return $cities_list;
}

/** get Location list **/
function getLocationList($country_id, $city_id) {
	//Get the location areas data
	$locations_query = '"zones_infos"."language_id" = (case when (select count(*) as totalcount from zones_infos where zones_infos.language_id = ' . getCurrentLang() . ' and zones.id = zones_infos.zone_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
	$locations = DB::table('zones')
		->select('zones.id', 'zones_infos.zone_name')
		->leftJoin('zones_infos', 'zones_infos.zone_id', '=', 'zones.id')
		->leftJoin('countries', 'countries.id', '=', 'zones.country_id')
		->leftJoin('cities', 'cities.id', '=', 'zones.city_id')
		->whereRaw($locations_query)
		->where('zones_status', 1)
		->where('countries.id', $country_id)
		->where('cities.id', $city_id)
		->orderBy('zone_name', 'asc')
		->get();
	$locations_list = array();
	if (count($locations) > 0) {
		$locations_list = $locations;
	}
	return $locations_list;
}

/** get language list **/
function getLanguageList() {
	$languages = DB::table('languages')->where('status', 1)->orderby("languages.id", "asc")->get();
	$languages_list = array();
	if (count($languages) > 0) {
		$languages_list = $languages;
	}
	return $languages_list;
}

/** get currency list **/
function getCurrencyList() {

	$language_id = getAdminCurrentLang();

	$query = '"currencies_infos"."language_id" = (case when (select count(*) as totalcount from currencies_infos where currencies_infos.language_id = ' . $language_id . ' and currencies.id = currencies_infos.currency_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
	$currencies = DB::table('currencies')
		->select('currencies.*', 'currencies_infos.*')
		->join('currencies_infos', 'currencies_infos.currency_id', '=', 'currencies.id')
		->where("currencies.active_status", "=", "A")
		->where("currencies.default_status", "=", 1)
		->whereRaw($query)
		->orderBy('id', 'asc')
		->whereRaw($query)
		->get();
	//print_r($currencies);exit;
	$currencies_list = array();
	if (count($currencies) > 0) {
		$currencies_list = $currencies;
	}
	return $currencies_list;
}
/** get weight class list **/
function getWeightClass() {
	$query = '"weight_classes_infos"."lang_id" = (case when (select count(*) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . getCurrentLang() . ' and weight_classes.id = weight_classes_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
	$weight_classes = DB::table('weight_classes')
		->select('weight_classes.*', 'weight_classes_infos.*')
		->leftJoin('weight_classes_infos', 'weight_classes_infos.id', '=', 'weight_classes.id')
		->where('active_status', '=', 1)
		->whereRaw($query)
		->orderBy('title', 'asc')
		->get();
	$weight_class_list = array();
	if (count($weight_classes) > 0) {
		$weight_class_list = $weight_classes;
	}
	return $weight_class_list;
}

/** get generl settings configuration **/
function getAppConfig() {
	//$defaultconfigs = DB::table('settings')->select('settings.*')->get();
	$language = getCurrentLang();
	$squery = '"settings_infos"."language_id" = (case when (select count(settings_infos.language_id) as totalcount from settings_infos where settings_infos.language_id = ' . $language . ' and settings.id = settings_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
	$config_items = DB::table('settings')
		->select('settings.*', 'settings_infos.site_name')
		->whereRaw($squery)
		->leftJoin('settings_infos', 'settings_infos.id', '=', 'settings.id')
		->first();

	return $config_items;
}

/** get social media settings configuration **/
function getAppSocialConfig() {
	$defaultconfigs = DB::table('socialmediasettings')->select('socialmediasettings.*')->get();
	$config_items = array();
	if (count($defaultconfigs) > 0) {
		$config_items = $defaultconfigs[0];
	}
	return $config_items;
}

/** get email settings configuration **/
function getAppConfigEmail() {
	$defaultconfigs = DB::table('emailsettings')->select('emailsettings.*')->get();
	$config_items = array();
	if (count($defaultconfigs) > 0) {
		$config_items = $defaultconfigs[0];
	}
	return $config_items;
}

/** get current language code **/
function getCurrentLangCode() {
	$currentlanguagecodes = DB::table('languages')->where('status', 1)->where('id', getAppConfig()->default_language)->get();
	$current_language_code = '';
	if (count($currentlanguagecodes) > 0) {
		$current_language_code = $currentlanguagecodes[0]->language_code;
	}
	return $current_language_code;
}

/** get category types **/
function getCategoryTypes() {
	$types = array(1 => 'Product', 2 => 'Vendor', 3 => 'Blog', 5 => 'Coupon');
	return $types;
}

/** get image resize  settings **/

function getImageResize($param = '') {
	if (!$param) {
		return false;
	}
	$settings = DB::table('imageresizesettings')->select('*')->get();
	$image_items = array();
	if (count($settings) > 0) {
		foreach ($settings as $key => $value) {
			switch ($param) {
			case 'LOGO':
				if ($value->type == 1) {
					$image_items['WIDTH'] = $value->list_width;
					$image_items['HEIGHT'] = $value->list_height;
				}
				break;
			case 'FAVICON':
				if ($value->type == 1) {
					$image_items['WIDTH'] = $value->detail_width;
					$image_items['HEIGHT'] = $value->detail_height;
				}
				break;
			case 'CATEGORY':
				if ($value->type == 1) {
					$image_items['WIDTH'] = $value->thumb_width;
					$image_items['HEIGHT'] = $value->thumb_height;
				}
				break;
			case 'STORE':
				if ($value->type == 2) {
					$image_items['LIST_WIDTH'] = $value->list_width;
					$image_items['LIST_HEIGHT'] = $value->list_height;
					$image_items['DETAIL_WIDTH'] = $value->detail_width;
					$image_items['DETAIL_HEIGHT'] = $value->detail_height;
					$image_items['THUMB_WIDTH'] = $value->thumb_width;
					$image_items['THUMB_HEIGHT'] = $value->thumb_height;
				}
				break;
			case 'PRODUCT':
				if ($value->type == 3) {
					$image_items['LIST_WIDTH'] = $value->list_width;
					$image_items['LIST_HEIGHT'] = $value->list_height;
					$image_items['DETAIL_WIDTH'] = $value->detail_width;
					$image_items['DETAIL_HEIGHT'] = $value->detail_height;
					$image_items['THUMB_WIDTH'] = $value->thumb_width;
					$image_items['THUMB_HEIGHT'] = $value->thumb_height;
				}
				break;
			case 'BANNER':
				if ($value->type == 4) {
					$image_items['LIST_WIDTH'] = $value->list_width;
					$image_items['LIST_HEIGHT'] = $value->list_height;
				}
				break;
			case 'VENDOR':
				if ($value->type == 5) {
					$image_items['LIST_WIDTH'] = $value->list_width;
					$image_items['LIST_HEIGHT'] = $value->list_height;
					$image_items['DETAIL_WIDTH'] = $value->detail_width;
					$image_items['DETAIL_HEIGHT'] = $value->detail_height;
					$image_items['THUMB_WIDTH'] = $value->thumb_width;
					$image_items['THUMB_HEIGHT'] = $value->thumb_height;
				}
				break;
			}
		}
	}
	return $image_items;
}

/** Add user activity log info **/

function userlog($message, $userid = '', $activity_type = '', $severity = 1, $device = '') {
	return addActivity($message, $activity_type, $severity, $userid, '', $device);
}

function addActivity($message, $activity_type = '', $severity = 1, $userid = '', $ip = '', $device = '') {

	$date = date("Y-m-d H:i:s");
	if (!$userid) {
		return false;
	}
	$ip = $ip ? $ip : Request::ip();
	//$browser = get_browser(null, true);
	$browser = '';
	$device = $device ? $device : isset($browser['browser']) ? $browser['browser'] : '';
	$data = array(
		'message' => $message,
		'date' => $date,
		'ip_' => $ip,
		'device' => $device,
		'user_id' => $userid,
		'activity_type' => $activity_type,
	);
	try {
		DB::table('user_activity_log')->insert($data);
	} catch (Exception $e) {
		print_r($e);exit;
	}
}

/** Compare old data and new data its return modified data values only - formate array() **/
function logcompare($olddata, $newdata, $type = false, $arr = array(), $unset = array()) {
	if (!$olddata) {
		$olddata = array();
	}
	$diff = array_diff($olddata, $newdata);
	if (count($diff)) {
		$final_data = array();
		foreach ($diff as $key => $val) {
			if (isset($newdata[$key])) {
				$final_data[$key][] = $val;
				$final_data[$key][] = $newdata[$key];
			}
		}
		$text = "";
		foreach ($final_data as $key1 => $data) {
			if (in_array($key1, $unset)) {continue;}
			$text .= $key1 . ' - ' . '<b>' . strip_tags($data[0]) . '</b>' . ' -> ' . '<b>' . strip_tags($data[1]) . '</b> ';
		}
		if ($type) {
			return $diff;
		} else {
			return $text;
		}
	}

}

/** Its return 3 days ago , 1 minute age this type of result **/

function nicetime($date) {
	if (empty($date)) {
		return "";
	}
	$periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
	$lengths = array("60", "60", "24", "7", "4.35", "12", "10");

	$now = time();
	$unix_date = strtotime($date);

	// check validity of date
	if (empty($unix_date)) {
		return "Bad date";
	}

	// is it future date or past date
	if ($now > $unix_date) {
		$difference = $now - $unix_date;
		$tense = trans('messages.ago');

	} else {
		$difference = $unix_date - $now;
		$tense = trans('messages.from now');
	}

	for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
		$difference /= $lengths[$j];
	}

	$difference = round($difference);

	if ($difference != 1) {
		$periods[$j] .= "s";
	}
	return "$difference $periods[$j] {$tense}";
}

/**
 * Calculates timezone offset
 *
 * @param  string $timezone
 * @return int offset between timezone and gmt
 */
function calculateOffset($timezone = null) {
	$result = true;
	$offset = 0;
	if (!is_null($timezone)) {
		$oldzone = @date_default_timezone_get();
		$result = date_default_timezone_set($timezone);
	}

	if ($result === true) {
		$offset = gmmktime(0, 0, 0, 1, 2, 1970) - mktime(0, 0, 0, 1, 2, 1970);
	}

	if (!is_null($timezone)) {
		date_default_timezone_set($oldzone);
	}

	return $offset;
}

/**
 * Forms GMT date
 *
 * @param  string $format
 * @param  int|string $input date in current timezone
 * @return string
 */
function gmtDate($format = null, $input = null) {
	if (is_null($format)) {
		$format = 'Y-m-d H:i:s';
	}

	$date = $this->gmtTimestamp($input);

	if ($date === false) {
		return false;
	}
	date_default_timezone_set(Config::get('app.timezone'));
	$result = date($format, $date);
	return $result;
}

/**
 * Forms GMT timestamp
 *
 * @param  int|string $input date in current timezone
 * @return int
 */
function gmtTimestamp($input = null) {
	if (is_null($input)) {
		return gmdate('U');
	} else if (is_numeric($input)) {
		$result = $input;
	} else {
		$result = strtotime($input);
	}

	if ($result === false) {
		// strtotime() unable to parse string (it's not a date or has incorrect format)
		return false;
	}

	$timestamp = time() + date("Z");

	return $timestamp;

}

/**
 * Get current timezone offset in seconds/minutes/hours
 *
 * @param  string $type
 * @return int
 */
function getGmtOffset($type = 'seconds') {
	$result = $this->_offset;
	switch ($type) {
	case 'seconds':
	default:
		break;

	case 'minutes':
		$result = $result / 60;
		break;

	case 'hours':
		$result = $result / 60 / 60;
		break;
	}
	return $result;
}

function is_validdate($date) {
	if (DateTime::createFromFormat('Y-m-d', $date) != false || $date != '0000-00-00 00:00:00') {
		$value = date("Y-m-d H:i:s", strtotime($date));
	} else {
		$value = '0000-00-00 00:00:00';
	}
	return $value;
}

function timeAgo($timestamp) {
	$datetime1 = new DateTime("now");
	$datetime2 = date_create($timestamp);
	$diff = date_diff($datetime1, $datetime2);
	$timemsg = '';
	if ($diff->y > 0) {
		$timemsg = $diff->y . ' year' . ($diff->y > 1 ? "s" : '');
	} else if ($diff->m > 0) {
		$timemsg = $diff->m . ' month' . ($diff->m > 1 ? "s" : '');
	} else if ($diff->d > 0) {
		$timemsg = $diff->d . ' day' . ($diff->d > 1 ? "s" : '');
	} else if ($diff->h > 0) {
		$timemsg = $diff->h . ' hour' . ($diff->h > 1 ? "s" : '');
	} else if ($diff->i > 0) {
		$timemsg = $diff->i . ' minute' . ($diff->i > 1 ? "s" : '');
	} else if ($diff->s > 0) {
		$timemsg = $diff->s . ' second' . ($diff->s > 1 ? "s" : '');
	} else {
		$timemsg = 'a few second';
	}

	$timemsg = $timemsg . ' ago';
	return $timemsg;
}

/**
 * Get email  template subject types
 *
 * @param
 * @return array
 */
function getSubjectType() {
	return array(
		'user_assignment' => 'User Assignment',
		'order_receipt' => 'Order Receipt',
		'order_pickup' => 'Order Pickup',
		'delivery_confirmation' => 'Delivery Confirmation',
		'payment_receipt' => 'Payment Receipt',
		'payment_transfer' => 'Payment Transfer',
		'order_cancellation' => 'Order Cancellation',
		'goods_return' => 'Goods Return',
		'requirement_post' => 'Requirement Post',
		'product_review' => 'Product Review',
		'people_review' => 'People Review',
		'place_review' => 'Place Review',
		'product_service_add_confirmation' => 'Product / Service Add Confirmation',
		'new_product_service' => 'New Product / Service',
		'mail' => 'Mail',
		'subscription' => 'Subscription',
		'review_report_abuse' => 'Review Report Abuse',
		'item_cancellation' => 'Item Cancellation',
		'return_request' => 'Return Request',
	);
}

/**
 * Get all email template
 *
 * @param
 * @return array object
 */
function getTemplates() {
	$templates = DB::table('email_templates')
		->select('email_templates.*')
		->get();
	return $templates;

}

/**
 * Get user types
 *
 * @param
 * @return array
 */
function getUserTypes() {
	$types = array(3 => 'Website users', 2 => 'Role User (Moderator)');
	return $types;
}

/**
 * smtp email send
 *
 * @param
 * @return
 */

function smtp($from = "", $from_name = "", $receiver = array(), $subject = "", $message = array(), $file = "", $attachment = array(), $reply_to = '') {
	$email_config = getAppConfigEmail();
	$smtp = $email_config->smtp_enable;
	if ($smtp) {
		require_once base_path() . '/includes/mail/class.phpmailer.php';
		$mail = new PHPMailer(TRUE);
		$mail->IsSMTP();
		try {
			$content = _TemplateResponse($message, $file);
			$mail->Host = "mail.yourdomain.com";
			$mail->SMTPDebug = 2;
			$mail->SMTPAuth = TRUE;
			$mail->SMTPSecure = $email_config->smtp_encryption;
			$mail->Host = $email_config->smtp_host_name;
			$mail->Port = $email_config->smtp_port;
			$mail->Username = $email_config->smtp_username;
			$mail->Password = $email_config->smtp_password;
			if ($reply_to != '') {
				$mail->AddReplyTo($reply_to);
				//~ $mail->addCC('saran@nextbrainitech.com');
				//~ $mail->addCC('chandru@nextbrainitech.com');
			} else {
				$mail->AddReplyTo($from);
			}

			if (is_array($receiver)) {
				call_user_func_array(array($mail, "addAddress"), $receiver);
				foreach ($receiver as $f) {
					$mail->addAddress($f);
				}
			} else {
				$mail->addAddress($receiver);
			}
			$mail->SetFrom($from, $from_name);
			$message = array_merge($message, _TemplateDefaultResponse());
			$subject = parseTemplate($subject, $message);
			$mail->Subject = $subject;
			$mail->MsgHTML($content);
			if (!empty($attachment)) {
				foreach ($attachment as $f) {
					$mail->AddAttachment($f);
				}
			}
			$mail->Send();
		} catch (phpmailerException $e) {
			echo $e->errorMessage();exit;
		} catch (Exception $e) {
			echo $e->getMessage();exit;
		}
		return;
	} else {
		try {
			if (count($attachment)) {
				$content = _TemplateResponse($message, $file);
				$message = array_merge($message, _TemplateDefaultResponse());
				$subject = parseTemplate($subject, $message);
				$fileatt = $attachment[0]; // Path to the file
				$filename = basename($fileatt);
				$file_size = filesize($fileatt);
				$content1 = chunk_split(base64_encode(file_get_contents($fileatt)));
				$uid = md5(uniqid(time()));
				$ecmessage = $content;
				$header = "From: " . $from_name . " <" . $from . ">\n";
				$header .= "MIME-Version: 1.0\n";
				$header .= "Content-Type: multipart/mixed; boundary=\"" . $uid . "\"\n\n";
				$emessage = "--" . $uid . "\n";
				$emessage .= "Content-type:text/html; charset=iso-8859-1\n";
				$emessage .= "Content-Transfer-Encoding: 7bit\n\n";
				$emessage .= $ecmessage . "\n\n";
				$emessage .= "--" . $uid . "\n";
				$emessage .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"\n"; // use different content types here
				$emessage .= "Content-Transfer-Encoding: base64\n";
				$emessage .= "Content-Disposition: attachment; filename=\"" . $filename . "\"\n\n";
				$emessage .= $content1 . "\n\n";
				$emessage .= "--" . $uid . "--";
				if (is_array($receiver)) {
					foreach ($receiver as $f) {
						//mail($f,$subject,$content,$headers);
						@mail($f, $subject, $emessage, $header);
					}
				} else {
					//mail($receiver,$subject,$content,$headers);
					@mail($receiver, $subject, $emessage, $header);
				}
			} else {
				$content = _TemplateResponse($message, $file);
				$message = array_merge($message, _TemplateDefaultResponse());
				$subject = parseTemplate($subject, $message);
				$headers = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .= 'From: <' . $from . '>' . "\r\n";
				if (is_array($receiver)) {
					foreach ($receiver as $f) {
						@mail($f, $subject, $content, $headers);
					}
				} else {
					@mail($receiver, $subject, $content, $headers);
				}
			}
		} catch (Exception $e) {
			print_r($e);exit;
		}
		return;
	}
}
/**
 * filter blackword from email content
 *
 * @param
 * @return
 */
function _TemplateResponse($message = array(), $file) {
	$message = array_merge($message, _TemplateDefaultResponse());
	/*if($file instanceof Model_Core_Email_Template) { */
	$mail_content = $file[0]->content;
	/* } */
	/* else {
		            $lang=App::getConfig('LOCALE');
		            $lang=explode("-",$lang);
		            $lang=isset($lang[0])?$lang[0]:'en';
		            $path=Kohana::find_file('i18n', $lang.'/email/'.$file,'html');
		            if(isset($path[0]) && !file_exists($path[0])){
		                throw new Kohana_Exception(__('Invalid Mail Template'));
		                return $this;
		            }
		            $mail_content = (string) file_get_contents($path[0]);
		        }
	*/
	$mail_content = filter($mail_content, $message);
	return $mail_content;
}
/**
 * Default Variables values set here
 *
 * @param
 * @return
 */
/** Default Variables**/
function _TemplateDefaultResponse() {
	$app_config = getAppConfig();
	$conf_email = getAppConfigEmail();
	$default = array('default' => array('SITE_NAME' => $app_config->site_name,
		"STOREADMIN_LOGO" => url('/assets/front/' . Session::get("general")->theme . '/images/logo/159_81/' . $app_config->logo),
		"ADMIN_LOGO" => url('/assets/front/' . Session::get("general")->theme . '/images/logo/159_81/' . $app_config->logo),
		"FRONT_LOGO" => url('/assets/front/' . Session::get("general")->theme . '/images/logo/159_81/' . $app_config->logo),
		"CONTACT_EMAIL" => $conf_email->contact_mail,
		"SUPPORT_EMAIL" => $conf_email->support_mail,
		"SITE_URL" => url('/'),
		"SITE_ASSETS_URL" => url('/assets/front/' . Session::get("general")->theme . '/images/'),
		"SITE_TWITTER_PAGE" => 'http://www.twitter.com',
		'SITE_FACEBOOK_PAGE' => 'http://www.facebook.com/',
	));
	return $default;
}

/** filter Content  removeBlackList**/

function filter($value, $templateVariables = array()) {
	$CONSTRUCTION_PATTERN = '/{{([a-z]{0,10})(.*?)}}/si';
	$templateVariables = array_merge($templateVariables, _TemplateDefaultResponse());
	if (preg_match_all($CONSTRUCTION_PATTERN, $value, $constructions, PREG_SET_ORDER)) {
		foreach ($constructions as $index => $construction) {
			$replacedValue = '';
			$callback = array($this, $construction[1] . 'Directive');
			if (!is_callable($callback)) {
				continue;
			}
			try {
				$replacedValue = call_user_func($callback, $construction);
			} catch (Exception $e) {
				throw $e;
			}
			$value = str_replace($construction[0], $replacedValue, $value);
		}
	}
	$values = parseTemplatereplace($value, $templateVariables);
	removeBlackList($value);
	return $values;
}

function parseTemplate($content = '', $templateVariables = array()) {
	preg_match_all('/\${(.*?)}/', $content, $matches);
	$matchings = $matches[1];
	$replaceset = array();
	foreach ($matchings as $match) {
		$data = explode(".", $match);
		if (!isset($templateVariables[$data[0]])) {
			continue;
		}
		$object = json_decode(json_encode($templateVariables[$data[0]]), FALSE);
		if (is_object($object)) {
			$replaceset['${' . $match . '}'] = $object->{$data[1]};
		} else if (is_string($templateVariables[$data[0]])) {
			$replaceset['${' . $match . '}'] = $templateVariables[$data[0]];
		}
	}
	$con = str_replace(array_keys($replaceset), array_values($replaceset), $content);
	return $con;
}

/** replace the variable to value  **/
function parseTemplatereplace(&$content = '', $templateVariables = array()) {
	preg_match_all('/\${(.*?)}/', $content, $matches);
	$matchings = $matches[1];
	$replaceset = array();
	foreach ($matchings as $match) {
		$data = explode(".", $match);
		if (!isset($templateVariables[$data[0]])) {
			continue;
		}
		$object = json_decode(json_encode($templateVariables[$data[0]]), FALSE);
		if (is_object($object)) {
			$replaceset['${' . $match . '}'] = $object->{$data[1]};
		} else if (is_string($templateVariables[$data[0]])) {
			$replaceset['${' . $match . '}'] = $templateVariables[$data[0]];
		}
	}
	$content = str_replace(array_keys($replaceset), array_values($replaceset), $content);
	return $content;
}
/** Filter Black Words - varible passing only not open string **/
function removeBlackList(&$text = '') {
	$blacklistwords = getAppConfig()->blocklist_words;
	$blacklistwords = explode(",", $blacklistwords);
	foreach ($blacklistwords as $bl) {
		$text = preg_replace('/\b(' . $bl . ')(s?)\b/u', '****', $text, 1);
	}
	return $text;
}
/*
 * timing schedule for doctors purpose
 */
function getDaysWeekArray() {
	return array('Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7);
}

/*
 * Get data here for outlet with days
 */
function getOpenTimings($v_id, $day_week) {
	$time_data = DB::table('opening_timings')
		->where("vendor_id", $v_id)
		->where("day_week", $day_week)
		->orderBy('id', 'asc')
		->get();
	$time_list = array();
	if (count($time_data) > 0) {
		$time_list = $time_data;
	}
	return $time_list;
}

/*
 * Get data here for outlet with days
 */
function getDeliveryTimings($v_id, $day_week) {
	$time_data = DB::table('delivery_timings')
		->where("vendor_id", $v_id)
		->where("day_week", $day_week)
		->orderBy('id', 'asc')
		->get();
	$time_list = array();
	if (count($time_data) > 0) {
		$time_list = $time_data;
	}
	return $time_list;
}
/** get vendors list **/
function getVendorLists() {
	//Get the vendors data
	$query = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = ' . getCurrentLang() . ' and vendors.id = vendors_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
	$data = DB::table('vendors')
		->leftJoin('vendors_infos', 'vendors_infos.id', '=', 'vendors.id')
		->select('vendors.id', 'vendors_infos.vendor_name')
		->whereRaw($query)
		->where('active_status', 1)
		->where('featured_vendor', 1)
		->orderBy('vendor_name', 'asc')
		->get();
	$data_list = array();
	if (count($data) > 0) {
		$data_list = $data;
	}
	return $data_list;
}
/* To coupon outlet list */
function getOutletLists($coupon_id) {
	//print_r($coupon_id);die;
	$data = DB::table('coupon_outlet')
		->select('outlet_id')
		->where('coupon_id', $coupon_id)
		->get();
	$data_list = array();
	if (count($data) > 0) {
		$data_list = $data;
	}
	return $data_list;
}

/** get vendors categories **/
function gethead_categories() {
	$data_list = array();
	$query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = ' . getCurrentLang() . ' and categories.id = categories_infos.category_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
	$data = DB::table('categories')
		->select('categories.id', 'categories_infos.category_name')
		->leftJoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
		->whereRaw($query)
		->where('category_status', 1)
		->where('category_type', 2)
		->orderBy('category_name', 'asc')
		->get();
	if (count($data) > 0) {
		$data_list = $data;
	}
	return $data_list;
}

/** get vendors list **/
function getStoreVendorLists($vendor_id) {
	//Get the vendors data
	$query = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = ' . getCurrentLang() . ' and vendors.id = vendors_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
	$data = DB::table('vendors')
		->leftJoin('vendors_infos', 'vendors_infos.id', '=', 'vendors.id')
		->select('vendors.id', 'vendors_infos.vendor_name')
		->whereRaw($query)
		->where('vendors.id', $vendor_id)
		->where('active_status', 1)
		->orderBy('vendor_name', 'asc')
		->get();
	$data_list = array();
	if (count($data) > 0) {
		$data_list = $data;
	}
	return $data_list;
}

/* Get weight classes data here */
function getVendorCategoryList($id) {
	$vdata = DB::table('vendors')->select('category_ids')->where('id', $id)->get();
	$data_list = array();
	if (count($vdata)) {
		$cids = explode(',', $vdata[0]->category_ids);
		//Get the categories data
		$query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = ' . getCurrentLang() . ' and categories.id = categories_infos.category_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
		$data = DB::table('categories')
			->select('categories.id', 'categories_infos.category_name', 'categories.url_key')
			->leftJoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
			->whereRaw($query)
			->where('category_status', 1)
			->where('category_type', 2)
			->whereIn('categories.id', $cids)
			->orderBy('category_name', 'asc')
			->get();
		if (count($data) > 0) {
			$data_list = $data;
		}
		return $data_list;
	} else {
		return $data_list;
	}
}

function getVendorsubCategoryLists($id) {
	$vdata = DB::table('vendors')->select('category_ids')->where('id', $id)->get();
	//print_r($vdata);//die;
	$data_list = array();
	if (count($vdata)) {
		$cids = explode(',', $vdata[0]->category_ids);

		//Get the categories data
		$query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = ' . getCurrentLang() . ' and categories.id = categories_infos.category_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
		$data = DB::table('categories')
			->select('categories.id', 'categories_infos.category_name', 'categories.url_key')
			->join('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
			->whereRaw($query)
			->where('category_status', '=', 1)
			->where('category_type', '=', 2)
			->whereIn('categories.id', $cids)
			->orderBy('category_name', 'asc')
			->get();
		if (count($data) > 0) {
			$data_list = $data;
		}
		return $data_list;
	} else {
		return $data_list;
	}
}

function getProduct_category_list($store_id, $cate_ids = '') {
	//echo $store_id;exit;
	$data_list = array();
	//Get the categories data
	$query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = ' . getCurrentLang() . ' and categories.id = categories_infos.category_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
	$data = DB::table('categories')
		->select('categories.id as category_id', 'categories.url_key', 'categories_infos.category_name', 'categories.image')
		->Join('products', 'products.category_id', '=', 'categories.id')
		->join('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
		->whereRaw($query)
		->where('outlet_id', '=', $store_id)
		->where('category_status', '=', 1)
		->where('category_level', '=', 2)
		->where('products.active_status', '=', 1)
		->where('category_type', '=', 1);

	if ($cate_ids != "") {
		$cids = str_replace("'", "", $cate_ids);
		$cids_1 = explode(',', $cids);

		$data = $data->whereIn('categories.parent_id', $cids_1);
	}
	$data = $data->groupBy('categories.id', "categories_infos.category_name")
		->orderBy('category_name', 'asc')
		->get();

	if (count($data) > 0) {
		$data_list = $data;
	}
	return $data_list;
}

/*SELECT cat.id,p.id,ci.category_name
FROM categories cat
RIGHT JOIN products p ON cat.id = p.sub_category_id
left join categories_infos ci on ci.category_id = cat.id
where ci.language_id = 1
 */

/* To get all outlet list based on vendor */
function getOutletList($c_id) {
	$query = '"outlet_infos"."language_id" = (case when (select count(language_id) as totalcount from outlet_infos where outlet_infos.language_id = ' . getCurrentLang() . ' and outlets.id = outlet_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
	$data = DB::table('outlets')
		->select('outlets.id', 'outlets.vendor_id', 'outlet_infos.outlet_name')
		->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
		->whereRaw($query)
		->where('vendor_id', $c_id)
		->where('active_status', 1)
		->get();
	$data_list = array();
	if (count($data) > 0) {
		$data_list = $data;
	}
	return $data_list;
}

/* To get all customers list */
function all_customers_list($group_id = "") {
	$customers = DB::table('users')
		->select('id', 'email', 'social_title', 'first_name', 'last_name')
		->where("user_type", 3)->where("status", 1);
	if ($group_id != "") {
		$customers = $customers->whereIn('user_group', $group_id);
	}
	$customers = $customers->orderBy('id', 'desc')->get();
	$customers_list = array();
	if (count($customers) > 0) {
		$customers_list = $customers;
	}
	return $customers_list;
}
/* To get all newsletter subscribeers list */
function all_newsletter_subscribers_list() {
	$newsletter_subscribers = DB::table('newsletter_subscribers')
		->select('id', 'email')
		->where("active_status", 1)
		->orderBy('id', 'desc')->get();
	$newsletter_subscribers_list = array();
	if (count($newsletter_subscribers) > 0) {
		$newsletter_subscribers_list = $newsletter_subscribers;
	}
	return $newsletter_subscribers_list;
}
/* To get all customers groups list */
function all_customers_groups_list() {
	$customers_groups = DB::table('users_group')
		->select('group_id', 'group_name')
		->where("group_status", 1)
		->orderBy('group_id', 'desc')->get();
	$customers_group_list = array();
	if (count($customers_groups) > 0) {
		$customers_group_list = $customers_groups;
	}
	return $customers_group_list;
}

/** get generl settings configuration **/
function getAdminpaymentemail() {
	$defaultconfigs = DB::table('users')
		->select('users.payment_account')
		->where('id', '=', 1)
		->get();
	$config_items = array();
	if (count($defaultconfigs) > 0) {
		$config_items = $defaultconfigs[0];
	}
	return $config_items;
}
/** get generl settings configuration **/
function getAppPaymentConfig() {
	$defaultconfigs = DB::table('payment_gateways')
		->select('payment_gateways.*')
		->where('id', '=', 1)
		->get();
	$config_items = array();
	if (count($defaultconfigs) > 0) {
		$config_items = $defaultconfigs[0];
	}
	return $config_items;
}
/** get generl settings configuration **/
function getCms() {
	$language = getCurrentLang();
	$query = 'cms_infos.language_id = (case when (select count(cms_infos.language_id) as totalcount from cms_infos where cms_infos.language_id = ' . $language . ' and cms.id = cms_infos.cms_id) > 0 THEN ' . $language . ' ELSE 1 END)';
	$cms = DB::table('cms')->select('cms.id', 'cms.url_index', 'cms.sort_order', 'cms_infos.title')
		->leftJoin('cms_infos', 'cms_infos.cms_id', '=', 'cms.id')
		->whereRaw($query)
		->where('cms.cms_type', "<>", 2)
		->where('cms.cms_status', '=', 1)
		->orderBy('cms.sort_order', 'asc')
		->get();
	$cms_items = array();
	if (count($cms) > 0) {
		$cms_items = $cms;
	}
	return $cms_items;

}

/** get city list **/
function getUserGroups() {
	$groups = DB::table('users_group')
		->select('users_group.*')
		->orderBy('group_id', 'asc')
		->get();
	$groups_list = array();
	if (count($groups) > 0) {
		$groups_list = $groups;
	}
	return $groups_list;
}

/** get city list **/
function getUserList($user_type) {
	$users = DB::table('users')
		->select('email', 'first_name', 'last_name', 'id')
		->where('active_status', 'A')
	//->where('status', 1)
		->where('user_type', "!=", 1)
		->orderBy('email', 'asc')
		->get();

	$doctors = DB::table('vendors')
		->select('vendors.email', 'vendors.first_name as name', 'vendors.last_name', 'vendors.id', 'vendors.phone_number', 'vendors.mobile_number')
		->join('vendors_infos', 'vendors_infos.id', '=', 'vendors.id')
	//->where('active_status', 1)
		->orderBy('vendors.email', 'asc')
		->get();

	$user_list = array();
	if (count($users) > 0 && $user_type == 1) {
		$user_list = $users;
		return $user_list;
	}
	if (count($doctors) > 0 && $user_type == 2) {
		$user_list = $doctors;
		return $user_list;
	}

}
/*
 * To get the outlet list based on vendor
 */
function get_outlet_list($vendor_id = "") {
	$query = '"outlet_infos"."language_id" = (case when (select count(language_id) as totalcount from outlet_infos where outlet_infos.language_id = ' . getCurrentLang() . ' and outlets.id = outlet_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
	$outlets = DB::table('outlets')
		->select('outlets.id', 'outlet_infos.outlet_name')
		->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
		->whereRaw($query)
		->where('active_status', 1);
	if ($vendor_id != '') {
		$outlets = $outlets->where('vendor_id', $vendor_id);
	}
	$outlets = $outlets->orderBy('outlet_infos.outlet_name', 'asc')->get();
	$outlets_list = array();
	if (count($outlets) > 0) {
		$outlets_list = $outlets;
	}
	return $outlets_list;
}
/*
 * To get the product list based on outlet
 */
function get_product_list($outlet_ids = "") {
	$query = '"products_infos"."lang_id" = (case when (select count(id) as totalcount from products_infos where products_infos.lang_id = ' . getCurrentLang() . ' and products.id = products_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
	//Get the product data
	$products = DB::table('products')
		->select('products.id', 'products_infos.product_name')
		->join('products_infos', 'products_infos.id', '=', 'products.id')
		->whereRaw($query)
		->where('products.active_status', '=', 1);
	if (!empty($outlet_ids)) {
		$products = $products->whereIn('products.outlet_id', $outlet_ids);
	}
	$products = $products->orderBy('products_infos.product_name', 'asc')->get();
	$products_list = array();
	if (count($products) > 0) {
		$products_list = $products;
	}
	return $products_list;
}

/*
 * To get the outlet list based on vendor
 */
function getuserrole($user_id = "") {
	//Get the outlet data
	$roles = DB::table('roles_users')
		->select('user_id', 'role_id', 'role_name', 'tag_bg_color', 'tag_text_color')
		->leftJoin('user_roles', 'user_roles.id', '=', 'roles_users.role_id')
		->where('active_status', 1)
		->where('roles_users.user_id', $user_id);
	$roles = $roles->orderBy('role_name', 'asc')->get();
	$outlets_list = array();
	if (count($roles) > 0) {
		$outlets_list = $roles;
	}
	return $outlets_list;
}

/*
 * To set task list for all modules
 */
function tasks() {
	return array(
		/** roles and users module task **/
		'permissions' => array(
			'sort' => '2',
			'title' => trans('messages.Permissions'),
			'children' => array(
				'roles' => array(
					'title' => trans('messages.Roles'),
					'sort' => '2',
					'task_note' => trans('messages.Managing the roles'),
					'task_index' => '["system/permission"]',
					'apiresources' => array(
						'permissions' => array(
							'title' => trans('messages.Roles'),
							'description' => 'Managing Roles',
							'resource' => array(
								'GET', 'POST', 'PUT', 'DELETE',
							),
						),
					),
				),
				'role/edit' => array(
					'title' => trans('messages.Roles Edit'),
					'sort' => '3',
					'task_note' => trans('messages.Edit,delete the roles and their tasks'),
					'task_index' => '["system/permission/create", "system/permission/edit", "update_role", "system/rolecreate", "system/permission/delete"]',
				),
				'users' => array(
					'title' => trans('messages.Roles Users'),
					'sort' => '4',
					'task_note' => trans('messages.Managing the users'),
					'task_index' => '["permission/users"]',
				),
				'edit' => array(
					'title' => trans('messages.Roles User Edit'),
					'sort' => '5',
					'task_note' => trans('messages.Edit,delete the role users'),
					'task_index' => '["permission/usercreate", "permission/userstore", "permission/users/edit", "usersupdate", "permission/users/delete"]',
				),
			),
		),
		/** roles and users module task **/
		/** vendors and outlets  module task **/
		'vendors' => array(
			'sort' => '2',
			'title' => trans('messages.Vendors'),
			'children' => array(
				'list' => array(
					'title' => trans('messages.Vendors'),
					'sort' => '2',
					'task_note' => trans('messages.Managing the vendors'),
					'task_index' => '["vendors/vendors","vendors/vendor_details"]',
					'apiresources' => array(
						'vendors' => array(
							'title' => trans('messages.Vendors'),
							'description' => 'Managing Vendors',
							'resource' => array(
								'GET', 'POST', 'PUT', 'DELETE',
							),
						),
					),
				),
				'vendors/edit' => array(
					'title' => trans('messages.Vendors Edit'),
					'sort' => '3',
					'task_note' => trans('messages.Edit,delete the vendors and their tasks'),
					'task_index' => '["vendors/create_vendor","vendors/edit_vendor","update_vendor","vendor_create ","vendors/delete_vendor"]',
				),
			),
		),
		'outlets' => array(
			'sort' => '2',
			'title' => trans('messages.Outlets'),
			'children' => array(
				'list' => array(
					'title' => trans('messages.Outlets'),
					'sort' => '2',
					'task_note' => trans('messages.Managing the Outlets'),
					'task_index' => '["vendors/outlets","vendors/outlet_details"]',
					'apiresources' => array(
						'outlets' => array(
							'title' => trans('messages.Outlets'),
							'description' => 'Managing Outlets',
							'resource' => array(
								'GET', 'POST', 'PUT', 'DELETE',
							),
						),
					),
				),
				'outlets/edit' => array(
					'title' => trans('messages.Outlets Edit'),
					'sort' => '3',
					'task_note' => trans('messages.Edit,delete the outlets and their tasks'),
					'task_index' => '["vendors/create_outlet","vendors/edit_outlet","update_outlet","outlet_create","vendors/delete_outlet"]',
				),
			),
		),
		'outletsmanagers' => array(
			'sort' => '2',
			'title' => trans('messages.Outlets Managers'),
			'children' => array(
				'list' => array(
					'title' => trans('messages.Outlets Managers'),
					'sort' => '2',
					'task_note' => trans('messages.Managing the Outlets Managers'),
					'task_index' => '["vendors/outlet_managers"]',
				),
				'outletsmanagers/edit' => array(
					'title' => trans('messages.Outlets Managers Edit'),
					'sort' => '3',
					'task_note' => trans('messages.Edit,delete the outlets managers and their tasks'),
					'task_index' => '["vendors/create_outlet_managers","vendors/edit_outlet_manager","admin/managers/update","create_manager","vendors/delete_outlet_managers"]',
				),
			),
		),
		'products' => array(
			'sort' => '2',
			'title' => trans('messages.Products'),
			'children' => array(
				'list' => array(
					'title' => trans('messages.Products'),
					'sort' => '2',
					'task_note' => trans('messages.Managing the Products'),
					'task_index' => '["admin/products","admin/products/product_details"]',
					'apiresources' => array(
						'products' => array(
							'title' => trans('messages.Products'),
							'description' => 'Managing Products',
							'resource' => array(
								'GET', 'POST', 'PUT', 'DELETE',
							),
						),
					),
				),
				'products/edit' => array(
					'title' => trans('messages.Products Edit'),
					'sort' => '3',
					'task_note' => trans('messages.Edit,delete the products and their tasks'),
					'task_index' => '["admin/products/create_product","admin/products/edit_product","update_product","product_create","admin/products/delete_product"]',
				),
			),
		),
		'drivers' => array(
			'sort' => '2',
			'title' => trans('messages.Drivers'),
			'children' => array(
				'list' => array(
					'title' => trans('messages.Drivers'),
					'sort' => '2',
					'task_note' => trans('messages.Managing the Drivers'),
					'task_index' => '["admin/drivers","admin/drivers/view"]',
					'apiresources' => array(
						'drivers' => array(
							'title' => trans('messages.Drivers'),
							'description' => 'Managing Drivers',
							'resource' => array(
								'GET', 'POST', 'PUT', 'DELETE',
							),
						),
					),
				),
				'drivers/edit' => array(
					'title' => trans('messages.Drivers Edit'),
					'sort' => '3',
					'task_note' => trans('messages.Edit,delete the drivers and their tasks'),
					'task_index' => '["admin/drivers/create","admin/drivers/edit","admin/drivers/update","create_driver","admin/drivers/delete"]',
				),
			),
		),
		'coupons' => array(
			'sort' => '2',
			'title' => trans('messages.Coupons'),
			'children' => array(
				'list' => array(
					'title' => trans('messages.Coupons'),
					'sort' => '2',
					'task_note' => trans('messages.Managing the Coupons'),
					'task_index' => '["admin/coupons","admin/coupons/view"]',
					'apiresources' => array(
						'drivers' => array(
							'title' => trans('messages.Coupons'),
							'description' => 'Managing Coupons',
							'resource' => array(
								'GET', 'POST', 'PUT', 'DELETE',
							),
						),
					),
				),
				'coupons/edit' => array(
					'title' => trans('messages.Coupons Edit'),
					'sort' => '3',
					'task_note' => trans('messages.Edit,delete the coupons and their tasks'),
					'task_index' => '["admin/coupons/create","admin/coupons/edit","admin/coupons/update","create_coupon","admin/coupons/delete"]',
				),
			),
		),
		'subscribers' => array(
			'sort' => '2',
			'title' => trans('messages.Subscribers'),
			'children' => array(
				'list' => array(
					'title' => trans('messages.Subscribers'),
					'sort' => '2',
					'task_note' => trans('messages.Subscribers List'),
					'task_index' => '["admin/subscribers"]',
				),
				'delete' => array(
					'title' => trans('messages.Subscribers'),
					'sort' => '2',
					'task_note' => trans('messages.Managing the subscribers'),
					'task_index' => '["admin/subscribers/delete","admin/subscribers/updateStatus"]',
				),
			),
		),
		'newsletter' => array(
			'sort' => '2',
			'title' => trans('messages.Newsletter'),
			'children' => array(
				'list' => array(
					'title' => trans('messages.Newsletter'),
					'sort' => '2',
					'task_note' => trans('messages.Managing the newsletter'),
					'task_index' => '["admin/newsletter","send_newsletter"]',
				),
			),
		),
		'cms' => array(
			'sort' => '2',
			'title' => trans('messages.CMS'),
			'children' => array(
				'list' => array(
					'title' => trans('messages.CMS'),
					'sort' => '2',
					'task_note' => trans('messages.Managing the cms'),
					'task_index' => '["admin/cms","admin/cms/view"]',
				),
				'cms/edit' => array(
					'title' => trans('messages.Cms Edit'),
					'sort' => '3',
					'task_note' => trans('messages.Edit,delete the cms and their tasks'),
					'task_index' => '["admin/cms/create","admin/cms/edit","updatecms","createcms","admin/cms/delete"]',
				),
			),
		),
		'blog' => array(
			'sort' => '2',
			'title' => trans('messages.Blog'),
			'children' => array(
				'list' => array(
					'title' => trans('messages.Blog'),
					'sort' => '2',
					'task_note' => trans('messages.Managing the blog'),
					'task_index' => '["admin/blog"]',
				),
				'blog/edit' => array(
					'title' => trans('messages.Blog Edit'),
					'sort' => '3',
					'task_note' => trans('messages.Edit,delete the blog and their tasks'),
					'task_index' => '["admin/blog/create","createblog","admin/blog/edit","updateblog","admin/blog/delete","admin/blog/view"]',
				),
			),
		),
		/** roles and users module task **/
		'users' => array(
			'sort' => '2',
			'title' => trans('messages.Users'),
			'children' => array(
				'users/groups' => array(
					'title' => trans('messages.Groups'),
					'sort' => '2',
					'task_note' => trans('messages.Managing the groups'),
					'task_index' => '["admin/users/groups"]',
				),
				'groups/edit' => array(
					'title' => trans('messages.Edit Group'),
					'sort' => '3',
					'task_note' => trans('messages.Add,Edit,delete the group and their tasks'),
					'task_index' => '["admin/groups/create","creategroup","admin/groups/edit","update_group","admin/groups/delete"]',
				),
				'users/addresstype' => array(
					'title' => trans('messages.Address Type'),
					'sort' => '2',
					'task_note' => trans('messages.Managing the address type'),
					'task_index' => '["admin/users/addresstype"]',
				),
				'addresstype/edit' => array(
					'title' => trans('messages.Edit Address Type'),
					'sort' => '3',
					'task_note' => trans('messages.Add,Edit,delete the address type and their tasks'),
					'task_index' => '["admin/addresstype/create","createaddresstype","admin/addresstype/edit","update_addresstype","admin/addresstype/delete"]',
				),
				'list' => array(
					'title' => trans('messages.Users'),
					'sort' => '2',
					'task_note' => trans('messages.Managing the users'),
					'task_index' => '["admin/users/index"]',
					'apiresources' => array(
						'permissions' => array(
							'title' => trans('messages.Users'),
							'description' => 'Managing Users',
							'resource' => array(
								'GET', 'POST', 'PUT', 'DELETE',
							),
						),
					),
				),
				'users/edit' => array(
					'title' => trans('messages.Users Edit'),
					'sort' => '3',
					'task_note' => trans('messages.Edit,delete the users and their tasks'),
					'task_index' => '["admin/users/create","admin/users/edit","update_users","createuser","admin/users/delete"]',
				),
			),
		),
		'category' => array(
			'sort' => '2',
			'title' => trans('messages.Category'),
			'children' => array(
				'list' => array(
					'title' => trans('messages.Category'),
					'sort' => '2',
					'task_note' => trans('messages.Managing the category'),
					'task_index' => '["admin/category"]',
				),
				'category/edit' => array(
					'title' => trans('messages.Category Edit'),
					'sort' => '3',
					'task_note' => trans('messages.Add,Edit,delete the category and their tasks'),
					'task_index' => '["admin/category/create","createcategory","admin/category/edit","updatecategory","admin/category/delete"]',
				),
			),
		),
		/** email notification **/
		'notification' => array(
			'sort' => '2',
			'title' => trans('messages.Email Notification'),
			'children' => array(
				/*'list' => array(
						'title' => trans('messages.Email Notification Subject'),
						'sort' => '2',
						'task_note' => trans('messages.Managing the notification subjects'),
						'task_index' => '["admin/template/subjects"]',
					),
					'subjects/edit' => array(
						'title' => trans('messages.Notification subjects edit'),
						'sort' => '3',
						'task_note' => trans('messages.Add,Edit the notification subjects'),
						'task_index' => '["admin/subjects/create","admin/subjects/edit","admin/subjects/update","createsubject"]'
					),*/
				'list' => array(
					'title' => trans('messages.Notification Templates'),
					'sort' => '4',
					'task_note' => trans('messages.Managing the notification templates'),
					'task_index' => '["admin/templates/email"]',
				),
				'templates/edit' => array(
					'title' => trans('messages.Notification templates edit'),
					'sort' => '5',
					'task_note' => trans('messages.Add,edit the notification templates'),
					'task_index' => '["admin/templates/create","createtemplate", "admin/templates/edit","admin/template/update","admin/templates/view","admin/templates/delete"]',
				),
			),
		),
		'banners' => array(
			'sort' => '2',
			'title' => trans('messages.Banners'),
			'children' => array(
				'list' => array(
					'title' => trans('messages.Banners'),
					'sort' => '2',
					'task_note' => trans('messages.Managing the banners'),
					'task_index' => '["admin/banners"]',
				),
				'banners/edit' => array(
					'title' => trans('messages.Banners Edit'),
					'sort' => '3',
					'task_note' => trans('messages.Add,Edit,delete the banner and their tasks'),
					'task_index' => '["admin/banner/create", "createbanner", "admin/banner/edit", "admin/banner/update", "admin/banner/ajaxupdate"]',
				),
			),
		),
		/*'settings' => array(
				'sort' => '2',
				'title' => trans('messages.Settings'),
				'children' => array(
					'list' => array(
						'title' => trans('messages.Settings'),
						'sort'  => '2',
						'task_note'  => trans('messages.Managing the all site settings'),
						'task_index' => '[admin/settings/general", "admin/settings/store", "admin/settings/local", "admin/settings/email", "admin/settings/socialmedia", "admin/settings/image", "admin/payment/settings", "admin/modules/settings"]',
					),
				),
			),
			'reports_analytics' => array(
				'sort' => '2',
				'title' => trans('messages.Reports & Analytics'),
				'children' => array(
					'list' => array(
						'title' => trans('messages.Reports & Analytics'),
						'sort'  => '2',
						'task_note'  => trans('messages.Managing the all reports and analytics'),
						'task_index' => '[reports/order", "reports/returns", "reports/user", "reports/vendor"]',
					),
				),
			),*/
		'sales' => array(
			'sort' => '2',
			'title' => trans('messages.Sales'),
			'children' => array(
				'orders/index' => array(
					'title' => trans('messages.Orders'),
					'sort' => '2',
					'task_note' => trans('messages.Orders List'),
					'task_index' => '["admin/orders/index"]',
				),
				'orders/info' => array(
					'title' => trans('messages.View Orders'),
					'sort' => '3',
					'task_note' => trans('messages.View orders and their tasks'),
					'task_index' => '["admin/orders/update-status","admin/orders/info","admin/orders/load_history","admin/orders/delete"]',
				),
				'orders/return_orders' => array(
					'title' => trans('messages.Return Orders'),
					'sort' => '4',
					'task_note' => trans('messages.Return Orders List'),
					'task_index' => '["orders/return_orders"]',
				),
				'orders/return_orders_view' => array(
					'title' => trans('messages.View Return Orders'),
					'sort' => '5',
					'task_note' => trans('messages.View orders and their tasks'),
					'task_index' => '["orders/return_orders_view","update_return_order"]',
				),
				'orders/fund_requests' => array(
					'title' => trans('messages.Fund Requests'),
					'sort' => '6',
					'task_note' => trans('messages.Fund Requests List'),
					'task_index' => '["orders/fund_requests"]',
				),
				'orders/approve_fund_status' => array(
					'title' => trans('messages.Approve Fund Requests'),
					'sort' => '6',
					'task_index' => '["orders/approve_fund_status"]',
				),
			),
		),
		'user_notification' => array(
			'sort' => '2',
			'title' => trans('messages.Notifications'),
			'children' => array(
				'notifications' => array(
					'title' => trans('messages.Notifications'),
					'sort' => '2',
					'task_note' => trans('messages.Managing the notifications'),
					'task_index' => '["admin/notifications", "admin/read_notifications", "admin/email-notifications","send_email", "admin/push-notifications"]',
				),
			),
		),
		'reviews' => array(
			'sort' => '2',
			'title' => trans('messages.Reviews'),
			'children' => array(
				'reviews' => array(
					'title' => trans('messages.Reviews'),
					'sort' => '2',
					'task_note' => trans('messages.Managing the reviews'),
					'task_index' => '["admin/reviews"]',
				),
				'admin/reviews/view' => array(
					'title' => trans('messages.View Reviews'),
					'sort' => '3',
					'task_note' => trans('messages.View reviews and their tasks'),
					'task_index' => '["admin/reviews/view","admin/reviews/approve","admin/reviews/delete"]',
				),
			),
		),
	);
}

/*
 * To check the module access
 */
function hasTask($task_index, $check_owner = true) {

	if (Auth::id() == 1 && $check_owner) {
		//print_r($check_owner);exit();

		return true;

	} else {

		$access = getAccessTasks();
		//print_r($access);exit();

		if (in_array($task_index, $access)) {
			return true;
		}
	}
	return false;

}

/*
 * To check the module access
 */
function getAccessTasks() {
	$roletasks = array();
	$user_id = Auth::id();
	$role_users = DB::table('roles_users')
		->select('role_id')
		->where('user_id', "=", $user_id)
		->get();
	if (count($role_users)) {
		foreach ($role_users as $user) {
			$rolet = getTasksIndex($user->role_id);
			if (count($rolet)) {
				foreach ($rolet as $tasks) {
					$roletasks[] = $tasks;
				}
			}
		}
	}
	//$diff = array_intersect(array_unique($roletasks),$menulist);
	$_assTasks = $roletasks;
	return $_assTasks;
}

/*
 * To check the module access
 */
function getTasksIndex($role_id) {
	$db = DB::table('role_tasks')
		->select('*')
		->where('role_id', '=', $role_id)
		->get();
	$tasks = array();
	foreach ($db as $result) {
		if ($result->task_index != "") {
			$array = json_decode($result->task_index, true);
			if (is_array($array)) {
				foreach ($array as $ar) {
					$tasks[] = $ar;
				}
			} else {
				$tasks[] = $result->task_index;
			}
		}
	}
	//$this->_tasksIndex[$role_id] = $tasks;
	return isset($tasks) ? $tasks : array();
}

function getlocation($api) {
	return $api->getLocation();
}

function getCity($api) {
	return $api->getCity();
}

function getFeatureSstore($api) {
	return $api->getFeatureSstore();
}

/** get Location list **/
function getFrontLocationList($city_url) {
	//Get the location areas data
	$locations_query = '"zones_infos"."language_id" = (case when (select count(*) as totalcount from zones_infos where zones_infos.language_id = ' . getCurrentLang() . ' and zones.id = zones_infos.zone_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
	$locations = DB::table('zones')
		->select('zones.id', 'zones_infos.zone_name', 'zones.url_index')
		->leftJoin('zones_infos', 'zones_infos.zone_id', '=', 'zones.id')
		->leftJoin('countries', 'countries.id', '=', 'zones.country_id')
		->leftJoin('cities', 'cities.id', '=', 'zones.city_id')
		->whereRaw($locations_query)
		->where('zones_status', 1)
		->where('cities.url_index', $city_url)
		->orderBy('zone_name', 'asc')
		->get();
	$locations_list = array();
	if (count($locations) > 0) {
		$locations_list = $locations;
	}
	return $locations_list;
}

function getoffers($api) {
	return $api->getOffers();
}

/**
 * Get user types
 *
 * @param
 * @return array
 */
function getBannerTypes() {
	$types = array(1 => 'Common', 2 => 'Store');
	return $types;
}

/** get current Position **/
function getCurrencyPosition() {
	$currency_side = DB::table('settings')->select('settings.currency_side')->first();
	return $currency_side;
}
/** get current Language **/
function getCurrency($language_id = '') {
	if ($language_id == '') {
		$language_id = getAdminCurrentLang();
	}
	$query = '"currencies_infos"."language_id" = (case when (select count(*) as totalcount from currencies_infos where currencies_infos.language_id = ' . $language_id . ' and currencies.id = currencies_infos.currency_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
	$currentcurrency = DB::table('currencies')
		->select('currencies_infos.currency_symbol')
		->join('settings', 'settings.default_currency', '=', 'currencies.id')
		->join('currencies_infos', 'currencies_infos.currency_id', '=', 'currencies.id')
		->where("currencies.active_status", "=", "A")
		->where("currencies.default_status", "=", 1)
		->whereRaw($query)
		->first();
	//print_r($currentcurrency);exit;
	$current_currency_code = '';
	if (count($currentcurrency) > 0) {
		if ($currentcurrency->currency_symbol) {
			$current_currency_code = $currentcurrency->currency_symbol;
		}
	}
	return $current_currency_code;
}

function getCurrencycode() {
	$currentcurrency = DB::table('currencies')->where('active_status', 'A')->where('id', Session::get('general')->default_currency)->get();
	$current_currency_code = '';
	if (count($currentcurrency) > 0) {
		$current_currency_code = $currentcurrency[0]->currency_code;
	}
	return $current_currency_code;
}

function get_coperativess() {
	$cooprative_list[""] = trans("messages.Select cooperative");
	$category_id = DB::table('categories')->select('id')->where('url_key', '=', 'cooperative')
		->first();

	if (isset($category_id->id)) {
		$condition = "(regexp_split_to_array(category_ids,',')::integer[] @> '{" . $category_id->id . "}'::integer[]  and category_ids !='')";
		$query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . getCurrentLang() . ' and outlets.id = outlet_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
		$vendors = DB::table('vendors')
		//->Leftjoin('vendors_infos','vendors_infos.id','=','vendors.id')
			->Leftjoin('outlets', 'outlets.vendor_id', '=', 'vendors.id')
			->Leftjoin('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
			->select('outlets.id as outlets_id', 'outlet_infos.outlet_name')
			->whereRaw($condition)
			->whereRaw($query1)
			->get();
		if (count($vendors) > 0) {
			//print_r($vendors);
			foreach ($vendors as $cooprative) {
				//echo $cooprative->outlet_name;
				if (isset($cooprative->outlets_id) && isset($cooprative->outlet_name)) {
					$cooprative_list[$cooprative->outlets_id] = $cooprative->outlet_name;
				}
			}
		}
	}
	return $cooprative_list;
}
function getOutLetCount($vendor_id) {
	$outlet_count = '';
	return $outlet_count;
}
function get_user_details($user_id) {
	$user_detail = DB::table('users')
		->select('users.social_title', 'users.first_name', 'users.last_name', 'users.email', 'users.gender', 'users.civil_id', 'users.cooperative as cooperative_id', 'users.cooperative', 'users.member_id', 'users.image', 'users.mobile', 'users.name','verfiy_pin','offer_wallet','offer_wallet','wallet_amount')
		->where('users.id', $user_id)
		->first();
	return $user_detail;
}
/* To get the active payment list */
function get_active_payment_gateway_list() {
	$payment_details = DB::table('payment_gateways')
		->select('id')
		->where('active_status', 1)
		->get();
	return $payment_details;
}
function get_coupon_details($coupon_id) {

	$coupon_details = DB::table('coupons')
		->select('coupons_infos.coupon_title', 'coupons.coupon_code')
		->join('coupons_infos', 'coupons_infos.id', '=', 'coupons.id')
		->where('coupons.id', $coupon_id)
		->first();
	return $coupon_details;
}
function get_admin_product_details($product_id) {
	$query = '"products_infos"."lang_id" = (case when (select count(products_infos.id) as totalcount from products_infos where products_infos.lang_id = ' . getAdminCurrentLang() . ' and products.id = products_infos.id) > 0 THEN ' . getAdminCurrentLang() . ' ELSE 1 END)';
	$product_details = DB::table('products')
		->select('products_infos.product_name', 'products.id')
		->join('products_infos', 'products_infos.id', '=', 'products.id')
		->where('products.id', $product_id)
		->whereRaw($query)
		->first();
	return $product_details;
}
function get_admin_vendor_details($product_id) {
	$query = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . getAdminCurrentLang() . ' and vendors.id = vendors_infos.id) > 0 THEN ' . getAdminCurrentLang() . ' ELSE 1 END)';
	$query1 = '"products_infos"."lang_id" = (case when (select count(products_infos.id) as totalcount from products_infos where products_infos.lang_id = ' . getAdminCurrentLang() . ' and products.id = products_infos.id) > 0 THEN ' . getAdminCurrentLang() . ' ELSE 1 END)';
	$vendor_details = DB::table('products')
		->select('vendors_infos.vendor_name', 'vendors.id')
		->join('vendors', 'vendors.id', '=', 'products.vendor_id')
		->join('vendors_infos', 'vendors_infos.id', '=', 'vendors.id')
		->join('products_infos', 'products_infos.id', '=', 'products.id')
		->where('products.id', $product_id)
		->whereRaw($query)
		->whereRaw($query1)
		->first();
	return $vendor_details;
}
/* to get modules list */
function modules_list() {
	$module_list = DB::table('modules')->select('module_name', 'active_status')->first();
	return $module_list;
}
function get_cms_list($index = "") {
	$cms_list = DB::table('cms')
		->select('cms_infos.title', 'cms_infos.content', 'cms.id', 'cms.url_index')
		->join('cms_infos', 'cms_infos.cms_id', '=', 'cms.id')
		->where('cms.url_index', '=', $index)
		->where('cms.cms_status', '=', 1)
		->first();
	return $cms_list;
}
function get_users_list_ids($user_type) {
	$user_list = DB::table('users')
		->select('id', 'email', 'first_name', 'last_name', 'android_device_token', 'ios_device_token')
		->where('status', '=', 1)
		->where('is_verified', '=', 1);
	if ($user_type == 1) {
		$user_list = $user_list->where('android_device_token', '<>', '');
	}
	if ($user_type == 2) {
		$user_list = $user_list->where('ios_device_token', '<>', '');
	}
	$user_list = $user_list->get();
	return $user_list;
}

/* drivers list by outlet_id */
/* To get vendors drivers list */
function vendor_drivers_list($vendor_id) {
	$driver_list = DB::table('drivers')
		->select('drivers.id as driver_id', 'drivers.first_name', 'drivers.last_name')
		->where('drivers.vendor_id', '=', $vendor_id)
		->where('drivers.driver_status', '=', 1)
		->where('drivers.driver_status', '=', 1)
		->where('drivers.active_status', '=', 1)
		->orderBy('drivers.first_name', 'asc')
		->get();
		//print_r("expression");exit();
	return $driver_list;
}
function vendors_drivers_list($outlet_latitude, $outlet_longitude, $vendor_id = 0) {

	deltedriverorderinfo();
	$date = date("Y-m-d H:i:s");
	$time1 = strtotime($date);
	$time = $time1 - (1 * 30);
	$date = date("Y-m-d H:i:s", $time);
	//print_r($vendor_id);exit();
	//print_r($outlet_latitude);echo"<br>";print_r($outlet_longitude);exit();
	//$outlet_latitude="11.022430";
	//$outlet_longitude="76.937790";

	/*$drivers = DB::select("select DISTINCT ON (driver_track_location.driver_id) driver_id, drivers.first_name, drivers.last_name, earth_distance(ll_to_earth(" . $outlet_latitude . "," . $outlet_longitude . "), ll_to_earth(driver_track_location.latitude, driver_track_location.longitude)) as distance from drivers left join driver_track_location on driver_track_location.driver_id = drivers.id where earth_box(ll_to_earth(" . $outlet_latitude . "," . $outlet_longitude . "), 30000) @> ll_to_earth(driver_track_location.latitude, driver_track_location.longitude)  and drivers.active_status=1 and drivers.is_verified=1  and drivers.android_device_token != '' and drivers.driver_status=1 and drivers.created_by = " . $vendor_id . " order by driver_track_location.driver_id,distance asc");*/

	$drivers = DB::select("select DISTINCT ON (driver_track_location.driver_id) driver_id, drivers.first_name, drivers.last_name, earth_distance(ll_to_earth(" . $outlet_latitude . "," . $outlet_longitude . "), ll_to_earth(driver_track_location.latitude, driver_track_location.longitude)) as distance from drivers left join driver_track_location on driver_track_location.driver_id = drivers.id where earth_box(ll_to_earth(" . $outlet_latitude . "," . $outlet_longitude . "), 3000000) @> ll_to_earth(driver_track_location.latitude, driver_track_location.longitude)  and drivers.active_status=1 and   drivers.android_device_token != '' and drivers.is_verified=1 and drivers.driver_status=1 and drivers.vendor_driver = " . $vendor_id . " and  driver_track_location.created_date > '" . $date . "' order by driver_track_location.driver_id,distance asc");
	//print_r($drivers);exit;
	//and drivers.vendor_driver = 0
	// and drivers.vendor_driver = " . $vendor_id . "

	//$drivers = DB::select("select DISTINCT ON (driver_track_location.driver_id) driver_id, drivers.first_name, drivers.last_name, earth_distance(ll_to_earth(".$outlet_latitude.",".$outlet_longitude."), ll_to_earth(driver_track_location.latitude, driver_track_location.longitude)) as distance from drivers left join driver_track_location on driver_track_location.driver_id = drivers.id where earth_box(ll_to_earth(".$outlet_latitude.",".$outlet_longitude."), 10000) @> ll_to_earth(driver_track_location.latitude, driver_track_location.longitude) and drivers.active_status=1  and drivers.is_verified=1 and drivers.driver_created_by=".$vendor_id." order by driver_track_location.driver_id,distance asc limit 1");

	return $drivers;
}

/* drivers list by outlet_id */
function drivers_list($outlet_latitude, $outlet_longitude) {

	deltedriverorderinfo();

	$date1 = date("Y-m-d H:i:s");
	$time1 = strtotime($date1);
	$time = $time1 - (1 * 30);
	$date = date("Y-m-d H:i:s", $time);
	// echo("date ".$outlet_longitude);
	//print_r($outlet_latitude);exit();
	$drivers = DB::select("select DISTINCT ON (driver_track_location.driver_id) driver_id, drivers.first_name, drivers.last_name, earth_distance(ll_to_earth(" . $outlet_latitude . "," . $outlet_longitude . "), ll_to_earth(driver_track_location.latitude, driver_track_location.longitude)) as distance from drivers left join driver_track_location on driver_track_location.driver_id = drivers.id where earth_box(ll_to_earth(" . $outlet_latitude . "," . $outlet_longitude . "), 3000000) @> ll_to_earth(driver_track_location.latitude, driver_track_location.longitude)  and drivers.active_status=1 and   drivers.android_device_token != '' and drivers.is_verified=1 and drivers.driver_status=1 and drivers.vendor_driver = 0 and driver_track_location.created_date > '" . $date . "' order by driver_track_location.driver_id,distance asc");
	//print_r($drivers);exit();

	// $drivers = DB::select("select DISTINCT ON (driver_track_location.driver_id) driver_id, drivers.first_name, drivers.last_name, earth_distance(ll_to_earth(".$outlet_latitude.",".$outlet_longitude."), ll_to_earth(driver_track_location.latitude, driver_track_location.longitude)) as distance from drivers left join driver_track_location on driver_track_location.driver_id = drivers.id where earth_box(ll_to_earth(".$outlet_latitude.",".$outlet_longitude."), 30000) @> ll_to_earth(driver_track_location.latitude, driver_track_location.longitude)  and drivers.active_status=1 and   drivers.android_device_token != '' and drivers.is_verified=1 and drivers.driver_status=1  order by driver_track_location.driver_id,distance asc");

	// echo("vendorID".$date);

	return $drivers;
}
/* drivers detail by id */
function driver_details($driver_track_id) {
	$drivers = DB::table('drivers')
		->join('driver_track_location', 'driver_track_location.driver_id', '=', 'drivers.id')
		->select('drivers.id', 'drivers.first_name', 'drivers.last_name', 'drivers.driver_status', 'driver_track_location.latitude', 'driver_track_location.longitude')
		->where('driver_track_location.id', '=', $driver_track_id)
		->where('drivers.is_verified', '=', 1)
		->where('drivers.active_status', '=', 1)
		->first();
	return $drivers;
}

/* drivers list by outlet_id */
/*function drivers_list($outlet_latitude, $outlet_longitude)
{
$drivers = DB::select("select DISTINCT ON (driver_track_location.driver_id) driver_id, drivers.first_name, drivers.last_name, earth_distance(ll_to_earth(".$outlet_latitude.",".$outlet_longitude."), ll_to_earth(driver_track_location.latitude, driver_track_location.longitude)) as distance from drivers left join driver_track_location on driver_track_location.driver_id = drivers.id where earth_box(ll_to_earth(".$outlet_latitude.",".$outlet_longitude."), 5000) @> ll_to_earth(driver_track_location.latitude, driver_track_location.longitude) and drivers.active_status=1 and drivers.is_verified=1 order by driver_track_location.driver_id,distance asc");
return $drivers;
}
 */
/*function driver_details($driver_track_id)
{
$drivers = DB::table('drivers')
->join('driver_track_location','driver_track_location.driver_id','=','drivers.id')
->select('drivers.id','drivers.first_name','drivers.last_name','drivers.driver_status','driver_track_location.latitude','driver_track_location.longitude')
->where('driver_track_location.id','=',$driver_track_id)
->where('drivers.is_verified','=',1)
->where('drivers.active_status','=',1)
->first();
return $drivers;
 */

function getCategoryVendorLists($head_categories, $language = '') {
	if ($language) {
		$query = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';

	} else {
		$query = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . getAdminCurrentLang() . ' and vendors.id = vendors_infos.id) > 0 THEN ' . getAdminCurrentLang() . ' ELSE 1 END)';
	}
	$c_ids = $head_categories;
	$c_ids = explode(",", $c_ids);
	$c_ids = implode($c_ids, "','");
	$c_ids = "'" . $c_ids . "'";
	$condition = " vendor_category_mapping.category in($c_ids)";
	$data = DB::table('vendors')
		->select('vendors.id', 'vendors_infos.vendor_name')
		->leftJoin('vendors_infos', 'vendors_infos.id', '=', 'vendors.id')
		->join('vendor_category_mapping', 'vendor_category_mapping.vendor_id', '=', 'vendors.id')
		->where('active_status', 1)
		->where('featured_vendor', 1)
		->whereRaw($query)
		->whereRaw($condition)
		->orderBy('vendor_name', 'asc')
		->get();
	$data_list = array();
	if (count($data) > 0) {
		$data_list = $data;
	}
	return $data_list;

}
function head_categories_list_by_url($category_url) {
	$categories = DB::table('categories')
		->select('categories.id')
		->join('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
		->where('category_type', 2)
		->where('url_key', $category_url)
		->where('category_status', 1)
		->first();
	return $categories;

}
function get_feature_brands() {
	$brands = DB::table('brands')
		->select('brands.brand_title', 'brands.brand_image', 'brands.brand_link')
		->where('status', 1)
		->get();
	return $brands;
}

function get_view_store() {
	$query = '"outlet_infos"."language_id" = (case when (select count(language_id) as totalcount from outlet_infos where outlet_infos.language_id = ' . getCurrentLang() . ' and outlets.id = outlet_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
	$outlets = DB::table('outlets')
		->select('outlets.id', 'outlet_infos.outlet_name', 'outlets.url_index')
		->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
		->whereRaw($query)
		->where('active_status', 1)
		->orderBy('outlets.view_count', 'desc')
		->limit(8)
		->get();

	return $outlets;
}
function get_cart_count() {
	if (Session::get('user_id')) {
		$cdata = DB::table('cart')
			->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
			->select('cart_detail.cart_id', DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
			->where("cart.user_id", "=", Session::get('user_id'))
			->groupby('cart_detail.cart_id')
			->get();
		if (count($cdata)) {
			$cart_item = $cdata[0]->cart_count;
		}
		return $cdata;
	}

}
function get_banner_list($language_id) {
	$language_id = getCurrentLang();
	$banners = DB::table('banner_settings')
		->select('banner_settings.banner_setting_id', 'banner_settings.banner_title', 'banner_settings.banner_image', 'banner_settings.banner_link', 'banner_settings.language_type')
		->where('banner_type', 1)
		->where('status', 1)
		->where('banner_settings.language_type', $language_id)
		->orderBy('default_banner', 'desc')
		->get();

	return $banners;
}
function get_store_banner_list($language_id) {
	$language_id = getCurrentLang();
	$banners = DB::table('banner_settings')
		->select('banner_settings.banner_setting_id', 'banner_settings.banner_title', 'banner_settings.banner_image', 'banner_settings.banner_link', 'banner_settings.language_type')
		->where('banner_type', 2)
		->where('status', 1)
		->where('banner_settings.language_type', $language_id)
		->orderBy('default_banner', 'desc')
		->get();

	return $banners;
}
function get_product_vendor($vendor_category_id, $product_url) {
	$product_vendors = DB::table('products')
		->select('products.vendor_id')
		->where('products.vendor_category_id', '=', $vendor_category_id)
		->where('products.product_url', '=', $product_url)
		->orderBy('products.vendor_id', 'desc')
		->groupBy('products.vendor_id')
		->get();
	return $product_vendors;
}
function getProductBasedOutlet($vendor_category_id, $product_url, $vendor_id) {
	$product_outlets = DB::table('products')
		->select('products.outlet_id', 'products.id')
		->where('products.vendor_category_id', '=', $vendor_category_id)
		->where('products.vendor_id', '=', $vendor_id)
		->where('products.product_url', '=', $product_url)
		->orderBy('products.outlet_id', 'desc')
		->distinct()
		->get();
	return $product_outlets;
}
function getSettingsLists() {
	$language_id = getCurrentLang();
	$query = 'settings_infos.language_id = (case when (select count(settings_infos.language_id) as totalcount from settings_infos where settings_infos.language_id = ' . $language_id . ' and settings.id = settings_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
	$data = DB::table('settings')
		->leftjoin('settings_infos', 'settings_infos.id', '=', 'settings.id')
		->select('settings.id', 'settings_infos.copyrights', 'settings_infos.site_name', 'settings_infos.site_description')
		->whereRaw($query)->where('settings.id', '=', 1)->first();
	return $data;
	//print_r($data);exit;
}
function getoutletsCategoryLists($id) {
	$vdata = DB::table('vendors')->select('category_ids')->where('id', $id)->get();
	//print_r($vdata);die;
	$data_list = array();
	if (count($vdata)) {
		$cids = explode(',', $vdata[0]->category_ids);
		//Get the categories data
		$query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = ' . getCurrentLang() . ' and categories.id = categories_infos.category_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
		$data = DB::table('categories')
			->select('categories.id', 'categories_infos.category_name', 'categories.url_key')
			->join('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
			->whereRaw($query)
			->where('category_status', '=', 1)
			->where('category_type', '=', 2)
			->whereIn('categories.id', "IN", $cids)
			->orderBy('category_name', 'asc')
			->get();
		//print_r($data);exit;
		if (count($data) > 0) {
			$data_list = $data;
		}
		return $data_list;
	} else {
		return $data_list;
	}
}
function geoutletCategoryLists($cids, $language) {
	$query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = ' . $language . ' and categories.id = categories_infos.category_id) > 0 THEN ' . $language . ' ELSE 1 END)';
	$data = DB::table('categories')
		->select('categories.id', 'categories_infos.category_name', 'categories.url_key')
		->join('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
		->whereRaw($query)
		->where('category_status', '=', 1)
		->where('category_type', '=', 2)
		->whereIn('categories.id', $cids)
		->orderBy('category_name', 'asc')
		->get();
	return $data;
}
function GetDrivingDistance($lat1, $long1, $lat2, $long2, $unit = "k") {

	$theta = $long1 - $long2;
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	$dist = acos($dist);
	$dist = rad2deg($dist);
	$miles = $dist * 60 * 1.1515;
	$unit = strtoupper($unit);
	$distance = ($miles * 1.609344);
	return number_format($distance, 1);
}
function utf8_encode_deep(&$input) {
	if (is_string($input)) {
		$input = utf8_encode($input);
	} else if (is_array($input)) {
		foreach ($input as &$value) {
			utf8_encode_deep($value);
		}

		unset($value);
	} else if (is_object($input)) {
		$vars = array_keys(get_object_vars($input));

		foreach ($vars as $var) {
			utf8_encode_deep($input->$var);
		}
	}
}
function getProduct_category_image($cate_url) {
	//echo $store_id;exit;
	$data_list = array();
	//Get the categories data
	$query = '"categories_infos"."language_id" = (case when (select count(categories_infos.category_id) as totalcount from categories_infos where categories_infos.language_id = ' . getCurrentLang() . ' and categories.id = categories_infos.category_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
	$data = DB::table('categories')
		->select('categories.id', 'categories.url_key', 'categories_infos.category_name', 'categories.image')
		->rightJoin('products', 'products.category_id', '=', 'categories.id')
		->join('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
		->whereRaw($query)
		->where('url_key', '=', $cate_url)
	//->where('categories.id','=',$category_id)
		->where('products.active_status', '=', 1)
		->where('category_type', '=', 1)
		->groupBy('categories.id', "categories_infos.category_name")
		->get();
	if (count($data) > 0) {
		$data_list = $data;
	}
	return $data_list;
}
function getCms_faq() {
	$language = getCurrentLang();
	$query = 'cms_infos.language_id = (case when (select count(cms_infos.language_id) as totalcount from cms_infos where cms_infos.language_id = ' . $language . ' and cms.id = cms_infos.cms_id) > 0 THEN ' . $language . ' ELSE 1 END)';
	$cms = DB::table('cms')->select('cms.id', 'cms.url_index', 'cms.sort_order', 'cms_infos.title')
		->leftJoin('cms_infos', 'cms_infos.cms_id', '=', 'cms.id')
		->whereRaw($query)
		->where('cms.cms_type', '=', 2)
		->where('cms.cms_status', '=', 1)
		->orderBy('cms.sort_order', 'asc')
		->get();
	$cms_items = array();
	if (count($cms) > 0) {
		$cms_items = $cms;
	}
	return $cms_items;

}
function getNewProductBasedOutlet($product_url, $outlet_id) {
	$product_new_outlets = DB::table('products')
		->select('products.outlet_id')
	//->where('products.vendor_category_id','=',$vendor_category_id)
		->where('products.outlet_id', '=', $outlet_id)
		->where('products.product_url', '=', $product_url)
		->orderBy('products.outlet_id', 'desc')
		->distinct()
		->get();
	return $product_new_outlets;
}

function openurl($url) {

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);

	curl_setopt($ch, CURLOPT_POST, 1);

	curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, ’3′);
	$content = trim(curl_exec($ch));
	curl_close($ch);

	return true;

}
function get_city_details($city_id = '', $language = '', $city_url = '') {
	if ($language == '') {
		$language = getAdminCurrentLang();
	}

	$city_query = '"cities_infos"."language_id" = (case when (select count(cities_infos.language_id) as totalcount from cities_infos where cities_infos.language_id = ' . $language . ' and cities.id = cities_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
	$cities = DB::table('cities')->select('cities.id', 'cities_infos.city_name')
		->join('cities_infos', 'cities_infos.id', '=', 'cities.id');
	if ($city_id != '') {
		$cities = $cities->where('cities.id', $city_id);
	} else if ($city_url != '') {
		$cities = $cities->where('cities.url_index', $city_url);
	}
	$cities_list = $cities->whereRaw($city_query)->first();
	return $cities_list;
}
function get_vendor_details($vendor_id) {
	//echo $vendor_id; exit;
	$vendors_query = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = ' . getCurrentLang() . ' and vendors.id = vendors_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';

	$vendors = DB::table('vendors')
		->select('vendors.id', 'vendors_infos.vendor_name')
		->leftJoin('vendors_infos', 'vendors_infos.id', '=', 'vendors.id')
		->whereRaw($vendors_query)
		->where('vendors.id', $vendor_id)
		->first();

	return $vendors;

}
function get_outlet_details($outlet_id) {

	$outlet_query = '"outlet_infos"."language_id" = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . getCurrentLang() . ' and outlets.id = outlet_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';

	$outlets = DB::table('outlets')
		->select('outlets.id', 'outlet_infos.outlet_name')
		->leftJoin('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
		->whereRaw($outlet_query)
		->where('outlets.id', $outlet_id)
		->first();

	return $outlets;

}
function getOutletsubCategoryLists($id) {
	$vdata = DB::table('outlets')->select('category_ids')->where('id', $id)->get();
	//print_r($vdata);//die;
	$data_list = array();
	if ($vdata[0]->category_ids != "") {
		$cids = explode(',', $vdata[0]->category_ids);
		//Get the categories data
		$query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = ' . getCurrentLang() . ' and categories.id = categories_infos.category_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
		$data = DB::table('categories')
			->select('categories.id', 'categories_infos.category_name', 'categories.url_key')
			->join('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
			->whereRaw($query)
			->where('category_status', '=', 1)
			->where('category_type', '=', 2)
			->whereIn('categories.id', $cids)
			->orderBy('category_name', 'asc')
			->get();
		if (count($data) > 0) {
			$data_list = $data;
		}
		return $data_list;
	} else {
		return $data_list;
	}
}
/*
 * To get the product list based on outlet
 */
function get_user_list($outlet_ids = "") {
	//$query = '"products_infos"."lang_id" = (case when (select count(id) as totalcount from products_infos where products_infos.lang_id = ' . getCurrentLang() . ' and products.id = products_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
	//Get the product data
	$users = DB::table('users')
		->select('users.id', 'users.name')
		->where('users.status', '=', 1)
		->get();
	$users_list = array();
	if (count($users) > 0) {
		$users_list = $users;
	}
	return $users_list;
}


/* To coupon outlet list */
function getUsersLists($coupon_id) {
	//print_r($coupon_id);die;
	$data = DB::table('coupon_users')
		->select('users_id')
		->where('coupon_id', $coupon_id)
		->get();
	$data_list = array();
	if (count($data) > 0) {
		$data_list = $data;
	}
	return $data_list;
}

function getreferral() {
	$data = DB::table('core_referrals')
		->select('*')
		//->where('coupon_id', $coupon_id)
		->get();
	//print_r($data);exit();;

	$data_list = array();
	if (count($data) > 0) {
		$data_list = $data;
	}
	return $data_list;
}


/*common function for delivery*/
function commonDelivery($data)
{
	//print_r("expression");exit;
	//print_r(USERS_FORGOT_PASSWORD_EMAIL_TEMPLATE);exit;
	$driverId = $data['driverId'];
	$order_id = $data['orderId'];
	$language = isset($data['language'])?$data['language']:1;
	$date = date("Y-m-d H:i:s");
	$comment = isset($post_data['comment']) ? $post_data['comment'] : '';

	$status_change = DB::update('update orders set order_status = 12 where id = ' . $order_id . '');

	$affected = DB::update('update orders_log set order_status=?, order_comments = ?, log_time = ? where id = (select max(id) from orders_log where order_id = ' . $order_id . ')', array(12, $comment, $date));

	$affected = DB::update('update drivers set driver_status =1  where id = ?', array($driverId));

	$notify = DB::table('orders')
		->select('orders.assigned_time', 'users.android_device_token', 'users.ios_device_token', 'users.login_type', 'drivers.first_name', 'vendors_infos.vendor_name', 'orders.total_amount','orders.customer_id','orders.order_key_formated','orders.vendor_id','orders.outlet_id', 'order_status.name as status_name','orders.salesperson_id','orders.created_date')
		->leftJoin('users', 'users.id', '=', 'orders.customer_id')
		->leftJoin('order_status', 'orders.order_status', '=', 'order_status.id')

		->leftJoin('vendors_infos', 'vendors_infos.id', '=', 'orders.vendor_id')

		->leftJoin('drivers', 'drivers.id', '=', 'orders.driver_ids')
		->where('orders.id', '=', (int) $order_id)
		->get();
	//print_r($notify);exit;

	$referral =getreferral();
	$customer_id = isset($notify[0]->customer_id)?$notify[0]->customer_id:0;

	$users_details=DB::table('customer_referral')
        ->select('*')
        ->where('customer_referral.customer_id',$customer_id)
        ->where('customer_referral.referal_amount_used', '!=', '1')
        ->get();

   	if($users_details && $users_details[0]->referal_amount_used !=1){

        $count_order=DB::table('orders')
            ->select('*')
            ->where('orders.customer_id',$customer_id)
            ->count();
        //print_r($count_order);exit;
        $wallet_details=DB::table('users')
            ->select('wallet_amount')
            ->where('users.id',$users_details[0]->referred_by)
            ->get();

        $wallet_amount = isset($wallet_details[0]->wallet_amount)?$wallet_details[0]->wallet_amount:0;
        if($count_order == $referral[0]->order_to_complete){
        	$wallet_amount = $wallet_amount + $referral[0]->referred_amount;
        	//print_r($referral);exit; 
        	$affected = DB::update('update users set wallet_amount =?  where id = ?', array($wallet_amount,$users_details[0]->referred_by));
			$affected = DB::update('update customer_referral set referal_amount_used =1  where id = ?', array($users_details[0]->id));

        }
	}
	$app_config = getAppConfig();
	$notify=$notify[0];

	$subject = 'Your Order with ' . $app_config->site_name . ' [' . $notify->order_key_formated . '] has been successfully Delivered!';
	$values = array('order_id' => $order_id,
		'customer_id' => $notify->customer_id,
		'vendor_id' => $notify->vendor_id,
		'outlet_id' => $notify->outlet_id,
		'message' => $subject,
		'read_status' => 0,
		'created_date' => date('Y-m-d H:i:s'));
	DB::table('notifications')->insert($values);


 	DB::insert('insert into outlet_reviews(customer_id,order_id,vendor_id,outlet_id,ratings,created_date
			) values(?,?,?,?,?,?)', [$notify->customer_id, $order_id,$notify->vendor_id,$notify->outlet_id,'-2',date("Y-m-d H:i:s")]);

	DB::insert('insert into driver_reviews(customer_id,order_id,vendor_id,outlet_id,driver_id
			) values(?,?,?,?,?)', [$notify->customer_id, $order_id,$notify->vendor_id,$notify->outlet_id,$driverId]);
			
	$salesperson_id = isset($notify->salesperson_id)?$notify->salesperson_id:0;
	/*DB::table('salesperson')
                ->where('salesperson.id', $salesperson_id)
               
                ->update(['status' =>'F']);*/
    DB::table('orders')
                ->where('id', $order_id)
               
                ->update(['delivery_date' =>date("Y-m-d H:i:s")]);

    $data['user_id'] = $customer_id;
    $data['order_id'] = $order_id;
    $data['language'] = $language;

   	$response = get_order_detail($data);

   	/*invoice*/

   	$order_detail = $response["order_items"];
	$delivery_details = $response["delivery_details"];
	$vendor_info = $response["vendor_info"];
	$logo = url('/assets/front/' . Session::get("general")->theme . '/images/' . Session::get("general")->theme . '.png');
	$delivery_date = date("d F, l", strtotime($delivery_details[0]->delivery_date));
	$delivery_time = date('g:i a', strtotime($delivery_details[0]->start_time)) . '-' . date('g:i a', strtotime($delivery_details[0]->end_time));
	$sub_total = 0;
	$item = '';
	$site_name = Session::get("general")->site_name;
	$currency_side = getCurrencyPosition()->currency_side;
	$currency_symbol = getCurrency($language);
	$items_no =0;
	foreach ($order_detail as $items) {
		if ($currency_side == 1) {
			$item_cost = $currency_symbol . $items->item_cost;
			$unit_cost = $currency_symbol . ($items->item_cost * $items->item_unit);
		} else {
			$item_cost = $items->item_cost . $currency_symbol;
			$unit_cost = ($items->item_cost * $items->item_unit) . $currency_symbol;
		}
		$item .= '<tr><td align="center" style="font-size:15px;padding:10px 0; font-family:dejavu sans,arial; font-weight:normal; border-bottom:1px solid #ccc;">' . wordwrap(ucfirst(strtolower($items->product_name)), 40, "<br>\n") . '</td><td align="center" style="font-size:15px;padding:10px 0;border-bottom:1px solid #ccc; font-family:dejavu sans,arial; font-weight:normal;">' . wordwrap(ucfirst(strtolower($items->description)), 40, "<br>\n") . '</td><td align="center" style="font-size:15px;padding:10px 0;border-bottom:1px solid #ccc; font-family:dejavu sans,arial; font-weight:normal;">' . $items->item_unit . '</td><td align="center" style="font-size:15px;padding:10px 0;border-bottom:1px solid #ccc; font-family:dejavu sans,arial; font-weight:normal;">' . $item_cost . '</td><td align="center" style="font-size:15px;padding:10px 0;border-bottom:1px solid #ccc; font-family:dejavu sans,arial; font-weight:normal;">' . $unit_cost . '</td></tr>';
		$sub_total += $items->item_cost * $items->item_unit;
		$items_no ++;
	}
	if ($currency_side == 1) {
		$delivery_charge = $currency_symbol . '0';
	} else {
		$delivery_charge = '0' . $currency_symbol;
	}
	if ($delivery_details[0]->order_type == 1) {

		if ($currency_side == 1) {
			$delivery_charge = $currency_symbol . $delivery_details[0]->delivery_charge;
		} else {
			$delivery_charge = $delivery_details[0]->delivery_charge . $currency_symbol;
		}
	}
	if ($currency_side == 1) {
		$total_amount = $currency_symbol . $delivery_details[0]->total_amount;
		$sub_total = $currency_symbol . $sub_total;
		$service_tax = $currency_symbol . $delivery_details[0]->service_tax;
	} else {
		$total_amount = $delivery_details[0]->total_amount . $currency_symbol;
		$sub_total = $sub_total . $currency_symbol;
		$service_tax = $delivery_details[0]->service_tax . $currency_symbol;
	}
	$delivery_email = $delivery_details[0]->email;
	$delivery_address = ($delivery_details[0]->contact_address != '') ? ucfirst($delivery_details[0]->contact_address) : '-';
	if ($delivery_details[0]->order_type == 1) {
		$delivery_type = 'DELIVERY ADDRESS :';
		$delivery_address = ($delivery_details[0]->user_contact_address != '') ? ucfirst($delivery_details[0]->user_contact_address) : '-';
	} else {
		$delivery_type = 'PICKUP ADDRESS :';
		$delivery_address = ($delivery_details[0]->contact_address != '') ? ucfirst($delivery_details[0]->contact_address) : '-';
	}

	$site_name = Session::get("general")->site_name;
	$html = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><table width="700px" cellspacing="0" cellpadding="0" bgcolor="#fff" style="border:1px solid #ccc;">
		    <tbody>
		    <tr>
		    <td style="border-bottom:1px solid #ccc;">
		    <table style="padding-top: 25px; padding-bottom: 25px;" width="700px" cellspacing="0" cellpadding="0">
		    <tbody>
		    <tr>
		    <td width="20">&nbsp;</td>
		    <td>
		    <table>
		    <tr>
		    <td style="font-size:16px; font-weight:bold; font-family:Verdana; color:#000; padding-bottom:10px;">BILL FORM :</td>
		    </tr>
		    <tr>
		    <td style="font-size:16px; font-weight:500; font-family:dejavu sans,arial; color:#666; line-height:28px;">' . ucfirst($vendor_info[0]->vendor_name) . ',' . wordwrap(ucfirst($vendor_info[0]->contact_address), 70, "<br>\n") . '<br/>' . ucfirst($vendor_info[0]->contact_email) . '</td>
		    </tr>
		    </table>
		    </td>
		  <!--  <td align="right"><a title="' . $site_name . '" href="' . url('/') . '"><img src="' . $logo . '" alt="' . $site_name . '" /></a></td>-->
		  <td></td>
		    <td width="20">&nbsp;</td>
		    </tr>
		    </tbody>
		    </table>
		    </td>
		    </tr>
		    <!-- end 1 tr -->
		    <tr>
		    <td>
		    <table style="padding-top: 25px; padding-bottom: 25px;" width="700px" cellspacing="0" cellpadding="0">
		    <tbody>
		    <tr>
		    <td width="20">&nbsp;</td>
		    <td colspan="4">
		    <table>
		    <tr>
		    <td style="font-size:16px; font-weight:bold; font-family:Verdana; color:#000; padding-bottom:10px;">' . $delivery_type . '</td>
		    </tr>
		    <tr>
		    <td style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;">' . wordwrap($delivery_address, 70, "<br>\n") . '
		    <br/>' . $delivery_email . '</td>
		    </tr>
		    </table>
		    </td>
		    <td align="right">
		    <table cellpadding="0" cellspacing="0">
		    <tr>
		    <td style="font-size:15px; font-weight:bold; font-family:Verdana; color:#000; line-height:28px;">Invoice</td>
		    <td></td>
		    <td align="left" style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;">' . $vendor_info[0]->invoice_id . '</td>
		    </tr>
		    <tr>
		    <td style="font-size:15px; font-weight:bold; font-family:Verdana; color:#000; line-height:28px;">Delivery date</td>
		    <td></td>
		    <td align="left" style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;">' . date('F d, Y', strtotime($delivery_details[0]->delivery_date)) . '</td>
		    </tr>
		    <tr>
		    <td style="font-size:15px; font-weight:bold; font-family:Verdana; color:#000; line-height:28px;">Invoice date</td>
		    <td></td>
		    <td align="left" style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;">' . date('F d, Y', strtotime($vendor_info[0]->created_date)) . '</td>
		    </tr>
		    <tr>
		    <td style="font-size:11px; font-weight:bold; font-family:Verdana; color:#000; line-height:28px; background:#d1d5d4; padding:0 9px;">AMOUNT DUE</td>
		    <td></td>
		    <td align="left" style="font-size:16px; font-weight:500; font-family:dejavu sans,arial;  color:#666; line-height:28px;background:#d1d5d4;padding:0 9px;">' . $total_amount . '</td>
		    </tr>
		    </table>
		    </td>
		    <td width="20">&nbsp;</td>
		    </tr>
		    </tbody>
		    </table>
		    </td>
		    </tr>
		    <!-- end 2 tr -->
		    <tr>
		    <td>
		    <table cellpadding="0" cellspacing="0" width="100%">
		    <tr style="background:#d1d5d4;padding:0 9px;">
		    <td align="center" style=" padding:7px 0; font-size:17px; font-family:Verdana; font-weight:bold;">Item</th>
		    <td align="center" style=" padding:7px 0;font-size:17px; font-family:Verdana; font-weight:bold;">Description</th>
		    <td align="center" style=" padding:7px 0;font-size:17px; font-family:Verdana; font-weight:bold;">Quantity</th>
		    <td align="center" style=" padding:7px 0;font-size:17px; font-family:Verdana; font-weight:bold;">Unit cost</th>
		    <td align="center" style=" padding:7px 0;font-size:17px; font-family:Verdana; font-weight:bold;">Line total</th>
		    </tr>' . $item . '
		    </table>
		    </td>
		    </tr>
		    <!-- end 3 tr -->
		    <tr>
		    <td>
		    <table style="padding-top: 25px; padding-bottom: 25px;" width="787" cellspacing="0" cellpadding="0">
		    <tbody>
		    <tr>
		    <td width="20">&nbsp;</td>
		    <td>
		    <table>
		    <tbody><tr>
		    <td style="font-size:16px; font-weight:bold; font-family:Verdana; color:#000; padding-bottom:10px;">NOTES / MEMO :</td>
		    </tr>
		    <tr>
		    <td style="font-size:16px; font-weight:500; font-family:dejavu sans,arial; color:#666; line-height:28px;">Free shipping with 30-day money-back guarntee </td>
		    </tr>
		    </tbody></table>
		    </td>
		    <td align="right">
		    <table cellspacing="0" cellpadding="0">
		    <tbody>
		    <tr>
		    <td style="font-size:15px; font-weight:bold; font-family:dejavu sans,arial; color:#000; line-height:28px;">SUBTOTAL</td>
		    <td width="10"></td>
		    <td style="font-size:16px; font-weight:500; font-family:dejavu sans,arial; color:#666; line-height:28px;" align="right">' . $sub_total . '</td>
		    </tr>
		    <tr>
		    <td style="font-size:15px; font-weight:bold; font-family:dejavu sans,arial; color:#000; line-height:28px;">Delivery fee</td>
		    <td width="10"></td>
		    <td style="font-size:16px; font-weight:500; font-family:dejavu sans,arial; color:#666; line-height:28px;" align="right">' . $delivery_charge . '</td>
		    </tr>
		   <tr>
			<td style="font-size:15px; font-weight:bold; font-family:dejavu sans,arial; color:#000; line-height:28px;">Tax </td>
			<td width="10"></td>
			<td style="font-size:16px; font-weight:500; font-family:dejavu sans,arial; color:#666; line-height:28px;" align="right">' . $service_tax . '</td>
			</tr>
		    <tr>
		    <td style="font-size:15px; font-weight:bold; font-family:dejavu sans,arial; color:#000; line-height:28px; background:#d1d5d4; padding:0 9px;">TOTAL</td>
		    <td style="background:#d1d5d4;padding:0 9px;" width="10"></td>
		    <td style="font-size:16px; font-weight:500; font-family:dejavu sans,arial; color:#666; line-height:28px;background:#d1d5d4;padding:0 9px;" align="right">' . $total_amount . '</td>
		    </tr>
		    </tbody></table>
		    </td>
		    <td width="20">&nbsp;</td>
		    </tr>
		    </tbody>
		    </table>
		    </td>
		    </tr>
		    <tr>
		    <td>
		    <table>
		    <tr>
		    <td width="20">&nbsp;</td>
		    <td style="font-size:12px; font-family:dejavu sans,arial; color:#666;padding:10px 10px 0 0;direction:rtl; text-alignment:right;"><b style="font-family: dejavu sans,arial; font-weight: bold;">' . trans('messages.Returns Policy: ') . '</b>' . trans('messages.At Oddappz we try to deliver perfectly each and every time. But in the off-chance that you need to return the item, please do so with the') . '<b style="font-family: dejavu sans,arial; font-weight: bold;">' . trans('messages.original Brand') . trans('messages.box/price tag, original packing and invoice') . '</b> ' . trans('messages.without which it will be really difficult for us to act on your request. Please help us in helping you. Terms and conditions apply') . '</td>
		    <td width="20">&nbsp;</td>
		    </tr>
		    </tbody>
		    </table>';

    $pdf = App::make('dompdf.wrapper');
	$pdf->loadHTML($html)->save(base_path() . '/public/assets/front/' . Session::get("general")->theme . '/images/invoice/' . $vendor_info[0]->invoice_id . '.pdf');
	//$attachment[] = base_path() . '/public/assets/front/' . Session::get("general")->theme . '/images/invoice/' . $vendor_info[0]->invoice_id . '.pdf';
   	/*invoice*/
	$attachurl =  url('/') . '/assets/front/' . Session::get("general")->theme . '/images/invoice/' . $vendor_info[0]->invoice_id . '.pdf';

	$users = Users::find($customer_id);
    /*delivery mail for user*/
	$to = $users->email;
	//$to = 'athhiraraveendran5@gmail.com';
	//$template = DB::table('email_templates')->select('*')->where('template_id', '=', 18)->get();
	$template = DB::table('email_templates')->select('*')->where('template_id', '=', 32)->get();
/*
	if (count($template)) {
		$from = $template[0]->from_email;
		$from_name = $template[0]->from;
		if (!$template[0]->template_id) {
			$template = 'mail_template';
			$from = getAppConfigEmail()->contact_mail;
		}
		$subject = 'Your Order with ' . getAppConfig()->site_name . ' [' . $notify->order_key_formated . '] has been successfully Delivered!';
		$orderId = encrypt($order_id);
		$reviwe_id = base64_encode('123abc');
		$orders_link = '<a href="' . URL::to("order-info/" . $orderId) . '" title="' . trans("messages.View") . '">' . trans("messages.View") . '</a>';
		$review_link = '<a href="' . URL::to("order-info/" . $orderId . '?r=' . $reviwe_id) . '" title="' . trans("messages.View") . '">' . trans("messages.View") . '</a>';
		$content = array('name' => "" . $users->name, 'order_key' => "" . $notify->order_key_formated, 'status_name' => "" . $notify->status_name, 'orders_link' => "" . $orders_link, "review_link" => $review_link);

		//$attachment = "";
		$email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
	}*/

	if (count($template)) {
			$from = $template[0]->from_email;
			$from_name = $template[0]->from;
			if (!$template[0]->template_id) {
				$template = 'mail_template';
				$from = getAppConfigEmail()->contact_mail;
			}
			$currency =getCurrencyList();
			$currency_code = isset($currency[0]->currency_code)?$currency[0]->currency_code:'AED';
			$subject = 'Your Order with ' . getAppConfig()->site_name . ' [' . $notify->order_key_formated . '] has been successfully Delivered!';
			$log_image = url('/assets/admin/email_temp/images/1570903488.jpg');
			$goal_image = url('/assets/admin/email_temp/images/goal.png');
			$order_id = $order_id;
			$created_date = $notify->created_date;
			$shipping_address = 'hopes coll';
			$currency_code = $currency_code;
			$total = $notify->total_amount;
			$attachurl =$attachurl;
			$total_item =(string)$items_no;
			$image1 = url('/assets/admin/email_temp/images/1.jpg');
			$image2 = url('/assets/admin/email_temp/images/2.jpg');
			$image3 = url('/assets/admin/email_temp/images/3.jpg');

			$content = array("log_image"=>$log_image,"goal_image"=>$goal_image,"order_id"=>$order_id,"created_date"=>$created_date,"shipping_address"=>$shipping_address,"currency_code"=>$currency_code,"total"=>$total,"attachurl"=>$attachurl,"total_item"=>$total_item,"image1"=>$image1,"image2"=>$image2,"image3"=>$image3);				    	
			$attachment = "";
			$email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
		}

	/*delivery mail for user end*/

	/*delivery  confirmation mail for admin*/

		$template = DB::table('email_templates')
			->select('*')
			->where('template_id', '=', 27)
			->get();
		if (count($template)) {
			$from = $template[0]->from_email;
			$from_name = $template[0]->from;
			$drivers = Drivers::find($driverId);
			$first_name = isset($drivers->first_name)?$drivers->first_name:'';
			$last_name = isset($drivers->last_name)?$drivers->last_name:'';
			if (!$template[0]->template_id) {
				$template = 'mail_template';
				$from = getAppConfigEmail()->contact_mail;
				$adminsubject = getAppConfig()->site_name . 'Order Delivered Successfully by the driver  - [' . $first_name . '-' . $last_name . ']';
				$from_name = "";
			}

			$adminsubject = getAppConfig()->site_name . 'Order Delivered Successfully by the driver  - [' . $first_name . '-' . $last_name . ']';

			$admin = Users::find(1);
			$admin_mail = $admin->email;
			$driver_name = $first_name . '-' . $last_name;
			$content = array('name' => "" . $admin->name, 'order_key' => "" . $notify->order_key_formated, 'status_name' => "" . $notify->status_name, 'driver_name' => "" . $first_name);
			$mail = smtp($from, $from_name, $admin_mail, $adminsubject, $content, $template);
		}

	/*delivery  confirmation mail for admin*/
    return 1;
    

}
/*common function for delivery*/

/*common function for push notification*/

function push_notification($orderId,$order_status,$backend = 0)
{	


	/*if($backend == 1){ // for backend order process
		 $notify = DB::table('orders')
            ->select('orders.assigned_time', 'users.android_device_token', 'users.ios_device_token','users.id as customerId ','users.login_type', 'users.first_name', 'vendors_infos.vendor_name','vendors.id as vendorId','orders.total_amount','outlets.id as outletId','outlet_infos.outlet_name','orders.driver_ids', 'orders.salesperson_id','orders.order_key_formated','order_status.name as status_name')
            ->Join('users', 'users.id', '=', 'orders.customer_id')
            ->Join('vendors_infos', 'vendors_infos.id', '=', 'orders.vendor_id')
            ->Join('vendors', 'vendors.id', '=', 'orders.vendor_id')
            ->Join('outlets', 'outlets.id', '=', 'orders.outlet_id')
            ->Join('outlet_infos','outlet_infos.id', '=', 'orders.outlet_id')
            ->Join('order_status','order_status.id', '=', 'orders.order_status')
           // ->Join('salesperson','salesperson.id', '=', 'orders.salesperson_id')
           // ->Join('drivers','drivers.id', '=', 'orders.driver_ids')
            ->where('orders.id', '=', (int) $orderId)
            ->get();
	}elseif($order_status == 34 || $order_status == 18 || $order_status == 10)
	{
 		$notify = DB::table('orders')
            ->select('orders.assigned_time', 'users.android_device_token', 'users.ios_device_token','users.id as customerId ','users.login_type', 'users.first_name', 'vendors_infos.vendor_name','vendors.id as vendorId','orders.total_amount','outlets.id as outletId','outlet_infos.outlet_name','orders.driver_ids', 'orders.salesperson_id','orders.order_key_formated','order_status.name as status_name','salesperson.name as salesPersonName','salesperson.android_device_token as salesperson_android_token','salesperson.ios_device_token as salesperson_ios_token')
            ->Join('users', 'users.id', '=', 'orders.customer_id')
            ->Join('vendors_infos', 'vendors_infos.id', '=', 'orders.vendor_id')
            ->Join('vendors', 'vendors.id', '=', 'orders.vendor_id')
            ->Join('outlets', 'outlets.id', '=', 'orders.outlet_id')
            ->Join('outlet_infos','outlet_infos.id', '=', 'orders.outlet_id')
            ->Join('order_status','order_status.id', '=', 'orders.order_status')
            ->Join('salesperson','salesperson.id', '=', 'orders.salesperson_id')
           // ->Join('drivers','drivers.id', '=', 'orders.driver_ids')
            ->where('orders.id', '=', (int) $orderId)
            ->get();
	}else
	{
		 $notify = DB::table('orders')
            ->select('orders.assigned_time', 'users.android_device_token', 'users.ios_device_token','users.id as customerId ','users.login_type', 'users.first_name', 'vendors_infos.vendor_name','vendors.id as vendorId','orders.total_amount','outlets.id as outletId','outlet_infos.outlet_name','orders.driver_ids', 'orders.salesperson_id','orders.order_key_formated','order_status.name as status_name','salesperson.name as salesPersonName','drivers.first_name as driverName')
            ->Join('users', 'users.id', '=', 'orders.customer_id')
            ->Join('vendors_infos', 'vendors_infos.id', '=', 'orders.vendor_id')
            ->Join('vendors', 'vendors.id', '=', 'orders.vendor_id')
            ->Join('outlets', 'outlets.id', '=', 'orders.outlet_id')
            ->Join('outlet_infos','outlet_infos.id', '=', 'orders.outlet_id')
            ->Join('order_status','order_status.id', '=', 'orders.order_status')
            ->Join('salesperson','salesperson.id', '=', 'orders.salesperson_id')
            ->Join('drivers','drivers.id', '=', 'orders.driver_ids')
            ->where('orders.id', '=', (int) $orderId)
            ->get();
	}*/



	$notify = DB::table('orders')
        ->select('orders.assigned_time', 'users.android_device_token', 'users.ios_device_token','users.id as customerId ','users.login_type', 'users.first_name', 'vendors_infos.vendor_name','vendors.id as vendorId','orders.total_amount','outlets.id as outletId','outlet_infos.outlet_name','orders.driver_ids', 'orders.salesperson_id','orders.order_key_formated','order_status.name as status_name')
        ->Join('users', 'users.id', '=', 'orders.customer_id')
        ->Join('vendors_infos', 'vendors_infos.id', '=', 'orders.vendor_id')
        ->Join('vendors', 'vendors.id', '=', 'orders.vendor_id')
        ->Join('outlets', 'outlets.id', '=', 'orders.outlet_id')
        ->Join('outlet_infos','outlet_infos.id', '=', 'orders.outlet_id')
        ->Join('order_status','order_status.id', '=', 'orders.order_status')
        ->where('orders.id', '=', (int) $orderId)
        ->get();
        //print_r($notify);exit();
        if($notify[0]->salesperson_id != 0 || $notify[0]->salesperson_id != NULL){
            $driver = DB::table('salesperson')
	            ->select('salesperson.name as salesPersonName','salesperson.android_device_token as salesperson_android_token','salesperson.ios_device_token as salesperson_ios_token')
	            ->where('salesperson.id', '=', (int)$notify[0]->salesperson_id)
	            ->get();  
	        $notify[0]->salesPersonName = isset($driver[0]->salesPersonName)?$driver[0]->salesPersonName:'';
	        $notify[0]->salesperson_android_token=isset($driver[0]->salesperson_android_token)?$driver[0]->salesperson_android_token:'';

        }else{	$notify[0]->salesPersonName = '';  }

        if($notify[0]->driver_ids !=0 || $notify[0]->driver_ids !=NULL) {
        	$driver = DB::table('drivers')
	            ->select('drivers.first_name as driverName')
	            ->where('drivers.id', '=', (int)$notify[0]->driver_ids)
	            ->get();  
	        $notify[0]->driverName = isset($driver[0]->driverName)?$driver[0]->driverName:'';

        }else{$notify[0]->driverName ='';}
   
		//print_r($notify);exit();
    if (count($notify) > 0 && $notify[0]->login_type != 1 ) {
        $notifys = $notify[0];

        if($backend==1){ // for backend order process
        	if($order_status==1){
                $order_title = '' . 'Order Placed';
                $description = '' . 'Your placed order successfully';
                $orderStatus =1;
            }
            if($order_status==10){
                $order_title = '' . 'Processing Your Order';
                $description = 'The order is in processing'.$notifys->order_key_formated;
                $orderStatus =10;
            }
            if($order_status==18){
                $order_title = '' . 'Order Packed';
                $description = 'Your order has been packed '.$notifys->order_key_formated;
                $orderStatus =18;
            }

            if($order_status==11){
                $order_title = '' . 'Order Cancelled';
                $description = '' . 'Your placed order is cancelled successfully';
                $orderStatus =11;
            }

            if($order_status==12){
                $referral =getreferral();
                $order_title = '' . 'Order Delivered';
                $description = 'Your order has been delivered '.$notifys->order_key_formated;
                $orderStatus =12;
            }

            if($order_status==14){
                $order_title = '' . 'Order Shipped';
                $description = '' . 'your placed order is shipped successfully';
                $orderStatus =14;
            }

            if($order_status==19){
                $order_title = '' . 'Order Picked ';
                $description = 'Your order has  been picked and on the way for delivery '.$notifys->order_key_formated;
                $orderStatus =19;
            }
        }else{

            if($order_status==1){
                $order_title = '' . 'Order Placed';
                $description = '' . 'Your placed order successfully';
                $orderStatus =1;
            }
            if($order_status==10){
                $order_title = '' . 'Processing Your Order';
                $description = '' .$notifys->salesPersonName.' is processing your order '.$notifys->order_key_formated;
                $orderStatus =10;
            }
            if($order_status==18){
                $order_title = '' . 'Order Packed';
                $description = '' .$notifys->salesPersonName.' has packed your order '.$notifys->order_key_formated;
                $orderStatus =18;
            }

            if($order_status==11){
                $order_title = '' . 'Order Cancelled';
                $description = '' . 'Your placed order is cancelled successfully';
                $orderStatus =11;
            }

            if($order_status==12){
                $referral =getreferral();
                $order_title = '' . 'Order Delivered';
                $description = '' .$notifys->driverName .' has delivered your order '.$notifys->order_key_formated;
                $orderStatus =12;
            }

            if($order_status==14){
                $order_title = '' . 'Order Shipped';
                $description = '' . 'your placed order is shipped successfully';
                $orderStatus =14;
            }

            if($order_status==19){
                $order_title = '' . 'Order Picked By Driver';
                $description = '' . $notifys->driverName .' has picked your order and on his way for delivery '.$notifys->order_key_formated;
                $orderStatus =19;
            }

            if($order_status==31){
                $order_title = '' . 'Order Accepted By Driver';
                $description = '' . $notifys->driverName .' Accepted your order and your order will deliver shortly';
                $orderStatus =31;
            }

            if($order_status==32){
                $order_title = '' . 'Driver Arrived';
                $description = '' . $notifys->driverName. ' has arrived at store to pick your order '.$notifys->order_key_formated;
                $orderStatus =32;
            }  

            if($order_status==34){
                $order_title = '' . 'Sales Person Assigned';
                $description = '' .$notifys->salesPersonName.' is Assigned for your order '.$notifys->order_key_formated;
                $orderStatus =34;
            }
        }

          //  print_r($order_status);exit;
			// android device & ios device token
        	if($notifys->login_type == 2){
                $token = $notifys->android_device_token;
            }else if($notifys->login_type == 3)
            {
                $token = $notifys->ios_device_token;
            }
            $token =isset($token)?$token:'';

          // $token = "fMZKiUHw0UM:APA91bG8EN9VI8laR6crolH3fSY2L5oxm6qqUXNhQaiTLFUW5SuCTiklZvzOdgC0qGHhUozXunwnJecVLbxEWpzpfzRLykI61ICAdzgz0tAZKooqa2ZEWgj5b99z5oPxRJwuCY0kpTmy";
            $data = array
                (
                'status' => 1,
                'message' => $order_title,
                'detail' =>array(
                'description'=>$description,    
        
                'customerId' => isset($notifys->customerId) ? $notifys->customerId : '',
                'orderId' => $orderId,
                'driverId' => isset($notifys->driver_ids) ? $notifys->driver_ids : '',
                'orderStatus' => $orderStatus,
                'type' => 2,
                'title' => $order_title,
                'totalamount' => isset($notifys->total_amount) ? $notifys->total_amount : 0,
                'vendorName' => isset($notifys->vendor_name) ? $notifys->vendor_name : '',
                'vendorId' => isset($notifys->vendorId) ? $notifys->vendorId : '',
                'outletId' => isset($notifys->outletId) ? $notifys->outletId : '',
                'outlet_name' => isset($notifys->outlet_name) ? $notifys->outlet_name : '',
                'request_type' => 1,
                "order_assigned_time" => isset($notifys->assigned_time) ? $notifys->assigned_time : '',
                'notification_dialog' => "1",
            ));


// "apns":{
//       "headers":{
//         "apns-priority":"5"
//       }
//     }


            $fields = array
                (
                'registration_ids' => array($token),
                'data' => array('title' => $order_title, 'body' =>  $data ,'sound'=>'Default','image'=>'Notification Image'),
                'notification' => array('title' => $order_title, 'body' =>  $description,'sound'=>'Default','image'=>'Notification Image'),
                'content_available' => true,
                'priority'=>'high',
                'apns' => array('headers' => array('apns-priority' => '10'))
            	);
            $headers = array
                (
                'Authorization: key='.FCM_SERVER_KEY,
               
                'Content-Type: application/json'
                );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            //print_r(json_encode($fields));exit;
            $result = curl_exec($ch);
            //print_r($result);exit;
            curl_close($ch);


            return $notify[0];
    }
}
/*common function for push notification*/


function get_order($data)
{	
	$post_data = $data;
	$order_items =array();
	$language_id = $post_data['language'];
	$product_id = isset($post_data['product_id'])?$post_data['product_id']:0;
	if($product_id !=0)	{
			$query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and p.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

		$order_items = DB::select('SELECT p.product_image,p.id AS product_id,p.weight,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.invoice_id,pi.product_name,pi.description,o.coupon_amount,oi.adjust_weight_qty,oi.replacement_product_id	
	        FROM orders o
	        LEFT JOIN orders_info oi ON oi.order_id = o.id
	        LEFT JOIN products p ON p.id = oi.item_id
	        LEFT JOIN products_infos pi ON pi.id = p.id
	        where ' . $query . ' AND o.id = ? AND o.customer_id= ?AND oi.item_id= ? ORDER BY oi.id', array($post_data['order_id'], $post_data['user_id'],$product_id));
		//print_r($order_items);exit;

	}else{

		$query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and p.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

		$order_items = DB::select('SELECT p.product_image,p.id AS product_id,p.weight,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.invoice_id,pi.product_name,pi.description,o.coupon_amount,oi.adjust_weight_qty,oi.replacement_product_id	
	        FROM orders o
	        LEFT JOIN orders_info oi ON oi.order_id = o.id
	        LEFT JOIN products p ON p.id = oi.item_id
	        LEFT JOIN products_infos pi ON pi.id = p.id
	        where ' . $query . ' AND o.id = ? AND o.customer_id= ? ORDER BY oi.id', array($post_data['order_id'], $post_data['user_id']));
	}
	return $order_items;
}

  function getDistanceBetweenPoints($lat1, $lon1, $lat2, $lon2) {
  //	print_r("expression");exit;
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
	    //return compact('miles','feet','yards','kilometers','meters'); 
	}

function get_order_detail($data) {

		$post_data = $data;
		$language_id = $post_data['language'];

		$query3 = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$query4 = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

		$vendor_info = DB::select('SELECT vendors_infos.vendor_name, vendors.logo_image, vendors.contact_address, vendors.contact_email, o.id as order_id,o.created_date,o.order_status,order_status.name/*,payment_gateways_info.name as payment_gateway_name*/,o.outlet_id,vendors.id as vendor_id,o.order_key_formated,o.invoice_id, vendors.email FROM orders o
        left join vendors vendors on vendors.id = o.vendor_id
        left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
        left join order_status order_status on order_status.id = o.order_status
       /*  left join payment_gateways payment_gateways on payment_gateways.id = o.payment_gateway_id
       left join payment_gateways_info payment_gateways_info on payment_gateways_info.payment_id = payment_gateways.id      */
        where  o.id = ? AND o.customer_id= ? ORDER BY o.id', array($post_data['order_id'], $post_data['user_id']));
		//print_r($vendor_info);exit;
		$query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and p.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$order_items = DB::select('SELECT p.product_image,p.id AS product_id,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.invoice_id,pi.product_name,pi.description,o.coupon_amount
        FROM orders o
        LEFT JOIN orders_info oi ON oi.order_id = o.id
        LEFT JOIN products p ON p.id = oi.item_id
        LEFT JOIN products_infos pi ON pi.id = p.id
        where ' . $query . ' AND o.id = ? AND o.customer_id= ? ORDER BY oi.id', array($post_data['order_id'], $post_data['user_id']));

		$query2 = 'pgi.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and pg.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$query5 = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$delivery_details = DB::select('SELECT o.delivery_instructions,ua.address,pg.id as payment_gateway_id,pgi.name,o.total_amount,o.delivery_charge,o.service_tax,dti.start_time,end_time,o.created_date,o.delivery_date,o.order_type,out_infos.contact_address,o.coupon_amount,ua.address as user_contact_address, u.email FROM orders o
                    LEFT JOIN user_address ua ON ua.id = o.delivery_address
                    LEFT JOIN users u ON u.id = ua.user_id
                    left join payment_gateways pg on pg.id = o.payment_gateway_id
                    left join payment_gateways_info pgi on pgi.payment_id = pg.id
                    left join delivery_time_slots dts on dts.id=o.delivery_slot
                    left join delivery_time_interval dti on dti.id = dts.time_interval_id
                    left join outlets out on out.id = o.outlet_id
                    left join outlet_infos out_infos on out_infos.id = out.id
                    where ' . $query2 . ' AND ' . $query5 . 'AND o.id = ? AND o.customer_id= ?', array($post_data['order_id'], $post_data['user_id']));
		$result = array("order_items" => array(), "delivery_details" => array(), "vendor_info" => array());
		if (count($order_items) > 0 && count($delivery_details) > 0 && count($vendor_info) > 0) {
			$result = array("order_items" => $order_items, "delivery_details" => $delivery_details, "vendor_info" => $vendor_info);
		}
		return $result;
	}


	function driver_assignlog($orderId = 0,$driverId = 0)
    {
        $replce_id = DB::table('driver_order_info')->insertGetId(
			[
				'order_id' => $orderId, 
				'driver_id' => $driverId,
				'assigned_time' => date("Y-m-d H:i:s") 
			]
		);
        return 1;
    }

    function driverorderInfo($orderId = 0,$driverId =0)
    {
      
		$date1 = date("Y-m-d H:i:s");
		$time1 = strtotime($date1);
		$time = $time1 - (1 * 60);
		$currentTime2 = date("Y-m-d H:i:s", $time);
		$orders = DB::table('driver_order_info')
			->select('id','assigned_time')
			->where('assigned_time', '>=', $currentTime2)
			->where('order_id', '=',$orderId)
			->where('driver_id', '=',$driverId)
			->get();
		if(count($orders)){
			return $orders[0];
		}else{
        	return array();
		}
    }

    function get_driverOrder($driverId =0)
    {
       $order = DB::table('driver_order_info')
			->select('assigned_time','id')
			->where('driver_id', '=',$driverId)
			->get();
		if(count($order)){
			return $order[0];
		}else{
        	return array();
		}
    } 
    function deltedriverorderinfo()
    {
    		$date1 = date("Y-m-d H:i:s");
			$time1 = strtotime($date1);
			$time = $time1 - (1 * 60);
			$currentTime2 = date("Y-m-d H:i:s", $time);
			$orders = DB::table('driver_order_info')
				->select('id','assigned_time','driver_id','order_id')
				->where('assigned_time', '<', $currentTime2)
				->get();
			if(count($orders))	{
				foreach ($orders as $key => $value) {
					$affected=DB::table('drivers')
						->where('drivers.id','=',$value->driver_id)
						->update(['driver_status'=>1]);
	            	$affected=DB::table('orders')
						->where('orders.id','=',$value->order_id)
						->update(['driver_ids'=>NULL]);
					$affected=DB::table('driver_order_info')
						->where('id','=',$value->id)
						->delete();
				}
			}
		
    }

	function check_driver_device($driverId, $logintype, $deviceToken) {

		$drivers = DB::table('drivers')
				->select('id','android_device_token','ios_device_token','login_type')
				->where('id', '=', $driverId)
				->get();
		$result =0;
		if(!empty($drivers)){
			$token= '';
			if($drivers[0]->login_type ==2 )
			{
				$token = isset($drivers[0]->android_device_token)?$drivers[0]->android_device_token:'';
			}elseif ($drivers[0]->login_type == 3){
				$token = isset($drivers[0]->ios_device_token)?$drivers[0]->ios_device_token:'';
			}
			$result = ($token == $deviceToken) ? 1 : 2;
		}
		return $result;
	}   


	function logout_push($driver_data)
    {
		if($driver_data->login_type == 2){
                $token = $driver_data->android_device_token;
            }else if($driver_data->login_type == 3)
            {
                $token = $driver_data->ios_device_token;
            }
            $token =isset($token)?$token:'';
          // $token = "fMZKiUHw0UM:APA91bG8EN9VI8laR6crolH3fSY2L5oxm6qqUXNhQaiTLFUW5SuCTiklZvzOdgC0qGHhUozXunwnJecVLbxEWpzpfzRLykI61ICAdzgz0tAZKooqa2ZEWgj5b99z5oPxRJwuCY0kpTmy";

            $order_title ="Force Logout";
            $description ="Force Logout";
            $data = array
                (
                'status' => 1,
                'message' => $order_title,
                'detail' =>array(
                'description'=>$description,    
                'type' => 2,
                'title' => $order_title,
                'request_type' => 1,
                'notification_dialog' => "1",
            ));

            $fields = array
                (
                'registration_ids' => array($token),
                'data' => array('title' => $order_title, 'body' =>  $data ,'sound'=>'Default','image'=>'Notification Image')
            	);
            $headers = array
                (
                'Authorization: key='.FCM_SERVER_KEY,
               
                'Content-Type: application/json'
                );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            //print_r($result);exit;
            curl_close($ch);
		/*FCM push notification*/
    }

    /*function orderdetails($orderId = 0,$outletId= 0,$vendorId=0,$language_id ,$userId,$flag)
    {
    	App::setLocale('en');
        /*--vendor details start--/
        	if($flag != 1)
        	{
	        	$query3 = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
	        	$query4 = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		        $vendor_info = DB::select('SELECT distinct(o.id) as order_id,vendors_infos.vendor_name, vendors.logo_image, vendors.contact_address, vendors.contact_email, o.created_date,o.order_status,order_status.name,payment_gateways_info.name as payment_gateway_name,o.outlet_id,vendors.id as vendor_id,o.order_key_formated,o.invoice_id, delivery_time_interval.start_time,delivery_time_interval.end_time,o.invoice_id
			        FROM orders o
			        left join vendors vendors on vendors.id = o.vendor_id
			        left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
			        left join outlets out on out.vendor_id = vendors.id
			        left join order_status order_status on order_status.id = o.order_status
			        left join payment_gateways payment_gateways on payment_gateways.id = o.payment_gateway_id
			        left join payment_gateways_info payment_gateways_info on payment_gateways_info.payment_id = payment_gateways.id
			        left join delivery_time_slots on delivery_time_slots.id =o.delivery_slot
			        left join delivery_time_interval on delivery_time_interval.id = delivery_time_slots.time_interval_id
			        where ' . $query3 . ' AND ' . $query4 .' AND o.id = ? AND o.outlet_id = ? AND o.vendor_id = ? ORDER BY o.id ', array((int)$orderId, (int)$outletId, (int)$vendorId));
		    }else
		    {
		        $query3 = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
				$query4 = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
				$vendor_info = DB::select('SELECT distinct(o.id) as order_id,vendors_infos.vendor_name, vendors.logo_image, vendors.contact_address, vendors.contact_email, o.created_date,o.order_status,order_status.name,payment_gateways_info.name as payment_gateway_name,o.outlet_id,vendors.id as vendor_id,o.order_key_formated,o.invoice_id, delivery_time_interval.start_time,delivery_time_interval.end_time,o.invoice_id
		        FROM orders o
		        left join vendors vendors on vendors.id = o.vendor_id
		        left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
		        left join outlets out on out.vendor_id = vendors.id
		        left join order_status order_status on order_status.id = o.order_status
		        left join payment_gateways payment_gateways on payment_gateways.id = o.payment_gateway_id
		        left join payment_gateways_info payment_gateways_info on payment_gateways_info.payment_id = payment_gateways.id
		        left join delivery_time_slots on delivery_time_slots.id =o.delivery_slot
		        left join delivery_time_interval on delivery_time_interval.id = delivery_time_slots.time_interval_id
		        where ' . $query3 . ' AND ' . $query4 . ' AND o.id = ? AND o.customer_id= ? ORDER BY o.id ', array($orderId, $userId));
		    }
		  //  print_r($vendor_info);exit();
	        foreach ($vendor_info as $k => $v) {
	            $logo_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
	            if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $v->logo_image) && $v->logo_image != '') {
	                $logo_image = url('/assets/admin/base/images/vendors/logos/' . $v->logo_image);
	            }
	            $vendor_info[$k]->logo_image = $logo_image;
	            $vendor_info[$k]->start_time = ($v->start_time != '') ? $v->start_time : '';
	            $vendor_info[$k]->end_time = ($v->end_time != '') ? $v->end_time : '';
	        }
	    /*--vendor details end--*/

	    /*--delivery details start--*/
	        /*$query2 = 'pgi.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and pg.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
	        $oquery = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
	        $delivery_details = DB::select('SELECT o.delivery_instructions,
		        ua.address as user_contact_address,
		        o.customer_id as user_id,
		        ua.latitude as user_latitude,
		        ua.longitude as user_longitude,
		        pg.id as payment_gateway_id,
		        pgi.name,
		        o.total_amount,
		        o.order_comments,
		        o.replace,
		        o.delivery_instructions,
		        o.salesperson_id,
		        sals.name as salespersonName,
		        o.delivery_charge,
		        o.service_tax,
		        dti.start_time,
		        end_time,
		        o.created_date,
		        o.delivery_date,
		        o.order_type,
		        out_infos.contact_address,out.latitude as outlet_latitude,out.longitude as outlet_longitude,o.coupon_amount, u.email,
		        o.driver_ids,dr.ratings,tr.ratings as rating,u.name as customer_name,drivers.first_name as driver_name,
		        o.used_wallet_amount,vendors_infos.vendor_name, vendors.logo_image,vendors.contact_address,vendors.contact_email,o.order_key_formated,o.invoice_id
		        FROM orders o LEFT JOIN user_address ua ON ua.id = o.delivery_address
		        LEFT JOIN users u ON u.id = ua.user_id
		        left join driver_reviews dr on dr.customer_id = o.customer_id
		        left join drivers  on drivers.id = o.driver_ids
		        left join salesperson sals on sals.id = o.salesperson_id
		        left join outlet_reviews tr on tr.customer_id = o.customer_id
		        left join payment_gateways pg on pg.id = o.payment_gateway_id
		        left join payment_gateways_info pgi on pgi.payment_id = pg.id
		        left join delivery_time_slots dts on dts.id=o.delivery_slot
		        left join delivery_time_interval dti on dti.id = dts.time_interval_id
		        left join outlets out on out.id = o.outlet_id
		        left join vendors vendors on vendors.id = o.vendor_id
	        	left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
		        left join outlet_infos out_infos on out_infos.id = out.id where
		        ' . $query2 . ' AND ' . $oquery . ' AND o.id = ?', array((int)$orderId));/
			    if($flag != 1)
			    {
			        $query2 = 'pgi.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and pg.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		        		$oquery = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
					    $delivery_details = DB::select('SELECT o.delivery_instructions,
					        ua.address as user_contact_address,
					        o.customer_id as user_id,
					        ua.latitude as user_latitude,
					        ua.longitude as user_longitude,
					        pg.id as payment_gateway_id,
					        pgi.name,
					        o.total_amount,
					        o.order_comments,
					        o.delivery_instructions,
					        o.salesperson_id,
					        sals.name as salespersonName,
					        o.delivery_charge,
					        o.service_tax,
					        dti.start_time,
					        end_time,
					        o.created_date,
					        o.delivery_date,
					        o.order_type,
					        out_infos.contact_address,out.latitude as outlet_latitude,out.longitude as outlet_longitude,o.coupon_amount, u.email,o.driver_ids,dr.ratings,tr.ratings as rating,u.name as customer_name,drivers.first_name as driver_name,vendors_infos.vendor_name, vendors.logo_image,vendors.contact_address,vendors.contact_email,o.created_date,o.order_status,order_status.name,payment_gateways_info.name as payment_gateway_name,o.outlet_id,o.vendor_id,o.order_key_formated,o.invoice_id
						    FROM orders o LEFT JOIN user_address ua ON ua.id = o.delivery_address
						     LEFT JOIN users u ON u.id = ua.user_id
						    left join driver_reviews dr on dr.customer_id = o.customer_id
						    left join drivers  on drivers.id = o.driver_ids
						    left join salesperson sals on sals.id = o.salesperson_id
						    left join outlet_reviews tr on tr.customer_id = o.customer_id
						    left join payment_gateways pg on pg.id = o.payment_gateway_id
						    left join payment_gateways_info pgi on pgi.payment_id = pg.id
						    left join delivery_time_slots dts on dts.id=o.delivery_slot
						    left join delivery_time_interval dti on dti.id = dts.time_interval_id
						    left join outlets out on out.id = o.outlet_id
						    left join vendors vendors on vendors.id = o.vendor_id
		        			left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
		        			left join payment_gateways payment_gateways on payment_gateways.id = o.payment_gateway_id
		        			left join payment_gateways_info payment_gateways_info on payment_gateways_info.payment_id = payment_gateways.id
		        		

		        			left join order_status order_status on order_status.id = o.order_status

						    left join outlet_infos out_infos on out_infos.id = out.id where
					        ' . $query2 . ' AND ' . $oquery . ' AND o.id = ?', array((int)$orderId));
			    }else{
			    	$query2 = 'pgi.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and pg.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
					$oquery = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
					$delivery_details = DB::select('SELECT o.delivery_instructions,ua.address as user_contact_address,o.customer_id as user_id,ua.latitude as user_latitude,ua.longitude as user_longitude,pg.id as payment_gateway_id,pgi.name,o.total_amount,o.delivery_charge,o.service_tax,dti.start_time,end_time,o.created_date,o.delivery_date,o.order_type,out_infos.contact_address,out.latitude as outlet_latitude,out.longitude as outlet_longitude,o.coupon_amount, u.email,o.driver_ids,dr.ratings,tr.ratings as rating,dri.first_name as driverName,o.order_comments,o.salesperson_id,sals.name as salespersonName, vendors.logo_image,u.name as customer_name,o.order_status as order_status,o.outlet_id as outlet_id,o.vendor_id as vendor_id
				    FROM orders o LEFT JOIN user_address ua ON ua.id = o.delivery_address
				     LEFT JOIN users u ON u.id = ua.user_id
				    left join driver_reviews dr on dr.customer_id = o.customer_id
				    left join drivers dri on dri.id = o.driver_ids
				    left join salesperson sals on sals.id = o.salesperson_id
				    left join outlet_reviews tr on tr.customer_id = o.customer_id
				    left join payment_gateways pg on pg.id = o.payment_gateway_id
				    left join payment_gateways_info pgi on pgi.payment_id = pg.id
				    left join delivery_time_slots dts on dts.id=o.delivery_slot
				     left join delivery_time_interval dti on dti.id = dts.time_interval_id
				      left join outlets out on out.id = o.outlet_id
				          left join vendors vendors on vendors.id = o.vendor_id
		        			left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
				      left join outlet_infos out_infos on out_infos.id = out.id where
			       ' . $query2 . ' AND ' . $oquery . ' AND o.id = ? AND o.customer_id= ?', array($orderId, $userId));
			    }

	       // print_r($delivery_details);exit();
	  		foreach ($delivery_details as $k => $v) {
	  			$logo_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
			            if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $v->logo_image) && $v->logo_image != '') {
			                $logo_image = url('/assets/admin/base/images/vendors/logos/' . $v->logo_image);
			            }
			            $delivery_details[$k]->logo_image = $logo_image;
	            $delivery_details[$k]->start_time = ($v->start_time != '') ? $v->start_time : '';
	            $delivery_details[$k]->end_time = ($v->end_time != '') ? $v->end_time : '';
	            $delivery_details[$k]->user_contact_address = ($v->user_contact_address != '') ? $v->user_contact_address : '';
	            $delivery_details[$k]->contact_address = ($v->contact_address != '') ? $v->contact_address : '';
	            $delivery_details[$k]->email = ($v->email != '') ? $v->email : '';
	            $wallet_amt =isset($v->used_wallet_amount)?$v->used_wallet_amount:0;
	            $sub_total = $v->total_amount - ($v->delivery_charge + $v->service_tax) + ($v->coupon_amount) +$wallet_amt;
	            $delivery_details[$k]->sub_total = $sub_total;
	            $tax_amount = $sub_total * $v->service_tax / 100;
	            $delivery_details[$k]->tax_amount = $tax_amount;
	            $delivery_details[$k]->userId = $v->user_id;
	            $delivery_details[$k]->driverId =$v->driver_ids;
	        }

	        $deliverynew = new \stdClass();
	        $deliverynew->driverId = isset($delivery_details[0]->driverId) ? $delivery_details[0]->driverId:"" ;
	        $deliverynew->deliveryInstructions = $delivery_details[0]->delivery_instructions;
	        $deliverynew->customerName = $delivery_details[0]->customer_name;
	        $deliverynew->driverName = isset($delivery_details[0]->driver_name) ? $delivery_details[0]->driver_name:"";
	        $deliverynew->userContactAddress = $delivery_details[0]->user_contact_address;
	        $deliverynew->paymentGatewayId = $delivery_details[0]->payment_gateway_id;
	        $deliverynew->name = $delivery_details[0]->name;
	        $deliverynew->totalAmount = $delivery_details[0]->total_amount;
	        $deliverynew->deliveryCharge = $delivery_details[0]->delivery_charge;
	        $deliverynew->serviceTax = $delivery_details[0]->service_tax;
	        $deliverynew->startTime = $delivery_details[0]->start_time;
	        $deliverynew->endTime = $delivery_details[0]->end_time;
	        $deliverynew->createdDate = $delivery_details[0]->created_date;
	        $deliverynew->deliveryDate = $delivery_details[0]->delivery_date;
	        $deliverynew->orderType = $delivery_details[0]->order_type;
	        $deliverynew->contactAddress = $delivery_details[0]->contact_address;
	        $deliverynew->couponAmount = $delivery_details[0]->coupon_amount;
	        $deliverynew->email = $delivery_details[0]->email;
	        $deliverynew->subTotal = $delivery_details[0]->sub_total;
	        $deliverynew->taxAmount = $delivery_details[0]->tax_amount;
	        $deliverynew->userLatitude = $delivery_details[0]->user_latitude;
	        $deliverynew->userLongitude = $delivery_details[0]->user_longitude;
	        $deliverynew->outletLatitude = $delivery_details[0]->outlet_latitude;
	        $deliverynew->outletLongitude = $delivery_details[0]->outlet_longitude;
	        $deliverynew->userId = $delivery_details[0]->userId;
	        $deliverynew->driverRating = isset($delivery_details[0]->ratings) ? $delivery_details[0]->ratings:"" ;
	        $deliverynew->orderRating = isset($delivery_details[0]->rating) ? $delivery_details[0]->rating:"" ;
	        $deliverynew->replace = isset($delivery_details[0]->replace) ? $delivery_details[0]->replace:"" ;
	        $deliverynew->walletAmountUsed = isset($delivery_details[0]->used_wallet_amount) ? $delivery_details[0]->used_wallet_amount:"0" ;
	    /*--delivery details end--*/
	        
	    /*--Order Item details start--/
		    if($flag != 1)
		    {
		        $query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and p.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

		        $wquery = '"weight_classes_infos"."lang_id" = (case when (select count(weight_classes_infos.lang_id) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . $language_id . ' and weight_classes.id = weight_classes_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		        $oquery = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		        $qry = 'oi.replacement_product_id = 0 OR oi.replacement_product_id = null';
		        $order_items = DB::select('SELECT p.product_image, pi.description,p.id AS product_id,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.id as order_id,o.invoice_id,pi.product_name,pi.description,o.coupon_amount,weight_classes_infos.title,weight_classes_infos.unit as unit_code,o.order_key_formated,p.weight,oi.replacement_product_id,oi.id,oi.additional_comments,oi.adjust_weight_qty,oi.pack_status,p.adjust_weight
			        FROM orders o
			        LEFT JOIN orders_info oi ON oi.order_id = o.id
			        LEFT JOIN products p ON p.id = oi.item_id
			        LEFT JOIN products_infos pi ON pi.id = p.id
			        LEFT JOIN weight_classes ON weight_classes.id = p.weight_class_id
			        LEFT JOIN weight_classes_infos ON weight_classes_infos.id = weight_classes.id
			        where ' . $query . ' AND ' . $wquery . ' AND o.id = ? ORDER BY oi.id', array((int)$orderId));
		    }else
		    {
		    	$query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and p.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

				$wquery = '"weight_classes_infos"."lang_id" = (case when (select count(weight_classes_infos.lang_id) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . $language_id . ' and weight_classes.id = weight_classes_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
				$oquery = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

				//$qry = 'oi.replacement_product_id = 0 or oi.replacement_product_id = null';
				//print_r($qry);exit;
				$order_items = DB::select('SELECT p.product_image, pi.description,p.id AS product_id,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.id as order_id,o.invoice_id,pi.product_name,pi.description,o.coupon_amount,weight_classes_infos.title,weight_classes_infos.unit as unit_code,o.order_key_formated,p.weight,oi.replacement_product_id,oi.id,oi.additional_comments,oi.adjust_weight_qty,oi.pack_status,p.adjust_weight
		        FROM orders o
		        LEFT JOIN orders_info oi ON oi.order_id = o.id
		        LEFT JOIN products p ON p.id = oi.item_id
		        LEFT JOIN products_infos pi ON pi.id = p.id
		        LEFT JOIN weight_classes ON weight_classes.id = p.weight_class_id
		        LEFT JOIN weight_classes_infos ON weight_classes_infos.id = weight_classes.id
		        where ' . $query . ' AND ' . $wquery . ' AND o.id = ? AND o.customer_id= ? ORDER BY oi.id', array($orderId, $userId));
		    }
	      //  print_r($order_items);exit();
			foreach ($order_items as $key => $items) {
	            $product_image = URL::asset('assets/front/' . Session::get('general')->theme . '/images/no_image.png');
	            if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $items->product_image) && $items->product_image != '') {
	                $product_image = url('/assets/admin/base/images/products/list/' . $items->product_image);
	            }
	            $invoic_pdf = url('/assets/front/' . Session::get('general')->theme . '/images/invoice/' . $items->invoice_id . '.pdf');
	            $order_items[$key]->product_image = $product_image;
	            $order_items[$key]->invoic_pdf = $invoic_pdf;
	        }

	        $produceInfo = array();
	        $k = $subtot = 0;
	        foreach ($order_items as $ke => $data) {
	            if ($data->replacement_product_id == 0 || $data->replacement_product_id == null) {    
	              //  $produceInfo[$k]['id'] = $data->id;
	                $produceInfo[$k]['id'] = $data->order_id;
	                $produceInfo[$k]['productImage'] = $data->product_image;
	                $produceInfo[$k]['description'] = $data->description;
	                $produceInfo[$k]['productId'] = $data->product_id;
	                $produceInfo[$k]['discountPrice'] = $data->item_cost;
	                $produceInfo[$k]['itemOffer'] = $data->item_offer;
	                $produceInfo[$k]['deliveryCharge'] = $data->delivery_charge;
	                $produceInfo[$k]['serviceTax'] = $data->service_tax;
	                $produceInfo[$k]['orderId'] = $data->order_id;
	                $produceInfo[$k]['replacement'] =  isset($data->additional_comments)?$data->additional_comments:"";
	                $produceInfo[$k]['packedStage'] =  isset($data->pack_status)?$data->pack_status:0;
	                $produceInfo[$k]['adjust_show'] =  isset($data->adjust_weight)?$data->adjust_weight:0;
	                $order_info=DB::select("select SUM(item_unit) as item_unit from orders_info where order_id = $data->order_id and item_id=$data->product_id");
	                if (count($order_info)>0) {
	                    $orderInfoArray=array();

	                    foreach ($order_info as $keys => $values) {
	                        $orderInfoArray[$keys]['itemCount']= $values->item_unit;
	                    }
	                }
	                $produceInfo[$k]['orderUnit'] = $data->item_unit;
	                $sum= DB::select("select   (item_cost * item_unit) as total  from orders_info where order_id = $data->order_id and item_id=$data->product_id");
	                if (count($sum)>0) {
	                    $sumArray=array();
	                    foreach ($sum as $ke => $valu) {
	                        $sumArray[$ke]['total']= $valu->total;
	                    }
	                }

	                $valu->total = $data->item_cost * $data->item_unit;
	                $subtot += $valu->total;
	                $produceInfo[$k]['totalAmount'] = $valu->total;
	                $produceInfo[$k]['invoiceId'] = $data->invoice_id;
	                $produceInfo[$k]['productName'] = $data->product_name;
	                $produceInfo[$k]['couponAmount'] = $data->coupon_amount;
	                $produceInfo[$k]['title'] = $data->title;
	                $produceInfo[$k]['unitCode'] = $data->unit_code;
	                $produceInfo[$k]['orderKeyFormated'] = $data->order_key_formated;
	                $produceInfo[$k]['weight'] = $data->weight;
	                $produceInfo[$k]['invoicePdf'] = $data->invoic_pdf;
	         
	                $weight = isset($data->weight)?$data->weight:$data->weight;
	                $produceInfo[$k]['weight'] =$weight;
	                $adjust_weight_qty= isset($data->adjust_weight_qty)?$data->adjust_weight_qty:"";
	                $weight_last = !empty($data->adjust_weight_qty)?$data->adjust_weight_qty:$data->weight;
	                $weight_last = !empty($data->adjust_weight_qty)?$data->adjust_weight_qty:$data->weight;
	                if ($data->adjust_weight == 1) {
	                    $qntyweight = $weight * $values->item_unit ;
	                    $weight_last = $adjust_weight_qty;
	                } else {
	                    $weight_last =$weight_last *$values->item_unit;
	                }
	                $itemprice =  $data->item_cost / $data->weight;
	                $amount =$weight_last * $itemprice;
	                if($deliverynew->replace == 1)  {
	                  $amount =  $valu->total;
	                }
	                if($amount !=0){$amounts = $amount;}else{$amounts= $valu->total;}
	                $produceInfo[$k]['totalAmount'] = $amounts;
	                $produceInfo[$k]['adjustmentWeight'] = $adjust_weight_qty;
	                $produceInfo[$k]['adjust'] =0 ;
	                $produceInfo[$k]['netWeight'] =$data->weight * $data->item_unit ;
	                if ($data->adjust_weight_qty !=0 || $data->adjust_weight_qty !=null) {
	                    $produceInfo[$k]['adjust'] = 1;
	                }
	            $k++;
	            }
	        }
	    /*--Order Item details End--*/
	        
	    /*--outlet review details start--/
	        $reviews = DB::table('outlet_reviews')
	            ->selectRaw('count(outlet_reviews.order_id) as reviewStatus')
	        	//->where("outlet_reviews.outlet_id","=",$reviews->outlet_id)
	            ->where("outlet_reviews.order_id", "=", (int)$orderId)
	            ->first();
	    /*--outlet review details end--*/

	  
	        
	    /*--tracking details start--/

	        $tracking_orders = array(1 => "Initiated", 10 => "Processed", 36 => "Processed", 18 => "Packed", 19 => "Dispatched", 12 => "Delivered");
	        $t =$y= 0;
	        $last_state = $mob_last_state = "";
	        $tracking_result = $mob_tracking_result = array();
	        foreach ($tracking_orders as $key => $track) {
	          	$mob_tracking_result[$t]['text'] = $track;
	            $mob_tracking_result[$t]['process'] = "0";
	            $mob_tracking_result[$t]['order_comments'] = "";
	            $mob_tracking_result[$t]['date'] = "";
	            $tracking_result[$key]['code'] = $key;
	            $tracking_result[$key]['text'] = $track;
	            $tracking_result[$key]['process'] = "0";
	            $tracking_result[$key]['order_comments'] = "";
	            $tracking_result[$key]['date'] = "";
	            $check_status = DB::table('orders_log')
	                ->select('order_id', 'log_time', 'order_comments')
	                ->where('order_id', '=', (int)$orderId)
	                ->where('order_status', '=', $key)
	                ->first();
	            if (count($check_status) > 0) {
	                $last_state = $key;
	                $tracking_result[$key]['process'] = "1";
	                $tracking_result[$key]['orderComments'] = ($check_status->order_comments != '') ? $check_status->order_comments : '';
	                $tracking_result[$key]['date'] = date('M j Y g:i A', strtotime($check_status->log_time));
	                $mob_last_state = $t;
	                $mob_tracking_result[$y]['text'] = $track;
	                $mob_tracking_result[$y]['process'] = "1";
	                $mob_tracking_result[$y]['orderComments'] = $check_status->order_comments;
	                $mob_tracking_result[$y]['date'] = date('M j Y g:i A', strtotime($check_status->log_time));
	                $y++;
	            }
	            $t++;
	        }
	    /*--tracking details start--/



	        $orderData = new \stdClass();
	        $orderData->orderId = (int)$orderId;

	        $order_info=DB::select("select SUM(item_unit) as item_unit from orders_info where order_id = $data->order_id");

	        if (count($order_info)>0) {
	            $orderInfoArray=array();

	            foreach ($order_info as $keys => $vall) {
	                $orderInfoArray[$keys]['itemCount']= $vall->item_unit;
	            }
	        }	    //echo"<pre>";print_r($delivery_details);exit();

	        $orderData->orderQuantity = $vall->item_unit;
	        $orderData->orderComments = isset($delivery_details[0]->order_comments)?$delivery_details[0]->order_comments:"";
	        $orderData->salesFleetId = isset($delivery_details[0]->salesperson_id) ? $delivery_details[0]->salesperson_id:"" ;
	        $orderData->salesFleetName = isset($delivery_details[0]->salespersonname) ? $delivery_details[0]->salespersonname:"";
	        $orderData->outletName = isset($delivery_details[0]->vendor_name)?$delivery_details[0]->vendor_name:"";
	        $orderData->vendorLogo = isset($delivery_details[0]->logo_image)?$delivery_details[0]->logo_image:"";
	        $orderData->outletAddress = isset($delivery_details[0]->contact_address)?$delivery_details[0]->contact_address:"";;
	        $orderData->contactEmail = isset($delivery_details[0]->contact_email)?$delivery_details[0]->contact_email:"";;
	        $orderData->createdDate = isset($delivery_details[0]->created_date) ? $delivery_details[0]->created_date:"";
	        $orderData->orderStatus = isset($delivery_details[0]->order_status) ? $delivery_details[0]->order_status:"";
	        if($flag != 1){
	        $orderData->name = isset($delivery_details[0]->name) ? $delivery_details[0]->name:"";
	    	}else{
	    	$orderData->name = isset($vendor_info[0]->name) ?$vendor_info[0]->name: $delivery_details[0]->name;
	    	}
	        $orderData->paymentGatewayName = isset($delivery_details[0]->payment_gateway_name) ? $delivery_details[0]->payment_gateway_name:"";
	        $orderData->outletId = isset($delivery_details[0]->outlet_id) ? $delivery_details[0]->outlet_id:"";
			$orderData->vendorId =isset($delivery_details[0]->vendor_id) ? $delivery_details[0]->vendor_id:"";

	        $orderData->orderKeyFormated = isset($delivery_details[0]->order_key_formated) ? $delivery_details[0]->order_key_formated:"";		
	        $orderData->invoiceId = isset($delivery_details[0]->invoice_id) ? $delivery_details[0]->invoice_id:"";
	        $orderData->startTime = isset($delivery_details[0]->start_time) ? $delivery_details[0]->start_time:"";
	        $orderData->endTime = isset($delivery_details[0]->end_time) ? $delivery_details[0]->end_time:"";
	        $orderData->deliveryAddress = isset($delivery_details[0]->user_contact_address)?$delivery_details[0]->user_contact_address:'';
	        //print_r($orderData);exit();
	        $result = array("produceInfo" => $produceInfo, "deliverynew" => $deliverynew, "orderData" => $orderData ,"last_state" => $last_state, "mob_tracking_result" => $mob_tracking_result, "reviews" => $reviews, "tracking_result" => $tracking_result);
	        return $result;
	    	//print_r($result);exit();
    }*/
    function orderdetails($orderId = 0,$outletId= 0,$vendorId=0,$language_id ,$userId,$flag)
    {
    	App::setLocale('en');
        /*--vendor details start--*/
        	if($flag != 1)
        	{
	        	$query3 = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
	        	$query4 = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		        $vendor_info = DB::select('SELECT distinct(o.id) as order_id,vendors_infos.vendor_name, vendors.logo_image, vendors.contact_address, vendors.contact_email, o.created_date,o.order_status,order_status.name,payment_gateways_info.name as payment_gateway_name,o.outlet_id,vendors.id as vendor_id,o.order_key_formated,o.invoice_id, delivery_time_interval.start_time,delivery_time_interval.end_time,o.invoice_id
			        FROM orders o
			        left join vendors vendors on vendors.id = o.vendor_id
			        left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
			        left join outlets out on out.vendor_id = vendors.id
			        left join order_status order_status on order_status.id = o.order_status
			        left join payment_gateways payment_gateways on payment_gateways.id = o.payment_gateway_id
			        left join payment_gateways_info payment_gateways_info on payment_gateways_info.payment_id = payment_gateways.id
			        left join delivery_time_slots on delivery_time_slots.id =o.delivery_slot
			        left join delivery_time_interval on delivery_time_interval.id = delivery_time_slots.time_interval_id
			        where ' . $query3 . ' AND ' . $query4 .' AND o.id = ? AND o.outlet_id = ? AND o.vendor_id = ? ORDER BY o.id ', array((int)$orderId, (int)$outletId, (int)$vendorId));
		    }else
		    {
		        $query3 = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
				$query4 = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
				$vendor_info = DB::select('SELECT distinct(o.id) as order_id,vendors_infos.vendor_name, vendors.logo_image, vendors.contact_address, vendors.contact_email, o.created_date,o.order_status,order_status.name,payment_gateways_info.name as payment_gateway_name,o.outlet_id,vendors.id as vendor_id,o.order_key_formated,o.invoice_id, delivery_time_interval.start_time,delivery_time_interval.end_time,o.invoice_id
		        FROM orders o
		        left join vendors vendors on vendors.id = o.vendor_id
		        left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
		        left join outlets out on out.vendor_id = vendors.id
		        left join order_status order_status on order_status.id = o.order_status
		        left join payment_gateways payment_gateways on payment_gateways.id = o.payment_gateway_id
		        left join payment_gateways_info payment_gateways_info on payment_gateways_info.payment_id = payment_gateways.id
		        left join delivery_time_slots on delivery_time_slots.id =o.delivery_slot
		        left join delivery_time_interval on delivery_time_interval.id = delivery_time_slots.time_interval_id
		        where ' . $query3 . ' AND ' . $query4 . ' AND o.id = ? AND o.customer_id= ? ORDER BY o.id ', array($orderId, $userId));
		    }
		  //  print_r($vendor_info);exit();
	        foreach ($vendor_info as $k => $v) {
	            $logo_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
	            if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $v->logo_image) && $v->logo_image != '') {
	                $logo_image = url('/assets/admin/base/images/vendors/logos/' . $v->logo_image);
	            }
	            $vendor_info[$k]->logo_image = $logo_image;
	            $vendor_info[$k]->start_time = ($v->start_time != '') ? $v->start_time : '';
	            $vendor_info[$k]->end_time = ($v->end_time != '') ? $v->end_time : '';
	        }
	    /*--vendor details end--*/

	    /*--delivery details start--*/
	        /*$query2 = 'pgi.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and pg.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
	        $oquery = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
	        $delivery_details = DB::select('SELECT o.delivery_instructions,
		        ua.address as user_contact_address,
		        o.customer_id as user_id,
		        ua.latitude as user_latitude,
		        ua.longitude as user_longitude,
		        pg.id as payment_gateway_id,
		        pgi.name,
		        o.total_amount,
		        o.order_comments,
		        o.replace,
		        o.delivery_instructions,
		        o.salesperson_id,
		        sals.name as salespersonName,
		        o.delivery_charge,
		        o.service_tax,
		        dti.start_time,
		        end_time,
		        o.created_date,
		        o.delivery_date,
		        o.order_type,
		        out_infos.contact_address,out.latitude as outlet_latitude,out.longitude as outlet_longitude,o.coupon_amount, u.email,
		        o.driver_ids,dr.ratings,tr.ratings as rating,u.name as customer_name,drivers.first_name as driver_name,
		        o.used_wallet_amount,vendors_infos.vendor_name, vendors.logo_image,vendors.contact_address,vendors.contact_email,o.order_key_formated,o.invoice_id
		        FROM orders o LEFT JOIN user_address ua ON ua.id = o.delivery_address
		        LEFT JOIN users u ON u.id = ua.user_id
		        left join driver_reviews dr on dr.customer_id = o.customer_id
		        left join drivers  on drivers.id = o.driver_ids
		        left join salesperson sals on sals.id = o.salesperson_id
		        left join outlet_reviews tr on tr.customer_id = o.customer_id
		        left join payment_gateways pg on pg.id = o.payment_gateway_id
		        left join payment_gateways_info pgi on pgi.payment_id = pg.id
		        left join delivery_time_slots dts on dts.id=o.delivery_slot
		        left join delivery_time_interval dti on dti.id = dts.time_interval_id
		        left join outlets out on out.id = o.outlet_id
		        left join vendors vendors on vendors.id = o.vendor_id
	        	left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
		        left join outlet_infos out_infos on out_infos.id = out.id where
		        ' . $query2 . ' AND ' . $oquery . ' AND o.id = ?', array((int)$orderId));*/
			    if($flag != 1)
			    {
			        $query2 = 'pgi.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and pg.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		        		$oquery = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
					    $delivery_details = DB::select('SELECT o.delivery_instructions,
					        ua.address as user_contact_address,
					        o.customer_id as user_id,
					        ua.latitude as user_latitude,
					        ua.longitude as user_longitude,
					        pg.id as payment_gateway_id,
					        pgi.name,
					        o.total_amount,
					        o.actual_total_amount,
					        o.order_comments,
					        o.delivery_instructions,
					        o.used_wallet_amount,
					        o.salesperson_id,
					        sals.name as salespersonName,
					        o.delivery_charge,
					        o.service_tax,
					        dti.start_time,
					        end_time,
					        o.created_date,
					        o.delivery_date,
					        o.order_type,
					        out_infos.contact_address,out.latitude as outlet_latitude,out.longitude as outlet_longitude,o.coupon_amount, u.email,o.driver_ids,dr.ratings,tr.ratings as rating,u.name as customer_name,drivers.first_name as driver_name,vendors_infos.vendor_name, vendors.logo_image,vendors.contact_address,vendors.contact_email,o.created_date,o.order_status,order_status.name,payment_gateways_info.name as payment_gateway_name,o.outlet_id,o.vendor_id,o.order_key_formated,o.invoice_id,out_infos.outlet_name as vendor_name
						    FROM orders o LEFT JOIN user_address ua ON ua.id = o.delivery_address
						     LEFT JOIN users u ON u.id = ua.user_id
						    left join driver_reviews dr on dr.customer_id = o.customer_id
						    left join drivers  on drivers.id = o.driver_ids
						    left join salesperson sals on sals.id = o.salesperson_id
						    left join outlet_reviews tr on tr.customer_id = o.customer_id
						    left join payment_gateways pg on pg.id = o.payment_gateway_id
						    left join payment_gateways_info pgi on pgi.payment_id = pg.id
						    left join delivery_time_slots dts on dts.id=o.delivery_slot
						    left join delivery_time_interval dti on dti.id = dts.time_interval_id
						    left join outlets out on out.id = o.outlet_id
						    left join vendors vendors on vendors.id = o.vendor_id
		        			left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
		        			left join payment_gateways payment_gateways on payment_gateways.id = o.payment_gateway_id
		        			left join payment_gateways_info payment_gateways_info on payment_gateways_info.payment_id = payment_gateways.id
		        		

		        			left join order_status order_status on order_status.id = o.order_status

						    left join outlet_infos out_infos on out_infos.id = out.id where
					        ' . $query2 . ' AND ' . $oquery . ' AND o.id = ?', array((int)$orderId));
			    }else{
			    	$query2 = 'pgi.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and pg.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
					$oquery = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
					$delivery_details = DB::select('SELECT o.delivery_instructions,ua.address as user_contact_address,o.customer_id as user_id,ua.latitude as user_latitude,ua.longitude as user_longitude,pg.id as payment_gateway_id,pgi.name,o.total_amount,o.actual_total_amount,o.used_wallet_amount,o.delivery_charge,o.service_tax,dti.start_time,end_time,o.created_date,o.delivery_date,o.order_type,out_infos.contact_address,out.latitude as outlet_latitude,out.longitude as outlet_longitude,o.coupon_amount, u.email,o.driver_ids,dr.ratings,tr.ratings as rating,dri.first_name as driverName,o.order_comments,o.salesperson_id,sals.name as salespersonName, vendors.logo_image,u.name as customer_name,o.order_status as order_status,o.outlet_id as outlet_id,o.vendor_id as vendor_id,out_infos.outlet_name as vendor_name
				    FROM orders o LEFT JOIN user_address ua ON ua.id = o.delivery_address
				     LEFT JOIN users u ON u.id = ua.user_id
				    left join driver_reviews dr on dr.customer_id = o.customer_id
				    left join drivers dri on dri.id = o.driver_ids
				    left join salesperson sals on sals.id = o.salesperson_id
				    left join outlet_reviews tr on tr.customer_id = o.customer_id
				    left join payment_gateways pg on pg.id = o.payment_gateway_id
				    left join payment_gateways_info pgi on pgi.payment_id = pg.id
				    left join delivery_time_slots dts on dts.id=o.delivery_slot
				     left join delivery_time_interval dti on dti.id = dts.time_interval_id
				      left join outlets out on out.id = o.outlet_id
				          left join vendors vendors on vendors.id = o.vendor_id
		        			left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
				      left join outlet_infos out_infos on out_infos.id = out.id where
			       ' . $query2 . ' AND ' . $oquery . ' AND o.id = ? AND o.customer_id= ?', array($orderId, $userId));
			    }
			//print_r($delivery_details);exit();
	  		foreach ($delivery_details as $k => $v) {
	  			//echo"<pre>";print_r($v);exit();
	  			$logo_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
			            if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $v->logo_image) && $v->logo_image != '') {
			                $logo_image = url('/assets/admin/base/images/vendors/logos/' . $v->logo_image);
			            }
			            $delivery_details[$k]->logo_image = $logo_image;
	            $delivery_details[$k]->start_time = ($v->start_time != '') ? $v->start_time : '';
	            $delivery_details[$k]->end_time = ($v->end_time != '') ? $v->end_time : '';
	            $delivery_details[$k]->user_contact_address = ($v->user_contact_address != '') ? $v->user_contact_address : '';
	            $delivery_details[$k]->contact_address = ($v->contact_address != '') ? $v->contact_address : '';
	            $delivery_details[$k]->email = ($v->email != '') ? $v->email : '';
	            $wallet_amt =isset($v->used_wallet_amount)?$v->used_wallet_amount:0;
	           // print_r($wallet_amt);exit();
	            $actual_total_amount =isset($v->actual_total_amount)?$v->actual_total_amount:0;
	            $total_amount =isset($v->total_amount)?$v->total_amount:0;
	           
	            ($total_amount == 0 )?$tot = $actual_total_amount : $tot = $total_amount;
	           // print_r($tot);exit();
		        $sub_total = $tot - ($v->delivery_charge + $v->service_tax) + ($v->coupon_amount) /*+$wallet_amt*/;
		       
	          /*  print_r($v->delivery_charge);echo"<br>";
	            print_r($v->service_tax);echo"<br>";
	            print_r($v->coupon_amount);echo"<br>";
	            print_r($wallet_amt);echo"<br>";
	            print_r($actual_total_amount);exit();*/

	            $delivery_details[$k]->sub_total = $sub_total;
	            $tax_amount = $sub_total * $v->service_tax / 100;
	            $delivery_details[$k]->tax_amount = $tax_amount;
	            $delivery_details[$k]->userId = $v->user_id;
	            $delivery_details[$k]->driverId =$v->driver_ids;
	        }

	        $deliverynew = new \stdClass();
	        $deliverynew->driverId = isset($delivery_details[0]->driverId) ? $delivery_details[0]->driverId:"" ;
	        $deliverynew->deliveryInstructions = $delivery_details[0]->delivery_instructions;
	        $deliverynew->customerName = $delivery_details[0]->customer_name;
	        $deliverynew->driverName = isset($delivery_details[0]->driver_name) ? $delivery_details[0]->driver_name:"";
	        $deliverynew->userContactAddress = $delivery_details[0]->user_contact_address;
	        $deliverynew->paymentGatewayId = $delivery_details[0]->payment_gateway_id;
	        $deliverynew->name = $delivery_details[0]->name;
	        $deliverynew->totalAmount = $delivery_details[0]->total_amount;
	        $deliverynew->deliveryCharge = $delivery_details[0]->delivery_charge;
	        $deliverynew->serviceTax = $delivery_details[0]->service_tax;
	        $deliverynew->startTime = $delivery_details[0]->start_time;
	        $deliverynew->endTime = $delivery_details[0]->end_time;
	        $deliverynew->createdDate = $delivery_details[0]->created_date;
	        $deliverynew->deliveryDate = $delivery_details[0]->delivery_date;
	        $deliverynew->orderType = $delivery_details[0]->order_type;
	        $deliverynew->contactAddress = $delivery_details[0]->contact_address;
	        $deliverynew->couponAmount = $delivery_details[0]->coupon_amount;
	        $deliverynew->email = $delivery_details[0]->email;
	        $deliverynew->subTotal = $delivery_details[0]->sub_total;
	        $deliverynew->taxAmount = $delivery_details[0]->tax_amount;
	        $deliverynew->userLatitude = $delivery_details[0]->user_latitude;
	        $deliverynew->userLongitude = $delivery_details[0]->user_longitude;
	        $deliverynew->outletLatitude = $delivery_details[0]->outlet_latitude;
	        $deliverynew->outletLongitude = $delivery_details[0]->outlet_longitude;
	        $deliverynew->userId = $delivery_details[0]->userId;
	        $deliverynew->driverRating = isset($delivery_details[0]->ratings) ? $delivery_details[0]->ratings:"" ;
	        $deliverynew->orderRating = isset($delivery_details[0]->rating) ? $delivery_details[0]->rating:"" ;
	        $deliverynew->replace = isset($delivery_details[0]->replace) ? $delivery_details[0]->replace:"" ;
	        $deliverynew->walletAmountUsed = isset($delivery_details[0]->used_wallet_amount) ? $delivery_details[0]->used_wallet_amount:"0" ;
	   //    echo"<pre>";print_r($deliverynew);exit();
	    /*--delivery details end--*/
	        
	    /*--Order Item details start--*/
	    $flag =0;
		    if($flag != 1)
		    {
				$query = 'p.lang_id = (case when (select count(*) as totalcount from admin_products where admin_products.lang_id = '.$language_id.' and op.product_id = admin_products.id) > 0 THEN '.$language_id.' ELSE 1 END)';

		        $wquery = '"weight_classes_infos"."lang_id" = (case when (select count(weight_classes_infos.lang_id) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . $language_id . ' and weight_classes.id = weight_classes_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		        $oquery = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		        $qry = 'oi.replacement_product_id = 0 OR oi.replacement_product_id = null';
		        /*$order_items = DB::select('SELECT p.product_image, pi.description,p.id AS product_id,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.id as order_id,o.invoice_id,pi.product_name,pi.description,o.coupon_amount,weight_classes_infos.title,weight_classes_infos.unit as unit_code,o.order_key_formated,p.weight,oi.replacement_product_id,oi.id,oi.additional_comments,oi.adjust_weight_qty,oi.pack_status,p.adjust_weight
			        FROM orders o
			        LEFT JOIN orders_info oi ON oi.order_id = o.id
			        LEFT JOIN products p ON p.id = oi.item_id
			        LEFT JOIN products_infos pi ON pi.id = p.id
			        LEFT JOIN weight_classes ON weight_classes.id = p.weight_class_id
			        LEFT JOIN weight_classes_infos ON weight_classes_infos.id = weight_classes.id
			        where ' . $query . ' AND ' . $wquery . ' AND o.id = ? ORDER BY oi.id', array((int)$orderId));*/
		       $order_items =DB::select('SELECT o.total_amount,
												o.delivery_charge,
												o.service_tax,
												o.id as order_id,
												o.invoice_id,
												o.coupon_amount,
												o.order_key_formated,
												oi.item_cost,
												oi.item_unit,
												oi.item_offer,
												oi.replacement_product_id,
												oi.id,
												oi.additional_comments,
												oi.adjust_weight_qty,
												oi.pack_status,
												p.description,
												p.product_name,
												p.image	 AS product_image,
												p.weight_class_id,
												p.weight,
												p.adjust_weight,
								    			p.id AS product_id,
												weight_classes_infos.title,
												weight_classes_infos.unit as unit_code
										  FROM orders o
										  LEFT JOIN orders_info oi ON oi.order_id = o.id 
										  LEFT JOIN admin_products p ON p.id = oi.item_id
										  LEFT JOIN outlet_products op ON op.product_id = oi.item_id

										  LEFT JOIN weight_classes ON weight_classes.id = p.weight_class_id
										  LEFT JOIN weight_classes_infos ON weight_classes_infos.id = weight_classes.id
										  where ' . $query . ' AND ' . $wquery . ' AND o.id = ? ORDER BY oi.id', array($orderId));
		    }else
		    {	   
		    	//$query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and p.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
				$query = 'p.lang_id = (case when (select count(*) as totalcount from admin_products where admin_products.lang_id = '.$language_id.' and op.product_id = admin_products.id) > 0 THEN '.$language_id.' ELSE 1 END)';

				$wquery = '"weight_classes_infos"."lang_id" = (case when (select count(weight_classes_infos.lang_id) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . $language_id . ' and weight_classes.id = weight_classes_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
				$oquery = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

				$order_items =DB::select('SELECT o.total_amount,
												o.delivery_charge,
												o.service_tax,
												o.id as order_id,
												o.invoice_id,
												o.coupon_amount,
												o.order_key_formated,
												oi.item_cost,
												oi.item_unit,
												oi.item_offer,
												oi.replacement_product_id,
												oi.id,
												oi.additional_comments,
												oi.adjust_weight_qty,
												oi.pack_status,
												p.description,
												p.product_name,
												p.image	 AS product_image,
												p.weight_class_id,
												p.weight,
												p.adjust_weight,
								    			p.id AS product_id,
												weight_classes_infos.title,
												weight_classes_infos.unit as unit_code
										  FROM orders o
										  LEFT JOIN orders_info oi ON oi.order_id = o.id 
										  LEFT JOIN admin_products p ON p.id = oi.item_id
										  LEFT JOIN outlet_products op ON op.product_id = oi.item_id

										  LEFT JOIN weight_classes ON weight_classes.id = p.weight_class_id
										  LEFT JOIN weight_classes_infos ON weight_classes_infos.id = weight_classes.id
										  where ' . $query . ' AND ' . $wquery . ' AND o.id = ? AND o.customer_id= ? ORDER BY oi.id', array($orderId, $userId));
										  //where o.id = ? AND o.customer_id= ? ORDER BY oi.id',array($orderId,$userId));
				//print_r($order_items);exit();

		        /*$query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and p.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

				$wquery = '"weight_classes_infos"."lang_id" = (case when (select count(weight_classes_infos.lang_id) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . $language_id . ' and weight_classes.id = weight_classes_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
				$oquery = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

				//$qry = 'oi.replacement_product_id = 0 or oi.replacement_product_id = null';
				//print_r($qry);exit;
				$order_items = DB::select('SELECT p.product_image, pi.description,p.id AS product_id,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.id as order_id,o.invoice_id,pi.product_name,pi.description,o.coupon_amount,weight_classes_infos.title,weight_classes_infos.unit as unit_code,o.order_key_formated,p.weight,oi.replacement_product_id,oi.id,oi.additional_comments,oi.adjust_weight_qty,oi.pack_status,p.adjust_weight
		        FROM orders o
		        LEFT JOIN orders_info oi ON oi.order_id = o.id
		        LEFT JOIN products p ON p.id = oi.item_id
		        LEFT JOIN products_infos pi ON pi.id = p.id
		        LEFT JOIN weight_classes ON weight_classes.id = p.weight_class_id
		        LEFT JOIN weight_classes_infos ON weight_classes_infos.id = weight_classes.id
		        where ' . $query . ' AND ' . $wquery . ' AND o.id = ? AND o.customer_id= ? ORDER BY oi.id', array($orderId, $userId));*/
		    }
	      //  print_r($order_items);exit();
			foreach ($order_items as $key => $items) {
				//print_r($items);exit();

					$no_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
					$path = url('/assets/admin/base/images/products/admin_products/');
		            $productImage=json_decode($items->product_image);
		          
		            $image1 =$image2=$image3 =array();
            		$image1[]= $no_image;
		            if($productImage != "")
		            {           

		                foreach ($productImage as $keys => $value) {
							if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $value) && $value != '') {
                        		$image1[] =$path.'/'.$value;
		                    }		
		                }
		            }

		           // $cart_data[$key]->product_image = $image1;


					/*
	            $product_image = URL::asset('assets/front/' . Session::get('general')->theme . '/images/no_image.png');
	            if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $items->product_image) && $items->product_image != '') {
	                $product_image = url('/assets/admin/base/images/products/list/' . $items->product_image);
	            }*/
	            $invoic_pdf = url('/assets/front/' . Session::get('general')->theme . '/images/invoice/' . $items->invoice_id . '.pdf');
	            //$order_items[$key]->product_image = $product_image;
	            $order_items[$key]->product_image = $image1;
	      
                  
	            $order_items[$key]->invoic_pdf = $invoic_pdf;
	        }

	        $produceInfo = array();
	        $k = $subtot = 0;
	        foreach ($order_items as $ke => $data) {
	            if ($data->replacement_product_id == 0 || $data->replacement_product_id == null) {    
	              //  $produceInfo[$k]['id'] = $data->id;
	                $produceInfo[$k]['id'] = $data->order_id;
	                $produceInfo[$k]['productImage'] = $data->product_image;
	                $produceInfo[$k]['productInfoImage'] = $data->product_image;
	                $produceInfo[$k]['productZoomImage'] = $data->product_image;
	                $produceInfo[$k]['description'] = $data->description;
	                $produceInfo[$k]['productId'] = $data->product_id;
	                $produceInfo[$k]['discountPrice'] = number_format($data->item_cost,2);
	                $produceInfo[$k]['itemOffer'] = $data->item_offer;
	                $produceInfo[$k]['deliveryCharge'] = $data->delivery_charge;
	                $produceInfo[$k]['serviceTax'] = $data->service_tax;
	                $produceInfo[$k]['orderId'] = $data->order_id;
	                $produceInfo[$k]['replacement'] =  isset($data->additional_comments)?$data->additional_comments:"";
	                $produceInfo[$k]['packedStage'] =  isset($data->pack_status)?$data->pack_status:0;
	                $produceInfo[$k]['adjust_show'] =  isset($data->adjust_weight)?$data->adjust_weight:0;
	                $order_info=DB::select("select SUM(item_unit) as item_unit from orders_info where order_id = $data->order_id and item_id=$data->product_id");
	                if (count($order_info)>0) {
	                    $orderInfoArray=array();

	                    foreach ($order_info as $keys => $values) {
	                        $orderInfoArray[$keys]['itemCount']= $values->item_unit;
	                    }
	                }
	                $produceInfo[$k]['orderUnit'] = $data->item_unit;
	                $sum= DB::select("select   (item_cost * item_unit) as total  from orders_info where order_id = $data->order_id and item_id=$data->product_id");
	                if (count($sum)>0) {
	                    $sumArray=array();
	                    foreach ($sum as $ke => $valu) {
	                        $sumArray[$ke]['total']= $valu->total;
	                    }
	                }
	                $valu->total = $data->item_cost * $data->item_unit;
	                $subtot += $valu->total;
	               // print_r($subtot);echo "<br>";
	                $produceInfo[$k]['totalAmount'] = number_format($valu->total,3);
	                $produceInfo[$k]['invoiceId'] = $data->invoice_id;
	                $produceInfo[$k]['productName'] = $data->product_name;
	                $produceInfo[$k]['couponAmount'] = $data->coupon_amount;
	                $produceInfo[$k]['title'] = $data->title;
	                $produceInfo[$k]['unitCode'] = $data->unit_code;
	                $produceInfo[$k]['orderKeyFormated'] = $data->order_key_formated;
	                $produceInfo[$k]['weight'] = $data->weight;
	                $produceInfo[$k]['invoicePdf'] = $data->invoic_pdf;
	         
	                $weight = isset($data->weight)?$data->weight:$data->weight;
	                $produceInfo[$k]['weight'] =$weight;
	                $adjust_weight_qty= isset($data->adjust_weight_qty)?$data->adjust_weight_qty:"";
	                $weight_last = !empty($data->adjust_weight_qty)?$data->adjust_weight_qty:$data->weight;
	                $weight_last = !empty($data->adjust_weight_qty)?$data->adjust_weight_qty:$data->weight;
	                if ($data->adjust_weight == 1) {
	                    $qntyweight = $weight * $values->item_unit ;
	                    $weight_last = $adjust_weight_qty;
	                } else {
	                    $weight_last =$weight_last *$values->item_unit;
	                }
	                $itemprice =  $data->item_cost / $data->weight;
	                $amount =$weight_last * $itemprice;
	                if($deliverynew->replace == 1)  {
	                  $amount =  $valu->total;
	                }
	                if($amount !=0){$amounts = $amount;}else{$amounts= $valu->total;}
	                $produceInfo[$k]['totalAmount'] =number_format($amounts,2);
	                $produceInfo[$k]['adjustmentWeight'] = $adjust_weight_qty;
	                $produceInfo[$k]['adjust'] =0 ;
	                $produceInfo[$k]['netWeight'] =$data->weight * $data->item_unit ;
	                if ($data->adjust_weight_qty !=0 || $data->adjust_weight_qty !=null) {
	                    $produceInfo[$k]['adjust'] = 1;
	                }
	            $k++;
	            }
	        }
	    /*--Order Item details End--*/
	        
	    /*--outlet review details start--*/
	        $reviews = DB::table('outlet_reviews')
	            ->selectRaw('count(outlet_reviews.order_id) as reviewStatus')
	        	//->where("outlet_reviews.outlet_id","=",$reviews->outlet_id)
	            ->where("outlet_reviews.order_id", "=", (int)$orderId)
	            ->first();
	    /*--outlet review details end--*/

	  
	        
	    /*--tracking details start--*/

	        $tracking_orders = array(1 => "Initiated", 10 => "Processed", 18 => "Packed", 19 => "Dispatched", 12 => "Delivered");
	        $t =$y= 0;
	        $last_state = $mob_last_state = "";
	        $tracking_result = $mob_tracking_result = array();
	        foreach ($tracking_orders as $key => $track) {
	          /*	$mob_tracking_result[$t]['text'] = $track;
	            $mob_tracking_result[$t]['process'] = "0";
	            $mob_tracking_result[$t]['order_comments'] = "";
	            $mob_tracking_result[$t]['date'] = "";*/
	            $tracking_result[$key]['code'] = $key;
	            $tracking_result[$key]['text'] = $track;
	            $tracking_result[$key]['process'] = "0";
	            $tracking_result[$key]['order_comments'] = "";
	            $tracking_result[$key]['date'] = "";
	            $check_status = DB::table('orders_log')
	                ->select('order_id', 'log_time', 'order_comments')
	                ->where('order_id', '=', (int)$orderId)
	                ->where('order_status', '=', $key)
	                ->first();
	            //print_r($key);exit();
	            if (count($check_status) > 0) {
	                $last_state = $key;
	                $tracking_result[$key]['process'] = "1";
	                $tracking_result[$key]['orderComments'] = ($check_status->order_comments != '') ? $check_status->order_comments : '';
	                $tracking_result[$key]['date'] = date('M j Y g:i A', strtotime($check_status->log_time));
	                $mob_last_state = $t;
	                $mob_tracking_result[$y]['text'] = $track;
	                $mob_tracking_result[$y]['process'] = "1";
	                $mob_tracking_result[$y]['orderComments'] = $check_status->order_comments;
	                $mob_tracking_result[$y]['date'] = date('M j Y g:i A', strtotime($check_status->log_time));
	                $y++;
	            }
	            $t++;
	        }
	    /*--tracking details start--*/



	        $orderData = new \stdClass();
	        $orderData->orderId = (int)$orderId;

	        $order_info=DB::select("select SUM(item_unit) as item_unit from orders_info where order_id =".$orderId);

	        if (count($order_info)>0) {
	            $orderInfoArray=array();

	            foreach ($order_info as $keys => $vall) {
	                $orderInfoArray[$keys]['itemCount']= $vall->item_unit;
	            }
	        }	  // echo"<pre>";print_r($delivery_details);exit();

	        $orderData->orderQuantity = $vall->item_unit;
	        $orderData->orderComments = isset($delivery_details[0]->order_comments)?$delivery_details[0]->order_comments:"";
	        $orderData->salesFleetId = isset($delivery_details[0]->salesperson_id) ? $delivery_details[0]->salesperson_id:"" ;
	        $orderData->salesFleetName = isset($delivery_details[0]->salespersonname) ? $delivery_details[0]->salespersonname:"";
	        $orderData->outletName = isset($delivery_details[0]->vendor_name)?$delivery_details[0]->vendor_name:"";
	        $orderData->vendorLogo = isset($delivery_details[0]->logo_image)?$delivery_details[0]->logo_image:"";
	        $orderData->outletAddress = isset($delivery_details[0]->contact_address)?$delivery_details[0]->contact_address:"";;
	        $orderData->contactEmail = isset($delivery_details[0]->contact_email)?$delivery_details[0]->contact_email:"";;
	        $orderData->createdDate = isset($delivery_details[0]->created_date) ? $delivery_details[0]->created_date:"";
	        $orderData->orderStatus = isset($delivery_details[0]->order_status) ? $delivery_details[0]->order_status:"";
	        if($flag != 1){
	        $orderData->name = isset($delivery_details[0]->name) ? $delivery_details[0]->name:"";
	    	}else{
	    	$orderData->name = isset($vendor_info[0]->name) ?$vendor_info[0]->name: $delivery_details[0]->name;
	    	}
	        $orderData->paymentGatewayName = isset($delivery_details[0]->payment_gateway_name) ? $delivery_details[0]->payment_gateway_name:"";
	        $orderData->outletId = isset($delivery_details[0]->outlet_id) ? $delivery_details[0]->outlet_id:"";
			$orderData->vendorId =isset($delivery_details[0]->vendor_id) ? $delivery_details[0]->vendor_id:"";

	        $orderData->orderKeyFormated = isset($delivery_details[0]->order_key_formated) ? $delivery_details[0]->order_key_formated:"";		
	        $orderData->invoiceId = isset($delivery_details[0]->invoice_id) ? $delivery_details[0]->invoice_id:"";
	        $orderData->startTime = isset($delivery_details[0]->start_time) ? $delivery_details[0]->start_time:"";
	        $orderData->endTime = isset($delivery_details[0]->end_time) ? $delivery_details[0]->end_time:"";
	        $orderData->deliveryAddress = isset($delivery_details[0]->user_contact_address)?$delivery_details[0]->user_contact_address:'';
	        //print_r($orderData);exit();
	        $result = array("produceInfo" => $produceInfo, "deliverynew" => $deliverynew, "orderData" => $orderData ,"last_state" => $last_state, "mob_tracking_result" => $mob_tracking_result, "reviews" => $reviews, "tracking_result" => $tracking_result);
	        return $result;
	    	//print_r($result);exit();
    }

    
	function getPaymentDetails($id) {
		$defaultconfigs = DB::table('payment_gateways')
			->select('payment_gateways.*')
			->where('id', '=', $id)
			->get();
		$config_items = array();
		if (count($defaultconfigs) > 0) {
			$config_items = $defaultconfigs[0];
		}
		return $config_items;
	}

	function getCustPromotiondetails($id) {
		$defaultconfigs = DB::table('customer_promotion')
			->select('customer_promotion.*')
			->where('id', '=', $id)
			->get();
		$config_items = array();
		if (count($defaultconfigs) > 0) {
			$config_items = $defaultconfigs[0];
		}
		return $config_items;
	}

	function getOutletCategory()
    {
    	$category = DB::table('outlet_category')
    				->select('*')
    				->get();
    	//echo"<pre>";		print_r($category);exit();
    	return $category;
    }

    function getwalletQuickPay()
    {
    	$wallet_quick_pay = DB::table('wallet_quick_pay')
    				->select('*')
    				->where('id',1)
    				->get();
    				//print_r($wallet_quick_pay);exit();
    	return $wallet_quick_pay[0];
    }

  
/*
    function paymentHsitory($api,$cust_id) {
    	//print_r("expression");exit();
		$val =$api->getOutlets(2,$vendor_id);
		echo"<pre>";print_r($val->detail);exit();
		return $api->paymentHsitory($cust_id);
	}*/

	function getOutlets($api,$outlet_type,$vendor_id)
	{

        $vendor_id=   isset($vendor_id)?$vendor_id:0;
        $outlet_category=   isset($outlet_type)?$outlet_type:1;
       // print_r($outlet_category);//exit();
        $stores = DB::table('outlets')
                ->join('vendors','vendors.id','=','outlets.vendor_id')
                ->join('outlet_infos','outlet_infos.id','=','outlets.id')
                ->join('vendors_infos','vendors_infos.id','=','outlets.vendor_id')
                ->select('outlets.vendor_id as vendors_id ', 'vendors.first_name', 'vendors.last_name','outlets.id as outlets_id','vendors_infos.vendor_name', 'outlet_infos.outlet_name', 'vendors.featured_image', 'vendors.logo_image','vendors.delivery_time as vendors_delivery_time', 'vendors.average_rating as vendors_average_rating', 'vendors.featured_vendor',  'outlets.delivery_time as outlets_delivery_time','outlets.category_ids','outlets.latitude' , 'outlets.longitude', 'outlets.average_rating as outlets_average_rating','vendors_infos.vendor_description' , 'outlet_infos.contact_address','outlets.image as outletImage')
                ->where('outlets.outlet_category',$outlet_category)
                ->where('outlets.vendor_id',$vendor_id)
                ->get();

         return$stores;
	}

	function paymentHsitory($api,$cust_id)
	{
		//print_r($cust_id);exit();
		 $user_details= DB::table('users')->select('*')->where('users.id', '=', $cust_id)->get();

            //print_r($user_details[0]);exit();
            // [wallet_amount] => 450
            //[offer_wallet] => 500
            $payment_history_cashier = DB::table('payment_history')
                    ->join('outlets','payment_history.outlet_id','=','outlets.id')
                    ->join('outlet_infos','outlet_infos.id','=','outlets.id')
                    ->select('payment_history.id','payment_history.payment_type','payment_history.amount','payment_history.order_id','payment_history.created_date','payment_history.outlet_id','outlets.latitude' , 'outlets.longitude','outlet_infos.contact_address','outlet_infos.outlet_name','outlets.image','payment_history.offer_id')
                    ->where('payment_history.customer_id',$cust_id)
                    ->where('payment_history.payment_type',3)
                    ->orderBy('payment_history.created_date', 'desc')

                    ->get();
            $payment_history_wallet = DB::table('payment_history')
                    ->select('payment_history.id','payment_history.payment_type','payment_history.amount','payment_history.created_date','payment_history.outlet_id','payment_history.offer_id')
                    ->where('payment_history.customer_id',$cust_id)
                   ->where('payment_history.payment_type','!=',3)

                    ->orderBy('payment_history.created_date', 'desc')


                    ->get();
                   $payment_history_wallet = json_decode(json_encode($payment_history_wallet), true); 
                   $payment_history_cashier = json_decode(json_encode($payment_history_cashier), true); 

                          //  echo"<pre>";    print_r($payment_history_wallet);echo"<pre>"; print_r($payment_history_cashier);exit;

            /*merge and sort the array based on created date*/
            $payment_history=array_merge($payment_history_cashier,$payment_history_wallet);
            $sort = array();
            foreach($payment_history as $k=>$v) {

                $sort['created_date'][$k] = $v['created_date'];
            }

            if($payment_history){

                array_multisort($sort['created_date'], SORT_DESC, $payment_history);
            }
            /*merge and sort the array based on created date*/

            foreach ($payment_history as $key => $value) {
            	// echo"<prE>"; print_r($payment_history[$key]);exit();

                $payment_history[$key]['promotion_name'] = '';
                $payment_history[$key]['addition_promotion'] = '';
                $payment_history[$key]['label'] = "Payment Success";
                $payment_history[$key]['order_id'] = isset($value['order_id'])?$value['order_id']:0;
                $payment_history[$key]['type'] = "DE";

                $payment_history[$key]['base_amount'] = '';
                if($value['offer_id'])
                {
                    $offer_details = DB::table('customer_promotion')
                        ->select('customer_promotion.*')
                        ->where('customer_promotion.id',$value['offer_id'])
                        ->get();
                    $payment_history[$key]['promotion_name'] = isset($offer_details[0]->promotion_name)?$offer_details[0]->promotion_name:'';
                    $payment_history[$key]['addition_promotion'] = isset($offer_details[0]->addition_promotion)?$offer_details[0]->addition_promotion:'';
                    $payment_history[$key]['base_amount'] = isset($offer_details[0]->base_amount)?$offer_details[0]->base_amount:'';

                }
                $payment_history[$key]['currencyCode'] =CURRENCYCODE;
                $logo_image = URL::asset('assets/admin/base/images/online-store.png');
                $img = isset($value['image'])?$value['image']:'online-store.png';
                if (file_exists(base_path() . '/public/assets/admin/base/images/outlets/' . $img) && $value['image'] != '') {
                 $logo_image = URL::asset('assets/admin/base/images/outlets/' . $img);
                }
                if(!isset($value['outlet_name'])){
                    $payment_history[$key]['order_id'] ="";
                    if($value['payment_type'] == 1)
                    {
                        $payment_history[$key]['label'] = "Added Wallet";

                        $logo_image = URL::asset('assets/admin/base/images/credit-card.png');
                        $payment_history[$key]['outlet_name'] = "Add Wallet money";
                        $payment_history[$key]['order_id'] = "";
                        $payment_history[$key]['type'] = "CR";

                    }else{
                        $payment_history[$key]['label'] = "Payment Cancelled";

                        $logo_image = URL::asset('assets/admin/base/images/declain.png');
                        $payment_history[$key]['outlet_name'] = "Declained/Cancelled";
                        $payment_history[$key]['order_id'] = "";
                        $payment_history[$key]['type'] = "DE";


                    }
                    $payment_history[$key]['latitude'] = "";
                    $payment_history[$key]['longitude'] = "";
                    $payment_history[$key]['contact_address'] = "";

                } 
                $payment_history[$key]['image'] = $logo_image;

            }
            $wallet = isset($user_details[0]->wallet_amount)?$user_details[0]->wallet_amount:0;
            $offer_wallet = isset($user_details[0]->offer_wallet)?$user_details[0]->offer_wallet:0;
            $total_wallet = $wallet + $offer_wallet ;
      
            $result = array("status" => 1, "message" =>trans('messages.success'),"detail"=>$payment_history,"wallet_amount"=>$total_wallet,"grocery_wallet"=>$offer_wallet,"actualWalletBalance"=>$wallet);
            //echo"<pre>"; print_r(json_encode($result));exit();
            //return json_encode($result);
            return $result;
	}


	 function getpromotion()
	{
	  $promotion = DB::table('customer_promotion')
            ->select('*')
            ->where('customer_promotion.active_status','=',1)
            ->get();
        foreach ($promotion as $key => $value) {
            $imageName = URL::asset('assets/admin/base/images/default_avatar_male.jpg');

            if (file_exists(base_path() . '/public/assets/admin/base/images/customerPromotion/' . $value->image) && $value->image != '') {
                    $imageName = URL::to("/assets/admin/base/images/customerPromotion/" . $value->image . '?' . time());
                }
                $promotion[$key]->image = $imageName;
                $promotion[$key]->description = "";
                $promotion[$key]->conditions = isset($value->conditions)?$value->conditions:'';
            }
        $quickpay =getwalletQuickPay();

        $pay=array();
        array_push($pay,$quickpay->amount1,$quickpay->amount2,$quickpay->amount3);
        $result = array("status" => 1, "message" =>trans('messages.success'),"detail"=>$promotion,"quickpay"=>$pay);
        //print_r($result);exit();
        return $result;
	}  

	function user_detail($post_data)
	{

        $check_auth = JWTAuth::toUser($post_data['token']);
        $user = get_user_details($post_data['user_id']);
      //  print_r($user);exit();
        if (count($user) > 0) {
            $user->id = $post_data['user_id'];
            $user->first_name = ($user->first_name != '') ? $user->first_name : '';
            $user->mobile = ($user->mobile != '') ? $user->mobile : '';
            $user->last_name = ($user->last_name != '') ? $user->last_name : '';
            $user->civil_id = ($user->civil_id != '') ? $user->civil_id : '';
            $user->cooperative_id = ($user->cooperative_id != '') ? $user->cooperative_id : '';
            $user->cooperative = ($user->cooperative != '') ? $user->cooperative : '';
            $user->member_id = ($user->member_id != '') ? $user->member_id : '';
            $user->verfiy_pin = ($user->verfiy_pin != '') ? $user->verfiy_pin : '';
            $user->wallet = ($user->wallet_amount != '') ? $user->wallet_amount : '';
            $user->currency_code = CURRENCYCODE;
            $imageName = url('/assets/admin/base/images/default_avatar_male.jpg');
            if (file_exists(base_path() . '/public/assets/admin/base/images/admin/profile/' . $user->image) && $user->image != '') {
                $imageName = URL::to("assets/admin/base/images/admin/profile/" . $user->image);
            }
            $user->image = $imageName;
            $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.User details"), 'user_data' => array($user)));
        } else {
            $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.No user found")));
        }
                	//	echo"<pre>";print_r($result);exit();

        return $result;
	}

	function walletpayment($post_data)
	{
		$user_id = Session::get('user_id');
		$wallet_transaction = DB::table('payment_transaction')
			->select('*')
			->where('cart_id',$post_data['cart_id'])
			->get();
		if(count($wallet_transaction)){

			$dec = $wallet_transaction[0]->description;
          	//  $str = "AWM-123-2-1";//eg paymwnt name - user id -wallet type -offer id 
            $dec_explode =explode("-",$dec);
			$amount = isset($wallet_transaction[0]->amount)?$wallet_transaction[0]->amount:0;
			$wallet_type = isset($dec_explode[2])?$dec_explode[2]:0;
			$offer_id = isset($dec_explode[3])?$dec_explode[3]:0;
			//echo"<pre>";print_r($offer_id);exit();

            /**payment log**/
				if($post_data['result_type'] == 1)
            {  
                $payment_type =1;//success
            }else
            {
               $payment_type =2;//fail
            }

            $res = DB::table('payment_transaction')
                    ->where('cart_id', $post_data['cart_id'])
                    ->update(['payment_type' => $payment_type, 'wallet_type' => $wallet_type, 'customer_id' => $user_id, 'offer_id' => $offer_id]);
          
            /**payment log**/
           
            $result = array("status" => 1, "message" =>trans('messages.success'),"amount"=>$amount,'userId'=>$dec_explode[1],'walletType'=>$dec_explode[2],'offerId'=>$dec_explode[3]);
        }else{
        	$result = array("status" => 0,  "message" => trans('messages.Invalid '));

        }
        return $result;
	}



	/*function cart_info($user_id,$language_id)
	{

		$query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = '.$language_id.' and p.id = products_infos.id) > 0 THEN '.$language_id.' ELSE 1 END)';
		$vquery = 'vi.lang_id = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = '.$language_id.' and vn.id = vendors_infos.id) > 0 THEN '.$language_id.' ELSE 1 END)';
		$wquery = 'weight.lang_id = (case when (select count(*) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = '.$language_id.' and weight.id = weight_classes_infos.id) > 0 THEN '.$language_id.' ELSE 1 END)';
		$cart_items = DB::select('SELECT c.cart_id,c.user_id,c.store_id,c.outlet_id,c.cart_status,pi.product_name,pi.description,
									p.original_price,
									p.discount_price,
									p.weight_class_id,
									p.weight, 
									p.quantity AS product_qty,
									p.product_image,
									p.stock_status,
									p.id as product_id,
									p.category_id,
									p.sub_category_id,
									p.outlet_id,
									p.vendor_id,
									vi.vendor_name,
									vn.featured_image,
									out.minimum_order_amount,
									outin.outlet_name,
									out.delivery_charges_fixed,
									out.url_index,
									out.delivery_charges_variation,
									out.delivery_time,
									out.service_tax,cd.quantity,
									out.url_index,
									out.latitude,out.longitude,
									cd.cart_detail_id,
									vn.vendor_key, weight.unit, weight.title
									FROM cart c
									LEFT JOIN cart_detail cd ON c.cart_id = cd.cart_id
									LEFT JOIN products p ON p.id = cd.product_id
									LEFT JOIN products_infos PI ON pi.id = p.id
									LEFT JOIN outlets OUT ON out.id = c.outlet_id
									LEFT JOIN outlet_infos outin ON outin.id = out.id
									LEFT JOIN vendors vn ON out.vendor_id = vn.id
									LEFT JOIN vendors_infos VI ON vi.id = vn.id
									Left join weight_classes ON weight_classes.id = p.weight_class_id
									Left join weight_classes_infos weight ON weight.id =weight_classes.id
									where '.$query.' AND '.$vquery.' AND '.$wquery.' AND c.user_id = ? ORDER BY cd.cart_detail_id' , array($user_id));


		//$cart_items = DB::table('orders_info')->select('current_balance')->where('id', $user_id)->get();

		return $cart_items;
	
	}*/




