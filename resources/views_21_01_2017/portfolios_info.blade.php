    @extends('layouts.app')

	@section('content')
    @if (count($portfolio) > 0 )
    @foreach($portfolio as $key => $value)
   <!-- content start -->
    <section>
        <div class="banner_sections portfolio_deti_bg">
            <div class="container-fluid">
                <div class="home_page_sections">
                    <div class="content_section">
                        <h1>{{ ucfirst($value->short_notes) }}</h1>
                        <a class="hvr-shutter-out-horizontal" href="/contact-us" title="Get a free quote">Get a free quote<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span></a>

                    </div>

                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="container-fluid">
            <div class="portfolio_details">
                <div class="col-md-6">
                    <div class="port_opt_left">
                        <div class="port_opt_top">
                            <div class="col-md-6 padding_left0">
                                <b>{{ ucfirst($value->title) }}</b>
                                  <?php $categories = explode(',',$value->category_ids);  ?>
                                <p>@foreach ($category as $val)
										@if (in_array($val->id,$categories))
										{{  ucfirst($val->category_name.',') }}
									@endif
									@endforeach
								</p>
                            </div>
                            <div class="col-md-4 col-sm-offset-2 padding_left0">
                                <b>Customers</b>
                                <p>{{ ucfirst($value->customer) }}</p>
                            </div>
                        </div>
                        <div class="port_opt_top">
                            <?php  if(file_exists(base_path().'/public/assets/admin/base/images/portfolio/detail/'.$value->image)) { ?>
								<img  alt="{{ ucfirst($value->title) }}"  src="<?php echo url('/assets/admin/base/images/portfolio/detail/'.$value->image.''); ?>" >
							<?php } else{  ?>
							<img  src=" {{ URL::asset('assets/front/base/images/blog_no_images.png') }} " alt="{{ ucfirst($value->title) }}">
									
				<?php } ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="port_opt_right port_opt_top">
                        <div class="col-md-9 padding_left0 col-sm-offset-3">
                            <b>Technology</b>
                            <p>{{ ucfirst($value->technology) }}</p>
                        </div>
                        <div class="col-md-12 padding_left0">
                            <label>{{ ucfirst($value->short_description) }}</label>
                        </div>
                        <div class="key_features">
							 <?php echo $value->long_description; ?>
                        </div>
                        <div class="app_store_links">
                        @if ($value->iphone_link)
                            <a href="{{ $value->iphone_link }}" target="_blank" title="Apple store"><img src=" {{ URL::asset('assets/front/base/images/app_store.png') }}" alt="Apple store">
                            </a>
                        @endif    
                        @if ($value->android_link)   
                            <a href="{{ $value->android_link }}" target="_blank" title="Google play store"><img src=" {{ URL::asset('assets/front/base/images/google_play.png') }}" alt="Google play store">
                            </a>
                        @endif 
                        @if ($value->web_link)    
                            <a href="{{ $value->web_link }}" target="_blank" title="Google play store"><img src=" {{ URL::asset('assets/front/base/images/web_links.png') }}" alt="Google play store">
                            </a>
                        @endif 
                            	
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
 @endforeach
 @endif 
    <section class="get_in_touch">
        <div class="container-fluid">
            <div class="informations_section">
                 <h2>Contact us for FREE web and mobile application consultation</h2>
                 <p>Ofcourse, We can sign a NDA document for your project confidential.</p>
                <a title="@lang('messages.Contact Us')" href="{{ URL::to('/contact-us') }}" class="hvr-shutter-out-horizontal">@lang('messages.Contact Us')</a>
            </div>
        </div>
        </div>
    </section>

    <!-- content end -->
    
    @endsection
