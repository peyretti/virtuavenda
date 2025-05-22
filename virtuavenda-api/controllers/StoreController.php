<?php
namespace App\Controllers;

use App\Models\Store;
use App\Utils\Response;
use App\Middleware\AuthMiddleware;
use App\Middleware\StoreMiddleware;

class StoreController {
    private $storeModel;
    
    public function __construct() {
        $this->storeModel = new Store();
    }
    
    /**
     * Obtém informações básicas da loja
     */
    public function getInfo() {
        // Verifica o token JWT
        $payload = AuthMiddleware::verifyToken();
        
        // Obtém e valida o store_id do token
        $storeId = $payload['store_id'];
        
        // Obtém as informações da loja
        $storeInfo = $this->storeModel->getStoreInfo($storeId);
        
        if (!$storeInfo) {
            Response::error('Loja não encontrada ou inativa', 404);
        }
        
        Response::success($storeInfo);
    }
}