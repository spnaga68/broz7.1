<?php

//echo "hai";
?>



@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script>
<link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/tinymce4.1/tinymce.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/ckeditor/ckeditor.js') }}"></script>
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
                        <li>@lang('messages.refferal_settings')</li>
                    </ul>
                    <h4>@lang('messages.refferal_settings')</h4>
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

                     {!!Form::open(array('url' => ['admin/settings/updaterefferal',1], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'driver_core_settings','files' => true));!!}
                     <?php //echo "<pre>"; print_r($data);exit; ?>

                  		<div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.referral_amount') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="referral_amount" id="referral_amount" placeholder="@lang('messages.referral_amount')" class="form-control" value="{{ $data->referral_amount }}"required />
                            </div>
                         </div>
 						<div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.referred_amount') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="referred_amount" id="referred_amount" placeholder="@lang('messages.referred_amount')" class="form-control" value="{{ $data->referred_amount }}"required />
                            </div>
                         </div>

                            <!--for ios-->

                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.order_to_complete') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="order_to_complete" id="order_to_complete" placeholder="@lang('messages.order_to_complete')" class="form-control" value="{{ $data->order_to_complete }}"required />
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

 $(document).ready(function(){
      var postURL = "admin/settings/updatedrivercore";
      var i=1;

  $('#submit').click(function(){

//window.location='{{ url('admin/dashboard') }}';
/*
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
*/


    });
});
</script>
@endsection
