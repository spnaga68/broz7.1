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
			<li>@lang('messages.Reviews')</li>
		</ul>
		<h4>@lang('messages.Reviews')</h4>
	</div>
</div><!-- media -->
</div><!-- pageheader -->
<div class="contentpanel">
@if (Session::has('message'))
	<div class="admin_sucess_common">
	<div class="admin_sucess">
    <div class="alert alert-info"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
    </div></div>
@endif
 <table id="ReviewsTable" class="table table-striped table-bordered responsive">
    <thead>
        <tr class="headings">
            <th>
                <input type="checkbox"  id="bulkDelete"/> 
                <button id="deleteTriger">@lang('messages.Approve')</button>
            </th>
<!--             <th>@lang('messages.Review Id')</th> 
 -->            <th>@lang('messages.Outlet Name')</th>
            <th>@lang('messages.Posetd By')</th> 
            <th>@lang('messages.Review Text')</th> 
            <th>@lang('messages.Star rating')</th>
            <th>@lang('messages.Posted Date')</th>
            <th>@lang('messages.Status')</th> 
            <?php if(hasTask('admin/reviews/view')) { ?>
            <th>@lang('messages.Actions')</th> 
            <?php } ?>
        </tr>
    </thead>
</table>
</div>
<script>
$(function() {
    var checkbox=$('#deleteTriger').show();

    $('#ReviewsTable').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
		autoWidth:false,
		dom: 'Blfrtip',
		 buttons:
		[
        'excel',
        'csv',
        'pdf',
		],
        ajax: '{!! route('ajaxReviewslist.data') !!}',
        "order": [],
		"columnDefs": [ {
		  "targets"  : 'no-sort',
		  "orderable": false,
		  "searchable":true,
		  "pagingType": "full"
		  
		}],
        columns: [
            //{ data: 'reviewid', name: 'reviewid',searchable:false,orderable: false },
			{ data: 'review_id', name: 'review_id',orderable: false },
			{ data: 'outlet_name', name: 'outlet_name' },
			{ data: 'user_name',name: 'user_name'},
			{ data: 'comments',name: 'comments'},
			{ data: 'ratings', name: 'ratings' },
            { data: 'review_posted_date', name: 'review_posted_date' ,searchable: false},
            { data: 'approval_status', name: 'approval_status' },
            <?php if(hasTask('admin/reviews/view')) { ?>
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
            if (confirm("Are you sure want to Approve All selected?")) {
                var ids = [];
                $('.deleteRow').each(function(){
                    if($(this).is(':checked')) { 
                        ids.push($(this).val());
                    }
                });

                var token;
                    token = $('input[name=_token]').val();
                var ids_string = ids.toString();  // array to string conversion
                url = '{{url('admin/reviews/bulkapprove')}}';
                
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
