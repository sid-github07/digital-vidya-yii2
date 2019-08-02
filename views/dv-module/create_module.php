<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\LinkPager;
use app\models\DvModuleModel;
use kartik\select2\Select2;//for multiple dropdown with search functionality.
$this->title = $model->isNewRecord ? "New Module" : "Edit Module"; 
$this->params['breadcrumbs'][] = $this->title; 
?>
<?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-md-5">
            <div class="form-group">
                <?php echo $form->field($model,'module_name')->input('text', ['placeholder' => 'Module Name', 'data-toggle'=>'tooltip', 'data-placement'=>'top','autocomplete'=>'off' ,'title'=>'Module Name'])->label(false); 
                ?>
            </div>
        </div>
        <div class="col-md-4">
            <?php 
            $selected_module = $model->module_type;
            echo $form->field($model,'module_type')->dropDownList($module_type,['placeholder' => 'Module Type', 'data-toggle'=>'tooltip', 'data-placement'=>'top', 'title'=>'Module Type','class'=>'form-control','prompt'=>'--- Select Module Type ---'])->label(false); ?>
        </div>
    </div>
    <div class="row"> 
        <div class="col-md-3">
            <?php 
            echo $form->field($model,'category_type')->dropDownList($module_category_type,['placeholder' => 'Category Type', 'data-toggle'=>'tooltip', 'data-placement'=>'top', 'title'=>'Category Type','class'=>'form-control','prompt'=>'--- Select Category ---'])->label(false); ?>
        </div>
        <div class="col-md-3">
             <?php 
            echo $form->field($model,'mcourse')->dropDownList($domain_array,['placeholder' => 'Course Domain', 'data-toggle'=>'tooltip', 'data-placement'=>'top', 'title'=>'Course Domain','class'=>'form-control','prompt'=>'--- Select Course Domain ---'])->label(false); ?>
        </div>
        <div class="col-md-3">
            <?php 
            echo $form->field($model,'number_of_weeks')->dropDownList($number_of_weeks_array,['placeholder' => 'Weeks', 'data-toggle'=>'tooltip', 'data-placement'=>'top', 'title'=>'Weeks','class'=>'form-control','prompt'=>'--- Select Weeks ---'])->label(false); ?>
        </div>
    </div>
    <?php
    // Get cURL resource
    $curl = curl_init();
    // Set some options - we are passing in a useragent too here
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'http://dev.digitalvidya.com/training/wp-json/course_list/v1/ld/',
        CURLOPT_USERAGENT => 'Get course data',
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => [
            'get_course' => 'get_course',
        ]
    ]);
    // Send the request & save response to $resp
    $resp = curl_exec($curl);
    // Close request to clear up some resources
    $result = json_decode($resp,true);
    // $result= array(""=>"--- Select LMS Course ---") + $resulst;
    curl_close($curl);
    ?>
    <div class="row"> 
        <div class="col-md-5" data-toggle='tooltip' data-placement='top' title='LMS Course'>
            <label>LMS Course</label>
            <?php 
            echo $form->field($model, 'lms_course')->widget(Select2::classname(), [
                'data' => $result,
                'options' => ['class'=>'form-control','multiple'=>'multiple','placeholder' => '--- Select LMS Course ---'],
                'pluginOptions' => [
                    'allowClear' => true
                ]
            ])->label(false);
            ?>
        </div>
        <div class="col-md-4 form-group prerequisite_module_class margin-bottom-0" data-toggle='tooltip' data-placement='top' title='Pre-requisite Module'>
            <label>Pre-requisite Module</label>
            <?php
            echo $form->field($model, 'prerequisite_module')->widget(Select2::classname(), [
                'data' => $model->CategoryDropdown,
                'options' => ['class'=>'form-control','multiple'=>'multiple','placeholder' => '--- Select Pre-requisite Module ---'],
                'pluginOptions' => [
                    'allowClear' => true
                ]
            ])->label(false);
            ?>
            <span class="hint-block"><small>You can select multiple pre-requisite module.</small></span>
        </div>
    </div>
    <input type="hidden" name="status" value="<?php echo $model->isNewRecord ? 1 : $model->status; ?>">
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-pencil"></i> Submit' : '<i class="fa fa-pencil"></i> Update', ['id'=>'create_emp','class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('<i class="fa fa-times"></i> Cancel', ['index'], ['class' => 'btn back_button btn-danger cancel_button']); ?>
    </div>
