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
								<div class="edit_profile_section">
									<h2 class="pay_title">@lang('messages.Add card')</h2>
									<div class="change_password ">
										<div class="election_change_pass add_new_card_yo9">
											{!!Form::open(array('url' => ['store-card'], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'change_password_form'));!!}
												<div class="col-md-12 add_card">
													<div class="form-group"> 
														<input type="text" pattern="[0-9]{13,16}" maxlength="16" name ="card_number" placeholder="@lang('messages.Card number')" required id="card_number" autocomplete="off" class="form-control card_number" title="@lang('messages.Card number')"> 
													</div>
												</div>
												
												<div class="col-md-12">
													<div class="form-group"> 
														<input autocomplete="off" type="text"  name ="name_on_card" placeholder="@lang('messages.Name on card')" id="name_on_card" class="form-control" title="@lang('messages.Name on card')"> 
													</div>
												</div>
												<div class="form-group year-month"> 
													<div class="col-md-6 col-sm-6 col-xs-6 ">
														{{ Form::selectMonth('month',date('m'),['class' => 'form-control select_dropdown js-example-disabled-results']) }}
													</div>
													<div class="col-md-6 col-sm-6 col-xs-6">
														{{ Form::selectYear('year', date('Y'), date('Y', strtotime('+15 years')),null, ['class' => 'form-control select_dropdown js-example-disabled-results']) }}
													</div>
													
												</div>
										</div>
										<div class="col-md-12">
											<div class="form-group"> </div>
										</div>
										<div class="col-md-12 padding0">
											<div class="button_sections">
												<a class="btn btn-primary cancel_button" data-url="cards" title="@lang('messages.Cancel')" href="{{URL::to('/cards')}}">@lang('messages.Cancel')</a>
												
												<button type="submit" class="btn btn-default" title="@lang('messages.Submit')">@lang('messages.Submit')</button>
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
