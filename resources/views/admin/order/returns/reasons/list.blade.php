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
			<li>@lang('messages.Return Reasons')</li>
		</ul>
		<h4>@lang('messages.Return Reasons List')</h4>
	</div>
</div><!-- media -->
</div><!-- pageheader -->
<div class="contentpanel">
<div class="buttons_block pull-right">
<div class="btn-group mr5">
<a class="btn btn-primary tip" href="{{ URL::to('admin/returnreason/create') }}" title="Add New">@lang('messages.Add New')</a>
</div>
</div>

@if (Session::has('message'))
	<div class="admin_sucess_common">
	<div class="admin_sucess">
    <div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div></div></div>
@endif

 <table id="currencyTable" class="table table-striped table-bordered responsive">
    <thead>
        <tr class="headings">
            <th>@lang('messages.S.No')</th> 
            <th>@lang('messages.Return Reason Name')</th>  
            <th>@lang('messages.Actions')</th> 
        </tr>
    </thead>
         @if (count($returnreasons) > 0 )
    <tbody>
       <?php $i=1; ?>
    @foreach($returnreasons as $key => $value)
        <tr>
            <td>{{$i}}</td>
            <td>{{ $value->name }}</td>
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
<?php /** echo $languages->render(); */ ?>
</div>
<script>
$(function() {
    $('#currencyTable').DataTable({
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
        ajax: '{!! route('ajaxreturnreason.data') !!}',
        "order": [],
		"columnDefs": [ {
		  "targets"  : 'no-sort',
		  "orderable": false,
		  "searchable":true,
		  "pagingType": "full"
		  
		}],
        columns: [
			{ data: 'id', name: 'id',orderable: false },
			{ data: 'name',name: 'name'},
            { data: 'action', name: 'action', orderable: false, searchable: false}
        ],
    });
    
});
</script>
@endsection
