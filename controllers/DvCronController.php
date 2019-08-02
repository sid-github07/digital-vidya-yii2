<?php

namespace app\controllers;

use Yii;
use app\models\DvQuickBook;
use yii\web\Controller;
use QuickBooksOnline\API\DataService\DataService;
use app\models\DvManageMonthlyIncentiveExceptionRate;
use app\models\DvManageMonthlyIncentiveRate;
use app\models\DvRegistration;
//Begin-added on 29 May 2019
use app\models\DvAssistBatches; 
use app\models\DvParticipantBatchMeta;
use yii\helpers\ArrayHelper;
use app\models\DvModuleModel;
//End-added on 29 May 2019

class DvCronController extends Controller {

    /**
     * @CDO  - 07 Jan 2019
     * Createed cron to update refreshtoken and save in database
     * */
    public function actionQb() {

        $dv_settings = DvQuickBook::find()->all();

        $ClientID = "";
        $ClientSecret = "";
        $accessTokenKey = "";
        $refreshTokenKey = "";
        $QBORealmID = "";
        $flag1 = 0;
        $flag2 = 0;

        foreach ($dv_settings as $val) {

            if ($val->qb_key == "ClientID") {
                $ClientID = $val->qb_value;
            } else if ($val->qb_key == "ClientSecret") {
                $ClientSecret = $val->qb_value;
            } else if ($val->qb_key == "accessTokenKey") {
                $accessTokenKey = $val->qb_value;
            } else if ($val->qb_key == "refreshTokenKey") {
                $refreshTokenKey = $val->qb_value;
            } else if ($val->qb_key == "QBORealmID") {
                $QBORealmID = $val->qb_value;
            }
        }


        $dataService = DataService::Configure(array(
                    'auth_mode' => 'oauth2',
                    'ClientID' => $ClientID,
                    'ClientSecret' => $ClientSecret,
                    'accessTokenKey' => $accessTokenKey,
                    'refreshTokenKey' => $refreshTokenKey,
                    'QBORealmID' => $QBORealmID,
                    'baseUrl' => "Development"
        ));

        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        $accessTokenObject = $OAuth2LoginHelper->refreshToken();

        $accessTokenValue = $accessTokenObject->getAccessToken();
        $refreshTokenValue = $accessTokenObject->getRefreshToken();

        $setting_models = DvQuickBook::find()->where(['qb_key' => "accessTokenKey"])->one();
        $setting_models->qb_value = $accessTokenValue;
        $setting_models->save();
        if ($setting_models) {
            $flag1 = 1;
        }

        $setting_models_1 = DvQuickBook::find()->where(['qb_key' => "refreshTokenKey"])->one();
        $setting_models_1->qb_value = $refreshTokenValue;
        $setting_models_1->save();

        if ($setting_models_1) {
            $flag2 = 1;
        }

        if ($flag1 == 1 && $flag2 == 1) {
            $setting_models_2 = DvQuickBook::find()->where(['qb_key' => "LastUpdatedMsg"])->one();
            $setting_models_2->qb_value = "Token updated successfully on : " . date("l jS \of F Y h:i:s A");
            $setting_models_2->save();
            echo "Token updated successfully on : " . date("l jS \of F Y h:i:s A");
        }
    }

