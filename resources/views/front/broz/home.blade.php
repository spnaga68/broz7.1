 @extends('layouts.app')
  @section('content')
 <?php  $language_id=getCurrentLang(); ?>
  <?php $general = Session::get("general"); $social = Session::get("social"); $email = Session::get("configemail");?>
    <section class="banner_sections slider">
<div class="flexslider big">

        <div class="loader-slider hidden-phone"></div>               
            <div class="flex-viewport">
			<ul class="slides">
				<?php if(App::getLocale() == 'ar'){  ?>
				<?php $banners=get_banner_list(2);?>
	<?php if(count($banners)){
	
                            foreach($banners as $data){ ?>
                                <li>
                                    <a "javascript:;"><img  src="<?php echo url('/assets/admin/base/images/banner/'.$data->banner_image.'?'.time()); ?>" alt="{{ $data->banner_title }}" title="{{ $data->banner_title }}"/></a>
                                </li>
                            <?php } ?>
                        <?php } else { ?>
                            <li>
                                <a "javascript:;"><img  src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/store_slid1.png');?>" alt="{{ $data->banner_title }}" title="{{ $data->banner_title }}"/></a>
                            </li>
                        <?php } } else { ?>
							
					<?php $banners=get_banner_list(1);?>
				<?php if(count($banners)>0){
	                   foreach($banners as $data){ ?>
                                <li> 
                                    <a "javascript:;"><img  src="<?php echo url('/assets/admin/base/images/banner/'.$data->banner_image.'?'.time()); ?>" alt="{{ $data->banner_title }}" title="{{ $data->banner_title }}"/></a>
                                </li>
                            <?php } ?>
                        <?php } else { ?>
                            <li>
                                <a "javascript:;"><img  src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/store_slid1.png');?>" /></a>
                            </li>
                            <?php }  }?>
				</ul>
				</div>
			
				</div>
	
        <div class="container_slider">
            <?php /*<div class="banner_captcha"> 
                <h1>@lang('messages.On demand delivery across Saudi Arabia')</h1>
                <h2>@lang('messages.Get the best of your city delivered in minutes')</h2>
            </div> */ ?>
            <div class="findyour_location">
                <div class="findyour_inner">
				  <div class="banner_captcha"> 
                <h1>On demand delivery across India</h1>
                <h2>Get the best of your city delivered in minutes</h2>
            </div>
                   {!!Form::open(array('url' =>'store', 'method' => 'get','class'=>'tab-form attribute_form','id'=>'home_search_form','files' => true));!!}
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="col-md-3 col-sm-12 col-xs-12 padding0">
						<div class="select_city">
							<select name="city"  id="city_id" required class="js-example-disabled-results">
								<option value="">@lang('messages.Select city')</option>
								<?php if(count(getCity($api)->response->data)){ ?>
									<?php foreach(getCity($api)->response->data as $data){ ?>
										<option <?php if(Session::get("city")==$data->url_index){ echo "selected"; } ?> value="{{ $data->url_index }}">{{ ucfirst($data->city_name) }} </option>
									<?php } ?>
								<?php } ?>
							</select>
						</div>
					</div>
                        <div class="col-md-5 col-sm-12 col-xs-12 padding0">
                            <div class="select_categ">
                                <select name="location" id="location_id" required class="js-example-disabled-results">
                                    <option value="">@lang('messages.Select Zone')</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12 col-xs-12 padding0">
                            <div class="submit_sect">
                                <button type="button" id="find_location" title="@lang('messages.Find')" class="btn btn-default">@lang('messages.Find')</button>
                            </div>
                        </div>
						     <div class="col-md-2 col-sm-12 col-xs-12 padding_right0">
                            <div class="submit_sect_locaket_me">
                                <button type="button" id="geolocation" title="@lang('messages.Find')" class="btn btn-default" onclick = "show_map()"> @lang('messages.Locate me')</button>
                            </div>
                        </div>
                     {!!Form::close();!!} 
                </div>
            </div>
        </div>.
    </section>
