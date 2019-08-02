<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\DvUsers;
use app\models\DvCourse;
use app\models\DvModuleModel;
use yii\widgets\LinkPager;
use yii\widgets\DetailView;
use yii\helpers\ArrayHelper;
use app\models\DvParticipant;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use app\models\DvUserMeta;
use kartik\export\ExportMenu;
use yii\data\ArrayDataProvider;
// this is a index file of "All Module/Batch". it is use to list all the All Module/Batch 10 per page. It is also contain a filter form. 
$this->title = 'All Batch';
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['index']]; ?>
<div class="dv-users-index">
 <?php $form = ActiveForm::begin(['id' => 'module-search-form', 'method' => 'get', 'action' => Url::to(['dv-delivery/filter'])]); 
    $select = 'selected="selected"';
    //module list 
    $module = DvModuleModel::find()->where(['status'=>1])->all();
    $Dv_module = ArrayHelper::map($module, 'id', 'module_name');
    echo '<div class="form-group col-md-3" data-toggle="tooltip" data-placement="top" title="Select Module">';    
    echo '<select class="form-control" name="module[]" multiple="multiple" size="5">';
    $module_k_get = Yii::$app->request->get('module');
    $module_k = count($module_k_get) > 0  && isset($module_k_get) ? $module_k_get : array();
    if(empty($module_k)){
        echo '<option value="" selected="selected">Select Module (All)</option>';
    } else {
        echo '<option value="" >Select Module</option>';
    }
    foreach($Dv_module as $key => $val){
        echo '<option ';
        if(in_array($key,$module_k)){
            echo $select;
        } 
        echo ' value="'.$key.'">'. $val .'</option>';
    }
    echo '</select>';
    echo '</div>';    

    echo '<div class="form-group col-md-3" data-toggle="tooltip" data-placement="top" title="Select Date/Month">
    <select class="form-control" name="" id="by_date_month">
    <option value="">Select Date/Month</option>';
    // for date drop down item
    $sdate_k = Yii::$app->request->get('sdate');
    $edate_k = Yii::$app->request->get('edate');
    if(!empty($sdate_k)){
        echo '<option value="d" selected="selected">Search by Date</option>';
        $date_class = ' ';
    } else if(!empty($edate_k)){
        echo '<option value="d" selected="selected">Search by Date</option>';
        $date_class = ' ';
    } else {
        echo '<option value="d">Search by Date</option>';
        $date_class = ' hide';
    }
    
    // for month drop down item
    $by_month = Yii::$app->request->get('bymonth');
    if(empty($by_month)){
        echo '<option value="m">Search by Month</option>';
    } else {
        echo '<option value="m" selected="selected">Search by Month</option>';
    }
    
    echo '</select></div>';

    echo '<div class="form-group col-md-3 select_by_date '.$date_class.'" data-toggle="tooltip" data-placement="top" title="Select Start Date">'; ?>
    <input type="text" id="sdate" value="<?php echo $sdate_k ?>" class="datepicker_se form-control select_by_date" name="sdate" placeholder="Start Date" autocomplete="off">
    <?php
    echo '</div>';
    echo '<div class="form-group col-md-3 select_by_date '.$date_class.'" data-toggle="tooltip" data-placement="top" title="Select End Date">';
    ?>
    <input type="text" value="<?php echo $edate_k ?>" class="datepicker_se form-control select_by_date" name="edate" id="edate" placeholder="End Date" autocomplete="off">
    <?php
    echo '</div>'; 
    // filter by month
    $by_month = Yii::$app->request->get('bymonth'); ?>
    <div class="form-group col-md-2 select_by_month <?php if(empty($by_month)){ echo 'hide'; } ?>" data-toggle="tooltip" data-placement="top" title="Select Month">
    <select class="form-control select_by_month" id="bymonth" name="bymonth">
    	<option value="">Select Month</option>
    	<?php $start_date = Yii::$app->db->createCommand("SELECT DISTINCT start_date FROM assist_batches")->queryAll();    	
    	$month_val = array();
    	foreach($start_date as $key => $value){
    		foreach($value as $key => $val){
    			$start_month_val = date('M_Y', strtotime($val));
    			$start_month = date('M Y', strtotime($val));
    			$month_val[$start_month_val] = $start_month;
    		}
    	}

        $end_date = Yii::$app->db->createCommand("SELECT DISTINCT meta_value FROM assist_batches_meta WHERE meta_key = 'final_end_date' ")->queryAll();

        foreach($end_date as $key => $value){
            foreach($value as $key => $val){
                $start_month_val = date('M_Y', strtotime($val));
                $start_month = date('M Y', strtotime($val));
                $month_val[$start_month_val] = $start_month;
            }
        }

    	foreach($month_val as $key => $value){
    		echo '<option ';
    		if($by_month == $key){
    			echo $select;
    		}
    		echo ' value="'.$key.'">'. $value .'</option>';
    	}
    ?>
    </select>
