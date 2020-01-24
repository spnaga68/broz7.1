@extends('layouts.app')
@section('content')
<?php $general = Session::get("general"); //print_r($general);exit; ?>
<section class="store_item_list">
<div class="container">
<div class="cms_pages margin_bottom">
<div class="order-confirmation">
	<h1>@lang('messages.Thank you')</h1>
	<h2>@lang('messages.Your order has been received successfully')</h2>
</div>
<?php $currency_side = getCurrencyPosition()->currency_side;$currency_symbol = getCurrency(); ?>
<div class="stor_title">
<h1>@lang('messages.Order Summary')</h1>
<div class="order_process_agin">
<a class="btn btn-primary btn-lg" href="{{url('/')}}" title=" Continue shopping"> Continue shopping </a>
</div>
</div>

<div class="cart_sections_tables">
	<div class="table-responsive">
		<table class="table">
			<thead>
				<tr>
					<th style="text-align:left;">@lang('messages.Items')</th>
					<th>@lang('messages.Price')</th>
					<th>@lang('messages.Quantity')</th>
					<th>@lang('messages.Total')</th>
				</tr>
			</thead>
			<tbody>
				<?php 
				$sub_total = 0;
				foreach($order_items as $items){ ?>
				<tr>
					<td style="text-align:left;">
					
						<a href="javascript:;" title="{{ ucfirst(strtolower($items->product_name)) }}">
							<img src="<?php echo $items->product_image.'?'.time(); ?>" title="{{ ucfirst($items->product_name) }}">
							{{ str_limit(ucfirst(strtolower($items->product_name)),30) }}
						</a>
						
					</td>
					<?php if($currency_side == 1) { ?>
					    <td>{{$currency_symbol.$items->item_cost}}</td>
					<?php } else { ?>
					    <td>{{$items->item_cost.$currency_symbol}}</td>
					<?php } ?>
					<td>
						<div class="count_numbers">
							<ul>
								<li class="minuse_number">{{$items->item_unit}}</li>
							</ul>
						</div>
					</td>
					<?php if($currency_side == 1) { ?>
					    <td valign="middle">{{($currency_symbol.$items->item_cost*$items->item_unit)}}</td>
					<?php } else { ?>
					     <td valign="middle">{{($items->item_cost*$items->item_unit).$currency_symbol}}</td>
					<?php } ?>	 
				</tr>
				
				<?php 
				$sub_total += $items->item_cost*$items->item_unit;
				} ?>
			</tbody>
		</table>
		<div class="price_setions">
			<div class="price_setions_list">
				<div class="col-md-7 dis_none"></div>
				<div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.Subtotal')</label></div>
				<?php if($currency_side == 1) { ?>
				    <div class="col-md-2 col-sm-6 col-xs-6"><p>{{$currency_symbol.$sub_total}}</p></div>
				<?php } else { ?>
				      <div class="col-md-2 col-sm-6 col-xs-6"><p>{{$sub_total.$currency_symbol}}</p></div>
				<?php } ?>	  
			</div>
			<?php if($delivery_details[0]->order_type == 1){ ?>
			<div class="price_setions_list">
				<div class="col-md-7 dis_none"></div>
				<div class="col-md-3 col-sm-6 col-xs-6"><label class="color_sec">@lang('messages.Delivery fee')</label></div>
				<?php if($currency_side == 1) { ?>
				<div class="col-md-2 col-sm-6 col-xs-6"><p class="color_sec">{{$currency_symbol.$delivery_details[0]->delivery_charge}}</p></div>
				<?php } else { ?>
				<div class="col-md-2 col-sm-6 col-xs-6"><p class="color_sec">{{$delivery_details[0]->delivery_charge.$currency_symbol}}</p></div>
				<?php } ?>
			</div>
			<?php } ?>
			<div class="price_setions_list">
				<div class="col-md-7 dis_none"></div>
				<div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.tax')</label></div>
				<?php if($currency_side == 1) { ?>
				     <div class="col-md-2 col-sm-6 col-xs-6"><p>{{$currency_symbol.$delivery_details[0]->service_tax}}</p></div>
				<?php } else { ?>
				     <div class="col-md-2 col-sm-6 col-xs-6"><p>{{$delivery_details[0]->service_tax.$currency_symbol}}</p></div>
				<?php } ?>
			</div>
			<?php if($delivery_details[0]->coupon_amount != 0) { ?>
			<div class="price_setions_list">
				<div class="col-md-7 dis_none"></div>
				<div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.Promo code discount')</label></div>
				<?php if($currency_side == 1) { ?>
				     <div class="col-md-2 col-sm-6 col-xs-6"><p>{{$currency_symbol.$delivery_details[0]->coupon_amount}}</p></div>
				<?php } else { ?>
				     <div class="col-md-2 col-sm-6 col-xs-6"><p>{{$delivery_details[0]->coupon_amount.$currency_symbol}}</p></div>
				<?php } ?>
			</div>
			<?php } ?>
			<div class="price_setions_list_total border-bottom">
				<div class="col-md-7 dis_none"></div>
				<div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.Total')</label></div>
				<?php if($currency_side == 1) { ?>
				     <div class="col-md-2 col-sm-6 col-xs-6"><p>{{$currency_symbol.$delivery_details[0]->total_amount}}</p></div>
				<?php } else { ?>
                       <div class="col-md-2 col-sm-6 col-xs-6"><p>{{$delivery_details[0]->total_amount.$currency_symbol}}</p></div>	
				<?php } ?>	   
					   
			</div>
		</div>
	</div>
