<?php
namespace app\controllers;
use Yii;
use yii\web\Controller;
use app\models\DvModuleModel;
use app\models\DvCourse;
use app\models\DvUsers;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\data\Pagination; 
use yii\helpers\Url;
 
class DvModuleController extends Controller {

    /**
     * @CDO  - 6 April 2019
     * Purpose:For Add,Edit and Listing of Module
     * */
    public function actionIndex() {
        if(!empty(Yii::$app->request->get('id'))){
            $model = DvModuleModel::find()->where(['id' =>Yii::$app->request->get('id')])->one();
            if(empty($model)){
                return $this->redirect(['dv-module/index']); 
            }
        }else{
            $model = new DvModuleModel();
        }

        if($model->load(Yii::$app->request->post())){
            $model->created_by = Yii::$app->getUser()->identity->id;
            $data = Yii::$app->request->post();
            $data = $data['DvModuleModel'];
            $model->module_name = $data['module_name'];
            $model->mcourse = $data['mcourse'];
            $model->module_type = $data['module_type'];
            $model->number_of_weeks = $data['number_of_weeks'];
            $model->category_type = $data['category_type'];
            // $model->lms_course = $data['lms_course'];
           //  $test = !empty($data['lms_course']) ? trim(implode(',', $data['lms_course'])) : "";
            $model->lms_course =  !empty($data['lms_course']) ? trim(implode(',', $data['lms_course'])) : "";
            $model->prerequisite_module = !empty($data['prerequisite_module']) ? trim(implode(',', $data['prerequisite_module'])) : "";
            $model->status = $_POST['status'];
            if($model->save()){
                $msg = "success";
                $msg_content = !empty(Yii::$app->request->get('id')) ?  "Record has been updated successfully." : "Record has been added successfully.";
            }else{
                $msg = "error";
                $msg_content = "Something went wrong !!!";
            }
            Yii::$app->session->setFlash($msg?$msg:"", $msg_content?$msg_content:"");
            if(!empty(Yii::$app->request->get('id'))){
                return $this->redirect(['dv-module/index?id='.Yii::$app->request->get('id')]);
            }else{
                return $this->redirect(['dv-module/index']);
            }
        }//end of post

        $module_type_array = ['Self Study','Instructor led'];
        $module_type = array();
        foreach ($module_type_array as $key => $value) {
            $module_type[$value] = $value ;
        }
        $module_category_type_array = ['Special','Core','Foundation'];
        $module_category_type = array();
        foreach ($module_category_type_array as $key => $value) {
            $module_category_type[$value] = $value ;
        }

        $domain_array = array(
            'da'=>"Digital Analytics",
            'dm'=>"Digital Marketing"
        );


        $existing_module = ArrayHelper::map(DvModuleModel::find()->all(), 'id', 'module_name');
        if(!empty(Yii::$app->request->get('id'))){
            $model->prerequisite_module = !empty($model->prerequisite_module) ? explode(',', $model->prerequisite_module) : "";
            $model->lms_course = !empty($model->lms_course) ? explode(',', $model->lms_course) : "";
        }else{
           // $existing_module_selectd = array();
           //  $existing_lms_module_selectd = array();
        }
        $number_of_weeks_array = array();
        for($i = 1 ; $i <= 25 ; $i++){
            $number_of_weeks_array[$i] = $i;
        } 
        $query = DvModuleModel::find();
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(),'PageSize' => 10]);
        $module_details = $query->offset($pages->offset)
                        ->limit($pages->limit)
                        ->orderBy(['id'=>SORT_DESC])
                        ->createCommand()
                        ->queryAll();
        return $this->render('create_module',
            array('model'=>$model,
                'module_type'=>$module_type,
                'existing_module'=>$existing_module,
                'domain_array'=>$domain_array,
                'module_category_type'=>$module_category_type,
                'number_of_weeks_array'=>$number_of_weeks_array,
                'module_details'=>$module_details,
                'pages' => $pages,
            )
        );
    } // End of function:actionIndex() 
     
    /**
     * @CDO  - 6 April 2019
     * Purpose:For Edit View of Module
     * */
    public function actionModule_edit($id = NULL){
        if($id != NULL && !empty($id)){
            return $this->redirect(['dv-module/index?id='.$id]);
        }else{
            return $this->redirect(['dv-module/index']);
        }
    }// End of function:actionModule_edit() 
    
    /**
     * @CDO  - 6 April 2019
     * Purpose:For Delete of Module
     * */
    public function actionModule_delete($id = NULL){
        if($id != NULL && !empty($id)){ 
            $model = DvModuleModel::find()->where(['id' => $id])->one();
            $msg = '';
            if($model->status){
                $model->status  =  0;
                $msg = "deactivate";
            }else{
                $model->status  =  1;
                $msg = "activate";
            }

            if($model->save()){
                echo $msg;
            }else{
                echo "error";
            }
        }
    }// End of function:actionModule_delete() 

    
    /**
     * @CDO  - 19 Aprril 2019
     * Purpose:For Filter
     **/
    public function actionFilter(){
        $model = new DvModuleModel();
        $model_filter = '';
        $filter_data = array();
        if(!empty($_GET['mcourse']) || !empty($_GET['module_type']) || !empty($_GET['category_type'])){
            $mcourse = $_GET['mcourse'];
            $module_type = $_GET['module_type'];
            $category_type = $_GET['category_type'];
            $common_query = DvModuleModel::find();
            if ($mcourse != '') {
                $filter_data['mcourse'] = $mcourse;
                $model_filter = $common_query->andWhere(['assist_module.mcourse'=>$mcourse]);
            }
            if ($module_type != '') {
                $filter_data['module_type'] = $module_type;
                $model_filter = $common_query->andWhere(['assist_module.module_type'=>$module_type]);
            }
            if ($category_type != '') {
                $filter_data['category_type'] = $category_type;
                $model_filter = $common_query->andWhere(['assist_module.category_type'=>$category_type]);
            }
        }else{
            $model_filter = DvModuleModel::find();
        }
        $module_type_array = ['Self Study','Instructor led'];
        $module_type = array();
        foreach ($module_type_array as $key => $value) {
            $module_type[$value] = $value ;
        }
        $module_category_type_array = ['Special','Core','Foundation'];
        $module_category_type = array();
        foreach ($module_category_type_array as $key => $value) {
            $module_category_type[$value] = $value ;
        }
        $domain_array = array(
            'da'=>"Digital Analytics",
            'dm'=>"Digital Marketing"
        );
        $existing_module = ArrayHelper::map(DvModuleModel::find()->all(), 'id', 'module_name');
        if(!empty(Yii::$app->request->get('id'))){
            $model->prerequisite_module = !empty($model->prerequisite_module) ? explode(',', $model->prerequisite_module) : "";
            $model->lms_course = !empty($model->lms_course) ? explode(',', $model->lms_course) : "";
        }else{
           // $existing_module_selectd = array();
           //  $existing_lms_module_selectd = array();
        }
        $number_of_weeks_array = array();
        for($i = 1 ; $i <= 25 ; $i++){
            $number_of_weeks_array[$i] = $i;
        } 
        $countQuery = clone $model_filter;
        $pages = new Pagination(['totalCount' => $countQuery->count(),'PageSize' => 10]);
        $module_details = $model_filter->offset($pages->offset)
                        ->limit($pages->limit)
                        ->orderBy(['id'=>SORT_DESC])
                        ->createCommand()
                        ->queryAll();
        /*echo "<pre>";
        print_r($module_details);
        die;*/
        return $this->render('create_module',
            array('model'=>$model,
                'module_type'=>$module_type,
                'existing_module'=>$existing_module,
                'domain_array'=>$domain_array,
                'module_category_type'=>$module_category_type,
                'number_of_weeks_array'=>$number_of_weeks_array,
                'module_details'=>$module_details,
                'pages' => $pages,
                'filter_data'=>$filter_data
            )
        );
    }//End of function:actionFilter()//
    /**
    By:CDO:28 May 2018
    Purpose:get modules data from sheet's data
    */
    public function actionGet_modules_sheet_data(){
        //get existing modules data
        $modules_data = ArrayHelper::map(DvModuleModel::find()->all(), 'module_name', 'module_name');
        //get sheet's modules data
        $fetch_sheet_data = Yii::$app->runAction('dv-cron/get_batch_data');
        $new_modules_array = array();
        $course_array = ['SMM','SEO','SEM','IM','WA','DAP','DAR','CMAM','EM'];
        //DAP','DAR : da && rest of --> //dm
        $domain_array = ['DAP','DAR']; // da
        $module_array = array();
         
            foreach ($fetch_sheet_data as $sheet_data) {
                if(!in_array(trim($sheet_data['Course']),$modules_data)){
                 
                    if(trim($sheet_data['Course']) !='' && !in_array(trim($sheet_data['Course']), $module_array)){
                        //get new modules name

                        $module_array[] = trim($sheet_data['Course']);

                        if(substr(trim($sheet_data['Type']),1) == 'w' || substr(trim($sheet_data['Type']),2) == 'w'){
                            $weeks_data = '';
                            $weeks_data = explode('w',$sheet_data['Type'])[0];
                        }
                        $domain='';
                        if($sheet_data['Course'] == "DAP" || $sheet_data['Course'] == 'DAR'){
                            $domain = "da";
                        }else{
                            $domain = "dm"; 
                        }  
                        if(in_array($sheet_data['Course'], $course_array)){
                            $new_modules_array[] = [trim($sheet_data['Course']),"Instructor led","Core",$weeks_data,$domain];
                        }else{
                            $new_modules_array[] = [trim($sheet_data['Course']),"Self","Special",$weeks_data,$domain];
                        }
                    }
                } 
            }//End of inner loop
        
        /* echo "<pre>";
        print_r($new_modules_array);
        die;*/
        //TRUNCATE TABLE assist_module_sheet_data;
        //INSERT INTO assist_module_sheet_data SELECT * FROM assist_module;
         
        /*
        foreach ($new_modules_array as $new_value) {
            Yii::$app->db->createCommand()->insert('assist_module_sheet_data',['module_name'=>$new_value[0], 'module_type'=>$new_value[1],'number_of_weeks'=>$new_value[3],'category_type'=>$new_value[2],'mcourse'=>$new_value[4]])->execute();
                                                                                
        }
        */
        echo "All Modules data has been inserted";
    }//End og function:actionGet_modules_sheet_data//

}// --- End of class:DvModuleController --- //