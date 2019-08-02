<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\widgets\DetailView;
use app\models\DvStates;
use app\models\DvCities;
use app\models\DvCountry;
use app\models\DvUsers; 
use app\models\DvAssistBatches; 
use app\models\DvCourse;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use app\models\DvRegistration;
$user_name = $model->first_name.' '.$model->last_name;
$this->title = 'Participant: '.$user_name;
$this->params['breadcrumbs'][] = $user_name; 
//for manage request from All Batch list 3 may 2019
$call_from = isset($_GET['call_from']) ? $_GET['call_from'] : '' ; 
?>
<style>
    .panel-heading .accordion-toggle:after {
        /* symbol for "opening" panels */
        font-family: 'Glyphicons Halflings';
        content: "\e113";
        float: right;
        color: #000;
        font-size: 14px;
        font-weight: 400;
    }
    .panel-heading .accordion-toggle.collapsed:after {
        /* symbol for "collapsed" panels */
        content: "\e114";    /* adjust as needed, taken from bootstrap.css */
    }
    .collapse_custom_settings .panel-title{    font-weight: bold;    font-size: 14px;}
    .collapse_custom_settings .panel-default > .panel-heading{    background-color: #ccc;}

</style>
<div class="container">
    <div class="row">
        <div class="row">
            <button type="button" class="btn btn-sm pull btn-success search_submit" onclick="edit_view_data(<?php echo $model->id ?>,<?php echo $call_from; ?>)" id="Edit_Change">
            <i class="fa fa-pencil"></i> Edit </button> 
            <button type="button" class="btn btn-sm pull btn-warning search_submit" onclick="participant_view_data(<?php echo $model->id ?>)" id="Back_View">
                <i class="fa fa-book"></i> View
            </button>
            <?php
        $participant_data = DvRegistration::findOne($model->id);
        $participant_status = '';
        //1-Active[yellow:blue], 2-On Hold[orange], 3-Drop Off[red], 4-Completed[green]
        
        if($participant_data->participant_status == 1){
            $participant_status = 'Active';
            $color = 'primary';
        }else if($participant_data->participant_status == 2){
            $participant_status = 'On Hold';
            $color = 'warning';
        }else if($participant_data->participant_status == 3){
            $participant_status = 'Drop Off';    
            $color = 'danger';
        }else if($participant_data->participant_status == 4){
            $participant_status = 'Completed';
            $color = 'success';
        }
        //echo $participant_status;
        ?>
        </div>
        <button type="button" class="btn btn-sm pull-right btn-<?php echo $color; ?> search_submit">
                <i class="fa fa-book"></i> Status : <?php echo $participant_status; ?> 
        </button> 
        <div class="dv-paricipant-details-view" id="participant_content_area">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <h3 class="blue_color">Participant Details</h3>
                    </div>
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [ 
                                'label' => 'Full Name',
                                'value' => function($model){
                                    return ucfirst($model->first_name).' '.ucfirst($model->last_name);
                                },
                            ],
                            [
                                'label' => 'Mail',
                                'value' => function($model){
                                    return $model->email;
                                },
                            ],
                            [ 'label' => 'Phone Number (Mobile)',
                                'value' => function($model){
                                    return $model->mobile;
                                },
                            ],
                            [ 'label' => 'Course Enrolled',
                                'value' => function($model){
                                    if(!empty($model->course)){
                                        return DvCourse::findOne($model->course)->name;
                                    }
                                },
                            ],
                            [ 'label' => 'Enrollment Date',
                                'value' => function($model){
                                    return  date('d-m-Y',strtotime($model->created_on)); 
                                },
                            ],
                            [ 'label' => 'Co-ordinator',
                                'value' => function($model){
                                    return !empty($model->program_coordinator) ? ucfirst($model->program_coordinator) : "";
                                },
                            ],
                            [ 'label' => 'Foundation Session Date',
                                'value' => function($model){
                                    return date('d-m-Y',strtotime($model->course_batch_date));
                                },
                            ],
                            [ 'label' => 'Sales Consultant',
                                'value' => function($model){
                                    $user_model_row = DvUsers::findOne($model->sales_user_id);
                                    return !empty($user_model_row) ? ucfirst($user_model_row->first_name).' '.ucfirst($user_model_row->last_name) : '';
                                },
                            ],
                            [ 'label' => 'Opted V Skills Via Sales Form',
                                'value' => function($model){
                                    return $model->vskills == 1 ? "Yes" : "No";
                                },
                            ],
                            [ 'label' => 'Placement',
                                'value' => function($model){
                                    //return  
                                },
                            ], 
                            [ 'label' => 'Blog URL',
                                'value' => function($model){
                                    return "NA";
                                },
                            ],
                            'remarks' 
                        ]
                    ]); ?>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                      <h3 class="blue_color">Course</h3>
                    </div>
                    <?php 
                    $course = DvCourse::findOne($model->course)->name;
                    $module_edit = '';
                    if($course == 'CDMM' || $course == 'CPDM'){
                      $module_edit = '<a href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="Edit" course-id="'.$course.'"  participant-id="'.$model->id.'" data-oldID="'.$model->modules_allowed.'" data-token_id = "'.$model->token_id.'" id="modules_allowed_view"><i class="fa fa-pencil"></i></a>';
                    }
                    ?>
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [ 'label' => 'Module Allowed',
                                'value' => function($model){
                                    return $model->modules_allowed;
                                },
                                'contentOptions' => ['class' => 'modules_allowed_value'],
                            ],
                            [ 'label' => 'Module Completed',
                                'value' => function($model){
                                    return $model->modules_completed;
                                },
                            ],
                            [ 'label' => 'Batch Type',
                                'value' => function($model){
                                    return $model->modules_completed == 1  ? "Fast" : "Normal";
                                },
                            ],
                            [ 'label' => 'Opted Batch',
                                'value' => function($model){
                                    if((trim($model->available_batch_opt) != '' || trim($model->available_batch_opt) != NULL) && trim($model->available_batch_opt) != '0' && trim($model->available_batch_opt) != '1'){
                                        $batch_day = trim($model->available_batch_opt);  
                                    }else { 
                                        $batch_day = trim($model->course_batch_date) == '0000-00-00' ? '' : strtolower(date('D',strtotime($model->course_batch_date)));
                                    }
                                    return $batch_day;
                                },
                            ]
                        ]
                    ]); ?> 
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <h3 class="blue_color">Extra Details</h3>
                    </div>
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [ 'label' => 'Google plus',
                                'value' => function($model){
                                    //return 
                                },
                            ],
                            [ 'label' => 'Mouthshut',
                                'value' => function($model){
                                    //return 
                                },
                            ],
                            [ 'label' => 'External Review Site',
                                'value' => function($model){
                                    //return 
                                },
                            ],
                            [ 'label' => 'Dv VSklls',
                                'value' => function($model){
                                    //return  
                                },
                            ],
                            [ 'label' => 'Dv Certificate',
                                'value' => function($model){
                                    //return  
                                },
                            ]
                        ]
                    ]); ?>   
                </div>
            </div>
            <!---Begin Added on 15 May 2019 Purpose:Batch History------->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <h3 class="blue_color">Batch Details</h3>
                    </div>
                    <?php if(count($batch_modules_data) > 0){ ?>
                    <div class="panel-group collapse_custom_settings" id="accordion">
                        <?php  
                        foreach ($batch_modules_data as $batch_val) { ?>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class="accordion-toggle" data-parent="#accordion" data-toggle="collapse" href="#collapse<?php echo $batch_val['id']; ?>" style="display: block;">
                                        <span class="badge label-info"> 
                                        Type : <?php  
                                            if($batch_val['category_type'] == "Core"){
                                                echo 'CS';
                                            }else if($batch_val['category_type'] == "Special"){
                                                echo "SS";
                                            }else{ 
                                                echo 'FS'; 
                                            } ?>
                                        </span> |     
                                        <span class="text-info"><?php echo $batch_val['module_name']; ?></span> |
                                        <span class="text-primary">
                                            Start Date : <?php echo $batch_val['start_date']; ?> ,
                                            End Date : <?php echo $batch_val['end_date']; ?>  
                                        </span> |
                                            <?php 
                                            $todays = strtotime(date('d-m-Y'));
                                            $batch_start_date = '';
                                            $batch_end_date = '';
                                            $auto_open = '';
                                            $batch_status = '';
                                            $status_color = '';
                                            $batch_start_date = strtotime($batch_val['start_date']);
                                            $batch_end_date = strtotime($batch_val['end_date']);
                                            if($batch_start_date < $todays && $batch_end_date < $todays){
                                                $batch_status = "Completed";  
                                                $auto_open = 1;
                                                $status_color = '#16724d';
                                            }elseif($batch_start_date <= $todays && $batch_end_date >= $todays){
                                                $batch_status = "Ongoing";  
                                                $auto_open = 2;
                                                $status_color = '#ef9200';
                                            }else if($batch_start_date > $todays && $batch_end_date > $todays){
                                                $batch_status = "Upcoming";  
                                                $auto_open = 3;
                                                $status_color = '#e44949';
                                            }
                                            ?>
                                        <span class="text-primary">
                                            <?php echo "Trainer : ".ucfirst($batch_val['trainer_name']); ?>
                                        </span> | 
                                        <span style="color:<?php echo $status_color; ?>">
                                            Status : <?php echo $batch_status; ?>
                                        </span>
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapse<?php echo $batch_val['id']; ?>" class="panel-collapse collapse <?php echo isset($auto_open) && $auto_open == 2 ? 'in' : ''; ?>">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="batch_data">
                                                <table class="table table-striped">
                                                    <tr>
                                                        <th>Session/s</th>
                                                        <th>Session Date</th>
                                                        <th>Session Start Time</th> 
                                                        <th>Session End Time</th>
                                                        <th>Session Recording URL</th>
                                                    </tr>
                                                    <?php 
                                                    foreach($batch_meta_master_data as $key=>$meta_val) {
                                                        $sessions_count = ''; 
                                                        $start_time_array = array();
                                                        if($key == $batch_val['id']){
                                                            if(count($meta_val) > 0){
                                                                $all_sessions_array = array();
                                                                for($i=0;$i<count($meta_val);$i++){
                                                                    if(trim($meta_val[$i]['meta_key']) == 'all_sessions'){
                                                                        $sessions_count = $meta_val[$i]['meta_value'];
                                                                    }
                                                                    $all_sessions_array[$meta_val[$i]['meta_key']] = $meta_val[$i]['meta_value'];  
                                                                }
                                                                if(!empty($sessions_count)){
                                                                    for($j=0;$j<$sessions_count;$j++){ 
                                                                        $k = $j+1;?>
                                                                        <tr>
                                                                            <td><?php echo "Session - ".$k; ?></td>
                                                                            <td><?php echo $all_sessions_array['session'.$k]; ?></td> 
                                                                            <td><?php echo $all_sessions_array['start_time'.$k]; ?></td> 
                                                                            <td><?php echo $all_sessions_array['end_time'.$k]; ?></td>
                                                                            <td><?php
                                                                            $kkey = "recording_url".$k;
                                                                            if(array_key_exists($kkey,$all_sessions_array)){
                                                                            echo $all_sessions_array['recording_url'.$k];} ?></td>
                                                                        </tr><?php  
                                                                    }//End of inner loop
                                                                } 
                                                            }
                                                        }//End of batch id compare
                                                    }//End of outer loop ?>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div><?php
                        }?>
                    </div>
                    <?php }else{
                        echo '<tr><td colspan="12"><center> <h3>No Record Found</h3> </center></td> </tr>';
                    }?>
                </div>
            </div>
            <!------End Added on 15 May 2019----------->
        </div>
        <p id="Back_ID_btn">
            <?php if(isset($call_from) && $call_from == 1){ 
                ?>
                <a class="btn back_button btn-warning cancel_button" onclick="back_view();" href="#"><i class="fa fa-backward"></i> Back</a>
            <?php } else { ?>
                <?= Html::a('<i class="fa fa-backward"></i> Back', ['index'], ['class' => 'btn back_button btn-warning cancel_button']); 
            } ?>
        </p>
    </div><!---End of div class="row"----->
