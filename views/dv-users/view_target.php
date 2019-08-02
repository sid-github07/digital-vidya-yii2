<?php

use yii\helpers\Html;
use app\models\DvCourseTarget;
use app\models\DvCourse;
use yii\data\Pagination;
use yii\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\DvUsers */
/* @var $form yii\widgets\ActiveForm */
$this->title = 'Digital Vidya: Course Target';
$second_title = 'Course Target';
$this->params['breadcrumbs'][] = $second_title;
/* This file contain a file with form to create Target by sales head */
?>
<style>
    .total_da_dm{font-weight: normal;}
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
<div class="dv-users-form row">
    <?php
    $months_arr = array(
        1 => "January",
        2 => "February",
        3 => "March",
        4 => "April",
        5 => "May",
        6 => "June",
        7 => "July",
        8 => "August",
        9 => "September",
        10 => "October",
        11 => "November",
        12 => "December"
    );
    $managers_arr = array();

    foreach ($managers as $manager) {
        if ($user_role == 6) {
            if ($user_id == $manager['id']) {
                $managers_arr[$manager['id']] = $manager['first_name'] . " " . $manager['last_name'];
            }
        } else {
            $managers_arr[$manager['id']] = $manager['first_name'] . " " . $manager['last_name'];
        }
    }
    ?>
</div>

