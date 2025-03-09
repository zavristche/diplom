<?php

namespace app\models\user;

use app\models\Role;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\user\User;
use Yii;

/**
 * UserSearch represents the model behind the search form of `app\models\user\User`.
 */
class UserSearch extends User
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'role_id', 'private_id'], 'integer'],
            [['last_active', 'status', 'name', 'surname', 'login', 'email', 'password', 'avatar', 'photo_header', 'auth_key'], 'safe'],
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
        $query = User::find();
    
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
    
        $this->load($params, '');
    
        if (!$this->validate()) {
            return $dataProvider;
        }
    
        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'surname', $this->surname])
            ->andFilterWhere(['like', 'login', $this->login])
            ->andFilterWhere(['!=', 'role_id', Role::getOne('admin')]);
    
        return $dataProvider;
    }
    
}
