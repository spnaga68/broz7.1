    @extends('layouts.app')
    @section('content')

    <!-- content start -->
    <section class="about_us_bg">
<div class="container">
<div class="banner_abt">
<h1>Oddappz</h1>
<p>@lang('messages.Donec eu metus pharetra, cursus enim et, malesuada lorem. Phasellus ultricies venenatis sapien nec pulvinar. Suspendisse euismod ipsum ut arcu viverra facilisis.')</p>
</div>
</div>    
</section>
<section class="who_we_are">
<div class="container">
<h2>@lang('messages.Who we are')</h2>
<p>@lang('messages.Any information gathered by :site_name Inc. is used to customize the Postmates Inc. experience, and is not shared or sold to any third parties, except as expressly provided for in this Privacy Policy and our Terms of Use.', ['site_name' => Session::get("general")->site_name])</p>
<p>@lang('messages.:site_name Inc. reserves the right to view private sites and private posts for the purposes of fixing issues, and to look for copyrighted or other inappropriate material. This will only be done as necessary and these posts will never be shared by anyone else.', ['site_name' => Session::get("general")->site_name])</p>
</div>
</section>
<?php /*<section class="store_item_list">
<div class="container">
<div class="bat_list">
<div class="col-md-6">
<div class="right_desc">
<h3>1000+</h3>
<p>@lang('messages.People have joined the :site_name team in the past six months', ['site_name' => Session::get("general")->site_name])</p>
</div>
</div>
<div class="col-md-6">
<div class="left_abt_img">
<img src="{{ URL::asset('assets/front/'.Session::get('general')->theme.'/images/abt_1.png') }}"  alt="">
</div>
</div>
</div>
<div class="bat_list">
<div class="col-md-6">
<div class="right_desc">
<h3>4X</h3>
<p>@lang('messages.Rate of growth in our monthly user base')</p>
</div>
</div>
<div class="col-md-6">
<div class="left_abt_img">
<img src="{{ URL::asset('assets/front/'.Session::get('general')->theme.'/images/abt_2.png') }}" alt="">
</div>
</div>
</div>
<div class="bat_list">
<div class="col-md-6">
<div class="right_desc">
<h3>15 days</h3>
<p>@lang('messages.Time taken to launch in 15 cities across Kuwait')</p>
</div>
</div>
<div class="col-md-6">
<div class="left_abt_img">
<img src="{{ URL::asset('assets/front/'.Session::get('general')->theme.'/images/abt_3.png') }}" alt="">
</div>
</div>
</div>
<div class="bat_list">
<div class="col-md-6">
<div class="right_desc">
<h3>2000+</h3>
<p>@lang('messages.App downloads in iOS & Android')</p>
</div>
</div>
<div class="col-md-6">
<div class="left_abt_img">
<img src="{{ URL::asset('assets/front/'.Session::get('general')->theme.'/images/abt_4.png') }}" alt="">
</div>
</div>
</div>
<div class="bat_list_buttons">
<button type="button" class="btn btn-secondary" title="@lang('messages.Join Us')" onclick="window.location='{{ url('contact-us') }}'">@lang('messages.Join Us')</button>
</div>
</div>
</section>  */?>
<section class="who_we_are">
<div class="container">
<h2>@lang('messages.Featured in...')</h2>
<p>@lang('messages.Any information gathered by :site_name Inc. is used to customize the Postmates Inc. experience, and is not shared or sold to any third parties, except as expressly provided for in this Privacy Policy and our Terms of Use.', ['site_name' => Session::get("general")->site_name])</p>

