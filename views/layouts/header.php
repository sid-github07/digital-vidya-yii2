<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

use app\models\DvUsersRole;
use app\models\DvUserMeta;
use app\models\DvUsersDepartment;

AppAsset::register($this);

/* @var $this \yii\web\View */
/* @var $content string */
/*' . Yii::$app->name . '*/

?>

<header class="main-header">

    <?= Html::a('<span class="logo-mini">DV</span><span class="logo-lg">DV Assist</span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">
          
    <ul class="nav navbar-nav">
        <li><a href='<?=Yii::$app->params['yii_url']?>'>Dashboard</a></li>
    </ul>
    <?php  /*if(!Yii::$app->user->isGuest && !Yii::$app->CustomComponents->is_super_admin()){?>
     <ul class="nav navbar-nav">
        <li><a href='<?=Yii::$app->params['yii_url']?>/mw-users/update?id=<?= Yii::$app->user->identity->id ?>'>Profile</a></li>
    </ul>
    <?php }?>
    <?php if(Yii::$app->CustomComponents->is_super_admin()){?>
     <ul class="nav navbar-nav">
        <li><a href='<?=Yii::$app->params['yii_url']?>/mw-users/update?id=<?= Yii::$app->user->identity->id ?>'>Profile</a></li>
    </ul>
    <?php } ?>
    <?php if(Yii::$app->CustomComponents->is_super_admin()){?>
     <ul class="nav navbar-nav">
        <li><a href='<?=Yii::$app->params['yii_url']?>/mw-users/index'>Admin panel</a></li>
    </ul>
    <?php }*/ ?>
    <?php /*  if(Yii::$app->user->isGuest){?>
     <ul class="nav navbar-nav">
        <li><a href='<?=Yii::$app->params['yii_url']?>/site/login'>Contact</a></li>
    </ul>
    <?php 
      } else {
    ?>
     <ul class="nav navbar-nav">
        <li><a href='<?=Yii::$app->params['yii_url']?>/site/contact'>Contact</a></li>
    </ul>
    <?php
      } */
    ?>
    

            <ul class="nav navbar-nav">

                <!-- Messages: style can be found in dropdown.less-->
                <li class="dropdown messages-menu" style='display:none;'>
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-envelope-o"></i>
                        <span class="label label-success">4</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header">You have 4 messages</li>
                        <li>
                            <!-- inner menu: contains the actual data -->
                            <ul class="menu">
                                <li><!-- start message -->
                                    <a href="#">
                                        <div class="pull-left">
                                            <img src="<?= $directoryAsset ?>/img/avatar.png" class="img-circle"
                                                 alt="User Image"/>
                                        </div>
                                        <h4>
                                            Support Team
                                            <small><i class="fa fa-clock-o"></i> 5 mins</small>
                                        </h4>
                                        <p>Why not buy a new awesome theme?</p>
                                    </a>
                                </li>
                                <!-- end message -->
                                <li>
                                    <a href="#">
                                        <div class="pull-left">
                                            <img src="<?= $directoryAsset ?>/img/user3-128x128.jpg" class="img-circle"
                                                 alt="user image"/>
                                        </div>
                                        <h4>
                                            AdminLTE Design Team
                                            <small><i class="fa fa-clock-o"></i> 2 hours</small>
                                        </h4>
                                        <p>Why not buy a new awesome theme?</p>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <div class="pull-left">
                                            <img src="<?= $directoryAsset ?>/img/user4-128x128.jpg" class="img-circle"
                                                 alt="user image"/>
                                        </div>
                                        <h4>
                                            Developers
                                            <small><i class="fa fa-clock-o"></i> Today</small>
                                        </h4>
                                        <p>Why not buy a new awesome theme?</p>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <div class="pull-left">
                                            <img src="<?= $directoryAsset ?>/img/user3-128x128.jpg" class="img-circle"
                                                 alt="user image"/>
                                        </div>
                                        <h4>
                                            Sales Department
                                            <small><i class="fa fa-clock-o"></i> Yesterday</small>
                                        </h4>
                                        <p>Why not buy a new awesome theme?</p>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <div class="pull-left">
                                            <img src="<?= $directoryAsset ?>/img/user4-128x128.jpg" class="img-circle"
                                                 alt="user image"/>
                                        </div>
                                        <h4>
                                            Reviewers
                                            <small><i class="fa fa-clock-o"></i> 2 days</small>
                                        </h4>
                                        <p>Why not buy a new awesome theme?</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="footer"><a href="#">See All Messages</a></li>
                    </ul>
                </li>
                <li class="dropdown notifications-menu" style='display:none;'>
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-bell-o"></i>
                        <span class="label label-warning">10</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header">You have 10 notifications</li>
                        <li>
                            <!-- inner menu: contains the actual data -->
                            <ul class="menu">
                                <li>
                                    <a href="#">
                                        <i class="fa fa-users text-aqua"></i> 5 new members joined today
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="fa fa-warning text-yellow"></i> Very long description here that may
                                        not fit into the page and may cause design problems
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="fa fa-users text-red"></i> 5 new members joined
                                    </a>
                                </li>

                                <li>
                                    <a href="#">
                                        <i class="fa fa-shopping-cart text-green"></i> 25 sales made
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="fa fa-user text-red"></i> You changed your username
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="footer"><a href="#">View all</a></li>
                    </ul>
                </li>
                <!-- Tasks: style can be found in dropdown.less -->
                <li class="dropdown tasks-menu" style='display:none;'>
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-flag-o"></i>
                        <span class="label label-danger">9</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header">You have 9 tasks</li>
                        <li>
                            <!-- inner menu: contains the actual data -->
                            <ul class="menu">
                                <li><!-- Task item -->
                                    <a href="#">
                                        <h3>
                                            Design some buttons
                                            <small class="pull-right">20%</small>
                                        </h3>
                                        <div class="progress xs">
                                            <div class="progress-bar progress-bar-aqua" style="width: 20%"
                                                 role="progressbar" aria-valuenow="20" aria-valuemin="0"
                                                 aria-valuemax="100">
                                                <span class="sr-only">20% Complete</span>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <!-- end task item -->
                                <li><!-- Task item -->
                                    <a href="#">
                                        <h3>
                                            Create a nice theme
                                            <small class="pull-right">40%</small>
                                        </h3>
                                        <div class="progress xs">
                                            <div class="progress-bar progress-bar-green" style="width: 40%"
                                                 role="progressbar" aria-valuenow="20" aria-valuemin="0"
                                                 aria-valuemax="100">
                                                <span class="sr-only">40% Complete</span>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <!-- end task item -->
                                <li><!-- Task item -->
                                    <a href="#">
                                        <h3>
                                            Some task I need to do
                                            <small class="pull-right">60%</small>
                                        </h3>
                                        <div class="progress xs">
                                            <div class="progress-bar progress-bar-red" style="width: 60%"
                                                 role="progressbar" aria-valuenow="20" aria-valuemin="0"
                                                 aria-valuemax="100">
                                                <span class="sr-only">60% Complete</span>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <!-- end task item -->
                                <li><!-- Task item -->
                                    <a href="#">
                                        <h3>
                                            Make beautiful transitions
                                            <small class="pull-right">80%</small>
                                        </h3>
                                        <div class="progress xs">
                                            <div class="progress-bar progress-bar-yellow" style="width: 80%"
                                                 role="progressbar" aria-valuenow="20" aria-valuemin="0"
                                                 aria-valuemax="100">
                                                <span class="sr-only">80% Complete</span>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <!-- end task item -->
                            </ul>
                        </li>
                        <li class="footer">
                            <a href="#">View all tasks</a>
                        </li>
                    </ul>
                </li>
                <!-- User Account: style can be found in dropdown.less -->

                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">                       
                        <img src="<?php echo Yii::$app->CustomComponents->dvuser_avatar(Yii::$app->getUser()->identity->id); ?>" class="user-image" alt="<?=Yii::$app->user->identity->first_name;?> <?=Yii::$app->user->identity->last_name;?>"/>
                        <span class="hidden-xs"><?=Yii::$app->user->identity->first_name;?> <?=Yii::$app->user->identity->last_name;?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="<?php echo Yii::$app->CustomComponents->dvuser_avatar(Yii::$app->getUser()->identity->id); ?>" class="img-circle"
                                 alt="<?=Yii::$app->user->identity->first_name;?> <?=Yii::$app->user->identity->last_name;?>"/>
                            <p>
                               <?=Yii::$app->user->identity->first_name;?> <?=Yii::$app->user->identity->last_name;?>

                               <?php 
                                    $user = Yii::$app->user->identity;
                                    $usermeta_result = DvUserMeta::find()->where(['uid'=>$user->id,'meta_key'=>'role'])->one();
                                    $user_department_id = $user->department; // 1 - sales department

                                    $user_department_name = DvUsersDepartment::find()->where(['id'=>$user_department_id])->one()->name;
                                    
                                    $user_role_id = $usermeta_result->meta_value; // 2 - Executive role. // 1 - Admin
                                    $user_role_name = DvUsersRole::find()->where(['id'=>$user_role_id])->one()->name;
                                    echo "<small>".$user_department_name." ".$user_role_name."</small>";
                               ?>
                                <!-- <small>Member of Digital Vidya</small> -->
                            </p>
                        </li>
                        <!-- Menu Body -->
                        <li class="user-body">
                            <div class="col-xs-6 text-center">
                                <a href='<?=Yii::$app->params['yii_url']?>/site/contact'>Contact</a>
                            </div>
                     <div class="col-xs-6 text-center">
                        <a href='<?=Yii::$app->params['yii_url']?>/dv-users/view?id=<?php echo Yii::$app->getUser()->identity->id ?>'>Your Profile</a>
                     </div>                            
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                           
                            <div class="pull-right">
                                <?= Html::a(
                                    'Sign out',
                                    ['/site/logout'],
                                    ['data-method' => 'post', 'class' => 'btn btn-info btn-flat']
                                ) ?>
                            </div>
                        </li>
                    </ul>
                </li>

                <!-- User Account: style can be found in dropdown.less -->
                <li>
                    <a href="#" data-toggle="control-sidebar" style='display:none;'><i class="fa fa-gears"></i></a>
                </li>
            </ul>
        </div>
    </nav>
</header>
