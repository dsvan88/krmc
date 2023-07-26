<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\ImageProcessing;
use app\core\View;
use app\core\Locale;
use app\core\Mailer;
use app\core\TelegramBot;
use app\core\Validator;
use app\models\Weeks;
use app\models\Days;
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
    public function loginAction()
    {
        if (!empty($_POST)) {
            if (Users::login($_POST)) {
                if (isset($_SESSION['path'])) {
                    View::location($_SESSION['path']);
                }
                View::location('/');
            } else {
                View::notice(['error' => 403, 'message' => '{{ Account_Login_User_Not_Found }}']);
            }
        }
        $vars = [
            'title' => '{{ Account_Login_Form_Title }}',
            'texts' => [
                'LoginInputPlaceholder' => '{{ Account_Login_Form_Login_Input_Placeholder }}',
                'PasswordInputPlaceholder' => '{{ Account_Login_Form_Password_Input_Placeholder }}',
                'SubmitLabel' => '{{ Account_Login_Form_Submit_Title }}',
                'ForgetLinkLabel' => 'Forget Password',
                'RegisterLinkLabel' => 'Register',
            ]
        ];
        View::modal($vars);
    }
    public function listAction()
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
            View::notice(['error' => 1, 'message' => '{{ Action_Failed }}']);
        }
        if ($_SESSION['id'] != $userId  && $_SESSION['privilege']['status'] !== 'admin') {
            View::notice(['error' => 1, 'message' => 'Ви не можете змінювати інформацію інших користувачів']);
        }

        $userData = Users::getDataById($userId);
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
                'string' => '{{ Account_Profile_Form_Title }}',
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
            View::message(['error' => 1, 'text' => 'Ви не можете змінювати інформацію інших користувачів']);
        }
        if ($section === 'contacts') {
            $data = ContactRepository::getFields($userId, 'No data');
            $data = ContactRepository::wrapLinks($data);
        } elseif ($section === 'security') {
            $data = ContactRepository::checkApproved($userId);
        } else {
            $data = AccountRepository::getFields($userId);
        }
        error_log(json_encode($data));
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
            View::message(['error' => 1, 'message' => '{{ Action_Failed }}']);
        }
        if ($_SESSION['privilege']['status'] !== 'admin') {
            View::message(['error' => 1, 'message' => 'Ви не можете змінювати інформацію других користувачів']);
        }
        extract(self::$route['vars']);

        if (empty($_POST['name']) || $_POST['name'] === '-') {
            $chatData =  TelegramChats::getChat($chatId);
            View::message(['error' => 1, 'message' => 'Поки не готова можливысть видаляти прив’язку користувачів до телеграму']);
        }
        $name = trim($_POST['name']);
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
        View::message('Success!');
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
                'SaveLabel' => 'Save',
                'CancelLabel' => 'Cancel'
            ],
            'chatData' => $chatData,
            // 'scripts' => '/public/scripts/apply-input-listener.js?v=' . $_SERVER['REQUEST_TIME']
        ];
        View::modal($vars);
    }
    public function emailVerifyHashAction()
    {
        if (!isset($_SESSION['id'])) {
            View::errorCode(404, ['message' => '<p>Your aren’t authorized yet!</p><p>Please - use browser, where you made your request!</p>']);
        }
        extract(self::$route['vars']);

        $contacts = Contacts::getByUserId($_SESSION['id']);

        $emailData = [];
        foreach ($contacts as $num => $contact) {
            if ($contact['type'] !== 'email') continue;
            $emailData = $contact;
            break;
        }

        if (empty($emailData['data'])) {
            View::errorCode(404, ['message' => '<p>We can’t find your request</p><p>Or</p><p>Link has been expired!</p>']);
        }

        $emailData['data'] = json_decode($emailData['data'], true);
        if ($emailData['data']['approve']['hash'] !== $hash) {
            View::errorCode(404, ['message' => '<p>We can’t find your request</p><p>Or</p><p>Link has been expired!</p>']);
        }
        unset($emailData['data']['approve']);
        $emailData['data']['approved'] = $_SERVER['REQUEST_TIME'];
        Contacts::edit(['data' => $emailData['data']], ['id' => $emailData['id']]);

        View::redirect('/');
    }
    public function emailVerifyCodeAction()
    {
        if (!isset($_SESSION['id'])) {
            View::errorCode(404, ['message' => '<p>Your aren’t authorized yet!</p><p>Please - use browser, where you made your request!</p>']);
        }

        $code = $_POST['approval_code'];
        $contacts = Contacts::getByUserId($_SESSION['id']);

        $emailData = [];
        foreach ($contacts as $num => $contact) {
            if ($contact['type'] !== 'email') continue;
            $emailData = $contact;
            break;
        }

        if (empty($emailData['data'])) {
            View::errorCode(404, ['message' => '<p>We can’t find your request</p><p>Or</p><p>Link has been expired!</p>']);
        }

        $emailData['data'] = json_decode($emailData['data'], true);
        if ($emailData['data']['approve']['code'] !== $code) {
            View::errorCode(404, ['message' => '<p>We can’t find your request</p><p>Or</p><p>Link has been expired!</p>']);
        }
        unset($emailData['data']['approve']);
        $emailData['data']['approved'] = $_SERVER['REQUEST_TIME'];
        Contacts::edit(['data' => $emailData['data']], ['id' => $emailData['id']]);

        View::message(['message' => 'Success!', 'location' => '/account/profile/' . $_SESSION['id']]);
    }
    public function emailApproveFormAction()
    {
        $contacts = Settings::load('contacts');
        $userData = Users::getDataById($_SESSION['id']);
        $userContacts = Contacts::getByUserId($_SESSION['id']);

        $mailer = new Mailer();

        $contact = ContactRepository::setApproveData('email', $userContacts);

        $mail = [
            'title' => '<no-reply> ' . MAFCLUB_NAME . ' - Verify your E-mail',
            'body' => "
            <p>Please follow this link to verify your email:</p>
            <p>https://krmc.gigalixirapp.com/account/approve/email/{$contact['data']['approve']['hash']}</p>
            <p>or</p>
            <p>Enter this code in the previous window:</p>
            <p>{$contact['data']['approve']['code']}</p>",
        ];
        $mailer->prepMessage($mail);
        $mailer->send($contact['contact']);

        $vars = [
            'title' => 'Approve User’s Email',
            'texts' => [
                'SubmitLabel' => 'Send',
                'CancelLabel' => 'Cancel',
            ],
            'userId' => $userData['id'],
            'userName' => $userData['name'],
            'contacts' => $contacts,
        ];
        View::modal($vars);
    }
    public function telegramApproveFormAction()
    {
        $contacts = Settings::load('contacts');
        $userData = Users::getDataById($_SESSION['id']);
        $vars = [
            'title' => 'Approve User’s Telegram',
            'texts' => [
                'AgreeLabel' => 'Agree',
            ],
            'userId' => $userData['id'],
            'userName' => $userData['name'],
            'contacts' => $contacts,
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
            if ($participant['name'] !== $userData['name']) continue;
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
        if (!in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
            View::errorCode(403, ['message' => 'Something went wrong! How did you get here?']);
        }

        $vars = [
            'title' => 'Rename Temporary Player',
            'texts' => [
                'SaveLabel' => 'Save',
                'CancelLabel' => 'Cancel',
            ],
            // 'scripts' => '/public/scripts/apply-input-listener.js?v=' . $_SERVER['REQUEST_TIME'],
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
            // 'scripts' => '/public/scripts/apply-input-listener.js?v=' . $_SERVER['REQUEST_TIME'],
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
    public function passwordChangeAction()
    {
        $userId = $_SESSION['id'];
        $userData = Users::find($userId);
        if (!$userData) {
            View::errorCode(404, ['message' => 'Page not found!']);
        }
        if (!empty($_POST)) {
            if ($_POST['new_password'] != $_POST['new_password_confirmation']) {
                $message = [
                    'error' => 1,
                    'message' => Locale::phrase('Passwords Not Match!'),
                    'wrong' => 'new_password',
                ];
                View::message($message);
            }
            if (!password_verify($_POST['new_password'], $userData['password'])) {
                $message = [
                    'error' => 1,
                    'message' => Locale::phrase('Old password is wrong!'),
                    'wrong' => 'password',
                ];
                View::message($message);
            }
            Users::passwordChange($userId, $_POST['password']);
            View::message(['message' => 'Success!', 'url' => '/']);
        }

        $vars = [
            'title' => 'Change Password',
            'texts' => [
                'SubmitLabel' => 'Execute',
                'CancelLabel' => 'Cancel'
            ],
            'userId' => $userId,
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
            $bot->sendMessage($userData['contacts']['telegramid'], Locale::phrase(['string' => '{{ Account_Forget_Check_Succes }}', 'vars' => [$link]]));
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
    /* public function nameCheckAction()
    {
        if (empty($_POST))
            View::message('Empty data!');

        $userData = Users::getDataByName($_POST['name']);
        $userId = $userData['id'];
        $data = ContactRepository::getApproved($userId);

        if (!empty($data['telegramid'])) {
            $code = AccountRepository::saveTelegramApproveCode($userId);
            TelegramBotController::send($data['telegramid'], "Your verification code:\n<b>$code</b>");
            $botData = TelegramBotController::getMe();
            View::message("This account has a Telegram Account connected to it!\nYour verification code has been sent to your Telegram.\nPlease, start a dialog with our <a href='https://t.me/{$botData['result']['username']}' target='_blank'>Telegram bot</a> to get a verification code!");
        }
        $vars = [
            'title' => '{{ Account_Forget_Form_Title }}',
            'texts' => [
                'authPlaceholder' => '{{ Account_Login_Form_Login_Input_Placeholder }}',
                'SubmitLabel' => 'Execute',
                'CancelLabel' => 'Cancel',
            ]
        ];
        View::message('Success!');
    } */
    public function registerAction()
    {
        if (!empty($_POST)) {
            $result = Users::register($_POST);
            if ($result !== true) {
                View::message($result);
            }
            View::message('Success!');
        }
        $vars = [
            'title' => '{{ Account_Register_Form_Title }}',
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
}
