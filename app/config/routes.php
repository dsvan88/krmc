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
    'page/edit/{pageId}' => [
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
        'controller' => 'telegramBot',
        'action' => 'send',
        'access' => ['category' => 'admin'],
    ],
    'chat/index' => [
        'controller' => 'telegramBot',
        'action' => 'index',
        'access' => ['category' => 'admin'],
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


    'api/account/login' =>
    [
        'controller' => 'account',
        'action' => 'loginForm',
        'access' => ['category' => 'all']
    ],
    'api/account/login/form' =>
    [
        'controller' => 'account',
        'action' => 'loginForm',
        'access' => ['category' => 'all']
    ],
    'api/account/password/change' =>
    [
        'controller' => 'account',
        'action' => 'passwordChangeForm',
        'access' => ['category' => 'user']
    ],
    'api/account/password/change/form' =>
    [
        'controller' => 'account',
        'action' => 'passwordChangeForm',
        'access' => ['category' => 'user']
    ],
    'api/account/doubles/{userId}' =>
    [
        'controller' => 'account',
        'action' => 'doublesForm',
        'access' => ['category' => 'admin']
    ],
    'api/account/doubles/{userId}/form' =>
    [
        'controller' => 'account',
        'action' => 'doublesForm',
        'access' => ['category' => 'admin']
    ],
    'api/account/profile/section' =>
    [
        'controller' => 'account',
        'action' => 'profileSection',
        'access' => ['category' => 'user']
    ],
    'api/account/profile/section/edit' =>
    [
        'controller' => 'account',
        'action' => 'profileSectionEditForm',
        'access' => ['category' => 'user']
    ],
    'api/account/profile/edit/{userId}/{section}' =>
    [
        'controller' => 'account',
        'action' => 'profileSectionEdit',
        'access' => ['category' => 'user']
    ],
    'api/account/profile/avatar/form' =>
    [
        'controller' => 'account',
        'action' => 'profileAvatarForm',
        'access' => ['category' => 'all']
    ],
    'api/account/profile/avatar/recrop/form' =>
    [
        'controller' => 'account',
        'action' => 'profileAvatarRecropForm',
        'access' => ['category' => 'all']
    ],
    'api/account/forget/form' =>
    [
        'controller' => 'account',
        'action' => 'forgetForm',
        'access' => ['category' => 'all']
    ],
    'api/account/forget' =>
    [
        'controller' => 'account',
        'action' => 'forgetForm',
        'access' => ['category' => 'all']
    ],
    'api/account/register/form' =>
    [
        'controller' => 'account',
        'action' => 'registerForm',
        'access' => ['category' => 'all']
    ],
    'api/account/register' =>
    [
        'controller' => 'account',
        'action' => 'registerForm',
        'access' => ['category' => 'all']
    ],
    'api/account/ban/form' =>
    [
        'controller' => 'account',
        'action' => 'banForm',
        'access' => ['category' => 'admin']
    ],
    'api/account/ban/{userId}' =>
    [
        'controller' => 'account',
        'action' => 'ban',
        'access' => ['category' => 'admin']
    ],
    'api/account/set/nickname/form' =>
    [
        'controller' => 'account',
        'action' => 'setNicknameForm',
        'access' => ['category' => 'manager']
    ],
    'api/account/set/nickname/{chatId}' =>
    [
        'controller' => 'account',
        'action' => 'setNickname',
        'access' => ['category' => 'manager']
    ],
    'api/account/dummy/rename/form' =>
    [
        'controller' => 'account',
        'action' => 'dummyRenameForm',
        'access' => ['category' => 'manager']
    ],
    'api/account/delete' => [
        'controller' => 'account',
        'action' => 'delete',
        'access' => ['category' => 'admin']
    ],

    'api/autocomplete/users-names' =>
    [
        'controller' => 'autocomplete',
        'action' => 'users',
        'access' => ['category' => 'manager']
    ],
    'api/participant-field-get' =>
    [
        'controller' => 'autocomplete',
        'action' => 'participantField',
        'access' => ['category' => 'manager']
    ],
    'api/add/participant/form' =>
    [
        'controller' => 'account',
        'action' => 'addParticipantForm',
        'access' => ['category' => 'manager']
    ],
    'api/game/add-participant' =>
    [
        'controller' => 'account',
        'action' => 'addParticipant',
        'access' => ['category' => 'manager']
    ],
    'api/game/remove-participant' =>
    [
        'controller' => 'account',
        'action' => 'removeParticipant',
        'access' => ['category' => 'manager']
    ],
    'api/game/{gameId}' =>
    [
        'controller' => 'games',
        'action' => 'load',
        'access' => ['category' => 'manager']
    ],
    'api/game/save/{gameId}' =>
    [
        'controller' => 'games',
        'action' => 'save',
        'access' => ['category' => 'manager']
    ],
    'api/game/history/{gameId}' =>
    [
        'controller' => 'games',
        'action' => 'historyItem',
        'access' => ['category' => 'trusted']
    ],
    
    'api/settings/edit/form' => [
        'controller' => 'settings',
        'action' => 'editForm',
        'access' => ['category' => 'admin']
    ],
    'api/settings/edit/{settingId}' => [
        'controller' => 'settings',
        'action' => 'edit',
        'access' => ['category' => 'admin']
    ],

    'account/verification/email/{hash}' =>
    [
        'controller' => 'verification',
        'action' => 'emailVerifyHash',
        'access' => ['category' => 'all']
    ],
    'api/verification/email' =>
    [
        'controller' => 'verification',
        'action' => 'emailVerification',
        'access' => ['category' => 'user']
    ],
    'api/verification/register/name' =>
    [
        'controller' => 'verification',
        'action' => 'registerName',
        'access' => ['category' => 'all']
    ],
    'api/verification/root' =>
    [
        'controller' => 'verification',
        'action' => 'root',
        'access' => ['category' => 'admin']
    ],

    'api/telegram/webhook' =>
    [
        'controller' => 'telegramBot',
        'action' => 'webhook',
        'access' => ['category' => 'all']
    ],

    'tech/sql' =>
    [
        'controller' => 'tech',
        'action' => 'sql',
        'access' => ['category' => 'admin']
    ],
    'tech/backup' =>
    [
        'controller' => 'tech',
        'action' => 'backup',
        'access' => ['category' => 'admin']
    ],
    'tech/migration' =>
    [
        'controller' => 'tech',
        'action' => 'migration',
        'access' => ['category' => 'admin']
    ],
    'tech/dbrebuild' =>
    [
        'controller' => 'tech',
        'action' => 'dbrebuild',
        'access' => ['category' => 'admin']
    ],
    'tech/selftest/telegram' =>
    [
        'controller' => 'tech',
        'action' => 'selfTestTelegram',
        'access' => ['category' => 'admin']
    ],
    'tech/test' =>
    [
        'controller' => 'tech',
        'action' => 'test',
        'access' => ['category' => 'admin']
    ],
    // 'tech/mail' =>
    // [
    //     'controller' => 'tech',
    //     'action' => 'sendMail',
    //     'access' => ['category' => 'admin']
    // ],
];
