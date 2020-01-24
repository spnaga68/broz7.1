      @extends('layouts.app')
      @section('content')
    <section class="banner_sections_inner blog">
<div class="container">
<div class="inner_banner_info">
<h2>@lang('messages.Blog')</h2>
<p>@lang('messages.Where everything on demand is discussed')</p>
</div>
</div>
    </section>
    <section class="error_sections">
<div class="container">
<div class="row">
<div class="title_product">
		<div class="col-md-12">
	<h2>@lang('messages.Blog')</h2>
	<p>@lang('messages.Where everything on demand is discussed')</p>
	</div>
	</div>
<div class="cat_drop_sec">
<div class="col-md-6">
<div class="select_content">
<div class="col-md-10 padding0">
 <select  name="category" class="js-example-disabled-results" id="category" >
	 <option value="">@lang('messages.Category')</option>
	 <?php if(count($category)) { ?>
		 <?php foreach($category as $cat){ ?>
			<option <?php if(Input::get('filter')==$cat->url_key){ ?> selected="selected" <?php } ?>  value="<?php echo $cat->url_key; ?>"><?php echo $cat->category_name; ?></option>
         <?php } ?>
     <?php } ?>

 </select>
											</div>
</div>
</div>
<div class="col-md-6">
<div class="col-md-10 full-right padding0">
<div class="search_box">
                <div class="icon-addon addon-lg">
                    <input type="text" id="keyword" value="<?php echo Input::get('keyword');?>" name="keyword" class="form-control" placeholder="@lang('messages.Search in Keyword...')">
					<div class="search_icon"><button class="btn btn-default" id="search" type="button"><label title="@lang('messages.Search')" rel="tooltip" class="glyphicon glyphicon-search" for="Search"></label></button></div>
                    
                </div>
            </div>
</div>
</div>
</div>
<div class="bolg_listing">
	
	@if (count($blog) > 0 )
		@foreach($blog as $key => $value)
				<div class="col-md-6">
				<div class="blog_list_in">
				<a title="{{ ucfirst($value->title) }}" href="{{ URL::to('/blog/info/' . $value->url_index . '') }}">{{ str_limit($value->title.',', 150) }}</a>
				<div class="blog_list_img">
				<a title="{{ ucfirst($value->title) }}" href="{{ URL::to('/blog/info/' . $value->url_index . '') }}">
                        <?php  if(file_exists(base_path().'/public/assets/admin/base/images/blog/list/'.$value->image)) { ?>
								<img   alt="{{ ucfirst($value->title) }}"  src="<?php echo url('/assets/admin/base/images/blog/list/'.$value->image.''); ?>" >
							<?php } else{  ?>
									<img src="{{ URL::asset('assets/admin/base/images/blog/blog.png') }}" alt="{{ ucfirst($value->title) }}">
							<?php } ?>
                        </a>
				</div>
				<p>{{ str_limit($value->short_notes , 250) }}</p>
				<a href="{{ URL::to('/blog/info/' . $value->url_index . '') }}" title="@lang('messages.Continue Reading')" class="continue_butt"> <span>→</span> @lang('messages.Continue Reading')</a>
				</div>
				</div>
		@endforeach
	@else
		<div class="blog_no_img">
	<img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/blog.png');?>" alt="">
	@lang('<p>No data found.</p>')
	
	</div>
	@endif 
</div>

</div>
</div>
    </section>

<script type="text/javascript">
$( document ).ready(function() {
	$('#category').on('change', function() {
		if(this.value){
			var url = "<?php echo  URL::to('/blog') ?>";
			var cat_id = this.value;
			window.location.assign(url+'?filter='+cat_id)	
		}
	});
	$('#search').on('click', function() {
			var keyword = $('#keyword').val();
			var cat_id = $('#category').val();
			var url = "<?php echo  URL::to('/blog') ?>";
			window.location.assign(url+'?filter='+cat_id+'&keyword='+keyword)	
	});
});
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
    </script>

    	
          @endsection
