@extends('layouts.admin')
@section('content')
 <script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/jquery-ui-1.10.3.min.js') }}"></script>
 <script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/bootstrap-timepicker.min.js') }}"></script>
 <link href="{{ URL::asset('assets/admin/base/css/bootstrap-timepicker.min.css') }}" media="all" rel="stylesheet" type="text/css" /> 
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
			<li>@lang('messages.Profile')</li>
		</ul>
		<h4>@lang('messages.Edit Profile') - <?php echo ucfirst($data->email); ?></h4>
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
		@if (Session::has('message'))
    <div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
@endif
<ul class="nav nav-tabs"></ul>

       {!!Form::open(array('url' => ['admin/updateprofile', $data->id], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'profile_edit_form','files' => true));!!} 
	<div class="tab-content mb30">
	<div class="tab-pane active" id="home3">
		
		<?php /*<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Social Title')</label>
			<div class="col-sm-1">
				<select name="social_title" class="form-control">
					<option value="Mr." <?php echo $data->social_title == 'Mr.'? "selected":"";?>>@lang('messages.Mr.')</option>
					<option value="Mrs." <?php echo $data->social_title == 'Mrs.'? "selected":"";?>>@lang('messages.Mrs.')</option>
					<option value="Ms." <?php echo $data->social_title == 'Ms.'? "selected":"";?>>@lang('messages.Ms.')</option>
				</select>
			</div>
        </div><!-- form-group --> */ ?>
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Name')<span class="asterisk">*</span></label>
			<div class="col-sm-10">
			  <input type="text" name="name"  maxlength="50" placeholder="@lang('messages.Name')"  class="form-control"  value="<?php echo $data->name; ?>" />
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Designation')<span class="asterisk">*</span></label>
			<div class="col-sm-10">
			  <input type="text" name="designation"  maxlength="50" placeholder="@lang('messages.Designation')"  class="form-control"  value="<?php echo $data->designation; ?>" />
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Email')</label>
			<div class="col-sm-10">
			  <input type="text" name="title" readonly   maxlength="100" placeholder="@lang('messages.Email')"  class="form-control"  value="<?php echo $data->email; ?>" />
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Mobile')</label>
			<div class="col-sm-4">
			  <input type="text" name="mobile"  maxlength="12" placeholder="@lang('messages.Mobile')"  class="form-control" value="<?php echo $data->mobile; ?>" />
			  <span class="help-block">@lang('messages.Add Phone number(s) in comma seperated. <br>For example: 9750550341,9791239324')</span>
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Date of birth')</label>
			<div class="col-sm-3">
				<div class="input-group">
					<input type="text" class="form-control" name="date_of_birth" autocomplete="off" value="<?php echo date("m/d/Y",strtotime($data->date_of_birth));?>" placeholder="mm/dd/yyyy" id="datepicker" readonly>
					<span class="input-group-addon datepicker-trigger"><i class="glyphicon glyphicon-calendar" id="dob"></i></span>
				</div><!-- input-group -->
			</div>
		</div><!-- form-group -->
		
		 <div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Gender')</label>
			<div class="col-sm-2">
				<select name="gender" class="form-control">
					<option value="" >@lang('messages.Select Gender')</option>
					<option value="M" <?php echo $data->gender == 'M'? "selected":"";?>>@lang('messages.Male')</option>
					<option value="F" <?php echo $data->gender == 'F'? "selected":"";?>>@lang('messages.Female')</option>
				</select>
			</div>
		</div><!-- form-group -->
		
		<div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Image')</label>
                <div class="col-sm-10">
                  <input type="file" name="image"  maxlength="255" placeholder="@lang('messages.Image')"  class="" value="" />
				
			</div>	
         </div>
		<?php if($data->image){ ?>
		<div class="form-group">
			<label class="col-sm-2 control-label"></label>
			<div class="col-sm-10">
				<a class="pull-left profile-thumb">	
				<?php  if(file_exists(base_path().'/public/assets/admin/base/images/admin/profile/thumb/'.$data->image)) { ?>
                            <img src="<?php echo url('/assets/admin/base/images/admin/profile/thumb/'.$data->image.'?'.time()); ?>" class="img-circle">
                        <?php } else{  ?>
                        <img src=" {{ URL::asset('assets/admin/base/images/a2x.jpg') }} " class="img-circle">
                <?php } ?>
                            
                </a>
			 </div>	  
		  </div>
		  <?php } ?>
		  	
       </div>
		<div class="panel-footer">
		<button class="btn btn-primary mr5" title="@lang('messages.Update')">@lang('messages.Update')</button>
		<button type="reset" title="@lang('messages.Cancel')" class="btn btn-default" onclick="window.location='{{ url('admin/dashboard') }}'">@lang('messages.Cancel')</button>
		</div>
        </div>
      
<?php /** {!!Form::close();!!} **/ ?>
</div></div></div>
<script> 
    $(window).load(function(){
        $('#datepicker').datepicker({								
            yearRange: '<?php echo date("Y") - 100; ?>:<?php echo date("Y"); ?>',
            maxDate: new Date(),
            changeMonth: true,
            changeYear: true
        });
        
        $(".datepicker-trigger").on("click", function() {
			$("#datepicker").datepicker("show");
		});
               
        $("select[name='social_title']").change(function(){
            if ($(this).val() =='Mrs.' || $(this).val() =='Ms.') {
                $("select[name='gender']").val("F");
            }
            if ($(this).val() =='Mr.') {
                $("select[name='gender']").val("M");
            }
        });
    });
</script>
@endsection
