<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Sender;
use app\core\Tech;
use app\core\View;
use app\mappers\Settings;
use app\mappers\TelegramChats;
use app\Services\TelegramChatsService;

class telegramChatController extends Controller
{
    public static function indexAction()
    {
        $chatsData = TelegramChats::getChatsList();
        $chatsData = TelegramChats::nicknames($chatsData);
        $vars = [
            'title' => '{{ Chats_List_Title }}',
            'chatsData' => $chatsData,
            'scripts' => [
                'chats/list.js'
            ],
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
            'scripts' => [
                'chats/list.js'
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
    public static function sendAction()
    {
        if (!empty($_POST)) {
            $result = TelegramChatsService::sendPhoto($_POST);
            return $result ?
                View::notice(['message' =>  'Success']) :
                View::notice(['type' => 'error', 'message' => 'Fail!']);
        }
        $chats = array_merge(TelegramChats::getGroupChatsList(), TelegramChats::getDirectChats());
        $chatsCount = count($chats);

        for ($i = 0; $i < $chatsCount; $i++) {
            $chats[$i]['title'] = TelegramChatsService::chatTitle($chats[$i]);
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
                'images-pad.js',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
}
