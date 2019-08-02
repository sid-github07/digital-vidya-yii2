<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dv_crouse_new".
 *
 * @property integer $id
 * @property string $name
 * @property integer $course_category
 */
class DvCourseNew extends \yii\db\ActiveRecord{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'dv_crouse_new';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            [['course_name','number_of_weeks','course_category','course_type','module','status'], 'required'],
            [['course_name','course_category','course_type','status'],'filter', 'filter' => 'trim'],            
            [['number_of_weeks','status'], 'integer'],
            [['course_name','course_category','course_type'], 'string'],
            ['course_name','unique']
        ];
    }

    /**
     * @inheritdoc
     */
   /* public function attributeLabels(){
        return [
            'id' => 'ID',
            'name' => 'Name',
            'name' => 'Type',
            'status' => 'Status',
            'created' => 'Created Date',
        ];
    }*/

   /* public function beforeSave($insert){
        if (parent::beforeSave($insert)) {
            //send email to user before save

            if(!empty($this->isNewRecord)){
                
            }

            return true;
        } else {
            return false;
        }
    }*/
}
