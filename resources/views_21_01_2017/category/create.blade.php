@extends('layouts.admin')
@section('content')
 <script type="text/javascript" src="{{ URL::asset('assets/js/media/picturecut/jquery.picture.cut.js') }}"></script>
  <script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/jquery-ui-1.10.3.min.js') }}"></script>
 <script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script> 
 <link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" /> 
  <link href="{{ URL::asset('assets/admin/base/css/jquery-ui-1.10.3.css') }}" media="all" rel="stylesheet" type="text/css" /> 
<!-- Nav tabs -->
<div class="pageheader">
<div class="media">
	<div class="pageicon pull-left">
		<i class="fa fa-home"></i>
	</div>
	<div class="media-body">
		<ul class="breadcrumb">
			<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
			<li>@lang('messages.Categories')</li>
		</ul>
		<h4>@lang('messages.Add Category')</h4>
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
       {!!Form::open(array('url' => 'createcategory', 'method' => 'post','class'=>'tab-form attribute_form','id'=>'category_form','files' => true));!!} 

	<div class="tab-content mb30">
		<div class="tab-pane active" id="home3">
			<div class="form-group">
				<label class="col-sm-2 control-label">@lang('messages.Category type')<span class="asterisk">*</span></label>
				<div class="col-sm-10">
				  <select id="category_type" name="category_type" class="form-control" >
					<option value="">@lang('messages.Select Category Type')</option>
					@if (count(getCategoryTypes()) > 0)
						@foreach (getCategoryTypes() as $key => $type)
							<option value="{{ $key }}" <?php echo (old('category_type')==$key)?'selected="selected"':''; ?> ><?php echo trans('messages.'.$type); ?></option>
						@endforeach
					@endif
				  </select>
				</div>
			</div>
			
			<div class="form-group" id="head_category">
				<label class="col-sm-2 control-label">@lang('messages.Head Category')<span class="asterisk">*</span></label>
				<div class="col-sm-10">
					<select id="head_categories" name="head_category" class="form-control" >
						<option value="">@lang('messages.Select head category')</option>
						<?php $cdata = gethead_categories(); ?>
						@if (count($cdata) > 0)
							@foreach ($cdata as $key => $val)
								<option value="{{ $val->id }}" <?php echo (old('head_category')==$val->id)?'selected="selected"':''; ?>>{{ $val->category_name }}</option>
							@endforeach
						@endif
					</select>
				</div>
			</div>
			
			<div class="form-group" id="sub_category_check" style="<?php echo (old('category_type')==1)?'display:block;':'display:none;'; ?>">
				<label class="col-sm-2 control-label">@lang('messages.Is Category')<span class="asterisk">*</span></label>
				<div class="col-sm-10">
					<label class="radio-inline">
						<input type="radio" name="is_category_type" id="inlineRadio1" <?php echo (old('is_category_type')==1)?'':'checked="checked"'; ?> value="0"> @lang('messages.Main Category')
					</label>
					<label class="radio-inline">
						<input type="radio" name="is_category_type" id="inlineRadio2" <?php echo (old('is_category_type')==1)?'checked="checked"':''; ?>  value="1"> @lang('messages.Sub Category')
					</label>
				</div>
			</div>
			
			
			
			<div class="form-group" id="sub_category_data" style="<?php echo (old('is_category_type')==1)?'display:block;':'display:none;'; ?>">
				<label class="col-sm-2 control-label">@lang('messages.Main Category')<span class="asterisk">*</span></label>
				<div class="col-sm-10">
					<select id="main_category" name="main_category" class="form-control" >
						<option value="">@lang('messages.Select Main Category')</option>
						<?php
							/* $cdata = getCategoryLists(1);
							@if (count($cdata) > 0)
								@foreach ($cdata as $key => $val)
									<option value="{{ $val->id }}" <?php echo (old('main_category')==$val->id)?'selected="selected"':''; ?>>{{ $val->category_name }}</option>
								@endforeach
							@endif */
						?>
					</select>
				</div>
			</div>
			
			<div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Name') <span class="asterisk">*</span></label>
                <div class="col-sm-10">
                    <?php $i = 0; foreach($languages as $langid => $language):?>
                    <div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
                          <input type="text" name="category_name[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Name').trans('messages.'.'('.$language->name.')');?>" class="form-control" value="{!! Input::old('category_name.'.$language->id) !!}" maxlength="30" />
                     
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
				  <input type="text"  name="url_key" value="{!! old('url_key') !!}" maxlength="100" placeholder="@lang('messages.Url index')"  class="form-control"  />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">@lang('messages.Sort order')</label>
				<div class="col-sm-10">
				  <input type="number" name="sort_order" value="{!! old('sort_order') !!}" maxlength="10" placeholder="@lang('messages.Sort order')"  class="form-control"  />
				</div>
			</div>
			<div class="form-group">
					<label class="col-sm-2 control-label">@lang('messages.Description') <span class="asterisk">*</span></label>
					<div class="col-sm-10">
					<?php $i = 0; foreach($languages as $langid => $language):?>
					<div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
					<div class="row">
						<div class="col-sm-10" style="padding-right: 0;">
						   <textarea class="form-control" rows="3" cols="150"  maxlength="250" name="description[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Description').trans('messages.'.'('.$language->name.')');?>" class="form-control" >{!! Input::old('description.'.$language->id) !!}</textarea>
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
					<span class="help-text">@lang('messages.Max length 250')</span>
					</div>
			</div>
			<div class="form-group">
					<label class="col-sm-2 control-label">@lang('messages.Meta Title')</label>
					<div class="col-sm-10">
						<?php $i = 0; foreach($languages as $langid => $language):?>
						<div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
							  <input type="text" maxlength="255"  name="meta_title[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Meta Title').trans('messages.'.'('.$language->name.')');?>" class="form-control" value="{!! Input::old('meta_title.'.$language->id) !!}"  />
						 
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
						<span class="help-text">@lang('messages.Max length 250')</span>
					</div>
			   </div>
			<div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Meta Keywords')</label>
                <div class="col-sm-10">
                    <?php $i = 0; foreach($languages as $langid => $language):?>
                    <div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
                          <input type="text" maxlength="255"  name="meta_keywords[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Meta Keywords').trans('messages.'.'('.$language->name.')');?>" class="form-control" value="{!! Input::old('meta_keywords.'.$language->id) !!}"  />
                     
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
                    <span class="help-text">@lang('messages.Max length 250')</span>
                </div>
           </div>
		   <div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Meta Description')</label>
                <div class="col-sm-10">
				<?php $i = 0; foreach($languages as $langid => $language):?>
                <div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
				<div class="row ">
					<div class="col-sm-10" style="padding-right: 0;">
                       <textarea class="form-control" rows="3" cols="150"  name="meta_description[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Meta Description').trans('messages.'.'('.$language->name.')');?>" class="form-control" >{!! Input::old('meta_description.'.$language->id) !!}</textarea>
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
            <span class="help-text">@lang('messages.Max length 250')</span>
                </div>
            </div>
		<?php /** 
		 <div class="form-group">
                    <label class="col-sm-2 control-label">@lang('messages.Image')</label>
                    <div class="col-sm-6">
                        <div class="input-group">
                            <div id="container_image"></div>
                        </div><!-- input-group -->
                    </div>
        </div>
        */ ?>
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Image')<span class="asterisk">*</span></label>
			<div class="col-sm-10">
				<input type="file" name="category_image"/>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Mobile Banner Image')<span class="asterisk">*</span></label>
			<div class="col-sm-10">
				<input type="file" name="mobile_banner_image" />
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
				<button type="reset" class="btn btn-default" onclick="window.location='{{ url('admin/category') }}'"  title="Cancel">@lang('messages.Cancel')</button>
			</div>
        </div>
		{!!Form::close();!!}
		</div>
	</div>
