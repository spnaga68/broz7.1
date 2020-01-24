@extends('layouts.admin')
@section('content')
<div class="row">	
	<div class="col-md-12 ">
<!-- Nav tabs -->
<div class="pageheader">
<div class="media">
	<div class="pageicon pull-left">
		<i class="fa fa-home"></i>
	</div>
	<div class="media-body">
		<ul class="breadcrumb">
			<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
			<li>@lang('messages.Notification Template')</li>
		</ul>
		<h4>@lang('messages.View Template')  - <?php echo $data->ref_name; ?></h4>
	</div>
</div><!-- media -->
</div><!-- pageheader -->

<div class="contentpanel">
		@if (count($errors) > 0)
		<div class="alert alert-danger">
				<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
		@endif
<div class="buttons_block pull-right">
<div class="btn-group mr5">
<a class="btn btn-primary tip" href="{{ URL::to('admin/templates/edit/'.$data->template_id.'') }}" title="Edit" >@lang('messages.Edit')</a>
</div>
</div>
<ul class="nav nav-tabs"></ul>
	<div class="tab-content mb30">
	<div class="tab-pane active" id="home3">
	

 <div class="row">
		<div class="col-md-12">
				<ul class="nav nav-tabs"></ul>
		<div class="modal-header">
			<button class="close" type="button" onclick="window.location='{{ url('admin/templates/email') }}'">×</button>
			<h4 class="modal-title"><?php echo "Preview Template"; ?></h4>
		</div>
		<div class="responce"></div>
		<div class="email_template">
			 <?php echo $data->content; ?>
		</div>
	  </div>
</div>

 

        </div>
        </div>
		
</div>
@endsection


