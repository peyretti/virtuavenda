<?php
namespace App\Models;

class Customer {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Busca um cliente pelo CPF/CNPJ
     * 
     * @param int $storeId ID da loja
     * @param string $cpfCnpj CPF ou CNPJ do cliente
     * @return array|null Dados do cliente ou null se não encontrado
     */
    public function getCustomerByCpfCnpj($storeId, $cpfCnpj) {
        $sql = "SELECT 
                    clientes.id_cliente, 
                    clientes.id_loja, 
                    clientes.nome, 
                    clientes.email, 
                    clientes.telefone, 
                    clientes.cpfcnpj, 
                    clientes.cep, 
                    clientes.endereco, 
                    clientes.numero, 
                    clientes.complemento, 
                    clientes.bairro, 
                    clientes.cidade, 
                    tab_municipios.nome_municipio as nome_municipio, 
                    clientes.uf,
                    clientes.data_nascimento_cliente,
                    clientes.permite_consignado,
                    clientes.ativo
                FROM clientes 
                LEFT JOIN tab_municipios ON clientes.cidade = tab_municipios.codigo_municipio_completo 
                WHERE clientes.id_loja = ? AND clientes.cpfcnpj = ? AND clientes.ativo = 'S'
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [$storeId, $cpfCnpj]);
    }
    
    /**
     * Busca um cliente pelo ID
     * 
     * @param int $storeId ID da loja
     * @param int $customerId ID do cliente
     * @return array|null Dados do cliente ou null se não encontrado
     */
    public function getCustomerById($storeId, $customerId) {
        $sql = "SELECT 
                    clientes.id_cliente, 
                    clientes.id_loja, 
                    clientes.nome, 
                    clientes.email, 
                    clientes.telefone, 
                    clientes.cpfcnpj, 
                    clientes.cep, 
                    clientes.endereco, 
                    clientes.numero, 
                    clientes.complemento, 
                    clientes.bairro, 
                    clientes.cidade, 
                    tab_municipios.nome_municipio as nome_municipio, 
                    clientes.uf,
                    clientes.data_nascimento_cliente,
                    clientes.permite_consignado,
                    clientes.ativo
                FROM clientes 
                LEFT JOIN tab_municipios ON clientes.cidade = tab_municipios.codigo_municipio_completo 
                WHERE clientes.id_loja = ? AND clientes.id_cliente = ? AND clientes.ativo = 'S'
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [$storeId, $customerId]);
    }
    
    /**
     * Verifica se já existe um cliente com o CPF/CNPJ na loja
     * 
     * @param int $storeId ID da loja
     * @param string $cpfCnpj CPF ou CNPJ
     * @param int|null $excludeId ID a ser excluído da verificação (para updates)
     * @return bool Verdadeiro se o cliente já existe
     */
    public function customerExists($storeId, $cpfCnpj, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count 
                FROM clientes 
                WHERE id_loja = ? AND cpfcnpj = ? AND ativo = 'S'";
        
        $params = [$storeId, $cpfCnpj];
        
        if ($excludeId) {
            $sql .= " AND id_cliente != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Cria um novo cliente
     * 
     * @param int $storeId ID da loja
     * @param array $customerData Dados do cliente
     * @return int ID do cliente criado
     */
    public function createCustomer($storeId, $customerData) {
        $customerData['id_loja'] = $storeId;
        $customerData['ativo'] = 'S';
        
        try {
            return $this->db->insert('clientes', $customerData);
        } catch (\Exception $e) {
            error_log('Erro ao criar cliente: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Atualiza um cliente existente
     * 
     * @param int $storeId ID da loja
     * @param int $customerId ID do cliente
     * @param array $customerData Dados a serem atualizados
     * @return bool Sucesso da operação
     */
    public function updateCustomer($storeId, $customerId, $customerData) {
        try {
            $affected = $this->db->update(
                'clientes',
                $customerData,
                'id_loja = ? AND id_cliente = ?',
                [$storeId, $customerId]
            );
            
            return $affected > 0;
        } catch (\Exception $e) {
            error_log('Erro ao atualizar cliente: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Desativa um cliente (soft delete)
     * 
     * @param int $storeId ID da loja
     * @param int $customerId ID do cliente
     * @return bool Sucesso da operação
     */
    public function deleteCustomer($storeId, $customerId) {
        return $this->updateCustomer($storeId, $customerId, ['ativo' => 'N']);
    }
    
    /**
     * Busca endereços adicionais de um cliente
     * 
     * @param int $customerId ID do cliente
     * @return array Lista de endereços do cliente
     */
    public function getCustomerAddresses($customerId) {
        $sql = "SELECT 
                    endereco_cliente.id_endereco_cliente,
                    endereco_cliente.descricao,
                    endereco_cliente.cep,
                    endereco_cliente.endereco,
                    endereco_cliente.numero,
                    endereco_cliente.complemento,
                    endereco_cliente.bairro,
                    endereco_cliente.cidade,
                    tab_municipios.nome_municipio as nome_municipio,
                    endereco_cliente.uf,
                    endereco_cliente.ativo
                FROM endereco_cliente 
                LEFT JOIN tab_municipios ON endereco_cliente.cidade = tab_municipios.codigo_municipio_completo 
                WHERE endereco_cliente.id_cliente = ? AND endereco_cliente.ativo = 'S'
                ORDER BY endereco_cliente.descricao";
        
        return $this->db->fetchAll($sql, [$customerId]);
    }
    
    /**
     * Obtém estatísticas do cliente
     * 
     * @param int $storeId ID da loja
     * @param int $customerId ID do cliente
     * @return array Estatísticas do cliente
     */
    public function getCustomerStats($storeId, $customerId) {
        $sql = "SELECT 
                    COUNT(*) as total_pedidos,
                    SUM(CASE WHEN pedido_pago = 'S' THEN 1 ELSE 0 END) as pedidos_pagos,
                    SUM(CASE WHEN pedido_cancelado = 'S' THEN 1 ELSE 0 END) as pedidos_cancelados,
                    (SELECT SUM(pi.qtd_item * pi.valor_item) 
                     FROM pedidos_itens pi 
                     INNER JOIN pedidos p ON pi.id_pedidos = p.id_pedidos 
                     WHERE p.id_cliente = ? AND p.id_loja = ? AND p.pedido_pago = 'S'
                    ) as valor_total_compras
                FROM pedidos 
                WHERE id_cliente = ? AND id_loja = ?";
        
        return $this->db->fetchOne($sql, [$customerId, $storeId, $customerId, $storeId]);
    }
}