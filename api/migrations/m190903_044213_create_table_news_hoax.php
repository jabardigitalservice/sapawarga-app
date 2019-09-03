<?php

use app\components\CustomMigration;

/**
 * Class m190903_044213_create_table_news_hoax */
class m190903_044213_create_table_news_hoax extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('news_hoax', [
            'id'          => $this->primaryKey(),
            'category_id' => $this->integer()->notNull(),
            'title'       => $this->string()->notNull(),
            'slug'        => $this->string()->null(),
            'cover_path'  => $this->string()->null(),
            'source_url'  => $this->string()->null(),
            'source_date' => $this->date()->null(),
            'content'     => $this->text()->null(),
            'featured'    => $this->boolean()->null(),
            'meta'        => $this->json()->null(),
            'seq'         => $this->integer()->null(),
            'status'      => $this->integer()->null(),
            'created_at'  => $this->integer()->null(),
            'updated_at'  => $this->integer()->null(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('news_hoax');

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190903_044213_create_table_news_hoax cannot be reverted.\n";

        return false;
    }
    */
}
