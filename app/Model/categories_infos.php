<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
class categories_infos extends Model
{
	public $timestamps  = false;
	private $_languages = array();
    private $_categoryLables = array();
    protected $primaryKey = 'info_id';
    
    public function getLabel($feild,$language_id=1,$id='') 
    {
		  $label=$this->getCategoryLabels($feild,$language_id,$id);
		  return isset($label->$feild) ? $label->$feild:''; 
	}
	
	public function getCategoryLabels($feild,$language_id,$id) 
    {
		if(!isset($this->_categoryLables[$language_id])) {
            $this->_categoryLables[$language_id] = array();
        }
        if(empty($this->_categoryLables[$language_id])) {
            $categoryLables = DB::table('categories_infos')
		    ->where('categories_infos.category_id','=',$id)
		    ->where('categories_infos.language_id','=',$language_id)
		    ->get();
            $categorysL = array();
            foreach($categoryLables as $coul) {
                $categorysL[$coul->category_id] = $coul;
            }
            $this->_categoryLables[$language_id] = $categorysL;
        }
        return $id && isset($this->_categoryLables[$language_id]) && isset($this->_categoryLables[$language_id][$id]) ?
                            $this->_categoryLables[$language_id][$id]:'';
	}
}
