<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
 */

//Route::get('form', function(){
//  return view('student.form');
//});
define('test', public_path() . '/' . 'test');
define("FCM_SERVER_KEY", "AAAAzIl1AiA:APA91bGu-ThfOepY30smrHk4Vm-ZkoaF7RI4MeMlQXYBGEn9-QYe_VM4MjuZziLhKewS6L6QdZjMHpZOS6T-wco644NgtsF9DRsptg8BFcPafThGNmZDPg4uMYrvM3LWkZq0YuY2mrJt");
define("BASE_URL", "https://brozapp.com");
$currency =getCurrencyList();
$appinfo =getAppConfig();
$currency_code = isset($currency[0]->currency_code)?$currency[0]->currency_code:'AED';
$country_code = isset($appinfo->country_code)?$appinfo->country_code:'+91';
define("CURRENCYCODE", $currency_code);
define("COUNTRYCODE", $country_code);

define("TWILIO_ACCOUNTSID", "AC6d40eb10c6a8be92b2d097bc848fe7bc");
define("TWILIO_AUTHTOKEN", "a321f99bdd0f15c805e0c0c3387b5184");
define("TWILIO_NUMBER", "+14783471785");


Route::resource('admin/portfolio', 'Portfolio');

/*

|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
 */

Route::group(['middleware' => ['web']], function () {
    //admin//
    Route::auth();
    Route::get('admin/login', 'Admin@index');
    Route::get('hai/{token}', 'Admin@passwordReset');

    Route::get('admin/logout', 'Admin@adminlogout');
    Route::get('admin/dashboard', 'Admin@dashboard');
    Route::get('admin/changepassword', 'Admin@change_password');
    Route::post('admin/password_data', 'Admin@change_details');
    Route::get('admin/editprofile/{id}', 'Admin@edit_profile');
    Route::post('admin/updateprofile/{id}', 'Admin@update_profile');

    //Vendors Authentication & Login
    Route::get('vendors/login', 'Store@login');
    Route::post('vendors/signin', 'Store@signin');
    Route::get('vendors/dashboard', 'Store@home');
    Route::get('vendors/reset', 'Store@forgot');
    Route::post('vendors/forgot_mail', 'Store@forgot_details');
    Route::get('vendors/changepassword', 'Store@change_password');
    Route::post('vendors/password_data', 'Store@change_details');
    Route::get('vendors/signout', 'Store@logout');
    Route::get('vendors/editprofile', 'Store@edit_profile');
    Route::post('vendors/updateprofile/{id}', 'Store@update_profile');

    //cms//
    Route::post('createcms', 'Acms@store');
    Route::get('admin/cms/create', 'Acms@create');
    Route::get('admin/cms/custom-filter-data', 'Acms@getCustomFilterData');
    Route::get('admin/cms/edit/{id}', 'Acms@edit');
    Route::get('admin/cms/view/{id}', 'Acms@show');
    Route::post('updatecms/{id}', 'Acms@update');
    Route::get('admin/cms/delete/{id}', 'Acms@destory');
    Route::get('admin/cms', 'Acms@index');

    // Admin User //
    Route::get('admin/user/viewprofile/{id}', 'User@view_profile');
    Route::get('admin/user/user_chcek_datepicker', 'User@user_chcek_datepicker');

    //admin settings//
    Route::get('admin/settings/general', 'Admin@general_settings');

    
    Route::post('admin/settings/updategeneral/{id}', 'Admin@update_general_settings');
    Route::get('admin/settings/email', 'Admin@email_settings');
    Route::post('admin/settings/updateemail/{id}', 'Admin@update_email_settings');
    Route::get('admin/settings/socialmedia', 'Admin@social_media_settings');
    Route::post('admin/settings/updatemedia/{id}', 'Admin@update_media_settings');
    Route::get('admin/settings/image', 'Admin@image_settings');
    Route::post('admin/settings/updateimagesettings/{id}', 'Admin@update_image_settings');

    //admin store//
    Route::get('admin/settings/store', 'Admin@store');
    Route::post('admin/settings/updatestore/{id}', 'Admin@update_store');

    //admin local//
    Route::get('admin/settings/local', 'Admin@local');
    Route::get('admin/faq/faqans', 'Admin@rFaq');
    Route::post('admin/insert_faq', 'Admin@insert_faq');
    Route::get('admin/faq/index', 'Admin@indexFaq');
    Route::get('admin/trigger/index', 'Admin@indextrigger');

    Route::get('admin/faq/edit{id}', 'Admin@editFaq');
    Route::get('admin/faq/delete{id}', 'Admin@deleteFaq');
    Route::get('admin/faq/view{id}', 'Admin@viewFaq');

    Route::post('admin/settings/updatelocal/{id}', 'Admin@update_local');

    //admin Localisation country//
    Route::get('admin/localisation/country', 'Localisation@country');
    Route::get('admin/country/create', 'Localisation@country_create');
    Route::post('createcountry', 'Localisation@country_store');
    Route::get('admin/country/edit/{id}', 'Localisation@country_edit');
    Route::post('admin/updatecountry/{id}', 'Localisation@country_update');
    Route::get('admin/country/delete/{id}', 'Localisation@country_destory');

    //admin Localisation zones//
    Route::get('admin/localisation/zones', 'Localisation@zones');
    Route::get('admin/zones/create', 'Localisation@zones_create');
    Route::post('createzone', 'Localisation@zone_store');
    Route::get('admin/zones/edit/{id}', 'Localisation@zone_edit');
    Route::post('admin/updatezone/{id}', 'Localisation@zone_update');
    Route::get('admin/zones/delete/{id}', 'Localisation@zone_destory');

    //admin Localisation city//
    Route::get('admin/localisation/city', 'Localisation@city');
    Route::get('admin/city/create', 'Localisation@city_create');
    Route::post('createcity', 'Localisation@city_store');
    Route::get('admin/city/edit/{id}', 'Localisation@city_edit');
    Route::post('admin/updatecity/{id}', 'Localisation@city_update');
    Route::get('admin/city/delete/{id}', 'Localisation@city_destory');

    //admin Localisation Language//
    Route::get('admin/localisation/language', 'Localisation@language');
    Route::get('admin/language/create', 'Localisation@language_create');
    Route::post('createlanguage', 'Localisation@language_store');
    Route::get('admin/language/edit/{id}', 'Localisation@language_edit');
    Route::post('admin/language/update/{id}', 'Localisation@language_update');
    Route::get('admin/language/delete/{id}', 'Localisation@language_destory');

    //admin Localisation Currency//
    Route::get('admin/localisation/currency', 'Localisation@currency');
    Route::get('admin/currency/create', 'Localisation@currency_create');
    Route::post('createcurrency', 'Localisation@currency_store');
    Route::get('admin/currency/edit/{id}', 'Localisation@currency_edit');
    Route::post('admin/currency/update/{id}', 'Localisation@currency_update');
    Route::get('admin/currency/delete/{id}', 'Localisation@currency_destory');

    //Weight Classes
    Route::get('admin/localisation/weight_classes', 'Localisation@weight_classes');
    Route::get('admin/localisation/create_weight_class', 'Localisation@weight_class_create');
    Route::post('weight_class_create', 'Localisation@weight_class_store');
    Route::get('admin/localisation/edit_weight_class/{id}', 'Localisation@weight_class_edit');
    Route::post('admin/update_weight_class/{id}', 'Localisation@weight_class_update');
    Route::get('admin/localisation/delete_weight_class/{id}', 'Localisation@weight_class_destory');

    //Stock Status
    Route::get('admin/localisation/stockstatuses', 'Localisation@stock_statuses');
    Route::get('admin/stockstatus/create', 'Localisation@stock_status_create');
    Route::post('stock_status_create', 'Localisation@stock_status_store');
    Route::get('admin/stockstatus/editstockstatus/{id}', 'Localisation@stock_status_edit');
    Route::post('update_stock_status/{id}', 'Localisation@stock_status_update');
    Route::get('admin/localisation/delete_stock_status/{id}', 'Localisation@stock_status_destory');

    //Order Status
    Route::get('admin/localisation/orderstatuses', 'Localisation@order_statuses');
    Route::get('admin/orderstatus/create', 'Localisation@order_status_create');
    Route::post('order_status_create', 'Localisation@order_status_store');
    Route::get('admin/orderstatus/editorderstatus/{id}', 'Localisation@order_status_edit');
    Route::post('update_order_status/{id}', 'Localisation@order_status_update');
    Route::get('admin/orderstatus/delete_order_status/{id}', 'Localisation@order_status_destory');

    //Return Status
    Route::get('admin/localisation/returnstatuses', 'Localisation@return_statuses');
    Route::get('admin/returnstatus/create', 'Localisation@return_status_create');
    Route::post('return_status_create', 'Localisation@return_status_store');
    Route::get('admin/returnstatus/editreturnstatus/{id}', 'Localisation@return_status_edit');
    Route::post('update_return_status/{id}', 'Localisation@return_status_update');
    Route::get('admin/returnstatus/delete_return_status/{id}', 'Localisation@return_status_destory');

    //Return Actions
    Route::get('admin/localisation/returnactions', 'Localisation@return_actions');
    Route::get('admin/returnaction/create', 'Localisation@return_action_create');
    Route::post('return_action_create', 'Localisation@return_action_store');
    Route::get('admin/returnaction/editreturnaction/{id}', 'Localisation@return_action_edit');
    Route::post('update_return_action/{id}', 'Localisation@return_action_update');
    Route::get('admin/returnactions/delete_return_action/{id}', 'Localisation@return_action_destory');

    //Return Reason
    Route::get('admin/localisation/returnreasons', 'Localisation@return_reasons');
    Route::get('admin/returnreason/create', 'Localisation@return_reason_create');
    Route::post('return_reason_create', 'Localisation@return_reason_store');
    Route::get('admin/returnreason/editreturnreason/{id}', 'Localisation@return_reason_edit');
    Route::post('update_return_reason/{id}', 'Localisation@return_reason_update');
    Route::get('admin/returnreason/delete_return_reason/{id}', 'Localisation@return_reason_destory');

    //Payment Settings
    Route::get('admin/payment/settings', 'Payment@payment_settings');
    Route::get('admin/payment/gatewaycreate', 'Payment@payment_gateway_create');
    Route::post('create_payment_gateway', 'Payment@payment_gateway_store');
    Route::get('admin/payment/gatewayedit/{id}', 'Payment@payment_gateway_edit');
    Route::post('update_payment_gateway/{id}', 'Payment@payment_gateway_update');
    Route::get('admin/payment/deletegateway/{id}', 'Payment@payment_gateway_destory');

    //Vendors
    Route::get('vendors/vendors', 'Vendor@vendors');
    Route::get('vendors/create_vendor', 'Vendor@vendor_create');
    Route::post('vendor_create', 'Vendor@vendor_store');
    Route::get('vendors/edit_vendor/{id}', 'Vendor@vendor_edit');
    Route::post('update_vendor/{id}', 'Vendor@vendor_update');
    Route::get('vendors/delete_vendor/{id}', 'Vendor@vendor_destory');
    Route::get('vendors/vendor_details/{id}', 'Vendor@vendor_show');
    Route::post('admin/vendors/bulkdelete', 'Vendor@bulkdeletevendor');

    Route::get('vendors/bulkimport', 'Vendor@bulkimport');
    Route::post('bulk_import', 'Vendor@bulk_import');

    //Outlets
    Route::get('vendors/outlets', 'Vendor@branches');
    Route::get('vendors/create_outlet', 'Vendor@branch_create');
    Route::post('outlet_create', 'Vendor@branch_store');
    Route::get('vendors/edit_outlet/{id}', 'Vendor@branch_edit');
    Route::post('update_outlet/{id}', 'Vendor@branch_update');
    Route::get('vendors/delete_outlet/{id}', 'Vendor@branch_destory');
    Route::get('vendors/outlet_details/{id}', 'Vendor@branch_show');
    Route::post('vendors/outlet/bulkdelete', 'Vendor@bulkdeleteoutlet');

    //Vendor Outlets
    Route::get('vendor/outlets', 'Store@branches');
    Route::get('vendor/create_outlet', 'Store@branch_create');
    Route::post('vendor/outlet_create', 'Store@branch_store');
    Route::get('vendor/edit_outlet/{id}', 'Store@branch_edit');
    Route::post('vendor/update_outlet/{id}', 'Store@branch_update');
    Route::get('vendor/delete_outlet/{id}', 'Store@branch_destory');
    Route::get('vendor/outlet_details/{id}', 'Store@branch_show');

    Route::post('c_list1/coupon_outlet_list', 'Store@getAllVendorOutletList');
    Route::post('c_list1/coupon_product_list', 'Store@getAllOutletProductList');
    Route::post('admin/read_notifications', 'Store@notifications_read');

    //Managers
    Route::get('vendors/outlet_managers', 'Vendor@outlet_managers');
    Route::get('vendors/create_outlet_managers', 'Vendor@outlet_managers_create');
    Route::post('create_manager', 'Vendor@outlet_managers_store');
    Route::get('vendors/edit_outlet_manager/{id}', 'Vendor@outlet_managers_edit');
    Route::post('admin/managers/update/{id}', 'Vendor@outlet_managers_update');
    Route::get('vendors/delete_outlet_managers/{id}', 'Vendor@outlet_managers_destory');

    //Managers
    Route::get('vendor/outletmanagers', 'Store@outlet_managers');
    Route::get('vendor/create_outlet_managers', 'Store@outlet_managers_create');
    Route::post('vendor/create_manager', 'Store@outlet_managers_store');
    Route::get('vendor/edit_outlet_manager/{id}', 'Store@outlet_managers_edit');
    Route::post('vendor/managers/update/{id}', 'Store@outlet_managers_update');
    Route::get('vendor/delete_outlet_managers/{id}', 'Store@outlet_managers_destory');
    Route::get('/managers/confirmation', 'Store@signup_confirmation_manager');

    //Products
    Route::get('admin/products', 'Product@index');
    Route::get('admin/products/create_product', 'Product@create');
    Route::post('product_create', 'Product@store');
    Route::get('admin/products/edit_product/{id}', 'Product@edit');
    Route::post('update_product/{id}', 'Product@update');
    Route::get('admin/products/delete_product/{id}', 'Product@destory');
    Route::get('admin/products/product_details/{id}', 'Product@show');
    Route::post('admin/products/bulkdelete', 'Product@bulkdelete');

    //Route::get('admin/products/bulkimports', 'Product@bulkimports');
    //Route::post('bulk_import', 'Product@bulk_import');
    //Products
    Route::get('vendor/products', 'Store@index');
    Route::get('vendor/products/create_product', 'Store@product_create');
    Route::post('vendor/product_create', 'Store@product_store');
    Route::get('vendor/products/edit_product/{id}', 'Store@product_edit');
    Route::post('vendor/update_product/{id}', 'Store@update_product');
    Route::get('vendor/products/delete_product/{id}', 'Store@product_destory');
    Route::get('vendor/products/product_details/{id}', 'Store@product_show');

    //categories//
    Route::get('admin/category', 'Category@index');
    Route::get('admin/category/create', 'Category@create');
    Route::post('createcategory', 'Category@store');
    Route::get('categorycreate', 'Category@create');
    Route::get('admin/category/edit/{id}', 'Category@edit');
    Route::get('admin/category/view/{id}', 'Category@show');
    Route::post('updatecategory/{id}', 'Category@update');
    Route::get('admin/category/delete/{id}', 'Category@destory');

    //Banners//
    Route::get('admin/banners', 'Banner@index');
    Route::get('admin/banner/create', 'Banner@create');
    Route::post('createbanner', 'Banner@store');
    Route::get('admin/banner/edit/{id}', 'Banner@edit');
    Route::post('admin/banner/update/{id}', 'Banner@update');
    Route::get('admin/banner/delete/{banner_setting_id}', 'Banner@destory');

    //Brands//
    Route::get('admin/brands', 'Brand@index');
    Route::get('admin/brand/create', 'Brand@create');
    Route::post('createbrand', 'Brand@store');
    Route::get('admin/brand/edit/{id}', 'Brand@edit');
    Route::post('admin/brand/update/{id}', 'Brand@update');
    Route::get('admin/brand/delete/{banner_setting_id}', 'Brand@destory');

    //Users//
    Route::get('admin/users/index', 'User@user_index');
    Route::get('admin/users/create', 'User@user_create');
    Route::post('createuser', 'User@user_store');
    Route::get('admin/users/delete/{id}', 'User@user_delete');
    Route::get('admin/users/edit/{id}', 'User@user_edit');
    Route::post('update_users/{id}', 'User@user_update');

    //User Groups//
    Route::get('admin/users/groups', 'User@group_index');
    Route::get('admin/groups/create', 'User@group_create');
    Route::post('creategroup', 'User@group_store');
    Route::get('admin/groups/edit/{id}', 'User@group_edit');
    Route::get('admin/groups/delete/{id}', 'User@group_delete');
    Route::post('update_group/{id}', 'User@group_update');

    //User Address Types//
    Route::get('admin/users/addresstype', 'User@address_index');
    Route::get('admin/addresstype/create', 'User@address_create');
    Route::post('createaddresstype', 'User@address_store');
    Route::get('admin/addresstype/edit/{id}', 'User@address_edit');
    Route::post('update_addresstype/{id}', 'User@address_update');
    Route::get('admin/addresstype/delete/{id}', 'User@address_delete');

    //Notification Templates//
    Route::get('admin/templates/email', 'Template@index');
    Route::get('admin/templates/create', 'Template@create');
    Route::post('createtemplate', 'Template@store');
    Route::get('admin/templates/delete/{id}', 'Template@destroy');
    Route::get('admin/templates/edit/{id}', 'Template@edit');
    Route::post('admin/template/update/{id}', 'Template@update');
    Route::get('admin/templates/view/{id}', 'Template@view');

    //Notification Subject//
    Route::get('admin/template/subjects', 'Template@subject_index');
    Route::get('admin/subjects/create', 'Template@subject_create');
    Route::post('createsubject', 'Template@subject_store');
    Route::get('admin/subjects/edit/{id}', 'Template@subject_edit');
    Route::post('admin/subjects/update/{id}', 'Template@subject_update');

    //Admin Drivers Management
    Route::get('admin/drivers', 'Driver@index');
    Route::get('admin/drivers/create', 'Driver@create');
    Route::post('create_driver', 'Driver@store');
    Route::get('admin/drivers/edit/{id}', 'Driver@edit');
    Route::post('admin/drivers/update/{id}', 'Driver@update');
    Route::get('admin/drivers/view/{id}', 'Driver@show');
    Route::get('admin/drivers/delete/{id}', 'Driver@delete');
    Route::post('drivers/CityList', 'Driver@getCityData');
    Route::get('admin/driver-location', 'Driver@driver_location_list');
    Route::get('admin/driver-settings', 'Driver@driver_settings');
    Route::post('admin/drivers/updatesettings/{id}', 'Driver@driver_settings_update');
//     Route::controller('admin/drivers', 'Driver', [
//         'anyAjaxdriverlist' => 'listDriverAjax.data',
//         'index' => 'datatables',
//     ]);

    Route::get('listDriverAjax/data', ['as'=>'listDriverAjax.data','uses'=>'Driver@anyAjaxdriverlist']);
    //Route::get('admin/drivers/datatables', 'Driver@index');

/*    Route::get('datatable', ['uses'=>'UserController@datatable']);
    Route::get('datatable/getposts', ['as'=>'datatable.getposts','uses'=>'UserController@getPosts']);
    //Admin Newsletter*/
    Route::get('admin/newsletter', 'Admin@newsletter');
    Route::post('send_newsletter', 'Admin@send_newsletter');
    Route::post('list/all_customers', 'Admin@getAllCustomersData');
    Route::post('list/newsletter_subscribers', 'Admin@getAllSubscribersData');
    Route::post('list/customers_group', 'Admin@getAllCustomersGroupData');

    //Admin Coupon Management
    Route::get('admin/coupons', 'Coupon@index');
    Route::get('admin/coupons/create', 'Coupon@create');
    Route::post('create_coupon', 'Coupon@store');
    Route::get('admin/coupons/edit/{id}', 'Coupon@edit');
    Route::post('admin/coupons/update/{id}', 'Coupon@update');
    Route::get('admin/coupons/view/{id}', 'Coupon@show');
    Route::get('admin/coupons/delete/{id}', 'Coupon@delete');
    Route::post('c_list/coupon_outlet_list', 'Coupon@getAllVendorOutletList');
    Route::post('c_list/coupon_product_list', 'Coupon@getAllOutletProductList');
  /*  Route::controller('admin/coupons', 'Coupon', [
        'anyAjaxcouponlist' => 'listCouponAjax.data',
        'index' => 'datatables',
    ]);*/
    Route::get('listCouponAjax/data', ['as'=>'listCouponAjax.data','uses'=>'Coupon@anyAjaxcouponlist']);


    // Admin Subscribers Management
    Route::get('admin/subscribers', 'Subscribers@index');
    Route::get('admin/subscribers/delete/{id}', 'Subscribers@delete');
    Route::get('admin/subscribers/updateStatus/{id}/{status}', 'Subscribers@UpdateStatus');
   /* Route::controller('admin/subscribers', 'Subscribers', [
        'anyAjaxSubscriberslist' => 'listSubscriberAjax.data',
        'index' => 'datatables',
    ]);*/
    Route::get('listSubscriberAjax/data', ['as'=>'listSubscriberAjax.data','uses'=>'Subscribers@anyAjaxSubscriberslist']);


    //blogs//
    Route::get('admin/blog', 'Blog@index');
    Route::get('admin/blog/create', 'Blog@create');
    Route::post('createblog', 'Blog@store');
    Route::get('admin/blog/edit/{id}', 'Blog@edit');
    Route::get('admin/blog/view/{id}', 'Blog@show');
    Route::post('updateblog/{id}', 'Blog@update');
    Route::get('admin/blog/delete/{id}', 'Blog@destory');

    //Permission Roles//
    Route::get('system/permission', 'Roles@index');
    Route::get('system/permission/create', 'Roles@create');
    Route::post('system/rolecreate', 'Roles@store');
    Route::get('system/permission/edit/{id}', 'Roles@edit');
    Route::post('update_role/{id}', 'Roles@update');
    Route::get('system/permission/delete/{id}', 'Roles@destory');

    //Permission Users//
    Route::get('permission/users', 'Roles@users');
    Route::get('permission/usercreate', 'Roles@user_create');
    Route::post('permission/userstore', 'Roles@user_store');
    Route::get('permission/users/edit/{id}', 'Roles@user_edit');
    Route::post('usersupdate/{id}', 'Roles@user_update');
    Route::get('permission/users/delete/{id}', 'Roles@users_destory');

    //portfolios//
    Route::post('createportfolio', 'Portfolio@store');
    Route::get('portfoliocreate', 'Portfolio@create');
    Route::get('admin/portfolio/edit/{id}', 'Portfolio@edit');
    Route::get('admin/portfolio/view/{id}', 'Portfolio@show');
    Route::post('updateportfolio/{id}', 'Portfolio@update');
    Route::get('admin/portfolio/delete/{id}', 'Portfolio@destory');

    Route::get('admin/delivery/time-interval/', 'Delivery@time_interval');
    Route::post('admin/delivery/update-interval/', 'Delivery@update_interval');
    Route::get('admin/delivery/slot-setting/', 'Delivery@slot_setting');
    Route::post('admin/delivery/update_delivery_slots/', 'Delivery@update_delivery_slots');

    //Return Orders Management for Admin
    Route::get('orders/return_orders', 'Returnorders@index');
    Route::get('orders/return_orders_view/{id}', 'Returnorders@show');
    Route::post('update_return_order/{id}', 'Returnorders@update');
    Route::post('admin/orders/refund-to-customer', 'Returnorders@refund_customer');

    //Return Orders Management for Vendors
    Route::get('vendors/return_orders', 'Store@return_orders_list');
    Route::get('vendors/return_orders_view/{id}', 'Store@return_orders_show');
    Route::post('vendors/update_return_orders/{id}', 'Store@return_orders_update');

    //front end cms//
    Route::get('stores/{city}/{location}', 'Frontstore@store_list');
    //Route::get('stores', 'Frontstore@store_list');
    Route::get('stores/{city}/{location}/{category}', 'Frontstore@store_list');
    Route::get('stores/{city}/{location}/{latitude}/{longitude}', 'Frontstore@store_list');
    Route::get('stores/{category}', 'Frontstore@store_list');
    Route::post('/stores_outlet', 'Frontstore@store_outlet_list');
    Route::post('/store_list_ajax_front', 'Frontstore@store_list_ajax_front');

    Route::get('/', 'Front@home');

    Route::get('/dynamic', 'Front@dynamics');
    //Route::get('/table', 'Front@table');

    Route::post('/login_user', 'Welcome@login_user');
    //Route::get('/', 'Front@welcome');
    Route::get('/about-us', 'Front@aboutus');
    Route::get('/weare-hiring', 'Front@weare_hiring');
    Route::get('/press-contact', 'Front@press_contact');
    Route::get('/ourservice-areas', 'Front@ourservice_areas');
    Route::get('/aboutus/filter/{id}', 'Front@aboutus');
    Route::get('/register-your-store', 'Front@register_your_store');
    //Route::get('/cart', 'Front@cart');
    Route::get('/portfolios/', 'Front@portfolios');
    Route::get('/portfolios/filter/{id}', 'Front@portfolios');
    Route::get('/portfolios/info/{id}', 'Front@portfolios_info');
    Route::get('/contact-us', 'Front@contactus');
    Route::get('/sitmap', 'Front@sitmap');
    Route::get('/faq', 'Front@faq');
    Route::get('/account-settings', 'Front@accountsettings');
    Route::get('/mint', 'Front@mint');
    Route::get('/price', 'Front@price');
    Route::get('/request', 'Front@request');
    Route::get('/editrequest', 'Front@editrequest');
    Route::post('postcontactus', 'Front@storecontact');
    Route::get('/blog', 'Front@blog');
    Route::get('/blog/filter/{id}', 'Front@blog');
    Route::get('/blog/info/{id}', 'Front@blog_info');
    Route::get('/thankyou', 'Front@thankyou');
    Route::get('thank-you', 'checkout@getDonePayFort');
    Route::get('/filter/{id}', 'Front@welcome');
    //front end cms//
    Route::get('cms/{id}', 'Front@cms');
    Route::get('/cms-mob/{id}/{language}', 'Front@cms_mob');
    Route::get('/mob/about-us/{language}', 'Front@aboutus_mob');
    Route::get('/mob/faq/{id}/{language}', 'Front@faq_mob');

    //Language Translate//
    Route::post('changelocale', ['as' => 'changelocale', 'uses' => 'Translation@changeLocale']);
    Route::get('/offer', 'Front@offer');
    //All ajax requests goes here
    Route::post('list/CityList', 'Store@getCityData');
    Route::post('list/LocationList', 'Store@getLocationData');
    Route::post('list1/LocationList', 'Store@getFrontLocationData');
    Route::post('list/OutletList', 'Store@getOutletData');
    Route::post('list/SubCategoryList', 'Store@getSubCategoryData');

    Route::post('list/SubCategoryListUpdated', 'Store@getSubCategoryDataUpdated');
    Route::post('list/Maincategorylist', 'Store@Maincategorylist');
    Route::post('list/getVendorcategorylist', 'Store@getVendorcategorylist');

    Route::post('list/ProductMaincategorylist', 'Store@ProductMaincategorylist');

    Route::post('translate', 'Translation@translate');
    Route::post('user/activity/ajaxlist', 'User@loadactivityajax');
    Route::post('admin/banner/ajaxupdate', 'Banner@ajaxupdate');
    // front End **/
    Route::get('/signup/confirmation/{key}/{email}/{password}', 'Front@signup_confirmation');
    Route::get('/account/welcome', 'Front@thankyou');
    Route::get('admin/modules/settings', 'Localisation@module_settings');
    Route::get('admin/modules/edit/{id}', 'Localisation@module_settings_edit');
    Route::post('admin/update_module/{id}', 'Localisation@module_update');
    Route::get('admin/modules/delivery_settings', 'Localisation@delivery_settings');
    Route::post('admin/settings/update_delivery_settings/{id}', 'Localisation@delivery_settings_update');

    Route::post('login-user', 'Front@login_user');
    Route::post('signup-user', 'Front@signup_user');
    Route::post('storeregister-user', 'Front@store_register_user');
    Route::post('member-ship', 'Front@user_membership');

    Route::post('rating', 'Front@user_rating');
    Route::post('product-rating', 'Front@product_rating');
    Route::post('/forgot_password', 'Front@forgot_password');
    Route::post('/forgotOtp', 'Front@forgotOtp');
    Route::get('/logout', 'Front@logout');
    Route::get('/profile', 'Profile@profile');
    Route::post('update-profile', 'Profile@update_profile');
    Route::get('/change-password', 'Welcome@change_password');
    Route::post('/update-password', 'Welcome@update_password');

    Route::get('auth/facebook', 'Front@redirectToProvider');
    Route::get('/auth/facebook/callback', 'Front@handleProviderCallback');
    Route::post('/location_outlet', 'Front@location_outlet');

    Route::get('store', 'Frontstore@index');
    Route::get('store/info/{id}', 'Frontstore@store_info');
    Route::get('store/info/{id}/{category}', 'Frontstore@store_info');
    Route::post('addtofavourite', 'Frontstore@addtofavourite');
    Route::get('cards', 'Welcome@cards');
    Route::get('new-card', 'Welcome@new_card');
    Route::post('store-card', 'Welcome@store_card');
    Route::get('edit-card/{id}', 'Welcome@edit_card');
    Route::post('update-card', 'Welcome@update_card');
    Route::get('delete-card/{id}', 'Welcome@delete_card');
    Route::get('checkout', 'checkout@index');

    Route::get('new-address', 'Welcome@new_address');
    Route::post('get_city', 'Welcome@get_city');
    Route::post('store-address', 'Welcome@store_address');
    Route::post('store-address-ajax', 'Welcome@store_address_ajax');

    Route::get('edit-address/{id}', 'Welcome@edit_address');
    Route::post('update-address', 'Welcome@update_address');
    Route::get('delete-address/{id}', 'Welcome@delete_address');

    Route::get('delete-address/{id}', 'Welcome@delete_address');
    Route::get('favourites', 'Welcome@favourites');
    Route::post('profile_image', 'Welcome@profile_image');
    Route::get('cart', 'Usercart@index');
    Route::get('table', 'Usercart@indexCopy');
    Route::get('cart-add', 'Usercart@add_to_cart');
    Route::post('addtocart', 'Usercart@add_cart_info');
    Route::post('update-cart', 'Usercart@update_cart');
    Route::post('delete-cart', 'Usercart@delete_cart');
    Route::post('proceed-checkout', 'checkout@proceed_checkout');
    Route::get('offline_payment', 'checkout@offline_payment');
    Route::get('thankyou/{id}', 'checkout@thankyou');
    Route::get('orders/', 'Welcome@orders');
    Route::post('update-promcode', 'checkout@update_promocode');
    Route::get('order-info/{id}', 'Welcome@orders_info');
    Route::post('check-otp', 'checkout@check_otp');
    Route::post('send-otp', 'checkout@send_otp');
    Route::get('re-order/{id}', 'checkout@re_order');
    Route::get('cancel-order/{id}', 'checkout@cancel_order');
    Route::get('invoice-order/{id}', 'Welcome@invoice');
    //Product details
    Route::get('/product/info/{url_index}', 'Frontstore@product_info');
    Route::get('/product/info/{outlet_url}/{url_index}', 'Frontstore@product_info');
    //Admin Reviews
    Route::get('admin/reviews', 'Reviews@index');
    Route::get('admin/reviews/approve/{id}', 'Reviews@approve');
    Route::get('admin/reviews/view/', 'Reviews@view');
    Route::get('admin/reviews/delete/{id}', 'Reviews@destory');
    //Admin Reviews
    Route::get('admin/product-reviews', 'Productreviews@index');
    Route::get('admin/product-reviews/approve/{id}', 'Productreviews@approve');
    Route::get('admin/product-reviews/view/', 'Productreviews@view');
    Route::get('admin/product-reviews/delete/{id}', 'Productreviews@destory');

    //Vendor Reviews
    Route::get('vendors/reviews/', 'Store@reviews');
    Route::get('vendors/reviews/view/', 'Store@view_review');
    Route::post('return_order/', 'Welcome@return_order');
    Route::get('product-list/', 'Frontstore@product_list');
    Route::get('products/{category}/{outlet}', 'Frontstore@products');
    Route::get('products/{category}/{outlet}/{id}', 'Frontstore@products');
    Route::get('products/{category}/{outlet}/{id}/{sid}', 'Frontstore@products');

    Route::get('distance-calculate', 'Frontstore@GetDrivingDistance');
    //Admin Orders
    Route::get('admin/orders/index', 'Orders@index');
    Route::get('admin/orders/info/{id}', 'Orders@order_info');
    Route::post('admin/orders/update-status', 'Orders@update_status');
    Route::get('admin/orders/load_history/{id}', 'Orders@load_history');
    Route::get('admin/orders/delete/{id}', 'Orders@order_destory');
    Route::post('admin/orders/driver-orders', 'Orders@driver_orders');
    Route::post('admin/orders/assign-driver', 'Orders@assign_driver_orders');

    Route::get('reports/order', 'Reports@order');
   // Route::post('reports/report-order-list', 'Reports@anyAjaxReportOrderList');
    
    Route::post('report-order-list/data', ['as'=>'report-order-list.data','uses'=>'Reports@anyAjaxReportOrderList']);


    Route::get('reports/returns', 'Reports@returns');
    //Route::post('reports/report_return_order_list', 'Reports@anyAjaxReportReturnOrderList');
    Route::post('report_return_order_list/data', ['as'=>'report_return_order_list.data','uses'=>'Reports@anyAjaxReportReturnOrderList']);

    Route::get('reports/user', 'Reports@user');
    //Route::post('reports/report_customer_order_list', 'Reports@anyAjaxReportCustomerOrderList');

    Route::post('report_customer_order_list/data', ['as'=>'report_customer_order_list.data','uses'=>'Reports@anyAjaxReportCustomerOrderList']);

    //~ Route::get('reports/vendor', 'Reports@vendor');
    Route::get('reports/coupons', 'Reports@coupons');
    //Route::post('reports/report_coupon_list', 'Reports@anyAjaxReportCouponList');

    Route::post('report_coupon_list/data', ['as'=>'report_coupon_list.data','uses'=>'Reports@anyAjaxReportCouponList']);

    Route::get('reports/products', 'Reports@products');
    //Route::post('reports/report_product_list', 'Reports@anyAjaxReportProdcutList');

    Route::post('report_product_list/data', ['as'=>'report_product_list.data','uses'=>'Reports@anyAjaxReportProdcutList']);

    //~ Route::get('reports/customerlocation', 'Reports@customerlocation');
    Route::get('drivers/confirmation', 'Front@driver_confirmation');
    Route::get('getDone', ['as' => 'getDone', 'uses' => 'checkout@getDone']);
    Route::get('getCancel', ['as' => 'getCancel', 'uses' => 'checkout@getCancel']);

    //Vendor Orders
    Route::get('vendors/orders/index', 'Store@orders');
    Route::get('vendors/orders/info/{id}', 'Store@order_info');
    Route::post('vendors/orders/update-status', 'Store@update_status');
    Route::get('vendors/orders/load_history/{id}', 'Store@load_history');

    //Vendor Fund Requests Amount
    Route::get('vendors/request_amount/index', 'Store@request_amount_list');
    Route::get('vendors/request_amount/view/{id}', 'Store@request_amount_show');
    Route::get('vendors/request_amount/create', 'Store@add_amount');
    Route::post('createamount', 'Store@amount_store');

    //Admin Fund Requests
    Route::get('orders/fund_requests', 'Orders@fund_requests_list');
    Route::post('orders/approve_fund_status', 'Orders@update_fund_request');
    //Admin Notification
    Route::get('admin/notifications', 'Notification@index');
    Route::get('admin/push-notifications', 'CommonNotification@push_notification_view');
    Route::get('admin/email-notifications', 'CommonNotification@email_notificaition');
    Route::post('send_email', 'CommonNotification@send_email');
    Route::post('push_notification', 'CommonNotification@push_notification');
    Route::post('send_notification', 'CommonNotification@send_notification');
    Route::post('list/newsletter', 'Admin@getUserData');
    //Vendors Notification
    Route::get('vendors/notifications', 'VendorsNotification@index');
    Route::post('vendors/read_notifications', 'VendorsNotification@notifications_read');
    Route::post('/user-subscribe', 'Front@user_subscribe');
    Route::get('/user-unsubscribe/{id}', 'Front@user_unsubscribe');
    Route::get('/mob-cms/{id}/{language}', 'Frontstore@cms_mob');
    Route::get('/send-notification', 'Cron@send_notification');
    //Route::get('/order-assign-automated','Cron@OrderAssignNotification');
    // Managers Authentication & Login
    Route::get('managers/login', 'Manager@login');
    Route::post('managers/signin', 'Manager@signin');
    Route::get('managers/dashboard', 'Manager@home');
    Route::get('managers/reset', 'Manager@forgot');
    Route::post('managers/forgot_mail', 'Manager@forgot_details');
    Route::get('managers/changepassword', 'Manager@change_password');
    Route::post('managers/password_data', 'Manager@change_details');
    Route::get('managers/signout', 'Manager@logout');
    Route::get('managers/editprofile', 'Manager@edit_profile');
    Route::post('managers/updateprofile/{id}', 'Manager@update_profile');
    Route::get('managers/reviews', 'Manager@reviews');
    Route::get('managers/orders/index', 'Manager@orders');
    Route::get('managers/notifications', 'ManagerNotification@index');
    Route::get('managers/orders/info/{id}', 'Manager@order_info');
    Route::get('managers/reviews/view/', 'Manager@view_review');
    Route::post('managers/read_notifications', 'ManagerNotification@notifications_reading');

    //Manager Products
    Route::get('managers/products', 'Manager@index');
    Route::get('managers/products/create_product', 'Manager@product_create');
    Route::post('managers/product_create', 'Manager@product_store');
    Route::get('managers/products/edit_product/{id}', 'Manager@product_edit');
    Route::post('managers/update_product/{id}', 'Manager@update_product');
    Route::get('managers/products/delete_product/{id}', 'Manager@product_destory');
    Route::get('managers/products/product_details/{id}', 'Manager@product_show');

    //Signup Otp
    Route::post('reg-check-otp', 'Front@register_check_otp');
    Route::post('reg-send-otp', 'Front@reg_send_otp');
    Route::get('admin/orders/cancel/{id}', 'Orders@order_cancel');

    Route::post('c_list/coupon_user_list', 'Store@getAllUsersList');
    Route::post('admin/orders/assign-driver', 'Orders@assign_driver_orders');
    Route::get('admin/orders/edit/{id}', 'Orders@orderedit');
    Route::post('update_cart/{id}', 'Orders@update_cart');
    Route::get('admin/settings/driver_core_settings', 'Admin@driver_core_settings');
    //  Route::get('driverabout', 'Admin@driveraboutus');
    Route::get('/driverabout-us', 'Front@driverabout');
    Route::get('/customerabout-us', 'Front@customerabout');
    Route::get('/driverterms-condition', 'Front@driverterms_condition');
    Route::get('/customerterms-condition', 'Front@customerterms_condition');

    Route::get('admin/settings/customer', 'Admin@customers_settings');
    //Route::post('updatedrivercore/{id}', 'Admin@Updatedrivercore');
    //Route::get('admin/settings/updatedrivercore', 'Admin@Updatedrivercore');
    Route::post('admin/settings/updatedrivercore/{id}', 'Admin@updatedrivercore');
    Route::post('admin/settings/updatecustomercore/{id}', 'Admin@updatecustomercore');
    
    Route::post('admin/reviews/bulkapprove', 'Reviews@bulkapprove');
    
    Route::get('admin/reviews/telr_gateway', 'Reviews@telr_gateway');
    Route::get('admin/import_products', 'Admin@import_products');

    Route::post('importdriver', 'Admin@importdriver');

    Route::post('check_promocode', 'Usercart@check_promocode');

    /**vendor driver**/
    //Route::get('vendors/drivers', 'Driver@vendordrivers');
    Route::get('vendors/drivers', 'VendorsNotification@vendordrivers');
    //Route::post('create_vendordriver', 'Driver@vendor_store');
    Route::post('create_vendordriver', 'VendorsNotification@vendor_store');
    // Route::controller('vendors/drivers', 'Driver', [
    //     'anyAjaxvendordriverlist' => 'listvendorDriverAjax.data',
    //     'index' => 'datatables',
    // ]);
    //Route::get('listvendorDriverAjax/data', ['as'=>'listvendorDriverAjax.data','uses'=>'Driver@anyAjaxvendordriverlist']);


    //Route::get('vendor/create_driver', 'Driver@driver_create');
    Route::get('vendor/create_driver', 'VendorsNotification@driver_create');
    Route::get('vendor/edit_driver', 'Driver@driver_edit');
    //Route::get('vendors/driver_edit/{id}', 'Driver@driver_edit');
        Route::get('vendors/driver_edit/{id}', 'VendorsNotification@driver_edit');
        Route::get('vendors/drivers/view/{id}', 'VendorsNotification@show');

   // Route::post('vendors/driver_update/{id}', 'Driver@driver_update');
    Route::post('vendors/driver_update/{id}', 'VendorsNotification@driver_update');
    Route::post('vendors/orders/request_admin', 'Orders@vendor_request_admin');


    Route::get('admin/refferal/refferal_settings', 'Admin@refferal_settings');
    Route::post('admin/settings/updaterefferal/{id}', 'Admin@updaterefferal');

     /* Vendor Salesperson*/
    Route::get('vendors/salesperson', 'VendorsNotification@vendorSalesperson');
    Route::post('create_vendorSalesperson', 'VendorsNotification@vendor_salespersonStore');
    Route::post('vendors/salesPerson_updates/{id}', 'VendorsNotification@salesPerson_updates');
    Route::get('vendor/create_salesperson', 'VendorsNotification@salesperson_create');
    Route::get('vendors/salesperson_edit/{id}', 'VendorsNotification@salesperson_edit');
    Route::get('vendors/salesperson/view/{id}', 'VendorsNotification@showSalesperson');
    Route::get('vendors/salesperson/delete/{id}', 'VendorsNotification@deleteSalesperson');

    Route::get('admin/feedback/index', 'Admin@indexfeedback');

  /*  Route::controller('admin/feedback', 'Admin', [
        'anyAjaxfeedbacklist' => 'lisfeedbackAjax.data',
        'index' => 'datatables',
    ]);*/
    Route::get('lisfeedbackAjax/data', ['as'=>'lisfeedbackAjax.data','uses'=>'Admin@anyAjaxfeedbacklist']);


    Route::post(' vendors/orders/assign-driver', 'Store@assign_driver_orders');

    Route::post('user/user_details', 'User@users_details');
    Route::get('/privacy_policy.html', 'Front@privacy_policy');
    Route::post('drivers/VendorCityList', 'VendorsNotification@getVendorCityData');
   

    /* Customer Promotion */
    Route::get('admin/settings/customer_promotion', 'Admin@customer_promotion');
    Route::get('admin/customer_promotion/create', 'Admin@customerPromotion_create');
    Route::post('create_customerPromotion', 'Admin@customerPromotion_store');
    Route::get('admin/customer_promotion/edit/{id}', 'Admin@customerPromotion_edit');
    Route::get('admin/customer_promotion/view/{id}', 'Admin@customerPromotion_show');
    Route::get('admin/customer_promotion/delete/{id}', 'Admin@customerPromotion_delete');
    Route::post('admin/customer_promotion/update/{id}', 'Admin@update_customer_promotion');


    /*customer promotion new*/

    Route::get('admin/customer_promotion/offer', 'Admin@offer');
    Route::post('create_offer', 'Admin@create_offer');


    /*customer promotion new*/

    /* Customer Promotion */
   /* Route::controller('admin/settings/customer_promotion', 'Admin', [
        'anyAjaxCustomerPromotionlist' => 'listCustomerPromotionAjax.data',
        'index' => 'datatables',
    ]);*/
    Route::get('listCustomerPromotionAjax/data', ['as'=>'listCustomerPromotionAjax.data','uses'=>'Admin@anyAjaxCustomerPromotionlist']);


     /**telr payment gateway**/
    Route::post('wallet/walletadd', 'Admin@walletAdd');
    Route::post('PromotionwalletAdd', 'Admin@PromotionwalletAdd');
    Route::post('common_promotion', 'Front@common_promotion');
  /*  Route::get('/payment_sucess', 'Front@payment_sucess');
    Route::get('/payment_cancel', 'Front@payment_cancel');
    Route::get('/payment_declain', 'Front@payment_declain');*/
    /**telr payment gateway**/


   /*user promotion*/
    Route::get('/promotion', 'Front@promotion');
    Route::get('/promotion_new', 'Front@promotion_new');
    Route::get('/outlets', 'Front@outlets');
    Route::get('/customerLogin', 'Front@custlogin');
    Route::get('/custsignup', 'Front@custsignup');
    Route::get('/profile', 'Front@profile');
    /*user promotion*/

    /*user outlet info*/
    Route::post('/getOutlet', 'Front@getOutlet');
    Route::get('/sample', 'Front@sample');
    Route::get('/ajaxcheck', 'Front@ajaxcheck');
    Route::post('/loginPhoneCheck', 'Front@loginPhoneCheck');
    Route::post('/signupUserCheck', 'Front@signupUserCheck');
    Route::post('/loginPasswordCheck', 'Front@loginPasswordCheck');
    Route::post('/loginotpCheck', 'Front@loginotpCheck');
    Route::get('/payment_sucess', 'Front@payment_sucess');
    Route::get('/payment_cancel', 'Front@payment_cancel');
    Route::get('/payment_declain', 'Front@payment_declain');
    Route::post('/userotpexpire', 'Front@userotpexpire');

    /*user outlet info*/


    /**login chek*/
    Route::get('/cust_login', 'Front@cust_login');
    Route::post('/loginPhoneCheck_dem', 'Front@loginPhoneCheck_dem');
    Route::post('/loginotpCheck_demo', 'Front@loginotpCheck_demo');
    Route::post('/signupUserCheck_demo', 'Front@signupUserCheck_demo');
    Route::post('/loginPasswordCheck_demo', 'Front@loginPasswordCheck_demo');

    /**login chek*/


    /* wallet quick pay */
    Route::get('admin/settings/wallet_qucik_pay', 'Admin@wallet_qucik_pay');
    Route::post('admin/settings/updatequickpay/{id}', 'Admin@updatequickpay');
    Route::get('admin/telr_walletpage', 'Admin@telr_walletpage');
    Route::post('admin/paymentgatewaychcek', 'Admin@paymentgatewaychcek');
    /*Route::get('admin/customer_promotion/create', 'Admin@customerPromotion_create');
    Route::post('create_customerPromotion', 'Admin@customerPromotion_store');
    Route::get('admin/customer_promotion/edit/{id}', 'Admin@customerPromotion_edit');
    Route::get('admin/customer_promotion/view/{id}', 'Admin@customerPromotion_show');
    Route::get('admin/customer_promotion/delete/{id}', 'Admin@customerPromotion_delete');
    Route::post('admin/customer_promotion/update/{id}', 'Admin@update_customer_promotion');

    Route::controller('admin/settings/customer_promotion', 'Admin', [
        'anyAjaxCustomerPromotionlist' => 'listCustomerPromotionAjax.data',
        'index' => 'datatables',
    ]);*/
    /* wallet quick pay */




});
/*Route::controller('vendors/salesperson', 'VendorsNotification', [
        'anyAjaxvendorsalespersonlist' => 'listvendorsalespersonAjax.data',
        'index' => 'datatables',
    ]);*/
