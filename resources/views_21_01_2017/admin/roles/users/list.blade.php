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
				<li>@lang('messages.Role Users')</li>
			</ul>
			<h4>@lang('messages.Role Users Mangagement')</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->
<div class="contentpanel">
	<?php if(hasTask('permission/usercreate')){ ?>
		<div class="buttons_block pull-right">
			<div class="btn-group mr5">
				<a class="btn btn-primary tip" href="{{ URL::to('permission/usercreate') }}" title="Add New">@lang('messages.Add New')</a>
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
	<table id="roleuserTable" class="table table-striped table-bordered responsive">
		<thead>
			<tr class="headings">
				<th>@lang('messages.S.No')</th>
				<th>@lang('messages.Email')</th>
				<th>@lang('messages.Role')</th>
				<?php if(hasTask('permission/users/edit')) { ?>
				<th>@lang('messages.Actions')</th>
				<?php } ?>
			</tr>
		</thead>
	</table>
</div>
<script>
$(function() {
    $('#roleuserTable').DataTable({
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
        ajax: '{!! route('listroleuserajax.data') !!}',
        "order": [],
		"columnDefs": [ {
			"targets"  : 'no-sort',
			"orderable": false,
			"searchable":true,
			"pagingType": "full"
		}],
        columns: [
			{ data: 'ruid', name: 'ruid',orderable: false },
			{ data: 'email',name: 'email'},
			{ data: 'role_name',name: 'role_name'},
			<?php if(hasTask('permission/users/edit')) { ?>
            { data: 'action', name: 'action', orderable: false, searchable: false}
            <?php } ?>
        ],
    });
});
</script>
@endsection
