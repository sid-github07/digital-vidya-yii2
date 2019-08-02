<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\DvModuleModel; 
use yii\widgets\LinkPager;
use yii\grid\GridView;
use kartik\select2\Select2;
$this->title = $model->isNewRecord ? "New Course" : "Edit Course"; 
$this->params['breadcrumbs'][] = $this->title; 
?>
<?php $form = ActiveForm::begin();?>
    <div class="row">
        <div class="col-md-5">
            <div class="form-group">
                <?php echo $form->field($model,'name')->input('text', ['id'=>'CourseName','placeholder' => 'Course Name', 'data-toggle'=>'tooltip', 'data-placement'=>'top', 'title'=>'Course Name','autocomplete'=>'off'])->label(false); 
                ?>
            </div>
        </div>
        <div class="col-md-4">
            <?php echo $form->field($model,'course_code')->input('text', ['placeholder' => 'Course Code', 'data-toggle'=>'tooltip', 'data-placement'=>'top', 'title'=>'Course Code','autocomplete'=>'off'])->label(false); 
            ?>
        </div>
    </div>
    <div class="row"> 
        <div class="col-md-3">
            <?php 
            echo $form->field($model,'mcourse')->dropDownList($domain_array,['placeholder' => 'Course Domain', 'data-toggle'=>'tooltip', 'data-placement'=>'top', 'title'=>'Course Domain','class'=>'form-control','prompt'=>'--- Select Course Domain ---'])->label(false); ?>
        </div>
        <div class="col-md-3">
            <?php 
            echo $form->field($model,'version')->dropDownList($version_array,['id'=>'VersionID','placeholder' => 'Version', 'data-toggle'=>'tooltip', 'data-placement'=>'top', 'title'=>'Version','class'=>'form-control','prompt'=>'--- Select Version ---'])->label(false); ?>
        </div>
        <div class="col-md-3" data-toggle='tooltip' data-placement='top' title='Speed'>
            <?php 
            echo $form->field($model, 'course_speed')->widget(Select2::classname(), [
                'data' => $course_speed_array,
                'options' => ['class'=>'form-control','multiple'=>'multiple','placeholder' => '--- Select Speed ---'],
                'pluginOptions' => [
                    'allowClear' => true
                ]
            ])->label(false);
            ?>
        </div>
    </div>
    <div class="row"> 
        <div class="col-md-4 margin-bottom-0" data-toggle='tooltip' data-placement='top' title='Core Module'>
            <label> Core Module </label>
            <?php
            echo $form->field($model, 'core_modules')->widget(Select2::classname(), [
                'data' => $model->CategoryDropdown,
                'options' => ['class'=>'form-control','multiple'=>'multiple','placeholder' => '--- Select Core Module ---','onchange'=>'set_module_type()','id'=>'core_module_id'],
                'pluginOptions' => [
                    'allowClear' => true
                ]
            ])->label(false);
            ?>
            <span class="hint-block"><small>You can select multiple core module.</small></span>
        </div>
        <div class="col-md-5" data-toggle='tooltip' data-placement='top' title='Module Type'>
            <label> Module Type </label>
            <?php 
            echo $form->field($model,'type')->dropDownList($module_type,['placeholder' => 'Module Type','data-toggle'=>'tooltip', 'data-placement'=>'top', 'title'=>'Module Type','class'=>'form-control','id'=>'module_type','prompt'=>'--- Select Module Type ---','disabled'=>'disabled'])->label(false); ?>
                <input type="hidden" name="type" id="type_id" value="<?php echo $model->type; ?>">
        </div>
    </div>
    <div class="row"> 
        <div class="col-md-4">
            <label> Foundation Module </label>
            <?php 
            echo $form->field($model,'foundation_module')->dropDownList(['Yes'=>'Yes','No'=>'No'],['placeholder' => 'Foundation Module', 'data-toggle'=>'tooltip', 'data-placement'=>'top', 'title'=>'Foundation Module','class'=>'form-control','id'=>'foundation_module','prompt'=>'--- Select Foundation Module ---'])->label(false); ?>
        </div>
        <div class="col-md-5 margin-bottom-0" data-toggle='tooltip' data-placement='top' title='Special Module'>
            <label> Special Module </label>
            <?php
            echo $form->field($model, 'special_module')->widget(Select2::classname(), [
                'data' => $model->CategoryDropdown,
                'options' => ['class'=>'form-control','multiple'=>'multiple','placeholder' => '--- Select Special Module ---'],
                'pluginOptions' => [
                    'allowClear' => true
                ]
            ])->label(false);

            ?>
            <span class="hint-block"><small>You can select multiple special module.</small></span>
        </div>
    </div>
    <input type="hidden" name="status" value="<?php echo $model->isNewRecord ? 1 : $model->status; ?>">
    <div class="form-group margin-top-20">
        <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-pencil"></i> Submit' : '<i class="fa fa-pencil"></i> Update', ['id'=>'create_emp','class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('<i class="fa fa-times"></i> Cancel', ['index'], ['class' => 'btn back_button btn-danger cancel_button']); ?>
    </div>
