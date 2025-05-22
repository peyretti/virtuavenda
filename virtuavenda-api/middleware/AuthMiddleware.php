<?php
namespace App\Middleware;

use App\Utils\Response;
use App\Models\Store;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use App\Config\Config;

class AuthMiddleware {
    /**
     * Verifica e valida o token JWT
     * 
     * @return array Dados do payload do token
     */
    public static function verifyToken() {
        // Verifica se o cabeçalho Authorization está presente
        $headers = getallheaders();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
        
        if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            Response::error('Token de autorização não fornecido', 401);
        }
        
        $token = $matches[1];
        
        try {
            // Decodifica o token
            $decoded = JWT::decode($token, new Key(Config::getJwtSecret(), 'HS256'));
            
            // Verifica se o token expirou
            if ($decoded->exp < time()) {
                Response::error('Token expirado', 401);
            }
            
            // Verifica se a loja ainda existe e está ativa
            $store = new Store();
            $storeInfo = $store->getStoreById($decoded->store_id);
            
            if (!$storeInfo) {
                Response::error('Loja não encontrada ou inativa', 401);
            }
            
            return (array) $decoded;
        } catch (ExpiredException $e) {
            Response::error('Token expirado', 401);
        } catch (\Exception $e) {
            Response::error('Token inválido: ' . $e->getMessage(), 401);
        }
    }
    
    /**
     * Gera um novo token JWT para uma loja
     * 
     * @param int $storeId ID da loja
     * @param string $storeUrl URL da loja
     * @return string Token JWT gerado
     */
    public static function generateToken($storeId, $storeUrl) {
        $issuedAt = time();
        $expirationTime = $issuedAt + Config::getJwtExpiration();
        
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'store_id' => $storeId,
            'store_url' => $storeUrl
        ];
        
        return JWT::encode($payload, Config::getJwtSecret(), 'HS256');
    }
    
    /**
     * Obtém informações do token atual sem validar a expiração
     * Útil para renovação de tokens
     * 
     * @param string $token Token JWT
     * @return array|false Informações do token ou falso se inválido
     */
    public static function getTokenInfo($token) {
        try {
            $decoded = JWT::decode($token, new Key(Config::getJwtSecret(), 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Renova um token JWT existente
     * 
     * @param string $token Token JWT atual
     * @return string|false Novo token ou falso se não foi possível renovar
     */
    public static function renewToken($token) {
        $tokenInfo = self::getTokenInfo($token);
        
        if (!$tokenInfo) {
            return false;
        }
        
        // Verifica se a loja ainda existe e está ativa
        $store = new Store();
        $storeInfo = $store->getStoreById($tokenInfo['store_id']);
        
        if (!$storeInfo) {
            return false;
        }
        
        // Gera um novo token
        return self::generateToken($tokenInfo['store_id'], $tokenInfo['store_url']);
    }
}