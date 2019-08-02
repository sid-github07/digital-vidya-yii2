<?php

/* @var $this yii\web\View */

$this->title = Yii::$app->params['site_name'];

?>
<?php $user = Yii::$app->user->identity->username;
//Yii::$app->params['site_name'];
?>
<style>.content-header{display:none;}</style>
<div class="site-index">
    <div class="body-content">
        <?php //start of users section
        if(Yii::$app->CustomComponents->check_permission('create_user') || Yii::$app->CustomComponents->check_permission('create_team') || Yii::$app->CustomComponents->check_permission('user_role') || Yii::$app->CustomComponents->check_permission('department')) {  ?>
        <h3><a href="<?=Yii::$app->params['yii_url']?>/dv-users/">Digital Vidya Users</a></h3>
        <div class="row">
            <?php if(Yii::$app->CustomComponents->check_permission('create_user')){ ?>
            <div class="col-lg-4">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">Create User</h3>
                    </div>
                    <div class="panel-body">
                        <p>You can create new Digital Vidya User from here.</p>
                        <p><a class="btn btn-default" href="<?=Yii::$app->params['yii_url']?>/dv-users/create">Create User &raquo;</a></p>
                    </div>
                </div>
            </div>
             <?php }
             if(Yii::$app->CustomComponents->check_permission('create_team')){ ?>
             <div class="col-lg-4">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">Create Team for Users</h3>
                    </div>
                    <div class="panel-body">
                        <p>You can Create Team for Digital Vidya Users</p>
                        <p><a class="btn btn-default" href="<?=Yii::$app->params['yii_url']?>/dv-users/create_team">Create Team &raquo;</a></p>
                    </div>
                </div>
            </div>
        <?php } 
        if(Yii::$app->CustomComponents->check_permission('user_role')){ ?>
            <div class="col-lg-4">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">Create User Role</h3>
                    </div>
                    <div class="panel-body">
                        <p>You can Create Role for Digital Vidya Users</p>
                        <p><a class="btn btn-default" href="<?=Yii::$app->params['yii_url']?>/dv-users/create_role">Create Role &raquo;</a></p>
                    </div>
                </div>
            </div>
        <?php }
        if(Yii::$app->CustomComponents->check_permission('department')){ ?>
            <div class="col-lg-4">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">Create Department</h3>
                    </div>
                    <div class="panel-body">
                        <p>You Can Create different Users Departments</p>
                        <p><a class="btn btn-default" href="<?=Yii::$app->params['yii_url']?>/dv-users/create_department">Create Department &raquo;</a></p>
                    </div>
                </div>
            </div>   
            <?php } ?>
        </div>
<hr>

<?php } //end of users section ?>


<?php  //start of Registration section
if(Yii::$app->CustomComponents->check_permission('new_registration') || Yii::$app->CustomComponents->check_permission('registration')){ ?>
        <h3><a href="<?=Yii::$app->params['yii_url']?>/dv-registration/">Participant Registration</a></h3>
        <div class="row">
            <?php if(Yii::$app->CustomComponents->check_permission('new_registration')){ ?>
            <div class="col-lg-4">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">Create Participant</h3>
                    </div>
                    <div class="panel-body">
                        <p>You can Registration Participant for Digital Vidya.</p>
                        <p><a class="btn btn-default" href="<?=Yii::$app->params['yii_url']?>/dv-registration/create">Create Participant &raquo;</a></p>
                    </div>
                </div>
            </div>
        <?php } ?>
        </div>
<hr>
<?php }  //End of Registration section ?>


<?php  //start of Module/Batch section 
if(Yii::$app->CustomComponents->check_permission('create_modules') || Yii::$app->CustomComponents->check_permission('course') || Yii::$app->CustomComponents->check_permission('topics')){ ?>
        <h3><a href="<?=Yii::$app->params['yii_url']?>/dv-delivery/">Module/Batch</a></h3>
        <div class="row">
            <?php if(Yii::$app->CustomComponents->check_permission('create_modules')){ ?>
            <div class="col-lg-4">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">Create Module/Batch</h3>
                    </div>
                    <div class="panel-body">
                        <p>You can create Module/Batch for Digital Vidya.</p>
                        <p><a class="btn btn-default" href="<?=Yii::$app->params['yii_url']?>/dv-delivery/create_module">Create Module/Batch &raquo;</a></p>
                    </div>
                </div>
            </div>
            <?php }
            if(Yii::$app->CustomComponents->check_permission('course')){ ?>
            <div class="col-lg-4">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">Create Course</h3>
                    </div>
                    <div class="panel-body">
                        <p>You can Create Course for Digital Vidya.</p>
                        <p><a class="btn btn-default" href="<?=Yii::$app->params['yii_url']?>/dv-delivery/create_course">Create Course &raquo;</a></p>
                    </div>
                </div>
            </div>
            <?php }
            if(Yii::$app->CustomComponents->check_permission('topics')){ ?>
            <div class="col-lg-4">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">Create Training Topics</h3>
                    </div>
                    <div class="panel-body">
                        <p>You can Create Training Topics for Digital Vidya.</p>
                        <p><a class="btn btn-default" href="<?=Yii::$app->params['yii_url']?>/dv-delivery/training_topics">Create Training Topics &raquo;</a></p>
                    </div>
                </div>
            </div>
        <?php } ?>
        </div>
<?php }  //End of Module/Batch section ?>


    </div>
</div>