<?php

namespace app\models\recipe;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\recipe\Recipe;
use yii\db\Expression;

/**
 * RecipeSearch represents the model behind the search form of `app\models\recipe\Recipe`.
 */
class RecipeSearch extends Recipe
{
    public array $marks;
    public array $products;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'status_id', 'private_id', 'complexity_id', 'saved', 'likes'], 'integer'],
            [['created_at', 'title', 'time', 'photo', 'description'], 'safe'],
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

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Recipe::find()->with([
            'user', 'recipeMarks', 'recipeProducts', 'steps', 'status', 'private', 'recipeReactions', 'collectionRecipes', 
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        if (!($this->load($params, '') && $this->validate())) {
            return $dataProvider;
        }

        if (!empty($this->marks)) {
            $query->joinWith(['recipeMarks' => function ($q) {
                $q->andWhere(['mark_id' => $this->marks]);
            }])
            ->groupBy('recipe.id')
            ->having('COUNT(DISTINCT recipe_mark.mark_id) = ' . count($this->marks));
        }

        if (!empty($this->products)) {
            $query->joinWith(['recipeProducts' => function ($q) {
                $q->andWhere(['product_id' => $this->products]);
            }])
            ->groupBy('recipe.id')
            ->having('COUNT(DISTINCT recipe_product.product_id) = ' . count($this->products));
        }

        // Фильтрация по стандартным полям
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['user_id' => $this->user_id]);
        $query->andFilterWhere(['complexity_id' => $this->complexity_id]);
        $query->andFilterWhere(['like', 'title', $this->title]);
        $query->andFilterWhere(['like', 'time', $this->time]);

        return $dataProvider;
    }
}
