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
/* $user_department = $user->department; // 1 - sales department */
$user_role = $usermeta_result->meta_value; /*  2 - Executive role */


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
$by_email = '';
$filter_course = array();


if(!isset($filter_data)){
    $filter_data = array();
}
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
}



?>
<div class="dv-users-index allparticipant_list">
    <?php $form = ActiveForm::begin(['id' => 'module-search-form', 'method' => 'get', 'action' => Url::to(['dv-registration/filter'])]); 

        /*  Select Course */
        $select = 'selected="selected"';
        $course = DvCourse::find()->where(['status'=>1])->all();
        $Dv_course = ArrayHelper::map($course, 'id', 'name');
        // unset($Dv_course[1]); unset($Dv_course[2]);

        echo '<div class="form-group col-md-2">';
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
        <div class="form-group col-md-3 sr-only">
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
        if($user_role == 1){
        ?>
        <!-- Search by Sales executive Name -->
        <div class="form-group col-md-3">
            <select class="form-control" name="sales_user_id">
                 <?php if($filter_sales_user_id == ''){
                    echo '<option value="" selected="selected" >Select Sales Consultant</option>';
                } else {
                    echo '<option value="" >Select Sales Consultant</option>';
                } ?>
                <?php $connection = Yii::$app->getDb();
                    $command = $connection->createCommand("select id, first_name, last_name, course from assist_users where status = 1 AND department = 1");
                    $enrolled_users_arr = $command->queryAll();
                  
                    if (!empty($enrolled_users_arr)){
                        foreach ($enrolled_users_arr as $enroll_user){
                            $name = $enroll_user['first_name'].' '.$enroll_user['last_name'];
                            $id = $enroll_user['id'];
                            
                    echo '<option ';
                     if($filter_sales_user_id == $id){
                        echo $select;
                    } 
                    echo ' value="'.$id.'">'. $name .'</option>';
                        }
                    } ?>
            </select>
        </div>
        <?php } ?>

        <div class="form-group col-md-4">
            <input type="text" value="<?= isset($by_email)?$by_email:'' ?>" class="form-control" name="email" placeholder="Enter Email" autocomplete="off">
        </div>

        <div class="form-group col-md-4">
        <?= Html::submitButton( '<i class="fa fa-search"></i> Search' , ['class' => 'btn btn-sm pull btn-success search_submit']) ?>
        <?= Html::resetButton( '<i class="fa fa-refresh"></i> Reset' , ['class' => 'btn btn-sm pull btn-warning search_submit']) ?>
        <div class="clear"></div>
        </div>
    <?php ActiveForm::end(); ?>
</div>

<?php if( $user_role == 1 ) { ?>
<div class="form-group col-md-12">
    <h4>Export Participant Data</h4>
    <?php
    /*  creation of array for excel exporet */
    $exl_array = array();
    $export_count = 1;
    foreach($all_query as $user){

        if( $user->participant_status == 1){
            $status = 'Active';
        } else {
            $status = 'In-active';
        }
        $date = date("d-m-Y",strtotime($user->created_on));
        /*  Get sales name */
        $sales_id = $user->sales_user_id;                
        $sales = DvUsers::find()->where(['id'=>$sales_id])->all();

        $sales_name = '';
        if($sales){
            $sales_name1 = array_values(ArrayHelper::map($sales, 'id', 'first_name'));
            $sales_name2 = array_values(ArrayHelper::map($sales, 'id', 'last_name'));
            $sales_name = $sales_name1[0]." ".$sales_name2[0];
        }
        
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
            'Batch Date' => $user->course_batch,
            'Total Modules Allowed' => $user->modules_allowed,
            'Participantion Status' => $p_status,
            'Payment Status' => $pp_status,
        );
        $export_count = $export_count+1;
    }

    $excel_array = array('allModels' => $exl_array ,'pagination'=>false);
    $dataProvider = new ArrayDataProvider($excel_array);
    /*  creation of array for export as excel */
    $columns = array('id', 'Date', 'Sales Name','Username','Email','Program Coordinator','Course','Batch Date','Total Modules Allowed','Participantion Status','Payment Status');
      

    $file_name = 'Participant Details('.date('Y-m-d').')';
    echo ExportMenu::widget([
        'dataProvider' => $dataProvider,
        'fontAwesome' => true,
        'columns' => $columns,
        'options' => ['id'=>'expMenu1'], /*  optional to set but must be unique */
        'target' => ExportMenu::TARGET_BLANK,
        'filename' => $file_name
    ]);

?>
</div>

<?php } ?>

