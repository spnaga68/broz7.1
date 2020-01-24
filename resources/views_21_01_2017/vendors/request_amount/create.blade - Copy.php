@extends('layouts.vendors')
@section('content')
<!-- Nav tabs -->
<div class="pageheader">
	<div class="media">
		<div class="pageicon pull-left">
			<i class="fa fa-home"></i>
		</div>
		<div class="media-body">
			<ul class="breadcrumb">
				<li><a href="{{url('vendors/dashboard')}}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Vendors')</a></li>
				<li>@lang('messages.Request Amount')</li>
			</ul>
			<h4>@lang('messages.New Request Amount')</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->

<div class="contentpanel">
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
	<ul class="nav nav-tabs"></ul>
	{!!Form::open(array('url' => 'createamount', 'method' => 'post','class'=>'tab-form attribute_form','id'=>'createamount'));!!}
		<table id="vendor-table" class="table table-striped table-bordered responsive">
			<thead>
				<tr class="headings">
					<th><input type="checkbox" id="select_all" name="orders_id[]" value="">@lang('messages.Select All')</th>
					<th>@lang('messages.Order Id')</th>
					<th>@lang('messages.Created Date')</th>
					<th>@lang('messages.Total Amount')</th> 
					<th>@lang('messages.Admin Amount')</th>
					<th>@lang('messages.Delivery Charges')</th>
					<th>@lang('messages.Service Tax')</th>
					<th>@lang('messages.Vendor Amount')</th>
				</tr>
			</thead>
			@if (count($remaining_orders) > 0 )
				<tbody>
					@foreach($remaining_orders as $key => $value)
						<tr>
							<td><input type="checkbox" class="order_id" name="orders_id[]" onclick="checkbox_action(this)" value="{{$value->id}}"></td>
							<td>{{$value->id}}</td>
							<td>{{$value->created_date}}</td>
							<td>{{$value->total_amount}}</td>
							<td>{{$value->admin_commission}}</td>
							<td>{{$value->delivery_charge}}</td>
							<td>{{$value->service_tax}}</td>
							<td>{{$value->vendor_commission}}</td>
						</tr>
					@endforeach
					<tr>
						<td colspan="7" style="text-align:right"><strong>@lang('messages.Total Pending Amount')</strong></td>
						<td id="total_amount">0</td>
					</tr>
				</tbody>
			@endif
		</table>
		@if (count($remaining_orders) > 0 )
			<div class="panel-footer">
				<button class="btn btn-primary mr5" id="request_fund" title="@lang('messages.Request Fund')">@lang('messages.Request Fund')</button>
			</div>
		@endif
		<input type="hidden" name="total_amount_hidden" value="" id="total_amount_hidden">
	{!!Form::close();!!}
</div>
<script type="text/javascript">
	$("#select_all").change(function(){
		$(".order_id").prop('checked', $(this).prop("checked"));
	});
	function checkbox_action(checkbox)
	{
		//$('.order_id').change(function(){
		var total_amt = $('#total_amount_hidden').val();
		console.log(checkbox.attr('value'));
		if(false == $(this).prop("checked"))
		{
			$("#select_all").prop('checked', false);
			//new_total = total - this.val();
		}
		if ($('.order_id:checked').length == $('.order_id').length )
		{
			$("#select_all").prop('checked', true);
			//new_total = total + vendor_amount;
		}
		//~ $('#total_amount_hidden').val(new_total);
		//});
	}
	$('#request_fund').click(function(){
		
	});
</script>
@endsection
