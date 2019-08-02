<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\MwUsers */

$this->title = 'Digital Vidya: Create Incentive in Per(%)';
$second_title = 'Create Incentive';
$this->params['breadcrumbs'][] = $second_title; ?>
<div style="min-height:35px; "></div>
<div class="container">
  <div class="row">
    <div class="col-md-10">
    	 <div class="dv-users-create">
    	 	<?= $this->render('_form_incentive', [ 'model' => $model, ]) ?>
    	 </div>
    	</div>
    <div class="col-md-2"></div>
  </div>
</div>