<?php
/*
categories:
all = all (guests+unauthorized users); 
user = authorized (authorized users + trusted + manager + admins)
trusted = level 2 users  (trusted + manager + admins)
activist = level 3 users  (trusted + activist + manager + admins)
manager = level 1 admin  (manager + admins)
admin = level 2 admin  (admins only)
root = root level admin (root only)
*/
return  [
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
    'tech/backup/save' =>
    [
        'controller' => 'tech',
        'action' => 'backupSave',
        'access' => ['category' => 'all']
    ],
    'tech/restore' =>
    [
        'controller' => 'tech',
        'action' => 'restore',
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
        'access' => ['category' => 'all']
    ],
];
