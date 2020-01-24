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
				<li>@lang('messages.Blog')</li>
			</ul>
			<h4>@lang('messages.Blog')</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->

<div class="contentpanel">
	@if(hasTask('admin/blog/create'))
		<div class="buttons_block pull-right">
			<div class="btn-group mr5">
				<a class="btn btn-primary tip" href="{{ URL::to('admin/blog/create') }}" title="Add New">@lang('messages.Add New')</a>
			</div>
		</div>
	@endif
    @if (Session::has('message'))
		<div class="admin_sucess_common">
			<div class="admin_sucess">
				<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
			</div>
		</div>
	@endif
	<table id="blogTable" class="table table-striped table-bordered responsive">
		<thead>
			<tr class="headings">
				<th>@lang('messages.S.no')</th> 
				<th>@lang('messages.Title')</th> 
				<th>@lang('messages.Index')</th> 
				<th>@lang('messages.Categories')</th> 
				<th>@lang('messages.Created Date')</th> 
				<th>@lang('messages.Updated Date')</th> 
				<th>@lang('messages.Status')</th>
				<?php if(hasTask('admin/blog/edit')) { ?>
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
    $('#blogTable').DataTable({
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
        ajax: '{!! route('listblogajax.data') !!}',
        "order": [],
		"columnDefs": [ {
			"targets"  : 'no-sort',
			"orderable": false,
			"searchable":true,
			"pagingType": "full"
		}],
        columns: [
			{ data: 'id', name: 'blogs.id',orderable: false },
			{ data: 'title',searchable: true, name: 'blog_infos.title'},
			{ data: 'url_index', name: 'url_index' },
			{ data: 'categories', name: 'categories' },
            { data: 'created_at', name: 'created_at' },
            { data: 'updated_at', name: 'updated_at' },
            { data: 'status', name: 'status' },
            <?php if(hasTask('admin/blog/edit')) { ?>
            { data: 'action', name: 'action', orderable: false, searchable: false}
            <?php } ?>
        ],
    });
});
</script>
@endsection
