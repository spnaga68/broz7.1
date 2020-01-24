@extends('layouts.front')


<div class="topmargin light page-wrap">

    <div id="login" class="">
        <div class="container ">
            <div class="row">

                    
                <div class="col-md-6 mb-2 light py-2 " id="">
                    <center><div id="result"></div></center>

                    <div class="col-md-12 p-3">
                        <h2 class="login-title">Login</h2>
                        <!-- <p class="login-p">or <a class="login-a" href="#">create an account</a> </p> -->
                        <div class="login-line"></div>
                        {!!Form::open(array('url' => ['login'], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'login','files' => true ,'onsubmit'=>'return false'));!!}
                        <div class="form-group d-flex" id="number_block">
                            <input type="tel" class="form-control mr-2" style= "width: 60px" name="countryCode" value="<?php echo COUNTRYCODE; ?>" aria-describedby="phoneHelp" maxlength="4" 
                                   id="countryCode" autocomplete="anyrandomstring" placeholder="" width="20" oninput="this.value = this.value.replace(/[^0-9.+]/g, '').replace(/(\..*)\./g, '$1');" >
                            <input type="tel" class="form-control" name="phone_number"  aria-describedby="phoneHelp"
                                   id="phone_number" autocomplete="new-password" placeholder="Phone Number" min="6" max="13" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                        </div>

                        <div class="form-group d-none" id="password_block">
                            <input type="password" class="form-control" name="password" 
                                   aria-describedby="phoneHelp" id="password" autocomplete="new-password"
                                   placeholder="Password">
                        </div>
                        <div class="d-none" id="otp_block">
                            <div class="form-group d-flex digit-group">
                                <!-- <input class="form-control" type="text" id="digit-1" name="digit-1" data-next="digit-2" />
                                <input class="form-control mx-1" type="text" id="digit-2" name="digit-2" data-next="digit-3" data-previous="digit-1" />
                                <input class="form-control mr-1" type="text" id="digit-3" name="digit-3" data-next="digit-4" data-previous="digit-2" />
                                <input class="form-control" type="text" id="digit-4" name="digit-4" data-next="digit-5" data-previous="digit-3" /> -->
                                <input type="tel" name="otp" placeholder="****" id="otp" maxlength="4">
                            </div>
                        </div>


                        <div class="form-group d-none cust_info" id="">
                            <input type="text" class="form-control" name="userName" value=""
                                   aria-describedby="phoneHelp" id="userName" placeholder="First Name">
                        </div>

                        <div class="form-group d-none cust_info" id="">
                            <input type="text" class="form-control" name="lastName" value=""
                                   aria-describedby="phoneHelp" id="lastName" placeholder="Last Name">
                        </div>

                        <div class="form-group d-none cust_info" id="">
                            <input type="email" class="form-control" name="email" value="" aria-describedby="phoneHelp"
                                   id="userEmail" placeholder="Email">
                        </div>

                        <div class="form-group d-none cust_info" id="">
                            <input type="password" class="form-control" name="pass" value=""
                                   aria-describedby="phoneHelp" id="userPassword" placeholder="Password">
                        </div>


                        <div class="form-group d-none" id="newPasswordDiv">
                            <input type="password" class="form-control" name="newPassword" value=""
                                   aria-describedby="phoneHelp" id="newPassword" placeholder="New Password">
                        </div>

                        <div class="form-group d-none " id="confirmPasswordDiv">
                            <input type="password" class="form-control" name="confirmPassword" value=""
                                   aria-describedby="phoneHelp" id="confirmPassword" placeholder="Confirm Password">
                        </div>


                        <input type="hidden" name="flow_type" id="flow_type" value="1">
                        <button type="submit" id="submit" onclick="phonenumbercheck();" class="btn btn-primary btn-block">Verify
                        <span class="spinner-border spinner-border-sm invisible" role="status" aria-hidden="true" id="loader_login"></span>
                        </button>

                        <input type="button" value="Forgot Password" onclick="forgotPassword()"
                               class="btn btn-primary btn-block" id="forgot_pwd">


                        <!--  <button  class="btn btn-primary d-none btn-block" id="forgot_password" onclick="forgotPassword();">Forgot Password</button> -->

                        <button class="btn btn-primary d-none btn-block" id="resend_otp" onclick="resendClick();">Resend
                            otp
                        </button>


                        {!!Form::close();!!}
                    </div>


                    
                 <!--    <div class="d-flex justify-content-center ">
                      <div class="spinner-border invisible" role="status"  id="loader_login">
                        <span class="sr-only">Loading...</span>
                      </div>
                    </div> -->

                </div>

                <div class="col-md-6  d-none d-md-block" id="imglogin">
                    <div id="imgLogin">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
 -->
