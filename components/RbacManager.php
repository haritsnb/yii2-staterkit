<?php

namespace app\components;

use Yii;
use yii\base\Component;
use app\models\AuthRole;
use app\models\AuthPermission;
use app\models\AuthUserRole;
use app\models\AuthRolePermission;

class RbacManager extends Component
{
    /**
     * Memory cache per-request agar query permission tidak berulang
     */
    private static array $_userPermissionsCache = [];
    private static array $_userRolesCache = [];

    /**
     * METODE UTAMA: Mengecek apakah pengguna memiliki izin tertentu
     * 
     * Contoh pemanggilan:
     * if (RbacManager::can('core:users:datatables:create')) { ... }
     *
     * @param string $permissionCode Kode unik permission (misal: 'core:users:datatables:create')
     * @param int|null $userId ID User (default: user aktif)
     * @return bool
     */
    public static function can(string $permissionCode, ?int $userId = null): bool
    {
        $userId = $userId ?: (Yii::$app->user->id ?? null);
        if (!$userId) {
            return false;
        }

        // Superadmin (User ID: 1) otomatis bypass semua permission
        if ((int) $userId === 1) {
            return true;
        }

        $permissions = self::getUserPermissions($userId);
        return in_array($permissionCode, $permissions, true);
    }

    /**
     * Mengambil daftar seluruh permission yang dimiliki user
     * (Kombinasi 1 Role Utama + Seluruh Role Bypass + Role Inheritance Hierarki)
     */
    public static function getUserPermissions(int $userId): array
    {
        if (isset(self::$_userPermissionsCache[$userId])) {
            return self::$_userPermissionsCache[$userId];
        }

        // 1. Ambil seluruh Role ID yang dimiliki user (Primary + Bypass)
        $userRoleRows = AuthUserRole::find()
            ->with('role')
            ->where(['user_id' => $userId])
            ->all();

        if (empty($userRoleRows)) {
            return self::$_userPermissionsCache[$userId] = [];
        }

        $allEffectiveRoleIds = [];
        foreach ($userRoleRows as $ur) {
            if (!$ur->role || $ur->role->status !== 'active' || $ur->role->deleted_at !== null) {
                continue;
            }

            $allEffectiveRoleIds[] = $ur->role->id;

            // HIERARKI PERUSAHAAN: Role level atas otomatis mewarisi permission bawahan
            $descendantIds = $ur->role->getDescendantRoleIds();
            if (!empty($descendantIds)) {
                $allEffectiveRoleIds = array_merge($allEffectiveRoleIds, $descendantIds);
            }
        }

        $allEffectiveRoleIds = array_unique($allEffectiveRoleIds);

        if (empty($allEffectiveRoleIds)) {
            return self::$_userPermissionsCache[$userId] = [];
        }

        // 2. Ambil seluruh kode permission dari role-role tersebut
        $permissionCodes = (new \yii\db\Query())
            ->select('p.code')
            ->from(['p' => AuthPermission::tableName()])
            ->innerJoin(['rp' => AuthRolePermission::tableName()], 'rp.permission_id = p.id')
            ->where(['in', 'rp.role_id', $allEffectiveRoleIds])
            ->andWhere(['p.status' => 'active', 'p.deleted_at' => null])
            ->column();

        return self::$_userPermissionsCache[$userId] = array_unique($permissionCodes);
    }

    /**
     * Mendapatkan ringkasan role user (Primary Role & Bypass Roles)
     */
    public static function getUserRolesSummary(int $userId): array
    {
        if (isset(self::$_userRolesCache[$userId])) {
            return self::$_userRolesCache[$userId];
        }

        $records = AuthUserRole::find()
            ->with('role')
            ->where(['user_id' => $userId])
            ->all();

        $summary = [
            'primary' => null,
            'bypass'  => [],
        ];

        foreach ($records as $r) {
            if (!$r->role || $r->role->deleted_at !== null) continue;

            if ($r->user_type === 'primary') {
                $summary['primary'] = $r->role;
            } else {
                $summary['bypass'][] = $r->role;
            }
        }

        return self::$_userRolesCache[$userId] = $summary;
    }
}