<?php
require 'vendor/autoload.php';
$m = new MongoDB\Client('mongodb://127.0.0.1:27017');
$db = $m->projeto_arquitetura_desempenho;

$collections = ['FUNCIONARIOS', 'ATENDIMENTOS', 'HOSPITAIS', 'PACIENTES'];

foreach ($collections as $colName) {
    echo "====================================\n";
    echo "Collection: $colName\n";
    echo "====================================\n";
    $doc = $db->$colName->findOne();
    if ($doc) {
        // Convert BSON Document to PHP array, then format to JSON
        $array = json_decode(MongoDB\BSON\toJSON(MongoDB\BSON\fromPHP($doc)), true);
        echo json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "No documents found.\n";
    }
}
