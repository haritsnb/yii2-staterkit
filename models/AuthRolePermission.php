<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class AuthRolePermission extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%auth_role_permissions}}';
    }

    public function rules(): array
    {
        return [
            [['role_id', 'permission_id'], 'required'],
            [['role_id', 'permission_id', 'created_by'], 'integer'],
            [['created_at'], 'safe'],
            [['role_id', 'permission_id'], 'unique', 'targetAttribute' => ['role_id', 'permission_id']],
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) return false;
        if ($insert) {
            $this->created_at = gmdate('Y-m-d H:i:s');
            $this->created_by = Yii::$app->user->id ?? 1;
        }
        return true;
    }
}