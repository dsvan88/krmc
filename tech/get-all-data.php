<?php
require $_SERVER['DOCUMENT_ROOT'] . '/engine/class.action.php';

$action = new Action;

/* backup the db OR just a table */
function backup_tables($action, $tables = [])
{
    $return = '';
    //cycle through
    foreach ($tables as $table) {
        $result = $action->query("SELECT * FROM $table ORDER BY id");
        while ($row = $action->getAssoc($result)) {
            if ($table === SQL_TBLUSERS) {
                $newRow = [
                    $row['id'],
                    $row['name'],
                    $row['login'],
                    $row['password'],
                    json_encode([
                        'status' => $row['status'],
                        'admin' => $row['admin'],
                        'rank' => $row['rank'],
                    ], JSON_UNESCAPED_UNICODE),
                    json_encode([
                        'fio' => $row['fio'],
                        'birthday' => $row['birthday'],
                        'gender' => $row['gender'],
                        'avatar' => '',
                    ], JSON_UNESCAPED_UNICODE),
                    json_encode([
                        'email' => $row['email'],
                        'telegram' => $row['telegram'],
                        'telegramid' => $row['telegramid'],
                    ], JSON_UNESCAPED_UNICODE),
                    json_encode([
                        'in_game' => $row['game_credo'],
                        'in_live' => $row['live_credo'],
                    ], JSON_UNESCAPED_UNICODE),
                ];
            } elseif ($table === SQL_TBLWEEKS) {
                $data = json_decode($row['data'], true);
                $newData = [];
                for ($x = 0; $x < 7; $x++) {
                    if (!isset($data[$x])) {
                        $newData[$x] = ["game" => "mafia", "mods" => [], "time" => "14:00", "status" => "", "participants" => [], "day_prim" => ""];
                    } else {
                        $newData[$x] = $data[$x];
                        if (!isset($data[$x]['game']))
                            $newData[$x]['game'] = 'mafia';
                        if (!isset($data[$x]['mods']))
                            $newData[$x]['mods'] = [];
                        if (!isset($data[$x]['time']))
                            $newData[$x]['time'] = '14:00';
                        if (!isset($data[$x]['status']))
                            $newData[$x]['status'] = '';
                        if (empty($data[$x]['participants'])) {
                            $newData[$x]['participants'] = [];
                        } else {
                            $realIndex = -1;
                            $newData[$x]['participants'] = [];
                            for ($i = 0; $i < count($data[$x]['participants']); $i++) {
                                if ($data[$x]['participants'][$i]['name'] == '' || $data[$x]['participants'][$i]['id'] < 2) {
                                    error_log($data[$x]['participants'][$i]['id']);
                                    continue;
                                }
                                unset($data[$x]['participants'][$i]['duration']);
                                $newData[$x]['participants'][++$realIndex] = [
                                    'id' => 0,
                                    'name' => '',
                                    'arrive' => '',
                                    'prim' => ''
                                ];
                                $newData[$x]['participants'][$realIndex]['prim'] = '';
                                foreach ($data[$x]['participants'][$i] as $key => $value) {
                                    if (in_array($key, ['id', 'name', 'arrive'])) {
                                        $newData[$x]['participants'][$realIndex][$key] = $value;
                                        continue;
                                    }
                                    if (!empty($value))
                                        $newData[$x]['participants'][$realIndex]['prim'] .= $value . ', ';
                                }
                                if (!empty($newData[$x]['participants'][$i]['prim'])) {
                                    $newData[$x]['participants'][$realIndex]['prim'] = mb_substr($newData[$x]['participants'][$realIndex]['prim'], 0, -2, 'UTF-8');
                                }
                            }
                        }
                        if (!isset($data[$x]['day_prim']))
                            $newData[$x]['day_prim'] = '';
                    }
                }
                $newRow = [
                    $row['id'],
                    json_encode($newData, JSON_UNESCAPED_UNICODE),
                    $row['start'],
                    $row['finish'],
                    $row['created_at'],
                    $row['updated_at']
                ];
            }
            $return .= "('" . implode("','", $newRow) . "'),\r\n";
        }
    }
    return $return;
}

header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header('Content-disposition: attachment; filename="dump.txt"');

echo backup_tables($action, [SQL_TBLUSERS, SQL_TBLWEEKS]);
