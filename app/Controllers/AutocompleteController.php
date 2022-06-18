<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\View;
use app\models\Users;

class AutocompleteController extends Controller
{
    public function usersAction()
    {
        $result = Users::getListNames($_POST['term']);
        View::message(['error' => 0, 'result' => $result]);
    }
    public function participantFieldAction()
    {
        $newId = (int) $_POST['id'];
        $participantNum = $newId + 1;
        $texts = [
            'participantNamePlaceholder' => '',
            'participantTimeArrivePlaceholder' => '{{ Time_Arrive }}',
            'participantRemarkPlaceHolder' => '{{ Day_Block_Prim_PLaceholder }}',
            'clearLabel' => '{{ Clear_Label }}',
        ];
        $texts = Locale::apply($texts);
        ob_start();
        require $_SERVER['DOCUMENT_ROOT'] . '/app/views/tech/participants-filed.php';
        $result = ob_get_clean();
        View::message(['error' => 0, 'html' => $result]);
    }
}
