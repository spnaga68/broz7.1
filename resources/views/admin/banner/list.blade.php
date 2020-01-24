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
				<li>@lang('messages.Banners')</li>
			</ul>
			<h4>@lang('messages.Banners List')</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->
<div class="contentpanel">
	<?php if(hasTask('admin/banner/create')) { ?>
		<div class="buttons_block pull-right">
			<div class="btn-group mr5">
				<a class="btn btn-primary tip" href="{{ URL::to('admin/banner/create') }}" title="Add New">@lang('messages.Add New')</a>
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
	<table id="bannerTable" class="table table-striped table-bordered responsive">
		<thead>
			<tr class="headings">
				<th>@lang('messages.S.No')</th> 
				<th>@lang('messages.Banner Title')</th> 
				<th>@lang('messages.Banner Subtitle')</th> 
				<th>@lang('messages.Banner Link')</th>
				<th>@lang('messages.Banner Type')</th>
				<th>@lang('messages.Created Date')</th> 
				<th>@lang('messages.Status')</th>
				<?php if(hasTask('admin/banner/edit')) { ?>
				<th>@lang('messages.Default')</th>
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
    $('#bannerTable').DataTable({
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
        ajax: '{!! route('ajaxbanner.data') !!}',
        "order": [],
		"columnDefs": [ {
			"targets"  : 'no-sort',
			"orderable": false,
			"searchable":true,
			"pagingType": "full"
		}],
        columns: [
			{ data: 'banner_setting_id', name: 'banner_setting_id',orderable: false },
			{ data: 'banner_title',name: 'banner_title'},
			{ data: 'banner_subtitle', name: 'banner_subtitle' },
            { data: 'banner_link', name: 'banner_link' },
            { data: 'banner_type', name: 'banner_type'},
            { data: 'created_date', name: 'created_date' },
            { data: 'status', name: 'status' },
            <?php if(hasTask('admin/banner/edit')) { ?>
            { data: 'default_banner', name: 'default_banner', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false}
            <?php } ?>
        ],
    });
});
</script>
@endsection