<div class="client_logos">
<div class="owl-carousel">
                <div class="item">
                    <a href="javascript:;"><img style="<?php echo "background:url('". URL::asset('assets/front/'.Session::get('general')->theme.'/images/haym_hover.png')."')";?>"  src="{{ URL::asset('assets/front/'.Session::get('general')->theme.'/images/haym.png') }}" onmouseover="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/haym_hover.png')."'";?>" onmouseout="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/haym.png')."'";?>" /></a>
                </div>
                <div class="item">
                    <a href="javascript:;"><img style="<?php echo "background:url('". URL::asset('assets/front/'.Session::get('general')->theme.'/images/doux.png')."')";?>"  src="{{ URL::asset('assets/front/'.Session::get('general')->theme.'/images/doux.png') }}" onmouseover="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/doux_hover.png')."'";?>" onmouseout="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/doux.png')."'";?>" /></a>
                </div>
                <div class="item">
                    <a href="javascript:;"><img style="<?php echo "background:url('". URL::asset('assets/front/'.Session::get('general')->theme.'/images/pittas.png')."')";?>"  src="{{ URL::asset('assets/front/'.Session::get('general')->theme.'/images/pittas.png') }}" onmouseover="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/pittas_hover.png')."'";?>" onmouseout="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/pittas.png')."'";?>" /></a>
                </div>
                <div class="item">
                    <a href="javascript:;"><img style="<?php echo "background:url('". URL::asset('assets/front/'.Session::get('general')->theme.'/images/india.png')."')";?>"  src="{{ URL::asset('assets/front/'.Session::get('general')->theme.'/images/india.png') }}" onmouseover="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/india_hover.png')."'";?>" onmouseout="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/india.png')."'";?>" /></a>
                </div>
                <div class="item">
                    <a href="javascript:;"><img style="<?php echo "background:url('". URL::asset('assets/front/'.Session::get('general')->theme.'/images/doux.png')."')";?>"  src="{{ URL::asset('assets/front/'.Session::get('general')->theme.'/images/doux.png') }}" onmouseover="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/doux_hover.png')."'";?>" onmouseout="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/doux.png')."'";?>" /></a>
                </div>
                <div class="item">
                    <a href="javascript:;"><img style="<?php echo "background:url('". URL::asset('assets/front/'.Session::get('general')->theme.'/images/pittas.png')."')";?>"  src="{{ URL::asset('assets/front/'.Session::get('general')->theme.'/images/pittas.png') }}" onmouseover="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/pittas_hover.png')."'";?>" onmouseout="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/pittas.png')."'";?>" /></a>
                </div>
                <div class="item">
                    <a href="javascript:;"><img style="<?php echo "background:url('". URL::asset('assets/front/'.Session::get('general')->theme.'/images/india.png')."')";?>"  src="{{ URL::asset('assets/front/'.Session::get('general')->theme.'/images/india.png') }}" onmouseover="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/india_hover.png')."'";?>" onmouseout="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/india.png')."'";?>" /></a>
                </div>
                <div class="item">
                    <a href="javascript:;"><img style="<?php echo "background:url('". URL::asset('assets/front/'.Session::get('general')->theme.'/images/doux.png')."')";?>"  src="{{ URL::asset('assets/front/'.Session::get('general')->theme.'/images/doux.png') }}" onmouseover="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/doux_hover.png')."'";?>" onmouseout="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/doux.png')."'";?>" /></a>
                </div>
                <div class="item">
                    <a href="javascript:;"><img style="<?php echo "background:url('". URL::asset('assets/front/'.Session::get('general')->theme.'/images/haym_hover.png')."')";?>"  src="{{ URL::asset('assets/front/'.Session::get('general')->theme.'/images/haym.png') }}" onmouseover="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/haym_hover.png')."'";?>" onmouseout="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/haym.png')."'";?>" /></a>
                </div>
                <div class="item">
                    <a href="javascript:;"><img style="<?php echo "background:url('". URL::asset('assets/front/'.Session::get('general')->theme.'/images/doux.png')."')";?>"  src="{{ URL::asset('assets/front/'.Session::get('general')->theme.'/images/doux.png') }}" onmouseover="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/doux_hover.png')."'";?>" onmouseout="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/doux.png')."'";?>" /></a>
                </div>
                <div class="item">
                    <a href="javascript:;"><img style="<?php echo "background:url('". URL::asset('assets/front/'.Session::get('general')->theme.'/images/pittas.png')."')";?>"  src="{{ URL::asset('assets/front/'.Session::get('general')->theme.'/images/pittas.png') }}" onmouseover="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/pittas_hover.png')."'";?>" onmouseout="<?php echo "this.src='". URL::asset('assets/front/'.Session::get('general')->theme.'/images/pittas.png')."'";?>" /></a>
                </div>
            </div>
</div>

</div>
</section>
    <!-- content end -->
    <script type="text/javascript">
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
