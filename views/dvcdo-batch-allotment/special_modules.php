<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\DvCourse;
use yii\web\Session;
use yii\helpers\Url;
use yii\widgets\LinkPager;
$this->title = "Possible Special Modules"; 
$this->params['breadcrumbs'][] = $this->title; ?>
<div class="row">
	<div class="col-md-12">
		<div class="table-responsive">
			 	<table class="table table-striped" style="width:100%">
		            <thead>
		                <tr> 
		                    <th><input type="checkbox" name="checkAll" id="checkAll"/></th>
		                    <th>Possible Special Module(s)</th>
		                   	<th>Completed Special Module(s)</th>
		                    <th>Number of Students</th>
		                </tr>
		            </thead>
		            <tbody>
		            <?php 
		            	if(count($model) > 0){ 
		            		$cnt = 1;
		            	  	foreach($model as $value){?>
			                	<tr>
			                    	<td>
			                    		<input type="checkbox" value="<?php echo $value['ids']."###".$cnt; ?>" name="single_check" id="single_check<?php echo $cnt; ?>"/>
			                    	</td>
			                    	<td><?php echo $value['possible_special_all_modules'];?></td>
			                    	<td><?php echo $value['completed_special_modules']; ?></td>
			                    	<td>
			                    		<a href="javascript:void(0);" class="students_list" data-id="<?php echo $value['ids'];?>"><b><?php echo $value['students']; ?></b>
			                    		</a>
			                    	</td>
			                    </tr>
			                    <?php $cnt++; 
				        	}//end of foreach()//
		            	}?>
		            </tbody>
	        	</table>
	    </div>
	    <input type="button" data-toggle="tooltip" data-placement="top" title="Student List" name="Student List" value="Student List" onclick="get_students_data();" class="btn btn-warning">
	    <?php 
	    echo LinkPager::widget(['pagination' => $pages]); ?>
	</div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.28.4/dist/sweetalert2.all.min.js"></script>
<script>
	$(document).ready(function(){

		$("#checkAll").click(function(){
		    $('input:checkbox').not(this).prop('checked', this.checked);
		});
		//Goto next page
 		$(".students_list").click(function(){
 			var ids = $(this).attr("data-id"); 
		  	window.location.href = "<?php echo Url::to(['dv-batch-allotment/student_list_special'])?>?students_id="+ids.toString();
		});

	});	//End of Jquery

	//submit data
	function submit_data(){
		document.getElementById("submit_form").submit();
	}

	function get_students_data(){
		var all_ids = [];
		$('input:checkbox[name=single_check]').each(function(){    
		    if($(this).is(':checked')){
		    	all_ids.push($(this).val().split('###')[0]);
 			}
		});
		if(all_ids.length > 0){  
			$("#loading_custom").show();
			window.location.href = "<?php echo Url::to(['dv-batch-allotment/student_list_special'])?>?students_id="+all_ids.toString();
        }
	}//End of JS:get_students_data()//
</script>