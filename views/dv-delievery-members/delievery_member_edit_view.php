<?php 
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use app\models\DvCourseModel;
use app\models\DvUsers;
use app\models\DvCoordinatorModel;
use app\models\DvUserMeta;
$form = ActiveForm::begin(['method' => 'post', 'action' => Url::to(['dv-delievery-members/edit_save'])]);?>
    <input type="hidden" value="<?php echo $model['id']; ?>" name="id">
    <input type="hidden" id="call_from" value="" name="call_from">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <h3 class="blue_color">Participant Details</h3>
            </div>
            <div class="form-group">
                <label>First Name</label>
                <input type="text" value="<?php echo $model['first_name']; ?>" class="form-control" name="first_name" placeholder="First Name" autocomplete="off" required="required">
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" value="<?php echo $model['last_name']; ?>" class="form-control" name="last_name" placeholder="Last Name" autocomplete="off" required="required">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="text" value="<?php echo $model['email']; ?>" class="form-control" name="email" placeholder="Mail" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Mobile</label>
                <input type="text" value="<?php echo $model['mobile']; ?>" class="form-control" name="mobile" placeholder="Phone Number (Mobile)" autocomplete="off" required="required">
            </div>
            <div class="form-group">
                <label>Course</label>
                <?php $all_course =  ArrayHelper::map(DvCourseModel::find()->where(['status'=>1])->all(), 'id', 'name'); ?>
                <select class="form-control" name="course" required="required">
                <option value="">Select</option>
                <?php foreach ($all_course as $key => $value) { ?>
                    <option <?php echo trim($model['course']) == $key ? 'selected="selected"' : ''; ?> value="<?php echo $key; ?>"><?php echo $value; ?></option>
                <?php } ?>
                </select>  
            </div>
            <div class="form-group">
                <label>Created Date</label>
                <input type="text" disabled="disabled" value="<?php echo date('d-m-Y',strtotime($model['created_on'])) ; ?>" class="form-control datepicker_se" name="created_on" placeholder="Created Date" autocomplete="off" required="required">
            </div>
            <div class="form-group">
                <?php
                //For Getting Todays Presenet Coordinator data    
                /*$today_present_coordinator = DvCoordinatorModel::find()
                                            ->where(['created_on' =>date('Y-m-d')])
                                            ->one();
                $coordinator_array = array();
                if(count($today_present_coordinator) > 0){
                    $today_present_coordinator_array = explode(',',$today_present_coordinator['coordinator_ids']);
                    //$existing_coordinator = ArrayHelper::map($today_present_coordinator,'id','id');
                    foreach ($today_present_coordinator_array as $value) {
                        $users_data = DvUsers::find()
                                    ->where(['id'=>$value])
                                    ->one();
                        if(!empty($users_data['first_name'])){
                            $coordinator_array[ucfirst($users_data['first_name'].' '.$users_data['last_name'])] = ucfirst($users_data['first_name'].' '.$users_data['last_name']);
                        }
                    }
                }

                $new_coordinator_array = '';
                if(count($coordinator_array) > 0){
                    if(!empty(trim($model['program_coordinator']))){
                        $existing_program_coordinator = array($model['program_coordinator'] => $model['program_coordinator']);
                        $new_coordinator_array = array_merge($coordinator_array,$existing_program_coordinator);
                    }else{
                        $new_coordinator_array = $coordinator_array;
                    }
                }else{
                    $new_coordinator_array = array($model['program_coordinator'] => $model['program_coordinator']);
                }*/

                /////////////////

                $all_coordinator_array =ArrayHelper::map(DvUserMeta::find()->where(['meta_key'=>'role','meta_value'=>5])->all(),'uid','uid');
                $all_coordinator_data = array();
                foreach ($all_coordinator_array as $value) {
                    $users_data = DvUsers::find()
                                ->where(['id'=>$value])
                                ->one();
                    if(!empty($users_data['first_name'])){
                        $all_coordinator_data[ucfirst($users_data['first_name'])] = ucfirst($users_data['first_name'].' '.$users_data['last_name']);
                    }
                }
                 
                ///////////////
                ?>
                <label>Program Co-ordinator</label>
                <select class="form-control" name="program_coordinator" required="required" >
                <option value="">Select</option>
                <?php foreach ($all_coordinator_data as $key => $value) { ?>
                    <option <?php echo $model['program_coordinator'] == $key ? 'selected="selected"' : ''; ?> value="<?php echo $key; ?>"><?php echo $value; ?></option>
                <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label>Foundation Session Date</label>
                <input type="text" value="<?php echo date('d-m-Y',strtotime($model['course_batch_date'])); ?>" class="form-control datepicker_se" name="course_batch_date" placeholder="Foundation Session Date" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Sales Consultant</label>
                <?php 
                $all_dv_users =  DvUsers::find()->where(['status'=>1])->all();
                $all_user_array = array();
                foreach ($all_dv_users as $key => $value) {
                    $all_user_array[$value->id] = $value->first_name.' '.$value->last_name;
                }
                ?>
                <select class="form-control" name="sales_user_id" disabled="disabled">
                <option value="">Select</option>
                <?php foreach ($all_user_array as $key => $value) { ?>
                    <option <?php echo $model['sales_user_id'] == $key ? 'selected="selected"' : ''; ?> value="<?php echo $key; ?>"><?php echo $value; ?></option>
                <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label>VSkills</label>
                <select class="form-control" name="vskills">
                    <option value="">Vskills</option>
                    <option <?php echo $model->vskills == 1  ? 'selected="selected"' : '' ; ?> value="1">Yes</option>
                    <option <?php echo $model->vskills == 0  ? 'selected="selected"' : '' ; ?> value="0">No</option>
                </select>
            </div>
            <div class="form-group">
                <label>Placement</label>
                <input type="text" value="" class="form-control" name="placement" placeholder="Placement" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Blog URL</label>
                <select class="form-control" name="blog_url">
                    <option value="">Blog URL</option>
                    <option <?php //echo $model['blog_url'] == 1  ? 'selected="selected"' : '' ; ?> value="1">Yes</option>
                    <option <?php //echo $model['blog_url'] == 0  ? 'selected="selected"' : '' ; ?> value="0">No</option>
                </select>
            </div>
            <div class="form-group">
                <label>Remarks</label>
                <input type="text" value="<?php echo $model['remarks']; ?>" class="form-control" name="remarks" placeholder="Remarks" autocomplete="off">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <h3 class="blue_color">Course</h3>
            </div>
            <div class="form-group">
                <label>Module Allowed</label>
                <select class="form-control" name="modules_allowed">
                <option value="">Select</option>
                <?php for($i = 0 ; $i<=6 ; $i++){ ?>
                    <option <?php echo $model->modules_allowed == $i && $model->modules_allowed!='' ? 'selected="selected"' : ''; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php } ?>
                </select> 
            </div>
            <div class="form-group">
                <label>Module Completed</label>
                <select class="form-control" name="modules_completed">
                <option value="">Select</option>
                <?php for($i = 0 ; $i<=6 ; $i++){ ?>
                    <option <?php echo $model->modules_completed == $i && $model->modules_completed!='' ? 'selected="selected"' : ''; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php } ?>
                </select> 
            </div>
            <div class="form-group">
                <label>Opted Batch</label>
                <?php 
                $batch_day = array(
                    'sat+sun'=>'sat+sun',
                    'sat+wd'=>'sat+wd',
                    'sun+wd'=>'sun+wd',
                    'wd'=>'wd',
                    'sat'=>'sat',
                    'sun'=>'sun'
                );
                ?>
                <select class="form-control" name="available_batch_opt">
                    <option value="">Select</option>
                    <?php foreach ($batch_day as $key => $value) { ?>
                        <option <?php echo $model->available_batch_opt == $batch_day[$key] ? 'selected="selected"' : ""; ?> value="<?php echo $value; ?>"><?php echo $value; ?></option>
                    <?php } ?> 
                </select> 
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <h3 class="blue_color">Extra Details</h3>
            </div>
            <div class="form-group">
                <label>Google plus</label>
                <input type="text" value="" class="form-control" name="google_plus" placeholder="Google plus" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Mouthshut</label>
                <input type="text" value="" class="form-control" name="mouthshut" placeholder="Mouthshut" autocomplete="off">
            </div>
            <div class="form-group">
                <label>External Review Site</label>
                <input type="text" value="" class="form-control" name="external_review_site" placeholder="External Review Site" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Dv VSklls</label>
                <input type="text" value="" class="form-control" name="dv_skills" placeholder="Dv VSklls" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Dv Certificate</label>
                <input type="text" value="" class="form-control" name="dv_certificates" placeholder="Dv Certificate" autocomplete="off">
            </div>
        </div>
    </div>
    <!-- <div class="row">
        <div class="col-md-6">
            <div class="form-group">
              <h3 class="blue_color">Batch</h3>
            </div>
            <div class="form-group">
                <label>Foundation Session Date</label>
                <input type="text" value="<?php echo date('d-m-Y',strtotime($model['course_batch_date'])); ?>" class="form-control datepicker_se" name="course_batch_date" placeholder="Foundation Session Date" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Modules Sessions Start Date</label>
                <input type="text" value="<?php //echo date('d-m-Y',strtotime($model['course_batch_date'])); ?>" class="form-control datepicker_se" name="session_start_date" placeholder="Modules Sessions Start Date" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Modules trainer name</label>
                <input type="text" value="<?php //echo $model['course_batch_date']; ?>" class="form-control" name="trainer_name" placeholder="Modules Sessions Start Date" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Modules Sessions Attendance</label>
                <input type="text" value="<?php //echo $model['course_batch_date']; ?>" class="form-control" name="session_attendance" placeholder="Modules Sessions Attendance" autocomplete="off">
            </div>
            <div class="form-group">
                <label>All alloted modules assignment details</label>
                <input type="text" value="<?php //echo $model['course_batch_date']; ?>" class="form-control" name="alloted_module_assignment" placeholder="All alloted modules assignment details" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Batch Type</label>
                <select class="form-control" name="batch_type" disabled="disabled">
                    <option value="">Select</option>
                    <option <?php echo $model->modules_completed == 1  ? 'selected="selected"' : '' ; ?> value="1">Fast</option>
                    <option <?php echo $model->modules_completed != 1  ? 'selected="selected"' : '' ; ?> value="<?php echo $model->modules_completed; ?>">Normal</option>
                </select>
            </div>
            <div class="form-group">
                <label>Completion Date</label> 
                <input type="text" value="" class="form-control datepicker_se" name="completion_date" placeholder="Completion Date" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Completion Month</label> 
                <select class="form-control" name="completion_month">
                <option value="">Completion Month</option>
                <?php 
                $month_array = array(1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec');
                foreach($month_array as $key => $value){ ?>
                        <option value="<?php echo $value; ?>"><?php echo $value; ?></option>
                <?php } ?>
                </select> 
            </div>
        </div>
    </div> -->
    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-pencil"></i> Update', ['id'=>'create_emp','class' =>'btn btn-primary']) ?>
    </div> 
<?php ActiveForm::end(); ?> 

     
 