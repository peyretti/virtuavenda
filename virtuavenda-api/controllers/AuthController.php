<?php
namespace App\Controllers;

use App\Models\Store;
use App\Utils\Response;
use App\Middleware\AuthMiddleware;
use App\Utils\Validator;

class AuthController {
    private $storeModel;
    
    public function __construct() {
        $this->storeModel = new Store();
    }
    
    /**
     * Autentica uma loja e gera um token JWT
     */
    public function login() {
        // Verifica se a requisição é POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Método não permitido', 405);
        }
        
        // Obtém o corpo da requisição
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Valida os dados recebidos
        if (!isset($data['url']) || empty($data['url'])) {
            Response::error('URL da loja é obrigatória', 400);
        }
        
        if (!isset($data['token']) || empty($data['token'])) {
            Response::error('Token da loja é obrigatório', 400);
        }
        
        // Sanitiza a URL - substituição para FILTER_SANITIZE_STRING
        $storeUrl = htmlspecialchars(strip_tags(trim($data['url'])), ENT_QUOTES, 'UTF-8');
        
        // Busca a loja pela URL
        $store = $this->storeModel->getStoreByUrl($storeUrl);
        
        if (!$store) {
            Response::error('Loja não encontrada ou inativa', 404);
        }
        
        // Valida o token da loja
        if (!$this->storeModel->validateToken($store['id_loja'], $data['token'])) {
            Response::error('Token inválido', 401);
        }
        
        // Registra o login
        $this->storeModel->recordLogin($store['id_loja']);
        
        // Gera um token JWT para a loja
        $jwt = AuthMiddleware::generateToken($store['id_loja'], $store['url_loja']);
        
        // Retorna o token e informações básicas da loja
        Response::success([
            'token' => $jwt,
            'expires_in' => \App\Config\Config::getJwtExpiration(),
            'store' => [
                'id' => $store['id_loja'],
                'name' => $store['nome_loja'],
                'url' => $store['url_loja']
            ]
        ]);
    }
    
    // Resto da classe permanece o mesmo...
    
    /**
     * Verifica se o token JWT é válido
     */
    public function validateToken() {
        try {
            $payload = AuthMiddleware::verifyToken();
            
            // Obtém informações da loja
            $storeInfo = $this->storeModel->getStorePublicInfo($payload['store_id']);
            
            Response::success([
                'valid' => true,
                'store_id' => $payload['store_id'],
                'store_url' => $payload['store_url'],
                'expires_at' => $payload['exp'],
                'store' => $storeInfo
            ]);
        } catch (\Exception $e) {
            Response::error('Token inválido: ' . $e->getMessage(), 401);
        }
    }
    
    /**
     * Renova um token JWT
     */
    public function renewToken() {
        // Verifica se a requisição é POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Método não permitido', 405);
        }
        
        // Verifica se o cabeçalho Authorization está presente
        $headers = getallheaders();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
        
        if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            Response::error('Token de autorização não fornecido', 401);
        }
        
        $currentToken = $matches[1];
        
        // Tenta renovar o token
        $newToken = AuthMiddleware::renewToken($currentToken);
        
        if (!$newToken) {
            Response::error('Não foi possível renovar o token', 401);
        }
        
        // Obtém informações do token
        $tokenInfo = AuthMiddleware::getTokenInfo($newToken);
        
        Response::success([
            'token' => $newToken,
            'expires_in' => \App\Config\Config::getJwtExpiration(),
            'expires_at' => $tokenInfo['exp']
        ]);
    }
    
    /**
     * Encerra a sessão (apenas para logging, o JWT não pode ser invalidado)
     */
    public function logout() {
        // Verifica se o token é válido
        $payload = AuthMiddleware::verifyToken();
        
        // Registramos o logout por questões de auditoria
        // (na prática, o cliente deve simplesmente descartar o token)
        
        Response::success([
            'message' => 'Logout realizado com sucesso'
        ]);
    }
}