<section class="times_description">
        <div class="container">
            <div class="row">
                <p>@lang('messages.Get your items at your doorstep within minutes.') <a href="javascript:void(0);" id="click_how_it" title="@lang('messages.See how')">@lang('messages.See how')<i class="glyph-icon flaticon-arrow-point-to-right"></i></a>
                </p>
            </div>
        </div>
        <div class="how_it_wk_home" id="open_how_it">
            <div class="container">
                <div class="row">
                    <div class="col-md-3">
                        <div class="how_list">
                            <span class="home_icon">
                                <img src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/search.png'); ?>" width="66" alt="<?php echo $general->site_name; ?>">
                            </span>
                            <h3>@lang('messages.1. Find')</h3>
                            <p>@lang('messages.Search stores that deliver to you')<br/>@lang('messages.by entering your address')</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="how_list">
                            <span class="home_icon">
                                <img src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/Choose.png'); ?>" width="80" alt="<?php echo $general->site_name; ?>">
                            </span>
                            <h3>@lang('messages.2. Select')</h3>
                            <p>@lang('messages.Browse hundreds of categories to')<br/>@lang('messages.find the productss you like')</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="how_list">
                            <span class="home_icon">
                                <img src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/Pay.png'); ?>" width="80" alt="<?php echo $general->site_name; ?>">
                            </span>
                            <h3>@lang('messages.3. Pay')</h3>
                            <p>@lang('messages.Pay fast secure online or on')<br/> @lang('messages.delivery')</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="how_list">
                            <span class="home_icon">
                                <img src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/enjoy.png'); ?>" width="90" alt="<?php echo $general->site_name; ?>">
                            </span>
                            <h3>@lang('messages.4. Enjoy')</h3>
                            <p>@lang('messages.Prodcucts is packed delivered to')<br/> @lang('messages.your door')</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
   <section class="product_cat">
        <div class="container">
            <div class="inner_product_list row">
                <h3>@lang('messages.A wide range of products')</h3>
                <p>@lang('messages.Integer ullamcorper nulla a mi fringilla scelerisque phasellus pharetra ante ut')<br>@lang('messages.finibus varius.')</p>
                <div class="item_common">
                    <?php $i=1; $categories= getCategoryLists(2); foreach($categories as $category){ if($i<7) { ?>
                        <div class="col-md-2 col-sm-4 col-xs-6">
                            <div class="market_item">
                                <a href="javascript:;" class="category_search" data-url="{{$category->url_key}}" title="<?php echo ucfirst($category->category_name);?>">
                                <img src="<?php echo url('/assets/admin/base/images/category/'.$category->image.'?'.time()); ?>" alt="{{ $category->category_name }}" class=bell col="g">
                                <div><?php echo ucfirst($category->category_name);?></div>
                                </a>
                            </div>
                        </div>
                    <?php  } $i++; } ?>    
                </div>
            </div>
        </div>
    </section>
   <section class="google_paly">
        <div class="container">
            <div class="google_play_app row">
                <div class="col-md-6 col-sm-12 col-xs-12">
                    <div class="left_app common_app">
                        <img src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/app.png?'.time()); ?>" alt="<?php echo $general->site_name; ?>">
                    </div>
                </div>
                <div class="col-md-6 col-sm-12 col-xs-12">
                    <div class="left_app">
                        <h3>Take Broz everywhere!</h3>
                        <p>Integer magna erat, egestas sit amet felis ut, ultricies cursus massa. Aenean pulvinar nisl sed ultrices hendrerit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis sit amet urna at elit lacinia imperdiet. Sed id mauris mi. Integer facilisis sapien non ornare ornare. Pellentesque viverra fringilla fringilla.</p>
                        <div class="app_sections">
                            <a href="{{url('https://play.google.com/store/apps/details?id=com.app.oddappz')}}" title="Android app on Google paly"> <img src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/google_play.png'); ?>" alt="Android app on Google paly">
                            </a>
                            <a href="javascript:;" title="Available on the iphone AppStore"> <img src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/app_store.png'); ?>" alt="Available on the iphone AppStore">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
        </div>
			</div>
			
        </div>
        </div>
    </section>
    <section class="featured_list">
        <div class="container">
            <div class="inner_product_list row">
               <h3>Featured stores</h3>
