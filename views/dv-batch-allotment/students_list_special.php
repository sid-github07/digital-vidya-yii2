<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\DvCourse;
use app\models\DvModuleModel;
use yii\web\Session;
use yii\helpers\Url;
use yii\widgets\LinkPager;
$this->title = "Unallocated Students list - Special Module";
$this->params['breadcrumbs'][] = "Unallocated Students list - Special Module"; ?>
<!-- <h4> 
	<span style="color: green;"> <b> <button class="btn btn-info"> Module</button> </b> </span>
	<button class="btn btn-success"><?php //echo $batch_allotment_details; ?></button> 
</h4> -->
<div class="table-responsive">
	<?php  
	//$students_id = isset($_GET['students_id']) ? explode(',', $_GET['students_id']) : '';
	//$students_id = implode(',',$students_id);
	$students_id = isset($_GET['students_id']) ? $_GET['students_id'] : '';
	$form = ActiveForm::begin(['id' => 'search-form', 'method' => 'get', 'action' => Url::to(['dv-batch-allotment/students_filter_special'])]); ?>
	<input type="hidden" name="students_id" id="students_id" value="<?php echo $students_id; ?>">
	<div class="form-group col-md-2" data-toggle="tooltip" data-placement="top" title="Status">
    	<select class="form-control" name="status" id="status">
    		<option value="">Status</option>
    		<option selected="selected" value="Active">Active</option>
    	</select>	
    </div>
    <div class="form-group col-md-2" data-toggle="tooltip" data-placement="top" title="Email Id">
    	<input type="text" name="email_search" id="email_search" value="<?php echo isset($filter_array['email_search']) ? $filter_array['email_search']: ''; ?>" autocomplete="off" class="form-control" placeholder="Email" >
    </div>
    <div class="form-group col-md-3" data-toggle="tooltip" data-placement="top" title="Opted Day">
        <?php 
 		$available_batch_opt = isset($filter_array['available_batch_opt']) ? $filter_array['available_batch_opt'] : '';
        $batch_day = array(
            'sat+sun'=>'sat+sun',
            'sat+wd'=>'sat+wd',
            'sun+wd'=>'sun+wd',
            'wd'=>'wd',
            'sat'=>'sat',
            'sun'=>'sun'
        );
        ?>
        <select class="form-control" id="available_batch_opt" name="available_batch_opt">
            <option value="">Opted Day</option>
            <?php foreach ($batch_day as $key => $value) { ?>
                <option <?php echo $available_batch_opt == $batch_day[$key] ? 'selected="selected"' : ""; ?> value="<?php echo $value; ?>"><?php echo $value; ?></option>
            <?php } ?> 
        </select> 
	</div>
  	<div class="form-group col-md-3">
        <?= Html::submitButton( '<i class="fa fa-search"></i> Search' , ['id'=>'submit_btn','class' => 'btn btn-sm pull btn-success search_submit','data-toggle'=>"tooltip" ,'data-placement'=>"top" ,'title'=>"Search"]) ?>
        <a href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="Reset">
            <button type="button" onclick="reset_data();" class="btn btn-sm pull btn-warning search_submit">
                <i class="fa fa-refresh"></i> Reset</button>
        </a>
    </div>
	<?php ActiveForm::end(); ?>
  	<div class="form-group col-md-5">
		<input type="button" name="Allocate Batch" value="Allocate Batch" onclick="get_batchs_data();" class="btn btn-warning" data-toggle="tooltip" data-placement="top" title="Allocate Batch">
	</div>
	<table class="table table-striped" style="width:100%">
	    <thead>
	        <tr> 
	            <th><input type="checkbox" name="checkAll" id="checkAll"/></th>
	            <th>Alloted Uncompleted Special Modules</th>
	            <th>Special Completed Module</th>
	            <th>Running Module</th>
	            <th>Course Status</th>
	            <th>Name</th>
	            <th>Email</th>
	            <th>Last Date</th>
	            <th>Program Coordinator</th>
	            <th>Opted Days </th>
	            <th>Remarks</th>
	        </tr>
	    </thead>
	    <tbody>
		    <?php if(count($module) > 0){ 
		    	$cnt = 1;  
		    	foreach($module as $value){?> 
		            <tr>
		            	<td><input type="checkbox" value="<?php echo $cnt.'@'.$value['id']; ?>" name="single_check" id="single_check<?php echo $cnt; ?>"/></td>
		            	<td><?php echo rtrim($unallocated_special_batch[$value['id']],',<br>'); ?>
		            	</td>
		            	<td><?php 
		            	$complt_spl_modl_name = '';
		            		foreach ($common_special_batch_data as $key => $val) {
		            			if(in_array($value['id'],explode(',',$val['ids']))){
		            				echo $val['completed_special_modules'];
		            				break;
		    					}
	    					}
		            	?></td>
		            	<td>
		            	<?php 
	            			echo $running_batch_array[$value['id']];
	            		?>
	            		</td>
		            	<td><?php echo "Active"; ?></td>
		            	<td><?php echo $value['first_name'].' '.$value['last_name']; ?></td>
		            	<td><?php echo $value['email']; ?></td>
		            	<td><?php echo ""; ?></td>
		            	<td><?php echo $value['program_coordinator']; ?></td>
		            	<td><?php 
		            		if(!empty(trim($value['available_batch_opt']))){
				                echo $value['available_batch_opt'];
				            }else{
				                echo strtolower(date('D',strtotime($value['course_batch_date']))); 
				            } 
				            ?>
				        </td>
		            	<td><?php echo $value['remarks']; ?></td>
		            </tr>
		        	<?php $cnt++; 
		    	}
		    }else{
		    	echo '<tr><td colspan="10"><center> <h3>No Record Found</h3> </center></td> </tr>';
		    }  ?>
	    </tbody>
    </table>
    <?php echo LinkPager::widget(['pagination' => $pages]); ?>
