@extends('layouts.admin')
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
				<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
				<li>@lang('messages.Drivers')</li>
			</ul>
			<h4>@lang('messages.Drivers')</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->

<div class="contentpanel">
	<div class="buttons_block pull-right">
		<div class="btn-group mr5">
			<a class="btn btn-primary tip" href="{{ URL::to('admin/drivers/create') }}" title="Add New"><i class="fa fa-plus"> </i> @lang('messages.Add New')</a>
		</div>
	</div>
	@if (Session::has('message'))
		<div class="admin_sucess_common">
			<div class="admin_sucess">
				<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
			</div>
		</div>
	@endif
	<table id="driversTable" class="table table-striped table-bordered responsive">
		<thead>
			<tr class="headings">
				<th>@lang('messages.S.No')</th> 
				<th>@lang('messages.Driver Name')</th>
				<th>@lang('messages.Email')</th>
				<th>@lang('messages.Registered On')</th>
				<th>@lang('messages.Updated Date')</th>
				<th>@lang('messages.Status')</th>
				<th>@lang('messages.Is Verified')</th>
				<th>@lang('messages.Actions')</th>
			</tr>
		</thead>
		@if (count($drivers) > 0 )
			<tbody>
				<?php $i=1; ?>
				@foreach($drivers as $key => $value)
					<tr>
						<td>{{$i}}</td>
						<td>{{ $value->social_title.ucfirst($value->first_name).' '.$value->last_name }}</td>
						 <td>{{ $value->email }}</td>
						 <td>{{ $value->created_date }}</td>
						 <td>{{ $value->modified_date }}</td>
						<!-- we will also add show, edit, and delete buttons -->
					</tr>
					<?php $i++; ?>
				@endforeach
			</tbody>
		@else
			<tbody>
				<tr>
					<td class="empty-text" colspan="7" style="background-color: #fff!important;">
						<div class="list-empty-text"> @lang('messages.No records found.') </div>
					</td>
				</tr>
			</tbody>
		@endif
	</table>
</div>
<script>
    $(function() {
        $('#driversTable').DataTable({
            dom: 'Blfrtip',
            buttons: [
                {
                    extend: 'excel',
                    text: 'Export',
                    title: 'drivers',
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
            ajax: '{!! route('listDriverAjax.data') !!}',
            "order": [],
            "columnDefs": [ {
                "targets"  : 'no-sort',
                "orderable": false,
            }],
            columns: [
                { data: 'id', name: 'id',orderable: false },
                { data: 'driver_name', name: 'driver_name',searchable:true },
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
