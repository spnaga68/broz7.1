@extends('layouts.vendors')
@section('content')
<link href="{{ URL::asset('assets/admin/base/css/bootstrap-timepicker.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/admin/base/css/select2.css') }}" media="all" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" /> 
<!-- Nav tabs -->
<div class="pageheader">
<div class="media">
	<div class="pageicon pull-left">
		<i class="fa fa-home"></i>
	</div>
	<div class="media-body">
		<ul class="breadcrumb">
			<li><a href="#"><i class="glyphicon glyphicon-home"></i>@lang('messages.Vendors')</a></li>
			<li>@lang('messages.Outlets')</li>
		</ul>
		<h4>@lang('messages.Edit Outlet') - {{ucfirst($infomodel->getLabel('outlet_name',getAdminCurrentLang(),$data->id))}}</h4>
	</div>
</div><!-- media -->
</div><!-- pageheader -->

<div class="contentpanel">
<div class="col-md-12">
<div class="row panel panel-default">
<div class="grid simple">
	@if (count($errors) > 0)
	<div class="alert alert-danger">
			<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>
		<ul>
			@foreach ($errors->all() as $error)
				<li><?php echo trans('messages.'.$error); ?> </li>
			@endforeach
		</ul>
	</div>
	@endif
	<ul class="nav nav-justified nav-wizard nav-pills">
		<li @if(old('tab_info')=='login_info' || old('tab_info')=='') class="active" @endif><a href="#login_info" class="login_info" data-toggle="tab"><strong>@lang('messages.Outlet Information')</strong></a></li>
		<li @if(old('tab_info')=='delivery_info') class="active" @endif ><a href="#delivery_info" class="delivery_info" data-toggle="tab"><strong>@lang('messages.Delivery Information')</strong></a></li>
		<li @if(old('tab_info')=='vendor_info') class="active" @endif ><a href="#vendor_info" class="vendor_info" data-toggle="tab"><strong>@lang('messages.Opening Hours')</strong></a></li>
		<?php /*<li @if(old('tab_info')=='contact_info') class="active" @endif ><a href="#contact_info" class="contact_info" data-toggle="tab"><strong>@lang('messages.Delivery Hours')</strong></a></li>*/?>
	</ul>
	{!!Form::open(array('url' => ['vendor/update_outlet',$data->id], 'method' => 'post','class'=>'panel-wizard','id'=>'outlet_edit_form','files' => true));!!}
	<div class="tab-content tab-content-simple mb30 no-padding" >
        <div class="tab-pane active" id="login_info">
				<legend>@lang('messages.Outlet Information')</legend>
				<div class="form-group">
					<label class="col-sm-3 control-label">@lang('messages.Outlet Name') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<?php /*<input type="text" name="outlet_name" value="{!! $data->outlet_name !!}"  maxlength="255" placeholder="@lang('messages.Outlet Name')"  class="form-control" />*/ ?>
						<?php $i = 0; foreach($languages as $langid => $language):?>
							<div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
								<input type="text" name="outlet_name[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>" placeholder="<?php echo trans('messages.Outlet Name').' '.trans('messages.'.'('.$language->name.')');?>" class="form-control" value="{{$infomodel->getLabel('outlet_name',$language->id,$data->id)}}" maxlength="50" />
								<div class="input-group-btn">
									<button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button"><?php echo $language->name;?> <span class="caret"></span></button>
									<ul class="dropdown-menu pull-right">
										<?php foreach($languages as $sublangid => $sublanguage):?>
											<li><a href="javascript:YL.Language.fieldchange(<?php echo $sublanguage->id;?>)"> <?php echo trans('messages.'.$sublanguage->name);?></a></li>
										<?php endforeach;?>
									</ul>
								</div><!-- input-group-btn -->
							</div>
						<?php $i++; endforeach;?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">@lang('messages.Country') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<select name="country" id="country_id" class="form-control" >
							<option value="">@lang('messages.Select Country')</option>
							@foreach($countries as $list)
								<option value="{{$list->id}}" <?php echo ($data->country_id==$list->id)?'selected="selected"':''; ?> >{{$list->country_name}}</option>
							@endforeach
						</select>
					</div>
			   </div>
				<div class="form-group">
					<label class="col-sm-3 control-label">@lang('messages.City') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<select name="city" id="city_id" class="form-control" >
							<option value="">@lang('messages.Select City')</option>
							<?php $city = getCityList($data->country_id); ?>
								@foreach($city as $list)
									<option value="{{$list->id}}" <?php echo ($data->city_id==$list->id)?'selected="selected"':''; ?> >{{$list->city_name}}</option>
								@endforeach
						</select>
					</div>
			   </div>
				<div class="form-group">
					<label class="col-sm-3 control-label">@lang('messages.Zone') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<select name="location" id="location_id" class="form-control">
							<option value="">@lang('messages.Select Zone')</option>
							<?php $location = getLocationList($data->country_id,$data->city_id); ?>
								@foreach($location as $list)
									<option value="{{$list->id}}" <?php echo ($data->location_id==$list->id)?'selected="selected"':''; ?> >{{$list->zone_name}}</option>
								@endforeach
						</select>
					</div>
			    </div>
				<div class="form-group">
					<label class="col-sm-3 control-label">@lang('messages.Contact Phone') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
					  <input type="text" name="contact_phone_number" value="{!! $data->contact_phone !!}"  maxlength="12" placeholder="@lang('messages.Contact Phone')"  class="form-control" />
					</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Contact Email') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<input type="text" name="contact_email" value="{!! $data->contact_email !!}"  maxlength="100" placeholder="@lang('messages.Contact Email')"  class="form-control"  />
					</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Contact Address') <span class="asterisk">*</span></label>
                       <div class="col-sm-7">
						<input id="target" type="text" onKeyPress="return disableEnterKey(event)" name="contact_address" value="{!! $data->contact_address !!}" placeholder="Search your place and press enter"  class="input-text select_place validate-place-map form-control" />
						<div id="map" style=" width: 565px; height:400px;margin-top:10px;float:left;"></div>
						<div class="gllpMap"></div>
						<input type="hidden" name="latitude" class="gllpLatitude" id="lat" value="" >
						<input type="hidden" name="longitude" class="gllpLongitude" id="lng" value="" >
						<input type="hidden" class="gllpZoom" value="18"/>
						<input type="hidden" class="gllpUpdateButton" value="update map">
                    </div>
				</div>
				<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Status') </label>
					<div class="col-sm-7">
						<?php $checked1 = "";
						if($data->active_status){ $checked1 = "checked=checked"; } ?>
						<input type="checkbox" class="toggle" name="active_status" data-size="small" <?php echo $checked1;?> data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
					</div>
				</div>
			</div>
			
			<div class="tab-pane" id="delivery_info">
				<legend>@lang('messages.Delivery Hours')</legend>
				<div class="form-group">
					<label class="col-sm-3 control-label ">@lang('messages.Delivery Zones') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
					<?php  $old =old('delivery_areas'); $tcate= explode(',',$data->delivery_areas); if($old){  $tcate=$old; } ?>
						<select id="delivery_areas"  name="delivery_areas[]"  data-placeholder="@lang('messages.Delivery Zones')" multiple class="width300">
						<?php $location_areas = getLocationList($data->country_id,$data->city_id); ?>
						@foreach ($location_areas as $val)
							<option value="{{ $val->id }}" <?php echo in_array($val->id,$tcate)?'selected="selected"':''; ?> >{{  ucfirst($val->zone_name) }}</option>	
						@endforeach
						</select>
					</div> 
				</div>
				<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Delivery Time') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<input type="text" name="delivery_time" value="{!! $data->delivery_time !!}"  maxlength="4" placeholder="@lang('messages.Delivery Time')"  class="form-control" />
						<span class="help-block">@lang('messages.Delivery Time')@lang('messages.Like 30 mins,1 hour')</span>
					</div>
				</div>
					<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Pickup Time') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<input type="text" name="pickup_time" value="{!! $data->pickup_time !!}"  maxlength="4" placeholder="@lang('messages.Pickup Time')"  class="form-control" />
						<span class="help-block">@lang('messages.Pickup Time')@lang('messages.Like 30 mins,1 hour')</span>
					</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Cancel Time') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<input type="text" name="cancel_time" value="{!! $data->cancel_time !!}"  maxlength="4" placeholder="@lang('messages.Cancel Time')"  class="form-control" />
						<span class="help-block">@lang('messages.Cancel Time')@lang('messages.Like 30 mins,1 hour')</span>
					</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Return Time') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<input type="text" name="return_time" value="{!! $data->return_time !!}"  maxlength="4" placeholder="@lang('messages.Return Time')"  class="form-control" />
						<span class="help-block">@lang('messages.Return Time')@lang('messages.Like 1 or 2 days')</span>
					</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Delivery Charges Fixed') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<input type="text" name="delivery_charges_fixed" value="{!! $data->delivery_charges_fixed !!}"  maxlength="5" placeholder="@lang('messages.Delivery Charges Fixed')"  class="form-control" />
						<span class="help-block">@lang('messages.Delivery Charges Fixed')@lang('messages.Fixed for 5km')</span>
					</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Delivery Cost Variation') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<input type="text" name="delivery_cost_variation" value="{!! $data->delivery_charges_variation !!}"  maxlength="5" placeholder="@lang('messages.Delivery Cost Variation')"  class="form-control" />
						<span class="help-block">@lang('messages.Delivery Cost Variation')@lang('messages.After 5km every km')</span>
					</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Service Tax') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<input type="text" name="service_tax" value="{!! $data->service_tax!!}"  maxlength="5" placeholder="@lang('messages.Service Tax')"  class="form-control"  />
						<span class="help-block">@lang('messages.Service Tax')@lang('messages.Like 5 or 10 percentage')</span>
					</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Minimum Order Amount') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<input type="text" name="minimum_order_amount" value="{!! $data->minimum_order_amount !!}"  maxlength="5" placeholder="@lang('messages.Minimum Order Amount')"  class="form-control"  />
						<span class="help-block">@lang('messages.Minimum Order Amount')@lang('messages.Min amount 5')</span>
					</div>
				</div>				
			</div>
			<div class="tab-pane" id="vendor_info">
				<legend>@lang('messages.Opening Hours')</legend>
				<?php
				$open_time_array = getDaysWeekArray();
				foreach($open_time_array as $key1 => $val1) {
					$u_time = getOpenTimings($data->id,$val1);
				?> 
				<div id="value-<?php echo $key1;  ?>" class="row mb15">				
					<label class="col-sm-2 pt10" for="opening_timing_<?php echo $key1; ?>"> <input type="checkbox"  id="opening_timing_<?php echo $key1; ?>" value='1' <?php echo (isset($u_time[0]->day_week) && $u_time[0]->day_week==$val1)?'checked="checked"':'';?> name="opening_timing[<?php echo $key1; ?>][istrue]" > <?php echo $key1; ?>  </label>
				<div class="row">
					<div class="col-sm-2">
						 <div class="input-group mb15">
								<span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
								 <div class="bootstrap-timepicker">
									<input type="text" value="<?php echo (isset($u_time[0]->start_time))?date("h:i a", strtotime($u_time[0]->start_time)):''; ?>" name="opening_timing[<?php echo $key1; ?>][from]" class="timepicker form-control">
								 </div>
						</div>
					</div>
					<div class="pt10 pull-left">@lang('messages.To')</div>
						<div class="col-sm-2">
							<div class="input-group mb15">
								  <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
								  <div class="bootstrap-timepicker"> 
									<input type="text"  value="<?php echo isset($u_time[0]->end_time)?date("h:i a", strtotime($u_time[0]->end_time)):''; ?>" name="opening_timing[<?php echo $key1; ?>][to]" class="timepicker form-control">
								  </div>
							</div>
						</div> 
					</div>
				</div>
			<?php }  ?>	
			</div>
			<?php /*<div class="tab-pane" id="contact_info">
				<legend>@lang('messages.Delivery Hours')</legend>
				<?php
				$delivery_time_array = getDaysWeekArray();
				foreach($delivery_time_array as $key1 => $val1) {
					$u_time = getDeliveryTimings($data->id,$val1);
				?> 
				<div id="value-<?php echo $key1;  ?>" class="row mb15">				
					<label class="col-sm-2 pt10" for="delivery_timing_<?php echo $key1; ?>"> <input type="checkbox"  id="delivery_timing_<?php echo $key1; ?>" value='1' <?php echo (isset($u_time[0]->day_week) && $u_time[0]->day_week==$val1)?'checked="checked"':'';?> name="delivery_timing[<?php echo $key1; ?>][istrue]" > <?php echo $key1; ?>  </label>
				<div class="row">
					<div class="col-sm-2">
						 <div class="input-group mb15">
								<span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
								 <div class="bootstrap-timepicker">
									<input type="text" value="<?php echo (isset($u_time[0]->start_time))?date("h:i a", strtotime($u_time[0]->start_time)):''; ?>" name="delivery_timing[<?php echo $key1; ?>][from]" class="timepicker form-control">
								 </div>
						</div>
					</div>
					<div class="pt10 pull-left">@lang('messages.To')</div>
						<div class="col-sm-2">
							<div class="input-group mb15">
								  <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
								  <div class="bootstrap-timepicker"> 
									<input type="text"  value="<?php echo isset($u_time[0]->end_time)?date("h:i a", strtotime($u_time[0]->end_time)):''; ?>" name="delivery_timing[<?php echo $key1; ?>][to]" class="timepicker form-control">
								  </div>
							</div>
						</div> 
					</div>
				</div>
			<?php }  ?>	
			</div>
			</div>*/?>
			<div class="form-group Loading_Img" style="display:none;">
				<div class="col-sm-3">
					<i class="fa fa-spinner fa-spin fa-3x"></i><strong style="margin-left: 3px;">@lang('messages.Processing...')</strong>
				</div>
			</div>
		</div>
		<!-- panel-body -->
			<div class="panel-footer">
				<input type="hidden" name="tab_info" class="tab_info" value="">
				<button class="btn btn-primary mr5" title="Save">@lang('messages.Save')</button>
				<button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('vendor/outlets') }}'">@lang('messages.Cancel')</button>
			</div><!-- panel-footer -->
		</div><!-- panel-default -->
		{!!Form::close();!!} 
	</div>
