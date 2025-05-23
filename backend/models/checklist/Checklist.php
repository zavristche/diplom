<?php

namespace app\models\checklist;

use Yii;
use app\models\user\User;

/**
 * This is the model class for table "checklist".
 *
 * @property int $id
 * @property int $user_id
 * @property string $created_at
 * @property string|null $date
 *
 * @property User $user
 */
class Checklist extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'checklist';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
            [['created_at', 'date'], 'safe'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    public function fields()
    {
        $fields = parent::fields();

        $fields['user'] = function() {
            $userData = $this->user;
            unset(
                $userData['auth_key'],
                $userData['password'],
            );
            return $userData;
        };
        $fields['products'] = fn() => $this->getProducts()->select([])->asArray()->all();

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
            'date' => 'Date',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getProducts()
    {
        return $this->hasMany(ChecklistProduct::class, ['list_id' => $this->id]);
    }
}
