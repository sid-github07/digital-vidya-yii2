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
use app\models\DvModuleModel;
/* @var $this yii\web\View */
/* @var $model app\models\DvUsers */
// This file created to dispaly the detail of A user.
$tm_facebook = "";
$tm_twitter = "";
$tm_linkedin = "";
$tm_description = "";
$tm_phone = "";
if (!empty($user_social_meta) ) {
    $user_social_meta = json_decode($user_social_meta);
    if(isset($user_social_meta->result) && $user_social_meta->result !="empty") {
            if(isset($user_social_meta->result->tm_facebook)){
                $tm_facebook = $user_social_meta->result->tm_facebook;
            }
            if(isset($user_social_meta->result->tm_twitter)){
                $tm_twitter = $user_social_meta->result->tm_twitter;
            }
            if(isset($user_social_meta->result->tm_linkedin)){
                  $tm_linkedin = $user_social_meta->result->tm_linkedin;
            }
            if(isset($user_social_meta->result->tm_description)){
                 $tm_description = $user_social_meta->result->tm_description;
            }
            if(isset($user_social_meta->result->tm_phone)){
                 $tm_phone = $user_social_meta->result->tm_phone;
            }
        
        
      
       
        
    }
}
$this->title = 'User: '.$model->first_name.' '.$model->last_name;
$this->params['breadcrumbs'][] = ['label' => 'All Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->first_name.' '.$model->last_name; ?>
<div style="min-height:35px; "></div>

<div class="container">
  <div class="row">
    <div class="col-md-6">
        
        <div class="form-group col-md-12 text-center">        
            <img width="150" height="150" src="<?php echo Yii::$app->CustomComponents->dvuser_avatar($model->id); ?>" class="img-circle" alt="User Image">
            <div class="help-block"></div>
        </div>

        <div class="dv-users-view">
        <table id="w0" class="table table-striped table-bordered detail-view">
           <tbody>
            <tr><th colspan="2"><h3 class="blue_color">Profile</h3></th></tr>
            <tr><th>Name</th><td><?php echo $model->first_name.' '.$model->last_name; ?></td></tr>
            <tr><th>Username</th><td><?php echo $model->username; ?></td></tr>
            <tr><th>Email</th><td><a href="mailto:<?php echo $model->email; ?>"><?php echo $model->email; ?></a></td></tr>

            <?php
            // Phone Mobile number
            $user_phone = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'phone' ])->all();
            $phone = ArrayHelper::map($user_phone, 'uid', 'meta_value');
            $phone_no = '';
            if($phone[$model->id]){
            	$phone_no = $phone[$model->id];
            }
            if ($current_user_role == 4) {
                if (empty($tm_phone)) {
                    echo '<tr><th>Phone Number</th><td>-</td></tr>';
                } else {
                    echo '<tr><th>Phone Number</th><td>'.$tm_phone.'</td></tr>';
                }
            } else {
                echo '<tr><th>Phone Number</th><td>'.$phone_no.'</td></tr>';
            }
             

            // Date of Birth
            $user_dob = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'dob' ])->all();
            $dob = ArrayHelper::map($user_dob, 'uid', 'meta_value');
            $udob = '';
            if($dob[$model->id]){
            	$udob = Yii::$app->CustomComponents->date_formatting($dob[$model->id]);
            }
            echo '<tr><th>Date of Birth</th><td>'.$udob.'</td></tr>';

            // Date of Joining
            $user_joining_date = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'joining_date' ])->all();
            $joining_date = ArrayHelper::map($user_joining_date, 'uid', 'meta_value');
            $ujdate = '';
            if($joining_date[$model->id]){
            	$ujdate = Yii::$app->CustomComponents->date_formatting($joining_date[$model->id]);
            }
            echo '<tr><th>Date of Joining</th><td>'.$ujdate.'</td></tr>';
             
            
            // Facebook profile url
            $fb_link = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'fb_link' ])->all();
            $fb_link_result = ArrayHelper::map($fb_link, 'uid', 'meta_value');
            $fb_link_value = '-';
            if($fb_link_result){
                if($fb_link_result[$model->id]){
                    $fb_link_value = $fb_link_result[$model->id];
                }
            }
            if ($current_user_role == 4) {
                if (empty($tm_facebook)) {
                    echo '<tr><th>Facebook Profile Page</th><td>-</td></tr>';
                } else {
                    echo '<tr><th>Facebook Profile Page</th><td><a href="'.$tm_facebook.'">'.$tm_facebook.'</a></td></tr>';
                }
            } else {
                echo '<tr><th>Facebook Profile Page</th><td><a href="'.$fb_link_value.'">'.$fb_link_value.'</a></td></tr>';
            }
            

            // LinkedIn profile url
            $linkedin_link = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'linkedin_link' ])->all();
            $linkedin_link_result = ArrayHelper::map($linkedin_link, 'uid', 'meta_value');
            $linkedin_link_value = '-';
            if($linkedin_link_result){
                if($linkedin_link_result[$model->id]){
                    $linkedin_link_value = $linkedin_link_result[$model->id];
                }
            }
            if ($current_user_role == 4) {
                if (empty($tm_linkedin)) {
                    echo '<tr><th>LinkedIn Profile URL</th><td>-</td></tr>';
                } else {
                    echo '<tr><th>LinkedIn Profile URL</th><td><a href="'.$tm_linkedin.'">'.$tm_linkedin.'</a></td></tr>';
                }
            } else {
                echo '<tr><th>LinkedIn Profile URL</th><td><a href="'.$linkedin_link_value.'">'.$linkedin_link_value.'</a></td></tr>';
            }
			
			 // Twitter profile url
            $twitter_link = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'twitter_link' ])->all();
            $twitter_link_result = ArrayHelper::map($twitter_link, 'uid', 'meta_value');
            $twitter_link_value = '-';
            if($twitter_link_result){
                if($twitter_link_result[$model->id]){
                    $twitter_link_value = $twitter_link_result[$model->id];
                }
            }
            if ($current_user_role == 4) {
                if (empty($tm_twitter)) {
                    echo '<tr><th>Twitter Profile URL</th><td>-</td></tr>';
                } else {
                    echo '<tr><th>Twitter Profile URL</th><td><a href="'.$tm_twitter.'">'.$tm_twitter.'</a></td></tr>';
                }
            } else {
                echo '<tr><th>Twitter Profile URL</th><td><a href="'.$twitter_link_value.'">'.$twitter_link_value.'</a></td></tr>';
            }
			
			
			 // trainer profile url
            $profile_link = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'trainer_profile_url' ])->all();
            $profile_link_result = ArrayHelper::map($profile_link, 'uid', 'meta_value');
            $profile_link_value = '-';
            if($profile_link_result){
                if($profile_link_result[$model->id]){
                    $profile_link_value = $profile_link_result[$model->id];
                }
            }else{
				$profile_link_post = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'wp_post_id' ])->all();
				$profile_link_post_result = ArrayHelper::map($profile_link_post, 'uid', 'meta_value');
				if($profile_link_post_result){
					if($profile_link_post_result[$model->id]){
						$pst_id = $profile_link_post_result[$model->id];
						$profile_link_value = $_SERVER['SERVER_NAME']."/?p=".$pst_id;
						
					}
				}
				
			}
            if ($current_user_role == 4) {
                if (empty($profile_link_value)) {
                    echo '<tr><th>Profile Page</th><td>-</td></tr>';
                } else {
                    echo '<tr><th>Profile Page</th><td><a href="'.$profile_link_value.'">'.$profile_link_value.'</a></td></tr>';
                }
            } 
            
            
            // PAN Number
            $pan_number = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'pan_number' ])->all();
            $pan_number_result = ArrayHelper::map($pan_number, 'uid', 'meta_value');
            $pan_number_value = '-';
            if($pan_number_result){
                if($pan_number_result[$model->id]){
                    $pan_number_value = $pan_number_result[$model->id];
                }
            }
            echo '<tr><th>PAN Number</th><td>'.$pan_number_value.'</td></tr>';
            

            $profile_visibility_meta = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'profile_visibility' ])->all();
            $profile_visibility = "";
            if (!empty($profile_visibility_meta)) {
                foreach ($profile_visibility_meta as $key => $value) {
                    if($value['meta_value'] == 1) {
                        $profile_visibility = "Publish";
                    } else if($value['meta_value'] == 2) {
                        $profile_visibility = "Draft";
                    }
                }
            }
            $userRole = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'role' ])->all();
            $usersrole = ArrayHelper::map($userRole, 'uid', 'meta_value');
            if($usersrole[$model->id] == 4){
            ?>
                <tr><th>Profile Visibility</th><td><?php echo ucfirst($profile_visibility); ?></td></tr>
            <?php } ?>
            <tr><th>Gender</th><td><?php echo ucfirst($model->gender); ?></td></tr>
            <tr><th colspan="2"><h3 class="blue_color">Address</h3></th></tr>
            <?php // Address
            $user_address = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'address' ])->all();
            $address = ArrayHelper::map($user_address, 'uid', 'meta_value');
            $uaddress = '';
            if($address[$model->id]){
            	$uaddress = $address[$model->id];
            }
            echo '<tr><th>Address</th><td>'.$uaddress.'</td></tr>';

            // City
            $user_city = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'city' ])->all();
            $city = ArrayHelper::map($user_city, 'uid', 'meta_value');
            if($city[$model->id]){
                echo '<tr><th>City</th><td>';
                echo DvCities::find()->where(['id'=>$city[$model->id]])->one()->name;
                echo '</td></tr>';
            }

            // State
            $user_state = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'state' ])->all();
            $state = ArrayHelper::map($user_state, 'uid', 'meta_value');
            if($state[$model->id]){
                echo '<tr><th>State</th><td>';
                echo DvStates::find()->where(['id'=>$state[$model->id]])->one()->name;
                echo '</td></tr>';
            }

            // Country
            $user_country = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'country' ])->all();
            $country = ArrayHelper::map($user_country, 'uid', 'meta_value');
            if($country[$model->id]){
                echo '<tr><th>Country</th><td>';
                echo DvCountry::find()->where(['id'=>$country[$model->id]])->one()->name;
                echo '</td></tr>';
            }  ?>

            <tr><th colspan="2"><h3 class="blue_color">Detail</h3></th></tr>
            <tr><th>Department</th><td><?php
            $user_department = '';
            if($model->department != 0){
                $user_department = DvUsersDepartment::find()->where(['id'=>$model->department])->one()->name;
            }
            echo $user_department;
            ?></td></tr>
            <?php

             // Role
            $user_role = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'role' ])->all();
            $role = ArrayHelper::map($user_role, 'uid', 'meta_value');
            if($role[$model->id]){
                echo '<tr><th>Role</th><td>';
                echo DvUsersRole::find()->where(['id'=>$role[$model->id]])->one()->name;
                if($role[$model->id] == 6){
                    echo ' of '.$user_department.' Department';
                }
                echo '</td></tr>';
            }


    // team (manager name)
       if($role[$model->id] != 6){     
            $user_team = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'team' ])->all();
            $team = ArrayHelper::map($user_team, 'uid', 'meta_value');
            if($team[$model->id]){            
                echo '<tr><th>Manager</th><td>';                
                echo DvUsers::find()->where(['id'=>$team[$model->id]])->one()->first_name;
                echo ' ';
                echo DvUsers::find()->where(['id'=>$team[$model->id]])->one()->last_name;
                echo '</td></tr>';
            } 
        }    

        if($model->course != null){ ?>
             <tr><th><?php echo $model->department == 7 ? "Module" : "Course"; ?></th><td>
             <?php if(($model->department == 1)||($model->department == 2)){
                echo strtoupper($model->course);
             } else {
                $acourse = explode(',', $model->course);
                $output = array();
                foreach($acourse as $value){
                    //$output[] =  DvCourse::find()->where(['id'=>$value])->one()->name;
                    $output[] =  DvModuleModel::find()->where(['id'=>$value])->one()['module_name'];
                }
                echo count($output) > 0 ? implode( ', ', $output) : "";  
             }

               ?>
            </td></tr><?php }

             // Day Available
            $user_day_avail = DvUserMeta::find()->where(['uid' => $model->id, 'meta_key' => 'day_avail'])->all();
            $day_avail = ArrayHelper::map($user_day_avail, 'uid', 'meta_value');
            if(isset($day_avail[$model->id])){
                echo '<tr><th>Day Availablity</th><td>';
                $days = explode(',', $day_avail[$model->id]);
                        $count = count($days);
                    $output = array();
                    $i = 0;
                            foreach($days as $value){
                                $i++;
                                if($value == 'sun'){
                                    echo 'Sunday';
                                } else if($value == 'mon'){
                                    echo 'Monday';
                                } else if($value == 'tue'){
                                    echo 'Tuesday';
                                } else if($value == 'wed'){
                                    echo 'Wednesday';
                                } else if($value == 'thur'){
                                    echo 'Thursday';
                                } else if($value == 'fri'){
                                    echo 'Friday';
                                } else if($value == 'sat'){
                                    echo 'Saturday';
                                }
                                if($count > $i){
                                    echo ', ';    
                                }                               
                            }                        
                        echo implode( ', ', $output); 
                echo '</td></tr>';
            }

            // Coordinator
            $user_coor = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'coordinator' ])->all();
            $coordinator = ArrayHelper::map($user_coor, 'uid', 'meta_value');
            if(!empty($coordinator[$model->id])){
                echo '<tr><th>Coordinator</th><td>';
                echo DvUsers::find()->where(['id'=>$coordinator[$model->id]])->one()->first_name;
                echo ' ';
                echo DvUsers::find()->where(['id'=>$coordinator[$model->id]])->one()->last_name;
                echo '</td></tr>';
            }  ?>

            <?php 
            $user_description = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'description' ])->all();
            $description_details = ArrayHelper::map($user_description, 'uid', 'meta_value');
            $description = '';
            if(isset($description_details[$model->id])){
                $description = $description_details[$model->id];
            }
            if($current_user_role == 4) {
                if (empty($tm_description)) {
                    echo '<tr><th>Description</th><td>-</td></tr>';
                } else {
                    echo '<tr><th>Description</th><td>'.$tm_description.'</td></tr>';
                }
            } else {
                echo '<tr><th>Description</th><td>'.$description.'</td></tr>';
            }
            ?>

            <?php 
            $user_compensation = DvUserMeta::find()->where(['uid' => $model->id , 'meta_key' => 'compensation' ])->all();
            $compensation_details = ArrayHelper::map($user_compensation, 'uid', 'meta_value');
            $compensation = '';
            if(isset($compensation_details[$model->id])){
                $compensation = $compensation_details[$model->id];
            }
            echo '<tr><th>Compensation</th><td>'.$compensation.'</td></tr>';
            ?>

            <tr><th>Status</th><td><?php if($model->status == 1){
                        echo '<label class="label label-success">Active</label>';
                    } else {
                        echo '<label class="label label-danger">Inactive</label>';
                    } ?></td></tr>

    <tr><th colspan="2"><h3 class="blue_color">User Log</h3></th></tr>
            <tr><th>User Logged</th><td><?php echo Yii::$app->CustomComponents->date_formatting($model->last_logged); ?></td></tr>
            <tr><th>Date Created</th><td><?php echo Yii::$app->CustomComponents->date_formatting($model->created); ?></td></tr>
        </tbody>
    </table>
    <p><?= Html::a('<i class="fa fa-pencil"></i> Update User', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']); ?>
        <?= Html::a('<i class="fa fa-times"></i> Cancel', ['index'], ['class' => 'btn back_button btn-danger cancel_button']); ?></p>
    </div>
</div>
    <div class="col-md-6"></div>
  </div>
</div>   