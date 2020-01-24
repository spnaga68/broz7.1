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
                        <li>@lang('messages.Driver')</li>
                    </ul>
                    <h4>@lang('messages.View Driver')  - {{$data->social_title.ucfirst($data->first_name).' '.$data->last_name}}</h4>
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
            <div class="buttons_block pull-right">
                <div class="btn-group mr5">
                    <a class="btn btn-primary tip" href="{{ URL::to('admin/drivers/edit/'.$data->id) }}" title="Edit" >@lang('messages.Edit')</a>
                </div>
            </div>
            <ul class="nav nav-tabs"></ul>
            <div class="tab-content mb30">
                <div class="tab-pane active" id="home3">
                    <!-- Task Name -->
                    <div class="form-group">
                        <label for="title" class="col-sm-2 control-label"> @lang('messages.Social Title') :</label>
                        <div class="col-sm-9">{{$data->social_title}}</div>
                    </div>
                    <div class="form-group">
                        <label for="index" class="col-sm-2 control-label"> @lang('messages.First Name') :</label>
                        <div class="col-sm-9">{{strip_tags($data->first_name)}}</div>
                    </div>
                    <div class="form-group">
                        <label for="index" class="col-sm-2 control-label"> @lang('messages.Last Name') :</label>
                        <div class="col-sm-9"><?php echo $data->last_name; ?></div>
                    </div>
                    <div class="form-group">
                        <label for="index" class="col-sm-2 control-label"> @lang('messages.Email') :</label>
                        <div class="col-sm-9"><?php echo $data->email; ?></div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label ">@lang('messages.Mobile') :</label>
                        <div class="col-sm-9">{{$data->mobile_number}}</div> 
                    </div>
                    <div class="form-group">
                        <label for="content" class="col-sm-2 control-label"> @lang('messages.Date of birth') :</label>
                        <div class="col-sm-9"><?php echo $data->date_of_birth; ?></div>
                    </div>
                    <div class="form-group">
                        <label for="content" class="col-sm-2 control-label"> @lang('messages.Gender') :</label>
                        <div class="col-sm-9"><?php if($data->gender == 'M') { echo 'Male'; } elseif($data->gender == 'F') { echo 'Female'; }?></div>
                    </div>
                    <div class="form-group">
                        <label for="content" class="col-sm-2 control-label"> @lang('messages.Country') :</label>
                        <div class="col-sm-9">
                            @if (count(getCountryLists()) > 0)
                                @foreach (getCountryLists() as $country)
                                    @if($country->id == $data->country_id)
                                        {{ ucfirst($country->country_name) }}
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="content" class="col-sm-2 control-label"> @lang('messages.City') :</label>
                        <div class="col-sm-9">
                            <?php $city_list = getCityList($data->country_id);?>
                            @if (count($city_list) > 0)
                                @foreach ($city_list as $city)
                                    @if($city->id == $data->city_id)
                                        {{ ucfirst($city->city_name) }}
                                    @endif
                                @endforeach
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
    </div>
</div>
@endsection
