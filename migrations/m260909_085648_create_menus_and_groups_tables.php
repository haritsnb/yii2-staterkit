<?php

use yii\db\Migration;

class m260909_085648_create_menus_and_groups_tables extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        // 1. Tabel menus_group
        $this->createTable('{{%menus_group}}', [
            'id'            => $this->primaryKey(),
            'name'          => $this->string(100)->notNull(),
            'code'          => $this->string(100)->notNull()->unique(),
            'description'   => $this->string(255)->null(),
            'type'          => $this->string(50)->notNull()->defaultValue('admin-panel'),
            'status'        => "ENUM('active', 'inactive') NOT NULL DEFAULT 'active'",

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

        // 2. Tabel menus
        $this->createTable('{{%menus}}', [
            'id'            => $this->primaryKey(),
            'group_id'      => $this->integer()->notNull(),
            'parent_id'     => $this->integer()->notNull()->defaultValue(0),
            'order'         => $this->integer()->notNull()->defaultValue(1),
            'label'         => $this->string(100)->notNull(),
            'link'          => $this->string(255)->notNull()->defaultValue('#'),
            'icon'          => $this->string(50)->null()->defaultValue('fas fa-circle'),
            'type'          => "ENUM('url', 'text') NOT NULL DEFAULT 'url'",
            'status'        => "ENUM('active', 'inactive') NOT NULL DEFAULT 'active'",
            'bind'          => $this->boolean()->notNull()->defaultValue(true),

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

        // Foreign Keys & Indexes
        $this->addForeignKey('fk_menus_group_id', '{{%menus}}', 'group_id', '{{%menus_group}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('idx_menus_group_order', '{{%menus}}', ['group_id', 'parent_id', 'order']);

        // Seeder Initial Data
        $nowUtc = gmdate('Y-m-d H:i:s');
        
        $this->batchInsert('{{%menus_group}}', ['name', 'code', 'description', 'type', 'status', 'registered_at', 'registered_by'], [
            ['Top Navigation', 'lp-top-navigation', 'Top Navigasi untuk halaman landing page', 'landing-page', 'active', $nowUtc, 1],
            ['Main Sidebar', 'ap-main-sidebar', 'Sidebar utama untuk halaman admin panel', 'admin-panel', 'active', $nowUtc, 1],
            ['Content Sidebar - Profile', 'ap-content-sidebar-profile', 'Sidebar konten halaman profil pengguna', 'admin-panel', 'active', $nowUtc, 1],
        ]);

        // Seed Default Menus untuk Main Sidebar (Group ID: 2)
        $this->batchInsert('{{%menus}}', ['group_id', 'parent_id', 'order', 'label', 'link', 'icon', 'type', 'status', 'bind', 'registered_at', 'registered_by'], [
            [2, 0, 1, 'Dashboard', '/dashboard', 'fas fa-tachometer-alt', 'url', 'active', 1, $nowUtc, 1],
            [2, 0, 2, 'Manajemen Pengguna', '/users', 'fas fa-users', 'url', 'active', 1, $nowUtc, 1],
            [2, 0, 3, 'Navigasi Menu', '/menus', 'fas fa-bars', 'url', 'active', 1, $nowUtc, 1],
            [2, 0, 4, 'RBAC', '/rbac', 'fas fa-user-shield', 'url', 'active', 1, $nowUtc, 1],
            [2, 4, 5, 'Roles', '/roles', 'fas fa-user-tag', 'url', 'active', 1, $nowUtc, 1],
            [2, 4, 6, 'Permissions', '/permissions', 'fas fa-user-lock', 'url', 'active', 1, $nowUtc, 1],
            [2, 0, 7, 'Profil Saya', '/profile', 'fas fa-user-cog', 'url', 'active', 1, $nowUtc, 1],
            [2, 0, 8, 'Pengaturan Sistem', '/settings', 'fas fa-cogs', 'url', 'active', 1, $nowUtc, 1],
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%menus}}');
        $this->dropTable('{{%menus_group}}');
    }
}
