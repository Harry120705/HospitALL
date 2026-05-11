<?php
require 'vendor/autoload.php';
$m = new MongoDB\Client('mongodb://127.0.0.1:27017');
foreach($m->listDatabases() as $db) {
    echo $db->getName() . PHP_EOL;
    foreach($m->selectDatabase($db->getName())->listCollections() as $c) {
        echo '  - ' . $c->getName() . PHP_EOL;
    }
}
