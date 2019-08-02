<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\DvUsers */

/* This file will call form target page to edit te created target*/

$monthNum  = $model->month;
$dateObj   = DateTime::createFromFormat('!m', $monthNum);
$monthName = $dateObj->format('F'); // March

$this->title = 'Edit Course Target: ' . strtoupper($model->course) . " for ".$monthName.",".$model->year;
$this->params['breadcrumbs'][] = ['label' => 'Dv Course Target', 'url' => ['create_target']];
$this->params['breadcrumbs'][] = 'Edit Course Target'; ?>
<div style="min-height:35px; "></div>
<div class="container">
  <div class="row">
    <div class="col-md-12">
    	<div class="dv-users-update">
    		<?= $this->render('_form_target', [ 'model' => $model, ]) ?>
    	</div>
    </div>
</div>
</div>