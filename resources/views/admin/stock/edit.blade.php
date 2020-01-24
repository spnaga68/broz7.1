@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script> 
<link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" />
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
			<li>@lang('messages.Stock Statuses')</li>
		</ul>
		<h4>@lang('messages.Edit Stock Statuses') - <?php echo ucfirst($data->name); ?></h4>
	</div>
</div><!-- media -->
</div><!-- pageheader -->

<div class="contentpanel">
		@if (count($errors) > 0)
		<div class="alert alert-danger">
				<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>
			<ul>
				@foreach ($errors->all() as $error)
					<li><?php echo trans('messages.'.$error); ?> </li>
				@endforeach
			</ul>
		</div>
		@endif
<ul class="nav nav-tabs"></ul>

       {!!Form::open(array('url' => ['update_stock_status', $data->id], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'stock_form','files' => true));!!} 
	<div class="tab-content mb30">
	<div class="tab-pane active" id="home3">
		
		<div class="form-group">
					<label class="col-sm-2 control-label">@lang('messages.Stock Status Name') <span class="asterisk">*</span></label>
					<div class="col-sm-10">
						<input type="text" autofocus name="status_name" id="" maxlength="15" placeholder="@lang('messages.Stock Status Name')" class="form-control" value="{!! $data->name !!}" />
					</div>
				</div>
		  	
       </div>
		<div class="panel-footer">
		<button class="btn btn-primary mr5" title="Update">@lang('messages.Update')</button>
		<button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('admin/localisation/stockstatuses') }}'">@lang('messages.Cancel')</button>
		</div>
        </div>
      
 {!!Form::close();!!} 
</div></div></div>
@endsection

