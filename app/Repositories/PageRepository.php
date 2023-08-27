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
}
