@extends('layouts.admin')
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
				<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
				<li>@lang('messages.Dashboard')</li>
			</ul>
			<h4>@lang('messages.Dashboard')</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->

<div class="contentpanel">
	<?php if (hasTask('admin/users/index')) {?>
		<a href="<?php echo URL::to("admin/users/index"); ?>">
			<div class="col-md-3">
				<div class="panel panel-primary  noborder">
					<div class="panel-heading noborder">
						<div class="panel-btns">
						</div><!-- panel-btns -->
						<div class="panel-icon"><i class="fa fa-users"></i></div>
						<div class="media-body">
							<h5 class="md-title nomargin">@lang('messages.Users')</h5>
							<h1 class="mt5"><?php echo count(getUserList(1)); ?></h1>
						</div><!-- media-body -->
					</div><!-- panel-body -->
				</div><!-- panel -->
			</div><!-- col-md-4 -->
		</a>
	<?php }?>

	<?php if (hasTask('vendors/vendors')) {?>
		<a href="<?php echo URL::to("vendors/vendors"); ?>">
			<div class="col-md-3">
				<div class="panel panel-dark panel_orange noborder">
					<div class="panel-heading noborder">
						<div class="panel-btns">
						</div><!-- panel-btns -->
						<div class="panel-icon"><i class="fa fa-rocket"></i></div>
						<div class="media-body">
							<h5 class="md-title nomargin">@lang('messages.Vendors')</h5>
							<h1 class="mt5"><?php echo count(getUserList(2)); ?></h1>
						</div><!-- media-body -->
					</div><!-- panel-body -->
				</div><!-- panel -->
			</div><!-- col-md-4 -->
		</a>
	<?php }?>

	<?php if (hasTask('vendors/outlets')) {?>
		<a href="<?php echo URL::to("vendors/outlets"); ?>">
			<div class="col-md-3">
				<div class="panel panel-dark panel_orange noborder">
					<div class="panel-heading noborder">
						<div class="panel-btns">
						</div><!-- panel-btns -->
						<div class="panel-icon"><i class="fa fa-building"></i></div>
						<div class="media-body">
							<h5 class="md-title nomargin">@lang('messages.Outlets')</h5>
							<h1 class="mt5"><?php echo count($outlets); ?></h1>
						</div><!-- media-body -->
					</div><!-- panel-body -->
				</div><!-- panel -->
			</div><!-- col-md-4 -->
		</a>
	<?php }?>

	<?php /*<a href="<?php echo URL::to("vendors/outlet_managers");?>">
<div class="col-md-3">
<div class="panel panel-dark panel_orange noborder">
<div class="panel-heading noborder">
<div class="panel-btns">
</div><!-- panel-btns -->
<div class="panel-icon"><i class="fa fa-child"></i></div>
<div class="media-body">
<h5 class="md-title nomargin">@lang('messages.Outlet Managers')</h5>
<h1 class="mt5"><?php echo count($outlet_managers); ?></h1>
</div><!-- media-body -->
</div><!-- panel-body -->
</div><!-- panel -->
</div><!-- col-md-4 -->
</a>*/?>

	<?php if (hasTask('admin/products')) {?>
		<a href="<?php echo URL::to("admin/products"); ?>">
			<div class="col-md-3">
				<div class="panel panel-dark panel_orange noborder">
					<div class="panel-heading noborder">
						<div class="panel-btns">
						</div><!-- panel-btns -->
						<div class="panel-icon"><i class="fa fa-gift"></i></div>
						<div class="media-body">
							<h5 class="md-title nomargin">@lang('messages.Products')</h5>
							<h1 class="mt5"><?php echo count($products); ?></h1>
						</div><!-- media-body -->
					</div><!-- panel-body -->
				</div><!-- panel -->
			</div><!-- col-md-4 -->
		</a>
	<?php }?>






