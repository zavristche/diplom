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
    // public array $marks;
    // public array $products;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'status_id', 'private_id', 'complexity_id', 'saved', 'likes'], 'integer'],
            [['created_at', 'title', 'time', 'photo', 'description'], 'safe'],
            // [['marks', 'products'], 'safe'],
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
            'user', 'recipeMarks', 'recipeProducts', 'steps', 'status', 'private', 'recipeReactions', 'collectionRecipes'
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        if (!($this->load($params, '') && $this->validate())) {
            return $dataProvider;
        }

        // Фильтрация по стандартным полям
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['user_id' => $this->user_id]);
        $query->andFilterWhere(['complexity_id' => $this->complexity_id]);
        $query->andFilterWhere(['like', 'title', $this->title]);

        // Фильтр по marks (JSON)
        if (!empty($params['marks']) && is_array($params['marks'])) {
            $jsonConditions = [];
            foreach ($params['marks'] as $mark) {
                if (isset($mark['id'])) {
                    $jsonConditions[] = "JSON_CONTAINS(marks, :mark" . $mark['id'] . ", '$')";
                }
            }
            if (!empty($jsonConditions)) {
                $query->andWhere(new Expression(implode(' OR ', $jsonConditions), array_reduce($params['marks'], function ($carry, $mark) {
                    $carry[":mark" . $mark['id']] = json_encode(['id' => (int)$mark['id']]);
                    return $carry;
                }, [])));
            }
        }

        return $dataProvider;
    }

    // public function search($params)
    // {
    //     $query = Recipe::find()->with([
    //         'user', 'recipeMarks', 'recipeProducts', 'steps', 'status', 'private', 'recipeReactions', 'collectionRecipes'
    //     ]);

    //     $dataProvider = new ActiveDataProvider([
    //         'query' => $query,
    //         'pagination' => ['pageSize' => 10],
    //         'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
    //     ]);

    //     if (!($this->load($params) && $this->validate())) {
    //         return $dataProvider;
    //     }

    //     // Фильтрация по параметрам
    //     $query->andFilterWhere(['id' => $this->id]);
    //     $query->andFilterWhere(['user_id' => $this->user_id]);
    //     $query->andFilterWhere(['complexity_id' => $this->complexity_id]);
    //     // $query->andFilterWhere(['user_id' => $this->user_id]);
    //     // $query->andFilterWhere(['like', 'title', $this->title]);

    //     if (!empty($params['marks']) && is_array($params['marks'])) {
    //         foreach ($params['marks'] as $mark) {
    //             if (isset($mark['id'])) {
    //                 $query->andWhere(new Expression("JSON_CONTAINS(marks, :mark, '$')", [
    //                     ':mark' => json_encode(['id' => (int)$mark['id']])
    //                 ]));
    //             }
    //         }
    //     }

    //     return $dataProvider;
    // }

    // public function search($params)
    // {
    //     $query = Recipe::find();

    //     // Если передан title, фильтруем по названию
    //     if (!empty($params['title'])) {
    //         $query->andFilterWhere(['like', 'title', $params['title']]);
    //     }

    //     // Если передан marks (ищем по JSON)
    //     if (!empty($params['marks']) && is_array($params['marks'])) {
    //         foreach ($params['marks'] as $mark) {
    //             if (isset($mark['id'])) {
    //                 $query->andWhere(new Expression("JSON_CONTAINS(marks, :mark, '$')", [
    //                     ':mark' => json_encode(['id' => (int)$mark['id']])
    //                 ]));
    //             }
    //         }
    //     }

    //     return new ActiveDataProvider([
    //         'query' => $query,
    //     ]);
    // }


    // public function search($params)
    // {
    //     $query = Recipe::find();
    
    //     // Добавляем жадную загрузку связей
    //     $query->joinWith(['recipeMarks', 'recipeProducts']);
    
    //     $dataProvider = new ActiveDataProvider([
    //         'query' => $query,
    //     ]);
    
    //     $this->load($params);
    
    //     if (!$this->validate()) {
    //         // Если валидация не прошла, возвращаем пустой результат
    //         return $dataProvider;
    //     }
    
    //     // Фильтрация по основным полям
    //     $query->andFilterWhere([
    //         'id' => $this->id,
    //         'user_id' => $this->user_id,
    //         'status_id' => $this->status_id,
    //         'private_id' => $this->private_id,
    //         'complexity_id' => $this->complexity_id,
    //         'created_at' => $this->created_at,
    //         'saved' => $this->saved,
    //         'likes' => $this->likes,
    //     ]);
    
    //     $query->andFilterWhere(['like', 'title', $this->title])
    //         ->andFilterWhere(['like', 'time', $this->time])
    //         ->andFilterWhere(['like', 'photo', $this->photo])
    //         ->andFilterWhere(['like', 'description', $this->description]);
    
    //     // Фильтрация по marks (рецепты, которые имеют указанные marks)
    //     if (!empty($this->marks)) {
    //         // Преобразуем массив объектов в массив ID
    //         $markIds = array_map(function($mark) {
    //             return $mark['id'];
    //         }, $this->marks);
    //         $query->andFilterWhere(['recipe_mark.mark_id' => $markIds]);
    //     }
    
    //     // Фильтрация по products (рецепты, которые имеют указанные products)
    //     if (!empty($this->products)) {
    //         // Преобразуем массив объектов в массив ID
    //         $productIds = array_map(function($product) {
    //             return $product['id'];
    //         }, $this->products);
    //         $query->andFilterWhere(['recipe_product.product_id' => $productIds]);
    //     }
    
    //     return $dataProvider;
    // }
}
