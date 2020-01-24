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
	@if (hasTask('admin/products/create_product'))
		<div class="buttons_block pull-right">
			<div class="btn-group mr5">
				<a class="btn btn-primary tip" href="{{ URL::to('admin/products/create_product') }}" title="Add New">@lang('messages.Add New')</a>
			</div>
		</div>
	@endif
	<table id="product-table" class="table table-striped table-bordered responsive">
		<thead>
			<tr class="headings">
				
				@if(hasTask('admin/products/edit_product'))
    				<th>
    				    <input type="checkbox"  id="bulkDelete"/> 
    				    <button id="deleteTriger">@lang('messages.Delete')</button>
    			    </th>
			    @else
			         <th>@lang('messages.S.no')</th>
			    @endif
			    
				<th>@lang('messages.Product Name')</th>
				<th>@lang('messages.Category Name')</th>
				<th>@lang('messages.Quantity')</th> 
				<th>@lang('messages.Status')</th>
				<th>@lang('messages.Publish Status')</th> 
				<?php if(hasTask('admin/products/edit_product')) { ?>
				<th>@lang('messages.Actions')</th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="empty-text" colspan="11" style="background-color: #fff!important;">
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
	var checkbox=$('#deleteTriger').show();
   var oTable = $('#product-table').DataTable({
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
        ajax: '{!! route('ajaxitems.data') !!}',
        "order": [],
		"columnDefs": [ {
			"targets"  : 'no-sort',
			"orderable": false,
		}],
        columns: [
             //{ data: 'id', name: 'id',orderable: false },
			{ data: 'id', name: 'id',searchable:false,orderable: false },
			{ data: 'product_name', name: 'admin_products.product_name',searchable:true },
			{ data: 'category_name', name: 'categories_infos.category_name',searchable:true },
			{ data: 'quantity', name: 'quantity' },
            { data: 'active_status', name: 'active_status',searchable:false },
			{ data: 'approval_status', name: 'approval_status' },
			<?php if(hasTask('admin/products/edit_product')) { ?>
            { data: 'action', name: 'action', orderable: false, searchable: false}
            <?php } ?>
        ],
    });
   
       $(".deleteRow").on('click',function() { 
			alert('data');
			var status1 = this.checked;
			if(status1){
			$('#deleteTriger').show();
			}else {
				$('#deleteTriger').hide();
			}
            $(this).prop("checked",status1);
            
        });
        
   
$("#bulkDelete").on('click',function() { // bulk checked
		
        var status = this.checked;
        if(status){
			$('#deleteTriger').show();
		}
        $(".deleteRow").each( function() {
            $(this).prop("checked",status);
            
        });
        
    });

    $('#deleteTriger').on("click", function(event){
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
				url = '{{url('admin/products/bulkdelete')}}';
				
				$.ajax({
				url: url,
				headers: {'X-CSRF-TOKEN': token},
				data: {data_ids:ids_string},
				type: 'POST',
				datatype: 'JSON',
				success: function(result) {
					$('#deleteTriger').hide();
					$('#bulkDelete').prop("checked",false);
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
