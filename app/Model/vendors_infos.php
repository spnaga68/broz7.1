<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use DB;

class vendors_infos extends Model
{
	public $timestamps  = false;
    //public $primarykey = 'id';
	private $_languages = array();
    private $_vendorsLabels = array();
    //public $primarykey = 'id';
    
    public function getLabel($feild,$language_id=1,$id='') 
    {
		  $label=$this->getVendorsLabels($feild,$language_id,$id);
		  return isset($label->$feild) ? $label->$feild:''; 
	}
	
	public function getVendorsLabels($feild,$language_id,$id) 
    {
		if(!isset($this->_vendorsLabels[$language_id])) {
            $this->_vendorsLabels[$language_id] = array();
        }
        if(empty($this->_vendorsLabels[$language_id])) {
            $vendorsLabels = DB::table('vendors_infos')
		    ->where('vendors_infos.id','=',$id)
		    ->where('vendors_infos.lang_id','=',$language_id)
		    ->get();
            $vendors = array();
            foreach($vendorsLabels as $col) {
                $vendors[$col->id] = $col;
            }
            $this->_vendorsLabels[$language_id] = $vendors;
        }
        return $id && isset($this->_vendorsLabels[$language_id]) && isset($this->_vendorsLabels[$language_id][$id]) ? $this->_vendorsLabels[$language_id][$id]:'';
	}
}
