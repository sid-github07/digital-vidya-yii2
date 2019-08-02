<?php
namespace app\controllers;
use Yii;
use yii\web\Controller;
use app\models\DvUsers;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\data\Pagination; 
use app\models\DvRegistration;
use app\models\DvCoordinatorModel;
use app\models\DvUserMeta;
use app\models\DvCourseModel;
use app\models\DvParticipantBatchMeta;

class DvDelieveryMembersController extends Controller {

    /**
     * @PP  - 8 April 2019
     * Purpose:For Listing of Delievery Members
     * */
    public function actionIndex() {
        $query = DvRegistration::find()
                ->select(['assist_participant.*',"concat(assist_users.first_name,' ',assist_users.last_name) as sales_person_name"])
                ->where(['between', 'assist_participant.created_on',date("Y-03-01"),date("Y-m-d")])
                ->andWhere(['!=','assist_participant.course_batch_date','0000-00-00'])
                ->andWhere(['!=','assist_participant.course_batch_date','1970-01-01'])
                ->leftJoin('assist_users','assist_participant.sales_user_id = assist_users.id');
        $countQuery = clone $query;
        //For Pagination
        $pages = new Pagination(['totalCount' => $countQuery->count(),'PageSize' => 10]);
        $model = $query->offset($pages->offset)
                        ->limit($pages->limit)
                        ->orderBy(['program_coordinator'=>SORT_ASC])
                        ->createCommand()
                        ->queryAll();
        //For Export
        $query_export = $countQuery->orderBy(['program_coordinator'=>SORT_ASC])->createCommand()->queryAll();
        
        //For Getting Todays Presenet Coordinator data    
        /*$today_present_coordinator = DvCoordinatorModel::find()
                                    ->where(['created_on' =>date('Y-m-d')])
                                    ->one();
        $coordinator_array = array();
        if(count($today_present_coordinator) > 0){
            $today_present_coordinator_array = explode(',',$today_present_coordinator['coordinator_ids']);
            //$existing_coordinator = ArrayHelper::map($today_present_coordinator,'id','id');
            foreach ($today_present_coordinator_array as $value) {
                $users_data = DvUsers::find()
                            ->where(['id'=>$value])
                            ->one();
                if(!empty($users_data['first_name'])){
                    $coordinator_array[ucfirst($users_data['first_name'].' '.$users_data['last_name'])] = ucfirst($users_data['first_name'].' '.$users_data['last_name']).'###'.$users_data['email'];
                }
            }
        }*/

        //For Getting all Co-ordinator data for Filter
        $all_coordinator_array =ArrayHelper::map(DvUserMeta::find()->where(['meta_key'=>'role','meta_value'=>5])->all(),'uid','uid');
        $all_coordinator_data = array();
        foreach ($all_coordinator_array as $value) {
            $users_data = DvUsers::find()
                        ->where(['id'=>$value])
                        ->one();
            if(!empty($users_data['first_name'])){
                $all_coordinator_data[ucfirst($users_data['first_name'].' '.$users_data['last_name'])] = ucfirst($users_data['first_name'].' '.$users_data['last_name']);
            }
        }

        return $this->render('delievery_members_list',
            array('participant_users'=>$model,
                'query_export'=>$query_export,
                'pages' => $pages,
                //'coordinator_array'=>$coordinator_array,
                'all_coordinator_data'=>$all_coordinator_data
            )
        );
    } // End of function:actionIndex() 
    
