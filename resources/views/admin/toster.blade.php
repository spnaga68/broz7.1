<html>
  <!--   <head>
        <title>ccPicker demo</title>
        <meta charset="UTF-8"></meta>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"></meta>
		<script src="https://code.jquery.com/jquery-1.12.4.min.js" type="text/javascript"></script>
        <script src="js/jquery.ccpicker.js" type="text/javascript"></script>
		<link rel="stylesheet" type="text/css" href="css/jquery.ccpicker.css">
		
    </head> -->
    <body>
        <div>
            <form id="mainForm" action="dialer.html" method="POST">
			<div class="input">
                <input type="text" id="phoneField1" name="phoneField1" class="phone-field"/>
			</div>
			<br/>			
			
            </form>
        </div>
    </body>
</html>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>


	<script src="https://code.jquery.com/jquery-1.12.4.min.js" type="text/javascript"></script>

	<script src="{{ URL::asset('assets/js/jquery.ccpicker.min.js') }}"></script>

	<link rel="stylesheet" type="text/css" href="{{ URL::asset('css/jquery.ccpicker.css') }}">

<script>
	$( document ).ready(function() {
		//alert("fggdfg");return false;
		 $("#phoneField1").CcPicker();
		// $("#phoneField1").CcPicker("setCountryByCode","es");
		// $("#phoneField3").CcPicker({"countryCode":"us"});
		// $("#phoneField5").CcPicker();
		/*$("#phoneField1").on("countrySelect", function(e, i){
												alert(i.countryName + " " + i.phoneCode);
											});*/
	});
</script>