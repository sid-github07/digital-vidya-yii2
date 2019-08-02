<?php
use yii\helpers\Html;
use app\models\DvUsersRole;
use yii\data\Pagination;
use yii\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $model app\models\DvUsers */
/* @var $form yii\widgets\ActiveForm */

// This file contains Create Role / Edit Role form.

?>

<div class="dv-users-form">
    <?php $form = ActiveForm::begin([
        'fieldConfig' => ['options' => ['class' => 'form-group col-md-4']],
        ]);
    if($model->isNewRecord){
        echo $form->field($model, 'name')->textInput(['maxlength' => true])->input('name', ['placeholder' => "Role Name",'required' => 'required'])->label(false);
        } else {
        echo $form->field($model, 'name')->textInput(['maxlength' => true])->input('name', ['placeholder' => "Role Name",'required' => 'required','readonly'=>'readonly'])->label(false);
    }

    if($model->isNewRecord){
        echo $form->field($model, 'status')->hiddenInput(['value'=>'1'])->label(false);
    } else {

    $user_id = $model->id;
    if($user_id == 1){
        echo $form->field($model, 'status')->hiddenInput(['value'=>'1'])->label(false);
    } else { 
        echo $form->field($model, 'status')->dropDownList([1 =>'Active',0 => 'Inactive'],['prompt'=>'Select Status','required' => 'required'])->label(false);
    }   ?>
    <div class="form-group col-md-12 user_permission">
        <h4>Accessible Sections for Current Role</h4>

        <div class="form-group col-md-3">
            <?php $accessdata = $model->access;
                  $access_data = explode(' ', $accessdata);
                  $user_id = $model->id;
                  if($user_id == 1){
                    $disable = ' disabled="disabled" ';
                    //$disable = '';
                  } else {
                    $disable = '';
                  } ?>
            <ul>
                <li><input name="users[all_users]" value="0" type="hidden"><label><input name="users[all_users]" value="1" type="checkbox" <?php if (in_array("all_users", $access_data)){ echo 'checked="checked"';} echo $disable; ?> > All Users</label>
                    <ul>                        
                        <li><input name="users[create_user]" value="0" type="hidden"><label><input name="users[create_user]" value="1" type="checkbox"  <?php if (in_array("create_user", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Create User</label></li>
                        <li><input name="users[view_user]" value="0" type="hidden"><label><input name="users[view_user]" value="1" type="checkbox"  <?php if (in_array("view_user", $access_data)){ echo 'checked="checked"';} echo $disable ?> > View User</label></li>
                        <li><input name="users[edit_user]" value="0" type="hidden"><label><input name="users[edit_user]" value="1" type="checkbox"  <?php if (in_array("edit_user", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Edit User</label></li>
                        <li><input name="users[create_team]" value="0" type="hidden"><label><input name="users[create_team]" value="1" type="checkbox" <?php if (in_array("create_team", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Create Team</label></li>
                        <li><input name="users[edit_team]" value="0" type="hidden"><label><input name="users[edit_team]" value="1" type="checkbox" <?php if (in_array("edit_team", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Edit Team</label></li>
                        <li><input name="users[user_role]" value="0" type="hidden"><label><input name="users[user_role]" value="1" type="checkbox" <?php if (in_array("user_role", $access_data)){ echo 'checked="checked"';} echo $disable ?> > User Role</label></li>
                        <li><input name="users[edit_role]" value="0" type="hidden"><label><input name="users[edit_role]" value="1" type="checkbox" <?php if (in_array("edit_role", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Edit Role</label></li>
                        <li><input name="users[department]" value="0" type="hidden"><label><input name="users[department]" value="1" type="checkbox" <?php if (in_array("department", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Create Department</label></li>
                        <li><input name="users[edit_department]" value="0" type="hidden"><label><input name="users[edit_department]" value="1" type="checkbox" <?php if (in_array("edit_department", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Edit Department</label></li>
                    </ul>
                </li>               
            </ul>
        </div>
        <div class="form-group col-md-3">

            <ul>
                <li><input name="registration[registration]" value="0" type="hidden"><label><input name="registration[registration]" value="1" type="checkbox" <?php if (in_array("registration", $access_data)){ echo 'checked="checked"';} echo $disable ?> > All Registration</label>
                    <ul>
                        <li><input name="registration[new_registration]" value="0" type="hidden"><label><input name="registration[new_registration]" value="1" type="checkbox" <?php if (in_array("new_registration", $access_data)){ echo 'checked="checked"';} echo $disable ?> > New Registration</label></li>

                        <li><input name="registration[view_registration]" value="0" type="hidden"><label><input name="registration[view_registration]" value="1" type="checkbox" <?php if (in_array("view_registration", $access_data)){ echo 'checked="checked"';} echo $disable ?> > View Registration</label></li>

                        <li><input name="registration[team_registration]" value="0" type="hidden"><label><input name="registration[team_registration]" value="1" type="checkbox" <?php if (in_array("team_registration", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Team Registration</label></li>
                        <li><input name="registration[users_activity_log]" value="0" type="hidden"><label><input name="registration[users_activity_log]" value="1" type="checkbox" <?php if (in_array("users_activity_log", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Users Activity Log </label></li>
                        <li><input name="registration[pending_revenue]" value="0" type="hidden"><label><input name="registration[pending_revenue]" value="1" type="checkbox" <?php if (in_array("pending_revenue", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Pending Revenue </label></li>

                    </ul>
                </li>                
            </ul>
        </div>
        <div class="form-group col-md-3">

            <ul>
                <li><input name="modules[delivery]" value="0" type="hidden"><label><input name="modules[delivery]" value="1" type="checkbox" <?php if (in_array("delivery", $access_data)){ echo 'checked="checked"';} echo $disable ?> > All Modules/Delivery</label>
                    <ul>                        
                        <li><input name="modules[create_modules]" value="0" type="hidden"><label><input name="modules[create_modules]" value="1" type="checkbox" <?php if (in_array("create_modules", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Create Modules</label></li>
                        <li><input name="modules[edit_modules]" value="0" type="hidden"><label><input name="modules[edit_modules]" value="1" type="checkbox" <?php if (in_array("edit_modules", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Edit Modules</label></li>
                        <li><input name="modules[view_modules]" value="0" type="hidden"><label><input name="modules[view_modules]" value="1" type="checkbox" <?php if (in_array("view_modules", $access_data)){ echo 'checked="checked"';} echo $disable ?> > View Modules</label></li>
                        <li><input name="modules[course]" value="0" type="hidden"><label><input name="modules[course]" value="1" type="checkbox" <?php if (in_array("course", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Create Course</label></li>
                        <li><input name="modules[edit_course]" value="0" type="hidden"><label><input name="modules[edit_course]" value="1" type="checkbox" <?php if (in_array("edit_course", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Edit Course</label></li>
                        <li><input name="modules[sales]" value="0" type="hidden"><label><input name="modules[sales]" value="1" type="checkbox" <?php if (in_array("sales", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Create Sales</label></li>
                        <li><input name="modules[edit_sales]" value="0" type="hidden"><label><input name="modules[edit_sales]" value="1" type="checkbox" <?php if (in_array("edit_sales", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Edit Sales</label></li>                            
                    </ul>
                </li>                
            </ul>
        </div>

        <div class="form-group col-md-3">

            <ul>
                <li><input name="reports[reports]" value="0" type="hidden"><label><input name="reports[reports]" value="1" type="checkbox" <?php if (in_array("reports", $access_data)){ echo 'checked="checked"';} echo $disable ?> > All Modules/Reports</label>
                    <ul>                        
                        <li><input name="reports[sales_incentive_report]" value="0" type="hidden"><label><input name="reports[sales_incentive_report]" value="1" type="checkbox" <?php if (in_array("sales_incentive_report", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Sales Incentive Report </label></li>
                        <li><input name="reports[monthly_sales_report]" value="0" type="hidden"><label><input name="reports[monthly_sales_report]" value="1" type="checkbox" <?php if (in_array("monthly_sales_report", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Monthly Sales Report </label></li>
                        <li><input name="reports[sales_report]" value="0" type="hidden"><label><input name="reports[sales_report]" value="1" type="checkbox" <?php if (in_array("sales_report", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Sales Report </label></li>
                        <li><input name="reports[yearly_sales_incentive]" value="0" type="hidden"><label><input name="reports[yearly_sales_incentive]" value="1" type="checkbox" <?php if (in_array("yearly_sales_incentive", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Yearly Sales Incentive </label></li>
                        <li><input name="reports[manage_monthly_incentive_rate]" value="0" type="hidden"><label><input name="reports[manage_monthly_incentive_rate]" value="1" type="checkbox" <?php if (in_array("manage_monthly_incentive_rate", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Manage Monthly Incentive Rate </label></li>
                        <li><input name="reports[view_monthly_incentive_rate]" value="0" type="hidden"><label><input name="reports[view_monthly_incentive_rate]" value="1" type="checkbox" <?php if (in_array("view_monthly_incentive_rate", $access_data)){ echo 'checked="checked"';} echo $disable ?> > View Monthly Incentive Rate </label></li>
                        <li><input name="reports[manage_full_payment_incentive_rate]" value="0" type="hidden"><label><input name="reports[manage_full_payment_incentive_rate]" value="1" type="checkbox" <?php if (in_array("manage_full_payment_incentive_rate", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Manage One Time Incentive Rate </label></li>
                        <li><input name="reports[view_full_payment_incentive_rate]" value="0" type="hidden"><label><input name="reports[view_full_payment_incentive_rate]" value="1" type="checkbox" <?php if (in_array("view_full_payment_incentive_rate", $access_data)){ echo 'checked="checked"';} echo $disable ?> > View One Time Incentive Rate </label></li>
                        <li><input name="reports[manage_team_member_exception]" value="0" type="hidden"><label><input name="reports[manage_team_member_exception]" value="1" type="checkbox" <?php if (in_array("manage_team_member_exception", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Manage Team Member Exception </label></li>
                        <li><input name="reports[view_all_exception]" value="0" type="hidden"><label><input name="reports[view_all_exception]" value="1" type="checkbox" <?php if (in_array("view_all_exception", $access_data)){ echo 'checked="checked"';} echo $disable ?> > View All Exception </label></li>
                        <li><input name="reports[view_current_executive_exception]" value="0" type="hidden"><label><input name="reports[view_current_executive_exception]" value="1" type="checkbox" <?php if (in_array("view_current_executive_exception", $access_data)){ echo 'checked="checked"';} echo $disable ?> > View Exception Rate</label></li>
                        <li><input name="reports[manage_team_manager_rule]" value="0" type="hidden"><label><input name="reports[manage_team_manager_rule]" value="1" type="checkbox" <?php if (in_array("manage_team_manager_rule", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Manage Team Manager Rule</label></li>
                        <li><input name="reports[consultant_dashboard_report]" value="0" type="hidden"><label><input name="reports[consultant_dashboard_report]" value="1" type="checkbox" <?php if (in_array("consultant_dashboard_report", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Consultant Dashboard Report</label></li>
                        <li><input name="reports[team_manager_dashboard]" value="0" type="hidden"><label><input name="reports[team_manager_dashboard]" value="1" type="checkbox" <?php if (in_array("team_manager_dashboard", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Team Manager Dashboard</label></li>
                        <li><input name="reports[incentive_report]" value="0" type="hidden"><label><input name="reports[incentive_report]" value="1" type="checkbox" <?php if (in_array("incentive_report", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Incentive Report</label></li>
                    </ul>
                </li>
            </ul>
        </div>

        <div class="form-group col-md-3">

            <ul>
                <li><input name="targets[targets]" value="0" type="hidden"><label><input name="targets[targets]" value="1" type="checkbox" <?php if (in_array("targets", $access_data)){ echo 'checked="checked"';} echo $disable ?> > targets</label>
                    <ul>                        
                        <li><input name="targets[create_target]" value="0" type="hidden"><label><input name="targets[create_target]" value="1" type="checkbox" <?php if (in_array("create_target", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Create Target </label></li>
                        <li><input name="targets[edit_target]" value="0" type="hidden"><label><input name="targets[edit_target]" value="1" type="checkbox" <?php if (in_array("edit_target", $access_data)){ echo 'checked="checked"';} echo $disable ?> > Edit Target </label></li>
                        <li><input name="targets[view_target]" value="0" type="hidden"><label><input name="targets[view_target]" value="1" type="checkbox" <?php if (in_array("view_target", $access_data)){ echo 'checked="checked"';} echo $disable ?> > View Target </label></li>
                    </ul>
                </li>                
            </ul>
        </div>

    </div>    
    <?php } ?>

    <div class="form-group col-md-12">
        <?php $user_id = $model->id;
        if($user_id == 1){
        } else { ?>
        <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-check"></i> Create Role' : '<i class="fa fa-pencil"></i> Update Role', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    <?php } ?>
        <?php if($model->isNewRecord) {
        } else { ?>
        <?= Html::a('<i class="fa fa-times"></i> Cancel', ['create_role'], ['class' => 'btn back_button btn-danger cancel_button']); ?>
        <?php } ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php   $query = DvUsersRole::find();
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count,'pageSize' => 10]);
        $user_role = $query->offset($pagination->offset)->limit($pagination->limit)->all(); ?>      
  <table class="table table-striped">
    <thead>
        <tr><th>#</th>
            <th>All User Roles</th>
            <th><center>Accessible Sections</center></th>
            <th><center>Status</center></th>
            <th><center>Edit</center></th>
        </tr>
    </thead>
    <tbody>
        <?php 
            foreach($user_role as $deuser){            
            if($deuser->status == 1){
                $status = '<center><i class="fa fa-check-circle green_icon"></i></center>';
            } else {
                $status = '<center><i class="fa fa-times-circle red_icon"></i></center>';
            }

            $access = $deuser->access;
            $access = rtrim($access," ");
            $access = str_replace(" ",", ",$access);
            $access = str_replace("_"," ",$access);
            $access = ucwords($access);

            echo '<tr>'; 
            if($deuser->id == 1){
                echo '<td> <a class="btn btn-xs btn-info"><strong>' . $deuser->id.'</strong></a> </td>';
            } else {
                echo '<td> <a class="btn btn-xs btn-info" href="edit_role?id='.$deuser->id.'"><strong>' . $deuser->id.'</strong></a> </td>';
            }
            
            echo '<td> '.$deuser->name .' </td>
            <td><center>'. $access .'</center></td>
            <td><center>'. $status .'</center></td>';
            if($deuser->id == 1){
                echo '<td><center> </center></td>';
            } else {
                echo '<td><center><a href="edit_role?id='.$deuser->id.'"><i class="fa fa-pencil"></i></a></center></td>';
            }
            echo '</tr>';
                } ?>
            </tbody>
        </table>
         <?php // display pagination
            echo LinkPager::widget(['pagination' => $pagination]); ?>