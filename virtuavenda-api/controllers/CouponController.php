<?php
namespace App\Controllers;

use App\Models\Coupon;
use App\Utils\Response;
use App\Middleware\AuthMiddleware;
use App\Utils\Validator;

class CouponController {
    private $couponModel;
    
    public function __construct() {
        $this->couponModel = new Coupon();
    }
    
    /**
     * Lista todos os cupons da loja
     */
    public function index() {
        // Verifica o token JWT
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        // Parâmetros de paginação
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 20;
        $offset = ($page - 1) * $limit;
        
        try {
            // Busca os cupons
            $coupons = $this->couponModel->getAllCoupons($storeId, $limit, $offset);
            
            // Conta o total de cupons para paginação
            $total = $this->couponModel->countCoupons($storeId);
            
            // Calcula informações de paginação
            $totalPages = ceil($total / $limit);
            
            Response::success([
                'coupons' => $coupons,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => $totalPages,
                    'has_next' => $page < $totalPages,
                    'has_prev' => $page > 1
                ]
            ]);
        } catch (\Exception $e) {
            error_log('Erro ao buscar cupons: ' . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }
    
    /**
     * Busca um cupom específico pelo ID
     */
    public function show($couponId) {
        // Verifica o token JWT
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        // Sanitiza o ID
        $couponId = Validator::sanitizeInt($couponId);
        
        if (!$couponId || $couponId <= 0) {
            Response::error('ID do cupom inválido', 400);
        }
        
        try {
            $coupon = $this->couponModel->getCouponById($storeId, $couponId);
            
            if (!$coupon) {
                Response::error('Cupom não encontrado', 404);
            }
            
            // Adiciona informações de status
            $coupon['status_cupom'] = $this->getCouponStatus($coupon);
            
            Response::success($coupon);
        } catch (\Exception $e) {
            error_log('Erro ao buscar cupom: ' . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }
    
    /**
     * Valida um cupom pelo código
     */
    public function validate() {
        // Verifica se a requisição é POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Método não permitido', 405);
        }
        
        // Verifica o token JWT
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        // Obtém o corpo da requisição
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Valida os dados recebidos
        if (!isset($data['codigo_cupom']) || empty(trim($data['codigo_cupom']))) {
            Response::error('Código do cupom é obrigatório', 400);
        }
        
        $couponCode = Validator::sanitizeString($data['codigo_cupom']);
        
        try {
            $validation = $this->couponModel->validateCoupon($storeId, $couponCode);
            
            if ($validation['valid']) {
                Response::success($validation);
            } else {
                Response::error($validation['message'], 400, [
                    'error_code' => $validation['error_code'],
                    'details' => $validation
                ]);
            }
        } catch (\Exception $e) {
            error_log('Erro ao validar cupom: ' . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }
    
    /**
     * Busca um cupom pelo código (sem validar se pode ser usado)
     */
    public function getByCode() {
        // Verifica o token JWT
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        // Obtém o código do cupom da query string
        $couponCode = $_GET['code'] ?? null;
        
        if (!$couponCode) {
            Response::error('Código do cupom é obrigatório', 400);
        }
        
        $couponCode = Validator::sanitizeString($couponCode);
        
        try {
            $coupon = $this->couponModel->getCouponByCode($storeId, $couponCode);
            
            if (!$coupon) {
                Response::error('Cupom não encontrado', 404);
            }
            
            // Adiciona informações de status
            $coupon['status_cupom'] = $this->getCouponStatus($coupon);
            
            Response::success($coupon);
        } catch (\Exception $e) {
            error_log('Erro ao buscar cupom por código: ' . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }
    
    /**
     * Busca cupons próximos do vencimento
     */
    public function nearExpiry() {
        // Verifica o token JWT
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        // Obtém a quantidade de dias (padrão: 7 dias)
        $days = isset($_GET['days']) ? max(1, intval($_GET['days'])) : 7;
        
        try {
            $coupons = $this->couponModel->getCouponsNearExpiry($storeId, $days);
            
            Response::success([
                'coupons' => $coupons,
                'days_threshold' => $days,
                'total' => count($coupons)
            ]);
        } catch (\Exception $e) {
            error_log('Erro ao buscar cupons próximos do vencimento: ' . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }
    
    /**
     * Busca estatísticas dos cupons
     */
    public function stats() {
        // Verifica o token JWT
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        try {
            $stats = $this->couponModel->getCouponStats($storeId);
            
            Response::success($stats);
        } catch (\Exception $e) {
            error_log('Erro ao buscar estatísticas dos cupons: ' . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }
    
    /**
     * Helper para determinar o status do cupom
     * 
     * @param array $coupon Dados do cupom
     * @return string Status do cupom
     */
    private function getCouponStatus($coupon) {
        $today = date('Y-m-d');
        
        if ($coupon['validade_cupom'] < $today) {
            return 'expirado';
        }
        
        if ($coupon['qtd_uso'] > 0 && $coupon['qtd_usado'] >= $coupon['qtd_uso']) {
            return 'esgotado';
        }
        
        // Verifica se está próximo do vencimento (7 dias)
        $expiryDate = new \DateTime($coupon['validade_cupom']);
        $todayDate = new \DateTime($today);
        $daysUntilExpiry = $todayDate->diff($expiryDate)->days;
        
        if ($daysUntilExpiry <= 7) {
            return 'proximo_vencimento';
        }
        
        return 'disponivel';
    }
}