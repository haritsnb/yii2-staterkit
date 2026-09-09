<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class UserProfile extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%users_profile}}';
    }

    public function rules(): array
    {
        return [
            [['name'], 'required'],
            ['gender', 'in', 'range' => ['male', 'female', 'other']],
            [['birth_place', 'phone'], 'string', 'max' => 100],
            [['birth_date'], 'date', 'format' => 'php:Y-m-d'],
            [['address'], 'string'],
            [['registered_by', 'modified_by', 'deleted_by', 'restored_by'], 'integer'],
            [['registered_at', 'modified_at', 'deleted_at', 'restored_at'], 'safe'],
        ];
    }

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
        } else {
            $this->modified_at = $nowUtc;
            $this->modified_by = $currentUserId;
        }

        return true;
    }
}