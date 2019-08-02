<?php

use yii\helpers\Html;
use app\models\DvSales;
use yii\data\Pagination;
use yii\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\DvUsers */
/* @var $form yii\widgets\ActiveForm */
?>
<div style="min-height:35px; "></div>
<div class="dv-users-form fullwidth_cls">
    <?php
    $form = ActiveForm::begin([
                'fieldConfig' => ['options' => ['class' => 'form-group col-md-5']],
    ]);
    ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => true])->input('name', ['placeholder' => "Product Name"])->label(false); ?>

    <?= $form->field($model, 'mcourse')->dropDownList(['dm' => 'Digital Marketing', 'da' => 'Digital Analytics'], ['prompt' => 'Select Domain'])->label(false); ?>

    <?= $form->field($model, 'normalize_rate')->textInput(['maxlength' => true])->input('normalize_rate', ['placeholder' => "Normalization Rate", 'pattern' => '[0-9]+(\.[0-9][0-9]?)?'])->label(false); ?>

    <?php if ($model->isNewRecord) { ?>
        <?= $form->field($model, 'status')->hiddenInput(['value' => '1'])->label(false); ?>
    <?php } else { ?>
        <?= $form->field($model, 'status')->dropDownList([1 => 'Active', 0 => 'Inactive'], ['prompt' => 'Select Course Status', 'required' => 'required'])->label(false); ?>
    <?php } ?>

    <div class="form-group col-md-12">
        <?php if ($model->isNewRecord) { ?>
            <?= Html::submitButton('<i class="fa fa-check"></i> Create Domain', ['class' => 'btn btn-success']) ?>
        <?php } else { ?>
            <?= Html::submitButton('<i class="fa fa-pencil"></i> Update Domain', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fa fa-times"></i> Cancel', ['create_sales'], ['class' => 'btn back_button btn-danger cancel_button']); ?>
        <?php } ?>
    </div>


    <?php ActiveForm::end(); ?>
</div>
<?php
$query = DvSales::find();
$count = $query->count();
$pagination = new Pagination(['totalCount' => $count, 'pageSize' => 25]);
$models = $query->offset($pagination->offset)->limit($pagination->limit)->all();
?>   
<div class="form-group table-responsive fullwidth_cls"> 
    <table class="table table-striped">
        <thead>
            <tr><th>#</th>
                <th>Product Name</th>
                <th>Domain</th>
                <th><center>Normalization Rate</center></th>
        <th><center>Status</center></th>
        <th><center>Created On</center></th>
        <th><center>Edit</center></th>
        </tr>
        </thead>
        <tbody>
            <?php
            foreach ($models as $module) {
                if ($module->status == 1) {
                    $status = '<center><i class="fa fa-check-circle green_icon"></i></center>';
                } else {
                    $status = '<center><i class="fa fa-times-circle red_icon"></i></center>';
                }

                if ($module->mcourse == 'dm') {
                    $mcourse = 'Digital Marketing';
                } else if ($module->mcourse == 'da') {
                    $mcourse = 'Digital Analytics';
                } else {
                    $mcourse = '';
                }

                echo '<tr>
            <td><a class="btn btn-xs btn-info" href="edit_sales?id=' . $module->id . '"><strong>' . $module->id . '</strong></a> </td>
            <td>' . $module->name . '</td>
            <td>' . $mcourse . '</td>
            <td><center>' . $module->normalize_rate . '</center></td>
            <td><center>' . $status . '</center></td>
            <td><center>' . date("d-m-Y", strtotime($module->created)) . '</center></td>
            <td><center><a href="edit_sales?id=' . $module->id . '"><i class="fa fa-pencil"></i></a></center></td>
            </tr>';
            }
            ?>
        </tbody>
    </table>
    <?php
    /* display pagination */
    echo LinkPager::widget(['pagination' => $pagination]);
    ?>
</div>
<script>
    function remove_required() {
        var status = $('#dvusers-status').val();
        if (status == 0) {
            $('input, select, textarea').removeAttr('required');
        }
    }
</script>