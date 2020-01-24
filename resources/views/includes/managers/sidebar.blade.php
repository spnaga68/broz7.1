<div class="leftpanel">
    <div class="media profile-left">
        <?php $manager_id = Session::get('manager_image'); ?>
        <a class="pull-left profile-thumb">
           <?php if(file_exists(base_path().'/public/assets/admin/base/images/managers/'.$manager_id) && $manager_id != '') { ?>
                <img src="<?php echo url('/assets/admin/base/images/managers/'.$manager_id.'?'.time()); ?>" class="img-circle">
            <?php } else{  ?>
                <img src=" {{ URL::asset('assets/admin/base/images/a2x.jpg') }} " class="img-circle">
            <?php } ?>
        </a>
        <div class="media-body">
            <h4 class="media-heading"><?php echo ucfirst(Session::get('manager_name'));?></h4>
            <small class="text-muted"> <a href="{{ url('managers/editprofile') }}" title="Edit Profile">@lang('messages.Edit Profile')</a> </small>
        </div>
    </div><!-- media -->
    <ul class="nav nav-pills nav-stacked">
         <li class="{{ Request::is('managers/dashboard*') ? 'active' : '' }}" ><a href="{{ URL::to('managers/dashboard') }}"><i class="fa  fa-home"></i> <span>@lang('messages.Dashboard')</span></a></li>
        <li class="{{ Request::is('managers/products*') ? 'active' : '' }}"><a href="{{ URL::to('managers/products') }}"><i class="fa fa-cubes"></i> <span>@lang('messages.Products')</span></a></li>
        <li class="{{ Request::is('managers/reviews*') ? 'active' : '' }}" ><a href="{{ URL::to('managers/reviews') }}"><i class="glyphicon  glyphicon-star"></i> <span>@lang('messages.Reviews')</span></a></li>
        <li class="parent {{ (Request::is('managers/return_orders*') || Request::is('managers/orders*') || Request::is('managers/request_amount/*')) ? 'active' : ''}}"><a  href="#"><i class="fa fa-database"></i> <span>@lang('messages.Sales')</span></a>
            <ul class="children">
                <li class="{{ Request::is('managers/orders*') ? 'active' : '' }}" ><a  href="{{ URL::to('managers/orders/index') }}">@lang('messages.Orders')</a></li>
                <?php /*<li class="{{ Request::is('managers/return_orders*') ? 'active' : '' }}" ><a  href="{{ URL::to('managers/return_orders') }}">@lang('messages.Return Orders')</a></li>*/ ?>
            </ul>
        </li>
      <?php /*  <li class="parent {{ (Request::is('managers/report_return_orders*') || Request::is('managers/report_orders*') || Request::is('managers/report_products*')) ? 'active' : ''}}"><a  href="#"><i class="fa fa-bar-chart-o"></i> <span>@lang('messages.Reports & Analytics')</span></a>
            <ul class="children">
                <li class="{{ Request::is('managers/report_orders*') ? 'active' : '' }}" ><a  href="{{ URL::to('managers/report_orders') }}">@lang('messages.Orders')</a></li>
                <?php /*<li class="{{ Request::is('managers/report_return_orders*') ? 'active' : '' }}" ><a  href="{{ URL::to('managers/report_return_orders') }}">@lang('messages.Return Orders')</a></li>
                <li class="{{ Request::is('managers/report_products*') ? 'active' : '' }}" ><a  href="{{ URL::to('managers/report_products') }}">@lang('messages.Products')</a></li>
            </ul>
        </li> */ ?>
        <li class="{{ Request::is('managers/notifications*') ? 'active' : '' }}" ><a href="{{ URL::to('managers/notifications') }}"><i class="fa fa-bell"></i> <span>@lang('messages.Notifications')</span></a></li>
    </ul>
    <footer>
        @include('includes.managers.footer')
    </footer>
</div><!-- leftpanel -->
