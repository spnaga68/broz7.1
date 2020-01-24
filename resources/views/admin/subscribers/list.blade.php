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
                <li>@lang('messages.Subscribers')</li>
            </ul>
            <h4>@lang('messages.Subscribers List')</h4>
        </div>
    </div><!-- media -->
</div><!-- pageheader -->
<div class="contentpanel">

    @if (Session::has('message'))
        <div class="admin_sucess_common">
            <div class="admin_sucess">
                <div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
            </div>
        </div>
    @endif
    <table id="subscribersTable" class="table table-striped table-bordered responsive">
        <thead>
            <tr class="headings">
                <th>@lang('messages.S.No')</th> 
                <th>@lang('messages.Email')</th> 
                <th>@lang('messages.Created Date')</th> 
                <th>@lang('messages.Status')</th>
                @if(hasTask('admin/subscribers/delete'))
                <th>@lang('messages.Actions')</th>
                @endif
			</tr>
        </thead>
        <tbody>
			<tr>
				<td class="empty-text" colspan="5" style="background-color: #fff!important;">
					<div class="list-empty-text"> @lang('messages.No records found.') </div>
				</td>
			</tr>
		</tbody>
    </table>
</div>
<script>
    $(function() {
        $('#subscribersTable').DataTable({
            dom: 'Blfrtip',
            buttons: [
                {
                    extend: 'excel',
                    text: 'Export',
                    title: 'subscribers',
                    footer: false,
                    exportOptions: {
                        columns: [0,1,2,3]
                    }
                }
            ],
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth:false,
            ajax: '{!! route('listSubscriberAjax.data') !!}',
            "order": [],
            "columnDefs": [ {
                "targets"  : 'no-sort',
                "orderable": false,
            }],
            columns: [
                { data: 'id', name: 'id',orderable: false },
                { data: 'email', name: 'email',searchable:true },
                { data: 'created_date', name: 'created_date' },
                { data: 'active_status', name: 'active_status' },
                <?php if(hasTask('admin/subscribers/delete')) { ?>
                { data: 'action', name: 'action', orderable: false, searchable: false}
                <?php } ?>
            ],
        });
    });
</script>
@endsection
