<link href="{{ URL::asset('assets/admin/base/css/bootstrap-timepicker.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/admin/base/css/select2.css') }}" media="all" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" /> 
<?php $general = Session::get("general"); $social = Session::get("social"); $email = Session::get("configemail"); $languages = Session::get("languages"); ?>
<script type="text/javascript" src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/js/validator.min.js');?>"></script>
    <!-- Modal for signup -->
    <div class="modal model_for_signup" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <span class="logo_popup"><img alt="{{$general->site_name}}" src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/oddappz.png?'.time()); ?>"></span>
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
								  <input type="text" maxlength="56" name="first_name" class="form-control" required placeholder="@lang('messages.First Name') (@lang('messages.Required'))">

                                </div>
                            </div>
							
							<div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
								  <input type="text" maxlength="56" name="last_name" class="form-control" required placeholder="@lang('messages.Last Name') (@lang('messages.Required'))">

                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">

                                   <input type="email" name="email" maxlength="250" class="form-control" required id="exampleInputEmail" placeholder="@lang('messages.Email') (@lang('messages.Required'))">

                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
								
                                    <input type="password" name="password" maxlength="15" required id="password"   maxlength="255" placeholder="@lang('messages.Password') (@lang('messages.Required'))" class="form-control"  />
                                </div>
                            </div>
							 <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <div class="gender_section">
                                            <label class="left_label">@lang('messages.Gender')</label>
                                            <label class="label_radio" for="radio-02">
                                            <input required checked name="gender" id="radio-02" value="F" type="radio" />@lang('messages.Female')</label>
                                            <label class="label_radio" for="radio-03">
                                            <input required name="gender" id="radio-03" value="M" type="radio" />@lang('messages.Male')</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
								
                                    <input type="text" maxlength="15"  name = "phone" class="form-control" required placeholder="@lang('messages.Phone') (@lang('messages.Required'))">
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

                      <?php /*      <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <input name="member_id" maxlength="15" type="text" class="form-control" placeholder="@lang('messages.Member id')">
                                </div>
                            </div> */ ?>
                            <div class="col-md-12 col-sm-12 col-xs-12 check_sect">
                                <label class="label_check" for="checkbox-01">
                                    <input name="terms_condition"  id="checkbox-01" value="1" type="checkbox" checked />@lang('messages.I agree the') <a href="<?php echo  URL::to('/cms/terms-amp-conditions') ?>" target="_blank" title=">@lang('messages.Terms &amp; conditions')"> @lang('messages.Terms &amp; conditions')</a> @lang('messages.of') {{$general->site_name}} </label>
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
    <!-- Modal for signup end -->
	<script type="text/javascript">
$('select').select2();
/*$('#sign_up').validator().on('submit', function (e)
{
  if (e.isDefaultPrevented())
  {
	//alert($("#city").val());
	if($("#city").val() == "")
	{
		$("#city").addClass("has-error");
	}
	if($("#enquiry_type").val() == "")
	{
		$("#enquiry_type").addClass("has-error");
	}
	return false;
  }
  else 
  {
	
	
    $this = $("#signupsubmit");
    $this.button('loading');
    $('#success_message_signup').show().html("");
    data = $("#sign_up").serializeArray();
    data.push({
        name: "user_type",
        value: "1"
    });
    var c_url = '/signup-user';
    token = $('input[name=_token]').val();
    $.ajax({
        url: c_url,
        headers: {
            'X-CSRF-TOKEN': token
        },
        data: data,
        type: 'POST',
        datatype: 'JSON',
        success: function(resp) {
            $this.button('reset');
            data = resp;
            if (data.httpCode == 200) {
                $('#myModal').modal('hide');
                toastr.success(data.Message);
                location.reload(true);
                toastr.options.onShown = function() {
                    location.reload(true);
                }
            } else {
                toastr.warning(data.Message)
            }
        },
        error: function(resp) {
            $this.button('reset');
            console.log('out--' + data);
            return false;
        }
    });
    return false;
  }
}); */
 
</script>
