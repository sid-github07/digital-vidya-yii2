<?php
namespace app\models;
use Yii; 
use yii\helpers\ArrayHelper;
class DvCoordinatorModel extends \yii\db\ActiveRecord{
    
    public static function tableName(){
        return 'assist_coordinator_data';
    }

    public function rules(){
        return [
            [['coordinator_ids','created_on'],'required'],
            [['created_by','created_on'],'default'],
            [['created_by'],'safe']
        ];
    }

    public function beforeSave($insert){
        if (!parent::beforeSave($insert)){
            return false;
        }
        return true;
    }
 
}// --- End of class:DvCoordinatorModel --- //
