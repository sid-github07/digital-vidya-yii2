<?php

namespace app\controllers;

use Yii;
use app\models\DvUsers;
use app\models\DvUserMeta;
use yii\web\Controller;
use app\models\DvStates;
use app\models\DvCities;
use app\models\DvCountry;
use yii\data\Pagination;
use app\models\DvCourse;
use app\models\DvModules;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use app\models\DvParticipantModules;
use app\models\DvRegistration;
use app\models\DvAssistBatches;
use app\models\DvModuleModel;

// use app\models\DvParticipantPayments;
// use app\models\DvParticipantPaymentMeta;
// use app\models\DvCurrency;
// use app\models\DvPaymentMode;
use app\models\DvIncentive;
use app\models\DvUsersActivityLog;
use app\models\DvGst;
use app\models\DvQuickBook;
use app\models\DvParticipantBatchMeta;

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
 * FeedbackController implements the CRUD actions for MwFeedback model.
 */
class DvCdoRegistrationController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
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
     * Lists all MwFeedback models.
     * @return mixed
     */
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

    public function actionIndex() {
        /* redirect a user if not super admin */
        if (!Yii::$app->CustomComponents->check_permission('registration')) {
            return $this->redirect(['site/index']);
        }

        $first_date_of_current_month = date("Y-m-01"); /* Default Start Date for filter. Current month First date */
        $last_date_of_current_month = date("Y-m-t"); /* Default End Date for filter. Current month Last date */
        $timestamp_of_first_date_of_month = strtotime($first_date_of_current_month);
        $timestamp_of_last_date_of_month = strtotime($last_date_of_current_month);

        $dataService = $this->quickbook_instance();

        $user = Yii::$app->user->identity;
        $usermeta_result = DvUserMeta::find()->where(['uid' => $user->id, 'meta_key' => 'role'])->one();
        $user_role = $usermeta_result->meta_value;
        $all_invoice_of_customer = array();
        $allInvoices = array();
        $all_query = array();
        if ($user_role == 1) {
            $total_invoices = $dataService->Query("SELECT count(*) FROM Invoice");
            $allInvoices = $dataService->Query("SELECT * FROM Invoice MAXRESULTS $total_invoices");
            $qb_cust_id_arr = array();
            if (!empty($allInvoices)) {
                foreach ($allInvoices as $key => $val) {
                    $qb_cust_id_arr[] = $val->CustomerRef;
                }
            }
            $query = DvRegistration::find()->where(['IN', 'qb_customer_id', $qb_cust_id_arr])->orderBy(['id' => SORT_DESC]);
            $all_query = DvRegistration::find()->where(['IN', 'qb_customer_id', $qb_cust_id_arr])->orderBy(['id' => SORT_DESC])->all();
        } else {
            $qb_id_from_us = DvRegistration::find()->select('qb_customer_id')->Where(['sales_user_id' => $user->id])->all();
            $qb_cust_id_str = "";

            if (!empty($qb_id_from_us)) {
                foreach ($qb_id_from_us as $qb_ids) {
                    if (!empty($qb_ids->qb_customer_id)) {
                        $qb_cust_id_str .= "'" . $qb_ids->qb_customer_id . "',";
                        $qb_cust_id_arr[] = $qb_ids->qb_customer_id;
                    }
                }
                $qb_cust_id_str = rtrim($qb_cust_id_str, ",");
               // $total_invoices = $dataService->Query("SELECT count(*) FROM Invoice");
                $allInvoices = $dataService->Query("SELECT * FROM Invoice where CustomerRef IN($qb_cust_id_str) MAXRESULTS 1000");
            }
            $qb_cust_id_arr = array();

            if (!empty($allInvoices)) {
                $all_invoice_of_customer = $allInvoices;
                foreach ($allInvoices as $key => $val) {
                    $qb_cust_id_arr[] = $val->CustomerRef;
                }
            }

            $query = DvRegistration::find()->where(['sales_user_id' => $user->id])->andWhere(['!=', 'qb_customer_id', ''])->andWhere(['IN', 'qb_customer_id', $qb_cust_id_arr])->orderBy(['id' => SORT_DESC]);
        }
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 10]);
        $models = $query->offset($pagination->offset)->limit($pagination->limit)->all();
        $offset = $pagination->offset + 1;



        /*$total_invoices = $dataService->Query("SELECT count(*) FROM Invoice");
        $allInvoices = $dataService->Query("SELECT * FROM Invoice MAXRESULTS $total_invoices");
        $all_invoice_of_customer = $allInvoices;*/

        $total_payments = $dataService->Query("Select count(*) from Payment ORDER BY Id DESC");
        $all_payments = $dataService->Query("Select * from Payment ORDER BY Id DESC MAXRESULTS $total_payments");

        $total_credit_memos = $dataService->Query("Select count(*) from CreditMemo");
        $all_credit_memos = $dataService->Query("Select * from CreditMemo ORDER BY Id DESC MAXRESULTS $total_credit_memos");

        return $this->render('index', ['dataService'=>$dataService, 'all_query'=> $all_query, 'participant_users' => $models, 'pages' => $pagination, 'count' => $offset, 'all_payments' => $all_payments, 'all_credit_memos' => $all_credit_memos]);
    }

    /**
     * Creates a new DvParticipant.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {

        /* redirect a user if not super admin */
        if (!Yii::$app->CustomComponents->check_permission('new_registration')) {
            return $this->redirect(['index']);
        }


        /*if(!$participant_meta->save()){
            echo "Something went wrong when insert data to participant meta table.";
            die;
        }else{
            echo "inserted";
            die;
        }*/


        $dataService = $this->quickbook_instance();
        $digits = 6;

        /*  Get incentive type */
        
        $incentive = DvIncentive::find()->where(['status' => 1])->all();
        $incentiveArr = array();

        

        foreach ($incentive as $incentive_value) {
            $incentive_details = "";
            if ($incentive_value['description'] != "") {
                $incentive_details = "(" . $incentive_value['description'] . ")";
            } else {
                $incentive_details = $incentive_value['description'];
            }
            $incentiveArr[$incentive_value['id']] = $incentive_value['inc_per'] . "% of paid amount " . $incentive_details;
        }
       
        $model = new DvRegistration();

        // $pp_model = new DvParticipantPayments();
        $dd_payment_currency_name = "";
        $dd_course_name = "";
        $qb_course_name = "";
        $customer_id = "";
        $product_id = "";
        //echo "Before API calling";  
        $total_companycurrency = $dataService->Query("select count(*) from Companycurrency where active=true");
         
        //echo "After API calling"; die;


        $companycurrency = $dataService->Query("select * from Companycurrency where active=true MAXRESULTS $total_companycurrency ");

        $qbCurrencyArr = array();
        if (!empty($companycurrency)) {
            foreach ($companycurrency as $qb_currency_value) {
                $qbCurrencyArr[$qb_currency_value->Id] = $qb_currency_value->Code;
            }
        }

        $qbCurrencyArr[] = "INR";


        end($qbCurrencyArr);

        $india_currency_key = key($qbCurrencyArr);

        $total_qb_course = $dataService->Query("SELECT count(*) from Item"); /* Products */
        $qb_course = $dataService->Query("SELECT * from Item MAXRESULTS $total_qb_course"); /* Products */

        $total_paymentmethod_result = $dataService->Query("SELECT count(*) from PaymentMethod");
        $allPaymentmethod_result = $dataService->Query("SELECT * from PaymentMethod MAXRESULTS $total_paymentmethod_result");
        $allPaymentmethod = array();

        if (!empty($allPaymentmethod_result)) {
            foreach ($allPaymentmethod_result as $pay_value) {
                $allPaymentmethod[$pay_value->Id] = $pay_value->Name;
            }
        }

        /* echo "<pre>";
          print_r($allPaymentmethod);
          die; */

        /*  Get post data then insert data into 3 tables. */
        if (Yii::$app->request->post()) {

            $data = Yii::$app->request->post();

            $token_id = $data['token_id'];
            $sales_user_id = $data['DvRegistration']['sales_user_id'];
            $first_name = $data['DvRegistration']['first_name'];
            $first_name = ucfirst(strtolower($first_name));
            $last_name = $data['DvRegistration']['last_name'];
            $last_name = ucfirst(strtolower($last_name));
            $email = $data['DvRegistration']['email'];
            $mobile = $data['DvRegistration']['mobile'];
            $scholarship_offered = $data['DvRegistration']['scholarship_offered'];
            // $obj_of_running_fields = $data['DvRegistration']['obj_of_running_fields'];
            $promises_notes = $data['DvRegistration']['promises_notes'];
            $country = $data['DvRegistration']['country'];
            $state = $data['DvRegistration']['state'];
            $city = $data['DvRegistration']['city'];
            $remarks = $data['DvRegistration']['remarks'];
            $course = $data['DvRegistration']['course'];
            $course_res = DvCourse::find()->where(['id' => $course])->all();
            $Ucourse = ArrayHelper::map($course_res, 'id', 'name');
            $u_course = array_values($Ucourse);




            $course_format = $data['DvRegistration']['course_format'];


            $course_batch = $data['DvRegistration']['course_batch'];
            $address = $data['DvRegistration']['address'];

            $check_registration_availability = Yii::$app->db->createCommand("SELECT * FROM assist_participant WHERE email='$email'")->queryAll();

            $qb_id_exitst = "new";

            if (!empty($check_registration_availability)) {
                if ($check_registration_availability[0]['course'] == $course) {

                    Yii::$app->session->setFlash('error', "$email participant is already registerd with $u_course[0] course.");
                    return $this->redirect(['create']);
                    die;
                } else {
                    $qb_id_exitst = $check_registration_availability[0]['qb_customer_id'];
                    $exitst_participant_id = $check_registration_availability[0]['id'];
                }
            }

            $opt_for_3_months = 0;
            $available_batch_opt = 0;
            if ($course == 1 || $course == 2 || $course == 13 || $course == 14) {
                if (isset($data['DvRegistration']['opt_for_3_months'])) {
                    $opt_for_3_months = ($data['DvRegistration']['opt_for_3_months'] == "on") ? "1" : "0";
                }
                if (isset($data['DvRegistration']['opt_for_3_months']) && $data['DvRegistration']['opt_for_3_months'] == "on") {
                    $available_batch_opt = $data['DvRegistration']['available_batch_opt'];
                }
            }

            $free_courses = "";
            if (isset($data['DvRegistration']['free_courses'])) {
                $free_courses = json_encode($data['DvRegistration']['free_courses']);
            }

            if (isset($data['DvRegistration']['vskills'])) {
                $vskills = $data['DvRegistration']['vskills'];
            } else {
                $vskills = 0;
            }

            $total_confirmed_amount = $data['DvRegistration']['total_confirmed_amount'];
            $is_full_payment = $data['DvRegistration']['is_full_payment'];

            $modules_allowed = $data['DvRegistration']['modules_allowed'];
            $is_pdc = $data['DvRegistration']['is_pdc'];
            $incentive_percentage = 0;

            $installment = count($data['installment']);

            $payment_currency = $data['DvParticipantPayments']['payment_currency'];
            $amount_recieved = $data['DvParticipantPayments']['amount_recieved'];
            $payment_mode = $data['DvParticipantPayments']['payment_mode'];
            $payment_reference_number = $data['DvParticipantPayments']['payment_reference_number'];

            /*  Add into participant registration table */

            $model->token_id = $token_id;
            $model->sales_user_id = $sales_user_id;
            $model->first_name = $first_name;
            $model->last_name = $last_name;
            $model->email = $email;
            $model->mobile = $mobile;
            $model->scholarship_offered = $scholarship_offered;
            //   $model->obj_of_running_fields = $obj_of_running_fields;
            $model->promises_notes = $promises_notes;
            $model->country = $country;
            $model->state = $state;
            $model->city = $city;
            $model->address = $address;
            $model->remarks = $remarks;
            $model->course = $course;
            $model->vskills = $vskills;

            $model->course_format = $course_format;

            $model->course_batch = $course_batch;
            $course_batch_date = strstr($course_batch, '#', true);
            if ($course_batch_date == '') {
                $course_batch_date = $course_batch;
            }
            $time = strtotime($course_batch_date);
            $course_batch_date1 = date('Y-m-d', $time);


            $model->course_batch_date = $course_batch_date1;

            $model->opt_for_3_months = $opt_for_3_months;
            $model->available_batch_opt = $available_batch_opt;
            $model->free_courses = $free_courses;
            $model->total_confirmed_amount = NULL;
            $model->modules_allowed = $modules_allowed;
            $model->modules_completed = 0;
            $model->is_full_payment = $is_full_payment;
            if ($is_full_payment == 0) {
                $model->is_pdc = $is_pdc;
            }
            $model->incentive_percentage = $incentive_percentage;
            $model->participant_status = 1;
            $model->participant_extra_details = 0;

            $model->participant_payment_status = 1;
            $model->participant_details_status = 1;


            $dd_payment_currency_name = "INR";

            $get_course_name = DvCourse::find()->select(['name'])->where(['status' => 1, 'id' => $course])->one();
            $dd_course_name = $get_course_name['name'];
            if (!empty($companycurrency)) {
                foreach ($companycurrency as $qb_cur) {
                    //if ($qb_cur->Code == $dd_payment_currency_name) {
                    if ($qb_cur->Id == $payment_currency) {
                        $dd_payment_currency_name = $qb_cur->Code;
                    }
                }
            }
            if (!empty($qb_course)) {
                foreach ($qb_course as $qb_v_course) {
                    if ($qb_v_course->Name == $dd_course_name) {
                        $qb_course_name = $qb_v_course->Name;
                        $product_id = $qb_v_course->Id;
                    }
                }
            }

            if (empty($qb_course_name)) {
                $create_item = Item::create([
                            "Name" => $dd_course_name,
                            "IncomeAccountRef" => [
                                "value" => "48",
                                "name" => "Sales of Product Income"
                            ],
                            "Type" => "Service"
                ]);
                $resultObj = $dataService->Add($create_item);
                $error = $dataService->getLastError();
                if ($error) {
                    echo "DisplayName cname: " . $dd_course_name;
                    echo "The Status code is: " . $error->getHttpStatusCode() . "<br>";
                    echo "The Helper message is: " . $error->getOAuthHelperError() . "<br>";
                    echo "The Response message is: " . $error->getResponseBody() . "<br>";
                    die;
                    Yii::$app->session->setFlash('error', 'Something went wrong while adding new Item. Please try again later.');
                    return $this->redirect(['create']);
                    die;
                } else {
                    $product_id = "{$resultObj->Id}";
                }
            }


            $DvRegistration_model = DvRegistration::find()->select('id')->orderBy(['id' => SORT_DESC])->one();
            if (!empty($DvRegistration_model)) {
                $last_participant = ($DvRegistration_model->id + 1);
            } else {
                $last_participant = "_";
            }

            $last_participant .= "_" . rand(pow(10, 4 - 1), pow(10, 4) - 1);

            $address_in_qb = "";
            $country_name = DvCountry::find()->select(['name'])->where(['id' => $country])->one();
            $state_name = DvStates::find()->select(['name'])->where(['id' => $state])->one();
            $city_name = DvCities::find()->select(['name'])->where(['id' => $city])->one();
            $address_in_qb = $city_name['name'] . ", " . $state_name['name'] . ", " . $country_name['name'];

            if ($qb_id_exitst == 'new') {
                $customer_first_name = str_replace(' ', '_', $first_name);
                $customer_last_name = str_replace(' ', '_', $last_name);
                $create_customer = Customer::create([
                            "BillAddr" => [
                                "Line1" => $address . ", " . $address_in_qb,
                            ],
                            "GivenName" => $first_name,
                            "FamilyName" => $last_name,
                            "FullyQualifiedName" => $first_name . " " . $last_name,
                            "DisplayName" => $customer_first_name . "_" . $customer_last_name . "_" . $last_participant,
                            "PrimaryPhone" => [
                                "FreeFormNumber" => $mobile
                            ],
                            "CurrencyRef" => $dd_payment_currency_name,
                            "PrimaryEmailAddr" => [
                                "Address" => $email
                            ]
                ]);

                $resultObj = $dataService->Add($create_customer);
                $error = $dataService->getLastError();
            } else {
                $error = array();
            }

            if ($error) {
                echo "DisplayName : " . $first_name . "_" . $last_name . "_" . $last_participant;
                echo "The Status code is: " . $error->getHttpStatusCode() . "<br>";
                echo "The Helper message is: " . $error->getOAuthHelperError() . "<br>";
                echo "The Response message is: " . $error->getResponseBody() . "<br>";
                die;

                Yii::$app->session->setFlash('error', 'Something went wrong while adding new Customer. Please try again later.');
                return $this->redirect(['create']);
                die;
            } else {

                if ($qb_id_exitst == 'new') {
                    $customer_id = "{$resultObj->Id}";
                } else {
                    $customer_id = $qb_id_exitst;
                }

                $model->qb_customer_id = $customer_id;

                /* Create wp user, If wp_id not get */
                $wp_user_id = $data['wp_id'];
                // $wp_user_name = $data['wp_user'];

                if ($wp_user_id == '') {
                    /* require ($_SERVER['DOCUMENT_ROOT']."/wp-load.php");
                      $wp_user_id = wp_create_user( $email , $email , $email );
                      $user =  get_user_by( 'id', $wp_user_id );
                      $wp_user_name = $user->user_login; */

                    // set post fields
                    $post = [
                        'wp_email' => $email,
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'course' => $course
                    ];

                    $environment = Yii::$app->params['environment']; // check server enviroment
                    if ($environment == 'Production') {
                        // live
                        $ch = curl_init('https://www.digitalvidya.com/training/wp-json/create/v1/email/');
                    } else {
                        $ch = curl_init('http://dev.digitalvidya.com/training/wp-json/create/v1/email/');
                    }

                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

                    // execute!
                    $response = curl_exec($ch);

                    // close the connection, release resources used
                    curl_close($ch);
                    $wp_user_id = $response;
                    if (!empty($response)) {

                        if (($course == '1') || ($course == '2')) {
                            $subject = "Get Started with your Digital Marketing Course!";
                            $body = "<p>Hi " . $first_name . " " . $last_name . ",</p>
              <p>Congratulations on your enrollment to Digital Vidya's Training Program.</p>
              <p>Let’s get started by taking the <a href=\"https://www.digitalvidya.com/training/\" target=\"_blank\">1st Step to understand how your training program</a> works. You <br>must take out 25 minutes today to watch the onboarding video and fill in your profile details to get <br>access to the training material and live sessions.</p>
              <p><strong>LMS Credentials:</strong><br>Username: " . $email . " <br>Password: " . $email . "</p>
              <p>After watching the video, you will be redirected to the Learning Management System (LMS) where <br>you will get access to the training material. Your assigned Program Coordinator will get in touch <br>with you shortly with answers to all your queries and guide you further.</p>
              <p>All the Best!<br>Digital Vidya Team</p>
              <p>Ph: 8081033033 | Email: delivery@digitalvidya.com<br>
              <em>Digital Vidya, 1001, 10th Floor, Pearls Omaxe Building, Netaji Subhash Place, New Delhi, 110034, India </em> ";

                            $is_sent = Yii::$app->mailer->compose()
                                    ->setFrom('delivery@digitalvidya.com')
                                    ->setTo($email)
                                    ->setSubject($subject)
                                    ->setHtmlBody($body)
                                    ->send();
                        }
                    }

                    //$wp_user_name = $email;
                }

                $model->wp_user_id = $wp_user_id;
                $model->wp_user_name = $email;

                // if User select DAP
                if (($course == '14')) {

                    // add course to the user account in wordpress
                    $post = [
                        'wp_user_id' => $wp_user_id,
                        'assist_course' => 'DAP'
                    ];

                    $environment = Yii::$app->params['environment']; // check server enviroment
                    if ($environment == 'Production') {
                        // live
                        $ch = curl_init('https://www.digitalvidya.com/training/wp-json/course/v1/ld/');
                    } else {
                        $ch = curl_init('http://dev.digitalvidya.com/training/wp-json/course/v1/ld/');
                    }

                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

                    // execute!
                    $response = curl_exec($ch);

                    // close the connection, release resources used
                    curl_close($ch);
                    //$wp_course_id = $response;

                    if (!empty($response)) {
                        $subject = "Get Started with your Data Science Course!";
                        $body = "<p>Hi " . $first_name . " " . $last_name . ",</p>
                  <p>Congratulations on your enrollment to Digital Vidya's Training Program.</p>
                  <p>Your LMS account has been activated which includes the following course:</p>
                  <ul>
                      <li>Basic of Python Programming - A Jupyter Notebook Based Tutorial.</li>
                      <li>Statistics Foundation - Self Paced Course.</li>
                      <li>Data Science Using Python.</li>
                      <li>Data Science Using R - Self Paced Course.</li>
                      <li>Data science Using Tableau</li>
                  </ul>

                  <p>To access the LMS account, please use the below credentials:</p>

                  <p><strong>URL -</strong> <a href=\"https://www.digitalvidya.com/training/\" target=\"_blank\">https://www.digitalvidya.com/training/</a>  <br>
                  <strong>Username:</strong> " . $email . "<br>
                  <strong>Password:</strong>  " . $email . "</p>
                  
                  <p>Your assigned Program Coordinator will get in touch with you shortly with answers to all your queries and guide you further </p>
                  <p>All the Best!<br>Digital Vidya Team</p>
                  <p>Ph: 8081033033 | Email: delivery@digitalvidya.com<br>
                  <em>Digital Vidya, 1001, 10th Floor, Pearls Omaxe Building, Netaji Subhash Place, New Delhi, 110034, India </em> ";

                        $is_sent = Yii::$app->mailer->compose()
                                ->setFrom('delivery@digitalvidya.com')
                                ->setTo($email)
                                ->setSubject($subject)
                                ->setHtmlBody($body)
                                ->send();
                    }
                }

                // if User select DAE
                if (($course == '16')) {

                    // add course to the user account in wordpress
                    $post = [
                        'wp_user_id' => $wp_user_id,
                        'assist_course' => 'DAE'
                    ];

                    $environment = Yii::$app->params['environment']; // check server enviroment
                    if ($environment == 'Production') {
                        // live
                        $ch = curl_init('https://www.digitalvidya.com/training/wp-json/course/v1/ld/');
                    } else {
                        $ch = curl_init('http://dev.digitalvidya.com/training/wp-json/course/v1/ld/');
                    }

                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

                    // execute!
                    $response = curl_exec($ch);

                    // close the connection, release resources used
                    curl_close($ch);
                    //$wp_course_id = $response;

                    if (!empty($response)) {

                        $subject = "Get Started with your Data Analytics Course!";
                        $body = "<p>Hi " . $first_name . " " . $last_name . ",</p>
              <p>Congratulations on your enrollment to Digital Vidya's Training Program.</p>
              <p>Your LMS account has been activated which includes the following course:</p>
              <ul>
                  <li>Analytic Techniques using Excel and Power BI.</li>
                  <li>Statistics Foundation - Self Paced Course.</li>
                  <li>VBA Macros- Self-paced Course.</li>
                  <li>SQL Foundation- Self paced Course</li>
                  <li>Data Analytics Using Tableau</li>
              </ul>

              <p>To access the LMS account, please use the below credentials:</p>

              <p><strong>URL -</strong> <a href=\"https://www.digitalvidya.com/training/\" target=\"_blank\">https://www.digitalvidya.com/training/</a>  <br>
              <strong>Username:</strong> " . $email . "
              <strong>Password:</strong>  " . $email . "</p>
              
              <p>Your assigned Program Coordinator will get in touch with you shortly with answers to all your queries and guide you further </p>
              <p>All the Best!<br>Digital Vidya Team</p>
              <p>Ph: 8081033033 | Email: delivery@digitalvidya.com<br>
              <em>Digital Vidya, 1001, 10th Floor, Pearls Omaxe Building, Netaji Subhash Place, New Delhi, 110034, India </em> ";

                        $is_sent = Yii::$app->mailer->compose()
                                ->setFrom('delivery@digitalvidya.com')
                                ->setTo($email)
                                ->setSubject($subject)
                                ->setHtmlBody($body)
                                ->send();
                    }
                }

                if ($model->save()) {



                    $id = Yii::$app->db->getLastInsertID();

                    $participant_meta = new DvParticipantBatchMeta();
                    $participant_meta->pid = $id;
                    $participant_meta->batch_id = $course_batch;
                    if(!$participant_meta->save()){
                        echo "Something went wrong when insert data to participant meta table.";
                        die;
                    }

                    /*
                    ** Start
                    ** @CDO - 21May2019 
                    ** Assign Module to user in LMS
                    */
                    $batch_data = DvAssistBatches::find()->where(["id"=>$course_batch])->one();
                    $lms_course = '';
                    if($batch_data){
                        $module_id = $batch_data['module'];
                        if($module_id){
                            $module_result = DvModuleModel::find()->where(['id'=>$module_id])->one();
                            if($module_result){
                                $lms_course = $module_result['lms_course'];
                            }

                        }
                    }

                    if($lms_course == ''){
                        echo "Lms course not found";
                        die;
                    }


                    $post = [
                        'wp_user_id' => $wp_user_id,
                        'lms_course' => $lms_course,
                    ];

                    $environment = Yii::$app->params['environment']; // check server enviroment
                    if ($environment == 'Production') {
                        // live
                        $ch = curl_init('https://www.digitalvidya.com/training/wp-json/course/v1/ld/');
                    } else {
                        $ch = curl_init('http://dev.digitalvidya.com/training/wp-json/course/v1/ld/');
                    }

                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

                    // execute!
                    $response = curl_exec($ch);

                    // close the connection, release resources used
                    curl_close($ch);

                    /*
                    ** End
                    ** @CDO - 21May2019 
                    ** Assign Module to user in LMS
                    */


                    if ($id && $customer_id) {
                        $gst_id = NULL;
                        if ($country == DvRegistration::INDIA_ID_FROM_DV_COUNTRY && $state == DvRegistration::DELHI_ID_DV_FROM_STATES) {
                            $dv_gst = DvGst::find()
                                    ->select(['max(id) as id'])
                                    ->where(['state' => 0])
                                    ->groupBy(['YEAR(gst_date),MONTH(gst_date), state'])
                                    ->one();
                            if (!empty($dv_gst)) {
                                $gst_id = $dv_gst->id;
                            }
                        } else if ($country == DvRegistration::INDIA_ID_FROM_DV_COUNTRY) {
                            $dv_gst = DvGst::find()
                                    ->select(['max(id) as id'])
                                    ->where(['state' => 1])
                                    ->groupBy(['YEAR(gst_date),MONTH(gst_date), state'])
                                    ->one();
                            if (!empty($dv_gst)) {
                                $gst_id = $dv_gst->id;
                            }
                        } else if ($payment_currency == DvRegistration::INDIA_CURRENCY_FROM_DV_CURRENCY) {
                            $dv_gst = DvGst::find()
                                    ->select(['max(id) as id'])
                                    ->where(['state' => 1])
                                    ->groupBy(['YEAR(gst_date),MONTH(gst_date), state'])
                                    ->one();
                            if (!empty($dv_gst)) {
                                $gst_id = $dv_gst->id;
                            }
                        }

                        /* $pp_model->participant_id = $id;
                          $pp_model->amount_recieved = $amount_recieved;
                          $pp_model->amount_recieved_date = date("Y-m-d");
                          $pp_model->payment_mode = $payment_mode;
                          $pp_model->payment_reference_number = $payment_reference_number;
                          $pp_model->payment_number = 1;
                          $pp_model->payment_currency = $payment_currency;
                          $pp_model->gst = $gst_id; */

                        if ($state == DvRegistration::DELHI_ID_DV_FROM_STATES) {
                            $qb_gst_rate = DvRegistration::GST_RATE_IN_DELHI;
                            $qb_gst_id = DvRegistration::QB_ID_OF_GST_RATE_IN_DELHI;

                            $invoice_row_amt = $amount_recieved / (1 + ($qb_gst_rate / 100));
                            $invoice_row_amt = number_format((float) $invoice_row_amt, 2, '.', '');
                        } else if ($payment_currency == $india_currency_key) {
                            $qb_gst_rate = DvRegistration::GST_RATE_IN_OTHER_STATE;
                            $qb_gst_id = DvRegistration::QB_ID_OF_GST_RATE_IN_OTHER_STATE;

                            $invoice_row_amt = $amount_recieved / (1 + ($qb_gst_rate / 100));
                            $invoice_row_amt = number_format((float) $invoice_row_amt, 2, '.', '');
                        } else {
                            $qb_gst_rate = DvRegistration::NONE_GST_RATE;
                            $qb_gst_id = DvRegistration::QB_ID_OF_NONE_GST_RATE;

                            $invoice_row_amt = number_format((float) $amount_recieved, 2, '.', '');
                        }

                        // if ($pp_model->save()) {
                        $state_vls = 7;
                        $random_doc_number = rand(pow(10, $digits - 1), pow(10, $digits) - 1);

// the key is value of quick book and the value is the value from DB of State
                        $state_list = array(
                            '35' => '1',
                            '37' => '2',
                            '12' => '3',
                            '18' => '4',
                            '10' => '5',
                            '04' => '6',
                            '22' => '7',
                            '26' => '8',
                            '25' => '9',
                            '07' => '10',
                            '30' => '11',
                            '24' => '12',
                            '06' => '13',
                            '02' => '14',
                            '01' => '15',
                            '20' => '16',
                            '29' => '17',
                            '32' => '19',
                            '31' => '20',
                            '23' => '21',
                            '27' => '22',
                            '14' => '23',
                            '17' => '24',
                            '15' => '25',
                            '13' => '26',
                            '21' => '29',
                            '34' => '31',
                            '03' => '32',
                            '08' => '33',
                            '11' => '34',
                            '33' => '35',
                            '36' => '36',
                            '16' => '37',
                            '09' => '38',
                            '05' => '39',
                            '19' => '41');

                        $state_id = array_search($state, $state_list);
                        if (empty($state_id)) {
                            $state_id = '97';
                            $qb_gst_id = DvRegistration::QB_ID_OF_NONE_GST_RATE;
                        }


                        $invoiceToCreate = Invoice::create([
                                    "DocNumber" => "IN-" . $random_doc_number,
                                    "TxnDate" => date("Y-m-d"), /* Generated Date */
                                    // "TransactionLocationType" => $state_vls,
                                    "Line" => [[
                                    "Description" => "Amount paid on registration time.",
                                    "Amount" => $invoice_row_amt, /* Amount */
                                    "DetailType" => "SalesItemLineDetail",
                                    "SalesItemLineDetail" => [
                                        "ItemRef" => [
                                            "value" => $product_id, /* Product Id */
                                            "name" => "Invoice 1"
                                        ],
                                        "TaxCodeRef" => [
                                            "value" => $qb_gst_id /* GST ID */
                                        ]
                                    ],
                                        ]],
                                    "CustomerRef" => [
                                        "value" => $customer_id
                                    ],
                                    "TransactionLocationType" => $state_id,
                                    "DueDate" => date("Y-m-d"),
                        ]);
                        $resultObj = $dataService->Add($invoiceToCreate);
                        $error = $dataService->getLastError();
                        if ($error) {
                            echo "customer_id : " . $customer_id . "_" . $qb_gst_id . "qb_gst_id";
                            echo "The Status code is: " . $error->getHttpStatusCode() . "<br>";
                            echo "The Helper message is: " . $error->getOAuthHelperError() . "<br>";
                            echo "The Response message is: " . $error->getResponseBody() . "<br>";
                            die;
                            Yii::$app->session->setFlash('error', 'Something went wrong while adding new Invoice. Please try again later.');
                            return $this->redirect(['create']);
                            die;
                        } else {
                            echo "Paid Invoice Created Id={$resultObj->Id}. Reconstructed response body:\n\n<br><Br>";
                            $invoice_id = "{$resultObj->Id}";
                            // $pp_model->qb_invoice_id = $invoice_id;
                            // $pp_model->qb_invoice_status = "Paid";
                            // $pp_model->save();
                            $InvoicePaymentToCreate = Payment::create([
                                        "CustomerRef" => [
                                            "value" => $customer_id/* Customer Id from Quickbook */
                                        ],
                                        "PaymentMethodRef" => [
                                            /*
                                             * Default Payment Methods are as below from PaymentMethod table
                                             * 1: Cash, 
                                             * 2: Check, 
                                             * 3: Visa, 
                                             * 4: MasterCard, 
                                             * 5: Discover, 
                                             * 6: American Express, 
                                             * 7: Diners Club */
                                            "value" => $payment_mode
                                        ],
                                        "PaymentRefNum" => "PY-" . $payment_reference_number,
                                        "TotalAmt" => $amount_recieved, /* Total amount of Invoice */
                                        "UnappliedAmt" => $amount_recieved
                            ]);
                            $resultObj = $dataService->Add($InvoicePaymentToCreate);
                            $error = $dataService->getLastError();
                            if ($error) {
                                Yii::$app->session->setFlash('error', 'Something went wrong while adding new Payment. Please try again later.');
                                return $this->redirect(['create']);
                                die;
                            }

                            if ($is_full_payment == 0) {
                                for ($i = 2; $i < $installment + 2; $i++) {
                                    /* Add into participant payment table */
                                    $inst_date = date("Y-m-d", strtotime($data['installment'][$i]['date']));

                                    $cheque_referenc_number = "";
                                    if (isset($data['installment'][$i]['ref_number'])) {

                                        $cheque_referenc_number = $data['installment'][$i]['ref_number'];
                                    }

                                    if ($state == DvRegistration::DELHI_ID_DV_FROM_STATES) {
                                        $qb_gst_rate = DvRegistration::GST_RATE_IN_DELHI;
                                        $qb_gst_id = DvRegistration::QB_ID_OF_GST_RATE_IN_DELHI;

                                        $invoice_row_amt = $data['installment'][$i]['amt'] / (1 + ($qb_gst_rate / 100));
                                        $invoice_row_amt = number_format((float) $invoice_row_amt, 2, '.', '');
                                    } else if ($payment_currency == $india_currency_key) {
                                        $qb_gst_rate = DvRegistration::GST_RATE_IN_OTHER_STATE;
                                        $qb_gst_id = DvRegistration::QB_ID_OF_GST_RATE_IN_OTHER_STATE;

                                        $invoice_row_amt = $data['installment'][$i]['amt'] / (1 + ($qb_gst_rate / 100));
                                        $invoice_row_amt = number_format((float) $invoice_row_amt, 2, '.', '');
                                    } else {
                                        $qb_gst_rate = DvRegistration::NONE_GST_RATE;
                                        $qb_gst_id = DvRegistration::QB_ID_OF_NONE_GST_RATE;

                                        $invoice_row_amt = number_format((float) $data['installment'][$i]['amt'], 2, '.', '');
                                    }

                                    $invoice_amt_without_tax = $data['installment'][$i]['amt'];

                                    $random_doc_number = rand(pow(10, $digits - 1), pow(10, $digits) - 1);

                                    $invoiceToCreate = Invoice::create([
                                                "DocNumber" => "IN-" . $random_doc_number,
                                                // "TransactionLocationType" => $state_vls,
                                                /* Generated Date is set to Due Date as per descussion on 21st Jan,2019 */
                                                "TxnDate" => $inst_date,
                                                "Line" => [[
                                                "Description" => "Installment " . ($i - 1),
                                                "Amount" => $invoice_row_amt,
                                                "DetailType" => "SalesItemLineDetail",
                                                "SalesItemLineDetail" => [
                                                    "ItemRef" => [
                                                        "value" => $product_id, /* Product Id */
                                                        "name" => "Invoice 1"
                                                    ],
                                                    "TaxCodeRef" => [
                                                        "value" => $qb_gst_id
                                                    ]
                                                ],
                                                    ]],
                                                "CustomerRef" => [
                                                    "value" => $customer_id
                                                ],
                                                "TransactionLocationType" => $state_id,
                                                "DueDate" => $inst_date,
                                    ]);
                                    $resultObj = $dataService->Add($invoiceToCreate);
                                    $error = $dataService->getLastError();
                                    if ($error) {
                                        Yii::$app->session->setFlash('error', 'Something went wrong while adding new Invoice. Please try again later.');
                                        return $this->redirect(['create']);
                                        die;
                                    } else {

                                        $check_payment_mode = DvRegistration::PAYMENT_MODE_CHEQUE;
                                        if ($payment_mode == $check_payment_mode) {
                                            $random_doc_number = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
                                            $InvoicePaymentToCreate = Payment::create([
                                                        "CustomerRef" => [
                                                            "value" => $customer_id/* Customer Id from Quickbook */
                                                        ],
                                                        "PaymentMethodRef" => [
                                                            "value" => $payment_mode
                                                        ],
                                                        "PaymentRefNum" => "PY-" . $cheque_referenc_number,
                                                        "TotalAmt" => $invoice_amt_without_tax, /* Total amount of Invoice */
                                                        "UnappliedAmt" => $invoice_amt_without_tax
                                            ]);
                                            $resultObj = $dataService->Add($InvoicePaymentToCreate);
                                            $error = $dataService->getLastError();
                                            if ($error) {
                                                Yii::$app->session->setFlash('error', 'Something went wrong while adding new Payment. Please try again later.');
                                                return $this->redirect(['create']);
                                                die;
                                            }
                                        }
                                        $qb_installment_id = "{$resultObj->Id}";
                                        //$ppm_models->qb_invoice_id = $qb_installment_id;
                                        //$ppm_models->save();
                                    }
                                    //}
                                }
                            }
                        }
                        // }
                    }

                    /*
                     * * @ Send Mail For : "Digital Vidya Trainings: Pre-Registration Information"
                     */

                    if ($course == 2) {
                        $fromName = ['hello@digitalacademyindia.com' => 'Digital Academy India'];
                        //   $team='Digital Academy India Team';
                    } else {
                        $fromName = ['donotreply@digitalvidya.co.in' => 'Digital Vidya'];
                        //  $team='Digital Vidya Team';
                    }

                    $bodyMsg = 'Dear ' . $first_name . ',

                        <p>Welcome to Digital Vidya!</p>
                        <p>We are still in the process of personalizing your training program. Very soon, you will receive further information from our side.</p>
                        <p>At this stage, we need some more information from your side. It will take 10 mins of your time.  Please click on the link below and provide your detailed information.</p>
                        <p><a href="http://salesadmin.digitalvidya.com/dashboard/userdetails/' . urlencode($email) . '/' . $token_id . '/' . strtolower($u_course[0]) . '">Provide your profile’s details</a></p>
                        <p>Btw, if you have any query you can dial in our support number 8081033033.</p>
                        <p>Looking forward to interacting with you further.</p>
                        <p>Regards,<br>
                        Training Delivery Team</p>';

                    //$this->email->to('dharmendra@whizlabs.com');
                    //$this->email->bcc('dharmendra@whizlabs.com');

                    $is_sent = Yii::$app->mailer->compose()
                            ->setFrom($fromName)
                            ->setTo($email)
                            ->setBcc('dharmendra@whizlabs.com')
                            ->setSubject('Digital Vidya Trainings: Pre-Registration Information')
                            ->setHtmlBody($bodyMsg)
                            ->send();
                    if (!$is_sent) {
                        echo "Record added successfully but mail not sent to user Subject : 'Digital Vidya Trainings: Pre-Registration Information', Please contact to developer";
                        die;
                    }

                    /*
                     * * @ Send Mail For : "'Confirm Your course Admission Details"
                     */

                    if ($course == 1) {
                        $fullcoursename = "Certified Digital Marketing Master Course";
                        $courseCategory = "Master Certification";
                        $courseId = "dm101";
                    }

                    if ($course == 2) {
                        $fullcoursename = "Certification Program in Digital Marketing Course";
                        $courseCategory = "Master Certification";
                        $courseId = "cpdm101";
                    }

                    if ($course == 3) {
                        $fullcoursename = "Certified Email Marketing Master Course";
                        $courseCategory = "Master Certification";
                        $courseId = "em101";
                    }

                    if ($course == 4) {
                        $fullcoursename = "Certified Social Media Marketing Master Course";
                        $courseCategory = "Master Certification";
                        $courseId = "smm101";
                    }

                    if ($course == 5) {
                        $fullcoursename = "Certified Inbound Marketing Master Course";
                        $courseCategory = "Master Certification";
                        $courseId = "im101";
                    }
                    if ($course == 6) {
                        $fullcoursename = "Certified Search Engine Marketing Master Course";
                        $courseCategory = "Master Certification";
                        $courseId = "sem101";
                    }

                    if ($course == 7) {
                        $fullcoursename = "Certified Search Engine Optimization Master Course";
                        $courseCategory = "Master Certification";
                        $courseId = "seo101";
                    }
                    /* if($course=="SEOS"){
                      $fullcoursename="Certified Search Engine Optimization Master Course";
                      $courseCategory="Master Certification";
                      $courseId="seos101";
                      } */
                    if ($course == 8) {
                        $fullcoursename = "Certified Web Analytics Master Course";
                        $courseCategory = "Master Certification";
                        $courseId = "wa101";
                    }

                    if ($course == 9) {
                        $fullcoursename = "Mobile Marketing";
                        $courseCategory = "Master Certification";
                        $courseId = "mm101";
                    }
                    if ($course == 10) {
                        $fullcoursename = "Certified Mobile App Marketing Course";
                        $courseCategory = "Master Certification";
                        $courseId = "cmam101";
                    }

                    if ($course == 11) {
                        $fullcoursename = "Certified Facebook Marketing Master Course";
                        $courseCategory = "Master Certification";
                        $courseId = "cfmm101";
                    }

                    if ($course == 12) {
                        $fullcoursename = "TJW";
                        $courseCategory = "Master Certification";
                        $courseId = "tjw01";
                    }

                    if ($course == 13) {
                        $fullcoursename = "Data Analytics using R";
                        $courseCategory = "Master Certification";
                        $courseId = "dar101";
                    }


                    if ($course == 14) {
                        $fullcoursename = "Data Science using Python Course";
                        $courseCategory = "Master Certification";
                        $courseId = "dap101";
                    }

                    if ($course == 15) {
                        $fullcoursename = "Data Analytics using SAS Course";
                        $courseCategory = "Master Certification";
                        $courseId = "dsas101";
                    }

                    if ($course == 16) {
                        $fullcoursename = "Data Analytics using Excel Course";
                        $courseCategory = "Master Certification";
                        $courseId = "dae101";
                    }

                    if ($course == 17) {
                        $fullcoursename = "BDA";
                        $courseCategory = "Master Certification";
                        $courseId = "bda101";
                    }

                    if ($course == 18) {
                        $fullcoursename = "BDE";
                        $courseCategory = "Master Certification";
                        $courseId = "bde101";
                    }


                    /* if($course=="DAPS"){
                      $fullcoursename="Certified Data Science Using Python";
                      $courseCategory="Master Certification";
                      $courseId="daps101";
                      }

                      if($course=="DMSW"){
                      $fullcoursename="Digital Marketing Strategy Workshop";
                      $courseCategory="Workshop";
                      $courseId="dmsw101";
                      } */

                    // Payment currency information
                    $Currency = '';
                    $Currencytext = '';

                    if ($dd_payment_currency_name == 'INR') {
                        $Currency = "Rs.";
                        $Currencytext = "Rs. 200/day";
                    } else if ($dd_payment_currency_name == 'USD') {
                        $Currency = "USD";
                        $Currencytext = "USD 5/day";
                    } else if ($dd_payment_currency_name == 'AED') {
                        $Currency = "AED";
                        $Currencytext = "AED 15/day";
                    } else if ($dd_payment_currency_name == 'AUD') {
                        $Currency = "AUD";
                        $Currencytext = "AUD 10/day";
                    } else {
                        $Currency = $dd_payment_currency_name;
                        $Currencytext = $dd_payment_currency_name . " 5/day";
                    }

                    $sales_det = DvUsers::find()->select('email')->where(['id' => $sales_user_id, 'status' => 1])->one();
                    $salepersonemail = $sales_det->email;
                    $installmentamount = '';
                    $installmenttext = '<p>Please ensure that you make the balance payment by the date(s) mentioned above. <strong>In case of a delayed payment, the late fee of ' . $Currencytext . '</strong> will be charged upto a maximum of 7 days grace period. In case your payment is not received within the grace period, your training will be stopped till you complete your payment</p>';
                    if ($is_full_payment == 0) {


                        $installmentamount = '<li><strong>Balance Fee Schedule:</strong>';
                        $installmentamount.='<ul>';

                        for ($i = 2; $i < $installment + 2; $i++) {

                            $inst_date = date("d-m-Y", strtotime($data['installment'][$i]['date']));
                            $inst_amt = $data['installment'][$i]['amt'];
                            $cnt = $i - 1;
                            $installmentamount.='<li>Installment ' . $cnt . ': ' . $Currency . ' ' . $inst_amt . ' on ' . $inst_date . '</li>';
                        }

                        $installmentamount.="</ul>";
                        $installmentamount.="</li>";
                    }



                    // $this->email->from('admissions@digitalvidya.com', 'Raj Sharma (Digital Vidya)');
                    $fromName_2 = ['admissions@digitalvidya.com' => 'Finance Team (Digital Vidya)'];

                    $bodyMsg_2 = 'Dear ' . $first_name . ',

                        <p>Congratulations on your registration for ' . $fullcoursename . ' by Digital Vidya. We’ve received following details related to your admission into our program:</p>

                        <ul>
                          <li><strong>Course Name:</strong> ' . $u_course[0] . '</li>
                          <li><strong>Batch Date:</strong> ' . $course_batch_date . '</li>
                          <li><strong>Confirmed Total Fee:</strong> ' . $Currency . ' ' . $total_confirmed_amount . '</li>
                          <li><strong>Fee Paid:</strong> ' . $Currency . ' ' . $amount_recieved . '</li>
                          ' . $installmentamount . '
                          <li><strong>Payment Currency</strong>: ' . $dd_payment_currency_name . '</li>
                        </ul>

                        <p><strong>Please confirm the above-mentioned details by replying to this mail</strong>. Once we’ve received your confirmation, we will forward your details to our Training Delivery Team to start your Training Process. In case you find any discrepancy, please reply back with the details so that we can get in touch with you to understand the gaps.</p>

                             ' . $installmenttext . '

                        <p>We will also mail you the invoice(s) of the paid fees once your payment is confirmed.</p>
                        <p>Happy Learning!</p>
                        <p>Thanks and regards<br>
                        Finance Team <br>
                        Digital Vidya</p>';

                    //$this->email->to('dharmendra@whizlabs.com');
                    $is_sent = Yii::$app->mailer->compose()
                            ->setFrom($fromName_2)
                            ->setTo($email)
                            ->setCc($salepersonemail)
                            ->setBcc('dharmendra@whizlabs.com')
                            ->setSubject('Confirm Your ' . $fullcoursename . ' Admission Details')
                            ->setHtmlBody($bodyMsg_2)
                            ->send();

                    if (!$is_sent) {
                        echo "Record added successfully but mail not sent to user Subject : 'Digital Vidya Trainings: Pre-Registration Information', Please contact to developer";
                        die;
                    }

                    /*
                     * * @ Sales person notification 
                     */
                    $sales_name = DvUsers::find()->select('first_name')->where(['id' => $sales_user_id, 'status' => 1])->one();
                    $sale_name = $sales_name->first_name;

                    $fromName_3 = ['donotreply@digitalvidya.co.in' => 'Digital Vidya'];
                    $bodyMsg_3 = 'Hi ' . $sale_name . ',

                            <p> <strong>Details you have filled:</strong><br/>
                            <strong>Enrollment Date</strong>: ' . date("d-m-Y") . ' <br/>
                            <strong>Name</strong>: ' . $first_name . ' ' . $last_name . ' <br/>
                            <strong>Email ID</strong>: ' . $email . ' <br/>
                            <strong>Phone No.</strong>: ' . $mobile . ' <br/>
                            <strong>Country: </strong>: ' . $country_name['name'] . ' <br/>
                            <strong>State: </strong>: ' . $state_name['name'] . ' <br/>
                            <strong>City: </strong>: ' . $city_name['name'] . ' <br/>
                            <strong>Address: </strong>: ' . $address . ' <br/>
                            <strong>Course Name</strong>: ' . $fullcoursename . ' <br/>
                            <strong>Modules Allowed</strong>: ' . $modules_allowed . ' <br/>
                            <strong>Batch Date</strong>: ' . $course_batch_date . ' <br/>
                            <strong>Total Confirmed Amount</strong>: ' . $total_confirmed_amount . ' <br/>
                            <strong>Payment Received Date</strong>: ' . date("d-m-Y") . ' <br/>
                            <strong>Payment Currency</strong>: ' . $dd_payment_currency_name . ' <br/>
                            <strong>Payment Received Amount</strong>: ' . $amount_recieved . ' <br/>
                            <strong>Payment Reference No.</strong>: ' . $payment_reference_number . ' <br/>
                            </p>
                            <p>Regards,<br>
                            Digital Vidya</p>';


                    $environment = Yii::$app->params['environment']; // check server enviroment
                    if ($environment == 'Production') {
                        // live
                        $is_sent = Yii::$app->mailer->compose()
                                ->setFrom($fromName_3)
                                ->setTo($salepersonemail)
                                ->setBcc('dharmendra@whizlabs.com')
                                ->setCc('finance@digitalvidya.com')
                                ->setSubject('WELL DONE: Registration Confirmation for ' . $fullcoursename)
                                ->setHtmlBody($bodyMsg_3)
                                ->send();
                    } else {
                        // development
                        $is_sent = Yii::$app->mailer->compose()
                                ->setFrom($fromName_3)
                                ->setTo($salepersonemail)
                                ->setBcc('dharmendra@whizlabs.com')
                                ->setSubject('WELL DONE: Registration Confirmation for ' . $fullcoursename)
                                ->setHtmlBody($bodyMsg_3)
                                ->send();
                    }





                    if (!$is_sent) {
                        echo "Record added successfully but mail not sent to user Subject : 'Digital Vidya Trainings: Pre-Registration Information', Please contact to developer";
                        die;
                    }
                }
            }

            if ($id && $customer_id) {
                //Yii::$app->session->setFlash('success', 'New Participant Created.');
                Yii::$app->session->setFlash('success', 'New Participant Created!');
                return $this->redirect(['create']);
            } else {
                Yii::$app->session->setFlash('error', 'Something went wrong to create Participant.');
                return $this->redirect(['create']);
            }
        } else {
            return $this->render('create', ['model' => $model, 'incentive' => $incentiveArr, 'allPaymentmethod' => $allPaymentmethod, 'companycurrency' => $qbCurrencyArr]);
        }
    }

    /* ajax call to list Course format  */

    public function actionGet_format() {
        
        if(isset($_POST['course_id']) && $_POST['course_id'] == 1){
            $output = '';
            $output .= '<select id="dvregistration-course_format" class="form-control" name="DvRegistration[course_format]" required="required">
          <option value="">Select Course Format</option>
          <option value="1">Format 1.0 (Online)</option>
          <option value="2">CRoom(Offline)</option>
          </select>';
            return $output;
        } else {
            $output = '';
            $output .= '<select id="dvregistration-course_format" class="form-control" name="DvRegistration[course_format]" required="required">
          <option value="">Select Course Format</option>
          <option value="1">Format 1.0 (Online)</option>
          </select>';
            return $output;
        }
        /*} else {
            return $this->redirect(['dv-cdo-registration/index']);
        }*/
    }

    /* ajax call to list Modules Allowed  */

    public function actionModules_allowed() {
        if (isset($_POST['course_id']) != 0) {
            $output = '';
            if ($_POST['course_id'] == 1) {
                $upto = 6;
            } else if ($_POST['course_id'] == 2) {
                $upto = 5;
            } else {
                $upto = 1;
            }
            $output .= '<select id="dvregistration-modules_allowed" class="form-control" name="DvRegistration[modules_allowed]" required="required">
          <option value="">Select Modules Allowed</option>';

            for ($i = 1; $i <= $upto; $i++) {
                $output .= '<option value="' . $i . '">' . $i . '</option>';
            }

            $output .= '</select>';
            return $output;
        } else {
            return $this->redirect(['dv-registration/index']);
        }
    }

    /* ajax call to list Avilable Batch   */

    public function actionGet_batch() {
        if (isset($_POST['format_id']) != 0) {
            $format_id = $_POST['format_id'];
            $course_id = $_POST['course_id'];
            $output = '';
            $output .= '<select id="dvregistration-course_batch" class="form-control" name="DvRegistration[course_batch]" required="required">';
            $output .= '<option value="">Select Batch</option>';
             
            if (($course_id == 1) || ($course_id == 2)) {

                $query = new \yii\db\Query;
                $query->select(["assist_batches.*", "assist_batches.*"])
                        ->from('assist_batches')
                        ->join('inner Join', 'assist_batches_meta', 'assist_batches_meta.mid = assist_batches.id'
                        )
                        ->andWhere(['assist_batches_meta.meta_key' => 'running_batch_status'])
                        ->andWhere(['assist_batches_meta.meta_value' => 0])
                        ->andWhere(['assist_batches.format' => $format_id]);
                $command = $query->createCommand();
                $Pmodule = $command->queryAll();
            } else {

                $query = new \yii\db\Query;
                $query->select(["assist_batches.*", "assist_batches.*"])
                        ->from('assist_batches')
                        ->join('inner Join', 'assist_batches_meta', 'assist_batches_meta.mid = assist_batches.id'
                        )
                        ->andWhere(['assist_batches_meta.meta_key' => 'running_batch_status'])
                        ->andWhere(['assist_batches_meta.meta_value' => 0])
                        ->andWhere(['assist_batches.format' => $format_id])
                        ->andWhere(['assist_batches.course' => $course_id]);
                $command = $query->createCommand();
                $Pmodule = $command->queryAll();
            }

            foreach ($Pmodule as $module) {
                $course = DvCourse::find()->where(['id' => $module['course']])->all();
                $Ucourse = ArrayHelper::map($course, 'id', 'name');
                $u_course = array_values($Ucourse);

                if ($module['trainer'] == 0) {
                    $trainer_person = '---';
                } else {
                    $trainer = DvUsers::find()->where(['id' => $module['trainer'], 'status' => 1])->all();
                    $Dv_trainer = ArrayHelper::map($trainer, 'id', 'first_name');
                    $trainer_person = array_values($Dv_trainer);
                    $trainer_person = $trainer_person[0];
                }
                $value = $module['id'];
                $Dv_pmodule = ' Date: ' . Yii::$app->CustomComponents->date_formatting($module['start_date']) . ', Time: ' . $module['stiming'] . '(IST) (' . $u_course[0] . '-' . $module['day'] . ') [Trainer: ' . $trainer_person . ', Format: ' . $module['format'] . ', Location: Online]';
                $output .= '<option value="' . $value . '">' . $Dv_pmodule . '</option>';
            }
            $output .= '</select>';

            return $output;
        } else {
            return $this->redirect(['dv-registration/index']);
        }
    }

    /* Function for view registration page */

    public function actionView($id) {
        /* redirect a user if not super admin */
        if (!Yii::$app->CustomComponents->check_permission('view_registration')) {
            return $this->redirect(['index']);
        }

        $dataService = $this->quickbook_instance();
        $currency = "";

        $query = DvRegistration::find()->where(['id' => $id])->one();
        $qb_customer_id = $query->qb_customer_id;

        $total_payments = $dataService->Query("Select count(*) from Payment");
        $all_payments = $dataService->Query("Select * from Payment where CustomerRef='" . $qb_customer_id . "' ORDER BY Id DESC MAXRESULTS $total_payments");

        $total_credit_memos = $dataService->Query("Select count(*) from CreditMemo");
        $all_credit_memos = $dataService->Query("Select * from CreditMemo where CustomerRef='" . $qb_customer_id . "' ORDER BY Id DESC MAXRESULTS $total_credit_memos");

        $total_invoices = $dataService->Query("SELECT count(*) FROM Invoice");
        $allInvoices = $dataService->Query("SELECT * FROM Invoice where CustomerRef='" . $qb_customer_id . "' MAXRESULTS $total_invoices");
        $all_invoice_of_customer = $allInvoices;

        $Payment_result = $dataService->Query("SELECT * from Payment where CustomerRef='" . $qb_customer_id . "' MAXRESULTS $total_payments");

        $customer_details = $dataService->Query("SELECT * from Customer WHERE Id='$qb_customer_id'");
        if (!empty($customer_details)) {
            $currency = $customer_details[0]->CurrencyRef;
        }
        $total_paymentmethod_result = $dataService->Query("SELECT count(*) from PaymentMethod");
        $allPaymentmethod_result = $dataService->Query("SELECT * from PaymentMethod MAXRESULTS $total_paymentmethod_result");
        $allPaymentmethod = array();
        foreach ($allPaymentmethod_result as $pay_value) {
            $allPaymentmethod[$pay_value->Id] = $pay_value->Name;
        }

        return $this->render('view', [ 'model' => $this->findModel($id), 'allInvoices' => $allInvoices, 'all_invoice_of_customer' => $all_invoice_of_customer, 'allPaymentmethod' => $allPaymentmethod, 'payment_result' => $Payment_result, 'qb_customer_id' => $qb_customer_id, 'all_credit_memos' => $all_credit_memos, 'all_payments' => $all_payments, "currency" => $currency]);
    }

    /**
     * Finds the MwFeedback model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MwFeedback the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = DvRegistration::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /* ajax call to check email address  */

    public function actionCheck_email() {
        if (isset($_POST['email']) != 0) {
            $output = '0';
            $email = $_POST['email'];
            $course = $_POST['course'];
            // if (preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^", $email)) {
            $modules = Yii::$app->db->createCommand("SELECT * FROM assist_participant WHERE email='$email' and course='$course'")->queryAll();
            if (!empty($modules)) {
                $output = '1';
            }
            //  }
            return $output;
        } else {
            return $this->redirect(['dv-registration/index']);
        }
    }

    public function actionSort($order) {
        /* redirect a user if not super admin */
        if (!Yii::$app->CustomComponents->check_permission('view_registration')) {
            return $this->redirect(['site/index']);
        }
        $query = DvRegistration::find();
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 20]);
        $offset = $pagination->offset + 1;

        if ($order == 'asc') {
            $sor_o = SORT_ASC;
        } elseif ($order == 'desc') {
            $sor_o = SORT_DESC;
        } else {
            $sor_o = SORT_ASC;
        }
        $models = $query->offset($pagination->offset)->orderBy(['created_on' => $sor_o])->limit($pagination->limit)->all();

        return $this->render('index', [ 'participant_users' => $models, 'pages' => $pagination, 'count' => $offset]);
    }

    public function actionFilter() {
        /* redirect a user if not super admin */
        if (!Yii::$app->CustomComponents->check_permission('view_registration')) {
            return $this->redirect(['site/index']);
        }

        $dataService = $this->quickbook_instance();

        $user = Yii::$app->user->identity;
        $usermeta_result = DvUserMeta::find()->where(['uid' => $user->id, 'meta_key' => 'role'])->one();
        $user_role = $usermeta_result->meta_value; /*  2 - Executive role. /*  1 - Admin */

        $data = Yii::$app->request->get();

        $filter_data = array();
        $all_invoice_of_customer = array();

        $all_query = array();
        if ($data) {
            // $first_date_of_current_month = date("Y-m-01"); /* Default Start Date for filter. Current month First date */
            // $last_date_of_current_month = date("Y-m-t"); /* Default End Date for filter. Current month Last date */
            if ($data['participant_status'] != '') {
                $filter_data['participant_status'] = $data['participant_status'];
            }
            if ($data['participant_payment_status'] != '') {
                $filter_data['participant_payment_status'] = $data['participant_payment_status'];
            }
            if (isset($data['sales_user_id']) && $data['sales_user_id'] != '') {
                $filter_data['sales_user_id'] = $data['sales_user_id'];
            }
            if (isset($data['email']) && $data['email'] != '') {
                $filter_data['email'] = $data['email'];
            }
            if ($data['bymonth'] != '' && $data['by_date_month'] == 'm') {

                $new_date = explode('_', $data['bymonth']);
                $fyear = $new_date['1'];
                $fmonth = $new_date['0'];

                if ($fmonth <= 9 && strlen($fmonth) == 1) {
                    $fmonth = "0" . $fmonth;
                }
                $filter_data['YEAR(created_on)'] = $fyear;
                $filter_data['MONTH(created_on)'] = $fmonth;
            }

            if ($user_role != 1) {
                $filter_data['sales_user_id'] = $user->id;
            }


            if ($data['course'][0] != "" && $data['sdate'] != "" && $data['by_date_month'] == 'd') {
                $model = DvRegistration::find()->where(['BETWEEN', 'created_on', date("Y-m-d", strtotime($data['sdate'])), date("Y-m-d", strtotime("+1 day", strtotime($data['edate'])))])->andWhere(['in', 'course', $data['course']])->andWhere($filter_data);
            } else if ($data['sdate'] != "" && $data['by_date_month'] == 'd') {
                $model = DvRegistration::find()->where(['BETWEEN', 'created_on', date("Y-m-d", strtotime($data['sdate'])), date("Y-m-d", strtotime("+1 day", strtotime($data['edate'])))])->andWhere($filter_data);
            } else if ($data['course'][0] != "") {
                $model = DvRegistration::find()->where(['in', 'course', $data['course']])->andWhere($filter_data);
            } else {
                $model = DvRegistration::find()->Where($filter_data);
            }


            $model->orderBy(['id' => SORT_DESC]);
            $count = $model->count();
            $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 20]);
            $models = $model->offset($pagination->offset)->limit($pagination->limit)->all();
            $offset = $pagination->offset + 1;

            if ($data['bymonth'] != '' && $data['by_date_month'] == 'm') {
                $filter_data['bymonth'] = $data['bymonth'];
            }

            if ($data['by_date_month'] != '') {
                $filter_data['by_date_month'] = $data['by_date_month'];
            }

            if ($data['sdate'] != '' && $data['by_date_month'] == 'd') {
                $filter_data['sdate'] = $data['sdate'];
                $filter_data['edate'] = $data['edate'];
            }

            if ($data['course'][0] != "") {
                $filter_data['course'] = $data['course'];
            }

            if ($data['email'] != "") {
                $filter_data['email'] = $data['email'];
            }

            $total_invoices = $dataService->Query("SELECT count(*) FROM Invoice");
            $allInvoices = $dataService->Query("SELECT * FROM Invoice MAXRESULTS $total_invoices");
            $all_invoice_of_customer = $allInvoices;

            return $this->render('index', ['dataService'=>$dataService, 'participant_users' => $models,'all_query'=>$all_query,'pages' => $pagination, 'count' => $offset, 'filter_data' => $filter_data, 'all_invoice_of_customer' => $all_invoice_of_customer]);
        } else {

            return $this->redirect(['dv-registration/index']);
        }
    }

    /**
     * @ Update Installments
     * */
    public function actionUpdate_participant_status() {
        /* redirect a user if not super admin */
        if (!Yii::$app->CustomComponents->check_permission('update_participant_status')) {
            return $this->redirect(['site/index']);
        }

        $user = Yii::$app->user->identity;
        $usermeta_result = DvUserMeta::find()->where(['uid' => $user->id, 'meta_key' => 'role'])->one();
        $user_role = $usermeta_result->meta_value;

        /*  Post form data */
        $data = Yii::$app->request->post();
        if ($data) {
            if (isset($data['DvRegistration']['sales_user_id'])) {
                $email = $data['DvRegistration']['email'];
                $models = DvRegistration::find()->where(['email' => $email])->one();
                $models->participant_status = $data['DvRegistration']['participant_status'];
                $models->save();
                Yii::$app->session->setFlash('success', 'Participant Status Updated.');
            }
            $email = $data['DvRegistration']['email'];
            $model = new DvRegistration();
            if ($user_role == 1) {
                $model_data = DvRegistration::find()->where(['email' => $email])->one();
            } else {
                $model_data = DvRegistration::find()->where(['email' => $email, 'sales_user_id' => $user->id])->one();
            }
            if (!$model_data) {
                return $this->render('update_paricipant_status', ['model' => $model, 'NoRecord' => "No Record Found, Related to <b>$email</b>"]);
            } else {
                return $this->render('update_paricipant_status', ['model' => $model, 'model_data' => $model_data]);
            }
        } else {
            $model = new DvRegistration();

            return $this->render('update_paricipant_status', [ 'model' => $model]);
        }
    }

    /**
     * @ Update Installments
     * */
    public function actionUpdate_participant_payment_ajex() {


        if (isset($_POST['participant_id'])) {
            $participant_id = $_POST['participant_id'];
            $status_id = $_POST['status_id'];
            $url = $_POST['url'];

            $user = Yii::$app->user->identity;
            $salesEmail = $user->email;
            $salesName = $user->first_name;

            $models = DvRegistration::findOne($participant_id);
            $email = $models->email;

            $old_participant_status = $models->participant_status;


            $userlog_id = Yii::$app->CustomComponents->create_UsersActivityLog($participant_id, $url, "Updated participant status", $old_participant_status, $status_id);
            if ($userlog_id == '') {
                echo "There are some error in user log";
                die;
            }

            // $models->participant_status = $status_id;
            // $models->save();

            $models_res = Yii::$app->db->createCommand('UPDATE assist_participant SET participant_status=' . $status_id . ' WHERE id=' . $participant_id)->execute();

            if ($models_res) {

                $p_status = '';
                if ($status_id == 1) {
                    $p_status = "Active";
                } elseif ($status_id == 2) {
                    $p_status = "On Hold";
                } elseif ($status_id == 3) {
                    $p_status = "Drop off";
                } elseif ($status_id == 4) {
                    $p_status = "completed";
                }

                $fromName = ['donotreply@digitalvidya.co.in' => 'Digital Vidya'];
                $bodyMsg = '<br>

                            Email ID : ' . $email . '<br>
                            Partipation Status : ' . $p_status . '<br>
                            Updated On : ' . date("d/m/Y") . '<br>
                            Updated By : ' . $salesName;

                $environment = Yii::$app->params['environment']; // check server enviroment
                if ($environment == 'Production') {
                    // live
                    $is_sent = Yii::$app->mailer->compose()
                            ->setFrom($fromName)
                            ->setTo(array("delivery@digitalvidya.com", $salesEmail))
                            ->setBcc('dharmendra@whizlabs.com')
                            ->setSubject('Module Alloment Sheet - Entry Updated ')
                            ->setHtmlBody($bodyMsg)
                            ->send();
                } else {
                    $is_sent = Yii::$app->mailer->compose()
                            ->setFrom($fromName)
                            ->setTo(array("chintand@whizlabs.com", "cdo@gmail.com"))
                            ->setSubject('Module Alloment Sheet - Entry Updated ')
                            ->setHtmlBody($bodyMsg)
                            ->send();
                }

                if ($is_sent) {
                    echo "1";
                } else {
                    echo "2";
                }
            } else {
                echo "2";
            }
        }
    }

    /**
     * @ Update Installments
     * */
    public function actionUpdate_participant_allowed_module_ajex() {
        if (isset($_POST['participant_id'])) {
            $participant_id = $_POST['participant_id'];
            $allowed_module = $_POST['allowed_module'];
            $url = $_POST['url'];

            $user = Yii::$app->user->identity;
            $salesEmail = $user->email;
            $salesName = $user->first_name;

            $models = DvRegistration::findOne($participant_id);
            $email = $models->email;

            $old_allowed_module = $models['modules_allowed'];

            $userlog_id = Yii::$app->CustomComponents->create_UsersActivityLog($participant_id, $url, "Updated allowed modules", $old_allowed_module, $allowed_module);
            if ($userlog_id == '') {
                echo "There are some error in user log";
                die;
            }

            // $models->'modules_allowed' = 5;
            // $models->save();

            $models_res = Yii::$app->db->createCommand('UPDATE assist_participant SET modules_allowed=' . $allowed_module . ' WHERE id=' . $participant_id)->execute();

            if ($models_res) {

                /*$participant_meta = Yii::$app->db->createCommand('UPDATE assist_participant_batch_meta SET total_allowed_module=' . $allowed_module . ' WHERE pid=' . $participant_id)->execute();

                if(!$participant_meta){
                    echo "Something went wrong when insert data to participant meta table.";
                    die; 
                }*/

                $fromName = ['donotreply@digitalvidya.co.in' => 'Digital Vidya'];
                $bodyMsg = '<br>

                            Email ID : ' . $email . '<br>
                            Modules Allowed : ' . $allowed_module . '<br>
                            Updated On : ' . date("d/m/Y") . '<br>
                            Updated By : ' . $salesName;

                $environment = Yii::$app->params['environment']; // check server enviroment
                if ($environment == 'Production') {
                    // live
                    $is_sent = Yii::$app->mailer->compose()
                            ->setFrom($fromName)
                            ->setTo(array("delivery@digitalvidya.com", $salesEmail))
                            ->setBcc('dharmendra@whizlabs.com')
                            ->setSubject('Module Alloment Sheet - Entry Updated ')
                            ->setHtmlBody($bodyMsg)
                            ->send();
                } else {
                    $is_sent = Yii::$app->mailer->compose()
                            ->setFrom($fromName)
                            ->setTo(array("chintand@whizlabs.com", "cdo@gmail.com"))
                            ->setSubject('Module Alloment Sheet - Entry Updated ')
                            ->setHtmlBody($bodyMsg)
                            ->send();
                }

                if ($is_sent) {
                    echo "1";
                } else {
                    echo "2";
                }
            } else {
                echo "2";
            }
        }
    }

    /**
     * Lists all MwFeedback models.
     * @return mixed
     */
    public function actionTeamview() {

        /* redirect a user if not super admin */
        if (!Yii::$app->CustomComponents->check_permission('team_registration')) {
            return $this->redirect(['site/index']);
        }

        $user = Yii::$app->user->identity;
        $user_team_data = DvUserMeta::find()->where(['meta_value' => $user->id, 'meta_key' => 'team'])->all();

        $user_id = array();
        $managers = Yii::$app->db->createCommand("SELECT assist_users.id  FROM assist_users 
            join assist_user_meta ON assist_users.id = assist_user_meta.uid WHERE assist_users.department = 1 and 
            assist_user_meta.meta_key = 'team' and assist_user_meta.meta_value = '' AND assist_users.status=1")->queryAll();
        if (!empty($managers)) {
            foreach ($managers as $manager) {
                $manager_id = $manager['id'];
                if ($user->id != $manager_id) {
                    $user_id[] = $manager_id;
                }
                $executives = Yii::$app->db->createCommand("SELECT assist_user_meta.uid FROM assist_user_meta 
                        join assist_users ON assist_users.id = assist_user_meta.uid 
                        WHERE meta_key = 'team' AND meta_value = $manager_id AND assist_users.status=1")->queryAll();
                if (!empty($executives)) {
                    foreach ($executives as $executive) {
                        $executive_id = $executive['uid'];
                        if ($user->id != $executive_id) {
                            $user_id[] = $executive_id;
                        }
                    }
                }
            }
        }

        $query = DvRegistration::find()->where(['in', 'sales_user_id', $user_id]);
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 10]);
        $models = $query->offset($pagination->offset)->limit($pagination->limit)->all();
        $offset = $pagination->offset + 1;

        $all_models = DvRegistration::find()->where(['in', 'sales_user_id', $user_id])->all();

        return $this->render('team_register', ['all_participant_users'=>$all_models, 'participant_users' => $models, 'pages' => $pagination, 'count' => $offset]);
    }

    public function actionTeam_filter() {

        /* redirect a user if not super admin */
       /* if (!Yii::$app->CustomComponents->check_permission('view_registration')) {
            return $this->redirect(['site/index']);
        }*/

        $user = Yii::$app->user->identity;
        $usermeta_result = DvUserMeta::find()->where(['uid' => $user->id, 'meta_key' => 'role'])->one();
        $user_role = $usermeta_result->meta_value; /*  2 - Executive role. // 1 - Admin */

        $data = Yii::$app->request->get();

        $filter_data = array();

        if ($data) {

            if (isset($data['participant_status']) && $data['participant_status']!= '') {
                $filter_data['participant_status'] = $data['participant_status'];
            }
            if (isset($data['participant_payment_status']) && $data['participant_payment_status'] != '') {
                $filter_data['participant_payment_status'] = $data['participant_payment_status'];
            }
            if (isset($data['sales_user_id']) && $data['sales_user_id'] != '') {
                $filter_data['sales_user_id'] = $data['sales_user_id'];
            }
            if (isset($data['email']) && $data['email'] != '') {
                $filter_data['email'] = $data['email'];
            }
            if (isset($data['bymonth']) && $data['bymonth'] != '' && isset($data['by_date_month']) && $data['by_date_month'] == 'm') {

                $new_date = explode('_', $data['bymonth']);
                $fyear = $new_date['1'];
                $fmonth = $new_date['0']; /* date("m", strtotime($new_date['0'])); */

                $filter_data['YEAR(created_on)'] = $fyear;
                $filter_data['MONTH(created_on)'] = $fmonth;
            }
 
            $user_id = array();
            if (empty($data['byTeam'])) {
                $user_team_data = DvUserMeta::find()->where(['meta_value' => $user->id, 'meta_key' => 'team'])->all();
                $managers = Yii::$app->db->createCommand("SELECT assist_users.id  FROM assist_users 
                    join assist_user_meta ON assist_users.id = assist_user_meta.uid WHERE assist_users.department = 1 and 
                    assist_user_meta.meta_key = 'team' and assist_user_meta.meta_value = '' AND assist_users.status=1")->queryAll();

                if (!empty($managers)) {
                    foreach ($managers as $manager) {
                        $manager_id = $manager['id'];
                        if ($user->id != $manager_id) {
                            $user_id[] = $manager_id;
                        }
                        $executives = Yii::$app->db->createCommand("SELECT assist_user_meta.uid FROM assist_user_meta 
                            join assist_users ON assist_users.id = assist_user_meta.uid 
                            WHERE meta_key = 'team' AND meta_value = $manager_id AND assist_users.status=1")->queryAll();
                        if (!empty($executives)) {
                            foreach ($executives as $executive) {
                                $executive_id = $executive['uid'];
                                if ($user->id != $executive_id) {
                                    $user_id[] = $executive_id;
                                }
                            }
                        }
                    }
                }
            } else {
                $user_id[] = $data['byTeam'];
            }

            if (!empty($data['sales_user_id'])) {
                $user_id[] = $data['sales_user_id'];
            }

            if (isset($data['course'][0]) && $data['course'][0] != "" && isset($data['sdate']) && $data['sdate'] != "" && isset($data['by_date_month']) && $data['by_date_month'] == 'd') {
                $model = DvRegistration::find()->where(['BETWEEN', 'created_on', date("Y-m-d", strtotime($data['sdate'])), date("Y-m-d", strtotime($data['edate']))])->andWhere(['in', 'course', $data['course']])->andwhere(['in', 'sales_user_id', $user_id])->andWhere($filter_data);
                 $all_model = DvRegistration::find()->where(['BETWEEN', 'created_on', date("Y-m-d", strtotime($data['sdate'])), date("Y-m-d", strtotime($data['edate']))])->andWhere(['in', 'course', $data['course']])->andwhere(['in', 'sales_user_id', $user_id])->andWhere($filter_data)->all();
            } else if (isset($data['sdate']) && $data['sdate'] != "" && isset($data['by_date_month']) && $data['by_date_month'] == 'd') {
                $model = DvRegistration::find()->where(['BETWEEN', 'created_on', date("Y-m-d", strtotime($data['sdate'])), date("Y-m-d", strtotime($data['edate']))])->andwhere(['in', 'sales_user_id', $user_id])->andWhere($filter_data);
                $all_model = DvRegistration::find()->where(['BETWEEN', 'created_on', date("Y-m-d", strtotime($data['sdate'])), date("Y-m-d", strtotime($data['edate']))])->andwhere(['in', 'sales_user_id', $user_id])->andWhere($filter_data)->all();
            } else if (isset($data['course'][0]) && $data['course'][0] != "") {
                $model = DvRegistration::find()->where(['in', 'course', $data['course']])->andwhere(['in', 'sales_user_id', $user_id])->andWhere($filter_data);
                $all_model = DvRegistration::find()->where(['in', 'course', $data['course']])->andwhere(['in', 'sales_user_id', $user_id])->andWhere($filter_data)->all();
            } else {
                $model = DvRegistration::find()->where(['in', 'sales_user_id', $user_id])->andWhere($filter_data);
                $all_model = DvRegistration::find()->where(['in', 'sales_user_id', $user_id])->andWhere($filter_data)->all();
            }
 

            $count = $model->count();
            $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 10]);
            $models = $model->offset($pagination->offset)->limit($pagination->limit)->all();
            $offset = $pagination->offset + 1;

            if (isset($data['bymonth']) && $data['bymonth'] != '' && isset($data['by_date_month']) && $data['by_date_month'] == 'm') {
                $filter_data['bymonth'] = $data['bymonth'];
            }
            if (isset($data['by_date_month']) && $data['by_date_month'] != '') {
                $filter_data['by_date_month'] = $data['by_date_month'];
            }

            if (isset($data['sdate']) && $data['sdate'] != '' && isset($data['by_date_month']) && $data['by_date_month'] == 'd') {
                $filter_data['sdate'] = $data['sdate'];
                $filter_data['edate'] = $data['edate'];
            }
            if (isset($data['course'][0]) && $data['course'][0] != "") {
                $filter_data['course'] = $data['course'];
            }

            if (isset($data['byTeam']) && $data['byTeam'] != "") {
                $filter_data['byTeam'] = $data['byTeam'];
            }

            if (isset($data['email']) && $data['email'] != "") {
                $filter_data['email'] = $data['email'];
            }

            return $this->render('team_register', [ 'all_participant_users' => $all_model, 'participant_users' => $models, 'pages' => $pagination, 'count' => $offset, 'filter_data' => $filter_data]);
        } else {
            return $this->redirect(['dv-registration/teamview']);
        }
    }

    /* ajax call to check email address  */

    public function actionCheck_wp_email() {
        if (isset($_POST['email']) != 0) {
            //  $output = '0';
            $email = $_POST['email'];

            //echo $email; die();
            $result_users = array();


            if (preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^", $email)) {
                $exists = Yii::$app->db->createCommand("SELECT * FROM website_users WHERE user_email='$email'")->queryAll();


                if ($exists) {
                    $user_data = $exists[0];
                    $result_users['result'] = '1';

                    $result_users['wp_id'] = $user_data['ID'];
                    $result_users['wp_user'] = $user_data['user_login'];
                    $result_users['wp_email'] = $user_data['user_email'];

                    $wp_user_id = $user_data['ID'];

                    $first_name = Yii::$app->db->createCommand("SELECT * FROM website_usermeta WHERE user_id='$wp_user_id' AND meta_key = 'first_name' ")->queryAll();
                    $result_users['first_name'] = ucfirst(strtolower($first_name[0]['meta_value']));

                    $last_name = Yii::$app->db->createCommand("SELECT * FROM website_usermeta WHERE user_id='$wp_user_id' AND meta_key = 'last_name' ")->queryAll();
                    $result_users['last_name'] = ucfirst(strtolower($last_name[0]['meta_value']));
                } else {
                    $result_users['result'] = '0';
                }
            }

            echo json_encode($result_users);
            exit;
        } else {
            return $this->redirect(['dv-registration/index']);
        }
    }

    /* Function for  user activity */

    public function actionUsers_activity_log() {
        $query = DvUsersActivityLog::find();

        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 10]);
        $model = $query->offset($pagination->offset)->limit($pagination->limit)->orderBy(['id' => SORT_DESC])->all();
        $offset = $pagination->offset + 1;

        return $this->render('users_activity_log', [ 'model' => $model, 'pagination' => $pagination, 'count' => $offset, 'total_count' => $count]);
    }

    public function pending_revenue_manager_filter() {
        $dataService = $this->quickbook_instance();
        $first_date_of_current_month = date("Y-m-01"); /* Default Start Date for filter. Current month First date */
        $last_date_of_current_month = date("Y-m-t"); /* Default End Date for filter. Current month Last date */
        $timestamp_of_first_date_of_month = strtotime($first_date_of_current_month);
        $timestamp_of_last_date_of_month = strtotime($last_date_of_current_month);

        $user = Yii::$app->user->identity;
        $usermeta_result = DvUserMeta::find()->where(['uid' => $user->id, 'meta_key' => 'role'])->one();
        $user_role = $usermeta_result->meta_value;
        $data = Yii::$app->request->post();

        $filter_data = array();
        $month_year_arr = array();
        $all_invoice_of_customer = array();
        $allInvoices = array();
        $allPayments = array();
    }

    /**
     * Pending Revenue : This function  will get details of all Payments whose amount is pending.
     * */
    public function actionPending_revenue() {

        /* redirect a user if not super admin */
        if (!Yii::$app->CustomComponents->check_permission('pending_revenue')) {
            return $this->redirect(['site/index']);
        }

        $dataService = $this->quickbook_instance();
        $courses_from_qb = $dataService->Query("SELECT * FROM Item");
        $first_date_of_current_month = date("Y-m-01"); /* Default Start Date for filter. Current month First date */
        $last_date_of_current_month = date("Y-m-t"); /* Default End Date for filter. Current month Last date */
        $timestamp_of_first_date_of_month = strtotime($first_date_of_current_month);
        $timestamp_of_last_date_of_month = strtotime($last_date_of_current_month);

        $user = Yii::$app->user->identity;
        $usermeta_result = DvUserMeta::find()->where(['uid' => $user->id, 'meta_key' => 'role'])->one();
        $user_role = $usermeta_result->meta_value;
        $data = Yii::$app->request->post();

        $filter_data = array();
        $by_team_arr = array();
        $month_year_arr = array();
        $all_invoice_of_customer = array();
        $allInvoices = array();
        $allPayments = array();

        $total_invoices = $dataService->Query("SELECT count(*) FROM Invoice");
        $total_payments = $dataService->Query("SELECT count(*) FROM Payment");

        if ($data) {

            if ($user_role == 33) {
                $this->pending_revenue_manager_filter();
            } else {
                $first_date_of_current_month = date("Y-m-01"); /* Default Start Date for filter. Current month First date */
                $last_date_of_current_month = date("Y-m-t"); /* Default End Date for filter. Current month Last date */

                if (isset($data['sales_user_id']) && $data['sales_user_id'] != "") {
                    $user_id = $data['sales_user_id'];
                } else {
                    $user_id = $user->id;
                }

                if ($user_role == 7) {
                    $filters = [];
                } else if ($user_role == 6) {
                    $filters = ['sales_user_id' => $data['sales_user_id']];
                } else if ($user_role == 1) {
                    $filters = [];
                } else {
                    $filters = ['sales_user_id' => $user_id];
                }

                $team_ids = array();
                if (isset($data['email']) && $data['email'] != '') {
                    $filter_data['email'] = $data['email'];
                }
                if ($data['bymonth'] != '' && $data['by_date_month'] == 'm') {

                    $new_date = explode('_', $data['bymonth']);
                    $fyear = $new_date['1'];
                    $fmonth = $new_date['0'];

                    if ($fmonth <= 9 && strlen($fmonth) == 1) {
                        $fmonth = "0" . $fmonth;
                    }
                    $first_date_of_current_month = date("$fyear-$fmonth-01");
                    $last_date_of_current_month = $lastday = date('Y-m-t', strtotime($first_date_of_current_month));
                } else if ($data['by_date_month'] == 'd') {
                    $first_date_of_current_month = date("Y-m-d", strtotime($data['sdate']));
                    $last_date_of_current_month = date("Y-m-d", strtotime($data['edate']));
                }

                $timestamp_of_first_date_of_month = strtotime($first_date_of_current_month);
                $timestamp_of_last_date_of_month = strtotime($last_date_of_current_month);

                $allInvoices = array();
                $qb_cust_id_arr = array();

                if ($user_role == 7) {
                    
                } else if ($user_role == 6) {
                    
                } else if ($user_role != 1) {
                    $filter_data['sales_user_id'] = $user->id;
                }
                if (isset($data['by_date_month'])) {

                    if ($user_role == 7) { /* If current user is not super admin */

                        if (isset($data['byTeam']) && !empty($data['byTeam'])) {
                            $by_team_arr['byTeam'] = $data['byTeam'];
                            $byteam = $data['byTeam'];
                            if (isset($data['sales_user_id']) && !empty($data['sales_user_id'])) {
                                if (count($data['sales_user_id']) == 1 && empty($data['sales_user_id'][0])) {
                                    $team_model_val = Yii::$app->db->createCommand("SELECT assist_user_meta.uid FROM assist_users join assist_user_meta ON assist_users.id = assist_user_meta.uid WHERE assist_user_meta.meta_key = 'team' and assist_user_meta.meta_value = $byteam ")->queryAll();
                                    $sales_head_str = "";
                                    if (!empty($team_model_val)) {
                                        foreach ($team_model_val as $m_sales_head) {
                                            $sales_head_str .= $m_sales_head['uid'] . ",";
                                        }
                                    }
                                    $sales_head_str .= $byteam;
                                    $sales_head_str = rtrim($sales_head_str, ",");

                                    $team = $data['byTeam'];
                                    $qb_id_from_us = Yii::$app->db->createCommand("SELECT * FROM assist_participant WHERE sales_user_id IN ($sales_head_str)")->queryAll();

                                    $combined_arr = $qb_id_from_us;

                                    $qb_cust_id_str = "";

                                    if (!empty($combined_arr)) {
                                        foreach ($combined_arr as $qb_ids) {
                                            if (!empty($qb_ids['qb_customer_id'])) {
                                                $qb_cust_id_str .= "'" . $qb_ids['qb_customer_id'] . "',";
                                                $qb_cust_id_arr[] = $qb_ids['qb_customer_id'];
                                            }
                                        }

                                        $qb_cust_id_str = rtrim($qb_cust_id_str, ",");
                                        $allInvoices = $dataService->Query("SELECT * FROM Invoice where CustomerRef IN($qb_cust_id_str) ORDER BY Id ASC MAXRESULTS $total_invoices");
                                        $allPayments = $dataService->Query("SELECT * FROM Payment where CustomerRef IN($qb_cust_id_str) MAXRESULTS $total_payments");
                                    }
                                } else {
                                    $by_team_arr['sales_user_id'] = $data['sales_user_id'];
                                    $sales_user_id_arr = $data['sales_user_id'];
                                    if (empty($data['sales_user_id'][0])) {
                                        $sales_user_id_arr[] = $data['byTeam'];
                                    }

                                    $qb_id_from_us = DvRegistration::find()->select('qb_customer_id')->Where(['in', 'sales_user_id', $sales_user_id_arr])->all();

                                    $qb_cust_id_str = "";

                                    if (!empty($qb_id_from_us)) {
                                        foreach ($qb_id_from_us as $qb_ids) {
                                            if (!empty($qb_ids->qb_customer_id)) {
                                                $qb_cust_id_str .= "'" . $qb_ids->qb_customer_id . "',";
                                                $qb_cust_id_arr[] = $qb_ids->qb_customer_id;
                                            }
                                        }
                                        $qb_cust_id_str = rtrim($qb_cust_id_str, ",");
                                        $allInvoices = $dataService->Query("SELECT * FROM Invoice WHERE CustomerRef IN($qb_cust_id_str) ORDER BY Id ASC MAXRESULTS $total_invoices");
                                        $allPayments = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($qb_cust_id_str) MAXRESULTS $total_payments");
                                    }
                                }
                            } else {
                                if (!empty($data['byTeam'])) {
                                    $team = $data['byTeam'];
                                    $qb_id_from_us = Yii::$app->db->createCommand("SELECT * FROM assist_participant WHERE sales_user_id = $team")->queryAll();

                                    $combined_arr = $qb_id_from_us;
                                } else {
                                    $qb_id_from_us = Yii::$app->db->createCommand("SELECT * FROM assist_participant WHERE sales_user_id IN (SELECT assist_user_meta.uid FROM assist_users join assist_user_meta ON assist_users.id = assist_user_meta.uid WHERE assist_users.department = 1 and assist_user_meta.meta_key = 'team' and assist_user_meta.meta_value = '' )")->queryAll();

                                    $team_model_val = Yii::$app->db->createCommand("SELECT qb_customer_id FROM assist_participant WHERE sales_user_id IN (SELECT assist_user_meta.uid FROM assist_users join assist_user_meta ON assist_users.id = assist_user_meta.uid WHERE assist_user_meta.meta_key = 'team' and assist_user_meta.meta_value IN(SELECT assist_user_meta.uid FROM assist_users join assist_user_meta ON assist_users.id = assist_user_meta.uid WHERE assist_users.department = 1 and assist_user_meta.meta_key = 'team' and assist_user_meta.meta_value = '' ) )")->queryAll();

                                    $combined_arr = array_merge($qb_id_from_us, $team_model_val);
                                }

                                $qb_cust_id_str = "";

                                if (!empty($combined_arr)) {
                                    foreach ($combined_arr as $qb_ids) {
                                        if (!empty($qb_ids['qb_customer_id'])) {
                                            $qb_cust_id_str .= "'" . $qb_ids['qb_customer_id'] . "',";
                                            $qb_cust_id_arr[] = $qb_ids['qb_customer_id'];
                                        }
                                    }

                                    $qb_cust_id_str = rtrim($qb_cust_id_str, ",");
                                    $allInvoices = $dataService->Query("SELECT * FROM Invoice where CustomerRef IN($qb_cust_id_str) ORDER BY Id ASC MAXRESULTS $total_invoices");
                                    $allPayments = $dataService->Query("SELECT * FROM Payment where CustomerRef IN($qb_cust_id_str) MAXRESULTS $total_payments");
                                }
                            }
                        } else {
                            $qb_id_from_us = Yii::$app->db->createCommand("SELECT * FROM assist_participant WHERE sales_user_id IN (SELECT assist_user_meta.uid FROM assist_users join assist_user_meta ON assist_users.id = assist_user_meta.uid WHERE assist_users.department = 1 and assist_user_meta.meta_key = 'team' and assist_user_meta.meta_value = '' )")->queryAll();

                            $team_model_val = Yii::$app->db->createCommand("SELECT qb_customer_id FROM assist_participant WHERE sales_user_id IN (SELECT assist_user_meta.uid FROM assist_users join assist_user_meta ON assist_users.id = assist_user_meta.uid WHERE assist_user_meta.meta_key = 'team' and assist_user_meta.meta_value IN(SELECT assist_user_meta.uid FROM assist_users join assist_user_meta ON assist_users.id = assist_user_meta.uid WHERE assist_users.department = 1 and assist_user_meta.meta_key = 'team' and assist_user_meta.meta_value = '' ) )")->queryAll();

                            $combined_arr = array_merge($qb_id_from_us, $team_model_val);

                            $qb_cust_id_str = "";

                            if (!empty($combined_arr)) {
                                foreach ($combined_arr as $qb_ids) {
                                    if (!empty($qb_ids['qb_customer_id'])) {
                                        $qb_cust_id_str .= "'" . $qb_ids['qb_customer_id'] . "',";
                                        $qb_cust_id_arr[] = $qb_ids['qb_customer_id'];
                                    }
                                }

                                $qb_cust_id_str = rtrim($qb_cust_id_str, ",");
                                $total_invoices = $dataService->Query("SELECT count(*) FROM Invoice");
                                $allInvoices = $dataService->Query("SELECT * FROM Invoice where CustomerRef IN($qb_cust_id_str) ORDER BY Id ASC MAXRESULTS $total_invoices");
                                $total_payments = $dataService->Query("SELECT count(*) FROM Payment");
                                $allPayments = $dataService->Query("SELECT * FROM Payment where CustomerRef IN($qb_cust_id_str) MAXRESULTS $total_payments");
                            }
                        }
                    } else if ($user_role == 6) { /* If current user is not super admin */
                        $qb_id_from_us = DvRegistration::find()->select('qb_customer_id')->Where(['sales_user_id' => $data['sales_user_id']])->all();
                        $by_team_arr['sales_user_id'] = $data['sales_user_id'];

                        $qb_cust_id_str = "";

                        if (!empty($qb_id_from_us)) {
                            foreach ($qb_id_from_us as $qb_ids) {
                                if (!empty($qb_ids->qb_customer_id)) {
                                    $qb_cust_id_str .= "'" . $qb_ids->qb_customer_id . "',";
                                    $qb_cust_id_arr[] = $qb_ids->qb_customer_id;
                                }
                            }

                            $qb_cust_id_str = rtrim($qb_cust_id_str, ",");
                            $allInvoices = $dataService->Query("SELECT * FROM Invoice WHERE CustomerRef IN($qb_cust_id_str) ORDER BY Id ASC MAXRESULTS $total_invoices");
                            $allPayments = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($qb_cust_id_str) MAXRESULTS $total_payments");
                        }
                    } else if ($user_role != 1) { /* If current user is not super admin */
                        $qb_id_from_us = DvRegistration::find()->select('qb_customer_id')->Where(['sales_user_id' => $user->id])->all();
                        $qb_cust_id_str = "";

                        if (!empty($qb_id_from_us)) {
                            foreach ($qb_id_from_us as $qb_ids) {
                                if (!empty($qb_ids->qb_customer_id)) {
                                    $qb_cust_id_str .= "'" . $qb_ids->qb_customer_id . "',";
                                    $qb_cust_id_arr[] = $qb_ids->qb_customer_id;
                                }
                            }
                            $qb_cust_id_str = rtrim($qb_cust_id_str, ",");
                            $allInvoices = $dataService->Query("SELECT * FROM Invoice WHERE CustomerRef IN($qb_cust_id_str) ORDER BY Id ASC MAXRESULTS $total_invoices");
                            $allPayments = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($qb_cust_id_str) MAXRESULTS $total_payments");
                        }
                    } else { /* If current user is super admin */
                        $allInvoices = $dataService->Query("SELECT * FROM Invoice ORDER BY Id ASC MAXRESULTS $total_invoices");
                        $allPayments = $dataService->Query("SELECT * FROM Payment MAXRESULTS $total_payments");
                    }
                } else {
                    if ($user_role == 7) { /* If current user is not super admin */
                        $qb_id_from_us = Yii::$app->db->createCommand("SELECT * FROM assist_participant WHERE sales_user_id IN (SELECT assist_user_meta.uid FROM assist_users join assist_user_meta ON assist_users.id = assist_user_meta.uid WHERE assist_users.department = 1 and assist_user_meta.meta_key = 'team' and assist_user_meta.meta_value = '' )")->queryAll();

                        $qb_cust_id_str = "";

                        if (!empty($qb_id_from_us)) {
                            foreach ($qb_id_from_us as $qb_ids) {
                                if (!empty($qb_ids['qb_customer_id'])) {
                                    $qb_cust_id_str .= "'" . $qb_ids['qb_customer_id'] . "',";
                                    $qb_cust_id_arr[] = $qb_ids['qb_customer_id'];
                                }
                            }
                            $qb_cust_id_str = rtrim($qb_cust_id_str, ",");
                            $allInvoices = $dataService->Query("SELECT * FROM Invoice where CustomerRef IN($qb_cust_id_str) ORDER BY Id ASC MAXRESULTS $total_invoices");
                            $allPayments = $dataService->Query("SELECT * FROM Payment where CustomerRef IN($qb_cust_id_str) MAXRESULTS $total_payments");
                        }
                    } else {
                        $allInvoices = $dataService->Query("SELECT * FROM Invoice ORDER BY Id ASC MAXRESULTS $total_invoices");
                        $allPayments = $dataService->Query("SELECT * FROM Payment MAXRESULTS $total_payments");
                    }
                }

                $qb_cust_id_arr = array();
                if (!empty($allInvoices)) {
                    $all_invoice_of_customer = $allInvoices;

                    foreach ($allInvoices as $key => $val) {
                        $timestamp_due_date = strtotime($val->DueDate);
                        $month_year_arr[date('m', $timestamp_due_date) . "_" . date('Y', $timestamp_due_date)] = date('M', $timestamp_due_date) . " " . date('Y', $timestamp_due_date);
                        if ($timestamp_due_date >= $timestamp_of_first_date_of_month && $timestamp_due_date <= $timestamp_of_last_date_of_month) {
                            if ($val->Balance > 0) {
                                $qb_cust_id_arr[] = $val->CustomerRef;
                            }
                        } else {
                            unset($allInvoices[$key]);
                        }
                    }
                }

                if ($data['sdate'] != "" && $data['by_date_month'] == 'd') {
                    $query = DvRegistration::find()
                            ->where($filters)
                            ->orderBy(['id' => SORT_DESC])
                            ->andWhere($filter_data)
                            ->andWhere(['IN', 'qb_customer_id', $qb_cust_id_arr]);
                } elseif ($data['bymonth'] != '' && $data['by_date_month'] == 'm') {
                    $query = DvRegistration::find()->where($filters)
                            ->orderBy(['id' => SORT_DESC])
                            ->andWhere($filter_data)
                            ->andWhere(['IN', 'qb_customer_id', $qb_cust_id_arr]);
                } else {
                    $query = DvRegistration::find()->where($filters)
                            ->orderBy(['assist_participant.id' => SORT_DESC])
                            ->andWhere($filter_data)
                            ->andWhere(['IN', 'qb_customer_id', $qb_cust_id_arr]);
                }

                $query->andWhere(['>', 'qb_customer_id', 0]);
                $query->groupBy(['email']);
                $total_model = $query->all();
                $count = $query->count();
                $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 20]);
                $models = $query->offset($pagination->offset)->limit($pagination->limit)->all();
                $offset = $pagination->offset + 1;

                if ($data['bymonth'] != '' && $data['by_date_month'] == 'm') {
                    $filter_data['bymonth'] = $data['bymonth'];
                }
                if ($data['by_date_month'] != '') {
                    $filter_data['by_date_month'] = $data['by_date_month'];
                }

                if ($data['sdate'] != '' && $data['by_date_month'] == 'd') {
                    $filter_data['sdate'] = $data['sdate'];
                    $filter_data['edate'] = $data['edate'];
                }

                if ($data['email'] != "") {
                    $filter_data['email'] = $data['email'];
                }

                if (isset($data['sales_user_id']) && $data['sales_user_id'] != "") {
                    $filter_data['sales_user_id'] = $data['sales_user_id'];
                }

                $month_year_arr = array_unique($month_year_arr);


                $invoice_number = "";
                $total_of_all_currencys = array();
                $invoice_balance = 0;

                foreach ($total_model as $value) {
                    $total_invoice_amt = 0;
                    $cnt_invoice = 0;
                    if (!empty($all_invoice_of_customer)) {
                        foreach ($all_invoice_of_customer as $invoice) {
                            if ($value->qb_customer_id == $invoice->CustomerRef) {
                                $timestamp_due_date = strtotime($invoice->DueDate);
                                if ($timestamp_due_date >= $timestamp_of_first_date_of_month && $timestamp_due_date <= $timestamp_of_last_date_of_month) {
                                    $total_invoice_amt += $invoice->TotalAmt;
                                    $invoice_number .= $invoice->DocNumber . ", ";
                                }
                            }
                        }
                    }

                    $invoice_number = rtrim($invoice_number, ", ");
                    if (!empty($allInvoices)) {
                        foreach ($allInvoices as $invoice) {
                            if ($value->qb_customer_id == $invoice->CustomerRef) {
                                $currency_ref = $invoice->CurrencyRef;
                                $invoice_balance += $invoice->Balance;

                                if (array_key_exists($currency_ref, $total_of_all_currencys)) {
                                    $total_of_all_currencys[$currency_ref] = $total_of_all_currencys[$currency_ref] + $invoice->Balance;
                                } else {
                                    $total_of_all_currencys[$currency_ref] = $invoice->Balance;
                                }
                            }
                        } // End for (allInvoices)
                    } // End if (allInvoices)
                } // End main for loop

                /* echo "<pre>";
                  print_r($models);
                  die; */
                return $this->render('pending_revenue', [
                            'model' => $models,
                            'pagination' => $pagination,
                            'total_of_all_currencys' => $total_of_all_currencys,
                            'count' => $offset,
                            'total_count' => $count,
                            'filter_data' => $filter_data,
                            'by_team_arr' => $by_team_arr,
                            'allInvoices' => $allInvoices,
                            'all_invoice_of_customer' => $all_invoice_of_customer,
                            'allPayments' => $allPayments,
                            'month_year_arr' => $month_year_arr,
                            'last_date_of_current_month' => $last_date_of_current_month,
                            'first_date_of_current_month' => $first_date_of_current_month,
                            'courses_from_qb' => $courses_from_qb
                ]);
            }
        } else {
            $usermeta_result = DvUserMeta::find()->where(['uid' => $user->id, 'meta_key' => 'role'])->one();
            $user_role = $usermeta_result->meta_value;
            if ($user_role == 1) {
                $allInvoices = $dataService->Query("SELECT * FROM Invoice MAXRESULTS $total_invoices");
                $allPayments = $dataService->Query("SELECT * FROM Payment MAXRESULTS $total_payments");
                $qb_cust_id_arr = array();
                if (!empty($allInvoices)) {
                    $all_invoice_of_customer = $allInvoices;

                    foreach ($allInvoices as $key => $val) {
                        $timestamp_due_date = strtotime($val->DueDate);
                        if ($timestamp_due_date >= $timestamp_of_first_date_of_month && $timestamp_due_date <= $timestamp_of_last_date_of_month) {
                            if ($val->Balance > 0) {
                                $qb_cust_id_arr[] = $val->CustomerRef;
                            }
                        } else {
                            unset($allInvoices[$key]);
                        }
                    }
                }
                $query = DvRegistration::find()->where(['IN', 'qb_customer_id', $qb_cust_id_arr]);
            } else if ($user_role == 6) {
                $sales_heads = Yii::$app->db->createCommand("SELECT assist_users.id FROM assist_users INNER JOIN assist_user_meta ON assist_users.id = assist_user_meta.uid WHERE assist_users.status = 1 AND assist_users.department = 1 AND assist_user_meta.meta_key = 'team' AND assist_user_meta.meta_value=$user->id")->queryAll();
                $sales_heads_str = "";
                if (!empty($sales_heads)) {
                    foreach ($sales_heads as $m_sales_head) {
                        $sales_heads_str .= $m_sales_head['id'] . ",";
                    }
                }
                $sales_heads_str .= $user->id;
                $sales_heads_str = rtrim($sales_heads_str, ",");

                $qb_id_from_us = Yii::$app->db->createCommand("SELECT qb_customer_id FROM assist_participant WHERE sales_user_id IN ($sales_heads_str )")->queryAll();

                $qb_cust_id_str = "";
                if (!empty($qb_id_from_us)) {
                    foreach ($qb_id_from_us as $qb_ids) {
                        if (!empty($qb_ids['qb_customer_id'])) {
                            $qb_cust_id_str .= "'" . $qb_ids['qb_customer_id'] . "',";
                            $qb_cust_id_arr[] = $qb_ids['qb_customer_id'];
                        }
                    }
                    $qb_cust_id_str = rtrim($qb_cust_id_str, ",");
                    $allInvoices = $dataService->Query("SELECT * FROM Invoice where CustomerRef IN($qb_cust_id_str) ORDER BY Id ASC MAXRESULTS $total_invoices");
                    $allPayments = $dataService->Query("SELECT * FROM Payment where CustomerRef IN($qb_cust_id_str) MAXRESULTS $total_payments");
                }

                $qb_cust_id_arr = array();

                if (!empty($allInvoices)) {
                    $all_invoice_of_customer = $allInvoices;
                    foreach ($allInvoices as $key => $val) {
                        $timestamp_due_date = strtotime($val->DueDate);
                        $month_year_arr[date('m', $timestamp_due_date) . "_" . date('Y', $timestamp_due_date)] = date('M', $timestamp_due_date) . " " . date('Y', $timestamp_due_date);
                        if ($timestamp_due_date >= $timestamp_of_first_date_of_month && $timestamp_due_date <= $timestamp_of_last_date_of_month) {
                            if ($val->Balance > 0) {
                                $qb_cust_id_arr[] = $val->CustomerRef;
                            }
                        } else {
                            unset($allInvoices[$key]);
                        }
                    }
                }

                $month_year_arr = array_unique($month_year_arr);

                $query = DvRegistration::find()->where(['!=', 'qb_customer_id', ''])->andWhere(['IN', 'qb_customer_id', $qb_cust_id_arr]);
            } else if ($user_role == 7) {

                $team_model_val = Yii::$app->db->createCommand("SELECT assist_user_meta.uid FROM assist_users join assist_user_meta ON assist_users.id = assist_user_meta.uid WHERE assist_users.department = 1 and assist_user_meta.meta_key = 'team' and assist_user_meta.meta_value = '' ")->queryAll();

                $executive_model_val = Yii::$app->db->createCommand("SELECT assist_user_meta.uid FROM assist_users join assist_user_meta ON assist_users.id = assist_user_meta.uid WHERE assist_user_meta.meta_key = 'team' and assist_user_meta.meta_value IN(SELECT assist_user_meta.uid FROM assist_users join assist_user_meta ON assist_users.id = assist_user_meta.uid WHERE assist_users.department = 1 and assist_user_meta.meta_key = 'team' and assist_user_meta.meta_value = '' ) ")->queryAll();
                $team_model_val[]['uid'] = $user->id;
                $combined_sales_head_arr = array_merge($team_model_val, $executive_model_val);

                $sales_head_str = "";
                if (!empty($combined_sales_head_arr)) {
                    foreach ($combined_sales_head_arr as $m_sales_head) {
                        $sales_head_str .= $m_sales_head['uid'] . ",";
                    }
                }
                $sales_head_str = rtrim($sales_head_str, ",");
                $qb_id_from_us = Yii::$app->db->createCommand("SELECT qb_customer_id FROM assist_participant WHERE sales_user_id IN ($sales_head_str)")->queryAll();

                $combined_arr = $qb_id_from_us;

                $qb_cust_id_str = "";
                if (!empty($combined_arr)) {
                    foreach ($combined_arr as $qb_ids) {
                        if (!empty($qb_ids['qb_customer_id'])) {
                            $qb_cust_id_str .= "'" . $qb_ids['qb_customer_id'] . "',";
                            $qb_cust_id_arr[] = $qb_ids['qb_customer_id'];
                        }
                    }
                    $qb_cust_id_str = rtrim($qb_cust_id_str, ",");
                    $allInvoices = $dataService->Query("SELECT * FROM Invoice where CustomerRef IN($qb_cust_id_str) ORDER BY Id ASC MAXRESULTS $total_invoices");
                    $allPayments = $dataService->Query("SELECT * FROM Payment where CustomerRef IN($qb_cust_id_str) MAXRESULTS $total_payments");
                }
                $qb_cust_id_arr = array();

                if (!empty($allInvoices)) {
                    $all_invoice_of_customer = $allInvoices;
                    /* echo "<pre>";
                      print_r($all_invoice_of_customer);
                      die; */
                    foreach ($allInvoices as $key => $val) {
                        $timestamp_due_date = strtotime($val->DueDate);
                        $month_year_arr[date('m', $timestamp_due_date) . "_" . date('Y', $timestamp_due_date)] = date('M', $timestamp_due_date) . " " . date('Y', $timestamp_due_date);
                        if ($timestamp_due_date >= $timestamp_of_first_date_of_month && $timestamp_due_date <= $timestamp_of_last_date_of_month) {
                            if ($val->Balance > 0) {
                                $qb_cust_id_arr[] = $val->CustomerRef;
                            }
                        } else {
                            unset($allInvoices[$key]);
                        }
                    }
                }

                $month_year_arr = array_unique($month_year_arr);

                $query = DvRegistration::find()->where(['!=', 'qb_customer_id', ''])->andWhere(['IN', 'qb_customer_id', $qb_cust_id_arr]);
            } else {
                $qb_id_from_us = DvRegistration::find()->select('qb_customer_id')->Where(['sales_user_id' => $user->id])->all();
                $qb_cust_id_str = "";

                if (!empty($qb_id_from_us)) {
                    foreach ($qb_id_from_us as $qb_ids) {
                        if (!empty($qb_ids->qb_customer_id)) {
                            $qb_cust_id_str .= "'" . $qb_ids->qb_customer_id . "',";
                            $qb_cust_id_arr[] = $qb_ids->qb_customer_id;
                        }
                    }
                    $qb_cust_id_str = rtrim($qb_cust_id_str, ",");
                    $allInvoices = $dataService->Query("SELECT * FROM Invoice where CustomerRef IN($qb_cust_id_str) ORDER BY Id ASC MAXRESULTS $total_invoices");
                    $allPayments = $dataService->Query("SELECT * FROM Payment where CustomerRef IN($qb_cust_id_str) MAXRESULTS $total_payments");
                }
                $qb_cust_id_arr = array();

                if (!empty($allInvoices)) {
                    $all_invoice_of_customer = $allInvoices;
                    foreach ($allInvoices as $key => $val) {
                        $timestamp_due_date = strtotime($val->DueDate);
                        $month_year_arr[date('m', $timestamp_due_date) . "_" . date('Y', $timestamp_due_date)] = date('M', $timestamp_due_date) . " " . date('Y', $timestamp_due_date);
                        if ($timestamp_due_date >= $timestamp_of_first_date_of_month && $timestamp_due_date <= $timestamp_of_last_date_of_month) {
                            if ($val->Balance > 0) {
                                $qb_cust_id_arr[] = $val->CustomerRef;
                            }
                        } else {
                            unset($allInvoices[$key]);
                        }
                    }
                }

                $month_year_arr = array_unique($month_year_arr);

                $query = DvRegistration::find()->where(['sales_user_id' => $user->id])->andWhere(['!=', 'qb_customer_id', ''])->andWhere(['IN', 'qb_customer_id', $qb_cust_id_arr]);
            }
            $query->groupBy(['email']);
            $query->orderBy(['id' => SORT_DESC]);
            $total_model = $query->all();
            $count = $query->count();
            $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 10]);
            $models = $query->offset($pagination->offset)->limit($pagination->limit)->all();
            $offset = $pagination->offset + 1;

            // echo "in"; die;
            // Start Get Total of different currency 
            $invoice_number = "";
            $total_of_all_currencys = array();

            $invoice_balance = 0;

            foreach ($total_model as $value) {
                $total_invoice_amt = 0;
                $cnt_invoice = 0;
                if (!empty($all_invoice_of_customer)) {
                    foreach ($all_invoice_of_customer as $invoice) {
                        if ($value->qb_customer_id == $invoice->CustomerRef) {
                            $timestamp_due_date = strtotime($invoice->DueDate);
                            if ($timestamp_due_date >= $timestamp_of_first_date_of_month && $timestamp_due_date <= $timestamp_of_last_date_of_month) {
                                $total_invoice_amt += $invoice->TotalAmt;
                                $invoice_number .= $invoice->DocNumber . ", ";
                            }
                        }
                    }
                }


                $invoice_number = rtrim($invoice_number, ", ");
                if (!empty($allInvoices)) {
                    foreach ($allInvoices as $invoice) {
                        if ($value->qb_customer_id == $invoice->CustomerRef) {
                            $currency_ref = $invoice->CurrencyRef;
                            $invoice_balance += $invoice->Balance;

                            if (array_key_exists($currency_ref, $total_of_all_currencys)) {
                                $total_of_all_currencys[$currency_ref] = $total_of_all_currencys[$currency_ref] + $invoice->Balance;
                            } else {
                                $total_of_all_currencys[$currency_ref] = $invoice->Balance;
                            }
                        }
                    } // End for (allInvoices)
                } // End if (allInvoices)
            } // End main for loop


            return $this->render('pending_revenue', [
                        'model' => $models,
                        'total_of_all_currencys' => $total_of_all_currencys,
                        'pagination' => $pagination,
                        'count' => $offset,
                        'total_count' => $count,
                        'allInvoices' => $allInvoices,
                        'courses_from_qb' => $courses_from_qb,
                        'all_invoice_of_customer' => $all_invoice_of_customer,
                        'allPayments' => $allPayments,
                        'month_year_arr' => $month_year_arr,
                        'last_date_of_current_month' => $last_date_of_current_month,
                        'first_date_of_current_month' => $first_date_of_current_month
            ]);
        }
    }

    /**
     * * @ Cliam for refund mony request set new memo on QuickBook. 
     * */
    public function actionCliam_refund() {
        $digits = 6;
        $random_doc_number = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
        $dataService = $this->quickbook_instance();
        $reason_for_refund = "";

        $data = Yii::$app->request->post();

        $participant_id = $data['participant_id'];
        $refund_amount = $data['refund_amount'];
        $reason_for_refund = $data['reason_for_refund'];

        $query = DvRegistration::find()->where(['id' => $participant_id])->one();

        $qb_customer_id = $query->qb_customer_id;

        $CreditMemoToCreate = CreditMemo::create([
                    "DocNumber" => "RF-" . $random_doc_number,
                    "TxnDate" => date("Y-m-d"), /* Generated Date */ 
                    "Line" => [
                        [
                            "Description" => $reason_for_refund,
                            "Amount" => $refund_amount,
                            "DetailType" => "SalesItemLineDetail",
                            "SalesItemLineDetail" =>
                            [
                                "ItemRef" =>
                                [
                                    "value" => "14"/* Product/Service Id(From Item table) */
                                ],
                                "TaxCodeRef" => [
                                    "value" => 48
                                ]
                            ]
                        ]],
                    "CustomerRef" =>
                    [
                        "value" => $qb_customer_id, /* Customer Id */
                    ]
        ]);

        $resultObj = $dataService->Add($CreditMemoToCreate);
        $error = $dataService->getLastError();
        if ($error) {
            $credetmeno = array('credit_memo' => 0);
            Yii::$app->session->set('credetmeno_ses', $credetmeno);
            return $this->redirect(['dv-registration/view?id=' . $participant_id]);
        } else {
            $credetmeno = array('credit_memo' => $resultObj->Id);
            $data = Yii::$app->request->post();
            Yii::$app->session->set('credetmeno_ses', $credetmeno);
            return $this->redirect(['dv-registration/view?id=' . $participant_id]);
        }
    }

    public function actionInvoice_payment() {
        $data = Yii::$app->request->post();
//echo "<pre>";print_r($data);die;
        $dataService = $this->quickbook_instance();

        $participant_data = DvRegistration::find()->where(['id' => $data['participant_id']])->one();

        if (isset($data['participant_id']) && $data['participant_id'] != '') {
            $participant_id = $data['participant_id'];
            //echo "test".; die;
            $amount_recieved = $data['DvParticipantPayments']['amount_recieved'];
            $payment_reference_number = $data['DvParticipantPayments']['payment_reference_number'];
            //$payment_currency = $data['DvParticipantPayments']['payment_currency'];
            $payment_mode = $data['DvParticipantPayments']['payment_mode'];

            $qb_participant_id = "";
            $participant_data = DvRegistration::find()->where(['id' => $data['participant_id']])->one();

            if (!empty($participant_data)) {
                $qb_participant_id = $participant_data->qb_customer_id;
            }

            if (!empty($qb_participant_id)) {
                $InvoicePaymentToCreate = Payment::create([
                            "CustomerRef" => [
                                "value" => $qb_participant_id/* Customer Id from Quickbook */
                            ],
                            "PaymentMethodRef" => [
                                /*
                                 * Default Payment Methods are as below from PaymentMethod table
                                 * 1: Cash, 
                                 * 2: Check, 
                                 * 3: Visa, 
                                 * 4: MasterCard, 
                                 * 5: Discover, 
                                 * 6: American Express, 
                                 * 7: Diners Club */
                                "value" => $payment_mode
                            ],
                            "PaymentRefNum" => "PY-" . $payment_reference_number,
                            "TotalAmt" => $amount_recieved, /* Total amount of Invoice */
                            "UnappliedAmt" => $amount_recieved,
                            "TxnDate" => date('Y-m-d', strtotime($data['payment_date']))
                ]);
                $resultObj = $dataService->Add($InvoicePaymentToCreate);
                $error = $dataService->getLastError();
                $status = 0;
                if ($error) {
                    $status = 0;
                } else {
                    $status = 1;
                }
            }
        }
        if ($status == 1) {
            Yii::$app->session->setFlash('success', 'Payment Request Generated Successfully.');
            return $this->redirect(['dv-registration/view?id=' . $participant_id]);
        } else {
            Yii::$app->session->setFlash('error', 'Something went wrong! Please try again later.');
            return $this->redirect(['dv-registration/view?id=' . $participant_id]);
        }
    }

    /**
     * * @Get all  for perticular user
     * */
    public function actionGet_coursenamebyid() {

        $data = Yii::$app->request->post();

        $id = $data['course_id'];
        $dd_course = DvCourse::find()->where(['status' => 1, 'id' => $id])->one();

        echo $dd_course->name;
        exit;
    }

    /**
     * * @Get Refresh token id
     * */
    public function actionGet_refreshtoken() {
        $length = 10;
        $characters = 'abcdefghijklmnopqrstuvwxyz123456789';
        $charactersLength = strlen($characters);
        $randomString = 'DV-';

        $user = Yii::$app->user->identity;

        $sales_name = DvUsers::find()->select('first_name')->where(['id' => $user->id, 'status' => 1])->one();

        $result = array();
        $result['sales_username'] = $sales_name->first_name;

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $query = DvRegistration::find()->where(['token_id' => $randomString])->one();

        if ($query) {
            echo $this->actionGet_refreshtoken();
        } else {
            $result['randomString'] = $randomString;
            echo json_encode($result);
            exit;
        }
    }

    /**
     * * @Get all  for perticular user
     * */
    public function actionGet_city_country() {
        // die("hi");
        $data = Yii::$app->request->post();

        $city = $data['city'];
        $country = $data['country'];

        $city = DvCities::find()->where(['id' => $city])->one()->name;
        $country = DvCountry::find()->where(['id' => $country])->one()->name;

        /* echo $city."<br>";
          echo $country;
          die; */


        $result_users['result'] = '1';
        $result_users['city'] = $city;
        $result_users['country'] = $country;

        echo json_encode($result_users);
        exit;
    }

    public function actionGet_batch_of_course() {
        $data = Yii::$app->request->post();
        $course_id = $data['course_id'];
        $get_core_modules = Yii::$app->db->createCommand("select core_modules from assist_course where id=".$course_id)->queryAll();
        $option_string = "";
        if(!empty($get_core_modules)) {
            $core_modules = $get_core_modules[0]['core_modules'];
            $core_modules_arr = explode(",", $core_modules);

            if (!empty($core_modules_arr)) {
                foreach ($core_modules_arr as $key => $value) {
                    if(!empty($value)) {

                    $get_participant_modules = Yii::$app->db->createCommand("SELECT * FROM assist_batches where module=$value AND UNIX_TIMESTAMP(STR_TO_DATE(start_date,'%d-%m-%Y')) >".strtotime(date('d-m-Y')))->queryAll();

                    if(!empty($get_participant_modules)) {
                        foreach ($get_participant_modules as $participant_module) {

                            $trainer = DvUsers::find()->where(['id'=>$participant_module['trainer'], "status" => 1])->all();
                            $Dv_trainer = ArrayHelper::map($trainer, 'id', 'first_name');
                            $trainer_person = array_values($Dv_trainer);
                            if(empty($trainer_person[0])){
                                $trainer_person = '----';
                            } else{
                                $trainer_person = $trainer_person[0];
                            }

                            $option_string .= "<option value='".$participant_module['id']."'>";
                            $option_string .= "Date: ".date("d-M-Y", strtotime($participant_module['start_date']));
                            $option_string .= ", Time: ".$participant_module['stiming'];
                            $option_string .= " (EM - ".$participant_module['day'].")";
                            $option_string .= " [Trainer - ".$trainer_person."]";
                            $option_string .= "</option>";
                        }
                    }
                }
                }
            }
        }
        echo $option_string;
    }

    //CDO:03 June 2019 Purpose:Getting course name based on DA & DM
    public function actionCourse_domain_check(){
        $domian = Yii::$app->request->post('course_domain');
        if($domian != ''){
            $course = DvCourse::find()->where(['status'=>1,'mcourse'=>$domian])->all();
            $Dv_course = ArrayHelper::map($course, 'id', 'name');
            $output = '';
            $output .= '<select id="course" class="form-control" name="DvRegistration[course]" title="Select Course"   data-toggle="tooltip" data-placement="top" required="required" aria-required="true" data-original-title="Select Course" aria-invalid="false">';
            $output .= '<option value="">Select Course</option>';
                foreach ($Dv_course as $key => $value) {
                    $output .= "<option value='".$key."'>".$value."</option>";
                }
            $output .= '</select>';
            return $output;
        }
    }//End of function:actionCourse_domain_check//

}