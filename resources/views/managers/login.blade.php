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
                                <form class="form-horizontal" role="form" method="POST" action="{{ url('managers/signin') }}">
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
                                                    <label>
                                                        <input type="checkbox" name="remember"> @lang('messages.Remember Me')
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>*/ ?>
                                    <div class="form-group">
                                        <div class="login_update_new">
                                            <div class="col-md-12">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fa fa-btn fa-sign-in"></i>@lang('messages.Log in')
                                                </button>
                                                <div class="col-md-12">
                                                    <a class="btn btn-link" href="{{ url('/managers/reset') }}">@lang('messages.Forgot Your Password?')</a>
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
