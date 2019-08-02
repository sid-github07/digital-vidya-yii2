<?php

use yii\helpers\Html;
use app\models\DvCourse;
use yii\data\Pagination;
use yii\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

?>

<div style="min-height:35px; "></div>
<div class="dv-course-form">
    <?php $form = ActiveForm::begin([
        'fieldConfig' => ['options' => ['class' => 'form-group col-md-4']],
        ]); ?>
    <?= $form->field($model, 'course_name')->input('name', ['placeholder' => "Course Name"])->label(false); ?>

    $number = range(1,25);
    <?= $form->field($model, 'number_of_weeks')->dropDownList($number,['prompt'=>'Select Number Of Weeks'])->label(false); ?>

    <?= $form->field($model, 'course_category')->dropDownList(['DM' =>'DM','1Month' => '1Month','DA' => 'DA'],['prompt'=>'Select Category'])->label(false); ?>

    <?= $form->field($model, 'course_type')->dropDownList(['Self-Study' =>'Self-Study','Instructor-Led' => 'Instructor-Led'],['prompt'=>'Select Type'])->label(false); ?>

    <?= $form->field($model, 'module')->dropDownList($number ,['prompt'=>'Select Module'])->label(false); ?>

    <?php if($model->isNewRecord) { ?>
        <?= $form->field($model, 'status')->hiddenInput(['value'=>'1'])->label(false); ?>
    <?php } else { ?>
        <?= $form->field($model, 'status')->dropDownList([1 =>'Active',0 => 'Inactive'],['prompt'=>'Select Course Status','required' => 'required'])->label(false); ?>
    <?php } ?>

    <div class="form-group col-md-12">
        <?php if($model->isNewRecord) { ?>
            <?= Html::submitButton( '<i class="fa fa-check"></i> Create Course' , ['class' => 'btn btn-success' ]) ?>
        <?php } else { ?>
            <?= Html::submitButton( '<i class="fa fa-pencil"></i> Update Course' , ['class' => 'btn btn-primary' ]) ?>
            <?= Html::a('<i class="fa fa-times"></i> Cancel', ['create_course'], ['class' => 'btn back_button btn-danger cancel_button']); ?>
        <?php } ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<div class="form-group col-md-8"> 
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>All Courses</th>
                <th>Course Type</th>
                <th>Course Category</th>
                <th>Number Of Weeks</th>
                <th>Module Name</th>
                <th><center>Status</center></th>
                <th><center>Edit</center></th>
            </tr>
        </thead>
        <tbody>
            <?php //foreach($user_team as $id => $deuser){
                foreach($model as $module){
                    if($module->status == 1){
                        $status = '<center><i class="fa fa-check-circle green_icon"></i></center>';
                    } else {
                        $status = '<center><i class="fa fa-times-circle red_icon"></i></center>';
                    }

                    echo '<tr>
                    <td><a class="btn btn-xs btn-info" href="edit_course?id='.$module->id.'"><strong>'.$module->id.'</strong></a> </td>
                    <td>'.$module->course_name.'</td>
                    <td>'.$module->course_type.'</td>
                    <td>'.$module->course_category.'</td>
                    <td>'.$module->number_of_weeks.'</td>
                    <td>'.$module->module.'</td>
                    <td><center>'.$status.'</center></td>
                    <td><center><a href="edit_course?id='.$module->id.'"><i class="fa fa-pencil"></i></a></center></td>
                    </tr>';
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