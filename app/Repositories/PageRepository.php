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
    public static function formPageOG(array $page = null){
        $url = "{$_SERVER['HTTP_X_FORWARDED_PROTO']}://{$_SERVER['SERVER_NAME']}";
        $result = [
            'title' => $page['title'],
            'type' => 'article',
            'url' => "$url/{$page['type']}/{$page['slug']}/",
            'image' => empty($page['logo']) ? "$url/public/images/club-logo-w-city.jpg" : $page['logo'],
            'description' => $page['description'],
            'site_name' => $page['title'] . ' | ' . CLUB_NAME,
        ];

        
/*     article:published_time - datetime - When the article was first published.
    article:modified_time - datetime - When the article was last changed.
    article:expiration_time - datetime - When the article is out of date after.
    article:author - profile array - Writers of the article.
    article:section - string - A high-level section name. E.g. Technology
    article:tag - string array - Tag words associated with this article. */

        return $result;
    }
}
