@extends('layouts.admin')
@section('content')
<?php /*<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/dataTables.min.js') }}"></script>
<script src="https://cdn.datatables.net/buttons/1.0.3/js/dataTables.buttons.min.js"></script>
<link href="{{ URL::asset('assets/admin/base/css/dataTables.min.css') }}" media="all" rel="stylesheet" type="text/css" />*/ ?>

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
				<li>@lang('messages.Users')</li>
			</ul>
			<h4>@lang('messages.Users')</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->
<div class="contentpanel">
	@if(hasTask('admin/users/create'))
		<div class="buttons_block pull-right">
			<div class="btn-group mr5">
				<a class="btn btn-primary tip" href="{{ URL::to('admin/users/create') }}" title="Add New"><i class="fa fa-plus"> </i> @lang('messages.Add New')</a>
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
	<table id="userTable" class="table table-striped table-bordered responsive">
		<thead>
			<tr class="headings">
				<th>@lang('messages.S.No')</th> 
				<?php /**<th>@lang('messages.Social Title')</th>*/ ?>
				<th>@lang('messages.Name')</th>  
				<th>@lang('messages.Email')</th>
				<th>@lang('messages.Group')</th>
				<th>@lang('messages.User Platform')</th>
				<th>@lang('messages.Registered On')</th>
				<?php /**<th>@lang('messages.Updated Date')</th>*/ ?>
				<th>@lang('messages.Status')</th>  
				<th>@lang('messages.Is Verified')</th>
				<?php if(hasTask('admin/users/create')){ ?>
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
	var t = $('#userTable').DataTable({
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'excel',
                text: 'Export',
                title: 'users',
                footer: false,
                exportOptions: {
                    columns: [0,1,2,3,4,5,6]
                }
            }
        ],
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth:false,
        ajax: '{!! route('ajaxusers.data') !!}',
       
        "columnDefs": [ {
            "targets"  : 0,
            "orderable": false,
        }],
		"order": [ 1, 'asc' ],
        columns: [
			{ data: 'id', name: 'users.id'},
			//{ data: 'social_title',name: 'social_title'},
			{ data: 'name',name: 'name'},
			{ data: 'email',name: 'email'},
			{ data: 'group_name',name: 'users_group.group_name'},
			{ data: 'login_type',name: 'login_type', searchable: false},
			{ data: 'created_date',name: 'created_date', searchable: false},
			//{ data: 'updated_date',name: 'updated_date'},
			{ data: 'status',name: 'status'},
			{ data: 'is_verified',name: 'is_verified'},
			<?php if(hasTask('admin/users/edit')){ ?>
            { data: 'action', name: 'action', orderable: false, searchable: false}
            <?php } ?>
        ],
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
        $("td:nth-child(1)", nRow).html(iDisplayIndex + 1);
        return nRow;
	}
    });
});
</script>
@endsection
