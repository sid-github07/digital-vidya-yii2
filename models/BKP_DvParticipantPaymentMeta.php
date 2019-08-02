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
class DvParticipantPaymentMeta extends \yii\db\ActiveRecord{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'assist_participant_payment_installment';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            [['participant_id','installment_status'],'integer'],
            [['installment_due_date','cheque_referenc_number'],'safe'],
        ];
    }
}
