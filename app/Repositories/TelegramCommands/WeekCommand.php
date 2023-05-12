<?
namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\models\Days;
use app\models\News;
use app\models\Weeks;

class WeekCommand extends ChatCommand {
    public static function description(){
        return self::locale('<u>/day (week day)</u> <i>// Booking information for a specific day. Without specifying the day - for today</i>');
    }
    public static function execute(array $arguments=[]){
        $weeksData = Weeks::nearWeeksDataByTime();

        $message = '';
        if (empty($weeksData)){
            return [false, self::locale('{{ Tg_Command_Games_Not_Set }}')];
        }
        foreach ($weeksData as $weekData) {
    
            for ($i = 0; $i < 7; $i++) {
    
                if (!isset($weekData['data'][$i]) || in_array($weekData['data'][$i]['status'], ['', 'recalled'])) {
                    continue;
                }
                $dayDescription = Days::getFullDescription($weekData, $i);
                if ($dayDescription !== '')
                    $message .=  $dayDescription .
                        "___________________________\n";
            }
        }
        
        $promoData = News::getPromo();
        if ($promoData) {
            if ($promoData['title'] !== '') {
                $message .= "<u><b>$promoData[title]</b></u>\n<i>$promoData[subtitle]</i>\n\n";
                $message .= preg_replace('/(<((?!b|u|s|strong|em|i|\/b|\/u|\/s|\/strong|\/em|\/i)[^>]+)>)/i', '', str_replace(['<br />', '<br/>', '<br>', '</p>'], "\n", trim($promoData['html'])));
            }
        }
        return [true, $message];
    }
}