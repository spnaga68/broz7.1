@extends('layouts.vendors')
@section('content')
	<script src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/plugins/rateit/src/jquery.rateit.js') ;?>"></script>
	<link href="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/plugins/rateit/src/rateit.css');?>" rel="stylesheet">	
	<div class="col-md-12 ">
<!-- Nav tabs -->
<div class="pageheader">
<div class="media">
	<div class="pageicon pull-left">
		<i class="fa fa-home"></i>
	</div>
	<div class="media-body">
		<ul class="breadcrumb">
			<li><a href="#"><i class="glyphicon glyphicon-home"></i>@lang('messages.Vendors')</a></li>
			<li>@lang('messages.Reviews')</li>
		</ul>
		<h4>@lang('messages.View review')  - {{ $review->title }}</h4>
	</div>
</div><!-- media -->
</div><!-- pageheader -->

<div class="contentpanel">
		@if (count($errors) > 0)
		<div class="alert alert-danger">
				<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
		@endif
<ul class="nav nav-tabs"></ul>
	<div class="tab-content mb30">
	<div class="tab-pane active" id="home3">
                       <!-- Task Name -->
            <?php /*<div class="form-group">
                <label for="title" class="col-sm-3 control-label"> @lang('messages.Review title') :</label>

                <div class="col-sm-3">
						{{ $review->title }}
                </div>
            </div>*/ ?>
            
            <div class="form-group">
                <label for="index" class="col-sm-3 control-label"> @lang('messages.Comments') :</label>

                <div class="col-sm-6">
                         <?php echo $review->comments; ?>
                </div>
            </div>

           <div class="form-group">
                <label for="index" class="col-sm-3 control-label"> @lang('messages.Rating') :</label>
                <div class="col-sm-6">
                       <div class="rateit" data-rateit-value="<?php echo $review->ratings; ?>" data-rateit-ispreset="true" data-rateit-readonly="true">  </div> 
                </div>
            </div>

            <div class="form-group">
                <label for="index" class="col-sm-3 control-label"> @lang('messages.Posted by') :</label>
                <div class="col-sm-6">
                         <?php echo $review->user_name; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="index" class="col-sm-3 control-label"> @lang('messages.Posted to') :</label>
                <div class="col-sm-6">
                         <?php echo $review->outlet_name; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="index" class="col-sm-3 control-label"> @lang('messages.Posted date') :</label>
                <div class="col-sm-6">
                         <?php echo date('d - M - Y h:i A' , strtotime($review->review_posted_date)); ?>
                         
                </div>
            </div>

            <div class="form-group">
                <label for="index" class="col-sm-3 control-label"> @lang('messages.Status') :</label>
                <div class="col-sm-6">
				<?php if($review->approval_status==0):
					$data = '<span  class="label label-danger">'.trans("messages.Pending").'</span>';
                elseif($review->approval_status==1):
					$data = '<span  class="label label-success">'.trans("messages.Approved").'</span>';
                endif;  echo $data; ?>
                         
                </div>
            </div>
            

        </div>
        </div>
		
</div>
@endsection


