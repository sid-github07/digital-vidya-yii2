<?php

namespace app\components;


use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
//use app\models\MwAssignedModules;
use app\models\DvUsersRole;
use app\models\DvUserMeta;
use app\models\DvUsers;
use app\models\DvUsersActivityLog;

class CustomComponents extends Component{
    // function for super admin
    public function check_permission($access){        
        $output = 0;
        if(!empty(Yii::$app->getUser()->identity->id)){            
            $user_id = Yii::$app->getUser()->identity->id;
            $super_admin = DvUserMeta::find()->where(['uid' => $user_id, 'meta_key' => 'role' ])->one()->meta_value;

            if($super_admin == '1'){ // when user is super admin
                $output = 1;
            } else {
                $user_role = DvUserMeta::find()->where(['uid' => $user_id, 'meta_key' => 'role' ])->one()->meta_value;

                $user_role_access = DvUsersRole::find()->where(['id' => $user_role])->one()->access;
                $access_data = explode(' ', $user_role_access);
                if (in_array($access, $access_data)){
                    $output = 1;
                }

                // for view/update user profile
                $current_user_id = Yii::$app->getUser()->identity->id;
                $userid = Yii::$app->request->get('id');
                if(($current_user_id == $userid && $access == 'view_user') || ($current_user_id == $userid && $access == 'edit_user' )){
                    $output = 1;
                }

                
            }
        }
       return $output;
    }

    // function for Coordinator
    public function is_coordinator(){        
        if(!empty(Yii::$app->getUser()->identity->id)){
            $result = DvUserMeta::find()->where(['uid' => Yii::$app->getUser()->identity->id, 'meta_key' => 'role' ])->one()->meta_value;

            if($result == '1'){
                return 1;
            }
        }
       return 0;
    }

/*        if(isset(Yii::$app->getUser()->identity->role)){
            if(Yii::$app->getUser()->identity->role == 'super_admin'){
                return 1;
            }

        }*/
       

    public function dvuser_avatar($uid){
        $output = Yii::$app->params['yii_url'].'/uploads/user_image/';
        $image = DvUsers::find()->where(['id' => $uid])->one()->picture;
        $output .= $image;
        if(empty($image)){
            $gender = DvUsers::find()->where(['id' => $uid])->one()->gender;
            if($gender == 'male'){
                $output .= 'male.png';
            } elseif($gender == 'female'){
                $output .= 'female.png';
            }
        }
        return $output;
    }


 

  

    // check permission of user for a module
  /*  public function has_permission($module_id){
        //$result = MwAssignedModules::find()->where(['user_id' => Yii::$app->getUser()->identity->id,'module_id' => $module_id ])->count();
        $result = DvUserMeta::find()->where(['uid' => Yii::$app->getUser()->identity->id, 'meta_key' => 'role' ])->one()->meta_value;    
        if($result >= 1){
            return 1;
        }
        else{
            return 0;
        }
    }*/

 

    //date format
    public function date_formatting($date){
        if(!empty($date)) {
            $date = strtotime($date);
            return date('d-M-Y', $date);
        }else {
            return "";
        }
    }

    // wordrpess database
    public function wordpress_db_connect(){
        $dbhost = 'localhost';
        $dbuser = 'digitalvidya_com';
        $dbpass = 'hl5icmYF4v3zAy8';
        $db = 'digitalvidya_com';
        $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $db);
        return $conn;
    }

    
     // Create users activity log.
    function create_UsersActivityLog($participant_id,$page_url,$meta_key,$old_value,$new_value) {
        $user = Yii::$app->user->identity;
        $userslog = new DvUsersActivityLog;
        $userslog['user_id']  = $user->id;
        $userslog['participant_id']  = $participant_id;
        $userslog['page_url']  = $page_url;
        $userslog['meta_key']  = $meta_key;
        $userslog['old_value']  = $old_value;
        $userslog['new_value']  = $new_value;

      /*echo "<pre>";
        print_r($userslog);
        die;*/

        $userslog->save();
        if($userslog){
            return $userslog->id;
        }
    }

}