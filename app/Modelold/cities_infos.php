<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
class cities_infos extends Model
{
	public $timestamps  = false;
	private $_languages = array();
    private $_cityLables = array();
    protected $primaryKey = 'info_id';
    //public $primarykey = 'id';
    
    public function getLabel($feild,$language_id=1,$id='') 
    {
		  $label=$this->getCityLabels($feild,$language_id,$id);
		  return isset($label->$feild) ? $label->$feild:''; 
	}
	
	public function getCityLabels($feild,$language_id,$id) 
    {
		if(!isset($this->_cityLables[$language_id])) {
            $this->_cityLables[$language_id] = array();
        }
        if(empty($this->_cityLables[$language_id])) {
            $cityLables = DB::table('cities_infos')
		    ->where('cities_infos.id','=',$id)
		    ->where('cities_infos.language_id','=',$language_id)
		    ->get();
            $citysL = array();
            foreach($cityLables as $coul) {
                $citysL[$coul->id] = $coul;
            }
            $this->_cityLables[$language_id] = $citysL;
        }
        return $id && isset($this->_cityLables[$language_id]) && isset($this->_cityLables[$language_id][$id]) ?
                            $this->_cityLables[$language_id][$id]:'';
	}
}
