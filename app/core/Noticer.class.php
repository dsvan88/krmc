<?php

namespace  app\core;

class Noticer
{
    private static $notices = [];
    public static function set(mixed $data): bool
    {
        if (empty($data)) return false;

        if (!is_array($data))
            return ['type' => '', 'message' => $data];

        if (!isset($data['message'])) {
            $result = [];
            foreach ($data as $num => $notice) {
                if (empty($notice)) continue;

                if (!is_array($notice)) {
                    $result[] = ['type' => '', 'message' => $notice];
                    continue;
                }
                $result[] = ['type' => $notice['type'], 'message' => $notice['message']];
            }
        } else {
            if (!isset($data['type']))
                $data['type'] = '';
            $result[] = $data;
        }

        if (!isset($_SESSION['notices']))
            $_SESSION['notices'] = [];

        self::$notices = $_SESSION['notices'] = array_merge($_SESSION['notices'], $result);

        return true;
    }
    public static function get(): array
    {
        if (!empty(self::$notices))
            return self::$notices;

        if (!empty($_SESSION['notices']))
            return $_SESSION['notices'];

        return [];
    }
    public static function clear(): void
    {
        self::$notices = $_SESSION['notices'] = [];
    }
}
