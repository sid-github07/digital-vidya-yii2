<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\data\ArrayDataProvider;

$this->title = 'Incentive Report';
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
                    <th scope="col" class="bg-gray">Manager</th>
                    <th scope="col" class="bg-gray">Consultant</th>
                    <th scope="col" class="bg-gray">DA</th>
                    <th scope="col" class="bg-gray">DM</th>
                    <th scope="col" class="bg-gray">Total Full Paid Sales</th>
                    <th scope="col" class="bg-gray">Incentive for Fresh Payments</th>
                    <th scope="col" class="bg-gray">Incentives from Instalment Payments</th>
                    <th scope="col" class="bg-gray">Incentives from Full Payments</th>
                    <th scope="col" class="bg-gray">Total Incentive</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($get_consultant_id_arr)) {
                    foreach ($get_consultant_id_arr as $key => $val) {
                        $total_incentive = 0;
                        if (!empty($val)) {
                            foreach ($val as $consultants) {
                                $total_incentive = $consultants['total_fresh_incentive'] + $consultants['total_incentive_from_payment'] +
                                        $consultants['full_payment_incentive'];
                                ?>
                                <tr>
                                    <td><?= $consultants['manager_name'] ?></td>
                                    <td><?= $consultants['user_name'] ?></td>
                                    <td><?= $consultants['da_sale'] ?></td>
                                    <td><?= $consultants['dm_sale'] ?></td>
                                    <td><?= $consultants['full_payment_sale'] ?></td>
                                    <td><?= number_format((float) $consultants['total_fresh_incentive'], 2, '.', ''); ?></td>
                                    <td><?= number_format((float) $consultants['total_incentive_from_payment'], 2, '.', ''); ?></td>
                                    <td><?= number_format((float) $consultants['full_payment_incentive'], 2, '.', ''); ?></td>
                                    <td><?= number_format((float) $total_incentive, 2, '.', ''); ?></td>
                                </tr>
                                <?php
                            }
                        }
                    }
                } else {
                    ?>
                    <Tr class="text-center">
                        <td>No Records Found!</td>
                    </tr>
                    <?php
                }
                ?>

            </tbody>
        </table>
    </div>
</div>