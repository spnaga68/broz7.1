 <script type="text/javascript" src="{{ URL::asset('assets/front/'.Session::get('general')->theme.'/js/jquery.form.js') }}"></script>
<div class="common_left">
	<div class="user_profile_sections">
		<div class="click_to_phoyo">
			{!!Form::open(array('url' => ['profile_image'], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'imageform','files'=>true));!!}
				<input type="file" name="image" id="photoimg" />
			{!!Form::close();!!}
			
			
			<div id="preview">
				<a href="javascript:;" title="@lang('messages.Change image')">
					<?php /*<img title="@lang('messages.Upload new')" height="150px" id="preview" alt="{{ $user_details->name }}" src="{{url('/assets/admin/base/images/admin/profile/'.$user_details->image)}}">
					*/ ?>
					<img src="<?php echo $user_details->image.'?'.time(); ?>" class="img-circle img-offline img-profile">
					<div class="select_user_photo"></div>
				</a>
			</div>
		</div>
		<?php if($user_details->first_name != null && $user_details->last_name) { ?>
		
		<h2>{{$user_details->social_title.' '.ucfirst($user_details->first_name).' '.$user_details->last_name}}</h2>
		<?php } else { ?>
		<h2>{{$user_details->social_title.' '.ucfirst($user_details->name)}}</h2>
		<?php } ?>
		<h5>{{$user_details->email}}</h5>
	</div>
	<div class="left_side_tabs">
		<ul class="resp-tabs-list">
			<li class="{{ Request::is('profile*') ? 'active' : '' }}"><a href="{{url('profile')}}"  title="@lang('messages.Edit profile')">@lang('messages.Edit profile')</a></li>
			<li class="{{ Request::is('cards*') ? 'active' : '' }}"><a href="{{url('cards')}}" title="@lang('messages.My Cards/address')">@lang('messages.My Cards/address')</a></li>
			<li class="{{ Request::is('orders*') ? 'active' : '' }}"><a href="{{url('orders')}}" title="@lang('messages.My orders')">@lang('messages.My orders')</a></li>
			<li class="{{ Request::is('favourites*') ? 'active' : '' }}"><a href="{{url('favourites')}}"  title="@lang('messages.My favourites')">@lang('messages.My favourites')</a></li>
			<li class="{{ Request::is('cart*') ? 'active' : '' }}"><a href="{{url('cart')}}"  title="@lang('messages.My cart')">@lang('messages.My cart')</a></li>
			<li class="{{ Request::is('change-password*') ? 'active' : '' }}"><a href="{{url('change-password')}}" title="@lang('messages.Change password')">@lang('messages.Change password')</a></li>
			<li class="{{ Request::is('logout*') ? 'active' : '' }}"><a href="{{url('logout')}}" title="@lang('messages.Logout')">@lang('messages.Logout')</a></li>
		</ul>
	</div>
</div>