    /**
     * @PP  - 8 April 2019
     * Purpose:For Filter of Delievery Members
     * */
    public function actionFilter() {
        $data = Yii::$app->request->get();
        if ($data) {
            $model = '';
            $filter_data = array();
            $common_query = DvRegistration::find()
                        ->select(['assist_participant.*',"concat(assist_users.first_name,' ',assist_users.last_name) as sales_person_name"])
                        ->where(['between', 'assist_participant.created_on',date("Y-03-01"),date("Y-m-d")])
                        ->andWhere(['!=','assist_participant.course_batch_date','0000-00-00'])
                        ->andWhere(['!=','assist_participant.course_batch_date','1970-01-01'])
                        ->leftJoin('assist_users','assist_participant.sales_user_id = assist_users.id');
            //For Course Filter
            if($data['course'][0] != "") {
                $filter_data['course'] = $data['course'];
                $model = $common_query->andWhere(['in', 'assist_participant.course',$data['course']]);
            }
            //For Name
            if (isset($data['name']) && $data['name'] != '') {
                $filter_data['name'] = $data['name'];
                $model = $common_query->andWhere(['like','assist_participant.first_name', trim($data['name'])]);
                //->orWhere(['like','assist_participant.last_name', trim($data['new_participant_name'])]);                            
            }
            //For Mobile
            if (isset($data['mobile']) && $data['mobile'] != '') {
                $filter_data['mobile'] = $data['mobile'];
                $model = $common_query->andWhere(['like', 'assist_participant.mobile', trim($data['mobile'])]);
            }
            //For Mail
            if (isset($data['email']) && $data['email'] != '') {
                $filter_data['email'] = $data['email'];
                $model = $common_query->andWhere(['like', 'assist_participant.email', trim($data['email'])]);
            }
            //For Opt out
            if (isset($data['optout']) && $data['optout'] != '') {
                $filter_data['optout'] = $data['optout'];
                $model = $common_query->andWhere(['assist_participant.opt_for_3_months'=>$data['optout']]);
            }
            //For Vskills
            if (isset($data['vskills']) && $data['vskills'] != '') {
                $filter_data['vskills'] = $data['vskills'];
                $model = $common_query->andWhere(['assist_participant.vskills'=>$data['vskills']]);
            }
            //For Blog URL
            if (isset($data['blog_url']) && $data['blog_url'] != '') {
                $filter_data['blog_url'] = $data['blog_url'];
                $model = $common_query->andWhere(['assist_participant.opt_for_3_months'=>$data['optout']]);
            }
            //For Enrollment Date  
            if(isset($data['en_sdate']) && $data['en_sdate'] !="" && isset($data['en_edate']) && $data['en_edate']!=''){
                $filter_data['en_sdate'] = $data['en_sdate'];
                $filter_data['en_edate'] = $data['en_edate'];
                $model = $common_query->andWhere(['between','assist_participant.created_on',date('Y-m-d',strtotime($data['en_sdate'])),date('Y-m-d',strtotime('+1 days',strtotime($data['en_edate'])))]);
            }
            //For Batch Date 
            if(isset($data['batch_sdate']) && $data['batch_sdate']!='' && isset($data['batch_edate']) && $data['batch_edate']!=''){
                $filter_data['batch_sdate'] = $data['batch_sdate'];
                $filter_data['batch_edate'] = $data['batch_edate'];
                $model = $common_query->andWhere(['between','assist_participant.course_batch_date',date("Y-m-d",strtotime($data['batch_sdate'])),date("Y-m-d",strtotime($data['batch_edate']))]);
            }
            //For foundation session date 
            if(isset($data['foundation_session_date']) && $data['foundation_session_date']!=''){
                $filter_data['foundation_session_date'] = $data['foundation_session_date'];
                $model = $common_query->andWhere(['between','assist_participant.course_batch_date',date("Y-m-d",strtotime($data['foundation_session_date'])),date("Y-m-d",strtotime($data['foundation_session_date']))]);
            } 
            //For foundation modules_allowed_from date 
            if((isset($data['modules_allowed_from']) && $data['modules_allowed_from']!='') || (isset($data['modules_allowed_to']) && $data['modules_allowed_to']!='')){
                $filter_data['modules_allowed_from'] = $data['modules_allowed_from'];
                $filter_data['modules_allowed_to'] = $data['modules_allowed_to'];
                //For Both
                if( $data['modules_allowed_from']!='' && $data['modules_allowed_to']!=''){
                    $model = $common_query->andWhere(['between','assist_participant.modules_allowed',$data['modules_allowed_from'],$data['modules_allowed_to']]);
                }

                //For single 24 May 2019
                if($data['modules_allowed_from']!='' && $data['modules_allowed_to']==''){
                    $model = $common_query->andWhere(['assist_participant.modules_allowed'=>$data['modules_allowed_from']]);
                }else if($data['modules_allowed_from']=='' && $data['modules_allowed_to']!=''){
                    $model = $common_query->andWhere(['assist_participant.modules_allowed'=>$data['modules_allowed_to']]);
                }
            }
            //For foundation modules_completed_from date 
            if((isset($data['modules_completed_from']) && $data['modules_completed_from']!='') || (isset($data['modules_completed_to']) && $data['modules_completed_to']!='')){
                $filter_data['modules_completed_from'] = $data['modules_completed_from'];
                $filter_data['modules_completed_to'] = $data['modules_completed_to'];
                //For Both
                if($data['modules_completed_from']!='' && $data['modules_completed_to']!=''){
                    $model = $common_query->andWhere(['between','assist_participant.modules_completed',$data['modules_completed_from'],$data['modules_completed_to']]);
                }
                //for single 24 May 2019
                if($data['modules_completed_from']!='' && $data['modules_completed_to']==''){
                    $model = $common_query->andWhere(['assist_participant.modules_completed'=>$data['modules_completed_from']]);
                }else if($data['modules_completed_from']=='' && $data['modules_completed_to']!=''){
                    $model = $common_query->andWhere(['assist_participant.modules_completed'=>$data['modules_completed_to']]);
                }
            }

            //For Program Co ordinator
            if(isset($data['program_coordinator']) && $data['program_coordinator']!=''){
                $filter_data['program_coordinator'] = $data['program_coordinator'];
                $model = $common_query->andWhere(['like','assist_participant.program_coordinator', trim($data['program_coordinator'])]); 
            }

            //for New participant search where Program Co ordinator still not allocated
            if(isset($data['participant_new_old_all']) && $data['participant_new_old_all']!=''){
                $filter_data['participant_new_old_all'] = $data['participant_new_old_all'];
                if($data['participant_new_old_all'] == "All"){
                    $model = $common_query; 
                } else if($data['participant_new_old_all'] == 1){
                    $model = $common_query->andWhere(['assist_participant.participant_status'=>1]);
                } else if($data['participant_new_old_all'] == 2){
                    $model = $common_query->andWhere(['assist_participant.participant_status'=>2]);
                } else if($data['participant_new_old_all'] == 3){
                    $model = $common_query->andWhere(['assist_participant.participant_status'=>3]);
                } else if($data['participant_new_old_all'] == 4){
                    $model = $common_query->andWhere(['assist_participant.participant_status'=>4]);
                }   
            }

            //For Future Development 
            /*
            completion month,
            completion date , 
            module start date &
            end date  ".
            */
            //For pagination 
            if(!empty($model)){    
                $countQuery = clone $model;
                $pages = new Pagination(['totalCount' => $countQuery->count(),'PageSize' => 10]);
                $models = $model->offset($pages->offset)
                            ->limit($pages->limit)
                            ->orderBy(['program_coordinator'=>SORT_ASC])
                            ->createCommand()
                            ->queryAll();
                $query_export = $countQuery->orderBy(['program_coordinator'=>SORT_ASC])->createCommand()->queryAll();
                
                //For Getting Todays Presenet Coordinator data    
                $today_present_coordinator = DvCoordinatorModel::find()
                                            ->where(['created_on' =>date('Y-m-d')])
                                            ->one();
                $coordinator_array = array();
                if(count($today_present_coordinator) > 0){
                    $today_present_coordinator_array = explode(',',$today_present_coordinator['coordinator_ids']);
                    //$existing_coordinator = ArrayHelper::map($today_present_coordinator,'id','id');
                    foreach ($today_present_coordinator_array as $value) {
                        $users_data = DvUsers::find()
                                    ->where(['id'=>$value])
                                    ->one();
                        if(!empty($users_data['first_name'])){
                            $coordinator_array[ucfirst($users_data['first_name'].' '.$users_data['last_name'])] = ucfirst($users_data['first_name'].' '.$users_data['last_name']).'###'.$users_data['email'];
                        }
                    }
                }

                //For Getting all Co-ordinator data for Filter
                $all_coordinator_array =ArrayHelper::map(DvUserMeta::find()->where(['meta_key'=>'role','meta_value'=>5])->all(),'uid','uid');
                $all_coordinator_data = array();
                foreach ($all_coordinator_array as $value) {
                    $users_data = DvUsers::find()
                                ->where(['id'=>$value])
                                ->one();
                    if(!empty($users_data['first_name'])){
                        $all_coordinator_data[ucfirst($users_data['first_name'].' '.$users_data['last_name'])] = ucfirst($users_data['first_name'].' '.$users_data['last_name']);
                    }
                }

                return $this->render('delievery_members_list', 
                    ['participant_users' => $models, 
                        'pages' => $pages,
                        'filter_data' => $filter_data,
                        'query_export'=>$query_export,
                        'coordinator_array'=>$coordinator_array,
                        'all_coordinator_data'=>$all_coordinator_data
                    ]);
            }else{
                return $this->redirect(['dv-delievery-members/index']);
            }
        } else {
            return $this->redirect(['dv-delievery-members/index']);
        }

    }//End of function:actionFilter

