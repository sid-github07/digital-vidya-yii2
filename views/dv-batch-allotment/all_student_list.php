<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\DvCourse;
use app\models\DvUsers;
use yii\web\Session;
use yii\helpers\Url;
use yii\widgets\LinkPager;
use kartik\export\ExportMenu;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;

// $batchs = !empty($batch_allotment_details) ? explode('#',$batch_allotment_details) : '';
if($type=="allocated"){
	$this->title = "Allocated Students list";
	$this->params['breadcrumbs'][] = "Allocated Students list"; 
	$btn_title = "Unallocate Students";
}else{
	$this->title = "Not Allocated Students list";
	$this->params['breadcrumbs'][] = "Not Allocated Students list"; 
	$btn_title = "Allocate Students";
}
?>
<div class="form-group"> 
    <?php
    $exl_array = array();
    $export_count = 1;
    foreach($participant_users_array as $user){
 		if( $user->participant_status == 1){
            $status = 'Active';
        } else {
            $status = 'In-active';
        }
        $date = date("d-m-Y",strtotime($user->created_on));
        /*  Get sales name */
        $sales_id = $user->sales_user_id;                
        $sales = DvUsers::find()->where(['id'=>$sales_id])->all();
 		$sales_name = '';
        if($sales){
            $sales_name1 = array_values(ArrayHelper::map($sales, 'id', 'first_name'));
            $sales_name2 = array_values(ArrayHelper::map($sales, 'id', 'last_name'));
            $sales_name = $sales_name1[0]." ".$sales_name2[0];
        }
        $username = $user->first_name . ' ' . $user->last_name; 
 		$course = DvCourse::find()->where(['id'=>$user->course])->all();
        $Ucourse = ArrayHelper::map($course, 'id', 'name');
        $u_course = array_values($Ucourse);
 		$participant_payment_status =  $user->participant_payment_status;
        $pp_status = "";
        if($participant_payment_status == 1){
            $pp_status = "Payment Due";
        }elseif($participant_payment_status == 2){
            $pp_status = "Refund";
        }elseif($participant_payment_status == 3){
            $pp_status = "Completed";
        }elseif($participant_payment_status == 4){
            $pp_status = "NA";
        }

        $participant_status =  $user->participant_status;
        $p_status = "";
        if($participant_status == 1){
            $p_status = "Active";
        }elseif($participant_status == 2){
            $p_status = "On Hold";
        }elseif($participant_status == 3){
            $p_status = "Drop off";
        }elseif($participant_status == 4){
            $p_status = "completed";
        }

        if($user->program_coordinator!=""){
            $program_coordinatior = $user->program_coordinator;
        }else{
            $program_coordinatior = "NA";
        }

 		$exl_array[] = array( 
            'id'=>$export_count,
            'Date' => $date,
            'Sales Name' => $sales_name,
            'Username' => $username,
            'Email' => $user->email,
            'Program Coordinator' => $program_coordinatior,
            'Course' => $u_course[0],
            'Batch Date' => $user->course_batch,
            'Total Modules Allowed' => $user->modules_allowed,
            'Participantion Status' => $p_status,
            'Payment Status' => $pp_status,
        );

        $export_count = $export_count+1;
    }

    $excel_array = array('allModels' => $exl_array ,'pagination'=>false);
    $dataProvider = new ArrayDataProvider($excel_array);
    /*  creation of array for export as excel */
    $columns = array('id', 'Date', 'Sales Name','Username','Email','Program Coordinator','Course','Batch Date','Total Modules Allowed','Participantion Status','Payment Status');
    $file_name = 'Participant Details('.date('Y-m-d').')';
    echo ExportMenu::widget([
        'dataProvider' => $dataProvider,
        'fontAwesome' => true,
        'columns' => $columns,
        'options' => ['id'=>'expMenu1'], /*  optional to set but must be unique */
        'target' => ExportMenu::TARGET_BLANK,
        'filename' => $file_name
    ]);
?> 
</div>
<div class="table-responsive">
	<input type="hidden" id="module_name" name="module_name" value="<?php echo isset($module_name) ? $module_name : ''; ?>">
	<input type="hidden" id="batch_id" name="batch_id" value="<?php echo isset($batch_id) ? trim($batch_id) : ''; ?>">
	<input type="hidden" id="module_date" name="module_date" value="<?php echo isset($module_date) ? date('d-M-Y',strtotime($module_date)) : ''; ?>">
  	<button class="btn btn-warning pull-left" id="allocation_id" data-toggle="" data-target="#allocation_action_modal" onclick="allocated_vice_versa();"><?php echo $btn_title; ?></button>
  	<table class="table table-striped" style="width:100%">
	    <thead>
	        <tr> 
	            <th><input type="checkbox" name="checkAll" id="checkAll"/></th>
	            <th>Name</th>
	            <th>Email</th>
	            <th>Mobile No.</th>
	            <th>Opted Days</th>
	            <th>Consultant</th>
	            <th>Coordinator</th>
	           <th> Edit </th> 
	        </tr>
	    </thead>
	    <tbody>
		    <?php if(count($students_list) > 0){ 
		    	$cnt = 1;
		    	?>    
		        <?php foreach($students_list as $value){ 
		        		$user_name = DvUsers::find($value['sales_user_id'])->one();
 					?> 
		            <tr>
		            	<td><input type="checkbox" value="<?php echo $cnt.'@'.$value['id']; ?>" name="single_check" id="single_check<?php echo $cnt.'@'.$value['id']; ?>"/></td>
		            	<td><?php echo $value['first_name'].' '.$value['last_name']; ?></td>
		            	<td><?php echo $value['email']; ?></td>
		            	<td><?php echo $value['mobile']; ?></td>
		            	<td><?php 
		            		if(!empty(trim($value['available_batch_opt']))){
				                echo $value['available_batch_opt'];
				            }else{
				                echo strtolower(date('D',strtotime($value['course_batch_date']))); 
				            } 
				            ?>
				        </td>
				        <td><?php echo $user_name['first_name'].' '.$user_name['last_name']; ?></td>
				        <td><?php echo ucfirst($value['program_coordinator']); ?></td>
				        <td><a href="javascript:void(0);" onclick="get_edit_participant(<?php echo $value['id']; ?>);" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fa fa-pencil"></i></a>
			        	</td> 
				    </tr>
		            <input type="hidden" id="email_id<?php echo $cnt;?>" value="<?php echo $value['email']; ?>">
		        <?php $cnt++; }
		    }else{
		    	echo '<tr><td colspan="10"><center> <h3>No Record Found</h3> </center></td> </tr>';
		    } ?>
	    </tbody>
    </table>
    <?php //echo LinkPager::widget(['pagination' => $pages]); ?>
