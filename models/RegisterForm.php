<?php

namespace app\models;

use Yii;
use yii\base\Model;

class RegisterForm extends Model
{
    public $name;
    public $username;
    public $email;
    public $password;
    public $password_repeat;
    public $terms = false;

    public function rules(): array
    {
        return [
            [['name', 'username', 'email', 'password', 'password_repeat'], 'required', 'message' => '{attribute} tidak boleh kosong.'],
            [['username', 'email', 'name'], 'trim'],
            ['email', 'email', 'message' => 'Format email tidak valid.'],
            ['username', 'string', 'min' => 3, 'max' => 50, 'tooShort' => 'Username minimal 3 karakter.'],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_\-\.]+$/', 'message' => 'Username hanya boleh berisi huruf, angka, titik, strip, atau underscore.'],
            ['password', 'string', 'min' => 6, 'tooShort' => 'Password minimal 6 karakter.'],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Konfirmasi password tidak cocok.'],
            ['terms', 'required', 'requiredValue' => 1, 'message' => 'Anda wajib menyetujui Syarat & Ketentuan.'],
            
            // Cek keunikan di tabel users
            ['username', 'unique', 'targetClass' => User::class, 'message' => 'Username sudah terdaftar.'],
            ['email', 'unique', 'targetClass' => User::class, 'message' => 'Email sudah terdaftar.'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name'            => 'Nama Lengkap',
            'username'        => 'Username',
            'email'           => 'Email',
            'password'        => 'Password',
            'password_repeat' => 'Ulangi Password',
            'terms'           => 'Syarat & Ketentuan',
        ];
    }

    public function register(): ?User
    {
        if (!$this->validate()) {
            return null;
        }

        $nowUtc = gmdate('Y-m-d H:i:s');

        $user = new User();
        $user->username      = $this->username;
        $user->email         = $this->email;
        $user->password_hash = $this->password;
        $user->login_mode    = 'single_device';
        $user->status        = 'active';
        $user->registered_at = $nowUtc;

        if ($user->save(false)) {
            $profile = new UserProfile();
            $profile->user_id       = $user->id;
            $profile->name          = $this->name;
            $profile->gender        = 'male';
            $profile->registered_at = $nowUtc;
            $profile->save(false);

            return $user;
        }

        return null;
    }
}