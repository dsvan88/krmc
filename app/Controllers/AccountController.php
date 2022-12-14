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
        }
        $vars = [
            'title' => '{{ Account_Login_Form_Title }}',
            'texts' => [
                'LoginInputPlaceholder' => '{{ Account_Login_Form_Login_Input_Placeholder }}',
                'PasswordInputPlaceholder' => '{{ Account_Login_Form_Password_Input_Placeholder }}',
                'SubmitLabel' => '{{ Account_Login_Form_Submit_Title }}',
                'ForgetLinkLabel' => '{{ Account_Login_Form_Forget_link }}',
                'RegisterLinkLabel' => '{{ Account_Login_Form_Register_Link }}',
            ]
        ];
        View::modal($vars);
    }
    public function usersListAction()
    {
        $vars = [
            'title' => '{{ Users_List_Page_Title }}',
            'usersData' => Users::getList(),
            'texts' => [
                'formTitle' => '{{ Users_List_Title }}',
            ]
        ];
        View::render($vars);
    }
    public function profileEditAction()
    {
        extract(self::$route['vars']);
        if (empty($_POST)) {
            View::message(['error' => 1, 'text' => '{{ Action_Failed }}']);
        }
        if ($_SESSION['id'] != $userId  && $_SESSION['privilege']['status'] !== 'admin') {
            View::message(['error' => 1, 'text' => 'Ви не можете змінювати інформацію інших користувачів']);
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
        View::message('{{ Action_Success }}');
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

        $vars = [
            'title' => [
                'string' => '{{ Account_Profile_Form_Title }}',
                'vars' => [$userData['name']]
            ],
            'texts' => [
                'FioLabel' => '{{ Account_Profile_Form_Fio_Label }}',
                'BirthdayLabel' => '{{ Account_Profile_Form_Birthday_Label }}',
                'GenderLabel' => '{{ Account_Profile_Form_Gender_Label }}',
                'EmailLabel' => '{{ Account_Profile_Form_Email_Label }}',
                'SaveLabel' => '{{ Save_Label }}',
                'CancelLabel' => '{{ Cancel_Label }}'
            ],
            'userData' => $userData
        ];
        View::modal($vars);
    }
    public function setNicknameAction()
    {
        if (empty($_POST)) {
            View::message(['error' => 1, 'message' => '{{ Action_Failed }}']);
        }
        if ($_SESSION['privilege']['status'] !== 'admin') {
            View::message(['error' => 1, 'message' => 'Ви не можете змінювати інформацію других користувачів']);
        }
        extract(self::$route['vars']);

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
        View::message('{{ Action_Success }}');
    }
    public function setNicknameFormAction()
    {
        if (!in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
            View::errorCode(403, ['message' => 'Something went wrong! How did you get here?']);
        }

        $cid = (int)$_POST['cid'];
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
            'title' => [
                'string' => '{{ Account_Profile_Form_Title }}',
                'vars' => [$chatTitle]
            ],
            'texts' => [
                'SaveLabel' => '{{ Save_Label }}',
                'CancelLabel' => '{{ Cancel_Label }}'
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
            'title' => [
                'string' => '{{ Account_Avatar_Form_Title }}',
                'vars' => [$userData['name']],
            ],
            'texts' => [
                'ReCropLabel' => '{{ Account_Avatar_Form_Newcrop_Link }}',
                'CancelLabel' => '{{ Cancel_Label }}'
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
            'title' => '{{ Account_Forget_Form_Title }}',
            'hash' => $hash,
            'texts' => [
                'authPlaceholder' => '{{ Account_Login_Form_Login_Input_Placeholder }}',
                'SubmitLabel' => '{{ Submit_Label }}'
            ],
            'scripts' => [
                '/public/scripts/forms-admin-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
        ];
        View::render($vars);
    }
    public function forgetAction()
    {
        $bot = new TelegramBot();
        if (!empty($_POST)) {
            $userData = Users::checkForget(trim($_POST['auth']));
            if (empty($userData)) {
                View::message('Перевірте бота!');
            }
            if (isset($userData['personal']['forget'])) {
                $hash = $userData['personal']['forget'];
            } else {
                $hash = md5(json_encode([$userData['personal'], $userData['privilege'], $userData['contacts']]) . $_SERVER['REQUEST_TIME']);
            }
            Users::saveForget($userData, $hash);
            $link = "{$_SERVER['HTTP_X_FORWARDED_PROTO']}://{$_SERVER['SERVER_NAME']}/account/password-reset/$hash";
            $link = "<a href='$link'>$link</a>";
            $bot = new TelegramBot();
            $bot->sendMessage($userData['contacts']['telegramid'], Locale::applySingle(['string' => '{{ Account_Forget_Check_Succes }}', 'vars' => [$link]]));
        }
        $botData = $bot->getMe();
        $vars = [
            'title' => '{{ Account_Forget_Form_Title }}',
            'texts' => [
                'authPlaceholder' => '{{ Account_Login_Form_Login_Input_Placeholder }}',
                'tgBotLink' => 'https://t.me/' . $botData['result']['username'],
                'SubmitLabel' => '{{ Submit_Label }}',
                'CancelLabel' => '{{ Cancel_Label }}',
            ]
        ];
        View::modal($vars);
    }
    public function registerAction()
    {
        if (!empty($_POST)) {
            $result = Users::register($_POST);
            if ($result !== true) {
                View::message($result);
            }
            View::message('{{ Action_Success }}');
        }
        $vars = [
            'title' => '{{ Account_Register_Form_Title }}',
            'texts' => [
                'LoginLabel' => '{{ Account_Login_Label }}',
                'NameLabel' => '{{ Account_Name_Label }}',
                'PasswordLabel' => '{{ Account_Password_Label }}',
                'PasswordAgainLabel' => '{{ Account_Password_Again_Label }}',
                'RegisterSubmit' => '{{ Account_Login_Form_Register_Link }}',
                'CancelLabel' => '{{ Cancel_Label }}',
            ]
        ];
        View::modal($vars);
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
