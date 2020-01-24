@extends('layouts.managers')
@section('content')
<div class="pageheader">
	<div class="media">
		<div class="pageicon pull-left">
			<i class="fa fa-home"></i>
		</div>
		<div class="media-body">
			<ul class="breadcrumb">
				<li><a href="{{url('managers/dashboard')}}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Restaurant Managers')</a></li>
				<li>@lang('messages.Change Password')</li>
			</ul>
			<h4>@lang('messages.Change Password')</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->

@if (Session::has('message'))
    <div class="alert alert-info">
		<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>{{ Session::get('message') }}
	</div>
@endif
<div class="contentpanel">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">@lang('messages.Change Password')</div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/managers/password_data') }}">
                        {!! csrf_field() !!}
                        <div class="form-group{{ $errors->has('old_password') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">@lang('messages.Old Password')</label>
                            <div class="col-md-8">
                                <input type="password" class="form-control" name="old_password" value="" --required />
                                @if ($errors->has('old_password'))
                                    <span class="help-block">
                                        <strong><?php echo trans('messages.'.$errors->first('old_password')); ?></strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">@lang('messages.New Password')</label>
                            <div class="col-md-8">
                                <input type="password" class="form-control" name="password" --required />
                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong><?php echo trans('messages.'.$errors->first('password')); ?></strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">@lang('messages.Confirm Password')</label>
                            <div class="col-md-8">
                                <input type="password" class="form-control" name="password_confirmation" --required />
                                @if ($errors->has('password_confirmation'))
                                    <span class="help-block">
                                        <strong><?php echo trans('messages.'.$errors->first('password_confirmation'));?></strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-envelope"></i> @lang('messages.Reset Password')
                                </button>
                            </div>
                        </div>
					</form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
