    @extends('layouts.app')
	@section('content')

    <!-- content start -->
    <section class="about_us_bg">
<div class="container">
<div class="banner_abt">
<h1>Oddappz</h1>
<p>Donec eu metus pharetra, cursus enim et, malesuada lorem. Phasellus ultricies venenatis sapien nec pulvinar. Suspendisse euismod ipsum ut arcu viverra facilisis.</p>
</div>
</div>	
</section>
<section class="who_we_are">
<div class="container">
<h2>@lang('messages.Who we are')</h2>
<p>@lang('messages.Any information gathered by Oddappz Inc. is used to customize the Postmates Inc. experience, and is not shared or sold to any third parties, except as expressly provided for in this Privacy Policy and our Terms of Use.')</p>
<p>@lang('messages.Oddappz Inc. reserves the right to view private sites and private posts for the purposes of fixing issues, and to look for copyrighted or other inappropriate material. This will only be done as necessary and these posts will never be shared by anyone else.')</p>
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
    @endsection
