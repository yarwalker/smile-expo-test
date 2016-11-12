<?php

use yii\db\Migration;

/**
 * Handles the creation of table `tree`.
 */
class m161101_190436_create_tree_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('tree', [
            'id' => $this->primaryKey(),
            'name' => $this->string(5),
            'level' => $this->integer(10),
            'left' => $this->integer(10),
            'right' => $this->integer(10),
        ]);

        $this->createIndex('ixLeft', 'tree', 'left');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('tree');
    }
}
