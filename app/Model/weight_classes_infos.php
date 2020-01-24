<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use DB;

class weight_classes_infos extends Model
{
	public $timestamps  = false;
    private $_languages = array();
    private $_weightclassLabels = array();
    //public $primarykey = 'id';
    
    public function getLabel($feild,$language_id=1,$id='') 
    {
		  $label=$this->getWeightClassLabels($feild,$language_id,$id);
		  return isset($label->$feild) ? $label->$feild:''; 
	}
	
	public function getWeightClassLabels($feild,$language_id,$id) 
    {
		if(!isset($this->_weightclassLabels[$language_id])) {
            $this->_weightclassLabels[$language_id] = array();
        }
        if(empty($this->_weightclassLabels[$language_id])) {
            $weight_classLabels = DB::table('weight_classes_infos')
		    ->where('weight_classes_infos.id','=',$id)
		    ->where('weight_classes_infos.lang_id','=',$language_id)
		    ->get();
            $weight_class = array();
            foreach($weight_classLabels as $coul) {
                $weight_class[$coul->id] = $coul;
            }
            $this->_weightclassLabels[$language_id] = $weight_class;
        }
        return $id && isset($this->_weightclassLabels[$language_id]) && isset($this->_weightclassLabels[$language_id][$id]) ? $this->_weightclassLabels[$language_id][$id]:'';
	}
}
