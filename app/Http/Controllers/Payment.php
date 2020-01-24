<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
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
use App\Model\Payment\gateways;
use App\Model\Payment\gateways_info;

class Payment extends Controller
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
    public function payment_settings()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else{
            if (!hasTask('admin/payment/settings'))
            {
                return view('errors.404');
            }
            $query = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.getAdminCurrentLang().' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $payment_seetings=DB::table('payment_gateways')
             ->select('payment_gateways.*','payment_gateways_info.*')
            ->leftJoin('payment_gateways_info','payment_gateways_info.payment_id','=','payment_gateways.id')
            ->whereRaw($query)
            ->orderBy('id', 'asc')
            ->get();
            return view('admin.payment.settings.list')->with('payment', $payment_seetings);
        }
        
    }
    
        /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxpaymentsettings()
    {
        if (!hasTask('admin/payment/settings'))
        {
            return view('errors.404');
        }
        $query = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.getAdminCurrentLang().' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $payment_seetings = Gateways::Leftjoin('payment_gateways_info','payment_gateways_info.payment_id','=','payment_gateways.id')
                            ->select('payment_gateways.*','payment_gateways_info.*')
                            ->whereRaw($query)
                            ->orderBy('payment_gateways.id', 'desc')
                            ->get();
        return Datatables::of($payment_seetings)->addColumn('action', function ($payment_seetings) {
            return '<div class="btn-group"><a href="'.URL::to("admin/payment/gatewayedit/".$payment_seetings->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu xs pull-right" role="menu">
                    <li><a href="'.URL::to("admin/payment/deletegateway/".$payment_seetings->id).'" class="delete-'.$payment_seetings->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                </ul>
            </div>
            <script type="text/javascript">
                $( document ).ready(function() {
                    $(".delete-'.$payment_seetings->id.'").on("click", function(){
                        return confirm("'.trans("messages.Are you sure want to delete?").'");
                    });
                });
            </script>';
        })
        ->addColumn('status', function ($payment_seetings) {
            if($payment_seetings->active_status==0):
                $data = '<span class="label label-danger">'.trans("messages.Inactive").'</span>';
            elseif($payment_seetings->active_status==1):
                $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
            endif;
            return $data;
        })
        ->addColumn('commision', function ($payment_seetings) {
                $data = $payment_seetings->commision.''.'%';
            return $data;
        })

        ->rawColumns(['commision','status','action'])

        ->make(true);
    }
    
    public function payment_gateway_create()
    {
        if (!hasTask('admin/payment/settings'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            return view('admin.payment.settings.create');
        }
    }
    
    public function payment_gateway_store(Request $data)
    {
        if (!hasTask('admin/payment/settings'))
        {
            return view('errors.404');
        }
        $fields['merchant_account_id'] = Input::get('merchant_account_id');
        $fields['merchant_key'] = Input::get('merchant_key');
        $fields['merchant_secret_key'] = Input::get('merchant_secret_key');
        $fields['merchant_password'] = Input::get('merchant_password');
        $fields['payment_commision'] = Input::get('payment_commision');
        $rules = array(
            'merchant_account_id' => 'required',
            'merchant_key' => 'required',
            'merchant_secret_key' => 'required',
            'merchant_password' => 'required',
            'payment_commision' => 'required|numeric|max:100',
        );
        $gateway_name = Input::get('gateway_name');
        foreach ($gateway_name  as $key => $value) {
            $fields['gateway_name'.$key] = $value;
            $rules['gateway_name'.'1'] = 'required|unique:payment_gateways_info,name';
        }
        $validator = Validator::make($fields, $rules);    
        // process the validation
        if ($validator->fails())
        { 
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            try{
                $Gateways = new Gateways;
                $Gateways->account_id = $_POST['merchant_account_id'];
                $Gateways->merchant_key = $_POST['merchant_key'];
                $Gateways->merchant_secret_key = $_POST['merchant_secret_key'];
                $Gateways->merchant_password = $_POST['merchant_password'];
                $Gateways->commision = $_POST['payment_commision'];
                $Gateways->payment_mode = $_POST['payment_mode'];
                $Gateways->created_date = date("Y-m-d H:i:s");          
                $Gateways->modified_date = date("Y-m-d H:i:s");
                $Gateways->active_status =  isset($_POST['status']) ? $_POST['status']: 0;
                $Gateways->save();
                $this->gateway_save_after($Gateways,$_POST);
                Session::flash('message', trans('messages.Payment gateway has been added successfully'));
            }catch(Exception $e) {
                    Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/payment/settings');
        }
    }
    
    public function payment_gateway_edit($id)
    {
        if (!hasTask('admin/payment/settings'))
        {
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }
        else{
            $payment_list = get_active_payment_gateway_list();
            $info = new Gateways_info;
            $Gateways = Gateways::find($id);
            return view('admin.payment.settings.edit')->with('data', $Gateways)->with('infomodel', $info)->with('payment_list', $payment_list);
        }
    }
            /**
     * Update the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function payment_gateway_update(Request $data, $id)
    {
        if (!hasTask('admin/payment/settings'))
        {
            return view('errors.404');
        }
        $fields['merchant_account_id'] = Input::get('merchant_account_id');
        $fields['merchant_key'] = Input::get('merchant_key');
        $fields['merchant_secret_key'] = Input::get('merchant_secret_key');
        $fields['merchant_password'] = Input::get('merchant_password');
        $fields['payment_commision'] = Input::get('payment_commision');
        $rules = array(
            'merchant_account_id' => 'required',
            'merchant_key' => 'required',
            'merchant_secret_key' => 'required',
            'merchant_password' => 'required',
            'payment_commision' => 'required|numeric|max:100',
        );        
        $gateway_name = Input::get('gateway_name');
        foreach ($gateway_name  as $key => $value) {
            $fields['gateway_name'.$key] = $value;
            $rules['gateway_name'.'1'] = 'required|unique:payment_gateways_info,name,'.$id.',payment_id';
            
        }
        $validator = Validator::make($fields, $rules);    
                // process the validation
        if ($validator->fails())
        { 
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            try{
                $Gateways = Gateways::find($id); 
                $Gateways->account_id = $_POST['merchant_account_id'];
                $Gateways->merchant_key = $_POST['merchant_key'];
                $Gateways->merchant_secret_key = $_POST['merchant_secret_key'];
                $Gateways->merchant_password = $_POST['merchant_password'];
                $Gateways->commision = $_POST['payment_commision'];
                $Gateways->payment_mode = $_POST['payment_mode'];    
                $Gateways->modified_date = date("Y-m-d H:i:s");
                $Gateways->active_status =  isset($_POST['status']) ? $_POST['status']: 0;
                $Gateways->save();
                $this->gateway_save_after($Gateways,$_POST);
                Session::flash('message', trans('messages.Payment gateway has been updated successfully'));
            }catch(Exception $e) {
                    Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/payment/settings');
        }
    }
    
            /**
     * add,edit datas  saved in main table 
     * after inserted in sub tabel.
     *
     * @param  int  $id
     * @return Response
     */
   public static function gateway_save_after($object,$post)
   {
        $gateway = $object;
        $post = $post;
        if(isset($post['gateway_name'])){
            $gateway_name = $post['gateway_name'];
            try{
                $affected = DB::table('payment_gateways_info')->where('payment_id', '=', $object->id)->delete();
                $languages = DB::table('languages')->where('status', 1)->get();
                foreach($languages as $key => $lang){
                    if(isset($gateway_name[$lang->id]) && $gateway_name[$lang->id]!=""){
                        $infomodel = new Gateways_info;
                        $infomodel->language_id = $lang->id;
                        $infomodel->payment_id = $object->id; 
                        $infomodel->name = $gateway_name[$lang->id];
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
    public function payment_gateway_destory($id)
    {
        if (!hasTask('admin/payment/settings'))
        {
            return view('errors.404');
        }
        $data = Gateways::find($id);
        $data->delete();
        Session::flash('message', trans('messages.Payment gateway has been deleted successfully!'));
        return Redirect::to('admin/payment/settings');
    }

}
