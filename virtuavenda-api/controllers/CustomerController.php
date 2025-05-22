<?php
namespace App\Controllers;

use App\Models\Customer;
use App\Utils\Response;
use App\Middleware\AuthMiddleware;
use App\Utils\Validator;

class CustomerController {
    private $customerModel;
    
    public function __construct() {
        $this->customerModel = new Customer();
    }
    
    /**
     * Busca cliente por ID específico via query string
     */
    public function index() {
        // Verifica o token JWT
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        // Verifica se foi passado um ID específico
        $customerId = $_GET['id'] ?? null;
        
        if ($customerId) {
            // Busca cliente específico
            $customerId = Validator::sanitizeInt($customerId);
            
            if (!$customerId || $customerId <= 0) {
                Response::error('ID do cliente inválido', 400);
            }
            
            $customer = $this->customerModel->getCustomerById($storeId, $customerId);
            
            if (!$customer) {
                Response::error('Cliente não encontrado', 404);
            }
            
            // Busca endereços adicionais do cliente
            $addresses = $this->customerModel->getCustomerAddresses($customerId);
            
            // Busca estatísticas do cliente
            $stats = $this->customerModel->getCustomerStats($storeId, $customerId);
            
            Response::success([
                'customer' => $customer,
                'addresses' => $addresses,
                'stats' => $stats
            ]);
        } else {
            // Não permite listagem geral por segurança
            Response::error('Acesso negado. Use a busca por CPF/CNPJ ou forneça um ID específico.', 403);
        }
    }
    
    /**
     * Busca um cliente específico por ID
     */
    public function show($customerId) {
        // Verifica o token JWT
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        // Sanitiza o ID
        $customerId = Validator::sanitizeInt($customerId);
        
        if (!$customerId || $customerId <= 0) {
            Response::error('ID do cliente inválido', 400);
        }
        
        $customer = $this->customerModel->getCustomerById($storeId, $customerId);
        
        if (!$customer) {
            Response::error('Cliente não encontrado', 404);
        }
        
        // Busca endereços adicionais do cliente
        $addresses = $this->customerModel->getCustomerAddresses($customerId);
        
        // Busca estatísticas do cliente
        $stats = $this->customerModel->getCustomerStats($storeId, $customerId);
        
        Response::success([
            'customer' => $customer,
            'addresses' => $addresses,
            'stats' => $stats
        ]);
    }
    