<div class="dv-users-index table-responsive allparticipant_list">
    <table class="table table-striped">
            <thead>
            <tr>
                <th>#</th>
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
                 <?php if( $user_role == 1 ) { ?>
                    <th>Sales Consultant</th>
                <?php } ?>
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

                $total_invoice_amt = 0;
                if( $user->participant_status == 1){
                    $status = 'Active';
                } else {
                    $status = 'In-active';
                }

                $qb_customer_id = $user->qb_customer_id;
                $allInvoices = $dataService->Query("SELECT * FROM Invoice where CustomerRef='" . $qb_customer_id . "' MAXRESULTS 1000");
                $all_invoice_of_customer = $allInvoices;

                if (!empty($all_invoice_of_customer)) {
                    foreach ($all_invoice_of_customer as $invoice) {
                        if ($user->qb_customer_id == $invoice->CustomerRef) {
                            $total_invoice_amt += $invoice->TotalAmt;
                        }
                    }
                }
                
                $date = date("d-m-Y",strtotime($user->created_on));
                // Get sales name
                $sales_id = $user->sales_user_id;                
                $sales = DvUsers::find()->where(['id'=>$sales_id])->all();
                $sales_name = '';
                if($sales){
                    $sales_name1 = array_values(ArrayHelper::map($sales, 'id', 'first_name'));
                    $sales_name2 = array_values(ArrayHelper::map($sales, 'id', 'last_name'));
                    $sales_name = $sales_name1[0]." ".$sales_name2[0];
                }
                
                $username = $user->first_name . ' ' . $user->last_name; 

                
                $course = DvCourse::find()->where(['id'=>$user->course])->all();
                $Ucourse = ArrayHelper::map($course, 'id', 'name');
                $u_course = array_values($Ucourse);

                $total_invoice_amt = 0;
                if (!empty($all_invoice_of_customer)) {
                    foreach ($all_invoice_of_customer as $invoice) {
                        if ($user->qb_customer_id == $invoice->CustomerRef) {
                            $total_invoice_amt += $invoice->TotalAmt;
                        }
                    }
                }
                
                $participant_payment_status =  $user->participant_payment_status;
                $total_applied_amt = 0;
                $total_received_amount = 0;
                $total_refunded_amount = 0;

                if (!empty($all_payments)) {
                    foreach ($all_payments as $payment) {
                        if($payment->CustomerRef == $user->qb_customer_id) {
                            $total_amt = $payment->TotalAmt;
                            if ($payment->UnappliedAmt == 0) {
                                $total_applied_amt += $total_amt;
                            }
                            $total_received_amount += ($payment->TotalAmt - $payment->UnappliedAmt);
                        }
                    }
               }

               if (!empty($all_credit_memos)) {
                   foreach ($all_credit_memos as $credit_memo) {
                       if($credit_memo->CustomerRef == $user->qb_customer_id) {
                           $remaining_credit = $credit_memo->RemainingCredit;
                            $total_refun_amt = $credit_memo->TotalAmt;
                            $total_applied_amt = $total_applied_amt + ($total_refun_amt - $remaining_credit);
                            
                            $total_amt = $credit_memo->TotalAmt;
                            $total_refunded_amount = $total_refunded_amount + ($total_amt - $remaining_credit);
                       }
                   }
               }
               
               if ((int) ($total_received_amount + $total_refunded_amount) >= (int) (($total_invoice_amt - 1 - $total_refunded_amount))) {
               //if($total_applied_amt >= $total_invoice_amt) {
                   $pp_status = "<span class='label label-success'>Completed</span>
                       <span class='sr-only total_received_amount'>$total_received_amount</span>
                           <span class='sr-only total_refunded_amount'>$total_refunded_amount</span>
                           <span class='sr-only total_invoice_amt'>$total_invoice_amt</span>";
               } else {
                   $pp_status = "<span class='label label-warning'>Payment Due</span>
                       <span class='sr-only total_received_amount'>$total_received_amount</span>
                           <span class='sr-only total_refunded_amount'>$total_refunded_amount</span>
                           <span class='sr-only total_invoice_amt'>$total_invoice_amt</span>";
               }

               $p_status =  $user->participant_status;

               if ($user->program_coordinator!="") {
                   $program_coordinatior = $user->program_coordinator;
               } else {
                   $program_coordinatior = "NA";
               }

               echo '<tr>
                <td> <a class="btn btn-xs btn-info" data-toggle="tooltip" data-placement="top" title="View Detail" href="view?id='.$user->id.'"><strong>' . $cnt . '</strong></a> </td>
                <td>' . $date . '</td>'; ?>
                <?php if( $user_role == 1 ) { echo '<td>'.$sales_name.'</td>'; } ?> 
                <?php echo '<td>' .$username. '</td>
                <td>' . $user->email . '</td>
                <td>' . $program_coordinatior . '</td>
                <td>' . $u_course[0] . ' </td>
                <td style="width:50px">'. date("d-m-Y",strtotime($user->course_batch_date)).'</td>';
                // <td> ' . $total_invoice_amt . '</td>'; 

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
                     <select name="t_modules_allowed" id="t_modules_allowed" class="t_modules_allowed" data-id="<?= $user->id ?>" data-token_id = "<?= $user->token_id ?>" data-oldvalue="<?= $user->modules_allowed ?>">
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
                   <select name="p_status" id="p_status" class="p_status" data-id="<?= $user->id ?>" data-token_id = "<?= $user->token_id ?>" data-oldvalue="<?= $p_status ?>">
                        <option value="1" <?= ($p_status == '1')?'selected':''; ?>>Active</option>
                        <option value="2" <?= ($p_status == '2')?'selected':''; ?>>On Hold</option>
                        <option value="3" <?= ($p_status == '3')?'selected':''; ?>>Drop off</option>
                        <option value="4" <?= ($p_status == '4')?'selected':''; ?>>Completed</option>
                    <?= $p_status ?>
                    </select>
                </td>
                <?php echo '<td>' . $pp_status . '</td>';
                echo "</tr>";
                $cnt++;
            } 
            if(count($participant_users) <= 0){
                echo '<tr><td colspan="15"><center> <h3>No Record Found</h3> </center></td> </tr>';
                $total_records = '';
            }
        ?>
         </tbody>
        </table>
        <?php 
            //  display pagination 
            echo LinkPager::widget(['pagination' => $pages]); ?>
