<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Student extends Model {
	protected $table = 'feedback';
	protected $fillable = ['id', 'userName', 'description', 'mailId', 'phone', 'deviceId', 'appVersion', 'deviceModel'];



	/*protected $table = 'products';
	protected $fillable = ['id', 'category_id', 'vendor_id', 'outlet_id', 'weight_class_id', 'weight', 'quantity', 'original_price', 'discount_price', 'created_by', 'modified_by', 'approval_status', 'return_time', 'vendor_category_id-1', 'vendor_type', 'stock_status', 'sub_category_id', 'product_image', 'product_url', 'active_status', 'vendor_category_id', 'product_info_image', 'product_zoom_image', 'product_type', 'description'];*/

}