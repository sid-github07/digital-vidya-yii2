<?php

namespace app\models;

use Yii;

use yii\base\Model;
use yii\web\UploadedFile;

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
 * @property string $created
 */
class DvUsers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'assist_users';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            [['first_name', 'username','email','password'], 'required'],
            [['email'], 'unique'],
            [['status'], 'integer'],
            [['picture','last_name','created','gender','email','department','course','last_logged'], 'safe'],
            [['picture','first_name','last_name','username','gender','email','department','course','last_logged'], 'filter','filter' => 'trim'],
            [['first_name'], 'string', 'max' => 255],
            [['last_name'], 'string', 'max' => 255],
            [['username', 'password'], 'string', 'max' => 155],
            [['picture'], 'file', 'extensions' => 'png, jpg, jpeg','maxSize' => 512000, 'tooBig' => 'Maxmimum image uploading Limit is 500KB'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'picture' => 'User Picture',
            'username' => 'Username',
            'password' => 'Password',
            'status' => 'Status'
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            //send email to user before save

            if(!empty($this->isNewRecord)){
                //encript pass before save
                if(!empty($this->password)) {
                    $this->password = md5($this->password);
                    //$this->password = Yii::$app->getSecurity()->generatePasswordHash($this->password);
                }else{
                    unset($this->password);
                }
            }

            return true;
        } else {
            return false;
        }
    }
}
