@extends('layouts.app')
@section('content')
<?php  $language_id=getCurrentLang(); ?>
<script src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/plugins/rateit/src/jquery.rateit.js');?>"></script>
<link href="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/plugins/rateit/src/rateit.css');?>" rel="stylesheet">
<section class="store_item_list">
	<div class="container">
		<div class="row">
			<div class="my_account_section">
				<div id="parentHorizontalTab">
					<div class="col-md-3">
					  @include('front.'.Session::get("general")->theme.'.profile_sidebar')
					</div>
					<div class="col-md-9">
						<div class="right_descript">
							<div class="resp-tabs-container hor_1">
								<div class="elections_sections">
									<div class="tabs effect-3">
									<!-- tab-content -->
										<div class="tab-content">
											<section id="tab-item-1" class="edit_profile">
												<div class="payment_info">
													<h2 class="pay_title">@lang('messages.My favourites')</h2>
													<div class="my_fav_sec">
														<?php 
														if(count($store_list)>0){
														foreach($store_list as $stores){ ?>
														<div class="col-md-4 col-sm-4 col-xs-6 fav_outlets">
															<div class="common_item" id="outlet_id{{$stores->outlet_id}}">
																<div class="store_itm_img">
																	<a title="" href="{{ URL::to('store/info/'.$stores->url_index) }}">
																		<img   alt="{{ ucfirst($stores->vendor_name) }}"  src="<?php echo ($stores->featured_image); ?>" >

																	</a>
																</div>
																<div class="store_itm_desc">
																	<a title="The Sultan store" href="{{ URL::to('store/info/'.$stores->url_index) }}">{{$stores->vendor_name}}</a>
																	<p>{{str_limit($stores->outlets_contact_address,40)}}</p>
																</div>
																<div class="store_itm_rating">
						<h2><div class="rateit" data-rateit-value="{{ $stores->outlets_average_rating }}" data-rateit-ispreset="true" data-rateit-readonly="true"></div>{{ $stores->outlets_average_rating }}</h2>
																	
																	<a class="favorite_store" style="cursor:pointer;" data-toggle="popover" title="" data-content="@lang('messages.My favourites')" data-store_id="{{$stores->outlet_id}}"><span class="favi">
																	<?php $class=($stores->status == 1)?"flaticon-favorite-heart-button":"flaticon-favorite-1"; ?>
																	<i id="store_id{{$stores->outlet_id}}" class="glyph-icon {{$class}}"></i></span></a>
																</div>
															</div>
														</div>
														<?php }
															$style_nof = "display:none;";
														}
														else {
															$style_nof = "display:block;";
														
														} ?>
														<div id="no_favorites" style="{{$style_nof}}" class="col-md-4 col-sm-4 col-xs-6">
															<h3>@lang('messages.No favourites Available')</h3>
														</div>
													</div>
											</section>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- footer section strat end -->
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
 <!--Plug-in Initialisation-->
<script type="text/javascript">
	$('select').select2();
	$(document).ready(function() {
		//Horizontal Tab		
		$('.favorite_store').on('click', function() 
		{
			store_id = $(this).attr("data-store_id");
			var url = '{{url("api/addto_favourite")}}';
			var token = '<?php echo csrf_token(); ?>';
			var user_id = '<?php echo Session::get('user_id'); ?>';
			var user_token = '<?php echo Session::get('token'); ?>';
			 var language  = '<?php echo $language_id;?>';
			$.ajax({
			url: url,
			headers: {'X-CSRF-TOKEN': token},
			data: {"vendor_id":store_id,"user_id":user_id,"token":user_token,"language":language},
			type: 'POST',
			dataType:"json",
			success: function (data){
				if(data.response.status)
				{
					toastr.success(data.response.Message);
					
					location.reload(true);
					//$('#store_id'+store_id).removeClass('flaticon-favorite-1');
					//$('#store_id'+store_id).addClass('flaticon-favorite-heart-button');
					////$(this).parent('li').addClass('info_active');
				}else {
					toastr.success(data.response.Message);
					location.reload(true);
					//$('#store_id'+store_id).removeClass('flaticon-favorite-heart-button');
					//$('#store_id'+store_id).addClass('flaticon-favorite-1');
				}
				$('#outlet_id'+store_id).hide();
				var numItems = $('.fav_outlets').length;
				if(numItems == 1)
				{
					$('#no_favorites').show();
				}
			},
			error: function(data){
				var datas = data.responseJSON;
			}
			});
		});
		
	});
</script>
@endsection
