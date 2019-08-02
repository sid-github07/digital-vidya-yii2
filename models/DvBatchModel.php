<?php
namespace app\models;
use Yii;
use app\models\DvBatchSessionModel;
class DvBatchModel extends \yii\db\ActiveRecord{
    
    public static function tableName(){
        return 'assist_batch_master';
    }

    public function rules(){
        return [
            [['module_id','trainer','trainer_coordinator','open_seats','start_date','number_of_sessions','batch_day','time_duration','joining_link'],'required'],
            [['status'], 'boolean'],
            [['module_id','trainer','open_seats','number_of_sessions'],'integer']
        ];
    }

    public function beforeSave($insert){
        if (!parent::beforeSave($insert)){
            return false;
        }
        return true;
    }
 
}// --- End of class:DvBatchModel --- //
