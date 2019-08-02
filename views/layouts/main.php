<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
#use app\models\DvParticipantModulesSession;

/* @var $this \yii\web\View */
/* @var $content string */

if (Yii::$app->controller->action->id === 'login') { 
/**
 * Do not use this code in your template. Remove it. 
 * Instead, use the code  $this->layout = '//main-login'; in your controller.
 */
    echo $this->render(
        'main-login',
        ['content' => $content]
    );
} else {

    if (class_exists('backend\assets\AppAsset')) {
        backend\assets\AppAsset::register($this);
    } else {
        app\assets\AppAsset::register($this);
    }

    dmstr\web\AdminLteAsset::register($this);

    $directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
    ?>
    <?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">   
        
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />

        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
        <style type="text/css"> .rowdisable > td > a {display:none !important;} </style>
        <link rel="shortcut icon" href="<?=Yii::$app->params['yii_url']?>/favicon.ico" />
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
    </head>
    <body class="hold-transition skin-blue sidebar-mini">
    <?php $this->beginBody() ?>
    <div class="wrapper">

        <?= $this->render(
            'header.php',
            ['directoryAsset' => $directoryAsset]
        ) ?>

        <?= $this->render(
            'left.php',
            ['directoryAsset' => $directoryAsset]
        )
        ?>

        <?= $this->render(
            'content.php',
            ['content' => $content, 'directoryAsset' => $directoryAsset]
        ) ?>

    </div>
<div style="display: none" class="loading_custom" id="loading_custom">Please wait while Loading&#8230;</div>
<button onclick="topFunction()" id="myBtn" title="Go to top"><i class="fa fa-fw fa-chevron-up"></i></button>

<?php $this->endBody() ?>
<script src="//code.jquery.com/ui/1.10.2/jquery-ui.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
<script src="https://getbootstrap.com/2.3.2/assets/js/bootstrap-tooltip.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>



<?php // for Module Edit page only
$controller_name = Yii::$app->controller->id;
$controller_function = Yii::$app->controller->action->id;
$add_count = 2;
if(($controller_name == 'dv-delivery') && ($controller_function == 'edit')){
    $total_session = 0;
    $mid = $_GET['id'];
    $total_session_2 = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'all_sessions' ")->queryOne(); 
    //$total_session = (int)$total_session_2;
    $total_session = $total_session_2['meta_value'];
    if($total_session  == 0){
        $add_count = 1;
    } elseif($total_session > 1){
        $add_count = $total_session+1;
    } else{
    }
    $add_count; 
}
// for Module Edit page only
?>

<script>
// When the user scrolls down 20px from the top of the document, show the button
window.onscroll = function() {scrollFunction()};

function scrollFunction() {
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        document.getElementById("myBtn").style.display = "block";
    } else {
        document.getElementById("myBtn").style.display = "none";
    }
}

// When the user clicks on the button, scroll to the top of the document
function topFunction() {
    document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;
}
</script>

<script type="text/javascript">
    var exception_closures_counter = 0;
