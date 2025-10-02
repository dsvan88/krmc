<?php
/*
categories:
all = all (guests+unauthorized users); 
user = authorized (authorized users + trusted + manager + admins)
trusted = level 2 users  (trusted + manager + admins)
manager = level 1 admin  (manager + admins)
admin = level 2 admin  (admins only)
root = root level admin (root only)
*/
return  [
    '' => [
        'controller' => 'pages',
        'action' => 'home',
        'access' => ['category' => 'all']
    ],
    'home' => [
        'controller' => 'pages',
        'action' => 'home',
        'access' => ['category' => 'all']
    ],
    'page/add' => [
        'controller' => 'pages',
        'action' => 'add',
        'access' => ['category' => 'manager']
    ],
    'page/{slug}' => [
        'controller' => 'pages',
        'action' => 'show',
        'access' => ['category' => 'all']
    ],
    'page/edit/{slug}' => [
        'controller' => 'pages',
        'action' => 'edit',
        'access' => ['category' => 'manager']
    ],
    'page/delete/{pageId}' => [
        'controller' => 'pages',
        'action' => 'delete',
        'access' => ['category' => 'manager']
    ],
    /*    'account' => [
        'redirect' => 'account/login'
    ],
     'account/login' => [
        'controller' => 'account',
        'action' => 'loginForm',
        'access' => ['category' => 'all']
    ], */
    'account/logout' => [
        'controller' => 'account',
        'action' => 'logout',
        'access' => ['category' => 'all']
    ],
    'account/register' => [
        'controller' => 'account',
        'action' => 'register',
        'access' => ['category' => 'all']
    ],
    'account/password-reset/{hash}' => [
        'controller' => 'account',
        'action' => 'passwordReset',
        'access' => ['category' => 'all']
    ],
    'account/profile/{userId}' =>
    [
        'controller' => 'account',
        'action' => 'show',
        'access' => ['category' => 'user']
    ],
    'account/verification/email/{hash}' =>
    [
        'controller' => 'verification',
        'action' => 'emailVerifyHash',
        'access' => ['category' => 'all']
    ],

    'users/list' => [
        'controller' => 'account',
        'action' => 'list',
        'access' => ['category' => 'manager']
    ],

    'news' => [
        'controller' => 'news',
        'action' => 'index',
        'access' => ['category' => 'all']
    ],
    'news/add' => [
        'controller' => 'news',
        'action' => 'add',
        'access' => ['category' => 'manager']
    ],
    'news/{pageNum}' => [
        'controller' => 'news',
        'action' => 'index',
        'access' => ['category' => 'all']
    ],
    'news/show/{newsId}' => [
        'controller' => 'news',
        'action' => 'show',
        'access' => ['category' => 'all']
    ],
    'news/edit/promo' => [
        'controller' => 'news',
        'action' => 'editPromo',
        'access' => ['category' => 'manager']
    ],
    'news/edit/{newsId}' => [
        'controller' => 'news',
        'action' => 'edit',
        'access' => ['category' => 'manager']
    ],
    'news/delete/{newsId}' => [
        'controller' => 'news',
        'action' => 'delete',
        'access' => ['category' => 'manager']
    ],

    'near' => [
        'controller' => 'days',
        'action' => 'near',
        'access' => ['category' => 'user']
    ],
    'week/{weekId}/day/{dayId}' => [
        'controller' => 'days',
        'action' => 'show',
        'access' => ['category' => 'all']
    ],
    'weeks' => [
        'controller' => 'weeks',
        'action' => 'show',
        'access' => ['category' => 'all']
    ],
    'weeks/add' => [
        'controller' => 'weeks',
        'action' => 'add',
        'access' => ['category' => 'manager']
    ],
    'weeks/{weekId}' => [
        'controller' => 'weeks',
        'action' => 'show',
        'access' => ['category' => 'all']
    ],
    'week/{weekId}/day/{dayId}/{bookingMode}' => [
        'controller' => 'days',
        'action' => 'selfBooking',
        'access' => ['category' => 'user']
    ],
    'settings/index' => [
        'controller' => 'settings',
        'action' => 'index',
        'access' => ['category' => 'admin']
    ],
    'settings/section/index/{section}' => [
        'controller' => 'settings',
        'action' => 'index',
        'access' => ['category' => 'admin']
    ],
    'settings/add' => [
        'controller' => 'settings',
        'action' => 'add',
        'access' => ['category' => 'admin']
    ],
    'settings/delete/{settingId}' => [
        'controller' => 'settings',
        'action' => 'delete',
        'access' => ['category' => 'admin']
    ],
    'chat/send' => [
        'controller' => 'telegramChat',
        'action' => 'send',
        'access' => ['category' => 'admin'],
    ],
    'chat/index' => [
        'controller' => 'telegramChat',
        'action' => 'index',
        'access' => ['category' => 'admin'],
    ],
    'chat/list' => [
        'controller' => 'telegramChat',
        'action' => 'list',
        'access' => ['category' => 'admin'],
    ],

    'images/index' => [
        'controller' => 'images',
        'action' => 'index',
        'access' => ['category' => 'manager'],
    ],
    'images/index/{pageToken}' => [
        'controller' => 'images',
        'action' => 'index',
        'access' => ['category' => 'manager'],
    ],

    'game' => [
        'controller' => 'gameTypes',
        'action' => 'index',
        'access' => ['category' => 'all'],
    ],
    'game/{game}' => [
        'controller' => 'gameTypes',
        'action' => 'show',
        'access' => ['category' => 'all'],
    ],
    'game/{game}/start' => [
        'controller' => 'games',
        'action' => 'prepeare',
        'access' => ['category' => 'manager'],
    ],
    'game/{game}/{gameId}' => [
        'controller' => 'games',
        'action' => 'play',
        'access' => ['category' => 'manager'],
    ],
    'game/show/{game}/{gameId}' => [
        'controller' => 'games',
        'action' => 'show',
        'access' => ['category' => 'all'],
    ],

    'activity/history' => [
        'controller' => 'games',
        'action' => 'history',
        'access' => ['category' => 'trusted'],
    ],
    'activity/history/{weekId}' => [
        'controller' => 'games',
        'action' => 'history',
        'access' => ['category' => 'trusted'],
    ],
    'activity/rating' => [
        'controller' => 'games',
        'action' => 'rating',
        'access' => ['category' => 'trusted'],
    ],
    'activity/rating/{weekId}' => [
        'controller' => 'games',
        'action' => 'rating',
        'access' => ['category' => 'trusted'],
    ],
    'activity/peek' => [
        'controller' => 'games',
        'action' => 'peek',
        'access' => ['category' => 'trusted'],
    ],
    'activity/play' => [
        'redirect' => 'game/mafia/start',
    ],
    'activity/last' => [
        'controller' => 'games',
        'action' => 'last',
        'access' => ['category' => 'all'],
    ],
];
