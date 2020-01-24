
<html lang="en" ng-app="app" ng-controller="titleCtrl">
   <head>
     
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
     <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,500,600,700" rel="stylesheet"> 
     
     
     <?php if($languages==2) {  ?>
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
	 
	 <link href="{{ URL::asset('assets/front/'.$general->theme.'/css/res_menu.css') }}" rel="stylesheet" />
	  
      <link href="{{ URL::asset('assets/front/'.$general->theme.'/css/owl.carousel.css') }}" rel="stylesheet" />
      <link href="{{ URL::asset('assets/front/'.$general->theme.'/css/responsive.css') }}" rel="stylesheet" />
      <link href="{{ URL::asset('assets/front/'.$general->theme.'/css/easy-responsive-tabs.css') }}" rel="stylesheet" />
      <link href="{{ URL::asset('assets/front/'.$general->theme.'/css/toastr.css') }}" rel="stylesheet" />
   </head>

   <body <?php if($languages==2){ echo "dir='rtl'"; }?>>
      <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
      <script src="{{ URL::asset('assets/front/'.$general->theme.'/js/jquery.min.js') }}"></script>
      <script src="{{ URL::asset('assets/front/'.$general->theme.'/js/bootstrap.min.js') }}"></script>
      <script src="{{ URL::asset('assets/front/'.$general->theme.'/js/waypoints.min.js') }}"></script>
      <script src="{{ URL::asset('assets/front/'.$general->theme.'/js/owl.carousel.min.js') }}"></script>
	  <script src="{{ URL::asset('assets/front/'.$general->theme.'/js/custom.js') }}"></script>
      <script type="text/javascript">
         $(document).ready( function() {
         
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
         <!-- check ,redio button js end-->
                <!-- responsive menu script -->
      </script>
      <!-- Include all compiled plugins (below), or include individual files as needed -->
      <div id="fadpage" style="display:none;"></div>
      <script src="{{ URL::asset('assets/front/'.$general->theme.'/js/front.js') }}"></script>


    <!-- content start -->
    <!-- container start -->
    <section class="inner_cms_section">
        <div class="container">
            <div class="cms_inner_cont_mob">
				
                @if(count($cmsinfo) > 0 )
                    <p><?php echo $cmsinfo->content;?></p>
                @else
                    <h1>@lang('messages.No data found')</h1>
                @endif
            </div>
        </div>
    </section>
