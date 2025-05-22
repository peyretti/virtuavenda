<?php
namespace App\Models;

class Product {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Busca todos os produtos de uma loja com paginação e filtros
     * 
     * @param int $storeId ID da loja
     * @param int $limit Limite de registros por página
     * @param int $offset Offset para paginação
     * @param int|null $categoryId ID da categoria (opcional)
     * @param string|null $search Termo de busca (opcional)
     * @return array Lista de produtos
     */
    public function getAllProducts($storeId, $limit = 20, $offset = 0, $categoryId = null, $search = null) {
        $sql = "SELECT 
            produtos.id_produtos as id_produtos, 
            produtos.id_loja as id_loja, 
            produtos.id_sequencial_loja as id_sequencial_loja, 
            produtos.nome_produto as nome_produto, 
            produtos.id_produtos_categorias as id_produtos_categorias, 
            (SELECT nome_categoria FROM produtos_categorias WHERE id_produtos_categorias = produtos.id_produtos_categorias) AS categoria,
            FORMAT(produtos.valor_produto, 2, 'pt_BR') AS valor_produto, 
            (SELECT imagem FROM produtos_imagens WHERE id_produtos = produtos.id_produtos AND destaque = 'S') AS imagem,
            produtos.frete_gratis as frete_gratis, 
            produtos.ativo as ativo,
            produtos.descricao_produto as descricao_produto,
            produtos.qtd_estoque as qtd_estoque,
            produtos.variacoes as variacoes,

            FORMAT(LEAST(
                CAST(produtos.valor_produto AS DECIMAL(10,2)), 
                CAST(COALESCE((SELECT MIN(valor_variacao) FROM produtos_variacoes_combinacoes 
                      WHERE id_produto = produtos.id_produtos AND valor_variacao > 0), produtos.valor_produto) AS DECIMAL(10,2))
            ), 2, 'pt_BR') AS menor_valor,

            FORMAT(GREATEST(
                CAST(produtos.valor_produto AS DECIMAL(10,2)), 
                CAST(COALESCE((SELECT MAX(valor_variacao) FROM produtos_variacoes_combinacoes 
                      WHERE id_produto = produtos.id_produtos AND valor_variacao > 0), produtos.valor_produto) AS DECIMAL(10,2))
            ), 2, 'pt_BR') AS maior_valor

        FROM produtos 

        WHERE 
            produtos.id_loja = ? 
            AND produtos.ativo = 'S'
            AND (
                EXISTS (
                    SELECT 1 FROM produtos_variacoes_combinacoes pvc
                    WHERE pvc.id_produto = produtos.id_produtos 
                      AND (pvc.qtd - COALESCE(pvc.qtd_reservado, 0)) > 0
                )
                OR (
                    NOT EXISTS (
                        SELECT 1 FROM produtos_variacoes_combinacoes pvc
                        WHERE pvc.id_produto = produtos.id_produtos
                    )
                    AND (produtos.qtd_estoque - COALESCE(produtos.qtd_reservado, 0)) > 0
                )
            )";
        
        $params = [$storeId];
        
        // Adiciona filtro por categoria se fornecido
        if ($categoryId) {
            $sql .= " AND produtos.id_produtos_categorias = ?";
            $params[] = $categoryId;
        }
        
        // Adiciona filtro de busca se fornecido
        if ($search) {
            $sql .= " AND (produtos.nome_produto LIKE ? OR produtos.descricao_produto LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Adiciona ordenação e paginação
        $sql .= " ORDER BY produtos.nome_produto ASC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Busca um produto específico pelo ID
     * 
     * @param int $storeId ID da loja
     * @param int $productId ID do produto
     * @return array|null Dados do produto ou null se não encontrado
     */
    public function getProductById($storeId, $productId) {
        $sql = "SELECT 
            produtos.id_produtos as id_produtos, 
            produtos.id_loja as id_loja, 
            produtos.id_sequencial_loja as id_sequencial_loja, 
            produtos.referencia_produto as referencia_produto,
            produtos.nome_produto as nome_produto, 
            produtos.id_produtos_categorias as id_produtos_categorias, 
            (SELECT nome_categoria FROM produtos_categorias WHERE id_produtos_categorias = produtos.id_produtos_categorias) AS categoria,
            FORMAT(produtos.valor_produto, 2, 'pt_BR') AS valor_produto, 
            produtos.descricao_produto as descricao_produto,
            produtos.qtd_estoque as qtd_estoque,
            produtos.qtd_reservado as qtd_reservado,
            produtos.estoque_minimo as estoque_minimo,
            produtos.localizacao as localizacao,
            produtos.controla_estoque as controla_estoque,
            produtos.variacoes as variacoes,
            produtos.disponivel_entrega as disponivel_entrega,
            produtos.frete_gratis as frete_gratis,
            produtos.video_youtube as video_youtube,
            produtos.comprimento_produto as comprimento_produto,
            produtos.largura_produto as largura_produto,
            produtos.altura_produto as altura_produto,
            produtos.peso_produto as peso_produto,
            produtos.data_cadastro as data_cadastro,
            produtos.ativo as ativo,

            FORMAT(LEAST(
                CAST(produtos.valor_produto AS DECIMAL(10,2)), 
                CAST(COALESCE((SELECT MIN(valor_variacao) FROM produtos_variacoes_combinacoes 
                      WHERE id_produto = produtos.id_produtos AND valor_variacao > 0), produtos.valor_produto) AS DECIMAL(10,2))
            ), 2, 'pt_BR') AS menor_valor,

            FORMAT(GREATEST(
                CAST(produtos.valor_produto AS DECIMAL(10,2)), 
                CAST(COALESCE((SELECT MAX(valor_variacao) FROM produtos_variacoes_combinacoes 
                      WHERE id_produto = produtos.id_produtos AND valor_variacao > 0), produtos.valor_produto) AS DECIMAL(10,2))
            ), 2, 'pt_BR') AS maior_valor

        FROM produtos 

        WHERE 
            produtos.id_loja = ? 
            AND produtos.id_produtos = ?
            AND produtos.ativo = 'S'
            AND (
                EXISTS (
                    SELECT 1 FROM produtos_variacoes_combinacoes pvc
                    WHERE pvc.id_produto = produtos.id_produtos 
                      AND (pvc.qtd - COALESCE(pvc.qtd_reservado, 0)) > 0
                )
                OR (
                    NOT EXISTS (
                        SELECT 1 FROM produtos_variacoes_combinacoes pvc
                        WHERE pvc.id_produto = produtos.id_produtos
                    )
                    AND (produtos.qtd_estoque - COALESCE(produtos.qtd_reservado, 0)) > 0
                )
            )";
        
        return $this->db->fetchOne($sql, [$storeId, $productId]);
    }
    
    /**
     * Conta o total de produtos de uma loja com filtros
     * 
     * @param int $storeId ID da loja
     * @param int|null $categoryId ID da categoria (opcional)
     * @param string|null $search Termo de busca (opcional)
     * @return int Total de produtos
     */
    public function countProducts($storeId, $categoryId = null, $search = null) {
        $sql = "SELECT COUNT(*) as total
        FROM produtos 
        WHERE 
            produtos.id_loja = ? 
            AND produtos.ativo = 'S'
            AND (
                EXISTS (
                    SELECT 1 FROM produtos_variacoes_combinacoes pvc
                    WHERE pvc.id_produto = produtos.id_produtos 
                      AND (pvc.qtd - COALESCE(pvc.qtd_reservado, 0)) > 0
                )
                OR (
                    NOT EXISTS (
                        SELECT 1 FROM produtos_variacoes_combinacoes pvc
                        WHERE pvc.id_produto = produtos.id_produtos
                    )
                    AND (produtos.qtd_estoque - COALESCE(produtos.qtd_reservado, 0)) > 0
                )
            )";
        
        $params = [$storeId];
        
        // Adiciona filtro por categoria se fornecido
        if ($categoryId) {
            $sql .= " AND produtos.id_produtos_categorias = ?";
            $params[] = $categoryId;
        }
        
        // Adiciona filtro de busca se fornecido
        if ($search) {
            $sql .= " AND (produtos.nome_produto LIKE ? OR produtos.descricao_produto LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result ? (int) $result['total'] : 0;
    }
    
    /**
     * Busca todas as imagens de um produto
     * 
     * @param int $productId ID do produto
     * @return array Lista de imagens do produto
     */
    public function getProductImages($productId) {
        $sql = "SELECT 
            id_produtos_imagens,
            imagem,
            tamanho_imagem,
            destaque
        FROM produtos_imagens 
        WHERE id_produtos = ? 
        ORDER BY destaque DESC, id_produtos_imagens ASC";
        
        return $this->db->fetchAll($sql, [$productId]);
    }
    
    /**
     * Busca as variações disponíveis de um produto
     * 
     * @param int $productId ID do produto
     * @return array Lista de variações do produto
     */
    public function getProductVariations($productId) {
        $sql = "SELECT 
            pvc.id_combinacao,
            pvc.id_variacao_1,
            pvc.id_opcao_variacao_1,
            (SELECT nome_variacao FROM variacoes WHERE id_variacao = pvc.id_variacao_1) AS nome_variacao_1,
            (SELECT nome_opcao FROM variacoes_opcoes WHERE id_variacao_opcao = pvc.id_opcao_variacao_1) AS opcao_variacao_1,
            pvc.id_variacao_2,
            pvc.id_opcao_variacao_2,
            (SELECT nome_variacao FROM variacoes WHERE id_variacao = pvc.id_variacao_2) AS nome_variacao_2,
            (SELECT nome_opcao FROM variacoes_opcoes WHERE id_variacao_opcao = pvc.id_opcao_variacao_2) AS opcao_variacao_2,
            pvc.id_variacao_3,
            pvc.id_opcao_variacao_3,
            (SELECT nome_variacao FROM variacoes WHERE id_variacao = pvc.id_variacao_3) AS nome_variacao_3,
            (SELECT nome_opcao FROM variacoes_opcoes WHERE id_variacao_opcao = pvc.id_opcao_variacao_3) AS opcao_variacao_3,
            pvc.qtd,
            pvc.qtd_reservado,
            pvc.estoque_minimo_var,
            FORMAT(COALESCE(pvc.valor_variacao, 0), 2, 'pt_BR') as valor_variacao,
            pvc.imagem,
            pvc.ativo
        FROM produtos_variacoes_combinacoes pvc
        WHERE pvc.qtd > 0 AND pvc.id_produto = ?
        ORDER BY pvc.id_combinacao";
        
        return $this->db->fetchAll($sql, [$productId]);
    }
    
    /**
     * Busca as opções sintetizadas de variações de um produto
     * Organiza as opções de variação de forma estruturada para facilitar uso no frontend
     * 
     * @param int $productId ID do produto
     * @return array Opções organizadas em formato de array indexado
     */
    public function getSynthesizedOptions($productId) {
        $variations = $this->getProductVariations($productId);
        $synthesizedOptions = [];
        $tempOptions = [];
        
        foreach ($variations as $variation) {
            // Processa variação 1
            if (!empty($variation['opcao_variacao_1'])) {
                $nomeVariacao1 = $variation['nome_variacao_1'] ?? 'Outros';
                $option = [
                    'id_variacao' => $variation['id_variacao_1'],
                    'id_opcao' => $variation['id_opcao_variacao_1'],
                    'nome' => $variation['opcao_variacao_1']
                ];
                
                if (!isset($tempOptions[$nomeVariacao1])) {
                    $tempOptions[$nomeVariacao1] = [
                        'id_variacao' => $variation['id_variacao_1'],
                        'nome_variacao' => $nomeVariacao1,
                        'opcoes' => []
                    ];
                }
                
                // Verifica se já existe para evitar duplicatas
                $exists = false;
                foreach ($tempOptions[$nomeVariacao1]['opcoes'] as $existingOption) {
                    if ($existingOption['id_opcao'] == $option['id_opcao']) {
                        $exists = true;
                        break;
                    }
                }
                
                if (!$exists) {
                    $tempOptions[$nomeVariacao1]['opcoes'][] = $option;
                }
            }
            
            // Processa variação 2
            if (!empty($variation['opcao_variacao_2'])) {
                $nomeVariacao2 = $variation['nome_variacao_2'] ?? 'Outros';
                $option = [
                    'id_variacao' => $variation['id_variacao_2'],
                    'id_opcao' => $variation['id_opcao_variacao_2'],
                    'nome' => $variation['opcao_variacao_2']
                ];
                
                if (!isset($tempOptions[$nomeVariacao2])) {
                    $tempOptions[$nomeVariacao2] = [
                        'id_variacao' => $variation['id_variacao_2'],
                        'nome_variacao' => $nomeVariacao2,
                        'opcoes' => []
                    ];
                }
                
                // Verifica se já existe para evitar duplicatas
                $exists = false;
                foreach ($tempOptions[$nomeVariacao2]['opcoes'] as $existingOption) {
                    if ($existingOption['id_opcao'] == $option['id_opcao']) {
                        $exists = true;
                        break;
                    }
                }
                
                if (!$exists) {
                    $tempOptions[$nomeVariacao2]['opcoes'][] = $option;
                }
            }
            
            // Processa variação 3
            if (!empty($variation['opcao_variacao_3'])) {
                $nomeVariacao3 = $variation['nome_variacao_3'] ?? 'Outros';
                $option = [
                    'id_variacao' => $variation['id_variacao_3'],
                    'id_opcao' => $variation['id_opcao_variacao_3'],
                    'nome' => $variation['opcao_variacao_3']
                ];
                
                if (!isset($tempOptions[$nomeVariacao3])) {
                    $tempOptions[$nomeVariacao3] = [
                        'id_variacao' => $variation['id_variacao_3'],
                        'nome_variacao' => $nomeVariacao3,
                        'opcoes' => []
                    ];
                }
                
                // Verifica se já existe para evitar duplicatas
                $exists = false;
                foreach ($tempOptions[$nomeVariacao3]['opcoes'] as $existingOption) {
                    if ($existingOption['id_opcao'] == $option['id_opcao']) {
                        $exists = true;
                        break;
                    }
                }
                
                if (!$exists) {
                    $tempOptions[$nomeVariacao3]['opcoes'][] = $option;
                }
            }
        }
        
        // Converte para array indexado e ordena pelas variações (1, 2, 3)
        foreach ($tempOptions as $variacao) {
            $synthesizedOptions[] = $variacao;
        }
        
        // Ordena pelo ID da variação para manter ordem consistente
        usort($synthesizedOptions, function($a, $b) {
            return $a['id_variacao'] - $b['id_variacao'];
        });
        
        return $synthesizedOptions;
    }
    
    /**
     * Busca produtos relacionados/similares
     * 
     * @param int $storeId ID da loja
     * @param int $productId ID do produto atual
     * @param int $categoryId ID da categoria
     * @param int $limit Limite de produtos relacionados
     * @return array Lista de produtos relacionados
     */
    public function getRelatedProducts($storeId, $productId, $categoryId, $limit = 4) {
        $sql = "SELECT 
            produtos.id_produtos as id_produtos, 
            produtos.nome_produto as nome_produto, 
            FORMAT(produtos.valor_produto, 2, 'pt_BR') AS valor_produto, 
            (SELECT imagem FROM produtos_imagens WHERE id_produtos = produtos.id_produtos AND destaque = 'S') AS imagem,
            produtos.frete_gratis as frete_gratis,

            FORMAT(LEAST(
                CAST(produtos.valor_produto AS DECIMAL(10,2)), 
                CAST(COALESCE((SELECT MIN(valor_variacao) FROM produtos_variacoes_combinacoes 
                      WHERE id_produto = produtos.id_produtos AND valor_variacao > 0), produtos.valor_produto) AS DECIMAL(10,2))
            ), 2, 'pt_BR') AS menor_valor,

            FORMAT(GREATEST(
                CAST(produtos.valor_produto AS DECIMAL(10,2)), 
                CAST(COALESCE((SELECT MAX(valor_variacao) FROM produtos_variacoes_combinacoes 
                      WHERE id_produto = produtos.id_produtos AND valor_variacao > 0), produtos.valor_produto) AS DECIMAL(10,2))
            ), 2, 'pt_BR') AS maior_valor

        FROM produtos 

        WHERE 
            produtos.id_loja = ? 
            AND produtos.id_produtos != ?
            AND produtos.id_produtos_categorias = ? 
            AND produtos.ativo = 'S'
            AND (
                EXISTS (
                    SELECT 1 FROM produtos_variacoes_combinacoes pvc
                    WHERE pvc.id_produto = produtos.id_produtos 
                      AND (pvc.qtd - COALESCE(pvc.qtd_reservado, 0)) > 0
                )
                OR (
                    NOT EXISTS (
                        SELECT 1 FROM produtos_variacoes_combinacoes pvc
                        WHERE pvc.id_produto = produtos.id_produtos
                    )
                    AND (produtos.qtd_estoque - COALESCE(produtos.qtd_reservado, 0)) > 0
                )
            )
        ORDER BY RAND() 
        LIMIT ?";
        
        return $this->db->fetchAll($sql, [$storeId, $productId, $categoryId, $limit]);
    }
}