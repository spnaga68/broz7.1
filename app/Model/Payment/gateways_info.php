<?php

namespace App\Model\Payment;

use Illuminate\Database\Eloquent\Model;
use DB;
class gateways_info extends Model
{
	public $timestamps  = false;
	private $_languages = array();
    private $_gatewayLables = array();
    protected $table = 'payment_gateways_info';    
    protected $primaryKey = 'info_id';

    public function getLabel($feild,$language_id=1,$id='') 
    {
		  $label=$this->getGatewayLabels($feild,$language_id,$id);
		  return isset($label->$feild) ? $label->$feild:''; 
	}
	
	public function getGatewayLabels($feild,$language_id,$id) 
    {
		if(!isset($this->_gatewayLables[$language_id])) {
            $this->_gatewayLables[$language_id] = array();
        }
        if(empty($this->_gatewayLables[$language_id])) {
            $gatewayLables = DB::table('payment_gateways_info')
		    ->where('payment_gateways_info.payment_id','=',$id)
		    ->where('payment_gateways_info.language_id','=',$language_id)
		    ->get();
            $gatewaysL = array();
            foreach($gatewayLables as $coul) {
                $gatewaysL[$coul->payment_id] = $coul;
            }
            $this->_gatewayLables[$language_id] = $gatewaysL;
        }
        return $id && isset($this->_gatewayLables[$language_id]) && isset($this->_gatewayLables[$language_id][$id]) ?
                            $this->_gatewayLables[$language_id][$id]:'';
	}
}
