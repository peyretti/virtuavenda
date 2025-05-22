<?php
namespace App\Controllers;

use App\Models\Product;
use App\Utils\Response;
use App\Middleware\AuthMiddleware;
use App\Utils\Validator;

class ProductController {
    private $productModel;
    
    public function __construct() {
        $this->productModel = new Product();
    }
    
    /**
     * Lista todos os produtos da loja
     */
    public function getAll() {
        // Verifica o token JWT
        $payload = AuthMiddleware::verifyToken();
        
        // Obtém e valida o store_id do token
        $storeId = $payload['store_id'];
        
        // Parâmetros de paginação e filtros opcionais
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 20;
        $categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
        $search = isset($_GET['search']) ? Validator::sanitizeString($_GET['search']) : null;
        
        // Calcula o offset
        $offset = ($page - 1) * $limit;
        
        try {
            // Busca os produtos
            $products = $this->productModel->getAllProducts($storeId, $limit, $offset, $categoryId, $search);
            
            // Conta o total de produtos para paginação
            $total = $this->productModel->countProducts($storeId, $categoryId, $search);
            
            // Calcula informações de paginação
            $totalPages = ceil($total / $limit);
            
            Response::success([
                'products' => $products,
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
            error_log('Erro ao buscar produtos: ' . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }
    
    /**
     * Busca um produto específico pelo ID
     */
    public function getById() {
        // Verifica o token JWT
        $payload = AuthMiddleware::verifyToken();
        
        // Obtém e valida o store_id do token
        $storeId = $payload['store_id'];
        
        // Obtém o ID do produto da URL
        $requestUri = $_SERVER['REQUEST_URI'];
        $pathParts = explode('/', trim(parse_url($requestUri, PHP_URL_PATH), '/'));
        
        // Procura pelo ID do produto na URL (formato: /products/{id})
        $productId = null;
        for ($i = 0; $i < count($pathParts); $i++) {
            if ($pathParts[$i] === 'products' && isset($pathParts[$i + 1])) {
                $productId = intval($pathParts[$i + 1]);
                break;
            }
        }
        
        if (!$productId) {
            Response::error('ID do produto não fornecido', 400);
        }
        
        try {
            // Busca o produto
            $product = $this->productModel->getProductById($storeId, $productId);
            
            if (!$product) {
                Response::error('Produto não encontrado', 404);
            }
            
            // Busca as imagens do produto
            $images = $this->productModel->getProductImages($productId);
            
            // Busca as variações se o produto tiver variações
            $variations = [];
            $synthesizedOptions = [];
            
            if ($product['variacoes'] === 'S') {
                $variations = $this->productModel->getProductVariations($productId);
                $synthesizedOptions = $this->productModel->getSynthesizedOptions($productId);
            }
            
            // Monta a resposta completa
            $response = [
                'produto' => $product,
                'variacoes' => $variations,
                'opcoes_sintetizadas' => $synthesizedOptions,
                'imagens' => $images
            ];
            
            Response::success($response);
        } catch (\Exception $e) {
            error_log('Erro ao buscar produto: ' . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }
    
    /**
     * Busca produtos por categoria
     */
    public function getByCategory() {
        // Verifica o token JWT
        $payload = AuthMiddleware::verifyToken();
        
        // Obtém e valida o store_id do token
        $storeId = $payload['store_id'];
        
        // Obtém o ID da categoria da URL
        $requestUri = $_SERVER['REQUEST_URI'];
        $pathParts = explode('/', trim(parse_url($requestUri, PHP_URL_PATH), '/'));
        
        // Procura pelo ID da categoria na URL (formato: /products/category/{id})
        $categoryId = null;
        for ($i = 0; $i < count($pathParts); $i++) {
            if ($pathParts[$i] === 'category' && isset($pathParts[$i + 1])) {
                $categoryId = intval($pathParts[$i + 1]);
                break;
            }
        }
        
        if (!$categoryId) {
            Response::error('ID da categoria não fornecido', 400);
        }
        
        // Parâmetros de paginação
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 20;
        $offset = ($page - 1) * $limit;
        
        try {
            // Busca os produtos da categoria
            $products = $this->productModel->getAllProducts($storeId, $limit, $offset, $categoryId);
            
            // Conta o total de produtos da categoria
            $total = $this->productModel->countProducts($storeId, $categoryId);
            
            // Calcula informações de paginação
            $totalPages = ceil($total / $limit);
            
            Response::success([
                'products' => $products,
                'category_id' => $categoryId,
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
            error_log('Erro ao buscar produtos por categoria: ' . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }
}