<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\DvUsers */

$this->title = 'Edit Incentive: ';
$this->params['breadcrumbs'][] = ['label' => 'Currency', 'url' => ['create_incentive']];
$this->params['breadcrumbs'][] = 'Edit Incentive'; ?>
<div style="min-height:35px; "></div>
<div class="container">
  <div class="row">
    <div class="col-md-10">
    	 <div class="dv-users-update">
    	 	<?= $this->render('_form_incentive', [ 'model' => $model ]) ?>
    	 </div>
    	</div>
    <div class="col-md-2"></div>
  </div>
</div>