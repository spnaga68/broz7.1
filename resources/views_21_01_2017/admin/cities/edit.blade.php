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
			<li>@lang('messages.Cities')</li>
		</ul>
		
		<h4>@lang('messages.Edit City') - {{$infomodel->getLabel('city_name',getAdminCurrentLang(),$data->id)}}</h4>
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
    {!!Form::open(array('url' => ['admin/updatecity', $data->id], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'city_form','files' => true));!!} 
	<div class="tab-content mb30">
	<div class="tab-pane active" id="home3">		
		<div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.City Name') <span class="asterisk">*</span></label>
                <div class="col-sm-10">
                    <?php $i = 0; foreach($languages as $langid => $language):?>
                    <div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
                          <input type="text" name="city_name[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.City Name').trans('messages.'.'('.$language->name.')');?>" class="form-control" value="{{$infomodel->getLabel('city_name',$language->id,$data->id)}}" maxlength="32" />
                     
                        <div class="input-group-btn">
                            <button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button"><?php echo $language->name;?> <span class="caret"></span></button>
                            <ul class="dropdown-menu pull-right">
                                <?php foreach($languages as $sublangid => $sublanguage):?>
                                    <li><a href="javascript:YL.Language.fieldchange(<?php echo $sublanguage->id;?>)"> <?php echo trans('messages.'.$sublanguage->name);?></a></li>
                                <?php endforeach;?>
                            </ul>
                        </div><!-- input-group-btn -->
                    </div>
                    <?php $i++; endforeach;?>
                </div>
        </div>
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Zone Code') <span class="asterisk">*</span></label>
			<div class="col-sm-10">
			  <input type="text" name="zone_code" value="{{ $data->zone_code }}"   maxlength="3" placeholder="@lang('messages.Zone Code')"  class="form-control"  />
			   <span class="help-block">@lang('messages.An unique numeric code for the city eg:(356)')</span>
			</div>
		</div>
		<div class="form-group ">
			<label class="col-sm-2 control-label">Country <span class="asterisk">*</span></label>
			<div class="col-sm-10">
				<select class="form-control" name="country">
					<option value="">Select Country</option>
					<?php foreach(getCountryLists() as $value) { ?>
						<option value="{!! $value->id !!}"  @if ($data->country_id == $value->id) selected @endif >{!! $value->country_name !!}</option>
					<?php } ?>
				</select>
			</div>
		</div>
		
		
		<div class="form-group">
		  <label  class="col-sm-2 control-label">@lang('messages.Status')</label>
			<div class="col-sm-10">
			<?php $checked = "";
			 if($data->default_status){ $checked = "checked=checked"; }?>
			<input type="checkbox" class="toggle" name="status" data-size="small" <?php echo $checked;?> data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
			</div>
	   </div>
					
       </div>
		<div class="panel-footer">
		<button class="btn btn-primary mr5" title="Save">@lang('messages.Update')</button>
		<button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('admin/localisation/city') }}'">@lang('messages.Cancel')</button>
		</div>
        </div>
      
 {!!Form::close();!!} 
</div></div></div>
<script>
$(window).load(function(){	
	$('form').preventDoubleSubmission();	
});
</script>
@endsection
