<?

namespace app\Repositories;

use app\core\ImageProcessing;
use app\core\Locale;
use app\core\Sender;
use app\models\Settings;
use app\models\TelegramChats;
use Exception;

class TelegramChatsRepository
{
    public static function chatTitle(array $chat = []): string
    {
        if (isset($chat['personal']['title'])) {
            return $chat['personal']['title'];
        }
        $titleParts = [];
        if (isset($chat['personal']['first_name'])) {
            $titleParts[] = $chat['personal']['first_name'];
        }
        if (isset($chat['personal']['last_name'])) {
            $titleParts[] = $chat['personal']['last_name'];
        }
        if (isset($chat['personal']['username'])) {
            $titleParts[] = "(@{$chat['personal']['username']})";
        }
        $title = implode(' ', $titleParts);
        return $title;
    }
    public static function sendPhoto(array $post = []): bool
    {
        $message =  preg_replace('/(<((?!b|u|s|strong|em|i|\/b|\/u|\/s|\/strong|\/em|\/i)[^>]+)>)/i', '', str_replace(['<br />', '<br/>', '<br>', '</p>'], "\n", trim($_POST['html'])));
        if (is_numeric($post['target'])) {
            $targets = $post['target'];
        } elseif ($post['target'] === 'main') {
            $targets = Settings::getMainTelegramId();
        } elseif ($post['target'] === 'groups') {
            $targets = array_column(TelegramChats::getGroupChatsList(), 'uid');
        } else {
            $targets = array_column(TelegramChats::getChatsList(), 'uid');
        }

        if (empty($post['image_link'])) {
            return Sender::message($targets, $message)[0]['ok'];
        }

        // $symbols = Locale::$cyrillicPattern;
        // $filename = preg_replace("/([^a-z$symbols.,;0-9_-]+)/ui", '', trim($post['filename']));

        // if (empty($post['image']))
        //     return Sender::message($targets, $message)[0]['ok'];

        // $image = ImageProcessing::saveBase64Image($post['image'], $filename);
        // if (!$image) {
        //     throw new Exception('Image didn’t saved');
        // }
        return Sender::photo($targets, $message, $post['image_link'])[0]['ok'];
    }
}
