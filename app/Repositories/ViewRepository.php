<?

namespace app\Repositories;

use app\core\ImageProcessing;
use app\core\Locale;
use app\models\GameTypes;
use app\models\News;
use app\models\Settings;
use app\models\Users;

class ViewRepository
{
    public static function defaultVars()
    {
        $header = self::headerData();
        $footer = self::footerData();
        return array_merge($header, $footer);
    }
    public static function headerData()
    {
        $images = Settings::getGroup('img');
        $images = Locale::apply($images);
        $vars = [
            'headerLogo' => ImageProcessing::inputImage($images['MainLogo']['value']),
            'headerLoginLabel' => Locale::phrase('Log In'),
            'headerLogoutLabel' => Locale::phrase('Log Out'),
            'headerMenu' => self::menu(),
            'headerDashboard' => false,
            'profileImage' => false,
        ];
        if (isset($_SESSION['id'])) {
            if (empty($_SESSION['avatar'])) {
                $profileImage = empty($_SESSION['gender']) ? $images['profile']['value'] : $images[$_SESSION['gender']]['value'];
            } else {
                $profileImage = FILE_USRGALL . "{$_SESSION['id']}/{$_SESSION['avatar']}";
            }

            $vars['profileImage'] = ImageProcessing::inputImage($profileImage, ['title' => $_SESSION['name']]);

            $vars['headerDashboard'] = self::dashboard();
            $vars['profileMenu'] = self::profileMenu();
        }
        return $vars;
    }
    public static function menu()
    {
        $menu = [
            [
                'path' => '',
                'label' => 'Home'
            ],
            [
                'path' => 'news/',
                'label' => 'News',
            ],
            [
                'path' => 'weeks/',
                'label' => 'Weeks',
            ],
            // [
            //     'path' => '',
            //     'label' => Locale::phrase('{{ HEADER_MENU_INFORMATION }}'),
            //     'menu' => Pages::getList(),
            //     'type' => 'page'
            // ],
            [
                'path' => 'game/',
                'label' => 'Games',
                'menu' => GameTypes::menu(),
                'type' => 'game',
            ],
        ];

        if (News::getCount('news') < 1) {
            unset($menu[1]);
            $menu = array_values($menu);
        }

        if (Users::checkAccess('trusted')) {
            $menu[] = [
                'label' => 'Activity',
                'menu' => [
                    [
                        'name' => 'Play a game',
                        'slug' => 'play',
                        'fields' => '',
                    ],
                    [
                        'name' => 'History',
                        'slug' => 'history',
                        'fields' => '',
                    ],
                    [
                        'name' => 'Rating',
                        'slug' => 'rating',
                        'fields' => '',
                    ],
                    // [
                    //     'name' => 'Peek on game',
                    //     'slug' => 'peek',
                    //     'fields' => '',
                    // ],
                    [
                        'name' => 'Last game',
                        'slug' => 'last',
                        'fields' => '',
                    ],
                ],
                'type' => 'activity',
            ];
        }
        return Locale::apply($menu);
    }
    public static function footerData()
    {
        $contacts = Settings::getGroup('contacts');

        $footerGmapLink = $contacts['gmap_link']['value'];
        $footerAdress = '<p>' . str_replace('  ', '</p><p>', $contacts['adress']['value']) . '</p>';

        $footerContacts = '';
        if (!empty($contacts['telegram']['value'])) {
            $footerContacts .= "<p><a class='fa fa-telegram' href='{$contacts['telegram']['value']}' target='_blank'> {$contacts['tg-name']['value']}</a></p>";
        }
        if (!empty($contacts['email']['value'])) {
            $footerContacts .= "<p><a class='fa fa-envelope' href='mailto:{$contacts['email']['value']}' target='_blank'> {$contacts['email']['value']}</a></p>";
        }
        if (!empty($contacts['phone']['value'])) {
            $phone = preg_replace('/[^0-9]/', '', $contacts['phone']['value']);
            if (strlen($phone) === 10) {
                $phone = '38' . $phone;
            } elseif (strlen($phone) === 11) {
                $phone = '3' . $phone;
            }
            if (strlen($phone) === 12) {
                preg_match('/(\d{2})(\d{3})(\d{3})(\d{2})(\d{2})/', $phone, $phoneParts);
                $phoneFormatted = sprintf('+%s (%s) %s-%s-%s', $phoneParts[1], $phoneParts[2], $phoneParts[3], $phoneParts[4], $phoneParts[5]);
                $footerContacts .= "<p><a class='fa fa-phone' href='tel:+$phone' target='_blank'></a> $phoneFormatted</p>";
            }
        }
        $footerGmapWidget = $contacts['gmap_widget']['value'];

        $socials = Settings::getGroup('socials');
        $footerSocials = '';
        if (!empty($socials['facebook']['value'])) {
            $footerSocials .= "<a class='fa fa-facebook-square' href='{$socials['facebook']['value']}' title='Facebook' target='_blank'></a>";
        }
        if (!empty($socials['youtube']['value'])) {
            $footerSocials .= "<a class='fa fa-youtube-square' href='{$socials['youtube']['value']}' title='Youtube' target='_blank'></a>";
        }
        if (!empty($socials['instagram']['value'])) {
            $footerSocials .= "<a class='fa fa-instagram' href='{$socials['instagram']['value']}' title='Facebook' target='_blank'></a>";
        }

        return compact('footerGmapLink', 'footerAdress', 'footerContacts', 'footerGmapWidget', 'footerSocials');
    }
    public static function dashboard(): array
    {

        if (!Users::checkAccess('trusted')) return [];

        $links = [
            [
                'link' => 'news/add',
                'icon' => 'newspaper-o',
                'label' => 'Add News',
            ],
            [
                'link' => 'news/edit/promo',
                'icon' => 'bullhorn',
                'label' => 'Change Promo',
            ],
            [
                'link' => 'page/add',
                'icon' => 'file-text-o',
                'label' => 'Add Page',
            ],
            [
                'link' => 'users/list',
                'icon' => 'users',
                'label' => 'Users List',
            ],
            [
                'link' => 'chat/index',
                'icon' => 'comments-o',
                'label' => 'Chats List',
            ],
            [
                'link' => 'chat/send',
                'icon' => 'paper-plane-o',
                'label' => 'Send message',
            ],
            [
                'link' => 'settings/index',
                'icon' => 'cogs',
                'label' => 'Settings List',
            ],
        ];

        $routes = require $_SERVER['DOCUMENT_ROOT'] . '/app/config/routes/http.php';
        $result = [];
        foreach ($links as $item) {
            if (empty($routes[$item['link']]) || !Users::checkAccess($routes[$item['link']]['access']['category'])) continue;
            $result[] = $item;
        }
        if (empty($result)) return [];

        return Locale::apply($result);
    }
    public static function profileMenu(): array
    {
        if (empty($_SESSION['id'])) return [];
        $menu = [
            [
                'link' => 'account/profile/' . $_SESSION['id'],
                'label' => 'Profile',
            ],
            [
                'link' => 'account/logout',
                'label' => 'Log Out',
            ],
        ];
        return Locale::apply($menu);
    }
}
