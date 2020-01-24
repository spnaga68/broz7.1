    @extends('layouts.app')
	@section('content')
<script src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/js/select2.min.js');?>"></script>
	<?php $segment = Request::segment(2);?>
	<?php if($segment=='about-us'){ ?>
       <section class="banner_about">
        <div class="container">
            <div class="captcha">
                <h1>@lang('messages.We are on a mission to help mankind live healthier, longer.')</h1>
            </div>
        </div>
    </section>
    <?php } ?>
	    
<section class="store_item_list">
<div class="container">
<div class="cms_pages">
<div class="stor_title">
<h1>{{ ucfirst($cmsinfo[0]->title) }}</h1>
</div>
<?php echo $cmsinfo[0]->content; ?>
</div>
</div>
</div>
        
    </section>

    <!-- container end -->        
            <script type="text/javascript">

				 $('select').select2();
        // get header height (without border)
        var getHeaderHeight = $('.headerContainerWrapper').outerHeight();

        // border height value (make sure to be the same as in your css)
        var borderAmount = 2;

        // shadow radius number (make sure to be the same as in your css)
        var shadowAmount = 30;

        // init variable for last scroll position
        var lastScrollPosition = 0;

        // set negative top position to create the animated header effect
        $('.headerContainerWrapper').css('top', '-' + (getHeaderHeight + shadowAmount + borderAmount) + 'px');

        $(window).scroll(function() {
            var currentScrollPosition = $(window).scrollTop();
            if ($(window).scrollTop() > 2 * (getHeaderHeight + shadowAmount + borderAmount)) {
                $('body').addClass('scrollActive').css('padding-top', getHeaderHeight);
                $('.headerContainerWrapper').css('top', 0);
                if (currentScrollPosition < lastScrollPosition) {
                    $('.headerContainerWrapper').css('top', '-' + (getHeaderHeight + shadowAmount + borderAmount) + 'px');
                }
                lastScrollPosition = currentScrollPosition;
            } else {
                $('body').removeClass('scrollActive').css('padding-top', 0);
            }
        });
        function toggleChevron(e) {
			alert('in'); 
            $(e.target)
                .prev('.panel-heading')
                .find("i.indicator")
                .toggleClass('glyphicon-chevron-down glyphicon-chevron-up');
        }
       
    </script>
    
    <script>
         $(window).load(function() {
				$('.accordion').on('hidden.bs.collapse', toggleChevron);
				$('.accordion').on('shown.bs.collapse', toggleChevron);
        });
    </script>
    <!-- content end -->
    @endsection
