@extends('layouts.app')
@section('content')
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
													<h2 class="pay_title">@lang('messages.My Cards')</h2>
													<?php if(count($card_list)>0){
														foreach($card_list as $cards){ ?>
														<div class="col-md-10 col-sm-9 col-xs-9"> 
															<h3>{{$cards->name_on_card}} {{  'XXXX-XXXX-XXXX-'.substr($cards->card_number,-4)  }} @lang('messages.EXP') : {{$cards->expiry_date}}</h3>
														</div>
														<div class="col-md-2 col-sm-3 col-xs-3 common_right">
															<a href="delete-card/{{$cards->card_id}}" class="delet_icon" title="@lang('messages.Delete')"><i class="glyph-icon flaticon-delete-1"></i></a>
														</div>
														<?php } } ?>
													<div class="col-md-10">
														<a href="new-card" class="btn btn-primary" title="@lang('messages.Add new card')">@lang('messages.Add new card')</a>
													</div>
												</div>   
												<div class="payment_info">
													<h2 class="pay_title">@lang('messages.Address book')</h2>
													<?php if(count($address_list)>0){
														foreach($address_list as $address){ ?>
														<div class="col-md-10 col-sm-9 col-xs-9">
															<h3>{{$address->address_type}}
																<address>{{$address->address}}</address>
															</h3>
														</div>
														<div class="col-md-2 col-sm-3 col-xs-3 common_right">
															<a href="delete-address/{{$address->address_id}}" class="delet_icon" title="@lang('messages.Delete')"><i class="glyph-icon flaticon-delete-1"></i></a>
															<?php /*<a href="edit-address/{{$address->address_id}}" class="edit_icon" title=""><i class="glyph-icon flaticon-write"></i></a>
															*/ ?>
														 </div>
														<?php } } ?>
													<div class="col-md-10">
														<a href="new-address" class="btn btn-primary" title="@lang('messages.Add new address')">@lang('messages.Add new address')</a>
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
		$('#parentHorizontalTab').easyResponsiveTabs({
			type: 'default', //Types: default, vertical, accordion
			width: 'auto', //auto or any width like 600px
			fit: true, // 100% fit in a container
			tabidentify: 'hor_1', // The tab groups identifier
			activate: function(event) { // Callback function if tab is switched
				var $tab = $(this);
				var $info = $('#nested-tabInfo');
				var $name = $('span', $info);
				$name.text($tab.text());
				$info.show();
			}
		});
		$(".delet_icon").on("click",function(event){
		event.stopPropagation();
		if(confirm("<?php echo trans('messages.Do you want to delete?');?>")) 
		{
			return true;
		}
		else
		{
			return false;
		}       
		event.preventDefault();
	   
	});
	});
</script>
@endsection