</div> 



<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.28.4/dist/sweetalert2.all.min.js"></script>

<script type="text/javascript">
    // var script_url = "https://script.google.com/macros/s/AKfycbxiHZGClxKKOdjO-u1IM8mB-5hMBSkCoTWzcpdzHykG96kfOwdZ/exec";

    /* Get Published URL from script APP */
    var environment = "<?= Yii::$app->params['environment'] ?>"; // check server enviroment
    
    if(environment == 'Production'){
        // live
        var script_url = "https://script.google.com/macros/s/AKfycbzFgSakTaaTreJH62j19QRQFqxek29rzbbrDChEJYnajkxQw2s/exec"; // Live
    } else {
        // development
        var script_url = "https://script.google.com/macros/s/AKfycbxiHZGClxKKOdjO-u1IM8mB-5hMBSkCoTWzcpdzHykG96kfOwdZ/exec"; // my
    }

    $(document).ready(function(){

        $('.datepicker_se').datepicker({dateFormat:'dd-mm-yy'});

        $("#by_date_month").change(function(){
            if($(this).val() == ''){
                $(".select_by_date").addClass('hide');
                $(".select_by_month").addClass('hide');
                $(".select_by_date input").removeAttr('required');
                $(".select_by_month select").removeAttr('required');
            }
            if($(this).val() == 'd'){
                $(".select_by_date input").prop('required',true);
                $(".select_by_month select").removeAttr('required');
            }
            if($(this).val() == 'm'){
                $(".select_by_month select").prop('required',true);
                $(".select_by_date input").removeAttr('required');
            }
        });

        /*  change participant status */
        $(".p_status").change(function(){
            var participant_id = $(this).attr("data-id");
            var status_id = $(this).val();
            var participant_status = '';
            if(status_id == 1){
                participant_status = 'Active';
            }else if(status_id == 2){
                participant_status = 'On Hold';
            }else if(status_id == 3){
                participant_status = 'Drop off';
            }else if(status_id == 4){
                participant_status = 'Completed';
            }

            var oldvalue = $(this).attr("data-oldvalue");
            var url = window.location.href;
            var token_id = $(this).attr("data-token_id");

            
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
                $("#loading_custom").show();
                $.ajax({
                    url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-registration/update_participant_payment_ajex') ?>',
                    type: 'POST',
                    data: { participant_id: participant_id, status_id: status_id ,url:url },
                    success: function(data){
                        if(data == '1'){

                            var url = script_url+"?callback=updatectrlq&token_id="+token_id+"&participant_status="+participant_status+"&action=update";

                            var request = jQuery.ajax({
                                crossDomain: true,
                                url: url ,
                                method: "GET",
                                async: false,
                                dataType: "jsonp"
                            });
                            /*swal(
                              'Updated!',
                              'Participant record status updated.',
                              'success'
                            )*/
                        }
                    }
                });
              }else{
                $(this).val(oldvalue);
              }
            })           
        });


        /*  Change allowed moduales */
        $(".t_modules_allowed").change(function(){
            var participant_id = $(this).attr("data-id");
            var oldvalue = $(this).attr("data-oldvalue");
            var allowed_module = $(this).val();
            var url = window.location.href;
            var token_id = $(this).attr("data-token_id");
            
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
                $("#loading_custom").show();
                $.ajax({
                    url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-registration/update_participant_allowed_module_ajex') ?>',
                    type: 'POST',
                    data: { participant_id: participant_id, allowed_module: allowed_module, url:url },
                    success: function(data){
                        if(data == '1'){

                            var url = script_url+"?callback=updatectrlq&token_id="+token_id+"&allowed_module="+allowed_module+"&action=update";

                            var request = jQuery.ajax({
                                crossDomain: true,
                                url: url ,
                                method: "GET",
                                async: false,
                                dataType: "jsonp"
                            });

                            /*swal(
                              'Updated!',
                              'Participant total allowed modules updated.',
                              'success'
                            )*/
                        }
                    }
                });
              }else{
                $(this).val(oldvalue);
              }
            });           
        });
        
    });

    function updatectrlq(e){
        $("#loading_custom").hide();
         console.log(e.result);
         if(e.result == "Token ID not found"){
            swal(
              'Updated Fail On Sheet!',
              'Record Not Updated On Sheet.',
              'error'
            );
         }else{
            swal(
              'Updated!',
              'Record Updated successfully.',
              'success'
            );
        }
    }
</script>