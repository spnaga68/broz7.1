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
      <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
      <!--320-->
      <meta name="apple-mobile-web-app-capable" content="yes">
      <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
      <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
      <title>{{ucfirst(Session::get("general")->site_name.' - '.Session::get("general")->site_description)}}</title>
     
      <?php $general = Session::get("general"); $social = Session::get("social");$general_site = Session::get("general_site"); $email = Session::get("configemail"); $languages = Session::get("languages"); ?>
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
      <link href="{{ URL::asset('assets/front/'.$general->theme.'/css/animate.css') }}" rel="stylesheet" />
	 <link href="{{ URL::asset('assets/front/'.$general->theme.'/css/smoothproducts.css') }}" media="all" rel="stylesheet" type="text/css" />
	 <link href="{{ URL::asset('assets/front/'.$general->theme.'/css/overlay.css') }}" rel="stylesheet" />
	 <link href="{{ URL::asset('assets/front/'.$general->theme.'/css/res_menu.css') }}" rel="stylesheet" />
	  
      <link href="{{ URL::asset('assets/front/'.$general->theme.'/css/owl.carousel.css') }}" rel="stylesheet" />
      <link href="{{ URL::asset('assets/front/'.$general->theme.'/css/responsive.css') }}" rel="stylesheet" />
      <link href="{{ URL::asset('assets/front/'.$general->theme.'/css/easy-responsive-tabs.css') }}" rel="stylesheet" />
      <link href="{{ URL::asset('assets/front/'.$general->theme.'/css/toastr.css') }}" rel="stylesheet" />
      <style type="text/css">
         <?php if(request()->path() == 'driverabout-us' || request()->path() == 'customerabout-us'|| request()->path() == 'customerterms-condition'|| request()->path() == 'driverterms-condition'|| request()->path() == 'customer_privacy_policy.html'){  ?>
          .main_head{
            display: none;
          <?php }
          ?> 
 
      </style>
   </head>
   <body <?php if(App::getLocale() == 'ar'){ echo "dir='rtl'"; }?>>
      <header class="heder_position home_drop main_head">
        <div class="headerContainerWrapper">
            <div class="headerContainerShadow">
                <div class="container">
                <div class="row">
                    <div class="responsive_common">
          					  <div class="navbar-header">
                        <div class="toggle-button" id="toggle">
                            <span class="bar top"></span>
                            <span class="bar middle"></span>
                            <span class="bar bottom"></span>
                        </div>
                      </div>
                      <nav class="navbar navbar-default">
                        <div class="header_common">
                          <!-- Brand and toggle get grouped for better mobile display -->
                          <div class="col-md-5 col-sm-4 col-xs-6 logo_responsive_sec">
                            <div class="navbarlogo_sections">
                              <a class="navbar-brand" href="<?php echo url('/'); ?>" title="{{ $general->site_name }}">
                  					   <img src="<?php echo url('/assets/front/'.$general->theme.'/images/logo/front_logo.png?'.time()); ?>" title="{{ $general->site_name }}" alt="{{ $general->site_name }}" >
                  						</a>

                            </div>
                          </div>
                          <!-- Collect the nav links, forms, and other content for toggling -->
                          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                            <div class="col-md-7 col-sm-6 col-xs-6 responsive_hidde">
                              <ul class="nav navbar-nav navbar-right">
      	                       <?php $cdata = get_cart_count();?>
                                 <?php $cart_item = 0;if(count($cdata)>0){
                                    $cart_item = $cdata[0]->cart_count;}?>
                                 <li ><a <?php if(!Session::get('user_id')){ ?>  href="javascript:;" data-toggle="modal" data-target="#myModal2"  <?php } else {  ?> href="{{url('cart')}}" <?php } ?> title="@lang('messages.Cart')">@lang('messages.Cart')<span class="cart_total_count"> </span></a></li>
        											   <?php
          												if(Session::has('user_id')) {?>
          												<li>
          													<a  id="open_drop_hed" href="{{ URL::to('/profile') }}" title="@lang('messages.Login')"><i class="glyph-icon flaticon-social"></i>
          													{{Session::get('social_title').ucfirst(Session::get('name'))}} 
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
												
        												<li><a href="javascript:;" data-toggle="modal" data-target="#myModal2" title="@lang('messages.Login')">@lang('messages.Login')</a>
        															</li>
        												<li><a href="javascript:;" title="@lang('messages.Signup')" data-toggle="modal" data-target="#myModal">@lang('messages.Signup')</a>
        															</li>
        												<?php } ?>
        												<li><a href="{{ URL::to('/offer') }}" title="@lang('messages.Offer')">@lang('messages.Offer')</a>
        												</li>
        												<li><a href="{{ URL::to('/contact-us') }}" title="@lang('messages.Help')">@lang('messages.Help')</a>
        												</li>
			
                          
          											<?php /*	<li>
          												
          													<div class="language_selection">
          														 <div class="ar_bg_headers"> <i class="ar_eng_lang">	<?php if(count($languages)){ 
                               foreach($languages as $key => $val){ ?>
                               <?php if(App::getLocale()!=$val->language_code){  ?>
                               <a style="cursor:pointer;" class="languageselection" <?php if($val->language_code == "ar"){ ?> id="ar" <?php } ?>  <?php if($val->language_code=="en"){ ?> id="en" <?php } ?>title="<?php echo strtoupper($val->language_code); ?>"><?php echo strtoupper($val->language_code); ?></a>
                               <?php } ?>  
                               <?php } } ?> </i>
                    				      </div> 
                    													</div>
                    												</li> */ ?>
										          </ul>

                            </div>
                          </div>
                                <!-- /.navbar-collapse -->
            								<!-- cart responsive buttons-->
            								<?php /*	<div class="responsive_cart">
            								<?php if(count($languages)){ 
            													foreach($languages as $key => $val){ ?>
            													<?php if(App::getLocale()==$val->language_code){  ?>
            															  <h3><a style="cursor:pointer;"  class="languageselection language_ar"  <?php if($val->language_code=="en"){ ?> id="ar" <?php } ?>  <?php if($val->language_code=="ar"){ ?> id="en" <?php } ?>title="<?php echo strtoupper($val->language_code); ?>"><?php echo strtoupper($val->language_code); ?></a></h3>
            													<?php } ?>  
            													<?php } } ?>   
            								</div> */?>
            								<!-- cart responsive end buttons -->
                            </div>
                            <!-- /.container-fluid -->
                      </nav>
                     
                    </div>
                </div>
                </div>
            </div>
        </div>
      </header>
	        <div class="overlay" id="overlay">
   <!-- responsive nav menu start -->
                        <div>
                            <ul>
							<?php
							if(Session::has('user_id')) {?>
								<li class="menu-item">
								<a title="{{Session::get('social_title').Session::get('name')}} " class="menu-link" id="open_drop_hed" href="{{ URL::to('/profile') }}" title="@lang('messages.Login')"><i class="glyph-icon flaticon-social"></i>
								
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
      <div class="container">
<input type="hidden" id="refreshed" value="no">
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
	  <script src="{{ URL::asset('assets/front/'.$general->theme.'/js/smoothproducts.min.js') }}"></script>
      <script src="{{ URL::asset('assets/front/'.$general->theme.'/js/custom.js') }}"></script>
      <script src="{{ URL::asset('assets/front/'.$general->theme.'/js/toastr.min.js') }}"></script>
     

<script type="text/javascript">
       onload=function(){
         var e=document.getElementById("refreshed");
         if(e.value=="no")e.value="yes";
               else{e.value="no";location.reload();}
           }

	 $('select').select2();

				
        // get header height (without border)
        var getHeaderHeight = $('.headerContainerWrapper').outerHeight();

        // border height value (make sure to be the same as in your css)
        var borderAmount = 2;

        // shadow radius number (make sure to be the same as in your css)
        var shadowAmount = 30;

        // init variable for last scroll position
        var lastScrollPosition = 0;

        // set negative top position to create the animated header effect
        $('.headerContainerWrapper').css('top', '-' + (getHeaderHeight + shadowAmount + borderAmount) + 'px');

        $(window).scroll(function() {
            var currentScrollPosition = $(window).scrollTop();
            if ($(window).scrollTop() > 2 * (getHeaderHeight + shadowAmount + borderAmount)) {
                $('body').addClass('scrollActive').css('padding-top', getHeaderHeight);
                $('.headerContainerWrapper').css('top', 0);
                if (currentScrollPosition < lastScrollPosition) {
                    $('.headerContainerWrapper').css('top', '-' + (getHeaderHeight + shadowAmount + borderAmount) + 'px');
                }
                lastScrollPosition = currentScrollPosition;
            } else {
                $('body').removeClass('scrollActive').css('padding-top', 0);
            }
        });
        function toggleChevron(e) {
			alert('in'); 
            $(e.target)
                .prev('.panel-heading')
                .find("i.indicator")
                .toggleClass('glyphicon-chevron-down glyphicon-chevron-up');
        }
       
    </script>
	 <script type="text/javascript">
		 
		  
         $(document).ready( function() {
            var cart_count = '<?php echo $cart_item;?>'
         $('.cart_total_count').html('('+cart_count+')');
         
          $(".toogle_play_cont").css('display','none');
         <!-- responsive nav cat -->
         $("#id_open_this").click(function(){
         $(".toogle_play_cont").slideToggle();
         });
         <!-- responsive nav cat -->
         
          $(".wtf-menu_new").css('display','none');
         <!-- responsive nav cat -->
         $("#id_open_this2").click(function(){
         $(".wtf-menu_new").slideToggle();
         });
         <!-- responsive nav cat -->
         
         
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
         	  "preventDuplicates": true,
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
			
			
			$('#toggle').click(function() {
    $(this).toggleClass('toggle-active');
    $('#overlay').toggleClass('nav-active');
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
         	$('#open_drop_me').click(function() {
         				$('.toogle_drop_menu').toggle();
         				if ($("#open_drop_me").hasClass("buttons_active")) { 
         					$( "#open_drop_me" ).removeClass( "buttons_active" );
         				} else { 
         					$( "#open_drop_me" ).addClass( "buttons_active" );
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
