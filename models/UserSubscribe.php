<?php

namespace app\models;

use Yii;
use app\models\User;

/**
 * This is the model class for table "user_subscribe".
 *
 * @property int $id
 * @property int $follower_id
 * @property int $subscriber_id
 * @property string $created_at
 *
 * @property User $follower
 * @property User $subscriber
 */
class UserSubscribe extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_subscribe';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['follower_id', 'subscriber_id'], 'required'],
            [['follower_id', 'subscriber_id'], 'integer'],
            [['created_at'], 'safe'],
            [['follower_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['follower_id' => 'id']],
            [['subscriber_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['subscriber_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'follower_id' => 'Follower ID',
            'subscriber_id' => 'Subscriber ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[Follower]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFollower()
    {
        return $this->hasOne(User::class, ['id' => 'follower_id']);
    }

    /**
     * Gets query for [[Subscriber]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriber()
    {
        return $this->hasOne(User::class, ['id' => 'subscriber_id']);
    }
}
