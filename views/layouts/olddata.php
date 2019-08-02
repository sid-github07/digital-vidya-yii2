<?php
main navbar of theme old
    NavBar::begin([
        
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            ['label' => 'Dashboard', 'url' => ['/site/index']],
            ['label' => 'Products', 'url' => ['/product']],

            Yii::$app->user->isGuest ? (
            ['label' => 'Profile', 'url' => ['/site/login']]
            ) : (
            ['label' => 'Profile', 'url' => ['/mw-users/update?id='. Yii::$app->user->identity->id]]
            ),
            Yii::$app->CustomComponents->is_super_admin()?
                ['label' => 'Admin Panel', 'url' => ['/mw-users/index']]:
                "",

            Yii::$app->user->isGuest ? (
                ['label' => 'Login', 'url' => ['/site/login']]
            ) : (
                '<li>'
                . Html::beginForm(['/site/logout'], 'post')
                . Html::submitButton(
                    'Logout (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>'
            ),

             Yii::$app->user->isGuest ? (
             ['label' => 'Contact', 'url' => ['/site/login']]
             ) : (
             ['label' => 'Contact', 'url' => ['/site/contact']]
             ),

        ],
    ]);
    NavBar::end();
    ?>


    dropdown with drill

     [
                        'label' => 'Some tools',
                        'icon' => 'share',
                        'url' => '#',
                        'items' => [
                            ['label' => 'Gii', 'icon' => 'file-code-o', 'url' => ['/gii'],],
                            ['label' => 'Debug', 'icon' => 'dashboard', 'url' => ['/debug'],],
                            [
                                'label' => 'Level One',
                                'icon' => 'circle-o',
                                'url' => '#',
                                'items' => [
                                    ['label' => 'Level Two', 'icon' => 'circle-o', 'url' => '#',],
                                    [
                                        'label' => 'Level Two',
                                        'icon' => 'circle-o',
                                        'url' => '#',
                                        'items' => [
                                            ['label' => 'Level Three', 'icon' => 'circle-o', 'url' => '#',],
                                            ['label' => 'Level Three', 'icon' => 'circle-o', 'url' => '#',],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],