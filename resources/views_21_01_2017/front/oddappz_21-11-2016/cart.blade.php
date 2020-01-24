@extends('layouts.app')
@section('content')
<?php $general = Session::get("general"); //print_r($general);exit; ?>
<section class="store_item_list">
    <div class="container">
        <div class="cms_pages">
            <div class="stor_title">
                <h1>@lang('messages.Cart')</h1>
            </div>
            <div class="cart_sections_tables">
                <?php if(count($cart_items)>0) {?>
                    <div class="table-responsive cart_items"> 
                        <div class="responsive_tables">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th style="text-align:left;">@lang('messages.Items')</th>
                                        <th>@lang('messages.Price')</th>
                                        <th>@lang('messages.Quantity')</th>
                                        <th>@lang('messages.Remove')</th>
                                        <th class="items_quantty">@lang('messages.Total')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($cart_items as $items){ ?>
                                        <tr data-cart_id="{{$items->cart_id}}" data-cart_detail_id="{{$items->cart_detail_id}}">
                                            <td style="text-align:left;">
                                                <a href="javascript:;" title="{{ ucfirst(strtolower($items->product_name)) }}">
                                                    <?php if(file_exists(base_path().'/public/assets/admin/base/images/products/list/'.$items->product_image)) { ?>
                                                        <img src="<?php echo url('/assets/admin/base/images/products/list/'.$items->product_image); ?>" title="{{ $items->product_name }}">
                                                    <?php } else { ?>
                                                        <img src="{{ URL::asset('assets/admin/base/images/products/product.png') }}" alt="{{ ucfirst(strtolower($items->product_name)) }}">
                                                    <?php } ?>{{ str_limit(ucfirst(strtolower($items->product_name)),30) }}
                                                </a>
                                            </td>
                                            <td><span class="item_price">{{$items->discount_price}}</span>{{getCurrency()}}</td>
                                            <td>
                                                <div class="count_numbers">
                                                    <ul data-item_price="{{$items->discount_price}}">
                                                        <li class="minuse_count qty_decrease"><a class="" href="javascript:;">-</a></li>
                                                        <li class="minuse_number">
                                                            <input class="actual_quantity" type="text" readonly value="{{$items->quantity}}">
                                                        </li>
                                                        <li class="pluse_number qty_increase"><a class="" href="javascript:;">+</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                            <td valign="middle" class="delet_icons">
                                                <a href="javascript:;" class="delete_item" title="">
                                                    <i class="glyph-icon flaticon-delete"></i>
                                                </a>
                                            </td>
                                            <td valign="middle" class="items_quantty"><span class="item_total">{{$items->discount_price*$items->quantity}}</span>{{getCurrency()}}</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="price_setions">
                            <div class="price_setions_list">
                                <div class="col-md-7 dis_none"></div>
                                <div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.Subtotal')</label></div>
                                <div class="col-md-2 col-sm-6 col-xs-6"><p><span id="sub_total">{{$sub_total}}</span>{{getCurrency()}}</span></p></div>
                            </div>
                            <div class="price_setions_list">
                                <div class="col-md-7 dis_none"></div>
                                <div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.tax')</label></div>
                                <div class="col-md-2 col-sm-6 col-xs-6"><p><span id="tax">{{$tax}}</span>{{getCurrency()}}</span></p></div>
                            </div>
                            <div class="price_setions_list_total">
                                <div class="col-md-7 dis_none"></div>
                                <div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.Total')</label></div>
                                <div class="col-md-2 col-sm-6 col-xs-6"><p><span id="total">{{$total}}</span>{{getCurrency()}}</p></div>
                            </div>
                            <div class="price_buttons">
                                <a href="{{url('')}}" class = "btn btn-primary btn-lg" title=" @lang('messages.Continue shopping')">
                                @lang('messages.Continue shopping')
                                </a>
                                <a href="{{url('checkout')}}" class = "btn btn-default btn-lg" title=" @lang('messages.Proceed to checkout')">
                                @lang('messages.Proceed to checkout')
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php $no_cart = 'style=display:none';
                } else { $no_cart = 'style=display:block'; } ?>
                <div class="table-responsive empty_cart" {{$no_cart}}> 
                    <div class="no_cart">
                        <img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/no_pro_03.png');?>" alt="ums_logo">
                        <h2>@lang('messages.You have no items in your shopping cart.')</h2>
                    </div>
                    <div class="price_buttons">
                        <a href="{{url('')}}" class = "btn btn-primary btn-lg" title=" @lang('messages.Continue shopping')">@lang('messages.Continue shopping')</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- container end -->
</section>
<script type="text/javascript">
    $('select').select2();
</script>
@endsection
