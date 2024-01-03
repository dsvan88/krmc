<?php

namespace app\core;

use app\models\GameTypes;
use app\models\News;
use app\models\Settings;
use app\models\Users;
use app\Repositories\TechRepository;

class View
{
    public static $path;
    public static $route;
    public static $layout = 'default';
    public static $viewsFolder = '/app/views';

    public function __construct($route)
    {
        self::$route = $route;
        self::$path = $route['controller'] . '/' . $route['action'];
    }
    public static function set($route)
    {
        self::$route = $route;
        if (strpos($route['action'], 'Form') === false) {
            self::$path = $route['controller'] . '/' . $route['action'];
            return true;
        }
        $decamelized = Locale::decamelize($route['action']);
        $viewPath = str_replace(' ', '-', mb_substr($decamelized, 0, mb_strrpos($decamelized, ' ', 0, 'UTF-8'), 'UTF-8'));
        $viewPath = Locale::camelize($viewPath);
        self::$path = "{$route['controller']}/forms/$viewPath";
    }
    public static function render()
    {
        $styles = $scripts = '';

        self::$route['vars'] = Locale::apply(self::$route['vars']);
        extract(self::$route['vars']);

        extract(self::defaultVars());

        if (empty($mainClass)) $mainClass = 'index';

        $pageTitle = preg_replace('/<.*?>/', '', $title);

        $filename = self::$path;
        $path = $_SERVER['DOCUMENT_ROOT'] . self::$viewsFolder . "/$filename.php";
        if (file_exists($path)) {
            ob_start();
            require $path;
            $content = ob_get_clean();
        }

        if (empty($content)) {
            self::errorCode('404', ['message' => "View $filename isn’t found!"]);
        }

        $notices = Noticer::get();
        Noticer::clear();

        $lang = Locale::$langCode;
        $locales = Locale::getLocaledLinks();

        require $_SERVER['DOCUMENT_ROOT'] . self::$viewsFolder . '/layouts/' . self::$layout . '.php';

        self::exit();
    }
    public static function modal()
    {
        self::$route['vars'] = Locale::apply(self::$route['vars']);
        extract(self::$route['vars']);

        $response = [
            'modal' => true,
            'html' => '',
            'title' => $title,
        ];

        if (isset($scripts))
            $response['jsFile'] = $scripts;
        if (isset($css))
            $response['cssFile'] = $css;

        $path = $_SERVER['DOCUMENT_ROOT'] . self::$viewsFolder . '/' . self::$path . '.php';
        if (file_exists($path)) {
            ob_start();
            require $path;
            $response['html'] = ob_get_clean();

            self::exit(json_encode($response));
        } else {
            self::errorCode('404', ['message' => 'View ' . self::$path . ' isn’t found!']);
        }
    }
    /**
     * Use for hard redirect from server
     * 
     * @param string $url - where to redirect;
     * 
     * @return void
     */
    public static function redirect(string $url = '/'): void
    {
        if ($url[strlen($url) - 1] !== '/')
            $url .= '/';
        header('Location: ' . $url);
        self::exit();
    }
    /**
     * Use for soft redirect for js handler
     * 
     * @param string $url - where to redirect;
     * 
     * @return json string with
     */
    public static function location(string $url, int $error = 0): void
    {
        if ($url[strlen($url) - 1] !== '/')
            $url .= '/';
        $message = ['location' => $url];
        if ($error > 0)
            $message['error'] = $error;
        self::exit(json_encode($message));
    }
    public static function errorCode($code, $data = [])
    {
        extract($data);
        $path = $_SERVER['DOCUMENT_ROOT'] . self::$viewsFolder . "/errors/$code.php";
        http_response_code($code);
        if (file_exists($path))
            require $path;
        self::exit();
    }
    public static function html()
    {

        extract(self::$route['vars']);
        // extract($vars);

        $path = $_SERVER['DOCUMENT_ROOT'] . self::$viewsFolder . "/$path.php";
        if (file_exists($path)) {
            ob_start();
            require $path;
            $content = ob_get_clean();
        }
        self::message(['html' => $content]);
    }
    public static function message($data = '')
    {
        if (!is_array($data)) {
            $data = ['message' => $data];
        }
        if (isset($data['message'])) {
            $data['message'] = Locale::phrase($data['message']);
        }
        if (!empty($data['error']) && $data['error'] > 1) {
            http_response_code($data['error']);
        }
        self::exit(json_encode($data));
    }
    public static function notice($data = '')
    {
        if (!is_array($data)) {
            $data = [
                'type' => '',
                'message' => $data
            ];
        }

        $data['message'] = empty($data['message']) ? '' : Locale::phrase($data['message']);

        if (!empty($data['error'])) {
            $data['type'] = 'error';
        }
        self::exit(json_encode(['notice' => $data]));
    }
    public static function response($data)
    {
        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        self::exit($data);
    }
    public static function defaultVars()
    {
        $header = self::headerData();
        $footer = self::footerData();
        return array_merge($header, $footer);
    }

