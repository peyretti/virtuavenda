<?php
namespace App\Models;

class Category {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Obtém todas as categorias de uma loja
     * 
     * @param int $storeId ID da loja
     * @return array Lista de categorias
     */
    public function getAllCategories($storeId) {
        $sql = "SELECT 
                    id_produtos_categorias,
                    nome_categoria
                FROM produtos_categorias 
                WHERE id_loja = ? AND ativo = 'S'
                ORDER BY nome_categoria";
        
        return $this->db->fetchAll($sql, [$storeId]);
    }
    
    /**
     * Obtém uma categoria específica por ID
     * 
     * @param int $storeId ID da loja
     * @param int $categoryId ID da categoria
     * @return array|null Dados da categoria ou null se não existir
     */
    public function getCategoryById($storeId, $categoryId) {
        $sql = "SELECT 
                    id_produtos_categorias,
                    nome_categoria,
                    ativo
                FROM produtos_categorias 
                WHERE id_loja = ? AND id_produtos_categorias = ? AND ativo = 'S'
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [$storeId, $categoryId]);
    }
    
    /**
     * Verifica se uma categoria com o nome já existe na loja
     * 
     * @param int $storeId ID da loja
     * @param string $categoryName Nome da categoria
     * @param int|null $excludeId ID a ser excluído da verificação (para updates)
     * @return bool Verdadeiro se a categoria já existe
     */
    public function categoryExists($storeId, $categoryName, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count 
                FROM produtos_categorias 
                WHERE id_loja = ? AND nome_categoria = ? AND ativo = 'S'";
        
        $params = [$storeId, $categoryName];
        
        if ($excludeId) {
            $sql .= " AND id_produtos_categorias != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Cria uma nova categoria
     * 
     * @param int $storeId ID da loja
     * @param array $categoryData Dados da categoria
     * @return int ID da categoria criada
     */
    public function createCategory($storeId, $categoryData) {
        $categoryData['id_loja'] = $storeId;
        
        try {
            return $this->db->insert('produtos_categorias', $categoryData);
        } catch (\Exception $e) {
            error_log('Erro ao criar categoria: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Atualiza uma categoria existente
     * 
     * @param int $storeId ID da loja
     * @param int $categoryId ID da categoria
     * @param array $categoryData Dados a serem atualizados
     * @return bool Sucesso da operação
     */
    public function updateCategory($storeId, $categoryId, $categoryData) {
        try {
            $affected = $this->db->update(
                'produtos_categorias',
                $categoryData,
                'id_loja = ? AND id_produtos_categorias = ?',
                [$storeId, $categoryId]
            );
            
            return $affected > 0;
        } catch (\Exception $e) {
            error_log('Erro ao atualizar categoria: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Desativa uma categoria (soft delete)
     * 
     * @param int $storeId ID da loja
     * @param int $categoryId ID da categoria
     * @return bool Sucesso da operação
     */
    public function deleteCategory($storeId, $categoryId) {
        return $this->updateCategory($storeId, $categoryId, ['ativo' => 'N']);
    }
    
    /**
     * Verifica se a categoria possui produtos vinculados
     * 
     * @param int $storeId ID da loja
     * @param int $categoryId ID da categoria
     * @return bool Verdadeiro se possui produtos
     */
    public function hasProducts($storeId, $categoryId) {
        $sql = "SELECT COUNT(*) as count 
                FROM produtos 
                WHERE id_loja = ? AND id_produtos_categorias = ? AND ativo = 'S'";
        
        $result = $this->db->fetchOne($sql, [$storeId, $categoryId]);
        return $result['count'] > 0;
    }
    
    /**
     * Obtém estatísticas da categoria
     * 
     * @param int $storeId ID da loja
     * @param int $categoryId ID da categoria
     * @return array Estatísticas da categoria
     */
    public function getCategoryStats($storeId, $categoryId) {
        $sql = "SELECT 
                    COUNT(*) as total_produtos,
                    SUM(CASE WHEN qtd_estoque > 0 THEN 1 ELSE 0 END) as produtos_com_estoque,
                    SUM(qtd_estoque) as total_estoque
                FROM produtos 
                WHERE id_loja = ? AND id_produtos_categorias = ? AND ativo = 'S'";
        
        return $this->db->fetchOne($sql, [$storeId, $categoryId]);
    }
    
    /**
     * Obtém todas as categorias incluindo as inativas (para administração)
     * 
     * @param int $storeId ID da loja
     * @return array Lista de todas as categorias
     */
    public function getAllCategoriesAdmin($storeId) {
        $sql = "SELECT 
                    id_produtos_categorias,
                    nome_categoria,
                    ativo,
                    (SELECT COUNT(*) FROM produtos p WHERE p.id_produtos_categorias = pc.id_produtos_categorias AND p.ativo = 'S') as total_produtos
                FROM produtos_categorias pc
                WHERE id_loja = ?
                ORDER BY ativo DESC, nome_categoria";
        
        return $this->db->fetchAll($sql, [$storeId]);
    }
}