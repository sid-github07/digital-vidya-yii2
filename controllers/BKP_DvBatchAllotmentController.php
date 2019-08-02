<?php 
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\DvParticipantBatchMeta;
use app\models\DvRegistration;
use app\models\DvParticipantModules;
use app\models\DvModuleModel;
use app\models\DvCourseModel;
use yii\data\Pagination; 

class DvBatchAllotmentController extends Controller {
	
	/* function to suggest the next batch to the student*/

    public function actionIndex() {
    	
    	$participant_data = DvParticipantBatchMeta::find()->all();
        $data = array();
    	$result = array();
	    $participant_ids = '';
    	foreach($participant_data as $val){


            if($val['total_allowed_module'] > $val['total_completed_module']+1 && $val['total_allowed_module']!=1){

                $participant_result = DvRegistration::find()->where(['id'=>$val['pid']])->one();
                $course = $participant_result['course'];

                $module_id = DvCourseModel::find()->select('core_modules')->where(['id'=>$course])->one();                
                $module_id = explode(',',$module_id->core_modules);
                

                $current_batch_id = $val['running_batch_id'];
                // echo $current_batch_id;
                

                $batch_data = DvParticipantModules::find()->where(["id"=>$current_batch_id])->one();
                
                // $module = $batch_data['module'];
                $batch_enddate = $batch_data['end_date'];
                $batch_enddate = date('d-m-Y', strtotime($batch_enddate. ' + 7 day'));

                $batch_startdate = $batch_data['start_date'];                
                $batch_day = date('D', strtotime($batch_startdate));

                $endDate = date('d-m-Y', strtotime($batch_data['end_date']));
                $checkdate = date('d-m-Y', strtotime(date("Y/m/d"). ' + 14 days'));
             
                if(strtotime($endDate) < strtotime($checkdate) ){
                    
        			$completed_modules_id = $val['completed_modules_id'];
        			$completed_modules_id = explode(",",$completed_modules_id);

        			$total_allowed_module = $val['total_allowed_module'];
        			$remaining_modules = array();
        			if($total_allowed_module==6){
        				$remaining_modules = array_diff($module_id, $completed_modules_id);
        			}else if($total_allowed_module==5){
        				$remaining_modules = array_diff($module_id, $completed_modules_id);
                        
        			}
                    /*else{
        				$remaining_modules = $module_id;
        			}*/
                    $key = implode(",",$remaining_modules);
                    
        			$module_id_array = explode(",",$key);
                    $module_names = '';
                    foreach($module_id_array as $module_val){
                        $module_result = DvModuleModel::find()->select('module_name')->where(['id'=>$module_val])->one();
                        if($module_result){
                            $module_names .= $module_result->module_name.",";
                        }
                    }

                    $module_names = rtrim($module_names,",");
                    
                	if(array_key_exists($key,$data)){
        				$data[$key]['modules'] = $module_names;
        				$data[$key]['students'] = $data[$key]['students']+1;
        				$data[$key]['date'] = $batch_enddate;
        				$data[$key]['day'] = $batch_day;
        				$data[$key]['ids'] = $data[$key]['ids'].','.$val['pid'];
        			}else{
                        $data[$key]['modules'] = $module_names;
        				$data[$key]['students'] = 1;
        				$data[$key]['date'] = $batch_enddate;
                        $data[$key]['day'] = $batch_day;
        				$data[$key]['ids'] = $val['pid'];
    				}
                }
			}
    	}
        //For Pagination new way implementation
        $pages = new Pagination(['totalCount' => count($data),'PageSize' => 10]);
        $data = array_slice($data,$pages->offset,$pages->limit); 
        return $this->render('index', ['model'=>$data,'pages' => $pages]);
    }

