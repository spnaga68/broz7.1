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
			<li>@lang('messages.Cms')</li>
		</ul>
		<h4>@lang('messages.View Cms')  - {{$infomodel->getLabel('title',getAdminCurrentLang(),$data->id)}}</h4>
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
		<div class="buttons_block pull-right">
<div class="btn-group mr5">
<a class="btn btn-primary tip" href="{{ URL::to('admin/cms/edit/' . $data->id . '') }}" title="Edit" >@lang('messages.Edit')</a>
</div>
</div>
	<div class="tab-pane active" id="home3">
	
                       <!-- Task Name -->
            <div class="form-group">
                <label for="title" class="col-sm-3 control-label">@lang('messages.Title') : </label>

                <div class="col-sm-3">
						{{$infomodel->getLabel('title',getAdminCurrentLang(),$data->id)}}
                </div>
            </div>
            
            <div class="form-group">
                <label for="index" class="col-sm-3 control-label">@lang('messages.Url index') : </label>

                <div class="col-sm-6">
                         <?php echo $data->url_index; ?>
                </div>
            </div>
            
			<div class="form-group">
                <label for="content" class="col-sm-3 control-label">@lang('messages.Content') : </label>
                <div class="col-sm-6">
               <?php echo $infomodel->getLabel('content',getAdminCurrentLang(),$data->id); ?>
                </div>
            </div>

            <div class="form-group">
                <label for="content" class="col-sm-3 control-label">@lang('messages.Meta keywords') : </label>
                <div class="col-sm-6">
               {{$infomodel->getLabel('meta_keywords',getAdminCurrentLang(),$data->id)}}
                </div>
            </div>

            <div class="form-group">
                <label for="content" class="col-sm-3 control-label">@lang('messages.Meta Description') : </label>
                <div class="col-sm-6">
              {{$infomodel->getLabel('meta_description',getAdminCurrentLang(),$data->id)}}
                </div>
            </div>

           <div class="form-group">
                <label for="content" class="col-sm-3 control-label">@lang('messages.Created Date') : </label>
                <div class="col-sm-6">
                <?php echo $data->created_at; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="content" class="col-sm-3 control-label">@lang('messages.Upated Date') : </label>
                <div class="col-sm-6">
                <?php echo $data->updated_at; ?>
                </div>
            </div>


        </div>
        </div>
		
</div>
@endsection


