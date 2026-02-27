<?php

namespace app\models;

use app\core\GoogleDrive;
use app\core\Locale;
use app\core\Model;
use Exception;

class Pages extends Model
{
    public static $table = SQL_TBL_PAGES;
    public static $foreign = ['users' => Users::class];

    public static $default = [
        'title' => 'Empty page',
        'type' => 'page',
        'subtitle' => '',
        'description' => '',
        'html' => '',
        'expired_at' => '',
    ];
    public static $blocks = [
        'text',
        'image',
        'text-image',
        'image-text',
        'text-text',
    ];

    public static $langsPriority = [
        'uk',
        'en',
        'ru'
    ];
    public static $jsonFields = ['blocks','data'];

    public static function getBySlug(string $slug)
    {
        $table = static::$table;

        $_langs = self::$langsPriority;
        array_splice($_langs, array_search(Locale::$langCode, $_langs, true), 1);
        array_unshift($_langs, Locale::$langCode);

        $query = "SELECT * FROM $table WHERE slug = ? AND lang = ?";
        if (CFG_SOFT_DELETE) {
            $query .= ' AND date_delete IS NULL';
        }
        $query .= ' ORDER BY id DESC LIMIT 1';
        
        $pages = [];
        $continue = true;
        do{
            if (empty($_langs)){
                $query = str_replace('lang = ?', 'lang is NULL', $query);
                $values = [$slug];
                $continue = false;
            }
            else {
                $values = [$slug, array_shift($_langs)];
            }
            $pages = self::query($query, $values, 'Assoc');

        } while ($continue && empty($pages));

        if (empty($pages)) return false;

        return static::decodeJson($pages[0]);
    }
    public static function decodeJson(array $array = [])
    {
        $array = parent::decodeJson($array);

        if (empty($array['blocks'])) return $array;

        foreach($array['blocks'] as $i=>$b){
            $array['blocks'][$i] = json_decode($b,true);
            if (!empty($array['blocks'][$i]['image'])){
                $array['blocks'][$i]['imageId'] = $array['blocks'][$i]['image'];
                $array['blocks'][$i]['imageLink'] = GoogleDrive::getLink($array['blocks'][$i]['image']);
            }
            if ($array['blocks'][$i]['type'] === 'image-text'){
                $array['blocks'][$i]['type'] = 'text-image';
                $array['blocks'][$i]['order'] = 'reverse';
            }
        }

        return $array;
    }
    public static function getCount(string $type = 'page', bool $all = false)
    {
        $table = static::$table;
        $query = "SELECT COUNT(id) FROM $table WHERE type = ?";
        if ($all)
            return self::query($query, [$type], 'Column');

        $query .= ' AND ( expired_at IS NULL OR expired_at > CURRENT_TIMESTAMP )';
        if (CFG_SOFT_DELETE) {
            $query .= ' AND date_delete IS NULL';
        }
        return self::query($query, [$type], 'Column');
    }
    public static function getPerPage($page = 0, $type = 'news')
    {
        $table = static::$table;
        $query = "SELECT * FROM $table WHERE type = ? ";
        $values = [$type];
        if (CFG_SOFT_DELETE) {
            $query .= ' AND date_delete IS NULL';
        }
        $query .= ' ORDER BY id DESC';

        if ($page === 0)
            $query .= ' LIMIT ' . CFG_NEWS_PER_PAGE;
        else
            $query .= ' LIMIT ' . CFG_NEWS_PER_PAGE . ' OFFSET ' . (CFG_NEWS_PER_PAGE * $page);

        return self::query($query, $values, 'Assoc');
    }
    public static function create(&$data)
    {
        $array = self::prepDbArray($data);

        if (!$array) return false;

        return self::insert($array);
    }
    public static function edit(array $data = [], string $slug = '')
    {
        if (empty($data) || empty($slug)) return false;

        $array = self::prepDbArray($data, $slug);

        if (!is_array($array)) return $array;

        $array['updated_at'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $page = self::getBySlug($slug);
        if (empty($page)) {
            $array['slug'] = $slug;
            return (bool) self::insert($array);
        }
        if ($page['lang'] !== $array['lang']) {
            $array['slug'] = $page['slug'];
            $array['type'] = $page['type'];
            return true;
        }
        return self::update($array, ['id' => $page['id']]);
    }
    public static function prepDbArray(array &$data, string $slug = '')
    {
        if (empty($data)){
            throw new Exception('Data can’t be empty');
        }

        $array = [
            'title' => trim($data['title']),
            'subtitle' => trim($data['subtitle']),
            'blocks' => $data['blocks'],
        ];
        if (!empty($data['html'])){
            $array['html'] = trim($data['html']);
        }
        if (!empty($data['published_at'])){
            $array['published_at'] = date('Y-m-d H:i:s', strtotime($data['published_at']));
        }
        if (!empty($data['description'])) {
            $pattern = ['/<\/p>\s*<p>/', '/<.*?>/', "/^\"/", "/ \"/", '/ "/', "/\"/", '/"/', "/\'/", "/'/"];
            $replace = ["\n", '', '«', ' «', ' «', '»', '»', '’', '’'];
            $array['description'] = trim(preg_replace($pattern, $replace, $data['description']));
            if (mb_strlen($array['description'], 'UTF-8') > 299) {
                throw new Exception('Description longer than 300 symbols!');
            }
        }
        if (!empty($data['type'])) {
            $array['type'] = trim($data['type']);
        }
        if (!empty($data['expired_at'])) {
            $array['expired_at'] = date('Y-m-d H:i:s', strtotime($data['expired_at']));
        }
        if (!empty($data['image_id'])) {
            $array['data']['logo'] = $data['image_id'];
        }
        if (!empty($data['keywords'])) {
            $array['data']['keywords'] = explode(',', $data['keywords']);
            foreach ($array['data']['keywords'] as $index => $keyword) {
                $array['data']['keywords'][$index] = trim($keyword);
            }
        }

        foreach(static::$jsonFields as $field){
            if (empty($array[$field]) || !is_array($array[$field])) continue;
            $array[$field] = json_encode($array[$field], JSON_UNESCAPED_UNICODE);
        }

        $array['slug'] = empty($slug) ? preg_replace(['/[^a-z0-9]+/i', '/--/'], '-', Locale::translitization(trim($array['title']))) : $slug;
        $array['lang'] = Locale::$langCode;
        return $array;
    }
    public static function remove(int $id)
    {
        if (CFG_SOFT_DELETE) {
            return self::update(['date_delete' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])], ['id' => $id]);
        }
        return self::delete($id, static::$table);
    }
    public static function init()
    {
        $table = static::$table;
        foreach (self::$foreign as $key => $class) {
            $$key = $class::$table;
        }

        self::query(
            "CREATE TABLE IF NOT EXISTS $table (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL DEFAULT '1',
                type CHARACTER VARYING(25) NOT NULL DEFAULT 'page',
                lang CHARACTER VARYING(5) DEFAULT NULL,
                title CHARACTER VARYING(250) NOT NULL DEFAULT '',
                slug CHARACTER VARYING(250) NOT NULL DEFAULT '',
                subtitle CHARACTER VARYING(250) NOT NULL DEFAULT '',
                description CHARACTER VARYING(300) NOT NULL DEFAULT '',
                html TEXT NULL DEFAULT NULL,
                blocks JSON DEFAULT NULL,
                data JSON DEFAULT NULL,
                published_at TIMESTAMP DEFAULT NOW(),
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW(),
                expired_at TIMESTAMP DEFAULT NULL,
                date_delete TIMESTAMP DEFAULT NULL,
                CONSTRAINT fk_page_author
                    FOREIGN KEY(user_id) 
                    REFERENCES $users(id)
                    ON DELETE SET DEFAULT
            );"
        );

        if (self::isExists(['id' => 1])) return true;

        $data = [
            [
                'type' => 'page',
                'title' => CLUB_NAME,
                'slug' => 'home',
                'subtitle' => 'Про нас',
                'description' => 'Наш клуб позитивних та кмітливих людей, заснований для того, аби кожен бажаючий міг провести час свого дозвілля з гарним настроєм та користью для власного розвитку!',
                'html' => '<p>Наш клуб позитивних та кмітливих людей, заснований для того, аби кожен бажаючий міг провести час свого дозвілля з гарним настроєм та користью для власного розвитку!</p><p>Запрошуємо Вас, до нашого дружнього та кмітливого клубу гравців у Мафію!:)</p>',
            ],
            [
                'type' => 'game',
                'title' => 'Мафія',
                'slug' => 'mafia',
                'subtitle' => 'Класична гра мафія',
                'description' => 'Класична салонна гра Мафія. Гравці розподіляються на два команди, мета кожного з яких - логікою та ораторськими здібностями знешкодити команду супротивніків. Ускладнюється тим, що команда, менша за кількістю гравців - знає, хто у якій команді, а інша - ні.',
                'html' => '<p>Клубна гра Мафія, в класичному стилі вражає свою легкістю та складністю одночасно! Результат кожної гри, завжди залежить не тільки від особистого вкладу кожного окремого гравця, але й від команди в цілому. Так, ми чудово розуміємо, що до цього моменту - нічого нового, для командних видів ігор - не було...</p><p>Але є нюанси!</p><p>Адже, стосовно того, хто знаходиться у твоїй команді - інтрига зберігається до самого закінчення гри! Прокачайте, разом з нами, свої навички логіки, дедукції, емпатії, інтуїції, да й що там казати - телепатії, також!</p><p>Запрошуємо Вас, до нашого дружнього та кмітливого клубу гравців у Мафію!:)</p>',
            ],
            [
                'type' => 'promo',
                'title' => 'Записываемся активнее!',
                'slug' => 'promo',
                'subtitle' => 'Важен каждый игрок!',
                'description' => 'Опис промо-повідомлення для додавання до низу команди "week"',
                'html' => 'Только Ваше участие позволяет клубу и другим игрокам становиться лучше!',
            ],
        ];
        self::insert($data, $table);
    }
}
