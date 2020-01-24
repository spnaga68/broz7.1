<html lang="en" ng-app="app" ng-controller="titleCtrl">
<head>
	{!! SEOMeta::generate() !!}
	{!! OpenGraph::generate() !!}
	{!! Twitter::generate() !!}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<meta name="description" content="Free Web tutorials">
	<meta name="keywords" content="HTML,CSS,XML,JavaScript">
    <meta name="author" content="Hege Refsnes">
	<meta name="csrf-token" content="{{ csrf_token() }}" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/> <!--320-->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<title>Oddappz - OnDemand Delivery App</title>
	<?php $general = Session::get("general"); $social = Session::get("social"); $email = Session::get("configemail"); $languages = Session::get("languages"); ?>
<link rel="shortcut icon" href="<?php echo url('/assets/front/'.$general->theme.'/images/favicon/16_16/'.Session::get("general")->favicon.'?'.time()); ?>">
	<!-- Bootstrap -->

	<?php if(App::getLocale()=='ar') {  ?>
		<link href="{{ URL::asset('assets/front/'.$general->theme.'/css/bootstrap.min.css') }}" media="all" rel="stylesheet" type="text/css" />
		<link href="{{ URL::asset('assets/front/'.$general->theme.'/css/bootstrap-rtl.css') }}" media="all" rel="stylesheet" type="text/css" />
		<link href="{{ URL::asset('assets/front/'.$general->theme.'/css/style-rtl.css') }}" media="all" rel="stylesheet" type="text/css" />
	<?php } else { ?>
		<link href="{{ URL::asset('assets/front/'.$general->theme.'/css/bootstrap.min.css') }}" media="all" rel="stylesheet" type="text/css" />
		<link href="{{ URL::asset('assets/front/'.$general->theme.'/css/style.css') }}" media="all" rel="stylesheet" type="text/css" />
	<?php } ?>
	
	<link href="{{ URL::asset('assets/front/'.$general->theme.'/css/font.css') }}" media="all" rel="stylesheet" type="text/css" />
	<link href="{{ URL::asset('assets/front/'.$general->theme.'/fonts/flaticon/flaticon.css') }}" rel="stylesheet" />
	<link href="{{ URL::asset('assets/front/'.$general->theme.'/css/select2.min.css') }}" media="all" rel="stylesheet" type="text/css" />
	  <link href="{{ URL::asset('assets/front/'.$general->theme.'/css/res_menu.css') }}" rel="stylesheet" />
	  <link href="{{ URL::asset('assets/front/'.$general->theme.'/css/owl.carousel.css') }}" rel="stylesheet" />
	<link href="{{ URL::asset('assets/front/'.$general->theme.'/css/responsive.css') }}" rel="stylesheet" />
	<link href="{{ URL::asset('assets/front/'.$general->theme.'/css/easy-responsive-tabs.css') }}" rel="stylesheet" />
	<link href="{{ URL::asset('assets/front/'.$general->theme.'/css/toastr.css') }}" rel="stylesheet" />

