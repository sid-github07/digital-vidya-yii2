<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\models\DvUserMeta;

AppAsset::register($this);

/* @var $this \yii\web\View */
/* @var $content string */
/*' . Yii::$app->name . '*/

?>

<aside class="main-sidebar cdo">
    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?php echo Yii::$app->CustomComponents->dvuser_avatar(Yii::$app->getUser()->identity->id); ?>" class="img-circle" alt="<?=Yii::$app->user->identity->first_name;?> <?=Yii::$app->user->identity->last_name;?>"/>
            </div>
            <div class="pull-left info"><br>
                <p><?=Yii::$app->user->identity->first_name;?> <?=Yii::$app->user->identity->last_name;?></p>
                <a href="#" style='display:none;'><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <!-- search form -->
        <form action="#" method="get" class="sidebar-form" style='display:none;'>
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search..."/>
              <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
            </div>
        </form>
        <!-- /.search form -->

<?php //Condition for Usesr Menu Item
        if(Yii::$app->CustomComponents->check_permission('create_user') || Yii::$app->CustomComponents->check_permission('create_team') || Yii::$app->CustomComponents->check_permission('user_role') || Yii::$app->CustomComponents->check_permission('department')) {
            $display_users = 1;
        } else {
            $display_users = 0;
        } 

    //Condition for Registration Menu Item
        if(Yii::$app->CustomComponents->check_permission('registration') || Yii::$app->CustomComponents->check_permission('new_registration') || Yii::$app->CustomComponents->check_permission('view_registration')) {
            $display_registration = 1;
        } else {
            $display_registration = 0;
        }

    //Condition for Module Menu Item
        if(Yii::$app->CustomComponents->check_permission('delivery') || Yii::$app->CustomComponents->check_permission('create_modules') || Yii::$app->CustomComponents->check_permission('create_course') || Yii::$app->CustomComponents->check_permission('training_topics') || Yii::$app->CustomComponents->check_permission('view_monthly_incentive_rate') || Yii::$app->CustomComponents->check_permission('view_full_payment_incentive_rate')) {
            $display_module = 1;
        } else {
            $display_module = 0;
        }    


     //Condition for Reports Menu Item
        if(Yii::$app->CustomComponents->check_permission('reports') || Yii::$app->CustomComponents->check_permission('sales_incentive_report')) {
            $display_reports = 1;
        } else {
            $display_reports = 0;
        }    

        //Condition for Target Menu Item
        if(Yii::$app->CustomComponents->check_permission('targets') || Yii::$app->CustomComponents->check_permission('create_target')) {
            $display_targets = 1;
        } else {
            $display_targets = 0;
        }    

          $user = Yii::$app->user->identity;
          $usermeta_result = DvUserMeta::find()->where(['uid'=>$user->id,'meta_key'=>'role'])->one();
          $user_role = $usermeta_result->meta_value; // 1 - admin

          if($user_role == 1){
        ?>
        <?php 
        echo dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu', 'data-widget' => 'tree'],
                'items' => [
                    ['label' => 'Users', 'icon' => 'address-card',  'url' => ['dv-users/index'],'visible' => $display_users, 'items' => [
                            ['label' => 'All Users', 'icon' => 'users', 'url' => ['/dv-users/index'],'visible' => Yii::$app->CustomComponents->check_permission('all_users')],
                            ['label' => 'Create User', 'icon' => 'user-circle', 'url' => ['/dv-users/create'],'visible' => Yii::$app->CustomComponents->check_permission('create_user')],
                            ['label' => 'Assign Manager', 'icon' => 'sitemap', 'url' => ['/dv-users/assign_team'],'visible' => Yii::$app->CustomComponents->check_permission('assign_team')],
                            ['label' => 'Create User Role', 'icon' => 'user-plus', 'url' => ['/dv-users/create_role'],'visible' => Yii::$app->CustomComponents->check_permission('user_role')],
                            ['label' => 'Create Department', 'icon' => 'vcard-o', 'url' => ['/dv-users/create_department'],'visible' => Yii::$app->CustomComponents->check_permission('department')],
                     ]],
                     ['label' => 'Registration', 'icon' => 'graduation-cap', 'url' => ['dv-registration/index'],'visible' => $display_registration, 'items' => [
                            ['label' => 'New Registration', 'icon' => 'files-o', 'url' => ['/dv-registration/create'],'visible' => Yii::$app->CustomComponents->check_permission('new_registration')],
                            ['label' => 'All Registration', 'icon' => 'male', 'url' => ['/dv-registration/index'],'visible' => Yii::$app->CustomComponents->check_permission('registration')],
                            /*['label' => 'Search By Email', 'icon' => 'search', 'url' => ['/dv-registration/search_by_email'],'visible' => Yii::$app->CustomComponents->check_permission('search_by_email')],
                            ['label' => 'Update Installments', 'icon' => 'edit', 'url' => ['/dv-registration/update_installments'],'visible' => Yii::$app->CustomComponents->check_permission('update_installments')],
                            ['label' => 'Update Participant Status', 'icon' => 'edit', 'url' => ['/dv-registration/update_participant_status'],'visible' => Yii::$app->CustomComponents->check_permission('update_participant_status')],*/
                            ['label' => 'Pending Revenue', 'icon' => 'th', 'url' => ['/dv-registration/pending_revenue'],'visible' => Yii::$app->CustomComponents->check_permission('pending_revenue')],
                            ['label' => 'Users Activity Log', 'icon' => 'book', 'url' => ['/dv-registration/users_activity_log'],'visible' => Yii::$app->CustomComponents->check_permission('users_activity_log')],
                     ]],
                     ['label' => 'Delivery', 'icon' => 'truck', 'url' => ['dv-delivery/index'],'visible' => $display_module,
                     'items' => [
                        ['label' => 'Module', 'icon' => 'object-group', 'url' => ['/dv-module/index'],'visible' => Yii::$app->CustomComponents->check_permission('create_modules')],
                        ['label' => 'Course', 'icon' => 'building', 'url' => ['/dv-course/index'],'visible' => Yii::$app->CustomComponents->check_permission('course')],
                        ['label' => 'Batch', 'icon' => 'building', 'url' => ['/dv-delivery/index'],'visible' => Yii::$app->CustomComponents->check_permission('course')],
                        ['label' => 'Upcoming Batches', 'icon' => 'building', 'url' => ['/dv-batch-allotment/all_batch_list'],'visible' => Yii::$app->CustomComponents->check_permission('course')],
                        ['label' => ' Possible Core Batches', 'icon' => 'building', 'url' => ['/dv-batch-allotment/index'],'visible' => Yii::$app->CustomComponents->check_permission('course')],
                        ['label' => ' Possible Special Batches', 'icon' => 'building', 'url' => ['/dv-batch-allotment/possible_special_modules'],'visible' => Yii::$app->CustomComponents->check_permission('course')],
                        ['label' => 'Participant', 'icon' => 'building', 'url' => ['/dv-delievery-members/index'],'visible' => Yii::$app->CustomComponents->check_permission('course')],

                        /*['label' => 'All Batch', 'icon' => 'object-ungroup', 'url' => ['/dv-delivery/index'],'visible' => Yii::$app->CustomComponents->check_permission('delivery')],*/
                        /*['label' => 'Training Topics', 'icon' => 'coffee', 'url' => ['/dv-delivery/training_topics'],'visible' => Yii::$app->CustomComponents->check_permission('topics')],*/
                     ]],   

                     ['label' => 'Incentive', 'icon' => 'industry', 'url' => ['dv-finance/create_currency'],'visible' => $display_module,
                     'items' => [
                        ['label' => 'Create Currency', 'icon' => 'dollar', 'url' => ['/dv-finance/create_currency'],'visible' => Yii::$app->CustomComponents->check_permission('currency'),'options' => ['class' => 'sr-only']],
                        ['label' => 'Create Payment Mode', 'icon' => 'credit-card', 'url' => ['/dv-finance/create_payment_mode'],'visible' => Yii::$app->CustomComponents->check_permission('payment'),'options' => ['class' => 'sr-only']],
                        ['label' => 'Create Incentive', 'icon' => 'hourglass-start', 'url' => ['/dv-finance/create_incentive'],'visible' => Yii::$app->CustomComponents->check_permission('incentive'),'options' => ['class' => 'sr-only']],
                        ['label' => 'Create Gst', 'icon' => 'money', 'url' => ['/dv-finance/create_gst'],'visible' => Yii::$app->CustomComponents->check_permission('create_gst'),'options' => ['class' => 'sr-only']],
                         ['label' => 'Domain Normalization', 'icon' => 'building', 'url' => ['/dv-finance/create_sales'],'visible' => Yii::$app->CustomComponents->check_permission('sales')],
                         ['label' => 'Consultant Incentive', 'icon' => 'table', 'url' => ['/dv-finance/manage_monthly_incentive_rate'],'visible' => Yii::$app->CustomComponents->check_permission('manage_monthly_incentive_rate')],
                         ['label' => 'View Consultant Incentive - Current Rate', 'icon' => 'sticky-note', 'url' => ['/dv-finance/view_monthly_incentive_rate'],'visible' => Yii::$app->CustomComponents->check_permission('view_monthly_incentive_rate'),'options' => ['class' => 'pre_wrap_txt']],
                         //['label' => 'View Monthly Incentive - All Time Rate', 'icon' => 'gift', 'url' => ['/dv-finance/view_monthly_incentive_all_time_rate'],'visible' => Yii::$app->CustomComponents->check_permission('view_monthly_incentive_all_time_rate'),'options' => ['class' => 'pre_wrap_txt']],
                         ['label' => 'Full Payment Incentive', 'icon' => 'industry', 'url' => ['/dv-finance/manage_full_payment_incentive_rate'],'visible' => Yii::$app->CustomComponents->check_permission('manage_full_payment_incentive_rate')]
                     ]],

                     ['label' => 'Reports', 'icon' => 'bar-chart', 'url' => ['dv-reports/sales_incentive_report'],'visible' => $display_reports,
                     'items' => [
                          ['label' => 'Sales Incentive Report', 'icon' => 'calendar-times-o', 'url' => ['/dv-reports/sales_incentive_report'],'visible' => Yii::$app->CustomComponents->check_permission('sales_incentive_report'),'options' => ['class' => 'sr-only']],
                          ['label' => 'Monthly Sales Report', 'icon' => 'calendar-times-o', 'url' => ['dv-reports/monthly_sales_report'],'visible' => Yii::$app->CustomComponents->check_permission('monthly_sales_report'),'options' => ['class' => 'sr-only']],
                         ['label' => 'Sales Report', 'icon' => 'table', 'url' => ['dv-reports/sales_report'],'visible' => Yii::$app->CustomComponents->check_permission('sales_report')],
                         ['label' => 'Consultant Report', 'icon' => 'table', 'url' => ['/dv-reports/consultant_dashboard_report'],'visible' => Yii::$app->CustomComponents->check_permission('consultant_dashboard_report')],
                         ['label' => 'Team Manager Report', 'icon' => 'table', 'url' => ['/dv-reports/team_manager_dashboard'],'visible' => Yii::$app->CustomComponents->check_permission('team_manager_dashboard')],
                         ['label' => 'Incentive Report', 'icon' => 'table', 'url' => ['/dv-reports/incentive_report'],'visible' => Yii::$app->CustomComponents->check_permission('incentive_report')],
                     ]],
                ],
            ]
        ); 

        }else{
            echo dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu', 'data-widget' => 'tree'],
                'items' => [
                    ['label' => 'Users', 'icon' => 'address-card',  'url' => ['dv-users/index'],'visible' => $display_users, 'items' => [
                            ['label' => 'All Users', 'icon' => 'users', 'url' => ['/dv-users/index'],'visible' => Yii::$app->CustomComponents->check_permission('all_users')],
                            ['label' => 'Create User', 'icon' => 'user-circle', 'url' => ['/dv-users/create'],'visible' => Yii::$app->CustomComponents->check_permission('create_user')],
                            ['label' => 'Create Team', 'icon' => 'sitemap', 'url' => ['/dv-users/create_team'],'visible' => Yii::$app->CustomComponents->check_permission('create_team')],
                            ['label' => 'Create User Role', 'icon' => 'user-plus', 'url' => ['/dv-users/create_role'],'visible' => Yii::$app->CustomComponents->check_permission('user_role')],
                            ['label' => 'Create Department', 'icon' => 'vcard-o', 'url' => ['/dv-users/create_department'],'visible' => Yii::$app->CustomComponents->check_permission('department')],
                     ]],
                     ['label' => 'Registration', 'icon' => 'graduation-cap', 'url' => ['dv-registration/index'],'visible' => $display_registration, 'items' => [
                            ['label' => 'New Registration', 'icon' => 'files-o', 'url' => ['/dv-registration/create'],'visible' => Yii::$app->CustomComponents->check_permission('new_registration')],
                            ['label' => 'My Registration', 'icon' => 'male', 'url' => ['/dv-registration/index'],'visible' => Yii::$app->CustomComponents->check_permission('registration')],
                            ['label' => 'My Team Registration', 'icon' => 'plus', 'url' => ['/dv-registration/teamview'],'visible' => Yii::$app->CustomComponents->check_permission('team_registration')],

                            /*['label' => 'Search By Email', 'icon' => 'search', 'url' => ['/dv-registration/search_by_email'],'visible' => Yii::$app->CustomComponents->check_permission('search_by_email')],
                            ['label' => 'Update Installments', 'icon' => 'edit', 'url' => ['/dv-registration/update_installments'],'visible' => Yii::$app->CustomComponents->check_permission('update_installments')],
                            ['label' => 'Update Participant Status', 'icon' => 'edit', 'url' => ['/dv-registration/update_participant_status'],'visible' => Yii::$app->CustomComponents->check_permission('update_participant_status')],*/
                            ['label' => 'Pending Revenue', 'icon' => 'th', 'url' => ['/dv-registration/pending_revenue'],'visible' => Yii::$app->CustomComponents->check_permission('pending_revenue')],
                            ['label' => 'Users Activity Log', 'icon' => 'book', 'url' => ['/dv-registration/users_activity_log'],'visible' => Yii::$app->CustomComponents->check_permission('users_activity_log')],
                     ]],
                     ['label' => 'Delivery', 'icon' => 'truck', 'url' => ['dv-delivery/index'],'visible' => $display_module,
                     'items' => [
                        ['label' => 'All Modules', 'icon' => 'object-ungroup', 'url' => ['/dv-delivery/index'],'visible' => Yii::$app->CustomComponents->check_permission('delivery')],
                        ['label' => 'Create Modules', 'icon' => 'object-group', 'url' => ['/dv-delivery/create_module'],'visible' => Yii::$app->CustomComponents->check_permission('create_modules')],
                        ['label' => 'Create Course', 'icon' => 'building', 'url' => ['/dv-delivery/create_course'],'visible' => Yii::$app->CustomComponents->check_permission('course')],
                        
                     ],'options' => ['class' => 'sr-only']],

                     ['label' => 'Incentive', 'icon' => 'industry', 'url' => ['dv-finance/create_currency'],'visible' => $display_module,
                     'items' => [
                        ['label' => 'Create Currency', 'icon' => 'dollar', 'url' => ['/dv-finance/create_currency'],'visible' => Yii::$app->CustomComponents->check_permission('currency')],               
                        ['label' => 'Create Payment Mode', 'icon' => 'credit-card', 'url' => ['/dv-finance/create_payment_mode'],'visible' => Yii::$app->CustomComponents->check_permission('payment')],               
                        ['label' => 'Create Incentive', 'icon' => 'hourglass-start', 'url' => ['/dv-finance/create_incentive'],'visible' => Yii::$app->CustomComponents->check_permission('incentive')],
                         ['label' => 'Consultant Incentive', 'icon' => 'table', 'url' => ['/dv-finance/manage_monthly_incentive_rate'],'visible' => Yii::$app->CustomComponents->check_permission('manage_monthly_incentive_rate')],
                         ['label' => 'View Consultant Incentive', 'icon' => 'sticky-note', 'url' => ['/dv-finance/view_monthly_incentive_rate'],'visible' => Yii::$app->CustomComponents->check_permission('view_monthly_incentive_rate')],
                         ['label' => 'Team Member Exception', 'icon' => 'industry', 'url' => ['/dv-finance/manage_team_member_exception'],'visible' => Yii::$app->CustomComponents->check_permission('manage_team_member_exception')],
                         ['label' => 'View All Exception', 'icon' => 'columns', 'url' => ['/dv-finance/view_all_exception'],'visible' => Yii::$app->CustomComponents->check_permission('view_all_exception')],
                         ['label' => 'Full Payment Incentive', 'icon' => 'tags', 'url' => ['/dv-finance/manage_full_payment_incentive_rate'],'visible' => Yii::$app->CustomComponents->check_permission('manage_full_payment_incentive_rate')],
                         ['label' => 'View Exception Rate', 'icon' => 'eraser', 'url' => ['/dv-finance/view_current_executive_exception'],'visible' => Yii::$app->CustomComponents->check_permission('view_current_executive_exception')]
                     ]],

                     ['label' => 'Reports', 'icon' => 'bar-chart', 'url' => ['dv-reports/sales_incentive_report'],'visible' => $display_reports,
                     'items' => [
                          ['label' => 'Sales Incentive Report', 'icon' => 'calendar-times-o', 'url' => ['/dv-reports/sales_incentive_report'],'visible' => Yii::$app->CustomComponents->check_permission('sales_incentive_report'),'options' => ['class' => 'sr-only']],
                          ['label' => 'Monthly Sales Report', 'icon' => 'calendar-times-o', 'url' => ['dv-reports/monthly_sales_report'],'visible' => Yii::$app->CustomComponents->check_permission('monthly_sales_report'),'options' => ['class' => 'sr-only']],
                          ['label' => 'Yearly Sales Incentive', 'icon' => 'calendar-times-o', 'url' => ['dv-reports/yearly_sales_incentive_report'],'visible' => Yii::$app->CustomComponents->check_permission('yearly_sales_incentive'),'options' => ['class' => 'sr-only']],
                          ['label' => 'Sales Report', 'icon' => 'table', 'url' => ['dv-reports/sales_report'],'visible' => Yii::$app->CustomComponents->check_permission('sales_report')],
                         ['label' => 'Consultant Report', 'icon' => 'table', 'url' => ['/dv-reports/consultant_dashboard_report'],'visible' => Yii::$app->CustomComponents->check_permission('consultant_dashboard_report')],
                         ['label' => 'Team Manager Report', 'icon' => 'table', 'url' => ['/dv-reports/team_manager_dashboard'],'visible' => Yii::$app->CustomComponents->check_permission('team_manager_dashboard')],
                         ['label' => 'Incentive Report', 'icon' => 'table', 'url' => ['/dv-reports/incentive_report'],'visible' => Yii::$app->CustomComponents->check_permission('incentive_report')],
                     ]],
                     ['label' => 'Targets', 'icon' => 'bar-chart', 'url' => ['dv-reports/sales_targets'],'visible' => $display_targets,
                     'items' => [
                          ['label' => 'Create Target', 'icon' => 'user-plus', 'url' => ['/dv-users/create_target'],'visible' => Yii::$app->CustomComponents->check_permission('create_target')],
                          ['label' => 'View Target', 'icon' => 'eye', 'url' => ['/dv-users/view_target'],'visible' => Yii::$app->CustomComponents->check_permission('view_target')]
                     ]]
                ],
            ]
        );
        }
        ?>
    </section>
</aside>
