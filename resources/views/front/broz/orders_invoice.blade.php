@extends('layouts.app')
@section('content')
<?php $general = Session::get("general");?>

   <section class="store_item_list">
        <div class="container">
            <div class="row">
                <div class="my_account_section">
                    <div id="parentHorizontalTab">
                        <div class="col-md-9">
                            <div class="right_descript">
                                <div class="resp-tabs-container hor_1">
                                    <div class="profile_sections">
                                        <h2 class="pay_title">@lang('messages.Order Summary')</h2>
                                        <div class="stores_det_info">
                                            <div class="col-md-7">
                                                <div class="store_det_in">
												<?php  if(file_exists(base_path().'/public/assets/admin/base/images/vendors/list/'.$vendor_info->logo_image)) { ?>
													<img  width="161px" height="107px" alt="{{ ucfirst($vendor_info->vendor_name) }}"  src="<?php echo url('/assets/admin/base/images/vendors/list/'.$vendor_info->logo_image.''); ?>" >
												<?php } else{  ?>
													<img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/blog_no_images.png');?>" alt="{{ ucfirst($vendor_info->vendor_name) }}">
												<?php } ?>
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
                                                        <h4>@lang('messages.Status')</h4></div>
                                                    <div class="col-md-6 col-ms-6 col-xs-6">
                                                        <h5>{{ $vendor_info->name  }}</h5></div>
                                                </div>
                                            </div>
                                        </div>

<?php $currency_side = getCurrencyPosition()->currency_side;$currency_symbol = getCurrency(getCurrentLang()); ?>
                                        <div class="my_account_sections">
                                            <div class="table-responsive">
                                                <div class="cart_sections_tables">
                                                    <div class="table-responsive">
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
																				<img src="<?php echo url('/assets/admin/'.$general->theme.'/images/products/thumb/'.$items->product_image); ?>" title="{{ $general->site_name }}">{{ str_limit(ucfirst(strtolower($items->product_name)),30) }}
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
																		<?php } else { ?>
																		    <td valign="middle">{{($items->item_cost*$items->item_unit).$currency_symbol}}</td>
																		<?php } ?>
																	</tr>
																<?php $sub_total += $items->item_cost*$items->item_unit; } ?>          
                                                            </tbody>
                                                        </table>
                                                        <div class="price_setions">
                                                            <div class="price_setions_list">
                                                                <div class="col-md-7 dis_none"></div>
                                                                <div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.Subtotal')</label></div>
																<?php if($currency_side == 1) { ?>
                                                                 <div lass="col-md-2 col-sm-6 col-xs-6">
                                                                    <p>{{$currency_symbol.$sub_total}}</p>
                                                                </div>
																<?php } else { ?>
																 <div lass="col-md-2 col-sm-6 col-xs-6">
                                                                    <p>{{$sub_total.$currency_symbol}}</p>
                                                                </div>
																<?php } ?>
                                                            </div>
                                                            <div class="price_setions_list">
                                                                <div class="col-md-7 dis_none"></div>
                                                                <div class="col-md-3 col-sm-6 col-xs-6"><label class="color_sec">@lang('messages.Delivery fee')</label></div>
																<?php if($currency_side == 1) { ?>
                                                                <div class="col-md-2 col-sm-6 col-xs-6">
                                                                    <p class="color_sec">{{$currency_symbol.delivery_details[0]->delivery_charge}}</p>
                                                                </div>
																<?php } else { ?>
																<div class="col-md-2 col-sm-6 col-xs-6">
                                                                    <p class="color_sec">{{$delivery_details[0]->delivery_charge.$currency_symbol}}</p>
                                                                </div>
																<?php } ?>
                                                            </div>
                                                            <div class="price_setions_list">
                                                                <div class="col-md-7 dis_none"></div>
                                                                <div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.tax')</label></div>
																<?php if($currency_side == 1) { ?>
                                                                <div class="col-md-2 col-sm-6 col-xs-6">
                                                                    <p>{{$currency_symbol.delivery_details[0]->service_tax}}</p>
                                                                </div>
																<?php } else { ?>
																<div class="col-md-2 col-sm-6 col-xs-6">
                                                                    <p>{{$delivery_details[0]->service_tax.$currency_symbol}}</p>
                                                                </div>
																	<?php } ?>
                                                            </div>

                                                            <div class="price_setions_list_total">
                                                                <div class="col-md-7 dis_none"></div>
                                                                <div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.Total')</label></div>
																<?php if($currency_side == 1) { ?>
                                                                <div class="col-md-2 col-sm-6 col-xs-6">
                                                                    <p>{{$currency_symbol.$delivery_details[0]->total_amount}}</p>
                                                                </div>
																<?php } else { ?>
																<div class="col-md-2 col-sm-6 col-xs-6">
                                                                    <p>{{$delivery_details[0]->total_amount.$currency_symbol}}</p>
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
                                                                    <h4>{{$delivery_details[0]->address}}</h4>
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
                                                                    <h4>{{ ucfirst(strtolower($vendor_info->payment_gateway_name))  }}</h4>
                                                                </div>
                                                            </div>
                                                            <?php } else { ?>
															<div class="deli_list">
                                                                <div class="col-md-3 col-sm-6 col-xs-6">
                                                                    <h4>@lang('messages.Pickup address')</h4>
                                                                </div>
                                                            </div>
															<?php } ?>
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
@endsection
