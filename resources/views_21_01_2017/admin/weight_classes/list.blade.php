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
			<li>@lang('messages.weight_classes')</li>
		</ul>
		<h4>@lang('messages.weight_classes')</h4>
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
<div class="buttons_block pull-right">
<div class="btn-group mr5">
<a class="btn btn-primary tip" href="{{ URL::to('admin/localisation/create_weight_class') }}" title="Add New" >@lang('messages.Add New')</a>
</div>
</div>
 <table id="weightclasses-table" class="table table-striped table-bordered responsive">

    <thead>
        <tr>
            <th>@lang('messages.s_no')</th> 
             <th>@lang('messages.weight_title')</th>
             <th>@lang('messages.weight_unit')</th>
             <th>@lang('messages.weight_value')</th>
             <th>@lang('messages.created_date')</th>
             <th>@lang('messages.current_status')</th>
             <th>@lang('messages.action')</th>
        </tr>
    </thead>
     @if (count($weight_classes) > 0 )
    <tbody>
    <?php $i=1; ?>
    @foreach($weight_classes as $key => $value)
        <tr>
             <td>{{$value->id}}</td> 
            <td>{{ ucfirst($value->title) }}</td>
            <td>{{ $value->unit }}</td>
            <td> {{ $value->weight_value}} </td>
            <td>{{ $value->created_date }}</td>
            <td><?php if($value->active_status==2): echo '<span class="label label-danger">';?>@lang('messages.delete')<?php echo '</span>'; ?>
            <?php elseif($value->active_status==1): echo '<span class="label label-success">';?>@lang('messages.Active')<?php echo '</span>'; ?>
            <?php else: echo '<span class="label label-warning">';?>@lang('messages.Block')<?php echo '</span>'; endif; ?></td>

            <!-- we will also add show, edit, and delete buttons -->
            <td>
                   <div class="btn-group">
						<a href="{{ URL::to('admin/localisation/edit_weight_class/' . $value->id . '') }}" class="btn btn-xs btn-white" title="@lang('messages.Edit')"><i class="fa fa-edit"></i>&nbsp;@lang('messages.Edit')</a>
						<button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
						<span class="caret"></span>
						<span class="sr-only">Toggle Dropdown</span>
						</button>
						<ul class="dropdown-menu xs pull-right" role="menu">
						<li><a href="{{ URL::to('admin/localisation/delete_weight_class/' . $value->id) }}" class="delete" title="@lang('messages.Delete')"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;@lang('messages.Delete')</a></li>
						</ul>
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
<?php echo $weight_classes->render(); ?>
</div>

<script>
$( document ).ready(function() {
    $(".delete").on("click", function(){
        return confirm("@lang('messages.Are you sure want to delete?')");
    });
});
$(function() {
    $('#weightclasses-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('ajax_weight_classes.data') !!}',
        "order": [],
		"columnDefs": [ {
		  "targets"  : 'no-sort',
		  "orderable": false,
		  "searchable":true,
		  "pagingType": "full",
		  'exportable':false
		}],
        columns: [
			{ data: 'id', name: 'weight_classes.id',orderable: false },
			{ data: 'title', name: 'title' },
			{ data: 'unit', name: 'unit' },
			{ data: 'weight_value', name: 'weight_value' },
            { data: 'created_date', name: 'created_date' },
            { data: 'active_status', name: 'active_status',searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false}
        ],
    });
});
</script>
@endsection
