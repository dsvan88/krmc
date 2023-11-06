<?

namespace app\Repositories;

use app\core\Locale;
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
        // $page = [];
        // if (!empty($pages)) {
        //     $count = count($pages);
        //     $default = [];
        //     for ($x=0; $x < $count; $x++) { 
        //         if (!empty($page[$x]['date_delete'])) continue;
        //         if (empty($pages[$x]['lang'])){
        //             $default = $pages[$x];
        //             continue;
        //         }
        //         if ($pages[$x]['lang'] !== Locale::$langCode) continue;
        //         $page = $pages[$x];
        //         break;
        //     }
        //     if (empty($page) && !empty($default)) $page = $default;
        // }

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
    public static function formPageOG(array $page = null){
        $url = "{$_SERVER['HTTP_X_FORWARDED_PROTO']}://{$_SERVER['SERVER_NAME']}";
        $page['logo'] = empty($page['logo']) ? '/public/images/club-logo-w-city.jpg' : $page['logo'];
        $imageSize = getimagesize($_SERVER['DOCUMENT_ROOT'].$page['logo']);
        $image = "$url/{$page['logo']}";
        $result = [
            'title' => $page['title'],
            'type' => 'article',
            'url' => "$url/{$page['type']}/{$page['slug']}/",
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
