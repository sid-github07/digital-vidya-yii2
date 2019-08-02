<?php
namespace app\controllers;
use Yii;
use app\models\DvUsers;
use app\models\DvUserMeta;
use app\models\DvUsersRole;
use app\models\DvRegistration;
use yii\web\Controller;
use yii\data\Pagination; 
use app\models\DvBatchModel;
use app\models\DvBatchSessionModel;
use yii\helpers\ArrayHelper;
use app\models\DvModuleModel;

class DvBatchController extends Controller {
    /**
     * @PP  - 16 April 2019
     * Purpose:Batch Form , Edit and Listing.
     * */
    public function actionIndex(){
        $id = Yii::$app->request->get('id'); 
        if($id!= '' && $id!=NULL){
            //For Edit Form
            $model =  DvBatchModel::findOne($id);
        }else{
            //For View Form
            $model =  new DvBatchModel();
        }
        //---Begin:For disply of form ,just getting data---//
        $module_ids = ArrayHelper::map(DvModuleModel::find()->all(), 'id', 'module_name');
        $user_meta_data = ArrayHelper::map(DvUsers::find()->where(['department'=>'7'])->all(),'id','id'); 
        
        $trainer_array = array();
        $sessions_batch_data = array();

        foreach ($user_meta_data as $key => $value) {
            //for trainner
            $users_data = DvUsers::find()->where(['id'=>$key])->one();
            $trainer_array[$key] = ucfirst($users_data['first_name'].' '.$users_data['last_name']);
        }
        $trainer_coordinator_ids = array();
        $open_seats = array();
        for($i = 1 ; $i <= 25 ; $i++){
            $open_seats[$i] = $i;
        } 
        $number_of_sessions = array();
        for($i = 1 ; $i <= 25 ; $i++){
            $number_of_sessions[$i] = $i;
        }
        $batch_day = array(
            'sat+sun'=>'sat+sun',
            'sat+wd'=>'sat+wd',
            'sun+wd'=>'sun+wd',
            'wd'=>'wd',
            'sat'=>'sat',
            'sun'=>'sun'
        );
        $time_duration = array();
        //---End:For disply of form ,just getting data---//
        //---Begin:For Post Data New Entry & Edit Entry---//
        if($model->load(Yii::$app->request->post())){
            $data = Yii::$app->request->post();
            //For Batch Master 
            $data = $data['DvBatchModel'];
            $model->module_id = $data['module_id'];
            $model->created_by = Yii::$app->getUser()->identity->id;
            $model->trainer = $data['trainer'];
            $model->trainer_coordinator = !empty($_POST['trainer_coordinator_r']) ? $_POST['trainer_coordinator_r'] : NULL ;
            $model->open_seats = $data['open_seats'];
            $model->start_date = date('Y-m-d',strtotime($data['start_date']));
            $model->number_of_sessions = $data['number_of_sessions'];
            $model->batch_day = $data['batch_day'];
            $model->time_duration = $data['time_duration'];
            $model->joining_link = $data['joining_link'];
            $model->status = $_POST['status'];
            //echo "<pre>"; print_r($model); die;
            if($model->save()){
                if(!empty($data['number_of_sessions'])){
                    $all_session_date = Yii::$app->request->post('session_date');
                    $all_session_time = Yii::$app->request->post('session_time');
                    $all_session_duration = Yii::$app->request->post('session_duration');
                    $all_session_master_id = Yii::$app->request->post('battch_session_id');
                    //print_r(count($all_session_master_id)); die;
                    $master_batch_id =  $model->id;
                    for($i = 0 ; $i < count($all_session_date) ; $i++){
                        $session_model = '';
                        $condition = '';
                        if(count($all_session_master_id) == 0){
                            //For New Entry
                            $session_model = new DvBatchSessionModel();
                            $condition = true;
                        }else{
                            //For Edit Entry
                            $session_model = DvBatchSessionModel::findOne($all_session_master_id[$i]);
                            //if($session_model->batch_master_id!=''){
                                $condition = ($all_session_master_id[$i] == $session_model->id) && ($master_batch_id == $session_model->batch_master_id);
                            //}else{
                                //$condition = true;
                            //}
                        }
                        if($condition){
                            $session_model->batch_master_id = $master_batch_id;
                            $session_model->session_date = !empty($all_session_date[$i]) ? date('Y-m-d',strtotime($all_session_date[$i])) : NULL;
                            $session_model->session_time = !empty($all_session_time[$i]) ? date('h:i A',strtotime($all_session_time[$i])) : NULL;
                            $session_model->session_duration = !empty($all_session_duration[$i]) ? $all_session_duration[$i] : NULL;
                            $session_model->save();
                        }
                    }//End of inner loop()//
                } 
                
                $msg = "success";
                $msg_content = !empty($id) ? "Record has been updated successfully." : "Record has been added successfully.";
            }else{
                $msg = "error";
                $msg_content = "Something went wrong !";
            }
            Yii::$app->session->setFlash($msg?$msg:"", $msg_content?$msg_content:"");
            if($id!= '' && $id != NULL){
                return $this->redirect(['dv-batch/index?id='.$id]);
            }else{
                return $this->redirect(['dv-batch/index']);
            }
        } 
        //---End:For Post Data New Entry & Edit Entry---//
        //---Begin:For Edit---//     
        if($id!= '' && $id!=NULL){
            //For fetch trainer's co-ordinator data
            $dv_users = $model->trainer_coordinator!='' ? DvUsers::find()->where(['id'=>$model->trainer_coordinator])->one() : '';
            if(!empty($dv_users)){
                $dv_user_full_name = ucfirst($dv_users['first_name'].' '.$dv_users['last_name']);
                $trainer_coordinator_ids[$dv_users['id']] = $dv_user_full_name;
            }
            //For Sessions of batch data
            $sessions_batch_data = DvBatchSessionModel::find()->where(['batch_master_id'=>$model->id])->createCommand()->queryAll();
        }           
        //---Begin:For view---//
        return $this->render('batch_action_view',
            [
                'model'=>$model,
                'module_ids'=>$module_ids,
                'trainer_ids'=>$trainer_array,
                'trainer_coordinator_ids'=>$trainer_coordinator_ids,
                'open_seats'=>$open_seats,
                'number_of_sessions'=>$number_of_sessions,
                'batch_day'=>$batch_day,
                'time_duration'=>$time_duration,
                'sessions_batch_data'=>$sessions_batch_data,
            ]
        );
        //---End:For view---//

    }//End of function:actionIndex() 
    /**
     * @PP  - 16 April 2019
     * Purpose:For getting Batch Listing
     * */
    public function actionBatch_list(){
        $query = DvBatchModel::find()
                ->select(['assist_batch_master.*','assist_module.module_name',"concat(assist_users.first_name,' ',assist_users.last_name) as trainer_name"])
                ->leftJoin('assist_module','assist_batch_master.module_id = assist_module.id')
                ->leftJoin('assist_users','assist_batch_master.trainer = assist_users.id');
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(),'PageSize' => 10]);
        $batch_listing_data = $query->offset($pages->offset)
                        ->limit($pages->limit)
                        ->orderBy(['id'=>SORT_DESC])
                        ->createCommand()  
                        ->queryAll();
         
        return $this->render('batch_list_view',
            [   
                'batch_listing_data'=>$batch_listing_data,
                'pages' => $pages
            ]
        );
         
    }//End of function:actionBatch_list//

    /**
     * @PP  - 16 April 2019
     * Purpose:For getting trainer's Co-ordinator
     * */
    public function actionGet_coordinator(){
        $trainer_id = Yii::$app->request->post('id');
        $option = '';
        $trainer_coordinator = DvUserMeta::find()->where(['uid'=>$trainer_id,'meta_key'=>'coordinator'])->one();
        if(!empty($trainer_coordinator)){
            $dv_users = DvUsers::find()->where(['id'=>$trainer_coordinator['meta_value']])->one();
            if(!empty($dv_users)){
                $dv_user_full_name = ucfirst($dv_users['first_name'].' '.$dv_users['last_name']);
                $option = "<option value=".$dv_users['id'].">".$dv_user_full_name."</option>";
            }
        }
        return $option;
    }//End of function:actionGet_coordinator()//

    /**
     * @PP  - 16 April 2019
     * Purpose:For Edit Form
     * */
    public function actionEdit_batch_form(){
        $id = Yii::$app->request->get('id');
        if(!empty($id) && $id != NULL){
            return $this->redirect(['dv-batch/index?id='.$id]);
        }else{
            return $this->redirect(['dv-batch/index']);
        }
    }//End of function:actionEdit_batch_form()//

    /**
     * @PP  - 16 April 2019
     * Purpose:For batch wise details view
     * */
    public function actionBatch_details(){
        $id = Yii::$app->request->get('id');
         if(!empty($id) && $id != NULL){
            $model = DvBatchModel::find()
                    ->select(['assist_batch_master.*','assist_module.module_name',"concat(assist_users.first_name,' ',assist_users.last_name) as trainer_name","concat(au.first_name,' ',au.last_name) as coordinator_name"])
                    ->where(['assist_batch_master.id'=>$id])
                    ->leftJoin('assist_module','assist_batch_master.module_id = assist_module.id')
                    ->leftJoin('assist_users','assist_batch_master.trainer = assist_users.id')
                    ->leftJoin('assist_users as au','assist_batch_master.trainer_coordinator = au.id')
                    ->createCommand()  
                    ->queryAll();
                //For fetch trainer's co-ordinator data
                $dv_users = $model[0]['trainer_coordinator']!='' ? DvUsers::find()->where(['id'=>$model[0]['trainer_coordinator']])->one() : '';
                if(!empty($dv_users)){
                    $dv_user_full_name = ucfirst($dv_users['first_name'].' '.$dv_users['last_name']);
                    $trainer_coordinator_ids[$dv_users['id']] = $dv_user_full_name;
                }
                //For Sessions of batch data
                $sessions_batch_data = DvBatchSessionModel::find()->where(['batch_master_id'=>$model[0]['id']])->createCommand()->queryAll();
                
                return $this->render('batch_details_view',
                    [
                        'model'=>$model,
                        'sessions_batch_data'=>$sessions_batch_data
                    ]
                );
        }else{
            return $this->redirect(['dv-batch/index']);
        } 

    }//End of function:actionBatch_details()//

    /**
     * @PP  - 16 April 2019
     * Purpose:For Status Enable & Disable
     * */
    public function actionBatch_status_update(){
        $id = Yii::$app->request->get('id');
        if($id != NULL && !empty($id)){ 
            $model = DvBatchModel::find()->where(['id' => $id])->one();
            $msg = '';
            if($model->status){
                $model->status  =  0;
                $msg = "disable";
            }else{
                $model->status  =  1;
                $msg = "enable";
            }
            $msg = $model->save() ? $msg : 'error';
            echo $msg;
        }
    }//End of function:actionBatch_status_update()//

}//End of class:DvBatchController