<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%password_histories}}`.
 */
class m260908_163749_create_password_histories_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%password_histories}}', [
            'id'          => $this->primaryKey(),
            'user_id'     => $this->integer()->notNull(),
            'ip_address'  => $this->string(45)->null(),
            'user_agent'  => $this->text()->null(),
            'location'    => $this->string(100)->defaultValue('Indonesia (Auto Detected)'),
            'created_at'  => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addForeignKey('fk_pwd_history_user', '{{%password_histories}}', 'user_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('idx_pwd_history_user_id', '{{%password_histories}}', 'user_id');
    }

    public function safeDown()
    {
        $this->dropTable('{{%password_histories}}');
    }
}
