var sitedata = {};
function setLocation(url)
{
   window.location = url;
}
 
if (typeof YL =='undefined') {
   var YL = {};
}

(function(YL){
    YL.options = {
        url :''
    }
    YL.init = function (options)
    {
        this.options = $.extend(this.options,options);
        return this;
    }
    YL.toParams  = function(searchUrl) {
        var result = {};
        if(searchUrl == '')
            return result;
        var queryString = searchUrl.substr(1);
        var params = queryString.split("&");
        $.each(params, function(index, param){
            var keyPair = param.split("=");
            var key = keyPair[0];
            var value = keyPair[1];
            if(result[key] == undefined)
                result[key] = value
            else{
                if(result[key] instanceof Array) //current var is an array just push another to it
                    result[key].push(value)
                else{ //duplicate var, then it must store as an array
                    result[key] = [result[key]]
                    result[key].push(value)
                }
            }
        });
        return result;
    };
    YL.parseURL = function(url) {
        var parser = document.createElement('a'),
            searchObject = {},
            queries, split, i;
        // Let the browser do the work
        parser.href = url;
        // Convert query string to object
        queries = parser.search.replace(/^\?/, '').split('&');
        for( i = 0; i < queries.length; i++ ) {
            split = queries[i].split('=');
            searchObject[split[0]] = split[1];
        }
        return {
            protocol: parser.protocol,
            host: parser.host,
            hostname: parser.hostname,
            port: parser.port,
            pathname: parser.pathname,
            search: parser.search,
            searchObject: searchObject,
            hash: parser.hash
        };
    };
    YL._addVarToUrl = function(url, varName, varValue){
        parseUrl =  YL.parseURL(url);
        var params =  YL.toParams(parseUrl.search);
        params[varName] = varValue;
        return parseUrl.protocol+'//'+parseUrl.host+ parseUrl.pathname + "?" + jQuery.param(params);
    };
    YL.addVarToUrl = function(varName, varValue)
    {
        YL.options.url = YL._addVarToUrl(YL.options.url ,varName, varValue);
        return YL.options.url ;
    };

}(YL));

YL.Block = {};
(function(YL) {
    YL.options = {
        blocks :{}
    }
    YL.init = function (options)
    {
        this.options = $.extend(this.options,options);
        return this;
    }
    YL.load = function(url,element,callback)
    {
        var ele = $(document.body).find("#"+element);
        ele.html('Loading...');
        var uri = this.options.blocks[url];
        //console.log(uri);
        ele.load(uri,function(response,status,xhr){
            if(status == "error") {
              //Something went wrong, have your error fallback code here
            }
        });
        if (typeof callback!='undefined') {
            callback.apply(this);
        }

    }
}(YL.Block));


var currentFieldLanguage = currentlanguage; 
YL.Language = {};
(function(YL) {
    YL.fieldchange = function(languageid)
    {
        $(".translatable_field").hide();
        $(".language-"+languageid).show();
        currentFieldLanguage = languageid;
        //$(".translatable_field select").
    }
}(YL.Language));
YL.CountryCode = {};
(function(YL) {
   var hiddenelement;
   var hasHiddenelement = false;
   YL.choose = function(ele,parentid,hiddenelementname)
   {
      var hdelementname = hiddenelementname || parentid;
      var element = $(ele);

      var parentelement = $("."+parentid);
      var imgelement = element.attr('data-countryname');
      parentelement.find('ul li').removeClass('active');
      element.parent().addClass('active');
      if (element.attr('data-flag')!="") {
         imgelement = '<img src="'+element.attr('data-flag')+'" class="drop-flag-img"/> ';
      }
      if ($('input.mobile_hidden',parentelement).length <= 0) {
         hiddenelement = $('<input />');
         hiddenelement.attr({id:parentid+'_element',"class":"mobile_hidden",'type':'hidden','name':hdelementname,'value':element.attr('data-id')});
         parentelement.append(hiddenelement);
         hasHiddenelement = true;
      } else {
         $('input.mobile_hidden',parentelement).val(element.attr('data-id'));
      }
      if ($('input.country_id_hidden',parentelement).length <= 0) {
         hiddenelement = $('<input />');
         hiddenelement.attr({id:parentid+'_element',"class":"country_id_hidden",'type':'hidden','name':hdelementname,'value':element.attr('data-countryid')});
         parentelement.append(hiddenelement);
         hasHiddenelement = true;
      } else {
               $('input.country_id_hidden',parentelement).val(element.attr('data-countryid'));
      }
      parentelement.find('button').html(imgelement+'<span class="caret"></span>');
   }
}(YL.CountryCode));

