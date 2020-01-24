@extends('layouts.admin')
@section('content')
<link href="{{ URL::asset('assets/admin/base/css/bootstrap-timepicker.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/admin/base/css/select2.css') }}" media="all" rel="stylesheet" type="text/css" />
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
                        <li>@lang('messages.Delivery tmes')</li>
                    </ul>
                    <h4>@lang('messages.Delivery tmes')</h4>
                </div>
            </div><!-- media -->
        </div><!-- pageheader -->
        <div class="contentpanel">
		<div class="col-md-12">
		<div class="row panel panel-default">
		<div class="grid simple">
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
			@if (Session::has('message'))
				<div class="admin_sucess_common">
					<div class="admin_sucess">
						<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
					</div>
				</div>
			@endif
			<div class="admin_sucess_common" id="error_time" style="display:none;">
				<div class="admin_sucess">
					<div class="alert alert-info" id="errors"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
				</div>
			</div>
		</div>
	
     <div class="tab-content mb30">
		{!!Form::open(array('url' => ['update_delivery_timing'], 'method' => 'post','class'=>'panel-wizard','id'=>'update_delivery_timing','files' => true,'onsubmit'=>'return update_delivery_timing()'));!!}
				<div class="tab-pane active" id="home3">
					<div class="form-group time_intervel">
						<legend>@lang('messages.Delivery Hours')</legend>
							<?php $i =0;  foreach($time_interval as $time){ ?>
							<div class="row mb15 time_slot"><div class="col-sm-1 to_time"></div>
								<div class="pt10 pull-left">@lang('messages.From')</div>
								<div class="col-sm-2 to_time">
									<div class="input-group mb15">
										<span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
										<div class="bootstrap-timepicker">
											<input type="text" value="<?php  echo (isset($time->start_time))?date("g:i a", strtotime($time->start_time)):''; ?>" name="from_time[]" class="timepicker form-control">
										</div>
									</div>
								</div>
								<div class="pt10 pull-left">@lang('messages.To')</div>
								<div class="col-sm-2 to_time">
									<div class="input-group mb15">
										<span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
										<div class="bootstrap-timepicker"> 
											<input type="text"  value="<?php echo isset($time->end_time)?date("g:i a", strtotime($time->end_time)):''; ?>" name="to_time[]" class="timepicker form-control">
										</div>
									</div>
								</div> 
								<?php if($i == 0) { ?>
									
									<div class="pt10 pull-left add_more"><a class="btn btn-primary tip" href="javascript:;" title="@lang('messages.Add More')"><i class="fa fa-plus"> </i></a></div>
								<?php } else { ?>
									<div class="pt10 pull-left delete" ><a class="btn btn-primary tip" href="javascript:;" title="@lang('messages.Delete')"><i class="fa fa-minus"> </i></a></div>
								<?php } ?>
								<div class="pt10 pull-left delete" style="display:none"><a class="btn btn-primary tip" href="javascript:;"title="@lang('messages.Delete')"><i class="fa fa-minus"> </i></a></div>
							</div>
							<?php $i++; } ?>
						</div>
					</div>	
				</div>		
						<div class="panel-footer">
							<input type="hidden" name="tab_info" class="tab_info" value="">
							<button type="submit" class="btn btn-primary mr5 save" title="Save">@lang('messages.Save')</button>
							<button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('admin/dashboard') }}'">@lang('messages.Cancel')</button>
						</div>
					</div>
				{!!Form::close();!!} 
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/jquery-ui-1.10.3.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/bootstrap-timepicker.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>
<script type="text/javascript">

$(window).load(function(){	
	$('form').preventDoubleSubmission();	
});
$( document ).ready(function() {
	$(".close").on('click', function()
	{
		$(".alert-error").remove();
	});
	$('.timepicker').timepicker({defaultTIme: false});
	//$('#signup_link').on('click', function() {
	$(".add_more").on('click', function() 
	{
		$('.timepicker').timepicker('remove');
		newElem = $(this).parent().clone(true,true).appendTo(".time_intervel");
		newElem.find("input[type='text']").val("");
		newElem.find(".add_more").hide();
		newElem.find(".delete").show();
		
		/*newElem.find('input').each(function() {
			 $(this).attr("name", $(this).attr("name").replace(/\d+/, "") );
		}); */
		
		$('.timepicker').timepicker();
	});
	$(".delete").on('click', function() 
	{
		$(this).parent().remove();
	});
});

function update_delivery_timing()
{
	
	$("#ajaxloading").show();
	data = $("#update_delivery_timing").serializeArray();
	var c_url = '/admin/delivery/update-interval';
	token = $('input[name=_token]').val();
	$.ajax({
		url: c_url,
		headers: {'X-CSRF-TOKEN': token},
		data: data,
		type: 'POST',
		datatype: 'JSON',
		success: function (resp) {
			
			data = JSON.parse(resp);console.log(data);
			if(data.status == 200)
			{ 
				setTimeout( function() 
				{
					$('#myModal2').modal('hide');
					location.reload(true);
					
				}, 1200 );
				return false;
			}
			else
			{ 
				$( '.alert-error' ).show();
				$('#error_time').show();
				alert(data.errors);
			}
		}, 
		error:function(resp)
		{
			
			//alert("Something went wrong");
			console.log('out--'+data); 
			return false;
		}
	});
	return false;
}



</script>
@endsection
