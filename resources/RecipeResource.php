<?php
namespace app\resources;

use yii\base\Model;
use yii\web\Link; // represents a link object as defined in JSON Hypermedia API Language.
use yii\web\Linkable;
use yii\helpers\Url;

class RecipeResource extends Model implements Linkable
{
    public $id;
    public $user_id;
    public $status_id;
    public $private_id;
    public $created_at;
    public $title;
    public $photo;
    public $description;
    public $saved;
    public $likes;

    public function fields()
    {
        return ['id', 'email'];
    }

    public function extraFields()
    {
        return ['profile'];
    }

    public function getLinks()
    {
        return [
            Link::REL_SELF => [
                'method' => 'GET',
                'href' => Url::to(['recipe/view', 'id' => $this->id], true),
            ],
            'edit' => [
                'method' => 'PATCH',
                'href' => Url::to(['recipe/update', 'id' => $this->id], true),
            ],
            'delete' => [
                'method' => 'DELETE',
                'href' => Url::to(['recipe/delete', 'id' => $this->id], true),
            ],
            // 'index' => [
            //     'method' => 'GET',
            //     'href' => Url::to(['recipe/index'], true),
            // ],
        ];
    }
}