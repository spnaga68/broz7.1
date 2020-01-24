<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Student extends Model {
	protected $table = 'faq';
	protected $fillable = ['id', 'subject', 'answer', 'type'];



}


class driver extends Model {
protected $table = 'drivers';
       protected $find=['id'];
                
}