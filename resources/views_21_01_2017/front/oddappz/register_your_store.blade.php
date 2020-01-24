    @extends('layouts.app')
	@section('content')

    <section class="list_my_store">
	<div class="container">
	<div class="reg_your_store">
	<div class="reg_login">
	<h2>@lang('messages.Register your store')</h2>
	<p>@lang('messages.Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin pharetra pellentesque tincidunt. Vestibulum vulputate odio ultricies.')</p>
	<a href="#" title="Not yet registered?">@lang('messages.Not yet registered?')</a>
	<div class="but_reg">
	<button class="btn btn-warning" type="button"  title="@lang('messages.Sign up with us')" data-toggle="modal" data-target="#store_register"> @lang('messages.Sign up with us')</button>
	<?php /** <label class="label_check" for="checkbox-01">
                                    <input name="sample-checkbox-01" id="checkbox-01" value="1" type="checkbox" checked />@lang('messages.We provide printed bills')</label> **/ ?>
	</div>
	</div>
	</div>
	</div>
	</section>	
	
		<section class="how_it_works">
	<div class="container">
	<div class="how_it_wors_inner">
<h1>@lang('messages.Selling through Oddappz is as easy as 1.2.3...')</h1>
<p>@lang('messages.Integer ullamcorper nulla a mi fringilla scelerisque phasellus pharetra ante ut') <br/>@lang('messages.finibus varius.')</p>


<div class="how_it_info">
<div class="steps_one">
<h2>@lang('messages.Step 01')</h2>
<p>@lang('messages.Send us your personal')<br/>
@lang('messages.and store details')</p>
</div>
<div class="steps_one two">
<h2>@lang('messages.Step 03')</h2>
<p>@lang('messages.Set up your inventory')<br/>
@lang('messages.and start selling')</p>
</div>
<div class="steps_one three">
<h2>@lang('messages.Step 02')</h2>
<p>@lang('messages.Set up your inventory')<br/>
@lang('messages.and start selling')</p>
</div>
<img src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/how_it_works.png'); ?>" alt="how it works"><br/>
	<a href="{{ URL::to('/cms/how-it-works') }}" title="@lang('messages.How it works')">@lang('messages.How it works')</a>
</div>

	</div>
	</div>
	</section>
	
	<section class="store_item_list">
	<div class="container">
	<div class="row">
<div class="cites_count">
<div class="col-md-4 border-right">
<div class="cites_list">
<div class="list_icons">
<i class="glyph-icon flaticon-skyscrapers"></i>
</div>
<h3>19</h3>
<p>@lang('messages.Cities across India')</p>
</div>
</div>
<div class="col-md-4 border-right">
<div class="cites_list">
<div class="list_icons">
<i class="glyph-icon flaticon-commerce"></i>
</div>
<h3>4000+</h3>
<p>@lang('messages.Daily orders')</p>
</div>
</div>
<div class="col-md-4">
<div class="cites_list">
<div class="list_icons">
<i class="glyph-icon flaticon-website"></i>
</div>
<h3>5000+</h3>
<p>@lang('messages.Stores live here')</p>
</div>
</div>
</div>
</div>
	</div>
	</section>
    <!-- content end -->
      <script type="text/javascript">
	/* wol corsol slider start*/
		$('.owl-carousel').owlCarousel({
    loop:true,
    margin:10,
	autoplay:true,
	autoplayTimeout: 5000,
    nav:true,
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
