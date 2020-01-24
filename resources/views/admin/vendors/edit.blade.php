@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script>
<link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>
 <link href="{{ URL::asset('assets/admin/base/css/select2.css') }}" media="all" rel="stylesheet" type="text/css" />
<!-- Nav tabs -->
<div class="pageheader">
<div class="media">
	<div class="pageicon pull-left">
		<i class="fa fa-home"></i>
	</div>
	<div class="media-body">
		<ul class="breadcrumb">
			<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
			<li>@lang('messages.Vendors')</li>
		</ul>
		<h4>@lang('messages.Edit Vendor') - {{ $infomodel->getLabel('vendor_name',getAdminCurrentLang(),$data->id) }}</h4>
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
		<li @if(old('tab_info')=='login_info' || old('tab_info')=='') class="active" @endif><a href="#login_info" class="login_info" data-toggle="tab"><strong>@lang('messages.Login Information')</strong></a></li>
		<li @if(old('tab_info')=='vendor_info') class="active" @endif ><a href="#vendor_info" class="vendor_info" data-toggle="tab"><strong>@lang('messages.Vendor Information')</strong></a></li>
		<li @if(old('tab_info')=='delivery_info') class="active" @endif ><a href="#delivery_info" class="delivery_info" data-toggle="tab"><strong>@lang('messages.Delivery Information')</strong></a></li>
		<li @if(old('tab_info')=='contact_info') class="active" @endif ><a href="#contact_info" class="contact_info" data-toggle="tab"><strong>@lang('messages.Contact Information')</strong></a></li>
	</ul>
	{!!Form::open(array('url' => ['update_vendor',$data->id], 'method' => 'post','class'=>'panel-wizard','id'=>'vendor_edit_form','files' => true));!!}
	<div class="tab-content tab-content-simple mb30 no-padding" >
        <div class="tab-pane active" id="login_info">
				<legend>@lang('messages.Login Information')</legend>
				<div class="form-group">
					<label class="col-sm-4 control-label">@lang('messages.First Name') <span class="asterisk">*</span></label>
					<div class="col-sm-8">
					  <input type="text" name="first_name" value="{!! $data->first_name !!}"  maxlength="30" placeholder="@lang('messages.First Name')"  class="form-control"  />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">@lang('messages.Last Name') <span class="asterisk">*</span></label>
					<div class="col-sm-8">
					  <input type="text" name="last_name" value="{!! $data->last_name !!}"  maxlength="30" placeholder="@lang('messages.Last Name')"  class="form-control"  />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">@lang('messages.Email') <span class="asterisk">*</span></label>
					<div class="col-sm-8">
					  <input type="text" name="email" value="{!! $data->email !!}"  maxlength="100" placeholder="@lang('messages.Email')"  id="disabledinput" class="form-control" readonly  />
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-sm-4 control-label">@lang('messages.Mobile Number') <span class="asterisk">*</span></label>
					<div class="col-sm-8">
					  <input type="text" name="mobile_number" value="{!! $data->mobile_number !!}"  maxlength="12" placeholder="@lang('messages.Mobile Number')"  class="form-control"  />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">@lang('messages.Phone Number') <span class="asterisk">*</span></label>
					<div class="col-sm-8">
					  <input type="text" name="phone_number" value="{!! $data->phone_number !!}"  maxlength="12" placeholder="@lang('messages.Phone Number')"  class="form-control" />
					</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-4 control-label">@lang('messages.Featured Vendor') </label>
					<div class="col-sm-8">
						<?php $checked = "";if($data->featured_vendor){ $checked = "checked=checked"; }?>
						<input type="checkbox" class="toggle" name="featured_vendor" data-size="small" <?php echo $checked;?> data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
					</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-4 control-label">@lang('messages.Status') </label>
					<div class="col-sm-8">
						<?php $checked1 = "";
						if($data->active_status){ $checked1 = "checked=checked"; } ?>
						<input type="checkbox" class="toggle" name="active_status" data-size="small" <?php echo $checked1;?> data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
					</div>
				</div>
			</div>
			<div class="tab-pane" id="vendor_info">
				<legend>@lang('messages.Vendor Information')</legend>
				<div class="form-group">
					<label class="col-sm-3 control-label">@lang('messages.Vendor Name') <span class="asterisk">*</span></label>
					<div class="col-sm-9">
					<?php $i = 0; foreach($languages as $langid => $language):?>
						<div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
							  <input type="text" name="vendor_name[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Vendor Name ').trans('messages.'.'('.$language->name.')');?>" class="form-control" value="{{$infomodel->getLabel('vendor_name',$language->id,$data->id)}}"  maxlength="30" />
						 
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
					<label class="col-sm-3 control-label">@lang('messages.Vendor Description') <span class="asterisk">*</span></label>
					<div class="col-sm-9">
						<?php $i = 0; foreach($languages as $langid => $language):?>
						<div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
							  <textarea name="vendor_description[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Vendor Description ').trans('messages.'.'('.$language->name.')');?>" class="form-control" rows="5">{{$infomodel->getLabel('vendor_description',$language->id,$data->id)}}</textarea>
						 
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
					<div class="col-sm-9">
						<select name="country" id="country_id" class="form-control" >
							<option value="">@lang('messages.Select Country')</option>
							@foreach($countries as $list)
								<option value="{{$list->id}}" <?php echo ($data->country_id==$list->id)?'selected="selected"':'';?>>{{$list->country_name}}</option>
							@endforeach
						</select>
					</div>
			   </div>
				<div class="form-group">
					<label class="col-sm-3 control-label">@lang('messages.City') <span class="asterisk">*</span></label>
					<div class="col-sm-9">
					<?php $city = getCityList($data->country_id); ?>
						<select name="city" id="city_id" class="form-control" >
							<option value="">@lang('messages.Select City')</option>
							@foreach($city as $list)
								<option value="{{$list->id}}" <?php echo ($data->city_id==$list->id)?'selected="selected"':'';?>>{{$list->city_name}}</option>
							@endforeach
						</select>
					</div>
			   </div>

		<div class="form-group">
                       <label  class="col-sm-3 control-label">@lang('messages.Address') <span class="asterisk">*</span></label>
                       <div class="col-sm-9">
                            <input id="target" type="text" onKeyPress="return disableEnterKey(event)" name="contact_address" value="{!! $data->contact_address !!}" placeholder="Search your place and press enter"  class=" input-text select_place validate-place-map form-control"  maxlength="100"/>
                            <div id="map" style=" width: 400px; height:400px;margin-top:10px;float:left;"></div>
                            <div class="gllpMap"></div>
                            <input type="hidden" name="latitude" class="gllpLatitude" id="lat" value="" >
                            <input type="hidden" name="longitude" class="gllpLongitude" id="lng" value="" >
                            <input type="hidden" class="gllpZoom" value="18"/>
                            <input type="hidden" class="gllpUpdateButton" value="update map">
                        </div>
            </div>
							
				<div class="form-group">
					<label class="col-sm-3 control-label">@lang('messages.Category') <span class="asterisk">*</span></label>
					<div class="col-sm-9">
						<?php  $old =old('category'); $cate= explode(',',$data->category_ids); if($old){  $cate=$old; } ?>
						<select id="categories"  name="category[]"  data-placeholder="@lang('messages.Select Category')" multiple class="width300">
						@foreach ($categories as $val)
							<option value="{{ $val->id }}" <?php echo in_array($val->id,$cate)?"selected" :"" ;?> >{{  ucfirst($val->category_name) }}</option>	
						@endforeach
						</select>
					</div>
			   </div> 
				<div class="form-group">
					<label class="col-sm-3 control-label">@lang('messages.Logo') </label>
					<div class="col-sm-6">
						<input type="file" name="logo" />
						<span class="help-text">@lang('messages.Please upload 253X133 images for better quality')</span>
						<div class="mb20"></div>
						<img src="<?php echo url('/assets/admin/base/images/vendors/logos/'.$data->logo_image.'?'.time()); ?>" class="thumbnail img-responsive" alt="No image">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">@lang('messages.Featured Image') </label>
					<div class="col-sm-6">
						<input type="file" name="featured_image" />
						<span class="help-text">@lang('messages.Please upload 640X232 images for better quality')</span>
						<div class="mb20"></div>
						<img src="<?php echo url('/assets/admin/base/images/vendors/thumb/'.$data->featured_image.'?'.time()); ?>" class="thumbnail img-responsive" alt="No image">
					</div>
				</div>
			</div>
			<div class="tab-pane" id="delivery_info">
				<legend>@lang('messages.Delivery Information')</legend>
				<div class="form-group">
					<label  class="col-sm-4 control-label">@lang('messages.Delivery Time') <span class="asterisk">*</span></label>
					<div class="col-sm-8">
						<input type="text" name="delivery_time" value="{!! $data->delivery_time !!}"  maxlength="4" placeholder="@lang('messages.Delivery Time')"  class="form-control" />
						<span class="help-block">@lang('messages.Delivery Time')@lang('messages.Like 30 mins,1 hour')</span>
					</div>
				</div>
					<div class="form-group">
					<label  class="col-sm-4 control-label">@lang('messages.Pickup Time') <span class="asterisk">*</span></label>
					<div class="col-sm-8">
						<input type="text" name="pickup_time" value="{!! $data->pickup_time !!}"  maxlength="4" placeholder="@lang('messages.Pickup Time')"  class="form-control" />
						<span class="help-block">@lang('messages.Pickup Time')@lang('messages.Like 30 mins,1 hour')</span>
					</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-4 control-label">@lang('messages.Cancel Time') <span class="asterisk">*</span></label>
					<div class="col-sm-8">
						<input type="text" name="cancel_time" value="{!! $data->cancel_time !!}"  maxlength="4" placeholder="@lang('messages.Cancel Time')"  class="form-control" />
						<span class="help-block">@lang('messages.Cancel Time')@lang('messages.Like 30 mins,1 hour')</span>
					</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-4 control-label">@lang('messages.Return Time') <span class="asterisk">*</span></label>
					<div class="col-sm-8">
						<input type="text" name="return_time" value="{!! $data->return_time !!}"  maxlength="4" placeholder="@lang('messages.Return Time')"  class="form-control" />
						<span class="help-block">@lang('messages.Return Time')@lang('messages.Like 1 or 2 days')</span>
					</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-4 control-label">@lang('messages.Delivery Charges Fixed') <span class="asterisk">*</span></label>
					<div class="col-sm-8">
						<input type="text" name="delivery_charges_fixed" value="{!! $data->delivery_charges_fixed !!}"  maxlength="5" placeholder="@lang('messages.Delivery Charges Fixed')"  class="form-control" />
						<span class="help-block">@lang('messages.Delivery Charges Fixed')@lang('messages.Fixed for 5km')</span>
					</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-4 control-label">@lang('messages.Delivery Cost Variation') <span class="asterisk">*</span></label>
					<div class="col-sm-8">
						<input type="text" name="delivery_cost_variation" value="{!! $data->delivery_cost_variation !!}"  maxlength="5" placeholder="@lang('messages.Delivery Cost Variation')"  class="form-control" />
						<span class="help-block">@lang('messages.Delivery Cost Variation')@lang('messages.After 5km every km')</span>
					</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-4 control-label">@lang('messages.Service Tax')</label>
					<div class="col-sm-8">
						<input type="text" name="service_tax" value="{!! $data->service_tax!!}"  maxlength="5" placeholder="@lang('messages.Service Tax')"  class="form-control"  />
						<span class="help-block">@lang('messages.Service Tax')@lang('messages.Like 5 or 10 percentage')</span>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="contact_info">
				<legend>@lang('messages.Contact Information')</legend>
				<div class="form-group">
					<label  class="col-sm-4 control-label">@lang('messages.Contact Email') <span class="asterisk">*</span></label>
					<div class="col-sm-8">
						<input type="text" name="contact_email" value="{!! $data->contact_email !!}"  maxlength="100" placeholder="@lang('messages.Contact Email')"  class="form-control"  />
					</div>
				</div>
				<?php /** 
				<div class="form-group">
					<label  class="col-sm-4 control-label">@lang('messages.Contact Address') <span class="asterisk">*</span></label>
					<div class="col-sm-8">
						<textarea name="contact_address" maxlength="255" placeholder="@lang('messages.Contact Address')"  class="form-control" rows="5" />{!! $data->contact_address !!}</textarea>
					</div>
				</div>
				*/ ?>
								
				
			</div>
			
			</div>
			
			<div class="form-group Loading_Img" style="display:none;">
				<div class="col-sm-4">
					<i class="fa fa-spinner fa-spin fa-3x"></i><strong style="margin-left: 3px;">@lang('messages.Processing...')</strong>
				</div>
			</div>
		</div>
		<!-- panel-body -->
			<div class="panel-footer">
				<input type="hidden" name="tab_info" class="tab_info" value="">
				<button class="btn btn-primary mr5" title="Save">@lang('messages.Save')</button>
				<button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('vendors/vendors') }}'">@lang('messages.Cancel')</button>
			</div><!-- panel-footer -->
    </div><!-- panel-default -->
	{!!Form::close();!!} 
		</div>
	</div>
	

<script>
$( document ).ready(function() {
	$('#categories').select2();
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
	initialize();
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
</script>
<script src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyAn_pLYhhBqRD1Cx_RzHLSAUe9PAclmTsw"></script>
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
