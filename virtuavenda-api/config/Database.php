<?php
namespace App\Config;

class Database {
    // Instância PDO
    private static $instance = null;
    
    // Método para obter a instância PDO (Singleton)
    public static function getInstance() {
        if (self::$instance === null) {
            try {
                $host = Config::getDbHost();
                $dbName = Config::getDbName();
                $username = Config::getDbUser();
                $password = Config::getDbPass();
                
                $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";
                $options = [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ];
                self::$instance = new \PDO($dsn, $username, $password, $options);
            } catch (\PDOException $e) {
                error_log('Erro de conexão com o banco de dados: ' . $e->getMessage());
                throw new \Exception('Erro de conexão com o banco de dados');
            }
        }
        return self::$instance;
    }
}