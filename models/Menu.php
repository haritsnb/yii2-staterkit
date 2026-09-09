<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Menu extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%menus}}';
    }

    public function rules(): array
    {
        return [
            [['group_id', 'label'], 'required'],
            [['group_id', 'parent_id', 'order', 'bind'], 'integer'],
            [['label', 'link', 'icon', 'type', 'status'], 'string'],
            [['registered_by', 'modified_by', 'deleted_by'], 'integer'],
            [['registered_at', 'modified_at', 'deleted_at'], 'safe'],
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
            if (empty($this->order)) {
                $maxOrder = (int) self::find()->where(['group_id' => $this->group_id, 'parent_id' => $this->parent_id])->max('`order`');
                $this->order = $maxOrder + 1;
            }
        } else {
            $this->modified_at = $now;
            $this->modified_by = $userId;
        }
        return true;
    }

    public function getGroup()
    {
        return $this->hasOne(MenuGroup::class, ['id' => 'group_id']);
    }
}