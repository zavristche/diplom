<?php

namespace app\models\mark;

use Yii;
use app\models\mark\Mark;


/**
 * This is the model class for table "mark_type".
 *
 * @property int $id
 * @property string $title
 *
 * @property Mark[] $marks
 */
class MarkType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mark_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    public static function getAll()
    {
        return self::find()->select('title')->indexBy('id')->column();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
        ];
    }

    /**
     * Gets query for [[Marks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMarks()
    {
        return $this->hasMany(Mark::class, ['type_id' => 'id']);
    }
}
