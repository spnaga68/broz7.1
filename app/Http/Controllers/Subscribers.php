<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Model\newsletter_subscribers;
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

class Subscribers extends Controller
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
        Twitter::setSite($this->site_name);
        App::setLocale('en');
    }

    /**
     * Show the application subscribers list.
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
            if(!hasTask('admin/subscribers')){
                return view('errors.404');
            }
            SEOMeta::setTitle('Manage Subscribers - '.$this->site_name);
            SEOMeta::setDescription('Manage Subscribers - '.$this->site_name);
            return view('admin.subscribers.list');
        }
    }
    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxSubscriberslist()
    {
        $subscribers = DB::table('newsletter_subscribers')
                        ->select('id', 'email', 'is_customer', 'active_status', 'created_date', 'modified_date')
                        ->orderBy('id', 'desc');
        $status = $status_msg = "";
        return Datatables::of($subscribers)->addColumn('action', function ($subscribers) {
            if($subscribers->active_status == 0):
                $status = "fa-unlock";
                $status_msg = @trans("messages.Unblock");
            elseif($subscribers->active_status == 1):
                $status = "fa-lock";
                $status_msg = @trans("messages.Block");
            endif;
            if(hasTask('admin/subscribers/delete') )
            {
                $html = '<div class="btn-group">
                        <a href="'.URL::to("admin/subscribers/delete/".$subscribers->id).'" class="btn btn-xs btn-white delete-'.$subscribers->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;'.trans("messages.Delete").'</a>
                        <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu xs pull-right" role="menu">
                            <li><a href="'.URL::to("admin/subscribers/updateStatus/".$subscribers->id."/".$subscribers->active_status).'" class="status-'.$subscribers->id.'" title="'.$status_msg.'" ><i class="fa '.$status.'"></i>&nbsp;&nbsp;'.$status_msg.'</a></li>
                        </ul>
                    </div>
                    <script type="text/javascript">
                        $( document ).ready(function() {
                            $(".delete-'.$subscribers->id.'").on("click", function(){
                                return confirm("'.trans("messages.Are you sure want to delete?").'");
                            })
                            $(".status-'.$subscribers->id.'").on("click", function(){
                                return confirm("'.trans("messages.Are you sure want to change the status?").'");
                            })
                        });
                    </script>';
                return $html;
            }
        })
        ->addColumn('active_status', function ($subscribers) {
            if($subscribers->active_status == 1):
                $data = '<span class="label label-success" id="status-'.$subscribers->id.'">'.trans("messages.Active").'</span>';
            elseif($subscribers->active_status == 0):
                $data = '<span class="label label-danger" id="status-'.$subscribers->id.'" onclick="change_status('.$subscribers->id.','.$subscribers->active_status.')">'.trans("messages.Inactive").'</span>';
            endif;
            return $data;
        })

                ->rawColumns(['active_status','action'])

        ->make(true);
    }
    /**
     * Delete the specified coupon in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function delete($id)
    {
        if(!hasTask('admin/subscribers/delete')){
                return view('errors.404');
        }
        $data = Newsletter_subscribers::find($id);
        if(!count($data))
        {
            Session::flash('message', 'Invalid Subscriber Details'); 
            return Redirect::to('admin/subscribers');
        }
        $data->delete();
        Session::flash('message', trans('messages.Subscriber has been deleted successfully'));
        return Redirect::to('admin/subscribers');
    }
    //Get status update on subscribers list
    public function UpdateStatus(Request $request)
    {
        if(!hasTask('admin/subscribers/updateStatus')){
                return view('errors.404');
        }
        $id    = $request->id;
        $value = $request->status;
        if ($value == 1)
        {
            $value = 0;
        }
        else {
            $value = 1;
        }
        $Subscribers = Newsletter_subscribers::find($id); 
        $Subscribers->active_status = $value;
        $Subscribers->save();
        Session::flash('message', trans('messages.Subscriber Status has been changed successfully'));
        return Redirect::to('admin/subscribers');
    }
}
