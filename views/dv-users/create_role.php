<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* This file contain a file with form Create User Role */

$this->title = 'Digital Vidya: Create User Role';
$second_title = 'Create User Role';
$this->params['breadcrumbs'][] = $second_title; ?>
<div style="min-height:35px; "></div>
<div class="container">
  <div class="row">
    <div class="col-md-8">
    	 <div class="dv-users-create">
    	 	<?= $this->render('_form_role', [ 'model' => $model, ]) ?>
    	 </div>
    	</div>
    	<div class="col-md-4"></div>
    </div>
</div>