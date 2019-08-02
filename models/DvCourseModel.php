<?php
namespace app\models;
use Yii;
use yii\helpers\ArrayHelper;
class DvCourseModel extends \yii\db\ActiveRecord{
    
    public static function tableName(){
        return 'assist_course';
    }
 
    public function rules(){
        return [
            [['name'],'trim'],
            [['name','course_code','mcourse','version','course_speed','core_modules',],'required'],
            [['name'],'unique'],
            [['foundation_module','special_module','type'],'default'],
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
 
}// --- End of class:DvCourseModel --- //
