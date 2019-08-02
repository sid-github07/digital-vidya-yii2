<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\DvUsers */

$this->title = 'Domain Normalization: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Sales', 'url' => ['create_sales']];
$this->params['breadcrumbs'][] = 'Edit'; ?>
<div class="container">
  <div class="row">
    <div class="col-md-12">
    	<div class="dv-users-update">
    		<?= $this->render('_form_sales', [ 'model' => $model ]) ?>
    	</div>
    </div>
</div>
</div>