</div>
<div class="form-group col-md-3" data-toggle="tooltip" data-placement="top" title="Select Trainer">
    <?php 
    $user_meta_data = ArrayHelper::map(DvUsers::find()->where(['department'=>'7'])->all(),'id','id'); 
    $trainer_array = array();
    foreach ($user_meta_data as $key => $value) {
        $users_data = DvUsers::find()->where(['id'=>$key])->one();
        $trainer_array[$key] = ucfirst($users_data['first_name'].' '.$users_data['last_name']);
    }
    $trainer_k = Yii::$app->request->get('trainer');
    ?>
    <select class="form-control" name="trainer">
        <!-- <option value="prompt">Select Trainer</option> -->
        <option value="">Select Trainer</option> 
        <?php foreach ($trainer_array as $key => $value) { ?>
            <option <?php echo $trainer_k == $key ? 'selected="selected"' : ''; ?> value="<?php echo $key; ?>"><?php echo $value; ?></option>     
        <?php } ?>
    </select>
</div>
<div class="form-group col-md-3" data-toggle="tooltip" data-placement="top" title="Select Coordinator">
    <select class="form-control" name="coordinator">
        <option value="">Select Coordinator (All)</option>
        <?php $connection = Yii::$app->getDb();
            $command = $connection->createCommand("select id, first_name, last_name, course from assist_users where status = 1 AND department = 2");
            $enrolled_users_arr = $command->queryAll();
            $coordinator_k = Yii::$app->request->get('coordinator');
            if (!empty($enrolled_users_arr)){
                foreach ($enrolled_users_arr as $enroll_user){
                    $name = $enroll_user['first_name'].' '.$enroll_user['last_name'];
                    $id = $enroll_user['id'];
                    
            echo '<option ';
             if($coordinator_k == $id){
                echo $select;
            } 
            echo ' value="'.$id.'">'. $name .'</option>';
                }
            } ?>
    </select>
</div>
<div class="form-group col-md-3" data-toggle="tooltip" data-placement="top" title="Select Batch Status" >
    <select class="form-control" name="vstatus">
        <?php $vstatus_k = Yii::$app->request->get('vstatus'); ?>
        <option value="">Running Batch Status (All)</option>
        <option <?php if($vstatus_k == '1'){ echo $select; } ?> value="1">Completed</option>
        <option <?php if($vstatus_k == '2'){ echo $select; } ?> value="2">Ongoing</option>
        <option <?php if($vstatus_k == '3'){ echo $select; } ?> value="3">Upcoming</option>
    </select>
</div>
<div class="form-group col-md-3" data-toggle="tooltip" data-placement="top" title="Select Status">
    <select class="form-control" name="bstatus">
        <?php $bstatus_k = Yii::$app->request->get('bstatus'); ?>
        <option value="">Batch Status (All)</option>
        <option <?php if($bstatus_k == '1'){ echo $select; } ?> value="1">Open</option>
        <option <?php if($bstatus_k == '0'){ echo $select; } ?> value="0">Close</option>
    </select>
</div>
<div class="form-group col-md-3">
    <?= Html::submitButton( '<i class="fa fa-filter"></i> Apply Filter' , ['class' => 'btn btn-sm pull btn-warning search_submit']) ?>
    <?= Html::resetButton( '<i class="fa fa-refresh"></i> Reset' , ['onclick'=>'reset_call()','class' => 'btn btn-sm btn-warning search_submit']) ?>
