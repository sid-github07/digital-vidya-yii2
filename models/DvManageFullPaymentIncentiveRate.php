<?php

namespace app\models;

use Yii;

class DvManageFullPaymentIncentiveRate extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'assist_fully_payment_incentive_rate';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['year'], 'required'],
            [['month'], 'required'],
            [['min_closures'], 'required'],
            [['max_closures'], 'required'],
            [['rate'], 'required'],
        ];
    }

}
