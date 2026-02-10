<?php

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\Repositories\TelegramBotRepository;

class HelpCommand extends ChatCommand
{
    public static function description()
    {
        return self::locale('<u>/?</u> or <u>/start</u> or <u>/help</u> <i>// This help menu</i>');
    }
    public static function execute()
    {
        $folder = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] . '/' . __NAMESPACE__);

        if (!is_dir($folder) || !file_exists($folder)) {
            return static::result('Something went wrong!');
        }

        $list = scandir($folder);

        if (empty($list)) {
            return static::result('Something went wrong! List is empty.');
        }

        $commandsList = [];
        foreach ($list as $command) {
            $commandsList[] = static::getCommandDescriptions($command);
        }

        // $self = self::class;
        // $commandsList = array_map(function ($element) use ($self) {
        //     return $self::getCommandDescriptions($element);
        // }, $list);

        $commandsList = array_values(array_filter($commandsList));

        $message = self::locale("<i>Thіs іs my instruction😊</i>:\n");
        $message .= implode("\n", $commandsList);
        $message .= self::locale("\n\nFeel free to ask the admins or community, if something is not clear!");

        return static::result($message, '👌', true);
    }
    public static function getCommandDescriptions(string $filename)
    {
        if (in_array($filename, ['.', '..'])) return false;
        $offset = mb_strpos($filename, 'Command.php', 0, 'UTF-8');
        $command = mb_substr($filename, 0, $offset, 'UTF-8');

        $class = ucfirst($command) . 'Command';
        $class = str_replace('/', '\\', __NAMESPACE__ . "\\$class");

        $status = empty(static::$requester->profile->status) ? '' : static::$requester->profile->status;

        if (!class_exists($class) || !TelegramBotRepository::hasAccess($status, $class::$accessLevel)) {
            return false;
        }

        return $class::description();
    }
}
