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
                        <li>@lang('messages.Outlet Managers')</li>
                    </ul>
                    <h4>@lang('messages.Add Manager')</h4>
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
            {!!Form::open(array('url' => 'create_manager', 'method' => 'post', 'class' => 'tab-form attribute_form', 'id' => 'create_driver_form', 'files' => true));!!} 
                <div class="tab-content mb30">
                    <div class="tab-pane active" id="home3">
                        <?php /*<div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Social Title') <span class="asterisk">*</span></label>
                            <div class="col-sm-1">
                                <select name="social_title" id="social_title" class="form-control">
                                    <option value="Mr.">@lang('messages.Mr.')</option>
                                    <option value="Mrs.">@lang('messages.Mrs.')</option>
                                    <option value="Ms.">@lang('messages.Ms.')</option>
                                </select>
                            </div>
                        </div>*/ ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.First Name') <span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <input type="text" name="first_name" maxlength="50" placeholder="@lang('messages.First Name')" class="form-control" value="{!! old('first_name') !!}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Last Name') <span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <input type="text" name="last_name" maxlength="50" placeholder="@lang('messages.Last Name')" class="form-control" value="{!! old('last_name') !!}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Email') <span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <input type="text" name="email" maxlength="100" placeholder="@lang('messages.Email')" class="form-control" value="{!! old('email') !!}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Password') <span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <div class="input-group">
                                    <input type="password" class="form-control" maxlength="32" name="user_password" autocomplete="off" value="" placeholder="">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                </div>
                                <span class="help-block">@lang('messages.Password length must be between 5 to 32 characters')</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Mobile')<span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                              <input type="text" name="mobile" maxlength="12" placeholder="@lang('messages.Mobile')" class="form-control" value="{!! old('mobile') !!}" />
                              <span class="help-block">@lang('messages.Add Phone number(s) in comma seperated. <br>For example: 9750550341,9791239324')</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Date of birth')</label>
                            <div class="col-sm-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="date_of_birth" autocomplete="off" value="{!! old('date_of_birth') !!}" placeholder="mm/dd/yyyy" id="datepicker">
                                    <span class="input-group-addon datepicker-trigger"><i class="glyphicon glyphicon-calendar" id="dob"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Gender') <span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <select name="gender" class="form-control">
                                    <option value="" >@lang('messages.Select Gender')</option>
                                    <option value="M" <?php if("M"==old('gender')){ echo "selected=selected"; } ?> >@lang('messages.Male')</option>
                                    <option value="F" <?php if("F"==old('gender')){ echo "selected=selected"; } ?>>@lang('messages.Female')</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group" id="vendor_head">
                            <label class="col-sm-2 control-label">@lang('messages.Vendor Name') <span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <select name="vendor_name" id="vendor_name" class="form-control" onchange="vendor_change()">
									<option value="">@lang("messages.Select Vendor")</option>
                                    <?php if(count($vendors_list) > 0 ) {
                                        foreach($vendors_list as $vendor) { ?>
                                            <option value="{{$vendor->id}}" <?php if(@old(vendor_name) == $vendor->id){ echo "selected";} ?>>{{ ucfirst($vendor->vendor_name) }}</option>
                                    <?php } } else { ?>
                                        <option value="">@lang('messages.No Vendor Found')</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group" id="outlet_head">
                            <label class="col-sm-2 control-label">@lang('messages.Outlet Name') <span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <select name="outlet_name" id="outlet_name" class="form-control">
									 <?php if(old('vendor_name') != ''){ ?>
										@if (count(get_outlet_list(old('vendor_name'))) > 0)
											@foreach (get_outlet_list(old('vendor_name')) as $outlet)
												<option value="{{ $outlet->id }}" <?php if($outlet->id == old('city')){ echo "selected=selected"; } ?> >{{ ucfirst($outlet->outlet_name) }}</option>
											@endforeach
										@endif
									<?php } else { ?>
										<option value="">@lang("messages.Select Vendor First")</option>
									<?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Country')</label>
                            <div class="col-sm-6">
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
                            <div class="col-sm-6">
                                <select name="city" id="city_id" class="select2-offscreen"  style="width:100%;" tabindex="-1" title="">
                                    <option value="">@lang('messages.Select City')</option>
                                    <?php if(old('country') != ''){ ?>
										@if (count(getCityList(old('country'))) > 0)
											@foreach (getCityList(old('country')) as $city)
												<option value="{{ $city->id }}" <?php if($city->id == old('city')){ echo "selected=selected"; } ?> >{{ ucfirst($city->city_name) }}</option>
											@endforeach
										@endif
									<?php } else { ?>
										@if (count(getCityList($settings->default_country)) > 0)
											@foreach (getCityList($settings->default_country) as $city)
												<option value="{{ $city->id }}" <?php if($city->id == old('city')){ echo "selected=selected"; } ?> >{{ ucfirst($city->city_name) }}</option>
											@endforeach
										@endif
									<?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Postal Code') <span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <input type="text" name="postal_code" maxlength="10" placeholder="@lang('messages.Postal Code')" class="form-control" value="{!! old('postal_code') !!}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Address') <span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <input type="text" name="address" maxlength="56" placeholder="@lang('messages.Address')" class="form-control" value="{!! old('address') !!}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Image')</label>
                            <div class="col-sm-6">
                                <input type="file" name="image" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label  class="col-sm-2 control-label">@lang('messages.Status')</label>
                            <div class="col-sm-6">
                                <input type="checkbox" class="toggle" name="active_status" data-size="small" data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
                            </div>
                        </div>
                        <div class="form-group">
                        <label class="col-sm-2 control-label">@lang('messages.Is Verified')</label>
                        <div class="col-sm-6">
                            <input type="checkbox" class="toggle" name="is_verified" data-size="small" data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
                        </div>
                    </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn btn-primary mr5" title="Save">@lang('messages.Save')</button>
                        <button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('vendors/outlet_managers') }}'">@lang('messages.Cancel')</button>
                    </div>
                </div>
            {!!Form::close();!!} 
        </div>
    </div>
</div>
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
        /*$("select[name='social_title']").change(function(){
            if ($(this).val() =='Mrs.' || $(this).val() =='Ms.') {
                $("select[name='gender']").val("F");
            }
            if ($(this).val() =='Mr.') {
                $("select[name='gender']").val("M");
            }
        });*/
    });
    $(document).ready(function(){
        $("#country_id").select2();
        var city_id="<?php echo $settings->default_city;?>";
        $("#city_id").select2();
    });
    $('#country_id').change(function(){
        var cid, token, url, data;
        token = $('input[name=_token]').val();
        cid   = $('#country_id').val();
        url   = '{{url('drivers/CityList')}}';
        data  = {cid: cid};
        $.ajax({
            url: url,
            headers: {'X-CSRF-TOKEN': token},
            data: data,
            type: 'POST',
            datatype: 'JSON',
            success: function (resp) {
                $('#city_id').empty();
                if(resp.data!='')
                { 
                    $('#select2-chosen-2').html('Select City');
                    $.each(resp.data, function(key, value)
                    {
                        //console.log(value['id']+'=='+value['city_name']);
                        $('#city_id').append($("<option></option>").attr("value",value['id']).text(value['city_name'])); 
                    });
                }
                else {
                    $('#select2-chosen-2').html('No Matches Found');
                }
            }
        });
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
                        html +='<option value='+value["id"]+'>'+ value["outlet_name"]+'</option>'; 
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
</script>
@endsection
