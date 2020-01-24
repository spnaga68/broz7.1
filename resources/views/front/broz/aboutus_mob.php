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
	 
	 <link href="{{ URL::asset('assets/front/'.$general->theme.'/css/res_menu.css') }}" rel="stylesheet" />
	  
      <link href="{{ URL::asset('assets/front/'.$general->theme.'/css/owl.carousel.css') }}" rel="stylesheet" />
      <link href="{{ URL::asset('assets/front/'.$general->theme.'/css/responsive.css') }}" rel="stylesheet" />
      <link href="{{ URL::asset('assets/front/'.$general->theme.'/css/easy-responsive-tabs.css') }}" rel="stylesheet" />
      <link href="{{ URL::asset('assets/front/'.$general->theme.'/css/toastr.css') }}" rel="stylesheet" />
   </head>
   <body <?php if(App::getLocale() == 'ar'){ echo "dir='rtl'"; }?>>
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
    <section class="about_us_bg">
<div class="container">
<div class="banner_abt_sec">
<h1>@lang('messages.Arbaty')</h1>
<p>@lang('messages.The best online shops in saudi arabia')</p>
</div>
</div>	
</section>
<section class="who_we_are">
<div class="container">
<h2>@lang('messages.What we promise')</h2>
<div class="col-md-3 col-lg-3 col-sm-6 col-xs-12">
<div class="listed_sect_abt">
 <img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/process1.png');?>" alt="">
 <p>@lang('messages.We bring best shops') </p>
</div>
</div>
<div class="col-md-3 col-lg-3 col-sm-6 col-xs-12">
<div class="listed_sect_abt">
 <img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/process2.png');?>" alt="">
 <p>@lang('messages.We meet your needs')</p>
</div>
</div>
<div class="col-md-3 col-lg-3 col-sm-6 col-xs-12">
<div class="listed_sect_abt">
 <img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/process3.png');?>" alt="">
 <p>@lang('messages.We expect your orders') </p>
</div>
</div>
<div class="col-md-3 col-lg-3 col-sm-6 col-xs-12">
<div class="listed_sect_abt">
 <img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/process4.png');?>" alt="">
 <p>@lang('messages.We will deliver on time')</p>
</div>
</div>
<div class="abt_top_sections">
<div class="col-md-6 col-lg-6 col-sm-6 col-xs-12">
<div class="left_who_is_sec">
<h2>@lang('messages.Who is arabty?')</h2>
<p> @lang('messages.Arabty is a smart application that came to meet the demands of morden life and the cravings of customers to buy their needs online in a fast and secure manner. The application provide all users the modern and simple methods to request their orders by connecting them to a range of shops and grocery stores.')</p>
<p>@lang('messages.Arabty provides customers with access to all products, prices and give them benift from offers and discounts,and then requests their products to home as soon as possible and according to the highest level safty. Arabty provide you a varity of features that make you live shopping experience like you are in the store.')</p>
<p>@lang('messages.Access to a wide range of  shops and groceries through one application. Determine the nearest stores to your home , and find out the opening and closing times.')</p>
<p>
@lang('messages.Access all products in the stores, Including vegetables,fruits,meat and fish Discover all offers,discounts and new items on the market. Organize your list of purchases,adjust them,and calcualte total expenditure.')	
</p>


</div>
</div>
<div class="col-md-6 col-lg-6 col-sm-6 col-xs-12">
<div class="left_who_is_sec">
<img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/process_rtg.png');?>" alt="">
</div>
</div>
</div>

</div>
</section>



<section class="who_we_are_bottom">
<div class="container">
<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12">
<div class="company_det_sec">
<span class="top_icons" id="bellLogo" onmouseover="hvr(this, 'in')" onmouseleave="hvr(this, 'out')">
<img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/eye_icon.png');?>" class=bell col="g" alt="">
  <img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/eye_hover.png');?>" class=bell style="display:none" col="b" alt="">
</span>
<h3>@lang('messages.Our Vision')</h3>
<label>@lang('messages.Since the launch of our ambitious project, we have set a long-term vision to lead the way in smart applications used in kingdom,and become the first reference for all those who want to shop online in simple and safe way.')</label>
</div>
</div>
<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12">
<div class="company_det_sec">
<span class="top_icons" id="bellLogo" onmouseover="hvr(this, 'in')" onmouseleave="hvr(this, 'out')">
<img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/msg_icon.png');?>" class=bell col="g" alt="">
  <img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/mag_hover.png');?>" class=bell style="display:none" col="b" alt="">
 
</span>
<h3>@lang('messages.Our Message')</h3>
<label>@lang('messages.We carry a social message. Through which we seek to contribute to the development  of lifestyles within saudi society by spreading a new culture of purchase based on modern technological methods.')</label>
</div>
</div>
<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12">
<div class="company_det_sec">

