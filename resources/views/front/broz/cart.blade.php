@extends('layouts.app')
@section('content')
<?php $general = Session::get("general");
$minimum_order_amount = 0;?>
<section class="store_item_list">
    <div class="container">
        <div class="cms_pages">
            <div class="stor_title">
                <h1>@lang('messages.Cart')</h1>
            </div>

			<?php $currency_side = getCurrencyPosition()->currency_side;
$currency_symbol = getCurrency($language);?>
            <div class="cart_sections_tables">
                <?php if (count($cart_items) > 0) {
	?>
                    <div class="table-responsive cart_items">
                        <div class="responsive_tables">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class ="cartalign">@lang('messages.Products')</th>
                                        <th>@lang('messages.Price')</th>
                                        <th>@lang('messages.Quantity')</th>
                                        <th>@lang('messages.Remove')</th>
                                        <th class="items_quantty">@lang('messages.Total')</th>
                                    </tr>
                                </thead>
                                <tbody><?php $minimum_order_amount = $cart_items[0]->minimum_order_amount;?>
                                <?php $outlet_url_index = $cart_items[0]->url_index;?>
                                    <?php foreach ($cart_items as $items) {?>

                                        <tr data-cart_id="{{$items->cart_id}}" data-cart_detail_id="{{$items->cart_detail_id}}" class="cart_row">
                                            <td style="text-align:left;">
                                                <a href="javascript:;" title="{{ ucfirst(strtolower($items->product_name)) }}">
                                                    <?php if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $items->product_image)) {?>
                                                        <img src="<?php echo url('/assets/admin/base/images/products/list/' . $items->product_image); ?>" title="{{ $items->product_name }}">
                                                    <?php } else {?>
                                                        <img src="{{ URL::asset('assets/admin/base/images/products/product.png') }}" alt="{{ ucfirst(strtolower($items->product_name)) }}">
                                                    <?php }?>{{ str_limit(ucfirst(strtolower($items->product_name)),30) }}
                                                </a>
                                            </td>
											<?php if ($currency_side == 1) {?>
                                            <td>{{$currency_symbol}}<span class="item_price">{{$items->discount_price}}</span>
											</td>
											<?php } else {?>
											 <td><span class="item_price">{{$items->discount_price}}</span>{{$currency_symbol}}
											</td>
											<?php }?>
                                            <td>
                                                <div class="count_numbers">
                                                    <ul data-item_price="{{$items->discount_price}}">
                                                        <li class="minuse_count qty_decrease1"><a class="" href="javascript:;">-</a></li>
                                                        <li class="minuse_number">
                                                            <input class="actual_quantity" type="text" readonly value="{{$items->quantity}}">
                                                        </li>
                                                        <li class="pluse_number qty_increase1"><a class="" href="javascript:;">+</a></li>
                                                    </ul>

                                                </div>
                                            </td>
                                            <td valign="middle" class="delet_icons">
                                                <a href="javascript:;" class="delete_item1" title="">
                                                    <i class="glyph-icon flaticon-delete"></i>
                                                </a>
                                            </td>
											<?php if ($currency_side == 1) {?>
                                                 <td valign="middle" class="items_quantty">{{$currency_symbol}}<span class="item_total">{{$items->discount_price*$items->quantity}}</span></td>
											<?php } else {?>
											     <td valign="middle" class="items_quantty"><span class="item_total">{{$items->discount_price*$items->quantity}}</span>{{$currency_symbol}}</td>
											<?php }?>
                                        </tr>
                                    <?php }?>
                                </tbody>
                            </table>
                        </div>
                        <div class="price_setions">
                            <div class="price_setions_list">
                                <div class="col-md-7 dis_none"></div>
                                <div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.Subtotal')</label></div>
								<?php if ($currency_side == 1) {?>
                                     <div class="col-md-2 col-sm-6 col-xs-6"><p>{{$currency_symbol}}<span id="sub_total">{{$sub_total}}</span></span></p></div>
								<?php } else {?>
								     <div class="col-md-2 col-sm-6 col-xs-6"><p><span id="sub_total">{{$sub_total}}</span>{{$currency_symbol}}</span></p></div>
								<?php }?>
                            </div>
                            <div class="price_setions_list">
				<div class="col-md-7 dis_none"></div>
				<div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.Delivery cost')</label></div>
				<?php if ($currency_side == 1) {?>
				      <div class="col-md-2 col-sm-6 col-xs-6"><p>{{$currency_symbol}}<span id="delivery_cost">{{$delivery_cost}}</span></p></div>
				<?php } else {?>
				      <div class="col-md-2 col-sm-6 col-xs-6"><p><span id="delivery_cost">{{$delivery_cost}}</span>{{$currency_symbol}}</p></div>
				<?php }?>
			</div>
                            <div class="price_setions_list">
                                <div class="col-md-7 dis_none"></div>
                                <div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.tax')</label></div>
								<?php if ($currency_side == 1) {?>
                                     <div class="col-md-2 col-sm-6 col-xs-6"><p>{{$currency_symbol}}<span id="tax">{{$tax_amount}}</span></span></p></div>
								<?php } else {?>
                                     <div class="col-md-2 col-sm-6 col-xs-6"><p><span id="tax">{{$tax_amount}}</span>{{$currency_symbol}}</span></p></div>
								<?php }?>
                            </div>
                            <div class="price_setions_list_total">
                                <div class="col-md-7 dis_none"></div>
                                <div class="col-md-3 col-sm-6 col-xs-6"><label>@lang('messages.Total with delivery cost')</label></div>
								<?php if ($currency_side == 1) {?>
                                    <div class="col-md-2 col-sm-6 col-xs-6"><p>{{$currency_symbol}}<span id="total">{{$total}}</span></p></div>
								<?php } else {?>
								   <div class="col-md-2 col-sm-6 col-xs-6"><p><span id="total">{{$total}}</span>{{$currency_symbol}}</p></div>
								   <?php }?>
                            </div>
                            <div class="price_buttons">
                                <a href="{{url('store/info/'.$outlet_url_index)}}" class = "btn btn-primary btn-lg" title=" @lang('messages.Continue shopping')">
                                @lang('messages.Continue shopping')
                                </a>
                                 <?php $remaining_amount = 0;
	if ($total <= $minimum_order_amount) {
		$remaining_amount = round($minimum_order_amount - $total, 2);
		if ($currency_side == 1) {
			$remaining_amount = $currency_symbol . $remaining_amount;
		} else {
			$remaining_amount = $remaining_amount . $currency_symbol;
		}?>
                                 <a href="" class="btn btn-default btn-lg" id="proceed_to_checkout" title=" @lang('messages.Add '){{$remaining_amount}}@lang('messages. to Checkout')" disabled>@lang('messages.Add '){{$remaining_amount}}@lang('messages. to Checkout')</a>
                                <?php } else {?>
                                    <a href="javascript:;" onclick="proceed_to_checkout(this)" class = "btn btn-default btn-lg" id="proceed_to_checkout" title=" @lang('messages.Proceed to checkout')">@lang('messages.Proceed to checkout')</a>
                                <?php }?>
                            </div>
                        </div>
                    </div>
                    <?php $no_cart = 'style=display:none';
} else { $no_cart = 'style=display:block';}?>
                <div class="table-responsive empty_cart" {{$no_cart}}>
                    <div class="no_cart">
                        <img src="<?php echo URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_pro_03.png'); ?>" alt="ums_logo">
                        <h2>@lang('messages.You don’t have any items in your shopping cart.')</h2>
                    </div>
                    <div class="price_buttons">
                        <a href="{{url('/')}}" class = "btn btn-primary btn-lg" title=" @lang('messages.Continue shopping')">@lang('messages.Continue shopping')</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- container end -->
