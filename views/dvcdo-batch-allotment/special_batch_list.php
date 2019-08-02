<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\DvCourse;
use yii\web\Session;
use yii\helpers\Url;
use yii\widgets\LinkPager;
$batchs = !empty($batch_allotment_details) ? $batch_allotment_details: '';
$this->title = "Ongoing Special Batch list";  
$this->params['breadcrumbs'][] = $this->title; ?>
<!-- <h4> 
	<span style="color: green;"> <b> <button class="btn btn-info"> Module</button> </b> </span>
	<button class="btn btn-success"><?php //echo $batch_allotment_details; ?></button> 
</h4> -->
 
<div class="table-responsive"> 
	<button class="btn btn-success pull-left">Number of students : <?php echo $total_selected; ?></button>
	<button class="btn btn-warning pull-right" id="allocate_students_ids" onclick="batch_allocat_students();"> Allocate Student </button>
</div>
<br>
<div class="row">
	<div class="col-md-12">
		<div class="table-responsive">
	        <table class="table table-striped" style="width:100%">
	            <thead>
	                <tr> 
	                    <th>#<!-- <input type="radio" name="checkAll" id="checkAll"/> --></th>
	                    <th>Upcoming Module</th>
	                    <th>Date</th>
	                    <th>Day</th>
	                    <th>Timing</th>
	                    <th>Trainer</th>
	                    <th>Total Seats</th>
	                    <th>Open Seats</th>
	                    <th>Alloted Seats</th>
	                </tr>
	            </thead>
	            <tbody>
	            <?php
 					if(count($batch_data) > 0){ 
	            	$cnt = 1;
	            	$query_batch = Yii::$app->db->createCommand("SELECT count(pid) as nos_of_student,batch_id FROM assist_participant_batch_meta GROUP BY batch_id")->queryAll();
	            	$batch_allocated_array = array();
	            	for($i=0;$i<count($query_batch);$i++){
	            		$batch_allocated_array[$query_batch[$i]['batch_id']] = $query_batch[$i]['nos_of_student'] ;
	            	}  	
	            	foreach($batch_data as $value){?> 
		                    <tr>
		                    	<td><input type="radio" value="<?php echo $value['id']; ?>" name="single_check" id="single_check<?php echo $cnt; ?>"/></td>
		                    	<td><?php echo !empty($value['module_name']) ? $value['module_name'] : ''; ?></td>
		                    	<td><?php echo !empty($value['start_date']) ? date('d-M',strtotime($value['start_date'])) : ''; ?></td> 
		                    	<td><?php echo !empty($value['day']) ? ucfirst($value['day']) : ''; ?></td>
		                    	<td><?php echo !empty($value['stiming']) ? $value['stiming'] : ''; ?></td>
		                    	<td><?php echo !empty($value['trainer_name']) ? ucfirst($value['trainer_name']) : ''; ?></td>
		                    	<td><?php echo !empty($value['seats']) ? $value['seats'] : ''; ?></td>
		                    	<td><?php echo isset($batch_allocated_array[$value['id']]) ? $value['seats'] - $batch_allocated_array[$value['id']] : $value['seats'] ;   ?></td>
		                    	<td><?php echo isset($batch_allocated_array[$value['id']]) ? $batch_allocated_array[$value['id']] : '0';  ?></td>
		                    </tr>
		                    <input type="hidden" name="open_seats" id="open_seats<?php echo $value['id']; ?>" value="<?php echo isset($batch_allocated_array[$value['id']]) ? $value['seats'] - $batch_allocated_array[$value['id']] : $value['seats'] ;   ?>">
		            	<?php 
		            	$cnt++; 
		            }
	            }else{
	            	echo '<tr><td colspan="9"><center> <h3>No Record Found</h3> </center></td> </tr>';
	            } ?>
	            </tbody>
	        </table>
	        <?php echo LinkPager::widget(['pagination' => $pages]); ?>
		</div>
	</div>
</div>
<?php $form = ActiveForm::begin(['method' => 'post', 'id'=>'submit_form','action' => Url::to(['dv-batch-allotment/allocate_students_special_batch'])]);?>
	<input type="hidden" name="all_students" id="all_students_frm" value="<?php echo isset($all_students_ids) ? $all_students_ids :''; ?>">
	<input type="hidden" name="batch_id" id="batch_id_frm" value="">
	<input type="hidden" name="total_selected" id="total_selected_id_frm" value="<?php echo $total_selected; ?>">
	<input type="hidden" name="batch_allotment_details" id="batch_allotment_details_frm" value="<?php echo $batch_allotment_details; ?>">
<?php ActiveForm::end(); ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.28.4/dist/sweetalert2.all.min.js"></script>
<script>
	$(document).ready(function(){});
 	//For batch allocate to students
    function batch_allocat_students(){
 		var batch_id = $('input[name=single_check]:checked').val();
    	var all_students_id = $('#all_students_frm').val().toString();
    	var total_selected = $('#total_selected_id_frm').val();
    	var total_open_seats = $('#open_seats'+batch_id).val();
    	var students_array = all_students_id.split(',');
 		//If Open Seats is greater then total students then its working other wise not working 
 		if(all_students_id!='' && batch_id != '' && batch_id != undefined && total_open_seats >= students_array.length){
	    	$("#loading_custom").show();
	    	$('#batch_id_frm').val('');
	    	$('#batch_id_frm').val(batch_id); 
	    	//submit function
	    	submit_data();
    	}else{
    		swal("Either Students are too many or proper selection required !");
    	}

	}//End of JS:batch_allocat_students//

	//submit data
	function submit_data(){
		document.getElementById("submit_form").submit();
	}

</script>