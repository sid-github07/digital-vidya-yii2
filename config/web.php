<?php

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'timeZone' => 'Asia/Calcutta',
    'components' => [

        // 'view' => [
        //  'theme' => [
        //      'pathMap' => [
        //         '@app/views' => '@vendor/dmstr/yii2-adminlte-asset/example-views/yiisoft/yii2-app'
        //      ],
        //  ],
        // ],

        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'adfdsfdsf54d56s4fdsfdsfdsnvfdsh89',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.gmail.com',
                'username' => 'chintand@whizlabs.com',
                'password' => 'jfds$%!45UH', 
                'port' => '587',
                'encryption' => 'tls',                                  
            ], 
        ], 
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,

        'urlManager' => [
          //    'baseUrl' => '/assist/',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        'assetManager' => [
            'class' => 'yii\web\AssetManager',
            'forceCopy' => true,          
        ],
        /*'assetManager' => [
            'baseUrl' => '@web/assist/assets',
         ],*/
//          custom component define
        'CustomComponents' => [
            'class' => 'app\components\CustomComponents',
        ],

        /*'JGoogleAPI' => array(
            'class' => 'ext.JGoogleAPI.JGoogleAPI',
            //Default authentication type to be used by the extension
            'defaultAuthenticationType'=>'serviceAPI',
            
            //Account type Authentication data
            'serviceAPI' => array(
                'clientId' => '1021335287805-j3tgohadl101v100n8s6rlitnr1ja5n0.apps.googleusercontent.com',
                'clientEmail' => 'chintand@whizlabs.com',
                'keyFilePath' => 'http://dev.digitalvidya.com/assist/uploads/google_key/dv-assist-1117b6d67442.json',
            ),
          
            'simpleApiKey' => 'AIzaSyBLRqpUr0grv0mh59jafUcJ6RpIciDn4f0',
            
            //Scopes needed to access the API data defined by authentication type
            'scopes' => array(
                'serviceAPI' => array(
                    'drive' => array(
                        'https://www.googleapis.com/auth/drive.file',
                    ),
                ),
                'webappAPI' => array(
                    'drive' => array(
                        'https://www.googleapis.com/auth/drive.file',
                    ),
                ),
            ),
            //Use objects when retriving data from api if true or an array if false
            'useObjects'=>true,
        ),*/

    ],

    'params' => $params,
    'modules' => [
        'gridview' =>  [
             'class' => '\kartik\grid\Module'
             // enter optional module parameters below - only if you need to  
             // use your own export download action or custom translation 
             // message source
             // 'downloadAction' => 'gridview/export/download',
             // 'i18n' => []
         ]
     ],
    'on beforeAction' => function ($event) {
        if (!Yii::$app->user->isGuest) {
            if(Yii::$app->getUser()->identity->status == 0){
                Yii::$app->user->logout();
                return Yii::$app->getResponse()->redirect(Yii::$app->homeUrl);
            }
        }
    },

];


if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
