@extends('layouts.admin')
@section('content')
 <script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>
  <script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/tinymce4.1/tinymce.min.js') }}"></script>
<div class="row">	
	<div class="col-md-12 ">
<!-- Nav tabs -->
<div class="pageheader">
<div class="media">
	<div class="pageicon pull-left">
		<i class="fa fa-home"></i>
	</div>
	<div class="media-body">
		<ul class="breadcrumb">
			<li><a href="#"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
			<li>@lang('messages.Portfolio')</li>
		</ul>
		<h4>@lang('messages.Add Portfolio')</h4>
	</div>
</div><!-- media -->
</div><!-- pageheader -->

<div class="contentpanel">
		@if (count($errors) > 0)
		<div class="alert alert-danger">
				<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">@lang('messages.Close')</span></button>
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
		@endif
<ul class="nav nav-tabs"></ul>

       {!!Form::open(array('url' => 'createportfolio', 'method' => 'post','class'=>'tab-form attribute_form','id'=>'portfolio_form','files' => true));!!} 



	<div class="tab-content mb30">
	<div class="tab-pane active" id="home3">
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Title')</label>
			<div class="col-sm-10">
			  <input type="text" name="title" required value="{!! old('title') !!}" maxlength="255" placeholder="@lang('messages.Title')"  class="form-control"  />
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Short Notes')</label>
			<div class="col-sm-10">
			  <input type="text" name="short_notes" required value="{!! old('short_notes') !!}"  maxlength="500" placeholder="@lang('messages.Short Notes')"  class="form-control"  />
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Url index')</label>
			<div class="col-sm-10">
			  <input type="text" name="portfolio_index"  value="{!! old('portfolio_index') !!}"  maxlength="255" placeholder="@lang('messages.Url index')"  class="form-control"  />
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Customer')</label>
			<div class="col-sm-10">
			  <input type="text" name="customer" required  value="{!! old('customer') !!}"  maxlength="255" placeholder="@lang('messages.Customer')"  class="form-control"  />
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Technology')</label>
			<div class="col-sm-10">
			  <input type="text" name="technology" required value="{!! old('technology') !!}"  maxlength="255" placeholder="@lang('messages.Technology')"  class="form-control"  />
			</div>
		</div>
		
<?php  $old =old('category_ids'); $cate=array(); if($old){  $cate=$old; } ?>
		<div class="form-group">
			<label class="col-sm-2 control-label ">@lang('messages.Categories')</label>
			<div class="col-sm-10">
			<select id="categories"  required name="category_ids[]" data-placeholder="@lang('messages.Choose One')" multiple class="width300">
			@foreach ($category as $val)
				<option value="{{ $val->id }}" <?php echo in_array($val->id,$cate)?"selected" :"" ;?> >{{  ucfirst($val->category_name) }}</option>	
			@endforeach
			</select>
			</div> 
		</div>
		<?php /**
		<div class="form-group">
			<label class="col-sm-2 control-label">Short Description</label>
			<div class="col-sm-10">
			  <textarea class="form-control" maxlength="255" name="short_description" id="short_description" rows="10"></textarea>
			</div>
		</div>
		* **/?>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Short description')</label>
			<div class="col-sm-10">
			  <input type="text" name="short_description" required value="{!! old('short_description') !!}" maxlength="500" placeholder="@lang('messages.Short description')"  class="form-control"  />
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Long description')</label>
			<div class="col-sm-10">
			  <textarea class="form-control" value="{!! old('long_description') !!}" required name="long_description" id="long_description" rows="10"></textarea>
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Web link')</label>
			<div class="col-sm-10">
			  <input type="text" name="web_link" value="{!! old('web_link') !!}" maxlength="255" placeholder="@lang('messages.Web link')"  class="form-control"  />
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Iphone link')</label>
			<div class="col-sm-10">
			  <input type="text" name="iphone_link" value="{!! old('iphone_link') !!}" maxlength="255" placeholder="@lang('messages.Iphone link')"  class="form-control"  />
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Android link')</label>
			<div class="col-sm-10">
			  <input type="text" name="android_link" value="{!! old('android_link') !!}" maxlength="255" placeholder="@lang('messages.Android link')"  class="form-control"  />
			</div>
		</div>

		 <div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Image')</label>
                <div class="col-sm-10">
                  <input type="file" name="image"  maxlength="255" placeholder="@lang('messages.Image')"  class="" value="" />
				
			</div>	
         </div>

         		  <div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Thumb Image')</label>
                <div class="col-sm-10">
                  <input type="file" name="thumb_image"    class="" value="" />
				
			</div>	
         </div>
								
       </div>
		<div class="panel-footer">
		<button class="btn btn-primary mr5" title="Save">@lang('messages.Save')</button>
		<button type="reset" class="btn btn-default" onclick="window.location='{{ url('admin/portfolio') }}'" title="Cancel" >@lang('messages.Cancel')</button>
		</div>
        </div>
      
<?php /** {!!Form::close();!!} **/ ?>
</div></div></div>
<script type="text/javascript">
$(document).ready(function(){
	$('#categories').select2(); 
});
</script>
<script type="text/javascript">
    $(window).load(function(){
        tinymce.init({
            menubar : false,statusbar : true,plugins: [
                "advlist autolink lists link image charmap print preview hr anchor pagebreak code",
                "emoticons template paste textcolor colorpicker textpattern"
            ],
            toolbar1: "code | insertfile undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image preview | forecolor backcolor | fontsizeselect",
            height:'450px',
            selector: "textarea#long_description"
         });         

    });
</script>
@endsection
