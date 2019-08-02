<?php
namespace app\controllers;
use Yii;
use app\models\DvUsers;
use yii\web\Controller;
use app\models\DvCourse;
use app\models\DvModuleModel;
use app\models\DvStates;
use app\models\DvCities;
use yii\data\Pagination;
use app\models\DvModules;
use app\models\DvUserMeta;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\models\DvTrainingTopics;
use yii\web\NotFoundHttpException;
use app\models\DvAssistBatches;

/**
 * DvDeliveryController created to run all the functions related with http://website.com/dv-delivery/index
 *  Last updated on 30-01-2019
 */
class DvDeliveryController extends Controller{
    /**
     * @inheritdoc
     */
    public function behaviors(){
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(){ 
        //redirect a user if not super admin
        if(!Yii::$app->CustomComponents->check_permission('delivery')) {
            return $this->redirect(['site/index']);
        }
       // $query = DvAssistBatches::find()->all();
        $query = DvAssistBatches::find();
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count,'pageSize' => 10]);
        $models = $query->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('index', [ 'modules' => $models,'total_records' => $count, 'pages' => $pagination]);
    }


    // this function is created for sorting the data on Index page
    public function actionSort($order){
        //redirect a user if not super admin
        if(!Yii::$app->CustomComponents->check_permission('delivery')) {
            return $this->redirect(['site/index']);
        }
       // $query = DvAssistBatches::find()->all();
        $query = DvAssistBatches::find();
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count,'pageSize' => 10]);
        if($order == 'asc'){ 
            $sor_o = SORT_ASC;
        } elseif($order == 'desc'){ 
            $sor_o = SORT_DESC;
        } else { 
            $sor_o = SORT_ASC;
        }
        //$models = $query->offset($pagination->offset)->orderBy(['start_date' => $sor_o,'created_on'=>$sor_o])->limit($pagination->limit)->all();
        //updated on 31 may 2019
        $models = $query->offset($pagination->offset)->orderBy(['UNIX_TIMESTAMP(STR_TO_DATE(start_date,"%d-%m-%Y"))' => $sor_o])->limit($pagination->limit)->all();

        return $this->render('index', [ 'modules' => $models,'total_records' => $count, 'pages' => $pagination]);
    }

    // this function is for fiter the data on Index Page
    public function actionFilter($sdate,$edate,$bymonth,$trainer,$coordinator,$vstatus,$bstatus){
        //redirect a user if not super admin
 	    if(!Yii::$app->CustomComponents->check_permission('delivery')) {
            return $this->redirect(['site/index']);
        }
        $module = isset($_GET['module']) && $_GET['module'][0] != '' ? $_GET['module'] : '';
        // write case for ALL (if user directly hit on filter button and list all records)
        if((empty($module != null)) && (empty($sdate != null)) && (empty($edate != null)) && (empty($bymonth != null)) && (empty($trainer != null)) && (empty($coordinator != null)) && (empty($vstatus != null)) && (empty($bstatus != null))){
            $query = DvAssistBatches::find();
            $count = $query->count();
            $pagination = new Pagination(['totalCount' => $count,'pageSize' => 10]);
            $models = $query->offset($pagination->offset)->limit($pagination->limit)->all();
        } else { // write case for ALL (if user directly hit on filter button and list all records)

        $result = $result_1 = $result_2 = $result_3 = $result_4 = $result_5 = $result_6 = array();
        // Condition based on Module/Course
        if(!empty($module) && count($module) > 0){
            $ucourse = DvAssistBatches::find()
                        ->select(['assist_batches.id'])
                        ->where(['in','assist_batches.module',$module])
                        ->createCommand()
                        ->queryAll();
            //$ucourse = Yii::$app->db->createCommand("SELECT id FROM assist_batches WHERE module = '$module'")->queryAll();
 		} else {
            $ucourse  = array();
        }

        
        //Start and End date combine
        /* if(!empty($sdate) && !empty($edate)){

 			$usdate = Yii::$app->db->createCommand("SELECT id FROM assist_batches WHERE DATE_FORMAT(STR_TO_DATE(start_date,'%d-%m-%Y'), '%d-%m-%Y') >= DATE_FORMAT(STR_TO_DATE('$sdate','%d-%m-%Y'), '%d-%m-%Y') AND DATE_FORMAT(STR_TO_DATE(end_date,'%d-%m-%Y'), '%d-%m-%Y') <= DATE_FORMAT(STR_TO_DATE('$edate','%d-%m-%Y'), '%d-%m-%Y')")->queryAll();
 		}
 		*/
		
         
        // Condition based on Start Date
        if(!empty($sdate)){
 			if(empty($edate)){
        		$usdate = Yii::$app->db->createCommand("SELECT id FROM assist_batches WHERE DATE_FORMAT(STR_TO_DATE(start_date, '%d-%m-%Y'), '%d-%m-%Y') = DATE_FORMAT(STR_TO_DATE('$sdate','%d-%m-%Y'), '%d-%m-%Y')")->queryAll();		
        	}else{
 				$usdate = Yii::$app->db->createCommand("SELECT id FROM assist_batches WHERE DATE_FORMAT(STR_TO_DATE(start_date, '%d-%m-%Y'), '%d-%m-%Y') >= DATE_FORMAT(STR_TO_DATE('$sdate','%d-%m-%Y'), '%d-%m-%Y')")->queryAll();
        	}
 			if(empty($ucourse)){
                $result = $usdate;
            } else {
                foreach($ucourse as $key=>$val){
                    if(in_array($val, $usdate)){
                        $result[] = $val;
                    }
                }
            }
 		} else {
            $usdate = array();
            $result = $ucourse;
        }
         
        // Condition based on End Date 
        if(!empty($edate)){
        	if(empty($sdate)){
        		$uedate = Yii::$app->db->createCommand("SELECT id FROM assist_batches WHERE DATE_FORMAT(STR_TO_DATE(end_date, '%d-%m-%Y'),'%d-%m-%Y') = DATE_FORMAT(STR_TO_DATE('$edate','%d-%m-%Y'), '%d-%m-%Y')")->queryAll();
        	}else{
         		$uedate = Yii::$app->db->createCommand("SELECT id FROM assist_batches WHERE DATE_FORMAT(STR_TO_DATE(end_date, '%d-%m-%Y'),'%d-%m-%Y') <= DATE_FORMAT(STR_TO_DATE('$edate','%d-%m-%Y'), '%d-%m-%Y')")->queryAll();
         	}
 			if(!empty($result)){
                foreach($result as $key=>$val){
                    if(in_array($val, $uedate)){
                        $result_1[] = $val;
                    }
                }
            }else{
            	$result_1 = $uedate;
            }
        } else {
            $result_1 = $result;
        }
        /*echo "<pre>";
        print_r($uedate);
        die;*/
		// Condition based on Month
        if(!empty($bymonth)){ 
            $new_date = explode('_',$bymonth);
            $fyear = $new_date['1'];
            $fmonth = date("m", strtotime($new_date['0']));
            $fdate = '01';
            $filter_date = $fdate.'-'.$fmonth.'-'.$fyear;

            // condition based on running batch status
            if($vstatus == '1'){                
                $uby_month = Yii::$app->db->createCommand("SELECT mid FROM assist_batches_meta WHERE DATE_FORMAT(STR_TO_DATE(meta_value, '%d-%m-%Y'), '%m-%Y') = DATE_FORMAT(STR_TO_DATE('$filter_date','%d-%m-%Y'), '%m-%Y') AND meta_key = 'final_end_date' ")->queryAll();

                $ubymonth = array();
                foreach ($uby_month as $key => $value){
                    $ubymonth[] = array('id' => $value['mid']);
                }               

            } else {
                $ubymonth = Yii::$app->db->createCommand("SELECT id FROM assist_batches WHERE DATE_FORMAT(STR_TO_DATE(start_date, '%d-%m-%Y'), '%m-%Y') = DATE_FORMAT(STR_TO_DATE('$filter_date','%d-%m-%Y'), '%m-%Y')")->queryAll();   
            }

                if(!empty($result_1)){
                    foreach($result_1 as $key=>$val){
                        if(in_array($val,$ubymonth)){
                            $result_2[] = $val;
                        }
                    }
                } else {
                    $result_2 = $ubymonth;
                }
            } else {
                $result_2 = $result_1;
            }

        // Condition based on Trainer
        if(!empty($trainer)){
          $utrainer = Yii::$app->db->createCommand("SELECT id FROM assist_batches WHERE trainer = '$trainer'")->queryAll();
            if(!empty($result_2)){
                foreach($result_2 as $key=>$val){
                    if(in_array($val,$utrainer)){
                        $result_3[] = $val;
                    }
                }
            } else {
                $result_3 = $utrainer;
            }   
        } else {
            $result_3 = $result_2;
        }

        // Condition based on Coordinator         
        if(!empty($coordinator)){
          $ucoordinator = Yii::$app->db->createCommand("SELECT id FROM assist_batches WHERE coordinator = '$coordinator'")->queryAll();
            if(!empty($result_3)){
                foreach($result_3 as $key=>$val){
                    if(in_array($val,$ucoordinator)){
                        $result_4[] = $val;
                    }
                }
            } else {
                $result_4 = $ucoordinator;
            }
                
        } else {
            $result_4 = $result_3;
        }

        // Condition based on Running batch Status
        if($vstatus != ''){         
            $uv_status = array();
            $uvstatus = Yii::$app->db->createCommand("SELECT mid FROM assist_batches_meta WHERE meta_value = '$vstatus' AND meta_key = 'running_batch_status' ")->queryAll();
            foreach ($uvstatus as $key => $value) {
                $uv_status[] = array('id' => $value['mid']);
            }

            if( (!empty($bymonth) && (empty($result_4)) )){ 
                $uv_status = array(); //echo 'test';
            }

            if(!empty($result_4)){
                foreach($result_4 as $key=>$val){
                    if(in_array($val,$uv_status)){
                        $result_5[] = $val;
                    }
                }
            } else {
                 $result_5 = $uv_status;
            }
        } else {
            $result_5 = $result_4;
        }

        // Condition based on Batch Status
        if($bstatus != ''){
          $ubstatus = Yii::$app->db->createCommand("SELECT mid FROM assist_batches_meta WHERE meta_value = '$bstatus' AND meta_key = 'batch_status' ")->queryAll();

          /*echo "<pre>";
          print_r($ubstatus);
          die;*/

          $ub_status = array();
          foreach ($ubstatus as $key => $value) {               
                $ub_status[] = array('id' => $value['mid']);
          }


            if(!empty($result_5)){
                foreach($result_5 as $key=>$val){
                    if(in_array($val,$ub_status)){
                        $result_6[] = $val;
                    }
                }
            } else {

            	if((($module == null)) && (($sdate == null)) && (($edate == null)) && (($bymonth == null)) && (($trainer == null)) && (($coordinator == null)) && (($vstatus == null)) && (($bstatus != null))){
            				$result_6 = $ub_status;
            	} else {
            				$result_6 = $result_5;
            	}

            }

        } else {
            $result_6 = $result_5;
        }
         
        $count = count($result_6);
        $output = '';
        $i = 0;
        foreach ($result_6 as $key => $value){
            foreach ($value as $key => $value2){
              $i++;
              $output .= $value2;
                if($count > $i){
                  $output .= ', ';
                }
            }
        }

        $ids = explode(',', $output);
        $idss = array_unique($ids);
        $count = count($ids);     
        $query = DvAssistBatches::find();        
        $pagination = new Pagination(['totalCount' => $count,'pageSize' => 10]);
        $models = $query->orWhere(['id' => $ids])->offset($pagination->offset)->limit($pagination->limit)->all();

    }// write case for ALL (if user directly hit on filter button and list all records)

        return $this->render('index',['modules' =>$models,'total_records' => $count, 'pages' => $pagination]);   
    }

    // funciton for detial page of Batch
    public function actionView($id){
        //redirect a user if not super admin
        if(!Yii::$app->CustomComponents->check_permission('view_modules')) {
            return $this->redirect(['index']);
        }
        return $this->render('view', [ 'model' => $this->findModel($id)]);
    }

    /**
     * funciton for Creates a new Course.     
     */
    public function actionCreate_course(){ 
        //redirect a user if not super admin
        if(!Yii::$app->CustomComponents->check_permission('course')) {
            return $this->redirect(['index']);
        }

        $model = new DvCourse();

        if ($model->load(Yii::$app->request->post()) && $model->save()){
            Yii::$app->session->setFlash('success','New Course Created Successfully');
            return $this->redirect(['dv-delivery/create_course']);
        } else {
            return $this->render('create_course', [ 'model' => $model, ]);
        }
    }

    // function for Create Module
    public function actionCreate_module(){ 
        //redirect a user if not super admin
        if(!Yii::$app->CustomComponents->check_permission('create_modules')) {
            return $this->redirect(['index']);
        }
        $model = new DvAssistBatches();
        $model->created_by = Yii::$app->user->identity->id;
        
        if ($model->load(Yii::$app->request->post())/* && $model->save()*/){
            $trainer_notify = $_POST['trainer_notify'];            
            $data = Yii::$app->request->post();
            $trainer = $data['DvAssistBatches']['trainer'];
            if(empty($trainer)){
                $model->trainer = '0';
            }
            $end_date = $_POST['end_date'];
            $model->end_date = date('d-m-Y',strtotime($end_date));
            $allsession = $data['allsession'];
 			$model->save();
            // $session_dates = array();
            $cnt = 1;
            foreach($allsession as $session_value){
                foreach($session_value as $key => $value){

                    /*  if(!empty($value)){
                        $session_dates[] = $value;  
                    }*/
                    $key = $key.''.$cnt;
                    
                    Yii::$app->db->createCommand()->insert('assist_batches_meta', [ 'mid' => $model->id, 'meta_key' => $key, 'meta_value' => $value ])->execute();
                }
                $cnt++;
            }
            
            $all_sessions = $data['all_sessions'];
            Yii::$app->db->createCommand()->insert('assist_batches_meta', [ 'mid' => $model->id, 'meta_key' => 'all_sessions', 'meta_value' => $all_sessions ])->execute();
            //$final_end_date = $data['fend_date'];
            $final_end_date = $_POST['end_date'];
            Yii::$app->db->createCommand()->insert('assist_batches_meta', [ 'mid' => $model->id, 'meta_key' => 'final_end_date', 'meta_value' => $final_end_date ])->execute(); 

            //for upcoming entry in meta
            Yii::$app->db->createCommand()->insert('assist_batches_meta', [ 'mid' => $model->id, 'meta_key' => 'running_batch_status', 'meta_value' => 3 ])->execute();

            Yii::$app->session->setFlash('success','New Module Created Successfully'); 

            // send email to trainer
            if($trainer_notify == 1 && $model->trainer != 0){
                $from_email = DvUsers::find()->select('email')->where(['id' => $model->coordinator,"status" => 1])->one();
                $cf_name = DvUsers::find()->select('first_name')->where(['id' => $model->coordinator,"status" => 1])->one();
                $cl_name = DvUsers::find()->select('last_name')->where(['id' => $model->coordinator,"status" => 1])->one();
                $coordinator_name = $cf_name['first_name'].' '.$cl_name['last_name'];
                $coordinator_phone = Yii::$app->db->createCommand("SELECT meta_value FROM assist_user_meta WHERE uid = '$model->coordinator' AND meta_key = 'phone' ")->queryOne();
                $coordinator_phone_num = $coordinator_phone['meta_value']; 

                //print_r($from_email);
                $tf_name = DvUsers::find()->select('first_name')->where(['id' => $model->trainer,"status" => 1])->one();
                $tl_name = DvUsers::find()->select('last_name')->where(['id' => $model->trainer,"status" => 1])->one();
                $trainer_name = $tf_name['first_name'].' '.$tl_name['last_name']; 
                $to_email = DvUsers::find()->select('email')->where(['id' => $model->trainer,"status" => 1])->one();
                //$to_email_address = $to_email['email'];
                $module_id = $data['DvAssistBatches']['module'];
                $module = DvModuleModel::find()->where(['id'=>$module_id])->one()->module_name;    

                // Get Start & End dates for Google Calendar   
                $start_date = $data['DvAssistBatches']['start_date'];
                $start_year = date('Y', strtotime($start_date));            
                $start_month = date('m', strtotime($start_date));            
                $start_day = date('d', strtotime($start_date));            
                $start_time = ' '.$data['DvAssistBatches']['stiming']; 
                $start_time_h = date('H', strtotime($start_time));            
                $start_time_m = date('i', strtotime($start_time));
                $final_end_date = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key = 'final_end_date' ")->queryOne();
                $end_date = $final_end_date['meta_value'];
                $end_year = date('Y', strtotime($end_date));            
                $end_month = date('m', strtotime($end_date));
                $end_day = date('d', strtotime($end_date));
                $end_time = ' '.$data['DvAssistBatches']['etiming'];
                $end_time_h = date('H', strtotime($end_time));            
                $end_time_m = date('i', strtotime($end_time));

                // Send email 1st
                $subject = $module.' | '.$start_date .' | Module/Batch invitation';
                $body = "
                <p>Hi $trainer_name,</p>                
                <p>Thanks for confirming the <strong>$module</strong> batch.</p>
                <br>
                <p>Sharing the details below:</p>";
                $allsession = $data['allsession'];                
                // foreach($allsession as $key => $value){
                $cnt = 1;
                foreach($allsession as $session_value){
                    echo "<div><h2>Session ".$cnt.'<h2>';
                    echo "<div>";
                    foreach($session_value as $key => $value){
                            echo "<p> ".$key." ".$cnt.": ".$value."</p>";
                        /*$new_key = str_replace("session","Session ",$key);
                        if (strpos($new_key, 'rec') !== false) {
                            
                        } else {
                            $body .= "<p>".$new_key.": <strong>".$value."</strong></p>";    
                        }*/
                    }
                    echo "</div></div>";
                    $cnt++;                   
                }
                $body .= "                
                <p>Session Timings: <strong>$start_time to $end_time</strong></p>
                <br>
                <p>Will share the further details soon.</p>                
                <br>                
                <p>Best Wishes,</p>
                <p>$coordinator_name</p>
                <p>$coordinator_phone_num</p>";

                /*$is_sent = Yii::$app->mailer->compose()
                ->setFrom($from_email['email'])
                ->setTo($to_email['email'])
                ->setSubject($subject)
                ->setHtmlBody($body)
                ->send();*/
              

                // send email with google calender
                //$email = "anoops@whizlabs.com";
                $email = "neha@digitalvidya.com";
                $email_2 = "chintand@whizlabs.com";
                $meeting_location = "Online"; //Where will your meeting take place

                date_default_timezone_set('Asia/Kolkata');
                $dtstart = gmdate("Ymd\THis\Z", mktime($start_time_h, $start_time_m, 0, $start_month, $start_day, $start_year));
                $dtend = gmdate("Ymd\THis\Z", mktime($end_time_h, $end_time_m, 0, $end_month, $end_day, $end_year));

                // exclude holidays from google calendar
                //EXDATE;TZID=Asia/Kolkata:20151229T143000,20160105T143000

                $todaystamp = gmdate("Ymd\THis\Z");
                    
                //Create unique identifier
                $cal_uid = date('Ymd').'T'.date('His')."-".rand()."@digitalvidya.com";
                    
                //Create Mime Boundry
                $mime_boundary = "----Meeting Booking----".md5(time());
                    
                //Create Email Headers
                $headers = "From: ".$coordinator_name." <".$from_email['email'].">\n";
                $headers .= "Reply-To: ".$coordinator_name." <".$from_email['email'].">\n";
                
                $headers .= "MIME-Version: 1.0\n";
                $headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\n";
                $headers .= "Content-class: urn:content-classes:calendarmessage\n";
                
                //Create Email Body (HTML)
                $message = "--$mime_boundary\n";
                $message .= "Content-Type: text/html; charset=UTF-8\n";
                $message .= "Content-Transfer-Encoding: 8bit\n\n";
                
                $message .= "<html>\n";
                $message .= "<body>\n"; 
                $message .= $body;
                $message .= "</body>\n";
                $message .= "</html>\n";
                $message .= "--$mime_boundary\n";
                
                //Create ICAL Content (Google rfc 2445 for details and examples of usage) 
                $ical = 'BEGIN:VCALENDAR
                        PRODID:-//Google Inc//Google Calendar 70.9054//EN
                        VERSION:2.0
                        CALSCALE:GREGORIAN
                        METHOD:REQUEST
                        BEGIN:VEVENT
                        ORGANIZER:MAILTO:'.$from_email['email'].'
                        DTSTART:'.$dtstart.'
                        DTEND:'.$dtend.'
                        LOCATION:'.$meeting_location.'
                        TRANSP:OPAQUE
                        SEQUENCE:1
                        STATUS:CONFIRMED
                        UID:'.$cal_uid.'
                        ATTENDEE:mailto:'.$to_email['email'].'
                        DTSTAMP:'.$todaystamp.'
                        CREATED:'.$todaystamp.'
                        LAST-MODIFIED:'.$todaystamp.'
                        DESCRIPTION:'.$subject.'
                        SUMMARY:'.$subject.'
                        PRIORITY:5
                        CLASS:PUBLIC
                        END:VEVENT
                        END:VCALENDAR';   
                
                $message .= 'Content-Type: text/calendar;name="meeting.ics";method=REQUEST;charset=utf-8\n';
                $message .= 'Content-Type: text/calendar;name="meeting.ics";method=REQUEST\n';
                $message .= "Content-Transfer-Encoding: 8bit\n\n";
                $message .= $ical;            
                
                //SEND Email to Trainer for batch details & Block calendar
                $mail_sent = @mail( $email_2, $subject, $message, $headers );
    
            // send email  with google calender

            //if ($is_sent === true) {
                //echo "mail_sent : ".$mail_sent;
                //die;
            if($mail_sent){
                $email_msg = $subject.' ###br### '.$body;
                $send_on = date('Y-m-d h:i:s');
                Yii::$app->db->createCommand()->insert('assist_email_log', [ 'event' => 'create_module', 'sid' => $model->id, 'from_email' => $from_email['email'], 'to_email' => $to_email['email'], 'message' => $email_msg, 'cron' => '0', 'send_on' => $send_on ])->execute();
                }    
            }

//            return $this->redirect(['dv-delivery/index']);
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create_module', [ 'model' => $model, ]);
        }
    }


    // funciton for Edit Module
    public function actionEdit($id){
        //redirect a user if not super admin
        if(!Yii::$app->CustomComponents->check_permission('edit_modules')) {
            return $this->redirect(['index']);
        }

        $model = $this->findModel($id);
        $course_name = DvModuleModel::find()->where(['id'=>$model->module])->one()->module_name;   
        //Note:Here:-$course_name = $module name    
        if ($model->load(Yii::$app->request->post())){
            $data = Yii::$app->request->post();
            
            $trainer = $data['DvAssistBatches']['trainer'];
            if(empty($trainer)){
                $model->trainer = '0';
            }
            $end_date = $_POST['end_date'];
            $model->end_date = date('d-m-Y',strtotime($end_date));         
            $allsession = $data['allsession']; 
            // echo "<pre>"; print_r($allsession); die; 
            $model->save();
            if(isset($data['allsession'])){
                $cnt = 1;
                $sessions_array =  array();
                foreach($allsession as $session_value){
                    foreach($session_value as $key => $value){
                        $key = $key.''.$cnt;
                        //echo "<br>"; print_r($key);
                        $sessions_array[] = 'session'.$cnt;
                        $total_meta = Yii::$app->db->createCommand("SELECT count(*) FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key='$key' ")->queryScalar();                                                                            
                        if(empty($total_meta)){
                            Yii::$app->db->createCommand()->insert('assist_batches_meta', [ 'mid' => $model->id, 'meta_key' => $key, 'meta_value' => $value ])->execute();
                        } else {
                            Yii::$app->db->createCommand("UPDATE assist_batches_meta SET meta_value='$value' WHERE mid='$model->id' AND meta_key='$key' ")->execute();
                            //for final end date
                            Yii::$app->db->createCommand("UPDATE assist_batches_meta SET meta_value='$end_date' WHERE mid='$model->id' AND meta_key='final_end_date' ")->execute();
                        } 
                    }//Inner foreach
                    $cnt++;
                }//Outer foreach
                
                //Purpose:For reschedule : added on 21 May 2019
                if(isset($_POST['reschedule_count']) && !empty($_POST['reschedule_count']) && isset($_POST['reschedule_text']) && !empty($_POST['reschedule_text'])){
                    $count_value = $_POST['reschedule_count'];
                    $reason_value = trim($_POST['reschedule_text']);
                    Yii::$app->db->createCommand()->insert('assist_batches_reschedule', [ 'mid' => $model->id, 'reschedule_count' => $count_value, 'reschedule_text' => $reason_value ])->execute();
                }
 
                //For Delete unused sessions and times
                $new_sessions_array = array_values(array_unique($sessions_array));
               
                $total_meta = Yii::$app->db->createCommand("SELECT meta_key FROM assist_batches_meta WHERE mid = '$model->id'")->queryAll();

                $sessions_data = array();
                foreach ($total_meta as $key => $value) {
                    $sessions_data[] = $value['meta_key'];
                }
                //fetch existing records
                $sessions_includes = array();
                for($i=0 ; $i< count($sessions_data) ; $i++){
                    if(strpos($sessions_data[$i],'session') !== false  &&  strlen($sessions_data[$i]) < 10 ){
                        $sessions_includes[] =  $sessions_data[$i]; // already existing sessions data
                    }
                }
                $a = array();
                for ( $i = 0 ; $i < count($sessions_includes) ; $i++) {
                    //go inside if sessions not match with updates list nos. of sessions.
                    if(!in_array($sessions_includes[$i],$new_sessions_array)){
                        $a[] = $sessions_includes[$i];
                        Yii::$app->db->createCommand("DELETE FROM assist_batches_meta WHERE mid='$model->id' AND meta_key='$sessions_includes[$i]'")->execute();
                        $condition = '';
                        $condition = substr($sessions_includes[$i],7);
                        Yii::$app->db->createCommand("DELETE FROM assist_batches_meta WHERE mid='$model->id' AND meta_key='start_time$condition'")->execute();
                        Yii::$app->db->createCommand("DELETE FROM assist_batches_meta WHERE mid='$model->id' AND meta_key='end_time$condition'")->execute();
                    }
                }
                //End of For Delete unused sessions and times

            }//if(isset($data['allsession']))
        //Trainer Confirmation
        if(isset($data['trainer_confirm'])){
            $key = 'trainer_confirm';
            $value = $data['trainer_confirm'];
            $total_meta = Yii::$app->db->createCommand("SELECT count(*) FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key='$key' ")->queryScalar();
                if(empty($total_meta)){
                    Yii::$app->db->createCommand()->insert('assist_batches_meta', [ 'mid' => $model->id, 'meta_key' => $key, 'meta_value' => $value ])->execute();
                } else {
                    Yii::$app->db->createCommand("UPDATE assist_batches_meta SET meta_value='$value' WHERE mid='$model->id' AND meta_key='$key' ")->execute();
                }
        }
        
        //Session Feedback Link (Google)
        if(isset($data['session_link'])){
            $key = 'session_link';
            $value = $data['session_link'];
            $total_meta = Yii::$app->db->createCommand("SELECT count(*) FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key='$key' ")->queryScalar();
                if(empty($total_meta)){
                    Yii::$app->db->createCommand()->insert('assist_batches_meta', [ 'mid' => $model->id, 'meta_key' => $key, 'meta_value' => $value ])->execute();
                } else {
                    Yii::$app->db->createCommand("UPDATE assist_batches_meta SET meta_value='$value' WHERE mid='$model->id' AND meta_key='$key' ")->execute();
                }
        }

        //Session Feedback Response Link (Google)
        if(isset($data['session_res_link'])){
            $key = 'session_res_link';
            $value = $data['session_res_link'];
            $total_meta = Yii::$app->db->createCommand("SELECT count(*) FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key='$key' ")->queryScalar();
                if(empty($total_meta)){
                    Yii::$app->db->createCommand()->insert('assist_batches_meta', [ 'mid' => $model->id, 'meta_key' => $key, 'meta_value' => $value ])->execute();
                } else {
                    Yii::$app->db->createCommand("UPDATE assist_batches_meta SET meta_value='$value' WHERE mid='$model->id' AND meta_key='$key' ")->execute();
                }
        }

        //Course and Feedback Final
        if(isset($data['course_feedback'])){
            $key = 'course_feedback';
            $value = $data['course_feedback'];
            $total_meta = Yii::$app->db->createCommand("SELECT count(*) FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key='$key' ")->queryScalar();
                if(empty($total_meta)){
                    Yii::$app->db->createCommand()->insert('assist_batches_meta', [ 'mid' => $model->id, 'meta_key' => $key, 'meta_value' => $value ])->execute();
                } else {
                    Yii::$app->db->createCommand("UPDATE assist_batches_meta SET meta_value='$value' WHERE mid='$model->id' AND meta_key='$key' ")->execute();
                }
        }

        //Online Platform ID
        if(isset($data['online_platform_id'])){
            $key = 'online_platform_id';
            $value = $data['online_platform_id'];
            $total_meta = Yii::$app->db->createCommand("SELECT count(*) FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key='$key' ")->queryScalar();
                if(empty($total_meta)){
                    Yii::$app->db->createCommand()->insert('assist_batches_meta', [ 'mid' => $model->id, 'meta_key' => $key, 'meta_value' => $value ])->execute();
                } else {
                    Yii::$app->db->createCommand("UPDATE assist_batches_meta SET meta_value='$value' WHERE mid='$model->id' AND meta_key='$key' ")->execute();
                }
        }

        //Online Platform URL
        if(isset($data['online_platform_url'])){
            $key = 'online_platform_url';
            $value = $data['online_platform_url'];
            $total_meta = Yii::$app->db->createCommand("SELECT count(*) FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key='$key' ")->queryScalar();
                if(empty($total_meta)){
                    Yii::$app->db->createCommand()->insert('assist_batches_meta', [ 'mid' => $model->id, 'meta_key' => $key, 'meta_value' => $value ])->execute();
                } else {
                    Yii::$app->db->createCommand("UPDATE assist_batches_meta SET meta_value='$value' WHERE mid='$model->id' AND meta_key='$key' ")->execute();
                }
        }
        
        //Online Platform Username
        if(isset($data['online_platform_username'])){
            $key = 'online_platform_username';
            $value = $data['online_platform_username'];
            $total_meta = Yii::$app->db->createCommand("SELECT count(*) FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key='$key' ")->queryScalar();
                if(empty($total_meta)){
                    Yii::$app->db->createCommand()->insert('assist_batches_meta', [ 'mid' => $model->id, 'meta_key' => $key, 'meta_value' => $value ])->execute();
                } else {
                    Yii::$app->db->createCommand("UPDATE assist_batches_meta SET meta_value='$value' WHERE mid='$model->id' AND meta_key='$key' ")->execute();
                }
        }
        
        //Online Platform Password
        if(isset($data['online_platform_password'])){
            $key = 'online_platform_password';
            $value = $data['online_platform_password'];
            $total_meta = Yii::$app->db->createCommand("SELECT count(*) FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key='$key' ")->queryScalar();
                if(empty($total_meta)){
                    Yii::$app->db->createCommand()->insert('assist_batches_meta', [ 'mid' => $model->id, 'meta_key' => $key, 'meta_value' => $value ])->execute();
                } else {
                    Yii::$app->db->createCommand("UPDATE assist_batches_meta SET meta_value='$value' WHERE mid='$model->id' AND meta_key='$key' ")->execute();
                }
        }

        //Batch Status
        if(isset($data['batch_status'])){
            $key = 'batch_status';
            $value = $data['batch_status']; 
            if($value == ''){
                $value = '0';
            }
            $total_meta = Yii::$app->db->createCommand("SELECT count(*) FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key='$key' ")->queryScalar();
                if(empty($total_meta)){
                    Yii::$app->db->createCommand()->insert('assist_batches_meta', [ 'mid' => $model->id, 'meta_key' => $key, 'meta_value' => $value ])->execute();
                } else {
                    Yii::$app->db->createCommand("UPDATE assist_batches_meta SET meta_value='$value' WHERE mid='$model->id' AND meta_key='$key' ")->execute();
                }
        }

        //Running Batch Status
        if(isset($data['running_batch_status'])){
            $key = 'running_batch_status';
            $value = $data['running_batch_status'];
            //updated on 29 April 2019

            if($value == ''){
                $value = '2';
            }
            $total_meta = Yii::$app->db->createCommand("SELECT count(*) FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key='$key' ")->queryScalar();
                if(empty($total_meta)){
                    Yii::$app->db->createCommand()->insert('assist_batches_meta', [ 'mid' => $model->id, 'meta_key' => $key, 'meta_value' => $value ])->execute();
                } else {
                    Yii::$app->db->createCommand("UPDATE assist_batches_meta SET meta_value='$value' WHERE mid='$model->id' AND meta_key='$key' ")->execute();
                }

            // Send Email to Trainer when Batch is Completed
            $trainer_c_notify = $_POST['trainer_c_notify'];            
            $rbs_value = $data['running_batch_status'];
            if(($trainer_c_notify == '1') &&($rbs_value == '1')){
                $trainer_email = DvUsers::find()->where(['id'=>$trainer,"status" => 1])->one()->email;
                $trainer_fname = DvUsers::find()->where(['id'=>$trainer,"status" => 1])->one()->first_name;
                $trainer_lname = DvUsers::find()->where(['id'=>$trainer,"status" => 1])->one()->last_name;
                $trainer_name = $trainer_fname.' '.$trainer_lname;

            // Coordinator info
            $user_coor = DvUserMeta::find()->where(['uid' => $trainer , 'meta_key' => 'coordinator' ])->all();
            $coordinator = ArrayHelper::map($user_coor, 'uid', 'meta_value');
            if(!empty($coordinator[$trainer])){
                $coordinator_email = DvUsers::find()->where(['id'=>$coordinator[$trainer],"status" => 1])->one()->email;
                $coordinator_fname = DvUsers::find()->where(['id'=>$coordinator[$trainer],"status" => 1])->one()->first_name;
                $coordinator_lname = DvUsers::find()->where(['id'=>$coordinator[$trainer],"status" => 1])->one()->last_name;
                $coordinator_name = $coordinator_fname.' '.$coordinator_lname;
            } else {
                $coordinator_email = 'no-reply@digitalvidya.com';
                $coordinator_name = 'Coordinator';
            }
            $coordinator_phone = Yii::$app->db->createCommand("SELECT meta_value FROM assist_user_meta WHERE uid = '$coordinator[$trainer]' AND meta_key = 'phone' ")->queryOne();
             $coordinator_phone_num = $coordinator_phone['meta_value']; 

            //$course = $data['DvAssistBatches']['course'];
            $start_date = $data['DvAssistBatches']['start_date'];


            // Get Start & End dates for Google Calendar   
            
            $start_year = date('Y', strtotime($start_date));            
            $start_month = date('m', strtotime($start_date));            
            $start_day = date('d', strtotime($start_date));            
            $start_time = ' '.$data['DvAssistBatches']['stiming']; 
            $start_time_h = date('H', strtotime($start_time));            
            $start_time_m = date('i', strtotime($start_time));
            $final_end_date = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key = 'final_end_date' ")->queryOne();
            $end_date = $final_end_date['meta_value'];
            $end_year = date('Y', strtotime($end_date));            
            $end_month = date('m', strtotime($end_date));
            $end_day = date('d', strtotime($end_date));
            $end_time = ' '.$data['DvAssistBatches']['etiming'];
            $end_time_h = date('H', strtotime($end_time));            
            $end_time_m = date('i', strtotime($end_time));
            $course_id = $data['DvAssistBatches']['module'];
            //$course = DvCourse::find()->where(['id'=>$course_id])->one()->name;
            $course = DvModuleModel::find()->where(['id'=>$course_id])->one()->module_name;
            $batch_rating = $data['batch_rating'];
            $nps = $data['nps'];
            $completion_per = $data['comper'];
            $assignmentsrec = $data['assignmentsrec'];
            $sugg_feed = $data['sugg_feed'];
            $number_of_reschedul = $data['number_of_reschedul'];
            
            $subject = "Completion Report for ".$course;
            $body = " <p>Hi $trainer_name,</p>
            <br>
            <p>Sharing the batch completion report below:</p>
            <p>Batch Rating: $batch_rating</p>
            <p>NPS: $nps</p>           
            <p>Completion Percentage: $completion_per</p>
            <p>Assignment Received: $assignmentsrec </p>
            <p>Number of Reschedulings: $number_of_reschedul</p>
            <p>Suggestions: $sugg_feed</p>
            <br>
            <p>Best Wishes,</p>
            <p>$coordinator_name</p>
            <p>$coordinator_phone_num</p>";                

            /*$is_sent = Yii::$app->mailer->compose()
                ->setFrom($coordinator_email)
                ->setTo($trainer_email)
                ->setSubject($subject)
                ->setHtmlBody($body)
                ->send();*/
            /*$is_sent = Yii::$app->mailer->compose()
                ->setFrom($coordinator_email)
                ->setTo('anoops@whizlabs.com')  //neha@digitalvidya.com
                ->setSubject($subject)
                ->setHtmlBody($body)
                ->send();*/


             if ($is_sent === true){            
                $email_msg = $subject.' ###br### '.$body;
                $event = $subject;
                $send_on = date('Y-m-d h:i:s');
                Yii::$app->db->createCommand()->insert('assist_email_log', [ 'event' => $event, 'sid' => $model->id, 'from_email' => $coordinator_email, 'to_email' => $trainer_email, 'message' => $email_msg , 'cron' => '0', 'send_on' => $send_on ])->execute();
                }
            }    
        }

        //Send details to Trainer by Email
        //trainer_cordi_notify
        $trainer_cordi_notify = $_POST['trainer_cordi_notify'];
        if($trainer_cordi_notify == '1'){
            // Coordinator info
            $user_coor = DvUserMeta::find()->where(['uid' => $trainer , 'meta_key' => 'coordinator' ])->all();
            $coordinator = ArrayHelper::map($user_coor, 'uid', 'meta_value');
            if(!empty($coordinator[$trainer])){
                $coordinator_email = DvUsers::find()->where(['id'=>$coordinator[$trainer],"status" => 1])->one()->email;
                $coordinator_fname = DvUsers::find()->where(['id'=>$coordinator[$trainer],"status" => 1])->one()->first_name;
                $coordinator_lname = DvUsers::find()->where(['id'=>$coordinator[$trainer],"status" => 1])->one()->last_name;
                $coordinator_name = $coordinator_fname.' '.$coordinator_lname;
            } else {
                $coordinator_email = 'no-reply@digitalvidya.com';
                $coordinator_name = 'Coordinator';
            }
            $coordinator_phone = Yii::$app->db->createCommand("SELECT meta_value FROM assist_user_meta WHERE uid = '$coordinator[$trainer]' AND meta_key = 'phone' ")->queryOne();
             $coordinator_phone_num = $coordinator_phone['meta_value']; 

            // trainer info
            $trainer_email = DvUsers::find()->where(['id'=>$trainer,"status" => 1])->one()->email;
            $trainer_fname = DvUsers::find()->where(['id'=>$trainer,"status" => 1])->one()->first_name;
            $trainer_lname = DvUsers::find()->where(['id'=>$trainer,"status" => 1])->one()->last_name;
            $trainer_name = $trainer_fname.' '.$trainer_lname;

            $course_id = $data['DvAssistBatches']['module'];
            $course = DvModuleModel::find()->where(['id'=>$course_id])->one()->module_name;
            $start_date = $data['DvAssistBatches']['start_date'];
            $start_time = ' '.$data['DvAssistBatches']['stiming']; 
            $end_time = ' '.$data['DvAssistBatches']['etiming'];
            $session_link = $data['session_link'];
            $session_res_link = $data['session_res_link'];
            $joining_link = $data['online_platform_url'];
            $username = $data['online_platform_username'];
            $password = $data['online_platform_password'];
            
            /*$final_end_date = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key = 'final_end_date' ")->queryOne();
                $end_date = $final_end_date['meta_value'];*/

            $subject = 'Batch Details for ####SessionNumber#### | '.$course;
            //$subject = $course.' | '.$start_date .' | Module/Batch invitation';
            $body = "<p>Hi $trainer_name,</p>
            <br>
            <p>Sharing the details below for ####SessionNumber####.</p>
            <br>
            <p>Session Date: ####SessionDate####</p>
            <p>Session Timings: $start_time to $end_time</p>
            <br>
            <p>Joining Link: $joining_link</p>
            <p>Username: $username</p>
            <p>Password: $password</p>
            <br>
            <p>Feedback Link: $session_link</p>
            <p>Feedback Response: $session_res_link</p>
            <br>
            <p>Best Wishes,</p>
            <p>$coordinator_name</p>
            <p>$coordinator_phone_num</p>";

            $email = "anoops@whizlabs.com";
            //$email = "neha@digitalvidya.com";

            /*$is_sent = Yii::$app->mailer->compose()
                ->setFrom($coordinator_email)
                ->setTo($email) //$trainer_email
                ->setSubject($subject)
                ->setHtmlBody($body)
                ->send();*/

    //if($is_sent === true){    
        // inseret email log into DB to send using cron job 
        $email_msg = $subject.' ###br### '.$body; 
        // delete previous records with same id
         Yii::$app->db->createCommand("DELETE FROM assist_email_log WHERE sid = '$model->id' AND cron = '2' ")->execute();      
        $allsession = $data['allsession'];
         foreach($allsession as $key => $value){
            $new_key = str_replace("session","Session ",$key);
                if (strpos($new_key, 'rec') !== false){
                } else {
//              $body .= "<p>".$new_key.": <strong>".$value."</strong></p>";
                $email_msg_1 = str_replace("####SessionNumber####",$new_key,$email_msg);
                $email_msg_2 = str_replace("####SessionDate####",$value['session'],$email_msg_1);
            
                $new_value = date('Y-m-d', strtotime($value['session']));                
                $new_date = strtotime($new_value . ' -1 day');                
                $send_on = date('Y-m-d h:i:s', $new_date);
                $event = 'Batch Details for '.$new_key.' | '.$course;

            $today = date("Y-m-d");
            #$yester_day = = strtotime($today . ' -1 day');
            #$yesterday = date('Y-m-d', $yester_day);
            if ($send_on > $today){ // check the email send date with current date
                //echo 'working'; die();
                

                // insert new records
                Yii::$app->db->createCommand()->insert('assist_email_log', [ 'event' => $event, 'sid' => $model->id, 'from_email' => $coordinator_email, 'to_email' => $trainer_email, 'message' => $email_msg_2, 'cron' => '2', 'send_on' => $send_on ])->execute();
            }

                }
            }

    //  }
    } // end of main condition trainer notify

    //Batch Rating
    if(isset($data['batch_rating'])){
        $key = 'batch_rating';
        $value = $data['batch_rating'];
        $total_meta = Yii::$app->db->createCommand("SELECT count(*) FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key='$key' ")->queryScalar();
            if(empty($total_meta)){
                Yii::$app->db->createCommand()->insert('assist_batches_meta', [ 'mid' => $model->id, 'meta_key' => $key, 'meta_value' => $value ])->execute();
            } else {
                Yii::$app->db->createCommand("UPDATE assist_batches_meta SET meta_value='$value' WHERE mid='$model->id' AND meta_key='$key' ")->execute();
            }
    }

    //Completion Percentage 
    if(isset($data['comper'])){
        $key = 'comper';
        $value = $data['comper'];
        $total_meta = Yii::$app->db->createCommand("SELECT count(*) FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key='$key' ")->queryScalar();
            if(empty($total_meta)){
                Yii::$app->db->createCommand()->insert('assist_batches_meta', [ 'mid' => $model->id, 'meta_key' => $key, 'meta_value' => $value ])->execute();
            } else {
                Yii::$app->db->createCommand("UPDATE assist_batches_meta SET meta_value='$value' WHERE mid='$model->id' AND meta_key='$key' ")->execute();
            }
    }

    //NPS
    if(isset($data['nps'])){
        $key = 'nps';
        $value = $data['nps'];
        $total_meta = Yii::$app->db->createCommand("SELECT count(*) FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key='$key' ")->queryScalar();
            if(empty($total_meta)){
                Yii::$app->db->createCommand()->insert('assist_batches_meta', [ 'mid' => $model->id, 'meta_key' => $key, 'meta_value' => $value ])->execute();
            } else {
                Yii::$app->db->createCommand("UPDATE assist_batches_meta SET meta_value='$value' WHERE mid='$model->id' AND meta_key='$key' ")->execute();
            }
    }

    //Assignments Received
    if(isset($data['assignmentsrec'])){
        $key = 'assignmentsrec';
        $value = $data['assignmentsrec'];
        $total_meta = Yii::$app->db->createCommand("SELECT count(*) FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key='$key' ")->queryScalar();
            if(empty($total_meta)){
                Yii::$app->db->createCommand()->insert('assist_batches_meta', [ 'mid' => $model->id, 'meta_key' => $key, 'meta_value' => $value ])->execute();
            } else {
                Yii::$app->db->createCommand("UPDATE assist_batches_meta SET meta_value='$value' WHERE mid='$model->id' AND meta_key='$key' ")->execute();
            }
    }

     

    //Suggestions/feedback
    if(isset($data['sugg_feed'])){
        $key = 'sugg_feed';
        $value = $data['sugg_feed'];
        $total_meta = Yii::$app->db->createCommand("SELECT count(*) FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key='$key' ")->queryScalar();
            if(empty($total_meta)){
                Yii::$app->db->createCommand()->insert('assist_batches_meta', [ 'mid' => $model->id, 'meta_key' => $key, 'meta_value' => $value ])->execute();
            } else {
                Yii::$app->db->createCommand("UPDATE assist_batches_meta SET meta_value='$value' WHERE mid='$model->id' AND meta_key='$key' ")->execute();
            }
    }


        $all_sessions = $data['all_sessions'];
        Yii::$app->db->createCommand("UPDATE assist_batches_meta SET meta_value='$all_sessions' WHERE mid='$model->id' AND meta_key='all_sessions' ")->execute();
         
        


        Yii::$app->session->setFlash('success','Module updated successfully');

        if(Yii::$app->CustomComponents->check_permission('all_users')){
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->redirect(['index']);
            }

        } else {
            return $this->render('edit', [ 'model' => $model, 'dvcourse_name' => $course_name ]);
        }

    }//End of edit()//


