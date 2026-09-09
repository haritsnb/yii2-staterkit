<?php

use yii\db\Migration;

class m260908_103022_seed_initial_users extends Migration
{
    public function safeUp()
    {
        $security = Yii::$app->security;

        $dummyUsers = [
            [
                'username' => 'superadmin',
                'email' => 'admin@example.com',
                'name' => 'Super Administrator',
                'gender' => 'male',
                'login_mode' => 'single_device',
                'status' => 'active',
                'phone' => '081234567890',
                'created_at' => '2026-03-01 02:30:00' // Waktu UTC
            ],
            [
                'username' => 'haritsnb',
                'email' => 'haritsnb@example.com',
                'name' => 'Harits Nala Barrun',
                'gender' => 'male',
                'login_mode' => 'multi_device',
                'status' => 'active',
                'phone' => '081298765432',
                'created_at' => '2026-03-04 10:15:20' // Waktu UTC
            ],
            [
                'username' => 'putriamanah',
                'email' => 'putriamanah@example.com',
                'name' => 'Putri Amanah',
                'gender' => 'female',
                'login_mode' => 'single_device',
                'status' => 'inactive',
                'phone' => '085612344321',
                'created_at' => '2026-03-07 14:45:10' // Waktu UTC
            ],
            [
                'username' => 'putrabagus',
                'email' => 'putrabagus@example.com',
                'name' => 'Putra Bagus',
                'gender' => 'other',
                'login_mode' => 'multi_device',
                'status' => 'banned',
                'phone' => '087788990011',
                'created_at' => '2026-03-08 08:00:00' // Waktu UTC
            ],
        ];

        foreach ($dummyUsers as $u) {
            $this->insert('{{%users}}', [
                'username'      => $u['username'],
                'email'         => $u['email'],
                'password_hash' => $security->generatePasswordHash('password123'),
                'auth_key'      => $security->generateRandomString(),
                'login_mode'    => $u['login_mode'],
                'status'        => $u['status'],
                'registered_at' => $u['created_at'],
                'registered_by' => 1,
            ]);

            $userId = $this->db->getLastInsertID();

            $this->insert('{{%users_profile}}', [
                'user_id'       => $userId,
                'name'          => $u['name'],
                'gender'        => $u['gender'],
                'phone'         => $u['phone'],
                'address'       => 'Jl. Jenderal Sudirman No. ' . rand(1, 100) . ', Jakarta',
                'registered_at' => $u['created_at'],
                'registered_by' => 1,
            ]);
        }
    }

    public function safeDown()
    {
        $this->delete('{{%users_profile}}');
        $this->delete('{{%users}}');
    }
}
