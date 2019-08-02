<?php

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
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
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
