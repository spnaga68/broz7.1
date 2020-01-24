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
									<h2 class="pay_title">@lang('messages.Change password')</h2>
									<div class="change_password">
										<div class="election_change_pass">
											{!!Form::open(array('url' => ['update-password'], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'change_password_form'));!!}
												<div class="col-md-12">
													<div class="form-group"> 
														<input type="password" required  name ="old_password" placeholder="@lang('messages.Old password')" id="old_password" class="form-control"> 
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group"> 
														<input type="password" required  name = "password" placeholder="@lang('messages.New password')" id="new_password" class="form-control"> 
													</div>
													<div class="form-group"> 
														<input type="password" required  name = "password_confirmation" placeholder="@lang('messages.Retype password')" id="conf_password" class="form-control"> 
													</div>
													<div class="col-md-12 padding0">
														<div class="button_sections">
															
															<a href="{{URL::previous()}}" title="@lang('messages.Cancel')" class="btn btn-primary cancel_button">@lang('messages.Cancel')</a>
															
															
															<button type="submit" class="btn btn-default" title="@lang('messages.Update')">@lang('messages.Update')</button>
														</div>
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