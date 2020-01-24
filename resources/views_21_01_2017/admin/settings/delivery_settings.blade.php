@extends('layouts.admin')
@section('content')
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
			<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>{{ trans('messages.Admin') }}</a></li>
			<li>@lang('messages.Settings')</li>
		</ul>
		<h4>@lang('messages.Delivery Settings')</h4>
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
<div class="alert alert-info success">
<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>
    {{ Session::get('message') }}</div></div></div>
@endif
<?php $settings_id=0;
if(isset($data->id)&& $data->id!=''){ $settings_id=$data->id; } ?>
       {!!Form::open(array('url' => ['admin/settings/update_delivery_settings', $settings_id], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'settings_edit_form','files' => true));!!}
<div class="col-md-12">
<div class="row panel panel-default">
<div class="grid simple">
	<div id="general" class="panel-heading">
	<h4 class="panel-title">@lang('messages.Delivery')</h4>
	<p>@lang('messages.Delivery Settings')</p>
		<div class="tools">
			<a class="collapse" href="javascript:;"></a>
		</div>
	</div>
	<ul class="nav nav-tabs"></ul>
	<div class="panel-body">
		<div class="form-group">
                            <label class="col-sm-3 control-label">@lang('messages.Status') <span class="asterisk">*</span></label>
                            <div class="col-sm-9">
								<?php if($data->on_off_status && !old('on_off_status')) { ?>
                                <select name="on_off_status" id="status" class="form-control">
                                    <option value="">@lang("messages.Select status")</option>
                                    <option <?php if($data->on_off_status == 1) { echo "selected=selected"; } ?> value="1" >@lang("messages.Yes")</option>
                                    <option <?php if($data->on_off_status == 2) { echo "selected=selected"; }?> value="2" >@lang("messages.No")</option>
                                </select>
                                <?php } else{ ?>
									<select name="on_off_status" id="status" class="form-control">
                                    <option value="">@lang("messages.Select status")</option>
                                    <option <?php if(old('on_off_status') == 1) { echo "selected=selected"; } ?> value="1" >@lang("messages.Yes")</option>
                                    <option <?php if(old('on_off_status') == 2 ) { echo "selected=selected"; }?> value="2" >@lang("messages.No")</option>
                                </select>
									<?php } ?>
                            </div>
        </div>
	
		<div class="delivery_type">
			<div class="form-group">
								<label class="col-sm-3 control-label">@lang('messages.Type') <span class="asterisk">*</span></label>
								<div class="col-sm-9">
									<?php if($data->delivery_type && !old('delivery_type')) { ?>
									<select name="delivery_type" id="type" class="form-control">
										<option value="">@lang("messages.Select type")</option>
										<option value="1" <?php if($data->delivery_type == 1) { echo "selected=selected"; } ?>>@lang("messages.Charges by distance")</option>
										<option value="2" <?php if($data->delivery_type == 2) { echo "selected=selected"; }?>>@lang("messages.Flat delivery charge")</option>
									</select>
									<?php } else { ?>
										<select name="delivery_type" id="type" class="form-control">
										<option value="">@lang("messages.Select type")</option>
										<option value="1" <?php if(old('delivery_type') == 1) { echo "selected=selected"; } ?>>@lang("messages.Charges by distance")</option>
										<option value="2" <?php if(old('delivery_type') == 2) { echo "selected=selected"; }?>>@lang("messages.Flat delivery charge")</option>
									</select>
									<?php } ?>
									
								</div>
			</div>

			<div class="cost_by_distance">
				
				<div class="form-group">
									<label class="col-sm-3 control-label">@lang('messages.Fixed Delivery Km') <span class="asterisk">*</span></label>
									<div class="col-sm-9">
										<input type="text" name="delivery_km_fixed" maxlength="6"  placeholder="@lang('messages.Fixed Delivery Km')" class="form-control" value="{!! $data->delivery_km_fixed !!}" />
										<span class="help-block">@lang('messages.Fixed Delivery kilometer')</span>
									</div>
									
				</div>
				
				<div class="form-group">
									<label class="col-sm-3 control-label">@lang('messages.Delivery Charges Fixed') <span class="asterisk">*</span></label>
									<div class="col-sm-9">
										<input type="text" name="delivery_cost_fixed" maxlength="6"  placeholder="@lang('messages.Delivery Charges Fixed')" class="form-control" value="{!! $data->delivery_cost_fixed !!}" />
										<span class="help-block">@lang('messages.Fixed Delivery Charges fixed with upto') {!! $data->delivery_km_fixed !!} kms</span>
									</div>
									
				</div>

				<div class="form-group">
									<label class="col-sm-3 control-label">@lang('messages.Delivery Charges Variation') <span class="asterisk">*</span></label>
									<div class="col-sm-9">
										<input type="text" name="delivery_cost_variation" maxlength="6"  placeholder="@lang('messages.Delivery Charges Variation')" class="form-control" value="{!! $data->delivery_cost_variation !!}" />
										<span class="help-block">@lang('messages.Delivery Charges Variation after') {!! $data->delivery_km_fixed !!} @lang('messages.km every km will added with this amount')</span>
									</div>
									
				</div>
			</div>

			<div class="flat_delivery_cost">
				<div class="form-group">
									<label class="col-sm-3 control-label">@lang('messages.Flat delivery charge') <span class="asterisk">*</span></label>
									<div class="col-sm-9">
										<input type="text" name="flat_delivery_cost" maxlength="6"  placeholder="@lang('messages.Flat delivery charge')" class="form-control" value="{!! $data->flat_delivery_cost !!}" />
										<span class="help-block">@lang('messages.Flat delivery charge')</span>
									</div>
									
				</div>
			</div>

			<div class="form-group">
								<label class="col-sm-3 control-label">@lang('messages.Minimum order amount') <span class="asterisk">*</span></label>
								<div class="col-sm-9">
									<input type="text" name="minimum_order_amount" maxlength="6"  placeholder="@lang('messages.Minimum order amount')" class="form-control" value="{!! $data->minimum_order_amount !!}" />
								</div>
			</div>
        </div>
		
	
