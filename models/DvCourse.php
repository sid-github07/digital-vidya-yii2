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
class DvCourse extends \yii\db\ActiveRecord{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'assist_course';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            [['name'], 'required'],
            [['name'],'filter', 'filter' => 'trim'],            
            [['status'], 'integer'],
            [['type'],'filter', 'filter' => 'trim'],
            [['mcourse'],'filter', 'filter' => 'trim'],
            [['name'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 50],
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(){
        return [
            'id' => 'ID',
            'name' => 'Name',
            'name' => 'Type',
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
