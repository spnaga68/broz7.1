<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title></title>
</head>
<body>
    Latitude:
    <input type="text" id="txtLatitude" value="18.92488028662047" />
    Latitude:
    <input type="text" id="txtLongitude" value="72.8232192993164" />
    <input type="button" value="Get Address" onclick="GetAddress()" />
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>
    <script type="text/javascript">
        function GetAddress() {
            var lat = parseFloat(document.getElementById("txtLatitude").value);
            var lng = parseFloat(document.getElementById("txtLongitude").value);
            var latlng = new google.maps.LatLng(lat, lng);
            var geocoder = geocoder = new google.maps.Geocoder();
            geocoder.geocode({ 'latLng': latlng }, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[1]) {
                        alert("Location: " + results[1].formatted_address);
                    }
                }
            });
        }
    </script>
</body>
</html>