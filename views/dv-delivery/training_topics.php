<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\DwUsers */

$this->title = 'Create Topic';
$this->params['breadcrumbs'][] = 'Training Topics'; ?>
<div class="container">
  <div class="row">
    <div class="col-md-10">
    	<div class="dv-participant-create">
    		<?= $this->render('_form_training_topics', [ 'model' => $model, ]) ?>
    	</div>
    </div>
    <div class="col-md-2"></div>
</div>
</div>