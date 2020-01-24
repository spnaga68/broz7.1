function setLocation(url)
{
   window.location = url;
}
if (typeof SD =='undefined') {
   var SD = {};
}

SD.Pinmap = {
   modalelement:'',
   containerId : 'pin-point',
   googlemap : '',
   getGridDatas: false,
   dataCallback:'',
   modalheading: SD.translate.get('Search the locations')
};
var pinmaploaded = false;
(function(SD,translate) {
   var pinmap = SD;
   var modalelement,container,element;
   var methods = {
      loadmap:function(response) {
         var data = response.html.replace(/>\s+</g, '><');
         $("#pinmap_modal").html(data);
         SD.googlemap.initialize();
         if (pinmap.dataCallback) {
            pinmap.dataCallback(response);
         }
         $(document.body).on('click',"#pinmap_modal_savePin",pinmap.addAddress);
      },
      loadgriddatas: function(response,data){ 
         if (pinmap.dataCallback) {
            pinmap.dataCallback(response,data);
         }
      }
   };
   pinmap.init = function(container,options)
   {
      container = $("#"+container);
      pinmap.loadElement();
      if (!pinmaploaded) {
         SD.googlemap = pinmap.googlemap();
         pinmaploaded = true;
      }
      container.on('click',this,pinmap.mapload);
      return this;
   };
   pinmap.addAddress = function(e) {
      var ele = $(e.target);
      var val = ele.parents('.pinmap_modal_container').find('.pinmap-popup #address1').val();
      SD.containerId.val(val);
      SD.modalelement.modal('hide');
      //$(".pac-container").css('z-index',999);
   }
   pinmap.mapload = function(e)
   {
      e.preventDefault();
      SD.containerId  = $(e.target);
      SD.modalelement.on('shown.bs.modal',function(e){

     }).modal({'show': true,'keyboard': false});
      pinmap.fetch(baseUrl+'api/map/load',{method:'post'},'loadmap');
   };
   pinmap.loadElement = function()
   {
      if($("#map-pin-modal").length == 0 ) {
         SD.modalelement = $("<div />").attr({id:'map-pin-modal'});
         var template = '<div class="modal pinmap_modal_container fade bs-example-modal in" tabindex="-1" role="dialog" aria-hidden="false" style="display: block;">'
               +'<div class="modal-dialog">'
               + '<div class="modal-content"><div class="modal-header">'
               + '<button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>'
               + '<h4 class="modal-title">'+pinmap.modalheading+'</h4>'
               + '</div>'
               + '<div class="modal-body" id="pinmap_modal">'+translate.get('Loading...')+'</div>'
               + '<div class="modal-footer">'
               + '<button type="button" class="btn btn-default" data-dismiss="modal">'+translate.get('Close')+'</button>'
               + '<button type="button" class="btn btn-primary" id="pinmap_modal_savePin">'+translate.get('Save')+'</button>'
               + '</div>'
               +'</div></div></div>';
         SD.modalelement.html(template);
      }
   };
   pinmap.fetch = function(url,data,method)
   {
       var amethod = data.method || 'post';
       var dataType = data.dataType || 'json';
       $.ajax ({
           url: url,
           type:amethod,
           data:data,
           dataType:dataType,
           cache:true,
           beforeSend:function() {

           },
           success: function(data) {
               pinmap._dispatchMethod(method,data);
           }, error: function(xhr,error) {
                //console.log(xhr.status);
           }
       });
   };
   pinmap.googlemap = function () {
      var object = this;
      var geocoder =  new google.maps.Geocoder();
      var gmap = {
         geocodePosition:function(pos){
            var obj = this;
            geocoder.geocode({
               latLng: pos
             }, function(responses) {
               if (responses && responses.length > 0) {
                 obj.updateMarkerAddress(responses[0].formatted_address);
               } else {
                 obj.updateMarkerAddress("Cannot determine address at this location.");
               }
             });
         },
         initialize:function(){
            var latvalue = $("#latvalue").val() || 29.291541137788954;
            var lngvalue = $("#lngvalue").val() || 47.98156943928677;
            var latLng = new google.maps.LatLng(latvalue,lngvalue);
            var map = new google.maps.Map(document.getElementById('map'), {
             zoom: 14,
             center: latLng,
             mapTypeId: google.maps.MapTypeId.ROADMAP
            });
            google.maps.event.trigger(map, "resize");
            var marker = new google.maps.Marker({
               position: latLng,
               title: 'Point A',
               map: map,
               draggable: true
            });
            var input = document.getElementById('target');

            var searchBox = new google.maps.places.SearchBox(input);
            var markers = [];
            var obj = this;
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
               obj.dragmarker(marker);
               obj.updateMarkerPosition(place.geometry.location);
               obj.geocodePosition(place.geometry.location);
               bounds.extend(place.geometry.location);
             }

             map.fitBounds(bounds);
            });

            // Update current position info.
            obj.updateMarkerPosition(latLng);
            obj.geocodePosition(latLng);
            obj.dragmarker(marker);
         },
         dragmarker :function (marker)
         {
            var obj = this;
            google.maps.event.addListener(marker, 'dragstart', function() {
             obj.updateMarkerAddress('Searching...');
            });
            google.maps.event.addListener(marker, 'drag', function() {
             //updateMarkerStatus('Dragging...');
             obj.updateMarkerPosition(marker.getPosition());
            });

            google.maps.event.addListener(marker, 'dragend', function() {
             //updateMarkerStatus('Drag ended');
             if(!object.getGridDatas) {
               $.ajax({
                  url: baseUrl+'api/gis/griddatas',
                  type:'get',
                  data: {lat:marker.getPosition().lat(),lng:marker.getPosition().lng()},
                  success:function(resp) {
                     resp.lat = marker.getPosition().lat();
                     resp.lng = marker.getPosition().lng();
                      pinmap._dispatchMethod('loadgriddatas',resp,{lat:marker.getPosition().lat(),lng:marker.getPosition().lng()});
                  }

               });
                  //obj.getGridInfo(marker.getPosition());
             }
             obj.geocodePosition(marker.getPosition());
            });
         },
         updateMarkerAddress: function (str) {
            document.getElementById('address1').value = str;
         },
         updateMarkerPosition: function (latLng) {
            document.getElementById('lat').value=latLng.lat();
            document.getElementById('lng').value=latLng.lng();
         }

      }
      return gmap;
   };
   pinmap._dispatchMethod = function(method)
    {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else {
           //console.log('Method ' +  method + ' does not exist');
        }
    };
}(SD.Pinmap,SD.translate));
$(function(){
});