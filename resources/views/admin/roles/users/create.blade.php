@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/colorpicker.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>
<link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" />
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
			<li>@lang('messages.Role Users')</li>
		</ul>
		<h4>@lang('messages.Add User')</h4>
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
       {!!Form::open(array('url' => 'permission/userstore', 'method' => 'post','class'=>'tab-form attribute_form','id'=>'currency_form','files' => true));!!} 
	<div class="tab-content mb30">
	<div class="tab-pane active" id="home3">

			
           <div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Role') <span class="asterisk">*</span></label>
                <div class="col-sm-10">
					<select class="width300 select2-offscreen" autofocus='on' id="source" style="width:100%" name="role_id">
						<option value=""><?php echo trans('messages.Select role'); ?></option>
					<?php foreach($roles as $list):?>
                          <option  value="<?php echo $list->id;?>" <?php if(old('role_id')==$list->id){  echo "selected=selected"; }  ?> ><?php echo ucfirst($list->role_name);?></option>
                      <?php endforeach;?>
					</select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.User') <span class="asterisk">*</span></label>
                <div class="col-sm-10">
					<select class="width300 select2-offscreen" autofocus='on' id="users" style="width:100%" name="user_id">
						<option value=""><?php echo trans('messages.Select user'); ?></option>
					<?php foreach($users as $list):?>
                          <option  value="<?php echo $list->id;?>" <?php if(old('user_id')==$list->id){  echo "selected=selected"; }  ?> ><?php echo $list->email;?></option>
                      <?php endforeach;?>
					</select>
                </div>
            </div>
       </div>
       
		<div class="panel-footer">
		<button class="btn btn-primary mr5" title="Save">@lang('messages.Save')</button>
		<button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('permission/users') }}'">@lang('messages.Cancel')</button>
		</div>
        </div>
 {!!Form::close();!!} 
</div></div></div>
<script type="text/javascript">
    $(window).load(function(){
		$('#source').select2();
		$('#users').select2();
		$('#subject_type').select2();    
    });
</script>
@endsection
