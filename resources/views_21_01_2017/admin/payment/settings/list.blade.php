@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/js/translate.js') }}"></script>
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
			<li>@lang('messages.Payemnt Gateways')</li>
		</ul>
		<h4>@lang('messages.Payemnt Gateways')</h4>
	</div>
</div><!-- media -->
</div><!-- pageheader -->
<div class="contentpanel">
	
<div class="buttons_block pull-right">
<div class="btn-group mr5">
<a class="btn btn-primary tip" href="{{ URL::to('admin/payment/gatewaycreate') }}" title="Add New">@lang('messages.Add New')</a>
</div>
</div>
@if (Session::has('message'))
	<div class="admin_sucess_common">
	<div class="admin_sucess">
    <div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div></div></div>
@endif

 <table id="payment-table" class="table table-striped table-bordered responsive">
    <thead>
        <tr class="headings">
			
            <th>@lang('messages.S.no')</th> 
            <th>@lang('messages.Payemnt Gateway Name')</th>  
            <th>@lang('messages.Commision')</th>  
            <th>@lang('messages.Status')</th>  
            <th>@lang('messages.Created date')</th> 
            <th>@lang('messages.Actions')</th>
        </tr>
    </thead>
         @if (count($payment) > 0 )
    <tbody>
       <?php $i=1; ?>
    @foreach($payment as $key => $value)
        <tr>
			
            <td>{{$value->id}}</td>
             <td>{{ ucfirst($value->name) }}</td>
            <td>{{ $value->created_date }}</td>
            <?php /**
            <td>
                	<div class="btn-group">
                    <a href="{{ URL::to('admin/country/edit/' . $value->id . '') }}" class="btn btn-xs btn-white" title="@lang('messages.Edit')"><i class="fa fa-edit"></i>&nbsp;@lang('messages.Edit')</a>
						<button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
						<span class="caret"></span>
						<span class="sr-only">Toggle Dropdown</span>
						</button>
						<ul class="dropdown-menu xs pull-right" role="menu">
						<li><a href="{{ URL::to('admin/country/delete/' . $value->id) }}" class="delete" title="@lang('messages.Delete')"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;@lang('messages.Delete')</a></li>

						</ul>
                    </div>
            </td>
            */ ?>
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
<?php  /** echo $countries->render(); */ ?>
</div>

<script>
$(function() {
    $('#payment-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('ajaxpayment.data') !!}',
        "order": [],
		"columnDefs": [ {
		  "targets"  : 'no-sort',
		  "orderable": false,
		  
		}],
        columns: [
			{ data: 'id', name: 'payment_gateways.id',orderable: false },
			{ data: 'name', name: 'name' },
			{ data: 'commision', name: 'commision' },
			{ data: 'status', name: 'status' },
            { data: 'created_date', name: 'created_date' },
            { data: 'action', name: 'action', orderable: false, searchable: false}
        ],
    });
});
</script>
@endsection
