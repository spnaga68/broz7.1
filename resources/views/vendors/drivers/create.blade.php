@extends('layouts.vendors')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/jquery-ui-1.10.3.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script> 
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/bootstrap-timepicker.min.js') }}"></script>
<link href="{{ URL::asset('assets/admin/base/css/bootstrap-timepicker.min.css') }}" media="all" rel="stylesheet" type="text/css" /> 
<link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>

<div class="row">
    <div class="col-md-12 ">
        <!-- Nav tabs -->
        <div class="pageheader">
            <div class="media">
                <div class="pageicon pull-left">
                    <i class="fa fa-home"></i>
                </div>
                <div class="media-body">
                    <ul class="breadcrumb">
                        <li><a href="{{ URL::to('vendors/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
                        <li>@lang('messages.Drivers')</li>
                    </ul>
                    <h4>@lang('messages.Add Driver')</h4>
                </div>
            </div><!-- media -->
        </div><!-- pageheader -->
        <div class="contentpanel">
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
            <ul class="nav nav-tabs"></ul>

            {!!Form::open(array('url' => 'create_vendordriver', 'method' => 'post', 'class' => 'tab-form attribute_form', 'id' => 'create_driver_form', 'files' => true));!!} 
                <div class="tab-content mb30">
                    <div class="tab-pane active" id="home3">
                       <?php /** <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Social Title') <span class="asterisk">*</span></label>
                            <div class="col-sm-1">
                                <select name="social_title" id="social_title" class="form-control">
                                    <option value="Mr.">@lang('messages.Mr.')</option>
                                    <option value="Mrs.">@lang('messages.Mrs.')</option>
                                    <option value="Ms.">@lang('messages.Ms.')</option>
                                </select>
                            </div>
                        </div> **/ ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.First Name') <span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <input type="text" name="first_name" maxlength="30" placeholder="@lang('messages.First Name')" class="form-control" value="{!! old('first_name') !!}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Last Name') <span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <input type="text" name="last_name" maxlength="30" placeholder="@lang('messages.Last Name')" class="form-control" value="{!! old('last_name') !!}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Email') <span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <input type="text" name="email" maxlength="100" placeholder="@lang('messages.Email')" class="form-control" value="{!! old('email') !!}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Password') <span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <div class="input-group">
                                    <input type="password" maxlength="32" class="form-control" name="user_password" autocomplete="off" value="" placeholder="">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                </div>
                                <span class="help-block">@lang('messages.Password length must be between 5 to 32 characters')</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Mobile')</label>
                            <div class="col-sm-6">
                              <input type="text" name="mobile" maxlength="12" placeholder="@lang('messages.Mobile')" class="form-control" value="{!! old('mobile') !!}" />
                              <span class="help-block">@lang('messages.Add Phone number(s) in comma seperated. <br>For example: 9750550341,9791239324')</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Date of birth')</label>
                            <div class="col-sm-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="date_of_birth" autocomplete="off" value="{!! old('date_of_birth') !!}" placeholder="mm/dd/yyyy" id="datepicker" readonly>
                                    <span class="input-group-addon datepicker-trigger"><i class="glyphicon glyphicon-calendar" id="dob"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Gender') <span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <select name="gender" class="form-control">
                                    <option value="" >@lang('messages.Select Gender')</option>
                                    <option value="M" <?php if("M"==old('gender')){ echo "selected=selected"; } ?> >@lang('messages.Male')</option>
                                    <option value="F" <?php if("F"==old('gender')){ echo "selected=selected"; } ?>>@lang('messages.Female')</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Country')</label>
                            <div class="col-sm-6">
                                <select id="country_id" class="select2-offscreen"  style="width:100%;" name="country" tabindex="-1" title="">
                                    <option value="">@lang('messages.Select Country')</option>
                                    @if (count(getCountryLists()) > 0)
                                        @foreach (getCountryLists() as $country)
                                            <option value="{{ $country->id }}" <?php if($country->id==old('country')){ echo "selected=selected"; } ?> >{{ ucfirst($country->country_name) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.City')</label>
                            <div class="col-sm-6">
                                <select name="city" id="city_id" class="select2-offscreen"  style="width:100%;" tabindex="-1" title="">
                                    <option value="">@lang('messages.Select City')</option>
                                    @if (count(getCityList($country->id)) > 0)
                                        @foreach (getCityList($country->id) as $city)
                                            <option value="{{ $city->id }}" <?php if($city->id == old('city')){ echo "selected=selected"; } ?> >{{ ucfirst($city->city_name) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="vendor" id="vendor_id" value="<?php echo Session::get('vendor_id'); ?>">

                      
                        <div class="form-group">
                            <label  class="col-sm-2 control-label">@lang('messages.Contact Address') <span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <input id="target" type="text" onKeyPress="return disableEnterKey(event)" name="address" value="" placeholder="Search your place and press enter"  class=" input-text select_place validate-place-map form-control"  maxlength="100"/>
                                <div id="map" style=" width: 400px; height:400px;margin-top:10px;float:left;"></div>
                                <div class="gllpMap"></div>
                                <input type="hidden" name="latitude" class="gllpLatitude" id="lat" value="" >
                                <input type="hidden" name="longitude" class="gllpLongitude" id="lng" value="" >
                                <input type="hidden" class="gllpZoom" value="18"/>
                                <input type="hidden" class="gllpUpdateButton" value="update map">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Image')</label>
                            <div class="col-sm-6">
                                <input type="file" name="image" />
                                <span class="help-text">@lang('messages.Please upload 75X75 images for better quality')</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label  class="col-sm-2 control-label">@lang('messages.Status')</label>
                            <div class="col-sm-6">
                                <input type="checkbox" class="toggle" name="active_status" data-size="small" data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
                            </div>
                        </div>
                        <div class="form-group">
                        <label class="col-sm-2 control-label">@lang('messages.Is Verified')</label>
                        <div class="col-sm-6">
                            <input type="checkbox" class="toggle" name="is_verified" data-size="small" data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
                        </div>
                    </div>

                  
                    </div>
                    <div class="panel-footer">
                        <button class="btn btn-primary mr5" title="Save">@lang('messages.Save')</button>
                        <button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('vendors/drivers') }}'">@lang('messages.Cancel')</button>
                    </div>
                </div>
            {!!Form::close();!!}
        </div>
    </div>
</div>
<script> 
    $(window).load(function(){
        $('#datepicker').datepicker({
            yearRange: '<?php echo date("Y") - 100; ?>:<?php echo date("Y"); ?>',
            maxDate: new Date(),
            changeMonth: true,
            changeYear: true
        });
        $(".datepicker-trigger").on("click", function() {
            $("#datepicker").datepicker("show");
        });
        $("select[name='social_title']").change(function(){
            if ($(this).val() =='Mrs.' || $(this).val() =='Ms.') {
                $("select[name='gender']").val("F");
            }
            if ($(this).val() =='Mr.') {
                $("select[name='gender']").val("M");
            }
        });
    });

   /*$(document).ready(function() {

         $("#Driver").click(function () {
            if ($(this).is(":checked")) {
                //$("#dvPassport").show();
                
                alert("helllo");
            // } else {
            //     //$("#dvPassport").hide();

           }
        });
});
*/
    $(document).ready(function(){
        $("#country_id").select2();
        var city_id="<?php echo $settings->default_city;?>";
        $("#city_id").select2();
    });
    $('#country_id').change(function(){
        var cid, token, url, data;
        token = $('input[name=_token]').val();
        cid   = $('#country_id').val();
        url   = '{{url('drivers/CityList')}}';
        data  = {cid: cid};
        $.ajax({
            url: url,
            headers: {'X-CSRF-TOKEN': token},
            data: data,
            type: 'POST',
            datatype: 'JSON',
            success: function (resp) {
                $('#city_id').empty();
                if(resp.data!='')
                { 
                    $('#select2-chosen-2').html('Select City');
                    $.each(resp.data, function(key, value)
                    {
                        //console.log(value['id']+'=='+value['city_name']);
                        $('#city_id').append($("<option></option>").attr("value",value['id']).text(value['city_name'])); 
                    });
                }
                else {
                    $('#select2-chosen-2').html('No Matches Found');
                }
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
        var latLng = new google.maps.LatLng(29.3167227, 48.002007100000014);
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
