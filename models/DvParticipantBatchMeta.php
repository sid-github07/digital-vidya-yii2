<?php
namespace app\models;
use Yii; 

class DvParticipantBatchMeta extends \yii\db\ActiveRecord{
    
    public static function tableName(){
        return 'assist_participant_batch_meta';
    }

    public function rules(){
        return [
            [['pid','batch_id'],'required'],
            [['pid','batch_id'],'integer']
        ];
    }
}
