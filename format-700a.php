<?php
include 'autoload.php';
include 'functions.php';

function removeTrailingChar($data)
{
    if (preg_match('/[,]$/', $data)) {
        return substr($data, 0, -1);
    }
    return $data;
}


$db = Db::getDb();
$c = new Zend_Console_Getopt('f');
$query = "SELECT i.value, i.id, i.`key`
FROM import AS i
RIGHT JOIN import AS trl ON (i.itemId = trl.itemId AND trl.col='700' AND trl.subcol='4' AND trl.value='trl')
WHERE i.col = '700' AND i.subcol='a'";
$items = $db->fetchAll($query, Zend_Db::FETCH_ASSOC);
$cnt = count($items);
$cleaners = array(
    'removeTrailingChar'
);
$ignoreList = array(
    'Krecar z Růžokvětu, Jarmil'
);
foreach ($items as $id => $item) {
    foreach ($cleaners as $function) {
        $item['value'] = $function($item['value']);
    }
    if (!preg_match('/^\w+(-\w+){0,1}, \w+(\.){0,1}( (\w+)(\.){0,1})*$/u', $item['value'])) {
        if (!in_array($item['value'], $ignoreList)) {
            echo sprintf("\t%s: '%s'" . PHP_EOL, $item['key'], $item['value']);
        }
    }
    
    if ($c->getOption('f')) {
        $db->update('import', array('value' => max($item['listItems'])), $db->quoteInto('id = ?', $item['id']));
    }
} 