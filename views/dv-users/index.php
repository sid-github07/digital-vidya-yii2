<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\LinkPager;
use yii\widgets\DetailView;
use app\models\DvUsersRole;
use app\models\DvUsersTeam;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use app\models\DvUserMeta;
use app\models\DvUsersDepartment;
use yii\helpers\Url;
use app\models\UserRole;
use app\models\DvUsers;
use app\models\DvModuleModel;
/* @var $this yii\web\View */
// this is a index file of Users. it is use to list all the users 50 per page. It is also contain a Search form and filter form. 

$this->title = 'All Users';
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['index']]; ?>
<div style="min-height:35px; "></div>
<div class="dv-users-index table-responsive">
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

 <table class="table table-striped" border="0">
 	<thead>
 		<tr>
 			<td colspan="3">
            <?php $form = ActiveForm::begin(['id' => 'user-search-form', 'method' => 'get', 'action' => Url::to(['dv-users/search'])]); ?>
                <input id="dvusers-search" value="<?php echo Yii::$app->request->get('s'); ?>" class="form-control" name="s" placeholder="Search User by Name or Email Address"> 
                <?= Html::submitButton( '<i class="fa fa-search"></i> Search' , ['class' => 'btn btn-sm btn-success search_submit']) ?>
               <?php ActiveForm::end(); ?>
            </td>
            <?php $filterform = ActiveForm::begin(['id' => 'user-filter', 'method' => 'get', 'action' => Url::to(['dv-users/filter'])]); 
            $select = 'selected="selected"'; ?>
            <td width="15%">
                <?php $DvUsersDepartment = DvUsersDepartment::find()->where(['status' => 1])->all();
                 $Users_dep = ArrayHelper::map($DvUsersDepartment, 'id', 'name'); 
                 echo '<select class="form-control" name="department" id="depart">';
                 echo '<option value="">Select Department</option>';
                 $depart_k = Yii::$app->request->get('department');
                    foreach($Users_dep as $key => $val){
                        echo '<option ';
                         if($depart_k == $key){
                            echo $select;
                        } 
                        echo ' value="'.$key.'">'. $val .'</option>';
                    }
                    echo '</select>'; ?>
            </td>
            <td width="12%">
                <?php
                  $DvUsersRole = DvUsersRole::find()->where(['status' => 1])->all();
                  $Users_Role = ArrayHelper::map($DvUsersRole, 'id', 'name');
                 
                 echo '<select class="form-control" name="role" id="role">';
                 echo '<option value="">Select Role</option>';
                    $role_k = Yii::$app->request->get('role');
                    foreach($Users_Role as $key => $val){
                        echo '<option ';
                         if($role_k == $key){
                            echo $select;
                        } 
                        echo ' value="'.$key.'">'. $val .'</option>';
                    }
                    echo '</select>';
                  //  echo '<input type="hidden" name="team" value="">';
                    ?>
     		</td>
     			<!--td width="13%">
                <?php /* $DvUsersTeam = DvUsersTeam::find()->where(['status' => 1])->all();
                 $Users_team = ArrayHelper::map($DvUsersTeam, 'id', 'name'); 
                 echo '<select class="form-control" name="team" id="team">';
                 echo '<option value="">Select Team</option>';
                 $team_k = Yii::$app->request->get('team');
                    foreach($Users_team as $key => $val){
                        echo '<option ';
                         if($team_k == $key){
                            echo $select;
                        } 
                        echo ' value="'.$key.'">'. $val .'</option>';
                    }
                    echo '</select>'; */ ?>
     			</td-->
     	      <?php $domain_data = Yii::$app->request->get('course_domain'); ?>
        <td>
            <select id="course_domain" class="form-control" name="course_domain" data-toggle="tooltip" data-placement="top" title="" data-original-title="Select Domain">
                <option value="">All</option>
                <option <?php echo isset($domain_data) && $domain_data == 'dm' ? 'selected="selected"' : ''; ?> value="dm">DM</option>
                <option <?php echo isset($domain_data) && $domain_data == 'da' ? 'selected="selected"' : ''; ?> value="da">DA</option>
            </select>
        </td>		
     	<td width="17%">
            <?= Html::submitButton( '<i class="fa fa-filter"></i> Apply Filter' , ['class' => 'btn btn-sm pull btn-warning search_submit']) ?>

            <a href="<?php echo Url::to(['dv-users/index']); ?>">
                <button type="button" class="btn btn-sm pull btn-success search_submit">
                    <i class="fa fa-refresh"></i> Reset</button>
            </a>

            <?php ActiveForm::end(); ?>
        </td>
 	</tr>
 	</thead>
 	<tbody>
 	</tbody>
 </table>


    <table class="table table-striped">
            <thead>
            <tr><th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Domain</th>
                <!-- <th>Team</th> -->
                <th>Department</th>
                <th>Role</th>
                <th><center>Status</center></th>
                <th><center>Edit</center></th>                
                </tr>
            </thead>
            <tbody>
            <?php $page = Yii::$app->request->get('page');
            if(empty($page)){
              $i = 0;
            } else {
                if($page == 1){
                    $i = 0;
                } else {
                    $i = ($page*50)-50;
                }
              
            }
            foreach($users as $user){ $i++;

                if( $user->status == 1){
                    $status = '<center data-toggle="tooltip" data-placement="top" title="Active"><i class="fa fa-check-circle green_icon"></i></center>';
                } else {
                    $status = '<center data-toggle="tooltip" data-placement="top" title="In-Active"><i class="fa fa-times-circle red_icon"></i></center>';
                }
                
                $user_role = DvUserMeta::find()->where(['uid' => $user->id , 'meta_key' => 'role' ])->all();
                $role = ArrayHelper::map($user_role, 'uid', 'meta_value');
                $u_role = count($role) > 0 ? DvUsersRole::find()->where(['id'=>$role[$user->id]])->one()->name : '';
                //Begin Added on 03 June 2019
                if($user->course!='' && $user->course!="da" &&  $user->course!="dm" && $user->course!="dm,da"){
                    $module_ids_array = explode(',',$user->course);
                    $module_array = ArrayHelper::map(DvModuleModel::find()->where(['IN','id',$module_ids_array])->all(),'id','mcourse');
                    $module_domain = implode(',',array_unique($module_array));
                }else{
                    $module_domain = $user->course;
                }
                //End Added on 03 June 2019
                
                /*$user_team = DvUserMeta::find()->where(['uid' => $user->id , 'meta_key' => 'team' ])->all();
                $team = ArrayHelper::map($user_team, 'uid', 'meta_value');    
                if(empty($team[$user->id])){
                	$u_team = '---';
                } else {
                	//$u_team = DvUsersTeam::find()->where(['id'=>$team[$user->id]])->one()->name;	
                    $u_team = DvUsers::find()->where(['id'=>$team[$user->id]])->one()->first_name;
                    $u_team .= ' ';
                    $u_team .= DvUsers::find()->where(['id'=>$team[$user->id]])->one()->last_name;
                    if($role[$user->id] ==6){
                        $u_team = '<span class="btn-warning"> &nbsp; Team Owner &nbsp; </span>';
                    }
                }*/
                
                
                if(empty($user->department)){
                    $u_dep[0] = '';
                } else {
                    $Userdepartment = DvUsersDepartment::find()->where(['id'=>$user->department])->all();
                    $Udepartment = ArrayHelper::map($Userdepartment, 'id', 'name');
                    $u_dep = array_values($Udepartment);
                }              

                //$module_model = DvModuleModel::find()->where(['in','id' =>])->one();
                
                //echo "<pre>";
                //print_r(explode(',',$user->course));

                   
                echo '<tr>
                <td> <a class="btn btn-xs btn-info" data-toggle="tooltip" data-placement="top" title="View Detail of '.$user->first_name.' '.$user->last_name.'" href="view?id='.$user->id.'"><strong>' . $i.'</strong></a> </td>
                <td><strong>' . $user->first_name.' '.$user->last_name.'</strong> </td>
                <td>' . $user->email . '</td>
                <td>' . $module_domain . '</td>
                <td>' . $u_dep[0]. '</td>
                <td>' .$u_role. '</td>
                <td>' . $status. '</td>
                <td><center><a data-toggle="tooltip" data-placement="top" title="Edit Detail of '.$user->first_name.' '.$user->last_name.'" href="update?id='.$user->id.'"><i class="fa fa-pencil"></i></a></center></td>
                </tr>';
         
            }

            if(empty($users)){
                echo '<tr><td colspan="8"></td></tr>';
                echo '<tr><td colspan="8"><center><h1>No Record Found</h1></center></td></tr>';
                echo '<tr><td colspan="8"></td></tr>';
                 $total_records = '';
            }
            ?>
            </tbody>
        </table>
                <?php // display pagination
                if(isset($pages)){
            echo LinkPager::widget(['pagination' => $pages]);
            } ?>
  <?php /* ?>  <p>
        
        <?= Html::a('Create Users', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Audit Logs', ['audit-logs/index'], ['class' => 'btn btn-info']) ?>
        <?= Html::a('Transaction parameters', ['transaction/admin'], ['class' => 'btn btn-danger']) ?>
       
    </p> <?php */ ?>
    <?php if($total_records != '') { ?>
    <ul class="pagination pull-right"><li>
        <a class="btn btn-xs btn-info" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php echo 'Total Listed Users: '.$total_records; ?>"><strong><?php echo 'Total Listed Users: '.$total_records; ?></strong></a></li></ul>
<div class="clr"></div>
<?php } ?>
</div>