// code commented due to some changes.
/*     public function actionTraining_topics(){ 
        //redirect a user if not super admin
        if(!Yii::$app->CustomComponents->check_permission('topics')) {
            return $this->redirect(['index']);
        }

        $model = new DvTrainingTopics();

        if ($model->load(Yii::$app->request->post()) && $model->save()){
            Yii::$app->session->setFlash('success','New Training Topics Created Successfully');
            return $this->redirect(['dv-delivery/training_topics']);
        } else {
            return $this->render('training_topics', [ 'model' => $model, ]);
        }
    } */

   // ajax call
   /*public function actionTrainingtopics(){ 
        //redirect a user if not super admin
         if(isset($_POST['course_id']) != 0){
            $course_id = $_POST['course_id'];
            $output = '';
            $output .= '<select id="dvassistbatches-training_topic" class="form-control" name="DvAssistBatches[training_topic]" required="required" aria-required="true" aria-invalid="false">';
            $output .= '<option value="prompt">Select Training Topic</option>';

            $TrainingTopics = DvTrainingTopics::find()->where(['status'=>1,'cid'=>$course_id])->all();
            $DvTrainingTopics = ArrayHelper::map($TrainingTopics, 'id', 'name');
            foreach($DvTrainingTopics as $key => $value){
                $output .= '<option value="'.$key.'">'.$value.'</option>';
            }
            $output .= '</select>';
            return $output;
         } else {
            return $this->redirect(['dv-delivery/index']);
         }
    } */

    // ajax call to list trainer while creating/ updating the Module
   public function actionList_trainer(){ 
        //redirect a user if not super admin
         if(isset($_POST['course_id']) != 0){
            $course_id = $_POST['course_id'];
            $output = '';
            $output .= '<select id="dvassistbatches-trainer" class="form-control" name="DvAssistBatches[trainer]" title="Select Trainer" required="required" data-toggle="tooltip" data-placement="top" data-original-title="Select Trainer">';
            $output .= '<option value="">Select Trainer</option>';

            $connection = Yii::$app->getDb();

            $command = $connection->createCommand("select assist_users.id, assist_users.first_name, assist_users.last_name, assist_users.course, assist_users.department from assist_users LEFT JOIN assist_user_meta ON assist_users.id = assist_user_meta.uid AND assist_users.status = 1 AND assist_user_meta.meta_key = 'role' AND assist_user_meta.meta_value = '4' ");

            $enrolled_users_arr = $command->queryAll();

            if (!empty($enrolled_users_arr)){
                foreach ($enrolled_users_arr as $enroll_user){
                    if($enroll_user['department'] == '7'){
                        $course = explode(',', $enroll_user['course']);
                        $inarray = array_search($course_id,$course);
                        if(!empty($inarray) || ($inarray === 0)){
                            $name = $enroll_user['first_name'].' '.$enroll_user['last_name'];
                            $output .= '<option value="'.$enroll_user['id'].'">'.$name.'</option>';
                        }
                    }
                }
            }
            $output .= '</select>';
            return $output;
         } else {
            return $this->redirect(['dv-delivery/index']);
         }
    }

   // Ajax call to list the Delivery Person while creating/ updating module
   public function actionDelivery_person(){ 
        //redirect a user if not super admin
         if(isset($_POST['trainer_id']) != 0){
            $trainer_id = $_POST['trainer_id'];
            $output = '';
            $output .= '<select id="dvassistbatches-coordinator" class="form-control" name="DvAssistBatches[coordinator]" title="Select Co-ordinator Person" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Select Delivery Person" aria-invalid="true">';
            //$output .= '<option value="">Select Delivery Person'.$trainer_id.'</option>';

            // Coordinator
            $user_coor = DvUserMeta::find()->where(['uid' => $trainer_id , 'meta_key' => 'coordinator' ])->all();
            $coordinator = ArrayHelper::map($user_coor, 'uid', 'meta_value');
            if(!empty($coordinator[$trainer_id])){
                $cid = DvUsers::find()->where(['id'=>$coordinator[$trainer_id],"status" => 1])->one()->id;
                $output .= '<option value="'.$cid.'">';
                $output .= DvUsers::find()->where(['id'=>$coordinator[$trainer_id],"status" => 1])->one()->first_name;
                $output .=  ' ';
                $output .= DvUsers::find()->where(['id'=>$coordinator[$trainer_id],"status" => 1])->one()->last_name;
                $output .= '</option>';
            } else {
                $output .= '<option value="">Select Co-ordinator Person</option>';
            }

            /*$connection = Yii::$app->getDb();
            $command = $connection->createCommand("select id, first_name, last_name, course from assist_users where status = 1 AND department = 2");
            $enrolled_users_arr = $command->queryAll();
            if (!empty($enrolled_users_arr)){
                foreach ($enrolled_users_arr as $enroll_user){
                    $course = explode(',', $enroll_user['course']);
                    $inarray = array_search($course_id,$course); 
                    if(!empty($inarray) || ($inarray === 0) ){
                        $name = $enroll_user['first_name'].' '.$enroll_user['last_name'];
                        $output .= '<option value="'.$enroll_user['id'].'">'.$name.'</option>';
                    }
                }
            }*/
            $output .= '</select>';
            return $output;
         } else {
            return $this->redirect(['dv-delivery/index']);
         }
    }    


   // ajax call to list days while creating  a module
   public function actionGet_day(){ 
        //redirect a user if not super admin
         if(isset($_POST['sdate']) != 0){
            $sdate = $_POST['sdate'];
            $F_date = date('l', strtotime($sdate));
            $S_date = date('D', strtotime($sdate));
            $S_date = strtolower($S_date);

            $output = '';
            $output .= '<select id="dvassistbatches-day" class="form-control" name="DvAssistBatches[day]" title="Select Day" required="required" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Select Day" aria-invalid="true">';

            if($F_date == 'Tuesday'){
                $output .= '<option value="">Select Day</option>';
            }
            
            $output .= '<option value="'.$S_date.'">'.$F_date.'</option>';
            if($F_date == 'Tuesday'){
                $output .= '<option value="tue-thu">Tuesday - Thursday</option>';
            }           
            $output .= '</select>';
            return $output;
         } else {
            return $this->redirect(['dv-delivery/index']);
         }
    }

   // ajax call to list weeks while creating a new module
   public function actionGet_weeks(){ 
        //redirect a user if not super admin
         if(isset($_POST['day_id']) != 0){
            $output = '';
            $output .= '<select id="dvassistbatches-type" class="form-control" name="DvAssistBatches[type]" title="Select Number of Session" required="required" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Select Number of Session" aria-invalid="true">';
            $output .= '<option value="">Select Number of Session</option>';
                for ($x = 1; $x <= 25; $x++){
                    $output .= '<option value="'.$x.'">'.$x.' Session</option>';
                }
            $output .= '</select>';
            return $output;
         } else {
            return $this->redirect(['dv-delivery/index']);
         }
    }    

  // ajax call to list sessions
   public function actionGet_session(){ 
        //redirect a user if not super admin
         if(isset($_POST['type_id']) != 0){
            $type_id = $_POST['type_id'];
            if(isset($_POST['module_id']) != ''){
                $module_id = $_POST['module_id'];
            }else {
                $module_id = '';
            }
            $start_date = $_POST['start_date'];
            //Added on 19 april 2019
            $start_stime = $_POST['start_stime'];
            $end_etime = $_POST['end_etime'];
            $time_duration = $_POST['time_duration'];
            if(isset($_POST['Sday'])){
                $Sday = $_POST['Sday'];
            } else {
                $Sday = '';
            }
            
            $nuofwe = rtrim($type_id,'w');

            $output = '';
            $output .= '<div id="TextBoxesGroup">';
            if($Sday =='tue-thu'){
                $nuofsession = $nuofwe + $nuofwe;
            } else {
                $nuofsession = $nuofwe;
            }
            $output .= '<div class="form-group"><h3 class="blue_color" id="total_session" data-total-session="'.$nuofsession.'">No. of Sessions: '.$nuofsession.' </h3>';
            $output .= '<input id="all_sessions" name="all_sessions" value="'.$nuofsession.'" type="hidden">';
            $output .= '</div>';
            $count = 0;
            $icount = 1;
            for($i = 1; $i<=$nuofwe; $i++){
                if($count == 0){
                    $N_date = $start_date;
                    $readonly = 'readonly="readonly"';
                } else {
                    $N_date = date('d-m-Y', strtotime('+'.$count.' week', strtotime($start_date)));
                    $readonly = '';
                    $time_dur = '';
                }
                $output .= '<div id="TextBoxDiv'.$icount.'" class="row">';
                if($icount == '1'){
                    $session_tooltip = 'data-toggle="tooltip" data-placement="top" data-original-title="Update Session 1 from Start Date" readonly="readonly" ';
                } else {
                    //$session_tooltip = '';
                    $session_tooltip = 'data-toggle="tooltip" data-placement="top" title="Select Date"';
                }

                $start_time_toolkit = 'data-toggle="tooltip" data-placement="top" data-original-title="Start Date"';
                $end_time_toolkit = 'data-toggle="tooltip" data-placement="top" data-original-title="End Date"';

                $output .= '<div class="form-group col-md-1 blank"><button type="button" class="btn btn-warning"><span class="badge">'.$icount.'</span></button></div>';
                
                $output .= '<div class="form-group col-md-2 session'.$icount.' ">';
                $output .= '<input class="form-control" id="session_'.$icount.'" autocomplete="off" required="required" name="allsession[session'.$icount.'][session]" placeholder="Session '.$icount.' Date" value="'.$N_date.'" type="text" '.$session_tooltip.'>';
                $output .= '</div>';
                //for start time
                $output .= '<div class="form-group col-md-2 session_time'.$icount.' ">';
                $output .= '<input class="form-control" id="session_stime'.$icount.'" required="required" name="allsession[session'.$icount.'][start_time]" placeholder="Start Time" autocomplete="off" '.$start_time_toolkit.' type="text" '.$readonly.' value="'.$start_stime.'" >';
                $output .= '</div>';
                //for end time
                $output .= '<div class="form-group col-md-2 session_time'.$icount.' ">';
                $output .= '<input class="form-control" id="session_etime'.$icount.'" required="required" name="allsession[session'.$icount.'][end_time]" placeholder="End Time" '.$end_time_toolkit.' autocomplete="off" type="text"  '.$readonly.' value="'.$end_etime.'" >';
                $output .= '</div>';
                $output .= '<div class="form-group col-md-1 blank">';
                $output .= '<button id="reschedule" type="button" class="btn btn-success" data-current="'.$icount.'" >Rescheduleing</button></div><div class="hide" data-totalsession="1">';
                $output .= '</div></div>';

                if($Sday =='tue-thu'){
                    $icount = $icount+1;
                    $session_url = $this->session_url($module_id,$icount);
                    $Nstart_date = date('Y-m-d', strtotime($start_date. ' + 2 days'));
                    $N_date = date('d-m-Y', strtotime('+'.$count.' week', strtotime($Nstart_date)));   
                    if($icount == '1'){
                        $session_tooltip = 'data-toggle="tooltip" data-placement="top" data-original-title="Update Session 1 from Start Date" readonly="readonly" ';
                    } else {
                        $session_tooltip = 'data-toggle="tooltip" data-placement="top" title="Select Date"';
                    }
                    $output .= '<div id="TextBoxDiv'.$icount.'" class="row">';
                    $output .= '<div class="form-group col-md-1 blank"><button type="button" class="btn btn-warning"><span class="badge">'.$icount.'</span></button></div>';

                    $output .= '<div class="form-group col-md-2 session'.$icount.' ">';
                    $output .= '<input class="form-control" id="session_'.$icount.'" required="required" name="allsession[session'.$icount.'][session]" autocomplete="off" placeholder="Session '.$icount.' Date" value="'.$N_date.'" type="text" '.$session_tooltip.'>';
                    $output .= '</div>';

                    //For Start time
                    $output .= '<div class="form-group col-md-2 session_time'.$icount.' ">';
                    $output .= '<input class="form-control" id="session_stime'.$icount.'" required="required" name="allsession[session'.$icount.'][start_time]" placeholder="Start Time" autocomplete="off" value="'.$start_stime.'" type="text" data-toggle="tooltip" data-placement="top" title="Select Start Time" >';
                    $output .= '</div>';

                    //For End time
                    $output .= '<div class="form-group col-md-2 session_time'.$icount.' ">';
                    $output .= '<input class="form-control" id="session_etime'.$icount.'" required="required" name="allsession[session'.$icount.'][end_time]" placeholder="End Time" autocomplete="off" type="text" value="'.$end_etime.'" data-toggle="tooltip" data-placement="top" title="Select End Time">';
                    $output .= '</div>';

                    $output .= '</div>';
                }
                $count++;
                $icount++;
            }
            $output .= '<div class="hide" data-totalsession="'.$nuofsession.'"></div>';
            $output .= '<input name="fend_date" id="fend_date" type="hidden" value="'.$N_date.'">';
            $output .= '<input name="edit_module_form" id="edit_module_form" value="'.$module_id.'" type="hidden">';
            $output .= '</div>';
            return $output;
         } else {
            return $this->redirect(['dv-delivery/index']);
         }
}

   // ajax call to get the end date of a session
   public function actionGet_session_enddate(){ 
        //redirect a user if not super admin
         if(isset($_POST['type_id']) != 0){
            $type_id = $_POST['type_id'];
            if(isset($_POST['module_id']) != ''){
                $module_id = $_POST['module_id'];
            }else {
                $module_id = '';
            }
            $start_date = $_POST['start_date'];
            if(isset($_POST['Sday'])){
                $Sday = $_POST['Sday'];
            } else {
                $Sday = '';
            }
            
            $nuofwe = rtrim($type_id,'w');

            $output = '';            
            if($Sday =='tue-thu'){
                $nuofsession = $nuofwe + $nuofwe;
            } else {
                $nuofsession = $nuofwe;
            }            

            $count = 0;
            $icount = 1;
            for($i = 1; $i<=$nuofwe; $i++){
                if($count == 0){
                    $N_date = $start_date;
                } else {
                    $N_date = date('d-m-Y', strtotime('+'.$count.' week', strtotime($start_date)));   
                }

                if($Sday =='tue-thu'){
                    $icount = $icount+1;
                    $Nstart_date = date('Y-m-d', strtotime($start_date. ' + 2 days'));
                    $N_date = date('d-m-Y', strtotime('+'.$count.' week', strtotime($Nstart_date)));
                }
                $count++;
                $icount++;
            }            
            
            $output .= '<input id="module_end_date" class="form-control hasDatepicker" title="" placeholder="End Date" data-toggle="tooltip" readonly="readonly" data-placement="top" aria-required="true" data-original-title="End Date" aria-invalid="false" value="'.$N_date.'">';
            
            return $output;
         } else {
            return $this->redirect(['dv-delivery/index']);
         }
    }

   // ajax call for based on Rescheduleing
    /*public function actionRescheduleing(){ 
        //redirect a user if not super admin
         if(isset($_POST['current_id']) != 0){
            $current_id = $_POST['current_id'];
            $module_id = $_POST['module_id'];
            $total_session = $_POST['total_session'];
            $event_date = $_POST['event_date'];
            $total_sessions = $total_session - $current_id;
            $output = '';
            $count = 0;
            $icount = $current_id;
            for($i = 0; $i<=$total_sessions; $i++){
                $N_date = date('d-m-Y', strtotime('+'.$count.' week', strtotime($event_date)));
                
                $output .= '<div id="TextBoxDiv'.$icount.'">';
                $output .= '<div class="form-group col-md-1 blank"><button type="button" class="btn btn-warning"><span class="badge">'.$icount.'</span></button></div>';
                $output .= '<div class="form-group col-md-4">';
                $output .= '<input class="form-control" id="session_'.$icount.'" required="required" name="allsession[session'.$icount.']" placeholder="Session '.$icount.' Date" value="'.$N_date.'" type="text">';
                $output .= '</div>';
                $output .= '<div class="form-group col-md-4">';
                $session_url = $this->session_url($module_id,$icount);
                $output .= '<input class="form-control" name="allsession[session'.$icount.'rec]" placeholder="Session '.$icount.' Recording URL" type="text" value="'.$session_url.'">';
                $output .= '</div><div class="form-group col-md-3 blank">';
                $output .= '<button id="reschedule" type="button" class="btn btn-success" data-current="'.$icount.'" >Rescheduleing</button></div><div class="hide" data-totalsession="1"></div>';
                $count++;
                $icount++;
            }
            $output .= '<input name="fend_date" id="fend_date" type="hidden" value="'.$N_date.'">';
            return $output;
         } else {
            return $this->redirect(['dv-delivery/index']);
         }
    }   */       

  // ajax call for add new session
   public function actionAdd_session(){ 
        //redirect a user if not super admin
         if(isset($_POST['counter']) != 0){
            $counter = $_POST['counter'];
            $pre_date = $_POST['pre_date'];
            $start_stime = $_POST['start_stime'];
            $end_etime = $_POST['end_etime'];
             
            if(isset($_POST['module_id']) != ''){
                $module_id = $_POST['module_id'];
            }else {
                $module_id = '';
            }
            $output = '';
            $session_url = $this->session_url($module_id,$counter);
            $Next_date = date('d-m-Y', strtotime('+1 week', strtotime($pre_date)));
            $output .= '<div class="form-group col-md-1 blank">
                            <button type="button" class="btn btn-warning"><span class="badge">'.$counter.'</span></button>
                        </div>
                        <div class="form-group col-md-2 session'.$counter.' ">
                            <input class="form-control" id="session_'.$counter.'" required="required" name="allsession[session'.$counter.'][session]" autocomplete="off" type="text" value="'.$Next_date.'" data-toggle="tooltip" data-placement="top" title="Select Date" placeholder="Session '.$counter.' Date">
                        </div>
                        <div class="form-group col-md-2 session_time'.$counter.' ">
                            <input class="form-control" id="session_stime'.$counter.'" required="required" name="allsession[session'.$counter.'][start_time]" placeholder="Start Time" type="text" value="'.$start_stime.'" autocomplete="off" data-toggle="tooltip" data-placement="top" title="Start Time">
                        </div>
                        <div class="form-group col-md-2 session_time'.$counter.' ">
                            <input class="form-control" id="session_etime'.$counter.'" required="required" name="allsession[session'.$counter.'][end_time]" placeholder="End Time" type="text" value="'.$end_etime.'" autocomplete="off" data-toggle="tooltip" data-placement="top" title="End Time">
                        </div>
                        <div class="form-group col-md-4 recording_url'.$counter.' ">
                            <input class="form-control" id="recording_url'.$counter.'"  name="allsession[session'.$counter.'][recording_url]" placeholder="Recording URL" type="text" value="" autocomplete="off" data-toggle="tooltip" data-placement="top" title="Recording URL">
                        </div>
                        <div class="hide" data-totalsession="1"></div>';

            $output .= '<input name="fend_date" id="fend_date" type="hidden" value="'.$Next_date.'">';
            return $output;
         } else {
            return $this->redirect(['dv-delivery/index']);
         }
    }

   // Ajax call to add the session end date
   public function actionAdd_session_enddate(){ 
        //redirect a user if not super admin
         if(isset($_POST['counter']) != 0){
            $counter = $_POST['counter'];
            $pre_date = $_POST['pre_date'];
            if(isset($_POST['module_id']) != ''){
                $module_id = $_POST['module_id'];
            }else {
                $module_id = '';
            }
            $output = '';
            $session_url = $this->session_url($module_id,$counter);
            $Next_date = date('d-m-Y', strtotime('+1 week', strtotime($pre_date)));            
            $output .= '<input id="module_end_date" class="form-control hasDatepicker" title="" placeholder="End Date" data-toggle="tooltip" readonly="readonly" data-placement="top" aria-required="true" data-original-title="End Date" aria-invalid="false" value="'.$Next_date.'">';
            return $output;
         } else {
            return $this->redirect(['dv-delivery/index']);
         }
    } 

   // ajax call to add sessoin for second case
   public function actionAdd_session2(){ 
        //redirect a user if not super admin
         if(isset($_POST['counter']) != 0){
            $counter = $_POST['counter']+1;
            $pre_date = $_POST['pre_date'];
            $start_stime = $_POST['start_stime'];
            $end_etime = $_POST['end_etime'];
			if(isset($_POST['module_id']) != ''){
                $module_id = $_POST['module_id'];
            }else {
                $module_id = '';
            }
            $output = '';
            $Next_date = date('d-m-Y', strtotime('+1 week', strtotime($pre_date)));
            $Nstart_date = date('d-m-Y', strtotime($Next_date. ' - 2 days'));
            $session_url = $this->session_url($module_id,$counter);

            $output .= '<div id="TextBoxDiv'.$counter.'" class="row">';

            $output .= '<div class="form-group col-md-1 blank"><button type="button" class="btn btn-warning"><span class="badge">'.$counter.'</span></button></div>';
            $output .= '<div class="form-group col-md-2 session'.$counter.' "><input class="form-control" id="session_'.$counter.'" required="required" name="allsession[session'.$counter.'][session]" type="text" value="'.$Nstart_date.'" autocomplete="off" placeholder="Session '.$counter.' Date"> </div>';
            //For Start time
            $output .= '<div class="form-group col-md-2 session_time'.$counter.' ">';
            $output .= '<input class="form-control" id="session_stime'.$counter.'" required="required" name="allsession[session'.$counter.'][start_time]" placeholder="Start Time" autocomplete="off" value="'.$start_stime.'" type="text" data-toggle="tooltip" data-placement="top" title="Select Start Time" >';
            $output .= '</div>';
            //For End time
            $output .= '<div class="form-group col-md-2 session_time'.$counter.' ">';
            $output .= '<input class="form-control" id="session_etime'.$counter.'" required="required" name="allsession[session'.$counter.'][end_time]" placeholder="End Time" autocomplete="off" type="text" value="'.$end_etime.'" data-toggle="tooltip" data-placement="top" title="Select End Time">';
            $output .= '</div>';
            //For Recodring URL 22 May 2019
            $output .= '<div class="form-group col-md-4 recording_url'.$counter.' ">';
            $output .= '<input class="form-control" id="recording_url'.$counter.'"   name="allsession[session'.$counter.'][recording_url]" placeholder="Recording URL" autocomplete="off" type="text" value="" data-toggle="tooltip" data-placement="top" title="Recording URL">';
            $output .= '</div>';

            $output .= '</div>';
            //-----------For Second part-----------//
            $counter2 = $counter+1;
            $output .= '<div id="TextBoxDiv'.$counter2.'" class="row">';
            $output .= '<div class="form-group col-md-1 blank"><button type="button" class="btn btn-warning"><span class="badge">'.$counter2.'</span></button></div>';

            $output .= '<div class="form-group col-md-2"><input class="form-control" id="session_'.$counter2.'" required="required" name="allsession[session'.$counter2.'][session]" type="text" value="'.$Next_date.'" placeholder="Session '.$counter2.' Date"> </div>';

            //For Start time
            $output .= '<div class="form-group col-md-2 session_time'.$counter2.' ">';
            $output .= '<input class="form-control" id="session_stime'.$counter2.'" required="required" name="allsession[session'.$counter2.'][start_time]" placeholder="Start Time" autocomplete="off" value="'.$start_stime.'" type="text" data-toggle="tooltip" data-placement="top" title="Select Start Time" >';
            $output .= '</div>';
            //For End time
            $output .= '<div class="form-group col-md-2 session_time'.$counter2.' ">';
            $output .= '<input class="form-control" id="session_etime'.$counter2.'" required="required" name="allsession[session'.$counter2.'][end_time]" placeholder="End Time" autocomplete="off" type="text" value="'.$end_etime.'" data-toggle="tooltip" data-placement="top" title="Select End Time">';
            $output .= '</div>';

            //For Recording URL
            $output .= '<div class="form-group col-md-4 recording_url'.$counter2.' ">';
            $output .= '<input class="form-control" id="recording_url'.$counter2.'" name="allsession[session'.$counter2.'][recording_url]" placeholder="Recording URL" autocomplete="off" type="text" value="" data-toggle="tooltip" data-placement="top" title="Recording URL">';
            $output .= '</div>';
             
            $output .='<div class="hide" data-totalsession="1"></div>'; 
            $output .= '<input name="fend_date" id="fend_date" type="hidden" value="'.$Next_date.'">';
            $output .= '</div>';
            return $output;
         } else {
            return $this->redirect(['dv-delivery/index']);
         }
    } 

    // function to edit the course
    public function actionEdit_course($id){
        //redirect a user if not super admin
        if(!Yii::$app->CustomComponents->check_permission('edit_course')) {
            return $this->redirect(['create_course']);
        }

       $model=DvCourse::findOne($id);
       if(empty($model)){
            return $this->redirect(['create_course']);
       }

        if ($model->load(Yii::$app->request->post())){
             $model->save();
            Yii::$app->session->setFlash('success','User Course Updated Successfully');
                return $this->redirect(['create_course']);
        } else {
            return $this->render('edit_course', ['model' => $model]);

        }
    }

