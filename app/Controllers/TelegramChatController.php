<?

namespace app\Controllers;

use app\core\Controller;
use app\core\Sender;
use app\core\View;
use app\models\Settings;
use app\models\TelegramChats;
use app\Repositories\TelegramChatsRepository;

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

        return View::render();
    }
    public static function listAction()
    {
        $chatsData = TelegramChats::getChatsList(10);
        $chatsData = TelegramChats::avatars($chatsData);

        $vars = [
            'title' => '{{ Chats_List_Title }}',
            'chatsData' => $chatsData,
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
    public static function sendAction()
    {
        if (!empty($_POST)) {
            $result = TelegramChatsRepository::sendPhoto($_POST);
            return $result ?
                View::notice(['message' =>  'Success!']) :
                View::notice(['type' => 'error', 'message' => 'Fail!']);
        }
        $chats = array_merge(TelegramChats::getGroupChatsList(), TelegramChats::getDirectChats());
        $chatsCount = count($chats);

        for ($i = 0; $i < $chatsCount; $i++) {
            $chats[$i]['title'] = TelegramChatsRepository::chatTitle($chats[$i]);
        }

        $vars = [
            'title' => 'Send message',
            'texts' => [
                'blockTitle' => 'Send message',
                'submitTitle' => 'Send',
                'sendAll' => '{{ Send_To_All }}',
                'sendGroups' => '{{ Send_To_Groups }}',
                'sendMain' => '{{ Send_To_Main }}',
                'ChatListLabel' => 'Chat’s list',
            ],
            'chats' => $chats,
            'chatsCount' => $chatsCount,
            'styles' => [
                'forms',
            ],
            'scripts' => [
                'plugins/ckeditor.js',
                'forms-admin-funcs.js',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
}