</div><!---End of div class="container"---> 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.28.4/dist/sweetalert2.all.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    //$(".modules_allowed_value").append('<?= $module_edit ?>');
    $('#Back_View').hide();
    $('.datepicker_se').datepicker({dateFormat:'dd-mm-yy',changeMonth: true,changeYear: true});
    /* $(document).on('click','#modules_allowed_view', function(e) {
        var course_id  = $(this).attr('course-id');
        var participant_id = $(this).attr('participant-id');
        var old_val = $(this).attr('data-oldID');
        var token_id = $(this).attr('data-token_id');
        var total = 1; //old is 1 instead of 1
        if(course_id == 'CDMM'){
            total = 6;
        }else if(course_id == "CPDM"){
            total = 5;  
        }

        if(total != 1){
            var i;
            var options = "";
            for (i = 1; i <= total; i++) { 
                if(i == old_val){
                    options +="<option selected value='"+i+"'>"+i+"</option>";
                }else{
                    options +="<option value='"+i+"'>"+i+"</option>";                        
                }
            }

            $(".modules_allowed_value").html("<select id='select_allowed_module' data-token_id='"+token_id+"' data-old_val='"+old_val+"' data-participant_id='"+participant_id+"' required>\
            "+options+"\
            </select><a href='javascript:void(0);' data-old_val='"+old_val+"' class='cancel-allowed_module' data-toggle='tooltip' data-placement='top' title='Cancel'> <i class='fa fa-fw fa-remove'></i> </span>");
        }else{
            $('.modules_allowed_value').html(old_val);
        }
    });

    $(document).on('click','.cancel-allowed_module', function(e) {
        var old_val = $(this).attr('data-old_val');
        $(this).remove(); 
        $('#modules_allowed_view').css('display','inline');
        $('.modules_allowed_value').html(old_val);
        $(".modules_allowed_value").append(' <?= $module_edit ?>');
    });

    $(document).on('change','#select_allowed_module', function(e) {
        var participant_id = $(this).attr("data-participant_id");
        var oldvalue = $(this).attr("data-old_val");
        var allowed_module = $(this).val();
        var token_id = $(this).attr("data-token_id");
        var url = window.location.href;
        swal({
          title: 'Are you sure?',
          text: "You want to change total allowed modules for this participant?",
          type: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, Change it!'
        }).then((result) => {
          if (result.value) {
            $("#loading_custom").show();
            $.ajax({
                url: '<?php echo Url::to(['dv-delievery-members/update_participant_allowed_module_ajax']); ?>',
                type: 'POST',
                data:{
                    participant_id: participant_id, 
                    allowed_module: allowed_module, 
                    url:url
                },
                success: function(data){  
                    if(data == '1'){
                        $("#loading_custom").hide();
                        swal(
                          'Updated!',
                          'Participant total allowed modules updated.',
                          'success'
                        );
                        $('.modules_allowed_value').html(allowed_module);
                        $('.cancel-allowed_module').remove(); 
                        $('#modules_allowed_view').css('display','inline');
                        $(".modules_allowed_value").append(' <?= $module_edit ?>');
                        $("#modules_allowed_view").attr('data-oldid',allowed_module);
                    }
                }
            });
        }else{
            $(this).val(oldvalue);
        }
        }); 
    });
    */
    $(document).on('click','.datepicker_se', function(e) {
            $('.datepicker_se').datepicker({dateFormat:'dd-mm-yy',changeMonth: true,changeYear: true});
    });

});

//For back to allocate studfents list
function back_view(){
    window.location.replace("<?php echo Url::to(['dv-batch-allotment/all_batch_list']); ?>");
}
 
//For Edit 
function edit_view_data(id,call_from){
    if(id!=''){
        $("#loading_custom").show();
        $('#Back_View').show();
        $('#Edit_Change').hide();
        $('#Back_ID_btn').hide();
        $.ajax({
            url: '<?php echo Url::to(['dv-delievery-members/participant_edit_view'])?>',
            type: 'POST',
            data:{
                id:id,
                call_from:call_from
            },
            success: function(data){  
                $("#loading_custom").hide();
                if(data){
                    $('#participant_content_area').html('');
                    $('#participant_content_area').html(data); 
                    $('#call_from').val(<?php echo isset($_GET['call_from']) ? $_GET['call_from'] : ''; ?>)  
                }else{
                    swal("Getting some error");
                }
            }
        });
    }
}//End of JS:edit_view_data(id)//

//For View
function participant_view_data(id){
    if(id!=''){  
        window.location.href = "<?php echo Url::to(['dv-delievery-members/participant_view?id='])?>"+id;
    }
}//End of JS:participant_view_data()//
</script>