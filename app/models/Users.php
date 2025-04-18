<?php

namespace app\models;

use app\core\Locale;
use app\core\Model;
use app\core\Tech;
use app\Repositories\ContactRepository;

class Users extends Model
{
    public static $table = SQL_TBL_USERS;
    public static $genders = ['', 'господин', 'госпожа', 'некто'];
    public static $statuses = ['Гость', 'Резидент', 'Мастер'];
    public static $usersAccessLevels = ['', 'guest', 'user', 'trusted', 'manager', 'admin', 'root'];
    public static $userToken = '';
    public static $jsonFields = ['privilege', 'personal', 'contacts', 'credo', 'ban'];
    public static $current = null;

    public static function auth(int $userId): bool
    {
        self::$current = self::find($userId);

        if (!self::$current) return false;

        self::setSessionData();
        self::setToken();

        return true;
    }
    public static function login($data)
    {
        $login = strtolower(trim($data['login']));
        $password = sha1(trim($data['password']));

        $authData = self::findBy('login', $login, 1);

        if (empty($authData)) return 'failed';
        $authData =  $authData[0];

        if (!password_verify($password, $authData['password']))
            return 'failed';

        if (self::isBanned('auth', $authData['ban']))
            return 'banned';

        self::$current = $authData;
        self::setSessionData();
        self::setToken();

        if (isset($_SESSION['personal']['forget'])) {
            self::deleteForget($_SESSION['id'], $_SESSION['personal']);
            unset($_SESSION['personal']['forget']);
        }
        if (!empty($_SESSION['login_fails'])) {
            unset($_SESSION['login_fails']);
        }
        return true;
    }
    public static function logout()
    {
        if (!isset($_SESSION['id'])) return true;
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
        $userData = self::find($userId);
        unset($userData['id']);
        $userData['personal']['token'] = '';
        self::edit($userData, ['id' => $userId]);

        return true;
    }
    public static function sessionReturn($token)
    {
        self::$current = self::getDataByToken($token);

        if (!self::$current) return false;

        if ($_COOKIE[CFG_TOKEN_NAME] !== self::prepeareToken(self::$current['login'])) {
            self::logout();
            return false;
        }
        return self::setSessionData();
    }
    public static function prepeareToken(string $login): string
    {
        self::$userToken = sha1($login . $_SERVER['HTTP_USER_AGENT'] . Tech::getClientIP() . date('W.F.Y'));
        return self::$userToken;
    }
    public static function setSessionData()
    {
        $_SESSION['id'] = self::$current['id'];
        $_SESSION['name'] = self::$current['name'];
        $_SESSION['login'] = self::$current['login'];
        $_SESSION['privilege'] = self::$current['privilege'];
        $_SESSION['personal'] = self::$current['personal'];

        $_SESSION['fio'] = empty($_SESSION['personal']['fio']) ? '' : $_SESSION['personal']['fio'];
        $_SESSION['gender'] = empty($_SESSION['personal']['gender']) ? '' : $_SESSION['personal']['gender'];
        $_SESSION['avatar'] = empty($_SESSION['personal']['avatar']) ? '' : $_SESSION['personal']['avatar'];

        if (empty($_SESSION['privilege']['status']))
            $_SESSION['privilege']['status'] = 'user';

        return true;
    }
    public static function setToken()
    {
        if (empty(self::$userToken)) {
            self::$userToken = self::prepeareToken(self::$current['login']);
        }
        setcookie(CFG_TOKEN_NAME, self::$userToken, $_SERVER['REQUEST_TIME'] + CFG_MAX_SESSION_AGE, '/');

        $userId = self::$current['id'];
        unset(self::$current['id']);
        self::$current['personal']['token'] = self::$userToken;
        self::edit(self::$current, ['id' => $userId]);
        return true;
    }
    public static function trottling(): bool
    {
        if (empty($_SESSION['login_fails']) || ($count = count($_SESSION['login_fails'])) < PASS_FAIL_MIN) return false;
        if ($_SESSION['login_fails'][$count - 1] + PASS_FAIL_TROTTLING * ($count - 3) < $_SERVER['REQUEST_TIME']) return false;
        return true;
    }
    public static function register($data)
    {
        if ($data['password'] !== $data['chk_password']) {
            return [
                'error' => 1,
                'message' => 'Паролі не співпадають!',
                'wrong' => 'password'
            ];
        }
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
                'message' => "Гравeць з таким іменем, вже кимось зареєстрований!\nБудь-ласка, вкажіть своє ім’я у нашому клубі!",
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
        if (empty($_SESSION['tg-code'])) {
            $approved = ContactRepository::getApproved($uid);
            if (!empty($approved['telegramid']))
                return [
                    'error' => 1,
                    'message' => 'Застарілий код верифікації. Будь-ласка, спробуйте ще раз!',
                ];
        }

        if (!empty($_SESSION['tg-code']) && (empty($data['code']) || $data['code'] != $_SESSION['tg-code'])) {
            return [
                'error' => 1,
                'message' => 'Не вірний код верифікації!',
            ];
        }

        $userData = [
            'login' => strtolower($data['login']),
            'password' => password_hash(sha1($data['password']), PASSWORD_DEFAULT),
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
        $table = self::$table;
        $query = "SELECT id, name, login, password, privilege, personal, contacts FROM $table WHERE login = :login OR name = :login OR contacts->'$.email' = :login LIMIT 1";
        $authData = self::query($query, ['login' => $login], 'Assoc');
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
        $table = self::$table;
        $userData = self::query("SELECT id,personal FROM $table WHERE personal->'$.forget' = :hash LIMIT 1", ['hash' => $hash], 'Assoc');
        if (!empty($userData)) {
            $userData[0]['personal'] = json_decode($userData[0]['personal'], true);
            return $userData[0];
        }
        return false;
    }
    public static function passwordReset($userData, $password)
    {
        $userId = $userData['id'];
        unset($userData['id']);
        unset($userData['personal']['forget']);

        $userData['password'] = password_hash(sha1($password), PASSWORD_DEFAULT);

        self::edit($userData, ['id' => $userId]);
        return true;
    }
    public static function passwordChange($userId, $password)
    {
        $newPassword = password_hash(sha1(trim($password)), PASSWORD_DEFAULT);

        self::edit(['password' => $newPassword], ['id' => $userId]);
        return true;
    }

    public static function getList(int $offset = 0, int $limit = 0)
    {
        $table = self::$table;
        $range = '';
        if (!empty($limit)) {
            $range .= ' LIMIT ' . $limit;
        }
        if (!empty($offset)) {
            $range .= ' OFFSET ' . $offset;
        }
        $result = self::query("SELECT * FROM $table ORDER BY id $range", [], 'Assoc');

        if (!$result) return $result;

        $count = count($result);
        for ($i = 0; $i < $count; $i++) {
            $result[$i]['password'] = '***';
            $result[$i] = self::decodeJson($result[$i]);
        }

        return $result;
    }
    public static function getListNames($name)
    {
        $table = self::$table;
        $name = mb_strtolower($name, 'UTF-8');
        // $result = self::query("SELECT name FROM $table WHERE name ILIKE ? ORDER BY id", ["%$name%"], 'Num');
        $result = self::query("SELECT name FROM $table WHERE LOWER( name ) LIKE ? ORDER BY id", ["%$name%"], 'Num');
        return empty($result) ? [] : $result;
    }
    // Получение ID в системе по никнейму в игре
    public static function getId($name, $free = false)
    {
        $table = self::$table;
        $name = mb_strtolower($name, 'UTF-8');
        // $result = self::query("SELECT id FROM $table WHERE name ILIKE ? LIMIT 1", [$name], 'Column');
        $result = self::query("SELECT id FROM $table WHERE LOWER( name ) = ? LIMIT 1", [$name], 'Column');

        return empty($result) ? 0 : $result;
    }
    // Поверка, зарегистрирован ли логин у конкретного игрока
    public static function isNameFree($name)
    {
        $table = self::$table;
        $name = mb_strtolower($name, 'UTF-8');
        // $result = self::query("SELECT id FROM $table WHERE name ILIKE ? AND login != ? LIMIT 1", [$name, ''], 'Column');
        $result = self::query("SELECT id FROM $table WHERE LOWER( name ) = ? AND login != ? LIMIT 1", [$name, ''], 'Column');

        return !empty($result);
    }
    // Получить всю информацию об игроке по его ID
    public static function find(int $id, bool $full = false)
    {
        $userData = parent::find($id);
        if (empty($userData)) return false;

        if (!$full)
            unset($userData['password']);

        return $userData;
    }
    // Получить всю информацию об игроке по его никнейму
    public static function getDataByName($name)
    {
        $table = self::$table;
        $name = mb_strtolower($name, 'UTF-8');
        $userData = self::query("SELECT * FROM $table WHERE LOWER( name ) = ? LIMIT 1", [$name], 'Assoc');
        if (empty($userData)) return false;

        $userData = $userData[0];
        unset($userData['password']);

        return self::decodeJson($userData);
    }
    /** Получить данные пользователей в случайном количестве
     * @param int $count - количество
     * 
     * @return array $users
     * */
    public static function random($count = 1)
    {
        $table = self::$table;

        // $query = "SELECT * FROM tbl AS t1 JOIN (SELECT id FROM tbl ORDER BY RAND() LIMIT $count) as t2 ON t1.id=t2.id"; query for Big tables
        $userData = self::query("SELECT * FROM $table WHERE name != ? ORDER BY RAND() LIMIT $count", [''], 'Assoc');
        if (empty($userData)) return false;

        for ($i = 0; $i < count($userData); $i++) {
            $userData[$i]['password'] = '***';
            $userData[$i] = self::decodeJson($userData[$i]);
        }

        return $userData;
    }
    /** Получить данные пользователей в случайном количестве
     * @param array $players - массив игроков
     * 
     * @return array $users
     * */
    public static function getByList($players, $column = 'name')
    {
        $usersData = self::findGroup($column, $players, count($players));
        if (empty($usersData)) return false;

        return $usersData;
    }
    // // Получить всю информацию об игроке по его TelegramID
    // public static function getDataByTelegramId($tgId)
    // {
    //     $table = self::$table;
    //     $query = "SELECT * FROM $table WHERE contacts->'$.telegramid' = ? LIMIT 1";
    //     $userData = self::query($query, [$tgId], 'Assoc');
    //     if (empty($userData)) return false;

    //     $userData = $userData[0];
    //     unset($userData['password']);

    //     return self::decodeJson($userData);
    // }
    public static function getDataByToken($token)
    {
        $table = self::$table;
        $query = "SELECT * FROM $table WHERE personal->'$.token' = ? LIMIT 1";
        $userData = self::query($query, [$token], 'Assoc');
        if (!empty($userData)) {
            return self::decodeJson($userData[0]);
        }
        return false;
    }
    public static function assingIds($players)
    {
        $users = self::getByList($players);
        $playersCount = count($players);

        if (count($users) !== $playersCount) {
            die('something wrong wing players counts!');
        }
        $result = array_fill(0, $playersCount, []);

        foreach ($users as $user) {
            $index = array_search($user['name'], $players, true);
            $result[$index] = [
                'id' => $user['id'],
                'role' => 'peace',
            ];
        }
        return $result;
    }
    public static function isUserExists($login)
    {
        return self::isExists(['login' => $login]);
    }
    public static function edit($data, $where)
    {
        if (!empty($data['contacts'])) {
            if (!empty($where['id'])) {
                Contacts::reLink($data['contacts'], $where['id']);
            }
            unset($data['contacts']);
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
        }
        return self::update($data, $where);
    }
    public static function add($name)
    {
        $table = self::$table;
        $nickname = self::formatName($name);

        return self::insert(['name' => $nickname], $table);
    }
    public static function remove($uid)
    {
        $table = self::$table;
        return self::delete($uid, $table);
    }
    public static function ban(int $userId, array $banned): bool
    {
        if (empty($userId)) return false;
        return self::edit(['ban' => $banned], ['id' => $userId]);
    }
    public static function unban(int $userId): bool
    {
        if (empty($userId)) return false;
        return self::edit(['ban' => null], ['id' => $userId]);
    }
    public static function isBanned(string $mode = 'auth', $ban = []): bool
    {
        if (empty($ban['expired']) || empty($ban[$mode]) || $ban['expired'] <= $_SERVER['REQUEST_TIME']) return false;

        return true;
    }
    public static function checkAccess(string $level): bool
    {
        if (empty($_SESSION['privilege']['status'])) return false;

        $userLevel = 0;
        $requireLevel = 0;
        $count  = count(self::$usersAccessLevels);

        for ($x = 0; $x < $count; $x++) {
            if (self::$usersAccessLevels[$x] === $_SESSION['privilege']['status'])
                $userLevel = $x;
            if (self::$usersAccessLevels[$x] === $level)
                $requireLevel = $x;
        }
        return $userLevel >= $requireLevel;
    }
    public static function contacts(array $source): array
    {
        if (!empty($source['id'])) {
            $contacts = Contacts::findBy('user_id', $source['id']);
            if (!$contacts) {
                $source['contacts'] = [];
                return $source;
            }
            $count = count($contacts);
            for ($x = 0; $x < $count; $x++) {
                $source['contacts'][$contacts[$x]['type']] = $contacts[$x]['contact'];
            }
            return $source;
        }

        $countUsers = count($source);
        $ids = [];
        for ($x = 0; $x < $countUsers; $x++) {
            if (empty($source[$x]['id'])) continue;
            $ids[] = $source[$x]['id'];
            $source[$x]['contacts'] = [];
        }

        $contacts = Contacts::findGroup('user_id', $ids);
        $countContacts = count($contacts);
        for ($x = 0; $x < $countContacts; $x++) {
            for ($y = 0; $y < $countUsers; $y++) {
                if ($contacts[$x]['user_id'] != $source[$y]['id']) continue;
                $source[$y]['contacts'][$contacts[$x]['type']] = $contacts[$x]['contact'];
            }
        }
        return $source;
    }
    public static function addNames(array &$source): array
    {
        if (empty($source))
            return $source;
        if (!empty($source['id'])) {
            $userData = self::find($source['id']);
            $source['name'] = empty($userData) ? '&lt; Deleted &gt;' : $userData['name'];
            return $source;
        }

        $countSource = count($source);
        $ids = [];
        for ($x = 0; $x < $countSource; $x++) {
            if (empty($source[$x]['id'])) continue;
            $ids[] = $source[$x]['id'];
        }

        if (empty($ids))
            return $source;

        $data = self::findGroup('id', $ids);
        $countData = count($data);
        for ($x = 0; $x < $countData; $x++) {
            for ($y = 0; $y < $countSource; $y++) {
                if ($source[$y]['id'] != $data[$x]['id']) continue;
                $source[$y]['name'] = $data[$x]['name'];
            }
        }

        for ($x = 0; $x < $countSource; $x++) {
            if (empty($source[$x]['id']) || !empty($source[$x]['name'])) continue;
            $element['name'] = '&lt; Deleted &gt;';
        }
        return $source;
    }
    /**
     * @param string $name  - user's nickname
     * 
     * @return mixed        - new, formated nickname
     */
    public static function formatName(string $name)
    {

        // $name = preg_replace(['/\s+/', '/[^a-zа-яєіїґ.0-9 ]+/ui'], [' ', ''], trim($name));
        $name = preg_replace(['/\s+/', '/[^а-яА-ЯрРсСтТуУфФчЧхХШшЩщЪъЫыЬьЭэЮюЄєІіЇїҐґ.0-9 ]+/ui'], [' ', ''], trim($name));

        if (empty($name)) return false;

        $nickname = '';
        $_name = explode(' ', $name);

        foreach ($_name as $slug) {
            $nickname .= Locale::mb_ucfirst($slug) . ' ';
        }

        return mb_substr($nickname, 0, -1, 'UTF-8');
    }
    public static function init()
    {
        $table = self::$table;
        /*         $privilegeDefault = [
            'status' => '',
            'admin' => '',
            'rank' => 0
        ];
        $privilegeDefault = json_encode($privilegeDefault, JSON_UNESCAPED_UNICODE);
        $personalDefault = [
            'fio' => '',
            'birthday' => 0,
            'gender' => '',
            'avatar' => ''
        ];
        $personalDefault = json_encode($personalDefault, JSON_UNESCAPED_UNICODE);
        $contactsDefault = [
            'email' => '',
            'telegram' => '',
            'telegramid' => ''
        ];
        $contactsDefault = json_encode($contactsDefault, JSON_UNESCAPED_UNICODE);
        $credoDefault = [
            'in_game' => '',
            'in_live' => '',
            'favorite' => '',
            'signature' => '',
        ];
        $credoDefault = json_encode($credoDefault, JSON_UNESCAPED_UNICODE);
                        privilege JSON DEFAULT '$privilegeDefault',
                personal JSON DEFAULT '$personalDefault',
                contacts JSON DEFAULT '$contactsDefault',
                credo JSON DEFAULT '$credoDefault',
 */
        self::query(
            "CREATE TABLE IF NOT EXISTS $table (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                name CHARACTER VARYING(250) NOT NULL DEFAULT '',
                login CHARACTER VARYING(250) NOT NULL DEFAULT '',
                password CHARACTER VARYING(250) NOT NULL DEFAULT '',
                privilege JSON DEFAULT NULL,
                personal JSON DEFAULT NULL,
                contacts JSON DEFAULT NULL,
                credo JSON DEFAULT NULL,
                ban JSON DEFAULT NULL,
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW(),
                date_delete TIMESTAMP DEFAULT NULL
            );"
        );
        if (self::isExists(['id' => 1])) return true;

        $privilege = ['status' => 'root', 'admin' => 1, 'rank' => 0];
        self::insert([
            'login' => 'admin',
            'password' => password_hash(sha1(ROOT_PASS_DEFAULT), PASSWORD_DEFAULT), //admin1234
            'privilege' => json_encode($privilege, JSON_UNESCAPED_UNICODE)
        ], $table);
    }
}
