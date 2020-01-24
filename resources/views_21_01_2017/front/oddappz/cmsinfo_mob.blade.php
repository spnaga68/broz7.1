

    <!-- container start -->
    <section class="inner_cms_section">
        <div class="container">
            <div class="cms_inner_cont_mob">
                @if(count($cmsinfo) > 0 )
                    <?php /*<h1>{{ucfirst($cms_details->title)}}</h1>*/ ?>
                    <p><?php echo $cmsinfo->content;?></p>
                @else
                    <h1>@lang('messages.No data found')</h1>
                @endif
            </div>
        </div>
    </section>
