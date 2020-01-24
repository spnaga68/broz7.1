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
			<li>@lang('messages.Languages')</li>
		</ul>
		<h4>@lang('messages.Languages')</h4>
	</div>
</div><!-- media -->
</div><!-- pageheader -->
<div class="contentpanel">
<div class="buttons_block pull-right">
<div class="btn-group mr5">
<a class="btn btn-primary tip" href="{{ URL::to('admin/language/create') }}" title="Add New">@lang('messages.Add New')</a>
</div>
</div>

@if (Session::has('message'))
<div class="admin_sucess_common">
	<div class="admin_sucess">
    <div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div></div></div>
@endif

 <table id="languageTable" class="table table-striped table-bordered responsive">
    <thead>
        <tr class="headings">
            <th>@lang('messages.S.No')</th> 
            <th>@lang('messages.Language Name')</th> 
            <th>@lang('messages.Language Code')</th> 
            <th>@lang('messages.Short Date Format')</th>
             <th>@lang('messages.Full Date Format')</th>  
               <th>@lang('messages.Created Date')</th>
               <th>@lang('messages.Status')</th>  
            <th>@lang('messages.Actions')</th> 
        </tr>
    </thead>
         @if (count($languages) > 0 )
    <tbody>
       <?php $i=1; ?>
    @foreach($languages as $key => $value)
        <tr>
            <td>{{$i}}</td>
             <td>{{ $value->name }}</td>
            <td>{{ $value->language_code }}</td>
			<td>
				{{ $value->date_format_short }}
			</td>
			<td>
				{{ $value->date_format_full }}
			</td>
            <td>{{ $value->created_at }}</td>
			<td>
				
			</td>
            <!-- we will also add show, edit, and delete buttons -->
            <td>

					<div class="btn-group">
                    <a href="{{ URL::to('admin/blog/edit/' . $value->id . '') }}" class="btn btn-xs btn-white" title="@lang('messages.Edit')"><i class="fa fa-edit"></i>&nbsp;@lang('messages.Edit')</a>
						<button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
						<span class="caret"></span>
						<span class="sr-only">Toggle Dropdown</span>
						</button>
                    </div>
                    

            </td>
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
    $('#languageTable').DataTable({
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
        ajax: '{!! route('listlanguageajax.data') !!}',
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
			{ data: 'language_code', name: 'language_code' },
            { data: 'date_format_short', name: 'date_format_short' },
            { data: 'date_format_full', name: 'date_format_full' },
            { data: 'created_at', name: 'created_at' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        
        
    });
    
});
</script>
@endsection
