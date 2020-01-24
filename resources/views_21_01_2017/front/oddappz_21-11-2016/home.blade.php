  @extends('layouts.app')
  @section('content')
  
  <?php $general = Session::get("general"); $social = Session::get("social"); $email = Session::get("configemail");?>
    <section class="banner_sections">
        <div class="container">
            <div class="banner_captcha">
                <h1>@lang('messages.On demand delivery across india')</h1>
                <h2>@lang('messages.Get the best of your city delivered in minutes')</h2>
            </div>
            <div class="findyour_location">
                <div class="findyour_inner">
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
                        <div class="col-md-7 col-sm-12 col-xs-12 padding0">
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
                                    <?php switch ($category->url_key) {
                                         case "supermarket":
                                            $span='<span><i class="glyph-icon flaticon-transport"></i></span>';
                                            break;
										  case "restaurant":
                                            $span='<span><i class="glyph-icon flaticon-restaurant"></i></span>';
                                            break;	
                                        case "fruitsvegetables":
                                            $span='<span><i class="glyph-icon flaticon-employment-deal"></i></span>';
                                            break;
                                      
                                        case "bakery-sweets":
                                            $span='<span><i class="glyph-icon flaticon-food"></i></span>';
                                            break;
										case "retail":
                                            $span='<span><i class="glyph-icon flaticon-shopping-bags-black-couple"></i></span>';
                                            break;		
                                        case "flowers":
                                            $span='<span><i class="glyph-icon flaticon-rose-shape"></i></span>';
                                            break;
										 
                                        default:
                                            $span='';
                                    }?>
                                    <?php echo $span.ucfirst($category->category_name);?>
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
                        <h3>@lang('messages.Take Oddappz everywhere!')</h3>
                        <p>@lang('messages.Integer magna erat, egestas sit amet felis ut, ultricies cursus massa. Aenean pulvinar nisl sed ultrices hendrerit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis sit amet urna at elit lacinia imperdiet. Sed id mauris mi. Integer facilisis sapien non ornare ornare. Pellentesque viverra fringilla fringilla.')</p>
                        <div class="app_sections">
                            <a href="javascript:;" title="Android app on Google paly"> <img src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/google_play.png'); ?>" alt="Android app on Google paly">
                            </a>
                            <a href="javascript:;" title="Available on the iphone AppStore"> <img src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/app_store.png'); ?>" alt="Available on the iphone AppStore">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="featured_list">
        <div class="container">
            <div class="inner_product_list row">
                <h3>@lang('messages.Featured stores')</h3>
                <p>@lang('messages.Integer ullamcorper nulla a mi fringilla scelerisque phasellus pharetra ante ut')
                    <br>@lang('messages.finibus varius.')</p>
                <div class="item_common">
                    <div class="owl-carousel">
                        <?php if(count(getFeatureSstore($api))){
                            foreach(getFeatureSstore($api)->response->data as $data){
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
                                }?>
                                <div class="col-md-4">
                                    <div class="item">
                                        <div class="futer_item">
                                            <?php echo $image; ?>
                                        </div>
                                    </div>
                                </div>
                        <?php } } ?>
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
            var location = $("#location_id").val();
            var city = $("#city_id").val();
            var category = $(this).attr("data-url");
            if(location == "" || city == "")
            {
                toastr.error('Please select location');
                return false;
            }
            //var url = '<?php echo url('store'); ?>?location='+location+'&category_ids='+category;
            var url = '{{url("stores/")}}/'+city+'/'+location+'/'+category;
            window.location = url;
        });

        $('.play').on('click', function() {
            owl.trigger('autoplay.play.owl', [1000])
        })
        $('.stop').on('click', function() {
            owl.trigger('autoplay.stop.owl')
        })

        // get header height (without border)
        var getHeaderHeight = $('.headerContainerWrapper').outerHeight();

        // border height value (make sure to be the same as in your css)
        var borderAmount = 2;

        // shadow radius number (make sure to be the same as in your css)
        var shadowAmount = 30;

        // init variable for last scroll position
        var lastScrollPosition = 0;

        // set negative top position to create the animated header effect
        $('.headerContainerWrapper').css('top', '-' + (getHeaderHeight + shadowAmount + borderAmount) + 'px');

        $(window).scroll(function() {
            var currentScrollPosition = $(window).scrollTop();

            if ($(window).scrollTop() > 2 * (getHeaderHeight + shadowAmount + borderAmount)) {

                $('body').addClass('scrollActive').css('padding-top', getHeaderHeight);
                $('.headerContainerWrapper').css('top', 0);

                if (currentScrollPosition < lastScrollPosition) {
                    $('.headerContainerWrapper').css('top', '-' + (getHeaderHeight + shadowAmount + borderAmount) + 'px');
                }
                lastScrollPosition = currentScrollPosition;

            } else {
                $('body').removeClass('scrollActive').css('padding-top', 0);
            }
        });
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
            $('#location_id').prop('disabled', 'disabled');
            var city_url, country_id, token, url, data;
            token = $('input[name=_token]').val();
            country_id = $('#country_id').val();
            city_url = $('#city_id').val();
            url = '{{url('list1/LocationList')}}';
            data = {city_url: city_url,country_id:country_id};
            $.ajax({
                url: url,
                headers: {'X-CSRF-TOKEN': token},
                data: data,
                type: 'POST',
                datatype: 'JSON',
                success: function (resp) {
                    $('#location_id').prop('disabled', false);
                    //console.log('in--'+resp.data);
                    $('#location_id,#delivery_areas').empty();
                    $('#s2id_delivery_areas .select2-choices .select2-search-choice').remove();
                    if(resp.data==''){
                        $('#location_id,#delivery_areas').append($("<option></option>").attr("value","").text('No data there..')); 
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
     </script>
    <!-- container end -->
    <!-- footer section strat -->
<?php echo Session::get("location"); ?>
@endsection
