<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\ImageProcessing;
use app\core\View;
use app\models\Settings;
use app\models\Users;
use app\core\Locale;
use app\core\TelegramBot;
use app\models\TelegramChats;

class AccountController extends Controller
{
    public static function before()
    {
        View::$layout = 'custom';
    }
    public function logoutAction()
    {
        Users::logout();
        View::redirect('/');
    }
    public function loginAction()
    {
        if (!empty($_POST)) {
            if (Users::login($_POST)) {
                View::location('/');
            } else {
                View::message(['error' => 1, 'message' => '{{ Account_Login_User_Not_Found }}']);
            }
            exit();
        }
        $vars = [
            'formTitle' => '{{ Account_Login_Form_Title }}'
        ];
        View::render('{{ Account_Login_Page_Title }}', $vars);
    }
    public function usersListAction()
    {
        $vars = [
            'formTitle' => '{{ Users_List_Title }}',
            'usersData' => Users::getList()
        ];
        View::render('{{ Users_List_Page_Title }}', $vars);
    }
    public function loginFormAction()
    {
        $vars = [
            'formTitle' => '{{ Account_Login_Form_Title }}',

            'loginFormTitle' => '{{ Account_Login_Form_Title }}',
            'loginFormLoginInputPlaceholder' => '{{ Account_Login_Form_Login_Input_Placeholder }}',
            'loginFormPasswordInputPlaceholder' => '{{ Account_Login_Form_Password_Input_Placeholder }}',
            'loginFormSubmitTitle' => '{{ Account_Login_Form_Submit_Title }}',
            'loginFormForgetLink' => '{{ Account_Login_Form_Forget_link }}',
            'loginFormRegisterLink' => '{{ Account_Login_Form_Register_Link }}',
        ];
        View::modal($vars);
    }
    public function profileEditAction()
    {
        if (!empty($_POST)) {
            $userId = $_SESSION['id'];
            if (isset($_POST['uid']) && $_SESSION['id'] != $_POST['uid']) {
                if ($_SESSION['privilege']['status'] !== 'admin') {
                    View::message('{"error":1,"text":"Ви не можете змінювати інформацію других користувачів"}');
                    die();
                    // die('{"error":1,"text":"Ви не можете змінювати інформацію других користувачів"}');
                }
                $userId = $_POST['uid'];
            }
            $userData = Users::getDataById($userId);
            unset($userData['id']);

            if ($_POST['birthday'] !== '') {
                $birthday = strtotime(trim($_POST['birthday']));

                if ($birthday > $_SERVER['REQUEST_TIME'] - 60 * 60 * 24 * 365)
                    $birthday = 0;
            }

            $userData['personal'] = [
                'fio' => trim($_POST['fio']),
                'birthday' => $birthday,
                'gender' => trim($_POST['gender']),
                'avatar' => $userData['personal']['avatar'],
            ];
            $userData['contacts']['email'] = trim($_POST['email']);
            /* 'telegram' => trim($_POST['telegram']),
            'telegramid' => trim($_POST['telegramid']) */

            if (isset($_POST['status']))
                $userData['privilege']['status'] = trim($_POST['status']);

            if (isset($_POST['image'])) {

                $path = $_SERVER['DOCUMENT_ROOT'] . FILE_USRGALL . $userId;
                $filename = md5($_POST['image']) . '_3,5x4';
                $image = ImageProcessing::saveBase64Image($_POST['image'], $filename, $path);
                if ($image) {
                    $userData['personal']['avatar'] = $image['filename'];
                    if (isset($_POST['uid']) && $_SESSION['id'] == $_POST['uid']) {
                        $_SESSION['avatar'] = $image['filename'];
                    }
                }
            }
            Users::edit($userData, ['id' => $userId]);
        }
        View::message(['error' => 0, 'message' => '{{ Action_Success }}']);
    }
    public function profileFormAction()
    {
        $uid = (int)$_POST['uid'];
        if (!in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
            $uid = (int) $_SESSION['id'];
        }
        $userData = Users::getDataById($uid);

        if ($userData['personal']['avatar'] !== '') {
            $avatar = FILE_USRGALL . "{$userData['id']}/{$userData['personal']['avatar']}";
        } else {
            $avatar = Settings::getImage('empty_avatar')['value'];
        }
        $userData['avatar'] = ImageProcessing::inputImage($avatar, ['title' => Locale::applySingle(['string' => '{{ Account_Profile_Form_User_Avatar }}', 'vars' => [$userData['name']]])]);

        $statuses = '';
        if ($_SESSION['privilege']['status'] === 'admin') {
            $statuses = '
        <div class="modal-form__row">
            <label class="modal-form__label" for="profile-status">Статус</label>
            <select class="modal-form__select" id="profile-status" name="status"/>
                <option value="" ' . ($userData['privilege']['status'] === '' ? ' selected' : '') . '>Гость</option>
                <option value="user" ' . ($userData['privilege']['status'] === 'user' ? ' selected' : '') . '>Користувач</option>
                <option value="admin" ' . ($userData['privilege']['status'] === 'admin' ? ' selected' : '') . '>Админ</option>
                <option value="manager" ' . ($userData['privilege']['status'] === 'manager' ? ' selected' : '') . '>Менеджер</option>
            </select>
        </div>';
        }

        $vars = [
            'texts' => [
                'formTitle' => [
                    'string' => '{{ Account_Profile_Form_Title }}',
                    'vars' => [$userData['name']]
                ],
                'profileFormTitle' => [
                    'string' => '{{ Account_Profile_Form_Title }}',
                    'vars' => [$userData['name']]
                ],
                'profileFormFioLabel' => '{{ Account_Profile_Form_Fio_Label }}',
                'profileFormBirthdayLabel' => '{{ Account_Profile_Form_Birthday_Label }}',
                'profileFormGenderLabel' => '{{ Account_Profile_Form_Gender_Label }}',
                'profileFormEmailLabel' => '{{ Account_Profile_Form_Email_Label }}',
                'profileFormTelegramLabel' => '{{ Account_Profile_Form_Telegram_Label }}',
                'profileFormSubmitLabel' => '{{ Account_Profile_Form_Telegram_Label }}',
                'formSaveLabel' => '{{ Save_Label }}',
                'formCancelLabel' => '{{ Cancel_Label }}'
            ],
            'profileFormStatusesBlock' => $statuses,
            'userData' => $userData
        ];
        View::modal($vars);
    }
    public function setNicknameAction()
    {
        if (!empty($_POST)) {
            if ($_SESSION['privilege']['status'] !== 'admin') {
                View::message('{"error":1,"text":"Ви не можете змінювати інформацію других користувачів"}');
                die();
            }
            $chatId = (int) $_POST['cid'];
            $name = Locale::mb_ucfirst(trim($_POST['name']));
            $chatData =  TelegramChats::getChat($chatId);
            $chatId = $chatData['id'];
            unset($chatData['id']);

            $userData = Users::getDataByName($name);
            if (!$userData) {
                $userId = Users::add($name);
                $userData = Users::getDataById($userId);
            } else {
                $userId = $userData['id'];
            }
            unset($userData['id']);

            $fio = '';
            if (isset($chatData['personal']['first_name'])) {
                $fio .= $chatData['personal']['first_name'];
            }
            if (isset($chatData['personal']['last_name'])) {
                $fio .= ' ' . $chatData['personal']['last_name'];
            }
            $fio = trim($fio);

            $userData['personal']['fio'] = $fio;
            $userData['contacts']['telegramid'] = $chatData['uid'];
            if (isset($chatData['personal']['username'])) {
                $userData['contacts']['telegram'] = $chatData['personal']['username'];
            }

            $chatData['personal']['nickname'] = $name;

            Users::edit($userData, ['id' => $userId]);
            TelegramChats::edit($chatData, $chatId);
        }
        View::message(['message' => '{{ Action_Success }}']);
    }
    public function setNicknameFormAction()
    {
        $cid = (int)$_POST['cid'];
        if (!in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
            View::errorCode(403, ['message' => 'Something went wrong! How did you get here?']);
        }

        $chatData = TelegramChats::getChat($cid);

        $chatTitle = '';
        if (isset($chatData['personal']['title'])) {
            $chatTitle = $chatData['personal']['title'];
        } else {
            $titleParts = [];
            if (isset($chatData['personal']['first_name'])) {
                $titleParts[] = $chatData['personal']['first_name'];
            }
            if (isset($chatData['personal']['last_name'])) {
                $titleParts[] = $chatData['personal']['last_name'];
            }
            if (isset($chatData['personal']['username'])) {
                $titleParts[] = "(<a href='https://t.me/{$chatData['personal']['username']}'>@{$chatData['personal']['username']}</a>)";
            }
            $chatTitle = implode(' ', $titleParts);
        }
        $vars = [
            'texts' => [
                'formTitle' => [
                    'string' => '{{ Account_Profile_Form_Title }}',
                    'vars' => [$chatTitle]
                ],
                'formSaveLabel' => '{{ Save_Label }}',
                'formCancelLabel' => '{{ Cancel_Label }}'
            ],
            'chatData' => $chatData,
            'scripts' => '/public/scripts/apply-input-listener.js?v=' . $_SERVER['REQUEST_TIME']
        ];
        View::modal($vars);
    }
    public function profileAvatarFormAction()
    {
        $uid = (int)$_POST['uid'];
        if (!isset($_SESSION['privilege']['status'])) {
            View::errorCode(403, ['message' => 'Forbidden!']);
        }
        if (!in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
            $uid = (int) $_SESSION['id'];
        }
        $userData = Users::getDataById($uid);

        if ($userData['personal']['avatar'] === '') {
            $vars = ['error' => 0, 'modal' => true, 'jsFile' => '/public/scripts/avatar-get-new.js?v=' . $_SERVER['REQUEST_TIME']];
            View::message($vars);
        }

        $userData['avatar'] = ImageProcessing::inputImage(FILE_USRGALL . "{$userData['id']}/{$userData['personal']['avatar']}", ['title' => Locale::applySingle(['string' => '{{ Account_Profile_Form_User_Avatar }}', 'vars' => [$userData['name']]])]);

        $vars = [
            'texts' => [
                'formTitle' => '{{ Account_Avatar_Form_Title }}',
                'profileAvatarFormTitle' => ['string' => '{{ Account_Profile_Form_Title }}', 'vars' => [$userData['name']]],
                'profileAvatarFormReCropTitle' => '{{ Account_Avatar_Form_Newcrop_Link }}',
                'formCancelLabel' => '{{ Cancel_Label }}'
            ],
            'userData' => $userData
        ];
        View::modal($vars);
    }
    public function profileAvatarRecropFormAction()
    {
        $vars = ['modal' => true, 'jsFile' => '/public/scripts/avatar-get-recrop.js?v=' . $_SERVER['REQUEST_TIME']];
        View::message($vars);
    }
    public function passwordResetAction()
    {
        extract(self::$route['vars']);
        $userData = Users::getForget($hash);
        if (!$userData) {
            View::errorCode(404, ['message' => 'Page not found!']);
        }
        if (!empty($_POST)) {
            if ($_POST['password'] != $_POST['check']) {
                View::message('Passwords Not Match!');
            }
            Users::passwordReset($userData, $_POST['password']);
            View::message(['message' => '{{ Action_Success }}', 'url' => '/']);
        }
        $vars = [
            'hash' => $hash,
            'texts' => [
                'formTitle' => '{{ Account_Forget_Form_Title }}',
                'authPlaceholder' => '{{ Account_Login_Form_Login_Input_Placeholder }}',
                'formSubmitLabel' => '{{ Submit_Label }}'
            ],
            'scripts' => [
                '/public/scripts/forms-admin-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
        ];
        View::render('{{ Account_Password_Reset_Title }}', $vars);
    }
    public function forgetAction()
    {
        if (!empty($_POST)) {
            $userData = Users::checkForget(trim($_POST['auth']));
            if (!empty($userData)) {
                if (isset($userData['personal']['forget'])) {
                    $hash = $userData['personal']['forget'];
                } else {
                    $hash = md5(json_encode([$userData['personal'], $userData['privilege'], $userData['contacts']]) . $_SERVER['REQUEST_TIME']);
                }
                Users::saveForget($userData, $hash);
                $link = "<a>{$_SERVER['HTTP_X_FORWARDED_PROTO']}://{$_SERVER['SERVER_NAME']}/account/password-reset/$hash</a>";
                $bot = new TelegramBot();
                $bot->sendMessage($userData['contacts']['telegramid'], Locale::applySingle(['string' => '{{ Account_Forget_Check_Succes }}', 'vars' => [$link]]));
            }
            $vars = ['message' => 'Перевірте бота!'];
            View::message($vars);
        }
        $vars = ['error' => 0, 'message' => 'Щось пішло не так. Перевірте введену інформацію!'];
        View::message($vars);
    }
    public function forgetFormAction()
    {
        $bot = new TelegramBot();
        $botData = $bot->getMe();
        $vars = [
            'texts' => [
                'formTitle' => '{{ Account_Forget_Form_Title }}',
                'authPlaceholder' => '{{ Account_Login_Form_Login_Input_Placeholder }}',
                'tgBotLink' => 'https://t.me/' . $botData['result']['username'],
                'formSubmitLabel' => '{{ Submit_Label }}',
                'formCancelLabel' => '{{ Cancel_Label }}',
            ]
        ];
        View::modal($vars);
    }
    public function registerFormAction()
    {
        $vars = [
            'texts' => [
                'formTitle' => '{{ Account_Register_Form_Title }}',
                'accountLoginLabel' => '{{ Account_Login_Label }}',
                'accountNameLabel' => '{{ Account_Name_Label }}',
                'accountPasswordLabel' => '{{ Account_Password_Label }}',
                'accountPasswordAgainLabel' => '{{ Account_Password_Again_Label }}',
                'accountRegisterSubmit' => '{{ Account_Login_Form_Register_Link }}',
                'formCancelLabel' => '{{ Cancel_Label }}',
            ]
        ];
        View::modal($vars);
    }
    public function registerAction()
    {
        $result = Users::register($_POST);
        if ($result !== true) {
            View::message($result);
        }
        View::message(['message' => '{{ Action_Success }}']);
    }
    public function deleteAction()
    {
        extract(self::$route['vars']);
        if ($userId < 2 || $_SESSION['id'] == $userId) {
            View::message(['message' => 'Wrong userID!']);
        }
        $result = Users::remove($userId);
        if ($result !== true) {
            View::message($result);
        }
        View::redirect('/users/list');
    }
}
