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
			<li>@lang('messages.Brands')</li>
		</ul>
		<h4>@lang('messages.Edit Brand') - <?php echo ucfirst($data->brand_title); ?></h4>
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

       {!!Form::open(array('url' => ['admin/brand/update', $data->id], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'brand_form','files' => true));!!} 
	<div class="tab-content mb30">
	<div class="tab-pane active" id="home3">
		
			
				<div class="form-group">
					<label class="col-sm-2 control-label">@lang('messages.Title') <span class="asterisk">*</span></label>
					<div class="col-sm-10">
					  <input type="text" name="brand_title"  maxlength="30" required placeholder="@lang('messages.Main Title')"  class="form-control" value="{!! $data->brand_title !!}" />
					</div>
				</div>
            
				
            
				<div class="form-group">
					<label class="col-sm-2 control-label">@lang('messages.Image') </label>
					<div class="col-sm-10">
					  <input type="file" name="brand_image"  maxlength="255"  placeholder="@lang('messages.Image')"  class="" value="" />
					</div>	
				</div>

				
				<?php if($data->brand_image){ ?>
					<div class="form-group">
					<label class="col-sm-2 control-label"></label>
					<div class="col-sm-10">
						<div class="thmb">
							<div class="thmb-prev" style="height: auto; overflow: hidden;">
								<img src="<?php echo url('/assets/admin/base/images/brand/thumb/'.$data->brand_image.'?'.time()); ?>" class="img-responsive" alt="{!! $data->banner_title !!}" width="200px" height="200px">
							</div>
						</div>
					 </div>
					</div>
				<?php } ?>
				<div class="form-group">
					<label class="col-sm-2 control-label">@lang('messages.Link')<span class="asterisk">*</span></label>
					<div class="col-sm-10">
					  <input type="text" name="brand_link"  maxlength="100" required placeholder="@lang('messages.Link')"  class="form-control" value="{!! $data->brand_link !!}" />
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
		<button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('admin/brands') }}'">@lang('messages.Cancel')</button>
		</div>
        </div>
      
 {!!Form::close();!!} 
</div></div></div>
@endsection

