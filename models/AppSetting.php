<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class AppSetting extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%app_settings}}';
    }

    public static function getValue(string $key, ?string $default = null): ?string
    {
        $row = self::findOne(['key' => $key]);
        return $row ? $row->value : $default;
    }

    public static function setValue(string $key, ?string $value, ?string $description = null): void
    {
        $setting = self::findOne(['key' => $key]) ?? new self(['key' => $key]);
        $setting->value = $value;
        if ($description) $setting->description = $description;
        $setting->modified_at = gmdate('Y-m-d H:i:s');
        $setting->save(false);
    }

    /**
     * Mengecek apakah Registrasi Publik saat ini DIBUKA atau DITUTUP
     */
    public static function isRegisterAllowed(): bool
    {
        $mode    = self::getValue('register_mode', 'always_active');
        $startAt = self::getValue('register_start_at');
        $endAt   = self::getValue('register_end_at');
        $nowUtc  = gmdate('Y-m-d H:i:s');

        return match ($mode) {
            'always_active'   => true,
            'always_inactive' => false,
            'active_schedule' => (!empty($startAt) && !empty($endAt) && $nowUtc >= $startAt && $nowUtc <= $endAt),
            'inactive_schedule' => (!(!empty($startAt) && !empty($endAt) && $nowUtc >= $startAt && $nowUtc <= $endAt)),
            default => true
        };
    }
}