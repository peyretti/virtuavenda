<?php
// Ponto de entrada da API

// Define o diretório raiz
define('ROOT_DIR', dirname(__DIR__));

// Autoload do Composer
require_once ROOT_DIR . '/vendor/autoload.php';

// Configurações de erro
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Use namespaces
use App\Config\Config;
use App\Routes\Api;
use App\Utils\Response;

try {
    // Configurações de CORS
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

    // Se for uma requisição OPTIONS (pré-voo CORS), retornamos OK
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }

    // Extrai informações da requisição para depuração
    $requestUri = $_SERVER['REQUEST_URI'];
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    
    // Log das informações da requisição (remove em produção)
    error_log("Request URI: " . $requestUri);
    error_log("Request Method: " . $requestMethod);

    // Roteamento
    Api::route();
    
} catch (\Exception $e) {
    // Log do erro
    error_log($e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    
    // Envia resposta de erro
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}