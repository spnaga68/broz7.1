<!DOCTYPE html>
<html lang="en" ng-app="app" ng-controller="titleCtrl" class="h-100">

<head>
 
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta http-equiv="Content-type" content="text/html; charset=utf-8">
      <meta name="description" content="Get the best service and offers from BROZ Community. BROZ Community consists of Groceries, Restaurants, Laundry, Salon for Men, Women and Kids. We strive in bringing the best shopping experience. Be a part of BROZ Community by shopping with us.">
      <meta name="keywords" content="BROZ,Grocery,Online shopping,Free delivery,Dubai,Restaurant,Barber,Pizza,Laundry,Broz community,JVC,Online shopping UAE">
      <meta name="author" content="Broz">
      <meta name="csrf-token" content="{{ csrf_token() }}" />
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
      <meta content="width=device-width, initial-scale=1.0" name="viewport">
      <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
      <!--320-->
      <meta name="apple-mobile-web-app-capable" content="yes">
      <meta name="apple-mobile-web-app-status-bar-style" content="black"/>

 
  <link rel="stylesheet" href="assets/front/broz/css/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="assets/front/broz/css/mobile.css">

  
  <!-- fontawesome -->
  <link rel="stylesheet" href="assets/front/broz/css/fontawesome/css/fontawesome.css">
  <link rel="stylesheet" href="assets/front/broz/css/fontawesome/css/brands.css">
  <link rel="stylesheet" href="assets/front/broz/css/fontawesome/css/solid.css">
      <link rel="shortcut icon" href="/assets/front/broz/images/favicon/16_16/favicon.png">


  <title>BROZ Community</title>
</head>

<body class="h-100">
  <!-- nav -->
  <!-- <div id="page-container"> -->
  <header>
    <nav class="navbar fixed-top navbar-light bg-light navbar-expand justify-content-between shadow-sm">
      <div class="container">
        <a class="navbar-brand" href="{{ URL::to('') }}"><img src="assets/front/broz/images/logo/logo512.png" alt="" width="50" height="50"></a>

          <ul class="navbar-nav justify-content-end">
                    <li class="nav-item   {{ Request::segment(1) === '' ? 'activeline' : null }}">
                        <a class="nav-link"href="{{ URL::to('') }}" id="home">HOME</a></li>

                    <li class="nav-item {{ Request::segment(1) === 'outlets' ? 'activeline' : null }}">
                        <a class="nav-link"href="{{ URL::to('/outlets') }}" ></i> OUTLET</a>
                    </li>
                    <li class="nav-item {{ Request::segment(1) === 'promotion' ? 'activeline' : null }}">
                        <a class="nav-link"href="{{URL::to('/promotion')}}"> OFFER</a>
                    </li>
                    <?php if(Session::get('user_id')) {?>
                    <li class="nav-item {{ Request::segment(1) === 'profile' ? 'activeline' : null }}">
                        <a class="nav-link"href="{{URL::to('/profile')}}"> PROFILE</a>
                    </li>
                    <?php }else{?>
                    <li class="nav-item {{ Request::segment(1) === 'customerLogin' ? 'activeline' : null }}">
                        <a class="nav-link"href="{{URL::to('/customerLogin')}}"> LOGIN</a>
                    </li>    
                      <?php } ?>                 
                </ul>

      </div>
     @if (Session::has('error'))
      <div class="admin_sucess_common">
        <div class="admin_sucess">
          <div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('error') }}</div>
        </div>
      </div>
      @endif 

      @if (Session::has('message'))
      <div class="admin_sucess_common">
        <div class="admin_sucess">
          <div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
        </div>
      </div>
      @endif


    </nav>

  </header>
  
  
  <!--  <script src="{{ URL::asset('assets/front/broz/js/jquery.min.js') }}"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
    integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous">
  </script>
  
   <script src="assets/front/broz/css/bootstrap/js/bootstrap.js"></script>
  <script src="assets/front/broz/js/google.js"></script>
<script src="assets/front/broz/js/active.js"></script>
 -->





