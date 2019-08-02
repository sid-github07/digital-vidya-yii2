<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\DvStates;
use app\models\DvCities;
use app\models\DvCourse;
use app\models\DvCountry;
use app\models\DvUsersRole;
use app\models\DvUsersTeam;
use app\models\DvUsersDepartment;
use app\models\DvUserMeta;
use app\models\DvUsers;
use app\models\DvModuleModel;
use dosamigos\ckeditor\CKEditor;
#use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\DvUsers */
/* @var $form yii\widgets\ActiveForm */

// This file contains Create User/Edit User form.

?>
<Script src="<?= Yii::$app->request->baseUrl ?>/web/js/ckeditor.js"></Script>
<Script src="<?= Yii::$app->request->baseUrl ?>/web/js/sample.js"></Script>
<div class="dv-users-form">
    <?php if($model->isNewRecord){
        $form = ActiveForm::begin(['id' => 'create_user',
        'fieldConfig' => ['options' => ['class' => 'form-group col-md-4','enctype' => 'multipart/form-data']],
        ]);
        echo '<div class="form-group col-md-12 text-left">';
        echo '<div class="form-group col-md-4"></div>';
        ?>
        <div class="col-md-2">
        <img width="80" height="80" src="" class="img-circle" alt="User Image" id="user_profile_picture" style="display: none;">
        </div>
        <?php
        // User Picture
        echo $form->field($model, 'picture')->fileInput();
        echo '<div class="form-group col-md-4"></div>';
        echo '</div>';
        } else {
            $form = ActiveForm::begin(['fieldConfig' => ['options' => ['class' => 'form-group col-md-4','enctype' => 'multipart/form-data']],
        ]);
            echo '<div class="form-group col-md-12 text-left">';
            if(empty($model->picture)){
                echo $form->field($model, 'picture')->fileInput();
                ?>
                <div class="form-group col-md-4 text-center">
                    <img width="80" height="80" src="http://dev.digitalvidya.com/assist/uploads/user_image/female.png" class="img-circle" alt="User Image">
                </div>
                <?php
            } else { ?>
                <div class="form-group col-md-4 text-center">
                    <img width="80" height="80" src="<?=Yii::$app->params['yii_url']?>/uploads/user_image/<?php echo $model->picture; ?>" class="img-circle" alt="User Image">
                </div>
            <?php echo $form->field($model, 'picture')->fileInput();
        }
        echo '</div>';
    }


    echo '<div class="form-group col-md-12"><h3 class="blue_color">Profile</h3></div>';

    if($model->isNewRecord){
        // Email
        echo $form->field($model, 'email')->textInput(['maxlength' => true])->input('email', ['placeholder' => "Email",'required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Email"])->label(false);
    } else{
        // Email
        echo $form->field($model, 'email')->textInput(['maxlength' => true, 'disabled' => 'disabled'])->label(false);
    }
    // First Name
    echo $form->field($model, 'first_name')->textInput(['maxlength' => true])->input('first_name', ['placeholder' => "First Name",'required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"First Name"])->label(false);

    // Last Name
    echo $form->field($model, 'last_name')->textInput(['maxlength' => true])->input('last_name', ['placeholder' => "Last Name", 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Last Name"])->label(false);

    if($model->isNewRecord){
        // User Name
        echo $form->field($model, 'username')->textInput(['maxlength' => true])->input('username', ['placeholder' => "User Name",'required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"User Name"])->label(false);
        // Email
        /*echo $form->field($model, 'email')->textInput(['maxlength' => true])->input('email', ['placeholder' => "Email",'required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Email"])->label(false);        */
        // Password
        echo $form->field($model, 'password')->passwordInput(['maxlength' => true])->input('password', ['placeholder' => "Password",'required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Password"])->label(false);
        // Confirm Password
        echo '<div class="form-group col-md-4 field-dvusers-cpassword required">
                <input required="required" placeholder="Confirm Password" type="password" name="cpassword" class="form-control" id="cpassword" data-toggle="tooltip" data-placement="top" title="Confirm Password"></div>';

    } else{
        // User Name
        echo $form->field($model, 'username')->textInput(['maxlength' => true, 'disabled' => 'disabled'])->label(false);
        // Email
       /* echo $form->field($model, 'email')->textInput(['maxlength' => true, 'disabled' => 'disabled'])->label(false);*/
        // Password
        echo '<div class="form-group col-md-4 field-dvusers-password"><input type="password" id="dvusers-password" class="form-control" name="DvUsers[password]" value="" maxlength="155" aria-invalid="false" placeholder="Password" data-toggle="tooltip" data-placement="top" title="Password"></div>';
        // Confirm Password
        echo '<div class="form-group col-md-4 field-dvusers-cpassword">
                <input placeholder="Confirm Password" type="password" name="cpassword" class="form-control" id="cpassword" data-toggle="tooltip" data-placement="top" title="Confirm Password"></div>';
    }

    // Phone Mobile number
    if($model->isNewRecord){
        echo '<div class="form-group col-md-4"><input id="phone" class="form-control" name="usermeta[phone]" placeholder="Phone Number" aria-invalid="false" type="text" pattern="[6789][0-9]{9}" data-toggle="tooltip" data-placement="top" title="Phone Number"></div>';
    } else {
    $user_phone = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'phone' ])->all();
    $phone = ArrayHelper::map($user_phone, 'uid', 'meta_value');
    $phone_no = '';
    if($phone[$model->id]){
        $phone_no = $phone[$model->id];
    }
            echo '<div class="form-group col-md-4"><input id="phone" class="form-control" name="usermeta[phone]" placeholder="Phone Number" aria-invalid="false" value="'.$phone_no.'" type="text" pattern="[6789][0-9]{9}" data-toggle="tooltip" data-placement="top" title="Phone Number"></div>';
        
    }

    // Date of Birth
    if($model->isNewRecord){
        echo '<div class="form-group col-md-4"><input class="form-control datepicker_dob" name="usermeta[dob]" placeholder="Date of Birth"  data-date-format="mm/dd/yyyy" data-date-end-date="0d" data-toggle="tooltip" data-placement="top" title="Date of Birth"></div>';
    } else {
        $user_dob = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'dob' ])->all();
        $dob = ArrayHelper::map($user_dob, 'uid', 'meta_value');
        $udob = '';
        if($dob[$model->id]){
            $udob = $dob[$model->id];
        }
            echo '<div class="form-group col-md-4"><input class="form-control datepicker_dob" name="usermeta[dob]" placeholder="Date of Birth" value="'.$udob.'" data-date-format="mm/dd/yyyy" data-date-end-date="0d" data-toggle="tooltip" data-placement="top" title="Date of Birth"></div>';
    }

    // Date of Joining
    if($model->isNewRecord){
        echo '<div class="form-group col-md-4"><input class="form-control datepicker_jd" name="usermeta[joining_date]" placeholder="Date of Joining"  data-date-format="mm-dd-yyyy" data-date-end-date="0d" data-toggle="tooltip" data-placement="top" title="Date of Joining"></div>';
    } else {
    $user_joining_date = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'joining_date' ])->all();
    $joining_date = ArrayHelper::map($user_joining_date, 'uid', 'meta_value');
    $ujdate = '';
        if($joining_date[$model->id]){
            $ujdate = $joining_date[$model->id];
        }
    echo '<div class="form-group col-md-4"><input class="form-control datepicker_jd" name="usermeta[joining_date]" placeholder="Date of Joining" value="'.$ujdate.'" data-date-format="mm-dd-yyyy" data-date-end-date="0d" data-toggle="tooltip" data-placement="top" title="Date of Joining"></div>';
        
    }
	// PAN Number
    if($model->isNewRecord){
        echo '<div class="form-group col-md-4"><input class="form-control" name="usermeta[pan_number]" placeholder="PAN Number" data-toggle="tooltip" data-placement="top" title="PAN Number"></div>';
    } else {
        $pan_link = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'fb_link' ])->all();
        $pan_link_result = ArrayHelper::map($pan_link, 'uid', 'meta_value');
        $pan_link_value = '';
        if(isset($pan_link_result[$model->id])){
            $pan_link_value = $pan_link_result[$model->id];
        }
        echo '<div class="form-group col-md-4"><input class="form-control" name="usermeta[pan_number]" placeholder="PAN Number" value="'.$pan_link_value.'" data-toggle="tooltip" data-placement="top" title="PAN Number"></div>';
    }
    
    // Gender
    echo $form->field($model, 'gender')->radioList(['male' => 'Male', 'female' => 'Female'],['required' => 'required'])->label('Gender');
    
    

    
	echo '<div class="form-group col-md-12 no-margin"><h3 class="blue_color">Social</h3></div>';

	
	// fb Social links
    if($model->isNewRecord){
        echo '<div class="form-group col-md-4"><input class="form-control" name="usermeta[fb_link]" placeholder="Facebook Profile URL" data-toggle="tooltip" data-placement="top" title="Facebook Profile URL"></div>';
    } else {
        $fb_link = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'fb_link' ])->all();
        $fb_link_result = ArrayHelper::map($fb_link, 'uid', 'meta_value');
        $fb_link_value = '';
            if(isset($fb_link_result[$model->id])){
                $fb_link_value = $fb_link_result[$model->id];
            }
        echo '<div class="form-group col-md-4"><input class="form-control" name="usermeta[fb_link]" placeholder="Facebook Profile URL" value="'.$fb_link_value.'" data-toggle="tooltip" data-placement="top" title="Facebook Profile URL"></div>';
    }

    // Linkedin link
    if($model->isNewRecord){
        echo '<div class="form-group col-md-4"><input class="form-control" name="usermeta[linkedin_link]" placeholder="Linkedin Profile URL" data-toggle="tooltip" data-placement="top" title="Linkedin Profile URL"></div>';
    } else {
        $linkedin_link = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'fb_link' ])->all();
        $linkedin_link_result = ArrayHelper::map($linkedin_link, 'uid', 'meta_value');
        $linkedin_link_value = '';
            if(isset($linkedin_link_result[$model->id])){
                $linkedin_link_value = $linkedin_link_result[$model->id];
            }
        echo '<div class="form-group col-md-4"><input class="form-control" name="usermeta[linkedin_link]" placeholder="LinkedIn Profile URL" value="'.$linkedin_link_value.'" data-toggle="tooltip" data-placement="top" title="LinkedIn Profile URL"></div>';
    }
	
	
	// Twitter link
    if($model->isNewRecord){
        echo '<div class="form-group col-md-4"><input class="form-control" name="usermeta[twitter_link]" placeholder="Twitter Profile URL" data-toggle="tooltip" data-placement="top" title="Twitter Profile URL"></div>';
    } else {
        $twitter_link = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'twitter_link' ])->all();
        $twitter_link_result = ArrayHelper::map($twitter_link, 'uid', 'meta_value');
        $twitter_link_value = '';
            if(isset($twitter_link_result[$model->id])){
                $twitter_link_value = $twitter_link_result[$model->id];
            }
        echo '<div class="form-group col-md-4"><input class="form-control" name="usermeta[twitter_link]" placeholder="Twitter Profile URL" value="'.$twitter_link_value.'" data-toggle="tooltip" data-placement="top" title="Twitter Profile URL"></div>';
    }
	
	
    echo '<div class="form-group col-md-12 no-margin"><h3 class="blue_color">Address</h3></div>';

    // Address
    if($model->isNewRecord){
        echo '<div class="form-group col-md-4 field-dvusers-address"><textarea id="dvusers-address" class="form-control" name="usermeta[address]" rows="2" placeholder="Address" data-toggle="tooltip" data-placement="top" title="Address"></textarea></div>';
    } else {
        $user_address = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'address' ])->all();
        $address = ArrayHelper::map($user_address, 'uid', 'meta_value');
        $uaddress = '';
        if($address[$model->id]){
            $uaddress = $address[$model->id];
        }
    echo '<div class="form-group col-md-4 field-dvusers-address"><textarea id="dvusers-address" class="form-control" name="usermeta[address]" rows="2" placeholder="Address" data-toggle="tooltip" data-placement="top" title="Address">'.$address[$model->id].'</textarea></div>';
        
    }

    // Country
     if($model->isNewRecord){
        $select = 'selected="selected"';
        $ucountry = '101';
     } else{
        $select = 'selected="selected"';
        $user_country = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'country' ])->all();
        $u_country = ArrayHelper::map($user_country, 'uid', 'meta_value');
        $ucountry = $u_country[$model->id]; 
     }
     $country = DvCountry::find()->all();
     $Dv_country = ArrayHelper::map($country, 'id', 'name');
     echo '<div class="form-group col-md-4 field-country">';
     echo '<select id="country" class="form-control" name="usermeta[country]" required="required" data-toggle="tooltip" data-placement="top" title="Select Country">';
     echo '<option value="">Select Country</option>';
        foreach($Dv_country as $key => $val){
            echo '<option ';
             if($ucountry == $key){
                echo $select;
            }
            echo ' value="'.$key.'">'. $val.'</option>';
        }
        echo '</select></div>';


    // State
    if($model->isNewRecord){
        $ucountry = '101';
        $States = DvStates::find()->where(['country_id' => $ucountry ])->all();
        $stateid = '';        
    } else {
        $user_state = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'state' ])->all();
        $state = ArrayHelper::map($user_state, 'uid', 'meta_value');
        $States = DvStates::find()->where(['country_id' => $ucountry ])->all();
        $stateid = $state[$model->id];
    }
    $Dv_States = ArrayHelper::map($States, 'id', 'name');
    echo '<div class="form-group col-md-4">';
    echo '<select id="dvusers-state" class="form-control" name="usermeta[state]" required="required" aria-invalid="false" data-toggle="tooltip" data-placement="top" title="Select State">';
    echo '<option value="">Select State</option>';
        foreach($Dv_States as $key => $val){
            echo '<option ';
             if($stateid == $key){
                echo $select;
            }
            echo ' value="'.$key.'">'. $val .'</option>';
        }    
    echo '</select></div>';


    // City
    if($model->isNewRecord){
        echo '<div class="form-group col-md-4">';
        echo '<select id="dvusers-city" class="form-control" name="usermeta[city]" required="required" data-toggle="tooltip" data-placement="top" title="Select City">';
        echo '<option value="">Select City</option>';
        echo '</select>';
        echo '</div>';
    } else {
        $user_city = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'city' ])->all();
        $ucity = ArrayHelper::map($user_city, 'uid', 'meta_value');
        $city = DvCities::find()->where(['state_id' => $state[$model->id] ])->all();
        $Dv_city = ArrayHelper::map($city, 'id', 'name');
        echo '<div class="form-group col-md-4">';
        echo '<select id="dvusers-city" class="form-control" name="usermeta[city]" required="required" data-toggle="tooltip" data-placement="top" title="Select City">';
        echo '<option value="">Select City</option>';
         foreach($Dv_city as $key => $val){
            echo '<option ';
             if($ucity[$model->id] == $key){
                echo $select;
            }
            echo ' value="'.$key.'">'. $val .'</option>';
        }    
        echo '</select></div>';
    }

    echo '<div class="form-group col-md-12 no-margin"><h3 class="blue_color">Department</h3></div>';

    // Department
    $Userdepartment = DvUsersDepartment::find()->where(['status'=>1])->all();
    $Udepartment = ArrayHelper::map($Userdepartment, 'id', 'name');
    echo $form->field($model, 'department')->dropDownList($Udepartment, ['prompt'=>'Select Department',
        'id'=>"department",'required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Select Department"])->label(false);

  // Team
    if($model->isNewRecord){
        echo '<input type="hidden" id="dvusers-team" class="form-control" name="usermeta[team]" value="">';
    } else {
        $user_team = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'team' ])->all();
        $team = ArrayHelper::map($user_team, 'uid', 'meta_value');
        if(empty($team[$model->id])){
            $u_team = '';
            $u_team_id = '';
        } else {
            $u_team = DvUsers::find()->where(['id'=>$team[$model->id]])->one()->first_name;
            $u_team .= ' ';
            $u_team .= DvUsers::find()->where(['id'=>$team[$model->id]])->one()->last_name;
            $u_team_id = DvUsers::find()->where(['id'=>$team[$model->id]])->one()->id;
        }
        
        $user_role = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'role' ])->all();
        $role = ArrayHelper::map($user_role, 'uid', 'meta_value');
        $u_role = $role[$model->id];

        if($u_role == 7){
        echo '<div class="form-group col-md-4 dvuserteam cdo">';

        if($u_role == 6){
            echo '<style>.dvuserteam{display:none;}</style>';
            echo '<select id="dvusers-team" class="form-control hide" name="usermeta[team]" aria-invalid="false" data-toggle="tooltip" data-placement="top" title="Select Manager">';
        } else {
            echo '<select id="dvusers-team" class="form-control" name="usermeta[team]" aria-invalid="false" data-toggle="tooltip" data-placement="top" title="Select Manager">';
            echo '<option value=""> ---- </option>';

            $dv_users_by_dep = Yii::$app->db->createCommand("SELECT id, first_name, last_name FROM assist_users WHERE department = '$model->department' ")->queryAll();
            foreach($dv_users_by_dep as $dvusers){
                $user_id_by_dep = $dvusers['id'];

                // check if the user is Manager
                $check_users_dep = Yii::$app->db->createCommand("SELECT id FROM assist_user_meta WHERE uid = '$user_id_by_dep' AND meta_key = 'role' AND meta_value = '6' ")->queryAll();
                if(!empty($check_users_dep)){
                    if($team[$model->id] == $dvusers['id']){
                        echo '<option selected="selected" value="'.$dvusers['id'].'">'.$dvusers['first_name'].' '.$dvusers['last_name'].'</option>';
                    } else {
                        echo '<option value="'.$dvusers['id'].'">'.$dvusers['first_name'].' '.$dvusers['last_name'].'</option>';
                    }
                }
            }
        }
        
        echo '</select>';
        echo '</div>'; 
        }
    }  
    // User Role
    if($model->isNewRecord){
        $u_role = $select = '';
        echo '<div class="form-group col-md-4 field-UsersRole">';
        echo '<select id="UsersRole" class="form-control" name="usermeta[role]" required="required" aria-invalid="false" data-toggle="tooltip" data-placement="top" title="" data-original-title="Select Role"><option value="">Select Role</option></select>';
        echo '</div>';
    } else {
        $user_role = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'role' ])->all();
        $role = ArrayHelper::map($user_role, 'uid', 'meta_value');
        $u_role = $role[$model->id];
        $select = 'selected="selected"';
        $UserRole = DvUsersRole::find()->where(['status'=>1])->all();
        $Urole = ArrayHelper::map($UserRole, 'id', 'name');        
        
        if(!Yii::$app->CustomComponents->check_permission('all_user')){
            unset($Urole[1]);         
        }

        echo '<div class="form-group col-md-4 field-UsersRole">';
      if($u_role == '6'){
        $user_team = DvUserMeta::find()->where(['meta_value'=> $model->id,'meta_key' => 'team'])->all();
        $userteam = ArrayHelper::map($user_team, 'uid', 'meta_value');
            if(empty($userteam)){
                  echo '<select id="UsersRole" class="form-control" name="usermeta[role]" required="required" aria-invalid="false" data-toggle="tooltip" data-placement="top" title="Select Role">';
            } else {
                echo '<select disabled="disabled" id="UsersRole" class="form-control" name="usermeta[role]" required="required" aria-invalid="false" data-toggle="tooltip" data-placement="top" title="To Remove Current User Role as Manager First Please remove all the users from there TEAM. Go to dv-users -> assign_team ">';
            }
            
        } else {
            echo '<select id="UsersRole" class="form-control" name="usermeta[role]" required="required" aria-invalid="false" data-toggle="tooltip" data-placement="top" title="Select Role">';
        }

        echo '<option value="">Select Role</option>';
        foreach($Urole as $key => $val){
            echo '<option ';
            if($u_role == $key){
                echo $select;
            } 
            echo ' value="'.$key.'">'.$val.'</option>';
        }
        echo '</select>';
        echo '</div>';
    }
     
    ?>
    <div class="col-md-4">
        <select onchange="get_course_data();" id="course_domain" class="form-control" name="course_domain" data-toggle="tooltip" data-placement="top" title="" data-original-title="Select Domain">
            <option value="">Select Domain</option>
            <option value="dm">DM</option>
            <option value="da">DA</option>
        </select>
    </div>
    <?php
     
    if($model->isNewRecord){ // New record
        echo '<div id="usercourse" class="hide">';
        echo $form->field($model, 'course')->hiddenInput(['value'=>''])->label(false);
        echo '<input id="dvusers-day_avail" class="form-control" name="usermeta[day_avail]" value="" type="hidden">';
        echo '<input id="dvusers-coordinator" class="form-control" name="usermeta[coordinator]" value="" type="hidden">';
        echo '</div>';
    } else {
        // New record
        echo '<div id="usercourse" class="">';
        //print_r($model->course);
        $userdepartment = $model->department;
        if(($userdepartment == 1) || ($userdepartment == 2) || ($userdepartment == 7)){
            //$course = DvCourse::find()->where(['status'=>1])->all();
            //$Dv_course = ArrayHelper::map($course, 'id', 'name');
            //added on 20 April 2019 by KK/PP
            $course = DvModuleModel::find()->where(['status'=>1])->all();
            $Dv_course = ArrayHelper::map($course, 'id', 'module_name');
            //unset($Dv_course[1]); unset($Dv_course[2]);
            ?>

        </div>
            <?php
            echo '<div class="form-group col-md-4 field-dvusers-course">';
            echo '<input name="DvUsers[course]" value="" type="hidden">';
            echo '<select id="user_course" class="form-control" name="DvUsers[course][]" multiple="multiple" size="4" required="required" data-toggle="tooltip" data-placement="top" title="Select Module">';
            if(($userdepartment == 1)||($userdepartment == 2) ){
                
                if(isset($model->course[0])){
                    if(($model->course[0] =='dm') && (empty($model->course[1]))){
                        echo '<option value="dm" selected="selected">DM</option>';
                        echo '<option value="da">DA</option>';
                    } elseif(($model->course[0] =='da')&&(empty($model->course[1]))){
                        echo '<option value="dm" >DM</option>';
                        echo '<option value="da" selected="selected">DA</option>';
                    } elseif(($model->course[0] =='dm')&&(!empty($model->course[1]))){
                        echo '<option value="dm" selected="selected">DM</option>';
                    } elseif(($model->course[0] =='da')&&(!empty($model->course[1]))){
                        echo '<option value="da" selected="selected">DA</option>';
                    } else {
                        echo '<option value="dm">DM</option>';
                    }
                }
                
                if(isset($model->course[1])){
                    if($model->course[1] =='dm'){
                        echo '<option value="dm" selected="selected">DM</option>';
                    } elseif($model->course[1] =='da'){
                        echo '<option value="da" selected="selected">DA</option>';
                    } else {
                        echo '<option value="dm">DM</option>';
                    }
                }                
                
            } else { //print_r($Dv_course);
                foreach($Dv_course as $id => $name){
                    $kid = array($id);
                    if(!empty($model->course)){
                        $result=array_intersect($model->course,$kid);
                        //$result=array_intersect($model->course,$kid);
                    }
                    if(empty($result)){
                        echo '<option value="'.$id.'">'.$name.'</option>';
                    } else {
                        echo '<option selected="selected" value="'.$id.'">'.$name.'</option>';
                    }
                }
            }
            echo '</select></div>';
        }

        // Day Available
        if($userdepartment == 7){
            $user_day_avail = DvUserMeta::find()->where(['uid' => $model->id, 'meta_key' => 'day_avail'])->all();
            $day_avail = ArrayHelper::map($user_day_avail, 'uid', 'meta_value');
            if($day_avail[$model->id]){
                echo '<div class="form-group col-md-4 field-dvusers-day_avail">';
                echo '<input name="DvUsers[day_avail]" value="" type="hidden">';
                echo '<select id="dvusers-day_avail" class="form-control" name="usermeta[day_avail][]" multiple="multiple" size="4" required="required" data-toggle="tooltip" data-placement="top" title="Day Available">';
                $sunday = '<option value="sun">Sunday</option>';
                $monday = '<option value="mon">Monday</option>';
                $tuesday = '<option value="tue">Tuesday</option>';
                $wednesday = '<option value="wed">Wednesday</option>';
                $thursday = '<option value="thur">Thursday</option>';
                $friday = '<option value="fri">Friday</option>';
                $saturday = '<option value="sat">Saturday</option>';

                $days = explode(',', $day_avail[$model->id]);
                $count = count($days);
                $i = 0;
                foreach($days as $value){
                    $i++;
                    if($value == 'sun'){
                        $sunday = '<option selected="selected" value="sun">Sunday</option>';
                    } else if($value == 'mon'){
                        $monday = '<option selected="selected" value="mon">Monday</option>';
                    } else if($value == 'tue'){
                        $tuesday = '<option selected="selected" value="tue">Tuesday</option>';
                    } else if($value == 'wed'){
                        $wednesday = '<option selected="selected" value="wed">Wednesday</option>';
                    } else if($value == 'thur'){
                        $thursday = '<option selected="selected" value="thur">Thursday</option>';
                    } else if($value == 'fri'){
                        $friday = '<option selected="selected" value="fri">Friday</option>';
                    } else if($value == 'sat'){
                        $saturday = '<option selected="selected" value="sat">Saturday</option>';
                    }
                }
                echo $sunday.$monday.$tuesday.$wednesday.$thursday.$friday.$saturday;
                echo '</select></div>';
            }
        }

        // Coordinator
        if(($userdepartment == 2) || ($userdepartment == 7)){  
            $user_coor = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'coordinator' ])->all();
            $coordinator = ArrayHelper::map($user_coor, 'uid', 'meta_value');
            if(isset($coordinator[$model->id])){
                echo '<div class="form-group col-md-4 field-dvusers-coordinator">';
                echo '<select id="dvusers-coordinator" class="form-control" name="DvUsers[coordinator]" data-toggle="tooltip" data-placement="top" title="Select Coordinator" >';
                if(empty($coordinator[$model->id])){
                    echo '<option value="">Select Course Coordinator</option>';
                } else {
                    echo '<option value="'.$coordinator[$model->id].'">';
                    echo DvUsers::find()->where(['id'=>$coordinator[$model->id]])->one()->first_name;
                    echo ' ';
                    echo DvUsers::find()->where(['id'=>$coordinator[$model->id]])->one()->last_name;
                    echo '</option>';
                }
                echo '</select></div>';
            }
            
        }

        if($userdepartment == 7) {
            $trainerid = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'trainerid' ])->all();
            if(count($trainerid) > 0){ 
                $trainerid_result = ArrayHelper::map($trainerid, 'uid', 'meta_value');
                $trainerid_value = '';
                if(isset($trainerid_result)){
                    if($trainerid_result[$model->id]){
                        $trainerid_value = $trainerid_result[$model->id];
                    }
                }
                echo '<div class="form-group col-md-4"><input class="form-control" name="usermeta[trainerid]" placeholder="TrainerID" readonly="readonly" value="'.$trainerid_value.'" data-toggle="tooltip" data-placement="top" title="TrainerID"></div>';

            }
            $compensation = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'compensation' ])->all();
            if(count($compensation) > 0){
                $compensation_result = ArrayHelper::map($compensation, 'uid', 'meta_value');
                $compensation_value = '';
                if(isset($compensation_result)){
                    if($compensation_result[$model->id]){
                        $compensation_value = $compensation_result[$model->id];
                    }
                }
                echo '<div class="form-group col-md-4"><input class="form-control" name="usermeta[compensation]" placeholder="Compensation" value="'.$compensation_value.'" data-toggle="tooltip" data-placement="top" title="Compensation" type="number" pattern="\d*"></div>';
            }

            $module_led = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'module_led' ])->all();
            if(count($module_led) > 0){
                $module_led_result = ArrayHelper::map($module_led, 'uid', 'meta_value');
                $module_led_value = '';
                if(isset($module_led_value)){
                    if($module_led_result[$model->id]){
                        $module_led_value = $module_led_result[$model->id];
                    }
                }
                echo '<div class="form-group col-md-4"><select class="form-control" multiple name="usermeta[module_led]" placeholder="Module to be Lead" data-toggle="tooltip" data-placement="top" title="Module to be Lead">
                    <option value="">Select Any One</option>
                    <option value="1"';
                    echo ($module_led_value==1)?"selected='selected'":'';
                    echo '>Core</option><option value="2"';
                    echo ($module_led_value==2)?"selected='selected'":'';
                    echo '>Special</option></select>';
                echo '</div>';
            }
            $profile_visibility_meta = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'profile_visibility' ])->all();
            if(count($profile_visibility_meta) > 0){
                $profile_visibility = "";
                if (!empty($profile_visibility_meta)) {
                    foreach ($profile_visibility_meta as $key => $value) {
                        $profile_visibility = $value['meta_value'];
                    }
                }
                echo '<div class="form-group col-md-4"><select class="form-control" name="usermeta[profile_visibility]" data-toggle="tooltip" data-placement="top" title="profile_visibility">
                    <option value="">Select Profile Visibility</option>
                    <option value="1"';
                    echo ($profile_visibility==1)?"selected='selected'":'';
                    echo '>Publish</option><option value="2"';
                    echo ($profile_visibility==2)?"selected='selected'":'';
                    echo '>Draft</option></select>';
                echo '</div>';
            }


            $description = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'description' ])->all();
            if(count($description) > 0){
                $description_result = ArrayHelper::map($description, 'uid', 'meta_value');
                $description_value = '';
                if(isset($description_result)){
                    if($description_result[$model->id]){
                        $description_value = $description_result[$model->id];
                    }
                }
                echo '<div class="form-group col-md-12"><textarea class="form-control" name="usermeta[description]" placeholder="Description" data-toggle="tooltip" data-placement="top" title="Description">'.$description_value.'</textarea></div>';
            }
        }
        echo '</div>';
    }    // new record

    if($model->isNewRecord){
        echo $form->field($model, 'status')->hiddenInput(['value'=>'1'])->label(false);
    } else {
        echo $form->field($model, 'status')->dropDownList([1 =>'Active',0 => 'Inactive'],['prompt'=>'Select User Status','required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"User Status"])->label(false);
    }
    echo $form->field($model, 'last_logged')->hiddenInput(['value'=>''])->label(false);
?>

<?php
    if($model->isNewRecord){
        echo '<div class="form-group col-md-12">';
        echo '<div class="form-group col-md-4">';
        echo '<input name="usermeta[notify]" value="0" type="hidden">';
        echo '<label><input name="usermeta[notify]" value="1" type="checkbox">';
        echo ' Send Account Detail Email to User';
        echo '</label>';
        echo '</div></div>';
    }
    echo '<div class="form-group col-md-12">';
    ?>
    <?php
    if($model->isNewRecord){
        echo Html::submitButton('<i class="fa fa-check"></i> Create User', ['class' => 'btn btn-success','id'=>'create_user']);
    } else {
        echo Html::submitButton('<i class="fa fa-pencil"></i> Update User', ['class' => 'btn btn-primary','id'=>'update_user']);
    }
    echo Html::a('<i class="fa fa-times"></i> Cancel', ['index'], ['class' => 'btn back_button btn-danger cancel_button']);
    echo '</div>';
    ActiveForm::end();
echo '</div>'; ?>

    <?php if(!$model->isNewRecord && $userdepartment == 7){ ?>
<script>
CKEDITOR.replace( 'usermeta[description]' );
</script>
    <?php }?>
<script type="text/javascript">
//Begin 03 June 2019
function get_course_data(){  
    var course_domain = $('#course_domain').val();
    if(course_domain!=''){
        $("#loading_custom").show();
        $.ajax({
            url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-users/course_domain_check') ?>',
            type: 'POST',
            data: { course_domain : course_domain },
            success: function(data){
                $("#loading_custom").hide();
                $('#user_course').replaceWith(data);
            }
        });
    }
}//End of JS:get_course_data
</script>