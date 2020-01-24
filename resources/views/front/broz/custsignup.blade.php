@extends('layouts.front')
  
<div id="page-container" class="topmargin light">
    <div id="login" class="">
        <div class="container ">
            <div class="row">
                <div class="col-md-6 mb-2 light py-2 " id="">
                    <div class="col-md-12 p-5">
                        <h2 class="login-title">Signup</h2>
                        <!-- <p class="login-p">or <a class="login-a" href="#">create an account</a> </p> -->
                        <div class="login-line"></div>
                        
                          
                            <div class="form-group d-none cust_info" id="">
                                <input type="text" class="form-control" id="exampleInputphone" name="phoneNumber" value="" aria-describedby="phoneHelp" id="phoneNumber" placeholder="phoneNumber" disabled>
                            </div>

                            <div class="form-group d-none cust_info" id="">
                                <input type="text" class="form-control" id="exampleInputphone" name="userName" value="" aria-describedby="phoneHelp" id="userName" placeholder="userName">
                            </div>

                            <div class="form-group d-none cust_info" id="">
                                <input type="text" class="form-control" id="exampleInputphone" name="useremail" value="" aria-describedby="phoneHelp" id="useremail" placeholder="userEmail">
                            </div>

                            <input type="hidden" name="flow_type" id="flow_type" value="1">
                            <button type="submit" onclick="phonenumbercheck();" class="btn btn-primary btn-block">SIGNUP</button>
                    </div>
                 </div>
                <div class="col-md-6  d-none d-md-block" id="imglogin">
                    <div id="imgLogin">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
   
    /*function phonenumbercheck()  {    
       var flow_type = $('input[name=flow_type]').val();
       alert(flow_type);//return false;
       var phone_number = $('input[name=phone]').val();
       var password = $('input[name=password]').val();
       var otp = $('input[name=otp]').val();
      // var phone_number = $('input[name=phone]').val();
        if(flow_type ==1) {
           if(phone_number != '') {
                token = $('input[name=_token]').val();
                url = '{{url('loginPhoneCheck')}}';
                data = {phone_number:phone_number};
                $.ajax({
                url: url,
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data: data,
                type: 'POST',
                datatype: 'JSON',
                async:false,
                success: function(result) {
                   console.log(result);//return false;
                    if(result == 2)
                    {
                        $('#password_block').removeClass('d-none');
                        $('input[name=flow_type]').val(2)
                    }else{
                        $('#otp_block').removeClass('d-none');
                        $('#password_block').addClass('d-none');
                        $('input[name=flow_type]').val(3)

                    }
                },
                
                });
           }else{
            alert("sorry");return false;
           }
       }else if(flow_type ==2) {
            if(phone_number != '' && password != '' ) {
               // alert("fgffdg");return false;
                token = $('input[name=_token]').val();
                url = '{{url('loginPasswordCheck')}}';
                data = {phone_number:phone_number,password:password};
                $.ajax({
                url: url,
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data: data,
                type: 'POST',
                datatype: 'JSON',
                async:false,
                success: function(result) {
                   console.log(result);//return false;
                    if(result == 3)
                    {
                        $(location).attr('href', 'https://brozapp.com/')

                    }else{
                        $('#otp_block').removeClass('d-none');
                        $('input[name=flow_type]').val(3)

                    }
                },
                
                });
           }else{
            alert("sorry");return false;
           }

       }else if(flow_type ==3)
       {
            if(phone_number != '' && otp != '' ) {
               // alert("fgffdg");return false;
                token = $('input[name=_token]').val();
                url = '{{url('loginotpCheck')}}';
                data = {phone_number:phone_number,otp:otp};
                $.ajax({
                url: url,
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data: data,
                type: 'POST',
                datatype: 'JSON',
                async:false,
                success: function(result) {
                   console.log(result);//return false;
                    if(result == 1)
                    {
                        $(".cust_info").removeClass('d-none');
                        $("#number_block").addClass('d-none');
                        $("#password_block").addClass('d-none');
                        $("#otp_block").addClass('d-none');
                        $('input[name=flow_type]').val(4)

                    }else{
                        

                    }
                },
                
                });
           }else{
            alert("sorry");return false;
           }

       }
    }*/


</script>