</div>

</div>
	<div class="cms_pages margin_bottom common_border_yo">
	<div class="delivery_det">
	<h3>@lang('messages.Delivery details')</h3>
	<div class="deli_list">
	<div class="col-md-3 col-sm-6 col-xs-6 padding_left0">
	<h4>@lang('messages.Payment type')</h4>
	</div>
	<div class="col-md-9 col-sm-6 col-xs-6 padding_right0">
	<h4>{{ ucfirst(strtolower($delivery_details[0]->name)) }}</h4>
	</div>
	</div>
	
	<?php if($delivery_details[0]->order_type == 1){ ?>
		<div class="deli_list">
		<div class="col-md-3 col-sm-6 col-xs-6 padding_left0">
		<h4>@lang('messages.Delivery address')</h4>
		</div>
		
		</div>
		<div class="col-md-9 col-sm-6 col-xs-6 padding_right0">
			<h4>{{$delivery_details[0]->user_contact_address}}</h4>
		</div>
		<div class="deli_list">
			<div class="col-md-3 col-sm-6 col-xs-6 padding_left0">
				<h4>@lang('messages.Delivery slot')</h4>
			</div>
			<div class="col-md-9 col-sm-6 col-xs-6 padding_right0">
				<?php $delivery_date = date("d F, l", strtotime($delivery_details[0]->delivery_date)); 
				$delivery_time = date('g:i a', strtotime($delivery_details[0]->start_time)).'-'.date('g:i a', strtotime($delivery_details[0]->end_time));
				?>
				<h4>{{ $delivery_date ." : ". $delivery_time}}</h4>
			</div>
		</div>
	<?php } else { ?>
		<div class="deli_list">
			<div class="col-md-3 col-sm-6 col-xs-6 padding_left0">
				<h4>@lang('messages.Pickup address')</h4>
			</div>
		</div>
		<div class="col-md-9 col-sm-6 col-xs-6 padding_right0">
			<h4>{{$delivery_details[0]->contact_address}}</h4>
		</div>
	<?php } ?>
	<div class="deli_list">
	<div class="col-md-3 col-sm-6 col-xs-6 padding_left0">
	<h4>@lang('messages.Delivery instructions')</h4>
	</div>
	<div class="col-md-9 col-sm-6 col-xs-6 padding_right0"> 
	<h4>{{$delivery_details[0]->delivery_instructions}}</h4>
	</div>
	</div>
	</div>
	</div>

</div>

    <!-- container end -->
    <!-- footer section strat -->
 </section> 
    <script type="text/javascript">
        $('select').select2();
    </script>
@endsection