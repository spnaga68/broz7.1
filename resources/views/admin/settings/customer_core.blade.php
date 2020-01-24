<?php

//echo "hai";
?>



@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script>
<link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/tinymce4.1/tinymce.min.js') }}"></script>
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
                        <li>@lang('messages.Customer_core')</li>
                    </ul>
                    <h4>@lang('messages.Customer_core')</h4>
                </div>
            </div><!-- media -->
        </div><!-- pageheader -->

        <div class="contentpanel">
            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li><?php echo trans('messages.' . $error); ?> </li>
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
            <ul class="nav nav-tabs"></ul>

                <div class="tab-content mb30">
                    <div class="tab-pane active" id="home3">

                              {!!Form::open(array('url' => ['admin/settings/updatecustomercore',1], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'driver_core_settings','files' => true));!!}

                       <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.app_name') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="app_name" id="app_name" placeholder="@lang('messages.app_name')" class="form-control" value="{{ $data->app_name }}" required />
                            </div>
                        </div>



                       <!--  <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.app_logo') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                
                                <input type="file" maxlength="100" name="app_logo" id="app_logo" placeholder="@lang('messages.app_logo')" class="form-control" value="{!! old('answer') !!}"required />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.login_logo') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="file" maxlength="100" name="login_logo" id="login_logo" placeholder="@lang('messages.login_logo')" class="form-control" value="{!! old('answer') !!}"required />
                            </div>
                        </div> -->


                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.country_code') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="country_code" id="country_code" placeholder="@lang('messages.country_code')" class="form-control" value="{{ $data->country_code }}"required />
                            </div>
                         </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.android_key') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="android_key" id="android_key" placeholder="@lang('messages.android_key')" class="form-control" value="{{ $data->android_key }}"required />
                            </div>
                         </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.latest_version') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="latest_version" id="latest_version" placeholder="@lang('messages.latest_version')" class="form-control" value="{{ $data->latest_version }}"required />
                            </div>
                         </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.forceupdate_version') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="forceupdate_version" id="forceupdate_version" placeholder="@lang('messages.forceupdate_version ')" class="form-control" value="{{ $data->forceupdate_version }}"required />
                            </div>
                         </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.update_type') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="update_type" id="update_type" placeholder="@lang('messages.update_type')" class="form-control" value="{{ $data->update_type }}"required />
                            </div>
                         </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.update_message') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="update_message" id="update_message" placeholder="@lang('messages.update_message')" class="form-control" value="{{ $data->update_message }}"required />
                            </div>
                         </div>
                         <!--for ios-->

                           <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.ioslatest_version') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="ioslatest_version" id="ioslatest_version" placeholder="@lang('messages.ioslatest_version')" class="form-control" value="{{ $data->ioslatest_version }}"required />
                            </div>
                         </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.iosforceupdate_version') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="iosforceupdate_version" id="iosforceupdate_version" placeholder="@lang('messages.iosforceupdate_version ')" class="form-control" value="{{ $data->iosforceupdate_version }}"required />
                            </div>
                         </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.iosupdate_type') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="iosupdate_type" id="iosupdate_type" placeholder="@lang('messages.iosupdate_type')" class="form-control" value="{{ $data->iosupdate_type }}"required />
                            </div>
                         </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.iosupdate_message') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="iosupdate_message" id="iosupdate_message" placeholder="@lang('messages.iosupdate_message')" class="form-control" value="{{ $data->iosupdate_message }}"required />
                            </div>
                         </div>
                         <!--for ios-->




                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.no_imageurl') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="no_imageurl" id="no_imageurl" placeholder="@lang('messages.no_imageurl')" class="form-control" value="{{ $data->no_imageurl }}"required />
                            </div>
                         </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.error_reportcase') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="error_reportcase" id="error_reportcase" placeholder="@lang('messages.error_reportcase')" class="form-control" value="{{ $data->error_reportcase }}"required />
                            </div>
                         </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.socket_url') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="socket_url" id="socket_url" placeholder="@lang('messages.socket_url')" class="form-control" value="{{ $data->socket_url }}"required />
                            </div>
                         </div>
                        <!-- <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.aboutus') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <textarea  name="aboutus"  class="ckeditor form-control"  placeholder="<?php //echo trans('messages.Meta Description');?>">{{$data->aboutus}}</textarea>

                            </div>
                         </div> -->


                         <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.aboutus') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <textarea type="text" class="content" rows="7"  name="aboutus" >{{$data->aboutus}}</textarea> 
                            </div>
                        </div>

                        
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.terms_condition') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <textarea type="text" class="content" rows="7"  name="terms_condition" >{{ $data->terms_condition }}</textarea> 
                            </div>
                        </div>

                        <!-- <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.terms&condition ') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="terms&condition" id="terms&condition" placeholder="@lang('messages.terms&condition')" class="form-control" value="{{ $data->terms_condition }}"required />
                            </div>
                         </div> -->





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

    $(window).load(function(){
        tinymce.init({
            menubar : false,statusbar : true,plugins: [
                "advlist autolink lists link image charmap print preview hr anchor pagebreak code",
                "emoticons template paste textcolor colorpicker textpattern"
            ],
            toolbar1: "code | insertfile undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image preview | forecolor backcolor | fontsizeselect",
            height:'450px',
            selector: "textarea.content"
         });
    });

 /*$(document).ready(function(){
      var postURL = "/api/forms";
      var i=1;

  $('#submit').click(function(){

//window.location='{{ url('admin/dashboard') }}';

console.log($('#form').serialize());
           $.ajax({
                url:postURL,
                method:"POST",
                data:$('#form').serialize(),
                success:function(data)
                {

                     alert( data);

                }
           });
      });



    });*/
</script>
@endsection
