<?php
namespace App\Models;

class Coupon {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Busca todos os cupons ativos de uma loja
     * 
     * @param int $storeId ID da loja
     * @param int $limit Limite de registros por página
     * @param int $offset Offset para paginação
     * @return array Lista de cupons
     */
    public function getAllCoupons($storeId, $limit = 20, $offset = 0) {
        $sql = "SELECT 
                    c.id_cupons_desconto,
                    c.codigo_cupom,
                    c.nome_cupom,
                    c.validade_cupom,
                    c.porcentual_desconto,
                    c.qtd_uso,
                    c.ativo,
                    (SELECT COUNT(*) 
                     FROM pedidos p 
                     WHERE p.codigo_cupom = c.codigo_cupom 
                       AND p.pedido_cancelado != 'S' 
                       AND p.id_loja = ?) AS qtd_usado,
                    CASE 
                        WHEN c.validade_cupom < CURDATE() THEN 'expirado'
                        WHEN c.qtd_uso > 0 AND (SELECT COUNT(*) FROM pedidos p WHERE p.codigo_cupom = c.codigo_cupom AND p.pedido_cancelado != 'S' AND p.id_loja = ?) >= c.qtd_uso THEN 'esgotado'
                        ELSE 'disponivel'
                    END AS status_cupom
                FROM cupons_desconto c
                WHERE c.id_loja = ? 
                  AND c.ativo = 'S'
                ORDER BY c.validade_cupom DESC
                LIMIT ? OFFSET ?";
        
        return $this->db->fetchAll($sql, [$storeId, $storeId, $storeId, $limit, $offset]);
    }
    
    /**
     * Conta o total de cupons ativos de uma loja
     * 
     * @param int $storeId ID da loja
     * @return int Total de cupons
     */
    public function countCoupons($storeId) {
        $sql = "SELECT COUNT(*) as total
                FROM cupons_desconto 
                WHERE id_loja = ? AND ativo = 'S'";
        
        $result = $this->db->fetchOne($sql, [$storeId]);
        return $result ? (int) $result['total'] : 0;
    }
    
