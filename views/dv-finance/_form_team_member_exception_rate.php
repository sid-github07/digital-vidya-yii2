<?php

use yii\helpers\Html;
use yii\data\Pagination;
use yii\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\DvIncentive;

$month_arr = array('1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April', '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August', '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
?>
<div class="dv-users-form" id="team_member_exception_rate">


    <div class="row">
        <?php
        $form = ActiveForm::begin([
                    'fieldConfig' => [
                        'options' => ['class' => 'form-group col-md-4']
        ]]);
        ?>
        <input type="text" class="sr-only" name="DvManageMonthlyIncentiveExceptionRate[year_to_save]" id="DvManageMonthlyIncentiveExceptionRate-year_to_save">
        <?=
        $form->field($model, 'years')->dropDownList(
                [date('Y') => date('Y'), date("Y") + 1 => date('Y') + 1], ['prompt' => 'Select Year'])->label(false);
        ?>
        <?=
        $form->field($model, 'month')->dropDownList(
                ['1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April', '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August', '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'], ['prompt' => 'Select Month','multiple' => TRUE])->label(false);
        ?>
        <?= $form->field($model, 'domain')->dropDownList(['dm' => 'Digital Marketing', 'da' => 'Digital Analytics'], ['prompt' => 'Select Domain'])->label(false); ?>
        <div class="form-group col-md-4">
            <select class="form-control" id="dvmanagemonthlyincentiveexceptionrate-executive_id" name="DvManageMonthlyIncentiveExceptionRate[executive_id]">
                <option value="">Select User</option>
                <optgroup label="Manager">
                    <?php
                    foreach ($executives as $executive) {
                        if ($executive['role'] == 6) {
                            ?>
                            <option value="<?= $executive['id'] ?>"><?= $executive['first_name'] . " " . $executive['last_name'] ?></option>
    <?php }
} ?>
                </optgroup>
                <optgroup label="Consultant">
                    <?php
                    foreach ($executives as $executive) {
                        if ($executive['role'] == 2) {
                            ?>
                            <option value="<?= $executive['id'] ?>"><?= $executive['first_name'] . " " . $executive['last_name'] ?></option>
    <?php }
} ?>
                </optgroup>
            </select>
        </div>
        
        <div class="row" id="min_max_rev_div">
            <div class="col-sm-12" id="min_max_rate_container">

            </div>
            <div class="col-sm-12">
                <div class="col-sm-2 pull-right text-right" id="add_remove_rate_btn">

                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <button id="check_current_exception_rate" type="button" class="btn btn-primary">Check Current Exception Rate</button>
                    <?php if ($model->isNewRecord) { ?>
                <button type="submit" class="btn btn-success" id="add_exception_rate" disabled="">Add Exception Rate</button>
                <?php } else { ?>
                    <button type="submit" class="btn btn-primary">Update Rate</button>
                <?php } ?>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                
            </div>
        </div>
    <?php ActiveForm::end(); ?>
    </div>
    <?php if ($model->isNewRecord) { ?>
    <?php } else { ?>

<?php } ?>
</div>