@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script> 
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
			<li>@lang('messages.Categories')</li>
		</ul>
		<h4>@lang('messages.Edit Category') - {{ $infomodel->getLabel('category_name',getAdminCurrentLang(),$data->id) }}</h4>
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

       {!!Form::open(array('url' => ['updatecategory', $data->id], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'category_form','files' => true));!!} 
	<div class="tab-content mb30">
	<div class="tab-pane active" id="home3">
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Category type')</label>
			<div class="col-sm-10">
				<select id="category_type" name="category_type" class="form-control" >
					<option value="">@lang('messages.Select Category Type')</option>
					@if (count(getCategoryTypes()) > 0)
						@foreach (getCategoryTypes() as $key => $type)
							<option value="{{ $key }}"  <?php if($data->category_type==$key){ echo "selected"; } ?>><?php echo trans('messages.'.$type); ?></option>
						@endforeach
					@endif
			  </select>
			</div>
		</div>
		<div class="form-group" id="head_category">
				<label class="col-sm-2 control-label">@lang('messages.Vendor Category')<span class="asterisk">*</span></label>
				<div class="col-sm-10">
				<?php //echo $data->head_category_id."asdf"; ?>
					<select id="head_categories" name="head_category" class="form-control" data-placeholder="@lang('messages.Select Category')">
						<option value="">@lang('messages.Select vendor category')</option>
						<?php 
						$cdata = gethead_categories(); ?>
						
						@if (count($cdata) > 0)
							@foreach ($cdata as $key => $val)
								<option value="{{ $val->id }}" <?php if($data->parent_id==$val->id) { echo"selected"; } ?>>{{ $val->category_name }}</option>
							@endforeach
						@endif
					</select>
				</div>
		</div>
		
	<div class="form-group" id="sub_category_check" style="<?php echo ($data->category_type==1)?'display:block;':'display:none;'; ?>">
				<label class="col-sm-2 control-label">@lang('messages.Is Category')<span class="asterisk">*</span></label>
				<div class="col-sm-10">
					<label class="radio-inline">
						<?php //echo $data->category_level;?>
						<input type="radio" name="is_category_type" id="inlineRadio1" <?php echo ($data->category_level==2)?'checked="checked"':''; ?> value="0"> @lang('messages.Main Category')
					</label>
					<label class="radio-inline">
						<input type="radio" name="is_category_type" id="inlineRadio2" <?php echo ($data->category_level == 3)?'checked="checked"':''; ?>  value="1"> @lang('messages.Sub Category')
					</label>
				</div>
			</div>
				<?php $cdata = getMainCategoryLists1();  ?>
			<div class="form-group" id="sub_category_data" style="<?php echo ($data->category_level == 3)?'display:block;':'display:none;'; ?>">
				<label class="col-sm-2 control-label">@lang('messages.Main Category')<span class="asterisk">*</span></label>
				<div class="col-sm-10">
					<select id="main_category" name="main_category" class="form-control" >
						<option value="">@lang('messages.Select Main Category')</option>
					
						@if (count($cdata) > 0)
							@foreach ($cdata as $key => $val)
								<option value="{{ $val->id }}" <?php echo ($data->head_category_ids==$val->id)?'selected="selected"':''; ?>>{{ $val->category_name }}</option>
							@endforeach
						@endif
					</select>
				</div>
			</div> 
		<div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Name') <span class="asterisk">*</span></label>
                <div class="col-sm-10">
                    <?php $i = 0; foreach($languages as $langid => $language):?>
                    <div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
                          <input type="text" name="category_name[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Name').trans('messages.'.'('.$language->name.')');?>" class="form-control" value="{{$infomodel->getLabel('category_name',$language->id,$data->id)}}"  maxlength="30" />
                     
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
			  <input type="text" name="url_key" value="<?php echo $data->url_key; ?>"  maxlength="100" placeholder="@lang('messages.Url index')"  class="form-control"  />
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Sort order')</label>
			<div class="col-sm-10">
			  <input type="number"  name="sort_order" value="{!! $data->sort_order !!}"   min="1" max="100" placeholder="@lang('messages.Sort order')"  class="form-control"  />
			</div>
		</div>

		
		<div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Description') <span class="asterisk">*</span></label>
                <div class="col-sm-10">
				<?php $i = 0; foreach($languages as $langid => $language):?>
                <div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
				<div class="row">
					<div class="col-sm-10" style="padding-right: 0;">
                       <textarea class="form-control"  rows="3" cols="150" name="description[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Description').trans('messages.'.'('.$language->name.')');?>" class="form-control" >{{$infomodel->getLabel('description',$language->id,$data->id)}}</textarea>
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
                          <input type="text"  name="meta_title[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Meta Title').trans('messages.'.'('.$language->name.')');?>" class="form-control" value="{{$infomodel->getLabel('meta_title',$language->id,$data->id)}}"  />
                     
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
                          <input type="text"  name="meta_keywords[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Meta Keywords').trans('messages.'.'('.$language->name.')');?>" class="form-control" value="{{$infomodel->getLabel('meta_keywords',$language->id,$data->id)}}"  />
                     
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
                       <textarea class="form-control" rows="3" cols="150"  name="meta_description[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Meta Description').trans('messages.'.'('.$language->name.')');?>" class="form-control" >{{$infomodel->getLabel('meta_description',$language->id,$data->id)}}</textarea>
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
				<label class="col-sm-2 control-label">@lang('messages.Image')</label>
				<div class="col-sm-10">
					<input type="file" name="category_image"/>
					<span class="help-text">@lang('messages.Please upload 1140X412 images for better quality')</span>
				</div>
			</div>
			<?php if($data->image){ ?>
				<div class="form-group">
					<label class="col-sm-2 control-label"></label>
					<div class="col-sm-10">
						<div class="thmb">
							<div class="thmb-prev" >
								<img src="<?php echo url('/assets/admin/base/images/category/'.$data->image.'?'.time()); ?>" class="img-responsive" alt="{{ $infomodel->getLabel('category_name',getAdminCurrentLang(),$data->id) }}" width="100px" height="100px">
							</div>
						</div>
					</div>
				</div>
			<?php } ?>
			<div class="form-group">
				<label class="col-sm-2 control-label">@lang('messages.White image')</label>
				<div class="col-sm-10">
					<input type="file" id="category_white_image" name="category_white_image"/>
				</div>
			</div>
			<?php if($data->category_white_image){ ?>
				<div class="form-group">
					<label class="col-sm-2 control-label"></label>
					<div class="col-sm-10">
						<div class="thmb">
							<div class="thmb-prev" >
								<img src="<?php echo url('/assets/admin/base/images/category/white_category/'.$data->category_white_image.'?'.time()); ?>" class="img-responsive" alt="{{ $infomodel->getLabel('category_name',getAdminCurrentLang(),$data->id) }}" width="100px" height="100px">
							</div>
						</div>
					</div>
				</div>
			<?php } ?>
			<div class="form-group">
				<label class="col-sm-2 control-label">@lang('messages.Mobile Banner Image')</label>
				<div class="col-sm-10">
					<input type="file" name="mobile_banner_image" />
					<span class="help-text">@lang('messages.Please upload 45X45 images for better quality')</span>
				</div>
			</div>
			<?php if($data->mobile_banner_image){ ?>
				<div class="form-group">
					<label class="col-sm-2 control-label"></label>
					<div class="col-sm-10">
						<div class="thmb">
							<div class="thmb-prev" >
								<img src="<?php echo url('/assets/admin/base/images/category/mobile_banner/'.$data->mobile_banner_image.'?'.time()); ?>" class="img-responsive" alt="{{ $infomodel->getLabel('category_name',getAdminCurrentLang(),$data->id) }}" width="100px" height="100px">
							</div>
						</div>
					</div>
				</div>
			<?php } ?>
		<div class="form-group">
			<label  class="col-sm-2 control-label">@lang('messages.Status')</label>
			<div class="col-sm-10">
			<?php $checked = "";
			 if($data->category_status){ $checked = "checked=checked"; }?>
			<input type="checkbox" class="toggle" name="status" data-size="small" <?php echo $checked;?> data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
			</div>
		</div>
		
						
       </div>
		<div class="panel-footer">
		<button class="btn btn-primary mr5" title="Update">@lang('messages.Update')</button>
		<button type="reset" class="btn btn-default" onclick="window.location='{{ url('admin/category') }}'" title="Cancel">@lang('messages.Cancel')</button>
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
		$('#sub_category_data').hide();
	}
});
$("#category_type").change();
$('input:radio[name="is_category_type"]').change(function() {
	var val = $('#category_type').val();
	if ($(this).val() == 1 && val==1) {
					var cid, token, url, data;
	token = $('input[name=_token]').val();
	cid = [$('#head_categories').val()];

	url = '{{url('list/ProductMaincategorylist')}}';
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
		$('#sub_category_data').show();
		
	} else {
		$('#sub_category_data').hide();
	}
});
$('#head_categories').change(function(){
		
	var  token, url, data;
	var cid = [$(this).val()];
	token = $('input[name=_token]').val();
	url = '{{url('list/ProductMaincategorylist')}}';
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
