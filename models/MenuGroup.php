<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class MenuGroup extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%menus_group}}';
    }

    public function rules(): array
    {
        return [
            [['name', 'code'], 'required'],
            [['code'], 'unique'],
            [['description', 'type', 'status'], 'string'],
            [['registered_by', 'modified_by'], 'integer'],
            [['registered_at', 'modified_at'], 'safe'],
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) return false;
        $now = gmdate('Y-m-d H:i:s');
        $userId = Yii::$app->user->id ?? 1;

        if ($insert) {
            $this->registered_at = $now;
            $this->registered_by = $userId;
        } else {
            $this->modified_at = $now;
            $this->modified_by = $userId;
        }
        return true;
    }

    public function getMenus()
    {
        return $this->hasMany(Menu::class, ['group_id' => 'id'])->orderBy(['parent_id' => SORT_ASC, 'order' => SORT_ASC]);
    }
}