<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $auth_key
 * @property string $login_mode
 * @property string|null $avatar
 * @property string $status
 * @property string $registered_at
 * @property int|null $registered_by
 * @property string|null $modified_at
 * @property int|null $modified_by
 * @property string|null $deleted_at
 * @property int|null $deleted_by
 * @property string|null $restored_at
 * @property int|null $restored_by
 *
 * @property UserProfile $profile
 * @property AuthSession[] $sessions
 */
class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName(): string
    {
        return '{{%users}}';
    }

    public function rules(): array
    {
        return [
            [['username', 'email'], 'required'],
            [['username', 'email'], 'trim'],
            ['email', 'email'],
            [['username', 'email'], 'unique'],
            ['login_mode', 'in', 'range' => ['single_device', 'multi_device']],
            ['status', 'in', 'range' => ['active', 'inactive', 'banned']],
            [['avatar'], 'string', 'max' => 255],
            [['registered_by', 'modified_by', 'deleted_by', 'restored_by'], 'integer'],
            [['registered_at', 'modified_at', 'deleted_at', 'restored_at'], 'safe'],
        ];
    }

    // ================= IMPLEMENTASI IDENTITY INTERFACE =================

    /**
     * Cari identitas berdasarkan ID (Hanya user aktif & tidak terhapus)
     */
    public static function findIdentity($id): ?self
    {
        return static::find()
            ->where(['id' => $id, 'deleted_at' => null])
            ->andWhere(['status' => 'active'])
            ->one();
    }

    /**
     * Cari identitas berdasarkan Access Token (untuk API/Bearer)
     */
    public static function findIdentityByAccessToken($token, $type = null): ?self
    {
        return null; // Bisa disesuaikan jika menggunakan API token
    }

    /**
     * Cari user berdasarkan USERNAME atau EMAIL (Bisa login dengan keduanya)
     */
    public static function findByUsername(string $usernameOrEmail): ?self
    {
        return static::find()
            ->where(['or', ['username' => $usernameOrEmail], ['email' => $usernameOrEmail]])
            ->andWhere(['deleted_at' => null])
            ->andWhere(['status' => 'active'])
            ->one();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey(): ?string
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey): ?bool
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validasi kecocokan password hash
     */
    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    // ================= EVENT & HELPER LAINNYA =================

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $nowUtc = gmdate('Y-m-d H:i:s');
        $currentUserId = Yii::$app->user->id ?? 1;

        if ($insert) {
            $this->registered_at = $nowUtc;
            $this->registered_by = $currentUserId;
            $this->auth_key = Yii::$app->security->generateRandomString();
            if (!empty($this->password_hash) && !preg_match('/^\$2[ayb]\$.{56}$/', $this->password_hash)) {
                $this->password_hash = Yii::$app->security->generatePasswordHash($this->password_hash);
            }
        } else {
            $this->modified_at = $nowUtc;
            $this->modified_by = $currentUserId;
        }

        return true;
    }

    public function getProfile()
    {
        return $this->hasOne(UserProfile::class, ['user_id' => 'id']);
    }

    public function getSessions()
    {
        return $this->hasMany(AuthSession::class, ['user_id' => 'id']);
    }

    /**
     * URL Avatar (Gunakan file foto jika ada, atau SVG Inisial jika kosong)
     */
    public function getAvatarUrl(): string
    {
        if (!empty($this->avatar) && file_exists(Yii::getAlias('@webroot/' . $this->avatar))) {
            return Yii::getAlias('@web/' . $this->avatar);
        }

        return self::generateInitialAvatar($this->profile->name ?? $this->username, $this->id);
    }

    public static function generateInitialAvatar(string $name, int|string $seed = 1): string
    {
        $words = preg_split("/[\s,_-]+/", trim($name));
        $initials = '';
        if (count($words) >= 2) {
            $initials = mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
        } elseif (!empty($words[0])) {
            $initials = mb_strtoupper(mb_substr($words[0], 0, 2));
        } else {
            $initials = 'U';
        }

        $palette = [
            '#007bff', '#28a745', '#17a2b8', '#ffc107', '#dc3545',
            '#6610f2', '#e83e8c', '#fd7e14', '#20c997', '#6f42c1',
            '#343a40', '#001f3f', '#39cccc', '#01ff70', '#d81b60'
        ];

        $colorIndex = abs(crc32((string)$seed . $name)) % count($palette);
        $bgColor = $palette[$colorIndex];

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128">'
             . '<rect width="128" height="128" rx="64" fill="' . $bgColor . '"/>'
             . '<text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="#ffffff" font-family="-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,sans-serif" font-size="48" font-weight="bold">'
             . htmlspecialchars($initials, ENT_QUOTES, 'UTF-8')
             . '</text>'
             . '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public function softDelete(): bool
    {
        $this->deleted_at = gmdate('Y-m-d H:i:s');
        $this->deleted_by = Yii::$app->user->id ?? 1;
        return $this->save(false, ['deleted_at', 'deleted_by']);
    }

    public function restore(): bool
    {
        $this->restored_at = gmdate('Y-m-d H:i:s');
        $this->restored_by = Yii::$app->user->id ?? 1;
        $this->deleted_at = null;
        $this->deleted_by = null;
        return $this->save(false, ['deleted_at', 'deleted_by', 'restored_at', 'restored_by']);
    }
}