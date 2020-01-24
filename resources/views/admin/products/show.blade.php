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
				<li>@lang('messages.Products')</li>
			</ul>
			<h4>@lang('messages.View Product Details')  - {{ ucfirst($infomodel->getLabel('product_name',getAdminCurrentLang(),$data[0]->id)) }}</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->
<div class="contentpanel">
   <ul class="nav nav-tabs"></ul>
    <div class="tab-content mb30">
        <div class="tab-pane active" id="home3">
	<div class="buttons_block pull-right">
		<div class="btn-group mr5">
			<a class="btn btn-primary tip" href="{{ URL::to('/product/info/'.$data[0]->url_index.'/'.$data[0]->product_url) }}" title="Edit" >@lang('messages.Preview')</a>
			<a class="btn btn-primary tip" href="{{ URL::to('admin/products/edit_product/'.$data[0]->id . '') }}" title="Edit" >@lang('messages.Edit')</a>
		</div>
	</div>
	
        <legend>@lang('messages.General Information')</legend>
		<?php $currency_side   = getCurrencyPosition()->currency_side;$currency_symbol = getCurrency(); ?>
		<div class="form-group">
			<label class="col-sm-3 control-label">@lang('messages.Vendor Name')</label>
			<div class="col-sm-7">{!! ucfirst($data[0]->vendor_name) !!}</div>
		</div>
			<div class="form-group">
			<label class="col-sm-3 control-label">@lang('messages.Outlet Name')</label>
			<div class="col-sm-7">{!! ucfirst($data[0]->outlet_name) !!}</div>
	   </div>
		<div class="form-group">
			<label class="col-sm-3 control-label">@lang('messages.Category Name')</label>
			<div class="col-sm-7">{!! ucfirst($data[0]->category_name) !!}</div>
	   </div>
		<?php /*<div class="form-group">
			<label class="col-sm-3 control-label">@lang('messages.Sub Category Name')</label>
			<div class="col-sm-7">
				<?php  $sub_category = getSubCategoryLists(1,$data[0]->category_id); ?>
				@foreach ($sub_category as $val)
					<?php echo ($val->id == $data[0]->sub_category_id)?ucfirst($val->category_name):''; ?>
				@endforeach
			</div>
		</div>*/?>
		<div class="form-group">
			<label class="col-sm-3 control-label">@lang('messages.Product Name')</label>
			<div class="col-sm-7">{{ucfirst($infomodel->getLabel('product_name',getAdminCurrentLang(),$data[0]->id))}}</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">@lang('messages.Product URL')
			</label>
			<div class="col-sm-7">{{$data[0]->product_url}}</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">@lang('messages.Description')</label>
			<div class="col-sm-7">{{ucfirst($infomodel->getLabel('description',getAdminCurrentLang(),$data[0]->id))}}</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">@lang('messages.Meta Title')</label>
			<div class="col-sm-7">{{$infomodel->getLabel('meta_title',getAdminCurrentLang(),$data[0]->id)}}</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">@lang('messages.Meta Keywords')</label>
			<div class="col-sm-7">{{$infomodel->getLabel('meta_keywords',getAdminCurrentLang(),$data[0]->id)}}
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">@lang('messages.Meta Description')</label>
			<div class="col-sm-7">{{$infomodel->getLabel('meta_description',getAdminCurrentLang(),$data[0]->id)}}</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">@lang('messages.Product Image')</label>
			<div class="col-sm-7">
				<img src="<?php echo url('/assets/admin/base/images/products/list/'.$data[0]->product_image.''); ?>" class="thumbnail img-responsive" alt="logo">
			</div>
		</div>
		<div class="form-group">
			<label  class="col-sm-3 control-label">@lang('messages.Publish Status')</label>
			<div class="col-sm-7"><?php if($data[0]->approval_status==1){ ?> @lang('messages.Yes') <?php } else { ?> @lang('messages.No'); <?php } ?></div>
		</div>
		<div class="form-group">
			<label  class="col-sm-3 control-label">@lang('messages.Status') </label>
			<div class="col-sm-7"><?php if($data[0]->active_status==1){ ?> @lang('messages.Yes') <?php } else { ?> @lang('messages.No'); <?php } ?></div>
		</div>
		<legend>@lang('messages.Data Information')</legend>
		<div class="form-group">
			<label class="col-sm-3 control-label ">@lang('messages.Weight Class') </label>
			<div class="col-sm-7"><?php $weight_class = getWeightClass(); ?>
				@foreach($weight_class as $list)
					<?php echo ($data[0]->weight_class_id==$list->id)?$list->title:''; ?>
				@endforeach
			</div> 
		</div>
		<div class="form-group">
			<label  class="col-sm-3 control-label">@lang('messages.Weight Value')</label>
			<div class="col-sm-7">{!! $data[0]->weight !!}</div>
		</div>
			<div class="form-group">
			<label  class="col-sm-3 control-label">@lang('messages.Total Quantity')</label>
			<div class="col-sm-7">{!! $data[0]->quantity !!}</div>
		</div>
		<div class="form-group">
			<label  class="col-sm-3 control-label">@lang('messages.Original Price')</label>
			<div class="col-sm-7"><?php if($currency_side == 1)
						{
						echo $currency_symbol.$data[0]->original_price;
						}
						else {
						echo $data[0]->original_price.$currency_symbol;
						}
						?>
</div>
		</div>
		<div class="form-group">
			<label  class="col-sm-3 control-label">@lang('messages.Discounted Price')</label>
			<div class="col-sm-7"><?php if($currency_side == 1)
						{
						echo $currency_symbol.$data[0]->discount_price;
						}
						else {
						echo $data[0]->discount_price.$currency_symbol;
						}
						?></div></div>
		</div>
		</div>
    </div>
</div>
@endsection
