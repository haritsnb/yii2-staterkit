<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class AuthSession extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%auth_sessions}}';
    }

    /**
     * Daftarkan session baru & hapus sesi lama jika user diset single_device
     */
    public static function registerSession(User $user, string $sessionId, int $ttlSeconds = 86400): void
    {
        $nowUtc = gmdate('Y-m-d H:i:s');
        $expireUtc = gmdate('Y-m-d H:i:s', time() + $ttlSeconds);

        if ($user->login_mode === 'single_device') {
            // Hapus semua sesi aktif sebelumnya dari user ini
            self::deleteAll(['user_id' => $user->id]);
        }

        $session = new self();
        $session->id = $sessionId;
        $session->user_id = $user->id;
        $session->ip_address = Yii::$app->request->userIP;
        $session->user_agent = Yii::$app->request->userAgent;
        $session->last_activity_at = $nowUtc;
        $session->expires_at = $expireUtc;
        $session->registered_at = $nowUtc;
        $session->registered_by = $user->id;
        $session->save(false);
    }
}