$("document").ready(function(){

   // $('#dvmodulemodel-lms_course').select2();
    
    

    $(".field-dvregistration-available_batch_opt").css("display","none");
    $('#date_for_daily').datepicker({dateFormat: 'yy-mm-dd'});
    //$('#weekly_date_to').datepicker({dateFormat: 'yy-mm-dd'});
    // $('#weekly_date_from').datepicker({dateFormat: 'yy-mm-dd'});

    $(document).on("click", ".enable_disable_target", function(){
        var month_year = $(this).attr('id');
        month_year = month_year.split("_");
        var month = month_year[2];
        var year = month_year[3];
        var is_checked = $(this).prop('checked') ? 1 : 0;
        
        $.ajax({
            url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-users/enable_disable_target') ?>',
                type: 'POST',
                data: { month:month,year:year,is_checked:is_checked},
                success: function(data){
                    var month_str = 'disable_month_' + month + '_' + year;
                    if(is_checked == 1) {
                        $("label[for='"+month_str+"']").html("Active");
                        $("table[tab='"+month_str+"']").removeClass("in_active_target_table");
                    } else {
                        $("label[for='"+month_str+"']").html("In-Active");
                        $("table[tab='"+month_str+"']").addClass("in_active_target_table");
                    }
                }
        });
        
    });

    $(document).on("change", "#sales_managers", function(){
        var sales_manager = $(this).val();
        var sales_manager_name = $( "#sales_managers option:selected" ).text();

        if (sales_manager != '') {
            $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-reports/get_executive_of_manager') ?>',
                type: 'POST',
                data: { sales_manager:sales_manager},
                success: function(data){
                    var executive = JSON.parse(data);
                    $("#executives").html('<option value="">Select Consultant</option>');
                    $.each(executive, function(key, val) {
                        $("#executives").append('<option value="'+val.id+'">'+val.first_name + " " + val.last_name +'</option>');
                    });
                    $("#executives").append('<option value="'+sales_manager+'">'+sales_manager_name+'</option>');
                }
            });
        }
    });
        
    $("#opt_for_3_months").click(function(){
        if($(this).prop('checked')){
            $(".field-dvregistration-available_batch_opt").css('display','block');
            $(".field-dvregistration-available_batch_opt").addClass('required has-error');
        }else{
            $(".field-dvregistration-available_batch_opt").css('display','none');
            $(".field-dvregistration-available_batch_opt").removeClass('required has-error');
            $("#dvregistration-available_batch_opt").val('');

        }
    });

    $('[data-toggle="tooltip"]').tooltip();
    $(document).on('mouseover', '[data-toggle="tooltip"]', function(){
        $('[data-toggle="tooltip"]').tooltip();
    });

    var counter = <?php echo $add_count; ?>;
    if(counter>2){
        if($('#remove_btn_nos').val() >=1){
            $('#removeButton').show();
            $('#removeButton2').show();
        }
    }

    $(document).on('click', '#addButton', function(){
        $('#removeButton').show();
        $('#fend_date').remove();
        
        <?php $moduleid = Yii::$app->request->get('id'); 
            if(empty($moduleid)){
                $module_id = '';
            } else{
                $module_id = ', module_id:'.$moduleid;
            } ?>
        var totalsess = $('#total_session').attr("data-total-session"); 
        //alert(totalsess); 


        //begin 23 may 2019//
        $('#sessions_data_updates_count').val(parseInt($('#sessions_data_updates_count').val())+parseInt(1));
        if($('#sessions_data_updates_count').val() == 1){
            $('#reschedule_count_block').hide(); // 21 May 2019
            $('.text_area_data').replaceWith("<textarea class='form-control text_area_data' rows='5' name='reschedule_text'></textarea>");
        }else{ 
            $('#reschedule_count_block').show(); // 21 May 2019
            $('.text_area_data').attr('required','required');//22 may 2019
        }
        //end 23 may 2019//
        //alert(totalsess);
        var pre_date = $('.session'+totalsess+' input').val();
        //alert(totalsess);
        //make date diffrence 29 April 2019
        var dt_splt = pre_date.split('-'); //25-04-2019 
        const date1 = new Date(); //m/d/yyyy 
        const date2 = new Date(dt_splt[1]+'/'+dt_splt[0]+'/'+dt_splt[2]); 
        //const diffTime = Math.abs(date2.getTime() - date1.getTime());
        const diffTime = date2.getTime() - date1.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        if(diffDays >= 1){
           $('#remove_btn_nos').val(diffDays);
        }
        //end make date diffrence
        var start_stime = $('#dvassistbatches-stiming').val();
        var end_etime = $('#dvassistbatches-etiming').val();
        var counter = parseInt(totalsess) + 1;
        $('#dvassistbatches-type').val(counter); //added on 16 May 2019
        //$('#removeButton').show();
        if(counter>25){
            alert("Only 25 Session allow");
            return false;
        }
        $("#loading_custom").show();
        $("#total_session").replaceWith('<h3 class="blue_color" id="total_session" data-total-session="'+counter+'">No. of Sessions: '+counter+' </h3>');
        $("#all_sessions").replaceWith('<input id="all_sessions" name="all_sessions" value="'+counter+'" type="hidden">');

        

        // var newTextBoxDiv = $(document.createElement('div')).attr("id", 'TextBoxDiv' + counter);
        var newTextBoxDiv = $(document.createElement('div')).attr({
                                                                    id:'TextBoxDiv' + counter, 
                                                                    class:"row"
                                                                });
         
        $.ajax({ url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-delivery/add_session') ?>',
                    type: 'POST',
                    data: { counter:counter,pre_date:pre_date<?php echo $module_id; ?>,start_stime:start_stime,end_etime:end_etime},
                    success: function(data){
                        newTextBoxDiv.after().html(data);
                       // $("#loading_custom").hide();
                        $.ajax({ url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-delivery/add_session_enddate') ?>',
                            type: 'POST',
                            data: { counter:counter,pre_date:pre_date<?php echo $module_id; ?>},
                            success: function(data1){  
                                $("#module_end_date").replaceWith(data1);
                                $('#set_end_date_hidden').val($("#module_end_date").val());
                                $("#loading_custom").hide();
                            }
                        });
                    }
                });

        newTextBoxDiv.appendTo("#TextBoxesGroup");
        counter++;
    });

    $(document).on('click', '#removeButton', function(){
        var counter = $('#total_session').attr("data-total-session");
        var tcounter = $('div.hide').attr("data-totalsession");
        //begin 23 may 2019//
        $('#sessions_data_updates_count').val(parseInt($('#sessions_data_updates_count').val())-parseInt(1));
        if($('#sessions_data_updates_count').val() == 1){
            $('#reschedule_count_block').hide(); // 21 May 2019
            $('.text_area_data').replaceWith("<textarea class='form-control text_area_data' rows='5' name='reschedule_text'></textarea>");
        }else{
            $('#reschedule_count_block').show(); // 21 May 2019
            $('.text_area_data').attr('required','required');//22 may 2019
        }
        //end 23 may 2019//
        $('#dvassistbatches-type').val(parseInt(counter) - 1); //added on 16 May 2019
        if(counter>tcounter){
            var n_counter = parseInt(counter) - 1;
            $("#total_session").replaceWith('<h3 class="blue_color" id="total_session" data-total-session="'+n_counter+'">No. of Sessions: '+n_counter+' </h3>');
            $("#all_sessions").replaceWith('<input id="all_sessions" name="all_sessions" value="'+n_counter+'" type="hidden">');
            var pre_date = $('#session_'+n_counter).val();  
            $("#fend_date").replaceWith('<input name="fend_date" id="fend_date" value="'+pre_date+'" type="hidden">');
            $("#module_end_date").replaceWith('<input id="module_end_date" class="form-control hasDatepicker" title="" placeholder="End Date" data-toggle="tooltip" readonly="readonly" data-placement="top" aria-required="true" data-original-title="End Date" aria-invalid="false" value="'+pre_date+'">');
            $('#set_end_date_hidden').val($("#module_end_date").val());
            $("#TextBoxDiv" + counter).remove();
            //For Remove Button make date diffrence 29 April 2019
            var dt_splt = pre_date.split('-'); //25-04-2019 
            const date1 = new Date(); //m/d/yyyy 
            const date2 = new Date(dt_splt[1]+'/'+dt_splt[0]+'/'+dt_splt[2]); 
            const diffTime = date2.getTime() - date1.getTime();
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            if(diffDays < 0){
                $('#removeButton').hide();
            } 
            //end make date diffrence
            //End Remove btn
            if(n_counter==tcounter){
                $('#removeButton').hide();
            }
        } 
    });

    $(document).on('click', '#addButton2', function(){
        var totalsess =  '';
        //if(document.getElementById('dvassistbatches-day').value == 'tue-thu'){
            //totalsess = parseInt($('#total_session').attr("data-total-session"))+parseInt(2);
        //}else{
            totalsess = $('#total_session').attr("data-total-session");
        //}
        //begin 23 may 2019//
        $('#sessions_data_updates_count').val(parseInt($('#sessions_data_updates_count').val())+parseInt(1));
        if($('#sessions_data_updates_count').val() == 1){
            $('#reschedule_count_block').hide(); // 21 May 2019
            $('.text_area_data').replaceWith("<textarea class='form-control text_area_data' rows='5' name='reschedule_text'></textarea>");
        }else{ 
            $('#reschedule_count_block').show(); // 21 May 2019
            $('.text_area_data').attr('required','required');//22 may 2019
        }
        //end 23 may 2019//
        //alert($("#module_end_date").val());
        var pre_date = $('#session_'+totalsess).val();
        <?php $moduleid = Yii::$app->request->get('id'); 
            if(empty($moduleid)){
                $module_id = '';
            } else{
                $module_id = ', module_id:'.$moduleid;
            } ?>
        $('#fend_date').remove();
        var counter = parseInt(totalsess) + 2;
         
        var start_stime = $('#dvassistbatches-stiming').val();
        var end_etime = $('#dvassistbatches-etiming').val();
        //alert(start_stime); alert(end_etime);
        $('#dvassistbatches-type').val(counter/2); //added on 16 May 2019
        $('#removeButton2').show();
        if(counter>50){
            alert("Only 50 Session allow");
            return false;
        }
        $("#loading_custom").show();
        $("#total_session").replaceWith('<h3 class="blue_color" id="total_session" data-total-session="'+counter+'">No. of Sessions: '+counter+' </h3>');
        $("#all_sessions").replaceWith('<input id="all_sessions" name="all_sessions" value="'+counter+'" type="hidden">');
        var newTextBoxDiv = $(document.createElement('div')).attr("id", 'TextBoxDiv' + totalsess);

        $.ajax({ url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-delivery/add_session2') ?>',
                    type: 'POST',
                    data: { counter:totalsess,pre_date:pre_date<?php echo $module_id; ?>,start_stime:start_stime,end_etime:end_etime},
                    success: function(data){
                        newTextBoxDiv.after().html(data);
                        //$('#set_end_date_hidden').val($("#module_end_date").val());
                        $("#loading_custom").hide();
                        //End date set
                        $.ajax({ url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-delivery/add_session_enddate') ?>',
                            type: 'POST',
                            data: { counter:counter,pre_date:pre_date<?php echo $module_id; ?>},
                            success: function(data){                                
                                $("#module_end_date").replaceWith(data);
                                $('#set_end_date_hidden').val($("#module_end_date").val());
                                $("#loading_custom").hide();
                            }
                        });
                        //End of End date set
                        }
                    });

        newTextBoxDiv.appendTo("#TextBoxesGroup");
        counter++;
        //document.getElementById('total_session').value
        //$('#total_session').attr("data-total-session",totalsess);
    });

    $(document).on('click', '#removeButton2', function(){
        if($('#total_session').attr("data-total-session") > 2 ){ 
            var counter = $('#total_session').attr("data-total-session");
            var tcounter = $('div.hide').attr("data-totalsession");
            var sess_end_date_find = $('#set_end_date_hidden').val();
            //begin 23 may 2019//
            $('#sessions_data_updates_count').val(parseInt($('#sessions_data_updates_count').val())-parseInt(1));
            if($('#sessions_data_updates_count').val() == 1){
                $('#reschedule_count_block').hide(); // 21 May 2019
                $('.text_area_data').replaceWith("<textarea class='form-control text_area_data' rows='5' name='reschedule_text'></textarea>");
            }else{
                $('#reschedule_count_block').show(); // 21 May 2019
                $('.text_area_data').attr('required','required');//22 may 2019
            }
            //end 23 may 2019//
            if(counter == tcounter){
                // in the case of equal
            } else {
                var n_counter = parseInt(counter) - 2; 
                //var n_counter = parseInt(counter);
                $("#total_session").replaceWith('<h3 class="blue_color" id="total_session" data-total-session="'+n_counter+'">No. of Sessions: '+n_counter+' </h3>');
                $("#all_sessions").replaceWith('<input id="all_sessions" name="all_sessions" value="'+n_counter+'" type="hidden">');
                //$("#TextBoxDiv" + n_counter).remove();
                //Updated at 29 April 2019
                $("#TextBoxDiv" + counter).remove();
                var next_id = '';
                next_id = parseInt(counter) - 1;
                $("#TextBoxDiv" + next_id).remove();
                //added at 30 April 2019
                $("#module_end_date").val($('#session_'+n_counter).val());
                $('#set_end_date_hidden').val($("#module_end_date").val());
                if(n_counter==tcounter){
                    $('#removeButton2').hide();
                }
                $('#dvassistbatches-type').val(n_counter/2); //added on 16 May 2019
            }
        }
    });
    // add new sessions upto 20

    // add new Installment  uptp 6
    var counter1 = 3;
    if(counter1>3){
        $('#remove_Button').show();
    }
    $("#dvregistration-is_pdc").click(function(){
        if($(this).prop('checked')){
            $(".pdc_ref_form_field").css('display','block');
        }else{
            $(".pdc_ref_form_field").css('display','none');
        }
    });
    $("#add_Button").click(function(){
        $('#remove_Button').show();
        if(counter1>6){
            alert("Only 6 Installments allow");
            return false;
        }  
    //var newTextBoxDiv = $(document.createElement('div')).attr("id", 'TextBoxDiv' + counter1);
    var newTextBoxDiv = $('<div id="TextBoxDiv'+counter1+'" class="form-group" style="float:left;width:100%;">');
    var pattern = "^\\d*(\\.\\d{0,2})?$";
    newTextBoxDiv.after().html('<div class="form-group col-md-4">\
            <input id="insamt_'+ counter1 +'" class="form-control ins_amt_ins" pattern="'+pattern+'" name="installment['+ counter1 +'][amt]" required="required"  type="text" placeholder="Installment no: '+ counter1 +' (Committed Amount)" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Installment no: '+ counter1 +' (Committed Amount)">\
                    <em>(including Service Tax)</em>\
            <div class="help-block"></div>\
        </div>\
        <div class="form-group col-md-4">\
            <input autocomplete="off" id="insdate_'+ counter1 +'"  class="form-control" name="installment['+ counter1 +'][date]" required="required"  type="text" placeholder="Installment no: '+ counter1 +' (Committed Date)" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Installment no: '+ counter1 +' (Committed Date)">\
            <div class="help-block"></div>\
        </div>\
        <div class="form-group col-md-4">\
            <input id="insref_'+ counter1 +'"  class="form-control pdc_ref_number" name="installment['+ counter1 +'][ref_number]" type="text" placeholder="Installment no: '+ counter1 +' (Cheque Reference Number)" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Installment no: '+ counter1 +' (Cheque Reference Number)">\
            <div class="help-block">\
        </div>');
            
    newTextBoxDiv.appendTo("#installmentboxe");
    if($("#dvregistration-is_pdc").prop('checked')){
        $(".pdc_ref_form_field").css('display','block');
    }else{
        $(".pdc_ref_form_field").css('display','none');
    }

    counter1++;
    });

    $("#remove_Button").click(function(){
        if(counter1==4){
            $('#remove_Button').hide();
        }
       if(counter1>2){
            counter1--;
            $("#TextBoxDiv" + counter1).remove();
       }
    });
    // add new Installment  uptp 6


    $('#start_date').datepicker();
    
    /*
    $('#dvassistbatches-stiming').timepicker({
            timeFormat: 'h:mm p',
            interval: 30,
            minTime: '10',
            maxTime: '09:30pm',
        //  defaultTime: '11',
            startTime: '10:00',
            dynamic: false,
            dropdown: true,
            scrollbar: true
    }) 

    $('#dvassistbatches-etiming').timepicker({
            timeFormat: 'h:mm p',
            interval: 30,
            minTime: '10',
            maxTime: '09:30pm',
        //  defaultTime: '11',
            startTime: '10:00',
            dynamic: false,
            dropdown: true,
            scrollbar: true
    });
    */

    //$('#dvassistbatches-etiming').on('focusout', function() { //alert(4);
    //$('input[name=etiming]').change(function() {  
    
    $(document).on('change','#dvassistbatches-etiming,#dvassistbatches-stiming', function(e){
        if(document.getElementById('dvassistbatches-stiming').value !='' && document.getElementById('dvassistbatches-etiming').value !=''){
                //var st = '10:00 AM';
                //var et = '11:10 AM';
                var st = document.getElementById('dvassistbatches-stiming').value;
                var et = document.getElementById('dvassistbatches-etiming').value
                //////////////////////////////////////////////////////////////
                var time = st;
                var hrs = Number(time.match(/^(\d+)/)[1]);
                var mnts = Number(time.match(/:(\d+)/)[1]);
                var format = time.match(/\s(.*)$/)[1];
                if (format == "PM" && hrs < 12) hrs = hrs + 12;
                if (format == "AM" && hrs == 12) hrs = hrs - 12;
                var hours = hrs.toString();
                var minutes = mnts.toString();
                if (hrs < 10) hours = "0" + hours;
                if (mnts < 10) minutes = "0" + minutes;
                //alert(hours+ ":" + minutes);
                var date1 = new Date();
                date1.setHours(hours);
                date1.setMinutes(minutes);
                //alert(date1);
                //////////////////////////////////////////////////////////////
                var etime = et;
                var ehrs = Number(etime.match(/^(\d+)/)[1]);
                var emnts = Number(etime.match(/:(\d+)/)[1]);

                var eformat = etime.match(/\s(.*)$/)[1];
                if (eformat == "PM" && ehrs < 12) ehrs = ehrs + 12;
                if (eformat == "AM" && ehrs == 12) ehrs = ehrs - 12;
                var ehours = ehrs.toString();
                var eminutes = emnts.toString();
                if (ehrs < 10) hours = "0" + hours;
                if (emnts < 10) minutes = "0" + minutes;
                //alert(ehours+ ":" + eminutes);
                var date2 = new Date();
                date2.setHours(ehours);
                date2.setMinutes(eminutes);
                //alert(date2);
                //////////////////////////////////////////////////////////////
                var diff = date2.getTime() - date1.getTime();
                var hours = Math.floor(diff / (1000 * 60 * 60));
                diff -= hours * (1000 * 60 * 60);
                var mins = Math.floor(diff / (1000 * 60));
                diff -= mins * (1000 * 60);
                $('#time_different').val(hours + " hours " + mins + " minutes");
                //alert( hours + " hours : " + mins + " minutes : " ); 
            }//End of hrs & minutes
    });
       
    /*
    $(document).on('click','.session_time', function(e) {
        $('.session_time').timepicker({
            timeFormat: 'h:mm p',
            interval: 30,
            minTime: '10',
            maxTime: '09:30pm',
        //  defaultTime: '11',
            startTime: '10:00',
            dynamic: false,
            dropdown: true,
            scrollbar: true
            });
    });
    */ 