    /**
     * Busca um cliente pelo CPF/CNPJ
     */
    public function getByCpfCnpj() {
        // Verifica o token JWT
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        // Obtém o CPF/CNPJ dos parâmetros da URL
        $cpfCnpj = $_GET['cpfcnpj'] ?? $_GET['cpf'] ?? $_GET['cnpj'] ?? null;
        
        if (!$cpfCnpj) {
            Response::error('CPF/CNPJ é obrigatório', 400);
        }
        
        // Remove formatação do CPF/CNPJ
        $cpfCnpj = preg_replace('/[^0-9]/', '', $cpfCnpj);
        
        // Valida o CPF/CNPJ
        if (strlen($cpfCnpj) == 11) {
            if (!Validator::isValidCpf($cpfCnpj)) {
                Response::error('CPF inválido', 400);
            }
        } elseif (strlen($cpfCnpj) == 14) {
            if (!Validator::isValidCnpj($cpfCnpj)) {
                Response::error('CNPJ inválido', 400);
            }
        } else {
            Response::error('CPF/CNPJ deve conter 11 ou 14 dígitos', 400);
        }
        
        try {
            $customer = $this->customerModel->getCustomerByCpfCnpj($storeId, $cpfCnpj);
            
            if (!$customer) {
                Response::error('Cliente não encontrado', 404);
            }
            
            // Busca endereços adicionais do cliente
            $addresses = $this->customerModel->getCustomerAddresses($customer['id_cliente']);
            
            // Busca estatísticas do cliente
            $stats = $this->customerModel->getCustomerStats($storeId, $customer['id_cliente']);
            
            Response::success([
                'customer' => $customer,
                'addresses' => $addresses,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            error_log('Erro ao buscar cliente por CPF/CNPJ: ' . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }
    
    /**
     * Cria um novo cliente
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
        
        // Valida os dados obrigatórios
        if (!isset($data['nome']) || empty(trim($data['nome']))) {
            Response::error('Nome é obrigatório', 400);
        }
        
        if (!isset($data['cpfcnpj']) || empty(trim($data['cpfcnpj']))) {
            Response::error('CPF/CNPJ é obrigatório', 400);
        }
        
        // Remove formatação do CPF/CNPJ
        $cpfCnpj = preg_replace('/[^0-9]/', '', $data['cpfcnpj']);
        
        // Valida o CPF/CNPJ
        if (strlen($cpfCnpj) == 11) {
            if (!Validator::isValidCpf($cpfCnpj)) {
                Response::error('CPF inválido', 400);
            }
        } elseif (strlen($cpfCnpj) == 14) {
            if (!Validator::isValidCnpj($cpfCnpj)) {
                Response::error('CNPJ inválido', 400);
            }
        } else {
            Response::error('CPF/CNPJ deve conter 11 ou 14 dígitos', 400);
        }
        
        // Verifica se já existe um cliente com esse CPF/CNPJ na loja
        if ($this->customerModel->customerExists($storeId, $cpfCnpj)) {
            Response::error('Já existe um cliente com este CPF/CNPJ', 409);
        }
        
        // Valida e-mail se fornecido
        if (isset($data['email']) && !empty($data['email'])) {
            if (!Validator::isValidEmail($data['email'])) {
                Response::error('E-mail inválido', 400);
            }
        }
        
        // Prepara os dados do cliente
        $customerData = [
            'nome' => Validator::sanitizeString($data['nome']),
            'cpfcnpj' => $cpfCnpj,
            'email' => isset($data['email']) ? Validator::sanitizeString($data['email']) : null,
            'telefone' => isset($data['telefone']) ? Validator::sanitizeString($data['telefone']) : null,
            'cep' => isset($data['cep']) ? Validator::sanitizeString($data['cep']) : null,
            'endereco' => isset($data['endereco']) ? Validator::sanitizeString($data['endereco']) : null,
            'numero' => isset($data['numero']) ? Validator::sanitizeString($data['numero']) : null,
            'complemento' => isset($data['complemento']) ? Validator::sanitizeString($data['complemento']) : null,
            'bairro' => isset($data['bairro']) ? Validator::sanitizeString($data['bairro']) : null,
            'cidade' => isset($data['cidade']) ? Validator::sanitizeString($data['cidade']) : null,
            'uf' => isset($data['uf']) ? Validator::sanitizeString($data['uf']) : null,
            'data_nascimento_cliente' => isset($data['data_nascimento_cliente']) ? $data['data_nascimento_cliente'] : null,
            'permite_consignado' => isset($data['permite_consignado']) ? $data['permite_consignado'] : null
        ];
        
        $customerId = $this->customerModel->createCustomer($storeId, $customerData);
        
        if ($customerId) {
            $newCustomer = $this->customerModel->getCustomerById($storeId, $customerId);
            Response::success($newCustomer, 201);
        } else {
            Response::error('Erro ao criar cliente', 500);
        }
    }
    
    /**
     * Atualiza um cliente existente
     */
    public function update($customerId) {
        // Verifica se a requisição é PUT
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::error('Método não permitido', 405);
        }
        
        // Verifica o token JWT
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        // Sanitiza o ID
        $customerId = Validator::sanitizeInt($customerId);
        
        if (!$customerId || $customerId <= 0) {
            Response::error('ID do cliente inválido', 400);
        }
        
        // Verifica se o cliente existe
        $existingCustomer = $this->customerModel->getCustomerById($storeId, $customerId);
        if (!$existingCustomer) {
            Response::error('Cliente não encontrado', 404);
        }
        
        // Obtém o corpo da requisição
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Prepara os dados para atualização
        $customerData = [];
        
        if (isset($data['nome']) && !empty(trim($data['nome']))) {
            $customerData['nome'] = Validator::sanitizeString($data['nome']);
        }
        
        if (isset($data['cpfcnpj']) && !empty(trim($data['cpfcnpj']))) {
            $cpfCnpj = preg_replace('/[^0-9]/', '', $data['cpfcnpj']);
            
            // Valida o CPF/CNPJ
            if (strlen($cpfCnpj) == 11) {
                if (!Validator::isValidCpf($cpfCnpj)) {
                    Response::error('CPF inválido', 400);
                }
            } elseif (strlen($cpfCnpj) == 14) {
                if (!Validator::isValidCnpj($cpfCnpj)) {
                    Response::error('CNPJ inválido', 400);
                }
            } else {
                Response::error('CPF/CNPJ deve conter 11 ou 14 dígitos', 400);
            }
            
            // Verifica se já existe outro cliente com esse CPF/CNPJ na loja
            if ($this->customerModel->customerExists($storeId, $cpfCnpj, $customerId)) {
                Response::error('Já existe outro cliente com este CPF/CNPJ', 409);
            }
            
            $customerData['cpfcnpj'] = $cpfCnpj;
        }
        
        if (isset($data['email'])) {
            if (!empty($data['email']) && !Validator::isValidEmail($data['email'])) {
                Response::error('E-mail inválido', 400);
            }
            $customerData['email'] = Validator::sanitizeString($data['email']);
        }
        
        // Outros campos opcionais
        $optionalFields = [
            'telefone', 'cep', 'endereco', 'numero', 'complemento', 
            'bairro', 'cidade', 'uf', 'data_nascimento_cliente', 'permite_consignado'
        ];
        
        foreach ($optionalFields as $field) {
            if (isset($data[$field])) {
                $customerData[$field] = in_array($field, ['data_nascimento_cliente', 'permite_consignado']) 
                    ? $data[$field] 
                    : Validator::sanitizeString($data[$field]);
            }
        }
        
        if (empty($customerData)) {
            Response::error('Nenhum dado válido fornecido para atualização', 400);
        }
        
        $updated = $this->customerModel->updateCustomer($storeId, $customerId, $customerData);
        
        if ($updated) {
            $updatedCustomer = $this->customerModel->getCustomerById($storeId, $customerId);
            Response::success($updatedCustomer);
        } else {
            Response::error('Erro ao atualizar cliente', 500);
        }
    }
    
    /**
     * Desativa um cliente (soft delete)
     */
    public function delete($customerId) {
        // Verifica se a requisição é DELETE
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            Response::error('Método não permitido', 405);
        }
        
        // Verifica o token JWT
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        // Sanitiza o ID
        $customerId = Validator::sanitizeInt($customerId);
        
        if (!$customerId || $customerId <= 0) {
            Response::error('ID do cliente inválido', 400);
        }
        
        // Verifica se o cliente existe
        $existingCustomer = $this->customerModel->getCustomerById($storeId, $customerId);
        if (!$existingCustomer) {
            Response::error('Cliente não encontrado', 404);
        }
        
        $deleted = $this->customerModel->deleteCustomer($storeId, $customerId);
        
        if ($deleted) {
            Response::success(['message' => 'Cliente desativado com sucesso']);
        } else {
            Response::error('Erro ao desativar cliente', 500);
        }
    }
}