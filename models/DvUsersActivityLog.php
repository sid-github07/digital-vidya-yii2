<?php

namespace app\models;

use Yii;

class DvUsersActivityLog extends \yii\db\ActiveRecord{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'assist_users_activity_log';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            [['user_id','participant_id'], 'integer'],
            [['page_url','meta_key','old_value','new_value'], 'required'],
        ];
    }
}
