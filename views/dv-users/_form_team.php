<?php

use yii\helpers\Html;
use yii\data\Pagination;
use yii\widgets\LinkPager;
use yii\widgets\ActiveForm;
use app\models\DvUsersTeam;
use yii\helpers\ArrayHelper;
use app\models\DvUsersDepartment;
use app\models\DvUserMeta;
use app\models\DvUsersRole;
use app\models\DvUsers;
/* @var $this yii\web\View */
/* @var $model app\models\DvUsers */
/* @var $form yii\widgets\ActiveForm */
/* This file will dipaly the team manager and the consultant available in his team and perform necessary operation on it*/
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<div class="dv-users-form">
    <?php $form = ActiveForm::begin([
        'fieldConfig' => ['options' => ['class' => 'form-group col-md-4']],
        ]);

    $course = DvUsersDepartment::find()->where(['status'=>1])->all();
    $Dv_course = ArrayHelper::map($course, 'id', 'name');  ?>
    <?php //= $form->field($model, 'dep_id')->dropDownList($Dv_course, ['prompt'=>'Select Department', 'id'=>"ccourse",'required' => 'required'])->label(false); ?>

<div class="form-group col-md-4 field-ccourse">
<select id="ccourse" class="form-control" name="DvUsersTeam[dep_id]" required="required">
<option value="">Select Department</option>
<?php foreach($Dv_course as $id => $name){
            echo '<option value="'.$id.'">'.$name.'</option>';
    } ?>
</select>
<div class="help-block"></div>
</div>


<div class="form-group col-md-4 field-ccourse">
    <select id="managers" class="form-control" name="team_manager" required="required">
        <option value="">Select Manager</option>        
    </select>
    <div class="help-block"></div>
</div>


    <?php //= $form->field($model, 'name')->textInput(['maxlength' => true])->input('name', ['placeholder' => "User Email",'required' => 'required'])->label(false); //,'onclick' => 'loadSportsTeams()' ?>    
    <div class="form-group col-md-4 field-dvusersteam-name has-success">
        <input type="text" id="dvusersteam-name" class="form-control" name="DvUsersTeam[user_email]" placeholder="User Email" required="required" aria-invalid="false">
        <div class="help-block"></div>
    </div>

    <?php //if($model->isNewRecord){ ?>
    <?php //= $form->field($model, 'status')->hiddenInput(['value'=>'1'])->label(false); ?>
    <?php //} else { ?>
        <?php //= $form->field($model, 'status')->dropDownList([1 =>'Active',0 => 'Inactive'],['prompt'=>'Select Status','required' => 'required'])->label(false); ?>
    <?php //} ?>

   <div class="form-group col-md-12">
    <button type="submit" class="btn btn-success pull-right"><i class="fa fa-check"></i> Assign Manager</button>

        <?php //= Html::submitButton($model->isNewRecord ? '<i class="fa fa-check"></i> Assign Team' : '<i class="fa fa-pencil"></i> Update Team', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?php //if($model->isNewRecord) {
        //} else { ?>
        <?php //= Html::a('<i class="fa fa-times"></i> Cancel', ['create_team'], ['class' => 'btn back_button btn-danger cancel_button']); ?>
        <?php //} ?>
    </div>
    <?php ActiveForm::end(); ?>


<!-- Modal -->
<div id="updateManagerModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><i class="fa fa-remove"></i></button>
        <h4 class="modal-title">Remove/Replace Manager</h4>
      </div>
      <div class="modal-body">
    <?php $form = ActiveForm::begin(['action' => ['dv-users/update_team'],'options' => ['method' => 'post']]) ?>

    <div class="form-group col-md-6">
        <select id="update_manager" class="form-control" name="manager" required="required">
        <option value="">Select Manager to Remove/Replace</option>
       <?php $user_meta = DvUserMeta::find()->where(['meta_value' => '6' , 'meta_key' => 'role' ])->all();
       $role = ArrayHelper::map($user_meta, 'uid', 'meta_value');
       foreach($role as $key => $val){
            $first_name = DvUsers::find()->where(['id'=>$key])->one()->first_name;
            $last_name = DvUsers::find()->where(['id'=>$key])->one()->last_name;
            $name = $first_name.' '.$last_name;           
              echo '<option value="'.$key.'"> '.$name.' </option>';
          }     ?>
       </select>
   </div>

    <div class="form-group col-md-6">
        <select id="new_manager" class="form-control" name="new_manager" required="required">
        <option value="">Select New Manager</option>
        <option value="">Only Remove the Manager</option>
        </select>
   </div>
    

    <div class="form-group col-md-12">
        <hr>
    <?= Html::submitButton('<i class="fa fa-check"></i> Submit', ['class' => 'btn btn-success']); ?>
    </div>
    <div style="clear: both;">    </div>

        <?php ActiveForm::end(); ?>
      </div>
      
    </div>
  </div>
