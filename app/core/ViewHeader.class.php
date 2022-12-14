<?

namespace app\core;

use app\models\Games;
use app\models\Settings;

class ViewHeader {
    
    public static function get(){
        $vars = [
            'headerLogo' => "<a href='/'>" . ImageProcessing::inputImage('/public/images/club_logo.png', ['title' => 'Main logo']) . '</a>',
            'headerProfileButton' => '<a class="header__profile-button" data-action-click="account/login/form">Вхід</a>',
            'headerMenu' => self::menu(),
        ];
        if (isset($_SESSION['id'])) {
            if ($_SESSION['avatar'] == '') {
                $profileImage = $_SESSION['gender'] === '' ? Settings::getImage('profile')['value'] : Settings::getImage($_SESSION['gender'])['value'];
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
                'path' => 'news',
                'label' => Locale::phrase('{{ HEADER_MENU_NEWS }}')
            ],
            [
                'path' => 'weeks',
                'label' => Locale::phrase('{{ HEADER_MENU_WEEKS }}')
            ],
            // [
            //     'path' => '',
            //     'label' => Locale::phrase('{{ HEADER_MENU_INFORMATION }}'),
            //     'menu' => Pages::getList(),
            //     'type' => 'page'
            // ],
            [
                'path' => 'game',
                'label' => Locale::phrase('Games'),
                'menu' => Games::menu(),
                'type' => 'game',
            ],
        ];

        if (isset($_SESSION['privilege']) && in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
            $menu[] = [
                'path' => 'game/mafia/start',
                'label' => Locale::phrase('Play a game'),
            ];
        }

        $headerMenu = '';

        for ($x = 0; $x < count($menu); $x++){
            if (!isset($menu[$x]['menu'])) {
                $headerMenu .= "
                <div class='header__navigation-item'>
                    <a href='/{$menu[$x]['path']}'>{$menu[$x]['label']}</a>
                    <div class='bar'></div>
                </div>";
            } else {
                $headerMenu .= '
                <div class="header__navigation-item dropdown">
                    <label class="dropdown__label">' . (empty($menu[$x]['path']) ? $menu[$x]['label'] : "<a href='/{$menu[$x]['path']}'>{$menu[$x]['label']}</a>") .'</label>
                    <div class="bar"></div>
                    <menu class="dropdown__menu">';
                        for ($i = 0; $i < count($menu[$x]['menu']); $i++){
                            $path = '';
                            if ($menu[$x]['menu'][$i]['short_name'] !== 'index') {
                                $path = $menu[$x]['type'] . '/' . $menu[$x]['menu'][$i]['short_name'];
                            }
                            $headerMenu .= "
                            <div class='dropdown__item'>
                                <a href='/$path'>{$menu[$x]['menu'][$i]['name']}</a>
                                <div class='dropdown__bar'></div>
                            </div>";
                        }
                $headerMenu .= "
                    </menu>
                </div>";
            }
        }
        // <label for='header__dropdown-menu-checkbox-{$menu[$x]['type']}' class='header__dropdown-menu-label'>{$menu[$x]['label']}</label>
        // <input type='checkbox' class='header__dropdown-menu-checkbox' id='header__dropdown-menu-checkbox-{$menu[$x]['type']}'>
        return $headerMenu;
    }
}