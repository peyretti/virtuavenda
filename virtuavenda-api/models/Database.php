<?php
namespace App\Models;

use App\Config\Database as DatabaseConfig;

class Database {
    private $pdo;
    
    public function __construct() {
        $this->pdo = DatabaseConfig::getInstance();
    }
    
    /**
     * Executa uma consulta SQL com parâmetros vinculados
     * 
     * @param string $sql A consulta SQL
     * @param array $params Parâmetros para vincular à consulta
     * @return \PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            error_log('Erro na consulta SQL: ' . $e->getMessage());
            throw new \Exception('Erro ao executar consulta no banco de dados');
        }
    }
    
    /**
     * Obtém um único registro
     * 
     * @param string $sql A consulta SQL
     * @param array $params Parâmetros para vincular à consulta
     * @return array|null
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Obtém todos os registros
     * 
     * @param string $sql A consulta SQL
     * @param array $params Parâmetros para vincular à consulta
     * @return array
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll() ?: [];
    }
    
    /**
     * Insere um registro e retorna o ID
     * 
     * @param string $table Nome da tabela
     * @param array $data Dados a serem inseridos (coluna => valor)
     * @return int O ID do registro inserido
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $this->query($sql, array_values($data));
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Atualiza registros
     * 
     * @param string $table Nome da tabela
     * @param array $data Dados a serem atualizados (coluna => valor)
     * @param string $where Condição WHERE
     * @param array $params Parâmetros para a condição WHERE
     * @return int Número de linhas afetadas
     */
    public function update($table, $data, $where, $params = []) {
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "{$column} = ?";
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $sets) . " WHERE {$where}";
        
        $stmt = $this->query($sql, array_merge(array_values($data), $params));
        return $stmt->rowCount();
    }
    
    /**
     * Exclui registros
     * 
     * @param string $table Nome da tabela
     * @param string $where Condição WHERE
     * @param array $params Parâmetros para a condição WHERE
     * @return int Número de linhas afetadas
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
}