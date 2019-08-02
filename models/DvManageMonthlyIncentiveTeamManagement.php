<?php

namespace app\models;

use Yii;

class DvManageMonthlyIncentiveTeamManagement extends \yii\db\ActiveRecord{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'assist_manage_monthly_incentive_team_management';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            [['team_id'],'required','message' => 'This field is required.'],
            [['rule_id'],'required','message' => 'This field is required.'],
        ];
    }
}
