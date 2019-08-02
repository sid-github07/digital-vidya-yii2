<?php

namespace app\models;

use Yii;

class DvManageMonthlyIncentiveRate extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'assist_manage_monthly_incentive_rate';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['mcourse'], 'required'],
            [['month'], 'required'],
            [['year'], 'required'],
            [['min_closures'], 'required'],
            [['max_closures'], 'required'],
            [['rate'], 'required'],
        ];
    }

}
