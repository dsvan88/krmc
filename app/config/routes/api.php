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
    'api/verification/telegram/hmac' =>
    [
        'controller' => 'verification',
        'action' => 'hmac',
        'access' => ['category' => 'all']
    ],

    'api/telegram/webhook' =>
    [
        'controller' => 'telegramBot',
        'action' => 'webhook',
        'access' => ['category' => 'all']
    ],


    'api/image/add' =>
    [
        'controller' => 'images',
        'action' => 'add',
        'access' => ['category' => 'manager']
    ],

];
