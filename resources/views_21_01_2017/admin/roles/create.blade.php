@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/colorpicker.js') }}"></script>
<link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/admin/base/css/colorpicker.css') }}" media="all" rel="stylesheet" type="text/css" />
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
                        <li>@lang('messages.Roles Mangagement')</li>
                    </ul>
                    <h4>@lang('messages.Add Role')</h4>
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
            {!!Form::open(array('url' => 'system/rolecreate', 'method' => 'post','class'=>'tab-form attribute_form','id'=>'currency_form','files' => true));!!} 
                <div class="tab-content mb30">
                    <div class="tab-pane active" id="home3">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Role Name') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" autofocus name="role_name" id="role_name" maxlength="32" placeholder="@lang('messages.Role Name')" class="form-control" value="{!! old('role_name') !!}" />
                                <span id="role_tag_prev" class="label label-default"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Api Key')<span class="asterisk">*</span></label>
                            <div class="col-sm-6">
                                <div> 
                                    <input type="text" name="app_key" value="{!! old('app_key') !!}" class="form-control" maxlength="36">
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <button class="btn btn-primary" type="button" id="generate_app_key">@lang('messages.Generate')</button>
                            </div>
                        </div><!-- form-group -->

                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Tag Background Color')</label>
                            <div class="col-sm-6">
                                <div class="controls">
                                    <input type="text" name="tag_bg_color" value="{!! old('tag_bg_color') !!}"  class="form-control colorpicker-input" placeholder="#000000" id="colorpicker" />
                                    <span id="colorSelector" class="colorselector"><span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Tag Font Color')</label>
                            <div class="col-sm-6">
                                <div class="controls">
                                    <input type="text" name="tag_text_color" value="{!! old('tag_text_color') !!}" class="form-control colorpicker-input" placeholder="#000000"  id="colortextpicker" />
                                    <span id="colortextSelector" class="colorselector"><span>
                                </div>
                            </div>
                        </div>
                        <?php echo $taskresource; ?>
                    </div>

                    <div class="panel-footer">
                        <button class="btn btn-primary mr5" title="Save">@lang('messages.Save')</button>
                        <button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('system/permission') }}'">@lang('messages.Cancel')</button>
                    </div>
                </div>
            {!!Form::close();!!} 
        </div>
    </div>
</div>
<script>
    //<![CDATA[
    $(window).load(function(){
        $('#role_name').focus(); 
        $("#role_name").keyup(function(){
            $("#role_tag_prev").text($(this).val());
        });
        if(jQuery('#colorpicker').length > 0)
        {
            jQuery('#colorSelector').ColorPicker({
                onShow: function (colpkr) {
                    jQuery(colpkr).fadeIn(500);
                    return false;
                },
                onHide: function (colpkr) {
                    jQuery(colpkr).fadeOut(500);
                    return false;
                },
                onChange: function (hsb, hex, rgb) {
                    jQuery('#colorSelector span').css('backgroundColor', '#' + hex);
                    jQuery('#colorpicker').val('#'+hex);
                    $("#role_tag_prev").css('backgroundColor', '#' + hex);
                }
            });
        }

        if(jQuery('#colortextpicker').length > 0)
        {
            jQuery('#colortextSelector').ColorPicker({
                onShow: function (colpkr) {
                    jQuery(colpkr).fadeIn(500);
                    return false;
                },
                onHide: function (colpkr) {
                    jQuery(colpkr).fadeOut(500);
                    return false;
                },
                onChange: function (hsb, hex, rgb) {
                    jQuery('#colortextSelector span').css('backgroundColor', '#' + hex);
                    jQuery('#colortextpicker').val('#'+hex);
                    $("#role_tag_prev").css('color', '#' + hex);
                }
            });
        }
        $("#generate_app_key").click(function(){
            var string = randomString(32, '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ');
            $("input[name='app_key']").val(string);
        });

        $(".parentcheckbox").click(function(){
            var id = $(this).val();
            if ($(this).is(":checked"))
            {
                $("#"+id).find('input.toggle').bootstrapSwitch('state',true);
            }
            else {
                $("#"+id).find('input.toggle').bootstrapSwitch('state',false);
            }
        });
        $("input.toggle").on('switchChange.bootstrapSwitch',function(){ 
            checkanduncheck($(this).parents("table")); 
        })
        $("form#role-permission").find('table').each(function(){
            checkanduncheck($(this));
        });
    })
    function randomString(length, chars)
    {
        var result = '';
        for (var i = length; i > 0; --i) result += chars[Math.round(Math.random() * (chars.length - 1))];
        return result;
    }

    function checkanduncheck(tablelement)
    {
        var totalchecked = tablelement.find('input.toggle:checked').length;
        if (totalchecked > 0)
        {
            tablelement.find('.parentcheckbox').attr('checked',true);
        }
        else {
            tablelement.find('.parentcheckbox').attr('checked',false);
        } 
    }
    //]]>
</script>
<script>
    $(window).load(function(){
        $('form').preventDoubleSubmission();
    });
</script>
@endsection
