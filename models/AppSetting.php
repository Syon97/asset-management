<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class AppSetting extends ActiveRecord
{
    const KEY_CLAIM_GM_STAFF_ID = 'claim_gm_staff_id';

    public static function tableName()
    {
        return 'app_setting';
    }

    public function rules()
    {
        return [
            [['setting_key'], 'required'],
            [['setting_key'], 'string', 'max' => 100],
            [['setting_value'], 'string', 'max' => 255],
        ];
    }

    public static function get($key, $default = null)
    {
        $row = self::findOne($key);
        return $row ? $row->setting_value : $default;
    }

    public static function set($key, $value)
    {
        $row = self::findOne($key) ?: new self(['setting_key' => $key]);
        $row->setting_value = $value;
        return $row->save(false);
    }
}