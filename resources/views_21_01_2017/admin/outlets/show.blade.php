@extends('layouts.admin')
@section('content')
<!-- Nav tabs -->
<div class="pageheader">
	<div class="media">
		<div class="pageicon pull-left">
			<i class="fa fa-home"></i>
		</div>
		<div class="media-body">
			<ul class="breadcrumb">
				<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
				<li>@lang('messages.Outlets')</li>
			</ul>
			<h4>@lang('messages.View Outlet Details')  - {!! ucfirst($data[0]->outlet_name) !!}</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->
<div class="contentpanel">
  <ul class="nav nav-tabs"></ul>
    <div class="tab-content mb30">
        <div class="tab-pane active" id="home3">
           
	<div class="buttons_block pull-right">
		<div class="btn-group mr5">
			<a class="btn btn-primary tip" href="{{ URL::to('vendors/edit_outlet/'.$data[0]->id . '') }}" title="@lang('messages.Edit')" >@lang('messages.Edit')</a>
		</div>
	</div>
	
            <legend>@lang('messages.Outlet Information')</legend>
			<div class="form-group">
				<label class="col-sm-5 control-label">@lang('messages.Outlet Name')</label>
				<div class="col-sm-7">{!! ucfirst($data[0]->outlet_name) !!}</div>
			</div>
			<div class="form-group">
				<label class="col-sm-5 control-label">@lang('messages.Vendor Name')</label>
				<div class="col-sm-7">{!! ucfirst($data[0]->vendor_name) !!}</div>
			</div>
			<div class="form-group">
				<label class="col-sm-5 control-label">@lang('messages.Country')</label>
				<div class="col-sm-7">
				@foreach($countries as $list)
					<?php echo ($data[0]->country_id==$list->id)?ucfirst($list->country_name):'';?>
				@endforeach
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-5 control-label">@lang('messages.City')</label>
				<div class="col-sm-7">
					<?php $city = getCityList($data[0]->country_id); ?>
					@foreach($city as $list)
						<?php echo ($data[0]->city_id==$list->id)?ucfirst($list->city_name):'';?>
					@endforeach
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-5 control-label">@lang('messages.Zone')</label>
				<div class="col-sm-7">
					<?php $location = getLocationList($data[0]->country_id,$data[0]->city_id); ?>
					@foreach($location as $list)
						<?php echo ($data[0]->location_id==$list->id)?ucfirst($list->zone_name):'';?>
					@endforeach
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-5 control-label">@lang('messages.Contact Phone')</label>
				<div class="col-sm-7">{!! $data[0]->contact_phone !!}</div>
			</div>
			<div class="form-group">
				<label class="col-sm-5 control-label">@lang('messages.Contact Email')</label>
				<div class="col-sm-7">{!! $data[0]->contact_email !!}</div>
			</div>
			<div class="form-group">
				<label class="col-sm-5 control-label">@lang('messages.Contact Address')</label>
				<div class="col-sm-7">{!! ucfirst($data[0]->contact_address) !!}</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-5 control-label">@lang('messages.Status')</label>
				<div class="col-sm-7"><?php if($data[0]->active_status==1){ ?> @lang('messages.Yes') <?php } else { ?> @lang('messages.No'); <?php } ?>
				</div>
			</div>	
			<legend>@lang('messages.Delivery Information')</legend>
			<div class="form-group">
				<label  class="col-sm-5 control-label">@lang('messages.Delivery Time')</label>
				<div class="col-sm-7">{!! $data[0]->delivery_time !!}</div>
			</div>
				<div class="form-group">
				<label  class="col-sm-5 control-label">@lang('messages.Pickup Time')</label>
				<div class="col-sm-7">{!! $data[0]->pickup_time !!}</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-5 control-label">@lang('messages.Cancel Time')</label>
				<div class="col-sm-7">{!! $data[0]->cancel_time !!}</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-5 control-label">@lang('messages.Return Time')</label>
				<div class="col-sm-7">{!! $data[0]->return_time !!}</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-5 control-label">@lang('messages.Delivery Charges Fixed')</label>
				<div class="col-sm-7">{!! $data[0]->delivery_charges_fixed !!}</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-5 control-label">@lang('messages.Delivery Cost Variation')</label>
				<div class="col-sm-7">{!! $data[0]->delivery_charges_variation !!}</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-5 control-label">@lang('messages.Service Tax')</label>
				<div class="col-sm-7">{!! $data[0]->service_tax!!}</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-5 control-label">@lang('messages.Minimum Order Amount')</label>
				<div class="col-sm-7">{!! $data[0]->minimum_order_amount!!}</div>
			</div>
		</div>
		
			<legend>@lang('messages.Opening Hours')</legend>
			<?php
				$timearray = getDaysWeekArray();
				foreach($timearray as $key1 => $val1) {
					$u_time = getOpenTimings($data[0]->id,$val1);
			?>
				<div id="value-<?php echo $key1;  ?>" class="row mb15">				
				<label class="col-sm-3 pt10" for="timing_<?php echo $key1; ?>"> <span class="<?php echo (isset($u_time[0]->day_week) && $u_time[0]->day_week==$val1)?'checked':'';?>"> <?php echo $key1; ?>  </span></label>
				<div class="row">
					<div class="col-sm-4">
						 <div class="input-group mb15">
								<span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
								 <div class="bootstrap-timepicker">
									<input type="text" value="<?php echo (isset($u_time[0]->start_time))?date("h:i a", strtotime($u_time[0]->start_time)):'Leave'; ?>" class="timepicker form-control" readonly disabled>
								 </div>
						</div>
					</div>
					<div class="pt10 pull-left">@lang('messages.To')</div>
						<div class="col-sm-4">
							<div class="input-group mb15">
								  <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
								  <div class="bootstrap-timepicker"> 
									<input type="text"  value="<?php echo isset($u_time[0]->end_time)?date("h:i a", strtotime($u_time[0]->end_time)):'Leave'; ?>" class="timepicker form-control" readonly disabled>
								  </div>
							</div>
						</div> 
					</div>
				</div>
			<?php }  ?>
			
		</div>
    </div>

@endsection
