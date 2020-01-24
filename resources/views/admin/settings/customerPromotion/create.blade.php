
@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/jquery-ui-1.10.3.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script> 
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/bootstrap-timepicker.min.js') }}"></script>
<link href="{{ URL::asset('assets/admin/base/css/bootstrap-timepicker.min.css') }}" media="all" rel="stylesheet" type="text/css" /> 
<link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/tinymce4.1/tinymce.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/moment-with-locales.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/bootstrap-datetimepicker.js') }}"></script>
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
                        <li>@lang('messages.Customer Promotion')</li>
                    </ul>
                    <h4>@lang('messages.Add Customer Promotion')</h4>
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
            {!!Form::open(array('url' => 'create_customerPromotion', 'method' => 'post', 'class' => 'tab-form attribute_form', 'id' => 'create_driver_form', 'files' => true));!!} 
                <div class="tab-content mb30">
                    <div class="tab-pane active" id="home3">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Promotion Name') <span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <input type="text" name="promotion_name" maxlength="30" placeholder="@lang('messages.Promotion Name')" class="form-control" value="{!! old('promotion_name') !!}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Base Amount') <span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <input type="text" name="base_amount" maxlength="30" placeholder="@lang('messages.Base Amount')" class="form-control" value="{!! old('base_amount') !!}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Addition Promotion')<span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <input type="text" name="addition_promotion" maxlength="100" placeholder="@lang('messages.Addition Promotion')" class="form-control" value="{!! old('addition_promotion') !!}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Grocery Wallet') <span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <input type="text" name="grocery_wallet" maxlength="100" placeholder="@lang('messages.Grocery Wallet')" class="form-control" value="{!! old('grocery_wallet') !!}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Start Date') <span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="start_date" autocomplete="off" value="{!! old('start_date') !!}" placeholder="yyyy-mm-dd" id="start_date" >
                                <div class="calender_common">
                                    <span class="input-group-addon datepicker-trigger"><i class="glyphicon glyphicon-calendar" id="start_date"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.End Date') <span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="end_date" autocomplete="off" value="{!! old('end_date') !!}" placeholder="yyyy-mm-dd" id="end_date" >
                                <div class="calender_common">
                                    <span class="input-group-addon datepicker-trigger"><i class="glyphicon glyphicon-calendar" id="end_date"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Image')</label>
                            <div class="col-sm-6">
                                <input type="file" name="image" />
                                <span class="help-text">@lang('messages.Please upload 75X75 images for better quality')</span>
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
                    <div class="panel-footer">
                        <button class="btn btn-primary mr5" title="Save">@lang('messages.Save')</button>
                        <button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('admin/settings/customer_promotion') }}'">@lang('messages.Cancel')</button>
                    </div>
                </div>
            {!!Form::close();!!}
        </div>
    </div>
</div>
<script> 
    $(window).load(function(){
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
            $("#start_date").datetimepicker("view");
            $("#end_date").datetimepicker("view");
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

</script>

@endsection