<?php ActiveForm::end(); ?>
<div class="row">
    <div class="col-md-2">
        <h3>Module List</h3>
    </div>
    <div class="col-md-10">
        <?php
        //declare filter variable
        $domain_filter = isset($filter_data['mcourse'])? $filter_data['mcourse'] :'' ;
        $module_type_filter = isset($filter_data['module_type']) ? $filter_data['module_type'] :'' ;
        $category_type_filter = isset($filter_data['category_type']) ? $filter_data['category_type'] :'' ;
        ?> 
        <?php $form = ActiveForm::begin(['id' => 'search-form', 'method' => 'get', 'action' => Url::to(['dv-module/filter'])]); ?>
        <div class="form-group col-md-3">
            <label></label>
            <select class="form-control" name="mcourse">
                <option value="">Domain</option>
                <option <?php echo $domain_filter == 'da' ? 'selected="selected"' : ''; ?> value="da">Digital Analytics</option>
                <option <?php echo $domain_filter == 'dm' ? 'selected="selected"' : ''; ?> value="dm">Digital Marketing</option>
            </select>
        </div>
        <div class="form-group col-md-3">
            <label></label>
            <select class="form-control" name="module_type">
                <option value="">Type</option>
                <option <?php echo $module_type_filter == 'Self Study' ? 'selected="selected"' : ''; ?> value="Self Study">Self Study</option>
                <option <?php echo $module_type_filter == 'Instructor led' ? 'selected="selected"' : ''; ?> value="Instructor led">Instructor led</option>
            </select>
        </div>
        <div class="form-group col-md-2">
            <label></label>
            <select class="form-control" name="category_type">
                <option value="">Category</option>
                <option <?php echo $category_type_filter == 'Special' ? 'selected="selected"' : ''; ?> value="Special">Special</option>
                <option <?php echo $category_type_filter == 'Core' ? 'selected="selected"' : ''; ?> value="Core">Core</option>
                <option <?php echo $category_type_filter == 'Foundation' ? 'selected="selected"' : ''; ?> value="Foundation">Foundation</option>
            </select>
        </div>
        <div class="form-group col-md-3"><br>
            <?= Html::submitButton( '<i class="fa fa-search"></i> Search' , ['class' => 'btn btn-sm pull btn-success search_submit']) ?>
            <a href="<?php echo Url::to(['dv-module/index']); ?>">
                <button type="button" class="btn btn-sm pull btn-warning search_submit">
                    <i class="fa fa-refresh"></i> Reset</button>
            </a>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<div class="table-responsive">
    <table class="table table-striped" style="width:100%">
        <thead>
            <tr> 
                <th>#</th>
                <th>Module Name</th>
                <th>Type</th>
                <th>Domain</th>
                <th>Category</th>
                <th>Week</th>
                <th>LMS Course</th>
                <th>Pre-requisite Module</th>
                <th>Status</th>
                <th>Edit</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($module_details) > 0){  
                if(!empty($_GET['page']) && $_GET['page']!=1){
                    $i = ($_GET['page'] - 1 )*$_GET['per-page'] + 1;
                }else{
                     $i=1;
                }
                $module_data = ArrayHelper::map(DvModuleModel::find()->all(), 'id', 'module_name');

                foreach($module_details as $module){?> 
                    <tr>
                        <td>
                            <a class="btn btn-xs btn-info" href="#" ><strong><?php echo $i++; ?></strong></a>
                        </td>
                        <td><?php echo $module['module_name'];?></td>
                        <td><?php echo $module['module_type'];?></td>
                        <td><?php echo $module['mcourse']; ?></td>
                        <td><?php echo $module['category_type'];?></td>
                        <td><?php echo $module['number_of_weeks'];?></td>
                        <td>
                            <?php
                             if(!empty($module['lms_course'])){
                                    $lms_course_array = array();
                                    $lms_course_result = explode(',',$module['lms_course']);
                                    
                                    if($result){
                                        foreach ($lms_course_result as $key => $value) {
                                            if(array_key_exists($value, $result)){
                                                $lms_course_array[] = $result[$value];
                                            }
                                        }
                                    }

                                    echo  count($lms_course_array) > 0 ? implode(',<br>', $lms_course_array) : "";
                                }
                            ?>
                        </td> 
                        <td><?php  
                            if(!empty($module['prerequisite_module'])){
                                $new_prerequisite_array = array();
                                $prerequisite_array = explode(',',$module['prerequisite_module']);
                                foreach ($module_data as $key => $value) {
                                    if(in_array($key, $prerequisite_array)){
                                        $new_prerequisite_array[] = $value;
                                    }
                                }
                                echo  count($new_prerequisite_array) > 0 ? implode(',<br>', $new_prerequisite_array) : "";
                            }
                        ?></td>
                        <td>
                        <?php if($module['status'] == 1){ ?>
                            <a onclick="alert_action(<?php echo $module['id']; ?>,1)" class="module_status_<?php echo $module['id']; ?>" href="javascript:void(0);">
                                <i class="fa fa-check-circle green_icon"></i>
                            </a>
                        <?php }else{ ?>
                            <a onclick="alert_action(<?php echo $module['id']; ?>,0)"   class="module_status_<?php echo $module['id']; ?>" href="javascript:void(0);">
                                <i class="fa fa-eye-slash red_icon"></i>
                            </a>
                        <?php } ?>
                        <input type="hidden" id="status_txt<?php echo $module['id']; ?>" value="">
                        </td>
                        <td>
                            <a onclick="get_edit(<?php echo $module['id']; ?>)" data-toggle="tooltip" data-placement="top" title="Edit" href="#"><i class="fa fa-pencil"></i></a>
                        </td>
                    </tr><?php 
                }  
            }else{ 
                echo '<tr><td colspan="8"><center> <h3>No Record Found</h3> </center></td> </tr>';
            }
            ?>
        <tbody> 
    </table>
    <?php  
        echo LinkPager::widget(['pagination' => $pages]);
    ?>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.28.4/dist/sweetalert2.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.7.5/js/bootstrap-select.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.7.5/css/bootstrap-select.min.css">
