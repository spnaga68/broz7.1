<div class="leftpanel">
	<div class="media profile-left">
		<?php $vendor_id = Session::get('vendor_image'); ?>
		<a class="pull-left profile-thumb">
			<img src="<?php echo url('assets/admin/base/images/vendors/logos/'.$vendor_id.'?'.time()); ?>" class="img-circle">
		</a>
		<div class="media-body">
			<h4 class="media-heading"><?php echo ucfirst(Session::get('user_name'));?></h4>
			<small class="text-muted"> <a href="{{ url('vendors/editprofile') }}" title="Edit Profile">@lang('messages.Edit Profile')</a> </small>
		</div>
	</div><!-- media -->
	<ul class="nav nav-pills nav-stacked">
		 <li class="{{ Request::is('vendors/dashboard*') ? 'active' : '' }}" ><a href="{{ URL::to('vendors/dashboard') }}"><i class="fa  fa-home"></i> <span>@lang('messages.Dashboard')</span></a></li>
		<li class="{{ Request::is('vendor/outlets*') ? 'active' : '' || Request::is('vendor/create_outlet') ? 'active' : '' || Request::is('vendor/edit_outlet/*') ? 'active' : '' }}"><a href="{{ URL::to('vendor/outlets') }}"><i class="fa fa-building"></i> <span>@lang('messages.Outlets')</span></a></li>
		<li class="{{ Request::is('vendor/outletmanagers*') ? 'active' : '' || Request::is('vendor/create_outlet_managers') ? 'active' : '' || Request::is('vendor/edit_outlet_manager*') ? 'active' : '' }}"><a href="{{ URL::to('vendor/outletmanagers') }}"><i class="fa  fa-users"></i> <span>@lang('messages.Outlets Managers')</span></a></li>
		<li class="{{ Request::is('vendor/products*') ? 'active' : '' }}"><a href="{{ URL::to('vendor/products') }}"><i class="fa fa-cubes"></i> <span>@lang('messages.Products')</span></a></li>
		<li class="{{ Request::is('vendors/reviews*') ? 'active' : '' }}" ><a href="{{ URL::to('vendors/reviews') }}"><i class="glyphicon  glyphicon-star"></i> <span>@lang('messages.Reviews')</span></a></li>
		<li class="parent {{ (Request::is('vendors/return_orders*') || Request::is('vendors/orders*') || Request::is('vendors/request_amount/*')) ? 'active' : ''}}"><a  href="#"><i class="fa fa-database"></i> <span>@lang('messages.Sales')</span></a>
			<ul class="children">
				<li class="{{ Request::is('vendors/orders*') ? 'active' : '' }}" ><a  href="{{ URL::to('vendors/orders/index') }}">@lang('messages.Orders')</a></li>
				<li class="{{ Request::is('vendors/return_orders*') ? 'active' : '' }}" ><a  href="{{ URL::to('vendors/return_orders') }}">@lang('messages.Return Orders')</a></li>
				<?php /*<li class="{{ Request::is('vendors/request_amount*') ? 'active' : '' }}" ><a  href="{{ URL::to('vendors/request_amount/index') }}">@lang('messages.Request Amount')</a></li> */ ?>
			</ul>
		</li>
		<li class="{{ Request::is('vendors/notifications*') ? 'active' : '' }}" ><a href="{{ URL::to('vendors/notifications') }}"><i class="fa fa-bell"></i> <span>@lang('messages.Notifications')</span></a></li>
	</ul>
	<footer>
        @include('includes.vendors.footer')
    </footer>
</div><!-- leftpanel -->
