<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\DvRegistration;
use yii\helpers\ArrayHelper;
use app\models\DvUsersRole;
use app\models\DvUsersTeam;
use app\models\DvUsersDepartment;
use app\models\DvParticipantModules;
use app\models\DvCountry;
use app\models\DvStates;
use app\models\DvCourse;
use app\models\DvUsers;
/* @var $this yii\web\View */
/* @var $model app\models\DvUsers */
/* @var $form yii\widgets\ActiveForm */
$user = Yii::$app->user->identity;
?>
<style>
    .field-dvparticipantpayments-payment_reference_number.required.has-error .help-block{display: block !important;}
</style>
<div class="dv-users-form">
    <?php $form = ActiveForm::begin(['id' => 'participant_registration_form',
        'fieldConfig' => ['options' => ['class' => 'form-group col-md-4']],
    ]); ?>
        
    <?= $form->field($model, 'sales_user_id')->hiddenInput(['value'=>$user->id])->label(false); ?>

    <div class="form-group col-md-12"><h3 class="blue_color">Participant Profile</h3></div>
    
    <!--  Form Hidden Fields -->
    <input type="hidden" name="token_id" id="token_id" value="">
    <input type="hidden" name="wp_id" id="wp_id" value="">
    <input type="hidden" name="wp_user" id="wp_user" value="">
    <input type="hidden" name="sales_user" id="sales_user" value="">
    

    <div class="form-group" style="float:left;width:100%">
        <?= $form->field($model, 'email')->textInput(['maxlength' => true])->input('text', ['placeholder' => "Email*", 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Email"])->label(false); ?>

        <?= $form->field($model, 'first_name')->textInput(['maxlength' => true])->input('text', ['placeholder' => "First Name*", 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"First Name"])->label(false); ?>

        <?= $form->field($model, 'last_name')->textInput(['maxlength' => true])->input('text', ['placeholder' => "Last Name*", 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Last Name"])->label(false); ?>
    </div>
    <div class="form-group" style="float:left;width:100%;position:relative;">
        <?= $form->field($model, 'mobile')->textInput(['maxlength' => true])->input('text', ['placeholder' => "Mobile*", 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Mobile"])->label(false); ?>

        <?= $form->field($model, 'remarks')->textArea(['rows' => '5','placeholder' => "Notes to Delivery Team", 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Notes to Delivery Team"])->label(false)->hint("<em>(Don't use special characters, Like : %,+,&,#)</em>");; ?>

        <?= $form->field($model, 'promises_notes')->textArea(['rows' => '5','placeholder' => "Promises Notes", 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Add Promises Notes"])->label(false)->hint("<em>(Don't use special characters, Like : %,+,&,#)</em>"); ?>

        <?= $form->field($model, 'scholarship_offered')->radioList(['1' => 'Yes', '0' => 'No'])->label('Scholarship offered');?>

    </div>

    <div class="form-group col-md-12 no-margin"><h3 class="blue_color">Address</h3></div>
    <div class="form-group">
    <?php
    $country = DvCountry::find()->all();

    $Dv_country = ArrayHelper::map($country, 'id', 'name');
    ?>
    <?= $form->field($model, 'address')->textArea(['rows' => '4','placeholder' => "Address", 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Address"])->label(false)->hint("<em>(Don't use special characters, Like : %,+,&,#)</em>"); ?>
    <?= $form->field($model, 'country')->dropDownList($Dv_country, ['prompt'=>'Select Country',
        'id'=>"country",'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Select Country"])->label(false); ?>
    <?= $form->field($model, 'state')->dropDownList([''=>'Select State'],['data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Select State"])->label(false); ?>
    <?= $form->field($model, 'city')->dropDownList([''=>'Select City'],['data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Select City"])->label(false); ?>
    
    </div>

    <div class="form-group col-md-12 no-margin"><h3 class="blue_color">Course</h3></div>
    <div class="form-group">
        <div class="col-md-4">
            <select onchange="get_course_data();" id="course_domain" class="form-control" name="course_domain" data-toggle="tooltip" data-placement="top" title="" data-original-title="Select Domain">
                <option value="">Select Domain</option>
                <option value="dm">DM</option>
                <option value="da">DA</option>
            </select>
        </div>
        <?php
        $course = DvCourse::find()->where(['status'=>1])->all();
        $Dv_course = ArrayHelper::map($course, 'id', 'name');
        ?>
        <?= $form->field($model, 'course')->dropDownList($Dv_course, ['prompt'=>'Select Course',
            'id'=>"course",'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Select Course"])->label(false); ?>

        <?= $form->field($model, 'course_format')->dropDownList([''=>'Select Course Format'],['data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Select Course Format"])->label(false)->hint("First you need to select course"); ?>

        <?= $form->field($model, 'course_batch')->dropDownList([''=>'Select Course Batch'],['data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Select Course Batch"])->label(false)->hint("First you need to select Format"); ?>    
    </div>

    <div class="all_check form-group col-md-12" style="display: none;">
        <div class="form-group field-DvRegistration-free_courses">
            <input name="DvRegistration[opt_for_3_months]" type="checkbox" id="opt_for_3_months">
            <div class="hint-block"><em>Check, If Participant Opt for 3 Months. (In case of CDMM only)</em></div>
            <div class="help-block"></div>
        </div>

         <?= $form->field($model, 'available_batch_opt')->dropDownList([''=>'Select available batch option','sat+sun'=>"sat+sun",'sat+wd'=>"sat+wd", 'sun+wd'=>"sun+wd", "wd"=>"wd",'sat'=>'sat','sun'=>'sun'],['data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Select available batch option"])->label(false); ?>
        <div class="form-group chck_course">

            <div class="form-group field-DvRegistration-free_courses cmam_cfmm_tj col-md-12">
                <label class="control-label" for="DvRegistration-free_courses">Please Select the Courses in which participant is eligible. ( In case of CDMM & CPDM )</label>
            </div>
            <div class="form-group field-DvRegistration-free_courses cmam_cfmm_tj col-md-4">
                <input name="DvRegistration[free_courses][CMAM]" value="CMAM" type="checkbox" id="cmam_checkbox">
                <div class="hint-block"><em>CMAM Course</em></div>
            </div>

            <div class="form-group field-DvRegistration-free_courses cmam_cfmm_tj col-md-4">
                <input name="DvRegistration[free_courses][CFMM]" value="CFMM" type="checkbox" id="cfmm_checkbox">
                <div class="hint-block"><em>CFMM Course</em></div>
            </div>

            <div class="form-group field-DvRegistration-free_courses cmam_cfmm_tj col-md-4">
                <input name="DvRegistration[free_courses][TJ_Walker]" value="TJ_Walker"  type="checkbox"  id="tjw_checkbox">
                <div class="hint-block"><em>TJ Walker Course</em></div>
            </div>
            <div class="form-group field-DvRegistration-free_courses Tableau_CWAW col-md-12">
                <label class="control-label">Please Select the Courses in which participant is eligible. ( In case of DAR, DAP) </label>
            </div>
            <div class="form-group field-DvRegistration-free_courses Tableau_CWAW col-md-4">
                <input name="DvRegistration[free_courses][Tableau]" value="Tableau" type="checkbox" id="tableau_checkbox">
                <div class="hint-block"><em>Tableau Course</em></div>
            </div>

            <div class="form-group field-DvRegistration-free_courses Tableau_CWAW col-md-4">
                <input name="DvRegistration[free_courses][CWAW]" value="CWAW" type="checkbox" id="cwaw_checkbox">
                <div class="hint-block"><em>CWAW Course</em></div>
            </div>
        </div>
    </div>
    <div class="form-group col-md-12 no-margin"><label class="control-label">Vskills Certification (<em>Price should be include in Certification</em>)</label></div> 
   <div class="form-group">
         <div class="form-group col-md-4">
                
                <input name="DvRegistration[vskills]" value="1" type="checkbox" id="vskills_checkbox">
                <div class="hint-block"><em>Yes</em></div>
            </div>
   </div>
    <div class="form-group col-md-12 no-margin"><h3 class="blue_color">Payment Details</h3></div>
    <div class="form-group">
        
        <?php // $form->field($pp_model, 'payment_currency')->dropDownList($companycurrency, ['prompt'=>'Select Currency','data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Select Currency"])->label(false)->hint("<em>First you need to select Country</em>"); ?> 

        <div class="form-group col-md-4 field-dvparticipantpayments-payment_currency">
            <select id="dvparticipantpayments-payment_currency" class="form-control" name="DvParticipantPayments[payment_currency]" title="" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Select Currency" aria-invalid="true">
                <option value="">Select Currency</option>
                
                <?php foreach($companycurrency as $key=>$val) { ?>
                    <option value="<?=$key ?>"><?=$val ?></option>
                <?php } ?>
            </select>
            <div class="hint-block"><em>First you need to select Country</em></div>
        </div>


        <?= $form->field($model,'total_confirmed_amount')->textInput()->hint('<em>(including Service Tax)</em>')->input('text', ['placeholder' => "Total Confirmed Amount*", 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Total Confirmed Amount" , "pattern"=>"^\d*(\.\d{0,2})?$"])->label(false); ?>

        <?= $form->field($model, 'is_full_payment')->radioList(['1' => 'Yes', '0' => 'No'])->label('Is it a Full Payment? (In case of complete payment)'); ?>
    </div>
    <div class="form-group ishide" style="float:left;width: 100%;">
        <?php // $form->field($pp_model,'amount_recieved')->textInput()->input('text', ['placeholder' => "Amount Recieved*", 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Amount Recieved", "pattern"=>"^\d*(\.\d{0,2})?$"])->label(false); ?>


        <div class="form-group col-md-4 field-dvparticipantpayments-amount_recieved">
            <input type="text" id="dvparticipantpayments-amount_recieved" class="form-control" name="DvParticipantPayments[amount_recieved]" title="" placeholder="Amount Recieved*" data-toggle="tooltip" data-placement="top" pattern="^\d*(\.\d{0,2})?$" aria-required="true" data-original-title="Amount Recieved" aria-invalid="true">
        </div>

        <div class="form-group col-md-4 tax_diff_rate_div">
            <input type="text" id="row_amount" class="form-control" name="row_amount" title="" placeholder="Total Amount" readonly="">
            <div class="hint-block"><em>(Amount without Tax.)</em></div>
        </div>
        <div class="form-group col-md-4 tax_diff_rate_div">
            <input type="text" id="tax_amount" class="form-control" name="tax_amount" title="" placeholder="Tax" readonly="">
            <div class="hint-block"><em>(Tax from your Paid Amount.)</em></div>
        </div>

        <?php // $form->field($pp_model, 'payment_mode')->dropDownList($allPaymentmethod, ['prompt'=>'Select Payment Mode','data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Select Payment Mode"])->label(false); ?>   

        <div class="form-group col-md-4 field-dvparticipantpayments-payment_mode">
            <select id="dvparticipantpayments-payment_mode" class="form-control" name="DvParticipantPayments[payment_mode]" title="" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Select Payment Mode" aria-invalid="true">
            <option value="">Select Payment Mode</option>
                <?php foreach($allPaymentmethod as $key=>$val) { ?>
                    <option value="<?=$key ?>"><?=$val ?></option>
                <?php } ?>
            </select>
        </div>


        <?php // $form->field($pp_model,'payment_reference_number')->textInput()->input('text', ['placeholder' => "Payment Reference Number*", 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Payment Reference Number"])->label(false); ?>
        <div class="form-group col-md-4 field-dvparticipantpayments-payment_reference_number">
            <input type="text" id="dvparticipantpayments-payment_reference_number" class="form-control" name="DvParticipantPayments[payment_reference_number]" title="" placeholder="Payment Reference Number*" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Payment Reference Number" aria-invalid="true">
        </div>

    </div>
    
    <?= $form->field($model, 'modules_allowed')->dropDownList([], ['prompt'=>'Select Modules Allowed','data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Select Modules Allowed", 'class'=>'form-control ishide'])->label(false); ?>

    <?= $form->field($model, 'is_pdc',['options'=>['style'=>'display:none;']])->checkbox(array('label'=>'In case of Post Date Cheques'))->hint('<em>(Yes, I have received PDCs.)</em>'); ?>

   <div class="installment_boxe" style="display: none;">
    <div class="form-group col-md-12 no-margin"><h3 class="blue_color">Installments</h3></div>
    <div class="form-group" style="float:left;width: 100%">
        <div class="form-group col-md-4">
            <input id="insamt_2" class="form-control ins_amt_ins" pattern="^\d*(\.\d{0,2})?$"  name="installment[2][amt]" type="text" placeholder="Installment no: 2 (Committed Amount)" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Installment no: 2 (Committed Amount)">
            <em>(including Service Tax)</em>
            <div class="help-block"></div>
        </div>
        <div class="form-group col-md-4">
            <input id="insdate_2"  class="form-control" name="installment[2][date]" type="text" placeholder="Installment no: 2 (Committed Date)" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Installment no: 2 (Committed Date)" autocomplete="off">
            <div class="help-block"></div>
        </div>
        <div class="form-group col-md-4"><!-- pdc_ref_form_field -->
            <input id="insref_2"  class="form-control pdc_ref_number" name="installment[2][ref_number]" type="text" placeholder="Installment no: 2 (Cheque Reference Number)" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Installment no: 2 (Cheque Reference Number)">
            <div class="help-block"></div>
        </div>
    </div>
    <div id='installmentboxe' class="form-group"  style="float:left;width:100%"> </div>
    <button type='button' id='remove_Button' class="btn pull-right btn-danger" style="display: none;"><i class="fa fa-times"></i></button>
    <button type='button' id='add_Button' class="btn pull-right btn-info" style="margin-right: 10px;"><i class="fa fa-plus"></i></button>
    </div>
    <div class="form-group col-md-12">
        <?= Html::Button($model->isNewRecord ? '<i class="fa fa-pencil"></i> Create Participant' : '<i class="fa fa-pencil"></i> Update Participant', ['id'=>'create_participant','class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('<i class="fa fa-times"></i> Cancel', ['index'], ['class' => 'btn back_button btn-danger cancel_button']); ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.28.4/dist/sweetalert2.all.min.js"></script>

<script type="text/javascript">
   

    function ctrlq_2(e){
        swal("Success!", "Record Inserted successfully in information sheet", "success")
        .then((value) => {
        });
        console.log("Record Inserted successfully in information sheet");
        $("#create_participant").submit();
    }

    function ctrlq(e){
        swal("Success!", "Record Inserted successfully in Module Allotment sheet", "success")
        .then((value) => {
         /* Insert record in sheet */
            console.log("Record Inserted successfully in Module Allotment sheet");
            var token_id = $("#token_id").val();
            var sales_user = $("#sales_user").val();

            var fname = $("#dvregistration-first_name").val();
            var lname = $("#dvregistration-last_name").val();
            var email = $("#dvregistration-email").val();
            var mobile = $("#dvregistration-mobile").val();

            var remarks = $("#dvregistration-remarks").val();
          
            var is_scholarship = $("input[name='DvRegistration[scholarship_offered]']:checked").val();
            var obj_of_running_fields = $("#dvregistration-obj_of_running_fields").val();
            var address = $("#dvregistration-address").val();
            
            var opt_for_3_months  = $("input[name='DvRegistration[opt_for_3_months]']:checked").val();
            if(opt_for_3_months == 'on'){
                opt_for_3_months = 'Yes';
            }else{
                opt_for_3_months = '';
            }
            // alert(opt_for_3_months);

            var country = $("#country").val();
            var state = $("#dvregistration-state").val();
            var city = $("#dvusers-city").val();

            var course = $("#course").val();
             var course_nm = '';
            if(course == 1){
                course_nm = "CDMM";
            }else if(course == 2){
                course_nm = "CPDM";
            }else if(course == 3){
                course_nm = "EM";
            }else if(course == 4){
                course_nm = "SMM";
            }else if(course == 5){
                course_nm = "IM";
            }else if(course == 6){
                course_nm = "SEM";
            }else if(course == 7){
                course_nm = "SEO";
            }else if(course == 8){
                course_nm = "WA";
            }else if(course == 9){
                course_nm = "MM";
            }else if(course == 10){
                course_nm = "CMAM";
            }else if(course == 11){
                course_nm = "CFMM";
            }else if(course == 12){
                course_nm = "TJW";
            }else if(course == 13){
                course_nm = "DAR";
            }else if(course == 14){
                course_nm = "DAP";
            }else if(course == 15){
                course_nm = "DSAS";
            }else if(course == 16){
                course_nm = "DAE";
            }else if(course == 17){
                course_nm = "BDA";
            }else if(course == 18){
                course_nm = "CDMM";
            }

            if(course_nm == "DAR" || course_nm == "DAP" || course_nm == "DAE" || course_nm =="DSAS" ){
                var environment = "<?= Yii::$app->params['environment'] ?>"; // check server enviroment
                // DA Sheet
                if(environment == 'Production'){
                    // live
                    var script_url_master = "https://script.google.com/macros/s/AKfycbw-Gz5S-7h5dhv_WYjcDep-lh-6Pnl4XthIJdcPotbkcapO-PU/exec"; // live
                } else {
                    // development
                    var script_url_master = "https://script.google.com/macros/s/AKfycbzwjMEiOzN1-zFt76uY2sjIQvjSM-K_MfRbtMBCAPRtEP-V1eDC/exec"; // my
                }
                
            } else {
                // DM Sheet
                var environment = "<?= Yii::$app->params['environment'] ?>";// check server enviroment
                if(environment == 'Production'){
                    // live
                    var script_url_master = "https://script.google.com/macros/s/AKfycbzCIUkgx1TQ7ZCmR6qa869w5V49swNpLpchFVf1tP3cLupNvQ8/exec"; // live
                } else {
                    // development
                    var script_url_master = "https://script.google.com/macros/s/AKfycbzLzezdnEf1Oga8qxbp4GXCh1l_VzHH1KLaaz1OS56IeUwxZ8k/exec"; // my
                }
            }

            var course_format = $("#dvregistration-course_format").val();
            var course_batch = $("#dvregistration-course_batch").val();
            var course_batch = course_batch.replace("#", "~");

            var payment_currency = $("#dvparticipantpayments-payment_currency").val();
            var confirm_amount = $("#dvregistration-total_confirmed_amount").val();
            var is_full_payment = $("input[name='DvRegistration[is_full_payment]']:checked").val();

            var amount_recieved = $("#dvparticipantpayments-amount_recieved").val();
            var payment_mode = $("#dvparticipantpayments-payment_mode").val();
            var payment_reference_number = $("#dvparticipantpayments-payment_reference_number").val();
            var modules_allowed = $("#dvregistration-modules_allowed").val();

            // var Coordinator = '';
            //  var status = '';

            $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-registration/get_city_country') ?>',
                type: 'POST',
                dataType: "json",
                async:false,
                data: { city: city, country: country },
                success: function(data){
                  //  alert(data);
                   // alert(data["city"]);
                   // alert(data["country"]);
                   console.log("city "+ data["city"]);
                   console.log("country "+ data["country"]);
                    if(data["result"] == '1'){
                        city = data["city"];
                        country = data["country"];

                        var url = script_url_master+"?callback=ctrlq_2&token_id="+token_id+"&sales_user="+sales_user+"&fname="+fname+"&email="+email+"&phone="+mobile+"&opt_for_3_months="+opt_for_3_months+"&Allowed_Modules="+modules_allowed+"&Course_Name="+course_nm+"&format="+course_format+"&Last_Name="+lname+"&country="+country+"&city="+city+"&course_batch="+course_batch+"&remarks="+remarks+"+&action=insert";

                        $.ajax({
                            crossDomain: true,
                            url: url,
                            method: "GET",
                            dataType: "jsonp",
                            async:false,                        
                        }); 
                    }
                }
            });
           
         });
       
    }
    
    $(document).ready(function(){

        $('body').on('change', '#dvusers-city', function (){
            $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-registration/get_city_country') ?>',
                type: 'POST',
                dataType: "json",
                async:false,
                data: { city: 706, country: 101 },
                success: function(data){
                   console.log("city "+ data["city"]);
                   console.log("country "+ data["country"]);
                }
            });
         });

        $('body').on('change', '#dvusers-city', function (){
            if($(this).val() == ""){
                var cls = $(".field-dvregistration-city").hasClass("has-error");
                if(!cls){
                    $(".field-dvregistration-city").addClass('required has-error');
                }
            }else{
                $(".field-dvregistration-city").removeClass('required has-error');
            }
        });

        $('body').on('change', '#dvusers-state', function (){
            if($(this).val() == ""){
                var cls = $(".field-dvregistration-state").hasClass("has-error");
                if(!cls){
                    $(".field-dvregistration-state").addClass('required has-error');
                }
            }else{
                $(".field-dvregistration-state").removeClass('required has-error');
            }
        });

        

        /* Get Token ID */
        $.ajax({
            url: "<?php echo \Yii::$app->getUrlManager()->createUrl('dv-registration/get_refreshtoken') ?>",
            dataType : 'json',
            success: function(result){
               // console.log(result);
                $("#token_id").val(result["randomString"]);
                $("#sales_user").val(result["sales_username"]);
            }
        });

        // on change select course format
        $('body').on('change', '#dvregistration-course_format', function (){
            $("#loading_custom").show();
            var format_id = $(this).val();
            var course_id = $("#course").val();
            var course_field = '';
            var fieldname = '';
            // var course_name = $("#course").val();

            if(format_id == ''){
                $("#loading_custom").hide();
                $("#dvregistration-course_batch").html("<option value=''>Select Batch</option>");
                return false;
            }


            $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-registration/get_coursenamebyid') ?>',
                type: 'POST',
                data: { course_id: course_id, format_id: format_id },
                async:false,
                success: function(data){
                  // $("#dvregistration-course_batch").replaceWith(data);
                    course_name = data;
                   // alert(course_name);
                  // $("#loading_custom").hide();
                }
            });
            
            course_field = 'course='+course_name;
            if(course_name=='CPDM') {
               // $query->setSpreadsheetQuery('cpdm ="Yes" and format ="'.$format.'"');
               course_field = 'cpdm=Yes';
            }
            if(course_name=='CDMM')  {
               // $query->setSpreadsheetQuery('cdmm ="Yes" and format ="'.$format.'"');
               course_field = 'cdmm=Yes';
            }

            if(course_name=='CFMM')  {
                // $query->setSpreadsheetQuery('cfmm ="Yes" and format ="'.$format.'"');
                course_field = 'cfmm=Yes';
            }

           if((course_name=='DAP')||(course_name=='DSAS')||(course_name=='DAR')||(course_name=='DAE')||(course_name=='DAPS')) {
               // $query->setSpreadsheetQuery('course="'.$course.'" and format ="'.$format.'"');
               course_field = 'course='+course_name+'%20and%20da=Yes';
            }

            if((course_name=='EM')||(course_name=='SMM')||(course_name=='IM')||(course_name=='SEO')||(course_name=='SEM')||(course_name=='WA')||(course_name=='MM')||(course_name=='DMSW')||(course_name=='CMAM')||(course_name=='DVRoom')||(course_name=='SEOS'))  {
              // $query->setSpreadsheetQuery('course="'.$course.'" and cdmm ="Yes" and format ="'.$format.'"');
              course_field = 'course='+course_name+'%20and%20cpdm=Yes';
            }


            $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-registration/get_batch_of_course') ?>',
                type: 'POST',
                data: { course_id: course_id },
                async:false,
                success: function(data){
                    $('#dvregistration-course_batch').html("");
                    $('#dvregistration-course_batch').append("<option value=''>Select Batch</option>");
                    $('#dvregistration-course_batch').append(data);
                    $("#loading_custom").hide();
                }
            });
       });

        $(".tax_diff_rate_div").hide();

        $("#dvregistration-available_batch_opt").change(function(){
            
            if($(this).val() == ""){
                var cls = $( ".field-dvregistration-available_batch_opt").hasClass("has-error");
                if(!cls){
                    $(".field-dvregistration-available_batch_opt").addClass('required has-error');
                }
            }else{
                $(".field-dvregistration-available_batch_opt").removeClass('required has-error');
            }
        });


        $("#dvparticipantpayments-payment_currency").change(function(){
            var cur_val = $("#dvparticipantpayments-payment_currency").val();
            if(cur_val == ''){
                $(".field-dvparticipantpayments-payment_currency").addClass("has-error");
                $(".field-dvparticipantpayments-payment_currency").removeClass("has-success");
            }else{
                $(".field-dvparticipantpayments-payment_currency").removeClass("has-error");
                $(".field-dvparticipantpayments-payment_currency").addClass("has-success");
            }  
        });

        $("#dvparticipantpayments-amount_recieved").focusout(function(){
            var amt_rec_val = $("#dvparticipantpayments-amount_recieved").val();

            if(amt_rec_val == ''){
                $(".field-dvparticipantpayments-amount_recieved").addClass("has-error");
                $(".field-dvparticipantpayments-amount_recieved").removeClass("has-success");
            }else{
                $(".field-dvparticipantpayments-amount_recieved").removeClass("has-error");
                $(".field-dvparticipantpayments-amount_recieved").addClass("has-success");
            }
        }); 

        $("#dvparticipantpayments-payment_mode").change(function(){
            var payment_mode_val = $("#dvparticipantpayments-payment_mode").val();
            if(payment_mode_val == ''){
                $(".field-dvparticipantpayments-payment_mode").addClass("has-error");
                $(".field-dvparticipantpayments-payment_mode").removeClass("has-success");
            }else{
                $(".field-dvparticipantpayments-payment_mode").removeClass("has-error");
                $(".field-dvparticipantpayments-payment_mode").addClass("has-success");
            }  
        });

        $("#dvparticipantpayments-payment_reference_number").focusout(function(){
            var pay_ref_val = $("#dvparticipantpayments-payment_reference_number").val().length;
        
            if(pay_ref_val == '' || pay_ref_val > 17){
                $(".field-dvparticipantpayments-payment_reference_number").addClass("has-error");
                $(".field-dvparticipantpayments-payment_reference_number").removeClass("has-success");
            }else{
                $(".field-dvparticipantpayments-payment_reference_number").removeClass("has-error");
                $(".field-dvparticipantpayments-payment_reference_number").addClass("has-success");
            }
        });

        $("#create_participant").click(function(){
         /*  $("#create_participant").submit();
           return false;
        */
            var cur_val = $("#dvparticipantpayments-payment_currency").val();
            if(cur_val == ''){
                $(".field-dvparticipantpayments-payment_currency").addClass("has-error");
                $(".field-dvparticipantpayments-payment_currency").removeClass("has-success");
            }else{
                $(".field-dvparticipantpayments-payment_currency").removeClass("has-error");
                $(".field-dvparticipantpayments-payment_currency").addClass("has-success");
            }

            var amt_rec_val = $("#dvparticipantpayments-amount_recieved").val();
            if(amt_rec_val == ''){
                $(".field-dvparticipantpayments-amount_recieved").addClass("has-error");
                $(".field-dvparticipantpayments-amount_recieved").removeClass("has-success");
            }else{
                $(".field-dvparticipantpayments-amount_recieved").removeClass("has-error");
                $(".field-dvparticipantpayments-amount_recieved").addClass("has-success");
            }
            
            var payment_mode_val = $("#dvparticipantpayments-payment_mode").val();
            if(payment_mode_val == ''){
                $(".field-dvparticipantpayments-payment_mode").addClass("has-error");
                $(".field-dvparticipantpayments-payment_mode").removeClass("has-success");
            }else{
                $(".field-dvparticipantpayments-payment_mode").removeClass("has-error");
                $(".field-dvparticipantpayments-payment_mode").addClass("has-success");
            }


            var pay_ref_val = $("#dvparticipantpayments-payment_reference_number").val().length;
            if(pay_ref_val == '' || pay_ref_val > 17){
                $(".field-dvparticipantpayments-payment_reference_number").addClass("has-error");
                $(".field-dvparticipantpayments-payment_reference_number").removeClass("has-success");
            }else{
                $(".field-dvparticipantpayments-payment_reference_number").removeClass("has-error");
                $(".field-dvparticipantpayments-payment_reference_number").addClass("has-success");
            }

            var $form = $("#participant_registration_form"), 
            data = $form.data("yiiActiveForm");
            $.each(data.attributes, function() {
                this.status = 3;
            });

            $form.yiiActiveForm("validate");
            if ($("#participant_registration_form").find(".has-error").length) {
                return false;
            }

            
           
            var total_amount = $("#dvregistration-total_confirmed_amount").val();
            var first_payment = $("#dvparticipantpayments-amount_recieved").val();
            var other_payment = 0;
            
            
            if(total_amount != '' && first_payment != ''){

                $('.ins_amt_ins').each(function() {
                    if($(this).val() != ''){
                        other_payment = other_payment + parseInt($(this).val());
                    }
                });

                isconfirm = parseInt(total_amount) - (parseInt(first_payment) + other_payment);

                if(isconfirm != 0){
                    swal(
                      'warning!',
                      'Total amount and installment amount(with first installment) needs to be equal',
                      'warning'
                    );
                    return false;
                }
            }

            $("#loading_custom").show();

            /* Insert record in sheet */
            var token_id = $("#token_id").val();
            var sales_user = $("#sales_user").val();

            var fname = $("#dvregistration-first_name").val();
            var lname = $("#dvregistration-last_name").val();
            var email = $("#dvregistration-email").val();
            var mobile = $("#dvregistration-mobile").val();
            var remarks = $("#dvregistration-remarks").val();
            var is_scholarship = $("input[name='DvRegistration[scholarship_offered]']:checked").val();
            // var obj_of_running_fields = $("#dvregistration-obj_of_running_fields").val();
            
            var available_batch_opt = $("#dvregistration-available_batch_opt").val();
            var available_batch_opt = available_batch_opt.replace("+", "~");

            var promises_notes = $("#dvregistration-promises_notes").val();
            var vskills = $("input[name='DvRegistration[vskills]']:checked").val();
            if(vskills == 1){
                vskills = "Yes";
            }else{
                vskills = "No";
            }
            var address = $("#dvregistration-address").val();
         //    alert(promises_notes);
            
            var opt_for_3_months  = $("input[name='DvRegistration[opt_for_3_months]']:checked").val();
            if(opt_for_3_months == 'on'){
                opt_for_3_months = 'Yes';
            }else{
                opt_for_3_months = '';
            }
            // alert(opt_for_3_months);

            //  var cmam = $("input[name='DvRegistration[free_courses][CMAM]']:checked").val();
            var cmam = $('#cmam_checkbox:checked').val();
            if(cmam == 'CMAM'){
                cmam = 'Yes';
            }else{
                cmam = '';
            } 


            // var cfmm = $("input[name='DvRegistration[free_courses][CFMM]']:checked").val();
            var cfmm = $('#cfmm_checkbox:checked').val();
            if(cfmm == 'CFMM'){
                cfmm = 'Yes';
            }else{
                cfmm = '';
            }

            // var tjw = $("input[name='DvRegistration[free_courses][TJ_Walker]']:checked").val();
            var tjw = $('#tjw_checkbox:checked').val();
            if(tjw == 'TJ_Walker'){
                tjw = 'Yes';
            }else{
                tjw = '';
            }

            // var tableau = $("input[name='DvRegistration[free_courses][Tableau]']:checked").val();
            var tableau = $('#tableau_checkbox:checked').val();
            if(tableau == 'Tableau'){
                tableau = 'Yes';
            }else{
                tableau = '';
            }

            // var cwaw = $("input[name='DvRegistration[free_courses][CWAW]']:checked").val();
            var cwaw = $('#cwaw_checkbox:checked').val();            
            if(cwaw == 'CWAW'){
                cwaw = 'Yes';
            }else{
                cwaw = '';
            }

            var country = $("#country").val();
            var state = $("#dvregistration-state").val();
            var city = $("#dvregistration-city").val();

            var course = $("#course").val();
            var course_nm = '';
            if(course == 1){
                course_nm = "CDMM";
            }else if(course == 2){
                course_nm = "CPDM";
            }else if(course == 3){
                course_nm = "EM";
            }else if(course == 4){
                course_nm = "SMM";
            }else if(course == 5){
                course_nm = "IM";
            }else if(course == 6){
                course_nm = "SEM";
            }else if(course == 7){
                course_nm = "SEO";
            }else if(course == 8){
                course_nm = "WA";
            }else if(course == 9){
                course_nm = "MM";
            }else if(course == 10){
                course_nm = "CMAM";
            }else if(course == 11){
                course_nm = "CFMM";
            }else if(course == 12){
                course_nm = "TJW";
            }else if(course == 13){
                course_nm = "DAR";
            }else if(course == 14){
                course_nm = "DAP";
            }else if(course == 15){
                course_nm = "DSAS";
            }else if(course == 16){
                course_nm = "DAE";
            }else if(course == 17){
                course_nm = "BDA";
            }else if(course == 18){
                course_nm = "CDMM";
            }

            var course_format = $("#dvregistration-course_format").val();
            var course_batch = $("#dvregistration-course_batch").val();

            var course_batch = course_batch.replace("#", "~");

            var payment_currency = $("#dvparticipantpayments-payment_currency").val();
            var confirm_amount = $("#dvregistration-total_confirmed_amount").val();
            var is_full_payment = $("input[name='DvRegistration[is_full_payment]']:checked").val();

            var amount_recieved = $("#dvparticipantpayments-amount_recieved").val();
            var payment_mode = $("#dvparticipantpayments-payment_mode").val();
            var payment_reference_number = $("#dvparticipantpayments-payment_reference_number").val();
            var modules_allowed = $("#dvregistration-modules_allowed").val();

        
            /* Get Published URL from script APP */
            var environment = "<?= Yii::$app->params['environment'] ?>"; // check server enviroment
            if(environment == 'Production'){
                // live
                var script_url = "https://script.google.com/macros/s/AKfycbzFgSakTaaTreJH62j19QRQFqxek29rzbbrDChEJYnajkxQw2s/exec"; // Live
            } else {
                // development
                var script_url = "https://script.google.com/macros/s/AKfycbxiHZGClxKKOdjO-u1IM8mB-5hMBSkCoTWzcpdzHykG96kfOwdZ/exec"; // my
            }

            var url = script_url+"?callback=ctrlq&token_id="+token_id+"&sales_user="+sales_user+"&fname="+fname+"&email="+email+"&phone="+mobile+"&opt_for_3_months="+opt_for_3_months+"&Allowed_Modules="+modules_allowed+"&Course_Name="+course_nm+"&format="+course_format+"&Last_Name="+lname+"&cmam="+cmam+"&cfmm="+cfmm+"&tjw="+tjw+"&remarks="+remarks+"&course_batch="+course_batch+"&promises_notes="+promises_notes+"&vskills="+vskills+"&available_batch_opt="+available_batch_opt+"+&action=insert";

            $.ajax({ 
                crossDomain: true,
                url: url,
                method: "GET",
                dataType: "jsonp",
                async:false,
            }); 
        });

        $("#dvregistration-total_confirmed_amount").keyup(function(){
            var is_pdc = $("input[name='DvRegistration[is_full_payment]']:checked").val();

            if (is_pdc == '1'){ 
                $("#dvparticipantpayments-amount_recieved").val($(this).val());
            }

            var tconf_amount = $(this).val();
            manage_tax($("#dvparticipantpayments-amount_recieved").val());
        });

        // Display PDC based on redio button (is full payment or not) 
        $("#dvregistration-is_full_payment").click(function(){
            $(".ishide").css("display","block");
           // $(".field-dvregistration-modules_allowed").css("display","block");
            var is_pdc = $("input[name='DvRegistration[is_full_payment]']:checked").val();

            if (is_pdc == '1'){
                //$('.field-dvregistration-is_pdc').hide();
                var tconf_amount = $('#dvregistration-total_confirmed_amount').val();
                $('#dvparticipantpayments-amount_recieved').val(tconf_amount);
                $('#dvparticipantpayments-amount_recieved').attr('readonly', true);

                manage_tax(tconf_amount);
            }else{
                //$('.field-dvregistration-is_pdc').show();
                $('#dvparticipantpayments-amount_recieved').val('');
                $('#dvparticipantpayments-amount_recieved').attr('readonly', false);
                $("#row_amount").val('');
                $("#tax_amount").val('');
            }
        });


        // Is email exist or not
        $("#dvregistration-email").keyup(function(){
            var email = $(this).val();
            var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            if(regex.test(email)) {
                $("#loading_custom").show();
            
                 $.ajax({
                    url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-registration/check_wp_email') ?>',
                    type: 'POST',
                    dataType: "json",
                    data: { email: email },
                    success: function(data){
                        // console.log(data["wp_id"]);
                        $(".field-dvregistration-email span.success").remove();
                        if(data["result"] == '1'){
                            $("#wp_id").val(data["wp_id"]);
                            $("#wp_user").val(data["wp_user"]);
                            $("#dvregistration-first_name").val(data["first_name"]);
                            $("#dvregistration-last_name").val(data["last_name"]);
                            $(".field-dvregistration-email").append("<span class='success alert-success'>User with this email id is exist in wordpress  </span>");
                            $("#loading_custom").hide();
                        }else{                                    
                            $("#dvregistration-first_name").val('');
                            $("#dvregistration-last_name").val('');
                            $(".field-dvregistration-email span.success").remove();
                            $("#wp_id").val('');
                            $("#wp_user").val('');
                            $("#loading_custom").hide();
                        }
                    }
                });
        }                 
                return false;
        });
    });

    $("#dvparticipantpayments-amount_recieved").change(function(){
	var row_amt = $(this).val();
        manage_tax(row_amt);
    });
//    $("#dvparticipantpayments-amount_recieved").blur(function(){
//	var row_amt = $(this).val();
//        manage_tax(row_amt);
//    });
    $(document).on('change','#dvparticipantpayments-payment_currency',function(){
        
        var india_currency_id = <?= DvRegistration::INDIA_CURRENCY_FROM_DV_CURRENCY ?>;
        if ($(this).val() == india_currency_id) {
            $(".tax_diff_rate_div").show();
        } else {
            $(".tax_diff_rate_div").hide();
        }
    });
    $(document).on('change','#dvusers-state',function(){
        var row_amt = $("#dvparticipantpayments-amount_recieved").val();
        manage_tax(row_amt);
    });

    /* Display amount without Tax and Display only Tax in readonly text field */
    function manage_tax(row_amt) {
        var state = $("#dvregistration-state").val();
        var delhi_id = <?= DvRegistration::DELHI_ID_DV_FROM_STATES ?>;
        var gst_rate = 18;
        /*if (state == delhi_id) {
            gst_rate = 18;
        }*/
	var tax_amt = row_amt / (1+(gst_rate/100));
	$("#row_amount").val(tax_amt.toFixed(2));
	$("#tax_amount").val((row_amt - tax_amt).toFixed(2));
    }

    function isValidEmail(email){
        return /^[a-z0-9]+([-._][a-z0-9]+)*@([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,4}$/.test(email)
            && /^(?=.{1,64}@.{4,64}$)(?=.{6,100}$).*/.test(email);
    }

    $(document).on('keydown', 'input[pattern]', function(e){
      var input = $(this);
      var oldVal = input.val();
      var regex = new RegExp(input.attr('pattern'), 'g');

      setTimeout(function(){
        var newVal = input.val();
        if(!regex.test(newVal)){
          input.val(oldVal); 
        }
      }, 0);
    });

    //Begin 03 June 2019
    function get_course_data(){
        var course_domain = $('#course_domain').val();
        if(course_domain!=''){
            $("#loading_custom").show();
            $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-registration/course_domain_check') ?>',
                type: 'POST',
                data: { course_domain : course_domain },
                success: function(data){
                    $("#loading_custom").hide();
                    $('#dvregistration #course').replaceWith(data);
                }
            });
        }
    }//End of JS:get_course_data
</script>

