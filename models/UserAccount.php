<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class UserAccount extends ActiveRecord implements IdentityInterface
{
    const ROLE_GENERIC = 'generic';
    const ROLE_PURCHASER = 'purchaser';
    const ROLE_FINANCER = 'financer';
    const ROLE_ADMIN = 'admin';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    public static function tableName()
    {
        return 'user_account';
    }

    public function rules()
    {
        return [
            [['staff_id', 'username', 'password_hash'], 'required'],
            [['staff_id'], 'integer'],
            [['username'], 'string', 'max' => 50],
            [['username'], 'unique'],
            [['password_hash'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['role'], 'in', 'range' => [self::ROLE_GENERIC, self::ROLE_PURCHASER, self::ROLE_FINANCER, self::ROLE_ADMIN]],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
            [['must_change_password'], 'boolean'],
            [['last_login_at'], 'safe'],
        ];
    }

    public static function roleLabels()
    {
        return [
            self::ROLE_GENERIC => 'Generic',
            self::ROLE_PURCHASER => 'Purchaser',
            self::ROLE_FINANCER => 'Financer',
            self::ROLE_ADMIN => 'Admin',
        ];
    }

    public function getStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'staff_id']);
    }

    // ---- Role helpers, used across the access-control retrofit ----

    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isPurchaser()
    {
        return $this->role === self::ROLE_PURCHASER;
    }

    public function isFinancer()
    {
        return $this->role === self::ROLE_FINANCER;
    }

    /**
     * Purchaser, Financer, or Admin - anyone allowed into master data,
     * procurement, and asset management.
     */
    public function canAccessOperations()
    {
        return in_array($this->role, [self::ROLE_PURCHASER, self::ROLE_FINANCER, self::ROLE_ADMIN]);
    }

    /**
     * Financer or Admin - anyone allowed to review/approve other people's
     * claims.
     */
    public function canApproveClaims()
    {
        return in_array($this->role, [self::ROLE_FINANCER, self::ROLE_ADMIN]);
    }

    // ---- Password handling ----

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function beforeSave($insert)
    {
        if ($insert && empty($this->auth_key)) {
            $this->generateAuthKey();
        }
        return parent::beforeSave($insert);
    }

    // ---- IdentityInterface ----

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new \yii\base\NotSupportedException('Token-based auth is not supported for this app - use username/password login.');
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }
}