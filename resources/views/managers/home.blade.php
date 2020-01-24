@extends('layouts.managers')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/highcharts.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/funnel.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/exporting.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/data.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/drilldown.js') }}"></script>
@if (Session::has('message'))
    <div class="admin_sucess_common">
        <div class="admin_sucess">
            <div class="alert alert-info">
                <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>{{ Session::get('message') }}
            </div>
        </div>
    </div>
@endif

<div class="pageheader">
    <div class="media">
        <div class="pageicon pull-left">
            <i class="fa fa-home"></i>
        </div>
        <div class="media-body">
            <ul class="breadcrumb">
                <li><a href="{{ URL::to('managers/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Restaurant Managers')</a></li>
                <li>@lang('messages.Dashboard')</li>
            </ul>
            <h4>@lang('messages.Dashboard')</h4>
        </div>
    </div><!-- media -->
</div><!-- pageheader -->

<div class="contentpanel">

    <a href="<?php echo URL::to("managers/products");?>">
        <div class="col-md-3">
            <div class="panel panel-dark panel_orange noborder">
                <div class="panel-heading noborder">
                    <div class="panel-btns">
                    </div><!-- panel-btns -->
                    <div class="panel-icon"><i class="fa fa-gift"></i></div>
                    <div class="media-body">
                        <h5 class="md-title nomargin">@lang('messages.Products')</h5>
                        <h1 class="mt5"><?php echo $products->product_count; ?></h1>
                    </div><!-- media-body -->
                </div><!-- panel-body -->
            </div><!-- panel -->
        </div><!-- col-md-4 -->
    </a>

    <a href="<?php echo URL::to("managers/orders/index");?>">
        <div class="col-md-3">
            <div class="panel panel-dark panel_orange noborder">
                <div class="panel-heading noborder">
                    <div class="panel-btns">
                    </div><!-- panel-btns -->
                    <div class="panel-icon"><i class="fa fa-shopping-cart"></i></div>
                    <div class="media-body">
                        <h5 class="md-title nomargin">@lang('messages.Orders')</h5>
                        <h1 class="mt5"><?php echo $orders->orders_count; ?></h1>
                    </div><!-- media-body -->
                </div><!-- panel-body -->
            </div><!-- panel -->
        </div><!-- col-md-4 -->
    </a>
    <a href="<?php echo URL::to("managers/reviews");?>">
        <div class="col-md-3">
            <div class="panel panel-dark panel_orange noborder">
                <div class="panel-heading noborder">
                    <div class="panel-btns">
                    </div><!-- panel-btns -->
                    <div class="panel-icon"><i class="fa fa-star"></i></div>
                    <div class="media-body">
                        <h5 class="md-title nomargin">@lang('messages.Reviews Rating')</h5>
                        <h1 class="mt5"><?php echo number_format($ratings,1); ?></h1>
                    </div><!-- media-body -->
                </div><!-- panel-body -->
            </div><!-- panel -->
        </div><!-- col-md-4 -->
    </a>
    <div class="admin_dasbord_home">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>@lang('messages.Detail')</th>
                    <th>@lang('messages.Today')</th>
                    <th>@lang('messages.This week')</th>
                    <th>@lang('messages.This month')</th>
                    <th>@lang('messages.This Year')</th>
                    <th>@lang('messages.Total')</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>@lang('messages.Orders')</td>
                    <td><h3><?php echo $order_period_count[0]->day_count; ?></h3></td>
                    <td><h3><?php echo $order_period_count[0]->week_count; ?></h3></td>
                    <td><h3><?php echo $order_period_count[0]->month_count; ?></h3></td>
                    <td><h3><?php echo $order_period_count[0]->year_count; ?></h3></td>
                    <td><h3><?php echo $order_period_count[0]->total_count; ?></h3></td>
                </tr>
                <tr>
                    <td>@lang('messages.Transactions')</td>
                    <td><h3><?php echo $transaction_period_count[0]->day_count; ?></h3></td>
                    <td><h3><?php echo $transaction_period_count[0]->week_count; ?></h3></td>
                    <td><h3><?php echo $transaction_period_count[0]->month_count; ?></h3></td>
                    <td><h3><?php echo $transaction_period_count[0]->year_count; ?></h3></td>
                    <td><h3><?php echo $transaction_period_count[0]->total_count; ?></h3></td>
                </tr>
                <tr>
                    <td>@lang('messages.Reviews')</td>
                    <td><h3><?php echo $outlet_reviews_query[0]->day_count; ?></h3></td>
                    <td><h3><?php echo $outlet_reviews_query[0]->week_count; ?></h3></td>
                    <td><h3><?php echo $outlet_reviews_query[0]->month_count; ?></h3></td>
                    <td><h3><?php echo $outlet_reviews_query[0]->year_count; ?></h3></td>
                    <td><h3><?php echo $outlet_reviews_query[0]->total_count; ?></h3></td>
                </tr>
            </tbody> 
        </table>
    </div>
    
</div><!-- row -->
@endsection