    /**
     * Busca um cupom específico pelo código
     * 
     * @param int $storeId ID da loja
     * @param string $couponCode Código do cupom
     * @return array|null Dados do cupom ou null se não encontrado
     */
    public function getCouponByCode($storeId, $couponCode) {
        $sql = "SELECT 
                    c.id_cupons_desconto,
                    c.codigo_cupom,
                    c.nome_cupom,
                    c.validade_cupom,
                    c.porcentual_desconto,
                    c.qtd_uso,
                    c.ativo,
                    (SELECT COUNT(*) 
                     FROM pedidos p 
                     WHERE p.codigo_cupom = c.codigo_cupom 
                       AND p.pedido_cancelado != 'S' 
                       AND p.id_loja = ?) AS qtd_usado
                FROM cupons_desconto c
                WHERE c.id_loja = ? 
                  AND c.codigo_cupom = ? 
                  AND c.ativo = 'S'
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [$storeId, $storeId, $couponCode]);
    }
    
    /**
     * Valida se um cupom pode ser usado
     * 
     * @param int $storeId ID da loja
     * @param string $couponCode Código do cupom
     * @return array Resultado da validação
     */
    public function validateCoupon($storeId, $couponCode) {
        $sql = "SELECT 
                    c.id_cupons_desconto,
                    c.codigo_cupom,
                    c.nome_cupom,
                    c.validade_cupom,
                    c.porcentual_desconto,
                    c.qtd_uso,
                    (SELECT COUNT(*) 
                     FROM pedidos p 
                     WHERE p.codigo_cupom = c.codigo_cupom 
                       AND p.pedido_cancelado != 'S' 
                       AND p.id_loja = ?) AS qtd_usado
                FROM cupons_desconto c
                WHERE c.ativo = 'S'
                  AND c.id_loja = ?
                  AND c.codigo_cupom = ?
                  AND c.validade_cupom >= CURDATE()
                HAVING (c.qtd_uso = 0 OR qtd_usado < c.qtd_uso)
                LIMIT 1";
        
        $coupon = $this->db->fetchOne($sql, [$storeId, $storeId, $couponCode]);
        
        if (!$coupon) {
            // Verifica se o cupom existe mas está inválido
            $existingCoupon = $this->getCouponByCode($storeId, $couponCode);
            
            if (!$existingCoupon) {
                return [
                    'valid' => false,
                    'message' => 'Cupom não encontrado',
                    'error_code' => 'NOT_FOUND'
                ];
            }
            
            // Verifica se expirou
            if ($existingCoupon['validade_cupom'] < date('Y-m-d')) {
                return [
                    'valid' => false,
                    'message' => 'Cupom expirado',
                    'error_code' => 'EXPIRED',
                    'expired_date' => $existingCoupon['validade_cupom']
                ];
            }
            
            // Verifica se esgotou
            if ($existingCoupon['qtd_uso'] > 0 && $existingCoupon['qtd_usado'] >= $existingCoupon['qtd_uso']) {
                return [
                    'valid' => false,
                    'message' => 'Cupom esgotado',
                    'error_code' => 'EXHAUSTED',
                    'used_count' => $existingCoupon['qtd_usado'],
                    'max_uses' => $existingCoupon['qtd_uso']
                ];
            }
            
            return [
                'valid' => false,
                'message' => 'Cupom inválido',
                'error_code' => 'INVALID'
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Cupom válido',
            'coupon' => [
                'id' => $coupon['id_cupons_desconto'],
                'code' => $coupon['codigo_cupom'],
                'name' => $coupon['nome_cupom'],
                'discount_percentage' => $coupon['porcentual_desconto'],
                'expiry_date' => $coupon['validade_cupom'],
                'max_uses' => $coupon['qtd_uso'],
                'used_count' => $coupon['qtd_usado'],
                'remaining_uses' => $coupon['qtd_uso'] > 0 ? ($coupon['qtd_uso'] - $coupon['qtd_usado']) : null
            ]
        ];
    }
    
    /**
     * Busca um cupom específico pelo ID
     * 
     * @param int $storeId ID da loja
     * @param int $couponId ID do cupom
     * @return array|null Dados do cupom ou null se não encontrado
     */
    public function getCouponById($storeId, $couponId) {
        $sql = "SELECT 
                    c.id_cupons_desconto,
                    c.codigo_cupom,
                    c.nome_cupom,
                    c.validade_cupom,
                    c.porcentual_desconto,
                    c.qtd_uso,
                    c.ativo,
                    (SELECT COUNT(*) 
                     FROM pedidos p 
                     WHERE p.codigo_cupom = c.codigo_cupom 
                       AND p.pedido_cancelado != 'S' 
                       AND p.id_loja = ?) AS qtd_usado
                FROM cupons_desconto c
                WHERE c.id_loja = ? 
                  AND c.id_cupons_desconto = ? 
                  AND c.ativo = 'S'
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [$storeId, $storeId, $couponId]);
    }
    
    /**
     * Busca cupons que estão próximos do vencimento
     * 
     * @param int $storeId ID da loja
     * @param int $days Quantidade de dias para considerar "próximo do vencimento"
     * @return array Lista de cupons próximos do vencimento
     */
    public function getCouponsNearExpiry($storeId, $days = 7) {
        $sql = "SELECT 
                    c.id_cupons_desconto,
                    c.codigo_cupom,
                    c.nome_cupom,
                    c.validade_cupom,
                    c.porcentual_desconto,
                    c.qtd_uso,
                    (SELECT COUNT(*) 
                     FROM pedidos p 
                     WHERE p.codigo_cupom = c.codigo_cupom 
                       AND p.pedido_cancelado != 'S' 
                       AND p.id_loja = ?) AS qtd_usado,
                    DATEDIFF(c.validade_cupom, CURDATE()) AS dias_restantes
                FROM cupons_desconto c
                WHERE c.id_loja = ? 
                  AND c.ativo = 'S'
                  AND c.validade_cupom >= CURDATE()
                  AND c.validade_cupom <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
                ORDER BY c.validade_cupom ASC";
        
        return $this->db->fetchAll($sql, [$storeId, $storeId, $days]);
    }
    
    /**
     * Busca estatísticas dos cupons da loja
     * 
     * @param int $storeId ID da loja
     * @return array Estatísticas dos cupons
     */
    public function getCouponStats($storeId) {
        $sql = "SELECT 
                    COUNT(*) as total_cupons,
                    SUM(CASE WHEN validade_cupom >= CURDATE() THEN 1 ELSE 0 END) as cupons_validos,
                    SUM(CASE WHEN validade_cupom < CURDATE() THEN 1 ELSE 0 END) as cupons_expirados,
                    (SELECT COUNT(DISTINCT codigo_cupom) 
                     FROM pedidos 
                     WHERE id_loja = ? AND codigo_cupom != '' AND pedido_cancelado != 'S') as cupons_utilizados
                FROM cupons_desconto 
                WHERE id_loja = ? AND ativo = 'S'";
        
        return $this->db->fetchOne($sql, [$storeId, $storeId]);
    }
}