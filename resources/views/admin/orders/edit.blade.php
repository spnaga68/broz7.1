@extends('layouts.admin')
@section('content')
<link href="{{ URL::asset('assets/admin/base/css/bootstrap-timepicker.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/admin/base/css/select2.css') }}" media="all" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" />
   
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

    <style>

  .container{margin-top:30px;}
  .form-group{float:left;width:100%;} 
  /*.btn{margin-bottom:10px;width:10%;}*/
   .form_filed{float:left;width:100%;border: 1px solid #ddd;padding: 20px;border-radius: 4px;}
 .form-control{float:left;width:30%;margin-right:20px}
  .cancel{float:left;width:10%;}

.count_numbers {
 display: inline-block; }

.count_numbers .actual_quantity {
 width: 35px;
 height: 39px;
 padding: 0 5px;
 text-align: center;
 background-color: transparent;
 border: 1px solid #efefef;
}
.count_numbers.buttons_added {
 text-align: left;
 position: relative;
 white-space: nowrap;
 vertical-align: top; }


.count_numbers.buttons_added input {
 display: inline-block;
 margin: 0;
 vertical-align: top;
 box-shadow: none;
}

.count_numbers.buttons_added .minuse_count,
.count_numbers.buttons_added .pluse_number {
 padding: 7px 10px 8px;
 height: 41px;
 background-color: #ffffff;
 border: 1px solid #efefef;
 cursor:pointer;}

.count_numbers.buttons_added .minuse_count {
 border-right: 0; }

.count_numbers.buttons_added .pluse_number {
 border-left: 0; }

.count_numbers.buttons_added .minuse_count:hover,
.count_numbers.buttons_added .pluse_number:hover {
 background: #eeeeee; }

.count_numbers input::-webkit-outer-spin-button,
.count_numbers input::-webkit-inner-spin-button {
 -webkit-appearance: none;
 -moz-appearance: none;
 margin: 0; }
 
 .count_numbers.buttons_added .minuse_count:focus,
.count_numbers.buttons_added .pluse_number:focus {
 outline: none; }
</style>
<?php $currency_side = getCurrencyPosition()->currency_side;
$currency_symbol = getCurrency();?>

<?php 
// $table=DB::table("coupons")
// 		->select('coupon_code')
// 		->first();
?>
<!-- Nav tabs -->
<div class="pageheader">
	<div class="media">
		<div class="pageicon pull-left">
			<i class="fa fa-home"></i>
		</div>
		<div class="media-body">
			<ul class="breadcrumb">
				<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
				<li>@lang('messages.Products')</li>
			</ul>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->


<div class="contentpanel">
    <div class="col-md-12">
        <div class="row panel panel-default">
            <div class="grid simple">
                @if (count($errors) > 0)
                <div class="alert alert-danger" style="display:none">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li><?php echo trans('messages.' . $error); ?> </li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <div class="alert alert-danger" style="display:none">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>
                    <ul id="error_list">
                    </ul>
                </div>
                <div class="admin_sucess_common" id="error_time" style="display:none;">
                    <div class="admin_sucess">
                        <div class="alert alert-info" id="errors"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
                    </div>
                </div>
	{!!Form::open(array('url' => ['update_cart',$order_id], 'method' => 'post','class'=>'panel-wizard','id'=>'edit_cart_form','files' => true));!!}
		<div class="tab-content tab-content-simple mb30 no-padding" >
			
			<div class="tab-pane" id="edit_info">
				<legend>@lang('messages.Order Edit')</legend>
				  <div class="cart_sections_tables">
                <?php if (count($cart_items) > 0) {	?>
                    <div class="table-responsive cart_items">
                        <div class="responsive_tables">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th align="center" class ="cartalign">@lang('messages.Products')</th>
                                        <th align="center">@lang('messages.Price')</th>
                                        <th align="center">@lang('messages.Weight')</th>
                                        <th align="center"></th>
                                        <th align="center">@lang('messages.Quantity')</th>
                                        <th align="center" >@lang('messages.Remove')</th>
                                        <th align="center" class="items_quantty">@lang('messages.Total')</th>
                                    </tr>
                                </thead>
                                <tbody><?php $minimum_order_amount = 0; //print_r($outlet_id);exit;?>
                                    <?php foreach ($cart_items as $items) { ?>

                                        <tr data-cart_id="" data-cart_detail_id="" class="cart_row">
                                        	<input type="hidden" name="order_id" id="order_id" value="<?php echo $order_id; ?>">
                                        
                                        	<input type="hidden" name="cart_items_count" id="cart_items_count" value="<?php echo count($cart_items) ?>">
                                        	<input type="hidden" name="vendor_key" id="vendor_key" value="<?php echo $items->vendor_key; ?>">

                                        	<input type="hidden" name="vendor_name" id="vendor_name" value="<?php echo $items->vendor_name; ?>">
                                            <td style="text-align:left;">
                                            	 <?php if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $items->product_image)) {?>
                                                       <img src="<?php echo url('/assets/admin/base/images/products/list/' . $items->product_image); ?>" title="{{ $items->product_name }}" width="70" height="40">  <?php } else {?>
                                                         <img src="{{ URL::asset('assets/admin/base/images/products/product.png') }}" alt="{{ ucfirst(strtolower($items->product_name)) }}" width="70" height="40">
                                                <?php } ?>
                                               <!--  <a href="javascript:;" title="{{ ucfirst(strtolower($items->product_name)) }}">
                                                 {{ str_limit(ucfirst(strtolower($items->product_name)),30) }}
                                                </a>  -->   <span title="{{ ucfirst(strtolower($items->product_name)) }}">
                                                 {{ str_limit(ucfirst(strtolower($items->product_name)),30) }}
                                                </span>
                                                <input type="hidden" name="products_name[]" id="products_name[]" value="<?php echo $items->id;  ?>">
                                            </td>

											<?php if ($currency_side == 1) {?>
                                            <td>{{$currency_symbol}}<span class="item_price">{{$items->discount_price}}</span>          
                                             <input type="hidden" name="item_price[]" id="item_price" value="<?php echo $items->discount_price;  ?>">
											</td>
											<?php } else {?>
											 <td><span class="item_price">{{$items->discount_price}}</span>  
											{{$currency_symbol}}						
											 <input type="hidden" name="item_price[]" id="item_price" value="<?php echo $items->discount_price;  ?>">

											</td>
											<?php }?>


                                            <td><span class="item_price">{{$items->weight}}</span>          
                                             <input type="hidden" name="weight[]" id="weight" value="<?php echo $items->weight;  ?>">
											</td>

											<?php if ($items->adjust_weight == 1) {?>
											
											<td><span class="item_price">{{$items->title}}</span>
												<input type="number" name="adjust_weight_qty[]" id="adjust_weight_qty" class="adjust_weightqty" value="<?php echo $items->adjust_weight_qty;  ?>">  
												<input type="hidden" name="weight_qty" id="weight_qty" value=""> 
											</td>
											<?php }else{?>
												<th><input type="hidden" name="adjust_weight_qty[]" id="adjust_weight_qty" value=0 class="adjust_weightqty" ><input type="hidden" name="weight_qty" id="weight_qty" value=""> 
												</th>

											<?php } ?>
                                            <td>
                                                <div class="count_numbers buttons_added">
  													<ul data-item_price="{{$items->discount_price}}">
                                                	 	<button type="button"  class="minuse_count qty_decrease1"><a class="" href="javascript:;" class="minuse_number"> - </a> </button>  <input type="button"class="actual_quantity"  readonly value="{{$items->quantity}}">
                                                	 	<button type="button" value=""  class="pluse_number qty_increase1"><a class="" href="javascript:;"> +  </a> </button>	
                                                	 	<input type="hidden" name="quantity[]" id="quantity" value="<?php echo $items->quantity;  ?>">			
                                                	</ul>                                       
												</div>
												<!--  <div class="count_numbers buttons_added">
											        <ul data-item_price="{{$items->discount_price}}">
											          <input type="button" value="-" class="minus minuse_count qty_decrease1">
											          <input type="button"class="actual_quantity"  readonly value="{{$items->quantity}}">
											          <input type="button" value="+" class="plus pluse_number qty_increase1">
											          <input type="hidden" name="quantity[]" id="quantity" value="<?php echo $items->quantity;  ?>">      
											        </ul>                                       
											      </div> -->
                                            </td>

                                            <td valign="middle" class="delet_icons">
			                                               
								               	<a href="#" class="remove_row">
										          <span class="glyphicon glyphicon-trash" ></span>
										        </a>
												<!-- <input type="button" name="remove" id="remove" class="remove_row">
												 -->                                       
											</td>                                      
								    <?php  $product_total = $items->discount_price*$items->quantity;
								      	$weight_qty = $items->adjust_weight_qty*$items->quantity;
								      	$adj_tot =$weight_qty *( $items->discount_price/$items->weight);
								      //	$product_total = $product_total+$adj_tot;
								      	$product_total =round($product_total, 2);
								 
								      	
								    ?>

											<?php if ($currency_side == 1) {?>
                                                 <td valign="middle" class="items_quantty">{{$currency_symbol}}<span class="item_total">{{round($product_total ,2)}}</span></td>
											    <input type="hidden" name="item_total[]" id="item_total" value="<?php echo $product_total;  ?>">
											    <input type="hidden" name="tot_org" id="tot_org" value="<?php echo $product_total;  ?>">
											<?php } else {?>
											     <td valign="middle" class="items_quantty"><span class="item_total">{{round($items->discount_price*$items->quantity+$adj_tot,2)}}</span>{{$currency_symbol}}</td>
											    <input type="hidden" name="item_total[]" id="item_total" value="<?php echo $product_total;  ?>">
											    <input type="hidden" name="tot_org" id="tot_org" value="<?php echo $product_total;  ?>">
											<?php }?>
                                        </tr>
                                    <?php }?>

                                </tbody>
	                    
                            </table>
                        </div>  
                        <br>                 
                           	<div class="price_setions"  id="price_section" >
	                            <div class="price_setions_list">
	                                <div class="col-md-7 dis_none"></div>
		                                <div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.Subtotal')</label></div>
										<?php if ($currency_side == 1) {?>
		                                     <div class="col-md-2 col-sm-6 col-xs-6"><p>{{$currency_symbol}}<span id="sub_total">{{$sub_total}}</span></span></p></div>
		                                   		<input type="hidden" name="sub_total" id="sub_total_h" value="<?php echo $sub_total;  ?>">
		                                   		<input type="hidden" name="sub_total_org" id="sub_total_org" value="<?php echo $sub_total;  ?>">

										<?php } else {?>
										     <div class="col-md-2 col-sm-6 col-xs-6"><p><span id="sub_total">{{$sub_total}}</span>{{$currency_symbol}}</span></p></div>
										    <input type="hidden" name="sub_total" id="sub_total_h" value="<?php echo $sub_total;  ?>">
										    <input type="hidden" name="sub_total_org" id="sub_total_org" value="<?php echo $sub_total;  ?>">

										<?php }?>
	                            </div>
	                            <div class="price_setions_list">
									<div class="col-md-7 dis_none"></div>
									<div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.Delivery cost')</label></div>
									<?php if ($currency_side == 1) {?>
									    <div class="col-md-2 col-sm-6 col-xs-6"><p>{{$currency_symbol}}<span id="delivery_cost">{{$delivery_cost}}</span></p></div>
									    <input type="hidden" name="delivery_cost" id="delivery_cost_h" value="<?php echo $delivery_cost;  ?>">

									<?php } else {?>
									    <div class="col-md-2 col-sm-6 col-xs-6"><p><span id="delivery_cost">{{$delivery_cost}}</span>{{$currency_symbol}}</p></div>
									    <input type="hidden" name="delivery_cost" id="delivery_cost_h" value="<?php echo $delivery_cost;  ?>">

									<?php }?>
								</div>
	                            <div class="price_setions_list">
	                                <div class="col-md-7 dis_none"></div>
	                                <div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.tax')</label></div>
									<?php if ($currency_side == 1) {?>
	                                     <div class="col-md-2 col-sm-6 col-xs-6"><p>{{$currency_symbol}}<span id="tax">{{$tax_amount}}</span></span></p></div>
	                                    <input type="hidden" name="tax" id="tax_amount" value="<?php echo $tax_amount;  ?>">
	                                     <input type="hidden" name="tax_amount_org" id="tax_amount_org" value="<?php echo $tax_amount;  ?>">

	                                    <input type="hidden" name="service_tax" id="service_tax" value="<?php echo $tax;  ?>">

									<?php } else {?>
	                                     <div class="col-md-2 col-sm-6 col-xs-6"><p><span id="tax">{{$tax_amount}}</span>{{$currency_symbol}}</span></p></div>
	                                    <input type="hidden" name="tax" id="tax_amount" value="<?php echo $tax_amount;  ?>">
	                                    <input type="hidden" name="tax_amount_org" id="tax_amount_org" value="<?php echo $tax_amount;  ?>">
	                                    <input type="hidden" name="service_tax" id="service_tax" value="<?php echo $tax;  ?>">


									<?php }?>
	                            </div>
	                            <div class="price_setions_list_total">
	                                <div class="col-md-7 dis_none"></div>
	                                <div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.Total with delivery cost')</label></div>
									<?php if ($currency_side == 1) {?>
	                                    <div class="col-md-2 col-sm-6 col-xs-6"><p>{{$currency_symbol}}<span id="total">{{$total}}</span></p></div>
	                                    <input type="hidden" name="total" id="total_h" value="<?php echo $total;  ?>">
	                                    <input type="hidden" name="total_org" id="total_org" value="<?php echo $total;  ?>">

									<?php } else {?>
									   <div class="col-md-2 col-sm-6 col-xs-6"><p><span id="total">{{$total}}</span>{{$currency_symbol}}</p></div>
									    <input type="hidden" name="total" id="total_h" value="<?php echo $total;  ?>">
									    <input type="hidden" name="total_org" id="total_org" value="<?php echo $total;  ?>">

									   <?php }?>
	                            </div>

	                            <div class="price_setions_list offer_amount" style="display:none;">
									<div class="col-md-7 dis_none"></div>
									<div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.Coupon discount')</label></div>
									<?php if ($currency_side == 1) {?>
									    <div class="col-md-2 col-sm-6 col-xs-6"><p>{{$currency_symbol}}<span id="offer_amount_value"></span></p></div>

									<?php } else {?>
									    <div class="col-md-2 col-sm-6 col-xs-6"><p><span id="offer_amount_value">{{$currency_symbol}}</p></div>

									<?php }?>
								</div>

								<div class="price_setions_list total_pay" style="display:none;">
									<div class="col-md-7 dis_none"></div>
									<div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.Amount to pay')</label></div>
									<?php if ($currency_side == 1) {?>
									    <div class="col-md-2 col-sm-6 col-xs-6"><p>{{$currency_symbol}}<span id="total_pay_amount"></span></p></div>

									<?php } else {?>
									    <div class="col-md-2 col-sm-6 col-xs-6"><p><span id="total_pay_amount">{{$currency_symbol}}</p></div>

									<?php }?>
									<input type="hidden" name="protot_amount" id="protot_amount" value="">
								</div>

	                      

	                            <div>
									<div class="col-md-7 dis_none"></div>
			                        <div class="col-md-5 col-sm-6 col-xs-4">
 										<input type="text" autocomplete="off" id="promo_code" class="search-query form-control" placeholder="@lang('messages.Promo code')" value="<?php echo $coupon_code ?>"/>
 										<p class="col-md-12">* @lang('messages.Please enter the valid promo code here , click apply button for apply coupon')</p>
 										<button class="btn btn-danger" type="button" id="apply_promocode" title="@lang('messages.Coupon Apply')" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> @lang('messages.Processing')">@lang('messages.Coupon Apply')</button>
										<button style="display:none" class="btn btn-danger" type="button" id="remove_promocode" title="@lang('messages.Remove')">@lang('messages.Remove')</button>
	                                </div>

		                        	<input type="hidden" name="coupon_id" id="coupon_id" value="">
									<input type="hidden" name="coupon_amount" id="coupon_amount">
									<input type="hidden" name="coupon_type" id="coupon_type" value="0">
												
	                                <input type="hidden" name="outlet_id" id="outlet_id" value="<?php echo $outlet_id;  ?>">
	                                <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $customer_id;  ?>">
	                            </div>
				    </div>

				<?php $no_cart = 'style=display:none';
				} else { $no_cart = 'style=display:block';}?>
	                <div class="table-responsive empty_cart" {{$no_cart}}>
	                    <div class="no_cart">
	                        <img src="<?php echo URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_pro_03.png'); ?>" alt="ums_logo">
	                        <h2>@lang('messages.You don’t have any items in your shopping cart.')</h2>
	                    </div>
	                </div>
            </div>

			<div class="form-group Loading_Img" style="display:none;">
                <div class="col-sm-4">
                   <i class="fa fa-spinner fa-spin fa-3x"></i><strong style="margin-left: 3px;">@lang('messages.Processing...')</strong>
                </div>
            </div>
        </div>
                    <!-- panel-default -->
        <div class="panel-footer Submit_button" id="sub_btn">
            <input type="hidden" name="tab_info" class="tab_info" value="">
              <button type="submit" onclick="HideButton('Submit_button','Loading_Img');" id="cart_sub" onsubmit="HideButton('Submit_button','Loading_Img');" class="btn btn-primary mr5" title="Save">@lang('messages.Save')</button>
                <button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('admin/orders/index') }}'">@lang('messages.Cancel')</button>
        </div><!-- <?php// echo"<pre>";print_r($currency_side) ;exit;?> -->
			{!!Form::close();!!}
			<?php// exit; ?>
		</div>
	</div>
