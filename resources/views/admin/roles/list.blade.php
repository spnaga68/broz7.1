@extends('layouts.admin')
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
				<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
				<li>@lang('messages.Roles')</li>
			</ul>
			<h4>@lang('messages.Roles')</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->
<div class="contentpanel">
	<?php if(hasTask('system/permission/create')){ ?>
		<div class="buttons_block pull-right">
			<div class="btn-group mr5">
				<a class="btn btn-primary tip" href="{{ URL::to('system/permission/create') }}" title="Add New">@lang('messages.Add New')</a>
			</div>
		</div>
	<?php } ?>
	@if (Session::has('message'))
		<div class="admin_sucess_common">
			<div class="admin_sucess">
				<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
			</div>
		</div>
	@endif
	<table id="roleTable" class="table table-striped table-bordered responsive">
		<thead>
			<tr class="headings">
				<th>@lang('messages.S.No')</th> 
				<th>@lang('messages.Role Name')</th>
				<th>@lang('messages.Status')</th>
				<?php if(hasTask('system/permission/edit')){ ?>
				<th>@lang('messages.Actions')</th>
				<?php } ?>
			</tr>
		</thead>
	</table>
</div>
<script>
$(function() {
    $('#roleTable').DataTable({
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
        ajax: '{!! route('listroleajax.data') !!}',
        "order": [],
		"columnDefs": [ {
			"targets"  : 'no-sort',
			"orderable": false,
			"searchable":true,
			"pagingType": "full"
		}],
        columns: [
			{ data: 'id', name: 'id',orderable: false },
			{ data: 'role_name',name: 'role_name'},
			{ data: 'active_status',name: 'active_status'},
			<?php if(hasTask('system/permission/edit')){ ?>
            { data: 'action', name: 'action', orderable: false, searchable: false}
            <?php } ?>
        ],
    });
    
});
</script>
@endsection
