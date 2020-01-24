@include('includes.admin.head')
<div class="contentpanel_ourter">
	<div class="contentpanel">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<div class="admin_login">
					<?php $site_name  = Session::get('general')->site_name;
						$copty_rights = Session::get('general')->copy_rights; ?>
					<div class="admin_login_inner">
						<div class="panel panel-default">
							<div class="panel-heading">
								<div class="logo text-center logo_setions_absolut">
									<img title="<?php echo $site_name; ?>" alt="<?php echo $site_name; ?>" src="<?php echo url('/assets/front/base/images/logo/159_81/admin_logo.png'); ?>" >
								</div>
							</div>
							<div class="panel-body">
								@if (session('status'))
									<div class="alert alert-success">
										{{ session('status') }}
									</div>
								@endif
								<form class="form-horizontal" role="form" method="POST" action="{{ url('/vendors/forgot_mail') }}">
								{!! csrf_field() !!}
									<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
										<label class="col-md-12 control-label">@lang('messages.E-Mail Address')</label>
										<div class="col-md-12">
											<input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
											@if ($errors->has('email'))
												<span class="help-block">
													<strong>{{ $errors->first('email') }}</strong>
												</span>
											@endif
										</div>
									</div>
									<div class="form-group">
										<div class="col-md-6 col-md-offset-4">
											<button type="submit" class="btn btn-primary">
												<i class="fa fa-btn fa-envelope"></i>@lang('messages.Reset')
											</button>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
					<div class="panel panel-signin "> 
						<div class="panel-footer"> <?php echo $copty_rights; ?> </div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script src=" {{ URL::asset('assets/front/base/js/jquery.ez-bg-resize.js') }} " type="text/javascript" charset="utf-8"></script>
<!-- Home page banner resize script-->
<script type="text/javascript">
    $(document).ready(function() {
        $(".contentpanel_ourter").ezBgResize({
            img: "{{ URL::asset('assets/front/base/images/banner.png') }}", // Relative path example.  You could also use an absolute url (http://...).
            opacity: 1, // Opacity. 1 = 100%.  This is optional.
            center: true // Boolean (true or false). This is optional. Default is true.
        });
    });
</script>
<!-- Home page banner resize script end-->
