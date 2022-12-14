<?php

namespace app\models;

use app\core\Model;
use app\core\Tech;

class Users extends Model
{
    public static $genders = ['', 'господин', 'госпожа', 'некто'];
    public static $statuses = ['Гость', 'Резидент', 'Мастер'];
    public static $usersAccessLevels = ['', 'guest', 'user', 'manager', 'admin'];
    public static $userToken = '';

    public static function login($data)
    {
        $login = strtolower(trim($data['login']));
        $password = sha1(trim($data['password']));

        $table = SQL_TBL_USERS;
        $authData = self::query("SELECT * FROM $table WHERE login = :login OR contacts->>'email' = :login LIMIT 1", ['login' => $login], 'Assoc');
        if (empty($authData)) return false;
        $authData =  $authData[0];
        if (password_verify($password, $authData['password'])) {
            $authData = self::decodeJson($authData);
            self::setSessionData($authData);
            self::setToken($authData);

            if (isset($_SESSION['personal']['forget'])) {
                self::deleteForget($_SESSION['id'], $_SESSION['personal']);
                unset($_SESSION['personal']['forget']);
            }
            return true;
        }
        return false;
    }
    public static function logout()
    {
        $userId = $_SESSION['id'];
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                $_SERVER['REQUEST_TIME'] - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        setcookie(
            CFG_TOKEN_NAME,
            '',
            $_SERVER['REQUEST_TIME'] - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
        session_destroy();
        $userData = self::getDataById($userId);
        unset($userData['id']);
        $userData['personal']['token'] = '';
        self::edit($userData, ['id' => $userId]);

        return true;
    }
    public static function sessionReturn($token)
    {
        $userData = self::getDataByToken($token);

        if (!$userData) return false;

        if ($_COOKIE[CFG_TOKEN_NAME] !== self::prepeareToken($userData['login'])) {
            self::logout();
            return false;
        }
        return self::setSessionData($userData);
    }
    public static function prepeareToken(string $login): string
    {
        self::$userToken = sha1($login . $_SERVER['HTTP_USER_AGENT'] . Tech::getClientIP() . date('W.F.Y'));
        return self::$userToken;
    }
    public static function setSessionData($userData)
    {
        $_SESSION['id'] = $userData['id'];
        $_SESSION['name'] = $userData['name'];
        $_SESSION['login'] = $userData['login'];
        $_SESSION['privilege'] = $userData['privilege'];
        $_SESSION['personal'] = $userData['personal'];
        $_SESSION['fio'] = $_SESSION['personal']['fio'];
        $_SESSION['gender'] = $_SESSION['personal']['gender'];
        $_SESSION['avatar'] = $_SESSION['personal']['avatar'];

        if ($_SESSION['privilege']['status'] === '')
            $_SESSION['privilege']['status'] = 'user';

        return true;
    }
    public static function setToken($userData)
    {
        if (empty(self::$userToken)) {
            self::$userToken = self::prepeareToken($userData['login']);
        }
        setcookie(CFG_TOKEN_NAME, self::$userToken, $_SERVER['REQUEST_TIME'] + CFG_MAX_SESSION_AGE, '/');

        $userId = $userData['id'];
        unset($userData['id']);
        $userData['personal']['token'] = self::$userToken;
        self::edit($userData, ['id' => $userId]);
        return true;
    }
    public static function register($data)
    {
        if (self::isUserExists($data['login'])) {
            return [
                'error' => 1,
                'message' => "Користувач з таким логіном - вже існує!\nОберіть, будь-ласка, інший",
                'wrong' => 'login'
            ];
        }
        if (self::isNameFree($data['name'])) {
            return [
                'error' => 1,
                'message' => "Гравeць з таким іменем, вже зареєстрований!\nБудь-ласка, вкажіть своє ім’я у нашому клубі!",
                'wrong' => 'name'
            ];
        }
        $uid = self::getId($data['name']);
        if ($uid <= 1) {
            return [
                'error' => 1,
                'message' => "Гравця з таким іменем, у базі не існує або псевдонім вже зайнятий!\nБудь-ласка, відвідайте хоча б одну гру у нашому клубі",
                'wrong' => 'name'
            ];
        }
        if ($data['password'] !== $data['chk_password']) {
            return [
                'error' => 1,
                'message' => 'Паролі не співпадають!',
                'wrong' => 'password'
            ];
        }
        $userData = [
            'login' => strtolower($data['login']),
            'password' => password_hash(sha1($data['password']), PASSWORD_DEFAULT),
            /* 'email' => strtolower($data['email']),
            'telegram' => strtolower($data['telegram']), */
        ];
        $userData['id'] = self::edit($userData, ['id' => $uid]);

        if (!$userData['id']) return [
            'error' => 1,
            'message' => 'Користувач не був доданий. Перевірте дані або зверніться до адміністратора',
            'wrong' => 'login'
        ];
        return true;
    }
    public static function checkForget($login)
    {
        $table = SQL_TBL_USERS;
        $authData = self::query("SELECT id, name, login, password, privilege, personal, contacts FROM $table WHERE login = :login OR name = :login OR contacts->>'email' = :login LIMIT 1", ['login' => $login], 'Assoc');
        return self::decodeJson($authData[0]);
    }
    public static function saveForget($userData, $hash)
    {
        $uid = $userData['id'];
        $personal = $userData['personal'];
        $personal['forget'] = $hash;
        self::edit(['personal' => $personal], ['id' => $uid]);
        return true;
    }
    public static function deleteForget($uid, $personal)
    {
        unset($personal['forget']);
        self::edit(['personal' => $personal], ['id' => $uid]);
        return true;
    }
    public static function getForget($hash)
    {
        $table = SQL_TBL_USERS;
        $userData = self::query("SELECT id,personal FROM $table WHERE personal->>'forget' = :hash LIMIT 1", ['hash' => $hash], 'Assoc');
        if (!empty($userData)) {
            $userData[0]['personal'] = json_decode($userData[0]['personal'], true);
            return $userData[0];
        }
        return false;
    }
    public static function passwordReset($userData, $password)
    {
        $table = SQL_TBL_USERS;
        $userId = $userData['id'];
        unset($userData['id']);
        unset($userData['personal']['forget']);

        $userData['password'] = password_hash(sha1($password), PASSWORD_DEFAULT);

        self::edit($userData, ['id' => $userId]);
        return true;
    }
    public static function getList()
    {
        $table = SQL_TBL_USERS;
        $result = self::query("SELECT * FROM $table ORDER BY id", [], 'Assoc');

        if (!$result) return $result;

        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['password'] = '***';
            $result[$i] = self::decodeJson($result[$i]);
        }

        return $result;
    }
    public static function getListNames($name)
    {
        $table = SQL_TBL_USERS;
        $result = self::query("SELECT name FROM $table WHERE name ILIKE ? ORDER BY id", ["%$name%"], 'Num');

        if (!$result) return $result;

        return $result;
    }
    // Получение ID в системе по никнейму в игре
    public static function getId($name, $free = false)
    {
        $table = SQL_TBL_USERS;
        $condArray = [$name];
        $addCondition = '';
        if ($free) {
            $addCondition = 'AND login != ?';
            $condArray[] = '';
        }
        $result = self::query("SELECT id FROM $table WHERE name ILIKE ? $addCondition LIMIT 1", $condArray, 'Column');

        if ($result)
            return $result;

        return 0;
    }
    // Поверка, зарегистрирован ли логин у конкретного игрока
    public static function isNameFree($name)
    {
        $table = SQL_TBL_USERS;
        $result = self::query("SELECT id FROM $table WHERE name ILIKE ? AND login != ? LIMIT 1", [$name, ''], 'Column');

        if ($result)
            return true;

        return false;
    }
    // Получить всю информацию об игроке по его ID
    public static function getDataById($id)
    {
        $table = SQL_TBL_USERS;
        $userData = self::query("SELECT * FROM $table WHERE id = ? LIMIT 1", [$id], 'Assoc');
        if (!$userData || empty($userData)) return false;

        $userData = $userData[0];
        unset($userData['password']);

        return self::decodeJson($userData);
    }
    // Получить всю информацию об игроке по его ID
    public static function getDataByName($name)
    {
        $table = SQL_TBL_USERS;
        $userData = self::query("SELECT * FROM $table WHERE name ILIKE ? LIMIT 1", [$name], 'Assoc');
        if (!$userData || empty($userData)) return false;

        $userData = $userData[0];
        unset($userData['password']);

        return self::decodeJson($userData);
    }
    // Получить всю информацию об игроке по его TelegramID
    public static function getDataByTelegramId($tgId)
    {
        $table = SQL_TBL_USERS;
        $userData = self::query("SELECT * FROM $table WHERE contacts->>'telegramid' = ? LIMIT 1", [$tgId], 'Assoc');
        if (!$userData || empty($userData)) return false;

        $userData = $userData[0];
        unset($userData['password']);

        return self::decodeJson($userData);
    }
    public static function getDataByToken($token)
    {
        $table = SQL_TBL_USERS;
        $userData = self::query("SELECT * FROM $table WHERE personal->>'token' = ? LIMIT 1", [$token], 'Assoc');
        if (!empty($userData)) {
            return self::decodeJson($userData[0]);
        }
        return false;
    }
    public static function isUserExists($login)
    {
        return self::isExists(['login' => $login], SQL_TBL_USERS);
    }
    public static function edit($data, $where)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
        }
        return self::update($data, $where, SQL_TBL_USERS);
    }
    public static function add($name)
    {
        return self::insert(['name' => $name], SQL_TBL_USERS);
    }
    public static function remove($uid)
    {
        return self::delete($uid, SQL_TBL_USERS);
    }
    public static function decodeJson($userData)
    {
        $userData['privilege'] = json_decode($userData['privilege'], true);
        $userData['personal'] = json_decode($userData['personal'], true);
        $userData['contacts'] = json_decode($userData['contacts'], true);
        return $userData;
    }
}
