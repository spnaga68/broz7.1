   jQuery.fn.preventDoubleSubmission = function() {
      $(this).on('submit',function(e){
        var $form = $(this);
    
        if ($form.data('submitted') === true) { 
          // Previously submitted - don't submit again
          e.preventDefault();
        } else {
          // Mark it so that the next submit can be ignored
          $form.data('submitted', true);
        }
      });
    
      // Keep chainability
      return this;
    };
jQuery(document).ready(function() {

   "use strict";

   // Tooltip
   jQuery('.tooltips').tooltip({ container: 'body'});

   //jQuery("#select-basic, #select-multi, #source, #source1").select2();

   if (jQuery('.toggle').length) {
      $('.toggle').bootstrapSwitch();
   }

   // Popover
   jQuery('.popovers').popover();

   // Show panel buttons when hovering panel heading
   jQuery('.panel-heading').hover(function() {
      jQuery(this).find('.panel-btns').fadeIn('fast');
   }, function() {
      jQuery(this).find('.panel-btns').fadeOut('fast');
   });

   // Close Panel
   jQuery('.panel .panel-close').click(function() {
      jQuery(this).closest('.panel').fadeOut(200);
      return false;
   });
   
   // Close model
   jQuery('.close_model').click(function() {
      $('.modal').modal('hide');
   });
   jQuery('.close').click(function() {
	   $('.responce').html('');
   });

   // Minimize Panel
   jQuery('.panel .panel-minimize').click(function(){
      var t = jQuery(this);
      var p = t.closest('.panel');
      if(!jQuery(this).hasClass('maximize')) {
         p.find('.panel-body, .panel-footer').slideUp(200);
         t.addClass('maximize');
         t.find('i').removeClass('fa-minus').addClass('fa-plus');
         jQuery(this).attr('data-original-title','Maximize Panel').tooltip();
      } else {
         p.find('.panel-body, .panel-footer').slideDown(200);
         t.removeClass('maximize');
         t.find('i').removeClass('fa-plus').addClass('fa-minus');
         jQuery(this).attr('data-original-title','Minimize Panel').tooltip();
      }
      return false;
   });

   jQuery('.leftpanel .nav .parent > a').click(function() {

      var coll = jQuery(this).parents('.collapsed').length;

      if (!coll) {
         jQuery('.leftpanel .nav .parent-focus').each(function() {
            jQuery(this).find('.children').slideUp('fast');
            jQuery(this).removeClass('parent-focus');
         });

         var child = jQuery(this).parent().find('.children');
         if(!child.is(':visible')) {
            child.slideDown('fast');
            if(!child.parent().hasClass('active'))
               child.parent().addClass('parent-focus');
         } else {
            child.slideUp('fast');
            child.parent().removeClass('parent-focus');
         }
      }
      return false;
   });

   $(document).on( 'click', function ( e ) {
      if ( $( e.target ).closest('#search-lists').length === 0 ) {
          $("#search-lists").hide();
      }
   });
   
   $( document ).on( 'keydown', function ( e ) {
       if ( e.keyCode === 27 ) { // ESC
           $("#search-lists").hide();
       }
   });
   jQuery('.leftpanel .nav li').hover(function(){
      $(this).addClass('nav-hover');
   }, function(){
      $(this).removeClass('nav-hover');
   });
   //**********************************BEGIN MAIN MENU********************************
   /*jQuery('.leftpanel .nav li.active').each(function (e) {
      $(this).find('li')   
   });*/
   


   //$('form:not(.attribute_form)').preventDoubleSubmission();
	
	
	//$('#settings-form').preventDoubleSubmission();	

   
   jQuery('.leftpanel .nav li > a').on('click', function (e) {
           if ($(this).next().hasClass('sub-menu') == false) {
               return;
   }
     var parent = $(this).parent().parent();

            parent.children('li.open').children('a').children('.arrow').removeClass('open');
            parent.children('li.open').children('.sub-menu').slideUp(200);
            parent.children('li.open').removeClass('open');

            var sub = jQuery(this).next();
            if (sub.is(":visible")) {
                jQuery('.arrow', jQuery(this)).removeClass("open");
                jQuery(this).parent().removeClass("open");
                sub.slideUp(200, function () {
                    handleSidenarAndContentHeight();
                });
            } else {
                jQuery('.arrow', jQuery(this)).addClass("open");
                jQuery(this).parent().addClass("open");
                sub.slideDown(200, function () {
                    handleSidenarAndContentHeight();
                });
            }

            e.preventDefault();
        });
    var handleSidenarAndContentHeight = function () {
        var content = $('.page-content');
        var sidebar = $('.leftpanel .nav');

        if (!content.attr("data-height")) {
            content.attr("data-height", content.height());
        }

        if (sidebar.height() > content.height()) {
            content.css("min-height", sidebar.height() + 120);
        } else {
            content.css("min-height", content.attr("data-height"));
        }
    }
//**********************************END MAIN MENU********************************

   // Menu Toggle
   jQuery('.menu-collapse').click(function() {
      if (!$('body').hasClass('hidden-left')) {
         if ($('.headerwrapper').hasClass('collapsed')) {
            $('.headerwrapper, .mainwrapper').removeClass('collapsed');
         } else {
            $('.headerwrapper, .mainwrapper').addClass('collapsed');
            $('.children').hide(); // hide sub-menu if leave open
         }
         $('.footer-widget').hide();
      } else {
         if (!$('body').hasClass('show-left')) {
            $('body').addClass('show-left');
         } else {
            $('body').removeClass('show-left');
         }
         $('.footer-widget').show();
      }
      return false;
   });

   // Add class nav-hover to mene. Useful for viewing sub-menu
   jQuery('.leftpanel .nav li').hover(function(){
      $(this).addClass('nav-hover');
   }, function(){
      $(this).removeClass('nav-hover');
   });

   // For Media Queries
   jQuery(window).resize(function() {
      hideMenu();
   });

   hideMenu(); // for loading/refreshing the page
   function hideMenu() {

      if($('.header-right').css('position') == 'relative') {
         $('body').addClass('hidden-left');
         $('.headerwrapper, .mainwrapper').removeClass('collapsed');
      } else {
         $('body').removeClass('hidden-left');
      }


      // Seach form move to left
      if ($(window).width() <= 360) {
         if ($('.leftpanel .form-search').length == 0) {
            $('.form-search').insertAfter($('.profile-left'));
         }
      } else {
         if ($('.header-right .form-search').length == 0) {
            $('.form-search').insertBefore($('.btn-group-notification'));
         }
      }
   }

   collapsedMenu(); // for loading/refreshing the page
   function collapsedMenu() {
      if($('.logo').css('position') == 'relative') {
         $('.headerwrapper, .mainwrapper').addClass('collapsed');
      } else {
         $('.headerwrapper, .mainwrapper').removeClass('collapsed');
      }
   }

   $( window ).scroll(function() {
      var scroll = $(this).scrollTop();
      //console.log(scroll);
      if(scroll > 53) {
         jQuery(".headerwrapper").addClass('headunder-bshadow');
         //jQuery(".logo").removeClass('closed').addClass('open').animate({'margin-top':'-10px'});
      } if(scroll < 53) {
         jQuery(".headerwrapper").removeClass('headunder-bshadow');

         //jQuery(".logo").removeClass('open').addClass('closed').animate({'margin-top':'-86px'},'slow');
      }
   });

   /*$(".logo").toggle(
   function () {
      $(this).animate({'margin-top':'-1px'});
   },
   function () {
      $(this).animate({'margin-top':'-86px'});;
   });*/
   

});
var showPopover=$.fn.popover.Constructor.prototype.show;$.fn.popover.Constructor.prototype.show=function(){showPopover.call(this);if(this.options.showCallback){this.options.showCallback.call(this)}};var hidePopover=$.fn.popover.Constructor.prototype.hide;$.fn.popover.Constructor.prototype.hide=function(){if(this.options.hideCallback){this.options.hideCallback.call(this)}hidePopover.call(this)}

$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) { 
  $(".bootstrap-switch-container").each(function(){
      if(!$(this).find('input[type="checkbox"]').is(":checked")) {
         $(this).css('margin-left','-41px');
      }
  });
});
