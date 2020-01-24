<!DOCTYPE html>
<html>
<head>
    <title>PHP - Dynamically Add or Remove input fields using JQuery</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</head>
<style>
  .container{margin-top:30px;}
  .form-group{float:left;width:100%;} 
  .btn{margin-bottom:10px;width:10%;}
  .form_filed{float:left;width:100%;border: 1px solid #ddd;padding: 20px;border-radius: 4px;}
  .form-control{float:left;width:30%;margin-right:20px}
  .cancel{float:left;width:10%;}
  body {
    margin: 0;
    padding: 0;
}
.form-control {
    width: 90%;
}
.d {
    display: inline-block;
    position: relative;
    left: 92%;
    top: -50px;
}

ul {
    list-style-type: none;
}

#a1 {
    margin-top: 0;
}

.b {
   margin-left: 90%;
}

</style>
<body>
<div class="container">
    <h2 align="center">PHP - Dynamically Add or Remove input fields using JQuery</h2>
    <div class="container">
      <div class="form_filed">
        <form   name="add_name" id="book-list" >
                 <ul class="" id="">
                     <label for="Gradel Dependency">Gradel Dependency</label>
                     <textarea name="gradel_dependency[]" id="" cols="30" rows="" class="form-control"></textarea>
                     <input type="button" value="Add" class="btn btn-info d" onclick="createmain('a1','gradel_dependency[]')">
                     <div id="a1">

                     </div>
                 </ul>
                  <ul class=""  id="">
                     <label for="">Gradel App Dependency</label>
                     <textarea name="dep_name[]" id="dep_name_1" cols="30" rows="" class="form-control"></textarea>
                     <textarea name="dep_data[]" id="dep_data_1" cols="30" rows="" class="form-control"></textarea>
                     <input type="button" value="Add" class="btn btn-info d" onclick="createmainf1('a2','dep_name[]','dep_data[]')">
                     <div id="a2"></div>
                     <input type="hidden" name="garde_app_val" id="garde_app_val" value="1">
                 </ul>
             
                 <button class="btn btn-danger b" id="submit">Submit</button>
           

        </form>
      </div>
  </div>
</div>


<script type="text/javascript">

  var i=0;
const createmain = (q,x) => {
    i++;
    const mainid = document.getElementById(q);
    const li = document.createElement('li');
    const input1 = document.createElement('textarea');
    const cancel = document.createElement('button');

    cancel.textContent = "cancel";
    li.setAttribute("class",'mb-1')
    input1.setAttribute('cols','30');
    input1.setAttribute('class','form-control mb-0');
    input1.setAttribute('name',x);
    input1.setAttribute('id',`main${i}`);
    cancel.setAttribute('class', 'btn btn-dark delete d');
    cancel.setAttribute('type', 'button');

    li.appendChild(input1)
    li.appendChild(cancel)
    mainid.appendChild(li)
    

}


var i=1;
const createmainf1 = (e,x,y) => {
   //console.log("id"+document.getElementById("garde_app_val").value);

  //alert("dfgdfg");
    i++;
    const mainid = document.getElementById(e);
    const li = document.createElement('li');
    const input1 = document.createElement('textarea');
    const input2 = document.createElement('textarea');
    const cancel = document.createElement('button');

    cancel.textContent = "cancel";
    li.setAttribute("class",'mb-4')
    input1.setAttribute('class','form-control');
    input2.setAttribute('class','form-control');
    input1.setAttribute('id',`dep_name_${i}`);
    input2.setAttribute('id',`dep_data_${i}`);

    input1.setAttribute('name',x);
    input2.setAttribute('name',y);
    cancel.setAttribute('class', 'btn btn-dark delete d');
    cancel.setAttribute('type', 'button');

    li.appendChild(input1)
    li.appendChild(input2)
    li.appendChild(cancel)
    mainid.appendChild(li)
    document.getElementById("garde_app_val").value = i;


}

// removesubsub
const lisub = document.querySelector('#book-list');
lisub.addEventListener('click',function(s) {
   if(s.target.className == "btn btn-dark delete d"){
        const lid = s.target.parentElement;
        lid.remove(lid)
    }

})


  $('#submit').click(function(){
        var x = $("#book-list").serializeArray();  
        var value= document.getElementById("garde_app_val").value;
       // console.log(document.getElementById("garde_app_val").value);
        y =value - 1;
        var gradel_dependency = new Array();
        var gradleAppDependency = new Array();
        $.each(x, function(i, field){  

         // console.log(y);//return false;
            if(field.name == "gradel_dependency[]"){

              gradel_dependency.push(field.value);

            }else if(field.name == "dep_name[]")
            {

              // var KeyValuePair = '{"depName"' + ":" + Question_id + "," + "Ans" + ":" + answer_id + "}";
             // alert(y);return false;
            //  arr.insert(y ,)
               gradleAppDependency[0].push({
                    depName: field.value ,
                    //depData:  field.value
                });
            }else if(field.name == "dep_data[]")
            {
               gradleAppDependency.push({
                  //depName: field.value 
                  depData:  field.value
              });
            }
            y++;
          });
          //alert(gradel_dependency);
         console.log(gradleAppDependency);return false;

      });


 /*   $(document).ready(function(){
      var postURL = "/api/data";
      var i=0;
      var j =x=0;
       
      $(".add").click(function(){
       // alert("fgdfg");
        j++;
         
        $('#first').append('<div class="form-group"><input type="textbox" name="gradel_dependency[]"  class="form-control" id=""> <button type="button" class="btn cancel">cancel</button>   </div>');
      });
      $(document).on('click', '.cancel', function(){
           var button_id = $(this).attr("id");
           alert(button_id);return false;
           $('#category_'+button_id+'').remove();
      });
      $('#submit').click(function(){
        var x = $("#add_name").serializeArray();  
        var y = new Array();
        $.each(x, function(i, field){  
            y.push(field.value);

            //$("#results").append(field.name + ":" + field.value + " ");  
        });
                alert(y);
         console.log(y);return false;

      });*/


     
</script>


</body>
</html>