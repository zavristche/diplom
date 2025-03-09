<?php

namespace app\models\block;

use Yii;
use app\models\block\BlockType;
use app\models\block\BlockReason;
use app\models\block\BlockStatus;
use app\models\user\User;

/**
 * This is the model class for table "block".
 *
 * @property int $id
 * @property int $user_id
 * @property int $block_type_id
 * @property int $block_status_id
 * @property int|null $block_reason_id
 * @property string|null $other_reason
 * @property string|null $time_unblock
 *
 * @property BlockReason $blockReason
 * @property BlockType $blockType
 * @property BlockStatus $blockStatus
 * @property User $user
 */
class Block extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'block';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'block_type_id', 'block_status_id'], 'required'],
            [['user_id', 'block_type_id', 'block_reason_id', 'block_status_id'], 'integer'],
            [['time_unblock', 'created_at'], 'safe'],
            [['other_reason'], 'string', 'max' => 255],
            [['block_reason_id'], 'exist', 'skipOnError' => true, 'targetClass' => BlockReason::class, 'targetAttribute' => ['block_reason_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['block_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => BlockType::class, 'targetAttribute' => ['block_type_id' => 'id']],
            [['block_status_id'], 'exist', 'skipOnError' => false, 'targetClass' => BlockStatus::class, 'targetAttribute' => ['block_status_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Пользователь',
            'block_type_id' => 'Тип блокировки',
            'block_status_id' => 'Статус блокировки',
            'block_reason_id' => 'Причина блокировки',
            'other_reason' => 'Иная причина',
            'time_unblock' => 'Время разблокировки',
        ];
    }
    
    /**
     * Gets query for [[BlockReason]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBlockReason()
    {
        return $this->hasOne(BlockReason::class, ['id' => 'block_reason_id']);
    }

    public function getBlockStatus()
    {
        return $this->hasOne(BlockStatus::class, ['id' => 'block_status_id']);
    }

    /**
     * Gets query for [[BlockType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBlockType()
    {
        return $this->hasOne(BlockType::class, ['id' => 'block_type_id']);
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
