<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\MwUsers */

$this->title = 'Digital Vidya: Monthly Incentive Rate';
$second_title = 'Monthly Incentive Rate';
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
                                    $val_month = $month['month'];
                                    $total_da = $month['total_da'];
                                    $total_dm = $month['total_dm'];
                                    $total_full_payment = $month['full_payment'];
                                    ?>
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h4 class="panel-title">
                                                <a class="accordion-toggle <?php
                                                if ($val_month != date('m')) {
                                                    echo "collapsed";
                                                }
                                                ?>" data-toggle="collapse" data-parent="#accordion" href="#<?= $val_month ?>" style="display: block;">
                                                   <?php
                                                   $dateObj = DateTime::createFromFormat('!m', $val_month);
                                                   $monthName = $dateObj->format('F'); // March
                                                   ?>
                                                   <?= $monthName . ", " . $val ?>
                                                   <?php if ($val_month == date('m')) { ?>
                                                        <span class="badge label-success">Active</span>
                                                    <?php } ?>
                                                </a>
                                            </h4>
                                        </div>
                                        <div id="<?= $val_month ?>" class="panel-collapse collapse <?php if ($val_month == date('m')) echo 'in'; ?>">
                                            <div class="panel-body">
                                                <div class="row">
                                                    <div class="col-sm-12 text-center">
                                                        <div class="col-sm-4">
                                                            <?php
                                                            if (!empty($incentive_for_fresh_payments)) {
                                                                foreach ($incentive_for_fresh_payments as $fp_key => $fp_val) {
                                                                    foreach ($fp_val as $month_key => $month_val) {
                                                                        if ($fp_key == $val && $month_key == $val_month) {
                                                                            ?>
                                                                            <button class="btn btn-primary">
                                                                                Incentive for Fresh Payments : <?= $month_val['total_fresh_incentive'] ?>
                                                                            </button>
                                                                            <?php
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <?php
                                                            if (!empty($incentives_from_instalment_payments)) {
                                                                foreach ($incentives_from_instalment_payments as $fp_key => $fp_val) {
                                                                    foreach ($fp_val as $month_key => $month_val) {
                                                                        if ($fp_key == $val && $month_key == $val_month) {
                                                                            ?>
                                                                            <button class="btn btn-primary">
                                                                                Incentives from Instalment Payments : <?= $month_val['total_incentive_from_payment'] ?>
                                                                            </button>
                                                                            <?php
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <?php
                                                            if (!empty($incentives_from_full_payments)) {
                                                                foreach ($incentives_from_full_payments as $fp_key => $fp_val) {
                                                                    foreach ($fp_val as $month_key => $month_val) {
                                                                        if ($fp_key == $val && $month_key == $val_month) {
                                                                            ?>
                                                                            <button class="btn btn-primary">
                                                                                Full Payment Incentive : <?= $month_val['full_payment_incentive'] ?>
                                                                            </button>
                                                                            <?php
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="pad-10">
                                                            <h3 class="text-center label-default pad-5">
                                                                DA - Total : <?= $total_da ?>
                                                                <span id="exc_da_<?= $val_month ?>" class="exception_rate_title"></span></h3>
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
                                                                        if ($incentive['month'] == $val_month && $incentive['mcourse'] == 'da' && $incentive['year'] == $val) {
                                                                            $recent_date[] = $incentive['created_at'];
                                                                        }
                                                                    }
                                                                    foreach ($incentives as $incentive) {
                                                                        if ($incentive['month'] == $val_month && $incentive['mcourse'] == 'da' && $incentive['year'] == $val) {
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
                                                                DM - Total : <?= $total_dm ?>
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
                                                                        if ($incentive['month'] == $val_month && $incentive['mcourse'] == 'dm' && $incentive['year'] == $val) {
                                                                            $recent_date[] = $incentive['created_at'];
                                                                        }
                                                                    }
                                                                    foreach ($incentives as $incentive) {
                                                                        if ($incentive['month'] == $val_month && $incentive['mcourse'] == 'dm' && $incentive['year'] == $val) {
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
                                                    <div class="col-sm-12">
                                                        <div class="pad-10">
                                                            <h4 class="text-center label-default pad-5">
                                                                Full Payment Incentive Rate - Total Full Paument <?= $total_full_payment ?>
                                                            </h4>
                                                            <table class="table table-striped brd-1">
                                                                <thead>
                                                                <th><center>Min Closures</center></th>
                                                                <th><center>Max Closures</center></th>
                                                                <th><center>Incentive(%)</center></th>
                                                                </thead>
                                                                <?php
                                                                $rules_in_month = 0;
                                                                $month_has_exception = 0;
                                                                if (!empty($full_payment_incentives) && $month_has_exception == 0) {
                                                                    $recent_date = array();
                                                                    foreach ($full_payment_incentives as $incentive) {
                                                                        if ($incentive['month'] == $val_month && $incentive['year'] == $val) {
                                                                            $recent_date[] = $incentive['created_at'];
                                                                        }
                                                                    }
                                                                    foreach ($full_payment_incentives as $incentive) {
                                                                        if ($incentive['month'] == $val_month && $incentive['year'] == $val) {
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
                                    $val_month = $month['month'];
                                    $total_da = $month['total_da'];
                                    $total_dm = $month['total_dm'];
                                    $total_full_payment = $month['full_payment'];
                                    ?>
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h4 class="panel-title">
                                                <a class="accordion-toggle <?php
                                                if ($val_month != date('m')) {
                                                    echo "collapsed";
                                                }
                                                ?>" data-toggle="collapse" data-parent="#accordion" href="#<?= $val_month ?>" style="display: block;">
                                                   <?php
                                                   $dateObj = DateTime::createFromFormat('!m', $val_month);
                                                   $monthName = $dateObj->format('F'); // March
                                                   ?>
                                                   <?= $monthName . ", " . $val ?>
                                                   <?php if ($val_month == date('m')) { ?>
                                                        <span class="badge label-success">Active</span>
                                                    <?php } ?>
                                                </a>
                                            </h4>
                                        </div>
                                        <div id="<?= $val_month ?>" class="panel-collapse collapse <?php if ($val_month == date('m')) echo 'in'; ?>">
                                            <div class="panel-body">
                                                <div class="row">
                                                    <div class="col-sm-12 text-center">
                                                        <div class="col-sm-4">
                                                            <?php
                                                            if (!empty($incentive_for_fresh_payments)) {
                                                                foreach ($incentive_for_fresh_payments as $fp_key => $fp_val) {
                                                                    foreach ($fp_val as $month_key => $month_val) {
                                                                        if ($fp_key == $val && $month_key == $val_month) {
                                                                            ?>
                                                                            <button class="btn btn-primary">
                                                                                Incentive for Fresh Payments : <?= $month_val['total_fresh_incentive'] ?>
                                                                            </button>
                                                                            <?php
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <?php
                                                            if (!empty($incentives_from_instalment_payments)) {
                                                                foreach ($incentives_from_instalment_payments as $fp_key => $fp_val) {
                                                                    foreach ($fp_val as $month_key => $month_val) {
                                                                        if ($fp_key == $val && $month_key == $val_month) {
                                                                            ?>
                                                                            <button class="btn btn-primary">
                                                                                Incentives from Instalment Payments : <?= $month_val['total_incentive_from_payment'] ?>
                                                                            </button>
                                                                            <?php
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <?php
                                                            if (!empty($incentives_from_full_payments)) {
                                                                foreach ($incentives_from_full_payments as $fp_key => $fp_val) {
                                                                    foreach ($fp_val as $month_key => $month_val) {
                                                                        if ($fp_key == $val && $month_key == $val_month) {
                                                                            ?>
                                                                            <button class="btn btn-primary">
                                                                                Full Payment Incentive : <?= $month_val['full_payment_incentive'] ?>
                                                                            </button>
                                                                            <?php
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="pad-10">
                                                            <h3 class="text-center label-default pad-5">
                                                                DA - Total : <?= $total_da ?>
                                                                <span id="exc_da_<?= $val_month ?>" class="exception_rate_title"></span></h3>
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
                                                                        if ($incentive['month'] == $val_month && $incentive['mcourse'] == 'da' && $incentive['year'] == $val) {
                                                                            $recent_date[] = $incentive['created_at'];
                                                                        }
                                                                    }
                                                                    foreach ($incentives as $incentive) {
                                                                        if ($incentive['month'] == $val_month && $incentive['mcourse'] == 'da' && $incentive['year'] == $val) {
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
                                                                DM - Total : <?= $total_dm ?>
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
                                                                        if ($incentive['month'] == $val_month && $incentive['mcourse'] == 'dm' && $incentive['year'] == $val) {
                                                                            $recent_date[] = $incentive['created_at'];
                                                                        }
                                                                    }
                                                                    foreach ($incentives as $incentive) {
                                                                        if ($incentive['month'] == $val_month && $incentive['mcourse'] == 'dm' && $incentive['year'] == $val) {
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
                                                    <div class="col-sm-12">
                                                        <div class="pad-10">
                                                            <h4 class="text-center label-default pad-5">
                                                                Full Payment Incentive Rate - Total Full Paument <?= $total_full_payment ?>
                                                            </h4>
                                                            <table class="table table-striped brd-1">
                                                                <thead>
                                                                <th><center>Min Closures</center></th>
                                                                <th><center>Max Closures</center></th>
                                                                <th><center>Incentive(%)</center></th>
                                                                </thead>
                                                                <?php
                                                                $rules_in_month = 0;
                                                                $month_has_exception = 0;
                                                                if (!empty($full_payment_incentives) && $month_has_exception == 0) {
                                                                    $recent_date = array();
                                                                    foreach ($full_payment_incentives as $incentive) {
                                                                        if ($incentive['month'] == $val_month && $incentive['year'] == $val) {
                                                                            $recent_date[] = $incentive['created_at'];
                                                                        }
                                                                    }
                                                                    foreach ($full_payment_incentives as $incentive) {
                                                                        if ($incentive['month'] == $val_month && $incentive['year'] == $val) {
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