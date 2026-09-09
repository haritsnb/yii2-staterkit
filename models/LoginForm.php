<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm model dengan dukungan penuh parameter KDF & Enkripsi Dinamis
 */
class LoginForm extends Model
{
    public $username;      // Menerima input Username ATAU Email
    public $password;
    public $rememberMe = true;

    // Parameter Enkripsi & KDF (Key Derivation Function) Umum
    public $cipher;
    public $allowedCiphers;
    public $kdfHash;
    public $kdfSalt;
    public $kdfIterations;
    public $iv;
    public $nonce;
    public $tag;
    public $publicKey;

    // Menampung parameter dinamis lainnya secara otomatis
    private $_dynamicAttributes = [];
    private $_user = false;

    public function rules(): array
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
            
            // Daftarkan semua parameter enkripsi & KDF sebagai safe attributes
            [[
                'cipher', 'allowedCiphers', 'kdfHash', 'kdfSalt', 
                'kdfIterations', 'iv', 'nonce', 'tag', 'publicKey'
            ], 'safe'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'username'   => 'Username atau Email',
            'password'   => 'Password',
            'rememberMe' => 'Ingat Saya',
        ];
    }

    /**
     * Magic Setter: Mencegah error "Setting unknown property" 
     * jika ada parameter keamanan baru yang dikirim dari form/frontend
     */
    public function __set($name, $value)
    {
        if ($this->hasProperty($name)) {
            parent::__set($name, $value);
        } else {
            $this->_dynamicAttributes[$name] = $value;
        }
    }

    /**
     * Magic Getter: Mengambil atribut dinamis jika ada
     */
    public function __get($name)
    {
        if ($this->hasProperty($name)) {
            return parent::__get($name);
        }
        return $this->_dynamicAttributes[$name] ?? null;
    }

    public function __isset($name)
    {
        return isset($this->_dynamicAttributes[$name]) || parent::__isset($name);
    }

    /**
     * Validasi kecocokan password
     */
    public function validatePassword($attribute, $params): void
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Username/Email atau Password tidak valid.');
            }
        }
    }

    /**
     * Proses Login & Catat Sesi Perangkat
     */
    public function login(): bool
    {
        if ($this->validate()) {
            $duration = $this->rememberMe ? 3600 * 24 * 30 : 0;
            $isLoggedIn = Yii::$app->user->login($this->getUser(), $duration);

            if ($isLoggedIn) {
                $user = $this->getUser();
                // 1. Catat sesi aktif
                AuthSession::registerSession($user, Yii::$app->session->getId());
                // 2. Catat riwayat login (maksimal 10 record disimpan)
                LoginHistory::recordLogin($user->id);
                return true;
            }
        }

        return false;
    }

    public function getUser(): ?User
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}