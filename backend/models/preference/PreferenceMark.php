<?php

namespace app\models\preference;

use Yii;
use app\models\mark\Mark;
use app\models\user\User;

/**
 * This is the model class for table "preference_mark".
 *
 * @property int $id
 * @property int $user_id
 * @property int $mark_id
 *
 * @property Mark $mark
 * @property User $user
 */
class PreferenceMark extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'preference_mark';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'mark_id'], 'required'],
            [['user_id', 'mark_id'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['mark_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mark::class, 'targetAttribute' => ['mark_id' => 'id']],
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
            'mark_id' => 'Mark ID',
        ];
    }

    /**
     * Gets query for [[Mark]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMark()
    {
        return $this->hasOne(Mark::class, ['id' => 'mark_id']);
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