</head>
<body <?php if(App::getLocale() == 'ar'){ echo "dir='rtl'"; }?>>
<header class="heder_position home_drop">
        <div class="headerContainerWrapper">
            <div class="headerContainerShadow">
                <div class="container">
                <div class="row">
                    <div class="responsive_common">
                        <div class="nav-toggle">
                            <div class="icon-menu">
                                <span class="line line-1"></span>
                                <span class="line line-2"></span>
                                <span class="line line-3"></span>
                            </div>
                        </div>
                        <nav class="navbar navbar-default">
                            <div class="header_common">
                                <!-- Brand and toggle get grouped for better mobile display -->
                                <div class="col-md-3 col-sm-4 col-xs-6 logo_responsive_sec">
                                    <div class="navbar-header">
                                        <a class="navbar-brand" href="<?php echo url('/'); ?>" title="{{ $general->site_name }}">

						<img src="<?php echo url('/assets/front/'.$general->theme.'/images/'.$general->theme.'.png?'.time()); ?>" title="{{ $general->site_name }}" alt="{{ $general->site_name }}">
						</a>

                                    </div>
                                </div><?php //echo '<pre>';print_r(getlocation($api));die;?>
                                <!-- Collect the nav links, forms, and other content for toggling -->
                                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                                  
									<div class="col-md-4 col-sm-6 col-xs-12 padding0 full_width_sec">
                                       <?php /*   <div class="select_city_sect">
                                            <select  name="location" id="location" class="js-example-disabled-results">
												<?php if(count(getlocation($api)->response->data)){ ?>
													<option value="">@lang('messages.Select location')</option>
													<?php foreach(getlocation($api)->response->data as $data){ ?>
															<option <?php if(Input::get('location')== $data->url_index){ echo "selected=selected"; }elseif(Session::get('location')== $data->url_index){ echo "selected=selected"; }?> value="{{ $data->url_index }}">{{ ucfirst($data->zone_name) }} </option>
													<?php } ?>	
												<?php } else { ?>
													<option value="">@lang('messages.No location found')</option>
												<?php } ?>
											 </select>
                                        </div> */ ?>
                                    </div>
                                    <div class="col-md-5 col-sm-6 col-xs-6 responsive_hidde">
                                        <ul class="nav navbar-nav navbar-right">
							
											<?php
												if(Session::has('user_id')) {?>
												<li>
													<a title="{{Session::get('social_title').Session::get('name')}} " id="open_drop_hed" href="{{ URL::to('/profile') }}" title="@lang('messages.Login')"><i class="glyph-icon flaticon-social"></i><?php 
													echo Session::get('social_title').' '.ucfirst(str_limit(Session::get('name'), 10)); ?>
													
													</a>
													
														<div class="after_login_drop">
								<ul>
								<li class="{{ Request::is('orders*') ? 'active' : '' }}"><a href="{{url('orders')}}" title="@lang('messages.My orders')">@lang('messages.My orders')</a></li>
								<li class="{{ Request::is('favourites*') ? 'active' : '' }}"><a href="{{url('favourites')}}" title="@lang('messages.My favourites')">@lang('messages.My favourites')</a></li>
								<li class="{{ Request::is('cart*') ? 'active' : '' }}"><a href="{{url('cart')}}" title="@lang('messages.My cart')">@lang('messages.My cart')</a></li>
								<li class="{{ Request::is('logout*') ? 'active' : '' }}">
									<a href="{{url('logout')}}" title="@lang('messages.Logout')">@lang('messages.Logout')</a>
								</li>
								</ul>
								</div>
												</li>
												<?php } else { ?>
												<li><a href="javascript:;" data-toggle="modal" data-target="#myModal2" title="@lang('messages.Login')">@lang('messages.Login')</a></li>
												<li><a href="javascript:;" title="@lang('messages.Signup')" data-toggle="modal" data-target="#myModal">@lang('messages.Signup')</a></li>
												<?php } ?>
												<?php $module = modules_list(); if($module->module_name == 'Offer' && $module->active_status == 1) { ?>
									<li><a href="{{ URL::to('/offer') }}" title="@lang('messages.Offer')">@lang('messages.Offer')</a></li>
								<?php } ?>
												<li><a href="{{ URL::to('/contact-us') }}" title="@lang('messages.Help')">@lang('messages.Help')</a>
												</li>
												<li>
												
													<div class="language_selection">
														<?php if(count($languages)){ 
														foreach($languages as $key => $val){ ?>
														<?php if(App::getLocale()==$val->language_code){  ?>
																  <h3><a style="cursor:pointer;"  class="languageselection"  <?php if($val->language_code=="en"){ ?> id="ar" <?php } ?>  <?php if($val->language_code=="ar"){ ?> id="en" <?php } ?>title="<?php echo strtoupper($val->language_code); ?>"><?php echo strtoupper($val->language_code); ?></a></h3>
														<?php } ?>  
														<?php } } ?>                                                    
													</div>
												</li>
										</ul>

                                    </div>
                                </div>
                                <!-- /.navbar-collapse -->
								<!-- cart responsive buttons-->
									<div class="responsive_cart">
								<?php if(count($languages)){ 
													foreach($languages as $key => $val){ ?>
													<?php if(App::getLocale()==$val->language_code){  ?>
															  <h3><a style="cursor:pointer;"  class="languageselection language_ar"  <?php if($val->language_code=="en"){ ?> id="ar" <?php } ?>  <?php if($val->language_code=="ar"){ ?> id="en" <?php } ?>title="<?php echo strtoupper($val->language_code); ?>"><?php echo strtoupper($val->language_code); ?></a></h3>
													<?php } ?>  
													<?php } } ?>   
								</div>
								<!-- cart responsive end buttons -->
                            </div>
                            <!-- /.container-fluid -->
                        </nav>
                        <!-- responsive nav menu start -->
                        <div class="nav-container">
                            <ul class="nav-menu menu">
							<?php
							if(Session::has('user_id')) {?>
								<li class="menu-item">
								<a title="{{Session::get('social_title').Session::get('name')}} " class="menu-link" id="open_drop_hed" href="{{ URL::to('/profile') }}" title="@lang('messages.Login')"><i class="glyph-icon flaticon-social"></i>
								{{Session::get('social_title').ucfirst(Session::get('name'))}} 
								</a>
								</li>
							<?php } else { ?>
								<li class="menu-item"> <a href="javascript:;" class="menu-link" data-toggle="modal" data-target="#myModal2" title="@lang('messages.Login')">@lang('messages.Login')</a> </li>
								<li class="menu-item"> <a href="javascript:;" class="menu-link" title="@lang('messages.Signup')" data-toggle="modal" data-target="#myModal">@lang('messages.Signup')</a> </li>
							<?php } ?>
                                <li class="menu-item"> <a href="{{ URL::to('/offer') }}" class="menu-link">@lang('messages.Offer')</a> </li>
                                <li class="menu-item"> <a href="{{ URL::to('/contact-us') }}" class="menu-link">@lang('messages.Help')</a> </li>
                            </ul>
                        </div>
                        <!-- responsive nav menu end -->
                    </div>
                </div>
                </div>
            </div>
        </div>
    </header>
	<div class="container">
		<?php /*<div class="container_inner">
			<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}alert info page</div>
		@if (Session::has('message'))
			<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
		@endif
		</div>*/?>
		
	</div>
	<?php /*<div class="alert alert-info">
		<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>
	</div> */?>


