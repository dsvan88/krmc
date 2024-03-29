<?php

namespace app\models;

use app\core\Locale;

class GameTypes extends Pages
{
    public static $gameNames = [];
    public static $defaultGames = [
        'mafia' => 'Mafia',
        'board' => 'Board',
        'nlh' => 'NLH',
        'etc' => 'Etc'
    ];

    public static function menu()
    {
        $games = Locale::apply(self::names());
        $result = [];
        foreach ($games as $game => $name) {
            $result[] =
                [
                    'name' => $name,
                    'slug' => $game,
                    'fields' => '',
                ];
        }
        return $result;
    }

    public static function names()
    {

        if (!empty(self::$gameNames))
            return self::$gameNames;

        $games = self::findBy('type', 'game');

        if (!$games)
            return self::$defaultGames;

        $count = count($games);
        $names = [];
        $lang = Locale::$langCode;
        for ($i = 0; $i < $count; $i++) {
            if (!empty($games[$i]['date_delete'])) continue;
            if (!empty($games[$i]['lang']) && $games[$i]['lang'] !== $lang) continue;
            $names[$games[$i]['slug']] = $games[$i]['title'];
        }
        self::$gameNames = array_merge(self::$defaultGames, $names);
        return self::$gameNames;
    }
    public static function getKeywords()
    {
        $games = self::findBy('type', 'game');

        if (!$games)
            return false;

        $keywords = [];

        $count = count($games);
        for ($i = 0; $i < $count; $i++) {
            if (empty($games[$i]['data'])) continue;
            $keywords[$games[$i]['slug']] = $games[$i]['data']['keywords'];
        }
        return empty($keywords) ? false : $keywords;
    }
}
