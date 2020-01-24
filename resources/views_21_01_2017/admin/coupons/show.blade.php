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
                <li>@lang('messages.Coupon')</li>
            </ul>
            <h4>@lang('messages.View Coupon')  - {{$infomodel->getLabel('coupon_title',getAdminCurrentLang(),$data->id)}}</h4>
        </div>
    </div><!-- media -->
</div><!-- pageheader -->

<div class="contentpanel">
    <div class="buttons_block pull-right">
        <div class="mr5 mt5 mb5">
            <a class="btn btn-primary tip" href="{{ URL::to('admin/coupons/edit/'.$data->id) }}" title="@lang('messages.Edit')" >@lang('messages.Edit')</a>
        </div>
    </div>
    <ul class="nav nav-tabs"></ul>
    <div class="tab-content mb30">
        <div class="tab-pane active" id="home3">
            <!-- Task Name -->
            <div class="form-group">
                <label for="title" class="col-sm-3 control-label"> @lang('messages.Coupon Title') :</label>
                <div class="col-sm-9">{{$infomodel->getLabel('coupon_title',getAdminCurrentLang(),$data->id)}}</div>
            </div>
            <div class="form-group">
                <label for="index" class="col-sm-3 control-label"> @lang('messages.Coupon Description') :</label>
                <div class="col-sm-9">{{strip_tags($infomodel->getLabel('coupon_info',getAdminCurrentLang(),$data->id))}}</div>
            </div>
            <div class="form-group">
                <label for="index" class="col-sm-3 control-label"> @lang('messages.Coupon Code') :</label>
                <div class="col-sm-9"><?php echo $data->coupon_code; ?></div>
            </div>
            <div class="form-group">
                <label for="index" class="col-sm-3 control-label"> @lang('messages.Coupon Type') :</label>
                <div class="col-sm-9">
                    @if($data->coupon_type == 1)
                        @lang('messages.All')
                    @elseif($data->coupon_type == 2)
                        @lang('messages.Outlet')
                    @elseif($data->coupon_type == 3)
                        @lang('messages.Product')
                    @endif
                </div>
            </div>
            @if($data->coupon_type == 2 || $data->coupon_type == 3)
                <div class="form-group">
                    <label for="index" class="col-sm-3 control-label"> @lang('messages.Vendor Name') :</label>
                    <div class="col-sm-9">
                        @if(count($vendors_list) > 0 )
                            @foreach($vendors_list as $vendor)
                                @if($vendor->id == $data->vendor)
                                    {{ucfirst($vendor->vendor_name)}}
                                @endif
                            @endforeach
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div class="form-group">
                    <label for="index" class="col-sm-3 control-label"> @lang('messages.Outlet Name') :</label>
                    <div class="col-sm-9">
                        <?php $outlet_list = get_outlet_list($data->vendor);$ot_name = ''; ?>
                        @if(count($selected_outlet_list) > 0 )
                            @foreach($outlet_list as $ot)
                                <?php $sel = '';?>
                                @foreach($selected_outlet_list as $outlet)
                                    @if($ot->id == $outlet->outlet_id)
                                        <?php $ot_name .= ucfirst($ot->outlet_name).', ';?>
                                    @endif
                                @endforeach
                            @endforeach
                        @else
                            <?php $ot_name = '-'; ?>
                        @endif
                        {{rtrim($ot_name,', ')}}
                    </div>
                </div>
            @endif
            @if($data->coupon_type == 3)
                <div class="form-group">
                    <label for="index" class="col-sm-3 control-label"> @lang('messages.Product Name') :</label>
                    <div class="col-sm-9">
                        <?php $product_list = get_product_list(explode(',',$data->outlets));$prod_name = ''; ?>
                        @if(count($product_list) > 0 )
                            @foreach($product_list as $prod)
                                @if(in_array($prod->id,explode(',',$data->products)))
                                    <?php $prod_name .= ucfirst($prod->product_name).', ';?>
                                @endif
                            @endforeach
                            {{rtrim($prod_name,', ')}}
                        @else
                            -
                        @endif
                    </div>
                </div>
            @endif
            <div class="form-group">
                <label for="index" class="col-sm-3 control-label"> @lang('messages.Offer Type') :</label>
                <div class="col-sm-9">
                    @if($data->offer_type == 1)
                        @lang('messages.Amount')
                    @elseif($data->offer_type == 2)
                        @lang('messages.Percentage')
                    @endif
                </div>
            </div>
            @if($data->offer_type == 1)
                <div class="form-group">
                    <label for="index" class="col-sm-3 control-label"> @lang('messages.Offer Amount') :</label>
                    <div class="col-sm-9"><?php echo $data->offer_amount; ?></div>
                </div>
            @elseif($data->offer_type == 2)
                <div class="form-group">
                    <label for="index" class="col-sm-3 control-label"> @lang('messages.Offer Percentage') :</label>
                    <div class="col-sm-9"><?php echo $data->offer_percentage; ?></div>
                </div>
            @endif
            <div class="form-group">
                <label class="col-sm-3 control-label ">@lang('messages.Category Name') :</label>
                <div class="col-sm-9">
                    @if (count($category_list) > 0)
                        @foreach ($category_list as $val)
                            @if ($val->id == $data->category_id)
                                {{ucfirst($val->category_name)}}
                            @endif
                        @endforeach
                    @endif
                </div> 
            </div>
            <div class="form-group">
                <label for="content" class="col-sm-3 control-label"> @lang('messages.Start Date') :</label>
                <div class="col-sm-9"><?php echo $data->start_date; ?></div>
            </div>
            <div class="form-group">
                <label for="content" class="col-sm-3 control-label"> @lang('messages.End Date') :</label>
                <div class="col-sm-9"><?php echo $data->end_date; ?></div>
            </div>
            <div class="form-group">
                <label for="content" class="col-sm-3 control-label"> @lang('messages.Coupon Limit') :</label>
                <div class="col-sm-9"><?php echo $data->coupon_limit; ?></div>
            </div>
            <div class="form-group">
                <label for="content" class="col-sm-3 control-label"> @lang('messages.User Limit') :</label>
                <div class="col-sm-9"><?php echo $data->user_limit; ?></div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">@lang('messages.Image') :</label>
                <div class="col-sm-9">
                    <?php if($data->coupon_image){ ?>
                        <img src="<?php echo url('/assets/admin/base/images/coupon/'.$data->coupon_image); ?>" title="{{$infomodel->getLabel('coupon_title',getAdminCurrentLang(),$data->id)}}" alt="{{$infomodel->getLabel('coupon_title',getAdminCurrentLang(),$data->id)}}" >
                    <?php } ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">@lang('messages.Terms & Conditions') :</label>
                <div class="col-sm-9">{{strip_tags($infomodel->getLabel('terms_condition',getAdminCurrentLang(),$data->id))}}</div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">@lang('messages.Status') :</label>
                <div class="col-sm-9">@if($data->active_status == 1) @lang('messages.Active') @elseif($data->active_status == 0) @lang('messages.Inactive') @else @lang('messages.Delete') @endif</div>
            </div>
        </div>
    </div>
</div>
@endsection