</div>
<?php ActiveForm::end(); ?>
<div class="form-group col-md-12">
    <h4>Export Batch</h4>
    <?php
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
    // creation of array for excel exporet
    $exl_array = array();
    $count = 1;
        foreach($modules as $module){
            $trainer = DvUsers::find()->where(['id'=>$module->trainer, "status" => 1])->all();
            $Dv_trainer_f = ArrayHelper::map($trainer, 'id', 'first_name');
            $trainer_f = array_values($Dv_trainer_f);                
            $Dv_trainer_l = ArrayHelper::map($trainer, 'id', 'last_name');
            $trainer_l = array_values($Dv_trainer_l);                
            if(!empty($trainer_f)){
                $trainer_person  = $trainer_f[0].' '.$trainer_l[0];
            } else {
                $trainer_person  = ' ';
            }

            $module_data = DvModuleModel::find()->where(['id'=>$module->module])->all();
            $Ucourse = ArrayHelper::map($module_data, 'id', 'module_name');
            //if(count($Ucourse) > 0){
                $u_course = array_values($Ucourse);
                $usercourse = !empty($u_course[0]) ? $u_course[0] : ''; //module name 
            //}
            $coordinator = DvUsers::find()->where(['id'=>$module->coordinator, "status" => 1])->all();
            $Dv_coordinator_f = ArrayHelper::map($coordinator, 'id', 'first_name');
            $f_coordinator_person = array_values($Dv_coordinator_f);
            $Dv_coordinator_l = ArrayHelper::map($coordinator, 'id', 'last_name');
            $l_coordinator_person = array_values($Dv_coordinator_l);
            if(!empty($f_coordinator_person)){
                $coordinator_person = $f_coordinator_person[0].' '.$l_coordinator_person[0];        
            } else {
                $coordinator_person = '';
            }
            
            // end date
            $end_date = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$module->id' AND meta_key = 'final_end_date' ")->queryOne();
            if(empty($end_date['meta_value'])){
                $end_date = '-----';
            } else {
                $end_date = $end_date['meta_value'];
            }    
            
            // Day     
            if ($module->day == 'sat'){
                $day = 'Saturday';
            } else if($module->day == 'sun'){
                $day = 'Sunday';
            } else if($module->day == 'fri'){
                $day = 'Friday';
            } else if($module->day == 'thu'){
                $day = 'Thursday';
            } else if($module->day == 'wed'){
                $day = 'Wednesday';
            } else if($module->day == 'tue'){
                $day = 'Tuesday';
            } else if($module->day == 'mon'){
                $day = 'Monday';    
            } else if($module->day == 'tue-thu'){
                $day = 'Tues - Thur';
            } else {
                $day = $module->day;
            }

            // batch status
            $batch_status = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$module->id' AND meta_key = 'batch_status' ")->queryOne();
            $batchstatus = $batch_status['meta_value'];
            
            if( $batchstatus == '1'){
                $view = 'Open';
            } elseif( $batchstatus == '0') {
                $view = 'Close';
            } else {
                $view = '---';
            }

            // running batch status
            $running_batch_status = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$module->id' AND meta_key = 'running_batch_status' ")->queryOne();
            $runbatsta = $running_batch_status['meta_value'];

            if( $runbatsta == 1){
                $status = 'Completed';
            } else if($runbatsta == 2) {
                $status = 'Ongoing';
            } else if($runbatsta == 3) {
                $status = 'Upcoming';
            }else{
                $status = '';
            }

            //Rating
            $batch_rating = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$module->id' AND meta_key = 'batch_rating' ")->queryOne();

            $rating = $batch_rating['meta_value'];
            if(empty($rating)){
                $rating = '---';
            }
            
            //Completion Percentage
            $comper = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$module->id' AND meta_key = 'comper' ")->queryOne();

            $com_per = $comper['meta_value'];
            if(empty($com_per)){
                $com_per = '---';
            }

            //NPS
            $nps_val = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$module->id' AND meta_key = 'nps' ")->queryOne();

            $nps = $nps_val['meta_value'];
            if(empty($nps)){
                $nps = '---';
            }

            //Assignments
            $assignmentsrec = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$module->id' AND meta_key = 'assignmentsrec' ")->queryOne();

            $assignments = $assignmentsrec['meta_value'];
            if(empty($assignments)){
                $assignments = '---';
            }

            //Rescheduling
            $number_of_reschedul = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$module->id' AND meta_key = 'number_of_reschedul' ")->queryOne();

            $reschedul = $number_of_reschedul['meta_value'];
            if(empty($reschedul)){
                $reschedul = '---';
            }
            if(array_key_exists($module->id,$batch_allocated_array)){
                $nos_alloted_seat_for_export =  $batch_allocated_array[$module->id]['batch_id'];
            }else{
                $nos_alloted_seat_for_export = 0;
            }
            if($vstatus_k != '1'){
                $exl_array[] = array( 
                'id'=>$count,
                'Module' => $usercourse,
                'Trainer' => $trainer_person,
                'Coordinator' => $coordinator_person,
                'Start Date' => $module->start_date,
                'End Date' => $module->end_date,
                'Day' => $day,
                'Timing' => $module->stiming,
                'Seat(s)' => $nos_alloted_seat_for_export.'/'.$module->seats,                
                'Batch Status' => $view,
                'Running Batch Status' => $status);                
                $count = $count+1;
            } else {
                $exl_array[] = array( 
                'id'=>$count,
                'Module' => $usercourse,
                'Trainer' => $trainer_person,
                'Coordinator' => $coordinator_person,
                'Start Date' => $module->start_date,
                'End Date' => $module->end_date,
                'Day' => $day,
                'Timing' => $module->stiming,
                'Seat(s)' => $nos_alloted_seat_for_export.'/'.$module->seats,
                'Rating' => $rating,
                'Completion Percentage' => $com_per,
                'NPS' => $nps,
                'Assignments Received' => $assignments,
                'No of Rescheduling' => $reschedul,
                'Batch Status' => $view,
                'Running Batch Status' => $status);                
                $count = $count+1;
            }

         } 

            $excel_array = array('allModels' => $exl_array );
            $dataProvider = new ArrayDataProvider($excel_array);

            // creation of array for export as excel
            if($vstatus_k != '1'){
                $columns = array('id', 'Module', 'Trainer','Coordinator','Start Date','End Date','Day','Timing','Seat(s)','Batch Status','Running Batch Status');
            } else {
                $columns = array('id', 'Module', 'Trainer','Coordinator','Start Date','End Date','Day','Timing','Seat(s)', 'Rating', 'Completion Percentage', 'NPS', 'Assignments Received', 'No of Rescheduling', 'Batch Status','Running Batch Status');
            }

        $file_name = 'Module Batch('.date('Y-m-d').')';
        echo ExportMenu::widget([
            'dataProvider' => $dataProvider,
            'fontAwesome' => true,
            'columns' => $columns, //['id', 'fruit', 'quantity']
            'options' => ['id'=>'expMenu1'], // optional to set but must be unique
            'target' => ExportMenu::TARGET_BLANK,
            'filename' => $file_name
        ]);
         ?>
    <button type='button' onclick="call_batch_form();" class="btn pull-right btn-info" data-toggle="tooltip" data-placement="top" title="New Batch"><i class="fa fa-form"></i> New Batch </button>
    </div>
    <table class="table table-striped">
            <thead>
            <tr> <th><center>#</center></th>
                <th>Module</th>
                <th>Trainer</th>
                <th>Coordinator</th>
                <th>
                 <?php $sort_order = Yii::$app->request->get('order');
                 if($sort_order == 'asc'){
                    echo '<a data-toggle="tooltip" data-placement="top" title="Sort Date by Ascending Order" href="sort?order=desc">Start Date <i class="fa fa-sort-asc" aria-hidden="true"></i></a>';
                 } elseif($sort_order == 'desc'){
                    echo '<a data-toggle="tooltip" data-placement="top" title="Sort Date by Descending Order" href="sort?order=asc">Start Date <i class="fa fa-sort-desc" aria-hidden="true"></i></a>';
                 } else {
                    echo '<a data-toggle="tooltip" data-placement="top" title="Sort Date by Ascending Order" href="sort?order=desc">Start Date <i class="fa fa-sort"></i></a>';
                 } ?>
                </th>
                <th>End Date</th>

                <?php if($vstatus_k != '1'){
                    echo '<th>Day</th><th>Timing</th><th>Seat(s)</th>';
                } else {
                    echo '<th>Rating</th><th>Completion %</th><th>NPS</th><th>Assignments</th><th>Rescheduling</th>';
                } ?>
                

                <th>Batch Status</th>
                
                <?php if($vstatus_k != '1'){
                    echo '<th>Running Batch Status</th>';
                } ?>                
                
                
                <th>Edit</th>                
                </tr>
            </thead>
            <tbody>
            <?php $count = 1;
             $page = Yii::$app->request->get('page');
            if(empty($page)){
              $i = 0;
            } else {
                if($page == 1){
                    $i = 0;
                } else {
                    $i = ($page*10)-10;                    
                }
              
            }

            foreach($modules as $module){  $i++;
                // Trainer
                $trainer = DvUsers::find()->where(['id'=>$module->trainer, "status" => 1])->all();
                $Dv_trainer = ArrayHelper::map($trainer, 'id', 'first_name');
                $trainer_person = array_values($Dv_trainer);
                if(empty($trainer_person[0])){
                    $trainer_person = '----';
                } else{
                    $trainer_person = $trainer_person[0];
                }
                // Module
                $module_daata = DvModuleModel::find()->where(['id'=>$module->module])->all();
                $Ucourse = ArrayHelper::map($module_daata, 'id', 'module_name');
                $u_course = array_values($Ucourse);

                // Coordinator   
                $coordinator = DvUsers::find()->where(['id'=>$module->coordinator, "status" => 1])->all();
                $Dv_coordinator = ArrayHelper::map($coordinator, 'id', 'first_name');
                $coordinator_person = array_values($Dv_coordinator);
                if(empty($coordinator_person[0])){
                    $coordinator_person = '-----';
                } else {
                    $coordinator_person = $coordinator_person[0];    
                }
             
                // end date
                $end_date = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$module->id' AND meta_key = 'final_end_date' ")->queryOne();
                if(empty($end_date['meta_value'])){
                    $end_date = '<center>-----</center>';
                } else {
                    $end_date = $end_date['meta_value'];    
                }

                if ($module->day == 'sat'){
                    $day = 'Sat';
                } else if($module->day == 'sun'){
                    $day = 'Sun';
                } else if($module->day == 'fri'){
                    $day = 'Fri';
                } else if($module->day == 'thu'){
                    $day = 'Thur';
                } else if($module->day == 'wed'){
                    $day = 'Wed';
                } else if($module->day == 'tue'){
                    $day = 'Tues';
                } else if($module->day == 'mon'){
                    $day = 'Mon';    
                } else if($module->day == 'tue-thu'){
                    $day = 'Tues - Thur';
                } else {
                    $day = $module->day;
                }

                $batch_status = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$module->id' AND meta_key = 'batch_status' ")->queryOne();
                $batchstatus = $batch_status['meta_value'];

                if( $batchstatus == '1'){
                    $view = '<center>Open</center>';
                } elseif( $batchstatus == '0') {
                    $view = '<center>Close</center>';
                } else {
                    $view = '<center>---</center>';
                }
                

                $running_batch_status = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$module->id' AND meta_key = 'running_batch_status' ")->queryOne();
                $runbatsta = $running_batch_status['meta_value'];
                //updated on 29 April 2019
                if( $runbatsta == '1'){
                    $status =  '<center data-toggle="tooltip" data-placement="top" title="Completed"><i class="fa fa-eye-slash red_icon"></i></center>';
                } elseif( $runbatsta == '2') {
                    $status = '<center data-toggle="tooltip" data-placement="top" title="Ongoing"><i class="fa fa-eye green_icon"></i></center>';
                } else if($runbatsta == '3') {
                    $status = '<center data-toggle="tooltip" data-placement="top" title="Upcoming" id="upcoming_idd"><i class="fa fa-hourglass-start red_icon"></i></center>';
                }else{
                    $status = '---';
                }


                //Rating
                $batch_rating = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$module->id' AND meta_key = 'batch_rating' ")->queryOne();

                $rating = $batch_rating['meta_value'];
                if(empty($rating)){
                    $rating = '---';
                }
                
                //Completion Percentage
                $comper = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$module->id' AND meta_key = 'comper' ")->queryOne();

                $com_per = $comper['meta_value'];
                if(empty($com_per)){
                    $com_per = '---';
                }

                //NPS
                $nps_val = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$module->id' AND meta_key = 'nps' ")->queryOne();

                $nps = $nps_val['meta_value'];
                if(empty($nps)){
                    $nps = '---';
                }

                //Assignments
                $assignmentsrec = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$module->id' AND meta_key = 'assignmentsrec' ")->queryOne();

                $assignments = $assignmentsrec['meta_value'];
                if(empty($assignments)){
                    $assignments = '---';
                }

                //Rescheduling
                $number_of_reschedul = Yii::$app->db->createCommand("SELECT meta_value FROM assist_batches_meta WHERE mid = '$module->id' AND meta_key = 'number_of_reschedul' ")->queryOne();

                $reschedul = $number_of_reschedul['meta_value'];
                if(empty($reschedul)){
                    $reschedul = '---';
                }

                $course_module = !empty($u_course[0]) ? $u_course[0] : '';
                echo '<tr> 
                 <td><center><a class="btn btn-xs btn-info" data-toggle="tooltip" data-placement="top" title="View Detail" href="view?id='.$module->id.'"><strong>'.$i . '</strong></a></center></td>
                <td>' . $course_module . '</td>
                <td>'.$trainer_person.'</td>
                <td>' . $coordinator_person . '</td>
                <td>' . $module->start_date . '</td>
                <td>' . $module->end_date . '</td>';

                if($vstatus_k != '1'){
                echo '<td>' . $day . '</td>
                <td>' . $module->stiming . '</td>';
                if(array_key_exists($module->id,$batch_allocated_array)){
                    $nos_alloted_seat =  $batch_allocated_array[$module->id]['batch_id'];
                }else{
                    $nos_alloted_seat = 0;
                }
                
                //echo "-----".$nos_alloted_seat."<br>"; 
                echo '<td><center data-toggle="tooltip" data-placement="top" title="Seats/Total Seats">'.$nos_alloted_seat.'/' . $module->seats . '</center></td>';
                } else {
                    echo '<td>'.$rating.'</td><td>'.$com_per.'</td><td>'.$nps.'</td><td>'.$assignments.'</td><td>'.$reschedul.'</td>';
                }
                
                echo '<td> '.$view.' </td>';
                
                if($vstatus_k != '1'){
                echo '<td><center>' . $status. '</center></td>';
                }
                echo '<td><center><a data-toggle="tooltip" data-placement="top" title="Edit Batch" href="edit?id='.$module->id.'"><i class="fa fa-pencil"></i></a></center></td>';
                echo '</tr>';
                $count = $count+1;
            } 

            if(empty($modules)){
                echo '<tr><td colspan="12"><center> <h3>No Record Found</h3> </center></td> </tr>';
                $total_records = '';
            }
            ?>
            </tbody>
        </table>
        <?php // display pagination
            echo LinkPager::widget(['pagination' => $pages]);
            if($total_records != '') {  ?>
        <ul class="pagination pull-right"><li>
        <a class="btn btn-xs btn-info" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php echo 'Total Listed Batches: '.$total_records; ?>"><strong><?php echo 'Total Listed Batches: '.$total_records; ?></strong></a></li></ul>
    <?php } ?>
        <div class="clr"></div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $('.datepicker_se').datepicker({dateFormat:'dd-mm-yy',changeMonth: true,changeYear: true});
        $('#by_date_month').change(function(){
            if($('#by_date_month').val() == 'm'){
                $('#sdate').val('');
                $('#edate').val('');
            }else if($('#by_date_month').val() == 'd'){
                $('#bymonth').val('');
            }
        });
    });
    function call_batch_form(){
        window.location.replace("<?php echo Url::to(['dv-delivery/create_module'])?>");
    }
    function reset_call(){
        window.location.replace("<?php echo Url::to(['dv-delivery/index'])?>");
    }
</script>