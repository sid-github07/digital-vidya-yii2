<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use app\models\DvUserMeta;
use app\models\DvUsers;
use app\models\DvCourseTarget;
use app\models\DvGst;
use app\models\DvCourse;
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

class DvReportsController extends Controller {

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
     * * @ Get executive of a team
     * */
    public function actionGet_executive() {

        $teamID = $_POST['teamID'];
        $usermeta_result = DvUserMeta::find()->where(['meta_key' => 'team', 'meta_value' => $teamID])->all();
        if ($usermeta_result) {
            echo "<div class='form-group col-md-3 cls_byExecutive'>
                <select class='form-control' name='sales_user_id' id='byExecutive'>
                  <option value=''>Select Consultant</option>";
            foreach ($usermeta_result as $val) {
                $usermeta_result = DvUsers::find()->where(["id" => $val['uid'], "status" => 1])->one();
                echo "<option value='" . $val['uid'] . "'>" . $usermeta_result->first_name . ' ' . $usermeta_result->last_name . "</option>";
            }
            echo "</select></div>";
        } else {
            echo "<div class='form-group col-md-3 cls_byExecutive'><select class='form-control col-md-3' name='sales_user_id' id='byExecutive'>
                  <option value=''>Select Consultant</option>";
            echo "</select></div>";
        }
    }

    /* Created By Hetal J - 25th Feb,2019
     * Calculate Sales Report
     */