     /**
     * @CDO  - 26 Feb 2019
     * Createed cron to update all records from module allotment sheet
     * */
    public function actionUpdate_participant_data() {

        $environment = Yii::$app->params['environment']; // check server enviroment
      //   if($environment == 'Production'){
            // live
             $feed = "https://docs.google.com/spreadsheets/d/e/2PACX-1vTW9fhKRTvtu3JzIiIQ9rosX2_qSeLTRF_ZnlyxVNZFpCL5_v6OdAEiZ4t2pYB-XX7kOC1GmLroriM1/pub?gid=0&single=true&output=csv"; // live
             $update_email = "dharmendra@whizlabs.com";
       /* } else {
            // Set your CSV feed
            $feed = 'https://docs.google.com/spreadsheets/d/11TrQKjZK7OqKnek1cFsRb4X-Aidt5H5ABmAV_uOpMw8/pub?gid=642116903&single=true&output=csv';  
            // my
            $update_email = "chintand@whizlabs.com";
        }
*/
        // Arrays we'll use later
        $keys = array();
        $newArray = array();
         
        // get feed in excel format and convet it to in array by "," sepreted.  
        if (($handle = fopen($feed, 'r')) !== FALSE) { 
            $i = 0; 
            while (($lineArray = fgetcsv($handle, 4000, ',', '"')) !== FALSE) { 
              for ($j = 0; $j < count($lineArray); $j++) { 
                $arr[$i][$j] = $lineArray[$j]; 
              } 
              $i++; 
            } 
            fclose($handle); 
        }
         
        $data = $arr;
         
        // Set number of elements (minus 1 because we shift off the first row)
        $count = count($data) - 1;
         
        //Use first row for names  
        $labels = array_shift($data);  
         
        foreach ($labels as $label) {
          $keys[] = $label;
        }

        // Bring it all together
        for ($j = 0; $j < $count; $j++) {
          $d = array_combine($keys, $data[$j]);
          $newArray[$j] = $d;
        }
        $update_cnt = 0;
        $not_update_cnt = 0;
        $not_found = 0;
        $exist_found = 0;
       /* echo "<pre>";
        print_r($newArray);
        die;*/
        foreach($newArray as $value){
            $token_id = '';
            $fname = '';
            $lname = '';
            $email = '';
            $phone = '';
            $status = '';
            $allowed_modules = '';
            $modules_completed='';
            $batch_date = '';
            $coordinator = '';
            $course_name = '';
            $batch = '';
            $course = '';
            //Begin-Added on 29 may 2019
            $em_start_date = '';
            $smm_start_date = ''; 
            $im_start_date = '';
            $sem_start_date = '';
            $seo_start_date = '';
            $wa_start_date = ''; 
            //End-Added on 29 may 2019
            foreach($value as $key=>$val){
                if($key == "Token"){
                    $token_id = $val;
                }else if($key == "First Name"){
                    $fname = $val;
                }else if($key == "Last Name"){
                    $lname = $val;
                }else if($key == "Email"){
                    $email = $val;
                }else if($key == "Mobile"){
                    $phone = $val;
                }else if($key == "Status"){
                    $status = $val;
                }else if($key == "Allowed Modules"){
                    $allowed_modules = $val;
                }else if($key == "Modules Completed"){
                    $modules_completed=$val;
                }else if($key == "Batch Date"){
                    $batch_date = $val;
                }else if($key == "Coordinator"){
                    $coordinator = $val;
                }else if($key == "Course Name"){
                    $course_name = $val;
                }else if($key == "Batch"){
                    $batch = $val;
                }else if($key == "EM"){
                    $em_start_date = $val;
                }else if($key == "SMM"){
                    $smm_start_date = $val; 
                }else if($key == "IM"){
                    $im_start_date = $val;
                }else if($key == "SEM"){
                    $sem_start_date = $val;
                }else if($key == "SEO"){
                    $seo_start_date = $val;
                }else if($key == "WA"){
                    $wa_start_date = $val;  
                }
                //Columns-Y to AD Data::EM  SMM IM  SEM SEO WA
               
            }

          if($token_id != ''){
              
            $pp_model =  DvRegistration::find()->Where(['token_id' => $token_id])->one();
           /* if($pp_model){
   

              $pp_model1 =  DvRegistration::find(284)->one();

                          
              echo "<pre>";
              print_r($pp_model1);
              die;

            }*/

            /*$pp_model1->mobile = '790171616100';

            if($pp_model1->save()){
              echo "in";
            }else{
              echo "else";
            }
            die;*/
            

          
            if($pp_model){

                if($fname != ''){
                    $pp_model['first_name'] = $fname;
                }
                
                if($email != ''){
                    $pp_model['email'] = $email;
                }
               
                if($phone != ''){
                    $pp_model['mobile'] = $phone;
                }
                
                if($status != ''){
                  $participant_status = ''; 
                  if($status == 'Active'){
                    $participant_status = 1;
                  }else if($status == 'On Hold'){
                    $participant_status = 2;
                  }else if($status == 'Drop off'){
                    $participant_status = 3;
                  }else if($status == 'Completed'){
                    $participant_status = 4;
                  }
                  $pp_model['participant_status'] = $participant_status;
                }
                 
                if($allowed_modules != ''){
                  $pp_model['modules_allowed'] = $allowed_modules;
                }
               

                if($course_name != ''){

                  if($course_name == 'CDMM'){
                    $course = '1';
                  }else if($course_name == "CPDM"){        
                    $course = '2';
                  }else if($course_name == "EM"){        
                    $course = '3';
                  }else if($course_name == "SMM"){
                    $course = '4';
                  }else if($course_name == "IM"){
                    $course = '5';
                  }else if($course_name == "SEM"){
                    $course = '6';
                  }else if($course_name == "SEO"){
                    $course = '7';
                  }else if($course_name == "WA"){
                    $course = '8';
                  }else if($course_name == "MM"){
                    $course = '9';
                  }else if($course_name == "CMAM"){
                    $course = '10';
                  }else if($course_name == "CFMM"){
                    $course = '11';
                  }else if($course_name == "TJW"){
                    $course = '12';
                  }else if($course_name == "DAR"){
                    $course = '13';
                  }else if($course_name == "DAP"){
                    $course = '14';
                  }else if($course_name == "DSAS"){
                    $course = '15';
                  }else if($course_name == "DAE"){
                    $course = '16';
                  }else if($course_name == "BDA"){
                    $course = '17';
                  }else if($course_name == "CDMM"){
                    $course = '18';
                  }else{
                    $course = '';
                  }

                  $pp_model['course'] = $course;
                }

               
                if($coordinator != ''){
                    $pp_model['program_coordinator'] = $coordinator;
                }

                
                if($lname != ''){
                    $pp_model['last_name'] = $lname;
                }

                 
                //if($modules_completed != ''){
                    //due to added modules completed based of batch's end date so provided comment here.
                    $pp_model_completed = $pp_model['modules_completed'];
                    //$pp_model['modules_completed'] = $modules_completed;
                //}
                
                if($batch != ''){
                    $pp_model['available_batch_opt'] = $batch;
                }

                
                if($batch_date != ''){
                  $pp_model['course_batch'] = $batch_date;

                  $course_batch_date = explode("#",$batch_date)[0];
                  $course_batch_date =   date("Y-m-d", strtotime($course_batch_date));              

                  $pp_model['course_batch_date'] = $course_batch_date;
                }

                //Begin-Added on 29 may 2019 :  
                $total_completed_module = $pp_model_completed;
                if($em_start_date !=''){
                  $module = "EM";
                  $module_id = DvModuleModel::find()->where(['module_name'=>$module])->one()->id;
                  $startdate = explode("#",$em_start_date);
                  if($startdate){
                    $em_start_date = $startdate[0];
                  }
                  $start_date = date('d-m-Y',strtotime(trim($em_start_date)));
                 
                  //get batch_id
                  $batch_data = DvAssistBatches::find()->where(['module'=>$module_id])->andWhere(['start_date'=>$start_date])->one();
                  if(!empty($batch_data)){
                    //get participant batch meta
                    $batch_meta_data = DvParticipantBatchMeta::find()->where(['pid'=>$pp_model['id']])->andWhere(['batch_id'=>$batch_data->id])->one();
                    if(empty($batch_meta_data)){
                      //insert if not exist participant batch meta
                      Yii::$app->db->createCommand()->insert('assist_participant_batch_meta', [ 'pid' => $pp_model['id'],'batch_id' => $batch_data->id])->execute();
                      if(strtotime(date('d-m-Y',strtotime($batch_data['end_date']))) < strtotime(date('d-m-Y'))){
                        //update module completed
                        $total_completed_module = $total_completed_module + 1;
                      }
                    }
                  }
                }//End of $em_start_date//
               
                            
                if($smm_start_date != ""){
                  $module = "SMM";
                  $module_id = DvModuleModel::find()->where(['module_name'=>$module])->one()->id;
                  
                  $startdate = explode("#",trim($smm_start_date));
                  if($startdate){
                    $smm_start_date = $startdate[0];
                  }

                  $start_date = date('d-m-Y',strtotime(trim($smm_start_date)));
                   
                  //get batch_id
                  $batch_data = DvAssistBatches::find()->where(['module'=>$module_id])->andWhere(['start_date'=>$start_date])->one();
                  if(!empty($batch_data)){
                    //get participant batch meta
                    $batch_meta_data = DvParticipantBatchMeta::find()->where(['pid'=>$pp_model['id']])->andWhere(['batch_id'=>$batch_data->id])->one();
                    if(empty($batch_meta_data)){
                      //insert if not exist participant batch meta
                      Yii::$app->db->createCommand()->insert('assist_participant_batch_meta', [ 'pid' => $pp_model['id'],'batch_id' => $batch_data->id])->execute();
                      if(strtotime(date('d-m-Y',strtotime($batch_data['end_date']))) < strtotime(date('d-m-Y'))){
                        //update module completed
                         
                        $total_completed_module = $total_completed_module + 1;
                      }
                    }
                  }
                } 
               
                if($im_start_date != ""){
                   $module = "IM";
                  $module_id = DvModuleModel::find()->where(['module_name'=>$module])->one()->id;

                  $startdate = explode("#",$im_start_date);
                  if($startdate){
                    $im_start_date = $startdate[0];
                  }
                  $start_date = date('d-m-Y',strtotime(trim($im_start_date)));
                  
                   
                  //get batch_id
                  $batch_data = DvAssistBatches::find()->where(['module'=>$module_id])->andWhere(['start_date'=>$start_date])->one();
                  if(!empty($batch_data)){
                    //get participant batch meta
                    $batch_meta_data = DvParticipantBatchMeta::find()->where(['pid'=>$pp_model['id']])->andWhere(['batch_id'=>$batch_data->id])->one();
                    if(empty($batch_meta_data)){
                      //insert if not exist participant batch meta
                      Yii::$app->db->createCommand()->insert('assist_participant_batch_meta', [ 'pid' => $pp_model['id'],'batch_id' => $batch_data->id])->execute();
                      if(strtotime(date('d-m-Y',strtotime($batch_data['end_date']))) < strtotime(date('d-m-Y'))){
                        //update module completed
                        $total_completed_module = $total_completed_module + 1;
                        
                      }
                    }
                  }
                } 

                if($sem_start_date != ""){
                   $module = "SEM";
                  $module_id = DvModuleModel::find()->where(['module_name'=>$module])->one()->id;
                  
                  $startdate = explode("#",$sem_start_date);
                  if($startdate){
                    $sem_start_date = $startdate[0];
                  }
                  $start_date = date('d-m-Y',strtotime(trim($sem_start_date)));

                  //get batch_id
                  $batch_data = DvAssistBatches::find()->where(['module'=>$module_id])->andWhere(['start_date'=>$start_date])->one();
                  if(!empty($batch_data)){
                    //get participant batch meta
                    $batch_meta_data = DvParticipantBatchMeta::find()->where(['pid'=>$pp_model['id']])->andWhere(['batch_id'=>$batch_data->id])->one();
                    if(empty($batch_meta_data)){
                      //insert if not exist participant batch meta
                      Yii::$app->db->createCommand()->insert('assist_participant_batch_meta', [ 'pid' => $pp_model['id'],'batch_id' => $batch_data->id])->execute();
                      if(strtotime(date('d-m-Y',strtotime($batch_data['end_date']))) < strtotime(date('d-m-Y'))){
                        //update module completed
                         
                        $total_completed_module = $total_completed_module + 1;
                      }
                    }
                  }
                } 

                if($seo_start_date != ""){
                   $module = "SEO";
                  $module_id = DvModuleModel::find()->where(['module_name'=>$module])->one()->id;

                  $startdate = explode("#",$seo_start_date);
                  if($startdate){
                    $seo_start_date = $startdate[0];
                  }
                  $start_date = date('d-m-Y',strtotime(trim($seo_start_date)));

                  //get batch_id
                  $batch_data = DvAssistBatches::find()->where(['module'=>$module_id])->andWhere(['start_date'=>$start_date])->one();
                  if(!empty($batch_data)){
                    //get participant batch meta
                    $batch_meta_data = DvParticipantBatchMeta::find()->where(['pid'=>$pp_model['id']])->andWhere(['batch_id'=>$batch_data->id])->one();
                    if(empty($batch_meta_data)){
                      //insert if not exist participant batch meta
                      Yii::$app->db->createCommand()->insert('assist_participant_batch_meta', [ 'pid' => $pp_model['id'],'batch_id' => $batch_data->id])->execute();
                      if(strtotime(date('d-m-Y',strtotime($batch_data['end_date']))) < strtotime(date('d-m-Y'))){
                        //update module completed
                         
                        $total_completed_module = $total_completed_module + 1;
                      }
                    }
                  }
                } 

                if($wa_start_date != ""){
                   $module = "WA";
                  $module_id = DvModuleModel::find()->where(['module_name'=>$module])->one()->id;

                   $startdate = explode("#",$wa_start_date);
                  if($startdate){
                    $wa_start_date = $startdate[0];
                  }
                  $start_date = date('d-m-Y',strtotime(trim($wa_start_date)));


                   
                  //get batch_id
                  $batch_data = DvAssistBatches::find()->where(['module'=>$module_id])->andWhere(['start_date'=>$start_date])->one();
                  if(!empty($batch_data)){
                    //get participant batch meta
                    $batch_meta_data = DvParticipantBatchMeta::find()->where(['pid'=>$pp_model['id']])->andWhere(['batch_id'=>$batch_data->id])->one();
                    if(empty($batch_meta_data)){
                      //insert if not exist participant batch meta
                      Yii::$app->db->createCommand()->insert('assist_participant_batch_meta', [ 'pid' => $pp_model['id'],'batch_id' => $batch_data->id])->execute();
                      if(strtotime(date('d-m-Y',strtotime($batch_data['end_date']))) < strtotime(date('d-m-Y'))){
                        //update module completed
                         
                        $total_completed_module = $total_completed_module + 1;
                      }
                    }
                  }
                } 

                Yii::$app->db->createCommand()->update('assist_participant', ['modules_completed' => $total_completed_module ], "id =". $pp_model['id'])->execute();




                //End-Added on 29 may 2019
                
                $model =  DvRegistration::find()->Where(['email' => $email,'course'=>$course])->all();
                if(count($model) == 1){
                    $model = $model[0];
                    if($model->token_id != $token_id){
                        if($token_id == ''){
                          echo "<br>cdo in<br>";
                        }else{
                          echo "<br>cdo out<br>";
                        }
                       // echo $token_id."- Record already existing with same email same course : ".$email."<br>";  
                        $exist_found++;
                        /* Send mail when update fail */
                        $fromName = ['chintand@whizlabs.com' => 'Digital Vidya'];
                        //$update_email = "chintand@whizlabs.com";
                        $bodyMsg = 'Deal Sales Team,

                            <p>Same email Id with same course name is found in google sheet at the time of sync data with database.</p>
                            <p><bold>Token ID : </bold>'.$token_id.'</p>
                            <p><bold>Email ID :</bold>'.$email.'</p>
                            <p><bold>Course Name :</bold>'.$course_name.'</p>
                            <p>So please verify course name in google sheet.</p>

                            <p>Regards,<br>
                            Cron System</p>';

                      /*  $is_sent = Yii::$app->mailer->compose()
                             ->setFrom($fromName)
                             ->setTo($update_email)
                             ->setSubject('Digital Vidya : Found Same email with same course name')
                             ->setHtmlBody($bodyMsg)
                             ->send();
                        if (!$is_sent) {
                            echo "Mail not sent for Record not update due to incorrect update query";
                            die;
                        }*/
                    } else {
                        /*echo "<pre>";
                        print_r($pp_model);
                        die;*/
                       //  $pp_model['mobile1'] = '13214564';
                        

                        if($pp_model->save()) {
                            $update_cnt++;
                          //  echo $token_id." : Record Update<br>";
                        } else {
                            $not_update_cnt++;
                            echo $token_id." - Not Updated<br>";
                            die;
                            ///Send mail when update fail 
                            $fromName = ['chintand@whizlabs.com' => 'Digital Vidya'];
                            //$update_email = "chintand@whizlabs.com";
                            $bodyMsg = 'Deal Sales Team,

                                <p>In Digital Vidya Update Sheet System this record is not updated on database so please check in sheet with below token id. </p>
                                <bold>Token ID : </bold>'.$token_id.'
                                
                                <p>Regards,<br>
                                Cron System</p>';

                            $is_sent = Yii::$app->mailer->compose()
                                 ->setFrom($fromName)
                                 ->setTo($update_email)
                                 ->setSubject('Digital Vidya : Fail to Update Sheet Records')
                                 ->setHtmlBody($bodyMsg)
                                 ->send();
                            if (!$is_sent) {
                                echo "Mail not sent for Record not update due to incorrect update query";
                                die;
                            }
                        } 
                    }
                }else{
                  
                  if($pp_model->save()) {
                      $update_cnt++;
                      // echo $token_id." : Record Update<br>";
                  } else {
                     //  echo $token_id."- Record already existing with same email same course : more then one<br>";  
                      $exist_found++;

                      // Send mail when update fail 
                      $fromName = ['chintand@whizlabs.com' => 'Digital Vidya'];
                      //$update_email = "chintand@whizlabs.com";
                      $bodyMsg = 'Deal Sales Team,

                          <p>Same email id with same course name is found in google sheet at the time of sync data with database.</p>
                          <p><bold>Token ID : </bold>'.$token_id.'</p>
                          <p><bold>Email ID :</bold>'.$email.'</p>
                          <p><bold>Course Name :</bold>'.$course_name.'</p>
                          <p>So please verify course name in google sheet.</p>

                          <p>Regards,<br>
                          Cron System</p>';

                     /* $is_sent = Yii::$app->mailer->compose()
                           ->setFrom($fromName)
                           ->setTo($update_email)
                           ->setSubject('Digital Vidya : Found Same email with same course name')
                           ->setHtmlBody($bodyMsg)
                           ->send();
                      if (!$is_sent) {
                          echo "Mail not sent for Record not update due to incorrect update query";
                          die;
                      }*/
                  } 
                }
                
            }else{
              $not_found++;
             // echo $token_id." - Record Not Found<br>";
            }
            
          }else{
           // echo "Token id is null <br>";
            $not_found++;
          }

          /*if($token_id == "DV-g4oiypg4uc"){
            echo "TEST";
            die;
          }*/
        }
        echo "<br><br>Total Updated Records is : ".$update_cnt."<br>";
        echo "Total wrong query Records is : ".$not_update_cnt."<br>";
        echo "Total Record Not Found by tokenid : ".$not_found."<br>";
        echo "Total Record Found with same email id with same course  : ".$exist_found;
    }

