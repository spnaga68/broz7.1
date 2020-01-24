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
                        <li>@lang('messages.Newsletter')</li>
                    </ul>
                    <h4>@lang('messages.Newsletter')</h4>
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
            <ul class="nav nav-tabs"></ul>
            {!!Form::open(array('url' => 'send_newsletter', 'method' => 'post','class'=>'tab-form attribute_form','id'=>'newsletter_form','files' => true));!!} 
                <div class="tab-content mb30">
                    <div class="tab-pane active" id="home3">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.User Type') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <select name="entity_type" class="form-control" id="entity_type">
                                    <option value = '1'>@lang('messages.All Customers')</option>
                                    <option value = '2'>@lang('messages.Newsletter Subscribers')</option>
                                    <option value = '3'>@lang('messages.Customers Group')</option>
                                    <option value = '4'>@lang('messages.Specific Customers')</option>
                                </select>
                            </div>
                        </div>
                         <div class="form-group">
                            <label class="col-sm-2 control-label ">@lang('messages.User') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <label class="control-label ">Select All</label>  <input type="checkbox" id="checkbox" > 
                                <select id="user_id" name="users[]" data-placeholder="@lang('messages.Select Users')" multiple style="width:100%" class="width300 select2-chosen-2"></select>
                            </div> 
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Subject') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" maxlength="100" name="subject" id="" placeholder="@lang('messages.Subject')" class="form-control" value="{!! old('subject') !!}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Message') <span class="asterisk">*</span></label>
                            <div class="col-sm-10">
                                <textarea type="text" class="content" rows="7"  name="message" >{!! old('message') !!}</textarea> 
                            </div>
                        </div>
                        <?php /*<div class="form-group">
                            <label class="col-sm-2 control-label">@lang('messages.Priority')</label>
                            <div class="col-sm-10">
                                <select name="priority" style="width:100%"  data-placeholder="Choose One"  class="no-changes form-control input-sm mb5 ">
                                    <option value="error" <?php echo (old('priority') == 'error') ? 'selected' : ''; ?>>@lang('messages.Error')</option>
                                    <option value="urgent" <?php echo (old('priority') == 'urgent') ? 'selected' : ''; ?>>@lang('messages.Urgent')</option>
                                    <option value="important" <?php echo (old('priority') == 'important') ? 'selected' : ''; ?>>@lang('messages.Important')</option>
                                    <option value="normal" <?php echo (old('priority') == 'normal') ? 'selected' : ''; ?>>@lang('messages.Normal')</option>
                                </select>
                            </div>
                        </div>*/ ?>
                    </div>
                    <div class="panel-footer">
                        <button class="btn btn-primary mr5" title="Send">@lang('messages.Send')</button>
                        <button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('admin/dashboard') }}'">@lang('messages.Cancel')</button>
                    </div>
                </div>
            {!!Form::close();!!} 
        </div>
    </div>
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
    $(document).ready(function() {
        $("#entity_type").change(function(){
            var entitytype = $(this).val(); 
            var token, url, data;
            token = $('input[name=_token]').val();
            if (entitytype == 1 || entitytype == 4 )
            {
                url = '{{url('list/all_customers')}}';
            }
            else if (entitytype == 2 )
            {
                url = '{{url('list/newsletter_subscribers')}}';
            }
            else if (entitytype == 3 )
            {
                url = '{{url('list/customers_group')}}';
            }
            var html = '';
            $.ajax({
                url: url,
                headers: {'X-CSRF-TOKEN': token},
                type: 'POST',
                data: {entity_type : entitytype},
                datatype: 'JSON',
                success: function(res) { 
                    $("#user_id").select2("val", "");
                    if(res.data!='')
                    { 
                        if (entitytype == 3 )
                        {
                            $.each(res.data, function(key, value) {
                                html +='<option value='+value["group_id"]+'>'+ value["group_name"]+'</option>'; 
                            });
                        }
                        else {
                            $.each(res.data, function(key, value) {
                                html +='<option value='+value["email"]+'>'+ value["email"]+'</option>'; 
                            });
                        }
                    }
                    else {
                        $('.select2-chosen-2').html('No Matches Found');
                    }
                    $("#user_id").html(html);
                }
            })
            $("#checkbox").click(function(){
                if($("#checkbox").is(':checked') )
                {
                    $("#user_id > option").prop("selected","selected");
                    $("#user_id").trigger("change");
                }
                else {
                    $("#user_id > option").removeAttr("selected");
                    $("#user_id").trigger("change");
                }
            });
        });
        $("#entity_type").change(); 
        $('#user_id').select2();
    });
</script>
@endsection
