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
		<h4>@lang('messages.View-Faq')</h4>
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

<?php
$data = DB::table('faq')->where('id', "$_SERVER[QUERY_STRING]")->select('id', 'question', 'answer', 'type')->get();
?>

@foreach($data as $value)


  <input type="hidden" value=" <?php echo "{{ $value->id }}"; ?>" />
    <input type="hidden" value=" <?php echo "{{ $value->question }}"; ?>" />
    <input type="hidden" value=" <?php echo " {{ $value->answer }}"; ?>" />
   <input type="hidden" value=" <?php echo " {{ $value->type }}"; ?>" />


@endforeach


<div class="contentpanel">
	@if (hasTask('adminfaq/edit'))
		<div class="buttons_block pull-right">
			<div class="btn-group mr5">
				<a class="btn btn-primary tip" href="edit.?{{$value->id}}" title="Add New">@lang('messages.Edit')</a><br><br>


			</div>
		</div>
	@endif


 <ul class="nav nav-tabs"></ul>

                <div class="tab-content mb30">


<table id="product-table" class="table table-striped table-bordered responsive">
<tbody>

                             <form name="form" id="form" method="Post">

<div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Question') <span class="asterisk">:</span></label> <?php echo " $value->question "; ?>

       </div>

<div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Answer') <span class="asterisk">:</span></label><?php echo " $value->answer "; ?>

       </div>
  <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Type') <span class="asterisk">:</span></label><?php echo " $value->type "; ?>

 </div>


</tbody>


</div>

</form>

@endsection
