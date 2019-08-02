<?php

use yii\helpers\Html;
use app\models\DvCourse;
use yii\data\Pagination;
use yii\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\DvUsers */
/* @var $form yii\widgets\ActiveForm */
?>
<div style="min-height:35px; "></div>
<div class="dv-users-form">
    <?php $form = ActiveForm::begin([
        'fieldConfig' => ['options' => ['class' => 'form-group col-md-4']],
        ]); ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => true])->input('name', ['placeholder' => "Course Name"])->label(false); ?>

     <?= $form->field($model, 'mcourse')->dropDownList(['dm' =>'DM','da' => 'DA'],['prompt'=>'Select Master Course'])->label(false); ?>

     <?= $form->field($model, 'type')->dropDownList(['1_module' =>'1 Module','5_module' => '5 Module','6_module' => '6 Module'],['prompt'=>'Select Type'])->label(false); ?>

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
  <?php $query = DvCourse::find();
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count,'pageSize' => 25]);
        $models = $query->offset($pagination->offset)->limit($pagination->limit)->all();
        /*$DvUsersTeam = DvCourse::find()->all();
        $user_team = ArrayHelper::map($DvUsersTeam, 'id', 'name');*/ ?>   
<div class="form-group col-md-8"> 
  <table class="table table-striped">
    <thead>
        <tr><th>#</th>
            <th>All Courses</th>
            <th>Master Course</th>
            <th>Module</th>
            <th><center>Status</center></th>
            <th><center>Edit</center></th>
        </tr>
    </thead>
    <tbody>
        <?php //foreach($user_team as $id => $deuser){
            foreach($models as $module){
            //$status_id = DvCourse::find()->where(['id'=>$id])->one()->status;
            if($module->status == 1){
                $status = '<center><i class="fa fa-check-circle green_icon"></i></center>';
            } else {
                $status = '<center><i class="fa fa-times-circle red_icon"></i></center>';
            }

            if($module->mcourse == 'dm'){
                $mcourse = 'DM';
            } else if($module->mcourse == 'da'){
                $mcourse = 'DA';
            } else {
                $mcourse = '';
            }

            $module_type = $module->type;
            $module_type = ucwords(str_replace("_"," ",$module_type));

            /*echo '<tr>
            <td> <a class="btn btn-xs btn-info"><strong>' . $id.'</strong></a> </td>
            <td> '.$deuser  . ' </td>
            <td><center>'.$status.'</center></td>
            <td><center><a href="edit_course?id='.$id.'"><i class="fa fa-pencil"></i></a></center></td>
            </tr>';*/

            echo '<tr>
            <td><a class="btn btn-xs btn-info" href="edit_course?id='.$module->id.'"><strong>'.$module->id.'</strong></a> </td>
            <td>'.$module->name.'</td>
            <td>'.$mcourse.'</td>
            <td>'.$module_type.'</td>
            <td><center>'.$status.'</center></td>
            <td><center><a href="edit_course?id='.$module->id.'"><i class="fa fa-pencil"></i></a></center></td>
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