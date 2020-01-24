@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script> 
<link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" />
 <script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>
 <script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/colorpicker.js') }}"></script>
 <link href="{{ URL::asset('assets/admin/base/css/colorpicker.css') }}" media="all" rel="stylesheet" type="text/css" />
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
			<li>@lang('messages.Notification Subjects')</li>
		</ul>
		<h4>@lang('messages.Edit Subject') - <?php echo ucfirst($data->subject_index); ?></h4>
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
		
<ul class="nav nav-tabs"></ul>
	{!!Form::open(array('url' => ['admin/subjects/update', $data->id],'method' => 'post','class'=>'tab-form attribute_form','id'=>'local_edit_form','files' => true));!!}
        <div class="tab-content mb30 no-padding">
        <div class="tab-pane active" id="home3">
            <div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Subject Type') <span class="asterisk">*</span></label>
                <div class="col-sm-10">
					<select class="width300 select2-offscreen" autofocus='on' id="subject_type" style="width:100%" name="subject_type">		
						<option value=""><?php echo 'Choose Subject Type'; ?></option>
						<?php foreach(getSubjectType() as $key => $value) { ?>
							<option value="<?php echo $key.'|'.$value; ?>" <?php if($key==$data->subject_type){ echo "selected"; } ?>><?php echo $value; ?></option>
						<?php } ?>
					</select>	                 
                </div>
            </div>	                                   
            <div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Template Type') <span class="asterisk">*</span></label>
                <div class="col-sm-10">
				<select class="width300 select2-offscreen" id="template_id" style="width:100%" name="template_id">		
					<option value=""><?php echo 'Choose Template Type'; ?></option>			
					<?php foreach(getTemplates() as $template) { ?>						
						<option value="<?php echo $template->template_id; ?>" <?php if($template->template_id==$data->template_id){ echo "selected"; }?>><?php echo $template->ref_name; ?></option>
					<?php } ?>					
				</select>			                  
                </div>
            </div>
            
			<div class="form-group">		
				<label  class="col-sm-2 control-label">@lang('messages.Color Code') <span class="asterisk">*</span></label>
				<div class="col-sm-10">
					<input type="text" name="color_code" class="form-control colorpicker-input" placeholder="#000000" id="colorpicker3" value="{!! $data->color_code !!}" />
					<div class="clearfix"></div><br />
					<span id="colorpickerholder"></span>
				</div>        
			</div> 
        
			<div class="form-group">
					<label  class="col-sm-2 control-label">@lang('messages.Status')</label>
				<div class="col-sm-10">
				 <?php $checked="";  if($data->status){ $checked = "checked=checked"; }?>
					<input type="checkbox" class="toggle" name="status" data-size="small" <?php echo $checked;?> data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
				</div>
			</div>
          </div>
		<div class="panel-footer ">
            <button class="btn btn-primary mr5">@lang('messages.Save')</button>
            <button type="reset" class="btn btn-default"   onclick="window.location='{{ url('admin/template/subjects') }}'" >@lang('messages.Cancel')</button>
        </div>
         </div>
	{!!Form::close();!!}
 
</div></div></div>
<script type="text/javascript">
    $(window).load(function(){
		$('#template_id').select2();
		$('#subject_type').select2();
		jQuery('#colorpickerholder').ColorPicker({
				flat: true,
				onChange: function (hsb, hex, rgb) {
		jQuery('#colorpicker3').val('#'+hex);
				}
		});    
    });
</script>
@endsection
