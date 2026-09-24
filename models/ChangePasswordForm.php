<?php

namespace app\models;

use Yii;
use yii\base\Model;

class ChangePasswordForm extends Model
{
    public $currentPassword;
    public $newPassword;
    public $newPasswordRepeat;

    public function rules()
    {
        return [
            [['currentPassword', 'newPassword', 'newPasswordRepeat'], 'required'],
            [['newPassword'], 'string', 'min' => 6],
            [['currentPassword'], 'validateCurrentPassword'],
            [['newPasswordRepeat'], 'compare', 'compareAttribute' => 'newPassword', 'message' => "New passwords don't match."],
        ];
    }

    public function attributeLabels()
    {
        return [
            'currentPassword' => 'Current Password',
            'newPassword' => 'New Password',
            'newPasswordRepeat' => 'Confirm New Password',
        ];
    }

    public function validateCurrentPassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $identity = Yii::$app->user->identity;
            if (!$identity || !$identity->validatePassword($this->currentPassword)) {
                $this->addError($attribute, 'Current password is incorrect.');
            }
        }
    }

    public function changePassword()
    {
        if (!$this->validate()) {
            return false;
        }

        $identity = Yii::$app->user->identity;
        $identity->setPassword($this->newPassword);
        $identity->must_change_password = false;
        return $identity->save(false, ['password_hash', 'must_change_password']);
    }
}