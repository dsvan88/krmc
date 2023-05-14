<?
namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\core\Locale;
use app\models\Contacts;
use app\models\Users;

class NickCommand extends ChatCommand {
    public static function description(){
        return self::locale('<u>/nick Your nickname</u> (Cyrillic) <i>// Register your nickname</i>');
    }
    public static function execute(array $arguments=[]){
        if (!empty(self::$requester)) {
            return [false, self::locale(['string' => '{{ Tg_Command_Name_Already_Set }}', 'vars' => [self::$requester['name']]])];
        }
        
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
        
        $telegramId = self::$message['message']['from']['id'];
        $telegram = self::$message['message']['from']['username'];
        
        $userExistsData = Users::getDataByName($username);
        
        if (empty($userExistsData['id'])) {
            $id = Users::add($username);

            Contacts::add([
                'user_id' => $id,
                'type' => 'telegramid',
                'contact' => $telegramId,
            ]);
            if (!empty($telegram)){
                Contacts::add([
                    'user_id' => $id,
                    'type' => 'telegram',
                    'contact' => $telegram,
                ]);
            }

            return [true, self::locale(['string' => '{{ Tg_Command_Name_Save_Success }}', 'vars' => [$username]])];
        }
        
        if ($userExistsData['contacts']['telegramid'] !== '') {
            if ($userExistsData['contacts']['telegramid'] !== $telegramId) {
                return [false, self::locale(['string' => '{{ Tg_Command_Name_Already_Set_By_Other }}', 'vars' => [$username]])];
            }
            return [false, self::locale('{{ Tg_Command_Name_You_Have_One }}')];
        }
        
        $userExistsData['contacts']['telegramid'] = $telegramId;
        $userExistsData['contacts']['telegram'] = $telegram;
        $userExistsData['contacts']['email'] = isset($userExistsData['contacts']['email']) ? $userExistsData['contacts']['email'] : '';
        
        Users::edit(['contacts' => $userExistsData['contacts']], ['id' => $userExistsData['id']]);
        
        $oldTgContacts = Contacts::findBy('user_id', $userExistsData['id']);
        if (empty($oldTgContacts)){
            Contacts::add([
                'user_id' => $userExistsData['id'],
                'type' => 'telegramid',
                'contact' => $telegramId,
            ]);
            if (!empty($telegram)){
                Contacts::add([
                    'user_id' => $userExistsData['id'],
                    'type' => 'telegram',
                    'contact' => $telegram,
                ]);
            }
        }
        return [true, self::locale(['string' => '{{ Tg_Command_Name_Save_Success }}', 'vars' => [$username]])];
    }
}