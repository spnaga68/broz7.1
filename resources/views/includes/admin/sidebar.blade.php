<div class="leftpanel">
	<div class="media profile-left">
		<a class="pull-left profile-thumb">
			<?php if (file_exists(base_path() . '/public/assets/admin/base/images/admin/profile/thumb/' . Auth::user()->image) && Auth::user()->image != '') {?>
				<img src="<?php echo url('/assets/admin/base/images/admin/profile/thumb/' . Auth::user()->image . '?' . time()); ?>" class="img-circle">
			<?php } else {?>
				<img src=" {{ URL::asset('assets/admin/base/images/a2x.jpg') }} " class="img-circle">
			<?php }?>
		</a>
		<div class="media-body">
			<h4 class="media-heading"><?php echo Auth::user()->name; ?></h4>
			<small class="text-muted"> <a href="{{ url('admin/editprofile/'.Auth::id()) }}" title="Edit Profile">@lang('messages.Edit Profile')</a> </small>
		</div>
	</div><!-- media -->
	<?php /** {{{ (Request::is('admin/blog') ? 'class=active' : '') }}} **/?>
	<h5 class="leftpanel-title"></h5>
	<ul class="nav nav-pills nav-stacked">
		<li class="{{ Request::is('admin/dashboard*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/dashboard') }}"><i class="fa  fa-home"></i> <span>@lang('messages.Dashboard')</span></a></li>
		<?php if (hasTask('admin/category')) {?>
			<li class="{{ Request::is('admin/category*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/category') }}"><i class="fa  fa-tasks"></i> <span>@lang('messages.Category')</span></a></li>
		<?php }?>

		<?php if (hasTask('admin/users/index') /* || hasTask('admin/users/create')*/ || hasTask('admin/users/groups') /*  || hasTask('admin/groups/create')*/ || hasTask('admin/users/addresstype')) /*hasTask('admin/addresstype/create')*/ {?>
			<li  class="parent {{ Request::is('admin/users*') ? 'active' : ''|| Request::is('admin/user*') ? 'active' : '' || Request::is('admin/groups*') ? 'active' : '' || Request::is('admin/addresstype*') ? 'active' : ''}}" ><a href=""><i class="fa fa-users"></i> <span>@lang('messages.Users')</span></a>
				<ul class="children">
					<?php if (hasTask('admin/users/groups')) {?>
						<li class="{{ Request::is('admin/users/groups*') ? 'active' : '' || Request::is('admin/groups*') ? 'active' : '' }}" ><a  href="{{ URL::to('admin/users/groups') }}">@lang('messages.Groups')</a></li>
					<?php }?>
					<?php if (hasTask('admin/users/addresstype')) {?>
						<li class="{{ Request::is('admin/users/addresstype*') ? 'active' : '' || Request::is('admin/address*') ? 'active' : '' || Request::is('admin/addresstype*') ? 'active' : ''}}" ><a  href="{{ URL::to('admin/users/addresstype') }}">@lang('messages.Address Type')</a></li>
					<?php }?>
					<?php if (hasTask('admin/users/index')) {?>
						<li class="{{ Request::is('admin/users/index*') ? 'active' : '' || Request::is('admin/users/create*') ? 'active' : '' || Request::is('admin/users/edit*') ? 'active' : '' }}" ><a  href="{{ URL::to('admin/users/index') }}">@lang('messages.Manage Users')</a></li>
					<?php }?>
				</ul>
			</li>
		<?php }?>
		<?php if (hasTask('admin/banners')) {?>
				<li  class="{{ Request::is('admin/banners*') ? 'active' : '' || Request::is('admin/banner*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/banners') }}"><i class="fa  fa-file-image-o"></i> <span>@lang('messages.Banners')</span></a></li>
		 <?php }?>

		 <?php if (hasTask('admin/template/subjects') || hasTask('admin/templates/email')) {?>
		 <li class="parent {{ Request::is('admin/templates*') ? 'active' : '' || Request::is('admin/template/subjects*') ? 'active' : '' || Request::is('admin/subjects*') ? 'active' : ''}}"><a  href="javascript:;"><i class="fa fa-envelope-o"></i> <span>@lang('messages.Email Notification')</span></a>
			<ul class="children">
			<?php /**<?php if(hasTask('admin/template/subjects')){ ?>
<li class="{{ Request::is('admin/template/subjects*') ? 'active' : '' || Request::is('admin/subjects*') ? 'active' : ''}}" ><a  href="{{ URL::to('admin/template/subjects') }}">@lang('messages.Notification Subject')</a></li>
<?php } **/?>
				 <?php if (hasTask('admin/templates/email')) {?>
				<li class="{{ Request::is('admin/templates/email*') ? 'active' : '' || Request::is('admin/templates*') ? 'active' : '' }}" ><a  href="{{ URL::to('admin/templates/email') }}">@lang('messages.Notification Templates')</a></li>
				<?php }?>
			</ul>
		</li>
		<?php }?>
		<?php if (hasTask('admin/blog')) {?>
			<li class="{{ Request::is('admin/blog*') ? 'active' : '' }}"><a href="{{ URL::to('admin/blog') }}"><i class="fa fa-file-text-o"></i> <span>@lang('messages.Blog')</span></a></li>
		<?php }?>
		<?php /**<li class="{{ Request::is('admin/portfolio*') ? 'active' : '' }}"><a href="{{ URL::to('admin/portfolio') }}"><i class="fa fa-briefcase"></i> <span>@lang('messages.Portfolio')</span></a></li>*/?>
		<?php if (hasTask('admin/settings/general')) {?>
			<li class="parent {{ Request::is('admin/settings*') ? 'active' : '' || Request::is('admin/localisation*') ? 'active' : '' || Request::is('admin/country*') ? 'active' : '' || Request::is('admin/localisation/language*') ? 'active' : '' || Request::is('admin/language*') ? 'active' : '' || Request::is('admin/city*') ? 'active' : '' || Request::is('admin/currency*') ? 'active' : '' || Request::is('admin/stockstatus*') ? 'active' : '' || Request::is('admin/orderstatus*') ? 'active' : '' || Request::is('admin/returnstatus*') ? 'active' : '' || Request::is('admin/returnaction*') ? 'active' : '' || Request::is('admin/returnreason*') ? 'active' : '' || Request::is('admin/payment*') ? 'active' : '' || Request::is('admin/modules*') ? 'active' : '' || Request::is('admin/delivery*') ? 'active' : '' || Request::is('admin/zones*') ? 'active' : '' }}">
				<a  href="javascript:;"><i class="fa fa-anchor"></i> <span>@lang('messages.Settings')</span></a>
				<ul class="children">
					<li class="{{ Request::is('admin/settings/general*') ? 'active' : '' }}" ><a  href="{{ URL::to('admin/settings/general') }}">@lang('messages.General')</a></li>

<li class="{{ Request::is('admin/settings/customer') ? 'active' : '' }}" ><a  href="{{ URL::to('admin/settings/customer') }}">@lang('messages.Customer_core')</a></li>


					
					<?php /*<li class="{{ Request::is('admin/settings/store*') ? 'active' : '' }}" ><a  href="{{ URL::to('admin/settings/store') }}">@lang('messages.Store')</a></li>*/?>
					<li class="{{ Request::is('admin/settings/local*') ? 'active' : '' }}"><a href="{{ URL::to('admin/settings/local') }}">@lang('messages.Local')</a></li>
					<li class="{{ Request::is('admin/settings/email*') ? 'active' : '' }}"><a href="{{ URL::to('admin/settings/email') }}">@lang('messages.Email')</a></li>
					<li class="{{ Request::is('admin/settings/socialmedia*') ? 'active' : '' }}"><a href="{{ URL::to('admin/settings/socialmedia') }}">@lang('messages.Social Media')</a></li>
					<li class="{{ Request::is('admin/settings/image*') ? 'active' : '' }}"><a href="{{ URL::to('admin/settings/image') }}">@lang('messages.Image')</a></li>
					<li class="parent {{ Request::is('admin/localisation*') ? 'active' : '' || Request::is('admin/country*') ? 'active' : '' || Request::is('admin/localisation/language*') ? 'active' : '' || Request::is('admin/language*') ? 'active' : '' || Request::is('admin/city*') ? 'active' : ''|| Request::is('admin/currency*') ? 'active' : '' || Request::is('admin/stockstatus*') ? 'active' : '' || Request::is('admin/orderstatus*') ? 'active' : '' || Request::is('admin/returnstatus*') ? 'active' : '' || Request::is('admin/returnaction*') ? 'active' : '' || Request::is('admin/returnreason*') ? 'active' : '' || Request::is('admin/payment*') ? 'active' : '' || Request::is('admin/modules*') ? 'active' : '' || Request::is('admin/zones*') ? 'active' : '' }} ">
						<a href="javascript:void();">@lang('messages.Localisation')</a>
						<ul class="sub children">
							<li class="{{ Request::is('admin/localisation/country*') ? 'active' : '' || Request::is('admin/country*') ? 'active' : ''  }}" ><a href="{{ URL::to('admin/localisation/country') }}">@lang('messages.Countries')</a></li>
							<li class="{{ Request::is('admin/localisation/zones*') ? 'active' : '' || Request::is('admin/zones*') ? 'active' : ''  }}"><a href="{{ URL::to('admin/localisation/zones') }}">@lang('messages.Zones')</a></li>
							<li class="{{ Request::is('admin/localisation/city*') ? 'active' : '' || Request::is('admin/city*') ? 'active' : ''  }}"><a href="{{ URL::to('admin/localisation/city') }}">@lang('messages.Cities')</a></li>
							<li class="{{ Request::is('admin/localisation/language*') ? 'active' : '' || Request::is('admin/language*') ? 'active' : ''  }}"><a  href="{{ URL::to('admin/localisation/language') }}">@lang('messages.Languages')</a></li>
							<li class="{{ Request::is('admin/localisation/currency*') ? 'active' : '' || Request::is('admin/currency*') ? 'active' : ''  }}" ><a href="{{ URL::to('admin/localisation/currency') }}">@lang('messages.Currencies')</a></li>
							<li style="display: none;" class="{{ Request::is('admin/localisation/stockstatuses*') ? 'active' : '' || Request::is('admin/stockstatus*') ? 'active' : ''  }}" ><a href="{{ URL::to('admin/localisation/stockstatuses') }}">@lang('messages.Stock Statuses')</a></li>
							<li style="display: none;" class="{{ Request::is('admin/localisation/orderstatuses*') ? 'active' : '' || Request::is('admin/orderstatus*') ? 'active' : ''  }}"><a href="{{ URL::to('admin/localisation/orderstatuses') }}">@lang('messages.Order Statuses')</a></li>
							<li style="display: none;" class="{{ Request::is('admin/localisation/returnstatuses*') ? 'active' : '' || Request::is('admin/returnstatus*') ? 'active' : '' }}"><a href="{{ URL::to('admin/localisation/returnstatuses') }}">@lang('messages.Return Statuses')</a></li>
							<li style="display: none;" class="{{ Request::is('admin/localisation/returnactions*') ? 'active' : '' || Request::is('admin/returnaction*') ? 'active' : '' }}"><a href="{{ URL::to('admin/localisation/returnactions') }}">@lang('messages.Return Actions')</a></li>
							<li style="display: none;" class="{{ Request::is('admin/localisation/returnreasons*') ? 'active' : '' || Request::is('admin/returnreason*') ? 'active' : '' }}"><a href="{{ URL::to('admin/localisation/returnreasons') }}">@lang('messages.Return Reasons')</a></li>
							<li class="{{ Request::is('admin/localisation/weight_classes*') ? 'active' : '' || Request::is('admin/localisation/create_weight_class*') ? 'active' : '' || Request::is('admin/localisation/edit_weight_class*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/localisation/weight_classes') }}">@lang('messages.Weight Classes')</a></li>
						</ul>
					</li>
					<li class="{{ Request::is('admin/payment*') ? 'active' : '' }}"><a href="{{ URL::to('admin/payment/settings') }}">@lang('messages.Payment')</a></li>
					<li style="display: none;" class="{{ Request::is('admin/modules/settings*') ? 'active' : '' || Request::is('admin/modules/edit*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/modules/settings') }}">@lang('messages.Module Settings')</a></li>
					<li class="{{ Request::is('admin/delivery/time-interval*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/delivery/time-interval') }}">@lang('messages.Time interval')</a></li>
					<li class="{{ Request::is('admin/delivery/slot-setting*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/delivery/slot-setting') }}">@lang('messages.Delivery slots')</a></li>
					<li class="{{ Request::is('admin/modules/delivery_settings*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/modules/delivery_settings') }}">@lang('messages.Delivery Settings')</a></li>


					<!--driver core settings-->
					<li class="{{ Request::is('admin/settings/driver_core_settings*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/settings/driver_core_settings') }}">@lang('messages.Driver Core Settings')</a></li>
					<!--driver core settings-->


					<!--refferal settings-->
					<li class="{{ Request::is('admin/refferal/refferal_settings*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/refferal/refferal_settings') }}">@lang('messages.Refferal Settings')</a></li>
					<!--refferal settings-->
					
					<!--Terms Of Service-->
					<!-- <li class="{{ Request::is('admin/settings/terms_of_service*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/settings/terms_of_service') }}">@lang('messages.Terms of Serivce')</a></li> -->
					<!--Terms Of Service-->
					

					<!--customer promotion-->
					<li class="{{ Request::is('admin/settings/customer_promotion*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/settings/customer_promotion') }}">@lang('messages.Customer Promotion')</a></li>
					<!--customer promotion-->


					<!--customer promotion-->
					<li class="{{ Request::is('admin/customer_promotion/offer*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/customer_promotion/offer') }}">@lang('messages.Customer Promotion new')</a></li>
					<!--customer promotion-->


				</ul>
			</li>
		<?php }?>
		<?php if (hasTask('admin/cms')) {?>
			<li class="{{ Request::is('admin/cms*') ? 'active' : '' }}"><a href="{{ URL::to('admin/cms') }}"><i class="fa fa-file-text-o"></i> <span>@lang('messages.Cms')</span></a></li>
		<?php }?>
		<?php if (hasTask('system/permission') || hasTask('permission/users')) {?>
			<li class="parent {{ Request::is('system/permission*') ? 'active' : '' || Request::is('permission/users*') ? 'active' : '' || Request::is('permission*') ? 'active' : '' }}"><a  href="javascript:;"><i class="fa fa-desktop"></i> <span>@lang('messages.Permission')</span></a>
				<ul class="children">
					<?php if (hasTask('system/permission')) {?>
					<li class="{{ Request::is('system/permission*') ? 'active' : '' }}" ><a  href="{{ URL::to('system/permission') }}">@lang('messages.Roles')</a></li>
					<?php }?>
					<?php if (hasTask('permission/users')) {?>
					<li class="{{ Request::is('permission/users*') ? 'active' : '' || Request::is('permission*') ? 'active' : '' }}" ><a  href="{{ URL::to('permission/users') }}">@lang('messages.Users')</a></li>
					<?php }?>
				</ul>
			</li>
		<?php }?>
        <?php if (hasTask('vendors/vendors') || hasTask('vendors/outlets') || hasTask('vendors/outlet_managers') || hasTask('admin/vendors/bulkimport')) {?>
			<li class="parent {{ Request::is('vendors/*') ? 'active' : ''}}"><a  href="javascript:;"><i class="fa fa-rocket"></i> <span>@lang('messages.Vendors')</span></a>
				<ul class="children">
					<?php if (hasTask('vendors/vendors')) {?>
						<li class="{{ (Request::is('vendors/vendors*') || Request::is('vendors/create_vendor') || Request::is('vendors/edit_vendor/*') || Request::is('vendors/vendor_details/*')) ? 'active' : '' }}" ><a  href="{{ URL::to('vendors/vendors') }}">@lang('messages.Vendors')</a></li>
					<?php }?>
					<?php if (hasTask('vendors/bulkimport')) {?>
						<li class="{{ (Request::is('vendors/bulkimport')) ? 'active' : '' }}" ><a  href="{{ URL::to('vendors/bulkimport') }}">@lang('messages.Outlet Products')</a></li>
					<?php }?>
					<?php if (hasTask('vendors/outlets')) {?>
						<li class="{{ (Request::is('vendors/outlets*') || Request::is('vendors/create_outlet') || Request::is('vendors/edit_outlet/*') || Request::is('vendors/outlet_details/*'))? 'active' : '' }}" ><a  href="{{ URL::to('vendors/outlets') }}">@lang('messages.Oulets')</a></li>
					<?php }?>
					<?php if (hasTask('vendors/outlet_managers')) {?>
						<li class="{{ (Request::is('vendors/outlet_managers*') || Request::is('vendors/create_outlet_managers') || Request::is('vendors/edit_outlet_manager/*')) ? 'active' : '' }}" ><a  href="{{ URL::to('vendors/outlet_managers') }}">@lang('messages.Oulet Managers')</a></li>
					<?php }?>

				</ul>
			</li>
		<?php }?>
		<?php if (hasTask('admin/products')) {?>
			<li  class="{{ Request::is('admin/products*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/products') }}"><i class="fa fa-cubes"></i> <span>@lang('messages.Products')</span></a></li>
		<?php }?>
		<?php if (hasTask('admin/orders/index') || hasTask('orders/return_orders') || hasTask('orders/fund_requests')) {?>
			<li class="parent {{ (Request::is('orders/*') || Request::is('admin/orders*')) ? 'active' : ''}}"><a  href="javascript:;"><i class="fa fa-database"></i> <span>@lang('messages.Sales')</span></a>
				<ul class="children">
				<?php if (hasTask('admin/orders/index')) {?>
						<li class="{{ Request::is('admin/orders/*') ? 'active' : '' }}" ><a  href="{{ URL::to('admin/orders/index') }}">@lang('messages.Orders')</a></li>
				<?php }?>
				<?php if (hasTask('orders/return_orders')) {?>
					<li class="{{ Request::is('orders/return_orders*') ? 'active' : '' }}" ><a  href="{{ URL::to('orders/return_orders') }}">@lang('messages.Return Orders')</a></li>
				<?php }?>
			     <?php if (hasTask('orders/fund_requests')) {?>
					<li class="{{ Request::is('orders/fund_requests*') ? 'active' : '' }}" ><a  href="{{ URL::to('orders/fund_requests') }}">@lang('messages.Amount Requests')</a></li>
				<?php }?>
				</ul>
			</li>
		<?php }?>
		<?php if (hasTask('reports/order') || hasTask('reports/returns') || hasTask('reports/user') || hasTask('reports/vendors')) {?>
			<li class="parent {{ (Request::is('reports/*')) ? 'active' : ''}}"><a  href="javascript:;"><i class="fa fa-bar-chart-o"></i> <span>@lang('messages.Reports & Analytics')</span></a>
				<ul class="children">
					<?php if (hasTask('reports/order')) {?>
						<li class="{{ Request::is('reports/order') ? 'active' : '' }}" ><a  href="{{ URL::to('reports/order') }}">@lang('messages.Orders')</a></li>
					<?php }?>
					<?php if (hasTask('reports/returns')) {?>
						<li class="{{ Request::is('reports/returns') ? 'active' : '' }}" ><a  href="{{ URL::to('reports/returns') }}">@lang('messages.Return Orders')</a></li>
					<?php }?>
					<?php if (hasTask('reports/user')) {?>
						<li class="{{ Request::is('reports/user') ? 'active' : '' }}" ><a  href="{{ URL::to('reports/user') }}">@lang('messages.Customers')</a></li>
					<?php }?>
					<?php if (hasTask('reports/coupons')) {?>
						<li class="{{ Request::is('reports/coupons') ? 'active' : '' }}" ><a  href="{{ URL::to('reports/coupons') }}">@lang('messages.Coupons')</a></li>
					<?php }?>
					<?php if (hasTask('reports/products')) {?>
						<li class="{{ Request::is('reports/products') ? 'active' : '' }}" ><a  href="{{ URL::to('reports/products') }}">@lang('messages.Products')</a></li>
					<?php }?>

					<?php /*<?php if(hasTask('reports/vendors')){ ?>
<li class="{{ Request::is('reports/vendor') ? 'active' : '' }}" ><a  href="{{ URL::to('reports/vendor') }}">@lang('messages.Vendors Overview')</a></li>
<?php } ?> */?>
				</ul>
			</li>
		<?php }?>
		<?php if (hasTask('admin/drivers') || hasTask('admin/driver-location') || hasTask('admin/driver-settings')) {?>
			<li class="parent {{ (Request::is('admin/drivers*')) ? 'active' : '' || Request::is('admin/driver*') ? 'active' : ''}}"><a  href="javascript:;"><i class="fa fa-automobile"></i> <span>@lang('messages.Drivers')</span></a>
				<ul class="children">
					<?php if (hasTask('admin/drivers')) {?>
						<li class="{{ Request::is('admin/drivers') ? 'active' : '' }}" ><a  href="{{ URL::to('admin/drivers') }}">@lang('messages.Drivers')</a></li>
					<?php }?>
					<?php if (hasTask('admin/driver-location')) {?>
						<li class="{{ Request::is('admin/driver-location') ? 'active' : '' }}" ><a  href="{{ URL::to('admin/driver-location') }}">@lang('messages.Drivers Location')</a></li>
					<?php }?>
					<?php if (hasTask('admin/driver-settings')) {?>
						<li class="{{ Request::is('admin/driver-settings') ? 'active' : '' }}" ><a  href="{{ URL::to('admin/driver-settings') }}">@lang('messages.Drivers Settings')</a></li>
					<?php }?>
				</ul>
			</li>
		<?php }?>

		<?php if (hasTask('admin/coupons')) {?>
		<li  class="{{ Request::is('admin/coupons*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/coupons') }}"><i class="fa fa-tags"></i> <span>@lang('messages.Coupons')</span></a></li>
		<?php }?>



