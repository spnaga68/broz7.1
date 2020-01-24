    @extends('layouts.app')
	@section('content')

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
							<img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/faq/questions.png');?>" class=bell col="g" alt="Account settings">
							<p>{{ ucfirst($value->title) }}</p>
						</a>
					</div>
				</div>
			@endif
			@if($i == 1)
				<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12">
					<div class="faq_list1">
							<a href="<?php echo URL::to('/cms/'.$value->url_index.''); ?>" title="{{ ucfirst($value->title) }}">
								<img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/faq/delivery.png');?>" class=bell col="g" alt="Delivery">
								<p> {{ ucfirst($value->title) }}</p>
							</a>
					</div>
				</div>
			@endif
			@if($i == 2)
				<div class="col-md-4 col-lg-4 col-sm-6 col-xs-12 border_right0">
				<div class="faq_list1">
				<a href="<?php echo URL::to('/cms/'.$value->url_index.''); ?>" title="{{ ucfirst($value->title) }}">
				<img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/faq/pay.png');?>" class=bell col="g" alt="Payment">
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
    @endsection
