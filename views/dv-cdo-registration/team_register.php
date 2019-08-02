<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\DvCourse;
use yii\widgets\LinkPager;
use yii\widgets\DetailView;
use yii\helpers\ArrayHelper;

use app\models\DvParticipant;
use app\models\DvUsers;
use app\models\DvUserMeta;
use app\models\DvParticipantModules;
// use app\models\DvParticipantPayments;

use kartik\export\ExportMenu;
use yii\data\ArrayDataProvider;

use yii\widgets\ActiveForm;
use yii\helpers\Url;


$user = Yii::$app->user->identity;
$usermeta_result = DvUserMeta::find()->where(['uid'=>$user->id,'meta_key'=>'role'])->one();
// $user_department = $user->department; // 1 - sales department
$user_role = $usermeta_result->meta_value; // 2 - Executive role


/* @var $this yii\web\View */
/* @var $searchModel app\models\DvUsersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'All Registered Participant';
$this->params['breadcrumbs'][] = $this->title; ?>

<!-- Searching functionality -->
<?php
$filter_p_status = '';
$filter_pp_status = '';
$filter_sales_user_id = '';
$bymonth = '';
$by_date_month = '';
$sdate = '';
$edate = '';
$filter_course = array();
$filter_team_id = '';
$byTeam = '';
$by_email = '';

/*echo "<pre>";
print_r($filter_data);
die;*/

if(isset($filter_data['participant_status'])){
    $filter_p_status = $filter_data['participant_status'];
}
if(isset($filter_data['participant_payment_status'])){
    $filter_pp_status = $filter_data['participant_payment_status'];
}
if(isset($filter_data['sales_user_id'])){
    $filter_sales_user_id = $filter_data['sales_user_id'];
}
if(isset($filter_data['bymonth'])){
    $bymonth = $filter_data['bymonth'];
}
if(isset($filter_data['by_date_month'])){
    $by_date_month = $filter_data['by_date_month'];
}
if(isset($filter_data['email'])){
    $by_email = $filter_data['email'];
}
if(isset($filter_data['sdate'])){
    $sdate = $filter_data['sdate'];
    $edate = $filter_data['edate'];
}
if(isset($filter_data['course'])){
    $filter_course = $filter_data['course'];
    /*echo "<pre>";
    print_R($filter_course);
    die;*/
}
if(isset($filter_data['byTeam'])){
    $byTeam = $filter_data['byTeam'];
}

 ?>
<div class="dv-users-index">
    <?php $form = ActiveForm::begin(['id' => 'module-search-form', 'method' => 'get', 'action' => Url::to(['dv-registration/team_filter'])]); 

        // Select Course
        $select = 'selected="selected"';
        $course = DvCourse::find()->where(['status'=>1])->all();
        $Dv_course = ArrayHelper::map($course, 'id', 'name');
        // unset($Dv_course[1]); unset($Dv_course[2]);

        echo '<div class="form-group col-md-2">';
        //echo '<input name="course" value="" type="hidden">';
        echo '<select class="form-control" name="course[]" multiple="multiple" size="5">';
        $course_k = Yii::$app->request->get('course');

        if(empty($filter_course)){
            echo '<option value="" selected="selected" >Select Courses</option>';
        } else {
            echo '<option value="" >Select Courses</option>';
        }
            foreach($Dv_course as $key => $val){
                echo '<option ';
                if(in_array($key, $filter_course)){
                    echo $select;
                } 
                echo ' value="'.$key.'">'. $val .'</option>';
            }
        echo '</select>';
        echo '</div>';
    ?>

<?php // filter by Month/Date ?>
    <div class="form-group col-md-3">
        <select class="form-control" name="by_date_month" id="by_date_month">

        <?php if($by_date_month == ''){
            echo '<option value="" selected="selected" >Select Date/Month</option>';
        } else {
            echo '<option value="">Select Date/Month </option>';
        } ?>
        <option <?php if($by_date_month == 'd'){ echo $select; } ?> value="d">Search by Date</option>
        <option <?php if($by_date_month == 'm'){ echo $select; } ?> value="m">Search by Month</option>
        </select>
    </div>

 <?php // filter by month ?>
    <div class="form-group col-md-2 select_by_month <?php if($bymonth == ''){ echo 'hide'; } ?>">
    <select class="form-control select_by_month" name="bymonth">
        <option value="">Select Month</option>
        <?php $date = Yii::$app->db->createCommand("SELECT DISTINCT created_on FROM assist_participant")->queryAll();     
        $month_val = array();

        foreach($date as $key => $value){
            foreach($value as $key => $val){
                $start_month_val = date('m_Y', strtotime($val));
                $start_month = date('M Y', strtotime($val));
                $month_val[$start_month_val] = $start_month;
            }
        }
        foreach($month_val as $key => $value){
            echo '<option ';
            if($bymonth == $key){
                echo $select;
            }
            echo ' value="'.$key.'">'. $value .'</option>';
        }
    ?>
    </select>
