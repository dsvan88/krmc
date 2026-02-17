<?php

namespace app\Repositories;

use app\core\Locale;
use app\models\Games;

class GameRepository
{
    public static $bools = ['courtAfterFouls'];
    public static $strings = ['voteType'];
    public static $encoded = ['gamePass'];
    public static function formConfig(array $data): array
    {
        $config = [];
        foreach ($data as $key => $value) {
            if (in_array($key, ['player', 'manager', 'role'])) continue;
            $key = Locale::camelize($key);

            if (in_array($key, self::$bools)) {
                $config[$key] = (bool) $value;
                continue;
            }
            if (in_array($key, self::$strings)) {
                $config[$key] = $value;
                continue;
            }
            if (in_array($key, self::$encoded)) {
                $config[$key] = base64_encode($value);
                continue;
            }
            if (is_array($value)) {
                $config[$key] = self::formConfig($value);
                continue;
            }
            $config[$key] = self::floatValues($value);
        }
        return $config;
    }
    public static function floatValues(string $value)
    {
        if (empty($value)) return $value;
        if (!strpos($value, ', ')) return (float) str_replace(',', '.', $value);

        $array = explode(', ', $value);
        $count = count($array);
        for ($x = 0; $x < $count; $x++) {
            $array[$x] = (float) str_replace(',', '.', $array[$x]);
        }
        return $array;
    }
    public static function formResult(array $state){
        $game = Games::find($state['gameId']);
        $game['players'] = json_decode($game['players'], true);
        $countPlayers = count($state['players']);
        for ($playerId=0; $playerId < $countPlayers; $playerId++) { 
            $state['players'][$playerId]['index'] = $playerId;
            $state['players'][$playerId]['id'] = $game['players'][$playerId]['id'];
            $state['players'][$playerId]['voted'] = $state['daysCount'] > 0 ? array_fill(0, $state['daysCount'], '') : [];
        }
        for ($day=0; $day < $state['daysCount']; $day++) { 
            $voting = -1;
            while(!empty($state['courtLog'][$day][++$voting])){
                foreach($state['courtLog'][$day][$voting] as $index=>$defendand){
                    foreach($defendand['voted'] as $voted){
                        $state['players'][$voted]['voted'][$day] .= ($defendand['id']+1).', ';
                    }
                }
            }
        }
        return [json_encode($state, JSON_UNESCAPED_UNICODE), json_encode($state['players'], JSON_UNESCAPED_UNICODE)];
    }
}