YL.Common = {};

(function(YL) {
   YL.confirm = function(event, message)
   {
      event.preventDefault();
      var ele = $(event.currentTarget);
      if (confirm(message)) {
        window.location.href = ele.attr('href');
      } else {
         return false;
      }
   };
   YL.popover = function(element, popfor) {
      var popmessage = {
         'database_model' :'Test popover messsage',
      }
      element.popover({content:popmessage[popfor]});
   };

   YL.modal = function(element,targetelement)
   {
         var tgele = targetelement || $('body');
        $("#"+element+'_modal').remove();
        var template = '<div class="modal fade bs-example-modal" id="'+element+'_modal" tabindex="-1" role="dialog"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button><h4 class="modal-title">Loading...</h4></div><div class="modal-body"></div></div></div></div>';
        tgele.prepend(template);
        return {hide:function() {
			$("#"+element+'_modal').modal('hide');
		}};
        //$('#'+element).modal()
   }

}(YL.Common));


YL.Autocomplete = {};

(function(YL,translate) {
    var cache;
    YL.fvals = {};
    YL.ele = {};
    YL.data = {};
    YL.type = 'multiple';
    YL.split = function ( val ) {
      return val.split( /,\s*/ );
    }
    YL.extractLast = function ( term ) {
      return YL.split( term ).pop();
    }
    YL.setType = function(type) {
        YL.type = type || 'multiple';
        return this;
    }

    YL._getType = function() {
        return YL.type;
    }
    YL.ajax  = function(ele,url,data)
    {
         $(".autocompleteinput").on("click","span.tag a",function(e){
            var id = $(this).data('id');
            var input= $(e.currentTarget.parentElement.parentElement.parentElement).prev('input');
            var currentval = new Array(), i = 0;

            $(this).parent().fadeOut(200, function() {
                $(this).remove();
            });
            $.each(input.val().split(","),function(k,v){
                if (v != id) {
                    currentval[i] = v;
                    i++;
                }
            });
            input.val(currentval.join(","));
            YL.fvals[YL.ele[input.attr('name')]] = input.val();

        });
        $(".autocompleteinput").click(function(){
            $("input",this).focus();

        });
        YL.data[ele] = data || {};
        var element = YL.ele[ele] = ele;
        $("#"+ele)
          // don't navigate away from the field on tab when selecting an item
          .bind( "keydown", function( event ) {
            if ( event.keyCode === $.ui.keyCode.TAB &&
                $( this ).autocomplete( "instance" ).menu.active ) {
              event.preventDefault();
            }
          })
          .autocomplete({
            source: function( request, response ) {
                var term = request.term;
                /*if ( term in cache ) {
                  response( cache[ term ] );
                  return;
                }*/
                var finaldata = $.extend(YL.data[ele],request);
                if (YL.data[ele].dependant) {
                    finaldata[YL.data[ele].dependant] = YL.getSelectedItem(YL.data[ele].dependant);
                }
                console.log(finaldata);
                $.ajax({
                    url: url,
                    data:finaldata,
                    type:'get',
                    dataType:'json',
                    beforeSend:function(){
                        Pace.start();
                        $(".autocomplete-error").remove();
                    },
                    success:function(res){
                        ajaxsuccesscallback(res);
                        //cache[term] = res;
                        if (res.datas.length <= 0) {
                            $("input[name='"+YL.ele[element]+"']").parents('.autocomplete-container').after('<span class="autocomplete-error">'+translate.get('No Results found')+'</span>');

                        }
                        if (!res.error) {
                            response( res.datas );
                        }
                        Pace.stop();

                    }
                });
            },
            search: function() {
              // custom minLength
              var term = YL.extractLast( this.value );
              var lengh = $(this).attr('data-length') ? parseInt($(this).attr('data-length')):2;
              if ( term.length < lengh ) {
                return false;
              }
            },
            focus: function() {
              // prevent value inserted on focus
              return false;
            },
            select: function( event, ui ) {
               var fieldtype = $(this).data('type') ? $(this).data('type') : YL._getType();
                if (fieldtype =='multiple') {
                    var hiddenele = $("input[name='"+YL.ele[element]+"']");
                     var terms = YL.split( this.value );
                     var existingval = YL.split(hiddenele.val());
                     if ($.inArray(ui.item.value, existingval) >= 0) {
                        //console.log(ui.item.value);
                        this.value = '';
                        var eele = $(this).parents('.autocompleteinput').find('.tag_element_'+ui.item.value);
                        if(eele.length) {
                           eele.flash('#32D7BF', 100, 3);
                        }
                        return false;
                     }
                    $(this).before("<span class='tag tag_element_"+ui.item.value+"'>" + ui.item.label + " <a href=\"javascript:;\" data-id='"+ui.item.value+"' title=\"Remove\">x</a></span>");
                    var values = YL.split(hiddenele.val());
                    // remove the current input
                    terms.pop();
                    if (values == '') {
                        values = new Array();
                    }
                    // add the selected item
                    terms.push( ui.item.label );
                    values.push( ui.item.value );
                    var fva = values.join(",");
                    hiddenele.val(fva);
                    YL.fvals[YL.ele[element]] = fva;

                    this.value = '';
                    return false;
                }

                if (fieldtype =='single') {
                    $(this).parent().find('.tag').remove();
                    $(this).before("<span class='tag'>" + ui.item.label + " <a href=\"javascript:;\" data-id='"+ui.item.value+"' title=\"Removing tag\">x</a></span>");
                    var hiddenele = $("input[name='"+YL.ele[element]+"']");
                    var terms = YL.split( this.value );
                    terms.pop();
                    terms.push( ui.item.label );
                    hiddenele.val(ui.item.value);
                    YL.fvals[YL.ele[element]] = ui.item.value;
                    this.value = '';
                    return false;
                }


            }
        });
    };
    YL.template = function(template) {
      return
    };
    YL.getSelectedItem = function(id) {
         if($("input[name='"+id+"']").length) {
           return $("input[name='"+id+"']").val();
         }
        return YL.fvals[id];
    }
}(YL.Autocomplete,YL.translate));


