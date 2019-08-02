<?php

namespace app\models;

use Yii;

class DvManageMonthlyIncentiveRule extends \yii\db\ActiveRecord{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'assist_manage_monthly_incentive_rule';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            [['rule_id'],'required','message' => 'This field is required.'],
            [['min_revenue','max_revenue','rate'],'double'],
        ];
    }
}
