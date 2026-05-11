<?php
require 'vendor/autoload.php';
$m = new MongoDB\Client('mongodb://127.0.0.1:27017');
echo "HOSPITAIS count: " . $m->projeto_arquitetura_desempenho->HOSPITAIS->countDocuments() . PHP_EOL;
echo "hospitais count: " . $m->projeto_arquitetura_desempenho->hospitais->countDocuments() . PHP_EOL;
