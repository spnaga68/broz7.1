    @extends('layouts.app')

	@section('content')

	<!-- content start -->
    <section>
        <div class="banner_sections portfolio_bg">
            <div class="container-fluid">
           e     <div class="home_page_sections">
                    <div class="content_section">
                        <h1>@lang('messages.We are stepping together with Brands and Start-ups to provide successful experience</h1>
                        <a class="hvr-shutter-out-horizontal" href="/contact-us" title="Get a free quote">Get a free quote<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span></a>

                    </div>

                </div>
            </div>
        </div>
    </section>
    
    <section class="portfolio_sections">
	<div class="container-fluid">
	<h2>@lang('messages.Our portfolios')</h2>
	<p>We are expertise in carving successful design, websites, web applicaton and mobile applications for all type of organisations where our services are highly dedicated, motivated and standard according to market standard.</p>
 
 <div class="portfolio_listings">
 
 		<!-- Filter Controls - Simple Mode -->
        <div class="row">
            <!-- A basic setup of simple mode filter controls, all you have to do is use data-filter="all"
            for an unfiltered gallery and then the values of your categories to filter between them -->
           <div class="menu_cat">
		   <ul class="simplefilter">
		   <?php $segment = Request::segment(3);  ?>
                <li <?php if(!$segment) { ?> class="active" <?php } ?> > <a href="{{ URL::to('/portfolios') }}" title="ALL"> ALL </a>  </li>
                @foreach ($category as $val)
					<li <?php if($segment==$val->url_key) { ?> class="active" <?php } ?> >  <a href="{{ URL::to('/portfolios/filter/' . $val->url_key . '') }}" title="{{ ucfirst($val->category_name) }}"> {{  ucfirst($val->category_name) }} </a></li>
				@endforeach 
			 <?php /**
                <li data-filter="1"> WEB</li>
                <li data-filter="2">IPHONE</li>
                <li data-filter="3">ANDROID</li>
                ***/?>
              
            </ul>
			</div>
        </div>
        
            <!-- This is the set up of a basic gallery, your items must have the categories they belong to in a data-category
            attribute, which starts from the value 1 and goes up from there -->
			<div class="lode_port">
            <div class="filtr-container">

                @if (count($portfolio) > 0 )
                @foreach($portfolio as $key => $value)
                <div class="col-xs-6 col-sm-4 col-md-3 filtr-item">
				<div class="port_inner_item">
				<?php  if(file_exists(base_path().'/public/assets/admin/base/images/portfolio/thumbimage/'.$value->thumb_image) && $value->thumb_image) { ?>
								<img class="img-responsive" alt="{{ ucfirst($value->title) }}"  src="<?php echo url('/assets/admin/base/images/portfolio/thumbimage/'.$value->thumb_image.''); ?>" >
							<?php } else{  ?>
									<img class="img-responsive" height="438px" src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/blog_no_images.png');?>" alt="{{ ucfirst($value->title) }}">
					<?php } ?>
                   <div class="caption">
					<div class="blur">
					<div class="caption-text">
					<a href="{{ URL::to('/portfolios/info/' . $value->portfolio_index . '') }}" title="{{ ucfirst($value->title) }}">
						<h3>{{ ucfirst($value->title) }}</h3>
						<span class="linke_a_sit"><i class="glyph-icon flaticon-tool"></i></span></a>
					</div>
					</div>
				</div>
				</div>
                </div>
                @endforeach
				@endif 
</section >	
    <section class="get_in_touch">
        <div class="container-fluid">
            <div class="informations_section">
                <h2>@lang('messages.Contact us for FREE web and mobile application consultation')</h2>
                <p>@lang(,messages Ofcourse, We can sign a NDA document for your project confidential.')</p>
                <a title="@lang('messages.Contact Us')" href="/contact-us" class="hvr-shutter-out-horizontal">Contact Us</a>
            </div>

        </div>
        </div>
    </section>

    <!-- content end -->
    <!-- Portfolio tab script -->
    <?php /** 
    <script src=" {{ URL::asset('assets/front/base/js/jquery.filterizr.js') }}"></script>
    **/ ?>
    
    <script src=" {{ URL::asset('assets/front/base/js/controls.js') }}"></script>
     <!-- Kick off Filterizr -->
     	<?php if($segment){ ?>
		<script type="text/javascript">
			$(document).ready(function() {
			 $("html,body").animate({scrollTop: 700}, 1000);
		});
		</script>
	<?php } ?>
    <script type="text/javascript">
        $(function() {
            //Initialize filterizr with default options
            $('.filtr-container').filterizr();
        });
    </script>
    
    @endsection
