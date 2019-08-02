<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\DvUsers */

$this->title = 'Edit Batch';
$this->params['breadcrumbs'][] = ['label' => 'All Batch', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Edit Batch'; ?>
<div class="container">
  <div class="row">
    <div class="col-md-10">
    	 <div class="dv-module-update">
            <?php $mid = $model->id;
            //added on 21 May 2019  
            $number_of_reschedul = Yii::$app->db->createCommand("SELECT count(*) FROM assist_batches_reschedule WHERE mid = '$model->id'")->queryScalar();

            $number_of_reschedul = isset($number_of_reschedul) ? $number_of_reschedul : ''; 
            ////////////Added on 22 May 2019//////////
            $batch_allocated_array = array();
            $query_batch = Yii::$app->db->createCommand("SELECT count(pid) as nos_of_student,batch_id,pid FROM assist_participant_batch_meta GROUP BY batch_id")->queryAll();
            if(count($query_batch) > 0){
                for($i=0;$i<count($query_batch);$i++){
                    $batch_allocated_array[$query_batch[$i]['batch_id']]['batch_id'] = $query_batch[$i]['nos_of_student'];
                    
                    $batch_meta_result = Yii::$app->db->createCommand("SELECT * FROM assist_participant_batch_meta where batch_id=".$query_batch[$i]['batch_id'])->queryAll();
                    foreach($batch_meta_result as $val_res){
                        $batch_allocated_array[$query_batch[$i]['batch_id']]['participant_id'][] = $val_res['pid'];
                    }
                }
            }
            if(array_key_exists($model->id,$batch_allocated_array)){
                $nos_alloted_seat_data =  $batch_allocated_array[$model->id]['batch_id'];
            }else{
                $nos_alloted_seat_data = 0;
            }
            ?>
        <button class="btn btn-success pull-right">Seat Left : <?php echo $model->seats - $nos_alloted_seat_data; ?></button>

        <?php if(!empty($number_of_reschedul)){ ?>
            <button style="margin-right: 10px;" class="btn btn-default pull-right">Reschedulings : <?php echo $number_of_reschedul; ?></button>
        <?php } ?>
            <h1><?php echo $dvcourse_name; ?> | <?php echo $model->start_date; ?></h1>
    	 	<?= $this->render('_form_edit', [ 'model' => $model, ]) ?>
    	 </div>
    </div>
    <div class="col-md-2"></div>
  </div>
</div>