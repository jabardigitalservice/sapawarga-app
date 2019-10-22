<?php

use app\components\CustomMigration;

/**
 * Class m191022_101330_add_broadcasts_foreign_key_author_id */
class m191022_101330_add_broadcasts_foreign_key_author_id extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'fk-broadcasts-author_id',
            'broadcasts',
            'author_id',
            'user',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-broadcasts-author_id', 'broadcasts');
    }
}
