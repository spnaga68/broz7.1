@extends('layouts.admin')
@section('content')
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/dataTables.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/export/dataTables.buttons.min.js') }}"></script>
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
				<li>@lang('messages.Outlets')</li>
			</ul>
			<h4>@lang('messages.Outlets')</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->
	<!-- will be used to show any messages -->
	@if (Session::has('message'))
		<div class="admin_sucess_common">
	<div class="admin_sucess">
		<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div></div></div>
	@endif
<div class="contentpanel">
	@if(hasTask('vendors/create_outlet'))
		<div class="buttons_block pull-right">
			<div class="btn-group mr5">
				<a class="btn btn-primary tip" href="{{ URL::to('vendors/create_outlet') }}" title="Add New">@lang('messages.Add New')</a>
			</div>
		</div>
	@endif
	<table id="vendor-table" class="table table-striped table-bordered responsive">
		<thead>
			<tr class="headings">
			<th><input type="checkbox"  id="bulkDeleteoutlet"/> <button id="deleteTrigeroutlet">@lang('messages.Delete')</button></th> 
				<th>@lang('messages.Outlet Name')</th> 
				<th>@lang('messages.Vendor Name')</th>
				<th>@lang('messages.Contact Email')</th> 
				<th>@lang('messages.Contact Phone')</th> 
				<th>@lang('messages.Contact Address')</th> 
				<th>@lang('messages.Created date')</th>
				<th>@lang('messages.Updated Date')</th>
				<th>@lang('messages.Status')</th>
				<?php if(hasTask('vendors/edit_outlet')){ ?>
				<th>@lang('messages.Actions')</th>
				<?php } ?>
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

<script>
$(function() {
	var checkbox=$('#deleteTrigeroutlet').show();
    $('#vendor-table').DataTable({
		dom: 'Blfrtip',
		buttons: [
			{
				extend: 'excel',
				footer: false,
				title:'Vendors',
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
        ajax: '{!! route('ajaxbranch.data') !!}',
        "order": [],
		"columnDefs": [ {
		  "targets"  : 'no-sort',
		  "orderable": false,
		}],
        columns: [
			{ data: 'id', name: 'id',searchable:false,orderable: false },
			{ data: 'outlet_name', name: 'outlet_name',searchable:true },
			{ data: 'vendor_name', name: 'vendor_name',searchable:true },
			{ data: 'contact_email', name: 'contact_email' },
			{ data: 'contact_phone', name: 'contact_phone' },
			{ data: 'contact_address', name: 'contact_address' },
            { data: 'created_date', name: 'created_date' },
			{ data: 'modified_date', name: 'modified_date' },
			{ data: 'active_status', name: 'active_status' },
			<?php if(hasTask('vendors/edit_outlet')){ ?>
            { data: 'action', name: 'action', orderable: false, searchable: false}
            <?php } ?>
        ],
    });
	$(".deleteRow").on('click',function() { 
			alert('data');
			var status1 = this.checked;
			if(status1){
			$('#deleteTrigeroutlet').show();
			}else {
				$('#deleteTrigeroutlet').hide();
			}
            $(this).prop("checked",status1);
            
        });
        
   
$("#bulkDeleteoutlet").on('click',function() { // bulk checked
		
        var status = this.checked;
        if(status){
			$('#deleteTrigeroutlet').show();
		}
        $(".deleteRow").each( function() {
            $(this).prop("checked",status);
            
        });
        
    });

    $('#deleteTrigeroutlet').on("click", function(event){
		 // triggering delete one by one
        if( $('.deleteRow:checked').length > 0 ){
			if (confirm("Are you sure want to delete?")) {
				var ids = [];
				$('.deleteRow').each(function(){
					if($(this).is(':checked')) { 
						ids.push($(this).val());
					}
				});

				var token;
					token = $('input[name=_token]').val();
				var ids_string = ids.toString();  // array to string conversion
				url = '{{url('vendors/outlet/bulkdelete')}}';
				
				$.ajax({
				url: url,
				headers: {'X-CSRF-TOKEN': token},
				data: {data_ids:ids_string},
				type: 'POST',
				datatype: 'JSON',
				success: function(result) {
					//cnsle.log(result);return false;
					$('#deleteTrigeroutlet').hide();
					$('#bulkDeleteoutlet').prop("checked",false);
					//oTable.ajax.reload();
					//checkbox.ajax.reload();
					location.reload(true);
				},
				async:false
				});
			}
        }
    });
});
</script>
@endsection