    /**
     * @PP  - 8 April 2019
     * Purpose:For Participant View
     * */
    public function actionParticipant_view($id = NULL){
        if($id != NULL && !empty($id)){
            $query = DvRegistration::find()->where(['assist_participant.id' => $id])->one();
            if(!empty($query)){
                //---Begin Added on 15 may 2019---//
                //Get batches data
                $batch_meta_master_data = array();
                $batch_modules_data = array();
                $batch_modules_data = DvParticipantBatchMeta::find()
                                    ->select(['assist_participant_batch_meta.pid as participant_id','assist_batches.*','assist_module.module_name','assist_module.category_type',"concat(assist_users.first_name,' ',assist_users.last_name) as trainer_name"])
                                    ->where(["assist_participant_batch_meta.pid"=>$id])
                                    ->leftJoin('assist_batches','assist_participant_batch_meta.batch_id = assist_batches.id')
                                    ->leftJoin('assist_module','assist_batches.module = assist_module.id')
                                    ->leftJoin('assist_users','assist_batches.trainer = assist_users.id')
                                    ->createCommand()
                                    ->queryAll();
                if(count($batch_modules_data) > 0){
                    $batch_meta = array();
                    $batch_meta_master_data = array();
                    foreach ($batch_modules_data as $value) {
                        $batch_meta = Yii::$app->db->createCommand("SELECT * FROM assist_batches_meta WHERE mid=".$value['id'])->queryAll();
                        if(count($batch_meta) > 0){
                            $batch_meta_data = array();
                            foreach ($batch_meta as $value_) {
                                $batch_meta_data[] = $value_; 
                            }
                            $batch_meta_master_data[$value['id']] = $batch_meta_data;
                        }
                    }
                    //---End Added on 15 may 2019---//
                }
                return $this->render('delievery_member_view', [ 'model' =>$query,
                                    'batch_modules_data'=>$batch_modules_data,
                                    'batch_meta_master_data'=>$batch_meta_master_data]);
            }else{
                $this->redirect(['dv-delievery-members/index']);   
            }
        }else{
            $this->redirect(['dv-delievery-members/index']);
        }
    }//End of function:actionParticipant_view

