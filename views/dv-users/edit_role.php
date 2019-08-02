<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\DvUsers */
// This file contain a file with a form of edit a User Role.
$this->title = 'Edit Users Role: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Dv Users Role', 'url' => ['create_role']];
$this->params['breadcrumbs'][] = 'Edit Users Role'; ?>
<div style="min-height:35px; "></div>
<div class="container">
  <div class="row">
    <div class="col-md-8">
    	<div class="dv-users-update">
    		<?= $this->render('_form_role', [ 'model' => $model, ]) ?>
    	</div>
    </div>
    <div class="col-md-4"></div>
</div>
</div>