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
            $return .= "('" . implode("','", $newRow) . "'),\r\n";
        }
    }
    return $return;
}

header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header('Content-disposition: attachment; filename="dump.txt"');

echo backup_tables($action, [SQL_TBLUSERS]);

/* 
$tables = [SQL_TBLWEEKS, SQL_TBLNEWS, SQL_TBLUSERS];
foreach ($tables as $table) {
    $sqlData = extract($action, $table);
}
function extract($action, $table)
{
    $sqlData =
        $result = $action->getAssocArray($action->query("SELECT * FROM $table"));

    $keys = array_keys($result[0]);
    for ($x = 0; $x < count($keys); $x++)
        $preKeys[$x] = ':' . $keys[$x];

    $query = "INSERT INTO $table (" . implode(',', $keys) . ') VALUES (' . implode(',', $preKeys) . ')';
    echo $query . '</br>';
    foreach ($result as $row) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE);
        echo '</br>';
    }
}



            $newRow = [
                $row['id'],
                $row['name'],
                $row['login'],
                $row['password'],
                str_replace(
                    '"',
                    '\\"',
                    json_encode([
                        'status' => $row['status'],
                        'admin' => $row['admin'],
                        'rank' => $row['rank'],
                    ], JSON_UNESCAPED_UNICODE)
                ),
                str_replace(
                    '"',
                    '\\"',
                    json_encode([
                        'fio' => $row['fio'],
                        'birthday' => $row['birthday'],
                        'gender' => $row['gender'],
                        'avatar' => '',
                    ], JSON_UNESCAPED_UNICODE)
                ),
                str_replace(
                    '"',
                    '\\"',
                    json_encode([
                        'email' => $row['email'],
                        'telegram' => $row['telegram'],
                        'telegramid' => $row['telegramid'],
                    ], JSON_UNESCAPED_UNICODE)
                ),
                str_replace(
                    '"',
                    '\\"',
                    json_encode([
                        'in_game' => $row['game_credo'],
                        'in_live' => $row['live_credo'],
                    ], JSON_UNESCAPED_UNICODE)
                ),
            ];

 */