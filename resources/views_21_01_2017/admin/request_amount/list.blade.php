@extends('layouts.admin')
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
				<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
				<li>@lang('messages.Amount Requests')</li>
			</ul>
			<h4>@lang('messages.Amount Requests')</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->
<!-- will be used to show any messages -->
<div class="contentpanel">
	@if (Session::has('message'))
		<div class="admin_sucess_common">
			<div class="admin_sucess">
				<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
			</div>
		</div>
	@endif
	{!!Form::open(array('url' => 'orders/fund_requests', 'method' => 'get','class'=>'tab-form attribute_form','id'=>'sales_order_search_form','files' => true));!!}
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
				<label class="col-sm-3 control-label padding_left0">@lang('messages.Vendor Name')</label>
				<div class="col-sm-9">
					<select name="vendor" id="vendor_id" class="select2-offscreen"  style="width:100%;">
						<option value="">@lang('messages.Select Vendor')</option>
						<?php $vendors = getVendorLists(); ?>
						@if(count($vendors) > 0 )
							@foreach($vendors as $list)
								<option value="{{$list->id}}" <?php echo (Input::get('vendor')==$list->id)?'selected="selected"':''; ?> >{{ucfirst($list->vendor_name)}}</option>
							@endforeach
						@else
							<option value="">@lang('No vendor found')</option>
						@endif
					</select>
				</div>
			</div>
			<input type="hidden" name="search" value="1" >
		</div>
		<div class="form-group">
			<button class="btn btn-primary mr5" title="@lang('messages.Save')">@lang('messages.Search')</button>
			<button type="reset" title="@lang('messages.Reset')" class="btn btn-default" onclick="window.location='{{ url('orders/fund_requests') }}'">@lang('messages.Reset')</button>
			<?php if(Input::get('search')){ ?>
				<button type="reset" title="@lang('messages.Export')" class="btn btn-default" onclick="window.location='{{ url('orders/fund_requests?export=1&from='.Input::get('from').'&to='.Input::get('to').'&search='.Input::get('search').'') }}'">@lang('messages.Export')</button>
			<?php } ?>
		</div>
	{!!Form::close();!!}
	<?php $currency_side   = getCurrencyPosition()->currency_side;$currency_symbol = getCurrency(); ?>
	<table id="vendor-table" class="table table-striped table-bordered responsive">
		<thead>
			<tr class="headings">
				<th>@lang('messages.S.no')</th>
				<th>@lang('messages.Vendor Name')</th> 
				<th>@lang('messages.Fund Request Id')</th> 
				<th>@lang('messages.Requested Amount')</th> 
				<th>@lang('messages.Previous Balance')</th> 
				<th>@lang('messages.Created date')</th>
				<th>@lang('messages.Updated Date')</th>
				<th>@lang('messages.Status')</th> 
				<?php if(hasTask('orders/approve_fund_status')) { ?>
					<th>@lang('messages.Actions')</th> 
				<?php } ?>
			</tr>
		</thead>
		@if (count($return_orders) > 0 )
			<tbody>
				<?php $i=1;?>
				@foreach($return_orders as $key => $value)
					<tr>
						<td>{{$value->id}}</td>
						<td>{{ucfirst($value->vendor_name)}}</td>
						<td>{{$value->unique_id}}</td>
						<?php if($currency_side == 1) { ?>
							<td>{{$currency_symbol.$value->request_amount}}</td>
							<td>{{$currency_symbol.$value->current_balance}}</td>
						<?php } else { ?>
							<td>{{$value->request_amount.$currency_symbol}}</td>
							<td>{{$value->current_balance.$currency_symbol}}</td>
						<?php } ?>
						<td>{{$value->created_date}}</td>
						<td>{{($value->modified_date != '')?$value->modified_date:'-'}}</td>
						<td>
							@if($value->approve_status==0)
								<span class="label label-warning" id="<?php echo 'approve_status_'.$value->id;?>">@lang('messages.Pending')</span>
							@elseif($value->approve_status==1)
								<span class="label label-success">@lang('messages.Completed')</span>
							@elseif($value->approve_status==2)
								<span class="label label-danger">@lang("messages.Cancelled")</span>
							@endif
						</td>
						<?php if(hasTask('orders/approve_fund_status')) { ?>
							<td>
								<div class="<?php echo 'request_amount_'.$value->id;?>">
									<select name="status" id="<?php echo 'fund_status_'.$value->id;?>" class="form-control" onchange="approve_fund_status(<?php echo $value->id.','.$value->vendor_id;?>)">
										<option <?php echo ($value->approve_status==0)?"selected='selected'":"";?> value="0">@lang('messages.Pending')</option>
										<option <?php echo ($value->approve_status==1)?"selected='selected'":"";?> value="1">@lang('messages.Completed')</option>
										<option <?php echo ($value->approve_status==2)?"selected='selected'":"";?> value="2">@lang('messages.Cancelled')</option>
									</select>
								</div>
							</td> 
						<?php } ?>
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
</div>

<script type="text/javascript">
	$(window).load(function(){
		$('select').select2();
        $('#datepicker').datetimepicker();
        $('#datepicker1').datetimepicker({
            
            useCurrent: false
        });
		 $("#datepicker").on("dp.change", function (e) {
            $('#datepicker1').data("DateTimePicker").minDate(e.date);
        });
        $("#datepicker1").on("dp.change", function (e) {
            $('#datepicker').data("DateTimePicker").maxDate(e.date);
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
	function approve_fund_status(cid,vid)
	{
		var token, url, data,status;
		status = $('#fund_status_'+cid).val();
		token = $('input[name=_token]').val();
		url = '{{url('orders/approve_fund_status')}}';
		data = {cid: cid,vid:vid,status:status};
		$('.request_amount'+cid).html('Loading..');
		$('#fund_status_'+cid).hide();
		$.ajax({
			url: url,
			headers: {'X-CSRF-TOKEN': token},
			data: data,
			type: 'POST',
			datatype: 'JSON',
			success: function (resp) {
				//console.log('in--'+resp.data);
				if(resp.data==1)
				{
					$('#fund_status_'+cid).hide();
					$('.request_amount'+cid).html('Success'); 
					if(status==1)
					{
						$('#approve_status_'+cid).html('Completed'); 
					}
					else if(status==2){
						$('#approve_status_'+cid).html('Cancelled'); 
					}
				}
			},
			error: function(error){
				console.log(error);
			}
		});
	}
</script>
@endsection
