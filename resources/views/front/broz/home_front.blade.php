    @extends('layouts.front')
    @section('content')

  <div class="container-fluid page-wrap " style="margin-top: 80px">
    <div class="row">
      <!-- for xs -->
      <div class="d-sm-none d-xs-block">
      
        <div class="bd-example">
          <div id="carouselExampleCaptions" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
              <li data-target="#carouselExampleCaptions" data-slide-to="0" class="active"></li>
              <li data-target="#carouselExampleCaptions" data-slide-to="1"></li>
              <li data-target="#carouselExampleCaptions" data-slide-to="2"></li>
            </ol>
            <div class="carousel-inner">
              <div class="carousel-item active">
                <img src="assets/front/broz/images/homepage/brozlaundry576x1100.jpg" class="d-block w-100 bg-opacity" alt="...">
                <div class="carousel-caption d-sm-none d-xs-block ">
                  <h5 class="text-white display-5">Pay 1500 and Shop for 2000</h5>
                  <p class="text-white display-6">Limited time only. Hurry up.</p>
                  <button class="btn btn-info btn-sm" onclick="offer()">Get offer</button>
                </div>
              </div>
              <div class="carousel-item">
                <img src="assets/front/broz/images/homepage/sevenpizza576x1100.jpg" class="d-block w-100 bg-opacity" alt="...">
                <div class="carousel-caption d-sm-none d-xs-block">
                 <h5 class="text-white display-5">Pay 1500 and Shop for 2000</h5>
                  <p class="text-white display-6">Limited time only. Hurry up.</p>
                  <button class="btn btn-info btn-sm" onclick="offer()">Get offer</button>
                </div>
              </div>
              <div class="carousel-item">
                <img src="assets/front/broz/images/homepage/kiddzo576x1100.jpg" class="d-block w-100 bg-opacity" alt="...">
                <div class="carousel-caption d-sm-none d-xs-block">
                  <h5 class="text-white display-5">Pay 1500 and Shop for 2000</h5>
                  <p class="text-white display-6">Limited time only. Hurry up.</p>
                  <button class="btn btn-info btn-sm" onclick="offer()">Get offer</button>
                </div>
              </div>
            </div>
            <a class="carousel-control-prev" href="#carouselExampleCaptions" role="button" data-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleCaptions" role="button" data-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="sr-only">Next</span>
            </a>
          </div>
        </div>
      </div>
      <!-- end xs -->

      <!-- for sm -->
      

      <div class="d-none d-md-none d-sm-block">
        <div class="bd-example">
          <div id="carouselExampleCaptions" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
              <li data-target="#carouselExampleCaptions" data-slide-to="0" class="active"></li>
              <li data-target="#carouselExampleCaptions" data-slide-to="1"></li>
              <li data-target="#carouselExampleCaptions" data-slide-to="2"></li>
            </ol>
            <div class="carousel-inner">
              <div class="carousel-item active">
                <img src="assets/front/broz/images/homepage/BrozLaundry745x992.jpg" class="d-block w-100 bg-opacity" alt="...">
                <div class="carousel-caption d-none d-md-block">
                  <h5 class="text-white display-5">Pay 1500 and Shop for 2000</h5>
                  <p class="text-white display-6">Limited time only. Hurry up.</p>
                  <button class="btn btn-info btn-sm" onclick="offer()">Get offer</button>
                </div>
              </div>
              <div class="carousel-item">
                <img src="assets/front/broz/images/homepage/SevenPizza-745x992.jpg" class="d-block w-100 bg-opacity" alt="...">
                <div class="carousel-caption d-none d-md-block">
                  <h5 class="text-white display-5">Pay 1500 and Shop for 2000</h5>
                  <p class="text-white display-6">Limited time only. Hurry up.</p>
                </div>
              </div>
              <div class="carousel-item">
                <img src="assets/front/broz/images/homepage/kiddzo-745x992.jpg" class="d-block w-100 bg-opacity" alt="...">
                <div class="carousel-caption d-none d-md-block">
                 <h5 class="text-white display-5">Pay 1500 and Shop for 2000</h5>
                  <p class="text-white display-6">Limited time only. Hurry up.</p>
                  <button class="btn btn-info btn-sm" onclick="offer()">Get offer</button>
                </div>
              </div>
            </div>
            <a class="carousel-control-prev" href="#carouselExampleCaptions" role="button" data-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleCaptions" role="button" data-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="sr-only">Next</span>
            </a>
          </div>
        </div>
      </div>
<!-- end sm -->
<!-- for md up -->
      <div class="d-none d-sm-none d-md-block p-0 m-0 m-auto">
        <div class="bd-example">
          <div id="carouselExampleCaptions" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
              <li data-target="#carouselExampleCaptions" data-slide-to="0" class="active"></li>
              <li data-target="#carouselExampleCaptions" data-slide-to="1"></li>
              <li data-target="#carouselExampleCaptions" data-slide-to="2"></li>
            </ol>
            <div class="carousel-inner">
              <div class="carousel-item active">
                <img src="assets/front/broz/images/homepage/BrozLaundry2153x1200.jpg" class="d-block w-100 bg-opacity" alt="...">
                <div class="carousel-caption d-none d-md-block">
                  <h5 class="text-white display-5">Pay 1500 and Shop for 2000</h5>
                  <p class="text-white display-6">Limited time only. Hurry up.</p>
                  <button class="btn btn-info btn-sm" onclick="offer()">Get offer</button>
                </div>
              </div>
              <div class="carousel-item">
                <img src="assets/front/broz/images/homepage/SevenPizza-2153x1200.jpg" class="d-block w-100 bg-opacity" alt="...">
                <div class="carousel-caption d-none d-md-block">
                  <h5 class="text-white display-5">Pay 1500 and Shop for 2000</h5>
                  <p class="text-white display-6">Limited time only. Hurry up.</p>
                  <button class="btn btn-info btn-sm" onclick="offer()">Get offer</button>
                </div>
              </div>
              <div class="carousel-item">
                <img src="assets/front/broz/images/homepage/kiddzo-2153x1200.jpg" class="d-block w-100 bg-opacity" alt="...">
                <div class="carousel-caption d-none d-md-block">
                  <h5 class="text-white display-5">Pay 1500 and Shop for 2000</h5>
                  <p class="text-white display-6">Limited time only. Hurry up.</p>
                  <button class="btn btn-info btn-sm" onclick="offer()">Get offer</button>
                </div>
              </div>
            </div>
            <a class="carousel-control-prev" href="#carouselExampleCaptions" role="button" data-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleCaptions" role="button" data-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="sr-only">Next</span>
            </a>
          </div>
        </div>
      </div>
      <!-- end mdup -->

    </div>
  </div>
  <script type="text/javascript">
    function offer(){
      window.location= "https://brozapp.com/promotion";
    }
  </script>
@endsection
