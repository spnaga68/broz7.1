@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/dataTables.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/export/dataTables.buttons.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/export/jszip.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/export/pdfmake.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/export/vfs_fonts.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/export/buttons.html5.min.js') }}"></script>
<link href="{{ URL::asset('assets/admin/base/css/dataTables.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/admin/base/plugins/export/buttons.dataTables.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<div class="pageheader">
	<div class="media">
		<div class="pageicon pull-left">
			<i class="fa fa-home"></i>
		</div>
		<div class="media-body">
			<ul class="breadcrumb">
				<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
				<li>@lang('messages.Vendors')</li>
			</ul>
			<h4>@lang('messages.Vendors')</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->
	<!-- will be used to show any messages -->
	@if (Session::has('message'))
		<div class="admin_sucess_common">
	<div class="admin_sucess">
		<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div></div></div>
	@endif
<div class="contentpanel">
	@if (hasTask('vendors/create_vendor'))
		<div class="buttons_block pull-right">
			<div class="btn-group mr5">
				<a class="btn btn-primary tip" href="{{ URL::to('vendors/create_vendor') }}" title="Add New">@lang('messages.Add New')</a>
			</div>
		</div>
	@endif
	<table id="vendor-table" class="table table-striped table-bordered responsive">
		<thead>
			<tr class="headings">
				<th>@lang('messages.S.no')</th> 
				<th>@lang('messages.Vendor Name')</th> 
				<th>@lang('messages.First Name')</th> 
				<th>@lang('messages.Email')</th> 
				<th>@lang('messages.Phone Number')</th> 
				<th>@lang('messages.Created date')</th>
				<th>@lang('messages.Updated Date')</th>
				<th>@lang('messages.Status')</th>
				<?php if(hasTask('vendors/edit_vendor')) { ?>
					<th>@lang('messages.Actions')</th>
				<?php } ?>
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

<script>
$(function() {
    $('#vendor-table').DataTable({
		dom: 'Blfrtip',
		buttons: [
			{
				extend: 'excel',
				footer: false,
				title:'Vendors',
				text:'Export',
				exportOptions: {
					 columns: [0,1,2,3,4,5,6,7,8]
				 }
			}
		],
        processing: true,
        serverSide: true,
		responsive: true,
		autoWidth:false,
        ajax: '{!! route('ajaxvendor.data') !!}',
        "order": [],
		"columnDefs": [ {
		  "targets"  : 'no-sort',
		  "orderable": false,
		}],
        columns: [
			{ data: 'id', name: 'vendors.id',orderable: false },
			{ data: 'vendor_name', name: 'vendor_name',searchable: true },
			{ data: 'first_name', name: 'first_name',searchable: true },
			{ data: 'email', name: 'email', searchable: true},
			{ data: 'phone_number', name: 'phone_number', searchable: true},
            { data: 'created_date', name: 'created_date',searchable: false },
			{ data: 'modified_date', name: 'modified_date',searchable:false},
			{ data: 'active_status', name: 'active_status',searchable: true},
			<?php if(hasTask('vendors/edit_vendor')) { ?>
            { data: 'action', name: 'action', orderable: false, searchable: false}
			<?php } ?>
        ],
    });
});
</script>
@endsection
