<?php

namespace app\models;

use Yii;

class DvPaymentMode extends \yii\db\ActiveRecord{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'assist_payment_mode';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            [['name'], 'required'],
            [['description'], 'safe'],                        
            [['status'], 'boolean']
        ];
    }
}
