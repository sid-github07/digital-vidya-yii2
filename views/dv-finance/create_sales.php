<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\DwUsers */

$this->title = 'Domain Normalization';
$this->params['breadcrumbs'][] = 'Create Sales'; ?>
<div class="container">
  <div class="row">
    <div class="col-md-12">
    	 <div class="dv-participant-create">
    	 	<?= $this->render('_form_sales', [ 'model' => $model, ]) ?>
    	 </div>
    	</div>
    </div>
</div>