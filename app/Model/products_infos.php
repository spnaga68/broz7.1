<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use DB;

class products_infos extends Model
{
    public $timestamps  = false;
    //public $primarykey = 'id';
    private $_languages = array();
    private $_Labels = array();
    //public $primarykey = 'id';
    
    public function getLabel($field,$language_id=1,$id='') 
    {
        $label=$this->getLabels($field,$language_id,$id);
        return isset($label->$field) ? $label->$field:''; 
    }
    public function getLabels($field,$language_id,$id) 
    {
        if(!isset($this->_Labels[$language_id]))
        {
            $this->_Labels[$language_id] = array();
        }
        if(empty($this->_Labels[$language_id]))
        {
            $Labels = DB::table('products_infos')
                        ->where('products_infos.id','=',$id)
                        ->where('products_infos.lang_id','=',$language_id)
                        ->get();
                        //print_r($lables);exit();
            $data = array();
            foreach($Labels as $col)
            {
                $data[$col->id] = $col;
            }
            $this->_Labels[$language_id] = $data;
        }
        return $id && isset($this->_Labels[$language_id]) && isset($this->_Labels[$language_id][$id]) ? $this->_Labels[$language_id][$id]:'';
    }
}
