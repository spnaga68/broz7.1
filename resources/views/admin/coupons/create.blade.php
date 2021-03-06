@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script> 
<link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/tinymce4.1/tinymce.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/moment-with-locales.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/bootstrap-datetimepicker.js') }}"></script>
<link href="{{ URL::asset('assets/admin/base/css/bootstrap-datetimepicker.css') }}" media="all" rel="stylesheet" type="text/css" /> 
<link href="{{ URL::asset('assets/admin/base/css/bootstrap-datetimepicker.css') }}" media="all" rel="stylesheet" type="text/css" /> 

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
                        <li>@lang('messages.Coupons')</li>
                    </ul>
                    <h4>@lang('messages.Add Coupon')</h4>
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
            {!!Form::open(array('url' => 'create_coupon', 'method' => 'post', 'class' => 'tab-form attribute_form', 'id' => 'create_coupon_form', 'files' => true));!!} 
                <div class="tab-content mb30">
                    <div class="tab-pane active" id="home3">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">@lang('messages.Coupon Title') <span class="asterisk">*</span></label>
                            <div class="col-sm-9">
                                <?php $i = 0; foreach($languages as $langid => $language):?>
                                    <div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
                                        <input type="text" name="coupon_title[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Coupon Title').trans('messages.'.'('.$language->name.')');?>" class="form-control" value="{!! Input::old('coupon_title.'.$language->id) !!}" maxlength="100" />
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
                            <label class="col-sm-3 control-label">@lang('messages.Coupon Description') <span class="asterisk">*</span></label>
                            <div class="col-sm-9">
                                <?php $i = 0; foreach($languages as $langid => $language):?>
                                    <div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
                                        <textarea name="coupon_description[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Coupon Description').trans('messages.'.'('.$language->name.')');?>" class="form-control description" rows="5">{!! Input::old('coupon_description.'.$language->id) !!}</textarea>
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
                            <label class="col-sm-3 control-label">@lang('messages.Coupon Code') <span class="asterisk">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="coupon_code" maxlength="8" required placeholder="@lang('messages.Coupon Code')" class="form-control" value="{!! old('coupon_code') !!}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">@lang('messages.Coupon Type') <span class="asterisk">*</span></label>
                            <div class="col-sm-9">
                                <select name="coupon_type" id="coupon_type" class="form-control">
                                    <option value="">@lang("messages.Select Coupon Type")</option>
                                    <option value="1" <?php if(old('coupon_type') == 1) { echo "selected"; } ?>>@lang("messages.All")</option>
                                    <option value="2" <?php if(old('coupon_type') == 2) { echo "selected"; }?>>@lang("messages.Outlet")</option>
                                 
                                </select>
                            </div>
                        </div>
                        <div class="form-group" id="vendor_head" style="display:none;">
                            <label class="col-sm-3 control-label">@lang('messages.Vendor Name') <span class="asterisk">*</span></label>
                            <div class="col-sm-9">
                                <select name="vendor_name" id="vendor_name" class="form-control" onchange="vendor_change()">
                                    <?php if(count($vendors_list) > 0 ) {
                                        foreach($vendors_list as $vendor) { ?>
                                            <option value="{{$vendor->id}}" <?php if(@old(vendor_name) == $vendor->id){ echo "selected";} ?>>{{ ucfirst($vendor->vendor_name) }}</option>
                                    <?php } } else { ?>
                                        <option value="">@lang('messages.No Vendor Found')</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" id="outlet_head" style="display:none;">
                            <label class="col-sm-3 control-label">@lang('messages.Outlet Name') <span class="asterisk">*</span></label>
                            <div class="col-sm-9">
                                <select name="outlet_name[]" id="outlet_name" class="form-control" multiple onchange="product_list()">
                                    <option value="">@lang("messages.Select Vendor First")</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" id="product_head" style="display:none;">
                            <label class="col-sm-3 control-label">@lang('messages.Product Name') <span class="asterisk">*</span></label>
                            <div class="col-sm-9">
                                <select name="product_name[]" id="product_name" class="form-control" multiple>
                                    <option value="">@lang("messages.Select Outlet First")</option>
                                </select>
                            </div>
                        </div>
                      
                        <div class="form-group">
                            <label class="col-sm-3 control-label">@lang('messages.Offer Type') <span class="asterisk">*</span></label>
                            <div class="col-sm-9">
                                <select name="offer_type" id="offer_type" class="form-control">
                                    <option value="">@lang("messages.Select Offer Type")</option>
                                    <option value="1" <?php if(old('offer_type') == 1) { echo "selected"; }?>>@lang("messages.Amount")</option>
                                    <option value="2" <?php if(old('offer_type') == 2) { echo "selected"; }?>>@lang("messages.Percentage")</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" id="offer_amount" style="display:none;">
                            <label class="col-sm-3 control-label">@lang('messages.Offer Amount') <span class="asterisk">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="offer_amount" maxlength="6" placeholder="@lang('messages.Offer Amount')" class="form-control" value="{!! old('offer_amount') !!}" />
                            </div>
                        </div>
                        <div class="form-group" id="offer_percentage" style="display:none;">
                            <label class="col-sm-3 control-label">@lang('messages.Offer Percentage') <span class="asterisk">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="offer_percentage" maxlength="6" placeholder="@lang('messages.Offer Percentage')" class="form-control" value="{!! old('offer_percentage') !!}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">@lang('messages.Category Name') <span class="asterisk">*</span></label>
                            <div class="col-sm-9">
                                <select name="category_name" class="form-control">
                                    <?php// print_r($category_list);exit(); ?>
                                    @if(count($category_list) > 0 )
                                        <option value="">@lang("messages.Select Category Name")</option>
                                        @foreach($category_list as $category)
                                            <option value="{{$category->id}}" <?php if($category->id == old('category_name')) { echo "selected"; } ?>>{{$category->category_name}}</option>
                                        @endforeach
                                    @else
                                        <option value="">@lang("messages.No Category Found")</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                         <!--  <div class="form-group">
                            <label class="col-sm-3 control-label">@lang('messages.User Type') <span class="asterisk">*</span></label>
                            <div class="col-sm-9">
                                <select name="user_type" id="user_type" class="form-control">
                                    <option value="">@lang("messages.Select User Type")</option>
                                    <option value="1" <?php //if(old('user_type') == 1) { echo "selected"; } ?>>@lang("messages.All")</option>
                                    <option value="2" <?php// if(old('user_type') == 2) { echo "selected"; }?>>@lang("messages.Individual")</option>
                                 
                                </select>
                            </div>
                            <input type="hidden" name="user_type" class="user_type" id="user_type" value="1">
                        </div> -->
                       <!--  <div class="form-group" id="user_head" style="display:none;">
                            <label class="col-sm-3 control-label">@lang('messages.Outlet Name') <span class="asterisk">*</span></label>
                            <div class="col-sm-9">
                                <select name="user_name[]" id="user_name" class="form-control" multiple >
                                    <option value="">@lang("messages.Select Vendor First")</option>
                                </select>
                            </div>
                        </div>
                         -->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">@lang('messages.Start Date') <span class="asterisk">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="start_date" autocomplete="off" value="{!! old('start_date') !!}" placeholder="yyyy-mm-dd" id="start_date" >
                                <div class="calender_common">
                                    <span class="input-group-addon datepicker-trigger"><i class="glyphicon glyphicon-calendar" id="start_date"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">@lang('messages.End Date') <span class="asterisk">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="end_date" autocomplete="off" value="{!! old('end_date') !!}" placeholder="yyyy-mm-dd" id="end_date" >
                                <div class="calender_common">
                                    <span class="input-group-addon datepicker-trigger"><i class="glyphicon glyphicon-calendar" id="end_date"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">@lang('messages.Coupon Limit') <span class="asterisk">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="coupon_limit" maxlength="6" required placeholder="@lang('messages.Coupon Limit')" class="form-control" value="{!! old('coupon_limit') !!}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">@lang('messages.User Limit') <span class="asterisk">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="user_limit" maxlength="6" required placeholder="@lang('messages.User Limit')" class="form-control" value="{!! old('user_limit') !!}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">@lang('messages.Minimum Order Amount') <span class="asterisk">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="minimum_order_amount" maxlength="10" placeholder="@lang('messages.Minimum Order Amount')" class="form-control" value="{!! old('minimum_order_amount') !!}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">@lang('messages.Image') <span class="asterisk">*</span></label>
                            <div class="col-sm-9">
                                <input type="file" name="coupon_image" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">@lang('messages.Terms & Conditions') <span class="asterisk">*</span></label>
                            <div class="col-sm-9">
                                <?php $i = 0; foreach($languages as $langid => $language):?>
                                    <div class="input-group translatable_field language-<?php echo $language->id;?>" <?php if($i > 0):?>style="display: none;"<?php endif;?>>
                                        <textarea name="terms_condition[<?php echo $language->id;?>]" id="suffix_<?php echo $language->id;?>"  placeholder="<?php echo trans('messages.Terms & Conditions').trans('messages.'.'('.$language->name.')');?>" class="form-control description" rows="5">{!! Input::old('terms_condition.'.$language->id) !!}</textarea>
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
                            <label  class="col-sm-3 control-label">@lang('messages.Status')</label>
                            <div class="col-sm-9">
                                <?php $checked = ""; ?>
                                <input type="checkbox" class="toggle" name="active_status" data-size="small" <?php echo $checked;?> data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn btn-primary mr5" title="Save">@lang('messages.Save')</button>
                        <button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('admin/coupons') }}'">@lang('messages.Cancel')</button>
                    </div>
                </div>
            {!!Form::close();!!} 
        </div>
    </div>
