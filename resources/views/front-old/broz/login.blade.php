   <!-- Modal for signIn -->
    <div class="modal model_for_signup" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <?php $locale = Session::get('locale', Config::get('app.locale')); ?>
	               <?php if ($locale == 'en') {?>
							<span class="logo_popup"><img alt="'.Session::get('general')->theme.'" src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/'.Session::get("general")->theme.'.png?'.time()); ?>"></span>
                    <?php  } else {?>
							<span class="logo_popup"><img alt="'.Session::get('general')->theme.'" src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/'.Session::get("general")->theme.'.png?'.time()); ?>"></span>
					 <?php } ?>	
                </div>
                <div class="modal-body">
                    <div class="sign_up_inner">
                        <h2>@lang('messages.sign In')<br>
							<div id="success_message_login" style="display:none">logged in successfully</div>
							<span class="bottom_border"></span>
						</h2>
                        {!!Form::open(array('url' => 'login', 'method' => 'post', 'class' => 'tab-form attribute_form', 'id' => 'login','onsubmit'=>'return login()'));!!} 
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <input type="email" maxlength="256" name="email" class="form-control"  id="exampleInputEmail" placeholder="@lang('messages.Email')">
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <input type="password" maxlength="32" name="password" class="form-control"  id="exampleInputPassword13" placeholder="@lang('messages.Password')">
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <p class="sign_up" id="signup_link">@lang('messages.Don’t have an account?')
									<a href="#" title="@lang('messages.Sign in')">@lang('messages.Sign up')</a>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <div class="sign_bot_sub">
                                        <button type="button" class="btn btn-primary cancel_button" data-url="cards" title="@lang('messages.Cancel')">@lang('messages.Cancel')</button>
                                        <button type="submit" id="signsubmit" class="btn btn-default" title="@lang('messages.Submit')" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> @lang('messages.Processing')">@lang('messages.sign In')</button>
										<div id="ajaxloading" style="display:none;">
											<div class="loader-coms">
												<div class="loder_gif">
													<img src="<?php echo url('assets/front/'.Session::get("general")->theme.'/images/ajax-loader.gif');?>" />
												</div>
											</div>
										</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <span class="forgout_pass"><a id="forgot_pass" href="javascript:;" title="@lang('messages.Forgot password?')">@lang('messages.Forgot password?')</a></span>
                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    
                                </div>
                            </div>
                            <div class="col-md-12 fb_or">
                                <span><img src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/or.png'); ?>" alt=""></span>
                                <a href="{{ URL::to('auth/facebook') }}" title="@lang('messages.Signup  with  facebook?')"> <i class="glyph-icon flaticon-facebook-logo"></i>@lang('messages.Login  with  facebook?')</a>
                            </div>
						{!!Form::close();!!}
                    </div>
                </div>
            </div>
        </div>
    </div>