<?php /*	<?php if(hasTask('admin/drivers')) { ?>
<a href="<?php echo URL::to("admin/drivers");?>">
<div class="col-md-3">
<div class="panel panel-dark panel_orange noborder">
<div class="panel-heading noborder">
<div class="panel-btns">
</div><!-- panel-btns -->
<div class="panel-icon"><i class="fa fa-truck"></i></div>
<div class="media-body">
<h5 class="md-title nomargin">@lang('messages.Drivers')</h5>
<h1 class="mt5"><?php echo count($drivers); ?></h1>
</div><!-- media-body -->
</div><!-- panel-body -->
</div><!-- panel -->
</div><!-- col-md-4 -->
</a>
<?php } ?> */?>

	<?php if (hasTask('admin/coupons')) {?>
		<a href="<?php echo URL::to("admin/coupons"); ?>">
			<div class="col-md-3">
				<div class="panel panel-dark panel_orange noborder">
					<div class="panel-heading noborder">
						<div class="panel-btns">
						</div><!-- panel-btns -->
						<div class="panel-icon"><i class="fa fa-ticket"></i></div>
						<div class="media-body">
							<h5 class="md-title nomargin">@lang('messages.Coupons')</h5>
							<h1 class="mt5"><?php echo count($coupons); ?></h1>
						</div><!-- media-body -->
					</div><!-- panel-body -->
				</div><!-- panel -->
			</div><!-- col-md-4 -->
		</a>
	<?php }?>

	<?php if (hasTask('admin/subscribers')) {?>
		<a href="<?php echo URL::to("admin/subscribers"); ?>">
			<div class="col-md-3">
				<div class="panel panel-dark panel_orange noborder">
					<div class="panel-heading noborder">
						<div class="panel-btns">
						</div><!-- panel-btns -->
						<div class="panel-icon"><i class="fa fa-user"></i></div>
						<div class="media-body">
							<h5 class="md-title nomargin">@lang('messages.Subscribers')</h5>
							<h1 class="mt5"><?php echo count($newsletter_subscribers); ?></h1>
						</div><!-- media-body -->
					</div><!-- panel-body -->
				</div><!-- panel -->
			</div><!-- col-md-4 -->
		</a>
	<?php }?>

	<?php if (hasTask('admin/orders/index')) {?>
		<a href="<?php echo URL::to("admin/orders/index"); ?>">
			<div class="col-md-3">
				<div class="panel panel-dark panel_orange noborder">
					<div class="panel-heading noborder">
						<div class="panel-btns">
						</div><!-- panel-btns -->
						<div class="panel-icon"><i class="fa fa-shopping-cart"></i></div>
						<div class="media-body">
							<h5 class="md-title nomargin">@lang('messages.Orders')</h5>
							<h1 class="mt5"><?php echo count($orders); ?></h1>
						</div><!-- media-body -->
					</div><!-- panel-body -->
				</div><!-- panel -->
			</div><!-- col-md-4 -->
		</a>
	<?php }?>

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
			<?php// print_r($order_period_count[0]);exit;?>
				<?php if (hasTask('admin/orders/index')) {?>
					<tr>
						<td>@lang('messages.Orders')</td>
						<td><h3><?php echo $order_period_count[0]->day_count; ?></h3></td>
						<td><h3><?php echo $order_period_count[0]->week_count; ?></h3></td>
						<td><h3><?php echo $order_period_count[0]->month_count; ?></h3></td>
						<td><h3><?php echo $order_period_count[0]->year_count; ?></h3></td>
						<td><h3><?php echo $order_period_count[0]->total_count; ?></h3></td>
					</tr>
				<?php }?>
				<?php /*if(hasTask('admin/orders/index') || hasTask('admin/orders/update-status') || hasTask('orders/return_orders') || hasTask('orders/return_orders_view') || hasTask('orders/fund_requests') ) { ?>
<tr>
<td>@lang('messages.Transactions')</td>
<td><h3><?php echo $transaction_period_count[0]->day_count; ?></h3></td>
<td><h3><?php echo $transaction_period_count[0]->week_count; ?></h3></td>
<td><h3><?php echo $transaction_period_count[0]->month_count; ?></h3></td>
<td><h3><?php echo $transaction_period_count[0]->year_count; ?></h3></td>
<td><h3><?php echo $transaction_period_count[0]->total_count; ?></h3></td>
</tr>
<?php }  */?>
				<?php if (hasTask('admin/users/index')) {?>
					<tr>
						<td>@lang('messages.Users')</td>
						<td><h3><?php echo $users_period_count[0]->day_count; ?></h3></td>
						<td><h3><?php echo $users_period_count[0]->week_count; ?></h3></td>
						<td><h3><?php echo $users_period_count[0]->month_count; ?></h3></td>
						<td><h3><?php echo $users_period_count[0]->year_count; ?></h3></td>
						<td><h3><?php echo $users_period_count[0]->total_count - 1; ?></h3></td>
					</tr>
				<?php }?>
				<?php if (hasTask('vendors/vendors')) {?>
					<tr>
						<td>@lang('messages.Vendors')</td>
						<td><h3><?php echo $vendors_period_count[0]->day_count; ?></h3></td>
						<td><h3><?php echo $vendors_period_count[0]->week_count; ?></h3></td>
						<td><h3><?php echo $vendors_period_count[0]->month_count; ?></h3></td>
						<td><h3><?php echo $vendors_period_count[0]->year_count; ?></h3></td>
						<td><h3><?php echo $vendors_period_count[0]->total_count; ?></h3></td>
					</tr>
				<?php }?>
				 <?php /* <?php if(hasTask('admin/drivers')) { ?>
<tr>
<td>@lang('messages.Drivers')</td>
<td><h3><?php echo $drivers_period_count[0]->day_count; ?></h3></td>
<td><h3><?php echo $drivers_period_count[0]->week_count; ?></h3></td>
<td><h3><?php echo $drivers_period_count[0]->month_count; ?></h3></td>
<td><h3><?php echo $drivers_period_count[0]->year_count; ?></h3></td>
<td><h3><?php echo $drivers_period_count[0]->total_count; ?></h3></td>
</tr>
<?php } ?> */?>
				<?php if (hasTask('admin/newsletter')) {?>
					<tr>
						<td>@lang('messages.Newsletter')</td>
						<td><h3><?php echo $newsletter_subscribers_period_count[0]->day_count; ?></h3></td>
						<td><h3><?php echo $newsletter_subscribers_period_count[0]->week_count; ?></h3></td>
						<td><h3><?php echo $newsletter_subscribers_period_count[0]->month_count; ?></h3></td>
						<td><h3><?php echo $newsletter_subscribers_period_count[0]->year_count; ?></h3></td>
						<td><h3><?php echo $newsletter_subscribers_period_count[0]->total_count; ?></h3></td>
					</tr>
				<?php }?>
				<?php if (hasTask('admin/blog')) {?>
					<tr>
						<td>@lang('messages.Blogs')</td>
						<td><h3><?php echo $blogs_count[0]->day_count; ?></h3></td>
						<td><h3><?php echo $blogs_count[0]->week_count; ?></h3></td>
						<td><h3><?php echo $blogs_count[0]->month_count; ?></h3></td>
						<td><h3><?php echo $blogs_count[0]->year_count; ?></h3></td>
						<td><h3><?php echo $blogs_count[0]->total_count; ?></h3></td>
					</tr>
				<?php }?>
				<?php if (hasTask('vendors/outlets')) {?>
					<tr>
						<td>@lang('messages.Outlets')</td>
						<td><h3><?php echo $outlets_period_count[0]->day_count; ?></h3></td>
						<td><h3><?php echo $outlets_period_count[0]->week_count; ?></h3></td>
						<td><h3><?php echo $outlets_period_count[0]->month_count; ?></h3></td>
						<td><h3><?php echo $outlets_period_count[0]->year_count; ?></h3></td>
						<td><h3><?php echo $outlets_period_count[0]->total_count; ?></h3></td>
					</tr>
				<?php }?>
				<?php if (hasTask('admin/reviews')) {?>
					<tr>
						<td>@lang('messages.Reviews')</td>
						<td><h3><?php echo $outlet_reviews_query[0]->day_count; ?></h3></td>
						<td><h3><?php echo $outlet_reviews_query[0]->week_count; ?></h3></td>
						<td><h3><?php echo $outlet_reviews_query[0]->month_count; ?></h3></td>
						<td><h3><?php echo $outlet_reviews_query[0]->year_count; ?></h3></td>
						<td><h3><?php echo $outlet_reviews_query[0]->total_count; ?></h3></td>
					</tr>
				<?php }?>
			</tbody>
		</table>
	</div>
	<?php /* if(count($order_status_count) > 0 ) { ?>
<div id="container" style="min-width: 410px; max-width: 750px;"></div>
<script type="text/javascript">
$(function () {
$('#container').highcharts({
chart: {
type: 'funnel',
marginRight: 100
},
title: {
text: '<?php echo trans('messages.Undelivered order status');?>',
x: -50
},
plotOptions: {
series: {
dataLabels: {
enabled: true,
format: '<b>{point.name}</b> ({point.y:,.0f})',
color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black',
softConnector: true
},
neckWidth: '30%',
neckHeight: '25%'
//-- Other available options
// height: pixels or percent
// width: pixels or percent
}
},
legend: {
enabled: false
},
series: [{
name: '<?php echo trans('messages.Order Status');?>',
data: [
['Initiated Order', <?php echo $order_status_count[0]->oreder_initiated;?>],
['Processed Order', <?php echo $order_status_count[0]->oreder_processed;?>],
['Shipped Order', <?php echo $order_status_count[0]->oreder_shipped;?>],
['Packed Order', <?php echo $order_status_count[0]->oreder_packed;?>],
['Dispatched Order', <?php echo $order_status_count[0]->oreder_dispatched;?>],
]
}]
});
});
</script>
<?php } ?>
<div id="container1"></div>
<?php if(count($year_transaction) > 0 ) {?>
<script type="text/javascript">
$(function () {
// Create the chart
$('#container1').highcharts({
chart: {
type: 'column'
},
title: {
text: '<?php echo trans("messages.Monthly transaction report in ").$currency_symbol;?>'
},
xAxis: {
type: 'category'
},
yAxis: {
title: {
text: '<?php echo trans("messages.Total transaction amount");?>'
}
},
legend: {
enabled: false
},
plotOptions: {
series: {
borderWidth: 0,
dataLabels: {
enabled: true,
format: '{point.y}'
}
}
},

tooltip: {
headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b><br/>'
},

series: [{
name: 'Transaction Amount',
colorByPoint: true,
data: [
<?php foreach($year_transaction as $y) { ?>
{
name: '<?php echo $y->month_string;?>',
y: <?php echo ($y->total_amount != '')?$y->total_amount:0;?>,
drilldown: '<?php echo $y->month_string;?>'
},
<?php } ?>
]
}]
});
});
</script>
<?php } ?>
<div id="container3"></div>
<?php if(count($store_transaction_count) > 0) { ?>
<script type="text/javascript">
$(function () {
// Radialize the colors
Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function (color) {
return {
radialGradient: {
cx: 0.5,
cy: 0.3,
r: 0.7
},
stops: [
[0, color],
[1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
]
};
});

// Build the chart
$('#container3').highcharts({
chart: {
plotBackgroundColor: null,
plotBorderWidth: null,
plotShadow: false,
type: 'pie'
},
title: {
text: '<?php echo trans('messages.Store transactions');?>'
},
tooltip: {
pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
},
plotOptions: {
pie: {
allowPointSelect: true,
cursor: 'pointer',
dataLabels: {
enabled: true,
format: '<b>{point.name}</b>: {point.percentage:.1f} %',
style: {
color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
},
connectorColor: 'silver'
}
}
},
series: [{
name: '<?php echo trans('messages.Stores');?>',
data: [
<?php foreach($store_transaction_count as $store) {?>
{
name: '<?php echo ucfirst($store->vendor_name);?>',
y: <?php echo $store->total;?>
},
<?php } ?>
]
}]
});
});
</script>
<?php } ?>
<div id="container4"></div>
<?php $month_list = $web_user_list = $android_user_list = $iOS_user_list = '';
if(count($web_user_count) > 0)
{
foreach($web_user_count as $web)
{
$month_list .= "'".$web->month_string."',";
$web_user_list .= $web->web_total_count.',';
}
$month_list = rtrim($month_list,',');
$web_user_list = rtrim($web_user_list,',');
}
if(count($android_user_count) > 0)
{
foreach($android_user_count as $andro)
{
$android_user_list .= $andro->android_total_count.',';
}
$android_user_list = rtrim($android_user_list,',');
}
if(count($ios_user_count) > 0)
{
foreach($ios_user_count as $ios_u)
{
$iOS_user_list .= $ios_u->ios_total_count.',';
}
$iOS_user_list = rtrim($iOS_user_list,',');
}?>
<script type="text/javascript">
$(function () {
$('#container4').highcharts({
chart: {
type: 'column'
},
title: {
text: '<?php echo trans('messages.Month wise user count');?>'
},
xAxis: {
categories: [<?php echo $month_list;?>]
},
yAxis: {
min: 0,
title: {
text: '<?php echo trans('messages.Total user count');?>'
},
stackLabels: {
enabled: true,
style: {
fontWeight: 'bold',
color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
}
}
},
legend: {
align: 'right',
x: -30,
verticalAlign: 'top',
y: 25,
floating: true,
backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || 'white',
borderColor: '#CCC',
borderWidth: 1,
shadow: false
},
tooltip: {
headerFormat: '<b>{point.x}</b><br/>',
pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
},
plotOptions: {
column: {
stacking: 'normal',
dataLabels: {
enabled: true,
color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
}
}
},
series: [{
name: '<?php echo trans('messages.Web user');?>',
data: [<?php echo $web_user_list;?>]
},
{
name: '<?php echo trans('messages.Android user');?>',
data: [<?php echo $android_user_list;?>]
},
{
name: '<?php echo trans('messages.iOS user');?>',
data: [<?php echo $iOS_user_list;?>]
}]
});
});
</script> */?>
</div><!-- row -->
@endsection