</div>
<script type="text/javascript">
    $(window).load(function(){
        user_list();
          $('#start_date').datetimepicker({
           // minDate: moment(),
           // format: 'YYYY-MM-DD',
        });
        $('#end_date').datetimepicker({
           // minDate: moment(),
           // format: 'YYYY-MM-DD',
        });
        $("#start_date").on("dp.change", function (e) {
            $('#end_date').data("DateTimePicker").minDate(e.date);
        });
        $("#end_date").on("dp.change", function (e) {
            $('#start_date').data("DateTimePicker").maxDate(e.date);
        });
        $(".datepicker-trigger").on("click", function() {
            $("#start_date").datetimepicker("show");
            $("#end_date").datetimepicker("show");
        });
        tinymce.init({
            menubar : false,statusbar : true,plugins: [
                "advlist autolink lists link image charmap print preview hr anchor pagebreak code",
                "emoticons template paste textcolor colorpicker textpattern"
            ],
            toolbar1: "code | insertfile undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image preview | forecolor backcolor | fontsizeselect",
            height:'450px',
            selector: "textarea.description"
        });
    });
    $('#coupon_type').change(function(){
        var coupon_type = $('#coupon_type').val();
        if(coupon_type == 2)
        {
            $('#vendor_head').show();
            $('#outlet_head').show();
            $('#product_head').hide();
            vendor_change();
        }
        else if(coupon_type == 3)
        {
            $('#vendor_head').show();
            $('#outlet_head').show();
            $('#product_head').show();
            vendor_change();
        }
        else {
            $('#vendor_head').hide();
            $('#outlet_head').hide();
            $('#product_head').hide();
        }
    });
    function vendor_change()
    {
        var coupon_type = $('#coupon_type').val();
        var vendor_name = $('#vendor_name').val();
        if (coupon_type == 3 )
        {
            $('#product_name').val('');
        }
        var token, url, data;
        token = $('input[name=_token]').val();
        url = '{{url('c_list/coupon_outlet_list')}}';
        var html = '';
        $.ajax({
            url: url,
            headers: {'X-CSRF-TOKEN': token},
            type: 'POST',
            data: {vendor_name : vendor_name},
            datatype: 'JSON',
            success: function(res) { 
                if(res.data!='')
                { 
                    $.each(res.data, function(key, value) {
                        html +='<option value='+value["id"]+'>'+ value["outlet_name"].charAt(0).toUpperCase() + value["outlet_name"].substr(1)+'</option>'; 
                    });
                    if (coupon_type == 3 )
                    {
                        product_list();
                    }
                    $("#outlet_name").html(html);
                }
                else {
                    $('#outlet_name').html('<option value="">No Matches Found</option>');
                }
            }
        })
    }
    function product_list()
    {
        var outlet_name = $('#outlet_name').val(); 
        var token, url, data;
        token = $('input[name=_token]').val();
        url = '{{url('c_list/coupon_product_list')}}';
        var html = '';
        $.ajax({
            url: url,
            headers: {'X-CSRF-TOKEN': token},
            type: 'POST',
            data: {outlet_name : outlet_name},
            datatype: 'JSON',
            success: function(res) { 
                if(res.data!='')
                { 
                    $.each(res.data, function(key, value) {
                        html +='<option value='+value["id"]+'>'+ value["product_name"]+'</option>'; 
                    });
                    $("#product_name").html(html);
                }
                else {
                    $('#product_name').html('<option value="">No Matches Found</option>');
                }
            }
        })
    }

     $('#user_type').change(function(){
        var user_type = $('#user_type').val();
        //alert(user_type);return false;

        if(user_type == 2)
        {
            $('#user_head').show();
        }
        else {
            $('#user_head').hide();
        }
    });

    function user_list()
    {
        var token, url, data;
        token = $('input[name=_token]').val();
        url = '{{url('c_list/coupon_user_list')}}';
        var html = '';
        $.ajax({
            url: url,
            headers: {'X-CSRF-TOKEN': token},
            type: 'POST',
            data: '',
            datatype: 'JSON',
            success: function(res) { 
                if(res.data!='')
                { 
                    $.each(res.data, function(key, value) {
                        html +='<option value='+value["id"]+'>'+ value["name"]+'</option>'; 
                    });
                    $("#user_name").html(html);
                }
                else {
                    $('#user_name').html('<option value="">No Matches Found</option>');
                }
            }
        })
    }
    $('#offer_type').change(function(){
        var offer_type = $('#offer_type').val();
        if(offer_type == 1)
        {
            $('#offer_amount').show();
            $('#offer_percentage').hide();
        }
        else if(offer_type == 2)
        {
            $('#offer_percentage').show();
            $('#offer_amount').hide();
        }
        else {
            $('#offer_amount').hide();
            $('#offer_percentage').hide();
        }
    });
    $("#coupon_type").change(); 
    $("#offer_type").change(); 
</script>
@endsection
