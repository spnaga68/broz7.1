    @extends('layouts.app')
    @section('content')

    <!-- content start -->
<section class="about_us_bg">
    <div class="container">
        <div class="banner_abt_sec">
            <h1>@lang('messages.Oddappz')</h1>
            <p>@lang('messages.The best online shops in India')</p>
        </div>
    </div>  
</section>
<section class="who_we_are">
    <div class="container">
    <h2>{{$data->title}}</h2>
        <div class="abt_top_sections">
            
           <p><?php echo $data->content; ?></p>
        </div>
</div>
</section>

    @endsection
