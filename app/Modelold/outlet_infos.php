<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use DB;

class outlet_infos extends Model
{
    public $timestamps  = false;
    private $_languages = array();
    private $_outletsLabels = array();
    public $primarykey  = 'id';
    
    public function getLabel($feild,$language_id=1,$id='') 
    {
        $label = $this->getOutletsLabels($feild,$language_id,$id);
        return isset($label->$feild) ? $label->$feild:''; 
    }
    
    public function getOutletsLabels($feild,$language_id,$id) 
    {
        if(!isset($this->_outletsLabels[$language_id]))
        {
            $this->_outletsLabels[$language_id] = array();
        }
        if(empty($this->_outletsLabels[$language_id]))
        {
            $outletLabels = DB::table('outlet_infos')
                                ->where('outlet_infos.id','=',$id)
                                ->where('outlet_infos.language_id','=',$language_id)
                                ->get();
            $outlets = array();
            foreach($outletLabels as $col)
            {
                $outlets[$col->id] = $col;
            }
            $this->_outletsLabels[$language_id] = $outlets;
        }
        return $id && isset($this->_outletsLabels[$language_id]) && isset($this->_outletsLabels[$language_id][$id]) ? $this->_outletsLabels[$language_id][$id]:'';
    }
}
