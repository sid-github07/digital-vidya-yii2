<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\widgets\DetailView;
use app\models\DvStates;
use app\models\DvCities;
use app\models\DvUsers;
use app\models\DvCountry;
use app\models\DvCourse;
use app\models\DvParticipantModules;
use app\models\DvPaymentMode;
use yii\web\Session;
use yii\helpers\Url;

$user_name = $model->first_name . ' ' . $model->last_name;
$this->title = 'Participant: ' . $user_name;
$this->params['breadcrumbs'][] = ['label' => 'All Registration', 'url' => ['index']];
$this->params['breadcrumbs'][] = $user_name;

$total_received_amount = 0;
$total_invoice_amt = 0;
$total_refunded_amount = 0;

if (!empty($all_invoice_of_customer)) {
    foreach ($all_invoice_of_customer as $invoice) {
        if ($model->qb_customer_id == $invoice->CustomerRef) {
            $total_invoice_amt += $invoice->TotalAmt;
        }
    }
}
if (!empty($all_credit_memos)) {
    foreach ($all_credit_memos as $credit_memo) {
        $remaining_credit = $credit_memo->RemainingCredit;
        $total_amt = $credit_memo->TotalAmt;
        $total_refunded_amount = $total_refunded_amount + ($total_amt - $remaining_credit);
    }
}
?>
<style>
    .field-dvparticipantpayments-payment_reference_number.required.has-error .help-block{display: block !important;}
