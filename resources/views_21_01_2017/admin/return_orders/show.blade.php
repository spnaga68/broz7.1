@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script>
 <link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" />
 <script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>
 <link href="{{ URL::asset('assets/admin/base/css/select2.css') }}" media="all" rel="stylesheet" type="text/css" />

<!-- Nav tabs -->
<div class="pageheader">
	<div class="media">
		<div class="pageicon pull-left">
			<i class="fa fa-home"></i>
		</div>
		<div class="media-body">
			<ul class="breadcrumb">
				
				<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
				<li><a href="{{ URL::to('orders/return_orders') }}">@lang('messages.View Order Return')</a></li>
			</ul>
			<h4>@lang('messages.View Order Return')</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->
<div class="contentpanel">
	<div class="col-md-12">
		<div class="row panel panel-default">
			<div class="grid simple">
			<?php $currency_side = getCurrencyPosition()->currency_side;$currency_symbol = getCurrency(); ?>
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
					<li @if(old('tab_info')=='login_info' || old('tab_info')=='') class="active" @endif><a href="#login_info" class="login_info" data-toggle="tab"><strong>@lang('messages.Return History')</strong></a></li>
					<li @if(old('tab_info')=='vendor_info') class="active" @endif ><a href="#vendor_info" class="vendor_info" data-toggle="tab"><strong>@lang('messages.Order Information')</strong></a></li>
					<li @if(old('tab_info')=='delivery_info') class="active" @endif ><a href="#delivery_info" class="delivery_info" data-toggle="tab"><strong>@lang('messages.Customer Information')</strong></a></li>
					<li @if(old('tab_info')=='contact_info') class="active" @endif ><a href="#contact_info" class="contact_info" data-toggle="tab"><strong>@lang('messages.Store and Delivery Information')</strong></a></li>
				</ul>
				<div class="tab-content tab-content-simple mb30 no-padding" >
					<div class="tab-pane active" id="login_info">
						<legend>@lang('messages.Return History')</legend>
				        <div id="history">
				        	<h4>@lang('messages.History')</h4>
				        	<table class="table table-bordered">
								<thead>
									<tr>
										<th class="text-left">@lang('messages.Date Added')</td>
										<th class="text-left">@lang('messages.Action')</td>
										<th class="text-left">@lang('messages.Status')</td>
										<th class="text-left">@lang('messages.Customer Notified')</td>
									</tr>
								</thead>
								<tbody>
								@if(count($return_orders_logs)>0)
									@foreach($return_orders_logs as $list)
									<tr>
										<td class="text-left">{!! $list->date_added !!}</td>
										<td class="text-left">{!! $list->return_actions_name !!}</td>
										<td class="text-left">{!! $list->return_status_name !!}</td>
										<td class="text-left">{!! ($list->customer_notified==1)?'Yes':'No' !!}</td>
									</tr>
									@endforeach
								@else
									<tr>
										<td class="text-center" colspan="4">No results!</td>
									</tr>
								@endif
								</tbody>
							</table>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.Customer Comments')</label>
							<div class="col-sm-9">{!! $data[0]->return_comments !!}</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.Current Return Reason')</label>
							<div class="col-sm-9">{!! $data[0]->return_reason_name !!}</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.Current Return Status')</label>
							<div class="col-sm-9">{!! $data[0]->return_status_name !!}</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.Current Return Action')</label>
							<div class="col-sm-9">{!! $data[0]->return_action_name !!}</div>
						</div>
						{!!Form::open(array('url' => ['update_return_order',$data[0]->id], 'method' => 'post','id'=>'product_form','files' => true));!!}
						<div class="form-group">
							<input type="hidden" name="order_id" value="{!! $data[0]->order_id !!}">
							<input type="hidden" name="vendor_id" value="{!! $data[0]->vendor_id !!}">
							<input type="hidden" name="customer_id" value="{!! $data[0]->customer_id !!}">
							<input type="hidden" name="outlet_id" value="{!! $data[0]->outlet_id !!}">
							<input type="hidden" name="order_key" value="{!! $data[0]->order_key_formated !!}">
							<input type="hidden" name="invoice_id" value="{!! $data[0]->invoice_id !!}">
							<label class="col-sm-3 control-label ">@lang('messages.Return Reason')</label>
							<div class="col-sm-6">
								<select name="return_reason" id="return_reason" class="form-control" readonly="readonly">
									<?php $return_reason = $return_reasons; ?>
										@foreach($return_reason as $list)
											<option value="{{$list->id}}" <?php echo ($data[0]->return_reason==$list->id)?'selected="selected"':'disabled="disabled"'; ?> >{{$list->name}}</option>
										@endforeach
								</select>
							</div> 
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label ">@lang('messages.Return Action')</label>
							<div class="col-sm-6">
								<select name="return_action" id="return_action" class="form-control">
									<?php $return_action = $return_actions; ?>
										@foreach($return_action as $list)
											<option value="{{$list->id}}" <?php echo ($data[0]->return_action_id==$list->id)?'selected="selected"':''; ?> >{{$list->name}}</option>
										@endforeach
								</select>
							</div> 
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label ">@lang('messages.Return Status') <span class="asterisk">*</span></label>
							<div class="col-sm-6">
								<select name="return_status" id="return_status" class="form-control">
									<?php $return_status = $return_statuses; ?>
										@foreach($return_status as $list)
											<option value="{{$list->id}}" <?php echo ($data[0]->return_status==$list->id)?'selected="selected"':''; ?> >{{$list->name}}</option>
										@endforeach
								</select>
							</div> 
						</div>
						<div class="form-group Loading_Img" style="display:none;">
							<div class="col-sm-3">
								<i class="fa fa-spinner fa-spin fa-3x"></i><strong style="margin-left: 3px;">@lang('messages.Processing...')</strong>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label "></label>
							<div class="col-sm-6">
								<input type="hidden" name="tab_info" class="tab_info" value="">
								<button type="submit" onclick="HideButton('Submit_button','Loading_Img');" onsubmit="HideButton('Submit_button','Loading_Img');" class="btn btn-primary mr5" title="Save">@lang('messages.Save')</button>
							</div><!-- panel-footer -->
						</div>
						{!!Form::close();!!} 
					</div>
					<div class="tab-pane" id="vendor_info">
						<legend>@lang('messages.Order Information')</legend>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.Order Id')</label>
							<div class="col-sm-9">{!! $data[0]->order_key_formated !!}</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.Order Key')</label>
							<div class="col-sm-9">{!! $data[0]->order_key !!}</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.Invoice Id')</label>
							<div class="col-sm-9">{!! $data[0]->invoice_id !!}</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.Order Status')</label>
							<div class="col-sm-9">{!! $data[0]->order_status !!}</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.Order Date')</label>
							<div class="col-sm-9">{!! $data[0]->ordered_date !!}</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.Payment Type')</label>
							<div class="col-sm-9">{!! $data[0]->name !!}</div>
						</div>
						<div id="order_details">
							<h4>@lang('messages.Order Items')</h4>
							<table class="table table-bordered">
								<thead>
									<tr>
										<th class="text-left">@lang('messages.Product')</td>
										<th class="text-left">@lang('messages.Quantity')</td>
										<th class="text-left">@lang('messages.Unit Price')</td>
										<th class="text-left">@lang('messages.Total')</td>
									</tr>
								</thead>
								<tbody>
								<?php $sub_total = 0; 
								foreach($items_data as $items){ 
									$sub_total += $items->item_cost*$items->item_unit; ?>
									<tr>
										<td class="text-left">{{ucfirst($items->product_name)}}</td>
										<td class="text-right">{{$items->item_unit}}</td>
										<?php if($currency_side == 1) { ?>
										      <td class="text-left"><?php echo $data[0]->currency_code." ".$items->item_cost; ?></td>
										      <td class="text-left"><?php echo $data[0]->currency_code." ".($items->item_cost*$items->item_unit); ?></td>
										<?php } else { ?>
										      <td class="text-left"><?php echo $items->item_cost." ".$data[0]->currency_code; ?></td>
										      <td class="text-left"><?php echo ($items->item_cost*$items->item_unit)." ".$data[0]->currency_code; ?></td>
										<?php } ?>
									</tr>
								<?php }	 if($currency_side == 1) { ?>
									  <tr><td class="text-right" colspan="3">@lang('messages.Sub Total'):</td><td class="text-left"><?php echo $data[0]->currency_code." ".$sub_total; ?></td></tr>
									  <tr><td class="text-right" colspan="3">@lang('messages.Delivery Fees'):</td><td class="text-left"><?php echo $data[0]->currency_code." ".$data[0]->delivery_charge;?></td></tr>
									  <tr><td class="text-right" colspan="3">@lang('messages.Tax'):</td><td class="text-left"><?php echo $data[0]->currency_code." ".$data[0]->service_tax; ?></td></tr>
									  <tr><td class="text-right" colspan="3">@lang('messages.Total'):</td><td class="text-left"><?php echo $data[0]->currency_code." ".$data[0]->total_amount; ?></td></tr>
								<?php } else { ?>
										<tr><td class="text-right" colspan="3">@lang('messages.Sub Total'):</td><td class="text-left"><?php echo $sub_total." ".$data[0]->currency_code; ?></td></tr>
										<tr><td class="text-right" colspan="3">@lang('messages.Delivery Fees'):</td><td class="text-left"><?php echo $data[0]->delivery_charge." ".$data[0]->currency_code;?></td></tr>
										<tr><td class="text-right" colspan="3">@lang('messages.Tax'):</td><td class="text-left"><?php echo $data[0]->service_tax." ".$data[0]->currency_code; ?></td></tr>
										<tr><td class="text-right" colspan="3">@lang('messages.Total'):</td><td class="text-left"><?php echo $data[0]->total_amount." ".$data[0]->currency_code; ?></td></tr>
								<?php } ?>
								</tbody>
								
							</table>
						</div>
					</div>
					<div class="tab-pane" id="delivery_info"> 
						<legend>@lang('messages.Customer Information')</legend>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.User Name')</label>
							<div class="col-sm-9">{!! $data[0]->username !!}</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.Email')</label>
							<div class="col-sm-9">{!! $data[0]->email !!}</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.First Name')</label>
							<div class="col-sm-9"><?php echo (trim($data[0]->first_name) != '')?$data[0]->first_name:'-';?></div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.Last Name')</label>
							<div class="col-sm-9"><?php echo (trim($data[0]->last_name) != '')?$data[0]->last_name:'-';?></div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.Mobile')</label>
							<div class="col-sm-9">{!! $data[0]->mobile !!}</div>
						</div>
					</div>
					<div class="tab-pane" id="contact_info"> 
						<legend>@lang('messages.Store and Delivery Information')</legend>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.Delivery Address')</label>
							<div class="col-sm-9">{!! ($data[0]->address)?$data[0]->address:'-' !!}</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.Delivery Instructions')</label>
							<div class="col-sm-9"><?php echo date("d F, l", strtotime($data[0]->delivery_date)); ?></div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.Delivery Slot')</label>
							<div class="col-sm-9">{!! $data[0]->start_time !!}-{!! $data[0]->end_time !!}</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.Delivery Instructions')</label>
							<div class="col-sm-9">{!! $data[0]->delivery_instructions !!}</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.Vendor Name')</label>
							<div class="col-sm-9">{!! $data[0]->vendor_name !!}</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.Outlet Name')</label>
							<div class="col-sm-9">{!! $data[0]->outlet_name !!}</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">@lang('messages.Outlet Address')</label>
							<div class="col-sm-9">{!! $data[0]->contact_address !!}</div>
						</div>
					</div>
				</div>
			</div>
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
	@endif
});
$(".login_info").on("click", function(){
	$('.tab_info').val('login_info');
	$('#delivery_info').hide();
	$('#vendor_info').hide();
	$('#login_info').show();
	$('#contact_info').hide();
});
$(".vendor_info").on("click", function(){
	$('.tab_info').val('vendor_info');
	$('#delivery_info').hide();
	$('#vendor_info').show();
	$('#login_info').hide();
	$('#contact_info').hide();
	initialize();
});
$(".delivery_info").on("click", function(){
	$('.tab_info').val('delivery_info');
	$('#delivery_info').show();
	$('#vendor_info').hide();
	$('#login_info').hide();
	$('#contact_info').hide();
});
$(".contact_info").on("click", function(){
	$('.tab_info').val('contact_info');
	$('#delivery_info').hide();
	$('#vendor_info').hide();
	$('#login_info').hide();
	$('#contact_info').show();
});
$(window).load(function(){
	$('form').preventDoubleSubmission();	
});
</script>
@endsection
