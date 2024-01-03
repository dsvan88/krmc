<?

namespace app\Controllers;

use app\core\Controller;
use app\core\Sender;
use app\core\View;
use app\models\Settings;
use app\models\TelegramChats;

class telegramChatController extends Controller
{
    public static function indexAction()
    {
        $chatsData = TelegramChats::getChatsList();
        $chatsData = TelegramChats::nicknames($chatsData);
        $vars = [
            'title' => '{{ Chats_List_Title }}',
            'chatsData' => $chatsData,
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        View::render();
    }
    public static function sendAction()
    {
        if (!empty($_POST)) {

            $message =  preg_replace('/(<((?!b|u|s|strong|em|i|\/b|\/u|\/s|\/strong|\/em|\/i)[^>]+)>)/i', '', str_replace(['<br />', '<br/>', '<br>', '</p>'], "\n", trim($_POST['html'])));
            if (is_numeric($_POST['target'])) {
                $targets = $_POST['target'];
            } else {
                switch ($_POST['target']) {
                    case 'main':
                        $targets = Settings::getMainTelegramId();
                        break;
                    case 'groups':
                        $chats = TelegramChats::getGroupChatsList();
                        $count = count($chats);
                        for ($i = 0; $i < $count; $i++) {
                            $targets[] = $chats[$i]['uid'];
                        }
                        break;
                    default:
                        $chats = TelegramChats::getChatsList();
                        $count = count($chats);
                        for ($i = 0; $i < $count; $i++) {
                            $targets[] = $chats[$i]['uid'];
                        }
                        break;
                }
            }
            if ($_FILES['logo']['size'] > 0 && $_FILES['logo']['size'] < 10485760) { // 10 Мб = 10 * 1024 *1024
                $result = Sender::photo($targets, $message, $_FILES['logo']['tmp_name']);
            } else {
                $result = Sender::message($targets, $message);
            }
            $message = 'Success!';
            if (!$result[0]['ok']) {
                $message = 'Fail!';
            }

            View::message(['error' => 0, 'message' => $message]);
        }
        $groupChats = TelegramChats::getGroupChatsList();
        $directChats = TelegramChats::getDirectChats();
        $chats = array_merge($groupChats, $directChats);
        $vars = [
            'title' => 'Send message',
            'texts' => [
                'blockTitle' => 'Send message',
                'submitTitle' => 'Send',
                'sendAll' => '{{ Send_To_All }}',
                'sendGroups' => '{{ Send_To_Groups }}',
                'sendMain' => '{{ Send_To_Main }}',
            ],
            'chats' => $chats,
            'scripts' => [
                'plugins/ckeditor.js',
                'forms-admin-funcs.js',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        View::render();
    }
}
