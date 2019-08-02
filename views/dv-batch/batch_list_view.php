<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\DvCourse;
use yii\web\Session;
use yii\helpers\Url;
use yii\widgets\LinkPager;
$this->title = "Batch List"; 
$this->params['breadcrumbs'][] = $this->title; ?>
<div class="row">
	<div align="right">
	 	<?= Html::a('<i class="fa fa-backward"></i> Back', ['index'], ['class' => 'btn back_button btn-primary']); ?>
	</div>
    <div class="col-md-12">
		<div class="table-responsive">
		        <table class="table table-striped" style="width:100%">
		            <thead>
		                <tr> 
		                    <th>#</th>
		                    <th>Upcoming Batch</th>
		                    <th>Date</th>
		                    <th>Day</th>
		                    <th>Timing</th>
		                    <th>Trainer</th>
		                    <th>Open Seats</th>
		                    <th>Alloted Seats</th>
		                    <th>Allowed</th>
		                    <th>No Allowed</th>
		                    <th>Status</th>
		                    <th>Edit</th>
		                </tr>
		            </thead>
		            <tbody>
		            <?php if(count($batch_listing_data) > 0){ ?>    
		                <?php
		                if(!empty($_GET['page']) && $_GET['page']!=1){
		                    $i = ($_GET['page'] - 1 )*$_GET['per-page'] + 1;
		                }else{
		                    $i=1;
		                }
		                foreach($batch_listing_data as $batch_data){ ?> 
		                    <tr>
		                        <td>
		                            <a class="btn btn-xs btn-info" href="<?php echo Url::to(['dv-batch/batch_details?id='.$batch_data['id']])?>"><strong><?php echo $i++; ?></strong></a>
		                        </td>
		                        <td><?php echo $batch_data['module_name']; ?></td>
		                        <td><?php echo date('d-m-Y',strtotime($batch_data['start_date'])); ?></td>
		                        <td><?php echo $batch_data['batch_day']; ?></td>
		                        <td><?php echo $batch_data['time_duration']; ?></td>
		                        <td><?php echo $batch_data['trainer_name']; ?></td>
		                        <td><?php echo $batch_data['open_seats']; ?></td>
		                        <td><?php //echo $batch_data['module_name']; ?></td>
		                        <td><?php //echo $batch_data['module_name']; ?></td>
		                        <td><?php //echo $batch_data['module_name']; ?></td>
		                        <td>
		                        <?php if($batch_data['status'] == 1){ ?>
		                            <a onclick="alert_action(<?php echo $batch_data['id']; ?>,1)" class="batch_status_<?php echo $batch_data['id']; ?>" href="javascript:void(0);">
		                                <i class="fa fa-check-circle green_icon"></i>
		                            </a>
		                        <?php }else{ ?>
		                            <a onclick="alert_action(<?php echo $batch_data['id']; ?>,0)" class="batch_status_<?php echo $batch_data['id']; ?>" href="javascript:void(0);">
		                                <i class="fa fa-eye-slash red_icon"></i>
		                            </a>
		                        <?php } ?>
		                        <input type="hidden" id="status_txt<?php echo $batch_data['id']; ?>" value="">
		                        </td>
		                        <td>
		                            <a onclick="get_edit(<?php echo $batch_data['id']; ?>)" data-toggle="tooltip" data-placement="top" title="Edit" href="#"><i class="fa fa-pencil"></i></a>
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
	</div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.28.4/dist/sweetalert2.all.min.js"></script>
<script>
 	//For Enable & Disable of Batch
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
                    url: '<?php echo Url::to(['dv-batch/batch_status_update'])?>',
                    type: 'GET',
                    data:{id:id},
                    success: function(data){
                    	$("#loading_custom").hide();
                        if(data == "enable"){
                            $('#status_txt'+id).val("");
                            $('#status_txt'+id).val("Disable");
                            $(".batch_status_"+id).html('<i class="fa fa-check-circle green_icon"></i>');
                        }else if(data == "disable"){
                            $('#status_txt'+id).val("");
                            $('#status_txt'+id).val("Enable");
                            $(".batch_status_"+id).html('<i class="fa fa-eye-slash red_icon"></i>');
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

    //For Getting Edit View
    function get_edit(id){
    	if(id!=''){
	        $.ajax({
	            url: '<?php echo Url::to(['dv-batch/edit_batch_form'])?>',
	            type: 'GET',
	            data:{id:id},
	            success: function(data){ }
	        });
    	}
    }//End of JS:get_edit()//
</script>