    public static function file($file, $name = 'backup.txt')
    {
        if (empty($file) || !file_exists($file)) {
            exit('File is not found');
        }
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename='$name'");
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit();
    }
    public static function exit(string $string = null): void
    {
        if (!empty($string)) {
            echo $string;
        }
        TechRepository::scheduleBackup();
        exit();
    }
    public static function component(string $filename, array $vars = [])
    {
        extract($vars);
        $texts = [];
        if (!empty(self::$route['vars']['texts'])) $texts = self::$route['vars']['texts'];
        return require $_SERVER['DOCUMENT_ROOT'] . self::$viewsFolder . "/components/$filename.php";
    }

    public static function headerData()
    {
        $images = Settings::getGroup('img');
        $images = Locale::apply($images);
        $vars = [
            'headerLogo' => "<a href='/'>" . ImageProcessing::inputImage($images['MainLogo']['value']) . '</a>',
            'headerProfileButton' => '<a class="header__profile-button" data-action-click="account/login/form">' . Locale::phrase('Log In') . '</a>',
            'headerMenu' => self::menu(),
        ];
        if (isset($_SESSION['id'])) {
            if (empty($_SESSION['avatar'])) {
                $profileImage = empty($_SESSION['gender']) ? $images['profile']['value'] : $images[$_SESSION['gender']]['value'];
            } else {
                $profileImage = FILE_USRGALL . "{$_SESSION['id']}/{$_SESSION['avatar']}";
            }

            $profileImage = ImageProcessing::inputImage($profileImage, ['title' => $_SESSION['name']]);

            $texts = [
                'headerMenuProfileLink' => '{{ HEADER_ASIDE_MENU_PROFILE }}',
                'headerMenuAddNewsLink' => '{{ HEADER_ASIDE_MENU_ADD_NEWS }}',
                'headerMenuChangePromoLink' => '{{ HEADER_ASIDE_MENU_CHANGE_PROMO }}',
                'headerMenuAddPageLink' => '{{ HEADER_ASIDE_MENU_ADD_PAGE }}',
                'headerMenuUsersListLink' => '{{ HEADER_ASIDE_MENU_USERS_LISTS }}',
                'headerMenuUsersChatsLink' => '{{ HEADER_ASIDE_MENU_USERS_CHATS }}',
                'headerMenuChatSendLink' => '{{ HEADER_ASIDE_MENU_CHAT_SEND }}',
                'headerMenuSettingsListLink' => '{{ HEADER_ASIDE_MENU_SETTINGS_LIST }}',
                'headerMenuLogoutLink' => '{{ HEADER_ASIDE_MENU_LOGOUT }}',
            ];

            $texts = Locale::apply($texts);

            ob_start();
            require $_SERVER['DOCUMENT_ROOT'] . '/app/views/main/header-menu.php';
            $vars['headerProfileButton'] = ob_get_clean();
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
        if (isset($contacts['telegram']) && !empty($contacts['telegram']['value'])) {
            $footerContacts .= "<p><a class='fa fa-telegram' href='{$contacts['telegram']['value']}' target='_blank'> {$contacts['tg-name']['value']}</a></p>";
        }
        if (isset($contacts['email']) && !empty($contacts['email']['value'])) {
            $footerContacts .= "<p><a class='fa fa-envelope' href='mailto:{$contacts['email']['value']}' target='_blank'> {$contacts['email']['value']}</a></p>";
        }
        if (isset($contacts['phone']) && !empty($contacts['phone']['value'])) {
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
        if (isset($socials['facebook']) && !empty($socials['facebook']['value'])) {
            $footerSocials .= "<a class='fa fa-facebook-square' href='{$socials['facebook']['value']}' target='_blank'></a>";
        }
        if (isset($socials['youtube']) && !empty($socials['youtube']['value'])) {
            $footerSocials .= "<a class='fa fa-youtube-square' href='{$socials['youtube']['value']}' target='_blank'></a>";
        }
        if (isset($socials['instagram']) && !empty($socials['instagram']['value'])) {
            $footerSocials .= "<a class='fa fa-instagram' href='{$socials['instagram']['value']}' target='_blank'></a>";
        }

        return compact('footerGmapLink', 'footerAdress', 'footerContacts', 'footerGmapWidget', 'footerSocials');
    }
}
