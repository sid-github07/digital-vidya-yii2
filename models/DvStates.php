<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "assist_user_team".
 *
 * @property integer $id
 * @property string $name
 * @property string $country_id
 */
class DvStates extends \yii\db\ActiveRecord{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'assist_states';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            [['name'], 'required'] 
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(){
        return [
            'id' => 'ID',
            'name' => 'Name',
            'country_id' => 'Country ID',
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
