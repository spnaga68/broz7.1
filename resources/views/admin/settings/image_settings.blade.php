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
		<h4>@lang('messages.Image settings')</h4>
	</div>
</div><!-- media -->

</div><!-- pageheader -->
<div class="contentpanel">
@if (count($errors) > 0)
<div class="alert alert-danger">
<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>
	<ul>
		@foreach ($errors->all() as $error)
			<li> <?php echo trans('messages.'.$error); ?> </li>
		@endforeach
	</ul>
</div>
@endif
@if (Session::has('message'))
	<div class="admin_sucess_common">
	<div class="admin_sucess">
<div class="alert alert-info success">
<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>
    {{ Session::get('message') }}</div></div></div>
@endif
<?php $settings_id=0; ?>
       {!!Form::open(array('url' => ['admin/settings/updateimagesettings', $settings_id], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'settings_edit_form','files' => true));!!}
<div class="col-md-12">
<div class="row panel panel-default">
<div class="grid simple">
	<div id="general" class="panel-heading">
	<h4 class="panel-title">@lang('messages.Image resize settings')</h4>
	<p>@lang('messages.Image resize settings')</p>
		<div class="tools">
			<a class="collapse" href="javascript:;"></a>
		</div>
	</div>
	<ul class="nav nav-tabs"></ul>
	<div class="panel-body">
			<div class="row col-sm-12">
				<div class="grid simple">
					<div id="copyrights" >
					<h4 class="panel-title">@lang('messages.Common')</h4>
					<ul class="nav nav-tabs"></ul>
					<p></p>
						<div class="tools">
						<a class="collapse" href="javascript:;"></a>
						</div>
					</div>
					<div class="panel-body">
							<div class="form-group col-sm-5">
								<label class="form-label">@lang('messages.Logo') <span class="asterisk">*</span></label>
								<span class="help">@lang('messages.Width')</span>
									<div class="controls">
									<input  class="form-control" value="{{ $common->list_width }}" maxlength="3" type="text" style="width:100%;"  name="logo_width">
									</div>
							</div>
							<div id="COPYRIGHTS" class="form-group col-sm-5">
								<label class="form-label">&nbsp;</label>
								<span class="help">@lang('messages.Height') <span class="asterisk">*</span></span>
									<div class="controls">
									<input  class="form-control" value="{{ $common->list_height }}" maxlength="3" type="text" style="width:100%;"  name="logo_height">
									</div>
							</div>
							<div class="form-group col-sm-5">
								<label class="form-label">@lang('messages.Favicon') <span class="asterisk">*</span></label>
								<span class="help">@lang('messages.Width')</span>
									<div class="controls">
									<input  class="form-control" value="{{ $common->detail_width }}" maxlength="3" type="text" style="width:100%;"  name="favicon_width">
									</div>
							</div>
							<div id="COPYRIGHTS" class="form-group col-sm-5">
								<label class="form-label">&nbsp;</label>
								<span class="help">@lang('messages.Height') <span class="asterisk">*</span></span>
									<div class="controls">
									<input  class="form-control" type="text" maxlength="3" value="{{ $common->detail_height }}" style="width:100%;"  name="favicon_height">
									</div>
							</div>
							<div class="form-group col-sm-5">
								<label class="form-label">@lang('messages.Category') <span class="asterisk">*</span></label>
								<span class="help">@lang('messages.Width')</span>
									<div class="controls">
									<input  class="form-control" type="text" maxlength="3" value="{{ $common->thumb_width }}" style="width:100%;"  name="category_width">
									</div>
							</div>
							<div id="COPYRIGHTS" class="form-group col-sm-5">
								<label class="form-label">&nbsp;</label>
								<span class="help">@lang('messages.Height') <span class="asterisk">*</span></span>
									<div class="controls">
									<input  class="form-control" type="text" maxlength="3" style="width:100%;"  value="{{ $common->thumb_height }}" name="category_height">
									</div>
							</div>
					</div>
				</div>
				<input type="hidden" name="common" value="1">
			</div>
			
			<div class="row col-sm-12">
				<div class="grid simple">
					<div id="copyrights">
					<h4 class="panel-title">@lang('messages.Store')</h4>
					<ul class="nav nav-tabs"></ul>
					<p></p>
						<div class="tools">
						<a class="collapse" href="javascript:;"></a>
						</div>
					</div>
					<div class="panel-body">
							<div class="form-group col-sm-5">
								<label class="form-label">@lang('messages.List') <span class="asterisk">*</span></label>
								<span class="help">@lang('messages.Width')</span>
									<div class="controls">
									<input  class="form-control" type="text"  maxlength="3" value="{{ $store->list_width }}"  style="width:100%;"  name="store_list_width">
									</div>
							</div>
							<div  class="form-group col-sm-5">
								<label class="form-label">&nbsp;</label>
								<span class="help">@lang('messages.Height') <span class="asterisk">*</span></span>
									<div class="controls">
									<input  class="form-control" type="text" maxlength="3" value="{{ $store->list_height }}" style="width:100%;"  name="store_list_height">
									</div>
							</div>
							<div class="form-group col-sm-5">
								<label class="form-label">@lang('messages.Detail') <span class="asterisk">*</span></label>
								<span class="help">@lang('messages.Width')</span>
									<div class="controls">
									<input  class="form-control" type="text" maxlength="3" value="{{ $store->detail_width }}" style="width:100%;"  name="store_detail_width">
									</div>
							</div>
							<div  class="form-group col-sm-5">
								<label class="form-label">&nbsp;</label>
								<span class="help">@lang('messages.Height') <span class="asterisk">*</span></span>
									<div class="controls">
									<input  class="form-control" type="text" maxlength="3" value="{{ $store->detail_height }}" style="width:100%;"  name="store_detail_height">
									</div>
							</div>
							<div class="form-group col-sm-5">
								<label class="form-label">@lang('messages.Thumb') <span class="asterisk">*</span></label>
								<span class="help">@lang('messages.Width')</span>
									<div class="controls">
									<input  class="form-control" type="text" maxlength="3"  value="{{ $store->thumb_width }}" style="width:100%;"  name="store_thumb_width">
									</div>
							</div>
							<div  class="form-group col-sm-5">
								<label class="form-label">&nbsp;</label>
								<span class="help">@lang('messages.Height') <span class="asterisk">*</span></span>
									<div class="controls">
									<input  class="form-control" type="text" maxlength="3" value="{{ $store->thumb_height }}" style="width:100%;"  name="store_thumb_height">
									</div>
							</div>
					</div>
				</div>
				<input type="hidden" name="store" value="2">
			</div>
			
			<div class="row col-sm-12">
				<div class="grid simple">
					<div id="copyrights">
					<h4 class="panel-title">@lang('messages.Product')</h4>
					<ul class="nav nav-tabs"></ul>
					<p></p>
						<div class="tools">
						<a class="collapse" href="javascript:;"></a>
						</div>
					</div>
					<div class="panel-body">
							<div class="form-group col-sm-5">
								<label class="form-label">@lang('messages.List') <span class="asterisk">*</span></label>
								<span class="help">@lang('messages.Width')</span>
									<div class="controls">
									<input  class="form-control" type="text" style="width:100%;" value="{{ $product->list_width }}"  name="product_list_width">
									</div>
							</div>
							<div id="COPYRIGHTS" class="form-group col-sm-5">
								<label class="form-label">&nbsp;</label>
								<span class="help">@lang('messages.Height') <span class="asterisk">*</span></span>
									<div class="controls">
									<input  class="form-control" type="text" style="width:100%;" value="{{ $product->list_height }}"  name="product_list_height">
									</div>
							</div>
							<div class="form-group col-sm-5">
								<label class="form-label">@lang('messages.Detail') <span class="asterisk">*</span></label>
								<span class="help">@lang('messages.Width')</span>
									<div class="controls">
									<input  class="form-control" type="text" style="width:100%;"  value="{{ $product->detail_width }}" name="product_detail_width">
									</div>
							</div>
							<div id="COPYRIGHTS" class="form-group col-sm-5">
								<label class="form-label">&nbsp;</label>
								<span class="help">@lang('messages.Height') <span class="asterisk">*</span></span>
									<div class="controls">
									<input  class="form-control" type="text" style="width:100%;"   value="{{ $product->detail_height }}" name="product_detail_height">
									</div>
							</div>
							<div class="form-group col-sm-5">
								<label class="form-label">@lang('messages.Thumb') <span class="asterisk">*</span></label>
								<span class="help">@lang('messages.Width')</span>
									<div class="controls">
									<input  class="form-control" type="text" style="width:100%;" value="{{ $product->thumb_width }}"  name="product_thumb_width">
									</div>
							</div>
							<div id="COPYRIGHTS" class="form-group col-sm-5">
								<label class="form-label">&nbsp;</label>
								<span class="help">@lang('messages.Height') <span class="asterisk">*</span></span>
									<div class="controls">
									<input  class="form-control" type="text" style="width:100%;" value="{{ $product->thumb_height }}"  name="product_thumb_height">
									</div>
							</div>
					</div>
				</div>
				<input type="hidden" name="product" value="3">
			</div>
			
			<div class="row col-sm-12">
				<div class="grid simple">
					<div id="copyrights" >
						
					<h4 class="panel-title">@lang('messages.Banner')</h4>
					<ul class="nav nav-tabs"></ul>
					<p></p>
						<div class="tools">
						<a class="collapse" href="javascript:;"></a>
						</div>
					</div>
					<div class="panel-body">
							<div class="form-group col-sm-5">
								<label class="form-label">&nbsp;</label>
								<span class="help">@lang('messages.Width') <span class="asterisk">*</span></span>
									<div class="controls">
									<input  class="form-control" type="text" style="width:100%;" value="{{ $banner->list_width }}"  name="banner_list_width">
									</div>
							</div>
							<div id="COPYRIGHTS" class="form-group col-sm-5">
								<label class="form-label">&nbsp;</label>
								<span class="help">@lang('messages.Height') <span class="asterisk">*</span></span>
									<div class="controls">
									<input  class="form-control" type="text" style="width:100%;" value="{{ $banner->list_height }}"  name="banner_list_height">
									</div>
							</div>
					</div>
				</div>
				<input type="hidden" name="banner" value="4">
			</div>
			<div class="row col-sm-12">
				<div class="grid simple">
					<div id="copyrights">
					<h4 class="panel-title">@lang('messages.Vendors')</h4>
					<ul class="nav nav-tabs"></ul>
					<p></p>
						<div class="tools">
						<a class="collapse" href="javascript:;"></a>
						</div>
					</div>
					<div class="panel-body">
							<div class="form-group col-sm-5">
								<label class="form-label">@lang('messages.List(Mobile Banner)') <span class="asterisk">*</span></label>
								<span class="help">@lang('messages.Width')</span>
									<div class="controls">
									<input  class="form-control" type="text" style="width:100%;" value="{{ $vendor->list_width }}"  name="vendor_list_width">
									</div>
							</div>
							<div id="COPYRIGHTS" class="form-group col-sm-5">
								<label class="form-label">&nbsp;</label>
								<span class="help">@lang('messages.Height') <span class="asterisk">*</span></span>
									<div class="controls">
									<input  class="form-control" type="text" style="width:100%;" value="{{ $vendor->list_height }}"  name="vendor_list_height">
									</div>
							</div>
							<div class="form-group col-sm-5">
								<label class="form-label">@lang('messages.Detail') <span class="asterisk">*</span></label>
								<span class="help">@lang('messages.Width')</span>
									<div class="controls">
									<input  class="form-control" type="text" style="width:100%;"  value="{{ $vendor->detail_width }}" name="vendor_detail_width">
									</div>
							</div>
							<div id="COPYRIGHTS" class="form-group col-sm-5">
								<label class="form-label">&nbsp;</label>
								<span class="help">@lang('messages.Height') <span class="asterisk">*</span></span>
									<div class="controls">
									<input  class="form-control" type="text" style="width:100%;"   value="{{ $vendor->detail_height }}" name="vendor_detail_height">
									</div>
							</div>
							<div class="form-group col-sm-5">
								<label class="form-label">@lang('messages.Thumb') <span class="asterisk">*</span></label>
								<span class="help">@lang('messages.Width')</span>
									<div class="controls">
									<input  class="form-control" type="text" style="width:100%;" value="{{ $vendor->thumb_width }}"  name="vendor_thumb_width">
									</div>
							</div>
							<div id="COPYRIGHTS" class="form-group col-sm-5">
								<label class="form-label">&nbsp;</label>
								<span class="help">@lang('messages.Height') <span class="asterisk">*</span></span>
									<div class="controls">
									<input  class="form-control" type="text" style="width:100%;" value="{{ $vendor->thumb_height }}"  name="vendor_thumb_height">
									</div>
							</div>
					</div>
				</div>
				<input type="hidden" name="vendor" value="5">
			</div>
		   
</div>


<div class="panel-footer">
		<button class="btn btn-primary mr5" title="@lang('Update')">@lang('messages.Update')</button>
		<button type="reset" title="@lang('Cancel')" class="btn btn-default" onclick="window.location='{{ url('admin/dashboard') }}'">@lang('messages.Cancel')</button>
	</div>
</div></div></div>

 {!!Form::close();!!} 
</div></div></div>
@endsection

