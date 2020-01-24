
@extends('layouts.admin')
@section('content')

<!-- Nav tabs -->
<div class="pageheader">
    <div class="media">
        <div class="pageicon pull-left">
            <i class="fa fa-home"></i>
        </div>
        <div class="media-body">
            <ul class="breadcrumb">
                <li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
                <li>@lang('messages.Customer Promotion')</li>
            </ul>
            <h4>@lang('messages.View Customer Promotion')  - {{$data->promotion_name}}</h4>
        </div>
    </div><!-- media -->
</div><!-- pageheader -->

<div class="contentpanel">
    <div class="buttons_block pull-right">
        <div class="mr5 mt5 mb5">
            <a class="btn btn-primary tip" href="{{ URL::to('admin/customer_promotion/edit/'.$data->id) }}" title="@lang('messages.Edit')" >@lang('messages.Edit')</a>
        </div>
    </div>
    <ul class="nav nav-tabs"></ul>
    <div class="tab-content mb30">
        <div class="tab-pane active" id="home3">
			
			<div class="form-group">
				<label for="index" class="col-sm-2 control-label"> @lang('messages.Promotion Name') :</label>
				<div class="col-sm-9">{{strip_tags($data->promotion_name)}}</div>
			</div>
			<div class="form-group">
				<label for="index" class="col-sm-2 control-label"> @lang('messages.Base Amount') :</label>
				<div class="col-sm-9"><?php echo $data->base_amount; ?></div>
			</div>
			<div class="form-group">
				<label for="index" class="col-sm-2 control-label"> @lang('messages.Addition Promotion'):</label>
				<div class="col-sm-9"><?php echo $data->addition_promotion; ?></div>
			</div>
			<div class="form-group">
				<label for="index" class="col-sm-2 control-label"> @lang('messages.Grocery Wallet') :</label>
				<div class="col-sm-9"><?php echo $data->grocery_wallet; ?></div>
			</div>
			<div class="form-group">
                <label for="content" class="col-sm-2 control-label"> @lang('messages.Start Date') :</label>
                <div class="col-sm-9"><?php echo $data->start_date; ?></div>
            </div>
            <div class="form-group">
                <label for="content" class="col-sm-2 control-label"> @lang('messages.End Date') :</label>
                <div class="col-sm-9"><?php echo $data->end_date; ?></div>
            </div>
			<div class="form-group">
				<label class="col-sm-2 control-label">@lang('messages.Image') :</label>
				<div class="col-sm-9">
				   <?php if(file_exists(base_path().'/public/assets/admin/base/images/customerPromotion/'.$data->image) && $data->image != '') { ?>
					<img src="<?php echo url('/assets/admin/base/images/customerPromotion/'.$data->image); ?>" class="img-circle">
					<?php } //else{  ?>
					<!-- <img src=" {{ URL::asset('assets/admin/base/images/default_avatar_male.jpg') }} " class="img-circle"> -->
					<?php// } ?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">@lang('messages.Status') :</label>
				<div class="col-sm-9">@if($data->active_status == 1) @lang('messages.Active') @elseif($data->active_status == 0) @lang('messages.Inactive') @else @lang('messages.Delete') @endif</div>
			</div>
			<!-- <div class="form-group">
				<label class="col-sm-2 control-label">@lang('messages.Is Verified') :</label>
				<div class="col-sm-9">@if($data->is_verified == 1) @lang('messages.Enable') @elseif($data->is_verified == 0) @lang('messages.Disable') @else - @endif</div>
			</div> -->
		</div>
	</div>
</div>
@endsection