<?php // for Module Edit page only
$controller_name = Yii::$app->controller->id;
$controller_function = Yii::$app->controller->action->id; ?>
    $('#dvassistbatches-start_date').datepicker({
        dateFormat: 'dd-mm-yy',<?php
        if(( $controller_name == 'dv-delivery') && ($controller_function != 'edit')){ ?>
            minDate: 0,
        <?php }

        // Get Start Date
        $module_id = Yii::$app->request->get('id');
        $session_1_date = Yii::$app->db->createCommand("SELECT start_date FROM assist_batches WHERE id = '$module_id' ")->queryOne();
        $session_one_date = $session_1_date['start_date'];?>
            onSelect: function (date){
                var prev_date = '<?php echo $session_one_date; ?>';
                var sdate = $(this).val();                
            if(prev_date != sdate){
                //alert(23) 
                // condition to check the same date
                $('#reschedule_num').val('1');
                $("#session_1").val(sdate);
                $("#addButton").hide();
                $(document).ajaxStart(function(){
                    $("#loading_custom").show();
                });
            $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-delivery/get_day') ?>',
                type: 'POST',
                data: { sdate: sdate},
                success: function(data){
                    $("#dvassistbatches-day").replaceWith(data);
                    $.ajax({
                        url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-delivery/get_weeks') ?>',
                        type: 'POST',
                        data: { day_id:sdate},
                        success: function(data){
                            $("#dvassistbatches-type").replaceWith(data);
                            $("#TextBoxesGroup").replaceWith("<div id='TextBoxesGroup'> </div>");
                            $('#confirm-submit .modal-body .session_error').replaceWith('<div class="session_error"></div>');
                            $("#loading_custom").hide();
                        }
                    });
                }
            });
          }  // condition to check the same date
        }
    });

    
    // on change Number of weeks
    $(document).on('change', '#dvassistbatches-type', function(){        
        if(this.value != ''){            
            var type_id = $(this).val();
            $("#addButton").show();
            $("#addButton2").show();
            <?php $moduleid = Yii::$app->request->get('id'); 
            if(empty($moduleid)){
                $module_id = '';
            } else{
                $module_id = ', module_id:'.$moduleid;
            } ?>
            var start_date = $('#dvassistbatches-start_date').val();
            var Sday = $('#dvassistbatches-day').val();
            var start_stime = $('#dvassistbatches-stiming').val();
            var end_etime = $('#dvassistbatches-etiming').val();
            var time_duration = $('#time_different').val();
            if(start_date == ''){
                $("#dvassistbatches-start_date").focus();
            } else {
            $("#loading_custom").show();
            $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-delivery/get_session') ?>',
                type: 'POST',
                data: { type_id: type_id,start_date:start_date,Sday:Sday<?php echo $module_id ?>,start_stime:start_stime,end_etime:end_etime,time_duration:time_duration},
                success: function(data){
                    $("#TextBoxesGroup").replaceWith(data);
                        $.ajax({
                            url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-delivery/get_session_enddate') ?>',
                            type: 'POST',
                            data: { type_id: type_id,start_date:start_date,Sday:Sday<?php echo $module_id ?>},
                            success: function(data){
                                $("#module_end_date").replaceWith(data);
                                $('#set_end_date_hidden').val($("#module_end_date").val());
                                $("#loading_custom").hide();
                            }
                        });
                    //$("#loading_custom").hide();
                }
            });

          }
        }
    });


    // on change Day
    $(document).on('change', '#dvassistbatches-day', function(){    
        if(this.value != ''){            
            var day_id = $(this).val(); 
            if(day_id=='tue-thu'){
                $('#addButton').attr("id","addButton2");
                $('#removeButton').attr("id","removeButton2");
            } else {
                $('#addButton2').attr("id","addButton");
                $('#removeButton2').attr("id","removeButton");
            }

            $(document).ajaxStart(function(){
                $("#loading_custom").show();
            });
            $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-delivery/get_weeks') ?>',
                type: 'POST',
                data: { day_id:day_id},
                success: function(data){
                    $("#dvassistbatches-type").replaceWith(data);
                    $("#TextBoxesGroup").replaceWith("<div id='TextBoxesGroup'> </div>");
                    $("#addButton").hide();
                    $("#loading_custom").hide();
                }
            });
        }
    });

    <?php /* // disable the session one and get their date from start date ?>

    $(document).on('click', '#session_1', function(){
        $('#session_1').datepicker({
            dateFormat:'dd-mm-yy',<?php
        //if(( $controller_name == 'dv-delivery') && ($controller_function != 'edit')){ ?>
            minDate: 0,
        <?php //} ?>
            onSelect: function (date){
                var sdate = $(this).val();
                $("#dvassistbatches-start_date").val(sdate);
            }}).focus();
    }); <?php */ ?>

        /**Time picker added on 18 April 2019 
        *create time picker at dv-delievery for dynamic sessions
        **/
         <?php $time_count = 2; for($i = 1; $i<=24; $i++){?>
            $(document).on('click','#session_stime<?php echo $time_count; ?>', function(e) { 
                var pre_date = $('#session_<?php echo $time_count; ?>').val();
                //added on 21 May 2019
                var pre_s_time = $('#session_stime<?php echo $time_count; ?>').val().split(':');
                var get_hours = pre_s_time[0];
                var get_minutes = pre_s_time[1].split(' ');
                var get_actual_minutes = get_minutes[0];
                var am_pm = get_minutes[1];
                var dt_splt = pre_date.split('-'); //25-04-2019 
                const date1 = new Date(); //m/d/yyyy 
                const date2 = new Date(dt_splt[1]+'/'+dt_splt[0]+'/'+dt_splt[2]); 
                const diffTime = date2.getTime() - date1.getTime();
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                if(diffDays >= 1){
                    $('#session_stime<?php echo $time_count; ?>').timepicker({
                        timeFormat: 'h:mm p',
                        interval: 30,
                        minTime: '10',
                        maxTime: '09:30pm',
                        startTime: '10:00',
                        dynamic: false,
                        dropdown: true,
                        scrollbar: true,
                        change: function(time) {  
                            if($('#actual_sessions_').val() >= <?php echo $time_count; ?>){     
                                var minutes_val = '';
                                if(get_actual_minutes == '00'){
                                    minutes_val = 0;
                                }else{
                                    minutes_val = get_actual_minutes;
                                }
                                if(get_hours == time.getHours() && minutes_val == time.getMinutes()){
                                    $('#sessions_data_updates_count').val(parseInt($('#sessions_data_updates_count').val())-parseInt(1));
                                    if($('#sessions_data_updates_count').val()==1){
                                        $('#reschedule_count_block').hide();//21 May 2019
                                        $('.text_area_data').replaceWith("<textarea class='form-control text_area_data' rows='5' name='reschedule_text'></textarea>");
                                    }
                                }else{
                                    $('#reschedule_count_block').show();//21 May 2019 
                                    $('.text_area_data').attr('required','required');//22 may 2019
                                    $('#sessions_data_updates_count').val(parseInt($('#sessions_data_updates_count').val())+parseInt(1));
                                }
                            }
                        }
                    });
                }

                 
                 
            });

            $(document).on('click','#session_etime<?php echo $time_count; ?>', function(e) {
                var pre_date = $('#session_<?php echo $time_count; ?>').val();
                var pre_e_time = $('#session_etime<?php echo $time_count; ?>').val().split(':');
                var get_hours = pre_e_time[0];
                var get_minutes = pre_e_time[1].split(' ');
                var get_actual_minutes = get_minutes[0];
                var am_pm = get_minutes[1];
                var dt_splt = pre_date.split('-'); //25-04-2019 
                const date1 = new Date(); //m/d/yyyy 
                const date2 = new Date(dt_splt[1]+'/'+dt_splt[0]+'/'+dt_splt[2]); 
                const diffTime = date2.getTime() - date1.getTime();
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                if(diffDays >= 1){
                    $('#session_etime<?php echo $time_count; ?>').timepicker({
                        timeFormat: 'h:mm p',
                        interval: 30,
                        minTime: '10',
                        maxTime: '09:30pm',
                        startTime: '10:00',
                        dynamic: false,
                        dropdown: true,
                        scrollbar: true,
                        change: function(time) {
                            if($('#actual_sessions_').val() >= <?php echo $time_count; ?>){    
                                var minutes_val = '';
                                if(get_actual_minutes == '00'){
                                    minutes_val = 0;
                                }else{
                                    minutes_val = get_actual_minutes;
                                }
                                if(get_hours == time.getHours() && minutes_val==time.getMinutes()){
                                    $('#sessions_data_updates_count').val(parseInt($('#sessions_data_updates_count').val())-parseInt(1));
                                    if($('#sessions_data_updates_count').val()==1){
                                        $('#reschedule_count_block').hide();//21 May 2019
                                        $('.text_area_data').replaceWith("<textarea class='form-control text_area_data' rows='5' name='reschedule_text'></textarea>");
                                    }
                                }else{
                                    $('#reschedule_count_block').show();//21 May 2019 
                                    $('.text_area_data').attr('required','required');//22 may 2019
                                    $('#sessions_data_updates_count').val(parseInt($('#sessions_data_updates_count').val())+parseInt(1));
                                }
                            }
                        }
                    });
                }
            });
        <?php $time_count++; } ?>

        <?php $count = 2; for($i = 1; $i<=39; $i++){ // create date picker from 2 to 40 dv-delivery/create_module
            ?>
            $(document).on('click', '#session_<?php echo $count; ?>', function(){
                var module_id = $('#edit_module_form').val();
                var pre_date = $('#session_<?php echo $count; ?>').val();
                var dt_splt = pre_date.split('-'); //25-04-2019 
                const date1 = new Date(); //m/d/yyyy 
                const date2 = new Date(dt_splt[1]+'/'+dt_splt[0]+'/'+dt_splt[2]); 
                const diffTime = date2.getTime() - date1.getTime();
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                //End date
                var sess_id = '';
                if(diffDays >= 1){ 
                    sess_id = 'session_'; // work it
                }else{
                    sess_id = 'session__'; // not work
                }
                $('#'+sess_id+'<?php echo $count; ?>').datepicker({
                    dateFormat:'dd-mm-yy', minDate: 0,
                    <?php if(( $controller_name == 'dv-delivery') && ($controller_function == 'edit')){ 
                    // edit case of module  
                    // Get Start Date
                    $session_key_val = 'session'.$count;
                    $moduleid = Yii::$app->request->get('id'); 
                    $session_date = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$moduleid' AND meta_key = '$session_key_val' ")->queryOne();
                    if(!empty($session_date['meta_value'])){
                        $session_date = $session_date['meta_value'];
                    } else {
                        $session_date = '';
                    }
    
                    ?>
                    onSelect: function (date){
                    var prev_date = '<?php echo $session_date; ?>';                
                
                    if(prev_date != date){ // condition to check the same date                        
                       $('.trainer_cordi_notify input[type=checkbox]').replaceWith('<input name="trainer_cordi_notify" value="1" type="checkbox" checked="checked">');
                       $('.trainer_c_notify input[type=checkbox]').replaceWith('<input name="trainer_c_notify" value="1" type="checkbox" checked="checked">');

                            if($('#actual_sessions_').val() >= <?php echo $count; ?>){    
                                $('#reschedule_count_block').show();//21 May 2019
                                $('.text_area_data').attr('required','required');//22 may 2019
                                $('#sessions_data_updates_count').val(parseInt($('#sessions_data_updates_count').val())+parseInt(1));
                            }
                        //}

                        $.ajax({ url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-delivery/check_date') ?>',
                                type: 'POST',
                                data: { current_date: date, module_id:module_id},
                                success: function(val){
                                    if(val == '0'){ 
                                        alert('The Date '+date+' is already Used');
                                        $('#session_<?php echo $count; ?>').focus();
                                        $('#update_module').addClass('hide');
                                    } else {  
                                        $('#reschedule_num').val('1');
                                        $('#update_module').removeClass('hide');
                                        var total_session = $('#all_sessions').val();
                                        var last_date = $('#session_'+total_session).val();
                                        $("#fend_date").replaceWith('<input name="fend_date" id="fend_date" value="'+last_date+'" type="hidden">');
                                        $("#module_end_date").replaceWith('<input id="module_end_date" class="form-control hasDatepicker" title="" placeholder="End Date" data-toggle="tooltip" readonly="readonly" data-placement="top" aria-required="true" data-original-title="End Date" aria-invalid="false" value="'+last_date+'">');
                                        $('#set_end_date_hidden').val($("#module_end_date").val());
                                    }   
                                    $("#loading_custom").hide();                                 
                                }
                            });
                        }else{
                            if($('#actual_sessions_').val() >= <?php echo $count; ?>){    
                                 
                                $('#reschedule_count_block').hide();//21 May 2019
                                $('.text_area_data').replaceWith("<textarea class='form-control text_area_data' rows='5' name='reschedule_text'></textarea>");
                                $('#sessions_data_updates_count').val(parseInt($('#sessions_data_updates_count').val())-parseInt(1));
                            
                            }
                        }   // condition to check the same date         
                    } // on select
                    <?php } else { // create case of module ?>
                        onSelect: function (date){
                            var total_session = $('#all_sessions').val();
                            var last_date = $('#session_'+total_session).val();
                            $("#fend_date").replaceWith('<input name="fend_date" id="fend_date" value="'+last_date+'" type="hidden">');
                            $("#module_end_date").replaceWith('<input id="module_end_date" class="form-control hasDatepicker" title="" placeholder="End Date" data-toggle="tooltip" readonly="readonly" data-placement="top" aria-required="true" data-original-title="End Date" aria-invalid="false" value="'+last_date+'">');
                            $('#set_end_date_hidden').val($("#module_end_date").val());
                        }
                <?php  } ?>
                }).focus();
            });
            <?php $count++;
        }

        $count = 2; for($i = 1; $i<=5; $i++){ // create date picker from 2 to 6 on dv-registration/create
            ?>$(document).on('click', '#insdate_<?php echo $count; ?>', function(){
                var date_cnt = "<?= $count ?>";
                if(date_cnt > 2){
                    date_cnt = date_cnt - 1;
                    var date_vals = $('#insdate_'+date_cnt).datepicker('getDate', '+1d');
                    date_vals.setDate(date_vals.getDate()+1); 
                    $('#insdate_<?php echo $count; ?>').datepicker({dateFormat:'dd-mm-yy',  minDate: date_vals}).focus();
                }else{
                    $('#insdate_<?php echo $count; ?>').datepicker({dateFormat:'dd-mm-yy',  minDate: 1}).focus();
                }
                //$('#insdate_<?php echo $count; ?>').datepicker({dateFormat:'dd-mm-yy'}).focus();
            });
            <?php
        $count++;
        } ?>

        $('#end-date').datepicker();
        $('#date_2').datepicker({ dateFormat: 'dd-mm-yy' });
        $('#date_3').datepicker({ dateFormat: 'dd-mm-yy' });
        $('.datepicker_dob').datepicker({ dateFormat: 'dd-mm-yy' });
        $('.datepicker_gst').datepicker({ dateFormat: 'yy-mm-dd',minDate:'0d' });
        $('.datepicker_jd').datepicker({ dateFormat: 'dd-mm-yy' });

        $('#fdate').datepicker({
            dateFormat: 'dd-mm-yy',
            minDate:'0d',
            beforeShowDay: $.datepicker.noWeekends,           
        });

       $('#edate').datepicker({
            dateFormat: 'dd-mm-yy',
            minDate:'0d',
            beforeShowDay: $.datepicker.noWeekends,
        });
       
        $('#edate').on('change', function(){
            var fdate = $('#fdate').val();
            var edate = $('#edate').val();
                if(edate < fdate){
                    alert('To Date Cannot Be Less Than From Date');
                    edate.val('');
                }
        });

        var closures_counter = 1;

        $ ("#monthly_incentive_rate #add_closures_range").click(function() {
            $("#remove_closures_button").show();

            var last_max_closures = parseInt($("#max_closures_"+(parseInt(closures_counter)-1)).val()) + 1;
            var last_min_closures = $("#min_closures_"+(parseInt(closures_counter)-1)).val();

            for (var i=0; i<=closures_counter; i++) {
                var min_closures = $("#min_closures_"+i).val();
                var max_closures = $("#max_closures_"+i).val();
                if (max_closures == "" || max_closures == "undefined" || min_closures == "" || min_closures == "undefined") {
                    $("#max_closures_"+i).val('');
                    alert("Max Closures must be greather than Min Closures.");
                    return false;
                }

                if (parseInt($("#min_closures_"+i).val()) >= parseInt($("#max_closures_"+i).val())) {
                    $("#max_closures_"+i).val('');
                    alert("Max Closures must be greather than Min Closures.");
                    return false;
                }
            }

            var newTextBoxDiv = $('<div id="TextBoxDiv'+closures_counter+'" style="float:left;width:100%;">');

            newTextBoxDiv.after().html('<div class="form-group col-md-4">\
            <input id="min_closures_'+ closures_counter +'" class="form-control" name="DvManageMonthlyIncentiveRate[min_closures][]" required="required"  type="number" placeholder="Min Closures" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Min Closures" readonly value="' + last_max_closures + '">\
        </div>\
        <div class="form-group col-md-4">\
            <input autocomplete="off" id="max_closures_'+ closures_counter +'"  class="form-control max_closures_val" name="DvManageMonthlyIncentiveRate[max_closures][]" required="required"  type="number" placeholder="Max Closures" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Max Closures">\
        </div>\
        <div class="form-group col-md-4">\
            <input id="rate'+ closures_counter +'"  class="form-control pdc_ref_number" name="DvManageMonthlyIncentiveRate[rate][]" required="required" type="number" placeholder="Rate (%)" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Rate (%)">\
        </div>');

            newTextBoxDiv.appendTo("#closures_rate_box");

            if (closures_counter > 1) {
                $("#remove_closures_button").show();
            }
            closures_counter++;
        });

        $(document).on("blur", "#monthly_incentive_rate .max_closures_val", function(){
            var max_closures = $(this).val();
            var max_closures_tx_bx_id = $(this).attr("id");
            var current_mx_rev_tx_bx_id = max_closures_tx_bx_id.split("max_closures_")[1];
            var last_min_closures = $("#min_closures_"+(parseInt(current_mx_rev_tx_bx_id))).val();
            $("#min_closures_"+(parseInt(current_mx_rev_tx_bx_id)+1)).val(max_closures);
            
            if (max_closures == "" || max_closures == "undefined" || last_min_closures == "" || last_min_closures == "undefined") {
                $(this).val('');
                alert("Max Closures must be greather than Min Closures.");
                return false;
            }
            
            for (var i=0; i<=closures_counter; i++) {
                if (parseInt($("#min_closures_"+i).val()) >= parseInt($("#max_closures_"+i).val())) {
                    $("#max_closures_"+i).val('');
                    alert("Max Closures must be greather than Min Closuress.");
                    return false;
                }
            }
        });

        $("#monthly_incentive_rate #remove_closures_button").click(function(){
            if(closures_counter>1){
                closures_counter--;
                $("#TextBoxDiv" + closures_counter).remove();
            }
            if(closures_counter==1){
                $('#remove_closures_button').hide();
            }
        });
        /* * * * * * * * * * * * * * * * * * * * */
        $ ("#full_payment_incentive_rate #add_closures_range").click(function() {
            $("#remove_closures_button").show();

            var last_max_closures = parseInt($("#max_closures_"+(parseInt(closures_counter)-1)).val()) + 1;
            var last_min_closures = $("#min_closures_"+(parseInt(closures_counter)-1)).val();

            for (var i=0; i<=closures_counter; i++) {
                var min_closures = $("#min_closures_"+i).val();
                var max_closures = $("#max_closures_"+i).val();
                if (max_closures == "" || max_closures == "undefined" || min_closures == "" || min_closures == "undefined") {
                    $("#max_closures_"+i).val('');
                    alert("Max Closures must be greather than Min Closures.");
                    return false;
                }

                if (parseInt($("#min_closures_"+i).val()) >= parseInt($("#max_closures_"+i).val())) {
                    $("#max_closures_"+i).val('');
                    alert("Max Closures must be greather than Min Closures.");
                    return false;
                }
            }

            var newTextBoxDiv = $('<div id="TextBoxDiv'+closures_counter+'" style="float:left;width:100%;">');

            newTextBoxDiv.after().html('<div class="form-group col-md-4">\
            <input id="min_closures_'+ closures_counter +'" class="form-control" name="DvManageFullPaymentIncentiveRate[min_closures][]" required="required"  type="number" placeholder="Min Closures" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Min Closures" readonly value="' + last_max_closures + '">\
        </div>\
        <div class="form-group col-md-4">\
            <input autocomplete="off" id="max_closures_'+ closures_counter +'"  class="form-control max_closures_val" name="DvManageFullPaymentIncentiveRate[max_closures][]" required="required"  type="number" placeholder="Max Closures" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Max Closures">\
        </div>\
        <div class="form-group col-md-4">\
            <input id="rate'+ closures_counter +'"  class="form-control pdc_ref_number" name="DvManageFullPaymentIncentiveRate[rate][]" required="required" type="number" placeholder="Rate (%)" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Rate (%)">\
        </div>');

            newTextBoxDiv.appendTo("#closures_rate_box");

            if (closures_counter > 1) {
                $("#remove_closures_button").show();
            }
            closures_counter++;
        });

        $(document).on("blur", "#full_payment_incentive_rate .max_closures_val", function(){
            var max_closures = $(this).val();
            var max_closures_tx_bx_id = $(this).attr("id");
            var current_mx_rev_tx_bx_id = max_closures_tx_bx_id.split("max_closures_")[1];
            var last_min_closures = $("#min_closures_"+(parseInt(current_mx_rev_tx_bx_id))).val();
            $("#min_closures_"+(parseInt(current_mx_rev_tx_bx_id)+1)).val(max_closures);
            
            if (max_closures == "" || max_closures == "undefined" || last_min_closures == "" || last_min_closures == "undefined") {
                $(this).val('');
                alert("Max Closures must be greather than Min Closures.");
                return false;
            }
            
            for (var i=0; i<=closures_counter; i++) {
                if (parseInt($("#min_closures_"+i).val()) >= parseInt($("#max_closures_"+i).val())) {
                    $("#max_closures_"+i).val('');
                    alert("Max Closures must be greather than Min Closuress.");
                    return false;
                }
            }
        });

        $("#full_payment_incentive_rate #remove_closures_button").click(function(){
            if(closures_counter>1){
                closures_counter--;
                $("#TextBoxDiv" + closures_counter).remove();
            }
            if(closures_counter==1){
                $('#remove_closures_button').hide();
            }
        });
        /* * * * * * * * * * * * * * * * * * * * */
        $(document).on("click","#team_member_exception_rate #add_closures_range", function() {
            $("#remove_closures_button").show();

            var last_max_closures = parseInt($("#max_closures_"+(parseInt(exception_closures_counter)-1)).val()) + 1;
            var last_min_closures = $("#min_closures_"+(parseInt(exception_closures_counter)-1)).val();

            for (var i=0; i<=exception_closures_counter; i++) {
                var min_closures = $("#min_closures_"+i).val();
                var max_closures = $("#max_closures_"+i).val();
                if (max_closures == "" || max_closures == "undefined" || min_closures == "" || min_closures == "undefined") {
                    $("#max_closures_"+i).val('');
                    alert("Max Closures must be greather than Min Closures.");
                    return false;
                }

                if (parseInt($("#min_closures_"+i).val()) >= parseInt($("#max_closures_"+i).val())) {
                    $("#max_closures_"+i).val('');
                    alert("Max Closures must be greather than Min Closures.");
                    return false;
                }
            }

            var newTextBoxDiv = $('<div id="TextBoxDiv'+exception_closures_counter+'" style="float:left;width:100%;">');

            newTextBoxDiv.after().html('<div class="form-group col-md-4">\
            <input id="min_closures_'+ exception_closures_counter +'" class="form-control" name="DvManageMonthlyIncentiveExceptionRate[min_closures][]" required="required"  type="number" placeholder="Min Closures" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Min Closures" readonly value="' + last_max_closures + '">\
        </div>\
        <div class="form-group col-md-4">\
            <input autocomplete="off" id="max_closures_'+ exception_closures_counter +'"  class="form-control max_closures_val" name="DvManageMonthlyIncentiveExceptionRate[max_closures][]" required="required"  type="number" placeholder="Max Closures" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Max Closures">\
        </div>\
        <div class="form-group col-md-4">\
            <input id="rate'+ exception_closures_counter +'"  class="form-control pdc_ref_number" name="DvManageMonthlyIncentiveExceptionRate[rate][]" required="required" type="number" placeholder="Rate (%)" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Rate (%)">\
        </div>');

            newTextBoxDiv.appendTo("#min_max_rate_container");

            if (exception_closures_counter > 1) {
                $("#remove_closures_button").show();
            }
            exception_closures_counter++;
        });

        $(document).on("blur", "#team_member_exception_rate .max_closures_val", function(){
            var max_closures = $(this).val();
            var max_closures_tx_bx_id = $(this).attr("id");
            var current_mx_rev_tx_bx_id = max_closures_tx_bx_id.split("max_closures_")[1];
            var last_min_closures = $("#min_closures_"+(parseInt(current_mx_rev_tx_bx_id))).val();
            $("#min_closures_"+(parseInt(current_mx_rev_tx_bx_id)+1)).val(max_closures);
            
            if (max_closures == "" || max_closures == "undefined" || last_min_closures == "" || last_min_closures == "undefined") {
                $(this).val('');
                alert("Max Closures must be greather than Min Closures.");
                return false;
            }
            
            for (var i=0; i<=exception_closures_counter; i++) {
                if (parseInt($("#min_closures_"+i).val()) >= parseInt($("#max_closures_"+i).val())) {
                    $("#max_closures_"+i).val('');
                    alert("Max Closures must be greather than Min Closuress.");
                    return false;
                }
            }
        });

        $(document).on("click", "#team_member_exception_rate #remove_closures_button", function(){
            if(exception_closures_counter>1){
                exception_closures_counter--;
                $("#TextBoxDiv" + exception_closures_counter).remove();
            }
            if(exception_closures_counter==1){
                $('#remove_closures_button').hide();
            }
        });

        var trs = $("tr");
        trs.each(function(){
            /* 
             * Append text " - Exception Rate" after DM & DA if any month contain Exception of incentive for any executive
             *   */
            $("#"+$(this).attr('is_exception')).html(" - Exception Rate");
        });

        $(document).on("click", "#check_current_exception_rate", function() {
            var months = $("#dvmanagemonthlyincentiveexceptionrate-month").val();
            var domain = $("#dvmanagemonthlyincentiveexceptionrate-domain").val();
            var years = $("#dvmanagemonthlyincentiveexceptionrate-years").val();
            $("#DvManageMonthlyIncentiveExceptionRate-year_to_save").val(years);
            var executive_id = $("#dvmanagemonthlyincentiveexceptionrate-executive_id").val();
            var all_months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];


            $("#min_max_rate_container").html("");

            if (months != '' && domain != '' && executive_id != '') {
                $.ajax({
                    type : 'POST',
                    url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-finance/check_current_exception_rate') ?>',
                    data : {years:years, months:months, domain:domain, executive_id:executive_id},
                    success : function(data) {
                        if (data != '') {
                            var exception_rates = JSON.parse(data);
                            if (exception_rates.length == 0) {
                                set_add_exception_rate_btn(0);
                                $.each(months, function(key, val){
                                    var newTextBoxDiv = '<div id="TextBoxDiv'+val+'">';
                                    newTextBoxDiv += '<button class="btn btn-primary month_label" type="button" >'+all_months[val-1]+'</button>';
                                    newTextBoxDiv += '<div class="col-md-12 no_rate_defined">No Rate Defined!</div>';
                                    newTextBoxDiv += '<div class="col-sm-12"><div class="col-sm-2 pull-right text-right"><button type="button" class="btn btn-info add_closures_range" closure_row='+val+'><i class="fa fa-plus"></i></button> <button type="button" class="btn btn-danger remove_closures_button"  closure_row='+val+'><i class="fa fa-times"></i></button></div></div>';

                                    newTextBoxDiv += "<div class='col-md-12'><hr class='gray_hr'/></div>";

                                    $("#min_max_rate_container").append(newTextBoxDiv);
                                });
                            } else {
                                $.each(exception_rates, function(key, val) {
                                    var single_month_exception = val['exceptions'];

                                    var newTextBoxDiv = '<div id="TextBoxDiv'+key+'">';
                                    newTextBoxDiv += '<button type="button" class="btn btn-primary month_label">'+all_months[val['month_number']-1]+'</button>';
                                    if (val['is_exception'] == 1) {
                                        newTextBoxDiv += ' <span class="badge badge-info">Exception Rate</span>';
                                    } else {
                                        newTextBoxDiv += ' <span class="badge badge-info">Default Incentive Rate</span>';
                                    }
                                    

                                    if (single_month_exception == 'undefined' || single_month_exception == null) {
                                        newTextBoxDiv += '<div class="col-md-12 no_rate_defined">No Rate Defined!</div>';
                                    } else {
                                        $.each(single_month_exception, function(month_exception_key, month_exception_val){

                                            newTextBoxDiv += '<div class="closures_rate">';
                                            newTextBoxDiv += '<div class="form-group col-md-4">\
        <input id="min_closures_'+month_exception_key+'" name="DvManageMonthlyIncentiveExceptionRate[min_closures]['+key+'][]" type="number" class="form-control" required="required" value="'+ month_exception_val.min_closures +'" readonly>\
                </div>';
                                            newTextBoxDiv += '<div class="form-group col-md-4">\
        <input id="max_closures_'+month_exception_key+'" name="DvManageMonthlyIncentiveExceptionRate[max_closures]['+key+'][]" type="number" class="form-control exec_max_closures_val" required="required" value="'+ month_exception_val.max_closures +'">\
                </div>';
                                            newTextBoxDiv += '<div class="form-group col-md-4">\
        <input id="rate_'+month_exception_key+'" name="DvManageMonthlyIncentiveExceptionRate[rate]['+key+'][]" type="number" class="form-control" required="required" value="'+ month_exception_val.rate +'">\
                </div>';
                                            newTextBoxDiv += '</div>';
                                       });
                                    }
                                    newTextBoxDiv += '<div class="col-sm-12"><div class="col-sm-2 pull-right text-right"><button type="button" class="btn btn-info add_closures_range" closure_row='+key+'><i class="fa fa-plus"></i></button> <button type="button" class="btn btn-danger remove_closures_button"  closure_row='+key+'><i class="fa fa-times"></i></button></div></div>';

                                    newTextBoxDiv += "<div class='col-md-12'><hr class='gray_hr'/></div>";

                                    set_add_exception_rate_btn(1);
                                    $("#min_max_rate_container").append(newTextBoxDiv);
                                });
                            }
                        }
                    }
                });
            }
        });

        $(document).on("click", ".add_closures_range", function(){
            var closure_row = $(this).attr("closure_row");
            var total_closures_in_month = $("#TextBoxDiv"+closure_row + " .closures_rate").length;
            var max_closure_val = parseInt($("#TextBoxDiv"+closure_row + " #max_closures_" + (total_closures_in_month-1)).val()) + 1;
            var newTextBoxDiv = '<div class="closures_rate">';

            if ($("#TextBoxDiv"+closure_row + " .closures_rate").last().length <= 0) {
                max_closure_val = 0;
            }

            for (var i=0; i<=total_closures_in_month; i++) {
                var min_closures = $("#TextBoxDiv"+closure_row + " #min_closures_"+i).val();
                var max_closures = $("#TextBoxDiv"+closure_row + " #max_closures_"+i).val();
                if (max_closures == "" || max_closures == "undefined" || min_closures == "" || min_closures == "undefined") {
                    $("#TextBoxDiv"+closure_row + " #max_closures_"+i).val('');
                    alert("Max Closures must be greather than Min Closures.");
                    return false;
                }

                if (parseInt($("#TextBoxDiv"+closure_row + " #min_closures_"+i).val()) >= parseInt($("#TextBoxDiv"+closure_row + " #max_closures_"+i).val())) {
                    $("#TextBoxDiv"+closure_row + " #max_closures_"+i).val('');
                    alert("2Max Closures must be greather than Min Closures.");
                    return false;
                }
            }

            newTextBoxDiv += '<div class="form-group col-md-4">\
        <input id="min_closures_'+total_closures_in_month+'" name="DvManageMonthlyIncentiveExceptionRate[min_closures]['+closure_row+'][]" type="number" class="form-control" required="required" value="'+ max_closure_val +'" readonly>\
                </div>';
            newTextBoxDiv += '<div class="form-group col-md-4">\
        <input id="max_closures_'+total_closures_in_month+'" name="DvManageMonthlyIncentiveExceptionRate[max_closures]['+closure_row+'][]" type="number" class="form-control exec_max_closures_val" required="required" value="">\
                </div>';
            newTextBoxDiv += '<div class="form-group col-md-4">\
        <input id="rate_'+total_closures_in_month+'" name="DvManageMonthlyIncentiveExceptionRate[rate]['+closure_row+'][]" type="number" class="form-control" required="required" value="">\
                </div>';
            newTextBoxDiv += '</div>';

            if ($("#TextBoxDiv"+closure_row + " .closures_rate").last().length <= 0) {
                $("#TextBoxDiv"+closure_row + " .month_label").after(newTextBoxDiv);
                $("#TextBoxDiv"+closure_row + " .no_rate_defined").remove();
            } else {
                $("#TextBoxDiv"+closure_row + " .closures_rate").last().after(newTextBoxDiv);
            }
            set_add_exception_rate_btn(1);
        });

        $(document).on("click", ".remove_closures_button", function(){
            var closure_row = $(this).attr("closure_row");
            $("#TextBoxDiv"+closure_row + " .closures_rate").last().remove();
            if ($(".closures_rate").length <= 0) {
                set_add_exception_rate_btn(0);
            }
        });
        
        $(document).on("blur", ".closures_rate .exec_max_closures_val", function(){
            var max_closures = $(this).val();
            var max_closures_tx_bx_id = $(this).attr("id");
            var closure_row = $(this).parent().parent().parent().find('.add_closures_range').attr('closure_row');
            //console.log(closure_row);
            var total_closures_in_month = $("#TextBoxDiv"+closure_row + " .closures_rate").length;
            //console.log("______-");
            //console.log(total_closures_in_month);
            var current_mx_rev_tx_bx_id = max_closures_tx_bx_id.split("max_closures_")[1];
            var last_min_closures = $("#min_closures_"+(parseInt(current_mx_rev_tx_bx_id))).val();
            //console.log("============");
            //console.log(last_min_closures);
            //console.log("current_mx_rev_tx_bx_id : " + current_mx_rev_tx_bx_id);

            for (var i=0; i<=total_closures_in_month; i++) {
                var min_closures = $("#TextBoxDiv"+closure_row + " #min_closures_"+i).val();
                var max_closures = $("#TextBoxDiv"+closure_row + " #max_closures_"+i).val();
                for (var j=current_mx_rev_tx_bx_id+1; j<=total_closures_in_month; j++) {
                    //$("#TextBoxDiv"+closure_row + " #min_closures_"+j).parent().parent().remove();
                    //$("#TextBoxDiv"+closure_row + " #min_closures_"+j).remove();
                    //$("#TextBoxDiv"+closure_row + " #max_closures_"+j).remove();
                    //$("#TextBoxDiv"+closure_row + " #rate_"+j).remove();
                }
                if (max_closures == "" || max_closures == "undefined" || min_closures == "" || min_closures == "undefined") {
                    $("#TextBoxDiv"+closure_row + " #max_closures_"+i).val('');
                    alert("Max Closures must be greather than Min Closures.");
                    return false;
                }

                if (parseInt($("#TextBoxDiv"+closure_row + " #min_closures_"+i).val()) >= parseInt($("#TextBoxDiv"+closure_row + " #max_closures_"+i).val())) {
                    $("#TextBoxDiv"+closure_row + " #max_closures_"+i).val('');
                    alert("2Max Closures must be greather than Min Closures.");
                    return false;
                }
            }
        });

        $(document).on("change", "#select_period", function() {
            var period = $(this).val();
            $(".period_inputs").hide();
            $("."+period).show();
        });

        $(".total_current_period").each(function(){
            $("#sold_course_td").html((parseFloat($("#sold_course_td").html()) + parseFloat($.trim($(this).text()))).toFixed(2));
        });

        $(".per_person_average").each(function(){
            $("#per_person_average_td").html((parseFloat($("#per_person_average_td").html()) + parseFloat($.trim($(this).text()))).toFixed(2));
        });

        $(".your_average").each(function(){
            $("#your_average_td").html((parseFloat($("#your_average_td").html()) + parseFloat($.trim($(this).text()))).toFixed(2));
        });

        $(".company_average").each(function(){
            $("#company_average_td").html((parseFloat($("#company_average_td").html()) + parseFloat($.trim($(this).text()))).toFixed(2));
        });
    });

    // Rescheduleing
    /*$(document).on('click', '#reschedule', function(){
        var current_id = $(this).attr("data-current");
        if(current_id != ''){
            var module_id = $('#edit_module_form').val();
            var total_session = $('#total_session').attr("data-total-session");
            var event_date = $('#session_'+current_id).val();
            $(document).ajaxStart(function(){
                $("#loading_custom").show();
            });
            $.ajax({
                url: '<?php //echo \Yii::$app->getUrlManager()->createUrl('dv-delivery/rescheduleing') ?>',
                type: 'POST',
                data: { current_id:current_id, module_id:module_id, total_session:total_session, event_date:event_date},
                success: function(data){
                    $('#TextBoxDiv'+current_id).nextAll('div').remove();
                    $('#TextBoxDiv'+current_id).after().html(data);
                    $("#loading_custom").hide();
                }
            });
        }
    });*/

    $("#dvparticipantpayments-payment_currency option[value!='']").css("display",'none');
    // on change country
    $("#country").change(function(){
        $("#dvparticipantpayments-payment_currency").val('');
        if(this.value != ''){
            $("#loading_custom").show();
            var country_id = $(this).val();
            var environment = "<?= Yii::$app->params['environment']; ?>";
            if(environment == 'Production'){
                // for live
                if(country_id == '101'){
                    $("#dvparticipantpayments-payment_currency option[value!='9']").css("display",'none');
                    $("#dvparticipantpayments-payment_currency option[value='9']").css("display",'block');
                }else{
                    $("#dvparticipantpayments-payment_currency option[value!='9']").css("display",'block');
                    $("#dvparticipantpayments-payment_currency option[value='9']").css("display",'none');
                }

            } else {
                // for dev
                if(country_id == '101'){
                    $("#dvparticipantpayments-payment_currency option[value!='9']").css("display",'none');
                    $("#dvparticipantpayments-payment_currency option[value='9']").css("display",'block');
                }else{
                    $("#dvparticipantpayments-payment_currency option[value!='9']").css("display",'block');
                    $("#dvparticipantpayments-payment_currency option[value='9']").css("display",'none');
                }

            }
            
        

            var att_name = $(this).attr('name');
            if(att_name == 'DvRegistration[country]'){
                var city_dummy = '<select id="dvregistration-city" class="form-control" name="DvRegistration[city]" required="required"><option value="">Select City</option></select>';
            } else {
                var city_dummy = '<select id="dvusers-city" class="form-control" name="DvUsers[city]" required="required"><option value="">Select City</option></select>';
            }
            
            $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-users/get_states') ?>',
                type: 'POST',
                data: { country_id: country_id,att_name:att_name},
                success: function(data){
                    $("#dvusers-state").replaceWith(data);
                    $("#dvregistration-state").replaceWith(data);
                    $("#dvusers-city").replaceWith(city_dummy);
                    $("#loading_custom").hide();
                }
            });
        }else{
            $("#dvparticipantpayments-payment_currency option[value!='']").css("display",'none');
            $("#dvparticipantpayments-payment_currency option[value='']").css("display",'block');
        }
    });

    // on change State
    $('body').on('change', '#dvusers-state', function (){
        var state_id = $(this).val();
        var att_name = $(this).attr('name');  
        if(state_id != ''){
            $("#loading_custom").show();
            $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-users/get_cities') ?>',
                type: 'POST',
                data: { state_id: state_id,att_name:att_name},
                success: function(data){
                    $("#dvusers-city").replaceWith(data);
                    $("#dvregistration-city").replaceWith(data);
                    $("#loading_custom").hide();
                }
            });
        }
    });

   $('#dvregistration-is_full_payment input[type=radio]').change(function(){
        if (this.value == '0'){
            $(".installment_boxe").show();
            if($("#dvregistration-is_pdc").prop('checked')){
                $(".pdc_ref_form_field").css('display','block');
            }else{
                $(".pdc_ref_form_field").css('display','none');
            }
            $("#dvregistration-modules_allowed").val("");
        } else if(this.value == '1'){
            $(".installment_boxe").hide();
            var course_val = $("#course").val();
            
            if(course_val == 1){
                $("#dvregistration-modules_allowed").val("6");
            }else if(course_val == 2){
                $("#dvregistration-modules_allowed").val("5");
            }

        }
    });

      $('#department').change(function(){
        if((this.value == '1') || (this.value == '2') || (this.value == '7')){    
            $('#usercourse').removeClass('hide');
        } else {
            $('#usercourse').addClass('hide');
        }

        var dep_id = $(this).val();
        $("#loading_custom").show();

        $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-users/get_course') ?>',
                type: 'POST',
                data: { dep_id: dep_id},
                success: function(data){
                    $('#usercourse').replaceWith(data);
                    if(dep_id == '7'){
                        CKEDITOR.replace( 'usermeta[description]' );
                    }
                 //   $.ajax({
                 //       url: '<?php //echo \Yii::$app->getUrlManager()->createUrl('dv-users/user_team') ?>',
                 //       type: 'POST',
                 //       data: { dep_id: dep_id},
                 //       success: function(data){
                 //           $('#dvusers-team').replaceWith(data);
                            //$("#loading_custom").hide();
                            $.ajax({
                                url:'<?php echo \Yii::$app->getUrlManager()->createUrl('dv-users/user_role') ?>',
                                type: 'POST',
                                data: { dep_id: dep_id},
                                success: function(data){
                                    $('#UsersRole').replaceWith(data);
                                    $("#loading_custom").hide();
                                }
                            });
                    //    }
                  //  });
                }
            });
   }); 

    // select course on dv-registration page
    /*$(".dv-participant-create #course").change(function(){
        var dv_email = $("#dvregistration-email").val();
        if(dv_email == ''){
            swal("Warning!", "Please Enter Participant Email", "warning")
            .then((value) => {
                 $("#loading_custom").hide();
                 return false;
            });
        }
       

        if(this.value == 1 || this.value == 2 || this.value == 13 || this.value == 14){
            //  if(this.value == 'CDMM' || this.value == 'CPDM' || this.value == 'DAP' || this.value == 'DAR'){
            if($("#opt_for_3_months").prop('checked')){
                $(".field-dvregistration-available_batch_opt").css('display','block');
            }else{
                $(".field-dvregistration-available_batch_opt").css('display','none');
            }
        }else{
            $(".field-dvregistration-available_batch_opt").css('display','none');
        }

        if(this.value != ''){
        $("#loading_custom").show();
        if ((this.value == 1) || (this.value == 2)){
            $(".all_check").show();
            $(".Tableau_CWAW").css('display','none');
            $(".cmam_cfmm_tj").css('display','block');
        } else if((this.value == 13) || (this.value == 14)){
            $(".all_check").show();
            $(".cmam_cfmm_tj").css('display','none');
            $(".Tableau_CWAW").css('display','block');
        }else {
            $(".all_check").hide();
        }
        var course_id = $(this).val();
        var dummp_batch = '<select id="dvregistration-course_batch" class="form-control" name="DvRegistration[course_batch]" required="required"><option value="">Select Batch</option></select>';

        

        var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

        if(regex.test(dv_email)) {

            $.ajax({
                url: '<?php //echo \Yii::$app->getUrlManager()->createUrl('dv-registration/check_email') ?>',
                type: 'POST',
                data: { email: dv_email , course: course_id},
                success: function(data){
                    console.log("456");
                    // alert(data);
                    if(data == '1'){
                        $("#course").removeClass('error');
                        $(".field-course  span.error-summary-msg").remove();
                        $("#course").addClass('error');
                        $(".field-course ").append("<span class='error-summary-msg'>Email already exist with this course.</span>");
                    }else{
                        $("#course").removeClass('error');
                        $(".field-course  span.error-summary-msg").remove();
                    }            
                }
            });

            $.ajax({
                url: '<?php //echo \Yii::$app->getUrlManager()->createUrl('dv-registration/get_format') ?>',
                type: 'POST',
                data: { course_id: course_id},
                success: function(data){
                   $("#dvregistration-course_format").replaceWith(data);
                   $.ajax({
                        url: '<?php //echo \Yii::$app->getUrlManager()->createUrl('dv-registration/modules_allowed') ?>',
                        type: 'POST',
                        data: { course_id: course_id},
                        success: function(data){
                            $("#dvregistration-modules_allowed").replaceWith(data);
                            $("#dvregistration-course_batch").replaceWith(dummp_batch)
                            $("#loading_custom").hide();
                        }
                    });
                }
            });
        }

       }else{
            $("#course").removeClass('error');
            $("#course").addClass('error');
            $(".field-course  span.error-summary-msg").remove();
       }
       $("#dvregistration-modules_allowed").val("");
    });*/

    //New added on 04 June 2019
    $(".dv-participant-create #course").change(function(){
        var dv_email = $("#dvregistration-email").val();
        if(dv_email == ''){
            swal("Warning!", "Please Enter Participant Email", "warning")
            .then((value) => {
                 $("#loading_custom").hide();
                 return false;
            });
        }
       

        if(this.value == 1 || this.value == 2 || this.value == 13 || this.value == 14){
            //  if(this.value == 'CDMM' || this.value == 'CPDM' || this.value == 'DAP' || this.value == 'DAR'){
            if($("#opt_for_3_months").prop('checked')){
                $(".field-dvregistration-available_batch_opt").css('display','block');
            }else{
                $(".field-dvregistration-available_batch_opt").css('display','none');
            }
        }else{
            $(".field-dvregistration-available_batch_opt").css('display','none');
        }

        if(this.value != ''){
        $("#loading_custom").show();
        if ((this.value == 1) || (this.value == 2)){
            $(".all_check").show();
            $(".Tableau_CWAW").css('display','none');
            $(".cmam_cfmm_tj").css('display','block');
        } else if((this.value == 13) || (this.value == 14)){
            $(".all_check").show();
            $(".cmam_cfmm_tj").css('display','none');
            $(".Tableau_CWAW").css('display','block');
        }else {
            $(".all_check").hide();
        }
        var course_id = $(this).val();
        var dummp_batch = '<select id="dvregistration-course_batch" class="form-control" name="DvRegistration[course_batch]" required="required"><option value="">Select Batch</option></select>';

        

        var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

        if(regex.test(dv_email)) {

            $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-cdo-registration/check_email') ?>',
                type: 'POST',
                data: { email: dv_email , course: course_id},
                success: function(data){
                    console.log("456");
                    // alert(data);
                    if(data == '1'){
                        $("#course").removeClass('error');
                        $(".field-course  span.error-summary-msg").remove();
                        $("#course").addClass('error');
                        $(".field-course ").append("<span class='error-summary-msg'>Email already exist with this course.</span>");
                    }else{
                        $("#course").removeClass('error');
                        $(".field-course  span.error-summary-msg").remove();
                    }            
                }
            });

            $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-cdo-registration/get_format') ?>',
                type: 'POST',
                data: { course_id: course_id },
                success: function(data){   
                   $("#dvregistration-course_format").replaceWith(data);
                   $.ajax({
                        url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-cdo-registration/modules_allowed') ?>',
                        type: 'POST',
                        data: { course_id: course_id},
                        success: function(data){
                            $("#dvregistration-modules_allowed").replaceWith(data);
                            $("#dvregistration-course_batch").replaceWith(dummp_batch)
                            $("#loading_custom").hide();
                        }
                    });
                }
            });
        }

       }else{
            $("#course").removeClass('error');
            $("#course").addClass('error');
            $(".field-course  span.error-summary-msg").remove();
       }
       $("#dvregistration-modules_allowed").val("");
    });
    //End of 04 June 2019

   // select course on
   $("#module_course").change(function(){   
     if(this.value != ''){
        $("#loading_custom").show();
        var course_id = $(this).val();
        
        if(course_id == 19){
            $('#dvassistbatches-view').val('0');
        } else {
            $('#dvassistbatches-view').val('1');
        }

         /*$.ajax({
            url: '<?php //echo \Yii::$app->getUrlManager()->createUrl('dv-delivery/trainingtopics') ?>',
            type: 'POST',
            data: { course_id: course_id},
            success: function(data){
                $("#dvassistbatches-training_topic").replaceWith(data);*/

                $.ajax({
                    url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-delivery/list_trainer') ?>',
                    type: 'POST',
                    data: { course_id: course_id},
                    success: function(data){
                        $("#dvassistbatches-trainer").replaceWith(data);
                            /*$.ajax({
                                url: '<?php //echo \Yii::$app->getUrlManager()->createUrl('dv-delivery/delivery_person') ?>',
                                type: 'POST',
                                data: { course_id: course_id},
                                success: function(data){
                                    $("#dvassistbatches-delivery_person").replaceWith(data);
                                    $("#loading_custom").hide();
                                }
                            });*/
                         $("#loading_custom").hide();
                    }
                });             
               
          /*  }
        });*/
      }
   });

   $('body').on('change', '#dvassistbatches-trainer', function (){ 
        if(this.value != ''){
            $("#loading_custom").show();
            var trainer_id = $(this).val();
                $.ajax({ url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-delivery/delivery_person') ?>',
                        type: 'POST',
                        data: { trainer_id: trainer_id},
                        success: function(data){
                            $("#dvassistbatches-coordinator").replaceWith(data);
                            $("#loading_custom").hide();
                        }
                    });
            }
        });

    // on change select course format
   /* $('body').on('change', '#dvregistration-course_format', function (){
        $("#loading_custom").show();
        var format_id = $(this).val();
        var course_id = $("#course").val();
         $.ajax({
            url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-registration/get_batch') ?>',
            type: 'POST',
            data: { course_id: course_id, format_id: format_id},
            success: function(data){
               $("#dvregistration-course_batch").replaceWith(data);
               $("#loading_custom").hide();
            }
        });
   });*/

    // on change select DV Users course format
    $('body').on('change', '#user_course', function (){  
        var cid = $(this).val();  
        $("#loading_custom").show();
        $.ajax({
            url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-users/course_coordinator') ?>',
            type: 'POST',
            data: { cid: cid},
            success: function(data){
               //alert(data);
               $("#dvusers-coordinator").replaceWith(data);
               $("#loading_custom").hide();
            }
        });
    });

    // submit form by Modal on create_module
   $('#submit').click(function(){
        $('#confirm-submit .modal-body .session_error').replaceWith('<div class="session_error"></div>');
        //$('#DvAssistBatches').removeClass('dv_module');
        //$('#DvAssistBatches').submit();
        $("#loading_custom").show();
        $.ajax({
            url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-delivery/batch_validate') ?>',
            type: 'POST',
            data: $("#DvAssistBatches").serialize(),
            success: function(data){
                if(data == '1'){
                    $('#confirm-submit .modal-body .session_error').replaceWith('<div class="session_error"></div>');
                    $('#DvAssistBatches').removeClass('dv_module');
                    $('#DvAssistBatches').submit();
                } else {
                    $('#confirm-submit .modal-body .session_error').replaceWith(data);
                }
               $("#loading_custom").hide();
            }
        });
    });

   $('#create_user').on('submit', function(){
        var password = $("#dvusers-password").val();
        var repassword = $("#cpassword").val();
        if (password != repassword){
            $('.field-dvusers-cpassword').addClass('has-error');
            return false;
        } else {
            $('.field-dvusers-cpassword').removeClass('has-error');
        }

        if($('#dvusers-gender input:checked').length<=0){
            $('.field-dvusers-gender').addClass('has-error');
            return false;
        } else {
            $('.field-dvusers-gender').removeClass('has-error');
        }
    });

   $(".dv-users-update #dvusers-password").keyup(function(){
    var password = $("#dvusers-password").val();
    var repassword = $("#cpassword").val();
      if(password == ''){
            $("#cpassword").prop('required',false);
            $('.field-dvusers-cpassword').removeClass('has-error');
            $('#update_user').prop('disabled', false);
        } else {
            $("#cpassword").prop('required',true);
                if (password != repassword){
                    $('.field-dvusers-cpassword').addClass('has-error');
                    $('#update_user').prop('disabled', true);
                } else {
                    $('.field-dvusers-cpassword').removeClass('has-error');
                    $('#update_user').prop('disabled', false);
                }
        }
    });

    $(".dv-users-update #cpassword").keyup(function(){
        var password = $("#dvusers-password").val();
        var repassword = $("#cpassword").val();
        if(repassword == ''){
            $("#dvusers-password").prop('required',false);
            $('.field-dvusers-cpassword').removeClass('has-error');
        } else {
            $("#dvusers-password").prop('required',true);
             if (password != repassword){
                $('.field-dvusers-cpassword').addClass('has-error');
                $('#update_user').prop('disabled', true);
             } else {
                $('.field-dvusers-cpassword').removeClass('has-error');
                $('#update_user').prop('disabled', false);
             }
        }
        /*
        $("#dvusers-password").prop('required',true);*/
    });

