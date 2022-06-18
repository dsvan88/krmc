<?php

namespace app\models;

use app\core\Model;
use app\core\Locale;

class Games extends Model
{
    public static $gameNames = [
        'mafia' => '{{ Mafia }}',
        'poker' => '{{ Poker }}',
        'board' => '{{ Board }}',
        'cash' => '{{ Cash }}',
        'etc' => '{{ Etc }}'
    ];
}
