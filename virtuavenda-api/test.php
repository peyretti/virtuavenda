<?php
// Define as configurações de erro
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Responde com JSON
header('Content-Type: application/json');

// Dados de teste
$data = [
    'status' => 'success',
    'message' => 'Teste de API funcionando!',
    'server_variables' => [
        'REQUEST_URI' => $_SERVER['REQUEST_URI'],
        'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'],
        'PHP_SELF' => $_SERVER['PHP_SELF']
    ]
];

// Envia a resposta
echo json_encode($data, JSON_PRETTY_PRINT);