function ajaxsuccesscallback(response)
{
   if (response.redirectLogin) {
      alert('Please login to continue');
      location.reload();
   }
}
$(document).ajaxSuccess(function( event, xhr, settings ) {
   if (xhr.responseText) { 
         try {
            var json = $.parseJSON(xhr.responseText);
            if (json && json.redirectLogin) {
               alert('Please login to continue');
               location.reload();
            }
         }catch(e) {

         }
   }
    if (xhr.responseJSON) {
        ajaxsuccesscallback(xhr.responseJSON);
    } 
});

$.fn.flash = function (highlightColor, duration, iterations) {
    var highlightBg = highlightColor || "#FFFF9C";
    var animateMs = duration || 1500;
    var originalBg = this.css('backgroundColor');
    var flashString = 'this';
    for (var i = 0; i < iterations; i++) {
        flashString = flashString + '.animate({ backgroundColor: highlightBg }, animateMs).animate({ backgroundColor: originalBg }, animateMs)';
    }
    eval(flashString);
}
 

$.fn.bindWithDelay = function( type, data, fn, timeout, throttle ) {

    if ( $.isFunction( data ) ) {
        throttle = timeout;
        timeout = fn;
        fn = data;
        data = undefined;
    }

    // Allow delayed function to be removed with fn in unbind function
    fn.guid = fn.guid || ($.guid && $.guid++);

    // Bind each separately so that each element has its own delay
    return this.each(function() {

        var wait = null;

        function cb() {
            var e = $.extend(true, { }, arguments[0]);
            var ctx = this;
            var throttler = function() {
                wait = null;
                fn.apply(ctx, [e]);
            };

            if (!throttle) { clearTimeout(wait); wait = null; }
            if (!wait) { wait = setTimeout(throttler, timeout); }
        }

        cb.guid = fn.guid;

        $(this).bind(type, data, cb);
    });
};
 
var _ksl = 0;
var popupnavs = new Array();

