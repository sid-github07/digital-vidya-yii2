<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\web\Session;
use yii\helpers\Url;
$this->title = "Batch Details View"; 
$this->params['breadcrumbs'][] = $this->title; ?>
<div class="row">
    <div align="right">
        <?= Html::a('<i class="fa fa-book"></i> List', ['batch_list'], ['class' => 'btn back_button btn-primary']); ?>
    </div>
    <div class="col-md-12">
    	<div class="table-responsive">
	        <table class="table table-striped" style="width:70%">
	            <thead>
	            	<tbody>
		                <tr> 
		                    <th>Module Name</th>
		                    <td><?php echo $model[0]['module_name']; ?></td>
		                </tr>
		                <tr> 
		                    <th>Trainer</th>
		                    <td><?php echo $model[0]['trainer_name']; ?></td>
		                </tr>
		                <tr> 
		                    <th>Co-ordinator</th>
		                    <td><?php echo $model[0]['coordinator_name']; ?></td>
		                </tr>
		                <tr> 
		                    <th>Open Seats</th>
		                    <td><?php echo $model[0]['open_seats']; ?></td>
		                </tr>
		                <tr> 
		                    <th>Start Date</th>
		                    <td><?php echo date('d-m-Y',strtotime($model[0]['start_date'])); ?></td>
		                </tr>
		                <tr> 
		                    <th>Number of sessions</th>
		                    <td><?php echo $model[0]['number_of_sessions']; ?></td>
		                </tr>
		                <tr> 
		                    <th>Batch Day</th>
		                    <td><?php echo $model[0]['batch_day']; ?></td>
		                </tr>
		                <tr> 
		                    <th>Time Duration</th>
		                    <td><?php echo $model[0]['time_duration']; ?></td>
		                </tr>
		                <tr> 
		                    <th>Joining Link</th>
		                    <td><?php echo $model[0]['joining_link']; ?></td>
		                </tr>
	                <tbody>
	            </thead>
	        </table>
		</div>
		<div class="table-responsive">
			<h3>Session Details</h3>
	        <table class="table table-striped" style="width:70%">
	            <thead>
	            	<tr>
	        			<th>Session Date</th>
	        			<th>Session Time</th>
	        			<th>Session Duration</th>
	        		</tr>
        		</thead>
            	<tbody>
            		<?php 
            			for ($i = 0 ; $i < count($sessions_batch_data) ; $i++) { ?>
	            		 	<tr> 
		                    	<td><?php echo $sessions_batch_data[$i]['session_date']!='' ? date('d-m-Y',strtotime($sessions_batch_data[$i]['session_date'])) : ''; ?></td>
		                    	
		                    	<td><?php echo $sessions_batch_data[$i]['session_time']; ?></td>
		                    	
		                    	<td><?php echo $sessions_batch_data[$i]['session_duration']; ?></td>
 							</tr><?php 
	            		} ?>
	            <tbody>
	        </table>
		</div>
	</div>
</div>