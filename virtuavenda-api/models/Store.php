<?php
namespace App\Models;

class Store {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Busca uma loja pelo URL
     * 
     * @param string $url URL da loja
     * @return array|null Dados da loja ou null se não existir
     */
    public function getStoreByUrl($url) {
        $sql = "SELECT * FROM lojas WHERE url_loja = ? AND ativo = 'S' AND publicar_site = 'S' LIMIT 1";
        return $this->db->fetchOne($sql, [$url]);
    }
    
    /**
     * Busca uma loja pelo ID
     * 
     * @param int $id ID da loja
     * @return array|null Dados da loja ou null se não existir
     */
    public function getStoreById($id) {
        $sql = "SELECT * FROM lojas WHERE id_loja = ? AND ativo = 'S' AND publicar_site = 'S' LIMIT 1";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Valida o token da loja
     * 
     * @param int $storeId ID da loja
     * @param string $token Token para validar
     * @return bool Verdadeiro se o token for válido
     */
    public function validateToken($storeId, $token) {
        $sql = "SELECT tkn_loja FROM lojas WHERE id_loja = ? AND ativo = 'S' LIMIT 1";
        $store = $this->db->fetchOne($sql, [$storeId]);
        
        if (!$store) {
            return false;
        }
        
        return password_verify($token, $store['tkn_loja']);
    }
    
    /**
     * Obtém informações completas da loja para resposta de API (incluindo endereço)
     * 
     * @param int $storeId ID da loja
     * @return array|null Dados completos da loja
     */
    public function getStoreInfo($storeId) {
        $sql = "SELECT id_loja, nome_loja, descricao_loja, endereco_loja, numero_end_loja, complemento_loja,
                        bairro_loja, (SELECT nome_municipio FROM tab_municipios WHERE codigo_municipio_completo = lojas.cidade_loja) AS cidade_loja,
                        uf_loja, url_loja, logo_loja, 
                        whatsapp_loja, telefone_loja, email_loja, facebook_loja, 
                        instagram_loja, tiktok_loja, publicar_site
                FROM lojas 
                WHERE id_loja = ? AND ativo = 'S' LIMIT 1";
        
        return $this->db->fetchOne($sql, [$storeId]);
    }
    
    /**
     * Obtém informações públicas da loja para resposta de API
     * 
     * @param int $storeId ID da loja
     * @return array|null Dados públicos da loja
     */
    public function getStorePublicInfo($storeId) {
        $sql = "SELECT id_loja, nome_loja, descricao_loja, url_loja, logo_loja, 
                whatsapp_loja, telefone_loja, email_loja, facebook_loja, 
                instagram_loja, tiktok_loja
                FROM lojas 
                WHERE id_loja = ? AND ativo = 'S' AND publicar_site = 'S' LIMIT 1";
        
        return $this->db->fetchOne($sql, [$storeId]);
    }
    
    /**
     * Registra o último login da loja
     * 
     * @param int $storeId ID da loja
     * @return bool Sucesso da operação
     */
    public function recordLogin($storeId) {
        $now = date('Y-m-d H:i:s');
        $sql = "UPDATE lojas SET ultimo_login = ? WHERE id_loja = ?";
        
        try {
            $this->db->query($sql, [$now, $storeId]);
            return true;
        } catch (\Exception $e) {
            error_log('Erro ao registrar login: ' . $e->getMessage());
            return false;
        }
    }
}