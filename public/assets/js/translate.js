/*  written by  : Pradeep shyam */
if(typeof SD =='undefined') {
    var SD = {};
}
SD.translate = {
    localeset : {}
};

(function(translate,$) {
    var _T = translate;
    var methods = {
        responseT: function(data)
        { 
			
			//$('.dataTables_empty').html('');
            _T.localeset = data;
            _T.bindElements(data);
        }
    };

    _T.loadTranslate = function()
    {
         //_T.fetch(baseUrl+'api/translate/load',{dataType:'json'},'responseT');
         _T.fetch('http://192.168.0.112:8282/translate',{dataType:'json'},'responseT');
    }

    _T.get = function(text,obj)
    {
        var object = obj || {};
        if(typeof _T.localeset[text] !='undefined') {
            return _T.format(_T.localeset[text],object);
        }
        return _T.format(text,object);
    }
    _T.bindElements = function()
    {
        $('body').find('.translate').each(function(){
            $(this).text(_T.get($(this).text()));
        });
        
    }

    _T.fetch = function(url,data,method)
    {
		var token = $('input[name=_token]').val();
        var amethod = data.method || 'post';
        var dataType = data.dataType || 'json';
        $.ajax ({
            url: url,
            headers: {'X-CSRF-TOKEN': token},
            type:amethod,
            data:data,
            dataType:dataType,
            cache:true,
            beforeSend:function() {

            },
            success: function(data) {
                _T._dispatchMethod(method,data);
            }, error: function(xhr,error) {
                 //console.log(xhr.status);
            }
        });
    }

    _T.format = function(str)
    {
        if(typeof arguments[1] == 'object') {

            var json = arguments[1];
            return str.replace(/:(\w+)/g, function(match) {
                return typeof json[match] != 'undefined'
                    ? json[match]
                    : match;
            });
        }
        return str;

    }
    _T._dispatchMethod = function(method)
    {
		
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else {
           //console.log('Method ' +  method + ' does not exist');
        }
    };

})(SD.translate,jQuery);

$(document).ready(function(){
	
    SD.translate.loadTranslate();
});
