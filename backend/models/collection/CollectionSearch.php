<?php

namespace app\models\collection;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\collection\Collection;

/**
 * CollectionSearch represents the model behind the search form of `app\models\collection\Collection`.
 */
class CollectionSearch extends Collection
{

    public array $marks;
    public array $products;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'private_id', 'status_id', 'saved', 'likes'], 'integer'],
            [['title', 'photo', 'description', 'created_at'], 'safe'],
            [['marks', 'products'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Collection::find()->with([
            'user', 'collectionMarks', 'collectionProducts', 'status', 'private', 'collectionReactions', 'collectionRecipes'
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        if (!($this->load($params, '') && $this->validate())) {
            return $dataProvider;
        }

        if (!empty($this->marks)) {
            $query->joinWith(['collectionMarks' => function ($q) {
                $q->andWhere(['mark_id' => $this->marks]);
            }])
            ->groupBy('collection.id')
            ->having('COUNT(DISTINCT collection_mark.mark_id) = ' . count($this->marks));
        }

        if (!empty($this->products)) {
            $query->joinWith(['collectionProducts' => function ($q) {
                $q->andWhere(['product_id' => $this->products]);
            }])
            ->groupBy('collection.id')
            ->having('COUNT(DISTINCT collection_product.product_id) = ' . count($this->products));
        }

        // Фильтрация по стандартным полям
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['user_id' => $this->user_id]);
        $query->andFilterWhere(['private_id' => $this->private_id]);
        $query->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }
}
