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
			<li>@lang('messages.Cities')</li>
		</ul>
		<h4>@lang('messages.Cities')</h4>
	</div>
</div><!-- media -->
</div><!-- pageheader -->
<div class="contentpanel">
	
<div class="buttons_block pull-right">
<div class="btn-group mr5">
<a class="btn btn-primary tip" href="{{ URL::to('admin/city/create') }}" title="Add New">@lang('messages.Add New')</a>
</div>
</div>
@if (Session::has('message'))
		<div class="admin_sucess_common">
	<div class="admin_sucess">
    <div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
    </div></div>
@endif

 <table id="city-table" class="table table-striped table-bordered responsive">
    <thead>
        <tr class="headings">
			
            <th>@lang('messages.S.no')</th> 
            <th>@lang('messages.City Name')</th> 
             <th>@lang('messages.Country Name')</th> 
            <th>@lang('messages.Zone Code')</th> 
            <th>@lang('messages.Created date')</th> 
             <th>@lang('messages.Status')</th>
            <th>@lang('messages.Actions')</th>
        </tr>
    </thead>
         @if (count($cities) > 0 )
    <tbody>
       <?php $i=1; ?>
    @foreach($cities as $key => $value)
        <tr>
			
            <td>{{$value->cid}}</td>
             <td>{{ ucfirst($value->city_name) }}</td>
              <td>{{ ucfirst($value->country_id) }}</td>
            <td>{{ $value->zone_code }}</td>
            <td>{{ $value->created_date }}</td>
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
<?php /** echo $cities->render(); */ ?>
</div>

<script>
$(function() {
    $('#city-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('ajaxcities.data') !!}',
        "order": [],
		"columnDefs": [ {
		  "targets"  : 'no-sort',
		  "orderable": false,
		  
		}],
        columns: [
			{ data: 'cid', name: 'cities.cid',orderable: false },
			{ data: 'city_name', name: 'city_name' },
			{ data: 'country_name', name: 'country_name' },
			{ data: 'zone_code', name: 'zone_code' },
            { data: 'created_date', name: 'created_date' },
            { data: 'default_status', name: 'default_status' },
            { data: 'action', name: 'action', orderable: false, searchable: false}
        ],
    });
});
</script>
@endsection