Route::get('listvendorsalespersonAjax/data', ['as'=>'listvendorsalespersonAjax.data','uses'=>'VendorsNotification@anyAjaxvendorsalespersonlist']);

/*Route::controller('vendors/drivers', 'VendorsNotification', [
        'anyAjaxvendordriverlist' => 'listvendorDriverAjax.data',
        'index' => 'datatables',
    ]);*/
Route::get('listvendorDriverAjax/data', ['as'=>'listvendorDriverAjax.data','uses'=>'VendorsNotification@anyAjaxvendordriverlist']);

/*
Route::controller('admin/notifications', 'Notification', [
    'anyAjaxNotificationList' => 'ajaxNotificationList.data',
    'index' => 'datatables',
]);*/
Route::get('ajaxNotificationList/data', ['as'=>'ajaxNotificationList.data','uses'=>'Notification@anyAjaxNotificationList']);

/*Route::controller('vendors/notifications', 'VendorsNotification', [
    'anyAjaxStoreNotificationList' => 'ajaxStoreNotificationList.data',
    'index' => 'datatables',
]);*/

Route::get('ajaxStoreNotificationList/data', ['as'=>'ajaxStoreNotificationList.data','uses'=>'VendorsNotification@anyAjaxStoreNotificationList']);

/*Route::controller('managers/notifications', 'ManagerNotification', [
    'anyAjaxStoreManagerNotificationList' => 'ajaxManagerNotificationList.data',
    'index' => 'datatables',
]);*/
Route::get('ajaxManagerNotificationList/data', ['as'=>'ajaxManagerNotificationList.data','uses'=>'ManagerNotification@anyAjaxStoreManagerNotificationList']);

