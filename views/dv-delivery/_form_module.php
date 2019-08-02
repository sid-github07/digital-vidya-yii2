<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\DvAssistBatches;
use yii\helpers\ArrayHelper;
use app\models\DvUsers;
use app\models\DvCourse;
use app\models\DvModuleModel;
use app\models\DvUsersRole;
use app\models\DvTrainingTopics;
use yii\helpers\Url;
?>
<div class="dv-module-form">
    <?php $form = ActiveForm::begin([ 'id' => $model->formName(),'options' => [ 'class' => 'dv_module'], 
        'fieldConfig' => ['options' => ['class' => 'form-group col-md-4']],
        ]); ?>
    <?php 
    $module = DvModuleModel::find()->where(['status'=>1])->all();
    $Dv_module = ArrayHelper::map($module, 'id', 'module_name');
       
    echo $form->field($model, 'module')->dropDownList($Dv_module, ['prompt'=>'Select Module','id'=>"module_module_id",'required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Select Module",'autocomplete'=>'off'])->label(false);
 
    echo $form->field($model, 'start_date')->input('start_date', ['placeholder' => "Start Date",'required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'autocomplete'=>'off','title'=>"Start Date"])->label(false);

    echo '<div class="form-group col-md-4"><input id="module_end_date" class="form-control hasDatepicker" title="" placeholder="End Date" data-toggle="tooltip" readonly="readonly" data-placement="top" aria-required="true" data-original-title="End Date" aria-invalid="false"><div class="help-block"></div></div>';
    echo '<input type="hidden" name="end_date" id="set_end_date_hidden" value="">';
    $time_pm = $time_am = $noon_time = array();
    for($i = 1 ; $i <= 12 ; $i++ ){
        if($i >= 1 && $i < 10 ){
            $time_pm[$i.':00 PM'] = $i.':00 PM'; 
            $time_pm[$i.':30 PM'] = $i.':30 PM'; 
        } 
        if($i >= 10 && $i < 12){
            $time_am[$i.':00 AM'] = $i.':00 AM';
            $time_am[$i.':30 AM'] = $i.':30 AM';
        }
        if($i==12){
            $noon_time[$i.':00 PM'] = $i.':00 PM'; 
            $noon_time[$i.':30 PM'] = $i.':30 PM'; 
        }
    }

    $times_array = array_merge($time_am,$noon_time,$time_pm);
    
    echo $form->field($model, 'stiming')->dropDownList($times_array, ['placeholder' => "Start Time",'required' => 'required', 'data-toggle'=>"tooltip",'data-placement'=>"top", 'autocomplete'=>'off','title'=>"Select Start Time",'prompt'=>'Select Start Time'])->label(false);
     
    echo $form->field($model, 'etiming')->dropDownList($times_array, ['placeholder' => "End Time",'required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'autocomplete'=>'off','title'=>"Select End time",'prompt'=>'Select End Time'])->label(false);

    echo $form->field($model, 'duration')->input('duration', ['placeholder' => "Time Duration",'required' => 'required', 'id'=>'time_different', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'autocomplete'=>'off', 'readonly'=>'readonly','title'=>"Time Duration"])->label(false);

    $user_meta_data = ArrayHelper::map(DvUsers::find()->where(['department'=>'7'])->all(),'id','id'); 
    $trainer_array = array();
    foreach ($user_meta_data as $key => $value) {
        $users_data = DvUsers::find()->where(['id'=>$key])->one();
        $trainer_array[$key] = ucfirst($users_data['first_name'].' '.$users_data['last_name']);
    }
    echo $form->field($model, 'trainer')->dropDownList($trainer_array,['prompt'=>'Select Trainer','required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top",'autocomplete'=>'off' ,'title'=>"Select Trainer"])->label(false);
    // For Co-ordinator Person
    echo $form->field($model, 'coordinator')->dropDownList([],['prompt'=>'Select Co-ordinator Person', 'data-toggle'=>"tooltip", 'data-placement'=>"top",'autocomplete'=>'off', 'title'=>"Select Co-ordinator Person"])->label(false);
    
    echo $form->field($model, 'day')->dropDownList([], ['prompt'=>'Select Day','required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top",'autocomplete'=>'off', 'title'=>"Select Day"])->label(false);

    echo $form->field($model, 'type')->dropDownList([], ['prompt'=>'Select Number of Session','required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'autocomplete'=>'off','title'=>"Select Number of Session"])->label(false);

     
    $seats_array = array();
            for($i = 20; $i<=80; $i++){
                $seats_array[$i] = $i;
            }
    echo $form->field($model, 'seats')->dropDownList($seats_array, ['prompt'=>'Select Number of Seat(s)','required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'autocomplete'=>'off','title'=>"Select Number of Seat(s)"])->label(false);
    echo $form->field($model, 'joining_link')->input('Joining Link', ['placeholder' => "Joining Link",'required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'autocomplete'=>'off','title'=>"Joining Link"])->label(false);
     
    ?>
<div class="form-group col-md-12">
    <div id='TextBoxesGroup'> </div>
    <div class="form-group col-md-6"></div>
    <div class="form-group col-md-3">
        <button type='button' id='removeButton' class="btn pull-right btn-danger" style="display: none;" data-toggle="tooltip" data-placement="top" title="Remove Session"><i class="fa fa-times"></i></button>
        <button type='button' id='addButton' class="btn pull-right btn-info" style="display: none;" data-toggle="tooltip" data-placement="top" title="Add Session"><i class="fa fa-plus"></i></button>
    </div>
</div>
  <?php if($model->isNewRecord){
        echo '<div class="form-group col-md-12">';
        echo '<input name="trainer_notify" value="0" type="hidden">';
        echo '<label><input name="trainer_notify" value="1" type="checkbox">';
        echo ' Send Batch details to Trainer & Block calendar';
        echo '</label>';
        echo '</div>';
    } ?>
  <div class="form-group col-md-8">
    <?= Html::submitButton( '<i class="fa fa-check"></i> Create Module', ['class' => 'btn btn-success']); ?>
    <?= Html::a('<i class="fa fa-times"></i> Cancel', ['index'], ['class' => 'btn back_button btn-danger cancel_button']); ?>
    </div>
    <?php ActiveForm::end(); ?>
    <?php $js = <<<JS
$('body').on('beforeSubmit', 'form#{$model->formName()}', function (){
    var form = $(this);
    if (form.find('.has-error').length){
        return false;
    }
    
    // return undefined; // form gets submitted
    // return null; // form gets submitted
    // return true; // form gets submitted
    if (form.hasClass('dv_module')){
        //alert('has class');
        $('#confirm-submit').modal('show');
        $('#confirm-submit .modal-body .session_error').replaceWith('<div class="session_error"></div>');
        return false;
    }    
    //return false; // form does not get submitted

});
JS;

$this->registerJs($js); ?>

<div class="modal fade" id="confirm-submit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1>Confirm Submit</h1>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to submit the following details?</p>
                <div class="session_error"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a id="submit" class="btn btn-success success">Submit</a>
            </div>
        </div>
    </div>
</div>

</div>

<style type="text/css"> 
[name*="allsession[session"]{ display: none; }  
[id*="session_"]{ display: block; } 
#reschedule{display: none;}
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
    function call_batch_list(){
        window.location.replace("<?php echo Url::to(['dv-delivery/index'])?>");
    }
    $('body').on('change', '#module_module_id', function (){
        var module_id = $(this).val();
        //alert(module_id);
        $.ajax({
            url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-delivery/get_trainers') ?>',
            type: 'POST',
            data: { module_id: module_id},
            success: function(data){
                $("#DvAssistBatches-trainer").html(data);
            }
        });
    });
</script>