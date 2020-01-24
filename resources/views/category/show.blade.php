@extends('layouts.admin')
@section('content')
<div class="container">

	@if(Session::has('message'))
		<div class="alert alert-success">
				<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
			<span>{!!Session::get('message');!!}</span>
		</div>
	@endif

<h1>View Blog - <?php echo $data->title; ?></h1>
	

        <div class="form-horizontal"> 

                       <!-- Task Name -->
            <div class="form-group">
                <label for="title" class="col-sm-3 control-label">Title : </label>

                <div class="col-sm-3">
						<?php echo $data->title; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="index" class="col-sm-3 control-label">Url index : </label>

                <div class="col-sm-6">
                         <?php echo $data->index; ?>
                </div>
            </div>

			<div class="form-group">
                <label for="content" class="col-sm-3 control-label">Content : </label>

                <div class="col-sm-6">
                <?php echo $data->content; ?>
          
                </div>
            </div>
					
        </div>
		
</div>
@endsection


