<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
class zones_infos extends Model
{
	public $timestamps  = false;
	private $_languages = array();
    private $_zoneLables = array();
    protected $primaryKey = 'info_id';
    //public $primarykey = 'id';
    
    public function getLabel($feild,$language_id=1,$id='') 
    {
		  $label=$this->getZoneLabels($feild,$language_id,$id);
		  return isset($label->$feild) ? $label->$feild:''; 
	}
	
	public function getZoneLabels($feild,$language_id,$id) 
    {
		if(!isset($this->_zoneLables[$language_id])) {
            $this->_zoneLables[$language_id] = array();
        }
        if(empty($this->_zoneLables[$language_id])) {
            $zoneLables = DB::table('zones_infos')
		    ->where('zones_infos.zone_id','=',$id)
		    ->where('zones_infos.language_id','=',$language_id)
		    ->get();
            $zonesL = array();
            foreach($zoneLables as $coul) {
                $zonesL[$coul->zone_id] = $coul;
            }
            $this->_zoneLables[$language_id] = $zonesL;
        }
        return $id && isset($this->_zoneLables[$language_id]) && isset($this->_zoneLables[$language_id][$id]) ?
                            $this->_zoneLables[$language_id][$id]:'';
	}
}