</div>
<?php $form = ActiveForm::begin(['method' => 'get', 'id'=>'submit_form','action' => Url::to(['dv-batch-allotment/display_special_batch_list'])]);?>
		<input type="hidden" name="total_selected" id="total_students_frm" value="">
		<input type="hidden" name="all_students_ids" id="all_students_id_frm" value="">
<?php ActiveForm::end(); ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.28.4/dist/sweetalert2.all.min.js"></script>
<script>
	$(document).ready(function(){
		$("#checkAll").click(function(){
			$('input:checkbox').not(this).prop('checked', this.checked);
		});
		$('.datepicker_se').datepicker({dateFormat:'dd-mm-yy',changeMonth: true,changeYear: true});
    });

	//For get batch data
	function get_batchs_data(){
		var all_ids = [];
		var students_id = [];
		$('input:checkbox[name=single_check]').each(function(){    
		    if($(this).is(':checked')){
		    	var splt_val = '';
		    	splt_val = $(this).val().split('@');
		    	all_ids.push(splt_val[0]);
		    	students_id.push(splt_val[1]);
		    }
		});
 		if(all_ids.length > 0){
			$("#loading_custom").show();
			//Set empty data
			$('#total_students_frm').val('');
			$('#all_students_id_frm').val('');
			//set actual values
			$('#total_students_frm').val(all_ids.length);
			$('#all_students_id_frm').val(students_id.toString());
			//submit data
			submit_data();  
 		}else{
        	swal('Proper selection required !');
        } 
	}//End of JS:get_batchs_data()//
	

	//submit data
	function submit_data(){
		document.getElementById("submit_form").submit();
	}

 	//For Reset Data
	function reset_data(){
 		var ids = $('#students_id').val();
		window.location.href = "<?php echo Url::to(['dv-batch-allotment/student_list_special'])?>?students_id="+ids;
	}//End of JS:reset_data()//
</script>