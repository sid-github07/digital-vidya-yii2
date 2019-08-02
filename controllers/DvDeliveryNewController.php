<?php
namespace app\controllers;
use Yii;
use yii\web\Controller;
use yii\data\Pagination;
use app\models\DvCourseNew;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * Created By @CDO 28 March 2019
 */
class DvDeliveryNewController extends Controller{
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

 	/**
 	*  Created By @CDO 28 March 2019 
    *  View all Courses.     
    */
    public function actionIndex(){
        //redirect a user if not super admin
        /*if(!Yii::$app->CustomComponents->check_permission('delivery')) {
            return $this->redirect(['site/index']);
        }*/
        $query = DvParticipantModules::find();
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count,'pageSize' => 10]);
        $models = $query->offset($pagination->offset)->limit($pagination->limit)->all();

        return $this->render('index', [ 'modules' => $models,'total_records' => $count, 'pages' => $pagination]);
    }

    /**
 	*  Created By @CDO 28 March 2019 
    *  View all Courses.     
    */
    public function actionCreate(){
        //redirect a user if not super admin
        /*if(!Yii::$app->CustomComponents->check_permission('course')) {
            return $this->redirect(['index']);
        }*/

        $model = new DvCourseNew();

        if ($model->load(Yii::$app->request->post()) && $model->save()){
            Yii::$app->session->setFlash('success','New Course Created Successfully');
            return $this->redirect(['dv-delivery-new/create']);
        } else {
        	$query = DvCourseNew::find();
		    $count = $query->count();
		    $pagination = new Pagination(['totalCount' => $count,'pageSize' => 25]);
		    $models = $query->offset($pagination->offset)->limit($pagination->limit)->all();
            return $this->render('create', [ 'model' => $model, ]);
        }
    }
}