<?php

use yii\db\Migration;

class m260909_074230_create_login_history_and_settings_tables extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        // 1. Tabel login_histories (Menyimpan riwayat login)
        $this->createTable('{{%login_histories}}', [
            'id'          => $this->primaryKey(),
            'user_id'     => $this->integer()->notNull(),
            'ip_address'  => $this->string(45)->null(),
            'user_agent'  => $this->text()->null(),
            'location'    => $this->string(100)->defaultValue('Indonesia (Auto Detected)'),
            'login_at'    => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addForeignKey('fk_login_hist_user', '{{%login_histories}}', 'user_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('idx_login_hist_user_time', '{{%login_histories}}', ['user_id', 'login_at']);

        // 2. Tabel app_settings (Pengaturan Sistem)
        $this->createTable('{{%app_settings}}', [
            'key'         => $this->string(50)->notNull(),
            'value'       => $this->text()->null(),
            'description' => $this->string(255)->null(),
            'modified_at' => $this->dateTime()->null(),
            'PRIMARY KEY ([[key]])',
        ], $tableOptions);

        // Default Pengaturan Registrasi Publik (Aktif Selamanya)
        $nowUtc = gmdate('Y-m-d H:i:s');
        $this->batchInsert('{{%app_settings}}', ['key', 'value', 'description', 'modified_at'], [
            ['register_mode', 'always_active', 'Mode Registrasi Publik: always_active | active_schedule | always_inactive | inactive_schedule', $nowUtc],
            ['register_start_at', null, 'Tanggal & Jam Mulai Registrasi (UTC)', $nowUtc],
            ['register_end_at', null, 'Tanggal & Jam Selesai Registrasi (UTC)', $nowUtc],
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%app_settings}}');
        $this->dropTable('{{%login_histories}}');
    }
}
