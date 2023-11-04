<?

namespace app\core;

use app\models\GameTypes;
use app\models\News;
use app\models\Settings;
use app\models\Users;

class ViewHeader {
    
    public static function get(){
        $images = Settings::getGroup('img');
        $images = Locale::apply($images);
        $vars = [
            'headerLogo' => "<a href='/'>" . ImageProcessing::inputImage($images['MainLogo']['value']) . '</a>',
            'headerProfileButton' => '<a class="header__profile-button" data-action-click="account/login/form">'.Locale::phrase('Log In').'</a>',
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
    public static function menu(){
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

        if (News::getCount('news') < 1){
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
}