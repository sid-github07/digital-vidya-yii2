<?php
namespace app\models;
use Yii;
class DvBatchSessionModel extends \yii\db\ActiveRecord{
    
    public static function tableName(){
        return 'assist_sessions_master';
    }

    public function rules(){
        return [
            [['session_date'],'required'],
        ];
        //[['batch_master_id','session_date','session_time','session_duration'],'required'],
    }

    public function beforeSave($insert){
        if (parent::beforeSave($insert)) {
            if(!empty($this->isNewRecord)){ }
            return true;
        } else {
            return false;
        }
    }
 
}// --- End of class:DvBatchSessionModel --- //
