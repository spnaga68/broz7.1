@extends('layouts.app')
@section('content')
<section class="poroduct_listing">
   <div class="nav_cat responsive_style">
      <div class="container">
         <div class="row_new">
            <div class="nav_bot_sec">
            <div class="row">
               <div class="col-md-10 colsm-9 col-xs-9">
                  <div class="bread_corm">
                     <div class="common_pro_listing">
                        <ul>
                           <li><?php
                              $outlet_info = $products->outlet_info[0];
                            
                            //echo '<pre>'; print_r($outlet_info); exit;
                              if(count($categories)>0){ 
                              
                              	$active ="";
                              	if($cate_url == 'all')
                              	{
                              		$active ="active";
                              	}
                              ?>
                              <a href="{{ URL::to('/products/all/'.$outlet_info->url_index) }}" class={{$active}} title="@lang('messages.All')">@lang('messages.All')</a>
		
                           </li>
                           <?php	} ?>
                           
                           <?php if(count($categories)>0){
                              //print_r($categories);exit;
                              } ?>
                           <?php $i=1; foreach($categories as $cate){ 
                              //print_r($cate);exit;
                              ?>
                           <?php  if($i <= 5){
                              $active ="";
                              if($cate_url == $cate['url_key'])
                              {
                              	$active ="active";
                              }
                              ?> 
                          
                               <li><a href="{{ URL::to('/products/'.$cate['url_key'].'/'.$outlet_info->url_index.'/'.$cate['category_id']) }}" class="category_clik" attr-id="{{ ucfirst(strtolower($cate['category_name'])) }}" title="{{ ucfirst(strtolower($cate['category_name'])) }}">{{ ucfirst(strtolower($cate['category_name'])) }} </a><span class="megamenu_arrows"></span>
                                                <?php if(count($cate['subcategory']) > 0 ) { ?>
													<div class="level_one_drop col-md-12">
														<div class="col-md-7">
															<div class="col-md-4">
															<ul class="first_list1">
																
															<?php  foreach($cate['subcategory'] as $sub){ ?>	
				<li><a href="{{ URL::to('/products/'.$sub->url_key.'/'.$outlet_info->url_index.'/'.$sub->parent_id.'/'.$sub->id) }}" title="{{ $sub->category_name }}">{{ $sub->category_name }}</a></li>
															<?php } ?>
															</ul>
															</div>
															</div>
															<div class="col-md-5 left_img_info_det">
															<h3> {{ ucfirst(strtolower($cate['category_name'])) }} </h3>

					<?php if(file_exists(base_path().'/public/assets/admin/base/images/category/'.$cate['image'])) { ?>
                        <img alt="{{ ucfirst(strtolower($cate['category_name'])) }}"  src="<?php echo url('/assets/admin/base/images/category/'.$cate['image'].''); ?>" >
                    <?php } else{ ?>
                        <img src="<?php echo URL::asset('assets/admin/base/images/products/product.png');?>" alt="{{ ucfirst(strtolower($cate['category_name'])) }}">
                    <?php } ?>
															</div>
														</div>
												<?php } ?>		
														
												</li>
                           <?php }  ?>
                           <?php $i++; } ?>
                           <?php if(count($categories)>5){ ?> 
                           <li>
                              <a href="javascript:;" title="@lang('messages.More')" id="open_drop_me">@lang('messages.More')</a>
           <div class="level_one_drop col-md-12">

											<div class="col-md-12">
												<?php $i=1;  foreach($categories as $cate){ ?>
												<?php  if($i>5){ ?>
											<div class="col-md-4">
												   
												<div class="head_cate"> <a href="{{ URL::to('/products/'.$cate['url_key'].'/'.$outlet_info->url_index.'/'.$cate['category_id']) }}" class="category_clik" attr-id="{{ ucfirst(strtolower($cate['category_name'])) }}" title="{{ ucfirst(strtolower($cate['category_name'])) }}">{{ ucfirst(strtolower($cate['category_name'])) }}</a></div>
												<?php if(count($cate['subcategory']) > 0 ) { ?>
													<ul class="first_list1">
														<?php  foreach($cate['subcategory'] as $sub1){ ?>	
															<li><a href="{{ URL::to('/products/'.$sub1->url_key.'/'.$outlet_info->url_index.'/'.$sub1->parent_id.'/'.$sub1->id) }}" title="{{ $sub1->category_name }}">{{ $sub1->category_name }}</a></li>
														<?php } ?>
													</ul>
												<?php  }  ?>     
											</div>
											<?php  }  ?>
											<?php $i++; } ?>
										
										</div>
											</div>

                           </li>
                           <?php } ?>
                        </ul>
                     </div>
                  </div>
               </div>
               <div class="col-md-2 colsm-3 col-xs-3">
                  <div class="cart_sections">
                     <a  <?php if(!Session::get('user_id')){ ?> data-toggle="modal" data-target="#myModal2"  <?php } else {  ?> href="{{url('cart')}}" <?php } ?> title="@lang('messages.items')"> <i class="glyph-icon flaticon-business"></i> <span class="cart_total_count"> <?php echo $cart_item ; ?> </span> @lang('messages.items') </a>
                  </div>
               </div>
            </div>
            </div>
         </div>
      </div>
   </div>
   <div class="container">
      <?php if(isset($products->outlet_info[0]))
         { ?> 
      <div class="store_img">
         <div class="slid_fad"></div>
         <?php  
            $outlet_info = $products->outlet_info[0];
           //print_r($category_image);exit;
            if($cate_url == 'all')
            { 
				if(file_exists(base_path().'/public/assets/admin/base/images/vendors/thumb/detail/'.$outlet_info->featured_image)) { ?>
					<img alt="{{ ucfirst($outlet_info->vendor_name) }}"  src="<?php echo url('/assets/admin/base/images/vendors/thumb/detail/'.$outlet_info->featured_image.''); ?>" >
				<?php } else{  ?>
					<img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/store_detial.png');?>" alt="{{ ucfirst($outlet_info->vendor_name) }}">
				<?php } 
			}  else {
				
				if(isset($category_image[0]->image) && file_exists(base_path().'/public/assets/admin/base/images/category/'.$category_image[0]->image) && !empty($category_image[0]->image)) { ?>
					<img   alt="{{ ucfirst($outlet_info->vendor_name) }}"  src="<?php echo url('/assets/admin/base/images/category/'.$category_image[0]->image); ?>" >
				<?php } else{  ?>
					<img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/store_detial.png');?>" alt="{{ ucfirst($outlet_info->vendor_name) }}">
			<?php } }?>


         <div class="store_details_sec">
            <?php  if(file_exists(base_path().'/public/assets/admin/base/images/vendors/logos/'.$outlet_info->logo_image)) { ?>
            <img  width="161px" height="107px" alt="{{ ucfirst($outlet_info->vendor_name) }}"  src="<?php echo url('/assets/admin/base/images/vendors/logos/'.$outlet_info->logo_image.''); ?>" >
            <?php } else{  ?>
            <img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/blog_no_images.png');?>" alt="{{ ucfirst($outlet_info->vendor_name) }}">
            <?php } ?>	
            <h2>{{ $outlet_info->vendor_name }}</h2>
            <p>{{ $outlet_info->outlets_contact_address }}</p>
            <h4>@lang('messages.Delivered in') {{ $outlet_info->outlets_delivery_time }}</h4>
         </div>
         <div class="store_icons">
            <ul>
               <li class="home info_active" ><a style="cursor:pointer;" onclick="back_product()" ><i class="icons"><img src="{{ URL::asset('assets/front/'.Session::get('general')->theme.'/images/home_icon.png') }}" alt=""></i></a></li>
               <li <?php if($fstatus){ ?> class="info_active" <?php } ?> ><a style="cursor:pointer;" class="favourite" <?php if(!Session::get('user_id')){ ?> data-toggle="modal" data-target="#myModal2"  <?php } else {  ?> data-toggle="popover" title="" onclick="favourite(<?php echo $outlet_info->outlets_id;?>)" <?php } ?> ><i class="glyph-icon flaticon-favorite-1"></i></a></li>
               <li class="review"><a style="cursor:pointer;"  onclick="review()"><i class="glyph-icon flaticon-interface"></i></a></li>
               <li class="info"><a style="cursor:pointer;"  onclick="info()"><i class="glyph-icon flaticon-signs"></i></a></li>
            </ul>
         </div>
      </div>
      <?php } ?>
      <div class="bottom_search">
         {!!Form::open(array('url' =>'products/'.$cate_url.'/'.$outlet_key, 'method' => 'get','class'=>'tab-form attribute_form','id'=>'home_search_form','files' => true));!!}
         <div class="product_search_box">
            <div class="inner-addon right-addon">
               <div class="header_search_icon">
                  <button id="btn"   class="btn btn-default" type="submit" title="submit"><i class="glyphicon glyphicon-search "></i></button>
               </div>
               <input class="form-control" value="{{ Input::get('keyword') }}" id="textbx" name="keyword" type="text" placeholder="Search">
            </div>
         </div>
         {!!Form::close();!!}
      </div>
      <div class="favourite-success"> </div>
      <div class="row detail_sections">
         <div class="poroduct_items margin_auto">
            <?php $currency_side = getCurrencyPosition()->currency_side;$currency_symbol = getCurrency(getCurrentLang()); ?>
            <?php 
               if(count($products->data)){  ?>
            <?php foreach($products->data as $pro) { ?>
            <div class="col-md-3 col-xs-6 col-sm-6">
               <div class="item_common_new">
                  <div class="store_list_img">
                     <?php  if(file_exists(base_path().'/public/assets/admin/base/images/products/list/'.$pro->product_image)) { ?>
                     <a href=" {{ URL::to('/product/info/'.$outlet_info->url_index.'/'.$pro->product_url)  }}" title=""> <img  title="{{ $pro->product_name }}" alt="{{ ucfirst(strtolower($pro->product_name)) }}"  src="<?php echo url('/assets/admin/base/images/products/list/'.$pro->product_image.''); ?>" ></a>
                     <?php } else{  ?>
                     <a href="{{ URL::to('/product/info/'.$outlet_info->url_index.'/'.$pro->product_url) }}" title=""><img src="{{ URL::asset('assets/admin/base/images/products/product.png') }}" alt="{{ ucfirst(strtolower($pro->product_name)) }}"></a>
                     <?php } ?>	
                  </div>
                  <div class="store_list_desc">
                     <a href=" {{ URL::to('/product/info/'.$outlet_info->url_index.'/'.$pro->product_url)  }}" style="cursor:pointer;" title="{{ strtolower($pro->product_name) }}">{{ ucfirst(strtolower(trim($pro->product_name))) }}  ({{ $pro->weight }}{{ $pro->unit }})</a>
                     <?php if($currency_side == 1) { ?>
                     <h3>@if(($pro->original_price-$pro->discount_price > 0) && ($pro->original_price > 0 ))<span class="marked_price"><?php echo  $currency_symbol; ?>{{ $pro->original_price }} </span>
                     @endif<span class="nrl_price"><?php echo $currency_symbol; ?>{{ $pro->discount_price }} </span></h3>
                     <?php } else { ?>
                     <h3>@if(($pro->original_price-$pro->discount_price > 0) && ($pro->original_price > 0))<span class="marked_price">{{ $pro->original_price }} <?php echo $currency_symbol; ?></span>
                     @endif<span class="nrl_price">{{ $pro->discount_price }} <?php echo $currency_symbol; ?></span></h3>
                     <?php } ?>
                  </div>
               </div>
            </div>
            <?php } ?>
            <div class="popup ">
               <?php foreach($products->data as $pro){ ?>	
               <div class="modal fade cart_dyn_sec exampleModal<?php echo $pro->product_id;?>"  tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" data-backdrop="static" data-keyboard="false">
                  <div class="modal-dialog position_relative" role="document">
                     <div class="modal-content">
                        <div class="modal-body">
                           <button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
                           <div class="alert_info<?php echo $pro->product_id;?>"> </div>
                           <div class="inner_cart_sections">
                              <div class="col-md-4">
                                 <div class="cart_img">
                                    <?php  if(file_exists(base_path().'/public/assets/admin/base/images/products/list/'.$pro->product_image)) { ?>
                                    <a href="javascript:;" title="{{ ucfirst(strtolower($pro->product_name)) }}"><img  alt="{{ ucfirst($pro->product_name) }}"  src="<?php echo url('/assets/admin/base/images/products/list/'.$pro->product_image.''); ?>" ></a>
                                    <?php } else{  ?>
                                    <a href="javascript:;" title="{{ ucfirst(strtolower($pro->product_name)) }}"><img src="{{ URL::asset('assets/admin/base/images/products/product.png') }}" alt="{{ ucfirst(strtolower($pro->product_name)) }}"></a>
                                    <?php } ?>	
                                 </div>
                              </div>
                              <div class="col-md-8">
                                 <div class="cart_infor">
                                    <a href="javascript:;" title="Kraft cheddar cheese spread original "><?php echo substr(ucfirst(strtolower($pro->product_name)),0,100);?> </a>
                                    <div class="col-md-4 col-sm-4 col-xs-4 padding_left0">
                                       <p class="cat_wight">{{ $pro->weight }} {{ $pro->unit }}</p>
                                    </div>
                                    <div class="col-md-4 col-sm-4 col-xs-4 padding0">
                                       <div class="count_numbers">
                                          <ul>
                                             <li class="minuse_count"><a href="javascript:;" class="sqty_decrease" id="<?php echo $pro->product_id;?>" >-</a></li>
                                             <li class="minuse_number sactual_quantity" id="<?php echo $pro->product_id;?>">
                                                <?php echo $pro->product_cart_count; ?>
                                             </li>
                                             <li class="pluse_number"><a href="javascript:;" class="sqty_increase" id="<?php echo $pro->product_id;?>">+</a></li>
                                          </ul>
                                       </div>
                                    </div>
                                    <form method="POST" name="appointment_form" accept-charset="UTF-8" id="add_cart_form<?php echo $pro->product_id;?>">
                                       <input type="hidden" name="_token" class="_token<?php echo $pro->product_id;?>" value="{{ csrf_token() }}">
                                       <input type="hidden" name="total_amount" class="total_amount<?php echo $pro->product_id;?>" value="{{ $pro->discount_price }}">
                                       <input type="hidden" name="product_id" class="product_id<?php echo $pro->product_id;?>" value="{{ $pro->product_id }}">
                                       <input type="hidden" name="quantity" class="quantity<?php echo $pro->product_id;?> qsactual_quantity" value="1">
                                       <input type="hidden" name="final_total_amount" class="final_total_amount<?php echo $pro->product_id;?>" value="{{ $pro->discount_price }}">
                                       <input type="hidden" name="outlet_id" class="outlet_id<?php echo $pro->product_id;?>" value="{{ $pro->outlet_id }}">
                                       <input type="hidden" name="vendors_id" class="vendors_id<?php echo $pro->product_id;?>" value="{{ $pro->vendor_id }}">
                                       <div class="col-md-4 col-sm-4 col-xs-4 padding_right0">
                                          <?php if($currency_side == 1) { ?>
                                          <p class="cat_price"> <?php echo $currency_symbol; ?><span id="total_amount<?php echo $pro->product_id;?>" >{{ $pro->discount_price }} </span> </p>
                                          <p class="cat_price1" style="display:none;color: #e91e63;font-size: 24px;font-weight: 500;text-align: right;"> <?php echo $currency_symbol; ?>{{ $pro->discount_price }} </p>
                                          <?php } else{  ?>
                                          <p class="cat_price"> <span id="total_amount<?php echo $pro->product_id;?>" > {{ $pro->discount_price }} </span> <?php echo $currency_symbol; ?></p>
                                          <p class="cat_price1" style="display:none;color: #e91e63;font-size: 24px;font-weight: 500;text-align: right;">  {{ $pro->discount_price }} <?php echo $currency_symbol; ?></p>
                                          <?php } ?>
                                       </div>
                                       <div class="col-md-12  padding_right0">
                                          <div class="cart_but_sec">
                                             <div class="form-group" id="ajaxloading<?php echo $pro->product_id;?>" style="display:none;">
                                                <div class="loader-coms">
                                                   <div class="loder_gif">
                                                      <img src="<?php echo url('assets/front/'.Session::get("general")->theme.'/images/ajax-loader.gif');?>" />
                                                   </div>
                                                </div>
                                             </div>
                                             <?php /*<button type="button" class="btn btn-primary add_to_cart" id="addtocart<?php echo $pro->product_id;?>" title="@lang('messages.Add to cart')" <?php if(!Session::get('user_id')){ ?> data-toggle="modal" data-target="#myModal2"  <?php } else {  ?> onclick="AddtocartSubmit(<?php echo $pro->product_id;?>)" <?php } ?>>@lang('messages.Add to cart')</button> */ ?>
                                          </div>
                                       </div>
                                    </form>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <?php } ?>
            </div>
            <?php } else { ?>
            <div class="detail_sections">
               <div class="no_data col-md-12">
                  <div class="no_store_avlable store_info_new">
                     <div class="no_store_img">
                        <img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/no_store.png');?>" alt="">
                        <p>@lang('messages.No product available in this category!') <a  title="@lang('messages.Pick another')">@lang('messages.Pick another')<br/>@lang('messages.category')</a></p>
                     </div>
                  </div>
               </div>
            </div>
            <?php } ?>
         </div>
      </div>
      <?php if(isset($products->outlet_info[0]))
         { ?> 
      <div class="info_sections" style="display:none;">
         <div class="store_info_sections">
            <div class="stor_title col-md-12">
               <h1>@lang('messages.Store info')</h1>
            </div>
            <div class="col-md-4">
               <div class="sto_info_add">
                  <h2>{{ $outlet_info->vendor_name }}</h2>
                  <p>{{ $outlet_info->outlets_contact_address }}</p>
               </div>
               <div class="delevery_free">
                  <div class="col-md-4"><i class="glyph-icon flaticon-scooter"></i></div>
                  <div class="col-md-8">
                     <h3>@lang('messages.Delivery fee')</h3>
                     <?php if($currency_side == 1) { ?>
                     <h4>from { $currency_symbol }} {{ $outlet_info->outlets_delivery_charges_fixed }} </h4>
                     <?php } else{  ?>
                     <h4>from {{ $outlet_info->outlets_delivery_charges_fixed }} {{ $currency_symbol }}</h4>
                     <?php } ?>
                  </div>
               </div>
               <div class="delevery_free">
                  <div class="col-md-4"><i class="glyph-icon flaticon-wait"></i></div>
                  <div class="col-md-8">
                     <h3>@lang('messages.Pickup time')</h3>
                     <h4>{{ $outlet_info->outlets_pickup_time }}</h4>
                  </div>
               </div>
               <div class="delevery_free">
                  <div class="col-md-4"><i class="glyph-icon flaticon-wait"></i></div>
                  <div class="col-md-8">
                     <h3>@lang('messages.Delivery time')</h3>
                     <h4>{{ $outlet_info->outlets_delivery_time }}</h4>
                  </div>
               </div>
            </div>
            <div class="col-md-8">
               <div class="map_sections">
                  <div id="googleMap" style="width:100%;height:340px;"></div>
                  <script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyARWz_82qaq0ocuNOsMkWeQCXjHi6lLBbw"></script>
                  <script type="text/javascript">
                     function initialize() {
                     	var address="<?php echo $outlet_info->outlets_contact_address; ?>";
                     	var latlng = new google.maps.LatLng('<?php echo $outlet_info->outlets_latitude; ?>','<?php echo $outlet_info->outlets_latitude; ?>');
                     	var myOptions = {
                     	zoom: 8,
                     	center: latlng,
                     	mapTypeId: google.maps.MapTypeId.ROADMAP,
                     	navigationControl: true,
                     	mapTypeControl: true,
                     	scaleControl: true,
                     	};
                     	var map = new google.maps.Map(document.getElementById("googleMap"), myOptions);
                     	var marker = new google.maps.Marker({
                     	position: latlng,
                     	animation: google.maps.Animation.BOUNCE
                     	});
                     	marker.setMap(map);
                     	
                     	var contentString = '<div id="content">'+
                     '<div id="siteNotice">'+
                     '</div>'+
                     '<h3 id="firstHeading" class="firstHeading">'+address+'</h3>'+
                     '</div>';
                     	var infowindow = new google.maps.InfoWindow({
                     		content: contentString
                     	});
                     	google.maps.event.addListener(marker, 'click', function() { infowindow.open(map,marker); }); infowindow.open(map,marker);
                     	google.maps.event.addListener(marker, 'click', function() {
                      infowindow.open(map,marker);
                     });
                     infowindow.open(map,marker);
                     }
                  </script>
                  <?php $url=url('store/info/'.$outlet_info->url_index); $image=url('/assets/admin/base/images/vendors/list/'.$outlet_info->logo_image.''); $description=ucfirst($outlet_info->vendor_name); $url1=url('store/info/'.$outlet_info->url_index.'&title='.$outlet_info->vendor_name.'&summary='.$outlet_info->vendor_name.'source='.$url);  ?>
                  <div class="social_share_info">
                     <p>@lang('messages.Share on')</p>
                     <ul>
                        <li><a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}" title="Facebook" class="fb_icon"><i class="glyph-icon flaticon-facebook-logo"></i></a></li>
                        <li><a target="_blank" href="https://twitter.com/intent/tweet?url={{ urlencode($url) }}" title="Twitter" class="Tw_icon"><i class="glyph-icon flaticon-twitter-logo-silhouette"></i></a></li>
                        <li><a target="_blank" href="http://www.linkedin.com/shareArticle?mini=true&url=<?php echo $url1; ?>" title="instagram" class="in_icon"><i class="glyph-icon flaticon-instagram-social-network-logo-of-photo-camera"></i></a></li>
                        <li><a target="_blank" href="https://pinterest.com/pin/create/button/?url={{ $url }}&media={{ $image }}&description={{ $description }}" title="pinterest" class="pint_icon"><i class="glyph-icon flaticon-pinterest"></i></a></li>
                     </ul>
                  </div>
               </div>
            </div>
         </div>
         <div class="delevery_stat">
            <div class="row">
               <div class="col-md-6">
                  <div class="left_deliver">
                     <h3>@lang('messages.Delivery hours')</h3>
                     <ul>
                        <?php if(count($deliver_slot)){ ?>
                        <?php foreach($deliver_slot as $dkey => $del){ ?>
                        <li>
                           <div class="col-md-6 col-sm-6 col-xs-6">
                              <p><?php echo trans('messages.'.$dkey); ?></p>
                           </div>
                           <div class="col-md-6 col-sm-6 col-xs-6">
                              <?php foreach($del as $dlkey => $deldata){ ?>
                              <label>{{ $deldata }} </label>
                              <?php } ?>
                           </div>
                        </li>
                        <?php } ?>
                        <?php } ?>
                     </ul>
                  </div>
               </div>
               <div class="col-md-6">
                  <div class="left_deliver">
                     <h3>@lang('messages.Takeway hours')</h3>
                     <ul>
                        <?php
                           foreach($open_time as $key1 => $val1) {
                           ?>
                        <li>
                           <div class="col-md-6 col-sm-6 col-xs-6">
                              <p><?php echo trans('messages.'.$key1); ?></p>
                           </div>
                           <div class="col-md-6 col-sm-6 col-xs-6">
                              <label><?php echo (isset($val1[0]->start_time))?date("h:i a", strtotime($val1[0]->start_time)):'Leave'; ?> - <?php echo isset($val1[0]->end_time)?date("h:i a", strtotime($val1[0]->end_time)):'Leave'; ?></label>
                           </div>
                        </li>
                        <?php }  ?>
                     </ul>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <?php } ?>
      <div class="review_sections" style="display:none;">
         <div class="store_info_sections margin40">
            <div class="stor_title col-md-12">
               <h1>@lang('messages.Reviews')</h1>
            </div>
            <div class="review_sections">
               <?php if(count($reviews)){ ?>
               <script src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/plugins/rateit/src/jquery.rateit.js');?>"></script>
               <link href="<?php echo URL::asset('assets/front/base/plugins/rateit/src/rateit.css');?>" rel="stylesheet">
               <?php foreach($reviews as $rev){ ?>
               <div class="review_list">
                  <div class="col-md-2">
                     <div class="review_photo">
                        <?php  if(file_exists(base_path().'/public/assets/admin/base/images/admin/profile/thumb/'.$rev->image) && $rev->image != '') { ?>
                        <img src="<?php echo url('/assets/admin/base/images/admin/profile/thumb/'.$rev->image.''); ?>"  alt="{{ $rev->name }}">
                        <?php } else{  ?>
                        <img src=" {{ URL::asset('assets/admin/base/images/a2x.jpg') }} "  alt="{{ $rev->name }}">
                        <?php } ?>
                        <p>{{ $rev->name }}</p>
                     </div>
                  </div>
                  <div class="col-md-10">
                     <div class="review_rating">
                        <h3>{{ nicetime($rev->created_date) }}</h3>
                        <p>{{ $rev->comments }}</p>
                        <div class="rating">
                           <div class="rateit" data-rateit-value="<?php echo $rev->ratings; ?>" data-rateit-ispreset="true" data-rateit-readonly="true"></div>
                        </div>
                     </div>
                  </div>
               </div>
               <?php } ?>
               <?php } else { ?>
               <div class="no_data_found col-md-12">
                  <div class="no_data">
                     <div class="">
                        <div class="no_store_img">
                           <img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/no_store.png');?>" alt="">
                           <p>@lang('messages.No review posted for this store')</p>
                        </div>
                     </div>
                  </div>
               </div>
               <?php } ?>
            </div>
         </div>
      </div>
   </div>
