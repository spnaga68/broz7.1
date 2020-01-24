@extends('layouts.front')
    @section('content')
<style type="text/css">
  .clickable {
    cursor: pointer;
}
</style>


  <!-- navtab -->
 {!!Form::open(array('url' => ['common_promotion'], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'walletadd','files' => true));!!}
    {!!Form::close();!!} 


  <div id="page-container" class="page-wrap">
    <div class="container topmargin">
      <section id="navtab">
        <ul class="nav  nav-pills nav-fill shadow-sm title4 nav-tabs" id="myTab" role="tablist">
          <li class="nav-item">
            <a class="nav-link title4 active"  id="wallet-tab" href="#wallet" data-toggle="tab" >WALLET</a>
          </li>
          <div class="hr-h">
          </div>
          <li class="nav-item ">
            <a class="nav-link  title4"  id="profile-tab" href="#profile" data-toggle="tab" >PROFILE</a>
          </li>

        </ul>
  </section>
  <!-- endnavtab -->
  <div class="tab-content" id="myTabContent">
          <input type="hidden" name="id" id="cust_id" value="{{$user_details->id}}">
          <input type="hidden" name="verfiy_pin" id="verfiy_pin" value="{{$user_details->verfiy_pin}}">

  <!-- wallet -->
    <div class="tab-pane fade show active" id="wallet" role="tabpanel" aria-labelledby="wallet-tab">
  <section id="wallet">
      <div class="bg-cuslight p-1">
        
        @if($user_details->offer_wallet) <p class="title2 p-1"><b>*USE UPTO {{$user_details->wallet- $user_details->offer_wallet}} AED FROM OFFER WALLET </b></p>@endif

        <div class="text-center">
            <p class="title3 mb-1 p-0">{{$user_details->currency_code}}</p>
            <h2 class="title1 p-0 mb-3">{{$user_details->wallet}}</h2>
            <button class="btn btn-success mb-3"> <a class="text-white" href="{{URL::to('promotion')}}">ADD AMOUNT</a></button>
        </div>
      </div>
      
    </section>

  <?php
    $date = isset($user_details->verify_pin_time)?$user_details->verify_pin_time:'';
    if($date){ 
      $to_time=strtotime(date("Y-m-d H:i:s"));
      $from_time=strtotime($date); 
      $diff_time =  round(abs($to_time - $from_time) / 60,2);
      if($diff_time <= 5)
        { if($user_details->verfiy_pin !=0){  ?>
          
          <section id="user-otp">
            <h2 class="m-0 p-2 text-primary card-title1">USER OTP : {{$user_details->verfiy_pin}}</h2>
          </section>

        <?php }}}?>

     <?php  $paymentHsitory =paymentHsitory($api,$user_details->id);$detail = (object)$paymentHsitory['detail'];?>
             <p class="title1 m-0 p-2">PAYMENT HISTORY</p>

    <section id="history" style="width: 100%; height: 600px; overflow-y: scroll;">
        <?php if($detail){ ?>

        <?php foreach($detail as $value){ ?>
          <?php //echo"<pre>";  print_r($value);exit
          ;?>

        <!-- card -->
     <div class="card shadow-sm my-1">
                <div class="d-flex justify-content-between p-2">
                    <div class="d-flex align-items-center">
                        <img class="imf-fluid rounded-circle" src="{{$value['image']}}" alt="" width="50px;" height="50px;">
                        <div class="text-break ml-1 w-40 align-self-center px-1">
                            @if($value['payment_type']!=3) 
               
                            <h2 class="outlet-title1 text-uppercase mb-1">{{$value['label']}}</h2>
                            <!-- <p class="outlet-title2 m-0 align-items-end m-0"></p>--> 
                            @else
                             <h2 class="outlet-title1 text-uppercase mb-1">{{$value['outlet_name']}}</h2>
                              @endif

                        </div>
                    </div>

                    <div class="d-flex align-self-center" style="max-width: 40%;">
                        <div class="last-rigth">
                            <p class="m-0 p-0 card-title3 text-primary">{{$value['created_date']}}</p>
                            @if($value['contact_address'])
                              <br>
                              <a class="text-success last-rigth" href="https://maps.google.com/?q={{$value['latitude']}},{{$value['longitude']}}"><p class="m-0 last-rigth"> <i class="fa fa-map-marker-alt fa-1x align-self-center ml-1 text-success"></i></p></a>
                              <p class="m-0 p-0 text-break card-title3">{{$value['contact_address']}}</p>
                            @endif
                        </div>
                            @if($value['contact_address'])

                            @endif
                        @if($value['offer_id'])


                        <!--  <i class="fa fa-info-circle fa-1x align-self-center ml-1 text-dark clickable" href="#"  data-item="" class="btn btn-block" data-toggle="modal" data-target="#myModal-<?php echo $value->id; // Displaying the increment ?>"></i> -->
                          <a href="#" data-toggle="modal" data-target="#myModal-<?php echo $value['id']; // Displaying the increment ?>"><li class="fa fa-info-circle fa-1x align-self-center ml-1 text-dark"></li></a>





                          <div class="modal fade" id="myModal-<?php echo $value['id'];?>" tabindex="-1" role="dialog"       a-labelledby="  exampleModalCenterTitle" aria-hidden="true">
                          <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                  <h5 class="modal-title card-title1 text-center" id="exampleModalCenterTitle">OFFER INFO:</h5>
                                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                  </button>
                                </div>
                                <div class="modal-body">
                                  <p class="card-title3">
                                    {{$value['conditions']}}
                                  </p>
                                </div>
                          </div>
                        </div>
                      </div>
                        @endif

                    </div>
                </div>
                <div class="line my-1"></div>
                <div class="d-flex justify-content-between py-2">
                    <div class="d-flex">
                       @if($value['order_id'])<p  class="m-0 card-title3 mr-2">ORDER ID : {{$value['order_id']}}</p>@endif


                    </div>
                    <?php if($value['type'] =="CR") {?>
                      <p class="m-0 card-title3 text-success">{{$value['amount']}} {{$value['currencyCode']}}</p>
                    <?php }else{ ?>
                      <p class="m-0 card-title3 icon-red">{{$value['amount']}} {{$value['currencyCode']}}</p>

                    <?php } ?>
              </div>
            </div>
         <!-- endcard -->
       <?php }?>
       <?php }else{?>
        <h5 class="card-title1 text-center">No History</h5>
       <?php } ?>


     </section>

    </div>

    
  <!-- endwallet -->
  

