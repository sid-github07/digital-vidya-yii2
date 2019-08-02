<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\LinkPager;
$this->title = $model->isNewRecord ? "Allocate Co-ordinator" : "Edit Co-ordinator"; 
$this->params['breadcrumbs'][] = $this->title; 
?>
<div class="coordinator-reg-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="label_date"><label> Select Date </label></div>
            <div class="date_value">
                <?= $form->field($model, 'created_on')->input('text', ['class'=>'datepicker_se form-control','placeholder' =>'Select Date','data-toggle'=>'tooltip', 'id'=>'custom_date','data-placement'=>'top', 'title'=>'Select Date','autocomplete'=>'off','onchange'=>'get_coordinator_data()'])->label(false); ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="form-group" id="content_area">
                <input type="checkbox" name="coordinator" class="check_all_class" id="check_all_id" value="">
                <label>Check All</label>&nbsp;<br>   
                <?php   
                foreach ($existing_coordinator_array as $key => $value) {?>
                    <input type="checkbox" <?php echo in_array($key,explode(',',$model->coordinator_ids)) ? "checked" : ''; ?> class="single_checkbox_class" name="coordinator_ids[]" value="<?php echo $key; ?>">
                    <label><?php echo $value; ?></label>&nbsp;<br><?php   
                } ?>
                    <?php /* echo $form->field($model, 'coordinator_ids[]')->checkboxList(
                        ['a' => 'Item A', 'b' => 'Item B', 'c' => 'Item C'] ); */
                    ?>      
            </div>
        </div>
    </div>
     
    <div class="form-group" id="only_today_adding">
        <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-pencil"></i> Submit' : '<i class="fa fa-pencil"></i> Update', ['id'=>'create_emp','class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('<i class="fa fa-times"></i> Cancel', ['index'], ['class' => 'btn back_button btn-danger cancel_button']); ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
    $(document).ready(function(){ 
        
        $('.datepicker_se').datepicker({ 
            dateFormat:'dd-mm-yy',
            maxDate:0
        }).datepicker("setDate",'now');

        //For Check all
        $("#check_all_id").click(function(){
            $('input:checkbox').not(this).prop('checked', this.checked);
        });

    });

    function get_coordinator_data(){
        var today_date = new Date();
        var js_splt_date = $('#custom_date').val().split('-');
        //If Selected Date is same with Today's Date,Month & Year then we can edit otherwise only view. 
        if( (today_date.getDate() == js_splt_date[0]) && (today_date.getMonth()+1 == js_splt_date[1]) && (today_date.getFullYear() == js_splt_date[2])){
            window.location = "<?php echo Url::to(['dv-coordinator/index'])?>";
        }else{
            $("#loading_custom").show();
            $.ajax({
                url: '<?php echo Url::to(['dv-coordinator/get_history'])?>',
                type: 'POST',
                data:{
                    get_date:$('#custom_date').val()
                },
                success: function(data){  
                    $("#loading_custom").hide();
                    if(data){
                        $('#only_today_adding').hide();
                        $('#content_area').html('');
                        $('#content_area').html(data);
                    }else{
                        $('#only_today_adding').show();
                    }
                }
            });
        }   
    }//End of JS:get_coordinator_data()//
</script>
