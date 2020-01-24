<?php

namespace App\Model;

use App\Model\products;
use App\Model\admin_products;
use App\Model\vendors;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use URL;

class stores extends Model {
	/* store vendor information */

	public static function vendor_information($vendor_id) {
		$query = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . getCurrentLang() . ' and vendors.id = vendors_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
		$vendor = Vendors::leftJoin('vendors_infos', 'vendors_infos.id', '=', 'vendors.id')
			->select('vendors_infos.vendor_name', 'vendors_infos.vendor_description')
			->whereRaw($query)
			->where('vendors_infos.id', '=', $vendor_id)
			->first();
		return $vendor;
	}

	/* store vendor user fav */

	public static function vendor_fav_info($user_id, $vendor_id) {
		$v_fav = DB::table('favorite_vendors')
			->select('id', 'status')
			->where('customer_id', '=', $user_id)
			->where('vendor_id', '=', $vendor_id)
			->where('status', '=', 1)
			->first();
		return $v_fav;
	}

	/* store information */

	public static function store_information($language_id, $store_id) {
		$query = 'vendors_infos.lang_id = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$query2 = 'zones_infos.language_id = (case when (select count(zones_infos.language_id) as totalcount from zones_infos where zones_infos.language_id = ' . $language_id . ' and zones.id = zones_infos.zone_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$store_info = DB::table('vendors')
			->Leftjoin('vendors_infos', 'vendors_infos.id', '=', 'vendors.id')
			->Leftjoin('outlets', 'outlets.vendor_id', '=', 'vendors.id')
			->Leftjoin('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
			->Leftjoin('zones', 'zones.id', '=', 'outlets.location_id')
			->Leftjoin('zones_infos', 'zones_infos.zone_id', '=', 'zones.id')
			->select('vendors.id as vendors_id', 'vendors_infos.vendor_name', 'vendors.first_name', 'vendors.last_name', 'vendors.featured_image', 'vendors.logo_image', 'vendors.delivery_time as vendors_delivery_time', 'vendors.category_ids', 'vendors.average_rating as vendors_average_rating', 'outlet_infos.contact_address as outlets_contact_address', 'outlets.id as outlets_id', 'outlets.vendor_id as outlets_vendors_id', 'outlet_infos.outlet_name', 'outlets.delivery_time as outlets_delivery_time', 'outlets.average_rating as outlets_average_rating', 'outlets.delivery_charges_fixed as outlets_delivery_charges_fixed', 'outlets.delivery_charges_variation as outlets_delivery_charges_variation', 'outlets.pickup_time as outlets_pickup_time', 'outlets.latitude as outlets_latitude', 'outlets.longitude as outlets_longitude', 'outlets.minimum_order_amount', 'zones_infos.zone_name as outlet_location_name')
			->whereRaw($query)
			->whereRaw($query1)
			->whereRaw($query2)
			->where('vendors.active_status', '=', 1)
			->where('vendors.featured_vendor', '=', 1)
			->where('outlets.id', '=', $store_id)
			->first();
		return $store_info;
	}

	/* user cart information */

	public static function user_cart_information($user_id) {
		$c_data = DB::table('cart')
			->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
			->select('cart_detail.cart_id', DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
			->where('cart.user_id', '=', $user_id)
			->groupby('cart_detail.cart_id')
			->first();
		return $c_data;
	}

	/* sub category list */

	public static function get_sub_category_list($main_category_id) {
		$category_query = '"categories_infos"."language_id" = (case when (select count(categories_infos.category_id) as totalcount from categories_infos where categories_infos.language_id = ' . getCurrentLang() . ' and categories.id = categories_infos.category_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
		$s_categories = DB::table('categories')
			->select('categories.id', 'categories_infos.category_name', 'categories.url_key', 'categories.image')
			->leftJoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
			->whereRaw($category_query)
			->where('category_status', 1)
			->where('category_level', 2)
			->where('parent_id', $main_category_id)
			->orderBy('categories_infos.category_name', 'asc')
			->get();
		return $s_categories;
	}

	/* outlet review */

	/*public static function outlet_reviews($store_id, $outlet_id ,$pageSize,$skipSize,$startDate,$endDate,$rating) {

		if(($rating=="") AND ($startDate=="") AND ($endDate=="")){
		$reviews = DB::table('outlet_reviews')
			->select('outlet_reviews.id as review_id', 'outlet_reviews.title', 'outlet_reviews.comments', 'outlet_reviews.ratings', 'outlet_reviews.created_date', 'users.id', 'users.first_name', 'users.last_name', 'users.name', 'users.image')
			->leftJoin('users', 'users.id', '=', 'outlet_reviews.customer_id')
			->where('outlet_reviews.outlet_id', '=', $outlet_id)
			->where('outlet_reviews.vendor_id', '=', $store_id)
			->where('outlet_reviews.approval_status', '=', 1)
			//->where('outlet_reviews.ratings', '=', $rating)
			//->whereBetween('outlet_reviews.created_date',[$startDate,$endDate])
			->orderBy('outlet_reviews.id', 'desc')
			->limit($pageSize)
            ->skip($skipSize)
			->get();
			            //print_r($reviews);exit();
		}elseif(($rating!=="") AND ($startDate=="") AND ($endDate=="")){

			$reviews = DB::table('outlet_reviews')
            ->select('outlet_reviews.id as review_id', 'outlet_reviews.title', 'outlet_reviews.comments', 'outlet_reviews.ratings', 'outlet_reviews.created_date', 'users.id', 'users.first_name', 'users.last_name', 'users.name', 'users.image')
            ->leftJoin('users', 'users.id', '=', 'outlet_reviews.customer_id')
            ->where('outlet_reviews.outlet_id', '=', $outlet_id)
            ->where('outlet_reviews.vendor_id', '=', $store_id)
            ->where('outlet_reviews.approval_status', '=', 1)
            ->where('outlet_reviews.ratings', '=', $rating)
            //->whereBetween('outlet_reviews.created_date',[$startDate,$endDate])
            ->orderBy('outlet_reviews.id', 'desc')
            ->limit($pageSize)
            ->skip($skipSize)
            ->get();


		}elseif(($rating=="") AND ($startDate!=="") AND ($endDate!=="")){

			$reviews = DB::table('outlet_reviews')
            ->select('outlet_reviews.id as review_id', 'outlet_reviews.title', 'outlet_reviews.comments', 'outlet_reviews.ratings', 'outlet_reviews.created_date', 'users.id', 'users.first_name', 'users.last_name', 'users.name', 'users.image')
            ->leftJoin('users', 'users.id', '=', 'outlet_reviews.customer_id')
            ->where('outlet_reviews.outlet_id', '=', $outlet_id)
            ->where('outlet_reviews.vendor_id', '=', $store_id)
            ->where('outlet_reviews.approval_status', '=', 1)
            //->where('outlet_reviews.ratings', '=', $rating)
            ->whereBetween('outlet_reviews.created_date',[$startDate,$endDate])
            ->orderBy('outlet_reviews.id', 'desc')
            ->limit($pageSize)
            ->skip($skipSize)
            ->get();


		}else{

			$reviews = DB::table('outlet_reviews')
            ->select('outlet_reviews.id as review_id', 'outlet_reviews.title', 'outlet_reviews.comments', 'outlet_reviews.ratings', 'outlet_reviews.created_date', 'users.id', 'users.first_name', 'users.last_name', 'users.name', 'users.image')
            ->leftJoin('users', 'users.id', '=', 'outlet_reviews.customer_id')
            ->where('outlet_reviews.outlet_id', '=', $outlet_id)
            ->where('outlet_reviews.vendor_id', '=', $store_id)
            ->where('outlet_reviews.approval_status', '=', 1)
            ->where('outlet_reviews.ratings', '=', $rating)
            ->whereBetween('outlet_reviews.created_date',[$startDate,$endDate])
            ->orderBy('outlet_reviews.id', 'desc')
            ->limit($pageSize)
            ->skip($skipSize)
            ->get();


		}
		return $reviews;
	}*/
public static function outlet_reviews($store_id, $outlet_id ,$pageSize,$skipSize,$startDate,$endDate,$rating) {

		if(($rating=="") AND ($startDate=="") AND ($endDate=="")){
			
			$raw = 'outlet_reviews.outlet_id = '.$outlet_id ;
			$raw.= 'and outlet_reviews.vendor_id ='.$store_id;
			$raw .= 'and outlet_reviews.approval_status ='. 1;
		}
		elseif(($rating!=="") AND ($startDate=="") AND ($endDate=="")){

			$raw = 'outlet_reviews.outlet_id = '.$outlet_id ;
			$raw.= 'and outlet_reviews.vendor_id ='.$store_id;
			$raw .= 'and outlet_reviews.approval_status ='. 1;
			$raw.='and outlet_reviews.ratings ='.$rating;
		}
		elseif(($rating=="") AND ($startDate!=="") AND ($endDate!=="")){
			$raw = 'outlet_reviews.outlet_id = '.$outlet_id ;
			$raw.= 'and outlet_reviews.vendor_id ='.$store_id;
			$raw .= 'and outlet_reviews.approval_status ='. 1;
			$raw.=" and outlet_reviews.created_date BETWEEN '".$startDate."' and '".$endDate."'";

		}elseif(($rating!=="") AND ($startDate!=="") AND ($endDate!=="")){
			$raw = 'outlet_reviews.outlet_id = '.$outlet_id ;
			$raw.= 'and outlet_reviews.vendor_id ='.$store_id;
			$raw .= 'and outlet_reviews.approval_status ='. 1;
			$raw.='and outlet_reviews.ratings ='.$rating;
			$raw.=" and outlet_reviews.created_date BETWEEN '".$startDate."' and '".$endDate."'";
		}
		$reviews = DB::table('outlet_reviews')
			->select('outlet_reviews.id as review_id', 'outlet_reviews.title', 'outlet_reviews.comments', 'outlet_reviews.ratings', 'outlet_reviews.created_date', 'users.id', 'users.first_name', 'users.last_name', 'users.name', 'users.image')
			->leftJoin('users', 'users.id', '=', 'outlet_reviews.customer_id')
			
			->whereRaw($raw)
			->orderBy('outlet_reviews.id', 'desc')
			->limit($pageSize)
            ->skip($skipSize)
			->get();
			           
		return $reviews;
	}

	/*public static function product_list_mob($language_id, $store_id, $outlet_id, $category_id, $sub_category_id, $product_name, $product_sub_category_id) {
		$condtion = "1 = 1";
		if ($product_name != "") {
			$condtion = " products_infos.product_name ILIKE '%" . $product_name . "%'";
		}

		if ($product_sub_category_id != "") {

			$condtion .= " and products.sub_category_id ='" . $product_sub_category_id . "'";
		}

		//  echo $condtion; exit;

		$pquery = '"products_infos"."lang_id" = (case when (select count(products_infos.id) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and products.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$cquery = '"categories_infos"."language_id" = (case when (select count(categories_infos.category_id) as totalcount from categories_infos where categories_infos.language_id = ' . $language_id . ' and categories.id = categories_infos.category_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$wquery = '"weight_classes_infos"."lang_id" = (case when (select count(weight_classes_infos.id) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . $language_id . ' and weight_classes.id = weight_classes_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$products = Products::join('products_infos', 'products.id', '=', 'products_infos.id')
			->join('categories', 'categories.id', '=', 'products.category_id')
			->Leftjoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
			->join('weight_classes', 'weight_classes.id', '=', 'products.weight_class_id')
			->Leftjoin('outlets', 'outlets.id', '=', 'products.outlet_id')
			->Leftjoin('outlet_infos', 'outlets.id', '=', 'outlet_infos.id')
			->Leftjoin('weight_classes_infos', 'weight_classes_infos.id', '=', 'weight_classes.id')
			->select('products.id as product_id','products.barcode', 'products.product_url', 'products.sub_category_id', 'products.product_image', 'products.product_info_image', 'products.product_zoom_image', 'products.weight', 'products.original_price', 'products.discount_price', 'products.vendor_id', 'products.outlet_id', 'products_infos.description', 'products_infos.product_name', 'categories_infos.category_name', 'categories.id', 'weight_classes_infos.unit', 'weight_classes_infos.title', 'outlet_infos.outlet_name', 'outlets.average_rating', 'outlets.id as outlet_id','item_limit as item_limit')
			->whereRaw($query1)
			->whereRaw($pquery)
			->whereRaw($cquery)
			->whereRaw($wquery)
			->whereRaw($condtion)
			->where('products.outlet_id', '=', $outlet_id)
			->where('products.vendor_id', '=', $store_id)
			->where('products.sub_category_id', '=', $sub_category_id)
			->where('products.active_status', '=', 1)
			->where('products.approval_status', '=', 1)
			->orderBy('categories_infos.category_name', 'asc')
			->get();
			 // print_r($products);
				//           echo " ";
				//           echo $outlet_id;
				//           echo " ";
				//           echo $store_id;
				//           echo " ";
				//           echo $sub_category_id;
			
		return $products;
	}*/

	// New ProductList::
	public static function product_list_mob($language_id, $store_id, $outlet_id, $child_category_id, $sub_category_id, $product_name, $product_sub_category_id) {
		$condtion = "1 = 1";
		if ($product_name != "") {
			$condtion = " admin_products.product_name ILIKE '%" . $product_name . "%'";
		}

		if ($product_sub_category_id != "") {

			$condtion .= " and admin_products.child_category_id ='" . $product_sub_category_id . "'";
		}

		//  echo $condtion; exit;

		$pquery = '"admin_products"."lang_id" = (case when (select count(admin_products.id) as totalcount from admin_products where admin_products.lang_id = ' . $language_id . ' and outlet_products.product_id = admin_products.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

		$cquery = '"categories_infos"."language_id" = (case when (select count(categories_infos.category_id) as totalcount from categories_infos where categories_infos.language_id = ' . $language_id . ' and categories.id = categories_infos.category_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

		$wquery = '"weight_classes_infos"."lang_id" = (case when (select count(weight_classes_infos.id) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . $language_id . ' and weight_classes.id = weight_classes_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

		$query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and outlet_products.outlet_id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		//print_r("expression");exit();

		$products = Admin_products::join('outlet_products', 'admin_products.id', '=', 'outlet_products.product_id')
			->join('categories', 'categories.id', '=', 'admin_products.sub_category_id')
			->Leftjoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
			->join('weight_classes', 'weight_classes.id', '=', 'admin_products.weight_class_id')
			->Leftjoin('outlets', 'outlets.id', '=', 'outlet_products.outlet_id')
			->Leftjoin('outlet_infos', 'outlets.id', '=', 'outlet_infos.id')
			->Leftjoin('weight_classes_infos', 'weight_classes_infos.id', '=', 'weight_classes.id')
			->select('admin_products.id as product_id','admin_products.barcode', 'admin_products.product_url', 'admin_products.sub_category_id', 'admin_products.image', 'admin_products.image as product_info_image', 'admin_products.image as product_zoom_image', 'admin_products.weight','outlet_products.original_price as original_price', 'outlet_products.discount_price', 'outlet_products.vendor_id', 'admin_products.description', 'admin_products.product_name', 'categories_infos.category_name', 'categories.id', 'weight_classes_infos.unit', 'weight_classes_infos.title','outlet_infos.outlet_name', 'outlets.average_rating', 'outlet_products.outlet_id as outlet_id','item_limit as item_limit')
			->whereRaw($query1)
			->whereRaw($pquery)
			->whereRaw($cquery)
			->whereRaw($wquery)
			->whereRaw($condtion)
			->where('outlet_products.outlet_id', '=', $outlet_id)
			->where('outlet_products.vendor_id', '=', $store_id)
			->where('admin_products.child_category_id', '=', $child_category_id)
			->where('admin_products.status', '=', 1)
			->where('outlet_products.admin_status', '=', 1)
			->orderBy('categories_infos.category_name', 'asc')
			->get();
		// print_r($products);exit();
		return $products;
	}

	public static function get_all_outlet_id_by_store_copy($store_id, $latitude, $longitude) {

		$query = 'earth_box(ll_to_earth(' . $latitude . ',' . $longitude . '), 5000) @> ll_to_earth(outlets.latitude, outlets.longitude)';

		$orderby = 'distance';

		$outlet_ids = DB::table('outlets')

			->select('outlets.id as outlets_id', 'outlets.vendor_id as outlet_vendor_id', 'outlet_infos.outlet_name as outletName',
				'outlet_infos.contact_address as contactAddress', 'outlets.delivery_time as deliveryTime', 'outlets.average_rating as averageRating'
				, DB::raw('earth_distance(ll_to_earth(' . $latitude . ',' . $longitude . '), ll_to_earth(outlets.latitude, outlets.longitude)) as distance'))
			->join('outlet_infos', 'outlets.id', '=', 'outlet_infos.id')

			->where('vendor_id', '=', $store_id)
			->whereRaw($query)
			->orderByRaw($orderby)
			->get();
		//print_r("$outlet_ids[0].outlets_id");exit;
		return $outlet_ids;
	}

/*ll_to_earth=>using mysql query: */

	// 	$commaStr = !empty($orderby) ? ',' : '';
	// 	$orderby = $commaStr . 'distance';

	// 	$query = 'earth_box(ll_to_earth(' . $latitude . ',' . $longitude . '), 6371) @> ll_to_earth(outlets.latitude, outlets.longitude)';

	// 	//$store_id = "vendor_id";

	// 	$outlet_ids = DB::select("select outlets.id as outlets_id,'outlets.vendor_id as outlet_vendor_id', outlet_infos.outlet_name as outletName,outlet_infos.contact_address as contactAddress,  outlets.delivery_time as deliveryTime, outlets.average_rating as averageRating
	// 		,earth_distance(ll_to_earth( " . $latitude . " , " . $longitude . " ), ll_to_earth(outlets.latitude , outlets.longitude)) as distance
	// 	 from outlets  LEFT JOIN outlet_infos ON outlet_infos.id = outlets.id LEFT JOIN vendors_infos ON vendors_infos.id = vendor_id   where " . $query . "  ORDER BY " . $orderby . "");
	// 	return $outlet_ids;

/* outlet id by store id */

	public static function get_all_outlet_id_by_store($store_id) {
		$outlet_ids = DB::table('outlets')
			->join('outlet_infos', 'outlets.id', '=', 'outlet_infos.id')

			->select('outlets.id as outlets_id', 'outlets.vendor_id as outlet_vendor_id', 'outlet_infos.outlet_name as outletName',
				'outlet_infos.contact_address as contactAddress', 'outlets.delivery_time as deliveryTime', 'outlets.average_rating as averageRating')
			->where('vendor_id', '=', $store_id)
			->get();
		//print_r("$outlet_ids[0].outlets_id");exit;
		return $outlet_ids;
	}

	/* vendor product list */

	public static function product_list($language_id, $store_id, $outlet_id, $category_id, $sub_category_id) {
		$pquery = '"products_infos"."lang_id" = (case when (select count(products_infos.id) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and products.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$cquery = '"categories_infos"."language_id" = (case when (select count(categories_infos.category_id) as totalcount from categories_infos where categories_infos.language_id = ' . $language_id . ' and categories.id = categories_infos.category_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$wquery = '"weight_classes_infos"."lang_id" = (case when (select count(weight_classes_infos.id) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . $language_id . ' and weight_classes.id = weight_classes_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$products = Products::join('products_infos', 'products.id', '=', 'products_infos.id')
			->join('categories', 'categories.id', '=', 'products.category_id')
			->Leftjoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
			->join('weight_classes', 'weight_classes.id', '=', 'products.weight_class_id')
			->Leftjoin('outlets', 'outlets.id', '=', 'products.outlet_id')
			->Leftjoin('outlet_infos', 'outlets.id', '=', 'outlet_infos.id')
			->Leftjoin('weight_classes_infos', 'weight_classes_infos.id', '=', 'weight_classes.id')
			->select('products.id as product_id', 'products.product_url', 'products.product_image', 'products.weight', 'products.original_price', 'products.discount_price', 'products.vendor_id', 'products.outlet_id', 'products_infos.description', 'products_infos.product_name', 'categories_infos.category_name', 'categories.id', 'weight_classes_infos.unit', 'weight_classes_infos.title', 'outlet_infos.outlet_name', 'outlets.average_rating')
			->whereRaw($query1)
			->whereRaw($pquery)
			->whereRaw($cquery)
			->whereRaw($wquery)
			->where('products.outlet_id', '=', $outlet_id)
			->where('products.vendor_id', '=', $store_id)
			->where('products.category_id', '=', $category_id)
			->where('products.sub_category_id', '=', $sub_category_id)
			->where('products.active_status', '=', 1)
			->where('products.approval_status', '=', 1)
			->orderBy('categories_infos.category_name', 'asc')
			->get();
		return $products;
	}

	/* To get the stores list */

	public static function stores_list($language, $category_ids, $category_url, $city, $location, $keyword, $sortby, $orderby) {
		$query = 'vendors_infos.lang_id = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
		//$query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount1 from outlet_infos where outlet_infos.language_id = '.$language.' and outlets.id = outlet_infos.id) > 0 THEN '.$language.' ELSE 1 END)';
		$condition = "vendors.active_status = '1' ";
		$orderby1 = 'vendors.id ASC';
		if ($city != '') {
			$condition .= ' and outlets.city_id = ' . $city;
		}
		if ($location != '') {
			$condition .= ' and outlets.location_id = ' . $location;
		}
		if ($category_ids != '') {
			//~ $condition .=" and (regexp_split_to_array(category_ids,',')::integer[] @> '{".$category_ids."}'::integer[]  and category_ids !='')";
			$c_ids = $category_ids;
			$c_ids = explode(",", $c_ids);
			$c_ids = implode($c_ids, "','");
			$c_ids = "'" . $c_ids . "'";
			$condition .= " and vendor_category_mapping.category in($c_ids)";
		}
		if ($keyword != '') {
			$keyword = pg_escape_string($keyword);
			$condition .= " and vendors_infos.vendor_name ILIKE '%" . $keyword . "%'";
		}
		if ($sortby == "delivery_time") {
			$orderby1 = 'vendors.delivery_time ' . $orderby;
		}
		if ($sortby == "rating") {
			$orderby1 = 'vendors.average_rating ' . $orderby;
		}

		$stores = DB::table('vendors')
			->select('vendors.id as vendors_id', 'vendors_infos.vendor_name', 'vendors.first_name', 'vendors.last_name', 'vendors.featured_image', 'vendors.logo_image', 'vendors.delivery_time as vendors_delivery_time', 'vendors.category_ids', 'vendors.average_rating as vendors_average_rating', 'vendors.contact_address', 'vendors_infos.vendor_description', 'vendors.delivery_charges_fixed', 'vendors.delivery_cost_variation', 'outlets.minimum_order_amount')
			->join('outlets', 'outlets.vendor_id', '=', 'vendors.id')
		//->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
			->join('vendors_infos', 'vendors_infos.id', '=', 'vendors.id')
			->join('vendor_category_mapping', 'vendor_category_mapping.vendor_id', '=', 'vendors.id')
			->where('vendors.featured_vendor', '=', 1)
			->where('outlets.active_status', '=', '1')
			->whereRaw($query)
		//->whereRaw($query1)
			->whereRaw($condition)
			->orderByRaw($orderby1)
			->groupby('vendors.id', 'vendors_infos.vendor_name', 'vendors_infos.vendor_description', 'outlets.minimum_order_amount')
			->get();
		//->toSql();
		//print_r($stores);die;
		return $stores;
	}

	/* outlet count */

	public static function get_outlet_count($store_id, $city = '', $location = '') {
		$condition = 'vendor_id = ' . $store_id;
		if ($city != '') {
			$condition .= ' and outlets.city_id = ' . $city;
		}
		if ($location != '') {
			$condition .= ' and outlets.location_id = ' . $location;
		}
		$outlet_count = DB::table('outlets')
			->select(DB::raw('count(outlets.id) as outlets_count')) //'outlets.id as outlets_id', 'outlets.vendor_id as outlet_vendor_id',
			->whereRaw($condition)
			->first();
		return $outlet_count;
	}

	/* outlet id by store id */

	public static function get_outlet_id_by_store($store_id) {
		$outlet_ids = DB::table('outlets')
			->select('outlets.id as outlets_id', 'outlets.vendor_id as outlet_vendor_id')
			->where('vendor_id', '=', $store_id)
			->first();
		return $outlet_ids;
	}

	/* outlet list */

	public static function get_outlet_list($store_id, $language, $city = '', $location = '') {
		$query = 'vendors_infos.lang_id = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
		$query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
		$query2 = 'zones_infos.language_id = (case when (select count(zones_infos.language_id) as totalcount from zones_infos where zones_infos.language_id = ' . $language . ' and zones.id = zones_infos.zone_id) > 0 THEN ' . $language . ' ELSE 1 END)';
		$condition = 'vendor_id = ' . $store_id;
		if ($city != '') {
			$condition .= ' and outlets.city_id = ' . $city;
		}
		if ($location != '') {
			$condition .= ' and outlets.location_id = ' . $location;
		}
		$outlet_list = DB::table('outlets')
			->select('outlets.id as outlets_id', 'outlets.vendor_id as outlets_vendors_id', 'outlet_infos.outlet_name', 'outlet_infos.contact_address', 'outlets.delivery_time as outlets_delivery_time', 'outlets.average_rating as outlets_average_rating', 'vendors_infos.vendor_name', 'vendors.logo_image', 'vendors.category_ids', 'outlets.delivery_charges_fixed', 'outlets.delivery_charges_variation', 'outlets.active_status', 'outlets.minimum_order_amount', 'zones_infos.zone_name as outlet_location_name')
			->join('outlet_infos', 'outlets.id', '=', 'outlet_infos.id')
			->join('vendors', 'outlets.vendor_id', '=', 'vendors.id')
			->join('vendors_infos', 'vendors_infos.id', '=', 'vendors.id')
			->join('zones', 'zones.id', '=', 'outlets.location_id')
			->join('zones_infos', 'zones_infos.zone_id', '=', 'zones.id')
			->whereRaw($query)
			->whereRaw($query1)
			->whereRaw($query2)
			->whereRaw($condition)
			->where('outlets.active_status', '=', 1)
			->groupBy('outlets.id', 'vendors_infos.vendor_name', 'vendors.logo_image', 'vendors.category_ids', 'outlet_infos.outlet_name', 'outlet_infos.contact_address', 'zones_infos.zone_name')
			->orderBy('outlets.created_date', 'asc')
			->get();
		return $outlet_list;
	}

	/* To get the stores list */

	public static function feature_stores_list($language, $category_ids, $category_url, $city, $location, $keyword, $sortby, $orderby) {
		$query = 'vendors_infos.lang_id = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
		$condition = "vendors.active_status = '1' ";
		$query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
		$orderby = 'vendors.id ASC';
		if ($city != '') {
			$condition .= ' and outlets.city_id = ' . $city;
		}
		if ($location != '') {
			$condition .= ' and outlets.location_id = ' . $location;
		}
		if ($category_ids != '') {
			$condition .= " and (regexp_split_to_array(category_ids,',')::integer[] @> '{" . $category_ids . "}'::integer[]  and category_ids !='')";
		}
		if ($keyword != '') {
			$condition .= " and vendors_infos.vendor_name ILIKE '%" . $keyword . "%'";
		}
		if ($sortby == "delivery_time") {
			$orderby = 'vendors.delivery_time ' . $orderby;
		}
		if ($sortby == "rating") {
			$orderby = 'vendors.average_rating ' . $orderby;
		}

		$stores = DB::table('vendors')
			->select('vendors.id as vendors_id', 'vendors_infos.vendor_name', 'vendors.first_name', 'vendors.last_name', 'vendors.featured_image', 'vendors.logo_image', 'vendors.delivery_time as vendors_delivery_time', 'vendors.category_ids', 'vendors.average_rating as vendors_average_rating', 'vendors.featured_vendor', 'outlet_infos.contact_address', 'vendors_infos.vendor_description')
			->join('outlets', 'outlets.vendor_id', '=', 'vendors.id')
			->leftjoin('outlet_infos', 'outlets.id', '=', 'outlet_infos.id')
			->leftjoin('vendors_infos', 'vendors_infos.id', '=', 'vendors.id')
			->whereRaw($query)
			->whereRaw($query1)
			->whereRaw($condition)
			->where('vendors.featured_vendor', '=', 1)
			->orderByRaw($orderby)
			->groupby('vendors_id', 'vendors_infos.vendor_name', 'outlet_infos.contact_address', 'vendors_infos.vendor_description')
			->get();
		return $stores;
	}

	public static function nearest_stores_list($language, $category_ids, $category_url, $city, $location, $latitude, $longitude, $keyword, $sortby, $orderby) {

		//$convert1 = "  vendors.latitude convert(character varying(32),double precision) ";
		//$convert2 = "  vendors.longitude convert(character varying(32),double precision) ";

		$convert1 = '11.0238';

		$convert2 = '77.0197';

		// $get1 = DB::select("select latitude from vendors");
		// $get2 = DB::select("select longitude from vendors");

		// $conver1 = "select CAST("$get1 or int)"";
		// $conver2 = "select  CAST("$get2 or int)"";

		// $convert1 = "select  CAST("$conver1 or float)"";
		// $convert2 = "select CAST("$conver2 or float)"";

		//print_r($get);exit();

		$query = 'earth_box(ll_to_earth(' . $latitude . ',' . $longitude . '), 6371) @> ll_to_earth( ' . $convert1 . ' , ' . $convert2 . ' ) and vendors_infos.lang_id = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
		$condition = " and vendors.active_status = '1' ";
		$query1 = ' and outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
		$orderby = '';
		if ($city != '') {
			$condition .= ' and outlets.city_id = ' . $city;
		}
		if ($location != '') {
			$condition .= ' and outlets.location_id = ' . $location;
		}
		if ($category_ids != '') {
			$condition .= " and (regexp_split_to_array(category_ids,',')::integer[] @> '{" . $category_ids . "}'::integer[]  and category_ids !='')";
		}
		if ($keyword != '') {
			$condition .= " and vendors_infos.vendor_name ILIKE '%" . $keyword . "%'";
		}
		if ($sortby == "delivery_time") {
			$orderby = 'vendors.delivery_time';
		}
		if ($sortby == "rating") {
			$orderby = 'vendors.average_rating';
		}
		$commaStr = !empty($orderby) ? ',' : '';
		$orderby = $commaStr . 'vendors.id ASC'; //,distance

		$stores = DB::select("select vendors.id as vendors_id, vendors_infos.vendor_name, vendors.first_name, vendors.last_name, vendors.featured_image, vendors.logo_image, vendors.delivery_time as vendors_delivery_time, vendors.category_ids, vendors.average_rating as vendors_average_rating, vendors.featured_vendor, outlet_infos.contact_address, vendors_infos.vendor_description ,earth_distance(ll_to_earth( " . $latitude . " , " . $longitude . " ), ll_to_earth( " . $convert1 . " , " . $convert2 . " )) as distance from vendors INNER JOIN outlets on outlets.vendor_id = vendors.id LEFT JOIN outlet_infos on outlets.id = outlet_infos.id LEFT JOIN vendors_infos ON vendors_infos.id = vendors.id where " . $query . $query1 . $condition . " AND vendors.featured_vendor = 1 GROUP BY vendors_id, vendors_infos.vendor_name, outlet_infos.contact_address, vendors_infos.vendor_description ORDER BY " . $orderby . " ");

		return $stores;
	}

	/* To get the nearest outlets list */

	public static function nearest_outlets_list($language, $category_ids, $category_url, $city, $location, $latitude, $longitude, $keyword, $sortby, $orderby) {

		$query = 'earth_box(ll_to_earth(' . $latitude . ',' . $longitude . '), 3000000) @> ll_to_earth( outlets.latitude , outlets.longitude ) and outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';

		$condition = " and outlets.active_status = '1' ";
		$query1 = ' and outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';

		$orderby = '';
		if ($city != '') {
			$condition .= ' and outlets.city_id = ' . $city;
		}
		if ($location != '') {
			$condition .= ' and outlets.location_id = ' . $location;
		}
		if ($category_ids != '') {
			$condition .= " and (regexp_split_to_array(category_ids,',')::integer[] @> '{" . $category_ids . "}'::integer[]  and category_ids !='')";
		}
		if ($keyword != '') {
			$condition .= " and outlet_infos.outlet_name ILIKE '%" . $keyword . "%'";
		}
		if ($sortby == "delivery_time") {
			$orderby = 'outlets.delivery_time';
		}
		if ($sortby == "rating") {
			$orderby = 'outlets.average_rating';
		}
		$commaStr = !empty($orderby) ? ',' : '';
		$orderby = $commaStr . 'distance'; //outlets.vendor_id ASC

		$stores = DB::select("select outlets.vendor_id as vendors_id , vendors.first_name,'vendors.featured_image', vendors.last_name,outlets.id as outlets_id,vendors_infos.vendor_name, outlet_infos.outlet_name, vendors.featured_image, vendors.logo_image,vendors.delivery_time as vendors_delivery_time, vendors.average_rating as vendors_average_rating, vendors.featured_vendor,  outlets.delivery_time as outlets_delivery_time, outlets.category_ids, outlets.average_rating as outlets_average_rating,vendors_infos.vendor_description , outlet_infos.contact_address
			,earth_distance(ll_to_earth( " . $latitude . " , " . $longitude . " ), ll_to_earth(outlets.latitude , outlets.longitude)) as distance
		 from outlets  LEFT JOIN outlet_infos ON outlet_infos.id = outlets.id LEFT JOIN vendors ON vendors.id = outlets.vendor_id LEFT JOIN vendors_infos ON vendors_infos.id = vendor_id   where " . $query . $query1 . $condition . "  ORDER BY " . $orderby . "");

		//print_r("coming");exit();

		return $stores;
	}
	public static function get_location_detail($location_url_index, $language) {
		$locations_query = 'zones_infos.language_id = (case when (select count(zones_infos.language_id) as totalcount from zones_infos where zones_infos.language_id = ' . $language . ' and zones.id = zones_infos.zone_id) > 0 THEN ' . $language . ' ELSE 1 END)';
		$locations_list = DB::table('zones')
			->select('zones.id as zone_id', 'zones.city_id', 'zones_infos.zone_name')
			->leftJoin('zones_infos', 'zones_infos.zone_id', '=', 'zones.id')
			->whereRaw($locations_query)
			->where('zones_status', 1)
			->where('zones.url_index', '=', $location_url_index)->first();
		return $locations_list;
	}

	public static function getCategoryLists($type, $language_id) {

		// $language_id = $post_data["language"];
		// $type        = $post_data["type"];
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
					$logo_image = url('/assets/admin/base/images/category/' . $value->image);
				}
				$banner_image = URL::asset('assets/admin/base/images/category/mobile_banner/no_image.png');
				if (file_exists(base_path() . '/public/assets/admin/base/images/category/mobile_banner/' . $value->mobile_banner_image) && $value->mobile_banner_image != '') {
					$banner_image = url('/assets/admin/base/images/category/mobile_banner/' . $value->mobile_banner_image);
				}
				$category_list[$i]['id'] = $value->id;
				$category_list[$i]['category_name'] = $value->category_name;
				$category_list[$i]['url_key'] = $value->url_key;
				$category_list[$i]['logo_image'] = $logo_image;
				$category_list[$i]['banner_image'] = $banner_image;
				$i++;
			}
			$result = array("response" => array("httpCode" => 200, 'Message' => 'Category list', 'data' => $category_list));
		}

		return $category_list;
	}


	public static function bulk_nearest_outlets($language, $latitude, $longitude,$outlet_id) {
		$query = 'earth_box(ll_to_earth(' . $latitude . ',' . $longitude . '), 3000) @> ll_to_earth( outlets.latitude , outlets.longitude ) and outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';

		$condition = " and outlets.id = ".$outlet_id;
		$query1 = ' and outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';

		$orderby = '';
		
		

		$stores = DB::select("select outlets.vendor_id as vendors_id , vendors.first_name, vendors.last_name,outlets.id as outlets_id,vendors_infos.vendor_name, outlet_infos.outlet_name, vendors.featured_image, vendors.logo_image,vendors.delivery_time as vendors_delivery_time, vendors.average_rating as vendors_average_rating, vendors.featured_vendor,  outlets.delivery_time as outlets_delivery_time, outlets.category_ids, outlets.average_rating as outlets_average_rating,vendors_infos.vendor_description , outlet_infos.contact_address
			,earth_distance(ll_to_earth( " . $latitude . " , " . $longitude . " ), ll_to_earth(outlets.latitude , outlets.longitude)) as distance
		 from outlets  LEFT JOIN outlet_infos ON outlet_infos.id = outlets.id LEFT JOIN vendors ON vendors.id = outlets.vendor_id LEFT JOIN vendors_infos ON vendors_infos.id = vendor_id   where " . $query . $query1 . $condition . "");

		//print_r($stores);exit();

		return $stores;
	}
	public static function city_list()
	{

		$city = DB::table('cities')
			->select('cities.id', 'cities_infos.city_name')
			->leftJoin('cities_infos', 'cities_infos.id', '=', 'cities.id')
			->where('cities.active_status', 'A')
			->get();
		$array = array();
		$citys = "";
		if($city) {
			foreach ($city as $key => $value) {
				$array[$key] =$value->city_name;
			}
			$citys =  implode("," ,$array );  
		}return $citys;
	}


	public static function zone_list()
	{

		$city = DB::table('zones_infos')
			->select('zones_infos.zone_name')
			//->leftJoin('cities_infos', 'cities_infos.id', '=', 'cities.id')
			//->where('cities.active_status', 'A')
			->get();
		$array = array();
		$citys = "";
		if($city) {
			foreach ($city as $key => $value) {
				$array[$key] =$value->zone_name;
			}
			$citys =  implode("," ,$array );  
		}return $citys;
	}}