<p>
Integer ullamcorper nulla a mi fringilla scelerisque phasellus pharetra ante ut
<br>
finibus varius.
</p>
                 <div class="item_common">
                    <div class="owl-carousel"> 
						<?php   if(count(getFeatureSstore($api))){ ?>
							<?php foreach(getFeatureSstore($api)->response->data as $data){
								//echo '<pre>';print_r(getFeatureSstore($api));die;
							$cate= explode(',',$data->category_ids);
					$url = url('/assets/admin/base/images/vendors/thumb/'.$data->featured_image);
					$store_url = URL::to('store/info/'.$data->url_index);
						$category_name ='';
						foreach ($categories as $val){ 
						 if(in_array($val->id,$cate)){ 
							 $category_name .= ucfirst($val->category_name).' , ';
						 } 
						}
					if(count(get_object_vars($data->outlets))>1){
						$image ='<a href="javascript:;" title="'.$data->vendor_name.'" data-toggle="modal" data-target=".bd-example-modal-lg'.$data->vendors_id.'" > <img   alt="'.$data->vendor_name.'"  src="'.$url.'" ></a>';
					} else {
						$image ='<a href="'.$store_url.'" title="'.$data->vendor_name.'"> <img   alt="'.$data->vendor_name.'"  src="'.$url.'" ></a>';
					}
								?>
                        <div class="col-md-4">
                            <div class="item">
                                <div class="futer_item">
								<?php echo $image; ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                        <?php }  ?>
                        
                     
                    </div>
                </div>
                <?php if(count(getFeatureSstore($api))){ //print_r(getFeatureSstore($api)->response->data); exit; ?>
                    <script src="<?php echo url('assets/front/'.Session::get("general")->theme.'/plugins/rateit/src/jquery.rateit.js');?>"></script>
                    <link href="<?php echo url('assets/front/'.Session::get("general")->theme.'/plugins/rateit/src/rateit.css');?>" rel="stylesheet" type="text/css" media="all">
                    <?php foreach(getFeatureSstore($api)->response->data as $data){
                        $cate= explode(',',$data->category_ids);
                        $outlet_html ='';
                        if(count(get_object_vars($data->outlets))>1){
                            $outlet_html .='<div  class="modal fade store_detials_list bd-example-modal-lg'.$data->vendors_id.'" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button><h4 id="myLargeModalLabel" class="modal-title">'.$data->vendor_name.'</h4><div class="store_title">'.count(get_object_vars($data->outlets)).' '.trans("messages.Outlets available near you.").'</div></div><div class="store_right_items">';
                            foreach($data->outlets as $o) {
                                $outlets = get_object_vars($o);
                                $outlets_url =URL::to('store/info/'.$outlets['url_index']);
                                $outlet_html .='<div class="col-md-3 col-sm-3 col-xs-6"><div class="common_item"><div class="store_itm_img">';
                                $store_no_image = URL::asset("assets/admin/base/images/vendors/stores.png");
                                if(file_exists(base_path().'/public/assets/admin/base/images/vendors/list/'.$data->logo_image)) {
                                    $url = url('/assets/admin/base/images/vendors/list/'.$data->logo_image);
                                    $oimage ='<a href="'.$outlets_url.'" title="'.$data->vendor_name.'"> <img   alt="'.$data->vendor_name.'"  src="'.$url.'" ></a>';
                                } else{  
                                    $oimage ='<a href="'.$outlets_url.'" title="'.$data->vendor_name.'"><img src="'.$store_no_image.'" alt="'.$data->vendor_name.'"></a>';
                                } 
                                $outlet_html .=$oimage.'<div class="price_sec"><b>'.$outlets['outlets_delivery_time'].'</b></div></div><div class="store_itm_desc"><a href="'.$store_url.'" title="'.$outlets['vendor_name'].'">'.$outlets['vendor_name'].'</a><p>'.substr($category_name,0,85).'</p></div><div class="store_itm_rating"><h2><div class="rateit" data-rateit-value='.$outlets['outlets_average_rating'].' data-rateit-ispreset="true" data-rateit-readonly="true">  </div>&nbsp'. $outlets['outlets_average_rating'].' </h2></div><div class="store_itm_rating map_location"><a class="location_location"><i class="glyph-icon flaticon-location-pin"></i>'.substr($outlets['contact_address'],0,30).'</a></div></div></div>';
                            }
                            $outlet_html .='</div></div></div></div>';
                            echo $outlet_html;
                        }
                    }
                } ?>
            </div>
        </div>
    </section>
	 <link href="{{ URL::asset('assets/front/'.$general->theme.'/css/flexslider.css') }}" rel="stylesheet" />
      <script src="{{ URL::asset('assets/front/'.$general->theme.'/js/jquery_003.js') }}"></script>
      <script src="{{ URL::asset('assets/front/'.$general->theme.'/js/jquery_004.js') }}"></script>
	<script src="{{ URL::asset('assets/front/'.$general->theme.'/js/jquery.gallery.js') }}"></script>
	<script src="{{ URL::asset('assets/front/'.$general->theme.'/js/modernizr.custom.53451.js') }}"></script>

    <script type="text/javascript">
        <?php if(App::getLocale()=='ar') {  ?>
            var ortl = true;
        <?php }else {  ?>
            var ortl = false;
            <?php } ?>
        $('select').select2();
        var owl = $('.owl-carousel');
        owl.owlCarousel({
           loop:true,
            margin:0,
            autoplay:true,
            autoplayTimeout: 5000,
            nav:true,
            rtl:ortl,
            responsive:{
                320:{
                    items:1
                },
                  400:{
                    items:2
                },
                800:{
                    items:2
                },
                1000:{
                    items:3
                }
            }
        });
        $('.category_search').on('click', function() {
			 $('#city_id').select2();
            var location = $("#location_id").val();
            var city = $("#city_id").val();
            var category = $(this).attr("data-url");
            
           
            //var url = '<?php echo url('store'); ?>?location='+location+'&category_ids='+category;
            var url = '{{url("stores/")}}/'+category;
            window.location = url;
        });

        $('.play').on('click', function() {
            owl.trigger('autoplay.play.owl', [1000])
        })
        $('.stop').on('click', function() {
            owl.trigger('autoplay.stop.owl')
        })

           <!-- see how js-->
        $('#open_how_it').hide();
        $('#click_how_it').click(function() {
            $('#open_how_it').slideToggle('slow');
            $("i", this).toggleClass("flaticon-arrow-point-to-right flaticon-down-arrow");
        });
        <!-- see howjs-->
     
        <!-- check ,redio button js-->
        function setupLabel() {
            if ($('.label_check input').length) {
                $('.label_check').each(function() {
                    $(this).removeClass('c_on');
                });
                $('.label_check input:checked').each(function() {
                    $(this).parent('label').addClass('c_on');
                });
            };
            if ($('.label_radio input').length) {
                $('.label_radio').each(function() {
                    $(this).removeClass('r_on');
                });
                $('.label_radio input:checked').each(function() {
                    $(this).parent('label').addClass('r_on');
                });
            };
        };
        $(document).ready(function() 
        {
            $('#city_id').trigger("change");
            $('body').addClass('has-js');
            $('.label_check, .label_radio').click(function() {
                setupLabel();
            });
            setupLabel();
            $("#find_location").on("click",function()
            {
                var city = $("#city_id").val();
                var location = $("#location_id").val();
                
                if(city != "" && location != ""&& location != null)
                {
                    var url = '{{url("stores/")}}/'+city+'/'+location;
                    window.location = url;
                    
                }
                else
                {
                    if(city == "")
                        toastr.error('<?php echo trans('messages.Please select city');?>');
                    else
                        toastr.error('<?php echo trans('messages.Please select location');?>');
                }
            })
        });
       $('#city_id').change(function()
	{
		$("#location_id").val(null).trigger("change")
		//$('#location_id').prop('disabled', 'disabled');
		var city_url, country_id, token, url, data;
		token = $('input[name=_token]').val();
		country_id = $('#country_id').val();
		city_url = $('#city_id').val();
		url = '{{url('list1/LocationList')}}';
		data = {city_url: city_url,country_id:country_id};
		$.ajax({
			url: url,
			headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
			data: data,
			type: 'POST',
			datatype: 'JSON',
			success: function (resp) {
				$('#location_id').prop('disabled', false);
				//console.log('in--'+resp.data);
				$('#location_id,#delivery_areas').empty();
				$('#s2id_delivery_areas .select2-choices .select2-search-choice').remove();
				if(resp.data==''){
					$('#location_id,#delivery_areas').append($("<option></option>").attr("value","").text('No data there..')).attr('selected','selected'); 
				}
				else
				{
					$('#location_id,#delivery_areas').append($("<option></option>").attr("value","").text('<?php echo trans('messages.Select zone') ?>'));
					$.each(resp.data, function(key, value)
					{
						string = value['zone_name'];
						string = string.charAt(0).toUpperCase() + string.slice(1);
						$('#location_id,#delivery_areas').append($("<option></option>").attr("value",value['url_index']).text(string)); 
					});
					$('#location_id').val('<?php echo Session::get("location"); ?>').change();
					// alert('<?php echo Session::get("location"); ?>');
				}
			}
		});
	});
        $(document ).ready(function() { 
            $(".close").on("click", function() { 
                $("body").removeClass("modal-open");
                $(".modal-backdrop").hide();
            });
			
        });
		function get_location() {
   
        if (geo_position_js.init()) {
            geo_position_js.getCurrentPosition(show_map, handle_error);
        }

    }
         
	 function show_map(position) {
         console.log(position)
		var latitude, longitude, token, c_url, cdata;
		latitude = position.coords.latitude;
        longitude = position.coords.longitude;
        language = '<?php echo getCurrentLang();?>';
        $('#lat').val(latitude);
         $('#lng').val(longitude);
         generate_map(latitude, longitude);
		$('#address_model').modal('show');
	
	   
     }
	 function handle_error(err) {
       
        if (err.code == 1) {
            // user said no!
        }
    }
  
	 $("#geolocation").click(function(){
           if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(show_map, handle_error);
		} else {
			error('not supported');
		}
        });
  
     </script>
    <!-- container end -->
	
	<script type="text/javascript">
    jQuery(function(){
        jQuery('.flexslider.big').flexslider({
            animation: "slide",
            controlNav: false,
//            prevText: "====<img src='//' />",
            prevText: "<div class='mainslider_left_arrow'><i class='fa fa-angle-left'></i></div>",
            nextText: "<div class='mainslider_right_arrow'><i class='fa fa-angle-right'></i></div>",
            animationLoop: true,
            slideshowSpeed: 7000,           //Integer: Set the speed of the slideshow cycling, in milliseconds
            animationSpeed: 900,
            direction:"horizontal",
            pauseOnHover: true,
                                   start:function(){

                                jQuery('.loader-slider').stop().animate({'width':jQuery('.flexslider.big').width()+'px'},this.slideshowSpeed-this.animationSpeed);
                            },
                        pause:false,

                        before:function(slider){

                jQuery('.loader-slider').hide().css({width:'0'});

            },
                        after:function(slider){

                data_prev=jQuery(slider.slides[slider.currentSlide]).attr('data-prev');
                data_next=jQuery(slider.slides[slider.currentSlide]).attr('data-next');

                                jQuery('.next-slider img').attr('src',data_next);
                jQuery('.prev-slider img').attr('src',data_prev);
                
                                jQuery('.loader-slider').hide();

                if(this.pause)return;
                /*.stop(true)*/
                               jQuery('.loader-slider').show().animate({width:jQuery('.flexslider.big').width()+'px'},this.slideshowSpeed-this.animationSpeed,'linear',function(){
                    jQuery('.loader-slider').hide().css({width:'0'});
                });
                                        

            },
                        pauseOn:function(slider){

                this.pause=true;
                time=jQuery('.loader-slider').width()*this.slideshowSpeed/jQuery('.flexslider.big').width();
                jQuery('.loader-slider').stop(true);
                jQuery('.loader-slider').hide().css({width:'0'});
            },
            pauseOff:function(slider){

                this.pause=false;
                time=(jQuery('.flexslider.big').width()-jQuery('.loader-slider').width())*this.slideshowSpeed/jQuery('.flexslider.big').width();
                //console.log(time);
                                jQuery('.loader-slider').stop(true).show().animate({width:jQuery('.flexslider.big').width()+'px'},time,'linear',function(){
                    jQuery('.loader-slider').hide().css({width:'0'});
                })
                            },
                    });

                    jQuery(".flexslider.big .flex-direction-nav .flex-prev").hover(function() {
                jQuery(".prev-slider").show();
            },function(){
                jQuery(".prev-slider").hide();
            });
            jQuery(".flexslider.big .flex-direction-nav .flex-next").hover(function() {
                jQuery(".next-slider").show();
            },function(){
                jQuery(".next-slider").hide();

            });
       // TopSlider();
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
    <!-- footer section strat -->
	@include('front.'.Session::get("general")->theme.'.locate_me_popup') 
@endsection
