<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* This file contain a file with form Create User */

$this->title = 'Digital Vidya: Create User';
$this->params['breadcrumbs'][] = ['label' => 'Dv Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title; ?>
<div style="min-height:35px; "></div>
<div class="container">
  <div class="row">
    <div class="col-md-10">
    	 <div class="dv-users-create">
    	 	<?= $this->render('_form', [ 'model' => $model ]) ?>
    	 </div>
    </div>
    <div class="col-md-2"></div>
  </div>
</div>

