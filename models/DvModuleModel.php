<?php
namespace app\models;
use Yii; 
use yii\helpers\ArrayHelper;
class DvModuleModel extends \yii\db\ActiveRecord{
    
    public static function tableName(){
        return 'assist_module';
    }

    public function rules(){
        return [
            [['module_name'],'trim'],
            [['module_name','module_type','number_of_weeks','category_type','lms_course','mcourse'],'required'],
            [['module_name'],'unique'],
            [['module_type','number_of_weeks','category_type'],'default'],
            [['module_type','category_type'],'string'],
            [['prerequisite_module'],'safe'],
            [['number_of_weeks'],'integer'],
        ];
    }

    public function getCategoryDropdown(){
            $listCategory   = DvModuleModel::find()->select('id,module_name')
                ->where(['status' => '1'])
                ->all();
            $list   = ArrayHelper::map( $listCategory,'id','module_name');
            return $list;
    }

    public function beforeSave($insert){
        if (!parent::beforeSave($insert)){
            return false;
        }
        return true;
    }
 
}// --- End of class:DvRegistrationNewModel --- //
