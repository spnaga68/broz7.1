@extends('layouts.admin')
@section('content')
<link href="{{ URL::asset('assets/admin/base/css/bootstrap-timepicker.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/admin/base/css/select2.css') }}" media="all" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<style>
  .container{margin-top:30px;}
  .form-group{float:left;width:100%;} 
  /*.btn{margin-bottom:10px;width:10%;}*/
   .form_filed{float:left;width:100%;border: 1px solid #ddd;padding: 20px;border-radius: 4px;}
 .form-control{float:left;width:30%;margin-right:20px}
  .cancel{float:left;width:10%;}
</style>

<!-- Nav tabs -->
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
			<h4>@lang('messages.Edit Product') - {{ $data->product_name}}</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->


<div class="contentpanel">
    <div class="col-md-12">
        <div class="row panel panel-default">
            <div class="grid simple">
                @if (count($errors) > 0)
                <div class="alert alert-danger" style="display:none">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li><?php echo trans('messages.' . $error); ?> </li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <div class="alert alert-danger" style="display:none">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>
                    <ul id="error_list">
                    </ul>
                </div>
                <div class="admin_sucess_common" id="error_time" style="display:none;">
                    <div class="admin_sucess">
                        <div class="alert alert-info" id="errors"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>{{ Session::get('message') }}</div>
                    </div>
                </div>
	<ul class="nav nav-justified nav-wizard nav-pills">
		<li @if(old('tab_info')=='login_info' || old('tab_info')=='') class="active" @endif><a href="#login_info" class="login_info" data-toggle="tab"><strong>@lang('messages.General Information')</strong></a></li>
		<li @if(old('tab_info')=='delivery_info') class="active" @endif ><a href="#delivery_info" class="delivery_info" data-toggle="tab"><strong>@lang('messages.Data Information')</strong></a></li>
	
	</ul>
	{!!Form::open(array('url' => ['update_product',$data->id], 'method' => 'post','class'=>'panel-wizard','id'=>'edit_product_form','files' => true));!!}
		<div class="tab-content tab-content-simple mb30 no-padding" >
			<div class="tab-pane active" id="login_info">
				<legend>@lang('messages.General Information')</legend>
				<div class="form-group">
					<label class="col-sm-3 control-label">@lang('messages.Product Name') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<?php //echo"<pre>";print_r($infomodel);exit(); ?>
						<?php $i = 0;foreach ($languages as $langid => $language): ?>
						<div class="input-group translatable_field language-<?php echo $language->id; ?>" <?php if ($i > 0): ?>style="display: none;"<?php endif;?>>
							  <input type="text" name="product_name[<?php echo $language->id; ?>]" id="suffix_<?php echo $language->id; ?>"  placeholder="<?php echo trans('messages.Product Name') . trans('messages.' . '(' . $language->name . ')'); ?>" class="form-control" value="{{$infomodel->getLabel('product_name',$language->id,$data->id)}}" maxlength="50"  />
							<div class="input-group-btn">
								<button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button"><?php echo $language->name; ?> <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right">
									<?php foreach ($languages as $sublangid => $sublanguage): ?>
										<li><a href="javascript:YL.Language.fieldchange(<?php echo $sublanguage->id; ?>)"> <?php echo trans('messages.' . $sublanguage->name); ?></a></li>
									<?php endforeach;?>
								</ul>
							</div><!-- input-group-btn -->
						</div>
						<?php $i++;endforeach;?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">@lang('messages.Product URL')</label>
					<div class="col-sm-7">
					  <input type="text" name="product_url" value="{{ $data->product_url }}"  maxlength="255" placeholder="@lang('messages.Product URL')"  class="form-control" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">@lang('messages.Description') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
					<?php $i = 0;foreach ($languages as $langid => $language): ?>
						<div class="input-group translatable_field language-<?php echo $language->id; ?>" <?php if ($i > 0): ?>style="display: none;"<?php endif;?>>
							<textarea class="form-control" rows="4" maxlength="250" name="description[<?php echo $language->id; ?>]" id="suffix_<?php echo $language->id; ?>"  placeholder="<?php echo trans('messages.Description') . trans('messages.' . '(' . $language->name . ')'); ?>" class="form-control" >{{$infomodel->getLabel('description',$language->id,$data->id)}}</textarea>
							<div class="input-group-btn">
								<button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button"><?php echo $language->name; ?> <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right">
									<?php foreach ($languages as $sublangid => $sublanguage): ?>
									   <li><a href="javascript:YL.Language.fieldchange(<?php echo $sublanguage->id; ?>)"> <?php echo trans('messages.' . $sublanguage->name); ?></a></li>
									<?php endforeach;?>
								</ul>
							</div><!-- input-group-btn -->
						</div>
					<?php $i++;endforeach;?>
					<span class="help-text">@lang('messages.Max length 250')</span>
				</div>
				</div>
				<div class="form-group">
					
						<label class="col-sm-3 control-label">@lang('messages.Vendor Category') <span class="asterisk">*</span></label>
						<div class="col-sm-7">
							<select id="head_categories" name="head_category" class="form-control" >
							<option value="">@lang('messages.Select vendor category')</option>
							<?php $cdata = gethead_categories();?>
							@if (count($cdata) > 0)
								@foreach ($cdata as $key => $val)
									<option value="{{ $val->id }}" <?php echo ($data->category_id == $val->id) ? 'selected="selected"' : ''; ?>>{{ $val->category_name }}</option>
								@endforeach
							@endif
						</select>
						</div>
				    </div> 
					<div class="form-group">
						<label class="col-sm-3 control-label">@lang('messages.Category Name') <span class="asterisk">*</span></label>
						<div class="col-sm-7"> <?php // echo $data->category_id; ?>
							<select name="category" id="category_id" class="form-control" >
								<option value="">@lang('messages.Select Category')</option>
								<?php $cdata = getSubCategoryLists(1, $data->category_id); ?>
								@if (count($cdata) > 0)
									@foreach ($cdata as $key => $val)
										<option value="{{ $val->id }}" <?php echo ($data->sub_category_id == $val->id) ? "selected" : ""; ?>>{{ $val->category_name }}</option>
									@endforeach
								@endif
							</select>
						</div>
				   </div>
					<div class="form-group">
						<label class="col-sm-3 control-label">@lang('messages.Sub Category Name') <span class="asterisk">*</span></label>
						<div class="col-sm-7">
							<select id="sub_category" name="sub_category" data-placeholder="@lang('messages.Sub Category Name')" class="form-control">
								<option value="">@lang('messages.Select sub category name')</option>
							<?php $sub_category = getSubCategoryListsupdated(1, $data->sub_category_id);?>
							@foreach ($sub_category as $val)
								<option value="{{ $val->id }}" <?php echo ($val->id == $data->child_category_id) ? "selected" : ""; ?> >{{  ucfirst($val->category_name) }}</option>
							@endforeach
							</select>
						</div>
				    </div>

				
				<!-- 	<div class="form-group">
						<label class="col-sm-3 control-label">@lang('messages.Product Image')</label>
						<div class="col-sm-7">
							<input type="file" name="product_image" placeholder="@lang('messages.Product Image')" />
							<span class="help-text">@lang('messages.Please upload 150X145 images for better quality')</span>
							<div class="mb20"></div>
							<img src="<?php echo url('/assets/admin/base/images/products/list/' . $data->product_image . ''); ?>" class="thumbnail img-responsive" alt="logo">
						</div>
					</div>
				 -->
					<div class="form-group">
						<label  class="col-sm-3 control-label">@lang('messages.Publish Status') <span class="asterisk">*</span></label>
						<div class="col-sm-7">
							<?php $checked = "";if ($data->approval_status) {$checked = "checked=checked";}?>
							<input type="checkbox" class="toggle" name="publish_status" data-size="small" <?php echo $checked; ?> data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
						</div>
					</div>
					<div class="form-group">
						<label  class="col-sm-3 control-label">@lang('messages.Status') <span class="asterisk">*</span></label>
						<div class="col-sm-7">
							<?php $checked1 = "";if ($data->status) {$checked1 = "checked=checked";}?>
							<input type="checkbox" class="toggle" name="active_status" data-size="small" <?php echo $checked1; ?> data-on-text="@lang('messages.Yes')" data-off-text="@lang('messages.No')" data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="1" />
						</div>
					</div>
				</div>
			<div class="tab-pane" id="delivery_info">
				<legend>@lang('messages.Data Information')</legend>
				<div class="form-group">
					<label class="col-sm-3 control-label ">@lang('messages.Weight Class') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<select name="weight_class" id="weight_class" class="form-control">
							<option value="">@lang('messages.Select Weight Class')</option>
							<?php $weight_class = getWeightClass();?>
								@foreach($weight_class as $list)
									<option value="{{$list->id}}" <?php echo ($data->weight_class_id == $list->id) ? 'selected="selected"' : ''; ?> >{{$list->title}}</option>
								@endforeach
						</select>
					</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Weight Value') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<input type="text" name="weight_value" value="{!! $data->weight !!}"  maxlength="255" placeholder="@lang('messages.Weight Value')"  class="form-control" />
						<span class="help-block">@lang('messages.Weight Value') @lang('messages.weight_value_help_text')</span>
					</div>
				</div>
					<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Total Quantity') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<input type="text" name="total_quantity" value="{!! $data->quantity !!}"  maxlength="255" placeholder="@lang('messages.Total Quantity')"  class="form-control" />
					</div>
				</div>
				
				
				<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Item Limit') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<input type="text" name="item_limit" value="{!! $data->item_limit !!}"  maxlength="255" placeholder="@lang('messages.Item Limit')"  class="form-control" />
					</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Barcode') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<input type="text" name="barcode" value="{!! $data->barcode !!}"  maxlength="255" placeholder="@lang('messages.Barcode')"  class="form-control" />
					</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-3 control-label">@lang('messages.Adjust Weight Value') <span class="asterisk">*</span></label>
					<div class="col-sm-7">
						<?php $checked = "";if ($data->adjust_weight) {$checked = "checked=checked";} //print_r($checked);exit; ?>
						<input type="checkbox" name="adjust_weight" value="1" <?php echo $checked;?>  maxlength="255" placeholder="@lang('messages.Adjust Weight Value')"  class="form-control" />

					</div>
				</div>
				<?php //print_r($data->adjust_weight);exit; ?>

			</div>

			 <div class="form-group Loading_Img" style="display:none;">
               <div class="col-sm-4">
                  <i class="fa fa-spinner fa-spin fa-3x"></i><strong style="margin-left: 3px;">@lang('messages.Processing...')</strong>
               </div>
            </div>
         </div>
                    <!-- panel-default -->
                </div>
<!--                 @if(old('tab_info')!='more_description')
 -->                <div class="panel-footer Submit_button" id="sub_btn">
                    <input type="hidden" name="tab_info" class="tab_info" value="">
                      <button type="submit" onclick="HideButton('Submit_button','Loading_Img');" onsubmit="HideButton('Submit_button','Loading_Img');" class="btn btn-primary mr5" title="Save">@lang('messages.Save')</button>
                        <button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('admin/products') }}'">@lang('messages.Cancel')</button>
						<!-- <input type="submit" name="desc" value="description" id="description" class="btn btn-primary mr5" onclick="window.location='{{ url('/dynamic?'.$data->id) }}'">
						 -->
						<!-- <input type ="text" size="" value="<?php //if (isset($data->id)) {echo json_encode($data->id);}?>"> -->

                </div><!-- panel-footer -->
