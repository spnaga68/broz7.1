@extends('layouts.admin')
@section('content')

<div class="pageheader">
    <div class="media">
        <div class="pageicon pull-left">
            <i class="fa fa-home"></i>
        </div>
        <div class="media-body">
            <ul class="breadcrumb">
                <li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
                <li>@lang('messages.Drivers Location')</li>
            </ul>
            <h4>@lang('messages.Drivers Location')</h4>
        </div>
    </div><!-- media -->
</div><!-- pageheader -->

<div class="contentpanel">
    {!!Form::open(array('url' => 'driver_track_location', 'method' => 'post', 'class' => 'tab-form attribute_form', 'id' => 'driver_track_location_form'));!!} 
        <div class="tab-pane active" id="home3">
            <div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Driver Availability') <span class="asterisk">*</span></label>
                <div class="col-sm-6">
                    <select name="driver_availability" id="driver_availability" onchange="location.href='{{url('/admin/driver-location?avail=')}}'.this.value">
                        <option value="">@lang('messages.All')</option>
                        <option value="1" <?php if(isset($_GET['avail']) && $_GET['avail'] == 1){ echo "selected";}?>>@lang('messages.Availability')</option>
                        <option value="2" <?php if(isset($_GET['avail']) && $_GET['avail'] == 2){ echo "selected";}?>>@lang('messages.Busy')</option>
                        <option value="3" <?php if(isset($_GET['avail']) && $_GET['avail'] == 3){ echo "selected";}?>>@lang('messages.Offline')</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <div class="google-map-wrap" itemscope itemprop="hasMap" itemtype="http://schema.org/Map">
                    <div id="google-map" class="google-map"></div><!-- #google-map -->
                </div>
            </div>
        </div>
    {!!Form::close();!!}
</div>
<script type="text/javascript" src="https://maps.google.com/maps/api/js?libraries=places&key=AIzaSyAn_pLYhhBqRD1Cx_RzHLSAUe9PAclmTsw"></script><!---->
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/jquery-gmaps-latlon-picker.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/gmaps.js') }}"></script>
<?php $locations = array();



if(count($driver_location_list)>0){
    foreach($driver_location_list as $driver) {
		
        $locations[] = array(
            'google_map' => array(
                'lat' => $driver->latitude,
                'lng' => $driver->longitude,
            ),
            'location_name' => ucfirst($driver->first_name)." ".ucfirst($driver->last_name),
            'orders' =>  $driver->orders,
        );
    }
} 
$map_content = "";
$map_area_lat = isset( $locations[0]['google_map']['lat'] ) ? $locations[0]['google_map']['lat'] : '';
$map_area_lng = isset( $locations[0]['google_map']['lng'] ) ? $locations[0]['google_map']['lng'] : '';
?>
<script>
    function change_driver_availability(avail)
    {
        url = "{{url('/admin/driver-location?avail=')}}"+avail.value;
        window.location.href=url;
    }
    jQuery( document ).ready( function() {
        /* Do not drag on mobile. */
        var is_touch_device = 'ontouchstart' in document.documentElement;

        var map = new GMaps({
            el: '#google-map',
            lat: '<?php echo $map_area_lat; ?>',
            lng: '<?php echo $map_area_lng; ?>',
            scrollwheel: false,
            draggable: ! is_touch_device
        });

        /* Map Bound */
        var bounds = [];

        <?php /* For Each Location Create a Marker. */
        foreach( $locations as $location )
		{
            $name = $location['location_name'];
            $map_lat = $location['google_map']['lat'];
            $map_lng = $location['google_map']['lng'];
			$map_content = '<p>'.$name.'</p>';
			$map_content .= '<table border = 1 style="border-spacing: 2px;">';
			$map_content .= '<tr><th style="padding: 6px 12px;">Order id</th><th style="padding: 6px 12px;">Name</th><th style="padding: 6px 12px;">Total</th></tr>';
			
			foreach($location['orders'] as $orders)
			{
				$map_content .= '<tr>';
				$map_content .= '<td style="padding: 6px 12px;">'.$orders->id.'</td>';
				$map_content .= '<td style="padding: 6px 12px;">'.$orders->name.'</td>';
				$map_content .= '<td style="padding: 6px 12px;">'.$orders->total_amount.'</td>';
				$map_content .= '<tr/>';
				
			}
			$map_content .= "</table>";
			?>
            /* Set Bound Marker */
            var latlng = new google.maps.LatLng(<?php echo $map_lat; ?>, <?php echo $map_lng; ?>);
            bounds.push(latlng);
            /* Add Marker */
            map.addMarker({
                lat: <?php echo $map_lat; ?>,
                lng: <?php echo $map_lng; ?>,
                title: '<?php echo $name; ?>',
                infoWindow: {
                    content: '<p><?php echo $map_content; ?></p>'
                }
            });
        <?php } //end foreach locations ?>
        /* Fit All Marker to map */
        map.fitLatLngBounds(bounds);
        /* Make Map Responsive */
        var $window = $(window);
        function mapWidth() {
            var size = $('.google-map-wrap').width();
            $('.google-map').css({width: size + 'px', height: (size/2) + 'px'});
        }
        mapWidth();
        $(window).resize(mapWidth);
    });
</script>
@endsection
