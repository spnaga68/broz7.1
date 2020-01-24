@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script> 
<link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<!-- Nav tabs -->
<div class="pageheader">
	<div class="media">
		<div class="pageicon pull-left">
			<i class="fa fa-home"></i>
		</div>
		<div class="media-body">
			<ul class="breadcrumb">
				<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
				<li>@lang('messages.Currency')</li>
			</ul>
			<h4>@lang('messages.Add Currency')</h4>
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
	{!!Form::open(array('url' => 'createcurrency', 'method' => 'post','class'=>'tab-form attribute_form','id'=>'currency_form','files' => true));!!} 
		<div class="tab-content mb30">
			<div class="tab-pane active" id="home3">
				<div class="form-group">
					<label class="col-sm-2 control-label">@lang('messages.Currency Name') <span class="asterisk">*</span></label>
					<div class="col-sm-10">
						<input type="text" autofocus name="currency_name" id="currency_name" maxlength="15" placeholder="@lang('messages.Currency Name')" class="form-control" value="{!! old('currency_name') !!}" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">@lang('messages.Currency Code') <span class="asterisk">*</span></label>
					<div class="col-sm-10">
						<input type="text" name="currency_code" id="currency_code" maxlength="3" placeholder="@lang('messages.Currency Code')" class="form-control" value="{!! old('currency_code') !!}" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">@lang('messages.Numeric ISO Code') <span class="asterisk">*</span></label>
					<div class="col-sm-10">
						<input type="text" name="numeric_iso_code" id="numeric_iso_code" maxlength="3" placeholder="@lang('messages.Numeric ISO Code')" class="form-control" value="{!! old('numeric_iso_code') !!}" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">@lang('messages.Currency Symbol') <span class="asterisk">*</span></label>
					<div class="col-sm-10">
						<input type="text" name="currency_symbol" id="currency_symbol" maxlength="3" placeholder="@lang('messages.Currency Symbol')" class="form-control" value="{!! old('currency_symbol') !!}" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">@lang('messages.Exchange Rate')</label>
					<div class="col-sm-10">
						<input type="text" name="exchange_rate" id="exchange_rate" maxlength="20" placeholder="@lang('messages.Exchange Rate')" class="form-control" value="{!! old('exchange_rate') !!}" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">@lang('messages.Decimal Places')</label>
					<div class="col-sm-10">
						<input type="text" name="decimal_values" id="decimal_values" maxlength="20" placeholder="@lang('messages.Decimal Places')" class="form-control" value="{!! old('decimal_values') !!}" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">@lang('messages.Status')</label>
					<div class="col-sm-10">
						<?php $checked = "";if(old('status')){$checked = "checked=checked";} ?>
						<input type="checkbox" class="toggle" name="status" data-size="small" <?php echo $checked;?> data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
					</div>
				</div>
			</div>
			<div class="panel-footer">
				<button class="btn btn-primary mr5" title="Save">@lang('messages.Save')</button>
				<button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('admin/localisation/currency') }}'">@lang('messages.Cancel')</button>
			</div>
		</div>
	{!!Form::close();!!} 
</div>
@endsection