</div>
<script type="text/javascript">
$('#category_type').change(function(){
	var val = $('#category_type').val();
	if(val==1){
		$('#sub_category_check').show();
		$('#head_category').show();
	} else {
		$('#sub_category_check').hide();
		$('#head_category').hide();
		$('#sub_category_data').hide();
	}
});
$("#category_type").change();
$('input:radio[name="is_category_type"]').change(function() {
	var val = $('#category_type').val();
	if ($(this).val() == 1 && val==1) {
		$('#sub_category_data').show();
	} else {
		$('#sub_category_data').hide();
	}
});
$('#head_categories').change(function(){
	var cid, token, url, data;
	token = $('input[name=_token]').val();
	cid = $(this).val();
	url = '{{url('list/Maincategorylist')}}';
	data = {cid: cid};
	$.ajax({
		url: url,
		headers: {'X-CSRF-TOKEN': token},
		data: data,
		type: 'POST',
		datatype: 'JSON',
		success: function (resp) {
			//console.log('in--'+resp.data);
			$('#main_category').empty();
			if(resp.data==''){
				$('#main_category').append($("<option></option>").attr("value","").text('No data there..')); 
			} else {
				$.each(resp.data, function(key, value) {
					//console.log(value['id']+'=='+value['city_name']);
					$('#main_category').append($("<option></option>").attr("value",value['id']).text(value['category_name'])); 
			   });
			}
		}
	});
});
</script>
@endsection
