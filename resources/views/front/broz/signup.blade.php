
<link href="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/css/intlTelInput.css');?>" media="all" rel="stylesheet" type="text/css" />
<link href="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/css/select2.css');?>" media="all" rel="stylesheet" type="text/css" />
<link href="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/css/bootstrap-switch.min.css');?>" media="all" rel="stylesheet" type="text/css" /> 
<script type="text/javascript" src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/js/validator.min.js');?>"></script>
<script type="text/javascript" src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/js/intlTelInput.min.js');?>"></script>
    <!-- Modal for signup -->
    <div class="modal model_for_signup" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <?php $locale = Session::get('locale', Config::get('app.locale')); ?>
	               <?php if ($locale == 'en') {?>
                    <span class="logo_popup"><img alt="{{$general->site_name}}" src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/'.Session::get('general')->theme.'.png?'.time()); ?>"></span>
                     <?php  } else {?>
					<span class="logo_popup"><img alt="{{$general->site_name}}" src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/'.Session::get('general')->theme.'.png?'.time()); ?>"></span>
					 <?php } ?>	
                </div>
                <div class="modal-body">
                    <div class="sign_up_inner">
                        <h2>@lang('messages.sign up')<br>
						  <span class="bottom_border"></span>
						</h2>
						<div id="success_message_signup"> </div>
						{!!Form::open(array('url' => 'signup', 'method' => 'post', 'class' => 'tab-form attribute_form', 'id' => 'sign_up' ,'onsubmit'=>'return signup()'));!!}
							
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
								  <input type="text" maxlength="56" name="first_name" class="form-control"  placeholder="@lang('messages.First Name') (@lang('messages.Required'))">

                                </div>
                            </div>
							
							<div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
								  <input type="text" maxlength="56" name="last_name" class="form-control"  placeholder="@lang('messages.Last Name')">

                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                           <input type="email" name="email" maxlength="250" class="form-control"  id="exampleInputEmail" placeholder="@lang('messages.Email') (@lang('messages.Required'))">

                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
								   <input type="password" name="password" maxlength="15"  id="password"   maxlength="255" placeholder="@lang('messages.Password') (@lang('messages.Required'))" class="form-control"  />
                                </div>
                            </div>
							 <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <div class="gender_section">
                                            <label class="left_label">@lang('messages.Gender')</label>
                                            <label class="label_radio" for="radio-02">
                                            <input   name="gender" id="radio-02" value="F" type="radio" />@lang('messages.Female')</label>
                                            <label class="label_radio" for="radio-03">
                                            <input  name="gender" id="radio-03" value="M" type="radio" />@lang('messages.Male')</label>
                                    </div>
                                </div>
                            </div>
                             <div class="col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <input type="tel" maxlength="15" name = "phone" class="form-control" id="phone_mumber"  placeholder="@lang('messages.Phone') (@lang('messages.Required'))">
                            </div>
                        </div>
                           
                        <?php /*  <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <input type="text" maxlength="10" name="civil_id" class="form-control" placeholder="@lang('messages.Civil id')">
                                </div>
                            </div> 
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <div class="sign_upcooper">
                                       {{ Form::select('cooperative', get_coperativess(),null, ['class' => 'form-control cooprative_select select_dropdown js-example-disabled-results'] ) }}
                                    </div>
                                </div>
                            </div>

                           <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <input name="member_id" maxlength="15" type="text" class="form-control" placeholder="@lang('messages.Member id')">
                                </div>
                            </div> */ ?>
                            <div class="col-md-12 col-sm-12 col-xs-12 check_sect">
                                <label class="label_check" for="checkbox-01">
                                       <input name="terms_condition"  id="checkbox-01" value="1" type="checkbox"/>@lang('messages.I agree oddappz')<a href="<?php echo  URL::to('cms/terms-amp-conditions') ?>" target="_blank" title="@lang('messages.Terms &amp; conditions')"> @lang('messages.Terms & conditions')</a>, <a href="<?php echo  URL::to('/cms/privacy-policy') ?>" target="_blank" title="@lang('messages.Privacy & policy')"> @lang('messages.Privacy & policy')</a> @lang('messages.and')<a href="<?php echo  URL::to('/cms/contracts') ?>" target="_blank" title="@lang('messages.Contracts for users')"> @lang('messages.Contracts for users')</a><label>
                             </div>			

                           <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <p class="sign_up" id="sign_in">@lang('messages.Already have account?') <a href="#" title="@lang('messages.Sign in')">@lang('messages.Sign in')</a>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <div class="sign_bot_sub">
										<button type="button" class="btn btn-primary cancel_button" data-url="" title="@lang('messages.Cancel')">@lang('messages.Cancel')</button>
                                        
										
										<button type="submit" class="btn btn-default" id="signupsubmit" title="@lang('messages.Submit')" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> @lang('messages.Processing')">@lang('messages.Sign Up')</button>
										
										<div class="ajaxloading" style="display:none;">
											<div class="loader-coms">
												<div class="loder_gif">
													<img src="<?php echo  url('assets/front/'.Session::get("general")->theme.'/images/ajax-loader.gif');?>" />
												</div>
											</div>
										</div>
										
                                    </div>
                                </div>
                            </div>
							<div class="col-md-12 fb_or">
                                <span><img src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/or.png'); ?>" alt=""></span>
                                <a href="{{ URL::to('auth/facebook') }}" title="@lang('messages.Signup  with  facebook?')"> <i class="glyph-icon flaticon-facebook-logo"></i>@lang('messages.Signup  with  facebook?')</a>
                            </div>
						{!!Form::close();!!}
                    </div>
                </div>
            </div>
        </div>

    </div>

    
