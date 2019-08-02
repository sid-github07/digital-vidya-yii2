<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\data\Pagination;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use app\models\DvPaymentMode;
use app\models\DvIncentive;
use app\models\DvMonthlyIncentiveRate;
use app\models\DvGst;
use app\models\DvManageMonthlyIncentiveRate;
use app\models\DvManageMonthlyIncentiveTeamManagement;
use app\models\DvManageMonthlyIncentiveRule;
use app\models\DvManageFullPaymentIncentiveRate;
use app\models\DvManageMonthlyIncentiveExceptionRate;
use app\models\DvSales;
use app\models\DvUserMeta;
use app\models\DvRegistration;
use app\models\DvQuickBook;

/* QuickBooksOnline Classes */
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Item;
use QuickBooksOnline\API\Facades\CreditMemo;
use QuickBooksOnline\API\Facades\Payment;
use QuickBooksOnline\API\Facades\TaxService;
use QuickBooksOnline\API\Data\IPPPaymentMethod;

/**
 * DvFinanceController implements the CRUD actions.
 * Yii::$app->CustomComponents->check_permission('all_users')
 */
class DvFinanceController extends Controller {

    public function quickbook_instance() {
        $dv_settings = DvQuickBook::find()->all();

        $ClientID = "";
        $ClientSecret = "";
        $accessTokenKey = "";
        $refreshTokenKey = "";
        $QBORealmID = "";
        $flag1 = 0;
        $flag2 = 0;

        foreach ($dv_settings as $val) {

            if ($val->qb_key == "ClientID") {
                $ClientID = $val->qb_value;
            } else if ($val->qb_key == "ClientSecret") {
                $ClientSecret = $val->qb_value;
            } else if ($val->qb_key == "accessTokenKey") {
                $accessTokenKey = $val->qb_value;
            } else if ($val->qb_key == "refreshTokenKey") {
                $refreshTokenKey = $val->qb_value;
            } else if ($val->qb_key == "QBORealmID") {
                $QBORealmID = $val->qb_value;
            }
        }


        $environment = Yii::$app->params['environment']; // check server enviroment
        if ($environment == 'Production') {
            // live
            $baseUrl = 'Production';
        } else {
            $baseUrl = 'Development';
        }


        $dataService = DataService::Configure(array(
                    'auth_mode' => 'oauth2',
                    'ClientID' => $ClientID,
                    'ClientSecret' => $ClientSecret,
                    'accessTokenKey' => $accessTokenKey,
                    'refreshTokenKey' => $refreshTokenKey,
                    'QBORealmID' => $QBORealmID,
                    'baseUrl' => $baseUrl
                        //'baseUrl' => "Production"
        ));

        return $dataService;
    }

