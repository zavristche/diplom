<?php
namespace app\resources;

use yii\base\Model;
use yii\web\Link; // represents a link object as defined in JSON Hypermedia API Language.
use yii\web\Linkable;
use yii\helpers\Url;

class RecipeResource extends Model implements Linkable
{
    public $user_id;
    public $email;


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
            Link::REL_SELF => Url::to(['recipe/view', 'id' => $this->id], true),
            'edit' => Url::to(['recipe/update', 'id' => $this->id], true),
            // 'profile' => Url::to(['user/profile/view', 'id' => $this->id], true),
            'index' => Url::to(['recipes'], true),
        ];
    }
}