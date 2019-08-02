<?php
namespace app\controllers;
use Yii;
use yii\web\Controller;
use app\models\DvCoordinatorModel;
use app\models\DvUsers;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\data\Pagination; 
use app\models\DvUserMeta;
 

class DvCoordinatorController extends Controller {

    /**
     * @PP  - 11 April 2019
     * Purpose:For Add & Edit of Co-ordinators
     * */
    public function actionIndex() {
        if(!empty(Yii::$app->request->get('get_date'))){
            $today_date = date('Y-m-d',strtotime(Yii::$app->request->get('get_date')));
        }else{
            $today_date = date('Y-m-d');
        }
        if($today_date){
            $model = DvCoordinatorModel::find()
            ->where(['created_on' =>$today_date])
            ->one();
            if(empty($model)){
                //return $this->redirect(['dv-coordinator/index']); 
                $model = new DvCoordinatorModel();
            }
        }
        if($model->load(Yii::$app->request->post())){
            $data = Yii::$app->request->post();
            $data = $data['DvCoordinatorModel'];
            $model->created_by = Yii::$app->getUser()->identity->id;
            $model->created_on = date('Y-m-d',strtotime($data['created_on']));
            $model->coordinator_ids = !empty($_POST['coordinator_ids']) ? trim(implode(',', $_POST['coordinator_ids'])) : "";
            if($model->save()){
                $msg = "success";
                $msg_content = "Record has been updated successfully.";
            }else{
                $msg = "error";
                $msg_content = "Something went wrong for new entry.";
            }
            Yii::$app->session->setFlash($msg?$msg:"", $msg_content?$msg_content:"");
            if(!empty(Yii::$app->request->get('id'))){
                return $this->redirect(['dv-coordinator/index?id='.Yii::$app->request->get('id')]);
            }else{
                return $this->redirect(['dv-coordinator/index']);
            }
        }//end of post
        $existing_coordinator = ArrayHelper::map(DvUserMeta::find()->where(['meta_key'=>'role','meta_value'=>5])->all(),'uid','uid');
        $existing_coordinator_array = array();
        foreach ($existing_coordinator as $key => $value) {
            $users_data = DvUsers::find()->where(['id'=>$key])->one();
            if(!empty($users_data['first_name'])){
                $existing_coordinator_array[$key] = ucfirst($users_data['first_name'].' '.$users_data['last_name']);
            }
        }

        return $this->render('coordinator_view',
            array(
                'model'=>$model,'existing_coordinator_array'=>$existing_coordinator_array
            )
        );

    } // End of function:actionIndex()

    /**
     * @PP  - 11 April 2019
     * Purpose:For Getting history of Co-ordinators
     * */
    public function actionGet_history(){
        $date_data = Yii::$app->request->post('get_date');
        $model = DvCoordinatorModel::find()
            ->where(['created_on' =>date('Y-m-d',strtotime($date_data))])
            ->one();
        if(!empty($model['coordinator_ids'])){
            $cordi_ids_array = explode(',',$model['coordinator_ids']);
            $existing_coordinator_array = array();
            foreach ($cordi_ids_array as $value) {
                $users_data = DvUsers::find()
                            ->where(['id'=>$value])
                            ->one();
                if(!empty($users_data['first_name'])){
                    $existing_coordinator_array[$value] = ucfirst($users_data['first_name'].' '.$users_data['last_name']);
                }
            }
            $retun_data = '';
            $retun_data .= '<ol>';
            foreach ($existing_coordinator_array as $value) {
                $retun_data .='<li><div class=form-group>'.$value.'</div></li>';
            }
            $retun_data .= '</ol>';
            return $retun_data;  
        }else{
           return "No Records Found."; 
        }    

    }//End of function:actionGet_history// 
}// --- End of class:DvCoordinatorController