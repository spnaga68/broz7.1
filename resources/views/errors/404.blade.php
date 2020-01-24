    @extends('layouts.app')
	@section('content')
<link href="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/css/animations.css');?>" rel="stylesheet" />
    <!-- content start -->
		 <section class="store_list">
        <div class="container">
<div class="four_not_error">
<img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/404.png');?>" alt="404">
<h1>@lang('messages.Page not found')</h1>
<h3>@lang('messages.The page your looking for is temporarily unavailable or has') <br> @lang('messages.been removed.')</h3>
<a class="hvr-ripple-out" href="{{ URL::to('/') }}" title="Home">@lang('messages.Go to Home')</a>
</div>
        </div>
    </section>
	<script type="text/javascript" src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/js/css3-animate-it.js');?>"></script>
     <script type="text/javascript">
 $(document).ready( function() {
	$('.header_outer').addClass('portfolio_header');
	$('.get_in_touch').hide();
 });
</script>
    @endsection
