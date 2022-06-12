<?php
require $_SERVER['DOCUMENT_ROOT'] . '/engine/class.action.php';

$action = new Action;

/* backup the db OR just a table */
function backup_tables($action, $tables = '*')
{

    //get all of the tables
    if ($tables == '*') {
        $tables = [];
        $result = $action->query('SELECT * FROM pg_catalog.pg_tables;');
        while ($tbl = $action->getRow($result)) {
            $tables[] = $tbl[0];
        }
    } else {
        $tables = is_array($tables) ? $tables : explode(',', $tables);
    }

    $return = '';
    //cycle through
    foreach ($tables as $table) {
        $result = $action->query("SELECT * FROM $table");
        $numFields = $result->columnCount();

        for ($i = 0; $i < $numFields; $i++) {
            while ($row = $action->getRow($result)) {
                $return .= "INSERT INTO $table VALUES(";
                for ($j = 0; $j < $numFields; $j++) {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = preg_replace("/\n/", "\\n", $row[$j]);
                    if (isset($row[$j])) {
                        $return .= '"' . $row[$j] . '"';
                    } else {
                        $return .= '""';
                    }
                    if ($j < ($numFields - 1)) {
                        $return .= ',';
                    }
                }
                $return .= ");\n";
            }
        }
        $return .= "\n\n\n";
    }

    return $return;
}

header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header('Content-disposition: attachment; filename="dump.sql"');

echo backup_tables($action, [SQL_TBLWEEKS, SQL_TBLNEWS, SQL_TBLUSERS]);

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
 */