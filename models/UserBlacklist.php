<?php

namespace app\models;

use Yii;
use app\models\User;

/**
 * This is the model class for table "user_blacklist".
 *
 * @property int $id
 * @property int $user_id
 * @property int $block_user_id
 * @property string $blocked_at
 *
 * @property User $blockUser
 * @property User $user
 */
class UserBlacklist extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_blacklist';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'block_user_id'], 'required'],
            [['user_id', 'block_user_id'], 'integer'],
            [['blocked_at'], 'safe'],
            [['block_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['block_user_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'block_user_id' => 'Block User ID',
            'blocked_at' => 'Blocked At',
        ];
    }

    /**
     * Gets query for [[BlockUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBlockUser()
    {
        return $this->hasOne(User::class, ['id' => 'block_user_id']);
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
}