/*Route::controller('admin/module/settings', 'Localisation', [
    'anyAjaxModules' => 'modules_list.data',
    'module_settings' => 'datatables',
]);*/
Route::get('modules_list/data', ['as'=>'modules_list.data','uses'=>'Localisation@anyAjaxModules']);

/*Route::controller('admin/localisation/country', 'Localisation', [
    'anyAjaxCountry' => 'ajaxcountry.data',
    'country' => 'datatables',
]);*/
Route::get('ajaxcountry/data', ['as'=>'ajaxcountry.data','uses'=>'Localisation@anyAjaxCountry']);

/*Route::controller('admin/cms', 'Acms', [
    'anyCmsAjax' => 'listcmsajax.data',
    'index' => 'datatables',
]);*/
Route::get('listcmsajax/data', ['as'=>'listcmsajax.data','uses'=>'Acms@anyCmsAjax']);

/*
Route::controller('admin/localisation/weight_classes', 'Localisation', [
    'anyAjaxWeightClasses' => 'ajax_weight_classes.data',
    'weightclasses' => 'datatables',
]);*/
Route::get('ajax_weight_classes/data', ['as'=>'ajax_weight_classes.data','uses'=>'Localisation@anyAjaxWeightClasses']);

/*Route::controller('admin/category', 'Category', [
    'anyAjaxCategory' => 'listcategoryajax.data',
    'index' => 'datatables',
]);*/
Route::get('listcategoryajax/data', ['as'=>'listcategoryajax.data','uses'=>'Category@anyAjaxCategory']);

