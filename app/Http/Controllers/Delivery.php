<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Model\drivers;
use App\Model\driver_orders;
use App\Model\driver_track_location;
use App\Model\settings;
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
use Illuminate\Support\Facades\Text;
use App\Model\delivery_time_interval;
use App\Model\delivery_time_slots;

class Delivery extends Controller
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
     * Show the application drivers list.
     *
     * @return \Illuminate\Http\Response
     */
    public function time_interval()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else 
        {
            if(!hasTask('admin/delivery/time-interval'))
            {
                return view('errors.404');
            }
            $time_interval = DB::table('delivery_time_interval')
                                ->select('id','start_time', 'end_time')
                                ->orderBy('start_time', 'asc')
                                ->get(); 
            //print_r( $time_interval); exit;
            SEOMeta::setTitle('Manage Delivery Time - '.$this->site_name);
            SEOMeta::setDescription('Manage Delivery Time - '.$this->site_name);
            return view('admin.delivery.time_intevral')->with('time_interval', $time_interval);
        }
    }
    
    public function slot_setting()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else 
        {
            if(!hasTask('admin/delivery/time-interval'))
            {
                return view('errors.404');
            }
            
            $time_interval = DB::table('delivery_time_interval')
                        ->select('id','start_time', 'end_time')
                        ->orderBy('start_time', 'asc')
                        ->get();
                        
            $time_slots = DB::table('delivery_time_slots')
                        ->select('id','day', 'time_interval_id')
                        ->get();
            $slots_dis = array();
            
            foreach($time_slots as $slots)
            {
                $slots_dis[$slots->day][$slots->time_interval_id] = $slots->time_interval_id;
            }
            //print_r($slots_dis);exit;
            SEOMeta::setTitle('Manage Delivery Time - '.$this->site_name);
            SEOMeta::setDescription('Manage Delivery Time - '.$this->site_name);
            return view('admin.delivery.slot_settings')->with('time_interval', $time_interval)->with('time_slots', $slots_dis);
        }
    }
    
    public function update_interval(Request $data)
    {
        $post_data = $data->all();
        if (Auth::guest())
        {
            $result["status"] = 404;
            return redirect()->guest('admin/login');
        }
        else 
        {
            $result = array();
            $time_slot = array();
            for($i=0; $i<count($post_data['from_time']); $i++)
            {
                $rules['from_time.'.$i] = 'required';
                $rules['to_time.'.$i] = 'required|after:from_time.'.$i;
            }
            
            $validation = Validator::make($post_data, $rules);
            if ($validation->fails())
            {
                $errors = '';
                $j = 0;
                foreach( $validation->errors()->messages() as $key => $value) 
                {
                    $error[] = is_array($value)?implode( ',',$value ):$value;
                }
                $errors = implode( ", \n ", $error );
                $result = array("httpCode" => 400, "errors" => $errors);
            }
            else 
            {
                DB::table('delivery_time_interval')->truncate();
                for($i=0; $i<count($post_data['from_time']); $i++)
                {
                    $delivery    = new delivery_time_interval;
                    $delivery->start_time  = $post_data['from_time'][$i];
                    $delivery->end_time    = $post_data['to_time'][$i];
                    $delivery->created_date = date('Y-m-d H:i:s');
                    $delivery->save();
                    
                }
                $result["status"] = 200;    
                Session::flash('message',trans('Delivery time added  successfully'));
                $result["errors"] = "";
            }
            
        }
        return json_encode($result);exit;
    }
    
    public function update_delivery_slots(Request $data)
    {
        $post_data = $data->all();
        if (Auth::guest())
        {
            $result["status"] = 404;
            return redirect()->guest('admin/login');
        }
        $fields['day'] = Input::get('day');
        $fields['slot'] = Input::get('slot');
        
        $rules = array(
            'day' => 'required',
            'slot' => 'required',
        );
        
        $validation = Validator::make($fields, $rules);
        
        // process the validation
        if ($validation->fails()) {
            //return redirect('categorycreate')->withErrors($validation);
            return Redirect::back()->withErrors($validation)->withInput();
        }
        else 
        {
            DB::table('delivery_time_slots')->truncate();
            if(count($post_data['day'])>0)
            {
                foreach($post_data['day'] as $key=>$day)
                {
                    if(isset($post_data['slot'][$day]))
                    {
                        for($i=0; $i<count($post_data['slot'][$day]); $i++)
                        {
                            //echo "<br/>".$key." ".$post_data['slot'][$day][$i];
                            if(isset($post_data['slot'][$day][$i]))
                            {
                                $delivery    = new delivery_time_slots;
                                $delivery->day  = $key;
                                $delivery->time_interval_id    = $post_data['slot'][$day][$i];
                                $delivery->created_date = date('Y-m-d H:i:s');
                                $delivery->save();
                            }
                        }
                    }
                /** else{
                        //Session::flash('message', trans('Select any one slot '));
                       // return Redirect::to('admin/delivery/slot-setting');
                    }    **/
                }
            }
            Session::flash('message',trans('Time slot updated successfully'));
            return Redirect::to('admin/delivery/slot-setting');
        }
    }
    
}
