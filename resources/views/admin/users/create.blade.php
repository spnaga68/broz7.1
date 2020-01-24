@extends('layouts.admin')
@section('content')
 <script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/jquery-ui-1.10.3.min.js') }}"></script>
 <script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script> 
 <script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/bootstrap-timepicker.min.js') }}"></script>
 <link href="{{ URL::asset('assets/admin/base/css/bootstrap-timepicker.min.css') }}" media="all" rel="stylesheet" type="text/css" /> 
 <link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" />
  <script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>
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
			<li>@lang('messages.Users')</li>
		</ul>
		<h4>@lang('messages.Add User')</h4>
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
<?php /**<div class="alert alert-info">
	<h4>@lang('messages.Info:')</h4>
	<p>
	-
	<strong>@lang('messages.Red')</strong>
	@lang('messages.highlighted label will be mandatory.')
	</p>
</div>**/?>
<ul class="nav nav-tabs"></ul>

       {!!Form::open(array('url' => 'createuser', 'method' => 'post','class'=>'tab-form attribute_form','id'=>'uaer_add_form','files' => true));!!} 
	<div class="tab-content mb30">
	<div class="tab-pane active" id="home3">
		
		<?php /*<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Social Title')</label>
			<div class="col-sm-1">
				<select name="social_title" class="form-control">
					<option value="Mr.">@lang('messages.Mr.')</option>
					<option value="Mrs.">@lang('messages.Mrs.')</option>
					<option value="Ms.">@lang('messages.Ms.')</option>
				</select>
			</div>
        </div>*/ ?><!-- form-group -->
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Name') <span class="asterisk">*</span></label>
			<div class="col-sm-6">
			  <input type="text" name="name"  maxlength="32" placeholder="@lang('messages.Name')"  class="form-control"  value="{!! old('name') !!}" />
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Email') <span class="asterisk">*</span></label>
			<div class="col-sm-6">
			  <input type="text" name="email"    maxlength="100" placeholder="@lang('messages.Email')"  class="form-control"  value="{!! old('email') !!}" />
			</div>
		</div>
		
		<div class="form-group">
                   <label class="col-sm-2 control-label">@lang('messages.Password') <span class="asterisk">*</span></label>
                   <div class="col-sm-4">
                       <div class="input-group">
                           <input type="password" class="form-control" name="user_password" autocomplete="off" value="" placeholder=""  maxlength="32">
                           <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                       </div><!-- input-group -->
                       <span class="help-block">@lang('messages.Password length must be between 5 to 32 characters')</span>
                   </div>
        </div><!-- form-group -->
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Mobile')</label>
			<div class="col-sm-4">
			  <input type="text" name="mobile"  maxlength="12" placeholder="@lang('messages.Mobile')"  class="form-control" value="{!! old('mobile') !!}" />
			  <span class="help-block">@lang('messages.Add Phone number(s) in comma seperated. <br>For example: 9750550341,9791239324')</span>
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Date of birth')</label>
			<div class="col-sm-3">
				<div class="input-group">
					<input type="text" class="form-control" name="date_of_birth" autocomplete="off" value="{!! old('date_of_birth') !!}" placeholder="mm/dd/yyyy" id="datepicker">
					<span class="input-group-addon datepicker-trigger"><i class="glyphicon glyphicon-calendar" id="dob"></i></span>
				</div><!-- input-group -->
			</div>
		</div><!-- form-group -->
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Gender') <span class="asterisk">*</span></label>
			<div class="col-sm-2">
				<select name="gender" class="form-control">
					<option value="" >@lang('messages.Select Gender')</option>
					<option value="M" <?php if("M"==old('gender')){ echo "selected=selected"; } ?> >@lang('messages.Male')</option>
					<option value="F" <?php if("F"==old('gender')){ echo "selected=selected"; } ?>>@lang('messages.Female')</option>
				</select>
			</div>
		</div><!-- form-group -->
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Group') <span class="asterisk">*</span></label>
			<div class="col-sm-2">
				<select name="group" class="form-control">
					<option value="" >@lang('messages.Select Group')</option>
						@if (count($groups) > 0)
							@foreach ($groups as $value)
								<option value="{{ $value->group_id }}" <?php if($value->group_id==old('group')){ echo "selected=selected"; } ?> >{{ ucfirst($value->group_name) }}</option>
							@endforeach
						@endif
				</select>
			</div>
		</div><!-- form-group -->
		
	
	    <div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.User Type') <span class="asterisk">*</span></label>
			<div class="col-sm-2">
				<select name="user_type" class="form-control" id="user_type">
					<option value="" >@lang('messages.Select Type')</option>
						@if (count(getUserTypes()) > 0)
							@foreach (getUserTypes() as $key => $value)
								<option value="{{ $key }}" <?php if($key==old('user_type')){ echo "selected=selected"; } ?> >{{ ucfirst($value) }}</option>
						@endforeach
						@endif
				</select>
			</div>
		</div><!-- form-group -->
		
		<div class="form-group">
				<label class="col-sm-2 control-label">@lang('messages.Country')</label>
				<div class="col-sm-4">
						<select id="country_id" class="select2-offscreen"  style="width:100%;" name="country" tabindex="-1" title="">
						<option value="">@lang('messages.Select Country')</option>
						@if (count(getCountryLists()) > 0)
							@foreach (getCountryLists() as $country)
								<option value="{{ $country->id }}" <?php if($country->id==old('country')){ echo "selected=selected"; } ?> >{{ ucfirst($country->country_name) }}</option>
							@endforeach
						@endif
					</select>
					</div>
		 </div>
		   
		<div class="form-group">
				<label class="col-sm-2 control-label">@lang('messages.City')</label>
				<div class="col-sm-4">
					<select name="city" id="city_id" class="select2-offscreen"  style="width:100%;" tabindex="-1" title="">
						<option value="">@lang('messages.Select City')</option>
						@if (count(getCityList($settings->default_country)) > 0)
							@foreach (getCityList($settings->default_country) as $city)
								<option value="{{ $city->id }}" <?php if($city->id==old('city')){ echo "selected=selected"; } ?> >{{ ucfirst($city->city_name) }}</option>
							@endforeach
						@endif
					</select>
					</div>
		 </div>
		
		<div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Image')</label>
                <div class="col-sm-10">
                  <input type="file" name="image"  maxlength="255" placeholder="@lang('messages.Image')"  class="" value="" />
				
		</div>	
         </div>		  	
         		<div class="form-group">
                    <label class="col-sm-2 control-label">@lang('messages.Status')</label>
                    <div class="col-sm-10">
                        <?php $checked = "";?>
                        <input type="checkbox" class="toggle" name="status" data-size="small" <?php echo $checked;?> data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
                    </div>
        </div>
        
		<div class="form-group" id="is_verified_div" style="<?php if(old('user_type') == 2) { echo 'display:none'; }?>">
			<label class="col-sm-2 control-label">@lang('messages.Is Verified')</label>
			<div class="col-sm-10">
				<?php $checked = "";?>
				<input type="checkbox" class="toggle" name="is_verified" data-size="small" <?php echo $checked;?> data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
			</div>
		</div>
       </div>
       
		<div class="panel-footer">
		<button class="btn btn-primary mr5" title="@lang('messages.Save')">@lang('messages.Save')</button>
		<button type="reset" title="@lang('messages.Cancel')" class="btn btn-default" onclick="window.location='{{ url('admin/users/index') }}'">@lang('messages.Cancel')</button>
		</div>
        </div>
      {!!Form::close();!!}
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
	<script>
	$(document).ready(function(){  $("#country_id").select2(); });
	$(document).ready(function(){ var city_id="<?php echo $settings->default_city;?>"; $("#city_id").select2();});
	$('#user_type').change(function(){
		user_type = $('#user_type').val();
		if(user_type == 2)
		{
			$('#is_verified_div').hide();
		}
		else {
			$('#is_verified_div').show();
		}
	});
	$('#country_id').change(function(){
	var cid, token, url, data;
	token = $('input[name=_token]').val();
	cid = $('#country_id').val();
	url = '{{url('list/CityList')}}';
	data = {cid: cid};
	$.ajax({
		url: url,
		headers: {'X-CSRF-TOKEN': token},
		data: data,
		type: 'POST',
		datatype: 'JSON',
		success: function (resp) {
			$('#city_id').empty();
			if(resp.data!=''){ 
				$('#select2-chosen-2').html('Select City');
				$.each(resp.data, function(key, value) {
					//console.log(value['id']+'=='+value['city_name']);
					$('#city_id').append($("<option></option>").attr("value",value['id']).text(value['city_name'])); 
			   });
		   }else{
				$('#select2-chosen-2').html('No Matches Found');
			}
		}
	});
});
	</script>
@endsection
