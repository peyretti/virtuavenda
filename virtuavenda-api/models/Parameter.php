<?php
namespace App\Models;

class Parameter {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Busca parâmetros de uma loja por grupo
     * 
     * @param int $storeId ID da loja
     * @param int $groupId ID do grupo de parâmetros
     * @return array Lista de parâmetros do grupo
     */
    public function getParametersByGroup($storeId, $groupId) {
        $sql = "SELECT chave, valor 
                FROM parametros 
                WHERE id_loja = ? AND id_grupos_param = ?
                ORDER BY chave";
        
        return $this->db->fetchAll($sql, [$storeId, $groupId]);
    }
    
    /**
     * Busca um parâmetro específico por chave e grupo
     * 
     * @param int $storeId ID da loja
     * @param int $groupId ID do grupo de parâmetros
     * @param string $key Chave do parâmetro
     * @return array|null Dados do parâmetro ou null se não encontrado
     */
    public function getParameterByKey($storeId, $groupId, $key) {
        $sql = "SELECT chave, valor, descricao
                FROM parametros 
                WHERE id_loja = ? AND id_grupos_param = ? AND chave = ?
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [$storeId, $groupId, $key]);
    }
    
    /**
     * Busca todos os grupos de parâmetros disponíveis
     * 
     * @return array Lista de grupos de parâmetros
     */
    public function getAllGroups() {
        $sql = "SELECT id_grupos_param, nome_grupo 
                FROM grupos_param 
                ORDER BY nome_grupo";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Verifica se um grupo de parâmetros existe
     * 
     * @param int $groupId ID do grupo
     * @return bool Verdadeiro se o grupo existe
     */
    public function groupExists($groupId) {
        $sql = "SELECT COUNT(*) as count 
                FROM grupos_param 
                WHERE id_grupos_param = ?";
        
        $result = $this->db->fetchOne($sql, [$groupId]);
        return $result['count'] > 0;
    }
    
    /**
     * Busca informações do grupo de parâmetros
     * 
     * @param int $groupId ID do grupo
     * @return array|null Dados do grupo ou null se não encontrado
     */
    public function getGroupById($groupId) {
        $sql = "SELECT id_grupos_param, nome_grupo 
                FROM grupos_param 
                WHERE id_grupos_param = ?
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [$groupId]);
    }
}