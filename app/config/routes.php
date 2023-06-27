<?php
/*
categories:
all = all (guests+unauthorized users); 
user = authorized (authorized users + manager + admins)
manager = level 1 admin  (manager + admins)
admin = root level admin  (admins only)
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
    'account' => [
        'redirect' => 'account/login'
    ],
    'account/login' => [
        'controller' => 'account',
        'action' => 'login',
        'access' => ['category' => 'all']
    ],
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
    'account/approve/email/{hash}' =>
    [
        'controller' => 'account',
        'action' => 'emailVerifyHash',
        'access' => ['category' => 'all']
    ],
    
    'users/list' => [
        'controller' => 'account',
        'action' => 'list',
        'access' => ['category' => 'manager']
    ],
    'users/delete/{userId}' => [
        'controller' => 'account',
        'action' => 'delete',
        'access' => ['category' => 'admin']
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
    'weeks/{weekId}' => [
        'controller' => 'weeks',
        'action' => 'show',
        'access' => ['category' => 'all']
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
    'chat/list' => [
        'controller' => 'telegramBot',
        'action' => 'chatsList',
        'access' => ['category' => 'admin'],
    ],

    'game' => [
        'controller' => 'gameTypes',
        'action' => 'index',
        'access' => ['category' => 'all'],
    ],
    'game/{game}' => [
        'controller' => 'gameTypes',
        'action' => 'game',
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


    'api/account/login' =>
    [
        'controller' => 'account',
        'action' => 'login',
        'access' => ['category' => 'all']
    ],
    'api/account/login/form' =>
    [
        'controller' => 'account',
        'action' => 'login',
        'access' => ['category' => 'all']
    ],
    'api/account/password/change' =>
    [
        'controller' => 'account',
        'action' => 'passwordChange',
        'access' => ['category' => 'user']
    ],
    'api/account/password/change/form' =>
    [
        'controller' => 'account',
        'action' => 'passwordChange',
        'access' => ['category' => 'user']
    ],
    'api/account/email/approve' =>
    [
        'controller' => 'account',
        'action' => 'emailVerifyCode',
        'access' => ['category' => 'user']
    ],
    'api/account/email/approve/form' =>
    [
        'controller' => 'account',
        'action' => 'emailApproveForm',
        'access' => ['category' => 'user']
    ],
    'api/account/telegram/approve/form' =>
    [
        'controller' => 'account',
        'action' => 'telegramApproveForm',
        'access' => ['category' => 'user']
    ],
    'api/account/profile/form' =>
    [
        'controller' => 'account',
        'action' => 'profileForm',
        'access' => ['category' => 'all']
    ],
    'api/account/profile/section' =>
    [
        'controller' => 'account',
        'action' => 'profileSection',
        'access' => ['category' => 'user']
    ],
    'api/account/profile/{userId}' =>
    [
        'controller' => 'account',
        'action' => 'profileEdit',
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
        'action' => 'forget',
        'access' => ['category' => 'all']
    ],
    'api/account/forget' =>
    [
        'controller' => 'account',
        'action' => 'forget',
        'access' => ['category' => 'all']
    ],
    'api/account/register/form' =>
    [
        'controller' => 'account',
        'action' => 'register',
        'access' => ['category' => 'all']
    ],
    'api/account/register' =>
    [
        'controller' => 'account',
        'action' => 'register',
        'access' => ['category' => 'all']
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
    // 'tech/test' =>
    // [
    //     'controller' => 'tech',
    //     'action' => 'test',
    //     'access' => ['category' => 'admin']
    // ],
    // 'tech/mail' =>
    // [
    //     'controller' => 'tech',
    //     'action' => 'sendMail',
    //     'access' => ['category' => 'admin']
    // ],
];
