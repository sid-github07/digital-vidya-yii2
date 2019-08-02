<?php

namespace app\controllers;

use Yii;
use app\models\DvUsers;
use yii\web\Controller;
use app\models\DvStates;
use app\models\DvCities;
use app\models\DvCourse;
use app\models\DvUserMeta;
use yii\data\Pagination;
use app\models\DvModules;
use yii\web\UploadedFile;
use app\models\DvUsersRole;
use app\models\DvCourseTarget;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\models\DvUsersDepartment;
use app\models\DvAssignedModules;
use yii\web\NotFoundHttpException;
use app\models\DvModuleModel;

/**
 * DvUsersController implements the CRUD actions for DvUsers model.
 */
class DvUsersController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all DvUsers models.
     * @return mixed
     */
    public function actionIndex() {
        //redirect a user if not super admin
        if (!Yii::$app->CustomComponents->check_permission('all_users')) {
            return $this->redirect(['site/index']);
        }

        $query = DvUsers::find();
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 50]);
        $models = $query->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('index', [ 'users' => $models, 'total_records' => $count, 'pages' => $pagination]);
    }

    public function actionSearch($s) {  // function to search user by name or email address
        //redirect a user if not super admin
        if (!Yii::$app->CustomComponents->check_permission('all_users')) {
            return $this->redirect(['site/index']);
        }
        $s = trim($s);
        if (preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^", $s)) {
            $squery = DvUsers::find()->where(['email' => $s, "status" => 1])->all();
        } else {
            $sq = explode(" ", $s);
            $size = sizeof($sq);
            if ($size > 1) {
                $squery = DvUsers::find()->orWhere(['first_name' => $sq[0]])->orWhere(['first_name' => $sq[1]])->orWhere(['last_name' => $sq[0]])->orWhere(['last_name' => $sq[1]])->andWhere(["status" => 1])->all();
            } else {
                $squery = DvUsers::find()->orWhere(['first_name' => $s])->orWhere(['last_name' => $s])->andWhere(["status" => 1])->all();
            }
        }
        return $this->render('index', [ 'users' => $squery, 'total_records' => '1']);
    }

    public function actionFilter($course_domain,$role, $department){ // funciton to run Filter to find users based on their course, Date, Running Batch Status, Trainer, coordinator and Batch Status.

        //redirect a user if not super admin
        if (!Yii::$app->CustomComponents->check_permission('all_users')) {
            return $this->redirect(['site/index']);
        }

        if(empty($course_domain) && empty($role) && empty($department)){
            return $this->redirect(['dv-users/index']);
        }

        //For Role
        if (!empty($role)) {
            $urole = Yii::$app->db->createCommand("SELECT uid FROM assist_user_meta WHERE meta_key = 'role' AND meta_value = '$role' ")->queryAll();
        } else {
            $urole = array();
        }

        $uteam = array();

        $result = array();
        if (!empty($urole)) {
            if (empty($team)) {
                $result = $urole;
            } else {
                foreach ($urole as $key => $val) {
                    if (in_array($val, $uteam)) {
                        $result[] = $val;
                    }
                }
            }
        } else {
            $result = $uteam;
        }
        //End For Role
        //For Department
        if (!empty($department)) {
            $udep = Yii::$app->db->createCommand("SELECT id FROM assist_users WHERE department = '$department' AND status = 1 ")->queryAll();
            $new_udep = array();
            foreach ($udep as $key => $value) {
                $new_udep[] = array('uid' => $value['id']);
            }
        } else {
            $new_udep = array();
        }

        $result2 = array();
        if (!empty($result)) {
            if (empty($department)) {
                $result2 = $result;
            } else {
                foreach ($result as $key => $val) {
                    if (in_array($val, $new_udep)) {
                        $result2[] = $val;
                    }
                }
            }
        } else {
            $result2 = $new_udep;
        }
        //End For Department
        //Begin of domain filter Added on 03 June 2019
        if(!empty($course_domain)){
            $users_array = array();
            $users = DvUsers::find()->all();
            foreach($users as $user){  
                if($user->course!='' && $user->course!="da" &&  $user->course!="dm" && $user->course!="dm,da"){
                    $module_ids_array = explode(',',$user->course);
                    $module_array = ArrayHelper::map(DvModuleModel::find()->where(['IN','id',$module_ids_array])->all(),'id','mcourse');
                    $module_domain = implode(',',array_unique($module_array));
                    $users_array[$user->id] = $module_domain;
                }else{
                    $module_domain = $user->course;
                    $users_array[$user->id] = $module_domain;
                }
            }
            $users_ids_array = array();
            foreach ($users_array as $key => $value) {
                if(in_array($course_domain,explode(',',$value))){
                    $users_ids_array[] = ['uid'=>$key];
                }
            }
        }else{
            $users_ids_array = array();
        }
        //End of domain filter Added on 03 June 2019
        $result3 = array();
        if (!empty($result2)) {
            if (empty($course_domain)) {
                $result3 = $result2;
            } else {
                foreach ($result2 as $key => $val) {
                    if (in_array($val, $users_ids_array)) {
                        $result3[] = $val;
                    }
                }
            }
        } else {
            $result3 = $users_ids_array;
        }
        //$count = count($result2);
        $count = count($result3);
        $output = '';
        $i = 0;
        foreach ($result3 as $key => $value) {
            foreach ($value as $key => $value2) {
                $i++;
                $output .= $value2;
                if ($count > $i) {
                    $output .= ', ';
                }
            }
        }
        $ids = explode(',', $output);
        $ids = array_unique($ids);
        $count = count($ids);
        $query = DvUsers::find()->where(["status" => 1]);
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 50]);
        $models = $query->Where(['id' => $ids])->offset($pagination->offset)->limit($pagination->limit)->all();

        // echo "<pre>"; print_r($models); die;

        return $this->render('index', [ 'users' => $models, 'total_records' => $count, 'pages' => $pagination]);
    }

    /**
     * Displays a single DvUsers model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id){ // function to view user by their ID
        //redirect a user if not super admin
        if (!Yii::$app->CustomComponents->check_permission('view_user')) {
            return $this->redirect(['index']);            
        }
		$model = DvUsers::find()->where(["id"=>$id])->one();
        $current_user_role = "";

        $get_userrole = $this->getUserRole($id); /* get the current user's role */
        if (!empty($get_userrole)) {
            $current_user_role = $get_userrole[0]['meta_value'];
        }
		
        $user_social_meta = $this->check_email_view_page($model->email);
		/*echo "<pre>";
		print_r($user_social_meta);die;*/
        return $this->render('view', [ 'model' => $model, 'user_social_meta' => $user_social_meta, 'current_user_role' => $current_user_role]);
    }

    /**
     * Creates a new DvUsers model.     
     * @return mixed
     */
    public function actionCreate(){ 
        // function to create User.
        //redirect a user if not super admin

        $site_url = Yii::$app->params['yii_url'];
        $upload_url = "";
        if($site_url == "http://dev.digitalvidya.com/assist") {
            $upload_url - $site_url."/uploads/user_image/";
        } else {
            $upload_url = $site_url . "/uploads/";
        }
        if (!Yii::$app->CustomComponents->check_permission('create_user')) {
            return $this->redirect(['site/index']);
        }

        $model = new DvUsers();
        if (!empty($model->course)) {
            $model->course = explode(',', $model->course);
        }


        if ($model->load(Yii::$app->request->post())) {
            if (!empty($model->course)) {
                $model->course = implode(",", $_POST['DvUsers']['course']);
            }

            if (isset($_POST['usermeta']['day_avail'])) {
                $day_avail = $_POST['usermeta']['day_avail'];
            }

            if (!empty($day_avail)) {
                $day_avail = implode(",", $_POST['usermeta']['day_avail']);
            }

            $userdata = Yii::$app->request->post();
			//echo "<pre>";
			//print_r($userdata); die;
			if($userdata['usermeta']['role'] == 4 || $userdata['usermeta']['role'] == 5){
				$email =  $userdata['DvUsers']['email'];
				$phone =  $userdata['usermeta']['phone'];
				$fname = $userdata['DvUsers']['first_name'];
				$lname = $userdata['DvUsers']['last_name'];
				$fb_link = $userdata['usermeta']['fb_link'];
				$linkedin_link =  $userdata['usermeta']['linkedin_link'];
				$twitter_link = $userdata['usermeta']['twitter_link'];
				
				if(isset($userdata['usermeta']['description'])){
					$desc = $userdata['usermeta']['description'];
				}else{
					$desc = "";
				}

			
			   
			}
			
            $model->save();

            $uid = Yii::$app->db->getLastInsertID();
			if($userdata['usermeta']['role'] == 4){
				$usre_role = 1;
				$profile_visibility = $userdata['usermeta']['profile_visibility'];
			}else{
				$usre_role = 2;
				$profile_visibility = 1;
			}

            if($userdata['usermeta']['role'] == 4 || $userdata['usermeta']['role'] == 5){
                // ***************** Start of curl ************************
               
                $curl = curl_init();
                // Set some options - we are passing in a useragent too here
                curl_setopt_array($curl, [
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_URL => 'http://dev.digitalvidya.com/training/wp-json/check_ta_email/v1/ld/',
                    CURLOPT_USERAGENT => 'Get course data',
                    CURLOPT_POST => 1,
                    CURLOPT_POSTFIELDS => [
                        
                        'ta_email' => $email,
                        'tm_fname' => $fname,
                        'tm_lname' => $lname,
                        'tm_phone' => $phone,
                        'tm_facebook' => $fb_link,
                        'tm_linkedin' => $linkedin_link,
                        'tm_twitter' => $twitter_link,
                        'tm_description' => $desc,
                        'tm_image_url' => $upload_url.'img_'.$uid.'.jpg',
                        'tm_image_name' => 'img_'.$uid.'.jpg',
                        'profile_visibility' => $profile_visibility,
						'user_role' => $usre_role

                    ]
                ]);
                // Send the request & save response to $resp
                $resp = curl_exec($curl);
                // Close request to clear up some resources
                $resulst = json_decode($resp,true);
                curl_close($curl);
				//echo "<pre>";print_r($resulst);die;
                // ************* End of the curl ************************
            }

            $model->picture = UploadedFile::getInstance($model, 'picture');
            if (!empty($model->picture->baseName)) {
                $model->picture->saveAs('uploads/user_image/img_' . $uid . '.' . $model->picture->extension);
                $user_image = 'img_' . $uid . '.' . $model->picture->extension;
                Yii::$app->db->createCommand("UPDATE assist_users SET picture = '$user_image' WHERE id = '$uid' AND status = 1 ")->execute();
            }

            $usermeta = $_POST['usermeta'];
            unset($usermeta['day_avail']);
			if(isset($resulst['user_id'])){
				$usermeta['wp_user_id'] = $resulst['user_id'];
				
			}
			if(isset($resulst['post_id'])){
				
				$usermeta['wp_post_id'] = $resulst['post_id'];
				$usermeta['trainer_profile_url'] = $_SERVER['SERVER_NAME']."/?p=".$resulst['post_id'];
			}


            foreach ($usermeta as $key => $val) {
                Yii::$app->db->createCommand()->insert('assist_user_meta', [ 'uid' => $uid, 'meta_key' => $key, 'meta_value' => $val])->execute();
            }

            if (!empty($day_avail)) {
                Yii::$app->db->createCommand()->insert('assist_user_meta', [ 'uid' => $uid, 'meta_key' => 'day_avail', 'meta_value' => $day_avail])->execute();
            }

            $user_password = $_POST['DvUsers']['password'];           

            // send email
            if ($usermeta['notify'] == 1) {
              $subject = Yii::$app->params['site_name']." New Account Invitation";
              $body = " <h3>Welcome to ". Yii::$app->params['site_name']."</h3>
              <p>Hi $model->first_name,</p>
              <p>Your login details are:</p>
              <p>Site URL: ". Yii::$app->params['yii_url']."</p>
              <p>Username: $model->username</p>
              <p>Password: $user_password</p>
              <br>
              <br>
              <p>Thanks and Regards</p>
              <p>Digital Vidya Team</p>
              ";

              $is_sent = Yii::$app->mailer->compose()
              ->setFrom('it@digitalvidya.com')
              //->setTo('kl@logixbuilt.com')
              ->setTo($model->email)
              ->setBcc ('dharmendra@whizlabs.com')
              ->setSubject($subject)
              ->setHtmlBody($body)
              ->send();
             
            }

            return $this->redirect(['view', 'id' => $uid]);
        } else {
            return $this->render('create', [
                        'model' => $model,
            ]);
        }
    }

    //ajax call to list states based on Countires 
    public function actionGet_states(){
        if (isset($_POST['country_id']) != 0) {
            $output = '';
            $country_id = $_POST['country_id'];
            $att_name = $_POST['att_name'];
            $all_states = DvStates::find()->where(['country_id' => $country_id])->all();
            $allstates = ArrayHelper::map($all_states, 'id', 'name');
            if ($att_name == 'DvRegistration[country]') {
                $output .= '<select id="dvusers-state" class="form-control" name="DvRegistration[state]" required="required" aria-invalid="false" data-toggle="tooltip" data-placement="top" title="" data-original-title="Select State">';
            } else {
                $output .= '<select id="dvusers-state" class="form-control" name="usermeta[state]" required="required" aria-invalid="false" data-toggle="tooltip" data-placement="top" title="" data-original-title="Select State">';
            }
            $output .= '<option value="">Select State</option>';
            foreach ($allstates as $id => $name) {
                $output .= '<option value="' . $id . '">' . $name . '</option>';
            }
            $output .= '</select>';
            return $output;
        } else {
            return $this->redirect(['dv-users/index']);
        }
    }

    //ajax call to list states based on State 
    public function actionGet_cities() {
        if (isset($_POST['state_id']) != 0) {
            $output = '';
            $state_id = $_POST['state_id'];
            $att_name = $_POST['att_name'];
            $all_cities = DvCities::find()->where(['state_id' => $state_id])->all();
            $allcities = ArrayHelper::map($all_cities, 'id', 'name');
            if ($att_name == 'DvRegistration[state]') {
                $output .= '<select id="dvusers-city" class="form-control" name="DvRegistration[city]" aria-invalid="false" data-toggle="tooltip" data-placement="top" title="" data-original-title="Select City">';
            } else {
                $output .= '<select id="dvusers-city" class="form-control" name="usermeta[city]" required="required" aria-invalid="false" data-toggle="tooltip" data-placement="top" title="" data-original-title="Select City">';
            }
            $output .= '<option value="">Select City</option>';
            foreach ($allcities as $id => $name) {
                $output .= '<option value="' . $id . '">' . $name . '</option>';
            }
            $output .= '</select>';
            return $output;
        } else {
            return $this->redirect(['dv-users/index']);
        }
    }

    //Ajax Call to List Course Coordinator 
    public function actionCourse_coordinator() {
        if (isset($_POST['cid']) != 0) {
            $output = '';
            $cid = $_POST['cid'];
            $output .= '<select id="dvusers-coordinator" class="form-control" name="usermeta[coordinator]" data-toggle="tooltip" data-placement="top" title="" data-original-title="Select Course Coordinator">';
            $output .= '<option value="">Select Course Coordinator</option>';
            /*
                $dv_users = Yii::$app->db->createCommand("SELECT * FROM assist_users WHERE department = 2 AND status = 1")->queryAll();
            */
            $dv_users = Yii::$app->db->createCommand("SELECT assist_users.* FROM assist_users LEFT JOIN assist_user_meta ON assist_users.id=assist_user_meta.uid WHERE assist_users.department = 2 AND assist_users.status = 1 and assist_user_meta.meta_key='role' and assist_user_meta.meta_value = 5")->queryAll();
             
            foreach ($dv_users as $dvusers) {
                $dvuser_course = explode(",", $dvusers['course']);
                $result = array_intersect($dvuser_course, $cid);
                if ($result) {
                    $output .= '<option value="' . $dvusers['id'] . '">' . $dvusers['first_name'] . ' ' . $dvusers['last_name'] . '</option>';
                }
            }


            // if there is no value then get master course name
            $output1 = '';
            $user_in_arr = array();
            foreach ($cid as $value) {
                //$master_course = Yii::$app->db->createCommand("SELECT mcourse FROM assist_course WHERE id = '$value' ")->queryAll();
                //added 20 April 2019 By KK / PP
                $master_course = Yii::$app->db->createCommand("SELECT mcourse FROM assist_module WHERE id = '$value' ")->queryAll();

                foreach ($dv_users as $dvusers) {
                    $dvuser_course = explode(",", $dvusers['course']);
                    foreach ($master_course as $mcourse_val) {
                        $result = array_intersect($dvuser_course, $mcourse_val);

                        if ($result) {
                            if (!in_array($dvusers['id'], $user_in_arr)) {
                                $user_in_arr[] = $dvusers['id'];
                                $output1 .= '<option value="' . $dvusers['id'] . '">' . $dvusers['first_name'] . ' ' . $dvusers['last_name'] . '</option>';
                            }
                        }
                    }
                }
            }
            
            $output .= $output1;
            $output .= '</select>';
            return $output;
        } else {
            return $this->redirect(['dv-users/index']);
        }
    }

    //ajax call to list Course based on Department
    public function actionGet_course() {
        if (isset($_POST['dep_id']) != 0) {
            $output = '';
            $role_id = $_POST['dep_id'];

            if (($role_id == '1') || ($role_id == '2') || ($role_id == '7')) {
                $output .= '<div id="usercourse" class="">';
                $output .= '<div class="form-group col-md-4 field-dvusers-course">';
                $output .= '<input name="DvUsers[course]" value="" type="hidden">';
                $output .= '<select id="user_course" class="form-control" name="DvUsers[course][]" multiple="multiple" size="4" required="required" data-toggle="tooltip" data-placement="top" title="" data-original-title="Select module">';
                if (($role_id == '1')||($role_id == '2')) {
                    $output .= '<option value="dm">DM</option>';
                    $output .= '<option value="da">DA</option>';
                } else {
                    //added 20 April 2019 By KK / PP
                    $course = DvModuleModel::find()->where(['status' => 1])->all();
                    $Dv_course = ArrayHelper::map($course, 'id', 'module_name');
                    //unset($Dv_course[1]);
                    //unset($Dv_course[2]);
                    foreach ($Dv_course as $id => $module_name) {
                        $output .= '<option value="' . $id . '">' . $module_name . '</option>';
                    }
                }

                $output .= '</select>';
                $output .= '</div>';

                if ($role_id == '7') {
                    $output .='<div class="form-group col-md-4 field-dvusers-day_avail">';
                    $output .= '<input name="DvUsers[day_avail]" value="" type="hidden">';
                    $output .= '<select id="dvusers-day_avail" class="form-control" name="usermeta[day_avail][]" multiple="multiple" size="4" required="required" data-toggle="tooltip" data-placement="top" title="" data-original-title="Select Day Available">';
                    $output .= '<option value="sun">Sunday</option>';
                    $output .= '<option value="mon">Monday</option>';
                    $output .= '<option value="tue">Tuesday</option>';
                    $output .= '<option value="wed">Wednesday</option>';
                    $output .= '<option value="thur">Thursday</option>';
                    $output .= '<option value="fri">Friday</option>';
                    $output .= '<option value="sat">Saturday</option></select>';
                    $output .= '</div>';
                    $output .= '<div class="form-group col-md-4 field-dvusers-coordinator">';
                    $output .= '<select id="dvusers-coordinator" class="form-control" name="DvUsers[coordinator]" required="required" data-toggle="tooltip" data-placement="top" title="" data-original-title="Select Course Coordinator">';
                    $output .= '<option value="">Select Course Coordinator</option></select>';
                    $output .= '</div>';
                }

                if ($role_id == '7') {
                    $output .= '<div class="form-group col-md-4"><input class="form-control" name="usermeta[trainerid]" placeholder="TrainerID" data-toggle="tooltip" data-placement="top" title="TrainerID" readonly="readonly"></div>';

                    $output .= '<div class="form-group col-md-4"><input class="form-control" name="usermeta[compensation]" placeholder="Compensation" data-toggle="tooltip" data-placement="top" title="Compensation" type="number" pattern="\d*"></div>';

                    $output .= '<div class="form-group col-md-4"><select class="form-control" name="usermeta[module_led]" placeholder="Module to be Led" data-toggle="tooltip" data-placement="top" title="Module to be Led" multiple><option value="">Select any one</option><option value="1">Core</option><option value="2">Special</option></select></div>';

                    $output .= '<div class="form-group col-md-4"><select class="form-control" name="usermeta[profile_visibility]"  data-toggle="tooltip" data-placement="top" title="Profile Visibility" ><option value="">Select Profile Visibility</option><option value="1">Publish</option><option value="2">Draft</option></select></div>';
                
                    $output .= '<div class="form-group col-md-12"><textarea id="usermeta[description]" name="usermeta[description]" style="display:none;">Brief Description</textarea></div>';
                }

                $output .= '</div>';
            } else {
                $output .= '<div id="usercourse" class="hide">';
                $output .= '<input id="dvusers-course" class="form-control" name="DvUsers[course]" value="" type="hidden">';
                $output .= '<input id="dvusers-day_avail" class="form-control" name="DvUsers[day_avail]" value="" type="hidden">';
                $output .= '<input id="dvusers-coordinator" class="form-control" name="DvUsers[coordinator]" value="" type="hidden"></div>';
            }

            return $output;
        } else {
            return $this->redirect(['dv-users/index']);
        }
    }


    //ajax call to list Course based on Department
    public function actionGet_course_trainer() {
        if (isset($_POST['dep_id']) != 0) {
            $output = '';
            $role_id = $_POST['dep_id'];

            if (($role_id == '1') || ($role_id == '2') || ($role_id == '7')) {
                $output .= '<div id="usercourse" class="">';
                $output .= '<div class="form-group col-md-4 field-dvusers-course">';
                $output .= '<input name="DvUsers[course]" value="" type="hidden">';
                $output .= '<select id="user_course" class="form-control" name="DvUsers[course][]" multiple="multiple" size="4" required="required" data-toggle="tooltip" data-placement="top" title="" data-original-title="Select Course">';
                if (($role_id == '1')||($role_id == '2')) {
                    $output .= '<option value="dm">DM</option>';
                    $output .= '<option value="da">DA</option>';
                } else {
                    $course = DvCourse::find()->where(['status' => 1])->all();
                    $Dv_course = ArrayHelper::map($course, 'id', 'name');
                    unset($Dv_course[1]);
                    unset($Dv_course[2]);
                    foreach ($Dv_course as $id => $name) {
                        $output .= '<option value="' . $id . '">' . $name . '</option>';
                    }
                }

                $output .= '</select>';
                $output .= '</div>';

                if ($role_id == '7') {
                    $output .='<div class="form-group col-md-4 field-dvusers-day_avail">';
                    $output .= '<input name="DvUsers[day_avail]" value="" type="hidden">';
                    $output .= '<select id="dvusers-day_avail" class="form-control" name="usermeta[day_avail][]" multiple="multiple" size="4" required="required" data-toggle="tooltip" data-placement="top" title="" data-original-title="Select Day Available">';
                    $output .= '<option value="sun">Sunday</option>';
                    $output .= '<option value="mon">Monday</option>';
                    $output .= '<option value="tue">Tuesday</option>';
                    $output .= '<option value="wed">Wednesday</option>';
                    $output .= '<option value="thur">Thursday</option>';
                    $output .= '<option value="fri">Friday</option>';
                    $output .= '<option value="sat">Saturday</option></select>';
                    $output .= '</div>';
                    $output .= '<div class="form-group col-md-4 field-dvusers-coordinator">';
                    $output .= '<select id="dvusers-coordinator" class="form-control" name="DvUsers[coordinator]" required="required" data-toggle="tooltip" data-placement="top" title="" data-original-title="Select Course Coordinator">';
                    $output .= '<option value="">Select Course Coordinator</option></select>';
                    $output .= '</div>';
                }

                $output .= '<div class="form-group col-md-4"><input class="form-control" name="usermeta[trainerid]" placeholder="TrainerID" data-toggle="tooltip" data-placement="top" title="TrainerID"></div>';

                $output .= '<div class="form-group col-md-4"><input class="form-control" name="usermeta[compensation]" placeholder="Compensation" data-toggle="tooltip" data-placement="top" title="Compensation"></div>';

                $output .= '<div class="form-group col-md-4"><select class="form-control" name="usermeta[module_led]" placeholder="Module to be Led" data-toggle="tooltip" data-placement="top" title="Module to be Led"><option value="">Select any one</option><option value="1">Core</option><option value="2">Special</option></select></div>';

                $output .= '<div class="form-group col-md-12"><textarea id="usermeta[description]" name="usermeta[description]">Brief Description</textarea></div>';

                $output .= '</div>';
            } else {
                $output .= '<div id="usercourse" class="hide">';
                $output .= '<input id="dvusers-course" class="form-control" name="DvUsers[course]" value="" type="hidden">';
                $output .= '<input id="dvusers-day_avail" class="form-control" name="DvUsers[day_avail]" value="" type="hidden">';
                $output .= '<input id="dvusers-coordinator" class="form-control" name="DvUsers[coordinator]" value="" type="hidden"></div>';
            }

            return $output;
        } else {
            return $this->redirect(['dv-users/index']);
        }
    }


    //Ajax call to list User Team based on Department 
    public function actionUser_team() {
        if (isset($_POST['dep_id']) != 0) {
            $output = '';
            $dep_id = $_POST['dep_id'];

            $UserTeam = DvUsersTeam::find()->where(['status' => 1, 'dep_id' => $dep_id])->all();
            $UTeam = ArrayHelper::map($UserTeam, 'id', 'name');

            if (!empty($UTeam)) {
                $output .= '<select id="dvusers-team" class="form-control" name="usermeta[team]" required="required" aria-invalid="false" data-toggle="tooltip" data-placement="top" title="" data-original-title="Select Team">';
                $output .= '<option value="">Select Team</option>';
                foreach ($UTeam as $id => $name) {
                    $output .= '<option value="' . $id . '">' . $name . '</option>';
                }

                $output .= '</select>';
            } else {
                $output .= '<select id="dvusers-team" class="form-control" name="usermeta[team]" aria-invalid="false" data-toggle="tooltip" data-placement="top" title="" data-original-title="Select Team">';
                $output .= '<option value="">Select Team</option>';
                $output .= '</select>';
            }

            return $output;
        } else {
            return $this->redirect(['dv-users/index']);
        }
    }

    //ajax call to list User Role based on Department 
    public function actionUser_role() {
        if (isset($_POST['dep_id']) != 0) {
            $output = '';
            $dep_id = $_POST['dep_id'];
            $UserRole = DvUsersRole::find()->where(['status' => 1])->all();
            $Urole = ArrayHelper::map($UserRole, 'id', 'name');
            echo '<select id="UsersRole" class="form-control" name="usermeta[role]" required="required" aria-invalid="false" data-toggle="tooltip" data-placement="top" title="Select Role">';
            echo '<option value="">Select Role</option>';
            foreach ($Urole as $key => $val) {
                if($key != 1){
                    //Removed "Super Admin" 03 June 2019
                    if (($dep_id == 7) && ($key == 4)) {
                        echo '<option value="' . $key . '" selected="selected" >' . $val . '</option>';
                    } else {
                        echo '<option value="' . $key . '">' . $val . '</option>';
                    }
                }
            }
            echo '</select>';
            return $output;
        } else {
            return $this->redirect(['dv-users/index']);
        }
    }

    //ajax call to check email address exists or not
    public function actionCheck_email() { //var_dump($_POST['email']); die();
        if (isset($_POST['email']) != 0) {
            $output = array();
            $email = $_POST['email'];
            if (preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^", $email)) {

                $modules = Yii::$app->db->createCommand("SELECT * FROM assist_users WHERE email='$email'")->queryAll();
                if (!empty($modules)) {
                    $output['status'] = '1';
                } else {
                    /*$post = [ 'ta_email' => $email];
                    $ch = curl_init('http://dev.digitalvidya.com/training/wp-json/check_ta_email/v1/ld/');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                    // execute!
                    $response = curl_exec($ch);

                    // close the connection, release resources used
                    curl_close($ch);*/

                    $post = [ 'ta_email' => $_POST['email']];                    
                    $ch = curl_init('http://dev.digitalvidya.com/training/wp-json/check_ta_email/v1/ld/');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                    // execute!
                    $response = curl_exec($ch);

                    // close the connection, release resources used
                    curl_close($ch);


					$resulst = json_decode($response); 
					//print_r($ourput);
					//print_r(json_decode($ourput)); 
					//echo "<pre>";
					//print_r($resulst); //var_dump($email); die();
					if(!empty($resulst)){
						$output['status'] = '0';
						$output['result'] = $resulst;
					}else{
						echo "test";
						die;
					}
                   // curl_close($ch);
                }
            }

            return json_encode($output);
        } else {
            return $this->redirect(['dv-users/index']);
        }
    }

    //function call to check email address exists or not
    public function check_email_view_page($email) {
       
        if (!empty($email)) {
            $output = array();
            if (preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^", $email)) {

                $modules = Yii::$app->db->createCommand("SELECT * FROM assist_users WHERE email='$email'")->queryAll();
                
                    // Get cURL resource
                    $curl = curl_init();
                    // Set some options - we are passing in a useragent too here
                    curl_setopt_array($curl, [
                        CURLOPT_RETURNTRANSFER => 1,
                        CURLOPT_URL => 'http://dev.digitalvidya.com/training/wp-json/check_ta_email/v1/ld/',
                        CURLOPT_USERAGENT => 'Get course data',
                        CURLOPT_POST => 1,
                        CURLOPT_POSTFIELDS => [
                            'ta_email' => $email,
                        ]
                    ]);
                    // Send the request & save response to $resp
                    $resp = curl_exec($curl);
                    // Close request to clear up some resources
                    $resulst = json_decode($resp,true);
                    if(!empty($resulst)){
                        $output['status'] = '0';
                        $output['result'] = $resulst;
                    }else{
                        $output['status'] = '-1';
                       
                    }
                    curl_close($curl);
                
            }

            return json_encode($output);
        } else {
            return json_encode(array());
        }
    }

    //ajax call to Get Team name
    public function actionTeam_name() {
        if (isset($_POST['search']) != 0) {
            $output = '';
            $teamname = $_POST['search'];
            $department = $_POST['department'];
            $managers = $_POST['managers'];
            $response = array();
            $modules = Yii::$app->db->createCommand("SELECT distinct assist_users.id, assist_users.email FROM assist_users JOIN assist_user_meta ON assist_user_meta.uid = assist_users.id WHERE email LIKE '%$teamname%' AND department = '$department'  AND assist_user_meta.meta_value = '' AND assist_user_meta.meta_key = 'team' AND assist_users.status = 1 ")->queryAll();
            foreach ($modules as $key => $val) {
                $get_userrole = $this->getUserRole($val['id']); /* get the current user's role */

                $userrole = $this->checkUserRole($val['id']); // check the current user is not manager
                if ($userrole != 1) {

                    $userteam = $this->checkUserTeam($val['id'], $managers); // exclude existing users
                    if ($userteam != 1) {
                        if (!empty($get_userrole)) {
                            if ($get_userrole[0]['meta_value'] != 7) {
                                $response[] = array("value" => $val['email']);
                            }
                        } else {
                            $response[] = array("value" => $val['email']);
                        }
                    }
                }
            }
            $output = json_encode($response);
            return $output;
        } else {
            return $this->redirect(['dv-users/index']);
        }
    }

    private function checkUserTeam($id, $mid) { // exclude existing users
        $dv_users = Yii::$app->db->createCommand("SELECT id FROM assist_user_meta WHERE uid = '$id' AND meta_key = 'team' AND meta_value = '$mid' ")->queryAll();

        if (empty($dv_users)) {
            $output = 0;
        } else {
            $output = 1;
        }
        return $output;
    }

    // ajax call to list users by Department
    public function actionUsers_by_dep() {
        if (isset($_POST['depid']) != 0) {
            $output = '';
            $depid = $_POST['depid'];
            $output = '';
            $output .= '<select id="managers" class="form-control" name="team_manager" required="required">';

            $dv_users = Yii::$app->db->createCommand("SELECT id, first_name, last_name FROM assist_users WHERE department = '$depid' AND status = 1 ")->queryAll();

            foreach ($dv_users as $dvusers){                
                $userrole = $this->checkUserRole($dvusers['id']);                
                if ($userrole == 1) {
                    $output .= '<option value="' . $dvusers['id'] . '">' . $dvusers['first_name'] . ' ' . $dvusers['last_name'] . '</option>';
                }
            }
            $output .= '</select>';
            return $output;
        }
    }

    // ajax call to list users by Department
    public function actionUsers_by_dep2() {
        if (isset($_POST['depid']) != 0) {
            $output = '';
            $depid = $_POST['depid'];
            $output = '';
            $output .= '<select id="dvusers-team" class="form-control" name="usermeta[team]" aria-invalid="false" data-toggle="tooltip" data-placement="top" title="" data-original-title="Select Manager">';
            $output .= '<option value=""> ---- </option>';

            $dv_users = Yii::$app->db->createCommand("SELECT id, first_name, last_name FROM assist_users WHERE department = '$depid' AND status = 1 ")->queryAll();

            foreach ($dv_users as $dvusers){                
                $userrole = $this->checkUserRole($dvusers['id']);                
                if ($userrole == 1) {
                    $output .= '<option value="' . $dvusers['id'] . '">' . $dvusers['first_name'] . ' ' . $dvusers['last_name'] . '</option>';
                }
            }
            $output .= '</select>';
            $output .= '<style>.dvuserteam{display:block;}</style>';
            return $output;
        }
    }

    // ajax call to update Manager Team or Replace the Manager
    public function actionUpdate_manager() {
        if (isset($_POST['mid']) != 0) {
            $output = '';
            $mid = $_POST['mid'];
            $output = '';
            $output .= '<select id="new_manager" class="form-control" name="new_manager" required="required">';
            $output .= '<option value="">Select New Manager</option>';
            $output .= '<option value="">Only Remove the Team</option>';
            $user_department = Yii::$app->db->createCommand("SELECT department FROM assist_users WHERE id = '$mid' AND status = 1 ")->queryAll();
            $user_department_id = $user_department[0]['department'];

            $dv_users = Yii::$app->db->createCommand("SELECT id, first_name, last_name FROM assist_users WHERE department = '$user_department_id' AND status = 1 ")->queryAll();

            foreach ($dv_users as $dvusers) {
                $userrole = $this->checkUserRole($dvusers['id']);
                if ($userrole == 1) {
                    if ($mid != $dvusers['id']) {
                        $output .= '<option value="' . $dvusers['id'] . '">' . $dvusers['first_name'] . ' ' . $dvusers['last_name'] . '</option>';
                    }
                }
            }
            $output .= '</select>';
            return $output;
        }
    }

    // Function to check the User Role
    private function checkUserRole($id) {
        $dv_users = Yii::$app->db->createCommand("SELECT id FROM assist_user_meta WHERE uid = '$id' AND meta_key = 'role' AND meta_value = '6' ")->queryAll();
        if (empty($dv_users)){
            $output = 0;
        } else {
            $output = 1;
        }
        return $output;
    }

    /**
     * Get any user's role.     
     //Function to get User Role
     */
    private function getUserRole($id) { 
        $dv_role = Yii::$app->db->createCommand("SELECT meta_value FROM assist_user_meta WHERE uid = '$id' AND meta_key = 'role'")->queryAll();
        return $dv_role;
    }

    /**
     // function to create Role
     */
    public function actionCreate_role() { 
        //redirect a user if not super admin
        if (!Yii::$app->CustomComponents->check_permission('user_role')) {
            return $this->redirect(['site/index']);
        }
        $model = new DvUsersRole();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'New User Role Created Successfully');
            return $this->redirect(['dv-users/create_role']);
        } else {
            return $this->render('create_role', [ 'model' => $model]);
        }
    }

    /**    
     * function to assign team
     */
    public function actionAssign_team() { // update by anoop 21-11-18
        //redirect a user if not super admin
        if (!Yii::$app->CustomComponents->check_permission('assign_team')) {
            return $this->redirect(['site/index']);
        }
        $model = new DvUsersRole();        
        if (Yii::$app->request->post()) {
            $dep_id = $_POST['DvUsersTeam']['dep_id'];
            $team_manager = $_POST['team_manager'];
            $useremail = $_POST['DvUsersTeam']['user_email'];
            $user_email = trim($useremail);
            // get user id
            $userid = Yii::$app->db->createCommand("SELECT assist_users.id as id FROM assist_users 
              JOIN assist_user_meta on assist_user_meta.uid = assist_users.id WHERE email = '$user_email' AND department = '$dep_id' 
                  AND assist_user_meta.meta_key= 'role' AND assist_user_meta.meta_value <> 7 AND assist_users.status = 1 ")->queryAll();
            if (!empty($userid)) {
                $user_id = $userid[0]['id'];
                // get manager email
                $manager_email = Yii::$app->db->createCommand("SELECT email FROM assist_users WHERE id = '$team_manager' AND status = 1 ")->queryAll();
                $manageremail = $manager_email[0]['email']; //var_dump($user_id); die();

                $result = Yii::$app->db->createCommand("UPDATE assist_user_meta SET meta_value = '$team_manager' WHERE meta_key = 'team' AND uid = '$user_id' AND meta_value = ''")->execute();
            }


            if (isset($result) && $result == 1) {
                Yii::$app->session->setFlash('success', 'User with Email Address <u>' . $useremail . '</u> Successfully Assigned to Manger with Email Address <u>' . $manageremail . '</u>');
            } else {
                Yii::$app->session->setFlash('danger', 'User with Email Address <u>' . $useremail . '</u> is already assiged to other Manger.</u>');
            }
            return $this->redirect(['dv-users/assign_team']);
        } else {
            return $this->render('assign_team', [ 'model' => $model]);
        }
    }

    // function to Update Team
    public function actionUpdate_team(){  // anoop 21-11-18
        //redirect a user if not super admin
        if (!Yii::$app->CustomComponents->check_permission('assign_team')) {
            return $this->redirect(['site/index']);
        }        
        $model = new DvUsersRole();        
        if (Yii::$app->request->post()){
            $user_of_manager = array();
            if (isset($_POST['user_of_manager'])) {
                $user_of_manager = $_POST['user_of_manager'];
            }
            $manager_id = $_POST['manager'];
            $new_manager_id = $_POST['new_manager'];
            // get manager email
            $manager_email = Yii::$app->db->createCommand("SELECT email FROM assist_users WHERE id = '$manager_id' AND status = 1 ")->queryAll();
            $manageremail = $manager_email[0]['email'];

            if (empty($new_manager_id) || $new_manager_id == 0) {
                Yii::$app->db->createCommand("UPDATE assist_user_meta SET meta_value = '' WHERE meta_key = 'team' AND meta_value = '$manager_id' ")->execute();
                Yii::$app->session->setFlash('success', 'The Team for Manager with Email Address <u>' . $manageremail . '</u> is Successfully Removed.');
            } else {
                // get new manager email
                $Nmanager_email = Yii::$app->db->createCommand("SELECT email FROM assist_users WHERE id = '$new_manager_id' AND status = 1 ")->queryAll();
                $Nmanageremail = $Nmanager_email[0]['email'];
                if (!empty($user_of_manager)) {
                    $user_of_manager_str = implode(",", $user_of_manager);
                    Yii::$app->db->createCommand("UPDATE assist_user_meta SET meta_value = '$new_manager_id' WHERE meta_key = 'team' AND meta_value = '$manager_id' AND uid IN($user_of_manager_str)")->execute();
                    Yii::$app->session->setFlash('success', 'The selected Managers are Successfully assigned to Manger with Email Address <u>' . $Nmanageremail . '</u>');
                } else {

                    Yii::$app->db->createCommand("UPDATE assist_user_meta SET meta_value = '$new_manager_id' WHERE meta_key = 'team' AND meta_value = '$manager_id' ")->execute();
                    Yii::$app->session->setFlash('success', 'The Team for Manager with Email Address <u>' . $manageremail . '</u> is Successfully assigned to Manger with Email Address <u>' . $Nmanageremail . '</u>');
                }
            }
            return $this->redirect(['dv-users/assign_team']);
        } else {

            return $this->render('assign_team', [ 'model' => $model]);
        }
    }

    // function to Remove User
    public function actionRemove_user($id) {   // by anoop 15-11-18
        //redirect a user if not super admin
        if (!Yii::$app->CustomComponents->check_permission('assign_team')) {
            return $this->redirect(['site/index']);
        }

        if (!empty($id)) {
            // get User email
            $user_email = Yii::$app->db->createCommand("SELECT email FROM assist_users WHERE id = '$id' AND status = 1 ")->queryAll();
            $useremail = $user_email[0]['email'];

            Yii::$app->db->createCommand("UPDATE assist_user_meta SET meta_value = '' WHERE meta_key = 'team' AND uid = '$id' ")->execute();

            Yii::$app->session->setFlash('success', 'User with Email Address <u>' . $useremail . '</u> Successfully Removed.');
            return $this->redirect(['dv-users/assign_team']);
            //return $this->redirect(['view', 'id' => $user_id]);
        }
    }

    /**
     * Update user team.     
     */
    public function actionUpdate_user_team() {
        /* redirect a user if not super admin */
        if (!Yii::$app->CustomComponents->check_permission('assign_team')) {
            return $this->redirect(['site/index']);
        }

        if (Yii::$app->request->post()) {
            $post_data = Yii::$app->request->post();
            $user_id = $post_data['user'];
            $useremail = $post_data['change_user_email'];
            if (!empty($post_data['new_manager'])) {
                $new_manager_id = $post_data['new_manager'];
                $manager_email = Yii::$app->db->createCommand("SELECT email FROM assist_users WHERE id = '$new_manager_id' AND status = 1 ")->queryAll();
                $manageremail = $manager_email[0]['email'];
                Yii::$app->db->createCommand("UPDATE assist_user_meta SET meta_value = '$new_manager_id' WHERE meta_key = 'team' AND uid = '$user_id' ")->execute();
                Yii::$app->session->setFlash('success', 'User with Email Address <u>' . $useremail . '</u> Successfully assigned manager to Email Address <u>' . $manageremail . '</u>.');
            } else {

                Yii::$app->db->createCommand("UPDATE assist_user_meta SET meta_value = '' WHERE meta_key = 'team' AND uid = '$user_id' ")->execute();
                Yii::$app->session->setFlash('success', 'User with Email Address <u>' . $useremail . '</u> Successfully Removed.');
            }
        }
        return $this->redirect(['dv-users/assign_team']);
    }

    /**     
     * function to Create Department
     */
    public function actionCreate_department() {
        //redirect a user if not super admin
        if (!Yii::$app->CustomComponents->check_permission('department')) {
            return $this->redirect(['site/index']);
        }

        $model = new DvUsersDepartment();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'New Department Created Successfully');
            return $this->redirect(['dv-users/create_department']);
        } else {

            return $this->render('create_department', [ 'model' => $model]);
        }
    }

    /**
     * Updates an existing User
     */
    public function actionUpdate($id) {
        //redirect a user if not super admin
        $site_url = Yii::$app->params['yii_url'];
        $upload_url = "";
        if($site_url == "http://dev.digitalvidya.com/assist") {
            $upload_url - $site_url."/uploads/user_image/";
        } else {
            $upload_url = $site_url . "/uploads/";
        }
        if (!Yii::$app->CustomComponents->check_permission('edit_user')){            
            return $this->redirect(['index']);
        }
        $model = DvUsers::find()->where(["id"=>$id])->one();
        if (!empty($model->course)) {
            $model->course = explode(',', $model->course);
        }

        if ($model->load(Yii::$app->request->post())) {
            if (!empty($model->course)) {
                unset($model->course);
                $model->course = implode(",", $_POST['DvUsers']['course']);
            }

            if (isset($_POST['usermeta']['day_avail'])) {
                $day_avail = $_POST['usermeta']['day_avail'];
            }

            if (!empty($day_avail)) {
                $day_avail = implode(",", $_POST['usermeta']['day_avail']);
            }

            $model->picture = UploadedFile::getInstance($model, 'picture');

            if (!empty($model->picture->baseName)) {
                $model->picture->saveAs('uploads/user_image/img_'.$id.'.'.$model->picture->extension);
                $model->picture = 'img_' . $id . '.' . $model->picture->extension;
            } else {                
                unset($model->picture);
            }

            //encript pass before save
            $password = $_POST['DvUsers']['password'];            
            if (!empty($password)) {
                $model->password = md5($model->password);                
            } else {
                unset($model->password);
            }
			//echo "<pre>";
			//print_r($model); die;
			if($_POST['usermeta']['role'] == 4 || $_POST['usermeta']['role'] == 5){
				$email =  $model->email;
				$phone =  $_POST['usermeta']['phone'];
				$fname = $_POST['DvUsers']['first_name'];
				$lname = $_POST['DvUsers']['last_name'];
				$fb_link = $_POST['usermeta']['fb_link'];
				$linkedin_link =  $_POST['usermeta']['linkedin_link'];
				$twitter_link = $_POST['usermeta']['twitter_link'];
				//$profile_visibility = $_POST['usermeta']['profile_visibility'];
				if(isset($_POST['usermeta']['description'])){
					$desc = $_POST['usermeta']['description'];
				}else{
					$desc = "";
				}
				if($_POST['usermeta']['role'] == 4){
					$usre_role = 1;
					$profile_visibility = $_POST['usermeta']['profile_visibility'];
				}else{
					$usre_role = 2;
					$profile_visibility = 1;
				}
			

			
			   // ***************** Start of curl ************************
			   
				$curl = curl_init();
				// Set some options - we are passing in a useragent too here
				curl_setopt_array($curl, [
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_URL => 'http://dev.digitalvidya.com/training/wp-json/check_ta_email/v1/ld/',
					CURLOPT_USERAGENT => 'Get course data',
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => [
						
						'ta_email' => $email,
						'tm_fname' => $fname,
						'tm_lname' => $lname,
						'tm_phone' => $phone,
						'tm_facebook' => $fb_link,
						'tm_linkedin' => $linkedin_link,
						'tm_twitter' => $twitter_link,
						'tm_description' => $desc,
						'tm_image_url' => $upload_url.'img_'.$id.'.jpg',
						'tm_image_name' => 'img_'.$id.'.jpg',
                        'profile_visibility' => $profile_visibility,
						'user_role' => $usre_role

					]
				]);
				// Send the request & save response to $resp
				$resp = curl_exec($curl);
				// Close request to clear up some resources
				$resulst = json_decode($resp,true);
				curl_close($curl);
				//echo "<pre>";
				//print_r($resulst); die;
			}
			
            $usermeta = $_POST['usermeta'];
            unset($usermeta['day_avail']);

            foreach ($usermeta as $key => $val) {
                Yii::$app->db->createCommand("UPDATE assist_user_meta SET meta_value = '$val' WHERE meta_key = '$key' AND uid = '$model->id' ")->execute();
            }

            if (isset($_POST['usermeta']['role'])) {
            	$user_usermeta = $_POST['usermeta']['role'];
	            if($user_usermeta == '6'){
	                Yii::$app->db->createCommand("UPDATE assist_user_meta SET meta_value = '' WHERE meta_key = 'team' AND uid = '$model->id' ")->execute();
	            }
            }            

            if (!empty($day_avail)) {
                Yii::$app->db->createCommand("UPDATE assist_user_meta SET meta_value = '$day_avail' WHERE meta_key = 'day_avail' AND uid = '$model->id' ")->execute();
            }
            unset($model->email);
            $model->save(false);
            Yii::$app->session->setFlash('success', 'Profile Updated successfully.');
            if (Yii::$app->CustomComponents->check_permission('all_users')) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                $user_id = Yii::$app->getUser()->identity->id;
                return $this->redirect(['view', 'id' => $user_id]);
            }
        } else {
            return $this->render('update', [ 'model' => $model]);
        }
    }

    /**
     * Edit/Updates User Role
     */
    public function actionEdit_role($id) {
        //redirect a user if not super admin
        if (!Yii::$app->CustomComponents->check_permission('edit_role')) {
            return $this->redirect(['create_role']);
        }

        $model = DvUsersRole::findOne($id);
        if (empty($model)) {
            return $this->redirect(['create_role']);
        }

        if ($model->load(Yii::$app->request->post())) {

            $role = '';
            if (isset($_POST['users'])) {
                $users = $_POST['users'];
                foreach ($users as $key => $value) {
                    if (!empty($value)) {
                        $role .= $key . ' ';
                    }
                }
            }

            if (isset($_POST['registration'])) {
                $registration = $_POST['registration'];
                foreach ($registration as $key => $value) {
                    if (!empty($value)) {
                        $role .= $key . ' ';
                    }
                }
            }

            if (isset($_POST['modules'])) {
                $modules = $_POST['modules'];
                foreach ($modules as $key => $value) {
                    if (!empty($value)) {
                        $role .= $key . ' ';
                    }
                }
            }

            if (isset($_POST['reports'])) {
                $reports = $_POST['reports'];
                foreach ($reports as $key => $value) {
                    if (!empty($value)) {
                        $role .= $key . ' ';
                    }
                }
            }

            if (isset($_POST['targets'])) {
                $targets = $_POST['targets'];
                foreach ($targets as $key => $value) {
                    if (!empty($value)) {
                        $role .= $key . ' ';
                    }
                }
            }

            Yii::$app->db->createCommand("UPDATE assist_user_role SET access = '$role' WHERE id = '$id' ")->execute();

            $model->save();
            Yii::$app->session->setFlash('success', 'User Role Updated successfully');
            return $this->redirect(['create_role']);
        } else {
            return $this->render('edit_role', ['model' => $model]);
        }
    }

    // Function to Edit Team 
    public function actionEdit_team($id) {
        if (!Yii::$app->CustomComponents->check_permission('edit_team')) {
            return $this->redirect(['create_team']);
        }

        $model = DvUsersTeam::findOne($id);
        if (empty($model)) {
            return $this->redirect(['create_team']);
        }
        if ($model->load(Yii::$app->request->post())) {
            $model->save();
            Yii::$app->session->setFlash('success', 'User Team Name Updated successfully');
            return $this->redirect(['create_team']);
        } else {
            return $this->render('edit_team', ['model' => $model]);
        }
    }

    // Function to Edit Department
    public function actionEdit_department($id) {
        //redirect a user if not super admin
        if (!Yii::$app->CustomComponents->check_permission('edit_department')) {
            return $this->redirect(['create_department']);
        }

        $model = DvUsersDepartment::findOne($id);
        if (empty($model)) {
            return $this->redirect(['create_department']);
        }
        if ($model->load(Yii::$app->request->post())) {
            $model->save();
            Yii::$app->session->setFlash('success', 'User Department Updated successfully');
            return $this->redirect(['create_department']);
        } else {
            return $this->render('edit_department', ['model' => $model]);
        }
    }   

    /**
     * Find model based on its primary key value.
     */
    protected function findModel($id) {
        if (($model = DvUsers::find()->where(["id"=>$id,"status"=>1])->one()) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
 

    public function actionCreate_target2() {
        //redirect a user if not sales head
        if (!Yii::$app->CustomComponents->check_permission('targets')) {
            return $this->redirect(['site/index']);
        }

        $managers = Yii::$app->db->createCommand("SELECT * FROM assist_users as du 
            WHERE du.id IN(SELECT uid FROM assist_user_meta WHERE meta_key = 'role' AND meta_value=6) AND status = 1 ")->queryAll();

        $user_id = Yii::$app->getUser()->identity->id;
        $get_userrole = $this->getUserRole($user_id); /* get the current user's role */
        $user_role = 0;
        if (!empty($get_userrole)) {
            $user_role = $get_userrole[0]['meta_value'];
        }
    }

    public function actionCreate_target() {
        //redirect a user if not sales head
        if (!Yii::$app->CustomComponents->check_permission('targets')) {
            return $this->redirect(['site/index']);
        }

        $managers = Yii::$app->db->createCommand("SELECT * FROM assist_users as du 
            WHERE du.id IN(SELECT uid FROM assist_user_meta WHERE meta_key = 'role' AND meta_value=6) 
            AND du.department = 1 AND du.status = 1")->queryAll();

        $user_id = Yii::$app->getUser()->identity->id;
        $get_userrole = $this->getUserRole($user_id); /* get the current user's role */
        $user_role = 0;
        if (!empty($get_userrole)) {
            $user_role = $get_userrole[0]['meta_value'];
        }

        $courses = DvCourseTarget::find();
        $years = array();
        $after_6_month = array();
        $before_6_month = array();

        for ($i = 0; $i < 7; $i++) {
            $courses->orWhere(["month"=>date('m',strtotime("+$i month")),"year"=>date('Y',strtotime("+$i month"))]);
            $after_6_month[date('Y',strtotime("+$i month"))][] = date('m',strtotime("+$i month"));
            $years[] = date('Y',strtotime("+$i month"));
        }
        for ($i = 1; $i < 6; $i++) {
            $courses->orWhere(["month"=>date('m',strtotime("-$i month")),"year"=>date('Y',strtotime("-$i month"))]);
            $before_6_month[date('Y',strtotime("-$i month"))][] = date('m',strtotime("-$i month"));
            $years[] = date('Y',strtotime("-$i month"));
        }

        $years = array_unique($years);
        asort($years);
        $courses = $courses->all();
        
        $model = new DvCourseTarget();
        if ($model->load(Yii::$app->request->post())){
            echo "<pre>";
            $data = Yii::$app->request->post();
            $month = $data['DvCourseTarget']['month'];
            $year = $data['DvCourseTarget']['year'];
            $manager_id = $data['DvCourseTarget']['manager_id'];

            Yii::$app->db->createCommand("UPDATE assist_course_target SET status = 0 WHERE month = $month AND year = $year AND manager_id=$manager_id")->execute();

            $model->status = 1;
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Course Target Created Successfully');
                return $this->redirect(['dv-users/create_target']);
            }
        } else {

            return $this->render('create_target', [ 'model' => $model, 'managers' => $managers, "user_role" => $user_role, "user_id" => $user_id,"courses" => $courses, 'before_6_month' => $before_6_month, 'after_6_month' => $after_6_month, 'years' => $years]);
        }
    }

    public function actionView_target() {
        //redirect a user if not sales head
        if (!Yii::$app->CustomComponents->check_permission('view_target')) {
            return $this->redirect(['site/index']);
        }

        $managers = Yii::$app->db->createCommand("SELECT * FROM assist_users as du 
            WHERE du.id IN(SELECT uid FROM assist_user_meta WHERE meta_key = 'role' AND meta_value=6) 
            AND du.department = 1 AND du.status = 1")->queryAll();

        $user_id = Yii::$app->getUser()->identity->id;
        $get_userrole = $this->getUserRole($user_id); /* get the current user's role */
        $user_role = 0;
        if (!empty($get_userrole)) {
            $user_role = $get_userrole[0]['meta_value'];
        }

        $courses = DvCourseTarget::find();
        $years = array();
        $after_6_month = array();
        $before_6_month = array();

        for ($i = 0; $i < 7; $i++) {
            $courses->orWhere(["month"=>date('m',strtotime("+$i month")),"year"=>date('Y',strtotime("+$i month"))]);
            $after_6_month[date('Y',strtotime("+$i month"))][] = date('m',strtotime("+$i month"));
            $years[] = date('Y',strtotime("+$i month"));
        }
        for ($i = 1; $i < 6; $i++) {
            $courses->orWhere(["month"=>date('m',strtotime("-$i month")),"year"=>date('Y',strtotime("-$i month"))]);
            $before_6_month[date('Y',strtotime("-$i month"))][] = date('m',strtotime("-$i month"));
            $years[] = date('Y',strtotime("-$i month"));
        }

        $years = array_unique($years);
        asort($years);
        $courses = $courses->all();
        
        $model = new DvCourseTarget();
        if ($model->load(Yii::$app->request->post())){
            echo "<pre>";
            $data = Yii::$app->request->post();
            $month = $data['DvCourseTarget']['month'];
            $year = $data['DvCourseTarget']['year'];
            $manager_id = $data['DvCourseTarget']['manager_id'];

            Yii::$app->db->createCommand("UPDATE assist_course_target SET status = 0 WHERE month = $month AND year = $year AND manager_id=$manager_id")->execute();

            $model->status = 1;
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Course Target Created Successfully');
                return $this->redirect(['dv-users/create_target']);
            }
        } else {

            return $this->render('view_target', [ 'model' => $model, 'managers' => $managers, "user_role" => $user_role, "user_id" => $user_id,"courses" => $courses, 'before_6_month' => $before_6_month, 'after_6_month' => $after_6_month, 'years' => $years]);
        }
    }

    public function actionEnable_disable_target(){
        if (Yii::$app->request->post()) {

            $data = Yii::$app->request->post();
            $month = $data['month'];
            $year = $data['year'];

            if (Yii::$app->request->post('is_checked') == true) {
                Yii::$app->db->createCommand("UPDATE assist_course_target SET status = 1 WHERE month = $month AND year = $year")->execute();
            } else {

                Yii::$app->db->createCommand("UPDATE assist_course_target SET status = 0 WHERE month = $month AND year = $year")->execute();
            }
        }
    }

    public function actionEdit_target($id){
        //redirect a user if not sales head
        if (!Yii::$app->CustomComponents->check_permission('edit_target')) {
            return $this->redirect(['create_target']);
        }

        $model = DvCourseTarget::findOne($id);
        if (empty($model)) {
            return $this->redirect(['create_role']);
        }

        if ($model->load(Yii::$app->request->post())) {

            if (isset($_POST['DvCourseTarget']['target'])) {
                $target = $_POST['DvCourseTarget']['target'];
                Yii::$app->db->createCommand("UPDATE assist_course_target SET target = '$target' WHERE id = '$id' ")->execute();
                $model->save();
                Yii::$app->session->setFlash('success', 'Course Target Updated successfully');
                return $this->redirect(['create_target']);
            } else {
                return $this->redirect(['create_target']);
            }
        } else {
            return $this->render('edit_target', ['model' => $model]);
        }
    }

    //CDO:03 June 2019 Purpose:Getting course name based on DA & DM
    public function actionCourse_domain_check(){
        $domian = Yii::$app->request->post('course_domain');
        if($domian != ''){
            $course = DvModuleModel::find()->where(['status'=>1,'mcourse'=>$domian])->all();
            $Dv_course = ArrayHelper::map($course, 'id', 'module_name');
            $output = '';
            $output .= '<select id="user_course" class="form-control" name="DvUsers[course][]" multiple="multiple" size="4" required="required" data-toggle="tooltip" data-placement="top" title="" data-original-title="Select module">';
            foreach ($Dv_course as $key => $value) {
                $output .= "<option value='".$key."'>".$value."</option>";
            }
            $output .= '</select>';
            return $output;
        }
    }//End of function:actionCourse_domain_check//

}