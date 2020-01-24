<?php 
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Services_Twilio;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use PushNotification;
use Illuminate\Support\Facades\Redirect;
use DB;
use App;
use URL;
use App\Http\Requests;
use Session;
use Closure;
use Illuminate\Support\Facades\Auth;
use App\Model\coupons;
use App\Model\coupons_infos;
use Illuminate\Support\Facades\Text;
DB::enableQueryLog();  
use Hash;

class Offers extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $data) {
		$post_data = $data->all();
		if(isset($post_data['language']) && $post_data['language']!='' && $post_data['language']==2)
			   {
				   App::setLocale('ar');
			   }
			   else {
				   App::setLocale('en');
			   }
   }
    /*
     * student login
     */
    public function getoffer($language_id)
    { 
        $data    = array();
        if($language_id == 2)
        {
            App::setLocale('ar');
        }
        else 
        {
            App::setLocale('en');
        }
        $result  = array("httpCode" => 400, "status" => 2, "data" =>$data, "Message" => trans('messages.No offer found'));
        $query   = '"coupons_infos"."lang_id" = (case when (select count(coupons_infos.id) as totalcount from coupons_infos where coupons_infos.lang_id = '.$language_id.' and coupons.id = coupons_infos.id) > 0 THEN '.$language_id.' ELSE 1 END)';
        $query1  = "coupons.end_date >= '".date('Y-m-d')."'";
        $coupons = Coupons::join('coupons_infos','coupons_infos.id','=','coupons.id')
                        ->select('coupons.id', 'coupons_infos.coupon_title', 'coupons.coupon_code', 'coupons.start_date', 'coupons.end_date','coupons.modified_date' ,'coupons.created_date', 'coupons.active_status', 'coupons.coupon_status', 'coupons.coupon_image','coupons_infos.coupon_info')
                        ->where('active_status',1)
			->whereRaw($query)
                        ->whereRaw($query1)
                        ->get();
//print_r( $coupons);exit;
        if(count($coupons))
        {
            $coupons_list= array();
            $s = 0;
            foreach($coupons as $st)
            {
                $logo_image = URL::asset('/assets/admin/base/images/coupon/offers.png');
                if(file_exists(base_path().'/public/assets/admin/base/images/coupon/'.$st->coupon_image) && $st->coupon_image != '')
                {
                    $logo_image = url('/assets/admin/base/images/coupon/'.$st->coupon_image."?".$st->modified_date);
                }
                $coupons_list[$s]['id'] = $st->id;
                $coupons_list[$s]['coupon_title'] = $st->coupon_title;
                $coupons_list[$s]['coupon_code'] = $st->coupon_code;
                $coupons_list[$s]['start_date'] = $st->start_date;
                $coupons_list[$s]['end_date'] = $st->end_date;
                $coupons_list[$s]['created_date'] = $st->created_date;
                $coupons_list[$s]['active_status'] = $st->active_status;
                $coupons_list[$s]['coupon_image'] = $logo_image;
                $s++;
            }
            $result = array("response" => array("httpCode" => 200, "status" => true,'data'=>$coupons_list, 'message' => trans('messages.Offers list')));
        }
        return json_encode($result);
    }






  public function mgetoffer()
    { 
        $data    = array();
        $language_id=1;
        if($language_id == 2)
        {
            App::setLocale('ar');
        }
        else 
        {
            App::setLocale('en');
        }

         App::setLocale('en');
        $result  = array("httpCode" => 400, "status" => false, "data" =>$data, "Message" => trans('messages.No offer found'));
        $query   = '"coupons_infos"."lang_id" = (case when (select count(coupons_infos.id) as totalcount from coupons_infos where coupons_infos.lang_id = '.$language_id.' and coupons.id = coupons_infos.id) > 0 THEN '.$language_id.' ELSE 1 END)';
        $query1  = "coupons.end_date >= '".date('Y-m-d')."'";
        $coupons = Coupons::join('coupons_infos','coupons_infos.id','=','coupons.id')
                        ->select('coupons.id', 'coupons_infos.coupon_title', 'coupons.coupon_code', 'coupons.start_date', 'coupons.end_date','coupons.modified_date', 'coupons.created_date', 'coupons.active_status', 'coupons.coupon_status', 'coupons.coupon_image','coupons_infos.coupon_info')
                        ->where('active_status',1)
            ->whereRaw($query)
                        ->whereRaw($query1)
                        ->get();
//print_r( $coupons);exit;
        if(count($coupons))
        {
            $coupons_list= array();
            $s = 0;
            foreach($coupons as $st)
            {
                $logo_image = URL::asset('/assets/admin/base/images/coupon/offers.png');
                if(file_exists(base_path().'/public/assets/admin/base/images/coupon/'.$st->coupon_image) && $st->coupon_image != '')
                {
                    $logo_image = url('/assets/admin/base/images/coupon/'.$st->coupon_image.'?'.$st->modified_date);
                }
                $coupons_list[$s]['id'] = $st->id;
                $coupons_list[$s]['couponTitle'] = $st->coupon_title;
                $coupons_list[$s]['couponCode'] = $st->coupon_code;
                $coupons_list[$s]['startDate'] = $st->start_date;
                $coupons_list[$s]['endDate'] = $st->end_date;
                $coupons_list[$s]['createdDate'] = $st->created_date;
                $coupons_list[$s]['activeStatus'] = $st->active_status;
                $coupons_list[$s]['couponImage'] = $logo_image;
                  $coupons_list[$s]['couponDescription'] =  $st->coupon_info;
                $s++;
            }
            $result = array("httpCode" => 200, "status" => 1,'data'=>$coupons_list, 'message' => trans('messages.Offers list'));
        }
        return json_encode($result);
    }

}
