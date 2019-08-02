<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\DvUsers */

$this->title = 'Edit Course Topic: '. $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Training Topics', 'url' => ['training_topics']];
$this->params['breadcrumbs'][] = 'Edit'; ?>
<div class="container">
  <div class="row">
    <div class="col-md-10">
    	<div class="dv-users-update">
    		<?= $this->render('_form_training_topics', [ 'model' => $model ]) ?>
    	</div>
    </div>
    <div class="col-md-2"></div>
  </div>
</div>