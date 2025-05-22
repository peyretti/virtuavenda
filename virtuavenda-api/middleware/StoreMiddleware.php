<?php
namespace App\Middleware;

use App\Utils\Response;
use App\Models\Store;

class StoreMiddleware {
    /**
     * Verifica se a URL da loja é válida e corresponde a uma loja ativa
     * 
     * @return array Dados da loja
     */
    public static function validateStore() {
        // Obtém a URL atual
        $requestUri = $_SERVER['REQUEST_URI'];
        $path = parse_url($requestUri, PHP_URL_PATH);
        
        // Extrai a URL da loja da solicitação
        // Esperamos que o formato seja /nome-da-loja/endpoint
        $pathParts = explode('/', trim($path, '/'));
        
        if (count($pathParts) < 1) {
            Response::error('URL da loja não especificada', 400);
        }
        
        $storeUrl = $pathParts[0];
        
        // Valida a URL da loja contra o banco de dados
        $storeModel = new Store();
        $store = $storeModel->getStoreByUrl($storeUrl);
        
        if (!$store) {
            Response::error('Loja não encontrada ou inativa', 404);
        }
        
        return $store;
    }
    
    /**
     * Restringe o acesso apenas a dados da loja especificada
     * 
     * @param int $requestedStoreId ID da loja requisitada
     * @param int $authenticatedStoreId ID da loja autenticada
     */
    public static function restrictToStore($requestedStoreId, $authenticatedStoreId) {
        if ($requestedStoreId != $authenticatedStoreId) {
            Response::error('Acesso não autorizado a dados de outra loja', 403);
        }
    }
}