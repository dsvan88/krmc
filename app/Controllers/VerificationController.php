<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\View;
use app\core\Sender;
use app\core\Tech;
use app\models\Users;
use app\Repositories\ContactRepository;
use app\Repositories\VerificationRepository;

class VerificationController extends Controller
{
    public static function before()
    {
        // View::$layout = 'custom';
        return true;
    }

    public static function rootAction(){
        $message = Locale::phrase("This action requires root right!\nApprove your rights with the root password:");
        View::message($message);
    }
    public function emailVerifyHashAction()
    {
        if (!isset($_SESSION['id'])) {
            View::errorCode(404, ['message' => '<p>Your aren’t authorized yet!</p><p>Please - use browser, where you made your request!</p>']);
        }
        extract(self::$route['vars']);

        if (empty($hash)){
            View::errorCode(404, ['message' => '<p>We can’t find your request</p><p>Or</p><p>Link has been expired!</p>']);
        }

        $result = VerificationRepository::setApproved('email', $hash);
        if (is_string($result)){
            View::errorCode(404, ['message' => $result ]);
        }
        View::redirect('/account/profile/' . $_SESSION['id']);
    }
    public function emailVerificationAction()
    {
        if (!isset($_SESSION['id'])) {
            View::errorCode(404, ['message' => '<p>Your aren’t authorized yet!</p><p>Please - use browser, where you made your request!</p>']);
        }
        if (!empty($_POST) && VerificationRepository::check2FAVerification(trim($_POST['approval_code']))){
            $result = VerificationRepository::setApproved('email');
            if (is_string($result)){
                View::notice(['type' => 'error', 'message' => $result, 'location' => "/account/profile/{$_SESSION['id']}/"]);
            }
            View::notice(['message' => 'Success!', 'location' => "/account/profile/{$_SESSION['id']}/"]);
        }

        if (!VerificationRepository::send2FAVerification('email')){
            View::message([
                'result' => false,
                'message' => 'Something went wrong!',
            ]);
        }

        View::message("To confirm your action, we have sent an email to your email adress.\nPlease, paste the code from it here:\n");
    }
    public function registerNameAction()
    {
        $userData = Users::getDataByName(trim($_POST['name']));
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
            View::message(['result' => true]);
        }

        if (empty($_SESSION['tg-code'])){
            $_SESSION['tg-code'] = Tech::getCode($_SERVER['HTTP_USER_AGENT'] . Tech::getClientIP() . date('W.F.Y'));
        }

        $message = Locale::phrase([
            'string' => "Your verification code:\n<b>%s</b>",
            'vars' => [ $_SESSION['tg-code'] ],
        ]);
        
        Sender::message($approved['telegramid'], $message);
        $botData = Sender::getMe();

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
                '/public/scripts/account-register.js',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
        View::modal();
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
