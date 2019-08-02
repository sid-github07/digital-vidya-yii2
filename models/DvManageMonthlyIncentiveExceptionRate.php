<?php

namespace app\models;

use Yii;

class DvManageMonthlyIncentiveExceptionRate extends \yii\db\ActiveRecord{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'assist_manage_monthly_incentive_exception_rate';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            [['executive_id'],'required','message' => 'This field is required.'],
            [['years'],'required'],
            [['month'],'required'],
            [['domain'],'required'],
            [['min_closures','max_closures','rate'],'double']
        ];
    }
}