<!-- Modal for otp -->
<div class="modal fade model_for_signup" id="reg_send_otp" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <?php $locale = Session::get('locale', Config::get('app.locale'));
             if ($locale == 'en') {?>
                <span class="logo_popup"><img alt="{{$general->site_name}}" src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/'.Session::get('general')->theme.'.png?'.time()); ?>"></span>
                 <?php  } else {?>
                <span class="logo_popup"><img alt="{{$general->site_name}}" src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/'.Session::get('general')->theme.'.png?'.time()); ?>"></span>
                 <?php } ?>	
            </div>
            <div class="modal-body" id="send_otp">
                <div class="sign_up_inner send_otp_sections">
                    {!!Form::open(array('url' => 'reg-send-otp', 'method' => 'post', 'class' => 'tab-form attribute_form', 'id' => 'check_otp','onsubmit'=>'return reg_send_otp()'));!!}
                        <input type="hidden" name="language" value="{{getCurrentLang()}}">
                        <div class="sign_up_inner">
                            <h4>On demand delivery across India</h4>
                            <div class="ajaxloading_otp" style="display:none;">
                                <div class="loader-coms">
                                    <div class="loder_gif">
                                        <img src="<?php echo url('assets/front/'.Session::get("general")->theme.'/images/ajax-loader.gif');?>" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12 resend_buttons" id="verify">
								<p>We send verification code to your mobile number. Please enter the code.</p>
								<div class="optp_inner">
									<div class="col-md-8 col-sm-9 col-xs-9 padding_right0">
										<input type="text" name="otp" maxlength="6" value="" class="with-gap otp_option search-query form-control" id="otp">
									</div>
									<input type="hidden" name="register" value="1">
									<input type="hidden" name="user_id" id="user_id" value="">
									<input type="hidden" name="otp_option" id="otp_option" value="2">
									<div class="col-md-4 col-sm-3 col-xs-3 padding0">
										<button type="button" onclick="verify_otp()" id="reg_verify_otp" class="btn btn-danger" title="@lang('messages.Verify')"data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> @lang('messages.Processing')">@lang('messages.Verify')</button>
									</div>
									<div class="error col-md-12">
										
										<button type="button" onclick ="ree_send_otp()"  id = "resend_otp" class="btn btn-primary"  title="@lang('messages.Resend')" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> @lang('messages.Processing')">@lang('messages.Resend')</button>
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

$(document).ready(function () {
 //called when key is pressed in textbox
 $("#phone_mumber").keypress(function (e) {
    //if the letter is not digit then display error and don't type anything
    if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
       //display error message
     toastr.warning('<?php echo trans('messages.Please enter valid phone number');?>');
              return false;
   }
  });
$('select').select2();
$("#phone_mumber").intlTelInput({
	
	nationalMode: false,
	separateDialCode: false,
	onlyCountries: ['in'],
	utilsScript: "<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/js/utils.js');?>" // just for formatting/placeholders etc
});
});