<span class="top_icons" id="bellLogo" onmouseover="hvr(this, 'in')" onmouseleave="hvr(this, 'out')">
<img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/goal_icon.png');?>" class=bell col="g" alt="">
  <img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/goal_hover.png');?>" class=bell style="display:none" col="b" alt="">
</span>
<h3>@lang('messages.Our Goal')</h3>
<label>@lang('messages.Customer is our highest goal and our focus provide the best quality for our customers, honesty in provideing information to our customers and innovation and support the initiative in our team.')</label>
</div>
</div>
</div>
</section>
<section class="teamp_photos">
<div class="container">
<div class="container_teams">
<a href="/contact-us" class="btn btn-default">@lang('messages.Join Us')</a>
<h2>@lang('messages.OUR TEAM')</h2>
<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,</p>
</div>
<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12">
<div class="container_team">
 <img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/ceo.jpg');?>" alt="">
  <div class="overlay">
    <div class="text_team">
	<h3>Muniraj</h3>
<h4>CEO &amp; Founder</h4>
<p>Brings in the passion of entrepreneurship and 12 years</p>
<div class="social_share">
                <ul>
                   
                    <li><a href="https://www.facebook.com/nextbraintech" target="_blank" title="Facebook"><i class="glyph-icon flaticon-facebook-logo"></i></a></li>
                    <li><a href="https://twitter.com/nextbrainitech" target="_blank" title="Twitter"><i class="glyph-icon flaticon-twitter-logo-silhouette"></i></a></li>
                    <li><a href="https://www.linkedin.com/company/nextbrain-technologies-private-limited" target="_blank" title="linkein"><i class="glyph-icon flaticon-linkedin-logo"></i></a></li>
                </ul>
            </div>
	</div>
  </div>
</div>
</div>
<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12">
<div class="container_team">
 <img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/cto.jpg');?>" alt="">
  <div class="overlay">
    <div class="text_team">
	<h3>Shivakumar</h3>
<h4>Founder</h4>
<p>Brings in the passion of entrepreneurship and 12 years</p>
<div class="social_share">
                <ul>
                   
                    <li><a href="https://www.facebook.com/nextbraintech" target="_blank" title="Facebook"><i class="glyph-icon flaticon-facebook-logo"></i></a></li>
                    <li><a href="https://twitter.com/nextbrainitech" target="_blank" title="Twitter"><i class="glyph-icon flaticon-twitter-logo-silhouette"></i></a></li>
                    <li><a href="https://www.linkedin.com/company/nextbrain-technologies-private-limited" target="_blank" title="linkein"><i class="glyph-icon flaticon-linkedin-logo"></i></a></li>
                </ul>
            </div>
	</div>
  </div>
</div>
</div>
<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12">
<div class="container_team">
 <img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/noimages.jpg');?>" alt="">
   <div class="overlay">
    <div class="text_team">
	<h3>Sathikumar</h3>
<h4>CTO &amp; Founder</h4>
<p>Brings in the passion of entrepreneurship and 12 years</p>
<div class="social_share">
                <ul>
                   
                    <li><a href="https://www.facebook.com/nextbraintech" target="_blank" title="Facebook"><i class="glyph-icon flaticon-facebook-logo"></i></a></li>
                    <li><a href="https://twitter.com/nextbrainitech" target="_blank" title="Twitter"><i class="glyph-icon flaticon-twitter-logo-silhouette"></i></a></li>
                    <li><a href="https://www.linkedin.com/company/nextbrain-technologies-private-limited" target="_blank" title="linkein"><i class="glyph-icon flaticon-linkedin-logo"></i></a></li>
                </ul>
            </div>
	</div>
  </div>
</div>
</div>
</div>
</section>

    <!-- content end -->
      <script type="text/javascript">
		   <!-- responsive menu script -->

        $(function() {
			   $(function() {
    $('img[data-alt-src]').each(function() { 
        new Image().src = $(this).data('alt-src'); 
    }).hover(); 
});
$(function () {
    $('img.xyz').hover();
});

        });
		  $('select').select2();
		  <?php if(App::getLocale()=='ar') {  ?>
			var ortl = true;
		<?php }else {  ?>
			var ortl = false;
			<?php } ?>
	/* wol corsol slider start*/
		$('.owl-carousel').owlCarousel({
    loop:true,
    margin:10,
	autoplay:true,
	autoplayTimeout: 5000,
    nav:true,
    rtl:ortl,
    responsive:{
        0:{
            items:1
        },
        642:{
            items:2
        },
        1000:{
            items:4
        }
    }
})
</script>
<script>
function hvr(dom, action)
{
    if (action == 'in')
    {
        $(dom).find("[col=g]").css("display", "none");
        $(dom).find("[col=b]").css("display", "inline-block");
    }

    else
    {
        $(dom).find("[col=b]").css("display", "none");
        $(dom).find("[col=g]").css("display", "inline-block");
    }
}
</script>
  
