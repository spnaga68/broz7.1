@extends('layouts.app')
@section('content')
<section class="store_item_list">
	<div class="container">
		<div class="row">
			<div class="my_account_section">
				<div id="parentHorizontalTab">
					<div class="col-md-3">
						@include('front.'.Session::get("general")->theme.'.profile_sidebar')
					</div>
					<div class="col-md-9">
						<div class="right_descript">
							<div class="resp-tabs-container hor_1">
								<div class="edit_profile_section">
									<h2 class="pay_title">@lang('messages.New Address')</h2>
									<div class="change_password margin_bot">
										{!!Form::open(array('url' => ['store-address'], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'change_password_form'));!!}
										<div class="location_product">
										<div class="col-md-4 padding_right0">
										<div class="location_text_feeld">
										<div class="form-group"> 
											{{ Form::select('address_type', $address_types, 'null', ['class' => 'address_type select_dropdown form-control col-xs-6','id'=>'address_type'] ) }}
												<?php /*<input type="text" name ="name" placeholder="@lang('messages.Name')" id="name" class="form-control"> */?>
													</div>
										</div>
										</div>
										<div class="col-md-6 padding0">
										<div class="location_text_feeld left_bord">
										<div class="input-group"> 
											<input type="text" id="found_address" name ="address" placeholder="@lang('messages.Address')" id="name" class="form-control">
											
											<input type="hidden" id="lat" name ="latitude" placeholder="@lang('messages.Name')" id="name" class="form-control"> 
											<input type="hidden" id="lng" name ="longtitude" placeholder="@lang('messages.Name')" id="name" class="form-control"> 
										</div>
									</div>
										</div>
										<div class="col-md-2 padding_left0">
<div class="location_butt">
																<button class="btn btn-secondary" id="get_location" type="button"><span class="glyphicon glyphicon-screenshot" ></span>@lang('messages.Locate me')</button>
															
</div>
										</div>
												<div class="col-md-12">
													<article  style="width:100%; height: 300px; margin-top: 10px; float: left; position: relative;">
													  <p style="display:none;">Finding your location: <span id="status">checking...</span></p>
													</article>
												</div>
												<?php /*<div class="col-md-12">
													<div class="form-group">
														{{ Form::select('country', $country_list, 'null', ['class' => 'country select_dropdown','id'=>'select_country'] ) }}
													</div>
												</div>
												
												<div class="col-md-12">
													<div class="form-group">
														{{ Form::select('city', $city_list, 'null', ['class' => 'city select_dropdown','id'=>'select_city'] ) }}
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group"> 
														<input type="text" name ="postal_code" placeholder="@lang('messages.Postal code')" id="name" class="form-control"> 
													</div>
												</div>
												
												*/ ?>
												<div class="col-md-12 padding0">
													<div class="button_sections">
														<button type="submit" class="btn btn-default" title="@lang('messages.Submit')">@lang('messages.Submit')</button>
														<a class="btn btn-primary cancel_button" data-url="cards" title="@lang('messages.Cancel')" href="{{URL::to('/cards')}}">@lang('messages.Cancel')</a>
													</div>
												</div>
											{!!Form::close();!!}
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<!-- footer section strat end -->
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
 <!--Plug-in Initialisation-->
<script type="text/javascript">
	$('select').select2();
</script>


