(function($){
        var totalList = 0;
        var datepicker = false;
        A = $;
        this.app = {};
        A.init = function(app)
        {
            this.app = A.extend (app,{
                toParams :function(searchUrl) {
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
                },
                parseURL : function(url) {
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
                },

            });
            $(document.body).on('click', '#'+ this.app.container+' .headings a', A.Sort);
            $(document.body).on('click', '#'+ this.app.container+' .pagination a', A.Paging);
            $(document.body).on('click','#'+ this.app.container+' .input-append.date',A.loadDatepicker);
            $(document.body).on('click','#'+ this.app.container+' tr.item_columns',A.RowEdit);

           // $('#'+ this.app.container+' .headings a').on('click', this, A.Sort);
           // $('#'+ this.app.container+' .pagination a').on('click', this, A.Paging);
            //$('#'+ this.app.container+' .pagination a').on('click', this, A.RowEdit);
        };

        A.loadDatepicker = function(ele)
        {
            var target = $(ele.target);
            if(!datepicker) {
                target.datepicker({
                        autoclose: true,
                        todayHighlight: true
                    });
                datepicker = true;
            }
        }

        A.checkbox = function(ele)
        {
            var target = $(ele.target);
            if(target.is(":checked"))
            {
                alert('checked');
            }
        }

        A.limit = function(ele)
        {
            if(ele && ele.name){
                A.reloadUrl(A.addVarToUrl(ele.name,ele.value));
            }
        }

        A.massAction = function(options){
            var _cur = A.app;
            var $this = this;
            var inputelement;
            var _op = A.extend({items:{},formid:_cur.container+'_massaction-form',submitId:_cur.container+'_massaction-submit',confirmtext:'',elementname:'',lists:{}},options);
            var init =  {
                prepare:function()
                {
                    $(document.body).on('change', '#'+ _op.formid+' select', this.selectChange);
                    $(document.body).on('click', '#'+ _op.formid+' input[name="'+_cur.container+'_massaction-submit"]', this.submitform);
                    $(document.body).on('click', '#'+ _cur.container+' .massaction_check', this.checkbox);
                },
                setItems : function(items)
                {
                    _op.items = items;
                },
                selectChange:function(ele)
                {
                    if(!ele.target.value) {
                        return this;
                    }
                    var item = _op.items[ele.target.value];
                    $('#'+ _op.formid).attr('action',item.url);
                    _op.confirmtext = item.confirm;
                },
                setList:function(total)
                {
                },
                checkbox:function(ele)
                {
                    var target = $(ele.target);
                    if(target.hasClass('allcheck'))
                    {
                        if(target.is(":checked")) {
                            $('#'+ _cur.container+' tr.item_columns input[type="checkbox"]').attr('checked',true);
                        } else {
                            $('#'+ _cur.container+' tr.item_columns input[type="checkbox"]').attr('checked',false);
                        }
                    }
                    $("#"+_cur.container+' tr.item_columns input[type="checkbox"]').each(function() {
                        if($(this).is(":checked")) {
                            $(this).parent().parent().parent().addClass('row_selected');
                        } else {
                            $(this).parent().parent().parent().removeClass('row_selected');
                        }
                    });

                    totalList = $('#'+ _cur.container+' tr.item_columns input[type="checkbox"]:checked').length;
                    if(totalList >= 0 && totalList < $('#'+ _cur.container+' tr.item_columns input[type="checkbox"]').length) {
                        $('#'+ _cur.container+' tr.headings #checkbox0').attr('checked',false);
                    }
                    if(totalList > 0 && totalList == $('#'+ _cur.container+' tr.item_columns input[type="checkbox"]').length) {
                        $('#'+ _cur.container+' tr.headings #checkbox0').attr('checked',true);
                    }
                },
                setElementName: function(name)
                {
                    _op.elementname = name;
                },
                submitform:function(ele)
                {
                    ele.preventDefault();
                    var checkedlength = totalList;
                    var values = [];
                    $('#'+ _cur.container+' tr.item_columns input[type="checkbox"]:checked').each(function(){
                        values.push($(this).val());
                    });
                    if(inputelement) {
                        inputelement.remove();
                    }
                    inputelement =  document.createElement('input');
                    inputelement.setAttribute('type', 'hidden');
                    inputelement.setAttribute('name', _op.elementname);
                    inputelement.setAttribute('value',values.join(","));
                    $(ele.target).parents('form').append(inputelement);
                    if(checkedlength <= 0) {
                        alert('Please select the items');
                        return false;
                    }
                    var confirms = confirm(_op.confirmtext);
                    if(_op.confirmtext!="") {
                        if(confirms)
                        {
                            $(ele.target).parents('form').submit();
                        }
                    }
                    if(_op.confirmtext=="")
                    {
                        $(ele.target).parents('form').submit();
                    }
                }
            }
            return init;
        }

        A._addVarToUrl = function(url, varName, varValue){
            parseUrl =  this.app.parseURL(url);
            var params =  this.app.toParams(parseUrl.search);
            params[varName] = varValue;
            return parseUrl.protocol+'//'+parseUrl.host+ parseUrl.pathname + "?" + jQuery.param(params);
        };
        A.addVarToUrl = function(varName, varValue)
        {
            this.app.url = A._addVarToUrl( this.app.url,varName, varValue);
            return  this.app.url;
        };

        A.Paging = function(event)
        {
            if(typeof event =='undefined') {
                return false;
            }
            var element = $(event.currentTarget);
            if(element.attr('href')=='#' || element.attr('href')=='javascript:;' || element.attr('href') =='javascript:void(0);') {
                return false;
            }
            if(element.attr('href') ){
                $.app.url = element.attr('href');
                A.reloadUrl( $.app.url);
            }
            event.preventDefault();
            return false;
        };

        A.reloadUrl = function(url)
        {
            totalList = 0;
            var _op = this.app;
            if( _op.useAjax) {

                $.ajax({
                    url:url + (url.match(new RegExp('\\?')) ? '&ajax=true' : '?ajax=true' ),
                    type:'get',
                    dataType: "html",
                    beforeSend:function(){
                         Pace.restart();
                    },
                    success: function (data) {
                         var response = data.replace(/>\s+</g, '><');
                         var IS_JSON = true;
                         try {
                             var json = $.parseJSON(response);
                         }
                         catch(err) {
                             IS_JSON = false;
                         }
                         if(IS_JSON) {

                         } else {
                             var divId = $('#'+ _op.container);
                             divId.html(response);
                         }

                        $(".select2-wrapper").select2({minimumResultsForSearch: -1});
                        /*$('#'+ _op.container+' .input-append.date').datepicker({
                                autoclose: true,
                                todayHighlight: true
                        });*/
                        var sortingth = $("#"+_op.container+' tr.headings th a.list-sortingcolumn').parents('th');
                        var thindex = $("#"+_op.container+' tr.headings th').index(sortingth);
                        $("#"+_op.container+' tr.item_columns').each(function(k,v) {
                            $('td:eq('+thindex+')',this).addClass('sorting_1');
                        });
                        $("#"+_op.container+' tr.headings').each(function(k,v) {
                            $('th:eq('+thindex+')',this).addClass('sorting');
                        });
                        $("#"+_op.container+' input').click( function() {
                            $(this).parent().parent().parent().toggleClass('row_selected');
                        });

                    },
                    error : function(xhr,err)
                    {
                        if(xhr.status == 404) {
                            alert('Ajax Error: Page not found.');
                        }
                        if(xhr.status == 500) {
                            alert('Server Error: Failed on getting response');
                        }
                    }
                });
            } else {
                window.location.href = url;
            }
        }

        A.RowEdit = function(event)
        {
            if(typeof event =='undefined') {
                return false;
            }
            var element = $(event.currentTarget);
            var target = $(event.target);
            if(target.find('input').length || target.find('a').length || target.find('select').length){
                return;
            }
            if(target.parents('td').find('input').length || target.parents('td').find('a').length || target.parents('td').find('select').length) {
                return;
            }
            if(element.attr('title') && element.attr('title')!="#"){
                window.location = element.attr('title');
            }
            event.preventDefault();
            return;
        }

        A.FilterList = function()
        {
            var elements = [];
            $('#'+ this.app.container+' .filter input').each(function(k,v){
                if(v.value && v.value.length) {
                    $ele = v.getAttribute('name')+'='+v.value;
                    elements.push($ele);
                }
            });
            $('#'+ this.app.container+' .filter select').each(function(k,v){
                if(v.value && v.value.length) {
                    $ele = v.getAttribute('name')+'='+v.value;
                    elements.push($ele);
                }
            });
            $filterval = encode_base64(elements.join('&'));
            A.reloadUrl(A.addVarToUrl( this.app.filtervar,$filterval));
        };
        A.ResetFilter = function()
        {
            A.reloadUrl(A.addVarToUrl( this.app.filtervar,''));
        }
        A.Sort = function(event){
            if(typeof event =='undefined') {
                return false;
            }
            var element = $(event.currentTarget);
            if(element.attr('name') && element.attr('title')){
                A.addVarToUrl( $.app.sortvar, element.attr('name'));
                A.addVarToUrl( $.app.dirvar, element.attr('title'));
                A.reloadUrl( $.app.url);
            }
            event.preventDefault();
            return false;
        }

        A.multipleselect = function(id) {
            $("#"+id).select2();
        }
        }(jQuery));


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
 $(window).on('load',function(){
                /*$('.input-append.date').datepicker({
                    autoclose: true,
                    todayHighlight: true
                });*/
            });