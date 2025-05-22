<?php
namespace App\Controllers;

use App\Utils\Response;
use App\Config\Config;

class ApiController {
    /**
     * Endpoint para a raiz da API
     */
    public function index() {
        Response::success([
            'name' => Config::APP_NAME,
            'message' => 'Bem-vindo à API VirtuaVenda. Use /health para verificar o status ou /info para obter informações sobre a API.',
            'version' => Config::APP_VERSION
        ]);
    }
    
    /**
     * Endpoint para verificar se a API está funcionando
     */
    public function healthCheck() {
        Response::success([
            'status' => 'ok',
            'message' => 'VirtuaVenda API está funcionando',
            'version' => Config::APP_VERSION,
            'environment' => Config::getEnvironment(),
            'timestamp' => time()
        ]);
    }
    
    /**
     * Endpoint para obter informações sobre a API
     */
    public function info() {
        Response::success([
            'name' => Config::APP_NAME,
            'version' => Config::APP_VERSION,
            'environment' => Config::getEnvironment(),
            'base_url' => Config::getBaseUrl(),
            'base_path' => Config::getBasePath(),
            'php_version' => phpversion()
        ]);
    }
    
    /**
     * Endpoint para depuração
     */
    public function debug() {
        $serverData = [
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
            'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'N/A',
            'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? 'N/A',
            'PHP_SELF' => $_SERVER['PHP_SELF'] ?? 'N/A',
            'QUERY_STRING' => $_SERVER['QUERY_STRING'] ?? 'N/A',
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'N/A',
            'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? 'N/A',
            'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
            'PATH_INFO' => $_SERVER['PATH_INFO'] ?? 'N/A'
        ];
        
        $pathInfo = [
            'base_path' => Config::getBasePath(),
            'original_uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'processed_uri' => $this->getProcessedUri(),
            'parts' => explode('/', trim($this->getProcessedUri(), '/'))
        ];
        
        Response::success([
            'server_variables' => $serverData,
            'path_info' => $pathInfo,
            'env_variables' => [
                'ENVIRONMENT' => Config::getEnvironment(),
                'BASE_URL' => Config::getBaseUrl()
            ],
            'current_dir' => __DIR__,
            'base_dir' => dirname(__DIR__)
        ]);
    }
    
    /**
     * Helper para obter a URI processada
     */
    private function getProcessedUri() {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $uri = parse_url($requestUri, PHP_URL_PATH);
        $basePath = Config::getBasePath();
        
        if (!empty($basePath) && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        return '/' . ltrim($uri, '/');
    }
}