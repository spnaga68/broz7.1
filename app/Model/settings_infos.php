<?php

namespace App\Model;
use Illuminate\Support\Facades\DB;
use Closure;
use Illuminate\Database\Eloquent\Model;

class settings_infos extends Model 
{
    public $timestamps  = false;
    private $_languages = array();
    private $_settingsLabels = array();

    public function getLabel($feild,$language_id=1,$id='') 
    {
        $label=$this->getSettingsLabels($feild,$language_id,$id);
        return isset($label->$feild) ? $label->$feild:''; 
    }
    
    public function getSettingsLabels($feild,$language_id,$id) 
    {
        if(!isset($this->_settingsLabels[$language_id])) {
            $this->_settingsLabels[$language_id] = array();
        }
        if(empty($this->_settingsLabels[$language_id])) {
            $settingsLabels = DB::table('settings_infos')
                                ->where('settings_infos.id','=',$id)
                                ->where('settings_infos.language_id','=',$language_id)
                                ->get();
            $setting = array();
            foreach($settingsLabels as $col) {
                $setting[$col->id] = $col;
            }
            $this->_settingsLabels[$language_id] = $setting;
        }
        return $id && isset($this->_settingsLabels[$language_id]) && isset($this->_settingsLabels[$language_id][$id]) ? $this->_settingsLabels[$language_id][$id]:'';
    }
}
