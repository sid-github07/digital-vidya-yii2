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
use app\models\DvParticipantPayments;
use app\models\DvParticipantPaymentMeta;
use kartik\export\ExportMenu;
use yii\data\ArrayDataProvider;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$user = Yii::$app->user->identity;
$usermeta_result = DvUserMeta::find()->where(['uid' => $user->id, 'meta_key' => 'role'])->one();
$user_role = $usermeta_result->meta_value;

$head_title = "";
if (isset($filter_data['sdate'])) {
    $this->title = 'Pending Revenue : ' . $filter_data['sdate'] . " to " . $filter_data['edate'];
    $head_title = $filter_data['sdate'] . " to " . $filter_data['edate'];
} else if (isset($filter_data['bymonth'])) {
    $new_date = explode('_', $filter_data['bymonth']);
    $fyear = $new_date['1'];
    $fmonth = $new_date['0'];
    $dateObj = DateTime::createFromFormat('!m', $fmonth);
    $monthName = $dateObj->format('M'); // March
    $this->title = 'Pending Revenue : ' . $monthName . "," . $fyear;
    $head_title = $monthName . "," . $fyear;
} else {
    $this->title = 'Pending Revenue : ' . date('M,Y');
    $head_title = date('M,Y');
}
$this->params['breadcrumbs'][] = $this->title;
?>
<!-- Searching functionality -->
<?php
$bymonth = '';
$by_date_month = '';
$sdate = '';
$edate = '';
$filter_sales_user_id = array();
$byTeam = '';
$by_email = '';

