<?php

namespace app\models;

use Yii;

class DvCurrency extends \yii\db\ActiveRecord{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'assist_currency';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            [['name'], 'required'],
            [['description','conversion_to_INR'], 'safe'],                        
            [['status'], 'boolean']
        ];
    }

    /**
     * @inheritdoc
     */
   /* public function attributeLabels(){
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Currency Info',
            'status' => 'Status',
            'updated_at' => 'Updated Date',
            'created_at' => 'Created Date',
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
    */
}
