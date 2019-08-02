<?php
namespace app\controllers;
use Yii;
use yii\web\Controller;
use app\models\DvModuleModel;
use app\models\DvCourseModel;
use app\models\DvCourse;
use app\models\DvUsers;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\data\Pagination;
use yii\helpers\Url; 
 

class DvCourseController extends Controller {

    /**
     * @CDO  - 7 April 2019
     * Purpose:For Add & Edit of Courses
     * */
    public function actionIndex() {
        if(!empty(Yii::$app->request->get('id'))){
            $model = DvCourseModel::find()->where(['id' =>Yii::$app->request->get('id')])->one();
            if(empty($model)){
                return $this->redirect(['dv-course/index']); 
            }
        }else{
            $model = new DvCourseModel();
        }
        if($model->load(Yii::$app->request->post())){
            $data = Yii::$app->request->post();
            $data = $data['DvCourseModel'];
            $model->name = $data['name'];
            $model->created_by = Yii::$app->getUser()->identity->id;
            $model->course_code = $data['course_code'];
            $model->mcourse = $data['mcourse'];
            $model->version = $data['version'];
            $model->course_speed = !empty($data['course_speed']) ? trim(implode(',',$data['course_speed'])) : "";
            $model->foundation_module = !empty($data['foundation_module']) ? $data['foundation_module'] : NULL;
            $model->type = !empty($_POST['type']) ? $_POST['type'] : NULL ;
            //$model->status = 1;
            $model->core_modules = !empty($data['core_modules']) ? trim(implode(',',$data['core_modules'])) : "";
            $model->special_module = !empty($data['special_module']) ? trim(implode(',',$data['special_module'])) : NULL;
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
                return $this->redirect(['dv-course/index?id='.Yii::$app->request->get('id')]);
            }else{
                return $this->redirect(['dv-course/index']);
            }
        }//end of post
        $module_dropDown = ArrayHelper::map(DvModuleModel::find()->all(), 'id', 'module_name');
        //Edit Purpose
        if(!empty(Yii::$app->request->get('id'))){
            $existing_module_core_selectd = ArrayHelper::map(DvModuleModel::find()->where(['id'=>explode(',',$model->core_modules)])->all(), 'id', 'module_name');
            $existing_module_special_selectd = ArrayHelper::map(DvModuleModel::find()->where(['id'=>explode(',',$model->special_module)])->all(), 'id', 'module_name');

            $model->core_modules = !empty($model->core_modules) ? explode(',', $model->core_modules) : "";
            $model->special_module = !empty($model->special_module) ? explode(',', $model->special_module) : "";
            $model->course_speed = !empty($model->course_speed) ? explode(',', $model->course_speed) : "";
        }else{
            $existing_module_selectd = array();
        }
        $module_type = array();
        $module_type_array = ['Self Study','Instructor led'];
        foreach ($module_type_array as $key => $value) {
            $module_type[$value] = $value ;
        }

        $domain_array = array(
            'da'=>"Digital Analytics",
            'dm'=>"Digital Marketing"
        );

        $version_array = array();
        for($i=1; $i<=$model->version; $i++) {
            $version_array[$i] = "Version ".$i;
        }
        
