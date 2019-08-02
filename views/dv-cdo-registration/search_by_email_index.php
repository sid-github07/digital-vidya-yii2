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

$this->title = 'Participant search by email';
$this->params['breadcrumbs'][] = $this->title; ?>
<!-- Searching functionality -->

<div class="dv-users-index row">
    <?php $form = ActiveForm::begin(['id' => 'module-search-form', 'method' => 'post', 'action' => Url::to(['dv-registration/search_by_email'])]); ?>

        <!-- Search by email -->
        <div class="form-group col-md-4">
            <?= $form->field($model, 'email')->textInput(['maxlength' => true])->input('text', ['placeholder' => "Email", 'data-toggle'=>"tooltip", 'data-placement'=>"top", 'title'=>"Email"])->label(false); ?>
        </div>
        
        <div class="form-group col-md-2">
        <?= Html::submitButton( '<i class="fa fa-search"></i> Search' , ['class' => 'btn btn-success']) ?>
        </div>
    <?php ActiveForm::end(); ?>
</div>

<?php 
/**
* Search if model_data is exist
**/
if(isset($notFound)){ ?>
    <div class="dv-users-index row">
        <span class="form-group col-md-12"><?= $notFound ?></span>
    </div>
<?php }


if(isset($model_data)){ ?>

<div class="dv-users-index">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
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
                 <?php if( $user_role == 1 ) { ?>
                    <th>Sales Executive</th>
                <?php } ?>
                <th>Name</th>
                <th>Email</th>
                <th>Program Coordinator</th>                
                <th>Course</th>
                <th>Batch Date</th> 
                <th>Committed Amount</th> 
                <th>Payment Received</th> 
                <th>Amount Due</th> 
                <th>Modules Allowed</th> 
                <th>Participantion Status</th> 
                <th>Payment Status</th>
              <!--   <th>Edit Participantion Status</th> -->
                <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $cnt = $count;
            foreach($model_data as $user){

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

                $course_batch = DvParticipantModules::find()->where(['id'=>$user->course_batch])->all();
                $b_start_date = ArrayHelper::map($course_batch, 'id', 'start_date');
                $batch_start_date = array_values($b_start_date);     

                $Payment_Received = DvParticipantPayments::find()->where(['participant_id'=>$user->id])->all();
                $total_payment_Received = 0;
                $total_conf_payment_Received = 0;
                foreach($Payment_Received as $payment_data){
                    if($payment_data->amount_confirmed == "1"){
                        $total_conf_payment_Received = $total_conf_payment_Received + $payment_data->amount_recieved;
                    }
                    $total_payment_Received = $total_payment_Received + $payment_data->amount_recieved;

                }
                
                $total_payment_pending = $user->total_confirmed_amount - $total_conf_payment_Received;
                
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

                $p_status =  $user->participant_status;
                /*
                $participant_status =  $user->participant_status;
                if($participant_status == 1){
                    $p_status = "Active";
                }elseif($participant_status == 2){
                    $p_status = "On Hold";
                }elseif($participant_status == 3){
                    $p_status = "Drop off";
                }elseif($participant_status == 4){
                    $p_status = "completed";
                }
                */

                if($user->program_coordinator_id!=""){
                    $program_coordinatior = $user->program_coordinator_id;
                }else{
                    $program_coordinatior = "NA";
                }

                /*<td> <a class="btn btn-xs btn-info" href="view?id='.$user->id.'"><strong>' . $cnt . '</strong></a> </td>*/
                echo '<tr>
                <td> <a class="btn btn-xs btn-info" data-toggle="tooltip" data-placement="top" title="View Detail" href="view?id='.$user->id.'"><strong>' . $cnt . '</strong></a> </td>
                <td>' . $date . '</td>'; ?>
                <?php if( $user_role == 1 ) { echo '<td>'.$sales_name.'</td>'; } ?> 
                <?php echo '<td>' .$username. '</td>
                <td>' . $user->email . '</td>
                <td>' . $program_coordinatior . '</td>
                <td>' . $u_course[0] . ' </td>
                <td> ' . $batch_start_date[0] . '</td>
                <td> ' . $user->total_confirmed_amount . '</td>
                <td> ' . $total_payment_Received . '</td>
                <td> ' . $total_payment_pending . '</td>'; 

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
                <td><center><a data-toggle="tooltip" data-placement="top" title="Edit" href="update_installments?id='.$user->id.'"><i class="fa fa-pencil"></i></a></center></td>
                </tr>';
                /*<td><center><a href="update?id='.$user->id.'"><i class="fa fa-pencil"></i></a></center></td>*/

                /*<td><center><a data-toggle="tooltip" data-placement="top" title="Edit" href="update_participant_status?id='.$user->id.'"><i class="fa fa-pencil"></i></a></center></td> */
                $cnt++;
            } 

            if(count($model_data) <= 0){
                echo '<tr><td colspan="15"><center> <h3>No Record Found</h3> </center></td> </tr>';
                $total_records = '';
            }
        ?>
        </table>
        <?php // display pagination
            echo LinkPager::widget(['pagination' => $pages]); ?>
</div>
<?php 
}

/**
* End if loop if not model_data exit 
**/

?>

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
                    data: { participant_id: participant_id, status_id: status_id ,url:url },
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
                $.ajax({
                    url: '<?php echo \Yii::$app->getUrlManager()->createUrl('dv-registration/update_participant_allowed_module_ajex') ?>',
                    type: 'POST',
                    data: { participant_id: participant_id, allowed_module: allowed_module, url:url },
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
            });           
        });
        
    });
</script>