/*
Route::controller('admin/localisation/language', 'Localisation', [
    'anyAjaxLanguage' => 'listlanguageajax.data',
    'language' => 'datatables',
]);*/
Route::get('listlanguageajax/data', ['as'=>'listlanguageajax.data','uses'=>'Localisation@anyAjaxLanguage']);

/*Route::controller('admin/localisation/zones', 'Localisation', [
    'anyAjaxZones' => 'ajaxzones.data',
    'zones' => 'datatables',
]);
*/
Route::get('ajaxzones/data', ['as'=>'ajaxzones.data','uses'=>'Localisation@anyAjaxZones']);

/*
Route::controller('admin/localisation/city', 'Localisation', [
    'anyAjaxCities' => 'ajaxcities.data',
    'city' => 'datatables',
]);
*/
Route::get('ajaxcities/data', ['as'=>'ajaxcities.data','uses'=>'Localisation@anyAjaxCities']);
/*
Route::controller('admin/localisation/currency', 'Localisation', [
    'anyAjaxCurrency' => 'ajaxcurrency.data',
    'currency' => 'datatables',
]);*/

Route::get('ajaxcurrency/data', ['as'=>'ajaxcurrency.data','uses'=>'Localisation@anyAjaxCurrency']);

/*Route::controller('admin/localisation/stockstatuses', 'Localisation', [
    'anyAjaxStockStatus' => 'ajaxstockstatus.data',
    'stock_statuses' => 'datatables',
]);
*/
Route::get('ajaxstockstatus/data', ['as'=>'ajaxstockstatus.data','uses'=>'Localisation@anyAjaxStockStatus']);

