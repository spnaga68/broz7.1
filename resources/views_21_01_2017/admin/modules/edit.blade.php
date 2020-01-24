@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script>
 <script type="text/javascript" src="{{ URL::asset('assets/js/admin.js') }}"></script>
 <link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" /> 
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
			<li>@lang('messages.Modules')</li>
		</ul>
		<h4>@lang('messages.Edit Module') - {{ $data->module_name }}</h4>
	</div>
</div><!-- media -->
</div><!-- pageheader -->

<div class="contentpanel">
		@if (count($errors) > 0)
		<div class="alert alert-danger">
				<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>
			<ul>
				@foreach ($errors->all() as $error)
					<li><?php echo trans('messages.'.$error); ?> </li>
				@endforeach
			</ul>
		</div>
		@endif
<ul class="nav nav-tabs"></ul>

       {!!Form::open(array('url' => ['admin/update_module', $data->id], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'weight_class_form'));!!} 

	<div class="tab-content mb30">
	<div class="tab-pane active" id="home3">
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Module Name')</label>
			<div class="col-sm-10">
			<input type="text"  name="module_name" value="{!! $data->module_name !!}"  readonly maxlength="16" placeholder="@lang('messages.Module Name')"  class="form-control"  />
			</div>
		</div>
		<div class="form-group">
			<label  class="col-sm-2 control-label">@lang('messages.Status')</label>
			<div class="col-sm-10">
				<?php $checked = "";
					if($data->active_status){ $checked = "checked=checked"; }
				?>
				<input type="checkbox" class="toggle" name="active_status" data-size="small" <?php echo $checked;?> data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
			</div>
	   </div>
       </div>
			<div class="panel-footer">
				<button class="btn btn-primary mr5" title="Save">@lang('messages.Save')</button>
				<button type="reset" class="btn btn-default" onclick="window.location='{{ url('admin/modules/settings') }}'"  title="Cancel">@lang('messages.Cancel')</button>
			</div>
		</div>
	{!!Form::close();!!}
		</div>
	</div>
</div>

@endsection
