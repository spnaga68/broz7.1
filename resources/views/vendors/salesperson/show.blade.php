@extends('layouts.vendors')
@section('content')

<!-- Nav tabs -->
<div class="pageheader">
    <div class="media">
        <div class="pageicon pull-left">
            <i class="fa fa-home"></i>
        </div>
        <div class="media-body">
            <ul class="breadcrumb">
                <li><a href="{{ URL::to('vendors/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Vendors')</a></li>
                <li>@lang('messages.Salesperson')</li>
            </ul>
            <h4>@lang('messages.View Salesperson')  - {{$data->social_title.ucfirst($data->name)}}</h4>
        </div>
    </div><!-- media -->
</div><!-- pageheader -->

<div class="contentpanel">
    <div class="buttons_block pull-right">
        <div class="mr5 mt5 mb5">
            <a class="btn btn-primary tip" href="{{ URL::to('vendors/salesperson_edit/'.$data->id) }}" title="@lang('messages.Edit')" >@lang('messages.Edit')</a>
        </div>
    </div>
    <ul class="nav nav-tabs"></ul>
    <div class="tab-content mb30">
        <div class="tab-pane active" id="home3">
			
			<div class="form-group">
				<label for="index" class="col-sm-2 control-label"> @lang('messages.Name') :</label>
				<div class="col-sm-9">{{strip_tags($data->name)}}</div>
			</div>
			
			<div class="form-group">
				<label for="index" class="col-sm-2 control-label"> @lang('messages.Email') :</label>
				<div class="col-sm-9"><?php echo $data->email; ?></div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label ">@lang('messages.Mobile') :</label>
				<div class="col-sm-9"><?php echo ($data->mobile_number != '')?$data->mobile_number:'-';?></div> 
			</div>
			<div class="form-group">
				<label for="content" class="col-sm-2 control-label"> @lang('messages.Date of birth') :</label>
				<div class="col-sm-9"><?php echo ($data->date_of_birth != '')?$data->date_of_birth:'-';?></div>
			</div>
			<div class="form-group">
				<label for="content" class="col-sm-2 control-label"> @lang('messages.Gender') :</label>
				<div class="col-sm-9"><?php if($data->gender == 'M') { echo 'Male'; } elseif($data->gender == 'F') { echo 'Female'; } else { echo '-'; }?></div>
			</div>
			<div class="form-group">
				<label for="content" class="col-sm-2 control-label"> @lang('messages.Country') :</label>
				<div class="col-sm-9">
					@if($data->country_id != '')
						@if (count(getCountryLists()) > 0)
							@foreach (getCountryLists() as $country)
								@if($country->id == $data->country_id)
									{{ ucfirst($country->country_name) }}
								@endif
							@endforeach
						@endif
					@else
						<?php echo '-';?>
					@endif
				</div>
			</div>
			<div class="form-group">
				<label for="content" class="col-sm-2 control-label"> @lang('messages.City') :</label>
				<div class="col-sm-9">
					@if($data->country_id != '')
						<?php $city_list = getCityList($data->country_id);?>
						@if (count($city_list) > 0)
							@foreach ($city_list as $city)
								@if($city->id == $data->city_id)
									{{ ucfirst($city->city_name) }}
								@endif
							@endforeach
						@endif
					@else
						<?php echo '-';?>
					@endif
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">@lang('messages.Image') :</label>
				<div class="col-sm-9">
					<?php if(file_exists(base_path().'/public/assets/admin/base/images/drivers/thumb/'.$data->profile_image) && $data->profile_image != '') { ?>
						<img src="<?php echo url('/assets/admin/base/images/drivers/thumb/'.$data->profile_image); ?>" class="img-circle">
					<?php } else{  ?>
						<img src=" {{ URL::asset('assets/admin/base/images/default_avatar_male.jpg') }} " class="img-circle">
					<?php } ?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">@lang('messages.Status') :</label>
				<div class="col-sm-9">@if($data->active_status == 1) @lang('messages.Active') @elseif($data->active_status == 0) @lang('messages.Inactive') @else @lang('messages.Delete') @endif</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">@lang('messages.Is Verified') :</label>
				<div class="col-sm-9">@if($data->is_verified == 1) @lang('messages.Enable') @elseif($data->is_verified == 0) @lang('messages.Disable') @else - @endif</div>
			</div>
		</div>
	</div>
</div>
@endsection
