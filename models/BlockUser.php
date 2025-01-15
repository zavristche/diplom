<?php

namespace app\models;

use Yii;
use app\models\User;
use app\models\BlockType;
use app\models\BlockReason;
use app\models\BlockStatus;

/**
 * This is the model class for table "block_user".
 *
 * @property int $id
 * @property int $user_id
 * @property int $type_id
 * @property int $status_id
 * @property int|null $reason_id
 * @property int|null $other_reason
 * @property string $created_at
 * @property string|null $end_time
 *
 * @property BlockReason $reason
 * @property BlockStatus $status
 * @property BlockType $type
 * @property User $user
 */
class BlockUser extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'block_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'type_id', 'status_id'], 'required'],
            [['user_id', 'type_id', 'status_id', 'reason_id', 'other_reason'], 'integer'],
            [['created_at', 'end_time'], 'safe'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => BlockType::class, 'targetAttribute' => ['type_id' => 'id']],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => BlockStatus::class, 'targetAttribute' => ['status_id' => 'id']],
            [['reason_id'], 'exist', 'skipOnError' => true, 'targetClass' => BlockReason::class, 'targetAttribute' => ['reason_id' => 'id']],
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
            'type_id' => 'Type ID',
            'status_id' => 'Status ID',
            'reason_id' => 'Reason ID',
            'other_reason' => 'Other Reason',
            'created_at' => 'Created At',
            'end_time' => 'End Time',
        ];
    }

    /**
     * Gets query for [[Reason]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReason()
    {
        return $this->hasOne(BlockReason::class, ['id' => 'reason_id']);
    }

    /**
     * Gets query for [[Status]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(BlockStatus::class, ['id' => 'status_id']);
    }

    /**
     * Gets query for [[Type]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(BlockType::class, ['id' => 'type_id']);
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