/*Route::controller('admin/localisation/orderstatuses', 'Localisation', [
    'anyAjaxOrderStatus' => 'ajaxorderstatus.data',
    'order_statuses' => 'datatables',
]);*/

Route::get('ajaxorderstatus/data', ['as'=>'ajaxorderstatus.data','uses'=>'Localisation@anyAjaxOrderStatus']);

/*Route::controller('admin/localisation/returnstatuses', 'Localisation', [
    'anyAjaxReturnStatus' => 'ajaxreturnstatus.data',
    'return_statuses' => 'datatables',
]);*/

Route::get('ajaxreturnstatus/data', ['as'=>'ajaxreturnstatus.data','uses'=>'Localisation@anyAjaxReturnStatus']);

/*Route::controller('admin/localisation/returnactions', 'Localisation', [
    'anyAjaxReturnaction' => 'ajaxreturnaction.data',
    'return_actions' => 'datatables',
]);*/

Route::get('ajaxreturnaction/data', ['as'=>'ajaxreturnaction.data','uses'=>'Localisation@anyAjaxReturnaction']);

/*Route::controller('admin/localisation/returnreasons', 'Localisation', [
    'anyAjaxReturnreason' => 'ajaxreturnreason.data',
    'return_reasons' => 'datatables',
]);
*/
Route::get('ajaxreturnreason/data', ['as'=>'ajaxreturnreason.data','uses'=>'Localisation@anyAjaxReturnreason']);

/*Route::controller('admin/payment/settings', 'Payment', [
    'anyAjaxpaymentsettings' => 'ajaxpayment.data',
    'payment_settings' => 'datatables',
]);
*/
Route::get('ajaxpayment/data', ['as'=>'ajaxpayment.data','uses'=>'Payment@anyAjaxpaymentsettings']);

/*Route::controller('admin/banners', 'Banner', [
    'anyAjaxbannerlist' => 'ajaxbanner.data',
    'index' => 'datatables',
]);*/

Route::get('ajaxbanner/data', ['as'=>'ajaxbanner.data','uses'=>'Banner@anyAjaxbannerlist']);
/*
Route::controller('admin/templates/email', 'Template', [
    'anyAjaxtemplatelist' => 'ajaxtemplate.data',
    'index' => 'datatables',
]);
*/
Route::get('ajaxtemplate/data', ['as'=>'ajaxtemplate.data','uses'=>'Template@anyAjaxtemplatelist']);

/*Route::controller('admin/template/subjects', 'Template', [
    'anyAjaxsubjectlist' => 'ajaxsubjects.data',
    'subject_index' => 'datatables',
]);*/

Route::get('ajaxsubjects/data', ['as'=>'ajaxsubjects.data','uses'=>'Template@anyAjaxsubjectlist']);

/*Route::controller('admin/users/groups', 'User', [
    'anyAjaxgroupslist' => 'ajaxgroup.data',
    'group_index' => 'datatables',
]);*/
Route::get('ajaxgroup/data', ['as'=>'ajaxgroup.data','uses'=>'User@anyAjaxgroupslist']);

/*Route::controller('admin/users/addresstype', 'User', [
    'anyAjaxaddresstype' => 'ajaxaddresstype.data',
    'address_index' => 'datatables',
]);*/
Route::get('ajaxaddresstype/data', ['as'=>'ajaxaddresstype.data','uses'=>'User@anyAjaxaddresstype']);

/*Route::controller('vendors/vendors_list', 'Vendor', [
    'anyAjaxVendor' => 'ajaxvendor.data',
    'vendors_list' => 'datatables',
]);*/

Route::get('ajaxvendor/data', ['as'=>'ajaxvendor.data','uses'=>'Vendor@anyAjaxVendor']);

/*Route::controller('vendors/branch_list', 'Vendor', [
    'anyAjaxBranch' => 'ajaxbranch.data',
    'branch_list' => 'datatables',
]);
*/
Route::get('ajaxbranch/data', ['as'=>'ajaxbranch.data','uses'=>'Vendor@anyAjaxBranch']);

/*
Route::controller('vendor/outletmanagers', 'Store', [
    'anyAjaxVendorBranchmanager' => 'ajaxvendorbranchmanager.data',
    'outlet_managers' => 'datatables',
]);*/
Route::get('ajaxvendorbranchmanager/data', ['as'=>'ajaxvendorbranchmanager.data','uses'=>'Store@anyAjaxVendorBranchmanager']);

/*Route::controller('vendors/outlet_managers', 'Vendor', [
    'anyAjaxBranchmanager' => 'ajaxbranchmanager.data',
    'outlet_managers' => 'datatables',
]);*/

Route::get('ajaxbranchmanager/data', ['as'=>'ajaxbranchmanager.data','uses'=>'Vendor@anyAjaxBranchmanager']);

/*Route::controller('vendors/items_list', 'Product', [
    'anyAjaxItems' => 'ajaxitems.data',
    'items_list' => 'datatables',
]);*/

Route::get('ajaxitems/data', ['as'=>'ajaxitems.data','uses'=>'Product@anyAjaxItems']);
/*
Route::controller('vendor/products', 'Store', [
    'anyAjaxProductItems' => 'ajaxproductitems.data',
    'index' => 'datatables',
]);*/

Route::get('ajaxproductitems/data', ['as'=>'ajaxproductitems.data','uses'=>'Store@anyAjaxProductItems']);

/*Route::controller('admin/template/subjects', 'Template', [
    'anyAjaxsubjectlist' => 'ajaxsubjects.data',
    'subject_index' => 'datatables',
]);*/
Route::get('ajaxsubjects/data', ['as'=>'ajaxsubjects.data','uses'=>'Template@anyAjaxsubjectlist']);

/*Route::controller('admin/users/groups', 'User', [
    'anyAjaxgroupslist' => 'ajaxgroup.data',
    'group_index' => 'datatables',
]);*/
Route::get('ajaxgroup/data', ['as'=>'ajaxgroup.data','uses'=>'User@anyAjaxgroupslist']);

/*Route::controller('admin/users/index', 'User', [
    'anyAjaxuserlist' => 'ajaxusers.data',
    'user_index' => 'datatables',
]);*/
Route::get('ajaxusers/data', ['as'=>'ajaxusers.data','uses'=>'User@anyAjaxuserlist']);
/*
Route::controller('admin/blog', 'Blog', [
    'anyAjaxbloglist' => 'listblogajax.data',
    'index' => 'datatables',
]);
*/
Route::get('listblogajax/data', ['as'=>'listblogajax.data','uses'=>'Blog@anyAjaxbloglist']);


/*Route::controller('system/permission', 'Roles', [
    'anyAjaxrolelist' => 'listroleajax.data',
    'index' => 'datatables',
]);*/

