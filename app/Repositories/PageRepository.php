<?

namespace app\Repositories;

use app\models\Pages;

class PageRepository
{
    public static $defaultData = [
        'decription' => '&ltNo Data&gt;',
        'title' => '&ltNo Data&gt;',
        'subtitle' => '&ltNo Data&gt;',
        'html' => '&ltNo Data&gt;',
    ];

    public static function getPage(string $slug)
    {
        $page = Pages::findBy('slug', $slug);
        if ($page) {
            $page = empty($page[0]['date_delete']) ? $page = $page[0] : false;
        }

        if (empty($page)) {

            if ($slug !== 'home') return false;

            $page = self::$defaultData;
            $page['id'] = 'home';
        }
        return $page;
    }

    public static function dashboard($index)
    {
        $dashboard = '';
        if (!empty($_SESSION['privilege']['status']) && in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
            // $id = empty($page['id']) ? $slug : $page['id'];
            $dashboard = "<span class='page__dashboard' style='float:right'>
                <a href='/page/edit/$index' title='Редагувати' class='fa fa-pencil-square-o'></a>";
            if ($index !== 'home') {
                $dashboard .= "<a href='/page/delete/$index' onclick='return confirm(\"Are you sure?\")' title='Видалити' class='fa fa-trash-o'></a>";
            }
            $dashboard .= '</span>';
        }
        return $dashboard;
    }
}
