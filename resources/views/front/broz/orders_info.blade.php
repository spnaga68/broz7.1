@extends('layouts.app')
@section('content')
<?php $general = Session::get("general");?>
<script src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/plugins/rateit/src/jquery.rateit.js');?>"></script>
<link href="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/plugins/rateit/src/rateit.css');?>" rel="stylesheet">
<?php $currency_side = getCurrencyPosition()->currency_side;$currency_symbol = getCurrency(getCurrentLang()); ?>
   <section class="store_item_list">
        <div class="container">
            <div class="row">
                <div class="my_account_section">
                    <div id="parentHorizontalTab">
                        <div class="col-md-3">
                            @include('front.'.Session::get("general")->theme.'.profile_sidebar')
                        </div>
                        <div class="col-md-9">
                            <div class="right_descript">
                                <div class="resp-tabs-container hor_1">
                                    <div class="profile_sections">
                                        <h2 class="pay_title">@lang('messages.Order Summary')</h2>
                                        <?php if($user_fav->count == 0 && $vendor_info->order_status=="12"){ ?>
											<div class="col-md-12">
												<div class="order_payments_reorder">
													<?php $order_id = encrypt($vendor_info->outlet_id); ?>
													<a class="btn btn-info" data-toggle="modal" data-target="#myModal3" title="@lang('messages.Review')">@lang('messages.Write review')</a>
												</div>
											</div>
										<?php } ?>
                                        <div class="stores_det_info">
                                            <div class="col-md-7">
                                                <div class="store_det_in">
												<img  width="161px" height="107px" alt="{{ ucfirst($vendor_info->vendor_name) }}"  src="<?php echo $vendor_info->logo_image.'?'.time(); ?>" >
                                                    <p>{{ $vendor_info->vendor_name  }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-5 store_mg">
                                                <div class="info_store">
                                                    <div class="col-md-6 col-ms-6 col-xs-6">
                                                        <h4>@lang('messages.Order Id')</h4></div>
                                                    <div class="col-md-6 col-ms-6 col-xs-6">
                                                        <h5>{{ $vendor_info->order_key_formated  }}</h5></div>
                                                </div>
                                                <div class="info_store">
                                                    <div class="col-md-6 col-ms-6 col-xs-6">
                                                        <h4>@lang('messages.Date')</h4></div>
                                                    <div class="col-md-6 col-ms-6 col-xs-6">
                                                        <h5>{{ date('d M, Y', strtotime($vendor_info->created_date)) }}</h5></div>
                                                </div>
                                                <div class="info_store">
                                                    <div class="col-md-6 col-ms-6 col-xs-6">
                                                        <h4>@lang('messages.Order Status')</h4></div>
                                                    <div class="col-md-6 col-ms-6 col-xs-6">
                                                        <h5><?php echo $vendor_info->name; ?></h5></div>
                                                </div>
                                                <?php if($vendor_info->order_status==17 && (count($return_orders_result)>0)){?>
                                                <div class="info_store">
                                                    <div class="col-md-6 col-ms-6 col-xs-6">
                                                        <h4>@lang('messages.Return Status')</h4></div>
                                                    <div class="col-md-6 col-ms-6 col-xs-6">
                                                        <h5><?php echo $return_orders_result->return_action_name; ?></h5></div>
                                                </div>
                                                <div class="info_store">
                                                    <div class="col-md-6 col-ms-6 col-xs-6">
                                                        <h4>@lang('messages.Return Action')</h4></div>
                                                    <div class="col-md-6 col-ms-6 col-xs-6">
                                                        <h5><?php echo $return_orders_result->return_status_name; ?></h5></div>
                                                </div>
                                                <?php } ?>
                                            </div>
                                        </div>


                                        <div class="my_account_sections">
                                            <div class="table-responsive">
                                                <div class="cart_sections_tables">
                                                    <div class="table-responsive">
                                                       
<div class="responsive_scroll">
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th colspan="4" style="text-align:left;">@lang('messages.Bill details')</th>
                                                                    <th></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
												<?php $sub_total = 0; foreach($order_items as $items){ ?>
                                                                <tr>
                                                                    <td style="text-align:left;">
                                                                        <a href="javascript:;" title="{{ ucfirst(strtolower($items->product_name)) }}">
																		<img src="<?php echo $items->product_image.'?'.time(); ?>" title="{{ $items->product_name }}">
																		{{ str_limit(ucfirst(strtolower($items->product_name)),30) }}
																		</a>
                                                                    </td>
																	<?php if($currency_side == 1) { ?>
                                                                    <td>{{$currency_symbol.$items->item_cost}}</td>
																		<?php } else { ?>
																		 <td>{{$items->item_cost.$currency_symbol}}</td>
																		 <?php } ?>
                                                                    <td>{{$items->item_unit}}</td>
																	<?php if($currency_side == 1) { ?>
                                                                         <td valign="middle">{{($currency_symbol.$items->item_cost*$items->item_unit)}}</td>
																	<?php } else {  ?>
																	      <td valign="middle">{{($items->item_cost*$items->item_unit).$currency_symbol}}</td>
																	<?php } ?>	  
                                                                </tr>
                                                      <?php $sub_total += $items->item_cost*$items->item_unit; } ?>          
                                                                
                                                            </tbody>
                                                        </table>
														</div>
                                                        <div class="price_setions">
                                                            <div class="price_setions_list">
                                                                <div class="col-md-7 dis_none"></div>
                                                                <div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.Subtotal')</label></div>
																<?php if($currency_side == 1) { ?>
                                                                     <div class="col-md-2 col-sm-6 col-xs-6">
                                                                         <p>{{$currency_symbol.$sub_total}}</p>
                                                                    </div>
																<?php } else {  ?>
																     <div class="col-md-2 col-sm-6 col-xs-6">
                                                                         <p>{{$sub_total.$currency_symbol}}</p>
                                                                    </div>
																<?php } ?>		
                                                            </div>

                                                            <?php  if($delivery_details[0]->order_type==1){ ?>
                                                            <div class="price_setions_list">
                                                                <div class="col-md-7 dis_none"></div>
                                                                <div class="col-md-3 col-sm-6 col-xs-6"><label class="color_sec">@lang('messages.Delivery fee')</label></div>
																<?php if($currency_side == 1) { ?>
                                                                <div class="col-md-2 col-sm-6 col-xs-6">
                                                                    <p class="color_sec">{{$currency_symbol.$delivery_details[0]->delivery_charge}}</p>
                                                                </div>
																<?php } else {  ?>
																<div class="col-md-2 col-sm-6 col-xs-6">
                                                                    <p class="color_sec">{{$delivery_details[0]->delivery_charge.$currency_symbol}}</p>
                                                                </div>
															<?php } ?>		
                                                            </div>
                                                            <?php } ?>

			
                                                    <div class="price_setions_list">
                                                                <div class="col-md-7 dis_none"></div>
                                                                <div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.tax')</label></div>
																<?php if($currency_side == 1) { ?>
                                                                <div class="col-md-2 col-sm-6 col-xs-6">
                                                                    <p>{{$currency_symbol.$delivery_details[0]->service_tax}}</p>
                                                                </div>
																<?php } else {  ?>
																<div class="col-md-2 col-sm-6 col-xs-6">
                                                                    <p>{{$delivery_details[0]->service_tax.$currency_symbol}}</p>
                                                                </div>
																<?php } ?>	
                                                            </div>
															
															<?php if($delivery_details[0]->coupon_amount != 0) { ?>
																<div class="price_setions_list">
																	<div class="col-md-7 dis_none"></div>
																	<div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.Promo code discount')</label></div>
																	<?php if($currency_side == 1) { ?>
																	   <div class="col-md-2 col-sm-6 col-xs-6"><p>{{$currency_symbol.$delivery_details[0]->coupon_amount}}</p></div>
																	<?php } else {  ?>
																	   <div class="col-md-2 col-sm-6 col-xs-6"><p>{{$delivery_details[0]->coupon_amount.$currency_symbol}}</p></div>
																	<?php } ?>
																</div>
															<?php } ?>
                                                            <div class="price_setions_list_total">
                                                                <div class="col-md-7 dis_none"></div>
                                                                <div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.Total')</label></div>
																<?php if($currency_side == 1) { ?>
                                                                <div class="col-md-2 col-sm-6 col-xs-6">
                                                                    <p>{{$currency_symbol.$delivery_details[0]->total_amount}}</p>
                                                                </div>
																<?php } else {  ?>
																<div class="col-md-2 col-sm-6 col-xs-6">
                                                                    <p>{{$delivery_details[0]->total_amount.currency_symbol}}</p>
                                                                </div>
																<?php } ?>
                                                            </div>

                                                        </div>
 
                                                        <div class="delivery_det">

															<?php if($delivery_details[0]->order_type == 1){ ?>
                                                            <div class="deli_list">
                                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                                    <h4>@lang('messages.Delivery address')</h4>
                                                                </div>
                                                                <div class="col-md-9 col-sm-6 col-xs-6 padding_right0">
                                                                    <h4>@if($delivery_details[0]->user_contact_address){{$delivery_details[0]->user_contact_address}} @else  &nbsp; @endif</h4>
                                                                </div>
                                                            </div>
                                                            <div class="deli_list">
                                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                                    <h4>@lang('messages.Delivery slot')</h4>
                                                                </div>
                                                                <div class="col-md-9 col-sm-6 col-xs-6 padding_right0">
																<?php $delivery_date = date("d F, l", strtotime($delivery_details[0]->delivery_date)); 
																$delivery_time = date('g:i a', strtotime($delivery_details[0]->start_time)).'-'.date('g:i a', strtotime($delivery_details[0]->end_time));?>
															<h4>{{ $delivery_date ." : ". $delivery_time}}</h4>
                                                                </div>
                                                            </div>
                                                            <div class="deli_list">
                                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                                    <h4>@lang('messages.Payment mode')</h4>
                                                                </div>
                                                                <div class="col-md-9 col-sm-6 col-xs-6 padding_right0">
                                                                    <h4>{{ $vendor_info->payment_gateway_name  }}</h4>
                                                                </div>
                                                            </div>
                                                            <div class="deli_list">
                                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                                    <h4>@lang('messages.Delivery mode')</h4>
                                                                </div>
                                                                <div class="col-md-9 col-sm-6 col-xs-6 padding_right0">
                                                                    <h4>@lang('messages.Delivery to your address')</h4>
                                                                </div>
                                                            </div>
                                                            <?php } else { ?>
																<div class="deli_list">
																	<div class="col-md-3 col-sm-6 col-xs-6">
																	<h4>@lang('messages.Pickup address')</h4>
																	</div>
																	<div class="col-md-9 col-sm-6 col-xs-6 padding_right0">
																	<h4>{{$delivery_details[0]->contact_address}}</h4>
																</div>
																
																<div class="deli_list">
                                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                                    <h4>@lang('messages.Payment mode')</h4>
                                                                </div>
                                                                <div class="col-md-9 col-sm-6 col-xs-6 padding_right0">
                                                                    <h4>{{ucfirst(strtolower($vendor_info->payment_gateway_name))  }}</h4>
                                                                </div>
                                                            </div>
                                                            <div class="deli_list">
                                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                                    <h4>@lang('messages.Delivery mode')</h4>
                                                                </div>
                                                                <div class="col-md-9 col-sm-6 col-xs-6 padding_right0">
                                                                    <h4>@lang('messages.Pickup directly in store')</h4>
                                                                </div>
                                                            </div>
															<?php } ?>
															
														  <?php  if($delivery_details[0]->order_type==1){ ?>
															<div class="deli_list">
                                                                <div class="col-md-12 col-sm-6 col-xs-6 tracking">
                                                                    <h4>@lang('messages.Tracking')</h4>
                                                                </div>
															</div>
															<div class="deli_list">
                <div class="col-md-12">
                <div class="grap_inner_se">
																	<?php //print_r($tracking_result);
																	/*
                                                                    <ol class="progtrckr" data-progtrckr-steps="5">
																		<?php foreach($tracking_result as $tracks) { ?>
																			<li data-toggle="tooltip" data-placement="top" title="{{$tracks->date}}" class="{{$tracks->class}}">{{trans($tracks->text)}}</li>
																		<?php } ?>
																		
																	</ol>
																	*/?>
																	
																	<ul class="line graph"> 
																		<?php
																		foreach($tracking_result as $key => $tracks) 
																		{
																		?>
																		<li class="unit size1of3 fk-text-center state"> 
																			<ul class="line"> 
																				<?php
																				$class = "inactive ".strtolower($tracks->text);
																				if($tracks->process == "1") 
																				{
																					$class = "processed ".strtolower($tracks->text);
																				}
																				if($key == $last_state) 
																				{
																					$class = "processed-continous ".strtolower($tracks->text); 
																				}
																				?>
																				<li class="order-step {{$class}}" data-state="payments-processed" data-row="{{$key}}" data-index="0"></li> 
																			</ul> 
																		</li> 
																		<?php } ?>
																	</ul>
																	<div class="rposition"> 
																	<?php 
																		$i = 0;
																		foreach($tracking_result as $key=>$tracks) 
																		{
																			$i++;
																			$style = "display:none";
																			//if($i == 1){$style = "display:block";}
																			
																			if($key == $last_state) 
																			{
																				$style = "display:block";
																			}
																		?>
															<div class="granular-info-box steps{{$key}} fk-hidden" style="{{$style}};"> 
																<div class="arrow arrow{{$key}}" style="{{$style}}"></div> 
																<div class="margin5"> 
																	<div class="processed bmargin5 fk-font-normal">
																			<div class="log-name"> {{$tracks->text}}</div> 
																			<div class="log-date">{{$tracks->date}}</div> 
																			<div class="log-comments">{{$tracks->order_comments}}</div>
																	</div> 
																</div> 
															</div>  
																		<?php }?>
																		</div>
                                                                </div>
                                                            </div>
                                                            </div>
														  <?php  } ?>
                                                            <?php $order_id = encrypt($vendor_info->order_id); ?>
                                                            <div class="order_payments">
                                                                <div class="col-md-12 border_top_reor">
                                                                    <div class="order_payments_reorder">
																		<?php $order_id = encrypt($vendor_info->order_id); ?>
																		
																		<?php if(($vendor_info->order_status != 12) &&($vendor_info->order_status != 11)) {?>
																		<a class="btn btn-primary" href="{{URL::to('/cancel-order/'.$order_id)}}" title="@lang('messages.Cancel')">@lang('messages.Cancel')</a>
																		 <?php } 
                                                                         if($vendor_info->order_status==12){ ?>
                                                                            <button class="btn btn-primary return_order"  title="@lang('messages.Return')" type="button">@lang('messages.Return')</button>
                                                                        <?php } ?>
																		<a class="btn btn-primary" target="_blank" href="{{URL::to('/invoice-order/'.$order_id)}}" title="@lang('messages.Invoice')">@lang('messages.Invoice')</a>
																		<?php $order_id = encrypt($vendor_info->order_id); ?>
																		 
																		<a class="btn btn-info" href="{{URL::to('/re-order/'.$order_id)}}" title="@lang('messages.Reorder')">@lang('messages.Reorder')</a>
                                                                    </div> 
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>
    <!-- Modal for membership signIn -->
    <div class="modal fade model_for_signup membership_login" id="myModal3" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
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
                        <h2>@lang('messages.Lets rate the store')<br>
	  <span class="bottom_border"></span>
	  </h2>
                        {!!Form::open(array('url' => 'rating', 'method' => 'post', 'class' => 'tab-form attribute_form', 'id' => 'rating' ,'onsubmit'=>'return rating()'));!!} 
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
							    
                                <input type="hidden" name="outlet_id" value="{{ $vendor_info->outlet_id }}" class="form-control">
                                <input type="hidden" name="user_id" value="{{ Session::get('user_id') }}" class="form-control">
                                <input type="hidden" name="vendor_id" value="{{ $vendor_info->vendor_id }}" class="form-control">
								<input type="hidden" name="language" value="{{ getCurrentLang() }}">
								<input type="hidden" name="order_id" value="{{ $vendor_info->order_id }}" class="form-control">
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
                        <?php /*<div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <label> Title : </label>
                                <input type="text"  name="title" required value="{!! old('title') !!}" class="form-control title"  placeholder="@lang('messages.Title')" value="">
                                <span class="error"> 
                                                @if ($errors->has('title'))
												{{ $errors->first('title', ':message') }}
												@endif
							    </span>
                            </div>
                        </div>*/?>
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
	
	
	
   <div class="modal fade model_for_signup membership_login" id="return_model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
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
				<div class="tab-content mb30">
                    <div class="sign_up_inner">
							<h2>@lang('messages.Return order')<br>
							<span class="bottom_border"></span>
						</h2>
                        {!!Form::open(array('url' => 'retun-order', 'method' => 'post', 'class' => 'tab-form attribute_form', 'id' => 'return_order' ,'onsubmit'=>'return return_order()'));!!} 
                            <div class="membership_inner">
								<div class="col-md-12 col-sm-12 col-xs-12">
									<div class="form-group">
										<label>@lang('messages.Return order') : </label>
										<div class="returen_order">
										<input type="hidden" name="order_id" value="{{ $vendor_info->order_id }}">
                                        <input type="hidden" name="vendor_name" value="{{ $vendor_info->vendor_name }}">
										{{ Form::select('return_reason', $return_reasons	,null, ['class' => 'form-control cooprative_select select_dropdown js-example-disabled-results'] ) }} 
									</div>
									</div>
								</div>
								<div class="col-md-12 col-sm-12 col-xs-12">
									<div class="form-group">
										<label> @lang('messages.Comments') : </label>
										<textarea name="comments" required class="form-control comments" placeholder="@lang('messages.Comments')" > <?php echo old('comments'); ?> </textarea>
										<span class="error"> 
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
	
    <script type="text/javascript">
	$('select').select2();
	$(function () {
	  $('[data-toggle="tooltip"]').tooltip()
	})
	function rating()
	{
		$( '#success_message_signup' ).show().html("");
		$(".membership_submit").hide();
		$(".ajaxloading").show();
		data = $("#rating").serializeArray();
		var c_url = '/rating';
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
					$('#myModal3').modal('hide');
					$('.comments').val('');
					$('.title').val('');
					  $('.order_payments_reorder').hide();
					$('.ajaxloading').hide();
					$('.membership_submit').show();
					setInterval(function(){location.reload(true); }, 5000);
					return false;
				}
				else
				{
					toastr.warning(data.Message);
					$('.ajaxloading').hide();
					$('.membership_submit').show();
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
			$('.membership_submit').show();
		});
		$(".return_order").on("click",function()
		{
			$('#return_model').modal('show');
		});
		
		$('.order-step').on("mouseover",function()
		{
			$('.granular-info-box').hide();
			$('.arrow').hide();
			var step = $(this).data("row");
			$('.steps'+step).show();
			$('.arrow'+step).show();
			
		});
	});
	
	function return_order()
	{
		$(".ajaxloading").show();
		data = $("#return_order").serializeArray();
		var c_url = '/return_order';
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
					$('#return_model').modal('hide');
					$('.ajaxloading').hide();
					location.reload(true);
					return false;
				}
				else
				{
					toastr.warning(data.Message);
					$('.ajaxloading').hide();
					$('.membership_submit').show();
					return false;
				}
			}, 
			error:function(resp)
			{
			}
		});
		return false;
	}
	
	
    </script>
@endsection
