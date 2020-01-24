

@extends('layouts.app')
@section('content')
<?php $currency_side = getCurrencyPosition()->currency_side;$currency_symbol = getCurrency(getCurrentLang());$product = $products->data;?>
<script src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/plugins/rateit/src/jquery.rateit.js');?>"></script>
<link href="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/plugins/rateit/src/rateit.css');?>" rel="stylesheet">

<section class="poroduct_listing_info">
    <div class="product_info responsive_style">
        <div class="container">
            <div class="row">
                <div class="bread_corumb_sectiuon col-md-12">
                    <ol class="breadcrumb">
                        <li><a href="{{url('/')}}" title="@lang('messages.Home')">@lang('messages.Home')</a></li>
                        <li><a href="{{url('store/info/'.$product->outlet_url_index)}}">{{ucfirst($product->outlet_name)}}</a></li>
                        <li class="active">{{ucfirst($product->product_name)}}</li>
                    </ol>
                </div>
                <div class="col-md-6">
                    <div class="product_info_left_sect">
                      
						<div class="page">
						<div class="sp-loading"><img src="{{ URL::asset('assets/front/tijik/images/zooming/sp-loading.gif')}}" alt=""><br>LOADING IMAGES</div>
						<div class="sp-wrap">
							
										<?php  if(file_exists(base_path().'/public/assets/admin/base/images/products/detail/'.$product->product_info_image) && $product->product_info_image!='') { ?>
										<a href="<?php echo url('/assets/admin/base/images/products/detail/'.$product->product_info_image.''); ?>" title=""> <img  title="{{ $product->product_name }}" alt="{{ ucfirst(strtolower($product->product_name)) }}"  src="<?php echo url('/assets/admin/base/images/products/detail/'.$product->product_info_image.''); ?>" ></a>
										<?php } else{  ?>
										<a href="{{ URL::asset('assets/admin/base/images/products/product.png') }}" title=""><img src="{{ URL::asset('assets/admin/base/images/products/product.png') }}" alt="{{ ucfirst(strtolower($product->product_name)) }}"></a>
										<?php } ?>
							
							<?php  if(file_exists(base_path().'/public/assets/admin/base/images/products/zoom/'.$product->product_zoom_image) && $product->product_zoom_image!='') { ?>
										<a href="<?php echo url('/assets/admin/base/images/products/zoom/'.$product->product_zoom_image.''); ?>" title=""> <img  title="{{ $product->product_name }}" alt="{{ ucfirst(strtolower($product->product_name)) }}"  src="<?php echo url('/assets/admin/base/images/products/zoom/'.$product->product_zoom_image.''); ?>" ></a>
										<?php } else{  ?>
										<a href="{{ URL::asset('assets/admin/base/images/products/product.png') }}" title=""><img src="{{ URL::asset('assets/admin/base/images/products/product.png') }}" alt="{{ ucfirst(strtolower($product->product_name)) }}"></a>
										<?php } ?>
						</div>
						</div>



                    </div>
                </div>
                <div class="col-md-6">
                    <div class="product_info_right_sect">
                        <p>{{$product->product_name}}</p>
                       <?php /* <div class="review_tijik">
                            <a href="javascript:;">@lang('messages.Reviews') (<?php echo (count($product_reviews));?>)</a>
                            <span class="left_new_divide">| </span>
                            <?php $product_id = encrypt($product->product_id); ?>
                            <?php if(Session::has('user_id')){?>
                            <a href="" data-toggle="modal" data-target="#productrating" title="@lang('messages.Review')">@lang('messages.Write a review')</a>	
                            <?php }else{?>
                            <a data-toggle="modal" data-target="#myModal2" href="javascript:;" title='@lang("messages.Write a review")'> @lang("messages.Rate this product")</a>
                            <?php } ?>
                        </div> */?>
                        <?php /*<h2>Product Code: U Ultra</h2>
                            <label>Availability: In Stock </label>
                            <h2>Product Code: product 11 </h2>*/ ?>
                        <div class="col-md-4 col-sm-4 col-xs-4 padding0">
                            <div class="count_numbers">
                                <form method="POST" name="appointment_form" accept-charset="UTF-8" id="add_cart_form<?php echo $product->product_id;?>">
                                    <input type="hidden" name="_token" class="_token<?php echo $product->product_id;?>" value="{{ csrf_token() }}">
                                    <input type="hidden" name="total_amount" class="total_amount<?php echo $product->product_id;?>" value="{{ $product->discount_price }}">
                                    <input type="hidden" name="product_id" class="product_id<?php echo $product->product_id;?>" value="{{ $product->product_id }}">
                                    <input type="hidden" name="quantity" class="quantity<?php echo $product->product_id;?> qsactual_quantity" value="<?php echo $product->product_cart_count; ?>">
                                    <input type="hidden" name="final_total_amount" class="final_total_amount<?php echo $product->product_id;?>" value="{{ $product->discount_price }}">
                                    <input type="hidden" name="outlet_id" class="outlet_id<?php echo $product->product_id;?>" value="{{ $product->outlet_id }}">
                                    <input type="hidden" name="vendors_id" class="vendors_id<?php echo $product->product_id;?>" value="{{ $product->vendor_id }}">
                                    <ul>
                                        <li class="minuse_count"><a href="javascript:;" class="sqty_decrease" id="<?php echo $product->product_id;?>">-</a></li>
                                        <li class="minuse_number sactual_quantity" id="<?php echo $product->product_id;?>">
                                            <?php echo $product->product_cart_count; ?>
                                        </li>
                                        <li class="pluse_number"><a href="javascript:;" class="sqty_increase" id="<?php echo $product->product_id;?>">+</a></li>
                                    </ul>
                                </form>
                            </div>
                        </div>
                        <ul class="list-unstyled">
                            <?php if($currency_side == 1) { ?>
                            <li>@if(($product->original_price-$product->discount_price > 0) && ($product->original_price > 0))<span class="marked_price"><?php echo  $currency_symbol; ?>{{ $product->original_price }} </span>
                            @endif<span class="nrl_price"><?php echo $currency_symbol; ?>{{ $product->discount_price }} </span></li>
                            <?php }else{?>
                            <li>@if(($product->original_price-$product->discount_price > 0) && ($product->original_price > 0))<span class="marked_price">{{ $product->original_price }}<?php echo  $currency_symbol; ?> </span>
                            @endif<span class="nrl_price">{{ $product->discount_price }}<?php echo $currency_symbol; ?> </span></li>
                            <?php } ?>
                        </ul>
                        <div class="addto_cart_buttons">
                            <p>{{ucfirst($product->description)}}</p>
                            <?php /*<button id="button-cart" class="item_common_new" data-toggle="modal" data-target=".exampleModal<?php echo $product->product_id;?>" data-whatever="@mdo">@lang('messages.add to cart')</button>*/ ?>
                        </div>
                         <div class="price_buttons">
                        <a href="{{url('store/info/'.$product->outlet_url_index)}}" class = "btn btn-primary btn-lg" title=" @lang('messages.Continue shopping')">@lang('messages.Continue shopping')</a>
                    </div>
                    </div>
                </div>
                <?php /*<div class="review_sections col-md-12">
                    <div class="store_info_sections margin40">
                        <div class="stor_title col-md-12">
                            <h1>@lang('messages.Reviews')</h1>
                        </div>
                        <div class="review_sections">
                            <?php if(count($product_reviews)){ ?>
                            <script src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/plugins/rateit/src/jquery.rateit.js');?>"></script>
                            <link href="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/plugins/rateit/src/rateit.css');?>" rel="stylesheet">
                            <?php foreach($product_reviews as $rev){ ?>
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
                                            <p>@lang('messages.No review posted for this product')</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div> */?>
                <div class="modal fade model_for_signup membership_login" id="productrating" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                                </button>
                                <span class="logo_popup">
                                <img src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/'.Session::get("general")->theme.'.png'); ?>" title="{{ Session::get('general')->site_name }}" alt="{{ Session::get('general')->site_name }}">
                                </span>
                            </div>
                            <div class="modal-body">
                                <div class="sign_up_inner">
                                    <h2>@lang('messages.Lets rate the product')<br>
                                        <span class="bottom_border"></span>
                                    </h2>
                                    {!!Form::open(array('url' => 'product-rating', 'method' => 'post', 'class' => 'tab-form attribute_form', 'id' => 'product-rating' ,'onsubmit'=>'return rating()'));!!} 
                                    <div class="membership_inner">
                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label> @lang('messages.Star rating'): </label>
                                                <div class="rateit" data-rateit-value="1"   id="rateit"  style="cursor:pointer" > </div>
                                                <input type="hidden" name="starrating" value="1" class="form-control rating_value">
                                                <span class="error"> 
                                                @if ($errors->has('starrating'))
                                                {{ $errors->first('starrating', ':message') }}
                                                @endif
                                                </span>
                                                <input type="hidden" name="outlet_id" value="{{ $product->outlet_id }}" class="form-control">
                                                <input type="hidden" name="user_id" value="{{ Session::get('user_id') }}" class="form-control">
                                                <input type="hidden" name="vendor_id" value="{{ $product->vendor_id }}" class="form-control">
                                                <input type="hidden" name="language" value="{{ getCurrentLang() }}">
                                                <input type="hidden" name="product_id" value="{{ $product->product_id }}" class="form-control">
                                                <div class="strss_over">
                                                    <span class="clr4 value5"></span>
                                                    <span class="hover5"></span>
                                                </div>
                                                <?php $star_descritption=array(trans('messages.Very Poor'),trans('messages.Poor'),trans('messages.Average'),trans('messages.Good'),trans('messages.Very Good'));
                                                    $description= (array)$star_descritption; ?>
                                                <script type="text/javascript">
                                                    var tooltipvalues = <?php echo json_encode(array_values($description));?>;
                                                    $("#rateit").bind('rated', function (event, value) { $('.rating_value').val(value); $('.value5').text('You\'ve rated it: ' + value +' '+$('#rateit').attr("title")); });
                                                    $("#rateit").bind('reset', function () { $('.value5').text('Rating reset'); });
                                                     $("#rateit").bind('over', function (event, value) {
                                                    	if(value) { var val= value }else { val='';}  
                                                    	$(this).attr('title', tooltipvalues[val-1]);
                                                    	$('.value5').text(val +' '+$('#rateit').attr("title")); 
                                                    });
                                                    $("#rateit").rateit({ max: 5, step: 1 ,resetable: false});
                                                </script>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label> @lang('messages.Comments'): </label>
                                                <textarea name="comments" required class="form-control comments" placeholder="@lang('messages.Comments')" rows="6" cols="50"> <?php echo old('comments'); ?> </textarea>
                                                <span class="error"> 
                                                @if ($errors->has('comments'))
                                                {{ $errors->first('comments', ':message') }}
                                                @endif
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <div class="sign_bot_sub">
                                                    <button type="button" class="btn btn-primary" data-dismiss="modal" title="@lang('messages.Cancel')">@lang('messages.Cancel')</button>
                                                    <button type="submit" class="btn btn-default membership_submit" title="@lang('messages.Submit')">@lang('messages.Submit')</button>
                                                    <div class="ajaxloading" style="display:none;">
                                                        <div class="loader-coms">
                                                            <div class="loder_gif">
                                                                <img src="<?php echo url('assets/front/'.Session::get("general")->theme.'/images/ajax-loader.gif');?>" />	
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {!!Form::close();!!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
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
    						if(data.cart_count > 0)
    						{
    							$(".cart_total_count").html('('+data.cart_count+')');
    						}
    						else {
    							$(".cart_total_count").html();
    						}
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
    function review()
    {
    		$(".review_sections").show();
    		}
    function rating()
    {
    	$( '#success_message_signup' ).show().html("");
    	//$(".membership_submit").hide();
    	$(".ajaxloading").show();
    	data = $("#product-rating").serializeArray();
    	var c_url = '/product-rating';
    	token = $('input[name=_token]').val();
    	$.ajax({
    		url: c_url,
    		headers: {'X-CSRF-TOKEN': token},
    		data: data,
    		type: 'POST',
    		datatype: 'JSON',
    		success: function (resp)
    		{
    			$(".ajaxloading").hide();
    			data = resp;
    			console.log(data.httpCode);
    			if(data.httpCode == 200)
    			{
    				toastr.success(data.Message);
    				$('#productrating').modal('hide');
    				$('.comments').val('');
    				$('.title').val('');
    				$('.ajaxloading').hide();
    				//$('.membership_submit').show();
    				//location.reload(true);
    				return false;
    			}
    			else
    			{
    				toastr.warning(data.Message);
    				$('.ajaxloading').hide();
    				//$('.membership_submit').show();
    				return false;
    			}
    		}, 
    		error:function(resp)
    		{
    		}
    	});
    	return false;
    }		
    $(document).ready(function()
    {
    	$('[data-dismiss="modal"]').click(function(e) {
    		$('.comments').val('');
    		$('.title').val('');
    		$('.rating_value').val(1);
    		$('.ajaxloading').hide();
    		
    	});
    	
    	
    	
    });
    
</script>
	<script type="text/javascript">
	/* wait for images to load */
	$(window).load(function() {
		$('.sp-wrap').smoothproducts();
	});
	</script>
@endsection

