<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\DvUsers */
// this file contain a file with update user form.

$this->title = 'Update User: ' . $model->first_name.' '.$model->last_name;
$this->params['breadcrumbs'][] = ['label' => 'All Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update'; ?>
<div style="min-height:35px; "></div>
<div class="container">
  <div class="row">
    <div class="col-md-10">
    	 <div class="dv-users-update">
    	 	<?= $this->render('_form', [ 'model' => $model, ]) ?>
    	 </div>
    </div>
    <div class="col-md-2"></div>
  </div>
</div>