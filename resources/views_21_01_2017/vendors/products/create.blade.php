@extends('layouts.vendors')
@section('content')
<link href="{{ URL::asset('assets/admin/base/css/bootstrap-timepicker.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/admin/base/css/select2.css') }}" media="all" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" /> 
<!-- Nav tabs -->
<div class="pageheader">
	<div class="media">
		<div class="pageicon pull-left">
			<i class="fa fa-home"></i>
		</div>
		<div class="media-body">
			<ul class="breadcrumb">
				<li><a href="#"><i class="glyphicon glyphicon-home"></i>@lang('messages.Vendors')</a></li>
				<li>@lang('messages.Products')</li>
			</ul>
			<h4>@lang('messages.Add Product')</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->


<div class="contentpanel">
<div class="col-md-12">
<div class="row panel panel-default">
<div class="grid simple">
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
	<ul class="nav nav-justified nav-wizard nav-pills">
		<li @if(old('tab_info')=='login_info' || old('tab_info')=='') class="active" @endif><a href="#login_info" class="login_info" data-toggle="tab"><strong>@lang('messages.General Information')</strong></a></li>
		<li @if(old('tab_info')=='delivery_info') class="active" @endif ><a href="#delivery_info" class="delivery_info" data-toggle="tab"><strong>@lang('messages.Data Information')</strong></a></li>
	</ul>
	{!!Form::open(array('url' => 'vendor/product_create', 'method' => 'post','class'=>'panel-wizard','id'=>'product_form','files' => true));!!}
		<div class="tab-content tab-content-simple mb30 no-padding" >
			<div class="tab-pane active" id="login_info">
				<legend>@lang('messages.General Information')</legend>

				<div class="form-group">
					<label class="col-sm-3 control-label">@lang('messages.Product Name') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<?php $i = 0; foreach($languages as $langid => $language):?>
						<div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
							  <input type="text" name="product_name[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Product Name').trans('messages.'.'('.$language->name.')');?>" class="form-control" value="{!! Input::old('product_name.'.$language->id) !!}"  />
						 
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
					<label class="col-sm-3 control-label">@lang('messages.Product URL') </label>
					<div class="col-sm-7">
					  <input type="text" name="product_url" value="{!! old('product_url') !!}"  maxlength="255" placeholder="@lang('messages.Product URL')"  class="form-control" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">@lang('messages.Description') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
					<?php $i = 0; foreach($languages as $langid => $language):?>
						<div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
							<textarea class="form-control" rows="4" maxlength="250" name="description[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Description').trans('messages.'.'('.$language->name.')');?>" class="form-control" >{!! Input::old('description.'.$language->id) !!}</textarea>
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
					<label class="col-sm-3 control-label">@lang('messages.Outlet Name') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<select name="outlet" id="outlet_id" class="form-control" >
							<?php $outlet = getOutletList(Session::get('vendor_id'));?>
							@if(count($outlet) > 0)
								<option value="">@lang('messages.Select Outlet')</option>
								@foreach($outlet as $list)
									<option value="{{$list->id}}" <?php echo (old('outlet')==$list->id)?'selected="selected"':''; ?> >{{ucfirst($list->outlet_name)}}</option>
								@endforeach
							@else
								<option value="">@lang('messages.No outlet found')</option>
							@endif
						</select>
					</div>
			   </div>
				<div class="form-group">
					<label class="col-sm-3 control-label">@lang('messages.Category Name') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<select name="category" id="category_id" class="form-control" >
							<?php $cdata = getVendorsubCategoryLists(Session::get('vendor_id')); ?>
							@if (count($cdata) > 0)
								<option value="">@lang('messages.Select Category')</option>
								@foreach ($cdata as $key => $val)
									<option value="{{ $val->id }}" <?php echo (old('category')==$val->id)?'selected="selected"':''; ?>>{{ $val->category_name }}</option>
								@endforeach
							@else
								<option value="">@lang('messages.No category found')</option>
							@endif
						</select>
					</div>
			   </div>
				<div class="form-group">
					<label class="col-sm-3 control-label">@lang('messages.Sub Category Name') <span class="asterisk">*</span></label>
					<?php $told =old('sub_category'); ?>
						<select id="sub_category" name="sub_category" data-placeholder="@lang('messages.Sub Category Name')" class="form-control">
							<?php if(!empty(old('category'))){ 
								$sub_category = getSubCategoryLists(1,old('category'));?>
								<option value="">@lang('messages.Select sub category name')</option>
								@foreach ($sub_category as $val)
									<option value="{{ $val->id }}" <?php echo ($val->id == $told)?"selected" :"" ;?> >{{  ucfirst($val->category_name) }}</option>
								@endforeach
							<?php } else { ?>
								<option value="">@lang('messages.Select main category first')</option>
							<?php } ?>
						</select>
					</div>
			  
				
				<div class="form-group">
					<label class="col-sm-3 control-label">@lang('messages.Meta Title') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
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
					<label class="col-sm-3 control-label">@lang('messages.Meta Keywords') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
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
					<label class="col-sm-3 control-label">@lang('messages.Meta Description') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
					<?php $i = 0; foreach($languages as $langid => $language):?>
						<div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
							<textarea class="form-control" rows="3" name="meta_description[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Meta Description').trans('messages.'.'('.$language->name.')');?>" class="form-control" >{!! Input::old('meta_description.'.$language->id) !!}</textarea>
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
					<label class="col-sm-3 control-label">@lang('messages.Product Image') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<input type="file" name="product_image" placeholder="@lang('messages.Product Image')" />
					</div>
				</div>

				<?php /** 
				<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Publish Status') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<?php $checked = ""; ?>
						<input type="checkbox" class="toggle" name="publish_status" data-size="small" <?php echo $checked;?> data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
					</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Status') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<?php $checked1 = ""; ?>
						<input type="checkbox" class="toggle" name="active_status" data-size="small" <?php echo $checked1;?> data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
					</div>
				</div>
				*/ ?>
				
			</div>
			<div class="tab-pane" id="delivery_info"> 
				<legend>@lang('messages.Data Information')</legend>
				<div class="form-group">
					<label class="col-sm-3 control-label ">@lang('messages.Weight Class') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<select name="weight_class" id="weight_class" class="form-control">
							<option value="">@lang('messages.Select Weight Class')</option>
							<?php $weight_class = getWeightClass(); ?>
								@foreach($weight_class as $list)
									<option value="{{$list->id}}" <?php echo (old('weight_class')==$list->id)?'selected="selected"':''; ?> >{{$list->title}}</option>
								@endforeach
						</select>
					</div> 
				</div>
				<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Weight Value') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<input type="text" name="weight_value" value="{!! old('weight_value') !!}"  maxlength="255" placeholder="@lang('messages.Weight Value')"  class="form-control" />
						<span class="help-block">@lang('messages.Weight Value') @lang('messages.weight_value_help_text')</span>
					</div>
				</div>
					<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Total Quantity') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<input type="text" name="total_quantity" value="{!! old('total_quantity') !!}"  maxlength="255" placeholder="@lang('messages.Total Quantity')"  class="form-control" />
					</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Original Price') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<input type="text" name="original_price" value="{!! old('original_price') !!}"  maxlength="255" placeholder="@lang('messages.Original Price')"  class="form-control" />
					</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Discounted Price') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<input type="text" name="discount_price" value="{!! old('discount_price') !!}"  maxlength="255" placeholder="@lang('messages.Discount Price')"  class="form-control" />
					</div>
				</div>
			</div>
			<div class="form-group Loading_Img" style="display:none;">
				<div class="col-sm-4">
					<i class="fa fa-spinner fa-spin fa-3x"></i><strong style="margin-left: 3px;">@lang('messages.Processing...')</strong>
				</div>
			</div>		
				</div><!-- panel-default -->
			</div>
			<div class="panel-footer Submit_button">
				<input type="hidden" name="tab_info" class="tab_info" value="">
				<button type="submit" onclick="HideButton('Submit_button','Loading_Img');" onsubmit="HideButton('Submit_button','Loading_Img');" class="btn btn-primary mr5" title="Save">@lang('messages.Save')</button>
				<button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('vendor/products') }}'">@lang('messages.Cancel')</button>
			</div><!-- panel-footer -->
			{!!Form::close();!!} 
		</div>
	</div>
