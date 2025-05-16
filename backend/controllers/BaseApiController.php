<?php
namespace app\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\filters\HttpCache;
use yii\filters\ContentNegotiator;
use yii\web\Response;

class BaseApiController extends ActiveController
{
    /**
     * @var bool Включить автоматическое HTTP-кеширование
     */
    public $enableHttpCache = true;
    
    /**
     * @var int Время кеширования в секундах (по умолчанию 1 час)
     */
    public $httpCacheDuration = 3600;
    
    /**
     * @var string[] Действия для кеширования
     */
    public $cachedActions = ['view', 'index'];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        // Content Negotiator
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        
        // HTTP Cache
        if ($this->enableHttpCache) {
            $behaviors['httpCache'] = [
                'class' => HttpCache::class,
                'only' => $this->cachedActions,
                'lastModified' => [$this, 'calculateLastModified'],
                'etagSeed' => [$this, 'calculateEtagSeed'],
                'cacheControlHeader' => "public, max-age={$this->httpCacheDuration}",
            ];
        }
        
        return $behaviors;
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (!Yii::$app->user->isGuest && !Yii::$app->user->identity->isAdmin && Yii::$app->user->identity->isBlocked()) {
            Yii::$app->user->logout();
            throw new ForbiddenHttpException('Ваш аккаунт заблокирован.');
        }

        return true;
    }

    /**
     * Автоматически вычисляет Last-Modified на основе модели
     */
    public function calculateLastModified($action, $params)
    {
        if (!property_exists($this, 'modelClass') || !$this->modelClass) {
            return null;
        }

        $modelClass = $this->modelClass;
        
        try {
            if ($action === 'view' && isset($params['id'])) {
                if ($model = $modelClass::findOne($params['id'])) {
                    return $model->hasAttribute('updated_at') 
                        ? $model->updated_at 
                        : ($model->hasAttribute('created_at') ? $model->created_at : null);
                }
            } elseif ($action === 'index') {
                if ($modelClass::hasTableProperty('updated_at')) {
                    return $modelClass::find()->max('updated_at');
                } elseif ($modelClass::hasTableProperty('created_at')) {
                    return $modelClass::find()->max('created_at');
                }
            }
        } catch (\Exception $e) {
            Yii::error("Failed to calculate last modified: " . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Автоматически вычисляет ETag на основе модели
     */
    public function calculateEtagSeed($action, $params)
    {
        if (!property_exists($this, 'modelClass') || !$this->modelClass) {
            return null;
        }

        $modelClass = $this->modelClass;
        
        try {
            if ($action === 'view' && isset($params['id'])) {
                if ($model = $modelClass::findOne($params['id'])) {
                    $data = [];
                    foreach (['updated_at', 'created_at', 'id'] as $attr) {
                        if ($model->hasAttribute($attr)) {
                            $data[] = $model->$attr;
                        }
                    }
                    return $data ? md5(implode('|', $data)) : null;
                }
            } elseif ($action === 'index') {
                $count = $modelClass::find()->count();
                $last = $this->calculateLastModified($action, $params);
                return $last ? md5($count . '|' . $last) : null;
            }
        } catch (\Exception $e) {
            Yii::error("Failed to calculate ETag: " . $e->getMessage());
        }
        
        return null;
    }

    public function serializeData($data)
    {
        if (is_array($data) && isset($data['models'])) {
            return [
                'models' => parent::serializeData($data['models']),
                'pagination' => $data['pagination'],
            ];
        }
        return parent::serializeData($data);
    }
}