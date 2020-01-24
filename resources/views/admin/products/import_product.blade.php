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
					<li>@lang('messages.Categories')</li>
				</ul>
			
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
		    {!!Form::open(array('url' => ['importdriver'], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'category_form','files' => true));!!} 
			<div class="tab-content mb30">
				<div class="tab-pane active" id="home3">
					 <div class="new_input_field import_box" id="import">
                        <input type="file" accept=".xls,.xlsx" name="import_file" id="import_file" class="required">
                        <input type="hidden" name="import_user_type" id="import_user_type" value="driver">
                    </div>                
                    <span class="error file_missing"></span>

			    </div>

			    <!--  <div class="import_notes">
			        <h2>@lang('messages.Import Instructs')</h2>
			        <ol type="decimal">
			            <li>@lang('messages.download')<a class="" href="<?php echo URL::to("admin/orders/edit/"); ?>" title="Download sample file">@lang('messages.download_sample_file')</a></li>
			        </ol>
			    </div> -->
				<div class="panel-footer">
					<button class="btn btn-primary mr5" title="Import" id="driver_import">@lang('messages.Import')</button>
					<button type="reset" class="btn btn-default" onclick="window.location='{{ url('admin/category') }}'" title="Cancel">@lang('messages.Cancel')</button>
				</div>
		   	</div>
			{!!Form::close();!!} 
		</div>
	</div>
</div>
<script type="text/javascript">
$( document ).ready(function() {
			var postURL = "/driver_import_validation";

	  $('#driver_import').click(function(e) {
                    e.preventDefault();
                    var file = $('#import_file').val();
                    if (file == '') {
                        $('.file_missing').text("file_missing");
                        return false;
                    }
                    alert("ddd");return false;
                    $('.file_missing').text('');
                    $('.form-submit-sec').hide();
                    $('#progress_indicator').show();
                    $('#progressMessage').html("");
                    $('#progressbar').html("");
                    $('#progressbar').html(PLEASE_WAIT);
                    /************************form ********************/
                    $.ajax({
                            enctype: 'multipart/form-data',
                            url: URL_BASE+'manage/ajax_driver_import_validate',
                            type: 'POST',
                            data: new FormData(this),
                            async: false,
                            contentType: false,
                            processData: false,
                            success: function(json) {

                                var j = JSON.parse(json);

                                if (j.http_code == 200) {
                                    //$('#progressMessage').append('<p>'+j.Message+'</p>');
                                    //$('#progressbar').append(user_import_start);
                                        driver_import_submit(0, j.total_row, j.file_name);
                                    } else {
                                        $('.form-submit-sec').show();
                                        $('.Loading_Img').hide();
                                        $('#progressbar').attr("style", 'width: 100%');
                                        $('#progressbar').html(COMPLETED);
                                        $('#progressMessage').append('<p class="error-msg">' + j.Message + '</p>');
                                        hide_message();
                                    }
                                }
                            });
                        return false;
                    });
});
   

</script>
@endsection
