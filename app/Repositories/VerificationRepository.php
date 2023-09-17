<?

namespace app\Repositories;

use app\core\Locale;
use app\core\Mailer;
use app\models\Contacts;
use app\models\Settings;
use app\models\Users;
use Throwable;

class VerificationRepository
{
    
    public static function send2FAVerification(string $type = 'email'): bool
    {
        $userContacts = Contacts::getByUserId($_SESSION['id']);

        $contact = ContactRepository::setApproveData($type, $userContacts);

        $method = $type.'Verification';

        return self::$method($contact);
    }
    private static function emailVerification(array $contact){
        try {
            $mailer = new Mailer();
            $title = Locale::phrase(['string' => '<no-reply> %s - Verify your E-mail', 'vars' => [ CLUB_NAME ]]);

            $message = '';

            if (empty($contact['data']['approved'])){
                $path = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) . "://{$_SERVER['HTTP_HOST']}/account/verification/email/{$contact['data']['approve']['hash']}";
                $message .= Locale::phrase(['string' => '<p>Please follow this link to verify your email:</p><p>%s</p><p>or</p>', 'vars' => [ $path ]]);
            }
            
            $message .= Locale::phrase([
                'string' => "<p>Enter this code in the previous window:</p><p><b style='letter-spacing:0.3em'>%s</b></p>",
                'vars' => [ $_SESSION['approve_code'] ],
            ]);

            $mail = [
                'title' => $title,
                'body' => $message,
            ];
            $mailer->prepMessage($mail);
            $mailer->send($contact['contact']);
            return true;
        }
        catch(Throwable $error){
            error_log(__METHOD__.': '. $error->__toString());
            return false;
        }
    }
    public static function check2FAVerification(string $code):bool{
        return $code == $_SESSION['approve_code'];
    }
    public static function setApproved(string $type, $hash = null) {
        $contact = Contacts::getUserContact($_SESSION['id'], $type);
        $result = '<p>We can’t find your request</p><p>Or</p><p>Request has been expired!</p>';

        if (empty($contact['data']))
            return $result;

        $contact['data'] = json_decode($contact['data'], true);
        
        if (empty($contact['data']['approve']))
            return $result;

        if ($contact['data']['approve']['expired'] < $_SERVER['REQUEST_TIME'])
            return $result;

        if (!empty($hash) && $contact['data']['approve']['hash'] !== $hash)
            return $result;

        unset($contact['data']['approve']);
        $contact['data']['approved'] = $_SERVER['REQUEST_TIME'];

        Contacts::edit(['data' => $contact['data']], ['id' => $contact['id']]);
        return true;
    }
}
