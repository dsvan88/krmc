<?
namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\core\Locale;
use app\models\Users;

class NewuserCommand extends ChatCommand {
    public static function description(){
        return self::locale('<u>/nick Your nickname</u> (Cyrillic) <i>// Register your nickname</i>');
    }
    public static function execute(array $arguments=[]){
        $username = '';
        foreach ($arguments as $string) {
            $username .= Locale::mb_ucfirst($string) . ' ';
        }
        $username = mb_substr($username, 0, -1, 'UTF-8');
        
        if (mb_strlen(trim($username), 'UTF-8') < 2) {
            return [false, self::locale('{{ Tg_Command_Name_Too_Short }}')];
        }
        if (preg_match('/([^а-яА-ЯрРсСтТуУфФчЧхХШшЩщЪъЫыЬьЭэЮюЄєІіЇїҐґ .0-9])/', $username) === 1) {
            return [false, self::locale('{{ Tg_Command_Name_Wrong_Format }}')];
        }
        
        if (Users::getId($username) > 0) {
            return [false, self::locale(['string' => '{{ Tg_Command_New_User_Already_Set }}', 'vars' => [$username]])];
        }
        
        Users::add($username);

        return [true, self::locale(['string' => '{{ Tg_Command_New_User_Save_Success }}', 'vars' => [$username]])];
    }
}