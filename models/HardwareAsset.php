<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

class HardwareAsset extends ActiveRecord
{
    const STATUS_ACTIVE = 'active';
    const STATUS_DISPOSED = 'disposed';

    const HOLDER_STAFF = 'staff';
    const HOLDER_PROJECT = 'project';
    const HOLDER_DEPARTMENT = 'department';

    /**
     * @var UploadedFile|null uploaded product photo (not persisted directly)
     */
    public $productImageFile;

    /**
     * @var UploadedFile|null uploaded serial number photo
     */
    public $serialImageFile;

    /**
     * @var UploadedFile|null uploaded serial-command screen photo
     */
    public $serialCommandImageFile;

    public static function tableName()
    {
        return 'hardware_asset';
    }

    public function rules()
    {
        return [
            [['brand', 'model'], 'required'],
            [['category_id', 'department_id', 'supplier_id', 'current_holder_id'], 'integer'],
            [['brand', 'model', 'serial_no'], 'string', 'max' => 191],
            [['purchase_price'], 'number'],
            [['purchase_date', 'warranty_start_date', 'warranty_end_date', 'disposed_at'], 'safe'],
            [['remarks', 'disposal_remarks'], 'string'],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DISPOSED]],
            [['current_holder_type'], 'in', 'range' => [self::HOLDER_STAFF, self::HOLDER_PROJECT, self::HOLDER_DEPARTMENT]],
            [['asset_tag'], 'string', 'max' => 30],
            [['productImageFile'], 'file', 'extensions' => 'jpg, jpeg, png', 'skipOnEmpty' => true],
            [['serialImageFile'], 'file', 'extensions' => 'jpg, jpeg, png', 'skipOnEmpty' => true],
            [['serialCommandImageFile'], 'file', 'extensions' => 'jpg, jpeg, png', 'skipOnEmpty' => true],
        ];
    }

    public function attributeLabels()
    {
        return [
            'asset_tag' => 'Asset Tag',
            'category_id' => 'Category',
            'department_id' => 'Location',
            'supplier_id' => 'Supplier',
            'serial_no' => 'Serial No.',
            'purchase_price' => 'Purchase Price',
            'purchase_date' => 'Purchase Date',
            'warranty_start_date' => 'Warranty Start',
            'warranty_end_date' => 'Warranty End',
            'productImageFile' => 'Product Photo',
            'serialImageFile' => 'Serial No. Photo',
            'serialCommandImageFile' => 'Serial Command Screen Photo',
        ];
    }

    public static function statusLabels()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_DISPOSED => 'Disposed',
        ];
    }

    public static function holderTypeLabels()
    {
        return [
            self::HOLDER_STAFF => 'Staff',
            self::HOLDER_PROJECT => 'Project',
            self::HOLDER_DEPARTMENT => 'Department',
        ];
    }

    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public function getDepartment()
    {
        return $this->hasOne(Department::class, ['id' => 'department_id']);
    }

    public function getSupplier()
    {
        return $this->hasOne(Supplier::class, ['id' => 'supplier_id']);
    }

    public function getAccessories()
    {
        return $this->hasMany(HardwareAccessory::class, ['hardware_asset_id' => 'id']);
    }

    public function getSoftwareLicenses()
    {
        return $this->hasMany(SoftwareLicense::class, ['hardware_asset_id' => 'id']);
    }

    public function getAssignments()
    {
        return $this->hasMany(AssetAssignment::class, ['hardware_asset_id' => 'id'])->orderBy(['assigned_at' => SORT_DESC]);
    }

    public function hasActiveHolder()
    {
        return !empty($this->current_holder_type) && !empty($this->current_holder_id);
    }

    /**
     * Resolves the current holder's display name regardless of holder_type.
     */
    public function getHolderLabel()
    {
        if (empty($this->current_holder_type) || empty($this->current_holder_id)) {
            return null;
        }
        switch ($this->current_holder_type) {
            case self::HOLDER_STAFF:
                $staff = Staff::findOne($this->current_holder_id);
                return $staff->staff_name ?? "Staff #{$this->current_holder_id}";
            case self::HOLDER_PROJECT:
                $project = Project::findOne($this->current_holder_id);
                return $project->project_name ?? "Project #{$this->current_holder_id}";
            case self::HOLDER_DEPARTMENT:
                $department = Department::findOne($this->current_holder_id);
                return $department->name ?? "Department #{$this->current_holder_id}";
            default:
                return null;
        }
    }

    /**
     * @return string one of: 'none', 'active', 'expired'
     */
    public function getWarrantyStatus()
    {
        if (empty($this->warranty_end_date)) {
            return 'none';
        }
        return strtotime($this->warranty_end_date) >= strtotime('today') ? 'active' : 'expired';
    }

    /**
     * Category-coded style matching the legacy stickers (e.g. "FF-0001/26"),
     * when the asset's category has a tag_prefix set. Sequence resets each
     * year per prefix - same convention as PR/PO/GRN numbering elsewhere in
     * this system, and matches how the legacy sequence itself behaved
     * (a later year restarting from a low number rather than counting up
     * forever).  Falls back to the old generic HW-YYYY-NNNN scheme when no
     * category is set, or the category has no prefix defined.
     */
    public static function generateAssetTag($categoryId = null)
    {
        $prefix = null;
        if ($categoryId) {
            $category = Category::findOne($categoryId);
            if ($category && !empty($category->tag_prefix)) {
                $prefix = $category->tag_prefix;
            }
        }

        if (!$prefix) {
            $year = date('Y');
            $count = self::find()
                ->where(['like', 'asset_tag', "HW-{$year}-"])
                ->count();
            $next = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            return "HW-{$year}-{$next}";
        }

        $yy = date('y');
        $likePattern = $prefix . '-%/' . $yy;
        $existing = self::find()
            ->where('asset_tag LIKE :pattern', [':pattern' => $likePattern])
            ->all();

        $maxSeq = 0;
        foreach ($existing as $asset) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '-(\d+)\//', $asset->asset_tag, $m)) {
                $maxSeq = max($maxSeq, (int) $m[1]);
            }
        }

        $nextSeq = str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
        return "{$prefix}-{$nextSeq}/{$yy}";
    }

    public function beforeSave($insert)
    {
        if ($insert && empty($this->asset_tag)) {
            $this->asset_tag = self::generateAssetTag($this->category_id);
        }
        if ($insert && empty($this->status)) {
            $this->status = self::STATUS_ACTIVE;
        }
        return parent::beforeSave($insert);
    }

    /**
     * Saves any newly uploaded images to web/uploads/hardware and stores
     * the relative filename. Call after a successful save().
     */
    public function saveUploadedImages()
    {
        $uploadDir = Yii::getAlias('@webroot/uploads/hardware');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $map = [
            'productImageFile' => 'product_image',
            'serialImageFile' => 'serial_image',
            'serialCommandImageFile' => 'serialcommand_image',
        ];

        $changed = false;
        foreach ($map as $fileAttr => $column) {
            /** @var UploadedFile|null $file */
            $file = $this->$fileAttr;
            if ($file instanceof UploadedFile) {
                $filename = $this->asset_tag . '_' . $column . '_' . time() . '.' . $file->extension;
                if ($file->saveAs($uploadDir . DIRECTORY_SEPARATOR . $filename)) {
                    $this->$column = $filename;
                    $changed = true;
                }
            }
        }

        if ($changed) {
            $this->save(false);
        }
    }

    public function getImageUrl($column)
    {
        if (empty($this->$column)) {
            return null;
        }
        return Yii::getAlias('@web/uploads/hardware/' . $this->$column);
    }
}