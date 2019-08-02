<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\DwUsers */

$this->title = 'Create Batch';
$this->params['breadcrumbs'][] = ['label' => 'All Batch', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Create Batch'; ?>
<div class="container">
  <div class="row">  	
    <div class="col-md-10">
    	<div class="dv-module-create">
    		<h1> </h1>
    		<?= $this->render('_form_module', [ 'model' => $model, ]) ?>
    	</div>
    </div>
    <div class="col-md-2"></div>
  </div>
</div>