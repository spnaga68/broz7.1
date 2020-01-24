<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use DB;
use App\Model\contactus;
use App\Model\users;
use App\Model\settings;
use App\Model\emailsettings;
use App\Model\cms;
use Session;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use MetaTag;
use Mail;
use SEO;
use SEOMeta;
use OpenGraph;
use Twitter;
use App;
use Illuminate\Support\Facades\Input;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use GuzzleHttp\Client;
use Guzzle\Http\Exception\ServerErrorResponseException;
use App\Model\api;
use App\Model\stores;
use App\Model\Api_model;
use Socialite;
use App\Model\cart_info;
use App\Model\vendors;
use App\Model\vendors_infos;
use App\Model\outlets;
use App\Model\outlet_infos;
DB::enableQueryLog();

class Frontstore extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
		//$client = new GuzzleHttp\Client();
		$this->client = new Client([
			// Base URI is used with relative requests
			'base_uri' => url('/'),
            // 'base_uri' => url('http://127.0.0.1/'),
			// You can set any number of default request options.
			'timeout'  => 3000.0,
		]);
		//print_r($this->client);exit;
		$this->theme = Session::get("general")->theme;
    }
	public function store_list($city="",$location="",$category="",$current_long = '')//If curent long not empty means category act on latitue.
	{ 
		//echo $location;exit;
		$api = New Api;
		$categories = "";
		if($location == "" && $category == "")
		{
			$categories_det = head_categories_list_by_url($city);
			if(!isset($categories_det->id))
			{
				return Redirect::to('/');
			}
			else {
				$categories = $categories_det->id;
			}
			$city = '';
		}
		$city         = $city;
		$location     = $location;
		//~ $current_lat  = 29.776255;
		//~ $current_long = 48.002007100000014;
		$user_id      = Session::get('user_id');
		Session::put('location', $location);
		Session::put('city', $city);
		$current_lat = '';
		if( !empty($current_long) )
		{
			$current_lat  = decrypt($category);
			$current_long = decrypt($current_long);
            Session::put('current_lat',  $current_lat);
              Session::put('current_long',  $current_long);
			$category = '';
		}
        else if($category!=''){
              $categories_det = head_categories_list_by_url($category);
            if(count($categories_det) == 0)
            {
                Session::flash('message-failure', trans('messages.Invalid URL'));
                return Redirect::to('/')->send();
            }
            $categories = $categories_det->id;


          }
    else{
    Session::forget('current_lat');
    Session::forget('current_long');


    }
		$user_id      = Session::get('user_id');
		$category_url = $category; 
		
		$store_array  = array("city" => $city, "location" => $location,"language" => getCurrentLang(),"category_ids" =>$categories,"type" =>"web","category_url" =>$category_url,"user_id" =>$user_id,"current_lat" =>$current_lat,"current_long" => $current_long);
		$method       = "POST";
		$data         = array('form_params' => $store_array);
		$response     = $api->call_api($data,'api/store_list_ajax',$method);
		$store        = isset($response->response)?$response->response:'';
		SEOMeta::setTitle(Session::get("general_site")->site_name.' - ' .'Stores');
		SEOMeta::setDescription(Session::get("general_site")->site_name.' - ' .'Stores');
		SEOMeta::addKeyword(Session::get("general_site")->site_name.' - ' .'Stores');
		OpenGraph::setTitle(Session::get("general_site")->site_name.' - ' .'Stores');
		OpenGraph::setDescription(Session::get("general_site")->site_name.' - ' .'Stores');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name.' - ' .'Stores');
		Twitter::setSite(Session::get("general_site")->site_name.' - ' .'Stores');
		$categories_list = getCategoryLists(2);
		$banners = get_store_banner_list(1);
		return view('front.'.$this->theme.'.store_list')->with('store', $store)->with('categories', $categories_list)->with('category_id', $categories)->with('banners', $banners);
	} 
