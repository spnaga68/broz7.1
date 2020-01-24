<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
class cms_infos extends Model
{
	public $timestamps  = false;
	private $_languages = array();
    private $_cmsLables = array();    
    protected $primaryKey = 'info_id';

    
    public function getLabel($feild,$language_id=1,$id='') 
    {
		  $label=$this->getCmsLabels($feild,$language_id,$id);
		  return isset($label->$feild) ? $label->$feild:''; 
	}
	
	public function getCmsLabels($feild,$language_id,$id) 
    {
		if(!isset($this->_cmsLables[$language_id])) {
            $this->_cmsLables[$language_id] = array();
        }
        if(empty($this->_cmsLables[$language_id])) {
            $cmsLables = DB::table('cms_infos')
		    ->where('cms_infos.cms_id','=',$id)
		    ->where('cms_infos.language_id','=',$language_id)
		    ->get();
            $cmssL = array();
            foreach($cmsLables as $coul) {
                $cmssL[$coul->cms_id] = $coul;
            }
            $this->_cmsLables[$language_id] = $cmssL;
        }
        return $id && isset($this->_cmsLables[$language_id]) && isset($this->_cmsLables[$language_id][$id]) ?
                            $this->_cmsLables[$language_id][$id]:'';
	}
}
