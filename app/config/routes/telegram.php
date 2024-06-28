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
    'telegram/auth' =>
    [
        'controller' => 'telegramApp',
        'action' => 'auth',
        'access' => ['category' => 'all']
    ],
];
