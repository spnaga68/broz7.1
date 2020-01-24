<?php $vendor_id = Session::get('vendor_id'); ?>
<header>
<div class="headerwrapper admin_logo_chngs">
    <div class="header-left">
        <a href="{{ url('vendors/dashboard') }}" class="logo">
    <img src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/logo/159_81/'.Session::get("general")->logo.'?'.time()); ?>" title="'.Session::get('general')->site_name.'" alt="logo">
        </a>
        <div class="pull-right">
    <a href="#" class="menu-collapse">
        <i class="fa fa-bars"></i>
    </a>
        </div>
    </div><!-- header-left -->
    <div class="header-right">
    <?php /*<div class="custom-button col-md-2">
    <!-- Language Translate -->
        {!! Form::open(['method' => 'POST', 'route' => 'changelocale', 'class' => 'form-inline navbar-select']) !!}
        <div class="form-group ">
        
        <div class="select_langu">
        <select id="locale" class="form-control" name="locale" onchange="this.form.submit()" required="required">
        <?php if(count($languages)){ 
        foreach($languages as $key => $val){ ?>
        <option <?php if(App::getLocale()==$val->language_code){ echo "selected";  } ?> value="<?php echo $val->language_code; ?>"><?php echo trans('messages.'.$val->name); ?></option>
        <?php } } ?>
        </select>
        </div>
        <small class="text-danger">{{ $errors->first('locale') }}</small>
        </div>
        <div class="btn-group pull-right sr-only">
        <input class="btn btn-success" type="submit" value="Change">
        </div>
        {!! Form::close() !!}    
    <!-- Language Translate -->
    </div> */ ?>
    <div class="pull-right">
        <?php /*$balance = getBalanceData($vendor_id,1); ?>
        <div class="btn-group btn-group-list">
            <span class="badge"><h5>@lang('messages.Balance') : <?php echo number_format($balance['vendor_balance'],2).' '.getCurrency(); ?></h5></span>
        </div>
		<form class="form form-search" action="http://themepixels.com/demo/webpage/chain/search-results.html">
			<input type="search" class="form-control" placeholder="Search" />
		</form> */ ?>
    <?php $notifications = getNotificationsList($vendor_id);  ?>
    @if(count($notifications)>0)
    <div class="btn-group btn-group-list btn-group-notification">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
          <i class="fa fa-bell-o"></i>
          <span class="badge" id="noti_count"><?php echo count($notifications); ?></span>
        </button>
        <div class="dropdown-menu pull-right">
            <?php /*<a href="#" class="link-right"><i class="fa fa-search"></i></a>*/ ?>
            <h5>Notifications</h5>
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <ul class="media-list dropdown-list" id="notifications">
                @foreach($notifications as $key => $value)
                <li class="media" id="<?php echo $value->id;?>" onclick="read_notifications('<?php echo $value->id;?>')">
                    <img class="img-circle pull-left noti-thumb" src="<?php echo (!empty($value->image))?url('/assets/admin/base/images/admin/profile/thumb/'.$value->image.''):url('assets/admin/base/images/default_avatar_male.jpg');; ?>" alt="">
                    <div class="media-body">
                      <strong><?php echo $value->name; ?> - </strong> <?php echo $value->message; ?>
                      <small class="date"><i class="fa fa-thumbs-up"></i> <?php echo nicetime($value->created_date); ?></small>
                    </div>
                </li>
                @endforeach
            </ul>
        </div><!-- dropdown-menu -->
    </div><!-- btn-group -->
    @endif
    
    <?php /* <div class="btn-group btn-group-list btn-group-notification">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
          <i class="fa fa-bell-o"></i>
          <span class="badge">5</span>
        </button>
        <div class="dropdown-menu pull-right">
            <a href="#" class="link-right"><i class="fa fa-search"></i></a>
            <h5>Notification</h5>
            <ul class="media-list dropdown-list">
        <li class="media">
            <img class="img-circle pull-left noti-thumb" src="images/photos/user1.png" alt="">
            <div class="media-body">
              <strong>Nusja Nawancali</strong> likes a photo of you
              <small class="date"><i class="fa fa-thumbs-up"></i> 15 minutes ago</small>
            </div>
        </li>
        <li class="media">
            <img class="img-circle pull-left noti-thumb" src="images/photos/user2.png" alt="">
            <div class="media-body">
              <strong>Weno Carasbong</strong> shared a photo of you in your <strong>Mobile Uploads</strong> album.
              <small class="date"><i class="fa fa-calendar"></i> July 04, 2014</small>
            </div>
        </li>
        <li class="media">
            <img class="img-circle pull-left noti-thumb" src="images/photos/user3.png" alt="">
            <div class="media-body">
              <strong>Venro Leonga</strong> likes a photo of you
              <small class="date"><i class="fa fa-thumbs-up"></i> July 03, 2014</small>
            </div>
        </li>
        <li class="media">
            <img class="img-circle pull-left noti-thumb" src="images/photos/user4.png" alt="">
            <div class="media-body">
              <strong>Nanterey Reslaba</strong> shared a photo of you in your <strong>Mobile Uploads</strong> album.
              <small class="date"><i class="fa fa-calendar"></i> July 03, 2014</small>
            </div>
        </li>
        <li class="media">
            <img class="img-circle pull-left noti-thumb" src="images/photos/user1.png" alt="">
            <div class="media-body">
              <strong>Nusja Nawancali</strong> shared a photo of you in your <strong>Mobile Uploads</strong> album.
              <small class="date"><i class="fa fa-calendar"></i> July 02, 2014</small>
            </div>
        </li>
            </ul>
            <div class="dropdown-footer text-center">
        <a href="#" class="link">See All Notifications</a>
            </div>
        </div><!-- dropdown-menu -->
    </div><!-- btn-group -->
    
    <div class="btn-group btn-group-list btn-group-messages">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            <i class="fa fa-envelope-o"></i>
            <span class="badge">2</span>
        </button>
        <div class="dropdown-menu pull-right">
            <a href="#" class="link-right"><i class="fa fa-plus"></i></a>
            <h5>New Messages</h5>
            <ul class="media-list dropdown-list">
        <li class="media">
            <span class="badge badge-success">New</span>
            <img class="img-circle pull-left noti-thumb" src="images/photos/user1.png" alt="">
            <div class="media-body">
              <strong>Nusja Nawancali</strong>
              <p>Hi! How are you?...</p>
              <small class="date"><i class="fa fa-clock-o"></i> 15 minutes ago</small>
            </div>
        </li>
        <li class="media">
            <span class="badge badge-success">New</span>
            <img class="img-circle pull-left noti-thumb" src="images/photos/user2.png" alt="">
            <div class="media-body">
              <strong>Weno Carasbong</strong>
              <p>Lorem ipsum dolor sit amet...</p>
              <small class="date"><i class="fa fa-clock-o"></i> July 04, 2014</small>
            </div>
        </li>
        <li class="media">
            <img class="img-circle pull-left noti-thumb" src="images/photos/user3.png" alt="">
            <div class="media-body">
              <strong>Venro Leonga</strong>
              <p>Do you have the time to listen to me...</p>
              <small class="date"><i class="fa fa-clock-o"></i> July 03, 2014</small>
            </div>
        </li>
        <li class="media">
            <img class="img-circle pull-left noti-thumb" src="images/photos/user4.png" alt="">
            <div class="media-body">
              <strong>Nanterey Reslaba</strong>
              <p>It might seem crazy what I'm about to say...</p>
              <small class="date"><i class="fa fa-clock-o"></i> July 03, 2014</small>
            </div>
        </li>
        <li class="media">
            <img class="img-circle pull-left noti-thumb" src="images/photos/user1.png" alt="">
            <div class="media-body">
              <strong>Nusja Nawancali</strong>
              <p>Hey I just met you and this is crazy...</p>
              <small class="date"><i class="fa fa-clock-o"></i> July 02, 2014</small>
            </div>
        </li>
            </ul>
            <div class="dropdown-footer text-center">
        <a href="#" class="link">See All Messages</a>
            </div>
        </div><!-- dropdown-menu -->
    </div><!-- btn-group -->*/?>
    
    <div class="btn-group btn-group-option">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
          <i class="fa fa-caret-down"></i>
        </button>
        <ul class="dropdown-menu pull-right" role="menu">
          <li><a href="{{ url('vendors/editprofile') }}" title="@lang('messages.My Profile')"><i class="glyphicon glyphicon-user"></i>@lang('messages.My Profile')</a></li>
          <li><a href="{{ url('vendors/changepassword') }}" title="@lang('messages.Change Password')"><i class="glyphicon glyphicon-star"></i>@lang('messages.Change Password')</a></li>
          <li><a href="{{ url('vendors/signout') }}" title="@lang('messages.Sign Out')"><i class="glyphicon glyphicon-log-out"></i>@lang('messages.Sign Out')</a></li>
        </ul>
    </div><!-- btn-group -->
    
        </div><!-- pull-right -->
        
    </div><!-- header-right -->
    
</div><!-- headerwrapper -->
<script type="text/javascript">
 $(document).ready( function() {
    if ( $(window).width() < 900) {
    $('.menu-collapse').click(function(){
        $('div.mainwrapper').removeClass('collapsed');
    });
    }
    setTimeout(function() {
            $('.alert-info').fadeOut('fast');
    }, 7500);
 });
function read_notifications(cid)
{
    var token, url, data;
    token = $('input[name=_token]').val();
    url = '{{url('admin/read_notifications')}}';
    data = {cid: cid};
    $.ajax({
        url: url,
        headers: {'X-CSRF-TOKEN': token},
        data: data,
        type: 'POST',
        datatype: 'JSON',
        success: function (resp) {
            if(resp.data==1)
            {
                //alert(resp.data+'-'+resp.count+'-'+resp.vid);
                $('#noti_count').html(resp.count);
                if(resp.count==0)
                {
                    $('#notifications #'+cid+'').html('There is no recent notifications.');
                }
                else {
                    $('#notifications #'+cid+'').remove();
                }
            }
        }
    });
}
</script>
</header>