Route::get('listroleajax/data', ['as'=>'listroleajax.data','uses'=>'Roles@anyAjaxrolelist']);

/*Route::controller('permission/users', 'Roles', [
    'anyAjaxroleuesrlist' => 'listroleuserajax.data',
    'users' => 'datatables',
]);*/
Route::get('listroleuserajax/data', ['as'=>'listroleuserajax.data','uses'=>'Roles@anyAjaxroleuesrlist']);

/*
Route::controller('vendor/outlets', 'Store', [
    'anyAjaxBranches' => 'anyajaxbranch.data',
    'branches' => 'datatables',
]);
*/
Route::get('anyajaxbranch/data', ['as'=>'anyajaxbranch.data','uses'=>'Store@anyAjaxBranches']);
/*
Route::controller('admin/reviews', 'Reviews', [
    'anyAjaxreviewlist' => 'ajaxReviewslist.data',
    'index' => 'datatables',
]);
*/
Route::get('ajaxReviewslist/data', ['as'=>'ajaxReviewslist.data','uses'=>'Reviews@anyAjaxreviewlist']);

/*Route::controller('vendors/reviews', 'Store', [
    'anyAjaxreviewlistvendor' => 'ajaxReviewslistvendor.data',
    'reviews' => 'datatables',
]);
*/
Route::get('ajaxReviewslistvendor/data', ['as'=>'ajaxReviewslistvendor.data','uses'=>'Store@anyAjaxreviewlistvendor']);

/*Route::controller('managers/reviews', 'Manager', [
    'anyAjaxreviewlistmanager' => 'ajaxReviewslistmanager.data',
    'reviews' => 'datatables',
]);*/

Route::get('ajaxReviewslistmanager/data', ['as'=>'ajaxReviewslistmanager.data','uses'=>'Manager@anyAjaxreviewlistmanager']);

/*Route::controller('admin/orders/index', 'Orders', [
    'anyAjaxorderlist' => 'ajaxorders.data',
    'order_index' => 'datatables',
]);*/
Route::get('ajaxorders/data', ['as'=>'ajaxorders.data','uses'=>'Orders@anyAjaxorderlist']);

/*Route::controller('vendors/orders/index`', 'Store', [
    'anyAjaxOrderlist' => 'ajax_orders.data',
    'order_index' => 'datatables',
]);*/
Route::get('ajax_orders/data', ['as'=>'ajax_orders.data','uses'=>'Store@anyAjaxOrderlist']);

/*Route::controller('orders/return_orders', 'Returnorders', [
    'anyAjaxReturnOrder' => 'ajax_return_orders.data',
    'return_orders' => 'datatables',
]);*/
Route::get('ajax_return_orders/data', ['as'=>'ajax_return_orders.data','uses'=>'Returnorders@anyAjaxReturnOrder']);

/*Route::controller('vendors/return_orders', 'Store', [
    'anyAjaxReturnOrders' => 'ajax_returnorders.data',
    'return_orders' => 'datatables',
]);*/

Route::get('ajax_returnorders/data', ['as'=>'ajax_returnorders.data','uses'=>'Store@anyAjaxReturnOrders']);

/*Route::controller('vendors/request_amount/index', 'Store', [
    'anyAjaxRequestPayment' => 'ajaxrequestpayment.data',
    'request_payment' => 'datatables',
]);*/
Route::get('ajaxrequestpayment/data', ['as'=>'ajaxrequestpayment.data','uses'=>'Store@anyAjaxRequestPayment']);
/*
Route::controller('orders/fund_requests', 'Orders', [
    'anyAjaxRequestPayments' => 'ajaxrequest_payments.data',
    'request_payment' => 'datatables',
]);*/
Route::get('ajaxrequest_payments/data', ['as'=>'ajaxrequest_payments.data','uses'=>'Orders@anyAjaxRequestPayments']);

/*Route::controller('admin/brands', 'Brand', [
    'anyAjaxbrandlist' => 'ajaxbrand.data',
    'index' => 'datatables',
]);
*/
Route::get('ajaxbrand/data', ['as'=>'ajaxbrand.data','uses'=>'Brand@anyAjaxbrandlist']);
/*
Route::controller('admin/product-reviews', 'Productreviews', [
    'anyAjaxproductreviewlist' => 'ajaxPoductReviewslist.data',
    'index' => 'datatables',
]);*/
Route::get('ajaxPoductReviewslist/data', ['as'=>'ajaxPoductReviewslist.data','uses'=>'Productreviews@anyAjaxproductreviewlist']);

/*Route::controller('managers/products', 'Manager', [
    'anyAjaxProductItems' => 'ajaxproductitemsm.data',
    'index' => 'datatables',
]);*/
Route::get('ajaxproductitemsm/data', ['as'=>'ajaxproductitemsm.data','uses'=>'Manager@anyAjaxProductItems']);

/*
Route::controller('admin/users/index', 'User', [
    'anyAjaxuserlist' => 'ajaxuserscomplete.data',
    'user_index' => 'datatables',
]);*/
Route::get('sitemap.xml', function () {

    // create new sitemap object
    $sitemap = App::make("sitemap");

    // set cache (key (string), duration in minutes (Carbon|Datetime|int), turn on/off (boolean))
    // by default cache is disabled
    $sitemap->setCache('laravel.sitemap', 3600);

    // add item to the sitemap (url, date, priority, freq)
    $sitemap->add(URL::to(), '2012-08-25T20:10:00+02:00', '1.0', 'daily');
    $sitemap->add(URL::to('page'), '2012-08-26T12:30:00+02:00', '0.9', 'daily');

    // get all posts from db
    $posts = DB::table('posts')->orderBy('created_at', 'desc')->get();

    // add every post to the sitemap
    foreach ($posts as $post) {
        $sitemap->add($post->slug, $post->modified, $post->priority, $post->freq);
    }

    // show your sitemap (options: 'xml' (default), 'html', 'txt', 'ror-rss', 'ror-rdf')
    return $sitemap->render('xml');
});