<!--                 @endif
 -->
			{!!Form::close();!!}
		</div>
	</div>
</div>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/jquery-ui-1.10.3.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/bootstrap-timepicker.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>
<script type="text/javascript">
$( document ).ready(function() {

	 $(document).ready(function(){

	 	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) { //to hide the submit btn for more description
		  var target = $(e.target).attr("href") // activated tab
		  if(target == "#more_description")
		  {	  
		  	$("#sub_btn").hide();
		  }else
		  {
		  	$("#sub_btn").show();

		  }
	
		});

      var postURL = "/api/data";
      var i=0;
      var j =x=0;
       
      $("#add_category").click(function(){
        j++;
        $("#cat_val").val(j);
          
        $('#check').append('<div id="cat_div_'+j+'"><div class="form-group" id="cat_row_'+j+'" name =cat_row[] ><input type="textbox"  class="form-control" name="featureName_'+j+'[]"  value="" required=""><button type="button" class="btn cancel btn_cat_remove">Cancel</button></div><div class="form-group" id="category_'+j+'_'+i+'"><input type="text" name="name_'+j+'[]" placeholder="Enter your Names" class="form-control name_list" required="" /><input type="text" name="value_'+j+'[]" placeholder="Enter value" class="form-control name_list" required /><button type="button" name="add_'+j+'" id="'+j+'" class="btn btn-success add_more">Add  More</button></div></div>');
      });
      $(document).on('click', '.btn_cat_remove', function(){
        /*  var button_id = $(this).attr("id");
        var ff = $('tr').attr('id');
        $('#cat_row_'+button_id+'').remove();*/
        //alert($(this).parent().parent().closest('div').attr('id'));return false;
       // var tr_id =$(this).closest('tr').prop('id');
        var colse_div = $(this).parent().parent().closest('div').attr('id');
        var cat_val =  $("#cat_val").val();
        var cat_val =cat_val - 1;
        j--;
        $("#cat_val").val(cat_val);
        $('#'+colse_div).remove();
        //$('#check').remove();
      });

      $(document).on('click', '.add_more', function(){
          var button_id = $(this).attr("id");
          var colse_div = $(this).parent().parent().closest('div').attr('id');

         // alert( $(this).parent().closest('div').attr('id'));

          var child_last_div = $(this).parent().closest('div').attr('id');

          //$(this).parent('#content').children('.comments')
         // alert('#'+child_last_div);return false;
         // alert($(this).parent().parent().closest('div').children().last().attr('id'));return false;
          i++;
            $("#"+child_last_div).after('<div class="form-group" id="category_'+i+'"><input type="text" name="name_'+button_id+'[]" placeholder="Enter your Names" class="form-control name_list" required="" /><input type="text" name="value_'+button_id+'[]" placeholder="Enter value" class="form-control name_list" required /><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove">X</button></div>');


      });
        $(document).on('click', '.btn_remove', function(){
           var button_id = $(this).attr("id");
           alert(button_id);
          // alert(button_id);return false;
           $('#category_'+button_id).remove();
      });
    
       $('#submit_des').click(function(){
      	    var form = $(this).closest('form');
    		var serialize = form.serialize();
           // console.log(serialize);return false;
            //console.log("haiiiiii"+ $('#more_desc').serialize());return false;
           $.ajax({
                url:postURL,
                method:"POST",
                data:serialize,
                type:'json',
                success:function(data)
                {
                  alert(data );
                  location.reload();

                }
           });
      });
    });

    $('#vendor_id').select2();
	//$('#sub_category').select2();
	@if(old('tab_info')=='login_info')
		$('.tab_info').val('login_info');
		$('#login_info').show();
		$('#delivery_info').hide();
		$('#more_description').hide();

	@elseif(old('tab_info')=='delivery_info')
		$('.tab_info').val('delivery_info');
		$('#login_info').hide();
		$('#delivery_info').show();
		$('#more_description').hide();

	@elseif(old('tab_info')=='more_description')
		$('.tab_info').val('more_description');
		$('#login_info').hide();
		$('#delivery_info').hide();
		$('#more_description').show();
	@endif
});
$(".login_info").on("click", function(){
	$('.tab_info').val('login_info');
	$('#delivery_info').hide();
	$('#login_info').show();
	$('#more_description').hide();
});
$(".delivery_info").on("click", function(){
	$('.tab_info').val('delivery_info');
	$('#delivery_info').show();
	$('#login_info').hide();
	$('#more_description').hide();

});
$(".more_description").on("click", function(){
	$('.tab_info').val('more_description');
	$('#more_description').show();
	$('#delivery_info').hide();
	$('#login_info').hide();
});
$(window).load(function(){
	$('form').preventDoubleSubmission();
});


