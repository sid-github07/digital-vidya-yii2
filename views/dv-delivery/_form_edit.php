<?php

use yii\helpers\Html;
use app\models\DvUsers;
use app\models\DvCourse;
use app\models\DvModuleModel;
use app\models\DvUserMeta;
use app\models\DvUsersRole;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\DvTrainingTopics;
use app\models\DvAssistBatches;
?>
<div class="edit-dv-module-form">
    <?php $form = ActiveForm::begin([ 'id' => $model->formName(),'options' => [ 'class' => 'dv_module'], 
        'fieldConfig' => ['options' => ['class' => 'form-group col-md-4']],
        ]);
 	$module = DvModuleModel::find()->where(['status'=>1])->all();
    $Dv_module = ArrayHelper::map($module, 'id', 'module_name');

    echo $form->field($model, 'module')->dropDownList($Dv_module, ['prompt'=>'Select Module', 'id'=>"module_module_id",'required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Select Course"])->label(false);
    echo $form->field($model, 'start_date')->textInput(['required' => 'required'])->input('start_date', ['placeholder' => "Start Date", 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Start Date"])->label(false);
    //here old :  $end_date new : $model->end_date       
    echo '<div class="form-group col-md-4"><input id="module_end_date" class="form-control hasDatepicker" title="" placeholder="End Date" data-toggle="tooltip" readonly="readonly" data-placement="top" aria-required="true" data-original-title="End Date" aria-invalid="false" value="'.$model->end_date.'"><div class="help-block"></div></div>';

    echo '<input type="hidden" name="end_date" id="set_end_date_hidden" value="'.$model->end_date.'">';
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

    echo $form->field($model, 'stiming')->dropDownList($times_array, ['placeholder' => "Start Time",'required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'autocomplete'=>'off','title'=>"Select Start Time",'prompt'=>'Select Start Time'])->label(false);

    echo $form->field($model, 'etiming')->dropDownList($times_array, ['placeholder' => "End Time",'required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'autocomplete'=>'off','title'=>"Select End time",'prompt'=>'Select End Time'])->label(false);


    echo $form->field($model, 'duration')->input('Time Duration', ['placeholder' => "Time Duration",'required' => 'required', 'id'=>'time_different', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'autocomplete'=>'off', 'readonly'=>'readonly','title'=>"Time Duration"])->label(false);
    $user_meta_data = DvUsers::find()->where(['department'=>'7'])->all();
    $trainer_array = array();
    foreach($user_meta_data as $val){
        $course_array =  explode(",",$val['course']);
        if(in_array($model->module,$course_array)){
            $trainer_array[$val['id']]=ucfirst($val['first_name'].' '.$val['last_name']);
        }
    }
    ?>
    <div class="form-group col-md-4 field-DvAssistBatches-trainer">
        <select id="DvAssistBatches-trainer" class="form-control" name="DvAssistBatches[trainer]" aria-required="true" aria-invalid="false" data-toggle="tooltip" data-placement="top" title="Select Trainer">
            <option value="prompt">Select Trainer</option>
            <?php  foreach ($trainer_array as $key => $value) { ?>
                <option <?php echo $model->trainer!='' && $model->trainer == $key ? 'selected="selected"' : ''; ?> value="<?php echo $key; ?>"><?php echo $value; ?></option>     
            <?php } ?>
        </select>
    </div>
    <?php
    // for Delivery Person
    echo '<div class="form-group col-md-4"><select id="DvAssistBatches-coordinator" class="form-control" name="DvAssistBatches[coordinator]" aria-required="true" aria-invalid="true" data-toggle="tooltip" data-placement="top" title="Select Co-ordinator Person">';
    
    // Coordinator
    $user_coor = DvUserMeta::find()->where(['uid' => $model->trainer , 'meta_key' => 'coordinator' ])->all();
    $coordinator = ArrayHelper::map($user_coor, 'uid', 'meta_value');
    if(!empty($coordinator[$model->trainer])){
        echo '<option value="'.$coordinator[$model->trainer].'">';
        echo DvUsers::find()->where(['id'=>$coordinator[$model->trainer], "status" => 1])->one()->first_name;
        echo ' ';
        echo DvUsers::find()->where(['id'=>$coordinator[$model->trainer], "status" => 1])->one()->last_name;
        echo '</option>';
    } else {
        echo '<option value="">Select Co-ordinator Person</option>';    
    }
     
    echo '</select></div>';        

    $number_of_weeks = array();
    for ($x = 1; $x <= 25; $x++){
        $key = $x;
        $number_of_weeks[$key] = $x.' Session';
    } 
    echo $form->field($model, 'type')->dropDownList($number_of_weeks, ['prompt'=>'Select Number of Sessions','required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Select Number of Sessions"])->label(false);

     

    $sdate = $model->start_date;
    $F_date = date('l', strtotime($sdate));
    $S_date = date('D', strtotime($sdate));
    $S_date = strtolower($S_date);

  
    // if($sdate == 'tue-thu'){
    if($model->day == 'tue-thu'){
        echo $form->field($model, 'day')->dropDownList(['tue'=>'Tuesday','tue-thu'=>'Tuesday-Thursday'], ['prompt'=>'Select Day','required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Select Day"])->label(false);
    } else {
        echo $form->field($model, 'day')->dropDownList([$S_date=>$F_date], ['required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Select Day"])->label(false);
    }

    $seats_array = array();
    for($i = 20; $i<=80; $i++){
        $seats_array[$i] = $i;
    }
    echo $form->field($model, 'seats')->dropDownList($seats_array, ['prompt'=>'Select Number of Seat(s)','required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Select Number of Seat(s)"])->label(false); 

    echo $form->field($model, 'joining_link')->input('Joining Link', ['placeholder' => "Joining Link",'required' => 'required', 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'autocomplete'=>'off','title'=>"Joining Link"])->label(false);
    

    ?>
     
<div class="form-group col-md-12 session_wrapper"><!----form-group col-md-12 session_wrapper---->
    <div id='TextBoxesGroup'>
            <?php $mid = $model->id;
            $total_session_2 = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'all_sessions' ")->queryOne();
            $total_session = $total_session_2['meta_value']; ?>
            <h3 id="total_session" class="blue_color" data-total-session="<?php echo $total_session; ?>">No. of Sessions: <?php echo $total_session; ?> </h3>
            <input id="all_sessions" name="all_sessions" value="<?php echo $total_session; ?>" type="hidden">
            <?php //$total_session = 0;
            $start_date_array = array(); 
            for($i = 1; $i<=40; $i++){ 
                // major for loop
                //echo $i.'<br>';
                $session_key_val = 'session'.$i;
                $session_key_val2 = 'session'.$i.'rec';
                $stime_key = 'start_time'.$i;
                $etime_key = 'end_time'.$i;
                $recordURL = 'recording_url'.$i;
                //For Session
                $meta_value = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key = '$session_key_val' ")->queryOne();

                //For Start Time
                $start_time_meta_value = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key = '$stime_key' ")->queryOne();

                //For End Time
                $end_time_meta_value = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key = '$etime_key' ")->queryOne();

                //For Recording URL
                 $recordURL_value = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key = '$recordURL' ")->queryOne();

                if(!empty($meta_value['meta_value'])){
                    $value = $meta_value['meta_value'];
                    if($i==1){
                        $readonly = 'readonly="readonly"';
                        $readonly_rec_url = 'readonly="readonly"';
                    }else{
                        $diff = date_diff(date_create(date('Y-m-d')),date_create($value));
                        $readonly_condition = $diff->format("%R%a");
                        if($readonly_condition >=0 ){
                            $readonly = ''; 
                        }else{
                            $readonly = 'readonly="readonly"';
                        }
                        $readonly_rec_url = '';
                    }
                    $start_stime = ''; 
                    $end_etime = '';
                    $start_stime = $start_time_meta_value['meta_value'];
                    $end_etime = $end_time_meta_value['meta_value'];
                    $rec_url = '';
                    $rec_url = $recordURL_value['meta_value'];
                    echo '<div id="TextBoxDiv'.$i.'" class="row">';
                    echo '<div class="form-group col-md-1 blank">';
                    echo '<button type="button" class="btn btn-warning">';
                    echo '<span class="badge">'.$i.'</span>';
                    echo '</button></div>';
                    $now_time = new DateTime();
                    $test = $value.' 9:00AM';
                    $event_date = new DateTime($test);
                    if ( $now_time > $event_date || $i == '1'){
                        $readonly_with_id = ' readonly="readonly" ';
                        $readonly = 'readonly="readonly"';
                    } else {
                        $readonly_with_id = '  id="session_'.$i.'" ';
                    }
                     
                    if($i == '1'){
                        $session_tooltip = 'data-toggle="tooltip" data-placement="top" data-original-title="Update Session 1 from Start Date"';
                    } else {
                        $session_tooltip = 'data-toggle="tooltip" data-placement="top" title="Select Date"';
                    }
                    $st_tooltip = 'data-toggle="tooltip" data-placement="top" data-original-title="Start Time"';
                    $et_tooltip = 'data-toggle="tooltip" data-placement="top" data-original-title="End Time"';
                    $rec_url_tooltip =  'data-toggle="tooltip" data-placement="top" data-original-title="Recording URL"';
                    //For Date
                    $start_date_array [] = $value;
                    echo '<div class="form-group col-md-2 session'.$i.' ">';
                        echo '<input class="form-control" id="session_'.$i.'" value="'.$value.'" name="allsession['.$session_key_val.'][session]" required="required" type="text" placeholder="Session '.$i.' Date" '.$readonly_with_id.' '.$session_tooltip.'>';
                    echo '</div>';

                    //For Start time
                    echo '<div class="form-group col-md-2 session_time'.$i.' ">';
                    echo '<input class="form-control" id="session_stime'.$i.'" required="required" name="allsession['.$session_key_val.'][start_time]" placeholder="Start Time" autocomplete="off" type="text" '.$st_tooltip.'  '.$readonly.' value="'.$start_stime.'" >';
                    echo '</div>';

                    //For End time
                    echo '<div class="form-group col-md-2 session_time'.$i.' ">';
                    echo '<input class="form-control" id="session_etime'.$i.'" required="required" name="allsession['.$session_key_val.'][end_time]" placeholder="End Time" autocomplete="off" type="text" '.$et_tooltip.'  '.$readonly.'   value="'.$end_etime.'" >';
                    echo '</div>';
                    ////////////////////22 May 2019///////////
                    echo '<div class="form-group col-md-4 recording_url'.$i.' ">';
                    echo '<input class="form-control" id="recording_url'.$i.'" name="allsession['.$session_key_val.'][recording_url]" placeholder="Recording URL" autocomplete="off" type="text"   '.$rec_url_tooltip.'   value="'.$rec_url.'" >';
                    echo '</div>';
                    ////////////////////22 May 2019///////////
                    echo '<div class="hide" data-totalsession="1"></div>';
                    echo '</div>';
                }  
            }// major for loop  
            echo '<input name="fend_date" id="fend_date" value="'.$model->end_date.'" type="hidden">';
            echo '<input name="edit_module_form" id="edit_module_form" value="'.$model->id.'" type="hidden">';
            // toatl number of existing sessions ?>
    </div>
    <div class="form-group col-md-4"></div>
    <div class="form-group col-md-8">
        <?php
        // updated at 29/30 april 2019  
        $last_date =  $start_date_array[count($start_date_array) - 1];
        $diff = date_diff(date_create(date('Y-m-d')),date_create(date('Y-m-d',strtotime($last_date))));
        $remove_condition = $diff->format("%R%a"); ?>
        <input type="hidden" id="remove_btn_nos" value="<?php echo $remove_condition; ?>">
        <button type='button' id='<?php echo $model->day == 'tue-thu' ? 'removeButton2' : 'removeButton'; ?>' class="btn pull-right btn-danger" 
            style="display: none;" data-toggle="tooltip" data-placement="top" title="Remove Session"><i class="fa fa-times"></i></button>
        <button type='button' id='<?php echo $model->day == 'tue-thu' ? 'addButton2' : 'addButton'; ?>' data-toggle="tooltip" data-placement="top" title="Add Session" class="btn pull-right btn-info" ><i class="fa fa-plus"></i></button>
    </div>
    <?php
    //Added on 21 may 2019
    $number_of_reschedul = $reschedule_data_reason = '';
    $number_of_reschedul = Yii::$app->db->createCommand("SELECT count(*) FROM assist_batches_reschedule WHERE mid = '$model->id'")->queryScalar();
    ?>
    <div id="reschedule_count_block" style="display:none" class="form-group col-md-7">
        <label>Reschedule Reason</label>
        <input name="reschedule_count" id="reschedule_count" value="<?php echo isset($number_of_reschedul) && !empty($number_of_reschedul) ? $number_of_reschedul + 1 : 1 ;?>" type="hidden">
        <textarea class='form-control text_area_data' rows="5" name="reschedule_text"></textarea>
    </div>
</div>

<input type="hidden" id="sessions_data_updates_count" name="sessions_data_updates_count" value="1">
<input type="hidden" id="actual_sessions_" name="actual_sessions_" value="<?php echo $model->day != 'tue-thu' ? $model->type : $model->type*2; ?>">

 

<div class="form-group col-md-12 trainer_confirm">
<?php $mid = $model->id;
    $trainer_confirm = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'trainer_confirm' ")->queryOne();
 $trainerconfirm = $trainer_confirm['meta_value']; //echo $trainerconfirm.'test'; ?>
    <input name="trainer_confirm" value="0" type="hidden">
    <label><input name="trainer_confirm" id="trainer_confirm" value="1" type="checkbox" <?php if($trainerconfirm == '1'){ echo 'checked="checked"'; } ?>> Add Batch Details</label></div> 


<div class="trainer_confirmation <?php if($trainerconfirm == 0){ echo 'hide'; } ?> ">
    <div class="form-group col-md-12"><h3 class="blue_color">Feedback Information</h3></div>

 <div class="form-group col-md-4">
 <?php 
 $session_link = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'session_link' ")->queryOne();
 $sessionlink = $session_link['meta_value']; ?>
    <input class="form-control" name="session_link" placeholder="Session Feedback Link (Google)" value="<?php echo $sessionlink; ?>" data-toggle="tooltip" data-placement="top" title="Session Feedback Link (Google)">
 </div>

 <div class="form-group col-md-4">
 <?php 
 $session_res_link = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'session_res_link' ")->queryOne();
 $sessionreslink = $session_res_link['meta_value']; ?>
 <input class="form-control" name="session_res_link" placeholder="Session Feedback Response Link (Google)" value="<?php echo $sessionreslink; ?>" data-toggle="tooltip" data-placement="top" title="Session Feedback Response Link (Google)">
 </div>


  <div class="form-group col-md-4">
<?php 
 $course_feedback = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'course_feedback' ")->queryOne();
 $coursefeedback = $course_feedback['meta_value']; ?>
    <input class="form-control" name="course_feedback" placeholder="Course end Feedback (Final)" value="<?php echo $coursefeedback; ?>" data-toggle="tooltip" data-placement="top" title="Course end Feedback (Final)">
 </div>

<div class="form-group col-md-12"><h3 class="blue_color">Online Platform Detail</h3></div>

 <div class="form-group col-md-4">
<?php 
 $online_platform_id = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'online_platform_id' ")->queryOne();
 $onlineplatformid = $online_platform_id['meta_value']; ?>    
    <input class="form-control" name="online_platform_id" placeholder="Online Platform ID" value="<?php echo $onlineplatformid; ?>" data-toggle="tooltip" data-placement="top" title="Online Platform ID">
 </div>
 <div class="form-group col-md-4">
<?php 
 $online_platform_url = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'online_platform_url' ")->queryOne();
 $onlineplatformurl = $online_platform_url['meta_value']; ?>    
    <input class="form-control" name="online_platform_url" placeholder="Online Platform URL" value="<?php echo $onlineplatformurl; ?>" data-toggle="tooltip" data-placement="top" title="Online Platform URL">
 </div><div class="form-group col-md-4 blank"></div>
 <div class="form-group col-md-4">
<?php 
 $online_platform_username = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'online_platform_username' ")->queryOne();
 $opusername = $online_platform_username['meta_value']; ?>    
<input class="form-control" name="online_platform_username" placeholder="Online Platform Username" value="<?php echo $opusername; ?>" data-toggle="tooltip" data-placement="top" title="Online Platform Username">
 </div>
 <div class="form-group col-md-4">
<?php 
 $online_platform_password = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'online_platform_password' ")->queryOne();
 $oppassword = $online_platform_password['meta_value']; ?>    
<input class="form-control" name="online_platform_password" placeholder="Online Platform Password" value="<?php echo $oppassword; ?>" data-toggle="tooltip" data-placement="top" title="Online Platform Password">
 </div>
 <div class="form-group col-md-12 no-margin"></div>
</div>

<?php 
 $batch_status = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'batch_status' ")->queryOne();
 $batchstatus = $batch_status['meta_value']; ?>

 <div class="form-group col-md-4">
    <select class="form-control" name="batch_status" data-toggle="tooltip" data-placement="top" title="Batch Status">
        <option value="">Batch Status</option>
        <option value="1" <?php if($batchstatus == '1'){echo 'selected="selected"';}?> >Open</option>
        <option value="0" <?php if($batchstatus == '0'){echo 'selected="selected"';}?>>Close</option>
    </select>
</div>

<?php 
 $running_batch_status = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'running_batch_status' ")->queryOne();

 $runbatsta = isset($running_batch_status['meta_value']) ? $running_batch_status['meta_value'] : '';
 //print_r($runbatsta);  
  ?>

 <div class="form-group col-md-4">
    <select class="form-control" name="running_batch_status" id="running_batch_status" data-toggle="tooltip" data-placement="top" title="Running Batch Status">
        <option value="">Running Batch Status</option>
        <option value="1" <?php if($runbatsta == '1'){echo 'selected="selected"';}?> >Completed</option>
        <option value="2" <?php if($runbatsta == '2'){echo 'selected="selected"';}?> >Ongoing</option>
        <option value="3" <?php if($runbatsta == '3'){echo 'selected="selected"';}?> >Upcoming</option>
    </select>
</div>

<div class="form-group col-md-12 trainer_cordi_notify trainer_confirmation <?php if($trainerconfirm == 0){ echo 'hide'; } if($runbatsta == '1'){echo 'hide';} ?> ">
    <input name="trainer_cordi_notify" value="0" type="hidden">
    <label><input name="trainer_cordi_notify" value="1" type="checkbox"> Send details to Trainer</label></div> 
<div class="pefor_info <?php if($runbatsta == '' || $runbatsta == '2' || $runbatsta == '3') { echo 'hide'; } ?> ">
    <div class="form-group col-md-12">
        <h3>Performance Information</h3>
    </div>
    <div class="form-group col-md-4">
        <?php 
         $batch_rating = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'batch_rating' ")->queryOne();
         $batchrating = $batch_rating['meta_value']; ?>
        <input class="form-control" name="batch_rating" placeholder="Batch Rating" value="<?php echo $batchrating; ?>" data-toggle="tooltip" data-placement="top" title="Batch Rating">
    </div>
    <div class="form-group col-md-4">
        <?php 
         $comper = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'comper' ")->queryOne();
         $ucomper = $comper['meta_value']; ?>
        <input class="form-control" name="comper" placeholder="Completion Percentage" value="<?php echo $ucomper; ?>" data-toggle="tooltip" data-placement="top" title="Completion Percentage">
    </div>
    <div class="form-group col-md-4">
        <?php 
         $nps = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'nps' ")->queryOne();
         $unps = $nps['meta_value']; ?>
            <input class="form-control" name="nps" placeholder="NPS" value="<?php echo $unps; ?>" data-toggle="tooltip" data-placement="top" title="NPS">
    </div>
    <div class="form-group col-md-4">
        <?php 
         $assignmentsrec = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'assignmentsrec' ")->queryOne();
         $assignsrec = $assignmentsrec['meta_value']; ?>
            <input class="form-control" name="assignmentsrec" placeholder="Assignments Received" value="<?php echo $assignsrec; ?>" data-toggle="tooltip" data-placement="top" title="Assignments Received">
    </div>
    <?php 
     //$number_of_reschedul = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'number_of_reschedul' ")->queryOne();
     //$number_of_reschedul = $number_of_reschedul['meta_value']; ?>
    <!-- <div class="form-group col-md-4">
        <input class="form-control" name="number_of_reschedul" placeholder="Number of Reschedulings" value="<?php //echo $number_of_reschedul; ?>" data-toggle="tooltip" data-placement="top" title="Number of Reschedulings">
    </div> -->

    <div class="form-group col-md-4">
        <?php 
         $sugg_feed = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'sugg_feed' ")->queryOne();
         $suggfeed = $sugg_feed['meta_value']; ?>
            <textarea class="form-control" name="sugg_feed" rows="2" placeholder="Suggestions/feedback" data-toggle="tooltip" data-placement="top" title="Suggestions/feedback"><?php echo $suggfeed; ?></textarea>
    </div>

    <div class="form-group col-md-12 trainer_c_notify">
        <input name="trainer_c_notify" value="0" type="hidden">
        <label><input name="trainer_c_notify" value="1" type="checkbox"> Send completion report to Trainer</label>
    </div>
</div>
<div class="form-group col-md-8">
<?php //if($model->isNewRecord){
    //echo Html::submitButton('<i class="fa fa-check"></i> Create Module', ['class' => 'btn btn-success']); 
//} else {
    echo Html::submitButton('<i class="fa fa-pencil"></i> Update Module', ['class' => 'btn btn-primary','id'=>'update_module']);
//} ?>

    <?= Html::a('<i class="fa fa-times"></i> Cancel', ['index'], ['class' => 'btn back_button btn-danger cancel_button']); ?>

    <?= Html::a('<i class="fa fa-arrow-left"></i> Back to View', ['view?id='.$model->id], ['class' => 'btn back_button btn-default pull-right']); ?>

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
#reschedule{display: none;}
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>

$('body').on('change', '#module_module_id', function (){
    var module_id = $(this).val();
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
