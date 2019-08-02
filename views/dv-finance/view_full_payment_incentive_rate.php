<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\MwUsers */

$this->title = 'Digital Vidya: Full Payment Incentive Rate';
$second_title = 'Full Payment Incentive Rate';
$this->params['breadcrumbs'][] = $second_title;
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
<div style="min-height:35px; "></div>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="dv-users-create">

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
                                                    <div class="col-sm-12">
                                                        <div class="pad-10">
                                                            <table class="table table-striped brd-1">
                                                                <thead>
                                                                <th><center>Min Closures</center></th>
                                                                <th><center>Max Closures</center></th>
                                                                <th><center>Incentive(%)</center></th>
                                                                </thead>
                                                                <?php
                                                                $rules_in_month = 0;
                                                                $month_has_exception = 0;
                                                                if (!empty($incentives) && $month_has_exception == 0) {
                                                                    $recent_date = array();
                                                                    foreach ($incentives as $incentive) {
                                                                        if ($incentive['month'] == $month && $incentive['year'] == $val) {
                                                                            $recent_date[] = $incentive['created_at'];
                                                                        }
                                                                    }
                                                                    foreach ($incentives as $incentive) {
                                                                        if ($incentive['month'] == $month && $incentive['year'] == $val) {
                                                                            if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                                                                                $rules_in_month = 1;
                                                                                ?>
                                                                                <tr>
                                                                                    <td><center><?= $incentive['min_closures'] ?></center></td>
                                                                                <td><center><?= $incentive['max_closures'] ?></center></td>
                                                                                <td><center><?= $incentive['rate'] ?></center></td>
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
                                                    <div class="col-sm-12">
                                                        <div class="pad-10">
                                                            <table class="table table-striped brd-1">
                                                                <thead>
                                                                <th><center>Min Closures</center></th>
                                                                <th><center>Max Closures</center></th>
                                                                <th><center>Incentive(%)</center></th>
                                                                </thead>
                                                                <?php
                                                                $rules_in_month = 0;
                                                                $month_has_exception = 0;
                                                                if (!empty($incentives) && $month_has_exception == 0) {
                                                                    $recent_date = array();
                                                                    foreach ($incentives as $incentive) {
                                                                        if ($incentive['month'] == $month && $incentive['year'] == $val) {
                                                                            $recent_date[] = $incentive['created_at'];
                                                                        }
                                                                    }
                                                                    foreach ($incentives as $incentive) {
                                                                        if ($incentive['month'] == $month && $incentive['year'] == $val) {
                                                                            if (end($recent_date) == $incentive['created_at']) { /* Display lastly added rules */
                                                                                $rules_in_month = 1;
                                                                                ?>
                                                                                <tr>
                                                                                    <td><center><?= $incentive['min_closures'] ?></center></td>
                                                                                <td><center><?= $incentive['max_closures'] ?></center></td>
                                                                                <td><center><?= $incentive['rate'] ?></center></td>
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
            </div>
        </div>
    </div>
</div>