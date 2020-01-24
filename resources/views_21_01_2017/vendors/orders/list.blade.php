@extends('layouts.vendors')
@section('content')

<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/jquery-ui-1.10.3.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/moment-with-locales.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/bootstrap-datetimepicker.min.js') }}"></script>
<link href="{{ URL::asset('assets/admin/base/css/bootstrap-datetimepicker.min.css') }}" media="all" rel="stylesheet" type="text/css" /> 
<link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>

<div class="pageheader">
	<div class="media">
		<div class="pageicon pull-left">
			<i class="fa fa-home"></i>
		</div>
		<div class="media-body">
			<ul class="breadcrumb">
				<li><a href="{{ URL::to('vendors/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Vendors')</a></li>
				<li>@lang('messages.Orders')</li>
			</ul>
			<h4>@lang('messages.Orders')</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->
<div class="contentpanel">
	@if (Session::has('message'))
		<div class="admin_sucess_common">
			<div class="admin_sucess">
				<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
			</div>
		</div>
	@endif
	{!!Form::open(array('url' => 'vendors/orders/index', 'method' => 'get','class'=>'tab-form attribute_form','id'=>'sales_order_search_form','files' => true));!!}
		<div class="form-group">
			<div class="col-md-6 padding0">
				<label class="col-sm-3 control-label padding_left0">@lang('messages.From')</label>
				<div class="col-sm-9">
					<input type="text" name="from" value="<?php echo Input::get('from'); ?>" autocomplete="off" id="datepicker" placeholder="mm/dd/yyyy" class="form-control"  />
				</div>
			</div>
			<div class="col-md-6 padding0">
				<label class="col-sm-3 control-label">@lang('messages.To')</label>
				<div class="col-sm-9">
					<input type="text"  name="to" value="<?php echo Input::get('to'); ?>" autocomplete="off" id="datepicker1"  placeholder="mm/dd/yyyy" class="form-control"  />
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-md-6 padding0">
				<label class="col-sm-3 control-label padding_left0">@lang('messages.Amount from')</label>
				<div class="col-sm-9">
					<input type="text" name="from_amount" onkeypress="return isNumber(event)" value="<?php echo Input::get('from_amount'); ?>" autocomplete="off" placeholder="@lang('messages.Amount from')" class="form-control"  />
				</div>
			</div>
			<div class="col-md-6 padding0">
				<label class="col-sm-3 control-label">@lang('messages.Amount to')</label>
				<div class="col-sm-9">
					<input type="text" name="to_amount" onkeypress="return isNumber(event)" value="<?php echo Input::get('to_amount'); ?>" autocomplete="off" placeholder="@lang('messages.Amount to')" class="form-control" />
				</div>
			</div>
			<input type="hidden" name="search" value="1" >
		</div>
		<div class="form-group">
			<div class="col-md-6 padding0">
				<label class="col-sm-3 control-label padding_left0">@lang('messages.Order Status')</label>
				<div class="col-sm-9">
					<select name="order_status" class="select2-offscreen"  style="width:100%;">
						<option value="">@lang('messages.Choose one')</option>
						@if(count($order_status) > 0)
							@foreach($order_status as $list)
								<option value="{{$list->id}}" <?php echo (Input::get('order_status')==$list->id)?'selected="selected"':''; ?> >{{ucfirst($list->name)}}</option>
							@endforeach
						@else
							<option value="">@lang('messages.No order status found')</option>
						@endif
					</select>
				</div>
			</div>
			<div class="col-md-6 padding0">
				<label class="col-sm-3 control-label">@lang('messages.Payment Mode')</label>
				<div class="col-sm-9">
					<select name="payment_type"  class="select2-offscreen"  style="width:100%;">
						<option value="">@lang('messages.Choose one')</option>
						@if(count($payment_seetings) > 0 )
							@foreach($payment_seetings as $list)
								<option value="{{$list->id}}" <?php echo (Input::get('payment_type')==$list->id)?'selected="selected"':''; ?> >{{ucfirst($list->name)}}</option>
							@endforeach
						@else
							<option value="">@lang('messages.No payment mode found')</option>
						@endif
					</select>
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-md-6 padding0">
				<label class="col-sm-3 control-label padding_left0">@lang('messages.Outlet Name')</label>
				<div class="col-sm-9">
					<select name="outlet" id="outlet_id" class="select2-offscreen"  style="width:100%;">
						<option value="">@lang('messages.Select Outlet')</option>
						<?php $outlet = getOutletList(Session::get('vendor_id'));?>
						@if(count($outlet) > 0 )
							@foreach($outlet as $list)
								<option value="{{$list->id}}" <?php echo (Input::get('outlet')==$list->id)?'selected="selected"':''; ?> >{{ ucfirst($list->outlet_name) }}</option>
							@endforeach
						@else
							<option value="">@lang('messages.No outlet found')</option>
						@endif
					</select>
				</div>
			</div>
		</div>
		<div class="form-group">
			<button class="btn btn-primary mr5" title="@lang('messages.Save')">@lang('messages.Search')</button>
			<button type="reset" title="@lang('messages.Reset')" class="btn btn-default" onclick="window.location='{{ url('vendors/orders/index') }}'">@lang('messages.Reset')</button>
			<?php if(Input::get('search')){ ?>
				<button type="reset" title="@lang('messages.Export')" class="btn btn-default" onclick="window.location='{{ url('vendors/orders/index?export=1&from='.Input::get('from').'&to='.Input::get('to').'&from_amount='.Input::get('from_amount').'&to_amount='.Input::get('to_amount').'&order_status='.Input::get('order_status').'&payment_type='.Input::get('payment_type').'&vendor='.Input::get('vendor').'&outlet='.Input::get('outlet').'&search='.Input::get('search').'') }}'">@lang('messages.Export')</button>
			<?php } ?>
		</div>
	{!!Form::close();!!}
	<div class="dataTables_wrapper">
	<table id="orders" class="table table-striped table-bordered responsive">
		<thead>
			<tr class="headings">
				<th>@lang('messages.Order id')</th> 
				<th>@lang('messages.Name')</th>
				<th>@lang('messages.Outlet Name')</th>
				<th>@lang('messages.Payment Type')</th>
				<th>@lang('messages.Status')</th> 
				<th>@lang('messages.Total Amount')</th>
				<th>@lang('messages.Order Date')</th>
				<th>@lang('messages.Updated Date')</th>
				<th>@lang('messages.View')</th> 
			</tr>
		</thead>
		@if (count($orders) > 0 )
			<tbody>
				<?php $i=1;?>
				@foreach($orders as $key => $value)
					<tr>
						<td>{{$value->id}}</td>
						<td>{{ucfirst($value->user_name)}}</td>
						<td>{{ucfirst($value->outlet_name)}}</td>
						<td>{{ucfirst($value->payment_type)}}</td>
						<td>{{ucfirst($value->status_name)}}</td>
						<td>{{$value->total_amount.$value->currency_code}}</td>
						<td><?php echo wordwrap(date("d F, l", strtotime($value->created_date)),10,'<br>');?></td>
						<td>
							@if($value->modified_date != '')
								<?php echo wordwrap(date("d F, l", strtotime($value->modified_date)),10,'\n');?>
							@else - @endif
						</td>
						<td>
							<div class="btn-group">
								<a href="<?php echo URL::to("vendors/orders/info/".$value->id);?>" class="btn btn-xs btn-white" title="@lang('messages.View')"><i class="fa fa-eye"></i>&nbsp;@lang("messages.View")</a>
							</div>
						</td>
					</tr>
					<?php $i++; ?>
				@endforeach
			</tbody>
		@else
			<tbody>
				<tr>
					<td class="empty-text" colspan="7" style="background-color: #fff!important;">
						<div class="list-empty-text"> @lang('messages.No records found.') </div>
					</td>
				</tr>
			</tbody>
		@endif 
	</table>
	<?php echo $orders->render();?>
</div>
</div>
<script type="text/javascript"> 
    $(window).load(function(){
		$('select').select2();
        $('#datepicker').datetimepicker({
            maxDate: new Date(),
            sideBySide: true,
            useCurrent: false
		});
        $('#datepicker1').datetimepicker({
            maxDate: new Date(),
            sideBySide: true,
            useCurrent: false
        });
    });
    function isNumber(evt)
    {
		evt = (evt) ? evt : window.event;
		var charCode = (evt.which) ? evt.which : evt.keyCode;
		if (charCode > 31 && (charCode < 48 || charCode > 57))
		{
			return false;
		}
		return true;
	}
	
</script>
@endsection
