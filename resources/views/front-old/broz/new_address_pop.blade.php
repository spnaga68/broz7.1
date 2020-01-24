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
										{!!Form::open(array('url' => ['store-address-ajax'], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'store-address-ajax','onsubmit'=>'return store_address()'));!!}
									
										<div class="location_product">
										<div class="locat_new_popup">
										
										<div class="col-md-4 padding_right0">
										<div class="location_text_feeld">
											<div class="form-group"> 
												{{ Form::select('address_type', $address_types, 'null', ['class' => 'address_type select_dropdown form-control ','id'=>'address_type'] ) }}
											</div>
										</div>
										</div>
										<div class="col-md-6 padding0">
										<div class="location_text_feeld">
										<div class="input-group"> 
															<input type="text" id="found_address1" name ="address" placeholder="@lang('messages.Address')" id="address" class="form-control"> 
														
														
														<input type="hidden" id="lat" name ="latitude" placeholder="@lang('messages.Name')" id="name" class="form-control"> 
														<input type="hidden" id="lng" name ="longtitude" placeholder="@lang('messages.Name')" id="name" class="form-control"> 
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
														<button type="submit" class="btn btn-default" title="@lang('messages.Save')">@lang('messages.Save')</button>
														<button type="button" class="btn btn-primary cancel_button" data-url="cards" title="@lang('messages.Cancel')">@lang('messages.Cancel')</button>
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
							
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->

<script src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyDCdWohFBZcQISBioA24aUqHNEviHDGpCk"></script>
<script type="text/javascript">
    var geocoder = new google.maps.Geocoder();
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
        //~ generate_map('21.285407', '39.237551');
        generate_map('0.0', '0.00');
    });
    var markersArray = [];

    function generate_map(lat,lng)
    {
        var mapcanvas = document.createElement('div');
        mapcanvas.id = 'mapcanvas';
        mapcanvas.style.height = '300px';
        mapcanvas.style.width = '100%';
        document.querySelector('article').appendChild(mapcanvas);
        var latlng = new google.maps.LatLng(lat,lng);
        var myOptions = {
        zoom: 12,
        center: latlng,
        mapTypeControl: false,
        navigationControlOptions: {style: google.maps.NavigationControlStyle.SMALL},
        mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map(document.getElementById("mapcanvas"), myOptions);

        var marker = new google.maps.Marker({
            position: latlng, 
            map: map, 
            title:"<?php echo trans('messages.Drag me to change the address');?>",
            draggable: true
        });
        
        var input = (document.getElementById('found_address1'));
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
            map.setZoom(16);
        });
        markersArray.push(marker);
        latlang = lat+','+lng;
        get_address(latlang);
        updateMarkerPosition(latlng);
        geocodePosition(latlng);
        dragmarker(marker); 
    }
    function clearOverlays()
    {
        for (var i = 0; i < markersArray.length; i++ ) {
            markersArray[i].setMap(null);
        }
        markersArray.length = 0;
    }
    function dragmarker(marker)
    {
        google.maps.event.addListener(marker, 'dragstart', function() {
            updateMarkerAddress('<?php echo trans('messages.Searching...');?>');
        });
        google.maps.event.addListener(marker, 'drag', function() {
            updateMarkerStatus('<?php echo trans('messages.Dragging...');?>');
            updateMarkerPosition(marker.getPosition());
        });
        google.maps.event.addListener(marker, 'dragend', function() {
            updateMarkerStatus('Drag ended');
            geocodePosition(marker.getPosition());
        });
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
    function updateMarkerStatus(str)
    {
    }
    function updateMarkerPosition(latLng)
    {
        document.getElementById('lat').value=latLng.lat(); 
        document.getElementById('lng').value=latLng.lng();
        latlang = latLng.lat()+','+latLng.lng();
        get_address(latlang);
    }
    function get_address(lat_lang)
    {
        $.ajax({ 
            url:'https://maps.googleapis.com/maps/api/geocode/json?latlng='+latlang+'&sensor=true&key=AIzaSyDCdWohFBZcQISBioA24aUqHNEviHDGpCk',
            success: function(data){
                if(data.results.length > 0)
                {
                    $("#found_address1").val(data.results[0].formatted_address);
                }
            }
        });
        return true;
    }
    function success(position) 
    {
        var s = document.querySelector('#status');
        if (s.className == 'success') {
            // not sure why we're hitting this twice in FF, I think it's to do with a cached result coming back    
            return;
        }
        generate_map(position.coords.latitude, position.coords.longitude);
    }
    function error(msg) 
    {
        var s = document.querySelector('#status');
        s.innerHTML = typeof msg == 'string' ? msg : "failed";
        s.className = 'fail';
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
