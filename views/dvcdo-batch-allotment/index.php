<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\DvCourse;
use yii\web\Session;
use yii\helpers\Url;
use yii\widgets\LinkPager;
$this->title = "Batch Allotment"; 
$this->params['breadcrumbs'][] = $this->title; ?>
<div class="row">
	<div class="col-md-12">
		<div class="table-responsive">
	        <table class="table table-striped" style="width:100%">
	            <thead>
	                <tr> 
	                    <th><input type="checkbox" name="checkAll" id="checkAll"/></th>
	                    <th>Possible Module(s)</th>
	                   	<th>Completed Module(s)</th>
	                    <th>Number of Students</th>
	                    <th>Day</th>
	                    <th>Start Date</th>
	                </tr>
	            </thead>
	            <tbody>
	            <?php if(count($model) > 0){ 
	            	$cnt = 1;
	            	$all_modules = array();
	            	//$modules_array = array();
	            	?>    
	                <?php foreach($model as $value){ 
	                	$all_modules[] = $value;
	                	?>
	                	<input type="hidden" name="batch_allotment_details" id="batch_allotment_details<?php echo $cnt; ?>" value="<?php echo $value['modules'].'#'.$value['date'].'#'.$value['day'];?>"> 
	                	<input type="hidden" id="completed_modules<?php echo $cnt; ?>" value="<?php echo rtrim($value['completed_modules'],','); ?>">
	                    <tr>
	                    	<td><input type="checkbox" value="<?php echo $value['ids']."###".$cnt; ?>" name="single_check" id="single_check<?php echo $cnt; ?>"/></td>
	                    	<td><?php echo $value['modules'];?></td>
	                    	<td><?php echo $value['completed_modules']; ?></td>
	                    	<td><a href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="Unallocated Students List" class="students_list" data-id="<?=$value['ids']; ?>"><b><?= $value['students']; ?></b></a></td>
	                    	<td><?php echo $value['day']; ?></td>
	                    	<td><?php echo $value['date']; ?></td>
	                    </tr>
		            <?php $cnt++; }
	            } ?>
	            </tbody>
	        </table>
	        <input type="button" data-toggle="tooltip" data-placement="top" title="Student List" name="Student List" value="Student List" onclick="get_students_data();" class="btn btn-warning">
	    </div>
	    <?php 
	    echo LinkPager::widget(['pagination' => $pages]); ?>
	</div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.28.4/dist/sweetalert2.all.min.js"></script>
<script>
	$(document).ready(function(){
		$(".students_list").click(function(){
			var ids = $(this).attr("data-id");
			var index_ = $('.students_list').index(this) + parseInt(1);
			var batch_allotment_details = $('#batch_allotment_details'+index_).val();
			var completed_modules_name = $('#completed_modules'+index_).val();
			if(batch_allotment_details!='' && ids!=''){
				$("#loading_custom").show();
	            $.ajax({
	                url: '<?php echo Url::to(['dv-batch-allotment/display_students_list'])?>',
	                type: 'POST',
	                data:{
	                    students_id:ids,
	                    batch_allotment_details:batch_allotment_details,
	                    completed_modules_name:completed_modules_name
	                },
	                success: function(data){}
	            });
        	}
        });
 		$("#checkAll").click(function(){
		    $('input:checkbox').not(this).prop('checked', this.checked);
		});
 	});

	function get_students_data(){
		var all_ids = [];
		var check_index = [];
		var completed_modules_array = [];
		var jsonObjects = [{id:1, name:"amit"}, {id:2, name:"ankit"},{id:3, name:"atin"},{id:1, name:"puneet"}];
		$('input:checkbox[name=single_check]').each(function(){    
		    if($(this).is(':checked')){
		    	all_ids.push($(this).val().split('###')[0]);
		    	check_index.push($(this).val().split('###')[1]);
		    	if($('#completed_modules'+$(this).val().split('###')[1]).val()!=null && $('#completed_modules'+$(this).val().split('###')[1]).val()!=''){
		    		completed_modules_array[$(this).val().split('###')[0]] = [];
		    		completed_modules_array.push($('#completed_modules'+$(this).val().split('###')[1]).val());
		    	}
		    }
		});

		if(all_ids.length > 0){  
			$("#loading_custom").show();
            $.ajax({
                url: '<?php echo Url::to(['dv-batch-allotment/display_students_list'])?>',
                type: 'POST',
                data:{
                    students_id:all_ids.toString(),
                    batch_allotment_details:$('#batch_allotment_details'+check_index[0]).val(),
                    completed_modules_name:'',
                    all_students_btn:1
                },
                success: function(data){
                }
            });
        }
	}//End of JS:get_students_data()//
</script>