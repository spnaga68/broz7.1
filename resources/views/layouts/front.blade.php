<!DOCTYPE html>
<?php //print_r("expression");exit(); ?>
@include('includes.front.'.Session::get("general")->theme.'.head_front')
@yield('content')
@include('includes.front.'.Session::get("general")->theme.'.footer_front')
</body>
</html>