</div>


<div class="table-responsive fullwidth_cls inlinetable">
<?php $user_meta = DvUserMeta::find()->where(['meta_value' => '6' , 'meta_key' => 'role' ])->all();
      $role = ArrayHelper::map($user_meta, 'uid', 'meta_value'); ?>
 <table class="table table_striped inlinetable">
    <thead>
        <tr><th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Department</th>
            <th>Update</th>
            <th>Expand</th>
        </tr>
    </thead>
</table>
<div class="accordion inlinetable" id="accordionExample">
<?php $i = 0; foreach($role as $key => $val){   $i++;               
                $user_role = DvUserMeta::find()->where(['uid' => $key , 'meta_key' => 'role' ])->all();
                $role = ArrayHelper::map($user_role, 'uid', 'meta_value');
                $u_role = DvUsersRole::find()->where(['id'=>$role[$key]])->one()->name;
                $user_department = DvUsers::find()->where(['id'=>$key])->one()->department;
                if(empty($user_department)){
                    $u_dep[0] = '';
                } else {
                    $Userdepartment = DvUsersDepartment::find()->where(['id'=>$user_department])->all();
                    $Udepartment = ArrayHelper::map($Userdepartment, 'id', 'name');
                    $u_dep = array_values($Udepartment);
                }          

$first_name = DvUsers::find()->where(['id'=>$key])->one()->first_name;
$last_name = DvUsers::find()->where(['id'=>$key])->one()->last_name;
$name = $first_name.' '.$last_name;
$eamil = DvUsers::find()->where(['id'=>$key])->one()->email;

// check status 
$status = DvUsers::find()->where(['id'=>$key])->one()->status;
if($status == '1'){ // if status is 1
 ?>
  <div class="card">
    <div class="card-header" id="headingOne_<?php echo $key; ?>">
<table class="table table-striped">
  <tbody>
    <tr>
      <td>
        <button class="btn btn-link collapsed arrow_icon" type="button" data-toggle="collapse" data-target="#collapseOne_<?php echo $key; ?>" aria-expanded="true" aria-controls="collapseOne_<?php echo $key; ?>"><?php echo $i; ?></button>
      </td>
      <td>
        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseOne_<?php echo $key; ?>" aria-expanded="true" aria-controls="collapseOne_<?php echo $key; ?>"><?php echo $name; ?></button>
      </td>
      <td>
        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseOne_<?php echo $key; ?>" aria-expanded="true" aria-controls="collapseOne_<?php echo $key; ?>"><strong> <?php echo $eamil; ?> </strong></button>
         </td>
      <td>
        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseOne_<?php echo $key; ?>" aria-expanded="true" aria-controls="collapseOne_<?php echo $key; ?>"><?php echo $u_role; ?></button>                  
      </td>
      <td>
        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseOne_<?php echo $key; ?>" aria-expanded="true" aria-controls="collapseOne_<?php echo $key; ?>"><?php echo $u_dep[0]; ?></button>
    </td>
    <td>
     <button type="button" class="btn btn-link collapsed arrow_icon edit_icon " data-toggle="modal" data-target="#updateManagerModal<?php echo $key; ?>"> <i class="fa fa-user-times"></i> </button>


<!-- Modal -->
<div id="updateManagerModal<?php echo $key; ?>" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><i class="fa fa-remove"></i></button>
        <h4 class="modal-title">Update Manager <span class="text-primary"><?php echo $name; ?></span></h4>
      </div>
      <div class="modal-body">       
    <?php $form = ActiveForm::begin(['action' => ['dv-users/update_team'],'options' => ['method' => 'post']]) ?>
    <div class="form-group col-md-6">
        <input type="text" class="sr-only" value="<?= $key; ?>" name="manager" id="update_manager">
        <input type="text" class="form-control" readonly="" value="<?= $name; ?>">
<!--        <select id="update_manager" class="form-control" name="manager" required="required" readonly>
          <option value="<?php echo $key; ?>"> <?php echo $name; ?> </option>          
        </select>-->
        <br>
        <br>
        <div class="form-group pull-left text-left users_list_check">
            <?php 
            $user_team = DvUserMeta::find()->where(['meta_value' => $key , 'meta_key' => 'team' ])->all();
            $team = ArrayHelper::map($user_team, 'uid', 'meta_value');
            $ii = 0;
            if(!empty($team)) {
                echo "<p class='text-primary'><strong>Select users from below to move to other manager.</strong></p>";
            foreach ($team as $key2 => $val) {
                if ($key2 != $key) {
                    $ii++;
                    $user_role = DvUserMeta::find()->where(['uid' => $key2 , 'meta_key' => 'role' ])->all();
                    $role = ArrayHelper::map($user_role, 'uid', 'meta_value');
                    $u_role = DvUsersRole::find()->where(['id'=>$role[$key2]])->one()->name;
                    $user_department = DvUsers::find()->where(['id'=>$key2])->one()->department;
                    if (empty($user_department)) {
                        $u_dep[0] = '';
                        
                    } else {
                        $Userdepartment = DvUsersDepartment::find()->where(['id'=>$user_department])->all();
                        $Udepartment = ArrayHelper::map($Userdepartment, 'id', 'name');
                        $u_dep = array_values($Udepartment);
                        
                    }
                    $first_name = DvUsers::find()->where(['id'=>$key2])->one()->first_name;
                    $last_name = DvUsers::find()->where(['id'=>$key2])->one()->last_name;
                    $name2 = $first_name.' '.$last_name;
                    ?>
            <div class="checkbox">
                <label>
                    <input class="user_of_manager_checkbox" manager_role="<?= $key ?>" type="checkbox" value="<?= $key2 ?>" name="user_of_manager[]"><?= $name2; ?>
                </label>
            </div>
            <?php } } } ?>
        </div>
   </div>

    <div class="form-group col-md-6">
        <select id="new_manager" class="form-control new_manager_<?= $key ?>" name="new_manager" required="required">
          <option value="">Select New Manager</option>
          <option value="0">Only Remove the Manager</option>
          <?php $user_department = Yii::$app->db->createCommand("SELECT department FROM assist_users WHERE id = '$key' ")->queryAll();
          $user_department_id = $user_department[0]['department'];

          $dv_users = Yii::$app->db->createCommand("SELECT id, first_name, last_name FROM assist_users WHERE department = '$user_department_id' ")->queryAll();
          foreach($dv_users as $dvusers){
            $user_id_by_dep = $dvusers['id'];

            // check if the user is Manager
            $check_users_dep = Yii::$app->db->createCommand("SELECT id FROM assist_user_meta WHERE uid = '$user_id_by_dep' AND meta_key = 'role' AND meta_value = '6' ")->queryAll();
            if(!empty($check_users_dep)){                          
              if($key != $dvusers['id']){
                echo '<option value="'.$dvusers['id'].'">'.$dvusers['first_name'].' '.$dvusers['last_name'].'</option>';
              }
            }
          }  ?>
        </select>
   </div>

    <div class="form-group col-md-12">
        <hr>
    <?= Html::submitButton('<i class="fa fa-check"></i> Submit', ['class' => 'btn btn-success pull-right']); ?>
    </div>
    <div style="clear: both;">    </div>

        <?php ActiveForm::end(); ?>
      </div>
      
    </div>
  </div>
</div>
<!-- Modal -->

    </td>
    <td>
      <button class="btn btn-link collapsed arrow_icon" type="button" data-toggle="collapse" data-target="#collapseOne_<?php echo $key; ?>" aria-expanded="true" aria-controls="collapseOne_<?php echo $key; ?>">
        <i class="fa fa-fw fa-chevron-down"></i>
        <i class="fa fa-fw fa-chevron-up"></i>
     </button>
    </td>
  </tr></tbody></table>
    <!-- </button> -->
    </div>
<div id="collapseOne_<?php echo $key; ?>" class="collapse show@" aria-labelledby="headingOne_<?php echo $key; ?>" data-parent="#accordionExample">
<div class="card-body">
<table class="table tablestriped">
    <tbody>
        <?php $user_team = DvUserMeta::find()->where(['meta_value' => $key , 'meta_key' => 'team' ])->all();
                $team = ArrayHelper::map($user_team, 'uid', 'meta_value');
                $ii = 0;
            foreach($team as $key2 => $val){                 
                if($key2 != $key){
                    $ii++;
                $user_role = DvUserMeta::find()->where(['uid' => $key2 , 'meta_key' => 'role' ])->all();
                $role = ArrayHelper::map($user_role, 'uid', 'meta_value');
                $u_role = DvUsersRole::find()->where(['id'=>$role[$key2]])->one()->name;
                $user_department = DvUsers::find()->where(['id'=>$key2])->one()->department;
                if(empty($user_department)){
                    $u_dep[0] = '';
                } else {
                    $Userdepartment = DvUsersDepartment::find()->where(['id'=>$user_department])->all();
                    $Udepartment = ArrayHelper::map($Userdepartment, 'id', 'name');
                    $u_dep = array_values($Udepartment);
                }

                $first_name = DvUsers::find()->where(['id'=>$key2])->one()->first_name;
                $last_name = DvUsers::find()->where(['id'=>$key2])->one()->last_name;
                $name2 = $first_name.' '.$last_name;
                $eamil = DvUsers::find()->where(['id'=>$key2])->one()->email;   ?>
       <tr><td><?php echo $ii; ?></td><td><?php echo $name2; ?></td><td><strong> <?php echo $eamil; ?> </strong> </td><td><?php echo $u_role; ?></td>
        <td><?php echo $u_dep[0]; ?></td>
        <td>
            <a data-toggle="modal" data-target="#updateUserManagerModal<?php echo $key2; ?>">
                <i class="fa fa-user-times" data-toggle="tooltip" data-placement="top" data-original-title="Remove '<?php echo $name2; ?>' from the Team of '<?php echo $name; ?>' " ></i>
            </a>
            <div id="updateUserManagerModal<?php echo $key2; ?>" class="modal fade" role="dialog">
                <div class="modal-dialog modal-sm">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><i class="fa fa-remove"></i></button>
                            <h4 class="modal-title">Update Manager <span class="text-primary"><?php echo $name2; ?></span></h4>
                        </div>
                        <div class="modal-body">
                            <?php $form = ActiveForm::begin(['action' => ['dv-users/update_user_team'],'options' => ['method' => 'post']]) ?>
                            <div class="form-group col-md-12">
                                <input type="text" class="sr-only" value="<?= $eamil ?>" name="change_user_email">
                                <label class="pull-left" for="update_user">Username</label>
                                <input type="text" name="user" id="update_user" value="<?= $key2 ?>" class="sr-only">
                                <input type="text" value="<?= $name2 ?>" class="form-control" readonly="">
<!--                                <select id="update_user" class="form-control" name="user" required="required" readonly>
                                    <option value="<?php echo $key2; ?>"> <?php echo $name2; ?> </option>
                                </select>-->
                            </div>
                            <div class="form-group col-md-12">
                                <label class="pull-left" for="new_manager">Select New Manager</label>
                                <select id="new_manager" class="form-control new_manager_<?= $key ?>" name="new_manager" required="required">
                                    <option value="">Select New Manager</option>
                                    <option value="">Remove from existing Manager</option>
                                        <?php 
                                        $user_department = Yii::$app->db->createCommand("SELECT department FROM assist_users WHERE id = '$key' ")->queryAll();
                                        $user_department_id = $user_department[0]['department'];
                                        $dv_users = Yii::$app->db->createCommand("SELECT id, first_name, last_name FROM assist_users WHERE department = '$user_department_id' ")->queryAll();
                                        foreach($dv_users as $dvusers){
                                            $user_id_by_dep = $dvusers['id'];
                                            /* check if the user is Manager*/
                                            $check_users_dep = Yii::$app->db->createCommand("SELECT id FROM assist_user_meta WHERE uid = '$user_id_by_dep' AND meta_key = 'role' AND meta_value = '6' ")->queryAll();
                                            if(!empty($check_users_dep)){
                                                if($key != $dvusers['id']){
                                                    echo '<option value="'.$dvusers['id'].'">'.$dvusers['first_name'].' '.$dvusers['last_name'].'</option>';
                                                }
                                            }
                                         }
                                         ?>
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                <hr>
                                <?= Html::submitButton('<i class="fa fa-check"></i> Submit', ['class' => 'btn btn-success pull-right']); ?>
                            </div>
                            <div style="clear: both;"></div>
                                <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </td>
<!--        <td><a data-toggle="tooltip" data-placement="top" title="" href="remove_user?id=<?php echo $key2; ?>" data-original-title="Remove '<?php echo $name2; ?>' from the Team of '<?php echo $name; ?>' " onclick="return confirm('Are you sure?');"><i class="fa fa-user-times"></i></a></td>-->
      </tr>
<?php } }

if(empty($user_team)){
    echo '<tr class="orange"><td colspan="5"><center>############## No Record Found ##############</center></td></tr>';
} ?>
    </tbody></table>
      </div>
    </div>
  </div>              
<?php
} // if status is 1

 } ?> 
