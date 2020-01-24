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
									<h2 class="pay_title">@lang('messages.Edit profile')</h2>
									<div class="edit_orofile_sections_inner">
										{!!Form::open(array('url' => 'update-profile', 'method' => 'post', 'class' => 'tab-form attribute_form', 'id' => 'sign_up'));!!} 
											<div class="col-md-6">
												<div class="form-group">
												<input type="text"  maxlength="50" value="{{$user_details->first_name}}" name="first_name" class="form-control" required placeholder="@lang('messages.First Name')">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
												<input type="text" maxlength="50" value="{{$user_details->last_name}}" name="last_name" class="form-control" required placeholder="@lang('messages.Last Name')">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
												<?php $readonly = ""; if($user_details->email != ""){ $readonly = "readonly"; } ?>
												<input type="email" name="email" maxlength="50" value="{{$user_details->email}}" class="form-control" 
												<?php echo $readonly; ?> placeholder="@lang('messages.Email')">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
												<input type="text" name="phone" maxlength="15" class="form-control" value="{{$user_details->mobile}}" required placeholder="@lang('messages.Phone')">
												</div>
											</div>
											<div class="col-md-6">
												<div class="gender_edit">
												<div class="form-group">
													<label class="left_label"> @lang('messages.Gender')</label>
													<label class="label_radio" for="radio-02">
													<input name="gender" id="radio-02" value="M" {{ ($user_details->gender == "M")?"checked=checked":""}} type="radio" /> @lang('messages.Male')</label>
													<label class="label_radio" for="radio-03">
													<input name="gender" id="radio-03" value="F" {{ ($user_details->gender == "F")?"checked=checked":""}}  type="radio" />@lang('messages.Female') </label>
												</div>
											</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
												<input type="text" class="form-control" maxlength="15" name="civil_id" value="{{$user_details->civil_id}}"  placeholder="@lang('messages.Civil id')">
												</div>
											</div>
											
											<div class="col-md-6">
												<div class="form-group">
													<div class="sign_upcooper">
														{{ Form::select('cooperative', get_coperativess(),$user_details->cooperative, ['class' => 'form-control select_dropdown js-example-disabled-results']) }}
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
												<input type="text" name="member_id" maxlength="15" class="form-control" value="{{$user_details->member_id}}" placeholder="@lang('messages.Member id')">
												</div>
											</div>
											<div class="col-md-6">
												<div class="button_sections">
													<button title="@lang('messages.Cancel')" class="btn btn-primary" data-url="" type="button" onclick="window.location='{{ url('/') }}'">@lang('messages.Cancel')</button>
													
													<button title="@lang('messages.Update')" class="btn btn-default button-submit" type="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> @lang('messages.Processing')">@lang('messages.Update')</button>
													
												
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