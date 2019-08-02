<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* This file contain a file with form Assign Manager */

$this->title = 'Assign Modules';
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dv-assign-modules">
  <div class="row">
    <h1><?= Html::encode($this->title) ?></h1>
    <form method="post" action="<?=Yii::$app->params['yii_url']?>/dv-users/assign_modules_save">

        <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />
        <input type="hidden" name="user_id" value="<?= $id; ?>" />
        <?php
        if(count($module_cat)){
            foreach($module_cat as $module_key => $module_sub){ ?>
                <div class="col-md-3 col-sm-2">
                    <h3><?php echo $module_key?></h3>
                    <?php foreach($module_sub as $model){  ?>
                        <div>
                            <input type="checkbox" <?php if(isset($model['assigned'])){if($model['assigned'] == 1 )echo 'checked';} ?> id="<?=$model['module_id'];?>" name="module_assigned[]" value="<?=$model['module_id'];?>">
                            <label for="<?=$model['module_id'];?>"><?=$model['module_name']?></label>
                        </div>
                    <?php } ?>
                </div>
            <?php
            }
        }
        ?>
        <div class="clearfix"></div>
        <div class="text-center">
        <input type="submit" class="btn btn-primary" value="Assign">
        </div>

    </form>
  </div>
</div>
