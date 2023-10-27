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
    public function login($data)
    {
        if (!Validator::csrfCheck() || Users::trottling()) {
            View::notice(['error' => 403, 'message' => 'Try again later:)', 'time' => 2000]);
        }
        $result = Users::login($data);
        if ($result === 'banned') {
            View::notice(['error' => 403, 'message' => "User isn’t found!\nCheck your login and password!", 'time' => 2000]);
        }
        if ($result === 'failed') {
            $_SESSION['login_fails'][] = $_SERVER['REQUEST_TIME'];
            View::notice(['error' => 403, 'message' => "User isn’t found!\nCheck your login and password!", 'time' => 2000]);
        }
        
        View::notice(['message' => 'Success!', 'location' => isset($_SESSION['path']) ? $_SESSION['path'] : '/', 'time' => 700]);
    }
    public function loginFormAction()
    {
        if (!empty($_POST)) {
            $this->login($_POST);
        }
        $vars = [
            'title' => 'Authorization form',
            'texts' => [
                'LoginInputPlaceholder' => 'Login',
                'PasswordInputPlaceholder' => '{{ Account_Login_Form_Password_Input_Placeholder }}',
                'SubmitLabel' => 'Log In',
                'ForgetLinkLabel' => 'Forget Password',
                'RegisterLinkLabel' => 'Register',
            ]
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
        View::modal();
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
            ],
        ];

        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        View::render();
    }
    public function showAction()
    {
        extract(self::$route['vars']);
        if (!Users::checkAccess('manager')) {
            $userId = (int) $_SESSION['id'];
            $isAdmin = false;
        } else {
            $isAdmin = true;
        }
        $userData = Users::getDataById($userId);

        $avatar = empty($userData['personal']['avatar']) ? Settings::getImage('empty_avatar')['value'] : FILE_USRGALL . "{$userData['id']}/{$userData['personal']['avatar']}";
        
        $userData['avatar'] = ImageProcessing::inputImage($avatar, ['title' => Locale::phrase(['string' => '{{ Account_Profile_Form_User_Avatar }}', 'vars' => [$userData['name']]])]);
        $userData['personal']['genderName'] = empty($userData['personal']['gender']) ? '' : Locale::phrase(ucfirst($userData['personal']['gender']));

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
                'profile.js',
            ],
            'userId' => $userId,
            'data' => $userData,
            'isAdmin' => $isAdmin,
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        View::render();
    }
    public function profileSectionAction()
    {
        $userId = (int) trim($_POST['uid']);
        $section = trim($_POST['section']);

        if ($_SESSION['id'] != $userId && !Users::checkAccess('manager')) {
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
        if ($section === 'control' && !empty($data['ban'])){
            if ($data['ban']['expired'] < $_SERVER['REQUEST_TIME'])
                $data['ban'] = null;
            else {
                $data['ban']['expired'] = date('d.m.Y H:i', $data['ban']['expired']);
                foreach($data['ban'] as $key => $value){
                    if ($key === 'expired') continue;
                    $data['ban']['options'][$key] = Locale::phrase(ucfirst($key));
                }
                $data['ban']['options'] = implode('</span>, <span class="text-accent">', $data['ban']['options']);
            }
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
        if (!Users::checkAccess('manager')) {
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

        if (!Users::checkAccess('manager')) {
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
        if (!Users::checkAccess('admin')) {
            View::message(['error' => 1, 'message' => 'You don’t have enough rights to change information about other users!']);
        }
        extract(self::$route['vars']);

        AccountRepository::unlinkTelegram($chatId);

        $name = trim($_POST['name']);
        if (empty($name) || $name === '-') {
            View::message('Success!');
        }

        AccountRepository::linkTelegram($chatId, $name);
        View::message('Success!');
    }
    public function setNicknameFormAction()
    {
        if (!Users::checkAccess('manager')) {
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
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
        View::modal();
    }
    public function addParticipantAction()
    {
        if (!Users::checkAccess('manager')) {
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
        if (!Users::checkAccess('manager')) {
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
        if (!Users::checkAccess('manager')) {
            View::message(['error' => 403, 'message' => 'Something went wrong! How did you get here?']);
        }

        if (!empty($_POST)) {

            $name = Users::formatName(trim($_POST['name']));

            if (empty($name)) View::message('Fail!');

            if (AccountRepository::renameDummy($name)) {
                View::message(['name' => $name, 'message' => 'Success!']);
            }

            View::message('Fail!');
        }

        $vars = [
            'title' => 'Rename Temporary Player',
            'texts' => [
                'SaveLabel' => 'Save',
                'CancelLabel' => 'Cancel',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
        View::modal();
    }
    public function addParticipantFormAction()
    {
        if (!Users::checkAccess('manager')) {
            View::errorCode(403, ['message' => 'Something went wrong! How did you get here?']);
        }

        $vars = [
            'title' => 'Add Participant',
            'texts' => [
                'SaveLabel' => 'Save',
                'CancelLabel' => 'Cancel',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
        View::modal();
    }
    public function profileAvatarFormAction()
    {
        $uid = (int)$_POST['uid'];
        if (!isset($_SESSION['privilege']['status'])) {
            View::errorCode(403, ['message' => 'Forbidden!']);
        }
        if (!Users::checkAccess('manager')) {
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
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
        View::modal();
    }
    public function profileAvatarRecropFormAction()
    {
        $vars = ['modal' => true, 'jsFile' => '/public/scripts/avatar-get-recrop.js?v=' . $_SERVER['REQUEST_TIME']];
        View::message($vars);
    }
    public function passwordChange($userData, $post)
    {
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
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
        View::modal();
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
                'forms-admin-funcs.js',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        View::render();
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

        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
        View::modal();
    }
    public function banAction(){
        extract(self::$route['vars']);
        $_POST['ban']['expired'] = strtotime($_POST['ban']['expired']);
        $ban = $_POST['ban'];
        $banOptions = [ 'booking', 'auth', 'chat' ];

        $count = count($banOptions);
        for ($i=0; $i < $count; $i++) { 
            if (!isset($ban[$banOptions[$i]])) continue;
            $ban[$banOptions[$i]] = true;
        }
        
        $user = Users::getDataById($userId);
        if (count($ban) === 1 || $ban['expired'] < $_SERVER['REQUEST_TIME']){
            if (Users::unban($userId))
                View::notice(Locale::phrase(['string' => 'User «<b>%s</b>» successfuly unbanned!', 'vars' => [ $user['name'] ]]));
            View::notice(['error' => 1, 'message' => 'Something went wrong']);
        }

        if (Users::ban($userId, $ban))
            View::notice(Locale::phrase(['string' => 'User «<b>%s</b>» successfuly banned to %s!', 'vars' => [ $user['name'], date('d.m.y H:i:s', $ban['expired']) ]]));
        View::notice(['error' => 1, 'message' => 'Something went wrong']);
    }
    public function banFormAction(){
        
        $userId = (int) $_POST['userId'];

        $user = Users::getDataById($userId);
        $bannedTime = !empty($user['ban']['expired']) && $user['ban']['expired'] > $_SERVER['REQUEST_TIME'] ? date('Y-m-d', $user['ban']['expired']).'T'.date('H:i', $user['ban']['expired']) : date('Y-m-d') . 'T23:59';

        $vars = [
            'title' => ['string' => 'Ban user «<b>%s</b>»', 'vars' => [$user['name']]],
            'texts' => [
                'bannedTo' => 'Ban time',
                'options' => 'Ban options',
                'booking' => 'Booking',
                'auth' => 'Authentication',
                'chat' => 'Chat',
                'SubmitLabel' => 'Execute',
                'CancelLabel' => 'Cancel',
            ],
            'user' => $user,
            'bannedTime' => $bannedTime,
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
        View::modal();
    }
    public function register($data)
    {
        $result = Users::register($data);
        if ($result !== true) {
            View::message($result);
        }
        View::message('Success!');
    }
    public function registerFormAction()
    {
        if (!empty($_POST)) {
            if (!Validator::csrfCheck()) {
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
                '/public/scripts/account-register.js',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
        View::modal();
    }
    public function deleteAction()
    {
        if (!Validator::validate('rootpass', trim($_POST['verification']))) {
            View::notice(['error' => 1, 'message' => 'Something went wrong']);
        }

        $userId = (int) trim($_POST['userId']);
        if ($userId < 2 || $_SESSION['id'] == $userId) {
            View::notice(['error' => 1, 'message' => 'Something went wrong']);
        }

        if (Users::remove($userId)) {
            View::notice(['message' => 'Success!', 'location' => '/users/list']);
        }

        View::notice(['error' => 1, 'message' => 'Something went wrong']);
    }
    public function doubles($post)
    {

        $name = Users::formatName($post['name']);
        if (empty($name)) {
            View::message(['error' => 404, 'message' => 'Not found!']);
        }

        extract(self::$route['vars']);

        $result = AccountRepository::mergeAccounts($userId, $name);

        if ($result)
            View::message(['message' => 'Success!']);

        View::message(['error' => 404, 'message' => 'Not found!']);
    }
    public function doublesFormAction()
    {
        if (!Users::checkAccess('admin')) {
            View::message(['error' => 403, 'message' => 'Something went wrong! How did you get here?']);
        }
        if (!empty($_POST)) {
            $this->doubles($_POST);
        }
        extract(self::$route['vars']);

        View::message(['error' => 404, 'message' => 'Not ready yet!']);
        $userData = Users::find($userId);

        if (empty($userData)) {
            View::message(['error' => 404, 'message' => 'Not found!']);
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
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
        View::modal();
    }
}
