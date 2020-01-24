<html lang="en" ng-app="app" ng-controller="titleCtrl">
<head>
	{!! SEOMeta::generate() !!}
	{!! OpenGraph::generate() !!}
	{!! Twitter::generate() !!}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<meta name="description" content="Free Web tutorials">
	<meta name="keywords" content="HTML,CSS,XML,JavaScript">
    <meta name="author" content="Hege Refsnes">
	<meta name="csrf-token" content="{{ csrf_token() }}" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta name="viewport" content="width=device-width; initial-scale=1.0" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/> <!--320-->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<title>Oddappz - OnDemand Delivery App</title>
	<?php $general = Session::get("general"); $social = Session::get("social"); $email = Session::get("configemail"); $languages = Session::get("languages"); ?>
	<link rel="shortcut icon" href="<?php echo url('/assets/front/'.$general->theme.'/images/favicon/16_16/'.Session::get("general")->favicon.''); ?>">
	<!-- Bootstrap -->
	<?php if(App::getLocale()=='ar') {  ?>
		<link href="{{ URL::asset('assets/front/'.$general->theme.'/css/bootstrap.min.css') }}" media="all" rel="stylesheet" type="text/css" />
		<link href="{{ URL::asset('assets/front/'.$general->theme.'/css/bootstrap-rtl.css') }}" media="all" rel="stylesheet" type="text/css" />
		<link href="{{ URL::asset('assets/front/'.$general->theme.'/css/style-rtl.css') }}" media="all" rel="stylesheet" type="text/css" />
	<?php } else { ?>
		<link href="{{ URL::asset('assets/front/'.$general->theme.'/css/bootstrap.min.css') }}" media="all" rel="stylesheet" type="text/css" />
		<link href="{{ URL::asset('assets/front/'.$general->theme.'/css/style.css') }}" media="all" rel="stylesheet" type="text/css" />
	<?php } ?>
	<link href="{{ URL::asset('assets/front/'.$general->theme.'/css/font.css') }}" media="all" rel="stylesheet" type="text/css" />
	<link href="{{ URL::asset('assets/front/'.$general->theme.'/fonts/flaticon/flaticon.css') }}" rel="stylesheet" />
	<link href="{{ URL::asset('assets/front/'.$general->theme.'/css/res_menu.css') }}" rel="stylesheet" />
	<link href="{{ URL::asset('assets/front/'.$general->theme.'/css/responsive.css') }}" rel="stylesheet" />
</head>
<body>
	<section class="store_item_list">
		<div class="container">
			<div class="cms_pages">
				<div class="stor_title">
					<h1>{{ ucfirst($cmsinfo[0]->title) }}</h1>
				</div>
				<?php echo $cmsinfo[0]->content; ?>
			</div>
		</div>
	</section>
</body>
</html>