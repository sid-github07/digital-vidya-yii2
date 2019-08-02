<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "assist_users".
 *
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property string $username
 * @property string $password
 * @property integer $status
 * @property string $role
 * @property string $created_date
 */
class DvParticipantPayments extends \yii\db\ActiveRecord{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'assist_participant_payments';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
       return [
            [['participant_id','payment_mode','payment_currency','payment_number','amount_confirmed','amount_confirmed_by'],'integer'],
            [[ 'amount_confirmed_date'],'safe'],
            [['payment_reference_number','amount_recieved_date','payment_currency','amount_recieved','payment_mode'],'required'],
            [['payment_reference_number'],'string','max' => 17],
            [['amount_recieved'],'double']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
           'ref_forfpay' =>'Reference Number (Full Payment or First Payment)'
        ];
    }
}
