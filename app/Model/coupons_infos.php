<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use DB;

class coupons_infos extends Model
{
    public $timestamps     = false;
    private $_languages    = array();
    private $_couponLables = array();
    protected $primaryKey  = 'info_id';

    public function getLabel($feild,$language_id=1,$id='') 
    {
        $label = $this->getCouponLabels($feild,$language_id,$id);
        return isset($label->$feild) ? $label->$feild:''; 
    }
    /* get coupon labels value */
    public function getCouponLabels($feild,$language_id,$id) 
    {
        if(!isset($this->_couponLables[$language_id]))
        {
            $this->_couponLables[$language_id] = array();
        }
        if(empty($this->_couponLables[$language_id]))
        {
            $couponLables = DB::table('coupons_infos')
                                ->where('coupons_infos.id','=',$id)
                                ->where('coupons_infos.lang_id','=',$language_id)
                                ->get();
            $couponsL = array();
            foreach($couponLables as $coul)
            {
                $couponsL[$coul->id] = $coul;
            }
            $this->_couponLables[$language_id] = $couponsL;
        }
        return $id && isset($this->_couponLables[$language_id]) && isset($this->_couponLables[$language_id][$id]) ? $this->_couponLables[$language_id][$id]:'';
    }
}
