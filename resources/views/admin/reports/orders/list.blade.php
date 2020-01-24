@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/datatables2.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/moment.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/bootstrap.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/bootstrap-datetimepicker.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/bootstrap-datetimepicker.min.js') }}"></script>
<link href="{{ URL::asset('assets/admin/base/css/datatables2.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/admin/base/css/bootstrap-datetimepicker.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/admin/base/css/dataTables.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/admin/base/plugins/export/buttons.dataTables.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/vfs_fonts.js') }}"></script>
<div class="pageheader">
	<div class="media">
		<div class="pageicon pull-left">
			<i class="fa fa-home"></i>
		</div>
		<div class="media-body">
			<ul class="breadcrumb">
				<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
				<li>@lang('messages.Reports')</li>
			</ul>
			<h4>@lang('messages.Order Reports')</h4>
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

	{!!Form::open(array('method' => 'POST','class'=>'tab-form attribute_form','id'=>'reports_order_form','files' => true));!!}
		<div class="form-group">
			<div class="col-md-6 padding0">
				<label class="col-sm-3 control-label padding_left0">@lang('messages.Date Start')</label>
				<div class="col-sm-9">
					<input type="text"  name="from" value="<?php echo Input::get('from'); ?>" autocomplete="off" id="datepicker" placeholder="mm/dd/yyyy"  class="form-control"  />
				</div>
			</div>
			<div class="col-md-6 padding0">
				<label class="col-sm-3 control-label">@lang('messages.Date End')</label>
				<div class="col-sm-9">
					<input type="text"  name="to" value="<?php echo Input::get('to'); ?>"  autocomplete="off" id="datepicker1"  placeholder="mm/dd/yyyy"  class="form-control"  />
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-md-6 padding0">
				<label class="col-sm-3 control-label padding_left0">@lang('messages.Order Status')</label>
				<div class="col-sm-9">
					<select name="order_status" id="order_status"  class="select2-offscreen"  style="width:100%;">
						@if(count($order_status) > 0)
							<option value="">@lang('messages.All')</option>
							@foreach($order_status as $list)
								<option value="{{$list->id}}" <?php echo (Input::get('order_status')==$list->id)?'selected="selected"':''; ?> >{{$list->name}}</option>
							@endforeach
						@else
							<option value="">@lang('messages.No order status found')</option>
						@endif
					</select>
				</div>
			</div>
			<div class="col-md-6 padding0">
				<label class="col-sm-3 control-label">@lang('messages.Group By')</label>
				<div class="col-sm-9">
					<select name="group_by" id="group_by" class="select2-offscreen" style="width:100%;">
						<option value="1" <?php echo (Input::get('group_by') == 1)?'selected="selected"':''; ?>>@lang('messages.Days')</option>
						<option value="2" <?php echo (Input::get('group_by') == 2)?'selected="selected"':''; ?>>@lang('messages.Weeks')</option>
						<option value="3" <?php echo (Input::get('group_by') == 3)?'selected="selected"':''; ?>>@lang('messages.Months')</option>
						<option value="4" <?php echo (Input::get('group_by') == 4)?'selected="selected"':''; ?>>@lang('messages.Years')</option>
				</select>
			</div>
		</div>

			</div>
		<div class="form-group">
            <div class="col-md-6 padding0">
                <label class="col-sm-3 control-label padding_left0">@lang('messages.Vendor Name')</label>
                <div class="col-sm-9">
                    <?php $vendors = getVendorLists();//echo '<pre>';print_r($vendors);die; ?>
                    <select name="vendor" id="vendor_id" class="select2-offscreen"  style="width:100%;">
                        @if(count($vendors) > 0)
                            <option value="">@lang('messages.Select Vendor')</option>
                            @foreach($vendors as $list)
                                <option value="{{$list->id}}" <?php echo (Input::get('vendor') == $list->id)?'selected="selected"':''; ?> >{{ucfirst($list->vendor_name)}}</option>
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

		<div class="form-group">
			<button type="submit" class="btn btn-primary mr5" title="@lang('messages.Save')">@lang('messages.Search')</button>
		</div>
	{!!Form::close();!!}

	<div class="vender_scroll_sec">
		<table id="ReportsOrdersTable" class="table table-striped table-bordered responsive">
			<thead>
				<tr class="headings">
					<th>@lang('messages.Date Start')</th> 
					<th>@lang('messages.Date End')</th>
					<th>@lang('messages.Vendor Name')</th>
					<th>@lang('messages.Outlet Name')</th>
					<th>@lang('messages.No. Orders')</th>
					<th>@lang('messages.No. Products')</th>
					<th>@lang('messages.Tax')</th>  
					<th>@lang('messages.Total Amount')</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="empty-text" colspan="7" style="background-color: #fff!important;">
						<div class="list-empty-text"> @lang('messages.No records found.') </div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<script> 
    $(window).load(function(){
		$('select').select2();
		$('#datepicker').datetimepicker();
        $('#datepicker1').datetimepicker({
            useCurrent: false //Important! See issue #1075
        });
        $("#datepicker").on("dp.change", function (e) {
            $('#datepicker1').data("DateTimePicker").minDate(e.date);
        });
        $("#datepicker1").on("dp.change", function (e) {
            $('#datepicker').data("DateTimePicker").maxDate(e.date);
        });
		/*
        $('#datepicker').datetimepicker({
            maxDate: new Date(),
            sideBySide: true,
            useCurrent: false
		});
        $('#datepicker1').datetimepicker({
            maxDate: new Date(),
            sideBySide: true,
            useCurrent: false
        });*/
		$('.dropdown-toggle').dropdown();
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
					console.log(resp.data);
                  //  $('#outlet_id').empty();
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
    $(function()
	{
		var row_count = $("#row_count").val();
		var oTable = $('#ReportsOrdersTable').DataTable({
		bFilter: false,
		dom: 'lBfrtip',
		buttons: [
            {
                extend: 'collection',
                text: 'Export',
				title: 'orders_reports',
                buttons: [
                    'copy',
                    'excel',
                   // 'csv',
                    'pdf',
                    'print'
                ]
            }
        ],
        exportOptions: {
			columns: ':visible'

		},
		lengthMenu: [[10,25, 100, -1], [10,25, 100, "All"]],
		pageLength: 10,
        processing: true,
        serverSide: true,
        responsive: true,
		autoWidth:  true,
        ajax: {
            //url: '{{ URL::to('reports/report-order-list') }}',
            url: '{!! route('report-order-list.data') !!}',

			type: 'POST',
            data: function (d) {
                d.from         = $('input[name=from]').val();
                d.to           = $('input[name=to]').val();
                d.group_by     = $('#group_by').val();
                d.order_status = $('#order_status').val();
				d.vendor = $('#vendor_id').val();
				d.outlet = $('#outlet_id').val();
            },
			headers:{
				'X-CSRF-TOKEN': $('input[name=_token]').val()
			}
        },
		order: [],
           columnDefs: [ {
               targets  : 'no-sort',
               orderable: false,
           }],
        columns: [
				
				{ data: 'date_start', name: 'date_start'},

				{ data: 'date_end', name: 'date_end'},
				{ data: 'vendor_name', name: 'vendor_name'},
				{ data: 'outlet_name', name: 'outlet_name'},
				{ data: 'orders_count', name: 'orders_count'},
				{ data: 'quantity_count', name: 'quantity_count' },
				{ data: 'tax_total', name: 'tax_total' },
				{ data: 'total', name: 'total' },
			],
		
		});
		$('#reports_order_form').on('submit', function(e) {
			oTable.draw();
			e.preventDefault();
		});
	});
	$('.dropdown-toggle').dropdown();
</script>
@endsection