<?php if (hasTask('admin/faq/index')) {?>
		<li  class="{{ Request::is('admin/faq/index*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/faq/index') }}"><i class="fa fa-fighter-jet
"></i> <span>@lang('messages.Faq')</span></a></li>
		<?php }?>



		<?php if (hasTask('admin/subscribers')) {?>
			<li  class="{{ Request::is('admin/subscribers*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/subscribers') }}"><i class="fa fa-child"></i> <span>@lang('messages.Subscribers')</span></a></li>
        <?php }?>
        <?php if (hasTask('admin/newsletter')) {?>
			<li  class="{{ Request::is('admin/newsletter*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/newsletter') }}"><i class="fa fa-reply-all"></i> <span>@lang('messages.Newsletter')</span></a></li>
        <?php }?>
        <?php if (hasTask('admin/reviews')) {?>
			<li  class="{{ Request::is('admin/reviews*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/reviews') }}"><i class="glyphicon  glyphicon-star"></i> <span>@lang('messages.Reviews')</span></a></li>
		<?php }?>


		<li style="display: none;"  class="{{ Request::is('admin/import_products*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/import_products') }}"><i class="glyphicon  glyphicon-star"></i> <span>@lang('messages.import products')</span></a></li>

		<?php if (hasTask('admin/notifications')) {?>
			<li class="parent {{ Request::is('admin/notifications*') ? 'active' : '' || Request::is('admin/email-notifications*') ? 'active' : '' || Request::is('admin/push-notifications*') ? 'active' : '' }}">
			<a href="javascript:;"><i class="fa fa-desktop"></i><span>@lang('messages.Notifications')</span></a>

				<ul class="children">
					<?php if (hasTask('admin/email-notifications')) {?>
						<li class="{{ Request::is('admin/notifications**') ? 'active' : '' }}" ><a href="{{ URL::to('admin/notifications') }}"><i class="fa fa-volume-down"></i> <span>@lang('messages.Notifications')</span></a></li>
					<?php }?>
				</ul>

				<ul class="children">
					<?php if (hasTask('admin/email-notifications')) {?>
						<li class="{{ Request::is('admin/email-notifications*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/email-notifications') }}"><i class="fa fa-envelope-square"></i> <span>@lang('Email notifications')</span></a></li>
					<?php }?>
				</ul>
				<ul class="children">
					<?php if (hasTask('admin/push-notifications')) {?>
						<li class="{{ Request::is('admin/push-notifications*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/push-notifications') }}"><i class="fa fa-bell"></i> <span>@lang('Push notifications')</span></a></li>
					<?php }?>
				</ul>
			</li>
		<?php }?>
		<?php if (hasTask('admin/feedback/index')) {?>
		<li  class="{{ Request::is('admin/feedback/index*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/feedback/index') }}"><i class="fa fa-comments-o"></i> <span>@lang('messages.Customer Feedback')</span></a></li>
		<?php }?>

		 <?php /*<?php if(hasTask('admin/brands')){ ?>
<li class="{{ Request::is('admin/brands*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/brands') }}"><i class="fa fa-bell"></i> <span>@lang('messages.Brands')</span></a></li>
<?php } ?>
if(hasTask('admin/product_reviews')){ ?>
<li  class="{{ Request::is('admin/product-reviews*') ? 'active' : '' }}" ><a href="{{ URL::to('admin/product-reviews') }}"><i class="glyphicon  glyphicon-star"></i> <span>@lang('messages.Product Reviews')</span></a></li>
<?php } */?>
	</ul>
	<footer>
        @include('includes.admin.footer')
    </footer>
</div><!-- leftpanel -->
