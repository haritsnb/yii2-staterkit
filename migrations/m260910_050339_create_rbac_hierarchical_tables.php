<?php

use yii\db\Migration;

class m260910_050339_create_rbac_hierarchical_tables extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        // =========================================================================
        // 1. TABEL ROLES (Hierarkis Jabatan Perusahaan)
        // =========================================================================
        $this->createTable('{{%auth_roles}}', [
            'id'            => $this->primaryKey(),
            'parent_id'     => $this->integer()->notNull()->defaultValue(0),
            'name'          => $this->string(100)->notNull(),
            'code'          => $this->string(100)->notNull()->unique(),
            'description'   => $this->string(255)->null(),
            'level'         => $this->smallInteger()->notNull()->defaultValue(1), // Kedalaman hierarki
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

        $this->createIndex('idx_roles_parent_level', '{{%auth_roles}}', ['parent_id', 'level']);

        // =========================================================================
        // 2. TABEL PERMISSIONS (Hierarki 4-Level: Module -> Page -> Widget -> Action)
        // =========================================================================
        $this->createTable('{{%auth_permissions}}', [
            'id'            => $this->primaryKey(),
            'parent_id'     => $this->integer()->notNull()->defaultValue(0),
            'code'          => $this->string(150)->notNull()->unique(), // contoh: core:users:datatables:create
            'name'          => $this->string(150)->notNull(),
            'type'          => "ENUM('module', 'page', 'widget', 'action') NOT NULL DEFAULT 'action'",
            'description'   => $this->string(255)->null(),
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

        $this->createIndex('idx_permissions_parent_type', '{{%auth_permissions}}', ['parent_id', 'type']);

        // =========================================================================
        // 3. TABEL MAPPING ROLE -> PERMISSION
        // =========================================================================
        $this->createTable('{{%auth_role_permissions}}', [
            'id'            => $this->primaryKey(),
            'role_id'       => $this->integer()->notNull(),
            'permission_id' => $this->integer()->notNull(),
            'created_at'    => $this->dateTime()->notNull(),
            'created_by'    => $this->integer()->null(),
        ], $tableOptions);

        $this->addForeignKey('fk_role_perm_role', '{{%auth_role_permissions}}', 'role_id', '{{%auth_roles}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_role_perm_perm', '{{%auth_role_permissions}}', 'permission_id', '{{%auth_permissions}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('idx_unique_role_permission', '{{%auth_role_permissions}}', ['role_id', 'permission_id'], true);

        // =========================================================================
        // 4. TABEL MAPPING USER -> ROLES (1 Role Utama + Unlimited Role Bypass)
        // =========================================================================
        $this->createTable('{{%auth_user_roles}}', [
            'id'            => $this->primaryKey(),
            'user_id'       => $this->integer()->notNull(),
            'role_id'       => $this->integer()->notNull(),
            'user_type'     => "ENUM('primary', 'bypass') NOT NULL DEFAULT 'primary'", // Penanda Role Utama vs Bypass
            'assigned_at'   => $this->dateTime()->notNull(),
            'assigned_by'   => $this->integer()->null(),
        ], $tableOptions);

        $this->addForeignKey('fk_user_role_user', '{{%auth_user_roles}}', 'user_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_user_role_role', '{{%auth_user_roles}}', 'role_id', '{{%auth_roles}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('idx_unique_user_role', '{{%auth_user_roles}}', ['user_id', 'role_id'], true);
        $this->createIndex('idx_user_role_type', '{{%auth_user_roles}}', ['user_id', 'user_type']);

        // =========================================================================
        // 5. SEEDER: HIERARKI ROLES PERUSAHAAN (Sesuai Diagram)
        // =========================================================================
        $nowUtc = gmdate('Y-m-d H:i:s');

        // Level 1: Owner / Pemilik
        $this->insert('{{%auth_roles}}', [
            'parent_id' => 0, 'level' => 1, 'name' => 'Owner / Pemilik', 'code' => 'owner',
            'description' => 'Akses penuh kepemilikan bisnis dan performa keseluruhan (HP/Laptop)',
            'registered_at' => $nowUtc, 'registered_by' => 1
        ]);
        $ownerId = $this->db->getLastInsertID();

        // Level 2: Manajer Warung
        $this->insert('{{%auth_roles}}', [
            'parent_id' => $ownerId, 'level' => 2, 'name' => 'Manajer Warung', 'code' => 'store-manager',
            'description' => 'Akses operasional POS Full + Backoffice cabang',
            'registered_at' => $nowUtc, 'registered_by' => 1
        ]);
        $managerId = $this->db->getLastInsertID();

        // Level 3a: Supervisor / Kasir Utama
        $this->insert('{{%auth_roles}}', [
            'parent_id' => $managerId, 'level' => 3, 'name' => 'Supervisor / Kasir Utama', 'code' => 'supervisor-cashier',
            'description' => 'Akses modul POS Supervisor dan pembatalan transaksi',
            'registered_at' => $nowUtc, 'registered_by' => 1
        ]);
        $spvId = $this->db->getLastInsertID();

        // Level 3b: Kepala Koki (Chef)
        $this->insert('{{%auth_roles}}', [
            'parent_id' => $managerId, 'level' => 3, 'name' => 'Kepala Koki (Chef)', 'code' => 'head-chef',
            'description' => 'Akses KDS Kitchen Display + Manajemen Stok Web Gudang',
            'registered_at' => $nowUtc, 'registered_by' => 1
        ]);
        $chefId = $this->db->getLastInsertID();

        // Level 4a: Kasir / Pramusaji (Anak dari Supervisor)
        $this->insert('{{%auth_roles}}', [
            'parent_id' => $spvId, 'level' => 4, 'name' => 'Kasir / Pramusaji', 'code' => 'cashier-waiter',
            'description' => 'Akses POS Standar penerimaan order dan pembayaran',
            'registered_at' => $nowUtc, 'registered_by' => 1
        ]);

        // Level 4b: Koki / Cook Helper (Anak dari Kepala Koki)
        $this->insert('{{%auth_roles}}', [
            'parent_id' => $chefId, 'level' => 4, 'name' => 'Koki / Cook Helper', 'code' => 'cook-helper',
            'description' => 'Akses KDS Layar Antrean dan update status masakan selesai',
            'registered_at' => $nowUtc, 'registered_by' => 1
        ]);

        // Role Khusus Independen: Staf Administrasi / HRD
        $this->insert('{{%auth_roles}}', [
            'parent_id' => $ownerId, 'level' => 2, 'name' => 'Staf Administrasi / HRD', 'code' => 'hrd-admin',
            'description' => 'Akses Khusus: Hanya Web Backoffice (Input Karyawan / Gaji)',
            'registered_at' => $nowUtc, 'registered_by' => 1
        ]);

        // =========================================================================
        // 6. SEEDER: NESTED PERMISSIONS (Module -> Page -> Widget -> Action)
        // =========================================================================
        $this->seedNestedPermissions($nowUtc);

        // =========================================================================
        // 7. ASSIGN SUPERADMIN DEFAULT ROLE
        // =========================================================================
        // Berikan User ID 1 (superadmin) Role Utama = Owner
        $this->insert('{{%auth_user_roles}}', [
            'user_id'     => 1,
            'role_id'     => $ownerId,
            'user_type'   => 'primary',
            'assigned_at' => $nowUtc,
            'assigned_by' => 1,
        ]);
    }

    /**
     * Helper Seeder untuk Struktur Pohon Permission
     */
    private function seedNestedPermissions(string $nowUtc): void
    {
        // -------------------------------------------------------------
        // MODULE 1: CORE
        // -------------------------------------------------------------
        $this->insert('{{%auth_permissions}}', [
            'parent_id' => 0, 'code' => 'core', 'name' => 'Modul Core Sistem', 'type' => 'module', 'registered_at' => $nowUtc
        ]);
        $modCoreId = $this->db->getLastInsertID();

        // 1.1 Page: Dashboard
        $this->insert('{{%auth_permissions}}', [
            'parent_id' => $modCoreId, 'code' => 'core:dashboard', 'name' => 'Halaman Dashboard', 'type' => 'page', 'registered_at' => $nowUtc
        ]);
        $pageDashboardId = $this->db->getLastInsertID();

        // 1.1.1 Widget: Statistic Users Inactive
        $this->insert('{{%auth_permissions}}', [
            'parent_id' => $pageDashboardId, 'code' => 'core:dashboard:statistic-users-inactive', 'name' => 'Widget Statistik User Nonaktif', 'type' => 'widget', 'registered_at' => $nowUtc
        ]);
        $wInactId = $this->db->getLastInsertID();
        $this->insert('{{%auth_permissions}}', ['parent_id' => $wInactId, 'code' => 'core:dashboard:statistic-users-inactive:read', 'name' => 'Lihat Widget User Nonaktif', 'type' => 'action', 'registered_at' => $nowUtc]);

        // 1.1.2 Widget: Statistic Users Active
        $this->insert('{{%auth_permissions}}', [
            'parent_id' => $pageDashboardId, 'code' => 'core:dashboard:statistic-users-active', 'name' => 'Widget Statistik User Aktif', 'type' => 'widget', 'registered_at' => $nowUtc
        ]);
        $wActId = $this->db->getLastInsertID();
        $this->insert('{{%auth_permissions}}', ['parent_id' => $wActId, 'code' => 'core:dashboard:statistic-users-active:read', 'name' => 'Lihat Widget User Aktif', 'type' => 'action', 'registered_at' => $nowUtc]);

        // 1.2 Page: Users Management
        $this->insert('{{%auth_permissions}}', [
            'parent_id' => $modCoreId, 'code' => 'core:users', 'name' => 'Halaman Manajemen Pengguna', 'type' => 'page', 'registered_at' => $nowUtc
        ]);
        $pageUsersId = $this->db->getLastInsertID();

        // 1.2.1 Widget: DataTables Users
        $this->insert('{{%auth_permissions}}', [
            'parent_id' => $pageUsersId, 'code' => 'core:users:datatables', 'name' => 'Tabel DataTables Pengguna', 'type' => 'widget', 'registered_at' => $nowUtc
        ]);
        $wUserDtId = $this->db->getLastInsertID();
        
        $crudActions = ['create' => 'Tambah Data', 'read' => 'Lihat Data', 'update' => 'Edit Data', 'delete' => 'Soft Delete Data', 'destroy' => 'Hapus Permanen Data'];
        foreach ($crudActions as $act => $actName) {
            $this->insert('{{%auth_permissions}}', [
                'parent_id' => $wUserDtId, 'code' => "core:users:datatables:{$act}", 'name' => "{$actName} Pengguna", 'type' => 'action', 'registered_at' => $nowUtc
            ]);
        }

        // 1.3 Page: Settings
        $this->insert('{{%auth_permissions}}', [
            'parent_id' => $modCoreId, 'code' => 'core:settings', 'name' => 'Halaman Pengaturan', 'type' => 'page', 'registered_at' => $nowUtc
        ]);
        $pageSettingsId = $this->db->getLastInsertID();

        // 1.3.1 Widget: Public Registration Control
        $this->insert('{{%auth_permissions}}', [
            'parent_id' => $pageSettingsId, 'code' => 'core:settings:public-registration', 'name' => 'Kontrol Registrasi Publik', 'type' => 'widget', 'registered_at' => $nowUtc
        ]);
        $wRegId = $this->db->getLastInsertID();
        $this->insert('{{%auth_permissions}}', ['parent_id' => $wRegId, 'code' => 'core:settings:public-registration:read', 'name' => 'Lihat Pengaturan Registrasi', 'type' => 'action', 'registered_at' => $nowUtc]);
        $this->insert('{{%auth_permissions}}', ['parent_id' => $wRegId, 'code' => 'core:settings:public-registration:update', 'name' => 'Ubah Pengaturan Registrasi', 'type' => 'action', 'registered_at' => $nowUtc]);

        // -------------------------------------------------------------
        // MODULE 2: RBAC
        // -------------------------------------------------------------
        $this->insert('{{%auth_permissions}}', [
            'parent_id' => 0, 'code' => 'rbac', 'name' => 'Modul RBAC Hak Akses', 'type' => 'module', 'registered_at' => $nowUtc
        ]);
        $modRbacId = $this->db->getLastInsertID();

        // 2.1 Page: Roles Management
        $this->insert('{{%auth_permissions}}', [
            'parent_id' => $modRbacId, 'code' => 'rbac:roles', 'name' => 'Halaman Kelola Roles', 'type' => 'page', 'registered_at' => $nowUtc
        ]);
        $pageRolesId = $this->db->getLastInsertID();
        $this->insert('{{%auth_permissions}}', [
            'parent_id' => $pageRolesId, 'code' => 'rbac:roles:datatables', 'name' => 'Tabel DataTables Roles', 'type' => 'widget', 'registered_at' => $nowUtc
        ]);
        $wRoleDtId = $this->db->getLastInsertID();
        foreach ($crudActions as $act => $actName) {
            $this->insert('{{%auth_permissions}}', [
                'parent_id' => $wRoleDtId, 'code' => "rbac:roles:datatables:{$act}", 'name' => "{$actName} Role", 'type' => 'action', 'registered_at' => $nowUtc
            ]);
        }

        // 2.2 Page: Permissions Management
        $this->insert('{{%auth_permissions}}', [
            'parent_id' => $modRbacId, 'code' => 'rbac:permissions', 'name' => 'Halaman Kelola Permissions', 'type' => 'page', 'registered_at' => $nowUtc
        ]);
        $pagePermsId = $this->db->getLastInsertID();
        $this->insert('{{%auth_permissions}}', [
            'parent_id' => $pagePermsId, 'code' => 'rbac:permissions:datatables', 'name' => 'Tabel DataTables Permissions', 'type' => 'widget', 'registered_at' => $nowUtc
        ]);
        $wPermDtId = $this->db->getLastInsertID();
        foreach ($crudActions as $act => $actName) {
            $this->insert('{{%auth_permissions}}', [
                'parent_id' => $wPermDtId, 'code' => "rbac:permissions:datatables:{$act}", 'name' => "{$actName} Permission", 'type' => 'action', 'registered_at' => $nowUtc
            ]);
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%auth_user_roles}}');
        $this->dropTable('{{%auth_role_permissions}}');
        $this->dropTable('{{%auth_permissions}}');
        $this->dropTable('{{%auth_roles}}');
    }
}
