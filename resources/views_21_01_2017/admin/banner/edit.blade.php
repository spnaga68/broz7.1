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
			<li>@lang('messages.Banners')</li>
		</ul>
		<h4>@lang('messages.Edit Banner') - <?php echo ucfirst($data->banner_title); ?></h4>
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

       {!!Form::open(array('url' => ['admin/banner/update', $data->banner_setting_id], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'banner_form','files' => true));!!} 
	<div class="tab-content mb30">
	<div class="tab-pane active" id="home3">
		
			
				<div class="form-group">
					<label class="col-sm-2 control-label">@lang('messages.Main Title') <span class="asterisk">*</span></label>
					<div class="col-sm-10">
					  <input type="text" name="banner_title"  maxlength="30" required placeholder="@lang('messages.Main Title')"  class="form-control" value="{!! $data->banner_title !!}" />
					</div>
				</div>
            
				<div class="form-group">
					<label class="col-sm-2 control-label">@lang('messages.Subtitle') <span class="asterisk">*</span></label>
					<div class="col-sm-10">
					  <input type="text" name="banner_subtitle"  maxlength="30" required placeholder="@lang('messages.Subtitle')"  class="form-control" value="{!! $data->banner_subtitle !!}" />
					</div>
				</div>
            
				<div class="form-group">
					<label class="col-sm-2 control-label">@lang('messages.Image') </label>
					<div class="col-sm-10">
					  <input type="file" name="banner_image"  maxlength="255"  placeholder="@lang('messages.Image')"  class="" value="" />
					</div>	
				</div>

				<div class="form-group">
						<label class="col-sm-2 control-label">@lang('messages.Banner type')<span class="asterisk">*</span></label>
						<div class="col-sm-10">
						  <select id="banner_type" name="banner_type" class="form-control" >
							<option value="">@lang('messages.Select Banner Type')</option>
							@if (count(getBannerTypes()) > 0)
								@foreach (getBannerTypes() as $key => $type)
									<option value="{{ $key }}" <?php echo ($data->banner_type==$key)?'selected="selected"':''; ?> ><?php echo trans('messages.'.$type); ?></option>
								@endforeach
							@endif
						  </select>
						</div>
					</div>
			
				<?php if($data->banner_image){ ?>
					<div class="form-group">
					<label class="col-sm-2 control-label"></label>
					<div class="col-sm-10">
						<div class="thmb">
							<div class="thmb-prev" style="height: auto; overflow: hidden;">
								<img src="<?php echo url('/assets/admin/base/images/banner/thumb/'.$data->banner_image.'?'.time()); ?>" class="img-responsive" alt="{!! $data->banner_title !!}" width="200px" height="200px">
							</div>
						</div>
					 </div>
					</div>
				<?php } ?>
				<div class="form-group">
					<label class="col-sm-2 control-label">@lang('messages.Link')<span class="asterisk">*</span></label>
					<div class="col-sm-10">
					  <input type="text" name="banner_link"  maxlength="100" required placeholder="@lang('messages.Link')"  class="form-control" value="{!! $data->banner_link !!}" />
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
		<div class="panel-footer">
		<button class="btn btn-primary mr5" title="Update">@lang('messages.Update')</button>
		<button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('admin/banners') }}'">@lang('messages.Cancel')</button>
		</div>
        </div>
      
 {!!Form::close();!!} 
</div></div></div>
@endsection

