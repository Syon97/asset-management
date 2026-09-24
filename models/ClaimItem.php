<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class ClaimItem extends ActiveRecord
{
    /**
     * @var \yii\web\UploadedFile|null
     */
    public $receiptFile;

    public static function tableName()
    {
        return 'claim_item';
    }

    public function rules()
    {
        return [
            [['item_date', 'amount'], 'required'],
            [['claim_id'], 'integer'],
            [['item_date'], 'safe'],
            [['particular', 'location_company', 'purpose', 'receipt_file'], 'string', 'max' => 255],
            [['vehicle_type'], 'in', 'range' => [MileageRate::VEHICLE_CAR, MileageRate::VEHICLE_MOTORCYCLE]],
            [['distance_km', 'amount'], 'number'],
            [['has_receipt'], 'boolean'],
            [['remarks'], 'string', 'max' => 255],
            [['receiptFile'], 'file', 'extensions' => 'jpg, jpeg, png, pdf', 'skipOnEmpty' => true],
        ];
    }

    public function attributeLabels()
    {
        return [
            'item_date' => 'Date',
            'particular' => 'Particular',
            'location_company' => 'Location & Company Name',
            'purpose' => 'Purpose / Destination',
            'vehicle_type' => 'Vehicle Type',
            'distance_km' => 'Distance (KM)',
            'amount' => 'Amount (RM)',
            'has_receipt' => 'Receipt Attached',
        ];
    }

    public function getClaim()
    {
        return $this->hasOne(Claim::class, ['id' => 'claim_id']);
    }

    public function isMileageRow()
    {
        return !empty($this->vehicle_type);
    }

    public function saveUploadedReceipt()
    {
        if (!($this->receiptFile instanceof \yii\web\UploadedFile)) {
            return;
        }

        $uploadDir = Yii::getAlias('@webroot/uploads/claim-receipts');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $filename = 'claim' . $this->claim_id . '_item' . $this->id . '_' . time() . '.' . $this->receiptFile->extension;
        if ($this->receiptFile->saveAs($uploadDir . DIRECTORY_SEPARATOR . $filename)) {
            $this->receipt_file = $filename;
            $this->has_receipt = true;
            $this->save(false);
        }
    }

    public function getReceiptUrl()
    {
        if (empty($this->receipt_file)) {
            return null;
        }
        return Yii::getAlias('@web/uploads/claim-receipts/' . $this->receipt_file);
    }

    public static function createMultiple($modelClass, $multipleModels = [])
    {
        $model = new $modelClass;
        $formName = $model->formName();
        $post = Yii::$app->request->post($formName);
        $models = [];

        if (!empty($post) && is_array($post)) {
            foreach ($post as $i => $item) {
                if (isset($multipleModels[$i])) {
                    $models[] = $multipleModels[$i];
                } else {
                    $models[] = new $modelClass;
                }
            }
        } else {
            $models = $multipleModels ?: [$model];
        }

        return $models;
    }
}