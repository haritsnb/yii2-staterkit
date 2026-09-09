<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class LoginHistory extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%login_histories}}';
    }

    /**
     * Catat login baru & otomatis hapus riwayat terlama jika melebihi 10 data per user
     */
    public static function recordLogin(int $userId): void
    {
        $nowUtc = gmdate('Y-m-d H:i:s');

        $log = new self();
        $log->user_id    = $userId;
        $log->ip_address = Yii::$app->request->userIP ?? '127.0.0.1';
        $log->user_agent = Yii::$app->request->userAgent;
        $log->location   = 'Indonesia (Auto Detected)';
        $log->login_at   = $nowUtc;
        $log->save(false);

        // Pertahankan hanya 10 riwayat login terakhir per pengguna
        $keepIds = (new \yii\db\Query())
            ->select('id')
            ->from(self::tableName())
            ->where(['user_id' => $userId])
            ->orderBy(['login_at' => SORT_DESC])
            ->limit(10)
            ->column();

        if (!empty($keepIds)) {
            self::deleteAll(['and', ['user_id' => $userId], ['not in', 'id', $keepIds]]);
        }
    }
}