</section>
 <?php $cdata = get_cart_count();?>
                           <?php $cart_item = 0;if (count($cdata) > 0) {
	$cart_item = $cdata[0]->cart_count;}?>

<script type="text/javascript">

		 var min_order_amount = '<?php echo $minimum_order_amount; ?>';
$('.qty_increase1, .qty_decrease1,.delete_item1').on('click', function()
	{
		if($(this).hasClass('delete_item1'))
		{
			if(confirm("Are you sure want to delete?"))
			{

			}
			else
			{
				return false;
			}

			cart_id = $(this).parent().parent().attr("data-cart_id");
			cart_detail_id = $(this).parent().parent().attr("data-cart_detail_id");
			qty = 0;
		}
		else
		{
			cart_id = $(this).parent().parent().parent().parent().attr("data-cart_id");
			cart_detail_id = $(this).parent().parent().parent().parent().attr("data-cart_detail_id");
			qty = $(this).parent().find('.actual_quantity').val();
			if ($(this).hasClass('qty_increase1'))
			{       $('.qty_decrease1'+cart_id).removeAttr('disabled');
				qty = parseInt(qty)+1;
			}
			else
			{
				if(qty == 1)
					{
					$('.qty_decrease1'+cart_id).attr('disabled', 'disabled');
					}
					else{
				qty = parseInt(qty)-1;
			}



			}
		}

	update_cart_1(cart_detail_id,cart_id,qty);
		if(qty == 0)
		{
			if($(this).hasClass('delete_item1'))
			{
				 $(this).parent().parent().remove();
				 cart_count = $('.cart_row').length;
				 $('.cart_total_count').html('('+cart_count+')');
				if(parseInt(cart_count) == 0)
				{
					$('.cart_items').hide();
					$('.empty_cart').css("display", "block");

				}
				return false;

			}
			$(this).parent().parent().parent().parent().parent().parent().remove();
			cart_count = $('.cart_row').length;
			 $('.cart_total_count').html('('+cart_count+')');
			if(parseInt(cart_count) == 0)
			{
				$('.cart_items').hide();
				$('.empty_cart').css("display", "block");
			}
			return false;
		}
		else
		{
			$(this).parent().find('.actual_quantity').val(qty);
			quantity = $(this).parent().find('.actual_quantity').val();
			item_price = $(this).parent().parent().parent().parent().find('.item_price').html();
			item_total = parseFloat(item_price*quantity).toFixed(2);
			$(this).parent().parent().parent().parent().find('.item_total').text(item_total);
		}

	});
	function update_cart_1(cart_detail_id,cart_id,qty)
	{
		$("#fadpage").show();
		var c_url = '/update-cart';
		token = $('input[name=_token]').val();
		$.ajax({
			url: c_url,
			headers: {'X-CSRF-TOKEN': token},
			data: {cart_detail_id:cart_detail_id,cart_id:cart_id,qty:qty},
			type: 'POST',
			datatype: 'JSON',
			success: function (resp)
			{   cart_count = $('.cart_row').length;
				$("#fadpage").hide();
				 if(parseInt(cart_count) == 0){
			    toastr.success("<?php echo trans('messages.Your cart is empty Now') ?>");
		               }
				else if(qty == 0){
				toastr.success("<?php echo trans('messages.Cart has been deleted successfully!') ?>");
			       }

				if(resp.total == 0)
				{
					$(".cart_items").hide();
					$(".empty_cart").show();
					location.reload(true);
				}

				$("#sub_total").text(resp.sub_total);
				$("#tax").text(resp.tax_amount);
				$("#total").text(resp.total);


				if(resp.total <= min_order_amount)
                {
                    remaining_amount = min_order_amount - resp.total;
                    <?php if ($currency_side == 1) {?>
                        remaining_amount = '<?php echo $currency_symbol; ?>'+' '+remaining_amount.toFixed(2);
                    <?php } else {?>
                        remaining_amount = remaining_amount.toFixed(2)+' '+'<?php echo $currency_symbol; ?>';
                    <?php }?>
                    $('#proceed_to_checkout').html('<?php echo trans('messages.Add '); ?>'+remaining_amount+'<?php echo trans('messages. to Checkout'); ?>').attr('title','<?php echo trans('messages.Add '); ?>'+remaining_amount+'<?php echo trans('messages. to Checkout'); ?>').attr('disabled', 'disabled');
                }
                 else {
                    $('#proceed_to_checkout').html('<?php echo trans('messages.Proceed to checkout'); ?>').attr('title','<?php echo trans('messages.Proceed to checkout'); ?>').removeAttr('disabled');
                }
				return true;
			},
			error:function(resp)
			{
				console.log('out--'+resp);
				return false;
			}
		});
		return false;
	}
	function proceed_to_checkout(checkout)
    {
        if($('#proceed_to_checkout').is('[disabled=disabled]'))
        {}
        else
        {
            user_id = "<?php echo Session::get('user_id'); ?>";
            if(user_id == "")
            {
                $('.cart_dyn_sec').modal('hide');
                $('#myModal2').modal('show');
                return false;
            }
            else {
                window.location.href='{{url("checkout")}}';
            }
        }
    }
		  window.addEventListener( "pageshow", function ( event ) {
var historyTraversal = event.persisted || ( typeof window.performance != "undefined" && window.performance.navigation.type === 2 );
if ( historyTraversal ) {
// Handle page restore.
window.location.reload();
}
});
	</script>

@endsection