</div>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/jquery-ui-1.10.3.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/bootstrap-timepicker.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>
<script type="text/javascript">
$( document ).ready(function() {
	//$('#sub_category').select2();
	@if(old('tab_info')=='login_info')
		$('.tab_info').val('login_info');
		$('#login_info').show();
		$('#delivery_info').hide();
	@elseif(old('tab_info')=='delivery_info')
		$('.tab_info').val('delivery_info');
		$('#login_info').hide();
		$('#delivery_info').show();
	@endif
});
$(".login_info").on("click", function(){
	$('.tab_info').val('login_info');
	$('#delivery_info').hide();
	$('#login_info').show();
});
$(".delivery_info").on("click", function(){
	$('.tab_info').val('delivery_info');
	$('#delivery_info').show();
	$('#login_info').hide();
});
$(window).load(function(){
	$('form').preventDoubleSubmission();	
});
$('#vendor_id').change(function(){
	var cid, token, url, data;
	token = $('input[name=_token]').val();
	cid = $('#vendor_id').val();
	url = '{{url('list/OutletList')}}';
	data = {cid: cid};
	$.ajax({
		url: url,
		headers: {'X-CSRF-TOKEN': token},
		data: data,
		type: 'POST',
		datatype: 'JSON',
		success: function (resp) {
			//console.log('in--'+resp.data);
			$('#outlet_id').empty();
			$('#category_id').empty();
			if(resp.data==''){
				$('#outlet_id').append($("<option></option>").attr("value","").text('No data there..')); 
			} else {
				$('#outlet_id').append($("<option></option>").attr("value","").text('Select Outlet'));
				$.each(resp.data, function(key, value) {
					//console.log(value['id']+'=='+value['city_name']);
					$('#outlet_id').append($("<option></option>").attr("value",value['id']).text(value['outlet_name'])); 
			   });
			}
			if(resp.cdata==''){
				$('#category_id').append($("<option></option>").attr("value","").text('No data there..')); 
			} else {
				$('#category_id').append($("<option></option>").attr("value","").text('Select Category'));
				$.each(resp.cdata, function(key, value) {
					//console.log(value['id']+'=='+value['city_name']);
					$('#category_id').append($("<option></option>").attr("value",value['id']).text(value['category_name'])); 
			   });
			}
		}
	});
});

$('#category_id').change(function(){
	
	var cid, token, url, data;
	token = $('input[name=_token]').val();
	cid = $('#category_id').val();
	url = '{{url('list/SubCategoryList')}}';
	data = {cid: cid};
	$.ajax({
		url: url,
		headers: {'X-CSRF-TOKEN': token},
		data: data,
		type: 'POST',
		datatype: 'JSON',
		success: function (resp) {
			//console.log('in--'+resp.data);
			$('#sub_category').empty();
			if(resp.data==''){
				$('#sub_category').append($("<option></option>").attr("value","").text('No data there..')); 
			} else {
				$.each(resp.data, function(key, value) {
					//console.log(value['id']+'=='+value['city_name']);
					$('#sub_category').append($("<option></option>").attr("value",value['id']).text(value['category_name'])); 
			   });
			}
		}
	});
});
</script>
@endsection