</div>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/jquery-ui-1.10.3.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/bootstrap-timepicker.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>
<script type="text/javascript">
$( document ).ready(function() {
	$('#delivery_areas').select2();
	// Time Picker
	$('.timepicker').timepicker({defaultTIme: false});
	@if(old('tab_info')=='vendor_info')
		$('.tab_info').val('vendor_info');
		$('#login_info').hide();
		$('#delivery_info').hide();
		$('#contact_info').hide();
		$('#vendor_info').show();
	@elseif(old('tab_info')=='login_info')
		$('.tab_info').val('login_info');
		$('#login_info').show();
		$('#delivery_info').hide();
		$('#contact_info').hide();
		$('#vendor_info').hide();
	@elseif(old('tab_info')=='delivery_info')
		$('.tab_info').val('delivery_info');
		$('#login_info').hide();
		$('#delivery_info').show();
		$('#contact_info').hide();
		$('#vendor_info').hide();
	@elseif(old('tab_info')=='contact_info')
		$('.tab_info').val('contact_info');
		$('#login_info').hide();
		$('#delivery_info').hide();
		$('#contact_info').show();
		$('#vendor_info').hide();
	@endif
});
$(".login_info").on("click", function(){
	$('.tab_info').val('login_info');
	$('#delivery_info').hide();
	$('#vendor_info').hide();
	$('#login_info').show();
	$('#contact_info').hide();
});
$(".vendor_info").on("click", function(){
	$('.tab_info').val('vendor_info');
	$('#delivery_info').hide();
	$('#vendor_info').show();
	$('#login_info').hide();
	$('#contact_info').hide();
});
$(".delivery_info").on("click", function(){
	$('.tab_info').val('delivery_info');
	$('#delivery_info').show();
	$('#vendor_info').hide();
	$('#login_info').hide();
	$('#contact_info').hide();
});
$(".contact_info").on("click", function(){
	$('.tab_info').val('contact_info');
	$('#delivery_info').hide();
	$('#vendor_info').hide();
	$('#login_info').hide();
	$('#contact_info').show();
});
$(window).load(function(){	
	$('form').preventDoubleSubmission();	
});
$('#country_id').change(function(){
	var cid, token, url, data;
	token = $('input[name=_token]').val();
	cid = $('#country_id').val();
	url = '{{url('list/CityList')}}';
	data = {cid: cid};
	$.ajax({
		url: url,
		headers: {'X-CSRF-TOKEN': token},
		data: data,
		type: 'POST',
		datatype: 'JSON',
		success: function (resp) {
			//console.log('in--'+resp);
			$('#city_id').empty();
			$.each(resp.data, function(key, value) {
				//console.log(value['id']+'=='+value['city_name']);
				$('#city_id').append($("<option></option>").attr("value",value['id']).text(value['city_name'])); 
		   });
		}
	});
});
$('#city_id').change(function(){
	var city_id, country_id, token, url, data;
	token = $('input[name=_token]').val();
	country_id = $('#country_id').val();
	city_id = $('#city_id').val();
	url = '{{url('list/LocationList')}}';
	console.log(city_id+'--'+country_id);
	data = {city_id: city_id,country_id:country_id};
	$.ajax({
		url: url,
		headers: {'X-CSRF-TOKEN': token},
		data: data,
		type: 'POST',
		datatype: 'JSON',
		success: function (resp) {
			//console.log('in--'+resp.data);
			$('#location_id,#delivery_areas').empty();
			$('#s2id_delivery_areas .select2-choices .select2-search-choice').remove();
			if(resp.data==''){
				$('#location_id,#delivery_areas').append($("<option></option>").attr("value","").text('No data there..')); 
			} else {
				$.each(resp.data, function(key, value) {
					//console.log(value['id']+'=='+value['city_name']);
					$('#location_id,#delivery_areas').append($("<option></option>").attr("value",value['id']).text(value['zone_name'])); 
			   });
			}
		}
	});
});
</script>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?libraries=places&key=AIzaSyCHpZueiSJJqJKuYi8je0ShnLhFcY9zJTw"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/jquery-gmaps-latlon-picker.js') }}"></script>
<script type="text/javascript">
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
    function updateMarkerStatus(str) {
    }
    function updateMarkerPosition(latLng) {
		document.getElementById('lat').value=latLng.lat(); 
		document.getElementById('lng').value=latLng.lng();
    }
    function initialize() {
		var latLng = new google.maps.LatLng(<?php echo $data->latitude?$data->latitude:29.3167227; ?>, <?php echo $data->longitude?$data->longitude:48.002007100000014; ?>);
		var map = new google.maps.Map(document.getElementById('map'), {
			zoom: 10,
			center: latLng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		});
		google.maps.event.trigger(map, "resize");
		var marker = new google.maps.Marker({
			position: latLng,
			title: 'Drag this Marker',
			map: map,
			draggable: true
		});
		var input = /** @type {HTMLInputElement}*/(document.getElementById('target'));
		var searchBox = new google.maps.places.SearchBox(input);
		var markers = [];
		google.maps.event.addListener(searchBox, 'places_changed', function() {
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
			}
			map.fitBounds(bounds);
		});
		  
		// Update current position info.
		updateMarkerPosition(latLng);
		geocodePosition(latLng);
		dragmarker(marker); 
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
    // Onload handler to fire off the app.
    google.maps.event.addDomListener(window, 'load', initialize); 
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
