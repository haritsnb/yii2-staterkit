<?php

use yii\db\Migration;

class m260908_100115_create_user_and_auth_tables extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        // 1. Tabel users
        $this->createTable('{{%users}}', [
            'id'            => $this->primaryKey(),
            'username'      => $this->string(100)->notNull()->unique(),
            'email'         => $this->string(150)->notNull()->unique(),
            'password_hash' => $this->string(255)->notNull(),
            'auth_key'      => $this->string(32)->notNull(),
            'login_mode'    => "ENUM('single_device', 'multi_device') NOT NULL DEFAULT 'single_device'",
            'avatar'        => $this->string(255)->null(),
            'status'        => "ENUM('active', 'inactive', 'banned') NOT NULL DEFAULT 'active'",

            // Mandatory Audit Columns (UTC)
            'registered_at' => $this->dateTime()->notNull(),
            'registered_by' => $this->integer()->null(),
            'modified_at'   => $this->dateTime()->null(),
            'modified_by'   => $this->integer()->null(),
            'deleted_at'    => $this->dateTime()->null(),
            'deleted_by'    => $this->integer()->null(),
            'restored_at'   => $this->dateTime()->null(),
            'restored_by'   => $this->integer()->null(),
        ], $tableOptions);

        // 2. Tabel users_profile
        $this->createTable('{{%users_profile}}', [
            'id'          => $this->primaryKey(),
            'user_id'     => $this->integer()->notNull()->unique(),
            'name'        => $this->string(150)->notNull(),
            'gender'      => "ENUM('male', 'female', 'other') NULL DEFAULT NULL", // <-- PERBAIKAN DI SINI
            'birth_place' => $this->string(100)->null(),
            'birth_date'  => $this->date()->null(),
            'phone'       => $this->string(30)->null(),
            'address'     => $this->text()->null(),

            // Mandatory Audit Columns (UTC)
            'registered_at' => $this->dateTime()->notNull(),
            'registered_by' => $this->integer()->null(),
            'modified_at'   => $this->dateTime()->null(),
            'modified_by'   => $this->integer()->null(),
            'deleted_at'    => $this->dateTime()->null(),
            'deleted_by'    => $this->integer()->null(),
            'restored_at'   => $this->dateTime()->null(),
            'restored_by'   => $this->integer()->null(),
        ], $tableOptions);

        // 3. Tabel auth_sessions
        $this->createTable('{{%auth_sessions}}', [
            'id'               => $this->string(128)->notNull(),
            'user_id'          => $this->integer()->notNull(),
            'ip_address'       => $this->string(45)->null(),
            'user_agent'       => $this->text()->null(),
            'last_activity_at' => $this->dateTime()->notNull(),
            'expires_at'       => $this->dateTime()->notNull(),

            // Mandatory Audit Columns
            'registered_at'    => $this->dateTime()->notNull(),
            'registered_by'    => $this->integer()->null(),
            'modified_at'      => $this->dateTime()->null(),
            'modified_by'      => $this->integer()->null(),
            'deleted_at'       => $this->dateTime()->null(),
            'deleted_by'       => $this->integer()->null(),
            'restored_at'      => $this->dateTime()->null(),
            'restored_by'      => $this->integer()->null(),
            'PRIMARY KEY ([[id]])',
        ], $tableOptions);

        // Foreign Keys & Indexes
        $this->addForeignKey('fk_profile_user', '{{%users_profile}}', 'user_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_session_user', '{{%auth_sessions}}', 'user_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
        
        $this->createIndex('idx_users_status', '{{%users}}', 'status');
        $this->createIndex('idx_users_reg_at', '{{%users}}', 'registered_at');
        $this->createIndex('idx_sessions_user_expire', '{{%auth_sessions}}', ['user_id', 'expires_at']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%auth_sessions}}');
        $this->dropTable('{{%users_profile}}');
        $this->dropTable('{{%users}}');
    }
}