<script type="text/javascript">
    var FLOW_VERIFY_PHONE = 1;
    var FLOW_VERIFY_PASSWORD = 2;
    var FLOW_VERIFY_OTP = 3;
    var FLOW_VERIFY_SIGNUP = 4;
    var FLOW_VERIFY_FORGOT = 5;
    var FLOW_NEW_PWD =7;
    var FLOW_FORGOT_VERIFY_OTP =6;
    var FLOW_UPDATE_PASSWORD =8;
var userUnique = "";
var otpUnique = "";
    var resendApi = 0;
    console.log("cameeeee");
    var otpObj = document.getElementById('otp');
    otpObj.addEventListener('keydown', stopCarretUp);
    otpObj.addEventListener('keyup', stopCarret);
    otpObj.scrollLeft = 0;

    function stopCarret() {
        if (otpObj.value.length > 3) {
            setCaretPosition(otpObj, 3);
        }
    }

    function stopCarretUp() {
        if (otpObj.value.length > 3) {
            otpObj.value = "";
        }
    }

    function setCaretPosition(elem, caretPos) {
        console.log("cameeeee mov" + caretPos);
        otpObj.blur();
        document.getElementById('submit').focus();
        // if(elem != null) {
        //     if(elem.createTextRange) {
        //         var range = elem.createTextRange();
        //         range.move('character', caretPos);
        //         range.select();
        //     }
        //     else {
        //         if(elem.selectionStart) {
        //             elem.focus();
        //             elem.setSelectionRange(caretPos, caretPos);
        //         }
        //         else
        //             elem.focus();
        //     }
        // }
    }

    function resendClick() {
        resendApi = 1;
        if($('input[name=flow_type]').val() != FLOW_FORGOT_VERIFY_OTP ){
        $('input[name=flow_type]').val(FLOW_VERIFY_PHONE);}else{
            $('input[name=flow_type]').val(FLOW_VERIFY_FORGOT);
        }
        phonenumbercheck();

    }

    function forgotPassword() {
        //resendApi = 1;
        $('input[name=flow_type]').val(FLOW_VERIFY_FORGOT);

        phonenumbercheck();

    }


    function phonenumbercheck() {
        var flow_type = $('input[name=flow_type]').val();
       // alert(flow_type);//return false;
        var phone_number = $('input[name=phone_number]').val();

        var userPassword = $('input[name=pass]').val();
        var password = $('input[name=password]').val();
        var otp = $('input[name=otp]').val();
        var userName = $('input[name=userName]').val();
        var lastName = $('input[name=lastName]').val();
        var userEmail = $('input[name=email]').val();
        var newPassword = $('input[name=newPassword]').val();
        var confirmPassword = $('input[name=confirmPassword]').val();
        var countryCode = $('input[name=countryCode]').val();
        console.log("haiiii");
       // alert(countryCode);
        // var phone_number = $('input[name=phone]').val();

     if(phone_number != ''){
        if(phone_number.length >= 6 && phone_number.length <= 13){
         document.getElementById('phone_number').setAttribute('readonly', true);
         $('input[name=phone_number]').readOnly = true;
        }else{
            //alert("Please enter phone number between 6 and 13 digits");
            $("#result").html('Please enter phone number between 6 and 13 digits'); 
            $("#result").addClass("alert alert-danger");
            setTimeout(function() {
                $("#result").html(''); 
                $("#result").removeClass("alert alert-danger");
            },3000);
            return  false;
        }
    }else{
            $("#result").html('Enter phone number'); 
            $("#result").addClass("alert alert-danger");
            setTimeout(function() {
                $("#result").html(''); 
                $("#result").removeClass("alert alert-danger");
            },3000);
       
            // alert("Enter phone number");
            // sweetAlert("Enter phone number");

         return  false;
    }

    if(countryCode !='')
    {    

        var digits = (""+countryCode).split("");
        if(digits[0] == '+')
        {
            document.getElementById('countryCode').setAttribute('readonly', true);
            $('input[name=countryCode]').readOnly = true;
        }else{
           // alert("shoukd add + symbol befor the country code");
            $("#result").html('Should add + symbol befor the country code'); 
            $("#result").addClass("alert alert-danger");
            setTimeout(function() {
                $("#result").html(''); 
                $("#result").removeClass("alert alert-danger");
            },3000);
            return  false;
        }

    }else{

        //alert("Enter countryCode");
        $("#result").html('Enter countryCode'); 
        $("#result").addClass("alert alert-danger");
        setTimeout(function() {
            $("#result").html(''); 
            $("#result").removeClass("alert alert-danger");
        },3000);
        return  false;
    }
    //phone verify
        if (flow_type == FLOW_VERIFY_PHONE || flow_type == FLOW_VERIFY_FORGOT) {
            if (phone_number != '') {
                $("#loader_login").removeClass('invisible');
                $("#loader_login").addClass('visible');

                token = $('input[name=_token]').val();
                console.log(flow_type +""+ FLOW_VERIFY_FORGOT);

                url = (flow_type == FLOW_VERIFY_FORGOT) ? '{{url('api/mforgotPassword')}}' : '{{url('api/mverifyPhone')}}' ;
                console.log(url);
                data = {phoneNumber: phone_number,countryCode : countryCode,language:1,login_type:2,deviceType:1,facebookId:null,isFacebookLogin:true};

                $.ajax({
                    url: url,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: data,
                    type: 'POST',
                    datatype: 'JSON',
                    async: false,
                    success: function (result) {
                         $("#loader_login").removeClass('visible');
                        $("#loader_login").addClass('invisible');
                        result =  $.parseJSON(result);
                        //console.log(result.status);return false;
                        var result_status =  1
                        if(flow_type == FLOW_VERIFY_FORGOT) {
                            result_status = result.status;
                            otpUnique = result.otpUnique;
                            console.log("otpUnique@$"+otpUnique);
                        }else{
                            result_status= result.status;
                        }
                        console.log(result_status);
                        if (result_status == 1) {
                            $('#forgot_pwd').addClass('d-none');
                            $('#password_block').removeClass('d-none');
                            $('input[name=flow_type]').val(FLOW_VERIFY_PASSWORD)
                        }else{
                        //submit otp
                            if (resendApi == 1) {
                                document.getElementById('submit').innerHTML = "Submit";
                                $("#result").html("OTP send to " + phone_number + " successfully"); 
                                $("#result").addClass("alert alert-danger");
                                setTimeout(function() {
                                    $("#result").html(''); 
                                    $("#result").removeClass("alert alert-danger");
                                },3000);
                                //return  false;
                                //alert("OTP send to " + phone_number + " successfully");
                            }
                            $('#forgot_pwd').addClass('d-none');
                            $('#otp_block').removeClass('d-none');
                            $('#resend_otp').removeClass('d-none');
                            $('#password_block').addClass('d-none');
                            $('input[name=flow_type]').val((flow_type == FLOW_VERIFY_FORGOT) ? FLOW_FORGOT_VERIFY_OTP : FLOW_VERIFY_OTP)

                        }
                    },

                });
            } else {
                //alert("Phone number filed required");

                $("#result").html('Phone number filed required'); 
                $("#result").addClass("alert alert-danger");
                setTimeout(function() {
                    $("#result").html(''); 
                    $("#result").removeClass("alert alert-danger");
                },3000);
                return false;
            }
        } else if (flow_type == FLOW_VERIFY_PASSWORD) {
            if (phone_number != '' && password != '') {
                $("#loader_login").removeClass('invisible');
                $("#loader_login").addClass('visible');
                 //alert("fgffdg");return false;
                token = $('input[name=_token]').val();
                url = '{{url('api/mverifyPassword')}}';
                data = {phoneNumber: phone_number, userPassword: password,countryCode:countryCode,deviceType:1,login_type:1,user_type:3,language:1};

                $.ajax({
                    url: url,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: data,
                    type: 'POST',
                    datatype: 'JSON',
                    async: false,
                    success: function (result) {
                        $("#loader_login").removeClass('visible');
                        $("#loader_login").addClass('invisible');
                        result = JSON.parse(JSON.stringify(result))
                        //console.log(result.detail.userId);

                        if (result.status == 1) {
                            $(location).attr('href', '/profile')

                        } else {
                           // alert("Password seems to be incorrect");
                            $("#result").html("Password seems to be incorrect"); 
                            $("#result").addClass("alert alert-danger");
                            setTimeout(function() {
                                $("#result").html(''); 
                                $("#result").removeClass("alert alert-danger");
                            },3000);
                            return  false;
                            // $('#otp_block').removeClass('d-none');
                            // //   $('#resend_otp').removeClass('d-none');
                            // $('input[name=flow_type]').val(FLOW_VERIFY_OTP)

                        }
                    },

                });
            } else {
                //alert("Phone number or Password filed required");
                $("#result").html("Phone number or Password filed required"); 
                $("#result").addClass("alert alert-danger");
                setTimeout(function() {
                    $("#result").html(''); 
                    $("#result").removeClass("alert alert-danger");
                },3000);
                return  false;
            }

        } else if (flow_type == FLOW_VERIFY_OTP) {
            if (phone_number != '' && otp != '') {
                // alert("fgffdg");return false;
                token = $('input[name=_token]').val();
                url = '{{url('api/msignupOtpVerify')}}';


                data = {phoneNumber: phone_number, otp: otp,countryCode:countryCode,language:1};
                $.ajax({
                    url: url,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: data,
                    type: 'POST',
                    datatype: 'JSON',
                    async: false,
                    success: function (result) {

                        result =  $.parseJSON(result);

                        console.log(result.status);//return false;

                        if (result.status == 1) {
                            $(".cust_info").removeClass('d-none');
                            $("#number_block").removeClass('d-none');
                            $("#password_block").addClass('d-none');
                            $("#otp_block").addClass('d-none');
                            $('#resend_otp').addClass('d-none');
                            $('input[name=flow_type]').val(FLOW_VERIFY_SIGNUP)
                            document.getElementById('submit').innerHTML = "Sign Up";
                        } else {
                            otpObj.value = "";
                           // alert("OTP seems to be incorrect");
                            $("#result").html("OTP seems to be incorrect"); 
                            $("#result").addClass("alert alert-danger");
                            setTimeout(function() {
                                $("#result").html(''); 
                                $("#result").removeClass("alert alert-danger");
                            },3000);
                            return  false;
                        }
                    },

                });
            } else {
                //alert("OTP filed is required");
                $("#result").html("OTP filed is required"); 
                $("#result").addClass("alert alert-danger");
                setTimeout(function() {
                    $("#result").html(''); 
                    $("#result").removeClass("alert alert-danger");
                },3000);
                return  false;
            }

        } else if (flow_type == FLOW_VERIFY_SIGNUP) {       //alert(phone_number);
            if (userName != '' && userEmail != '' && userPassword != '' && phone_number != '') {
                //alert("fgffdg");return false;
                $("#loader_login").removeClass('invisible');
                $("#loader_login").addClass('visible');
                token = $('input[name=_token]').val();
                url = '{{url('api/msignupNew')}}';
                data = {
                    /*userName: userName,
                    lastName: lastName,
                    userEmail: userEmail,
                    userPassword: userPassword,
                    phone_number: phone_number,
                    countryCode: countryCode*/
                    userName: userName,
                    lastName: lastName,
                    userEmail: userEmail,
                    password: userPassword,
                    phoneNumber: phone_number,
                    countryCode: countryCode,
                    gender: "M",
                    deviceType: 1,
                    login_type: 1,
                    referral: "",
                    language: 1
                };
                $.ajax({
                    url: url,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: data,
                    type: 'POST',
                    datatype: 'JSON',
                    async: false,
                    success: function (result) {
                        $("#loader_login").removeClass('visible');
                        $("#loader_login").addClass('invisible');
                        result =  $.parseJSON(result);

                        console.log(result.status);//return false;
                        if (result.status == 1) {
                            //window.location = "https://brozapp.com/profile"
                            $(location).attr('href', '/profile')

                        } else {
                            console.log(result);
                            $("#result").html(result.message); 
                            $("#result").addClass("alert alert-danger");
                            setTimeout(function() {
                                $("#result").html(''); 
                                $("#result").removeClass("alert alert-danger");
                            },3000);
                            return  false;
                           // alert(result.message);

                        }
                    },

                });
            } else {
               // alert("Please enter the required field");
                $("#result").html("Please enter the required field"); 
                $("#result").addClass("alert alert-danger");
                setTimeout(function() {
                    $("#result").html(''); 
                    $("#result").removeClass("alert alert-danger");
                },3000);
                return  false;
            }

        }else if(flow_type == FLOW_FORGOT_VERIFY_OTP){
            if (otp != ''  && phone_number != '') {
                token = $('input[name=_token]').val();
                url = '{{url('api/mforgotOtp')}}';
                console.log("otpUnique@"+otpUnique);
                data = {
                    otpUnique: otpUnique,
                    otp: otp
                };
                $.ajax({
                    url: url,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: data,
                    type: 'POST',
                    datatype: 'JSON',
                    async: false,
                    success: function (result) {
                        if (result.status == 1) {
                            userUnique = result.userUnique;
                            $('input[name=flow_type]').val(FLOW_UPDATE_PASSWORD)
                            $('#newPasswordDiv').removeClass('d-none');
                            $('#confirmPasswordDiv').removeClass('d-none');
                            $('#resend_otp').addClass('d-none');
                            $('#otp').addClass('d-none');
                            document.getElementById('submit').innerHTML = "Update Password";
                        } else {
                            $("#result").html(result.message); 
                            $("#result").addClass("alert alert-danger");
                            setTimeout(function() {
                                $("#result").html(''); 
                                $("#result").removeClass("alert alert-danger");
                            },3000);
                        }
                    },

                });
            } else {
                //alert("Please enter the required field");
                $("#result").html("Please enter the required field"); 
                $("#result").addClass("alert alert-danger");
                setTimeout(function() {
                    $("#result").html(''); 
                    $("#result").removeClass("alert alert-danger");
                },3000);
                return false;
            }
        }else if(flow_type == FLOW_UPDATE_PASSWORD){
            if (newPassword != ''  && confirmPassword != '') {
                //alert("fgffdg");return false;
                token = $('input[name=_token]').val();
                url = '{{url('api/mupdateNewPassword')}}';
                console.log("userUnique@"+userUnique);
                data = {
                    userUnique: userUnique,
                    newPassword: newPassword,
                    retypePassword : confirmPassword,
                    deviceType : "3",
                    loginType : "3",
                    deviceId : "web",
                    deviceToken : "web"

                };
                $.ajax({
                    url: url,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: data,
                    type: 'POST',
                    datatype: 'JSON',
                    async: false,
                    success: function (result) {
                        var data = JSON.parse(result);
                        if (data.status == 1) {
                            alert(data.message);
                            window.location = "https://brozapp.com/custlogin"
                        } else {
                            $("#result").html(data.message); 
                            $("#result").addClass("alert alert-danger");
                            setTimeout(function() {
                                $("#result").html(''); 
                                $("#result").removeClass("alert alert-danger");
                            },3000);
                            //alert(data.message);
                        }
                    },

                });
            } else {
               // alert("Please enter the required field");
                $("#result").html("Please enter the required field"); 
                $("#result").addClass("alert alert-danger");
                setTimeout(function() {
                    $("#result").html(''); 
                    $("#result").removeClass("alert alert-danger");
                },3000);
                return false;
            }
        }
    }


</script>

