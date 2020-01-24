<?php //echo Session::get("location"); ?>
@extends('layouts.app')
@section('content')
<script src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/js/jquery.flexslider-min.js');?>"></script>
<script src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/plugins/rateit/src/jquery.rateit.js');?>"></script>
<link href="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/plugins/rateit/src/rateit.css');?>" rel="stylesheet">
<!-- container start -->
<section class="store_list">
    <div class="container">
        <div class="store_list_slider">
            <div class="slider_container">
                <div class="flexslider">
                    <ul class="slides">
                        <?php if(count($banners)){
                            foreach($banners as $data){ ?>
                                <li>
                                    <a href="{{ $data->banner_link }}"><img  src="<?php echo url('/assets/admin/base/images/banner/'.$data->banner_image.'?'.time()); ?>" alt="{{ $data->banner_title }}" title="{{ $data->banner_title }}"/></a>
                                </li>
                            <?php } ?>
                        <?php } else { ?>
                            <li>
                                <a href="{{ $data->banner_link }}"><img  src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/store_slid1.png');?>" alt="{{ $data->banner_title }}" title="{{ $data->banner_title }}"/></a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="store_listing_locat">
    <div class="container">
        <div class="row">
            <div class="findyour_location">
                <div class="findyour_inner">
                   {!!Form::open(array('url' =>'store', 'method' => 'get','class'=>'tab-form attribute_form','id'=>'home_search_form','files' => true));!!}
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="col-md-4 col-sm-12 col-xs-12">
                            <div class="select_city">
                                <select name="city"  id="city_id" required class="js-example-disabled-results">
                                    <option value="">@lang('messages.Select city')</option>
                                    <?php if(count(getCity($api)->response->data)){ ?>
                                        <?php foreach(getCity($api)->response->data as $data){ ?> 
                                            <option <?php if(Session::get('city')==$data->url_index){ echo "selected"; } ?> value="{{ $data->url_index }}">{{ ucfirst($data->city_name) }} </option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12 col-xs-12 ">
                            <div class="select_categ">
                                <select name="location" id="location_id" required class="js-example-disabled-results">
                                        <option value="">@lang('messages.Select Zone')</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12 col-xs-12">
                            <div class="submit_sect">
                                 <button type="button" id="find_location" title="@lang('messages.Find')" class="btn btn-default">@lang('messages.Find')</button>
                            </div>
                        </div>
                     {!!Form::close();!!} 
                </div>
            </div>
        </div>
    </div>
</section>
<section class="store_item_list">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="responsive_buttons" id="click_filter">
                    <button type="button" class="btn btn-primary">@lang('messages.Filter')</button>
                </div>
                <div class="filter_responsive" id="responsive_open">
                    <div class="side_filter">
                        <div id="custom-search-input">
                            <div class="input-group col-md-12">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-danger">
                                        <span class=" glyphicon glyphicon-search"></span>
                                    </button>
                                </span>
                                <input type="text" name="search"  id="txtSearch" placeholder="@lang('messages.Search')" class="search-query form-control">
                            </div>
                        </div>
                        <div class="cat_listing">
                            <h2 class="cat_all_titl">@lang('messages.Categories')<i class="glyph-icon flaticon-category"></i></h2>
                            <ul>
                                <form>
                                    <?php $cate_ids= array(); if(Input::get("category_ids")){ $cate_ids = explode(",",Input::get("category_ids")); } ?>
                                    @foreach ($categories as $val)
                                        <li>
                                            <label class="label_check" for="{{ $val->id }}">
                                            <input name="category" data-attr="{{ $val->url_key }}" id="{{ $val->id }}" class="category_ids"  value="{{ $val->id }}" <?php if(count($cate_ids)){ if(in_array($val->id,$cate_ids)) { echo  "checked"; } } ?> type="checkbox"/> <p> {{ $val->category_name }} </p></label>
                                        </li>
                                    @endforeach
                                </form>
                            </ul>
                        </div>
                        <div class="cat_listing sort_by">
                            <h2 class="cat_all_titl">@lang('messages.Sort by') <span id="orderby"><a style="cursor:pointer;" title="Order by"  onclick="myFunction1()" id="ASC" class="orderby"><i class="glyph-icon flaticon-sort-by-attributes"></i></a></span> </h2>
                            <ul class="sort_active">
                                <?php /**<li><a href="#" title="Relevance"> <i class="glyph-icon flaticon-settings"></i>Relevance</a></li>**/ ?>
                                <li class="activeclass"><a style="cursor:pointer;" title="@lang('messages.Delivery time')" id="delivery_time" class="sorting"><i class="glyph-icon flaticon-clock"></i>@lang('messages.Delivery time')</a></li>
                                 <li class="activeclass"><a style="cursor:pointer;" id="rating" title="@lang('messages.Rating')" class="sorting"><i class="glyph-icon flaticon-favorite-2"></i>@lang('messages.Rating')</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal for membership signIn -->
            <div class="modal fade model_for_signup membership_login" id="myModal3" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                            </button>
                            <span class="logo_popup">
                                <img src="<?php echo url('/assets/front/'.Session::get("general")->theme.'/images/oddappz.png'); ?>" title="{{ Session::get('general')->site_name }}" alt="{{ Session::get('general')->site_name }}">
                            </span>
                        </div>
                        <div class="modal-body">
                            <div class="sign_up_inner">
                                <h2>@lang('messages.Membership update')<br><span class="bottom_border"></span></h2>
                                <p>@lang('messages.Do you have membership in cooperative?')</p>
                                {!!Form::open(array('url' => 'membership', 'method' => 'post', 'class' => 'tab-form attribute_form', 'id' => 'membership' ,'onsubmit'=>'return membership()'));!!} 
                                    <div class="membership_inner">
                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <div class="gender_section">
                                                    <label class="label_radio" for="radio-04">
                                                    <input name="member_ship" id="radio-04" value="1" class="select_coprative_yes" type="radio" />@lang('messages.Yes')</label>
                                                    <label class="label_radio" for="radio-05">
                                                    <input name="member_ship" id="radio-05" value="1" class="select_coprative_no" type="radio" />@lang('messages.No')</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="select_coprative" > 
                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                <div class="form-group">
                                                    <div class="sign_upcooper">
                                                        <select class="form-control cooprative_select select_dropdown js-example-disabled-results" name="cooperative" required tabindex="-1">
                                                            <?php foreach(get_coperativess() as $key => $cop) { ?>
                                                                <option value="{{ $key }}">{{ $cop }}</option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                <div class="form-group">
                                                    <input type="text" class="form-control" class="membership_id" name ="membership_id" required id="exampleInputEmail" placeholder="@lang('messages.Membership id')">
                                                </div>
                                            </div>
                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                <div class="form-group">
                                                    <div class="sign_bot_sub">
                                                        <button type="button" class="btn btn-primary cancel_button" data-dismiss="modal" title="@lang('messages.Cancel')">@lang('messages.Cancel')</button>
                                                        <button type="submit" class="btn btn-default membership_submit" title="@lang('messages.Submit')">@lang('messages.Submit')</button>
                                                        <div class="ajaxloading" style="display:none;">
                                                            <div class="loader-coms">
                                                                <div class="loder_gif">
                                                                    <img src="<?php echo url('assets/front/'.Session::get("general")->theme.'/images/ajax-loader.gif');?>" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {!!Form::close();!!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal for signIn membership end -->
            <div class="col-md-9 padding0">
                <div class="store_right_items">
                    <div id="fadpage" style="display:none;"></div>
                    <span id="ajax_store_list">
                    <?php if(count($store->data)) {  echo $store->data; } else { ?>
                    </span>
                    <div class="no_data col-md-12">
                        <div class="no_store_avlable no_store_avlable1">
                            <div class="no_store_img">
                                <img src="<?php echo URL::asset('assets/front/'.Session::get("general")->theme.'/images/no_store.png');?>" alt="">
                                <p>@lang('messages.No store available in your location!') <a  title="@lang('messages.Pick another')">@lang('messages.Pick another')<br/>@lang('messages.location')</a> @lang('messages.here...')</p>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <span id="store_list"></span>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- footer section strat end -->
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<!--Plug-in Initialisation-->
<script type="text/javascript">
    $('select').select2();
    var $ = jQuery.noConflict();
    $(window).load(function() {
        $('.flexslider').flexslider({
            animation: "fade"
        });
        $('#city_id').trigger("change");
        //$('#location_id').val('<?php echo Session::get("location"); ?>').trigger("change");
    });
    $( document ).ready(function() {
        $('.cancel_button').on('click', function()
        {
            $("body").css({"padding-right":"0px !important"});
            $('#membership')[0].reset();
        });
        $("#find_location").on("click",function()
        {
            var city = $("#city_id").val();
            var location = $("#location_id").val();
            if(city != "" && location != ""&& location != null)
            {
                var url = '{{url("stores/")}}/'+city+'/'+location;
                window.location = url;
            }
            else
            {
                toastr.error('<?php echo trans('messages.Please select location');?>');
            }
        })
        $('.select_coprative_yes').on('click', function() {
            $('.select_coprative').show();
          
        });
        $('.select_coprative_no').on('click', function() {
            $('.select_coprative').hide();
            $('#myModal3').modal('hide');
          
        });
        $('#city_id').change(function(){
            $('#location_id').prop('disabled', 'disabled');
            var city_url, country_id, token, url, data;
            token = $('input[name=_token]').val();
            country_id = $('#country_id').val();
            city_url = $('#city_id').val();
            url = '{{url('list1/LocationList')}}';
            data = {city_url: city_url,country_id:country_id};
            $.ajax({
                url: url,
                headers: {'X-CSRF-TOKEN': token},
                data: data,
                type: 'POST',
                datatype: 'JSON',
                success: function (resp) {
                    $('#location_id').prop('disabled', false);
                    //console.log('in--'+resp.data);
                    $('#location_id,#delivery_areas').empty();
                    $('#s2id_delivery_areas .select2-choices .select2-search-choice').remove();
                    if(resp.data==''){
                        $('#location_id,#delivery_areas').append($("<option></option>").attr("value","").text('No data there..')); 
                    }
                    else 
                    {
                        $('#location_id,#delivery_areas').append($("<option></option>").attr("value","").text('Select Zone'));
                        $.each(resp.data, function(key, value) {
                            //console.log(value['id']+'=='+value['city_name']);
                            $('#location_id,#delivery_areas').append($("<option></option>").attr("value",value['url_index']).text(value['zone_name'])); 
                       });
                        $('#location_id').val('<?php echo Session::get("location"); ?>').change();
                    }
                }
            });
        });

        $('.category_ids').on('click', function() {
            var category_urls = "";
            if ($(this).is(":checked"))
            {
                var category_ids = $('input[name="category"]:checked').map(function () {
                    return this.value;
                }).get();
                var category_urls = $(this).attr("data-attr");
            }
            else{
                $(this).prop('checked', false);
                var category_ids = $('input[name="category"]:checked').map(function () {
                    return this.value;
                }).get();
            }
            var city = '<?php echo Session::get("city"); ?>';
            var location = '<?php echo Session::get("location"); ?>';
            var language = '<?php echo getCurrentLang(); ?>';
            var user_id = '<?php echo Session::get('user_id'); ?>';
            var cat_ids = category_ids.join(",");
            var category_url = "";
            if(category_urls == "cooperative")
            {
                var category_url = category_urls;
            }
            var token;
            var token = $("input[name=_token]").val();
            var url = "<?php echo  URL::to('api/store_list_ajax') ?>";
            $("#fadpage").show();
            $.ajax({
                url: url,
                type: "post",
                data: {"city":city,"location":location,"_token":token,"category_ids":cat_ids,"language":language,"type":"web","category_url":category_url,"user_id":user_id},
                dataType:"json",
                success: function(d) {
                    if(d.response.httpCode==200)
                    {
                        $("#fadpage").hide();
                        $("#store_list").hide();
                        $("#ajax_store_list").html(d.response.data);
                        if(d.response.member_status==2)
                        {
                            $('#myModal3').modal('show');
                            $('.select_coprative').hide();
                        }
                    }
                    else {
                        var no_store = '<?php echo URL::asset("assets/front/".Session::get("general")->theme."/images/no_store.png");?>';
                        var nodata='<div class="no_store_avlable"><div class="no_store_img"><img src="'+no_store+'" alt=""><p>No store available in your search terms</p></div></div>';
                        $("#fadpage").hide();
                        $("#store_list").hide();
                        $(".no_store_avlable1").hide();
                        $("#ajax_store_list").html(nodata);
                    }
                }
            });
        });
        $("#txtSearch").keyup(function(event){
            if ($(".category_ids").is(":checked"))
            {
                var category_ids = $('input[name="category"]:checked').map(function () {
                    return this.value;
                }).get();
            }
            else{
                $(".category_ids").prop('checked', false);
                var category_ids = $('input[name="category"]:checked').map(function () {
                    return this.value;
                }).get();
            }
            var keyword = $(this).val();
            var city = '<?php echo Input::get("city"); ?>';
            var location = '<?php echo Input::get("location"); ?>';
            var language = '<?php echo getCurrentLang(); ?>'; 
            var cat_ids = category_ids.join(",");
            var token;
            var token = $("input[name=_token]").val();
            var url = "<?php echo  URL::to('/api/store_list_ajax') ?>";
            $("#fadpage").show();
            $.ajax({
                url: url,
                type: "post",
                data: {"city":city,"location":location,"_token":token,"category_ids":cat_ids,"language":language,"keyword":keyword,"type":"web"},
                dataType:"json",
                success: function(d) {
                    if(d.response.httpCode==200){
                        $("#fadpage").hide();
                        $("#store_list").hide();
                        $("#ajax_store_list").html(d.response.data);
                    }else {
                        var no_store = '<?php echo URL::asset("assets/front/".Session::get("general")->theme."/images/no_store.png");?>';
                        var nodata='<div class="no_store_avlable"><div class="no_store_img"><img src="'+no_store+'" alt=""><p>No store available in your search terms</p></div></div>';
                        $("#fadpage").hide();
                        $("#store_list").hide();
                        $(".no_store_avlable1").hide();
                        $("#ajax_store_list").html(nodata);
                    }
                }
            });
        });
        $(".sorting").on('click', function() {
            $(this).parent('li').addClass('active');
            $(this).parent('li').siblings().removeClass('active');
            if ($(".category_ids").is(":checked"))
            {
                var category_ids = $('input[name="category"]:checked').map(function () {
                    return this.value;
                }).get();
            }
            else{
                $(".category_ids").prop('checked', false);
                var category_ids = $('input[name="category"]:checked').map(function () {
                    return this.value;
                }).get();
            }
            var keyword = $(this).val();
            var city = '<?php echo Input::get("city"); ?>';
            var location = '<?php echo Input::get("location"); ?>';
            var language = '<?php echo getCurrentLang(); ?>'; 
            var cat_ids = category_ids.join(",");
            var sort_id = $(this).attr('id');
            var sortby = '';
            var orderby ='';
            if(sort_id=='delivery_time'){
                var sortby = 'delivery_time';
                var attrid=$(".orderby1").attr('id');
                if(attrid){
                    var orderby = attrid;
                }
                var attrid1=$(".orderby").attr('id');
                if(attrid1){
                    var orderby = attrid1;
                }
            }
            if(sort_id=='rating'){
                var sortby = 'rating';
                var attrid=$(".orderby1").attr('id');
                if(attrid){
                    var orderby = attrid;
                }
                var attrid1=$(".orderby").attr('id');
                if(attrid1){
                    var orderby = attrid1;
                }
            }
            var token;
            var token = $("input[name=_token]").val();
            var url = "<?php echo  URL::to('api/store_list_ajax') ?>";
            $("#fadpage").show();
            $.ajax({
                url: url,
                type: "post",
                data: {"city":city,"location":location,"_token":token,"category_ids":cat_ids,"language":language,"keyword":keyword,"sortby":sortby,"orderby":orderby,"type":"web"},
                dataType:"json",
                success: function(d) {
                    if(d.response.httpCode==200){
                        $("#fadpage").hide();
                        $("#store_list").hide();
                        $("#ajax_store_list").html(d.response.data);
                        
                    }
                }
            });
        });
        <!-- filter section responsive start -->
        $("#click_filter").click(function(){
            $("#responsive_open").toggle();
            $(".side_filter").show();
        });
    <!-- filter section responsive end -->
    
    });
    function myFunction(){
        var orderbyid=$(".orderby1").attr('id'); 
        var html ='';
        if(orderbyid=='DESC'){
            var html ='<a style="cursor:pointer;" title="Order by"  id="ASC" class="orderby" onclick="myFunction1()" ><i class="glyph-icon flaticon-sort-by-attributes"></i></a>';
        }
        $('#orderby').html(html);
    }
    function myFunction1(){
        var orderbyid=$(".orderby").attr('id'); 
        var html ='';
        if(orderbyid=='ASC'){
            var html ='<a style="cursor:pointer;" title="Order by"  id="DESC" class="orderby1" onclick="myFunction()"><i class="glyph-icon glyphicon  glyphicon-sort-by-attributes"></i></a>';
        }
        $('#orderby').html(html);
    }
    function membership()
    {
        $( '#success_message_signup' ).show().html("");
        $(".membership_submit").hide();
        $(".ajaxloading").show();
        data = $("#membership").serializeArray();
        var c_url = '/member-ship';
        token = $('input[name=_token]').val();
        $.ajax({
            url: c_url,
            headers: {'X-CSRF-TOKEN': token},
            data: data,
            type: 'POST',
            datatype: 'JSON',
            success: function (resp)
            {
                $(".ajaxloading").hide();
                data = resp;
                console.log(data.httpCode);
                if(data.httpCode == 200)
                {
                    toastr.success("<?php echo trans('messages.Membership has been updated successfully') ?>");
                    $('#myModal3').modal('hide');
                    $('form input, form select').val('');
                    //location.reload(true);
                    return false;
                }
                else
                {
                    toastr.warning(data.Message);
                    return false;
                }
            }, 
            error:function(resp)
            {
            }
        });
        return false;
    }
</script>
@endsection
