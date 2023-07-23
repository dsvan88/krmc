<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\models\News;

class PromoCommand extends ChatCommand
{
    public static $accessLevel = 'manager';
    public static function description()
    {
        return self::locale("\n<u>/promo</u> <i>// Fix notification that is added at the bottom of the /week command.</i> The text before the first line break is the title, before the second one is the subtitle, everything below is the text of the alert. Example:\n\t\t/promo Title\nSubtitle\nText, or: here could be your <b>Advertising</b><i>:)</i>\n");
    }
    public static function execute(array $arguments = [])
    {
        $text = self::$message['message']['text'];
        $promoText = trim(mb_substr($text, mb_strpos($text, ' ', 0, 'UTF-8') + 1, NULL, 'UTF-8'));

        if (isset(self::$message['message']['entities'])) {

            $newString = '';
            $offset = 0;
            $formattings = [
                'bold' => 'b',
                'italic' => 'i',
                'strikethrough' => 's',
                'spoiler' => 'tg-spoiler',
            ];
            for ($i = 0; $i < count(self::$message['message']['entities']); $i++) {
                if (self::$message['message']['entities'][$i]['type'] === 'bot_command') {
                    $offset = self::$message['message']['entities'][$i]['offset'] + self::$message['message']['entities'][$i]['length'];
                    continue;
                }
                $newString .= mb_substr($text, $offset, self::$message['message']['entities'][$i]['offset'] - $offset, 'UTF-8');
                $newString .= "<{$formattings[self::$message['message']['entities'][$i]['type']]}>" . mb_substr($text, self::$message['message']['entities'][$i]['offset'], self::$message['message']['entities'][$i]['length'], 'UTF-8') . "</{$formattings[self::$message['message']['entities'][$i]['type']]}>";
                $offset = self::$message['message']['entities'][$i]['offset'] + self::$message['message']['entities'][$i]['length'];
            }
            $newString .= mb_substr($text, $offset, null, 'UTF-8');

            $newString = preg_replace(['/\-\-(.*)\-\-/'], ['<u>$1</u>'], $newString);

            if ($newString !== '')
                $promoText = $newString;
        }
        preg_match('/(.*?)\n(.*?)\n([^`]*)/', $promoText, $matches);

        $data = [
            'title' => isset($matches[1]) ? trim($matches[1]) : '',
            'subtitle' => isset($matches[2]) ? trim($matches[2]) : '',
            'html' => isset($matches[3]) ? str_replace("\n", '</br>', $matches[3]) : '',
        ];

        News::edit($data, 'promo');

        self::$operatorClass::$resultMessage = self::locale('{{ Tg_Command_Promo_Saved }}');
        return true;
    }
}