// <?php
// if (isset($_POST["descs"])) {

// 	echo "hai";
// }
// ?>





//       var postURL = "/dynamic";

// $('#description').click(function(){
// $.post(
//     {

//      url:postURL,
//       method:"POST",
//        data:"hai",
// success:function(data)
//                 {

//           alert('' + data);


//                 }
//     });


//   });

/** $('#category_id').change(function(){
	var cid, token, url, data,language;
	token = $('input[name=_token]').val();
	cid = $('#category_id').val();
	url = '{{url('list/SubCategoryList')}}';
	language = 1;
	data = {cid: cid,language:language};
	$.ajax({
		url: url,
		headers: {'X-CSRF-TOKEN': token},
		data: data,
		type: 'POST',
		datatype: 'JSON',
		success: function (resp) {
			//console.log('in--'+resp.data);
			$('#sub_category').empty();
			if(resp.data==''){
				$('#sub_category').append($("<option></option>").attr("value","").text('No data there..'));
			} else {
				$.each(resp.data, function(key, value) {
					//console.log(value['id']+'=='+value['city_name']);
					$('#sub_category').append($("<option></option>").attr("value",value['id']).text(value['category_name']));
			   });
			}
		}
	});
});**/

$('#head_categories').change(function(){
	$("#vendor_id").val(null).trigger("change")
	var cid, token, url, data,language;
	language = 1;
	token = $('input[name=_token]').val();
	cid = $('#head_categories').val();
	url = '{{url('list/getVendorcategorylist')}}';
	data = {cid: cid,language:language};
	var ajax1=$.ajax({
		url: url,
		headers: {'X-CSRF-TOKEN': token},
		data: data,
		type: 'POST',
		datatype: 'JSON',
		success: function (resp) {
			//console.log('in--'+resp.data);
			$('#vendor_id').empty();
			$('#category_id').empty();
			if(resp.data==''){
				//$('#vendor_id').append($("<option></option>").attr("value","").text('No data there..'));
			} else {
				//$('#vendor_id').append($("<option></option>").attr("value","").text('Select Vendor'));
				$.each(resp.data, function(key, value) {

					//console.log(value['id']+'=='+value['city_name']);
					$('#vendor_id').append($("<option></option>").attr("value",value['id']).text(value['vendor_name']));
			   });
			}
			if(resp.cdata==''){
				$('#category_id').append($("<option></option>").attr("value","").text('No data there..'));
			} else {
				$('#category_id').append($("<option></option>").attr("value",-1).text('Select Product Category'));
				$.each(resp.cdata, function(key, value) {
					//console.log(value['id']+'=='+value['city_name']);
					$('#category_id').append($("<option></option>").attr("value",value['id']).text(value['category_name']));
			   });
			}
		}
	});

});
 $('#edit_product_form').submit(function(evt) {
        evt.preventDefault();
        var formData = new FormData(this);
        //console.log(formData);return false;
        var c_url ='/update_product/{{$data->id}}';
        token = $('input[name=_token]').val();

        $.ajax({
            url: c_url,
            headers: {'X-CSRF-TOKEN': token},
            data: formData,
            processData: false,
            contentType: false,
            type: 'POST',
            datatype: 'json',
            success: function (resp)
            {
                data = JSON.parse(resp);console.log(data);
                if(data.status == 200)
                {
                    window.location='{{ url('admin/products') }}';
                }
                else
                {
                    $( '.alert-danger' ).show();
                    $('#error_list').html(data.errors);
                     $( '.Loading_Img' ).hide();
                    $( '.Submit_button' ).show();
                }
            },
            error:function(resp)
            {
                console.log('out--'+data);
                return false;
            }
        });
        return false;
    });
    $('#category_id').change(function(){
		var cid, token, url, data,language, head_category;
		token = $('input[name=_token]').val();
		cid = $('#category_id').val();
		head_category = $('#head_categories').val();
		language = 1;
		url = '{{url('list/SubCategoryListUpdated')}}';
		data = {cid: cid,language:language,head_category:head_category};
		$.ajax({
			url: url,
			headers: {'X-CSRF-TOKEN': token},
			data: data,
			type: 'POST',
			datatype: 'JSON',
			success: function (resp) {
				//console.log('in--'+resp.data);
				$('#sub_category').empty();
				if(resp.data==''){
					$('#sub_category').append($("<option></option>").attr("value","").text('No data there..'));
				} else {
					$.each(resp.data, function(key, value) {
						//console.log(value['id']+'=='+value['city_name']);
						$('#sub_category').append($("<option></option>").attr("value",value['id']).text(value['category_name']));
				   });
				}
			}
		});
	});
</script>




@endsection