</div>
</div>
 <style type="text/css">
.ui-draggable, .ui-droppable { background-position: top; }
.ui-autocomplete.ui-front.ui-menu.ui-widget.ui-widget-content.ui-corner-all{left:66%;max-width:205px;top:28%;}
.ui-widget-content a { color: #333333; width: 100%; display: inherit; padding: 4px 8px;}
.card .btn { width: 100%; text-align: left;  padding: 0px; display: block; margin: 0; cursor: pointer; color: #000; }
.card .modal-body .btn{width:auto; padding: 5px 10px; color: #fff;}
.collapse.in .card-body{ overflow: hidden; }
.collapse .card-body{ padding: 10px; }
.card-body{ padding: 10px; background: #FBFBFB; border-bottom: 10px solid #ECF0F5; }
.table { width: 100%; max-width: 100%; margin-bottom: 0px; }
.card .table > tbody > tr > td,
.table_striped > thead > tr > th{ text-align: left;}
.card .table > tbody > tr > td:nth-of-type(1n),
.table_striped > thead > tr > th:nth-of-type(1n){width:50px; text-align: center;}
.card .table > tbody > tr > td:nth-of-type(2n),
.table_striped > thead > tr > th:nth-of-type(2n){width: 200px; text-align: left;}
.card .table > tbody > tr > td:nth-of-type(3n),
.table_striped > thead > tr > th:nth-of-type(3n){width: 275px; text-align: left;}
.card .table > tbody > tr > td:nth-of-type(4n),
.table_striped > thead > tr > th:nth-of-type(4n){width: 143px; text-align: left;}
.card .table > tbody > tr > td:nth-of-type(5n),
.table_striped > thead > tr > th:nth-of-type(5n){width: 125px; text-align: left;}
.card .table > tbody > tr > td:nth-of-type(6n),
.table_striped > thead > tr > th:nth-of-type(6n){width:70px; text-align: right;}
.card .table > tbody > tr > td:nth-of-type(7n),
.table_striped > thead > tr > th:nth-of-type(7n){width:70px; text-align: right;}
.card-body .tablestriped > tbody > tr{background-color:#fff;}
.tablestriped > tbody > tr:nth-of-type(2n+1) { background-color: #D9EDF7;}
.tablestriped > tbody > tr.orange{background-color:#f39c12; color: #fff;}
.card .table.tablestriped > tbody > tr.orange > td{width:100%;}
.btn.btn-link .fa-chevron-up{display: block; color:#fff;}
.btn.btn-link .fa-chevron-down{display: none;}
.btn.btn-link.collapsed .fa-chevron-up{display: none;}
.btn.btn-link.collapsed .fa-chevron-down{display: block; color: #fff;}
.fa-remove{float: right; color:#d22222;}
/*.modal-dialog { width: 800px; margin: 150px auto; }*/
.modal-dialog { margin: 150px auto; }
.loading_custom { z-index: +99999; }
.btn:active { -webkit-box-shadow: inset 0 0px 0px rgba(0,0,0,0.125); -moz-box-shadow: inset 0 0px 0px rgba(0,0,0,0.125); box-shadow: inset 0 0px 0px rgba(0,0,0,0.125); }
.card .btn.arrow_icon{background-color: #337ab7; padding:3px; max-width:25px; float: right; text-align: center; color: #fff; font-weight: bold; }
.card .btn.arrow_icon:hover{background-color:#4097e2; padding:3px; max-width:25px; float: right; text-decoration: none; }
.card .btn.arrow_icon:focus{text-decoration: none;}
.card .btn.arrow_icon.edit_icon{background-color:#cd1700;}
.modal-title{text-align: left; }
</style>
</div>
<script>
    jQuery(".user_of_manager_checkbox").click(function() {
        var manager_id = jQuery(this).attr('manager_role');
        if (jQuery(".users_list_check input[type=checkbox]").is(":checked")) {
            $('.new_manager_'+manager_id+' option').each(function(i,elem){
                if(elem.text == "Only Remove the Manager" || jQuery(elem).val() == 0){
                    jQuery(elem).attr('disabled',true);
                }
            });
        } else {
            $('.new_manager_'+manager_id+' option').each(function(i,elem){
                if(elem.text == "Only Remove the Manager" || jQuery(elem).val() == 0){
                    jQuery(elem).attr('disabled',false);
                }
            });
        }
    });
</script>