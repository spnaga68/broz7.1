<!DOCTYPE html>
@include('includes.front.'.Session::get("general")->theme.'.head')
@yield('content')
@include('includes.front.'.Session::get("general")->theme.'.footer')
</body>
</html>
