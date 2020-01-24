@extends('layouts.admin')
@section('content')
 <script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>
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
			<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>{{ trans('messages.Admin') }}</a></li>
			<li>@lang('messages.Settings')</li>
		</ul>
		<h4>@lang('messages.Store')</h4>
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
@if (Session::has('message'))
		<div class="admin_sucess_common">
	<div class="admin_sucess">
<div class="alert alert-info success">
<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>
    {{ Session::get('message') }}</div></div></div>
@endif
<?php $settings_id=0;
if(isset($settings->id)&& $settings->id!=''){ $settings_id=$settings->id; } ?>
       {!!Form::open(array('url' => ['admin/settings/updatestore', $settings_id], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'settings_edit_form','files' => true));!!}

<div class="col-md-12">
<div class="row panel panel-default">
<div class="grid simple">
	<div id="general" class="panel-heading">
	<h4 class="panel-title">@lang('messages.Store')</h4>
	<p>@lang('messages.Store settings')</p>
		<div class="tools">
			<a class="collapse" href="javascript:;"></a>
		</div>
	</div>
	<ul class="nav nav-tabs"></ul>
	<div class="panel-body">
		
	<div id="meta_title" class="form-group">
		<label class="form-label">@lang('messages.Meta Title') <span class="asterisk">*</span></label>
		<span class="help">@lang('messages.This title will visible as a title in frontend')</span>
		<div class="controls">
		<input class="form-control" type="text" maxlength="300" value="@if(isset($settings->meta_title)&&$settings->meta_title!=''){{ $settings->meta_title }}@endif " required name="meta_title">
		</div>
	</div>

	<div id="meta_keywords" class="form-group">
		<label class="form-label">@lang('messages.Meta Tag Keyword') <span class="asterisk">*</span></label>
		<span class="help">@lang('messages.This add as meta keywords in frontend')</span>
		<div class="controls">
		<input class="form-control" type="text" maxlength="300" value="@if(isset($settings->meta_keywords)&&$settings->meta_keywords!=''){{ $settings->meta_keywords }}@endif" required name="meta_keywords">
		</div>
	</div>

	<div id="meta_description" class="form-group">
		<label class="form-label">@lang('messages.Meta Tag Description') <span class="asterisk">*</span></label>
		<span class="help">@lang('messages.This add as meta description frontend')</span>
		<div class="controls">
			<textarea class="form-control" rows="10" cols="50" required name="meta_description">@if(isset($settings->meta_description)&&$settings->meta_description!='')<?php echo $settings->meta_description; ?>@endif</textarea>
		</div>
	</div>
	<?php  $path= 'assets/front/';  $directories = array_map('basename', File::directories($path));?>
	<div id="default_template" class="form-group">
	<label class="form-label">@lang('messages.Choose your Template') <span class="asterisk">*</span></label>
		<select id="localeselect" class="select2-offscreen" required style="width:100%;" name="template" tabindex="-1" title="">
			@if (count($directories) > 0)
					@foreach ($directories as $dir)
						<option value="{{ $dir }}" <?php if($dir==$settings->theme){ echo "selected=selected"; } ?> >{{ $dir }}</option>
					@endforeach
			@endif
			*/ ?>
		</select>
	</div>



</div>
<div class="panel-footer">
		<button class="btn btn-primary mr5" title="@lang('Update')">@lang('messages.Update')</button>
		<button type="reset" title="@lang('Cancel')" class="btn btn-default" onclick="window.location='{{ url('admin/dashboard') }}'">@lang('messages.Cancel')</button>
	</div>
</div></div></div>

 {!!Form::close();!!} 
</div></div></div>
	<script>
	$(document).ready(function(){  $("#localeselect").select2(); });
	</script>
@endsection

