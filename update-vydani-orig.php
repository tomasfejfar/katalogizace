<?php
include 'autoload.php';
$db = Zend_Db::factory('pdo_mysql', array(
		'host' => '127.0.0.1',
		'username' => 'root', 
		'dbname' => 'katalogizace',
		'password' => '',
	)); 

$query = 'SELECT LeaveNumber(value), value, id, `itemKey`
FROM import
WHERE col = \'o26\'
AND LeaveNumber(value) <> value AND LENGTH(LeaveNumber(value)) <> 4';
$items = $db->fetchAll($query, Zend_Db::FETCH_ASSOC);
$cnt = count($items);
foreach ($items as $id => $item) {
  $item['valueNew'] = preg_split('/[^0-9]/',$item['value']);
  //echo $item['value'] . "\t" . reset($item['valueNew']) . "\t\t\t\t[".$id ."/".$cnt."]\r";
  echo reset($item['valueNew']) . "\t" . $item['value'] . "\t\t\t\t[".$id ."/".$cnt."]\r";
  //usleep(500000);
  $db->update('import', array('value' => reset($item['valueNew'])), $db->quoteInto('id = ?', $item['id']));
} 