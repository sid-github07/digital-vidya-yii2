<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\DwUsers */

$this->title = 'Digital Vidya - Update Particpant Status';
$this->params['breadcrumbs'][] = ['label' => 'All Registration', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update Particpant Status'; ?>
<div style="min-height:35px; "></div>
<div class="container">
  <div class="row">
    <div class="col-md-10">
    	<div class="dv-update-installments">
    		<?php 
            if(isset($model_data)){ 
                echo $this->render('_form_update_participant_status', [ 'model' => $model, 'model_data' => $model_data ]);
    		 }else{
                if(isset($NoRecord)){
                    echo $this->render('_form_update_participant_status', [ 'model' => $model, 'NoRecord'=>$NoRecord]);
                }else{
    			     echo $this->render('_form_update_participant_status', [ 'model' => $model ]);
                }
    		 } 
             ?>
    	</div>
    </div>
    <div class="col-md-2"></div>
  </div>
</div>