    /**
     * @PP  - 8 April 2019
     * Purpose:For Update Participant Allowed Module
     * */
    public function actionUpdate_participant_allowed_module_ajax(){
        if (isset($_POST['participant_id'])) {
            $participant_id = $_POST['participant_id'];
            $allowed_module = $_POST['allowed_module'];
            $url = $_POST['url'];
            $models = DvRegistration::findOne($participant_id);
            $old_allowed_module = $models['modules_allowed'];
            
            $userlog_id = Yii::$app->CustomComponents->create_UsersActivityLog($participant_id, $url, "Updated allowed modules", $old_allowed_module, $allowed_module);
            if ($userlog_id == '') {
                echo "There are some error in user log";
                die;
            }

            $models_res = Yii::$app->db->createCommand('UPDATE assist_participant SET modules_allowed='.$allowed_module.' WHERE id='.$participant_id)->execute();
            if ($models_res) {
                echo "1";
            } else {
                echo "2";
            }
            
        }

    }// End of function:update_participant_allowed_module_ajax() 

    /**
     * @PP  - 8 April 2019
     * Purpose:For Update Co-ordinator Name
     * */
    public function actionUpdate_coordinator(){
        $id = Yii::$app->request->post('participant_id');
        $coordinator_name = Yii::$app->request->post('coordinator_name');
        $coordinator_mail = Yii::$app->request->post('coordinator_mail');
        if($id!=''){
            $custom_query = Yii::$app->db->createCommand("UPDATE assist_participant SET program_coordinator = '$coordinator_name' WHERE id = '$id' ")->execute();
            $model = DvRegistration::find()->where(['id'=>$id])->one();
            $course_model = DvCourseModel::find()->where(['status'=>1,'id'=>$model->course])->one();
            //$coordinator_data = DvUsers::find()->where(['first_name'=>$model])->one();
            if($model->program_coordinator_mail_sent == 0){ 
                //Mail Code Goes here
                $subject = Yii::$app->params['site_name']." Co-ordinator";
                  $body = " <h3>Hello ". ucfirst($model['first_name'].' '.$model['last_name'])."</h3>
                            <p>Congratulations on your registration in ".$course_model->name." Course!</p>
                            <p>I will be your Program coordinator for this journey with Digital Vidya and would be reachable at <coordinatornumber> for any queries. In case, you are unable to reach me, you may call on support number 8081033033.</p>
                            <p>Schedule for 1st Month - All session related communication would happen via noreply@digitalvidya.co.in or donotreply@digitalvidya.co.in. You can also check the details of the current running module through the following  link: <Live Session Schedule></p>
                            <p>Schedule Table based on module allotted to him/ her</p>
                            <p>Learning Management System - Your 1st module is added to the LMS account. The same can be accessed using the following credentials:</p>
                            <p>URL- https://www.digitalvidya.com/training/</p>
                            <p>Username & Password - ".$model['email']."</p>
                            <p>Prerequisites of the Training - Do refer to the attached documents before the live session and be ready with following to have a smooth experience of online sessions.</p>
                            <p>Computer (Windows/ Mac)
                                Earphones with Mic
                                Wifi (Min. speed - 2 MBPS)</p>
                            <p>I will give you a call shortly to discuss further.</p>
                            
                            Best wishes,<br>
                            $coordinator_name
                        ";
                //$model->email
                Yii::$app->mailer->compose()
                  ->setFrom('it@digitalvidya.com') // put here $coordinator_mail
                  ->setTo($model->email)
                  ->setBcc ('cdo@logixbuilt.com')
                  ->setSubject($subject)
                  ->setHtmlBody($body)
                  ->send();

                  //Mail Status update
                  Yii::$app->db->createCommand("UPDATE assist_participant SET program_coordinator_mail_sent = '1' WHERE id = '$id' ")->execute();
                  echo 1;
            }//End of mail
            echo $custom_query;
        }else{
            echo 0;
        }
        /*
        //$model = DvRegistration::find()->where(['id'=>$id])->one();
        if(count($module)>0){
            //$model->program_coordinator = !empty($coordinaror_name)?$coordinaror_name:'';
            //$model->save();
            return print_r($module);
        }else{
            return 0;
        }
        */
        //return 11;
    }// End of function:actionUpdate_coordinator() 