</div>
<div class="modal fade" id="allocation_action_modal" role="dialog">
<div class="modal-dialog">
 	<!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="modal_title"></h4>
        </div>
        <div class="modal-body">
        	<div class="form-group">
        		<span><b>Module Name : </b></span><span id="module_name_show"></span>
        	</div>
        	<div class="form-group">
        		<span><b>Module Start Date : </b></span><span id="module_start_date"></span>
        	</div>
        	<div class="form-group">
        		<span><b>User Email Id(s) : </b></span><span id="user_emails"></span>
        	</div>
        	<br>
        	<div class="form-group">
        		<input type="checkbox" value="" name="user_initiated_check" id="user_initiated_check"/> <label>User Initiated Check</label>
        	</div>
        </div>
        <input type="hidden" id="checked_studants_id" name="checked_studants_id" value="">
        <div class="modal-footer">
          <button type="button" class="btn btn-danger pull-left" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary pull-right" id="user_submit_btn"></button>
        </div>
    </div>
</div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.28.4/dist/sweetalert2.all.min.js"></script>
<script>
	$(document).ready(function(){
		$("#checkAll").click(function(){
		    $('input:checkbox').not(this).prop('checked', this.checked);
		});

		$("#user_submit_btn").click(function(){
		    var students_ids = $('#checked_studants_id').val();
		    var action_name = "";
		    var user_initiated_check = $('#user_initiated_check').is(':checked') ? 1 : 0;
		    var type = '<?php echo $_GET['type']; ?>';
		    var all_students_ids = '<?php echo $_GET['pid']; ?>';
		    $.ajax({
                url: '<?php echo Url::to(['dv-batch-allotment/batch_action_vice_versa'])?>',
                type: 'POST',
                data:{
                    students_ids:students_ids,
                    all_students_ids:all_students_ids,
                    batch_id : $('#batch_id').val(),
                    type : type,
                    module_name : $("#module_name").val(),
                    module_date : $("#module_date").val(),
                    user_initiated_check:user_initiated_check
                },
                success: function(data){}
            });
		});	

	});

	//For Edit Student data 3 May 2019
 	function get_edit_participant(id){
 		if(id!='' && id!=null){
 			$("#loading_custom").show();
			window.location.replace("<?php echo Url::to(['dv-delievery-members/participant_view?id='])?>"+id+'&call_from=1');
		} 
 	}//End of JS:get_edit_participant()//

 	//For Allocate to Unallocate & Vice Versa 3 May 2019
 	function allocated_vice_versa(){
 		var all_ids = [];
 		var all_mails = [];
		var check_index = [];
		$('input:checkbox[name=single_check]').each(function(){    
		    if($(this).is(':checked')){
		    	all_ids.push($(this).val().split('@')[1]);
		    	check_index.push($(this).val().split('@')[0]);
		    	all_mails.push($('#email_id'+$(this).val().split('@')[0]).val());
		    }
		});
		//for modal view
		if(all_ids.length > 0 && check_index.length > 0){
			$('#allocation_id').attr('data-toggle','modal'); //modal setting
 			var dynamic_txt = '';
			<?php
			if($_GET['type'] == "allocated"){?>
				action_text = 'Unallocate';
				dynamic_txt = 'Allocated to unallocate student/s';<?php
			}else if($_GET['type'] == "not_allocated"){?>
				action_text = 'Allocate';
				dynamic_txt = 'Unallocated to allocate student/s';<?php
			}else{}
			?>
 			//dynamic model data showing;
			//Remove old data
			$('#modal_title').html('');
			$('#module_name').html('');
			$('#module_start_date').html('');
			$('#user_emails').html(''); 
			$('#user_submit_btn').html('');
			$('#checked_studants_id').val('');
			//Put new updated data
			$('#user_initiated_check').prop('checked', false); // Unchecks it
			$('#modal_title').html(action_text);
			$('#module_name_show').html($("#module_name").val());
			$('#module_start_date').html($('#module_date').val());
			$('#user_emails').html(all_mails.toString());
			$('#user_submit_btn').html(action_text);
			$('#checked_studants_id').val(all_ids.toString());
 		}//IF Empty ids

 	}//End of JS:allocated_vice_versa()//

</script>