/* public function store_list($city="",$location="",$category="",$current_long = '')//If curent long not empty means category act on latitue.
	{ 
		$api = New Api;
		$categories = "";
		$description ="";
		if($location == "" && $category == "")
		{
			$categories_det = head_categories_list_by_url($city);
			if(!isset($categories_det->id))
			{
				return Redirect::to('/');
			}
			else {
				$categories = $categories_det->id;
				$description = $categories_det->description;
			}
			$city = '';
		}
		/**  if(!empty($category))
        {
			$categories_det = head_categories_list_by_url($category);
			if(count($categories_det) == 0)
			{
				Session::flash('message-failure', trans('messages.Invalid URL'));
				return Redirect::to('/')->send();
			}
			$categories = $categories_det->id;
		}
		$city         = $city;
		$location     = $location;
		$html = '';
		//~ $current_lat  = 29.776255;
		//~ $current_long = 48.002007100000014;
		$user_id      = Session::get('user_id');
		Session::put('location', $location);
		Session::put('city', $city);
		$current_lat = '';
		if( !empty($current_long) )
		{
			$current_lat  = decrypt($category);
			$current_long = decrypt($current_long);
			$category = '';
		}
	    $language = getCurrentLang();
	    $location_detail = Stores::get_location_detail($location, $language);
	    if(count($location_detail)>0){
		$location_id  = $location_detail->zone_id;
	}
		$user_id      = Session::get('user_id');
		$category_url = $category;
		$store_array  = array("city" => $city, "location" => $location,"language" => getCurrentLang(),"category_ids" =>$categories,"type" =>"web","category_url" =>$category_url,"user_id" =>$user_id,"current_lat" =>$current_lat,"current_long" => $current_long);
		// echo '<pre>';print_r($store_array);die;
		$method       = "POST";
		$data         = array('form_params' => $store_array);
		//print_r($store_array);exit;
		//$response     = $api->call_api($data,'api/store_list_ajax',$method);
		
		
        $post_data = $data;
        
        $category_url =  isset($post_data['category_url'])?$post_data['category_url']:'';
     
       /* $language = isset($language)?$language:'2';
        if($language == 2)
        {
            App::setLocale('ar');
        }
        else {
            App::setLocale('en');
        } 
      
        $current_lat  = isset($current_lat)?$current_lat:'';
        $current_long =  isset($current_long)?$current_long:'';
       
        $distance = 25*1000;
        $data   = array();
        $result = array("response" => array("httpCode" => 400, "status" => false, "data" =>$data));
        $query  = 'vendors_infos.lang_id = (case when (select count(vendors_infos.lang_id) as totalcount from vendors_infos where vendors_infos.lang_id = '.$language.' and vendors.id = vendors_infos.id) > 0 THEN '.$language.' ELSE 1 END)';
        $query1  = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount1 from outlet_infos where outlet_infos.language_id = '.$language.' and outlets.id = outlet_infos.id) > 0 THEN '.$language.' ELSE 1 END)';
        $condition = 'vendors.active_status = 1';
        $orderby   = 'vendors.id ASC';
        if(isset($city) && $city)
        {
           // $condition .=' and cities.url_index = '.$post_data['city'];
            $condition .=" and cities.url_index = '".$city."'";
        }
        if(isset($location) && $location)
        {
             $condition .=" and zones.url_index = '".$location."'";
        }
        if(isset($categories) && $categories)
        {
            $c_ids   = $categories;
            //$c_ids = "'".$c_ids."'"; 
            $c_ids   =  explode(",",$c_ids);
            $c_ids   =  implode($c_ids,"','");
            $c_ids   = "'".$c_ids."'"; 
            //echo $c_ids;exit;
            //$condition .= " and (regexp_split_to_array(category_ids,',')::integer[] @> '{".$c_ids."}'::integer[]  and category_ids !='')";
            $condition .= " and vendor_category_mapping.category in($c_ids)";
        }
        if(isset($post_data['keyword']) && $post_data['keyword'])
        {
            $keyword    = pg_escape_string($post_data['keyword']);
            $condition .= " and vendors_infos.vendor_name ILIKE '%".$keyword."%'";
        }
        if(isset($post_data['sortby']) && $post_data['sortby']=="delivery_time")
        {
            $orderby ='vendors_delivery_time '.$post_data['orderby'];
        }
        if(isset($post_data['sortby']) && $post_data['sortby']=="rating")
        {
            $orderby ='vendors_average_rating '.$post_data['orderby'];
        }
        
        if( empty($current_lat) && empty($current_long) )
        {  
			     $vendors = Vendors::join('vendors_infos','vendors_infos.id','=','vendors.id')
                    ->join('outlets','outlets.vendor_id','=','vendors.id')
                    ->join('outlet_infos','outlet_infos.id','=','outlets.id')
                    ->join('zones','zones.id','=','outlets.location_id')
                    ->join('cities','cities.id','=','outlets.city_id')
                    ->join('vendor_category_mapping','vendor_category_mapping.vendor_id','=','vendors.id')
                    //left join "vendor_category_mapping" on vendor_category_mapping.outlet_id = vendors.id
                    ->select('vendors.id as vendors_id','vendors_infos.vendor_name','vendors.first_name','vendors.last_name','vendors.featured_image','vendors.logo_image','vendors.delivery_time as vendors_delivery_time','vendors.category_ids','vendors.average_rating as vendors_average_rating','outlet_infos.contact_address','outlet_infos.outlet_name','outlets.id as outlets_id','outlets.vendor_id as outlets_vendors_id','outlet_infos.outlet_name','outlets.delivery_time as outlets_delivery_time','outlets.average_rating as outlets_average_rating','outlets.url_index','outlets.category_ids as outlets_category_ids')
                    ->whereRaw($query)
                    ->whereRaw($query1)
                    ->whereRaw($condition)
                    ->where('vendors.featured_vendor','=',1)
                    ->where('outlets.active_status','=','1')
                    ->orderByRaw($orderby)
                    ->get();
        }
        else 
        {  
            $query = 'vendors_infos.lang_id = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language. ' ELSE 1 END)';
            $query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
            $query2 = 'cities_infos.language_id = (case when (select count(cities_infos.language_id) as totalcount from cities_infos where cities_infos.language_id = '.$language.' and cities.id = cities_infos.id) > 0 THEN '.$language.' ELSE 1 END)';
            $query3 = 'zones_infos.language_id = (case when (select count(zones_infos.language_id) as totalcount from zones_infos where zones_infos.language_id = ' . $language . ' and zones.id = zones_infos.zone_id) > 0 THEN ' . $language . ' ELSE 1 END)';

            $vendors = DB::select("select vendors.logo_image,vendors.category_ids,outlets.delivery_charges_fixed,outlets.city_id,outlets.location_id,vendors.id as vendors_id,vendors.delivery_time as vendors_delivery_time,vendors.average_rating as vendors_average_rating,vendors.featured_image, outlet_infos.contact_address,outlets.vendor_id as outlets_vendors_id,outlets.id as outlets_id,outlet_infos.outlet_name,outlets.delivery_time as outlets_delivery_time,outlets.average_rating as outlets_average_rating,outlets.category_ids as outlets_category_ids,vendors_infos.vendor_name,vendors.first_name,outlets.url_index,vendors.last_name, outlets.delivery_charges_variation,outlets.minimum_order_amount,outlets.active_status,zones_infos.zone_name,cities_infos.city_name,earth_distance(ll_to_earth(".$current_lat.', '.$current_long."), ll_to_earth(outlets.latitude, outlets.longitude)) as distance  from  vendors  left join outlets on outlets.vendor_id =vendors.id left join outlet_infos on outlets.id = outlet_infos.id left Join cities  on cities.id = vendors.city_id left join cities_infos on cities_infos.id =vendors.city_id left join zones on zones.city_id =vendors.city_id left join zones_infos on zones_infos.zone_id =zones.id left join vendors_infos on vendors_infos.id = vendors.id where earth_box(ll_to_earth(".$current_lat.', '.$current_long.'), '.$distance.") @> ll_to_earth(outlets.latitude, outlets.longitude)and ".$query." and ".$query1." and ".$query2." and ".$query3." and outlets.active_status='1' and vendors.active_status=1 and vendors.is_verified='1' order by distance asc");
        }
        
        if(count($vendors))
        {
            $outlets_list = array();
            $i = 1;
            foreach($vendors as $key => $datas)
            {
                if($datas->outlets_id != 0)
                {
                    $outlets_list[$datas->vendors_id]['vendors_id']     = $datas->vendors_id;
                    $outlets_list[$datas->vendors_id]['vendor_name']    = $datas->vendor_name;
                    $outlets_list[$datas->vendors_id]['featured_image'] = $datas->featured_image;
                    $outlets_list[$datas->vendors_id]['logo_image']     = $datas->logo_image;
                    $outlets_list[$datas->vendors_id]['category_ids']   = $datas->category_ids;
                    $category_name = '';
                    $outlets_list[$datas->vendors_id]['category_ids'] = $category_name;
                    if(!empty($datas->category_ids))
                    {
                        $category_ids = geoutletCategoryLists(explode(',', $datas->category_ids),$language);
                        if( count( $category_ids ) > 0 ) {
                            foreach( $category_ids as $cat ) {
                                $category_name .= $cat->category_name.', ';
                            }
                        }
                        $outlets_list[$datas->vendors_id]['vendor_category'] = rtrim($category_name, ', ');
                    }
                    $outlets_list[$datas->vendors_id]['vendors_delivery_time']  = $datas->vendors_delivery_time;
                    $outlets_list[$datas->vendors_id]['vendors_average_rating'] = ($datas->vendors_average_rating==null)?0:$datas->vendors_average_rating;
                    $outlets_list[$datas->vendors_id]['outlets_id'] = $datas->outlets_id;
                    $outlets_list[$datas->vendors_id]['outlet_name'] = $datas->outlet_name; 
                    $distance_km = number_format($datas->distance/1000,1);
                    $outlets_list[$datas->vendors_id]['distance_km'] = $distance_km; 
                    $outlets_list[$datas->vendors_id]['outlets_category_ids'] = $datas->outlets_category_ids;
                    $outlets_list[$datas->vendors_id]['url_index']  = $datas->url_index;
                    $outlets_list[$datas->vendors_id]['outlets_delivery_time']      = $datas->outlets_delivery_time;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['vendor_name'] = $datas->vendor_name;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlets_id']  = $datas->outlets_id;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlet_name']  = $datas->outlet_name;
                    $outlets_list[$datas->vendors_id]['outlets'] [$datas->outlets_id]['outlets_category_ids']  = $datas->outlets_category_ids;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlets_vendors_id']     = $datas->outlets_vendors_id;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outletname']             = $datas->outlet_name;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['url_index']              = $datas->url_index ;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['contact_address']        = $datas->contact_address;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlets_delivery_time']  = $datas->outlets_delivery_time;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlets_average_rating'] = ($datas->outlets_average_rating==null)?0:$datas->outlets_average_rating;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['distance'] = $datas->distance;
                    $out_category_name = '';
                    $outlets_status = '';
                    $outlet_open_status = '';
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlets_category'] = $out_category_name;
                    if(is_numeric($datas->vendors_delivery_time))
                    $vendorhour =  intdiv($datas->vendors_delivery_time, 60).':'. ($datas->vendors_delivery_time % 60);
                    if(is_numeric($datas->outlets_delivery_time))
                    $outlethour =  intdiv($datas->outlets_delivery_time, 60).':'. ($datas->outlets_delivery_time % 60);
                    // print_r($distance_km);exit;
                    if(!empty($datas->outlets_category_ids))
                    {
                        $outlet_category_ids = geoutletCategoryLists(explode(',', $datas->outlets_category_ids),$language);
                        if( count( $outlet_category_ids ) > 0 ) {
                            foreach( $outlet_category_ids as $out_cat ) {
                                $out_category_name .= $out_cat->category_name.', ';
                              
                            }
                        }
                        $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlets_category'] = rtrim($out_category_name, ', ');
                       
                        $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['distance_km'] = $distance_km;
                    }
					// print_r($datas->outlets_id);exit;
					if(!empty($datas->outlets_id)) {
						$opentime = getOpenTimings($datas->outlets_id,date("N"));
						$now_time = date("h.i A");
						if(count($opentime) > 0 ) {
							if(isset($opentime[0]) && $opentime[0]!=''){
								if(($now_time < date("h.i A",strtotime($opentime[0]->start_time))) && ($now_time  > date("h.i A",strtotime($opentime[0]->end_time)))) {
									$outlet_open_status = 0 ; 
									$outlet_open_status_text =trans('messages.Closed');
								} else {
									$outlet_open_status = 1;
									$outlet_open_status_text =trans('messages.Open');
								}
							}
						} else {
							$outlet_open_status = 0 ; 
							$outlet_open_status_text =trans('messages.Closed');
						}
						$outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlet_open_status'] = $outlet_open_status;
						$outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlet_open_status_text'] = $outlet_open_status_text;
						$outlets_list[$datas->vendors_id]['vendor_outlet_open_status'] = $outlet_open_status;
						$outlets_list[$datas->vendors_id]['vendor_outlet_open_status_text'] = $outlet_open_status_text;
					}
                }
                $i ++;
            }

            //$rateit_js  = url('assets/front/'.Session::get('general')->theme.'/plugins/rateit/src/jquery.rateit.js');
            //$rateit_css = url('assets/front/'.Session::get('general')->theme.'/plugins/rateit/src/rateit.css');
            //$html       = '<meta  content="text/html; charset=UTF-8" /><script src="'.$rateit_js.'"></script><link href="'.$rateit_css.'" rel="stylesheet">';
          
            foreach($outlets_list as $outlets_data)
            { 
				
                if($category_url !='')
                {
					$store_url = URL::toi('store/info/'.$outlets_data['url_index'].'/'.$category_url);
			    }
			    else{
					$store_url = URL::to('store/info/'.$outlets_data['url_index']);
				}
                if(file_exists(base_path().'/public/assets/admin/base/images/vendors/list/'.$outlets_data['featured_image']))
                {
                    if(count($outlets_data['outlets'])>1)
                    {
                        $url = url('/assets/admin/base/images/vendors/list/'.$outlets_data['featured_image'].'?'.time());
                        $image ='<a href="javascript:;" title="'.$outlets_data['vendor_name'].'" data-toggle="modal" data-target=".bd-example-modal-lg'.$outlets_data['vendors_id'].'" > <img alt="'.$outlets_data['outlet_name'].'" src="'.$url.'" ></a>';
                    }
                    elseif(count($outlets_data['outlets']) == 1) {
                        $url = url('/assets/admin/base/images/vendors/list/'.$outlets_data['featured_image'].'?'.time());
                        
                         if($outlets_data['vendor_outlet_open_status'] == 0){
							 $image ='<a href="javascript:;" onclick = "toastr.warning(\''.trans('Store is closed if you place an order it will be served when we get openned.').'\')" title="'.$outlets_data['vendor_name'].'"> <img alt="'.$outlets_data['outlet_name'].'" src="'.$url.'" ></a>';
							}
						else{
								 $image ='<a href="'.$store_url.'" title="'.$outlets_data['vendor_name'].'"> <img alt="'.$outlets_data['outlet_name'].'" src="'.$url.'" ></a>';
								
							}
                       
                    }
                }
                else {
                    if(count($outlets_data['outlets'])>1)
                    {
                        $image ='<a href="javascript:;" title="'.$outlets_data['vendor_name'].'" data-toggle="modal" data-target=".bd-example-modal-lg'.$outlets_data['vendors_id'].'" ><img src="{{ URL::asset("assets/admin/base/images/vendors/stores.png") }}" alt="'.$outlets_data['outlet_name'].'"></a>';
                    }
                    elseif(count($outlets_data['outlets']) == 1) {
						 if($outlets_data['vendor_outlet_open_status'] == 0){
							 
							  $image ='<a href="javascript:;" onclick = "toastr.warning(\''.trans('Store is closed if you place an order it will be served when we get openned.').'\')" title="'.$outlets_data['vendor_name'].'"></a>';
							
					     }
					     else {
							  $image ='<a href="'.$store_url.'" title="'.$outlets_data['vendor_name'].'"><img src="{{ URL::asset("assets/admin/base/images/vendors/stores.png") }}" alt="'.$outlets_data['outlet_name'].'"></a>';
							 
							 }
                    }
                }

                $outlet_html ='<script>
                                    $(document ).ready(function() { 
                                        $(".close").on("click", function() { 
                                            $("body").removeClass("modal-open");
                                            $(".modal-backdrop").hide();
                                        });
                                    });
                                </script>';
                
                if(count($outlets_data['outlets'])>1)
                {
                    $outlet_html .='<div class="modal fade store_detials_list bd-example-modal-lg'.$outlets_data['vendors_id'].'" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button><h4 id="myLargeModalLabel" class="modal-title">'.$outlets_data['vendor_name'].'</h4><p class="store_title">'.count($outlets_data['outlets']).' '.trans("messages.Branches available near you.").'</p></div><div class="store_right_items">';
                    foreach($outlets_data['outlets'] as $outlets)
                    {
						if($category_url !='')
						{
							$outlets_url = URL::to('store/info/'.$outlets['url_index'].'/'.$category_url);
						}
						else{
							$outlets_url = URL::to('store/info/'.$outlets['url_index']);
						}
                        $outlet_html .='<div class="col-md-3 col-sm-3 col-xs-6"><div class="common_item"><div class="store_itm_img">';
                        if(file_exists(base_path().'/public/assets/admin/base/images/vendors/logos/'.$outlets_data['logo_image']))
                        {
                            $url    = url('/assets/admin/base/images/vendors/logos/'.$outlets_data['logo_image'].'?'.time());
								if($outlets['outlet_open_status']==0)
								{
									
									$oimage = '<a  href="javascript:;" onclick = "toastr.warning(\''.trans('Store is closed if you place an order it will be served when we get openned.').'\')" title="'.$outlets['outlet_name'].'"> <img alt="'.	$outlets['outlet_name'].'"  src="'.$url.'" ></a>';
								}
								else{
									
									$oimage = '<a href="'.$outlets_url.'" title="'.$outlets['outlet_name'].'"> <img alt="'.$outlets['outlet_name'].'"  src="'.$url.'" ></a>';
								}

                        }
                        else{
							
							if($outlets['outlet_open_status']==0)
								{
									  $oimage ='<a href="javascript:;" onclick = "toastr.warning(\''.trans('Store is closed if you place an order it will be served when we get openned.').'\')" title="'.$outlets['outlet_name'].'"><img src="{{ URL::asset("assets/admin/base/images/vendors/stores.png") }}" alt="'.$outlets['outlet_name'].'"></a>';
								}
								else{
									  $oimage ='<a href="'.$outlets_url.'" title="'.$outlets['outlet_name'].'"><img src="{{ URL::asset("assets/admin/base/images/vendors/stores.png") }}" alt="'.$outlets['outlet_name'].'"></a>';
								}
                          
                        }
                       
                        
                        
                        if( !empty($current_lat) && !empty($current_long) )
                        {
                            $distance2 = '<div class="price_sec"><b>'.$outlets['distance_km'].' KM</b></div>';
                            if($outlets['outlet_open_status'] == 0){
							 $outlet_open_closed_status = '<div class="price_sec_outlet_closed_status"><b>'.$outlets['outlet_open_status_text'].'</b></div>';
							 }
							 else{
								 $outlet_open_closed_status = '<div class="price_sec_outlet_open_status"><b>'.$outlets['outlet_open_status_text'].'</b></div>'; 
								 
								 };

								 
                        }else {


							$distance2 = '<div class="price_sec"><b>'.$outlets['outlets_delivery_time'].' '.trans("messages.Mins").'</b></div>';
                         if($outlets['outlet_open_status'] == 0){
							 $outlet_open_closed_status = '<div class="price_sec_outlet_closed_status"><b>'.$outlets['outlet_open_status_text'].'</b></div>';
							 }
							 else{
								 $outlet_open_closed_status = '<div class="price_sec_outlet_open_status"><b>'.$outlets['outlet_open_status_text'].'</b></div>'; 
								 
								 }
							
						}
                        $outlet_html .=$oimage.$distance2.$outlet_open_closed_status.'</div><div class="store_itm_desc">
                        <a href="'.$outlets_url.'" title="'.$outlets['outlet_name'].'">'.$outlets['outlet_name'].'</a><p>'.substr($outlets['outlets_category'],0,85).'</p></div><div class="store_itm_rating"><h2>
                                <div class="rateit" data-rateit-value='.$outlets['outlets_average_rating'].' data-rateit-ispreset="true" data-rateit-readonly="true">  </div>&nbsp'. $outlets['outlets_average_rating'].' </h2></div><div class="store_itm_rating map_location">
                        <a class="location_location"><i class="glyph-icon flaticon-location-pin"></i>'.substr($outlets['contact_address'],0,50).'</a>
                        </div></div></div>';//.' '.trans("messages.Mins")
                    }
                    $outlet_html .='</div></div></div></div>';
                }
                $more =$outlet_open_closed_status='';
                if(count($outlets_data['outlets'])>1)
                {
                    $count = count($outlets_data['outlets'])-1;
                    $more .= '<a href="javascript:;" class="right_store" title="'.$count.' '.trans("messages.Branches available").'" data-toggle="modal" data-target=".bd-example-modal-lg'.$outlets_data['vendors_id'].'">'.$count.' '.trans("messages.Branches available").'</a>';
                }
                if(count($outlets_data['outlets']) > 0)
                {   
                    $distance1 = '<div class="price_sec"><b>'.$outlets_data['outlets_delivery_time'].' '.trans("messages.Mins").'</b></div>';
                   // $outlet_open_closed_status = '<div class="price_sec_outlet_open_status"><b>'.$outlets_data['vendor_outlet_open_status_text'].'</b></div>';
                     
                    if( !empty($current_lat) && !empty($current_long) )
                    {
                        $distance1 = '<div class="price_sec"><b>'.$outlets_data['outlets_delivery_time'].' KM</b></div>';
                        
                    }
                    if(count($outlets_data['outlets']) ==1 )
                    {
                        $distance1 = '<div class="price_sec"><b>'.$outlets_data['vendors_delivery_time'].' '.trans("messages.Mins").'</b></div>';    
                        if($outlets_data['vendor_outlet_open_status'] == 0){
							  $outlet_open_closed_status = '<div class="price_sec_outlet_closed_status"><b>'.$outlets_data['vendor_outlet_open_status_text'].'</b></div>';
						}
						 else{
								 $outlet_open_closed_status = '<div class="price_sec_outlet_open_status"><b>'.$outlets_data['vendor_outlet_open_status_text'].'</b></div>'; 
								 
					         }
                    }
                    $html .='<div class="col-md-4 col-sm-4 col-xs-6"><div class="common_item"><div class="store_itm_img">'.$image.$distance1.$outlet_open_closed_status.'</div><div class="store_itm_desc"><a href="javascript:;" data-toggle="modal" data-target=".bd-example-modal-lg'.$outlets_data['vendors_id'].'" title="'.$outlets_data['vendor_name'].'">'.$outlets_data['vendor_name'].'</a><p>'.substr($outlets_data['vendor_category'],0,85).'</p></div><div class="store_itm_rating">'. $more.'<h2><a><div class="rateit" data-rateit-value="'.$outlets_data['vendors_average_rating'].'" data-rateit-ispreset="true" data-rateit-readonly="true"></div></a>'.$outlets_data['vendors_average_rating'].'</h2></div></div></div>'.$outlet_html;
                }
               
               
            }
            if(isset($post_data['type']) && $post_data['type']=='web')
            {
				
               if(isset($post_data['filter']) && $post_data['filter']=='1')
               {
				    $result = array("response" => array("httpCode" => 200, "status" => true,'data' =>$html));
				}
				else {
					  $result = array("response" => array("httpCode" => 200, "status" => true,'data' =>utf8_encode($html)));
					}
				
				
            }
            else {
				
                $result = array("response" => array("httpCode" => 200, "status" => true,'data' => $outlets_list));
            }
         
        }
        else
        {
			$no_store =  url("assets/front/".Session::get("general")->theme."/images/no_store.png");
        $html='<div class="no_store_avlable"><div class="no_store_img"><img src="'.$no_store.'" alt=""><p>'. trans("messages.No Shops available based on your search").'</p></div></div>';
			}
          $store_html = $html; 
          
       
		SEOMeta::setTitle(Session::get("general_site")->site_name.' - ' .'Stores');
		SEOMeta::setDescription(Session::get("general_site")->site_name.' - ' .'Stores');
		SEOMeta::addKeyword(Session::get("general_site")->site_name.' - ' .'Stores');
		OpenGraph::setTitle(Session::get("general_site")->site_name.' - ' .'Stores');
		OpenGraph::setDescription(Session::get("general_site")->site_name.' - ' .'Stores');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name.' - ' .'Stores');
		Twitter::setSite(Session::get("general_site")->site_name.' - ' .'Stores');
		$categories_list = getCategoryLists(2);
		$banners = get_store_banner_list(1);
		
		return view('front.'.$this->theme.'.store_list')->with('categories', $categories_list)->with('category_id', $categories)->with('banners', $banners)->with('store_html', $store_html)->with('description', $description);
	}*/
	
	public function index(Request $data)
	{
		$api = New Api;
		$post_array   = $data->all();
		$categories   = isset($post_array['category_ids'])?$post_array['category_ids']:"";
		$category_url = isset($post_array['category_url'])?$post_array['category_url']:"";
		$city         = isset($post_array['city'])?$post_array['city']:"";
		$user_id      = Session::get('user_id');
		$location     = "";
		if(isset($post_array['location']))
		{
			$location = $post_array['location'];
			Session::put('location', $post_array['location']);
		}
		if(isset($post_array['city']))
		{
			$city = $post_array['city'];
			Session::put('city', $post_array['city']);
		}
		$store_array  = array("city" => $city, "location" => $location,"language" => getCurrentLang(),"category_ids" =>$categories,"type" =>"web","category_url" =>$category_url,"user_id" =>$user_id);
		$method       = "POST";
		$data         = array('form_params' => $store_array);
		$response     = $api->call_api($data,'api/store_list_ajax',$method);
		$store        = $response->response;
		SEOMeta::setTitle(Session::get("general_site")->site_name.' - ' .'Stores');
		SEOMeta::setDescription(Session::get("general_site")->site_name.' - ' .'Stores');
		SEOMeta::addKeyword(Session::get("general_site")->site_name.' - ' .'Stores');
		OpenGraph::setTitle(Session::get("general_site")->site_name.' - ' .'Stores');
		OpenGraph::setDescription(Session::get("general_site")->site_name.' - ' .'Stores');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name.' - ' .'Stores');
		Twitter::setSite(Session::get("general_site")->site_name.' - ' .'Stores');
		$categories= getCategoryLists(2);
		
		$banners = DB::table('banner_settings')->select('*')->where('banner_type', 2)->where('status', 1)->orderBy('banner_setting_id', 'desc')->get();
		return view('front.'.$this->theme.'.store_list')->with('store', $store)->with('categories', $categories)->with('banners', $banners);
	}




	public function getOpenTimings($v_id,$day_week)
	{
		$time_data = DB::table('opening_timings')
			->where("vendor_id",$v_id)
			->where("day_week",$day_week)
			->orderBy('id', 'asc')
			->get();
		$time_list=array();
		if(count($time_data)>0){
			$time_list = $time_data;
		}
		return $time_list;
	}
	
	public function get_delivery_time_interval()
	{
		$time_interval = DB::table('delivery_time_interval')
				->select('*')
				->orderBy('start_time', 'asc')
				->get();
		return $time_interval;
	}

	public function addtofavourite(Request $data)
	{
		$api = New Api;
		$post_array = $data->all();
		$user_id = Session::get('user_id');
		$token = Session::get('token');
		$store_array = array("user_id" => $user_id,"token"=>$token,"language" => getCurrentLang(),"vendor_id" =>$post_array["vendor_id"]);
		$method = "POST";
		$data = array('form_params' => $store_array);
		$response = $api->call_api($data,'api/addto_favourite',$method);
		return response()->json($response->response);	
		
	}
	
	public function store_info($outlet_url,$category_ids='')
	{ 

		
		$api = New Api;
		if($outlet_url == '' )
		{
			Session::flash('message', 'Invalid Store'); 
			return Redirect::to('/');
		}
		$vendor_category = "";
		if($category_ids != '' )
		{
			$category_ids = explode("_",$category_ids);
			$c_ids   =  implode($category_ids,"','");
			$vendor_category   = "'".$c_ids."'";
			
		}
		 //$language_id =getcurrentlanguage();
		$vendors = DB::table('outlets')
					->select('outlets.vendor_id','outlets.id as outlet_id')
					->join('vendors','vendors.id','=','outlets.vendor_id')
					->where('outlets.url_index','=',$outlet_url)
					->where('vendors.featured_vendor','=',1)
					->where('vendors.active_status','=',1)
					->where('outlets.active_status','=',1)
					->first();
		if(!count($vendors))
		{
			Session::flash('message', 'Invalid Store'); 
			return Redirect::to('/');
		}
		$store_id = $vendors->outlet_id;
		DB::table('outlets')->where('outlets.id','=',$store_id)->increment('view_count');
		
       $query = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.lang_id) as totalcount from vendors_infos where vendors_infos.lang_id = '.getCurrentLang().' and vendors.id = vendors_infos.id) > 0 THEN '.getCurrentLang().' ELSE 1 END)';
		$vendor = DB::table('vendors')
					->select('vendors_infos.vendor_name','vendors_infos.vendor_description')
					->leftJoin('vendors_infos','vendors_infos.id','=','vendors.id')
					->whereRaw($query)
					->where('vendors_infos.id','=',$vendors->vendor_id)
					->where('vendors.featured_vendor','=',1)
					->where('vendors.active_status','=',1)
					->get();
		$fstatus = 0;
		$vdata   = DB::table('favorite_vendors')
					->select('favorite_vendors.id','favorite_vendors.status')
					->where('favorite_vendors.customer_id','=',Session::get('user_id'))
					->where('favorite_vendors.vendor_id','=',$store_id)
					->where('favorite_vendors.status','=',1)
					->get();
		if(count($vdata))
		{
			$fstatus = $vdata[0]->status;
		}
		
		$store_array = array("language" => getCurrentLang(),"store_id" => $store_id,"user_id" => Session::get('user_id'),"vendor_category"=>$vendor_category);
        //print_r($store_array);exit;
		$method      = "POST";
		$data        = array('form_params' => $store_array);
		$response    = $api->call_api($data,'api/store_info',$method);
		//print_r($response);exit;
		$store       = $response->response;
		
		
		SEOMeta::setTitle(Session::get("general_site")->site_name.' - '.$vendor[0]->vendor_name);
		SEOMeta::setDescription(Session::get("general_site")->site_name.' - '.$vendor[0]->vendor_name.' - '.$vendor[0]->vendor_description);
		SEOMeta::addKeyword(Session::get("general_site")->site_name.' - '.$vendor[0]->vendor_name);
		OpenGraph::setTitle(Session::get("general_site")->site_name.' - '.$vendor[0]->vendor_name);
		OpenGraph::setDescription(Session::get("general_site")->site_name.' - '.$vendor[0]->vendor_name.' - '.$vendor[0]->vendor_description);
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name.' - '.$vendor[0]->vendor_name);
		Twitter::setSite(Session::get("general_site")->site_name.' - '.$vendor[0]->vendor_name);
		$categories = getVendorsubCategoryLists($vendors->vendor_id);
		//~ $banners    = DB::table('banner_settings')->select('banner_settings')->where('banner_type', 2)->orderBy('banner_setting_id', 'desc')->get();
		$cart_item  = 0;
		if(Session::get('user_id'))
		{
			$cdata  = DB::table('cart')
						->leftJoin('cart_detail','cart_detail.cart_id','=','cart.cart_id')
						->select('cart_detail.cart_id',DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
						->where("cart.user_id","=",Session::get('user_id'))
						->groupby('cart_detail.cart_id')
						->get();
			if(count($cdata))
			{
				$cart_item = $cdata[0]->cart_count;
			}
		}
		$time_interval = $this->get_delivery_time_interval();
		$date          = date('Y-m-d'); //today date
		$weekOfdays    = $week = $deliver_slot_array = array();
		for($i =1; $i <= 7; $i++)
		{
			$weekOfdays[$i] = date('d M', strtotime($date));
			$weekday        = date('l', strtotime($date));
			foreach($time_interval as $time)
			{
				$deliver_slot_array[$weekday][] = date('g:i a', strtotime($time->start_time)).' - '.date('g:i a', strtotime($time->end_time));
			}
			$week[$i] = date('l',strtotime($date));
			$date     = date('Y-m-d', strtotime('+1 day', strtotime($date)));
		} 
		$rcondtion = "(outlet_reviews.approval_status = 1)";
		$reviews   = DB::table('outlet_reviews')
						->select('outlet_reviews.comments', 'outlet_reviews.ratings', 'outlet_reviews.created_date', 'users.id', 'users.first_name', 'users.last_name', 'users.name', 'users.image')
						->leftJoin('users','users.id','=','outlet_reviews.customer_id')
						->whereRaw($rcondtion)
						->where("outlet_reviews.outlet_id", "=", $store_id)
						->orderBy('outlet_reviews.id', 'desc')//->toSql();
						->get();
						//print_r($reviews);exit;
		$timearray = getDaysWeekArray();
		$u_time    = array();
		foreach($timearray as $key1 => $val1)
		{
			$u_time[$key1] = $this->getOpenTimings($store_id,$val1);
		}
		
		$categories = getProduct_category_list($store_id,$vendor_category);
		$category_formated = array();
		if(count($categories)>0){
			
			foreach($categories as $key => $cat){
				$subcategories = getSubCategoryListsupdated(1,$cat->category_id);
				//print_r($subcategories);
				$category_formated[$key]["category_id"]=$cat->category_id;
				$category_formated[$key]["url_key"]=$cat->url_key;
				$category_formated[$key]["category_name"]=$cat->category_name;
				$category_formated[$key]["image"]=$cat->image;
				$category_formated[$key]["subcategory"]=$subcategories;
				
			}
		}

		//print_r($category_formated); exit;

		//print_r($categories); exit;
		
		return view('front.'.$this->theme.'.store_info')->with('store', $store)->with('vcategories', $categories)->with('categories', $category_formated)->with('api', $api)->with('cart_item',$cart_item)->with('deliver_slot', $deliver_slot_array)->with('reviews', $reviews)->with('fstatus', $fstatus)->with('open_time',$u_time);//->with('banners', $banners)
	}

	public function product_list()
	{
		$outlet_key   = Input::get('url-key');
		$cate_url   = Input::get('category');
		//$outlet_key = Input::get('outlet-key');

	
		$api = New Api;
		$keyword    = pg_escape_string(Input::get('keyword'));
		SEOMeta::setTitle(Session::get("general_site")->site_name.' - ' .'Stores');
		SEOMeta::setDescription(Session::get("general_site")->site_name.' - ' .'Stores');
		SEOMeta::addKeyword(Session::get("general_site")->site_name.' - ' .'Stores');
		OpenGraph::setTitle(Session::get("general_site")->site_name.' - ' .'Stores');
		OpenGraph::setDescription(Session::get("general_site")->site_name.' - ' .'Stores');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name.' - ' .'Stores');
		Twitter::setSite(Session::get("general_site")->site_name.' - ' .'Stores');
		$store_array = array("language" => getCurrentLang(), "outlet_key" => $outlet_key, "category_url" => $cate_url, "keyword" => $keyword);
		$method      = "POST";
		$data        = array('form_params' => $store_array);
		$response    = $api->call_api($data,'api/product_list',$method);
		$products    = $response->response;
		$cart_item  = 0;
		
		//print_r($response->response);exit;
		
		if(Session::get('user_id'))
		{
			$cdata = DB::table('cart')
					->leftJoin('cart_detail','cart_detail.cart_id','=','cart.cart_id')
					->select('cart_detail.cart_id',DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
					->where("cart.user_id","=",Session::get('user_id'))
					->groupby('cart_detail.cart_id')
					->get();
			if(count($cdata))
			{
				$cart_item = $cdata[0]->cart_count;
			}
		}
		
		$fstatus = 0;
		$vdata   = DB::table('favorite_vendors')
					->select('favorite_vendors.id','favorite_vendors.status')
					->where('favorite_vendors.customer_id','=',Session::get('user_id'))
					->where('favorite_vendors.vendor_id','=',$vendors->vendor_id)
					->where('favorite_vendors.status','=',1)
					->get();
		if(count($vdata))
		{
			$fstatus = $vdata[0]->status;
		}
		
		//echo $fstatus;exit;
		return view('front.'.$this->theme.'.product_list')->with('products', $products)->with('cart_item', $cart_item)->with('fstatus', $fstatus);
	}
	
	public function products($category = "",$outlet = "",$category_id = "",$subcategory_id = "")
	{
	

		$cate_url   = $category;
		$outlet_key = $outlet;
		$subcategory   = $subcategory_id;

		$keyword    = pg_escape_string(Input::get('keyword'));
		SEOMeta::setTitle(Session::get("general_site")->site_name.' - ' .'Stores');
		SEOMeta::setDescription(Session::get("general_site")->site_name.' - ' .'Stores');
		SEOMeta::addKeyword(Session::get("general_site")->site_name.' - ' .'Stores');
		OpenGraph::setTitle(Session::get("general_site")->site_name.' - ' .'Stores');
		OpenGraph::setDescription(Session::get("general_site")->site_name.' - ' .'Stores');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name.' - ' .'Stores');
		Twitter::setSite(Session::get("general_site")->site_name.' - ' .'Stores');


		$api = New Api;
		$store_array = array("language" => getCurrentLang(), "outlet_key" => $outlet_key, "category_url" => $cate_url, "keyword" => $keyword,"user_id" => Session::get('user_id'),"subcategory" =>  $subcategory);
		
		$method      = "POST";
		$data        = array('form_params' => $store_array);
		$response    = $api->call_api($data,'api/product_list',$method);
		$products    = $response->response;

		//print_r($products); exit;
		//echo "<pre>";
		////print_r($products->categories);exit;
		$cart_item  = 0;
		if(Session::get('user_id'))
		{
			$cdata = DB::table('cart')
					->leftJoin('cart_detail','cart_detail.cart_id','=','cart.cart_id')
					->select('cart_detail.cart_id',DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
					->where("cart.user_id","=",Session::get('user_id'))
					->groupby('cart_detail.cart_id')
					->get();
			if(count($cdata))
			{
				$cart_item = $cdata[0]->cart_count;
			}
		}
		
		if($outlet_key == '' )
		{
			Session::flash('message', 'Invalid Store'); 
			return Redirect::to('/');
		}
		$vendors = DB::table('outlets')
					->select('outlets.vendor_id','outlets.id as outlet_id')
					->join('vendors','vendors.id','=','outlets.vendor_id')
					->where('outlets.url_index','=',$outlet_key)
					->where('vendors.featured_vendor','=',1)
					->where('vendors.active_status','=',1)
					->where('outlets.active_status','=',1)
					->first();
		if(!count($vendors))
		{
			Session::flash('message', 'Invalid Store'); 
			return Redirect::to('/');
		}
		$store_id = $vendors->outlet_id;
		$category_image = getProduct_category_image($cate_url,$category_id);
		$categories = getProduct_category_list($store_id);
		$category_formated = array();
		if(count($categories)>0){
			
			foreach($categories as $key => $cat){
				$subcategories = getSubCategoryListsupdated(1,$cat->category_id);
				//print_r($subcategories);
				$category_formated[$key]["category_id"]=$cat->category_id;
				$category_formated[$key]["url_key"]=$cat->url_key;
				$category_formated[$key]["category_name"]=$cat->category_name;
				$category_formated[$key]["image"]=$cat->image;
				$category_formated[$key]["subcategory"]=$subcategories;
				
			}
		}
		//print_r($categories);exit;
		//$categories = getVendorsubCategoryLists($vendors->vendor_id);
		$query = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.lang_id) as totalcount from vendors_infos where vendors_infos.lang_id = '.getCurrentLang().' and vendors.id = vendors_infos.id) > 0 THEN '.getCurrentLang().' ELSE 1 END)';
		$vendor = DB::table('vendors')
					->select('vendors_infos.vendor_name','vendors_infos.vendor_description')
					->leftJoin('vendors_infos','vendors_infos.id','=','vendors.id')
					->whereRaw($query)
					->where('vendors_infos.id','=',$vendors->vendor_id)
					->where('vendors.featured_vendor','=',1)
					->where('vendors.active_status','=',1)
					->get();
	$fstatus = 0;
		$vdata   = DB::table('favorite_vendors')
					->select('favorite_vendors.id','favorite_vendors.status')
					->where('favorite_vendors.customer_id','=',Session::get('user_id'))
					->where('favorite_vendors.vendor_id','=',$vendors->vendor_id)
					->where('favorite_vendors.status','=',1)
					->get();
		if(count($vdata))
		{
			$fstatus = $vdata[0]->status;
		}
		$time_interval = $this->get_delivery_time_interval();
		$date          = date('Y-m-d'); //today date
		$weekOfdays    = $week = $deliver_slot_array = array();
		for($i =1; $i <= 7; $i++)
		{
			$weekOfdays[$i] = date('d M', strtotime($date));
			$weekday        = date('l', strtotime($date));
			foreach($time_interval as $time)
			{
				$deliver_slot_array[$weekday][] = date('g:i a', strtotime($time->start_time)).' - '.date('g:i a', strtotime($time->end_time));
			}
			$week[$i] = date('l',strtotime($date));
			$date     = date('Y-m-d', strtotime('+1 day', strtotime($date)));
		}
		$rcondtion = "(outlet_reviews.approval_status = 1)";
		$reviews   = DB::table('outlet_reviews')
						->select('outlet_reviews.comments', 'outlet_reviews.ratings', 'outlet_reviews.created_date', 'users.id', 'users.first_name', 'users.last_name', 'users.name', 'users.image')
						->leftJoin('users','users.id','=','outlet_reviews.customer_id')
						->whereRaw($rcondtion)
						->where("outlet_reviews.outlet_id", "=", $store_id)
						->orderBy('outlet_reviews.id', 'desc')
						->get();
		$timearray = getDaysWeekArray();
		$u_time    = array();
		foreach($timearray as $key1 => $val1)
		{
			$u_time[$key1] = $this->getOpenTimings($store_id,$val1);
		}
		return view('front.'.$this->theme.'.product_list')->with('products', $products)->with('cart_item', $cart_item)->with('deliver_slot', $deliver_slot_array)->with('reviews', $reviews)->with('fstatus', $fstatus)->with('open_time',$u_time)->with('cate_url',$cate_url)->with('outlet_key',$outlet_key)->with('categories',$category_formated)->with('category_image',$category_image);//->with('banners', $banners)
	}
	public function cms_mob($index="",$language)
	{
		$query    = '"cms_infos"."language_id" = (case when (select count(*) as totalcount from cms_infos where cms_infos.language_id = '.$language.' and cms.id = cms_infos.cms_id) > 0 THEN '.$language.' ELSE 1 END)';
		$cms_info = DB::table('cms')
					->select('cms.*','cms_infos.*')
					->leftJoin('cms_infos','cms_infos.cms_id','=','cms.id')
					->whereRaw($query)
					->where("url_index","=",$index)
					->limit(1)
					->get();
		if(!count($cms_info))
		{
			Session::flash('message', 'Invalid Page'); 
			Session::flash('alert-class', 'alert-danger'); 
			return Redirect::to('/');	
		}
		SEOMeta::setTitle(Session::get("general_site")->site_name.' - '.$cms_info[0]->title);
        SEOMeta::setDescription(Session::get("general_site")->site_name.' - '.$cms_info[0]->title);
        SEOMeta::addKeyword(Session::get("general_site")->site_name.' - '.$cms_info[0]->title);
        OpenGraph::setTitle(Session::get("general_site")->site_name.' - '.$cms_info[0]->title);
		OpenGraph::setDescription(Session::get("general_site")->site_name.' - '.$cms_info[0]->title);
        // OpenGraph::setUrl(URL::to('/'));
        Twitter::setTitle(Session::get("general_site")->site_name.' - '.$cms_info[0]->title);
        Twitter::setSite(Session::get("general_site")->site_name);
		return view('front.'.$this->theme.'.cmsinfo_mob')->with('cmsinfo', $cms_info,'languages',$language);
	}
	public function product_info($outlet_url,$product_url)
    {   
		$api = New Api;
		$store_array = array("language" => getCurrentLang(),"product_url" => $product_url,"outlet_url" => $outlet_url);
		$method      = "POST";
		$data        = array('form_params' => $store_array);
	    $response    = $api->call_api($data,'api/product_details',$method);
		$products    = $response->response;
		//print_r($products); exit;
		if($products->httpCode == 400)
		{
			Session::flash('message', 'Invalid Product'); 
			return Redirect::to('/');
		}
		$cart_item  = 0;
		if(Session::get('user_id'))
		{
			$cdata = DB::table('cart')
					->leftJoin('cart_detail','cart_detail.cart_id','=','cart.cart_id')
					->select('cart_detail.cart_id',DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
					->where("cart.user_id","=",Session::get('user_id'))
					->groupby('cart_detail.cart_id')
					->get();
			if(count($cdata))
			{
				$cart_item = $cdata[0]->cart_count;
			}
		}
		
		$product_id = $products->data->product_id;
		$rcondtion = "(product_reviews.approval_status = 1)";
		$product_reviews   = DB::table('product_reviews')
						->select('product_reviews.comments', 'product_reviews.ratings', 'product_reviews.created_date', 'users.id', 'users.first_name', 'users.last_name', 'users.name', 'users.image')
						->leftJoin('users','users.id','=','product_reviews.customer_id')
						->whereRaw($rcondtion)
						->where("product_reviews.product_id", "=", $product_id)
						->orderBy('product_reviews.id', 'desc')//->toSql();
						->get();
			
		SEOMeta::setTitle(Session::get("general_site")->site_name.' - '.ucfirst($products->data->product_name));
		SEOMeta::setDescription(Session::get("general_site")->site_name.' - '.ucfirst($products->data->product_name));
		SEOMeta::addKeyword(Session::get("general_site")->site_name.' - '.ucfirst($products->data->product_name));
		OpenGraph::setTitle(Session::get("general_site")->site_name.' - '.ucfirst($products->data->product_name));
		OpenGraph::setDescription(Session::get("general_site")->site_name.' - '.ucfirst($products->data->product_name));
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name.' - '.ucfirst($products->data->product_name));
		Twitter::setSite(Session::get("general_site")->site_name.' - '.ucfirst($products->data->product_name));
		return view('front.'.$this->theme.'.product_info')->with('products', $products)->with('product_reviews', $product_reviews)->with('cart_item', $cart_item);//->with('banners', $banners)
    }
    public function store_outlet_list(Request $data)
	{  
	  
        $post_data = $data->all();
          $language      = $post_data['language'];
              $geolocation   = $post_data['latitude'].','.$post_data['longitude'];
$request ='https://maps.googleapis.com/maps/api/geocode/json?latlng='.$geolocation.'&key=AIzaSyCyxq_aPwRll2NUXhhJQT4KRu1JG3a2dDM&sensor=false';
            $file_contents = file_get_contents($request);
            $json_decode   = json_decode($file_contents);

            $city_name     = $location_name = 'null';
         
            if(isset($json_decode->results[0]))
            {
                $response = array();

                foreach($json_decode->results[0]->address_components as $addressComponet)
                {
                    if(in_array('locality', $addressComponet->types))
                    {
                        $city_name = $addressComponet->long_name; 
                    }
                    if(in_array('sublocality_level_1', $addressComponet->types))
                    {
                        $location_name = $addressComponet->long_name; 
                    }
                }
                  // echo '<pre>';print_r($city_name);exit;
                $result = array("response" => array("httpCode" => 400 , "Message" => trans("messages.No service available in your location. Please select available location.")));
                if($city_name != '')
                {
                    //$city_location_det = Api_Model::get_location_list_city($language, str_slug($city_name), str_slug($location_name));
                   
                        $result   = array("response" => array("httpCode" => 200, "Message" => trans("messages.Location details"),  "city_url_index" =>str_slug($city_name),  "location_url_index" =>'null','latitude' => encrypt($data->latitude), 'longitude' => encrypt($data->longitude)));
                   
                }
            }
            else 
            {
                $result = array("response" => array("httpCode" => 400 , "Message" => trans("messages.No service available in your location. Please select available location.")));
            }  

//print_r($result);exit;
        return json_encode($result, JSON_UNESCAPED_UNICODE);
	}
	public function store_list_ajax_front(Request $data)
    {
        $post_data = $data->all();
        $no_store =  url("assets/front/".Session::get("general")->theme."/images/no_store.png");
        $html='<div class="no_store_avlable"><div class="no_store_img"><img src="'.$no_store.'" alt=""><p>'. trans("messages.No Shops available based on your search").'</p></div></div>';
        $category_url =  isset($post_data['category_url'])?$post_data['category_url']:'';
        $language = isset($post_data['language'])?$post_data['language']:'1';
        if($language == 2)
        {
            App::setLocale('ar');
        }
        else {
            App::setLocale('en');
        } 
       $current_lat =session::get('current_lat');
        $current_long = session::get('current_long');
        //print_r($current_lat);exit;
        $distance = 25*1000;
        $data   = array();
        $result = array("response" => array("httpCode" => 400, "status" => false, "data" =>$data));
        $query  = 'vendors_infos.lang_id = (case when (select count(vendors_infos.lang_id) as totalcount from vendors_infos where vendors_infos.lang_id = '.$language.' and vendors.id = vendors_infos.id) > 0 THEN '.$language.' ELSE 1 END)';
        $query1  = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount1 from outlet_infos where outlet_infos.language_id = '.$language.' and outlets.id = outlet_infos.id) > 0 THEN '.$language.' ELSE 1 END)';
        $condition = 'vendors.active_status = 1';
        $orderby   = 'vendors.id ASC';
        if(isset($post_data['city']) && $post_data['city'])
        {
           // $condition .=' and cities.url_index = '.$post_data['city'];
            $condition .=" and cities.url_index = '".$post_data['city']."'";
        }
        if(isset($post_data['location']) && $post_data['location'])
        {
            $condition .=" and zones.url_index = '".$post_data['location']."'";
        }
        if(isset($post_data['category_ids']) && $post_data['category_ids'])
        {
            $c_ids   = $post_data['category_ids'];
            //$c_ids = "'".$c_ids."'"; 
			$c_ids   =  explode(",",$c_ids);
			$cat_ids = implode($c_ids,"_");
            $c_ids   =  implode($c_ids,"','");
            $c_ids   = "'".$c_ids."'"; 
            //echo $c_ids;exit;
            //$condition .= " and (regexp_split_to_array(category_ids,',')::integer[] @> '{".$c_ids."}'::integer[]  and category_ids !='')";
            $condition .= " and vendor_category_mapping.category in($c_ids)";
        }
        if(isset($post_data['keyword']) && $post_data['keyword'])
        {
            $keyword    = pg_escape_string($post_data['keyword']);
            $condition .= " and vendors_infos.vendor_name ILIKE '%".$keyword."%'";
        }
        if(isset($post_data['sortby']) && $post_data['sortby']=="delivery_time")
        {
            $orderby ='vendors_delivery_time '.$post_data['orderby'];
        }
        if(isset($post_data['sortby']) && $post_data['sortby']=="rating")
        {
            $orderby ='vendors_average_rating '.$post_data['orderby'];
        }
        if( empty($current_lat) && empty($current_long) )
        { 
            $vendors = Vendors::join('vendors_infos','vendors_infos.id','=','vendors.id')
                    ->join('outlets','outlets.vendor_id','=','vendors.id')
                    ->join('outlet_infos','outlet_infos.id','=','outlets.id')
                    ->join('zones','zones.id','=','outlets.location_id')
                    ->join('cities','cities.id','=','outlets.city_id')
                    ->join('vendor_category_mapping','vendor_category_mapping.vendor_id','=','vendors.id')
                    //left join "vendor_category_mapping" on vendor_category_mapping.outlet_id = vendors.id
                    ->select('vendors.id as vendors_id','vendors_infos.vendor_name','vendors.first_name','vendors.last_name','vendors.featured_image','vendors.logo_image','vendors.delivery_time as vendors_delivery_time','vendors.category_ids','vendors.average_rating as vendors_average_rating','outlet_infos.contact_address','outlet_infos.outlet_name','outlets.id as outlets_id','outlets.vendor_id as outlets_vendors_id','outlet_infos.outlet_name','outlets.delivery_time as outlets_delivery_time','outlets.average_rating as outlets_average_rating','outlets.url_index','outlets.category_ids as outlets_category_ids')
                    ->whereRaw($query)
                    ->whereRaw($query1)
                    ->whereRaw($condition)
                    ->where('vendors.featured_vendor','=',1)
                    ->where('outlets.active_status','=','1')
                    ->orderByRaw($orderby)
                    ->get();
        }
        else 
        { 

            $conditiona =  "vendors.active_status=1";

 if (isset($post_data['category_ids']) && $post_data['category_ids']) {
            $c_ids = $post_data['category_ids'];
            //$c_ids = "'".$c_ids."'"; 
            $c_ids = explode(",", $c_ids);
            $c_ids = implode($c_ids, "','");
            $c_ids = "'" . $c_ids . "'";
            //echo $c_ids;exit;
            //$condition .= " and (regexp_split_to_array(category_ids,',')::integer[] @> '{".$c_ids."}'::integer[]  and category_ids !='')";
               $conditiona .= "and vendor_category_mapping.category in($c_ids)";
        } 
            $query = 'vendors_infos.lang_id = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language. ' ELSE 1 END)';
            $query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
            $query2 = 'cities_infos.language_id = (case when (select count(cities_infos.language_id) as totalcount from cities_infos where cities_infos.language_id = '.$language.' and cities.id = cities_infos.id) > 0 THEN '.$language.' ELSE 1 END)';
            $query3 = 'zones_infos.language_id = (case when (select count(zones_infos.language_id) as totalcount from zones_infos where zones_infos.language_id = ' . $language . ' and zones.id = zones_infos.zone_id) > 0 THEN ' . $language . ' ELSE 1 END)';

            $vendors = DB::select("select vendors.logo_image,vendors.category_ids,outlets.delivery_charges_fixed,outlets.city_id,outlets.location_id,vendors.id as vendors_id,vendors.delivery_time as vendors_delivery_time,vendors.average_rating as vendors_average_rating,vendors.featured_image, outlet_infos.contact_address,outlets.vendor_id as outlets_vendors_id,outlets.id as outlets_id,outlet_infos.outlet_name,outlets.delivery_time as outlets_delivery_time,outlets.average_rating as outlets_average_rating,outlets.category_ids as outlets_category_ids,vendors_infos.vendor_name,vendors.first_name,outlets.url_index,vendors.last_name, outlets.delivery_charges_variation,outlets.minimum_order_amount,outlets.active_status,zones_infos.zone_name,cities_infos.city_name,earth_distance(ll_to_earth(".$current_lat.', '.$current_long."), ll_to_earth(outlets.latitude, outlets.longitude)) as distance  from  vendors  left join outlets on outlets.vendor_id =vendors.id left join outlet_infos on outlets.id = outlet_infos.id left Join cities  on cities.id = vendors.city_id left join cities_infos on cities_infos.id =vendors.city_id left join zones on zones.city_id =vendors.city_id left join zones_infos on zones_infos.zone_id =zones.id left join vendors_infos on vendors_infos.id = vendors.id left join vendor_category_mapping on vendor_category_mapping.vendor_id = vendors.id  where earth_box(ll_to_earth(".$current_lat.', '.$current_long.'), '.$distance.") @> ll_to_earth(outlets.latitude, outlets.longitude)and ".$query." and ".$query1." and ".$query2." and ".$query3." and outlets.active_status='1' and vendors.active_status=1 and ". $conditiona ."order by distance desc ");
        }
        if(count($vendors))
        {
            $outlets_list = array();
            $i = 1;
            foreach($vendors as $key => $datas)
            {
                if($datas->outlets_id != 0)
                {
                    $outlets_list[$datas->vendors_id]['vendors_id']     = $datas->vendors_id;
                    $outlets_list[$datas->vendors_id]['vendor_name']    = $datas->vendor_name;
                    $outlets_list[$datas->vendors_id]['featured_image'] = $datas->featured_image;
                    $outlets_list[$datas->vendors_id]['logo_image']     = $datas->logo_image;
                    $outlets_list[$datas->vendors_id]['category_ids']   = $datas->category_ids;
                    $category_name = '';
                    $outlets_list[$datas->vendors_id]['category_ids'] = $category_name;
                    if(!empty($datas->category_ids))
                    {
                        $category_ids = geoutletCategoryLists(explode(',', $datas->category_ids),$language);
                        if( count( $category_ids ) > 0 ) {
                            foreach( $category_ids as $cat ) {
                                $category_name .= $cat->category_name.', ';
                            }
                        }
                        $outlets_list[$datas->vendors_id]['vendor_category'] = rtrim($category_name, ', ');
                    }
                    $outlets_list[$datas->vendors_id]['vendors_delivery_time']  = $datas->vendors_delivery_time;
                    $outlets_list[$datas->vendors_id]['vendors_average_rating'] = ($datas->vendors_average_rating==null)?0:$datas->vendors_average_rating;
                    $outlets_list[$datas->vendors_id]['outlets_id'] = $datas->outlets_id;
                    $outlets_list[$datas->vendors_id]['outlet_name'] = $datas->outlet_name; 
                    $distance_km = number_format($datas->distance/1000,1);
                    $outlets_list[$datas->vendors_id]['distance_km'] = $distance_km; 
                    $outlets_list[$datas->vendors_id]['outlets_category_ids'] = $datas->outlets_category_ids;
                    $outlets_list[$datas->vendors_id]['url_index']  = $datas->url_index;
                    $outlets_list[$datas->vendors_id]['outlets_delivery_time']      = $datas->outlets_delivery_time;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['vendor_name'] = $datas->vendor_name;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlets_id']  = $datas->outlets_id;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlet_name']  = $datas->outlet_name;
                    $outlets_list[$datas->vendors_id]['outlets'] [$datas->outlets_id]['outlets_category_ids']  = $datas->outlets_category_ids;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlets_vendors_id']     = $datas->outlets_vendors_id;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outletname']             = $datas->outlet_name;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['url_index']              = $datas->url_index ;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['contact_address']        = $datas->contact_address;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlets_delivery_time']  = $datas->outlets_delivery_time;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlets_average_rating'] = ($datas->outlets_average_rating==null)?0:$datas->outlets_average_rating;
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['distance'] = $datas->distance;
                    $out_category_name = '';
                    $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlets_category'] = $out_category_name;
                    
                    // print_r($distance_km);exit;
                    if(!empty($datas->outlets_category_ids))
                    {
                        $outlet_category_ids = geoutletCategoryLists(explode(',', $datas->outlets_category_ids),$language);
                        if( count( $outlet_category_ids ) > 0 ) {
                            foreach( $outlet_category_ids as $out_cat ) {
                                $out_category_name .= $out_cat->category_name.', ';
                              
                            }
                        }
                        $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['outlets_category'] = rtrim($out_category_name, ', ');
                       
                        $outlets_list[$datas->vendors_id]['outlets'][$datas->outlets_id]['distance_km'] = $distance_km;
                    }
                    
                    $i ++;
                }
            }

            $rateit_js  = url('assets/front/'.Session::get('general')->theme.'/plugins/rateit/src/jquery.rateit.js');
            $rateit_css = url('assets/front/'.Session::get('general')->theme.'/plugins/rateit/src/rateit.css');
            $html       = '<meta  content="text/html; charset=UTF-8" /><script src="'.$rateit_js.'"></script><link href="'.$rateit_css.'" rel="stylesheet">';
            foreach($outlets_list as $outlets_data)
            {
				if(isset($post_data['category_ids']) && $post_data['category_ids']){
					$store_url = URL::to('store/info/'.$outlets_data['url_index'].'/'.$cat_ids);

				}
				else{
					if($category_url !='')
					{
						$store_url = URL::to('store/info/'.$outlets_data['url_index'].'/'.$category_url);
					}
					else{
						$store_url = URL::to('store/info/'.$outlets_data['url_index']);
					}
				}
              
                if(file_exists(base_path().'/public/assets/admin/base/images/vendors/list/'.$outlets_data['featured_image']))
                {
                    if(count($outlets_data['outlets'])>1)
                    {
                        $url = url('/assets/admin/base/images/vendors/list/'.$outlets_data['featured_image'].'?'.time());
                        $image ='<a href="javascript:;" title="'.$outlets_data['vendor_name'].'" data-toggle="modal" data-target=".bd-example-modal-lg'.$outlets_data['vendors_id'].'" > <img alt="'.$outlets_data['outlet_name'].'" src="'.$url.'" ></a>';
                    }
                    elseif(count($outlets_data['outlets']) == 1) {
                        $url = url('/assets/admin/base/images/vendors/list/'.$outlets_data['featured_image'].'?'.time());
                        $image ='<a href="'.$store_url.'" title="'.$outlets_data['vendor_name'].'"> <img alt="'.$outlets_data['outlet_name'].'" src="'.$url.'" ></a>';
                    }
                }
                else {
                    if(count($outlets_data['outlets'])>1)
                    {
                        $image ='<a href="javascript:;" title="'.$outlets_data['vendor_name'].'" data-toggle="modal" data-target=".bd-example-modal-lg'.$outlets_data['vendors_id'].'" ><img src="{{ URL::asset("assets/admin/base/images/vendors/stores.png") }}" alt="'.$outlets_data['outlet_name'].'"></a>';
                    }
                    elseif(count($outlets_data['outlets']) == 1) {
                        $image ='<a href="'.$store_url.'" title="'.$outlets_data['vendor_name'].'"><img src="{{ URL::asset("assets/admin/base/images/vendors/stores.png") }}" alt="'.$outlets_data['outlet_name'].'"></a>';
                    }
                }

                $outlet_html ='<script>
                                    $(document ).ready(function() { 
                                        $(".close").on("click", function() { 
                                            $("body").removeClass("modal-open");
                                            $(".modal-backdrop").hide();
                                        });
                                    });
                                </script>';
                if(count($outlets_data['outlets'])>1)
                {
                    $outlet_html .='<div class="modal fade store_detials_list bd-example-modal-lg'.$outlets_data['vendors_id'].'" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button><h4 id="myLargeModalLabel" class="modal-title">'.$outlets_data['vendor_name'].'</h4><p class="store_title">'.count($outlets_data['outlets']).' '.trans("messages.Branches available near you.").'</p></div><div class="store_right_items">';
                    foreach($outlets_data['outlets'] as $outlets)
                    {

						if(isset($post_data['category_ids']) && $post_data['category_ids']){
							$outlets_url = URL::to('store/info/'.$outlets_data['url_index'].'/'.$cat_ids);
		
						}else{
							if($category_url !='')
							{
								$outlets_url = URL::to('store/info/'.$outlets['url_index'].'/'.$category_url);
							}
							else{
								$outlets_url = URL::to('store/info/'.$outlets['url_index']);
							}

						}
						
                        $outlet_html .='<div class="col-md-3 col-sm-3 col-xs-6"><div class="common_item"><div class="store_itm_img">';
                        if(file_exists(base_path().'/public/assets/admin/base/images/vendors/logos/'.$outlets_data['logo_image']))
                        {
                            $url    = url('/assets/admin/base/images/vendors/logos/'.$outlets_data['logo_image'].'?'.time());
                            $oimage = '<a href="'.$outlets_url.'" title="'.$outlets['outlet_name'].'"> <img alt="'.$outlets['outlet_name'].'"  src="'.$url.'" ></a>';
                        }
                        else{
                            $oimage ='<a href="'.$outlets_url.'" title="'.$outlets['outlet_name'].'"><img src="{{ URL::asset("assets/admin/base/images/vendors/stores.png") }}" alt="'.$outlets['outlet_name'].'"></a>';
                        }
                        $distance2 = '<div class="price_sec"><b>'.$outlets['outlets_delivery_time'].' '.trans("messages.Mins").'</b></div>';
                        if( !empty($current_lat) && !empty($current_long) )
                        {
                            $distance2 = '<div class="price_sec"><b>'.$outlets['outlets_delivery_time'].' KM</b></div>';
                        }
                        $outlet_html .=$oimage.$distance2.'</div><div class="store_itm_desc">
                        <a href="'.$outlets_url.'" title="'.$outlets['outlet_name'].'">'.$outlets['outlet_name'].'</a><p>'.substr($outlets['outlets_category'],0,85).'</p></div><div class="store_itm_rating"><h2>
                                <div class="rateit" data-rateit-value='.$outlets['outlets_average_rating'].' data-rateit-ispreset="true" data-rateit-readonly="true">  </div>&nbsp'. $outlets['outlets_average_rating'].' </h2></div><div class="store_itm_rating map_location">
                        <a class="location_location"><i class="glyph-icon flaticon-location-pin"></i>'.substr($outlets['contact_address'],0,50).'</a>
                        </div></div></div>';//.' '.trans("messages.Mins")
                    }
                    $outlet_html .='</div></div></div></div>';
                }
                $more ='';
                if(count($outlets_data['outlets'])>1)
                {
                    $count = count($outlets_data['outlets'])-1;
                    $more .= '<a href="javascript:;" class="right_store" title="'.$count.' '.trans("messages.Branches available").'" data-toggle="modal" data-target=".bd-example-modal-lg'.$outlets_data['vendors_id'].'">'.$count.' '.trans("messages.Branches available").'</a>';
                }
                if(count($outlets_data['outlets']) > 0)
                {
                    $distance1 = '<div class="price_sec"><b>'.$outlets_data['vendors_delivery_time'].' '.trans("messages.Mins").'</b></div>';
                    if( !empty($current_lat) && !empty($current_long) )
                    {
                        $distance1 = '<div class="price_sec"><b>'.$outlets_data['distance_km'].' KM</b></div>';
                    }
                    $html .='<div class="col-md-4 col-sm-4 col-xs-6"><div class="common_item"><div class="store_itm_img">'.$image.$distance1.'</div><div class="store_itm_desc"><a href="javascript:;" data-toggle="modal" data-target=".bd-example-modal-lg'.$outlets_data['vendors_id'].'" title="'.$outlets_data['vendor_name'].'">'.$outlets_data['vendor_name'].'</a><p>'.substr($outlets_data['vendor_category'],0,85).'</p></div><div class="store_itm_rating">'. $more.'<h2><a><div class="rateit" data-rateit-value="'.$outlets_data['vendors_average_rating'].'" data-rateit-ispreset="true" data-rateit-readonly="true"></div></a>'.$outlets_data['vendors_average_rating'].'</h2></div></div></div>'.$outlet_html;
                }
            }
            if(isset($post_data['type']) && $post_data['type']=='web')
            {
				
               if(isset($post_data['filter']) && $post_data['filter']=='1')
               {
				    $result = array("response" => array("httpCode" => 200, "status" => true,'data' =>$html));
				}
				else {
					  $result = array("response" => array("httpCode" => 200, "status" => true,'data' =>utf8_encode($html)));
					}
				
				
            }
            else {
				
                $result = array("response" => array("httpCode" => 200, "status" => true,'data' => $outlets_list));
            }
            //$result = array("response" => array("httpCode" => 200, "status" => true,'data'=>$outlets_list));
        }
        echo $html;exit;
      //  return json_encode($result,JSON_UNESCAPED_UNICODE);
    }
	
}
