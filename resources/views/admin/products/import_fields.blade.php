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
           
                <?php if (isset($data)) {
                    if(isset($data['uploaded'])) { if(count($data['uploaded']) != 0){ ?>
                    <div class="text-success">Excel Data Imported successfully. </div>
                    <?php foreach ($data['uploaded'] as $key => $value) {?>
                        <td >SNO</td>
                        <td >#{{$value}}</td>
                    <?php } ?>

                <?php }else{ ?>
                    <div class="text-danger">Their is some problem while uploading data</div>

                <?php }} }?>
                <div>
                    <table style="width:100%">
                        <?php if(isset($data['prdouctNotexist']) && count($data['prdouctNotexist']) !=0 ){  ?>
                            <tr>
                                <th class="text-warning">Product Not exist</th>
                            </tr>
                            <tr>
                                <td >SNO</td>
                                <?php foreach ($data['prdouctNotexist'] as $key => $value) {?>
                                    <td >#{{$value}}</td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                        <tr></tr>
                        <br/>
                        <?php if(isset($data['recorde_exist']) && count($data['recorde_exist']) !=0 ){  ?>
                            <tr>
                                <th class="text-warning">This record are already exist in db</th>
                            </tr>
                            <tr>
                                <td >SNO</td>
                                <?php 
                                    foreach ($data['recorde_exist'] as $key => $value) {?>
                                    <td >#{{$value}}</td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
                <br/>
                <div><a href="{{url('admin/products/bulkimport')}}" class="btn btn-default">Go back to upload again</a></div>
             

              
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
