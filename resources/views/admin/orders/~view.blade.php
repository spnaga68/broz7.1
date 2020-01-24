@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script>
<link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>
 <link href="{{ URL::asset('assets/admin/base/css/select2.css') }}" media="all" rel="stylesheet" type="text/css" />
 <link href="{{ URL::asset('assets/admin/base/css/toastr.min.css') }}" rel="stylesheet" />
 
 <script src="{{ URL::asset('assets/admin/base/js/toastr.min.js') }}"></script>
<!-- Nav tabs -->
<div class="pageheader">
<div class="media">
	<div class="pageicon pull-left">
		<i class="fa fa-home"></i>
	</div>
	<div class="media-body">
		<ul class="breadcrumb">
			<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
			<li>@lang('messages.View orders')</li>
		</ul>
		<h4>@lang('messages.View orders')  - {!! $delivery_details[0]->invoice_id !!}</h4>
	</div>
</div><!-- media -->
</div><!-- pageheader -->
<?php $currency_side = getCurrencyPosition()->currency_side;$currency_symbol = getCurrency(); ?>
<div class="contentpanel">
<div class="col-md-12">
<div class="row panel panel-default">
	<div class="grid simple">
		@if (count($errors) > 0)
		<div class="alert alert-danger">
				<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>
			<ul>
				@foreach ($errors->all() as $error)
					<li><?php echo trans('messages.'.$error); ?> </li>
				@endforeach
			</ul>
		</div>
		@endif
		<ul class="nav nav-justified nav-wizard nav-pills">
			<li @if(old('tab_info')=='login_info' || old('tab_info')=='') class="active" @endif><a href="#login_info" class="login_info" data-toggle="tab"><strong>@lang('messages.Order Information')</strong></a></li>
			<li @if(old('tab_info')=='vendor_info') class="active" @endif ><a href="#vendor_info" class="vendor_info" data-toggle="tab"><strong>@lang('messages.Payment Information')</strong></a></li>
			<li @if(old('tab_info')=='delivery_info') class="active" @endif ><a href="#delivery_info" class="delivery_info" data-toggle="tab"><strong><?php  if($delivery_details[0]->order_type==1) echo trans('messages.Delivery Information'); else echo trans('messages.Pickup Information'); ?></strong></a></li>
			<li @if(old('tab_info')=='contact_info') class="active" @endif ><a href="#contact_info" class="contact_info" data-toggle="tab"><strong>@lang('messages.Products')</strong></a></li>
			<li @if(old('tab_info')=='history') class="active" @endif ><a href="#history" class="history" data-toggle="tab"><strong>@lang('messages.History')</strong></a></li>
		</ul>
		
		<div class="tab-content tab-content-simple mb30 no-padding" >
			<div class="tab-pane active" id="login_info">
					<legend>@lang('messages.Order Information')</legend>
					<div class="form-group">
						<label class="col-sm-4 control-label">@lang('messages.Order id')</label>
						<div class="col-sm-8">
						  <label class="col-sm-4 control-label">{!! $delivery_details[0]->order_key_formated !!}</label>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">@lang('messages.Name')</label>
						<div class="col-sm-8">
						  <label class="col-sm-4 control-label">{!! $delivery_details[0]->user_name !!}</label>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">@lang('messages.Store name')</label>
						<div class="col-sm-8">
						  <label class="col-sm-4 control-label"><?php echo isset($vendor_info[0]->vendor_name)?$vendor_info[0]->vendor_name:'-';?></label>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">@lang('messages.Outlet name')</label>
						<div class="col-sm-8">
						  <label class="col-sm-4 control-label">{!! $delivery_details[0]->outlet_name !!}</label>
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-sm-4 control-label">@lang('messages.Status')</label>
						<div class="col-sm-8">
						  <label class="col-sm-4 control-label">
						  <span style="color:<?php echo isset($vendor_info[0]->color_code)?$vendor_info[0]->color_code:''; ?>"><?php echo isset($vendor_info[0]->status_name)?$vendor_info[0]->status_name:'-'; ?></span>
						  </label>
						</div>

					</div>
					
					<div class="form-group">
						<label class="col-sm-4 control-label">@lang('messages.Total')</label>
						<div class="col-sm-8">
						<?php if($currency_side == 1) { ?>
						       <label class="col-sm-4 control-label">{!!$delivery_details[0]->currency_code!!}{!! $delivery_details[0]->total_amount !!} </label>
						  <?php } else { ?>
						       <label class="col-sm-4 control-label">{!! $delivery_details[0]->total_amount !!} {!!$delivery_details[0]->currency_code!!}</label>
						  <?php } ?>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">@lang('messages.Email')</label>
						<div class="col-sm-8">
						  <label class="col-sm-6 control-label">{!! $delivery_details[0]->email !!}</label>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">@lang('messages.Phone')</label>
						<div class="col-sm-8">
						  <label class="col-sm-4 control-label">{!! $delivery_details[0]->mobile !!}</label>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label"></label>
						<div class="col-sm-8">
							<?php $order_id = encrypt($delivery_details[0]->order_id); ?>
							<label class="col-sm-4 control-label"><a class="btn btn-primary" href="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/invoice/'.$delivery_details[0]->invoice_id.'.pdf');?>" title="@lang('messages.Invoice')">@lang('messages.Download invoice')</a></label>
						</div>
					</div>
			</div>
			<div class="tab-pane" id="vendor_info">
				<legend>@lang('messages.Payment Information')</legend>
				<div class="form-group">
					<label class="col-sm-4 control-label">@lang('messages.Payment mode')</label>
					<div class="col-sm-8">
					  <label class="col-sm-4 control-label">{!! $delivery_details[0]->payment_gateway_name !!}</label>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">@lang('messages.Currency')</label>
					<div class="col-sm-8">
				
					  <label class="col-sm-4 control-label">{!! $delivery_details[0]->currency_code !!}</label>
				
					</div>
				</div>
			</div>
			<div class="tab-pane" id="delivery_info">
				<legend> <?php if($delivery_details[0]->order_type==1) echo trans('messages.Delivery Information'); else echo trans('messages.Pickup Information'); ?></legend>
				<div class="form-group">
					<label class="col-sm-4 control-label">@lang('messages.Name')</label>
					<div class="col-sm-8">
						<label class="col-sm-4 control-label">{!! $delivery_details[0]->user_name !!}</label>
					</div>
				</div>
				<?php if($delivery_details[0]->order_type==1) { ?>
				<div class="form-group">
					<label class="col-sm-4 control-label">@lang('messages.Address')</label>
					<div class="col-sm-8">
						<label class="col-sm-4 control-label">{!! $delivery_details[0]->address !!}</label>
					</div>
				</div>
				<?php } else {?>
				
				<div class="form-group">
					<label class="col-sm-4 control-label">@lang('messages.Pickup address')</label>
					<div class="col-sm-8">
						<label class="col-sm-4 control-label">{!! $delivery_details[0]->contact_address !!}</label>
					</div>
				</div>
				<?php } ?>
				<div class="form-group">
					<label class="col-sm-4 control-label">@lang('messages.Delivery Instructions Date')</label>
					<div class="col-sm-8"><label class="col-sm-4 control-label"><?php echo date("d F, l", strtotime($delivery_details[0]->delivery_date)); ?></label></div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">@lang('messages.Delivery Slot')</label>
					<div class="col-sm-8"><label class="col-sm-4 control-label"><?php echo date('h:i:s A', strtotime($delivery_details[0]->start_time)).' - '.date('h:i:s A', strtotime($delivery_details[0]->end_time)); ?></label></div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">@lang('messages.Delivery Instructions')</label>
					<div class="col-sm-8"><label class="col-sm-4 control-label">{!! $delivery_details[0]->delivery_instructions !!}</label></div>
				</div>

								<?php if($delivery_details[0]->order_status==12){ $theme = Session::get('general')->theme; if(count($order_history1) > 0){ ?>
					<div class="form-group">
						<label class="col-sm-4 control-label">@lang('messages.Digital signature') </label>
						<div class="col-sm-8">
							<?php if(file_exists(base_path().'/public/assets/front/'.$theme.'/images/digital_signature/'.$order_history1[0]->digital_signature) && $order_history1[0]->digital_signature != '') { ?>
								<img height='165px'; width='165px'; src="<?php echo url('/assets/front/'.$theme.'/images/digital_signature/'.$order_history1[0]->digital_signature); ?>" class="img-circle">
							<?php } else {  ?>
								<img src=" {{ URL::asset('assets/admin/base/images/default_avatar_male.jpg') }} " class="img-circle">
							<?php } ?>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">@lang('messages.Order attachment') </label>
						<div class="col-sm-8">
							<?php if(file_exists(base_path().'/public/assets/front/'.$theme.'/images/order_attachment/'.$order_history1[0]->order_attachment) && $order_history1[0]->order_attachment != '') { ?>
								<img height='165px'; width='165px'; src="<?php echo url('/assets/front/'.$theme.'/images/order_attachment/'.$order_history1[0]->order_attachment); ?>" class="img-circle">
							<?php } else {  ?>
								<img src=" {{ URL::asset('assets/admin/base/images/default_avatar_male.jpg') }} " class="img-circle">
							<?php } ?>
						</div>
					</div>
				<?php } } ?>
			</div>
			<div class="tab-pane" id="contact_info">
				<legend>@lang('messages.Products')</legend>
				<table class="table table-bordered">
					<thead>
						<tr>
						  <th class="text-left">@lang('messages.Product')</td>
						  <th class="text-right">@lang('messages.Quantity')</td>
						  <th class="text-right">@lang('messages.Unit Price')</td>
						  <th class="text-right">@lang('messages.Total')</td>
						</tr>
					</thead>
					<tbody>
						<?php
						$subtotal = "";
						foreach($order_items as $items) { ?>
							<tr>
								<td class="text-left">{{ucfirst($items->product_name)}}</a>
								<td class="text-right">{{$items->item_unit}}</td>
								<?php if($currency_side == 1) { ?>
								     <td class="text-right">{{$delivery_details[0]->currency_code.$items->item_cost}}</td>
									 <td class="text-right"><?php echo $delivery_details[0]->currency_code.$items->item_cost*$items->item_unit; ?></td>
								<?php } else { ?>
								     <td class="text-right">{{$items->item_cost.$delivery_details[0]->currency_code}}</td>
									 <td class="text-right"><?php echo $items->item_cost*$items->item_unit.$delivery_details[0]->currency_code; ?></td>
								<?php } ?>
								
							</tr>
						<?php 
							$subtotal += $items->item_cost*$items->item_unit;
						} ?>
							<tr>
								<td colspan="3" class="text-right">Sub-Total:</td>
								<?php if($currency_side == 1) { ?>
								    <td class="text-right">{{$delivery_details[0]->currency_code.$subtotal}}</td>
								<?php } else { ?>
								    <td class="text-right">{{$subtotal.$delivery_details[0]->currency_code}}</td>
								<?php } ?>
							</tr>
							
							<?php if($delivery_details[0]->order_type==1) { ?>
							<tr>
								<td colspan="3" class="text-right">Delivery charge:</td>
								<?php if($currency_side == 1) { ?>
								    <td class="text-right">{{$delivery_details[0]->currency_code.$items->delivery_charge}}</td>
								<?php } else { ?>
								    <td class="text-right">{{$items->delivery_charge.$delivery_details[0]->currency_code}}</td>
								<?php } ?>
							</tr>
							<?php } ?>
							<tr>
								<td colspan="3" class="text-right">Service tax:</td>
								<?php if($currency_side == 1) { ?>
								    <td class="text-right">{{$delivery_details[0]->currency_code.$items->service_tax}}</td>
								<?php } else { ?>
								    <td class="text-right">{{$items->service_tax.$delivery_details[0]->currency_code}}</td>
								<?php } ?>
							</tr>
							<?php if($delivery_details[0]->coupon_amount > 0) { ?>
							<tr>
								<td colspan="3" class="text-right">Coupon discount:</td>
								<?php if($currency_side == 1) { ?>
								   <td class="text-right">- {{$delivery_details[0]->currency_code.$items->coupon_amount}}</td>
								<?php } else { ?>
								   <td class="text-right">- {{$items->coupon_amount.$delivery_details[0]->currency_code}}</td>
								<?php } ?>
							</tr>
							<?php } ?>
							<tr>
								<td colspan="3" class="text-right">Total:</td>
								<?php if($currency_side == 1) { ?>
								<td class="text-right">{{$delivery_details[0]->currency_code.$items->total_amount}}</td>
								<?php } else { ?>
								<td class="text-right">{{$items->total_amount.$delivery_details[0]->currency_code}}</td>
								<?php } ?>
							</tr>
					</tbody>
				</table>
			</div>
			<div class="tab-pane" id="history">
				<legend>@lang('messages.History')</legend>
				<div id="history-data">
					<table class="table table-bordered">
						<thead>
							<tr>
							  <th class="text-left">@lang('messages.Date')</th>
							  <th class="text-right">@lang('messages.Comment')</th>
							  <th class="text-right">@lang('messages.Status')</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$subtotal = "";
							foreach($order_history as $history) { ?>
								<tr>
									<td class="text-left"><?php echo date('M j Y g:i A', strtotime($history->log_time)); ?> </td>
									<td class="text-right">{{$history->order_comments}}</td>
									<td class="text-right"><span style="color:<?php echo $history->color_code; ?>">
										{!! $history->status_name !!}</span></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
				<fieldset>
					<legend>Add Order History</legend>
					{!!Form::open(array('url' => ['update_delivery_status',$delivery_details[0]->order_id], 'method' => 'post','class'=>'form-horizontal','id'=>'update_delivery_status','files' => true));!!}
							<div class="form-group">
							<label class="col-sm-2 control-label" for="input-order-status">Order Status</label>
							<div class="col-sm-10">
							<select name="order_status_id" id="input-order-status" class="form-control">
								<option value="">Select status</option>
								<?php 
								//11 Delivered
								 if($delivery_details[0]->order_type==2) {
									 $order_status_list = array();
									 $order_status_list['id'] = 12;
									 $order_status_list['name'] = "Delivered";
									 ?>
									 <option value="{{$order_status_list['id']}}">{{$order_status_list['name']}}</option> <?php }
								
								 else
								 {
								foreach($order_status_list as $status) { ?>
									<option value="{{$status->id}}">{{$status->name}}</option>
								 <?php } } ?>
							</select>
						</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input-notify">Notify Customer</label>
								<div class="col-sm-10">
								<div class="checkbox"><label>
								<input name="notify" value="1" id="input-notify" type="checkbox">
								<input name="order_id" value="<?php echo isset($vendor_info[0]->order_id)?$vendor_info[0]->order_id:'';?>" type="hidden">
								</label></div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input-comment">Comment</label>
								<div class="col-sm-10">
									<textarea maxlength="300" name="comment" rows="8" id="input-comment" class="form-control"></textarea>
								</div>
							</div>
						{!!Form::close();!!} 
						<div class="text-left"><p class="text-left error_data"></p></div>
						<div class="text-right">
							<button type="button" id="button-history" data-loading-text="Loading..." class="btn btn-primary"><i class="fa fa-plus-circle"></i> Add History</button>
						</div>
				</fieldset>
			</div>
		</div>
		<div class="form-group Loading_Img" style="display:none;">
			<div class="col-sm-4">
				<i class="fa fa-spinner fa-spin fa-3x"></i><strong style="margin-left: 3px;">@lang('messages.Processing...')</strong>
			</div>
		</div>
	</div>
		<!-- panel-body -->
	<?php /*<div class="panel-footer">
		<input type="hidden" name="tab_info" class="tab_info" value="">
		<button class="btn btn-primary mr5" title="Save">@lang('messages.Save')</button>
		<button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('vendors/vendors') }}'">@lang('messages.Cancel')</button>
	</div><!-- panel-footer --> */ ?>
</div><!-- panel-default -->
	
</div>
</div>
<script type="text/javascript">
$( document ).ready(function() {
	$('#categories').select2();
	@if(old('tab_info')=='vendor_info')
		$('.tab_info').val('vendor_info');
		$('#login_info').hide();
		$('#delivery_info').hide();
		$('#contact_info').hide();
		$('#vendor_info').show();
	@elseif(old('tab_info')=='login_info')
		$('.tab_info').val('login_info');
		$('#login_info').show();
		$('#delivery_info').hide();
		$('#contact_info').hide();
		$('#vendor_info').hide();
	@elseif(old('tab_info')=='delivery_info')
		$('.tab_info').val('delivery_info');
		$('#login_info').hide();
		$('#delivery_info').show();
		$('#contact_info').hide();
		$('#vendor_info').hide();
	@elseif(old('tab_info')=='contact_info')
		$('.tab_info').val('contact_info');
		$('#login_info').hide();
		$('#delivery_info').hide();
		$('#contact_info').show();
		$('#vendor_info').hide();
	@elseif(old('tab_info')=='history')
		$('.tab_info').val('history');
		$('#login_info').hide();
		$('#delivery_info').hide();
		$('#history').show();
		$('#contact_info').hide();
		$('#vendor_info').hide();	
	@endif
	/*function addOrderInfo()
	{
		var status_id = $('select[name="order_status_id"]').val();
		var token = $('input[name=_token]').val();
		var url = '{{url('order/update-status')}}';
		  $.ajax({
			url: url,
			type: 'post',
			dataType: 'html',
			data: $("#update_delivery_status").serialize()
		  });
	} */
	$('#button-history').on('click', function() 
	{
		if($("#input-order-status").val() == "")
		{
			alert("Please select order status");
			return false;
		}
		token = $('input[name=_token]').val();
		var url = '{{url('admin/orders/update-status')}}';
		$.ajax({
			url: url,
			headers: {'X-CSRF-TOKEN': token},
			data: $("#update_delivery_status").serialize(),
			type: 'POST',
			datatype: 'JSON',
			beforeSend: function() {
			$('#button-history').button('loading');
			},
			complete: function() {
				$('#button-history').button('reset');
			},
			success: function(json) 
			{
				$('#history-data').load('{{url('admin/orders/load_history/'.$delivery_details[0]->order_id)}}');
				toastr.success('History updated successfully')
				 $('#update_delivery_status')[0].reset();
				 $('#button-history').button('reset');	
				
			},
			error: function(xhr, ajaxOptions, thrownError) 
			{
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	});
});
	

$(".login_info").on("click", function(){
	$('.tab_info').val('login_info');
	$('#delivery_info').hide();
	$('#vendor_info').hide();
	$('#login_info').show();
	$('#contact_info').hide();
	$('#history').hide();
});
$(".vendor_info").on("click", function(){
	$('.tab_info').val('vendor_info');
	$('#delivery_info').hide();
	$('#vendor_info').show();
	$('#login_info').hide();
	$('#contact_info').hide();
	$('#history').hide();
	//initialize();
});
$(".delivery_info").on("click", function(){
	$('.tab_info').val('delivery_info');
	$('#delivery_info').show();
	$('#vendor_info').hide();
	$('#contact_info').hide();
	$('#login_info').hide();
	$('#history').hide();
});
$(".contact_info").on("click", function(){
	$('.tab_info').val('contact_info');
	$('#delivery_info').hide();
	$('#vendor_info').hide();
	$('#login_info').hide();
	$('#contact_info').show();
	$('#history').hide();
});
$(".history").on("click", function(){
	$('.tab_info').val('contact_info');
	$('#delivery_info').hide();
	$('#vendor_info').hide();
	$('#login_info').hide();
	$('#contact_info').hide();
	$('#history').show();
});


$(window).load(function(){	
	$('form').preventDoubleSubmission();	
});
$('#country_id').change(function(){
	var cid, token, url, data;
	token = $('input[name=_token]').val();
	cid = $('#country_id').val();
	url = '{{url('list/CityList')}}';
	data = {cid: cid};
	$.ajax({
		url: url,
		headers: {'X-CSRF-TOKEN': token},
		data: data,
		type: 'POST',
		datatype: 'JSON',
		success: function (resp)
		{
			$('#city_id').empty();
			$.each(resp.data, function(key, value){
				$('#city_id').append($("<option></option>").attr("value",value['id']).text(value['city_name'])); 
		   });
		}
	});
});
</script>

@endsection
