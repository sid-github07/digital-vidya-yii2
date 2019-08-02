<?php
use yii\helpers\Html;
use yii\widgets\LinkPager;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\export\ExportMenu;
use yii\data\ArrayDataProvider;
use app\models\DvCourse;
$this->title = 'Participant Members';
$this->params['breadcrumbs'][] = $this->title; 

//Define empty variable
$en_sdate = '';
$en_edate = ''; 
$batch_sdate = '';
$batch_edate = ''; 
$filter_optout = '';
$filter_vskills = '';
$foundation_session_date = '';
$modules_allowed_from = '';
$modules_allowed_to = '';
$modules_completed_from = '';
$modules_completed_to = '';
$program_coordinator = '';
$participant_new_old_all = '';

//Filter Purpose data set
$filter_course = array();

//For Basic Filter
if(isset($filter_data['email'])){
    $by_email = $filter_data['email'];
}
if(isset($filter_data['name'])){
    $by_name = $filter_data['name'];
}
if(isset($filter_data['mobile'])){
    $by_mobile = $filter_data['mobile'];
}
if(isset($filter_data['course'])){
    $filter_course = $filter_data['course'];
}

//Advanced Filter
//For Yes(1) & No(0) data found or not 
if(isset($filter_data['blog_url'])){
    $filter_blog_url = $filter_data['blog_url'];   
}
if(isset($filter_data['optout'])){
    $filter_optout = $filter_data['optout'];
}
if(isset($filter_data['vskills'])){
    $filter_vskills = $filter_data['vskills']; 
}

//For Enrollment Date 
if(isset($filter_data['en_sdate'])){
    $en_sdate = $filter_data['en_sdate'];
}
if(isset($filter_data['en_edate'])){
    $en_edate = $filter_data['en_edate'];
}

//For Batch date
if(isset($filter_data['batch_sdate'])){
    $batch_sdate = $filter_data['batch_sdate'];
}
if(isset($filter_data['batch_edate'])){
    $batch_edate = $filter_data['batch_edate'];
}
if(isset($filter_data['foundation_session_date'])){
    $foundation_session_date = $filter_data['foundation_session_date'];
}

//For Module allowed
if(isset($filter_data['modules_allowed_from'])){
    $modules_allowed_from = $filter_data['modules_allowed_from'];
}

if(isset($filter_data['modules_allowed_to'])){
    $modules_allowed_to = $filter_data['modules_allowed_to'];
}

//For Module Completed
if(isset($filter_data['modules_completed_from'])){
    $modules_completed_from = $filter_data['modules_completed_from'];
}

if(isset($filter_data['modules_completed_to'])){
    $modules_completed_to = $filter_data['modules_completed_to'];
}

