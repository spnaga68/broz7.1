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
		<h4>@lang('messages.General')</h4>
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

<?php //echo '<pre>'; print_r($settings);exit;?>
@if (Session::has('message'))
	<div class="admin_sucess_common">
	<div class="admin_sucess">
<div class="alert alert-info success">
<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>
    {{ Session::get('message') }}</div></div></div>
@endif
       {!!Form::open(array('url' => ['admin/settings/updategeneral', $settings->id], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'settings_edit_form','files' => true));!!}

<div class="col-md-12">
<div class="row panel panel-default">
<div class="grid simple">
	<div id="general" class="panel-heading">
	<h4 class="panel-title">@lang('messages.General')</h4>
	<p>@lang('messages.Admin general settings')</p>
		<div class="tools">
			<a class="collapse" href="javascript:;"></a>
		</div>
	</div>
	<ul class="nav nav-tabs"></ul>
	<div class="panel-body">
		
	<div id="site_name" class="form-group">
		<label class="form-label">@lang('messages.Site Name') <span class="asterisk">*</span></label>
		<span class="help">@lang('messages.This name will visible as a title in frontend')</span>
		<div class="controls">
			<?php $i = 0; foreach($languages as $langid => $language):?>
						<div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
							  <input type="text" maxlength="255"  name="site_name[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Site Name').trans('messages.'.'('.$language->name.')');?>" class="form-control" value="{{$infomodel->getLabel('site_name',$language->id,$settings->id)}}"  />
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
	
	<div id="site_name" class="form-group">
		<label class="form-label">@lang('messages.Site description') <span class="asterisk">*</span></label>
		<span class="help">@lang('messages.This description will visible as a title in frontend')</span>
		<div class="controls">
			<?php $i = 0; foreach($languages as $langid => $language):?>
						<div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
							  <input type="text" maxlength="255"  name="site_description[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Site description').trans('messages.'.'('.$language->name.')');?>" class="form-control" value="{{$infomodel->getLabel('site_description',$language->id,$settings->id)}}"  />
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
	
	<div id="site_owner" class="form-group">
		<label class="form-label">@lang('messages.Site Owner') <span class="asterisk">*</span></label>
		<span class="help">@lang('messages.This name is used to contact purpose')</span>
		<div class="controls">
			<input class="form-control" maxlength="36" type="text" value="{{ $settings->site_owner }}" required name="site_owner">
		</div>
	</div>
	
	<div id="email" class="form-group">
		<label class="form-label">@lang('messages.E-Mail') <span class="asterisk">*</span></label>
		<span class="help">@lang('messages.This eamil is used to contact purpose')</span>
		<div class="controls">
			<input class="form-control" maxlength="54" type="email" value="{{ $settings->email }}" required name="email">
		</div>
	</div>
	
	<div id="mobile_number" class="form-group">
		<label class="form-label">@lang('messages.Telephone') <span class="asterisk">*</span></label>
		<span class="help">	@lang('messages.This telephone is used to contact purpose')</span>
		<div class="controls">
		<input class="form-control" type="tel"  maxlength="15" value="{{ $settings->telephone }}" required name="telephone">
		</div>
	</div>
	
	<div id="fax" class="form-group">
		<label class="form-label">@lang('messages.Fax') <span class="asterisk">*</span></label>
		<span class="help">	@lang('messages.This fax is used to contact purpose')</span>
		<div class="controls">
		<input class="form-control" type="text"  maxlength="36" value="{{ $settings->fax }}" required name="fax">
		</div>
	</div>
	
	<div id="min_fund_request" class="form-group">
		<label class="form-label">@lang('messages.Minimum Fund Request') <span class="asterisk">*</span></label>
		<span class="help">	@lang('messages.Minimum Fund Request Text')</span>
		<div class="controls">
		<input class="form-control" type="text"  maxlength="8" value="{{ $settings->min_fund_request }}" required name="min_fund_request">
		</div>
	</div>

	<div id="max_fund_request" class="form-group">
		<label class="form-label">@lang('messages.Maximum Fund Request') <span class="asterisk">*</span></label>
		<span class="help">	@lang('messages.Maximum Fund Request Text')</span>
		<div class="controls">
		<input class="form-control" type="text" maxlength="8" value="{{ $settings->country_code }}" required name="max_fund_request">
		</div>
	</div>

	<div id="max_fund_request" class="form-group">
		<label class="form-label">@lang('messages.Country Code') <span class="asterisk">*</span></label>
		<span class="help">	@lang('messages.Country Code')</span>
		<div class="controls">
		<input class="form-control" type="text" maxlength="8" value="{{ $settings->max_fund_request }}" required name="country_code">
		</div>
	</div>
	<div id="Geocode" class="form-group">
		<label class="form-label">@lang('messages.Geocode') <span class="asterisk">*</span></label>
		<span class="help">	@lang('messages.This geocode is used to contact purpose')</span>
		<div class="controls">
		<input class="form-control" type="text"  maxlength="256" value="{{ $settings->geocode }}" required name="geocode">
		</div>
	</div>


	<div id="meta_title" class="form-group">
		<label class="form-label">@lang('messages.Meta Title') <span class="asterisk">*</span></label>
		<span class="help">@lang('messages.The meta title is the HTML code that specifies the title of a certain web page.')</span>
		<div class="controls">
		<?php $i = 0; foreach($languages as $langid => $language):?>
						<div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
							  <input type="text" maxlength="255"  name="meta_title[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Meta Title').trans('messages.'.'('.$language->name.')');?>" class="form-control" value="{{$infomodel->getLabel('meta_title',$language->id,$settings->id)}}"  />
						 
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

	<div id="meta_keywords" class="form-group">
		<label class="form-label">@lang('messages.Meta Keyword') <span class="asterisk">*</span></label>
		<span class="help">@lang('messages.This keywords are used by search engines in adding a page to search index.')</span>
		<div class="controls">
		<?php $i = 0; foreach($languages as $langid => $language):?>
						<div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
							  <input type="text" maxlength="255"  name="meta_keywords[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Meta Keywords').trans('messages.'.'('.$language->name.')');?>" class="form-control" value="{{$infomodel->getLabel('meta_keywords',$language->id,$settings->id)}}" />
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

	<div id="meta_description" class="form-group">
		<label class="form-label">@lang('messages.Meta Description') <span class="asterisk">*</span></label>
		<span class="help">@lang('messages.The description meta tag includes a brief one- or two-sentence description of the page.')</span>
		<div class="controls">
			<?php $i = 0; foreach($languages as $langid => $language):?>
						<div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
							  <textarea  name="meta_description[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Meta Description').trans('messages.'.'('.$language->name.')');?>" class="form-control" >{{$infomodel->getLabel('meta_description',$language->id,$settings->id)}}</textarea>
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
			
			
			
			
			
			
			<?php /*$i = 0; foreach($languages as $langid => $language):?>
					<div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
					<div class="row">
						<div class="col-sm-10" style="padding-right: 0;">
						   <textarea class="form-control" rows="3" cols="150"  maxlength="250" name="meta_description[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Meta Description').trans('messages.'.'('.$language->name.')');?>" class="form-control"  value="{{$infomodel->getLabel('meta_description',$language->id,$settings->id)}}">{!! Input::old('meta_description.'.$language->id) !!}</textarea>
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
					<?php $i++; endforeach;*/?>
		</div>
	</div>

	<div id="ADMIN_LOGO" class="form-group">
	<label class="form-label">@lang('messages.Logo') </label>
	<span class="help">@lang('messages.Upload type is *.jpg, *.png, *.jpeg')</span>
		<div class="controls">
		<a target="_blank" onclick="imagePreview('ADMIN_LOGO_image'); return false;" href="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/logo/'.$settings->logo.'?'.time()); ?>">
		<img id="ADMIN_LOGO_image" class="small-image-preview v-middle"  alt="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/logo/'.$settings->logo.'?'.time()); ?>" src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/logo/159_81/'.$settings->logo.'?'.time()); ?>">
		</a>
		<input type="file" name="logo">
		<?php /** 
				<div class="ckbox ckbox-default">
				<input id="ADMIN_LOGO_delete" type="checkbox" value="1" name="logo_delete">
				<label for="ADMIN_LOGO_delete"> Delete Image</label>
					<input type="hidden" value="URL::asset('assets/front/'.Session::get("general")->theme.'/images/nextbraintech.png') }}" name="logo_value">
				</div>
				*/ ?>
		</div>
	</div>
	<div id="FRONT_LOGO" class="form-group">
	<label class="form-label">@lang('messages.Front logo') </label>
	<span class="help">@lang('messages.Upload type is *.jpg, *.png, *.jpeg')</span>
		<div class="controls">
		<a target="_blank" onclick="imagePreview('FRONT_LOGO_image'); return false;" href="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/logo/'.$settings->front_logo.'?'.time()); ?>">
		<img id="FRONT_LOGO_image" class="small-image-preview v-middle"  alt="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/logo/'.$settings->front_logo.'?'.time()); ?>" src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/logo/'.$settings->front_logo.'?'.time()); ?>">
		</a>
		<input type="file" name="front_logo">
		<?php /** 
				<div class="ckbox ckbox-default">
				<input id="ADMIN_LOGO_delete" type="checkbox" value="1" name="logo_delete">
				<label for="ADMIN_LOGO_delete"> Delete Image</label>
					<input type="hidden" value="URL::asset('assets/front/'.Session::get("general")->theme.'/images/nextbraintech.png') }}" name="logo_value">
				</div>
				*/ ?>
		</div>
	</div>
	<?php /*<div id="ARABIC_LOGO" class="form-group">
	<label class="form-label">@lang('messages.Arabic logo') </label>
	<span class="help">@lang('messages.Upload type is *.jpg, *.png, *.jpeg')</span>
		<div class="controls">
		<a target="_blank" onclick="imagePreview('ARABIC_LOGO_image'); return false;" href="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/logo/159_81/'.$settings->arabic_logo.'?'.time()); ?>">
		<img id="ARABIC_LOGO_image" class="small-image-preview v-middle"  alt="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/logo/159_81/'.$settings->arabic_logo.'?'.time()); ?>" src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/logo/159_81/'.$settings->arabic_logo.'?'.time()); ?>">
		</a>
		<input type="file" name="arabic_logo">
		<?php /** 
				<div class="ckbox ckbox-default">
				<input id="ADMIN_LOGO_delete" type="checkbox" value="1" name="logo_delete">
				<label for="ADMIN_LOGO_delete"> Delete Image</label>
					<input type="hidden" value="URL::asset('assets/front/'.Session::get("general")->theme.'/images/nextbraintech.png') }}" name="logo_value">
				</div>
				
		</div>
	</div>*/ ?>
	<div id="FRONT_LOGO" class="form-group">
	<label class="form-label">@lang('messages.Responsive logo') </label>
	<span class="help">@lang('messages.Upload type is *.jpg, *.png, *.jpeg')</span>
		<div class="controls">
		<a target="_blank" onclick="imagePreview('Responsive_logo_image'); return false;" href="<?php echo url('/assets/admin/base/images/'.$settings->responsive_logo.'?'.time()); ?>">
		<img id="Responsive_logo_image" class="small-image-preview v-middle"  alt="<?php echo url('/assets/admin/base/images/'.$settings->responsive_logo.'?'.time()); ?>" src="<?php echo url('/assets/admin/base/images/'.$settings->responsive_logo.'?'.time()); ?>">
		</a>
		<input type="file" name="responsive_logo">
		<?php /** 
				<div class="ckbox ckbox-default">
				<input id="ADMIN_LOGO_delete" type="checkbox" value="1" name="logo_delete">
				<label for="ADMIN_LOGO_delete"> Delete Image</label>
					<input type="hidden" value="URL::asset('assets/front/'.Session::get("general")->theme.'/images/nextbraintech.png') }}" name="logo_value">
				</div>
				*/ ?>
		</div>
	</div>
	<div id="FAVICON" class="form-group">
	<label class="form-label">@lang('messages.Favicon')</label>
	<span class="help">@lang('messages.Upload type is *.ico')</span>
			<div class="controls">
			<a target="_blank" onclick="imagePreview('FAVICON_image'); return false;" href="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/favicon/16_16/'.$settings->favicon.'?'.time()); ?>">
			<img id="FAVICON_image" class="small-image-preview v-middle"  alt="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/favicon/16_16/'.$settings->favicon.'?'.time()); ?>"  src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/favicon/16_16/'.$settings->favicon.'?'.time()); ?>">
			</a>
			<input type="file" name="favicon">
			<?php /** 
				<div class="ckbox ckbox-default">
				<input id="FAVICON_delete" type="checkbox" value="1" name="favicon_delete">
				<label for="FAVICON_delete"> Delete Image</label>
				<input type="hidden" value="{{ asset('favicon.png') }}" name="favicon_delete">
				</div>
				*/ ?>
			</div>
	</div>

	<div id="contact_address" class="form-group">
	<label class="form-label">@lang('messages.Company contact address') <span class="asterisk">*</span></label>
	<span class="help">@lang('messages.Company contact address')</span>
		<div class="controls">
			<textarea class="form-control" style="width:100%;" required rows="10" cols="50" name="contact_address">{{$settings->contact_address}}</textarea>
		</div>
	</div>

	<div id="footer_text" class="form-group">
	<label class="form-label">@lang('messages.Footer Text') <span class="asterisk">*</span></label>
	<span class="help">@lang('messages.Footer Text')</span>
		<div class="controls">
			<?php $i = 0; foreach($languages as $langid => $language):?>
					<div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
					<div class="row">
						<div class="col-sm-10" style="padding-right: 0;">
						   <textarea class="form-control" rows="3" cols="150"  maxlength="250" name="footer_text[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Description').trans('messages.'.'('.$language->name.')');?>" class="form-control">{{$infomodel->getLabel('footer_text',$language->id,$settings->id)}}</textarea>
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
	
	<div id="copyrights" class="form-group">
		<label class="form-label">@lang('messages.Copyrights') <span class="asterisk">*</span></label>
		<span class="help">@lang('messages.This name will visible as a footer copyrights in admin & frontend')</span>
			<div class="controls">
				<?php /*<input id="assets_path_url" class="form-control" maxlength="300" required type="text" style="width:100%;" value="{{ $settings->copyrights }}" name="copyrights">*/?>
                                <?php $i = 0; foreach($languages as $langid => $language):?>
									<div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
										<input type="text" name="copyrights[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>" placeholder="<?php echo trans('messages.Copyrights').' '.trans('messages.'.'('.$language->name.')');?>" class="form-control" value="{{$infomodel->getLabel('copyrights',$language->id,$settings->id)}}" maxlength="250" />
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
	<?php  $path= 'assets/front/';  $directories = array_map('basename', File::directories($path));?>
	<div id="default_template" class="form-group">
	<label class="form-label">@lang('messages.Choose your Template') <span class="asterisk">*</span></label>
		<select id="localeselect" class="select2-offscreen" required style="width:100%;" name="theme" tabindex="-1" title="">
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
	<script type="text/javascript">
		$(document).ready(function(){  $("#localeselect").select2(); });
	</script>
@endsection

