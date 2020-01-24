<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Model\countries;
use App\Model\countries_infos;
use App\Model\users;
use App\Model\zones;
use App\Model\zones_infos;
use App\Model\cities;
use App\Model\cities_infos;
use App\Model\currencies_infos;
use App\Model\languages;
use App\Model\currencies;
use App\Model\stock_status;
use App\Model\order_status;
use App\Model\return_status;
use App\Model\return_actions;
use App\Model\return_reasons;
use App\Model\modules;
use Session;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Image;
use MetaTag;
use Mail;
use File;
use SEO;
use SEOMeta;
use OpenGraph;
use Twitter;
use App;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\Input;
use Yajra\Datatables\Datatables;
use URL;
use App\Model\weight_classes;
use App\Model\weight_classes_infos;
use App\Model\delivery_settings;

class Localisation extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->site_name = isset(getAppConfig()->site_name)?ucfirst(getAppConfig()->site_name):'';
        $this->middleware('auth');
        SEOMeta::setTitle($this->site_name);
        SEOMeta::setDescription($this->site_name);
        SEOMeta::addKeyword($this->site_name);
        OpenGraph::setTitle($this->site_name);
        OpenGraph::setDescription($this->site_name);
        OpenGraph::setUrl($this->site_name);
        Twitter::setTitle($this->site_name);
        Twitter::setSite('@'.$this->site_name);
        App::setLocale('en');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function country()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            $query = '"countries_infos"."language_id" = (case when (select count(*) as totalcount from countries_infos where countries_infos.language_id = '.getAdminCurrentLang().' and countries.id = countries_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $countries=DB::table('countries')
             ->select('countries.*','countries_infos.*')
            ->leftJoin('countries_infos','countries_infos.id','=','countries.id')
            ->whereRaw($query)
            ->orderBy('country_name', 'asc')
            ->get();
            return view('admin.country.list')->with('countries', $countries);
        }
        
    }
    
   /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function zones()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            $query = '"zones_infos"."language_id" = (case when (select count(*) as totalcount from zones_infos where zones_infos.language_id = '.getAdminCurrentLang().' and zones.id = zones_infos.zone_id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $zones=DB::table('zones')
             ->select('zones.*','zones_infos.*')
            ->leftJoin('zones_infos','zones_infos.zone_id','=','zones.id')
            ->whereRaw($query)
            ->orderBy('zone_name', 'asc')  
            ->get(); 
            return view('admin.zones.list')->with('zones', $zones);
        }
        
    }
    
            /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function city()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            $query = '"cities_infos"."language_id" = (case when (select count(*) as totalcount from cities_infos where cities_infos.language_id = '.getAdminCurrentLang().' and cities.id = cities_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $cities=DB::table('cities')
             ->select(DB::raw('cities.* ,cities.id as cid'),'cities_infos.*')
            ->leftJoin('cities_infos','cities_infos.id','=','cities.id')
            ->whereRaw($query)
            ->orderBy('city_name', 'asc')
            ->get();
            return view('admin.cities.list')->with('cities', $cities);
        }
        
    }
    
    
    public function country_create()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            return view('admin.country.create');
        }
    }
    
    public function zones_create()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            return view('admin.zones.create');
        }
    }
    
    public function city_create()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            return view('admin.cities.create');
        }
    }

    public function country_edit($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            $info = new Countries_infos;
            $countries = Countries::find($id);
            return view('admin.country.edit')->with('data', $countries)->with('infomodel', $info);
        }
    }
    
    public function zone_edit($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            $info = new Zones_infos;
            $zones = Zones::find($id);
            return view('admin.zones.edit')->with('data', $zones)->with('infomodel', $info);
        }
    }
    
    public function city_edit($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            $info = new Cities_infos;
            $cities = Cities::find($id);
            return view('admin.cities.edit')->with('data', $cities)->with('infomodel', $info);
        }
    }
    
    public function country_store(Request $data)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $fields['iso_code'] = Input::get('iso_code');
        $fields['alpha_code'] = Input::get('alpha_code');
        $fields['country_isd_code'] = Input::get('country_isd_code');
        $rules = array(
            //'iso_code'         => 'required|integer|min:004|max:894',
            //'alpha_code'       => 'required|alpha|max:2',
            //'country_isd_code' => 'required|integer|min:1|max:998',
        );
        $country_name = Input::get('country_name');
        foreach ($country_name  as $key => $value) {
            $fields['country_name'.$key] = $value;
            $rules['country_name'.'1'] = 'required|unique:countries_infos,country_name';
            
        }
        $validator = Validator::make($fields, $rules);    
        // process the validation
        if ($validator->fails())
        { 
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            try{

                $Countries = new Countries;
                $Countries->url_index =  $_POST['country_name'][1] ? str_slug($_POST['country_name'][1]): str_slug($_POST['country_name'][1]);
                $Countries->iso_code = $_POST['iso_code'];
                $Countries->alpha_code = $_POST['alpha_code'];
                $Countries->country_isd_code = $_POST['country_isd_code'];
                $Countries->created_at = date("Y-m-d H:i:s");          
                $Countries->updated_at = date("Y-m-d H:i:s");
                $Countries->country_status =  isset($_POST['status']) ? $_POST['status']: 0;
                //$this->country_save_before($Countries,$_POST);
                $Countries->save();
                $this->country_save_after($Countries,$_POST);
                Session::flash('message', trans('messages.Country has been added successfully'));
            }catch(Exception $e) {
                    Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/localisation/country');
        }
    }
    
    
    public function zone_store(Request $data)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $fields['country'] = Input::get('country');
        $fields['city'] = Input::get('city');
        $rules = array(
            'country' => 'required',
            'city' => 'required',
        );
        $zone_name = Input::get('zone_name');
        foreach ($zone_name  as $key => $value) {
            $fields['zone_name'.$key] = $value;
            $rules['zone_name'.'1'] = 'required|unique:zones_infos,zone_name';
            
        }
        $validator = Validator::make($fields, $rules);    
                // process the validation
        if ($validator->fails())
        { 
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            try{
                $Zones = new Zones;
                $Zones->url_index =  $_POST['zone_name'][1] ? str_slug($_POST['zone_name'][1]): str_slug($_POST['zone_name'][1]);
                $Zones->city_id = $_POST['city'];
                $Zones->country_id = $_POST['country'];
                $Zones->created_at = date("Y-m-d H:i:s");
                $Zones->zones_status =  isset($_POST['status']) ? $_POST['status']: 0;
                $Zones->save();
                $this->zone_save_after($Zones,$_POST);
                Session::flash('message', trans('messages.Zone has been added successfully'));
            }catch(Exception $e) {
                    Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/localisation/zones');
        }
    }
    
    public function city_store(Request $data)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $fields['zone_code'] = Input::get('zone_code');
        $fields['country'] = Input::get('country');
        $rules = array(
            'zone_code' => 'required|numeric',
            'country' => 'required',
        );
        $city_name = Input::get('city_name');
        foreach ($city_name  as $key => $value) {
            $fields['city_name'.$key] = $value;
            $rules['city_name'.'1'] = 'required|regex:/(^[A-Za-z0-9 ]+$)+/|unique:cities_infos,city_name';
            
        }
        $validator = Validator::make($fields, $rules);    
                // process the validation
        if ($validator->fails())
        { 
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            try{

                $Cities = new Cities;
                $Cities->url_index =  $_POST['city_name'][1] ? str_slug($_POST['city_name'][1]): str_slug($_POST['city_name'][1]);
                $Cities->zone_code = $_POST['zone_code'];
                $Cities->country_id = $_POST['country'];
                $Cities->created_date = date("Y-m-d H:i:s");          
                $Cities->modified_date = date("Y-m-d H:i:s");
                $Cities->default_status =  isset($_POST['status']) ? $_POST['status']: 0;
                //$this->country_save_before($Countries,$_POST);
                $Cities->save();
                $this->city_save_after($Cities,$_POST);
                Session::flash('message', trans('messages.City has been added successfully'));
            }catch(Exception $e) {
                    Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/localisation/city');
        }
    }
    
        /**
     * Update the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function country_update(Request $data, $id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $fields['iso_code']         = Input::get('iso_code');
        $fields['alpha_code']       = Input::get('alpha_code');
        $fields['country_isd_code'] = Input::get('country_isd_code');
        $rules = array(
           // 'iso_code'         => 'required|integer|min:004|max:894',
            //'alpha_code'       => 'required|alpha|max:2',
            //'country_isd_code' => 'required|integer|min:1|max:998',
        );
        $country_name = Input::get('country_name');
        foreach ($country_name  as $key => $value) {
            $fields['country_name'.$key] = $value;
            $rules['country_name'.'1'] = 'required|unique:countries_infos,country_name,'.$id;
            
        }
        $validator = Validator::make($fields, $rules);    
                // process the validation
        if ($validator->fails())
        { 
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            try{
                $Countries = Countries::find($id); 
                $Countries->url_index =  $_POST['country_name'][1] ? str_slug($_POST['country_name'][1]): str_slug($_POST['country_name'][1]);
                $Countries->iso_code = $_POST['iso_code'];
                $Countries->alpha_code = $_POST['alpha_code'];
                $Countries->country_isd_code = $_POST['country_isd_code'];          
                $Countries->updated_at = date("Y-m-d H:i:s");
                $Countries->country_status =  isset($_POST['status']) ? $_POST['status']: 0;
                //$this->country_save_before($Countries,$_POST);
                $Countries->save();
                $this->country_save_after($Countries,$_POST);
                Session::flash('message', trans('messages.Country has been updated successfully'));
            }catch(Exception $e) {
                    Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/localisation/country');
        }
    }
    
    /**
     * Update the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function zone_update(Request $data, $id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $fields['country'] = Input::get('country');
        $fields['city'] = Input::get('city');
        $rules = array(
            'country' => 'required',
            'city' => 'required',
        );
        $zone_name = Input::get('zone_name');
        foreach ($zone_name  as $key => $value) {
            $fields['zone_name'.$key] = $value;
            $rules['zone_name'.'1'] = 'required|unique:zones_infos,zone_name,'.$id.',zone_id';
            
        }
        
        $validator = Validator::make($fields, $rules);    
        // process the validation
        if ($validator->fails())
        { 
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            try{
                $Zones = Zones::find($id); 
                $Zones->url_index =  $_POST['zone_name'][1] ? str_slug($_POST['zone_name'][1]): str_slug($_POST['zone_name'][1]);
                $Zones->country_id = $_POST['country'];       
                $Zones->updated_at = date("Y-m-d H:i:s");
                $Zones->zones_status =  isset($_POST['status']) ? $_POST['status']: 0;
                $Zones->save();
                $this->zone_save_after($Zones,$_POST);
                Session::flash('message', trans('messages.Zones has been updated successfully'));
            }catch(Exception $e) {
                    Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/localisation/zones');
        }
    }
    
    
    /**
     * Update the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function city_update(Request $data, $id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $fields['zone_code'] = Input::get('zone_code');
        $fields['country'] = Input::get('country');
        $rules = array(
            'zone_code' => 'required|integer',
            'country' => 'required',
        );
        $city_name = Input::get('city_name');
        foreach ($city_name  as $key => $value) {
            $fields['city_name'.$key] = $value;
            $rules['city_name'.'1'] = 'required|regex:/(^[A-Za-z0-9 ]+$)+/|unique:cities_infos,city_name,'.$id.',id';
            
        }
        
        $validator = Validator::make($fields, $rules);    
        // process the validation
        if ($validator->fails())
        { 
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            
            try{
                
                $Cities = Cities::find($id); 
                $Cities->url_index =  $_POST['city_name'][1] ? str_slug($_POST['city_name'][1]): str_slug($_POST['city_name'][1]);
                $Cities->zone_code = $_POST['zone_code'];
                $Cities->country_id = $_POST['country'];       
                $Cities->modified_date = date("Y-m-d H:i:s");
                $Cities->default_status =  isset($_POST['status']) ? $_POST['status']: 0;
                $Cities->save();
                $this->city_save_after($Cities,$_POST);
                Session::flash('message', trans('messages.City has been updated successfully'));
            }catch(Exception $e) {
                    Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/localisation/city');
        }
    }
        /**
     * add,edit datas  saved in main table 
     * after inserted in sub tabel.
     *
     * @param  int  $id
     * @return Response
     */
   public static function country_save_after($object,$post)
   {
        $country = $object;
        $post = $post;
        if(isset($post['country_name'])){
            $country_name = $post['country_name'];
            try{
                $data = Countries_infos::find($object->id);
                if(count($data)>0){
                    $data->delete();
                }
                $languages = DB::table('languages')->where('status', 1)->get();
                foreach($languages as $key => $lang){
                    if(isset($country_name[$lang->id]) && $country_name[$lang->id]!=""){
                        $infomodel = new Countries_infos;
                        $infomodel->language_id = $lang->id;
                        $infomodel->id = $object->id; 
                        $infomodel->country_name = $country_name[$lang->id];
                        $infomodel->save();
                    }
                }
                }catch(Exception $e) {
                    
                    Log::Instance()->add(Log::ERROR, $e);
                }
        }
   }
   
           /**
     * add,edit datas  saved in main table 
     * after inserted in sub tabel.
     *
     * @param  int  $id
     * @return Response
     */
   public static function zone_save_after($object,$post)
   {
        $zone = $object;
        $post = $post;
        if(isset($post['zone_name'])){
            $zone_name = $post['zone_name'];
            try{
                $affected = DB::table('zones_infos')->where('zone_id', '=', $object->id)->delete();
                $languages = DB::table('languages')->where('status', 1)->get();
                foreach($languages as $key => $lang){
                    if(isset($zone_name[$lang->id]) && $zone_name[$lang->id]!=""){
                        $infomodel = new Zones_infos;
                        $infomodel->language_id = $lang->id;
                        $infomodel->zone_id = $object->id; 
                        $infomodel->zone_name = $zone_name[$lang->id];
                        $infomodel->save();
                    }
                }
            }catch(Exception $e) {
                Log::Instance()->add(Log::ERROR, $e);
            }
        }
   }
   
   
   
              /**
     * add,edit datas  saved in main table 
     * after inserted in sub tabel.
     *
     * @param  int  $id
     * @return Response
     */
   public static function city_save_after($object,$post)
   {
        $city = $object;
        $post = $post;
        if(isset($post['city_name'])){
            $city_name = $post['city_name'];
            try{                
                $affected = DB::table('cities_infos')->where('id', '=', $object->id)->delete();
                $languages = DB::table('languages')->where('status', 1)->get();
                foreach($languages as $key => $lang){
                    if(isset($city_name[$lang->id]) && $city_name[$lang->id]!=""){
                        $infomodel = new Cities_infos;
                        $infomodel->language_id = $lang->id;
                        $infomodel->id = $object->id; 
                        $infomodel->city_name = $city_name[$lang->id];
                        $infomodel->save();
                    }
                }
                }catch(Exception $e) {
                    Log::Instance()->add(Log::ERROR, $e);
                }
        }
   }
   
       /**
     * Delete the specified country in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function country_destory($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $data = Countries::find($id);
        $data->delete();
        Session::flash('message', trans('messages.Country has been deleted successfully!'));
        return Redirect::to('admin/localisation/country');
    }
    
    
           /**
     * Delete the specified country in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function zone_destory($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $data = Zones::find($id);
        $data->delete();
        Session::flash('message', trans('messages.Zone has been deleted successfully!'));
        return Redirect::to('admin/localisation/zones');
    }
    
    
    
               /**
     * Delete the specified country in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function city_destory($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $data = Cities::find($id);
        $data->delete();
        Session::flash('message', trans('messages.City has been deleted successfully!'));
        return Redirect::to('admin/localisation/city');
    }

    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxCountry()
    {
        $query = '"countries_infos"."language_id" = (case when (select count(*) as totalcount from countries_infos where countries_infos.language_id = '.getAdminCurrentLang().' and countries.id = countries_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $country = Countries::Leftjoin('countries_infos','countries_infos.id','=','countries.id')
        ->select('countries.*','countries_infos.*')
        ->whereRaw($query)
        ->orderBy('countries.id', 'desc')
        ->get();
        return Datatables::of($country)->addColumn('action', function ($country) {
                return '<div class="btn-group"><a href="'.URL::to("admin/country/edit/".$country->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                        <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/country/delete/".$country->id).'" class="delete-'.$country->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                        </ul>
                    </div><script type="text/javascript">
                    $( document ).ready(function() {
                    $(".delete-'.$country->id.'").on("click", function(){
                         return confirm("'.trans("messages.Are you sure want to delete?").'");
                    });});</script>';
            })
            ->addColumn('country_isd_code', function ($country) {
                    $data = '-';
                    if($country->country_isd_code != ''):
                    $data = $country->country_isd_code;
                    endif;
                    return $data;
            })
            ->addColumn('alpha_code', function ($country) {
                    $data = '-';
                    if($country->alpha_code != ''):
                    $data = $country->alpha_code;
                    endif;
                    return $data;
            })
            ->addColumn('iso_code', function ($country) {
                    $data = '-';
                    if($country->iso_code != ''):
                    $data = $country->iso_code;
                    endif;
                    return $data;
            })
             ->rawColumns(['iso_code','alpha_code','country_isd_code','action'])

            ->make(true);
    }
    
    
    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxZones()
    {
        
        $country=getCountryLists();
        $query = '"zones_infos"."language_id" = (case when (select count(*) as totalcount from zones_infos where zones_infos.language_id = '.getAdminCurrentLang().' and zones.id = zones_infos.zone_id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $zones = Zones::Leftjoin('zones_infos','zones_infos.zone_id','=','zones.id')
        ->join('countries','countries.id','=','zones.country_id')
        ->join('countries_infos','countries_infos.id','=','countries.id')
        ->join('cities','cities.id','=','zones.city_id')
        ->join('cities_infos','cities_infos.id','=','cities.id')
        ->select(DB::raw('zones.* ,zones.id as zid'),'zones_infos.*',"countries_infos.*","cities_infos.*")
        ->where("countries_infos.language_id","=",getAdminCurrentLang())
        ->where("cities_infos.language_id","=",getAdminCurrentLang())
        ->whereRaw($query)
        ->get();
        return Datatables::of($zones)->addColumn('action', function ($zones) {
                return '<div class="btn-group"><a href="'.URL::to("admin/zones/edit/".$zones->zid).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                        <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/zones/delete/".$zones->zid).'" class="delete-'.$zones->zid.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                        </ul>
                    </div><script type="text/javascript">
                    $( document ).ready(function() {
                    $(".delete-'.$zones->zid.'").on("click", function(){
                         return confirm("'.trans("messages.Are you sure want to delete?").'");
                    });});</script>';
            })
            ->addColumn('zones_status', function ($zones) {
                if($zones->zones_status==0):
                    $data = '<span class="label label-danger">'.trans("messages.Inactive").'</span>';
                elseif($zones->zones_status==1):
                    $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
                endif;
                return $data;
            })
            ->rawColumns(['zones_status','action'])

            ->make(true);
    }
    
    
    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxCities()
    {
        $country=getCountryLists();
        $query = '"cities_infos"."language_id" = (case when (select count(*) as totalcount from cities_infos where cities_infos.language_id = '.getAdminCurrentLang().' and cities.id = cities_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $cities = Cities::Leftjoin('cities_infos','cities_infos.id','=','cities.id')
        ->join('countries','countries.id','=','cities.country_id')
        ->join('countries_infos','countries_infos.id','=','countries.id')
        ->select(DB::raw('cities.* ,cities.id as cid'),'cities_infos.*',"countries_infos.*")
        ->where("countries_infos.language_id","=",getAdminCurrentLang())
        ->whereRaw($query)
        ->orderBy('cities.id', 'desc')
        ->get();
        return Datatables::of($cities)->addColumn('action', function ($cities) {
                return '<div class="btn-group"><a href="'.URL::to("admin/city/edit/".$cities->cid).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                        <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/city/delete/".$cities->cid).'" class="delete-'.$cities->cid.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                        </ul>
                    </div><script type="text/javascript">
                    $( document ).ready(function() {
                    $(".delete-'.$cities->cid.'").on("click", function(){
                         return confirm("'.trans("messages.Are you sure want to delete?").'");
                    });});</script>';
            })
            ->addColumn('default_status', function ($cities) {
                if($cities->default_status==0):
                    $data = '<span class="label label-danger">'.trans("messages.Inactive").'</span>';
                elseif($cities->default_status==1):
                    $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
                endif;
                return $data;
            })
            ->addColumn('city_name', function ($cities) {
                    $data = ucfirst($cities->city_name);
                return $data;
            })
            ->rawColumns(['city_name','default_status','action'])

            ->make(true);
    }

    /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function weight_classes()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }
        else{
            // Section description
            // get all the weight classes
            $skey = app('request')->input('skey');
            $condition="";
            if($skey){
                $condition=' and "weight_classes_infos"."title" Ilike '."'".'%'.trim($skey).'%'."'";
            }        
            $query = '"weight_classes_infos"."lang_id" = (case when (select count(*) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = '.getAdminCurrentLang().' and weight_classes.id = weight_classes_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)'.$condition.'';
            $weight_classes=DB::table('weight_classes')
             ->select('weight_classes.*','weight_classes_infos.*')
            ->leftJoin('weight_classes_infos','weight_classes_infos.id','=','weight_classes.id')
            ->whereNotIn('active_status', [2])
            ->whereRaw($query)
            ->orderBy('title', 'asc')
            ->paginate(10);  
            // load the list view (resources/views/admin/weight_classes/create.blade.php)
            return view('admin.weight_classes.list')->with('weight_classes', $weight_classes);
        }
    }

    /**
     * Show the form for creating a new weight classes.
     *
     * @return Response
     */
    public function weight_class_create()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()) {
            return redirect()->guest('admin/login');
        } else {
            // load the create form (resources/views/admin/weight_classes/create.blade.php)
            return view('admin.weight_classes.create');
        }
    }
    
    /**
     * Store a newly created weight classes in storage.
     *
     * @return Response
     */
    public function weight_class_store(Request $data)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation
       /* $fields['weight_value'] = Input::get('weight_value');
        $rules = array(
            'weight_value' => 'required',
        );
        **/ 
        $weight_title = Input::get('weight_title');
        foreach ($weight_title  as $key => $value) {
            $fields['weight_title'.$key] = $value;
            $rules['weight_title'.'1'] = 'required|unique:weight_classes_infos,title';
        }
        /*$weight_unit = Input::get('weight_unit');
        foreach ($weight_unit  as $key => $value) {
            $fields['weight_unit'.$key] = $value;
            $rules['weight_unit'.'1'] = 'required|unique:weight_classes_infos,unit';
        }*/ 
        $validation = Validator::make($fields, $rules);
        // process the validation
        if ($validation->fails()) {
                return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store the data here            
           try{
                $weight_classes = new weight_classes;
                $weight_classes->weight_value =  $_POST['weight_value'];
                $weight_classes->created_date = date("Y-m-d H:i:s");
                $weight_classes->active_status = isset($_POST['active_status']) ? $_POST['active_status']: 0;
                $weight_classes->save();
                $this->weight_classes_save_after($weight_classes,$_POST);
                Session::flash('message', trans('messages.Weight class has been added successfully'));
            }catch(Exception $e) {
                Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/localisation/weight_classes');
        }
    }

    /**
     * add,edit datas  saved in main table 
     * after inserted in sub tabel.
     *
     * @param  int  $id
     * @return Response
     */
   public static function weight_classes_save_after($object,$post)
   {
        if(isset($post['weight_title']) && isset($post['weight_unit'])){
            $weight_title = $post['weight_title'];
            $weight_unit = $post['weight_unit'];
            try{
                $data = weight_classes_infos::find($object->id);
                
                if(count($data)>0){
                    $data->delete();
                }
                $languages = DB::table('languages')->where('status', 1)->get();
                foreach($languages as $key => $lang){
                    if((isset($weight_title[$lang->id]) && $weight_title[$lang->id]!="")){
                        $infomodel = new weight_classes_infos;
                        $infomodel->lang_id = $lang->id;
                        $infomodel->id = $object->id; 
                        $infomodel->title = $weight_title[$lang->id];
                        $infomodel->unit = $weight_unit[$lang->id];
                        $infomodel->save();
                    }
                }
            }catch(Exception $e) {
                Log::Instance()->add(Log::ERROR, $e);
            }
        }
   }
    /*
     * Edit the corresponding weight class
    */
    public function weight_class_edit($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            $info = new Weight_classes_infos;
            $weight_classes = Weight_classes::find($id);
            return view('admin.weight_classes.edit')->with('data', $weight_classes)->with('infomodel', $info);
        }
    }

    /**
     * Update the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function weight_class_update(Request $data, $id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation
        /** $fields['weight_value'] = Input::get('weight_value');
        $rules = array(
            'weight_value' => 'required',
        );
        **/ 
        $weight_title = Input::get('weight_title');
        foreach ($weight_title  as $key => $value) {
            $fields['weight_title'.$key] = $value;
            $rules['weight_title'.'1'] = 'required|unique:weight_classes_infos,title,'.$id;
        }
        /** 
        $weight_unit = Input::get('weight_unit');
        foreach ($weight_unit  as $key => $value) {
            $fields['weight_unit'.$key] = $value;
            $rules['weight_unit'.'1'] = 'required|unique:weight_classes_infos,unit,'.$id;
        }
        **/ 
        $validator = Validator::make($fields, $rules);
        // process the validation
        if ($validator->fails())
        { 
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            try{
                $weight_classes = weight_classes::find($id);
                $weight_classes->weight_value =  $_POST['weight_value'];
                $weight_classes->created_date = date("Y-m-d H:i:s");
                $weight_classes->active_status = isset($_POST['active_status']) ? $_POST['active_status']: 0;
                $weight_classes->save();
                $this->weight_classes_save_after($weight_classes,$_POST);
                Session::flash('message', trans('messages.Weight class has been updated successfully'));
            }catch(Exception $e) {
                    Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/localisation/weight_classes');
        }
    }

    /**
     * Delete the specified country in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function weight_class_destory($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $data = weight_classes::find($id);
        //Update delete status while deleting
        $data->active_status = 2;
        $data->save();
        Session::flash('message', trans('messages.Weight class has been deleted successfully'));
        return Redirect::to('admin/localisation/weight_classes');
    }

    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxWeightClasses()
    {
        $query = '"weight_classes_infos"."lang_id" = (case when (select count(*) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = '.getAdminCurrentLang().' and weight_classes.id = weight_classes_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $weight_classes = weight_classes::Leftjoin('weight_classes_infos','weight_classes_infos.id','=','weight_classes.id')
        ->select('weight_classes.*','weight_classes_infos.*')
        ->whereNotIn('active_status', [2])
        ->whereRaw($query)
        ->orderBy('weight_classes.id', 'desc')
        ->get();
        return Datatables::of($weight_classes)->addColumn('action', function ($weight_classes) {
                return '<div class="btn-group"><a href="'.URL::to("admin/localisation/edit_weight_class/".$weight_classes->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                        <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/localisation/delete_weight_class/".$weight_classes->id).'" class="delete-'.$weight_classes->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                        </ul>
                    </div><script type="text/javascript">
                    $( document ).ready(function() {
                    $(".delete-'.$weight_classes->id.'").on("click", function(){
                         return confirm("'.trans("messages.Are you sure want to delete?").'");
                    });});</script>';
            })
            ->addColumn('active_status', function ($weight_classes) {
                if($weight_classes->active_status==2):
                    $data = '<span class="label label-danger">'.trans("messages.delete").'</span>';
                elseif($weight_classes->active_status==1):
                    $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
                else:
                    $data = '<span class="label label-warning">'.trans("messages.Inactive").'</span>';
                endif;
                return $data;
            })
            ->rawColumns(['active_status','action'])

            ->make(true);
    }

    /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function stock_statuses()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }
        else{
             $stock=DB::table('stock_status')
             ->select('*')
            ->orderBy('id', 'asc')
            ->get();
            // load the list view (resources/views/admin/weight_classes/create.blade.php)
            return view('admin.stock.list')->with('stock', $stock);
        }
    }
    
        /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function return_statuses()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }
        else{
             $return_statuses=DB::table('return_status')
             ->select('*')
            ->orderBy('id', 'asc')
            ->get();
            // load the list view (resources/views/admin/weight_classes/create.blade.php)
            return view('admin.order.returns.status.list')->with('returnstatuses', $return_statuses);
        }
    }
    
            /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function return_actions()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }
        else{
             $return_actions=DB::table('return_action')
             ->select('*')
            ->orderBy('id', 'asc')
            ->get();
            return view('admin.order.returns.actions.list')->with('returnactions', $return_actions);
        }
    }
    
                /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function return_reasons()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }
        else{
             $return_reasons=DB::table('return_reason')
             ->select('*')
            ->orderBy('id', 'asc')
            ->get();
            return view('admin.order.returns.reasons.list')->with('returnreasons', $return_reasons);
        }
    }
    
    
        /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function order_statuses()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }
        else{
             $stock=DB::table('order_status')
             ->select('*')
            ->orderBy('id', 'asc')
            ->get();
            // load the list view (resources/views/admin/weight_classes/create.blade.php)
            return view('admin.order.status.list')->with('stock', $stock);
        }
    }

    /**
     * Show the form for creating a new weight classes.
     *
     * @return Response
     */
    public function stock_status_create()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()) {
            return redirect()->guest('admin/login');
        } else {
            // load the create form (resources/views/admin/weight_classes/create.blade.php)
            return view('admin.stock.create');
        }
    }
    
    /**
     * Show the form for creating a new weight classes.
     *
     * @return Response
     */
    public function order_status_create()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()) {
            return redirect()->guest('admin/login');
        } else {
            // load the create form (resources/views/admin/weight_classes/create.blade.php)
            return view('admin.order.status.create');
        }
    }
    
    
        /**
     * Show the form for creating a new weight classes.
     *
     * @return Response
     */
    public function return_status_create()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()) {
            return redirect()->guest('admin/login');
        } else {
            // load the create form (resources/views/admin/weight_classes/create.blade.php)
            return view('admin.order.returns.status.create');
        }
    }
    
    
    /**
     * Show the form for creating a new weight classes.
     *
     * @return Response
     */
    public function return_action_create()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()) {
            return redirect()->guest('admin/login');
        } else {
            return view('admin.order.returns.actions.create');
        }
    }
    
    
    /**
     * Show the form for creating a new weight classes.
     *
     * @return Response
     */
    public function return_reason_create()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()) {
            return redirect()->guest('admin/login');
        } else {
            // load the create form (resources/views/admin/weight_classes/create.blade.php)
            return view('admin.order.returns.reasons.create');
        }
    }
    

    /**
     * Store a newly created weight classes in storage.
     *
     * @return Response
     */
    public function stock_status_store(Request $data)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation
        $fields['status_name'] = Input::get('status_name');
        $rules = array(
            'status_name' => 'required|unique:stock_status,name',
        );
        $validation = Validator::make($fields, $rules);
        // process the validation
        if ($validation->fails()) {
                return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store the data here            
           try{
                $stock_status = new Stock_status;
                $stock_status->name =  $_POST['status_name'];
                $stock_status->save();
                Session::flash('message', trans('messages.Stock status name has been added successfully'));
            }catch(Exception $e) {
                Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/localisation/stockstatuses');
        }
    }
    
        /**
     * Store a newly created weight classes in storage.
     *
     * @return Response
     */
    public function order_status_store(Request $data)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation
        $fields['status_name'] = Input::get('status_name');
        $rules = array(
            'status_name' => 'required|unique:order_status,name',
        );
        $validation = Validator::make($fields, $rules);
        // process the validation
        if ($validation->fails()) {
                return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store the data here            
           try{
                $order_status = new Order_status;
                $order_status->name =  $_POST['status_name'];
                $order_status->save();
                Session::flash('message', trans('messages.Order status name has been added successfully'));
            }catch(Exception $e) {
                Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/localisation/orderstatuses');
        }
    }


        /**
     * Store a newly created weight classes in storage.
     *
     * @return Response
     */
    public function return_status_store(Request $data)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation
        $fields['status_name'] = Input::get('status_name');
        $rules = array(
            'status_name' => 'required|unique:return_status,name',
        );
        $validation = Validator::make($fields, $rules);
        // process the validation
        if ($validation->fails()) {
                return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store the data here            
           try{
                $return_status = new Return_status;
                $return_status->name =  $_POST['status_name'];
                $return_status->save();
                Session::flash('message', trans('messages.Return status name has been added successfully'));
            }catch(Exception $e) {
                Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/localisation/returnstatuses');
        }
    }


    /**
     * Store a newly created weight classes in storage.
     *
     * @return Response
     */
    public function return_action_store(Request $data)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation
        $fields['action_name'] = Input::get('action_name');
        $rules = array(
            'action_name' => 'required|unique:return_action,name',
        );
        $validation = Validator::make($fields, $rules);
        // process the validation
        if ($validation->fails()) {
                return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store the data here            
           try{
                $return_action = new Return_actions;
                $return_action->name =  $_POST['action_name'];
                $return_action->save();
                Session::flash('message', trans('messages.Return Action  name has been added successfully'));
            }catch(Exception $e) {
                Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/localisation/returnactions');
        }
    }
    
        /**
     * Store a newly created weight classes in storage.
     *
     * @return Response
     */
    public function return_reason_store(Request $data)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation
        $fields['reason_name'] = Input::get('reason_name');
        $rules = array(
            'reason_name' => 'required|unique:return_reason,name',
        );
        $validation = Validator::make($fields, $rules);
        // process the validation
        if ($validation->fails()) {
                return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store the data here            
           try{
                $return_reason = new Return_Reasons;
                $return_reason->name =  $_POST['reason_name'];
                $return_reason->save();
                Session::flash('message', trans('messages.Return reason  name has been added successfully'));
            }catch(Exception $e) {
                Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/localisation/returnreasons');
        }
    }
    
    /*
     * Edit the corresponding weight class
    */
    public function stock_status_edit($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            $stock_status = Stock_status::find($id);
            return view('admin.stock.edit')->with('data', $stock_status);
        }
    }
    
        /*
     * Edit the corresponding weight class
    */
    public function order_status_edit($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            $order_status = Order_status::find($id);
            return view('admin.order.status.edit')->with('data', $order_status);
        }
    }
    
        /*
     * Edit the corresponding weight class
    */
    public function return_status_edit($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            $return_status = Return_status::find($id);
            return view('admin.order.returns.status.edit')->with('data', $return_status);
        }
    }
    
            /*
     * Edit the corresponding weight class
    */
    public function return_action_edit($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            $return_action = Return_actions::find($id);
            return view('admin.order.returns.actions.edit')->with('data', $return_action);
        }
    }
    
                /*
     * Edit the corresponding weight class
    */
    public function return_reason_edit($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            $return_reason = Return_reasons::find($id);
            return view('admin.order.returns.reasons.edit')->with('data', $return_reason);
        }
    }

    /**
     * Update the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function stock_status_update(Request $data, $id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation        
        $fields['status_name'] = Input::get('status_name');
        $rules = array(
            'status_name' => 'required|unique:stock_status,name,'.$id,
        );
        $validator = Validator::make($fields, $rules);
        // process the validation
        if ($validator->fails())
        { 
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            try{
                $stock_status = Stock_status::find($id);
                $stock_status->name =  $_POST['status_name'];
                $stock_status->save();
                Session::flash('message', trans('messages.Stock status name has been updated successfully'));
            }catch(Exception $e) {
                    Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/localisation/stockstatuses');
        }
    }
    
        /**
     * Update the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function order_status_update(Request $data, $id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation        
        $fields['status_name'] = Input::get('status_name');
        $rules = array(
            'status_name' => 'required|unique:order_status,name,'.$id,
        );
        $validator = Validator::make($fields, $rules);
        // process the validation
        if ($validator->fails())
        { 
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            try{
                $order_status = Order_status::find($id);
                $order_status->name =  $_POST['status_name'];
                $order_status->save();
                Session::flash('message', trans('messages.Order status name has been updated successfully'));
            }catch(Exception $e) {
                    Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/localisation/orderstatuses');
        }
    }
    
    
        /**
     * Update the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function return_status_update(Request $data, $id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation        
        $fields['status_name'] = Input::get('status_name');
        $rules = array(
            'status_name' => 'required|unique:return_status,name,'.$id,
        );
        $validator = Validator::make($fields, $rules);
        // process the validation
        if ($validator->fails())
        { 
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            try{
                $return_status = Return_status::find($id);
                $return_status->name =  $_POST['status_name'];
                $return_status->save();
                Session::flash('message', trans('messages.Return status name has been updated successfully'));
            }catch(Exception $e) {
                    Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/localisation/returnstatuses');
        }
    }


    /**
     * Update the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function return_action_update(Request $data, $id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation        
        $fields['action_name'] = Input::get('action_name');
        $rules = array(
            'action_name' => 'required|unique:return_action,name,'.$id,
        );
        $validator = Validator::make($fields, $rules);
        // process the validation
        if ($validator->fails())
        { 
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            try{
                $return_action = Return_actions::find($id);
                $return_action->name =  $_POST['action_name'];
                $return_action->save();
                Session::flash('message', trans('messages.Return action name has been updated successfully'));
            }catch(Exception $e) {
                    Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/localisation/returnactions');
        }
    }
    
      /**
     * Update the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function return_reason_update(Request $data, $id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation        
        $fields['reason_name'] = Input::get('reason_name');
        $rules = array(
            'reason_name' => 'required|unique:return_reason,name,'.$id,
        );
        $validator = Validator::make($fields, $rules);
        // process the validation
        if ($validator->fails())
        { 
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            try{
                $reason_name = Return_reasons::find($id);
                $reason_name->name =  $_POST['reason_name'];
                $reason_name->save();
                Session::flash('message', trans('messages.Return reason name has been updated successfully'));
            }catch(Exception $e) {
                    Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/localisation/returnreasons');
        }
    }
    
    
        /**
     * Delete the specified country in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function return_action_destory($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $data = Return_actions::find($id);
        //Update delete status while deleting
        $data->delete();
        Session::flash('message', trans('messages.Return action has been deleted successfully'));
        return Redirect::to('admin/localisation/returnactions');
    }
    
    /**
     * Delete the specified country in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function stock_status_destory($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $data = Stock_status::find($id);
        //Update delete status while deleting
        $data->delete();
        Session::flash('message', trans('messages.Stock status has been deleted successfully'));
        return Redirect::to('admin/localisation/stockstatuses');
    }
    
    
        /**
     * Delete the specified country in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function order_status_destory($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $data = Order_status::find($id);
        //Update delete status while deleting
        $data->delete();
        Session::flash('message', trans('messages.Order status has been deleted successfully'));
        return Redirect::to('admin/localisation/orderstatuses');
    }
    
    
            /**
     * Delete the specified country in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function return_status_destory($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $data = Return_status::find($id);
        //Update delete status while deleting
        $data->delete();
        Session::flash('message', trans('messages.Return status has been deleted successfully'));
        return Redirect::to('admin/localisation/returnstatuses');
    }
    
                /**
     * Delete the specified country in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function return_reason_destory($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $data = Return_reasons::find($id);
        //Update delete status while deleting
        $data->delete();
        Session::flash('message', trans('messages.Return resoan has been deleted successfully'));
        return Redirect::to('admin/localisation/returnreasons');
    }

    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxStockStatus()
    {

        $stock_status = DB::table('stock_status')->select('*')->orderBy('id', 'desc');
        return Datatables::of($stock_status)->addColumn('action', function ($stock_status) {
                return '<div class="btn-group"><a href="'.URL::to("admin/stockstatus/editstockstatus/".$stock_status->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                        <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/localisation/delete_stock_status/".$stock_status->id).'" class="delete-'.$stock_status->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                        </ul>
                    </div><script type="text/javascript">
                    $( document ).ready(function() {
                    $(".delete-'.$stock_status->id.'").on("click", function(){
                         return confirm("'.trans("messages.Are you sure want to delete?").'");
                    });});</script>';
            })
            ->addColumn('name', function ($stock_status) {
                    $data = $stock_status->name;
                return $data;
            })
            ->rawColumns(['name','action'])

            ->make(true);
    }
    
        /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxOrderStatus()
    {

        $order_status = DB::table('order_status')->select('*')->orderBy('id', 'desc');
        return Datatables::of($order_status)->addColumn('action', function ($order_status) {
                return '<div class="btn-group"><a href="'.URL::to("admin/orderstatus/editorderstatus/".$order_status->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                        <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/orderstatus/delete_order_status/".$order_status->id).'" class="delete-'.$order_status->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                        </ul>
                    </div><script type="text/javascript">
                    $( document ).ready(function() {
                    $(".delete-'.$order_status->id.'").on("click", function(){
                         return confirm("'.trans("messages.Are you sure want to delete?").'");
                    });});</script>';
            })
            ->addColumn('name', function ($order_status) {
                    $data = $order_status->name;
                return $data;
            })
                        ->rawColumns(['name','action'])

            ->make(true);
    }
    
    
        /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxReturnStatus()
    {
        $return_status = DB::table('return_status')->select('*')->orderBy('id', 'desc');
        return Datatables::of($return_status)->addColumn('action', function ($return_status) {
                return '<div class="btn-group"><a href="'.URL::to("admin/returnstatus/editreturnstatus/".$return_status->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                        <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/returnstatus/delete_return_status/".$return_status->id).'" class="delete-'.$return_status->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                        </ul>
                    </div><script type="text/javascript">
                    $( document ).ready(function() {
                    $(".delete-'.$return_status->id.'").on("click", function(){
                         return confirm("'.trans("messages.Are you sure want to delete?").'");
                    });});</script>';
            })
            ->addColumn('name', function ($return_status) {
                    $data = $return_status->name;
                return $data;
            })
                        ->rawColumns(['name','action'])

            ->make(true);
    }
    
    
        /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxReturnaction()
    {
        $return_action = DB::table('return_action')->select('*')->orderBy('id', 'desc');
        return Datatables::of($return_action)->addColumn('action', function ($return_action) {
                return '<div class="btn-group"><a href="'.URL::to("admin/returnaction/editreturnaction/".$return_action->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                        <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/returnactions/delete_return_action/".$return_action->id).'" class="delete-'.$return_action->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                        </ul>
                    </div><script type="text/javascript">
                    $( document ).ready(function() {
                    $(".delete-'.$return_action->id.'").on("click", function(){
                         return confirm("'.trans("messages.Are you sure want to delete?").'");
                    });});</script>';
            })
            ->addColumn('name', function ($return_action) {
                    $data = $return_action->name;
                return $data;
            })
                        ->rawColumns(['name','action'])

            ->make(true);
    }
    
    
            /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxReturnreason()
    {
        $return_reason = DB::table('return_reason')->select('*')->orderBy('id', 'desc');
        return Datatables::of($return_reason)->addColumn('action', function ($return_reason) {
                return '<div class="btn-group"><a href="'.URL::to("admin/returnreason/editreturnreason/".$return_reason->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                        <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/returnreason/delete_return_reason/".$return_reason->id).'" class="delete-'.$return_reason->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                        </ul>
                    </div><script type="text/javascript">
                    $( document ).ready(function() {
                    $(".delete-'.$return_reason->id.'").on("click", function(){
                         return confirm("'.trans("messages.Are you sure want to delete?").'");
                    });});</script>';
            })
            ->addColumn('name', function ($return_reason) {
                    $data = $return_reason->name;
                return $data;
            })
                        ->rawColumns(['name','action'])

            ->make(true);
    }
    
       /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function language()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            $languages=DB::table('languages')
             ->select('languages.*')
            ->orderBy('name', 'asc')
            ->paginate(10);   
            return view('admin.language.list')->with('languages', $languages);
        }
    }
    
    
        /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function anyAjaxLanguage()
    {
    
        
        $languages = DB::table('languages')->select('*')->orderBy('id', 'desc');
        return Datatables::of($languages)->addColumn('action', function ($languages) {
                return '<div class="btn-group"><a href="'.URL::to("admin/language/edit/".$languages->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                        <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/language/delete/".$languages->id).'" class="delete-'.$languages->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                        </ul>
                    </div><script type="text/javascript">
                    $( document ).ready(function() {
                    $(".delete-'.$languages->id.'").on("click", function(){
                         return confirm("'.trans("messages.Are you sure want to delete?").'");
                    });});</script>';
            })
            ->addColumn('status', function ($languages) {
                if($languages->status==0):
                    $data = '<span class="label label-danger">'.trans("messages.Inactive").'</span>';
                elseif($languages->status==1):
                    $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
                endif;
                return $data;
            })
                        ->rawColumns(['status','action'])

            ->make(true);
    }
    
        /**
     * Show the form for creating a new blog.
     *
     * @return Response
     */
    public function language_create()
    { 
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{    
            return view('admin.language.create');
        }
    }
    
        /**
     * Store a newly created blog in storage.
     *
     * @return Response
     */
    public function language_store(Request $data)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $data1=Input::all();
        // validate
        // read more on validation at http://laravel.com/docs/validation        
        $validation = Validator::make($data->all(), array(
            'name' => 'required|alpha|unique:languages,name|max:25',
            'language_code' => 'required|alpha|unique:languages,language_code|max:2',
            'short_date_format' => 'required',
            'full_date_format' => 'required',
        ));
        // process the validation
        if ($validation->fails()) {
                //return redirect('create')->withInput($data1)->withErrors($validation);
                return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store
            $languages = new Languages;
            $languages->name      = $_POST['name'];
            $languages->language_code    = $_POST['language_code'];
            $languages->date_format_short    = $_POST['short_date_format'];
            $languages->date_format_full    = $_POST['full_date_format'];
            $languages->status    = isset($_POST['status']);
            $languages->is_rtl    = isset($_POST['rtl']);
            $languages->created_at = date("Y-m-d H:i:s");
            $languages->save();
            // redirect
            Session::flash('message', trans('messages.Language has been created successfully'));
            //return Redirect::to('blog')->with('updatemsg', 'Blog has been successfully create');
            return Redirect::to('admin/localisation/language');
        }
    }
    
        /**
     * Show the form for editing the specified blog.
     *
     * @param  int  $id
     * @return Response
     */
    public function language_edit($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
        // get the blog
         $languages =  Languages::find($id);
         if(!count($languages)){
             Session::flash('message', 'Invalid Language'); 
             Session::flash('alert-class', 'alert-danger'); 
             return Redirect::to('admin/localisation/language');    
         }
        return view('admin.language.edit')->with('data', $languages);
        }
    }
    
    /**
     * Store a newly created blog in storage.
     *
     * @return Response
     */
    public function language_update(Request $data, $id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $data1=Input::all();
        // validate
        // read more on validation at http://laravel.com/docs/validation        
        $validation = Validator::make($data->all(), array(
            'name' => 'required|alpha|max:25|unique:languages,name,'.$id,
            'language_code' => 'required|alpha|max:2|unique:languages,language_code,'.$id,
            'short_date_format' => 'required',
            'full_date_format' => 'required',
        ));
        // process the validation
        if ($validation->fails()) {
                //return redirect('create')->withInput($data1)->withErrors($validation);
                return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store
            $languages = Languages::find($id);
            $languages->name      = $_POST['name'];
            $languages->language_code    = $_POST['language_code'];
            $languages->date_format_short    = $_POST['short_date_format'];
            $languages->date_format_full    = $_POST['full_date_format'];
            $languages->status    = isset($_POST['status']);
            $languages->is_rtl    = isset($_POST['rtl']);
            $languages->updated_at = date("Y-m-d H:i:s");
            $languages->save();
            // redirect
            Session::flash('message', trans('messages.Language has been updated successfully'));
            return Redirect::to('admin/localisation/language');
        }
    }
    
     /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function currency()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else{
            return view('admin.currencies.list');
        }
    }
    
     /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function anyAjaxCurrency()
    {   
		$language_id = getAdminCurrentLang();
	    $query = '"currencies_infos"."language_id" = (case when (select count(*) as totalcount from currencies_infos where currencies_infos.language_id = '.$language_id.' and currencies.id = currencies_infos.currency_id) > 0 THEN '.$language_id.' ELSE 1 END)';
        $currencies=Currencies::Leftjoin('currencies_infos','currencies_infos.currency_id','=','currencies.id')
                         ->select('currencies.id','currencies_infos.currency_name', 'currencies.currency_code', 'currencies_infos.currency_symbol', 'currencies.exchange_rate', 'currencies.numeric_iso_code', 'currencies.created_date', 'currencies.default_status') 
                         ->whereRaw($query)
                         ->orderBy('id', 'desc')
                         ->get();
   
        return Datatables::of($currencies)->addColumn('action', function ($currencies) {
            return '<div class="btn-group"><a href="'.URL::to("admin/currency/edit/".$currencies->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu xs pull-right" role="menu">
                    <li><a href="'.URL::to("admin/currency/delete/".$currencies->id).'" class="delete-'.$currencies->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                </ul>
            </div>
            <script type="text/javascript">
                $( document ).ready(function() {
                    $(".delete-'.$currencies->id.'").on("click", function(){
                        return confirm("'.trans("messages.Are you sure want to delete?").'");
                    });
                });
            </script>';
        })
        ->addColumn('default_status', function ($currencies) {
            if($currencies->default_status==0):
                $data = '<span class="label label-danger">'.trans("messages.Inactive").'</span>';
            elseif($currencies->default_status==1):
                $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
            endif;
            return $data;
        })
                                ->rawColumns(['default_status','action'])

        ->make(true);
    }
    /**
     * Show the form for creating a new blog.
     *
     * @return Response
     */
    public function currency_create()
    { 
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            return view('admin.currencies.create');
        }
    }
    /**
     * Store a newly created currency in storage.
     *
     * @return Response
     */
    public function currency_store(Request $data)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $data_all   = $data->all();
        $validation = Validator::make($data_all, array(
            //'currency_name'    => 'required|unique:currencies,currency_name|max:15',
            'currency_code'    => 'required|alpha|unique:currencies,currency_code|max:3',
            'numeric_iso_code' => 'required|integer',
           // 'currency_symbol'  => 'required',
            'exchange_rate'    => 'required|between:0,99.99',
            'decimal_values'   => 'required|between:0,99.99',
        ));
        $currency_name = Input::get('currency_name');
        foreach ($currency_name  as $key => $value) {
            $fields['currency_name'.$key] = $value;
            $rules['currency_name'.'1'] = 'required|unique:currencies_infos,currency_name';
        }
        $currency_symbol = Input::get('currency_symbol');
        foreach ($currency_symbol  as $key => $value) {
            $fields['currency_symbol'.$key] = $value;
            $rules['currency_symbol'.'1'] = 'required';
        }
        // process the validation
        if ($validation->fails())
        {
            return Redirect::back()->withErrors($validation)->withInput();
        }
        else {
            // store
            $Currencies = new Currencies;
            //~ $Currencies->currency_symbol_left    = $_POST['currency_symbol_left'];
            //~ $Currencies->currency_symbol_right    = $_POST['currency_symbol_right'];
           // $Currencies->currency_name    = $data_all['currency_name'];
            $Currencies->currency_code    = $data_all['currency_code'];
            $Currencies->numeric_iso_code = $data_all['numeric_iso_code'];
           // $Currencies->currency_symbol  = $data_all['currency_symbol'];
            $Currencies->exchange_rate    = $data_all['exchange_rate'];
            $Currencies->decimal_values   = $data_all['decimal_values'];
            $Currencies->default_status   = isset($data_all['status'])?$data_all['status']:0;
            $Currencies->created_date     = date("Y-m-d H:i:s");
            $Currencies->save();
            $this->currencies_save_after($Currencies,$_POST);
            // redirect
            Session::flash('message', trans('messages.Currency has been created successfully'));
            return Redirect::to('admin/localisation/currency');
        }
    }
    /**
     * Show the form for editing the specified currency.
     *
     * @param  int  $id
     * @return Response
     */
       /**
     * Currecy name added in currencies infos table the specified currency in storage.
     *
     * @param  int  $id
     * @return Response
     */
     public static function currencies_save_after($object,$post)
    {   
        $Currencies = $object;
        $post = $post;
        if(isset($post['currency_name'])){
            $currency_name = $post['currency_name'];
             $currency_symbol = $post['currency_symbol'];
            try{
                $affected = DB::table('currencies_infos')->where('currency_id', '=', $object->id)->delete();
                $languages = DB::table('languages')->where('status', 1)->get();
                foreach($languages as $key => $lang){
					 if(isset($currency_name[$lang->id]) && $currency_name[$lang->id]!=""){
                        $infomodel = new Currencies_infos;
                        $infomodel->currency_name = $currency_name[$lang->id]; 
                        $infomodel->currency_symbol = $currency_symbol[$lang->id]; 
                        $infomodel->language_id = $lang->id;
                        $infomodel->currency_id = $object->id; 
                        $infomodel->save();
					}
                      
                   
                }
                }catch(Exception $e) {
                    
                    Log::Instance()->add(Log::ERROR, $e);
                }
        }
    }
    public function currency_edit($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else{
            // get the currency
            $currencies =  Currencies::find($id);
            if(!count($currencies))
            {
                Session::flash('message', 'Invalid currncy'); 
                Session::flash('alert-class', 'alert-danger'); 
                return Redirect::to('admin/localisation/currency');
            }
             $info = new currencies_infos;
            return view('admin.currencies.edit')->with('data', $currencies)->with('infomodel', $info);;
        }
    }
    /**
     * Store a newly created currency in storage.
     *
     * @return Response
     */
    public function currency_update(Request $data, $id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $data_all   = $data->all();
        $validation = Validator::make($data_all, array(
            //'currency_name'    => 'required|unique:currencies,currency_name,'.$id,
            'currency_code'    => 'required|alpha|unique:currencies,currency_code,'.$id,
            'numeric_iso_code' => 'required|integer',
           // 'currency_symbol'  => 'required',
            'exchange_rate'    => 'required|between:0,99.99',
            'decimal_values'   => 'required|between:0,99.99',
        ));
         $currency_name = Input::get('currency_name');
        foreach ($currency_name  as $key => $value) {
            $fields['currency_name'.$key] = $value;
            $rules['currency_name'.'1'] = 'required|unique:currencies_infos,currency_name,'.$id.',currency_id';
        }
        $currency_symbol = Input::get('currency_symbol');
        foreach ($currency_symbol  as $key => $value) {
            $fields['currency_symbol'.$key] = $value;
            $rules['currency_symbol'.'1'] = 'required';
        }
        // process the validation
        if ($validation->fails())
        {
            return Redirect::back()->withErrors($validation)->withInput();
        }
        else {
            // store
            $Currencies = Currencies::find($id);
            //~ $Currencies->currency_symbol_left  = $_POST['currency_symbol_left'];
            //~ $Currencies->currency_symbol_right = $_POST['currency_symbol_right'];
            //$Currencies->currency_name    = $data_all['currency_name'];
            $Currencies->currency_code    = $data_all['currency_code'];
           // $Currencies->currency_symbol = $data_all['currency_symbol'];
            $Currencies->numeric_iso_code = $data_all['numeric_iso_code'];
            $Currencies->exchange_rate    = $data_all['exchange_rate'];
            $Currencies->decimal_values   = $data_all['decimal_values'];
            $Currencies->default_status   = isset($data_all['status'])?$data_all['status']:0;
            $Currencies->save();
             $this->currencies_save_after($Currencies,$_POST);
            // redirect
            Session::flash('message', trans('messages.Currency has been updated successfully'));
            return Redirect::to('admin/localisation/currency');
        }
    }
  
    /**
     * Delete the specified currency in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function currency_destory($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $data = Currencies::find($id);
        $data->delete();
        Session::flash('message', trans('messages.Currency has been deleted successfully!'));
        return Redirect::to('admin/localisation/currency');
    }
    /**
     * Delete the specified country in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function language_destory($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $data = Languages::find($id);
        $data->delete();
        Session::flash('message', trans('messages.Language has been deleted successfully!'));
        return Redirect::to('admin/localisation/language');
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function module_settings()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            return view('admin.modules.list');
        }
        
    }

    /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function anyAjaxModules()
    {
        $modules = DB::table('modules')->select('*')->orderBy('id', 'desc');
        return Datatables::of($modules)->addColumn('action', function ($modules) {
                return '<div class="btn-group"><a href="'.URL::to("admin/modules/edit/".$modules->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                    </div>';
            })
            ->addColumn('active_status', function ($modules) {
                if($modules->active_status==0):
                    $data = '<span class="label label-danger">'.trans("messages.Inactive").'</span>';
                elseif($modules->active_status==1):
                    $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
                endif;
                return $data;
            })
                    ->rawColumns(['active_status','action'])

            ->make(true);
    }

        /**
     * Show the form for editing the specified blog.
     *
     * @param  int  $id
     * @return Response
     */
    public function module_settings_edit($id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            // get the blog
             $modules =  Modules::find($id);
             if(!count($modules)){
                 Session::flash('message', 'Invalid Modules');  
                 return Redirect::to('admin/modules/settings');    
             }
             return view('admin.modules.edit')->with('data', $modules);
        }
    }

     /**
     * Store a newly created blog in storage.
     *
     * @return Response
     */
    public function module_update(Request $data, $id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $data1=Input::all();
        // validate
        // read more on validation at http://laravel.com/docs/validation        
        $validation = Validator::make($data->all(), array(
            'module_name' => 'required|max:15|unique:modules,module_name,'.$id,
        ));
        // process the validation
        if ($validation->fails()) {
                //return redirect('create')->withInput($data1)->withErrors($validation);
                return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store
            $modules = Modules::find($id);
            $modules->module_name      = $_POST['module_name'];
            $modules->active_status    = isset($_POST['active_status']);
            $modules->save();
            // redirect
            Session::flash('message', trans('messages.Module settings has been updated successfully'));
            return Redirect::to('admin/modules/settings');
        }
    }

    /**
     * Show the form for creating a new weight classes.
     *
     * @return Response
     */
    public function delivery_settings()
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        if (Auth::guest()) {
            return redirect()->guest('admin/login');
        } else {
            // load the create form (resources/views/admin/weight_classes/create.blade.php)
            $delivery_settings = Delivery_settings::find(1);
            return view('admin.settings.delivery_settings')->with('data', $delivery_settings);
        }
    }

    public function delivery_settings_update(Request $data, $id)
    {
        if(!hasTask('admin/settings/general'))
        {
            return view('errors.404');
        }
        $data1=Input::all();
        $status = Input::get('on_off_status');
        if($status==1){
            $delivery_type = Input::get('delivery_type');
            $validation = Validator::make($data->all(), array(
                    'delivery_type' => 'required',
                ));
                // process the validation
                if ($validation->fails()) {
                        //return redirect('create')->withInput($data1)->withErrors($validation);
                        return Redirect::back()->withErrors($validation)->withInput();
                }
            if($delivery_type==1){
                // validate
                // read more on validation at http://laravel.com/docs/validation
                $validation = Validator::make($data->all(), array(
                    'delivery_cost_fixed' => 'required|numeric',
                    'delivery_cost_variation' => 'required|numeric|min:1',
                    'minimum_order_amount' => 'required|numeric|min:1',
                    'delivery_km_fixed' => 'required|numeric|min:1',
                ));
                // process the validation
                if ($validation->fails()) {
                        //return redirect('create')->withInput($data1)->withErrors($validation);
                        return Redirect::back()->withErrors($validation)->withInput();
                } else {
                    // store
                    $delivery = Delivery_settings::find($id);
                    $delivery->delivery_cost_fixed      = $_POST['delivery_cost_fixed'];
                    $delivery->delivery_km_fixed      = $_POST['delivery_km_fixed'];
                    $delivery->on_off_status      = 1;
                    $delivery->delivery_cost_variation    = $_POST['delivery_cost_variation'];
                    $delivery->minimum_order_amount    = $_POST['minimum_order_amount'];
                    $delivery->delivery_type    = $_POST['delivery_type'];
                    $delivery->flat_delivery_cost      = 0;
                    $delivery->save();
                    // redirect
                    Session::flash('message', trans('messages.Delivery settings has been updated successfully'));
                    return Redirect::to('admin/modules/delivery_settings');
                }
            }
            if($delivery_type==2){
                // validate
                // read more on validation at http://laravel.com/docs/validation        
                $validation = Validator::make($data->all(), array(
                    'flat_delivery_cost' => 'required|numeric',
                    'minimum_order_amount' => 'required|numeric|min:1',
                ));
                // process the validation
                if ($validation->fails()) {
                        //return redirect('create')->withInput($data1)->withErrors($validation);
                        return Redirect::back()->withErrors($validation)->withInput();
                } else {
                    // store
                    $delivery = Delivery_settings::find($id);
                    $delivery->on_off_status      = 1;
                    $delivery->flat_delivery_cost      = $_POST['flat_delivery_cost'];
                    $delivery->delivery_km_fixed      = 0;
                    $delivery->minimum_order_amount      = $_POST['minimum_order_amount'];
                    $delivery->delivery_type    = $_POST['delivery_type'];
                    $delivery->delivery_cost_fixed      = 0;
                    $delivery->delivery_cost_variation    = 0;
                    $delivery->save();
                    // redirect
                    Session::flash('message', trans('messages.Delivery settings has been updated successfully'));
                    return Redirect::to('admin/modules/delivery_settings');
                }
            }
            
        }else {
                $delivery = Delivery_settings::find($id);
                $delivery->on_off_status      = 2;
                $delivery->delivery_type      = 0;
                $delivery->delivery_cost_fixed = 0;
                $delivery->delivery_cost_variation = 0;
                $delivery->flat_delivery_cost = 0;
                $delivery->minimum_order_amount = 0;
                $delivery->save();
                // redirect
                Session::flash('message', trans('messages.Delivery settings has been updated successfully'));
                return Redirect::to('admin/modules/delivery_settings');
        }
    }
}
