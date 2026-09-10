<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $parent_id
 * @property string $code
 * @property string $name
 * @property string $type
 * @property string|null $description
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
 * @property AuthPermission $parent
 * @property AuthPermission[] $children
 */
class AuthPermission extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%auth_permissions}}';
    }

    public function rules(): array
    {
        return [
            [['code', 'name', 'type'], 'required'],
            [['parent_id', 'registered_by', 'modified_by', 'deleted_by'], 'integer'],
            [['code', 'name'], 'string', 'max' => 150],
            [['code'], 'unique', 'targetAttribute' => ['code', 'deleted_at'], 'message' => 'Kode Permission sudah terdaftar.'],
            [['description'], 'string', 'max' => 255],
            ['type', 'in', 'range' => ['module', 'page', 'widget', 'action']],
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

    /**
     * Clone Subtree Permission (Termasuk seluruh child / action di bawahnya)
     */
    public function cloneSubtree(int $targetParentId, string $prefixFind, string $prefixReplace): ?self
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $cloned = new self();
            $cloned->attributes = $this->attributes;
            $cloned->id = null;
            $cloned->parent_id = $targetParentId;
            $cloned->code = str_replace($prefixFind, $prefixReplace, $this->code);
            $cloned->name = $this->name . ' (Clone)';
            
            if (!$cloned->save()) {
                throw new \Exception('Gagal clone node permission: ' . json_encode($cloned->getErrors()));
            }

            // Clone seluruh anak secara rekursif
            foreach ($this->children as $child) {
                $child->cloneSubtree($cloned->id, $prefixFind, $prefixReplace);
            }

            $transaction->commit();
            return $cloned;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error('Clone Permission Subtree Error: ' . $e->getMessage());
            return null;
        }
    }
}