        //For Course
        $course_speed_data = ['Fast','Normal'];
        $course_speed_array = array();
        foreach ($course_speed_data as $key => $value) {
            $course_speed_array[$value] = $value ;
        }
        //For Pagination
        $query = DvCourseModel::find();
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(),'PageSize' => 10]);
        $course_details = $query->offset($pages->offset)
                        ->limit($pages->limit)
                        ->orderBy(['id'=>SORT_DESC])
                        ->createCommand()
                        ->queryAll();
        $module_data = ArrayHelper::map(DvModuleModel::find()->all(), 'id', 'module_name');
        
        //For view
        return $this->render('create_course',
                array('model'=>$model,
                    'module_dropDown'=>$module_dropDown,
                    'module_type'=>$module_type,
                    'domain_array'=>$domain_array,
                    'version_array'=>$version_array,
                    'course_speed_array'=>$course_speed_array,
                    'course_details'=>$course_details,
                    'module_data'=>$module_data,
                    'pages' => $pages
                )
        );
    } // End of function:actionIndex() 

    /**
     * @CDO  - 7 April 2019
     * Purpose:For Edit View of Courses
     * */
    public function actionCourse_edit($id = NULL){
        if($id != NULL && !empty($id)){
            return $this->redirect(['dv-course/index?id='.$id]);
        }else{
            return $this->redirect(['dv-course/index']);
        }
    }// End of function:actionCourse_edit() 
    
    /**
     * @CDO  - 7 April 2019
     * Purpose:For Delete of Courses
     * */
    public function actionCourse_delete($id = NULL){
        if($id != NULL && !empty($id)){ 
            $model = DvCourseModel::find()->where(['id' => $id])->one();
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
    }// End of function:actionCourse_delete() 

    /**
     * @CDO  - 7 April 2019
     * Purpose:For Getting Course Type
     * */
    public function actionGet_course_type(){
        $module_id =  Yii::$app->request->post('core_module_id');
        $module_course_type = ArrayHelper::map(DvModuleModel::find()->where(['in','id',$module_id])->all(),'module_type','module_type');
        if(in_array('Instructor led', $module_course_type)){
            echo true;
        }else{
            echo false;
        } 
        
    }//End of function:actionGet_course_type()
    
    /**
     * @CDO  - 11 April 2019
     * Purpose:For Check Course Version
     * */
    public function actionCheck_course_version(){
        $course_name =  strtolower(Yii::$app->request->post('name'));
        $model_count = DvCourseModel::find()
                ->where(['status' => 1])
                ->andWhere(['name'=>$course_name])
                ->count();

        $option = '';
        if(!empty($model_count)){        
            for($i = 1 ; $i <= $model_count+1 ; $i++){
                $option .= "<option value=".$i.">Version ".$i."</option>";
            }
        }else{
            $option = "<option value='1'>"."Version 1"."</option>";
        }
        return $option;
    }//End of function:check_course_version()  
    /**
     * @CDO  - 19 Aprril 2019
     * Purpose:For Filter
     **/
    public function actionFilter(){
        //Filter Data
        $model_filter = '';
        $filter_data = array();
        if(!empty($_GET['mcourse']) || !empty($_GET['type']) || !empty($_GET['course_speed'])){
            $mcourse = $_GET['mcourse'];
            $module_type = $_GET['type'];
            $course_speed = $_GET['course_speed'];
            $common_query = DvCourseModel::find();
            if ($mcourse != '') {
                $filter_data['mcourse'] = $mcourse;
                $model_filter = $common_query->andWhere(['assist_course.mcourse'=>$mcourse]);
            }
            if ($module_type != '') {
                $filter_data['type'] = $module_type;
                $model_filter = $common_query->andWhere(['assist_course.type'=>$module_type]);
            }
            if ($course_speed != '') {
                $filter_data['course_speed'] = $course_speed;
                $model_filter = $common_query->andWhere(['like','assist_course.course_speed',$course_speed]);
            }
        }else{
            $model_filter = DvCourseModel::find();
        }
        
        $countQuery = clone $model_filter;
        $pages = new Pagination(['totalCount' => $countQuery->count(),'PageSize' => 10]);
        $course_details = $model_filter->offset($pages->offset)
                    ->limit($pages->limit)
                    ->orderBy(['id'=>SORT_DESC])
                    ->createCommand()
                    ->queryAll();
        //Begin Fix data//
        $model = new DvCourseModel();
        $module_dropDown = ArrayHelper::map(DvModuleModel::find()->all(), 'id', 'module_name');
        if(!empty(Yii::$app->request->get('id'))){
            $existing_module_core_selectd = ArrayHelper::map(DvModuleModel::find()->where(['id'=>explode(',',$model->core_modules)])->all(), 'id', 'module_name');
            $existing_module_special_selectd = ArrayHelper::map(DvModuleModel::find()->where(['id'=>explode(',',$model->special_module)])->all(), 'id', 'module_name');

            $model->core_modules = !empty($model->core_modules) ? explode(',', $model->core_modules) : "";
            $model->special_module = !empty($model->special_module) ? explode(',', $model->special_module) : "";
        }else{
            $existing_module_selectd = array();
        }
        $module_type = array();
        $module_type_array = ['Self Study','Instructor led'];
        foreach ($module_type_array as $key => $value) {
            $module_type[$value] = $value ;
        }
        $domain_array = array(
            'da'=>"Digital Analytics",
            'dm'=>"Digital Marketing"
        );
        $version_array = array();
        for($i=1; $i<=$model->version; $i++) {
            $version_array[$i] = "Version ".$i;
        }
        //For Course
        $course_speed_data = ['Fast','Normal'];
        $course_speed_array = array();
        foreach ($course_speed_data as $key => $value) {
            $course_speed_array[$value] = $value ;
        }
        $module_data = ArrayHelper::map(DvModuleModel::find()->all(), 'id', 'module_name');
        //End Fix data//

        return $this->render('create_course',
                array(
                    'model'=>$model,
                    'module_dropDown'=>$module_dropDown,
                    'module_type'=>$module_type,
                    'domain_array'=>$domain_array,
                    'version_array'=>$version_array,
                    'course_speed_array'=>$course_speed_array,
                    'course_details'=>$course_details,
                    'module_data'=>$module_data,
                    'pages'=>$pages,
                    'filter_data'=>$filter_data
                )
            );
    }//End of function:actionFilter()// 
}// --- End of class:DvCourseController --- //