<?php

use yii\helpers\Html;
use yii\data\Pagination;
use yii\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\DvUsers;
use app\models\DvRegistration;


$this->title = 'Digital Vidya: Users Activity Log';
$this->params['breadcrumbs'][] = ['label' => 'All Registration', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Users Activity Log';
?> 
<div style="min-height:35px; "></div>
<div class="row">
    <div class="col-md-12">
        <div class="dv-users-activity_log table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr><th>#</th>
                        <th>Updated By</th>
                        <th>Participant Name</th>
                        <th>Page URL</th>
                        <th>Meta Key</th>
                        <th><center>Old Value</center></th>
                        <th><center>New Value</center></th>
                        <th><center>Created On</center></th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                    $cnt = $count;
                    foreach($model as $model_value){
                        $dt = new DateTime($model_value->created_on);

                        $user_id = $model_value->user_id;
                        $user = DvUsers::find()->where(['id'=>$user_id])->all();
                        $user_name1 = array_values(ArrayHelper::map($user, 'id', 'first_name'));
                        $user_name2 = array_values(ArrayHelper::map($user, 'id', 'last_name'));
                        $username = $user_name1[0]." ".$user_name2[0];                            

                        $p_user_id = $model_value->participant_id;
                        
                        $p_user = DvRegistration::find()->where(['id'=>$p_user_id])->all();

                        $p_user_name1 = array_values(ArrayHelper::map($p_user, 'id', 'first_name'));
                        /*echo "<pre>";
                        print_r($p_user_name1);
                        die;*/
                        $p_user_name2 = array_values(ArrayHelper::map($p_user, 'id', 'last_name'));
                        $p_name = $p_user_name1[0]." ".$p_user_name2[0];      

                        $old_value = $model_value->old_value;
                        $new_value = $model_value->new_value;

                        if($model_value->meta_key == "Updated participant status"){
                            $old_participant_status =  $model_value->old_value;
                            if($old_participant_status == 1){
                                $old_value = "Active";
                            }elseif($old_participant_status == 2){
                                $old_value = "On Hold";
                            }elseif($old_participant_status == 3){
                                $old_value = "Drop off";
                            }elseif($old_participant_status == 4){
                                $old_value = "completed";
                            }  

                            $new_participant_status =  $model_value->new_value;
                            if($new_participant_status == 1){
                                $new_value = "Active";
                            }elseif($new_participant_status == 2){
                                $new_value = "On Hold";
                            }elseif($new_participant_status == 3){
                                $new_value = "Drop off";
                            }elseif($new_participant_status == 4){
                                $new_value = "completed";
                            }  

                        }

                                                    
                        echo '<tr>
                        <td> <a class="btn btn-xs btn-info" href="javascript:void(0);"><strong>' . $cnt.'</strong></a> </td>
                        <td> '.$username. ' </td>
                        <td> '.$p_name. ' </td>
                        <td> '.$model_value->page_url . ' </td>
                        <td> '.$model_value->meta_key.' </td>
                        <td><center> '.$old_value.' </center></td>
                        <td><center> '.$new_value.' </center></td>
                        <td><center>'.$dt->format('d M, Y H:i:s').'</center></td>
                         </tr>';
                        $cnt++;
                    } 
                    if(count($model) <= 0){
                        echo '<tr><td colspan="8"><center> <h3>No Record Found</h3> </center></td> </tr>';
                        $total_records = '';
                    } ?>
                </tbody>
            </table>

            <?php // display pagination
                echo LinkPager::widget(['pagination' => $pagination]);
            ?>
            <div class="pull-right"><ul class="pagination"><li>
                <a class="btn btn-xs btn-info" data-toggle="tooltip" data-placement="top" title="" data-original-title="Total Listed: <?=$total_count ?>"><strong>Total List : <?= $total_count ?></strong></a></li></ul>
            </div>

        </div>
    </div>
</div>

