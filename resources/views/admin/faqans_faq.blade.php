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
                        <li>@lang('messages.Faq')</li>
                    </ul>
                    <h4>@lang('messages.Faq')</h4>
                </div>
            </div><!-- media -->
        </div><!-- pageheader -->

        <div class="contentpanel">
            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">�</span><span class="sr-only">@lang('messages.Close')</span></button>
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
                        <div class="alert alert-info success"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">�</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
                    </div>
                </div>
            @endif
            <ul class="nav nav-tabs"></ul>
             {!!Form::open(array('url' => 'admin/insert_faq', 'method' => 'post','class'=>'tab-form attribute_form','id'=>'cms_form','files' => true));!!}

                <div class="tab-content mb30">
                    <div class="tab-pane active" id="home3">

                        <div class="form-group">
<!--                              <form name="form" id="form" method="Post">
 -->                            <label class="col-sm-2 control-label">@lang('messages.Subject') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="subject" id="subject" placeholder="@lang('messages.Subject')" class="form-control" value="{!! old('subject') !!}" required />
                            </div>
                        </div>



                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Answer') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="answer" id="answer" placeholder="@lang('messages.Answer')" class="form-control" value="{!! old('answer') !!}"required />
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Type') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="type" id="type" placeholder="@lang('messages.Type')" class="form-control" value="{!! old('Type') !!}"required />
                            </div>
                        </div>

                    </div>
                    <div class="panel-footer">
                        <button class="btn btn-primary mr5" name="Save" id="submit" >@lang('messages.Save')</button>
                        <button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('admin/dashboard') }}'">@lang('messages.Cancel')</button>
                    </div>
                </div>
            {!!Form::close();!!}
        </div>
    </div>

<!--       </form>
 -->
</div>
<script type="text/javascript">

 $(document).ready(function(){
      var postURL = "/api/admin/insert_faq";
      var i=1;

  $('#submit').click(function(){
                //location.href="admin/dashboard";

console.log($('#form').serialize());
           $.ajax({
                url:postURL,
             headers: {'X-CSRF-TOKEN': token},

                type:"POST",
                data:$('#form').serialize(),
               // type:'string',
                success:function(data)
                {

                     alert('Data from the server' + data);

                }
           });
      });



    });
</script>
@endsection
