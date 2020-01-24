$( document ).ready(function() {
	$('#signup_link').on('click', function(){
		$('#myModal2').modal('hide');
		$('#myModal').modal('show');
	});
	$('.cancel_button').on('click', function()
	{
		$('#login')[0].reset();
		$('#forgot')[0].reset();
		$('#sign_up')[0].reset();
		$('#store_registernew')[0].reset();
		//~ $('#membership')[0].reset();
		//$('#login').find("#login input").val('').end();
		$('#forgot').find("#forgot input").val('').end();
		//$('#sign_up').find("#sign_up input").val('').end();
		$('#myModal3').modal('hide');
		$('#myModal').modal('hide');
		$('#myModal2').modal('hide');
		$('#store_register').modal('hide');
		$('#forgot_model').modal('hide');
		$('#address_model').modal('hide');
		$('#send_otp').modal('hide');
	});
	$('#sign_in').on('click', function() {
		$('#myModal').modal('hide');
		$('#myModal2').modal('show');
	});
	$('#store_register_link').on('click', function() {
		
		$('#myModal').modal('hide');
		$('#store_register').modal('show');
	});
	$('#forgot_pass').on('click', function() {
		$('#myModal2').modal('hide');
		$('#forgot_model').modal('show');
		$('.has-js').addClass('test_popup');
	});
	$('#forgot_pass').on('show.bs.modal', function (e) {
		$('body').addClass('test');
	});
	$(".delet_icon").on("click",function(event){
		event.stopPropagation();
		if(confirm("Do you want to delete?")) 
		{
			return true;
		}
		else
		{
			return false;
		}
		event.preventDefault();
	});

	$('#open_drop_hed').click(function() {
		
		$('.header_toogle_drop_menu').toggle();
		if ($("#open_drop_hed").hasClass("buttons_hed")) { 
			$( "#open_drop_hed" ).removeClass( "buttons_hed" );
		} else { 
			$( "#open_drop_hed" ).addClass( "buttons_hed" );
		}

	});
	$('#preview').on('click', function()
	{
		$('#photoimg').trigger('click');
	});
	
	
	$('#photoimg').on('change', function()
	{   
		$("#fadpage").show();
		jQuery("#imageform").ajaxForm(
		{  
			success:function() { location.reload(true); } 
		}).submit();
	});
	$('#select_country').on('change', function() 
	{
		$('#select_city').html("");
		country_id = $(this).val();
		var c_url = '/get_city';
		token = $('input[name=_token]').val();
		$.ajax({
			url: c_url,
			headers: {'X-CSRF-TOKEN': token},
			data: {country_id:country_id},
			type: 'POST',
			datatype: 'JSON',
			success: function (resp) {
				$('#select_city').empty();
				$('#select_city').trigger('contentChanged');
				resp = jQuery.parseJSON(resp);
				$.each( resp, function( key, value ) {
				  $('#select_city').append($("<option/>", {
						value: key,
						text: value
					}))
					$('#select_city').trigger('contentChanged');
				});
			}, 
			error:function(resp)
			{
				console.log('out--'+resp); 
				return false;
			}
		});
		return false;
	});
	$('.qty_increase, .qty_decrease,.delete_item').on('click', function() 
	{
		if($(this).hasClass('delete_item'))
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
			if ($(this).hasClass('qty_increase')) 
			{       $('.qty_decrease'+cart_id).removeAttr('disabled');
				qty = parseInt(qty)+1;
			}
			else
			{
				qty = parseInt(qty)-1;
					if(qty == 1)
					{
					$('.qty_decrease'+cart_id).attr('disabled', 'disabled');
					}


			}
		}
		update_cart(cart_detail_id,cart_id,qty);
		if(qty == 0)
		{
			if($(this).hasClass('delete_item'))
			{
				$(this).parent().parent().remove();
				cart_count = $('.cart_row').length;
				if(parseInt(cart_count) == 0)
				{
					$('.cart_items').hide();
					$('.empty_cart').css("display", "block");
				}
				return false;
			}
			$(this).parent().parent().parent().parent().parent().parent().remove();
			cart_count = $('.cart_row').length;
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
			item_total = parseFloat(item_price*quantity);
			$(this).parent().parent().parent().parent().find('.item_total').text(item_total);
		}
	});
	$('.slot_available').on('click', function() 
	{
		slot_text = $(this).attr("data-value");
		slot_id = $(this).attr("data-slot_id");
		delivery_date = $(this).attr("data-delivery_date");
		$("#slot_text").text(slot_text);
		$("#delivery_date").val(delivery_date);
		$('.responsive_table').find('.green_slat').removeClass("green_slat").addClass("available");
		$(this).removeClass("available").addClass("green_slat");
		$("#delivery_slot_id").val(slot_id);
	});
	var slot_val = $('.responsive_table').find(".available:first").attr("data-slot_id");
	var slot_tex = $('.responsive_table').find(".available:first").attr("data-value");
	var delivery_date = $('.responsive_table').find(".available:first").attr("data-delivery_date");
	$("#slot_text").text(slot_tex);
	$("#delivery_slot_id").val(slot_val);
	$("#delivery_date").val(delivery_date);

	$('.delivery, .pickup').on('click', function() 
	{


		if($(this).hasClass('delivery'))
		{
		
			var delivery_cost = $("#delivery_cost").html();
			var coupon_amount = $(".offer_amount_value").html();
			var total = $("#total").html();
			total = parseFloat(total) + parseFloat(delivery_cost);
			total_pay = parseFloat(total) + parseFloat(delivery_cost)+ parseFloat(coupon_amount);
			$("#total").html(total);
			$("#total_pay").html(total_pay);
			
			
			$('#pay-18').show();
			$("#pickup_info").hide();
			$("#delivery_info").show();
			$(".add_address").show();
			$(".time_slat_info").show();
			$(".delivery_cost_info").show();
		}
		else
		{
			
			var delivery_cost = $("#delivery_cost").html();
			var coupon_amount = $(".offer_amount_value").html();

			var total = $("#total").html();
			total = parseFloat(total) - parseFloat(delivery_cost);

			totalpay = parseFloat(total) - parseFloat(delivery_cost)+parseFloat(coupon_amount);
		   $("#total_pay").html(totalpay);
			$("#total").html(total);
			$('#pay-18').hide();
			$("#delivery_info").hide();
			$("#pickup_info").show();
			$(".add_address").hide();
			$(".time_slat_info").hide();
			$(".delivery_cost_info").hide();
			
			
		}
	});

});
	function update_cart(cart_detail_id,cart_id,qty)
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
			{ 
				$("#fadpage").hide();
				toastr.success(resp.Message)
				if(resp.total == 0)
				{
					$(".cart_items").hide();
					$(".empty_cart").show();
					location.reload(true);
				}
				$("#sub_total").text(resp.sub_total);
				$("#tax").text(resp.tax);
				$("#total").text(resp.total);
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
	function forgot()
	{
		$this = $("#forgotsubmit");
		$this.button('loading');
		data = $("#forgot").serializeArray();
		var c_url = '/forgot-password';
		token = $('input[name=_token]').val();
		$.ajax({
			url: c_url,
			headers: {'X-CSRF-TOKEN': token},
			data: data,
			type: 'POST',
			datatype: 'JSON',
			success: function (resp) {
				$this.button('reset');
				data = resp;
				if(data.httpCode == 200)
				{
					$('#forgot_model').modal('hide');
					toastr.success(data.Message)
				}
				else
				{
					toastr.warning(data.Message)
				}
			}, 
			error:function(resp)
			{
				$this.button('reset');
				console.log('out--'+data); 
				//location.reload(true);
				return false;
			}
		});
		return false;
	}
	
	function store_address()
	{
		$("#ajaxloading").show();
		data = $("#store-address-ajax").serializeArray();
		data.push({ name: "user_type", value: "1" });
		var c_url = '/store-address-ajax';
		token = $('input[name=_token]').val();
		$.ajax({
			url: c_url,
			headers: {'X-CSRF-TOKEN': token},
			data: data,
			type: 'POST',
			datatype: 'JSON',
			success: function (resp) 
			{
				$("#ajaxloading").hide();
				data = resp;
				if(data.httpCode == 200)
				{
					$('#address_model').modal('hide');
					toastr.success(data.Message);
					location.reload(true);
					toastr.options.onShown = function() { location.reload(true); }
				}
				else
				{
					toastr.warning(data.Message)
				}
			}, 
			error:function(resp)
			{
				console.log('out--'+data); 
				return false;
			}
		});
		return false;
	}
	
	function login()
	{
		$this = $("#signsubmit");
		$this.button('loading');
		$('#success_message_login' ).show().html("");
		//$("#ajaxloading").show();
		data = $("#login").serializeArray();
		data.push({ name: "user_type", value: "1" });
		var c_url = '/login-user';
		token = $('input[name=_token]').val();
		$.ajax({
			url: c_url,
			headers: {'X-CSRF-TOKEN': token},
			data: data,
			type: 'POST',
			datatype: 'JSON',
			success: function (resp) {
				//$("#ajaxloading").hide();
				$this.button('reset');
				data = resp;
				if(data.httpCode == 200)
				{
					$('#myModal2').modal('hide');
					toastr.success(data.Message);
					location.reload(true);
					toastr.options.onShown = function() { location.reload(true); }
				}
				else
				{
					toastr.warning(data.Message)
				}
			}, 
			error:function(resp)
			{
				console.log('out--'+data); 
				return false;
			}
		});
		return false;
	}
	
	function signup()
	{
		$this = $("#signupsubmit");
		$this.button('loading');
		$( '#success_message_signup' ).show().html("");
		data = $("#sign_up").serializeArray();
		data.push({ name: "user_type", value: "1" });
		var c_url = '/signup-user';
		token = $('input[name=_token]').val();
		$.ajax({
			url: c_url,
			headers: {'X-CSRF-TOKEN': token},
			data: data,
			type: 'POST',
			datatype: 'JSON',
			success: function (resp)
			{
				$this.button('reset');
				data = resp;
				if(data.httpCode == 200)
				{  
					$('#myModal').modal('hide');
					toastr.success(data.Message);
					location.reload(true);
					toastr.options.onShown = function() { location.reload(true); }
				}
				else
				{
					toastr.warning(data.Message)
				}
			},
			error:function(resp)
			{
				$this.button('reset');
				//$("#ajaxloading").hide();
				console.log('out--'+data); 
				return false;
			}
		});
		return false;
	}
	/*function storeregister()
	{   $this = $("#storeregistersubmit");
		$this.button('loading');

		data = $("#forgot").serializeArray();
		var c_url = '/forgot-password';
		token = $('input[name=_token]').val();
		$.ajax({
			url: c_url,
			headers: {'X-CSRF-TOKEN': token},
			data: data,
			type: 'POST',
			datatype: 'JSON',
			success: function (resp) {
				$this.button('reset');
				data = resp;
				if(data.httpCode == 200)
				{
					$('#forgot_model').modal('hide');
					toastr.success(data.Message)
				}
				else
				{
					toastr.warning(data.Message)
				}
			}, 
			error:function(resp)
			{
				$this.button('reset');
				console.log('out--'+data); 
				//location.reload(true);
				return false;
			}
		});
		return false;
	}
	 */
	function store_address()
	{
		$("#ajaxloading").show();
		data = $("#store-address-ajax").serializeArray();
		data.push({ name: "user_type", value: "1" });
		var c_url = '/store-address-ajax';
		token = $('input[name=_token]').val();
		$.ajax({
			url: c_url,
			headers: {'X-CSRF-TOKEN': token},
			data: data,
			type: 'POST',
			datatype: 'JSON',
			success: function (resp) 
			{
				$("#ajaxloading").hide();
				data = resp;
				if(data.httpCode == 200)
				{
					$('#address_model').modal('hide');
					toastr.success(data.Message);
					location.reload(true);
					toastr.options.onShown = function() { location.reload(true); }
				}
				else
				{
					toastr.warning(data.Message)
				}
			}, 
			error:function(resp)
			{
				console.log('out--'+data); 
				return false;
			}
		});
		return false;
	}
	
	function login()
	{
		$this = $("#signsubmit");
		$this.button('loading');
		$('#success_message_login' ).show().html("");
		//$("#ajaxloading").show();
		data = $("#login").serializeArray();
		data.push({ name: "user_type", value: "1" });
		var c_url = '/login-user';
		token = $('input[name=_token]').val();
		$.ajax({
			url: c_url,
			headers: {'X-CSRF-TOKEN': token},
			data: data,
			type: 'POST',
			datatype: 'JSON',
			success: function (resp) {
				//$("#ajaxloading").hide();
				$this.button('reset');
				data = resp;
				if(data.httpCode == 200)
				{
					$('#myModal2').modal('hide');
					toastr.success(data.Message);
					location.reload(true);
					toastr.options.onShown = function() { location.reload(true); }
				}
				else
				{
					toastr.warning(data.Message)
				}
			}, 
			error:function(resp)
			{
				console.log('out--'+data); 
				return false;
			}
		});
		return false;
	}
	
	function signup()
	{
		$this = $("#signupsubmit");
		$this.button('loading');
		$( '#success_message_signup' ).show().html("");
		data = $("#sign_up").serializeArray();
		data.push({ name: "user_type", value: "1" });
		var c_url = '/signup-user';
		token = $('input[name=_token]').val();
		$.ajax({
			url: c_url,
			headers: {'X-CSRF-TOKEN': token},
			data: data,
			type: 'POST',
			datatype: 'JSON',
			success: function (resp)
			{
				$this.button('reset');
				data = resp;
				if(data.httpCode == 200)
				{  
					$('#myModal').modal('hide');
					toastr.success(data.Message);
					location.reload(true);
					toastr.options.onShown = function() { location.reload(true); }
				}
				else
				{
					toastr.warning(data.Message)
				}
			},
			error:function(resp)
			{
				$this.button('reset');
				//$("#ajaxloading").hide();
				console.log('out--'+data); 
				return false;
			}
		});
		return false;
	}
	function store_address()
	{
		$("#ajaxloading").show();
		data = $("#store-address-ajax").serializeArray();
		data.push({ name: "user_type", value: "1" });
		var c_url = '/store-address-ajax';
		token = $('input[name=_token]').val();
		$.ajax({
			url: c_url,
			headers: {'X-CSRF-TOKEN': token},
			data: data,
			type: 'POST',
			datatype: 'JSON',
			success: function (resp) 
			{
				$("#ajaxloading").hide();
				data = resp;
				if(data.httpCode == 200)
				{
					$('#address_model').modal('hide');
					toastr.success(data.Message);
					location.reload(true);
					toastr.options.onShown = function() { location.reload(true); }
				}
				else
				{
					toastr.warning(data.Message)
				}
			}, 
			error:function(resp)
			{
				console.log('out--'+data); 
				return false;
			}
		});
		return false;
	}
	
	function login()
	{
		$this = $("#signsubmit");
		$this.button('loading');
		$('#success_message_login' ).show().html("");
		//$("#ajaxloading").show();
		data = $("#login").serializeArray();
		data.push({ name: "user_type", value: "1" });
		var c_url = '/login-user';
		token = $('input[name=_token]').val();
		$.ajax({
			url: c_url,
			headers: {'X-CSRF-TOKEN': token},
			data: data,
			type: 'POST',
			datatype: 'JSON',
			success: function (resp) {
				//$("#ajaxloading").hide();
				$this.button('reset');
				data = resp;
				if(data.httpCode == 200)
				{
					$('#myModal2').modal('hide');
					toastr.success(data.Message);
					location.reload(true);
					toastr.options.onShown = function() { location.reload(true); }
				}
				else
				{
					toastr.warning(data.Message)
				}
			}, 
			error:function(resp)
			{
				console.log('out--'+data); 
				return false;
			}
		});
		return false;
	}
	
	function signup()
	{
		$this = $("#signupsubmit");
		$this.button('loading');
		$( '#success_message_signup' ).show().html("");
		data = $("#sign_up").serializeArray();
		data.push({ name: "user_type", value: "1" });
		var c_url = '/signup-user';
		token = $('input[name=_token]').val();
		$.ajax({
			url: c_url,
			headers: {'X-CSRF-TOKEN': token},
			data: data,
			type: 'POST',
			datatype: 'JSON',
			success: function (resp)
			{
				$this.button('reset');
				data = resp;
				if(data.httpCode == 200)
				{  
					$('#myModal').modal('hide');
					toastr.success(data.Message);
					location.reload(true);
					toastr.options.onShown = function() { location.reload(true); }
				}
				else
				{
					toastr.warning(data.Message)
				}
			},
			error:function(resp)
			{
				$this.button('reset');
				//$("#ajaxloading").hide();
				console.log('out--'+data); 
				return false;
			}
		});
		return false;
	}
	function storeregister()
	{
		
		data = $("#store_registernew").serializeArray();
		data.push({ name: "user_type", value: "1" });
		var c_url = '/storeregister-user';
		token = $('input[name=_token]').val();
		$.ajax({
			url: c_url,
			headers: {'X-CSRF-TOKEN': token},
			data: data,
			type: 'POST',
			datatype: 'JSON',
			success: function (resp)
			{
				$(".ajaxloading").hide();
				data = resp;
				console.log(data.httpCode);
				if(data.httpCode == 200)
				{
					toastr.success(data.Message);
					location.reload(true);
					return false;
				}
				else
				{
				toastr.warning(data.Message);
					return false;	
				}
			}, 
			error:function(resp)
			{
			}
		});
		return false;
	}
	
	// get header height (without border)
	var getHeaderHeight = $('.headerContainerWrapper').outerHeight();
	// border height value (make sure to be the same as in your css)
	var borderAmount = 2;
	// shadow radius number (make sure to be the same as in your css)
	var shadowAmount = 30;
	// init variable for last scroll position
	var lastScrollPosition = 0;
	// set negative top position to create the animated header effect
	$('.headerContainerWrapper').css('top', '-' + (getHeaderHeight + shadowAmount + borderAmount) + 'px');
	/*$(window).scroll(function(){
		var currentScrollPosition = $(window).scrollTop();
		if ($(window).scrollTop() > 2 * (getHeaderHeight + shadowAmount + borderAmount))
		{
			$('body').addClass('scrollActive').css('padding-top', getHeaderHeight);
			$('.headerContainerWrapper').css('top', 0);
			if (currentScrollPosition < lastScrollPosition) {
				$('.headerContainerWrapper').css('top', '-' + (getHeaderHeight + shadowAmount + borderAmount) + 'px');
			}
			lastScrollPosition = currentScrollPosition;
		}
		else {
			$('body').removeClass('scrollActive').css('padding-top', 0);
		}
	});*/
        var owl = $('.owl-carousel');
        owl.owlCarousel({
            items: 3,
            loop: true,
            margin: 0,
            autoplay: true,
            autoplayTimeout: 1000,
            autoplayHoverPause: false,
            responsiveClass: true,
            autoplayHoverPause: true,
            responsive: {
                0: {
                    items: 1,
                    nav: true
                },
                400: {
                    items: 2,
                    nav: false
                },
                600: {
                    items: 3,
                    nav: false
                },

            }
        });
        $('.play').on('click', function() {
            owl.trigger('autoplay.play.owl', [1000])
        })
        $('.stop').on('click', function() {
            owl.trigger('autoplay.stop.owl')
        })
        // get header height (without border)
        var getHeaderHeight = $('.headerContainerWrapper').outerHeight();

        // border height value (make sure to be the same as in your css)
        var borderAmount = 2;

        // shadow radius number (make sure to be the same as in your css)
        var shadowAmount = 30;

        // init variable for last scroll position
        var lastScrollPosition = 0;

        // set negative top position to create the animated header effect
        $('.headerContainerWrapper').css('top', '-' + (getHeaderHeight + shadowAmount + borderAmount) + 'px');

        /*$(window).scroll(function() {
            var currentScrollPosition = $(window).scrollTop();

            if ($(window).scrollTop() > 2 * (getHeaderHeight + shadowAmount + borderAmount)) {

                $('body').addClass('scrollActive').css('padding-top', getHeaderHeight);
                $('.headerContainerWrapper').css('top', 0);

                if (currentScrollPosition < lastScrollPosition) {
                    $('.headerContainerWrapper').css('top', '-' + (getHeaderHeight + shadowAmount + borderAmount) + 'px');
                }
                lastScrollPosition = currentScrollPosition;

            } else {
                $('body').removeClass('scrollActive').css('padding-top', 0);
            }
        });*/

        function setupLabel() {
            if ($('.label_check input').length) {
                $('.label_check').each(function() {
                    $(this).removeClass('c_on');
                });
                $('.label_check input:checked').each(function() {
                    $(this).parent('label').addClass('c_on');
                });
            };
            if ($('.label_radio input').length) {
                $('.label_radio').each(function() {
                    $(this).removeClass('r_on');
                });
                $('.label_radio input:checked').each(function() {
                    $(this).parent('label').addClass('r_on');
                });
            };
        };
        $(document).ready(function() {
            $('body').addClass('has-js');
            $('.label_check, .label_radio').click(function() {
                setupLabel();
            });
            setupLabel();
        });