function signup()
{
	$this = $("#signupsubmit");
	$('#success_message_signup').show().html("");
	var isValid = $('#phone_mumber').intlTelInput("isValidNumber");
    var phone_mumber = $('#phone_mumber').val();
	if((!isValid) && (phone_mumber != ''))
	{
		toastr.warning('<?php echo trans('messages.Please enter valid phone number');?>');
		return false;
	}
	$this.button('loading');
	data = $("#sign_up").serializeArray();
	data.push({ name: "user_type", value: "1" });
	var c_url = '/signup-user';
	token = $('input[name=_token]').val();
	$.ajax({
		url: c_url,
		headers: {'X-CSRF-TOKEN': token},
		data: data,
		type: 'POST',
		datatype: 'JSON',
		success: function (resp)
		{
			$this.button('reset');
			data = resp;
			if(data.httpCode == 200)
			{  

                // console.log(data.user_id);
                 $('#user_id').val(data.user_id);
				$('#myModal').modal('hide');
				$('#reg_send_otp').modal('show');
			}
			else
			{
				toastr.warning(data.Message)
			}
		},
		error:function(resp)
		{
			$this.button('reset');
			//$("#ajaxloading").hide();
			console.log('out--'+data); 
			return false;
		}
	});
	return false;
}


	function ree_send_otp() 
	{
		 var $this = $("#resend_otp");
        $this.button('loading');
        var c_url = '/reg-send-otp';
        token = $('input[name=_token]').val();     
		user_id=$('#user_id').val();
		
		$.ajax({
            url: c_url,
            headers: {'X-CSRF-TOKEN': token},
            data: {token:token,user_id:user_id},
            type: 'POST',
            datatype: 'JSON',
            success: function (resp) {
				$this.button('reset');
                $(".ajaxloading_otp").hide();
                data = resp;
                if(data.httpCode == 200)
                {
			 toastr.success(resp.Message);
                $('.options_box').hide();
                $('.otp_box').show();
                $('.send_buttons').hide();
                $('.resend_buttons').show();
                }
                else
                {
					alert("nn"); return false;
                    toastr.warning(data.Message)
                }
            }, 
            error:function(resp)
            {
                //location.reload(true);
                console.log('out--'+data); 
                return false;
            }
        });
        return false;
    }

$(document).ready(function() 
{
	$('#otp').on("keypress", function(e) {
        if (e.keyCode == 13) {
			otp = $("#otp").val();
			if(otp == "")
			{
				toastr.warning("<?php echo trans('messages.Please fill OTP') ?>");
				return false;
			}
			var $this = $("#verify_otp");
			$this.button('loading');
			var c_url = '/check-otp';
			$.ajax({
				url: c_url,
				headers: {'X-CSRF-TOKEN': token},
				data: {otp:otp,token:token,user_id:user_id},
				type: 'POST',
				datatype: 'JSON',
				success: function (resp) {
					$this.button('reset');
					$(".ajaxloading_otp").hide();
					data = resp;
					if(data.httpCode == 200)
					{
						/*toastr.success(data.Message);*/
						/*$('#checkout_form').submit();*/
						$( "#checkout_form" )[0].submit();
						$("#fadpage").show();
						$('#reg_send_otp').modal("hide");
						location.reload(true);
						toastr.options.onShown = function() { location.reload(true); }
					}
					else
					{
						toastr.warning(data.Message)
					}
				}, 
				error:function(resp)
				{
					//location.reload(true);
					console.log('out--'+data); 
					return false;
				}
			});
			return false;
        }
	});
		
		$('#reg_verify_otp').on('click', function() 
	{
		otp = $("#otp").val();
		
        if(otp == "")
        {
            toastr.warning("<?php echo trans('messages.Please fill OTP') ?>");
            return false;
        }
        var $this = $("#reg_verify_otp");
        $this.button('loading');
        var c_url = '/reg-check-otp';
        token = $('input[name=_token]').val();     
		user_id=$('#user_id').val();
		
		$.ajax({
            url: c_url,
            headers: {'X-CSRF-TOKEN': token},
            data: {otp:otp,token:token,user_id:user_id,register:1},
            type: 'POST',
            datatype: 'JSON',
            success: function (resp) {
				$this.button('reset');
                $(".ajaxloading_otp").hide();
                data = resp;
                if(data.httpCode == 200)
                {
					//alert("Ss"); return false;
					$('#reg_send_otp').modal("hide");
					toastr.success(data.Message);
					setInterval(function(){ location.reload(true); }, 5000);
                }
                else
                {
					
                    toastr.warning(data.Message)
                }
            }, 
            error:function(resp)
            {
                //location.reload(true);
                console.log('out--'+data); 
                return false;
            }
        });
        return false;
    });
    //$('#verify_otp').on('click',
   
});
</script>
