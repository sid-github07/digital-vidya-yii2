<?php

use yii\helpers\Html;
use app\models\DvRegistration;
use app\models\DvUsers;
use app\models\DvUserMeta;
use app\models\DvCourse;
use app\models\DvSales;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

use kartik\export\ExportMenu;
use yii\data\ArrayDataProvider;


$this->title = 'Sales Report';
$this->params['breadcrumbs'][] = "Reports";
$this->params['breadcrumbs'][] = $this->title;

$month_arr = array('1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April', '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August', '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
$min_year = 2018;
?>
<?php ?>
<div class="dv-sales-monthly-report">
    <div class="search_form row">
        <?php
        $form = ActiveForm::begin(['id' => 'sales-report-form', 'method' => 'post']);
        $select = 'selected="selected"';
        ?>
        <?php // if ($current_user_role == 7) { ?>
            <div class="col-md-3 form-group">
                <label class="control-label"><small>&nbsp;</small></label>
                <select class="form-control" name="managers" id="sales_managers">
                    <option value="">Select Team</option>
                    <?php
                    if (!empty($sales_managers)) {
                        foreach ($sales_managers as $manager) {
                            ?>
                            <option value="<?= $manager['id'] ?>" <?php if ($filtered_data['selected_manager'] == $manager['id']) echo $select ?>>
                                <?= $manager['first_name'] . " " . $manager['last_name']; ?>
                            </option>
                            <?php
                        }
                    }
                    ?>
                </select>
            </div>
        <?php // } ?>

        <div class="col-md-3 form-group">
            <label class="control-label"><small>Showing People in Team</small></label>
            <select class="form-control" name="executives" id="executives">
                <option value="">Select Consultant</option>
                <?php
                if (!empty($filtered_data['executive_of_manager'])) {
                    foreach ($filtered_data['executive_of_manager'] as $exe) {
                        ?>
                        <option value="<?= $exe['id'] ?>" <?php if ($filtered_data['executives'] == $exe['id']) echo $select; ?>>
                            <?= $exe['first_name'] . " " . $exe['last_name'] ?>
                        </option>
                        <?php
                    }
                    ?>
                    <option value="<?= $filtered_data['selected_manager'] ?>" <?php if ($filtered_data['executives'] == $filtered_data['selected_manager']) echo $select; ?>>
                        <?php foreach ($sales_managers as $manager) { 
                            if($manager['id']  == $filtered_data['selected_manager']){
                                echo $manager['first_name'] . " " . $manager['last_name'];
                            }
                        } ?>
                    </option>
                    <?php
                }
                ?>
            </select>
        </div>

        <?php // if ($current_user_role == 1 || $current_user_role == 6 || $current_user_role == 7) { ?>
            <div class="col-md-3 form-group">
                <label class="control-label"><small>&nbsp;</small></label>
                <select class="form-control" name="products" id="products">
                    <option value="">Select Product</option>
                    <?php
                    if (!empty($all_courses)) {
                        foreach ($all_courses as $course) {
                            ?>
                            <option value="<?= $course->id ?>" <?php if ($filtered_data['selected_course'] == $course->id) echo $select ?>>
                                <?= $course->name; ?>
                            </option>
                            <?php
                        }
                    }
                    ?>
                </select>
            </div>
        <?php // } ?>

        <div class="col-md-3 form-group">
            <label class="control-label"><small>&nbsp;</small></label>
            <select class="form-control" required="" name="select_period" id="select_period">
                <option value="">Select Period</option>
                <option value="monthly" <?php if ($filtered_data['select_period'] == 'monthly') echo $select; ?>>Monthly</option>
                <option value="weekly" <?php if ($filtered_data['select_period'] == 'weekly') echo $select; ?>>Weekly</option>
                <!--<option value="quarterly" <?php if ($filtered_data['select_period'] == 'quarterly') echo $select; ?>>Quarterly</option>-->
                <option value="daily" <?php if ($filtered_data['select_period'] == 'daily') echo $select; ?>>Daily</option>
            </select>
        </div>

        <div class="col-md-3 form-group monthly period_inputs" style="display: <?php
        if ($filtered_data['select_period'] == 'monthly')
            echo 'block';
        else
            echo 'none'
            ?>;">
            <label class="control-label"><small>&nbsp;</small></label>
            <select class="form-control" name="month" id="month">
                <option value="">Select Month</option>
                <?php foreach ($month_arr as $month_key => $month_val) { ?>
                    <option value="<?= $month_key ?>"  <?php if ($filtered_data['month'] == $month_key) echo $select; ?>>
                        <?= $month_val ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="col-md-3 form-group monthly period_inputs" style="display: <?php
        if ($filtered_data['select_period'] == 'monthly')
            echo 'block';
        else
            echo 'none'
            ?>;">
            <label class="control-label"><small>&nbsp;</small></label>
            <select class="form-control" name="year" id="year">
                <option value="">Select Year</option>
                <?php for ($i = $min_year; $i <= date('Y'); $i++) { ?>
                    <option value="<?= $i ?>"  <?php if ($filtered_data['year'] == $i) echo $select; ?>>
                        <?= $i ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="col-md-3 form-group weekly period_inputs" style="display: <?php
        if ($filtered_data['select_period'] == 'weekly')
            echo 'block';
        else
            echo 'none'
            ?>;">
            <label class="control-label"><small>&nbsp;</small></label>
            <input type="text" name="weekly_date_from" id="weekly_date_from" class="form-control" placeholder="From Date" value="<?php if (isset($filtered_data['weekly_date_from'])) echo date("d-m-Y", strtotime($filtered_data['weekly_date_from'])); ?>" autocomplete="off">
        </div>
        <div class="col-md-3 form-group weekly period_inputs" style="display: <?php
        if ($filtered_data['select_period'] == 'weekly')
            echo 'block';
        else
            echo 'none'
            ?>;">
            <label class="control-label"><small>&nbsp;</small></label>
            <input type="text" name="weekly_date_to" id="weekly_date_to" class="form-control" placeholder="To Date" value="<?php if (isset($filtered_data['weekly_date_from'])) echo date("d-m-Y", strtotime($filtered_data['weekly_date_to'])); ?>" autocomplete="off" readonly>
        </div>

        <div class="col-md-3 form-group quarterly period_inputs" style="display: <?php
        if ($filtered_data['select_period'] == 'quarterly')
            echo 'block';
        else
            echo 'none'
            ?>;">
            <label class="control-label"><small>&nbsp;</small></label>
            <select class="form-control" name="quarterly_month[]" id="quarterly_month" multiple="">
                <option value="">Select Month</option>
                <?php foreach ($month_arr as $month_key => $month_val) { ?>
                    <option value="<?= $month_key ?>" <?php if (in_array($month_key, $filtered_data['quarterly_month'])) echo $select; ?>>
                        <?= $month_val ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="col-md-3 form-group quarterly period_inputs" style="display: <?php
        if ($filtered_data['select_period'] == 'quarterly')
            echo 'block';
        else
            echo 'none'
            ?>;">
            <label class="control-label"><small>&nbsp;</small></label>
            <select class="form-control" name="quarterly_year" id="quarterly_year">
                <option value="">Select Year</option>
                <?php for ($i = $min_year; $i <= date('Y'); $i++) { ?>
                    <option value="<?= $i ?>" <?php if ($filtered_data['quarterly_year'] == $i) echo $select; ?>>
                        <?= $i ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="col-md-3 form-group daily period_inputs" style="display: <?php
        if ($filtered_data['select_period'] == 'daily')
            echo 'block';
        else
            echo 'none'
            ?>;">
            <label class="control-label"><small>&nbsp;</small></label>
            <input type="text" name="date_for_daily" id="date_for_daily" class="form-control" value="<?php if (isset($filtered_data['date_for_daily'])) echo $filtered_data['date_for_daily']; ?>">
        </div>

        <div class="col-md-3 form-group">
            <label class="control-label"><small>&nbsp;</small></label>
            <select class="form-control" name="domain" id="domain">
                <option value="">Select Domain</option>
                <option value="dm" <?php if ($filtered_data['selected_domain'] == 'dm') echo $select; ?>>Digital Marketing</option>
                <option value="da" <?php if ($filtered_data['selected_domain'] == 'da') echo $select; ?>>Digital Analytics</option>
            </select>
        </div>

        <div class="col-md-12 form-group">
            <input type="submit" value="Filter" class="btn btn-success">
            &nbsp;
            <a href="" class="btn btn-info">Reset</a>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <div class="row">
        <div class="col-sm-12 table-responsive">
            <?php
            $has_data = 0;
            $sales_normalized = DvSales::find()->where(["status" => 1])->all();

            /*echo "<pre>";
            print_r($sales_normalized);
            die;*/

            $norm_CPDM = '';
            $norm_CDMM = '';
            $norm_SEO = '';
            $norm_WA = '';
            $norm_SEM = '';
            $norm_SMM = '';
            $norm_IM = '';
            $norm_EM = '';
            $norm_DAP = '';
            $norm_DAPS = '';
            $norm_DAE = '';
            $norm_BDA = '';

            /*echo "<pre>";
            print_r($sales_normalized);
            die;*/
            foreach($sales_normalized as $normalized_val){
                    
                    if($normalized_val->name == "CDMM"){
                        $norm_CDMM =  $normalized_val->normalize_rate;
                    }

                    if($normalized_val->name == "CPDM"){
                        $norm_CPDM =  $normalized_val->normalize_rate;
                    }

                    if($normalized_val->name == "SEO"){
                        $norm_SEO =  $normalized_val->normalize_rate;
                    }

                    if($normalized_val->name == "WA"){
                        $norm_WA =  $normalized_val->normalize_rate;
                    }

                    if($normalized_val->name == "SEM"){
                        $norm_SEM =  $normalized_val->normalize_rate;
                    }

                    if($normalized_val->name == "SMM"){
                        $norm_SMM = $normalized_val->normalize_rate;
                    }

                    if($normalized_val->name == "IM"){
                        $norm_IM =  $normalized_val->normalize_rate;
                    }

                    if($normalized_val->name == "EM"){
                        $norm_EM =  $normalized_val->normalize_rate;
                    }

                    if($normalized_val->name == "DAP"){
                        $norm_DAP = $normalized_val->normalize_rate;
                    }

                    if($normalized_val->name == "DAPS"){
                        $norm_DAPS =  $normalized_val->normalize_rate;
                    }

                    if($normalized_val->name == "DAE"){
                        $norm_DAE =  $normalized_val->normalize_rate;
                    }

                    if($normalized_val->name == "BDA") {
                        $norm_BDA =  $normalized_val->normalize_rate;
                    }
                }
            
            if (!empty($all_course_details)) {
                ?>
                <table id="sales_report_tbl" class="table" border="1">
                    <thead>
                        <tr>
                            <th scope="col" class="bg-gray">Team</th>
                            <th scope="col" class="bg-gray">Product</th>
                            <th scope="col" class="bg-gray">Domain</th>
                            <th scope="col" class="bg-gray">Team</th>
                            <th scope="col" class="bg-gray">Normalised Sales <br>(Current Period)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $current_period = 0;
                        if (!empty($all_course_details)) {
                            if (!empty($all_course_details['course_details'])) {
                                foreach ($all_course_details['course_details'] as $course_details) {
                                    $has_data = 1;
                                    ?>
                                    <tr>
                                        <td><?= $executive['manager_name'] ?></td>
                                        <td><?= $course_details['name'] ?></td>
                                        <td><?php 
                                            $course_master = $course_details['mcourse'];
                                            if($course_master == "da"){
                                                echo "Digital Analytics";
                                            }
                                            if($course_master == "dm"){
                                                echo "Digital Marketing";
                                            }
                                         ?></td>
                                         <td></td>
                                        <td class="total_current_period">
                                            <?php
                                            $sold_course = $course_details['sold_course'];
                                              
                                            if ($course_details['name'] == "CPDM") {
                                                echo $current_period = $sold_course * $norm_CPDM;
                                            } else if ($course_details['name'] == "CDMM") {
                                                echo $current_period = $sold_course * $norm_CDMM;
                                            } else if ($course_details['name'] == "SEO") {
                                                echo $current_period = $sold_course * $norm_SEO;
                                            } 
                                            else if ($course_details['name'] == "WA") {
                                                echo $current_period = $sold_course * $norm_WA;
                                            } 
                                            else if ($course_details['name'] == "SEM") {
                                                echo $current_period = $sold_course * $norm_SEM;
                                            } 
                                            else if ($course_details['name'] == "SMM") {
                                                echo $current_period = $sold_course * $norm_SMM;
                                            } 
                                            else if ($course_details['name'] == "IM") {
                                                echo $current_period = $sold_course * $norm_IM;
                                            } 
                                            else if ($course_details['name'] == "EM") {
                                                echo $current_period = $sold_course * $norm_EM;
                                            } 
                                            else if ($course_details['name'] == "DAP") {
                                                echo $current_period = $sold_course * $norm_DAP;
                                            }
                                            else if ($course_details['name'] == "DAPS") {
                                                echo $current_period = $sold_course * $norm_DAPS;
                                            }
                                            else if ($course_details['name'] == "DAE") {
                                                echo $current_period = $sold_course * $norm_DAE;
                                            }
                                            else if ($course_details['name'] == "BDE") {
                                                echo $current_period = $sold_course * $norm_BDE;
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                        }
                        if ($has_data == 0) {
                            ?>
                            <tr>
                                <td class="text-center" colspan="7">No Data Found!</td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
                <?php
            } else if (!empty($all_executive)) {
                ?>
                <table id="sales_report_tbl" class="table" border="1">
                    <thead>
                        <tr>
                            <?php if (empty($filtered_data['executives'])) { ?>
                                <th scope="col" class="bg-gray">Team</th>
                                <th scope="col" class="bg-gray">Person</th>
                            <?php } ?>
                            <th scope="col" class="bg-gray">Product</th>
                            <th scope="col" class="bg-gray">Domain</th>
                            <th scope="col" class="bg-gray">Sales <br>(Current Period)</th>
                            <th scope="col" class="bg-gray">Normalised Sales <br>(Current Period) <i class="fa fa-sort"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $current_period = 0;
                        $has_data = 0;
                       
                        $total_sold_course = 0;
                        $total_sales_for_da = 0;
                        $total_sales_for_dm = 0;
                        $total_sales_for_normlized_da = 0;
                        $total_sales_for_normlized_dm = 0;

                        if (!empty($all_executive)) {
                            $exl_array = array();
                            $export_count = 1;
                            foreach ($all_executive as $executive) {
                                ?>
                                <?php
                                if (!empty($executive['course_details'])) {
                                    foreach ($executive['course_details'] as $course_details) {
                                        $has_data = 1;
                                        ?>
                                        <tr>
                                            <?php if (empty($filtered_data['executives'])) { ?>
                                                <td><?= $executive['manager_name'] ?></td>
                                                <td><?= $executive['name'] ?></td>
                                            <?php } ?>
                                            <td><?= $course_details['name'] ?></td>
                                            <td>
                                                <?php    
                                                $course_master = $course_details['mcourse'];
                                                if($course_master == "da"){
                                                    echo "Digital Analytics";
                                                }
                                                if($course_master == "dm"){
                                                    echo "Digital Marketing";
                                                }
                                            ?>  
                                            </td>
                                            <td><?= $course_details['sold_course'] ?></td>
                                            <td class="total_current_period">
                                                <?php
                                                $sold_course = $course_details['sold_course'];

                                                if ($course_details['name'] == "CPDM") {
                                                echo $current_period = $sold_course * $norm_CPDM;
                                                } else if ($course_details['name'] == "CDMM") {
                                                    echo $current_period = $sold_course * $norm_CDMM;
                                                } else if ($course_details['name'] == "SEO") {
                                                    echo $current_period = $sold_course * $norm_SEO;
                                                } 
                                                else if ($course_details['name'] == "WA") {
                                                    echo $current_period = $sold_course * $norm_WA;
                                                } 
                                                else if ($course_details['name'] == "SEM") {
                                                    echo $current_period = $sold_course * $norm_SEM;
                                                } 
                                                else if ($course_details['name'] == "SMM") {
                                                    echo $current_period = $sold_course * $norm_SMM;
                                                } 
                                                else if ($course_details['name'] == "IM") {
                                                    echo $current_period = $sold_course * $norm_IM;
                                                } 
                                                else if ($course_details['name'] == "EM") {
                                                    echo $current_period = $sold_course * $norm_EM;
                                                } 
                                                else if ($course_details['name'] == "DAP") {
                                                    echo $current_period = $sold_course * $norm_DAP;
                                                }
                                                else if ($course_details['name'] == "DAPS") {
                                                    echo $current_period = $sold_course * $norm_DAPS;
                                                }
                                                else if ($course_details['name'] == "DAE") {
                                                    echo $current_period = $sold_course * $norm_DAE;
                                                }else if($course_details['name'] == "BDA") {
                                                    echo $current_period = $sold_course * $norm_BDA;
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php

                                        $total_sold_course += $course_details['sold_course'];
                                        if($course_details['mcourse'] == "da"){
                                            $total_sales_for_da += $sold_course;
                                            $total_sales_for_normlized_da += $current_period;
                                        }
                                        if($course_details['mcourse'] == "dm"){
                                            $total_sales_for_dm += $sold_course;
                                            $total_sales_for_normlized_dm += $current_period;
                                        }

                                        $exl_array[] = array( 
                                            'id'=>$export_count,
                                            'Team'=>$executive['manager_name'],
                                            'Person' => $executive['name'],
                                            'Product' => $course_details['name'],
                                            'Domain' => $course_details['mcourse'],
                                            'Sales (Current Period)' => $course_details['sold_course'],
                                            'Normalised Sales (Current Period)' => $current_period,
                                        );
                                        $export_count = $export_count+1;

                                    }
                                    
                                }
                            }
                        }
                        if ($has_data == 0) {
                            ?>
                            <Tr>
                                <td class="text-center" colspan="7">No Data Found!</td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>

                <div class="import_export">
                    <span class="title">Export Sales Report </span>
                    <?php
                        $excel_array = array('allModels' => $exl_array );
                        $dataProvider = new ArrayDataProvider($excel_array);
                        //  creation of array for export as excel 
                        $columns = array('id', 'Team', 'Person','Product','Domain','Sales (Current Period)','Normalised Sales (Current Period)');
                      

                        $file_name = 'Sales_Report_('.date('Y-m-d').')';
                        echo ExportMenu::widget([
                            'dataProvider' => $dataProvider,
                            'fontAwesome' => true,
                            'columns' => $columns,
                            // 'options' => ['id'=>'expMenu1'], //  optional to set but must be unique 
                            'target' => ExportMenu::TARGET_BLANK,
                            'filename' => $file_name
                        ]);
                    ?>
                </div>
                <div class="total_sales_container" style="width: 70%">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Total Sales Info.</th>
                        <th>Total Sales Values</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>Total Sales :</td>
                        <td><?= $total_sold_course ?></td>
                      </tr>
                      <tr>
                        <td>Total Sales for DA :</td>
                        <td><?= $total_sales_for_da ?></td>
                      </tr>
                      <tr>
                        <td>Total Sales for DM :</td>
                        <td><?= $total_sales_for_dm ?></td>
                      </tr>

                      <tr>
                        <td>Total Sales for Normlized DA : </td>
                        <td><?= $total_sales_for_normlized_da ?></td>
                      </tr>
                      <tr>
                        <td>Total Sales for Normlized DM : </td>
                        <td><?= $total_sales_for_normlized_dm ?></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
               
            <?php }
            ?>
        </div>
    </div>
</div>

<?php
    /*
    * @CDO - 10 March 2019
    * Added custom js(for select weekly data) & Datatable js,css (for sorting)
    */
    $this->registerCssFile("https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css");
    $this->registerJsFile("https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js",[
        'depends' => [
            \yii\web\JqueryAsset::className()
        ]
    ]);
    $this->registerJs(
        '$("document").ready(function(){
            $("#sales_report_tbl").DataTable({
               "columns": [
                    { "orderable": false },
                    { "orderable": false },
                    { "orderable": false },
                    { "orderable": false },
                    { "orderable": false },
                    null
                ],
                "aaSorting": [],
                "paging":   false,
                "searching": false,
                "info" : false
            });

            var startDate;
            var endDate;

            var selectCurrentWeek = function() {
                window.setTimeout(function () {
                    $("#weekly_date_from").find(".ui-datepicker-current-day a").addClass("ui-state-active")
                }, 1);
            }

            $("#weekly_date_from").datepicker({
                dateFormat: "dd-mm-yy",
                showOtherMonths: true,
                selectOtherMonths: true,
                onSelect: function(dateText, inst) { 
                    var date = $(this).datepicker("getDate");
                    startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - date.getDay());
                    endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - date.getDay() + 6);
                    var dateFormat = inst.settings.dateFormat || $.datepicker._defaults.dateFormat;
                    $("#weekly_date_from").val($.datepicker.formatDate( dateFormat, startDate, inst.settings ));
                    $("#weekly_date_to").val($.datepicker.formatDate( dateFormat, endDate, inst.settings ));                    
                    selectCurrentWeek();
                },
                beforeShowDay: function(date) {
                    var cssClass = "";
                    if(date >= startDate && date <= endDate)
                        cssClass = "ui-datepicker-current-day";
                    return [true, cssClass];
                },
                onChangeMonthYear: function(year, month, inst) {
                    selectCurrentWeek();
                }
            });

            jQuery("#weekly_date_from .ui-datepicker-calendar tr").hover(function () {
                jQuery("td a", this).addClass("ui-state-hover");
              },
              function () {
                jQuery("td a", this).removeClass("ui-state-hover");
              }
            );

        });'
    );
  ?>

