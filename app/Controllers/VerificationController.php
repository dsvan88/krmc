<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\View;
use app\core\Mailer;
use app\core\Tech;
use app\models\Contacts;
use app\models\Settings;
use app\models\Users;
use app\Repositories\AccountRepository;
use app\Repositories\ContactRepository;

class VerificationController extends Controller
{
    public static function before()
    {
        // View::$layout = 'custom';
        return true;
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
    public function registerNameAction()
    {
        $userData = Users::getDataByName($_POST['name']);

        if (empty($userData)) {
            View::message([
                'result' => false,
                'message' => "This nickname is not among those registered on the site.\nYou can only register after you play at least one game in our club!",
            ]);
        }

        if (!empty($userData['login'])) {
            View::message([
                'result' => false,
                'message' => 'This nickname is alerady has account on this site.',
            ]);
        }

        $userId = (int) $userData['id'];
        $approved = ContactRepository::getApproved($userId);

        if (empty($approved['telegramid'])) {
            View::message(['result' => false]);
        }

        $code = Tech::getCode(json_encode($userData));
        AccountRepository::saveTelegramApproveCode($userData, $code);
        TelegramBotController::send($approved['telegramid'], "Your verification code:\n<b>$code</b>");
        $botData = TelegramBotController::getMe();

        $message = Locale::phrase([
            'string' => "This account has a Telegram Account connected to it!\nYour verification code has been sent to your Telegram.\nPlease, start a dialog with our <a href='https://t.me/%s' target='_blank'>Telegram bot</a> to get a verification code!",
            'vars' => [
                $botData['result']['username']
            ],
        ]);

        View::message($message);
    }
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
}
