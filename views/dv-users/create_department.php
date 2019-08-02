<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\MwUsers */
/* This file contain a file with form Create Department */

$this->title = 'Digital Vidya: Create Department';
$second_title = 'Create Department';
$this->params['breadcrumbs'][] = $second_title; ?>
<div style="min-height:35px; "></div>
<div class="container">
  <div class="row">
    <div class="col-md-6">
    	 <div class="dv-users-create">
    	 	<?= $this->render('_form_department', [ 'model' => $model, ]) ?>
    	 </div>
    	</div>
    <div class="col-md-6"></div>
  </div>
</div>