<?php

namespace app\models;

use Yii;

class DvGst extends \yii\db\ActiveRecord{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'assist_gst';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            [['gst_per'],'required','message' => 'This field is required.'],
            [['gst_date'],'required','message' => 'Please select date for GST.'],
            [['state'],'required','message' => 'Please select state for GST.']
        ];
    }
}
