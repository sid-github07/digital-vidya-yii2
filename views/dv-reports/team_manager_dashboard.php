<?php

use yii\helpers\Html;
use app\models\DvRegistration;
use app\models\DvUsers;
use app\models\DvUserMeta;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\data\ArrayDataProvider;

$this->title = 'Team Manager Dashboard';
$this->params['breadcrumbs'][] = "Reports";
$this->params['breadcrumbs'][] = $this->title;
$month_arr = array('1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April', '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August', '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
$min_year = 2018;
?>
<style>
    tr.manager_tbl_head th{text-align: center;}
</style>
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
<div class="row">
    <div class="col-sm-12 table-responsive">
        <table id="sales_report_tbl" class="table" border="1">
            <thead>
                <tr class="manager_tbl_head">
                    <th scope="col" class="bg-gray">Team</th>
                    <th scope="col" class="bg-gray" colspan="2">Min Target</th>
                    <th scope="col" class="bg-gray" colspan="2">Current</th>
                    <th scope="col" class="bg-gray" colspan="2">Left</th>
                    <th scope="col" class="bg-gray">Days Left (execept Sunday)</th>
                    <th scope="col" class="bg-gray" colspan="2">Per Day Sales Needed</th>
                    <th scope="col" class="bg-gray" colspan="2">Today (execept Sunday)</th>
                    <th scope="col" class="bg-gray" colspan="2">Today-1 (execept Sunday)</th>
                    <th scope="col" class="bg-gray" colspan="2">Today-2 (execept Sunday)</th>
                    <th scope="col" class="bg-gray" colspan="2">Per day Sales (execept Sunday)</th>
                </tr>
                <tr>
                    <Th scope="col" class="bg-gray"></Th>
                    <Th scope="col" class="bg-gray">DM</Th>
                    <Th scope="col" class="bg-gray">DA</Th>
                    <Th scope="col" class="bg-gray">DM</Th>
                    <Th scope="col" class="bg-gray">DA</Th>
                    <Th scope="col" class="bg-gray">DM</Th>
                    <Th scope="col" class="bg-gray">DA</Th>
                    <Th scope="col" class="bg-gray"></Th>
                    <Th scope="col" class="bg-gray">DM</Th>
                    <Th scope="col" class="bg-gray">DA</Th>
                    <Th scope="col" class="bg-gray">DM</Th>
                    <Th scope="col" class="bg-gray">DA</Th>
                    <Th scope="col" class="bg-gray">DM</Th>
                    <Th scope="col" class="bg-gray">DA</Th>
                    <Th scope="col" class="bg-gray">DM</Th>
                    <Th scope="col" class="bg-gray">DA</Th>
                    <Th scope="col" class="bg-gray">DM</Th>
                    <Th scope="col" class="bg-gray">DA</Th>
                </tr>
            </thead>
            <tbody>

            </tbody>
            <tbody>
                <?php
                $total_dm_target = 0;
                $total_da_target = 0;
                $total_dm_total_sales = 0;
                $total_da_total_sales = 0;
                $total_dm_left = 0;
                $total_da_left = 0;
                $total_left_days = 0;
                $total_dm_per_day_sales_needed = 0;
                $total_da_per_day_sales_needed = 0;
                $total_dm_today = 0;
                $total_da_today = 0;
                $total_dm_yesterday = 0;
                $total_da_yesterday = 0;
                $total_dm_day_before_yesterday = 0;
                $total_da_day_before_yesterday = 0;
                $total_dm_per_day_sales = 0;
                $total_da_per_day_sales = 0;

                if (!empty($manager_data)) {
                    foreach ($manager_data as $manager) {
                        if (!empty($manager)) {
                            $total_dm_target += $manager['dm_target'];
                            $total_da_target += $manager['da_target'];
                            $total_dm_total_sales += $manager['dm_total_sales'];
                            $total_da_total_sales += $manager['da_total_sales'];
                            $total_dm_left += $manager['dm_left'];
                            $total_da_left += $manager['da_left'];
                            $total_left_days += $manager['left_days'];
                            $total_dm_per_day_sales_needed += $manager['dm_per_day_sales_needed'];
                            $total_da_per_day_sales_needed += $manager['da_per_day_sales_needed'];
                            $total_dm_today += $manager['dm_today'];
                            $total_da_today += $manager['da_today'];
                            $total_dm_yesterday += $manager['dm_yesterday'];
                            $total_da_yesterday += $manager['da_yesterday'];
                            $total_dm_day_before_yesterday += $manager['dm_day_before_yesterday'];
                            $total_da_day_before_yesterday += $manager['da_day_before_yesterday'];
                            $total_dm_per_day_sales += $manager['dm_per_day_sales'];
                            $total_da_per_day_sales += $manager['da_per_day_sales'];
                            ?>
                            <tr>
                                <td><?= $manager['user_name'] ?></td>
                                <td><?= $manager['dm_target'] ?></td>
                                <td><?= $manager['da_target'] ?></td>
                                <td><?= $manager['dm_total_sales'] ?></td>
                                <td><?= $manager['da_total_sales'] ?></td>
                                <td><?= $manager['dm_left'] ?></td>
                                <td><?= $manager['da_left'] ?></td>
                                <td><?= $manager['left_days'] ?></td>
                                <td><?= $manager['dm_per_day_sales_needed'] ?></td>
                                <td><?= $manager['da_per_day_sales_needed'] ?></td>
                                <td><?= $manager['dm_today'] ?></td>
                                <td><?= $manager['da_today'] ?></td>
                                <td><?= $manager['dm_yesterday'] ?></td>
                                <td><?= $manager['da_yesterday'] ?></td>
                                <td><?= $manager['dm_day_before_yesterday'] ?></td>
                                <td><?= $manager['da_day_before_yesterday'] ?></td>
                                <td><?= $manager['dm_per_day_sales'] ?></td>
                                <td><?= $manager['da_per_day_sales'] ?></td>
                            </tr>
                            <?php
                            ?>
                            <?php
                        }
                    }
                    ?>
                    <tr>
                        <td><b>Total</b></td>
                        <td><b><?= $total_dm_target ?></b></td>
                        <td><b><?= $total_da_target ?></b></td>
                        <td><b><?= $total_dm_total_sales ?></b></td>
                        <td><b><?= $total_da_total_sales ?></b></td>
                        <td><b><?= $total_dm_left ?></b></td>
                        <td><b><?= $total_da_left ?></b></td>
                        <td><b><?= $total_left_days ?></b></td>
                        <td><b><?= $total_dm_per_day_sales_needed ?></b></td>
                        <td><b><?= $total_da_per_day_sales_needed ?></b></td>
                        <td><b><?= $total_dm_today ?></b></td>
                        <td><b><?= $total_da_today ?></b></td>
                        <td><b><?= $total_dm_yesterday ?></b></td>
                        <td><b><?= $total_da_yesterday ?></b></td>
                        <td><b><?= $total_dm_day_before_yesterday ?></b></td>
                        <td><b><?= $total_da_day_before_yesterday ?></b></td>
                        <td><b><?= $total_dm_per_day_sales ?></b></td>
                        <td><b><?= $total_da_per_day_sales ?></b></td>
                    </tr>
                    <?php
                } else {
                    ?>
                    <Tr class="text-center">
                        <td colspan="18">No Records Found!</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>