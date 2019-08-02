<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "assist_user_role".
 *
 * @property integer $id
 * @property string $target
 * @property string $created
 */
class DvCourseTarget extends \yii\db\ActiveRecord{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'assist_course_target';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            [['manager_id'], 'required'],
            [['month'], 'required'],
            [['dm_target'], 'integer'],
            [['da_target'], 'integer'],
            [['incentive'], 'required'],
            [['year'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'da_target' => 'DA Target',
            'dm_target' => 'DM Target',
            'month' => 'Month',
            'year' => 'Year',
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