</section>
<?php if(!Session::get('user_id')){ ?>
<script>
   $(document).ready(function() {
   $('.add_to_cart').on('click', function() {
   $('.cart_dyn_sec').modal('hide');
   });
   });
</script>		
<?php } else { ?>
<script>
   function AddtocartSubmit(product_id)
   {
   	$('.alert_infos').hide();
   	$('.alert_info'+product_id).html("");
   	$("#addtocart"+product_id).hide();
   	$("#ajaxloading"+product_id).show();
       var form = $("#add_cart_form"+product_id);
       var token = $('._token'+product_id).val();
       var form_data = $("#add_cart_form"+product_id).serialize();
       var form_method = $("#add_cart_form"+product_id).attr("method");
       var url = '{{url("addtocart")}}';
       var rurl = '{{url("cart")}}';
       $.ajax({
           url: url,
   		headers: {'X-CSRF-TOKEN': token},
   		data: form_data,
   		type: 'POST',
   		datatype: 'JSON',
   		success: function (data)
   		{
   			toastr.success(data.Message);
   			//$('.alert_infos').show();
   			//$('.alert_info'+product_id).addClass('alert_infos');
   			//$('.alert_info'+product_id).html(data.Message);
   			$("#ajaxloading"+product_id).hide();
   			$(".cart_total_count").html(data.cart_count);
   			$("#addtocart"+product_id).show();
           },
           error: function(data)
   		{
               var datas = data.responseJSON;
               /** $.each( datas.errors, function( key, value ) {
                  
               }); **/
           }
       });
   }
   	
</script>
<?php } ?>
<script>
   $(document).ready(function() {
   	$('.close').on('click', function() {
   			$('.sactual_quantity').val(1);
   			$('.qsactual_quantity').val(1);
   			$('.alert_infos').html("");
   			$('.add_to_cart').show();
   			$('.cat_price').hide();
   			$('.cat_price1').show();
   			$('.alert_infos').hide();
   			
   			//location.reload(); 
   		});
   		
   		
   		
   		$('.sqty_increase, .sqty_decrease').on('click', function() 
   		{
   			
   			user_id = "<?php echo Session::get('user_id'); ?>";
   			if(user_id == "")
   			{
   				$('.cart_dyn_sec').modal('hide');
   				$('#myModal2').modal('show');
   				return false;
   			}
   			
   			
   			$("#fadpage").show();
   			$('.alert_infos').hide();
   			$('.cat_price').hide();
   			$('.cat_price1').hide();
   			qty = $(this).parent().parent().find('.sactual_quantity').text();
   			qty = $.trim(qty);
   			if ($(this).hasClass('sqty_increase')) 
   			{
   				qty = parseInt(qty)+1;
   			}
   			else
   			{
   				qty = parseInt(qty)-1;
   			}
   			var current_id = $(this).attr('id');
   			if(qty >0)
   			{
   				amount = $('.total_amount'+current_id).val();
   				$(".cat_price").show();
   				total = qty*amount;
   				$('.final_total_amount'+current_id).val(total);
   				$('#total_amount'+current_id).html(total);
   				$("#addtocart"+current_id).show();
   				$(this).parent().parent().find('.sqty_decrease').attr("disabled", false);
   				
   			}
   			else
   			{
   				$(this).parent().parent().find('.sqty_decrease').attr("disabled", true);
   				$(".cat_price").hide();
   			}
   			if(qty >= 0)
   			{
   				$(this).parent().parent().find('.sactual_quantity').text(qty);
   				$('.quantity'+current_id).val(qty);
   				$('.alert_infos').hide();
   				$('.alert_info'+current_id).html("");
   				$("#addtocart"+current_id).hide();
   				var form = $("#add_cart_form"+current_id);
   				var token = $('._token'+current_id).val();
   				var form_data = $("#add_cart_form"+current_id).serialize();
   				var form_method = $("#add_cart_form"+current_id).attr("method");
   				var url = '{{url("addtocart")}}';
   				var rurl = '{{url("cart")}}';
   				$.ajax({
   					url: url,
   					headers: {'X-CSRF-TOKEN': token},
   					data: form_data,
   					type: 'POST',
   					datatype: 'JSON',
   					success: function (data)
   					{
   						$("#fadpage").hide();
   						toastr.success(data.Message);
   						$(".cart_total_count").html(data.cart_count);
   						$("#addtocart"+current_id).show();
   					},
   					error: function(data)
   					{
   						$("#fadpage").hide();
   						var datas = data.responseJSON;
   					}
   				});
   			}
   			else
   			{
   				$("#fadpage").hide();
   				return false;
   			}
   			$("#fadpage").hide();
   				
   		});
   		
   		
   		
   		
   			$('.sactual_quantity').keyup(function() {
   				var dInput = this.value;
   				var current_id = $(this).attr('id');
   				amount = $('.total_amount'+current_id).val();
   				total = dInput*amount;
   				$('.final_total_amount'+current_id).val(total);
   				$('#total_amount'+current_id).html(total);
   				$('.quantity'+current_id).val(dInput);
   				if(!total){
   					$("#addtocart"+current_id).hide();
   				}else {
   					$("#addtocart"+current_id).show();
   				}
   			});
   		});
   		function isNumber(evt) {
   					evt = (evt) ? evt : window.event;
   					var charCode = (evt.which) ? evt.which : evt.keyCode;
   					if (charCode > 31 && (charCode < 48 || charCode > 57)) {
   						return false;
   					}
   					return true;
   		}
   		$(document).ready(function () {
   			$("#textbx").keyup(function () {
   				if ($("#textbx").val().length > 0) {
   					$("#btn").removeAttr('disabled');
   				}
   			});
   			$("#textbx").blur(function () {
   				if ($("#textbx").val().replace(/\s{1,}/g, "").length == 0) {
   					$("#btn").attr('disabled', 'disabled');
   				}
   			});
   		});
   		function review(){
   		$(".review").addClass("info_active");
   		$(".info").removeClass("info_active");
   		$(".home").removeClass("info_active");
   	    $(".review_sections").show();
   		$(".detail_sections").hide();
   		$(".info_sections").hide();
   	}
   	function info(){
   		initialize();
   		$(".review").removeClass("info_active");
   		$(".info").addClass("info_active");
   		$(".home").removeClass("info_active");
   		$(".info_sections").show();
   		$(".review_sections").hide();
   		$(".detail_sections").hide();
   	}
   	
   	function back_product(){
   		$(".review").removeClass("info_active");
   		$(".home").addClass("info_active");
   		$(".info").removeClass("info_active");
   		$(".info_sections").hide();
   		$(".review_sections").hide();
   		$(".detail_sections").show();
   	}
   	  function favourite(vendor_id){
   		var url = '{{url("api/addto_favourite")}}';
   		var token = '<?php echo csrf_token(); ?>';
   		var user_id = '<?php echo Session::get('user_id'); ?>';
   		var user_token = '<?php echo Session::get('token'); ?>';
   		$.ajax({
   		url: url,
   		headers: {'X-CSRF-TOKEN': token},
   		data: {"vendor_id":vendor_id,"user_id":user_id,"token":user_token},
   		type: 'POST',
   		dataType:"json",
   		success: function (data){
   			 $('[data-toggle="popover"]').popover(); 
   			//$('.favourite-success').html(data.response.Message);
   			toastr.success(data.response.Message);
   			if(data.response.status){
   				$('.favourite').parent('li').addClass('info_active');
   			}else {
   				$('.favourite').parent('li').removeClass('info_active');
   			}
   		},
   		error: function(data){
   			var datas = data.responseJSON;
   		}
   		});
   	}
</script>
@endsection
