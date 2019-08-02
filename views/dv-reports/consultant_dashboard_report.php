<?php

use yii\helpers\Html;
use app\models\DvRegistration;
use app\models\DvUsers;
use app\models\DvUserMeta;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\data\ArrayDataProvider;

$this->title = 'Consultant Dashboard';
$this->params['breadcrumbs'][] = "Reports";
$this->params['breadcrumbs'][] = $this->title;
$month_arr = array('1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April', '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August', '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
$min_year = 2018;
?>
<div class="dv-consultant-report">
    <?php
    $form = ActiveForm::begin(['id' => 'sales-report-form', 'method' => 'post']);
    $select = 'selected="selected"';
    ?>
    <div class="col-md-5 form-group">
        <select class="form-control" name="month" id="month">
            <option value="">--- Select Month ---</option>
            <?php foreach ($month_arr as $month_key => $month_val) { ?>
                <option value="<?= $month_key ?>"  <?php if ($filtered_data['month'] == $month_key) echo "selected"; ?>>
                    <?= $month_val ?>
                </option>
            <?php } ?>
        </select>
    </div>
    <div class="col-md-5 form-group monthly period_inputs" >  
        <select class="form-control" name="year" id="year">
            <option value="">--- Select Year ---</option>
            <?php for ($i = $min_year; $i <= date('Y'); $i++) { ?>
                <option value="<?= $i ?>"  <?php if ($filtered_data['year'] == $i) echo "selected"; ?>>
                    <?= $i ?>
                </option>
            <?php } ?>
        </select>
    </div>
    <div class="col-md-2 form-group">
        <input type="submit" value="Filter" class="btn btn-success">
        &nbsp;
        <a href="" class="btn btn-info">Reset</a>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<!-- DM Table -->
<div class="row">
    <div class="col-sm-12 table-responsive">
        <table id="sales_report_tbl" class="table" border="1">
            <thead>
                <tr>
                    <th scope="col" class="bg-gray">Name</th>
                    <th scope="col" class="bg-gray">Sales</th>
                    <th scope="col" class="bg-gray">Min Target</th>
                    <th scope="col" class="bg-gray">Left</th>
                    <th scope="col" class="bg-gray">Days Left</th>
                    <th scope="col" class="bg-gray">Today</th>
                    <th scope="col" class="bg-gray">Today -1</th>
                    <th scope="col" class="bg-gray">Today -2</th>
                    <th scope="col" class="bg-gray">Per Day Sales till now</th>
                    <th scope="col" class="bg-gray">Per Day Sales Needed</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_sales_in_period = 0;
                $total_min_target_in_period = 0;
                $total_left_in_period = 0;
                $total_left_days_in_period = 0;
                $total_today_days_in_period = 0;
                $total_yesterday_in_period = 0;
                $total_day_before_yesterday_in_period = 0;
                $total_per_day_sales_till_now_in_period = 0;
                $total_per_day_sales_in_period = 0;

                if (!empty($get_consultant_id_arr_dm)) {
                    foreach ($get_consultant_id_arr_dm as $consultants) {
                        if (!empty($consultants)) {

                            $chk_manager_cnt = 0;
                            $manager_name = "";
                            foreach ($consultants as $consultant) {
                                if ($chk_manager_cnt == 0) {
                                    $manager_name = $consultant['user_name'];
                                }

                                $total_sales_in_period += $consultant['sales_current_month'];
                                $total_min_target_in_period += $consultant['min_target'];
                                $total_left_in_period += $consultant['left'];
                                $total_left_days_in_period += $consultant['left_days'];
                                $total_today_days_in_period += $consultant['today'];
                                $total_yesterday_in_period += $consultant['yesterday'];
                                $total_day_before_yesterday_in_period += $consultant['day_before_yesterday'];
                                $total_per_day_sales_till_now_in_period += $consultant['per_day_sales_till_now'];
                                $total_per_day_sales_in_period += $consultant['per_day_sales'];
                                ?>
                                <tr>
                                    <td><?= $consultant['user_name'] ?></td>
                                    <td><?= $consultant['sales_current_month'] ?></td>
                                    <td><?= $consultant['min_target'] ?></td>
                                    <td><?= $consultant['left'] ?></td>
                                    <td><?= $consultant['left_days'] ?></td>
                                    <td><?= $consultant['today'] ?></td>
                                    <td><?= $consultant['yesterday'] ?></td>
                                    <td><?= $consultant['day_before_yesterday'] ?></td>
                                    <td><?= $consultant['per_day_sales_till_now'] ?></td>
                                    <td><?= $consultant['per_day_sales'] ?></td>
                                </tr>
                                <?php
                                $chk_manager_cnt++;
                            }
                            ?>
                            <?php
                        }
                    }
                    ?>
                    <tr style="background-color: #999;">
                        <td><b>Total DM</b></td>
                        <td><b><?= $total_sales_in_period ?></b></td>
                        <td><b><?= $total_min_target_in_period ?></b></td>
                        <td><b><?= $total_left_in_period ?></b></td>
                        <td><b><?= $total_left_days_in_period ?></b></td>
                        <td><b><?= $total_today_days_in_period ?></b></td>
                        <td><b><?= $total_yesterday_in_period ?></b></td>
                        <td><b><?= $total_day_before_yesterday_in_period ?></b></td>
                        <td><b><?= $total_per_day_sales_till_now_in_period ?></b></td>
                        <td><b><?= $total_per_day_sales_in_period ?></b></td>
                    </tr>
                    <?php
                } else {
                    ?>
                    <Tr class="text-center">
                        <td colspan="10">No Records Found!</td>
                    </tr>
                    <?php
                }
                ?>

            </tbody>
        </table>
    </div>
</div>
<!-- DA Table -->
<div class="row">
    <div class="col-sm-12 table-responsive">
        <table id="sales_report_tbl" class="table" border="1">
            <thead>
                <tr>
                    <th scope="col" class="bg-gray">Name</th>
                    <th scope="col" class="bg-gray">Sales</th>
                    <th scope="col" class="bg-gray">Min Target</th>
                    <th scope="col" class="bg-gray">Left</th>
                    <th scope="col" class="bg-gray">Days Left</th>
                    <th scope="col" class="bg-gray">Today</th>
                    <th scope="col" class="bg-gray">Today -1</th>
                    <th scope="col" class="bg-gray">Today -2</th>
                    <th scope="col" class="bg-gray">Per Day Sales till now</th>
                    <th scope="col" class="bg-gray">Per Day Sales Needed</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_sales_in_period = 0;
                $total_min_target_in_period = 0;
                $total_left_in_period = 0;
                $total_left_days_in_period = 0;
                $total_today_days_in_period = 0;
                $total_yesterday_in_period = 0;
                $total_day_before_yesterday_in_period = 0;
                $total_per_day_sales_till_now_in_period = 0;
                $total_per_day_sales_in_period = 0;

                if (!empty($get_consultant_id_arr)) {
                    foreach ($get_consultant_id_arr as $consultants) {
                        if (!empty($consultants)) {

                            $chk_manager_cnt = 0;
                            $manager_name = "";
                            foreach ($consultants as $consultant) {
                                if ($chk_manager_cnt == 0) {
                                    $manager_name = $consultant['user_name'];
                                }

                                $total_sales_in_period += $consultant['sales_current_month'];
                                $total_min_target_in_period += $consultant['min_target'];
                                $total_left_in_period += $consultant['left'];
                                $total_left_days_in_period += $consultant['left_days'];
                                $total_today_days_in_period += $consultant['today'];
                                $total_yesterday_in_period += $consultant['yesterday'];
                                $total_day_before_yesterday_in_period += $consultant['day_before_yesterday'];
                                $total_per_day_sales_till_now_in_period += $consultant['per_day_sales_till_now'];
                                $total_per_day_sales_in_period += $consultant['per_day_sales'];
                                ?>
                                <tr>
                                    <td><?= $consultant['user_name'] ?></td>
                                    <td><?= $consultant['sales_current_month'] ?></td>
                                    <td><?= $consultant['min_target'] ?></td>
                                    <td><?= $consultant['left'] ?></td>
                                    <td><?= $consultant['left_days'] ?></td>
                                    <td><?= $consultant['today'] ?></td>
                                    <td><?= $consultant['yesterday'] ?></td>
                                    <td><?= $consultant['day_before_yesterday'] ?></td>
                                    <td><?= $consultant['per_day_sales_till_now'] ?></td>
                                    <td><?= $consultant['per_day_sales'] ?></td>
                                </tr>
                                <?php
                                $chk_manager_cnt++;
                            }
                            ?>
                            <?php
                        }
                    }
                    ?>
                    <tr style="background-color: #999;">
                        <td><b>Total DA</b></td>
                        <td><b><?= $total_sales_in_period ?></b></td>
                        <td><b><?= $total_min_target_in_period ?></b></td>
                        <td><b><?= $total_left_in_period ?></b></td>
                        <td><b><?= $total_left_days_in_period ?></b></td>
                        <td><b><?= $total_today_days_in_period ?></b></td>
                        <td><b><?= $total_yesterday_in_period ?></b></td>
                        <td><b><?= $total_day_before_yesterday_in_period ?></b></td>
                        <td><b><?= $total_per_day_sales_till_now_in_period ?></b></td>
                        <td><b><?= $total_per_day_sales_in_period ?></b></td>
                    </tr>
                    <?php
                } else {
                    ?>
                    <Tr class="text-center">
                        <td colspan="10">No Records Found!</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>