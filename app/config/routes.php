<?php
/*
categories:
all = all (guests+unauthorized users); 
users = authorized (authorized users + manager + admins)
manager = level 1 admin  (manager + admins)
admin = root level admin  (admins only)
*/
return  [
    '' => [
        'controller' => 'pages',
        'action' => 'index',
        'access' => ['category' => 'all']
    ],
    'index' => [
        'controller' => 'pages',
        'action' => 'index',
        'access' => ['category' => 'all']
    ],
    'page/add' => [
        'controller' => 'pages',
        'action' => 'add',
        'access' => ['category' => 'manager']
    ],
    'page/{shortName}' => [
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
    'users/list' => [
        'controller' => 'account',
        'action' => 'usersList',
        'access' => ['category' => 'manager']
    ],
    'users/delete/{userId}' => [
        'controller' => 'account',
        'action' => 'delete',
        'access' => ['category' => 'admin']
    ],

    'news' => [
        'controller' => 'news',
        'action' => 'showList',
        'access' => ['category' => 'all']
    ],
    'news/add' => [
        'controller' => 'news',
        'action' => 'addItem',
        'access' => ['category' => 'manager']
    ],
    'news/{pageNum}' => [
        'controller' => 'news',
        'action' => 'showList',
        'access' => ['category' => 'all']
    ],
    'news/show/{newsId}' => [
        'controller' => 'news',
        'action' => 'showItem',
        'access' => ['category' => 'all']
    ],
    'news/edit/promo' => [
        'controller' => 'news',
        'action' => 'editPromoItem',
        'access' => ['category' => 'manager']
    ],
    'news/edit/{newsId}' => [
        'controller' => 'news',
        'action' => 'editItem',
        'access' => ['category' => 'manager']
    ],
    'news/delete/{newsId}' => [
        'controller' => 'news',
        'action' => 'deleteItem',
        'access' => ['category' => 'manager']
    ],

    'days{dayId}/w{weekId}' => [
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
    'settings/list' => [
        'controller' => 'settings',
        'action' => 'list',
        'access' => ['category' => 'admin']
    ],
    'settings/add' => [
        'controller' => 'settings',
        'action' => 'add',
        'access' => ['category' => 'admin']
    ],
    'settings/edit/{settingId}' => [
        'controller' => 'settings',
        'action' => 'edit',
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
        'access' => ['category' => 'admin']
    ],
    'chat/list' => [
        'controller' => 'telegramBot',
        'action' => 'chatsList',
        'access' => ['category' => 'admin']
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
        'action' => 'loginForm',
        'access' => ['category' => 'all']
    ],
    'api/account/profile' =>
    [
        'controller' => 'account',
        'action' => 'profileEdit',
        'access' => ['category' => 'all']
    ],
    'api/account/profile/form' =>
    [
        'controller' => 'account',
        'action' => 'profileForm',
        'access' => ['category' => 'all']
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
    'api/account/forget' =>
    [
        'controller' => 'account',
        'action' => 'forget',
        'access' => ['category' => 'all']
    ],
    'api/account/forget/form' =>
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
        'action' => 'register',
        'access' => ['category' => 'all']
    ],
    'api/users/account/set/nickname' =>
    [
        'controller' => 'account',
        'action' => 'setNickname',
        'access' => ['category' => 'manager']
    ],
    'api/account/set/nickname/form' =>
    [
        'controller' => 'account',
        'action' => 'setNicknameForm',
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
    'tech/migration' =>
    [
        'controller' => 'tech',
        'action' => 'migration',
        'access' => ['category' => 'all']
    ],
];
