<?php

namespace app\models;

use app\core\Model;

class News extends Model
{
    public static function getPerPage($page = 0)
    {
        $searchQuery = 'SELECT * FROM ' . SQL_TBL_NEWS . ' WHERE type = ? ';
        $values = ['news'];
        $searchQuery .= ' ORDER BY id DESC';

        if ($page === 0)
            $searchQuery .= ' LIMIT ' . CFG_NEWS_PER_PAGE;
        else
            $searchQuery .= ' LIMIT ' . CFG_NEWS_PER_PAGE . ' OFFSET ' . (CFG_NEWS_PER_PAGE * $page);

        return self::query($searchQuery, $values, 'Assoc');
    }
    public static function getCount()
    {
        return self::query('SELECT COUNT(id) FROM ' . SQL_TBL_NEWS . ' WHERE type = ? ', ['news'], 'Column');
    }
    public static function getAll($condition)
    {
        $where = '';
        $condArray = [];
        if (!empty($condition)) {
            $where = ' WHERE ';
            foreach ($condition as $key => $value) {
                $where .= "$key = :$key,";
            }
            $where = substr($where, 0, -1);
        }
        return self::query('SELECT * FROM ' . SQL_TBL_NEWS . $where, $condArray, 'Assoc');
    }
    public static function getDataById($id)
    {
        return self::query('SELECT * FROM ' . SQL_TBL_NEWS . ' WHERE id = ? LIMIT 1', [$id], 'Assoc')[0];
    }
    public static function getPromoData()
    {
        $result = self::query('SELECT * FROM ' . SQL_TBL_NEWS . ' WHERE type = ? LIMIT 1', ['promo'], 'Assoc');
        if (empty($result)) return false;
        return $result[0];
    }
    public static function getGameData($game)
    {
        $result = self::query('SELECT * FROM ' . SQL_TBL_NEWS . ' WHERE type = ? LIMIT 1', [$game], 'Assoc');
        if (empty($result)) return false;
        return $result[0];
    }
    public static function create(&$data)
    {
        $array = [
            'title' => trim($data['title']),
            'subtitle' => trim($data['subtitle']),
            'html' => trim($data['html']),
            'type' => trim($data['type']),
        ];
        if (isset($data['logo'])) {
            $array['logo'] = trim($data['logo']);
        }
        return self::insert($array, SQL_TBL_NEWS);
    }
    public static function edit($data, $id)
    {
        $array = [
            'title' => trim($data['title']),
            'subtitle' => trim($data['subtitle']),
            'html' => trim($data['html']),
        ];
        if (isset($data['type'])) {
            $array['type'] = trim($data['type']);
        }
        if (isset($data['logo'])) {
            $array['logo'] = trim($data['logo']);
        }
        $searchBy = ['id' => $id];
        if ($id === 'promo') {
            unset($array['type']);
            $searchBy = ['type' => 'promo'];
        }
        return self::update($array, $searchBy, SQL_TBL_NEWS);
    }
    public static function remove($id)
    {
        return self::delete($id, SQL_TBL_NEWS);
    }
}
