<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\DwUsers */

$this->title = 'Digital Vidya - Update Installments';
$this->params['breadcrumbs'][] = ['label' => 'All Registration', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update Installments'; ?>
<div style="min-height:35px; "></div>
<div class="container">
  <div class="row">
    <div class="col-md-10">
    	<div class="dv-participant-update">
    		<?php 
            if(isset($ppm_model_data)){ 
                echo $this->render('_form_update_installments', [ 'model' => $model, 'model_data'=>$model_data, 'ppm_model_data' => $ppm_model_data ,'pp_model'=>$pp_model]);
            }else{
                if(isset($NoRecord)){
                    if(isset($model_data)){
                        echo $this->render('_form_update_installments', [ 'model' => $model, 'NoRecord'=>$NoRecord,  'model_data'=>$model_data]);
                    }else{
                        echo $this->render('_form_update_installments', [ 'model' => $model, 'NoRecord'=>$NoRecord]);
                    }
                }else{
    			     echo $this->render('_form_update_installments', [ 'model' => $model]);
                }
    		 } 
             ?>
    	</div>
    </div>
    <div class="col-md-2"></div>
  </div>
</div>