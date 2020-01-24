<?php
namespace App\Http\Controllers\Api;

use App;
use App\Http\Controllers\Controller;
use App\Model\api_model;
use App\Model\favorite_vendors;
use App\Model\outlets;
use App\Model\products;
use App\Model\stores;
use App\Model\vendors;
use DB;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Input;

DB::enableQueryLog();
use JWTAuth;
use Session;
use Tymon\JWTAuth\Exceptions\JWTException;
use URL;
use App\Model\admin_products;

class Store extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $data)
    {
        $post_data = $data->all();
        if (isset($post_data['language']) && $post_data['language'] != '' && $post_data['language'] == 2) {
            App::setLocale('ar');
        } else {
            App::setLocale('en');
        }
    }

    /*
             *  get feature store
    */

    public function getfeaturesstore($language_id)
    {
        $data = array();
        $result = array("response" => array("httpCode" => 400, "status" => false, "data" => $data));
        $orderby = 'vendors.id ASC';
        $query = 'vendors_infos.lang_id = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $query1 = 'outlet_infos.language_id = (case when (select count(language_id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $vendors = Vendors::join('vendors_infos', 'vendors_infos.id', '=', 'vendors.id')
            ->join('outlets', 'outlets.vendor_id', '=', 'vendors.id')
            ->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
            ->select('vendors.id as vendors_id', 'vendors_infos.vendor_name', 'vendors.first_name', 'vendors.last_name', 'vendors.featured_image', 'vendors.logo_image', 'vendors.delivery_time as vendors_delivery_time', 'vendors.category_ids', 'vendors.average_rating as vendors_average_rating', 'outlet_infos.contact_address', 'outlets.id as outlets_id', 'outlets.vendor_id as outlets_vendors_id', 'outlet_infos.outlet_name', 'outlets.delivery_time as outlets_delivery_time', 'outlets.average_rating as outlets_average_rating', 'outlets.url_index')
            ->whereRaw($query)
            ->whereRaw($query1)
            ->where('vendors.active_status', '=', 1)
            ->where('vendors.featured_vendor', '=', 1)
            ->where('outlets.active_status', '=', 1)
            ->orderByRaw($orderby)
            ->get();
        if (count($vendors)) {
            $outlets_list = array();
            $i = 1;
            foreach ($vendors as $key => $datas) {
                $outlets_list[$datas->vendors_id]['vendors_id'] = $datas->vendors_id;
                $outlets_list[$datas->vendors_id]['vendor_name'] = $datas->vendor_name;
                $outlets_list[$datas->vendors_id]['featured_image'] = $datas->featured_image;
                $outlets_list[$datas->vendors_id]['logo_image'] = $datas->logo_image;
                $outlets_list[$datas->vendors_id]['category_ids'] = $datas->category_ids;
                $outlets_list[$datas->vendors_id]['vendors_delivery_time'] = $datas->vendors_delivery_time;
                $outlets_list[$datas->vendors_id]['vendors_average_rating'] = ($datas->vendors_average_rating == null) ? 0 : $datas->vendors_average_rating;
                $outlets_list[$datas->vendors_id]['outlets_id'] = $datas->outlets_id;
                $outlets_list[$datas->vendors_id]['outlets_delivery_time'] = $datas->outlets_delivery_time;
                $outlets_list[$datas->vendors_id]['url_index'] = $datas->url_index;
                $outlets_list[$datas->vendors_id]['outlets'][$i]['vendor_name'] = $datas->vendor_name;
                $outlets_list[$datas->vendors_id]['outlets'][$i]['outlets_id'] = $datas->outlets_id;
                $outlets_list[$datas->vendors_id]['outlets'][$i]['outlets_vendors_id'] = $datas->outlets_vendors_id;
                $outlets_list[$datas->vendors_id]['outlets'][$i]['outletname'] = $datas->outlet_name;
                $outlets_list[$datas->vendors_id]['outlets'][$i]['contact_address'] = $datas->contact_address;
                $outlets_list[$datas->vendors_id]['outlets'][$i]['outlets_delivery_time'] = $datas->outlets_delivery_time;
                $outlets_list[$datas->vendors_id]['outlets'][$i]['outlets_average_rating'] = ($datas->outlets_average_rating == null) ? 0 : $datas->outlets_average_rating;
                $outlets_list[$datas->vendors_id]['outlets'][$i]['url_index'] = $datas->url_index;
                $i++;
            }
            $result = array("response" => array("httpCode" => 200, "status" => true, 'data' => $outlets_list));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /*
             * student login
    */

    public function store_list(Request $data)
    {
        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $data = array();
        $result = array("response" => array("httpCode" => 400, "status" => false, "data" => $data));
        $query = 'vendors_infos.lang_id = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $post_data['language'] . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $post_data['language'] . ' ELSE 1 END)';
        $query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.language_id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $post_data['language'] . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $post_data['language'] . ' ELSE 1 END)';
        $condition = 'vendors.active_status = 1';
        $orderby = 'vendors.id ASC';
        if (isset($post_data['city']) && $post_data['city']) {
            $condition .= ' and outlets.city_id = ' . $post_data['city'];
        }
        if (isset($post_data['location']) && $post_data['location']) {
            $condition .= ' and outlets.location_id = ' . $post_data['location'];
        }
        if (isset($post_data['category_ids']) && $post_data['category_ids']) {
            $c_ids = $post_data['category_ids'];
            //~ $condition .=" and (regexp_split_to_array(category_ids,',')::integer[] @> '{".$c_ids."}'::integer[]  and category_ids !='')";
            $c_ids = explode(",", $c_ids);
            $c_ids = implode($c_ids, "','");
            $c_ids = "'" . $c_ids . "'";
            $condition .= " and vendor_category_mapping.category in($c_ids)";
        }
        $vendors = Vendors::Leftjoin('vendors_infos', 'vendors_infos.id', '=', 'vendors.id')
            ->Leftjoin('outlets', 'outlets.vendor_id', '=', 'vendors.id')
            ->join('vendor_category_mapping', 'vendor_category_mapping.vendor_id', '=', 'vendors.id')
            ->Leftjoin('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
            ->select('vendors.id as vendors_id', 'vendors_infos.vendor_name', 'vendors.first_name', 'vendors.last_name', 'vendors.featured_image', 'vendors.logo_image', 'vendors.delivery_time as vendors_delivery_time', 'vendors.category_ids', 'vendors.average_rating as vendors_average_rating', 'outlet_infos.contact_address', 'outlets.id as outlets_id', 'outlets.vendor_id as outlets_vendors_id', 'outlet_infos.outlet_name', 'outlets.delivery_time as outlets_delivery_time', 'outlets.average_rating as outlets_average_rating')
            ->whereRaw($query)
            ->whereRaw($query1)
            ->whereRaw($condition)
            ->orderByRaw($orderby)
            ->get();
        if (count($vendors)) {
            $outlets_list = array();
            $i = 1;
            foreach ($vendors as $key => $datas) {
                $outlets_list[$datas->vendors_id]['vendors_id'] = $datas->vendors_id;
                $outlets_list[$datas->vendors_id]['vendor_name'] = $datas->vendor_name;
                $outlets_list[$datas->vendors_id]['featured_image'] = $datas->featured_image;
                $outlets_list[$datas->vendors_id]['logo_image'] = $datas->logo_image;
                $outlets_list[$datas->vendors_id]['category_ids'] = $datas->category_ids;
                $outlets_list[$datas->vendors_id]['vendors_delivery_time'] = $datas->vendors_delivery_time;
                $outlets_list[$datas->vendors_id]['vendors_average_rating'] = ($datas->vendors_average_rating == null) ? 0 : $datas->vendors_average_rating;
                $outlets_list[$datas->vendors_id]['outlets_id'] = $datas->outlets_id;
                $outlets_list[$datas->vendors_id]['outlets_delivery_time'] = $datas->outlets_delivery_time;
                $outlets_list[$datas->vendors_id]['outlets'][$i]['vendor_name'] = $datas->vendor_name;
                $outlets_list[$datas->vendors_id]['outlets'][$i]['outlets_id'] = $datas->outlets_id;
                $outlets_list[$datas->vendors_id]['outlets'][$i]['outlets_vendors_id'] = $datas->outlets_vendors_id;
                $outlets_list[$datas->vendors_id]['outlets'][$i]['outletname'] = $datas->outlet_name;
                $outlets_list[$datas->vendors_id]['outlets'][$i]['contact_address'] = $datas->contact_address;
                $outlets_list[$datas->vendors_id]['outlets'][$i]['outlets_delivery_time'] = $datas->outlets_delivery_time;
                $outlets_list[$datas->vendors_id]['outlets'][$i]['outlets_average_rating'] = ($datas->outlets_average_rating == null) ? 0 : $datas->outlets_average_rating;
                $i++;
            }
            $result = array("response" => array("httpCode" => 200, "status" => true, 'data' => $outlets_list));
        }
        return json_encode($result);
    }

    /*
             * student login
    */
    public function Store_list_ajax(Request $data)
    {
        $post_data = $data->all();
        $category_url = isset($post_data['category_url']) ? $post_data['category_url'] : '';
        // if ($post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }

        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $current_lat = isset($post_data['current_lat']) ? $post_data['current_lat'] : '';
        $current_long = isset($post_data['current_long']) ? $post_data['current_long'] : '';
        //print_r($current_lat);exit;
        $distance = 25 * 1000;
        $data = array();
        $result = array("response" => array("httpCode" => 400, "status" => false, "data" => $data));
        $query = 'vendors_infos.lang_id = (case when (select count(vendors_infos.lang_id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $post_data['language'] . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $post_data['language'] . ' ELSE 1 END)';
        $query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount1 from outlet_infos where outlet_infos.language_id = ' . $post_data['language'] . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $post_data['language'] . ' ELSE 1 END)';
        $condition = 'vendors.active_status = 1';
        $orderby = 'vendors.id ASC';
        if (isset($post_data['city']) && $post_data['city']) {
            // $condition .=' and cities.url_index = '.$post_data['city'];
            $condition .= " and cities.url_index = '" . $post_data['city'] . "'";
        }
        if (isset($post_data['location']) && $post_data['location']) {
            $condition .= " and zones.url_index = '" . $post_data['location'] . "'";
        }
        if (isset($post_data['category_ids']) && $post_data['category_ids']) {
            $c_ids = $post_data['category_ids'];
            $c_ids = explode(",", $c_ids);
            $cat_ids = implode($c_ids, "_");
            $c_ids = implode($c_ids, "','");
            $c_ids = "'" . $c_ids . "'";
            //$condition .= " and (regexp_split_to_array(category_ids,',')::integer[] @> '{".$c_ids."}'::integer[]  and category_ids !='')";
            $condition .= " and vendor_category_mapping.category in($c_ids)";
        }
        if (isset($post_data['keyword']) && $post_data['keyword']) {
            $keyword = pg_escape_string($post_data['keyword']);
            $condition .= " and vendors_infos.vendor_name ILIKE '%" . $keyword . "%'";
        }
        if (isset($post_data['sortby']) && $post_data['sortby'] == "delivery_time") {
            $orderby = 'vendors_delivery_time ' . $post_data['orderby'];
        }
        if (isset($post_data['sortby']) && $post_data['sortby'] == "rating") {
            $orderby = 'vendors_average_rating ' . $post_data['orderby'];
        }
        if (empty($current_lat) && empty($current_long)) {
            $vendors = Vendors::join('vendors_infos', 'vendors_infos.id', '=', 'vendors.id')
                ->join('outlets', 'outlets.vendor_id', '=', 'vendors.id')
                ->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
                ->join('zones', 'zones.id', '=', 'outlets.location_id')
                ->join('cities', 'cities.id', '=', 'outlets.city_id')
                ->join('vendor_category_mapping', 'vendor_category_mapping.vendor_id', '=', 'vendors.id')
            //left join "vendor_category_mapping" on vendor_category_mapping.outlet_id = vendors.id
                ->select('vendors.id as vendors_id', 'vendors_infos.vendor_name', 'vendors.first_name', 'vendors.last_name', 'vendors.featured_image', 'vendors.logo_image', 'vendors.delivery_time as vendors_delivery_time', 'vendors.category_ids', 'vendors.average_rating as vendors_average_rating', 'outlet_infos.contact_address', 'outlet_infos.outlet_name', 'outlets.id as outlets_id', 'outlets.vendor_id as outlets_vendors_id', 'outlet_infos.outlet_name', 'outlets.delivery_time as outlets_delivery_time', 'outlets.average_rating as outlets_average_rating', 'outlets.url_index', 'outlets.category_ids as outlets_category_ids')
                ->whereRaw($query)
                ->whereRaw($query1)
                ->whereRaw($condition)
                ->where('vendors.featured_vendor', '=', 1)
                ->where('outlets.active_status', '=', '1')
                ->orderByRaw($orderby)
                ->get();
        } else {
            $query = 'vendors_infos.lang_id = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $post_data['language'] . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $post_data['language'] . ' ELSE 1 END)';
            $query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $post_data['language'] . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $post_data['language'] . ' ELSE 1 END)';
            $query2 = 'cities_infos.language_id = (case when (select count(cities_infos.language_id) as totalcount from cities_infos where cities_infos.language_id = ' . $post_data['language'] . ' and cities.id = cities_infos.id) > 0 THEN ' . $post_data['language'] . ' ELSE 1 END)';
            $query3 = 'zones_infos.language_id = (case when (select count(zones_infos.language_id) as totalcount from zones_infos where zones_infos.language_id = ' . $post_data['language'] . ' and zones.id = zones_infos.zone_id) > 0 THEN ' . $post_data['language'] . ' ELSE 1 END)';

            $vendors = DB::select("select vendors.logo_image,vendors.category_ids,outlets.delivery_charges_fixed,outlets.city_id,outlets.location_id,vendors.id as vendors_id,vendors.delivery_time as vendors_delivery_time,vendors.average_rating as vendors_average_rating,vendors.featured_image, outlet_infos.contact_address,outlets.vendor_id as outlets_vendors_id,outlets.id as outlets_id,outlet_infos.outlet_name,outlets.delivery_time as outlets_delivery_time,outlets.average_rating as outlets_average_rating,outlets.category_ids as outlets_category_ids,vendors_infos.vendor_name,vendors.first_name,outlets.url_index,vendors.last_name, outlets.delivery_charges_variation,outlets.minimum_order_amount,outlets.active_status,zones_infos.zone_name,cities_infos.city_name,earth_distance(ll_to_earth(" . $current_lat . ', ' . $current_long . "), ll_to_earth(outlets.latitude, outlets.longitude)) as distance  from  vendors  left join outlets on outlets.vendor_id =vendors.id left join outlet_infos on outlets.id = outlet_infos.id left Join cities  on cities.id = vendors.city_id left join cities_infos on cities_infos.id =vendors.city_id left join zones on zones.city_id =vendors.city_id left join zones_infos on zones_infos.zone_id =zones.id left join vendors_infos on vendors_infos.id = vendors.id where earth_box(ll_to_earth(" . $current_lat . ', ' . $current_long . '), ' . $distance . ") @> ll_to_earth(outlets.latitude, outlets.longitude)and " . $query . " and " . $query1 . " and " . $query2 . " and " . $query3 . " and outlets.active_status='1' and vendors.active_status=1 and vendors.featured_vendor='1' order by distance asc");
        }
        //print_r(count($vendors));exit;
        if (count($vendors)) {
            $outlets_list = array();
            $i = 1;
            foreach ($vendors as $key => $datas) {
                if ($datas->outlets_id != 0) {
                    $outlets_list[$datas->vendors_id]['vendors_id'] = $datas->vendors_id;
                    $outlets_list[$datas->vendors_id]['vendor_name'] = $datas->vendor_name;
                    $outlets_list[$datas->vendors_id]['featured_image'] = $datas->featured_image;
                    $outlets_list[$datas->vendors_id]['logo_image'] = $datas->logo_image;
                    $outlets_list[$datas->vendors_id]['category_ids'] = $datas->category_ids;
                    $category_name = '';
                    $outlets_list[$datas->vendors_id]['category_ids'] = $category_name;
                    if (!empty($datas->category_ids)) {
                        $category_ids = geoutletCategoryLists(explode(',', $datas->category_ids), $post_data['language']);
                        if (count($category_ids) > 0) {
                            foreach ($category_ids as $cat) {
                                $category_name .= $cat->category_name . ', ';
                            }
                        }
                        $outlets_list[$datas->vendors_id]['vendor_category'] = rtrim($category_name, ', ');
                    }
                    $outlets_list[$datas->vendors_id]['vendors_delivery_time'] = $datas->vendors_delivery_time;
                    $outlets_list[$datas->vendors_id]['vendors_average_rating'] = ($datas->vendors_average_rating == null) ? 0 : $datas->vendors_average_rating;
                    $outlets_list[$datas->vendors_id]['outlets_id'] = $datas->outlets_id;
                    $outlets_list[$datas->vendors_id]['outlet_name'] = $datas->outlet_name;
                    $distance_km = number_format($datas->distance / 1000, 1);
                    $outlets_list[$datas->vendors_id]['distance_km'] = $distance_km;
                    $outlets_list[$datas->vendors_id]['outlets_category_ids'] = $datas->outlets_category_ids;
                    $outlets_list[$datas->vendors_id]['url_index'] = $datas->url_index;
                    $outlets_list[$datas->vendors_id]['outlets_delivery_time'] = $datas->outlets_delivery_time;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['vendor_name'] = $datas->vendor_name;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlets_id'] = $datas->outlets_id;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlet_name'] = $datas->outlet_name;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlets_category_ids'] = $datas->outlets_category_ids;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlets_vendors_id'] = $datas->outlets_vendors_id;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outletname'] = $datas->outlet_name;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['url_index'] = $datas->url_index;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['contact_address'] = $datas->contact_address;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlets_delivery_time'] = $datas->outlets_delivery_time;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlets_average_rating'] = ($datas->outlets_average_rating == null) ? 0 : $datas->outlets_average_rating;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['distance'] = $datas->distance;
                    $out_category_name = '';
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlets_category'] = $out_category_name;

                    // print_r($distance_km);exit;
                    if (!empty($datas->outlets_category_ids)) {
                        $outlet_category_ids = geoutletCategoryLists(explode(',', $datas->outlets_category_ids), $post_data['language']);
                        if (count($outlet_category_ids) > 0) {
                            foreach ($outlet_category_ids as $out_cat) {
                                $out_category_name .= $out_cat->category_name . ', ';
                            }
                        }
                        $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlets_category'] = rtrim($out_category_name, ', ');

                        $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['distance_km'] = $distance_km;
                    }

                    $i++;
                }
            }

            $rateit_js = url('assets/front/' . Session::get('general')->theme . '/plugins/rateit/src/jquery.rateit.js');
            $rateit_css = url('assets/front/' . Session::get('general')->theme . '/plugins/rateit/src/rateit.css');
            $html = '<meta  content="text/html; charset=UTF-8" /><script src="' . $rateit_js . '"></script><link href="' . $rateit_css . '" rel="stylesheet">';
            foreach ($outlets_list as $outlets_data) {
                if (isset($post_data['category_ids']) && $post_data['category_ids']) {
                    $store_url = URL::to('store/info/' . $outlets_data['url_index'] . '/' . $cat_ids);
                } else {
                    if ($category_url != '') {
                        $store_url = URL::to('store/info/' . $outlets_data['url_index'] . '/' . $category_url);
                    } else {
                        $store_url = URL::to('store/info/' . $outlets_data['url_index']);
                    }
                }

                if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/list/' . $outlets_data['featured_image'])) {
                    if (count($outlets_data['outlets']) > 1) {
                        $url = url('/assets/admin/base/images/vendors/list/' . $outlets_data['featured_image'] . '?' . time());
                        $image = '<a href="javascript:;" title="' . $outlets_data['vendor_name'] . '" data-toggle="modal" data-target=".bd-example-modal-lg' . $outlets_data['vendors_id'] . '" > <img alt="' . $outlets_data['outlet_name'] . '" src="' . $url . '" ></a>';
                    } elseif (count($outlets_data['outlets']) == 1) {
                        $url = url('/assets/admin/base/images/vendors/list/' . $outlets_data['featured_image'] . '?' . time());
                        $image = '<a href="' . $store_url . '" title="' . $outlets_data['vendor_name'] . '"> <img alt="' . $outlets_data['outlet_name'] . '" src="' . $url . '" ></a>';
                    }
                } else {
                    if (count($outlets_data['outlets']) > 1) {
                        $image = '<a href="javascript:;" title="' . $outlets_data['vendor_name'] . '" data-toggle="modal" data-target=".bd-example-modal-lg' . $outlets_data['vendors_id'] . '" ><img src="{{ URL::asset("assets/admin/base/images/vendors/stores.png") }}" alt="' . $outlets_data['outlet_name'] . '"></a>';
                    } elseif (count($outlets_data['outlets']) == 1) {
                        $image = '<a href="' . $store_url . '" title="' . $outlets_data['vendor_name'] . '"><img src="{{ URL::asset("assets/admin/base/images/vendors/stores.png") }}" alt="' . $outlets_data['outlet_name'] . '"></a>';
                    }
                }

                $outlet_html = '<script>
                                    $(document ).ready(function() {
                                        $(".close").on("click", function() {
                                            $("body").removeClass("modal-open");
                                            $(".modal-backdrop").hide();
                                        });
                                    });
                                </script>';
                if (count($outlets_data['outlets']) > 1) {
                    $outlet_html .= '<div class="modal fade store_detials_list bd-example-modal-lg' . $outlets_data['vendors_id'] . '" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button><h4 id="myLargeModalLabel" class="modal-title">' . $outlets_data['vendor_name'] . '</h4><p class="store_title">' . count($outlets_data['outlets']) . ' ' . trans("messages.Branches available near you.") . '</p></div><div class="store_right_items">';
                    foreach ($outlets_data['outlets'] as $outlets) {
                        if (isset($post_data['category_ids']) && $post_data['category_ids']) {
                            $outlets_url = URL::to('store/info/' . $outlets_data['url_index'] . '/' . $cat_ids);
                        } else {
                        }
                        if ($category_url != '') {
                            $outlets_url = URL::to('store/info/' . $outlets['url_index'] . '/' . $category_url);
                        } else {
                            $outlets_url = URL::to('store/info/' . $outlets['url_index']);
                        }
                        $outlet_html .= '<div class="col-md-3 col-sm-3 col-xs-6"><div class="common_item"><div class="store_itm_img">';
                        if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $outlets_data['logo_image'])) {
                            $url = url('/assets/admin/base/images/vendors/logos/' . $outlets_data['logo_image'] . '?' . time());
                            $oimage = '<a href="' . $outlets_url . '" title="' . $outlets['outlet_name'] . '"> <img alt="' . $outlets['outlet_name'] . '"  src="' . $url . '" ></a>';
                        } else {
                            $oimage = '<a href="' . $outlets_url . '" title="' . $outlets['outlet_name'] . '"><img src="{{ URL::asset("assets/admin/base/images/vendors/stores.png") }}" alt="' . $outlets['outlet_name'] . '"></a>';
                        }
                        $distance2 = '<div class="price_sec"><b>' . $outlets['outlets_delivery_time'] . ' ' . trans("messages.Mins") . '</b></div>';
                        if (!empty($current_lat) && !empty($current_long)) {
                            $distance2 = '';
                        }
                        $outlet_html .= $oimage . $distance2 . '</div><div class="store_itm_desc">
                        <a href="' . $outlets_url . '" title="' . $outlets['outlet_name'] . '">' . $outlets['outlet_name'] . '</a><p>' . substr($outlets['outlets_category'], 0, 85) . '</p></div><div class="store_itm_rating"><h2>
                                <div class="rateit" data-rateit-value=' . $outlets['outlets_average_rating'] . ' data-rateit-ispreset="true" data-rateit-readonly="true">  </div>&nbsp' . $outlets['outlets_average_rating'] . ' </h2></div><div class="store_itm_rating map_location">
                        <a class="location_location"><i class="glyph-icon flaticon-location-pin"></i>' . substr($outlets['contact_address'], 0, 50) . '</a>
                        </div></div></div>'; //.' '.trans("messages.Mins")
                    }
                    $outlet_html .= '</div></div></div></div>';
                }
                $more = '';
                if (count($outlets_data['outlets']) > 1) {
                    $count = count($outlets_data['outlets']) - 1;
                    $more .= '<a href="javascript:;" class="right_store" title="' . $count . ' ' . trans("messages.Branches available") . '" data-toggle="modal" data-target=".bd-example-modal-lg' . $outlets_data['vendors_id'] . '">' . $count . ' ' . trans("messages.Branches available") . '</a>';
                }
                if (count($outlets_data['outlets']) > 0) {
                    $distance1 = '<div class="price_sec"><b>' . $outlets_data['vendors_delivery_time'] . ' ' . trans("messages.Mins") . '</b></div>';
                    if (!empty($current_lat) && !empty($current_long)) {
                        $distance1 = '<div class="price_sec"><b>' . $outlets_data['distance_km'] . ' KM</b></div>';
                    }
                    $html .= '<div class="col-md-4 col-sm-4 col-xs-6"><div class="common_item"><div class="store_itm_img">' . $image . $distance1 . '</div><div class="store_itm_desc"><a href="javascript:;" data-toggle="modal" data-target=".bd-example-modal-lg' . $outlets_data['vendors_id'] . '" title="' . $outlets_data['vendor_name'] . '">' . $outlets_data['vendor_name'] . '</a><p>' . substr($outlets_data['vendor_category'], 0, 85) . '</p></div><div class="store_itm_rating">' . $more . '<h2><a><div class="rateit" data-rateit-value="' . $outlets_data['vendors_average_rating'] . '" data-rateit-ispreset="true" data-rateit-readonly="true"></div></a>' . $outlets_data['vendors_average_rating'] . '</h2></div></div></div>' . $outlet_html;
                }
            }
            if (isset($post_data['type']) && $post_data['type'] == 'web') {
                if (isset($post_data['filter']) && $post_data['filter'] == 1) {
                    $result = array("response" => array("httpCode" => 200, "status" => true, 'data' => utf8_encode($html)));
                } else {
                    $result = array("response" => array("httpCode" => 200, "status" => true, 'data' => utf8_encode($html)));
                }
            } else {
                $result = array("response" => array("httpCode" => 200, "status" => true, 'data' => $outlets_list));
            }
            //print_r($result);exit;
            //$result = array("response" => array("httpCode" => 200, "status" => true,'data'=>$outlets_list));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /*
             * student login
    */

    public function store_info(Request $data)
    {
        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $data = array();
        $rules = [
            'store_id' => ['required'],
            'store_id' => ['numeric'],
            'language' => ['required'],
            'language' => ['numeric'],
        ];
        $category_url = isset($post_data['category_url']) ? $post_data['category_url'] : '';
        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);

        $distance = 25 * 1000;
        if ($validator->fails()) {
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => $errors));
        } else {
            $user_id = isset($post_data["user_id"]) ? $post_data["user_id"] : "";
            $language_id = $post_data["language"];
            $store_id = $post_data["store_id"];
            $vendor_category = $post_data["vendor_category"];
            $result = array("response" => array("httpCode" => 400, "status" => false, "data" => $data));

            $query = 'vendors_infos.lang_id = (case when (select count(vendors_infos.lang_id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
            $query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.language_id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
            $query2 = 'zones_infos.language_id = (case when (select count(zones_infos.language_id) as totalcount from zones_infos where zones_infos.language_id = ' . $language_id . ' and zones.id = zones_infos.zone_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
            $condition = 'vendors.active_status = 1 and vendors.featured_vendor = 1';

            $vendors = Vendors::join('vendors_infos', 'vendors_infos.id', '=', 'vendors.id')
                ->join('outlets', 'outlets.vendor_id', '=', 'vendors.id')
                ->join('outlet_infos', 'outlets.id', '=', 'outlet_infos.id')
                ->join('zones', 'zones.id', '=', 'outlets.location_id')
                ->join('zones_infos', 'zones_infos.zone_id', '=', 'zones.id')
                ->select('vendors.id as vendors_id', 'vendors_infos.vendor_name', 'vendors.first_name', 'vendors.last_name', 'vendors.featured_image', 'vendors.logo_image', 'vendors.delivery_time as vendors_delivery_time', 'vendors.category_ids', 'vendors.average_rating as vendors_average_rating', 'outlet_infos.contact_address as outlets_contact_address', 'outlets.id as outlets_id', 'outlets.vendor_id as outlets_vendors_id', 'outlet_infos.outlet_name', 'outlets.delivery_time as outlets_delivery_time', 'outlets.average_rating as outlets_average_rating', 'outlets.delivery_charges_fixed as outlets_delivery_charges_fixed', 'outlets.pickup_time as outlets_pickup_time', 'outlets.latitude as outlets_latitude', 'outlets.longitude as outlets_longitude', 'outlets.url_index', 'outlets.contact_phone as outlet_phone_number', 'outlets.category_ids as outlets_category_ids', 'zones_infos.zone_name as outlet_location_name')
                ->whereRaw($query)
                ->whereRaw($query1)
                ->whereRaw($query2)
                ->whereRaw($query2)
                ->whereRaw($condition)
                ->where('outlets.id', '=', $store_id)
                ->where('outlets.active_status', '=', '1')
                ->get();
            $category_id = '';
            if ($category_url != '') {
                $cat_detail = getSubCategoryLists(2, '', $category_url);
                //print_r($cat_detail);exit;
                if (count($cat_detail) > 0) {
                    $category_id = $cat_detail[0]->id;
                }
            }

            $condition1 = 'products.active_status = 1 and products.approval_status = 1';

            if ($vendor_category != "") {
                $condition1 .= " and products.vendor_category_id in($vendor_category)";
            }

            //print_r($condition1);exit;
            $pquery = '"products_infos"."lang_id" = (case when (select count(products_infos.lang_id) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and products.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
            $cquery = '"categories_infos"."language_id" = (case when (select count(categories_infos.language_id) as totalcount from categories_infos where categories_infos.language_id = ' . $language_id . ' and categories.id = categories_infos.category_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
            $wquery = '"weight_classes_infos"."lang_id" = (case when (select count(weight_classes_infos.lang_id) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . $language_id . ' and weight_classes.id = weight_classes_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
            $products = Products::join('products_infos', 'products.id', '=', 'products_infos.id')
                ->join('categories', 'categories.id', '=', 'products.category_id')
                ->join('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
                ->join('weight_classes', 'weight_classes.id', '=', 'products.weight_class_id')
                ->join('weight_classes_infos', 'weight_classes_infos.id', '=', 'weight_classes.id')
                ->select('products.id as product_id', 'products.product_url', 'products.product_image', 'products.weight', 'products.original_price', 'products.discount_price', 'products.vendor_id', 'products.outlet_id', 'products_infos.description', 'products_infos.product_name', 'categories_infos.category_name', 'categories.id', 'weight_classes_infos.unit', 'weight_classes_infos.title', 'products.category_id', 'categories.url_key as cat_url')
                ->whereRaw($pquery)
                ->whereRaw($cquery)
                ->whereRaw($wquery)
                ->whereRaw($condition1)
                ->where('products.outlet_id', '=', $store_id)
                ->orderBy('categories_infos.category_name', 'asc')
                ->get();
            $most_sell_products = DB::table('orders')
                ->select(DB::raw('COUNT(orders_info.item_id)AS product_id_count'), 'orders.outlet_id', 'orders_info.item_id as product_id', 'products.product_url', 'products.product_image', 'products.weight', 'products.original_price', 'products.discount_price', 'products.vendor_id', 'products_infos.product_name', 'products_infos.description', 'weight_classes_infos.unit', 'weight_classes_infos.title')
                ->join('orders_info', 'orders.id', '=', 'orders_info.order_id')
                ->join('products', 'products.id', '=', 'orders_info.item_id')
                ->join('products_infos', 'products_infos.id', '=', 'products.id')
                ->join('weight_classes_infos', 'weight_classes_infos.id', '=', 'products.weight_class_id')
                ->groupBy('orders_info.item_id', 'orders.outlet_id', 'products.id', 'products_infos.product_name', 'products_infos.description', 'weight_classes_infos.unit', 'weight_classes_infos.title')
                ->where('orders.outlet_id', "=", $store_id)
                ->whereRaw($pquery)
                ->whereRaw($condition1)
                ->orderBy('product_id_count', 'desc')
                ->limit(10)
                ->get();
            $outlet_products_list = $outlet_products_cate = $outlet_products_category_url = array();
            $categories = array();
            //print_r($products);exit;
            if (count($products)) {
                $i = 1;
                $current_category_url = '';
                foreach ($products as $pkey => $pdatas) {
                    $outlet_products_list[$pdatas->category_name][$i]['product_id'] = $pdatas->product_id;
                    $outlet_products_list[$pdatas->category_name][$i]['product_name'] = $pdatas->product_name;
                    $outlet_products_list[$pdatas->category_name][$i]['product_url'] = $pdatas->product_url;
                    $outlet_products_list[$pdatas->category_name][$i]['description'] = $pdatas->description;
                    $outlet_products_list[$pdatas->category_name][$i]['product_image'] = $pdatas->product_image;
                    $outlet_products_list[$pdatas->category_name][$i]['weight'] = $pdatas->weight;
                    $outlet_products_list[$pdatas->category_name][$i]['unit'] = $pdatas->unit;
                    $outlet_products_list[$pdatas->category_name][$i]['title'] = $pdatas->title;
                    $outlet_products_list[$pdatas->category_name][$i]['original_price'] = $pdatas->original_price;
                    $outlet_products_list[$pdatas->category_name][$i]['discount_price'] = $pdatas->discount_price;
                    $outlet_products_list[$pdatas->category_name][$i]['vendor_id'] = $pdatas->vendor_id;
                    $outlet_products_list[$pdatas->category_name][$i]['outlet_id'] = $pdatas->outlet_id;
                    $outlet_products_list[$pdatas->category_name][$i]['category_id'] = $pdatas->category_id;
                    $outlet_products_list[$pdatas->category_name][$i]['product_cart_count'] = $this->get_cart_product_count($user_id, $pdatas->product_id);
                    $outlet_products_cate[$pdatas->category_name] = $pdatas->category_id;
                    if ($current_category_url != $pdatas->cat_url) {
                        $current_category_url = $pdatas->cat_url;
                        $outlet_products_category_url[$pdatas->category_name]['category_url_key'] = $current_category_url;
                    }
                    $categories[$pdatas->cat_url]['category_id'] = $pdatas->category_id;
                    $categories[$pdatas->cat_url]['category_name'] = $pdatas->category_name;
                    $categories[$pdatas->cat_url]['url_key'] = $pdatas->cat_url;
                    $i++;
                }
            }

            if (count($vendors)) {
                $outlet_info = array();
                foreach ($vendors as $key => $datas) {
                    $outlet_info[$key]['outlets_id'] = $datas->outlets_id;
                    $outlet_info[$key]['vendors_id'] = $datas->vendors_id;
                    $outlet_info[$key]['vendor_name'] = $datas->vendor_name;
                    $outlet_info[$key]['first_name'] = $datas->first_name;
                    $outlet_info[$key]['last_name'] = $datas->last_name;
                    $outlet_info[$key]['featured_image'] = $datas->featured_image;
                    $outlet_info[$key]['logo_image'] = $datas->logo_image;
                    $outlet_info[$key]['category_ids'] = $datas->category_ids;
                    $outlet_info[$key]['vendors_delivery_time'] = $datas->vendors_delivery_time;
                    $outlet_info[$key]['category_ids'] = $datas->category_ids;
                    $outlet_info[$key]['vendors_average_rating'] = ($datas->vendors_average_rating == null) ? 0 : $datas->vendors_average_rating;
                    $outlet_info[$key]['outlets_contact_address'] = $datas->outlets_contact_address;
                    $outlet_info[$key]['outlet_phone_number'] = $datas->outlet_phone_number;
                    $outlet_info[$key]['outlets_vendors_id'] = $datas->outlets_vendors_id;
                    $outlet_info[$key]['outlet_name'] = $datas->outlet_name;
                    $outlet_info[$key]['outlets_delivery_time'] = $datas->outlets_delivery_time;
                    $outlet_info[$key]['outlets_average_rating'] = ($datas->outlets_average_rating == null) ? 0 : $datas->outlets_average_rating;
                    $outlet_info[$key]['outlets_delivery_charges_fixed'] = $datas->outlets_delivery_charges_fixed;
                    $outlet_info[$key]['outlets_pickup_time'] = $datas->outlets_pickup_time;
                    $outlet_info[$key]['outlets_latitude'] = $datas->outlets_latitude;
                    $outlet_info[$key]['outlets_longitude'] = $datas->outlets_longitude;
                    $outlet_info[$key]['outlet_location_name'] = $datas->outlet_location_name;
                    $outlet_info[$key]['url_index'] = $datas->url_index;
                    $outlet_info[$key]['products'] = $outlet_products_list;
                    $outlet_info[$key]['categories'] = $categories;
                    $outlet_info[$key]['categories_url'] = $outlet_products_category_url;
                    $out_category_name = '';
                    $outlet_info[$key]['outlets_category'] = $out_category_name;
                    $outlet_info[$key]['distance'] = $distance;
                    $outlet_info[$key]['outlet_location_name'] = $datas->outlet_location_name;
                    $distance_km = number_format($distance / 100, 2);
                    if (!empty($datas->outlets_category_ids)) {
                        $outlet_category_ids = geoutletCategoryLists(explode(',', $datas->outlets_category_ids), $post_data['language']);
                        if (count($outlet_category_ids) > 0) {
                            foreach ($outlet_category_ids as $out_cat) {
                                $out_category_name .= $out_cat->category_name . ', ';
                            }
                        }
                        $outlet_info[$key]['outlets_category'] = rtrim($out_category_name, ', ');
                    }
                }
                $result = array("response" => array("httpCode" => 200, "status" => true, 'data' => $outlet_info, 'most_sell_products' => $most_sell_products, 'category_url' => $category_url, 'category_id' => $category_id));
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function get_cart_product_count($user_id, $product_id)
    {
        if ($user_id == "") {
            return 0;
        } else {
            $cart_count = DB::table('cart')
                ->join('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
                ->select('cart_detail.quantity')
                ->where("cart_detail.product_id", "=", $product_id)
                ->where("cart.user_id", "=", $user_id)
                ->get();
            //print_r($cart_count[0]->quantity);exit;
            if (count($cart_count) > 0) {
                return $cart_count[0]->quantity;
            }
            return 0;
        }
    }

    public function product_list(Request $data)
    {
        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $store_key = $post_data['outlet_key'];
        //print_r($store_key);exit;
        $category_url = $post_data['category_url'];
        $sub_category = $post_data['subcategory'];
        // $pro_category_url = $post_data['pro_category_url'];
        $language_id = $post_data['language'];
        $user_id = isset($post_data["user_id"]) ? $post_data["user_id"] : "";
        $keyword = isset($post_data['keyword']) ? $post_data['keyword'] : '';
        $data = array();
        $result = array("response" => array("httpCode" => 400, "status" => false, "data" => $data));
        $condtion = " products.active_status = 1";
        if ($keyword) {
            $condtion .= " and products_infos.product_name ILIKE '%" . $keyword . "%'";
        }
        /*  $category_id = '';
                    if($pro_category_url !='')
                    {
                        $cat_detail = getSubCategoryLists(2,'',$pro_category_url);
                        //print_r($cat_detail);exit;
                        if(count($cat_detail) > 0)
                        {
                            $category_id = $cat_detail[0]->id;
                        }
        */
        //print_r($category_url);exit;
        $pquery = '"products_infos"."lang_id" = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and products.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $cquery = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = ' . $language_id . ' and categories.id = categories_infos.category_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $wquery = '"weight_classes_infos"."lang_id" = (case when (select count(*) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . $language_id . ' and weight_classes.id = weight_classes_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $products = Products::join('products_infos', 'products.id', '=', 'products_infos.id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->join('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
            ->join('weight_classes', 'weight_classes.id', '=', 'products.weight_class_id')
            ->join('weight_classes_infos', 'weight_classes_infos.id', '=', 'weight_classes.id')
            ->join('outlets', 'outlets.id', '=', 'products.outlet_id')
            ->select('products.id as product_id', 'products.product_url', 'products.vendor_category_id', 'products.product_image', 'products.weight', 'products.original_price', 'products.discount_price', 'products.vendor_id', 'products.outlet_id', 'products_infos.description', 'products_infos.product_name', 'categories_infos.category_name', 'categories.id', 'weight_classes_infos.unit', 'weight_classes_infos.title', 'products.category_id', 'categories.url_key', 'categories.url_key as cat_url', 'outlets.id as outlet_id')
            ->whereRaw($pquery)
            ->whereRaw($cquery)
            ->whereRaw($wquery)
            ->where('outlets.url_index', '=', $store_key);
        if ($category_url != 'all' && !$sub_category) {
            $products = $products->where('categories.url_key', '=', $category_url);
        }
        if (isset($sub_category) && $sub_category != '') {
            $products = $products->where('products.sub_category_id', '=', $sub_category);
        }
        $products = $products->whereRaw($condtion)
            ->where('products.approval_status', '=', 1)
            ->orderBy('categories_infos.category_name', 'asc')
            ->get();
        //print_r($products);exit;
        $categories = array();
        foreach ($products as $key => $prod) {
            $products[$key]->product_cart_count = $this->get_cart_product_count($user_id, $prod->product_id);
            $categories['category_name'] = $prod->category_name;
            $categories['url_key'] = $prod->cat_url;
        }
        //print_r($categories);exit;
        $query = 'vendors_infos.lang_id = (case when (select count(vendors_infos.lang_id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.language_id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $condition = 'vendors.active_status = 1 and vendors.featured_vendor = 1';
        $vendors = Vendors::join('vendors_infos', 'vendors_infos.id', '=', 'vendors.id')
            ->join('outlets', 'outlets.vendor_id', '=', 'vendors.id')
            ->join('outlet_infos', 'outlets.id', '=', 'outlet_infos.id')
            ->select('vendors.id as vendors_id', 'vendors_infos.vendor_name', 'vendors.first_name', 'vendors.last_name', 'vendors.featured_image', 'vendors.logo_image', 'vendors.delivery_time as vendors_delivery_time', 'vendors.category_ids', 'vendors.average_rating as vendors_average_rating', 'outlet_infos.contact_address as outlets_contact_address', 'outlets.id as outlets_id', 'outlets.vendor_id as outlets_vendors_id', 'outlet_infos.outlet_name', 'outlets.delivery_time as outlets_delivery_time', 'outlets.average_rating as outlets_average_rating', 'outlets.delivery_charges_fixed as outlets_delivery_charges_fixed', 'outlets.pickup_time as outlets_pickup_time', 'outlets.latitude as outlets_latitude', 'outlets.longitude as outlets_longitude', 'outlets.url_index')
            ->whereRaw($query)
            ->whereRaw($query1)
            ->whereRaw($condition)
            ->where('outlets.url_index', '=', $store_key)
            ->where('outlets.active_status', '=', '1')
            ->get();
        $outlet_info = array();
        if (count($vendors)) {
            foreach ($vendors as $key => $datas) {
                $outlet_info[$key]['outlets_id'] = $datas->outlets_id;
                $outlet_info[$key]['vendors_id'] = $datas->vendors_id;
                $outlet_info[$key]['vendor_name'] = $datas->vendor_name;
                $outlet_info[$key]['first_name'] = $datas->first_name;
                $outlet_info[$key]['last_name'] = $datas->last_name;
                $outlet_info[$key]['featured_image'] = $datas->featured_image;
                $outlet_info[$key]['logo_image'] = $datas->logo_image;
                $outlet_info[$key]['vendors_delivery_time'] = $datas->vendors_delivery_time;
                $outlet_info[$key]['category_ids'] = $datas->category_ids;
                $outlet_info[$key]['vendors_average_rating'] = ($datas->vendors_average_rating == null) ? 0 : $datas->vendors_average_rating;
                $outlet_info[$key]['outlets_contact_address'] = $datas->outlets_contact_address;
                $outlet_info[$key]['outlets_vendors_id'] = $datas->outlets_vendors_id;
                $outlet_info[$key]['outlet_name'] = $datas->outlet_name;
                $outlet_info[$key]['outlets_delivery_time'] = $datas->outlets_delivery_time;
                $outlet_info[$key]['outlets_average_rating'] = ($datas->outlets_average_rating == null) ? 0 : $datas->outlets_average_rating;
                $outlet_info[$key]['outlets_delivery_charges_fixed'] = $datas->outlets_delivery_charges_fixed;
                $outlet_info[$key]['outlets_pickup_time'] = $datas->outlets_pickup_time;
                $outlet_info[$key]['outlets_latitude'] = $datas->outlets_latitude;
                $outlet_info[$key]['outlets_longitude'] = $datas->outlets_longitude;
                $outlet_info[$key]['url_index'] = $datas->url_index;
            }
        }
        $result = array("response" => array("httpCode" => 200, "status" => true, 'data' => $products, 'outlet_info' => $outlet_info, 'categories' => $categories));
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function addto_favourite(Request $data)
    {
        $post_data = $data->all();
        $data = array();
        $rules = [
            'user_id' => ['required'],
            'vendor_id' => ['required'],
            'token' => ['required'],
        ];
        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("response" => array("httpCode" => 200, "status" => false, "Message" => $errors));
        } else {
            try {
                $check_auth = JWTAuth::toUser($post_data['token']);
                $ucdata = DB::table('favorite_vendors')
                    ->select('favorite_vendors.id', 'favorite_vendors.status')
                    ->where("favorite_vendors.customer_id", "=", $post_data['user_id'])
                    ->where("favorite_vendors.vendor_id", "=", $post_data['vendor_id'])
                    ->get();
                if (count($ucdata)) {
                    $favourite = Favorite_vendors::find($ucdata[0]->id);
                    $favourite->status = $ucdata[0]->status ? 0 : 1;
                    $favourite->save();
                    $status = $ucdata[0]->status ? 0 : 1;

                    $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.The shop has been added to your favorites list"), "status" => 1));
                    if ($ucdata[0]->status == 1) {
                        $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.The shop has been deleted from your favorites list"), "status" => $status));
                    }
                } else {
                    //echo "asdfa";exit;
                    $favourite = new Favorite_vendors;
                    //print_r($favourite);exit;
                    $favourite->vendor_id = $post_data['vendor_id'];
                    $favourite->customer_id = $post_data['user_id'];
                    $favourite->status = 1;
                    $favourite->save();
                    $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.The shop has been added to your favorites list"), "status" => 1));
                }
            } catch (JWTException $e) {
                $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
            } catch (TokenExpiredException $e) {
                $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    /* Store information mobile */

    public function store_info_mob(Request $data)
    {
        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $data = array();
        $rules = [
            'language' => ['required', 'numeric'],
            'store_id' => ['required', 'numeric'],
        ];
        $delivery_cost = "";
        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("response" => array("httpCode" => 400, "Message" => $errors));
        } else {
            $language_id = $post_data['language'];
            $store_id = $post_data["store_id"];
            $user_id = isset($post_data["user_id"]) ? $post_data["user_id"] : "";
            $token = isset($post_data["token"]) ? $post_data["token"] : "";
            $vendor_det = array();
            $vendors = Outlets::find($store_id);
            if (!count($vendors)) {
                $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Invalid Store")));
                return json_encode($result);
            }
            if ($user_id != '') {
                try {
                    $check_auth = JWTAuth::toUser($post_data['token']);
                } catch (JWTException $e) {
                    $result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
                    return json_encode($result);
                } catch (TokenExpiredException $e) {
                    $result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
                    return json_encode($result);
                }
            }
            $delivery_settings = $this->get_delivery_settings();
            if ($delivery_settings->on_off_status == 1) {
                if ($delivery_settings->delivery_type == 1) {
                    $delivery_cost = $delivery_settings->delivery_cost_fixed;
                }
                if ($delivery_settings->delivery_type == 2) {
                    $delivery_cost = $delivery_settings->flat_delivery_cost;
                }
            }
            $vendor_id = $vendors->vendor_id;
            $vendor_info = Stores::vendor_information($vendor_id);
            $store_info = Stores::store_information($language_id, $store_id);
            // print_r($store_info);exit;
            $vendor_det['vendor_id'] = $store_info->vendors_id;
            $vendor_det['vendor_name'] = $vendor_info->vendor_name;
            $featured_image = $logo_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
            if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/thumb/detail/' . $store_info->featured_image) && $store_info->featured_image != '') {
                $featured_image = url('/assets/admin/base/images/vendors/thumb/detail/' . $store_info->featured_image);
            }
            if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $store_info->logo_image) && $store_info->logo_image != '') {
                $logo_image = url('/assets/admin/base/images/vendors/logos/' . $store_info->logo_image);
            }
            $category_ids = explode(',', $store_info->category_ids);
            $category_name = '';

            $get_categorys = getVendorCategoryList($vendor_id);
            //print_r($get_categorys);exit;
            foreach ($get_categorys as $category) {
                $category_name .= $category->category_name . ', ';
            }
            //echo  $category_name;exit;
            $vendor_det['featured_image'] = $featured_image;
            $vendor_det['logo_image'] = $logo_image;
            $vendor_det['vendors_delivery_time'] = $store_info->vendors_delivery_time;
            $vendor_det['category_ids'] = rtrim($category_name, ', ');
            $vendor_det['vendors_average_rating'] = ($store_info->vendors_average_rating == null) ? 0 : $store_info->vendors_average_rating;
            $vendor_det['outlets_contact_address'] = $store_info->outlets_contact_address;
            $vendor_det['outlets_id'] = $store_info->outlets_id;
            $vendor_det['outlets_vendors_id'] = $store_info->outlets_vendors_id;
            $vendor_det['outlet_name'] = $store_info->outlet_name;
            $vendor_det['outlets_delivery_time'] = $store_info->outlets_delivery_time;
            $vendor_det['outlets_average_rating'] = ($store_info->outlets_average_rating == null) ? 0 : $store_info->outlets_average_rating;
            $vendor_det['outlets_pickup_time'] = $store_info->outlets_pickup_time;
            $vendor_det['outlets_latitude'] = $store_info->outlets_latitude;
            $vendor_det['minimum_order_amount'] = $store_info->minimum_order_amount;
            $vendor_det['outlet_location_name'] = $store_info->outlet_location_name;
            $vendor_det['outlets_longitude'] = $store_info->outlets_longitude;
            if ($delivery_cost != '') {
                $vendor_det['outlets_delivery_charges_fixed'] = $delivery_cost;
            } else {
                $vendor_det['outlets_delivery_charges_fixed'] = $store_info->outlets_delivery_charges_fixed;
            }

            $vendor_det['outlets_delivery_charges_variation'] = $store_info->outlets_delivery_charges_variation;
            // print_r($vendor_det);exit;
            $m_categories = getVendorCategoryList($vendor_id);
            $category = array();
            if (count($m_categories) > 0) {
                $c = 0;
                foreach ($m_categories as $main) {
                    $sub_category_list = Stores::get_sub_category_list($main->id);

                    //print_r($sub_category_list); exit;
                    $sub_category = array();
                    if (count($sub_category_list) > 0) {
                        $category[$c]['main_category_id'] = $main->id;
                        $category[$c]['main_category_name'] = $main->category_name;
                        $category[$c]['main_category_url_key'] = $main->url_key;
                        $s = 0;
                        foreach ($sub_category_list as $sub) {
                            $sub_category[$s]['main_category_id'] = $main->id;
                            $sub_category[$s]['sub_category_id'] = $sub->id;

                            $sub_category[$s]['sub_category_name'] = $sub->category_name;
                            $sub_category[$s]['sub_category_url_key'] = $sub->url_key;
                            $category_image = URL::asset('assets/front/tijik/images/no_image.png');
                            // print_r($category_image);exit;
                            if (file_exists(base_path() . '/public/assets/admin/base/images/category/' . $sub->image) && $sub->image != '') {
                                $category_image = url('/assets/admin/base/images/category/' . $sub->image);
                            }

                            $sub_category[$s]['category_image'] = $category_image;

                            $subcat = getSubCategoryListsupdated(1, $sub->id);
                            $sub_category[$s]['child_sub_categories'] = $subcat;
                            $s++;
                        }
                        $category[$c]['sub_category_list'] = $sub_category;
                        $c++;
                    }
                }
            }
            $vendor_det['category_list'] = $category;
            $fstatus = $cart_count = 0;
            if ($user_id) {
                $vendor_fav = Stores::vendor_fav_info($user_id, $store_id);
                $user_cart_info = Stores::user_cart_information($user_id);
                if (count($vendor_fav)) {
                    $fstatus = $vendor_fav->status;
                }
                if (count($user_cart_info)) {
                    $cart_count = $user_cart_info->cart_count;
                }
            }
            $vendor_det['user_favourite'] = $fstatus;
            $vendor_det['cart_count'] = $cart_count;

            $time_interval = $this->get_delivery_time_interval();
            $delivery_slots = $this->get_delivery_slots();
            $date = date('Y-m-d'); //today date
            $weekOfdays = $week = $deliver_slot_array = $u_time = array();
            $datetime = new \DateTime();
            $datetime->modify('+1 day');
            //$listItem = array('<li class="active">', '</li>');
            $i = 0;
            $weekarray = array();
            while (true) {
                if ($i === 7) {
                    break;
                }

                // echo $datetime->format('N');exit;
                if ($datetime->format('N') === '7' && $i === 0) {
                    $datetime->add(new \DateInterval('P1D'));
                    continue;
                }
                $weekarray[] = $datetime->format('N');
                $j = $datetime->format('N');
                $datetime->add(new \DateInterval('P1D'));
                $wk_day = date('N', strtotime($date));
                $weekOfdays[$j] = date('d M', strtotime($date));
                $weekOfdays_mob[] = date('d-m-Y', strtotime($date));
                $weekday = date('l', strtotime('+1 day', strtotime($date)));

                // $date = date('Y-m-d', strtotime('+1 day', strtotime($date)));

                $deliver_slot_array[$weekday] = "";
                foreach ($time_interval as $time) {
                    $slot_id = $this->check_value_exist($delivery_slots, $time->id, $j, 'time_interval_id', 'day');
                    if ($slot_id != 0) {
                        $deliver_slot_array[$weekday] .= date('g:i a', strtotime($time->start_time)) . ' - ' . date('g:i a', strtotime($time->end_time)) . ",";
                    }
                }
                $week[$j] = date('l', strtotime($date));
                $week_mob[] = date('l', strtotime($date));
                $date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
                $i++;
            }

            end($deliver_slot_array);
            $key = key($deliver_slot_array);

            array_pop($deliver_slot_array);
            //print_r($deliver_slot_array);

            $final_delivery_slot[$key] = end($deliver_slot_array);

            //array_merge($final_delivery_slot,$final_delivery_slot);

            $output = array_merge($final_delivery_slot, $deliver_slot_array);
            //print_r($output);
            //exit;
            // $last = end($deliver_slot_array);
            //key($last)
            // print_r($deliver_slot_array);exit;
            //print_r($deliver_slot_array);exit;
            /*$d = $dj = 0;
            $available_s_time = $week_new = array();
            $w_day = '';
            $week_date = $date = date('d-m-Y');
            $avaliable_slot_mob = $this->get_avaliable_slot_mobl();
            foreach($avaliable_slot_mob as $key => $mobile_slot)
            {
                $current_day = date('w') + 1;echo $week_new[$mobile_slot->day];die;
                if($w_day != $week_new[$mobile_slot->day])
                {echo 2111;die;
                    if($w_day != '')
                        $d++;
                    $w_day = $week_new[$mobile_slot->day];
                    if($current_day < $mobile_slot->day)
                    {
                        $min_date  = $mobile_slot->day - $current_day;
                        $week_date = date('d-m-Y', strtotime('+'.$min_date.' day', strtotime($date)));
                    }
                    else if($current_day == $mobile_slot->day) {
                        $week_date = $date;
                    }
                    else if($current_day > $mobile_slot->day) {
                        $total_week_day = 7;
                        $rem_day = $total_week_day - $current_day;
                        $tot_day = $rem_day + $mobile_slot->day;
                        $week_date = date('d-m-Y', strtotime('+'.$tot_day.' day', strtotime($date)));
                    }
                    $available_s_time = array();
                    $available_slot_new[$d]['day'] = date('l',strtotime($week_date));
                    $dj = 0;
                }echo 11;die;
                $available_s_time[$dj]['week_mob_time'][date('l',strtotime($week_date))][] = date('g:i a', strtotime($mobile_slot->start_time)).' - '.date('g:i a', strtotime($mobile_slot->end_time));
                $available_slot_new[$d]['time']   = $available_s_time;
                $dj++;
            }*/
            //~ $vendor_det['deliver_slot'] = $available_slot_new;
            $timearray = getDaysWeekArray();
            foreach ($timearray as $key1 => $val1) {
                $u_time[$key1] = $this->getOpenTimings($store_id, $val1);
            }
            if (count($u_time)) {
                $ctime = array();
                $k = 0;
                foreach ($u_time as $key3 => $value) {
                    if (isset($value[0])) {
                        $ctime[$k]['id'] = $value[0]->id;
                        $ctime[$k]['created_date'] = $value[0]->created_date;
                        $ctime[$k]['vendor_id'] = $value[0]->vendor_id;
                        $ctime[$k]['day'] = $key3;
                        $ctime[$k]['day_week'] = $value[0]->day_week;
                        $ctime[$k]['start_time'] = date('g:i a', strtotime($value[0]->start_time));
                        $ctime[$k]['end_time'] = date('g:i a', strtotime($value[0]->end_time));
                        $k++;
                    }
                }
            }
            $vendor_det['deliver_slot'] = $output;
            $vendor_det['open_time'] = $ctime;

            $result = array("response" => array("httpCode" => 200, "Message" => "Vendor Information", "vendor_detail" => $vendor_det));
        }
        return json_encode($result);
    }
    public function get_avaliable_slot_mobl()
    {
        $available_slots = DB::select('SELECT dts.day,dti.start_time,dti.end_time,dts.id AS slot_id
        FROM delivery_time_slots dts
        LEFT JOIN delivery_time_interval dti ON dti.id = dts.time_interval_id');
        return $available_slots;
    }
    /* To get delivery time interval */

    public function get_delivery_time_interval()
    {
        $time_interval = DB::table('delivery_time_interval')
            ->select('id', 'start_time', 'end_time')
            ->orderBy('start_time', 'asc')
            ->get();
        return $time_interval;
    }
    public function get_delivery_slots()
    {
        $delivery_slots = DB::table('delivery_time_slots')
            ->select('*')
            ->get();
        return $delivery_slots;
    }

    /* To get store open timings */

    public function getOpenTimings($v_id, $day_week)
    {
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
    public function check_value_exist($delivery_slots, $interval_id, $day, $key1, $key2)
    {
        foreach ($delivery_slots as $slots) {
            if (is_array($slots) && check_value_exist($delivery_slots, $interval_id, $day, $key1, $key2)) {
                return $slots->id;
            }

            if (isset($slots->$key1) && $slots->$key1 == $interval_id && isset($slots->$key2) && $slots->$key2 == $day) {
                return $slots->id;
            }
        }
        return 0;
    }

    /* Store product list based on category and sub category id */
    /* Store information mobile */

    public function store_product(Request $data)
    {
        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $data = array();
        $rules = [
            'language' => ['required', 'numeric'],
            'store_id' => ['required', 'numeric'],
            'outlet_id' => ['required', 'numeric'],
            'category_id' => ['required', 'numeric'],
            'sub_category_id' => ['required', 'numeric'],
        ];
        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("response" => array("httpCode" => 400, "Message" => $errors));
        } else {
            $language_id = $post_data['language'];
            $store_id = $post_data['store_id'];
            $outlet_id = $post_data['outlet_id'];
            $category_id = $post_data['category_id'];
            $sub_category_id = $post_data['sub_category_id'];
            $vendor_det = array();
            $vendors = Vendors::find($store_id);
            if (!count($vendors)) {
                $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Invalid Store")));
                return json_encode($result);
            }
            $product_list = Stores::product_list($language_id, $store_id, $outlet_id, $category_id, $sub_category_id);
            $product = array();
            if (count($product_list) > 0) {
                $p = 0;
                foreach ($product_list as $pro) {
                    $product[$p]['product_id'] = $pro->product_id;
                    $product[$p]['product_url'] = $pro->product_url;
                    $product[$p]['weight'] = $pro->weight;
                    $product[$p]['original_price'] = $pro->original_price;
                    $product[$p]['discount_price'] = $pro->discount_price;
                    $product[$p]['category_id'] = $pro->id;
                    $product[$p]['unit'] = $pro->unit;
                    $product[$p]['description'] = $pro->description;
                    $product[$p]['title'] = $pro->title;
                    $product[$p]['outlet_name'] = $pro->outlet_name;
                    $product[$p]['average_rating'] = $pro->average_rating;
                    $product_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $pro->product_image) && $pro->product_image != '') {
                        $product_image = url('/assets/admin/base/images/products/list/' . $pro->product_image);
                    }
                    $product[$p]['product_image'] = $product_image;
                    $p++;
                }
            }
            $currency_symbol = getCurrency();
            $currency_side = getCurrencyPosition()->currency_side;
            $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Product list"), "product_list" => $product, "currency_symbol" => $currency_symbol, "currency_side" => $currency_side));
        }
        return json_encode($result);
    }

    /* Store product list based on category and sub category id */
    /* Store information mobile */

    public function store_product_mob(Request $data)
    {
        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $data = array();
        $rules = [
            'language' => ['required', 'numeric'],
            'store_id' => ['required', 'numeric'],
            'outlet_id' => ['required', 'numeric'],
            'category_id' => ['required', 'numeric'],
            'sub_category_id' => ['required', 'numeric'],
        ];
        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("response" => array("httpCode" => 400, "Message" => $errors));
        } else {
            $language_id = $post_data['language'];
            $store_id = $post_data['store_id'];
            $outlet_id = $post_data['outlet_id'];
            $category_id = $post_data['category_id'];
            $sub_category_id = $post_data['sub_category_id'];
            $product_sub_category_id = isset($post_data['product_sub_category_id']) ? $post_data['product_sub_category_id'] : "";
            $product_name = isset($post_data['product_name']) ? $post_data['product_name'] : "";
            $user_id = isset($post_data['user_id']) ? $post_data['user_id'] : "";
            $token = isset($post_data['token']) ? $post_data['token'] : "";
            if ($user_id != '' && $token != '') {
                try {
                    $check_auth = JWTAuth::toUser($post_data['token']);
                } catch (JWTException $e) {
                    $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
                    return json_encode($result);
                } catch (TokenExpiredException $e) {
                    $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
                    return json_encode($result);
                }
            }
            $vendor_det = array();
            $vendors = Vendors::find($store_id);
            if (!count($vendors)) {
                $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Invalid Store")));
                return json_encode($result);
            }
            $product_list = Stores::product_list_mob($language_id, $store_id, $outlet_id, $category_id, $sub_category_id, $product_name, $product_sub_category_id);
            //print_r($product_list);exit;
            $product = array();
            if (count($product_list) > 0) {
                $p = 0;
                foreach ($product_list as $pro) {
                    $product[$p]['product_id'] = $pro->product_id;
                    $product[$p]['product_name'] = $pro->product_name;
                    $product[$p]['product_url'] = $pro->product_url;
                    $product[$p]['weight'] = $pro->weight;
                    $product[$p]['original_price'] = $pro->original_price;
                    $product[$p]['discount_price'] = $pro->discount_price;
                    $product[$p]['category_id'] = $pro->id;
                    $product[$p]['unit'] = $pro->unit;
                    $product[$p]['description'] = $pro->description;
                    $product[$p]['title'] = $pro->title;
                    $product[$p]['outlet_id'] = $pro->outlet_id;
                    $product[$p]['outlet_name'] = $pro->outlet_name;
                    $product[$p]['vendorId'] = $post_data['vendorId'];
                    $product[$p]['average_rating'] = ($pro->average_rating == null) ? 0 : $pro->average_rating;
                    $product_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $pro->product_image) && $pro->product_image != '') {
                        $product_image = url('/assets/admin/base/images/products/list/' . $pro->product_image);
                    }
                    $product[$p]['product_image'] = $product_image;

                    $product_info_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/products/detail/' . $pro->product_info_image) && $pro->product_info_image != '') {
                        $product_info_image = url('/assets/admin/base/images/products/detail/' . $pro->product_info_image);
                    }
                    $product[$p]['product_info_image'] = $product_info_image;

                    $product_zoom_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/products/zoom/' . $pro->product_zoom_image) && $pro->product_zoom_image != '') {
                        $product_zoom_image = url('/assets/admin/base/images/products/zoom/' . $pro->product_zoom_image);
                    }
                    $product[$p]['product_zoom_image'] = $product_zoom_image;

                    $cart_count = 0;
                    if ($user_id != '') {
                        $cart_count = $this->get_cart_product_count($user_id, $pro->product_id);
                    }
                    $product[$p]['cart_count'] = $cart_count;
                    $p++;
                }
            }

            //print_r(getCurrency());exit;
            $currency_symbol = getCurrency($language_id);
            $currency_side = getCurrencyPosition()->currency_side;
            $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Product list"), "product_list" => $product, "currency_symbol" => $currency_symbol, "currency_side" => $currency_side));
        }
        return json_encode($result);
    }

    /* Store product list based on category and sub category id */
    /* Store information mobile */

    public function store_review(Request $data)
    {
        $post_data = $data->all();
        $data = array();
        $rules = [
            'store_id' => ['required', 'numeric'],
            'outlet_id' => ['required', 'numeric'],
        ];
        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result =  array("status" => 0, "message" => $errors);
        } else {
            $store_id = $post_data['store_id'];
            $outlet_id = $post_data['outlet_id'];
            $skipSize = $post_data['skipSize'];
            $pageSize = $post_data['pageSize'];
            $startDate = $post_data['startDate'];
            $endDate = $post_data['endDate'];
            $rating = $post_data['rating'];
            $vendor_det = array();
            $vendors = Vendors::find($store_id);
            if (!count($vendors)) {
                $result = array( "status" => 0, "message" => trans("messages.Invalid Store"));
                return json_encode($result);
            }
            
            $outlet_reviews = Stores::outlet_reviews($store_id, $outlet_id, $pageSize, $skipSize, $startDate, $endDate, $rating);
           

            $outletsRating=DB::select("select round(Avg(ratings),1) as total from outlet_reviews where ratings != '-1'  and outlet_id=$outlet_id");

            $array = array();
            if (count($outletsRating) > 0) {
                foreach ($outletsRating as $ord) {
                    $array['overAllRating'] = $ord->total;

                    $total= $ord->total;
                }
            }
            $review = array();
            if (count($outlet_reviews) > 0) {
                $r = 0;
                foreach ($outlet_reviews as $rev) {
                    $review[$r]['review_id'] = $rev->review_id;
                    $review[$r]['title'] = ($rev->title != '') ? $rev->title : 'test';
                    $review[$r]['comments'] = $rev->comments;
                    $review[$r]['ratings'] = $rev->ratings;
                    $review[$r]['created_date'] = $rev->created_date;
                    $review[$r]['user_id'] =($rev->id != '') ? ucfirst($rev->id) : '123';
                    // $review[$r]['user_id'] = $rev->id;
                    $review[$r]['first_name'] = ($rev->first_name != '') ? ucfirst($rev->first_name) : 'demo';
                    $review[$r]['last_name'] = ($rev->last_name != '') ? ucfirst($rev->last_name) : 'demo';
                    $review[$r]['name'] = ($rev->name != '') ? ucfirst($rev->name) : 'demo';
                    // $review[$r]['name'] = $rev->name;
                    $review_image = URL::asset('assets/admin/base/images/a2x.jpg');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/admin/profile/thumb/' . $rev->image) && $rev->image != '') {
                        $review_image = url('/assets/admin/base/images/admin/profile/thumb/' . $rev->image . '?' . time());
                    }
                    $review[$r]['image'] = $review_image;
                    $r++;
                }
            }

           
            $result =  array("status" => 1, "message" => trans("messages.Store review list"),"overAllRating"=>(int)$total, "review_list" => $review);
        }
        return json_encode($result);
    }



    /* Store product list based on category and sub category id */
    /* Store information mobile */

    public function store_list_mob(Request $data)
    {
        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $language = isset($post_data['language']) ? $post_data['language'] : "";
        $category_ids = isset($post_data['category_ids']) ? $post_data['category_ids'] : "";
        $category_url = isset($post_data['category_url']) ? $post_data['category_url'] : "";
        $city = isset($post_data['city']) ? $post_data['city'] : "";
        $location = isset($post_data['location']) ? $post_data['location'] : "";
        $keyword = isset($post_data['keyword']) ? $post_data['keyword'] : "";
        $sortby = isset($post_data['sortby']) ? $post_data['sortby'] : "";
        $orderby = isset($post_data['orderby']) ? $post_data['orderby'] : "desc";
        $stores = array();
        $stores_list = Stores::stores_list($language, $category_ids, $category_url, $city, $location, $keyword, $sortby, $orderby);
        $banners = DB::table('banner_settings')->select('*')->where('banner_type', 2)->where('status', 1)->orderBy('default_banner', 'desc')->get();
        $banner_list = array();
        if (count($banners) > 0) {
            foreach ($banners as $key => $items) {
                $banners[$key]->banner_image_url = url('/assets/admin/base/images/' . $items->banner_image . '?' . time());
            }
        }
        if (count($stores_list) > 0) {
            $s = 0;
            $vendors_id = '';
            foreach ($stores_list as $st) {
                if ($vendors_id != $st->vendors_id) {
                    $vendors_id = $st->vendors_id;
                    $stores[$s]['vendors_id'] = $st->vendors_id;
                    $stores[$s]['vendor_name'] = $st->vendor_name;
                    $stores[$s]['category_ids'] = $st->category_ids;
                    $stores[$s]['contact_address'] = $st->contact_address;
                    $stores[$s]['vendor_description'] = $st->vendor_description;
                    $stores[$s]['vendors_delivery_time'] = $st->vendors_delivery_time;
                    $stores[$s]['delivery_charges_fixed'] = $st->delivery_charges_fixed;
                    $stores[$s]['delivery_cost_variation'] = $st->delivery_cost_variation;
                    $stores[$s]['minimum_order_amount'] = $st->minimum_order_amount;
                    $stores[$s]['vendors_average_rating'] = ($st->vendors_average_rating == null) ? 0 : $st->vendors_average_rating;
                    $category_ids = explode(',', $st->category_ids);
                    $category_name = '';
                    if (count($category_ids) > 0) {
                        foreach ($category_ids as $cate) {
                            $get_category_name = getCategoryListsById($cate);

                            if (count($get_category_name) > 0) {
                                //echo "in";exit;
                                $category_name .= $get_category_name->category_name . ', ';
                            }
                        }
                    }
                    $stores[$s]['category_ids'] = rtrim($category_name, ', ');
                    $logo_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/list/' . $st->logo_image) && $st->logo_image != '') {
                        $logo_image = url('/assets/admin/base/images/vendors/list/' . $st->logo_image . '?' . time());
                    }
                    $stores[$s]['logo_image'] = $logo_image;
                    $stores[$s]['logo_image'] = $logo_image;
                    $outlet_count = Stores::get_outlet_count($st->vendors_id, $city, $location);
                    if (count($outlet_count) > 0) {
                        $stores[$s]['outlets_count'] = (int) $outlet_count->outlets_count;
                        if ($outlet_count->outlets_count == 1) {
                            $get_outlet_id = Stores::get_outlet_id_by_store($st->vendors_id);
                            $stores[$s]['outlets_id'] = (int) $get_outlet_id->outlets_id;
                        } else {
                            $stores[$s]['outlets_id'] = '';
                        }
                    } else {
                        $stores[$s]['outlets_count'] = 0;
                        $stores[$s]['outlets_id'] = '';
                    }
                    $s++;
                }
            }
        }
        $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Store list"), "store_list" => $stores, "banners" => $banners));
        return json_encode($result);
    }

    /* outlet list by store id */

    public function store_outlet_list(Request $data)
    {
        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $data = array();
        $rules = [
            'store_id' => ['required', 'numeric'],
            'language' => ['required', 'integer'],
        ];
        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("response" => array("httpCode" => 400, "Message" => $errors));
        } else {
            $store_id = $post_data['store_id'];
            $language = $post_data['language'];
            $city = isset($post_data['city']) ? $post_data['city'] : "";
            $location = isset($post_data['location']) ? $post_data['location'] : "";
            $outlet = array();
            $outlet_list = Stores::get_outlet_list($store_id, $language, $city, $location);
            $vendor_name = '';
            if (count($outlet_list) > 0) {
                $o = 0;
                foreach ($outlet_list as $out) {
                    $outlet[$o]['outlets_id'] = $out->outlets_id;
                    $outlet[$o]['outlets_vendors_id'] = $out->outlets_vendors_id;
                    $outlet[$o]['outlet_name'] = $out->outlet_name;
                    $outlet[$o]['contact_address'] = $out->contact_address;
                    $outlet[$o]['outlets_delivery_time'] = $out->outlets_delivery_time;
                    $outlet[$o]['delivery_charges_fixed'] = $out->delivery_charges_fixed;
                    $outlet[$o]['minimum_order_amount'] = $out->minimum_order_amount;
                    $outlet[$o]['outlets_average_rating'] = ($out->outlets_average_rating == null) ? 0 : $out->outlets_average_rating;
                    $outlet[$o]['delivery_charges_variation'] = $out->delivery_charges_variation;
                    $outlet[$o]['outlet_location_name'] = $out->outlet_location_name;
                    $logo_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $out->logo_image) && $out->logo_image != '') {
                        $logo_image = url('/assets/admin/base/images/vendors/logos/' . $out->logo_image);
                    }
                    $outlet[$o]['logo_image'] = $logo_image;
                    $category_ids = explode(',', $out->category_ids);
                    $category_name = '';
                    if (count($category_ids) > 0) {
                        foreach ($category_ids as $cate) {
                            $get_category_name = getCategoryListsById($cate);
                            $category_name .= $get_category_name->category_name . ', ';
                        }
                    }
                    $outlet[$o]['category_ids'] = rtrim($category_name, ', ');
                    $vendor_name = $out->vendor_name;
                    $o++;
                }
            }
            $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Outlets list"), "outlet_list" => $outlet, "vendor_name" => $vendor_name));
        }
        return json_encode($result);
    }

    /* Store banner list */

    public function store_banner()
    {
        $banner_list = Api_Model::get_store_banner_list();
        $banner = array();

        if (count($banner_list) > 0) {
            $b = 0;
            foreach ($banner_list as $bnr) {
                $banner[$b]['banner_id'] = $bnr->banner_setting_id;
                $banner[$b]['banner_title'] = $bnr->banner_title;
                $banner[$b]['banner_subtitle'] = $bnr->banner_subtitle;
                $banner_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_slid1.png');
                if (file_exists(base_path() . '/public/assets/admin/base/images/banner/' . $bnr->banner_image) && $bnr->banner_image != '') {
                    $banner_image = url('/assets/admin/base/images/banner/' . $bnr->banner_image);
                }
                $banner[$b]['banner_image'] = $banner_image;
                $banner[$b]['banner_link'] = $bnr->banner_link;
                $b++;
            }
        }
        $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Banner list"), "banner_list" => $banner));
        return json_encode($result);
    }

    public function store_featurelist_mob(Request $data)
    {
        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $language = isset($post_data['language']) ? $post_data['language'] : "";
        $category_ids = isset($post_data['category_ids']) ? $post_data['category_ids'] : "";
        $category_url = isset($post_data['category_url']) ? $post_data['category_url'] : "";
        $city = isset($post_data['city']) ? $post_data['city'] : "";
        $location = isset($post_data['location']) ? $post_data['location'] : "";
        $keyword = isset($post_data['keyword']) ? $post_data['keyword'] : "";
        $sortby = isset($post_data['sortby']) ? $post_data['sortby'] : "";
        $orderby = isset($post_data['orderby']) ? $post_data['orderby'] : "desc";
        $stores = array();
        $stores_list = Stores::feature_stores_list($language, $category_ids, $category_url, $city, $location, $keyword, $sortby, $orderby);
        if (count($stores_list) > 0) {
            //$outlet_count = Stores::get_outlet_count(18);
            //print_r($outlet_count); exit;
            $vendors_id = '';
            $s = 0;
            foreach ($stores_list as $st) {
                if ($vendors_id != $st->vendors_id) {
                    $vendors_id = $st->vendors_id;
                    $stores[$s]['vendors_id'] = $st->vendors_id;
                    $stores[$s]['vendor_name'] = $st->vendor_name;
                    $stores[$s]['category_ids'] = $st->category_ids;
                    $stores[$s]['contact_address'] = $st->contact_address ? $st->contact_address : '';
                    $stores[$s]['vendor_description'] = $st->vendor_description ? $st->vendor_description : '';
                    $stores[$s]['vendors_delivery_time'] = $st->vendors_delivery_time;
                    $stores[$s]['vendors_average_rating'] = ($st->vendors_average_rating == null) ? 0 : $st->vendors_average_rating;
                    $stores[$s]['featured_vendor'] = $st->featured_vendor;
                    $logo_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/list/' . $st->logo_image) && $st->logo_image != '') {
                        $logo_image = url('/assets/admin/base/images/vendors/list/' . $st->logo_image);
                    }
                    $stores[$s]['logo_image'] = $logo_image;
                    $outlet_count = Stores::get_outlet_count($st->vendors_id);
                    if (count($outlet_count) > 0) {
                        $stores[$s]['outlets_count'] = (int) $outlet_count->outlets_count;
                        if ($outlet_count->outlets_count == 1) {
                            $get_outlet_id = Stores::get_outlet_id_by_store($st->vendors_id);
                            $stores[$s]['outlets_id'] = (int) $get_outlet_id->outlets_id;
                        } else {
                            $stores[$s]['outlets_id'] = '';
                        }
                    } else {
                        $stores[$s]['outlets_count'] = 0;
                        $stores[$s]['outlets_id'] = '';
                    }
                    $s++;
                }
            }
        }

        $category_list = Stores::getCategoryLists(2, $language);

        $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Store list"), "store_list" => $stores, "category_list" => $category_list));
        return json_encode($result);
    }

    public function cart_count(Request $data)
    {
        $post_data = $data->all();
        $user_id = $post_data['user_id'];
        try {
            $check_auth = JWTAuth::toUser($post_data['token']);
            $user_cart_info = Stores::user_cart_information($user_id);
            $cart_count = 0;
            if (count($user_cart_info)) {
                $cart_count = $user_cart_info->cart_count;
            }
            $result = array("response" => array("httpCode" => 200, "cart_count" => $cart_count));
        } catch (JWTException $e) {
            $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
        } catch (TokenExpiredException $e) {
            $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
        }
        return $result;
    }
    public function product_details(Request $data)
    {
        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $user_id = isset($post_data['user_id']) ? $post_data['user_id'] : "";
        $product_url = $post_data['product_url'];
        $outlet_url = $post_data['outlet_url'];
        $language_id = $post_data['language'];
        $result = array("response" => array("httpCode" => 400, "status" => false, "data" => $data));
        $condtion = " products.active_status = 1";
        $pquery = '"products_infos"."lang_id" = (case when (select count(products_infos.lang_id) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and products.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $wquery = '"weight_classes_infos"."lang_id" = (case when (select count(weight_classes_infos.lang_id) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . $language_id . ' and products.weight_class_id = weight_classes_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $products = Products::join('products_infos', 'products.id', '=', 'products_infos.id')
            ->join('weight_classes_infos', 'weight_classes_infos.id', '=', 'products.weight_class_id')
            ->join('outlets', 'outlets.id', '=', 'products.outlet_id')
            ->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
            ->select('products.id as product_id', 'products.product_url', 'products.product_info_image', 'products.product_zoom_image', 'products.product_image', 'products.weight', 'products.original_price', 'products.discount_price', 'products.vendor_id', 'products.outlet_id', 'products_infos.description', 'products_infos.product_name', 'products.category_id', 'weight_classes_infos.unit', 'weight_classes_infos.title', 'outlet_infos.outlet_name', 'outlets.url_index as outlet_url_index')
            ->whereRaw($pquery)
            ->whereRaw($wquery)
            ->where('outlets.url_index', '=', $outlet_url)
            ->where('products.product_url', '=', $product_url)
            ->first();

        if (count($products) > 0) {
            $products->product_cart_count = 0;
            if (!empty($user_id)) {
                $products->product_cart_count = $this->get_cart_product_count($user_id, $products->product_id);
            }
            $result = array("response" => array("httpCode" => 200, "status" => true, 'data' => $products));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    public function get_delivery_settings()
    {
        $delivery_settings = DB::table('delivery_settings')
            ->first();
        return $delivery_settings;
    }

    //mob Apis:

   /* public function mstore_product_mob(Request $data)
    {
        $post_data = $data->all();
        //print_r("expression");exit();


        $data = array();
        $rules = [
            'language' => ['required'],
            'vendorId' => ['required', 'numeric'],
            'outletId' => ['required', 'numeric'],
            'childCategoryId' => ['required', 'numeric'],
            'subCategoryId' => ['required', 'numeric'],
        ];
        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("httpCode" => 400, "status" => $errors);
        } else {
            $language_id = 1;
            // if ($post_data['language'] == "ar") {
            //     $language_id = 2;
            // }
            if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
            } else {
            App::setLocale('en');
            }


            $store_id = $post_data['vendorId'];
            $outlet_id = $post_data['outletId'];
            $category_id = $post_data['childCategoryId'];
            $sub_category_id = $post_data['subCategoryId'];
            $product_sub_category_id = isset($post_data['product_sub_category_id']) ? $post_data['product_sub_category_id'] : "";
            $product_name = isset($post_data['product_name']) ? $post_data['product_name'] : "";
            $user_id = isset($post_data['user_id']) ? $post_data['user_id'] : "";
            $token = isset($post_data['token']) ? $post_data['token'] : "";
            if ($user_id != '' && $token != '') {
                try {
                    $check_auth = JWTAuth::toUser($post_data['token']);
                } catch (JWTException $e) {
                    $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
                    return json_encode($result);
                } catch (TokenExpiredException $e) {
                    $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
                    return json_encode($result);
                }
            }
            $vendor_det = array();
            $vendors = Vendors::find($store_id);
            if (!count($vendors)) {
                $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Invalid Store")));
                return json_encode($result);
            }
            $product_list = Stores::product_list_mob($language_id, $store_id, $outlet_id, $category_id, $sub_category_id, $product_name, $product_sub_category_id);
            //print_r("products".$product_list);exit;
            $product = array();
            if (count($product_list) > 0) {
                $p = 0;
                foreach ($product_list as $pro) {
                    $product[$p]['productId'] = $pro->product_id;
                    $product[$p]['productName'] = $pro->product_name;
                    $product[$p]['productUrl'] = $pro->product_url;
                    $product[$p]['weight'] = $pro->weight;
                    $product[$p]['originalPrice'] = floatval($pro->original_price);
                    $product[$p]['discountPrice'] = floatval($pro->discount_price);
                    $product[$p]['categoryId'] = $pro->id;
                    $product[$p]['unit'] = $pro->unit;
                    $product[$p]['description'] = $pro->description;
                    $product[$p]['title'] = $pro->title;
                    $product[$p]['vendorId'] = $post_data['vendorId'];
                    $product[$p]['outletId'] = $pro->outlet_id;
                    $product[$p]['outletName'] = $pro->outlet_name;
                    $item_quantity = array();
                    $item_quantity[0]["subId"] = "1";
                    $item_quantity[0]["itemUnit"] = $pro->unit;
                    $item_quantity[0]["itemNewPrice"] = floatval($pro->discount_price);
                    $item_quantity[0]["itemOldPrice"] = floatval($pro->original_price);
                    $item_quantity[0]['itemWeight'] = $pro->weight;
                    $product[$p]['itemQuantity'] = $item_quantity;
                    $product[$p]['averageRating'] = ($pro->average_rating == null) ? 0 : $pro->average_rating;
                    $product[$p]['item_limit'] = isset($pro->item_limit)?$pro->item_limit:0;
                    $product_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');

                    if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $pro->product_image) && $pro->barcode != '') {
                        $product_image = url('/assets/admin/base/images/products/list/' . $pro->product_image);
                        //echo( $product_image );exit;
                    }
                    $product[$p]['productImage'] = $product_image;
                    $product[$p]['productInfoImage'] = $product_image;
                    $product[$p]['productZoomImage'] = $product_image;

                    // $product_info_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    // if (file_exists(base_path() . '/public/assets/admin/base/images/products/detail/' . $pro->product_info_image) && $pro->product_info_image != '') {
                    //     $product_info_image = url('/assets/admin/base/images/products/detail/' . $pro->product_info_image);
                    // }
                    // $product[$p]['productInfoImage'] = $product_info_image;

                    // $product_zoom_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    // if (file_exists(base_path() . '/public/assets/admin/base/images/products/zoom/' . $pro->product_zoom_image) && $pro->product_zoom_image != '') {
                    //     $product_zoom_image = url('/assets/admin/base/images/products/zoom/' . $pro->product_zoom_image);
                    // }
                    // $product[$p]['productZoomImage'] = $product_zoom_image;

                    $cart_count = 0;
                    if ($user_id != '') {
                        $cart_count = $this->get_cart_product_count($user_id, $pro->product_id);
                    }
                    $product[$p]['cartCount'] = $cart_count;
                    $p++;
                }
            }

            //print_r(getCurrency());exit;
            $currency_symbol = getCurrency($language_id);
            $currency_side = getCurrencyPosition()->currency_side;
            $result = array("status" => 1, "message" => trans("messages.Product list"), "productList" => $product, "currencySymbol" => $currency_symbol, "currencySide" => $currency_side);
        }
        return json_encode($result);
    }*/
    public function mstore_product_mob(Request $data)
    {
        $post_data = $data->all();
        //print_r("expression");exit();


        $data = array();
        $rules = [
            'language' => ['required'],
            'vendorId' => ['required', 'numeric'],
            'outletId' => ['required', 'numeric'],
            'childCategoryId' => ['required', 'numeric'],
            'subCategoryId' => ['required', 'numeric'],
        ];
        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("httpCode" => 400, "status" => $errors);
        } else {
            $language_id = 1;
            if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
            } else {
            App::setLocale('en');
            }


            $store_id = $post_data['vendorId'];
            $outlet_id = $post_data['outletId'];
            $child_category_id = $post_data['childCategoryId'];
            $sub_category_id = $post_data['subCategoryId'];
            $product_sub_category_id = isset($post_data['product_sub_category_id']) ? $post_data['product_sub_category_id'] : "";
            $product_name = isset($post_data['product_name']) ? $post_data['product_name'] : "";
            $user_id = isset($post_data['user_id']) ? $post_data['user_id'] : "";
            $token = isset($post_data['token']) ? $post_data['token'] : "";
            if ($user_id != '' && $token != '') {
                try {
                    $check_auth = JWTAuth::toUser($post_data['token']);
                } catch (JWTException $e) {
                    $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
                    return json_encode($result);
                } catch (TokenExpiredException $e) {
                    $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
                    return json_encode($result);
                }
            }
            $vendor_det = array();
            $vendors = Vendors::find($store_id);
            if (!count($vendors)) {
                $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Invalid Store")));
                return json_encode($result);
            }
            $product_list = Stores::product_list_mob($language_id, $store_id, $outlet_id, $child_category_id, $sub_category_id, $product_name, $product_sub_category_id);
           // print_r("products".$product_list);exit;
            $product = array();
            if (count($product_list) > 0) {
                $p = 0;
                foreach ($product_list as $pro) {
                    $product[$p]['productId'] = $pro->product_id;
                    $product[$p]['productName'] = $pro->product_name;
                    $product[$p]['productUrl'] = $pro->product_url;
                    $product[$p]['weight'] = $pro->weight;
                    $product[$p]['originalPrice'] = floatval($pro->original_price);
                    $product[$p]['discountPrice'] = floatval($pro->discount_price);
                    $product[$p]['categoryId'] = $pro->id;
                    $product[$p]['unit'] = $pro->unit;
                    $product[$p]['description'] = $pro->description;
                    $product[$p]['title'] = $pro->title;
                    $product[$p]['vendorId'] = $post_data['vendorId'];
                    $product[$p]['outletId'] = $pro->outlet_id;
                    $product[$p]['outletName'] = $pro->outlet_name;
                    $item_quantity = array();
                    $item_quantity[0]["subId"] = "1";
                    $item_quantity[0]["itemUnit"] = $pro->unit;
                    $item_quantity[0]["itemNewPrice"] = floatval($pro->discount_price);
                    $item_quantity[0]["itemOldPrice"] = floatval($pro->original_price);
                    $item_quantity[0]['itemWeight'] = $pro->weight;
                    $product[$p]['itemQuantity'] = $item_quantity;
                    $product[$p]['averageRating'] = ($pro->average_rating == null) ? 0 : $pro->average_rating;
                    $product[$p]['item_limit'] = isset($pro->item_limit)?$pro->item_limit:0;

                    $no_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
                    /*
                    if (file_exists(base_path() . '/public/assets/admin/base/images/products/admin_products/' . $pro->image) && $pro->barcode != '') {
                        $product_image = url('/assets/admin/base/images/products/admin_products/' . $pro->image);
                       // echo( $product_image );exit;
                    }

                    */
                    $path = url('/assets/admin/base/images/products/admin_products/');

                    $productImage=json_decode($pro->image);
                    $productInfoImage=json_decode($pro->product_info_image);
                    $productZoomImage=json_decode($pro->product_zoom_image);
                    $image1 =$image2=$image3 =array();
                    //print_r($productImage);exit();
                    $image1[]= $no_image;

                    if($productImage != "" and $productInfoImage != "" and $productZoomImage != "")
                    {
                        foreach ($productImage as $key => $value) {
                            if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $value) && $value != '') {
                                $image1[] =$path.'/'.$value;
                            }
                        }/*foreach ($productInfoImage as $key => $value) {
                             if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $value) && $value != '') {
                                $image1[] =$path.'/'.$value;
                            }
                        }foreach ($productZoomImage as $key => $value) {
                            $image3[] =$product_images.'/'.$value;
                        }*/
                    }
                        $product[$p]['productImage'] = $image1;
                        $product[$p]['productInfoImage'] = $image1;
                        $product[$p]['productZoomImage'] = $image1;
                  
                    $cart_count = 0;
                    if ($user_id != '') {
                        $cart_count = $this->get_cart_product_count($user_id, $pro->product_id);
                    }
                    $product[$p]['cartCount'] = $cart_count;
                    $p++;
                }
            }

            //print_r(getCurrency());exit;
            $currency_symbol = getCurrency($language_id);
            $currency_side = getCurrencyPosition()->currency_side;
            $result = array("status" => 1, "message" => trans("messages.Product list"), "productList" => $product, "currencySymbol" => $currency_symbol, "currencySide" => $currency_side);
        }
        return json_encode($result);
    }

    public function mproduct_details(Request $data)
    {
        $post_data = $data->all();
        $user_id = isset($post_data['user_id']) ? $post_data['user_id'] : "";
        $productUrl = $post_data['productUrl'];
        $outletId = $post_data['outletId'];
        $language_id = 1;
        // if ($post_data['language'] == "ar") {
        //     $language_id = 2;
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        $result = array("response" => array("httpCode" => 400, "status" => false, "data" => $data));
        $condtion = " products.active_status = 1";
        $pquery = '"products_infos"."lang_id" = (case when (select count(products_infos.lang_id) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and products.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $wquery = '"weight_classes_infos"."lang_id" = (case when (select count(weight_classes_infos.lang_id) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . $language_id . ' and products.weight_class_id = weight_classes_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $products = Products::join('products_infos', 'products.id', '=', 'products_infos.id')
            ->join('weight_classes_infos', 'weight_classes_infos.id', '=', 'products.weight_class_id')
            ->join('outlets', 'outlets.id', '=', 'products.outlet_id')
            ->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
            ->select('products.id as product_id', 'products.product_url', 'products.product_info_image', 'products.product_zoom_image', 'products.product_image', 'products.weight', 'products.original_price', 'products.discount_price', 'products.vendor_id', 'products.outlet_id', 'products_infos.description', 'products_infos.product_name', 'products.category_id', 'products.barcode','products.more_description', 'weight_classes_infos.unit', 'weight_classes_infos.title', 'outlet_infos.outlet_name', 'outlets.url_index as outlet_url_index', 'products.item_limit as item_limit')
            ->whereRaw($pquery)
            ->whereRaw($wquery)
            ->where('outlets.id', '=', $outletId)
            ->where('products.product_url', '=', $productUrl)
            ->first();
        //print_r($products);exit;

        if (count($products) > 0) {
            $products->product_cart_count = 0;
            if (!empty($user_id)) {
                $products->product_cart_count = $this->get_cart_product_count($user_id, $products->product_id);
            }

            $productDetail = new \stdClass();
            $productDetail->productId = $products->product_id;
            $productDetail->productName = $products->product_name;
            $productDetail->productUrl = $products->product_url;
            $productDetail->weight = $products->weight;
            $productDetail->originalPrice = $products->original_price;
            $productDetail->discountPrice = $products->discount_price;
            $productDetail->categoryId = $products->category_id;
            //  $productDetail-> offer=$products -> product_id;
            $productDetail->unit = $products->unit;
            $productDetail->description = $products->description;
            $productDetail->title = $products->product_name;
            $productDetail->itemQuantity = $products->product_id;
            $productDetail->outletId = $products->outlet_id;
            $productDetail->outletName = $products->outlet_name;
            $productDetail->item_limit = isset($products->item_limit)?$products->item_limit:0;

            $productDetail->averageRating = 0;
            // $productDetail-> itemFeatures=array();
            //print_r(json_decode($products->more_description));exit;
            $des = json_decode($products->more_description);
            $data = array();
            if (count($des) != 0) {
                foreach ($des as $key => $value) {
                   // $featureName = $value->featureName;
                  //  print_r($value->data);exit;
                    $data[$key]['featureName'] = $value->featureName;
                    foreach ($value->data as $keys => $val) {
                        $data[$key]['data'][$keys]['itemName'] = key($val);
                        $data[$key]['data'][$keys]['itemDescription'] = current($val);
                    }
                }
            }
          //  print_r($data);exit();
            /*$data = '[
                {
                    "featureName": "Highlights",
                    "data": [
                        {
                            "itemName": "Key Features",
                            "itemDescription": "Pulses provide important amounts of vitamins and mineral. Some of the key minerals in pulses include: iron, potassium, magnesium and zinc. Pulses are also particularly abundant in B vitamins; including folate, thiamin and niacin"
                        },
                        {
                            "itemName": "Colour",
                            "itemDescription": "Yellow"
                        },
                        {
                            "itemName": "Disclaimer",
                            "itemDescription": "The information on this website of Pulse Business Solutions BV has been compiled with great care. In the information that it gives to you, Pulse endeavours to be transparent and accurate"
                        }
                    ]
                },
                {
                    "featureName": "Info",
                    "data": [
                        {
                            "itemName": "Shelf Life",
                            "itemDescription": "6 months"
                        }
                    ]
                }
            ]';
            */
            //$productDetail->itemFeatures = json_decode($data, true);
            $productDetail->itemFeatures = $data;

            $productDetail->itemQuantity = array();

            $productDetail->itemQuantity[0]["subId"] = "1";
            $productDetail->itemQuantity[0]["itemUnit"] = $products->unit;
            $productDetail->itemQuantity[0]["itemNewPrice"] = floatval($products->discount_price);
            $productDetail->itemQuantity[0]["itemOldPrice"] = floatval($products->original_price);

            $productDetail->itemQuantity[0]['itemWeight'] = $products->weight;
            $productDetail->itemImageUrls = array();
            // if (file_exists(base_path() . '/public/assets/admin/base/images/products/' . $st->product_image) && $st->product_image != '') {
            // $productDetail->productImage = url('/assets/admin/base/images/products/list/' . $products->product_image);
            // $productDetail->productZoomImage = url('/assets/admin/base/images/products/list/' . $products->product_zoom_image);
            // $productDetail->productInfoImage = url('/assets/admin/base/images/products/list/' . $products->product_info_image);
            // $productDetail->itemImageUrls[0] = url('/assets/admin/base/images/products/list/' . $products->product_image);
            // $productDetail->itemImageUrls[1] = url('/assets/admin/base/images/products/list/' . $products->product_zoom_image);

          
            $productDetail->productImage = url('/assets/admin/base/images/products/list/' . $products->product_image);
            $productDetail->productZoomImage = url('/assets/admin/base/images/products/list/' . $products->product_zoom_image);
            $productDetail->productInfoImage = url('/assets/admin/base/images/products/list/' . $products->product_info_image);
            $productDetail->itemImageUrls[0] = url('/assets/admin/base/images/products/list/' . $products->product_image);
            $productDetail->itemImageUrls[1] = url('/assets/admin/base/images/products/list/' . $products->product_image);

            $result = array("status" => 200, "message" => "Success", 'productDetail' => $productDetail);
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function products(Request $data)
    {
        $rules = [

            'productName' => ['required'],
            'language' => ['required'],
            'outletId' => ['required'],
            'vendorId' => ['required'],

        ];

        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            // $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            $store_key = $post_data['productName'];

            //print_r($store_key);exit;
            $language_id = $post_data['language'];
            $outletId = $post_data['outletId'];
            $vendorId = $post_data['vendorId'];
            $keyword = isset($post_data['keyword']) ? $post_data['keyword'] : '';
            $data = array();
            $result = array("response" => array("httpCode" => 400, "status" => false, "data" => $data));
            $condtion = " products.active_status = 1";
            if ($store_key) {
                $condtion .= "and products_infos.product_name ILIKE '%" . $store_key . "%'";
            }

            //print_r($category_url);exit;

            $products = Products::join('products_infos', 'products.id', '=', 'products_infos.id')
                ->join('categories', 'categories.id', '=', 'products.category_id')
                ->join('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
                ->join('weight_classes', 'weight_classes.id', '=', 'products.weight_class_id')
                ->join('weight_classes_infos', 'weight_classes_infos.id', '=', 'weight_classes.id')
                ->join('outlets', 'outlets.id', '=', 'products.outlet_id')
                ->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')

            //->select('products.id as productId', 'products.product_url as productUrl','products.product_image','products.product_info_image','products.product_zoom_image','products.vendor_category_id as vendorCategoryId', 'products.product_image', 'products.weight', 'products.original_price as originalPrice', 'products.discount_price as discountPrice', 'products.vendor_id as vendorId', 'products.outlet_id as outletId', 'products_infos.description', 'products_infos.product_name as productName', 'categories_infos.category_name as categoryName', 'categories.id', 'weight_classes_infos.unit', 'weight_classes_infos.title', 'products.category_id as categoryId', 'categories.url_key as catUrl')

                ->select(
                    'product_url as productUrl',
                    'product_name as productName',
                    'vendor_category_id as categoryId',
                    'products.product_image',
                    'products.product_info_image',
                    'products.product_zoom_image',
                    'original_price as originalPrice',
                    'discount_price as discountPrice',
                    'products_infos.description',
                    'products.weight',
                    'categories_infos.category_name as categoryName',
                    'weight_classes_infos.unit',
                    'weight_classes_infos.title',
                    'products.category_id as categoryId',
                    'outlets.id as outletId',
                    'outlets.vendor_id as vendorId',
                    'outlet_infos.outlet_name as outletName',
                    'categories.url_key as urlKey',
                    'categories.url_key as catUrl'
                )

            //->select('product_url as productUrl','product_name as productName','vendor_category_id as vendorCategoryId','original_price as originalPrice','discount_price as discountPrice','products_infos.description','products.weight','categories_infos.category_name as categoryName', 'categories.id as categoryId', 'weight_classes_infos.unit', 'weight_classes_infos.title',  'categories.url_key as urlKey', 'categories.url_key as catUrl')

                ->distinct()

                ->where('outlets.id', '=', $outletId)
                ->where('outlets.vendor_id', '=', $vendorId)
                ->where('products_infos.product_name', 'ILIKE', "%{$store_key}%")
                ->get();

            /*
                      $products = $products->whereRaw($condtion)
                                ->where('products.approval_status', '=', 1)
                                ->orderBy('products.category_id', 'asc' )
                        -> where('products_infos.product_name', 'ILIKE', "%{$condtion}%")

                             ->get();

                $array= $products->unique();

                  $resultArray=array();

                for($i=$resultArray;$i<$products;$i++){

                $resultArray=$array;
            */
            $currency_symbol = getCurrency($language_id);
            $currency_side = getCurrencyPosition()->currency_side;
            $p = 0;
            foreach ($products as $pro) {
                $item_quantity = array();
                $products[$p]["currencySymbol"] = $currency_symbol;
                $products[$p]["currencySide"] = $currency_side;

                $item_quantity[0]["subId"] = "1";
                $item_quantity[0]["itemUnit"] = $pro->unit;
                $item_quantity[0]["itemNewPrice"] = floatval($pro->discountPrice);
                $item_quantity[0]["itemOldPrice"] = floatval($pro->originalPrice);
                $item_quantity[0]['itemWeight'] = $pro->weight;

                $products[$p]['itemQuantity'] = $item_quantity;
                // echo(base_path() . '/public/assets/admin/base/images/products/list/' . $pro->product_image);exit;
                $products[$p]['averageRating'] = ($pro->average_rating == null) ? 0 : $pro->average_rating;
                $product_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $pro->product_image) && $pro->product_image != '') {
                    $product_image = url('/assets/admin/base/images/products/list/' . $pro->product_image);
                }
                $products[$p]['productImage'] = $product_image;

                $product_info_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                if (file_exists(base_path() . '/public/assets/admin/base/images/products/detail/' . $pro->product_info_image) && $pro->product_info_image != '') {
                    $product_info_image = url('/assets/admin/base/images/products/detail/' . $pro->product_info_image);
                }
                $products[$p]['productInfoImage'] = $product_info_image;

                $product_zoom_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                if (file_exists(base_path() . '/public/assets/admin/base/images/products/zoom/' . $pro->product_zoom_image) && $pro->product_zoom_image != '') {
                    $product_zoom_image = url('/assets/admin/base/images/products/zoom/' . $pro->product_zoom_image);
                }
                $products[$p]['productZoomImage'] = $product_zoom_image;

                /* $cart_count = 0;
                                        if ($user_id != '') {
                                            $cart_count = $this->get_cart_product_count($user_id, $pro->product_id);
                                        }
                */
                $p++;
            }

            $result = array("status" => 200, "message" => "Success", 'productList' => $products);

            // $result = array("response" => array("httpCode" => 200, "status" => true, 'data' =>$products ));
        }
        return json_encode($result);

        //}
    }

    //mob apis:

    public function mstore_list_mob(Request $data)
    {
        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $language = isset($post_data['language']) ? $post_data['language'] : "1";
        $category_ids = isset($post_data['category_ids']) ? $post_data['category_ids'] : "";
        $category_url = isset($post_data['category_url']) ? $post_data['category_url'] : "";
        $city = isset($post_data['city']) ? $post_data['city'] : "";
        $location = isset($post_data['location']) ? $post_data['location'] : "";
        $keyword = isset($post_data['keyword']) ? $post_data['keyword'] : "";
        $sortby = isset($post_data['sortby']) ? $post_data['sortby'] : "";
        $orderby = isset($post_data['orderby']) ? $post_data['orderby'] : "desc";
        $stores = array();
        $limit = !empty($post_data['countPerPage']) ? $post_data['countPerPage'] : 10;
        if (!empty($post_data['pageNumber'])) {
            $pn = $post_data['pageNumber'];
        } else {
            $pn = 1;
        }
        $offset = ($pn - 1) * $limit;
        $stores_list = Stores::stores_list($language, $category_ids, $category_url, $city, $location, $keyword, $sortby, $orderby, $limit, $offset);

        if (count($stores_list) > 0) {
            $s = 0;
            $vendors_id = '';
            foreach ($stores_list as $st) {
                if ($vendors_id != $st->vendors_id) {
                    $vendors_id = $st->vendors_id;
                    $stores[$s]['vendorId'] = $st->vendors_id;
                    $stores[$s]['vendorName'] = $st->vendor_name;
                    $stores[$s]['categoryIds'] = $st->category_ids;
                    $stores[$s]['address'] = $st->contact_address;
                    $stores[$s]['description'] = $st->vendor_description;

                    $featured_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/list/' . $st->featured_image) && $st->featured_image != '') {
                        $featured_image = url('/assets/admin/base/images/vendors/list/' . $st->featured_image . '?' . time());
                    }

                    $stores[$s]['featuredImage'] = $featured_image;
                    $stores[$s]['deliveryTime'] = $st->vendors_delivery_time . " Mins";
                    // $stores[$s]['deliveryChargesFixed'] = $st->delivery_charges_fixed;
                    // $stores[$s]['deliveryCostVariation'] = $st->delivery_cost_variation;
                    // $stores[$s]['minimumOrderAmount'] = $st->minimum_order_amount;
                    $stores[$s]['vendorsRating'] = ($st->vendors_average_rating == null) ? 0 : (int)$st->vendors_average_rating;
                    $category_ids = explode(',', $st->category_ids);
                    $category_name = '';
                    $stores[$s]['offer'] = "15% off on orders above ₹ 250*";
                    if ($s / 3 == 0 && $s != 0) {
                        $stores[$s]['comboOffer'] = "";
                    } else {
                        $stores[$s]['comboOffer'] = "₹ " . (($s + 1) * 100) . " for two";
                    }

                    if (count($category_ids) > 0) {
                        foreach ($category_ids as $cate) {
                            $get_category_name = getCategoryListsById($cate);

                            if (count($get_category_name) > 0) {
                                //echo "in";exit;
                                $category_name .= $get_category_name->category_name . ', ';
                            }
                        }
                    }
                    $stores[$s]['categoryIds'] = rtrim($category_name, ', ');
                    $logo_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/list/' . $st->logo_image) && $st->logo_image != '') {
                        $logo_image = url('/assets/admin/base/images/vendors/list/' . $st->logo_image . '?' . time());
                    }
                    $stores[$s]['logoImage'] = $logo_image;
                    // $stores[$s]['logoImage'] = $logo_image;

                    //$outlet_list = Stores::get_all_outlet_id_by_store_copy($st->vendors_id, $latitude, $longitude);

                    $outlet_list = Stores::get_all_outlet_id_by_store($st->vendors_id);
                    $outlet = array();
                    if (count($outlet_list) > 0) {
                        $n = 0;
                        foreach ($outlet_list as $out) {
                            $outlet[$n]['outletId'] = $out->outlets_id;
                            $outlet[$n]['outletName'] = $out->outletName;

                            $outlet[$n]['address'] = $out->contactAddress;
                            $outlet[$n]['description'] = $st->vendor_description;
                            $outlet[$n]['deliveryTime'] = $out->deliveryTime;
                            // $stores[$s]['deliveryChargesFixed'] = $st->delivery_charges_fixed;
                            // $stores[$s]['deliveryCostVariation'] = $st->delivery_cost_variation;
                            // $stores[$s]['minimumOrderAmount'] = $st->minimum_order_amount;
                            $outlet[$n]['vendorsRating'] = ($out->averageRating == null) ? 0 : (int)$out->averageRating;

                            $n++;
                        }
                    }

                    $stores[$s]['nearestList'] = $outlet;

                    // $outlet_count = Stores::get_outlet_count($st->vendors_id, $city, $location);

                    //                   if (count($outlet_count) > 0) {
                    //                       $stores[$s]['outletsCount'] = (int) $outlet_count->outlets_count;
                    //                       if ($outlet_count->outlets_count == 1) {
                    //                           $get_outlet_id = Stores::get_outlet_id_by_store($st->vendors_id);
                    //                           $stores[$s]['outletsId'] = (int) $get_outlet_id->outlets_id;
                    //                       } else {
                    //                           $stores[$s]['outletsId'] = '';
                    //                       }
                    //                   } else {
                    //                       $stores[$s]['outletsCount'] = 0;
                    //                       $stores[$s]['outletsId'] = '';
                    //                   }
                    $s++;
                }
            }
        }
        $result = array("status" => 1, "message" => trans("messages.Store list"), "vendorList" => $stores);
        return json_encode($result);
    }

    public function getNearestOutlets(Request $data)
    {
        $post_data = $data->all();
        //  $language = isset($post_data['language']) ? $post_data['language'] : "1";
        $language = "1";
        $category_ids = isset($post_data['category_ids']) ? $post_data['category_ids'] : "";
        $category_url = isset($post_data['category_url']) ? $post_data['category_url'] : "";
        $city = isset($post_data['city']) ? $post_data['city'] : "";
        $location = isset($post_data['location']) ? $post_data['location'] : "";
        $keyword = isset($post_data['keyword']) ? $post_data['keyword'] : "";
        $sortby = isset($post_data['sortby']) ? $post_data['sortby'] : "";
        $orderby = isset($post_data['orderby']) ? $post_data['orderby'] : "desc";
        $stores = array();
        $limit = !empty($post_data['countPerPage']) ? $post_data['countPerPage'] : 10;
        if (!empty($post_data['pageNumber'])) {
            $pn = $post_data['pageNumber'];
        } else {
            $pn = 1;
        }
        $offset = ($pn - 1) * $limit;

        // $latitude = isset($post_data['latitude']) ? $post_data['latitude'] : "11.0238";
        // $longitude = isset($post_data['longitude']) ? $post_data['longitude'] : "77.0197";

        $latitude = $post_data['latitude'];
        $longitude = $post_data['longitude'];
        // $stores_list = Stores::stores_list($language, $category_ids, $category_url, $city, $location, $keyword, $sortby, $orderby, $limit, $offset);
        // $stores_list = Stores::feature_stores_list($language, $category_ids, $category_url, $city, $location, $keyword, $sortby, $orderby);
        $stores_list = Stores::nearest_outlets_list($language, $category_ids, $category_url, $city, $location, $latitude, $longitude, $keyword, $sortby, $orderby);
        //echo '<pre>';print_r($stores_list);exit;
        return $stores_list;
    }

    public function getNearestVendors(Request $data)
    {
        $post_data = $data->all();
        //  $language = isset($post_data['language']) ? $post_data['language'] : "1";
        $language = "1";
        $category_ids = isset($post_data['category_ids']) ? $post_data['category_ids'] : "";
        $category_url = isset($post_data['category_url']) ? $post_data['category_url'] : "";
        $city = isset($post_data['city']) ? $post_data['city'] : "";
        $location = isset($post_data['location']) ? $post_data['location'] : "";
        $keyword = isset($post_data['keyword']) ? $post_data['keyword'] : "";
        $sortby = isset($post_data['sortby']) ? $post_data['sortby'] : "";
        $orderby = isset($post_data['orderby']) ? $post_data['orderby'] : "desc";
        $stores = array();
        $limit = !empty($post_data['countPerPage']) ? $post_data['countPerPage'] : 10;
        if (!empty($post_data['pageNumber'])) {
            $pn = $post_data['pageNumber'];
        } else {
            $pn = 1;
        }
        $offset = ($pn - 1) * $limit;

        // $latitude = isset($post_data['latitude']) ? $post_data['latitude'] : "11.0238";
        // $longitude = isset($post_data['longitude']) ? $post_data['longitude'] : "77.0197";

        $latitude = $post_data['latitude'];
        $longitude = $post_data['longitude'];
        // $stores_list = Stores::stores_list($language, $category_ids, $category_url, $city, $location, $keyword, $sortby, $orderby, $limit, $offset);
        // $stores_list = Stores::feature_stores_list($language, $category_ids, $category_url, $city, $location, $keyword, $sortby, $orderby);
        $data = Stores::nearest_outlets_list($language, $category_ids, $category_url, $city, $location, $latitude, $longitude, $keyword, $sortby, $orderby);
        //print_r($data);exit;

        $list = $stores = array();
        foreach ($data as $key => $value) {
            if (!in_array($value->vendors_id, $list)) {
                array_push($list, $value->vendors_id);
                $stores[$key]['vendorId'] = $value->vendors_id;
                $stores[$key]['vendorName'] = $value->vendor_name;
                $stores[$key]['categoryIds'] = $value->vendor_name;
                $stores[$key]['address'] = $value->vendor_name;
                $stores[$key]['description'] = $value->vendor_name;
                $stores[$key]['featuredImage'] = $value->vendor_name;
                $stores[$key]['deliveryTime'] = $value->vendor_name;
                $stores[$key]['vendorsRating'] = $value->vendor_name;
                $stores[$key]['offer'] = $value->vendor_name;
                $stores[$key]['comboOffer'] = $value->vendor_name;
                $stores[$key]['logoImage'] = $value->vendor_name;
                $stores[$key]['nearestList'][$key]['outletId'] = $value->outlets_id;
                $stores[$key]['nearestList'][$key]['outletName'] = $value->outlet_name;
                $stores[$key]['nearestList'][$key]['address'] = $value->contact_address;
                $stores[$key]['nearestList'][$key]['description'] = $value->vendor_description;
                $stores[$key]['nearestList'][$key]['deliveryTime'] = $value->outlets_delivery_time;
                $stores[$key]['nearestList'][$key]['vendorsRating'] = $value->vendors_average_rating;
            } else {
                foreach ($stores as $key => $val) {
                    $x = '';
                    if ($val['vendorId'] === $value->vendors_id) {
                        $x = $key;
                    }
                }
                $count = count($stores[$x]['nearestList']);
                $stores[$x]['nearestList'][$count]['outletId'] = $value->outlets_id;
                $stores[$x]['nearestList'][$count]['outletName'] = $value->outlet_name;
                $stores[$x]['nearestList'][$count]['address'] = $value->contact_address;
                $stores[$x]['nearestList'][$count]['description'] = $value->vendor_description;
                $stores[$x]['nearestList'][$count]['deliveryTime'] = $value->outlets_delivery_time;
                $stores[$x]['nearestList'][$count]['vendorsRating'] = $value->vendors_average_rating;
            }
        }

        $result = array("status" => 1, "message" => trans("messages.Store list"), "vendorList" => $stores);
        //print_r($result);exit;
        return json_encode($result);
        //return $stores_list;
    }

    //DashboadMob

    public function mdashboard_mob_copy(Request $data)
    {
        $post_data = $data->all();
        //  $language = isset($post_data['language']) ? $post_data['language'] : "1";
        $language = "1";
        $category_ids = isset($post_data['category_ids']) ? $post_data['category_ids'] : "";
        $category_url = isset($post_data['category_url']) ? $post_data['category_url'] : "";
        $city = isset($post_data['city']) ? $post_data['city'] : "";
        $location = isset($post_data['location']) ? $post_data['location'] : "";
        $keyword = isset($post_data['keyword']) ? $post_data['keyword'] : "";
        $sortby = isset($post_data['sortby']) ? $post_data['sortby'] : "";
        $orderby = isset($post_data['orderby']) ? $post_data['orderby'] : "desc";
        $stores = array();
        $limit = !empty($post_data['countPerPage']) ? $post_data['countPerPage'] : 10;
        if (!empty($post_data['pageNumber'])) {
            $pn = $post_data['pageNumber'];
        } else {
            $pn = 1;
        }
        $offset = ($pn - 1) * $limit;

        // $latitude = $post_data['latitude'];
        //$longitude = $post_data['longitude'];
        //print_r($post_data);exit;
        /*$latitude = isset($post_data['latitude']) ? $post_data['latitude'] : "11.0238";
        $longitude = isset($post_data['longitude']) ? $post_data['longitude'] : "77.0197";*/
        // $stores_list = Stores::stores_list($language, $category_ids, $category_url, $city, $location, $keyword, $sortby, $orderby, $limit, $offset);
        //$stores_list = Stores::feature_stores_list($language, $category_ids, $category_url, $city, $location, $keyword, $sortby, $orderby);

        //$stores_list = Stores::nearest_stores_list($language, $category_ids, $category_url, $city, $location, $latitude, $longitude, $keyword, $sortby, $orderby);

        $stores_list = Stores::feature_stores_list($language, $category_ids, $category_url, $city, $location, $keyword, $sortby, $orderby);


        //->where('banner_type', 2)
        $banners = DB::table('banner_settings')->select('*')->where('status', 1)->orderBy('default_banner', 'desc')->get();
        $banner_list = array();
        $featuredItem = array();

        $featureNameArray = array("Grocery", "Fish & Meat", "Personal", "Home & Kitchen", "Baby", "Bevarages", "Fruits", "Snacks");

        $featureImageArray = array("https://broz.app/assets/admin/base/images/featureItem/Grocery.png?2019-02-17 19:42:25"
            , "https://broz.app/assets/admin/base/images/featureItem/meat.png?2019-02-17 19:42:26",
            "https://broz.app/assets/admin/base/images/featureItem/personal.png?2019-02-17 19:42",
            "https://broz.app/assets/admin/base/images/featureItem/kitchen.png?2019-02-17 19:42:25",
            "https://broz.app/assets/admin/base/images/featureItem/baby.png?2019-02-17 19:42:25",
            "https://broz.app/assets/admin/base/images/featureItem/beverges.png?2019-02-17 19:42:25",
            "https://broz.app/assets/admin/base/images/featureItem/fruits.png?2019-02-17 19:42:25",

            "https://broz.app/assets/admin/base/images/featureItem/snacks.png?2019-02-17 19:42:25",
        );

        //Naga -> Waiting for php dev

        $data1 = '[{
			    "id": 49,
			    "description": "Grocery",
			    "categoryLevel": 2,
			    "categoryName": "Grocery & Stables",
			    "categoryId": 514,
			    "logoImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
			    "bannerImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
			    "itemOffer": "",
			    "childCategories": [{
			        "id": 471,
			        "description": "Pulses",
			        "categoryName": "Pulses",
			        "categoryId": 471
			    }]
			}]';
        $data2 = '[{
		    "id": 49,
		    "description": "Fish & Meat",
		    "categoryLevel": 2,
		    "categoryName": "Fish & Meat",
		    "categoryId": 49,
		    "logoImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
		    "bannerImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
		    "itemOffer": "",
		    "childCategories": [{
		        "id": 507,
		        "description": "Fish",
		        "categoryName": "Fish",
		        "categoryId": 507
		    }]
		}]';
        $data3 = '[{
			    "id": 49,
			    "description": "Personal",
			    "categoryLevel": 2,
			    "categoryName": "Personal",
			    "categoryId": 49,
			    "logoImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
			    "bannerImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
			    "itemOffer": "",
			    "childCategories": [{
			        "id": 490,
			        "description": "Body Care",
			        "categoryName": "Body Care",
			        "categoryId": 490
			    }]
			}]';
        $data4 = '[{
		    "id": 49,
		    "description": "Home & Kitchen",
		    "categoryLevel": 2,
		    "categoryName": "Home & Kitchen",
		    "categoryId": 49,
		    "logoImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
		    "bannerImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
		    "itemOffer": "",
		    "childCategories": [{
		        "id": 500,
		        "description": "DishWasher",
		        "categoryName": "DishWasher",
		        "categoryId": 500
		    }]
		}]';
        $data5 = '[{
		    "id": 49,
		    "description": "Babys & Kids",
		    "categoryLevel": 2,
		    "categoryName": "Babys & Kids",
		    "categoryId": 49,
		    "logoImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
		    "bannerImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
		    "itemOffer": "",
		    "childCategories": [{
		        "id": 497,
		        "description": "Baby Shop",
		        "categoryName": "Baby Shop",
		        "categoryId": 497
		    }]
		}]';
        $data6 = '[{
		    "id": 49,
		    "description": "Beverages",
		    "categoryLevel": 2,
		    "categoryName": "Beverages & Soft Drinks",
		    "categoryId": 49,
		    "logoImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
		    "bannerImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
		    "itemOffer": "",
		    "childCategories": [{
		        "id": 502,
		        "description": "Cool Drinks",
		        "categoryName": "Cool Drinks",
		        "categoryId": 502
		    }]
		}]';
        $data7 = '[{
		    "id": 49,
		    "description": "Fruits",
		    "categoryLevel": 2,
		    "categoryName": "Fruits",
		    "categoryId": 49,
		    "logoImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
		    "bannerImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
		    "itemOffer": "",
		    "childCategories": [{
		        "id": 484,
		        "description": "Dry Fruits",
		        "categoryName": "Dry Fruits",
		        "categoryId": 484
		    }]
		}]';
        $data8 = '[{
		    "id": 49,
		    "description": "Backery & Snacks",
		    "categoryLevel": 2,
		    "categoryName": "Backery & Snacks",
		    "categoryId": 49,
		    "logoImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
		    "bannerImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
		    "itemOffer": "",
		    "childCategories": [{
		        "id": 493,
		        "description": "Breads",
		        "categoryName": "Breads",
		        "categoryId": 493
		    }]
		}]';

        $fdataArray = array($data1, $data2, $data3, $data4, $data5, $data6, $data7, $data8);

        for ($x = 0; $x <= 7; $x++) {
            $key = "name" . $x;
            $featuredItem[$x]['name'] = $featureNameArray[$x];
            $featuredItem[$x]['logo'] = $featureImageArray[$x];

            $data = $fdataArray[$x];
            $featuredItem[$x]['vendorId'] = 236;
            $featuredItem[$x]['outletId'] = 113;
            $featuredItem[$x]['vendorName'] = "Broz";

            $featuredItem[$x]['subCategories'] = json_decode($data);
        }

        if (count($banners) > 0) {
            foreach ($banners as $key => $items) {
                // $banners[$key]->bannerImageUrl = url('/assets/admin/base/images/' . $items->banner_image.'?'.time());
                $banner_list[$key]['bannerSettingId'] = $items->banner_setting_id;
                $banner_list[$key]['bannerTitle'] = $items->banner_title;
                $banner_list[$key]['bannerSubtitle'] = $items->banner_subtitle;
                $banner_list[$key]['bannerImage'] = $items->banner_image;
                $banner_list[$key]['bannerLink'] = $items->banner_link;
                $banner_list[$key]['defaultBanner'] = $items->default_banner;
                $banner_list[$key]['status'] = $items->status;
                $banner_list[$key]['updatedDate'] = $items->updated_date;
                $banner_list[$key]['createdDate'] = $items->created_date;
                $banner_list[$key]['createdDate'] = $items->created_date;
                $banner_list[$key]['bannerType'] = $items->banner_type;
                $banner_list[$key]['languageType'] = $items->language_type;
                $banner_list[$key]['bannerImageUrl'] = url('/assets/admin/base/images/banner/' . $items->banner_image . '?' . $items->updated_date);
            }
        }

        if (count($stores_list) > 0) {
            $s = 0;
            $vendors_id = '';

            foreach ($stores_list as $st) {
                //print_r($st);exit;

                if ($vendors_id != $st->vendors_id) {
                    $vendors_id = $st->vendors_id;
                    $stores[$s]['vendorId'] = $st->vendors_id;

                    $stores[$s]['vendorName'] = trim($st->vendor_name);
                    $stores[$s]['categoryIds'] = $st->category_ids;
                    $stores[$s]['address'] = $st->contact_address;
                    $stores[$s]['description'] = trim($st->vendor_description);

                    $featured_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/list/' . $st->featured_image) && $st->featured_image != '') {
                        $featured_image = url('/assets/admin/base/images/vendors/list/' . $st->featured_image . '?' . time());
                    }

                    $stores[$s]['featuredImage'] = $featured_image;

                    $stores[$s]['deliveryTime'] = $st->vendors_delivery_time . " Mins";
                    // $stores[$s]['deliveryChargesFixed'] = $st->delivery_charges_fixed;
                    // $stores[$s]['deliveryCostVariation'] = $st->delivery_cost_variation;
                    // $stores[$s]['minimumOrderAmount'] = $st->minimum_order_amount;
                    $stores[$s]['vendorsRating'] = ($st->vendors_average_rating == null) ? 0 : $st->vendors_average_rating;
                    $category_ids = explode(',', $st->category_ids);
                    $category_name = '';
                    $stores[$s]['offer'] = "50% off on orders above ₹ 250*";
                    if ($s / 3 == 0 && $s != 0) {
                        $stores[$s]['comboOffer'] = "";
                    } else {
                        $stores[$s]['comboOffer'] = "₹ " . (($s + 1) * 100) . " for two";
                    }

                    if (count($category_ids) > 0) {
                        foreach ($category_ids as $cate) {
                            $get_category_name = getCategoryListsById($cate);

                            if (count($get_category_name) > 0) {
                                //echo "in";exit;
                                $category_name .= $get_category_name->category_name . ', ';
                            }
                        }
                    }
                    $stores[$s]['categoryIds'] = rtrim($category_name, ', ');
                    $logo_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $st->logo_image) && $st->logo_image != '') {
                        $logo_image = url('/assets/admin/base/images/vendors/logos/' . $st->logo_image . '?' . time());
                    }
                    $stores[$s]['logoImage'] = $logo_image;
                    // $stores[$s]['logoImage'] = $logo_image;

                    //$outlet_list = Stores::get_all_outlet_id_by_store_copy($st->vendors_id, $latitude, $longitude);

                    $outlet_list = Stores::get_all_outlet_id_by_store($st->vendors_id);
                    $outlet = array();
                    if (count($outlet_list) > 0) {
                        $n = 0;
                        foreach ($outlet_list as $out) {
                            $outlet[$n]['outletId'] = $out->outlets_id;
                            $outlet[$n]['outletName'] = $out->outletName;
                            $outlet[$n]['address'] = $out->contactAddress;
                            //print_r($out->contactAddress);exit;

                            $outlet[$n]['description'] = $st->vendor_description;
                            $outlet[$n]['deliveryTime'] = $out->deliveryTime;
                            // $stores[$s]['deliveryChargesFixed'] = $st->delivery_charges_fixed;
                            // $stores[$s]['deliveryCostVariation'] = $st->delivery_cost_variation;
                            // $stores[$s]['minimumOrderAmount'] = $st->minimum_order_amount;
                            $outlet[$n]['vendorsRating'] = ($out->averageRating == null) ? 0 : $out->averageRating;

                            $n++;
                        }
                    }

                    $stores[$s]['nearestList'] = $outlet;

                    // @Parcelize
                    //     data class CategoryData(val id: Int
                    //                             , val description: String
                    //                             , val itemOffer: String? = "Up to 50% Offer"
                    //                             , val categoryLevel: String?
                    //                             , val categoryName: String
                    //                             , val logoImage: String
                    //                             , val bannerImage: String
                    //                             , val categoryId: String
                    //                             , val childCategories: ArrayList<ChildCategoryData>)

                    // val intent = Intent(mContext, SubCategoryActivity::class.java)
                    //                intent.putExtra("selectedItem", item)
                    //                intent.putExtra("name",smName)
                    //                intent.putExtra("outletId",outletId)
                    //                intent.putExtra("vendorId",vendorId)
                    //                mContext.startActivity(intent)

                    // $trendingProduct

                    // $outlet_count = Stores::get_outlet_count($st->vendors_id, $city, $location);

                    //                   if (count($outlet_count) > 0) {
                    //                       $stores[$s]['outletsCount'] = (int) $outlet_count->outlets_count;
                    //                       if ($outlet_count->outlets_count == 1) {
                    //                           $get_outlet_id = Stores::get_outlet_id_by_store($st->vendors_id);
                    //                           $stores[$s]['outletsId'] = (int) $get_outlet_id->outlets_id;
                    //                       } else {
                    //                           $stores[$s]['outletsId'] = '';
                    //                       }
                    //                   } else {
                    //                       $stores[$s]['outletsCount'] = 0;
                    //                       $stores[$s]['outletsId'] = '';
                    //                   }
                    $s++;
                }
            }
        }
        $result = array("status" => 1, "message" => trans("messages.Store list"), "vendorList" => $stores, "bannerList" => $banner_list, "featuredItem" => $featuredItem);
        return json_encode($result);
    }

  
    /*public function mproducts(Request $data)
    {
        $rules = [

            'productName' => ['required'],
            'language' => ['required'],
            'outletId' => ['required'],
            'vendorId' => ['required'],
            'pageSize' => ['required'],
            'skipSize' => ['required'],

        ];

        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            // $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result =  array("status" => 0, "Error" => trans("messages.Error List"), "message" => $errors);
        } else {
            $store_key = $post_data['productName'];
            $language_id = $post_data['language'];
            $outletId = $post_data['outletId'];
            $vendorId = $post_data['vendorId'];
            $pageSize = $post_data['pageSize'];
            $skipSize = $post_data['skipSize'];
            $keyword = isset($post_data['keyword']) ? $post_data['keyword'] : '';
            $data = array();
            $result = array("status" => 2, "data" => $data);
            $condtion = " products.active_status = 1";
            if ($store_key) {
                $condtion .= "and products_infos.product_name ILIKE '%" . $store_key . "%'";
            }

            //print_r($category_url);exit;

            $products = Products::join('products_infos', 'products.id', '=', 'products_infos.id')
                ->join('categories', 'categories.id', '=', 'products.category_id')
                ->join('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
                ->join('weight_classes', 'weight_classes.id', '=', 'products.weight_class_id')
                ->join('weight_classes_infos', 'weight_classes_infos.id', '=', 'weight_classes.id')
                ->join('outlets', 'outlets.id', '=', 'products.outlet_id')
                ->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')

            //->select('products.id as productId', 'products.product_url as productUrl','products.product_image','products.product_info_image','products.product_zoom_image','products.vendor_category_id as vendorCategoryId', 'products.product_image', 'products.weight', 'products.original_price as originalPrice', 'products.discount_price as discountPrice', 'products.vendor_id as vendorId', 'products.outlet_id as outletId', 'products_infos.description', 'products_infos.product_name as productName', 'categories_infos.category_name as categoryName', 'categories.id', 'weight_classes_infos.unit', 'weight_classes_infos.title', 'products.category_id as categoryId', 'categories.url_key as catUrl')

                ->select(
                     'products.id as productId',
                    'product_url as productUrl',
                    'product_name as productName',
                    'vendor_category_id as categoryId',
                    'products.product_image',
                    'products.product_info_image',
                    'products.product_zoom_image',
                    'original_price as originalPrice',
                    'discount_price as discountPrice',
                    'products_infos.description',
                    'products.weight',
                    'categories_infos.category_name as categoryName',
                    'weight_classes_infos.unit',
                    'weight_classes_infos.title',
                    'products.category_id as categoryId',
                    'outlets.id as outletId',
                    'outlets.vendor_id as vendorId',
                    'outlet_infos.outlet_name as outletName',
                    'categories.url_key as urlKey',
                    'categories.url_key as catUrl',
                    'products.item_limit'

                )

            //->select('product_url as productUrl','product_name as productName','vendor_category_id as vendorCategoryId','original_price as originalPrice','discount_price as discountPrice','products_infos.description','products.weight','categories_infos.category_name as categoryName', 'categories.id as categoryId', 'weight_classes_infos.unit', 'weight_classes_infos.title',  'categories.url_key as urlKey', 'categories.url_key as catUrl')

                ->distinct()

                ->where('outlets.id', '=', $outletId)
                ->where('outlets.vendor_id', '=', $vendorId)
                ->where('products_infos.product_name', 'ILIKE', "%{$store_key}%")
                ->limit($pageSize)
                ->skip($skipSize)
                ->get();

            /*
                      $products = $products->whereRaw($condtion)
                                ->where('products.approval_status', '=', 1)
                                ->orderBy('products.category_id', 'asc' )
                        -> where('products_infos.product_name', 'ILIKE', "%{$condtion}%")

                             ->get();

                $array= $products->unique();

                  $resultArray=array();

                for($i=$resultArray;$i<$products;$i++){

                $resultArray=$array;
            /
            $currency_symbol = getCurrency($language_id);
            $currency_side = getCurrencyPosition()->currency_side;
            $p = 0;
            foreach ($products as $pro) {
                $item_quantity = array();
                $products[$p]["currencySymbol"] = $currency_symbol;
                $products[$p]["currencySide"] = $currency_side;

                $item_quantity[0]["subId"] = "1";
                $item_quantity[0]["itemUnit"] = $pro->unit;
                $item_quantity[0]["itemNewPrice"] = floatval($pro->discountPrice);
                $item_quantity[0]["itemOldPrice"] = floatval($pro->originalPrice);
                $item_quantity[0]['itemWeight'] = $pro->weight;

                $products[$p]['itemQuantity'] = $item_quantity;
                // echo(base_path() . '/public/assets/admin/base/images/products/list/' . $pro->product_image);exit;
                $products[$p]['averageRating'] = ($pro->average_rating == null) ? 0 : $pro->average_rating;
                $product_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $pro->product_image) && $pro->product_image != '') {
                    $product_image = url('/assets/admin/base/images/products/list/' . $pro->product_image);
                }
                $products[$p]['productImage'] = $product_image;

                $product_info_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                if (file_exists(base_path() . '/public/assets/admin/base/images/products/detail/' . $pro->product_info_image) && $pro->product_info_image != '') {
                    $product_info_image = url('/assets/admin/base/images/products/detail/' . $pro->product_info_image);
                }
                $products[$p]['productInfoImage'] = $product_info_image;

                $product_zoom_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                if (file_exists(base_path() . '/public/assets/admin/base/images/products/zoom/' . $pro->product_zoom_image) && $pro->product_zoom_image != '') {
                    $product_zoom_image = url('/assets/admin/base/images/products/zoom/' . $pro->product_zoom_image);
                }
                $products[$p]['productZoomImage'] = $product_zoom_image;
                $products[$p]['item_limit'] = isset($pro->item_limit)?$pro->item_limit:0;

                /* $cart_count = 0;
                                        if ($user_id != '') {
                                            $cart_count = $this->get_cart_product_count($user_id, $pro->product_id);
                                        }
                /
                $p++;
            }

            $result = array("status" => 1, "message" => "Success", 'productList' => $products);

            // $result = array("response" => array("httpCode" => 200, "status" => true, 'data' =>$products ));
        }
        return json_encode($result);

        //}
    }*/
      public function mproducts(Request $data)
    {
        $rules = [

            'productName' => ['required'],
            'language' => ['required'],
            'outletId' => ['required'],
            'vendorId' => ['required'],
            'pageSize' => ['required'],
            'skipSize' => ['required'],

        ];

        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            // $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result =  array("status" => 0, "Error" => trans("messages.Error List"), "message" => $errors);
        } else {
            $store_key = $post_data['productName'];
            $language_id = $post_data['language'];
            $outletId = $post_data['outletId'];
            $vendorId = $post_data['vendorId'];
            $pageSize = $post_data['pageSize'];
            $skipSize = $post_data['skipSize'];
            $keyword = isset($post_data['keyword']) ? $post_data['keyword'] : '';
            $data = array();
            $result = array("status" => 2, "data" => $data);
            $condtion = " admin_products.status = 1";
            if ($store_key) {
                $condtion .= "and admin_products.product_name ILIKE '%" . $store_key . "%'";
            }

            //print_r($category_url);exit;

            $products = Admin_products::join('outlet_products', 'admin_products.id', '=', 'outlet_products.product_id')
                ->join('categories', 'categories.id', '=', 'admin_products.sub_category_id')
                ->join('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
                ->join('weight_classes', 'weight_classes.id', '=', 'admin_products.weight_class_id')
                ->join('weight_classes_infos', 'weight_classes_infos.id', '=', 'weight_classes.id')
                ->join('outlets', 'outlets.id', '=', 'outlet_products.outlet_id')
                ->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')


                ->select(
                    'admin_products.id as productId',
                    'product_url as productUrl',
                    'product_name as productName',
                    'admin_products.image',
                    'admin_products.image as product_info_image',
                    'admin_products.image as product_zoom_image',
                    'original_price as originalPrice',
                    'discount_price as discountPrice',
                    'admin_products.description',
                    'admin_products.weight',
                    'categories_infos.category_name as categoryName',
                    'weight_classes_infos.unit',
                    'weight_classes_infos.title',
                    'admin_products.category_id as categoryId',
                    'outlets.id as outletId',
                    'outlets.vendor_id as vendorId',
                    'outlet_infos.outlet_name as outletName',
                    'categories.url_key as urlKey',
                    'categories.url_key as catUrl'/*,
                    'admin_products.item_limit'*/

                )

            

                ->distinct()

                ->where('outlet_products.outlet_id', '=', $outletId)
                ->where('outlet_products.vendor_id', '=', $vendorId)
                ->where('admin_products.product_name', 'ILIKE', "%{$store_key}%")
                ->limit($pageSize)
                ->skip($skipSize)
                ->get();

                //print_r($products);exit();
           
            $currency_symbol = getCurrency($language_id);
            $currency_side = getCurrencyPosition()->currency_side;

             $product = array();
            if (count($products) > 0) {
                $p = 0;
                foreach ($products as $pro) {
                    $product[$p]['productId'] = $pro->productId;
                    $product[$p]['productName'] = $pro->productName;
                    $product[$p]['productUrl'] = $pro->productUrl;
                    $product[$p]['weight'] = $pro->weight;
                    $product[$p]['originalPrice'] = floatval($pro->originalPrice);
                    $product[$p]['discountPrice'] = floatval($pro->discountPrice);
                    $product[$p]['categoryId'] = $pro->categoryId;
                    $product[$p]['unit'] = $pro->unit;
                    $product[$p]['description'] = $pro->description;
                    $product[$p]['title'] = $pro->title;
                    $product[$p]['vendorId'] = $post_data['vendorId'];
                    $product[$p]['outletId'] = $pro->outletId;
                    $product[$p]['outletName'] = $pro->outletName;

            
                    $product[$p]["currencySymbol"] = $currency_symbol;
                    $product[$p]["currencySide"] = $currency_side;


                    $item_quantity = array();
                    $item_quantity[0]["subId"] = "1";
                    $item_quantity[0]["itemUnit"] = $pro->unit;
                    $item_quantity[0]["itemNewPrice"] = floatval($pro->discountPrice);
                    $item_quantity[0]["itemOldPrice"] = floatval($pro->originalPrice);
                    $item_quantity[0]['itemWeight'] = $pro->weight;

                    $product[$p]['itemQuantity'] = $item_quantity;
                    $product[$p]['averageRating'] = ($pro->average_rating == null) ? 0 : $pro->average_rating;
                    $product[$p]['item_limit'] = isset($pro->item_limit)?$pro->item_limit:0;


                   /* $product_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $pro->product_image) && $pro->product_image != '') {
                        $product_image = url('/assets/admin/base/images/products/list/' . $pro->product_image);
                    }
                    $products[$p]['productImage'] = $product_image;

                    $product_info_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/products/detail/' . $pro->product_info_image) && $pro->product_info_image != '') {
                        $product_info_image = url('/assets/admin/base/images/products/detail/' . $pro->product_info_image);
                    }
                    $products[$p]['productInfoImage'] = $product_info_image;

                    $product_zoom_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/products/zoom/' . $pro->product_zoom_image) && $pro->product_zoom_image != '') {
                        $product_zoom_image = url('/assets/admin/base/images/products/zoom/' . $pro->product_zoom_image);
                    }
                    $products[$p]['productZoomImage'] = $product_zoom_image;*/



                    
                    $no_image = URL::asset('assets/front/'. Session::get("general")->theme . '/images/store_detial.png');
                   
                    $product_images = url('/assets/admin/base/images/products/admin_products/');

                    $productImage=json_decode($pro->image);
                    $productInfoImage=json_decode($pro->product_info_image);
                    $productZoomImage=json_decode($pro->product_zoom_image);
                    $image1 =$image2=$image3 =array();
                    //print_r($productImage);exit();

                    if($productImage != "" and $productInfoImage != "" and $productZoomImage != "")
                    {
                        foreach ($productImage as $key => $value) {
                            $image1[] =$product_images.'/'.$value;
                        }foreach ($productInfoImage as $key => $value) {
                            $image2[] =$product_images.'/'.$value;
                        }foreach ($productZoomImage as $key => $value) {
                            $image3[] =$product_images.'/'.$value;
                        }
                    }else{
                        $image1[]= $no_image;
                        $image2[]= $no_image;
                        $image3[]= $no_image;
                    }
                        $product[$p]['productImage'] = $image1;
                        $product[$p]['productInfoImage'] = $image2;
                        $product[$p]['productZoomImage'] = $image3;

                   
                    $p++;
                }
            }
            $result = array("status" => 1, "message" => "Success", 'productList' => $product);

            
        }
        return json_encode($result);

        
    }

    //dynamic view page api:

    public function dynamic(request $request)
    {
        $name = 'name';
        $value = 'value';

        if ($request) {
            $id = $request['id'];
            $post_data = $request->all();
            //print_r($post_data);exit;
            $rootArray = array();
            if (!empty($post_data['cat_val']) && $post_data['cat_val'] != 0) {
                $j = 1;
                $y = 0;
                for ($i = 0; $i < $post_data['cat_val']; $i++) {
                    $err = 0;

                    if ($post_data['featureName_' . $j][$y] != '') {
                        $featureName = $post_data['featureName_' . $j][$y];
                        $rootArray[$i]["featureName"] = $featureName;
                        $err = 1;
                    }
                    $arrayJson = array();
                    for ($x = 0; $x < count($post_data[$value . '_' . $j]); $x++) {
                        if ($post_data[$value . '_' . $j][$x] != '' && $post_data[$name . '_' . $j][$x] != '') {
                            $arrayJson[$x][$request[$name . '_' . $j][$x]] = $request[$value . '_' . $j][$x];
                            $rootArray[$i]["data"] = $arrayJson;
                            $err = 1;
                        }
                    }
                    $j++;
                }
                if ($err == 1) {
                    //print_r($rootArray);exit;
                    $res = json_encode($rootArray);
                    //print_r($res);exit();

                    $result = DB::table('products')
                        ->where('id', $id)
                        ->update(['more_description' => $res]);
                    echo " " . "data saved successfully";
                } else {
                    echo " " . "field missing";
                }
            } else {
                echo " " . "no record found";
            }
        }
    }

    public function getval(request $request)
    {
        $id = $request['id'];
        $data = DB::table('products')
            ->select('products.id', 'products.more_description')
            ->where('products.id', $id)
            ->get();
        $des = $data[0]->more_description;
        $data = json_decode($des);
        //print_r($data);exit;

        return $data;
    }
    /*public function dynamic(request $request) {

    $name = 'name';
    $value = 'value';

    if ($request) {

    $id = $request['id'];

    echo (json_decode($request));exit;
    // echo ("hai");exit;
    //print_r($request['featureName']);
    $rootArray = array();

    if (!empty($request[$name])) {
    $arrayJson = array();
    print_r($request[$value]);exit;
    for ($i = 0; $i < count($request[$value]); $i++) {
    $arrayJson[$i][$request[$name][$i]] = $request[$value][$i];
    //$arrayJson[$i][$value] = $request[$value][$i];
    //print_r($arrayJson);
    }
    $c = json_encode($arrayJson);
    $rootArray[0]["featureName"] = $request['featureName'];
    $rootArray[0]["data"] = $arrayJson;
    $res = json_encode($rootArray);
    //$result = DB::insert('insert into dynamic(id,data) values(?,?)', [$id, $c]);
    print_r($res);exit;
    //[{"featureName":"1","data":[{"1":"1"},{"2":"2"},{"3":"3"}]}]
    $result = DB::table('products')
    ->where('id', $id)

    ->update(['description' => $res]);

    echo " " . "data saved successfully";

    }
    }
    }
     */
    public function dynamicshow(request $request)
    {
        $id = $request->input('id');
        $data = DB::select('select id,data from dynamic where id=?', [$id]);
        // print_r($data); exit;
        $res = array();
        if ($data) {
            for ($i = 0; $i < count($data); $i++) {
                // print_r($data[$i]->id);exit;
                $res[$i]["id"] = $data[$i]->id;
                $res[$i]["data"] = json_decode($data[$i]->data);
                // print_r(json_decode($data[$i]->data));exit;
            }

            //echo (json_encode($res));

            $result = array("httpcode" => 200, "status" => 1, "data" => $res);
            return (json_encode($result));
        } else {
            $result = array("httpcode" => 400, "status" => 2, "message" => "There is no User data available on this id");
        }
        return json_encode($result);
    }

    public function productApi(Request $data)
    {

        // $rules = [
        // 	'id' => ['required'],
        // 	'category_id' => ['required'],
        // 	'vendor_id' => ['required'],
        // 	'outlet_id' => ['required'],
        // 	'weight_class_id' => ['required'],
        // 	'weight' => ['required'],
        // 	'quantity' => ['required'],
        // 	'original_price' => ['required'],
        // 	'discount_price' => ['required'],
        // 	'created_by' => ['required'],
        // 	//'modified_date' => ['required'],
        // 	'modified_by' => ['required'],
        // 	'approval_status' => ['required'],
        // 	'stock_status' => ['required'],
        // 	'sub_category_id' => ['required'],
        // 	'product_image' => ['required'],
        // 	'product_url' => ['required'],
        // 	'active_status' => ['required'],
        // 	'return_time' => ['required'],
        // 	'vendor_category_id' => ['required'],
        // 	'vendor_type' => ['required'],
        // 	'product_type' => ['required'],
        // 	'product_info_image' => ['required'],
        // 	'vendor_category_id' => ['required'],
        // 	'product_zoom_image' => ['required'],
        // 	'description' => ['required'],

        // ];

        // $post_data = $data->all();

        // $error = $result = array();
        // $validator = app('validator')->make($post_data, $rules);
        // if ($validator->fails()) {
        // 	$errors = '';
        // 	// $j = 0;
        // 	foreach ($validator->errors()->messages() as $key => $value) {
        // 		$error[] = is_array($value) ? implode(',', $value) : $value;
        // 	}
        // 	$errors = implode(", \n ", $error);
        // 	$result = array("response" => array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));

        // } else{

        // 	echo "hai";exit();

        // 			$students = new Student();

        // 			$students->id = $post_data['id'];
        // 			$students->category_id = $post_data['category_id'];
        // 			$students->vendor_id = $post_data['vendor_id'];
        // 			$students->outlet_id = ($post_data['outlet_id']);
        // 			$students->weight_class_id = $post_data['weight_class_id'];
        // 			$students->weight = $post_data['weight'];
        // 			$students->quantity = $post_data['quantity'];
        // 			$students->original_price = $post_data['original_price'];
        // 			$students->discount_price = $post_data['discount_price'];
        // 			$students->created_by = ($post_data['created_by']);
        // 			//$students->modified_date = $post_data['modified_date'];
        // 			$students->modified_by = $post_data['modified_by'];
        // 			$students->approval_status = $post_data['approval_status'];
        // 			$students->return_time = $post_data['return_time'];
        // 			$students->vendor_category_id = $post_data['vendor_category_id';
        // 			$students->vendor_type = $post_data['vendor_type'];
        // 			$students->stock_status = $post_data['stock_status'];
        // 			$students->sub_category_id = $post_data['sub_category_id'];
        // 			$students->product_image = $post_data['product_image'];
        // 			$students->product_url = ($post_data['product_url']);
        // 			$students->active_status = $post_data['active_status'];
        // 			$students->vendor_category_id = $post_data['vendor_category_id'];
        // 			$students->product_info_image = $post_data['product_info_image'];
        // 			$students->product_zoom_image = $post_data['product_zoom_image'];
        // 			$students->product_type =($post_data['product_type']);
        // 			$students->description = $post_data['description'];

        // 			$students->save();

        // 			$result = array("response" => array("httpCode" => 200, "status" => true, "Message" => $students, trans("messages.created successfully")));
        // }

        // return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function demo(Request $data)
    {
        //print_r("expression")exit();
        $post_data = $data->all();

        // $latitude = isset($post_data['latitude']) ? $post_data['latitude'] : "11.0238";
        // $longitude = isset($post_data['longitude']) ? $post_data['longitude'] : "77.0197";
        $latitude = $post_data['latitude'];
        $longitude = $post_data['longitude'];

        $vendors = DB::select("select id,first_name,last_name, earth_distance(ll_to_earth(" . $latitude . "," . $longitude . "), ll_to_earth(11.110695, 77.348045)) as distance from vendors  where earth_box(ll_to_earth(" . $latitude . "," . $longitude . "), 300) @> ll_to_earth(11.110695, 77.348045) order by distance asc");

        // $vendors = DB::select("'select select id ,first_name',earth_distance(ll_to_earth(" . $latitude . "," . $longitude . "), ll_to_earth(11.110695, 77.348045)) as distance from vendors where earth_box(ll_to_earth(" . $latitude . "," . $longitude . "),9000/1.609) @> ll_to_earth(11.110695, 77.348045)");

        // $vendors = DB::table("vendors")
        // 	->select('vendors.id as vendors_id', 'vendors_infos.vendor_name', 'vendors.first_name', 'vendors.last_name', 'vendors.featured_image', 'vendors.logo_image', 'vendors.delivery_time as vendors_delivery_time', 'vendors.category_ids', 'vendors.average_rating as vendors_average_rating', 'vendors.featured_vendor', 'vendors.contact_address'
        // 		, DB::raw("6371 * acos(cos(radians(" . $latitude . "))
        //       * cos(radians(vendors.latitude))
        //       * cos(radians(vendors.longitude) - radians(" . $longitude . "))
        //       + sin(radians(" . $latitude . "))
        //       * sin(radians(vendors.latitude))) AS distance"))
        // 	->leftjoin('vendors_infos', 'vendors_infos.id', '=', 'vendors.id')
        // //->groupBy("vendors.id")
        // 	->orderby('distance')
        // 	->get();

        return $vendors;
    }




    public function uproduct_details(Request $data)
    {
        $post_data = $data->all();
        $user_id = isset($post_data['user_id']) ? $post_data['user_id'] : "";
        $productUrl = $post_data['productUrl'];
        $outletId = $post_data['outletId'];
        $vendorId = $post_data['vendorId'];
        $language_id = 1;
        // if ($post_data['language'] == "ar") {
        //     $language_id = 2;
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        $result = array("response" => array("httpCode" => 400, "status" => false, "data" => $data));
        $condtion = " products.active_status = 1";
        $pquery = '"products_infos"."lang_id" = (case when (select count(products_infos.lang_id) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and products.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $wquery = '"weight_classes_infos"."lang_id" = (case when (select count(weight_classes_infos.lang_id) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . $language_id . ' and products.weight_class_id = weight_classes_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $products = Products::join('products_infos', 'products.id', '=', 'products_infos.id')
            ->join('weight_classes_infos', 'weight_classes_infos.id', '=', 'products.weight_class_id')
            ->join('outlets', 'outlets.id', '=', 'products.outlet_id')
            ->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
            ->select('products.id as product_id', 'products.product_url', 'products.product_info_image', 'products.product_zoom_image', 'products.product_image', 'products.weight', 'products.original_price', 'products.discount_price', 'products.vendor_id', 'products.outlet_id', 'products_infos.description', 'products_infos.product_name', 'products.category_id', 'products.more_description', 'weight_classes_infos.unit', 'weight_classes_infos.title', 'outlet_infos.outlet_name', 'outlets.url_index as outlet_url_index')
            ->whereRaw($pquery)
            ->whereRaw($wquery)
            ->where('vendors.id', $vendorId)
            ->where('outlets.id', '=', $outletId)
            ->where('products_infos.product_name', $productUrl)
            ->first();

        // $products=DB::table('products')
//             ->join('products_infos', 'products.id', '=', 'products_infos.id')
//             ->join('weight_classes_infos', 'weight_classes_infos.id', '=', 'products.weight_class_id')
//             ->join('outlets', 'outlets.id', '=', 'products.outlet_id')
//             ->join('vendors', 'vendors.id', '=', 'products.vendor_id')
//             ->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
//             ->select('products.id as product_id','products_infos.product_name as productName','products.id', 'products.product_url', 'products.product_info_image', 'products.product_zoom_image', 'products.product_image', 'products.weight', 'products.original_price', 'products.discount_price', 'products.vendor_id', 'products.outlet_id', 'products_infos.description','products.category_id', 'products.more_description', 'weight_classes_infos.unit', 'weight_classes_infos.title', 'outlet_infos.outlet_name', 'outlets.url_index as outlet_url_index')

//              ->where('outlets.id', $outletId)
//              ->where('vendors.id', $vendorId)
//             ->where('products_infos.product_name',$productUrl)
//             ->get();
        // print_r($products);exit;
        

        if (count($products) > 0) {
            $products->product_cart_count = 0;
            if (!empty($user_id)) {
                $products->product_cart_count = $this->get_cart_product_count($user_id, $products->product_id);
            }

            $productDetail = new \stdClass();
            $productDetail->productId = $products->product_id;
            $productDetail->productName = $products->product_name;
            $productDetail->productUrl = $products->product_url;
            $productDetail->weight = $products->weight;
            $productDetail->originalPrice = $products->original_price;
            $productDetail->discountPrice = $products->discount_price;
            $productDetail->categoryId = $products->category_id;
            //  $productDetail-> offer=$products -> product_id;
            $productDetail->unit = $products->unit;
            $productDetail->description = $products->description;
            $productDetail->title = $products->product_name;
            $productDetail->itemQuantity = $products->product_id;
            $productDetail->outletId = $products->outlet_id;
            $productDetail->outletName = $products->outlet_name;

            $productDetail->averageRating = 0;
            // $productDetail-> itemFeatures=array();
            //print_r(json_decode($products->more_description));exit;
            $des = json_decode($products->more_description);
            $data = array();
            if (count($des) != 0) {
                foreach ($des as $key => $value) {
                    $featureName = $value->featureName;
                    $data[$key]['featureName'] = $featureName;
                    foreach ($value->data as $keys => $val) {
                        $data[$key]['data'][$keys]['itemName'] = key($val);
                        $data[$key]['data'][$keys]['itemDescription'] = current($val);
                    }
                }
            }

            //$productDetail->itemFeatures = json_decode($data, true);
            $productDetail->itemFeatures = $data;

            $productDetail->itemQuantity = array();

            $k=0;

            $productDetail->itemQuantity[$k]["subId"] = "1";
            $productDetail->itemQuantity[$k]["itemUnit"] = $products->unit;
            $productDetail->itemQuantity[$k]["itemNewPrice"] = floatval($products->discount_price);
            $productDetail->itemQuantity[$k]["itemOldPrice"] = floatval($products->original_price);

            $productDetail->itemQuantity[$k]['itemWeight'] = $products->weight;


            $k++;

            $i=0;


            //$array=array();
            if (count($products->$productUrl)>0) {
                $productDetail->itemQuantity[$i]["subId"] = "2";
                $productDetail->itemQuantity[$i]["itemUnit"] = $products->unit;
                $productDetail->itemQuantity[$i]["itemNewPrice"] = floatval($products->discount_price);
                $productDetail->itemQuantity[$i]["itemOldPrice"] = floatval($products->original_price);
                $productDetail->itemQuantity[$i]['itemWeight'] = $products->weight;

                $i++;
            }
            $productDetail->itemImageUrls = array();
            // if (file_exists(base_path() . '/public/assets/admin/base/images/products/' . $st->product_image) && $st->product_image != '') {
            $productDetail->productImage = url('/assets/admin/base/images/products/list/' . $products->product_image);
            $productDetail->productZoomImage = url('/assets/admin/base/images/products/list/' . $products->product_zoom_image);
            $productDetail->productInfoImage = url('/assets/admin/base/images/products/list/' . $products->product_info_image);
            $productDetail->itemImageUrls[0] = url('/assets/admin/base/images/products/list/' . $products->product_image);
            // $productDetail->itemImageUrls[1] = url('/assets/admin/base/images/products/list/' . $products->product_zoom_image);

            // }
            $result = array("status" => 200, "message" => "Success", 'productDetail' => $productDetail);
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }






    public function mdashboard_mob(Request $data)
    {
        $post_data = $data->all();

        
        $language = 1;
        // if ($post_data['language'] == "ar") {
        //     $language = 2;
        // }

        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        
        $category_ids = isset($post_data['category_ids']) ? $post_data['category_ids'] : "";
        $category_url = isset($post_data['category_url']) ? $post_data['category_url'] : "";
        $city = isset($post_data['city']) ? $post_data['city'] : "";
        $location = isset($post_data['location']) ? $post_data['location'] : "";
        $keyword = isset($post_data['keyword']) ? $post_data['keyword'] : "";
        $sortby = isset($post_data['sortby']) ? $post_data['sortby'] : "";
        $orderby = isset($post_data['orderby']) ? $post_data['orderby'] : "desc";
        $stores = array();
        $limit = !empty($post_data['countPerPage']) ? $post_data['countPerPage'] : 10;
        if (!empty($post_data['pageNumber'])) {
            $pn = $post_data['pageNumber'];
        } else {
            $pn = 1;
        }
        $offset = ($pn - 1) * $limit;

       
        $latitude = isset($post_data['latitude']) ? $post_data['latitude'] : "25.204849";
        $longitude = isset($post_data['longitude']) ? $post_data['longitude'] : "55.270782";

        $stores_list = Stores::nearest_outlets_list($language, $category_ids, $category_url, $city, $location, $latitude, $longitude, $keyword, $sortby, $orderby);
        if (count($stores_list)>0) {
            $message =trans("messages.Store list");
            $status=1;
        } else {
            //$city_list = Stores::city_list();
            $zone_list = Stores::zone_list();

            $status=2;
            $message =trans("messages.Sorry for inconvenience ,currently we are available at ").$zone_list;
        }

        //print_r($stores_list);exit;
         $banners = DB::table('banner_settings')->select('*')->where('status', 1)->orderBy('default_banner', 'desc')->get();
         //print_r($banners);exit;

         $banner_list = array();
         $featuredItem = array();

         $featureNameArray = array("Grocery", "Fish & Meat", "Personal", "Home & Kitchen", "Baby", "Bevarages", "Fruits", "Snacks");

         $featureImageArray = array("https://broz.app/assets/admin/base/images/featureItem/Grocery.png?2019-02-17 19:42:25"
             , "https://broz.app/assets/admin/base/images/featureItem/meat.png?2019-02-17 19:42:26",
             "https://broz.app/assets/admin/base/images/featureItem/personal.png?2019-02-17 19:42",
             "https://broz.app/assets/admin/base/images/featureItem/kitchen.png?2019-02-17 19:42:25",
             "https://broz.app/assets/admin/base/images/featureItem/baby.png?2019-02-17 19:42:25",
             "https://broz.app/assets/admin/base/images/featureItem/beverges.png?2019-02-17 19:42:25",
             "https://broz.app/assets/admin/base/images/featureItem/fruits.png?2019-02-17 19:42:25",

             "https://broz.app/assets/admin/base/images/featureItem/snacks.png?2019-02-17 19:42:25",
         );

         //Naga -> Waiting for php dev

         $data1 = '[{
                     "id": 49,
                     "description": "Grocery",
                     "categoryLevel": 2,
                     "categoryName": "Grocery & Stables",
                     "categoryId": 514,
                     "logoImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
                     "bannerImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
                     "itemOffer": "",
                     "childCategories": [{
                         "id": 471,
                         "description": "Pulses",
                         "categoryName": "Pulses",
                         "categoryId": 471
                     }]
                 }]';
         $data2 = '[{
                 "id": 49,
                 "description": "Fish & Meat",
                 "categoryLevel": 2,
                 "categoryName": "Fish & Meat",
                 "categoryId": 49,
                 "logoImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
                 "bannerImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
                 "itemOffer": "",
                 "childCategories": [{
                     "id": 507,
                     "description": "Fish",
                     "categoryName": "Fish",
                     "categoryId": 507
                 }]
             }]';
         $data3 = '[{
                     "id": 49,
                     "description": "Personal",
                     "categoryLevel": 2,
                     "categoryName": "Personal",
                     "categoryId": 49,
                     "logoImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
                     "bannerImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
                     "itemOffer": "",
                     "childCategories": [{
                         "id": 490,
                         "description": "Body Care",
                         "categoryName": "Body Care",
                         "categoryId": 490
                     }]
                 }]';
         $data4 = '[{
                 "id": 49,
                 "description": "Home & Kitchen",
                 "categoryLevel": 2,
                 "categoryName": "Home & Kitchen",
                 "categoryId": 49,
                 "logoImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
                 "bannerImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
                 "itemOffer": "",
                 "childCategories": [{
                     "id": 500,
                     "description": "DishWasher",
                     "categoryName": "DishWasher",
                     "categoryId": 500
                 }]
             }]';
         $data5 = '[{
                 "id": 49,
                 "description": "Babys & Kids",
                 "categoryLevel": 2,
                 "categoryName": "Babys & Kids",
                 "categoryId": 49,
                 "logoImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
                 "bannerImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
                 "itemOffer": "",
                 "childCategories": [{
                     "id": 497,
                     "description": "Baby Shop",
                     "categoryName": "Baby Shop",
                     "categoryId": 497
                 }]
             }]';
         $data6 = '[{
                 "id": 49,
                 "description": "Beverages",
                 "categoryLevel": 2,
                 "categoryName": "Beverages & Soft Drinks",
                 "categoryId": 49,
                 "logoImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
                 "bannerImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
                 "itemOffer": "",
                 "childCategories": [{
                     "id": 502,
                     "description": "Cool Drinks",
                     "categoryName": "Cool Drinks",
                     "categoryId": 502
                 }]
             }]';
         $data7 = '[{
                 "id": 49,
                 "description": "Fruits",
                 "categoryLevel": 2,
                 "categoryName": "Fruits",
                 "categoryId": 49,
                 "logoImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
                 "bannerImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
                 "itemOffer": "",
                 "childCategories": [{
                     "id": 484,
                     "description": "Dry Fruits",
                     "categoryName": "Dry Fruits",
                     "categoryId": 484
                 }]
             }]';
         $data8 = '[{
                 "id": 49,
                 "description": "Backery & Snacks",
                 "categoryLevel": 2,
                 "categoryName": "Backery & Snacks",
                 "categoryId": 49,
                 "logoImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
                 "bannerImage": "https:\/\/broz.app\/assets\/admin\/base\/images\/featuredItem\/fruits.jpg?2019-02-17 19:42:25",
                 "itemOffer": "",
                 "childCategories": [{
                     "id": 493,
                     "description": "Breads",
                     "categoryName": "Breads",
                     "categoryId": 493
                 }]
             }]';
         $fdataArray = array($data1, $data2, $data3, $data4, $data5, $data6, $data7, $data8);



         for ($x = 0; $x <= 7; $x++) {
             $key = "name" . $x;
             $featuredItem[$x]['name'] = $featureNameArray[$x];
             $featuredItem[$x]['logo'] = $featureImageArray[$x];

             $data = $fdataArray[$x];
             $featuredItem[$x]['vendorId'] = 236;
             $featuredItem[$x]['outletId'] = 113;
             $featuredItem[$x]['vendorName'] = "Broz";

             $featuredItem[$x]['subCategories'] = json_decode($data);
         }

         if (count($banners) > 0) {
             foreach ($banners as $key => $items) {
                 // $banners[$key]->bannerImageUrl = url('/assets/admin/base/images/' . $items->banner_image.'?'.time());
                 $banner_list[$key]['bannerSettingId'] = $items->banner_setting_id;
                 $banner_list[$key]['bannerTitle'] = $items->banner_title;
                 $banner_list[$key]['bannerSubtitle'] = $items->banner_subtitle;
                 $banner_list[$key]['bannerImage'] = $items->banner_image;
                 $banner_list[$key]['bannerLink'] = $items->banner_link;
                 $banner_list[$key]['defaultBanner'] = $items->default_banner;
                 $banner_list[$key]['status'] = $items->status;
                 $banner_list[$key]['updatedDate'] = $items->updated_date;
                 $banner_list[$key]['createdDate'] = $items->created_date;
                 $banner_list[$key]['createdDate'] = $items->created_date;
                 $banner_list[$key]['bannerType'] = $items->banner_type;
                 $banner_list[$key]['languageType'] = $items->language_type;
                 $banner_list[$key]['bannerImageUrl'] = url('/assets/admin/base/images/banner/' . $items->banner_image . '?' . $items->updated_date);
             }
         }


        if (count($stores_list) > 0) {
            $list =$stores=array() ;

            //  print_r($stores_list);exit;
            $j = 0;

            foreach ($stores_list as $key => $value) {
                if (!in_array($value->vendors_id, $list)) {
                    array_push($list, $value->vendors_id);
                    //print_r($value);//exit;

                    $stores[$j]['vendorId'] = $value->vendors_id;

                    $stores[$j]['vendorName'] = $value->vendor_name;
                    $stores[$j]['categoryIds'] = $value->category_ids;
                    $stores[$j]['address'] = $value->contact_address;
                    $stores[$j]['description'] = $value->vendor_description;

                    $featured_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/list/' . $value->featured_image) && $value->featured_image != '') {
                        $featured_image = url('/assets/admin/base/images/vendors/list/' . $value->featured_image . '?' . time());
                    }
                    

                    $stores[$j]['featuredImage'] = $featured_image;
                    $stores[$j]['deliveryTime'] = $value->vendors_delivery_time ." min(s)";
                    $stores[$j]['vendorsRating'] = $value->vendors_average_rating;
                    //print_r($featured_image);exit();
                    $stores[$j]['offer'] = ""
                    ;
                    $stores[$j]['comboOffer'] = "";

                    $logo_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $value->logo_image) && $value->logo_image != '') {
                        $logo_image = url('/assets/admin/base/images/vendors/logos/' . $value->logo_image . '?' . time());
                    }
                    
                
                    $stores[$j]['logoImage'] =$logo_image;
                    
                    $k=0;
                    $stores[$j]['nearestList'][$k]['vendorId']=$value->vendors_id;
                    $stores[$j]['nearestList'][$k]['outletId']=$value->outlets_id;
                    $stores[$j]['nearestList'][$k]['outletName'] = $value->outlet_name;
                    $stores[$j]['nearestList'][$k]['address'] = $value->contact_address;
                    $stores[$j]['nearestList'][$k]['description'] = $value->vendor_description;
                    $stores[$j]['nearestList'][$k]['deliveryTime'] = $value->outlets_delivery_time ." min(s)";
                    $stores[$j]['nearestList'][$k]['vendorsRating'] = $value->vendors_average_rating;

                    $k++;
                } else {
                    $x=0;
                    foreach ($stores as $key => $val) {
                        //$x = '';
                        if ($val['vendorId'] === $value->vendors_id) {
                            //$x = $key;
                  

                            $count = count($stores[$x]['nearestList']);
                            $stores[$x]['nearestList'][$count]['vendorId'] = $value->vendors_id;
                            $stores[$x]['nearestList'][$count]['outletId'] = $value->outlets_id;
                            $stores[$x]['nearestList'][$count]['outletName'] = $value->outlet_name;
                            $stores[$x]['nearestList'][$count]['address'] = $value->contact_address;
                            $stores[$x]['nearestList'][$count]['description'] = $value->vendor_description;
                            $stores[$x]['nearestList'][$count]['deliveryTime'] = $value->outlets_delivery_time ." min(s)";
                            $stores[$x]['nearestList'][$count]['vendorsRating'] = $value->vendors_average_rating;
                        }
                        $x++;
                    }
                    $j--;
                }

                $j++;
            }
        }


       
        $result = array("status" => $status, "httpCode" => 200, "message" => $message, "vendorList" => $stores, "bannerList" => $banner_list, "featuredItem" => $featuredItem);
        //}
        return json_encode($result);
    }

   

    public function newProductList(Request $data)
    {
        $rules = [
            'language' => ['required'],
            // 'outletId' => ['required'],
            // 'vendorId' => ['required'],
            // 'categoryId' => ['required'],
            // 'pageSize' => ['required'],
            // 'SkipSize' => ['required'],
        ];

        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        $error = $result = array();
        //$validator = app('validator')->make($post_data, $rules);
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            // $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array( "status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            $productName = $post_data['productName'];
            $language_id = $post_data['language'];
            $outletId = $post_data['outletId'];
            $vendorId = $post_data['vendorId'];
            $categoryId = $post_data['categoryId'];
            $orderStatus = $post_data['orderStatus'];
            
            $pageSize = $post_data['pageSize'];
            $SkipSize = $post_data['SkipSize'];
            
            $keyword = isset($productName) ? $productName : '';
            $data = array();
            // $result =  array("status" => 0, "data" => $data);


            //{"SkipSize":0,"categoryId":0,"language":"en","outletId":123,"pageSize":20,"productName":"drink","vendorId":245}

            $condtion = 'outlets.id ='.$outletId.' and outlets.vendor_id ='.$vendorId;
            if ($categoryId) {
                $condtion .= " and products.category_id =".$categoryId;

            }

            if ($orderStatus != 0 && $orderStatus == 2) {
                
                $condtion .= " and products.item_available_status =".$orderStatus;
                $condtion .= " and products.item_next_available_time = 'Coming Soon'";

            }elseif($orderStatus != 0 && $orderStatus == 3){

                $condtion .= " and products.item_available_status = 2";
                $condtion .= " and products.item_next_available_time != 'Coming Soon'";

            }elseif ($orderStatus != 0 && $orderStatus != 2 && $orderStatus != 3) {

                $condtion .= " and products.item_available_status =".$orderStatus;
            }

            if ($productName) {
                $condtion .= " and products_infos.product_name ILIKE '%" . $keyword . "%'";
            }
            //print_r($condtion);exit;

             $products = Products::join('products_infos', 'products.id', '=', 'products_infos.id')
                ->join('categories', 'categories.id', '=', 'products.category_id')
                ->join('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
                ->join('weight_classes', 'weight_classes.id', '=', 'products.weight_class_id')
                ->join('weight_classes_infos', 'weight_classes_infos.id', '=', 'weight_classes.id')
                ->join('outlets', 'outlets.id', '=', 'products.outlet_id')
                ->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
                ->select(
                    'products.id as id',
                    'products.item_next_available_time as itemAvailableStatus',
                    'product_url as productUrl',
                    'product_name as productName',
                    'vendor_category_id as categoryId',
                    'products.product_image',
                    'products.product_info_image',
                    'products.product_zoom_image',
                    'original_price as originalPrice',
                    'discount_price as discountPrice',
                    'products_infos.description',
                    'products.weight',
                    'categories_infos.category_name as categoryName',
                    'weight_classes_infos.unit',
                    'weight_classes_infos.title',
                    'products.category_id as categoryId',
                    'outlets.id as outletId',
                    'outlets.vendor_id as vendorId',
                    'outlet_infos.outlet_name as outletName',
                    'categories.url_key as urlKey',
                    'categories.url_key as catUrl'
                )
                ->distinct()
                ->whereRaw($condtion)
                ->limit($pageSize)
                ->skip($SkipSize)
                ->orderBy('products.id', 'asc')
                ->get();

                $data=array();

                
                if (count($products)>0) {
                    foreach ($products as $index=> $value) {

                        $product_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                        if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $value->product_image) && $value->product_image != '') {
                            $product_image = url('/assets/admin/base/images/products/list/' . $value->product_image);
                        }
                        $data[$index]['productId'] = $value->id;
                        $data[$index]['productUrl'] = $value->productUrl;
                        $data[$index]['productName'] = $value->productName;
                        $data[$index]['categoryId'] = $value->categoryId;
                        $data[$index]['productImage'] = $product_image;
                        $data[$index]['productInfoImage'] = $product_image;
                        $data[$index]['productZoomImage'] = $product_image;
                        $data[$index]['originalPrice'] =$value->originalPrice;
                        $data[$index]['discountPrice'] =$value->discountPrice;
                        $data[$index]['description'] =$value->description;
                        $data[$index]['weight'] =$value->weight;
                        $data[$index]['categoryName'] =$value->categoryName;
                        $data[$index]['unit'] =$value->unit;
                        $data[$index]['title'] =$value->title;
                        $data[$index]['categoryId'] =$value->categoryId;
                        $data[$index]['outletId'] =$value->outletId;
                        $data[$index]['vendorId'] =$value->vendorId;
                        $data[$index]['outletName'] =$value->outletName;
                        $data[$index]['urlKey'] =$value->urlKey;
                        $data[$index]['catUrl'] =$value->catUrl;
                        $data[$index]['itemAvailableStatus'] =$value->itemAvailableStatus;
                    }
                }
                                                  //  print_r($data);exit;

                $result = array("status" => 200, "message" => "Success","pageSize"=>$products->count(), 'productList' => $data);



            /*if (!empty($productName) and (""==$categoryId)) {               // print_r("expression");exit;

                $products = Products::join('products_infos', 'products.id', '=', 'products_infos.id')
                ->join('categories', 'categories.id', '=', 'products.category_id')
                ->join('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
                ->join('weight_classes', 'weight_classes.id', '=', 'products.weight_class_id')
                ->join('weight_classes_infos', 'weight_classes_infos.id', '=', 'weight_classes.id')
                ->join('outlets', 'outlets.id', '=', 'products.outlet_id')
                ->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
                ->select(
                    'products.id as id',
                    'products.item_next_available_time as itemAvailableStatus',
                    'product_url as productUrl',
                    'product_name as productName',
                    'vendor_category_id as categoryId',
                    'products.product_image',
                    'products.product_info_image',
                    'products.product_zoom_image',
                    'original_price as originalPrice',
                    'discount_price as discountPrice',
                    'products_infos.description',
                    'products.weight',
                    'categories_infos.category_name as categoryName',
                    'weight_classes_infos.unit',
                    'weight_classes_infos.title',
                    'products.category_id as categoryId',
                    'outlets.id as outletId',
                    'outlets.vendor_id as vendorId',
                    'outlet_infos.outlet_name as outletName',
                    'categories.url_key as urlKey',
                    'categories.url_key as catUrl'
                )
                ->distinct()
                ->where('products.category_id', '=', $categoryId)
                ->where('outlets.id', '=', $outletId)
                ->where('outlets.vendor_id', '=', $vendorId)
                ->limit($pageSize)
                ->skip($SkipSize)
                ->orderBy('products.id', 'asc')
                ->get();

                $data=array();
                if (count($products)>0) {
                    foreach ($products as $index=> $value) {
                        $product_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                        if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $value->product_image) && $value->product_image != '') {
                            $product_image = url('/assets/admin/base/images/products/list/' . $value->product_image);
                        }
                        $data[$index]['productId'] = $value->id;
                        $data[$index]['productUrl'] = $value->productUrl;
                        $data[$index]['productName'] = $value->productName;
                        $data[$index]['categoryId'] = $value->categoryId;
                        $data[$index]['productImage'] = $product_image;
                        $data[$index]['productInfoImage'] = $product_image;
                        $data[$index]['productZoomImage'] = $product_image;
                        $data[$index]['originalPrice'] =$value->originalPrice;
                        $data[$index]['discountPrice'] =$value->discountPrice;
                        $data[$index]['description'] =$value->description;
                        $data[$index]['weight'] =$value->weight;
                        $data[$index]['categoryName'] =$value->categoryName;
                        $data[$index]['unit'] =$value->unit;
                        $data[$index]['title'] =$value->title;
                        $data[$index]['categoryId'] =$value->categoryId;
                        $data[$index]['outletId'] =$value->outletId;
                        $data[$index]['vendorId'] =$value->vendorId;
                        $data[$index]['outletName'] =$value->outletName;
                        $data[$index]['urlKey'] =$value->urlKey;
                        $data[$index]['catUrl'] =$value->catUrl;
                        $data[$index]['itemAvailableStatus'] =$value->itemAvailableStatus;
                    }
                }
                $result = array("status" => 200, "message" => "Success","pageSize"=>$products->count(), 'productList' => $data);
            }
            // print_r($data);exit;
            elseif (!empty($categoryId) and (""==$productName)) {
                $products = Products::join('products_infos', 'products.id', '=', 'products_infos.id')
                ->join('categories', 'categories.id', '=', 'products.category_id')
                ->join('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
                ->join('weight_classes', 'weight_classes.id', '=', 'products.weight_class_id')
                ->join('weight_classes_infos', 'weight_classes_infos.id', '=', 'weight_classes.id')
                ->join('outlets', 'outlets.id', '=', 'products.outlet_id')
                ->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
                ->select(
                    'products.id as id',
                    'products.item_next_available_time as itemAvailableStatus',
                    'product_url as productUrl',
                    'product_name as productName',
                    'vendor_category_id as categoryId',
                    'products.product_image',
                    'products.product_info_image',
                    'products.product_zoom_image',
                    'original_price as originalPrice',
                    'discount_price as discountPrice',
                    'products_infos.description',
                    'products.weight',
                    'categories_infos.category_name as categoryName',
                    'weight_classes_infos.unit',
                    'weight_classes_infos.title',
                    'products.category_id as categoryId',
                    'outlets.id as outletId',
                    'outlets.vendor_id as vendorId',
                    'outlet_infos.outlet_name as outletName',
                    'categories.url_key as urlKey',
                    'categories.url_key as catUrl'
                )
                ->distinct()
                 ->where('products.category_id', '=', $categoryId)
                ->where('outlets.id', '=', $outletId)
                ->where('outlets.vendor_id', '=', $vendorId)
                ->limit($pageSize)
                ->skip($SkipSize)
                ->orderBy('products.id', 'asc')
                ->get();

                $data=array();

                
                if (count($products)>0) {
                    foreach ($products as $index=> $value) {
                        $product_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                        if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $value->product_image) && $value->product_image != '') {
                            $product_image = url('/assets/admin/base/images/products/list/' . $value->product_image);
                        }
                        $data[$index]['productId'] = $value->id;
                        $data[$index]['productUrl'] = $value->productUrl;
                        $data[$index]['productName'] = $value->productName;
                        $data[$index]['categoryId'] = $value->categoryId;
                        $data[$index]['productImage'] = $product_image;
                        $data[$index]['productInfoImage'] = $product_image;
                        $data[$index]['productZoomImage'] = $product_image;
                        $data[$index]['originalPrice'] =$value->originalPrice;
                        $data[$index]['discountPrice'] =$value->discountPrice;
                        $data[$index]['description'] =$value->description;
                        $data[$index]['weight'] =$value->weight;
                        $data[$index]['categoryName'] =$value->categoryName;
                        $data[$index]['unit'] =$value->unit;
                        $data[$index]['title'] =$value->title;
                        $data[$index]['categoryId'] =$value->categoryId;
                        $data[$index]['outletId'] =$value->outletId;
                        $data[$index]['vendorId'] =$value->vendorId;
                        $data[$index]['outletName'] =$value->outletName;
                        $data[$index]['urlKey'] =$value->urlKey;
                        $data[$index]['catUrl'] =$value->catUrl;
                        $data[$index]['itemAvailableStatus'] =$value->itemAvailableStatus;
                    }
                }
                $result = array("status" => 200, "message" => "Success","pageSize"=>$products->count(), 'productList' => $data);
            } elseif ((""==$productName) and (""==$categoryId)) {
                $products = Products::join('products_infos', 'products.id', '=', 'products_infos.id')
                ->join('categories', 'categories.id', '=', 'products.category_id')
                ->join('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
                ->join('weight_classes', 'weight_classes.id', '=', 'products.weight_class_id')
                ->join('weight_classes_infos', 'weight_classes_infos.id', '=', 'weight_classes.id')
                ->join('outlets', 'outlets.id', '=', 'products.outlet_id')
                ->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
                ->select(
                    'products.id as id',
                    'products.item_next_available_time as itemAvailableStatus',
                    'product_url as productUrl',
                    'product_name as productName',
                    'vendor_category_id as categoryId',
                    'products.product_image',
                    'products.product_info_image',
                    'products.product_zoom_image',
                    'original_price as originalPrice',
                    'discount_price as discountPrice',
                    'products_infos.description',
                    'products.weight',
                    'categories_infos.category_name as categoryName',
                    'weight_classes_infos.unit',
                    'weight_classes_infos.title',
                    'products.category_id as categoryId',
                    'outlets.id as outletId',
                    'outlets.vendor_id as vendorId',
                    'outlet_infos.outlet_name as outletName',
                    'categories.url_key as urlKey',
                    'categories.url_key as catUrl'
                )
                ->distinct()
                ->where('outlets.id', '=', $outletId)
                //->where('products.category_id', '=', $categoryId)
                ->where('outlets.vendor_id', '=', $vendorId)
                ->limit($pageSize)
                ->skip($SkipSize)
                ->orderBy('products.id', 'asc')
                ->get();

                $data=array();

                
                if (count($products)>0) {
                    foreach ($products as $index=> $value) {
                        $product_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                        if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $value->product_image) && $value->product_image != '') {
                            $product_image = url('/assets/admin/base/images/products/list/' . $value->product_image);
                        }
                        $data[$index]['productId'] = $value->id;
                        $data[$index]['productUrl'] = $value->productUrl;
                        $data[$index]['productName'] = $value->productName;
                        $data[$index]['categoryId'] = $value->categoryId;
                        $data[$index]['productImage'] = $product_image;
                        $data[$index]['productInfoImage'] = $product_image;
                        $data[$index]['productZoomImage'] = $product_image;
                        $data[$index]['originalPrice'] =$value->originalPrice;
                        $data[$index]['discountPrice'] =$value->discountPrice;
                        $data[$index]['description'] =$value->description;
                        $data[$index]['weight'] =$value->weight;
                        $data[$index]['categoryName'] =$value->categoryName;
                        $data[$index]['unit'] =$value->unit;
                        $data[$index]['title'] =$value->title;
                        $data[$index]['categoryId'] =$value->categoryId;
                        $data[$index]['outletId'] =$value->outletId;
                        $data[$index]['vendorId'] =$value->vendorId;
                        $data[$index]['outletName'] =$value->outletName;
                        $data[$index]['urlKey'] =$value->urlKey;
                        $data[$index]['catUrl'] =$value->catUrl;
                        $data[$index]['itemAvailableStatus'] =$value->itemAvailableStatus;
                    }
                }
                $result = array("status" => 1, "message" => "Success","pageSize"=>$products->count(), 'productList' => $data);
            } else {
                $products = Products::join('products_infos', 'products.id', '=', 'products_infos.id')
                ->join('categories', 'categories.id', '=', 'products.category_id')
                ->join('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
                ->join('weight_classes', 'weight_classes.id', '=', 'products.weight_class_id')
                ->join('weight_classes_infos', 'weight_classes_infos.id', '=', 'weight_classes.id')
                ->join('outlets', 'outlets.id', '=', 'products.outlet_id')
                ->select(
                    'products.id as id',
                    'product_url as productUrl',
                    'product_name as productName',
                    'vendor_category_id as categoryId',
                    'products.product_image',
                    'products.product_info_image',
                    'products.product_zoom_image',
                    'original_price as originalPrice',
                    'discount_price as discountPrice',
                    'products_infos.description',
                    'products.weight',
                    'categories_infos.category_name as categoryName',
                    'weight_classes_infos.unit',
                    'weight_classes_infos.title',
                    'products.category_id as categoryId',
                    'outlets.id as outletId',
                    'outlets.vendor_id as vendorId',
                    'products.item_next_available_time as itemAvailableStatus',
                    'categories.url_key as urlKey',
                    'categories.url_key as catUrl'
                )
                ->where('outlets.id', '=', $outletId)
                ->where('outlets.vendor_id', '=', $vendorId)
                ->where('products.category_id', '=', $categoryId)
                ->where('products_infos.product_name', 'LIKE', "%{$productName}%")
                ->limit($pageSize)
                ->skip($SkipSize)
                ->orderBy('products_infos.id', 'asc')
                ->get();

                $data=array();

                if (count($products)>0) {
                    foreach ($products as $index=> $value) {
                        $product_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                        if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $value->product_image) && $value->product_image != '') {
                            $product_image = url('/assets/admin/base/images/products/list/' . $value->product_image);
                        }
                        $data[$index]['productId'] = $value->id;
                        $data[$index]['productUrl'] = $value->productUrl;
                        $data[$index]['productName'] = $value->productName;
                        $data[$index]['categoryId'] = $value->categoryId;
                        $data[$index]['productImage'] = $product_image;
                        $data[$index]['productInfoImage'] = $product_image;
                        $data[$index]['productZoomImage'] = $product_image;
                        $data[$index]['originalPrice'] =$value->originalPrice;
                        $data[$index]['discountPrice'] =$value->discountPrice;
                        $data[$index]['description'] =$value->description;
                        $data[$index]['weight'] =$value->weight;
                        $data[$index]['categoryName'] =$value->categoryName;
                        $data[$index]['unit'] =$value->unit;
                        $data[$index]['title'] =$value->title;
                        $data[$index]['categoryId'] =$value->categoryId;
                        $data[$index]['outletId'] =$value->outletId;
                        $data[$index]['vendorId'] =$value->vendorId;
                        $data[$index]['urlKey'] =$value->urlKey;
                        $data[$index]['catUrl'] =$value->catUrl;
                        $data[$index]['itemAvailableStatus'] =$value->itemAvailableStatus;
                    }
                }
                $result = array("status" => 1, "message" => "Success","pageSize"=>$products->count(), 'productList' => $data);
                
            }*/
        }
        
        return json_encode($result);
    }

    public function getItemDetails(Request $data)
    {
        $rules = [
            'language' => ['required'],
            'productId' => ['required'],
        ];

        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors);
        } else {
            $productId= $post_data['productId'];
           
            $products = Products::join('products_infos', 'products.id', '=', 'products_infos.id')
                ->join('categories', 'categories.id', '=', 'products.category_id')
                ->join('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
                ->join('weight_classes', 'weight_classes.id', '=', 'products.weight_class_id')
                ->join('weight_classes_infos', 'weight_classes_infos.id', '=', 'weight_classes.id')
                ->join('outlets', 'outlets.id', '=', 'products.outlet_id')
                ->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
                ->select(
                    'products.id as id',
                    'product_url as productUrl',
                    'product_name as productName',
                    'vendor_category_id as categoryId',
                    'products.product_image',
                    'products.product_info_image',
                    'products.product_zoom_image',
                    'original_price as originalPrice',
                    'discount_price as discountPrice',
                    'products_infos.description',
                    'products.weight',
                    'categories_infos.category_name as categoryName',
                    'weight_classes_infos.unit',
                    'weight_classes_infos.title',
                    'products.weight_class_id as weightClassId',
                    'products.quantity as quantity',
                    'products.adjust_quantity as itemAdjustmentQuantity',
                    'products.item_next_available_time',
                    'products.item_available_status',
                    'products.category_id as categoryId',
                    'outlets.id as outletId',
                    'outlets.vendor_id as vendorId',
                    'outlet_infos.outlet_name as outletName',
                    'categories.url_key as urlKey',
                    'categories.url_key as catUrl'
                )
                //->distinct()
                ->where('products.id', '=', $productId)
                
                ->orderBy('products_infos.id', 'asc')
                ->get();
            $data=array();

            if (count($products)>0) {
                foreach ($products as $index=> $value) {
                    $product_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $value->product_image) && $value->product_image != '') {
                        $product_image = url('/assets/admin/base/images/products/list/' . $value->product_image);
                    }
                    $data[$index]['productId'] = $value->id;
                    $data[$index]['productUrl'] = $value->productUrl;
                    $data[$index]['productName'] = $value->productName;
                    $data[$index]['categoryId'] = $value->categoryId;
                    $data[$index]['productImage'] = $product_image;
                    $data[$index]['productInfoImage'] = $product_image;
                    $data[$index]['productZoomImage'] = $product_image;
                    $data[$index]['originalPrice'] =$value->originalPrice;
                    $data[$index]['discountPrice'] =$value->discountPrice;
                    $data[$index]['description'] =$value->description;
                    $data[$index]['weight'] =$value->weight;
                    $data[$index]['weightClassId'] =$value->weightClassId;
                    $data[$index]['quantity'] =$value->quantity;
                    $data[$index]['itemAdjustmentQuantity'] =$value->itemAdjustmentQuantity;
                    $data[$index]['itemNextAvailableTime'] =$value->item_next_available_time;
                    $data[$index]['itemAvailableStatus'] =$value->item_available_status;
                    $data[$index]['categoryName'] =$value->categoryName;
                    $data[$index]['unit'] =$value->unit;
                    $data[$index]['title'] =$value->title;
                    $data[$index]['categoryId'] =$value->categoryId;
                    $data[$index]['outletId'] =$value->outletId;
                    $data[$index]['vendorId'] =$value->vendorId;
                    $data[$index]['outletName'] =$value->outletName;
                    $data[$index]['urlKey'] =$value->urlKey;
                    $data[$index]['catUrl'] =$value->catUrl;
                }
            }

            $result = array("status" => 200, "message" => "Success",'productList' => $data);
        }
        return json_encode($result);
    }



    public function updateProductDetails(Request $data)
    {
        $rules = [
            'language' => ['required'],
        ];

        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors);
        } else {
            $productId=$post_data['productId'];
            $itemWeightClassId=$post_data['itemWeightClassId'];
            $itemWeight=$post_data['itemWeight'];
            $itemQuantity=$post_data['itemQuantity'];
            $itemOriginalPrice=$post_data['itemOriginalPrice'];
            $itemDiscountPrice=$post_data['itemDiscountPrice'];
            $itemAdjustmentQuantity=$post_data['itemAdjustmentQuantity'];
            $getItemAvailbaleStatus=$post_data['getItemAvailbaleStatus'];
            $itemNextAvailable=$post_data['itemNextAvailable'];


            $updateProductDetails=DB::table('products')
            ->where('products.id', $productId)
            ->update(['weight_class_id'=>$itemWeightClassId,
                                    'weight'=> $itemWeight,
                                    'quantity'=>$itemQuantity,
                                    'original_price'=>$itemOriginalPrice,
                                    'discount_price'=>$itemDiscountPrice,
                                    'adjust_quantity'=>$itemAdjustmentQuantity,
                                    'item_available_status'=>$getItemAvailbaleStatus,
                                    'item_next_available_time'=>$itemNextAvailable]);
                                         
            $result = array("status" => 200, "message" => "product details updated successfully.");
        }

        return json_encode($result);
    }

    //Ram :11/09/2019
    public function getCategoryDetail(Request $data)
    {
        $rules = [
            'language' => ['required'],
            // 'outletId' => ['required'],
            // 'vendorId' => ['required'],
            
        ];

        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            // $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors);
        } else {
            $outletId=$post_data['outletId'];
            $vendorId=$post_data['vendorId'];



            $categoryDetails=DB::table('products')
                   -> join('categories', 'categories.id', '=', 'products.category_id')
                ->join('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
                // ->join('outlets', 'outlets.id', '=', 'products.outlet_id')
                 //->join('vendors', 'vendors.id', '=', 'products.vendor_id')
                ->select(
                    'categories_infos.category_name as categoryName',
                    'products.category_id as categoryId',
                    'categories.url_key as catUrl'
                )

                ->distinct()
                ->where('products.outlet_id', $outletId)
                ->where('products.vendor_id', $vendorId)
                ->get();

                    

            $data=array();
            $i=1;
            if (count($categoryDetails)>0) {
                $data[0]['categoryName'] = "All";
                $data[0]['categoryId'] = 0;
                $data[0]['catUrl'] = "all";
                foreach ($categoryDetails as $index=> $value) {
                    $data[$i]['categoryName'] = $value->categoryName;
                    $data[$i]['categoryId'] = $value->categoryId;
                    $data[$i]['catUrl'] = $value->catUrl;
                    $i++;
                }
                    
   
                   
                $result = array("status" => 200, "message" => "success","categoryDetails"=>$data);
            }
        }

        return json_encode($result);
    }

    public function getOrderHistory_copy(Request $data)
    {
        $rules = [
            'language' => ['required'],
            
        ];

        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array( "status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            $outletId=$post_data['outletId'];
            $vendorId=$post_data['vendorId'];
            $orderId=$post_data['orderId'];
            $fromDate=$post_data['fromDate'];
            $todate=$post_data['todate'];
            $deliveryType=$post_data['deliveryType'];
            $pageSize=$post_data['pageSize'];
            $skipSize=$post_data['skipSize'];

            
            //$fleetDriverId=$post_data['fleetDriverId'];
            //$fleetSalesId=$post_data['fleetSalesId'];

            $condition = 'orders.outlet_id = '.$outletId.' and orders.vendor_id = '.$vendorId;
            $condition .= ' and orders.id = '.$orderId;
            
            
                                    
            if (($fromDate =="") and ($todate =="") and ($orderId =="")) {
                $completeOrders=DB::table('orders')
                            
                            ->join('orders_info', 'orders_info.order_id', '=', 'orders.id')
                            ->join('users', 'users.id', '=', 'orders.customer_id')
                            ->join('user_address', 'user_address.id', '=', 'orders.delivery_address')
                            ->join('outlets', 'outlets.id', '=', 'orders.outlet_id')
                            ->join('vendors', 'vendors.id', '=', 'orders.vendor_id')
                            ->select(
                                'orders.id',
                                'orders.outlet_id',
                                'orders.vendor_id',
                                'total_amount',
                                'orders_info.item_unit',
                                'orders_info.item_offer as orderDiscount',
                                'order_status as orderStatus',
                                'order_type as orderType',
                                'orders.created_date as orderDate',
                                'delivery_date as orderDeliveryDate',
                                'users.name as customerName',
                                'customer_id as customerId',
                                'user_address.address as customerAddress'
                            )

                               ->distinct()
                               ->where('orders.outlet_id', $outletId)
                              ->where('orders.vendor_id', $vendorId)
                              ->where('orders.order_status', 12)
                              ->orWhere('orders.order_status', 11)
                              ->orWhere('orders.order_status', 16)
                             ->limit($pageSize)
                             ->skip($skipSize)


                            ->get();

                //print_r($completeOrders);exit();


                $data=array();

                if (count($completeOrders)>0) {
                    foreach ($completeOrders as $index=> $value) {
                        $data[$index]['orderId'] = $value->id;
                        $data[$index]['outletId'] = $value->outlet_id;
                        $data[$index]['vendorId'] = $value->vendor_id;
                        $data[$index]['totalAmount'] = $value->total_amount;
                        $data[$index]['orderQuantity'] = $value->item_unit;
                        $data[$index]['orderDiscount'] = $value->orderDiscount;
                        $data[$index]['orderStatus'] = $value->orderStatus;
                        $data[$index]['orderType'] = $value->orderType;
                        $data[$index]['orderDate'] = $value->orderDate;
                        $data[$index]['orderDeliveryDate'] = $value->orderDeliveryDate;
                        $data[$index]['customerName'] =$value->customerName;
                        $data[$index]['customerId'] =$value->customerId;
                        $data[$index]['customerAddress'] =$value->customerAddress;
                        $data[$index]['deliveryType'] =1;
                        $data[$index]['fleetDriverId'] ="";
                        $data[$index]['fleetDriverName'] ="";
                        $data[$index]['fleetSalesId'] ="";
                        $data[$index]['fleetSalesName'] ="";
                        $data[$index]['paymentType'] ="COD";
                    }
                }

                $result = array("status" => 1, "message" => "orderHistoryList",'detail' => $data);
            } elseif ($orderId =="") {
                $completeOrders=DB::table('orders')
                            
                            ->join('orders_info', 'orders_info.order_id', '=', 'orders.id')
                            ->join('users', 'users.id', '=', 'orders.customer_id')
                            ->join('user_address', 'user_address.id', '=', 'orders.delivery_address')
                            ->join('outlets', 'outlets.id', '=', 'orders.outlet_id')
                            ->join('vendors', 'vendors.id', '=', 'orders.vendor_id')
                            ->select(
                                'orders.id',
                                'orders.outlet_id',
                                'orders.vendor_id',
                                'total_amount',
                                'orders_info.item_unit',
                                'orders_info.item_offer as orderDiscount',
                                'order_status as orderStatus',
                                'order_type as orderType',
                                'orders.created_date as orderDate',
                                'delivery_date as orderDeliveryDate',
                                'users.name as customerName',
                                'customer_id as customerId',
                                'user_address.address as customerAddress'
                            )

                              ->distinct()
                              ->where('orders.outlet_id', $outletId)
                              ->where('orders.vendor_id', $vendorId)
                              ->where('orders.order_status', 12)
                              ->orWhere('orders.order_status', 11)
                              ->orWhere('orders.order_status', 16)
                              ->whereBetween('orders.delivery_date', [$fromDate, $todate])
                             ->limit($pageSize)
                             ->skip($skipSize)


                            ->get();

                //print_r($completeOrders);exit();


                $data=array();

                if (count($completeOrders)>0) {
                    foreach ($completeOrders as $index=> $value) {
                        $data[$index]['orderId'] = $value->id;
                        $data[$index]['outletId'] = $value->outlet_id;
                        $data[$index]['vendorId'] = $value->vendor_id;
                        $data[$index]['totalAmount'] = $value->total_amount;
                        $data[$index]['orderQuantity'] = $value->item_unit;
                        $data[$index]['orderDiscount'] = $value->orderDiscount;
                        $data[$index]['orderStatus'] = $value->orderStatus;
                        $data[$index]['orderType'] = $value->orderType;
                        $data[$index]['orderDate'] = $value->orderDate;
                        $data[$index]['orderDeliveryDate'] = $value->orderDeliveryDate;
                        $data[$index]['customerName'] =$value->customerName;
                        $data[$index]['customerId'] =$value->customerId;
                        $data[$index]['customerAddress'] =$value->customerAddress;
                        $data[$index]['deliveryType'] =1;
                        $data[$index]['fleetDriverId'] ="";
                        $data[$index]['fleetDriverName'] ="";
                        $data[$index]['fleetSalesId'] ="";
                        $data[$index]['fleetSalesName'] ="";
                        $data[$index]['paymentType'] ="COD";
                    }
                }

                $result = array("status" => 1, "message" => "orderHistoryList",'detail' => $data);
            } elseif (($fromDate =="") and ($todate =="")) {
                $completeOrders=DB::table('orders')
                            
                            ->join('orders_info', 'orders_info.order_id', '=', 'orders.id')
                            ->join('users', 'users.id', '=', 'orders.customer_id')
                            ->join('user_address', 'user_address.id', '=', 'orders.delivery_address')
                            ->join('outlets', 'outlets.id', '=', 'orders.outlet_id')
                            ->join('vendors', 'vendors.id', '=', 'orders.vendor_id')
                            ->select(
                                'orders.id',
                                'orders.outlet_id',
                                'orders.vendor_id',
                                'total_amount',
                                'orders_info.item_unit',
                                'orders_info.item_offer as orderDiscount',
                                'order_status as orderStatus',
                                'order_type as orderType',
                                'orders.created_date as orderDate',
                                'delivery_date as orderDeliveryDate',
                                'users.name as customerName',
                                'customer_id as customerId',
                                'user_address.address as customerAddress'
                            )

                              ->distinct()
                              ->where('orders.outlet_id', $outletId)
                              ->where('orders.vendor_id', $vendorId)
                              ->where('orders.id', $orderId)
                              ->where('orders.order_status', 12)
                              ->orWhere('orders.order_status', 11)
                              ->orWhere('orders.order_status', 16)
                              ->limit($pageSize)
                              ->skip($skipSize)


                            ->get();

                //print_r($completeOrders);exit();


                $data=array();

                if (count($completeOrders)>0) {
                    foreach ($completeOrders as $index=> $value) {
                        $data[$index]['orderId'] = $value->id;
                        $data[$index]['outletId'] = $value->outlet_id;
                        $data[$index]['vendorId'] = $value->vendor_id;
                        $data[$index]['totalAmount'] = $value->total_amount;
                        $data[$index]['orderQuantity'] = $value->item_unit;
                        $data[$index]['orderDiscount'] = $value->orderDiscount;
                        $data[$index]['orderStatus'] = $value->orderStatus;
                        $data[$index]['orderType'] = $value->orderType;
                        $data[$index]['orderDate'] = $value->orderDate;
                        $data[$index]['orderDeliveryDate'] = $value->orderDeliveryDate;
                        $data[$index]['customerName'] =$value->customerName;
                        $data[$index]['customerId'] =$value->customerId;
                        $data[$index]['customerAddress'] =$value->customerAddress;
                        $data[$index]['deliveryType'] =1;
                        $data[$index]['fleetDriverId'] ="";
                        $data[$index]['fleetDriverName'] ="";
                        $data[$index]['fleetSalesId'] ="";
                        $data[$index]['fleetSalesName'] ="";
                        $data[$index]['paymentType'] ="COD";
                    }
                }

                $result = array("status" => 1, "message" => "orderHistoryList",'detail' => $data);
            } else {
                $completeOrders=DB::table('orders')
                            
                            ->join('orders_info', 'orders_info.order_id', '=', 'orders.id')
                            ->join('users', 'users.id', '=', 'orders.customer_id')
                            ->join('user_address', 'user_address.id', '=', 'orders.delivery_address')
                            ->join('outlets', 'outlets.id', '=', 'orders.outlet_id')
                            ->join('vendors', 'vendors.id', '=', 'orders.vendor_id')
                            ->select(
                                'orders.id',
                                'orders.outlet_id',
                                'orders.vendor_id',
                                'total_amount',
                                'orders_info.item_unit',
                                'orders_info.item_offer as orderDiscount',
                                'order_status as orderStatus',
                                'order_type as orderType',
                                'orders.created_date as orderDate',
                                'delivery_date as orderDeliveryDate',
                                'users.name as customerName',
                                'customer_id as customerId',
                                'user_address.address as customerAddress'
                            )

                             ->distinct()
                             ->where('orders.outlet_id', $outletId)
                             ->where('orders.vendor_id', $vendorId)
                             ->where('orders.id', $orderId)
                             ->where('orders.order_status', 12)
                             ->whereBetween('orders.delivery_date', [$fromDate, $todate])
                             ->limit($pageSize)
                             ->skip($skipSize)

                            ->get();

                //print_r($completeOrders);exit();


                $data=array();

                if (count($completeOrders)>0) {
                    foreach ($completeOrders as $index=> $value) {
                        $data[$index]['orderId'] = $value->id;
                        $data[$index]['outletId'] = $value->outlet_id;
                        $data[$index]['vendorId'] = $value->vendor_id;
                        $data[$index]['totalAmount'] = $value->total_amount;
                        $data[$index]['orderQuantity'] = $value->item_unit;
                        $data[$index]['orderDiscount'] = $value->orderDiscount;
                        $data[$index]['orderStatus'] = $value->orderStatus;
                        $data[$index]['orderType'] = $value->orderType;
                        $data[$index]['orderDate'] = $value->orderDate;
                        $data[$index]['orderDeliveryDate'] = $value->orderDeliveryDate;
                        $data[$index]['customerName'] =$value->customerName;
                        $data[$index]['customerId'] =$value->customerId;
                        $data[$index]['customerAddress'] =$value->customerAddress;
                        $data[$index]['deliveryType'] =1;
                        $data[$index]['fleetDriverId'] ="";
                        $data[$index]['fleetDriverName'] ="";
                        $data[$index]['fleetSalesId'] ="";
                        $data[$index]['fleetSalesName'] ="";
                        $data[$index]['paymentType'] ="COD";
                    }
                }

                $result = array("status" => 1, "message" => "orderHistoryList",'detail' => $data);
                // $result = array("status" => 200, "message" => "Success"/*,"pageSize"=>$completeOrders->count()*/,'orderHistory' => $data);
            }
        }

        return json_encode($result);
    }


    public function getOrderHistory(Request $data)
    {
        $rules = [
            'language' => ['required'],
            
        ];

        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array( "status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            $outletId=$post_data['outletId'];
            $vendorId=$post_data['vendorId'];
            $orderId=$post_data['orderId'];
            $fromDate=$post_data['fromDate'];
            $todate=$post_data['todate'];
            $deliveryType=$post_data['deliveryType'];
            $pageSize=$post_data['pageSize'];
            $skipSize=$post_data['skipSize'];

            //(order_status == 11,12,16);
            if (($fromDate =="") and ($todate =="") and ($orderId =="")) {
                $condition = 'orders.outlet_id = '.$outletId;
                $condition .= ' and orders.vendor_id = '.$vendorId;
               $condition .= ' and orders.order_status !='. 1 . ' and orders.order_status !='. 10 .' and orders.order_status !='. 18 .' and orders.order_status !='. 19 .' and orders.order_status !='. 14 .' and orders.order_status !='. 31 .' and orders.order_status !='. 32 .' and orders.order_status !='. 12;
               // $condition .= ' and orders.order_status = 33';
            } elseif ($orderId =="") {
                $condition = 'orders.outlet_id = '.$outletId;
                $condition .= ' and orders.vendor_id = '.$vendorId;
                $condition .= ' and orders.order_status !='. 1 . ' and orders.order_status !='. 10 .' and orders.order_status !='. 18 .' and orders.order_status !='. 19 .' and orders.order_status !='. 14 .' and orders.order_status !='. 31 .' and orders.order_status !='. 32 .' and orders.order_status !='. 12;
                 //$condition .= ' and orders.order_status = 33';

                $condition .=" and orders.created_date BETWEEN '".$fromDate."' and '".$todate."'";
            } elseif (($fromDate =="") and ($todate =="")) {
                $condition = 'orders.outlet_id = '.$outletId;
                $condition .= ' and orders.vendor_id = '.$vendorId;
                $condition .= ' and orders.order_status !='. 1 . ' and orders.order_status !='. 10 .' and orders.order_status !='. 18 .' and orders.order_status !='. 19 .' and orders.order_status !='. 14 .' and orders.order_status !='. 31 .' and orders.order_status !='. 32 .' and orders.order_status !='. 12;
                
               // $condition .= ' and orders.order_status = 33';


                $condition .= ' and orders.id = '.$orderId;
            } elseif (($fromDate !=="") and ($todate !=="") and ($orderId !=="")) {
                $condition = 'orders.outlet_id = '.$outletId;
                $condition .= ' and orders.vendor_id = '.$vendorId;
                $condition .= ' and orders.order_status !='. 1 . ' and orders.order_status !='. 10 .' and orders.order_status !='. 18 .' and orders.order_status !='. 19 .' and orders.order_status !='. 14 .' and orders.order_status !='. 31 .' and orders.order_status !='. 32 .' and orders.order_status !='. 12;
                //$condition .= ' and orders.order_status = 33';

                $condition .= ' and orders.id = '.$orderId;
                $condition .=" and orders.created_date BETWEEN '".$fromDate."' and '".$todate."'";
            }
                    $completeOrders=DB::table('orders')                        
                            ->join('users', 'users.id', '=', 'orders.customer_id')
                            ->join('user_address', 'user_address.id', '=', 'orders.delivery_address')
                            ->join('outlets', 'outlets.id', '=', 'orders.outlet_id')
                            ->join('vendors', 'vendors.id', '=', 'orders.vendor_id')
                            ->select(
                                'orders.id',
                                'orders.outlet_id',
                                'orders.vendor_id',
                                'total_amount',
                                'order_status as orderStatus',
                                'order_type as orderType',
                                'orders.created_date as orderDate',
                                'orders.delivery_date as orderDeliveryDate',
                                'users.name as customerName',
                                'customer_id as customerId',
                                'user_address.address as customerAddress'
                                    )

                            ->distinct()
                            ->whereRaw($condition)
                            ->orderBy('orderDeliveryDate', 'desc')
                            ->limit($pageSize)
                            ->skip($skipSize)
                            ->get();



                $data=array();

                if (count($completeOrders)>0) 
                {
                    foreach ($completeOrders as $index=> $value) 
                    {
                        $data[$index]['orderId'] = $value->id;
                        $data[$index]['outletId'] = $value->outlet_id;
                        $data[$index]['vendorId'] = $value->vendor_id;
                        $data[$index]['totalAmount'] = $value->total_amount;

                        $order_info=DB::select("select SUM(item_unit) as item_count , SUM(item_offer) as item_offer from orders_info where order_id = $value->id ");
                            if (count($order_info)>0) 
                            {
                                $orderInfoArray=array();

                                foreach ($order_info as $keys => $values)
                                {

                                    $orderInfoArray[$keys]['itemCount']= $values->item_count;
                                    $orderInfoArray[$keys]['orderDiscount']= $values->item_offer;

                                }

                            }
      
                        $data[$index]['orderQuantity'] = $values->item_count;
                        $data[$index]['orderDiscount'] = $values->item_offer;
                        $data[$index]['orderStatus'] = $value->orderStatus;
                        $data[$index]['orderType'] = $value->orderType;
                        $data[$index]['orderDate'] = $value->orderDate;
                        $data[$index]['orderDeliveryDate'] = $value->orderDeliveryDate;
                        $data[$index]['customerName'] =$value->customerName;
                        $data[$index]['customerId'] =$value->customerId;
                        $data[$index]['customerAddress'] =$value->customerAddress;
                        $data[$index]['deliveryType'] =1;
                        $data[$index]['fleetDriverId'] ="";
                        $data[$index]['fleetDriverName'] ="";
                        $data[$index]['fleetSalesId'] ="";
                        $data[$index]['fleetSalesName'] ="";
                        $data[$index]['paymentType'] ="COD";
                    }
                }

            $result = array("status" => 1, "message" => "orderHistoryList",'detail' => $data);
        }

        return json_encode($result);
    }

    
    
      public function orderHistoryItemDetails(Request $data)
    {
        $rules = [
            'language' => ['required'],
            'orderId' => ['required'],
            
        ];

        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 400, "message" => trans("messages.Error List"), "detail" => $errors);
        }


        //print_r($post_data);exit();

        else {
            $orderId = $post_data['orderId'];

            $pageSize = $post_data['pageSize'];
            $skipSize = $post_data['skipSize'];

            $orderDetails = DB::table('orders')


                   -> join('orders_info', 'orders_info.order_id', '=', 'orders.id')
                   -> join('products', 'products.id', '=', 'orders_info.item_id')

                   -> join('user_address', 'user_address.user_id', '=', 'orders.customer_id')
                   -> join('weight_classes_infos', 'weight_classes_infos.id', '=', 'products.weight_class_id')
                   -> join('products_infos', 'products_infos.id', '=', 'products.id')
                   -> join('payment_gateways_info', 'payment_gateways_info.payment_id', '=', 'orders.payment_gateway_id')

                   -> join('outlets', 'outlets.vendor_id', '=', 'orders.vendor_id')
                   -> join('users', 'users.id', '=', 'orders.customer_id')
                   -> join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
                   -> join('vendors', 'vendors.id', '=', 'orders.vendor_id')
                   -> join('order_status', 'order_status.id', '=', 'orders.order_status')
                   
                
                ->select(
                    'products.product_image as productImage',
                    'products.description as description',
                    'orders_info.item_id as productId',
                    'orders_info.item_cost as discountPrice',
                    'orders.total_amount as totalAmount',
                    'orders.delivery_charge as deliveryCharge',
                    'orders.service_tax as serviceTax',
                    'orders.id as orderId',
                    'orders.invoice_id as invoiceId',
                    'orders.coupon_amount as couponAmount',
                    'orders.order_key_formated as orderKeyFormated',
                   'products.weight as weight',
                    'weight_classes_infos.title as title',
                    'weight_classes_infos.unit as unitCode',
                   'products_infos.product_name as productName',
                    'orders.driver_ids as driverId',
                    'orders.delivery_instructions as deliveryInstructions',
                    'orders.payment_gateway_id as paymentGatewayId',
                    'user_address.address as userContactAddress',
                    'user_address.latitude as userLatitude',
                    'user_address.longitude as userLongitude',
                    'payment_gateways_info.name as name',
                    'orders.created_date as createdDate',
                    'orders.delivery_date as deliveryDate',
                    'orders.order_type as orderType',
                    'orders.customer_id as userId',
                    'outlets.latitude as outletLatitude',
                    'outlets.longitude as outletlongitude',
                    'orders.coupon_amount as couponAmount',
                    'users.email as email',
                    'outlet_infos.outlet_name as outletName ',
                    'vendors.logo_image as vendorLogo ',
                    'outlet_infos.contact_address as outletAddress ',
                    'outlets.contact_email as contactEmail ',
                    'orders.order_status as orderStatus ',
                    'order_status.name as orderStatusName ',
                    'orders.outlet_id as outletId ',
                    'orders.vendor_id as vendorId '
                    // DB::raw(count('item_unit') as 'orderQuantity')
                )
                ->distinct()
                ->where('orders.id', $orderId)
                ->orderBy('orders.delivery_date', 'desc')
                ->limit($pageSize)
                ->skip($skipSize)
                ->get();

            //print_r($orderDetails);exit();

            $data=array();

            if (count($orderDetails)>0) {
                foreach ($orderDetails as $index=> $value) {
                    $product_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $value->productImage) && $value->productImage != '') {
                        $product_image = url('/assets/admin/base/images/products/list/' . $value->productImage);
                    }
                    $data[$index]['productImage'] = $product_image;
                    $data[$index]['description'] = $value->description;
                    $data[$index]['productId'] = $value->productId;
                    $data[$index]['discountPrice'] = $value->discountPrice;
                    $data[$index]['totalAmount'] = $value->totalAmount;
                    $data[$index]['deliveryCharge'] = $value->deliveryCharge;
                    $data[$index]['serviceTax'] = $value->serviceTax;
                    $data[$index]['orderId'] = $value->orderId;
                    $data[$index]['invoiceId'] = $value->invoiceId;
                    $data[$index]['couponAmount'] = $value->couponAmount;
                    $data[$index]['orderKeyFormated'] = $value->orderKeyFormated;
                   $data[$index]['weight'] = $value->weight;
                  $data[$index]['title'] = $value->title;
                   $data[$index]['unitCode'] = $value->unitCode;
                   $data[$index]['productName'] = $value->productName;

            
                    $deliveryDetails=array();
                    $deliveryDetails['driverId'] = $value->driverId;
                    $deliveryDetails['deliveryInstructions'] = $value->deliveryInstructions;
                    $deliveryDetails['paymentGatewayId'] = $value->paymentGatewayId;
                    $deliveryDetails['userContactAddress'] = $value->userContactAddress;
                    $deliveryDetails['totalAmount'] = $value->totalAmount;
                    $deliveryDetails['deliveryCharge'] = $value->deliveryCharge;
                    $deliveryDetails['serviceTax'] = $value->serviceTax;
                    $deliveryDetails['userLatitude'] = $value->userLatitude;
                    $deliveryDetails['userLongitude'] = $value->userLongitude;
                    $deliveryDetails['name'] = $value->name;
                    $deliveryDetails['deliveryDate'] = $value->deliveryDate;
                    $deliveryDetails['orderType'] = $value->orderType;
                    $deliveryDetails['userId'] = $value->userId;
                    $deliveryDetails['outletLatitude'] = $value->outletLatitude;
                    $deliveryDetails['outletlongitude'] = $value->outletlongitude;
                    $deliveryDetails['couponAmount'] = $value->couponAmount;
                    $deliveryDetails['email'] = $value->email;

                    // $total=0;
                    // $total+=$value->totalAmount;
                    // $total+=$value->deliveryCharge;
                    // $total+=$value->serviceTax;

                    $sum= DB::select("select   sum(item_cost) as total  from orders_info where order_id = $value->orderId ");

                    if (count($sum)>0) {

                        $sumArray=array();

                    foreach ($sum as $ke => $valu) {

                            $sumArray[$ke]['total']= $valu->total;

                            }

                     }

                     //print_r($valu->total);exit();

                   
                    $deliveryDetails['subTotal'] =$valu->total;
                   
                 
 
                    $orderData=array();
                    $vendorLogo = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $value->vendorLogo) && $value->vendorLogo != '') {
                        $vendorLogo = url('/assets/admin/base/images/vendors/logos/' . $value->vendorLogo);
                    }

                    $orderData['orderId'] = $value->orderId;
                    $orderData['outletName'] = $value->outletName;
                    $orderData['vendorLogo'] = $vendorLogo;
                    $orderData['outletAddress'] = $value->outletAddress;
                    $orderData['contactEmail'] = $value->contactEmail;
                    $orderData['orderStatus'] = $value->orderStatus;
                    $orderData['orderStatusName'] = $value->orderStatusName;
                    $orderData['paymentGatewayName'] = $value->name;
                    $orderData['outletId'] = $value->outletId;
                    $orderData['vendorId'] = $value->vendorId;
                    $orderData['orderKeyFormated'] = $value->orderKeyFormated;
                    $orderData['invoiceId'] = $value->invoiceId;
                    $orderData['deliveryAddress'] = $value->userContactAddress;
                    $orderData['createdDate'] = $value->createdDate;

       
                    /* $return_reasons=array("return_reasons": {
                       "9": "Dead on arrival",
                       "10": "Faulty, please supply details",
                       "11": "Order error",
                       "12": "Other, please supply details",
                       "13": "Received wrong item",
                       "14": "Others"
                     });*/




                    $result = array("status" => 200, "message" => "success","orderProductList"=>$data,"deliveryDetails"=>$deliveryDetails,"orderData"=>$orderData/*,"return_reasons"=>$return_reasons*/);
                }
            }
        }
        return json_encode($result);
    }

    //Ram : 12/09/2019

    
    /*public function outletOrders(Request $data)
    {
        $rules = [
            'language' => ['required'],
            'outletId' => ['required'],
            'type' => ['required'],
            
        ];

        $post_data = $data->all();

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 400, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            $outletId = $post_data['outletId'];
            $type = $post_data['type'];

            if ($type==1) {
                $raw = 'orders.outlet_id = '.$outletId ;
                $raw .= 'and orders.order_status ='. 1;
                $raw.=" and orders.created_date <'".date('Y-m-d H:i:s')."'";
            }
            if ($type==2) {
                $raw ='orders.outlet_id= '.$outletId ;
                $raw .= 'and orders.order_status !='. 1;
                $raw .= 'and orders.order_status !='. 19;
                $raw .= 'and orders.order_status !='. 12;
                $raw .= 'and orders.order_status !='. 11;
                $raw .= 'and orders.order_status !='. 14;
                $raw .= 'and orders.order_status !='. 31;
                $raw .= 'and orders.order_status !='. 32;
                $raw .= 'and orders.order_status ='. 10;
                 //$raw .= 'and orders.driver_ids ='. "";
                $raw.=" and orders.created_date <'".date('Y-m-d H:i:s')."'";
            }

            if ($type==3) {
                $raw = 'orders.outlet_id = '.$outletId ;
                $raw .= 'and orders.order_status ='. 19;
                $raw.=" and orders.created_date <'".date('Y-m-d H:i:s')."'";
            }
        

            $outletOrders=DB::table('orders')
                            ->join('order_status', 'order_status.id', '=', 'orders.order_status')
                            ->join('users', 'users.id', '=', 'orders.customer_id')
                            ->join('orders_info', 'orders_info.order_id', '=', 'orders.id')
                            //->join('salesperson', 'salesperson.id', '=', 'orders.driver_ids')
                            ->select(
                                'orders.id as orderId',
                                'orders.customer_id as customerId',
                                'orders.outlet_id as outletId',
                                'orders.vendor_id as vendorId',
                                'orders.order_key as orderKey',
                                'orders.order_key_formated as orderKeyFormated',
                                'orders.order_comments as orderComments',
                                'orders.order_status as orderStatus',
                                'order_status.name as statusName',
                                'orders.created_date as createdDate',
                                'users.name as customerName',
                                'orders.total_amount as totalAmount',
                                // 'salesperson.id as salesDriverId',
                                // 'salesperson.first_name as salesDriverName',
                                'orders_info.item_unit as orderQuantity'
                            )
                            ->distinct()
                            ->whereRaw($raw)
                            ->get();

             $salesperson=DB::table('salesperson')
                            ->select('salesperson.id as salesDriverId','salesperson.name as salesDriverName')
  
                            ->where('salesperson.status','=','F')
                            ->get();
                         
                      //print_r($salesperson)  ;exit(); 
            $count = count($outletOrders);
            $data=array();
            foreach ($outletOrders as $key => $value) {
                $data[$key]['orderId']=$value->orderId;
                $data[$key]['customerId']=$value->customerId;
                $data[$key]['outletId']=$value->outletId;
                $data[$key]['vendorId']=$value->vendorId;
                $data[$key]['orderKey']=$value->orderKey;
                $data[$key]['orderKeyFormated']=$value->orderKeyFormated;
                $data[$key]['orderComments']=$value->orderComments;
                $data[$key]['orderStatus']=$value->orderStatus;
                $data[$key]['createdDate']=$value->createdDate;
                $data[$key]['customerName']=$value->customerName;
                $data[$key]['totalAmount']=$value->totalAmount;
                $data[$key]['orderQuantity']=$value->orderQuantity;
                $data[$key]['salesFleetId']="";
                $data[$key]['salesFleetName']="";

               
            }

            $datas=array();
            foreach ($salesperson as $key => $value) {
                $datas[$key]['salesDriverId']=$value->salesDriverId;
                $datas[$key]['salesDriverName']=$value->salesDriverName;

            }

            $result = array("status" => 1, "message" => "outletOrdersList","count"=>$count,"detail"=>$data,"availableSalesPerson"=>$datas);
        }
        return json_encode($result);
    }
*/


    public function orderStatusUpdate(Request $data) {
        //print_r("expression");exit();
        $post_data = $data->all();
        $affected = DB::update('update orders set order_status = ?,order_comments = ? where id = ?', array($post_data['order_status_id'], $post_data['comment'], $post_data['order_id']));

         $date=date("Y-m-d ");

        
        $affected = DB::update('update orders_log set order_status=?, order_comments = ? where id = (select max(id) from orders_log where order_id = ' . $post_data['order_id'] . ')', array($post_data['order_status_id'], $post_data['comment']));

         $rules = [
        //  //'userId' => ['required'],
        //  'orderId' => ['required'],
         ];
        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 2, "httpCode" => 400, "Error" => trans("messages.Error List"), "message" => $errors);
        } else {
            
                

                $orderId = $post_data['order_id'];
                $comment = isset($post_data['comment']) ? $post_data['comment'] : '';

                
                $affected = DB::update('update orders set request_vendor = 0 where id = ?', array($post_data['order_id']));

                
                $notifys = DB::table('orders')
                    ->select('orders.assigned_time', 'users.android_device_token', 'users.ios_device_token','users.id as customerId ','users.login_type', 'users.first_name', 'vendors_infos.vendor_name','vendors.id as vendorId','orders.total_amount','outlets.id as outletId','outlet_infos.outlet_name','orders.driver_ids','orders.salesperson_id')
                    ->Join('users', 'users.id', '=', 'orders.customer_id')
                    ->Join('vendors_infos', 'vendors_infos.id', '=', 'orders.vendor_id')
                    ->Join('vendors', 'vendors.id', '=', 'orders.vendor_id')
                    ->Join('outlets', 'outlets.id', '=', 'orders.outlet_id')
                    ->Join('outlet_infos','outlet_infos.id', '=', 'orders.outlet_id')
                    ->where('orders.id', '=', (int) $orderId)
                    ->get();
             //  print_r($notifys[0]);exit;
                    if($post_data['order_status_id'] == 18){
                    $updateStatus=DB::table('salesperson')
                                ->where('salesperson.id', $notifys[0]->salesperson_id)                             
                                ->update(['status' =>'F']);


                   
                            }

                 if($post_data['order_status_id'] == 12){
                    $updateStatus=DB::table('orders')
                                ->where('orders.id', $orderId)
                               
                                ->update(['delivery_date' => $date]);
                            }






                
                $result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.Order Status updated successfully"));
            
            
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }


     public function mproductSearchNew(Request $data)
    {
        $rules = [

            'productName' => ['required'],
            'language' => ['required'],
            'outletId' => ['required'],
            'vendorId' => ['required'],

        ];

        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            // $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result =  array("status" => 0, "Error" => trans("messages.Error List"), "message" => $errors);
        } else {
            $store_key = $post_data['productName'];

            //print_r($store_key);exit;
            $language_id = $post_data['language'];
            $outletId = $post_data['outletId'];
            $vendorId = $post_data['vendorId'];
            $keyword = isset($post_data['keyword']) ? $post_data['keyword'] : '';
            $data = array();
            $result = array("status" => 2, "data" => $data);
            $condtion = " products.active_status = 1";
            if ($store_key) {
                $condtion .= "and products_infos.product_name ILIKE '%" . $store_key . "%'";
            }

            //print_r($category_url);exit;

            $products = Products::join('products_infos', 'products.id', '=', 'products_infos.id')
                ->join('categories', 'categories.id', '=', 'products.category_id')
                ->join('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
                ->join('weight_classes', 'weight_classes.id', '=', 'products.weight_class_id')
                ->join('weight_classes_infos', 'weight_classes_infos.id', '=', 'weight_classes.id')
                ->join('outlets', 'outlets.id', '=', 'products.outlet_id')
                ->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
                // ->join('vendors_infos', 'outlet_id', '=', 'outlets.id')

          
                ->select(
                    'product_url as productUrl',
                    'product_name as productName',
                    'vendor_category_id as categoryId',
                    'products.product_image',
                    'products.id as productId',
                    'products.product_info_image',
                    'products.product_zoom_image',
                    'original_price as originalPrice',
                    'discount_price as discountPrice',
                    'products_infos.description',
                    'products.weight',
                    'categories_infos.category_name as categoryName',
                    'weight_classes_infos.unit',
                    'weight_classes_infos.title',
                    'products.category_id as categoryId',
                    'outlets.id as outletId',
                    'outlets.vendor_id as vendorId',
                    'outlet_infos.outlet_name as outletName',
                    // 'vendors_infos.vendor_name as vendorName',
                    'categories.url_key as urlKey',
                    'categories.url_key as catUrl'
                )

           
                 ->distinct()

                ->where('outlets.id', '=', $outletId)
                ->where('outlets.vendor_id', '=', $vendorId)
                ->where('products_infos.product_name', 'ILIKE', "%{$store_key}%")
                ->get();

           
            $currency_symbol = getCurrency($language_id);
            $currency_side = getCurrencyPosition()->currency_side;
            $data=array();

           // $p = 0;
            foreach($products as $pro => $value) {

                $data[$pro]['productId'] = $value->productId;
                $data[$pro]['productName'] = $value->productName;
                $data[$pro]['productUrl'] = $value->productUrl;
                $data[$pro]['outletId'] = $value->outletId;
                $data[$pro]['vendorId'] = $value->vendorId;
                // $data[$pro]['outletName'] = $value->outletName;
                // $data[$pro]['vendorName'] = $value->vendorName;
                
                
               
                if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $value->product_image) && $value->product_image != '') {
                    $product_image = url('/assets/admin/base/images/products/list/' . $value->product_image);
                }

                $data[$pro]['productImage'] = $product_image;               
                
            }
            $categoryDetails=array();

            foreach($products as $key => $value) {

                $categoryDetails[$key]['categoryId'] = $value->categoryId;
                $categoryDetails[$key]['categoryName'] = $value->categoryName;
                $categoryDetails[$key]['catUrl'] = $value->catUrl;
                
            }

            $result = array("status" => 1, "message" => "Success", 'productList' => $data,'categoryDetails' => $categoryDetails);

           
        }
        return json_encode($result);

        
    }



     public function mbarcode(Request $data)
    {
        $rules = [

            'barcode' => ['required'],
            'language' => ['required'],
           

        ];

        $post_data = $data->all();

        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result =  array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            $barcode = $post_data['barcode'];

            $language_id = $post_data['language'];



            $products = Products::join('products_infos', 'products.id', '=', 'products_infos.id')
                ->join('categories', 'categories.id', '=', 'products.category_id')
                ->join('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
                ->join('weight_classes', 'weight_classes.id', '=', 'products.weight_class_id')
                ->join('weight_classes_infos', 'weight_classes_infos.id', '=', 'weight_classes.id')
                ->join('outlets', 'outlets.id', '=', 'products.outlet_id')
                ->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
                // ->join('vendors_infos', 'outlet_id', '=', 'outlets.id')

          
                ->select(
                    'product_url as productUrl',
                    'product_name as productName',
                    'vendor_category_id as categoryId',
                    'products.product_image',
                    'products.id as productId',
                    'products.product_info_image',
                    'products.product_zoom_image',
                    'original_price as originalPrice',
                    'discount_price as discountPrice',
                    'products_infos.description',
                    'products.weight',
                    'categories_infos.category_name as categoryName',
                    'weight_classes_infos.unit',
                    'weight_classes_infos.title',
                    'products.category_id as categoryId',
                    'outlets.id as outletId',
                    'outlets.vendor_id as vendorId',
                    'outlet_infos.outlet_name as outletName',
                    // 'vendors_infos.vendor_name as vendorName',
                    'categories.url_key as catUrl'
                )

           
                 ->distinct()

                ->where('products.barcode', '=', $barcode)
                
                ->get();

           //print_r($products);exit();

            
            $data=array();

            foreach($products as $pro => $value) {

                $data['productId'] = $value->productId;
                $data['productName'] = $value->productName;
                $data['productUrl'] = $value->productUrl;
                $data['categoryId'] = $value->categoryId;
                $data['originalPrice'] = $value->originalPrice;
                $data['discountPrice'] = $value->discountPrice;
                $data['description'] = $value->description;
                $data['weight'] = $value->weight;
                $data['outletId'] = $value->outletId;
                $data['vendorId'] = $value->vendorId;
                $data['outletName'] = $value->outletName;
                $data['categoryName'] = $value->categoryName;
                $data['unit'] = $value->unit;
                $data['categoryUrl'] = $value->catUrl;
                
                
                $product_image = URL::asset('assets/front/' . Session::get('general')->theme . '/images/no_image.png?' . time());

                if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $value->product_image) && $value->product_image != '') {
                    $product_image = url('/assets/admin/base/images/products/list/' . $value->product_image);
                }

                $data['productImage'] = $product_image;               
                
            }
            
            if($data){
            $result = array("status" => 1, "message" => trans("messages.Success"),"availability"=>1, 'productList' => $data);
            }else{

            $result = array("status" => 1, "message" => "Success","availability"=>0/*, 'productList' => array()*/);
            }
        return json_encode($result);
        }

        
    }



    public function barcodeUpdate (Request $data){

        //print_r("expression");exit();

        $update=DB::table('products')
                ->where('product_url','=', 'BIG BEN BEEF AMPALAYA')
                ->update(['barcode'=> 0]);

                if($update){

                    echo "inserted";
                }else{

                    echo "no";
                }

    }
}
