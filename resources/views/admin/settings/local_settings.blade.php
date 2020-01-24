@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>
<div class="pageheader">
    <div class="media">
        <div class="pageicon pull-left">
            <i class="fa fa-home"></i>
        </div>
        <div class="media-body">
            <ul class="breadcrumb">
                <li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>{{ trans('messages.Admin') }}</a></li>
                <li>@lang('messages.Settings')</li>
            </ul>
            <h4>@lang('messages.Local')</h4>
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
    @if (Session::has('message'))
        <div class="admin_sucess_common">
            <div class="admin_sucess">
                <div class="alert alert-info success"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
            </div>
        </div>
    @endif
    <?php $settings_id=0;if(isset($settings->id)&& $settings->id!=''){ $settings_id=$settings->id; } ?>
    {!!Form::open(array('url' => ['admin/settings/updatelocal', $settings_id], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'local_edit_form','files' => true));!!}
        <div class="col-md-12">
            <div class="row panel panel-default">
                <div class="grid simple">
                    <div id="general" class="panel-heading">
                        <h4 class="panel-title">@lang('messages.Local')</h4>
                        <p>@lang('messages.Local settings')</p>
                        <div class="tools">
                            <a class="collapse" href="javascript:;"></a>
                        </div>
                    </div>
                    <ul class="nav nav-tabs"></ul>
                    <div class="panel-body">
                        <div class="form-group">
                            <label class="form-label">@lang('messages.Country') <span class="asterisk">*</span></label>
                            <select id="country_id" class="select2-offscreen" required style="width:100%;" name="default_country" tabindex="-1" title="">
                                <option value="">@lang('messages.Select Country')</option>
                                @if (count(getCountryLists()) > 0)
                                    @foreach (getCountryLists() as $country)
                                        <option value="{{ $country->id }}" <?php if($country->id==$settings->default_country){ echo "selected=selected"; } ?> >{{ ucfirst($country->country_name) }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">@lang('messages.City') <span class="asterisk">*</span></label>
                            <select name="default_city" id="city_id" class="select2-offscreen" required style="width:100%;" tabindex="-1" title="">
                                <option value="">@lang('messages.Select City')</option>
                                @if (count(getCityList($settings->default_country)) > 0)
                                    @foreach (getCityList($settings->default_country) as $city)
                                        <option value="{{ $city->id }}" <?php if($city->id==$settings->default_city){ echo "selected=selected"; } ?> >{{ ucfirst($city->city_name) }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">@lang('messages.Language') <span class="asterisk">*</span></label>
                            <select name="default_language" id="country_id" class="form-control">
                                <option value="">@lang('messages.Select Language')</option>
                                @if (count(getCountryLists()) > 0)
                                    @foreach (getLanguageList() as $language)
                                        <option value="{{ $language->id }}" <?php if($language->id==$settings->default_language){ echo "selected=selected"; } ?> >{{ ucfirst($language->name) }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">@lang('messages.Currency') <span class="asterisk">*</span></label>
                            <select name="default_currency" id="country_id" class="form-control">
                                <option value="">@lang('messages.Select Currency')</option>
                                @if (count(getCurrencyList()) > 0)
                                    @foreach (getCurrencyList() as $currency)
                                        <option value="{{ $currency->id }}" <?php if($currency->id==$settings->default_currency){ echo "selected=selected"; } ?> >{{ ucfirst($currency->currency_name) }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="radio_new_button">
                                <div class="col-md-2 pading_left0">
                                    <label class="form-label">@lang('messages.Currency side at') <span class="asterisk">*</span></label>
                                </div>
                                <div class="col-md-2">
                                    <label><input type="radio" name="currency_side" value="1" <?php if($settings->currency_side == 1){ echo "checked"; } ?> class="form-control"><p>@lang('messages.Left')</p></label>
                                </div>
                                <div class="col-md-2">
                                    <label><input type="radio" name="currency_side" value="2" <?php if($settings->currency_side == 2){ echo "checked"; } ?> class="form-control"><p>@lang('messages.Right')</p></label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">@lang('messages.Weight Class') <span class="asterisk">*</span></label>
                            <select name="default_weight_class" id="weight_id" class="form-control">
                                <option value="">@lang('messages.Select Weight Class')</option>
                                @if (count(getWeightClass()) > 0)
                                    @foreach (getWeightClass() as $weight)
                                        <option value="{{ $weight->id }}" <?php if($weight->id==$settings->default_weight_class){ echo "selected=selected"; } ?> >{{ ucfirst($weight->title) }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn btn-primary mr5" title="@lang('Update')">@lang('messages.Update')</button>
                        <button type="reset" title="@lang('Cancel')" class="btn btn-default" onclick="window.location='{{ url('admin/dashboard') }}'">@lang('messages.Cancel')</button>
                    </div>
                </div>
            </div>
        </div>
    {!!Form::close();!!} 
</div>

<script type="text/javascript">
    $(document).ready(function(){
        $("#country_id").select2();
        var city_id="<?php echo $settings->default_city;?>";
        $("#city_id").select2();
    });
    $('#country_id').change(function(){
        var cid, token, url, data;
        token = $('input[name=_token]').val();
        cid   = $('#country_id').val();
        url   = '{{url('list/CityList')}}';
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
                    $.each(resp.data, function(key, value) {
                        //console.log(value['id']+'=='+value['city_name']);
                        $('#city_id').append($("<option></option>").attr("value",value['id']).text(value['city_name'])); 
                    });
                }
                else{
                    $('#select2-chosen-2').html('No Matches Found');
                }
            }
        });
    });
</script>
@endsection
