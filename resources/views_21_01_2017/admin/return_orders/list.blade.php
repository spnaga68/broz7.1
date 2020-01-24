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
				<li>@lang('messages.Return Orders List')</li>
			</ul>
			<h4>@lang('messages.Return Orders List')</h4>
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
	{!!Form::open(array('url' => 'orders/return_orders', 'method' => 'get','class'=>'tab-form attribute_form','id'=>'sales_order_search_form','files' => true));!!}
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
							<option value="">@lang('messages.No vendor found')</option>
						@endif
					</select>
				</div>
			</div>
			<div class="col-md-6 padding0">
				<label class="col-sm-3 control-label">@lang('messages.Outlet Name')</label>
				<div class="col-sm-9">
					<select name="outlet" id="outlet_id" class="select2-offscreen"  style="width:100%;">
						<option value="">@lang('messages.Select Outlet')</option>
						<?php if(Input::get('outlet')){
							$outlet = getOutletList(Input::get('vendor'));?>
							@if(count($outlet) > 0)
								@foreach($outlet as $list)
									<option value="{{$list->id}}" <?php echo (Input::get('outlet')==$list->id)?'selected="selected"':''; ?> >{{ ucfirst($list->outlet_name) }}</option>
								@endforeach
							@else
								<option value="">@lang('messages.No outlet found')</option>
							@endif
						<?php } ?>
					</select>
				</div>
			</div>
		</div>
		<input type="hidden" name="search" value="">
		<div class="form-group">
			<button class="btn btn-primary mr5" title="@lang('messages.Save')">@lang('messages.Search')</button>
			<button type="reset" title="@lang('messages.Reset')" class="btn btn-default" onclick="window.location='{{ url('orders/return_orders') }}'">@lang('messages.Reset')</button>
			<?php if(Input::get('search')){ ?>
				<button type="reset" title="@lang('messages.Export')" class="btn btn-default" onclick="window.location='{{ url('orders/return_orders?export=1&from='.Input::get('from').'&to='.Input::get('to').'&vendor='.Input::get('vendor').'&outlet='.Input::get('outlet').'&search='.Input::get('search')) }}'">@lang('messages.Export')</button>
			<?php } ?>
		</div>
	{!!Form::close();!!}
	<div class="dataTables_wrapper">
	<table id="return_orders" class="table table-striped table-bordered responsive">
		<thead>
			<tr class="headings">
				<th>@lang('messages.S.No')</th> 
				<th>@lang('messages.Order Id')</th> 
				<th>@lang('messages.Customer Name')</th>
				<th>@lang('messages.Vendor Name')</th>
				<th>@lang('messages.Outlet Name')</th>
				<th>@lang('messages.Return Reason')</th>  
				<th>@lang('messages.Return Comments')</th>  
				<th>@lang('messages.Return Status')</th>
				<th>@lang('messages.Return Action')</th>
				<th>@lang('messages.Created Date')</th>
				<th>@lang('messages.Updated Date')</th>
				<?php if(hasTask('orders/return_orders_view')){ ?>
					<th>@lang('messages.Actions')</th>
				<?php } ?>
			</tr>
		</thead>
		@if (count($data) > 0 )
			<tbody>
				<?php $i=1; ?>
				@foreach($data as $key => $value)
					<tr> 
						<td>{{$value->id}}</td>
						<td>{{$value->order_id}}</td>
						<td>{{ucfirst($value->username)}}</td>
						<td>{{ucfirst($value->vendor_name)}}</td>
						<td>{{ucfirst($value->outlet_name)}}</td>
						<td>{{($value->return_reason_name != '')?ucfirst($value->return_reason_name):'-'}}</td>
						<td><?php echo ($value->return_comments != '')?trim(substr(wordwrap(ucfirst($value->return_comments),25,"<br>\n"),0,50)):'-';?></td>
						<td>{{ucfirst($value->return_status_name)}}</td>
						<td>{{ucfirst($value->return_action_name)}}</td>
						<td>{{$value->created_at}}</td>
						<td>
							@if($value->modified_at != '')
							{{$value->modified_at}}
							@else - @endif
						</td>
						
						<?php if(hasTask('orders/return_orders_view')){ ?>
							<td>
								<div class="btn-group">
									<a href="{{URL::to('orders/return_orders_view/'.$value->id)}}" class="btn btn-xs btn-white" title="@lang('messages.View')"><i class="fa fa-file-text-o"></i>@lang("messages.View")</a>
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
					<td class="empty-text" colspan="12" style="background-color: #fff!important;">
						<div class="list-empty-text"> @lang('messages.No records found.') </div>
					</td>
				</tr>
			</tbody>
		@endif 
	</table>
    <?php /** echo $languages->render(); */ ?>
</div>
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
        $('#vendor_id').change(function(){
			var cid, token, url, data;
			token = $('input[name=_token]').val();
			cid = $('#vendor_id').val();
			url = '{{url('list/OutletList')}}';
			data = {cid: cid};
			$.ajax({
				url: url,
				headers: {'X-CSRF-TOKEN': token},
				data: data,
				type: 'POST',
				datatype: 'JSON',
				success: function (resp) {
					$('#outlet_id').empty();
					if(resp.data=='')
					{
						$('#outlet_id').append($("<option></option>").attr("value","").text('No data there..')); 
					}
					else {
						$.each(resp.data, function(key, value) {
							$('#outlet_id').append($("<option></option>").attr("value",value['id']).text(value['outlet_name'])); 
						});
					}
				}
			});
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

