<?php
include 'autoload.php';
include 'functions.php';

function addTrailingChar($data)
{
    if (preg_match('/[^,]$/', $data)) {
        return $data . ',';
    }
    return $data;
}

function tryAddComma($data)
{
    if (preg_match('/^((\w+)(-\w+){0,1}){1} (((\w+)[\.]{0,1})+),$/u', $data, $matches)) {
        return $matches[1] . ', ' . $matches[4] . ',';
    }
    return $data;
}

function missingSpaceAfterComma($data)
{
    if (preg_match('/^((\w+)(-\w+){0,1}){1},((\w+)[\.]{0,1})+,$/u', $data, $matches)) {
        return $matches[1] . ', ' . $matches[4] . ',';
    }
    return $data;
}

function addSpaceAfterDot($data)
{
    $x = preg_replace('/([^ ])\.([^ ,])/u', '$1. $2', $data);
    return $x;
}


$db = Db::getDb();
$c = new Zend_Console_Getopt('fv');
$query = "SELECT i.value, i.id, i.`itemKey`
FROM import AS i
RIGHT JOIN import AS trl ON (i.itemId = trl.itemId AND i.colId = trl.colId AND trl.col='700' AND trl.subcol='4' AND trl.value='trl')
WHERE i.col = '700' AND i.subcol='a'";
$items = $db->fetchAll($query, Zend_Db::FETCH_ASSOC);
$cnt = count($items);
$cleaners = array(
    'addTrailingChar',
    'tryAddComma',
    'missingSpaceAfterComma',
    'addSpaceAfterDot',
);
$ignoreList = array(
    'Krecar z Růžokvětu, Jarmil'
);
foreach ($items as $id => &$item) {
    $before = $item['value'];
    foreach ($cleaners as $function) {
        $item['value'] = $function($item['value']);
    }
    $after = $item['value'];
    if (!preg_match('/^\w+(-\w+){0,1}, \w+(\.){0,1}( (\w+)(\.){0,1})*( ){0,1},$/u', $item['value'])) {
        if (!in_array($item['value'], $ignoreList)) {
            echo sprintf("\t%s: '%s'" . PHP_EOL, $item['itemKey'], $item['value']);
        }
        } elseif (($before !== $after) && $c->getOption('v')) {
        echo sprintf("%s\t=>\t%s". PHP_EOL, $before, $after);
    }
} 

if ($c->getOption('f')) {
    echo 'Starting the update' . PHP_EOL;
    foreach ($items as $id => $item) {
        $db->update(
            'import', 
            array(
                'value' => $item['value']
            ), 
            $db->quoteInto('id = ?', $item['id'])
        );
        if ($cnt) echo sprintf('Updated id:%s with "%s"' . PHP_EOL, $item['id'], $item['value']);
    }
} 