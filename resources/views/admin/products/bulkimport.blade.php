@extends('layouts.admin')
@section('content')
<link href="{{ URL::asset('assets/admin/base/css/bootstrap-timepicker.min.css') }}" media="all" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/admin/base/css/select2.css') }}" media="all" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/admin/base/plugins/switch/css/bootstrap3/bootstrap-switch.min.css') }}" media="all" rel="stylesheet" type="text/css"/>
<!-- Nav tabs -->
<div class="pageheader">
	<div class="media">
		<div class="pageicon pull-left">
			<i class="fa fa-home"></i>
		</div>
		<div class="media-body">
			<ul class="breadcrumb">
				<li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
				<li>@lang('messages.Outlet Products')</li>
			</ul>
			<h4>@lang('messages.Add Outlet Product')</h4>
		</div>
	</div><!-- media -->
</div><!-- pageheader -->

<div class="contentpanel">
    <div class="col-md-12">
        <div class="row panel panel-default">
        	<div class="container">
			   <h3 align="center">Import Excel File in Laravel</h3>
			    <br />
			   @if(count($errors) > 0)
			    <div class="alert alert-danger">
			     Upload Validation Error<br><br>
			     <ul>
			      @foreach($errors->all() as $error)
			      <li>{{ $error }}</li>
			      @endforeach
			     </ul>
			    </div>
			   @endif

			   @if($message = Session::get('success'))
			   <div class="alert alert-success alert-block">
			    <button type="button" class="close" data-dismiss="alert">×</button>
			           <strong>{{ $message }}</strong>
			   </div>
			   @endif
           	<form method="post" enctype="multipart/form-data" action="{{ url('/bulk_import') }}">
		    	{{ csrf_field() }}
			    <div class="form-group">
			     <table class="table">
			      <tr>
			       <td width="40%" align="right"><label>Select File for Upload</label></td>
			       <td width="30">
			        <input type="file" name="select_file" />
			       </td>
			       <td width="30%" align="left">
			        <input type="submit" name="upload" class="btn btn-primary" value="Upload">
			       </td>
			      </tr>
			      <tr>
			       <td width="40%" align="right"></td>
			       <td width="30"><span class="text-muted">.xls, .xslx</span></td>

			       <td width="30"><a href="{{url('/outletproduct.xlsx')}}" download>click to download sample excel file</a></td>
			       <td width="30%" align="left"></td>
			      </tr>
			    </table>
			</div>
			   </form>
              
		</div>
	</div>
</div>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/jquery-ui-1.10.3.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/plugins/switch/js/bootstrap-switch.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/bootstrap-timepicker.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/admin/base/js/select2.min.js') }}"></script>


<script type="text/javascript">


</script>
@endsection
