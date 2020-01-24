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
				<li>@lang('messages.Categories')</li>
			</ul>
			<h4>@lang('messages.Categories')</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->
<div class="contentpanel">
	@if(hasTask('admin/category/create'))
		<div class="buttons_block pull-right">
			<div class="btn-group mr5">
				<a class="btn btn-primary tip" href="{{ URL::to('admin/category/create') }}" title="Add New" >@lang('messages.Add New')</a>
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
	<table id="countryTable" class="table table-striped table-bordered responsive display nowrap">
		<thead>
			<tr>
				<th>@lang('messages.S.no')</th> 
				 <th>@lang('messages.Name')</th>
				 <th>@lang('messages.Url Key')</th>
				 <th>@lang('messages.Category Type')</th>
				 <th>@lang('messages.Vendor Category')</th>
				 <th>@lang('messages.Created Date')</th>
				 <th>@lang('messages.Updated Date')</th>
				 <th>@lang('messages.Status')</th>
				 <?php if(hasTask('admin/category/create')){ ?>
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
    $('#countryTable').DataTable({
		/*dom: 'Blfrtip',
		buttons: [{'excel','csv','pdf'}
		},
		{
           extend: 'pdf',
           footer: false,
           exportOptions: {
                columns: [0,1,2,3,4,5,6]
            }
		},
		{
           extend: 'excel',
           footer: false,
           exportOptions: {
                columns: [0,1,2,3,4,5,6]
            }
		}],*/
        processing: true,
        serverSide: false,
        responsive: true,
		autoWidth:false,
		//bLengthChange: false,
		//bFilter : false,
        ajax: '{!! route('listcategoryajax.data') !!}',
        "order": [],
		"columnDefs": [ {
			"targets"  : 'no-sort',
			"orderable": false,
			"searchable":true,
			"pagingType": "full",
			'exportable':false
		}],
        columns: [
			{ data: 'id', name: 'categories.id',orderable: false },
			{ data: 'category_name',searchable: true, name: 'categories_infos.category_name'},
			{ data: 'url_key', name: 'url_key' },
			{ data: 'category_type', name: 'category_type' },
			{ data: 'parent_category', name: 'parent_category' },
            { data: 'created_at', name: 'created_at' },
            { data: 'updated_at', name: 'updated_at' },
			{ data: 'category_status', name: 'category_status' },
			<?php if(hasTask('admin/category/create')) { ?>
            { data: 'action', name: 'action', orderable: false, searchable: false}
            <?php } ?>
        ],
    });
});
</script>
@endsection
