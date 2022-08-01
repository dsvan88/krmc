<?

namespace app\Controllers;

use app\core\Controller;
use app\core\Pages;
use app\core\View;
use app\libs\Db;
use app\models\Settings;
use app\models\Users;

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
}
