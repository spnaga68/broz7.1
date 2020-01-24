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
				<li>@lang('messages.Vendors')</li>
			</ul>
			<h4>@lang('messages.View Vendor Details')  - {{ $infomodel->getLabel('vendor_name',getAdminCurrentLang(),$data->id) }}</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->
<div class="contentpanel">
<ul class="nav nav-tabs"></ul>
    <div class="tab-content mb30">
        <div class="tab-pane active" id="home3">
	<div class="buttons_block pull-right">
		<div class="btn-group mr5">
			<a class="btn btn-primary tip" href="{{ URL::to('vendors/edit_vendor/'.$data->id . '') }}" title="Edit" >@lang('messages.Edit')</a>
		</div>
	</div>
	
            <legend>@lang('messages.Login Information')</legend>
			<div class="form-group">
				<label class="col-sm-4 control-label">@lang('messages.First Name')</label>
				<div class="col-sm-8">{!! $data->first_name !!}</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label">@lang('messages.Last Name')</label>
				<div class="col-sm-8">{!! $data->last_name !!}</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label">@lang('messages.Email')</label>
				<div class="col-sm-8">{!! $data->email !!}</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label">@lang('messages.Password')</label>
				<div class="col-sm-8">{!! $data->original_password !!}</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label">@lang('messages.Mobile Number')</label>
				<div class="col-sm-8">{!! $data->mobile_number !!}</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label">@lang('messages.Phone Number')</label>
				<div class="col-sm-8">{!! $data->phone_number !!}</div>
			</div>
			<legend>@lang('messages.Vendor Information')</legend>
			<div class="form-group">
				<label class="col-sm-4 control-label">@lang('messages.Vendor Name')</label>
				<div class="col-sm-8">{{$infomodel->getLabel('vendor_name',getAdminCurrentLang(),$data->id)}}
				</div>
		   </div>
			<div class="form-group">
				<label class="col-sm-4 control-label">@lang('messages.Vendor Description')</label>
				<div class="col-sm-8">{{$infomodel->getLabel('vendor_description',getAdminCurrentLang(),$data->id)}}
				</div>
		   </div>
		   
			<div class="form-group">
				<label class="col-sm-4 control-label">@lang('messages.Country')</label>
				<div class="col-sm-8">
					@foreach($countries as $list)
						<?php echo ($data->country_id==$list->id)?$list->country_name:'';?>
					@endforeach
				</div>
		   </div>
			<div class="form-group">
				<label class="col-sm-4 control-label">@lang('messages.City')</label>
				<div class="col-sm-8">
					<?php $city = getCityList($data->country_id); ?>
					@foreach($city as $list)
						<?php echo ($data->city_id==$list->id)?$list->city_name:'';?>
					@endforeach
				</div>
		   </div>	
						
			<div class="form-group">
				<label class="col-sm-4 control-label">@lang('messages.Category')</label>
				<div class="col-sm-8">
					<?php $category = explode(',',$data->category_ids);$category_name = '';?>
					@foreach($categories as $list)
						<?php $category_name .= in_array($list->id, $category) ? $list->category_name.', ' : ''; ?>
					@endforeach
					<?php echo rtrim($category_name,', ');?>
				</div>
		   </div>
			<div class="form-group">
				<label class="col-sm-4 control-label">@lang('messages.Logo')</label>
				<div class="col-sm-8">
				<?php if($data->logo_image){ ?>
					<img src="<?php echo url('/assets/admin/base/images/vendors/logos/'.$data->logo_image.''); ?>" class="thumbnail img-responsive" alt="logo">
                 <?php } ?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label">@lang('messages.Featured Image')</label>
				<div class="col-sm-8">
				<?php if($data->logo_image){ ?>
					<img src="<?php echo url('/assets/admin/base/images/vendors/thumb/'.$data->featured_image.''); ?>" class="thumbnail img-responsive" alt="featured_image">
                 <?php } ?>
				</div>
			</div>
		</div>
		
			<legend>@lang('messages.Delivery Information')</legend>
			<div class="form-group">
				<label  class="col-sm-4 control-label">@lang('messages.Delivery Time')</label>
				<div class="col-sm-8">{!! $data->delivery_time !!}</div>
			</div>
				<div class="form-group">
				<label  class="col-sm-4 control-label">@lang('messages.Pickup Time')</label>
				<div class="col-sm-8">{!! $data->pickup_time !!}</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-4 control-label">@lang('messages.Cancel Time')</label>
				<div class="col-sm-8">{!! $data->cancel_time !!}</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-4 control-label">@lang('messages.Return Time')</label>
				<div class="col-sm-8">{!! $data->return_time !!}</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-4 control-label">@lang('messages.Delivery Charges Fixed')</label>
				<div class="col-sm-8">{!! $data->delivery_charges_fixed !!}</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-4 control-label">@lang('messages.Delivery Cost Variation')</label>
				<div class="col-sm-8">{!! $data->delivery_cost_variation !!}</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-4 control-label">@lang('messages.Service Tax')</label>
				<div class="col-sm-8">{!! $data->service_tax!!}</div>
			</div>
			<legend>@lang('messages.Contact Information')</legend>
			<div class="form-group">
				<label  class="col-sm-4 control-label">@lang('messages.Contact Email')</label>
				<div class="col-sm-8">{!! $data->contact_email !!}</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-4 control-label">@lang('messages.Contact Address')</label>
				<div class="col-sm-8">{!! $data->contact_address !!}</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-4 control-label">@lang('messages.Featured Vendor')</label>
				<div class="col-sm-8"><?php if($data->featured_vendor==1){?> @lang('messages.Yes') <?php } else { ?> @lang('messages.No') <?php } ?>
				</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-4 control-label">@lang('messages.Status')</label>
				<div class="col-sm-8"><?php if($data->active_status==1){ ?> @lang('messages.Yes') <?php } else { ?> @lang('messages.No') <?php } ?>
				</div>
			</div>	
		</div>
    </div>

@endsection
