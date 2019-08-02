<?php

use yii\helpers\Html;
use yii\data\Pagination;
use yii\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\DvIncentive;
?>
<div class="dv-users-form">
    <?php $form = ActiveForm::begin([
        'fieldConfig' => ['options' => ['class' => 'form-group col-md-4']],
        ]); ?>
    <?= $form->field($model, 'inc_per')->hint('Enter Only Digits')->input('text', ['placeholder' => "Incentive in Per(%)",'required' => 'required'])->label(false); ?>
    <?= $form->field($model, 'description')->textarea(['placeholder' => "Incentive info"])->label(false); ?>

    <?php if($model->isNewRecord){ ?>
    <?= $form->field($model, 'status')->hiddenInput(['value'=>'1'])->label(false); ?>
    <?php } else { ?>
        <?= $form->field($model, 'status')->dropDownList([1 =>'Active',0 => 'Inactive'],['prompt'=>'Select Status','required' => 'required'])->label(false); ?>
    <?php } ?>
   <div class="form-group col-md-12">
        <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-check"></i> Create Incentive' : '<i class="fa fa-pencil"></i> Update Incentive', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?php if(!$model->isNewRecord) { ?>
           <?= Html::a('<i class="fa fa-times"></i> Cancel', ['create_incentive'], ['class' => 'btn back_button btn-danger cancel_button']); ?>
        <?php } ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
  <?php 
        $query = DvIncentive::find();
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count,'pageSize' => 10]);
        $incentive_result = $query->offset($pagination->offset)->limit($pagination->limit)
        ->orderBy(['id' => SORT_DESC])->all(); 
        ?>   
  <table class="table table-striped">
    <thead>
        <tr><th>#</th>
            <th>Incentive in per(%)</th>
            <th>Description</th>
            <th><center>Updated Date</center></th>
            <th><center>Status</center></th>
            <th><center>Edit</center></th>
        </tr>
    </thead>
    <tbody>
    <?php 
        $cnt = $pagination->offset+1;
        foreach($incentive_result as $incentive){
            $dt = new DateTime($incentive->updated_at);
            if($incentive->status == 1){
                $status = '<center><i data-toggle="tooltip" data-placement="top" title="Active" class="fa fa-check-circle green_icon"></i></center>';
            } else {
                $status = '<center><i data-toggle="tooltip" data-placement="top" title="In-Active" class="fa fa-times-circle red_icon"></i></center>';
            }
            echo '<tr>
            <td> <a class="btn btn-xs btn-info" data-toggle="tooltip" data-placement="top" title="View Details" href="edit_incentive?id='.$incentive->id.'"><strong>' . $cnt.'</strong></a> </td>
            <td> '.$incentive->inc_per . '% </td>
            <td> '.$incentive->description . ' </td>
            <td><center>'.$dt->format('d M, Y').'</center></td>
            <td><center>'.$status.'</center></td>
            <td><center><a data-toggle="tooltip" data-placement="top" title="Edit" href="edit_incentive?id='.$incentive->id.'"><i class="fa fa-pencil"></i></a></center></td>
             </tr>';

            $cnt++;
        } ?>
    </tbody>
</table>
<?php // display pagination
    echo LinkPager::widget(['pagination' => $pagination]);
?>
<div class="pull-right"><ul class="pagination"><li>
    <a class="btn btn-xs btn-info" data-toggle="tooltip" data-placement="top" title="" data-original-title="Total Listed: <?=$count ?>"><strong>Total List : <?= $count ?></strong></a></li></ul>
</div>