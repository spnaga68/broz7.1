@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script> 
<link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/tinymce4.1/tinymce.min.js') }}"></script>
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
			<li>@lang('messages.Notification Template')</li>
		</ul>
		<h4>@lang('messages.Edit Template') - <?php echo ucfirst($data->ref_name); ?></h4>
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
 <div class="row">
    <div class="col-md-12">
		<div class=" panel panel-default">
        <ul class="nav nav-justified nav-wizard nav-pills">
            <li class="active"><a href="#user_info" data-toggle="tab"><strong>@lang('messages.From Information')</strong></a></li>
            <li><a href="#user_social_info" data-toggle="tab"><strong>@lang('messages.HTML Content')</strong></a></li> 
        </ul>
        {!!Form::open(array('url' => ['admin/template/update', $data->template_id], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'banner_form','files' => true));!!} 
        <div class="tab-content  tab-content-simple mb30 no-padding" >
            <div class="tab-pane active" id="user_info">
                <div class="form-group">
                    <label class="col-sm-2 control-label">@lang('messages.Reference Name') <span class="asterisk">*</span></label>
                    <div class="col-sm-6"> 
                            <input type="text" class="form-control" autocomplete="off" name="ref_name" value="{!! $data->ref_name !!}" id="reference_name">
                            <span class="help-block">@lang('messages.A unique name for this template. This for internal use.')</span> 
                    </div>
                </div><!-- form-group -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">@lang('messages.From Name')</label>
                    <div class="col-sm-6"> 
                            <input type="text" class="form-control" autocomplete="off" name="from_name" value="{!! $data->from !!}" id="from_name">
                            <span class="help-block">@lang('messages.The name this emails comes from (Eg: Bill smith, Rocky)')</span> 
                    </div>
                </div><!-- form-group -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">@lang('messages.From Email') <span class="asterisk">*</span></label>
                    <div class="col-sm-6"> 
                            <input type="text" class="form-control" autocomplete="off" name="from_email" value="{!! $data->from_email !!}" id="from_email">
                            <span class="help-block">@lang('messages.The email address this comes from')</span>  
                    </div>
                </div><!-- form-group -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">@lang('messages.Reply to')</label>
                    <div class="col-sm-6"> 
                            <input type="text" class="form-control" autocomplete="off" name="reply_to" value="{!! $data->reply_to !!}" id="reply_to">
                            <span class="help-block">@lang('messages.The address most directly replies comes to. usually the same as the from email')</span>  
                    </div>
                </div><!-- form-group -->
                 <div class="form-group">
                    <label class="col-sm-2 control-label">@lang('messages.Subject') <span class="asterisk">*</span></label>
                    <div class="col-sm-6"> 
                            <textarea type="text" class="form-control" rows="7"  name="subject" id="subject">{!! $data->subject !!}</textarea>
                            <span class="help-block">@lang('messages.Subject line for this email')</span>  
                    </div>
                </div><!-- form-group -->
            </div>
            <div class="tab-pane" id="user_social_info">                
                 <div class="form-group"> 
                    <div class="col-sm-12"> 
                    		<label class="control-label">@lang('messages.Web Template') <span class="asterisk">*</span></label>
                            <textarea type="text" class="" rows="7"  name="content" id="content"><?php echo $data->content; ?></textarea>  
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <button class="btn btn-primary mr5" type="submit">@lang('messages.Update')</button>
				<button type="button"  onclick="window.location='{{ url('admin/templates/email') }}'" class="btn btn-default">@lang('messages.Cancel')</button>
            </div>
        </div>
         {!!Form::close();!!} 
    </div>
    </div>
</div>
</div></div></div>
<script type="text/javascript">
    $(window).load(function(){
        tinymce.init({
            menubar : false,statusbar : true,plugins: [
                "advlist autolink lists link image charmap print preview hr anchor pagebreak code",
                "emoticons template paste textcolor colorpicker textpattern"
            ],
            toolbar1: "code | insertfile undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image preview | forecolor backcolor | fontsizeselect",
            height:'450px',
            selector: "textarea#content"
         });         
    });
</script>
@endsection