</div>


<?php  // Search by date  ?>
 <div class="form-group col-md-3 select_by_date <?= ($sdate=='')?'hide':''; ?>">
    <input type="text" value="<?= $sdate ?>" class="datepicker_se form-control" name="sdate" placeholder="Select Start Date" autocomplete="off">   
 </div>

 <div class="form-group col-md-3 select_by_date <?= ($edate=='')?'hide':''; ?>">
    <input type="text" value="<?= $edate ?>" class="datepicker_se form-control" name="edate" placeholder="Select End Date" autocomplete="off">
 </div>

        <!-- Search by Participantion Status -->
        <div class="form-group col-md-3">
            <select class="form-control" name="participant_status">
                <?php if($filter_p_status == ''){
                    echo '<option value="" selected="selected" >Select Participant Status</option>';
                } else {
                    echo '<option value="" >Select Participant Status</option>';
                } ?>
                <option <?php if($filter_p_status == '1'){ echo $select; } ?> value="1">Active</option>
                <option <?php if($filter_p_status == '2'){ echo $select; } ?> value="2">On Hold</option>
                <option <?php if($filter_p_status == '3'){ echo $select; } ?> value="3">Drop Off</option>
                <option <?php if($filter_p_status == '4'){ echo $select; } ?> value="4">Completed</option>
            </select>
        </div>

        <!-- Search by Participantion Payment Status -->
        <div class="form-group col-md-3">
            <select class="form-control" name="participant_payment_status">
                <?php if($filter_pp_status == ''){
                    echo '<option value="" selected="selected" >Select Participant Payment Status</option>';
                } else {
                    echo '<option value="" >Select Participant payment Status</option>';
                } ?>
                <option <?php if($filter_pp_status == '1'){ echo $select; } ?> value="1">Payment Due</option>
                <option <?php if($filter_pp_status == '2'){ echo $select; } ?> value="2">Refund</option>
                <option <?php if($filter_pp_status == '3'){ echo $select; } ?> value="3">Completed</option>
                <option <?php if($filter_pp_status == '4'){ echo $select; } ?> value="4">NA</option>
            </select>
        </div>

        <?php 
        if($user_role == 7){
            
        ?>
        <!-- // filter by Team -->
        <div class="form-group select_by_team by_executive">
            <div class="form-group col-md-3">
                <select class="form-control select_by_team" name="byTeam" id="byTeam">
                    <option value="">All Consultant</option>
                    <?php 
                    $team_model_val = Yii::$app->db->createCommand("SELECT assist_user_meta.* , assist_users.*  FROM assist_users join assist_user_meta ON assist_users.id = assist_user_meta.uid WHERE assist_users.department = 1 and assist_user_meta.meta_key = 'team' and assist_user_meta.meta_value = '' AND assist_users.status=1")->queryAll();
                    
                     foreach($team_model_val as $team_value){
                        if($team_value['id'] == $user->id){
                            echo '<option ';
                            if($byTeam == $team_value['id']){
                                echo $select;
                            }
                            echo ' value="'.$team_value['id'].'">My Team</option>';
                        }
                    }

                    foreach($team_model_val as $team_value){
                        if($team_value['id'] != $user->id){
                            echo '<option ';
                            if($byTeam == $team_value['id']){
                                echo $select;
                            }
                            echo ' value="'.$team_value['id'].'">'. $team_value['first_name']." ".$team_value['last_name'] .'</option>';
                        }
                    }
                ?>
                </select>
            </div>
            <?php if($byTeam != '') { 
                  $usermeta_result = DvUserMeta::find()->where(['meta_key'=>'team','meta_value'=>$byTeam])->all();
                  if($usermeta_result){
                    echo "<div class='form-group col-md-3 cls_byExecutive'>
                            <select class='form-control' name='sales_user_id' id='byExecutive'>
                              <option value=''>Select Consultant</option>";
                              foreach($usermeta_result as $val){
                                  $usermeta_result = DvUsers::findOne($val['uid']);
                               echo "<option value='".$val['uid']."'";
                               if($filter_sales_user_id == $val['uid']){
                                    echo $select;
                                }
                               echo ">".$usermeta_result->first_name.' '.$usermeta_result->last_name."</option>";
                              }
                    echo "</select></div>";
                    }else{
                        echo "<div class='form-group col-md-3 cls_byExecutive'><select class='form-control col-md-3' name='sales_user_id' id='byExecutive'>
                                  <option value=''>Select Consultant</option>";
                        echo "</select></div>";
                    }
            }
            ?>
        </div>
        <?php } ?>

        <?php 
        if($user_role == 6 ){
        ?>
        <!-- Search by Sales executive Name -->
        <div class="form-group col-md-3">
            <select class="form-control" name="sales_user_id">
                 <?php if($filter_team_id == ''){
                    echo '<option value="" selected="selected" >Select Sales Consultant</option>';
                } else {
                    echo '<option value="" >Select Sales Consultant</option>';
                } ?>
                <?php 

                    $user_res = Yii::$app->user->identity;
                    /*$usermeta_result = DvUserMeta::find()->where(['uid'=>$user_res->id,'meta_key'=>'team'])->one();
                    $team_id =  $usermeta_result->meta_value;*/

                    $connection = Yii::$app->getDb();
                    $command = $connection->createCommand("SELECT assist_users.id, assist_users.first_name, assist_users.last_name, assist_users.course, assist_user_meta.meta_key, assist_user_meta.meta_value FROM assist_users INNER JOIN assist_user_meta ON assist_users.id = assist_user_meta.uid WHERE assist_users.status = 1 AND assist_users.department = 1 AND assist_user_meta.meta_key = 'team' AND assist_user_meta.meta_value=$user_res->id");


                    $enrolled_users_arr = $command->queryAll();
                  
                    if (!empty($enrolled_users_arr)){
                        foreach ($enrolled_users_arr as $enroll_user){
                            if($enroll_user['id'] != $user_res->id){
                                $name = $enroll_user['first_name'].' '.$enroll_user['last_name'];
                                $id = $enroll_user['id'];
                                
                                echo '<option ';
                                 if($filter_sales_user_id == $id){
                                    echo $select;
                                } 
                                echo ' value="'.$id.'">'. $name .'</option>';
                            }
                        }
                    } ?>
            </select>
        </div>
        <?php } ?>

        <div class="form-group col-md-4">
            <input type="text" value="<?= isset($by_email)?$by_email:'' ?>" class="form-control" name="email" placeholder="Enter Email" autocomplete="off">
        </div>

        <div class="form-group col-md-1">
            <?= Html::submitButton( '<i class="fa fa-search"></i> Search' , ['class' => 'btn btn-sm pull btn-success search_submit']) ?>
        </div>
        <div class="form-group col-md-1">
            <a href="<?= Url::to(['dv-registration/teamview']) ?>" class="btn btn-default">
                <i class="fa fa-refresh" aria-hidden="true"></i> Reset
            </a>
        </div>
    <?php ActiveForm::end(); ?>