    /**
     * @Hetal  - 07 Feb 2019
     * Created cron to update incentive exception in database
     * */
    public function actionManaer_incentive_exception_carry() {
        $executives = Yii::$app->db->createCommand("SELECT * FROM assist_users as du 
            WHERE du.id IN(SELECT uid FROM assist_user_meta WHERE meta_key = 'role' AND meta_value=2) AND status=1")->queryAll();
        $current_month = date('m');
        if ($current_month == 1) {
            $last_month = 12;
        } else {
            $last_month = $current_month - 1;
        }
        if (!empty($executives)) {
            foreach ($executives as $executive) {

                $executive_id = $executive['id'];

                /* For DA */
                $da_exception_of_executive = DvManageMonthlyIncentiveExceptionRate::find()->where(['executive_id' => $executive['id'], 'month' => $last_month, 'domain' => 'da'])->orderBy(['id' => 'DESC'])->All();
                if (!empty($da_exception_of_executive)) {
                    $participants = Yii::$app->db->createCommand("SELECT * FROM assist_participant as ap 
                        JOIN assist_course as ac ON ac.name = ap.course WHERE ac.mcourse='da' AND ap.sales_user_id=$executive_id  AND MONTH(ap.created_on) = $last_month")->queryAll();
                    
                    
                    if (!empty($participants)) {
                        $total_participant = sizeof($participants);
                    } else {
                        $total_participant = 0;
                    }

                    if ($da_exception_of_executive[0]->max_closures > $total_participant) {
                        $diff_target = $da_exception_of_executive[0]->max_closures - $total_participant;

                        $get_current_month_exception = DvManageMonthlyIncentiveExceptionRate::find()->where(['executive_id' => $executive['id'], 'month' => $current_month, 'domain' => 'da'])->orderBy(['id' => 'DESC'])->All();

                        $inserted_date = date("Y-m-d H:i:s");
                        if (!empty($get_current_month_exception)) {
                            //$delete_current_month_exception = DvManageMonthlyIncentiveExceptionRate::deleteAll(['executive_id' => $executive['id'], 'month' => $current_month, 'domain' => 'da']);
                            foreach ($get_current_month_exception as $key => $exception) {
                                $model = new DvManageMonthlyIncentiveExceptionRate();
                                if ($key == 0) {
                                    $model->executive_id = $executive['id'];
                                    $model->month = $current_month;
                                    $model->domain = "da";
                                    $model->min_closures = 0;
                                    $model->max_closures = intval($exception['max_closures']) + intval($diff_target);
                                    $model->rate = $exception['rate'];
                                    $model->created_at = $inserted_date;
                                    $model->updated_at = $inserted_date;
                                    //$model->save();
                                } else if ($key == 1) {
                                    $model->executive_id = $executive['id'];
                                    $model->month = $current_month;
                                    $model->domain = "da";
                                    $model->min_closures = intval($get_current_month_exception[0]['max_closures']) + intval($diff_target) + 1;
                                    $model->max_closures = $exception['max_closures'];
                                    $model->rate = $exception['rate'];
                                    $model->created_at = $inserted_date;
                                    $model->updated_at = $inserted_date;
                                    //$model->save();
                                } else {
                                    $model->executive_id = $executive['id'];
                                    $model->month = $current_month;
                                    $model->domain = "da";
                                    $model->min_closures = intval($exception['min_closures']);
                                    $model->max_closures = $exception['max_closures'];
                                    $model->rate = $exception['rate'];
                                    $model->created_at = $inserted_date;
                                    $model->updated_at = $inserted_date;
                                    //$model->save();
                                }
                            }
                        }
                    }
                }

                /* For DM */
                $dm_exception_of_executive = DvManageMonthlyIncentiveExceptionRate::find()->where(['executive_id' => $executive['id'], 'month' => $last_month, 'domain' => 'dm'])->orderBy(['id' => 'DESC'])->All();

                if (!empty($dm_exception_of_executive)) {

                    $participants = Yii::$app->db->createCommand("SELECT * FROM assist_participant as ap 
                        JOIN assist_course as ac ON ac.name = ap.course WHERE ac.mcourse='dm' AND ap.sales_user_id=$executive_id  AND MONTH(ap.created_on) = $last_month")->queryAll();

                    if (!empty($participants)) {
                        $total_participant = sizeof($participants);
                    } else {
                        $total_participant = 0;
                    }

                    if ($dm_exception_of_executive[0]->max_closures > $total_participant) {
                        $diff_target = $dm_exception_of_executive[0]->max_closures - $total_participant;

                        $get_current_month_exception = DvManageMonthlyIncentiveExceptionRate::find()->where(['executive_id' => $executive['id'], 'month' => $current_month, 'domain' => 'dm'])->orderBy(['id' => 'DESC'])->All();

                        $inserted_date = date("Y-m-d H:i:s");
                        if (!empty($get_current_month_exception)) {
                            //$delete_current_month_exception = DvManageMonthlyIncentiveExceptionRate::deleteAll(['executive_id' => $executive['id'], 'month' => $current_month, 'domain' => 'dm']);
                            foreach ($get_current_month_exception as $key => $exception) {
                                $model = new DvManageMonthlyIncentiveExceptionRate();
                                if ($key == 0) {
                                    $model->executive_id = $executive['id'];
                                    $model->month = $current_month;
                                    $model->domain = "dm";
                                    $model->min_closures = 0;
                                    $model->max_closures = intval($exception['max_closures']) + intval($diff_target);
                                    $model->rate = $exception['rate'];
                                    $model->created_at = $inserted_date;
                                    $model->updated_at = $inserted_date;
                                    $model->save();
                                } else if ($key == 1) {
                                    $model->executive_id = $executive['id'];
                                    $model->month = $current_month;
                                    $model->domain = "dm";
                                    $model->min_closures = intval($get_current_month_exception[0]['max_closures']) + intval($diff_target) + 1;
                                    $model->max_closures = $exception['max_closures'];
                                    $model->rate = $exception['rate'];
                                    $model->created_at = $inserted_date;
                                    $model->updated_at = $inserted_date;
                                    $model->save();
                                } else {
                                    $model->executive_id = $executive['id'];
                                    $model->month = $current_month;
                                    $model->domain = "dm";
                                    $model->min_closures = intval($exception['min_closures']);
                                    $model->max_closures = $exception['max_closures'];
                                    $model->rate = $exception['rate'];
                                    $model->created_at = $inserted_date;
                                    $model->updated_at = $inserted_date;
                                    $model->save();
                                }
                            }
                        }
                    }
                }
            }
        }
    }



     /**
     *  27 May 2019
     * Createed cron to create Module & Batch from sheet.
     **/
    public function actionGet_batch_data() {

        $feed = "https://docs.google.com/spreadsheets/d/e/2PACX-1vTmAKMo20xhwdD8OxE-Cc-Vj50Ukxu5gJ9yQoVMoHK3T2P77dlgiU4fy7aBvS4B8V9aygWuw5mZDEfe/pub?gid=0&single=true&output=csv";

        // Arrays we'll use later
        $keys = array();
        $newArray = array();
         
        // get feed in excel format and convet it to in array by "," sepreted.  
        if (($handle = fopen($feed, 'r')) !== FALSE) {
            $i = 0; 
            while (($lineArray = fgetcsv($handle, 4000, ',', '"')) !== FALSE) { 
              for ($j = 0; $j < count($lineArray); $j++) { 
                $arr[$i][$j] = $lineArray[$j]; 
              } 
              $i++; 
            } 
            fclose($handle); 
        }
         
        $data = $arr;
         
        // Set number of elements (minus 1 because we shift off the first row)
        $count = count($data) - 1;
         
        //Use first row for names  
        $labels = array_shift($data);  
         
        foreach ($labels as $label) {
          $keys[] = $label;
        }

        // Bring it all together
        for ($j = 0; $j < $count; $j++) {
          $d = array_combine($keys, $data[$j]);
          $newArray[$j] = $d;
        }
        $update_cnt = 0;
        $not_update_cnt = 0;
        $not_found = 0;
        $exist_found = 0;
        return $newArray;
        /*echo "<pre>";
        print_r($newArray);
        die;*/

      }//End of function:actionGet_batch_data//

}