</div>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/jquery-ui-1.10.3.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/bootstrap-timepicker.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>

<script type="text/javascript" src="{{ URL::asset('assets/front/broz/js/toastr.min.js') }}"></script>

<link href="{{ URL::asset('assets/admin/base/js/toastr.min.css') }}" media="all" rel="stylesheet" type="text/css" />

<!-- 	  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
 --><script type="text/javascript">

$( document ).ready(function() {

	$('#apply_promocode').on('click', function() {
		var promo_code = $('#promo_code').val();
		var outlet_id  = $('#outlet_id').val();
				var customer_id =$("#customer_id").val();

		if(promo_code == ""){
			//alert("Please fill promo code");
			toastr.warning("<?php echo trans('messages.Please fill promo code') ?>", {timeOut: 5000});

			return false;
		}
		var $this = $("#apply_promocode");
		var c_url = '/check_promocode';
		token = $('input[name=_token]').val();
		$.ajax({
			url: c_url,
			headers: {'X-CSRF-TOKEN': token},
			data: {promo_code:promo_code,outlet_id:outlet_id,customer_id:customer_id},
			type: 'POST',
			datatype: 'JSON',
			success: function (resp)
			{
				//console.log(resp.httpCode);//return false;
				var total_h  = $('#total_h').val();
				total=Math.floor(total_h) ;
				var total_pay = "";
				if(resp.httpCode == 400) {
					toastr.error(resp.Message, {timeOut: 5000});
					return false;
				}
				var offer_amount = 0;
				if(resp.coupon_details.offer_type == 1) {
					var offer_amount = resp.coupon_details.offer_amount;
					if(total > offer_amount ) {
						var total_pay = parseFloat(total - resp.coupon_details.offer_amount).toFixed(3);
					}else {
						var total_pay = 0;
					}
				} else {
					var offer_amount = ((total*resp.coupon_details.offer_amount)/100).toFixed(2);
					if(total > offer_amount ){
						var total_pay = parseFloat(total - resp.coupon_details.offer_amount).toFixed(3);
					} else {
						var total_pay = 0;
					}
                 	//  var total_pay = parseFloat(total - offer_amount).toFixed(2);
				}

				$(".offer_amount").show();
				$(".total_pay").show();
	         	//$("#total_h").val(total_pay);
				$("#total_pay_amount").text(total_pay);
				$("#protot_amount").val(total_pay);
	            $("#offer_amount_value").text(offer_amount);
	            $("#offer_amount").text(offer_amount);
	            $("#coupon_id").val(resp.coupon_details.coupon_id);
	            $("#coupon_amount").val(offer_amount);
	            $("#coupon_type").val(resp.coupon_details.coupon_type);
	            $('#promo_code').attr('readonly', true);
	            $("#apply_promocode").hide();
	            $("#remove_promocode").show();
				toastr.success("Coupon applied success", {timeOut: 5000});
				return false;
			},
			error:function(resp)
			{
				console.log('out--'+data);
				return false;
			}
		});
		return false;
	});

	$('#remove_promocode').on('click', function() {
		$(".offer_amount").hide();
        $(".offer_amount_percentage").hide();
		$(".total_pay").hide();
		$("#remove_promocode").hide();
		$("#apply_promocode").show();
		$("#coupon_id").val('');
		$("#coupon_amount").val('');
		$("#promo_code").val('');
		$('#promo_code').attr('readonly', false);
		toastr.success("Coupon removed success");
	});

	/*$("#coupon_code").change(function()
	{
		
		var val =$(this).val();
		var outlet_id =$("#outlet_id").val();
		var customer_id =$("#customer_id").val();
		var token = $('input[name=_token]').val();

		var c_url = '/check_promocode';
		$.ajax({
			url: c_url,
			headers: {'X-CSRF-TOKEN': token},
			data: {coupon_code:val,outlet_id:outlet_id,customer_id:customer_id},
			type: 'POST',
			datatype: 'JSON',
			success: function (resp)
			{   cart_count = $('.cart_row').length;
				$("#fadpage").hide();
				 if(parseInt(cart_count) == 0){
			    toastr.success("<?php echo trans('messages.Your cart is empty Now') ?>");
		               }
				else if(qty == 0){
				toastr.success("<?php echo trans('messages.Cart has been deleted successfully!') ?>");
			       }

				if(resp.total == 0)
				{
					$(".cart_items").hide();
					$(".empty_cart").show();
					location.reload(true);
				}

				$("#sub_total").text(resp.sub_total);
				$("#tax").text(resp.tax_amount);
				$("#total").text(resp.total);


				if(resp.total <= min_order_amount)
                {
                    remaining_amount = min_order_amount - resp.total;
                    <?php if ($currency_side == 1) {?>
                        remaining_amount = '<?php echo $currency_symbol; ?>'+' '+remaining_amount.toFixed(2);
                    <?php } else {?>
                        remaining_amount = remaining_amount.toFixed(2)+' '+'<?php echo $currency_symbol; ?>';
                    <?php }?>
                    $('#proceed_to_checkout').html('<?php echo trans('messages.Add '); ?>'+remaining_amount+'<?php echo trans('messages. to Checkout'); ?>').attr('title','<?php echo trans('messages.Add '); ?>'+remaining_amount+'<?php echo trans('messages. to Checkout'); ?>').attr('disabled', 'disabled');
                }
                 else {
                    $('#proceed_to_checkout').html('<?php echo trans('messages.Proceed to checkout'); ?>').attr('title','<?php echo trans('messages.Proceed to checkout'); ?>').removeAttr('disabled');
                }
				return true;
			},
			error:function(resp)
			{
				console.log('out--'+resp);
				return false;
			}
		});
		return false;
	});*/


	$(".adjust_weightqty").change(function(){
	  
	  	var adj_weight=$(this).val() ;
	  	var org_weight = $(this).parent().parent().find('#weight').val();
	  	var item_total = $(this).parent().parent().find('#tot_org').val();
	  	var item_price = $(this).parent().parent().find('.item_price').html();
	  	var quantity = $(this).parent().parent().find('#quantity').val();
	  	//var adjst_amnt = adj_weight *(item_price/org_weight);
	  	//alert(adj_weight);
	  	var adjst_amnt = item_price/org_weight;
	  	var adjt_amnt = adjst_amnt*adj_weight;
	  	var adjst_amnt = adjt_amnt*quantity;
	  	
	  	//alert(adjst_amnt);return false;

	 	var total = item_price*quantity;
	  	var main_tot = (parseFloat(total)+parseFloat(adjst_amnt)).toFixed(2);
	  




	  	$(this).parent().parent().find('.item_total').text(main_tot);
	  	$(this).parent().parent().find('#item_total').val(main_tot);

	  	/*sub total of product*/
			var arr = $('input[name="item_total[]"]').map(function () {
			    return this.value; // $(this).val()
			}).get();
			  sub_total = 0;
			$.each(arr,function(){sub_total+=parseFloat(this) || 0;});
			
		/*sub total of product*/

		var tax = $("#tax_amount_org").val();
		var service_tax = $("#service_tax").val();
		var delivery_cost = $("#delivery_cost_h").val();
		var tot = $("#total_org").val();
		var main_subtot = (sub_total).toFixed(2);

		var tax_amount = parseFloat(main_subtot *service_tax /100).toFixed(4);

		var main_tot =( parseFloat(main_subtot) + parseFloat(tax_amount) + parseFloat(delivery_cost)).toFixed(4);
		$("#sub_total_h").val(main_subtot);
		$("#sub_total_org").val(main_subtot);
		$("#sub_total").text(main_subtot);
		$("#tax").text(tax_amount);
		$("#tax_amount").val(tax_amount);
		$("#total_h").val(main_tot);
		$("#total").text(main_tot);
	  
	});

	$(".remove_row").on('click',function()
	{
		//alert("dd");return false;
		var item_count = $("#cart_items_count").val();
		if(item_count != 1)
		{
			item_count--;
			//console.log(item_count);return false;

			var quantity = $(this).parent().parent().find('.actual_quantity').val();
			var item_price = $(this).parent().parent().find('.item_price').html();
			var item_total = $(this).parent().parent().find('.item_total').html();
			var sub_tot = $("#sub_total_h").val();
			var service_tax = $("#service_tax").val();
			var delivery_cost = $("#delivery_cost_h").val();
			//alert(delivery_cost);return false;
			var product_price = (parseFloat(sub_tot)-parseFloat(item_total)).toFixed(2);
			var tax_amount = parseFloat(product_price *service_tax /100).toFixed(4);
			//alert(tax_amount);alert(product_price);return false;

			var main_tot = (parseFloat(product_price)+parseFloat(tax_amount)+parseFloat(delivery_cost)).toFixed(2);

			$(this).closest("tr").remove();
			$("#sub_total_h").val(product_price);
			$("#sub_total").text(product_price);
			$("#tax_amount").val(tax_amount);
			$("#tax").text(tax_amount);
			$("#total").text(main_tot);
			$("#total_h").val(main_tot);
			$("#cart_items_count").val(item_count);


		}else{
			alert("you cannot delete whole items from order or else you can cancel the order ");
		}
	})

	$('#edit_info').show();

	var min_order_amount = '<?php echo $minimum_order_amount; ?>';
	$('.qty_increase1, .qty_decrease1,.delete_item1').on('click', function()
	{

		if($(this).hasClass('delete_item1')) // for remove item
		{
			if(confirm("Are you sure want to delete?"))
			{

			}
			else
			{
				return false;
			}

			cart_id = $(this).parent().parent().attr("data-cart_id");
			cart_detail_id = $(this).parent().parent().attr("data-cart_detail_id");
			qty = 0;
		}
		else
		{
			x =0;
			cart_id = $(this).parent().parent().parent().parent().attr("data-cart_id");
			cart_detail_id = $(this).parent().parent().parent().parent().attr("data-cart_detail_id");
			qty = $(this).parent().find('.actual_quantity').val();


			if ($(this).hasClass('qty_increase1'))
			{   
			   //$('.qty_decrease1'+cart_id).removeAttr('disabled');
			   $(this).parent().parent().find('.qty_decrease1').removeAttr('disabled')
				qty = parseInt(qty)+1;

			}
			else
			{
				if(qty == 1)
					{
					$(this).parent().parent().find('.qty_decrease1').attr('disabled', 'disabled');
					//qty=0;
					x = 1;
					//$('.qty_decrease1'+cart_id).attr('disabled', 'disabled');
					}
					else{
				qty = parseInt(qty)-1;
			}

			}
		}
		//update_cart_1(cart_detail_id,cart_id,qty);
		if(qty == 0)
		{
			if($(this).hasClass('delete_item1'))
			{
				 $(this).parent().parent().remove();
				 cart_count = $('.cart_row').length;
				 $('.cart_total_count').html('('+cart_count+')');
				if(parseInt(cart_count) == 0)
				{
					$('.cart_items').hide();
					$('.empty_cart').css("display", "block");

				}
				return false;

			}
			$(this).parent().parent().parent().parent().parent().parent().remove();
			cart_count = $('.cart_row').length;
			 $('.cart_total_count').html('('+cart_count+')');
			if(parseInt(cart_count) == 0)
			{
				$('.cart_items').hide();
				$('.empty_cart').css("display", "block");
			}
			return false;
		}
		else if(x == 0)
		{
			/*sub total of product*/
			var arr = $('input[name="item_total[]"]').map(function () {
			    return this.value; // $(this).val()
			}).get();
			  sub_total = 0;
			$.each(arr,function(){sub_total+=parseFloat(this) || 0;});
			
			/*sub total of product*/
			$(this).parent().find('.actual_quantity').val(qty);
			$(this).parent().find('#quantity').val(qty);

			var quantity = $(this).parent().find('.actual_quantity').val();
			var item_price = $(this).parent().parent().parent().parent().find('.item_price').html();

			var adj_weight = $(this).parent().parent().parent().parent().find('#adjust_weight_qty').val();
			var org_weight = $(this).parent().parent().parent().parent().find('#weight').val();
			var item_total = $(this).parent().parent().parent().parent().find('#item_total').val();
			var adjust_weight_qty = adj_weight *(item_price/org_weight);
			var delivery_cost = $("#delivery_cost_h").val();
			var action= $(this).attr('class');
			//var tax = $("#tax_amount").val();
			var service_tax = $("#service_tax").val();
			//var sub_tot = $("#sub_total_h").val();
			var tot = $("#total_h").val();




	  	
		  	var adjst_amnt = adj_weight *(item_total/org_weight);
		  	var total = item_price*quantity;
		  	var main_tot = (parseFloat(total)+parseFloat(adjst_amnt)).toFixed(2);
		  	//alert(main_tot);
	  	




			if(action == 'pluse_number qty_increase1') {
				item_total = (parseFloat(item_total) + parseFloat(adjust_weight_qty)+ parseFloat(item_price)).toFixed(2);

				main_subtot = (parseFloat(item_price)+parseFloat(sub_total)+parseFloat(adjust_weight_qty)).toFixed(2);
				main_tot = (parseFloat(item_price)+parseFloat(tot)).toFixed(2);
			}else{

				item_total = (parseFloat(item_total) - parseFloat(adjust_weight_qty) - parseFloat(item_price)).toFixed(2);
				main_subtot = (parseFloat(sub_total)-parseFloat(item_price)-parseFloat(adjust_weight_qty)).toFixed(2);
				main_tot = (parseFloat(tot)-parseFloat(item_price)).toFixed(2);
			}
			var tax_amount = parseFloat(main_subtot *service_tax /100).toFixed(4);
			var main_tot =( parseFloat(main_subtot) + parseFloat(tax_amount) + parseFloat(delivery_cost)).toFixed(4);
			
			$(this).parent().parent().parent().parent().find('.item_total').text(item_total);
			$(this).parent().parent().parent().parent().find('#item_total').val(item_total);
			$("#sub_total_h").val(main_subtot);
			$("#sub_total_org").val(main_subtot);
			$("#sub_total").text(main_subtot);
			$("#total_h").val(main_tot);
			$("#total_org").val(main_tot);
			$("#total").text(main_tot);
			$("#tax").text(tax_amount);
			$("#tax_amount_org").val(tax_amount);
			$("#tax_amount").val(tax_amount);
		}

	});
	function update_cart_1(cart_detail_id,cart_id,qty)
	{
		$("#fadpage").show();
		var c_url = '/update-cart';
		token = $('input[name=_token]').val();
		$.ajax({
			url: c_url,
			headers: {'X-CSRF-TOKEN': token},
			data: {cart_detail_id:cart_detail_id,cart_id:cart_id,qty:qty},
			type: 'POST',
			datatype: 'JSON',
			success: function (resp)
			{  console.log(resp);return false;

				if(resp.total == 0)
				{
					$(".cart_items").hide();
					$(".empty_cart").show();
					location.reload(true);
				}

				$("#sub_total").text(resp.sub_total);
				$("#tax").text(resp.tax_amount);
				$("#total").text(resp.total);


				if(resp.total <= min_order_amount)
                {
                    remaining_amount = min_order_amount - resp.total;
                    <?php if ($currency_side == 1) {?>
                        remaining_amount = '<?php echo $currency_symbol; ?>'+' '+remaining_amount.toFixed(2);
                    <?php } else {?>
                        remaining_amount = remaining_amount.toFixed(2)+' '+'<?php echo $currency_symbol; ?>';
                    <?php }?>
                    $('#proceed_to_checkout').html('<?php echo trans('messages.Add '); ?>'+remaining_amount+'<?php echo trans('messages. to Checkout'); ?>').attr('title','<?php echo trans('messages.Add '); ?>'+remaining_amount+'<?php echo trans('messages. to Checkout'); ?>').attr('disabled', 'disabled');
                }
                 else {
                    $('#proceed_to_checkout').html('<?php echo trans('messages.Proceed to checkout'); ?>').attr('title','<?php echo trans('messages.Proceed to checkout'); ?>').removeAttr('disabled');
                }
				return true;
			},
			error:function(resp)
			{
				console.log('out--'+resp);
				return false;
			}
		});
		return false;
	}
	function proceed_to_checkout(checkout)
    {
        if($('#proceed_to_checkout').is('[disabled=disabled]'))
        {}
        else
        {
            user_id = "<?php echo Session::get('user_id'); ?>";
            if(user_id == "")
            {
                $('.cart_dyn_sec').modal('hide');
                $('#myModal2').modal('show');
                return false;
            }
            else {
                window.location.href='{{url("checkout")}}';
            }
        }
    }
    $("#cart_sub").on('click', function()
	{	
		var postURL = "/update_cart";

		var form = $(this).closest('form');
    	var serialize = form.serialize();
		//console.log(serialize);return false;
		 $.ajax({
                url:postURL,
                method:"POST",
                data:serialize,
                type:'json',
                success:function(data)
                {
                  alert(data );
                }
           });
		///alert(serialize);return false;
	});




});

$(window).load(function(){
	$('form').preventDoubleSubmission();
});


</script>




@endsection
