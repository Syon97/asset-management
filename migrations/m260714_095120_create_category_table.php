<?php
use yii\db\Migration;

class m260714_095120_create_category_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('category', [
            'id' => $this->primaryKey(),
            'category_name' => $this->string(200)->notNull()->unique(),
            'description' => $this->text()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('category');
    }
}