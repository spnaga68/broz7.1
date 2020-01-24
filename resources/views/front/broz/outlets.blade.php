    @extends('layouts.front')
        @section('content')

     <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
     
	<div class="container  page-wrap topmargin">
    	<div class="row">
      		<div class="col-lg-6">
        		<!-- map -->
			        <section id="map">
			           <div id="map-container-google-2" class="z-depth-1-half map-container map">
							<!--  <iframe class="map-size" src="https://maps.google.com/maps?q=chicago&t=&z=13&ie=UTF8&iwloc=&output=embed"
				              frameborder="0" style="border:0;" allowfullscreen></iframe> -->
				            <div id="outlet_map" class="map-size"></div>

			           </div>
			        </section>
        {!!Form::open(array('url' => ['common_promotion'], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'walletadd','files' => true));!!}
		{!!Form::close();!!} 

        <!-- endmap -->
      		</div>
      		<div class="col-lg-6">
	        	<div id="navtab">
	          	  <!-- navtab -->
		          <section id="navtab">

		            <ul class="nav  nav-fill shadow-sm title4" id="myTab" role="tablist">
		              <li class="nav-item border-bottom">
		                <a class="nav-link  title4 activeline" id="grocery-tab"  data-toggle="tab" href="#grocery">GROCERY</a>
		              </li>
		              <div class="hr-h"></div>
		              <li class="nav-item  border-bottom">
		                <a class="nav-link title4 " id="barber-tab" data-toggle="tab"  href="#barber">BARBER</a>
		              </li>
		              <div class="hr-h"></div>
		              <li class="nav-item  border-bottom">
		                <a class="nav-link title4 " id="res-tab" data-toggle="tab"  href="#restaurant">RESTAURANT</a>
		              </li>
		               <div class="hr-h"></div>
		               <li class="nav-item  border-bottom">
		                <a class="nav-link title4 " id="laun-tab" data-toggle="tab"  href="#laundry">LAUNDRY</a>
		              </li>
		               <!-- <div class="hr-h"></div>
		               <li class="nav-item  border-bottom">
		                <a class="nav-link title4 " id="res-tab" data-toggle="tab"  href="#restaurant">BROZE</a>
		              </li> -->
		            </ul>
		          </section>
		          <input type="hidden" name="seleted_nav" id="seleted_nav" value="3">
		          <!-- endnavtab -->
		        </div>
	      
        <!-- cardoutlet -->
        <section id="outlets" class="pb-3">
            <div class="tab-content" id="myTabContent">
              <!-- grocery -->
	            <div class="tab-pane fade show active" id="grocery" role="tabpanel" aria-labelledby="grocery-tab">
		          <div class="card shadow-sm  mt-2 ">
		          	   <div id="card-btmno">
		            <!-- cardbody -->

		            <?php if(count(getOutlets($api,1,236))){  $detail =getOutlets($api,1,236); ?>
		            	<?php //echo"<pre>";print_r($detail);exit; ?>
		      			<?php $i = 0; foreach($detail as $data){ $i++; //echo"<pre>";print_r($data);//exit; ?>
				        <div id="cardbody">
			            <a href="#">

			            	<div  class="card p-1">
                                <div class="d-flex justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <img class="img-fluid rounded-circle " src="{{$data->outletImage}}" alt="" style="width: 70px;height: 70px;">
                                        <div class="text-break ml-1 w-70 align-self-center px-1" >
                                            
                                            <h2 class="outlet-title1 text-uppercase mb-1">{{$data->outlet_name}}</h2>
                                            <p class="outlet-title2 m-0 align-items-end m-0">{{$data->contact_address}}</p>
                                        </div>
                                    </div>
                                    <div class="d-column align-self-center last-rigth  w-30">
                                        <a href="https://maps.google.com/?q={{$data->latitude}},{{$data->longitude}}" target="_blank"><i class="fas fa-directions fa-c1x mb-2 text-danger"></i></a>
                                        <p class="outlet-title3 align-self-end  m-0 p-0 ">3AM - 7AM</p>
                                    </div>
                                </div>
                            </div>
                           
				                </a>
				              </div>
				          <?php } ?>
		            <?php }else{ ?>
		          		<h5 class="title1">No data found</h5>
		          	<?php }  ?>
		            <!-- endcardbody -->
		          </div>
	          	</div>
	          </div>
          	<!-- endgrocery -->

          	<!-- barber -->
	          	<div class="tab-pane fade  " id="barber" role="tabpanel" aria-labelledby="barber-tab">
	              	<div class="card shadow-sm  mt-2 ">
	              		<div id="card-btmno">
		                <!-- cardbody -->
		                <?php if(count(getOutlets($api,2,236))){  $detail =getOutlets($api,2,236); //echo "<pre>"; print_r($detail);exit(); ?>
			               
		                <?php $i = 0; foreach($detail as $data){ $i++;//<?php //echo"<pre>";print_r($detail);exit; ?>
		      			
					        <div id="cardbody">
				            <a href="#">

			            	<div  class="card p-1">
                                <div class="d-flex justify-content-between">
                                    <div class="d-flex align-items-center ">
                                        <img class="img-fluid rounded-circle " src="{{$data->outletImage}}" alt="" style="width: 70px;height: 70px;">
 										<div class="text-break ml-1 w-70 align-self-center px-1" >                                            
                                            <h2 class="outlet-title1 text-uppercase mb-1">{{$data->outlet_name}}</h2>
                                            <p class="outlet-title2 m-0 align-items-end m-0">{{$data->contact_address}}</p>
                                        </div>
                                    </div>
                                    <div class="d-column align-self-center last-rigth  w-30">
                                        <a href="https://maps.google.com/?q={{$data->latitude}},{{$data->longitude}}" target="_blank"><i class="fas fa-directions fa-c1x mb-2 text-danger"></i></a>
                                        <p class="outlet-title3 align-self-end  m-0 p-0 ">3AM - 7AM</p>
                                    </div>
                                </div>
                            </div>
                           
				                </a>
				              </div>
				          <?php } ?>
		            <?php }else{ ?>
		          		<h5 class="title1">No data found</h5>
		          	<?php }  ?>
		            <!-- endcardbody -->
		          </div>
	          	</div>
	          </div>
          	<!-- endbarber -->
         	<!-- rest -->
         	 <div class="tab-pane fade  " id="restaurant" role="tabpanel" aria-labelledby="res-tab">

             <div class="card shadow-sm  mt-2 ">
	              		<div id="card-btmno">
		                <!-- cardbody -->
		                <?php if(count(getOutlets($api,4,236))){  $detail =getOutlets($api,4,236);// echo "<pre>"; print_r($detail);exit(); ?>
			               
		                <?php $i = 0; foreach($detail as $data){ $i++;//<?php //echo"<pre>";print_r($detail);exit; ?>
		      			
					        <div id="cardbody">
				            <a href="#">

			            	<div  class="card p-1">
                                <div class="d-flex justify-content-between">
                                    <div class="d-flex align-items-center ">
                                        <img class="img-fluid rounded-circle " src="{{$data->outletImage}}" alt="" style="width: 70px;height: 70px;">
                                        <div class="text-break ml-1 w-70 align-self-center px-1" >
                                            
                                            <h2 class="outlet-title1 text-uppercase mb-1">{{$data->outlet_name}}</h2>
                                            <p class="outlet-title2 m-0 align-items-end m-0">{{$data->contact_address}}</p>
                                        </div>
                                    </div>
                                    <div class="d-column align-self-center last-rigth  w-30">
                                        <a href="https://maps.google.com/?q={{$data->latitude}},{{$data->longitude}}" target="_blank"><i class="fas fa-directions fa-c1x mb-2 text-danger"></i></a>
                                        <p class="outlet-title3 align-self-end  m-0 p-0 ">3AM - 7AM</p>
                                    </div>
                                </div>
                            </div>
                           
				                </a>
				              </div>
				          <?php } ?>
		            <?php }else{ ?>
		          		<h5 class="title1">No data found</h5>
		          	<?php }  ?>
		            <!-- endcardbody -->
		          </div>
	          	</div>
	          </div>
          	<!-- endrest -->


          	<!-- laun -->
         	 <div class="tab-pane fade  " id="laundry" role="tabpanel" aria-labelledby="laun-tab">

             <div class="card shadow-sm  mt-2 ">
	              		<div id="card-btmno">
		                <!-- cardbody -->
		                <?php if(count(getOutlets($api,5,236))){  $detail =getOutlets($api,5,236);// echo "<pre>"; print_r($detail);exit(); ?>
			               
		                <?php $i = 0; foreach($detail as $data){ $i++;//<?php //echo"<pre>";print_r($detail);exit; ?>
		      			
					        <div id="cardbody">
				            <a href="#">

			            	<div  class="card p-1">
                                <div class="d-flex justify-content-between">
                                    <div class="d-flex align-items-center ">
                                        <img class="img-fluid rounded-circle " src="{{$data->outletImage}}" alt="" style="width: 70px;height: 70px;">
                                        <div class="text-break ml-1 w-70 align-self-center px-1" >
                                            
                                            <h2 class="outlet-title1 text-uppercase mb-1">{{$data->outlet_name}}</h2>
                                            <p class="outlet-title2 m-0 align-items-end m-0">{{$data->contact_address}}</p>
                                        </div>
                                    </div>
                                    <div class="d-column align-self-center last-rigth  w-30">
                                        <a href="https://maps.google.com/?q={{$data->latitude}},{{$data->longitude}}" target="_blank"><i class="fas fa-directions fa-c1x mb-2 text-danger"></i></a>
                                        <p class="outlet-title3 align-self-end  m-0 p-0 ">3AM - 7AM</p>
                                    </div>
                                </div>
                            </div>
                           
				                </a>
				              </div>
				          <?php } ?>
		            <?php }else{ ?>
		          		<h5 class="title1">No data found</h5>
		          	<?php }  ?>
		            <!-- endcardbody -->
		          </div>
	          	</div>
	          </div>
          	<!-- endlaun -->
          </section>
        
      </div>
    </div>
  </div>
   <style>
      /* Set the size of the div element that contains the map */
      
    </style>
     <?php //print_r(request()->path());exit(); ?>

	<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAn_pLYhhBqRD1Cx_RzHLSAUe9PAclmTsw&callback=initMap"> 
    </script>
	
	<?php $arr = getOutlets($api,1,236); 
	    $listeDesPoints1=array();
	    $i =0;
	    foreach ($arr as $key => $value) {
	     	$listeDesPoints1[$key][0]=$value->contact_address;
	     	$listeDesPoints1[$key][1]=(double)$value->latitude;
	     	$listeDesPoints1[$key][2]=(double)$value->longitude;
	    } 
	?>
	<?php $arr = getOutlets($api,2,236);
	    $listeDesPoints2=array();
	    $i =0;
	    foreach ($arr as $key => $value) {
	     	$listeDesPoints2[$key][0]=$value->contact_address;
	     	$listeDesPoints2[$key][1]=(double)$value->latitude;
	     	$listeDesPoints2[$key][2]=(double)$value->longitude;
	    } 
	?>
	<?php $arr = getOutlets($api,1,236); 
	    $listeDesPoints3=array();

	    $i =0;
	    foreach ($arr as $key => $value) {
	     	$listeDesPoints3[$key][0]=$value->contact_address;
	     	$listeDesPoints3[$key][1]=(double)$value->latitude;
	     	$listeDesPoints3[$key][2]=(double)$value->longitude;
	    } 
	    //echo"hi";print_r($listeDesPoints3);exit(); 

	?><?php $arr = getOutlets($api,4,236); 
	    $listeDesPoints4=array();
	    $i =0;
	    foreach ($arr as $key => $value) {
	     	$listeDesPoints4[$key][0]=$value->contact_address;
	     	$listeDesPoints4[$key][1]=(double)$value->latitude;
	     	$listeDesPoints4[$key][2]=(double)$value->longitude;
	    } 
	?><?php $arr = getOutlets($api,5,236); 
	    $listeDesPoints5=array();
	    $i =0;
	    foreach ($arr as $key => $value) {
	     	$listeDesPoints5[$key][0]=$value->contact_address;
	     	$listeDesPoints5[$key][1]=(double)$value->latitude;
	     	$listeDesPoints5[$key][2]=(double)$value->longitude;
	    } 
	?>
	
    

	
	<script type="text/javascript">

		var x =1;



      	$("#navtab > ul.nav-fill > li > a").click(function() {
      		// alert("fdgfdg");
      		var outlet_type =$(this).attr("id");
      		if(outlet_type == "grocery-tab"){
      			$('ul li a').removeClass("activeline");
      			$(this).addClass("activeline");
      			$("#seleted_nav").val(1);
      			initMap();
      			//document.location.reload("brozapp.com/outlets");

      		}else if(outlet_type == "barber-tab"){      		      		
      			$('ul li a').removeClass("activeline");
      			$(this).addClass("activeline");
      			$("#seleted_nav").val(2);
      			initMap();

      			
      		}else if(outlet_type == "res-tab"){
      			$('ul li a').removeClass("activeline");
				$(this).addClass("activeline");   
				$("#seleted_nav").val(4);
      			initMap();

      		}else if(outlet_type == "laun-tab"){
      			$('ul li a').removeClass("activeline");
				$(this).addClass("activeline");   
				$("#seleted_nav").val(5);
      			initMap();
      		}

      	});
      		
    
		function initMap() {

			
		  	// The location of Uluru
		  	var uluru = {lat: 11.016844, lng: 76.955833};
		  	// The map, centered at Uluru
		  	

		  	var map = new google.maps.Map(
		    document.getElementById('outlet_map')/*,{zoom: 7, center:pos}*/);

		  	// The marker, positioned at Uluru
		 	// var marker = new google.maps.Marker({position: uluru, map: map});
		    var bounds = new google.maps.LatLngBounds();
		   	
		    var val =$("#seleted_nav").val();
		    console.log(val);
		    if(val == 1)
		    {
		    	var markers = <?php echo json_encode($listeDesPoints1); ?>

		    }else if(val == 2){
		    	var markers = <?php echo json_encode($listeDesPoints2); ?>

		    }else if(val == 3){
		    	var markers = <?php echo json_encode($listeDesPoints3); ?>

		    }else if(val == 4){
		    	var markers = <?php echo json_encode($listeDesPoints4); ?>

		    }else if(val == 5){
		    	var markers = <?php echo json_encode($listeDesPoints5); ?>

		    }
   			/*var currentZoomLevel = map.getZoom();

		   	console.log("currentZoomLevel"+currentZoomLevel);
		   	console.log(markers);*/

		    for( i = 0; i < markers.length; i++ ) {
		        var position = new google.maps.LatLng(markers[i][1], markers[i][2]);
		        bounds.extend(position);
		        marker = new google.maps.Marker({
		            position: position,
		            map: map,
		            title: markers[i][0]
		        });
		        // Automatically center the map fitting all markers on the screen
		        map.fitBounds(bounds);
		        map.setOptions({ minZoom: 5, maxZoom: 19.5 });
		        //map.setZoom(20);
		    }
		}
		function getoutlets(outlet_type) {


			//alert("ffff");
			var token = $('input[name=_token]').val();
			url = '{{url('getOutlet')}}';
			//alert(token);return false;

			$.ajax({
			    type: 'POST',
			    url: url,
			    data: {outlet_type:outlet_type},
			    dataType: 'json',
			   success: function(result) {
					//cnsle.log(result);return false;
					$('#deleteTrigeroutlet').hide();
					$('#bulkDeleteoutlet').prop("checked",false);
					//oTable.ajax.reload();
					//checkbox.ajax.reload();
					location.reload(true);
				},
				async:false
			});
			/*$.ajax({
				url: url,
				headers: {'X-CSRF-TOKEN': token},
				data: {outlet_type:outlet_type},
				type: 'POST',
				datatype: 'JSON',
				success: function(result) {
					//cnsle.log(result);return false;
					$('#deleteTrigeroutlet').hide();
					$('#bulkDeleteoutlet').prop("checked",false);
					//oTable.ajax.reload();
					//checkbox.ajax.reload();
					location.reload(true);
				},
				async:false
			});*/


		}


	</script>
@endsection