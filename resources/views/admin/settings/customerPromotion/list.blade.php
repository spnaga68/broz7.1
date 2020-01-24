@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/dataTables.min.js') }}"></script>
<script src="https://cdn.datatables.net/buttons/1.0.3/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/export/jszip.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/export/pdfmake.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/export/vfs_fonts.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/export/buttons.html5.min.js') }}"></script>
<link href="{{ URL::asset('assets/admin/base/css/dataTables.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/admin/base/plugins/export/buttons.dataTables.min.css') }}" media="all" rel="stylesheet" type="text/css" />

<div class="pageheader">
	<div class="media">
		<div class="pageicon pull-left">
			<i class="fa fa-home"></i>
		</div>
		<div class="media-body">
			<ul class="breadcrumb">
				<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
				<li>@lang('messages.Customer Promotion')</li>
			</ul>
			<h4>@lang('messages.Customer Promotion')</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->

<div class="contentpanel">
	@if(hasTask('admin/customer_promotion/create'))
		<div class="buttons_block pull-right">
			<div class="btn-group mr5">
				<a class="btn btn-primary tip" href="{{ URL::to('admin/customer_promotion/create') }}" title="Add New"><i class="fa fa-plus"> </i> @lang('messages.Add New')</a>
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
	<table id="customer_promotionTable" class="table table-striped table-bordered responsive">
		<thead>
			<tr class="headings">
				<th>@lang('messages.S.No')</th> 
				<th>@lang('messages.Promotion Name')</th>
				<th>@lang('messages.Base Amount')</th>
				<th>@lang('messages.Addition Promotion')</th>
				<th>@lang('messages.Grocery Wallet')</th>
				 <th>@lang('messages.Start Date')</th> 
                <th>@lang('messages.End Date')</th> 
				<th>@lang('messages.Status')</th>
<!-- 				<th>@lang('messages.Is Verified')</th>
 -->				<?php if(hasTask('admin/customer_promotion/create')) { ?>
				<th>@lang('messages.Actions')</th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="empty-text" colspan="8" style="background-color: #fff!important;">
					<div class="list-empty-text"> @lang('messages.No records found.') </div>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<script>
    $(function() {
        $('#customer_promotionTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth:false,
            ajax: '{!! route('listCustomerPromotionAjax.data') !!}',
            "order": [],
            "columnDefs": [ {
                "targets"  : 'no-sort',
				"orderable": true,
				"searchable":true,
				"pagingType": "full",
				'exportable':false
            }],
            dom: 'Blfrtip',
            buttons: [
                {
                    extend: 'excel',
                    text: 'Export',
                    title: 'customer_promotion',
                    footer: false,
                    exportOptions: {
                        columns: [0,1,2,3,4,5,6,7]
                    }
                }
            ],
            columns: [
                { data: 'id', name: 'id',orderable: false },
                { data: 'promotion_name', name: 'promotion_name',searchable:true },
                { data: 'base_amount', name: 'base_amount' },
                { data: 'addition_promotion', name: 'addition_promotion' },
                { data: 'grocery_wallet', name: 'grocery_wallet' },
                { data: 'start_date', name: 'start_date',searchable:true },
                { data: 'end_date', name: 'end_date'},
                { data: 'active_status', name: 'active_status'},
                <?php if(hasTask('admin/customer_promotion/create')) { ?>
                { data: 'action', name: 'action', orderable: false, searchable: false}
                <?php } ?>
            ],
        });
    });
</script>
@endsection
