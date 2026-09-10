<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $user_id
 * @property int $role_id
 * @property string $user_type ('primary' | 'bypass')
 * @property string $assigned_at
 * @property int|null $assigned_by
 */
class AuthUserRole extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%auth_user_roles}}';
    }

    public function rules(): array
    {
        return [
            [['user_id', 'role_id'], 'required'],
            [['user_id', 'role_id', 'assigned_by'], 'integer'],
            ['user_type', 'in', 'range' => ['primary', 'bypass']],
            [['assigned_at'], 'safe'],
            [['user_id', 'role_id'], 'unique', 'targetAttribute' => ['user_id', 'role_id'], 'message' => 'Role ini sudah diberikan kepada pengguna.'],
        ];
    }

    public function getRole()
    {
        return $this->hasOne(AuthRole::class, ['id' => 'role_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Set / Ganti Role Utama (Hanya boleh 1 per user)
     */
    public static function assignPrimaryRole(int $userId, int $roleId): bool
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Hapus role primary lama
            self::deleteAll(['user_id' => $userId, 'user_type' => 'primary']);

            // Jika role tersebut sebelumnya berstatus bypass, hapus agar menjadi primary
            self::deleteAll(['user_id' => $userId, 'role_id' => $roleId]);

            $model = new self();
            $model->user_id = $userId;
            $model->role_id = $roleId;
            $model->user_type = 'primary';
            $model->assigned_at = gmdate('Y-m-d H:i:s');
            $model->assigned_by = Yii::$app->user->id ?? 1;

            if ($model->save()) {
                $transaction->commit();
                return true;
            }

            $transaction->rollBack();
            return false;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * Tambah Role Bypass (Bisa tidak terbatas)
     */
    public static function addBypassRole(int $userId, int $roleId): bool
    {
        // Jangan duplikasi jika sudah menjadi primary role
        $isPrimary = self::find()->where(['user_id' => $userId, 'role_id' => $roleId, 'user_type' => 'primary'])->exists();
        if ($isPrimary) {
            return true;
        }

        $exists = self::find()->where(['user_id' => $userId, 'role_id' => $roleId, 'user_type' => 'bypass'])->one();
        if ($exists) {
            return true;
        }

        $model = new self();
        $model->user_id = $userId;
        $model->role_id = $roleId;
        $model->user_type = 'bypass';
        $model->assigned_at = gmdate('Y-m-d H:i:s');
        $model->assigned_by = Yii::$app->user->id ?? 1;
        return $model->save();
    }

    /**
     * Hapus Role Bypass
     */
    public static function removeBypassRole(int $userId, int $roleId): bool
    {
        return (bool) self::deleteAll(['user_id' => $userId, 'role_id' => $roleId, 'user_type' => 'bypass']);
    }
}