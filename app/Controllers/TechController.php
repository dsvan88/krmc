<?

namespace app\Controllers;

use app\core\Controller;
use app\core\Pages;
use app\core\View;
use app\libs\Db;
use app\models\Settings;
use app\models\Users;
use app\models\Weeks;

class TechController extends Controller
{
    public static function sqlAction()
    {
        if (!empty($_POST)) {
            if (isset($_POST['sql_query'])) {
                $result = Db::query($_POST['sql_query'], [], 'Assoc');
                View::message(['error' => 0, 'sql-result' => $result]);
            }
        }
        $vars = [
            'title' => '{{ SQL_Action_Title }}',
            'texts' => [
                'SubmitLabel' => '{{ Submit_Label }}',
            ],
            'scripts' => [
                '/public/scripts/plugins/ckeditor.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/forms-admin-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
        ];
        View::render($vars);
    }
    public static function migrationAction()
    {
        for ($id = 17; $id < 20; $id++) {
            $week = Weeks::weekDataById($id);

            if (!$week) return false;

            $count = count($week['data']);
            for ($x = 0; $x < $count; $x++) {
                $week['data'][$x]['status'] = '';
            }
            $weekId = $week['id'];
            unset($week['id']);
            Weeks::setWeekData($weekId, $week);
        }
    }
}
