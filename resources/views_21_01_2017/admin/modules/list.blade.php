@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/dataTables.min.js') }}"></script>
<link href="{{ URL::asset('assets/admin/base/css/dataTables.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<div class="pageheader">
<div class="media">
	<div class="pageicon pull-left">
		<i class="fa fa-home"></i>
	</div>
	<div class="media-body">
		<ul class="breadcrumb">
			<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
			<li>@lang('messages.Modules')</li>
		</ul>
		<h4>@lang('messages.Module List')</h4>
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
	
 <table id="module-table" class="table table-striped table-bordered responsive">

    <thead>
        <tr>
            <th>@lang('messages.s_no')</th> 
             <th>@lang('messages.Module Name')</th>
             <th>@lang('messages.Status')</th>
             <th>@lang('messages.action')</th>
        </tr>
    </thead>
</table>
</div>

<script>
$(function() {
    $('#module-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('modules_list.data') !!}',
        "order": [],
		"columnDefs": [ {
		  "targets"  : 'no-sort',
		  "orderable": false,
		  "searchable":true,
		  "pagingType": "full",
		  'exportable':false
		}],
        columns: [
			{ data: 'id', name: 'id',orderable: false },
			{ data: 'module_name', name: 'module_name' },
            { data: 'active_status', name: 'active_status',searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false}
        ],
    });
});
</script>
@endsection
