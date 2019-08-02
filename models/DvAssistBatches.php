<?php

namespace app\models;

use Yii;

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
 * @property string $created_date
 */
class DvAssistBatches extends \yii\db\ActiveRecord{
    /**
     * @inheritdoc
     */
    //added on 29 april 2019
    const Completed = 1;
    const Ongoing = 2;
    const Upcoming = 3;

    public static function tableName()
    {
        return 'assist_batches';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            ['trainer', 'filter', 'filter' => 'intval', 'skipOnEmpty' => true],
            [['created_by'], 'integer'],
            [['stiming','etiming'], 'required'],
            [['start_date','end_date'], 'required'],
            [['module'], 'required'],
            [['coordinator'], 'required'],
            //[['format'], 'required'],
            [['created_by'], 'required'],
            [['duration'], 'required'],
            [['type'], 'required'],
            [['seats'], 'required'],
            [['day'], 'required'],
            [['joining_link'], 'required'],
            // ['trainer_topic','filter', 'filter' => 'intval', 'skipOnEmpty' => true],
        ];
    }    

    /**
     * @inheritdoc
     */
    public function attributeLabels(){
        return [
            'id' => 'ID',
            'trainer' => 'Trainer',
            'stiming' => 'Start Timing',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'module' => 'Module',          
            'coordinator' => 'Co-ordinator Person',
            // 'format' => 'Delivery Format',
            'duration' => 'Time Duration',
            'type' => 'Number of Weeks (Type)',
            'seats' => 'Number of Seat(s)',
            'day' => 'Day',
            'created_on' => 'Created Date',
        ];
    }

    public function beforeSave($insert){
        if (parent::beforeSave($insert)){
            return true;
        } else {
            return false;
        }
    }
}
