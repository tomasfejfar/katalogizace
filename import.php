<?php
include 'autoload.php';
include 'functions.php';
/*
 * 
 */
$file = 'export.xml';
$reader = new XMLReader();
$reader->open($file);
dtimer($timers, -1);
$timer = array();
do {
    $reader->read();
} while ($reader->name !== 'section-02');
$items = array();
$item = array();
$item['status'] = 'OK';
$i = 0;
while ($reader->name == 'section-02') {
    $dom = new DOMDocument();
    $node = simplexml_import_dom($dom->importNode($reader->expand(), true));
    if ((string) $node->col1 == 'STZ' &&
     in_array(trim((string) $node->col2), array('DEL', 'PER'))) {
        $item['status'] = (string) $node->col2;
    }
    if (! parseItem($node, $item)) {
        echo str_pad($i++, 7, ' ', STR_PAD_RIGHT) . "\r";
        $items[] = $item;
        $item = array();
        $item['status'] = 'OK';
        if (count($items) > 100) {
            //die(var_dump($items));
        }
    }
    $reader->next('section-02');
}
foreach ($items as $key => $item) {
    if ($item['status'] !== 'OK') {
        unset($items[$key]);
    }
}
var_dump(count($items));
insertItemsToDb($items);