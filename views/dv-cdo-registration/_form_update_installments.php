<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

use app\models\DvCurrency;
use app\models\DvPaymentMode;

?>

<div class="dv-users-form">
    <?php if(isset($ppm_model_data)){ ?>
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

        <!-- Record list -->
              <table class="table table-striped">
                <thead>
                    <tr><th>#</th>
                        <th>Installment No.</th>
                        <th>Installment Amount</th>
                        <th>Cheque Referenc Number</th>
                        <th>Installment Due Date</th>
                        <th><center>Installment Status</center></th>
                        <th><center>Action</center></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $cnt = 1;
                foreach($ppm_model_data as $ppm_value){
                    
                    $dt = new DateTime($ppm_value->installment_due_date);
                    
                    if($ppm_value->installment_status == 1){
                        $status = '<center><i data-toggle="tooltip" data-placement="top" title="Paid" class="fa fa-check-circle green_icon"></i></center>';
                    } else {
                        $status = '<center><i data-toggle="tooltip" data-placement="top" title="Remaining" class="fa fa-times-circle red_icon"></i></center>';
                    }

                    echo '<tr>
                    <td> <a class="btn btn-xs btn-info"><strong>' . $cnt.'</strong></a> </td>
                    <td> '.$ppm_value->installment . ' </td>
                    <td> '.$ppm_value->installment_amount. ' </td>
                    <td> '.$ppm_value->cheque_referenc_number. ' </td>
                    <td><center>'.$dt->format('d M, Y').'</center></td>
                    <td><center>'.$status.'</center></td>';
                    if($ppm_value->installment_status != 1){
                        echo'<td><center><a data-toggle="tooltip" data-placement="top" title="Edit" class="edit_instlallment" href="javascript:void(0);" data-id="'.$ppm_value->id.'" data-participant-id="'.$ppm_value->participant_id.'" data-number="'.$ppm_value->installment.'"><i class="fa fa-pencil"></i></a></center></td>';
                    }else{
                        echo '<td></td>';
                    }
                    echo '</tr>';
                    
                    $cnt++;
                } ?>
                </tbody>
            </table>
        <!-- End Record List -->

        <div class="update_installment_form_data" style="display:none">
            <div class="form-group col-md-12"><h3 class="inst_titl blue_color"></h3></div>
        <?php
            // Get Currency
            $currency = DvCurrency::find()->where(['status'=>1])->all();
            $currencyArr = array();
            foreach($currency as $currency_value){
              $currencyArr[$currency_value['id']] = $currency_value['name'];
            }

            // Get paymentmode          
            $paymentmode = DvPaymentMode::find()->where(['status'=>1])->all();
            $paymentmodeArr = array();
            foreach($paymentmode as $paymentmode_value){
              $paymentmodeArr[$paymentmode_value['id']] = $paymentmode_value['name'];
            }
         ?>
            <?php $form = ActiveForm::begin(['id' => 'update_installment_form',
                'fieldConfig' => ['options' => ['class' => 'form-group col-md-4']],
            ]); ?>
                
            <input type="hidden" name="installment_id" id="installment_id" value="">
            <input type="hidden" name="participant_id" id="participant_id" value="">
            <input type="hidden" name="payment_number" id="payment_number" value="">
            <?= $form->field($model_data, 'email')->hiddenInput()->label(false); ?>

            <?= $form->field($pp_model, 'amount_recieved')->input('text', ['placeholder' => "Amount Recieved", 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Amount Recieved"])->label(false); ?>

            <?= $form->field($pp_model, 'amount_recieved_date')->input('text', ['placeholder' => "Amount Recieved Date", 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Amount Recieved Date"])->label(false); ?>

            <?= $form->field($pp_model, 'payment_reference_number')->input('text', ['placeholder' => "Payment Reference Number", 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Payment Reference Number"])->label(false); ?>

            <?= $form->field($pp_model, 'payment_currency')->dropDownList($currencyArr, ['prompt'=>'Select Currency','data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Select Currency"])->label(false);   ?> 

            <?= $form->field($pp_model, 'payment_mode')->dropDownList($paymentmodeArr, ['prompt'=>'Select Payment Mode','data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Select Payment Mode"])->label(false); ?>   

            <div class="form-group col-md-12">
                <?= Html::submitButton('Update', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('<i class="fa fa-times"></i> Cancel', ['update_installments'], ['class' => 'btn back_button btn-danger cancel_button']); ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
        
        <?php }else{ ?>
            <!-- Start: Participant email search form -->
            <?php $form = ActiveForm::begin(['id' => 'search_email',
                'fieldConfig' => ['options' => ['class' => 'form-group col-md-4']],
            ]); ?>
                <?php 
                if(isset($model_data)){ ?>
                    <?= $form->field($model_data, 'email')->textInput(['maxlength' => true])->input('text', ['placeholder' => "Email", 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Email"])->label(false); ?>
                <?php }else { ?>
                    <?= $form->field($model, 'email')->textInput(['maxlength' => true])->input('text', ['placeholder' => "Email", 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Email"])->label(false); ?>
                <?php } ?>
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
        // added value in hidden fields and open update installment form
        $(".edit_instlallment").click(function(){
            var id = $(this).attr('data-id');
            var inst_num = $(this).attr('data-number');
            var participant_id = $(this).attr('data-participant-id');

            $('#installment_id').val(id);
            $('#payment_number').val(inst_num);
            $('#participant_id').val(participant_id);

            var title = "Update Installment Number : " + inst_num;
            $(".inst_titl").html(title);
            
            $(".update_installment_form_data").show();
        });
        // Datepicker
        $('#dvparticipantpayments-amount_recieved_date').datepicker({dateFormat:'dd-mm-yy'}).focus();
    });
</script>