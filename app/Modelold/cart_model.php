<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;


class cart_model extends Model 
{   


	public $timestamps  = false;
    protected $table = 'cart';
	protected $primaryKey = 'cart_id';
	
	public static function cart_items($language_id,$user_id)
	{
	
		$query = 'p.lang_id = (case when (select count(*) as totalcount from admin_products where admin_products.lang_id = '.$language_id.' and op.product_id = admin_products.id) > 0 THEN '.$language_id.' ELSE 1 END)';

		$vquery = 'vi.lang_id = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = '.$language_id.' and vn.id = vendors_infos.id) > 0 THEN '.$language_id.' ELSE 1 END)';
		$wquery = 'weight.lang_id = (case when (select count(*) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = '.$language_id.' and weight.id = weight_classes_infos.id) > 0 THEN '.$language_id.' ELSE 1 END)';
		$cart_items =DB::select('SELECT distinct(c.cart_id),
									c.user_id,c.store_id,c.outlet_id,c.cart_status,
									op.original_price,
									op.discount_price,
									op.stock_status,
									op.outlet_id,
									op.vendor_id,
									p.weight_class_id,
									p.weight,
									p.quantity AS product_qty,
									p.image	 AS product_image,
									
									p.id as product_id,
									p.category_id,
									p.sub_category_id,
									p.item_limit,
									p.product_name,
									p.description,
									vi.vendor_name,
									vn.featured_image,
									vn.logo_image,
									out.minimum_order_amount,
									outin.outlet_name,
									out.delivery_charges_fixed,
									out.url_index,
									out.delivery_charges_variation,
									out.delivery_time,
									out.service_tax,cd.quantity,
									out.url_index,
									out.latitude,out.longitude,
									cd.cart_detail_id,
									vn.vendor_key, 
									vn.contact_address, 
									weight.unit, 
									weight.title,
									zones.url_index AS outlet_address
								FROM cart c
								LEFT JOIN cart_detail cd ON c.cart_id = cd.cart_id
								LEFT JOIN admin_products p ON p.id = cd.product_id
								LEFT JOIN outlet_products op ON op.product_id = cd.product_id
								LEFT JOIN outlets OUT ON out.id = c.outlet_id
								LEFT JOIN outlet_infos outin ON outin.id = out.id
								LEFT JOIN vendors vn ON out.vendor_id = vn.id
								LEFT JOIN vendors_infos VI ON vi.id = vn.id
								Left join weight_classes ON weight_classes.id = p.weight_class_id
								Left join zones zones ON zones.id = out.location_id
								Left join weight_classes_infos weight ON weight.id =weight_classes.id
								where '.$query.' AND '.$vquery.' AND '.$wquery.' AND c.user_id = ? ORDER BY cd.cart_detail_id' , array($user_id));
		return $cart_items;
	}
	public static function delivery_address($address_id)
	{
		$latlng = DB::table('user_address')
			->select('user_address.latitude','user_address.longitude')
			->where("user_address.id", "=", $address_id)
			->get();
		if(count($latlng))
		{
			return $latlng[0];

		}else
		{
			return array();
		}
		//print_r($latlng);exit;

	}
}
