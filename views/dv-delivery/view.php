<?php
use yii\helpers\Html;
use app\models\DvUsers;
use app\models\DvCourse;
use app\models\DvModuleModel;
use yii\widgets\DetailView;
use yii\helpers\ArrayHelper;
use app\models\DvTrainingTopics;
use app\models\DvAssistBatches;
//use app\models\DvParticipantModulesSession;


/* @var $this yii\web\View */
/* @var $model app\models\DvUsers */

$this->title = 'Module Detail';
$this->params['breadcrumbs'][] = ['label' => 'All Module/Batch', 'url' => ['index']];  ?>
<div class="container"> 
  <div class="row">
    <div class="col-md-10">
        <div class="dv-users-view">
            <?php $mid = $model->id;
                $number_of_reschedul = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'number_of_reschedul' ")->queryOne();
                $number_of_reschedul = $number_of_reschedul['meta_value'];
                ////////////Added on 22 May 2019//////////
            $batch_allocated_array = array();
            $query_batch = Yii::$app->db->createCommand("SELECT count(pid) as nos_of_student,batch_id,pid FROM assist_participant_batch_meta GROUP BY batch_id")->queryAll();
            if(count($query_batch) > 0){
                for($i=0;$i<count($query_batch);$i++){
                    $batch_allocated_array[$query_batch[$i]['batch_id']]['batch_id'] = $query_batch[$i]['nos_of_student'];
                    
                    $batch_meta_result = Yii::$app->db->createCommand("SELECT * FROM assist_participant_batch_meta where batch_id=".$query_batch[$i]['batch_id'])->queryAll();
                    foreach($batch_meta_result as $val_res){
                        $batch_allocated_array[$query_batch[$i]['batch_id']]['participant_id'][] = $val_res['pid'];
                    }
                }
            }
            if(array_key_exists($model->id,$batch_allocated_array)){
                $nos_alloted_seat_data =  $batch_allocated_array[$model->id]['batch_id'];
            }else{
                $nos_alloted_seat_data = 0;
            }
                 ?>
            <button class="btn btn-success pull-right">Seat Left : <?php echo $model->seats - $nos_alloted_seat_data; ?> </button>

            <?php if(!empty($number_of_reschedul)){ ?>
            <button style="margin-right: 10px;" class="btn btn-default pull-right">Reschedulings : <?php echo $number_of_reschedul; ?></button>
        <?php } ?>
        <h1><?php 
                if(!empty($model->module)){
                        $module_nname = DvModuleModel::find()->where(['id'=>$model->module])->one()->module_name;
                        echo !empty($module_nname) ? $module_nname : '';
                } ?> | <?php echo $model->start_date; ?></h1>
