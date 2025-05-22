<?php

/**
 * Configuração da API para o Frontend
 * Versão simplificada e robusta
 */

// Verificar se as funções já foram definidas para evitar redefinição
if (!function_exists('getStoreData')) {

    class ApiClient
    {
        private $baseUrl;
        private $token;
        private $storeUrl;

        public function __construct()
        {
            $this->baseUrl = $_ENV['API_BASE_URL'] ?? 'http://localhost/virtuavenda-api';
            $this->storeUrl = $this->getCurrentStoreUrl();

            // Tentar autenticar, mas não quebrar se falhar
            try {
                $this->authenticate();
            } catch (Exception $e) {
                error_log('Erro na autenticação da API: ' . $e->getMessage());
                // Continuar sem autenticação para permitir modo offline
            }
        }

        private function getCurrentStoreUrl()
        {
            return $_SERVER['HTTP_HOST'] ?? 'localhost';
        }

        private function authenticate()
        {
            // Verificar se já temos um token válido na sessão
            if (isset($_SESSION['api_token']) && isset($_SESSION['token_expires'])) {
                if (time() < $_SESSION['token_expires']) {
                    $this->token = $_SESSION['api_token'];
                    return;
                }
            }

            // Token específico da loja
            $storeToken = $this->getStoreToken();

            $loginData = [
                'url' => $this->storeUrl,
                'token' => $storeToken
            ];

            $response = $this->makeRequest('POST', '/auth/login', $loginData, false);

            if ($response && isset($response['data']['token'])) {
                $this->token = $response['data']['token'];
                $_SESSION['api_token'] = $this->token;
                $_SESSION['token_expires'] = time() + ($response['data']['expires_in'] ?? 3600);
                $_SESSION['store_data'] = $response['data']['store'];
            }
        }

        private function getStoreToken()
        {
            // Tokens de exemplo - em produção usar configuração segura
            $tokens = [
                'localhost' => 'token_da_loja_local',
                'loja.exemplo.com' => 'token_da_loja_exemplo'
            ];

            return $tokens[$this->storeUrl] ?? 'token_padrao';
        }

        private function makeRequest($method, $endpoint, $data = null, $useAuth = true)
        {
            // Implementação básica que não quebra se a API estiver offline
            try {
                $url = $this->baseUrl . $endpoint;

                $options = [
                    'http' => [
                        'method' => $method,
                        'header' => ['Content-Type: application/json'],
                        'timeout' => 5 // Timeout de 5 segundos
                    ]
                ];

                if ($useAuth && $this->token) {
                    $options['http']['header'][] = 'Authorization: Bearer ' . $this->token;
                }

                if ($data && in_array($method, ['POST', 'PUT'])) {
                    $options['http']['content'] = json_encode($data);
                }

                $context = stream_context_create($options);
                $response = @file_get_contents($url, false, $context);

                if ($response === false) {
                    return null;
                }

                return json_decode($response, true);
            } catch (Exception $e) {
                error_log("Erro na requisição para API: " . $e->getMessage());
                return null;
            }
        }

        public function getStoreInfo()
        {
            // Tentar dados da sessão primeiro
            if (isset($_SESSION['store_data'])) {
                return $_SESSION['store_data'];
            }

            // Tentar API
            $response = $this->makeRequest('GET', '/store/info');
            if ($response && isset($response['data'])) {
                return $response['data'];
            }

            // Retornar dados padrão se API não disponível
            return [
                'nome_loja' => 'VirtuaVenda',
                'descricao_loja' => 'Sua loja online de confiança',
                'url_loja' => $this->storeUrl,
                'logo_loja' => '',
                'whatsapp_loja' => '',
                'telefone_loja' => '',
                'email_loja' => '',
                'facebook_loja' => '',
                'instagram_loja' => '',
                'tiktok_loja' => ''
            ];
        }

        public function getProducts($page = 1, $limit = 12, $categoryId = null, $search = null)
        {
            $params = [
                'page' => $page,
                'limit' => $limit
            ];

            if ($categoryId) {
                $params['category_id'] = $categoryId;
            }

            if ($search) {
                $params['search'] = $search;
            }

            $queryString = http_build_query($params);
            $response = $this->makeRequest('GET', '/products?' . $queryString);

            if ($response && isset($response['data'])) {
                return $response['data'];
            }

            // Retornar dados de exemplo se API não disponível
            return [
                'products' => [],
                'pagination' => [
                    'current_page' => 1,
                    'total' => 0,
                    'total_pages' => 0
                ]
            ];
        }

        public function getProduct($productId)
        {
            $response = $this->makeRequest('GET', "/products/$productId");

            if ($response && isset($response['data'])) {
                return $response['data'];
            }

            return null;
        }

        public function getCategories()
        {
            $response = $this->makeRequest('GET', '/categories');

            if ($response && isset($response['data']['categories'])) {
                return $response['data']['categories'];
            }

            // Retornar categorias de exemplo se API não disponível
            return [
                ['id_produtos_categorias' => 1, 'nome_categoria' => 'Produtos'],
                ['id_produtos_categorias' => 2, 'nome_categoria' => 'Ofertas']
            ];
        }

        public function getPublicConfig()
        {
            $response = $this->makeRequest('GET', '/public-config', null, false);

            if ($response && isset($response['data'])) {
                return $response['data'];
            }

            return [
                'theme' => [
                    'primary_500' => '#3b82f6',
                    'secondary_500' => '#64748b'
                ]
            ];
        }
    }

    // Funções auxiliares globais
    function getApiClient()
    {
        static $apiClient = null;
        if ($apiClient === null) {
            $apiClient = new ApiClient();
        }
        return $apiClient;
    }

    function getStoreData()
    {
        return getApiClient()->getStoreInfo();
    }

    function getProducts($page = 1, $limit = 12, $categoryId = null, $search = null)
    {
        return getApiClient()->getProducts($page, $limit, $categoryId, $search);
    }

    function getProduct($productId)
    {
        return getApiClient()->getProduct($productId);
    }

    function getCategories()
    {
        return getApiClient()->getCategories();
    }

    function getPublicConfig()
    {
        return getApiClient()->getPublicConfig();
    }

    function formatPrice($price)
    {
        return 'R$ ' . number_format($price, 2, ',', '.');
    }

    function getImageUrl($imagePath, $width = null, $height = null)
    {
        if (empty($imagePath)) {
            $w = $width ?? 300;
            $h = $height ?? 250;
            return "./placeholder.php?w={$w}&h={$h}&text=Produto";
        }

        if (strpos($imagePath, 'http') === 0) {
            return $imagePath;
        }

        return './assets/images/products/' . $imagePath;
    }

    function truncateText($text, $length = 100)
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . '...';
    }
} // fim do if (!function_exists)
?>
