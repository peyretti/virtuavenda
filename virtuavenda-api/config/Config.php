<?php
namespace App\Config;

use Dotenv\Dotenv;

// Carrega variáveis de ambiente do arquivo .env se existir
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

class Config {
    // Configurações gerais
    const APP_NAME = 'VirtuaVenda API';
    const APP_VERSION = '1.0.0';
    
    // Configurações de CORS
    const ALLOW_ORIGINS = ['*']; // Em produção, especifique os domínios permitidos
    const ALLOW_METHODS = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];
    const ALLOW_HEADERS = ['Content-Type', 'Authorization', 'X-Requested-With'];
    
    // Detecta automaticamente o base path
    public static function getBasePath() {
        // Se definido no .env, use esse valor
        if (isset($_ENV['BASE_PATH'])) {
            return $_ENV['BASE_PATH'];
        }
        
        // Tenta detectar automaticamente a partir da REQUEST_URI e SCRIPT_NAME
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptDir = dirname($scriptName);
        
        // Se estamos acessando diretamente o index.php
        if (strpos($scriptName, 'index.php') !== false) {
            // Remove /public/index.php ou /index.php do final
            return preg_replace('#/public(/index\.php)?$#', '', $scriptDir);
        }
        
        // Se estamos usando mod_rewrite
        return $scriptDir;
    }
    
    // Ambiente de execução
    public static function getEnvironment() {
        return $_ENV['ENVIRONMENT'] ?? 'production';
    }
    
    // URL base da API
    public static function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $basePath = self::getBasePath();
        
        return "{$protocol}://{$host}{$basePath}";
    }
    
    // Configurações de segurança
    public static function getJwtSecret() {
        return $_ENV['JWT_SECRET'] ?? 'chave_secreta_padrao';
    }
    
    public static function getJwtExpiration() {
        return (int)($_ENV['JWT_EXPIRATION'] ?? 3600);
    }
    
    // Configurações do banco de dados
    public static function getDbHost() {
        return $_ENV['DB_HOST'] ?? 'localhost';
    }
    
    public static function getDbName() {
        return $_ENV['DB_NAME'] ?? 'virtuavenda';
    }
    
    public static function getDbUser() {
        return $_ENV['DB_USER'] ?? 'root';
    }
    
    public static function getDbPass() {
        return $_ENV['DB_PASS'] ?? '';
    }
}