    @extends('layouts.app')
	@section('content')
	<section class="store_item_list">
		<div class="container">
			<div class="row">
			 	<div class="container topmargin">
			 	<?php	if(Session::get('user_id')) {?>
			 		{!!Form::open(array('url' => ['wallet/walletadd'], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'walletadd','files' => true));!!}
		            <section id="wallet">
		                 <h2 class="title1">
		                   PAYMENT
		                 </h2> 
		                 <div class="text-center mb-3">
		                   <p class="title2 mb-2">Add custom amount</p>
		                  
		                   <div class="px-5 mb-2">
		                      <input type="text" class="form-control" name="amount" placeholder="Enter amount" aria-label="Username" aria-describedby="basic-addon1">
		                   </div>
		                   <input type="hidden" name="customer_id" id="customer_id" value="<?php echo Session::get('user_id'); ?>">
		                   <input type="hidden" name="add_wallet" id="add_wallet" value="1">

		                  <button class="btn btn-success">ADD</button>
		                 </div>
		                 <h2 class="title1">
		                    OFFER
		                  </h2>
		            
		            </section>
		            {!!Form::close();!!} 

		         <?php } ?>
			        <!-- OFFERS -->
			        {!!Form::open(array('url' => ['PromotionwalletAdd'], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'walletadd_promoption','files' => true));!!}

			            <section id="offer">
			            	<?php if(count(getpromotion($api)->detail)){  $promotion =getpromotion($api)->detail;?>

							<?php foreach($promotion as $data){ // echo"<pre>";print_r($data);exit; ?>

			                <div class="card shadow-sm p-2 mt-2 ">
		                        <!-- cardbody -->
		                        <div class="d-flex justify-content-between">
		                            <div class="img d-flex align-items-center">
		                                <img class="rounded" src=<?php  URL::asset('assets/admin/base/images/no_image.png') ?> alt="" width="100" height="100">
		                                <div class="flex-column text-wrap" style="width: 10rem;">
		                                    <p class="m-0 px-2  pb-4  title3">{{$data->promotion_name}} </p>
		                                </div>
		                                <input type="hidden" name="add_wallet" id="add_wallet" value="2">
			                   			<input type="hidden" name="customer_id" id="customer_id" value="<?php echo Session::get('user_id'); ?>">

		                            </div>
			                        <div class="d-flex align-items-center">
			                            <a class="text-danger" onclick="promotionaddmoney(
			                            {{$data->id}});" href="">dfdsgdgdfg<p class="m-0 last-rigth"><i class="fa fa-chevron-right fa-c1x py-1"></i></p></a>
			                        </div>
			                    </div>
			                </div>
			            <?php }}  else{?>
			            	<!-- <h1>no offer
			            	</h1> -->
			            <?php } ?>
			            </section>
		            {!!Form::close();!!} 


				</div>
			</div>
		</div>
	</section>
	<script type="text/javascript">
		$('select').select2();


		function  promotionaddmoney(id) {
			if (confirm("Are you sure want to continue with this promotion?")) {

				var token = $('input[name=_token]').val();
				var customer_id = $('input[name=customer_id]').val();
				url = '{{url('PromotionwalletAdd')}}';
				//alert(token);return false;
				$.ajax({
				url: url,
				headers: {'X-CSRF-TOKEN': token},
				data: {promotion_type:id,customer_id:customer_id},
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
				});
				/*        var outlet_name = "fffff"; 
		        var token, url, data;
		        token = $('input[name=_token]').val();
		        url = '{{url('samplewallet')}}';
		        var html = '';
		        $.ajax({
		            url: url,
		            headers: {'X-CSRF-TOKEN': token},
		            type: 'POST',
		            data: {outlet_name : outlet_name},
		            datatype: 'JSON',
		            success: function(res) { 
		                if(res.data!='')
		                { 
		                    $.each(res.data, function(key, value) {
		                        html +='<option value='+value["id"]+'>'+ value["product_name"]+'</option>'; 
		                    });
		                    $("#product_name").html(html);
		                }
		                else {
		                    $('#product_name').html('<option value="">No Matches Found</option>');
		                }
		            }
		        })*/
			}
			alert("dsfds");return false;
		}
	</script>
    @endsection
