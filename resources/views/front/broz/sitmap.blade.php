    @extends('layouts.app')
	@section('content')

    <!-- content start -->
    <section class="about_us_bg_user">
<div class="container">
<div class="row">
<div class="banner_abt_news">
<h1>@lang('messages.Site Map')</h1>
<h5>@lang('messages.A sitemap lists all pages available on a website.The sitemap for web pages on Arabty is shown below.')</h5>
 
 <div class="col-md-6">
 <ul>
 <li><a href="<?php echo url('/'); ?>" title="Home">@lang('messages.Home')</a></li>
 <li><a href="#" title="Wish List">@lang('messages.Wishlist')</a></li>
 <li><a href="#" title="Cart">@lang('messages.Cart')</a></li>
 
 <ul>
 <p class="thijk_title_info">@lang('messages.Information')</p>
 <li><a href="#" title="Register your store">@lang('messages.Today offers')</a></li>
 <li><a href="#" title="About us">@lang('messages.About us')</a></li>
 <li><a href="#" title="Contact">@lang('messages.How it works')</a></li>
 <li><a href="#" title="Terms &amp; conditions">@lang('messages.FAQ')</a></li>
 <li><a href="#" title="Privacy policy  ">@lang('messages.Terms and conditions')  </a></li>
 <li><a href="#" title="We’re hiring">@lang('messages.Contracts')</a></li>
 <li><a href="#" title="Blog">@lang('messages.Careers')</a></li>
 </ul>
  <li><a href="#" title="My account">@lang('messages.My account')</a></li>
 <li><a href="#" title="Checkout">@lang('messages.Checkout')</a></li>
<?php /* <li><a href="#" title="Site map">@lang('messages.Site map')</a></li>
 <li><a href="#" title="Contact us">@lang('messages.Contact us')</a></li> */?>
 </ul>
 </div>
 <div class="col-md-6">
 <ul>
 <ul>
 <p class="thijk_title_info">@lang('messages.Categories')</p>
 <li><a href="#" title="Pik n pay">@lang('messages.Supermarket')</a></li>
 <li><a href="#" title="Edgars">@lang('messages.Grocery')</a></li>
 <li><a href="#" title="Toy kingdom">@lang('messages.Bakery and Sweets')</a></li>
 <li><a href="#" title="Queespark">@lang('messages.Retail')</a></li>
 <?php /*<li><a href="#" title="Toy kingdom">@lang('messages.Toy kingdom')</a></li>
 <li><a href="#" title="Queespark">@lang('messages.Queespark')</a></li> */?>
 </ul>
 <ul>
 <p class="thijk_title_info">@lang('messages.Extras')</p>
 <li><a href="#" title="Offer of the day">@lang('messages.Trade assurance')</a></li>
 <li><a href="#" title="How it works ">@lang('messages.We are hiring') </a></li>
 <li><a href="#" title="FAQ ">@lang('messages.Blog')</a></li>
 <?php /*<li><a href="#" title="Our service areas">Our service areas </a></li> */?>
 <li><a href="#" title="Toy kingdom">@lang('messages.Our Application on Android')</a></li>
 <li><a href="#" title="Queespark">@lang('messages.Our Application on IPhone')</a></li>
 </ul>
 </ul>
 </div>
</div>
</div>	
</div>	
</section>

    <!-- content end -->
    @endsection
