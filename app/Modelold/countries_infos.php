<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
class countries_infos extends Model
{
	public $timestamps  = false;
	private $_languages = array();
    private $_countryLables = array();
    //public $primarykey = 'id';
    
    public function getLabel($feild,$language_id=1,$id='') 
    {
		  $label=$this->getCountryLabels($feild,$language_id,$id);
		  return isset($label->$feild) ? $label->$feild:''; 
	}
	
	public function getCountryLabels($feild,$language_id,$id) 
    {
		if(!isset($this->_countryLables[$language_id])) {
            $this->_countryLables[$language_id] = array();
        }
        if(empty($this->_countryLables[$language_id])) {
            $countryLables = DB::table('countries_infos')
		    ->where('countries_infos.id','=',$id)
		    ->where('countries_infos.language_id','=',$language_id)
		    ->get();
            $countrysL = array();
            foreach($countryLables as $coul) {
                $countrysL[$coul->id] = $coul;
            }
            $this->_countryLables[$language_id] = $countrysL;
        }
        return $id && isset($this->_countryLables[$language_id]) && isset($this->_countryLables[$language_id][$id]) ?
                            $this->_countryLables[$language_id][$id]:'';
	}
}
