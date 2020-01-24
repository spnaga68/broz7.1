@extends('layouts.admin')
@section('content')
 <div class="row activelogin">
<div class="pageheader">
<div class="media">
    <div class="pageicon pull-left">
        <i class="fa fa-home"></i>
    </div>
    <div class="media-body">
        <ul class="breadcrumb">
            <li><a href="{{ URL::to('admin/dashboard') }}"><i class="glyphicon glyphicon-home"></i>@lang('messages.Admin')</a></li>
            <li>@lang('messages.View User')</li>
        </ul>
        <h4>@lang('messages.View User')  - {{$users->name}}</h4>
    </div>
</div><!-- media -->
</div><!-- pageheader -->

    <div class="col-sm-4 col-md-3 profile_bg">
        <div class="text-center">
            
         <?php  if(file_exists(base_path().'/public/assets/admin/base/images/admin/profile/thumb/'.$users->image) && $users->image != '') { ?>
            <img src="<?php echo url('/assets/admin/base/images/admin/profile/thumb/'.$users->image.''); ?>" class="img-circle img-offline  img-profile" alt="No image">
            
        <?php } else{  ?>
            <img src=" {{ URL::asset('assets/admin/base/images/a2x.jpg') }} " class="img-circle">
        <?php } ?>
            
            <h4 class="profile-name mb5">&nbsp;<?php echo $users->name;?></h4>

            <div><i class="fa fa-envelope-o"></i> <?php echo $users->email;?></div>

             <div class="mb20"></div>
            <div class="btn-group">
                <?php if($users->user_type == 1 || $users->user_type == 2 ) { ?>
                    <button class="btn btn-primary btn-bordered" onclick="window.location='{{ url('admin/editprofile/'.$users->id) }}'" ><i class="fa fa-edit"></i>&nbsp;@lang('messages.Edit')</button>
                <?php } else { ?>
                    <button class="btn btn-primary btn-bordered" onclick="window.location='{{ url('admin/users/edit/'.$users->id) }}'" ><i class="fa fa-edit"></i>&nbsp;@lang('messages.Edit')</button>
                <?php } ?>
            </div>
                    <h5 class="md-title">@lang('messages.Other Info'):</h5>
        <?php if($users->date_of_birth):?>
            <div class="pdb10"><?php echo "DOB: ". date("d/m/Y",strtotime($users->date_of_birth));?></div>
        <?php endif;?>
        <div class="pdb10"><?php echo "Registered on: ". date("d/m/Y",strtotime($users->created_date));?></div>
        <div class="pdb10"><?php echo "User Status: ". ($users->status ? '<span class="label label-success">'."Enabled".'</span>':
        '<span class="label label-danger">'."Disabled".'</span>');?></div>
        </div><!-- text-center -->
    </div><!-- col-sm-4 col-md-3 -->

    <div class="col-sm-8 col-md-9">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs nav-line">
            <li class="active"><a href="#activities" class="activities" data-toggle="tab"><strong>@lang('messages.Activities')</strong></a></li>
            <li><a href="#roles"  class="roles" data-toggle="tab"><strong>@lang('messages.Roles')</strong></a></li>
        </ul>

        
        <!-- Tab panes -->
        <div class="tab-content nopadding noborder">
            <div class="tab-pane active" id="activities">
                <div class="activity-list">
                    <?php if(count($activities)):?>
                    <?php foreach($activities as $activity): ?>
                        <div class="media">
                            <a class="pull-left" href="#">
                               <!-- <img class="media-object img-circle" src="images/photos/user1.png" alt="" />-->
                            </a>
                            <div class="media-body">
                                <strong><?php echo $users->name;?> </strong> <i class="glyphicon glyphicon-chevron-right"></i> <?php echo $activity->message;?>. <br />
                                <small>@lang('messages.Ip:') <?php echo $activity->ip_;?></small> <br />
                                <small>@lang('messages.Device:') <?php echo $activity->device;?></small> <br />
                                <small class="text-muted"><?php echo nicetime($activity->date);?></small>
                            </div>
                        </div><!-- media -->
                    <?php endforeach;?>
                    <?php else:?>
                        @lang('messages.No Recent Activities found')
                    <?php endif;?>
                    <div id="activity_results"></div>
                    <span class="animation_load" style="text-align: center; padding: 10px 0; display: none;">@lang('messages.Loading')</span>
                </div><!-- activity-list -->
                <?php  echo $activities->render(); ?>
                <!--<button class="btn btn-white btn-block">Show More</button> -->
            </div><!-- tab-pane -->
            <div class="tab-pane active" id="roles">
                <div class="role-list">
                    <?php $i = 0;
$html = ''; if(count(getuserrole($users->id))) {
                            foreach(getuserrole($users->id) as $roles) {
                                $tagbgcolor =
                                $tagtextcolor = '';
                                if(isset($roles->tag_bg_color) && $roles->tag_bg_color) {
                                    $tagbgcolor = 'border-left-color:'.$roles->tag_bg_color.';border-right-color:'.$roles->tag_bg_color.';border-bottom-color:'.$roles->tag_bg_color.';border-top-color:'.$roles->tag_bg_color.";";
                                }
                                if(isset($roles->tag_text_color) && $roles->tag_text_color) {
                                    $tagtextcolor = 'color:'.$roles->tag_text_color.";";
                                }
                                $html .= '<li><div class="row role-box" style="'.$tagbgcolor.' border-image: none;border-radius: 1px;border-style: solid;border-width: 1px 1px 1px 4px;padding: 10px;">
                                                    <div class="col-sm-3"><i class="fa fa-building-o" style="font-size: 40px;"></i></div>
                                                    <div class="col-sm-9">
                                                        <div class="role-type" style="'.$tagtextcolor.'">'.$roles->role_name.'</div>
                                                    </div>
                                            </div></li>';
                                $i++;
                            }
                        }
                        ?>
                        
                     <?php if($i >0):?> 
                        <ul class="view-roles-list">
                    <?php endif;?>
                    <?php echo $html;?>
                    <?php if($i >0):?>
                        </ul>
                    <?php endif;?>
                    <?php if($i <=0):?>
                        @lang('messages.No Roles assigned')
                    <?php endif;?>
                </div><!-- activity-list -->
                
                <!--<button class="btn btn-white btn-block">Show More</button> -->
            </div><!-- tab-pane -->
 </div>

            

</div><!-- row -->
</div>

<script type="text/javascript">
    $( document ).ready(function() {
    $('#roles').hide();
    $('#activities').show
    $(".activities").on("click", function(){
        $('#activities').show();
        $('#roles').hide();
    });
    $(".roles").on("click", function(){
        $('#activities').hide();
        $('#roles').show();
    });
});            
 
</script>

<style>
.loading-image {
  position: absolute;
  top: 50%;
  left: 50%;
  z-index: 10;
}
.loader
{
    display: none;
    width:200px;
    height: 200px;
    position: fixed;
    top: 50%;
    left: 50%;
    text-align:center;
    margin-left: -50px;
    margin-top: -100px;
    z-index:2;
    overflow: auto;
}
.cursor_pointer {
    cursor:pointer;
}
</style>
@endsection