Route::group(['middleware' => 'log.request'], function () {
    $api = app('Dingo\Api\Routing\Router');
    $api->version('v1', function ($api) {
        $api->post('login_user', 'App\Http\Controllers\Api\Account@login_user');
        $api->get('languages', 'App\Http\Controllers\Api\Common@languages');
        $api->get('getlocation/{id}', 'App\Http\Controllers\Api\Zone@getlocation');
        $api->get('getcity/{id}', 'App\Http\Controllers\Api\Zone@getcity');
        $api->post('locationlist', 'App\Http\Controllers\Api\Zone@getapiLocationData');
        $api->post('categorylist', 'App\Http\Controllers\Api\Zone@getCategoryLists');
        $api->get('getoffers_list/{id}', 'App\Http\Controllers\Api\Offers@getoffer');
        $api->get('getcountry_select/{id}', 'App\Http\Controllers\Api\Zone@getcountry_select');
        $api->post('getcountrybasedcity', 'App\Http\Controllers\Api\Zone@getcountrybasedcity');
        $api->get('getfeaturesstore/{id}', 'App\Http\Controllers\Api\Store@getfeaturesstore');
        $api->post('signup_user', 'App\Http\Controllers\Api\Account@signup_user');
        $api->post('user_membership', 'App\Http\Controllers\Api\Account@user_membership');
        $api->post('user_rating', 'App\Http\Controllers\Api\Account@user_rating');
        $api->post('signup_confirmation', 'App\Http\Controllers\Api\Account@signup_confirmation');
        $api->post('forgot_password', 'App\Http\Controllers\Api\Account@forgot_password');
        $api->post('signup_fb_user', 'App\Http\Controllers\Api\Account@signup_fb_user');
        $api->post('user_detail', 'App\Http\Controllers\Api\Account@user_detail');
        //$api->post('user_details', 'App\Http\Controllers\Api\Account@user_details');
        $api->post('meditProfile', 'App\Http\Controllers\Api\Account@meditProfile');
        $api->post('update_password', 'App\Http\Controllers\Api\Account@update_password');
        $api->post('store_card', 'App\Http\Controllers\Api\Account@store_card');
        $api->post('get_cards', 'App\Http\Controllers\Api\Account@get_cards');
        $api->post('card_detail', 'App\Http\Controllers\Api\Account@card_detail');
        $api->post('update_card', 'App\Http\Controllers\Api\Account@update_card');
        $api->post('delete_card', 'App\Http\Controllers\Api\Account@delete_card');
        $api->post('delete_address', 'App\Http\Controllers\Api\Account@delete_address');
        $api->post('get_city', 'App\Http\Controllers\Api\Account@get_city');
        $api->post('get_city_list', 'App\Http\Controllers\Api\Account@get_city_list');
        $api->post('store_address', 'App\Http\Controllers\Api\Account@store_address');
        $api->post('get_address', 'App\Http\Controllers\Api\Account@get_address');
        $api->post('coupon_list', 'App\Http\Controllers\Api\Account@coupon_list');
        $api->post('address_detail', 'App\Http\Controllers\Api\Account@address_detail');
        $api->post('store_list', 'App\Http\Controllers\Api\Store@Store_list');
        $api->post('store_info', 'App\Http\Controllers\Api\Store@store_info');
        $api->post('addto_favourite', 'App\Http\Controllers\Api\Store@addto_favourite');
        $api->post('store_list_ajax', 'App\Http\Controllers\Api\Store@Store_list_ajax');
        $api->post('update_address', 'App\Http\Controllers\Api\Account@update_address');
        $api->post('favourites', 'App\Http\Controllers\Api\Account@favourites');
        $api->post('update_profile_image', 'App\Http\Controllers\Api\Account@update_profile_image');
        $api->post('get_cart', 'App\Http\Controllers\Api\CartController@get_cart');
        $api->post('update_cart', 'App\Http\Controllers\Api\CartController@update_cart');
        $api->post('delete_cart', 'App\Http\Controllers\Api\CartController@delete_cart');
        $api->post('add_to_cart', 'App\Http\Controllers\Api\CartController@add_cart');
        $api->post('checkout_detail', 'App\Http\Controllers\Api\Ordercheckout@index');
        $api->post('get_payment_details', 'App\Http\Controllers\Api\Ordercheckout@get_payment_details');
        $api->post('proceed_checkout', 'App\Http\Controllers\Api\Ordercheckout@proceed_checkout');
        $api->post('offline_payment', 'App\Http\Controllers\Api\Ordercheckout@offline_payment');
        $api->post('online_payment', 'App\Http\Controllers\Api\Ordercheckout@online_payment');
        $api->post('order_detail', 'App\Http\Controllers\Api\Ordercheckout@order_detail');
        $api->post('orders', 'App\Http\Controllers\Api\Account@orders');
        $api->post('get_coperatives', 'App\Http\Controllers\Api\Account@get_coperatives');
        $api->post('orders/order-info', 'App\Http\Controllers\Api\Account@order_info');
        $api->post('address_type', 'App\Http\Controllers\Api\Account@address_type');
        $api->post('update_promocode', 'App\Http\Controllers\Api\Ordercheckout@update_promocode');
        $api->post('send_otp', 'App\Http\Controllers\Api\Ordercheckout@send_otp');
        $api->post('check_otp', 'App\Http\Controllers\Api\Ordercheckout@check_otp');
        $api->post('re_order', 'App\Http\Controllers\Api\Ordercheckout@re_order');
        $api->post('cancel_order', 'App\Http\Controllers\Api\Ordercheckout@cancel_order');
        $api->post('store_register_user', 'App\Http\Controllers\Api\Account@store_register_user');
        $api->post('return_order', 'App\Http\Controllers\Api\Ordercheckout@return_order');
        $api->post('product_list', 'App\Http\Controllers\Api\Store@product_list');
        $api->get('store_banner', 'App\Http\Controllers\Api\Store@store_banner');
        $api->post('store_featurelist_mob', 'App\Http\Controllers\Api\Store@store_featurelist_mob');
        $api->post('store_list_mob', 'App\Http\Controllers\Api\Store@store_list_mob');
        $api->post('store_info_mob', 'App\Http\Controllers\Api\Store@store_info_mob');
        $api->post('store_product', 'App\Http\Controllers\Api\Store@store_product');
        $api->post('store_product_mob', 'App\Http\Controllers\Api\Store@store_product_mob');
        $api->post('store_review', 'App\Http\Controllers\Api\Store@store_review');
        $api->post('store_outlet_list', 'App\Http\Controllers\Api\Store@store_outlet_list');
        $api->post('paypal_payment', 'App\Http\Controllers\Api\Ordercheckout@paypal_payment');
        $api->post('user_subscribe', 'App\Http\Controllers\Api\Account@user_subscribe');
        $api->post('user_unsubscribe', 'App\Http\Controllers\Api\Account@user_unsubscribe');
        $api->post('cart_count', 'App\Http\Controllers\Api\Store@cart_count');
        $api->get('mob-cms/{id}', 'App\Http\Controllers\Api\Api@cms_mob');
        $api->post('payment_gateway_list', 'App\Http\Controllers\Api\Common@payment_gateway_list');
        $api->post('location_outlet', 'App\Http\Controllers\Api\Account@location_outlet');
        $api->post('notification-list', 'App\Http\Controllers\Api\Account@notification_list');
        $api->post('delete-notification', 'App\Http\Controllers\Api\Account@delete_notification');
        $api->post('currency-converter', 'App\Http\Controllers\Api\Common@currency_converter');
        $api->post('product_details', 'App\Http\Controllers\Api\Store@product_details');
        $api->post('product_rating', 'App\Http\Controllers\Api\Account@product_rating');
        $api->post('store_contact', 'App\Http\Controllers\Api\Account@store_contact');
        $api->post('driver-login', 'App\Http\Controllers\Api\Drivers_api@login');
        $api->post('driver-forgot-password', 'App\Http\Controllers\Api\Drivers_api@forgot_password');
        $api->post('driver-update-profile', 'App\Http\Controllers\Api\Drivers_api@update_profile');
        $api->post('driver-change-password', 'App\Http\Controllers\Api\Drivers_api@change_password');
        $api->post('driver-orders', 'App\Http\Controllers\Api\Drivers_api@driver_orders');
        $api->post('driver-detail', 'App\Http\Controllers\Api\Drivers_api@driver_detail');
        $api->post('driver-order-detail', 'App\Http\Controllers\Api\Drivers_api@driver_order_detail');
        $api->post('driver-update-location', 'App\Http\Controllers\Api\Drivers_api@driver_update_location');
        $api->post('change-order-status', 'App\Http\Controllers\Api\Drivers_api@change_order_status');
        $api->post('order-report', 'App\Http\Controllers\Api\Drivers_api@report_chart');
        $api->get('cms-faq-list/{language}', 'App\Http\Controllers\Api\Common@mob_faq');
        $api->post('check-social-login-id', 'App\Http\Controllers\Api\Account@check_social_login_id');
        $api->post('check-social-user-credientials', 'App\Http\Controllers\Api\Account@check_social_user_credientials');
        $api->post('driver-signup', 'App\Http\Controllers\Api\Drivers_api@driver_signup');
        $api->post('driver_confirmation', 'App\Http\Controllers\Api\Account@driver_confirmation');
        $api->get('banners', 'App\Http\Controllers\Api\Common@banners');
        $api->post('order-assign-driver', 'App\Http\Controllers\Api\Drivers_api@assign_driver_orders');
        $api->post('driver-notification-list', 'App\Http\Controllers\Api\Drivers_api@order_notification_list');
        $api->post('update-order-attachments', 'App\Http\Controllers\Api\Drivers_api@update_order_attachments');
        $api->post('driver-delete-notification', 'App\Http\Controllers\Api\Drivers_api@delete_notification');
        $api->post('order-driver-location-details', 'App\Http\Controllers\Api\Ordercheckout@order_driver_location');
        $api->post('update-reject-status', 'App\Http\Controllers\Api\Drivers_api@update_reject_status');
        $api->post('driver-logout', 'App\Http\Controllers\Api\Drivers_api@driver_logout');
        $api->post('update_driver_order_status', 'App\Http\Controllers\Api\Drivers_api@update_driver_order_status');
        //Signup otp
        $api->post('check-otp-registration', 'App\Http\Controllers\Api\Account@check_otp_registration');
        $api->post('reg-send-otp', 'App\Http\Controllers\Api\Account@reg_send_otp');
        $api->post('verifyPhone', 'App\Http\Controllers\Api\Account@verifyPhone');
        $api->post('signupOtpVerify', 'App\Http\Controllers\Api\Account@signupOtpVerify');
        $api->post('signupSendOtp', 'App\Http\Controllers\Api\Account@signupSendOtp');
        //productSearch
        $api->post('productSearch', 'App\Http\Controllers\Api\Store@products');
        $api->post('signup_new', 'App\Http\Controllers\Api\Account@signup_new');
        $api->post('productInsert', 'App\Http\Controllers\Api\Store@productApi');
        //Mob Api's:
        $api->post('mproductSearch', 'App\Http\Controllers\Api\Store@mproducts');
        $api->post('mcategorybroz', 'App\Http\Controllers\Api\Zone@mgetCategoryLevelLists');
        $api->post('mstore_product_mob', 'App\Http\Controllers\Api\Store@mstore_product_mob');
        $api->post('mproduct_details', 'App\Http\Controllers\Api\Store@mproduct_details');
        $api->post('mverifyPhone', 'App\Http\Controllers\Api\Account@mverifyPhone');
        $api->post('msignupOtpVerify', 'App\Http\Controllers\Api\Account@msignupOtpVerify');
        $api->post('mresendOtp', 'App\Http\Controllers\Api\Account@mresendOtp');
        $api->post('mverifyPassword', 'App\Http\Controllers\Api\Account@mverifyPassword');
        $api->post('mfacebookSignup', 'App\Http\Controllers\Api\Account@mfacebookSignup');
        $api->post('mforgotPassword', 'App\Http\Controllers\Api\Account@mforgotPassword');
        $api->post('msignupNew', 'App\Http\Controllers\Api\Account@msignup_new');
        $api->post('mbulkCartInsert', 'App\Http\Controllers\Api\CartController@mbulkCartInsert');
        $api->post('mstore_list_mob', 'App\Http\Controllers\Api\Store@mstore_list_mob');
        $api->post('maddCart', 'App\Http\Controllers\Api\CartController@maddCart');
        $api->post('mdriver-order-detail', 'App\Http\Controllers\Api\Drivers_api@mdriver_order_detail');
        $api->post('morders', 'App\Http\Controllers\Api\Account@morders');
        $api->post('mdriver-order-detail', 'App\Http\Controllers\Api\Drivers_api@mdriver_order_detail');
        $api->post('morder_detail', 'App\Http\Controllers\Api\Ordercheckout@morder_detail');
        $api->post('mdashboard_mob', 'App\Http\Controllers\Api\Store@mdashboard_mob');
        $api->post('getNearestVendors', 'App\Http\Controllers\Api\Store@getNearestVendors');
        //new bulkcart insert
        $api->post('mbulkCartInsert1', 'App\Http\Controllers\Api\CartController@mbulkCartInsert1');
        $api->post('mbulkCartInsert1_copy', 'App\Http\Controllers\Api\CartController@mbulkCartInsert1_copy');
        $api->post('mpayment_response', 'App\Http\Controllers\Api\CartController@payment_response');
        $api->post('moffline_payment', 'App\Http\Controllers\Api\Ordercheckout@moffline_payment');
        $api->post('mstore_address', 'App\Http\Controllers\Api\Account@mstore_address');
        //dynamic view page api:ram 29/04/2019
        $api->post('coreData', 'App\Http\Controllers\Api\Account@getcore');
        $api->post('/feedback', 'App\Http\Controllers\Api\Account@feedback');
        $api->post('/getGroceryDetail', 'App\Http\Controllers\Api\Account@getGroceryDetail');
        $api->post('/data', 'App\Http\Controllers\Api\Store@dynamic');
        $api->post('/dynamicshow', 'App\Http\Controllers\Api\Store@dynamicshow');
        $api->post('getoffers_list', 'App\Http\Controllers\Api\Offers@mgetoffer');
        $api->post('mget_address', 'App\Http\Controllers\Api\Account@mget_address');
        $api->post('cms', 'App\Http\Controllers\Api\Account@cms_infos');
        //faq:ram 16/05/2019
        $api->post('forms', 'App\Http\Controllers\Api\Account@form');
        $api->post('formUpdate', 'App\Http\Controllers\Api\Account@formUpdate');
        $api->post('faq', 'App\Http\Controllers\Api\Account@faq_details');
        //Ram Driver Api:
        // $api->post('getCustomerProfileInfo', 'App\Http\Controllers\Api\Drivers_api@pDriverInfo');
        // $api->post('signIn', 'App\Http\Controllers\Api\Drivers_api@plogin');
        // $api->post('forgot_password', 'App\Http\Controllers\Api\Drivers_api@pforgotPassword');
        // $api->post('pUpdateProfile', 'App\Http\Controllers\Api\Drivers_api@pUpdateProfile');
        // $api->post('user_logout', 'App\Http\Controllers\Api\Drivers_api@pDriverLogout');
        // $api->post('pSendOtp', 'App\Http\Controllers\Api\Drivers_api@pSendOtp');
        // $api->post('pUploadProfileImage', 'App\Http\Controllers\Api\Drivers_api@pUploadProfileImage');
        // $api->post('getcoreconfig', 'App\Http\Controllers\Api\Drivers_api@getcoreconfig');
        // $api->post('update_new_password', 'App\Http\Controllers\Api\Drivers_api@update_new_password');
        // $api->post('otp_process', 'App\Http\Controllers\Api\Drivers_api@otp_process');
        // $api->post('driverOrderList', 'App\Http\Controllers\Api\Drivers_api@driverOrderList');
        $api->post('getDriverCurrentLocation', 'App\Http\Controllers\Api\Drivers_api@getDriverCurrentLocation');
        //change the Driver API
        $api->post('getCustomerProfileInfo', 'App\Http\Controllers\Api\MobileDriver_api@DriverInfo');
        $api->post('signIn', 'App\Http\Controllers\Api\MobileDriver_api@login');
        $api->post('forgot_password', 'App\Http\Controllers\Api\MobileDriver_api@forgotPassword');
        $api->post('pUpdateProfile', 'App\Http\Controllers\Api\MobileDriver_api@UpdateProfile');
        $api->post('user_logout', 'App\Http\Controllers\Api\MobileDriver_api@DriverLogout');
        $api->post('pSendOtp', 'App\Http\Controllers\Api\MobileDriver_api@SendOtp');
        $api->post('pUploadProfileImage', 'App\Http\Controllers\Api\MobileDriver_api@UploadProfileImage');
        $api->post('getcoreconfig', 'App\Http\Controllers\Api\MobileDriver_api@getcoreconfig');
        $api->post('update_new_password', 'App\Http\Controllers\Api\MobileDriver_api@update_new_password');
        $api->post('otp_process', 'App\Http\Controllers\Api\MobileDriver_api@otp_process');
        $api->post('driverOrderList', 'App\Http\Controllers\Api\MobileDriver_api@driverOrderList');
        $api->post('page', 'App\Http\Controllers\Api\Account@page');
        $api->post('mchange_order_status', 'App\Http\Controllers\Api\MobileDriver_api@mchange_order_status');
        $api->post('mdriver_order_info', 'App\Http\Controllers\Api\MobileDriver_api@mdriver_order_info');
        $api->post('mdriver_orders', 'App\Http\Controllers\Api\MobileDriver_api@mdriver_orders');
        $api->post('mdriver_update_location', 'App\Http\Controllers\Api\MobileDriver_api@mdriver_update_location');
        $api->post('morder_accept', 'App\Http\Controllers\Api\MobileDriver_api@morder_accept');
        $api->post('morder_reject', 'App\Http\Controllers\Api\MobileDriver_api@morder_reject');
        $api->post('morder_dispatched', 'App\Http\Controllers\Api\MobileDriver_api@morder_dispatched');
        $api->post('morder_delivered', 'App\Http\Controllers\Api\MobileDriver_api@morder_delivered');
        $api->post('mdriver_shift_status', 'App\Http\Controllers\Api\MobileDriver_api@mdriver_shift_status');
        $api->post('mdriver_order_detail', 'App\Http\Controllers\Api\MobileDriver_api@mdriver_order_detail');
        $api->post('mdriver_status', 'App\Http\Controllers\Api\MobileDriver_api@mdriver_status');
        $api->post('mdriver_status_new', 'App\Http\Controllers\Api\MobileDriver_api@mdriver_status_new');
        $api->post('promocodesamp', 'App\Http\Controllers\Api\MobileDriver_api@promocodesamp');
        $api->post('demo', 'App\Http\Controllers\Api\Store@demo');
        $api->post('/getval', 'App\Http\Controllers\Api\Store@getval');
        $api->post('mdriver_shift_status_new', 'App\Http\Controllers\Api\MobileDriver_api@mdriver_shift_status_new');
        $api->post('morder_arrived', 'App\Http\Controllers\Api\MobileDriver_api@morder_arrived');
        //$api->post('driver-forgot-password', 'App\Http\Controllers\Api\Drivers_api@forgot_password');
        //$api->post('forgot_password', 'App\Http\Controllers\Api\Account@forgot_password');
        $api->post('mforgotOtp', 'App\Http\Controllers\Api\Account@mforgotOtp');
        $api->post('mforgotPassword', 'App\Http\Controllers\Api\Account@forgotPassword');
        $api->post('mchangePassword', 'App\Http\Controllers\Api\Account@mchangePassword');
        $api->post('driverLogin', 'App\Http\Controllers\Api\MobileDriver_api@driverLogin');
        $api->post('driver_rating', 'App\Http\Controllers\Api\Account@driver_rating');
        $api->post('mupdateNewPassword', 'App\Http\Controllers\Api\Account@update_new_password');
        $api->post('puhscheck_fun', 'App\Http\Controllers\Api\MobileDriver_api@puhscheck_fun');
        $api->post('get_profile', 'App\Http\Controllers\Api\Account@get_profile');
        $api->post('rating', 'App\Http\Controllers\Api\Account@rating');
        $api->post('order_shortlist', 'App\Http\Controllers\Api\Account@order_shortlist');
        //$api->post('order_shortlist_copy', 'App\Http\Controllers\Api\Account@order_shortlist_copy');
        $api->post('mdashboard_mob_copy', 'App\Http\Controllers\Api\Store@mdashboard_mob_copy');
        $api->post('uproduct_details', 'App\Http\Controllers\Api\Store@uproduct_details');
        $api->post('mdashboard_mob_test', 'App\Http\Controllers\Api\Store@mdashboard_mob_test');
        $api->post('mdashboard_mob_not', 'App\Http\Controllers\Api\Store@mdashboard_mob_not');
        $api->post('rating_test', 'App\Http\Controllers\Api\Account@rating_test');
        //Outlet App:
        //Vignesh : 22/08/2019
        $api->get('orders', 'App\Http\Controllers\Api\outlet@orders');
        $api->get('ordersDetails', 'App\Http\Controllers\Api\outlet@ordersDetails');
        $api->post('assignSalesFleet', 'App\Http\Controllers\Api\outlet@assignSalesFleet');
        $api->post('assignDriverFleet', 'App\Http\Controllers\Api\outlet@assignDriverFleet');
        //Ram : 22/08/2019
        $api->post('outforgotPassword', 'App\Http\Controllers\Api\outlet@outforgotPassword');
        $api->post('outResendOtp', 'App\Http\Controllers\Api\outlet@outResendOtp');
        $api->post('outVerifyPhone', 'App\Http\Controllers\Api\outlet@outVerifyPhone');
        $api->post('outVerifyOtp', 'App\Http\Controllers\Api\outlet@outVerifyOtp');
        $api->post('outletManagerLogout', 'App\Http\Controllers\Api\outlet@outletManagerLogout');
        $api->post('outletsManager_login', 'App\Http\Controllers\Api\outlet@outletsManager_login');
        //SalesPerson_login
        //Ram :23/08/2019
        $api->post('salesPersonInfo', 'App\Http\Controllers\Api\sales@salesPersonInfo');
        $api->post('login_salesPerson', 'App\Http\Controllers\Api\sales@login_salesPerson');
        $api->post('salesUpdateProfile', 'App\Http\Controllers\Api\sales@salesUpdateProfile');
        $api->post('salesForgotPassword', 'App\Http\Controllers\Api\sales@salesForgotPassword');
        $api->post('salesResendOtp', 'App\Http\Controllers\Api\sales@salesResendOtp');
        $api->post('salesVerifyOtp', 'App\Http\Controllers\Api\sales@salesVerifyOtp');
        $api->post('salesPersonLogout', 'App\Http\Controllers\Api\sales@salesPersonLogout');
        //Ram : 31/08/2019
        $api->post('newProductList', 'App\Http\Controllers\Api\Store@newProductList');
        $api->post('getItemDetails', 'App\Http\Controllers\Api\Store@getItemDetails');
        $api->post('getCategoryDetail', 'App\Http\Controllers\Api\Store@getCategoryDetail');
        $api->post('updateProductDetails', 'App\Http\Controllers\Api\Store@updateProductDetails');
        $api->post('order_initiated', 'App\Http\Controllers\Api\Orders@order_initiated');
        //Vignesh : 02/09/2019
        $api->post('getOrderHistory', 'App\Http\Controllers\Api\Store@getOrderHistory');
        //$api->post('orderHistoryItemDetails', 'App\Http\Controllers\Api\Store@orderHistoryItemDetails');
        //Ram : 04/09/2019
        $api->post('muserLogout', 'App\Http\Controllers\Api\Account@muserLogout');
        $api->post('orderHistoryItemDetails', 'App\Http\Controllers\Api\Store@orderHistoryItemDetails');
        $api->post('outletOrders', 'App\Http\Controllers\Api\outlet@outletOrders');
        $api->post('store_review_copy', 'App\Http\Controllers\Api\Store@store_review_copy');
        //Vignesh : 11/09/2019    
        $api->post('getUserReview', 'App\Http\Controllers\Api\Account@getUserReview');
        $api->post('getOrderHistory_copy', 'App\Http\Controllers\Api\Store@getOrderHistory_copy');
        $api->post('orderStatusUpdate', 'App\Http\Controllers\Api\Store@orderStatusUpdate');
        $api->post('testing', 'App\Http\Controllers\Api\outlet@testing');
        //Ram : 17/09/19
        $api->post('assignSalesPerson', 'App\Http\Controllers\Api\outlet@assignSalesPerson');
        $api->post('assignSalesPerson_copy', 'App\Http\Controllers\Api\outlet@assignSalesPerson_copy');
        $api->post('orderComplete', 'App\Http\Controllers\Api\outlet@orderComplete');
        $api->post('updateOrderStatus', 'App\Http\Controllers\Api\outlet@updateOrderStatus');
        //Ram :18/09/19
        $api->post('mproductSearchNew', 'App\Http\Controllers\Api\Store@mproductSearchNew');
        //Ram :19/09/19
        $api->post('morderDetail', 'App\Http\Controllers\Api\Ordercheckout@morderDetail');
        //Vignesh: 19/09/2019
        $api->post('orderItemDetails', 'App\Http\Controllers\Api\outlet@orderItemDetails');
        //Ram :20/09/19
        $api->post('assignDriver', 'App\Http\Controllers\Api\MobileDriver_api@assignDriver');
        $api->post('availableDrivers', 'App\Http\Controllers\Api\MobileDriver_api@availableDrivers');
        $api->post('substitution', 'App\Http\Controllers\Api\MobileDriver_api@substitution');
        $api->post('adjustments', 'App\Http\Controllers\Api\MobileDriver_api@adjustments');
        $api->post('mbarcode', 'App\Http\Controllers\Api\Store@mbarcode');
        $api->post('barcodes', 'App\Http\Controllers\Api\Account@barcodes');
        $api->post('delivery_copy', 'App\Http\Controllers\Api\outlet@delivery_copy');
        $api->post('outletgetcoreconfig', 'App\Http\Controllers\Api\outlet@outletgetcoreconfig');
        $api->post('getWeightclasses', 'App\Http\Controllers\Api\outlet@getWeightclasses');
        $api->post('orderPackestatus', 'App\Http\Controllers\Api\outlet@orderPackestatus');
        //Ram:03/10/2019 
        $api->post('mcashIn', 'App\Http\Controllers\Api\Ordercheckout@mcashIn');
        $api->post('referralDetails', 'App\Http\Controllers\Api\MobileDriver_api@referralDetails');
        $api->post('morder_delivered_copy', 'App\Http\Controllers\Api\MobileDriver_api@morder_delivered_copy');
        $api->post('outeletRevenue', 'App\Http\Controllers\Api\outlet@outeletRevenue');
        $api->post('revenuExport', 'App\Http\Controllers\Api\outlet@revenuExport');
        $api->post('salespersonOrders', 'App\Http\Controllers\Api\sales@salespersonOrders');
        $api->post('returnreason', 'App\Http\Controllers\Api\Ordercheckout@returnreason');
        $api->post('pushchk', 'App\Http\Controllers\Api\MobileDriver_api@pushchk');
        $api->post('salespersonAssigned', 'App\Http\Controllers\Api\sales@salespersonAssigned');
        $api->post('salespersonOrders_copy', 'App\Http\Controllers\Api\sales@salespersonOrders_copy');
        $api->post('assignDriver_copy', 'App\Http\Controllers\Api\sales@assignDriver_copy');
        $api->post('barcodeUpdate', 'App\Http\Controllers\Api\Store@barcodeUpdate');
        $api->post('offlinepayment', 'App\Http\Controllers\Api\Ordercheckout@offlinepayment');
        $api->post('salespersonStatusChange', 'App\Http\Controllers\Api\sales@salespersonStatusChange');
        $api->post('salespersonStatus', 'App\Http\Controllers\Api\sales@salespersonStatus');
        $api->post('driverLogin', 'App\Http\Controllers\Api\MobileDriver_api@driverLogin');
        $api->post('pdf_check', 'App\Http\Controllers\Api\MobileDriver_api@pdf_check');
        $api->post('FunctionName', 'App\Http\Controllers\Api\MobileDriver_api@FunctionName');
        $api->post('change_password', 'App\Http\Controllers\Api\MobileDriver_api@change_password');
        $api->post('outletDetails', 'App\Http\Controllers\Api\Account@outletDetails');
        $api->post('outletDetails_new', 'App\Http\Controllers\Api\Account@outletDetails_new');
        $api->post('promotionOffers', 'App\Http\Controllers\Api\Account@promotionOffers');
        $api->post('PaymentHistory', 'App\Http\Controllers\Api\Account@PaymentHistory');
        $api->post('walletPaymentResult', 'App\Http\Controllers\Api\Account@walletPaymentResult');
        $api->get('walletAddTelr', 'App\Http\Controllers\Api\Account@walletAddTelr');
       

        $api->post('userWalletDebit', 'App\Http\Controllers\Api\outlet@userWalletDebit');
        $api->post('getWalletDebitOTP', 'App\Http\Controllers\Api\outlet@getWalletDebitOTP');
        $api->post('searchUser', 'App\Http\Controllers\Api\outlet@searchUser');
        $api->post('getPaymentHistory', 'App\Http\Controllers\Api\outlet@getPaymentHistory');


        $api->post('image_added', 'App\Http\Controllers\Api\Store@image_added');
        $api->post('image_delete', 'App\Http\Controllers\Api\Store@image_delete');
        $api->post('product_edit', 'App\Http\Controllers\Api\Store@product_edit');
        $api->post('adminProductsDetails', 'App\Http\Controllers\Api\Store@adminProductsDetails');

    });
});