</div>


<!-- Export particioant data -->
<div class="form-group col-md-12">
    <h4>Export Participant Data</h4>
    <?php
    // creation of array for excel exporet
    $exl_array = array();
    $export_count = 1;
  
    foreach($all_participant_users as $user){

        if( $user->participant_status == 1){
            $status = 'Active';
        } else {
            $status = 'In-active';
        }
        $date = date("d-m-Y",strtotime($user->created_on));
        // Get sales name
        $sales_id = $user->sales_user_id;                
        $sales = DvUsers::find()->where(['id'=>$sales_id])->all();
        $sales_name1 = array_values(ArrayHelper::map($sales, 'id', 'first_name'));
        $sales_name2 = array_values(ArrayHelper::map($sales, 'id', 'last_name'));
        $sales_name = $sales_name1[0]." ".$sales_name2[0];
        
        $username = $user->first_name . ' ' . $user->last_name; 

        $course = DvCourse::find()->where(['id'=>$user->course])->all();
        $Ucourse = ArrayHelper::map($course, 'id', 'name');
        $u_course = array_values($Ucourse);
        
        $participant_payment_status =  $user->participant_payment_status;
        $pp_status = "";
        if($participant_payment_status == 1){
            $pp_status = "Payment Due";
        }elseif($participant_payment_status == 2){
            $pp_status = "Refund";
        }elseif($participant_payment_status == 3){
            $pp_status = "Completed";
        }elseif($participant_payment_status == 4){
            $pp_status = "NA";
        }

        $participant_status =  $user->participant_status;
        $p_status = "";
        if($participant_status == 1){
            $p_status = "Active";
        }elseif($participant_status == 2){
            $p_status = "On Hold";
        }elseif($participant_status == 3){
            $p_status = "Drop off";
        }elseif($participant_status == 4){
            $p_status = "completed";
        }

        if($user->program_coordinator!=""){
            $program_coordinatior = $user->program_coordinator;
        }else{
            $program_coordinatior = "NA";
        }

        $exl_array[] = array( 
            'id'=>$export_count,
            'Date' => $date,
            'Sales Name' => $sales_name,
            'Username' => $username,
            'Email' => $user->email,
            'Program Coordinator' => $program_coordinatior,
            'Course' => $u_course[0],
            'Batch Date' => $user->course_batch_date,
            'Total Modules Allowed' => $user->modules_allowed,
            'Participantion Status' => $p_status,
            'Payment Status' => $pp_status,
        );
        $export_count = $export_count+1;
    }

        $excel_array = array('allModels' => $exl_array,'pagination' => false );
        $dataProvider = new ArrayDataProvider($excel_array);

        // creation of array for export as excel
        $columns = array('id', 'Date', 'Sales Name','Username','Email','Program Coordinator','Course','Batch Date','Total Modules Allowed','Participantion Status','Payment Status');
      

    $file_name = 'Participant Details('.date('Y-m-d').')';
    echo ExportMenu::widget([
        'dataProvider' => $dataProvider,
        'fontAwesome' => true,
        'columns' => $columns,
        // 'pagination' => false, 
        'options' => ['id'=>'expMenu1'], // optional to set but must be unique
        'target' => ExportMenu::TARGET_BLANK,
        'filename' => $file_name,
    ]);