<script src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyDCdWohFBZcQISBioA24aUqHNEviHDGpCk"></script>
<script>

	var geocoder = new google.maps.Geocoder();
    function geocodePosition(pos) {
		geocoder.geocode({
			latLng: pos
		}, function(responses) {
			if (responses && responses.length > 0) {
			} else {

			}
		});

    }
	$("#get_location").on('click', function()
	{
		if (navigator.geolocation) 
		{
			navigator.geolocation.getCurrentPosition(success, error);
		}
		else 
		{
		  error('not supported');
		}
	});
	$( document ).ready(function() {
		//var latlng = new google.maps.LatLng(29.379986,47.988963);
//		generate_map('29.379986', '47.988963');
		generate_map('12.983893', '77.750033');
	});
	var markersArray = [];

	function clearOverlays() {
		for (var i = 0; i < markersArray.length; i++ ) {
			markersArray[i].setMap(null);
		}
		markersArray.length = 0;
	}
	function generate_map(lat,lng)
	{
		var mapcanvas = document.createElement('div');
		mapcanvas.id = 'mapcanvas';
		mapcanvas.style.height = '300px';
		mapcanvas.style.width = '100%';
		document.querySelector('article').appendChild(mapcanvas);
		var latlng = new google.maps.LatLng(lat,lng);
		// console.log(latlng);
		var myOptions = {
		zoom: 15,
		center: latlng,
		mapTypeControl: false,
		navigationControlOptions: {style: google.maps.NavigationControlStyle.SMALL},
		mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		var map = new google.maps.Map(document.getElementById("mapcanvas"), myOptions);

		var marker = new google.maps.Marker({
		position: latlng, 
		map: map, 
		title:"Drag me to change the address",
		draggable: true
		});
		
		var input = /** @type {HTMLInputElement}*/(document.getElementById('found_address'));
		//console.log(input)
		var searchBox = new google.maps.places.SearchBox(input);
		var markers = [];
		google.maps.event.addListener(searchBox, 'places_changed', function() {
			clearOverlays();
			var places = searchBox.getPlaces();
			for (var i = 0, marker; marker = markers[i]; i++) {
			marker.setMap(null);
			}
			markers = [];
			var bounds = new google.maps.LatLngBounds();
			for (var i = 0, place; place = places[i]; i++) {
			var marker = new google.maps.Marker({
				map: map, 
				title: place.name,
				position: place.geometry.location,
				draggable: true
			});
			markers.push(marker);
			dragmarker(marker);
			updateMarkerPosition(place.geometry.location);
			geocodePosition(place.geometry.location);
			bounds.extend(place.geometry.location);
			markersArray.push(marker);
			}
			map.fitBounds(bounds);
		});
		markersArray.push(marker);
		latlang = lat+','+lng;
		get_address(latlang);
		updateMarkerPosition(latlng);
		geocodePosition(latlng);
		dragmarker(marker); 
	}
	function success(position) 
	{
		//	console.log(position);
		var s = document.querySelector('#status');
		if (s.className == 'success') {
		// not sure why we're hitting this twice in FF, I think it's to do with a cached result coming back    
		return;
		}
		generate_map(position.coords.latitude, position.coords.longitude);
	}
	
	function updateMarkerPosition(latLng) 
	{
		document.getElementById('lat').value=latLng.lat(); 
		document.getElementById('lng').value=latLng.lng();
		latlang = latLng.latitude+','+latLng.longitude
		get_address(latlang);
    }
	function geocodePosition(pos) 
	{
		geocoder.geocode({
			latLng: pos
		}, function(responses) {
			if (responses && responses.length > 0) {
			} else {

			}
		});
    }
	function dragmarker(marker)
    {
		google.maps.event.addListener(marker, 'dragstart', function() {
			updateMarkerAddress('Searching...');
		});
		google.maps.event.addListener(marker, 'drag', function() {
			updateMarkerStatus('Dragging...');
			updateMarkerPosition(marker.getPosition());
		});
		google.maps.event.addListener(marker, 'dragend', function() {
			updateMarkerStatus('Drag ended');
			geocodePosition(marker.getPosition());
		});
    }
	function updateMarkerAddress(str)
	{}
    function updateMarkerPosition(latLng) {
		document.getElementById('lat').value=latLng.lat(); 
		document.getElementById('lng').value=latLng.lng();
		
		latlang = latLng.lat()+','+latLng.lng();
		get_address(latlang);
    }
	function error(msg) 
	{
		var s = document.querySelector('#status');
		s.innerHTML = typeof msg == 'string' ? msg : "failed";
		s.className = 'fail';
	}

	function get_address(lat_lang)
	{
		$.ajax({ url:'https://maps.googleapis.com/maps/api/geocode/json?latlng='+latlang+'&sensor=true&key=AIzaSyCHpZueiSJJqJKuYi8je0ShnLhFcY9zJTw',
		/*$.ajax({ url:'http://maps.googleapis.com/maps/api/geocode/json?latlng='+latlang+'&sensor=true',*/
			 success: function(data){
				if(data.results.length>0)
				{
					$("#found_address").val(data.results[0].formatted_address);
				}
			 }
		});
		return true;
	}
	function disableEnterKey(e)
    {
		var key;
		if(window.event)
			key = window.event.keyCode; //IE
		else
			key = e.which; //firefox
		return (key != 13);
    }
</script>
@endsection
