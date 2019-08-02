<?php

namespace app\models;

use Yii;

class DvMonthlyIncentiveRate extends \yii\db\ActiveRecord{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'assist_monthly_incentive_rate';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            [['executive_id'],'required','message' => 'This field is required.'],
            [['manager_id','year','month'],'required','message' => 'This field is required.'],
            [['manager_id','year','month'],'integer','message' => 'Please enter digits only.'],
            [['rate','from_amount','to_amount'],'double'],
        ];
    }
}
