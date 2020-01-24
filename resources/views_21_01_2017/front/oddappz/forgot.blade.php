   <!-- Modal for signIn -->
	<div class="modal model_for_signup" id="forgot_model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false" data-toggle="modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <span class="logo_popup"><img alt="oddappz" src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/oddappz.png?'.time()); ?>"></span>
                </div>
                <div class="modal-body">
                    <div class="sign_up_inner">
                        <h2>@lang('messages.Forgot Password')<br>
							<span class="bottom_border"></span>
						</h2><div id="forgot_pass_message"></div>
                        {!!Form::open(array('url' => 'forgot', 'method' => 'post', 'class' => 'tab-form attribute_form', 'id' => 'forgot','onsubmit'=>'return forgot()'));!!} 
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <input type="email" name="email" maxlength="256" class="form-control" required id="exampleInputEmail" placeholder="Email">
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <div class="sign_bot_sub">
                                        <button type="button" class="btn btn-primary cancel_button" data-url="forgot_cancel" title="@lang('messages.Cancel')">@lang('messages.Cancel')</button>
                                        <button type="submit" id="forgotsubmit" class="btn btn-default" title="@lang('messages.Send')" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> @lang('messages.Processing')">@lang('messages.Send')</button>
										<div id="ajaxloading_forgot" style="display:none;">
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