<!-- profile -->
<?php //print_r($user_details);exit();?>

  <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">

   <section id="wallet">
    <div class="bg-light">
       <div class="text-center p-5">
          <h1 class="title1">{{$user_details->name}}</h1>
          <p class="title3 m-0">{{$user_details->mobile}}</p>
         <div class="d-flex justify-content-center">
            <i class="fa fa-envelope icon-gray pr-2"></i><p class="title3 m-0">{{$user_details->email}}</p>
         </div>
        </div>
       </div>
    </section>

    <section id="extra">
    
      <div class="d-flex justify-content-between py-2">
        <div class="img-text d-flex align-items-center">
          <i class="fa fa-tag  pr-2 text-1"></i>
          <p class="m-0 text-1">REFFER A FRIEND</p>
        </div>
        <div class="img-right">
          <a href=""><i class="fa fa-chevron-right text-1"></i></a>
        </div>
      </div>
      <!-- <hr class="m-0"> -->
      <div class="d-flex justify-content-between py-2">
          <div class="img-text d-flex align-items-center">
            <i class="fa fa-gift pr-2 text-1">
            </i>
            <p class="m-0 text-1">OFFER</p>
          </div>
          <div class="img-right">
            <a href="{{ URL::to('/promotion') }}"><i class="fa fa-chevron-right text-1"></i></a>
          </div>
      </div>
      
    <!-- <hr class="m-0"> --> 
      <div class="d-flex justify-content-between py-2">
        <div class="img-text d-flex align-items-center">
           <!--  <i class="fa fa-gift pr-2 text-1"></i> -->
            <p class="m-0 text-1"> <a href="{{ URL::to('/logout') }}" onclick="return confirm('Are you sure ,you want logout ?')">LOGOUT</a></p>
        </div>
          <!-- <div class="img-right">
            <a href="{{ URL::to('/logout') }}"><i class="fa fa-chevron-right text-1"></i></a>
          </div> -->
      </div>

      <!-- <hr class="m-0"> -->
    </section>

    <section id="terms">
            <div class="d-flex justify-content-center py-4">
              <div class="img d-flex align-items-center">
                <i class="fa fa-lock icon-gray fa-1x"></i>
                  <div class="title5">
                    <p class="m-0 px-2 pb-1">Terms and condition for product<br>
                    shows the <a href="">service</a> and <a href="">terms</a> </p>
                  </div>
              </div>
            </div>
    </section>
  </div>
 <!-- endprofile -->
  </div>
</div>
</div>
    @endsection
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>


 <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js">
        </script>
   
<!-- <script type="text/javascript">
 $(document).ready(function() {
        function disableBack() { alert("gdgdgd");window.history.forward() }

       window.onload = disableBack();
       //window.onpageshow = function(evt) { if (evt.persisted) disableBack() }
    });
</script> -->
<!-- <script type="text/javascript">
        function preventBack() { window.history.forward(); }
        setTimeout("preventBack()", 0);
        window.onunload = function () { null };
    </script> -->
<!--     <script type="text/javascript">
       $(document).ready(function() {
        function disableBack() { window.history.forward() }

       window.onload = disableBack();
       //window.onpageshow = function(evt) { if (evt.persisted) disableBack() }
    });
    </script> -->



<script type="text/javascript">
  
  function userotpexpire() {
    url = '{{url('userotpexpire')}}';
    cust_id =$("#cust_id").val();
    var token = $('input[name=_token]').val();

    $.ajax({
      url: url,
      type: 'POST',
      headers: {'X-CSRF-TOKEN': token},

      data: {id:cust_id},

      success: function(data) {
        location.reload(true);

        // do something with the return value here if you like
      }
  });
  setTimeout(executeQuery, 50000); // you could choose not to continue on failure...
}

$(document).ready(function() {
 // ClearHistory();

   setTimeout(function(){
         $("div.admin_sucess_common").remove();
      }, 5000 );
      verfiy_pin =$("#verfiy_pin").val();
      /*if(verfiy_pin)
      {
        setTimeout(userotpexpire, 50000);
      }*/
});

/*function ClearHistory()
{
     var backlen = history.length;
     history.go(-backlen);
     window.location.href = '/profile';
}*/
</script>