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
			<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
			<li>@lang('messages.Blog')</li>
		</ul>
		<h4>@lang('messages.View Blog')  - {{$infomodel->getLabel('title',getAdminCurrentLang(),$data->id)}}</h4>
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
		<div class="buttons_block pull-right">
	
<div class="btn-group mr5">
<a class="btn btn-primary tip" href="{{ URL::to('admin/blog/edit/' . $data->id . '') }}" title="Edit" >@lang('messages.Edit')</a>
</div>
</div>
                       <!-- Task Name -->
            <div class="form-group">
                <label for="title" class="col-sm-2 control-label"> @lang('messages.Title') :</label>

                <div class="col-sm-9">
						{{$infomodel->getLabel('title',getAdminCurrentLang(),$data->id)}}
                </div>
            </div>
            
            <div class="form-group">
                <label for="index" class="col-sm-2 control-label"> @lang('messages.Url index') :</label>

                <div class="col-sm-10">
                         <?php echo $data->url_index; ?>
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
                <label for="content" class="col-sm-2 control-label"> @lang('messages.Short Notes') :</label>
                <div class="col-sm-10">
               
                {{$infomodel->getLabel('short_notes',getAdminCurrentLang(),$data->id)}}
                </div>
            </div>
            
			<div class="form-group">
                <label for="content" class="col-sm-2 control-label"> @lang('messages.Content') :</label>
                <div class="col-sm-10">
                <?php echo $infomodel->getLabel('content',getAdminCurrentLang(),$data->id); ?>
                </div>
            </div>
            <div class="form-group">
                <label for="content" class="col-sm-2 control-label"> @lang('messages.Created Date') :</label>
                <div class="col-sm-10">
                <?php echo $data->created_at; ?>
                </div>
            </div>
            <div class="form-group">
                <label for="content" class="col-sm-2 control-label"> @lang('messages.Updated Date') :</label>
                <div class="col-sm-10">
                <?php echo $data->updated_at; ?>
                </div>
            </div>
		<?php if($data->image){ ?>
          <div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Image') :</label>
                <div class="col-sm-10">
                 		<div class="row media-manager" style="margin-top:10px;">
							<div class="col-xs-6 col-sm-4 col-md-3 image">
							  <div class="thmb">                 
									<div class="thmb-prev" style="height: auto; overflow: hidden;">
									  <a href="<?php echo url('/assets/admin/base/images/blog/list/'.$data->image.''); ?>" target="_blank">
										<img src="<?php echo url('/assets/admin/base/images/blog/list/'.$data->image.''); ?>" class="img-responsive" alt="{{$infomodel->getLabel('title',getAdminCurrentLang(),$data->id)}}">
									  </a>
									</div>                      
							  </div>
							</div>
						</div>
				</div>	
         </div>
		<?php } ?>
        </div>
        </div>
		
</div>
@endsection


