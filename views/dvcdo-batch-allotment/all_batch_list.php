<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\DvCourse;
use yii\web\Session;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = "Upcoming Batches";  
$this->params['breadcrumbs'][] = $this->title; ?>
<div class="row">
	<div class="col-md-12">
		<div class="table-responsive">
	        <div class="form-group">
	        	<button type="button" onclick="get_batch_edit();" value="Update Batch Info" class="btn btn-sm pull btn-info search_submit">Update Batch Info</button>
	        	<!-- <button type="button" onclick="get_students_edit();" value="Update Participant Info" class="btn btn-sm pull btn-primary search_submit">Update Students</button> -->
	 		</div>
	        <table class="table table-striped" style="width:100%">
	            <thead>
	                <tr> 
	                    <th>#</th>
	                    <th>Upcoming Module</th>
	                    <th>Date</th>
	                    <th>Day</th>
	                    <th>Timing</th>
	                    <th>Trainer</th>
	                    <th>Total Seats</th>
	                    <th>Open Seats</th>
	                    <th>Alloted Seats</th>
	                    <th>Allowed</th>
	                    <th>Not Allowed</th>
	                </tr>
	            </thead>
	            <tbody>
	            <?php if(count($all_batch_data) > 0){ 
	            	$cnt = 1;
	            	$query_batch = Yii::$app->db->createCommand("SELECT count(pid) as nos_of_student,batch_id,pid FROM assist_participant_batch_meta GROUP BY batch_id")->queryAll();
	            	
	            	$batch_allocated_array = array();
	            	
	            	for($i=0;$i<count($query_batch);$i++){
	            		$batch_allocated_array[$query_batch[$i]['batch_id']]['batch_id'] = $query_batch[$i]['nos_of_student'];
	            		
	            		$batch_meta_result = Yii::$app->db->createCommand("SELECT * FROM assist_participant_batch_meta where batch_id=".$query_batch[$i]['batch_id'])->queryAll();
	            		foreach($batch_meta_result as $val_res){
		            		$batch_allocated_array[$query_batch[$i]['batch_id']]['participant_id'][] = $val_res['pid'];
		            	}
	            	}
	            	//array_unique($batch_allocated_array);
	            	/*echo "<pre>";
	            	print_r($batch_allocated_array);
	            	die;*/
	            	
	            	?>
	                <?php foreach($all_batch_data as $value){

	                	$list = (isset($batch_allocated_array[$value['id']]['participant_id']))?$batch_allocated_array[$value['id']]['participant_id']:array();

	                	$allowed = 0;
		            	$not_allowed = 0;
		            	$allowed_students = array();
		            	$not_allowed_students = array();
		            	$pid_batch_meta_count = '';
		            	 
						foreach($list as $parti_val){
							$get_students = Yii::$app->db->createCommand("SELECT * FROM assist_participant 
								where participant_status = 1 and id=".$parti_val)->queryOne();
							$pid_batch_meta_count = Yii::$app->db->createCommand("SELECT count(pid) as pid FROM assist_participant_batch_meta WHERE pid =".$parti_val)->queryOne()['pid'];
 							if($get_students['course'] == 1){
 								if($get_students['modules_allowed'] >= $pid_batch_meta_count){
 									
		            				//$get_students['modules_allowed'] == 6
		            				//allowed >= count 
		            				if(!in_array($parti_val, $allowed_students)){
			            				$allowed++;
			            				$allowed_students[] = $parti_val;
									}
		            			}else{
		            				if(!in_array($parti_val, $not_allowed_students)){
			            				$not_allowed++;
			            				$not_allowed_students[] = $parti_val;
			            			}
		            			}
		            		}else if($get_students['course'] == 2){
		            			if($get_students['modules_allowed'] >= $pid_batch_meta_count){
		            				//$get_students['modules_allowed'] == 5
		            				if(!in_array($parti_val, $allowed_students)){
			            				$allowed++;
			            				$allowed_students[] = $parti_val;
			            			}
		            			}else{
		            				if(!in_array($parti_val, $not_allowed_students)){
		            					$not_allowed++;
			            				$not_allowed_students[] = $parti_val;
			            			}
		            			}
		            		}else{
		            			if($get_students['modules_allowed'] >= $pid_batch_meta_count){
		            				//$get_students['modules_allowed'] == 5
		            				if(!in_array($parti_val, $allowed_students)){
			            				$allowed++;
			            				$allowed_students[] = $parti_val;
			            			}
		            			}else{
		            				if(!in_array($parti_val, $not_allowed_students)){
		            					$not_allowed++;
			            				$not_allowed_students[] = $parti_val;
			            			}
		            			}
		            		}
		            	}
 						$allowed_students = implode(',',$allowed_students);
		            	$not_allowed_students = implode(',',$not_allowed_students);
		            	?> 
		                    <tr>
		                    	<td><input type="radio" value="<?php echo $value['id']; ?>" name="single_check" id="single_check"/></td>
		                    	<td><?php echo !empty($value['module_name']) ? $value['module_name'] : ''; ?></td>
		                    	<td><?php echo !empty($value['start_date']) ? date('d-M',strtotime($value['start_date'])) : ''; ?></td> 
		                    	<td><?php echo !empty($value['day']) ? ucfirst($value['day']) : ''; ?></td>
		                    	<td><?php echo !empty($value['stiming']) ? $value['stiming'] : ''; ?></td>
		                    	<td><?php echo !empty($value['trainer_name']) ? ucfirst($value['trainer_name']) : ''; ?></td>
		                    	<td><?php echo !empty($value['seats']) ? $value['seats'] : ''; ?></td>
		                    	<td><?php echo isset($batch_allocated_array[$value['id']]['batch_id']) ? $value['seats'] - $batch_allocated_array[$value['id']]['batch_id'] : $value['seats'] ;   ?></td>
		                    	<td><?php echo isset($batch_allocated_array[$value['id']]['batch_id']) ? $batch_allocated_array[$value['id']]['batch_id'] : '0';  ?>
		                    	</td>
 								<td><a href="all_students?pid=<?= $allowed_students ?>&type=allocated&module_name=<?php echo !empty($value['module_name']) ? $value['module_name'] : ''; ?>&module_date=<?php echo !empty($value['start_date']) ? date('d-M-Y',strtotime($value['start_date'])) : ''; ?>&batch_id=<?php echo $value['id']; ?>"><?= $allowed ?></a>
 								<input type="hidden" id='allowed_id<?php echo $value['id']; ?>' value="<?= $allowed_students ?>">	
 								</td>
		                    	<td><a href="all_students?pid=<?= $not_allowed_students ?>&type=not_allocated&module_name=<?php echo !empty($value['module_name']) ? $value['module_name'] : ''; ?>&module_date=<?php echo !empty($value['start_date']) ? date('d-M-Y',strtotime($value['start_date'])) : ''; ?>&batch_id=<?php echo $value['id']; ?>"><?= $not_allowed?></a></td>
		                    	<input type="hidden" id="all_batch_info<?php echo $value['id']; ?>" value="<?php echo $value['module_name'].'@'.$value['start_date'].'@'.$value['id']; ?>">
		                    </tr>
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

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.28.4/dist/sweetalert2.all.min.js"></script>
<script>
	$(document).ready(function(){
	});
	//For Edit View of Batch View
	function get_batch_edit(){
		var check_value = $('input[name=single_check]:checked').val();
		if(check_value!='' && check_value!=null){
			$("#loading_custom").show();
			window.location.replace("<?php echo Url::to(['dv-delivery/edit?id='])?>"+check_value);
		}	 	
 	}//End of JS:get_batch_edit()//

 	//For Edit Student  data 3 May 2019
 	function get_students_edit(){
 		var check_value = $('input[name=single_check]:checked').val();
		if(check_value!='' && check_value!=null){
			var pid = $('#allowed_id'+check_value).val(); //for allowed
			var type = 'allocated';
			if(pid!='' && type!=''){
				$("#loading_custom").show();
				//&module_name=SMM&module_date=25-Apr-2019&batch_id=38
				var all_info = $('#all_batch_info'+check_value).val().split('@');
				window.location.replace("<?php echo Url::to(['dv-batch-allotment/all_students?pid='])?>"+pid+'&type=allocated&call_from=1&module_name='+all_info[0]+'&module_date='+all_info[1]+'&batch_id='+all_info[2]+'');
			}
		}//call if checked
 	}//End of JS:get_students_edit()//
</script>