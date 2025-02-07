<?php

namespace app\models\block;

use Yii;
use app\models\block\BlockReason;
use app\models\block\BlockStatus;
use app\models\block\BlockType;


/**
 * This is the model class for table "block_collection".
 *
 * @property int $id
 * @property int $collection_id
 * @property int $type_id
 * @property int $status_id
 * @property int|null $reason_id
 * @property string|null $other_reason
 * @property string $created_at
 *
 * @property BlockReason $reason
 * @property BlockStatus $status
 * @property BlockType $type
 */
class BlockCollection extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'block_collection';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['collection_id', 'type_id', 'status_id'], 'required'],
            [['collection_id', 'type_id', 'status_id', 'reason_id'], 'integer'],
            [['created_at'], 'safe'],
            [['other_reason'], 'string', 'max' => 255],
            [['reason_id'], 'exist', 'skipOnError' => true, 'targetClass' => BlockReason::class, 'targetAttribute' => ['reason_id' => 'id']],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => BlockStatus::class, 'targetAttribute' => ['status_id' => 'id']],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => BlockType::class, 'targetAttribute' => ['type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'collection_id' => 'Collection ID',
            'type_id' => 'Type ID',
            'status_id' => 'Status ID',
            'reason_id' => 'Reason ID',
            'other_reason' => 'Other Reason',
            'created_at' => 'Created At',
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
}
