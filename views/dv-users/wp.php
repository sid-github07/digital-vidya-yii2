<?php
use yii\helpers\Html;
use app\models\DvUsers;
use app\models\DvUserMeta;
use app\models\DvStates;
use app\models\DvCities;
use app\models\DvCourse;
use app\models\DvCountry;
use yii\widgets\DetailView;
use app\models\DvUsersRole;
use app\models\DvUsersTeam;
use app\models\DvUsersDepartment;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
// this file is created for testing 

$this->title = 'User: '.$model->first_name.' '.$model->last_name;
$this->params['breadcrumbs'][] = ['label' => 'All Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->first_name.' '.$model->last_name; ?>
<div style="min-height:35px; "></div>
<style type="text/css">
    .content-header h1{display: none;}
</style>
<div class="container">
  <div class="row">
    <div class="col-md-6">    
        <div class="dv-users-view">
        <table id="w0" class="table table-striped table-bordered detail-view">
           <tbody>

            <form name="post-form" id="post-form">
                <label for="title">Group Title</label>
                <input type="text" name="title" id="title" class="form-control" value="Title of Group">
                 
                <!-- <label for="status">Group Status</label>
                <select name="status" id="status" class="form-control">
                    <option selected="selected" value="publish">Publish</option>
                    <option value="draft">Draft</option>
                </select> -->
                 
                <!-- <label for="content">Group Content</label>
                <textarea name="content" id="content" class="form-control">content of post</textarea> -->

                
                <label for="course">Group Courses</label>
                <select name="course" id="course" class="form-control">
                    <option value="107724">Certified Search Engine Marketing Master Course 1.0</option>
                    <!-- <option selected="selected" value="94">Course 1</option> -->
                    <option value="88">Course 2</option>
                </select>
                
                <label for="gleader">Group Leaders</label>
                
                
                <select name="gleader" id="gleader" class="form-control">
                    <option value="435">dharmendra@digitalvidya.com</option>
                    <!-- <option selected="selected" value="11">anoops@whizlabs.com</option> -->
                    <!-- <option value="anoop4saini@gmail.com">anoop4saini@gmail.com</option> -->
                </select>
                
                <label for="guser">Group Users</label>
                <select name="guser" id="guser" class="form-control">
                    <option  value="3001">divya@digitalacademyindia.com</option>
                    <!-- <option selected="selected" value="10">anoopsinghsaini1@gmail.com</option> -->
                    <!-- <option value="anoop4saini@gmail.com">anoop4saini@gmail.com</option> -->
                </select>

                <input type="submit" name="submit" value="Submit" class="hide"><br>    
                <input id="create_group" type="button" class="btn btn-success" name="submit" value="Create Group" class="">
            </form><br>
            <br><div class="post_message"></div> <br>

            <form name="post-form" id="user-form">
                <label for="user_eamil">Email Address</label>
                <input type="text" name="user_eamil" id="user_eamil" class="form-control" value="anoop4saini@gmail.com">     
                <label for="user_password">Password</label>
                <input type="text" name="user_password" id="user_password" class="form-control" value="admin">     
                <input type="submit" name="submit" value="Submit" class="hide">
                <br>
                <input id="create_user" type="button" class="btn btn-success" name="submit" value="Create User" class="">
            </form> <br><br>
            <div class="user_message"></div> 
        </tbody>
        </table>    
    </div>
    </div>
    <div class="col-md-6"></div>
  </div>
</div>
<?php $user_login = base64_encode('anoopsinghsaini1@gmail.com' . ':' . 'jhpo(3R0a9VxoX)Lh%q$X3IY');
      //$user_login = base64_encode('admin' . ':' . 'admin');
      //http://192.168.1.102/wordpress/wp-json/wp/v2/users
      //http://192.168.1.102/wordpress/wp-json/wp/v2/courses
      //http://dev.digitalvidya.com/training/wp-json/wp/v2/courses
      //http://dev.digitalvidya.com/training/wp-json/wp/v2/users

 $js = <<<JS

    $(document).on('click', '#create_group', function(){
        var title = $('#title').val();
        var course = $('#course').val();
        var gleader = $('#gleader').val();
        var guser = $('#guser').val();
        var postForm = $( '#post-form' );
            $("#loading_custom").show();        
            $.ajax({
                url: 'http://dev.digitalvidya.com/training/wp-json/wp/v2/groups',
                method: 'POST',
                //data: postForm.serialize(),
                data: {title:title,status:'publish',content:'' },
                crossDomain: true,
                beforeSend: function ( xhr ) {
                    //xhr.setRequestHeader( 'Authorization', 'Basic admin:admin' );
                    xhr.setRequestHeader( 'Authorization', 'Basic  $user_login' );                    
                },
                success: function( data ) {
                    //console.log( data ); 
                    var group_id = data.id;

                    // assign users to group
    //http://192.168.1.102/wordpress/wp-json/batch/v1/group/?group[id]=105&group[course]=2015&group[leader]=10&group[user]=14

                    $.ajax({
                        url: 'http://dev.digitalvidya.com/training/wp-json/batch/v1/group/?group[id]='+group_id+'&group[course]='+course+'&group[leader]='+gleader+'&group[user]='+guser,
                        method: 'GET',                        
                        crossDomain: true,
                        beforeSend: function ( xhr ) {
                            xhr.setRequestHeader( 'Authorization', 'Basic $user_login' );
                            },
                            success: function( data ){
                            //console.log( data );
                                $(".post_message").replaceWith("<div class='post_message'><div class='alert-success alert fade in'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button> A New Group Created with ID: "+group_id+"</div></div>");
                                $("#loading_custom").hide();
                             },
                             error: function(xhr){
                                var err = JSON.parse(xhr.responseText);
                                $(".post_message").replaceWith("<div class='post_message'><p>Error: "+err.code+"</p></div>");
                                $("#loading_custom").hide();
                            }
                       });
                    // assign users to group
                },
                error: function(xhr) {
                     var err = JSON.parse(xhr.responseText);
                    $(".post_message").replaceWith("<div class='post_message'><p>Error: "+err.code+"</p></div>");
                    $("#loading_custom").hide();                    
                },
            });         
       });

     $(document).on('click', '#create_user', function(){        
        var eamil = $('#user_eamil').val();
        var password = $('#user_password').val();
            $("#loading_custom").show();        
            $.ajax({
                url: 'http://dev.digitalvidya.com/training/wp-json/wp/v2/users',
                method: 'POST',
                data: {email:eamil,username:eamil,password:password },
                crossDomain: true,
                beforeSend: function ( xhr ) {
                    xhr.setRequestHeader( 'Authorization', 'Basic $user_login' );                    
                },
                success: function( data ) {                     
                    //console.log( data );
                        $(".user_message").replaceWith("<div class='user_message'><div class='alert-success alert fade in'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button> A New User Created with ID: "+data.id+"</div></div>");
                        $("#loading_custom").hide();
                    },
                    error: function(xhr){
                        var err = JSON.parse(xhr.responseText);                        
                        $(".user_message").replaceWith("<div class='user_message'><p>Error: "+err.code+"</p></div>");
                        $("#loading_custom").hide();
                    }
            });
        });
JS;

$this->registerJs($js); ?>