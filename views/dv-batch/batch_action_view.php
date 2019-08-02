<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\DvCourse;
use yii\web\Session;
use yii\helpers\Url;
use yii\widgets\LinkPager;
$this->title = $model->isNewRecord ? 'New Batch Registration' : "Edit Batch"; 
$this->params['breadcrumbs'][] = $this->title; ?>
<div class="row">
	<div align="right">
	 	<?= Html::a('<i class="fa fa-book"></i> List', ['batch_list'], ['class' => 'btn back_button btn-primary']); ?>
	</div>
    <div class="col-md-8">
    	<div class="dv-batch-create">
	 	  	<?php $form = ActiveForm::begin(); ?>
				<div class="row"> 
					<div class="col-md-4">
						<?= $form->field($model, 'module_id')->dropDownList($module_ids,['placeholder' => 'Module Name','data-toggle'=>'tooltip', 'data-placement'=>'top', 'title'=>'Module Name','prompt'=>'Select Module','autocomplete'=>'off'])->label(false); ?>
					</div>
					<div class="col-md-4">
						<?= $form->field($model, 'trainer')->dropDownList($trainer_ids,['data-toggle'=>'tooltip', 'data-placement'=>"top", 'title'=>'Trainer','id'=>'trainer_id','prompt'=>'Select Trainer','onchange'=>'get_coordinator()','autocomplete'=>'off'])->label(false); ?>
					</div>
					<div class="col-md-4">
						<?= $form->field($model, 'trainer_coordinator')->dropDownList($trainer_coordinator_ids,['data-toggle'=>'tooltip', 'data-placement'=>"top", 'title'=>'Co-ordinator','id'=>'coordinator_id','prompt'=>'Select Co-ordinator','disabled'=>'disabled'])->label(false); ?>
							<input type="hidden" id='coordinator_idd' name="trainer_coordinator_r" value="<?php echo $model->trainer_coordinator; ?>">
					</div>
				</div>
				<div class="row"> 
					<div class="col-md-4">
						<?= $form->field($model, 'open_seats')->textInput(['maxlength' => true])->input('text', ['placeholder' => 'Open Seats', 'data-toggle'=>'tooltip', 'data-placement'=>"top", 'title'=>'Open Seats','autocomplete'=>'off'])->label(false); ?>
					</div>
					<div class="col-md-4">
						<?= $form->field($model, 'start_date')->input('text', ['class'=>'datepicker_se form-control','placeholder' =>'Start Date','data-toggle'=>'tooltip', 'data-placement'=>'top', 'title'=>'Start Date','autocomplete'=>'off'])->label(false); ?>
					</div>
					<div class="col-md-4">
					<?= $form->field($model, 'number_of_sessions')->dropDownList($number_of_sessions,['data-toggle'=>'tooltip', 'data-placement'=>"top", 'title'=>'Number of Sessions','id'=>'number_of_sessions_id','onchange'=>'get_sessions_data(null)','prompt'=>'Select Number of Sessions','autocomplete'=>'off'])->label(false); ?>
			  		</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<?= $form->field($model, 'batch_day')->dropDownList($batch_day,['data-toggle'=>'tooltip', 'data-placement'=>"top", 'title'=>'Batch Day','prompt'=>'Select Batch Day','autocomplete'=>'off'])->label(false); ?>
					</div>
					<div class="col-md-4">
						<?= $form->field($model, 'time_duration')->textInput(['maxlength' => true])->input('text', ['placeholder' => 'Time Duration', 'data-toggle'=>'tooltip', 'data-placement'=>"top", 'title'=>'Time Duration','autocomplete'=>'off'])->label(false); ?>
					</div>
					<div class="col-md-4">
						<?= $form->field($model, 'joining_link')->textInput(['maxlength' => true])->input('text', ['placeholder' => 'Joining Link', 'data-toggle'=>'tooltip', 'data-placement'=>"top", 'title'=>'Joining Link','autocomplete'=>'off'])->label(false); ?>
					</div>
				</div>
				<input type="hidden" name="status" value="<?php echo $model->isNewRecord ? 1 : $model->status; ?>">
				<div class="row" id="session_data">
				
				</div>
				<div class="form-group">
		    		<?php 
		    		if($model->isNewRecord) { ?>
				    		<?= Html::submitButton( '<i class="fa fa-check"></i> Create Batch' , ['class' => 'btn btn-success' ]);?>  
				    <?php } else { ?>
				    		<?= Html::submitButton( '<i class="fa fa-pencil"></i> Update Batch' , ['class' => 'btn btn-primary' ]) ?>
				    <?php } ?>
					<?= Html::a('<i class="fa fa-times"></i> Cancel', ['index'], ['class' => 'btn back_button btn-danger cancel_button']); ?>
				</div>
		    <?php ActiveForm::end(); ?>
		</div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.28.4/dist/sweetalert2.all.min.js"></script>
