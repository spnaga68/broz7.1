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
                <li>@lang('messages.Driver')</li>
            </ul>
            <h4>@lang('messages.Drivers Settings')</h4>
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
    <?php $settings_id=0;if(isset($drivers_settings->id)&& $drivers_settings->id!=''){ $settings_id=$drivers_settings->id; } ?>
    {!!Form::open(array('url' => ['admin/drivers/updatesettings', $settings_id], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'local_edit_form','files' => true));!!}
        <div class="col-md-12">
            <div class="row panel panel-default">
                <div class="grid simple">
					<?php /* 
                    <div id="general" class="panel-heading">
                        <h4 class="panel-title">@lang('messages.Local')</h4>
                        <p>@lang('messages.Local settings')</p>
                        <div class="tools">
                            <a class="collapse" href="javascript:;"></a>
                        </div>
                    </div>
                    */ ?>
                    
                    <ul class="nav nav-tabs"></ul>
                    <div class="panel-body">


						<div id="smtp_port" class="form-group">
							<label class="form-label">@lang('messages.Order Accept Time') <span class="asterisk">*</span></label>
							<span class="help">@lang('messages.Order accept waiting time for the driver')</span>
							<div class="controls">
							<input class="form-control" type="text" maxlength="300" value="{{ $drivers_settings->order_accept_time }}" required name="order_accept_time">
							<span class="help">@lang('messages.Time in minutes Ex:10')</span>
							</div>
						</div>

						<div id="smtp_port" class="form-group">
							<label class="form-label">@lang('messages.Driver Order Total') <span class="asterisk">*</span></label>
							<span class="help">@lang('messages.Total order request at a time')</span>
							<div class="controls">
							<input class="form-control" type="text" maxlength="300" value="{{ $drivers_settings->driver_order_total }}" required name="driver_order_total">
							<span class="help">@lang('messages.Total order count Ex:5')</span>
							</div>
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
@endsection
