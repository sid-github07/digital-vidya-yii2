<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
?>

<div class="dv-users-form">
    <?php if(isset($model)){ ?>
        <!-- Start: Participant emai search form -->
        <?php $form = ActiveForm::begin([
            'fieldConfig' => ['options' => ['class' => 'form-group col-md-4']],
        ]); ?>
             <?= $form->field($model, 'email')->input('text', ['placeholder' => "Email", 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Email"])->label(false); ?>
            <div class="form-group col-md-4">
                <?= Html::submitButton('Search',['id'=>'create_participant','class' =>'btn btn-primary']) ?>
            </div>
        <?php ActiveForm::end(); ?>

        <?php  if(isset($isemail) && $isemail == 1 ){ ?>
            <div class='form-group col-md-12'>Your entered email id is exist. </div>
        <?php } else if(isset($isemail) && $isemail == 0 ){ ?>
            <div class='form-group col-md-12'>Your entered email id not exist. </div>
        <?php } ?>
    <?php } ?>
</div>