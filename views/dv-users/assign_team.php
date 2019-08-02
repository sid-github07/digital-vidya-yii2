<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* This file contain a file with form Assign Manager */

$this->title = 'Digital Vidya: Assign Manager to the User';
$second_title = 'Assign Manager to the User';
$this->params['breadcrumbs'][] = $second_title; ?>
<div style="min-height:35px; "></div>
<div class="container">
  <div class="row">
    <div class="col-md-10">
    	 <div class="dv-users-create">
    	 	<?= $this->render('_form_team') ?>
    	 </div>
    </div>
    <div class="col-md-2"></div>
  </div>
</div>