<?php
$user_target = DvCourseTarget::find()->all();
?>
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
                                   $monthName = $dateObj->format('F');
                                   $rules_in_month = 0;
                                   $month_has_exception = 0;
                                   $total_dm = 0;
                                   $total_da = 0;
                                   $is_active_this_month = 0;
                                   if (!empty($courses) && $month_has_exception == 0) {
                                       $recent_date = array();
                                       foreach ($managers_arr as $manager_key => $manager_val) {
                                           foreach ($courses as $course) {
                                               if ($course['month'] == $month && $course['year'] == $val && $course['manager_id'] == $manager_key) {
                                                   $recent_date[] = $course['created'];
                                               }
                                           }
                                           foreach ($courses as $course) {
                                               if ($course['month'] == $month && $course['year'] == $val && $course['manager_id'] == $manager_key) {
                                                   if (end($recent_date) == $course['created']) { /* Display lastly added rules */
                                                       $rules_in_month = 1;
                                                       $total_da = $total_da + $course['da_target'];
                                                       $total_dm = $total_dm + $course['dm_target'];
                                                   }
                                               }
                                           }
                                       }
                                   }
                                   echo $monthName . ", " . $val;
                                   echo " - <span class='total_da_dm'>Total DA : " . $total_da . " , Total DM : " . $total_dm . "</span>";
                                   $is_active_this_month = 0;
                                   if (!empty($courses)) {
                                       foreach ($courses as $course) {
                                           if ($course['month'] == $month && $course['year'] == $val && $course['status'] == 1) {
                                               $is_active_this_month = 1;
                                           }
                                       }
                                   }
                                   if ($month == date('m')) {
                                       ?>
                                        <span class="badge label-success target_status" for_month="<?= (int) $month . "_" . $val ?>" status="1">
                                            Active
                                        </span>
                                        <?php
                                    } else {
                                        ?>
                                        <span class="badge label-danger target_status" for_month="<?= (int) $month . "_" . $val ?>" status="0">
                                            In-Active
                                        </span>
                                        <?php
                                    }
                                    ?>
                                </a>
                            </h4>
                        </div>
                        <div id="<?= $month ?>" class="panel-collapse collapse <?php if ($month == date('m')) echo 'in'; ?>">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="pad-10">
                                            <div class="form-group">
                                                <input type="checkbox" id="disable_month_<?= (int) $month . "_" . $val ?>" class="enable_disable_target" <?php if ($is_active_this_month == 1) echo "checked"; ?>>
                                                <label for="disable_month_<?= (int) $month . "_" . $val ?>"> 
                                                    <?php
                                                    if ($is_active_this_month == 1) {
                                                        echo "Active";
                                                    } else {
                                                        echo "In-Active";
                                                    }
                                                    ?>
                                                </label>
                                            </div>
                                            <table class="table table-striped brd-1 <?php if ($is_active_this_month == 0) echo "in_active_target_table" ?>" tab="disable_month_<?= (int) $month . "_" . $val ?>">
                                                <thead>
                                                <th><center>Manager</center></th>
                                                <th><center>DA Target</center></th>
                                                <th><center>DM Target</center></th>
                                                <th><center>Incentive</center></th>
                                                </thead>
                                                <?php
                                                $rules_in_month = 0;
                                                $month_has_exception = 0;
                                                if (!empty($courses) && $month_has_exception == 0) {
                                                    $recent_date = array();
                                                    foreach ($managers_arr as $manager_key => $manager_val) {
                                                        foreach ($courses as $course) {
                                                            if ($course['month'] == $month && $course['year'] == $val && $course['manager_id'] == $manager_key) {
                                                                $recent_date[] = $course['created'];
                                                            }
                                                        }
                                                        foreach ($courses as $course) {
                                                            if ($course['month'] == $month && $course['year'] == $val && $course['manager_id'] == $manager_key) {
                                                                if (end($recent_date) == $course['created']) { /* Display lastly added rules */
                                                                    $rules_in_month = 1;
                                                                    ?>
                                                                    <tr>
                                                                        <td><Center><?= $managers_arr[$course['manager_id']] ?></Center></td>
                                                                    <td><center><?= $course['da_target'] ?></center></td>
                                                                    <td><center><?= $course['dm_target'] ?></center></td>
                                                                    <td><center><?= $course['incentive'] ?></center></td>
                                                                    </tr>
                                                                    <?php
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                                if ($rules_in_month == 0) {
                                                    ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center">No Incentive Rate Found!</td>
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
                                   $monthName = $dateObj->format('F');
                                   $is_active_this_month = 0;
                                   $rules_in_month = 0;
                                   $month_has_exception = 0;
                                   $total_dm = 0;
                                   $total_da = 0;
                                   if (!empty($courses) && $month_has_exception == 0) {
                                       $recent_date = array();
                                       foreach ($managers_arr as $manager_key => $manager_val) {
                                           foreach ($courses as $course) {
                                               if ($course['month'] == $month && $course['year'] == $val && $course['manager_id'] == $manager_key) {
                                                   $recent_date[] = $course['created'];
                                               }
                                           }
                                           foreach ($courses as $course) {
                                               if ($course['month'] == $month && $course['year'] == $val && $course['manager_id'] == $manager_key) {
                                                   if (end($recent_date) == $course['created']) { /* Display lastly added rules */
                                                       $rules_in_month = 1;
                                                       $total_da = $total_da + $course['da_target'];
                                                       $total_dm = $total_dm + $course['dm_target'];
                                                   }
                                               }
                                           }
                                       }
                                   }
                                   echo $monthName . ", " . $val;
                                   echo " - <span class='total_da_dm'>Total DA : " . $total_da . " , Total DM : " . $total_dm . "</span>";
                                   $is_active_this_month = 0;
                                   if (!empty($courses)) {
                                       foreach ($courses as $course) {
                                           if ($course['month'] == $month && $course['year'] == $val && $course['status'] == 1) {
                                               $is_active_this_month = 1;
                                           }
                                       }
                                   }
                                   if ($month == date('m')) {
                                       ?>
                                        <span class="badge label-success target_status" for_month="<?= (int) $month . "_" . $val ?>" status="1">
                                            Active
                                        </span>
                                        <?php
                                    } else {
                                        ?>
                                        <span class="badge label-danger target_status" for_month="<?= (int) $month . "_" . $val ?>" status="0">
                                            In-Active
                                        </span>
                                        <?php
                                    }
                                    ?>
                                </a>
                            </h4>
                        </div>
                        <div id="<?= $month ?>" class="panel-collapse collapse <?php if ($month == date('m')) echo 'in'; ?>">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="pad-10">
                                            <div class="form-group">
                                                <input type="checkbox" id="disable_month_<?= (int) $month . "_" . $val ?>" class="enable_disable_target" <?php if ($is_active_this_month == 1) echo "checked"; ?>>
                                                <label for="disable_month_<?= (int) $month . "_" . $val ?>"> 
                                                    <?php
                                                    if ($is_active_this_month == 1) {
                                                        echo "Active";
                                                    } else {
                                                        echo "In-Active";
                                                    }
                                                    ?>
                                                </label>
                                            </div>
                                            <table class="table table-striped brd-1 <?php if ($is_active_this_month == 0) echo "in_active_target_table" ?>" tab="disable_month_<?= (int) $month . "_" . $val ?>">
                                                <thead>
                                                <th><center>Manager</center></th>
                                                <th><center>DA Target</center></th>
                                                <th><center>DM Target</center></th>
                                                <th><center>Incentive</center></th>
                                                </thead>
                                                <?php
                                                $rules_in_month = 0;
                                                $month_has_exception = 0;
                                                if (!empty($courses) && $month_has_exception == 0) {
                                                    $recent_date = array();
                                                    foreach ($managers_arr as $manager_key => $manager_val) {
                                                        foreach ($courses as $course) {
                                                            if ($course['month'] == $month && $course['year'] == $val && $course['manager_id'] == $manager_key) {
                                                                $recent_date[] = $course['created'];
                                                            }
                                                        }
                                                        foreach ($courses as $course) {
                                                            if ($course['month'] == $month && $course['year'] == $val && $course['manager_id'] == $manager_key) {
                                                                if (end($recent_date) == $course['created']) { /* Display lastly added rules */
                                                                    $rules_in_month = 1;
                                                                    ?>
                                                                    <tr>
                                                                        <td><Center><?= $managers_arr[$course['manager_id']] ?></Center></td>
                                                                    <td><center><?= $course['da_target'] ?></center></td>
                                                                    <td><center><?= $course['dm_target'] ?></center></td>
                                                                    <td><center><?= $course['incentive'] ?></center></td>
                                                                    </tr>
                                                                    <?php
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                                if ($rules_in_month == 0) {
                                                    ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center">No Incentive Rate Found!</td>
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
<script>
    function remove_required() {
        var status = $('#dvusers-status').val();
        if (status == 0) {
            $('input, select, textarea').removeAttr('required');
        }
    }
    function manage_month(year) {
        var d = new Date();
        var current_month = d.getMonth();
        current_month = current_month + 1;
        var current_year = (new Date()).getFullYear();
        if (current_year == year) {
            for (var i = 1; i < current_month; i++) {
                $("#dvcoursetarget-month option[value='" + i + "']").attr("disabled", true);
            }
        } else {
            for (var i = 1; i < 12; i++) {
                $("#dvcoursetarget-month option[value='" + i + "']").attr("disabled", false);
            }
        }
    }


</script>