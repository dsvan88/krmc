<?

namespace app\Repositories;

use app\core\GoogleDrive;
use app\core\ImageProcessing;
use app\core\Locale;
use app\core\Sender;
use app\models\Settings;
use app\models\TelegramChats;
use app\models\Users;
use app\core\TelegramBot;
use app\core\Tech;
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
    public static function getAndSaveTgAvatar(int $userId = 0, $silent = false): bool
    {
        if (empty($userId)) {
            if ($silent) return false;
            throw new Exception("UserID is empty!");
        }

        $userData = Users::find($userId);
        Users::contacts($userData);

        if (empty($userData['contacts']['telegramid'])) {
            if ($silent) return false;
            throw new Exception("Telegram ID is empty!\nLink your Telegram account first.");
        }

        $avatar = Sender::getUserProfileAvatar($userData['contacts']['telegramid']);

        if (!$avatar) {
            if ($silent) return false;
            throw new Exception("Profile avatar is empty!");
        }

        $filename = $userData['id'] . '_avatar.jpg';
        $image = ImageProcessing::saveRawImage($avatar, $filename);

        if ($image === false) {
            if ($silent) return false;
            throw new Exception("Image didn’t saved successfully!");
        }

        $gDrive = new GoogleDrive();
        $fileId = $gDrive->create($image['fullpath'], 'avatars');

        $userData['personal']['avatar'] = $fileId;

        unlink($image['fullpath']);

        return (bool) Users::edit(['personal' => $userData['personal']], ['id' => $userId]);
    }
    public static function isChatExists(int $chatId)
    {

        $tgBot = new TelegramBot;
        $tgBot->getChat($chatId);

        return $tgBot::$result['ok'] || $tgBot::$result['error_code'] !== 400;
    }
    public static function setChatsType(int $chatId = 0, string $type = 'main'): bool
    {
        $slug = 'main_group_chat';

        if ($type !== 'main') {
            if ($type === 'log' || $type === 'tech') {
                $slug = 'tech_chat';
            } elseif ($type === 'admin') {
                $slug = 'admin_chat';
            }
        }
        Settings::save('telegram', $slug, $chatId);
        return true;
    }
}
