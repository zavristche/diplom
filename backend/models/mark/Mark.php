<?php

namespace app\models\mark;

use Yii;
use app\models\collection\CollectionMark;
use app\models\recipe\RecipeMark;
use app\models\mark\MarkType;
use app\models\preference\PreferenceMark;

/**
 * This is the model class for table "mark".
 *
 * @property int $id
 * @property int $type_id
 * @property string $title
 *
 * @property CollectionMark[] $collectionMarks
 * @property PreferenceMark[] $preferenceMarks
 * @property RecipeMark[] $recipeMarks
 * @property MarkType $type
 */
class Mark extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mark';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type_id', 'title'], 'required'],
            [['type_id'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => MarkType::class, 'targetAttribute' => ['type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type_id' => 'Type ID',
            'title' => 'Title',
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['type'] = fn() => $this->type;

        return $fields;
    }

    public static function getAll()
    {
        return self::find()
        ->select(['id', 'title', 'type_id'])
        ->indexBy('id')
        ->asArray()
        ->all();
    }

    /**
     * Gets query for [[CollectionMarks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCollectionMarks()
    {
        return $this->hasMany(CollectionMark::class, ['mark_id' => 'id']);
    }

    /**
     * Gets query for [[PreferenceMarks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPreferenceMarks()
    {
        return $this->hasMany(PreferenceMark::class, ['mark_id' => 'id']);
    }

    /**
     * Gets query for [[RecipeMarks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipeMarks()
    {
        return $this->hasMany(RecipeMark::class, ['mark_id' => 'id']);
    }

    /**
     * Gets query for [[Type]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(MarkType::class, ['id' => 'type_id']);
    }
}
