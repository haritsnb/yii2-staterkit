<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $parent_id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property int $level
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
 * @property AuthRole $parent
 * @property AuthRole[] $children
 * @property AuthPermission[] $permissions
 */
class AuthRole extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%auth_roles}}';
    }

    public function rules(): array
    {
        return [
            [['name', 'code'], 'required'],
            [['parent_id', 'level', 'registered_by', 'modified_by', 'deleted_by'], 'integer'],
            [['name', 'code'], 'string', 'max' => 100],
            [['code'], 'unique', 'targetAttribute' => ['code', 'deleted_at'], 'message' => 'Kode Role ini sudah digunakan.'],
            [['description'], 'string', 'max' => 255],
            ['status', 'in', 'range' => ['active', 'inactive']],
            [['registered_at', 'modified_at', 'deleted_at', 'restored_at'], 'safe'],
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $nowUtc = gmdate('Y-m-d H:i:s');
        $userId = Yii::$app->user->id ?? 1;

        if ($insert) {
            $this->registered_at = $nowUtc;
            $this->registered_by = $userId;
            
            // Hitung level hierarki otomatis berdasarkan parent
            if ($this->parent_id > 0) {
                $parent = self::findOne($this->parent_id);
                $this->level = $parent ? $parent->level + 1 : 1;
            } else {
                $this->level = 1;
            }
        } else {
            $this->modified_at = $nowUtc;
            $this->modified_by = $userId;
        }

        return true;
    }

    public function getParent()
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }

    public function getChildren()
    {
        return $this->hasMany(self::class, ['parent_id' => 'id'])->where(['deleted_at' => null]);
    }

    public function getRolePermissions()
    {
        return $this->hasMany(AuthRolePermission::class, ['role_id' => 'id']);
    }

    public function getPermissions()
    {
        return $this->hasMany(AuthPermission::class, ['id' => 'permission_id'])
            ->via('rolePermissions');
    }

    public function getUserRoles()
    {
        return $this->hasMany(AuthUserRole::class, ['role_id' => 'id']);
    }

    /**
     * Mengambil seluruh ID role turunan / bawahan secara rekursif (untuk pewarisan hak akses)
     */
    public function getDescendantRoleIds(): array
    {
        $descendants = [];
        foreach ($this->children as $child) {
            $descendants[] = $child->id;
            $descendants = array_merge($descendants, $child->getDescendantRoleIds());
        }
        return array_unique($descendants);
    }

    /**
     * Clone Role beserta seluruh mapping permission-nya
     */
    public function cloneRole(string $newCode, string $newName, ?int $newParentId = null): ?self
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $cloned = new self();
            $cloned->attributes = $this->attributes;
            $cloned->id = null;
            $cloned->code = $newCode;
            $cloned->name = $newName;
            $cloned->parent_id = $newParentId !== null ? $newParentId : $this->parent_id;
            
            if (!$cloned->save()) {
                throw new \Exception('Gagal menyimpan role hasil clone: ' . json_encode($cloned->getErrors()));
            }

            // Duplikasi seluruh relasi permission
            $permissions = AuthRolePermission::find()->where(['role_id' => $this->id])->all();
            $nowUtc = gmdate('Y-m-d H:i:s');
            $userId = Yii::$app->user->id ?? 1;

            foreach ($permissions as $p) {
                $newMap = new AuthRolePermission();
                $newMap->role_id = $cloned->id;
                $newMap->permission_id = $p->permission_id;
                $newMap->created_at = $nowUtc;
                $newMap->created_by = $userId;
                $newMap->save(false);
            }

            $transaction->commit();
            return $cloned;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error('Clone Role Error: ' . $e->getMessage());
            return null;
        }
    }
}