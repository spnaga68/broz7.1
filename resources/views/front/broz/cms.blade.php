    @extends('layouts.app')
	@section('content')
<script src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/js/select2.min.js');?>"></script>
	<?php $segment = Request::segment(2);?>
	<?php if($segment=='about-us'){ ?>
       <section class="banner_about">
        <div class="container">
            <div class="captcha">
               
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
