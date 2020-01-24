    @extends('layouts.front')
	
	<section class="store_item_list">
		<div class="container">
			<div class="row">
			 	<div class="container topmargin">
			 	<?php	if(Session::get('user_id')) {?>
			 		
			        <!-- OFFERS -->
			        {!!Form::open(array('url' => ['common_promotion'], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'walletadd_promoption','files' => true));!!}

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
		                            <?php	if(Session::get('user_id')) {?>

			                        <div class="d-flex align-items-center">
			                            <a class="text-danger" onclick="promotionaddmoney(
			                            {{$data->id}});" href=""><p class="m-0 last-rigth"><i class="fa fa-chevron-right fa-c1x py-1"></i></p></a>
			                        </div>
			                    <?php } ?>
			                    </div>
			                </div>
			            <?php }}  else{?>
			            	<!-- <h1>no offer
			            	</h1> -->
			            <?php } ?>
			            </section>
		            {!!Form::close();!!} 

		            <?php } ?>
				</div>
			</div>
		</div>
	</section>
 
	<script type="text/javascript">


		function  promotionaddmoney(id) {
			if (confirm("Are you sure want to continue with this promotion?")) {
				var token, customer_id, url, data, add_wallet;
				token = $('input[name=_token]').val();
				customer_id = $('input[name=customer_id]').val();
				add_wallet = 2;
				url = '{{url('common_promotion')}}';
				//alert(token);return false;
				data = {promotion_type:id,customer_id:customer_id,add_wallet:add_wallet};
				$.ajax({
				type: 'POST',
				url: url,
				//headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
				headers: {'X-CSRF-TOKEN': token},
				data: data,
				datatype: 'JSON',
				async:false,
				success: function(result) {
					//console.log("hello");return false;
					$('#deleteTrigeroutlet').hide();
					$('#bulkDeleteoutlet').prop("checked",false);
					//oTable.ajax.reload();
					//checkbox.ajax.reload();
					//location.reload(true);
				},
				
				});
				
			}
			alert("hai");return false;
		}
	</script>


