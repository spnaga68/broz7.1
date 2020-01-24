<style>
.pac-container {
    background-color: #FFF;
    z-index: 20;
    position: fixed;
    display: inline-block;
    float: left;
}
.modal{
    z-index: 20;   
}
.modal-backdrop{
    z-index: 10;        
}​
</style>
 <div class="modal fade model_for_signup bd-example-modal-lg18" id="address_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
					<h2>@lang('messages.Tell us about your location')</h2>
                   <?php /* <span class="logo_popup"><img alt="'.Session::get('general')->site_name.'" src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/'.Session::get("general")->theme.'.png'); ?>"></span> */ ?>
                </div>
                <div class="modal-body">
								<div class="edit_profile_section">
									<div class="add_new_address_pop">
									
										<div class="location_product">
										<div class="locat_new_popup">
										
								<div class="col-md-6">
                                        <div class="location_text_feeld">
                                            <div class="form-group">
                                               <input type="text" name="block" id="lat" placeholder="@lang('messages.Lattitude')" value="{{old('block')}}" class="form-control">     
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-6">
                                        <div class="location_text_feeld">
                                            <div class="form-group">
                                               <input type="text" name="block" id="lng" placeholder="@lang('messages.Longitude')" value="{{old('block')}}" class="form-control">     
                                            </div>
                                        </div>
                                </div>	

									<div class="col-md-6">
                                    <div class="location_text_feeld">
                                        <div class="input-group"> 
                                            <input type="text" id="found_address1" name ="address" placeholder="@lang('messages.Find your location')" class="form-control"> 
                                            <input type="hidden" id="lat" name ="latitude" class="form-control"> 
                                            <input type="hidden" id="lng" name ="longtitude" class="form-control"> 
                                        </div>
                                    </div>
                                </div>
										<div class="col-md-2 padding_left0">
<div class="location_butt">
	<span class="input-group-btn">
																<button class="btn btn-secondary" id="get_location" type="button">@lang('messages.Locate me')</button>
															</span>
</div>
										</div>
										</div>
										
										
											
										
												<div class="col-md-12">
													<article  style="width:100%; height: 330px; margin-top: 10px; float: left; position: relative;">
													  <p style="display:none;">@lang('messages.Finding your location:') <span id="status">@lang('messages.checking...')</span></p>
													</article>
												</div>
												<div class="col-md-12 padding0">
													<div class="button_sections">
														<button type="submit" onclick = "store_list()" class="btn btn-default" title="@lang('messages.Save')">@lang('messages.Go')</button>
														<?php /*<button type="button" class="btn btn-primary cancel_button" data-url="cards" title="@lang('messages.Cancel')">@lang('messages.Cancel')</button> */ ?>
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
							
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->

<script src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyDMe9JO_FXcbJBw8bQntPlLoV2MaJkfCno"></script>
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
		latitude = $('#lat').val();
        longitude = $('#lng').val();
		
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
		
		var input = (document.getElementById('found_address1'));
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
    
     function updateMarkerAddress(str)
    {
        document.getElementById('found_address1').value = str;
    }
       function updateMarkerStatus(str) {
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
		console.log(lat_lang)
		$.ajax({ url:'https://maps.googleapis.com/maps/api/geocode/json?latlng='+latlang+'&sensor=true&key=AIzaSyDMe9JO_FXcbJBw8bQntPlLoV2MaJkfCno',
		/*$.ajax({ url:'http://maps.googleapis.com/maps/api/geocode/json?latlng='+latlang+'&sensor=true',*/
			 success: function(data){
				if(data.results.length>0)
				{
					$("#found_address1").val(data.results[0].formatted_address);
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


     function store_list(position) {
		var latitude, longitude, token, c_url, cdata;
		latitude = $('#lat').val();
        longitude = $('#lng').val();
        language = '<?php echo getCurrentLang();?>';
	     c_url = '{{url("/stores_outlet")}}';
	    token = $('input[name=_token]').val();
		//console.log(token);
		//alert(token);//return false;
		
		cdata ={latitude:latitude,longitude:longitude,language:language};
		$.ajax({
			url: c_url,
			headers: {'X-CSRF-TOKEN': token},
			data: cdata,
			type: 'POST',
			dataType:"json",
			success: function (resp){
				if(resp.response.httpCode == 200)
				{ 
					window.location.replace('{{url("stores/")}}/'+resp.response.city_url_index+'/'+resp.response.location_url_index+'/'+resp.response.latitude+'/'+resp.response.longitude);
				}
				else
				{ 
					toastr.warning("<?php echo trans('messages.No stores avaiable in your location') ?>");
					return false;
				}
			},
			error: function(resp){
				toastr.warning("<?php echo trans('messages.No stores avaiable in your location') ?>");
                return false;
			}
        });
	}



</script>
