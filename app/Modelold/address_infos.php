<?php

namespace App\Model;
use Illuminate\Support\Facades\DB;
use Closure;
use Illuminate\Database\Eloquent\Model;

class address_infos extends Model 
{
    public $timestamps  = false;
    private $_languages = array();
    private $_addressLabels = array();
    protected $primaryKey = 'info_id';

    public function getLabel($feild,$language_id=1,$id='') 
    {
        $label=$this->getAddressLabels($feild,$language_id,$id);
        return isset($label->$feild) ? $label->$feild:''; 
    }
    
    public function getAddressLabels($feild,$language_id,$id) 
    {
        if(!isset($this->_addressLabels[$language_id])) {
            $this->_addressLabels[$language_id] = array();
        }
        if(empty($this->_addressLabels[$language_id])) {
            $addressLabels = DB::table('address_infos')
                                ->where('address_infos.address_id','=',$id)
                                ->where('address_infos.language_id','=',$language_id)
                                ->get();
            $address = array();
            foreach($addressLabels as $col) {
                $address[$col->address_id] = $col;
            }
            $this->_addressLabels[$language_id] = $address;
        }
        return $id && isset($this->_addressLabels[$language_id]) && isset($this->_addressLabels[$language_id][$id]) ? $this->_addressLabels[$language_id][$id]:'';
    }
}
