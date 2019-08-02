<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\DvUsers */
/* This file contain a file with form Edit Department */

$this->title = 'Edit Users Team: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Dv Users Department', 'url' => ['create_department']];
$this->params['breadcrumbs'][] = 'Edit Users Department'; ?>
<div style="min-height:35px; "></div>
<div class="container">
  <div class="row">
    <div class="col-md-6">
    	 <div class="dv-users-update">
    	 	<?= $this->render('_form_department', [ 'model' => $model ]) ?>
    	 </div>
    	</div>
    <div class="col-md-6"></div>
  </div>
</div>