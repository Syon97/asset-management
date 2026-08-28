<?php
use yii\db\Migration;

/**
 * Lets each category define its own asset-tag prefix, so new hardware
 * assets can be tagged in the same style as the imported legacy stickers
 * (e.g. "FF-0001/26"), without hardcoding a fixed prefix list in code.
 * Seeded here with the prefixes reverse-engineered from the legacy data
 * itself (near-100% correlation between prefix and category).
 */
class m260826_075839_add_tag_prefix_to_category extends Migration
{
    private $prefixMap = [
        'COMPUTER & SOFTWARE' => 'C',
        'FURNITURE & FITTINGS' => 'FF',
        'FACTORY EQUIPMENT' => 'FE',
        'MACHINERY' => 'MA',
        'OFFICE EQUIPMENT' => 'OE',
        'RENOVATION' => 'R',
        'ELECTRICAL FITTINGS' => 'EF',
        'AIR-CONDITIONER' => 'A',
        'FACTORY PREMISES' => 'FP',
        'MOTOR VEHICLE' => 'MV',
    ];

    public function safeUp()
    {
        if (!$this->db->getTableSchema('category')->getColumn('tag_prefix')) {
            $this->addColumn('category', 'tag_prefix', $this->string(10)->null()->after('category_name'));
        }

        foreach ($this->prefixMap as $name => $prefix) {
            $normalized = str_replace(' ', '', strtolower($name));
            $this->execute(
                "UPDATE category SET tag_prefix = :prefix "
                . "WHERE REPLACE(LOWER(category_name), ' ', '') = :normalized",
                [':prefix' => $prefix, ':normalized' => $normalized]
            );
        }
    }

    public function safeDown()
    {
        $this->dropColumn('category', 'tag_prefix');
    }
}