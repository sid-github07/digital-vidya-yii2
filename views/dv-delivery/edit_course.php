<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\DvUsers */

$this->title = 'Edit Course: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Users Course', 'url' => ['create_course']];
$this->params['breadcrumbs'][] = 'Edit'; ?>
<div class="container">
  <div class="row">
    <div class="col-md-8">
    	<div class="dv-users-update">
    		<?= $this->render('_form_course', [ 'model' => $model ]) ?>
    	</div>
    </div>
    <div class="col-md-4"></div>
</div>
</div>