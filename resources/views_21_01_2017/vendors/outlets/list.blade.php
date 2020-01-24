@extends('layouts.vendors')
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
			<li><a href="{{ URL::to('vendors/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Vendors')</a></li>
			<li>@lang('messages.Outlets')</li>
		</ul>
		<h4>@lang('messages.Outlets')</h4>
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

<div class="buttons_block pull-right">
<div class="btn-group mr5">
<a class="btn btn-primary tip" href="{{ URL::to('vendor/create_outlet') }}" title="Add New">@lang('messages.Add New')</a>
</div>
</div>

 <table id="vendor-table" class="table table-striped table-bordered responsive">
    <thead>
        <tr class="headings">
			
            <th>@lang('messages.S.no')</th>
			<th>@lang('messages.Outlet Name')</th> 
			<th>@lang('messages.Contact Email')</th> 
            <th>@lang('messages.Contact Phone')</th> 
            <th>@lang('messages.Contact Address')</th> 
            <th>@lang('messages.Created date')</th>
			<th>@lang('messages.Updated Date')</th>
			<th>@lang('messages.Status')</th> 
            <th>@lang('messages.Actions')</th>
        </tr>
    </thead>
</table>
</div>

<script>
$(function() {
    $('#vendor-table').DataTable({
		dom: 'Blfrtip',
		buttons: [
			/*{
				extend: 'csv',
				footer: false,
				exportOptions: {
					 columns: [0,1,2,3,4,5,6]
				 }
			},
			{
				extend: 'pdf',
				footer: false,
				exportOptions: {
					 columns: [0,1,2,3,4,5,6]
				 }
			},*/
			{
				extend: 'excel',
				footer: false,
				title:'Outlets',
				text:'Export',
				exportOptions: {
					 columns: [0,1,2,3,4,5,6]
				 }
			}
		],
        processing: true,
        serverSide: true,
		responsive: true,
		autoWidth:false,
        ajax: '{!! route('anyajaxbranch.data') !!}',
        "order": [],
		"columnDefs": [ {
		  "targets"  : 'no-sort',
		  "orderable": false,
		}],
        columns: [
			{ data: 'id', name: 'vendors.id',orderable: false },
			{ data: 'outlet_name', name: 'outlet_name',searchable:true },
			{ data: 'contact_email', name: 'contact_email',searchable:true  },
			{ data: 'contact_phone', name: 'contact_phone',searchable:true  },
			{ data: 'contact_address', name: 'contact_address',searchable:true  },
            { data: 'created_date', name: 'created_date' },
			{ data: 'modified_date', name: 'modified_date' },
			{ data: 'active_status', name: 'active_status' },
            { data: 'action', name: 'action', orderable: false, searchable: false}
        ],
    });
});
</script>
@endsection
