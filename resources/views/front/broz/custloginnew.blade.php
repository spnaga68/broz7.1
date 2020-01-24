@extends('layouts.front')

<div class="topmargin light page-wrap">
    <div id="login" class="">
        <div class="container ">
            <div class="row">
                <div class="col-md-6 mb-2 light py-2 " id="">
                    <div class="col-md-12 p-3">
                        <h2 class="login-title">Login</h2>
                        <!-- <p class="login-p">or <a class="login-a" href="#">create an account</a> </p> -->
                        <div class="login-line"></div>
                        {!!Form::open(array('url' => ['login'], 'method' => 'post','class'=>'tab-form attribute_form','id'=>'login','files' => true ,'onsubmit'=>'return false'));!!}
                        <div class="form-group d-flex" id="number_block">
                            <input type="tel" class="form-control mr-2" style= "width: 60px" name="countryCode" value="<?php echo COUNTRYCODE; ?>" aria-describedby="phoneHelp" maxlength="4" 
                                   id="countryCode" autocomplete="anyrandomstring" placeholder="" width="20">
                            <input type="tel" class="form-control" name="phone_number"  aria-describedby="phoneHelp"
                                   id="phone_number" autocomplete="new-password" placeholder="Phone Number" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" maxlength="13">
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
                        <button type="submit" id="submit" onclick="phonenumbercheck();"
                                class="btn btn-primary btn-block">Verify
                        </button>

                        <input type="button" value="Forgot Password" onclick="forgotPassword()"
                               class="btn btn-primary btn-block" id="forgot_pwd">


                        <!--  <button  class="btn btn-primary d-none btn-block" id="forgot_password" onclick="forgotPassword();">Forgot Password</button> -->

                        <button class="btn btn-primary d-none btn-block" id="resend_otp" onclick="resendClick();">Resend
                            otp
                        </button>


                        {!!Form::close();!!}
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
        //alert(flow_type);//return false;
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
        //var countryCode = '+91';
        console.log("haiiii");

        // var phone_number = $('input[name=phone]').val();

     if(phone_number != ''){
         document.getElementById('phone_number').setAttribute('readonly', true);
         $('input[name=phone_number]').readOnly = true;
     }else{
         alert("Enter phone number");
         return  false;
     }
//phone verify
        if (flow_type == FLOW_VERIFY_PHONE || flow_type == FLOW_VERIFY_FORGOT) {
            if (phone_number != '') {


                token = $('input[name=_token]').val();
                console.log(flow_type +""+ FLOW_VERIFY_FORGOT);

                url = (flow_type == FLOW_VERIFY_FORGOT) ? '{{url('forgot_password')}}' : '{{url('loginPhoneCheck')}}' ;
                console.log(url);
                data = {phone_number: phone_number,countryCode : countryCode};
                $.ajax({
                    url: url,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: data,
                    type: 'POST',
                    datatype: 'JSON',
                    async: false,
                    success: function (result) {
                        console.log(result);//return false;
                        var result_status =  1
                        if(flow_type == FLOW_VERIFY_FORGOT) {
                            result_status = result.status;
                            otpUnique = result.otpUnique;
                            console.log("otpUnique@$"+otpUnique);
                        }else{
                            result_status= result;
                        }
                        if (result_status == 2) {
                            $('#forgot_pwd').addClass('d-none');
                            $('#password_block').removeClass('d-none');
                            $('input[name=flow_type]').val(FLOW_VERIFY_PASSWORD)
                        }else{
                            //submit otp
                            if (resendApi == 1) {
                                document.getElementById('submit').innerHTML = "Submit";
                                alert("OTP send to " + phone_number + " successfully");
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
                alert("Some thing went wrong");
                return false;
            }
        } else if (flow_type == FLOW_VERIFY_PASSWORD) {
            if (phone_number != '' && password != '') {
                // alert("fgffdg");return false;
                token = $('input[name=_token]').val();
                url = '{{url('loginPasswordCheck')}}';
                data = {phone_number: phone_number, password: password,countryCode:countryCode};
                $.ajax({
                    url: url,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: data,
                    type: 'POST',
                    datatype: 'JSON',
                    async: false,
                    success: function (result) {
                        console.log(result);//return false;
                        if (result == 3) {
                            $(location).attr('href', 'https://brozapp.com/profile')

                        } else {
                            alert("Password seems to be incorrect");
                            // $('#otp_block').removeClass('d-none');
                            // //   $('#resend_otp').removeClass('d-none');
                            // $('input[name=flow_type]').val(FLOW_VERIFY_OTP)

                        }
                    },

                });
            } else {
                alert("Please enter your password");
                return false;
            }

        } else if (flow_type == FLOW_VERIFY_OTP) {
            if (phone_number != '' && otp != '') {
                // alert("fgffdg");return false;
                token = $('input[name=_token]').val();
                url = '{{url('loginotpCheck')}}';
                data = {phone_number: phone_number,countryCode: countryCode, otp: otp};
                $.ajax({
                    url: url,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: data,
                    type: 'POST',
                    datatype: 'JSON',
                    async: false,
                    success: function (result) {
                        console.log(result);//return false;
                        if (result == 1) {
                            $(".cust_info").removeClass('d-none');
                            $("#number_block").removeClass('d-none');
                            $("#password_block").addClass('d-none');
                            $("#otp_block").addClass('d-none');
                            $('#resend_otp').addClass('d-none');
                            $('input[name=flow_type]').val(FLOW_VERIFY_SIGNUP)
                            document.getElementById('submit').innerHTML = "Sign Up";
                        } else {
                            otpObj.value = "";
                            alert("OTP seems to be incorrect");
                        }
                    },

                });
            } else {
                alert("Please enter the opt");
                return false;
            }

        } else if (flow_type == FLOW_VERIFY_SIGNUP) {       //alert(phone_number);
            if (userName != '' && userEmail != '' && userPassword != '' && phone_number != '') {
                //alert("fgffdg");return false;
                token = $('input[name=_token]').val();
                url = '{{url('signupUserCheck')}}';
                data = {
                    userName: userName,
                    lastName: lastName,
                    userEmail: userEmail,
                    userPassword: userPassword,
                    phone_number: phone_number,
                    countryCode: countryCode,
                };
                $.ajax({
                    url: url,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: data,
                    type: 'POST',
                    datatype: 'JSON',
                    async: false,
                    success: function (result) {
                        if (result == 1) {
                            window.location = "https://brozapp.com/profile"
                        } /*else {


                        }*/
                    },
                    error: function (request, status, error) {
                        console.log("coming");return false;
                        json = $.parseJSON(request.responseText);
                        $.each(json.errors, function(key, value){
                            $('.alert-danger').show();
                            $('.alert-danger').append('<p>'+value+'</p>');
                        });
                        $("#result").html('');
                    }

                });
            } else {
                alert("Please enter the required field");
                return false;
            }

        }else if(flow_type == FLOW_FORGOT_VERIFY_OTP){
            if (otp != ''  && phone_number != '') {
                //alert("fgffdg");return false;
                token = $('input[name=_token]').val();
                url = '{{url('forgotOtp')}}';
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

                        alert(result.message);
                        }
                    },

                });
            } else {
                alert("Please enter the required field");
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

                            alert(data.message);
                        }
                    },

                });
            } else {
                alert("Please enter the required field");
                return false;
            }
        }
    }


</script>

