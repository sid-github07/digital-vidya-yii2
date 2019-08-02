<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\AuditLogs;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     * Yii::$app->CustomComponents->is_super_admin()
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionError(){
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            //track error
            $content = $exception;
            mail('anoops@whizlabs.com', 'Whizlabs Assist error', $content);
            return $this->render('error', ['exception' => $exception]);
        }
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex(){
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }
        return $this->render('index'); 
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin(){
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {

            $date = date('d-m-Y H:i:s');
            $id = Yii::$app->getUser()->identity->id;

            Yii::$app->db->createCommand()
                ->update('assist_users', ['last_logged' => $date], "id = $id")
                ->execute();


            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout(){
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    private function send_email($to, $subject, $body){
        $is_sent = Yii::$app->mailer->compose()
            ->setFrom(Yii::$app->params['mail_from'])
            ->setTo($to)
            ->setSubject($subject)
            ->setHtmlBody($body)
            ->send();
        if ($is_sent) {
            return 1;
        } else {
            return 0;
        }
    }  
      
}