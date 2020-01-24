@extends('layouts.vendors')
@section('content')
<!-- Nav tabs -->
<div class="pageheader">
	<div class="media">
		<div class="pageicon pull-left">
			<i class="fa fa-home"></i>
		</div>
		<div class="media-body">
			<ul class="breadcrumb">
				<li><a href="#"><i class="glyphicon glyphicon-home"></i>@lang('messages.Vendors')</a></li>
				<li>@lang('messages.Request Amount')</li>
			</ul>
			<h4>@lang('messages.New Request Amount')</h4>
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
	{!!Form::open(array('url' => 'createamount', 'method' => 'post','class'=>'tab-form attribute_form','id'=>'createamount'));!!}
	<?php $settings = session::get('general');?>
	<div class="tab-content mb30">
		<div class="tab-pane active">
			<div class="form-group">
				<label class="col-sm-2 control-label">@lang('messages.request_amount')</label>
				<div class="col-sm-10">
					<input type="text"  name="request_amount" value="{!! old('request_amount') !!}"  maxlength="15" placeholder="@lang('messages.request_amount')"  class="form-control"  />
					<span class="help-block">@lang('messages.fund_request_help_text') <?php echo $settings->min_fund_request.' to '.$settings->max_fund_request;?></span>
				</div>
			</div>
       </div>
		<div class="panel-footer">
			<button class="btn btn-primary mr5" title="Save">@lang('messages.Save')</button>
			<button type="reset" class="btn btn-default" onclick="window.location='{{ url('vendors/request_amount/index') }}'"  title="Cancel">@lang('messages.Cancel')</button>
		</div>
	</div>
	{!!Form::close();!!}
</div>
@endsection
