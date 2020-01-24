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
			<li>@lang('messages.Zones')</li>
		</ul>
		<h4>@lang('messages.Zones')</h4>
	</div>
</div><!-- media -->
</div><!-- pageheader -->
@if (Session::has('message'))
	<div class="admin_sucess_common">
	<div class="admin_sucess">
    <div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div></div></div>
@endif
<div class="contentpanel">
	<div class="buttons_block pull-right">
		<div class="btn-group mr5">
			<a class="btn btn-primary tip" href="{{ URL::to('admin/zones/create') }}" title="Add New">@lang('messages.Add New')</a>
		</div>
	</div>
 <table id="country-table" class="table table-striped table-bordered responsive">
    <thead>
        <tr class="headings">
			<th>@lang('messages.S.no')</th> 
			<th>@lang('messages.Zone Name')</th> 
			<th>@lang('messages.Country Name')</th> 
			<th>@lang('messages.City Name')</th> 
			<th>@lang('messages.Created date')</th> 
			<th>@lang('messages.Status')</th>
			<th>@lang('messages.Actions')</th>
        </tr>
    </thead>
         @if (count($zones) > 0 )
    <tbody>
       <?php $i=1; ?>
    @foreach($zones as $key => $value)
        <tr>
			<td>{{$value->id}}</td>
			<td>{{ ucfirst($value->zone_name) }}</td>
			<td>{{ ucfirst($value->country_id) }}</td>
			<td>{{ $value->city_id }}</td>
			<td>{{ $value->created_at }}</td>
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
<?php /** echo $zones->render(); */ ?>
</div>

<script>
$(function() {
    $('#country-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('ajaxzones.data') !!}',
        "order": [],
		"columnDefs": [ {
		  "targets"  : 'no-sort',
		  "orderable": false,
		  
		}],
        columns: [
			{ data: 'zid', name: 'zid',orderable: false },
			{ data: 'zone_name', name: 'zone_name' },
			{ data: 'country_name', name: 'country_name' },
			{ data: 'city_name', name: 'city_name' },
            { data: 'created_at', name: 'created_at' },
            { data: 'zones_status', name: 'zones_status' },
            { data: 'action', name: 'action', orderable: false, searchable: false}
        ],
    });
});
</script>
@endsection