?>
</div>



<div class="dv-users-index">
    <table class="table table-striped">
            <thead>
            <tr><th>#</th>
                <th>
                    <?php $sort_order = Yii::$app->request->get('order');
                     if($sort_order == 'asc'){
                        echo '<a data-toggle="tooltip" data-placement="top" title="Sort Date by Ascending Order" href="sort?order=desc">Registration Date<i class="fa fa-sort-asc" aria-hidden="true"></i></a>';
                     } elseif($sort_order == 'desc'){
                        echo '<a data-toggle="tooltip" data-placement="top" title="Sort Date by Descending Order" href="sort?order=asc">Registration Date<i class="fa fa-sort-desc" aria-hidden="true"></i></a>';
                     } else {
                        echo '<a data-toggle="tooltip" data-placement="top" title="Sort Date by Ascending Order" href="sort?order=desc">Registration Date<i class="fa fa-sort"></i></a>';
                     } ?>
                 </th>
                <th>Sales Consultant</th>
                <th>Name</th>
                <th>Email</th>
                <th>Program Coordinator</th> 
                <th>Course</th>
                <th>Batch Date</th>
                <th>Modules Allowed</th> 
                <th>Participantion Status</th> 
                <th>Payment Status</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $cnt = $count;
            foreach($participant_users as $user){

                if( $user->participant_status == 1){
                    $status = 'Active';
                } else {
                    $status = 'In-active';
                }
                $date = date("d-m-Y",strtotime($user->created_on));
                // Get sales name
                $sales_id = $user->sales_user_id;                
                $sales = DvUsers::find()->where(['id'=>$sales_id])->all();
                $sales_name1 = array_values(ArrayHelper::map($sales, 'id', 'first_name'));
                $sales_name2 = array_values(ArrayHelper::map($sales, 'id', 'last_name'));
                $sales_name = $sales_name1[0]." ".$sales_name2[0];
                
                $username = $user->first_name . ' ' . $user->last_name; 

                $course = DvCourse::find()->where(['id'=>$user->course])->all();
                $Ucourse = ArrayHelper::map($course, 'id', 'name');
                $u_course = array_values($Ucourse);
                
                $participant_payment_status =  $user->participant_payment_status;

                $pp_status = "";
                if($participant_payment_status == 1){
                    $pp_status = "<span class='label label-warning'>Payment Due</span>";
                }elseif($participant_payment_status == 2){
                    $pp_status = "<span class='label label-danger'>Refund</span>";
                }elseif($participant_payment_status == 3){
                    $pp_status = "<span class='label label-success'>Completed</span>";
                }elseif($participant_payment_status == 4){
                    $pp_status = "<span class='label label-default'>NA</span>";
                }

                $p_status =  $user->participant_status;

                if($user->program_coordinator!=""){
                    $program_coordinatior = $user->program_coordinator;
                }else{
                    $program_coordinatior = "NA";
                }

                echo '<tr>
                <td> <a class="btn btn-xs btn-info" data-toggle="tooltip" data-placement="top" title="View Detail" href="view?id='.$user->id.'"><strong>' . $cnt . '</strong></a> </td>
                <td>' . $date . '</td>
                <td>'.$sales_name.'</td>'; ?>
                <?php echo '<td>' .$username. '</td>
                <td>' . $user->email . '</td>       
                <td>' . $program_coordinatior . '</td>         
                <td>' . $u_course[0] . ' </td>
                <td> ' . $user->course_batch_date . '</td>';

                if($user->course == 1){
                    $total_module = 6;
                }else if($user->course == 2){
                    $total_module = 5;
                }else{
                    $total_module = 1;
                }
                ?>
                <td> 
                <?php if($total_module != 1) { ?>
                     <select name="t_modules_allowed" id="t_modules_allowed" class="t_modules_allowed" data-id="<?= $user->id ?>" data-oldvalue="<?= $user->modules_allowed ?>">
                        <?php for($i=1;$i<=$total_module;$i++){ ?>
                            <option value="<?= $i ?>" <?= ($user->modules_allowed == $i)?'selected':''; ?>><?= $i ?></option>
                        <?php } ?>
                    <?= $p_status ?>
                    </select>
                <?php } else {
                    echo $total_module;
                }?>
                </td>

                <td> 
                    <select name="p_status" id="p_status" class="p_status" data-id="<?= $user->id ?>" data-oldvalue="<?= $p_status ?>">
                        <option value="1" <?= ($p_status == '1')?'selected':''; ?>>Active</option>
                        <option value="2" <?= ($p_status == '2')?'selected':''; ?>>On Hold</option>
                        <option value="3" <?= ($p_status == '3')?'selected':''; ?>>Drop off</option>
                        <option value="4" <?= ($p_status == '4')?'selected':''; ?>>Completed</option>
                    <?= $p_status ?>
                    </select>
                </td>
                <?php echo '<td>' . $pp_status . '</td>
                </tr>';
                $cnt++;
            } 
            if(count($participant_users) <= 0){
                echo '<tr><td colspan="15"><center> <h3>No Record Found</h3> </center></td> </tr>';
                $total_records = '';
            } ?>
        </table>
        
        <?php // display pagination
            echo LinkPager::widget(['pagination' => $pages]); ?>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.28.4/dist/sweetalert2.all.min.js"></script>

