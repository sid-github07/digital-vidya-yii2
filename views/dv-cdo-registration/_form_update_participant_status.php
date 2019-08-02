<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
?>

<div class="dv-users-form">
    <?php if(isset($model_data)){ ?>
        <!-- Start: Participant emai search form -->
        <?php $form = ActiveForm::begin(['id' => 'search_email',
            'fieldConfig' => ['options' => ['class' => 'form-group col-md-4']],
        ]); ?>
             <?= $form->field($model_data, 'email')->textInput(['maxlength' => true])->input('text', ['placeholder' => "Email", 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Email"])->label(false); ?>
            <div class="form-group col-md-4">
                <?= Html::submitButton('<i class="fa fa-search"></i> Search',['id'=>'create_participant','class' => $model_data->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            </div>
        <?php ActiveForm::end(); ?>
        <!-- End: Participant emai search form -->


        <div class="form-group col-md-12"><h3 class="blue_color">Participant Status</h3></div>
        <?php $form = ActiveForm::begin(['id' => 'participant_status_form',
            'fieldConfig' => ['options' => ['class' => 'form-group col-md-4']],
        ]); ?>
            
        <?= $form->field($model_data, 'sales_user_id')->hiddenInput()->label(false); ?>
        <?= $form->field($model_data, 'email')->hiddenInput()->label(false); ?>

        <?= $form->field($model_data, 'participant_status')->dropDownList([1=>'Active',2=>'On Hold',3=>'Drop off',4=>'Completed'], ['prompt'=>'Select Participant Status', 'id'=>"participant_status"]); ?>
        
        
        <div class="form-group col-md-12">
            <?= Html::submitButton('Update', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fa fa-times"></i> Cancel', ['update_participant_status'], ['class' => 'btn back_button btn-danger cancel_button']); ?>
        </div>
        <?php ActiveForm::end(); ?>

        <?php }else{ ?>
            <!-- Start: Participant emai search form -->
            <?php $form = ActiveForm::begin(['id' => 'search_email',
                'fieldConfig' => ['options' => ['class' => 'form-group col-md-4']],
            ]); ?>
                 <?= $form->field($model, 'email')->textInput(['maxlength' => true])->input('text', ['placeholder' => "Email", 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Email"])->label(false); ?>
                <div class="form-group col-md-4">
                    <?= Html::submitButton('<i class="fa fa-search"></i> Search',['id'=>'create_participant','class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                </div>
            <?php ActiveForm::end(); ?>
            <!-- End: Participant emai search form -->
            <?php if(isset($NoRecord)){ ?>
                <div class="Norecordfound form-group col-md-12">
                    <span><?=$NoRecord ?></span>
                </div>
            <?php } ?>
        <?php } ?>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>


<script type="text/javascript">
    $(document).ready(function(){

    });
</script>