    /**
     * Creates incentive.
     * If creation is successful, incentive will be added in database and dispaly in listing.
     * @return mixed
     */
    public function actionCreate_incentive() {

        //redirect a user if not super admin
        if (!Yii::$app->CustomComponents->check_permission('crearte_incentive')) {
            return $this->redirect(['site/index']);
        }

        $model = new DvIncentive();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'New Incentive Created.');
            return $this->redirect(['dv-finance/create_incentive']);
        } else {
            return $this->render('create_incentive', [ 'model' => $model]);
        }
    }

    /**
     * Update incentive.
     * If updated successful, incentive will be updated in database and dispaly in curency listing.
     * @return mixed
     */
    public function actionEdit_incentive($id) {

        //redirect a user if not super admin
        if (!Yii::$app->CustomComponents->check_permission('edit_incentive')) {
            return $this->redirect(['site/index']);
        }

        $model = DvIncentive::findOne($id);
        if (empty($model)) {
            return $this->redirect(['create_incentive']);
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->save();
            Yii::$app->session->setFlash('success', 'Incentive updated.');
            return $this->redirect(['create_incentive']);
        } else {
            return $this->render('edit_incentive', ['model' => $model]);
        }
    }

    /**
     * Creates incentive.
     * If creation is successful, incentive will be added in database and dispaly in listing.
     * @return mixed
     */
    public function actionMonthly_incentive_rate() {

        //redirect a user if not manager
        if (!Yii::$app->CustomComponents->check_permission('monthly_incentive_rate')) {
            return $this->redirect(['site/index']);
        }

        $model = new DvMonthlyIncentiveRate();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'New Incentive Created.');
            return $this->redirect(['dv-finance/create_incentive']);
        } else {
            return $this->render('create_incentive', [ 'model' => $model]);
        }
    }

    public function actionManage_monthly_incentive_rate() {
        if (!Yii::$app->CustomComponents->check_permission('manage_monthly_incentive_rate')) {
            return $this->redirect(['site/index']);
        }

        $user_id = Yii::$app->getUser()->identity->id;
        $user = Yii::$app->user->identity;
        $usermeta_result = DvUserMeta::find()->where(['uid' => $user->id, 'meta_key' => 'role'])->one();
        $user_role = $usermeta_result->meta_value; /*  2 - Executive role. // 1 - Admin */

        if ($user->department == 1 || $user->department == 8) {/* Only users of Sales department can view this. 8 is for Super Admin. */
        } else {
            return $this->redirect(['site/index']);
        }

        $teams = Yii::$app->db->createCommand("SELECT * FROM assist_users as du 
            WHERE du.id IN(SELECT uid FROM assist_user_meta WHERE meta_key = 'role' AND meta_value=6) AND status = 1 ")->queryAll();

        $incentives = DvManageMonthlyIncentiveRate::find();
        $years = array();
        $after_6_month = array();
        $before_6_month = array();

        for ($i = 0; $i < 7; $i++) {
            $incentives->orWhere(["month" => date('m', strtotime("+$i month")), "year" => date('Y', strtotime("+$i month"))]);
            $after_6_month[date('Y', strtotime("+$i month"))][] = date('m', strtotime("+$i month"));
            $years[] = date('Y', strtotime("+$i month"));
        }
        for ($i = 1; $i < 6; $i++) {
            $incentives->orWhere(["month" => date('m', strtotime("-$i month")), "year" => date('Y', strtotime("-$i month"))]);
            $before_6_month[date('Y', strtotime("-$i month"))][] = date('m', strtotime("-$i month"));
            $years[] = date('Y', strtotime("-$i month"));
        }

        $years = array_unique($years);
        asort($years);
        $incentives = $incentives->all();

        $exceptions = DvManageMonthlyIncentiveExceptionRate::find()->all();

        $model = new DvManageMonthlyIncentiveRate();

        $inserted_date = date("Y-m-d H:i:s");

        if ($model->load(Yii::$app->request->post())) {
            if (isset($_POST['DvManageMonthlyIncentiveRate'])) {
                if (isset($_POST['DvManageMonthlyIncentiveRate']['month'])) {
                    $total_number_of_month = sizeof($_POST['DvManageMonthlyIncentiveRate']['month']);
                    for ($i = 0; $i < $total_number_of_month; $i++) {
                        if (isset($_POST['DvManageMonthlyIncentiveRate']['min_closures'])) {
                            $total_closures_range = sizeof($_POST['DvManageMonthlyIncentiveRate']['min_closures']);
                            for ($j = 0; $j < $total_closures_range; $j++) {

                                $DvManageMonthlyIncentiveRate = new DvManageMonthlyIncentiveRate();
                                $DvManageMonthlyIncentiveRate->year = $_POST['DvManageMonthlyIncentiveRate']['year'];
                                $DvManageMonthlyIncentiveRate->month = $_POST['DvManageMonthlyIncentiveRate']['month'][$i];
                                $DvManageMonthlyIncentiveRate->mcourse = $_POST['DvManageMonthlyIncentiveRate']['mcourse'];
                                $DvManageMonthlyIncentiveRate->min_closures = $_POST['DvManageMonthlyIncentiveRate']['min_closures'][$j];
                                $DvManageMonthlyIncentiveRate->max_closures = $_POST['DvManageMonthlyIncentiveRate']['max_closures'][$j];
                                $DvManageMonthlyIncentiveRate->rate = $_POST['DvManageMonthlyIncentiveRate']['rate'][$j];
                                $DvManageMonthlyIncentiveRate->created_at = $inserted_date;
                                $DvManageMonthlyIncentiveRate->updated_at = $inserted_date;
                                if ($DvManageMonthlyIncentiveRate->save()) {
                                    
                                }
                            }
                        }
                    }
                }
            }
            Yii::$app->session->setFlash('success', 'New Monthly Incentive Rule Created.');
            return $this->redirect(['dv-finance/manage_monthly_incentive_rate']);
        } else {
            return $this->render('manage_monthly_incentive_rate', [ 'model' => $model, 'teams' => $teams, 'incentives' => $incentives, 'exceptions' => $exceptions, 'before_6_month' => $before_6_month, 'after_6_month' => $after_6_month, 'years' => $years]);
        }
    }

    public function incentives_from_instalment_payments() {
        $dataService = $this->quickbook_instance();
        $user_id = Yii::$app->getUser()->identity->id;
        $years = array();
        $data = array();

        for ($i = 0; $i < 7; $i++) {
            $month = date('m', strtotime("+$i month"));
            $year = date('Y', strtotime("+$i month"));
            $data[$year][$month]['da_sale'] = 0;
            $data[$year][$month]['dm_sale'] = 0;
            $data[$year][$month]['all_da_payment_from_instalment'] = 0;
            $data[$year][$month]['all_dm_payment_from_instalment'] = 0;
            $da_incentive_rate = 0;
            $dm_incentive_rate = 0;
            $all_dm_qb_id_str = "";
            $all_da_qb_id_str = "";
            $da_dm_qb_id = array();

            $get_dm_qb_customer_id = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$user_id AND Month(assist_participant.created_on) = $month AND 
                            YEAR(assist_participant.created_on) = $year AND assist_course.mcourse='dm' AND 
                                qb_customer_id IS NOT NULL")->queryAll();

            $get_da_qb_customer_id = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$user_id AND Month(assist_participant.created_on) = $month AND 
                            YEAR(assist_participant.created_on) = $year AND assist_course.mcourse='da' AND 
                                qb_customer_id IS NOT NULL")->queryAll();

            $get_da_exception_rate = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_exception_rate 
                    WHERE month=$month AND years=$year AND domain='da' AND executive_id=$user_id")->queryAll();
            if (!empty($get_da_exception_rate)) {
                $recent_date = array();
                foreach ($get_da_exception_rate as $exception) {

                    $recent_date[] = $exception['created_at'];
                }

                foreach ($get_da_exception_rate as $exception) {
                    if (end($recent_date) == $exception['created_at']) { /* Display lastly added rules */
                        if ($data[$year][$month]['da_sale'] >= $exception['min_closures'] &&
                                $data[$year][$month]['da_sale'] <= $exception['max_closures']) {
                            $da_incentive_rate = $exception['rate'];
                        }
                    }
                }
            }

            $get_dm_exception_rate = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_exception_rate 
                    WHERE month=$month AND years=$year AND domain='dm' AND executive_id=$user_id")->queryAll();
            if (!empty($get_dm_exception_rate)) {
                $recent_date = array();
                foreach ($get_dm_exception_rate as $exception) {

                    $recent_date[] = $exception['created_at'];
                }

                foreach ($get_dm_exception_rate as $exception) {
                    if (end($recent_date) == $exception['created_at']) { /* Display lastly added rules */
                        if ($data[$year][$month]['da_sale'] >= $exception['min_closures'] &&
                                $data[$year][$month]['da_sale'] <= $exception['max_closures']) {
                            $dm_incentive_rate = $exception['rate'];
                        }
                    }
                }
            }

            if ($da_incentive_rate == 0) {
                $get_da_incentive_rate = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_rate 
                    WHERE month=$month AND year=$year AND mcourse='da'")->queryAll();
                if (!empty($get_da_incentive_rate)) {
                    $recent_date = array();
                    foreach ($get_da_incentive_rate as $incentive) {

                        $recent_date[] = $incentive['created_at'];
                    }

                    foreach ($get_da_incentive_rate as $incentive) {
                        if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                            if ($data[$year][$month]['da_sale'] >= $incentive['min_closures'] &&
                                    $data[$year][$month]['da_sale'] <= $incentive['max_closures']) {
                                $da_incentive_rate = $incentive['rate'];
                            }
                        }
                    }
                }
            }

            if ($dm_incentive_rate == 0) {
                $get_dm_incentive_rate = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_rate 
                    WHERE month=$month AND year=$year AND mcourse='dm'")->queryAll();
                if (!empty($get_dm_incentive_rate)) {
                    $recent_date = array();
                    foreach ($get_dm_incentive_rate as $incentive) {

                        $recent_date[] = $incentive['created_at'];
                    }
                    foreach ($get_dm_incentive_rate as $incentive) {
                        if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                            if ($data[$year][$month]['dm_sale'] >= $incentive['min_closures'] &&
                                    $data[$year][$month]['dm_sale'] <= $incentive['max_closures']) {
                                $dm_incentive_rate = $incentive['rate'];
                            }
                        }
                    }
                }
            }

            $get_all_dm_qb_customer_id = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$user_id AND assist_course.mcourse='dm' AND qb_customer_id IS NOT NULL")->queryAll();

            $get_all_da_qb_customer_id = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$user_id AND assist_course.mcourse='da' AND qb_customer_id IS NOT NULL")->queryAll();

            if (!empty($get_all_da_qb_customer_id)) {
                foreach ($get_all_da_qb_customer_id as $qb) {
                    if (!empty($qb['qb_customer_id'])) {
                        $all_da_qb_id_str .= "'" . $qb['qb_customer_id'] . "',";
                    }
                }
            }

            if (!empty($get_all_dm_qb_customer_id)) {
                foreach ($get_all_dm_qb_customer_id as $qb) {
                    if (!empty($qb['qb_customer_id'])) {
                        $all_dm_qb_id_str .= "'" . $qb['qb_customer_id'] . "',";
                    }
                }
            }

            if (!empty($get_dm_qb_customer_id)) {
                foreach ($get_dm_qb_customer_id as $qb) {
                    $da_dm_qb_id[] = $qb['qb_customer_id'];
                }
            }
            if (!empty($get_da_qb_customer_id)) {
                foreach ($get_da_qb_customer_id as $qb) {
                    $da_dm_qb_id[] = $qb['qb_customer_id'];
                }
            }

            $all_da_qb_id_str = rtrim($all_da_qb_id_str, ",");
            $all_dm_qb_id_str = rtrim($all_dm_qb_id_str, ",");

            $total_payment = $dataService->Query("SELECT count(*) FROM Payment");
            $da_payment_from_instalment = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($all_da_qb_id_str)
                        MAXRESULTS $total_payment");
            $dm_payment_from_instalment = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($all_dm_qb_id_str)
                        MAXRESULTS $total_payment");

            if (!empty($dm_payment_from_instalment)) {
                foreach ($dm_payment_from_instalment as $payment) {
                    if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                            date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                        if (!in_array($payment->CustomerRef, $da_dm_qb_id)) {
                            $data[$year][$month]['all_dm_payment_from_instalment'] += $payment->TotalAmt;
                        }
                    }
                }
            }

            if (!empty($da_payment_from_instalment)) {
                foreach ($da_payment_from_instalment as $payment) {
                    if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                            date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                        if (!in_array($payment->CustomerRef, $da_dm_qb_id)) {
                            $data[$year][$month]['all_da_payment_from_instalment'] += $payment->TotalAmt;
                        }
                    }
                }
            }

            $da_incentive_from_payment = ($data[$year][$month]['all_da_payment_from_instalment'] *
                    $da_incentive_rate) / 100;
            $dm_incentive_from_payment = ($data[$year][$month]['all_dm_payment_from_instalment'] *
                    $dm_incentive_rate) / 100;

            $data[$year][$month]['da_incentive_from_payment'] = $da_incentive_from_payment;
            $data[$year][$month]['dm_incentive_from_payment'] = $dm_incentive_from_payment;
            $total_incentive_from_payment = $dm_incentive_from_payment + $da_incentive_from_payment;
            $data[$year][$month]['total_incentive_from_payment'] = number_format((float) $total_incentive_from_payment, 2, '.', '');
        }
        for ($i = 1; $i < 6; $i++) {
            $month = date('m', strtotime("-$i month"));
            $year = date('Y', strtotime("-$i month"));

            $data[$year][$month]['da_sale'] = 0;
            $data[$year][$month]['dm_sale'] = 0;
            $data[$year][$month]['all_da_payment_from_instalment'] = 0;
            $data[$year][$month]['all_dm_payment_from_instalment'] = 0;
            $da_incentive_rate = 0;
            $dm_incentive_rate = 0;
            $all_dm_qb_id_str = "";
            $all_da_qb_id_str = "";
            $da_dm_qb_id = array();

            $get_dm_qb_customer_id = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$user_id AND Month(assist_participant.created_on) = $month AND 
                            YEAR(assist_participant.created_on) = $year AND assist_course.mcourse='dm' AND 
                                qb_customer_id IS NOT NULL")->queryAll();

            $get_da_qb_customer_id = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$user_id AND Month(assist_participant.created_on) = $month AND 
                            YEAR(assist_participant.created_on) = $year AND assist_course.mcourse='da' AND 
                                qb_customer_id IS NOT NULL")->queryAll();

            $get_da_exception_rate = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_exception_rate 
                    WHERE month=$month AND years=$year AND domain='da' AND executive_id=$user_id")->queryAll();
            if (!empty($get_da_exception_rate)) {
                $recent_date = array();
                foreach ($get_da_exception_rate as $exception) {

                    $recent_date[] = $exception['created_at'];
                }

                foreach ($get_da_exception_rate as $exception) {
                    if (end($recent_date) == $exception['created_at']) { /* Display lastly added rules */
                        if ($data[$year][$month]['da_sale'] >= $exception['min_closures'] &&
                                $data[$year][$month]['da_sale'] <= $exception['max_closures']) {
                            $da_incentive_rate = $exception['rate'];
                        }
                    }
                }
            }

            $get_dm_exception_rate = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_exception_rate 
                    WHERE month=$month AND years=$year AND domain='dm' AND executive_id=$user_id")->queryAll();
            if (!empty($get_dm_exception_rate)) {
                $recent_date = array();
                foreach ($get_dm_exception_rate as $exception) {

                    $recent_date[] = $exception['created_at'];
                }

                foreach ($get_dm_exception_rate as $exception) {
                    if (end($recent_date) == $exception['created_at']) { /* Display lastly added rules */
                        if ($data[$year][$month]['da_sale'] >= $exception['min_closures'] &&
                                $data[$year][$month]['da_sale'] <= $exception['max_closures']) {
                            $dm_incentive_rate = $exception['rate'];
                        }
                    }
                }
            }

            if ($da_incentive_rate == 0) {
                $get_da_incentive_rate = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_rate 
                    WHERE month=$month AND year=$year AND mcourse='da'")->queryAll();
                if (!empty($get_da_incentive_rate)) {
                    $recent_date = array();
                    foreach ($get_da_incentive_rate as $incentive) {

                        $recent_date[] = $incentive['created_at'];
                    }

                    foreach ($get_da_incentive_rate as $incentive) {
                        if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                            if ($data[$year][$month]['da_sale'] >= $incentive['min_closures'] &&
                                    $data[$year][$month]['da_sale'] <= $incentive['max_closures']) {
                                $da_incentive_rate = $incentive['rate'];
                            }
                        }
                    }
                }
            }

            if ($dm_incentive_rate == 0) {
                $get_dm_incentive_rate = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_rate 
                    WHERE month=$month AND year=$year AND mcourse='dm'")->queryAll();
                if (!empty($get_dm_incentive_rate)) {
                    $recent_date = array();
                    foreach ($get_dm_incentive_rate as $incentive) {

                        $recent_date[] = $incentive['created_at'];
                    }
                    foreach ($get_dm_incentive_rate as $incentive) {
                        if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                            if ($data[$year][$month]['dm_sale'] >= $incentive['min_closures'] &&
                                    $data[$year][$month]['dm_sale'] <= $incentive['max_closures']) {
                                $dm_incentive_rate = $incentive['rate'];
                            }
                        }
                    }
                }
            }

            $get_all_dm_qb_customer_id = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$user_id AND assist_course.mcourse='dm' AND qb_customer_id IS NOT NULL")->queryAll();

            $get_all_da_qb_customer_id = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$user_id AND assist_course.mcourse='da' AND qb_customer_id IS NOT NULL")->queryAll();

            if (!empty($get_all_da_qb_customer_id)) {
                foreach ($get_all_da_qb_customer_id as $qb) {
                    if (!empty($qb['qb_customer_id'])) {
                        $all_da_qb_id_str .= "'" . $qb['qb_customer_id'] . "',";
                    }
                }
            }

            if (!empty($get_all_dm_qb_customer_id)) {
                foreach ($get_all_dm_qb_customer_id as $qb) {
                    if (!empty($qb['qb_customer_id'])) {
                        $all_dm_qb_id_str .= "'" . $qb['qb_customer_id'] . "',";
                    }
                }
            }

            if (!empty($get_dm_qb_customer_id)) {
                foreach ($get_dm_qb_customer_id as $qb) {
                    $da_dm_qb_id[] = $qb['qb_customer_id'];
                }
            }
            if (!empty($get_da_qb_customer_id)) {
                foreach ($get_da_qb_customer_id as $qb) {
                    $da_dm_qb_id[] = $qb['qb_customer_id'];
                }
            }

            $all_da_qb_id_str = rtrim($all_da_qb_id_str, ",");
            $all_dm_qb_id_str = rtrim($all_dm_qb_id_str, ",");

            $total_payment = $dataService->Query("SELECT count(*) FROM Payment");
            $da_payment_from_instalment = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($all_da_qb_id_str)
                        MAXRESULTS $total_payment");
            $dm_payment_from_instalment = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($all_dm_qb_id_str)
                        MAXRESULTS $total_payment");

            if (!empty($dm_payment_from_instalment)) {
                foreach ($dm_payment_from_instalment as $payment) {
                    if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                            date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                        if (!in_array($payment->CustomerRef, $da_dm_qb_id)) {
                            $data[$year][$month]['all_dm_payment_from_instalment'] += $payment->TotalAmt;
                        }
                    }
                }
            }

            if (!empty($da_payment_from_instalment)) {
                foreach ($da_payment_from_instalment as $payment) {
                    if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                            date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                        if (!in_array($payment->CustomerRef, $da_dm_qb_id)) {
                            $data[$year][$month]['all_da_payment_from_instalment'] += $payment->TotalAmt;
                        }
                    }
                }
            }

            $da_incentive_from_payment = ($data[$year][$month]['all_da_payment_from_instalment'] *
                    $da_incentive_rate) / 100;
            $dm_incentive_from_payment = ($data[$year][$month]['all_dm_payment_from_instalment'] *
                    $dm_incentive_rate) / 100;

            $data[$year][$month]['da_incentive_from_payment'] = $da_incentive_from_payment;
            $data[$year][$month]['dm_incentive_from_payment'] = $dm_incentive_from_payment;
            $total_incentive_from_payment = $dm_incentive_from_payment + $da_incentive_from_payment;
            $data[$year][$month]['total_incentive_from_payment'] = number_format((float) $total_incentive_from_payment, 2, '.', '');
        }
        return $data;
    }

    public function incentive_for_fresh_payments() {
        $dataService = $this->quickbook_instance();
        $user_id = Yii::$app->getUser()->identity->id;
        $data = array();

        for ($i = 0; $i < 7; $i++) {
            $month = date('m', strtotime("+$i month"));
            $year = date('Y', strtotime("+$i month"));
            $data[$year][$month]['da_sale'] = 0;
            $data[$year][$month]['dm_sale'] = 0;
            $data[$year][$month]['da_fresh_payment'] = 0;
            $data[$year][$month]['dm_fresh_payment'] = 0;
            $dm_qb_id_str = "";
            $da_qb_id_str = "";
            $dm_incentive = 0;
            $da_incentive = 0;
            $da_incentive_rate = 0;
            $dm_incentive_rate = 0;

            $currnet_month_sale = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale, assist_course.mcourse 
                            FROM assist_participant LEFT JOIN  assist_course ON assist_participant.course = assist_course.id
                            WHERE Month(assist_participant.created_on) = $month AND YEAR(assist_participant.created_on) = $year AND 
                                assist_participant.sales_user_id=$user_id GROUP BY assist_course.mcourse")->queryAll();

            if (!empty($currnet_month_sale)) {
                foreach ($currnet_month_sale as $sale) {
                    if ($sale['mcourse'] == 'da') {
                        $data[$year][$month]['da_sale'] = $sale['total_sale'];
                    }
                    if ($sale['mcourse'] == 'dm') {
                        $data[$year][$month]['dm_sale'] = $sale['total_sale'];
                    }
                }
            }

            $get_da_exception_rate = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_exception_rate 
                    WHERE month=$month AND years=$year AND domain='da' AND executive_id=$user_id")->queryAll();

            if (!empty($get_da_exception_rate)) {
                $recent_date = array();
                foreach ($get_da_exception_rate as $exception) {

                    $recent_date[] = $exception['created_at'];
                }

                foreach ($get_da_exception_rate as $exception) {
                    if (end($recent_date) == $exception['created_at']) { /* Display lastly added rules */
                        if ($data[$year][$month]['da_sale'] >= $exception['min_closures'] &&
                                $data[$year][$month]['da_sale'] <= $exception['max_closures']) {
                            $da_incentive_rate = $exception['rate'];
                        }
                    }
                }
            }

            $get_dm_exception_rate = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_exception_rate 
                    WHERE month=$month AND years=$year AND domain='dm' AND executive_id=$user_id")->queryAll();

            if (!empty($get_dm_exception_rate)) {
                $recent_date = array();
                foreach ($get_dm_exception_rate as $exception) {

                    $recent_date[] = $exception['created_at'];
                }

                foreach ($get_dm_exception_rate as $exception) {
                    if (end($recent_date) == $exception['created_at']) { /* Display lastly added rules */
                        if ($data[$year][$month]['da_sale'] >= $exception['min_closures'] &&
                                $data[$year][$month]['da_sale'] <= $exception['max_closures']) {
                            $dm_incentive_rate = $exception['rate'];
                        }
                    }
                }
            }

            if ($da_incentive_rate == 0) {
                $get_da_incentive_rate = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_rate 
                    WHERE month=$month AND year=$year AND mcourse='da'")->queryAll();

                if (!empty($get_da_incentive_rate)) {
                    $recent_date = array();
                    foreach ($get_da_incentive_rate as $incentive) {

                        $recent_date[] = $incentive['created_at'];
                    }

                    foreach ($get_da_incentive_rate as $incentive) {
                        if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                            if ($data[$year][$month]['da_sale'] >= $incentive['min_closures'] &&
                                    $data[$year][$month]['da_sale'] <= $incentive['max_closures']) {
                                $da_incentive_rate = $incentive['rate'];
                            }
                        }
                    }
                }
            }

            if ($dm_incentive_rate == 0) {
                $get_dm_incentive_rate = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_rate 
                    WHERE month=$month AND year=$year AND mcourse='dm'")->queryAll();

                if (!empty($get_dm_incentive_rate)) {
                    $recent_date = array();
                    foreach ($get_dm_incentive_rate as $incentive) {

                        $recent_date[] = $incentive['created_at'];
                    }
                    foreach ($get_dm_incentive_rate as $incentive) {
                        if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                            if ($data[$year][$month]['dm_sale'] >= $incentive['min_closures'] &&
                                    $data[$year][$month]['dm_sale'] <= $incentive['max_closures']) {
                                $dm_incentive_rate = $incentive['rate'];
                            }
                        }
                    }
                }
            }

            $get_dm_qb_customer_id = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$user_id AND Month(assist_participant.created_on) = $month AND 
                            YEAR(assist_participant.created_on) = $year AND assist_course.mcourse='dm' AND 
                                qb_customer_id IS NOT NULL")->queryAll();

            $get_da_qb_customer_id = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$user_id AND Month(assist_participant.created_on) = $month AND 
                            YEAR(assist_participant.created_on) = $year AND assist_course.mcourse='da' AND 
                                qb_customer_id IS NOT NULL")->queryAll();

            if (!empty($get_dm_qb_customer_id)) {
                foreach ($get_dm_qb_customer_id as $qb) {
                    $da_dm_qb_id[] = $qb['qb_customer_id'];
                    $dm_qb_id_str .= "'" . $qb['qb_customer_id'] . "',";
                }
            }
            if (!empty($get_da_qb_customer_id)) {
                foreach ($get_da_qb_customer_id as $qb) {
                    $da_dm_qb_id[] = $qb['qb_customer_id'];
                    $da_qb_id_str .= "'" . $qb['qb_customer_id'] . "',";
                }
            }
            $dm_qb_id_str = rtrim($dm_qb_id_str, ",");
            $da_qb_id_str = rtrim($da_qb_id_str, ",");

            $total_payment = $dataService->Query("SELECT count(*) FROM Payment");
            $all_da_payments = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($da_qb_id_str) 
                        MAXRESULTS $total_payment");
            $all_dm_payments = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($dm_qb_id_str) 
                        MAXRESULTS $total_payment");

            if (!empty($all_da_payments)) {
                foreach ($all_da_payments as $payment) {
                    if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                            date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                        $data[$year][$month]['da_fresh_payment'] += $payment->TotalAmt;
                    }
                }
            }

            if (!empty($all_dm_payments)) {
                foreach ($all_dm_payments as $payment) {
                    if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                            date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                        $data[$year][$month]['dm_fresh_payment'] += $payment->TotalAmt;
                    }
                }
            }

            $data[$year][$month]['dm_incentive_rate'] = $dm_incentive_rate;
            $data[$year][$month]['da_incentive_rate'] = $da_incentive_rate;
            $dm_incentive = ($data[$year][$month]['dm_fresh_payment'] * $dm_incentive_rate) / 100;
            $da_incentive = ($data[$year][$month]['da_fresh_payment'] * $da_incentive_rate) / 100;
            $data[$year][$month]['total_fresh_incentive'] = $da_incentive + $dm_incentive;
            $data[$year][$month]['total_fresh_incentive'] = number_format((float) $data[$year][$month]['total_fresh_incentive'], 2, '.', '');

        }
        for ($i = 1; $i < 6; $i++) {
            $month = date('m', strtotime("-$i month"));
            $year = date('Y', strtotime("-$i month"));

            $data[$year][$month]['da_sale'] = 0;
            $data[$year][$month]['dm_sale'] = 0;
            $data[$year][$month]['da_fresh_payment'] = 0;
            $data[$year][$month]['dm_fresh_payment'] = 0;
            $dm_qb_id_str = "";
            $da_qb_id_str = "";
            $dm_incentive = 0;
            $da_incentive = 0;
            $da_incentive_rate = 0;
            $dm_incentive_rate = 0;

            $currnet_month_sale = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale, assist_course.mcourse 
                            FROM assist_participant LEFT JOIN  assist_course ON assist_participant.course = assist_course.id
                            WHERE Month(assist_participant.created_on) = $month AND YEAR(assist_participant.created_on) = $year AND 
                                assist_participant.sales_user_id=$user_id GROUP BY assist_course.mcourse")->queryAll();

            if (!empty($currnet_month_sale)) {
                foreach ($currnet_month_sale as $sale) {
                    if ($sale['mcourse'] == 'da') {
                        $data[$year][$month]['da_sale'] = $sale['total_sale'];
                    }
                    if ($sale['mcourse'] == 'dm') {
                        $data[$year][$month]['dm_sale'] = $sale['total_sale'];
                    }
                }
            }
            
            $get_da_exception_rate = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_exception_rate 
                    WHERE month=$month AND years=$year AND domain='da' AND executive_id=$user_id")->queryAll();
            if (!empty($get_da_exception_rate)) {
                $recent_date = array();
                foreach ($get_da_exception_rate as $exception) {

                    $recent_date[] = $exception['created_at'];
                }

                foreach ($get_da_exception_rate as $exception) {
                    if (end($recent_date) == $exception['created_at']) { /* Display lastly added rules */
                        if ($data[$year][$month]['da_sale'] >= $exception['min_closures'] &&
                                $data[$year][$month]['da_sale'] <= $exception['max_closures']) {
                            $da_incentive_rate = $exception['rate'];
                        }
                    }
                }
            }

            $get_dm_exception_rate = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_exception_rate 
                    WHERE month=$month AND years=$year AND domain='dm' AND executive_id=$user_id")->queryAll();
            if (!empty($get_dm_exception_rate)) {
                $recent_date = array();
                foreach ($get_dm_exception_rate as $exception) {

                    $recent_date[] = $exception['created_at'];
                }

                foreach ($get_dm_exception_rate as $exception) {
                    if (end($recent_date) == $exception['created_at']) { /* Display lastly added rules */
                        if ($data[$year][$month]['da_sale'] >= $exception['min_closures'] &&
                                $data[$year][$month]['da_sale'] <= $exception['max_closures']) {
                            $dm_incentive_rate = $exception['rate'];
                        }
                    }
                }
            }

            if ($da_incentive_rate == 0) {
                $get_da_incentive_rate = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_rate 
                    WHERE month=$month AND year=$year AND mcourse='da'")->queryAll();
                if (!empty($get_da_incentive_rate)) {
                    $recent_date = array();
                    foreach ($get_da_incentive_rate as $incentive) {

                        $recent_date[] = $incentive['created_at'];
                    }

                    foreach ($get_da_incentive_rate as $incentive) {
                        if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                            if ($data[$year][$month]['da_sale'] >= $incentive['min_closures'] &&
                                    $data[$year][$month]['da_sale'] <= $incentive['max_closures']) {
                                $da_incentive_rate = $incentive['rate'];
                            }
                        }
                    }
                }
            }

            if ($dm_incentive_rate == 0) {
                $get_dm_incentive_rate = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_rate 
                    WHERE month=$month AND year=$year AND mcourse='dm'")->queryAll();
                if (!empty($get_dm_incentive_rate)) {
                    $recent_date = array();
                    foreach ($get_dm_incentive_rate as $incentive) {

                        $recent_date[] = $incentive['created_at'];
                    }
                    foreach ($get_dm_incentive_rate as $incentive) {
                        if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                            if ($data[$year][$month]['dm_sale'] >= $incentive['min_closures'] &&
                                    $data[$year][$month]['dm_sale'] <= $incentive['max_closures']) {
                                $dm_incentive_rate = $incentive['rate'];
                            }
                        }
                    }
                }
            }

            $get_dm_qb_customer_id = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$user_id AND Month(assist_participant.created_on) = $month AND 
                            YEAR(assist_participant.created_on) = $year AND assist_course.mcourse='dm' AND 
                                qb_customer_id IS NOT NULL")->queryAll();

            $get_da_qb_customer_id = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$user_id AND Month(assist_participant.created_on) = $month AND 
                            YEAR(assist_participant.created_on) = $year AND assist_course.mcourse='da' AND 
                                qb_customer_id IS NOT NULL")->queryAll();

            if (!empty($get_dm_qb_customer_id)) {
                foreach ($get_dm_qb_customer_id as $qb) {
                    $da_dm_qb_id[] = $qb['qb_customer_id'];
                    $dm_qb_id_str .= "'" . $qb['qb_customer_id'] . "',";
                }
            }
            if (!empty($get_da_qb_customer_id)) {
                foreach ($get_da_qb_customer_id as $qb) {
                    $da_dm_qb_id[] = $qb['qb_customer_id'];
                    $da_qb_id_str .= "'" . $qb['qb_customer_id'] . "',";
                }
            }
            $dm_qb_id_str = rtrim($dm_qb_id_str, ",");
            $da_qb_id_str = rtrim($da_qb_id_str, ",");

            $total_payment = $dataService->Query("SELECT count(*) FROM Payment");
            $all_da_payments = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($da_qb_id_str) 
                        MAXRESULTS $total_payment");
            $all_dm_payments = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($dm_qb_id_str) 
                        MAXRESULTS $total_payment");

            if (!empty($all_da_payments)) {
                foreach ($all_da_payments as $payment) {
                    if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                            date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                        $data[$year][$month]['da_fresh_payment'] += $payment->TotalAmt;
                    }
                }
            }

            if (!empty($all_dm_payments)) {
                foreach ($all_dm_payments as $payment) {
                    if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                            date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                        $data[$year][$month]['dm_fresh_payment'] += $payment->TotalAmt;
                    }
                }
            }

            $data[$year][$month]['dm_incentive_rate'] = $dm_incentive_rate;
            $data[$year][$month]['da_incentive_rate'] = $da_incentive_rate;
            $dm_incentive = ($data[$year][$month]['dm_fresh_payment'] * $dm_incentive_rate) / 100;
            $da_incentive = ($data[$year][$month]['da_fresh_payment'] * $da_incentive_rate) / 100;
            $data[$year][$month]['total_fresh_incentive'] = $da_incentive + $dm_incentive;
            $data[$year][$month]['total_fresh_incentive'] = number_format((float) $data[$year][$month]['total_fresh_incentive'], 2, '.', '');
        }
        return $data;
    }

    public function incentives_from_full_payments() {
        $dataService = $this->quickbook_instance();
        $user_id = Yii::$app->getUser()->identity->id;
        $years = array();
        $data = array();

        for ($i = 0; $i < 7; $i++) {
            $month = date('m', strtotime("+$i month"));
            $year = date('Y', strtotime("+$i month"));
            $full_payment_qb_id_str = "";
            $full_payment_incentive_rate = 0;
            $data[$year][$month]['all_full_payment'] = 0;
            $data[$year][$month]['full_payment_sale'] = 0;

            $total_full_payment_sale = Yii::$app->db->createCommand("SELECT COUNT(*) as total_full_payment_sale FROM assist_participant 
                        WHERE Month(assist_participant.created_on) = $month AND YEAR(assist_participant.created_on) = $year AND 
                            assist_participant.sales_user_id=$user_id AND is_full_payment=1")->queryAll();
            if (!empty($total_full_payment_sale)) {
                $data[$year][$month]['full_payment_sale'] = $total_full_payment_sale[0]['total_full_payment_sale'];
            }

            $get_full_payment_incentive_rate = Yii::$app->db->createCommand("SELECT * FROM assist_fully_payment_incentive_rate 
                    WHERE month=$month AND year=$year")->queryAll();
            if (!empty($get_full_payment_incentive_rate)) {
                $recent_date = array();
                foreach ($get_full_payment_incentive_rate as $incentive) {

                    $recent_date[] = $incentive['created_at'];
                }
                foreach ($get_full_payment_incentive_rate as $incentive) {
                    if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                        if ($data[$year][$month]['full_payment_sale'] >= $incentive['min_closures'] &&
                                $data[$year][$month]['full_payment_sale'] <= $incentive['max_closures']) {
                            $full_payment_incentive_rate = $incentive['rate'];
                        }
                    }
                }
            }

            $get_qb_customer_id_full_payment = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$user_id AND assist_participant.is_full_payment=1 
                            AND Month(assist_participant.created_on) = $month AND YEAR(assist_participant.created_on) = $year AND 
                                assist_course.mcourse='dm' AND qb_customer_id IS NOT NULL")->queryAll();

            if (!empty($get_qb_customer_id_full_payment)) {
                foreach ($get_qb_customer_id_full_payment as $qb) {
                    $full_payment_qb_id_str .= "'" . $qb['qb_customer_id'] . "',";
                }
            }
            $full_payment_qb_id_str = rtrim($full_payment_qb_id_str, ",");
            $total_payment = $dataService->Query("SELECT count(*) FROM Payment");
            $all_full_payments = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($full_payment_qb_id_str) 
                        MAXRESULTS $total_payment");

            if (!empty($all_full_payments)) {
                foreach ($all_full_payments as $payment) {
                    if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                            date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                        $data[$year][$month]['all_full_payment'] += $payment->TotalAmt;
                    }
                }
            }

            $data[$year][$month]['full_payment_incentive_rate'] = $full_payment_incentive_rate;
            $full_payment_incentive = ($data[$year][$month]['all_full_payment'] *
                    $full_payment_incentive_rate) / 100;
            $data[$year][$month]['full_payment_incentive'] = number_format((float) $full_payment_incentive, 2, '.', '');
        }
        for ($i = 1; $i < 6; $i++) {
            $month = date('m', strtotime("-$i month"));
            $year = date('Y', strtotime("-$i month"));

            $full_payment_qb_id_str = "";
            $full_payment_incentive_rate = 0;
            $data[$year][$month]['all_full_payment'] = 0;
            $data[$year][$month]['full_payment_sale'] = 0;

            $total_full_payment_sale = Yii::$app->db->createCommand("SELECT COUNT(*) as total_full_payment_sale FROM assist_participant 
                        WHERE Month(assist_participant.created_on) = $month AND YEAR(assist_participant.created_on) = $year AND 
                            assist_participant.sales_user_id=$user_id AND is_full_payment=1")->queryAll();
            if (!empty($total_full_payment_sale)) {
                $data[$year][$month]['full_payment_sale'] = $total_full_payment_sale[0]['total_full_payment_sale'];
            }

            $get_full_payment_incentive_rate = Yii::$app->db->createCommand("SELECT * FROM assist_fully_payment_incentive_rate 
                    WHERE month=$month AND year=$year")->queryAll();
            if (!empty($get_full_payment_incentive_rate)) {
                $recent_date = array();
                foreach ($get_full_payment_incentive_rate as $incentive) {

                    $recent_date[] = $incentive['created_at'];
                }
                foreach ($get_full_payment_incentive_rate as $incentive) {
                    if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                        if ($data[$year][$month]['full_payment_sale'] >= $incentive['min_closures'] &&
                                $data[$year][$month]['full_payment_sale'] <= $incentive['max_closures']) {
                            $full_payment_incentive_rate = $incentive['rate'];
                        }
                    }
                }
            }

            $get_qb_customer_id_full_payment = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$user_id AND assist_participant.is_full_payment=1 
                            AND Month(assist_participant.created_on) = $month AND YEAR(assist_participant.created_on) = $year AND 
                                assist_course.mcourse='dm' AND qb_customer_id IS NOT NULL")->queryAll();

            if (!empty($get_qb_customer_id_full_payment)) {
                foreach ($get_qb_customer_id_full_payment as $qb) {
                    $full_payment_qb_id_str .= "'" . $qb['qb_customer_id'] . "',";
                }
            }
            $full_payment_qb_id_str = rtrim($full_payment_qb_id_str, ",");
            $total_payment = $dataService->Query("SELECT count(*) FROM Payment");
            $all_full_payments = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($full_payment_qb_id_str) 
                        MAXRESULTS $total_payment");

            if (!empty($all_full_payments)) {
                foreach ($all_full_payments as $payment) {
                    if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                            date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                        $data[$year][$month]['all_full_payment'] += $payment->TotalAmt;
                    }
                }
            }

            $data[$year][$month]['full_payment_incentive_rate'] = $full_payment_incentive_rate;
            $full_payment_incentive = ($data[$year][$month]['all_full_payment'] *
                    $full_payment_incentive_rate) / 100;
            $data[$year][$month]['full_payment_incentive'] = number_format((float) $full_payment_incentive, 2, '.', '');
        }
        return $data;
    }

    public function actionView_monthly_incentive_rate() {

        $user_id = Yii::$app->getUser()->identity->id;
        $incentives_from_instalment_payments = $this->incentives_from_instalment_payments();
        $incentive_for_fresh_payments = $this->incentive_for_fresh_payments();
        $incentives_from_full_payments = $this->incentives_from_full_payments();

        $user = Yii::$app->user->identity;
        $usermeta_result = DvUserMeta::find()->where(['uid' => $user->id, 'meta_key' => 'role'])->one();
        $user_role = $usermeta_result->meta_value; /*  2 - Executive role. // 1 - Admin */

        if ($user->department == 1 || $user->department == 8) {/* Only users of Sales department can view this. 8 is for Super Admin. */
        } else {
            return $this->redirect(['site/index']);
        }
        if ($user_role == 2) {
            $exceptions = DvManageMonthlyIncentiveExceptionRate::find()->where(['executive_id' => $user_id])->all();
        } else {
            $exceptions = DvManageMonthlyIncentiveExceptionRate::find()->all();
        }

        $full_payment_incentives = DvManageFullPaymentIncentiveRate::find();
        $incentives = DvManageMonthlyIncentiveRate::find();
        $years = array();
        $after_6_month = array();
        $before_6_month = array();

        for ($i = 0; $i < 7; $i++) {
            $incentives->orWhere(["month" => date('m', strtotime("+$i month")), "year" => date('Y', strtotime("+$i month"))]);
            $full_payment_incentives->orWhere(["month" => date('m', strtotime("+$i month")), "year" => date('Y', strtotime("+$i month"))]);
            $after_6_month[date('Y', strtotime("+$i month"))][date('m', strtotime("+$i month"))]['month'] = date('m', strtotime("+$i month"));

            $total_da = DvRegistration::find()
                    ->leftJoin('assist_course', 'assist_participant.course=assist_course.id')
                    ->where(["MONTH(assist_participant.created_on)" => date('m', strtotime("+$i month")), "YEAR(assist_participant.created_on)" => date('Y', strtotime("+$i month")), 'assist_participant.sales_user_id' => $user_id, 'assist_course.mcourse' => 'da'])
                    ->count();

            $total_dm = DvRegistration::find()
                    ->leftJoin('assist_course', 'assist_participant.course=assist_course.id')
                    ->where(["MONTH(assist_participant.created_on)" => date('m', strtotime("+$i month")), "YEAR(assist_participant.created_on)" => date('Y', strtotime("+$i month")), 'assist_course.mcourse' => 'dm', 'assist_participant.sales_user_id' => $user_id])
                    ->count();

            $total_full_pay = DvRegistration::find()
                    ->where(["MONTH(created_on)" => date('m', strtotime("+$i month")), "YEAR(created_on)" => date('Y', strtotime("+$i month")), 'is_full_payment' => 1, 'assist_participant.sales_user_id' => $user_id])
                    ->count();

            $after_6_month[date('Y', strtotime("+$i month"))][date('m', strtotime("+$i month"))]['total_da'] = $total_da;
            $after_6_month[date('Y', strtotime("+$i month"))][date('m', strtotime("+$i month"))]['total_dm'] = $total_dm;
            $after_6_month[date('Y', strtotime("+$i month"))][date('m', strtotime("+$i month"))]['full_payment'] = $total_full_pay;

            $years[] = date('Y', strtotime("+$i month"));
        }
        for ($i = 1; $i < 6; $i++) {
            $incentives->orWhere(["month" => date('m', strtotime("-$i month")), "year" => date('Y', strtotime("-$i month"))]);
            $full_payment_incentives->orWhere(["month" => date('m', strtotime("-$i month")), "year" => date('Y', strtotime("-$i month"))]);
            // $before_6_month[date('Y',strtotime("-$i month"))][] = date('m',strtotime("-$i month"));
            $before_6_month[date('Y', strtotime("-$i month"))][date('m', strtotime("-$i month"))]['month'] = date('m', strtotime("-$i month"));

            $total_da = DvRegistration::find()
                    ->leftJoin('assist_course', 'assist_participant.course=assist_course.id')
                    ->where(["MONTH(assist_participant.created_on)" => date('m', strtotime("-$i month")), "YEAR(assist_participant.created_on)" => date('Y', strtotime("-$i month")), 'assist_participant.sales_user_id' => $user_id, 'assist_course.mcourse' => 'da'])
                    ->count();

            $total_dm = DvRegistration::find()
                    ->leftJoin('assist_course', 'assist_participant.course=assist_course.id')
                    ->where(["MONTH(assist_participant.created_on)" => date('m', strtotime("-$i month")), "YEAR(assist_participant.created_on)" => date('Y', strtotime("-$i month")), 'assist_course.mcourse' => 'dm', 'assist_participant.sales_user_id' => $user_id])
                    ->count();

            $total_full_pay = DvRegistration::find()
                    ->where(["MONTH(created_on)" => date('m', strtotime("-$i month")), "YEAR(created_on)" => date('Y', strtotime("-$i month")), 'is_full_payment' => 1, 'assist_participant.sales_user_id' => $user_id])
                    ->count();

            $before_6_month[date('Y', strtotime("-$i month"))][date('m', strtotime("-$i month"))]['total_da'] = $total_da;
            $before_6_month[date('Y', strtotime("-$i month"))][date('m', strtotime("-$i month"))]['total_dm'] = $total_dm;
            $before_6_month[date('Y', strtotime("-$i month"))][date('m', strtotime("-$i month"))]['full_payment'] = $total_full_pay;

            $years[] = date('Y', strtotime("-$i month"));
        }

        $years = array_unique($years);
        asort($years);
        $incentives = $incentives->all();
        $full_payment_incentives = $full_payment_incentives->all();

        return $this->render('view_monthly_incentive_rate', ['incentives' => $incentives,
                    'full_payment_incentives' => $full_payment_incentives,
                    'exceptions' => $exceptions,
                    'user_role' => $user_role,
                    'before_6_month' => $before_6_month,
                    'after_6_month' => $after_6_month,
                    'years' => $years,
                    'incentives_from_full_payments' => $incentives_from_full_payments,
                    'incentive_for_fresh_payments' => $incentive_for_fresh_payments,
                    'incentives_from_instalment_payments' => $incentives_from_instalment_payments]);
    }

    public function actionManage_full_payment_incentive_rate() {
        if (!Yii::$app->CustomComponents->check_permission('manage_full_payment_incentive_rate')) {
            return $this->redirect(['site/index']);
        }

        $current_month = date('m');
        $next_6_month = $current_month + 6;

        if ($next_6_month > 12) {
            
        }

        $user_id = Yii::$app->getUser()->identity->id;
        $user = Yii::$app->user->identity;
        if ($user->department == 1 || $user->department == 8) {/* Only users of Sales department can view this. 8 is for Super Admin. */
        } else {
            return $this->redirect(['site/index']);
        }

        $incentives = DvManageFullPaymentIncentiveRate::find();
        $years = array();
        $after_6_month = array();
        $before_6_month = array();

        for ($i = 0; $i < 7; $i++) {
            $incentives->orWhere(["month" => date('m', strtotime("+$i month")), "year" => date('Y', strtotime("+$i month"))]);
            $after_6_month[date('Y', strtotime("+$i month"))][] = date('m', strtotime("+$i month"));
            $years[] = date('Y', strtotime("+$i month"));
        }
        for ($i = 1; $i < 6; $i++) {
            $incentives->orWhere(["month" => date('m', strtotime("-$i month")), "year" => date('Y', strtotime("-$i month"))]);
            $before_6_month[date('Y', strtotime("-$i month"))][] = date('m', strtotime("-$i month"));
            $years[] = date('Y', strtotime("-$i month"));
        }

        $years = array_unique($years);
        asort($years);
        $incentives = $incentives->all();

        $model = new DvManageFullPaymentIncentiveRate();

        $inserted_date = date("Y-m-d H:i:s");
        if ($model->load(Yii::$app->request->post())) {
            if (isset($_POST['DvManageFullPaymentIncentiveRate'])) {
                if (isset($_POST['DvManageFullPaymentIncentiveRate']['month'])) {
                    $total_number_of_month = sizeof($_POST['DvManageFullPaymentIncentiveRate']['month']);
                    for ($i = 0; $i < $total_number_of_month; $i++) {
                        if (isset($_POST['DvManageFullPaymentIncentiveRate']['min_closures'])) {
                            $total_closures_range = sizeof($_POST['DvManageFullPaymentIncentiveRate']['min_closures']);
                            for ($j = 0; $j < $total_closures_range; $j++) {

                                $DvManageFullPaymentIncentiveRate = new DvManageFullPaymentIncentiveRate();
                                $DvManageFullPaymentIncentiveRate->year = $_POST['DvManageFullPaymentIncentiveRate']['year'];
                                $DvManageFullPaymentIncentiveRate->month = $_POST['DvManageFullPaymentIncentiveRate']['month'][$i];
                                $DvManageFullPaymentIncentiveRate->min_closures = $_POST['DvManageFullPaymentIncentiveRate']['min_closures'][$j];
                                $DvManageFullPaymentIncentiveRate->max_closures = $_POST['DvManageFullPaymentIncentiveRate']['max_closures'][$j];
                                $DvManageFullPaymentIncentiveRate->rate = $_POST['DvManageFullPaymentIncentiveRate']['rate'][$j];
                                $DvManageFullPaymentIncentiveRate->created_at = $inserted_date;
                                $DvManageFullPaymentIncentiveRate->updated_at = $inserted_date;
                                if ($DvManageFullPaymentIncentiveRate->save()) {
                                    
                                }
                            }
                        }
                    }
                }
            }
            Yii::$app->session->setFlash('success', 'New Full Payment Incentive Rule Created.');
            return $this->redirect(['dv-finance/manage_full_payment_incentive_rate']);
        } else {
            return $this->render('manage_full_payment_incentive_rate', [ 'model' => $model, 'incentives' => $incentives, 'before_6_month' => $before_6_month, 'after_6_month' => $after_6_month, 'years' => $years]);
        }
    }

    public function actionView_full_payment_incentive_rate() {
        if (!Yii::$app->CustomComponents->check_permission('view_full_payment_incentive_rate')) {
            return $this->redirect(['site/index']);
        }

        $user_id = Yii::$app->getUser()->identity->id;
        $user = Yii::$app->user->identity;
        if ($user->department == 1 || $user->department == 8) {/* Only users of Sales department can view this. 8 is for Super Admin. */
        } else {
            return $this->redirect(['site/index']);
        }

        $incentives = DvManageFullPaymentIncentiveRate::find();
        $years = array();
        $after_6_month = array();
        $before_6_month = array();

        for ($i = 0; $i < 7; $i++) {
            $incentives->orWhere(["month" => date('m', strtotime("+$i month")), "year" => date('Y', strtotime("+$i month"))]);
            $after_6_month[date('Y', strtotime("+$i month"))][] = date('m', strtotime("+$i month"));
            $years[] = date('Y', strtotime("+$i month"));
        }
        for ($i = 1; $i < 6; $i++) {
            $incentives->orWhere(["month" => date('m', strtotime("-$i month")), "year" => date('Y', strtotime("-$i month"))]);
            $before_6_month[date('Y', strtotime("-$i month"))][] = date('m', strtotime("-$i month"));
            $years[] = date('Y', strtotime("-$i month"));
        }

        $years = array_unique($years);
        asort($years);
        $incentives = $incentives->all();

        return $this->render('view_full_payment_incentive_rate', ['incentives' => $incentives, 'before_6_month' => $before_6_month, 'after_6_month' => $after_6_month, 'years' => $years]);
    }

    public function actionManage_team_member_exception() {
        if (!Yii::$app->CustomComponents->check_permission('manage_team_member_exception')) {
            return $this->redirect(['site/index']);
        }

        $exception_number = 1;
        $user = Yii::$app->user->identity;
        if ($user->department == 1 || $user->department == 8) {/* Only users of Sales department can view this. 8 is for Super Admin. */
        } else {
            return $this->redirect(['site/index']);
        }

        $model = new DvManageMonthlyIncentiveExceptionRate();
        $executives = Yii::$app->db->createCommand("SELECT du.first_name, du.last_name, du.id, aum.meta_value as role FROM assist_users as du JOIN assist_user_meta aum ON aum.uid = du.id WHERE aum.meta_key = 'role' AND aum.meta_value IN(2,6) AND du.department = 1 AND status = 1 ")->queryAll();

        $inserted_date = date("Y-m-d H:i:s");

        if ($model->load(Yii::$app->request->post())) {
            if (isset($_POST['DvManageMonthlyIncentiveExceptionRate']['min_closures'])) {

                $total_month = sizeof($_POST['DvManageMonthlyIncentiveExceptionRate']['min_closures']);
                $min_closures = $_POST['DvManageMonthlyIncentiveExceptionRate']['min_closures'];
                $max_closures = $_POST['DvManageMonthlyIncentiveExceptionRate']['max_closures'];
                $rates = $_POST['DvManageMonthlyIncentiveExceptionRate']['rate'];
                foreach ($min_closures as $key => $min_closure) {/* Month wise closures */
                    $old_exception_number = DvManageMonthlyIncentiveExceptionRate::find()
                                    ->select(['exception_number'])->orderBy(['id' => SORT_DESC])->one();
                    if (!empty($old_exception_number)) {
                        $exception_number = $old_exception_number->exception_number + 1;
                    }
                    foreach ($min_closure as $min_key => $closure) {

                        $DvManageMonthlyIncentiveExceptionRate = new DvManageMonthlyIncentiveExceptionRate();
                        $DvManageMonthlyIncentiveExceptionRate->executive_id = $_POST['DvManageMonthlyIncentiveExceptionRate']['executive_id'];
                        $DvManageMonthlyIncentiveExceptionRate->month = $key;
                        $DvManageMonthlyIncentiveExceptionRate->years = $_POST['DvManageMonthlyIncentiveExceptionRate']['year_to_save'];
                        $DvManageMonthlyIncentiveExceptionRate->domain = $_POST['DvManageMonthlyIncentiveExceptionRate']['domain'];
                        $DvManageMonthlyIncentiveExceptionRate->min_closures = $closure;
                        $DvManageMonthlyIncentiveExceptionRate->max_closures = $max_closures[$key][$min_key];
                        if ($DvManageMonthlyIncentiveExceptionRate->max_closures <= $DvManageMonthlyIncentiveExceptionRate->min_closures) {
                            Yii::$app->session->setFlash('danger', 'Max Closures  must be greater than Min Closures.' . $DvManageMonthlyIncentiveExceptionRate->max_closures . ' <= ' . $DvManageMonthlyIncentiveExceptionRate->min_closures);
                            return $this->redirect(['dv-finance/manage_team_member_exception']);
                        }
                        $DvManageMonthlyIncentiveExceptionRate->rate = $rates[$key][$min_key];
                        $DvManageMonthlyIncentiveExceptionRate->created_at = $inserted_date;
                        $DvManageMonthlyIncentiveExceptionRate->updated_at = $inserted_date;
                        $DvManageMonthlyIncentiveExceptionRate->exception_number = $exception_number;
                        $DvManageMonthlyIncentiveExceptionRate->save();
                    }
                }
            }
            Yii::$app->session->setFlash('success', 'Incentive Exception Rule Created Successfully.');
            return $this->redirect(['dv-finance/manage_team_member_exception']);
        } else {
            return $this->render('manage_team_member_exception_rate', [ 'model' => $model, 'executives' => $executives]);
        }
    }

    public function actionFilter_incentive_exception() {
        if (!Yii::$app->CustomComponents->check_permission('filter_incentive_exception')) {
            return $this->redirect(['site/index']);
        }

        $user = Yii::$app->user->identity;
        if ($user->department == 1 || $user->department == 8) {/* Only users of Sales department can view this. 8 is for Super Admin. */
        } else {
            return $this->redirect(['site/index']);
        }

        $model = new DvManageMonthlyIncentiveExceptionRate();
        $executives = Yii::$app->db->createCommand("SELECT * FROM assist_users as du 
            WHERE du.id IN(SELECT uid FROM assist_user_meta WHERE meta_key = 'role' AND meta_value=2) AND status = 1 ")->queryAll();

        $inserted_date = date("Y-m-d H:i:s");

        if ($model->load(Yii::$app->request->post())) {
            if (isset($_POST['DvManageMonthlyIncentiveExceptionRate']['min_closures'])) {
                $total_closures_range = sizeof($_POST['DvManageMonthlyIncentiveExceptionRate']['min_closures']);
                for ($j = 0; $j < $total_closures_range; $j++) {
                    $DvManageMonthlyIncentiveExceptionRate = new DvManageMonthlyIncentiveExceptionRate();
                    $DvManageMonthlyIncentiveExceptionRate->executive_id = $_POST['DvManageMonthlyIncentiveExceptionRate']['executive_id'];
                    $DvManageMonthlyIncentiveExceptionRate->month = $_POST['DvManageMonthlyIncentiveExceptionRate']['month'];
                    $DvManageMonthlyIncentiveExceptionRate->domain = $_POST['DvManageMonthlyIncentiveExceptionRate']['domain'];
                    $DvManageMonthlyIncentiveExceptionRate->min_closures = $_POST['DvManageMonthlyIncentiveExceptionRate']['min_closures'][$j];
                    $DvManageMonthlyIncentiveExceptionRate->max_closures = $_POST['DvManageMonthlyIncentiveExceptionRate']['max_closures'][$j];
                    $DvManageMonthlyIncentiveExceptionRate->rate = $_POST['DvManageMonthlyIncentiveExceptionRate']['rate'][$j];
                    $DvManageMonthlyIncentiveExceptionRate->created_at = $inserted_date;
                    $DvManageMonthlyIncentiveExceptionRate->updated_at = $inserted_date;
                    if ($DvManageMonthlyIncentiveExceptionRate->save()) {
                        
                    }
                }
            }
            Yii::$app->session->setFlash('success', 'Incentive Exception Rule Created Successfully.');
            return $this->redirect(['dv-finance/manage_team_member_exception']);
        } else {
            return $this->render('manage_team_member_exception_rate', [ 'model' => $model, 'executives' => $executives]);
        }
    }

    public function actionCheck_current_exception_rate() {

        $current_incentive = array();
        $return_arr = array();
        $month_exception = array();
        $return_arr['is_exception'] = 0;
        $return_arr['current_incentive'] = array();
        $list_of_month_exception = array();

        if (!empty(Yii::$app->request->post())) {
            $months = Yii::$app->request->post('months');
            $domain = Yii::$app->request->post('domain');
            $years = Yii::$app->request->post('years');
            $executive_id = Yii::$app->request->post('executive_id');
            if (!empty($months) && !empty($domain) && !empty($executive_id)) {
                $month_exception = DvManageMonthlyIncentiveExceptionRate::find()->where(['domain' => $domain, 'executive_id' => $executive_id, 'years' => $years])->andWhere(["in", "month", $months])->all();
                $incentive_rate = DvManageMonthlyIncentiveRate::find()->where(['mcourse' => $domain, 'year' => $years])->andWhere(["in", "month", $months])->all();
                if (!empty($month_exception)) {
                    if (!empty($months)) {
                        foreach ($months as $month) {
                            foreach ($month_exception as $exception) {
                                $list_of_month_exception[$month]['month_number'] = $month;
                                if ($exception->month == $month) {
                                    $list_of_month_exception[$month]['exception_number'] = $exception->exception_number;
                                    $list_of_month_exception[$month]['is_exception'] = 1;
                                }
                            }
                            foreach ($month_exception as $exception) {
                                if ($exception->month == $month && $exception->exception_number == $list_of_month_exception[$month]['exception_number']) {
                                    $exception_arr = array();
                                    $exception_arr['executive_id'] = $exception->executive_id;
                                    $exception_arr['domain'] = $exception->domain;
                                    $exception_arr['month'] = $exception->month;
                                    $exception_arr['min_closures'] = $exception->min_closures;
                                    $exception_arr['max_closures'] = $exception->max_closures;
                                    $exception_arr['rate'] = $exception->rate;
                                    $exception_arr['exception_number'] = $exception->exception_number;
                                    $list_of_month_exception[$month]['exceptions'][] = $exception_arr;
                                }
                            }
                        }
                    }
                } else if (!empty($incentive_rate)) {
                    if (!empty($months)) {
                        foreach ($months as $month) {
                            $recent_date = array();
                            foreach ($incentive_rate as $incentive) {
                                $list_of_month_exception[$month]['month_number'] = $month;
                                if ($incentive->month == $month) {
                                    $list_of_month_exception[$month]['exception_number'] = 0;
                                    $list_of_month_exception[$month]['is_exception'] = 0;
                                    $recent_date[] = $incentive['created_at'];
                                }
                            }
                            foreach ($incentive_rate as $incentive) {
                                if ($incentive->month == $month) {
                                    if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                                        $incentive_arr = array();
                                        $incentive_arr['domain'] = $incentive->mcourse;
                                        $incentive_arr['month'] = $incentive->month;
                                        $incentive_arr['min_closures'] = $incentive->min_closures;
                                        $incentive_arr['max_closures'] = $incentive->max_closures;
                                        $incentive_arr['rate'] = $incentive->rate;
                                        $list_of_month_exception[$month]['exceptions'][] = $incentive_arr;
                                    }
                                }
                            }
                        }
                    }
                }
                if (!empty($list_of_month_exception)) {
                    foreach ($list_of_month_exception as $exception) {
                        if (isset($exception['exceptions'])) {
                            
                        } else {
                            $recent_date = array();
                            foreach ($incentive_rate as $incentive) {
                                if ($incentive['month'] == $exception['month_number']) {
                                    $recent_date[] = $incentive['created_at'];
                                }
                            }
                            foreach ($incentive_rate as $incentive) {
                                if ($incentive['month'] == $exception['month_number']) {
                                    if (end($recent_date) == $incentive['created_at']) {
                                        $recent_date[] = $incentive['created_at'];

                                        $exception_arr = array();
                                        $exception_arr['executive_id'] = $executive_id;
                                        $exception_arr['domain'] = $domain;
                                        $exception_arr['month'] = $incentive->month;
                                        $exception_arr['min_closures'] = $incentive->min_closures;
                                        $exception_arr['max_closures'] = $incentive->max_closures;
                                        $exception_arr['rate'] = $incentive->rate;
                                        $list_of_month_exception[$incentive->month]['exceptions'][] = $exception_arr;
                                        $list_of_month_exception[$incentive->month]['is_exception'] = 0;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        echo json_encode($list_of_month_exception);
        die;
    }

    public function actionView_all_exception() {

        if (!Yii::$app->CustomComponents->check_permission('view_all_exception')) {
            return $this->redirect(['site/index']);
        }

        $users = Yii::$app->db->createCommand("SELECT du.first_name, du.last_name, du.id, aum.meta_value as role FROM assist_users as du JOIN assist_user_meta aum ON aum.uid = du.id WHERE aum.meta_key = 'role' AND aum.meta_value IN(2,6) AND du.department = 1 AND status = 1 ")->queryAll();

        if (Yii::$app->request->post()) {

            $months = Yii::$app->request->post('months');
            $domain = Yii::$app->request->post('domain');
            $user_id = Yii::$app->request->post('user_id');

            $exceptions = array();

            $exception_number = DvManageMonthlyIncentiveExceptionRate::find()->select(["exception_number"])->distinct()->where(['domain' => $domain, 'executive_id' => $user_id, 'month' => $months])->orderBy(["id" => SORT_DESC])->all();

            $month_exception = DvManageMonthlyIncentiveExceptionRate::find()->where(['domain' => $domain, 'executive_id' => $user_id, 'month' => $months])->all();

            if (!empty($exception_number)) {
                if (!empty($month_exception)) {
                    foreach ($exception_number as $number) {
                        foreach ($month_exception as $exception) {
                            if ($number->exception_number == $exception->exception_number) {
                                $exceptions[$number->exception_number][] = $exception;
                            }
                        }
                    }
                }
            }
            return $this->render('view_all_exception', ['users' => $users, 'exceptions' => $exceptions, 'months' => $months, 'domain' => $domain, 'user_id' => $user_id]);
        } else {
            return $this->render('view_all_exception', ['users' => $users]);
        }
    }

    public function actionView_current_executive_exception() {

        if (!Yii::$app->CustomComponents->check_permission('view_current_executive_exception')) {
            return $this->redirect(['site/index']);
        }

        if (Yii::$app->request->post()) {

            $months = Yii::$app->request->post('months');
            $domain = Yii::$app->request->post('domain');
            $year = Yii::$app->request->post('year');
            $user_id = Yii::$app->getUser()->identity->id;

            $exceptions = array();

            $exception_number = DvManageMonthlyIncentiveExceptionRate::find()->select(["exception_number"])->distinct()->where(['domain' => $domain, 'executive_id' => $user_id, 'month' => $months, "years" => $year])->orderBy(["id" => SORT_DESC])->all();

            $month_exception = DvManageMonthlyIncentiveExceptionRate::find()->where(['domain' => $domain, 'executive_id' => $user_id, 'month' => $months, "years" => $year])->all();

            if (!empty($exception_number)) {
                if (!empty($month_exception)) {
                    foreach ($exception_number as $number) {
                        foreach ($month_exception as $exception) {
                            if ($number->exception_number == $exception->exception_number) {
                                $exceptions[$number->exception_number][] = $exception;
                            }
                        }
                    }
                }
            }
            return $this->render('view_current_executive_exception', ['exceptions' => $exceptions, 'months' => $months, 'domain' => $domain, 'user_id' => $user_id, "year" => $year]);
        } else {
            return $this->render('view_current_executive_exception', []);
        }
    }

    public function actionGet_month_wise_closures() {
        $current_incentive = array();
        $return_arr = array();
        $month_exception = array();
        $return_arr['is_exception'] = 0;
        $return_arr['current_incentive'] = array();
        if (!empty(Yii::$app->request->post())) {

            $month = Yii::$app->request->post('month');
            $domain = Yii::$app->request->post('domain');
            $executive_id = Yii::$app->request->post('executive_id');

            if (!empty($month) && !empty($domain) && !empty($executive_id)) {
                $month_exception = DvManageMonthlyIncentiveExceptionRate::find()->where(['month' => $month, 'domain' => $domain, 'executive_id' => $executive_id])->all();
                if (!empty($month_exception)) {
                    $return_arr['is_exception'] = 1;
                }
            } else if (!empty($month) && !empty($domain)) {
                //$month_exception = DvManageMonthlyIncentiveExceptionRate::find()->where(['month' => $month, 'domain' => $domain])->all();
            }

            if (!empty($month_exception)) {
                $recent_date = array();
                foreach ($month_exception as $incentive) {
                    $recent_date[] = $incentive['created_at'];
                }
                foreach ($month_exception as $incentive) {
                    if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                        $arr = array();
                        $arr['min_closures'] = $incentive['min_closures'];
                        $arr['max_closures'] = $incentive['max_closures'];
                        $arr['rate'] = $incentive['rate'];
                        $return_arr['current_incentive'][] = $arr;
                    }
                }
            } else {
                $month_incentives = DvManageMonthlyIncentiveRate::find()->where(['month' => $month, 'mcourse' => $domain])->all();

                if (!empty($month_incentives)) {
                    $recent_date = array();
                    foreach ($month_incentives as $incentive) {
                        $recent_date[] = $incentive['created_at'];
                    }
                    foreach ($month_incentives as $incentive) {
                        if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                            $arr = array();
                            $arr['min_closures'] = $incentive['min_closures'];
                            $arr['max_closures'] = $incentive['max_closures'];
                            $arr['rate'] = $incentive['rate'];
                            $return_arr['current_incentive'][] = $arr;
                        }
                    }
                }
            }
        }

        if (!empty($return_arr)) {
            echo json_encode($return_arr);
        } else {
            echo 0;
        }
        die;
    }

    public function actionManage_team_manager_rule() {
        if (!Yii::$app->CustomComponents->check_permission('manage_team_manager_rule')) {
            return $this->redirect(['site/index']);
        }

        $model = new DvManageMonthlyIncentiveExceptionRate();
        $managers = Yii::$app->db->createCommand("SELECT * FROM assist_users as du 
            WHERE du.id IN(SELECT uid FROM assist_user_meta WHERE meta_key = 'role' AND meta_value=6) AND status = 1 ")->queryAll();

        $inserted_date = date("Y-m-d H:i:s");

        if ($model->load(Yii::$app->request->post())) {
            if (isset($_POST['DvManageMonthlyIncentiveExceptionRate']['min_closures'])) {
                $total_closures_range = sizeof($_POST['DvManageMonthlyIncentiveExceptionRate']['min_closures']);
                for ($j = 0; $j < $total_closures_range; $j++) {
                    $DvManageMonthlyIncentiveExceptionRate = new DvManageMonthlyIncentiveExceptionRate();
                    $DvManageMonthlyIncentiveExceptionRate->executive_id = $_POST['DvManageMonthlyIncentiveExceptionRate']['executive_id'];
                    $DvManageMonthlyIncentiveExceptionRate->month = $_POST['DvManageMonthlyIncentiveExceptionRate']['month'];
                    $DvManageMonthlyIncentiveExceptionRate->domain = $_POST['DvManageMonthlyIncentiveExceptionRate']['domain'];
                    $DvManageMonthlyIncentiveExceptionRate->min_closures = $_POST['DvManageMonthlyIncentiveExceptionRate']['min_closures'][$j];
                    $DvManageMonthlyIncentiveExceptionRate->max_closures = $_POST['DvManageMonthlyIncentiveExceptionRate']['max_closures'][$j];
                    $DvManageMonthlyIncentiveExceptionRate->rate = $_POST['DvManageMonthlyIncentiveExceptionRate']['rate'][$j];
                    $DvManageMonthlyIncentiveExceptionRate->created_at = $inserted_date;
                    $DvManageMonthlyIncentiveExceptionRate->updated_at = $inserted_date;
                    if ($DvManageMonthlyIncentiveExceptionRate->save()) {
                        
                    }
                }
            }
            Yii::$app->session->setFlash('success', 'Incentive Exception Rule Created Successfully.');
            return $this->redirect(['dv-finance/manage_team_member_exception']);
        } else {
            return $this->render('manage_team_manager_rule', [ 'model' => $model, 'managers' => $managers]);
        }
    }

    public function actionCheck_for_old_incentive_of_month() {
        if (Yii::$app->request->post()) {
            $months = Yii::$app->request->post('month');
            $domain = Yii::$app->request->post('domain');
            $incentives = DvManageMonthlyIncentiveRate::find()->where(["mcourse" => $domain])->andWhere(["in", "month", $months])->all();
            if (!empty($incentives)) {
                echo 1;
                die;
            }
        }
        echo 0;
        die;
    }

    /**
     * funciton for Creates a new Sales.
     * By Hetal J.
     * On 31st Jan,2019
     */
    public function actionCreate_sales() {
        //redirect a user if not super admin
        if (!Yii::$app->CustomComponents->check_permission('sales')) {
            return $this->redirect(['index']);
        }

        $model = new DvSales();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'New Sales Created Successfully');
            return $this->redirect(['dv-finance/create_sales']);
        } else {
            return $this->render('create_sales', [ 'model' => $model,]);
        }
    }

    /**
     * funciton for Edit a Sales.
     * By Hetal J.
     * On 31st Jan,2019
     */
    public function actionEdit_sales($id) {
        //redirect a user if not super admin
        if (!Yii::$app->CustomComponents->check_permission('edit_sales')) {
            return $this->redirect(['create_sales']);
        }

        $model = DvSales::findOne($id);
        if (empty($model)) {
            return $this->redirect(['create_sales']);
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->save();
            Yii::$app->session->setFlash('success', 'Sales Updated Successfully');
            return $this->redirect(['create_sales']);
        } else {
            return $this->render('edit_sales', ['model' => $model]);
        }
    }

}