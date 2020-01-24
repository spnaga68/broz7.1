    <link href="{{ captcha_layout_stylesheet_url() }}" type="text/css" rel="stylesheet">
	    <!-- contact us sections -->
    <section class="contact_us_section_service_det">
        <div class="container-fluid">
            <div class="contact_us_inner_new">
                <div class="contact_us_form_service">
                    <h3 class="contact_header_text">Want to deploy our services in to your business? </h3>
                    <span class="contact_info"> </span>
                    <div class="row">
                                  {!!Form::open(array('url' => 'postcontactservice', 'method' => 'post','class'=>'col-md-12 row','id'=>'contactus_form','files' => true));!!}
                            <div class="input-field col-md-6">
                                <input type="text" kl_virtual_keyboard_secure_input="on" id="last_name" class="validate" name="name" value="{!! old('name') !!}" required>
                                <label for="last_name">@lang('messages.Name')</label>
                                 <span class="error"> 
                                                @if ($errors->has('name'))
												{{ $errors->first('name', ':message') }}
												@endif
								</span>
                            </div>
                            <div class="input-field col-md-6">
                                <input type="email" id="email"  name="email" value="{!! old('email') !!}" class="validate" required>
                                <label for="email">@lang('messages.Email')</label>
                                <span class="error"> 
                                                @if ($errors->has('email'))
												{{ $errors->first('email', ':message') }}
												@endif
												</span>
                            </div>
                            <div class="input-field col-md-12">
                                <textarea id="textarea1" name="message" class="materialize-textarea">{!! old('message') !!}</textarea>
                                <label for="textarea1">Message</label>
                                            <span class="error"> 
                                                @if ($errors->has('message'))
												{{ $errors->first('message', ':message') }}
												@endif
												</span>
                            </div>
                             <?php $segment = Request::segment(1);?>
                            <input type="hidden" name="redirect_url" value="<?php echo $segment;?>">
                             <div class="form-group margin_top">
                                      <div class="col-md-7 captcha">
										 <div class="captcha_sec">
										   <?php   echo captcha_image_html('ContactCaptcha'); ?>
										     <input type="text" name="captcha" value="{!! old('captcha') !!}"  id="CaptchaCode"  required>
										      <span class="error"> 
                                                @if ($errors->has('captcha'))
												{{ $errors->first('captcha', 'Invalid security code') }}
												@endif
												</span>
										</div>
										<?php if($errors->has('captcha')){ ?>
									<script type="text/javascript">
										$(document).ready(function() {
										 $("html,body").animate({scrollTop: 6700}, 1000);
									});
									</script>
									<?php } ?>
                                    </div>
                            <div class="buttons_sections">
                                <div class="col-md-5 submit_buttons">
                                    <button class="btn waves-effect waves-light" title="Send now" type="submit" name="action">Send now
                                    </button>
                                </div>
                            </div>
                           {!!Form::close();!!} 
                    </div>
                </div>
            </div>
        </div>
    </section>
        <script type="text/javascript">
		$( document ).ready(function() {
			$('.BDC_CaptchaImageDiv a').addClass('test');
			$('.test').css('visibility', 'hidden');
		});
	</script>
    <!-- contact us sections -->
