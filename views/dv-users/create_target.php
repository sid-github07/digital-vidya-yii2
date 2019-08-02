<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\MwUsers */
/* This file will call the form taget file */


$this->title = 'Digital Vidya: Create Course Target';
$second_title = 'Create Course Target';
$this->params['breadcrumbs'][] = $second_title;
?>
<div style="min-height:35px; "></div>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="dv-users-create">
                <?= $this->render('_form_target', [ 'model' => $model, 
                    'managers' => $managers, 
                    "user_role" => $user_role, 
                    "user_id" => $user_id,
                    "before_6_month" => $before_6_month,
                    "courses" => $courses,
                    "after_6_month" => $after_6_month,
                    "years" => $years,
                        ]) ?>
            </div>
        </div>
    </div>
</div>