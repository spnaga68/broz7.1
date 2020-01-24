    @extends('layouts.app')
	@section('content')
	<section class="store_item_list">
	<div class="container">
	<div class="row">
<div class="offers_list">

<?php if(count(getoffers($api)->response->data)){ ?>
	<?php foreach(getoffers($api)->response->data as $data){  ?>
		<div class="col-md-6">
		<div class="offer_list">
		<div class="offers_img">
			<img src="<?php echo $data->coupon_image; ?>"  alt="{{ $data->coupon_title }}"> 
		</div>
		<div class="offers_desc">
		<a href="javascript::void();" title="{{ $data->coupon_title }}">{{ $data->coupon_title }}</a>
		<p>@lang('messages.Promo code') {{ $data->coupon_code }}</p>
		</div>
		</div>
		</div>
	<?php } ?>
<?php }else {  ?>
	
		<div class="no_store_avlable">
	<div class="no_store_img">
		
	<img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/no_store.png');?>" alt="">
	<p>@lang('messages.No offers available!')</p>
	</div>
	
	
		</div>
<?php } ?>

</div>
</div>
	</div>
	</section>
	<script type="text/javascript">
		$('select').select2();
	</script>
    @endsection
