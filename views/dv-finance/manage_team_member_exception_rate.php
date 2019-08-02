<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\MwUsers */

$this->title = 'Digital Vidya: Manage Monthly Incentive Rate';
$second_title = 'Manage Monthly Incentive Rate';
$this->params['breadcrumbs'][] = $second_title;
?>
<div style="min-height:35px; "></div>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="dv-users-create">
                <?=
                $this->render('_form_team_member_exception_rate', [
                    'model' => $model,
                    'executives' => $executives,
                ])
                ?>
            </div>
        </div>
    </div>
</div>