var popupBlockerChecker = {
   check: function(popup_window){
       var _scope = this;
       if (popup_window) {
           if(/chrome/.test(navigator.userAgent.toLowerCase())){
               setTimeout(function () {
                   _scope._is_popup_blocked(_scope, popup_window);
                },200);
           }else{
               popup_window.onload = function () {
                   _scope._is_popup_blocked(_scope, popup_window);
               };
           }
       }else{
           _scope._displayError();
       }
   },
   _is_popup_blocked: function(scope, popup_window){
       if ((popup_window.innerHeight > 0)==false){ scope._displayError(); }
   },
   _displayError: function(){
      console.log("Popup Blocker is enabled! Please add this site to your exception list.");
   }
};
function popup(url)
{
   if (popupnavs[_ksl]) {      
      popupnavs[_ksl].focus()
   } else {
      popupnavs[_ksl] = window.open(url,'add_product','width='+screen.width+',height='+screen.height+',fullscreen=yes,scrollbars=yes,status,resizable');
      popupBlockerChecker.check(popup);
      _ksl++;
   }
}


function encode_base64( what )
{
    var base64_encodetable = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    var result = "";
    var len = what.length;
    var x, y;
    var ptr = 0;

    while( len-- > 0 )
    {
        x = what.charCodeAt( ptr++ );
        result += base64_encodetable.charAt( ( x >> 2 ) & 63 );

        if( len-- <= 0 )
        {
            result += base64_encodetable.charAt( ( x << 4 ) & 63 );
            result += "==";
            break;
        }

        y = what.charCodeAt( ptr++ );
        result += base64_encodetable.charAt( ( ( x << 4 ) | ( ( y >> 4 ) & 15 ) ) & 63 );

        if ( len-- <= 0 )
        {
            result += base64_encodetable.charAt( ( y << 2 ) & 63 );
            result += "=";
            break;
        }

        x = what.charCodeAt( ptr++ );
        result += base64_encodetable.charAt( ( ( y << 2 ) | ( ( x >> 6 ) & 3 ) ) & 63 );
        result += base64_encodetable.charAt( x & 63 );

    }

    return result;
}

function decode_base64( what )
{
    var base64_decodetable = new Array (
        255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255,
        255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255,
        255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255,  62, 255, 255, 255,  63,
         52,  53,  54,  55,  56,  57,  58,  59,  60,  61, 255, 255, 255, 255, 255, 255,
        255,   0,   1,   2,   3,   4,   5,   6,   7,   8,   9,  10,  11,  12,  13,  14,
         15,  16,  17,  18,  19,  20,  21,  22,  23,  24,  25, 255, 255, 255, 255, 255,
        255,  26,  27,  28,  29,  30,  31,  32,  33,  34,  35,  36,  37,  38,  39,  40,
         41,  42,  43,  44,  45,  46,  47,  48,  49,  50,  51, 255, 255, 255, 255, 255
    );
    var result = "";
    var len = what.length;
    var x, y;
    var ptr = 0;

    while( !isNaN( x = what.charCodeAt( ptr++ ) ) )
    {
        if( x == 13 || x == 10 )
            continue;

        if( ( x > 127 ) || (( x = base64_decodetable[x] ) == 255) )
            return false;
        if( ( isNaN( y = what.charCodeAt( ptr++ ) ) ) || (( y = base64_decodetable[y] ) == 255) )
            return false;

        result += String.fromCharCode( (x << 2) | (y >> 4) );

        if( (x = what.charCodeAt( ptr++ )) == 61 )
        {
            if( (what.charCodeAt( ptr++ ) != 61) || (!isNaN(what.charCodeAt( ptr ) ) ) )
                return false;
        }
        else
        {
            if( ( x > 127 ) || (( x = base64_decodetable[x] ) == 255) )
                return false;
            result += String.fromCharCode( (y << 4) | (x >> 2) );
            if( (y = what.charCodeAt( ptr++ )) == 61 )
            {
                if( !isNaN(what.charCodeAt( ptr ) ) )
                    return false;
            }
            else
            {
                if( (y > 127) || ((y = base64_decodetable[y]) == 255) )
                    return false;
                result += String.fromCharCode( (x << 6) | y );
            }
        }
    }
    return result;
}

var globalloader = {
	show: function(text)
	{ 
        text || (text = 'Loading...');
		this.hide();
		var div = $("<div class='loader-com'>"+text+"</div>"); 
		$('body').append(div);
	},
	hide: function()
	{
		$('body').find('.loader-com').remove();
	}
}

function goToByScroll(id){
      // Remove "link" from the ID
    id = id.replace("link", "");
      // Scroll
    $('html,body').animate({
        scrollTop: $("#"+id).offset().top},
        'slow');
}
function HideButton(hideparam,showparam) {
    $("."+hideparam).hide();
    $("."+showparam).show();
}
