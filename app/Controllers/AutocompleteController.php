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
        return View::message(['error' => 0, 'result' => $result]);
    }
    public function participantFieldAction()
    {
        $texts = [
            'NamePlaceholder' => '',
            'ArrivePlaceHolder' => 'Arrive',
            'RemarkPlaceHolder' => 'Remark',
            'clearLabel' => 'Clear',
        ];
        View::$route['vars']['texts'] = Locale::apply($texts);
        View::$route['vars']['participantId'] = (int) $_POST['id'];
        View::$route['vars']['path'] = 'components/participants-field';

        return View::html();
    }
}
