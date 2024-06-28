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
    'telegram/app' =>
    [
        'controller' => 'telegramApp',
        'action' => 'home',
        'access' => ['category' => 'all']
    ],
];