    /**
     * @PP  - 15 April 2019
     * Purpose:For Edit of Participant
     * */
    public function actionParticipant_edit_view(){
        $id = Yii::$app->request->post('id');
        if($id!=''){
            $model = DvRegistration::find()->where(['id'=>$id])->one();
            return $this->renderPartial('delievery_member_edit_view',
                array('model'=>$model)
            );
        }
    }//End of function:actionParticipant_edit_view()

    /**
     * @PP  - 15 April 2019
     * Purpose:For Edit Save of Participant
     * */
    public function actionEdit_save(){
        $data = Yii::$app->request->post();
        if(isset($data)){
            $id = $data['id'];
            //Get All Post Data  
            $first_name = $data['first_name'];
            $last_name = $data['last_name'];
            $email = $data['email'];
            $mobile = $data['mobile'];
            $course = $data['course'];
            $program_coordinator = $data['program_coordinator'];
            $vskills = $data['vskills'];
            $remarks = $data['remarks'];
            $modules_allowed = $data['modules_allowed']; 
            $modules_completed  = $data['modules_completed'];
            $available_batch_opt = $data['available_batch_opt']; 
            $course_batch_date =  date('Y-m-d',strtotime($data['course_batch_date']));
            //Set Insert Data
            /*
            $model = DvRegistration::findOne($id);
            $model->first_name = $first_name;
            $model->last_name = $last_name;
            $model->email = $email;
            */
            $custom_query = Yii::$app->db->createCommand("UPDATE assist_participant SET first_name = '$first_name',last_name = '$last_name' , email = '$email' , mobile = '$mobile' , course = '$course' ,program_coordinator = '$program_coordinator' , vskills = '$vskills' , remarks = '$remarks' , modules_allowed = '$modules_allowed' , modules_completed = '$modules_completed' ,available_batch_opt = '$available_batch_opt' ,course_batch_date = '$course_batch_date' WHERE id = '$id' ")->execute();

            if($custom_query){
                $msg = "success";
                $msg_content = "Record has been updated successfully.";
            }else{
                $msg = "error";
                $msg_content = "Something Went Wrong !";
            }
            Yii::$app->session->setFlash($msg?$msg:"", $msg_content?$msg_content:"");
        }
        $call_from = isset($_POST['call_from']) ? $_POST['call_from'] : '';
        return $this->redirect(['dv-delievery-members/participant_view?id='.$id.'&call_from='.$call_from]);  

    }//End of function:actionEdit_save()

}// --- End of class:DvDelieveryMembersController --- //