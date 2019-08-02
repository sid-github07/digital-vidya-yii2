<?php

use yii\helpers\Html;
use yii\data\Pagination;
use app\models\DvCourse;
use yii\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\DvTrainingTopics;

/* @var $this yii\web\View */
/* @var $model app\models\DvUsers */
/* @var $form yii\widgets\ActiveForm */
?>
<div style="min-height:35px; "></div>
<div class="dv-users-form">
    <?php $form = ActiveForm::begin([
        'fieldConfig' => ['options' => ['class' => 'form-group col-md-4']],
        ]); ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => true])->input('name', ['placeholder' => "Topic Name"])->label(false); ?>

     <?php 
    $course = DvCourse::find()->where(['status'=>1])->all();
    $Dv_course = ArrayHelper::map($course, 'id', 'name'); ?>
    <?= $form->field($model, 'cid')->dropDownList($Dv_course, ['prompt'=>'Select Course',
        'id'=>"ccourse",'required' => 'required'])->label(false); ?>

 <?php if($model->isNewRecord) { ?>
     <?= $form->field($model, 'status')->hiddenInput(['value'=>'1'])->label(false); ?>
 <?php } else { ?>
     <?= $form->field($model, 'status')->dropDownList([1 =>'Active',0 => 'Inactive'],['prompt'=>'Select Topic Status','required' => 'required'])->label(false); ?>
<?php } ?>

  
   <div class="form-group col-md-12">
    <?php if($model->isNewRecord) { ?>
    <?= Html::submitButton( '<i class="fa fa-check"></i> Create Topic' , ['class' => 'btn btn-success' ]) ?>
    <?php } else { ?>
    <?= Html::submitButton( '<i class="fa fa-pencil"></i> Update Topic' , ['class' => 'btn btn-primary' ]) ?>
        <?= Html::a('<i class="fa fa-times"></i> Cancel', ['training_topics'], ['class' => 'btn back_button btn-danger cancel_button']); ?>
    <?php } ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
  <?php //$DvTrainingTopics = DvTrainingTopics::find()->all();
        $query = DvTrainingTopics::find();
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count,'pageSize' => 10]);
        $models = $query->offset($pagination->offset)->limit($pagination->limit)->all();

      //  $user_tt = ArrayHelper::map($DvTrainingTopics, 'id', 'name'); ?> 
<div class="form-group col-md-12">  
  <table class="table table-striped">
    <thead>
        <tr><th>#</th>
            <th>All Training Topics</th>
            <th>Course</th>
            <th><center>Status</center></th>
            <th><center>Edit</center></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($models as $module){
            
            if($module->cid == 0){
                $cid = '----';
            } else {
                $cid = DvCourse::find()->where(['id'=>$module->cid])->one()->name;
            }
            if($module->status == 1){
                $status = '<center><i class="fa fa-check-circle green_icon"></i></center>';
            } else {
                $status = '<center><i class="fa fa-times-circle red_icon"></i></center>';
            }

            echo '<tr> 
            <td> <a class="btn btn-xs btn-info" href="edit_topic?id='.$module->id.'"><strong>' . $module->id.'</strong></a> </td>
            <td> '.$module->name  . ' </td>
            <td>'.$cid.'</td>
            <td><center>'.$status.'</center></td>
            <td><center><a href="edit_topic?id='.$module->id.'"><i class="fa fa-pencil"></i></a></center></td>
            </tr>';
    //<td><center><a href="delete_team?id='.$id.'" onclick="return confirm(\'Are you sure want to delete?\')"><i class="fa fa-trash"></i></a></center></td>
                } ?>
            </tbody>
        </table>
        <?php // display pagination
            echo LinkPager::widget(['pagination' => $pagination]); ?>
    </div>
<script>
    function remove_required(){
        var status = $('#dvusers-status').val();
        if(status == 0){
            $('input, select, textarea').removeAttr('required');
        }
    }
</script>