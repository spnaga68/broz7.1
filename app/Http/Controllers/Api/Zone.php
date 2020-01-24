<?php
namespace App\Http\Controllers\Api;
use App;
use App\Http\Controllers\Controller;
use App\Model\zones;
use DB;
use Dingo\Api\Http\Request;
DB::enableQueryLog();
use URL;

class Zone extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(Request $data) {
		$post_data = $data->all();
		if (isset($post_data['language']) && $post_data['language'] != '' && $post_data['language'] == 2) {
			App::setLocale('ar');
		} else {
			App::setLocale('en');
		}
	}

	/*
		     * student login
	*/
	public function getlocation($language_id) {
		$data = array();
		$result = array("response" => array("httpCode" => 400, "status" => false, "data" => $data));
		$query = '"zones_infos"."language_id" = (case when (select count(zones_infos.language_id) as totalcount from zones_infos where zones_infos.language_id = ' . $language_id . ' and zones.id = zones_infos.zone_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$zones = Zones::join('zones_infos', 'zones_infos.zone_id', '=', 'zones.id')
			->join('cities', 'cities.id', '=', 'zones.city_id')
			->select(DB::raw('zones.* ,zones.id as zid'), 'zones.url_index', 'zones_infos.*')
			->whereRaw($query)->get();
		if (count($zones)) {
			$result = array("response" => array("httpCode" => 200, "status" => true, 'data' => $zones));
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	/*
		     * student login
	*/
	public function getcity($language_id) {
		$data = array();
		$result = array("response" => array("httpCode" => 400, "status" => false, "data" => $data));
		$query = '"cities_infos"."language_id" = (case when (select count(*) as totalcount from cities_infos where cities_infos.language_id = ' . $language_id . ' and cities.id = cities_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$cities = DB::table('cities')
			->select(DB::raw('cities.* ,cities.id as cid'), 'cities_infos.*')
			->leftJoin('cities_infos', 'cities_infos.id', '=', 'cities.id')
			->whereRaw($query)
			->where('active_status', 'A')
			->where('default_status', 1)
			->orderBy('city_name', 'asc')
			->get();
		if (count($cities)) {
			$result = array("response" => array("httpCode" => 200, "status" => true, 'data' => $cities));
		} else {
			$result = array("response" => array("httpCode" => 200, 'Message' => 'No cities found'));
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	public function getcountry_select($language_id) {
		//DB::setFetchMode(PDO::FETCH_ASSOC);
		$data = array();
		$result = array("response" => array("httpCode" => 400, "status" => false, "data" => $data));
		$query = '"countries_infos"."language_id" = (case when (select count(*) as totalcount from countries_infos where countries_infos.language_id = ' . $language_id . ' and countries.id = countries_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$countries = DB::table('countries')
			->select(DB::raw('countries.id as cid'), 'countries_infos.country_name')
			->leftJoin('countries_infos', 'countries_infos.id', '=', 'countries.id')
			->whereRaw($query)
			->orderBy('country_name', 'asc')
			->get();

		if (count($countries)) {
			$result = array("response" => array("httpCode" => 200, "status" => true, 'data' => $countries));
		}

		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	/** get category list **/
	function getcountrybasedcity(Request $data) {
		$post_data = $data->all();
		$data = array();
		$rules = [
			'country_id' => ['required'],
			'language' => ['required'],
		];
		$errors = $result = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			$j = 0;
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("response" => array("httpCode" => 400, "status" => false, "Message" => $errors));
		} else {

			$language_id = $post_data["language"];
			$country_id = $post_data["country_id"];

			$data = array();
			$result = array("response" => array("httpCode" => 400, "status" => false, "data" => $data));
			$query = '"cities_infos"."language_id" = (case when (select count(*) as totalcount from cities_infos where cities_infos.language_id = ' . $language_id . ' and cities.id = cities_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
			$cities = DB::table('cities')
				->select(DB::raw('cities.* ,cities.id as cid'), 'cities_infos.*')
				->leftJoin('cities_infos', 'cities_infos.id', '=', 'cities.id')
				->whereRaw($query)
				->where('cities.country_id', '=', $country_id)
				->orderBy('city_name', 'asc')
				->get();
			if (count($cities)) {
				$result = array("response" => array("httpCode" => 200, "status" => true, 'data' => $cities));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	/*
		     * student login
	*/
	public function getapiLocationData(Request $data) {
		$post_data = $data->all();
		$data = array();

		$rules = [
			'language' => ['required'],
			'city_id' => ['required'],
		];
		$errors = $result = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			$j = 0;
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("response" => array("httpCode" => 400, "status" => false, "Message" => $errors));
		} else {
			//echo "asdf";exit;
			$language_id = $post_data["language"];
			$city_id = $post_data["city_id"];

			$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.No location found in city. Please change the city."), "status" => false, "data" => $data));
			$locations_query = '"zones_infos"."language_id" = (case when (select count(*) as totalcount from zones_infos where zones_infos.language_id = ' . $language_id . ' and zones.id = zones_infos.zone_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
			$locations = DB::table('zones')
				->select('zones.id', 'zones_infos.zone_name')
				->leftJoin('zones_infos', 'zones_infos.zone_id', '=', 'zones.id')
				->leftJoin('countries', 'countries.id', '=', 'zones.country_id')
				->leftJoin('cities', 'cities.id', '=', 'zones.city_id')
				->whereRaw($locations_query)
				->where('zones_status', 1)
				->where('cities.id', $city_id)
				->orderBy('zone_name', 'asc')
				->get();
			if (count($locations) > 0) {
				$result = array("response" => array("httpCode" => 200, "Message" => "Locations list", "status" => true, 'data' => $locations));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	/** get category list **/
	function getCategoryLists(Request $data) {
		$post_data = $data->all();
		$data = array();
		$rules = [
			'type' => ['required'],
			'language' => ['required'],
		];
		$errors = $result = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			$j = 0;
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("response" => array("httpCode" => 400, "status" => false, "Message" => $errors));
		} else {
			$language_id = $post_data["language"];
			$type = $post_data["type"];
			//Get the categories data
			$category_query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = ' . $language_id . ' and categories.id = categories_infos.category_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
			$categories = DB::table('categories')
				->select('categories.id', 'categories_infos.category_name', 'categories.url_key', 'categories.image', 'categories.mobile_banner_image')
				->leftJoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
				->whereRaw($category_query)
				->where('category_status', 1)
				->where('parent_id', 0) //getting parent categories only
				->where('category_type', $type)
				->orderBy('categories.sort_order', 'asc')
				->get();
			if (count($categories) > 0) {
				$category_list = array();
				$i = 0;
				foreach ($categories as $key => $value) {
					$logo_image = URL::asset('assets/admin/base/images/category/12.png');
					if (file_exists(base_path() . '/public/assets/admin/base/images/category/' . $value->image) && $value->image != '') {
						$logo_image = url('/assets/admin/base/images/category/' . $value->image . '?' . time());
					}
					$banner_image = URL::asset('assets/admin/base/images/category/mobile_banner/no_image.png');
					if (file_exists(base_path() . '/public/assets/admin/base/images/category/mobile_banner/' . $value->mobile_banner_image) && $value->mobile_banner_image != '') {
						$banner_image = url('/assets/admin/base/images/category/mobile_banner/' . $value->mobile_banner_image . '?' . time());
					}
					$category_list[$i]['id'] = $value->id;
					$category_list[$i]['category_name'] = $value->category_name;
					$category_list[$i]['url_key'] = $value->url_key;
					$category_list[$i]['logo_image'] = $logo_image;
					$category_list[$i]['banner_image'] = $banner_image;
					$i++;
				}
				$result = array("response" => array("httpCode" => 200, 'Message' => 'Category list', 'data' => $category_list));
			} else {
				$result = array("response" => array("httpCode" => 400, 'Message' => 'No category found'));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	//mob Apis:

	function mgetCategoryLevelLists(Request $data) {

		/*  $post_data = $data->all();
			        $data      = array();
			        $rules = [
			            'type'    => ['required'],
			            'language' => ['required'],
			        ];
			        $errors = $result = array();
			        $validator = app('validator')->make($post_data, $rules);
			        if ($validator->fails())
			        {
			            $j = 0;
			            foreach( $validator->errors()->messages() as $key => $value)
			            {
			                $errors[] = is_array($value)?implode( ',',$value ):$value;
			            }
			            $errors = implode( ", \n ", $errors );
			            $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => $errors));
			        }
		*/
		//  $language_id = $post_data["language"];
		//   $type       = $post_data["type"];
		$language_id = 1;
		$type = 2;
		//Get the categories data
		$category_query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = ' . $language_id . ' and categories.id = categories_infos.category_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$categories = DB::table('categories')
			->select('categories.id', 'categories_infos.description', 'categories_infos.category_id', 'categories.category_level', 'categories_infos.category_name', 'categories.image', 'categories.mobile_banner_image', 'categories.updated_at')
			->leftJoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
			->whereRaw($category_query)
			->where('category_status', 1)
		//getting parent categories only
			->where('category_type', $type)
			->orderBy('categories.sort_order', 'asc')
			->get();
		if (count($categories) > 0) {
			$category_list = array();
			$i = 0;
			foreach ($categories as $key => $value) {

				$logo_image = URL::asset('assets/admin/base/images/category/11.png');
				// if (file_exists(base_path() . '/public/assets/admin/base/images/category/' . $value->image) && $value->image != '') {
				$logo_image = url('/assets/admin/base/images/category/' . $value->image . '?' . $value->updated_at);
				// }
				$banner_image = URL::asset('assets/admin/base/images/category/mobile_banner/no_image.png');
				if (file_exists(base_path() . '/public/assets/admin/base/images/category/mobile_banner/' . $value->mobile_banner_image) && $value->mobile_banner_image != '') {
					$banner_image = url('/assets/admin/base/images/category/mobile_banner/' . $value->mobile_banner_image . '?' . $value->updated_at);
				}
				$category_list[$i]['id'] = $value->id;

				$category_list[$i]['description'] = $value->description;

				$category_list[$i]['categoryLevel'] = $value->category_level;
				$category_list[$i]['categoryName'] = $value->category_name;
				$category_list[$i]['categoryId'] = $value->category_id;

				// $category_list[$i]['url_key'] = $value->url_key;
				$category_list[$i]['logoImage'] = $logo_image;
				$category_list[$i]['bannerImage'] = $banner_image;

				$category_list[$i]['itemOffer'] = "";

				$sub_categories = DB::table('categories')
					->select('categories.id', 'categories_infos.description', 'categories_infos.category_id', 'categories.category_level', 'categories_infos.category_name', 'categories.image', 'categories.mobile_banner_image', 'categories.updated_at')
					->leftJoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
					->whereRaw($category_query)
					->where('category_status', 1)
					->where('category_level', 2)
					->where('parent_id', $value->id) //getting parent categories only
					->where('category_type', 1)
					->orderBy('categories.sort_order', 'asc')
					->get();

				$sub_category_list = array();
				$k = 0;
				foreach ($sub_categories as $key => $value) {
					$logo_image = URL::asset('assets/admin/base/images/category/11.png');
					if (file_exists(base_path() . '/public/assets/admin/base/images/category/' . $value->image) && $value->image != '') {
						$logo_image = url('/assets/admin/base/images/category/' . $value->image . '?' . $value->updated_at);
					}
					$banner_image = URL::asset('assets/admin/base/images/category/mobile_banner/no_image.png');
					if (file_exists(base_path() . '/public/assets/admin/base/images/category/mobile_banner/' . $value->mobile_banner_image) && $value->mobile_banner_image != '') {
						$banner_image = url('/assets/admin/base/images/category/mobile_banner/' . $value->mobile_banner_image . '?' . $value->updated_at);
					}
					$sub_category_list[$k]['id'] = $value->id;

					$sub_category_list[$k]['description'] = $value->description;

					$sub_category_list[$k]['categoryLevel'] = $value->category_level;
					$sub_category_list[$k]['categoryName'] = $value->category_name;
					$sub_category_list[$k]['categoryId'] = $value->category_id;

					// $category_list[$i]['url_key'] = $value->url_key;
					$sub_category_list[$k]['logoImage'] = $logo_image;
					$sub_category_list[$k]['bannerImage'] = $banner_image;

					$sub_category_list[$k]['itemOffer'] = "";

					$child_categories = DB::table('categories')
						->select('categories.id', 'categories_infos.description', 'categories_infos.category_id', 'categories.category_level', 'categories_infos.category_name', 'categories.image', 'categories.mobile_banner_image')
						->leftJoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
						->whereRaw($category_query)
						->where('category_status', 1)
						->where('category_level', 3)
					// ->where('parent_id',$value->id)//getting parent categories only
						->where('head_category_ids', $value->id)
						->where('category_type', 1)
						->orderBy('categories.sort_order', 'asc')
						->get();

					$child_categories_list = array();
					$l = 0;
					foreach ($child_categories as $key => $value) {

						$child_categories_list[$l]['id'] = $value->id;

						$child_categories_list[$l]['description'] = $value->description;

						$child_categories_list[$l]['categoryName'] = $value->category_name;
						$child_categories_list[$l]['categoryId'] = $value->category_id;

						$l++;
					}

					$sub_category_list[$k]['childCategories'] = $child_categories_list;

					$k++;
				}

				$category_list[$i]['subCategories'] = $sub_category_list;
				$i++;

			}
			$result = array("status" => 1, 'message' => 'Category list', 'data' => $category_list);
		} else {
			$result =  array("status" => 2, 'message' => 'No category found');
		}
		//}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
		// return "";
		//echo "$category_query";
	}

}