// code commented due to some updates
    /*public function actionEdit_topic($id){
        //redirect a user if not super admin
        if(!Yii::$app->CustomComponents->check_permission('edit_topics')) {
            return $this->redirect(['training_topics']);
        }

       $model = DvTrainingTopics::findOne($id);
       if(empty($model)){
            return $this->redirect(['training_topics']);
       }

        if ($model->load(Yii::$app->request->post())){
             $model->save();
            Yii::$app->session->setFlash('success','Training Topics Updated Successfully');
                return $this->redirect(['training_topics']);
        } else {
            return $this->render('edit_topic', ['model' => $model]);

        }
    }*/


    // batch validation based on the dates. this code check the Trainer is busy for selected dates or not.
    public function actionBatch_validate(){
        if(isset($_POST) != 0){
            //var_dump($_POST);
            $output = '';
            $finaloutput = '0';
            $DvAssistBatches = $_POST["DvAssistBatches"];
            $trainer = $DvAssistBatches['trainer'];
            $new_stiming = $DvAssistBatches['stiming'];
            $new_etiming = $DvAssistBatches['etiming'];
            //$start_date = $DvAssistBatches['start_date'];
            $total_sessions = $_POST['all_sessions'];

            $allsession = $_POST['allsession'];

            $module_id = '';
            if(isset($_POST['edit_module_form'])){
                $module_id = $_POST['edit_module_form'];    
            }
            
           

            for($i = 1; $i<=$total_sessions; $i++){
                    $key = 'session'.$i;
                    $start_date= $allsession[$key]['session'];
                    
                    $check_session_date = $this->check_session_date($trainer,$new_stiming,$new_etiming,$start_date,$module_id);
                    if($check_session_date == '0'){
                        $output .= '<div class="session_error">This Trainer is already Busy on Date ';
                        $output .= $start_date.' between ';
                        $output .= $new_stiming.' to '.$new_etiming;
                        $output .= ' for another Module</div>';
                         break;
                    } elseif($check_session_date == '1'){
                        $finaloutput = '1';
                    }
                    //$output .= $check_session_date.'<br>';
                }

                if($finaloutput == '1'){
                    $output .= '1';
                }

            /*if($check_session_date == '0'){
                $output .= '<div class="session_error">This Trainer is already Busy on Date ';
                $output .= $start_date.' from ';
                $output .= $new_stiming.' to '.$new_etiming;
                $output .= ' for another Module</div>';
            } else {
                //$output .= '1';
                for($i = 1; $i<=$total_sessions; $i++){
                    $key = 'session'.$i;
                    $output .= $allsession[$key].'<br>';
                }
            }*/
           
            return $output;
         } else {
            return $this->redirect(['dv-delivery/index']);
         }
    }

    // ajax call will check the dates while updating the date of Module
    public function actionCheck_date(){
        if(isset($_POST) != 0){
            $module_id = $_POST['module_id'];
            $current_date = $_POST['current_date'];
            //echo $module_id.' '.$current_date;
            $meta_value = Yii::$app->db->createCommand("SELECT id FROM assist_batches_meta WHERE mid = '$module_id' AND meta_value = '$current_date' ")->queryOne();
            if(empty($meta_value)){
                $output = '1';
            } else {
                $output = '0';
            }
            return $output;
        }
    }

    // Cron Job to send Sessions Emails to Trainer befor a Day of event
    public function actionCronjobs(){
        
        $emails_value = Yii::$app->db->createCommand("SELECT * FROM assist_email_log WHERE cron = '2'")->queryAll();

        foreach($emails_value as $value){
            $id = $value['id'];
            $message_data = $value['message'];
            $message_val = explode('###br###', $message_data);
            $subject = $message_val[0];
            $body = $message_val[1];
            $coordinator_email = $value['from_email'];
            $trainer_email = $value['to_email'];               
            $send_on = $value['send_on'];
            //$email = "anoops@whizlabs.com";
            $email = "neha@digitalvidya.com";

            $today = date("Y-m-d");
            $send_on = $value['send_on'];
            $send_on = date('Y-m-d', strtotime($send_on));

            if ($send_on == $today){ // check the email send date with current date
                $is_sent = Yii::$app->mailer->compose()
                ->setFrom($coordinator_email)
                ->setTo($email) //$trainer_email
                ->setSubject($subject)
                ->setHtmlBody($body)
                ->send();

                 if ($is_sent === true){
                    Yii::$app->db->createCommand("UPDATE assist_email_log SET cron='1' WHERE id='$id'")->execute();
                   // echo 'true';
                 }
            }  // check the email send date with current date
        }

    }

    // this function is a part of batch validation to check the date
    private function check_session_date($trainer,$stime,$etime,$sdate,$module_id){
        $moduleid = array();
        $module_id = Yii::$app->db->createCommand("SELECT id FROM assist_batches WHERE trainer = '$trainer' AND id != '$module_id' ")->queryAll();
            foreach($module_id as $key => $value){
                $selected_module = $value['id'];
                $aStr = strtotime($stime);
                $bStr = strtotime($etime);

                //if($selected_module != $module_id){

                for ($i=$aStr; $i<=$bStr; $i+=1800){
                    $between_date = date('h:i A', $i);
                    $moduleid_sat = Yii::$app->db->createCommand("SELECT id FROM assist_batches WHERE stiming = '$between_date' AND id = '$selected_module' ")->queryAll();
                        if(!empty($moduleid_sat)){
                            $moduleid[] = $moduleid_sat['0'];    
                        }
                        
                        $moduleid_end = Yii::$app->db->createCommand("SELECT id FROM assist_batches WHERE etiming = '$between_date' AND id = '$selected_module' ")->queryAll();
                        if(!empty($moduleid_end)){
                            $moduleid[] = $moduleid_end['0'];    
                        }
                    }
                //}
            }
            
            $fmid = array();
            foreach($moduleid as $key => $value){
                $mid = $value['id'];
                $module_id = Yii::$app->db->createCommand("SELECT id FROM assist_batches_meta WHERE mid = '$mid' AND meta_value = '$sdate' ")->queryAll();
                if(!empty($module_id)){
                    $fmid[] = $module_id['0'];
                }
            }
            if(empty($fmid)){
                $return = '1';
            } else {
                $return = '0';
            }
            
        return $return;
    }

    
    /**     
     * using this function If the model is not found then a 404 HTTP exception will be thrown.     
     */
    protected function findModel($id){
        if (($model = DvAssistBatches::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    // this function is a part of create/update sessions functions. it is use to get the URL of a session.
    private function session_url($id,$sid){
        $output = '';
        $meta_key = 'session'.$sid.'rec';
        $session_url = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$id' AND meta_key = '$meta_key' ")->queryOne();
        $output = $session_url['meta_value'];
        return $output;
   }


   public function actionGet_trainers(){
    
        $data = yii::$app->request->post();
        $module_id = $data['module_id'];

        $user_meta_data = DvUsers::find()->where(['department'=>'7'])->all();
        $user_id = array();
        foreach($user_meta_data as $val){
            $course_array =  explode(",",$val['course']);

            if(in_array($module_id,$course_array)){
                $user_id[$val['id']]=ucfirst($val['first_name'].' '.$val['last_name']);
            }
            /*echo "<pre>";
            print_r($course_array);
            die;*/


        }
        
        $trainers = '<option value="">Select Trainer</option>';

        foreach ($user_id as $key => $value) {
            // $users_data = DvUsers::find()->where(['id'=>$key])->one();
            $trainers .= "<option value='".$key."'>".$value."</option>";
        }
        echo $trainers;
   }


   /**
    By:CDO:28 May 2018
    Purpose:get batches data from sheet's data
    */
    public function actionGet_batches_sheet_data(){
        //http://dev.digitalvidya.com/assist/dv-delivery/get_batches_sheet_data 
        //assist_module_sheet_data
        $dv_modules_data = Yii::$app->db->createCommand('SELECT * FROM assist_module')->queryAll();
        //get sheet's modules data
        $fetch_sheet_data = Yii::$app->runAction('dv-cron/get_batch_data');
        $new_batches_array = array();
        foreach($fetch_sheet_data as $sheet_data) {
            if(trim($sheet_data['Course']) !='' && !empty(trim($sheet_data['Course']))){
                $start_time = '';
                $end_time = '';
                $start_date = '';
                $end_date = '';
                $duration = '';
                $type = '';
                $day = '';
                $last_id = '';
                $module_id = '';
                $batch_status = '';
                $running_batch_status = '';
                if(substr(trim($sheet_data['Type']),1) == 'w' || substr(trim($sheet_data['Type']),2) == 'w'){
                    $no_of_sessions = '';
                    $no_of_sessions = explode("w",trim($sheet_data['Type']))[0];
                    //For Start time and End time
                    if($sheet_data['Timings'] == '8:00 PM(IST)'){
                        $start_time = '8:00 PM';
                        $end_time = '9:30 PM';
                        $duration = "1 hours 30 minutes";
                    }else if($sheet_data['Timings'] == '10:00 AM(IST)'){
                        $start_time = '10:00 AM';
                        $end_time = '1:00 PM';
                        $duration = "3 hours 0 minutes";
                    }
                     
                    $start_date = !empty($sheet_data['Start Date']) ? date('d-m-Y',strtotime(explode('#',$sheet_data['Start Date'])[0])) : '';

                    $end_date = array_key_exists('Session '.$no_of_sessions, $sheet_data) ? date('d-m-Y',strtotime($sheet_data['Session '.$no_of_sessions])) : '';
                    $end_date = date('d-m-Y',strtotime($start_date."+ $no_of_sessions week"));
                    foreach ($dv_modules_data as $module_value) {
                        
                        if(trim($sheet_data['Course']) == trim($module_value['module_name'])){
                            $module_id = $module_value['id'];
                            break;  
                        }      
                    }

                    $type = $no_of_sessions;
                    $seats = 35;
                    $day = date('D',strtotime($start_date));
                    $meeting_link = $sheet_data['GotoMeeting Link'];
                    if(trim($sheet_data['Session Complete']) == "Yes"){
                        $batch_status = 0;
                        $running_batch_status = 1;
                    }else if(trim($sheet_data['Session Complete']) == "No"){
                        if(strtotime($start_date) > strtotime(date('d-m-Y'))){
                            $batch_status = 0;
                            $running_batch_status = 3;  
                        }else if(strtotime($end_date) < strtotime(date('d-m-Y'))){
                            $batch_status = 0;
                            $running_batch_status = 1;   
                        }else{
                            $batch_status = 1;
                            $running_batch_status = 2;  
                        }    
                    }else{
                        if(strtotime($start_date) > strtotime(date('d-m-Y'))){
                            $batch_status = 0;
                            $running_batch_status = 3;
                        }else if(strtotime($end_date) < strtotime(date('d-m-Y'))){
                            $batch_status = 0;
                            $running_batch_status = 1;   
                        }else{
                            $batch_status = 1;
                            $running_batch_status = 2; 
                        } 
                    }
                    
                    //$new_batches_array[] = [$start_time,$end_time,$start_date,$end_date,$module_id,$duration,$type,$seats,$day];
                    /*
                    Yii::$app->db->createCommand()->insert('assist_batches_sheet_data',['created_by'=>1,'stiming'=>$start_time,'etiming'=>$end_time,'start_date'=>$start_date,'end_date'=>$end_date,'module'=>$module_id,'duration'=>$duration,'type'=>$type,'seats'=>$seats,'day'=>$day,'joining_link'=>$meeting_link])->execute();
                    $last_id = Yii::$app->db->getLastInsertID(); 
                    
                    $sessions_array = array();
                    for($i=1 ; $i<= $no_of_sessions ; $i++){
                        //For session
                        $sess_meta_key = '';
                        $sess_meta_value = '';
                        $sess_meta_key = "session".$i;
                        $sess_meta_value = array_key_exists("Session $i", $sheet_data) ? date('d-m-Y',strtotime($sheet_data["Session $i"])) : '';
                        Yii::$app->db->createCommand()->insert('assist_batches_meta_sheet_data',['mid'=>$last_id,'meta_key'=>$sess_meta_key,'meta_value'=>$sess_meta_value])->execute();

                        //For Start time
                        $start_time_meta = '';
                        $start_time_meta = "start_time".$i;
                        $start_time_meta_value = $start_time;
                        Yii::$app->db->createCommand()->insert('assist_batches_meta_sheet_data',['mid'=>$last_id,'meta_key'=>$start_time_meta,'meta_value'=>$start_time_meta_value])->execute();


                        //For End time
                        $end_time_meta = '';
                        $end_time_meta = "end_time".$i;
                        $end_time_meta_value = $end_time;
                        Yii::$app->db->createCommand()->insert('assist_batches_meta_sheet_data',['mid'=>$last_id,'meta_key'=>$end_time_meta,'meta_value'=>$end_time_meta_value])->execute();
                        
                        //For Recoring URL 
                        $sess_rec_url = '';
                        $sess_rec_url_key = "recording_url".$i;
                        $sess_rec_url_value_before = array_key_exists("Session $i Rec", $sheet_data) ? $sheet_data["Session $i Rec"] : ''; 
                        if(substr(trim($sess_rec_url_value_before),0,32) == "https://www.youtube.com/watch?v="){
                            $sess_rec_url_value = $sess_rec_url_value_before;
                        }else if($sess_rec_url_value_before!=''){
                            $sess_rec_url_value = "https://www.youtube.com/watch?v=".$sess_rec_url_value_before;
                        }
                        Yii::$app->db->createCommand()->insert('assist_batches_meta_sheet_data',['mid'=>$last_id,'meta_key'=>$sess_rec_url_key,'meta_value'=>$sess_rec_url_value])->execute();


                        if($i==1){
                            //For all_sessions
                            //final_end_date
                            Yii::$app->db->createCommand()->insert('assist_batches_meta_sheet_data',['mid'=>$last_id,'meta_key'=>'all_sessions','meta_value'=>$no_of_sessions])->execute();

                            Yii::$app->db->createCommand()->insert('assist_batches_meta_sheet_data',['mid'=>$last_id,'meta_key'=>'final_end_date','meta_value'=>$end_date])->execute();
                             
                            Yii::$app->db->createCommand()->insert('assist_batches_meta_sheet_data',['mid'=>$last_id,'meta_key'=>'batch_status','meta_value'=>$batch_status])->execute();

                            Yii::$app->db->createCommand()->insert('assist_batches_meta_sheet_data',['mid'=>$last_id,'meta_key'=>'running_batch_status','meta_value'=>$running_batch_status])->execute();
                        }
                    }
                    
                   */ 
                } 
            }//End of if
        }//End of for 
         
        //TRUNCATE TABLE assist_batches_sheet_data;
        //INSERT INTO assist_batches_sheet_data SELECT * FROM assist_batches;

        //TRUNCATE TABLE assist_batches_meta_sheet_data;
        //INSERT INTO assist_batches_meta_sheet_data SELECT * FROM assist_batches_meta;
        echo "All Batches data has been inserted";
         
    }//End og function:actionGet_batches_sheet_data//

}