    public function actionSales_report() {
        /* redirect a user if not super admin */
        if (!Yii::$app->CustomComponents->check_permission('sales_report')) {
            return $this->redirect(['site/index']);
        }


        $logged_in_user_id = Yii::$app->user->identity->id;
        $executives = $logged_in_user_id;
        $current_user_role = DvUserMeta::find()->select(["meta_value"])->where(['uid' => $logged_in_user_id, "meta_key" => "role"])->one();
        $current_user_role = $current_user_role['meta_value'];

        $Dv_course = array();
        $month = date('m');
        $year = date('Y');
        $all_executive = array();
        $sales_managers = array();
        $filtered_data = array();
        $filtered_data['month'] = $month;
        $filtered_data['year'] = $year;
        $filtered_data['select_period'] = "monthly";
        $filtered_data['quarterly_month'] = array();
        $filtered_data['quarterly_year'] = "";
        $filtered_data['executive_of_manager'] = array();
        $filtered_data['selected_course'] = "";
        $filtered_data['selected_domain'] = "";
        $filtered_data['selected_manager'] = "";

        $data = Yii::$app->request->post();
        $get_all_executive_of_manager = array();
        $date_before_1_year = date('Y-m-d', strtotime(date('Y-m-d') . ' -1 year'));
        $today = date('Y-m-d');
        $datestring = "$today last day of last month";
        $dt = date_create($datestring);
        $last_date_of_last_month = $dt->format('Y-m-d');
        $date_before_1_year_of_month = date('Y-m-d', strtotime($last_date_of_last_month . ' -1 year'));
        $first_date_of_month = date('Y-m-1');

        $all_courses = DvCourse::find()->all();

        $course_sold_last_year_by_current_user = Yii::$app->db->createCommand("SELECT course, count(*) as total_sold, ac.name 
            FROM assist_participant ap JOIN assist_course ac ON ac.id = ap.course 
                WHERE created_on BETWEEN DATE('$date_before_1_year') AND DATE('$today') AND sales_user_id = $logged_in_user_id
                GROUP BY course")->queryAll();

        $total_course_sold_last_year = Yii::$app->db->createCommand("SELECT course, count(*) as total_sold, ac.name FROM assist_participant ap 
                JOIN assist_course ac ON ac.id = ap.course 
                WHERE created_on BETWEEN DATE('$date_before_1_year') AND DATE('$today') 
                GROUP BY course")->queryAll();

        $all_course_except_this_period = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, 
                        count('ap.*') as sold_course, ap.sales_user_id FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                        WHERE DATE(ap.created_on) < '$first_date_of_month' 
                            AND (DATE(ap.created_on) BETWEEN '$date_before_1_year_of_month' AND '$last_date_of_last_month')
                            GROUP by ap.course,ap.sales_user_id")->queryAll();

        $sales_user_created_on = Yii::$app->db->createCommand("SELECT id,created FROM assist_users WHERE status = 1 ")->queryAll();
        if (!empty($sales_user_created_on)) {
            foreach ($sales_user_created_on as $key => $sales_user) {
                $date1 = $sales_user['created'];
                $date2 = $last_date_of_last_month;

                $ts1 = strtotime($date1);
                $ts2 = strtotime($date2);

                $year1 = date('Y', $ts1);
                $year2 = date('Y', $ts2);

                $month1 = date('m', $ts1);
                $month2 = date('m', $ts2);

                $diff = (($year2 - $year1) * 12) + ($month2 - $month1);
                $sales_user_created_on[$key]['created_before_month'] = $diff;
            }
        }

        $all_course_in_this_period = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, 
                        count('ap.*') as sold_course FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                        WHERE MONTH(ap.created_on)=$month AND YEAR(ap.created_on) = $year 
                            GROUP by ap.course")->queryAll();

        /* Total users of Sales department except Sales Head */
        $count_sales_users = Yii::$app->db->createCommand("SELECT count(assist_users.id) as total_sales_users FROM assist_users 
            join assist_user_meta ON assist_user_meta.uid = assist_users.id 
            WHERE (department=1) AND (status=1) AND (meta_key='role') AND (meta_value <> 7) AND assist_users.status = 1 ")->queryAll();

        $sales_managers = Yii::$app->db->createCommand("SELECT au.id, au.first_name, au.last_name FROM assist_users au JOIN  assist_user_meta aum ON aum.uid = au.id WHERE aum.meta_key='role' AND aum.meta_value=6 AND au.department=1 AND au.status = 1 ")->queryAll();
        $manager_str = "";
        if (!empty($sales_managers)) {
            foreach ($sales_managers as $manager) {
                $manager_str .= $manager['id'] . ",";
            }
        }

        $manager_str = rtrim($manager_str, ",");

        $get_all_executive_of_manager = Yii::$app->db->createCommand("SELECT au.id, au.first_name, au.last_name, aut.meta_value as team  
            FROM assist_users au JOIN  assist_user_meta aum ON aum.uid = au.id LEFT JOIN  assist_user_meta aut ON aut.meta_key = 'team' and 
            aut.uid = au.id WHERE aum.meta_key='team' AND aum.meta_value IN ($manager_str) AND au.department=1 AND au.status = 1 ")->queryAll();

        if (!empty($sales_managers)) {
            foreach ($sales_managers as $manager) {
                $get_all_executive_of_manager[] = $manager;
            }
        }
        $get_all_manager = DvUsers::find()->select(["assist_users.id", "first_name", "last_name"])
                ->join('join', 'assist_user_meta', 'assist_user_meta.uid = assist_users.id')
                ->where(["meta_key" => "role", "department" => 1, "assist_users.status" => 1])
                ->andWhere(["IN", "meta_value", array(6)])
                ->all();

        if (!empty($get_all_executive_of_manager)) {


            foreach ($get_all_executive_of_manager as $executive) {
                $executive_id = $executive['id'];

                $manager_id = '';
                if (!isset($executive['team'])) {
                    $manager_id = $executive_id;
                } else {
                    $manager_id = $executive['team'];
                }
                $manager = DvUsers::find()->where(['id' => $manager_id, "status" => 1])->one();
                $all_executive[$executive_id]['manager_name'] = $manager->first_name . " " . $manager->last_name;

                $all_executive[$executive_id]['name'] = $executive['first_name'] . " " . $executive['last_name'];

                $all_executive[$executive_id]['course_details'] = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, 
                        count('ap.*') as sold_course FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                        WHERE ap.sales_user_id = $executive_id  AND MONTH(ap.created_on)=$month AND YEAR(ap.created_on) = $year 
                            GROUP by ap.course")->queryAll();

                $all_executive[$executive_id]['user_wise_sold_course'] = Yii::$app->db->createCommand("SELECT course, count(*) as 
                        total_sold, ac.name ,ac.mcourse FROM assist_participant ap JOIN assist_course ac ON ac.id = ap.course 
                        WHERE created_on BETWEEN DATE('$date_before_1_year') AND DATE('$today') AND sales_user_id = $executive_id 
                            GROUP BY course")->queryAll();
            }
        }

        $all_course_details = array();


        if (!empty($data) && !isset($data['export_type'])) {

            if (!empty($data['executives'])) {
                $executives = $data['executives'];
            } else {
                $executives = $logged_in_user_id;
            }

            $filtered_data['select_period'] = $data['select_period'];
            if ($data['select_period'] == "monthly") {
                if (!empty($data['month'])) {
                    $month = $data['month'];
                    $filtered_data['month'] = $month;
                }
                if (!empty($data['year'])) {
                    $year = $data['year'];
                    $filtered_data['year'] = $year;
                }
                $domain = $data['domain'];
                $filtered_data['selected_domain'] = $domain;

                $fil_domain = '';
                if ($domain) {
                    $fil_domain = "ac.mcourse ='" . $domain . "' AND";
                }

                $Dv_course = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, count('ap.*') as sold_course 
                    FROM assist_participant ap 
                    JOIN assist_course ac on ac.id= ap.course 
                    WHERE $fil_domain ap.sales_user_id = $executives  AND MONTH(ap.created_on)=$month AND YEAR(ap.created_on) = $year 
                        GROUP by ap.course")->queryAll();

                /* Total users of Sales department except Sales Head */
                $count_sales_users = Yii::$app->db->createCommand("SELECT count(assist_users.id) as total_sales_users FROM assist_users 
                    join assist_user_meta ON assist_user_meta.uid = assist_users.id 
                    WHERE (department=1) AND (status=1) AND (meta_key='role') AND (meta_value <> 7) AND assist_users.status = 1 ")->queryAll();

                $all_course_in_this_period = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, 
                        count('ap.*') as sold_course FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                        WHERE MONTH(ap.created_on)=$month AND YEAR(ap.created_on) = $year 
                            GROUP by ap.course")->queryAll();

                $date_before_1_year = date('Y-m-d', strtotime(date('Y-m-d') . ' -1 year'));
                $selected_date = date("$year-$month-1");
                $datestring = "$selected_date last day of last month";
                $dt = date_create($datestring);
                $last_date_of_last_month = $dt->format('Y-m-d');
                $date_before_1_year_of_month = date('Y-m-d', strtotime($last_date_of_last_month . ' -1 year'));
                $first_date_of_month = date("$year-$month-1");

                $all_course_except_this_period = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, 
                        count('ap.*') as sold_course, ap.sales_user_id FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                        WHERE DATE(ap.created_on) < '$first_date_of_month' 
                            AND (DATE(ap.created_on) BETWEEN '$date_before_1_year_of_month' AND '$last_date_of_last_month')
                            GROUP by ap.course,ap.sales_user_id")->queryAll();

                $product = $data['products'];
                $managers = $data['managers'];
                $executives = $data['executives'];
                $domain = $data['domain'];
                $filtered_data['selected_course'] = $product;
                $filtered_data['selected_domain'] = $domain;
                $filtered_data['selected_manager'] = $managers;
                $filtered_data['executives'] = $executives;

                $fil_product = '';
                if ($product) {
                    $fil_product = "ap.course =" . $product . " AND";
                }

                $fil_domain = '';
                if ($domain) {
                    $fil_domain = "ac.mcourse ='" . $domain . "' AND";
                }


                $all_executive = array();
                if (!empty($managers)) {

                    $manager_info = DvUsers::find()->select(["assist_users.id", "first_name", "last_name"])
                            ->where(["id" => $managers, "status" => 1])
                            ->one();
                    $executive_of_manager = Yii::$app->db->createCommand("SELECT au.id, au.first_name, au.last_name, aut.meta_value as team
                            FROM assist_users au 
                            JOIN assist_user_meta aum on aum.uid= au.id 
                            LEFT JOIN  assist_user_meta aut ON aut.meta_key = 'team' and aut.uid = au.id
                            WHERE aum.meta_key = 'team' AND aum.meta_value=$managers AND au.status = 1 ")->queryAll();
                    $filtered_data['executive_of_manager'] = $executive_of_manager;
                }
                if (empty($managers)) {

                    $sales_managers = Yii::$app->db->createCommand("SELECT au.id, au.first_name, au.last_name FROM assist_users au 
                        JOIN  assist_user_meta aum ON aum.uid = au.id 
                        WHERE aum.meta_key='role' AND aum.meta_value=6 AND au.department=1 AND au.status = 1 ")->queryAll();
                    $manager_str = "";
                    if (!empty($sales_managers)) {
                        foreach ($sales_managers as $manager) {
                            $manager_str .= $manager['id'] . ",";
                        }
                    }

                    $manager_str = rtrim($manager_str, ",");

                    $get_all_executive_of_manager = Yii::$app->db->createCommand("SELECT au.id, au.first_name, au.last_name, 
                        aut.meta_value as team FROM assist_users au JOIN  assist_user_meta aum ON aum.uid = au.id 
                            LEFT JOIN  assist_user_meta aut ON aut.meta_key = 'team' and aut.uid = au.id
                            WHERE aum.meta_key='team' AND aum.meta_value IN ($manager_str) AND au.department=1 AND au.status = 1 ")->queryAll();

                    if (!empty($sales_managers)) {
                        foreach ($sales_managers as $manager) {
                            $get_all_executive_of_manager[] = $manager;
                        }
                    }
                    $get_all_manager = DvUsers::find()->select(["assist_users.id", "first_name", "last_name"])
                            ->join('join', 'assist_user_meta', 'assist_user_meta.uid = assist_users.id')
                            ->where(["meta_key" => "role", "department" => 1, "assist_users.status" => 1])
                            ->andWhere(["IN", "meta_value", array(6)])
                            ->all();

                    if (!empty($get_all_executive_of_manager)) {
                        foreach ($get_all_executive_of_manager as $executive) {
                            $executive_id = $executive['id'];

                            $manager_id = '';
                            if (!isset($executive['team'])) {
                                $manager_id = $executive_id;
                            } else {
                                $manager_id = $executive['team'];
                            }

                            $manager = DvUsers::find()->where(['id' => $manager_id, "status" => 1])->one();
                            $all_executive[$executive_id]['manager_name'] = $manager->first_name . " " . $manager->last_name;

                            $all_executive[$executive_id]['name'] = $executive['first_name'] . " " . $executive['last_name'];

                            $all_executive[$executive_id]['course_details'] = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, 
                        count('ap.*') as sold_course FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                        WHERE $fil_domain $fil_product ap.sales_user_id = $executive_id  AND MONTH(ap.created_on)=$month AND YEAR(ap.created_on) = $year 
                            GROUP by ap.course")->queryAll();

                            $all_executive[$executive_id]['user_wise_sold_course'] = Yii::$app->db->createCommand("SELECT course, count(*) as 
                        total_sold, ac.name FROM assist_participant ap JOIN assist_course ac ON ac.id = ap.course 
                        WHERE $fil_domain $fil_product created_on BETWEEN DATE('$date_before_1_year') AND DATE('$today') AND sales_user_id = $executive_id 
                            GROUP BY course")->queryAll();
                        }
                    }
                } else if (empty($product) && empty($executives)) {

                    $manager_details = array();
                    $manager_details['id'] = $managers;
                    $manager_details['first_name'] = $manager_info['first_name'];
                    $manager_details['last_name'] = $manager_info['last_name'];
                    $executive_of_manager[] = $manager_details;

                    if (!empty($executive_of_manager)) {

                        foreach ($executive_of_manager as $exe) {
                            $exe_id = $exe['id'];

                            $manager_id = '';
                            if (!isset($exe['team'])) {
                                $manager_id = $exe_id;
                            } else {
                                $manager_id = $exe['team'];
                            }

                            $manager = DvUsers::find()->where(['id' => $manager_id, "status" => 1])->one();
                            $all_executive[$exe_id]['manager_name'] = $manager->first_name . " " . $manager->last_name;

                            $all_executive[$exe_id]['name'] = $exe['first_name'] . " " . $exe['last_name'];

                            $all_executive[$exe_id]['course_details'] = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, 
                        count('ap.*') as sold_course FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                        WHERE $fil_domain ap.sales_user_id = $exe_id  AND MONTH(ap.created_on)=$month AND YEAR(ap.created_on) = $year 
                            GROUP by ap.course")->queryAll();

                            $all_executive[$exe_id]['user_wise_sold_course'] = Yii::$app->db->createCommand("SELECT course, count(*) as 
                        total_sold, ac.name FROM assist_participant ap JOIN assist_course ac ON ac.id = ap.course 
                        WHERE created_on BETWEEN DATE('$date_before_1_year') AND DATE('$today') AND sales_user_id = $exe_id 
                            GROUP BY course")->queryAll();
                        }
                    }
                } else if (empty($product) && !empty($executives)) {

                    $exe_id = $executives;

                    $all_executive[$exe_id]['manager_name'] = "";

                    $all_executive[$exe_id]['name'] = "";

                    $all_executive[$exe_id]['course_details'] = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, 
                        count('ap.*') as sold_course FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                        WHERE $fil_domain ap.sales_user_id = $exe_id  AND MONTH(ap.created_on)=$month AND YEAR(ap.created_on) = $year 
                            GROUP by ap.course")->queryAll();

                    $all_executive[$exe_id]['user_wise_sold_course'] = Yii::$app->db->createCommand("SELECT course, count(*) as 
                        total_sold, ac.name FROM assist_participant ap JOIN assist_course ac ON ac.id = ap.course 
                        WHERE created_on BETWEEN DATE('$date_before_1_year') AND DATE('$today') AND sales_user_id = $exe_id 
                            GROUP BY course")->queryAll();
                } else if (!empty($product) && !empty($executives)) {

                    $exe_id = $executives;

                    $all_executive[$exe_id]['manager_name'] = "";

                    $all_executive[$exe_id]['name'] = "";

                    $all_executive[$exe_id]['course_details'] = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, 
                        count('ap.*') as sold_course FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                        WHERE $fil_domain $fil_product ap.sales_user_id = $exe_id AND ap.course = $product AND MONTH(ap.created_on)=$month AND YEAR(ap.created_on) = $year 
                            GROUP by ap.course")->queryAll();

                    $all_executive[$exe_id]['user_wise_sold_course'] = Yii::$app->db->createCommand("SELECT course, count(*) as 
                        total_sold, ac.name FROM assist_participant ap JOIN assist_course ac ON ac.id = ap.course 
                        WHERE ap.course = $product AND created_on BETWEEN DATE('$date_before_1_year') AND DATE('$today') 
                            AND sales_user_id = $exe_id 
                            GROUP BY course")->queryAll();
                } else if (!empty($product) && empty($executives)) {

                    if (!empty($executive_of_manager)) {
                        foreach ($executive_of_manager as $exe) {
                            $exe_id = $exe['id'];

                            $manager_id = '';
                            if (!isset($exe['team'])) {
                                $manager_id = $exe_id;
                            } else {
                                $manager_id = $exe['team'];
                            }
                            $manager = DvUsers::find()->where(['id' => $manager_id, "status" => 1])->one();
                            $all_executive[$exe_id]['manager_name'] = $manager->first_name . " " . $manager->last_name;

                            $all_executive[$exe_id]['name'] = $exe['first_name'] . " " . $exe['last_name'];

                            $all_executive[$exe_id]['course_details'] = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, 
                        count('ap.*') as sold_course FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                        WHERE $fil_domain $fil_product ap.course = $product AND ap.sales_user_id = $exe_id  AND MONTH(ap.created_on)=$month 
                            AND YEAR(ap.created_on) = $year 
                            GROUP by ap.course")->queryAll();

                            $all_executive[$exe_id]['user_wise_sold_course'] = Yii::$app->db->createCommand("SELECT course, count(*) as 
                        total_sold, ac.name FROM assist_participant ap JOIN assist_course ac ON ac.id = ap.course 
                        WHERE ap.course = $product AND created_on BETWEEN DATE('$date_before_1_year') AND DATE('$today') 
                            AND sales_user_id = $exe_id 
                            GROUP BY course")->queryAll();
                        }
                    }
                }
            } else if ($data['select_period'] == 'weekly') {
                if (!empty($data['weekly_date_from'])) {
                    $weekly_date_from = $data['weekly_date_from'];
                    $weekly_date_from = date("Y-m-d", strtotime($weekly_date_from));
                    $filtered_data['weekly_date_from'] = $weekly_date_from;
                }
                if (!empty($data['weekly_date_to'])) {
                    $weekly_date_to = $data['weekly_date_to'];
                    $weekly_date_to = date("Y-m-d", strtotime($weekly_date_to));
                    $filtered_data['weekly_date_to'] = $weekly_date_to;
                }

                $domain = $data['domain'];
                $filtered_data['selected_domain'] = $domain;

                $fil_domain = '';
                if ($domain) {
                    $fil_domain = "ac.mcourse ='" . $domain . "' AND";
                }

                $Dv_course = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, count('ap.*') as sold_course 
                    FROM assist_participant ap 
                    JOIN assist_course ac on ac.id= ap.course 
                    WHERE $fil_domain ap.sales_user_id = $executives  AND (ap.created_on BETWEEN '$weekly_date_from' AND '$weekly_date_to')
                        GROUP by ap.course")->queryAll();

                /* Total users of Sales department except Sales Head */
                $count_sales_users = Yii::$app->db->createCommand("SELECT count(assist_users.id) as total_sales_users FROM assist_users 
                    join assist_user_meta ON assist_user_meta.uid = assist_users.id 
                    WHERE (department=1) AND (status=1) AND (meta_key='role') AND (meta_value <> 7) AND assist_users.status = 1 ")->queryAll();

                $all_course_in_this_period = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, 
                        count('ap.*') as sold_course FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                        WHERE  (ap.created_on BETWEEN '$weekly_date_from' AND '$weekly_date_to') 
                            GROUP by ap.course")->queryAll();

                $date_before_1_year = date('Y-m-d', strtotime($weekly_date_from . ' -1 year'));
                $selected_date = $weekly_date_from;
                $datestring = "$selected_date last day of last month";
                $dt = date_create($datestring);
                $last_date_of_last_month = $dt->format('Y-m-d');
                $date_before_1_year_of_month = date('Y-m-d', strtotime($last_date_of_last_month . ' -1 year'));
                $first_date_of_month = date("$year-$month-1");

                if (!empty($sales_user_created_on)) {
                    foreach ($sales_user_created_on as $key => $sales_user) {

                        $diff = $this->datediff('ww', $sales_user['created'], $weekly_date_from);
                        $sales_user_created_on[$key]['created_before_weeks'] = $diff;
                    }
                }
                $all_course_except_this_period = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, 
                        count('ap.*') as sold_course, ap.sales_user_id FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                        WHERE DATE(ap.created_on) < '$weekly_date_from' 
                            AND (DATE(ap.created_on) BETWEEN '$date_before_1_year_of_month' AND '$last_date_of_last_month')
                            GROUP by ap.course,ap.sales_user_id")->queryAll();

                $product = $data['products'];
                $managers = $data['managers'];
                $domain = $data['domain'];

                $executives = $data['executives'];
                $filtered_data['selected_course'] = $product;
                $filtered_data['selected_domain'] = $domain;
                $filtered_data['selected_manager'] = $managers;
                $filtered_data['executives'] = $executives;
                $all_executive = array();

                $fil_product = '';
                if ($product) {
                    $fil_product = "ap.course =" . $product . " AND";
                }

                $fil_domain = '';
                if ($domain) {
                    $fil_domain = "ac.mcourse ='" . $domain . "' AND";
                }

                if (!empty($managers)) {
                    $manager_info = DvUsers::find()->select(["assist_users.id", "first_name", "last_name"])
                            ->where(["id" => $managers, "status" => 1])
                            ->one();
                    $executive_of_manager = Yii::$app->db->createCommand("SELECT au.id, au.first_name, au.last_name , aut.meta_value as team
                            FROM assist_users au 
                            JOIN assist_user_meta aum on aum.uid= au.id 
                            LEFT JOIN  assist_user_meta aut ON aut.meta_key = 'team' and aut.uid = au.id
                            WHERE aum.meta_key = 'team' AND aum.meta_value=$managers AND au.status = 1 ")->queryAll();
                    $filtered_data['executive_of_manager'] = $executive_of_manager;
                }
                if (empty($managers)) {

                    $sales_managers = Yii::$app->db->createCommand("SELECT au.id, au.first_name, au.last_name FROM assist_users au 
                        JOIN  assist_user_meta aum ON aum.uid = au.id WHERE aum.meta_key='role' AND aum.meta_value=6 
                        AND au.department=1 AND au.status = 1 ")->queryAll();
                    $manager_str = "";
                    if (!empty($sales_managers)) {
                        foreach ($sales_managers as $manager) {
                            $manager_str .= $manager['id'] . ",";
                        }
                    }

                    $manager_str = rtrim($manager_str, ",");

                    $get_all_executive_of_manager = Yii::$app->db->createCommand("SELECT au.id, au.first_name, au.last_name , aut.meta_value as team
                            FROM assist_users au 
                            JOIN  assist_user_meta aum ON aum.uid = au.id 
                            LEFT JOIN  assist_user_meta aut ON aut.meta_key = 'team' and aut.uid = au.id
                            WHERE aum.meta_key='team' AND aum.meta_value IN ($manager_str) AND au.department=1 AND au.status = 1 ")->queryAll();

                    if (!empty($sales_managers)) {
                        foreach ($sales_managers as $manager) {
                            $get_all_executive_of_manager[] = $manager;
                        }
                    }
                    $get_all_manager = DvUsers::find()->select(["assist_users.id", "first_name", "last_name"])
                            ->join('join', 'assist_user_meta', 'assist_user_meta.uid = assist_users.id')
                            ->where(["meta_key" => "role", "department" => 1, "assist_users.status" => 1])
                            ->andWhere(["IN", "meta_value", array(6)])
                            ->all();

                    if (!empty($get_all_executive_of_manager)) {
                        foreach ($get_all_executive_of_manager as $executive) {
                            $executive_id = $executive['id'];

                            $manager_id = '';
                            if (!isset($executive['team'])) {
                                $manager_id = $executive_id;
                            } else {
                                $manager_id = $executive['team'];
                            }
                            $manager = DvUsers::find()->where(['id' => $manager_id, "status" => 1])->one();
                            $all_executive[$executive_id]['manager_name'] = $manager->first_name . " " . $manager->last_name;

                            $all_executive[$executive_id]['name'] = $executive['first_name'] . " " . $executive['last_name'];

                            $all_executive[$executive_id]['course_details'] = Yii::$app->db->createCommand("SELECT ap.course, 
                                    ac.name,ac.mcourse, count('ap.*') as sold_course 
                                    FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                                    WHERE $fil_domain $fil_product ap.sales_user_id = $executive_id 
                                        AND (ap.created_on BETWEEN '$weekly_date_from' AND '$weekly_date_to')  
                            GROUP by ap.course")->queryAll();

                            $all_executive[$executive_id]['user_wise_sold_course'] = Yii::$app->db->createCommand("SELECT course, 
                                    count(*) as total_sold, ac.name FROM assist_participant ap JOIN assist_course ac ON ac.id = ap.course 
                                    WHERE $fil_domain $fil_product created_on BETWEEN DATE('$date_before_1_year') AND DATE('$today') AND sales_user_id = $executive_id 
                            GROUP BY course")->queryAll();
                        }
                    }
                } else if (empty($product) && empty($executives)) {

                    if (!empty($executive_of_manager)) {

                        $manager_details = array();
                        $manager_details['id'] = $managers;
                        $manager_details['first_name'] = $manager_info['first_name'];
                        $manager_details['last_name'] = $manager_info['last_name'];
                        $executive_of_manager[] = $manager_details;

                        foreach ($executive_of_manager as $exe) {
                            $exe_id = $exe['id'];

                            $manager_id = '';
                            if (!isset($exe['team'])) {
                                $manager_id = $exe_id;
                            } else {
                                $manager_id = $exe['team'];
                            }
                            $manager = DvUsers::find()->where(['id' => $manager_id, "status" => 1])->one();
                            $all_executive[$exe_id]['manager_name'] = $manager->first_name . " " . $manager->last_name;

                            $all_executive[$exe_id]['name'] = $exe['first_name'] . " " . $exe['last_name'];

                            $all_executive[$exe_id]['course_details'] = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, 
                        count('ap.*') as sold_course FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                        WHERE $fil_domain  ap.sales_user_id = $exe_id  AND (ap.created_on BETWEEN '$weekly_date_from' AND '$weekly_date_to')  
                            GROUP by ap.course")->queryAll();

                            $all_executive[$exe_id]['user_wise_sold_course'] = Yii::$app->db->createCommand("SELECT course, count(*) as 
                        total_sold, ac.name FROM assist_participant ap JOIN assist_course ac ON ac.id = ap.course 
                        WHERE created_on BETWEEN DATE('$date_before_1_year') AND DATE('$today') AND sales_user_id = $exe_id 
                            GROUP BY course")->queryAll();
                        }
                    }
                } else if (empty($product) && !empty($executives)) {

                    $exe_id = $executives;

                    $all_executive[$executive_id]['manager_name'] = '';

                    $all_executive[$exe_id]['name'] = "";

                    $all_executive[$exe_id]['course_details'] = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, 
                        count('ap.*') as sold_course FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                        WHERE $fil_domain  ap.sales_user_id = $exe_id  AND (ap.created_on BETWEEN '$weekly_date_from' AND '$weekly_date_to')  
                            GROUP by ap.course")->queryAll();

                    $all_executive[$exe_id]['user_wise_sold_course'] = Yii::$app->db->createCommand("SELECT course, count(*) as 
                        total_sold, ac.name FROM assist_participant ap JOIN assist_course ac ON ac.id = ap.course 
                        WHERE created_on BETWEEN DATE('$date_before_1_year') AND DATE('$today') AND sales_user_id = $exe_id 
                            GROUP BY course")->queryAll();
                } else if (!empty($product) && !empty($executives)) {


                    $exe_id = $executives;
                    $all_executive[$executive_id]['manager_name'] =
                            $all_executive[$exe_id]['name'] = "";

                    $all_executive[$exe_id]['course_details'] = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, 
                        count('ap.*') as sold_course FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                        WHERE $fil_domain  $fil_product ap.sales_user_id = $exe_id AND ap.course = $product 
                            AND (ap.created_on BETWEEN '$weekly_date_from' AND '$weekly_date_to') 
                            GROUP by ap.course")->queryAll();

                    $all_executive[$exe_id]['user_wise_sold_course'] = Yii::$app->db->createCommand("SELECT course, count(*) as 
                        total_sold, ac.name FROM assist_participant ap JOIN assist_course ac ON ac.id = ap.course 
                        WHERE ap.course = $product AND created_on BETWEEN DATE('$date_before_1_year') AND DATE('$today') 
                            AND sales_user_id = $exe_id 
                            GROUP BY course")->queryAll();
                } else if (!empty($product) && empty($executives)) {

                    if (!empty($executive_of_manager)) {
                        foreach ($executive_of_manager as $exe) {
                            $exe_id = $exe['id'];
                            $manager_id = '';
                            if (!isset($exe['team'])) {
                                $manager_id = $exe_id;
                            } else {
                                $manager_id = $exe['team'];
                            }
                            $manager = DvUsers::find()->where(['id' => $manager_id, "status" => 1])->one();
                            $all_executive[$executive_id]['manager_name'] = $manager->first_name . " " . $manager->last_name;

                            $all_executive[$exe_id]['name'] = $exe['first_name'] . " " . $exe['last_name'];

                            $all_executive[$exe_id]['course_details'] = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, 
                        count('ap.*') as sold_course FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                        WHERE $fil_domain  $fil_product ap.course = $product AND ap.sales_user_id = $exe_id  
                            AND (ap.created_on BETWEEN '$weekly_date_from' AND '$weekly_date_to') 
                            GROUP by ap.course")->queryAll();

                            $all_executive[$exe_id]['user_wise_sold_course'] = Yii::$app->db->createCommand("SELECT course, count(*) as 
                        total_sold, ac.name FROM assist_participant ap JOIN assist_course ac ON ac.id = ap.course 
                        WHERE ap.course = $product AND created_on BETWEEN DATE('$date_before_1_year') AND DATE('$today') 
                            AND sales_user_id = $exe_id 
                            GROUP BY course")->queryAll();
                        }
                    }
                }
            } else if ($data['select_period'] == 'daily') {
                $daily_date = $data['date_for_daily'];
                $filtered_data['date_for_daily'] = $daily_date;

                $domain = $data['domain'];
                $filtered_data['selected_domain'] = $domain;

                $fil_domain = '';
                if ($domain) {
                    $fil_domain = "ac.mcourse ='" . $domain . "' AND";
                }



                $Dv_course = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, count('ap.*') as sold_course 
                    FROM assist_participant ap 
                    JOIN assist_course ac on ac.id= ap.course 
                    WHERE $fil_domain ap.sales_user_id = $executives  AND DATE(ap.created_on) = '$daily_date'
                        GROUP by ap.course")->queryAll();

                /* Total users of Sales department except Sales Head */
                $count_sales_users = Yii::$app->db->createCommand("SELECT count(assist_users.id) as total_sales_users FROM assist_users 
                    join assist_user_meta ON assist_user_meta.uid = assist_users.id 
                    WHERE (department=1) AND (status=1) AND (meta_key='role') AND (meta_value <> 7) AND assist_users.status = 1 ")->queryAll();

                $all_course_in_this_period = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, 
                        count('ap.*') as sold_course FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                        WHERE  DATE(ap.created_on) = '$daily_date'  
                            GROUP by ap.course")->queryAll();

                if (!empty($sales_user_created_on)) {
                    foreach ($sales_user_created_on as $key => $sales_user) {

                        $earlier = new \DateTime($daily_date);
                        $later = new \DateTime($sales_user['created']);

                        $diff = $later->diff($earlier)->format("%a");
                        $sales_user_created_on[$key]['created_before_days'] = $diff;
                    }
                }

                $product = $data['products'];
                $managers = $data['managers'];
                $domain = $data['domain'];
                $executives = $data['executives'];
                $filtered_data['selected_course'] = $product;
                $filtered_data['selected_domain'] = $domain;
                $filtered_data['selected_manager'] = $managers;
                $filtered_data['executives'] = $executives;
                $all_executive = array();

                $fil_product = '';
                if ($product) {
                    $fil_product = "ap.course =" . $product . " AND";
                }

                $fil_domain = '';
                if ($domain) {
                    $fil_domain = "ac.mcourse ='" . $domain . "' AND";
                }

                if (!empty($managers)) {
                    $manager_info = DvUsers::find()->select(["assist_users.id", "first_name", "last_name"])
                            ->where(["id" => $managers, "status" => 1])
                            ->one();

                    $executive_of_manager = Yii::$app->db->createCommand("SELECT au.id, au.first_name, au.last_name ,aut.meta_value as team
                            FROM assist_users au 
                            JOIN assist_user_meta aum on aum.uid= au.id 
                            LEFT JOIN  assist_user_meta aut ON aut.meta_key = 'team' and aut.uid = au.id
                            WHERE aum.meta_key = 'team' AND aum.meta_value=$managers AND au.status = 1 ")->queryAll();
                    $filtered_data['executive_of_manager'] = $executive_of_manager;
                }
                if (empty($managers)) {

                    $sales_managers = Yii::$app->db->createCommand("SELECT au.id, au.first_name, au.last_name FROM assist_users au 
                        JOIN  assist_user_meta aum ON aum.uid = au.id 
                WHERE aum.meta_key='role' AND aum.meta_value=6 AND au.department=1 AND au.status = 1 ")->queryAll();
                    $manager_str = "";
                    if (!empty($sales_managers)) {
                        foreach ($sales_managers as $manager) {
                            $manager_str .= $manager['id'] . ",";
                        }
                    }

                    $manager_str = rtrim($manager_str, ",");

                    $get_all_executive_of_manager = Yii::$app->db->createCommand("SELECT au.id, au.first_name, au.last_name, 
                        aut.meta_value as team FROM assist_users au JOIN  assist_user_meta aum ON aum.uid = au.id 
                            LEFT JOIN  assist_user_meta aut ON aut.meta_key = 'team' and aut.uid = au.id
                            WHERE aum.meta_key='team' AND aum.meta_value IN ($manager_str) AND au.department=1 AND au.status = 1 ")->queryAll();

                    if (!empty($sales_managers)) {
                        foreach ($sales_managers as $manager) {
                            $get_all_executive_of_manager[] = $manager;
                        }
                    }
                    $get_all_manager = DvUsers::find()->select(["assist_users.id", "first_name", "last_name"])
                            ->join('join', 'assist_user_meta', 'assist_user_meta.uid = assist_users.id')
                            ->where(["meta_key" => "role", "department" => 1, "assist_users.status" => 1])
                            ->andWhere(["IN", "meta_value", array(6)])
                            ->all();

                    if (!empty($get_all_executive_of_manager)) {
                        foreach ($get_all_executive_of_manager as $executive) {
                            $executive_id = $executive['id'];

                            $manager_id = '';
                            if (!isset($executive['team'])) {
                                $manager_id = $executive_id;
                            } else {
                                $manager_id = $executive['team'];
                            }
                            $manager = DvUsers::find()->where(['id' => $manager_id, "status" => 1])->one();
                            $all_executive[$executive_id]['manager_name'] = $manager->first_name . " " . $manager->last_name;

                            $all_executive[$executive_id]['name'] = $executive['first_name'] . " " . $executive['last_name'];

                            $all_executive[$executive_id]['course_details'] = Yii::$app->db->createCommand("SELECT ap.course, 
                                    ac.name,ac.mcourse, count('ap.*') as sold_course 
                                    FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                                    WHERE $fil_domain $fil_product ap.sales_user_id = $executive_id 
                                        AND DATE(ap.created_on) = '$daily_date'  
                            GROUP by ap.course")->queryAll();

                            $all_executive[$executive_id]['user_wise_sold_course'] = Yii::$app->db->createCommand("SELECT course, 
                                    count(*) as total_sold, ac.name FROM assist_participant ap JOIN assist_course ac ON ac.id = ap.course 
                                    WHERE $fil_domain $fil_product created_on BETWEEN DATE('$date_before_1_year') AND DATE('$today') AND sales_user_id = $executive_id 
                            GROUP BY course")->queryAll();
                        }
                    }
                } else if (empty($product) && empty($executives)) {

                    $manager_details = array();
                    $manager_details['id'] = $managers;
                    $manager_details['first_name'] = $manager_info['first_name'];
                    $manager_details['last_name'] = $manager_info['last_name'];
                    $executive_of_manager[] = $manager_details;

                    if (!empty($executive_of_manager)) {
                        foreach ($executive_of_manager as $exe) {
                            $exe_id = $exe['id'];

                            $manager_id = '';
                            if (!isset($exe['team'])) {
                                $manager_id = $exe_id;
                            } else {
                                $manager_id = $exe['team'];
                            }
                            $manager = DvUsers::find()->where(['id' => $manager_id, "status" => 1])->one();
                            $all_executive[$exe_id]['manager_name'] = $manager->first_name . " " . $manager->last_name;

                            $all_executive[$exe_id]['name'] = $exe['first_name'] . " " . $exe['last_name'];

                            $all_executive[$exe_id]['course_details'] = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, 
                        count('ap.*') as sold_course FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                        WHERE  $fil_domain ap.sales_user_id = $exe_id  AND DATE(ap.created_on) = '$daily_date'   
                            GROUP by ap.course")->queryAll();

                            $all_executive[$exe_id]['user_wise_sold_course'] = Yii::$app->db->createCommand("SELECT course, count(*) as 
                        total_sold, ac.name FROM assist_participant ap JOIN assist_course ac ON ac.id = ap.course 
                        WHERE created_on BETWEEN DATE('$date_before_1_year') AND DATE('$today') AND sales_user_id = $exe_id 
                            GROUP BY course")->queryAll();
                        }
                    }
                } else if (empty($product) && !empty($executives)) {

                    $exe_id = $executives;

                    $all_executive[$exe_id]['manager_name'] = "";
                    $all_executive[$exe_id]['name'] = "";

                    $all_executive[$exe_id]['course_details'] = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, 
                        count('ap.*') as sold_course FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                        WHERE $fil_domain ap.sales_user_id = $exe_id   AND DATE(ap.created_on) = '$daily_date'   
                            GROUP by ap.course")->queryAll();

                    $all_executive[$exe_id]['user_wise_sold_course'] = Yii::$app->db->createCommand("SELECT course, count(*) as 
                        total_sold, ac.name FROM assist_participant ap JOIN assist_course ac ON ac.id = ap.course 
                        WHERE created_on BETWEEN DATE('$date_before_1_year') AND DATE('$today') AND sales_user_id = $exe_id 
                            GROUP BY course")->queryAll();
                } else if (!empty($product) && !empty($executives)) {

                    $exe_id = $executives;
                    $all_executive[$exe_id]['manager_name'] = "";
                    $all_executive[$exe_id]['name'] = "";

                    $all_executive[$exe_id]['course_details'] = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, 
                        count('ap.*') as sold_course FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                        WHERE $fil_domain  $fil_product ap.sales_user_id = $exe_id AND ap.course = $product 
                            AND  DATE(ap.created_on) = '$daily_date'  
                            GROUP by ap.course")->queryAll();

                    $all_executive[$exe_id]['user_wise_sold_course'] = Yii::$app->db->createCommand("SELECT course, count(*) as 
                        total_sold, ac.name FROM assist_participant ap JOIN assist_course ac ON ac.id = ap.course 
                        WHERE ap.course = $product AND created_on BETWEEN DATE('$date_before_1_year') AND DATE('$today') 
                            AND sales_user_id = $exe_id 
                            GROUP BY course")->queryAll();
                } else if (!empty($product) && empty($executives)) {

                    if (!empty($executive_of_manager)) {
                        foreach ($executive_of_manager as $exe) {
                            $exe_id = $exe['id'];

                            $manager_id = '';
                            if (!isset($exe['team'])) {
                                $manager_id = $exe_id;
                            } else {
                                $manager_id = $exe['team'];
                            }
                            $manager = DvUsers::find()->where(['id' => $manager_id, "status" => 1])->one();
                            $all_executive[$exe_id]['manager_name'] = $manager->first_name . " " . $manager->last_name;

                            $all_executive[$exe_id]['name'] = $exe['first_name'] . " " . $exe['last_name'];

                            $all_executive[$exe_id]['course_details'] = Yii::$app->db->createCommand("SELECT ap.course, ac.name,ac.mcourse, 
                        count('ap.*') as sold_course FROM assist_participant ap JOIN assist_course ac on ac.id= ap.course 
                        WHERE $fil_domain  $fil_product ap.course = $product AND ap.sales_user_id = $exe_id  AND DATE(ap.created_on) = '$daily_date' 
                            GROUP by ap.course")->queryAll();

                            $all_executive[$exe_id]['user_wise_sold_course'] = Yii::$app->db->createCommand("SELECT course, count(*) as 
                        total_sold, ac.name FROM assist_participant ap JOIN assist_course ac ON ac.id = ap.course 
                        WHERE ap.course = $product AND created_on BETWEEN DATE('$date_before_1_year') AND DATE('$today') 
                            AND sales_user_id = $exe_id 
                            GROUP BY course")->queryAll();
                        }
                    }
                }
            }
        }

        return $this->render('sales_head_sales_report', ['current_user_role' => $current_user_role,
                    'all_executive_of_manager' => $get_all_executive_of_manager,
                    'logged_in_user_id' => $logged_in_user_id,
                    'executives' => $executives,
                    'all_executive' => $all_executive,
                    'all_course_details' => $all_course_details,
                    'Dv_course' => $Dv_course,
                    'all_course_in_this_period' => $all_course_in_this_period,
                    'all_courses' => $all_courses,
                    'course_sold_last_year_by_current_user' => $course_sold_last_year_by_current_user,
                    'total_course_sold_last_year' => $total_course_sold_last_year,
                    'sales_managers' => $sales_managers,
                    'count_sales_users' => $count_sales_users[0]['total_sales_users'],
                    'all_course_except_this_period' => $all_course_except_this_period,
                    'sales_user_created_on' => $sales_user_created_on,
                    'filtered_data' => $filtered_data]);
    }

    /**
     * * @ Get executive of a manager
     * */
    public function actionGet_executive_of_manager() {
        $data = Yii::$app->request->post();
        $get_all_executive_of_manager = array();
        if (!empty($data)) {
            $manager_id = $data['sales_manager'];
            $get_all_executive_of_manager = Yii::$app->db->createCommand("SELECT au.id, au.first_name, au.last_name 
                    FROM assist_users au 
                    JOIN assist_user_meta aum on aum.uid= au.id 
                    WHERE aum.meta_key = 'team' AND aum.meta_value=$manager_id AND au.status = 1 ")->queryAll();
        }
        echo json_encode($get_all_executive_of_manager);
        die;
    }

    /**
     * @param $interval
     * @param $datefrom
     * @param $dateto
     * @param bool $using_timestamps
     * @return false|float|int|string
     */
    function datediff($interval, $datefrom, $dateto, $using_timestamps = false) {
        /*
          $interval can be:
          yyyy - Number of full years
          q    - Number of full quarters
          m    - Number of full months
          y    - Difference between day numbers
          (eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33". The datediff is "-32".)
          d    - Number of full days
          w    - Number of full weekdays
          ww   - Number of full weeks
          h    - Number of full hours
          n    - Number of full minutes
          s    - Number of full seconds (default)
         */

        if (!$using_timestamps) {
            $datefrom = strtotime($datefrom, 0);
            $dateto = strtotime($dateto, 0);
        }

        $difference = $dateto - $datefrom; /* Difference in seconds */
        $months_difference = 0;

        switch ($interval) {
            case 'yyyy': /* Number of full years */
                $years_difference = floor($difference / 31536000);
                if (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom), date("j", $datefrom), date("Y", $datefrom) + $years_difference) > $dateto) {
                    $years_difference--;
                }

                if (mktime(date("H", $dateto), date("i", $dateto), date("s", $dateto), date("n", $dateto), date("j", $dateto), date("Y", $dateto) - ($years_difference + 1)) > $datefrom) {
                    $years_difference++;
                }

                $datediff = $years_difference;
                break;

            case "q": /* Number of full quarters */
                $quarters_difference = floor($difference / 8035200);

                while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom) + ($quarters_difference * 3), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
                    $months_difference++;
                }

                $quarters_difference--;
                $datediff = $quarters_difference;
                break;

            case "m": /* Number of full months */
                $months_difference = floor($difference / 2678400);

                while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom) + ($months_difference), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
                    $months_difference++;
                }

                $months_difference--;

                $datediff = $months_difference;
                break;

            case 'y': /* Difference between day numbers */
                $datediff = date("z", $dateto) - date("z", $datefrom);
                break;

            case "d": /* Number of full days */
                $datediff = floor($difference / 86400);
                break;

            case "w": /* Number of full weekdays */
                $days_difference = floor($difference / 86400);
                $weeks_difference = floor($days_difference / 7); /* Complete weeks */
                $first_day = date("w", $datefrom);
                $days_remainder = floor($days_difference % 7);
                $odd_days = $first_day + $days_remainder; /* Do we have a Saturday or Sunday in the remainder? */

                if ($odd_days > 7) { /* Sunday */
                    $days_remainder--;
                }

                if ($odd_days > 6) { /* Saturday */
                    $days_remainder--;
                }

                $datediff = ($weeks_difference * 5) + $days_remainder;
                break;

            case "ww": /* Number of full weeks */
                $datediff = floor($difference / 604800);
                break;

            case "h": /* Number of full hours */
                $datediff = floor($difference / 3600);
                break;

            case "n": /* Number of full minutes */
                $datediff = floor($difference / 60);
                break;

            default: /* Number of full seconds (default) */
                $datediff = $difference;
                break;
        }

        return $datediff;
    }

    public function actionConsultant_dashboard_report() {
        if (!Yii::$app->CustomComponents->check_permission('consultant_dashboard_report')) {
            return $this->redirect(['site/index']);
        }

        $data = Yii::$app->request->post();
        if (!empty($data)) {
            $month = $data['month'];
            $year = $data['year'];
        } else {
            $month = date('m');
            $year = date('Y');
        }

        /* Get all manager id */
        $get_all_mangers = DvUserMeta::find()->select(["uid"])->where(['meta_key' => "team", "meta_value" => ""])->all();

        $get_manager_id_arr = array();
        foreach ($get_all_mangers as $value) {
            $get_all_mangers_role = DvUserMeta::find()->select(["uid"])
                            ->join('join', 'assist_users', 'assist_users.id = assist_user_meta.uid')
                            ->where(['uid' => $value->uid, "meta_key" => "role", 'meta_value' => 6,
                                "assist_users.department" => 1, "status" => 1])->one();

            if ($get_all_mangers_role) {
                $get_manager_id_arr[] = $get_all_mangers_role->uid;
            }
        }


        /* Get all consultant id manager wise */
        $get_consultant_id_arr = array();
        $get_consultant_id_arr_dm = array();

        foreach ($get_manager_id_arr as $cons_value) {

            $get_all_consultant_role = DvUserMeta::find()->select(["uid"])->where(["meta_key" => "team", 'meta_value' => $cons_value])->all();
            $cnt = 1;
            $cnt_dm = 1;


            foreach ($get_all_consultant_role as $value) {

                $get_all_consultant_course = DvUsers::find()->select(["course"])->where(["id" => $value->uid, "status" => 1])->one();

                $course = $get_all_consultant_course->course;
                $course_arr = (explode(",", $course));


                if (in_array("da", $course_arr)) {

                    /* This is used to determine the current month and also to calculate the first and last day of the month */
                    $now = new \DateTime('now', new \DateTimeZone('Asia/Kolkata'));
                    /* Create a \DateTime representation of the first day of the current month based off of "now" */
                    $start = new \DateTime($now->format("$month/01/$year"), new \DateTimeZone('Asia/Kolkata'));
                    /* Create a \DateTime representation of the last day of the current month based off of "now" */
                    $end = new \DateTime($now->format("$month/t/$year"), new \DateTimeZone('Asia/Kolkata'));
                    /* Define our interval (1 Day) */
                    $interval = new \DateInterval('P1D');
                    /* Setup a DatePeriod instance to iterate between the start and end date by the interval */
                    $period = new \DatePeriod($start, $interval, $end);

                    /* Iterate over the DatePeriod instance */
                    $left_days = 0;
                    foreach ($period as $date) {
                        /* Make sure the day displayed is greater than or equal to today */
                        /* Make sure the day displayed is NOT sunday. */
                        if ($date >= $now && $date->format('w') != 0) {
                            $left_days++;
                        }
                    }

                    /* Iterate over the DatePeriod instance */
                    $till_days = 0;
                    foreach ($period as $date) {
                        /* Make sure the day displayed is greater than or equal to today */
                        /* Make sure the day displayed is NOT sunday. */
                        if ($date <= $now && $date->format('w') != 0) {
                            $till_days++;
                        }
                    }

                    $date_cur = date('Y-m-d H:i:s');
                    $date_pre = date("Y-m-d", strtotime($date_cur . ' -1 day'));
                    $time = strtotime($date_pre);
                    $day = date("D", $time);

                    $date_pre_except_sun = $date_pre;
                    if ($day == "Sun") {
                        $date_pre_except_sun = date("Y-m-d H:i:s", strtotime($date_pre . ' -1 day'));
                    }

                    $date_before_yes_except_sun = date("Y-m-d H:i:s", strtotime($date_pre_except_sun . ' -1 day'));
                    if ($day == "Sun") {
                        $date_before_yes_except_sun = date("Y-m-d H:i:s", strtotime($date_before_yes_except_sun . ' -1 day'));
                    }

                    if ($cnt == 1) {
                        $get_consultant_id_arr[$cons_value][$cons_value]['name'] = $cons_value;

                        $get_name = DvUsers::find()->select(["first_name", "last_name"])->where(['id' => $cons_value, "status" => 1])->one();
                        if (!empty($get_name)) {
                            $get_consultant_id_arr[$cons_value][$cons_value]['user_name'] = $get_name->first_name . " " . $get_name->last_name;
                        } else {
                            $get_consultant_id_arr[$cons_value][$cons_value]['user_name'] = "";
                        }

                        $currnet_month_sale_da = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale 
                            FROM assist_participant 
                            LEFT JOIN  assist_course 
                            ON assist_participant.course = assist_course.id
                            WHERE Month(assist_participant.created_on) = $month AND YEAR(assist_participant.created_on) = $year AND assist_participant.sales_user_id=$cons_value AND assist_course.mcourse = 'da' ")->queryAll();

                        $get_consultant_id_arr[$cons_value][$cons_value]['sales_current_month'] = $currnet_month_sale_da[0]['total_sale'];

                        $min_target = 0;
                        $check_exception = Yii::$app->db->createCommand("SELECT * FROM 
                            assist_manage_monthly_incentive_exception_rate WHERE executive_id=$cons_value AND month=$month AND years=$year 
                                AND domain='da'")->queryAll();
                        if (!empty($check_exception)) {
                            $recent_date = array();
                            foreach ($check_exception as $exception) {

                                $recent_date[] = $exception['created_at'];
                            }
                            $cnt_for_3rd_closures = 0;
                            foreach ($check_exception as $exception) {
                                if (end($recent_date) == $exception['created_at']) { /* Display lastly added rules */
                                    $cnt_for_3rd_closures++;
                                    if ($cnt_for_3rd_closures == 3) {
                                        /* Select min_closures of 3rd rule. */
                                        $min_target = $exception['min_closures'];
                                    }
                                }
                            }
                        } else {
                            $get_min_closures = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_rate 
                    WHERE month=$month AND year=$year AND mcourse='da'")->queryAll();
                            if (!empty($get_min_closures)) {
                                $recent_date = array();
                                foreach ($get_min_closures as $incentive) {

                                    $recent_date[] = $incentive['created_at'];
                                }
                                $cnt_for_3rd_closures = 0;
                                foreach ($get_min_closures as $incentive) {
                                    if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                                        $cnt_for_3rd_closures++;
                                        if ($cnt_for_3rd_closures == 3) {
                                            /* Select min_closures of 3rd rule. */
                                            $min_target = $incentive['min_closures'];
                                        }
                                    }
                                }
                            }
                        }
                        $get_consultant_id_arr[$cons_value][$cons_value]['min_target'] = $min_target;


                        $left = $get_consultant_id_arr[$cons_value][$cons_value]['min_target'] - $get_consultant_id_arr[$cons_value][$cons_value]['sales_current_month'];
                        if ($left <= 0) {
                            $left = 0;
                        }

                        $get_consultant_id_arr[$cons_value][$cons_value]['left'] = $left;

                        if (date('m') == $month && date('Y') == $year) {
                            $get_consultant_id_arr[$cons_value][$cons_value]['left_days'] = $left_days;
                        } else if (!empty($data)) {
                            $get_consultant_id_arr[$cons_value][$cons_value]['left_days'] = 0;
                        } else {
                            $get_consultant_id_arr[$cons_value][$cons_value]['left_days'] = $left_days;
                        }


                        // $today = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale_today FROM assist_participant WHERE DATE(created_on) = CURDATE() AND sales_user_id=$cons_value")->queryAll();

                        $today = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale_today 
                            FROM assist_participant 
                            LEFT JOIN  assist_course 
                            ON assist_participant.course = assist_course.id
                            WHERE DATE(assist_participant.created_on) = CURDATE() AND assist_participant.sales_user_id=$cons_value AND assist_course.mcourse = 'da' ")->queryAll();

                        if (date('m') == $month && date('Y') == $year) {
                            $get_consultant_id_arr[$cons_value][$cons_value]['today'] = number_format((float) $today[0]['total_sale_today'], 2, '.', '');
                        } else if (!empty($data)) {
                            $get_consultant_id_arr[$cons_value][$cons_value]['today'] = 0;
                        } else {
                            $get_consultant_id_arr[$cons_value][$cons_value]['today'] = number_format((float) $today[0]['total_sale_today'], 2, '.', '');
                        }

                        $yesterday = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale_yesterday 
                            FROM assist_participant 
                            LEFT JOIN  assist_course 
                            ON assist_participant.course = assist_course.id
                            WHERE DATE(assist_participant.created_on) = '$date_pre_except_sun' AND assist_participant.sales_user_id=$cons_value AND assist_course.mcourse = 'da' ")->queryAll();

                        if (date('m') == $month && date('Y') == $year) {
                            $get_consultant_id_arr[$cons_value][$cons_value]['yesterday'] = number_format((float) $yesterday[0]['total_sale_yesterday'], 2, '.', '');
                        } else if (!empty($data)) {
                            $get_consultant_id_arr[$cons_value][$cons_value]['yesterday'] = 0;
                        } else {
                            $get_consultant_id_arr[$cons_value][$cons_value]['yesterday'] = number_format((float) $yesterday[0]['total_sale_yesterday'], 2, '.', '');
                        }

                        $day_before_yesterday = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale_before_yesterday 
                            FROM assist_participant 
                            LEFT JOIN  assist_course 
                            ON assist_participant.course = assist_course.id
                            WHERE DATE(assist_participant.created_on) = '$date_before_yes_except_sun' AND assist_participant.sales_user_id=$cons_value AND assist_course.mcourse = 'da' ")->queryAll();


                        if (date('m') == $month && date('Y') == $year) {
                            $get_consultant_id_arr[$cons_value][$cons_value]['day_before_yesterday'] = number_format((float) $day_before_yesterday[0]['total_sale_before_yesterday'], 2, '.', '');
                        } else if (!empty($data)) {
                            $get_consultant_id_arr[$cons_value][$cons_value]['day_before_yesterday'] = 0;
                        } else {
                            $get_consultant_id_arr[$cons_value][$cons_value]['day_before_yesterday'] = number_format((float) $day_before_yesterday[0]['total_sale_before_yesterday'], 2, '.', '');
                        }


                        $per_day_sales_till_now = $currnet_month_sale_da[0]['total_sale'] / $till_days;
                        $get_consultant_id_arr[$cons_value][$cons_value]['per_day_sales_till_now'] = number_format((float) $per_day_sales_till_now, 2, '.', '');

                        if (date('m') == $month && date('Y') == $year) {
                            if($left_days == 0){
                                $per_day_sales = 0;
                            }else{
                                $per_day_sales = $left / $left_days;
                            }

                            if (date('m') == $month && date('Y') == $year) {
                                $get_consultant_id_arr[$cons_value][$cons_value]['per_day_sales'] = number_format((float) $per_day_sales, 2, '.', '');
                            } else if (!empty($data)) {
                                $get_consultant_id_arr[$cons_value][$cons_value]['per_day_sales'] = 0;
                            } else {
                                $get_consultant_id_arr[$cons_value][$cons_value]['per_day_sales'] = number_format((float) $per_day_sales, 2, '.', '');
                            }
                        } else {
                            $get_consultant_id_arr[$cons_value][$cons_value]['per_day_sales'] = 0;
                        }

                        $cnt++;
                    }

                    $get_consultant_id_arr[$cons_value][$value->uid]['name'] = $value->uid;
                    $get_name = DvUsers::find()->select(["first_name", "last_name"])->where(['id' => $value->uid, "status" => 1])->one();
                    if (!empty($get_name)) {
                        $get_consultant_id_arr[$cons_value][$value->uid]['user_name'] = $get_name->first_name . " " . $get_name->last_name;
                    } else {
                        $get_consultant_id_arr[$cons_value][$value->uid]['user_name'] = "";
                    }

                    $currnet_month_sale_da = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale 
                            FROM assist_participant 
                            LEFT JOIN  assist_course 
                            ON assist_participant.course = assist_course.id
                            WHERE Month(assist_participant.created_on) = $month AND YEAR(assist_participant.created_on) = $year AND assist_participant.sales_user_id=$value->uid AND assist_course.mcourse = 'da' ")->queryAll();

                    $get_consultant_id_arr[$cons_value][$value->uid]['sales_current_month'] = $currnet_month_sale_da[0]['total_sale'];

                    $min_target = 0;
                    $check_exception = Yii::$app->db->createCommand("SELECT min_closures FROM assist_manage_monthly_incentive_exception_rate 
                    WHERE executive_id=$value->uid AND month=$month AND years=$year AND rate>0 AND domain='da'")->queryAll();
                    if (!empty($check_exception)) {
                        $recent_date = array();
                        foreach ($check_exception as $exception) {

                            $recent_date[] = $exception['created_at'];
                        }
                        $cnt_for_3rd_closures = 0;
                        foreach ($check_exception as $exception) {
                            if (end($recent_date) == $exception['created_at']) { /* Display lastly added rules */
                                $cnt_for_3rd_closures++;
                                if ($cnt_for_3rd_closures == 3) {
                                    /* Select min_closures of 3rd rule. */
                                    $min_target = $exception['min_closures'];
                                }
                            }
                        }
                    } else {
                        $get_min_closures = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_rate 
                    WHERE month=$month AND year=$year AND mcourse='da'")->queryAll();
                        if (!empty($get_min_closures)) {
                            $recent_date = array();
                            foreach ($get_min_closures as $incentive) {

                                $recent_date[] = $incentive['created_at'];
                            }
                            $cnt_for_3rd_closures = 0;
                            foreach ($get_min_closures as $incentive) {
                                if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                                    $cnt_for_3rd_closures++;
                                    if ($cnt_for_3rd_closures == 3) {
                                        /* Select min_closures of 3rd rule. */
                                        $min_target = $incentive['min_closures'];
                                    }
                                }
                            }
                        }
                    }
                    $get_consultant_id_arr[$cons_value][$value->uid]['min_target'] = $min_target;

                    $left = $get_consultant_id_arr[$cons_value][$value->uid]['min_target'] - $get_consultant_id_arr[$cons_value][$value->uid]['sales_current_month'];
                    if ($left <= 0) {
                        $left = 0;
                    }
                    $get_consultant_id_arr[$cons_value][$value->uid]['left'] = $left;
                    if (date('m') == $month && date('Y') == $year) {
                        $get_consultant_id_arr[$cons_value][$value->uid]['left_days'] = $left_days;
                    } else if (!empty($data)) {
                        $get_consultant_id_arr[$cons_value][$value->uid]['left_days'] = 0;
                    } else {
                        $get_consultant_id_arr[$cons_value][$value->uid]['left_days'] = $left_days;
                    }


                    $today = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale_today 
                            FROM assist_participant 
                            LEFT JOIN  assist_course 
                            ON assist_participant.course = assist_course.id
                            WHERE DATE(assist_participant.created_on) = CURDATE() AND assist_participant.sales_user_id=$value->uid AND assist_course.mcourse = 'da' ")->queryAll();

                    if (date('m') == $month && date('Y') == $year) {
                        $get_consultant_id_arr[$cons_value][$value->uid]['today'] = number_format((float) $today[0]['total_sale_today'], 2, '.', '');
                    } else if (!empty($data)) {
                        $get_consultant_id_arr[$cons_value][$value->uid]['today'] = 0;
                    } else {
                        $get_consultant_id_arr[$cons_value][$value->uid]['today'] = number_format((float) $today[0]['total_sale_today'], 2, '.', '');
                    }

                    $yesterday = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale_yesterday 
                            FROM assist_participant 
                            LEFT JOIN  assist_course 
                            ON assist_participant.course = assist_course.id
                            WHERE DATE(assist_participant.created_on) = '$date_pre_except_sun' AND assist_participant.sales_user_id=$value->uid AND assist_course.mcourse = 'da' ")->queryAll();

                    if (date('m') == $month && date('Y') == $year) {
                        $get_consultant_id_arr[$cons_value][$value->uid]['yesterday'] = number_format((float) $yesterday[0]['total_sale_yesterday'], 2, '.', '');
                    } else if (!empty($data)) {
                        $get_consultant_id_arr[$cons_value][$value->uid]['yesterday'] = 0;
                    } else {
                        $get_consultant_id_arr[$cons_value][$value->uid]['yesterday'] = number_format((float) $yesterday[0]['total_sale_yesterday'], 2, '.', '');
                    }

                    $day_before_yesterday = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale_before_yesterday 
                            FROM assist_participant 
                            LEFT JOIN  assist_course 
                            ON assist_participant.course = assist_course.id
                            WHERE DATE(assist_participant.created_on) = '$date_before_yes_except_sun' AND assist_participant.sales_user_id=$value->uid AND assist_course.mcourse = 'da' ")->queryAll();

                    if (date('m') == $month && date('Y') == $year) {
                        $get_consultant_id_arr[$cons_value][$value->uid]['day_before_yesterday'] = number_format((float) $day_before_yesterday[0]['total_sale_before_yesterday'], 2, '.', '');
                    } else if (!empty($data)) {
                        $get_consultant_id_arr[$cons_value][$value->uid]['day_before_yesterday'] = 0;
                    } else {
                        $get_consultant_id_arr[$cons_value][$value->uid]['day_before_yesterday'] = number_format((float) $day_before_yesterday[0]['total_sale_before_yesterday'], 2, '.', '');
                    }


                    $per_day_sales_till_now = $currnet_month_sale_da[0]['total_sale'] / $till_days;
                    $get_consultant_id_arr[$cons_value][$value->uid]['per_day_sales_till_now'] = number_format((float) $per_day_sales_till_now, 2, '.', '');
                    if (date('m') == $month && date('Y') == $year) {

                        // $per_day_sales = $left / $left_days;

                        if($left_days == 0){
                            $per_day_sales = 0;
                        }else{
                            $per_day_sales = $left / $left_days;
                        }

                        if (date('m') == $month && date('Y') == $year) {
                            $get_consultant_id_arr[$cons_value][$value->uid]['per_day_sales'] = number_format((float) $per_day_sales, 2, '.', '');
                        } else if (!empty($data)) {
                            $get_consultant_id_arr[$cons_value][$value->uid]['per_day_sales'] = 0;
                        } else {
                            $get_consultant_id_arr[$cons_value][$value->uid]['per_day_sales'] = number_format((float) $per_day_sales, 2, '.', '');
                        }
                    } else {
                        $get_consultant_id_arr[$cons_value][$value->uid]['per_day_sales'] = 0;
                    }
                }
                if (in_array("dm", $course_arr)) {
                    /* This is used to determine the current month and also to calculate the first and last day of the month */
                    $now = new \DateTime('now', new \DateTimeZone('Asia/Kolkata'));
                    /* Create a \DateTime representation of the first day of the current month based off of "now" */
                    $start = new \DateTime($now->format("$month/01/$year"), new \DateTimeZone('Asia/Kolkata'));
                    /* Create a \DateTime representation of the last day of the current month based off of "now" */
                    $end = new \DateTime($now->format("$month/t/$year"), new \DateTimeZone('Asia/Kolkata'));
                    /* Define our interval (1 Day) */
                    $interval = new \DateInterval('P1D');
                    /* Setup a DatePeriod instance to iterate between the start and end date by the interval */
                    $period = new \DatePeriod($start, $interval, $end);

                    /* Iterate over the DatePeriod instance */
                    $left_days = 0;
                    foreach ($period as $date) {
                        /* Make sure the day displayed is greater than or equal to today */
                        /* Make sure the day displayed is NOT sunday. */
                        if ($date >= $now && $date->format('w') != 0) {
                            $left_days++;
                        }
                    }

                    /* Iterate over the DatePeriod instance */
                    $till_days = 0;
                    foreach ($period as $date) {
                        /* Make sure the day displayed is greater than or equal to today */
                        /* Make sure the day displayed is NOT sunday. */
                        if ($date <= $now && $date->format('w') != 0) {
                            $till_days++;
                        }
                    }

                    $date_cur = date('Y-m-d H:i:s');
                    $date_pre = date("Y-m-d", strtotime($date_cur . ' -1 day'));
                    $time = strtotime($date_pre);
                    $day = date("D", $time);

                    $date_pre_except_sun = $date_pre;
                    if ($day == "Sun") {
                        $date_pre_except_sun = date("Y-m-d H:i:s", strtotime($date_pre . ' -1 day'));
                    }

                    $date_before_yes_except_sun = date("Y-m-d H:i:s", strtotime($date_pre_except_sun . ' -1 day'));
                    if ($day == "Sun") {
                        $date_before_yes_except_sun = date("Y-m-d H:i:s", strtotime($date_before_yes_except_sun . ' -1 day'));
                    }

                    if ($cnt_dm == 1) {
                        $get_consultant_id_arr_dm[$cons_value][$cons_value]['name'] = $cons_value;

                        $get_name = DvUsers::find()->select(["first_name", "last_name"])->where(['id' => $cons_value, "status" => 1])->one();
                        if (!empty($get_name)) {
                            $get_consultant_id_arr_dm[$cons_value][$cons_value]['user_name'] = $get_name->first_name . " " . $get_name->last_name;
                        } else {
                            $get_consultant_id_arr_dm[$cons_value][$cons_value]['user_name'] = "";
                        }

                        $currnet_month_sale = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale 
                            FROM assist_participant 
                            LEFT JOIN  assist_course 
                            ON assist_participant.course = assist_course.id
                            WHERE Month(assist_participant.created_on) = $month AND YEAR(assist_participant.created_on) = $year AND assist_participant.sales_user_id=$cons_value AND assist_course.mcourse = 'dm' ")->queryAll();

                        $get_consultant_id_arr_dm[$cons_value][$cons_value]['sales_current_month'] = $currnet_month_sale[0]['total_sale'];

                        $min_target = 0;
                        $check_exception = Yii::$app->db->createCommand("SELECT * FROM 
                            assist_manage_monthly_incentive_exception_rate WHERE executive_id=$cons_value AND month=$month AND years=$year 
                                AND domain='dm'")->queryAll();
                        if (!empty($check_exception)) {
                            $recent_date = array();
                            foreach ($check_exception as $exception) {

                                $recent_date[] = $exception['created_at'];
                            }
                            $cnt_for_3rd_closures = 0;
                            foreach ($check_exception as $exception) {
                                if (end($recent_date) == $exception['created_at']) { /* Display lastly added rules */
                                    $cnt_for_3rd_closures++;
                                    if ($cnt_for_3rd_closures == 3) {
                                        /* Select min_closures of 3rd rule. */
                                        $min_target = $exception['min_closures'];
                                    }
                                }
                            }
                        } else {
                            $get_min_closures = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_rate 
                    WHERE month=$month AND year=$year AND mcourse='dm'")->queryAll();
                            if (!empty($get_min_closures)) {
                                $recent_date = array();
                                foreach ($get_min_closures as $incentive) {

                                    $recent_date[] = $incentive['created_at'];
                                }
                                $cnt_for_3rd_closures = 0;
                                foreach ($get_min_closures as $incentive) {
                                    if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                                        $cnt_for_3rd_closures++;
                                        if ($cnt_for_3rd_closures == 3) {
                                            /* Select min_closures of 3rd rule. */
                                            $min_target = $incentive['min_closures'];
                                        }
                                    }
                                }
                            }
                        }
                        $get_consultant_id_arr_dm[$cons_value][$cons_value]['min_target'] = $min_target;

                        $left = $get_consultant_id_arr_dm[$cons_value][$cons_value]['min_target'] - $get_consultant_id_arr_dm[$cons_value][$cons_value]['sales_current_month'];
                        if ($left <= 0) {
                            $left = 0;
                        }

                        $get_consultant_id_arr_dm[$cons_value][$cons_value]['left'] = $left;

                        if (date('m') == $month && date('Y') == $year) {
                            $get_consultant_id_arr_dm[$cons_value][$cons_value]['left_days'] = $left_days;
                        } else if (!empty($data)) {
                            $get_consultant_id_arr_dm[$cons_value][$cons_value]['left_days'] = 0;
                        } else {
                            $get_consultant_id_arr_dm[$cons_value][$cons_value]['left_days'] = $left_days;
                        }


                        $today = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale_today 
                            FROM assist_participant 
                            LEFT JOIN  assist_course 
                            ON assist_participant.course = assist_course.id
                            WHERE DATE(assist_participant.created_on) =  CURDATE() AND assist_participant.sales_user_id=$cons_value AND assist_course.mcourse = 'dm' ")->queryAll();

                        if (date('m') == $month && date('Y') == $year) {
                            $get_consultant_id_arr_dm[$cons_value][$cons_value]['today'] = number_format((float) $today[0]['total_sale_today'], 2, '.', '');
                        } else if (!empty($data)) {
                            $get_consultant_id_arr_dm[$cons_value][$cons_value]['today'] = 0;
                        } else {
                            $get_consultant_id_arr_dm[$cons_value][$cons_value]['today'] = number_format((float) $today[0]['total_sale_today'], 2, '.', '');
                        }

                        $yesterday = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale_yesterday 
                            FROM assist_participant 
                            LEFT JOIN  assist_course 
                            ON assist_participant.course = assist_course.id
                            WHERE DATE(assist_participant.created_on) =  '$date_pre_except_sun' AND assist_participant.sales_user_id=$cons_value AND assist_course.mcourse = 'dm' ")->queryAll();

                        if (date('m') == $month && date('Y') == $year) {
                            $get_consultant_id_arr_dm[$cons_value][$cons_value]['yesterday'] = number_format((float) $yesterday[0]['total_sale_yesterday'], 2, '.', '');
                        } else if (!empty($data)) {
                            $get_consultant_id_arr_dm[$cons_value][$cons_value]['yesterday'] = 0;
                        } else {
                            $get_consultant_id_arr_dm[$cons_value][$cons_value]['yesterday'] = number_format((float) $yesterday[0]['total_sale_yesterday'], 2, '.', '');
                        }


                        $day_before_yesterday = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale_before_yesterday 
                            FROM assist_participant 
                            LEFT JOIN  assist_course 
                            ON assist_participant.course = assist_course.id
                            WHERE DATE(assist_participant.created_on) =  '$date_before_yes_except_sun' AND assist_participant.sales_user_id=$cons_value AND assist_course.mcourse = 'dm' ")->queryAll();

                        if (date('m') == $month && date('Y') == $year) {
                            $get_consultant_id_arr_dm[$cons_value][$cons_value]['day_before_yesterday'] = number_format((float) $day_before_yesterday[0]['total_sale_before_yesterday'], 2, '.', '');
                        } else if (!empty($data)) {
                            $get_consultant_id_arr_dm[$cons_value][$cons_value]['day_before_yesterday'] = 0;
                        } else {
                            $get_consultant_id_arr_dm[$cons_value][$cons_value]['day_before_yesterday'] = number_format((float) $day_before_yesterday[0]['total_sale_before_yesterday'], 2, '.', '');
                        }


                        $per_day_sales_till_now = $currnet_month_sale[0]['total_sale'] / $till_days;
                        $get_consultant_id_arr_dm[$cons_value][$cons_value]['per_day_sales_till_now'] = number_format((float) $per_day_sales_till_now, 2, '.', '');

                        if (date('m') == $month && date('Y') == $year) {

                            if($left_days == 0){
                                $per_day_sales = 0;
                            }else{
                                $per_day_sales = $left / $left_days;
                            }


                            if (date('m') == $month && date('Y') == $year) {
                                $get_consultant_id_arr_dm[$cons_value][$cons_value]['per_day_sales'] = number_format((float) $per_day_sales, 2, '.', '');
                            } else if (!empty($data)) {
                                $get_consultant_id_arr_dm[$cons_value][$cons_value]['per_day_sales'] = 0;
                            } else {
                                $get_consultant_id_arr_dm[$cons_value][$cons_value]['per_day_sales'] = number_format((float) $per_day_sales, 2, '.', '');
                            }
                        } else {
                            $get_consultant_id_arr_dm[$cons_value][$cons_value]['per_day_sales'] = 0;
                        }

                        $cnt_dm++;
                    }


                    $get_consultant_id_arr_dm[$cons_value][$value->uid]['name'] = $value->uid;
                    $get_name = DvUsers::find()->select(["first_name", "last_name"])->where(['id' => $value->uid, "status" => 1])->one();
                    if (!empty($get_name)) {
                        $get_consultant_id_arr_dm[$cons_value][$value->uid]['user_name'] = $get_name->first_name . " " . $get_name->last_name;
                    } else {
                        $get_consultant_id_arr_dm[$cons_value][$value->uid]['user_name'] = "";
                    }

                    $currnet_month_sale = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale 
                            FROM assist_participant 
                            LEFT JOIN  assist_course 
                            ON assist_participant.course = assist_course.id
                            WHERE Month(assist_participant.created_on) = $month AND YEAR(assist_participant.created_on) = $year AND assist_participant.sales_user_id=$value->uid AND assist_course.mcourse = 'dm' ")->queryAll();


                    $get_consultant_id_arr_dm[$cons_value][$value->uid]['sales_current_month'] = $currnet_month_sale[0]['total_sale'];

                    $min_target = 0;
                    $check_exception = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_exception_rate 
                    WHERE executive_id=$value->uid AND month=$month AND years=$year AND domain='dm'")->queryAll();
                    if (!empty($check_exception)) {
                        $recent_date = array();
                        foreach ($check_exception as $exception) {

                            $recent_date[] = $exception['created_at'];
                        }
                        $cnt_for_3rd_closures = 0;
                        foreach ($check_exception as $exception) {
                            if (end($recent_date) == $exception['created_at']) { /* Display lastly added rules */
                                $cnt_for_3rd_closures++;
                                if ($cnt_for_3rd_closures == 3) {
                                    /* Select min_closures of 3rd rule. */
                                    $min_target = $exception['min_closures'];
                                }
                            }
                        }
                    } else {
                        $get_min_closures = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_rate 
                    WHERE month=$month AND year=$year AND mcourse='dm'")->queryAll();
                        if (!empty($get_min_closures)) {
                            $recent_date = array();
                            foreach ($get_min_closures as $incentive) {

                                $recent_date[] = $incentive['created_at'];
                            }
                            $cnt_for_3rd_closures = 0;
                            foreach ($get_min_closures as $incentive) {
                                if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                                    $cnt_for_3rd_closures++;
                                    if ($cnt_for_3rd_closures == 3) {
                                        /* Select min_closures of 3rd rule. */
                                        $min_target = $incentive['min_closures'];
                                    }
                                }
                            }
                        }
                    }
                    $get_consultant_id_arr_dm[$cons_value][$value->uid]['min_target'] = $min_target;

                    $left = $get_consultant_id_arr_dm[$cons_value][$value->uid]['min_target'] - $get_consultant_id_arr_dm[$cons_value][$value->uid]['sales_current_month'];
                    if ($left <= 0) {
                        $left = 0;
                    }
                    $get_consultant_id_arr_dm[$cons_value][$value->uid]['left'] = $left;
                    if (date('m') == $month && date('Y') == $year) {
                        $get_consultant_id_arr_dm[$cons_value][$value->uid]['left_days'] = $left_days;
                    } else if (!empty($data)) {
                        $get_consultant_id_arr_dm[$cons_value][$value->uid]['left_days'] = 0;
                    } else {
                        $get_consultant_id_arr_dm[$cons_value][$value->uid]['left_days'] = $left_days;
                    }

                    $today = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale_today 
                            FROM assist_participant 
                            LEFT JOIN  assist_course 
                            ON assist_participant.course = assist_course.id
                            WHERE DATE(assist_participant.created_on) = CURDATE() AND assist_participant.sales_user_id=$value->uid AND assist_course.mcourse = 'dm' ")->queryAll();

                    if (date('m') == $month && date('Y') == $year) {
                        $get_consultant_id_arr_dm[$cons_value][$value->uid]['today'] = number_format((float) $today[0]['total_sale_today'], 2, '.', '');
                    } else if (!empty($data)) {
                        $get_consultant_id_arr_dm[$cons_value][$value->uid]['today'] = 0;
                    } else {
                        $get_consultant_id_arr_dm[$cons_value][$value->uid]['today'] = number_format((float) $today[0]['total_sale_today'], 2, '.', '');
                    }

                    $yesterday = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale_yesterday 
                            FROM assist_participant 
                            LEFT JOIN  assist_course 
                            ON assist_participant.course = assist_course.id
                            WHERE DATE(assist_participant.created_on) = '$date_pre_except_sun' AND assist_participant.sales_user_id=$value->uid AND assist_course.mcourse = 'dm' ")->queryAll();


                    if (date('m') == $month && date('Y') == $year) {
                        $get_consultant_id_arr_dm[$cons_value][$value->uid]['yesterday'] = number_format((float) $yesterday[0]['total_sale_yesterday'], 2, '.', '');
                    } else if (!empty($data)) {
                        $get_consultant_id_arr_dm[$cons_value][$value->uid]['yesterday'] = 0;
                    } else {
                        $get_consultant_id_arr_dm[$cons_value][$value->uid]['yesterday'] = number_format((float) $yesterday[0]['total_sale_yesterday'], 2, '.', '');
                    }

                    $day_before_yesterday = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale_before_yesterday 
                            FROM assist_participant 
                            LEFT JOIN  assist_course 
                            ON assist_participant.course = assist_course.id
                            WHERE DATE(assist_participant.created_on) = '$date_before_yes_except_sun' AND assist_participant.sales_user_id=$value->uid AND assist_course.mcourse = 'dm' ")->queryAll();

                    if (date('m') == $month && date('Y') == $year) {
                        $get_consultant_id_arr_dm[$cons_value][$value->uid]['day_before_yesterday'] = number_format((float) $day_before_yesterday[0]['total_sale_before_yesterday'], 2, '.', '');
                    } else if (!empty($data)) {
                        $get_consultant_id_arr_dm[$cons_value][$value->uid]['day_before_yesterday'] = 0;
                    } else {
                        $get_consultant_id_arr_dm[$cons_value][$value->uid]['day_before_yesterday'] = number_format((float) $day_before_yesterday[0]['total_sale_before_yesterday'], 2, '.', '');
                    }


                    $per_day_sales_till_now = $currnet_month_sale[0]['total_sale'] / $till_days;
                    $get_consultant_id_arr_dm[$cons_value][$value->uid]['per_day_sales_till_now'] = number_format((float) $per_day_sales_till_now, 2, '.', '');
                    if (date('m') == $month && date('Y') == $year) {

                        // $per_day_sales = $left / $left_days;
                        if($left_days == 0){
                            $per_day_sales = 0;
                        }else{
                            $per_day_sales = $left / $left_days;
                        }

                        if (date('m') == $month && date('Y') == $year) {
                            $get_consultant_id_arr_dm[$cons_value][$value->uid]['per_day_sales'] = number_format((float) $per_day_sales, 2, '.', '');
                        } else if (!empty($data)) {
                            $get_consultant_id_arr_dm[$cons_value][$value->uid]['per_day_sales'] = 0;
                        } else {
                            $get_consultant_id_arr_dm[$cons_value][$value->uid]['per_day_sales'] = number_format((float) $per_day_sales, 2, '.', '');
                        }
                    } else {
                        $get_consultant_id_arr_dm[$cons_value][$value->uid]['per_day_sales'] = 0;
                    }
                }
            }
        }


        $filtered_data = array();
        $filtered_data['month'] = $month;
        $filtered_data['year'] = $year;

        $all_users = DvUsers::find()->where(["status" => 1])->all();

        return $this->render('consultant_dashboard_report', [ 'filtered_data' => $filtered_data,
                    'all_users' => $all_users,
                    'get_consultant_id_arr' => $get_consultant_id_arr,
                    'get_consultant_id_arr_dm' => $get_consultant_id_arr_dm,
                    'data' => $data,
                    'month' => $month,
                    'year' => $year,
        ]);
    }

    /*  --- End of actionConsultant_dashboard_report() */
    /*  --- End of actionConsultant_dashboard_report() */
    /* For Team Manager Dashboard Report 18 March 2019 */

    public function actionTeam_manager_dashboard() {
        if (!Yii::$app->CustomComponents->check_permission('team_manager_dashboard')) {
            return $this->redirect(['site/index']);
        }

        $data = Yii::$app->request->post();

        $get_all_mangers = DvUserMeta::find()->select(["uid"])->where(['meta_key' => "team", "meta_value" => ""])->all();

        $get_manager_id_arr = array();
        foreach ($get_all_mangers as $value) {
            $get_all_mangers_role = DvUserMeta::find()->select(["uid"])
                            ->join('join', 'assist_users', 'assist_users.id = assist_user_meta.uid')
                            ->where(['uid' => $value->uid, "meta_key" => "role", 'meta_value' => 6,
                                "assist_users.department" => 1, "status" => 1])->one();
            if ($get_all_mangers_role) {
                $get_manager_id_arr[] = $get_all_mangers_role->uid;
            }
        }

        if (!empty($data)) {
            $month = $data['month'];
            $year = $data['year'];
        } else {
            $month = date('m');
            $year = date('Y');
        }

        /* This is used to determine the current month and also to calculate the first and last day of the month */
        $now = new \DateTime('now', new \DateTimeZone('Asia/Kolkata'));
        /* Create a \DateTime representation of the first day of the current month based off of "now" */
        $start = new \DateTime($now->format("$month/01/$year"), new \DateTimeZone('Asia/Kolkata'));
        /* Create a \DateTime representation of the last day of the current month based off of "now" */
        $end = new \DateTime($now->format("$month/t/$year"), new \DateTimeZone('Asia/Kolkata'));
        /* Define our interval (1 Day) */
        $interval = new \DateInterval('P1D');
        /* Setup a DatePeriod instance to iterate between the start and end date by the interval */
        $period = new \DatePeriod($start, $interval, $end);

        /* Iterate over the DatePeriod instance */
        $left_days = 0;
        foreach ($period as $date) {
            /* Make sure the day displayed is greater than or equal to today */
            /* Make sure the day displayed is NOT sunday. */
            if ($date >= $now && $date->format('w') != 0) {
                $left_days++;
            }
        }

        /* Iterate over the DatePeriod instance */
        $till_days = 0;
        foreach ($period as $date) {
            /* Make sure the day displayed is greater than or equal to today */
            /* Make sure the day displayed is NOT sunday. */
            if ($date <= $now && $date->format('w') != 0) {
                $till_days++;
            }
        }

        $date_cur = date('Y-m-d H:i:s');
        $date_pre = date("Y-m-d", strtotime($date_cur . ' -1 day'));
        $time = strtotime($date_pre);
        $day = date("D", $time);

        $date_pre_except_sun = $date_pre;
        if ($day == "Sun") {
            $date_pre_except_sun = date("Y-m-d H:i:s", strtotime($date_pre . ' -1 day'));
        }

        $date_before_yes_except_sun = date("Y-m-d H:i:s", strtotime($date_pre_except_sun . ' -1 day'));
        if ($day == "Sun") {
            $date_before_yes_except_sun = date("Y-m-d H:i:s", strtotime($date_before_yes_except_sun . ' -1 day'));
        }

        $get_course_normalize_value = Yii::$app->db->createCommand("SELECT * FROM assist_sales")->queryAll();

        $get_all_manager = Yii::$app->db->createCommand("SELECT assist_users.id as user_id,assist_users.first_name, assist_users.last_name,
            assist_users.email, assist_users.username 
            FROM assist_user_meta JOIN assist_users ON assist_users.id = assist_user_meta.uid 
            WHERE assist_users.department=1 AND assist_user_meta.meta_key='role' 
            AND assist_user_meta.meta_value=6 AND assist_users.status = 1 ")->queryAll();

        $manager_data = array();

        if (!empty($get_all_manager)) {
            foreach ($get_all_manager as $manager) {
                $manager_id = $manager['user_id'];

                $manager_data[$manager_id] = array();
                $manager_data[$manager_id]['user_name'] = $manager['first_name'] . " " . $manager['last_name'];
                $manager_data[$manager_id]['da_total_sales'] = 0;
                $manager_data[$manager_id]['dm_total_sales'] = 0;
                $executive_id_str = "";

                $get_manager_target = Yii::$app->db->createCommand("SELECT * FROM assist_course_target WHERE 
                    manager_id=$manager_id AND month=$month AND year=$year ORDER BY id desc LIMIT 1")->queryAll();

                if (!empty($get_manager_target)) {
                    $manager_data[$manager_id]['da_target'] = $get_manager_target[0]['da_target'];
                    $manager_data[$manager_id]['dm_target'] = $get_manager_target[0]['dm_target'];
                } else {
                    $manager_data[$manager_id]['da_target'] = 0;
                    $manager_data[$manager_id]['dm_target'] = 0;
                }


                $get_executives_of_manager = Yii::$app->db->createCommand("SELECT uid FROM assist_user_meta WHERE 
                    meta_key='team' AND meta_value=$manager_id")->queryAll();

                if (!empty($get_executives_of_manager)) {
                    foreach ($get_executives_of_manager as $executive) {
                        $executive_id_str .= $executive['uid'] . ", ";
                    }
                }
                $executive_id_str .= $manager_id;

                $da_currnet_month_sale = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale,assist_course.name 
                    FROM assist_participant JOIN assist_course ON assist_course.id=assist_participant.course 
                    WHERE Month(created_on) = $month AND YEAR(created_on) = $year AND sales_user_id IN ($executive_id_str) AND 
                        assist_course.mcourse='da' GROUP BY assist_course.name")->queryAll();

                $dm_currnet_month_sale = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale,assist_course.name 
                    FROM assist_participant JOIN assist_course ON assist_course.id=assist_participant.course 
                    WHERE Month(created_on) = $month AND YEAR(created_on) = $year AND sales_user_id IN ($executive_id_str) 
                        AND assist_course.mcourse='dm' GROUP BY assist_course.name")->queryAll();

                if (!empty($dm_currnet_month_sale)) {
                    foreach ($dm_currnet_month_sale as $course_sale) {
                        foreach ($get_course_normalize_value as $normalize_value) {
                            if ($normalize_value['name'] == $course_sale['name']) {
                                $manager_data[$manager_id]['dm_total_sales'] += ($course_sale['total_sale'] * $normalize_value['normalize_rate']);
                            }
                        }
                    }
                }

                if (!empty($da_currnet_month_sale)) {
                    foreach ($da_currnet_month_sale as $course_sale) {
                        foreach ($get_course_normalize_value as $normalize_value) {
                            if ($normalize_value['name'] == $course_sale['name']) {
                                $manager_data[$manager_id]['da_total_sales'] += $course_sale['total_sale'] * $normalize_value['normalize_rate'];
                            }
                        }
                    }
                }

                $manager_data[$manager_id]['da_left'] = $manager_data[$manager_id]['da_target'] - $manager_data[$manager_id]['da_total_sales'];
                $manager_data[$manager_id]['dm_left'] = $manager_data[$manager_id]['dm_target'] - $manager_data[$manager_id]['dm_total_sales'];

                if (date('m') == $month && date('Y') == $year) {
                    $manager_data[$manager_id]['left_days'] = $left_days;
                } else if (!empty($data)) {
                    $manager_data[$manager_id]['left_days'] = 0;
                } else {
                    $manager_data[$manager_id]['left_days'] = $left_days;
                }

                $da_left = $manager_data[$manager_id]['da_target'] - $manager_data[$manager_id]['da_total_sales'];
                $dm_left = $manager_data[$manager_id]['dm_target'] - $manager_data[$manager_id]['dm_total_sales'];

                if ($da_left <= 0) {
                    $da_left = 0;
                }

                if ($dm_left <= 0) {
                    $dm_left = 0;
                }

                if ($left_days <= 0) {
                    $da_per_day_sales = 0;
                    $dm_per_day_sales = 0;
                } else {
                    $da_per_day_sales = $da_left / $left_days;
                    $dm_per_day_sales = $dm_left / $left_days;
                }

                $manager_data[$manager_id]['da_per_day_sales_needed'] = number_format((float) $da_per_day_sales, 2, '.', '');
                $manager_data[$manager_id]['dm_per_day_sales_needed'] = number_format((float) $dm_per_day_sales, 2, '.', '');

                $da_today = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale_today,ac.name 
                    FROM assist_participant JOIN assist_course ac ON ac.id = assist_participant.course 
                    WHERE DATE(created_on) = CURDATE() AND sales_user_id IN ($executive_id_str) 
                        AND ac.mcourse='da' GROUP BY ac.name
                        ")->queryAll();

                $dm_today = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale_today,ac.name 
                    FROM assist_participant JOIN assist_course ac ON ac.id = assist_participant.course 
                    WHERE DATE(created_on) = CURDATE() AND sales_user_id IN ($executive_id_str)
                        AND ac.mcourse='dm' GROUP BY ac.name
                        ")->queryAll();

                $manager_data[$manager_id]['dm_today'] = 0;
                $manager_data[$manager_id]['da_today'] = 0;
                foreach ($dm_today as $today_sale) {
                    foreach ($get_course_normalize_value as $normalize_value) {
                        if ($normalize_value['name'] == $today_sale['name']) {
                            $manager_data[$manager_id]['dm_today'] += ($today_sale['total_sale_today'] * $normalize_value['normalize_rate']);
                        }
                    }
                }
                foreach ($da_today as $today_sale) {
                    foreach ($get_course_normalize_value as $normalize_value) {
                        if ($normalize_value['name'] == $today_sale['name']) {
                            $manager_data[$manager_id]['da_today'] += ($today_sale['total_sale_today'] * $normalize_value['normalize_rate']);
                        }
                    }
                }
                if (date('m') == $month && date('Y') == $year) {
                    
                } else if (!empty($data)) {
                    $manager_data[$manager_id]['dm_today'] = 0;
                    $manager_data[$manager_id]['da_today'] = 0;
                }

                $da_yesterday = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale_yesterday,ac.name 
                    FROM assist_participant JOIN assist_course ac ON ac.id = assist_participant.course 
                    WHERE DATE(created_on) = '$date_pre_except_sun' AND sales_user_id IN($executive_id_str)
                        AND ac.mcourse='da' GROUP BY ac.name")->queryAll();

                $dm_yesterday = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale_yesterday,ac.name 
                    FROM assist_participant JOIN assist_course ac ON ac.id = assist_participant.course 
                    WHERE DATE(created_on) = '$date_pre_except_sun' AND sales_user_id IN($executive_id_str)
                        AND ac.mcourse='dm' GROUP BY ac.name")->queryAll();

                $manager_data[$manager_id]['da_yesterday'] = 0;
                $manager_data[$manager_id]['dm_yesterday'] = 0;
                foreach ($dm_yesterday as $yesterday_sale) {
                    if (!empty($yesterday_sale)) {
                        foreach ($get_course_normalize_value as $normalize_value) {
                            if ($normalize_value['name'] == $yesterday_sale['name']) {
                                $manager_data[$manager_id]['dm_yesterday'] += ($yesterday_sale['total_sale_yesterday'] * $normalize_value['normalize_rate']);
                            }
                        }
                    }
                }
                foreach ($da_yesterday as $yesterday_sale) {
                    if (!empty($yesterday_sale)) {
                        foreach ($get_course_normalize_value as $normalize_value) {
                            if ($normalize_value['name'] == $yesterday_sale['name']) {
                                $manager_data[$manager_id]['da_yesterday'] += ($yesterday_sale['total_sale_yesterday'] * $normalize_value['normalize_rate']);
                            }
                        }
                    }
                }

                if (date('m') == $month && date('Y') == $year) {
                    
                } else if (!empty($data)) {
                    $manager_data[$manager_id]['da_yesterday'] = 0;
                    $manager_data[$manager_id]['dm_yesterday'] = 0;
                }


                $da_day_before_yesterday = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale_before_yesterday,ac.name 
                    FROM assist_participant JOIN assist_course ac ON ac.id = assist_participant.course 
                        WHERE DATE(created_on) = '$date_before_yes_except_sun' AND sales_user_id IN($executive_id_str)
                            AND ac.mcourse='da' GROUP BY ac.name")->queryAll();

                $dm_day_before_yesterday = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale_before_yesterday,ac.name 
                    FROM assist_participant JOIN assist_course ac ON ac.id = assist_participant.course 
                        WHERE DATE(created_on) = '$date_before_yes_except_sun' AND sales_user_id IN($executive_id_str)
                            AND ac.mcourse='dm' GROUP BY ac.name")->queryAll();

                $manager_data[$manager_id]['da_day_before_yesterday'] = 0;
                $manager_data[$manager_id]['dm_day_before_yesterday'] = 0;

                foreach ($dm_day_before_yesterday as $day_before_yesterday_sale) {
                    foreach ($get_course_normalize_value as $normalize_value) {
                        if ($normalize_value['name'] == $day_before_yesterday_sale['name']) {
                            $manager_data[$manager_id]['dm_day_before_yesterday'] += ($day_before_yesterday_sale['total_sale_before_yesterday'] * $normalize_value['normalize_rate']);
                        }
                    }
                }
                foreach ($da_day_before_yesterday as $day_before_yesterday_sale) {
                    foreach ($get_course_normalize_value as $normalize_value) {
                        if ($normalize_value['name'] == $day_before_yesterday_sale['name']) {
                            $manager_data[$manager_id]['da_day_before_yesterday'] += ($day_before_yesterday_sale['total_sale_before_yesterday'] * $normalize_value['normalize_rate']);
                        }
                    }
                }

                $manager_data[$manager_id]['da_day_before_yesterday'] = number_format((float) $manager_data[$manager_id]['da_day_before_yesterday'], 2, '.', '');
                $manager_data[$manager_id]['dm_day_before_yesterday'] = number_format((float) $manager_data[$manager_id]['dm_day_before_yesterday'], 2, '.', '');

                if (date('m') == $month && date('Y') == $year) {
                    
                } else if (!empty($data)) {
                    $manager_data[$manager_id]['da_day_before_yesterday'] = 0;
                    $manager_data[$manager_id]['dm_day_before_yesterday'] = 0;
                }

                $da_per_day_sales = number_format((float) ($manager_data[$manager_id]['da_total_sales'] / $till_days), 2, '.', '');
                $dm_per_day_sales = number_format((float) ($manager_data[$manager_id]['dm_total_sales'] / $till_days), 2, '.', '');

                $manager_data[$manager_id]['da_per_day_sales'] = $da_per_day_sales;
                $manager_data[$manager_id]['dm_per_day_sales'] = $dm_per_day_sales;
            }
        }

        $all_users = DvUsers::find()->where(["status" => 1])->all();

        $filtered_data = array();
        $filtered_data['month'] = $month;
        $filtered_data['year'] = $year;

        return $this->render('team_manager_dashboard', [ 'filtered_data' => $filtered_data,
                    'all_users' => $all_users,
                    'manager_data' => $manager_data,
                    'get_consultant_id_arr' => array(),
                    'data' => $data,
                    'month' => $month,
                    'year' => $year,
        ]);
    }

    /* --- End of actionTeam_manager_dashboard() ---   */

    /* For Incentive Report of Manager & Executive(Consultant) 23 March 2019 */

    public function actionIncentive_report() {
        if (!Yii::$app->CustomComponents->check_permission('incentive_report')) {
            return $this->redirect(['site/index']);
        }

        $dataService = $this->quickbook_instance();
        $data = Yii::$app->request->post();
        if (!empty($data)) {
            $month = $data['month'];
            $year = $data['year'];
        } else {
            $month = date('m');
            $year = date('Y');
        }

        $get_all_mangers = DvUserMeta::find()->select(["uid"])->where(['meta_key' => "team", "meta_value" => ""])->all();

        $get_manager_id_arr = array();
        foreach ($get_all_mangers as $value) {
            $get_all_mangers_role = Yii::$app->db->createCommand("SELECT uid, first_name, last_name FROM assist_user_meta 
                            JOIN  assist_users on assist_users.id =  assist_user_meta.uid
                            WHERE uid=$value->uid AND meta_key='role' AND meta_value=6 AND assist_users.department=1 AND 
                                assist_users.status=1")->queryAll();

            if (!empty($get_all_mangers_role)) {
                $details_arr = array();
                $details_arr['id'] = $get_all_mangers_role[0]['uid'];
                $details_arr['manager_name'] = $get_all_mangers_role[0]['first_name'] . " " . $get_all_mangers_role[0]['last_name'];
                $get_manager_id_arr[] = $details_arr;
            }
        }
        $get_consultant_id_arr = array();
        foreach ($get_manager_id_arr as $cons_value) {
            $manager_id = $cons_value['id'];
            $get_all_consultant_role = DvUserMeta::find()->select(["uid"])
                            ->join('join', 'assist_users', 'assist_user_meta.uid = assist_users.id')
                            ->where(["meta_key" => "team", 'meta_value' => $manager_id, 'assist_users.status' => 1])->all();
            $cnt = 1;

            foreach ($get_all_consultant_role as $value) {
                if ($cnt == 1) {
                    $get_consultant_id_arr[$manager_id][$manager_id]['id'] = $manager_id;
                    $get_consultant_id_arr[$manager_id][$manager_id]['manager_name'] = $cons_value['manager_name'];


                    $get_name = DvUsers::find()->select(["first_name", "last_name"])->where(['id' => $manager_id, "status" => 1])->one();
                    if (!empty($get_name)) {
                        $get_consultant_id_arr[$manager_id][$manager_id]['user_name'] = $get_name->first_name . " " . $get_name->last_name;
                    } else {
                        $get_consultant_id_arr[$manager_id][$manager_id]['user_name'] = "";
                    }

                    $currnet_month_sale = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale, assist_course.mcourse 
                            FROM assist_participant LEFT JOIN  assist_course ON assist_participant.course = assist_course.id
                            WHERE Month(assist_participant.created_on) = $month AND YEAR(assist_participant.created_on) = $year 
                                AND assist_participant.sales_user_id=$manager_id GROUP BY assist_course.mcourse")->queryAll();

                    $get_consultant_id_arr[$manager_id][$manager_id]['da_sale'] = 0;
                    $get_consultant_id_arr[$manager_id][$manager_id]['dm_sale'] = 0;
                    $get_consultant_id_arr[$manager_id][$manager_id]['full_payment_sale'] = 0;

                    if (!empty($currnet_month_sale)) {
                        foreach ($currnet_month_sale as $sale) {
                            if ($sale['mcourse'] == 'da') {
                                $get_consultant_id_arr[$manager_id][$manager_id]['da_sale'] = $sale['total_sale'];
                            }
                            if ($sale['mcourse'] == 'dm') {
                                $get_consultant_id_arr[$manager_id][$manager_id]['dm_sale'] = $sale['total_sale'];
                            }
                        }
                    }

                    $total_full_payment_sale = Yii::$app->db->createCommand("SELECT COUNT(*) as total_full_payment_sale FROM assist_participant 
                        WHERE Month(assist_participant.created_on) = $month AND YEAR(assist_participant.created_on) = $year AND 
                            assist_participant.sales_user_id=$manager_id AND is_full_payment=1")->queryAll();
                    if (!empty($total_full_payment_sale)) {
                        $get_consultant_id_arr[$manager_id][$manager_id]['full_payment_sale'] = $total_full_payment_sale[0]['total_full_payment_sale'];
                    }

                    $get_qb_customer_id = Yii::$app->db->createCommand("SELECT qb_customer_id FROM assist_participant 
                        WHERE sales_user_id=$manager_id AND Month(assist_participant.created_on) = $month AND YEAR(assist_participant.created_on) = $year")->queryAll();


                    $da_incentive_rate = 0;
                    $dm_incentive_rate = 0;
                    $full_payment_incentive_rate = 0;
                    $get_da_exception_rate = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_exception_rate 
                    WHERE month=$month AND years=$year AND domain='da' AND executive_id=$manager_id")->queryAll();
                    if (!empty($get_da_exception_rate)) {
                        $recent_date = array();
                        foreach ($get_da_exception_rate as $exception) {

                            $recent_date[] = $exception['created_at'];
                        }

                        foreach ($get_da_exception_rate as $exception) {
                            if (end($recent_date) == $exception['created_at']) { /* Display lastly added rules */
                                if ($get_consultant_id_arr[$manager_id][$manager_id]['da_sale'] >= $exception['min_closures'] &&
                                        $get_consultant_id_arr[$manager_id][$manager_id]['da_sale'] <= $exception['max_closures']) {
                                    $da_incentive_rate = $exception['rate'];
                                }
                            }
                        }
                    }

                    $get_dm_exception_rate = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_exception_rate 
                    WHERE month=$month AND years=$year AND domain='dm' AND executive_id=$manager_id")->queryAll();
                    if (!empty($get_dm_exception_rate)) {
                        $recent_date = array();
                        foreach ($get_dm_exception_rate as $exception) {

                            $recent_date[] = $exception['created_at'];
                        }

                        foreach ($get_dm_exception_rate as $exception) {
                            if (end($recent_date) == $exception['created_at']) { /* Display lastly added rules */
                                if ($get_consultant_id_arr[$manager_id][$manager_id]['da_sale'] >= $exception['min_closures'] &&
                                        $get_consultant_id_arr[$manager_id][$manager_id]['da_sale'] <= $exception['max_closures']) {
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
                                    if ($get_consultant_id_arr[$manager_id][$manager_id]['da_sale'] >= $incentive['min_closures'] &&
                                            $get_consultant_id_arr[$manager_id][$manager_id]['da_sale'] <= $incentive['max_closures']) {
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
                                    if ($get_consultant_id_arr[$manager_id][$manager_id]['dm_sale'] >= $incentive['min_closures'] &&
                                            $get_consultant_id_arr[$manager_id][$manager_id]['dm_sale'] <= $incentive['max_closures']) {
                                        $dm_incentive_rate = $incentive['rate'];
                                    }
                                }
                            }
                        }
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
                                if ($get_consultant_id_arr[$manager_id][$manager_id]['full_payment_sale'] >= $incentive['min_closures'] &&
                                        $get_consultant_id_arr[$manager_id][$manager_id]['full_payment_sale'] <= $incentive['max_closures']) {
                                    $full_payment_incentive_rate = $incentive['rate'];
                                }
                            }
                        }
                    }

                    $get_consultant_id_arr[$manager_id][$manager_id]['full_payment_incentive_rate'] = $full_payment_incentive_rate;
                    $get_consultant_id_arr[$manager_id][$manager_id]['dm_incentive_rate'] = $dm_incentive_rate;
                    $get_consultant_id_arr[$manager_id][$manager_id]['da_incentive_rate'] = $da_incentive_rate;

                    $get_consultant_id_arr[$manager_id][$manager_id]['fresh_payment'] = 0;
                    $get_consultant_id_arr[$manager_id][$manager_id]['all_full_payment'] = 0;
                    $get_consultant_id_arr[$manager_id][$manager_id]['payment_from_instalment'] = 0;
                    $get_consultant_id_arr[$manager_id][$manager_id]['da_fresh_payment'] = 0;
                    $get_consultant_id_arr[$manager_id][$manager_id]['dm_fresh_payment'] = 0;
                    $get_consultant_id_arr[$manager_id][$manager_id]['all_dm_payment_from_instalment'] = 0;
                    $get_consultant_id_arr[$manager_id][$manager_id]['all_da_payment_from_instalment'] = 0;

                    $get_all_qb_customer_id = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$manager_id")->queryAll();

                    $get_all_dm_qb_customer_id = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$manager_id AND assist_course.mcourse='dm' AND qb_customer_id IS NOT NULL")->queryAll();

                    $get_all_da_qb_customer_id = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$manager_id AND assist_course.mcourse='da' AND qb_customer_id IS NOT NULL")->queryAll();

                    $get_qb_customer_id_full_payment = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$manager_id AND assist_participant.is_full_payment=1 
                            AND Month(assist_participant.created_on) = $month AND YEAR(assist_participant.created_on) = $year AND 
                                qb_customer_id IS NOT NULL")->queryAll();

                    $get_dm_qb_customer_id = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$manager_id AND Month(assist_participant.created_on) = $month AND 
                            YEAR(assist_participant.created_on) = $year AND assist_course.mcourse='dm' AND 
                                qb_customer_id IS NOT NULL")->queryAll();

                    $get_da_qb_customer_id = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$manager_id AND Month(assist_participant.created_on) = $month AND 
                            YEAR(assist_participant.created_on) = $year AND assist_course.mcourse='da' AND 
                                qb_customer_id IS NOT NULL")->queryAll();

                    $full_payment_qb_id_str = "";
                    $dm_qb_id_str = "";
                    $da_qb_id_str = "";
                    $all_qb_id_str = "";
                    $all_dm_qb_id_str = "";
                    $all_da_qb_id_str = "";
                    $da_dm_qb_id = array();

                    if (!empty($get_all_qb_customer_id)) {
                        foreach ($get_all_qb_customer_id as $qb) {
                            if (!empty($qb['qb_customer_id'])) {
                                $all_qb_id_str .= "'" . $qb['qb_customer_id'] . "',";
                            }
                        }
                    }

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

                    if (!empty($get_qb_customer_id_full_payment)) {
                        foreach ($get_qb_customer_id_full_payment as $qb) {
                            $full_payment_qb_id_str .= "'" . $qb['qb_customer_id'] . "',";
                        }
                    }
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
                    $full_payment_qb_id_str = rtrim($full_payment_qb_id_str, ",");
                    $dm_qb_id_str = rtrim($dm_qb_id_str, ",");
                    $da_qb_id_str = rtrim($da_qb_id_str, ",");
                    $all_qb_id_str = rtrim($all_qb_id_str, ",");
                    $all_da_qb_id_str = rtrim($all_da_qb_id_str, ",");
                    $all_dm_qb_id_str = rtrim($all_dm_qb_id_str, ",");

                    $total_payment = $dataService->Query("SELECT count(*) FROM Payment");
                    $payment_from_instalment = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($all_qb_id_str)
                        MAXRESULTS $total_payment");
                    $da_payment_from_instalment = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($all_da_qb_id_str)
                        MAXRESULTS $total_payment");
                    $dm_payment_from_instalment = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($all_dm_qb_id_str)
                        MAXRESULTS $total_payment");
                    $all_full_payments = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($full_payment_qb_id_str) 
                        MAXRESULTS $total_payment");
                    $all_da_payments = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($da_qb_id_str) 
                        MAXRESULTS $total_payment");
                    $all_dm_payments = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($dm_qb_id_str) 
                        MAXRESULTS $total_payment");

                    if (!empty($payment_from_instalment)) {
                        foreach ($payment_from_instalment as $payment) {
                            if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                                    date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                                if (!in_array($payment->CustomerRef, $da_dm_qb_id)) {
                                    $get_consultant_id_arr[$manager_id][$manager_id]['payment_from_instalment'] += $payment->TotalAmt;
                                }
                            }
                        }
                    }

                    if (!empty($dm_payment_from_instalment)) {
                        foreach ($dm_payment_from_instalment as $payment) {
                            if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                                    date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                                if (!in_array($payment->CustomerRef, $da_dm_qb_id)) {
                                    $get_consultant_id_arr[$manager_id][$manager_id]['all_dm_payment_from_instalment'] += $payment->TotalAmt;
                                }
                            }
                        }
                    }

                    if (!empty($da_payment_from_instalment)) {
                        foreach ($da_payment_from_instalment as $payment) {
                            if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                                    date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                                if (!in_array($payment->CustomerRef, $da_dm_qb_id)) {
                                    $get_consultant_id_arr[$manager_id][$manager_id]['all_da_payment_from_instalment'] += $payment->TotalAmt;
                                }
                            }
                        }
                    }

                    if (!empty($all_full_payments)) {
                        foreach ($all_full_payments as $payment) {
                            if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                                    date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                                $get_consultant_id_arr[$manager_id][$manager_id]['all_full_payment'] += $payment->TotalAmt;
                            }
                        }
                    }

                    if (!empty($all_da_payments)) {
                        foreach ($all_da_payments as $payment) {
                            if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                                    date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                                $get_consultant_id_arr[$manager_id][$manager_id]['da_fresh_payment'] += $payment->TotalAmt;
                            }
                        }
                    }

                    if (!empty($all_dm_payments)) {
                        foreach ($all_dm_payments as $payment) {
                            if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                                    date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                                $get_consultant_id_arr[$manager_id][$manager_id]['dm_fresh_payment'] += $payment->TotalAmt;
                            }
                        }
                    }

                    $full_payment_incentive = 0;
                    $dm_incentive = 0;
                    $da_incentive = 0;
                    $da_incentive_from_payment = 0;
                    $dm_incentive_from_payment = 0;

                    $full_payment_incentive = ($get_consultant_id_arr[$manager_id][$manager_id]['all_full_payment'] *
                            $full_payment_incentive_rate) / 100;
                    $dm_incentive = ($get_consultant_id_arr[$manager_id][$manager_id]['dm_fresh_payment'] * $dm_incentive_rate) / 100;
                    $da_incentive = ($get_consultant_id_arr[$manager_id][$manager_id]['da_fresh_payment'] * $da_incentive_rate) / 100;
                    $da_incentive_from_payment = ($get_consultant_id_arr[$manager_id][$manager_id]['all_da_payment_from_instalment'] *
                            $da_incentive_rate) / 100;
                    $dm_incentive_from_payment = ($get_consultant_id_arr[$manager_id][$manager_id]['all_dm_payment_from_instalment'] *
                            $dm_incentive_rate) / 100;

                    $get_consultant_id_arr[$manager_id][$manager_id]['total_fresh_incentive'] = $da_incentive + $dm_incentive;
                    $get_consultant_id_arr[$manager_id][$manager_id]['full_payment_incentive'] = $full_payment_incentive;
                    $get_consultant_id_arr[$manager_id][$manager_id]['da_incentive_from_payment'] = $da_incentive_from_payment;
                    $get_consultant_id_arr[$manager_id][$manager_id]['dm_incentive_from_payment'] = $dm_incentive_from_payment;
                    $get_consultant_id_arr[$manager_id][$manager_id]['total_incentive_from_payment'] = $dm_incentive_from_payment +
                            $da_incentive_from_payment;


                    $total_invoices = $dataService->Query("SELECT count(*) FROM Invoice");
                    $allInvoices = $dataService->Query("SELECT * FROM Invoice MAXRESULTS $total_invoices");
                    $cnt++;
                    /* ------ End OF Manager calculation ------ */
                }
                /* ------ Start calculation of consultant ------ */

                $get_consultant_id_arr[$manager_id][$value->uid]['name'] = $value->uid;
                $get_name = DvUsers::find()->select(["first_name", "last_name"])->where(['id' => $value->uid, "status" => 1])->one();
                if (!empty($get_name)) {
                    $get_consultant_id_arr[$manager_id][$value->uid]['user_name'] = $get_name->first_name . " " . $get_name->last_name;
                } else {
                    $get_consultant_id_arr[$manager_id][$value->uid]['user_name'] = "";
                }

                $currnet_month_sale = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale, assist_course.mcourse 
                            FROM assist_participant LEFT JOIN  assist_course ON assist_participant.course = assist_course.id
                            WHERE Month(assist_participant.created_on) = $month AND YEAR(assist_participant.created_on) = $year AND 
                                assist_participant.sales_user_id=$value->uid GROUP BY assist_course.mcourse")->queryAll();

                $get_consultant_id_arr[$manager_id][$value->uid]['da_sale'] = 0;
                $get_consultant_id_arr[$manager_id][$value->uid]['dm_sale'] = 0;
                $get_consultant_id_arr[$manager_id][$value->uid]['full_payment_sale'] = 0;

                if (!empty($currnet_month_sale)) {
                    foreach ($currnet_month_sale as $sale) {
                        if ($sale['mcourse'] == 'da') {
                            $get_consultant_id_arr[$manager_id][$value->uid]['da_sale'] = $sale['total_sale'];
                        }
                        if ($sale['mcourse'] == 'dm') {
                            $get_consultant_id_arr[$manager_id][$value->uid]['dm_sale'] = $sale['total_sale'];
                        }
                    }
                }

                $total_full_payment_sale = Yii::$app->db->createCommand("SELECT COUNT(*) as total_full_payment_sale FROM assist_participant 
                        WHERE Month(assist_participant.created_on) = $month AND YEAR(assist_participant.created_on) = $year AND 
                            assist_participant.sales_user_id=$value->uid AND is_full_payment=1")->queryAll();
                if (!empty($total_full_payment_sale)) {
                    $get_consultant_id_arr[$manager_id][$value->uid]['full_payment_sale'] = $total_full_payment_sale[0]['total_full_payment_sale'];
                }

                $da_incentive_rate = 0;
                $dm_incentive_rate = 0;
                $full_payment_incentive_rate = 0;
                $get_da_exception_rate = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_exception_rate 
                    WHERE month=$month AND years=$year AND domain='da' AND executive_id=$value->uid")->queryAll();
                if (!empty($get_da_exception_rate)) {
                    $recent_date = array();
                    foreach ($get_da_exception_rate as $exception) {

                        $recent_date[] = $exception['created_at'];
                    }

                    foreach ($get_da_exception_rate as $exception) {
                        if (end($recent_date) == $exception['created_at']) { /* Display lastly added rules */
                            if ($get_consultant_id_arr[$manager_id][$value->uid]['da_sale'] >= $exception['min_closures'] &&
                                    $get_consultant_id_arr[$manager_id][$value->uid]['da_sale'] <= $exception['max_closures']) {
                                $da_incentive_rate = $exception['rate'];
                            }
                        }
                    }
                }

                $get_dm_exception_rate = Yii::$app->db->createCommand("SELECT * FROM assist_manage_monthly_incentive_exception_rate 
                    WHERE month=$month AND years=$year AND domain='dm' AND executive_id=$value->uid")->queryAll();
                if (!empty($get_dm_exception_rate)) {
                    $recent_date = array();
                    foreach ($get_dm_exception_rate as $exception) {

                        $recent_date[] = $exception['created_at'];
                    }

                    foreach ($get_dm_exception_rate as $exception) {
                        if (end($recent_date) == $exception['created_at']) { /* Display lastly added rules */
                            if ($get_consultant_id_arr[$manager_id][$value->uid]['da_sale'] >= $exception['min_closures'] &&
                                    $get_consultant_id_arr[$manager_id][$value->uid]['da_sale'] <= $exception['max_closures']) {
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
                                if ($get_consultant_id_arr[$manager_id][$value->uid]['da_sale'] >= $incentive['min_closures'] &&
                                        $get_consultant_id_arr[$manager_id][$value->uid]['da_sale'] <= $incentive['max_closures']) {
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
                                if ($get_consultant_id_arr[$manager_id][$value->uid]['dm_sale'] >= $incentive['min_closures'] &&
                                        $get_consultant_id_arr[$manager_id][$value->uid]['dm_sale'] <= $incentive['max_closures']) {
                                    $dm_incentive_rate = $incentive['rate'];
                                }
                            }
                        }
                    }
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
                            if ($get_consultant_id_arr[$manager_id][$value->uid]['full_payment_sale'] >= $incentive['min_closures'] &&
                                    $get_consultant_id_arr[$manager_id][$value->uid]['full_payment_sale'] <= $incentive['max_closures']) {
                                $full_payment_incentive_rate = $incentive['rate'];
                            }
                        }
                    }
                }

                $get_consultant_id_arr[$manager_id][$value->uid]['full_payment_incentive_rate'] = $full_payment_incentive_rate;
                $get_consultant_id_arr[$manager_id][$value->uid]['dm_incentive_rate'] = $dm_incentive_rate;
                $get_consultant_id_arr[$manager_id][$value->uid]['da_incentive_rate'] = $da_incentive_rate;

                $get_consultant_id_arr[$manager_id][$value->uid]['fresh_payment'] = 0;
                $get_consultant_id_arr[$manager_id][$value->uid]['all_full_payment'] = 0;
                $get_consultant_id_arr[$manager_id][$value->uid]['da_fresh_payment'] = 0;
                $get_consultant_id_arr[$manager_id][$value->uid]['dm_fresh_payment'] = 0;
                $get_consultant_id_arr[$manager_id][$value->uid]['all_dm_payment_from_instalment'] = 0;
                $get_consultant_id_arr[$manager_id][$value->uid]['all_da_payment_from_instalment'] = 0;

                $get_all_dm_qb_customer_id = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$value->uid AND assist_course.mcourse='dm' AND qb_customer_id IS NOT NULL")->queryAll();

                $get_all_da_qb_customer_id = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$value->uid AND assist_course.mcourse='da' AND qb_customer_id IS NOT NULL")->queryAll();

                $get_qb_customer_id_full_payment = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$value->uid AND assist_participant.is_full_payment=1 
                            AND Month(assist_participant.created_on) = $month AND YEAR(assist_participant.created_on) = $year AND 
                                qb_customer_id IS NOT NULL")->queryAll();

                $get_dm_qb_customer_id = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$value->uid AND Month(assist_participant.created_on) = $month AND 
                            YEAR(assist_participant.created_on) = $year AND assist_course.mcourse='dm'")->queryAll();

                $get_da_qb_customer_id = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$value->uid AND Month(assist_participant.created_on) = $month AND 
                            YEAR(assist_participant.created_on) = $year AND assist_course.mcourse='da'")->queryAll();

                $full_payment_qb_id_str = "";
                $dm_qb_id_str = "";
                $da_qb_id_str = "";
                $all_qb_id_str = "";
                $all_dm_qb_id_str = "";
                $all_da_qb_id_str = "";
                $da_dm_qb_id = array();

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

                if (!empty($get_qb_customer_id_full_payment)) {
                    foreach ($get_qb_customer_id_full_payment as $qb) {
                        $full_payment_qb_id_str .= "'" . $qb['qb_customer_id'] . "',";
                    }
                }
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

                $full_payment_qb_id_str = rtrim($full_payment_qb_id_str, ",");
                $dm_qb_id_str = rtrim($dm_qb_id_str, ",");
                $da_qb_id_str = rtrim($da_qb_id_str, ",");
                $all_qb_id_str = rtrim($all_qb_id_str, ",");
                $all_da_qb_id_str = rtrim($all_da_qb_id_str, ",");
                $all_dm_qb_id_str = rtrim($all_dm_qb_id_str, ",");

                $total_payment = $dataService->Query("SELECT count(*) FROM Payment");
                $da_payment_from_instalment = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($all_da_qb_id_str)
                        MAXRESULTS $total_payment");
                $dm_payment_from_instalment = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($all_dm_qb_id_str)
                        MAXRESULTS $total_payment");
                $all_full_payments = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($full_payment_qb_id_str) 
                        MAXRESULTS $total_payment");
                $all_da_payments = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($da_qb_id_str) MAXRESULTS $total_payment");
                $all_dm_payments = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($dm_qb_id_str) MAXRESULTS $total_payment");

                if (!empty($dm_payment_from_instalment)) {
                    foreach ($dm_payment_from_instalment as $payment) {
                        if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                                date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                            if (!in_array($payment->CustomerRef, $da_dm_qb_id)) {
                                $get_consultant_id_arr[$manager_id][$value->uid]['all_dm_payment_from_instalment'] += $payment->TotalAmt;
                            }
                        }
                    }
                }

                if (!empty($da_payment_from_instalment)) {
                    foreach ($da_payment_from_instalment as $payment) {
                        if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                                date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                            if (!in_array($payment->CustomerRef, $da_dm_qb_id)) {
                                $get_consultant_id_arr[$manager_id][$value->uid]['all_da_payment_from_instalment'] += $payment->TotalAmt;
                            }
                        }
                    }
                }
                if (!empty($all_full_payments)) {
                    foreach ($all_full_payments as $payment) {
                        if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                                date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                            $get_consultant_id_arr[$manager_id][$value->uid]['all_full_payment'] += $payment->TotalAmt;
                        }
                    }
                }

                if (!empty($all_da_payments)) {
                    foreach ($all_da_payments as $payment) {
                        if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                                date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                            $get_consultant_id_arr[$manager_id][$value->uid]['da_fresh_payment'] += $payment->TotalAmt;
                        }
                    }
                }

                if (!empty($all_dm_payments)) {
                    foreach ($all_dm_payments as $payment) {
                        if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                                date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                            $get_consultant_id_arr[$manager_id][$value->uid]['dm_fresh_payment'] += $payment->TotalAmt;
                        }
                    }
                }

                $full_payment_incentive = 0;
                $dm_incentive = 0;
                $da_incentive = 0;
                $da_incentive_from_payment = 0;
                $dm_incentive_from_payment = 0;

                $full_payment_incentive = ($get_consultant_id_arr[$manager_id][$value->uid]['all_full_payment'] *
                        $full_payment_incentive_rate) / 100;

                $dm_incentive = ($get_consultant_id_arr[$manager_id][$value->uid]['dm_fresh_payment'] * $dm_incentive_rate) / 100;
                $da_incentive = ($get_consultant_id_arr[$manager_id][$value->uid]['da_fresh_payment'] * $da_incentive_rate) / 100;
                $da_incentive_from_payment = ($get_consultant_id_arr[$manager_id][$value->uid]['all_da_payment_from_instalment'] *
                        $da_incentive_rate) / 100;
                $dm_incentive_from_payment = ($get_consultant_id_arr[$manager_id][$value->uid]['all_dm_payment_from_instalment'] *
                        $dm_incentive_rate) / 100;

                $get_consultant_id_arr[$manager_id][$value->uid]['total_fresh_incentive'] = $da_incentive + $dm_incentive;
                $get_consultant_id_arr[$manager_id][$value->uid]['full_payment_incentive'] = $full_payment_incentive;
                $get_consultant_id_arr[$manager_id][$value->uid]['da_incentive_from_payment'] = $da_incentive_from_payment;
                $get_consultant_id_arr[$manager_id][$value->uid]['dm_incentive_from_payment'] = $dm_incentive_from_payment;
                $get_consultant_id_arr[$manager_id][$value->uid]['total_incentive_from_payment'] = $dm_incentive_from_payment +
                        $da_incentive_from_payment;


                $get_consultant_id_arr[$manager_id][$value->uid]['manager_name'] = $cons_value['manager_name'];
            }
        }
        $filtered_data = array();
        $filtered_data['month'] = $month;
        $filtered_data['year'] = $year;

        return $this->render('incentive_report', [ 'filtered_data' => $filtered_data,
                    'get_consultant_id_arr' => $get_consultant_id_arr,
                    'data' => $data,
                    'month' => $month,
                    'year' => $year,
        ]);
    }

    public function actionTest_report() {

        $dataService = $this->quickbook_instance();
        $data = Yii::$app->request->post();
        if (!empty($data)) {
            $month = $data['month'];
            $year = $data['year'];
        } else {
            $month = date('m');
            $year = date('Y');
        }

        $get_all_mangers = DvUserMeta::find()->select(["uid"])->where(['meta_key' => "team", "meta_value" => ""])->all();

        $get_consultant_id_arr = array();

        $manager_id = 14;
        //$manager_id = 84;
        $get_all_consultant_role = DvUserMeta::find()->select(["uid"])
                        ->join('join', 'assist_users', 'assist_user_meta.uid = assist_users.id')
                        ->where(["meta_key" => "team", 'meta_value' => $manager_id, 'assist_users.status' => 1])->all();
        $cnt = 1;
        $user_id = 19;
        //$user_id = 79;
            /* ------ Start calculation of consultant ------ */

            $currnet_month_sale = Yii::$app->db->createCommand("SELECT COUNT(*) as total_sale, assist_course.mcourse 
                            FROM assist_participant LEFT JOIN  assist_course ON assist_participant.course = assist_course.id
                            WHERE Month(assist_participant.created_on) = $month AND YEAR(assist_participant.created_on) = $year AND 
                                assist_participant.sales_user_id=$user_id GROUP BY assist_course.mcourse")->queryAll();
            echo "<br>";
            echo "Start currnet_month_sale<br>";
            echo "<pre>";
            print_r($currnet_month_sale);
            echo "<br>End currnet_month_sale";
            echo "<br>";

            $get_consultant_id_arr[$manager_id][$user_id]['da_sale'] = 0;
            $get_consultant_id_arr[$manager_id][$user_id]['dm_sale'] = 0;
            $get_consultant_id_arr[$manager_id][$user_id]['full_payment_sale'] = 0;

            $total_full_payment_sale = Yii::$app->db->createCommand("SELECT COUNT(*) as total_full_payment_sale FROM assist_participant 
                        WHERE Month(assist_participant.created_on) = $month AND YEAR(assist_participant.created_on) = $year AND 
                            assist_participant.sales_user_id=$user_id AND is_full_payment=1")->queryAll();

            if (!empty($total_full_payment_sale)) {
                $get_consultant_id_arr[$manager_id][$user_id]['full_payment_sale'] = $total_full_payment_sale[0]['total_full_payment_sale'];
            }

            $da_incentive_rate = 0;
            $dm_incentive_rate = 0;
            $full_payment_incentive_rate = 0;

            $get_full_payment_incentive_rate = Yii::$app->db->createCommand("SELECT * FROM assist_fully_payment_incentive_rate 
                    WHERE month=$month AND year=$year")->queryAll();
            if (!empty($get_full_payment_incentive_rate)) {
                $recent_date = array();
                foreach ($get_full_payment_incentive_rate as $incentive) {

                    $recent_date[] = $incentive['created_at'];
                }
                foreach ($get_full_payment_incentive_rate as $incentive) {
                    if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                        if ($get_consultant_id_arr[$manager_id][$user_id]['full_payment_sale'] >= $incentive['min_closures'] &&
                                $get_consultant_id_arr[$manager_id][$user_id]['full_payment_sale'] <= $incentive['max_closures']) {
                            $full_payment_incentive_rate = $incentive['rate'];
                        }
                    }
                }
            }
            

            $get_consultant_id_arr[$manager_id][$user_id]['full_payment_incentive_rate'] = $full_payment_incentive_rate;
            $get_consultant_id_arr[$manager_id][$user_id]['dm_incentive_rate'] = $dm_incentive_rate;
            $get_consultant_id_arr[$manager_id][$user_id]['da_incentive_rate'] = $da_incentive_rate;

            $get_consultant_id_arr[$manager_id][$user_id]['fresh_payment'] = 0;
            $get_consultant_id_arr[$manager_id][$user_id]['all_full_payment'] = 0;
            $get_consultant_id_arr[$manager_id][$user_id]['da_fresh_payment'] = 0;
            $get_consultant_id_arr[$manager_id][$user_id]['dm_fresh_payment'] = 0;
            $get_consultant_id_arr[$manager_id][$user_id]['all_dm_payment_from_instalment'] = 0;
            $get_consultant_id_arr[$manager_id][$user_id]['all_da_payment_from_instalment'] = 0;

            $get_qb_customer_id_full_payment = Yii::$app->db->createCommand("SELECT DISTINCT qb_customer_id FROM assist_participant 
                    JOIN assist_course on assist_course.id=assist_participant.course 
                        WHERE sales_user_id=$user_id AND assist_participant.is_full_payment=1 
                            AND Month(assist_participant.created_on) = $month AND YEAR(assist_participant.created_on) = $year AND 
                                qb_customer_id IS NOT NULL")->queryAll();

            echo "<br>";
            echo "Start get_qb_customer_id_full_payment<br>";
            echo "<pre>";
            print_r($get_qb_customer_id_full_payment);
            echo "<br>End get_qb_customer_id_full_payment";
            echo "<br>";
            
            $full_payment_qb_id_str = "";
            $dm_qb_id_str = "";
            $da_qb_id_str = "";
            $all_qb_id_str = "";
            $all_dm_qb_id_str = "";
            $all_da_qb_id_str = "";
            $da_dm_qb_id = array();

            if (!empty($get_qb_customer_id_full_payment)) {
                foreach ($get_qb_customer_id_full_payment as $qb) {
                    $full_payment_qb_id_str .= "'" . $qb['qb_customer_id'] . "',";
                }
            }

            $full_payment_qb_id_str = rtrim($full_payment_qb_id_str, ",");
            $dm_qb_id_str = rtrim($dm_qb_id_str, ",");
            $da_qb_id_str = rtrim($da_qb_id_str, ",");
            $all_qb_id_str = rtrim($all_qb_id_str, ",");
            $all_da_qb_id_str = rtrim($all_da_qb_id_str, ",");
            $all_dm_qb_id_str = rtrim($all_dm_qb_id_str, ",");

            echo "<br>Start full_payment_qb_id_str";
            echo "<br>";
            echo $full_payment_qb_id_str;
            echo "<br>End full_payment_qb_id_str";
            echo "<br>";
            
            $total_payment = $dataService->Query("SELECT count(*) FROM Payment");
            $all_full_payments = $dataService->Query("SELECT * FROM Payment WHERE CustomerRef IN($full_payment_qb_id_str) 
                        MAXRESULTS $total_payment");

            echo "<br>";
            echo "Start all_full_payments<br>";
            echo "<pre>";
            print_r($all_full_payments);
            echo "<br>End all_full_payments";
            echo "<br>";

            if (!empty($all_full_payments)) {
                foreach ($all_full_payments as $payment) {
                    if (date('Y', strtotime($payment->MetaData->CreateTime)) == $year &&
                            date('m', strtotime($payment->MetaData->CreateTime)) == $month) {
                        $get_consultant_id_arr[$manager_id][$user_id]['all_full_payment'] += $payment->TotalAmt;
                    }
                }
            }

            $full_payment_incentive = 0;
            $dm_incentive = 0;
            $da_incentive = 0;
            $da_incentive_from_payment = 0;
            $dm_incentive_from_payment = 0;

            $full_payment_incentive = ($get_consultant_id_arr[$manager_id][$user_id]['all_full_payment'] *
                    $full_payment_incentive_rate) / 100;

            $get_consultant_id_arr[$manager_id][$user_id]['full_payment_incentive'] = $full_payment_incentive;
        
echo "<pre>";
print_r($get_consultant_id_arr);die;
        $filtered_data = array();
        $filtered_data['month'] = $month;
        $filtered_data['year'] = $year;

        return $this->render('incentive_report', [ 'filtered_data' => $filtered_data,
                    'get_consultant_id_arr' => $get_consultant_id_arr,
                    'data' => $data,
                    'month' => $month,
                    'year' => $year,
        ]);
    }

}