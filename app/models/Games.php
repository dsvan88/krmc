<?php

namespace app\models;

use app\core\Model;
use app\core\Locale;
use app\core\Tech;
use app\Repositories\GameRepository;

class Games extends Model
{

    public static $table = SQL_TBL_GAMES;
    public static $foreign = [ 'users' => Users::class];
    public static $jsonFields = ['players', 'state', 'prevstates'];

    public static function create($post)
    {
        $manager = Users::getDataByName(Users::formatName($post['manager']));
        $state = [
            'config' => GameRepository::formConfig($post)
        ];
        $data = [
            'week_id' => Weeks::currentId(),
            'day_id' => Days::current(),
            'manager' => $manager['id'],
            'state' => json_encode($state, JSON_UNESCAPED_UNICODE),
            'players' => json_encode(Users::assingIds($post['player']), JSON_UNESCAPED_UNICODE),
            'started_at' => $_SERVER['REQUEST_TIME'],
        ];
        
        if (!empty($post['default'])){
            $setting = [
                'type' => 'mafia_config',
                'slug' => 'mafia-config',
                'options' => $state['config'],
            ];
            Settings::save($setting);
        }
        return self::insert($data);
    }

    public static function save($post, $id)
    {
        $data = [
            'state' => $post['state'],
            'prevstates' => $post['prevstates'],
        ];
        $state = json_decode($data['state'], true);
        if (!empty($state['winners'])){
            [$data['state'], $data['players']] = GameRepository::formResult($state);
            $data['win'] = $state['winners'];
        }
        self::update($data, ['id' => $id]);
    }
    public static function load(int $gameId){
        $gameData = Games::find($gameId);

        $gameData['players'] = Users::addNames($gameData['players']);
        
        $gameData['players'] = json_encode($gameData['players'], JSON_UNESCAPED_UNICODE);
        return $gameData;
    }
    public static function init()
    {
        $table = self::$table;
        foreach(self::$foreign as $key=>$class){
            $$key = $class::$table;
        }

        self::query(
            "CREATE TABLE IF NOT EXISTS $table (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                week_id INT NOT NULL DEFAULT '0',
                day_id INT NOT NULL DEFAULT '0',
                type CHARACTER VARYING(30) NOT NULL DEFAULT 'mafia',
                win INT NOT NULL DEFAULT '0',
                manager INT NOT NULL DEFAULT '1',
                players JSON DEFAULT NULL,
                state JSON DEFAULT NULL,
                prevstates JSON DEFAULT NULL,
                started_at INT NOT NULL DEFAULT '0',
                CONSTRAINT fk_game_manager
                    FOREIGN KEY(manager) 
                    REFERENCES $users(id)
                    ON DELETE SET DEFAULT
            );"
        );
    }
}
