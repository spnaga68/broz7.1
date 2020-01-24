@extends('layouts.front')



  <!-- navtab -->
  <?php //echo"<pre>";print_r($user_details);exit();  ?>
  <div id="page-container">
  <div class="container topmargin">
  <section id="navtab">
    <ul class="nav  nav-pills nav-fill shadow-sm title4 nav-tabs" id="myTab" role="tablist">
      <li class="nav-item">
        <a class="nav-link title4 active"  id="wallet-tab" href="#wallet" data-toggle="tab" >WALLET</a>
      </li>
      <div class="hr-h"></div>

      <li class="nav-item ">
        <a class="nav-link  title4"  id="profile-tab" href="#profile" data-toggle="tab" >PROFILE</a>
      </li>

    </ul>
  </section>
  <!-- endnavtab -->

<div class="tab-content" id="myTabContent">
  <!-- wallet -->
<div class="tab-pane fade show active" id="wallet" role="tabpanel" aria-labelledby="wallet-tab">
<section id="wallet">
    <div class="bg-cuslight p-1">
      
        <p class="title2 p-1">*USE UPTO 1000 AED FOR GROCERY </p>

        <div class="text-center">
          <p class="title3 mb-1 p-0">AED</p>
          <h2 class="title1 p-0 mb-3">{{$user_details->wallet_amount}}</h2>
          <button class="btn btn-success mb-3"> <a class="text-white" href="offer.html">ADD AMOUNT</a></button>
        </div>
      </div>
    
  </section>
 <section id="history" >
      <p class="title1 m-0 p-2">PAYMENT HISTORY</p>

      <?php //foreach($user_details as $data){ ?>
      <!-- card -->
      <div class="card shadow-sm p-2 mb-2">
          <div class="d-flex justify-content-between">
              <div class="img d-flex align-items-center">
                <img class="circle" src="assets/front/broz/images/logo/logo512.png" alt="" width="40" height="40">
                <div class="flex-column">
                    <p class="m-0 px-2 pb-1 card-title1 col-6 text-truncate text-uppercase" style="max-width: 150px;">{{$user_details->outlet_name}}</p>
                </div>
              </div>
              <div class="d-flex align-items-center justify-content-end">
                  <p class="m-0 pr-1 card-title3 text-truncate" style="max-width: 80px">{{$user_details->contact_address}}</p>
                 <a class="text-success last-rigth" href=""><p class="m-0 last-rigth"><i class="fa fa-map-marker-alt fa-1x py-1"></i></p></a>
              </div>
          </div>
          <div class="line my-1"></div>
          <div class="d-flex justify-content-between py-2">
            <p  class="m-0 card-title3">{{$user_details->created_date}}</p>
            <p class="m-0 card-title3 icon-red">{{$user_details->total_amount}} AED</p>
          </div>
      </div>
       <!-- endcard -->
     <?php //}?>

    </section>

</div>
  
  <!-- endwallet -->
  

<!-- profile -->

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
      <hr class="m-0">
      <div class="d-flex justify-content-between py-2">
          <div class="img-text d-flex align-items-center">
            <i class="fa fa-gift pr-2 text-1"></i>
            <p class="m-0 text-1">OFFER</p>
          </div>
          <div class="img-right">
            <a href="{{ URL::to('/promotion') }}"><i class="fa fa-chevron-right text-1"></i></a>
          </div>
        </div>
 <hr class="m-0">
     

        <div class="d-flex justify-content-between py-2">
          <div class="img-text d-flex align-items-center">
           <!--  <i class="fa fa-gift pr-2 text-1"></i> -->
            <p class="m-0 text-1"> <a href="{{ URL::to('/logout') }}">LOGUT</a></p>
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
  </section>
 </div>
 <!-- endprofile -->
</div>





  

