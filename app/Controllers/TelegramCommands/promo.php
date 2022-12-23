<?php

use app\models\News;

$text = self::$message['message']['text'];
$promoText = trim(mb_substr($text, mb_strpos($text, ' ', 0, 'UTF-8') + 1, NULL, 'UTF-8'));

if (isset(self::$message['message']['entities'])) {

    $newString = '';
    $offset = 0;
    $formattings = [
        'bold' => 'b',
        'italic' => 'i',
        'strikethrough' => 's',
        'spoiler' => 'tg-spoiler',
    ];
    for ($i = 0; $i < count(self::$message['message']['entities']); $i++) {
        if (self::$message['message']['entities'][$i]['type'] === 'bot_command') {
            $offset = self::$message['message']['entities'][$i]['offset'] + self::$message['message']['entities'][$i]['length'];
            continue;
        }
        $newString .= mb_substr($text, $offset, self::$message['message']['entities'][$i]['offset'] - $offset, 'UTF-8');
        $newString .= "<{$formattings[self::$message['message']['entities'][$i]['type']]}>" . mb_substr($text, self::$message['message']['entities'][$i]['offset'], self::$message['message']['entities'][$i]['length'], 'UTF-8') . "</{$formattings[self::$message['message']['entities'][$i]['type']]}>";
        $offset = self::$message['message']['entities'][$i]['offset'] + self::$message['message']['entities'][$i]['length'];
    }
    $newString .= mb_substr($text, $offset, null, 'UTF-8');

    $newString = preg_replace(['/\-\-(.*)\-\-/'], ['<u>$1</u>'], $newString);

    if ($newString !== '')
        $promoText = $newString;
}
preg_match('/(.*?)\n(.*?)\n([^`]*)/', $promoText, $matches);

$data = [
    'title' => isset($matches[1]) ? trim($matches[1]) : '',
    'subtitle' => isset($matches[2]) ? trim($matches[2]) : '',
    'html' => isset($matches[3]) ? str_replace("\n", '</br>', $matches[3]) : '',
];

News::edit($data, 'promo');

$result = true;
$message = '{{ Tg_Command_Promo_Saved }}';