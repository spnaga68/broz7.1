   <!-- Modal for otp -->
    <div class="modal fade model_for_signup" id="send_otp" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                   
                      <?php $locale = Session::get('locale', Config::get('app.locale')); ?>
	               <?php if ($locale == 'en') {?>
                    <span class="logo_popup"><img alt="'.Session::get('general')->site_name.'" src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/'.Session::get('general')->theme.'.png'); ?>"></span>
                     <?php  } else {?>
					  <span class="logo_popup"><img alt="'.Session::get('general')->site_name.'" src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/'.Session::get('general')->theme.'.png'); ?>"></span>
					 <?php } ?>	
                </div>
                <div class="modal-body">
                    <div class="sign_up_inner send_otp_sections">
						 {!!Form::open(array('url' => 'check-otp', 'method' => 'post', 'class' => 'tab-form attribute_form', 'id' => 'check_otp','onsubmit'=>'return send_otp()'));!!} 
							<div class="sign_up_inner">
							 <h4>@lang('messages.On-demand delivery, across India')</h4>
							<div class="ajaxloading_otp" style="display:none;">
								<div class="loader-coms">
								<div class="loder_gif">
									<img src="<?php echo url('assets/front/'.Session::get("general")->theme.'/images/ajax-loader.gif');?>" />	
								</div>
								</div>
							</div>
							<div class="input-field col-sm-12 col-xs-12 radio_butt options_box">
								<h2>@lang('messages.SEND VERIFICATION CODE TO')
									<br>
									<span class="bottom_border"></span>
								</h2>
							<label class="label_radio" for="email">
									<input name="otp_option" class="with-gap otp_option" id="email" value="1" type="radio" /> @lang('messages.Email')
								</label> 
								
								<label class="label_radio" for="mobile">
									<input name="otp_option" class="mobile" id="mobile" value="2" type="radio" /> @lang('messages.Mobile')
								</label>
								<label class="label_radio" for="both">
									<input type="radio" name="otp_option" value="3" class="with-gap otp_option" checked id="both">@lang('messages.Both')
								</label>
							</div>
                            <div class="col-md-12 col-sm-12 col-xs-12 send_buttons">
                                <div class="form-group">
                                    <div class="sign_bot_sub">
                                        <button type="button" class="btn btn-primary cancel_button" data-url="cards" title="@lang('messages.Cancel')">@lang('messages.Cancel')</button>
										<button type="submit" id="otpsubmit" class="btn btn-default" title="@lang('messages.Submit')"data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> @lang('messages.Processing')">@lang('messages.Send')</button>
                                    </div>
                                </div>
                            </div>
							</div>
							<div class="col-md-12 col-sm-12 col-xs-12 resend_buttons" style="display:none;">
								<p id ="otp_test"></p>
									<div class="optp_inner">
										<div class="col-md-8 col-sm-9 col-xs-9 padding_right0">
											<input type="text" name="otp" value="" class="with-gap otp_option search-query form-control" id="otp">
										</div>
										<div class="col-md-4 col-sm-3 col-xs-3 padding0">
											<button type="button" id="verify_otp" class="btn btn-danger" title="@lang('messages.Verify')"data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> @lang('messages.Processing')">@lang('messages.Verify')</button>
										</div>
										<div class="error col-md-12">
											<a href="javascript:void(0)" Resend OTP</a>
											<button type="submit" id="resend_otp" class="btn btn-primary" data-url="cards" title="@lang('messages.Resend')" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> @lang('messages.Processing')">@lang('messages.Resend')</button>
										</div>
									</div>
							</div>
						{!!Form::close();!!}
                    </div>
                </div>
            </div>
        </div>
    </div>
	<script type="text/javascript">
	function send_otp()
	{
		var $this = $("#otpsubmit");
		  var otp_option  = $('input[name=otp_option]:checked').val();
		 // alert(otp_option);
		$this.button('loading');
		$("#resend_otp").button('loading');
		data = $("#check_otp").serializeArray();
		var c_url = '/send-otp';
		token = $('input[name=_token]').val();
		$.ajax({
			url: c_url,
			headers: {'X-CSRF-TOKEN': token},
			data: data,
			type: 'POST',
			datatype: 'JSON',
			success: function (resp) {
				 $this.button('reset');
				 $("#resend_otp").button('reset');
				$(".ajaxloading_otp").hide();
				data = resp;
				if(data.httpCode == 200)
				{
					toastr.success(data.Message);
					
					$('.options_box').hide();
					$('.otp_box').show();
					$('.send_buttons').hide();
					$('.resend_buttons').show();
					if(otp_option == 1)
                       $('#otp_test').html('We have sent a verification code to your email. Please enter the code.');
                    else if(otp_option == 2)
                            $('#otp_test').html('We have sent a verification code to your mobile number. Please enter the code.');
                    else
                      $('#otp_test').html('We have sent a verification code to your email & mobile number. Please enter the code.');
				}
				else
				{
					toastr.warning(data.Message)
				}
			}, 
			error:function(resp)
			{
				 $this.button('reset');
				//location.reload(true);
				console.log('out--'+data); 
				return false;
			}
		});
		return false;
	}
	
	$(document).ready(function() 
	{
		$('#verify_otp').on('click', function() 
		{
			otp = $("#otp").val();
			if(otp == "")
			{
				toastr.warning("<?php echo trans('messages.Please fill OTP') ?>");
				return false;
			}
			var $this = $("#verify_otp");
			$this.button('loading');
			var c_url = '/check-otp';
			token = $('input[name=_token]').val();
			$.ajax({
				url: c_url,
				headers: {'X-CSRF-TOKEN': token},
				data: {otp:otp},
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
						$('#send_otp').modal("hide");
						
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
	});
	</script>
