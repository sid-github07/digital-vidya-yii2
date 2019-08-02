<?php 
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\DvParticipantBatchMeta;
use app\models\DvRegistration;
use app\models\DvAssistBatches;
use app\models\DvModuleModel;
use app\models\DvCourseModel;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class DvBatchAllotmentController extends Controller {
	
	/* function to suggest the next batch to the student*/

    public function actionIndex($paticipant_ids = NULL) {
        if($paticipant_ids!=NULL){
            $participant_data = DvRegistration::find()->where(['IN','id',$paticipant_ids])->all();
        }else{
            $participant_data = DvRegistration::find()->all();
        }
    	$data = array();
    	$result = array();
	    $participant_ids = '';
        foreach($participant_data as $val){
            if($val['modules_allowed'] > $val['modules_completed']+1 && $val['modules_allowed']!=1){
                $course = $val['course'];
                $module_id = DvCourseModel::find()->select('core_modules')->where(['id'=>$course])->one();
                $module_id = explode(',',$module_id->core_modules);
                $current_batch_id = $val['course_batch'];

                $participant_batch_modules = DvParticipantBatchMeta::find()->where(["pid"=>$val['id']])->orderBy(['id'=>SORT_DESC])->createCommand()->queryAll();

                $last_batch_id = '';
                $checkdate = '';
                $endDate = '';

                if(count($participant_batch_modules)){
                    $last_batch_id = $participant_batch_modules[0]['batch_id'];
                    $batch_data = DvAssistBatches::find()->where(["id"=>$last_batch_id])->one();
                    $batch_enddate = $batch_data['end_date'];
                    $batch_enddate = date('d-m-Y', strtotime($batch_enddate. ' + 7 day'));
                    $batch_startdate = $batch_data['start_date'];
                    $batch_day = date('D', strtotime($batch_startdate));
                    $endDate = date('d-m-Y', strtotime($batch_data['end_date']));
                    $checkdate = date('d-m-Y', strtotime(date("Y/m/d"). ' + 14 days'));
                } 
               
                if(strtotime($endDate) < strtotime($checkdate) && !empty($endDate) && !empty($checkdate)){
                    $completed_modules = DvParticipantBatchMeta::find()->where(["pid"=>$val['id']])->all();
                    $completed_modules_id = array();

                    foreach($completed_modules as $completed_modules_val ){
                        $completed_modules_result = Yii::$app->db->createCommand("SELECT * FROM assist_batches_meta WHERE mid = '".$completed_modules_val['batch_id']."' AND meta_key = 'running_batch_status' AND meta_value !='3'")->queryOne();
                        if($completed_modules_result){
                            $batch_result = Yii::$app->db->createCommand("SELECT * FROM assist_batches WHERE id = '".$completed_modules_val['batch_id']."' AND UNIX_TIMESTAMP(STR_TO_DATE(end_date,'%d-%m-%Y')) < ".strtotime(date('Y-m-d'))."")->queryOne();
                            //$batch_result = Yii::$app->db->createCommand("SELECT * FROM assist_batches WHERE id = '".$completed_modules_val['batch_id']."'")->queryOne();

                            $completed_modules_id[] = $batch_result['module'];
                        }
                    }

                    $total_allowed_module = $val['modules_allowed'];

        			$remaining_modules = array();
        			
                    if($course==1){
                       $remaining_modules = array_diff($module_id, $completed_modules_id);
        			}else if($course==2){
                       $remaining_modules = array_diff($module_id, $completed_modules_id);
        			}

                    $key = implode(",",$remaining_modules);

                    $module_id_array = explode(",",$key);
                    $module_names = '';
                    
                    foreach($module_id_array as $module_val){
                        //$module_result = DvModuleModel::find()->select('module_name')->where(['id'=>$module_val])->one();
                        $module_result = DvModuleModel::find()->select('module_name')->where(['id'=>$module_val,'category_type'=>'Core'])->one();
                        if($module_result){
                            $module_names .= $module_result->module_name.",";
                        }
                    }

                    $module_names = rtrim($module_names,",");
                    
                    //Begin for completed module added on 7 May 2019
                    $completed_key = implode(",",$completed_modules_id);
                    $module_completed_id_array = explode(",",$completed_key);
                    $completed_module_names = '';
                    foreach($module_completed_id_array as $module_val){
                        $module_result_completed = DvModuleModel::find()->select('module_name')->where(['id'=>$module_val,'category_type'=>'Core'])->one();
                        if($module_result_completed){
                            $completed_module_names .= $module_result_completed->module_name.",";
                        }
                    }
                    $completed_module =  rtrim($completed_module_names,",");
                    //End of completd module array
                    if(array_key_exists($key,$data)){
        				$data[$key]['modules'] = $module_names;
                        $data[$key]['completed_modules'] = $completed_module;
        				$data[$key]['students'] = $data[$key]['students']+1;
                        $data[$key]['date'] = $batch_enddate;
        				$data[$key]['day'] = $batch_day;
        				$data[$key]['ids'] = $data[$key]['ids'].','.$val['id'];
                    }else{
                        $data[$key]['modules'] = $module_names;
                        $data[$key]['completed_modules'] = $completed_module;
        				$data[$key]['students'] = 1;
        				$data[$key]['date'] = $batch_enddate;
                        $data[$key]['day'] = $batch_day;
        				$data[$key]['ids'] = $val['id'];
    				}
                }
			}
        }
        //For Pagination new way implementation
        if($paticipant_ids == NULL){
            $pages = new Pagination(['totalCount' => count($data),'PageSize' => 10]);
            $data = array_slice($data,$pages->offset,$pages->limit); 
            return $this->render('index', ['model'=>$data,'pages' => $pages]);
        }else{
            return $data;
        }
    }

    /* function to display the list of student when clicked from open sales form page */
    public function actionDisplay_students_list(){
        $data = yii::$app->request->post();
        $students_id = $data['students_id'];
        $students_id = explode(',',$students_id);
        return $this->redirect(array('student_list', 'students_id'=>$students_id,'batch_allotment_details'=>$batch_allotment_details,'completed_modules_name'=>$completed_modules_name));
    }

    /* function to get the list of student when clicked from open sales form page */
    public function actionStudent_list(){
        $data = yii::$app->request->get();
        if(isset($data) && isset($data['students_id']) && $data['students_id'] !=''){
            $students_id = explode(',',$data['students_id']);
            $module_data = DvRegistration::find()->WHERE(['in', 'id', $students_id]);
            //For Pagination
            $query = $module_data;
            $countQuery = clone $query;
            $pages = new Pagination(['totalCount' => $countQuery->count(),'PageSize' => 10]);
            $module = $query->offset($pages->offset)
                            ->limit($pages->limit)
                            ->createCommand()
                            ->queryAll();
            //get batch data
            $common_batch_data = $this->actionIndex($students_id); // here consider common module batch
            //End of left Special module
            return $this->render('students_list',['module'=>$module,'pages' => $pages,
                'common_batch_data'=>$common_batch_data
                //'unallocated_special_batch'=>$main_filter_special_batch
            ]);

        }else{
            return $this->redirect('possible_special_modules');
        }

    }

    /**
    *By : PP 24 April 2019
    *Purpose : function to get all ongoing batch details
    **/
    public function actionDisplay_batch_list(){
        $data = Yii::$app->request->post();
        $total_selected = $data['total_students'];
        $all_students_ids = $data['all_students_id'];
        return $this->redirect(array('batch_list', 'total_selected'=>$total_selected,'all_students_ids'=>$all_students_ids));
    } // End of function:actionDisplay_batch_list//

    /**
    *By : PP 24 April 2019
    *Purpose : function to get all ongoing batch details
    **/
    public function actionBatch_list(){
        $data = Yii::$app->request->get();
        $total_selected = $data['total_selected'];
        //$all_students_ids = $data['all_students_ids'];
        $all_students_ids = explode(',', $data['all_students_ids']);

        $heighest_batch_enddate = date("Y/m/d");
        // $heighest_batch_enddate_arr = array();

        foreach($all_students_ids as $val){
            $participant_batch_modules = DvParticipantBatchMeta::find()->where(["pid"=>$val])->orderBy(['id'=>SORT_DESC])->createCommand()->queryAll();

            
            if(count($participant_batch_modules)){
                $last_batch_id = $participant_batch_modules[0]['batch_id'];
               //  $heighest_batch_enddate_arr[]  $last_batch_id;
                $batch_data = DvAssistBatches::find()->where(["id"=>$last_batch_id])->one();
                $batch_enddate = $batch_data['end_date'];
            } 
            

            if(strtotime($heighest_batch_enddate) < strtotime($batch_enddate) ){
                $heighest_batch_enddate = $batch_enddate;
            }

        }
        $all_students_ids = $data['all_students_ids'];
        //select except special modules
        $only_core_foundation_modules = ArrayHelper::map(DvModuleModel::find()->where(['category_type'=>'Core'])->all(),'id','module_name');
            if(count($only_core_foundation_modules) > 0){
                $batch_cp_array = array_keys($only_core_foundation_modules); 
        }
        //Get all ongoing batch
        $batch_meta = ArrayHelper::map(Yii::$app->db->createCommand("SELECT * FROM assist_batches_meta WHERE meta_key = 'running_batch_status' AND meta_value = '3'")->queryAll(),'mid','mid');
        $only_key_batch_meta = array_keys($batch_meta);

        if(count($batch_cp_array) > 0 && count($only_key_batch_meta) > 0){
            //Get Batch details
            $batch_data = DvAssistBatches::find()
                        ->select(['assist_batches.*','assist_module.module_name',"concat(assist_users.first_name,' ',assist_users.last_name) as trainer_name"])
                        ->where(['in','assist_batches.id',$only_key_batch_meta])
                        ->andWhere(['in','assist_module.id',$batch_cp_array])
                        ->leftJoin('assist_users','assist_batches.trainer = assist_users.id')
                        ->leftJoin('assist_module','assist_batches.module = assist_module.id')
                        ->where(['>',"UNIX_TIMESTAMP(STR_TO_DATE(assist_batches.start_date,'%d-%m-%Y'))",strtotime($heighest_batch_enddate)]);
        }else{
            $batch_data = array();
        }

        //For Pagination 
        $query = $batch_data;
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(),'PageSize' => 10]);
        $batch_all_data = $query->offset($pages->offset)
                        ->limit($pages->limit)
                        ->createCommand()
                        ->queryAll();
        return $this->render('batch_list',['batch_data'=>$batch_all_data,'total_selected'=>$total_selected,'batch_ongoing_array'=>$batch_cp_array,'pages' => $pages,'all_students_ids'=>$all_students_ids]);

    }//End of function:actionBatch_list//

    /**
    Date:25 April 2019 By PP
    Purpose:For filter students data
    */
    public function actionStudents_filter(){
        $filter_array = array();
        $data = Yii::$app->request->get();
        if(!isset($data['students_id'])){
            return $this->redirect(array('index'));
        }
        $students_id = isset($data['students_id']) ? explode(',',$data['students_id']) : '';
        $module_data = count($students_id) > 0 ? DvRegistration::find()->where(['in', 'id', $students_id]) : '';
        $custom_query = '';
        //For Module allowed filter goes here 
        if(!empty($data['modules_allowed'])){
            $custom_query = $module_data->andWhere(['modules_allowed'=>$data['modules_allowed']]);
            $filter_array['modules_allowed'] = $data['modules_allowed'];
        }
        //For batch opt out filter goes here
        if(!empty($data['available_batch_opt'])){
            $custom_query = $module_data->andWhere(['available_batch_opt'=>$data['available_batch_opt']]);
            $filter_array['available_batch_opt'] = $data['available_batch_opt'];
        }
        $common_batch_data = $this->actionIndex($students_id);
        if(!empty($custom_query)){
            //For Pagination
            $query = $custom_query;
            $countQuery = clone $query;
            $pages = new Pagination(['totalCount' => $countQuery->count(),'PageSize' => 10]);
            $module = $query->offset($pages->offset)
                            ->limit($pages->limit)
                            ->createCommand()
                            ->queryAll();
                        
            return $this->render('students_list',['module'=>$module,'pages' => $pages,'filter_array'=>$filter_array,'common_batch_data'=>$common_batch_data]);
        }else{
            return $this->redirect(array('student_list', 'students_id'=>$data['students_id']));
        }
    }//End of function:actionStudents_filter//

    /**
    *@PP 30 April 2019
    *Purpose:Allocate Students
    **/
    public function actionAllocate_students_batch(){
        $data = Yii::$app->request->post();
        $total_selected = $data['total_selected'];
        $batch_id = $data['batch_id'];
        $all_students_ids = explode(',',$data['all_students']);
        if(count($all_students_ids) > 0){
            for($i = 0 ; $i < count($all_students_ids) ; $i++){
                Yii::$app->db->createCommand()->insert('assist_participant_batch_meta', [ 'pid' => $all_students_ids[$i],'batch_id'=>$batch_id ])->execute();
            }
        }
        //need to set condition
        $msg = "success";
        $msg_content = "Batch has been Allocated successfully.";
        Yii::$app->session->setFlash($msg?$msg:"", $msg_content?$msg_content:"");

        return $this->redirect(array('batch_list', 'total_selected'=>$total_selected,'all_students_ids'=>$data['all_students']));

    }//End of function:actionAllocate_students_batch//

    /**
    *By : PP 26 April 2019
    *Purpose : function to get all batch details
    **/
    public function actionAll_batch_list(){
        //Get All Batch details
        $batch_data = DvAssistBatches::find()
                    ->select(['assist_batches.*','assist_module.module_name',"concat(assist_users.first_name,' ',assist_users.last_name) as trainer_name"])
                    ->leftJoin('assist_users','assist_batches.trainer = assist_users.id')
                    ->leftJoin('assist_module','assist_batches.module = assist_module.id');

        //For Pagination 
        $query = $batch_data;
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(),'PageSize' => 10]);
        $batch_all_data = $query->offset($pages->offset)
                        ->limit($pages->limit)
                        ->createCommand()
                        ->queryAll();

        return $this->render('all_batch_list',['all_batch_data'=>$batch_all_data,'pages' => $pages]);

    }//End of function:actionAll_batch_list//


    /**
    *By : Chintan 02 May 2019
    *Purpose : function to get all batch details
    **/
    public function actionAll_students(){
        //Get All students data
        $data = $_GET;
        $pid = explode(',',$data['pid']);
        $students_list = DvRegistration::find()->where(['in','id',$pid])->all();
        //For Pagination 
        /* $query = $batch_data;
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(),'PageSize' => 10]);
        $batch_all_data = $query->offset($pages->offset)
                        ->limit($pages->limit)
                        ->createCommand()
                        ->queryAll(); */
        $data['call_from'] = isset($data['call_from']) ? $data['call_from'] : ''; 
        $data['module_name'] = isset($data['module_name']) ? $data['module_name'] : ''; 
        $data['module_date'] = isset($data['module_date']) ? $data['module_date'] : ''; 
        $data['batch_id'] = isset($data['batch_id']) ? $data['batch_id'] : ''; 
        //For export purpose
        $participant_users = DvRegistration::find()->where(['in','id',$pid])->all(); 
         
        return $this->render('all_student_list',['students_list'=>$students_list,'type'=>$data['type'],'call_from'=>$data['call_from'],
                'module_name'=>$data['module_name'],
                'module_date'=>$data['module_date'],
                'batch_id'=>$data['batch_id'],'participant_users_array'=>$participant_users]);

    }//End of function:actionAll_batch_list//

    /**
    *By:CDO 3 May 2019
    *Purpose:For Unallocate/Allocate based on request
    *
    **/
    public function actionBatch_action_vice_versa(){
        $data = Yii::$app->request->post();
        $students_array = explode(",",$data['students_ids']);
        $all_students_array = explode(",",$data['all_students_ids']);
        //For Allocated students to unallocate
        if($data['type'] == 'allocated'){
            $msg = "success";
            $which_field = 'unallocated_batch_initiated';
            $msg_content = "Batch has been unllocated successfully.";
        }
        //For unallocated students to allocate
        if($data['type'] == 'not_allocated'){
            $msg = "success";
            $which_field = 'allocated_batch_initiated';
            $msg_content = "Batch has been allocated successfully.";
        }
        $batch_id = isset($data['batch_id']) ? $data['batch_id'] : '';
        $data['all_students_ids'] = isset($data['all_students_ids']) ? $data['all_students_ids'] : '';

        $unallocated_students_array = array();
        $allocated_students_array = array();
        $msg_con = false;
        if(count($students_array) > 0){
            foreach ($students_array as $value) {
                //For Allocated students to unallocate 
                if($data['type'] == 'allocated'){
                    $unallocated_students_array[] = $value;
                    $query = "UPDATE assist_participant_batch_meta SET ".$which_field."=".$data['user_initiated_check']." WHERE pid=".$value." AND batch_id=".$batch_id;
                    Yii::$app->db->createCommand($query)->execute();
                    
                    $query_participant = "UPDATE assist_participant SET participant_status=2 WHERE id=".$value;
                    Yii::$app->db->createCommand($query_participant)->execute();
                } 
                //For unallocated students to allocate
                if($data['type'] == 'not_allocated'){
                    $allocated_students_array[] = $value;
                    
                    //$query = "UPDATE assist_participant_batch_meta SET ".$which_field."=".$data['user_initiated_check']." WHERE pid=".$value." AND batch_id=".$batch_id;
                    //Yii::$app->db->createCommand($query)->execute();

                    //Get All batch records
                    $get_max_batch_data = "SELECT id,pid FROM assist_participant_batch_meta WHERE pid=".$value;
                    $get_last_batch_id = Yii::$app->db->createCommand($get_max_batch_data)->queryAll();
                    if(count($get_last_batch_id) > 0){
                        //Delete Records from batch meta table last record
                        $delete_batch_meta = "DELETE FROM assist_participant_batch_meta WHERE id=".$get_last_batch_id[count($get_last_batch_id) - 1]['id'];  
                        Yii::$app->db->createCommand($delete_batch_meta)->execute();
                    }
                    //update on assist participant table
                    //$get_pdata = "SELECT modules_allowed FROM assist_participant WHERE id=".$value;
                    //$get_participant_data = Yii::$app->db->createCommand($get_pdata)->queryOne()['modules_allowed'] + 1 ;

                    //Update module allowed increase by 1 at assist participant
                    /*
                    $query_participant_module_allowed = "UPDATE assist_participant SET modules_allowed = $get_participant_data WHERE id=$value";
                    */

                    //Yii::$app->db->createCommand($query_participant_module_allowed)->execute();
                }
                $msg_con = true;//Just temp.
            }
        }
        $all_students_ids = implode(",",$all_students_array); 
        if($msg_con){
            Yii::$app->session->setFlash(isset($msg)?$msg:"", isset($msg_content)?$msg_content:"");
        }

        return $this->redirect(array('all_batch_list'));
        /*
        return $this->redirect(array('all_students','pid'=>$all_students_ids,
            'type'=>$data['type'],'module_name'=>$data['module_name'],'module_date'=>$data['module_date'],'batch_id'=>$data['batch_id']));
        */
    }//End of function:batch_action_vice_versa//
    /**
    *By:CDO 08 May 2019
    *Purpose:Posssible Special Modules
    *
    */
    public function actionPossible_special_modules($paticipant_ids = NULL){
        if($paticipant_ids!=NULL){
            $participant_data = DvRegistration::find()->where(['participant_status'=>1])->andWhere(['IN','id',$paticipant_ids])->all();
        }else{
            $participant_data = DvRegistration::find()->where(['participant_status'=>1])->all();
        }
        $data = array();
        $ids_array = array();
        foreach($participant_data as $val){
            // if($val['modules_allowed'] > $val['modules_completed']+1 && $val['modules_allowed'] != 1){
           
                $batch_meta_data = DvParticipantBatchMeta::find()
                                ->where(['pid'=>$val['id']])
                                ->createCommand()
                                ->queryall();
                                //464 & 474testing id : $val['id'] // && $val['id'] == 474
                    
                $ongoing_upcoming_special_batch = array();
                if(count($batch_meta_data) > 0){
                    //either ongoing or upcoming batch 
                    foreach ($batch_meta_data as $batch_meta_value) {
                    //for($i = 0 ; $i < count($batch_meta_data) ; $i++){ 
                        $ongoing_upcoming_batch_selection = Yii::$app->db->createCommand("SELECT * FROM assist_batches WHERE UNIX_TIMESTAMP(STR_TO_DATE(end_date,'%d-%m-%Y')) > ".strtotime(date('Y-m-d'))." AND id=".$batch_meta_value['batch_id'])->queryAll();

                        if(count($ongoing_upcoming_batch_selection) > 0){
                            foreach ($ongoing_upcoming_batch_selection as $ong_upc_batch_value) {
                                $ongoing_upcoming_special_batch = DvModuleModel::find()
                                                        ->where(['id'=>$ong_upc_batch_value['module']])
                                                        ->andWhere(['category_type'=>'Special'])
                                                        ->createCommand()
                                                        ->queryall();
                                
                            }
                        }
                    }
                    if(count($ongoing_upcoming_special_batch) > 0) {
                        continue;
                    }
                    //Get all Completed batch's data (all Modules)
                    $completed_modules_array = array();
                    foreach ($batch_meta_data as $value) {
                        $batch_data = Yii::$app->db->createCommand("SELECT * FROM assist_batches WHERE UNIX_TIMESTAMP(STR_TO_DATE(end_date,'%d-%m-%Y')) < ".strtotime(date('Y-m-d'))." AND id=".$value['batch_id'])->queryAll();
                        //echo "<pre>"; print_r($batch_data);die;
                        if(count($batch_data) > 0){
                            //completed all modules of the student
                          $completed_modules_array[] = $batch_data[0]['module'];  
                        }
                    }

                    //print_r($completed_modules_array); die;

                    // $core_modules_array = array();
                    $completed_special_modules_array = array();                  

                    foreach ($completed_modules_array as $value) {
                        //get special modules
                        $module_special = DvModuleModel::find()
                                        ->where(['id'=>$value])
                                        ->andWhere(['category_type'=>'Special'])
                                        ->createCommand()
                                        ->queryall();

                        if(count($module_special) > 0){
                            $completed_special_modules_array[] = $module_special[0]['id'];
                        }
                    }//End of Module loop 
                    
                    $course_data = DvCourseModel::find()
                                    ->where(['id'=>$val['course']])
                                    ->createCommand()
                                    ->queryAll();
                    $total_course_special_module = explode(',',$course_data[0]['special_module']); 
                    $special_module_left = count($total_course_special_module) > 0 ? array_diff($total_course_special_module,$completed_special_modules_array) : '';
                    
                    $special_all_module_names = '';
                    $special_all_module_ids = array();
                    if(count($special_module_left) > 0){
                        foreach($special_module_left as $module_val_left){
                            $module_special = DvModuleModel::find()
                                        ->where(['id'=>$module_val_left])
                                        ->one();
                            $prerequisite_module = $module_special['prerequisite_module'];
                            $prerequisite_module_array = explode(',',$prerequisite_module);

                            $completed_prerequisite_modules = array_intersect($prerequisite_module_array,$completed_modules_array);
                            $left_prerequisite_modules = array_diff( $prerequisite_module_array , $completed_prerequisite_modules );

                            if(!$left_prerequisite_modules){
                                 
                                $special_all_module_result = DvModuleModel::find()->select('module_name')->where(['id'=>$module_val_left])->one();

                                if($special_all_module_result){
                                    //possible al special modules
                                    $special_all_module_names .= $special_all_module_result->module_name.",";
                                    $special_all_module_ids[] = $module_val_left;
                                }
                            }
                        }
                    }

                    // echo "<pre>"; print_r($special_all_module_ids); die;
                    if(empty($special_all_module_ids)){
                        continue;
                    }

                    $completed_special_module_names = '';
                    $completd_special_ids = '';
                    foreach($completed_special_modules_array as $module_val_completed){
                        $special_compl_module_result = DvModuleModel::find()->select('module_name')->where(['id'=>$module_val_completed])->one();
                        if($special_compl_module_result){
                            $completd_special_ids .= $module_val_completed;
                            $completed_special_module_names .= $special_compl_module_result->module_name.",";
                        }
                    }

                    $key = implode(",",$special_all_module_ids);

                    if(array_key_exists($key,$data)){
                        $data[$key]['possible_special_all_modules'] = rtrim($special_all_module_names,',');
                        $data[$key]['completed_special_modules'] = rtrim($completed_special_module_names,',');
                        $data[$key]['students'] = $data[$key]['students']+1;
                        $data[$key]['ids'] = $data[$key]['ids'].','.$val['id'];
                        $data[$key]['completd_special_ids'] = rtrim($completd_special_ids,',');
                    }else{
                        $data[$key]['possible_special_all_modules'] = rtrim($special_all_module_names,',');
                        $data[$key]['completed_special_modules'] = rtrim($completed_special_module_names,',');
                        $data[$key]['students'] = 1;
                        $data[$key]['ids'] = $val['id'];
                        $data[$key]['completd_special_ids'] = rtrim($completd_special_ids,',');
                    }
                 }//End of IF//check batch id table participant found

           //}//End of If//check moduled allowed criteria
        }//End of foreach($participant_data as $val)//
      //  echo "<pre>"; print_r($ids_array); echo "**"; print_r($data); die;
        if($paticipant_ids == NULL){
            $pages = new Pagination(['totalCount' => count($data),'PageSize' => 10]);
            $data = array_slice($data,$pages->offset,$pages->limit); 
            return $this->render('special_modules', ['model'=>$data,'pages' => $pages]);
        }else{
            return $data;
        }

    }//End of function:actionPossible_special_modules//
    
    /**
    By:PP 13 May 2019 
    Purpose: Getting student listing via post
    */
    public function actionStudent_list_special(){
        $data = yii::$app->request->get();
        if(isset($data) && isset($data['students_id']) && $data['students_id'] !=''){
            $students_id = explode(',',$data['students_id']);
            $module_data = DvRegistration::find()->WHERE(['in', 'id', $students_id]);
            //For Pagination
            $query = $module_data;
            $countQuery = clone $query;
            $pages = new Pagination(['totalCount' => $countQuery->count(),'PageSize' => 10]);
            $module = $query->offset($pages->offset)
                            ->limit($pages->limit)
                            ->createCommand()
                            ->queryAll();
            //get batch data
            $common_special_batch_data = $this->actionPossible_special_modules($students_id);
            //Begin of left Special module
            $main_filter_special_batch = array();
            for($i=0 ; $i<count($students_id);$i++){
                $single_student = DvRegistration::find()->WHERE(['id'=>$students_id[$i]])->one();
                $course_data = '';
                $course_data = DvCourseModel::find()->where(['id'=>$single_student->course])->one();
                $special_module = '';
                $special_module = $course_data->special_module;
                $special_module_array = array();
                $special_module_array = explode(',', $special_module);
                $students_special_data = '';
                $students_special_data = $this->actionPossible_special_modules($students_id[$i]);
                $special_module = '';
                foreach ($students_special_data as $key => $value_) {
                    $special_module = explode(',',$value_['completd_special_ids']);
                }
                $left_special_array = array();
                $left_special_array = array_diff($special_module_array,$special_module);
                $unallocated_special_modules_name = '';
                foreach ($left_special_array as $vall) {
                    $module_data = '';
                    $module_data = DvModuleModel::find()->where(['id'=>$vall])->one();
                    $unallocated_special_modules_name .= $module_data->module_name.',<br>';   
                }
                $main_filter_special_batch[$students_id[$i]] = $unallocated_special_modules_name; 
            }
            //End of left Special module
            return $this->render('students_list_special',['module'=>$module,'pages' => $pages,
                'common_special_batch_data'=>$common_special_batch_data,
                'unallocated_special_batch'=>$main_filter_special_batch]);
        }else{
            return $this->redirect('possible_special_modules');
        }
    }//End of function:actionStudent_list_special//
    
    /**
    By:PP 13 May 2019
    Purpose:display special batch list
    */ 
    public function actionDisplay_special_batch_list(){
        $data = Yii::$app->request->post() ;
        if(isset($data) && isset($data['all_students_ids']) && $data['all_students_ids'] !=''){
            $total_selected = $data['total_selected'];
            $all_students_ids = explode(',', $data['all_students_ids']);

            $heighest_batch_enddate = date("Y/m/d");
            // $heighest_batch_enddate_arr = array();
            
            foreach($all_students_ids as $val){
                $participant_batch_modules = DvParticipantBatchMeta::find()->where(["pid"=>$val])->orderBy(['id'=>SORT_DESC])->createCommand()->queryAll();

                
                if(count($participant_batch_modules)){
                    $last_batch_id = $participant_batch_modules[0]['batch_id'];
                   //  $heighest_batch_enddate_arr[]  $last_batch_id;
                    $batch_data = DvAssistBatches::find()->where(["id"=>$last_batch_id])->one();
                    $batch_enddate = $batch_data['end_date'];
                } 
                

                if(strtotime($heighest_batch_enddate) < strtotime($batch_enddate) ){
                    $heighest_batch_enddate = $batch_enddate;
                }

            }


            $all_students_ids = $data['all_students_ids'];

            $batch_allotment_details = $data['batch_allotment_details']; 

            //get only special batch data
            $only_special_modules = ArrayHelper::map(DvModuleModel::find()->where(['category_type'=>'Special'])->all(),'id','module_name');
            if(count($only_special_modules) > 0){
                $batch_special_array = array_keys($only_special_modules); 
            }

            //Get all ongoing batch
            $batch_meta = ArrayHelper::map(Yii::$app->db->createCommand("SELECT * FROM assist_batches_meta WHERE meta_key = 'running_batch_status' AND meta_value = '3'")->queryAll(),'mid','mid');
            $only_key_batch_meta = array_keys($batch_meta);

            if(count($batch_special_array) > 0 && count($only_key_batch_meta) > 0){
                //Get Batch details
                $batch_data = DvAssistBatches::find()
                            ->select(['assist_batches.*','assist_module.module_name',"concat(assist_users.first_name,' ',assist_users.last_name) as trainer_name"])
                            ->where(['in','assist_batches.id',$only_key_batch_meta])
                            ->andWhere(['in','assist_module.id',$batch_special_array])
                            ->leftJoin('assist_users','assist_batches.trainer = assist_users.id')
                            ->leftJoin('assist_module','assist_batches.module = assist_module.id')
                            ->where(['>',"UNIX_TIMESTAMP(STR_TO_DATE(assist_batches.start_date,'%d-%m-%Y'))",strtotime($heighest_batch_enddate)]);
            }else{
                $batch_data = array();
            }
            //For Pagination 
            $query = $batch_data;
            $countQuery = clone $query;
            $pages = new Pagination(['totalCount' => $countQuery->count(),'PageSize' => 100]);
            $batch_all_data = $query->offset($pages->offset)
                            ->limit($pages->limit)
                            ->createCommand()
                            ->queryAll();
            
            return $this->render('special_batch_list',['batch_data'=>$batch_all_data,'total_selected'=>$total_selected,'batch_ongoing_array'=>$batch_special_array,'pages' => $pages,'batch_allotment_details'=>$batch_allotment_details,'all_students_ids'=>$all_students_ids]);
        }else{
            return $this->redirect('possible_special_modules');
        }

    }
    /*public function actionDisplay_special_batch_list(){
        $data = Yii::$app->request->post() ;
        if(isset($data) && isset($data['all_students_ids']) && $data['all_students_ids'] !=''){
            $total_selected = $data['total_selected'];
            $all_students_ids = $data['all_students_ids'];
            $batch_allotment_details = $data['batch_allotment_details']; 

            //get only special batch data
            $only_special_modules = ArrayHelper::map(DvModuleModel::find()->where(['category_type'=>'Special'])->all(),'id','module_name');
            if(count($only_special_modules) > 0){
                $batch_special_array = array_keys($only_special_modules); 
            }

            //Get all ongoing batch
            $batch_meta = ArrayHelper::map(Yii::$app->db->createCommand("SELECT * FROM assist_batches_meta WHERE meta_key = 'running_batch_status' AND meta_value = '3'")->queryAll(),'mid','mid');
            $only_key_batch_meta = array_keys($batch_meta);

            if(count($batch_special_array) > 0 && count($only_key_batch_meta) > 0){
                //Get Batch details
                $batch_data = DvAssistBatches::find()
                            ->select(['assist_batches.*','assist_module.module_name',"concat(assist_users.first_name,' ',assist_users.last_name) as trainer_name"])
                            ->where(['in','assist_batches.id',$only_key_batch_meta])
                            ->andWhere(['in','assist_module.id',$batch_special_array])
                            ->leftJoin('assist_users','assist_batches.trainer = assist_users.id')
                            ->leftJoin('assist_module','assist_batches.module = assist_module.id');
            }else{
                $batch_data = array();
            }
            //For Pagination 
            $query = $batch_data;
            $countQuery = clone $query;
            $pages = new Pagination(['totalCount' => $countQuery->count(),'PageSize' => 100]);
            $batch_all_data = $query->offset($pages->offset)
                            ->limit($pages->limit)
                            ->createCommand()
                            ->queryAll();
            
            return $this->render('special_batch_list',['batch_data'=>$batch_all_data,'total_selected'=>$total_selected,'batch_ongoing_array'=>$batch_special_array,'pages' => $pages,'batch_allotment_details'=>$batch_allotment_details,'all_students_ids'=>$all_students_ids]);
        }else{
            return $this->redirect('possible_special_modules');
        }

    }*/ //End of function:actionDisplay_special_batch_list//

    public function actionAllocate_students_special_batch(){
        $data = Yii::$app->request->post();
        if(isset($data) && isset($data['all_students']) && $data['all_students'] !=''){
            $total_selected = $data['total_selected'];
            $batch_allotment_details = $data['batch_allotment_details'];
            $batch_id = $data['batch_id'];
            $all_students_ids = explode(',',$data['all_students']);
            //get all batch details
            $batch_data = Yii::$app->db->createCommand("SELECT id,start_date FROM assist_batches WHERE id=".$batch_id)->queryOne();
            $msg_condition = '';
             
            $allocated_batch_students = array();
            $unallocated_batch_students = array();

            if(count($all_students_ids) > 0){
                for($i = 0 ; $i < count($all_students_ids) ; $i++){
                    //get students all batch data
                    $batch_meta_data = Yii::$app->db->createCommand("SELECT * FROM assist_participant_batch_meta WHERE pid=".$all_students_ids[$i])->queryAll();
                    $all_batch_array = array();

                    //get batch ids of student
                    foreach($batch_meta_data as $value) {
                        $all_batch_array[] = $value['batch_id'];
                    } 
                    //main batch data
                    if(!in_array($batch_id,$all_batch_array)){ 
                        $allocated_batch_students[] = $all_students_ids[$i];
                        Yii::$app->db->createCommand()->insert('assist_participant_batch_meta', [ 'pid' => $all_students_ids[$i],'batch_id'=>$batch_id ])->execute();
                    }else{
                        $unallocated_batch_students[] = $all_students_ids[$i];
                    }
                }
            }
            //need to set condition
            $msg = "success";
            $msg_content = count($unallocated_batch_students) > 0 ? "Some student/s are not allocat due to batch are already ongoing !" : "Special batch has been allocated successfully.";
            
            Yii::$app->session->setFlash($msg?$msg:"", $msg_content?$msg_content:"");

            return $this->redirect(array('display_special_batch_list','total_selected'=>$total_selected,
                'batch_allotment_details'=>$batch_allotment_details,'all_students_ids'=>$data['all_students']));
        }else{
            return $this->redirect('possible_special_modules');
        }
    }
    
    //End of function:actionAllocate_students_batch//

    /**
    By:PP 13 May 2019
    Purpose:filter fo special batch
    */

    public function actionStudents_filter_special(){
        $filter_array = array();
        $data = Yii::$app->request->get();

        if(!isset($data['students_id'])){
            return $this->redirect(array('possible_special_modules'));
        }
        $students_id = isset($data['students_id']) ? explode(',',trim($data['students_id'])) : '';
        $module_data = count($students_id) > 0 ? DvRegistration::find()->where(['in', 'id', $students_id]) : '';
        $custom_query = '';
        //For email_search filter goes here 
        if(!empty($data['email_search'])){
            //echo "<pre>"; print_r($module_data); die;
            $custom_query = $module_data->andWhere(['like','email',trim($data['email_search'])]);
            $filter_array['email_search'] = $data['email_search'];
        }

        //For batch opt out filter goes here
        if(!empty($data['available_batch_opt'])){
            $custom_query = $module_data->andWhere(['available_batch_opt'=>$data['available_batch_opt']]);
            $filter_array['available_batch_opt'] = $data['available_batch_opt'];
        }
         
        if(!empty($custom_query)){
            //For Pagination
            $query = $custom_query;
            $countQuery = clone $query;
            $pages = new Pagination(['totalCount' => $countQuery->count(),'PageSize' => 10]);
            $module = $query->offset($pages->offset)
                            ->limit($pages->limit)
                            ->createCommand()
                            ->queryAll();
            //get batch data
            $common_special_batch_data = $this->actionPossible_special_modules($students_id);
            //Begin of left Special module list student wise
            $main_filter_special_batch = array();
            for($i=0 ; $i<count($students_id);$i++){
                $single_student = DvRegistration::find()->WHERE(['id'=>$students_id[$i]])->one();
                $course_data = '';
                $course_data = DvCourseModel::find()->where(['id'=>$single_student->course])->one();
                $special_module = '';
                $special_module = $course_data->special_module;
                $special_module_array = array();
                $special_module_array = explode(',', $special_module);
                $students_special_data = '';
                $students_special_data = $this->actionPossible_special_modules($students_id[$i]);
                $special_module = '';
                foreach ($students_special_data as $key => $value_) {
                    $special_module = explode(',',$value_['completd_special_ids']);
                }
                $left_special_array = array();
                $left_special_array = array_diff($special_module_array,$special_module);
                $unallocated_special_modules_name = '';
                foreach ($left_special_array as $vall) {
                    $module_data = '';
                    $module_data = DvModuleModel::find()->where(['id'=>$vall])->one();
                    $unallocated_special_modules_name .= $module_data->module_name.',<br>';   
                }
                $main_filter_special_batch[$students_id[$i]] = $unallocated_special_modules_name; 
            }
            //End of left Special module
            return $this->render('students_list_special',['module'=>$module,'pages' => $pages,'filter_array'=>$filter_array,'common_special_batch_data'=>$common_special_batch_data,
                'unallocated_special_batch'=>$main_filter_special_batch]);

        }else{
            return $this->redirect(array('student_list_special', 'students_id'=>$data['students_id']));
        }
    }//End of actionStudents_filter_special()---//

}//End of Main Class//
?>