    @extends('layouts.app')
	@section('content')

	
	<link href="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/css/animations.css');?>" rel="stylesheet" />
	<script src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/js/select2.min.js');?>"></script>
	<script type="text/javascript" src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/js/css3-animate-it.js');?>"></script>
	<script type="text/javascript" src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/js/validator.min.js');?>"></script>
	<!-- container start -->
    <section class="inner_page_common">
        <div class="container">
<div class="sucess_messgaes">
 <div class="sucess_messgaes_absolut animatedParent"  data-sequence="500">
		<div class="sucess_messgaes_inner animated growIn slower go" data-id="1">
	</div>
	</div>
</div>
            <div class="contact_us_inner row">
                <div class="col-md-12 col-xs-12">
                    <div class="map_section">
                        <div id="googleMap" style="width:100%;height:410px;"></div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="contact_det">
                        <h3>@lang('messages.Contact details')</h3>
                        <address>
		<h4><i class="glyph-icon flaticon-location-pin"></i><?php echo Session::get("general")->site_name; ?><br>
<p><?php echo Session::get("general")->contact_address; ?></p></h4>		
		<a href="mailto:<?php echo Session::get("configemail")->contact_mail; ?>" title="<?php echo Session::get("configemail")->contact_mail; ?>"><i class="glyph-icon flaticon-close-envelope"></i><?php echo Session::get("configemail")->contact_mail; ?></a>
		<label><i class="glyph-icon flaticon-phone-receiver"></i><?php echo Session::get("configemail")->mobile_number; ?></label>
		</address>
                        <h3>@lang('messages.Be social')</h3>
                        <div class="social_share contact_us_social">
                            <ul>
                                <li>
                                    <a title="@lang('messages.Facebook')" class="face"target="_blank"  href="<?php echo Session::get("social")->facebook_page; ?>"> <i class="glyph-icon flaticon-facebook-logo"></i>
                                    </a>
                                </li>
                                <li><a title="@lang('messages.Instagram')" class="ins" target="_blank" href="<?php echo Session::get("social")->google_plus_page; ?>"><i class="glyph-icon flaticon-instagram-social-network-logo-of-photo-camera"></i></a>
                                </li>
                                <li><a title="@lang('messages.Twitter')" class="twi" target="_blank" href="<?php echo Session::get("social")->twitter_page; ?>"><i class="glyph-icon flaticon-twitter-logo-silhouette"></i></a>
                                </li>
                                <li><a title="@lang('messages.linkein')" class="link" target="_blank" href="<?php echo Session::get("social")->linkedin_page; ?>"><i class="glyph-icon flaticon-linkedin-logo"></i></a>
                                </li>
                            </ul>

                        </div>
                    </div>
                </div>
                <div class="col-md-8 col-sm-6 col-xs-12">
                    <div class="right_form">
                         {!!Form::open(array('url' => 'postcontactus', 'method' => 'post','class'=>'col s12','id'=>'contactus_form','files' => true));!!}
						 <?php //print_r($errors); ?>
                            <div class="col-md-7 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <input maxlength="20" type="text" class="form-control" value="{!! old('name') !!}" name="name" required placeholder="@lang('messages.Name')">
                                    <span class="error"> 
                                                @if ($errors->has('name'))
												{{ $errors->first('name', ':message') }}
												@endif
												</span>
                                </div>
                            </div>
                            <div class="col-md-5 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <input maxlength="15" type="text" class="form-control" type="tel" value="{!! old('mobile_number') !!}" name="mobile_number"  required placeholder="@lang('messages.Phone')">
                                    <span class="error">  
                                                @if ($errors->has('mobile_number'))
												{{ $errors->first('mobile_number', ':message') }}
												@endif
												</span>
                                </div>
                            </div>
                            
                                 
                                                        
												
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group">
                                     <input id="email" maxlength="100" type="email" value="{!! old('email') !!}" name="email" class="form-control" required placeholder="@lang('messages.Email')">
                                                                                        <span class="error"> 
                                                @if ($errors->has('email'))
												{{ $errors->first('email', ':message') }}
												@endif
												</span>
                                </div>
                            </div>
                            

                            <div class="col-md-6 col-sm-12 col-xs-12">
							  <div class="form-group">
                                    <div class="form-control" style=" padding:0px;">
								<select id = "city" class="" required style="width:100%;" name="city" >
								<option value="">@lang('messages.Select City')</option>
								@foreach(getCityList(getAppConfig()->default_country) as $city)
								<option value="{{ $city->id }}" <?php if($city->id==Input::get('city')){  echo "selected";   } ?> > {{ $city->city_name }}</option>
								@endforeach
								</select>
								@if ($errors->has('city'))
								{{ $errors->first('city', ':message') }}
								@endif
								</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12 border_none_new">
                                <div class="form-group">
                                    <div class="form-control" style=" padding:0px;">
                                        <select id="enquiry_type" class="" required="true" style="width:100%;" name="enquery_type">
                                            <option value="">@lang('messages.Enquery type')</option>
                                            <option value="1" <?php if(Input::get('enquery_type')==1){  echo "selected";   } ?> >General</option>
                                            <option value="2" <?php if(Input::get('enquery_type')==2){  echo "selected";   } ?>>Product </option>
                                             <option value="3" <?php if(Input::get('enquery_type')==3){  echo "selected";   } ?>>Delivery</option>
                                            <option value="4" <?php if(Input::get('enquery_type')==4){  echo "selected";   } ?>>Payment</option>
                                            <option value="5" <?php if(Input::get('enquery_type')==5){  echo "selected";   } ?>>Outlet</option>
                                        </select>
                                        @if ($errors->has('enquery_type'))
												{{ $errors->first('enquery_type', ':message') }}
												@endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 col-xs-12">
                                <div class="form-group">
                                    <textarea class="form-control" required rows="5" id="comment" name="message">{!! old('message') !!}</textarea>
                                    @if ($errors->has('enquery_type'))
									{{ $errors->first('enquery_type', ':message') }}
									@endif
                                </div>
                            </div>

                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <div class="sign_bot_sub">
										<button type="button" class="btn btn-primary" onclick="window.location='{{ url('/') }}'" title="@lang('messages.Cancel')">@lang('messages.Cancel')</button>
                                        <button type="submit" id="contactsubmit"  class="btn btn-default" title="@lang('messages.Send')">@lang('messages.Send')</button>

                                        			<div class="col-sm-4 loader-coms" id="payajaxloading" style="display:none;">
			<i class="fa fa-spinner fa-spin fa-3x "></i><strong style="margin-left: 3px;">@lang('messages.Processing...')</strong>
			</div>
                                        
                                    </div>
                                </div>
                            </div>
                       {!!Form::close();!!}
                    </div>

                </div>

            </div>

        </div>
    </section>
    <?php /** 
	<script src="http://maps.googleapis.com/maps/api/js"></script>
*/ ?>
<script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyBnuG3TBKQkFl83wd-JRt7WTQBxgkRtuTg"></script>
    <script type="text/javascript">
	
	
		$('#contactus_form').validator().on('submit', function (e)
		{
		  if (e.isDefaultPrevented()) {
			//alert($("#city").val());
			if($("#city").val() == "")
			{
				$("#city").addClass("has-error");
			}
			if($("#enquiry_type").val() == "")
			{
				$("#enquiry_type").addClass("has-error");
			}
			//alert($("#enquiry_type").val());
		  } else {
			// everything looks good!
		  }
		})
	
	
	
        $('select').select2();
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
    </script>
		<script type="text/javascript">
			var address="<?php echo Session::get("general")->contact_address; ?>";
			var latlng = new google.maps.LatLng(29.379986,47.988963);
			var myOptions = {
			zoom: 13,
			center: latlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			navigationControl: true,
			mapTypeControl: true,
			scaleControl: true
			};
			var map = new google.maps.Map(document.getElementById("googleMap"), myOptions);
			var marker = new google.maps.Marker({
			position: latlng,
			animation: google.maps.Animation.BOUNCE
			});
			marker.setMap(map);
			var contentString = '<div id="content">'+
			'<div id="siteNotice">'+
			'</div>'+
			'<h3 id="firstHeading" class="firstHeading">'+address+'</h3>'+
			'</div>';
			var infowindow = new google.maps.InfoWindow({
				content: contentString
			});
			google.maps.event.addListener(marker, 'click', function() { infowindow.open(map,marker); }); infowindow.open(map,marker);
			google.maps.event.addListener(marker, 'click', function() {
				infowindow.open(map,marker);
			});
		infowindow.open(map,marker);
		</script>  
    <!-- mobile responsive menu js-->
    <script>	
        function ContactSubmit()
		{
			$("#contactajaxloading").show();
			$("#contactsubmit").hide();
		}
    </script>
    <script type="text/javascript">
		$( document ).ready(function() {
			
			$('.BDC_CaptchaImageDiv a').addClass('test');
			$('.test').css('visibility', 'hidden');
		});
	</script>
    @endsection

	
