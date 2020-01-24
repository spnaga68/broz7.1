 <!-- Modal for membership signIn -->
    <div class="modal fade model_for_signup membership_login" id="myModal3" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <span class="logo_popup"><img alt="'.Session::get('general')->theme.'" src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/'.Session::get("general")->theme.'.png'); ?>"></span>
                </div>
                <div class="modal-body">
                    <div class="sign_up_inner">
                        <h2>Membership update<br>
	  <span class="bottom_border"></span>
	  </h2>
                        <p>Do you have membership in cooperative?</p>
                        <form>
                            <div class="membership_inner">
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <div class="form-group">
                                        <div class="gender_section">
                                            <label class="label_radio" for="radio-04">
                                            <input name="sample-radio" id="radio-04" value="1" type="radio" /> Yes</label>
                                            <label class="label_radio" for="radio-05">
                                            <input name="sample-radio" id="radio-05" value="1" type="radio" />No</label>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <div class="form-group">
                                        <div class="sign_upcooper">
                                            <select class="js-example-disabled-results" required style="width:100%;">
                                            <option value="one">Select a cooperative</option>
                                            <option value="three">Select a cooperative</option>
                                        </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <div class="form-group">
                                        <input type="text" class="form-control" required id="exampleInputEmail" placeholder="Membership id">
                                    </div>
                                </div>
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <div class="form-group">
                                        <div class="sign_bot_sub">
                                            <button type="button" class="btn btn-primary cancel_button" data-url="" title="@lang('messages.Cancel')">Cancel</button>
                                            <button type="submit" class="btn btn-default" title="@lang('messages.Submit')">Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>