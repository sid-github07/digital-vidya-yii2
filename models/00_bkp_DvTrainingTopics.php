<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dv_user_team".
 *
 * @property integer $id
 * @property string $name
 * @property string $created
 */
class DvTrainingTopics extends \yii\db\ActiveRecord{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'assist_training_topics';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            [['name'], 'required'],
            [['name'],'filter', 'filter' => 'trim'],
            [['status'], 'integer'],
            [['cid'], 'integer'],
            [['name'], 'string', 'max' => 255],
            
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(){
        return [
            'id' => 'ID',
            'name' => 'Name',
            
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
