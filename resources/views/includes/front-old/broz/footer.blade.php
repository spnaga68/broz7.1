<?php $general = Session::get("general");?>

  <style type="text/css">
         <?php if(request()->path() == 'driverabout-us' || request()->path() == 'customerabout-us' || request()->path() == 'customerterms-condition'|| request()->path() == 'driverterms-condition'|| request()->path() == 'customer_privacy_policy.html'){  ?>
          .main_footer{
            display: none;
          <?php }
          ?> 
 
      </style>
<footer class="footer main_footer">
    <div class="container">
        <div class="footer_common row">
            <div class="col-md-12 col-sm-12 col-xs-12 footer_descr">
            <div class="col-md-5 padding_left0">
             <a  href="<?php echo url('/'); ?>" title="{{ Session::get('general')->site_name }}">
                        <img src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/footer_logo.png'); ?>" title="{{ Session::get('general')->site_name }}" alt="{{ Session::get('general')->site_name }}">
                        </a>
                        </div>
                    <div class="col-md-7 padding0">
                        {!!Form::open(array('class'=>'subscriber_sce'));!!}
                            <div class="col-md-10 col-sm-8 col-xs-8 padding0">
                                <div class="form-group">
                                    <input id="exampleInputEmail1" class="form-control" name="subscribe_email" required placeholder="@lang('messages.Enter your email')" type="text">
                                    <input type="hidden" name="c_language" value="{{getCurrentLang()}}" id="c_language">
                                </div>
                            </div>
                            <?php /*<div class="col-md-2 col-md-2 col-sm-4 col-xs-4 padding_left0 padding0"><button class="btn btn-default" id="subscribe_btn" type="button">@lang('messages.Subscribe')</button></div>*/ ?>
                           <div class="col-md-2 col-md-2 col-sm-4 col-xs-4 padding_left0 padding0"><button class="btn btn-default" type="button" id="subscribe_btn" title="@lang('messages.Subscribe')" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> @lang('messages.Processing')">@lang('messages.Subscribe')</button></div>
                        {!!Form::close();!!}
                    </div>
               <p>@lang("messages.Do not be last ont to know our news! Do not skip the most important news subscribe to oddappz news.emails to get the latest offers & Feeds you need to know,every day.")</p>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-6">
                <div class="cms_listing">
                    <ul>
						 <li><a href="{{ URL::to('/weare-hiring') }}" title="@lang('messages.We’re hiring')">@lang('messages.We’re hiring')</a></li>
                    <li><a href="{{ URL::to('/blog') }} " title="@lang('messages.Blog')">@lang('messages.Blog')</a></li>
                    <li><a href="{{ URL::to('/register-your-store') }}" title="@lang('messages.Register your store')">@lang('messages.Register your store')</a></li>
						  <li><a href="{{ url('vendors/login') }}" title="@lang('messages.Vendors')">@lang('messages.Vendors')</a></li>
                   </ul>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-6">
                <div class="cms_listing">
                    <ul>
					
			 <li><a href="{{ URL::to('/about-us') }}" title="@lang('messages.About us')">@lang('messages.About us')</a></li>
                        <li><a href="{{ URL::to('/contact-us') }}" title="@lang('messages.Contact')">@lang('messages.Contact')</a></li>
                        <li><a href="{{ URL::to('/contact-us') }}" title="@lang('messages.Press contact')">@lang('messages.Press contact')</a></li>
                        <li><a href="{{ URL::to('/about-us') }}" title="@lang('messages.Our service areas')">@lang('messages.Our service areas')</a></li>
					
                    
                    </ul>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-6">
                <div class="cms_listing">

                        <!--<h3 class="new_info_title">@lang('messages.Extras')</h3>!-->
                        
                         <ul>
                         <li><a href="{{ URL::to('/cms/faq') }}" title="@lang('messages.FAQ')">@lang('messages.FAQ')</a></li> 
                        <?php $i=1; ?>
                        @foreach(getCms() as $key => $value)
                        @if($i<=4)
                        <li><a title="{{ ucfirst($value->title) }}" href="<?php echo URL::to('/cms/'.$value->url_index.''); ?>">{{ ucfirst($value->title) }} </a>
                        </li>
                        @endif
                        <?php $i++; ?>
                        @endforeach
                        
                    </ul>

                </div>
            </div>
          <div class="col-md-3 col-sm-6 col-xs-6">
                <div class="cms_listing">
                    <address>
                        <p><i class="glyph-icon flaticon-paper"></i><?php echo getAppConfig()->email; ?></p>
                        <label><i class="glyph-icon flaticon-phone-receiver"></i><?php echo getAppConfig()->telephone; ?></label>
                        <p>FAX:<?php echo getAppConfig()->fax; ?></p>
                    </address>
                </div>
            </div>
        </div>
        <div class="copy_rights">
            <div class="social_share">
                <ul>
                    <li><a href="<?php echo Session::get("social")->instagram_page; ?>" target="_blank" title="@lang('messages.Instagram')"><i class="glyph-icon flaticon-instagram-social-network-logo-of-photo-camera"></i></a></li>
                    <li><a href="<?php echo Session::get("social")->facebook_page; ?>" target="_blank" title="@lang('messages.Facebook')"><i class="glyph-icon flaticon-facebook-logo"></i></a></li>
                    <li><a href="<?php echo Session::get("social")->twitter_page; ?>" target="_blank" title="@lang('messages.Twitter')"><i class="glyph-icon flaticon-twitter-logo-silhouette"></i></a></li>
                    <li><a href="<?php echo Session::get("social")->linkedin_page; ?>" target="_blank" title="@lang('messages.linkein')"><i class="glyph-icon flaticon-linkedin-logo"></i></a></li>
                </ul>
            <p><?php echo getSettingsLists()->copyrights;?></p>
            </div>
        </div>
    </div>
</footer>
<?php /** 
<!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/57f4ce86bb785b3a47d76a2c/default';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
*/ ?>
<!--End of Tawk.to Script-->
@include('front.'.Session::get("general")->theme.'.login')
@include('front.'.Session::get("general")->theme.'.member_signup')
@include('front.'.Session::get("general")->theme.'.signup')
@include('front.'.Session::get("general")->theme.'.forgot')
@include('front.'.Session::get("general")->theme.'.store_register')

<script type="text/javascript">
    $(document).ready(function() {
        $('#subscribe_btn').on('click', function(event ){
            var subscribe_email = $('#exampleInputEmail1').val();
            var c_language      = $('#c_language').val();
           // alert(c_language);
            if(subscribe_email == "")
            {
                toastr.warning("<?php echo trans('messages.Please fill subscribe email') ?>");
                return false;
            }
            var $this = $("#subscribe_btn");
            $this.button('loading');
            var c_url = '/user-subscribe';
            token = $('input[name=_token]').val();
            $.ajax({
                url: c_url,
                headers: {'X-CSRF-TOKEN': token},
                data: {subscribe_email: subscribe_email,language:c_language},
                type: 'POST',
                datatype: 'JSON',
                success: function (resp) 
                {
                    $this.button('reset');
					console.log(resp);
                    //$("#fadpage").hide();
                    if(resp.httpCode == 400)
                    {
                        toastr.error(resp.Message);
                        return false;
                    }
                    toastr.success(resp.Message);
                    $('.subscriber_sce')[0].reset();
                    return false;
                },
                error:function(resp)
                {
                    console.log('out--'+data); 
                    return false;
                }
            });
            return false;
        });
    });
   
</script>
</script>
</body>
</html>

