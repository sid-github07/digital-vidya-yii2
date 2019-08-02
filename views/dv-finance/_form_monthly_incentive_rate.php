<?php

use yii\helpers\Html;
use yii\data\Pagination;
use yii\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\DvIncentive;

$month_arr = array('1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April', '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August', '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
?>
<style>
    .panel-heading .accordion-toggle:after {
        /* symbol for "opening" panels */
        font-family: 'Glyphicons Halflings';
        content: "\e113";
        float: right;
        color: #000;
        font-size: 14px;
        font-weight: 400;
    }
    .panel-heading .accordion-toggle.collapsed:after {
        /* symbol for "collapsed" panels */
        content: "\e114";    /* adjust as needed, taken from bootstrap.css */
    }
    .collapse_custom_settings .panel-title{    font-weight: bold;    font-size: 14px;}
    .collapse_custom_settings .panel-default > .panel-heading{    background-color: #ccc;}

</style>
<div class="dv-users-form" id="monthly_incentive_rate">


    <div class="row">
        <?php
        $form = ActiveForm::begin([
                    'fieldConfig' => [
                        'options' => ['class' => 'form-group col-md-4']
        ]]);
        ?>
        <?=
        $form->field($model, 'year')->dropDownList(
                [date('Y') => date('Y'), date("Y") + 1 => date('Y') + 1], ['prompt' => 'Select Year'])->label(false);
        ?>
        <?=
        $form->field($model, 'month')->dropDownList(
                ['1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April', '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August', '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'], ['prompt' => 'Select Month', 'multiple' => true])->label(false);
        ?>
        <?= $form->field($model, 'mcourse')->dropDownList(['dm' => 'Digital Marketing', 'da' => 'Digital Analytics'], ['prompt' => 'Select Domain'])->label(false); ?>
        <div class="row" id="min_max_rev_div">
            <div class="col-sm-12">
                <div class="col-sm-4">
                    <div class="form-group">
                        <input type="number" class="form-control" placeholder="Min Closures" name="DvManageMonthlyIncentiveRate[min_closures][]" id="min_closures_0" required="required" value="0" readonly=""/>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <input type="number" class="form-control max_closures_val" placeholder="Max Closures" name="DvManageMonthlyIncentiveRate[max_closures][]" id="max_closures_0" required="required" value="<?= $model->max_closures ?>"/>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <input type="number" class="form-control" placeholder="Rate (%)" name="DvManageMonthlyIncentiveRate[rate][]" id="rate" required="required" value="<?= $model->rate ?>" max="100" min="0"/>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div id='closures_rate_box' class="form-group"  style="float:left;width:100%"> </div>
            </div>
            <div class="col-sm-12">
                <div class="col-sm-2 pull-right text-right">
                    <button type="button" id="add_closures_range" class="btn btn-info">
                        <i class="fa fa-plus"></i>
                    </button>
                    <button type='button' id='remove_closures_button' class="btn btn-danger" style="display: none;">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <?php if ($model->isNewRecord) { ?>
                    <button type="submit" class="btn btn-success">Add Rate</button>
                <?php } else { ?>
                    <button type="submit" class="btn btn-primary">Update Rate</button>
                <?php } ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <?php if ($model->isNewRecord) { ?>
    <?php } else { ?>

    <?php } ?>
</div>
<div class="panel-group collapse_custom_settings" id="accordion">
    <?php
    foreach ($years as $key => $val) {
        foreach ($before_6_month as $before_key => $before_val) {
            asort($before_val);
            if ($val == $before_key) {
                foreach ($before_val as $month) {
                    ?>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a class="accordion-toggle <?php
                                if ($month != date('m')) {
                                    echo "collapsed";
                                }
                                ?>" data-toggle="collapse" data-parent="#accordion" href="#<?= $month ?>" style="display: block;">
                                   <?php
                                   $dateObj = DateTime::createFromFormat('!m', $month);
                                   $monthName = $dateObj->format('F'); // March
                                   ?>
                                   <?= $monthName . ", " . $val ?>
                                   <?php if ($month == date('m')) { ?>
                                        <span class="badge label-success">Active</span>
                                    <?php } ?>
                                </a>
                            </h4>
                        </div>
                        <div id="<?= $month ?>" class="panel-collapse collapse <?php if ($month == date('m')) echo 'in'; ?>">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="pad-10">
                                            <h3 class="text-center label-default pad-5">
                                                DA
                                                <span id="exc_da_<?= $month ?>" class="exception_rate_title"></span></h3>
                                            </h3>
                                            <table class="table table-striped brd-1">
                                                <thead>
                                                <th>Min Closures</th>
                                                <th>Max Closures</th>
                                                <th>Incentive(%)</th>
                                                </thead>
                                                <?php
                                                $rules_in_month = 0;
                                                $month_has_exception = 0;
                                                if (!empty($incentives) && $month_has_exception == 0) {
                                                    $recent_date = array();
                                                    foreach ($incentives as $incentive) {
                                                        if ($incentive['month'] == $month && $incentive['mcourse'] == 'da' && $incentive['year'] == $val) {
                                                            $recent_date[] = $incentive['created_at'];
                                                        }
                                                    }
                                                    foreach ($incentives as $incentive) {
                                                        if ($incentive['month'] == $month && $incentive['mcourse'] == 'da' && $incentive['year'] == $val) {
                                                            if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                                                                $rules_in_month = 1;
                                                                ?>
                                                                <tr>
                                                                    <td><?= $incentive['min_closures'] ?></td>
                                                                    <td><?= $incentive['max_closures'] ?></td>
                                                                    <td><?= $incentive['rate'] ?></td>
                                                                </tr>
                                                                <?php
                                                            }
                                                        }
                                                    }
                                                }
                                                if ($rules_in_month == 0) {
                                                    ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center">No Incentive Rate Found!</td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="pad-10">
                                            <h3 class="text-center label-default pad-5">
                                                DM
                                                <span id="exc_dm_<?= $key ?>" class="exception_rate_title"></span></h3>
                                            </h3>
                                            <table class="table table-striped brd-1">
                                                <thead>
                                                <th>Min Closures</th>
                                                <th>Max Closures</th>
                                                <th>Incentive(%)</th>
                                                </thead>
                                                <?php
                                                $rules_in_month = 0;
                                                $month_has_exception = 0;
                                                if (!empty($incentives) && $month_has_exception == 0) {
                                                    $recent_date = array();
                                                    foreach ($incentives as $incentive) {
                                                        if ($incentive['month'] == $month && $incentive['mcourse'] == 'dm' && $incentive['year'] == $val) {
                                                            $recent_date[] = $incentive['created_at'];
                                                        }
                                                    }
                                                    foreach ($incentives as $incentive) {
                                                        if ($incentive['month'] == $month && $incentive['mcourse'] == 'dm' && $incentive['year'] == $val) {
                                                            if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                                                                $rules_in_month = 1;
                                                                ?>
                                                                <tr>
                                                                    <td><?= $incentive['min_closures'] ?></td>
                                                                    <td><?= $incentive['max_closures'] ?></td>
                                                                    <td><?= $incentive['rate'] ?></td>
                                                                </tr>
                                                                <?php
                                                            }
                                                        }
                                                    }
                                                }
                                                if ($rules_in_month == 0) {
                                                    ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center">No Incentive Rate Found!</td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
        }
        foreach ($after_6_month as $after_key => $after_val) {
            asort($after_val);
            if ($val == $after_key) {
                foreach ($after_val as $month) {
                    ?>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a class="accordion-toggle <?php
                                if ($month != date('m')) {
                                    echo "collapsed";
                                }
                                ?>" data-toggle="collapse" data-parent="#accordion" href="#<?= $month ?>" style="display: block;">
                                   <?php
                                   $dateObj = DateTime::createFromFormat('!m', $month);
                                   $monthName = $dateObj->format('F'); // March
                                   ?>
                                   <?= $monthName . ", " . $val ?>
                                   <?php if ($month == date('m')) { ?>
                                        <span class="badge label-success">Active</span>
                                    <?php } ?>
                                </a>
                            </h4>
                        </div>
                        <div id="<?= $month ?>" class="panel-collapse collapse <?php if ($month == date('m')) echo 'in'; ?>">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="pad-10">
                                            <h3 class="text-center label-default pad-5">
                                                DA
                                                <span id="exc_da_<?= $month ?>" class="exception_rate_title"></span></h3>
                                            </h3>
                                            <table class="table table-striped brd-1">
                                                <thead>
                                                <th>Min Closures</th>
                                                <th>Max Closures</th>
                                                <th>Incentive(%)</th>
                                                </thead>
                                                <?php
                                                $rules_in_month = 0;
                                                $month_has_exception = 0;
                                                if (!empty($incentives) && $month_has_exception == 0) {
                                                    $recent_date = array();
                                                    foreach ($incentives as $incentive) {
                                                        if ($incentive['month'] == $month && $incentive['mcourse'] == 'da' && $incentive['year'] == $val) {
                                                            $recent_date[] = $incentive['created_at'];
                                                        }
                                                    }
                                                    foreach ($incentives as $incentive) {
                                                        if ($incentive['month'] == $month && $incentive['mcourse'] == 'da' && $incentive['year'] == $val) {
                                                            if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                                                                $rules_in_month = 1;
                                                                ?>
                                                                <tr>
                                                                    <td><?= $incentive['min_closures'] ?></td>
                                                                    <td><?= $incentive['max_closures'] ?></td>
                                                                    <td><?= $incentive['rate'] ?></td>
                                                                </tr>
                                                                <?php
                                                            }
                                                        }
                                                    }
                                                }
                                                if ($rules_in_month == 0) {
                                                    ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center">No Incentive Rate Found!</td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="pad-10">
                                            <h3 class="text-center label-default pad-5">
                                                DM
                                                <span id="exc_dm_<?= $key ?>" class="exception_rate_title"></span></h3>
                                            </h3>
                                            <table class="table table-striped brd-1">
                                                <thead>
                                                <th>Min Closures</th>
                                                <th>Max Closures</th>
                                                <th>Incentive(%)</th>
                                                </thead>
                                                <?php
                                                $rules_in_month = 0;
                                                $month_has_exception = 0;
                                                if (!empty($incentives) && $month_has_exception == 0) {
                                                    $recent_date = array();
                                                    foreach ($incentives as $incentive) {
                                                        if ($incentive['month'] == $month && $incentive['mcourse'] == 'dm' && $incentive['year'] == $val) {
                                                            $recent_date[] = $incentive['created_at'];
                                                        }
                                                    }
                                                    foreach ($incentives as $incentive) {
                                                        if ($incentive['month'] == $month && $incentive['mcourse'] == 'dm' && $incentive['year'] == $val) {
                                                            if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                                                                $rules_in_month = 1;
                                                                ?>
                                                                <tr>
                                                                    <td><?= $incentive['min_closures'] ?></td>
                                                                    <td><?= $incentive['max_closures'] ?></td>
                                                                    <td><?= $incentive['rate'] ?></td>
                                                                </tr>
                                                                <?php
                                                            }
                                                        }
                                                    }
                                                }
                                                if ($rules_in_month == 0) {
                                                    ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center">No Incentive Rate Found!</td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
        }
    }
    ?>
</div>