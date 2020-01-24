@extends('layouts.managers')
@section('content')
<?php use App\Model\products;?>
<!-- Nav tabs -->
<div class="pageheader">
    <div class="media">
        <div class="pageicon pull-left">
            <i class="fa fa-home"></i>
        </div>
        <div class="media-body">
            <ul class="breadcrumb">
                <li><a href="{{ URL::to('managers/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Restaurant Managers')</a></li>
                <li>@lang('messages.Products')</li>
            </ul>
            <h4>@lang('messages.View Product Details')  - {{ ucfirst($data->product_name) }}</h4>
        </div>
    </div>
    <!-- media -->
</div>
<!-- pageheader -->
<div class="contentpanel">
    <ul class="nav nav-tabs"></ul>
    <div class="tab-content mb30">
        <div class="tab-pane active" id="home3">
            <div class="buttons_block pull-right">
                <div class="btn-group mr5">
                    <a class="btn btn-primary tip" href="{{ URL::to('managers/products/edit_product/'.$data->id) }}" title="@lang('messages.Edit')">@lang('messages.Edit')</a>
                </div>
            </div>
                <legend>@lang('messages.General Information')</legend>
                <?php $currency_side   = getCurrencyPosition()->currency_side;$currency_symbol = getCurrency(); ?>
                <div class="form-group">
                    <label class="col-sm-3 control-label">@lang('messages.Product Name')</label>
                    <div class="col-sm-7">{{ucfirst($data->product_name)}}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">@lang('messages.Description')</label>
                    <div class="col-sm-7">{{ucfirst($data->description)}}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">@lang('messages.Merchant Name')</label>
                    <div class="col-sm-7">{!! ucfirst($data->vendor_name) !!}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">@lang('messages.Restaurant Name')</label>
                    <div class="col-sm-7">{!! ucfirst($data->outlet_name) !!}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">@lang('messages.Category Name')</label>
                    <div class="col-sm-7">{!! ucfirst($data->category_name) !!}</div>
                </div>
               <?php /* <div class="form-group">
                    <label class="col-sm-3 control-label">@lang('messages.Cuisine Name')</label>
                    <div class="col-sm-7">
                        <?php// $cuisine = explode(',',$data->cuisine_ids);$cuisine_name = '';$cuisine_list = getAdminCuisineLists($cuisine);?>
                        @if(count($cuisine_list) > 0)
                            @foreach($cuisine_list as $list)
                                <?php $cuisine_name .= $list->cuisine_name.', '; ?>
                            @endforeach
                            <?php echo rtrim($cuisine_name,', ');?>
                        @else
                            -
                        @endif
                    </div>
                </div> */ ?>
                <div class="form-group">
                    <label class="col-sm-3 control-label">@lang('messages.Meta Title')</label>
                    <div class="col-sm-7">{{$data->meta_title}}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">@lang('messages.Meta Keywords')</label>
                    <div class="col-sm-7">{{$data->meta_keywords}}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">@lang('messages.Meta Description')</label>
                    <div class="col-sm-7">{{$data->meta_description}}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">@lang('messages.Product Image')</label>
                    <div class="col-sm-7">
                        <img src="<?php echo url('/assets/admin/base/images/products/list/'.$data->product_image.'?'.time()); ?>" class="thumbnail img-responsive" alt="{{ucfirst($data->product_name)}}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">@lang('messages.Publish Status')</label>
                    <div class="col-sm-7">
                        <?php if($data->approval_status==1){ ?> @lang('messages.Yes') <?php } else { ?> @lang('messages.No')<?php } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">@lang('messages.Status') </label>
                    <div class="col-sm-7">
                        <?php if($data->active_status==1){ ?> @lang('messages.Yes') <?php } else { ?> @lang('messages.No') <?php } ?>
                    </div>
                </div>
                <legend>@lang('messages.Data Information')</legend>
                <div class="form-group">
                    <label class="col-sm-3 control-label ">@lang('messages.Weight Class') </label>
                    <div class="col-sm-7">
                        <?php $weight_class = getWeightClass(); ?>
                        @foreach($weight_class as $list)
                            <?php echo ($data->weight_class_id == $list->id)?$list->title:''; ?>
                        @endforeach
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">@lang('messages.Weight Value')</label>
                    <div class="col-sm-7">{!! $data->weight !!}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">@lang('messages.Total Quantity')</label>
                    <div class="col-sm-7">{!! $data->quantity !!}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">@lang('messages.Original Price')</label>
                    <div class="col-sm-7">
                        <?php if($currency_side == 1){ echo $currency_symbol.$data->original_price; } else { echo $data->original_price.$currency_symbol; }?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">@lang('messages.Discounted Price')</label>
                    <div class="col-sm-7">
                        <?php if($currency_side == 1) { echo $currency_symbol.$data->discount_price;} else { echo $data->discount_price.$currency_symbol;} ?>
                    </div>
                </div>
                <?php /*
                if(count($product_ingre_type_list) > 0){ $i = 0;?>
                    <legend>@lang('messages.Toppings')</legend>
                    <?php foreach($product_ingre_type_list as $prod_ingr_type)
                    {$ingredient_type = isset($prod_ingr_type->ingredient_type)?$prod_ingr_type->ingredient_type:'';?>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">@lang('messages.Topping Type')</label>
                            <div class="col-sm-7">{{ucfirst($prod_ingr_type->ingredient_type_name)}}</div>
                        </div>
                        <?php $ingredient_list = products::get_product_ingred_type_ingred($data->id, $ingredient_type);
                        if(count($ingredient_list) > 0) 
                        {$ii = 1;
                            foreach($ingredient_list as $ingred)
                            {?>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">@lang('messages.Toppings')</label>
                                    <div class="col-sm-4">{{ucfirst($ingred->ingredient_name)}}</div>
                                    <label class="col-sm-1 control-label">@lang('messages.Price')</label>
                                    <div class="col-sm-1">{{$ingred->price}}</div>
                                </div>
                                <?php $ii++;
                            }
                        }?>
                        <div class="form-group">
                            <div class="col-sm-3 control-label">
                                <label>@lang('messages.Type')</label>
                            </div>
                            <div class="col-md-2">
                                <p><?php if(isset($prod_ingr_type->type) && $prod_ingr_type->type == 1) { echo trans('messages.Single');} else if(isset($prod_ingr_type->type) && $prod_ingr_type->type == 2) { echo trans('messages.Multiple');}?></p>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-3 control-label">
                                <label>@lang('messages.Required')</label>
                            </div>
                            <div class="col-md-2">
                                <p><?php if(isset($prod_ingr_type->required) && $prod_ingr_type->required == 1) { echo trans('messages.Yes');} else if(isset($prod_ingr_type->required) && $prod_ingr_type->required == 2) { echo trans('messages.No');}?></p>
                            </div>
                        </div>
                        <?php $i++;
                    }
                } */?>
            </div>
        </div>
    </div>
</div>
@endsection

