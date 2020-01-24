      @extends('layouts.app')
      @section('content')
<?Php $image_url = url('/assets/admin/base/images/blog/914_649/'.$blog->image.''); ?>
	<input type="hidden" class="getiamge" value="<?php echo $image_url; ?>">
    <section class="banner_sections_inner blog_det">
<div class="container">
<div class="inner_banner_info">
<h3>{{ ucfirst($blog->short_notes) }}</h3>

</div>
</div>
    </section>
	
    <section class="blog_det_admin">
<div class="container">
<div class="row">
<div class="col-md-6 padding0">
<div class="col-md-3">
<div class="admin_photo">
<?php  if(file_exists(base_path().'/public/assets/admin/base/images/admin/profile/thumb/'.$users->image)) { ?>
	<img src="<?php echo url('/assets/admin/base/images/admin/profile/thumb/'.$users->image.''); ?>" >
	
		<?php } else{  ?>
		<img src=" {{ URL::asset('assets/admin/base/images/a2x.jpg') }} " >
<?php } ?>
</div>
</div>
<div class="col-md-9">
<div class="admin_inf">
<h3>{{ $users->name }}</h3>
<h4>{{ $users->designation }}</h4>
</div>
</div>
</div>
<div class="col-md-6">
<div class="blog_share">
<div class="footer_social">
<ul>
	<?php $url=url('blog/info/'.$blog->url_index.''); $image=url('/assets/admin/base/images/blog/list/'.$blog->image.''); $description=$blog->short_notes; $url1=url('blog/info/'.$blog->url_index.'&title='.$blog->title.'&summary='.$blog->short_notes.'source='.$url);  ?>
<li><p>@lang('messages.Share at')</p></li>
									                                        <li>
                                            <a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}" title="Facebook" class="face1"><i class="glyph-icon flaticon-facebook-logo">
                                            </i></a>
                                        </li>
                                        <li>
                                            <a target="_blank" href="http://www.linkedin.com/shareArticle?mini=true&url=<?php echo $url1; ?>" title="Linked In" class="linked1">
                                            <i class="glyph-icon flaticon-linkedin-logo"></i></a>
                                        </li>
										      <li>
                                            <a target="_blank" href="https://twitter.com/intent/tweet?url={{ urlencode($url) }}" title="Twitter" class="twitt1">
                                            <i class="glyph-icon flaticon-twitter-logo-silhouette"></i></a>
                                        </li>
                                        <li>
                                            <a target="_blank" href="https://plus.google.com/share?url={{ urlencode($url) }}" class="inst1" title="Instagram"><i class="glyph-icon flaticon-instagram-social-network-logo-of-photo-camera"></i></a>
                                        </li>
                                    </ul>
</div>
</div>
</div>
</div>
</div>
    </section>
    <section class="error_sections">
<div class="container">
<div class="blog_det_infor">
<p>{{ ucfirst($blog->title) }}</p>
<?php echo $blog->content; ?>
</div>

@if (count($related_blog) > 0 )
	<div class="related_blogs">
	<div class="row">
	<div class="title_product">
			<div class="col-md-12">
		<h2>@lang('messages.Related blogs')</h2>
		</div>
		</div> 	
	@foreach($related_blog as $key => $related_value)	
		<div class="col-md-4">
		<div class="rel_list">
		<div class="blog_list_oimg">
		
		<?php  if(file_exists(base_path().'/public/assets/admin/base/images/blog/'.$related_value->image)) { ?>
			<a title="{{ ucfirst($related_value->title) }}" href="{{ URL::to('/blog/info/' . $related_value->url_index . '') }}">
			<img width="360" height="221" alt="{{ ucfirst($related_value->title) }}"  src="<?php echo url('/assets/admin/base/images/blog/'.$related_value->image.''); ?>">
			</a>
		<?php } else{  ?>
				<img width="360" height="221" src=" {{ URL::asset('assets/admin/base/images/blog.png') }} " alt="{{ ucfirst($related_value->title) }}">
		<?php } ?>
		
		</div>
		<p>{{ str_limit($related_value->short_notes , 160) }}</p>
		</div>
		</div>
	@endforeach

	</div>
	</div>
@endif
</div>
    </section>
<script>
$('select').select2();
$( document ).ready(function() {
	var inputC = $('input.getiamge').val();
	$('.blog_detials_bg').css('background', 'url(' + inputC + ')no-repeat');
	//$('.banner_sections_inner').css('background', 'url(' + inputC + ')no-repeat');
});
</script>
      @endsection
