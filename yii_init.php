<?php
// set it to false when in production
defined('YII_DEBUG') or define('YII_DEBUG', true);

require('vendor/autoload.php');
require('vendor/yiisoft/yii2/Yii.php');

$config = require('config/web.php');

new yii\web\Application($config);