/*   $('#update_user').on('submit', function(){ alert('d5');
        var password = $("#dvusers-password").val();
        var repassword = $("#cpassword").val();
        if (password != repassword){
            $('.field-dvusers-cpassword').addClass('has-error');
            alert('d');
            return false;
        } else {
            $('.field-dvusers-cpassword').removeClass('has-error');
        }
    });*/   

   

    $("#dvusers-email").keyup(function(){
        var email = $(this).val();
        var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        if(regex.test(email)) {
            $("#user_profile_picture").css("display","none");
            $("#loading_custom").show();
           $.ajax({
            url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-users/check_email') ?>',
            type: 'POST',
            dataType: "json",
            data: { email: email},
            success: function(data){
				console.log(data);
               // CKEDITOR.instances['usermeta[description]'].setData("hi");

                $("#user_profile_picture").css("display","none");
                if(data.status == '1'){
                    $("#dvusers-email").addClass('error');
                    $('button#create_user').prop("type", "button");
                    $("#loading_custom").hide();
                } else {
                    $("#dvusers-email").removeClass('error');
                    
                    
                    if(data.result.tm_image_url == null){}else{
                        $("#user_profile_picture").css("display","block");
                        $("#user_profile_picture").attr("src",data.result.tm_image_url);
                    }
					if(data.result.tm_name){
						var ret = data.result.tm_name.split(" ");
						var fname = ret[0];
						var lname = ret[1];
						$("#dvusers-first_name").val(fname);
						$("#dvusers-last_name").val(lname);
					}
                    
                    $("#phone").val(data.result.tm_phone);
                    $("input[name='usermeta[fb_link]']").val(data.result.tm_facebook);
                    $("input[name='usermeta[linkedin_link]']").val(data.result.tm_linkedin);
                    $("input[name='usermeta[twitter_link]']").val(data.result.tm_twitter);
					
					if(data.result.user_role == 1){
						$("#department").val("7");
						$("#department").trigger("change");
						var editor = CKEDITOR.instances['usermeta[description]'];
						if (editor) {
						   setTimeout(function(){ CKEDITOR.instances['usermeta[description]'].setData(data.result.tm_description); }, 500);
						}                 
						
						setTimeout(function(){ $("input[name='usermeta[trainerid]']").val(data.result.post_id); }, 1500);
					}
					if(data.result.user_role == 2){
							$("#department").val("2");
							$("#department").trigger("change");
						setTimeout(function(){ 
							$("#UsersRole").val("5");
							$("#UsersRole").trigger("change");
						}, 1500);
						
					     
					}

                    $('button#create_user').prop("type", "submit");
                    $("#loading_custom").hide();
                    return false;
                } 
            }
        });
    }

    });

    $('#user-search-form').on('submit', function(){
        var sear = $('#dvusers-search').val();
        if(sear == ''){
            $("#dvusers-search").addClass('error');
            return false;
        } else {
            $("#dvusers-search").removeClass('error');
        }        
    });

    $('#user-filter').on('submit', function(){
        var role = $('#role').val();
        var team = $('#team').val();
        var department = $('#depart').val();
        
        if((role == '')&&(team == '')&&(department == '')){
            $("#role").addClass('error');
            $('#team').addClass('error');
            $('#depart').addClass('error');
            return false;
        } else {
            $("#role").removeClass('error');
            $('#team').removeClass('error');
            $('#depart').removeClass('error');
        }        
    });

    /*$('body').on('change', '#trainerconfirm', function (){
        var value = $(this).val();
        if(value == 1){
             $(".trainer_confirmation").removeClass('hide');
        } else if(value == 0) {
             $(".trainer_confirmation").addClass('hide');
        }
    });*/

    $('.trainer_confirm input[type="checkbox"]').click(function(){
        if($(this).prop("checked") == true){
            $(".trainer_confirmation").removeClass('hide');
        } else if($(this).prop("checked") == false){
            $(".trainer_confirmation").addClass('hide');
        }
    });

    //updated on 29 April 2019
    $('body').on('change', '#running_batch_status', function (){
        var value = $(this).val();
        if(value == 1){
             $(".pefor_info").removeClass('hide');
        } else if(value == 2 || value == 3) {
             $(".pefor_info").addClass('hide');
        }
    });

    // on change select by date or month
    $('body').on('change', '#by_date_month', function (){
        var selection_id = $(this).val();
        if(selection_id != ''){
            if(selection_id == 'd'){
                $(".select_by_date").removeClass('hide');
                $(".select_by_month").addClass('hide');
                $("select.select_by_month").val('');
            } else if(selection_id == 'm'){
                $(".select_by_month").removeClass('hide');
                $(".select_by_date").addClass('hide');
                $("select.select_by_date").val('');
            } else {
                $(".select_by_month").addClass('hide');
                $(".select_by_date").addClass('hide');
                $("select.select_by_month").val('');
                $("select.select_by_date").val('');
            }
        }

        
        /*if(state_id != ''){
            $("#loading_custom").show();
            $.ajax({
                url: '<?php //echo \Yii::$app->getUrlManager()->createUrl('dv-users/get_cities') ?>',
                type: 'POST',
                data: { state_id: state_id,att_name:att_name},
                success: function(data){
                    $("#dvusers-city").replaceWith(data);
                    $("#dvregistration-city").replaceWith(data);
                    $("#loading_custom").hide();
                }
            });
        }*/
    });


    

   $("#dvusersteam-name").autocomplete({        
        source: function( request, response){
            $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-users/team_name') ?>',
                type: 'post',
                dataType: "json",
                data: {
                    search: request.term,
                    department: $('#ccourse').val(),
                    managers: $('#managers').val(),
                },
                success: function( data ) {
                    response( data );
                }
            });
        },
        select: function (event, ui) {
            $('#dvusersteam-name').val(ui.item.label); // display the selected text
           // $('#selectuser_id').val(ui.item.value); // save selected id to input
            return false;
        }
    });        
    
    // on change Department for Team
    $('body').on('change', '#ccourse', function (){
        var depid = $(this).val();        
        if(depid != ''){
            $("#loading_custom").show();
            $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-users/users_by_dep') ?>',
                type: 'POST',
                data: { depid: depid},
                success: function(data){
                    $("#managers").replaceWith(data);                    
                    $("#loading_custom").hide();
                }
            });
        }
    });    

    // on change Department for Team
    $('body').on('change', '.dv-users-update #department', function (){
        var depid = $(this).val();        
        if(depid != ''){
            $("#loading_custom").show();
            $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-users/users_by_dep2') ?>',
                type: 'POST',
                data: { depid: depid},
                success: function(data){                    
                    $("#dvusers-team").replaceWith(data);
                    $("#loading_custom").hide();
                }
            });
        }
    });

    // on change Department for Team
    $('body').on('change', '#update_manager', function (){
        var mid = $(this).val();        
        if(mid != ''){
            $("#loading_custom").show();
            $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-users/update_manager') ?>',
                type: 'POST',
                data: { mid: mid},
                success: function(data){                    
                    $("#new_manager").replaceWith(data);
                    $("#loading_custom").hide();
                }
            });
        }
    });        

    // on change Department for Team
    $('body').on('change', '#dvmanagemonthlyincentiveexceptionrate-months', function (){
        exception_closures_counter = 0;
        var month = $(this).val();        
        var domain = $("#dvmanagemonthlyincentiveexceptionrate-domain").val();
        var executive_id = $("#dvmanagemonthlyincentiveexceptionrate-executive_id").val();
        $("#min_max_rate_container").html('');
        $("#add_remove_rate_btn").html('');
        if(month != '' && domain != ''){
            $("#loading_custom").show();
            $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-finance/get_month_wise_closures') ?>',
                type: 'POST',
                data: {month: month, domain:domain, executive_id:executive_id},
                success: function(data){
                    if (data == 0) {
                        alert("No rules added for " + month);
                    } else {
                        var closures = JSON.parse(data);
                        if (closures['current_incentive'] == "") {
                            $("#min_max_rate_container").html('<div class="col-md-12 form-group"><div class="small_custom_alert alert-warning">No rules.</div></div>');
                        } else {
                            if (closures['is_exception'] == "1") {
                                $("#min_max_rate_container").html('<div class="col-md-12 form-group"><div class="small_custom_alert alert-info">Rate with Exception.</div></div>');
                            } else {
                                $("#min_max_rate_container").html('<div class="col-md-12 form-group"><div class="small_custom_alert alert-info">Rate without Exception.</div></div>');
                            }
                            $.each(closures['current_incentive'], function(key,val){
                                var newTextBoxDiv = '<div id="TextBoxDiv'+key+'">';
                                $.each(val, function(k,v){

                                    newTextBoxDiv += '<div class="form-group col-md-4">\
        <input id="'+ k +'_'+key+'" name="DvManageMonthlyIncentiveExceptionRate['+ k +'][]" type="number" class="form-control" required="required" value="'+ v +'">\
                </div>';
                                });
                                $("#min_max_rate_container").append(newTextBoxDiv);
                                exception_closures_counter++;
                            });
                            
                            if ($("#min_max_rate_container").length <= 0) {
                            
                            } else {
                                if ($("#op_btn").length <= 0) {
                                    var add_remove_rate_btn = $('<div id="op_btn">');
                                    add_remove_rate_btn.after().html('<button type="button" id="add_closures_range" class="btn btn-info"><i class="fa fa-plus"></i></button><button type="button" id="remove_closures_button" class="btn btn-danger"><i class="fa fa-times"></i></button>');
                                    add_remove_rate_btn.appendTo("#add_remove_rate_btn");
                                }
                            }
                        }
                    }
                    $("#loading_custom").hide();
                }
            });
        }
    });
    $('body').on('change', '#dvmanagemonthlyincentiveexceptionrate-domains', function (){
        exception_closures_counter = 0;
        var domain = $(this).val();
        var month = $("#dvmanagemonthlyincentiveexceptionrate-month").val();
        var executive_id = $("#dvmanagemonthlyincentiveexceptionrate-executive_id").val();
        $("#min_max_rate_container").html('');
        $("#add_remove_rate_btn").html('');
        if(month != '' && domain != ''){
            $("#loading_custom").show();
            $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-finance/get_month_wise_closures') ?>',
                type: 'POST',
                data: {month: month, domain:domain, executive_id:executive_id},
                success: function(data){
                    if (data == 0) {
                        alert("No rules added for " + month);
                    } else {
                        var closures = JSON.parse(data);
                        if (closures['current_incentive'] == "") {
                            $("#min_max_rate_container").html('<div class="col-md-12 form-group"><div class="small_custom_alert alert-warning">No rules.</div></div>');
                        } else {
                            if (closures['is_exception'] == "1") {
                                $("#min_max_rate_container").html('<div class="col-md-12 form-group"><div class="small_custom_alert alert-info">Rate with Exception.</div></div>');
                            } else {
                                $("#min_max_rate_container").html('<div class="col-md-12 form-group"><div class="small_custom_alert alert-info">Rate without Exception.</div></div>');
                            }
                            $.each(closures['current_incentive'], function(key,val){
                                var newTextBoxDiv = '<div id="TextBoxDiv'+key+'">';
                                $.each(val, function(k,v){

                                    newTextBoxDiv += '<div class="form-group col-md-4">\
    <input id="'+ k +'_'+key+'" name="DvManageMonthlyIncentiveExceptionRate['+ k +'][]" type="number" class="form-control" required="required" value="'+ v +'">\
            </div>';
                                });
                                $("#min_max_rate_container").append(newTextBoxDiv);
                                exception_closures_counter++;
                            });

                            if ($("#min_max_rate_container").length <= 0) {

                            } else {
                                if ($("#op_btn").length <= 0) {
                                    var add_remove_rate_btn = $('<div id="op_btn">');
                                    add_remove_rate_btn.after().html('<button type="button" id="add_closures_range" class="btn btn-info"><i class="fa fa-plus"></i></button> <button type="button" id="remove_closures_button" class="btn btn-danger"><i class="fa fa-times"></i></button>');
                                    add_remove_rate_btn.appendTo("#add_remove_rate_btn");
                                }
                            }
                        }
                        
                    }
                    $("#loading_custom").hide();
                }
            });
        }
    });
    $('body').on('change', '#dvmanagemonthlyincentiveexceptionrate-executive_ids', function (){
        exception_closures_counter = 0;
        var executive_id = $(this).val();
        var month = $("#dvmanagemonthlyincentiveexceptionrate-month").val();
        var domain = $("#dvmanagemonthlyincentiveexceptionrate-domain").val();
        $("#min_max_rate_container").html('');
        $("#add_remove_rate_btn").html('');
        if(month != '' && domain != '' && executive_id != ''){
            $("#loading_custom").show();
            $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-finance/get_month_wise_closures') ?>',
                type: 'POST',
                data: {month: month, domain:domain, executive_id:executive_id},
                success: function(data){
                    if (data == 0) {
                        alert("No rules added for " + month);
                    } else {
                        var closures = JSON.parse(data);
                        if (closures['current_incentive'] == "") {
                            $("#min_max_rate_container").html('<div class="col-md-12 form-group"><div class="small_custom_alert alert-warning">No rules.</div></div>');
                        } else {
                            if (closures['is_exception'] == "1") {
                                $("#min_max_rate_container").html('<div class="col-md-12 form-group"><div class="small_custom_alert alert-info">Rate with Exception.</div></div>');
                            } else {
                                $("#min_max_rate_container").html('<div class="col-md-12 form-group"><div class="small_custom_alert alert-info">Rate without Exception.</div></div>');
                            }
                            $.each(closures['current_incentive'], function(key,val){
                                var newTextBoxDiv = '<div id="TextBoxDiv'+key+'">';
                                $.each(val, function(k,v){

                                    newTextBoxDiv += '<div class="form-group col-md-4">\
        <input id="'+ k +'_'+key+'" name="DvManageMonthlyIncentiveExceptionRate['+ k +'][]" type="number" class="form-control" required="required" value="'+ v +'">\
                </div>';
                                });
                                $("#min_max_rate_container").append(newTextBoxDiv);
                                exception_closures_counter++;
                            });

                            if ($("#min_max_rate_container").length <= 0) {
                                alert("No Rules");
                            } else {
                                if ($("#op_btn").length <= 0) {
                                    var add_remove_rate_btn = $('<div id="op_btn">');
                                    add_remove_rate_btn.after().html('<button type="button" id="add_closures_range" class="btn btn-info"><i class="fa fa-plus"></i></button> <button type="button" id="remove_closures_button" class="btn btn-danger"><i class="fa fa-times"></i></button>');
                                    add_remove_rate_btn.appendTo("#add_remove_rate_btn");
                                }
                            }
                        }
                    }
                    $("#loading_custom").hide();
                }
            });
        }
    });

    function set_add_exception_rate_btn(enable) {
        if (enable == 1) {
            $("#add_exception_rate").attr("disabled", false);
        } else {
            $("#add_exception_rate").attr("disabled", true);
        }
    }

</script>
    </body>
    </html>
    <?php $this->endPage() ?>
<?php } ?>
