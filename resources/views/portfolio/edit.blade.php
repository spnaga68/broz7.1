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
		<h4>@lang('messages.Edit Portfolio') - <?php echo ucfirst($data->title); ?></h4>
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

       {!!Form::open(array('url' => ['updateportfolio', $data->id], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'portfolio_form','files' => true));!!} 
	<div class="tab-content mb30">
	<div class="tab-pane active" id="home3">
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Title')</label>
			<div class="col-sm-10">
			  <input type="text" name="title" required maxlength="255" placeholder="@lang('messages.Title')"  class="form-control"  value="<?php echo $data->title; ?>" />
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Short Notes')</label>
			<div class="col-sm-10">
			  <input type="text" name="short_notes" required maxlength="500" placeholder="@lang('messages.Short Notes')"  class="form-control"  value="<?php echo $data->short_notes; ?>" />
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Url index')</label>
			<div class="col-sm-10">
			  <input type="text" name="portfolio_index" value="<?php echo $data->portfolio_index; ?>" maxlength="255" placeholder="@lang('messages.Url index')"  class="form-control"  />
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Customer')</label>
			<div class="col-sm-10">
			  <input type="text" name="customer" required maxlength="255" placeholder="@lang('messages.Customer')"  class="form-control" value="<?php echo $data->customer; ?>"   />
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Technology')</label>
			<div class="col-sm-10">
			  <input type="text" name="technology"  maxlength="255" placeholder="@lang('messages.Technology')" value="<?php echo $data->technology; ?>"  class="form-control"  />
			</div>
		</div>
		
		<?php $categories =explode(',',$data->category_ids);  ?>
		<div class="form-group">
			<label class="col-sm-2 control-label ">@lang('messages.Categories')</label>
			<div class="col-sm-10">
			<select id="categories" required name="category_ids[]" data-placeholder="@lang('messages.Choose One')" multiple class="width300">
			@foreach ($category as $val)
				<option value="{{ $val->id }}"  <?php echo in_array($val->id,$categories)?"selected" :"" ;?> >{{  ucfirst($val->category_name) }}</option>	
			@endforeach
			</select>
			</div> 
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Short description')</label>
			<div class="col-sm-10">
			  <input type="text" name="short_description" required  maxlength="500" placeholder="@lang('messages.Short description')"  class="form-control"  value="<?php echo $data->short_description; ?>" />
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Long description')</label>
			<div class="col-sm-10">
			  <textarea class="form-control" required name="long_description" id="long_description" rows="10"><?php echo $data->long_description; ?></textarea>
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Web link')</label>
			<div class="col-sm-10">
			  <input type="text" name="web_link" value="<?php echo $data->web_link; ?>"  maxlength="255" placeholder="@lang('messages.Web link')"  class="form-control"  />
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Iphone link')</label>
			<div class="col-sm-10">
			  <input type="text" name="iphone_link" value="<?php echo $data->iphone_link; ?>" maxlength="255" placeholder="@lang('messages.Iphone link')"  class="form-control"  />
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-sm-2 control-label">@lang('messages.Android link')</label>
			<div class="col-sm-10">
			  <input type="text" name="android_link"  value="<?php echo $data->android_link; ?>" maxlength="255" placeholder="@lang('messages.Android link')"  class="form-control"  />
			</div>
		</div>
		
		<div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Image')</label>
                <div class="col-sm-10">
                  <input type="file" name="image"  value="" />
				
			</div>	
         </div>
		<?php if($data->image){ ?>
		<div class="form-group">
			<label class="col-sm-2 control-label"></label>
			<div class="col-sm-10">
			 <img src="<?php echo url('/assets/admin/base/images/portfolio/thumb/'.$data->image.''); ?>" title="Nextbrain" alt="Nextbrain">
			 </div>	
		  </div>
		  <?php } ?>

		  <div class="form-group">
                <label class="col-sm-2 control-label">@lang('messages.Thumb Image')</label>
                <div class="col-sm-10">
                  <input type="file" name="thumb_image"    class="" value="" />
				
			</div>	
         </div>
		<?php if($data->thumb_image){ ?>
		<div class="form-group">
			<label class="col-sm-2 control-label"></label>
			<div class="col-sm-10">
			 <img width="50" height="50" src="<?php echo url('/assets/admin/base/images/portfolio/thumbimage/'.$data->thumb_image.''); ?>" title="Nextbrain" alt="Nextbrain">
			 </div>	
		  </div>
		  <?php } ?>
		  	
       </div>
		<div class="panel-footer">
		<button class="btn btn-primary mr5" title="Update" >@lang('messages.Update')</button>
		<button type="reset" title="Cancel" class="btn btn-default" onclick="window.location='{{ url('admin/portfolio') }}'">@lang('messages.Cancel')</button>
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

