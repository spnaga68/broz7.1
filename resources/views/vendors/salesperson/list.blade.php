@extends('layouts.vendors')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/dataTables.min.js') }}"></script>
<script src="https://cdn.datatables.net/buttons/1.0.3/js/dataTables.buttons.min.js"></script>
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
				<li>@lang('messages.Salesperson')</li>

			</ul>
			<h4>@lang('messages.Salesperson')</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->

<div class="contentpanel">
 		<div class="buttons_block pull-right">
			<div class="btn-group mr5">
				<a class="btn btn-primary tip" href="{{ URL::to('vendor/create_salesperson') }}" title="Add New"><i class="fa fa-plus"> </i> @lang('messages.Add New')</a>
			</div>
		</div>
	@if (Session::has('message'))
		<div class="admin_sucess_common">
			<div class="admin_sucess">
				<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
			</div>
		</div>
	@endif
	<table id="salespersonTable" class="table table-striped table-bordered responsive">
		<thead>
			<tr class="headings">
				<th>@lang('messages.S.No')</th> 
				<th>@lang('messages.SalesPerson Name')</th>
				<th>@lang('messages.Email')</th>
				<th>@lang('messages.Registered On')</th>
				<th>@lang('messages.Updated Date')</th>
				<th>@lang('messages.Status')</th>
				<th>@lang('messages.Is Verified')</th>
				<th>@lang('messages.Actions')</th>
			</tr>
		</thead>

		<tbody>
			<tr>
				<td class="empty-text" colspan="8" style="background-color: #fff!important;">
					<div class="list-empty-text"> @lang('messages.No records found.') </div>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<script type="text/javascript"> 
   $(function() {
        $('#salespersonTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth:false,
            ajax: '{!! route('listvendorsalespersonAjax.data') !!}',
            "order": [],
            "columnDefs": [ {
                "targets"  : 'no-sort',
				"orderable": true,
				"searchable":true,
				"pagingType": "full",
				'exportable':false
            }],
            dom: 'Blfrtip',
            buttons: [
                {
                    extend: 'excel',
                    text: 'Export',
                    title: 'salesperson',
                    footer: false,
                    exportOptions: {
                        columns: [0,1,2,3,4,5,6]
                    }
                }
            ],
            columns: [
               	{ data: 'id', name: 'id',orderable: false },
                { data: 'first_name', name: 'first_name',searchable:true },
                { data: 'email', name: 'email',searchable:true },
                { data: 'created_date', name: 'created_date',searchable:true },
                { data: 'modified_date', name: 'modified_date' },
                { data: 'active_status', name: 'active_status' },
                { data: 'is_verified', name: 'is_verified' },
                { data: 'action', name: 'action', orderable: false, searchable: false}
            ],
        });
    });
</script>
@endsection
