<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;

class HelpCommand extends ChatCommand
{
    public static function description()
    {
        return self::locale('<u>/?</u> or <u>/start</u> or <u>/help</u> <i>// This help menu</i>');
    }
    public static function execute(array $arguments = [])
    {
        $folder = str_replace('\\','/',$_SERVER['DOCUMENT_ROOT'] . self::$operatorClass::$CommandNamespace);

        if (!is_dir($folder) || !file_exists($folder)) {
            self::$operatorClass::$resultMessage = 'Something went wrong!';
            return false;
        }

        $list = scandir($folder);

        if (!$list) {
            self::$operatorClass::$resultMessage = 'Something went wrong! List is empty.';
            return false;
        }

        $self = self::class;
        $commandsList = array_map(function ($element) use ($self) {
            return $self::getCommandDescriptions($element);
        }, $list);

        $commandsList = array_values(array_filter($commandsList));

        $message = self::locale("<i>Thіs іs my instruction😊</i>:\n");
        $message .= implode("\n", $commandsList);
        $message .= self::locale("\n\nFeel free to ask the admins or community, if something is not clear!");

        self::$operatorClass::$resultMessage = $message;
        return true;
    }
    public static function getCommandDescriptions(string $filename)
    {
        if (in_array($filename, ['.', '..'])) return false;
        $offset = mb_strpos($filename, 'Command.php', 0, 'UTF-8');
        $command = mb_substr($filename, 0, $offset, 'UTF-8');

        $class = ucfirst($command) . 'Command';
        $class = str_replace('/', '\\', self::$operatorClass::$CommandNamespace . '\\' . $class);

        if (!class_exists($class) || !self::$operatorClass::checkAccess($class::$accessLevel)) {
            error_log("Command $command - false");
            return false;
        }
        return $class::description();
    }
}
