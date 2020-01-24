
   <footer class="site-footer">
        <div class="container">
            <div class="row align-items-center py-3">
               <div class="col">
                 

                     <a  href="{{ URL::to('') }}"><img src="assets/front/broz/images/logo/brozlogotext.png" width="35" height="35"></a>
                  
               </div> 
                 <div class="col d-flex justify-content-center">
                 
                    <p class="p-0 m-0" style="font-size: 0.80rem">Copyrigth&copy;2019</p>
                 
                 </div> 
                  <div class="col">
                  
                    <div class="d-flex justify-content-end">
                     <a class="text-white " href=""><i class="fab fa-facebook"></i></a>
                     <a class="text-white pl-2" href=""><i class="fab fa-instagram"></i></a>
                     <a class="text-white pl-2" href=""><i class="fas fa-blog"></i></a>
                    </div>
                 
                 </div> 
            </div>  
        </div>
     </footer>
     </body>
</html>
<script type="text/javascript">
      $( document ).ready(function() {
                    $("#load_type").val(1);

      });

  $(".navbar .container ul.navbar-nav > li > a").click(function() {
          var outlet_type =$(this).attr("id");
         // alert( outlet_type);return false;
          if(outlet_type == "home"){
            $('ul li a').removeClass("activeline");
            $(this).addClass('activeline');
            $("#load_type").val(1);
          }else if(outlet_type == "outlet"){                    
            $('ul li a').removeClass("activeline");
            $(this).addClass("activeline");   
            $("#load_type").val(2);

          }else if(outlet_type == "offer"){
            $('ul li a').removeClass("activeline");
            $(this).addClass("activeline");   
            $("#load_type").val(3);

          }else if(outlet_type == "login"){
            $('ul li a').removeClass("activeline");
            $(this).addClass("activeline");
            $("#load_type").val(4);
   
          }

        });
</script>
 
     <script src="{{ URL::asset('assets/front/broz/js/jquery.min.js') }}"></script>

 
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
    integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous">
  </script>
 
   <script src="assets/front/broz/css/bootstrap/js/bootstrap.js"></script>
  <script src="assets/front/broz/js/google.js"></script>

