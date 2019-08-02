<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\MwUsers */

$this->title = 'Digital Vidya: Manage Full Payment Incentive Rate';
$second_title = 'Manage Full Payment Incentive Rate';
$this->params['breadcrumbs'][] = $second_title;
?>
<div style="min-height:35px; "></div>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="dv-users-create">
                <?=
                $this->render('_form_full_payment_incentive_rate', [
                    'model' => $model,
                    'incentives' => $incentives,
                    'before_6_month' => $before_6_month,
                    'after_6_month' => $after_6_month,
                    'years' => $years
                ])
                ?>
            </div>
        </div>
    </div>
</div>