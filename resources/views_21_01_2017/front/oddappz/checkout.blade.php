@extends('layouts.app')
@section('content')
    <!-- container start -->
	 <div id="overlay">
        <div id="progstat"></div>
        <div id="progress"></div>
      </div>
    <section class="store_item_list">
        <div class="container">
            <div class="row">
			<?php $currency_side = getCurrencyPosition()->currency_side;$currency_symbol = getCurrency(); ?>
			<?php // echo "<pre>"; print_r($checkout_details); echo "</pre>"; ?>
			{!!Form::open(array('url' => ['proceed-checkout'], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'checkout_form'));!!}
                <div class="checkout_sec">
                    <div class="col-md-8">
                        <div class="checkout_inf">
                            <h2 class="check_title">@lang('messages.Checkout')</h2>
							 <div class="form-group" id="errors"> </div>
                            <div class="deliver_and_pickup">
                                <div class="gender_section">
                                    <div class="col-md-12">
                                            <label class="label_radio delivery_lab" for="radio-02">
                                            <input name="order_type" class="delivery" checked id="radio-02" value="1" type="radio" /> @lang('messages.Delivery')</label>
                                            <label class="label_radio pickup_lab" for="radio-03">
                                            <input name="order_type" class="pickup" id="radio-03" value="2" type="radio" />@lang('messages.Pickup')</label>
                                    </div>
									<div id="delivery_info" class="col-md-9 col-sm-8 col-xs-12 deliver_optins_button">
                                        <div class="add_new_addres pick_address">
											<input type="hidden" id="outlet_lat" name="outlet_lat" value="<?php echo $checkout_details->outlet_detail->latitude;?>">
											<input type="hidden" id="outlet_lon" name="outlet_lon" value="<?php echo $checkout_details->outlet_detail->longitude;?>">	
											<?php
											if(count($checkout_details->address_list)>0)
											{?>
												<select name="delivery_address" class="delivery_address" id="delivery_address">
												<option value="">@lang('messages.Select address')</option>
												<?php foreach($checkout_details->address_list as $address){ ?>
												<option value="{{$address->address_id}}" data-latitude="{{$address->latitude}}" data-longtitude="{{$address->longtitude}}" >{{$address->address_type}}, {{$address->address}}</option>
												
											<?php }?>
											</select>
											<?php foreach($checkout_details->address_list as $address){ ?>
											<input type="hidden" value="{{$address->latitude}}" id="address_latitude{{$address->address_id}}">
											<input type="hidden" value="{{$address->longtitude}}" id="address_longtitude{{$address->address_id}}">
											<?php } ?>
											<?php } else {  ?>
											<input name="delivery_address" class="delivery_address" id="delivery_address" value="" type="hidden" />
											<?php } ?>
                                        </div>
                                    </div>
									<div id="pickup_info" class="col-md-9 col-sm-8 col-xs-12" style="display:none">
                                        <div class="ext_address">
											<div class="col-md-10 padding_left0">
												<h3>@lang('messages.You will pickup by yourself at'),</h3>
												<address>
													<p>{{$checkout_details->outlet_detail->contact_address}}</p>
												</address>
											</div>
                                        </div>
                                    </div>
                                    <div class="col-md-9 col-sm-8 col-xs-12 add_address">
                                        <div class="add_new_addres">
											<a class="btn btn-default btn-lg" href="javascript:;" id="add_new_address" title="@lang('messages.Add new addresss')">@lang('messages.Add new addresss')<i class="glyph-icon flaticon-arrow-point-to-right"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-9 col-sm-8 col-xs-12">
                                        <div class="add_new_addres">
                                            <textarea name="delivery_instructions" required placeholder="@lang('messages.Delivery instructions')" class="form-control" rows="5" id="comment"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="time_slat time_slat_info">
                            <h3 class="del_slt_title">@lang('messages.Delivery Slot')</h3>
                            <div class="col-md-9 col-sm-8 col-xs-12">
                                <div class="list_of_deliver">
                                    <a id="click_button" href="javascript:;" title=""><span id="slot_text"></span> 
									<span class="deliver_icon"><i class="glyph-icon flaticon-down-arrow-1"></i></span></a>
									<input id="delivery_slot_id" name="delivery_slot" type="hidden">
									<input id="delivery_date" name="delivery_date" type="hidden">
                                    <div class="deliver_slat_scetions">
                                        <div class="deliver_slat_innr">
                                            <table class="responsive_table">
                                                <thead>
                                                    <tr>
                                                        <th>@lang('messages.Day') / @lang('messages.Date')</th>
                                                        <th class="c-align" colspan="3">@lang('messages.Time slots')</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
													<?php
													$week = $checkout_details->week;
													foreach($checkout_details->weekOfdays as $key=>$days) { ?>
                                                    <tr>
                                                        <td>{{$days}} <?php echo ($key==1)?trans('messages.Today'):trans($week->$key);?></td>
                                                        <?php 	
															foreach($checkout_details->delivery_slot_array as $key1 =>$times)
															{
																if($days == $key1) 
																{
																	foreach($times as $time)
																	{
																		$slot_class = "available slot_available";
																		if($time->slot == 0)
																		{
																			$slot_class = "not_available";
																		}
																		?>
																			<td 
																			data-slot_id ="{{$time->slot}}"
																			data-delivery_date ="{{$time->date}}"
																			data-value="{{$days}} <?php echo ($key==1)?trans('messages.Today').' '.$time->time:trans($week->$key).' '.$time->time;?>" 
																			class="{{$slot_class}}">{{$time->time}}</td>
																		<?php 
																	}
																}
															}
														?>
                                                    </tr>
													<?php } ?>
                                                </tbody>
                                            </table>
                                            <div class="botton_select">
                                                <ul>
                                                    <li>
                                                        <p><span class="dark_green"></span>@lang('messages.Restricted')</p>
                                                    </li>
                                                    <li>
                                                        <p><span class="light_green"></span>@lang('messages.Not available')</p>
                                                    </li>
                                                    <li>
                                                        <p><span class="green_green"></span>@lang('messages.Selected')</p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="time_slat checkout_info">
                            <h3 class="del_slt_title">@lang('messages.Payment method')</h3>
                            <div class="col-md-12 col-sm-8 col-xs-12">
                                    <div class="method_sec">
										<?php foreach($checkout_details->gateway_list as $gateways){ ?>
										  <div class="method_sec_list_se">
										  <div class="col-md-4 padding_left0">
												<label class="label_radio" for="radio-{{$gateways->payment_gateway_id}}">
												<input name="payment_gateway_id" required id="radio-{{$gateways->payment_gateway_id}}" value="{{$gateways->payment_gateway_id}}" type="radio" />{{ ucfirst($gateways->name) }}</label>
												 </div>
												 </div>
										<?php } ?>
                                    </div>
                                   
                                    <div class="price_buttons responsive_hidde">
                                        <input type="button" id="checkout_submit" class="btn btn-default btn-lg" title="Proceed" value="@lang('messages.Proceed')">
                                    </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="side_bar">
                            <div class="checkout_inf">
                                <h3 class="del_slt_exp">
								<span class="ex_logo">									
									<img src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/pay_method.png'); ?>" title="@lang('messages.Pay')" alt="@lang('messages.Pay')">
								</span>
								<span class="exp_sec">@lang('messages.express')</span></h3>
                                <div class="col-md-8 col-sm-6 col-xs-6">
                                    <p>@lang('messages.Estimated delivery time')</p>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-6 minuts">
                                    <b>{{ $checkout_details->outlet_detail->delivery_time }}</b>
									<span>@lang('messages.Mins')</span>
									<br/>                                   
                                </div>
                            </div>
                            <div class="checkout_inf">
                                <h3 class="del_slt_exp">@lang('messages.Your order')</h3>
								<?php foreach($checkout_details->cart_items as $items){ ?>
								   <div class="list_order">
                                    <div class="col-md-8 col-sm-6 col-xs-6">
                                        <h4>{{ucfirst(strtolower($items->product_name))}}</h4>
                                    </div>
                                    <div class="col-md-4 col-sm-6 col-xs-6 minuts">
									<?php if($currency_side == 1) { ?>
                                            <h4><span class="item_total">{{$currency_symbol}}{{$items->discount_price*$items->quantity}}</span></h4>
										<?php } else { ?>
										   <h4><span class="item_total">{{$items->discount_price*$items->quantity}}</span>{{$currency_symbol()}}</h4>
										<?php } ?>
                                    </div>
									</div>
								<?php } ?>
                                <div class="list_order">
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <h5>@lang('messages.Subtotal')</h5>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6 minuts">
<div class="sub_tot_info">               
                                      <?php if($currency_side == 1) { ?>                        
									      <h5>{{$currency_symbol}}<span id="sub_total">{{$checkout_details->sub_total}}</span></span></h5>
									   <?php } else { ?>
									       <h5><span id="sub_total">{{$checkout_details->sub_total}}</span>{{$currency_symbol}}</span></h5>
										<?php } ?>
									   </div>
                                    </div>
                                </div>
                                <div class="list_order delivery_cost_info">
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <h6>@lang('messages.Delivery fee')</h6>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6 minuts">
<div class="sub_tot_info">               <?php if($currency_side == 1) { ?>                        
									   <h6>
										     {{ $currency_symbol }} <span id="delivery_cost">{{$checkout_details->delivery_cost}}</span>
										      <input type="hidden" name="delivery_cost" value="{{$checkout_details->delivery_cost}}" id="delivery_cost_hid">
										 </h6>
									<?php } else { ?>
										  <h6>
										   <span id="delivery_cost">{{$checkout_details->delivery_cost}}</span>{{$currency_symbol}}<input type="hidden" name="delivery_cost" value="{{$checkout_details->delivery_cost}}" id="delivery_cost_hid">
										
										</h6>
										<?php } ?>
										</div>
                                    </div>
                                </div>
                                <div class="list_order">
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <h5>@lang('messages.tax')</h5>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6 minuts">
<div class="sub_tot_info">              <?php if($currency_side == 1) { ?>                         
									       <h5>{{$currency_symbol}}<span id="tax">{{$checkout_details->tax}}</span></span></h5>
									  <?php } else { ?>
									      <h5><span id="tax">{{$checkout_details->tax}}</span>{{$currency_symbol}}</span></h5>
									<?php } ?>
									  </div>
                                    </div>
                                </div>
                                <div class="list_order border">
                                    <div class="col-md-5 col-sm-6 col-xs-6">
                                        <h5>@lang('messages.Total')</h5>
                                    </div>
                                    <div class="col-md-7 col-sm-6 col-xs-6 minuts">
									 <?php if($currency_side == 1) { ?> 
                                        <b>{{$currency_symbol}}<span id="total">{{$checkout_details->total}}</span></b>
										  <?php } else { ?>
										  <b><span id="total">{{$checkout_details->total}}</span>{{$currency_symbol}}</b>
										  <?php } ?>
                                    </div>
                                </div>
								
								<div class="list_order offer_amount" style="display:none;">
                                    <div class="col-md-8 col-sm-6 col-xs-6">
                                        <h5>@lang('messages.Coupon discount')</h5>
                                    </div>
                                    <div class="col-md-4 col-sm-6 col-xs-6 minuts">
									<?php if($currency_side == 1) { ?>
                                        <b>{{$currency_symbol}}<span id="offer_amount"></span></b>
										 <?php } else { ?>
									 <b><span id="offer_amount"></span>{{$currency_symbol}}</b>
                                   <?php } ?>									 
                                    </div>
                                </div>
								<div class="list_order total_pay" style="display:none;">
                                    <div class="col-md-8 col-sm-6 col-xs-6">
                                        <h5>@lang('messages.Amount to pay')</h5>
                                    </div>
                                    <div class="col-md-4 col-sm-6 col-xs-6 minuts">
									<?php if($currency_side == 1) { ?>
                                        <b>{{$currency_symbol}}<span id="total_pay"></span></b>
										<?php } else { ?>
										  <b><span id="total_pay"></span>{{$currency_symbol}}</b>
										 <?php } ?>	  
										<input type="hidden" name="coupon_id" id="coupon_id">
										<input type="hidden" name="coupon_amount" id="coupon_amount">
                                    </div>
                                </div>
                               <div class="coupon_code">
									<div class="col-md-8 col-sm-6 col-xs-8 padding_right0">
										<?php $module = modules_list(); if($module->module_name == 'Offer' && $module->active_status == 1) { ?>
											<input type="text" autocomplete="off" id="promo_code" class="search-query form-control" placeholder="@lang('messages.Promo code')" /></div>
											<div class="col-md-4 col-sm-4 col-xs-4">
												<button class="btn btn-danger" type="button" id="apply_promocode" title="@lang('messages.Apply')" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> @lang('messages.Processing')">@lang('messages.Apply')</button>
												<button style="display:none" class="btn btn-danger" type="button" id="remove_promocode" title="@lang('messages.Remove')">@lang('messages.Remove')</button>
											</div>
											<p class="col-md-12">* @lang('messages.Please enter the valid promo code here')</p>
										<?php } ?>
										<div class="price_buttons responsive_show">
											<form>
												<input type="button" id="checkout_submit1" class="btn btn-default btn-lg" title="Proceed" value="@lang('messages.Proceed')">
											</form>
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
		@include('front.'.Session::get("general")->theme.'.send_otp')
	</section>
	<script type="text/javascript" src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/js/jquery.validate.min.js');?>"></script>
	<script type="text/javascript">
		$('select').select2();
		$(document).ready(function() 
		{
			
			
			
			
			$('#click_button').on('click', function() 
			{
				$(".deliver_slat_scetions").toggle();
			});
			$('#add_new_address').one('click', function() 
			{
				$('#address_model').modal('show');
			});
			$('#address_model').on('shown.bs.modal', function(){
				generate_map('<?php echo $checkout_details->outlet_detail->latitude;?>', '<?php echo $checkout_details->outlet_detail->longitude?>');
			});
			
			$('#remove_promocode').on('click', function() 
			{
				$(".offer_amount").hide();
				$(".total_pay").hide();
				$("#remove_promocode").hide();
				$("#apply_promocode").show();
				$("#coupon_id").val('');
				$("#coupon_amount").val('');
				$("#promo_code").val('');
				$('#promo_code').attr('readonly', false);
				toastr.success("Coupon removed success");
			});
			
			$('#apply_promocode').on('click', function() 
			{
				//otp = $("#otp").val();
				var promo_code = $('#promo_code').val();
				if(promo_code == "")
				{
					toastr.warning("<?php echo trans('messages.Please fill promo code') ?>");
					return false;
				}
				var $this = $("#apply_promocode");
				$this.button('loading');
				//$("#fadpage").show();
				
				var c_url = '/update-promcode';
				token = $('input[name=_token]').val();
				$.ajax({
					url: c_url,
					headers: {'X-CSRF-TOKEN': token},
					data: {promo_code:promo_code},
					type: 'POST',
					datatype: 'JSON',
					success: function (resp) 
					{
						 $this.button('reset');
						//$("#fadpage").hide();
						var total = $("#total").text();
						var total_pay = "";
						if(resp.httpCode == 400)
						{
							toastr.error("<?php echo trans('messages.No coupons found') ?>");
							return false;
						}
						if(resp.coupon_details.offer_type == 1)
						{
							var total_pay = parseFloat(total - resp.coupon_details.offer_amount);
						}
						else
						{
							total_pay = parseFloat(total - (total*resp.coupon_details.offer_amount)/100)
						}
						$("#total_pay").text(total_pay);
						$(".offer_amount").show();
						$(".total_pay").show();
						$("#offer_amount").text(resp.coupon_details.offer_amount);
						$("#coupon_id").val(resp.coupon_details.coupons_id);
						$("#coupon_amount").val(resp.coupon_details.offer_amount);
						$('#promo_code').attr('readonly', true);
						$("#apply_promocode").hide();
						$("#remove_promocode").show();
						toastr.success("Coupon applied success");
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
			$("#checkout_form").validate({
				errorClass: "my-error-class",
				rules: {
					order_type: "required",
					delivery_address: {
					required: {
						depends: function(element) {
							//*alert($("input[name=order_type]:checked").val()); */
							if($("input[name=order_type]:checked").val() == 1) return true;
							else return false;
						}
					}
					},
					delivery_instructions: "required",
					payment_gateway_id: "required",
					
				},
				messages: {
					order_type: "<?php echo trans ('messages.Order type is required') ?>",
					delivery_address: "<?php echo trans ('messages.Please add delivery address') ?>",
					delivery_instructions: "<?php echo trans ('messages.Please add delivery instructions') ?>",
					payment_gateway_id: "<?php echo trans ('messages.Please select payment type') ?>",

				},
				
				errorElement: "div",
				//place all errors in a <div id="errors"> element
				errorPlacement: function(error, element) {
					//error.appendTo("div#errors");
					toastr.error(error);
				}, 
				submitHandler: function(form) {
					$('#send_otp').modal('show');
				},
				 ignore: []
			});
			$('#checkout_submit, #checkout_submit1').on('click', function() 
			{
				valid =  $("#checkout_form").valid();
				if(valid == true)
				{
					var payment_id = $('.method_sec input:radio:checked').val();
					//alert(payment_type);
					//return false;
					if(payment_id == 18)
					{
						$('#send_otp').modal('show');
						return false;
					}
					else
					{
						$( "#checkout_form" )[0].submit();
					}
				}
				return false;
			});
			
			$('.delivery_address').on('change', function() 
			{
				
				<?php if($checkout_details->delivery_settings->delivery_type == 1){	?>
					var address_id = ($(this).val());
					if(address_id != "")
					{
						var latitude_a = $("#address_latitude"+address_id).val();
						var longtitude_a = $("#address_longtitude"+address_id).val();
						
						var out_latitude = $("#outlet_lat").val();
						var out_longtitude = $("#outlet_lon").val();
						var origin1 = {lat: parseFloat(latitude_a), lng: parseFloat(longtitude_a)};
						var destinationB = {lat: parseFloat(out_latitude), lng: parseFloat(out_longtitude)};
						/*console.log(origin1);
						console.log(destinationB);*/
						var distanceService = new google.maps.DistanceMatrixService();
						distanceService.getDistanceMatrix({
							origins: [origin1],
							destinations: [destinationB],
							travelMode: google.maps.TravelMode.DRIVING,
							unitSystem: google.maps.UnitSystem.METRIC,
							durationInTraffic: true,
							avoidHighways: false,
							avoidTolls: false
						},
						function (response, status) 
						{
							if (status !== google.maps.DistanceMatrixStatus.OK) {
								console.log('Error:', status);
							} 
							else 
							{
								
								if(response.rows[0].elements[0].status == "ZERO_RESULTS")
								{
									toastr.warning("<?php echo trans('messages.Sorry we are not there!') ?>");
								}
							
								console.log(response);
								console.log(response.rows[0].elements[0].distance.text);
								distance = response.rows[0].elements[0].distance.text;
								distance_km = distance.split(' ')[0];
								if(distance_km > 5)
								{
									distance_km_minus_five = distance_km-5;
									console.log(distance_km_minus_five);
									console.log(<?php echo $checkout_details->delivery_settings->delivery_cost_variation;?>);
									var delivery_cost = <?php echo $checkout_details->delivery_cost;?>;
									var total =<?php echo $checkout_details->total;?>;
									new_delivery_cost = (<?php echo $checkout_details->delivery_settings->delivery_cost_variation;?>*distance_km_minus_five)+delivery_cost;
									new_total = (total+new_delivery_cost)-delivery_cost;
									$("#delivery_cost").text(new_delivery_cost);
									$("#delivery_cost_hid").val(new_delivery_cost);
									$("#total").text(new_total);
								}
							}
						});
					}
				<?php } ?>
				return false;
			});
			
		});
	</script>
	<!-- container end -->
	@include('front.'.Session::get("general")->theme.'.new_address_pop')   
	@endsection
