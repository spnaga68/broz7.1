@extends('layouts.admin')
@section('content')
<div class="pageheader">
<div class="media">
	<div class="pageicon pull-left">
		<i class="fa fa-home"></i>
	</div>
	<div class="media-body">
		<ul class="breadcrumb">
			<li><a href="#"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
			<li>@lang('messages.Portfolio')</li>
		</ul>
		<h4>@lang('messages.Portfolio')</h4>
	</div>
</div><!-- media -->
</div><!-- pageheader -->
<div class="contentpanel">
<div class="buttons_block pull-right">
<div class="btn-group mr5">
<a class="btn btn-primary tip" href="{{ URL::to('admin/portfolio/create') }}" title="Add New" >@lang('messages.Add New')</a>
</div>
</div>
                        <?php /** <p class="mb20"><a href="http://datatables.net/" target="_blank">DataTables</a> is a plug-in for the jQuery Javascript library. It is a highly flexible tool, based upon the foundations of progressive enhancement, and will add advanced interaction controls to any HTML table.</p>
                    
                        <div class="panel panel-primary-head">
                            <div class="panel-heading">
                                <h4 class="panel-title">Basic Configuration</h4>
                                <p>Searching, ordering, paging etc goodness will be immediately added to the table, as shown in this example.</p>
                            </div><!-- panel-heading --> **/?>
<!-- will be used to show any messages -->
@if (Session::has('message'))
    <div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
@endif

 <table id="basicTable" class="table table-striped table-bordered responsive">
    <thead>
        <tr class="headings">
            <th>@lang('messages.S.no')</th> 
            <th style="width:15%;">@lang('messages.Title')</th> 
            <th>@lang('messages.Customer')</th>
            <th style="width:15%;">@lang('messages.Technology')</th>
            <th  style="width:15%;" >@lang('messages.Categories')</th> 
            <th>@lang('messages.Created Date')</th> 
            <th>@lang('messages.Actions')</th> 
        </tr>
    </thead>
         @if (count($portfolio) > 0 )
    <tbody>
       <?php $i=1; ?>
    @foreach($portfolio as $key => $value)
    <?php $categories = explode(',',$value->category_ids);  ?>
        <tr>
			<td>{{$i}}</td>
			<td>{{ ucfirst($value->title) }}</td>
			<td>{{ ucfirst($value->customer) }}</td>
			<td>{{ ucfirst($value->technology) }}</td>
			<td>
				@foreach ($category as $val)
				@if (in_array($val->id,$categories))
					{{  ucfirst($val->category_name.',') }}
				@endif
				@endforeach
			</td>
			</td>
			<td>{{ $value->created_at }}</td>
            <!-- we will also add show, edit, and delete buttons -->
            <td>
                <!-- show the blog (uses the show method found at GET /nerds/{id} -->
                
		
					<div class="btn-group">
                    <a href="{{ URL::to('admin/portfolio/edit/' . $value->id . '') }}" class="btn btn-xs btn-white" title="Edit"><i class="fa fa-edit"></i>&nbsp;@lang('messages.Edit')</a>
						<button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
						<span class="caret"></span>
						<span class="sr-only">Toggle Dropdown</span>
						</button>
						<ul class="dropdown-menu xs pull-right" role="menu">
						<li><a href="{{ URL::to('admin/portfolio/view/' . $value->id) }}" class="view" title="View" ><i class="fa fa-eye"></i>&nbsp;&nbsp;@lang('messages.View')</a></li>
						<li><a href="{{ URL::to('admin/portfolio/delete/' . $value->id) }}" class="delete"title="Delete" ><i class="fa fa-trash-o"></i>&nbsp;&nbsp;@lang('messages.Delete')</a></li>
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
<?php echo $portfolio->render(); ?>
</div>

<script>
$( document ).ready(function() {
    $(".delete").on("click", function(){
        //return confirm("Are you sure want to delete?");
        return confirm("@lang('messages.Are you sure want to delete?')");
    });
});
</script>
@endsection
