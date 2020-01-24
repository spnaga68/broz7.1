    @extends('layouts.app')
	@section('content')

    <!-- content start -->
    <section class="about_us_bg">
<div class="container">
<div class="banner_abt_sec">
<h1>@lang('messages.Oddappz')</h1>
<p>@lang('messages.The best online shops in India')</p>
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
<h2>@lang('messages.Who is oddappz?')</h2>
<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>
<p>@Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>


</div>
</div>
<div class="col-md-6 col-lg-6 col-sm-6 col-xs-12">
<div class="left_who_is_sec">
	<?php $locale = Session::get('locale', Config::get('app.locale')); ?>
	 <?php if ($locale == 'en') {?>
<img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/arabty_en.png');?>" alt="">
 <?php  } else {?>
	 <img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/process_rtg.png');?>" alt="">
	  <?php } ?>
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
    @endsection
