<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class MileageRate extends ActiveRecord
{
    const VEHICLE_CAR = 'car';
    const VEHICLE_MOTORCYCLE = 'motorcycle';

    public static function tableName()
    {
        return 'mileage_rate';
    }

    public function rules()
    {
        return [
            [['vehicle_type', 'rate_per_km'], 'required'],
            [['vehicle_type'], 'in', 'range' => [self::VEHICLE_CAR, self::VEHICLE_MOTORCYCLE]],
            [['rate_per_km'], 'number'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'vehicle_type' => 'Vehicle Type',
            'rate_per_km' => 'Rate per KM (RM)',
        ];
    }

    public static function vehicleTypeLabels()
    {
        return [
            self::VEHICLE_CAR => 'Car',
            self::VEHICLE_MOTORCYCLE => 'Motorcycle',
        ];
    }

    public static function currentRateFor($vehicleType)
    {
        $rate = self::findOne(['vehicle_type' => $vehicleType]);
        return $rate ? (float) $rate->rate_per_km : 0.0;
    }
}