<?php

namespace app\models;

use app\core\Model;

class Contacts extends Model
{
    public static $table = SQL_TBL_CONTACTS;
    public static $foreign = ['users' => Users::class];
    public static $jsonFields = ['data'];

    public static function getByUserId(int $userId)
    {
        return self::findBy('user_id', $userId);
    }
    public static function getUserContact(int $userId, string $contactType)
    {
        $table = self::$table;
        $contact = Contacts::query("SELECT * FROM $table WHERE user_id = ? AND type = ? LIMIT 1", [$userId, $contactType], 'Assoc');
        return empty($contact) ? false : $contact[0];
    }
    public static function getUserIdByContact(string $contactType, string $value): mixed
    {
        $table = self::$table;
        $userId = Contacts::query("SELECT user_id FROM $table WHERE type = ? AND contact = ? LIMIT 1", [$contactType, $value], 'Column');
        return empty($userId) ? false : $userId;
    }
    public static function isContactExists($contact)
    {
        return self::isExists(['contact' => $contact]);
    }
    public static function new(array $data, int $userId): void
    {
        foreach ($data as $column => $value) {
            if (empty($value)) continue;
            Contacts::add([
                'user_id' => $userId,
                'type' => $column,
                'contact' => $value,
            ]);
        }
    }
    public static function reLink(array $data, int $userId, int $newUserId = 0): void
    {
        self::deleteByUserId($userId, array_keys($data));
        self::new($data, $userId);
    }
    public static function edit($data, $where)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
        }
        return self::update($data, $where);
    }
    public static function add($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
        }
        return self::insert($data);
    }
    public static function deleteByUserId(int $userId, array $types = [])
    {
        $contacts = self::getByUserId($userId);
        if (empty($contacts)) return true;
        $count = count($contacts);
        for ($x = 0; $x < $count; $x++) {
            if (!in_array($contacts[$x]['type'], $types)) continue;
            self::remove($contacts[$x]['id']);
        }
        return true;
    }
    public static function remove($cid)
    {
        return self::delete($cid);
    }
    public static function init()
    {
        $table = self::$table;
        foreach (self::$foreign as $key => $class) {
            $$key = $class::$table;
        }

        self::query(
            "CREATE TABLE IF NOT EXISTS $table (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                user_id INT DEFAULT NULL,
                type CHARACTER VARYING(25) NOT NULL DEFAULT '',
                contact CHARACTER VARYING(250) NOT NULL DEFAULT '',
                data JSON DEFAULT NULL,
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW(),
                CONSTRAINT fk_contact_user
                    FOREIGN KEY(user_id) 
                    REFERENCES $users(id)
                    ON DELETE CASCADE
            );"
        );
    }
}
