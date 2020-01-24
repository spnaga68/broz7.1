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
			<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
			<li>@lang('messages.Settings')</li>
		</ul>
		<h4>@lang('messages.Email SMTP Settings')</h4>
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
@if (Session::has('message'))
	<div class="admin_sucess_common">
	<div class="admin_sucess">
<div class="alert alert-info success">
<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>
    {{ Session::get('message') }}</div></div></div>
@endif
       {!!Form::open(array('url' => ['admin/settings/updateemail', $settings->id], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'settings_edit_form','files' => true));!!}

<div class="col-md-12">
<div class="row panel panel-default">
<div class="grid simple">
	<div id="general" class="panel-heading">
	<h4 class="panel-title">@lang('messages.Email')</h4>
	<p>@lang('messages.SMTP Settings')</p>
		<div class="tools">
			<a class="collapse" href="javascript:;"></a>
		</div>
	</div>
	<ul class="nav nav-tabs"></ul>
	<div class="panel-body">
	<div id="contact_mail" class="form-group">
		<label class="form-label">@lang('messages.Contact Email') <span class="asterisk">*</span></label>
		<span class="help">@lang('messages.This email will visible as a from in sent email')</span>
		<div class="controls">
			<input class="form-control" maxlength="255" type="email" value="{{ $settings->contact_mail }}" required name="contact_mail">
		</div>
	</div>

	<div id="support_mail" class="form-group">
		<label class="form-label">@lang('messages.Support Email') <span class="asterisk">*</span></label>
		<span class="help">	@lang('messages.This email will visible as a from site enquiry')</span>
		<div class="controls">
		<input class="form-control" type="email" maxlength="300" value="{{ $settings->support_mail }}" required name="support_mail">
		</div>
	</div>

	<div id="mobile_number" class="form-group">
		<label class="form-label">@lang('messages.Mobile Number') <span class="asterisk">*</span></label>
		<span class="help">	@lang('messages.This mobile number will visible as a from site enquiry')</span>
		<div class="controls">
		<input class="form-control" type="tel"  maxlength="300" value="{{ $settings->mobile_number }}" required name="mobile_number">
		</div>
	</div>

	<div id="skype" class="form-group">
		<label class="form-label">@lang('messages.Skype') <span class="asterisk">*</span></label>
		<span class="help">	@lang('messages.This skype will visible as a from site enquiry')</span>
		<div class="controls">
		<input class="form-control" type="text"  maxlength="300" value="{{ $settings->skype }}" required name="skype">
		</div>
	</div>
	
	<div id="SMTP_ENABLE" class="form-group">
		<label class="form-label">@lang('messages.Enable')</label>
		<span class="help">@lang('messages.Smtp Enable')</span>
			<select id="select_smtpenable" class="select2-offscreen" style="width:100%;" name="smtp_enable" tabindex="-1" title="">
			<option value="">@lang('messages.Select a value')</option>
			<option value="1" <?php if($settings->smtp_enable==1){ echo "selected=selected"; } ?> >@lang('messages.Yes')</option>
			<option  value="0" <?php if($settings->smtp_enable==0){ echo "selected=selected"; } ?> >@lang('messages.No')</option>
			</select>
	</div>

	<div id="smtp_host_name" class="form-group">
		<label class="form-label">@lang('messages.Mail Driver') <span class="asterisk">*</span></label>
		<span class="help">@lang('messages.mail driver')</span>
		<div class="controls">
		<input class="form-control" type="text" maxlength="5" value="{{ $settings->mail_driver }}" required name="mail_driver">
		</div>
	</div>

	<div id="smtp_host_name" class="form-group">
		<label class="form-label">@lang('messages.Host Name') <span class="asterisk">*</span></label>
		<span class="help">@lang('messages.smtp hostname')</span>
		<div class="controls">
		<input class="form-control" type="text" maxlength="300" value="{{ $settings->smtp_host_name }}" required name="smtp_host_name">
		</div>
	</div>

	<div id="smtp_username" class="form-group">
		<label class="form-label">@lang('messages.Username') <span class="asterisk">*</span></label>
		<span class="help">@lang('messages.smtp username')</span>
		<div class="controls">
		<input class="form-control" type="text" maxlength="300" value="{{ $settings->smtp_username }}" required name="smtp_username">
		</div>
	</div>

	<div id="smtp_password" class="form-group">
		<label class="form-label">@lang('messages.Password') <span class="asterisk">*</span></label>
		<span class="help">@lang('messages.smtp password')</span>
		<div class="controls">
		<input class="form-control" type="password" maxlength="300" value="{{ $settings->smtp_password }}" required name="smtp_password">
		</div>
	</div>

	<div id="smtp_port" class="form-group">
		<label class="form-label">@lang('messages.Port') <span class="asterisk">*</span></label>
		<span class="help">@lang('messages.smtp port')</span>
		<div class="controls">
		<input class="form-control" type="text" maxlength="300" value="{{ $settings->smtp_port }}" required name="smtp_port">
		</div>
	</div>

	<div id="SSL_Security" class="form-group">
		<label class="form-label">@lang('messages.SSL Security') <span class="asterisk">*</span></label>
		<span class="help">	@lang('messages.Use an encrypt')</span>
			<select id="smtp_auth" class="select2-offscreen" style="width:100%;" name="smtp_encryption" tabindex="-1" title="" required >
				<option value="">@lang('messages.Select a value')</option>
				<option value="tls" <?php if($settings->smtp_encryption=="tls"){ echo "selected=selected"; } ?> >@lang('messages.TLS')</option>
				<option value="ssl" <?php if($settings->smtp_encryption=="ssl"){ echo "selected=selected"; } ?>>@lang('messages.SSL')</option>
			</select>
	</div>


</div>
<div class="panel-footer">
		<button class="btn btn-primary mr5" title="Update">@lang('messages.Update')</button>
		<button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('admin/dashboard') }}'">@lang('messages.Cancel')</button>
	</div>
</div></div></div>

 {!!Form::close();!!} 
</div></div></div>
	<script>
$(document).ready(function(){  $("#smtp_auth").select2();  $("#select_smtpenable").select2();});
	</script>
@endsection