if (isset($filter_data['bymonth'])) {
    $bymonth = $filter_data['bymonth'];
}
if (isset($filter_data['by_date_month'])) {
    $by_date_month = $filter_data['by_date_month'];
}
if (isset($filter_data['sdate'])) {
    $sdate = $filter_data['sdate'];
    $edate = $filter_data['edate'];
}
if (isset($filter_data['email'])) {
    $by_email = $filter_data['email'];
}
if (isset($by_team_arr['sales_user_id'])) {
    $filter_sales_user_id = $by_team_arr['sales_user_id'];
}
if (isset($by_team_arr['byTeam'])) {
    $byTeam = $by_team_arr['byTeam'];
}
?>
<div class="dv-pending_revenue inner_content">
    <?php
    $form = ActiveForm::begin(['id' => 'module-search-form', 'method' => 'post', 'action' => Url::to(['dv-registration/pending_revenue'])]);
    $select = 'selected="selected"';
    ?>
    <div class="Search-section">
        <!-- // filter by Month/Date -->
        <div class="form-group col-md-3">
            <select class="form-control" name="by_date_month" id="by_date_month">

                <?php
                if ($by_date_month == '') {
                    echo '<option value="" selected="selected" >Select Date/Month</option>';
                } else {
                    echo '<option value="">Select Date/Month </option>';
                }
                ?>
                <option <?php
                if ($by_date_month == 'd') {
                    echo $select;
                }
                ?> value="d">Search by Date</option>
                <option <?php
                if ($by_date_month == 'm') {
                    echo $select;
                }
                ?> value="m">Search by Month</option>
            </select>
        </div>

        <!-- // filter by month -->
        <div class="form-group col-md-2 select_by_month <?php
        if ($bymonth == '') {
            echo 'hide';
        }
        ?>">
            <select class="form-control select_by_month" name="bymonth">
                <option value="">Select Month</option>
                <?php
                foreach ($month_year_arr as $key => $value) {
                    echo '<option ';
                    if ($bymonth == $key) {
                        echo $select;
                    }
                    echo ' value="' . $key . '">' . $value . '</option>';
                }
                ?>
            </select>
        </div>


        <!-- // Search by date -->
        <div class="form-group col-md-3 select_by_date <?= ($sdate == '') ? 'hide' : ''; ?>">
            <input type="text" value="<?= $sdate ?>" id="sdate" class="datepicker_se form-control" name="sdate" placeholder="Select Start Date" autocomplete="off">   
        </div>

        <div class="form-group col-md-3 select_by_date <?= ($edate == '') ? 'hide' : ''; ?>">
            <input type="text" value="<?= $edate ?>" id="edate" class="datepicker_se form-control" name="edate" placeholder="Select End Date" autocomplete="off">
        </div>



        <?php
        if ($user_role == 7) {
            ?>
            <!-- // filter by Team -->
            <div class="form-group select_by_team by_executive">
                <div class="form-group col-md-3">
                    <select class="form-control select_by_team" name="byTeam" id="byTeam">
                        <option value="">Select Team</option>
                        <?php
                        $team_model_val = Yii::$app->db->createCommand("SELECT assist_user_meta.* , assist_users.*  FROM assist_users join assist_user_meta ON assist_users.id = assist_user_meta.uid WHERE assist_users.department = 1 and assist_user_meta.meta_key = 'team' and assist_user_meta.meta_value = '' ")->queryAll();


                        foreach ($team_model_val as $team_value) {
                            if ($team_value['id'] == $user->id) {
                                echo '<option ';
                                if ($byTeam == $team_value['id']) {
                                    echo $select;
                                }
                                echo ' value="' . $team_value['id'] . '" is_head="1">My</option>';
                            }
                        }

                        foreach ($team_model_val as $team_value) {
                            if ($team_value['id'] != $user->id) {
                                echo '<option ';
                                if ($byTeam == $team_value['id']) {
                                    echo $select;
                                }
                                echo ' value="' . $team_value['id'] . '">' . $team_value['first_name'] . " " . $team_value['last_name'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <?php
                if ($byTeam != '' && $byTeam != $user->id) {
                    $usermeta_result = DvUserMeta::find()->where(['meta_key' => 'team', 'meta_value' => $byTeam])->all();
                    if ($usermeta_result) {
                        echo "<div class='form-group col-md-3 cls_byExecutive'>
                            <select class='form-control' name='sales_user_id[]' id='byExecutive' multiple>
                              <option value=''>Select Consultant</option>";
                        foreach ($usermeta_result as $val) {
                            $usermeta_result = DvUsers::findOne($val['uid']);
                            echo "<option value='" . $val['uid'] . "'";
                            if (in_array($val['uid'], $filter_sales_user_id)) {
                                echo $select;
                            }
                            echo ">" . $usermeta_result->first_name . ' ' . $usermeta_result->last_name . "</option>";
                        }
                        echo "</select></div>";
                    } else {
                        echo "<div class='form-group col-md-3 cls_byExecutive'><select class='form-control col-md-3' name='sales_user_id[]' id='byExecutive' multiple>
                                  <option value=''>Select Consultant</option>";
                        echo "</select></div>";
                    }
                }
                ?>
            </div>
        <?php } ?>


        <?php
        if ($user_role == 6) {
            ?>
            <!-- Search by Sales executive Name -->
            <div class="form-group col-md-3">
                <select class="form-control" name="sales_user_id">

                    <option value="<?= $user->id ?>">Me</option>

                    <?php
                    $user_res = Yii::$app->user->identity;
                    $usermeta_result = DvUserMeta::find()->where(['uid' => $user_res->id, 'meta_key' => 'team'])->one();
                    $team_id = $usermeta_result->meta_value;

                    $connection = Yii::$app->getDb();
                    $command = $connection->createCommand("SELECT assist_users.id, assist_users.first_name, assist_users.last_name, assist_users.course, assist_user_meta.meta_key, assist_user_meta.meta_value FROM assist_users INNER JOIN assist_user_meta ON assist_users.id = assist_user_meta.uid WHERE assist_users.status = 1 AND assist_users.department = 1 AND assist_user_meta.meta_key = 'team' AND assist_user_meta.meta_value=$user_res->id");


                    $enrolled_users_arr = $command->queryAll();

                    if (!empty($enrolled_users_arr)) {
                        foreach ($enrolled_users_arr as $enroll_user) {
                            if ($enroll_user['id'] != $user_res->id) {
                                $name = $enroll_user['first_name'] . ' ' . $enroll_user['last_name'];
                                $id = $enroll_user['id'];

                                echo '<option ';
                                if ($filter_sales_user_id == $id) {
                                    echo $select;
                                }
                                echo ' value="' . $id . '">' . $name . '</option>';
                            }
                        }
                    }
                    ?>
                </select>
            </div>
        <?php } ?>

        <div class="form-group col-md-3">
            <input type="text" value="<?= isset($by_email) ? $by_email : '' ?>" class="form-control" name="email" placeholder="Enter Email" autocomplete="off">
        </div>

        <div class="form-group col-md-4">
            <?= Html::submitButton('<i class="fa fa-search"></i> Search', ['class' => 'btn btn-success']) ?>
            <?= Html::a('<i class="fa fa-times"></i> Reset', ['pending_revenue'], ['class' => 'btn back_button btn-danger cancel_button']); ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <div class="g_total_all" style="display: inline-block;padding: 0px 0px 25px;margin:40px auto 0;width:100%">
    </div>

    <?php
    
    if (isset($model)) {
        $timestamp_of_first_date_of_month = strtotime($first_date_of_current_month);
        $timestamp_of_last_date_of_month = strtotime($last_date_of_current_month);
        ?>
        <div class="dv-users-index table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice No.</th>
                        <th>Team</th>
                        <th>Consultant</th>
                        <th>Course</th>
                        <th>Invoice Due Date</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Participation Status</th>
                        <th>Payment Status</th>
                        <!-- <th>Confirmed Amt.</th>
                        <th>Total Rcvd. Payment</th> -->
                        <th>Total Amt. Due<Br>(<?= $head_title ?>)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $cnt = 1;
                    $total_incentive = 0;
                   // $total_amount_due = 0;
                    $currency_ref = "-";
                    $product_name = "-";
                    $invoice_number = "";
                    $total_invoice_due = 0;
                    $timestamp_of_first_date_of_month = strtotime($first_date_of_current_month);
                    $timestamp_of_last_date_of_month = strtotime($last_date_of_current_month);

                    foreach ($model as $value) {

                        $invoice_balance = 0;
                        $total_invoice_amt = 0;
                        $total_received_payment = 0;
                        $unapplied_amt_payment = 0;
                        $total_received_payment2 = 0;

                        /* Difference between "Invoice Due amount and Payment Received" in any month or two date period. */
                        $invoice_due_in_this_period = 0;

                        $cnt_invoice = 0;
                        if (!empty($all_invoice_of_customer)) {
                            foreach ($all_invoice_of_customer as $invoice) {
                                if ($value->qb_customer_id == $invoice->CustomerRef) {
                                    $timestamp_due_date = strtotime($invoice->DueDate);
                                    if ($timestamp_due_date >= $timestamp_of_first_date_of_month && $timestamp_due_date <= $timestamp_of_last_date_of_month) {
                                        $total_invoice_amt += $invoice->TotalAmt;
                                        $invoice_number .= $invoice->DocNumber . ", ";
                                    }
                                }
                            }
                        }
                        $invoice_number = rtrim($invoice_number, ", ");
                        if (!empty($allInvoices)) {
                            foreach ($allInvoices as $invoice) {
                                if ($value->qb_customer_id == $invoice->CustomerRef) {
                                    $currency_ref = $invoice->CurrencyRef;
                                    $invoice_balance += $invoice->Balance;
                                    if ($cnt_invoice == 0) {
                                        $next_installment_date = $invoice->DueDate;
                                        
                                        if (!empty($courses_from_qb)) {

                                            foreach ($courses_from_qb as $course) {
                                                if (!empty($invoice->Line[0]->SalesItemLineDetail->ItemRef)) {
                                                    if ($course->Id == $invoice->Line[0]->SalesItemLineDetail->ItemRef) {
                                                        $product_name = $course->Name;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $cnt_invoice++;
                                    
                                }
                            }
                        }
                        
                        $total_invoice_due += $invoice_balance;
                        if (!empty($allPayments)) {
                            foreach ($allPayments as $payment) {
                                if ($value->qb_customer_id == $payment->CustomerRef) {
                                    $payment_timestamp = strtotime($payment->TxnDate);
                                    if ($timestamp_of_first_date_of_month <= $payment_timestamp && $timestamp_of_last_date_of_month >= $payment_timestamp) {
                                        $total_received_payment2 += $payment->TotalAmt;
                                        $total_received_payment += ($payment->TotalAmt - $payment->UnappliedAmt);
                                        $unapplied_amt_payment += $payment->UnappliedAmt;
                                    }
                                }
                            }
                        }

                        $invoice_due_in_this_period = $invoice_balance - $total_received_payment;


                        if ($invoice_due_in_this_period <= 0) {
                            $invoice_due_in_this_period = 0;
                        }
                        if ($invoice_balance <= 0) {
                            $payment_status = "<span class='label label-success'>Completed</span>";
                        } else {
                            $payment_status = "<span class='label label-warning'>Payment Due</span>";
                        }

                        $date = date('d-m-Y', strtotime($value['created_on']));

                        $customer_name = $value['first_name'] . ' ' . $value['last_name'];

                        $email = $value['email'];

                        $total_amount = $value['total_confirmed_amount'];

                        $participant_id = $value['id'];

                        // $result = DvParticipantPayments::find()->where(['participant_id' => $participant_id])->all();

                        // $received_amount = 0;
                        // $not_confirmed = 0;
                        // $payment_number = 1;
                        /*if ($result) {
                            foreach ($result as $res_val) {
                                if ($res_val->amount_confirmed == 1) {
                                    $received_amount += $res_val->amount_recieved;
                                } else {
                                    $not_confirmed += $res_val->amount_recieved;
                                }
                                if ($payment_number < $res_val->payment_number) {
                                    $payment_number = $res_val->payment_number;
                                }
                            }
                        }*/

                        // $amount_due = $total_amount - $received_amount;
                       // $total_amount_due = $total_amount_due + $invoice_balance;
                        // $isDisplay = $total_amount - $received_amount;

                       /* $participant_payment_status = $value['participant_payment_status'];
                        $pp_status = "";
                        if ($participant_payment_status == 1) {
                            $pp_status = "<span class='label label-warning'>Payment Due</span>";
                        } elseif ($participant_payment_status == 2) {
                            $pp_status = "<span class='label label-danger'>Refund</span>";
                        } elseif ($participant_payment_status == 3) {
                            $pp_status = "<span class='label label-success'>Completed</span>";
                        } elseif ($participant_payment_status == 4) {
                            $pp_status = "<span class='label label-default'>NA</span>";
                        }*/

                        $p_status = '';
                        $participant_status = $value['participant_status'];
                        if ($participant_status == 1) {
                            $p_status = "<span class='label label-primary'>Active</span>";
                        } else if ($participant_status == 2) {
                            $p_status = "<span class='label label-warning on_hold_lbl'>On Hold</span>";
                        } else if ($participant_status == 3) {
                            $p_status = "<span class='label label-danger'>Drop off</span>";
                        } else if ($participant_status == 4) {
                            $p_status = "<span class='label label-success'>completed</span>";
                        }

                        if($total_received_payment != 0){
                            $total_received_payment = $total_received_payment." ".$currency_ref;
                        } 

                        // Get Executive/consultant's manager name
                        $manager_id = $value->sales_user_id;
                        $sales_manager_id = DvUserMeta::find()->where(['uid'=>$manager_id,'meta_key'=>'team'])->one();
                        
                        if($sales_manager_id->meta_value == ''){
                            $manager_id = $manager_id;
                        }else{
                            $manager_id = $sales_manager_id->meta_value;
                        }

                        $manager_result = DvUsers::find()->where(['id'=>$manager_id])->all();
                        $fname = array_values(ArrayHelper::map($manager_result, 'id', 'first_name'));
                        $lname = array_values(ArrayHelper::map($manager_result, 'id', 'last_name'));
                        $sales_manager_name = $fname[0]." ".$lname[0];
                        

                        // Get Executive/consultant name
                        $sales = DvUsers::find()->where(['id'=>$value->sales_user_id])->all();
                        $sales_name1 = array_values(ArrayHelper::map($sales, 'id', 'first_name'));
                        $sales_name2 = array_values(ArrayHelper::map($sales, 'id', 'last_name'));
                        $sales_name = $sales_name1[0]." ".$sales_name2[0];

                        /* If total amount & received amount is equal then recould should not display */
                        echo '<tr><td> <a class="btn btn-xs btn-info" data-toggle="tooltip" data-placement="top" title="View Detail"  href="view?id='.$participant_id.'" ><strong>' . $cnt . '</strong> </td>
                    <td>' . $invoice_number . '</td>
                    <td>' .$sales_manager_name.'</td> 
                    <td>' .$sales_name.'</td> 
                    <td>' . $product_name . '</td>
                    <td>' . date("d-m-Y", strtotime($next_installment_date)) . '</td>
                    <td>' . $customer_name . '</td>
                    <td>' . $email . '</td>
                    <td> ' . $p_status . '</td>
                    <td> ' . $payment_status . '</td>
                    <td> ' . $invoice_balance." ".$currency_ref.'</td>
                    </tr>';
                        ?>

                        <?php
                        $cnt++;
                        $invoice_number = "";
                    }

                    if (count($model) <= 0) {
                        echo '<tr><td colspan="12"><center> <h3>No Record Found</h3> </center></td> </tr>';
                    }
                    ?>

                </tbody>
            </table>
            <div class="row paginations">
                <div class="form-group col-md-12" style="text-align: right;">
                    <div class="col-md-12">
                        <?= LinkPager::widget(['pagination' => $pagination]) ?>
                    </div>
                </div>   
            </div>
        </div>
        <?php
    }
    ?>
</div>
<div id="g_total_amount_due" style="display:none">
    <div class="g_total text-center">
        <?php 
            if($total_of_all_currencys){
                foreach($total_of_all_currencys as $key=>$val){
        ?>
             <label>Total Amt.Due :  <?=  number_format($val) ?> <?= $key ?> </label>
        <?php
                }
            } 
        ?>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        var gtotal = $("#g_total_amount_due").html();
        $(".g_total_all").append(gtotal);

        $('.datepicker_se').datepicker({dateFormat: 'dd-mm-yy'});
        $("#by_date_month").change(function() {
            if ($(this).val() == '') {
                $(".select_by_date").addClass('hide');
                $(".select_by_month").addClass('hide');
                $(".select_by_date input").removeAttr('required');
                $(".select_by_month").removeAttr('required');
            }
            if ($(this).val() == 'd') {
                $(".select_by_date input").prop('required', true);

            }
            if ($(this).val() == 'm') {
                $(".select_by_date input").removeAttr('required');
                $(".select_by_month").prop('required', true);
            }
        });

        /* For get executive from team id.*/
        $("#byTeam").change(function() {
            var teamID = $(this).val();
            var is_head = $('option:selected', this).attr('is_head');
            if (typeof is_head !== typeof undefined && is_head !== false) {
                $(".cls_byExecutive").remove();
            } else {
                $.ajax({
                    url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-reports/get_executive') ?>',
                    type: 'POST',
                    data: {teamID: teamID},
                    success: function(data) {
                        $(".cls_byExecutive").remove();
                        if (data) {
                            $('.select_by_team.by_executive').append(data);
                            $('#byExecutive').attr("multiple", "multiple");
                            $('#byExecutive').attr("name", "sales_user_id[]");
                        }
                    }
                });
            }
        });
    });
</script>