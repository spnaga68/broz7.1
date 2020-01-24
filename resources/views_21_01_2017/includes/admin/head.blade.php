<head>
    <meta charset="utf-8" />
    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! Twitter::generate() !!}
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <?php /* echo app_path().'<br>'.base_path().'<br>'.config_path().'<br>'.database_path().'<br>'.public_path().'<br>'.storage_path(); */ ?>
    <script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/jquery-1.11.1.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/custom.js') }}"></script>
    <script type="text/javascript">var currentlanguage = '<?php echo $currentlanguage;?>'; </script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/admin.js') }}"></script>
    <!-- Fonts -->
    <!-- Styles -->
    <link href="{{ URL::asset('assets/admin/base/css/style.css') }}" media="all" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('assets/admin/base/css/bootstrap.min.css') }}" media="all" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('assets/admin/base/css/jquery-ui-1.10.3.css') }}" media="all" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('assets/admin/base/css/select2.css') }}" media="all" rel="stylesheet" type="text/css" />
    <?php if(App::getLocale()=='ar'){ ?>
        <link href="{{ URL::asset('assets/admin/base/css/style.arabic.css') }}" media="all" rel="stylesheet" type="text/css" /> 
        <link href="{{ URL::asset('assets/admin/base/css/style-arabic-resp.css') }}" media="all" rel="stylesheet" type="text/css" />
    <?php }else { ?>
        <link href="{{ URL::asset('assets/admin/base/css/style.default.css') }}" media="all" rel="stylesheet" type="text/css" /> 
        <link href="{{ URL::asset('assets/admin/base/css/style-resp.css') }}" media="all" rel="stylesheet" type="text/css" />
    <?php } ?>
</head>

<link rel="shortcut icon" href="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/favicon/16_16/'.Session::get("general")->favicon.'?'.time()); ?>" >
<script type="text/javascript">
    $(document).ready( function() {
        if ( $(window).width() < 900) {
            $('.menu-collapse').click(function(){
                $('div.mainwrapper').removeClass('collapsed');
            });
        }
    });
</script>
