<?

namespace app\Repositories;

use app\core\Tech;
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
        $page = Pages::getBySlug($slug);

        if (empty($page)) {

            if ($slug !== 'home') return false;

            $page = self::$defaultData;
            $page['id'] = 'home';
            $page['slug'] = $slug;
            $page['description'] = '';
            $page['type'] = 'page';
        }
        return $page;
    }
    public static function formPageOG(array $page = null)
    {
        $url = Tech::getRequestProtocol() . "://{$_SERVER['SERVER_NAME']}";
        $page['logo'] = empty($page['logo']) ? '/public/images/club-logo-w-city.jpg' : $page['logo'];
        $imageSize = getimagesize($_SERVER['DOCUMENT_ROOT'] . $page['logo']);
        $image = "$url/{$page['logo']}";
        $uri = $page['slug'] === 'home' && $page['type'] === 'page' ? '' : "{$page['type']}/{$page['slug']}/";
        $result = [
            'title' => $page['title'],
            'type' => 'article',
            'url' =>  "$url/$uri",
            'image' => $image,
            'og:image:width' => $imageSize[0],
            'og:image:height' => $imageSize[1],
            'description' => $page['description'],
            'site_name' => $page['title'] . ' | ' . CLUB_NAME,
            'twitter' => [
                'card' => 'summary_large_image',
                'image' => $image,
            ],
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
