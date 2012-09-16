<?php
include 'autoload.php';
include 'functions.php';
function removeBrackets($data)
{
    return trim(strtr($data, '[]', '  '));
}

function removeLeadingChar($data)
{
    if (preg_match('/^[cp]/', $data)) {
        return substr($data, 1);
    }
    return trim($data);
}

function removeTrailingComma($data)
{
    if (strpos($data, ',') === (mb_strlen($data) - 1)) {
        return substr($data, 0, -1);
    }
    return trim($data);
}

function removeCopyright($data)
{
    if (preg_match('/^([0-9]{4})[^0-9]+[cp][0-9]{4}$/', $data, $matches)) {
        return $matches[1];
    }
    return $data;
}

function fixYearSpan($data)
{
    if (preg_match('/^([0-9]{4})-([0-9]{4})/', $data, $matches)) {
        if ($matches[1] > $matches[2]) {
            return $matches[1];
        } else {
            return $matches[2];
        }
    }
    return $data;
}

function removeTrailingChar($data)
{
    if (preg_match('/[,-\?]$/', $data)) {
        return substr($data, 0, -1);
    }
    return $data;
}


$db = Db::getDb();
$c = new Zend_Console_Getopt('f');
$query = "SELECT LeaveNumber(value), value, id, `key`
FROM import
WHERE col = '260' AND subcol = 'c' AND LeaveNumber(value) <> value";
$items = $db->fetchAll($query, Zend_Db::FETCH_ASSOC);
$cnt = count($items);
$cleaners = array(
    'removeBrackets', 
    'removeLeadingChar', 
    'removeTrailingComma', 
    'removeCopyright',
    'fixYearSpan',
    'removeTrailingChar'
);
foreach ($items as $id => $item) {
    foreach ($cleaners as $function) {
        $item['value'] = $function($item['value']);
    }
    if (!preg_match('/^[0-9]+$/', $item['value'])) {
        echo sprintf("\t%s: '%s'" . PHP_EOL, $item['key'], $item['value']);
    }
    
    if ($c->getOption('f')) {
        $db->update('import', array('value' => max($item['listItems'])), $db->quoteInto('id = ?', $item['id']));
    }
} 