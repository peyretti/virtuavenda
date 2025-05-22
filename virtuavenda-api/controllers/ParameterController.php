<?php
namespace App\Controllers;

use App\Models\Parameter;
use App\Utils\Response;
use App\Middleware\AuthMiddleware;
use App\Utils\Validator;

class ParameterController {
    private $parameterModel;
    
    public function __construct() {
        $this->parameterModel = new Parameter();
    }
    
    /**
     * Busca parâmetros por grupo
     */
    public function getByGroup() {
        // Verifica o token JWT
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        // Obtém o ID do grupo da URL ou query string
        $groupId = null;
        
        // Primeiro tenta pegar da URL (/parameters/group/{id})
        $requestUri = $_SERVER['REQUEST_URI'];
        $pathParts = explode('/', trim(parse_url($requestUri, PHP_URL_PATH), '/'));
        
        for ($i = 0; $i < count($pathParts); $i++) {
            if ($pathParts[$i] === 'group' && isset($pathParts[$i + 1])) {
                $groupId = intval($pathParts[$i + 1]);
                break;
            }
        }
        
        // Se não encontrou na URL, tenta pegar da query string (?group_id=X)
        if (!$groupId && isset($_GET['group_id'])) {
            $groupId = intval($_GET['group_id']);
        }
        
        if (!$groupId || $groupId <= 0) {
            Response::error('ID do grupo de parâmetros é obrigatório', 400);
        }
        
        try {
            // Verifica se o grupo existe
            if (!$this->parameterModel->groupExists($groupId)) {
                Response::error('Grupo de parâmetros não encontrado', 404);
            }
            
            // Busca os parâmetros do grupo
            $parameters = $this->parameterModel->getParametersByGroup($storeId, $groupId);
            
            // Busca informações do grupo
            $groupInfo = $this->parameterModel->getGroupById($groupId);
            
            Response::success([
                'group' => $groupInfo,
                'parameters' => $parameters,
                'total' => count($parameters)
            ]);
        } catch (\Exception $e) {
            error_log('Erro ao buscar parâmetros: ' . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }
    
    /**
     * Lista todos os grupos de parâmetros disponíveis
     */
    public function getGroups() {
        try {
            $groups = $this->parameterModel->getAllGroups();
            
            Response::success([
                'groups' => $groups,
                'total' => count($groups)
            ]);
        } catch (\Exception $e) {
            error_log('Erro ao buscar grupos de parâmetros: ' . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }
    
    /**
     * Busca um parâmetro específico por chave dentro de um grupo
     */
    public function getByKey() {
        // Verifica o token JWT
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        // Obtém parâmetros da query string
        $groupId = isset($_GET['group_id']) ? intval($_GET['group_id']) : null;
        $key = isset($_GET['key']) ? Validator::sanitizeString($_GET['key']) : null;
        
        if (!$groupId || $groupId <= 0) {
            Response::error('ID do grupo de parâmetros é obrigatório', 400);
        }
        
        if (!$key || empty($key)) {
            Response::error('Chave do parâmetro é obrigatória', 400);
        }
        
        try {
            // Verifica se o grupo existe
            if (!$this->parameterModel->groupExists($groupId)) {
                Response::error('Grupo de parâmetros não encontrado', 404);
            }
            
            // Busca o parâmetro específico
            $parameter = $this->parameterModel->getParameterByKey($storeId, $groupId, $key);
            
            if (!$parameter) {
                Response::error('Parâmetro não encontrado', 404);
            }
            
            // Busca informações do grupo
            $groupInfo = $this->parameterModel->getGroupById($groupId);
            
            Response::success([
                'group' => $groupInfo,
                'parameter' => $parameter
            ]);
        } catch (\Exception $e) {
            error_log('Erro ao buscar parâmetro: ' . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }
}