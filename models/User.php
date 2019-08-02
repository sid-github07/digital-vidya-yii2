<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class User extends ActiveRecord implements \yii\web\IdentityInterface
{

    public static function tableName() { return 'assist_users'; }

    /**
     * @inheritdoc
     */


    public function rules()
    {
        return [
            [['username','password','first_name','email'], 'required'],            
            ['password', 'string', 'min' => 8],
            [['email'], 'email'],
            [['username','email'], 'unique'],            
            [['username','password','firstname','lastname'], 'string', 'max' => 250],
            [['firstname'], 'safe'],
            [['email','email'], 'string', 'max' => 500],

        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'email' => 'Email'
        ];
    }


    public static function findIdentity($id) {
        $user = self::find()
            ->where([
                "id" => $id
            ])
            ->one();
        if (!count($user)) {
            return null;
        }
        return new static($user);
    }
    public function beforeSave($options = array()) {
        $pass = md5($this->password);
        $this->password = $pass;
        return true;
    }
    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $userType = null) {

        $user = self::find()
            ->where(["accessToken" => $token])
            ->one();
        if (!count($user)) {
            return null;
        }
        return new static($user);
    }

    /**
     * Finds user by username
     *
     * @param  string      $username
     * @return static|null
     */
    public static function findByUsername($username) {
        $user = self::find()->where(["username" => $username])->one();            

        if (!count($user)){
                $user_e = self::find()->where(["email" => $username])->one();

                if (!count($user_e)) {
                    return null;
                } else {
                    $user = $user_e;
                }
        }
        return new static($user);
    }

    public static function findByUser($username) {
        $user = self::find()
            ->where([
                "username" => $username
            ])
            ->one();
        if (!count($user)) {
            return null;
        }
        return $user;
    }

    /**
     * @inheritdoc
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey() {
        //  return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey) {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param  string  $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password){        
        return $this->password ===  md5($password);
    }
}