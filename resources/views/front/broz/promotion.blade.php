    @extends('layouts.front')
	    @section('content')

	<section class="store_item_list page-wrap">
		<div class="container">
			<div class="row">
			 	<div class="container topmargin">
			 	<?php	if(Session::get('user_id')) {?>
			 		{!!Form::open(array('url' => ['common_promotion'], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'walletadd','files' => true));!!}
		            <section id="wallet">
		             <!--     <h2 class="title1">
		                   PAYMENT
		                 </h2>  -->
		                 <div class="text-center mb-3">
		                   <p class="title2 mb-2 mt-2">Add custom amount</p>
		                  
		                   <div class="px-5 mb-2 col-md-6 m-auto">
		                   		<?php $quick_pay =  getwalletQuickPay(); ?>
		                   		<input type="button" name="" class="btn btn-light btn-sm btn-outline-success mb-1" id="amount1" value="{{$quick_pay->amount1}}" onclick="quickPay({{$quick_pay->amount1}})">
		                   		<input type="button" name="" class="btn btn-light btn-sm btn-outline-success mb-1" id="amount1" value="{{$quick_pay->amount2}}" onclick="quickPay({{$quick_pay->amount2}})">
		                   		<input type="button" name="" class="btn btn-light btn-sm btn-outline-success mb-1" id="amount1" value="{{$quick_pay->amount3}}" onclick="quickPay({{$quick_pay->amount3}})">
		                      	<input type="text" class="form-control" id="amount_input" name="amount" placeholder="Enter amount" aria-label="Username" aria-describedby="basic-addon1" required oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">

		                   </div>
		                   <input type="hidden" name="customer_id" id="customer_id" value="<?php echo Session::get('user_id'); ?>">
		                   <input type="hidden" name="add_wallet" id="add_wallet" value="1">
		                   <input type="hidden" name="promotion_type" id="promotion_type" value="0">

		                  <button class="btn btn-success mt-2" id="add_amount">Proceed to Pay</button>
		                 </div>
		                 <h2 class="title1">
		                    OFFER
		                  </h2>
		            
		            </section>
		            {!!Form::close();!!} 

		         <?php } ?>
			        <!-- OFFERS -->
			        {!!Form::open(array('url' => ['common_promotion'], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'walletadd_promoption','files' => true));!!}

			            <section id="offer" class="pb-3">
			            	<?php $promotion = getpromotion(); //echo "<pre>";print_r($promotion['detail']);exit
			            	;
			            	if(count($promotion['detail'])){  ?>

							<?php foreach($promotion['detail'] as $data){ //echo"<pre>";print_r($data);exit; ?>
							<?php $totalAmount= $data->base_amount + $data->addition_promotion;
							/*$imageName = url('/assets/admin/base/images/no_image.png');
				                    if (file_exists(base_path() . '/public/assets/admin/base/images/customerPromotion/' . $data->image) && $data->image != '') {$imageName = URL::to("assets/admin/base/images/customerPromotion/" . $data->image);
				                    }*/
					                    ?>
			                <div class="card shadow-sm p-2 mt-2 ">
		                        <!-- cardbody -->
		                        <div class="d-flex justify-content-between">
		                            <div class="img d-flex align-items-center">
		                                <img class="rounded " src="{{$data->image}}" alt="" width="100" height="100">
		                                <div class="flex-column text-wrap" style="width: 10rem;">
		                                	<h4 class="px-2 py-0 card-title1">{{$data->promotion_name}}</h4>
		                                    <p class="m-0 px-2  pb-1  card-title3">{{$data->description}}<!-- Add {{$data->base_amount}} AED and get  {{$totalAmount}} AED--></p>
		                                    <p class="m-0 px-2  pt-1  title2">{{$data->base_amount}} AED </p>
		                                </div>
		                                <input type="hidden" name="add_wallet" id="add_wallet" value="2">
			                   			<input type="hidden" name="customer_id" id="customer_id" value="<?php echo Session::get('user_id'); ?>">

		                            </div>
		                            <?php	if(Session::get('user_id')) {?>

			                        <div class="d-column align-self-end">

			                        	 <a role="button" class="btn btn-success btn-sm  text-white align-self-center" onclick="promotionaddmoney({{$data->base_amount}},{{$data->id}});"><p class="m-0 last-rigth btn-titlep">Add</p></a>

			                            <a role="button" class="bg-color"  data-toggle="modal" data-target="#exampleModalCenter" ><p class="m-0 last-rigth"><i class="fa fa-info-circle icon-light pt-3"></i></p></a>

			                        </div>
			                    <?php }else{ ?>

			                    	 <div class="d-column align-self-end">

			                            <a role="button" class="btn btn-success btn-sm  text-white align-self-center" onclick="signin()"><p class="m-0 last-rigth btn-titlep">Add</p></a>

			                            <a role="button" class="bg-color " data-toggle="modal" data-target="#exampleModalCenter"><p class="m-0 last-rigth"><i class="fa fa-info-circle icon-light pt-3"></i></p></a>

			                        </div>
			                    <?php } ?>

			                  

			                <!-- Modal -->
			<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog"				a-labelledby="	exampleModalCenterTitle" aria-hidden="true">
				  <div class="modal-dialog modal-dialog-centered" role="document">
				    <div class="modal-content">
				        <div class="modal-header">
					        <h5 class="modal-title card-title1" id="exampleModalCenterTitle">Conditions:</h5>
					        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
					          <span aria-hidden="true">&times;</span>
					        </button>
			      		</div>
				      	<div class="modal-body">
					        <p class="card-title3">
					        	{{$data->conditions}}
					        </p>
				       	</div>
			    </div>
			  </div>
			</div>
			  </div>
			                </div>
			            <?php }}  ?>
			            	
			            </section>
		            {!!Form::close();!!} 


				</div>
			</div>


		</div>
	</section>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>


	<script type="text/javascript">

		$( document ).ready(function() {
			$("#amount_input").val('');
		});

		 function quickPay(amount)
		 {
		 	document.getElementById('amount_input').value =  amount;
	 	 	$("#walletadd").submit();
		 }
		function signin(){
			alert("Please login");
			window.location= "https://brozapp.com/customerLogin";
		}

		function  promotionaddmoney(id,type) {
			document.getElementById('amount_input').value =  id;
			document.getElementById('add_wallet').value =  2;
			document.getElementById('promotion_type').value =  type;

			document.getElementById("add_amount").click();
			var token, customer_id, url, data, add_wallet;
			add_wallet = 2;
			customer_id = $('input[name=customer_id]').val();
			data = {promotion_type:id,customer_id:customer_id,add_wallet:add_wallet};
			//alert(id);
			{{--if (confirm("Are you sure want to continue with this promotion?")) {--}}
			{{--	var token, customer_id, url, data, add_wallet;--}}
			{{--	token = $('input[name=_token]').val();--}}
			{{--	customer_id = $('input[name=customer_id]').val();--}}
			{{--	add_wallet = 2;--}}
			{{--	url = '{{url('common_promotion')}}';--}}
			{{--	//alert(token);return false;--}}
			{{--	data = {promotion_type:id,customer_id:customer_id,add_wallet:add_wallet};--}}
			{{--	$.ajax({--}}
			{{--	type: 'POST',--}}
			{{--	url: url,--}}
			{{--	//headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},--}}
			{{--	headers: {'X-CSRF-TOKEN': token},--}}
			{{--	data: data,--}}
			{{--	datatype: 'JSON',--}}
			{{--	async:false,--}}
			{{--	success: function(data) {--}}
			{{--		//console.log("hello");return false;--}}
			{{--		$('#deleteTrigeroutlet').hide();--}}
			{{--		$('#bulkDeleteoutlet').prop("checked",false);--}}
			{{--	},--}}
			{{--	--}}
			{{--	});--}}
			{{--	--}}
			{{--}--}}
			{{--alert("dsfds");return false;--}}
		}
	</script>

 @endsection