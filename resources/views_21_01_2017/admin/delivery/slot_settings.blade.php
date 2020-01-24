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
                        <li>@lang('messages.Delivery Slot')</li>
                    </ul>
                    <h4>@lang('messages.Delivery Slot')</h4>
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
            <div class="tab-content mb30">
				{!!Form::open(array('url' => ['admin/delivery/update_delivery_slots'], 'method' => 'post','class'=>'panel-wizard','id'=>'update_delivery_timing','files' => true));!!}
					<div class="tab-pane active" id="home3">
					<div class="form-group time_intervel">
						<legend>@lang('messages.Delivery slots')</legend>
					<?php	$week = array("1"=>trans('messages.Sunday'),"2"=>@trans('messages.Monday'),"3"=>@trans('messages.Tuesday'),"4"=>@trans('messages.Wednesday'),"5"=>@trans('messages.Thursday'),"6"=>@trans('messages.Friday'),"7"=>@trans('messages.Saturday'));
					
						for($i = 1; $i < 8; $i++) { ?>
				<div id="value-<?php echo $i;  ?>" class="row mb15">
					<div class="checkbox">
						<?php $checked_day =""; if(isset($time_slots[$i])) { $checked_day = "checked"; }?>
						<div class="col-sm-2 to_time">
							<label><input {{$checked_day}} name="day[{{$i}}]" type="checkbox" value="{{$i}}",required><?php echo $week[$i];?></label>
						</div>
					</div>
						<?php foreach($time_interval as $ke => $time){ ?>
						<?php if($ke!="-1"){  ?>
								<label class="col-sm-2 pt10">   </label>
						<?php } ?>
					
					<div class="row">
						<div class="pt10 pull-left">
							<?php $checked =""; if(isset($time_slots[$i][$time->id])) { $checked = "checked"; }?>
							<label><input {{$checked}}  name="slot[{{$i}}][]" type="checkbox" value="{{$time->id}}"> <?php ;?></label>
						</div>
						<div class="col-sm-2" >
							<div class="input-group mb15">
								<span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
								<input type="text" value="<?php echo isset($time->start_time)?date("g:i a", strtotime($time->start_time)):''; ?>"  class="timepicker form-control" readonly>
							</div>
						</div>
				
					<div class="pt10 pull-left">@lang('messages.To')</div>
						<div class="col-sm-2">
							<div class="input-group mb15">
								  <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
								  <input type="text" value="<?php echo isset($time->end_time)?date("g:i a", strtotime($time->end_time)):''; ?>"  class="timepicker form-control" readonly >
							</div>
						</div> 
					</div>
						<?php }  ?>	
					</div>	
				<?php }  ?>	
			</div>	
	</div>
</div>			
					<div class="form-group Loading_Img" style="display:none;">
				<div class="col-sm-4">
					<i class="fa fa-spinner fa-spin fa-3x"></i><strong style="margin-left: 3px;">@lang('messages.Processing...')</strong>
				</div>
			</div>	
			
	<!-- panel-default -->
                   <div class="panel-footer Submit_button">
							<input type="hidden" name="tab_info" class="tab_info" value="">
							<button type="submit" onclick="HideButton('Submit_button','Loading_Img');" onsubmit="HideButton('Submit_button','Loading_Img');" class="btn btn-primary mr5" title="Save">@lang('messages.Save')</button>
							<button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('admin/dashboard') }}'">@lang('messages.Cancel')</button>
					</div><!-- panel-footer -->
			{!!Form::close();!!} 
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
			
			data = JSON.parse(resp);
			if(data.status == 200)
			{
				setTimeout( function() 
				{
					$('#myModal2').modal('hide');
					location.reload(true);
					$( '.alert-info' ).show().html( "Delivery time added success" );
				}, 1200 );
				return false;
			}
			else
			{
				$( '.alert-error' ).show();
				$( '#errors' ).html(data.errors);
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
