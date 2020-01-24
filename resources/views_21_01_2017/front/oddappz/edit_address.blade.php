@extends('layouts.app')
@section('content')
<section class="store_item_list">
	<div class="container">
		<div class="row">
			<div class="my_account_section">
				<div id="parentHorizontalTab">
					<div class="col-md-3">
					  @include('includes.front.'.Session::get("general")->theme.'.profile_sidebar')
					</div>
					<div class="col-md-9">
						<div class="right_descript">
							<div class="resp-tabs-container hor_1">
								<div class="edit_profile_section">
									<h2 class="pay_title">@lang('messages.Address')</h2>
									<div class="change_password">
										<div class="election_change_pass">
											{!!Form::open(array('url' => ['update-address'], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'update_address'));!!}
												<div class="col-md-12">
													<div class="form-group"> 
														<input type="text" value="{{$address_detail->name}}" name ="name" placeholder="@lang('messages.Name')" id="card_number" class="form-control"> 
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group"> 
														<textarea name="address">{{$address_detail->address}}</textarea>
													</div>
												</div>
												
												<div class="col-md-12">
													<div class="form-group">
														{{ Form::select('country', $country_list, $address_detail->country_id, ['class' => 'country select_dropdown','id'=>'select_country'] ) }}
													</div>
												</div>
												
												<div class="col-md-12">
													<div class="form-group">
														{{ Form::select('city', $city_list, $address_detail->city_id, ['class' => 'city select_dropdown','id'=>'select_city'] ) }}
														
														{{ Form::hidden('address_id', $address_detail->address_id) }}
													</div>
												</div>
												
												<div class="col-md-12 padding0">
													<div class="button_sections">
														<button type="submit" class="btn btn-default" title="@lang('messages.Update')">@lang('messages.Submit')</button>
														<button type="button" class="btn btn-primary cancel_button"  data-url="cards" title="@lang('messages.Cancel')">Cancel</button>
													</div>
												</div>
											{!!Form::close();!!}
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
	});
</script>
@endsection