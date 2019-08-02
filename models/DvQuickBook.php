<?php

namespace app\models;

use Yii;

class DvQuickBook extends \yii\db\ActiveRecord {
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'assist_qb_setting';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
       return [
         [['qb_key','qb_value'], 'required']
        ];
    }
}
