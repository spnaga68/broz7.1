<?php

namespace App\Model;
use Illuminate\Support\Facades\DB;
use Closure;
use Illuminate\Database\Eloquent\Model;

class currencies_infos extends Model 
{
    public $timestamps  = false;
    private $_languages = array();
    private $_currencyLabels = array();
    protected $primaryKey = 'info_id';

    public function getLabel($feild,$language_id=1,$id='') 
    {
        $label=$this->getCurrencyLabels($feild,$language_id,$id);
        return isset($label->$feild) ? $label->$feild:''; 
    }
    
    
    public function getCurrencyLabels($feild,$language_id,$id) 
    {
        if(!isset($this->_currencyLabels[$language_id])) {
            $this->_currencyLabels[$language_id] = array();
        }
        if(empty($this->_currencyLabels[$language_id])) {
            $currencyLabels = DB::table('currencies_infos')
                                ->where('currencies_infos.currency_id','=',$id)
                                ->where('currencies_infos.language_id','=',$language_id)
                                ->get();
            $currency = array();
            foreach($currencyLabels as $cur) {
                $currency[$cur->currency_id] = $cur;
            }
            $this->_currencyLabels[$language_id] = $currency;
        }
        return $id && isset($this->_currencyLabels[$language_id]) && isset($this->_currencyLabels[$language_id][$id]) ? $this->_currencyLabels[$language_id][$id]:'';
    }
}