</div>
<div class="panel-footer">
		<button class="btn btn-primary mr5" title="@lang('Update')">@lang('messages.Update')</button>
		<button type="reset" title="@lang('Cancel')" class="btn btn-default" onclick="window.location='{{ url('admin/dashboard') }}'">@lang('messages.Cancel')</button>
	</div>
</div></div></div>

 {!!Form::close();!!} 
</div></div></div>
<?php if(old('on_off_status')==1){  ?>
	<script>
	$(document).ready(function(){
		
		 $('.delivery_type').show();
		 $('.cost_by_distance').hide();
		 $('.flat_delivery_cost').hide();
	});
	</script>
	<?php if(old('delivery_type')==1){ ?>
		<script>
	$(document).ready(function(){
		 $('.cost_by_distance').show();
		 $('.flat_delivery_cost').hide();
	});
	</script>
	<?php } ?>
	<?php if(old('delivery_type')==2){ ?>
		<script>
	$(document).ready(function(){
		 $('.cost_by_distance').hide();
		 $('.flat_delivery_cost').show();
	});
	</script>
	<?php } ?>
	<script>
	$(document).ready(function(){
	$('#status').change(function(){
			var status = $('#status').val();
			if(status == 1){
				$('.delivery_type').show();
			}
			if(status == 2){
				$('.delivery_type').hide();
			}
			
		});
		$('#type').change(function(){
			var type = $('#type').val();
			if(type == 1){
				$('.cost_by_distance').show();
				$('.flat_delivery_cost').hide();
			}
			if(type == 2){
				$('.cost_by_distance').hide();
				$('.flat_delivery_cost').show();
			}
			
		});
		});
		</script>
	<?php }  ?>

<?php if($data->on_off_status==1){ ?>
	<script>
	$(document).ready(function(){
		 $('.delivery_type').show();
	});
	</script>
	<?php if($data->delivery_type==1){ ?>
		<script>
	$(document).ready(function(){
		 $('.cost_by_distance').show();
		 $('.flat_delivery_cost').hide();
	});
	</script>
	<?php } ?>
	<?php if($data->delivery_type==2){ ?>
		<script>
	$(document).ready(function(){
		 $('.cost_by_distance').hide();
		 $('.flat_delivery_cost').show();
	});
	</script>
	<?php } ?>
	<script>
	$(document).ready(function(){
	$('#status').change(function(){
			var status = $('#status').val();
			if(status == 1){
				$('.delivery_type').show();
			}
			if(status == 2){
				$('.delivery_type').hide();
			}
			
		});
		$('#type').change(function(){
			var type = $('#type').val();
			if(type == 1){
				$('.cost_by_distance').show();
				$('.flat_delivery_cost').hide();
			}
			if(type == 2){
				$('.cost_by_distance').hide();
				$('.flat_delivery_cost').show();
			}
			
		});
		});
		</script>
	<?php } else { if(!old('on_off_status')){ ?>
	<script>
	$(document).ready(function(){
		 $('.delivery_type').hide();
		 $('.flat_delivery_cost').hide();
		 $('.cost_by_distance').hide();
		 $('#status').change(function(){
			var status = $('#status').val();
			if(status == 1){
				$('.delivery_type').show();
			}
			if(status == 2){
				$('.delivery_type').hide();
			}
			
		});
		$('#type').change(function(){
			var type = $('#type').val();
			if(type == 1){
				$('.cost_by_distance').show();
				$('.flat_delivery_cost').hide();
			}
			if(type == 2){
				$('.cost_by_distance').hide();
				$('.flat_delivery_cost').show();
			}
			
		});
    });
	</script>
	<?php }  } ?>
@endsection