{!! Form::open(['method' => 'POST', 'route' => 'changelocale', 'class' => 'form-inline navbar-select' ,'id' => 'form-inline']) !!}
			<input type="hidden" name="locale" class="locale">
			{!! Form::close() !!}
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="{{ URL::asset('assets/front/'.$general->theme.'/js/jquery.min.js') }}"></script>
<script src="{{ URL::asset('assets/front/'.$general->theme.'/js/bootstrap.min.js') }}"></script>
<script src="{{ URL::asset('assets/front/'.$general->theme.'/js/waypoints.min.js') }}"></script>
<script src="{{ URL::asset('assets/front/'.$general->theme.'/js/select2.min.js') }}"></script>
<script src="{{ URL::asset('assets/front/'.$general->theme.'/js/owl.carousel.min.js') }}"></script>
<script src="{{ URL::asset('assets/front/'.$general->theme.'/js/custom.js') }}"></script>
<script src="{{ URL::asset('assets/front/'.$general->theme.'/js/toastr.min.js') }}"></script>

<script type="text/javascript">
 $(document).ready( function() {
		
		//toastr.success('Have fun storming the castle!', 'Miracle Max Says')
		$('.languageselection').click(function(){
			var locale= $(this).attr("id");
			$('.locale').val(locale);
			$( "#form-inline" ).submit();
		});
		
		toastr.options = {
		  "closeButton": false,
		  "debug": false,
		  "newestOnTop": false,
		  "progressBar": false,
		  "positionClass": "toast-top-right",
		  "preventDuplicates": false,
		  "onclick": null,
		  "showDuration": "300",
		  "hideDuration": "1000",
		  "timeOut": "7000",
		  "extendedTimeOut": "1000",
		  "showEasing": "swing",
		  "hideEasing": "linear",
		  "showMethod": "fadeIn",
		  "hideMethod": "fadeOut"
		}
		
 });
 $(function() {
            var html = $('html, body'),
                navContainer = $('.nav-container'),
                navToggle = $('.nav-toggle'),
                navDropdownToggle = $('.has-dropdown');
            // Nav toggle
            navToggle.on('click', function(e) {
                var $this = $(this);
                e.preventDefault();
                $this.toggleClass('is-active');
                navContainer.toggleClass('is-visible');
                html.toggleClass('nav-open');
            });
            // Nav dropdown toggle
            navDropdownToggle.on('click', function() {
                var $this = $(this);
                $this.toggleClass('is-active').children('ul').toggleClass('is-visible');
            });
            // Prevent click events from firing on children of navDropdownToggle
            navDropdownToggle.on('click', '*', function(e) {
                e.stopPropagation();
            });
        });
 <!-- check ,redio button js end-->
        <!-- responsive menu script -->
        $(document).ready( function() {
            $('#location').on('change', function() {
				if(this.value){
					var url = "<?php echo  URL::to('/store') ?>";
					var location_url = this.value;
					window.location.assign(url+'?location='+location_url+'&city=')
				}
			});
        });
        <!-- responsive menu script -->
</script>
@if (Session::has('message'))
	<?php 
		$succ_msg = preg_replace("/\r\n|\r|\n/",'<br>',Session::get('message'));
	?>
	<script>
		toastr.info('<?php echo $succ_msg;?>');
	</script>
@endif
@if (Session::has('message-success'))
	<?php 
		$succ_msg = preg_replace("/\r\n|\r|\n/",'<br>',Session::get('message-success'));
	?>
	<script>
		toastr.success('<?php echo $succ_msg;?>');
	</script>
@endif

@if (Session::has('message-failure'))
	<?php 
		
		$succ_msg = preg_replace("/\r\n|\r|\n/",'<br>',Session::get('message-failure'));
	?>
	<script>
		toastr.error('<?php echo $succ_msg;?>');
	</script>
@endif

<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '115633832231279',
      xfbml      : true,
      version    : 'v2.6'
    });
  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "//connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));
</script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<div id="fadpage" style="display:none;"></div>
<script src="{{ URL::asset('assets/front/'.$general->theme.'/js/front.js') }}"></script>
