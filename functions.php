<?php
class Db
{
    protected static $db = null;
    public static function getDb ()
    {
        if (! self::$db) {
            echo 'Connecting...' . PHP_EOL . PHP_EOL;
            $db = Zend_Db::factory('pdo_mysql', 
            array('host' => '127.0.0.1', 'username' => 'root', 
            'dbname' => 'katalogizace', 'password' => ''));
            $db->query("SET NAMES utf8");
            self::$db = $db;
        }
        return self::$db;
    }
}
function parseItem (SimpleXMLElement $e, &$currentItem)
{
    $col1 = trim((string) $e->col1);
    $col2 = trim((string) $e->col2);
    if ($col1 == 'KGP') {}
    if ((strpos($col1, '---') !== false) && (strpos($col2, '---') !== false)) {
        return false;
    }
    if (strlen($col1) > 3) {
        $item['col'] = substr($col1, 0, 3);
        $item['ind1'] = $col1[3];
        if (isset($col1[4])) {
            $item['ind2'] = $col1[4];
        }
    } else {
        $item['col'] = $col1;
    }
    if (preg_match('/(\|([a-z0-9]{1}) ([^|]+))+/', $col2, $matches)) {
        $col2 = trim($col2, '| ');
        $parts = explode('|', $col2);
        $matches = array();
        foreach ($parts as $part) {
            preg_match('/([a-z0-9]) (.*)/', $part, $match);
            if (! count($match)) {
                die(var_dump($part) . var_dump($col2) . var_dump($parts));
            }
            $matches[] = $match;
        }
    } else {
        $matches = array(array('', '', $col2));
    }
    if ($col1 == '001') {
        $currentItem['key'] = trim($col2);
    }
    foreach ($matches as $match) {
        $tmpItem = $item;
        $tmpItem['subcol'] = trim($match[1]);
        $tmpItem['value'] = trim($match[2]);
        $currentItem[] = $tmpItem;
         //echo PHP_EOL . implode("\t|\t",$tmpItem) . PHP_EOL;
    }
    return true;
}
function secondsToTime ($seconds)
{
    // extract hours
    $hours = floor($seconds / (60 * 60));
    // extract minutes
    $divisor_for_minutes = $seconds % (60 * 60);
    $minutes = floor($divisor_for_minutes / 60);
    // extract the remaining seconds
    $divisor_for_seconds = $divisor_for_minutes % 60;
    $seconds = ceil($divisor_for_seconds);
    return sprintf('%s:%s:%s', (int) $hours, (int) $minutes, (int) $seconds);
}
function insertItemToDb ($item, $itemId = null)
{
    $db = Db::getDb();
    if (is_null($itemId)) {
        $itemIdSql = $db->select()->from('import', 
        array(new Zend_Db_Expr('MAX(itemId)')));
        $itemId = $db->fetchOne($itemIdSql) + 1;
    }
    unset($item['status']);
    $key = $item['key'];
    unset($item['key']);
    $tmp = array('itemId' => $itemId, 'key' => $key);
    foreach ($item as $row) {
        $arr = array_merge($tmp, $row);
        $db->insert('import', $arr);
    }
    return $itemId;
}
function insertItemsToDb ($items)
{
    $db = Db::getDb();
    $db->query("ALTER TABLE import DISABLE KEYS");
    //$db->query("TRUNCATE TABLE import");
    $itemIdSql = $db->select()
                    ->from('import', array(new Zend_Db_Expr('MAX(itemId)')));
    $itemId = $db->fetchOne($itemIdSql) + 10;
    $total = count($items);
    $cnt = 0;
    $start = time();
    foreach ($items as $item) {
        unset($item['status']);
        $key = $item['key'];
        unset($item['key']);
        $tmp = array('itemId' => $itemId, 'key' => $key);
        foreach ($item as $row) {
            $arr = array_merge($tmp, $row);
            $db->insert('import', $arr);
        }
        $secs = time() - $start;
        echo $itemId . "\tEST: " .
        secondsToTime(round($secs / ($cnt / $total)) - $secs) .  "s                 " . "\r";
        $cnt ++;
        $itemId ++;
    }
    $db->query("ALTER TABLE import ENABLE KEYS");
}