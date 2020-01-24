function setLocation(url)
{
   window.location = url;
}
if (typeof SD =='undefined') {
   var SD = {};
}
var	forsubplace = false,getadd;
SD.GMAP = {
   mapelement :'map',
   target : 'target',
   addsearch : 'address_search',
   dropmarker : 'id',
   googlemap : '',
   getGridDatas: false,
   gridelement : ['gridsearch','boxsearch','polygon_number','search-by_grd','error-girdsear'],
   dataCallback:'',
   needdragmarker : true,
   parentplace : '',
   options : {},
   modalheading: SD.translate.get('Search the locations')
};
var itsmaploaded = false;
SD.GMAP = (function(SD,translate){
   var itsmap = SD;
   console.log(SD);
   var infodragcontentString = '<div id="content"></div>';
   var markerset = [],dropmarker,choosedcity;
   var modalelement,container,element;
   var methods = {
      loadmap:function(response) {
         var data = response.html.replace(/>\s+</g, '><');
         $("#itsmap_modal").html(data);
         SD.googlemap.initialize();
         if (itsmap.dataCallback) {
            itsmap.dataCallback(response);
         }
         $(document.body).on('click',"#itsmap_modal_savePin",itsmap.addAddress);
      },
      loadgriddatas: function(response,data){ 
         if (itsmap.dataCallback) {
            itsmap.dataCallback(response,data);
         }
      },
      checkcity : function(response,data)
      {
         choosedcity = response.city;
         $("#choosed").html('');
         if (response.city) { 
            $("#choosed").show().html('Choosed city is <b>'+response.city['name_eng']+'</b>'); 
            $("#table_prefix").val(response.city['city_pre']);            
         } else {
            $("#choosed").show().html('We couldn\'t able to identify the city you choosed.'); 
             $("#table_prefix").val('');             
         }
      },
      getaddress : function(response,data)
      {
			var html="";
			if(response.errors){	
				$('.responce').html(html);
			}
			if(response.sucess){
				$.each(response.address,function(k,v){
					var checked='';
					if(k==0){
						var checked ='';
					}
					html +='<input type="radio" data-id="'+k+'" data-value="'+v.adress_eng+'" data-value1="'+v.adress_arb+'" class="selected-id" name="default_address" '+checked+' value='+"'"+JSON.stringify(v)+"'"+'> <span class="address_hide'+k+' address">'+v.adress_eng+' - '+v.adress_arb+'</span><span class="field_address" id="address_show'+k+'"></span></br><input type="hidden" name="map_data" value='+"'"+JSON.stringify(response.mapdata)+"'"+'>';
				});
				$('#myModal').modal('show');
				$('.responce').html(html);
				$('.responce').show();
				//$('.adress_eng').remove();
				//$('.adress_arb').remove();
				$('.default_address').remove();
				$('.map_data').remove();
			}
			$(".selected-id").on("click", function() {
				var id = $(this).attr('data-id');
				var dataeng = $(this).attr('data-value');
				var dataara = $(this).attr('data-value1');
				$('.address_hide'+id).hide();
				var html='<input type="text" name="adress_eng" class="form-control" value="'+dataeng+'"> &nbsp; <input type="text" class="form-control" name="adress_arb" value="'+dataara+'">';
				$('.field_address').hide();
				$('#address_show'+id).html(html);
				$('.adress_eng').remove();
				$('.adress_arb').remove();
				$('.default_address').remove();
				$('.map_data').remove();
				$('#address_show'+id).show();
				$('.address').show();
				
			});
      },
      movetoloc : function(response,data)
      {
          if (response.points) {
            if (itsmap.gridelement[4]) { 
               $("#"+itsmap.gridelement[4]).hide();
            }
            itsmap.map.setCenter(new google.maps.LatLng(response.points['lat'],response.points['lng']));
            itsmap.map.setZoom(15);
          } else {
            if (itsmap.gridelement[4]) { 
               $("#"+itsmap.gridelement[4]).show().html('No Data found');
            }
          }
      },
      getpoints : function(response)
      {
          
         if (response.points) { 
            if (markerset.length > 0) {
               for(i = 0; i < markerset.length; i++)
               { 
                  markerset[i].setMap(null);
               }
            }
             markerset = [],placeset = [];
            $.each(response.points,function(k,point){
               var icon = baseUrl+'assets/admin/base/images/departmentstore.png';
              var marker = new google.maps.Marker({
                  position: new google.maps.LatLng(point.lat, point.lng),
                  title : point.place_name,
                  map: itsmap.map,
                  icon : icon
               });
               markerset.push(marker);
               placeset.push(point);
            });
            
            for (var i = 0; i < markerset.length; i++) {
               var marker = markerset[i], place = placeset[i];
               var content = '<div class="infowindow">\
                              <h5>'+place.place_name+'</h5>';               
               if (place.place_id) {
                  content +='<p>This will attached this location as a sub place to this company</p>';
                  content +='<button class="btn btn-primary attachplace"  type="button" onclick="attachplace(this)" data-lat="'+place.lat+'" data-lng="'+place.lng+'" data-placename="'+place.place_name+'" data-placeid="'+place.place_id+'">Attach this place</button>';
            } else {
               content +='<p>This will attached this location to this place</p>';
                  content +='<button class="btn btn-primary attachplace" data-isotherpoint="true" type="button" data-lat="'+place.lat+'" data-lng="'+place.lng+'" data-placename="'+place.place_name+'" onclick="attachpointtothisplace(this)" data-placeid="'+place.gid+'">Attach point to this place</button>'; 
               }
               content +='</div>'; 
               google.maps.event.addListener(marker, 'click', (function (marker,content) {
                  return function(){
                     var element = $(content);
                     var btn = element.find('.attachplace');
                     var placeid = btn.attr('data-placeid');
                     if ($("#hid_location_point_id").val() == placeid && btn.attr('data-isotherpoint')) { 
                        btn.text('Added');
                        btn.attr('disabled',true);
                     }
                     itsmap.firstmarkerpopup.setContent(element.html());
                     itsmap.firstmarkerpopup.open(itsmap.map, this);
                      this.setOptions({zIndex:10});
                  }
               })(marker,content)); 
               google.maps.event.addListener(marker, "mouseout", function() {
                  this.setOptions({zIndex:1});
               });
            }
         };
      }
   };
    
   itsmap.init = function(options)
   {	   
      var opt = (options) || {};
      this.googlemap().initialize();
      $("#drop_marker").on('click',itsmap.dropmarker);
      if (itsmap.gridelement[3]) { 
         $("#"+itsmap.gridelement[3]).on('click',itsmap.searchbygrid);
      }
      $("[data-toggle='tab']").on('click',function(){
         if ($(this).attr('href') == "#gin") { 
            if(itsmap.map) {
               google.maps.event.trigger(itsmap.map, "resize"); 
            }
         }
      });
      $(document.body).on('click','#submit-marker',function(){
         $("#places_generel_form").submit();
      });
      this.intializeAutocomplete(); 
      return this;
   };
   
   itsmap.navigate = function(lat,lon) 
   {
	 latlng = new google.maps.LatLng(lat,lon);
	 itsmap.map.setCenter(latlng);
	 itsmap.map.setZoom(18);
   },
   
   itsmap.intializeAutocomplete = function()
   { 
      if (itsmap.gridelement[0]) { 
         $('#'+itsmap.gridelement[0]).autocompletesingle({
               valueKey:'grid_no',
               titleKey:'grid_no',
               source:[{
                   url:"/api/map/getgriddatas?fetch=grid&term=%QUERY%&limit=10&suppressResponseCodes=true",
                   type:'remote',
                   getTitle:function(item){ 
                       return item['grid_no']
                   },
                   getValue:function(item){
                       return item['grid_no']
                   },	
                   ajax:{
                       dataType : 'json'	
                   }
           }]}).on('selected.xdsoft',function(e,datum){ 
         });
      }
      if (itsmap.gridelement[1]) {
       $('#'+itsmap.gridelement[1]).autocompletesingle({
            valueKey:'build_id',
            titleKey:'build_id',
            source:[{
                url:"/api/map/getgriddatas?fetch=box&tablep=%tableprefix%&term=%QUERY%&limit=10&suppressResponseCodes=true",
                type:'remote',
                getTitle:function(item){ 
                    return item['build_id']
                },
                getValue:function(item){
                    return item['build_id']
                },	
                ajax:{
                    dataType : 'json'
                },
                replace:function( url,query ){
                  var mapObj = {
                        "%tableprefix%":$("#table_prefix").val(),
                        "%QUERY%":query
                     };
                     url = url.replace(/%tableprefix%|%QUERY%/gi, function(matched){
                       return mapObj[matched];
                     });
                     return url;
                 }
        }]}).on('selected.xdsoft',function(e,datum){ 
        });
      }
      if (itsmap.gridelement[2]) {
       $('#'+itsmap.gridelement[2]).autocompletesingle({
            valueKey:'test_code',
            titleKey:'test_code',
            source:[{
                url:"/api/map/getgriddatas?fetch=polygon&tablep=%tableprefix%&term=%QUERY%&limit=10&suppressResponseCodes=true",
                type:'remote',
                getTitle:function(item){ 
                    return item['test_code']
                },
                getValue:function(item){
                    return item['test_code']
                },	
                ajax:{
                    dataType : 'json'
                },
                replace:function( url,query ){
                  var mapObj = {
                        "%tableprefix%":$("#table_prefix").val(),
                        "%QUERY%":query
                     };
                     url = url.replace(/%tableprefix%|%QUERY%/gi, function(matched){
                       return mapObj[matched];
                     });
                     return url;
                 }
        }]}).on('selected.xdsoft',function(e,datum){ 
        });
      }
   };
   
   itsmap.searchbygrid = function()
   { 
      itsmap.fetch(baseUrl+'api/map/searchgriddatas',{method:'post',grid_no: $("#gridsearch").val(),box_no: $("#boxsearch").val(),table_prefix:$("#table_prefix").val()},'movetoloc');
   }
   
   itsmap.dropmarker = function()
   {
      var cmarker = itsmap.map.getCenter();
      itsmap.firstmarker.setPosition(cmarker);
     document.getElementById('latitude').value = cmarker.lat();
     document.getElementById('longitude').value = cmarker.lng();
     var prefix = $('#table_prefix').val(); 
     var latitude = $('#latitude').val();  
     var longitude = $('#longitude').val();
     if(getadd) { 
		 getadd.abort();
	 }
     itsmap.fetch(baseUrl+'api/map/checkcity',{method:'post','lat':latitude,'lng':longitude},'checkcity');
     getadd = itsmap.fetch(baseUrl+'api/map/getaddress',{method:'post','latitude':latitude,'longitude':longitude,'tableprefix':prefix},'getaddress','Fetching address...');
   }
   
   itsmap.addAddress = function(e) {
      var ele = $(e.target);
      var val = ele.parents('.itsmap_modal_container').find('.itsmap-popup #address1').val();
      SD.containerId.val(val);
      SD.modalelement.modal('hide');
      //$(".pac-container").css('z-index',999);
   }
   itsmap.mapload = function(e)
   {
      e.preventDefault();
      SD.containerId  = $(e.target);
      SD.modalelement.on('shown.bs.modal',function(e){

      }).modal({'show': true,'keyboard': false});
      itsmap.fetch(baseUrl+'api/map/load',{method:'post'},'loadmap');
   };
   itsmap.loadElement = function()
   {
      if($("#map-pin-modal").length == 0 ) {
         SD.modalelement = $("<div />").attr({id:'map-pin-modal'});
         var template = '<div class="modal itsmap_modal_container fade bs-example-modal in" tabindex="-1" role="dialog" aria-hidden="false" style="display: block;">'
               +'<div class="modal-dialog">'
               + '<div class="modal-content"><div class="modal-header">'
               + '<button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>'
               + '<h4 class="modal-title">'+itsmap.modalheading+'</h4>'
               + '</div>'
               + '<div class="modal-body" id="itsmap_modal">'+translate.get('Loading...')+'</div>'
               + '<div class="modal-footer">'
               + '<button type="button" class="btn btn-default" data-dismiss="modal">'+translate.get('Close')+'</button>'
               + '<button type="button" class="btn btn-primary" id="itsmap_modal_savePin">'+translate.get('Save')+'</button>'
               + '</div>'
               +'</div></div></div>';
         SD.modalelement.html(template);
      }
   };
   itsmap.fetch = function(url,data,method,loadingtext)
   {
       var amethod = data.method || 'post';
       var dataType = data.dataType || 'json';
       loadingtext || (loadingtext = false) 
       var req = $.ajax ({
           url: url,
           type:amethod,
           data:data,
           dataType:dataType,
           cache:true,
           beforeSend:function() {
               if (loadingtext) {
                  globalloader.show(loadingtext)
               }
               
           },
           success: function(response) {
               itsmap._dispatchMethod(method,response,data);
               if (loadingtext) {
                  globalloader.hide();
               }
           }, error: function(xhr,error) {
                //console.log(xhr.status);
           }
       });
       return req;
   };
   itsmap.googlemap = function () {
       
      var object = this;
      var geocoder =  new google.maps.Geocoder();
      var gmap = {
         getUserLocation : function(marker)
         {
             if (navigator.geolocation) { 
               navigator.geolocation.getCurrentPosition($.proxy(this.userposition,itsmap));
            } else {
                alert("Geolocation is not supported by this browser.");
            }
         },
         userposition : function(position)
         { 
            var lat = $("#latitude").val() || position.coords.latitude;
            var lng = $("#longitude").val() || position.coords.longitude; 
             itsmap.userloc = new google.maps.LatLng(lat,lng);
            itsmap.map.setCenter(itsmap.userloc);
            itsmap.firstmarker.setPosition(itsmap.userloc);
            itsmap.fetch(baseUrl+'api/map/checkcity',{method:'post','lat':lat,'lng':lng},'checkcity');
            
            return this;
         },
         geocodePosition:function(pos){
            var obj = this;
            geocoder.geocode({
               latLng: pos
             }, function(responses) {
               if (responses && responses.length > 0) {
                 //obj.updateMarkerAddress(responses[0].formatted_address);
               } else {
                // obj.updateMarkerAddress("Cannot determine address at this location.");
               }
             });
         },
         initialize:function(){ 
            var latvalue = $("#latitude").val() || 29.291541137788954;
            var lngvalue = $("#longitude").val() || 47.98156943928677;
            var latLng = new google.maps.LatLng(latvalue,lngvalue);
            itsmap.map = new google.maps.Map(document.getElementById(itsmap.mapelement), {
             zoom: 18,
            // minZoom:15,
             center: latLng,
             mapTypeId: google.maps.MapTypeId.ROADMAP
            });
            
            google.maps.event.trigger(itsmap.map, "resize");
            itsmap.firstmarker = new google.maps.Marker({
               position: latLng,
               title: 'Draggable Marker',               
               draggable: true
            });
            /** google.maps.event.addListenerOnce(itsmap.map,'idle',function(){ 
				
			}); **/
            if (itsmap.needdragmarker) { 
               itsmap.firstmarker.setMap(itsmap.map);
            }
            itsmap.firstmarkerpopup = new google.maps.InfoWindow({
               content: infodragcontentString,
               disableAutoPan: true
            });
            this.getUserLocation(); 
            var input = document.getElementById(itsmap.target);
            var searchBox = new google.maps.places.SearchBox(input);
            var markers = [];
            var obj = this;
            /*
            google.maps.event.addListener(searchBox, 'places_changed', function() {
               var places = searchBox.getPlaces();               
               for (var i = 0, marker; marker = markers[i]; i++) {
                 marker.setMap(null);
               }
               markers = [];
               var bounds = new google.maps.LatLngBounds();               
               for (var i = 0, place; place = places[i]; i++) { 
               obj.updateMarkerPosition(itsmap.map, place.geometry.location); 
               bounds.extend(place.geometry.location);
             }
				//latlng = new google.maps.LatLng(-33.8688,151.2195);
				//itsmap.map.setCenter(latlng)
             itsmap.map.fitBounds(bounds);
            });
            */
            google.maps.event.addListener(itsmap.map, "idle", function() {

					$('#latitude').val(itsmap.firstmarker.getPosition().lat());
					$('#longitude').val(itsmap.firstmarker.getPosition().lng());
				  itsmap.fetch(baseUrl+'api/map/checkcity',{method:'post','lat':itsmap.firstmarker.getPosition().lat(),'lng':itsmap.firstmarker.getPosition().lng()},'checkcity');
                google.maps.event.trigger(itsmap.map, 'resize');
               var longmin = itsmap.map.getBounds().getNorthEast().lng(), latmin = itsmap.map.getBounds().getNorthEast().lat(), longmax = itsmap.map.getBounds().getSouthWest().lng(), latmax = itsmap.map.getBounds().getSouthWest().lat(); 
               if ($("#table_prefix").val() && itsmap.map.getZoom() > 15) {                  
                  itsmap.fetch(baseUrl+'api/map/getpoints',{method:'post',longmin:longmin,latmin:latmin, longmax:longmax, latmax:latmax,table_prefix:$("#table_prefix").val(),forsubplace:forsubplace},'getpoints');
               } 
            });
            // Update current position info.
            //obj.updateMarkerPosition(latLng);
            //obj.geocodePosition(latLng);
            obj.dragmarker(itsmap.firstmarker);
         },
         
         dragmarker :function (marker)
         { 
			var obj = this;
            google.maps.event.addListener(marker, 'dragstart', function() {
				
               itsmap.firstmarkerpopup.close();
            });
            google.maps.event.addListener(marker, 'drag', function() { 
               
            });
            google.maps.event.addListener(marker, 'dragend', function() {  	
                  itsmap.fetch(baseUrl+'api/map/checkcity',{method:'post','lat':marker.getPosition().lat(),'lng':marker.getPosition().lng()},'checkcity');
                  obj.geocodePosition(marker.getPosition());                 
                  $.ajax ({
					   url: baseUrl+'api/map/getInfoWindow',
					   type: 'post',
					   data: {'lat':marker.getPosition().lat(),'lng':marker.getPosition().lng()},
					   dataType: 'json',
					   cache:true,
					   beforeSend:function() {

					   },
					   success: function(response) {
						   						    
						   if(response.info) {
								if(typeof(response.info.grid_id) != 'undefined') {
									var infodragcontentString   = '';
									infodragcontentString  += '<div id="content">';
									if(response.info.grid_id != null) {
										infodragcontentString += '<p><b>Grid No : </b>'+response.info.grid_id+'</p>';
									}
									if(response.info.build_id != null) {
										infodragcontentString += '<p><b>Box No : </b>'+response.info.build_id+'</p>';
									}
									if(response.info.test_code != null) {
										infodragcontentString += '<p><b>Polygon No : </b>'+response.info.test_code+'</p>';
									}
									if(response.info.n_name != null) {
										infodragcontentString += '<p><b>Name : </b>'+response.info.n_name+'</p>';
									}
									if(response.info.g_name != null) {
										infodragcontentString += '<p><b>Governorate Name : </b>'+response.info.g_name+'</p>';
									}									
									//infodragcontentString += '<button class="btn btn-primary" id="submit-marker">Save</button>';
									infodragcontentString += '</div>';
								}
						   }else{
							   var infodragcontentString   = translate.get('No Data Found');
						   }
						   if(typeof(infodragcontentString) != 'undefined') {
								itsmap.firstmarkerpopup.setContent(infodragcontentString);
						   }
					   }, error: function(xhr,error) {
							//console.log(xhr.status);
					   }
				  });                
                  $("#hid_location_point_id").val('');                  
                  itsmap.firstmarkerpopup.open(itsmap.map,itsmap.firstmarker);
                  obj.updateMarkerPosition(itsmap.map, marker.getPosition());
            });
         },
         updateMarkerAddress: function (str) {
            //document.getElementById('address1').value = str;
         },
         updateMarkerPosition: function (map, latLng) {

			 var prefix = $('#table_prefix').val(); 
			 if(getadd) { 
					getadd.abort();
			 }
             getadd = itsmap.fetch(baseUrl+'api/map/getaddress',{method:'post','latitude':latLng.lat(),'longitude':latLng.lng(),'tableprefix':prefix},'getaddress','Fetching address...');
             itsmap.fetch(baseUrl+'api/map/checkcity',{method:'post','lat':latLng.lat(),'lng':latLng.lng()},'checkcity');
              var longmin = map.getBounds().getNorthEast().lng(), latmin = map.getBounds().getNorthEast().lat(), longmax = map.getBounds().getSouthWest().lng(), latmax = map.getBounds().getSouthWest().lat(); 
            if ($("#table_prefix").val() && map.getZoom() > 15) {                  
               itsmap.fetch(baseUrl+'api/map/getpoints',{method:'post',longmin:longmin,latmin:latmin, longmax:longmax, latmax:latmax,table_prefix:$("#table_prefix").val(),forsubplace:forsubplace},'getpoints');
            } 
            document.getElementById('latitude').value=latLng.lat();
            document.getElementById('longitude').value=latLng.lng();
         }

      }
      return gmap;
   };
   itsmap._dispatchMethod = function(method)
   {
       if (methods[method]) {
           return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
       } else {
          //console.log('Method ' +  method + ' does not exist');
       }
   };
   return itsmap;
}(SD.GMAP,SD.translate));

function attachplace(ele)
{
   var element = $(ele);
   element.data('placeid');
   var oldtext = element.text();
   element.text('Adding...');
   $.ajax ({
      url : baseUrl+'api/map/addmyplace',
      type: 'post',
      dataType: 'json',
      data: {placeid : element.data('placeid'),parentid : SD.GMAP.parentplace},
      success : function(){
         element.attr('disabled',true);
         element.text('Attached');
      }
   }); 
}


function attachpointtothisplace(ele)
{
   var element = $(ele);
   var oldtext = element.text();
   if ($("#hid_location_point_id").val() == element.data('placeid')) {
        element.text('Added');
        element.attr('disabled',true);
   } else {
      element.attr('disabled',false);
      element.text(oldtext);
      if ($("#company_name_1") =='') { 
         $("#company_name_1").val(element.data('placename'));
      }
      if (element.data('lat')) {
        $("#latitude").val(element.data('lat'));
      }
      if (element.data('lng')) {
        $("#longitude").val(element.data('lng'));
      } 
      element.text('Adding...'); 
      $("#hid_location_point_id").val(element.data('placeid'));
      element.text('Added');
      element.attr('disabled',true);
   }     
}