//For coordinator
if(isset($filter_data['program_coordinator'])){
    $program_coordinator = $filter_data['program_coordinator'];
}
//For New Participant
if(isset($filter_data['participant_new_old_all'])){
    $participant_new_old_all = $filter_data['participant_new_old_all'];
}
?>
<div class="table-responsive">
    <div class="dv-participant-index">
        <?php $form = ActiveForm::begin(['id' => 'search-form', 'method' => 'get', 'action' => Url::to(['dv-delievery-members/filter'])]); ?>
            <?php
            $select = 'selected="selected"';
            $yes_no_array = ['Yes','No'];
            $course = DvCourse::find()->where(['status'=>1])->all();
            $Dv_course = ArrayHelper::map($course, 'id', 'name');
            echo '<div class="form-group col-md-2" data-toggle="tooltip" data-placement="top" title="Select Courses">';
            echo '<select class="form-control" name="course[]" multiple="multiple" size="12">';
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
        <div class="form-group col-md-3" data-toggle="tooltip" data-placement="top" title="Enter Name">
            <input type="text" value="<?= isset($by_name)?$by_name:'' ?>" class="form-control" name="name" placeholder="Enter Name" autocomplete="off">
        </div>
        <div class="form-group col-md-3" data-toggle="tooltip" data-placement="top" title="Enter Mobile">
            <input type="text" value="<?= isset($by_mobile)?$by_mobile:'' ?>" class="form-control" name="mobile" placeholder="Enter Mobile" autocomplete="off">
        </div>
        <div class="form-group col-md-3" data-toggle="tooltip" data-placement="top" title="Enter Email">
            <input type="text" value="<?= isset($by_email)?$by_email:'' ?>" class="form-control" name="email" placeholder="Enter Email" autocomplete="off">
        </div>
        <div class="form-group col-md-3" data-toggle="tooltip" data-placement="top" title="Opt 3 month"> 
            <select class="form-control" name="optout">
                <option value="">Opt 3 month</option>
                <option <?php echo $filter_optout == 1 && $filter_optout != '' ? $select : '' ; ?> value="1">Yes</option>
                <option <?php echo $filter_optout == 0 && $filter_optout != '' ? $select : '' ; ?> value="0">No</option>
            </select>
        </div>
        <div class="form-group col-md-3" data-toggle="tooltip" data-placement="top" title="Vskills Exam">
            <select class="form-control" name="vskills">
                <option value="">Vskills Exam</option>
                <option <?php echo $filter_vskills == 1 && $filter_vskills != '' ? $select : '' ; ?> value="1">Yes</option>
                <option <?php echo $filter_vskills == 0 && $filter_vskills != '' ? $select : '' ; ?> value="0">No</option>
            </select>
        </div>
        <div class="form-group col-md-3" data-toggle="tooltip" data-placement="top" title="Blog URL">
            <select class="form-control" name="blog_url">
                <option value="">Blog URL</option>
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
        </div>
        <div class="form-group col-md-3 select_by_date" data-toggle="tooltip" data-placement="top" title="Enrollment Start Date">
            <input type="text" value="<?= $en_sdate ?>" class="datepicker_se form-control" name="en_sdate" placeholder="Enrollment Start Date" autocomplete="off">   
        </div>
        <div class="form-group col-md-3 select_by_date" data-toggle="tooltip" data-placement="top" title="Enrollment End Date">
            <input type="text" value="<?= $en_edate ?>" class="datepicker_se form-control" name="en_edate" placeholder="Enrollment End Date" autocomplete="off">
        </div>
        <div class="form-group col-md-3 select_by_date" data-toggle="tooltip" data-placement="top" title="Batch Start Date">
            <input type="text" value="<?= $batch_sdate ?>" class="datepicker_se form-control" name="batch_sdate" placeholder="Batch Start Date" autocomplete="off">   
        </div>
        <div class="form-group col-md-3 select_by_date" data-toggle="tooltip" data-placement="top" title="Batch End Date">
            <input type="text" value="<?= $batch_edate ?>" class="datepicker_se form-control" name="batch_edate" placeholder="Batch End Date" autocomplete="off">      
        </div>
        <div class="form-group col-md-3 select_by_date" data-toggle="tooltip" data-placement="top" title="Foundation Session Date">
            <input type="text" value="<?php echo $foundation_session_date; ?>" class="datepicker_se form-control" name="foundation_session_date" placeholder="Foundation Session Date" autocomplete="off">
        </div>
        <div class="form-group col-md-3" data-toggle="tooltip" data-placement="top" title="Module Allow Range From"> 
           <select class="form-control" name="modules_allowed_from">
                <option value="">Module Allow Range From</option>
                <?php for($i = 0 ; $i<=6 ; $i++){ ?>
                        <option <?php echo $modules_allowed_from == $i && $modules_allowed_from != '' ? $select : ''; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-md-3" data-toggle="tooltip" data-placement="top" title="Module Allow Range To">
           <select class="form-control" name="modules_allowed_to">
                <option value="">Module Allow Range To</option>
                <?php for($i = 0 ; $i<=6 ; $i++){ ?>
                    <option <?php echo $modules_allowed_to == $i && $modules_allowed_to != '' ? $select : ''; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-md-3" data-toggle="tooltip" data-placement="top" title="Module Completed Range From">
           <select class="form-control" name="modules_completed_from">
                <option value="">Module Completed Range From</option>
                <?php for($i = 0 ; $i<=6 ; $i++){ ?>
                    <option <?php echo $modules_completed_from == $i && $modules_completed_from != '' ? $select : ''; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-md-3" data-toggle="tooltip" data-placement="top" title="Module Completed Range To">
           <select class="form-control" name="modules_completed_to">
                <option value="">Module Completed Range To</option>
                <?php for($i = 0 ; $i<=6 ; $i++){ ?>
                    <option <?php echo $modules_completed_to == $i && $modules_completed_to != '' ? $select : ''; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-md-2" data-toggle="tooltip" data-placement="top" title="Select Participant">
            <select class="form-control" name="participant_new_old_all">
                <option value="">Select Participant</option>
                <option <?= isset($participant_new_old_all) && $participant_new_old_all =='All'?'selected="selected"':''; ?> value="All">All</option>
                <option <?= isset($participant_new_old_all) && $participant_new_old_all =='1'?'selected="selected"':''; ?> value="1">Active</option>
                <option <?= isset($participant_new_old_all) && $participant_new_old_all =='2'?'selected="selected"':''; ?> value="2">On hold</option>
                <option <?= isset($participant_new_old_all) && $participant_new_old_all =='3'?'selected="selected"':''; ?> value="3">Drop off</option>
                <option <?= isset($participant_new_old_all) && $participant_new_old_all =='4'?'selected="selected"':''; ?> value="4">Completed</option>
            </select> 
        </div>
        <div class="form-group col-md-3" data-toggle="tooltip" data-placement="top" title="Certifications">
            <select class="form-control" name="certifications" multiple="multiple" size="7">
                <option value="">Certifications</option>
                <option value="Google">Google</option>
                <option value="Vskills">Vskills Exam</option>
            </select> 
        </div>
        <div class="form-group col-md-3" data-toggle="tooltip" data-placement="top" title="Program Co-ordinator">
            <select class="form-control" name="program_coordinator">
                <option value="">Program Co-ordinator</option>
                <?php foreach($all_coordinator_data as $key => $value){ ?>
                        <option <?php echo $value == $program_coordinator && $program_coordinator != '' ? $select : ''; ?> value="<?php echo $value; ?>"><?php echo $value; ?></option>
                <?php } ?>
            </select> 
        </div>
        <div class="form-group col-md-3" data-toggle="tooltip" data-placement="top" title="Completion Month">
            <select class="form-control" name="Completion_Month">
                <option value="">Completion Month</option>
                <?php 
                $month_array = array(1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec');
                foreach($month_array as $key => $value){ ?>
                        <option value="<?php echo $value; ?>"><?php echo $value; ?></option>
                <?php } ?>
            </select> 
        </div>
        <div class="form-group col-md-3" data-toggle="tooltip" data-placement="top" title="Completion Date">
            <input type="text" value="" class="datepicker_se form-control" name="Completion_Date" placeholder="Completion Date" autocomplete="off"> 
        </div>
        
        <div class="form-group col-md-3 select_by_date" data-toggle="tooltip" data-placement="top" title="Module Start Date">
            <input type="text" value="" class="datepicker_se form-control" name="modulesdate" placeholder="Module Start Date" autocomplete="off">   
        </div>
        <div class="form-group col-md-3 select_by_date" data-toggle="tooltip" data-placement="top" title="Module End Date">
            <input type="text" value="" class="datepicker_se form-control" name="moduleedate" placeholder="Module End Date" autocomplete="off">   
        </div>
        <div class="form-group col-md-3">
            <?= Html::submitButton( '<i class="fa fa-search"></i> Search' , ['class' => 'btn btn-sm pull btn-success search_submit']) ?>
            <a href="<?php echo Url::to(['dv-delievery-members/index']); ?>">
                <button type="button" class="btn btn-sm pull btn-warning search_submit">
                    <i class="fa fa-refresh"></i> Reset</button>
            </a>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <div class="form-group col-md-12">
        <h4>Export Participant Data</h4>
        <?php
        $exl_array = array();
        $export_count = 1;
        foreach($query_export as $user){
            $batch_day='';
             
            if((trim($user['available_batch_opt']) != '' || trim($user['available_batch_opt']) != NULL) && trim($user['available_batch_opt']) != '0' && trim($user['available_batch_opt']) != '1'){
                $batch_day = trim($user['available_batch_opt']);  
            }else { 
                $batch_day = trim($user['course_batch_date']) == '0000-00-00' ? '' : strtolower(date('D',strtotime($user['course_batch_date'])));
            }

            $participant_status = '';
            //1-active, 2-on hold, 3-drop off, 4-completed
            if($user['participant_status'] == 1){
                $participant_status = 'Active';
            }else if($user['participant_status'] == 2){
                $participant_status = 'On Hold';
            }else if($user['participant_status'] == 3){
                $participant_status = 'Drop Off';    
            }else if($user['participant_status'] == 4){
                $participant_status = 'Completed';
            }
            $exl_array[] = array( 
                'id'=>$export_count,
                'Course name' => DvCourse::findOne($user['course'])->name,
                'Name'=> ucfirst($user['first_name'].' '.$user['last_name']),
                'Email id' => $user['email'],
                'Mobile' => $user['mobile'],
                'Batch Day' => $batch_day,
                'Batch Date' => $user['course_batch_date'] == '0000-00-00' ? '' : date('d-m-Y', strtotime($user['course_batch_date'])),
                'Participant Status' => $participant_status,
                'Enrollment Date' =>date('d-m-Y',strtotime($user['created_on'])),
                'Foundation Date' =>date('d-m-Y', strtotime($user['course_batch_date'])),
                'Module Allowed' => $user['modules_allowed'],
                'Module Completed' => $user['modules_completed'],
                'Active Module' =>"NA",
                'Program Coordinator' => !empty($user['program_coordinator']) ? $user['program_coordinator'] : '',
                'Sales Person' => $user['sales_person_name'],
                'Remarks' => $user['remarks']
            );
            $export_count = $export_count+1;
        }
        $excel_array = array('allModels' => $exl_array ,'pagination'=>false);
        $dataProvider = new ArrayDataProvider($excel_array);
        $columns = array('id', 'Course name','Name','Email id','Mobile','Batch Day','Batch Date','Participant Status',
            'Enrollment Date','Foundation Date','Module Allowed','Module Completed','Active Module',
            'Program Coordinator','Sales Person','Remarks');
        $file_name = 'Participant Details('.date('Y-m-d').')';
        echo ExportMenu::widget([
            'dataProvider' => $dataProvider,
            'fontAwesome' => true,
            'columns' => $columns,
            'options' => ['id'=>'expMenu1'],
            'target' => ExportMenu::TARGET_BLANK,
            'filename' => $file_name
        ]);?>
    </div>
    <div class="dv-users-index">
        <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Course Name</th>
                        <th>Name</th>
                        <th>Email id</th>
                        <th>Mobile</th>
                        <th>Batch Day</th>
                        <th>Batch Date</th>
                        <th>Participant Status</th>
                        <th>Enrollment Date</th>
                        <th>Foundation Date</th>
                        <th>Module Allowed</th>
                        <th>Module Completed</th>
                        <th>Active Module</th>
                        <th>Program Coordinator</th>
                        <th>Sales Person</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                    <tbody>
                    <?php
                    if(count($participant_users) > 0){ 
                        if(!empty($_GET['page']) && $_GET['page']!=1){
                            $i = ($_GET['page'] - 1 )*$_GET['per-page'] + 1;
                        }else{
                             $i=1;
                        }
                        foreach($participant_users as $user){ ?>
                            <tr>
                                <td>
                                    <a class="btn btn-xs btn-info" data-toggle="tooltip" data-placement="top" title="view" href="<?= Url::to("participant_view?id={$user['id']}")?>" data-original-title="View Detail"><strong><?php echo $i++; ?></strong></a>
                                </td>
                                <td><?php
                                    if(!empty($user['course'])){
                                        echo DvCourse::findOne($user['course'])->name; 
                                    } ?>
                                </td>
                                <td><?php echo ucfirst($user['first_name'].' '.$user['last_name']); ?>
                                </td>
                                <td><?php echo $user['email']; if(empty($user['program_coordinator'])) { echo "<small style='color:red;'><b><sup>New</sup></b></small>"; } ?></td>
                                <td><?php echo $user['mobile']; ?></td>
                                <td>
                                    <?php   
                                    if((trim($user['available_batch_opt']) != '' || trim($user['available_batch_opt']) != NULL) && trim($user['available_batch_opt']) != '0' && trim($user['available_batch_opt']) != '1'){
                                        echo trim($user['available_batch_opt']);  
                                    }else { 
                                        echo trim($user['course_batch_date']) == '0000-00-00' ? '' : strtolower(date('D',strtotime($user['course_batch_date'])));
                                    }  
                                    ?>
                                </td>
                                <td><?php echo trim($user['course_batch_date']) == '0000-00-00' ? '' : date('d-m-Y', strtotime($user['course_batch_date'])); ?></td>
                                <td><?php
                                $participant_status_val = '';
                                //1-active, 2-on hold, 3-drop off, 4-completed
                                if($user['participant_status'] == 1){
                                    $participant_status_val = 'Active';
                                }else if($user['participant_status'] == 2){
                                    $participant_status_val = 'On Hold';
                                }else if($user['participant_status'] == 3){
                                    $participant_status_val = 'Drop Off';    
                                }else if($user['participant_status'] == 4){
                                    $participant_status_val = 'Completed';
                                }
                                echo $participant_status_val; 
                                ?></td>
                                <td><?php echo date('d-m-Y',strtotime($user['created_on'])); ?></td>
                                <td><?php echo date('d-m-Y',strtotime($user['course_batch_date'])); ?></td>
                                <td><?php echo $user['modules_allowed']; ?></td>
                                <td><?php echo $user['modules_completed']; ?></td>
                                <td><?php echo "NA"; ?></td>
                                <td><?php 
                                    $new_coordinator_array = '';
                                    //if(count($coordinator_array) > 0){ //updated on 25 April 2019
                                    if(count($all_coordinator_data) > 0){
                                        if(!empty(trim($user['program_coordinator']))){
                                            $existing_program_coordinator = array($user['program_coordinator'] => $user['program_coordinator']);
                                            $new_coordinator_array = array_merge($all_coordinator_data,$existing_program_coordinator);
                                        }else{
                                            $new_coordinator_array = $all_coordinator_data;
                                        }
                                    }else{
                                        $new_coordinator_array = array($user['program_coordinator'] => $user['program_coordinator']);
                                    }?>
                                    <select id="Program_Coordinator_Id" onchange="change_coordinator_data(<?php echo $user['id']; ?>,this.value)">
                                        <option value="">Select Coordinator</option>
                                        <?php foreach($new_coordinator_array as $key => $value){?>
                                                <option <?php echo trim($user['program_coordinator']) == trim($key) ? 'selected="selected"' : ''; ?> value="<?php echo $value; ?>"><?php echo explode('#',$value)[0]; ?></option>
                                        <?php } ?>
                                    </select> 
                                </td>
                                <td><?php echo $user['sales_person_name']; ?></td>
                                <td><?php echo $user['remarks']; ?></td>
                            </tr><?php
                        }  
                    }else{ 
                        echo '<tr><td colspan="15"><center> <h3>No Record Found</h3> </center></td> </tr>';
                    }  ?>
                    </tbody>     
        </table>
        <?php 
            echo LinkPager::widget(['pagination' => $pages]); 
        ?>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.28.4/dist/sweetalert2.all.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $('.datepicker_se').datepicker({dateFormat:'dd-mm-yy',changeMonth: true,changeYear: true});
    });

    function change_coordinator_data(id,coordinator_name){
        var coordinator_data = coordinator_name.split("###");
        if(id!='' && coordinator_name !=''){
            swal({
                  title: 'Are you sure?',
                  text: "You want to change Co-ordinator name ?",
                  type: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'Yes, Change it!'
            }).then((result) => {
                if (result.value) {
                    $("#loading_custom").show();
                    $.ajax({
                        url: '<?php echo Url::to(['dv-delievery-members/update_coordinator'])?>',
                        type: 'POST',
                        data:{
                            participant_id:id,
                            coordinator_name:coordinator_data[0],
                            coordinator_mail:coordinator_data[1]
                        },
                        success: function(data){  
                            $("#loading_custom").hide();
                            if(data){
                                $('#Program_Coordinator_Id option[value='+coordinator_name+']').attr('selected','selected');
                            }else{
                                swal("Getting some error");
                            }
                        }
                    });
                } else {
                    //swal("Getting some error");
                }
            });
        }
    }//End of JS:alert_action()//

</script>