</style>
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="dv-paricipant-details-view">
                <div class="form-group"><h3 class="blue_color">Participant Profile</h3></div>

                <?=
                DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [ 'label' => 'Name',
                            'value' => function($model) {
                                return $model->first_name . ' ' . $model->last_name;
                            },
                        ],
                        [ 'label' => 'Consultant Name',
                            'value' => function($model) {
                                // Get Executive/consultant name
                                $sales = DvUsers::find()->where(['id' => $model->sales_user_id])->all();
                                $sales_name = '';
                                if($sales){
                                    $sales_name1 = array_values(ArrayHelper::map($sales, 'id', 'first_name'));
                                    $sales_name2 = array_values(ArrayHelper::map($sales, 'id', 'last_name'));
                                    $sales_name = $sales_name1[0] . " " . $sales_name2[0];
                                }
                                return $sales_name;
                            },
                        ],
                        'email:email',
                        [ 'label' => 'Phone Number (Mobile)',
                            'value' => function($model) {
                                return $model->mobile;
                            },
                        ],
                        'remarks',
                        [ 'label' => 'Scholarship offered',
                            'value' => function($model) {
                                if ($model->scholarship_offered == 0) {
                                    return 'No';
                                } else if ($model->scholarship_offered == 1) {
                                    return 'Yes';
                                };
                            },
                        ],
                        [ 'label' => 'Promises Notes',
                            'value' => function($model) {
                                /* if($model->obj_of_running_fields == 1){
                                  return "Fresher, interested in placement";
                                  }else if($model->obj_of_running_fields == 2){
                                  return "Experienced, interested in placement";
                                  }else if($model->obj_of_running_fields == 3){
                                  return "Learning, not interested in placement";
                                  }else if($model->obj_of_running_fields == 4){
                                  return "Entrepreneur, not interested in placement";
                                  } */
                                return $model->promises_notes;
                            },
                        ]
                    ]
                ]);
                ?>

                <div class="form-group"><h3 class="blue_color">Address</h3></div>
                <?=
                DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [ 'label' => 'Address',
                            'value' => function($model) {
                                return $model->address;
                            },
                        ],
                        [ 'label' => 'City',
                            'value' => function($model) {
                                if ($model->city != 0) {
                                    return DvCities::find()->where(['id' => $model->city])->one()->name;
                                };
                            },
                        ],
                        [ 'label' => 'State',
                            'value' => function($model) {
                                if ($model->state != 0) {
                                    return DvStates::find()->where(['id' => $model->state])->one()->name;
                                };
                            },
                        ],
                        [ 'label' => 'Country',
                            'value' => function($model) {
                                if ($model->country != 0) {
                                    return DvCountry::find()->where(['id' => $model->country])->one()->name;
                                };
                            },
                        ]
                    ]
                ]);
                ?>

                <div class="form-group"><h3 class="blue_color">Course</h3></div>

                <?php
                $course = $model->course;

                $module_edit = '';
                if ($course == 1 || $course == 2) {
                    $module_edit = '<a href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="Edit" course-id="' . $model->course . '"  participant-id="' . $model->id . '" data-oldID="' . $model->modules_allowed . '" data-token_id = "' . $model->token_id . '" id="modules_allowed_view"><i class="fa fa-pencil"></i></a>';
                }
                $payment_status_edit = '<a href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="Edit" participant-id="' . $model->id . '" data-oldID="' . $model->participant_status . '" data-token_id = "' . $model->token_id . '" id="participant_status_view"><i class="fa fa-pencil"></i></a>';
                ?>

                <?=
                DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [ 'label' => 'Course',
                            'value' => function($model) {
                                if ($model->course != '') {
                                    return DvCourse::find()->where(['id' => $model->course])->one()->name;
                                    //return $model->course;
                                }
                            },
                        ],
                        [ 'label' => 'Course Format',
                            'value' => function($model) {
                                if ($model->course_format == 1) {
                                    return 'Format 1.0 (Online)';
                                } else if ($model->course_format == 2) {
                                    return 'Format 2.0 (Online)';
                                };
                            },
                        ],
                        [ 'label' => 'Course Batch',
                            'value' => function($model) {
                                /* if($model->course_batch != 0){
                                  $course_batch = DvParticipantModules::find()->where(['id'=>$model->course_batch])->one();
                                  $course_id = $course_batch->course;
                                  $batch_date = $course_batch->start_date;
                                  $course_name = DvCourse::find()->where(['id'=>$course_id])->one()->name;
                                  return $course_name." | ".$batch_date;
                                  }; */
                                return date("d-m-Y", strtotime($model->course_batch_date));
                            },
                        ],
                        [ 'label' => 'Modules Allowed',
                            'value' => function($model) {
                                return $model->modules_allowed;
                            },
                            'contentOptions' => ['class' => 'modules_allowed_value'],
                        ],
                        [ 'label' => 'Modules Completed',
                            'value' => 0,
                        ],
                        [ 'label' => 'Participant Status',
                            'value' => function($model) {
                                if ($model->participant_status == 1) {
                                    return "Active";
                                } else if ($model->participant_status == 2) {
                                    return "On Hold";
                                } else if ($model->participant_status == 3) {
                                    return "Drop Off";
                                } else if ($model->participant_status == 4) {
                                    return "Completed";
                                }
                            },
                            'contentOptions' => ['class' => 'participant_status_value'],
                        ],
                    ]
                ]);
                ?>

                <div class="form-group"><h3 class="blue_color">Payment Details</h3></div>
                <?=
                DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [ 'label' => 'Total Confirmed Amount',
                            'value' => function($model) use($total_invoice_amt, $currency) {
                                return $total_invoice_amt . ' ' . $currency;
                            },
                        ],
                        [ 'label' => 'Full Payment?',
                            'value' => function($model) {
                                if ($model->is_full_payment == 0) {
                                    return 'No';
                                } else if ($model->is_full_payment == 1) {
                                    return 'Yes';
                                };
                            },
                        ],
                        [ 'label' => 'Payment Status',
                            'value' => function($model)use($total_invoice_amt, $all_payments) {
                                $total_applied_amt = 0;
                                if (!empty($all_payments)) {
                                    foreach ($all_payments as $payment) {
                                        $total_amt = $payment->TotalAmt;
                                        if ($payment->UnappliedAmt == 0) {
                                            $total_applied_amt += $total_amt;
                                        }
                                    }
                                }
                                if ($total_applied_amt >= $total_invoice_amt) {
                                    return "Completed";
                                } else {
                                    return "Payment Due";
                                }
                            },
                        ],
                    ]
                ]);
                ?>


                <div class="form-group">
                    <div class="pad-10-0">
                        <h3 class="blue_color" style="display:inline;">Invoice History</h3>
                        <a class="btn btn-primary make_payments" data-id="<?= $model->id ?>"><i data-toggle="tooltip" data-placement="top" title="Request to refund" class="fa fa-pencil"></i> Make Payment </a>
                    </div>
                    <div class="listing">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="bg-gray">
                                <th>S.No</th>
                                <th>Amount</th>
                                <th>Invoice Due Balance</th>
                                <th>Status</th>
                                <th>Invoice Created Date</th>
                                <th>Invoice Due Date</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $cnt = 1;
                                    if (!empty($allInvoices)) {
                                        foreach ($allInvoices as $val) {

                                            $due = $val->TotalAmt - $val->Balance;
                                            $status = "Paid";
                                            if ($val->Balance == $val->TotalAmt) {
                                                $status = "Not Paid";
                                            } else if ($val->Balance > 0) {
                                                $status = "Payment Due";
                                            }
                                            ?>
                                            <tr>
                                                <td><?= $cnt; ?></td>
                                                <td><?= $val->TotalAmt; ?></td>
                                                <td><?= $val->Balance; ?></td>
                                                <td><?= $status; ?></td>
                                                <td><?= date('d-m-Y', strtotime($val->TxnDate)); ?></td>
                                                <td><?= date('d-m-Y', strtotime($val->DueDate)); ?></td>
                                            </tr>
                                            <?php
                                            $cnt++;
                                        }
                                    } else {
                                        ?>
                                        <tR>
                                            <td colspan="6">No Invoices</td>
                                        </tR>
                                    <?php }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <div class="form-group">
                    <div class="pad-10-0">
                        <h3 class="blue_color" style="display:inline;">Payment(s) Completed</h3>
                    </div>
                    <div class="listing">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="bg-gray">
                                <th>S.No</th>
                                <th>Amount</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Payment Created Date</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $cnt = 1;
                                    if (!empty($all_payments)) {
                                        foreach ($all_payments as $payment) {
                                            $total_amt = $payment->TotalAmt;
                                            $unapplied_amt = $payment->UnappliedAmt;
                                            $txn_date = $payment->TxnDate;
                                            $status = "";
                                            $balance = 0;
                                            if ($payment->UnappliedAmt == 0) {
                                                $status = "Applied";
                                                $balance = 0;
                                            } else {
                                                $status = "UnApplied";
                                                $balance = $payment->UnappliedAmt;
                                            }
                                            //$total_received_amount += $total_amt;
                                            $total_received_amount += ($payment->TotalAmt - $payment->UnappliedAmt);
                                            ?>
                                            <tr>
                                                <td><?= $cnt; ?></td>
                                                <td><?= $total_amt; ?></td>
                                                <td><?= $balance; ?></td>
                                                <td><?= $status; ?></td>
                                                <td><?= date('d-m-Y', strtotime($txn_date)); ?></td>
                                            </tr>
                                            <?php
                                            $cnt++;
                                        }
                                    } else {
                                        ?>
                                        <tR>
                                            <td colspan="3">No Payments</td>
                                        </tR>
                                        <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <ul>

                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <div class="pad-10-0">
                        <h3 class="blue_color" style="display:inline;">Request for Refund</h3>
                        <a class="btn btn-primary refund" data-toggle="modal" data-target="#refund_payment" data-id="<?= $model->id ?>"><i data-toggle="tooltip" data-placement="top" title="Request to refund" class="fa fa-pencil"></i> Refund </a>
                    </div>
                    <div class="listing">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="bg-gray">
                                <th>S.No</th>
                                <th>Amount</th>
                                <th>Remaining Credit</th>
                                <th>Reason For Refund</th>
                                <th>Status</th>
                                <th>Refund Created Date</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $cnt = 1;
                                    if (!empty($all_credit_memos)) {
                                        foreach ($all_credit_memos as $credit_memo) {
                                            $reason_for_refund = "-";
                                            if (!empty($credit_memo->Line)) {
                                                if (!empty($credit_memo->Line[0])) {
                                                    if (!empty($credit_memo->Line[0]->Description)) {
                                                        $reason_for_refund = $credit_memo->Line[0]->Description;
                                                    }
                                                }
                                            }
                                            $remaining_credit = $credit_memo->RemainingCredit;
                                            $total_amt = $credit_memo->TotalAmt;
                                            $txn_date = $credit_memo->TxnDate;
                                            $status = "Not Used";
                                            if ($remaining_credit == 0) {
                                                $status = "Used";
                                            }
                                            ?>
                                            <tr>
                                                <td><?= $cnt; ?></td>
                                                <td><?= $total_amt; ?></td>
                                                <td><?= $remaining_credit; ?></td>
                                                <td><?= $reason_for_refund; ?></td>
                                                <td><?= $status; ?></td>
                                                <td><?= date('d-m-Y', strtotime($txn_date)); ?></td>
                                            </tr>
                                            <?php
                                            $cnt++;
                                        }
                                    } else {
                                        ?>
                                        <tR>
                                            <td colspan="5" class="text-center">No Refunds</td>
                                        </tR>
                                    <?php }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <ul>

                        </ul>
                    </div>
                </div>

                <div class="refund_list"> </div>

                <p>
                    <?= Html::a('<i class="fa fa-backward"></i> Back', ['index'], ['class' => 'btn back_button btn-warning cancel_button']); ?>
                </p>

            </div>
        </div>
        <div class="col-md-3 col-md-offset-1" <?= $total_invoice_amt ?>>
            <?php echo "<span class='sr-only total_received_amount'>$total_received_amount</span>
                           <span class='sr-only total_refunded_amount'>$total_refunded_amount</span>
                           <span class='sr-only total_invoice_amt'>$total_invoice_amt</span>"; ?>
            <?php if ((int) ($total_received_amount + $total_refunded_amount) >= (int) (($total_invoice_amt - 1 - $total_refunded_amount))) { ?>
                <div class="paymnetdue">
                    <img src="<?= Url::to(['uploads/payment_conf.png']) ?>" />
                </div>
            <?php } else { ?>
                <div class="paymnetdue"><img src="<?= Url::to(['uploads/payment_due.png']) ?>" /></div>
            <?php } ?>

            <div class="amout_summery">
                <button class="btn btn-default conf_amt_btn">Total Confirmed Amount = <?= $total_invoice_amt ?> <?= $currency ?></button>
                <button class="btn btn-success recv_amt_btn">Total Received Amount = <?= ($total_received_amount) ? $total_received_amount : 0; ?> <?= $currency; ?> </button>
            </div>

        </div>
    </div>

    <!--  Refund payment model  -->
    <div class="modal fade in" id="refund_payment">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?= Url::to(["/dv-registration/cliam_refund"]) ?>" method="POST" id="refund_payment_form">
                    <input type ="hidden" name ="<?php echo Yii::$app->request->csrfParam; ?>" value="<?php echo Yii::$app->request->csrfToken; ?>">
                    <input type="hidden" class="form-control" id="participant_id" name="participant_id" value="<?= $model->id ?>">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h4 class="modal-title">Claim for Refund</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Refund Amount</label>
                            <input type="number" class="form-control" id="refund_amount" name="refund_amount" placeholder="Enter Refund Amount"  required min="1">
                        </div>
                        <div class="form-group">
                            <label>Reason</label>
                            <textarea class="form-control" name="reason_for_refund" id="reason_for_refund" placeholder="Reason For Refund"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="refund_payment_form_submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>


    <!-- Make Payments -->
    <div class="modal fade in" id="make_payment">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="blue_color col-md-12"> Make Payment </h3>
                </div>
                <div class="modal-body">
                    <?php
                    ?>
                    <?php
                    $form = ActiveForm::begin(['action' => Url::to(['/dv-registration/invoice_payment']), 'id' => 'make_payment_form', 'fieldConfig' => ['options' => ['class' => 'form-group col-md-12']],
                    ]);
                    ?>

                    <input type="hidden" name="participant_id" id="participant_id" value="">           

<?php // $form->field($pp_model_new, 'amount_recieved')->input('number', ['min' => 1, 'placeholder' => "Amount Recieved", 'data-toggle' => "tooltip", 'data-placement' => "top", 'title' => "Amount Recieved"])->label(false);  ?>
                    <div class="form-group col-md-12 field-dvparticipantpayments-amount_recieved">
                        <input type="number" id="dvparticipantpayments-amount_recieved" class="form-control" name="DvParticipantPayments[amount_recieved]" title="" min="1" placeholder="Amount Recieved" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Amount Recieved" aria-invalid="true">
                    </div>


                    <div class="form-group col-md-12">
                        <input id="insdate_2"  class="form-control" name="payment_date" type="text"  data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title= autocomplete="off">
                    </div>

<?php // $form->field($pp_model_new, 'payment_reference_number')->input('text', ['placeholder' => "Payment Reference Number", 'data-toggle' => "tooltip", 'data-placement' => "top", 'title' => "Payment Reference Number"])->label(false);  ?>
                    <div class="form-group col-md-12 field-dvparticipantpayments-payment_reference_number">
                        <input type="text" id="dvparticipantpayments-payment_reference_number" class="form-control" name="DvParticipantPayments[payment_reference_number]" title="" placeholder="Payment Reference Number" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Payment Reference Number" aria-invalid="true">
                    </div>


<?php // $form->field($pp_model_new, 'payment_mode')->dropDownList($allPaymentmethod, ['prompt' => 'Select Payment Mode', 'data-toggle' => "tooltip", 'data-placement' => "top", 'title' => "Select Payment Mode"])->label(false);  ?>   
                    <div class="form-group col-md-12 field-dvparticipantpayments-payment_mode">
                        <select id="dvparticipantpayments-payment_mode" class="form-control" name="DvParticipantPayments[payment_mode]" title="" data-toggle="tooltip" data-placement="top" aria-required="true" data-original-title="Select Payment Mode" aria-invalid="true">
                            <option value="">Select Payment Mode</option>
<?php foreach ($allPaymentmethod as $key => $val) { ?>
                                <option value="<?= $key ?>"><?= $val ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-footer">
<?= Html::submitButton('Pay', ['class' => 'btn btn-primary pay_submit_button']) ?>
                        <?= Html::button('<i class="fa fa-times"></i> Cancel', ['class' => 'btn back_button btn-danger cancel_button', 'data-dismiss' => 'modal']); ?>
                    </div>
                        <?php ActiveForm::end(); ?>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.28.4/dist/sweetalert2.all.min.js"></script>
<?php
$session = Yii::$app->session;
$ses_data = $session->get('credetmeno_ses');
if (isset($ses_data)) {
    ?>
        <script type="text/javascript">

            var memoid = "<?= $ses_data['credit_memo'] ?>";
                    if (memoid == 0) {
            swal('Something went wrong!', 'Please try again later.', 'error');
            } else {
            swal('Refund request created successfully!', '', 'success');
            }
        </script>
    <?php
    $session->destroy('credetmeno_ses');
}
?>

    <script type="text/javascript">

        /* Get Published URL from script APP */
        var environment = "<?= Yii::$app->params['environment'] ?>"; // check server enviroment
                if (environment == 'Production'){
        // live
        var script_url = "https://script.google.com/macros/s/AKfycbzFgSakTaaTreJH62j19QRQFqxek29rzbbrDChEJYnajkxQw2s/exec"; // Live
        } else {
        // development
        var script_url = "https://script.google.com/macros/s/AKfycbxiHZGClxKKOdjO-u1IM8mB-5hMBSkCoTWzcpdzHykG96kfOwdZ/exec"; // my
        }

        $(document).ready(function(){

        $(".modules_allowed_value").append(' <?= $module_edit ?>');
                $(document).on('click', '#modules_allowed_view', function(e) {
        var course_id = $(this).attr('course-id');
                var participant_id = $(this).attr('participant-id');
                var old_val = $(this).attr('data-oldID');
                var token_id = $(this).attr('data-token_id');
                var total = 1;
                if (course_id == 1){
        total = 6;
        } else if (course_id == 2){
        total = 5;
        }

        if (total != 1){
        var i;
                var options = "";
                for (i = 1; i <= total; i++) {
        if (i == old_val){
        options += "<option selected value='" + i + "'>" + i + "</option>";
        } else{
        options += "<option value='" + i + "'>" + i + "</option>";
        }
        }
        $(".modules_allowed_value").html("<select id='select_allowed_module' data-token_id='" + token_id + "' data-old_val='" + old_val + "' data-participant_id='" + participant_id + "' required>\
                            " + options + "\
                            </select><a href='javascript:void(0);' data-old_val='" + old_val + "' class='cancel-allowed_module' data-toggle='tooltip' data-placement='top' title='Cancel'> <i class='fa fa-fw fa-remove'></i> </span>");
        } else{
        $('.modules_allowed_value').html(old_val);
        }
        });
                $(document).on('click', '.cancel-allowed_module', function(e) {
        var old_val = $(this).attr('data-old_val');
                $(this).remove();
                $('#modules_allowed_view').css('display', 'inline');
                $('.modules_allowed_value').html(old_val);
                $(".modules_allowed_value").append(' <?= $module_edit ?>');
        });
                $(document).on('change', '#select_allowed_module', function(e) {
        var participant_id = $(this).attr("data-participant_id");
                var oldvalue = $(this).attr("data-old_val");
                var allowed_module = $(this).val();
                var token_id = $(this).attr("data-token_id");
                var url = window.location.href;
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
        if (data == '1'){
        var url = script_url + "?callback=updatectrlq&token_id=" + token_id + "&allowed_module=" + allowed_module + "&action=update";
                var request = jQuery.ajax({
        crossDomain: true,
                url: url,
                method: "GET",
                async: false,
                dataType: "jsonp"
        });
                $('.modules_allowed_value').html(allowed_module);
                $('.cancel-allowed_module').remove();
                $('#modules_allowed_view').css('display', 'inline');
                $(".modules_allowed_value").append(' <?= $module_edit ?>');
                $("#modules_allowed_view").attr('data-oldid', allowed_module);
        }
        }
        });
        } else{
        $(this).val(oldvalue);
        }
        });
        });
                $(".participant_status_value").append(' <?= $payment_status_edit ?>');
                $(document).on('click', '#participant_status_view', function(e) {
        var participant_id = $(this).attr('participant-id');
                var old_val = $(this).attr('data-oldID');
                var token_id = $(this).attr('data-token_id');
                $(".participant_status_value").html("<select id='select_participant_status' data-old_val='" + old_val + "' data-participant_id='" + participant_id + "' data-token_id='" + token_id + "' required>\
                        <option value='1'>Active</option>\
                        <option value='2'>On Hold</option>\
                        <option value='3'>Drop off</option>\
                        <option value='4'>Completed</option>\
                        </select> <a href='javascript:void(0);' data-old_val='" + old_val +
                "' class='cancel-participant_status'> <i class='fa fa-fw fa-remove' data-toggle='tooltip' data-placement='top' title='Cancel'></i></span>");
                $("#select_participant_status").val(old_val);
        });
                $(document).on('click', '.cancel-participant_status', function(e) {
        var old_val = $(this).attr('data-old_val');
                var participant_status = '';
                if (old_val == 1){
        participant_status = 'Active';
        } else if (old_val == 2){
        participant_status = 'On Hold';
        } else if (old_val == 3){
        participant_status = 'Drop off';
        } else if (old_val == 4){
        participant_status = 'Completed';
        }

        $(this).remove();
                $('#participant_status_view').css('display', 'inline');
                $('.participant_status_value').html(participant_status);
                $(".participant_status_value").append(' <?= $payment_status_edit ?>');
        });
                $(document).on('change', '#select_participant_status', function(e) {
        var participant_id = $(this).attr("data-participant_id");
                var oldvalue = $(this).attr("data-old_val");
                var token_id = $(this).attr("data-token_id");
                var status_id = $(this).val();
                var url = window.location.href;
                // alert(token_id);
                var participant_status = '';
                if (status_id == 1){
        participant_status = 'Active';
        } else if (status_id == 2){
        participant_status = 'On Hold';
        } else if (status_id == 3){
        participant_status = 'Drop off';
        } else if (status_id == 4){
        participant_status = 'Completed';
        }

        swal({
        title: 'Are you sure?',
                text: "You want to change participant status?",
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
                data: { participant_id: participant_id, status_id: status_id, url:url },
                success: function(data){

        if (data == '1'){

        var url = script_url + "?callback=updatectrlq&token_id=" + token_id + "&participant_status=" + participant_status + "&action=update";
                var request = jQuery.ajax({
        crossDomain: true,
                url: url,
                method: "GET",
                async: false,
                dataType: "jsonp"
        });
                $('.participant_status_value').html(participant_status);
                $('.cancel-participant_status').remove();
                $('#participant_status_view').css('display', 'inline');
                $(".participant_status_value").append(' <?= $payment_status_edit ?>');
                $("#participant_status_view").attr('data-oldid', status_id);
        }
        }
        });
        } else{
        $(this).val(oldvalue);
        }
        });
        });
                $(".make_payments").click(function(){
        var id = $(this).attr('data-id');
                $('#make_payment #participant_id').val(id);
                $('#make_payment').modal('show');
                $('#insdate_2').datepicker({ dateFormat: 'yy-mm-dd' });
                $('#insdate_2').datepicker('setDate', new Date());
                return false;
        });
                $("#dvparticipantpayments-amount_recieved").focusout(function(){
        var amt_rec_val = $("#dvparticipantpayments-amount_recieved").val();
                if (amt_rec_val == ''){
        $(".field-dvparticipantpayments-amount_recieved").addClass("has-error");
                $(".field-dvparticipantpayments-amount_recieved").removeClass("has-success");
        } else{
        $(".field-dvparticipantpayments-amount_recieved").removeClass("has-error");
                $(".field-dvparticipantpayments-amount_recieved").addClass("has-success");
        }
        });
                $("#dvparticipantpayments-payment_reference_number").focusout(function(){
        var pay_ref_val = $("#dvparticipantpayments-payment_reference_number").val();
                if (pay_ref_val == ''){
        $(".field-dvparticipantpayments-payment_reference_number").addClass("has-error");
                $(".field-dvparticipantpayments-payment_reference_number").removeClass("has-success");
        } else{
        $(".field-dvparticipantpayments-payment_reference_number").removeClass("has-error");
                $(".field-dvparticipantpayments-payment_reference_number").addClass("has-success");
        }
        });
                $("#dvparticipantpayments-payment_mode").change(function(){
        var payment_mode_val = $("#dvparticipantpayments-payment_mode").val();
                if (payment_mode_val == ''){
        $(".field-dvparticipantpayments-payment_mode").addClass("has-error");
                $(".field-dvparticipantpayments-payment_mode").removeClass("has-success");
        } else{
        $(".field-dvparticipantpayments-payment_mode").removeClass("has-error");
                $(".field-dvparticipantpayments-payment_mode").addClass("has-success");
        }
        });
                $("#make_payment_form .pay_submit_button").click(function(){

        var amt_rec_val = $("#dvparticipantpayments-amount_recieved").val();
                if (amt_rec_val == ''){
        $(".field-dvparticipantpayments-amount_recieved").addClass("has-error");
                $(".field-dvparticipantpayments-amount_recieved").removeClass("has-success");
        } else{
        $(".field-dvparticipantpayments-amount_recieved").removeClass("has-error");
                $(".field-dvparticipantpayments-amount_recieved").addClass("has-success");
        }

        var pay_ref_val = $("#dvparticipantpayments-payment_reference_number").val();
                if (pay_ref_val == ''){
        $(".field-dvparticipantpayments-payment_reference_number").addClass("has-error");
                $(".field-dvparticipantpayments-payment_reference_number").removeClass("has-success");
        } else{
        $(".field-dvparticipantpayments-payment_reference_number").removeClass("has-error");
                $(".field-dvparticipantpayments-payment_reference_number").addClass("has-success");
        }

        var payment_mode_val = $("#dvparticipantpayments-payment_mode").val();
                if (payment_mode_val == ''){
        $(".field-dvparticipantpayments-payment_mode").addClass("has-error");
                $(".field-dvparticipantpayments-payment_mode").removeClass("has-success");
        } else{
        $(".field-dvparticipantpayments-payment_mode").removeClass("has-error");
                $(".field-dvparticipantpayments-payment_mode").addClass("has-success");
        }

        var $form = $("#make_payment_form"),
                data = $form.data("yiiActiveForm");
                $.each(data.attributes, function() {
        this.status = 3;
        });
                $form.yiiActiveForm("validate");
                if ($("#make_payment_form").find(".has-error").length) {
        return false;
        }
        $form.submit();
                $(".pay_submit_button").attr('disabled', true);
        });
        });
                function updatectrlq(e){
                $("#loading_custom").hide();
                        console.log(e.result);
                        if (e.result == "Token ID not found"){
                swal(
                        'Updated Fail On Sheet!',
                        'Record Not Updated On Sheet.',
                        'error'
                        );
                } else{
                swal(
                        'Updated!',
                        'Record Updated successfully.',
                        'success'
                        );
                }
                }
    </script>