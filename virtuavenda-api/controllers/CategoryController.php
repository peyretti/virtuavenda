<?php
namespace App\Controllers;

use App\Models\Category;
use App\Utils\Response;
use App\Middleware\AuthMiddleware;
use App\Utils\Validator;

class CategoryController {
    private $categoryModel;
    
    public function __construct() {
        $this->categoryModel = new Category();
    }
    
    /**
     * Lista todas as categorias da loja ou busca por ID específico
     */
    public function index() {
        // Verifica o token JWT
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        // Verifica se foi passado um ID específico
        $categoryId = $_GET['id'] ?? null;
        
        if ($categoryId) {
            // Busca categoria específica
            $categoryId = Validator::sanitizeInt($categoryId);
            
            if (!$categoryId || $categoryId <= 0) {
                Response::error('ID da categoria inválido', 400);
            }
            
            $category = $this->categoryModel->getCategoryById($storeId, $categoryId);
            
            if (!$category) {
                Response::error('Categoria não encontrada', 404);
            }
            
            Response::success($category);
        } else {
            // Lista todas as categorias da loja
            $categories = $this->categoryModel->getAllCategories($storeId);
            
            Response::success([
                'categories' => $categories,
                'total' => count($categories)
            ]);
        }
    }
    
    /**
     * Busca uma categoria específica por ID
     */
    public function show($categoryId) {
        // Verifica o token JWT
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        // Sanitiza o ID
        $categoryId = Validator::sanitizeInt($categoryId);
        
        if (!$categoryId || $categoryId <= 0) {
            Response::error('ID da categoria inválido', 400);
        }
        
        $category = $this->categoryModel->getCategoryById($storeId, $categoryId);
        
        if (!$category) {
            Response::error('Categoria não encontrada', 404);
        }
        
        Response::success($category);
    }
    
    /**
     * Cria uma nova categoria
     */
    public function store() {
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
        if (!isset($data['nome_categoria']) || empty(trim($data['nome_categoria']))) {
            Response::error('Nome da categoria é obrigatório', 400);
        }
        
        $categoryName = Validator::sanitizeString($data['nome_categoria']);
        
        if (strlen($categoryName) > 45) {
            Response::error('Nome da categoria deve ter no máximo 45 caracteres', 400);
        }
        
        // Verifica se já existe uma categoria com esse nome na loja
        if ($this->categoryModel->categoryExists($storeId, $categoryName)) {
            Response::error('Já existe uma categoria com este nome', 409);
        }
        
        $categoryData = [
            'nome_categoria' => $categoryName,
            'ativo' => 'S'
        ];
        
        $categoryId = $this->categoryModel->createCategory($storeId, $categoryData);
        
        if ($categoryId) {
            $newCategory = $this->categoryModel->getCategoryById($storeId, $categoryId);
            Response::success($newCategory, 201);
        } else {
            Response::error('Erro ao criar categoria', 500);
        }
    }
    
    /**
     * Atualiza uma categoria existente
     */
    public function update($categoryId) {
        // Verifica se a requisição é PUT
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::error('Método não permitido', 405);
        }
        
        // Verifica o token JWT
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        // Sanitiza o ID
        $categoryId = Validator::sanitizeInt($categoryId);
        
        if (!$categoryId || $categoryId <= 0) {
            Response::error('ID da categoria inválido', 400);
        }
        
        // Verifica se a categoria existe
        $existingCategory = $this->categoryModel->getCategoryById($storeId, $categoryId);
        if (!$existingCategory) {
            Response::error('Categoria não encontrada', 404);
        }
        
        // Obtém o corpo da requisição
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Valida os dados recebidos
        if (!isset($data['nome_categoria']) || empty(trim($data['nome_categoria']))) {
            Response::error('Nome da categoria é obrigatório', 400);
        }
        
        $categoryName = Validator::sanitizeString($data['nome_categoria']);
        
        if (strlen($categoryName) > 45) {
            Response::error('Nome da categoria deve ter no máximo 45 caracteres', 400);
        }
        
        // Verifica se já existe outra categoria com esse nome na loja
        if ($this->categoryModel->categoryExists($storeId, $categoryName, $categoryId)) {
            Response::error('Já existe uma categoria com este nome', 409);
        }
        
        $categoryData = [
            'nome_categoria' => $categoryName
        ];
        
        // Se foi passado o status ativo, inclui na atualização
        if (isset($data['ativo']) && in_array($data['ativo'], ['S', 'N'])) {
            $categoryData['ativo'] = $data['ativo'];
        }
        
        $updated = $this->categoryModel->updateCategory($storeId, $categoryId, $categoryData);
        
        if ($updated) {
            $updatedCategory = $this->categoryModel->getCategoryById($storeId, $categoryId);
            Response::success($updatedCategory);
        } else {
            Response::error('Erro ao atualizar categoria', 500);
        }
    }
    
    /**
     * Desativa uma categoria (soft delete)
     */
    public function delete($categoryId) {
        // Verifica se a requisição é DELETE
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            Response::error('Método não permitido', 405);
        }
        
        // Verifica o token JWT
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        // Sanitiza o ID
        $categoryId = Validator::sanitizeInt($categoryId);
        
        if (!$categoryId || $categoryId <= 0) {
            Response::error('ID da categoria inválido', 400);
        }
        
        // Verifica se a categoria existe
        $existingCategory = $this->categoryModel->getCategoryById($storeId, $categoryId);
        if (!$existingCategory) {
            Response::error('Categoria não encontrada', 404);
        }
        
        // Verifica se existem produtos vinculados a esta categoria
        if ($this->categoryModel->hasProducts($storeId, $categoryId)) {
            Response::error('Não é possível excluir categoria que possui produtos vinculados', 409);
        }
        
        $deleted = $this->categoryModel->deleteCategory($storeId, $categoryId);
        
        if ($deleted) {
            Response::success(['message' => 'Categoria desativada com sucesso']);
        } else {
            Response::error('Erro ao desativar categoria', 500);
        }
    }
}