<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Model\coupons;
use App\Model\coupons_infos;
use App\Model\coupon_outlet;
use App\Model\coupon_users;
use Session;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use PushNotification;
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

class Coupon extends Controller
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
        SEOMeta::addKeyword($this->site_name);
        OpenGraph::setTitle($this->site_name);
        OpenGraph::setDescription($this->site_name);
        OpenGraph::setUrl($this->site_name);
        Twitter::setTitle($this->site_name);
        Twitter::setSite('@'.$this->site_name);
		App::setLocale('en');
    }

    /**
     * Show the application coupons list.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
			if(!hasTask('admin/coupons')){
				return view('errors.404');
			}
            SEOMeta::setTitle('Manage Coupons - '.$this->site_name);
            SEOMeta::setDescription('Manage Coupons - '.$this->site_name);
            return view('admin.coupons.list');
        }
    }
    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxcouponlist()
    {
        $query = '"coupons_infos"."lang_id" = (case when (select count(coupons_infos.id) as totalcount from coupons_infos where coupons_infos.lang_id = '.getAdminCurrentLang().' and coupons.id = coupons_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $coupons = Coupons::join('coupons_infos','coupons_infos.id','=','coupons.id')
                        ->select('coupons.id', 'coupons_infos.coupon_title', 'coupons.coupon_code', 'coupons.start_date', 'coupons.end_date', 'coupons.created_date', 'coupons.active_status', 'coupons.coupon_status')
                        ->whereRaw($query)
                        ->orderBy('coupons.id', 'desc')
                        ->get();

        return Datatables::of($coupons)->addColumn('action', function ($coupons) {
			if(hasTask('admin/coupons/edit'))
			{
				$html='<div class="btn-group">
					<a href="'.URL::to("admin/coupons/edit/".$coupons->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
					<button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
						<span class="caret"></span>
						<span class="sr-only">Toggle Dropdown</span>
					</button>
					<ul class="dropdown-menu xs pull-right" role="menu">
						<li><a href="'.URL::to("admin/coupons/view/".$coupons->id).'" class="view-'.$coupons->id.'" title="'.trans("messages.View").'"><i class="fa fa-file-text-o"></i>&nbsp;&nbsp;'.@trans("messages.View").'</a></li>
						<li><a href="'.URL::to("admin/coupons/delete/".$coupons->id).'" class="delete-'.$coupons->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
					</ul>
				</div>
				<script type="text/javascript">
					$( document ).ready(function() {
						$(".delete-'.$coupons->id.'").on("click", function(){
							return confirm("'.trans("messages.Are you sure want to delete?").'");
						});
					});
				</script>';
				return $html;
			}
        })
        ->addColumn('active_status', function ($coupons) {
            if($coupons->active_status == 0):
                $data = '<span class="label label-warning">'.trans("messages.Inactive").'</span>';
            elseif($coupons->active_status == 1):
                $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
            else:
                $data = '<span class="label label-danger">'.trans("messages.Delete").'</span>';
            endif;
            return $data;
        })
        ->editColumn('coupon_title', '{!! str_limit($coupon_title, 30) !!}')
        ->rawColumns(['active_status','action'])

        ->make(true);
    }
    /**
     * Create the specified coupon in view.
     *
     * @param  int  $id
     * @return Response
     */
    public function create()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else{
			if(!hasTask('admin/coupons/create')){
				return view('errors.404');
			}
            //Get the category data
            $category_list = getCategoryLists(5);
            //Get the vendors data
            $vendors_list  = getVendorLists(5);
            SEOMeta::setTitle('Create Coupon - Election');
            SEOMeta::setDescription('Create Coupon - Election');
            return view('admin.coupons.create')->with('category_list', $category_list)->with('vendors_list', $vendors_list);
        }
    }
    /**
     * Add the specified coupon in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function store(Request $data)
    {
        if(!hasTask('create_coupon'))
		{
			return view('errors.404');
		}
		$language = getCurrentLang();
        $fields['coupon_code']      = Input::get('coupon_code');
        $fields['coupon_type']      = Input::get('coupon_type');
        $fields['vendor_name']      = Input::get('vendor_name');
        $fields['outlet_name']      = Input::get('outlet_name');
        $fields['product_name']     = Input::get('product_name');
        $fields['offer_type']       = Input::get('offer_type');
        $fields['offer_amount']     = Input::get('offer_amount');
        $fields['offer_percentage'] = Input::get('offer_percentage');
        $fields['minimum_order_amount'] = Input::get('minimum_order_amount');
        $fields['category_name']    = Input::get('category_name');
        $fields['start_date']       = Input::get('start_date');
        $fields['end_date']         = Input::get('end_date');
        $fields['coupon_limit']     = Input::get('coupon_limit');
        $fields['user_limit']       = Input::get('user_limit');
        $fields['active_status']    = Input::get('active_status');
        $fields['coupon_image']     = Input::file('coupon_image');
        $fields['user_type']     = Input::get('user_type');
        $fields['user_name']     = Input::get('user_name');

        $rules = array(
            'coupon_code'      => 'required|max:8|alpha_num|unique:coupons,coupon_code',
            'coupon_type'      => 'required',
            'vendor_name'      => 'required_if:coupon_type,2',
            'outlet_name'      => 'required_if:coupon_type,2',
            //'product_name'   => 'required_if:coupon_type,3',
            'offer_type'       => 'required',
            'offer_amount'     => 'required_if:offer_type,1|numeric',
            'offer_percentage' => 'required_if:offer_type,2|between:0,100|numeric',
            'minimum_order_amount' => 'required|numeric',
            'category_name'    => 'required',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date',
            'coupon_limit'     => 'required|Integer',
            'user_limit'       => 'required|Integer',
            'coupon_image'     => 'required|mimes:png,jpg,jpeg,bmp|max:2024',
            //'user_type'        => 'required', // coupon for users
           // 'user_name'        => 'required_if:user_type,2',// coupon for users

        );

        $coupon_title = Input::get('coupon_title');
        foreach ($coupon_title  as $key => $value)
        {
            $fields['coupon_title'.$key] = $value;
            $rules['coupon_title1']  = 'required|max:100|regex:/(^[A-Za-z0-9 ]+$)+/|unique:coupons_infos,coupon_title';
        }
        $coupon_description = Input::get('coupon_description');
        foreach ($coupon_description  as $key => $value)
        {
            $fields['coupon_description'.$key] = $value;
            $rules['coupon_description1']  = 'required';
        }
        $terms_condition = Input::get('terms_condition');
        foreach ($terms_condition  as $key => $value)
        {
            $fields['terms_condition'.$key] = $value;
            $rules['terms_condition1']  = 'required';
        }
        $validation = Validator::make($fields, $rules);

        // process the validation
        if ($validation->fails())
        {                         
            return Redirect::back()->withErrors($validation)->withInput();
        }
        else {
            // echo"<pre>"; print_r($_POST);exit;
            //Store the data here with database
            try{
                $Coupons = new Coupons;
                $Coupons->coupon_code = strtoupper($_POST['coupon_code']);
                $Coupons->coupon_type = $_POST['coupon_type'];
                $Coupons->user_type = isset($_POST['user_type'])?$_POST['user_type']:0;;
                if( $_POST['coupon_type'] == 2 )
                {
                    $Coupons->vendor = $_POST['vendor_name'];
                }
                if($_POST['offer_type']==2){
					$Coupons->offer_amount     = ($_POST['offer_percentage'] != '')?$_POST['offer_percentage']:0;
				}
				else{
					$Coupons->offer_amount     = ($_POST['offer_amount'] != '')?$_POST['offer_amount']:0;
				}
                $Coupons->offer_type       = $_POST['offer_type'];
                $Coupons->offer_percentage = ($_POST['offer_percentage'] != '')?$_POST['offer_percentage']:0;
                $Coupons->minimum_order_amount = $_POST['minimum_order_amount'];
                $Coupons->category_id      = $_POST['category_name'];
                $Coupons->start_date       = $_POST['start_date'];
                $Coupons->end_date         = $_POST['end_date'];
                $Coupons->coupon_limit     = $_POST['coupon_limit'];
                $Coupons->user_limit       = $_POST['user_limit'];
                $Coupons->created_date     = date('Y-m-d H:i:s');
                $Coupons->created_by       = Auth::user()->id;
                $Coupons->active_status    = isset($_POST['active_status'])?$_POST['active_status']:0;
               $Coupons->save();
                $imageName = $Coupons->id.'.'.$data->file('coupon_image')->getClientOriginalExtension();
                $data->file('coupon_image')->move(
                    base_path() . '/public/assets/admin/base/images/coupon/',$imageName
                );
                $destinationPath1 = url('/assets/admin/base/images/coupon/'.$imageName);
              //  Image::make( $destinationPath1 )->fit(556, 273)->save(base_path().'/public/assets/admin/base/images/coupon/'.$imageName)->destroy();//
                Image::make( $destinationPath1 )->fit(720, 360)->save(base_path().'/public/assets/admin/base/images/coupon/'.$imageName)->destroy();//
                $Coupons->coupon_image = $imageName;
                $Coupons->save();
                if( $_POST['coupon_type'] == 1 )
                {

					$outlets = DB::table('outlets')->select('outlets.id')->where('active_status', 1)->get();
					if(count($outlets)>0)
					{
						foreach($outlets as $out)
						{
							$Coupon_outlet = new Coupon_outlet;
							$Coupon_outlet->coupon_id = $Coupons->id;
							$Coupon_outlet->outlet_id = $out->id;
							$Coupon_outlet->save();
						}
					}
                }
                if( $_POST['coupon_type'] == 2 )
                {
                    if(count($_POST['outlet_name']) > 0)
					{
						foreach($_POST['outlet_name'] as $out)
						{
							$Coupon_outlet = new Coupon_outlet;
							$Coupon_outlet->coupon_id = $Coupons->id;
							$Coupon_outlet->outlet_id = $out;
							$Coupon_outlet->save();
						}
					}
                }

                /*coupon for user*/

              /*  if( $_POST['user_type'] == 1 )
                {

                    $users_list = DB::table('users')->select('users.id')->where('active_status', 1)->get();
                    if(count($users_list)>0)
                    {
                        foreach($users_list as $users)
                        {
                            $values = array('coupon_id' => $Coupons->id,
                                'users_id'  => $user);
                            DB::table('coupon_users')->insert($values);
                        }
                    }
                }
                if( $_POST['user_type'] == 2 )
                {
                    if(count($_POST['user_name']) > 0)
                    {
                        foreach($_POST['user_name'] as $user)
                        {
                            $values = array('coupon_id' => $Coupons->id,
                                'users_id'  => $user);
                            DB::table('coupon_users')->insert($values);
                        }
                    }
                }*/
                /*coupon for user*/

                $android_users = get_users_list_ids(1);
                //$ios_users = get_users_list_ids(2);
                if($_POST['offer_type'] == 1)
                {
					$currency_symbol = getCurrency($language);
					$currency_side   = getCurrencyPosition()->currency_side;
					if($currency_side == 1)
						$offer_price = $currency_symbol.$_POST['offer_amount'];
					else
						$offer_price = $_POST['offer_amount'].$currency_symbol;
				}
				else
					$offer_price = $_POST['offer_percentage'].'%';
                $coupon_title = "New offer available in ".Session::get("general")->site_name.". Use promocode ".$Coupons->coupon_code." to get ".$offer_price;
				$notification_message = PushNotification::Message($coupon_title,array(
					'badge' => 1,
					'sound' => 'example.aiff',
					'actionLocKey' => $coupon_title,
					//'launchImage' => base_path().'/assets/admin/base/images/offers/'.$offer_image,
					'id' => $Coupons->id,
					'type' => 1,
					'title' => $coupon_title,
					'custom' => array('id' => $Coupons->id,'type' => 1,'title' => $coupon_title)//If type 1 means offers and 2 means orders
				));
                if(count($android_users) > 0)
                {
					foreach($android_users as $android)
					{
						$android_device_arr[0] = PushNotification::Device($android->android_device_token);
						$android_devices = PushNotification::DeviceCollection($android_device_arr);
						$collection = PushNotification::app('TijikAndroid')->to($android_devices);
						//it was need to set 'sslverifypeer' parameter to false
						$collection->adapter->setAdapterParameters(['sslverifypeer' => false]);
						$collection->send($notification_message);
						// get response for each device push
						foreach ($collection->pushManager as $push)
						{
							$response = $push->getAdapter()->getResponse();
						}
					}
				} 
                $this->coupon_save_after($Coupons,$_POST);
                Session::flash('message', trans('messages.Coupon has been added successfully'));
            } catch(Exception $e) {
                Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/coupons');
        }
    }
    /**
     * add,edit datas  saved in main table 
     * after inserted in sub tabel.
     *
     * @param  int  $id
     * @return Response
     */
   public static function coupon_save_after($object,$post)
   { //echo '<pre>'; print_r($post);echo '</pre>';die;
        if(isset($post['coupon_title']) && isset($post['coupon_description']) && isset($post['terms_condition']))
        {
            $coupon_title       = $post['coupon_title'];
            $coupon_description = $post['coupon_description'];
            $terms_condition    = $post['terms_condition'];
            try{
                //~ $data = Coupons_infos::find($object->id);
                //~ if(count($data)>0)
                //~ {
                    //~ $data->delete();
                //~ }
                $affected = DB::table('coupons_infos')->where('id', '=', $object->id)->delete();
                $languages = DB::table('languages')->where('status', 1)->get();
                foreach($languages as $key => $lang)
                {
                    if(isset($coupon_title[$lang->id]) && $coupon_title[$lang->id] != '')
                    {
                        $infomodel = new Coupons_infos;
                        $infomodel->lang_id = $lang->id;
                        $infomodel->id      = $object->id; 
                        $infomodel->coupon_title    = $coupon_title[$lang->id];
                        $infomodel->coupon_info     = $coupon_description[$lang->id];
                        $infomodel->terms_condition = $terms_condition[$lang->id];
                        $infomodel->save();
                    }
                }
            }
            catch(Exception $e) {
                Log::Instance()->add(Log::ERROR, $e);
            }
        }
    }
    /**
     * Display the specified coupon.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
		if(!hasTask('admin/coupons/view'))
		{
			return view('errors.404');
		}
        //Get coupon details
        $coupons = Coupons::find($id);
        if(!count($coupons))
        {
            Session::flash('message', 'Invalid Coupon Details'); 
            return Redirect::to('admin/coupons');
        }
        //Get the coupons information
        $info = new Coupons_infos;
        //Get the category data
        $category_list = getCategoryLists(5);
        //Get the vendors data
        $vendors_list  = getVendorLists(5);
        $outlet_list   = getOutletLists($id);
        SEOMeta::setTitle('View Coupon - '.$this->site_name);
        SEOMeta::setDescription('View Coupon - '.$this->site_name);
        return view('admin.coupons.show')->with('vendors_list', $vendors_list)->with('category_list', $category_list)->with('data', $coupons)->with('infomodel', $info)->with('selected_outlet_list', $outlet_list);
    }
    /**
     * Delete the specified coupon in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function delete($id)
    {
		if(!hasTask('admin/coupons/delete'))
		{
			return view('errors.404');
		}
        $data = Coupons::find($id);
        if(!count($data))
        {
            Session::flash('message', 'Invalid Coupon Details'); 
            return Redirect::to('admin/coupons');
        }
        if(file_exists(base_path().'/public/assets/admin/base/images/coupon/'.$data->coupon_image) && $data->coupon_image != '')
        {
            unlink(base_path().'/public/assets/admin/base/images/coupon/'.$data->coupon_image);
        }
        DB::table('coupon_outlet')->where('coupon_id', '=', $id)->delete();
        $data->delete();
        Session::flash('message', trans('messages.Coupon has been deleted successfully'));
        return Redirect::to('admin/coupons');
    }
    /**
     * Edit the specified coupon in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
			if(!hasTask('admin/coupons/edit'))
			{
				return view('errors.404');
			}
            //Get coupon details
            $coupons = Coupons::find($id);
            if(!count($coupons))
            {
                Session::flash('message', 'Invalid Coupon Details'); 
                return Redirect::to('admin/coupons');
            }
            //Get the coupons information
            $info = new Coupons_infos;
            //Get the categories data with type coupon
            $category_list = getCategoryLists(5);
            //Get the vendors data
            $vendors_list  = getVendorLists(5);
            $outlet_list   = getOutletLists($id);
            $users_list   = getUsersLists($id);
          //  echo"<pre>";print_r($coupons);exit();
            SEOMeta::setTitle('Edit Coupon - '.$this->site_name);
            SEOMeta::setDescription('Edit Coupon - '.$this->site_name);
            return view('admin.coupons.edit')->with('vendors_list', $vendors_list)->with('category_list', $category_list)->with('data', $coupons)->with('infomodel', $info)->with('selected_outlet_list', $outlet_list)->with('selected_users_list', $users_list);
        }
    }
    /**
     * Update the specified coupon in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $data, $id)
    {
		if(!hasTask('admin/coupons/update'))
		{
			return view('errors.404');
		}
        $fields['coupon_code']      = Input::get('coupon_code');
        $fields['coupon_type']      = Input::get('coupon_type');
        $fields['vendor_name']      = Input::get('vendor_name');
        $fields['outlet_name']      = Input::get('outlet_name');
        $fields['product_name']     = Input::get('product_name');
        $fields['offer_type']       = Input::get('offer_type');
        $fields['offer_amount']     = Input::get('offer_amount');
        $fields['offer_percentage'] = Input::get('offer_percentage');
        $fields['category_name']    = Input::get('category_name');
        $fields['start_date']       = Input::get('start_date');
        $fields['end_date']         = Input::get('end_date');
        $fields['coupon_limit']     = Input::get('coupon_limit');
        $fields['user_limit']       = Input::get('user_limit');
        $fields['active_status']    = Input::get('active_status');
        $fields['coupon_image']     = Input::file('coupon_image');
        $fields['minimum_order_amount'] = Input::get('minimum_order_amount');
       // $fields['user_type'] = Input::get('user_type');
       // $fields['user_name'] = Input::get('user_name');
        $rules = array(
            'coupon_code'      => 'required|max:8|alpha_num|unique:coupons,coupon_code,'.$id,
            'coupon_type'      => 'required',
            'vendor_name'      => 'required_if:coupon_type,2',
            'outlet_name'      => 'required_if:coupon_type,2',
            'offer_type'       => 'required',
            'offer_amount'     => 'required_if:offer_type,1|numeric',
            'offer_percentage' => 'required_if:offer_type,2|between:0,100|numeric',
            'category_name'    => 'required',
            'minimum_order_amount' => 'required|numeric',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date',
            'coupon_limit'     => 'required|Integer',
            'user_limit'       => 'required|Integer',
            'coupon_image'     => 'mimes:png,jpg,jpeg,bmp|max:2024',
           // 'user_type'      => 'required',
           // 'user_name'      => 'required_if:user_type,2',

        );
      //  echo"<pre>";print_r($_POST);exit;
        $coupon_title = Input::get('coupon_title');
		foreach ($coupon_title  as $key => $value) {
			$fields['coupon_title'.$key] = $value;
			$rules['coupon_title'.'1'] = 'required|max:100|regex:/(^[A-Za-z0-9 ]+$)+/|unique:coupons_infos,coupon_title,'.$id.',id';
		}
        $coupon_description = Input::get('coupon_description');
        foreach ($coupon_description  as $key => $value)
        {
            $fields['coupon_description'.$key] = $value;
            $rules['coupon_description1']  = 'required';
        }
        $terms_condition = Input::get('terms_condition');
        foreach ($terms_condition  as $key => $value)
        {
            $fields['terms_condition'.$key] = $value;
            $rules['terms_condition1']  = 'required';
        }
        $validator = Validator::make($fields, $rules);
        // process the validation
        if ($validator->fails())
        { 
            return Redirect::back()->withErrors($validator)->withInput();
        }
        else {
            try{
                $Coupons = Coupons::find($id);
                $Coupons->coupon_code = strtoupper($_POST['coupon_code']);
                $Coupons->coupon_type = $_POST['coupon_type'];
                DB::table('coupon_outlet')->where('coupon_id', '=', $id)->delete();
                if( $_POST['coupon_type'] == 1 )
                {
					$outlets = DB::table('outlets')->select('outlets.id')->where('active_status', 1)->get();
					if(count($outlets)>0)
					{
						foreach($outlets as $out)
						{
							$Coupon_outlet = new Coupon_outlet;
							$Coupon_outlet->coupon_id = $id;
							$Coupon_outlet->outlet_id = $out->id;
							$Coupon_outlet->save();
						}
					}
                }
                if( $_POST['coupon_type'] == 2 )
                {
                    $Coupons->vendor = $_POST['vendor_name'];
                    if(count($_POST['outlet_name']) > 0)
					{
						foreach($_POST['outlet_name'] as $out)
						{
							$Coupon_outlet = new Coupon_outlet;
							$Coupon_outlet->coupon_id = $id;
							$Coupon_outlet->outlet_id = $out;
							$Coupon_outlet->save();
						}
					}
                }
                 /*coupon for user*/
                DB::table('coupon_users')->where('coupon_id', '=', $id)->delete();

                // if( $_POST['user_type'] == 1 )
                // {

                //     $users_list = DB::table('users')->select('users.id')->where('active_status', 1)->get();
                //     if(count($users_list)>0)
                //     {
                //         foreach($users_list as $users)
                //         {
                //             $values = array('coupon_id' => $Coupons->id,
                //                 'users_id'  => $user);
                //             DB::table('coupon_users')->insert($values);
                //         }
                //     }
                // }
                // if( $_POST['user_type'] == 2 )
                // {
                //     if(count($_POST['user_name']) > 0)
                //     {
                //         foreach($_POST['user_name'] as $user)
                //         {
                //             $values = array('coupon_id' => $Coupons->id,
                //                 'users_id'  => $user);
                //             DB::table('coupon_users')->insert($values);
                //         }
                //     }
                // }
                // /*coupon for user*/

                $Coupons->user_type =isset($_POST['user_type'])?$_POST['user_type']:0;


                $Coupons->offer_type       = $_POST['offer_type'];
              if($_POST['offer_type']==2){
					$Coupons->offer_amount     = ($_POST['offer_percentage'] != '')?$_POST['offer_percentage']:0;
				}
				else{
					$Coupons->offer_amount     = ($_POST['offer_amount'] != '')?$_POST['offer_amount']:0;
				}
                $Coupons->offer_percentage = ($_POST['offer_percentage'] != '')?$_POST['offer_percentage']:0;
                $Coupons->category_id      = $_POST['category_name'];
                $Coupons->minimum_order_amount = $_POST['minimum_order_amount'];
                $Coupons->start_date       = $_POST['start_date'];
                $Coupons->end_date         = $_POST['end_date'];
                $Coupons->coupon_limit     = $_POST['coupon_limit'];
                $Coupons->user_limit       = $_POST['user_limit'];
                $Coupons->modified_date    = date('Y-m-d H:i:s');
                $Coupons->active_status    = isset($_POST['active_status'])?$_POST['active_status']:0;
                $Coupons->save();
                $this->coupon_save_after($Coupons,$_POST);
                if(isset($_FILES['coupon_image']['name']) && $_FILES['coupon_image']['name']!='')
                {
                    $imageName = $id.'.'.$data->file('coupon_image')->getClientOriginalExtension();
                    $data->file('coupon_image')->move(
                        base_path().'/public/assets/admin/base/images/coupon/', $imageName
                    );
                    $destinationPath1 = url('/assets/admin/base/images/coupon/'.$imageName);

                    //Image::make( $destinationPath1 )->fit(556, 273)->save(base_path() .'/public/assets/admin/base/images/coupon/'.$imageName)->destroy();
                    Image::make( $destinationPath1 )->fit(720, 360)->save(base_path() .'/public/assets/admin/base/images/coupon/'.$imageName)->destroy();
                    $Coupons->coupon_image = $imageName;
                    $Coupons->save();
                }
                Session::flash('message', trans('messages.Coupon has been updated successfully'));
            }
            catch(Exception $e) {
                Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/coupons');
        }
    }
    /*
     * vendor based outlet list
     */
    public function getAllVendorOutletList(Request $request)
    { 
        if($request->ajax())
        {
            $vendor_id   = $request->input('vendor_name');
            $outlet_list = get_outlet_list($vendor_id);
            return response()->json([
                'data' => $outlet_list
            ]);
        }
    }
    /*
     * outlets based product list
     */
    public function getAllOutletProductList(Request $request)
    { 
        if($request->ajax())
        {
            $outlet_id    = $request->input('outlet_name');
            $product_list = get_product_list($outlet_id);
            return response()->json([
                'data' => $product_list
            ]);
        }
    }
}
