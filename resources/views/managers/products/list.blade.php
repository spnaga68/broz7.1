@extends('layouts.managers')
@section('content')
<link href="{{ URL::asset('assets/admin/base/css/dataTables.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/admin/base/plugins/export/buttons.dataTables.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<div class="pageheader">
<div class="media">
    <div class="pageicon pull-left">
        <i class="fa fa-home"></i>
    </div>
    <div class="media-body">
        <ul class="breadcrumb">
            <li><a href="{{ URL::to('managers/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Outlet Managers')</a></li>
            <li>@lang('messages.Products')</li>
        </ul>
        <h4>@lang('messages.Products')</h4>
    </div>
</div><!-- media -->
</div><!-- pageheader -->
<!-- will be used to show any messages -->
@if (Session::has('message'))
    <div class="admin_sucess_common">
        <div class="admin_sucess">
            <div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
        </div>
    </div>
@endif
<div class="contentpanel">
    <div class="buttons_block pull-right">
        <div class="btn-group mr5">
            <a class="btn btn-primary tip" href="{{ URL::to('managers/products/create_product') }}" title="Add New">@lang('messages.Add New')</a>
        </div>
    </div>
    <table id="product-table" class="table table-striped table-bordered responsive">
        <thead>
            <tr class="headings">
                <th>@lang('messages.S.no')</th>
                <th>@lang('messages.Product Name')</th>
                <th>@lang('messages.Outlet Name')</th>
                <th>@lang('messages.Category Name')</th>
                <th>@lang('messages.Quantity')</th> 
                <th>@lang('messages.Original Price')</th> 
                <th>@lang('messages.Discounted Price')</th>
                <th>@lang('messages.Status')</th>
                <th>@lang('messages.Publish Status')</th> 
                <th>@lang('messages.Actions')</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="empty-text" colspan="7" style="background-color: #fff!important;">
                    <div class="list-empty-text"> @lang('messages.No records found.') </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/dataTables.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/export/dataTables.buttons.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/export/jszip.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/export/pdfmake.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/export/vfs_fonts.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/export/buttons.html5.min.js') }}"></script>
<script type="text/javascript">
$(function() {
    $('#product-table').DataTable({
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'excel',
                footer: false,
                title:'Products',
                text:'Export',
                exportOptions: {
                     columns: [0,1,2,3,4,5,6,7,8]
                 }
            }
        ],
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth:false,
        ajax: '{!! route('ajaxproductitemsm.data') !!}',
        "order": [],
        "columnDefs": [ {
          "targets"  : 'no-sort',
          "orderable": false,
        }],
        columns: [
            { data: 'id', name: 'id',orderable: false },
            { data: 'product_name', name: 'product_name',searchable:true },
            { data: 'outlet_name', name: 'outlet_name',searchable:true },
            { data: 'category_name', name: 'category_name',searchable:true },
            { data: 'quantity', name: 'quantity' },
            { data: 'original_price', name: 'original_price' },
            { data: 'discount_price', name: 'discount_price' },
            //{ data: 'created_date', name: 'created_date' },
            //{ data: 'modified_date', name: 'modified_date' },
            { data: 'active_status', name: 'active_status' },
            { data: 'approval_status', name: 'approval_status' },
            { data: 'action', name: 'action', orderable: false, searchable: false}
        ],
    });
});
</script>
@endsection
