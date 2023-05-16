<?php

namespace app\models;

use app\core\Locale;

class GameTypes extends Pages
{
    public static $gameNames = [];
    public static $defaultGames = [
        'mafia' => 'Mafia',
        // 'poker' => 'Poker',
        'board' => 'Board',
        'nlh' => 'NLH',
        'etc' => 'Etc'
    ];
    
    public static function menu()
    {
        $games = Locale::apply(self::names());
        $result = [];
        foreach($games as $game=>$name){
            $result[] =
                [
                    'name' => $name,
                    'slug' => $game,
                    'fields' => '',
                ];
        }
        return $result;
    }

    public static function names(){
        
        if (!empty(self::$gameNames))
            return self::$gameNames;

        $games = self::findBy('type', 'game');

        if (!$games)
            return self::$defaultGames;

        $count = count($games);
        $names = [];

        for ($i=0; $i < $count; $i++) {
            $names[$games[$i]['slug']] = $games[$i]['title'];
        }
        self::$gameNames = array_merge(self::$defaultGames, $names);
        return self::$gameNames;
    }
}
