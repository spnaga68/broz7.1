<!DOCTYPE html>
<html>
<head>
<script type="text/javascript" src="//code.jquery.com/jquery-latest.js"></script>

</head>
<body>
<div id="main">
    <div class="my-form">
        <form role="form" method="post">
            <p class="text-box">
                <label for="box1"> <span class="box-number"></span></label>
               
                <a class="add-box" href="#">Add More</a>
            </p>
            <p><input type="submit" value="Submit"  id="box1"/></p>
			
			<?php 
			
			
			$a='boxes';
			if(isset($_POST[$a])){
				echo json_encode( $_POST[$a]);
				
				
				
				
				}
				
				
				?>

        </form>
    </div>
</div>
<script type="text/javascript">
jQuery(document).ready(function($){
    $('.my-form .add-box').click(function(){
        var n = $('.text-box').length ;
        
        var box_html = $('<p class="text-box"><label for="box' + n + '"> <span class="box-number"></span></label> <input type="text" name="boxes[]" value="" id="box' + n + '" /> </p>');
        box_html.hide();
        $('.my-form p.text-box:last').after(box_html);
        box_html.fadeIn('slow');
        return false;
    });
	$('.my-form .add-box').click(function(){
        var n = $('.text-box').length ;
        
        var box_html = $('<p class="text-box"><label for="box' + n + '"> <span class="box-number"></span></label> <input type="text" name="boxes[]" value="" id="box' + n + '" /> </p>');
        box_html.hide();
        $('.my-form p.text-box:last').after(box_html);
        box_html.fadeIn('slow');
        return false;
    });
    
});
</script>
</body>
</html>




<?php
/*
$('.my-form').on('click', '.remove-box', function(){
        $(this).parent().css( 'background-color', '#FF6C6C' );
        $(this).parent().fadeOut("slow", function() {
            $(this).remove();
            $('.box-number').each(function(index){
                $(this).text( index + 1 );
            });
        });
        return false;
    });
*/
?>