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
			<li>@lang('messages.weight_classes')</li>
		</ul>
		<h4>@lang('messages.add_weight_classes')</h4>
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

       {!!Form::open(array('url' => 'weight_class_create', 'method' => 'post','class'=>'tab-form attribute_form','id'=>'weight_class_form'));!!} 

	<div class="tab-content mb30">
	<div class="tab-pane active" id="home3">
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.weight_title') <span class="asterisk">*</span></label>
			<div class="col-sm-10">
			  
			  <?php $i = 0; foreach($languages as $langid => $language):?>
                    <div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
                          <input type="text" name="weight_title[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.weight_title').trans('messages.'.'('.$language->name.')');?>" class="form-control" value="{!! Input::old('weight_title.'.$language->id) !!}" maxlength="20" />
                     
                        <div class="input-group-btn">
                            <button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button"><?php echo $language->name;?> <span class="caret"></span></button>
                            <ul class="dropdown-menu pull-right">
                                <?php foreach($languages as $sublangid => $sublanguage):?>
                                    <li><a href="javascript:YL.Language.fieldchange(<?php echo $sublanguage->id;?>)"> <?php echo trans('messages.'.$sublanguage->name);?></a></li>
                                <?php endforeach;?>
                            </ul>
                        </div><!-- input-group-btn -->
                    </div>
                    <?php $i++; endforeach;?>
                    <span class="help-block">@lang('messages.weight_title_help_text')</span>
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.weight_unit')</label>
			<div class="col-sm-10">
			  <?php $i = 0; foreach($languages as $langid => $language):?>
                    <div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
                          <input type="text" name="weight_unit[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.weight_unit').trans('messages.'.'('.$language->name.')');?>" class="form-control" value="{!! Input::old('weight_unit.'.$language->id) !!}"  maxlength="10" />
                     
                        <div class="input-group-btn">
                            <button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button"><?php echo $language->name;?> <span class="caret"></span></button>
                            <ul class="dropdown-menu pull-right">
                                <?php foreach($languages as $sublangid => $sublanguage):?>
                                    <li><a href="javascript:YL.Language.fieldchange(<?php echo $sublanguage->id;?>)"> <?php echo trans('messages.'.$sublanguage->name);?></a></li>
                                <?php endforeach;?>
                            </ul>
                        </div><!-- input-group-btn -->
                    </div>
                    <?php $i++; endforeach;?>
                    <span class="help-block">@lang('messages.weight_unit_help_text')</span>
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.weight_value')</label>
			<div class="col-sm-10">
				<input type="text"  name="weight_value" value="{!! old('weight_value') !!}"  maxlength="15" placeholder="@lang('messages.weight_value')"  class="form-control"  />
				<span class="help-block">@lang('messages.weight_value_help_text')</span>
			</div>
		</div>
		<div class="form-group">
			<label  class="col-sm-2 control-label">@lang('messages.Status')</label>
			<div class="col-sm-10">
				<?php $checked = ""; ?>
				<input type="checkbox" class="toggle" name="active_status" data-size="small" <?php echo $checked;?> data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
			</div>
	   </div>
       </div>
			<div class="panel-footer">
				<button class="btn btn-primary mr5" title="Save">@lang('messages.Save')</button>
				<button type="reset" class="btn btn-default" onclick="window.location='{{ url('admin/localisation/weight_classes') }}'"  title="Cancel">@lang('messages.Cancel')</button>
			</div>
		</div>
	{!!Form::close();!!}
		</div>
	</div>
</div>

@endsection