<?php ActiveForm::end(); ?>
<div class="row">
    <div class="col-md-2">
        <h3>Course List</h3>
    </div>
    <div class="col-md-10">
        <?php
        //declare filter variable
        $domain_filter = isset($filter_data['mcourse'])? $filter_data['mcourse'] :'' ;
        $module_type_filter = isset($filter_data['type']) ? $filter_data['type'] :'' ;
        $course_speed_filter = isset($filter_data['course_speed']) ? $filter_data['course_speed'] :'' ;
        ?> 
        <?php $form = ActiveForm::begin(['id' => 'search-form', 'method' => 'get', 'action' => Url::to(['dv-course/filter'])]); ?>
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
            <select class="form-control" name="type">
                <option value="">Type</option>
                <option <?php echo $module_type_filter == 'Self Study' ? 'selected="selected"' : ''; ?> value="Self Study">Self Study</option>
                <option <?php echo $module_type_filter == 'Instructor led' ? 'selected="selected"' : ''; ?> value="Instructor led">Instructor led</option>
            </select>
        </div>
        <div class="form-group col-md-2">
            <label></label>
            <select class="form-control" name="course_speed">
                <option value="">Speed</option>
                <option <?php echo $course_speed_filter == 'Fast' ? 'selected="selected"' : ''; ?> value="Fast">Fast</option>
                <option <?php echo $course_speed_filter == 'Normal' ? 'selected="selected"' : ''; ?> value="Normal">Normal</option>
            </select>
        </div>
        <div class="form-group col-md-3"><br>
            <?= Html::submitButton( '<i class="fa fa-search"></i> Search' , ['class' => 'btn btn-sm pull btn-success search_submit']) ?>
            <a href="<?php echo Url::to(['dv-course/index']); ?>">
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
                <th>Course Name</th>
                <th>Code</th>
                <th>Domain</th>
                <th>Version</th>
                <th>Speed</th>
                <th>Foundation Module</th>
                <th>Type</th>
                <th>Core Module</th>
                <th>Special Module</th>
                <th>Status</th>
                <th>Edit</th>
            </tr>
        </thead>
        <tbody>
        <?php if(count($course_details) > 0){ ?>    
            <?php
            if(!empty($_GET['page']) && $_GET['page']!=1){
                $i = ($_GET['page'] - 1 )*$_GET['per-page'] + 1;
            }else{
                $i=1;
            }
            foreach($course_details as $course){ ?> 
                <tr>
                    <td>
                        <a class="btn btn-xs btn-info" href="#"><strong><?php echo $i++; ?></strong></a>
                    </td>
                    <td><?php echo $course['name'];?></td>
                    <td><?php echo $course['course_code'];?></td>
                    <td><?php echo strtoupper($course['mcourse']); ?></td>
                    <td><?php echo $course['version'];?></td>
                    <td><?php echo $course['course_speed'];?></td> 
                    <td><?php echo $course['foundation_module'] !='' ? $course['foundation_module'] : ''; ?></td> 
                    <td><?php echo $course['type'];?></td> 
                    <td>
                        <?php
                        if(!empty($course['core_modules'])){
                            $core_array = array();
                            $core_exist = explode(',',$course['core_modules']);
                            foreach ($module_data as $key => $value) {
                                if(in_array($key, $core_exist)){
                                    $core_array[] = $value;
                                }
                            }
                            echo  count($core_array) > 0 ? implode(',<br>', $core_array) : "";
                        }
                        ?>
                    </td> 
                    <td>
                        <?php
                        if(!empty($course['special_module'])){
                            $special_array = array();
                            $special_exist = explode(',',$course['special_module']);
                            foreach ($module_data as $key => $value) {
                                if(in_array($key, $special_exist)){
                                    $special_array[] = $value;
                                }
                            }
                            echo  count($special_array) > 0 ? implode(',<br>', $special_array) : "";
                        }
                        ?>
                    </td> 
                    <td>
                    <?php if($course['status'] == 1){ ?>
                        <a onclick="alert_action(<?php echo $course['id']; ?>,1)" class="course_status_<?php echo $course['id']; ?>" href="javascript:void(0);">
                            <i class="fa fa-check-circle green_icon"></i>
                        </a>
                    <?php }else{ ?>
                        <a onclick="alert_action(<?php echo $course['id']; ?>,0)" class="course_status_<?php echo $course['id']; ?>" href="javascript:void(0);">
                            <i class="fa fa-eye-slash red_icon"></i>
                        </a>
                    <?php } ?>
                    <input type="hidden" id="status_txt<?php echo $course['id']; ?>" value="">
                    </td>
                    <td>
                        <a onclick="get_edit(<?php echo $course['id']; ?>)" data-toggle="tooltip" data-placement="top" title="Edit" href="#"><i class="fa fa-pencil"></i></a>
                    </td>
                </tr><?php 
            }?><?php 
        }else{
            echo '<tr><td colspan="12"><center> <h3>No Record Found</h3> </center></td> </tr>';
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
    $(document).ready(function(){
        $("#CourseName").keyup(function(){  
            if($('#CourseName').val() !=''){
                $.ajax({
                    url: '<?php echo Url::to(['dv-course/check_course_version'])?>',
                    type: 'POST',
                    data:{
                        name:$('#CourseName').val()
                    },
                    success: function(data){  
                        $('#VersionID').html(data);
                    }
                }); 
            }else{
                $('#VersionID').val('');
            }    
        });
    });
    function set_module_type(){
        if($('#core_module_id').val() !=''){
            $("#loading_custom").show();
            $.ajax({
                url: '<?php echo Url::to(['dv-course/get_course_type'])?>',
                type: 'POST',
                data:{
                    core_module_id:$('#core_module_id').val()
                },
                success: function(data){  
                    $("#loading_custom").hide();
                    if(data){ 
                        $('#type_id').val('Instructor led');
                        $('#module_type option:contains(Instructor led)').prop('selected',true);
                    }else{
                        $('#type_id').val('Self Study');
                        $('#module_type option:contains(Self Study)').prop('selected',true);
                    }
                }
            });
        }else{
            $('#module_type').val('');
            $('#type_id').val('');
        }
    }//End of JS:set_module_type()//
    function alert_action(id,status){
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
                    url: '<?php echo Url::to(['dv-course/course_delete'])?>',
                    type: 'GET',
                    data:{id:id},
                    success: function(data){
                        $("#loading_custom").hide();
                        if(data == "activate"){
                            $('#status_txt'+id).val("");
                            $('#status_txt'+id).val("Disable");
                            $("a.course_status_"+id).attr("title","Enable");
                            $(".course_status_"+id).html('<i class="fa fa-check-circle green_icon"></i>');
                        }else if(data == "deactivate"){
                            $('#status_txt'+id).val("");
                            $('#status_txt'+id).val("Enable");
                            $("a.course_status_"+id).attr("title","Disable");
                            $(".course_status_"+id).html('<i class="fa fa-eye-slash red_icon"></i>');
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
        $('#type_id').val($('#module_type').val());
        $.ajax({
            url: '<?php echo Url::to(['dv-course/course_edit'])?>',
            type: 'GET',
            data:{id:id},
            success: function(data){ }
        });
    }//End of JS:et_edit()//
</script>