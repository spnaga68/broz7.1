@include('includes.admin.head')
<div class="contentpanel_ourter">
    <div class="contentpanel">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="admin_login">
                    <div class="admin_login_inner">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <div class="logo text-center logo_setions_absolut">
                                    <img title="<?php echo Session::get("general")->site_name; ?>" alt="<?php echo Session::get("general")->site_name; ?>" src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/logo/159_81/admin_logo.png'); ?>" >
                                </div>
                            </div>
                            <div class="panel-body">
                                @if (session('status'))
                                    <div class="alert alert-success">
                                        {{ session('status') }}
                                    </div>
                                @endif
                                @if (session::has('message'))
                                    <div class="admin_sucess_common">
                                        <div class="admin_sucess">
                                            <div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
                                        </div>
                                    </div>
                                @endif
                                @if (isset($error_email) && $error_email)<?php echo 111111;die;?>
                                    <div class="admin_sucess_common">
                                        <div class="admin_sucess">
                                            <div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ $error_email }}</div>
                                        </div>
                                    </div>
                                @endif
                                <form class="form-horizontal" role="form" method="POST" action="{{ url('/login') }}">
                                    {!! csrf_field() !!}
                                    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                        <label class="col-md-12 control-label">@lang('messages.E-Mail Address')</label>
                                        <div class="col-md-12">
                                            <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                                            @if ($errors->has('email'))
                                                <span class="help-block">
                                                    <strong><?php echo trans('messages.'.$errors->first('email')); ?> </strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                        <label class="col-md-12 control-label">@lang('messages.Password')</label>
                                        <div class="col-md-12">
                                            <input type="password" class="form-control" name="password" required>
                                            @if ($errors->has('password'))
                                                <span class="help-block">
                                                    <strong><?php echo trans('messages.'.$errors->first('password')); ?></strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php /*<div class="form-group">
                                        <div class="login_update_new">
                                            <div class="col-md-12">
                                                <div class="checkbox">
                                                    <label><input type="checkbox" name="remember"> @lang('messages.Remember Me')</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>*/ ?>
                                    <div class="form-group">
                                        <div class="login_update_new">
                                            <div class="col-md-12">
                                                <button type="submit" class="btn btn-primary"><i class="fa fa-btn fa-sign-in"></i>@lang('messages.Log in')</button>
                                                <div class="col-md-12">
                                                    <a class="btn btn-link" href="{{ url('/password/reset') }}">@lang('messages.Forgot Your Password?')</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div> 
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-signin "> 
                        <div class="panel-footer"> <?php echo Session::get("general")->copyrights; ?> </div>
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
            img: '<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/banner.png');?>', // Relative path example.  You could also use an absolute url (http://...).
            opacity: 1, // Opacity. 1 = 100%.  This is optional.
            center: true // Boolean (true or false). This is optional. Default is true.
        });
    });
</script>
<!-- Home page banner resize script end-->
