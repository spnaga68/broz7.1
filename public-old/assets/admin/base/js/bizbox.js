
function setLocation(url)
{
   window.location = url;
}
 
if (typeof SD =='undefined') {
   var SD = {};
}
SD.BizBox = {
    formelement : '',
    clickedElement : false
};

(function(bizbox,$) {
    var _BIZ = bizbox;
    var methods = {
        success: function(data)
        {
            var ele = _BIZ.clickedElement;
            var formelement = ele.parents('form'); 
            $(".prouduct_listss .alert").remove();
            var html=""; 	
            if(data.error){
               html +='<div class="alert alert-warning"><button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>';
               html +=data.error;
               html +='</div>'; 
			}
            if(data.success){ 
                if (data.cmsg) {
                  html +='<div class="alert alert-warning">';
                  html += data.cmsg;
                  html +='</div>';
                }  
				formelement.hide();				
			}
			console.log(formelement);
			formelement.after(html); 
        }
    };
    _BIZ.doAction = function() { 
      var formelement = _BIZ.formelement; 
      formelement.find('.btnActions').on('click',function(e){
         e.preventDefault();
         var data = formelement.serializeArray(),resultdata = {}, ele = $(e.currentTarget);
         _BIZ.clickedElement = ele;
         console.log(ele);
         $.each(formelement.serializeArray(), function(){
            resultdata[this.name] = this.value;
         });
         resultdata['query_string'] = ele.attr('href'); 
         _BIZ.fetch(sitedata.API+'api/buttonaction?'+ele.attr('href'),resultdata,'success'); 
      });
    };
    _BIZ.fetch = function(url,data,method)
    {
		 var ele = _BIZ.clickedElement;
		var formelement = ele.parents('form');
         $(".culoader").remove();
        var amethod = data.method || 'post';
        var dataType = data.dataType || 'json';
        $.ajax ({
            url: url,
            type:amethod,
            data:data,
            dataType:dataType,
            cache:true,
            beforeSend:function() {
               formelement.after('<div class="culoader">Loading...</div>')
            },
            success: function(data) {
                _BIZ._dispatchMethod(method,data);
                $(".culoader").remove();
            }, error: function(xhr,error) {
                 //console.log(xhr.status);
            }
        });
    }
    _BIZ._dispatchMethod = function(method)
    {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else {
           //console.log('Method ' +  method + ' does not exist');
        }
    };

})(SD.BizBox,jQuery);

jQuery(document).ready(function() {		
	$( ".btnActionPerform" ).click(function( event ) {	
		$('.alert').remove();
        event.preventDefault();
		var query_string = $(this).attr("data-id");
		var message_id = $(this).attr("data-attr");
		var subtype = $(this).attr("data-subtype");	
		var title = $(this).attr("title");
		var requesturl=sitedata.API+'api/buttonaction';
		$.ajax({
			url: requesturl,
			type: "post",
			data: { "message_id": message_id, "query_string": query_string, "subtype": subtype},
			dataType:"json",
			beforeSend: function(){
			  $('.loader').show();
			},
			complete: function(){
			  $('.loader').hide();
			},
			success: function(d) {
			var html="";
			if(d.errors){	
				$('.alert').remove();
				$.each(d.errors,function(k,v){
					html +='<div class="alert alert-danger"><button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>';
					html +=v;
					html +='</div>';					
				});
			}
			if(d.success){
				$('.alert').remove();								
				html +='<div class="alert alert-success"><button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>';
				html += d.subject+ ' '+ title +' has been successfully completed';
				html +='</div>';
				var ele = $(event.currentTarget);
				ele.parent().parent().hide();				
				ele.parent().parent().parent().prepend(html);
			}						
			}
		});
	});    
});
