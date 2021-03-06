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
			<li>@lang('messages.Payemnt Gateways')</li>
		</ul>
		<h4>@lang('messages.Edit Payemnt Gateway') - {{$infomodel->getLabel('name',getAdminCurrentLang(),$data->id)}}</h4>
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
    {!!Form::open(array('url' => ['update_payment_gateway', $data->id], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'country_form','files' => true));!!} 
	<div class="tab-content mb30">
	<div class="tab-pane active" id="home3">
				
		<div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Gateway Name') <span class="asterisk">*</span></label>
                <div class="col-sm-10">
                    <?php $i = 0; foreach($languages as $langid => $language):?>
                    <div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
                          <input type="text" maxlength="48" name="gateway_name[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Gateway Name').trans('messages.'.'('.$language->name.')');?>" class="form-control" value="{{$infomodel->getLabel('name',$language->id,$data->id)}}"  />
                     
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
			<label class="col-sm-2 control-label">@lang('messages.Merchant Account Id') <span class="asterisk">*</span></label>
			<div class="col-sm-10">
			  <input type="text" maxlength="122" name="merchant_account_id" value="{!! $data->account_id !!}"   maxlength="255" placeholder="@lang('messages.Merchant Account Id')"  class="form-control"  />
			   <span class="help-block"></span>
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Merchant Key') <span class="asterisk">*</span></label>
			<div class="col-sm-10">
			  <input type="text" name="merchant_key" maxlength="256" value="{!! $data->merchant_key !!}"   maxlength="255" placeholder="@lang('messages.Merchant Key')"  class="form-control"  />
			  <span class="help-block"></span>
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Merchant Secret Key') <span class="asterisk">*</span></label>
			<div class="col-sm-10">
			  <input type="text" name="merchant_secret_key" maxlength="256" value="{!! $data->merchant_secret_key !!}"  maxlength="255" placeholder="@lang('messages.Merchant Secret Key')"  class="form-control"  />
			  <span class="help-block"></span>
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Merchant Password') <span class="asterisk">*</span></label>
			<div class="col-sm-10">
			  <input type="text" name="merchant_password" maxlength="256" value="{!! $data->merchant_password !!}"  maxlength="255" placeholder="@lang('messages.Merchant Password')"  class="form-control"  />
			  <span class="help-block"></span>
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Payment Commision') <span class="asterisk">*</span></label>
			<div class="col-sm-10">
			  <input type="text" maxlength="3" name="payment_commision" value="{!! $data->commision !!}"  maxlength="3" placeholder="@lang('messages.Payment Commision')"  class="form-control"  />
			  <span class="help-block">@lang('messages.Commision') %</span>
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Payment Mode')</label>
			<div class="col-sm-10">
				<input   type="radio"  value="1"  name="payment_mode" @if($data->payment_mode==1) checked @endif >
				<span>@lang('messages.Test')</span>
				<input   type="radio"  value="2"   name="payment_mode" @if($data->payment_mode==2) checked @endif >
				<span>@lang('messages.Live')</span>
			</div>
		</div>
		
		<?php if(count($payment_list) == 1 && $data->active_status == 1) {?>
			<div class="form-group">
				<label class="col-sm-2 control-label"></label>
				<div class="col-sm-10"><label><b>Status was disabled, because atleast one payment gateway is active.</b></label></div>
			</div>
		<?php } else {?>
			<div class="form-group">
				<label  class="col-sm-2 control-label">@lang('messages.Status')</label>
				<div class="col-sm-10">
					<?php $checked = "";if($data->active_status){ $checked = "checked=checked"; }?>
					<input type="checkbox" class="toggle" name="status" data-size="small" <?php echo $checked;?> data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
				</div>
			</div>
		<?php } ?>
       </div>
		<div class="panel-footer">
<?php if(count($payment_list) == 1 && $data->active_status == 1) { ?>
		<button class="btn btn-primary mr5" title="Save" disabled>@lang('messages.Update')</button>
<?php } else { ?>
<button class="btn btn-primary mr5" title="Save">@lang('messages.Update')</button>
<?php } ?>
		<button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('admin/payment/settings') }}'">@lang('messages.Cancel')</button>
		</div>
        </div>
      
 {!!Form::close();!!} 
</div></div></div>
<script>
$(window).load(function(){	
	$('form').preventDoubleSubmission();	
});
</script>
@endsection
