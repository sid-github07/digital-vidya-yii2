<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\MwUsers */

$this->title = 'Digital Vidya: View Exception Rate';
$second_title = 'Exception Rate';
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
            <form method="POST">
                <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />
                <div class="form-group col-md-3">
                    <select class="form-control" id="months" name="months" required="">
                        <option value="">Select Month</option>
                        <?php
                        foreach ($month_arr as $key => $val) {
                            ?>
                            <option value="<?= $key ?>" <?php if (isset($months) && $months == $key) echo "selected"; ?> ><?= $val ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <select class="form-control" id="domain" name="domain" required="">
                        <option value="">Select Domain</option>
                        <option value="dm" <?php if (isset($domain) && $domain == "dm") echo "selected"; ?> >Digital Marketing</option>
                        <option value="da" <?php if (isset($domain) && $domain == "da") echo "selected"; ?> >Digital Analytics</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <select class="form-control" id="user_id" name="user_id" required="">
                        <option value="">Select Manager / Consultant</option>
                        <optgroup label="Manager">
                            <?php
                            foreach ($users as $user) {
                                if ($user['role'] == 6) {
                                    ?>
                                    <option value="<?= $user['id'] ?>" <?php if (isset($user_id) && $user_id == $user['id']) echo "selected"; ?> >
                                        <?= $user['first_name'] . " " . $user['last_name'] ?>
                                    </option>
                                    <?php
                                }
                            }
                            ?>
                        </optgroup>
                        <optgroup label="Consultant">
                            <?php
                            foreach ($users as $user) {
                                if ($user['role'] == 2) {
                                    ?>
                                    <option value="<?= $user['id'] ?>" <?php if (isset($user_id) && $user_id == $user['id']) echo "selected"; ?> >
                                        <?= $user['first_name'] . " " . $user['last_name'] ?>
                                    </option>
                                    <?php
                                }
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <button type="submit" class="btn btn-success col-xs-12">Search</button>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="panel-group collapse_custom_settings col-md-12" id="accordion">
                <?php
                $cnt = 0;
                if (!empty($exceptions)) {
                    foreach ($exceptions as $exception) {
                        ?>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle <?php
                                    if ($cnt == 0) {
                                        echo "collapsed";
                                    }
                                    ?>" data-toggle="collapse" data-parent="#accordion" href="#<?= $cnt ?>" style="display: block;">
                                       <?php
                                       if ($cnt == 0) {
                                           ?>
                                            <span>#<?= $cnt + 1; ?> - Current Exception Rate</span>
                                        <?php } else { ?>
                                            <span>#<?= $cnt + 1; ?> - Old</span>
                                        <?php } ?>
                                    </a>
                                </h4>
                            </div>
                            <div id="<?= $cnt ?>" class="panel-collapse collapse <?php if ($cnt == 0) echo 'in'; ?>">
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-sm-10 col-sm-offset-1">
                                            <div class="pad-10">
                                                <table class="table table-striped brd-1">
                                                    <thead>
                                                    <th><center>Min Closures</center></th>
                                                    <th><center>Max Closures</center></th>
                                                    <th><center>Incentive(%)</center></th>
                                                    </thead>
                                                    <?php
                                                    foreach ($exception as $exce) {
                                                        ?>
                                                        <tr>
                                                            <td><center><?= $exce->min_closures ?></center></td>
                                                        <td><center><?= $exce->max_closures ?></center></td>
                                                        <td><center><?= $exce->rate ?></center></td>
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
                        $cnt++;
                    }
                } else {
                    ?>
                    <?php if (Yii::$app->request->post()) {
                        ?>
                        <div class="alert alert-info">
                            <p>
                                <i class="fa fa-info-circle"></i> No Exception found!</p>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>