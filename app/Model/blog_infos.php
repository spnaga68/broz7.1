<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
class blog_infos extends Model
{
	public $timestamps  = false;
	private $_languages = array();
    private $_blogLables = array();    
    protected $primaryKey = 'info_id';

    
    public function getLabel($feild,$language_id=1,$id='') 
    {
		  $label=$this->getBlogLabels($feild,$language_id,$id);
		  return isset($label->$feild) ? $label->$feild:''; 
	}
	
	public function getBlogLabels($feild,$language_id,$id) 
    {
		if(!isset($this->_blogLables[$language_id])) {
            $this->_blogLables[$language_id] = array();
        }
        if(empty($this->_blogLables[$language_id])) {
            $blogLables = DB::table('blog_infos')
		    ->where('blog_infos.blog_id','=',$id)
		    ->where('blog_infos.language_id','=',$language_id)
		    ->get();
            $blogsL = array();
            foreach($blogLables as $coul) {
                $blogsL[$coul->blog_id] = $coul;
            }
            $this->_blogLables[$language_id] = $blogsL;
        }
        return $id && isset($this->_blogLables[$language_id]) && isset($this->_blogLables[$language_id][$id]) ?
                            $this->_blogLables[$language_id][$id]:'';
	}
}