<script type="text/javascript">
    $(document).ready(function(){

        $('.datepicker_se').datepicker({dateFormat:'dd-mm-yy'});

        $("#by_date_month").change(function(){
            if($(this).val() == ''){
                $(".select_by_date").addClass('hide');
                $(".select_by_month").addClass('hide');
                $(".select_by_date input").removeAttr('required');
                $(".select_by_month input").removeAttr('required');
            }
            if($(this).val() == 'd'){
                $(".select_by_date input").prop('required',true);
            }
        });

        // change participant status
        $(".p_status").change(function(){
            var participant_id = $(this).attr("data-id");
            var status_id = $(this).val();
            var oldvalue = $(this).attr("data-oldvalue");
            var url = window.location.href;
            
            swal({
              title: 'Are you sure?',
              text: "You want to change participant status!",
              type: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Yes, Change it!'
            }).then((result) => {
              if (result.value) {
                $.ajax({
                    url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-registration/update_participant_payment_ajex') ?>',
                    type: 'POST',
                    data: { participant_id: participant_id, status_id: status_id ,url: url},
                    success: function(data){
                        if(data == '1'){
                            swal(
                              'Updated!',
                              'Participant record status updated.',
                              'success'
                            )
                        }
                    }
                });
              }else{
                $(this).val(oldvalue);
              }
            })           
        });


        // Change allowed moduales
        $(".t_modules_allowed").change(function(){
            var participant_id = $(this).attr("data-id");
            var oldvalue = $(this).attr("data-oldvalue");
            var allowed_module = $(this).val();
            
            swal({
              title: 'Are you sure?',
              text: "You want to change total allowed moduals for this participant?",
              type: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Yes, Change it!'
            }).then((result) => {
              if (result.value) {
                $.ajax({
                    url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-registration/update_participant_allowed_module_ajex') ?>',
                    type: 'POST',
                    data: { participant_id: participant_id, allowed_module: allowed_module },
                    success: function(data){
                        if(data == '1'){
                            swal(
                              'Updated!',
                              'Participant total allowed modules updated.',
                              'success'
                            )
                        }
                    }
                });
              }else{
                $(this).val(oldvalue);
              }
            })           
        });
        
        // For get executive from team id.
        $("#byTeam").change(function(){
            var teamID = $(this).val();
            $.ajax({
                url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-reports/get_executive') ?>',
                type: 'POST',
                data: { teamID : teamID },
                success: function(data){
                    $(".cls_byExecutive").remove();
                    if(data){
                        $('.select_by_team.by_executive').append(data);
                    }
                }
            });
        });
    });
</script>