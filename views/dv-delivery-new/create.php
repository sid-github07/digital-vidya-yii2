<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\DwUsers */

$this->title = 'Create Course';
$this->params['breadcrumbs'][] = 'Create Course'; ?>
<div class="container">
  <div class="row">
    <div class="col-md-8">
    	 <div class="dv-participant-create">
    	 	<?= $this->render('_form_course', [ 'model' => $model, ]) ?>
    	 </div>
	</div>
	<div class="col-md-4"></div>
    </div>
</div>