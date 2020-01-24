@extends('layouts.app')
@section('content')

<section class="store_item_list">
	<div class="container">
		<div class="row">
			<div class="my_account_section">
				<div id="parentHorizontalTab">
					<div class="col-md-3">
					  @include('front.'.Session::get("general")->theme.'.profile_sidebar')
					</div>
					<?php $currency_side = getCurrencyPosition()->currency_side;$currency_symbol = getCurrency(); ?>
					<div class="col-md-9">
						<div class="right_descript">
							<div class="resp-tabs-container hor_1">
								<div class="elections_sections">
									<div class="tabs effect-3">
									<!-- tab-content -->
										<div class="tab-content">
											<section id="tab-item-1" class="edit_profile">
												<div class="payment_info">
													<h2 class="pay_title">@lang('messages.My orders')</h2>
													<div class="my_account_sections">
													   <div class="table-responsive"> 
															<table class="table">
																<thead>
																	<tr>
																		<th>@lang('messages.Order Id')</th>
																		<th>@lang('messages.Store name')</th>
																		<th>@lang('messages.Price')</th>
																		<th>@lang('messages.Status')</th>
																		<th>@lang('messages.Date')</th>
																		<th>@lang('messages.View')</th>
																	</tr>
																</thead>
																<tbody>
																	<?php // print_r($orders);exit;
																		if(count($orders)>0){
																		foreach($orders as $order){ 
																	//{{ URL::to('/order-info/'.$order->id) }}
																	$order_id = encrypt($order->id);
																	?>
																	<tr>
																		<td>{{$order->order_key_formated}}</td>
																		<td>{{$order->vendor_name}}</td>
																		<?php if($currency_side == 1) { ?>
																		    <td>{{$currency_symbol.$order->total_amount}}</td>
																		<?php } else { ?>
																		    <td>{{$order->total_amount.$currency_symbol}}</td>
																		<?php } ?>
																		<td><p style="color:{{$order->color_code}}">
																		<?php echo trans('messages.'.$order->status_name);?>
																		</p></td>
																		<td>{{ date('d M, Y', strtotime($order->created_date)) }}</td>
																		<td valign="middle">
																			<a title="@lang('messages.View')" href="{{ URL::to('/order-info/'.$order_id) }}">
																			<i class="glyph-icon flaticon-view"></i>
																			</a>
																		</td>
																	</tr>
																	<?php } } else { ?>
																	<tr>
																		<td colspan=6>No orders found</td>
																		
																	</tr>
																	<?php } ?>
																	
																</tbody>
															</table>
														</div>
													</div>
												</div>
											</section>
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
<!-- footer section strat end -->
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
 <!--Plug-in Initialisation-->
<script type="text/javascript">
	$('select').select2();
	$(document).ready(function() {
		//Horizontal Tab
		$('#parentHorizontalTab').easyResponsiveTabs({
			type: 'default', //Types: default, vertical, accordion
			width: 'auto', //auto or any width like 600px
			fit: true, // 100% fit in a container
			tabidentify: 'hor_1', // The tab groups identifier
			activate: function(event) { // Callback function if tab is switched
				var $tab = $(this);
				var $info = $('#nested-tabInfo');
				var $name = $('span', $info);
				$name.text($tab.text());
				$info.show();
			}
		});
	});
</script>
@endsection
