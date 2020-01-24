@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/dataTables.min.js') }}"></script>
<script src="https://cdn.datatables.net/buttons/1.0.3/js/dataTables.buttons.min.js"></script>
<link href="{{ URL::asset('assets/admin/base/css/dataTables.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<div class="pageheader">
    <div class="media">
        <div class="pageicon pull-left">
            <i class="fa fa-home"></i>
        </div>
        <div class="media-body">
            <ul class="breadcrumb">
                <li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
                <li>@lang('messages.Currency')</li>
            </ul>
            <h4>@lang('messages.Currency')</h4>
        </div>
    </div><!-- media -->
</div><!-- pageheader -->
<div class="contentpanel">
    <div class="buttons_block pull-right">
        <div class="btn-group mr5">
            <a class="btn btn-primary tip" href="{{ URL::to('admin/currency/create') }}" title="Add New">@lang('messages.Add New')</a>
        </div>
    </div>
    @if (Session::has('message'))
        <div class="admin_sucess_common">
            <div class="admin_sucess">
                <div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
            </div>
        </div>
    @endif
    <table id="currencyTable" class="table table-striped table-bordered responsive">
        <thead>
            <tr class="headings">
                <th>@lang('messages.S.No')</th>
                <th>@lang('messages.Currency Name')</th>
                <th>@lang('messages.Currency Code')</th>
                <th>@lang('messages.Numeric ISO Code')</th>
                <th>@lang('messages.Currency Symbol')</th>
                <th>@lang('messages.Exchange Rate')</th>
                <th>@lang('messages.Created Date')</th>
                <th>@lang('messages.Status')</th>
                <th>@lang('messages.Actions')</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="empty-text" colspan="9" style="background-color: #fff!important;">
                    <div class="list-empty-text"> @lang('messages.No records found.') </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script type="text/javascript">
$(function() {
    $('#currencyTable').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        autoWidth:false,
        dom: 'Blfrtip',
        buttons: ['excel','csv','pdf',],
        ajax: '{!! route('ajaxcurrency.data') !!}',
        "order": [],
        "columnDefs": [ {
            "targets"  : 'no-sort',
            "orderable": false,
            "searchable":true,
            "pagingType": "full"
        }],
        columns: [
            { data: 'id', name: 'id',orderable: false },
            { data: 'currency_name',name: 'currencies_infos.currency_name'},
            { data: 'currency_code', name: 'currency_code' },
            { data: 'numeric_iso_code', name: 'numeric_iso_code' },
            { data: 'currency_symbol', name: 'currencies_infos.currency_symbol' },
            { data: 'exchange_rate', name: 'exchange_rate' },
            { data: 'created_date', name: 'created_date' },
            { data: 'default_status', name: 'default_status' },
            { data: 'action', name: 'action', orderable: false, searchable: false}
        ],
    });
});
</script>
@endsection
