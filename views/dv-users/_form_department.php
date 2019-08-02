<?php

use yii\helpers\Html;
use yii\data\Pagination;
use yii\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\DvUsersDepartment;
/* @var $this yii\web\View */
/* @var $model app\models\DvUsers */
/* @var $form yii\widgets\ActiveForm */

// This file contains Create Department/Edit Department form.

?>
<div class="dv-users-form">
    <?php $form = ActiveForm::begin([
        'fieldConfig' => ['options' => ['class' => 'form-group col-md-6']],
        ]); ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => true])->input('name', ['placeholder' => "Department Name",'required' => 'required'])->label(false); ?>
    <?php if($model->isNewRecord){ ?>
    <?= $form->field($model, 'status')->hiddenInput(['value'=>'1'])->label(false); ?>
    <?php } else { ?>
        <?= $form->field($model, 'status')->dropDownList([1 =>'Active',0 => 'Inactive'],['prompt'=>'Select Status','required' => 'required'])->label(false); ?>
    <?php } ?>
   <div class="form-group col-md-12">
        <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-check"></i> Create Department' : '<i class="fa fa-pencil"></i> Update Department', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?php if($model->isNewRecord) { } else { ?>
        <?= Html::a('<i class="fa fa-times"></i> Cancel', ['create_department'], ['class' => 'btn back_button btn-danger cancel_button']); ?>
        <?php } ?>

    </div>
    <?php ActiveForm::end(); ?>
</div>
  <?php
        $query = DvUsersDepartment::find();
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count,'pageSize' => 10]);
        $user_dep = $query->offset($pagination->offset)->limit($pagination->limit)->all(); ?>   
  <table class="table table-striped">
    <thead>
        <tr><th>#</th>
            <th>All Departments</th>
            <th><center>Status</center></th>
            <th><center>Edit</center></th>
        </tr>
    </thead>
    <tbody>
        <?php 
            foreach($user_dep as $deuser){            
            if($deuser->status == 1){
                $status = '<center><i class="fa fa-check-circle green_icon"></i></center>';
            } else {
                $status = '<center><i class="fa fa-times-circle red_icon"></i></center>';
            }
                    echo '<tr>
                    <td> <a class="btn btn-xs btn-info" href="edit_department?id='.$deuser->id.'"><strong>' . $deuser->id.'</strong></a> </td>
                    <td> '.$deuser->name . ' </td>
                    <td><center>'.$status.'</center></td>
                    <td><center><a href="edit_department?id='.$deuser->id.'"><i class="fa fa-pencil"></i></a></center></td>
                     </tr>';
                } ?>
            </tbody>
        </table>
        <?php // display pagination
            echo LinkPager::widget(['pagination' => $pagination]); ?>