<script>
    function alert_action(id,status){
        //txt = (status==1)?'Disable':'Enable';
        txt = $('#status_txt'+id).val() != '' ? $('#status_txt'+id).val() : status==1 ? 'Disable' : 'Enable';
        swal({
              title: 'Are you sure?',
              text: "You want to change status to "+txt+"?",
              type: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Yes, Change it!'
        }).then((result) => {
            if (result.value) {
                $("#loading_custom").show();
                $.ajax({
                    url: '<?php echo Url::to(['dv-module/module_delete'])?>',
                    type: 'GET',
                    data:{id:id},
                    success: function(data){
                        $("#loading_custom").hide();
                        if(data == "activate"){
                            $('#status_txt'+id).val("");
                            $('#status_txt'+id).val("Disable");
                            $("a.module_status_"+id).attr("title","Enable");
                            $(".module_status_"+id).html('<i class="fa fa-check-circle green_icon"></i>');
                        }else if(data == "deactivate"){
                            $('#status_txt'+id).val("");
                            $('#status_txt'+id).val("Enable");
                            $("a.module_status_"+id).attr("title","Disable");
                            $(".module_status_"+id).html('<i class="fa fa-eye-slash red_icon"></i>');
                        }else{
                            swal("Getting some error");
                        }
                    }
                });
            } else {
                //swal("Your imaginary file is safe!");
            }
        });
    }//End of JS:alert_action()//
    function get_edit(id){
        $.ajax({
            url: '<?php echo Url::to(['dv-module/module_edit'])?>',
            type: 'GET',
            data:{id:id},
            success: function(data){ }
        });
    }//End of JS:get_edit()//
</script>