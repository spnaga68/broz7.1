<style>
.has-error.has-danger{
	border-color: #a94442 !important;
	box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset !important;
}

</style>
<link href="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/css/intlTelInput.css');?>" media="all" rel="stylesheet" type="text/css" />
<link href="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/css/select2.css');?>" media="all" rel="stylesheet" type="text/css" />
<link href="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/css/bootstrap-switch.min.css');?>" media="all" rel="stylesheet" type="text/css" /> 
<script type="text/javascript" src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/js/validator.min.js');?>"></script>
<script type="text/javascript" src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/js/intlTelInput.min.js');?>"></script>
<?php $general = Session::get("general"); $social = Session::get("social"); $email = Session::get("configemail"); $languages = Session::get("languages"); ?>
    <!-- Modal for signup -->
	
    <div class="modal fade model_for_signup" id="store_register" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                     <?php $locale = Session::get('locale', Config::get('app.locale')); ?>
	               <?php if ($locale == 'en') {?>
							<span class="logo_popup"><img alt="" src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/'.Session::get('general')->theme.'.png'); ?>"></span>
                    <?php  } else {?>
							<span class="logo_popup"><img alt="" src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/'.Session::get('general')->theme.'.png'); ?>"></span>
					 <?php } ?>	
                    
                </div>
                <div class="modal-body">
                    <div class="sign_up_inner register_your_store">
                        <h2>@lang('messages.Register your store')<br>
							<span class="bottom_border"></span>
						</h2>
						<div id="success_message_signup" ></div>
						{!!Form::open(array('url' => 'storeregister', 'method' => 'post', 'class' => 'tab-form attribute_form', 'id' => 'store_registernew'));!!} 
							<div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <input type="text" name="first_name" maxlength="50" class="form-control"  placeholder="@lang('messages.First name')">
                                </div>
                            </div>
							 <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <input type="text" name="last_name"  maxlength="50" class="form-control"  placeholder="@lang('messages.Last name')">
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <input type="email" name="email"  maxlength="50" class="form-control"  id="exampleInputEmail" placeholder="@lang('messages.Email')">
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <input type="password" name = "password" maxlength="15" class="form-control"  id="exampleInputPassword1" placeholder="@lang('messages.Password')">
                                </div>
                            </div>
							<div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <input type="password" name="password_confirmation"  id="confirm_password" value="{!! old('password_confirmation') !!}"  maxlength="255" placeholder="@lang('messages.Confirm password')"  class="form-control"  />
                                </div>
                            </div>
                            
							  <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                      <div class="form-group">
                                <input type="tel" maxlength="15" name = "phone_number" class="form-control" id="phone_number" required placeholder="@lang('messages.Phone') (@lang('messages.Required'))">
                            </div>
                                </div>
                            </div>
                           
                         <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <input type="text" name="vendor_name" maxlength="50" class="form-control"  placeholder="@lang('messages.Vendor name')">
                                </div>
                            </div>
                           <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <textarea type="text" class="content" rows="7" cols="30" name="vendor_description"   placeholder="@lang('messages.Vendor description')"></textarea>
									 
                                </div>
                            </div>
							<div class="col-md-12 col-sm-12 col-xs-12 check_sect">
                                <label class="label_check" for="checkbox-02">
                                     <input name="terms_condition"  id="checkbox-02" value="1" type="checkbox"/>@lang('messages.I agree oddappz') <a href="<?php echo  URL::to('cms/terms-amp-conditions') ?>" target="_blank" title=">@lang('messages.Terms &amp; conditions')"> @lang('messages.Terms & conditions')</a>, <a href="<?php echo  URL::to('/cms/privacy-policy') ?>" target="_blank" title=">@lang('messages.Terms &amp; conditions')"> @lang('messages.Privacy & policy')</a> @lang('messages.and')<a href="<?php echo  URL::to('/cms/contracts') ?>" target="_blank" title=">@lang('messages.Terms &amp; conditions')"> @lang('messages.Contracts for merchants')</a><label>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <div class="sign_bot_sub">
										<button type="button" class="btn btn-primary cancel_button" data-url="" title="@lang('messages.Cancel')">@lang('messages.Cancel')</button>
                                        <button type="submit" class="btn btn-default" id="store_registersubmit" title="@lang('messages.Save')">@lang('messages.Save')</button>
										
										<div class="ajaxloading" style="display:none;">
											<div class="loader-coms">
												<div class="loder_gif">
													<img src="<?php echo url('assets/front/'.Session::get("general")->theme.'/images/ajax-loader.gif');?>" />	
												</div>
											</div>
										</div>
										
                                    </div>
                                </div>
                            </div>
							
						{!!Form::close();!!}
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Modal for signup end -->
	<script type="text/javascript">
$('select').select2();
$("#phone_number").intlTelInput({
	
	nationalMode: false,
	separateDialCode: false,
	//onlyCountries: ['sa', 'in'],
	utilsScript: "<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/js/utils.js');?>" // just for formatting/placeholders etc
});
/*$('#store_registernew').on('submit',function (e)
{	alert("asdf");
	e.preventDefault();
	$this.validator();
}
);*/

$('#store_registernew').validator().on('submit', function (e)
{
	if (e.isDefaultPrevented())
	{
		//alert("asf");
	}
	else 
	{
		$('#success_message_storeregister' ).show().html("");
			var isValid = $('#phone_number').intlTelInput("isValidNumber");
	if(!isValid)
	{
		toastr.warning('<?php echo trans('messages.Please enter valid phone number');?>');
		return false;
	}
		$(".ajaxloading").show();
		data = $("#store_registernew").serializeArray();
		data.push({ name: "user_type", value: "1" });
		var c_url = '/storeregister-user';
		token = $('input[name=_token]').val();
		$.ajax({
			url: c_url,
			headers: {'X-CSRF-TOKEN': token},
			data: data,
			type: 'POST',
			datatype: 'JSON',
			success: function (resp)
			{
				$(".ajaxloading").hide();
				data = resp;
				console.log(data.httpCode);
				if(data.httpCode == 200)
				{
					toastr.success(data.Message);
					location.reload(true);
					return false;
				}
				else
				{
				toastr.warning(data.Message);
					return false;	
				}
			}, 
			error:function(resp)
			{
			}
		});
		return false;
	}
	e.preventDefault();
});


</script>