<table id="w0" class="table table-striped table-bordered detail-view">
    <tbody>
        <tr><th>Module</th>
            <td><?php if(!empty($model->module)){
                        $module_nnamee = DvModuleModel::find()->where(['id'=>$model->module])->one()->module_name;
                        echo !empty($module_nnamee) ? $module_nnamee : '';
                    } ?></td>
        </tr>
        <?php /* ?>
        <tr><th>Training Topic</th>
            <td><?php if($model->training_topic != 0){
                        echo DvTrainingTopics::find()->where(['id'=>$model->training_topic])->one()->name;
                    } ?></td>
        </tr> <?php */ ?>
        <tr><th>Create By</th>
            <td>
                <?php
                    $created_by = $model->created_by;
                    $users_result = DvUsers::find()->where(['id'=>$created_by])->one();
                    echo $users_result->first_name." ".$users_result->last_name;

                ?>

            </td>
        </tr>
        <tr><th>Start Date</th>
            <td><?php echo $model->start_date; ?></td>
        </tr>
        <tr><th>End Date</th>
            <td><?php
            /*
            $end_date = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key = 'final_end_date' ")->queryOne();
            if(empty($end_date['meta_value'])){
                $end_date = '-----';
            } else {
                $end_date = $end_date['meta_value'];
            }
            */
            $total_session_2 = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key = 'all_sessions' ")->queryOne();
            $total_session = $total_session_2['meta_value']; 
            echo $model->end_date; ?></td>
        </tr>
        <tr><th>Time Duration</th>
            <td><?php echo $model->duration; ?></td>
        </tr>
        <tr><th>Day</th>
            <td><?php if ($model->day == 'sat'){
                    $day = 'Saturday';
                } else if($model->day == 'sun'){
                    $day = 'Sunday';
                } else if($model->day == 'fri'){
                    $day = 'Friday';
                } else if($model->day == 'thu'){
                    $day = 'Thursday';
                } else if($model->day == 'wed'){
                    $day = 'Wednesday';
                } else if($model->day == 'tue'){
                    $day = 'Tuesday';
                } else if($model->day == 'mon'){
                    $day = 'Monday';    
                } else if($model->day == 'tue-thu'){
                    $day = 'Tuesday - Thursday';
                } else {
                    $day = '----';
                } echo $day; ?></td>
        </tr>
        <tr><th>Start Time</th>
            <td><?php echo $model->stiming; ?></td>
        </tr>
        <tr><th>End Time</th>
            <td><?php echo $model->etiming; ?></td>
        </tr>
        <tr><th>Trainer</th>
            <td><?php if($model->trainer != 0){
                        echo DvUsers::find()->where(['id'=>$model->trainer, "status" => 1])->one()->first_name;
                        echo ' ';
                        echo DvUsers::find()->where(['id'=>$model->trainer, "status" => 1])->one()->last_name;
                    } else {
                        echo '---';
                    } ?></td>
        </tr>
         <tr><th>Coordinator</th>
            <td><?php if($model->coordinator != 0){
                        echo DvUsers::find()->where(['id'=>$model->coordinator, "status" => 1])->one()->first_name;
                        echo ' ';
                        echo DvUsers::find()->where(['id'=>$model->coordinator, "status" => 1])->one()->last_name;
                    } ?></td>
        </tr>

        <tr><th>Number of Session</th>
            <td><?php echo $model->type.' Session'; ?></td>
        </tr>
        
        <tr><th>Number of Seat(s)</th>
            <td><?php echo $model->seats; ?></td>
        </tr>

        <tr><th>Number of Seat(s) Alloted</th>
            <td><?php echo $nos_alloted_seat_data; ?></td>
        </tr>

        <tr><th>Joining Link</th>
            <td><?php echo $model->joining_link; ?></td>
        </tr>

        </tbody>
        </table>        
        <h3 class="blue_color">Total Number of Sessions : <?php echo $total_session; ?></h3>
        <?php
        echo "<table class='table table-striped'>
                <thead>
                    <tr>
                        <th>Session Date</th>
                        <th>Session Start Time</th>
                        <th>Session End Time</th>
                        <th>Session Recording URL</th>
                    </tr>
                </thead>
                <tbody>";
                 for($i = 1; $i<=50; $i++){ // major for loop
                        $session_key_val = 'session'.$i;
                        $meta_value = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key = '$session_key_val' ")->queryOne();
                         
                        $session_key_val_st = 'start_time'.$i;
                        $meta_value_st = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key = '$session_key_val_st' ")->queryOne();

                        $session_key_val_et = 'end_time'.$i;
                        $meta_value_et = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key = '$session_key_val_et' ")->queryOne();

                        $session_rec_url = 'recording_url'.$i;
                        $session_rec_url_data = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$model->id' AND meta_key = '$session_rec_url' ")->queryOne(); 

                        if($meta_value['meta_value'] != ''){
                            $session_date = $meta_value['meta_value'];
                            $session_s_time = $meta_value_st['meta_value'];
                            $session_e_time = $meta_value_et['meta_value'];
                            $session_url = $session_rec_url_data['meta_value'];
                            echo" <tr>
                                            <td>$session_date</td>
                                            <td>$session_s_time</td>
                                            <td>$session_e_time</td>
                                            <td>$session_url</td>
                                </tr>";
                                   

                        }
                    }
        echo "</tbody>
            </table>";

        //Added on 21 may 2019
        $number_of_reschedul = Yii::$app->db->createCommand("SELECT count(*) FROM assist_batches_reschedule WHERE mid = '$model->id'")->queryScalar();    
        $reschedul_data = Yii::$app->db->createCommand("SELECT * FROM assist_batches_reschedule WHERE mid = '$model->id'")->queryAll();    
        if(!empty($number_of_reschedul) && count($reschedul_data) > 0){
            ?>

            <h3 class="blue_color">Total Number of Reschedule : <?php echo $number_of_reschedul; ?></h3>
            
            <table class='table table-striped'>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Date</th>
                        <th>Reschedule Text</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reschedul_data as $value_reschedule) {
                        ?>
                        <tr>
                        <td><?php echo $value_reschedule['reschedule_count']; ?></td>
                        <td><?php echo date('d-m-Y',strtotime($value_reschedule['created_on'])); ?></td>
                        <td><?php echo $value_reschedule['reschedule_text']; ?></td>
                    </tr>
                        <?php
                    } ?>
                </tbody>
            </table>
 
            <?php 
        }
 $mid = $model->id;
 $trainer_confirm = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'trainer_confirm' ")->queryOne();
 $trainerconfirm = $trainer_confirm['meta_value'];
    if($trainerconfirm != ''){ ?>
        <tr>
            <th colspan="2">
                <h3 class="blue_color">Feedback Information</h3>
            </th>
        </tr>
        <tr>
        <div class="form-group">
            <label>Trainer Confirmation : </label>
            <?php if($trainerconfirm == '1'){ echo 'Yes'; } else {echo 'No';} ?>
        </div>
            <?php 
    }
    if($trainerconfirm == '1'){ // if Trainer Confirmation is YES
        $session_link = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'session_link' ")->queryOne();
        $sessionlink = $session_link['meta_value']; ?>
        <div class="form-group">
            <label>Session Feedback Link (Google) : </label>
            <?php echo $sessionlink; ?>
        </div>
        <?php $session_res_link = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'session_res_link' ")->queryOne();
        $sessionreslink = $session_res_link['meta_value']; ?>
        <div class="form-group">
            <label>Session Feedback Response Link (Google) : </label>
            <?php echo $sessionreslink; ?> 
        </div>
    <?php 
    $course_feedback = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'course_feedback' ")->queryOne();
    $coursefeedback = $course_feedback['meta_value']; ?>
   <div class="form-group">
        <label>Course and Feedback (Final) : </label>
            <?php echo $coursefeedback; ?>
    </div>
    <div class="form-group">
        <h3 class="blue_color">Online Platform Detail</h3>
    </div>
    <?php 
     $online_platform_id = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'online_platform_id' ")->queryOne();
     $onlineplatformid = $online_platform_id['meta_value']; ?>
    <div class="form-group">
        <label>Online Platform ID : </label>
        <?php echo $onlineplatformid; ?>
    </div>
    <?php 
    $online_platform_url = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'online_platform_url' ")->queryOne();
    $onlineplatformurl = $online_platform_url['meta_value']; ?>
    <div class="form-group">
        <label>Online Platform URL : </label>
        <?php echo $onlineplatformurl; ?>
    </div>
    <?php 
    $online_platform_username = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'online_platform_username' ")->queryOne();
    $opusername = $online_platform_username['meta_value']; ?>
    <div class="form-group">
        <label>Online Platform Username : </label>
        <?php echo $opusername; ?>
    </div>
    <?php 
    $online_platform_password = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'online_platform_password' ")->queryOne();
    $oppassword = $online_platform_password['meta_value']; ?>
    <div class="form-group">
        <label>Online Platform Password : </label>
        <?php echo $oppassword; ?>
    </div>
        <?php 
    } // if Trainer Confirmation is YES


    $batch_status = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'batch_status' ")->queryOne();
    $batchstatus = $batch_status['meta_value'];
    if($batchstatus != ''){ ?>
        <div class="form-group">
            <label>Batch Status : </label>
            <?php if($batchstatus == '1'){echo 'Open';} else {echo 'Close';} ?> 
        </div>
        <?php 
    }

    $running_batch_status = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'running_batch_status' ")->queryOne();
    $runbatsta = $running_batch_status['meta_value'];
 
    if($runbatsta != ''){ ?>
        <div class="form-group">
            <label>Running Batch Status : </label>
             <?php if($runbatsta == '1'){echo 'Completed';} else if($runbatsta == '2') { echo 'Ongoing';} else if($runbatsta == '3'){echo 'Upcoming';} ?>
        </div>
        <?php 
    } ?>
    <?php if($runbatsta == '1'){ ?>
    <div class="form-group">
        <h4 class="blue_color"><label>Performance Information</label></h4>
    </div>
    <?php 
    $batch_rating = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'batch_rating' ")->queryOne();
    $batchrating = $batch_rating['meta_value']; ?> 
    <div class="form-group">
        <label>Batch Rating : </label>
        <?php echo $batchrating; ?> 
    </div>
    <?php 
    $comper = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'comper' ")->queryOne();
    $ucomper = $comper['meta_value']; ?>
    <div class="form-group">
        <label>Completion Percentage : </label>
        <?php echo $ucomper; ?>
    </div>

    <?php 
    $nps = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'nps' ")->queryOne();
    $unps = $nps['meta_value']; ?>
    <div class="form-group">
        <label>NPS : </label>
        <?php echo $unps; ?>
   </div>
    <?php 
    $assignmentsrec = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'assignmentsrec' ")->queryOne();
    $assignsrec = $assignmentsrec['meta_value']; ?>
    <div class="form-group">
        <label>Assignments Received : </label>

        <?php echo $assignsrec; ?>
    </div>
    <?php 
    $sugg_feed = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$mid' AND meta_key = 'sugg_feed' ")->queryOne();
    $suggfeed = $sugg_feed['meta_value']; ?>
    <div class="form-group">
        <label>Suggestions/feedback : </label>
        <?php echo $suggfeed; ?>
    </div>
    <?php 
    }  ?>
    <div class="form-group">
        <h3 class="blue_color">Communication History</h3>
    </div>

    <?php $eamils_log = Yii::$app->db->createCommand("SELECT * FROM assist_email_log where sid = '$mid' and cron <= '1' ")->queryAll();
     //print_r($eamils_log);
        foreach($eamils_log as $key => $value){
            $event = str_replace("_"," ",$value['event']);
            $events = ucfirst($event);
            $message = str_replace("###br###","<br><br>",$value['message']);
            echo '<tr><td><strong>Event:</strong> '.$events.'</td><td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#myModal'.$key.'">View Email Detail</button>';
            echo '<div id="myModal'.$key.'" class="modal fade" role="dialog">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><strong>From: </strong> '.$value['from_email'].'</h4>
                    <h4 class="modal-title"><strong>To: </strong>'.$value['to_email'].'</h4>
                  </div>
                  <div class="modal-body"><p> '.$message.'</p> </div>      
                </div>

            </div>
        </div> ';
        echo '<div class="pull-right">';
        if(empty($value['send_on'])){
            echo Yii::$app->CustomComponents->date_formatting($value['created']);
        } else {
            echo Yii::$app->CustomComponents->date_formatting($value['send_on']);
        }

        echo ' </div></td></tr>';
        }

        ?>
    </tbody>
</table>
<p><?= Html::a('<i class="fa fa-pencil"></i> Edit Module', ['edit', 'id' => $model->id], ['class' => 'btn btn-primary']); ?>
    <?= Html::a('<i class="fa fa-arrow-left"></i> Back', ['index'], ['class' => 'btn back_button btn-danger pull-right']); ?>
</p>
</div>
</div>
<div class="col-md-4"></div>
</div>
</div>