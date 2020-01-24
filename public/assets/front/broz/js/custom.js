jQuery.fn.preventDoubleSubmission = function() {
    $(this).on('submit', function(e) {
        var $form = $(this);
        if ($form.data('submitted') === true) {
            e.preventDefault();
        } else {
            $form.data('submitted', true);
        }
    });
    return this;
};
jQuery(document).ready(function() {
    "use strict";
    jQuery('.tooltips').tooltip({
        container: 'body'
    });
    if (jQuery('.toggle').length) {
        $('.toggle').bootstrapSwitch();
    }
    jQuery('.popovers').popover();
    jQuery('.panel-heading').hover(function() {
        jQuery(this).find('.panel-btns').fadeIn('fast');
    }, function() {
        jQuery(this).find('.panel-btns').fadeOut('fast');
    });
    jQuery('.panel .panel-close').click(function() {
        jQuery(this).closest('.panel').fadeOut(200);
        return false;
    });
    jQuery('[data-toggle="modal"]').click(function(e) {
        $('.signup_form').removeClass('signup_form_error');
        resetErrors();
    });
    jQuery('[data-dismiss="modal"]').click(function(e) {
        $('.signup_form').removeClass('signup_form_error');
        $('#signin_form').find("#signin_form input").val('').end();
        $('#signup_form').find("#signup_form input,select").val('').end();
        $('#forgot_form').find("#forgot_form input").val('').end();
		 $('#forgot_form').find("#forgot_form input").val('').end();
		 $('#storeregister_form').find("#storeregister_form input").val('').end();
        $('#login')[0].reset();
        $('#forgot')[0].reset();
        $('#sign_up')[0].reset();
		$('#membership')[0].reset();
		$('#store_registernew')[0].reset();
		
        $('#appointment_form').find("#appointment_form input").val('').end();
        resetErrors();
    });
    jQuery('.close_model').click(function() {
        $('.modal').modal('hide');
    });
    jQuery('.close').click(function() {
        $("body").css({
            "padding-right": "0px !important"
        });
        $('.responce').html('');
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    });
});
var showPopover = $.fn.popover.Constructor.prototype.show;
$.fn.popover.Constructor.prototype.show = function() {
    showPopover.call(this);
    if (this.options.showCallback) {
        this.options.showCallback.call(this)
    }
};
var hidePopover = $.fn.popover.Constructor.prototype.hide;
$.fn.popover.Constructor.prototype.hide = function() {
    if (this.options.hideCallback) {
        this.options.hideCallback.call(this)
    }
    hidePopover.call(this)
}

function resetErrors() {
    $('form input, form select').removeClass('inputTxtError');
    $('label.error').remove();
}

function HideButton(hideparam, showparam) {
    $("." + hideparam).hide();
    $("." + showparam).show();
}