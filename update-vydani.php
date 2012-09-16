<?php
include 'autoload.php';
$db = Zend_Db::factory('pdo_mysql', array(
		'host' => '127.0.0.1',
		'username' => 'root', 
		'dbname' => 'katalogizace',
		'password' => '',
	)); 

$query = 'SELECT 
LeaveNumberAndColon(value) as list, LeaveNumber(value) as x, value, id
FROM import
WHERE col = \'250\' AND LeaveNumberAndColon(value) <> LeaveNumber(value)';
$items = $db->fetchAll($query, Zend_Db::FETCH_ASSOC);
$cnt = count($items);
foreach ($items as $id => $item) {
  $item['listItems'] = explode('(',$item['list']);
  echo $item['list'] . "\t" . max($item['listItems']) . "\t\t\t\t[".$id ."/".$cnt."]\r";
  usleep(500000);
  //$db->update('import', array('value' => max($item['listItems'])), $db->quoteInto('id = ?', $item['id']));
} 