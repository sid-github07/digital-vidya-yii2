<?php

namespace app\models;

use Yii;

use yii\base\Model;
use yii\web\UploadedFile;

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
 * @property string $created
 */
class DvUserMeta extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'assist_user_meta';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            [['uid','meta_key','meta_value'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'User ID'
        ];
    }
 
}