<script>
 	$(document).ready(function(){
 		//For normal date picker
        $('.datepicker_se').datepicker({dateFormat:'dd-mm-yy',changeMonth: true,changeYear: true});
        //for date picker at append action
        $(document).on('click','.datepicker_se', function(e) {
        	$('.datepicker_se').datepicker({dateFormat:'dd-mm-yy',changeMonth: true,changeYear: true});
        }); 
        //For time picker
        $(document).on('click','.stiming', function(e) {
        	$('.stiming').timepicker({dateFormat:'dd-mm-yy'});
        }); 
        //For remove append items
        $(document).on('click','.remove-btn', function(e) {
          	$(this).parents('.session_data_class').remove();
	    });
	    <?php
    	if(!$model->isNewRecord){ ?>
	    	//For edit purpose getting sessions
	    	get_sessions_data_php_call();
		<?php } ?>
	});
	
	//For Getting / Appending Sessions data
 	function get_sessions_data(){
 		$('#session_data').html('');
    	count = $('#number_of_sessions_id').val();
    	for(var i = 1 ; i <= count ;i++){
    		$("#session_data").append("<div class='session_data_class col-md-9 form-group'><div class='col-md-3'><input type='text' class='datepicker_se form-control' placeholder='Select Date' name='session_date[]' autocomplete='off' data-toggle='tooltip' data-placement='top' title='Start Date'></div><div class='col-md-3'><input type='text' class='form-control stiming' autocomplete='off' placeholder='Select Time' name='session_time[]'></div><div class='col-md-3'><input type='text' autocomplete='off' class='form-control' placeholder='Select Duration' name='session_duration[]'></div><div class='col-md-3'><button type='button' class='btn btn-default btn-sm remove-btn'><span class='glyphicon glyphicon-minus'></span>Remove</button></div></div>");
    	}//End of for()//
    		 
 	}//End of JS:get_sessions_data()//

 	function get_sessions_data_php_call(){
 		$('#session_data').html('');
 		<?php 
 		for($i = 0 ; $i < count($sessions_batch_data) ; $i++){ ?>
	    	$("#session_data").append("<div class='session_data_class col-md-9 form-group'><input type='hidden' name='battch_session_id[]' value='<?php echo $sessions_batch_data[$i]['id'] ?>'><div class='col-md-3'><input type='text' class='datepicker_se form-control' value='<?php echo date('d-m-Y',strtotime($sessions_batch_data[$i]['session_date'])) ?>' placeholder='Select Date' name='session_date[]' autocomplete='off' data-toggle='tooltip' data-placement='top' title='Start Date'></div><div class='col-md-3'><input type='text' class='form-control stiming' autocomplete='off' value='<?php echo date('h:i A',strtotime($sessions_batch_data[$i]['session_time'])) ?>' placeholder='Select Time' name='session_time[]'></div><div class='col-md-3'><input type='text' autocomplete='off' class='form-control' placeholder='Select Duration' value='<?php echo $sessions_batch_data[$i]['session_duration']?>' name='session_duration[]'></div><div class='col-md-3'><button type='button' class='btn btn-default btn-sm remove-btn'><span class='glyphicon glyphicon-minus'></span>Remove</button></div></div>");
	    	<?php 
	   } ?>
 	}//End of JS:get_sessions_data_php_call()//

 	//For Getting dependant Coordinator name
    function get_coordinator(){

    	if($('#trainer_id').val()!=''){
	    	$("#loading_custom").show();
	        $.ajax({
	            url: '<?php echo Url::to(['dv-batch/get_coordinator'])?>',
	            type: 'POST',
	            data:{
	                id:$('#trainer_id').val()
	            },
	            success: function(data){ 
	            	if(data){
	            		$("#loading_custom").hide();
	            		$('#coordinator_id').html(data);
	                	$('#coordinator_idd').val($('#coordinator_id').val());
	                }else{
	            		$("#loading_custom").hide();
	            		$('#coordinator_id').html('');
	            	}
	            }
	        });
    	}else{
    		$('#coordinator_id').html('');
    	}

    }//End of JS:get_coordinator()//

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