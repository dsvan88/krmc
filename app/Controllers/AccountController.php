<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\ImageProcessing;
use app\core\View;
use app\core\Locale;
use app\core\TelegramBot;
use app\core\Validator;
use app\models\Weeks;
use app\models\Contacts;
use app\models\TelegramChats;
use app\models\Settings;
use app\models\Users;
use app\Repositories\AccountRepository;
use app\Repositories\ContactRepository;

class AccountController extends Controller
{
    public static function before()
    {
        // View::$layout = 'custom';
        return true;
    }
    public function logoutAction()
    {
        Users::logout();
        View::redirect('/');
    }
    public function login($data){
        error_log(json_encode($data, JSON_UNESCAPED_UNICODE));
        if (!Validator::csrfCheck() || Users::trottling()){
            View::notice(['error' => 403, 'message' => 'Try again later:)']);
        }
        if (!Users::login($data)) {
            $_SESSION['login_fails'][] = $_SERVER['REQUEST_TIME'];
            View::notice(['error' => 403, 'message' => "User isn’t found!\nCheck your login and password!"]);
        }
        View::location(isset($_SESSION['path']) ? $_SESSION['path'] : '/');
    }
    public function loginFormAction()
    {
        if (!empty($_POST)) {
            $this->login($_POST);
        }
        $vars = [
            'title' => 'Authorization form',
            'texts' => [
                'LoginInputPlaceholder' => '{{ Account_Login_Form_Login_Input_Placeholder }}',
                'PasswordInputPlaceholder' => '{{ Account_Login_Form_Password_Input_Placeholder }}',
                'SubmitLabel' => 'Log In',
                'ForgetLinkLabel' => 'Forget Password',
                'RegisterLinkLabel' => 'Register',
            ]
        ];
        View::modal($vars);
    }
    public function listAction()
    {
        $usersData = Users::getList();
        $usersData = Users::contacts($usersData);

        $vars = [
            'title' => '{{ Users_List_Page_Title }}',
            'usersData' => $usersData,
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
            View::notice(['error' => 1, 'message' => 'Fail!']);
        }
        if ($_SESSION['id'] != $userId  && $_SESSION['privilege']['status'] !== 'admin') {
            View::notice(['error' => 1, 'message' => 'You don’t have enough rights to change information about other users!']);
        }

        $userData = Users::getDataById($userId);
        $userData = Users::contacts($userData);
        unset($userData['id']);

        if ($_POST['birthday'] !== '') {
            $birthday = strtotime(trim($_POST['birthday']));

            if ($birthday > $_SERVER['REQUEST_TIME'] - 31536000) // 31536000 = 60 * 60 * 24 * 365
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
        View::notice('Success!');
    }
    public function showAction()
    {
        extract(self::$route['vars']);
        if (!in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
            $userId = (int) $_SESSION['id'];
            $isAdmin = false;
        } else {
            $isAdmin = true;
        }
        $userData = Users::getDataById($userId);

        if ($userData['personal']['avatar'] !== '') {
            $avatar = FILE_USRGALL . "{$userData['id']}/{$userData['personal']['avatar']}";
        } else {
            $avatar = Settings::getImage('empty_avatar')['value'];
        }
        $userData['avatar'] = ImageProcessing::inputImage($avatar, ['title' => Locale::phrase(['string' => '{{ Account_Profile_Form_User_Avatar }}', 'vars' => [$userData['name']]])]);
        $userData['personal']['genderName'] = Locale::phrase(ucfirst($userData['personal']['gender']));

        $vars = [
            'title' => [
                'string' => 'Agent’s profile «<b>%s</b>»',
                'vars' => [$userData['name']]
            ],
            'texts' => [
                'FioLabel' => 'Name, secondary name, middle name',
                'BirthdayLabel' => 'Birthday',
                'GenderLabel' => 'Gender',
                'EmailLabel' => 'Email',
                'SaveLabel' => 'Save',
                'CancelLabel' => 'Cancel',
            ],
            'scripts' => [
                '/public/scripts/profile.js?v=' . $_SERVER['REQUEST_TIME']
            ],
            'userId' => $userId,
            'data' => $userData,
            'isAdmin' => $isAdmin,
        ];
        View::render($vars);
    }
    public function profileSectionAction()
    {
        $userId = (int) trim($_POST['uid']);
        $section = trim($_POST['section']);

        if ($_SESSION['id'] != $userId && !in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
            View::message(['error' => 1, 'text' => 'You don’t have enough rights to change information about other users!']);
        }
        if ($section === 'contacts') {
            $data = ContactRepository::getFields($userId, 'No data');
            $data = ContactRepository::wrapLinks($data);
        } elseif ($section === 'security') {
            $data = ContactRepository::checkApproved($userId);
        } else {
            $data = AccountRepository::getFields($userId);
        }
        
        $texts = [
            'FioLabel' => 'Name, secondary name, middle name',
            'BirthdayLabel' => 'Birthday',
            'GenderLabel' => 'Gender',
            'EmailLabel' => 'E-mail',
            'TelegramLabel' => 'Telegram',
            'PhoneLabel' => 'Phone',
            'CredoLiveLabel' => 'Life Creed',
            'CredoGameLabel' => 'Gaming Creed',
            'BestQuoteLabel' => 'Favorite Quote',
            'SignatureLabel' => 'Signature',
            'SaveLabel' => 'Save',
            'CancelLabel' => 'Cancel',
        ];
        $texts = Locale::apply($texts);

        ob_start();
        require $_SERVER['DOCUMENT_ROOT'] . "/app/views/account/sections/$section.php";
        $result = ob_get_clean();

        View::message(['html' => $result]);
    }
    public function profileSectionEditAction()
    {
        extract(self::$route['vars']);

        $isAdmin = false;
        if (!in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
            $userId = (int) $_SESSION['id'];
        } else {
            $isAdmin = true;
        }

        if ($section === 'contacts') {
            $contacts = [
                'email' => Validator::validate('email', $_POST['email']),
                'telegram' => Validator::validate('telegram', $_POST['telegram']),
                'phone' => Validator::validate('phone', $_POST['phone']),
            ];
            ContactRepository::edit($userId, $contacts);
        } else if ($section === 'control' && $isAdmin) {
            $name = trim($_POST['name']);
            $status = trim($_POST['status']);
            $userData = Users::getDataById($userId);
            if ($userData['name'] !== $name) {
                $result = AccountRepository::rename($userId, $name);
                if (!$result['result']) {
                    $result['type'] = 'error';
                    View::notice($result);
                }
                $result['location'] = '/account/profile/' . $userId;
                View::notice($result);
            }
            if ($userData['privilege']['status'] !== $status) {
                $userData['privilege']['status'] = $status;
                Users::edit(['privilege' => $userData['privilege']], ['id' => $userId]);
            }
        } else {
            AccountRepository::edit($userId, $_POST);
        }
        View::notice(['message' => 'Success!', 'location' => '/account/profile/' . $userId]);
    }
    public function profileSectionEditFormAction()
    {
        $userId = (int) trim($_POST['uid']);
        $section = trim($_POST['section']);

        if (!in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
            $userId = (int) $_SESSION['id'];
        }
        if ($section === 'contacts') {
            $data = ContactRepository::getFields($userId);
        } else {
            $data = AccountRepository::getFields($userId);
        }

        $texts = [
            'FioLabel' => 'Name, secondary name, middle name',
            'BirthdayLabel' => 'Birthday',
            'GenderLabel' => 'Gender',
            'EmailLabel' => 'E-mail',
            'TelegramLabel' => 'Telegram',
            'PhoneLabel' => 'Phone',
            'CredoLiveLabel' => 'Life Creed',
            'CredoGameLabel' => 'Gaming Creed',
            'BestQuoteLabel' => 'Favorite Quote',
            'SignatureLabel' => 'Signature',
            'SaveLabel' => 'Save',
            'CancelLabel' => 'Cancel',
        ];
        $texts = Locale::apply($texts);

        ob_start();
        require $_SERVER['DOCUMENT_ROOT'] . "/app/views/account/sections/forms/$section.php";
        $result = ob_get_clean();

        View::message(['html' => $result]);
    }
    public function setNicknameAction()
    {
        if (empty($_POST)) {
            View::message(['error' => 1, 'message' => 'Fail!']);
        }
        if ($_SESSION['privilege']['status'] !== 'admin') {
            View::message(['error' => 1, 'message' => 'You don’t have enough rights to change information about other users!']);
        }
        extract(self::$route['vars']);
        
        $name = trim($_POST['name']);
        
        if (empty($name) || $name === '-') {
            AccountRepository::unlinkTelegram($chatId);
            View::message('Success!');
        }

        AccountRepository::linkTelegram($chatId, $name);
        View::message('Success!');
    }
    public function setNicknameFormAction()
    {
        if (!in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
            View::errorCode(403, ['message' => 'Something went wrong! How did you get here?']);
        }

        $cid = (int) $_POST['cid'];
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
                'string' => 'Agent’s profile «<b>%s</b>»',
                'vars' => [$chatTitle]
            ],
            'texts' => [
                'SaveLabel' => 'Save',
                'CancelLabel' => 'Cancel'
            ],
            'chatData' => $chatData,
        ];
        View::modal($vars);
    }
    public function addParticipantAction()
    {
        if (!in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
            return View::errorCode(403, ['message' => 'Something went wrong! How did you get here?']);
        }

        $result = AccountRepository::addParticipantToDay($_POST['name']);

        if ($result['result']) {
            return View::message(['name' => $result['name']]);
        }
        return View::notice(['error' => 1, 'message' => $result['message']]);
    }
    public function removeParticipantAction()
    {
        if (!in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
            View::errorCode(403, ['message' => 'Something went wrong! How did you get here?']);
        }

        $userData = Users::getDataByName($_POST['name']);
        $weekId = Weeks::currentId();
        $weekData = Weeks::weekDataById($weekId);

        $today = getdate()['wday'] - 1;

        if ($today === -1)
            $today = 6;

        foreach ($weekData['data'][$today]['participants'] as $index => $participant) {
            if ($participant['id'] !== $userData['id']) continue;
            unset($weekData['data'][$today]['participants'][$index]);
            break;
        }
        $weekData['data'][$today]['participants'] = array_values($weekData['data'][$today]['participants']);
        $weekData['data'] = json_encode($weekData['data'], JSON_UNESCAPED_UNICODE);

        Weeks::update(['data' => $weekData['data']], ['id' => $weekId]);

        View::notice('Done');
    }
    public function dummyRenameFormAction()
    {
        if (!in_array($_SESSION['privilege']['status'], ['manager', 'admin', 'root'])) {
            View::message(['error'=> 403, 'message' => 'Something went wrong! How did you get here?']);
        }

        $vars = [
            'title' => 'Rename Temporary Player',
            'texts' => [
                'SaveLabel' => 'Save',
                'CancelLabel' => 'Cancel',
            ],
        ];
        View::modal($vars);
    }
    public function addParticipantFormAction()
    {
        if (!in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
            View::errorCode(403, ['message' => 'Something went wrong! How did you get here?']);
        }

        $vars = [
            'title' => 'Add Participant',
            'texts' => [
                'SaveLabel' => 'Save',
                'CancelLabel' => 'Cancel',
            ],
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

        $userData['avatar'] = ImageProcessing::inputImage(FILE_USRGALL . "{$userData['id']}/{$userData['personal']['avatar']}", ['title' => Locale::phrase(['string' => '{{ Account_Profile_Form_User_Avatar }}', 'vars' => [$userData['name']]])]);

        $vars = [
            'title' => [
                'string' => '{{ Account_Avatar_Form_Title }}',
                'vars' => [$userData['name']],
            ],
            'texts' => [
                'ReCropLabel' => '{{ Account_Avatar_Form_Newcrop_Link }}',
                'CancelLabel' => 'Cancel'
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
    public function passwordChange($userData, $post){
        if ($post['new_password'] != $post['new_password_confirmation']) {
            $message = [
                'error' => 1,
                'message' => Locale::phrase('Passwords Not Match!'),
                'wrong' => 'new_password',
            ];
            View::message($message);
        }

        $oldPass = sha1(trim($post['password']));
        if (!password_verify($oldPass, $userData['password'])) {
            $message = [
                'error' => 1,
                'message' => Locale::phrase('Old password is wrong!'),
                'wrong' => 'password',
            ];
            View::message($message);
        }
        Users::passwordChange($_SESSION['id'], $post['new_password']);
        View::message(['message' => 'Success!', 'url' => '/']);
    }
    public function passwordChangeFormAction()
    {
        $userData = Users::find($_SESSION['id']);
        if (!$userData) {
            View::errorCode(404, ['message' => 'Page not found!']);
        }

        if (!empty($_POST)) {
            $this->passwordChange($userData, $_POST);
        }

        $vars = [
            'title' => 'Change Password',
            'texts' => [
                'SubmitLabel' => 'Execute',
                'CancelLabel' => 'Cancel'
            ],
            'userId' => $_SESSION['id'],
        ];
        View::modal($vars);
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
            View::message(['message' => 'Success!', 'url' => '/']);
        }

        $vars = [
            'title' => '{{ Account_Forget_Form_Title }}',
            'hash' => $hash,
            'texts' => [
                'authPlaceholder' => '{{ Account_Login_Form_Login_Input_Placeholder }}',
                'SubmitLabel' => 'Execute'
            ],
            'scripts' => [
                '/public/scripts/forms-admin-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
        ];
        View::render($vars);
    }
    public function forgetFormAction()
    {
        $bot = new TelegramBot();
        if (!empty($_POST)) {
            $userData = Users::checkForget(trim($_POST['auth']));
            if (empty($userData)) {
                View::message('Перевірте введені дані!');
            }
            if (isset($userData['personal']['forget'])) {
                $hash = $userData['personal']['forget'];
            } else {
                $hash = md5(json_encode([$userData['personal'], $userData['privilege']]) . $_SERVER['REQUEST_TIME']);
            }
            Users::saveForget($userData, $hash);
            $contact = Contacts::getUserContact($userData['id'], 'telegramid');
            $telegramId = $contact['contact'];
            $link = "{$_SERVER['HTTP_X_FORWARDED_PROTO']}://{$_SERVER['SERVER_NAME']}/account/password-reset/$hash";
            $link = "<a href='$link'>$link</a>";
            $bot = new TelegramBot();
            $bot->sendMessage($telegramId, Locale::phrase(['string' => '{{ Account_Forget_Check_Succes }}', 'vars' => [$link]]));
        }
        $botData = $bot->getMe();
        $vars = [
            'title' => '{{ Account_Forget_Form_Title }}',
            'texts' => [
                'authPlaceholder' => '{{ Account_Login_Form_Login_Input_Placeholder }}',
                'SubmitLabel' => 'Execute',
                'CancelLabel' => 'Cancel',
            ]
        ];

        if ($botData['ok'])
            $vars['texts']['tgBotLink'] = 'https://t.me/' . $botData['result']['username'];
        else
            $vars['texts']['tgBotLink'] = 'https://t.me/';

        View::modal($vars);
    }
    public function register($data){
        $result = Users::register($data);
        if ($result !== true) {
            View::message($result);
        }
        View::message('Success!');
    }
    public function registerFormAction()
    {
        if (!empty($_POST)) {
            if (!Validator::csrfCheck()){
                View::notice(['error' => 403, 'message' => 'Try again later:)']);
            }
            $this->register($_POST);
        }
        $vars = [
            'title' => 'Registration form',
            'texts' => [
                'LoginLabel' => 'Login',
                'NameLabel' => 'Nickname (in game)',
                'PasswordLabel' => 'Password',
                'PasswordAgainLabel' => 'Password again',
                'RegisterSubmit' => 'Register',
                'CancelLabel' => 'Cancel',
            ],
            'scripts' => [
                '/public/scripts/account-register.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
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
    public function doubles($post){

        $name = Users::formatName($post['name']);
        if (empty($name)){
            View::message(['error'=>404, 'message' => 'Not found!']);
        }

        extract(self::$route['vars']);

        $result = AccountRepository::mergeAccounts($userId, $name);

        if ($result)
            View::message(['message' => 'Success!']);
        
        View::message(['error'=>404, 'message' => 'Not found!']);
    }
    public function doublesFormAction()
    {
        if (!in_array($_SESSION['privilege']['status'], ['admin', 'root'])) {
            View::message(['error'=>404, 'message' => 'How do you get here?']);
        }
        if (!empty($_POST)){
            $this->doubles($_POST);
        }
        extract(self::$route['vars']);

        View::message(['error'=>404, 'message' => 'Not ready yet!']);
        $userData = Users::find($userId);
        
        if (empty($userData)){
            View::message(['error'=>404, 'message' => 'Not found!']);
        }

        $vars = [
            'title' => 'Merge double registration',
            'userId' => (int) $userId,
            'userData' => $userData,
            'texts' => [
                'SaveLabel' => 'Save',
                'CancelLabel' => 'Cancel',
            ],
        ];
        View::modal($vars);
    }
}
