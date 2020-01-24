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
   <!--  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
 -->
<div class="pageheader">
<div class="media">
	<div class="pageicon pull-left">
		<i class="fa fa-home"></i>
	</div>
	<div class="media-body">
		<ul class="breadcrumb">
			<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
			<li>@lang('messages.Faq')</li>
		</ul>
		<h4>@lang('messages.Faq')</h4>
	</div>
</div><!-- media -->
</div><!-- pageheader -->
<!-- will be used to show any messages -->
@if (Session::has('message'))
	<div class="admin_sucess_common">
		<div class="admin_sucess">
			<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">Ã—</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
		</div>
	</div>
@endif



<div class="contentpanel">
	@if (hasTask('admin/faq/faqans'))
		<div class="buttons_block pull-right">
			<div class="btn-group mr5">
				<a class="btn btn-primary tip" href="{{ URL::to('admin/faq/faqans') }}" title="Add New">@lang('messages.Add New')</a><br><br>
<!--
				<form type="text" method="post">
   <input type="text" width="50"  name="search" placeholder="search">

</form> -->
			</div>
		</div>
	@endif

	<?php
$data = DB::table('faq')

	->select('id', 'question', 'answer', 'type', 'created_date', 'updated_date')
	->orderby('id', 'asc')

	->LIMIT('2')
//->paginate(1)

	->get();

?>




<table id="product-table" class="table table-striped table-bordered responsive">




		<thead>
			<tr class="headings">

				<!-- @if(hasTask('admin/faq/edit'))
    				<th>
    				    <input type="checkbox"  id="bulkDelete"/>
    				    <button id="deleteTriger">@lang('messages.Delete')</button>
    			    </th>
			    @else

			    @endif -->
				<th>@lang('messages.Id')</th>
				<th>@lang('messages.Question')</th>
				<th>@lang('messages.Answer')</th>
				<th>@lang('messages.Type')</th>
				<th>@lang('messages.Created Date')</th>
				<th>@lang('messages.Updated Date')</th>


<?php if (hasTask('admin/faq/edit')) {?>
				<th>@lang('messages.Actions')</th>
				<?php }?>

			</tr>



		</thead>


<tbody>
@foreach($data as $value)
<tr>
						<td> {{ $value->id }} </td>
						<td> {{ $value->question }} </td>
						<td> {{ $value->answer }} </td>
						<td>{{ $value->type }}</td>
						<td>{{ $value->created_date }}</td>
						<td>{{ $value->updated_date }}</td>

  						<td>







		    <div class="btn-group">
<a href="edit.?{{$value->id}}"> <button type="button" > <span class="glyphicon glyphicon-edit"></span>Edit</button> </a>
<button type="button" class=" dropdown-toggle" data-toggle="dropdown">
     <span class="caret"></span></button>
<ul class="dropdown-menu" >
        <li><a href="delete.?{{$value->id}}" onClick=" return confirm('Are you sure you want to delete?{{$value->id}}')" ><span class="glyphicon glyphicon-trash"></span> Delete</a></li>

        <li><a href="view.?{{$value->id}}" > <span class="glyphicon glyphicon-eye-open"></span>View </a></li>
</ul>
</div>
   					 </td>

						<!-- <a href="delete.?{{$value->id}}" > <button class="btn btn-danger" onClick="return confirm('Are you sure you want to delete?')">Delete</button> </a> <a href="view.?{{$value->id}}" > <button class="btn btn-secondary">View</button> </a></td>
 -->


</tr>




@endforeach



</tbody>	<?php //echo"<pre>"; print_r(count($data));exit(); ?>

@if(count($data)<=0)
		<tbody>
			<tr>
				<td class="empty-text" colspan="11" style="background-color: #fff!important;">
					<div class="list-empty-text"> @lang('messages.No records found.') </div>
				</td>
			</tr>
		</tbody>
		@endif
	</table>
<?php
$data = DB::table('faq')->paginate(2);

echo $data;
?>
</div>









@endsection
