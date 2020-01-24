    @extends('layouts.front')
    
     <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
     
	  <div class="container topmargin">
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
              <li class="nav-item ">
                <a class="nav-link  title4 activeline" id="grocery-tab"  data-toggle="tab" href="#home">GROCERY</a>
              </li>
              <div class="hr-h"></div>
              <li class="nav-item ">
                <a class="nav-link title4 " id="barber-tab" data-toggle="tab"  href="#barber">BARBER</a>
              </li>
              <div class="hr-h"></div>
              <li class="nav-item ">
                <a class="nav-link title4 " id="res-tab" data-toggle="tab"  href="#restaurant">RESTAURANT</a>
              </li>

            </ul>

         
          </section>
          <input type="hidden" name="seleted_nav" id="seleted_nav" value="3">
          <!-- endnavtab -->
        </div>
      

        <!-- cardoutlet -->
        <section id="outlets">
            <div class="tab-content" id="myTabContent">
              <!-- grocery -->
	            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="grocery-tab">
		          <div class="card shadow-sm p-2 mt-2 ">
		            <!-- cardbody -->
		            <?php if(count(getOutlets($api,1,236)->detail)){  $detail =getOutlets($api,1,236)->detail; ?>
			            <a href="#" id="00">
			            	<?php foreach($detail as $data){  //echo"<pre>";print_r($data);//exit; ?>
				              <div id="cardbody">
				                <div class="d-flex justify-content-between">
				                  <div class="img d-flex align-items-center">
				                    <img class="circle" src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/logo/159_81/'.Session::get("general")->logo) ?>" alt="" width="50" height="50">
				                    <div class="flex-column">
				                      <p class="m-0 px-2 pb-3 card-title1">{{$data->outlet_name}}</p>
				                      <p class="m-0 px-2 title2">{{$data->contact_address}}</p>
				                    </div>
				                  </div>
				                  <div class="flex-column align-items-center justify-content-end">
				                    <a class="text-danger last-rigth" href="https://maps.google.com/?q={{$data->latitude}},{{$data->longitude}}">
				                      <p class="m-0 last-rigth"><i class="fa fa-directions fa-c1x py-1"></i></p>
				                    </a>
				                    <p class="m-0 card-title3">7AM - 2AM</p>
				                  </div>
				                </div>
				                <div class="line my-2"></div>
				              </div>
				          <?php } ?>
			            </a>
		            <?php }else{ ?>
		          		<h5 class="title1">No data found</h5>
		          	<?php }  ?>
		            <!-- endcardbody -->
		          </div>
	          	</div>
	          
          	<!-- endgrocery -->

          	<!-- barber -->
	          	<div class="tab-pane fade  " id="barber" role="tabpanel" aria-labelledby="barber-tab">
	              	<div class="card shadow-sm p-2 mt-2 ">
		                <!-- cardbody -->
		                <?php if(count(getOutlets($api,3,236)->detail)){  $detail =getOutlets($api,3,236)->detail;// echo "<pre>"; print_r($detail);exit(); ?>

		                	<a href="#">
		                		<?php foreach($detail as $data){  //echo"<pre>";print_r($data);exit; ?>
			                		<div id="cardbody">
						                <div class="d-flex justify-content-between">
						                  <div class="img d-flex align-items-center">
						                    <img class="circle" src="../img/outlet1.jpg" alt="" width="50" height="50">
						                    <div class="flex-column">
						                      <p class="m-0 px-2 pb-3 card-title1">{{$data->outlet_name}}</p>
						                      <p class="m-0 px-2 title2">{{$data->contact_address}}</p>
						                    </div>
						                  </div>
						                  <div class="flex-column align-items-center justify-content-end">
						                    <a class="text-danger last-rigth" href="https://maps.google.com/?q={{$data->latitude}},{{$data->longitude}}">
						                      <p class="m-0 last-rigth"><i class="fa fa-directions fa-c1x py-1"></i></p>
						                    </a>
						                    <p class="m-0 card-title3">7AM - 2AM</p>
						                  </div>
						                </div>
						                <div class="line my-2"></div>
						            </div>
		              			<?php } ?>
		                	</a>
		                <?php } else{ ?>
		          			<h5 class="title1">No data found</h5>
		         		<?php } ?>
		                <!-- endcardbody -->
	              	</div>
	            </div>
         
          	<!-- endbarber -->
         	<!-- rest -->
         	 <div class="tab-pane fade  " id="restaurant" role="tabpanel" aria-labelledby="res-tab">

              <div class="card shadow-sm p-2 mt-2 ">
                <!-- cardbody -->
               <?php if(count(getOutlets($api,4,236)->detail)){  $detail =getOutlets($api,4,236)->detail;// echo "<pre>"; print_r($detail);exit(); ?>

                <a href="#">
                	<?php foreach($detail as $data){  ?>
	                 <div id="cardbody">
		                <div class="d-flex justify-content-between">
		                  <div class="img d-flex align-items-center">
		                    <img class="circle" src="../img/outlet1.jpg" alt="" width="50" height="50">
		                    <div class="flex-column">
		                      <p class="m-0 px-2 pb-3 card-title1">{{$data->outlet_name}}</p>
		                      <p class="m-0 px-2 title2">{{$data->contact_address}}</p>
		                    </div>
		                  </div>
		                  <div class="flex-column align-items-center justify-content-end">
		                    <a class="text-danger last-rigth" href="https://maps.google.com/?q={{$data->latitude}},{{$data->longitude}}">
		                      <p class="m-0 last-rigth"><i class="fa fa-directions fa-c1x py-1"></i></p>
		                    </a>
		                    <p class="m-0 card-title3">7AM - 2AM</p>
		                  </div>
		                </div>
		                <div class="line my-2"></div>
		            </div>
	              <?php }?>
                </a>
            <?php }else{ ?>
            	<h5 class="title1">No data found</h5>

            <?php } ?>
                <!-- endcardbody -->
              </div>
              </div>
          	<!-- endrest -->
          </section>
        </section>
      </div>
    </div>
  </div>
   <style>
      /* Set the size of the div element that contains the map */
      #outlet_map {
        height: 400px;  /* The height is 400 pixels */
        width: 100%;  /* The width is the width of the web page */
       }
    </style>
     <?php //print_r(request()->path());exit(); ?>

	<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAn_pLYhhBqRD1Cx_RzHLSAUe9PAclmTsw&callback=initMap">
    </script>
     <script type="text/javascript">
	
	<?php $arr = getOutlets($api,1,236)->detail; 
	    $listeDesPoints1=array();
	    $i =0;
	    foreach ($arr as $key => $value) {
	     	$listeDesPoints1[$key][0]=$value->contact_address;
	     	$listeDesPoints1[$key][1]=(double)$value->latitude;
	     	$listeDesPoints1[$key][2]=(double)$value->longitude;
	    } 
	?>
	<?php $arr = getOutlets($api,2,236)->detail;
	    $listeDesPoints2=array();
	    $i =0;
	    foreach ($arr as $key => $value) {
	     	$listeDesPoints2[$key][0]=$value->contact_address;
	     	$listeDesPoints2[$key][1]=(double)$value->latitude;
	     	$listeDesPoints2[$key][2]=(double)$value->longitude;
	    } 
	?>
	<?php $arr = getOutlets($api,3,236)->detail; 
	    $listeDesPoints3=array();

	    $i =0;
	    foreach ($arr as $key => $value) {
	     	$listeDesPoints3[$key][0]=$value->contact_address;
	     	$listeDesPoints3[$key][1]=(double)$value->latitude;
	     	$listeDesPoints3[$key][2]=(double)$value->longitude;
	    } 
	    //echo"hi";print_r($listeDesPoints3);exit(); 

	?><?php $arr = getOutlets($api,4,236)->detail; 
	    $listeDesPoints4=array();
	    $i =0;
	    foreach ($arr as $key => $value) {
	     	$listeDesPoints4[$key][0]=$value->contact_address;
	     	$listeDesPoints4[$key][1]=(double)$value->latitude;
	     	$listeDesPoints4[$key][2]=(double)$value->longitude;
	    } 
	?>
	

	
	</script>
	<script type="text/javascript">

		var x =1;



      	$("#navtab > ul.nav-fill > li > a").click(function() {
      		alert("fdgfdg");
      		var outlet_type =$(this).attr("id");
      		if(outlet_type == "grocery-tab"){
      			$('ul li a').removeClass("activeline");
      			$(this).addClass('activeline');
      			$("#seleted_nav").val(1);
      			initMap();

      		}else if(outlet_type == "barber-tab"){      		      		
      			$('ul li a').removeClass("activeline");
      			$(this).addClass("activeline");
      			$("#seleted_nav").val(3);
      			   initMap();

      			
      		}else if(outlet_type == "res-tab"){
      			$('ul li a').removeClass("activeline");
				$(this).addClass("activeline");   
				$("#seleted_nav").val(4);
      			initMap();

      		}

      	});
      		
    
		function initMap() {

			
		  	// The location of Uluru
		  	var uluru = {lat: 11.016844, lng: 76.955833};
		  	// The map, centered at Uluru
		  	var map = new google.maps.Map(
		    document.getElementById('outlet_map'), {zoom: 4, center: uluru});
		  	// The marker, positioned at Uluru
		 	// var marker = new google.maps.Marker({position: uluru, map: map});
		    var bounds = new google.maps.LatLngBounds();
		   	/*var markers = [
		        ['Coimbatore', 11.016844,76.955833],
		        ['Palace of Westminster, London', 51.499633,-0.124755]
		    ];*/
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

		    }

		   	console.log(markers);

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
