<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="/web/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/web/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/web/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

</head>

<?php
$isDev = YII_ENV_DEV;

if ($isDev) {
    // Для разработки подключаем сервер Vite
    $this->registerJsFile('http://localhost:5173/@vite/client', ['type' => 'module']);
    $this->registerJsFile('http://localhost:5173/src/main.js', ['type' => 'module']);
} else {
    // Для продакшена подключаем собранные файлы
    $this->registerCssFile('/assets/frontend/assets/index.css');
    $this->registerJsFile('/assets/frontend/assets/index.js', ['type' => 'module']);
}
?>
<?php $this->beginBody() ?>
    <div id="app"> </div>
<?php $this->endBody() ?>
</html>
<?php $this->endPage() ?>