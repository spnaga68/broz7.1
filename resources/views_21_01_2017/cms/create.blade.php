@extends('layouts.admin')
@section('content')
 <script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script>
 <link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" /> 
 <script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>
  <script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/tinymce4.1/tinymce.min.js') }}"></script>
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
			<li>@lang('messages.Cms')</li>
		</ul>
		<h4>@lang('messages.Add Cms')</h4>
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

       {!!Form::open(array('url' => 'createcms', 'method' => 'post','class'=>'tab-form attribute_form','id'=>'cms_form','files' => true));!!} 



	<div class="tab-content mb30">
	<div class="tab-pane active" id="home3">
				
		<div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Title') <span class="asterisk">*</span></label>
                <div class="col-sm-10">
                    <?php $i = 0; foreach($languages as $langid => $language):?>
                    <div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
                          <input type="text" name="title[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Title').trans('messages.'.'('.$language->name.')');?>" class="form-control" value="{!! Input::old('title.'.$language->id) !!}"  maxlength="32" />
                     
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
                </div>
           </div>
           
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Url index')</label>
			<div class="col-sm-10">
			  <input type="text" name="index" value="{!! old('index') !!}"  maxlength="255" placeholder="@lang('messages.Url index')"  class="form-control"  />
			</div>
		</div>
				
		     <div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Content') <span class="asterisk">*</span></label>
                <div class="col-sm-10">
				<?php $i = 0; foreach($languages as $langid => $language):?>
                <div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
				<div class="row">
					<div class="col-sm-10">
                       <textarea class="form-control content" rows="10" cols="45"  name="content[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Content').trans('messages.'.'('.$language->name.')');?>" class="form-control" >{!! Input::old('content.'.$language->id) !!}</textarea>
					   </div>
					<div class="col-sm-2">
						<div class="input-group-btn">
							<button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button"><?php echo $language->name;?> <span class="caret"></span></button>
							<ul class="dropdown-menu pull-right">
								<?php foreach($languages as $sublangid => $sublanguage):?>
									<li><a href="javascript:YL.Language.fieldchange(<?php echo $sublanguage->id;?>)"> <?php echo trans('messages.'.$sublanguage->name);?></a></li>
								<?php endforeach;?>
							</ul>
						</div><!-- input-group-btn -->
					</div>
					</div>
                </div>
				<?php $i++; endforeach;?>
                </div>
            </div>
            		
				<div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Meta Keywords')</label>
                <div class="col-sm-10">
                    <?php $i = 0; foreach($languages as $langid => $language):?>
                    <div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
                          <input type="text" maxlength="255" name="meta_keywords[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Meta Keywords').trans('messages.'.'('.$language->name.')');?>" class="form-control" value="{!! Input::old('meta_keywords.'.$language->id) !!}"  />
                     
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
                </div>
           </div>
		
		   <div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Meta Description')</label>
                <div class="col-sm-10">
				<?php $i = 0; foreach($languages as $langid => $language):?>
                <div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
				<div class="row ">
					<div class="col-sm-10" style="padding-right: 0;">
                       <textarea class="form-control"  rows="8" cols="100"  name="meta_description[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Meta Description').trans('messages.'.'('.$language->name.')');?>" class="form-control" >{!! Input::old('meta_description.'.$language->id) !!}</textarea>
					</div>
					<div class="col-sm-2" style="padding-left: 0;">
                    <div class="input-group-btn">
                        <button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button"><?php echo $language->name;?> <span class="caret"></span></button>
                        <ul class="dropdown-menu pull-right">
                            <?php foreach($languages as $sublangid => $sublanguage):?>
                               <li><a href="javascript:YL.Language.fieldchange(<?php echo $sublanguage->id;?>)"> <?php echo trans('messages.'.$sublanguage->name);?></a></li>
                            <?php endforeach;?>
                        </ul>
                    </div><!-- input-group-btn -->
					</div>
				</div>
                </div>
            <?php $i++; endforeach;?>
                </div>
            </div>
            
           <div class="form-group">
					  <label  class="col-sm-2 control-label">@lang('messages.Status')</label>
						<div class="col-sm-10">
                        <?php $checked = ""; ?>
                        <input type="checkbox" class="toggle" name="status" data-size="small" <?php echo $checked;?> data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
						</div>
		   </div>
		   	
       </div>
		<div class="panel-footer">
		<button class="btn btn-primary mr5" title="Save">@lang('messages.Save')</button>
		<button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('admin/cms') }}'">@lang('messages.Cancel')</button>
		</div>
        </div>
      
 {!!Form::close();!!} 
</div></div></div>

<script type="text/javascript">
	
    $(window).load(function(){
        tinymce.init({
            menubar : false,statusbar : true,plugins: [
                "advlist autolink lists link image charmap print preview hr anchor pagebreak code",
                "emoticons template paste textcolor colorpicker textpattern"
            ],
            toolbar1: "code | insertfile undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image preview | forecolor backcolor | fontsizeselect",
            height:'450px',
            selector: "textarea.content"
         });         

    });
</script>
@endsection
