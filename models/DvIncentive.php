<?php

namespace app\models;

use Yii;

class DvIncentive extends \yii\db\ActiveRecord{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'assist_incentive';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            [['inc_per'],'required','message' => 'This field is required.'],
            [['inc_per'],'integer','message' => 'Please enter digits only.'],
            [['description'], 'safe'],                        
            [['status'], 'boolean']
        ];
    }
}
