@extends('layouts.admin')
@section('content')
<div class="row">	
	<div class="col-md-12 ">
<!-- Nav tabs -->
<div class="pageheader">
<div class="media">
	<div class="pageicon pull-left">
		<i class="fa fa-home"></i>
	</div>
	<div class="media-body">
		<ul class="breadcrumb">
			<li><a href="#"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
			<li>@lang('messages.Portfolio')</li>
		</ul>
		<h4>@lang('messages.View Portfolio')  - <?php echo ucfirst($data->title); ?></h4>
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
<div class="buttons_block pull-right">
<div class="btn-group mr5">
<a class="btn btn-primary tip" href="{{ URL::to('admin/portfolio/edit/' . $data->id . '') }}" title="Edit" >@lang('messages.Edit')</a>
</div>
</div>
<ul class="nav nav-tabs"></ul>




	<div class="tab-content mb30">
	<div class="tab-pane active" id="home3">
	
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Title') :</label>
			<div class="col-sm-10">
			  <?php echo $data->title; ?>
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Short Notes') :</label>
			<div class="col-sm-10">
			  <?php echo $data->short_notes; ?>
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Url index') :</label>
			<div class="col-sm-10">
			<?php echo $data->portfolio_index; ?>
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Customer') :</label>
			<div class="col-sm-10">
			  <?php echo $data->customer; ?>
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Technology') :</label>
			<div class="col-sm-10">
			  <?php echo $data->technology; ?>
			</div>
		</div>
		

		 <?php $categories = explode(',',$data->category_ids);  ?>
		<div class="form-group">
			<label class="col-sm-2 control-label ">@lang('messages.Categories') :</label>
			<div class="col-sm-10">
				@foreach ($category as $val)
				@if (in_array($val->id,$categories))
					{{  ucfirst($val->category_name.',') }}
				@endif
				@endforeach
			</div> 
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Short description') :</label>
			<div class="col-sm-10">
			 <?php echo $data->short_description; ?>
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Long description') :</label>
			<div class="col-sm-10">
			<?php echo $data->long_description; ?>
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Web link') :</label>
			<div class="col-sm-10">
			  <?php echo $data->web_link; ?>
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Iphone link') :</label>
			<div class="col-sm-10">
			  <?php echo $data->iphone_link; ?>
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Android link') :</label>
			<div class="col-sm-10">
			  <?php echo $data->android_link; ?>
			</div>
		</div>
		
		<div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Image') :</label>
                <div class="col-sm-10">
                 <?php if($data->image){ ?>
                 <img src="<?php echo url('/assets/admin/base/images/portfolio/thumb/'.$data->image.''); ?>" title="Nextbrain" alt="Nextbrain">
                 <?php } ?>
				</div>	
         </div>

         <div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Thumb Image') :</label>
                <div class="col-sm-10">
                 <?php if($data->image){ ?>
                 <img width="50" height="50" src="<?php echo url('/assets/admin/base/images/portfolio/thumbimage/'.$data->thumb_image.''); ?>" title="Nextbrain" alt="Nextbrain">
                 <?php } ?>
				</div>	
         </div>
         
        </div>
        </div>
		
</div>
@endsection
