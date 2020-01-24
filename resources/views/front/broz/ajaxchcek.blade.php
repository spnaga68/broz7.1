  @extends('layouts.front')

 <section id="map">
          <div id="map-container-google-2" class="z-depth-1-half map-container map">
			<!--  <iframe class="map-size" src="https://maps.google.com/maps?q=chicago&t=&z=13&ie=UTF8&iwloc=&output=embed"
              frameborder="0" style="border:0;" allowfullscreen></iframe> -->
            <div id="outlet_map"></div>
            <input type="button" name="sasmple" id="sasmple">
	     {!!Form::open(array('url' => ['common_promotion'], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'walletadd','files' => true));!!}
			{!!Form::close();!!} 
           </div>
        </section>
<!--  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
 -->
	<script type="text/javascript">
		$( document ).ready(function() {
    	console.log( "ready!" );
    	var token = $('input[name=_token]').val();
		url = '{{url('getOutlet')}}';

		$.ajax({
		    type: 'POST',
		    url: url,
		    data: {outlet_type:1},
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
		});
	</script>