	/* function to display the list of student when clicked from open sales form page */
    public function actionDisplay_students_list(){
    	$data = yii::$app->request->post();
        $students_id = $data['students_id'];
        $batch_allotment_details = $data['batch_allotment_details'];  
		$students_id = explode(',',$students_id);
		return $this->redirect(array('student_list', 'students_id'=>$students_id,'batch_allotment_details'=>$batch_allotment_details));
    }
	/* function to get the list of student when clicked from open sales form page */
    public function actionStudent_list(){
    	$data = yii::$app->request->get();
    	$students_id = $data['students_id'];
    	$module_data = DvRegistration::find()->WHERE(['in', 'id', $students_id]);
        //For Pagination
        $query = $module_data;
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(),'PageSize' => 10]);
        $module = $query->offset($pages->offset)
                        ->limit($pages->limit)
                        ->createCommand()
                        ->queryAll();
        $batch_allotment_details = isset($data['batch_allotment_details']) ? $data['batch_allotment_details'] : '' ;                
        return $this->render('students_list',['module'=>$module,'pages' => $pages,'batch_allotment_details'=>$batch_allotment_details]);
    }
    
    /**
    *By : PP 24 April 2019
    *Purpose : function to get all ongoing batch details
    **/
    public function actionDisplay_batch_list(){
        $data = Yii::$app->request->post();
        $total_selected = $data['total_students'];
        $batch_allotment_details = $data['batch_allotment_details']; // batch_allotment_details
        return $this->redirect(array('batch_list', 'total_selected'=>$total_selected,'batch_allotment_details'=>$batch_allotment_details));
    }//End of function:actionDisplay_batch_list//

    /**
    *By : PP 24 April 2019
    *Purpose : function to get all ongoing batch details
    **/
    public function actionBatch_list(){
        // die;
        $data = Yii::$app->request->get();
        $total_selected = $data['total_selected'];
        //Get ongoing batch ids
        $batch_meta = Yii::$app->db->createCommand("SELECT mid FROM assist_participant_module_meta WHERE meta_key = 'running_batch_status' AND meta_value = '0' ")->queryAll();
        if(count($batch_meta) > 0){
            $batch_ongoing_array = array();
            for($i=0 ; $i < count($batch_meta) ; $i++){
                $batch_ongoing_array[] = $batch_meta[$i]['mid'];
            }
        }

        if(count($batch_ongoing_array) > 0){
            //Get Batch details
            $batch_data = DvParticipantModules::find()
                        ->select(['assist_participant_modules.*','assist_module.module_name',"concat(assist_users.first_name,' ',assist_users.last_name) as trainer_name"])
                        ->where(['in','assist_participant_modules.id',$batch_ongoing_array])
                        ->leftJoin('assist_users','assist_participant_modules.trainer = assist_users.id')
                        ->leftJoin('assist_module','assist_participant_modules.module = assist_module.id');
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
        $batch_allotment_details = $data['batch_allotment_details'];                
        return $this->render('batch_list',['batch_data'=>$batch_all_data,'total_selected'=>$total_selected,'batch_ongoing_array'=>$batch_ongoing_array,'pages' => $pages,'batch_allotment_details'=>$batch_allotment_details]);

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
        $batch_allotment_details = $data['batch_allotment_details'];
        $students_id = isset($data['students_id']) ? explode(',',$data['students_id']) : '';
        $filter_array['students_id'] = $data['students_id']; 
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
        if(!empty($custom_query)){
            //For Pagination
            $query = $custom_query;
            $countQuery = clone $query;
            $pages = new Pagination(['totalCount' => $countQuery->count(),'PageSize' => 10]);
            $module = $query->offset($pages->offset)
                            ->limit($pages->limit)
                            ->createCommand()
                            ->queryAll();
            return $this->render('students_list',['module'=>$module,'pages' => $pages,'filter_array'=>$filter_array,'batch_allotment_details'=>$batch_allotment_details]);
        }else{
            return $this->redirect(array('student_list', 'students_id'=>$students_id,'batch_allotment_details'=>$batch_allotment_details));
        }
    }//End of function:actionStudents_filter//


    /**
    *By : PP 26 April 2019
    *Purpose : function to get all batch details
    **/
    public function actionAll_batch_list(){
        //Get All Batch details
        $batch_data = DvParticipantModules::find()
                    ->select(['assist_participant_modules.*','assist_module.module_name',"concat(assist_users.first_name,' ',assist_users.last_name) as trainer_name"])
                    ->leftJoin('assist_users','assist_participant_modules.trainer = assist_users.id')
                    ->leftJoin('assist_module','assist_participant_modules.module = assist_module.id');
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

}//End of Main class//
?>