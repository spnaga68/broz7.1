@extends('layouts.vendors')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/dataTables.min.js') }}"></script>
<script src="https://cdn.datatables.net/buttons/1.0.3/js/dataTables.buttons.min.js"></script>
<link href="{{ URL::asset('assets/admin/base/css/dataTables.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<div class="pageheader">
<div class="media">
	<div class="pageicon pull-left">
		<i class="fa fa-home"></i>
	</div>
	<div class="media-body">
		<ul class="breadcrumb">
			<li><a href="{{ URL::to('vendors/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Vendors')</a></li>
			<li>@lang('messages.Notifications')</li>
		</ul>
		<h4>@lang('messages.Notifications')</h4>
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
	<div class="admin_sucess_common" id="read_msg" style="display:none;">
		<div class="admin_sucess">
			<div class="alert alert-info" id="read_stauts_msg"></div>
		</div>
	</div>
	<input type="hidden" name="_token" value="{{ csrf_token() }}">
	<table id="NotificationTable" class="table table-striped table-bordered responsive">
		<thead>
			<tr class="headings">
				<th>@lang('messages.S.No')</th> 
				<th>@lang('messages.Message')</th>
				<th>@lang('messages.Read Status')</th>
				<th>@lang('messages.Created Date')</th>
				<th>@lang('messages.Actions')</th> 
			</tr>
		</thead>
	</table>
	<?php /** echo $languages->render(); */ ?>
</div>
<script>
	$(function() {
		$('#NotificationTable').DataTable({
			processing: true,
			serverSide: false,
			responsive: true,
			autoWidth:false,
			dom: 'Blfrtip',
			buttons:
			[
				'excel',
				'csv',
				'pdf',
			],
			ajax: '{!! route('ajaxStoreNotificationList.data') !!}',
			"order": [],
			"columnDefs": [ {
				"targets"  : 'no-sort',
				"orderable": false,
				"searchable":true,
				"pagingType": "full"
			  
			}],
			columns: [
				{ data: 'notification_id', name: 'notification_id',orderable: false, searchable: false},
				{ data: 'message', name: 'message' },
				{ data: 'read_status', name: 'read_status' },
				{ data: 'created_date', name: 'created_date' },
				{ data: 'action', name: 'action', orderable: false, searchable: false}
			],
		});
	});
	function change_read_status(notifications_id)
	{
		var token, url, data;
		token = $('input[name=_token]').val();
		url   = '{{url('vendors/read_notifications')}}';
		data  = {cid: notifications_id};
		$.ajax({
			url: url,
			headers: {'X-CSRF-TOKEN': token},
			data: data,
			type: 'POST',
			datatype: 'JSON',
			success: function (resp) {console.log(resp);
				if(resp.data==1)
				{
					$('.notify-'+notifications_id).html('-');
					$('#read_msg').show();
					$('#read_stauts_msg').show().html('<?php echo trans('messages.Notification read status changed successfully');?>');
					$('.n_status_'+notifications_id).removeClass('label-danger').html('<?php echo trans('messages.Read');?>').addClass('label label-success');
				}
			}
		});
    }
</script>
@endsection
