<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "assist_user_team".
 *
 * @property integer $id
 * @property string $name
 * @property string $created
 */
class DvUsersTeam extends \yii\db\ActiveRecord{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'assist_user_team';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            [['name'],'filter', 'filter' => 'trim'],
            //[['dep_id'], 'integer'],
            [['dep_id'], 'safe'],
            [['status'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(){
        return [
            'id' => 'ID',
            'name' => 'Name',
            'dep_id' => 'Department',
            'status' => 'Status',
            'created' => 'Created Date',
        ];
    }

    public function beforeSave($insert){
        if (parent::beforeSave($insert)) {
            //send email to user before save

            if(!empty($this->isNewRecord)){
                
            }

            return true;
        } else {
            return false;
        }
    }
}
