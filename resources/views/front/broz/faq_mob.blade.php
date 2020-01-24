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


<section class="faq_sections">
	<div class="container">
		<div class="container_teams">
			<h2>@lang('messages.Help Center')</h2>
			<p>@lang('messages.Click on the appropriate section to answer your questions.')</p>
		</div>
		
	 <div class="container_teams_bottom">
	<?php $cms_list = getCms_faq(); $i = 0;?>
	@if(count($cms_list) > 0)
		@foreach($cms_list as $key => $value)
			@if($i == 0)
				<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12">
					<div class="faq_list1">
						<a href="<?php echo URL::to('/cms/'.$value->url_index); ?>" title="{{ ucfirst($value->title) }}">
							<img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/faq/payable.png');?>" class=bell col="g" alt="Account settings">
							<p>{{ ucfirst($value->title) }}</p>
						</a>
					</div>
				</div>
			@endif
			@if($i == 1)
				<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12">
					<div class="faq_list1">
						<div class="faq_list1_abs">
							<a href="<?php echo URL::to('/cms/'.$value->url_index.''); ?>" title="{{ ucfirst($value->title) }}">
								<img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/faq/general.png');?>" class=bell col="g" alt="Add a request">
								<p> {{ ucfirst($value->title) }}</p>
							</a>
						</div>
					</div>
				</div>
			@endif
			@if($i == 2)
				<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12 border_right0">
				<div class="faq_list1">
				<a href="<?php echo URL::to('/cms/'.$value->url_index.''); ?>" title="{{ ucfirst($value->title) }}">
				<img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/faq/request.png');?>" class=bell col="g" alt="How to Use Mint">
				<p> {{ ucfirst($value->title) }}</p>
				</a>
				</div>
				</div>
			@endif
			@if($i == 3)
				<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12 border_right1 border_right1">
				<div class="faq_list1">
				<a href="<?php echo URL::to('/cms/'.$value->url_index.''); ?>" title="{{ ucfirst($value->title) }}">
				<img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/faq/security.png');?>" class=bell col="g" alt="">
				<p> {{ ucfirst($value->title) }}</p>
				</a>
				</div>
				</div>
			@endif
			@if($i == 4)		
				<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12 border_right1 border_right1 responsive_hidde">
				<div class="faq_list1">
				<a href="#" title="The prices">
				<p></p>
				</a>
				</div>
				</div>
				<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12 border_right0 border_right1">
				<div class="faq_list1">
				<a href="<?php echo URL::to('/cms/'.$value->url_index.''); ?>" title="{{ ucfirst($value->title) }}">
				<img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/faq/prices.png');?>" class=bell col="g" alt="">
				<p> {{ ucfirst($value->title) }}</p>
				</a>
				</div>
				</div>
		    @endif
		      <?php $i++; ?>
	   @endforeach
   @endif
    
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
  
