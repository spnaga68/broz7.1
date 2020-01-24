@include('includes.admin.head')
<div class="contentpanel_ourter">
<div class="contentpanel">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
		
		<div class="admin_login">
		<div class="admin_login_inner">
	<div class="panel panel-default">
            
                <div class="panel-heading">
					  <div class="panel-heading">@lang('messages.Reset Password')</div>
				<div class="logo text-center logo_setions_absolut">
			<img  alt="<?php echo Session::get("general")->site_name; ?>" alt="<?php echo Session::get("general")->site_name; ?>" src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/logo/159_81/admin_logo.png'); ?>" >
			</div></div>
                
                <div class="panel-body">
				@if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
						  <form class="form-horizontal" role="form" method="POST" action="{{ url('/password/reset') }}">
                        {!! csrf_field() !!}


                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">@lang('messages.E-Mail Address')</label>

                            <div class="col-md-8">
                                <input type="email" class="form-control" name="email" value="{{ $email or old('email') }}" required>

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">@lang('messages.Password')</label>

                            <div class="col-md-8">
                                <input type="password" class="form-control" name="password" required>

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">@lang('messages.Confirm Password')</label>
                            <div class="col-md-8">
                                <input type="password" class="form-control" name="password_confirmation" required>

                                @if ($errors->has('password_confirmation'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary" >
                                    <i class="fa fa-btn fa-envelope"></i>@lang('messages.Reset Password')
                                </button>
                            </div>
                        </div>
                        
                    </form>
                </div>
            </div>
		</div>
			
		</div>
        </div>
    </div>
</div>

</div>

<script src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/js/jquery.ez-bg-resize.js');?>" type="text/javascript" charset="utf-8"></script>
<!-- Home page banner resize script-->
<script type="text/javascript">
    $(document).ready(function() {
        $(".contentpanel_ourter").ezBgResize({
            img: '<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/banner.png');?>", // Relative path example.  You could also use an absolute url (http://...).
            opacity: 1, // Opacity. 1 = 100%.  This is optional.
            center: true // Boolean (true or false). This is optional. Default is true.
        });
    });
</script>
<!-- Home page banner resize script end-->
