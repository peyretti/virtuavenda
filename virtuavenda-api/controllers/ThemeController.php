<?php
// ========================================
// ThemeController.php
// ========================================

namespace App\Controllers;

use App\Models\ThemeConfiguration;
use App\Utils\Response;
use App\Middleware\AuthMiddleware;
use App\Utils\Validator;

class ThemeController {
    private $themeModel;
    
    public function __construct() {
        $this->themeModel = new ThemeConfiguration();
    }
    
    /**
     * Obtém configurações de tema
     */
    public function getTheme() {
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $theme = $this->themeModel->getThemeConfig($storeId);
        
        if (!$theme) {
            Response::error('Configurações de tema não encontradas', 404);
        }
        
        Response::success($theme);
    }
    
    /**
     * Atualiza configurações de tema
     */
    public function updateTheme() {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::error('Método não permitido', 405);
        }
        
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('JSON inválido', 400);
        }
        
        // Campos permitidos
        $allowedFields = [
            'cor_primaria', 'cor_secundaria', 'logo_principal', 
            'logo_alternativa', 'favicon', 'tema_escuro_disponivel'
        ];
        
        $updateData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                // Validação específica para cores (formato hex)
                if (strpos($key, 'cor_') === 0 && !empty($value)) {
                    if (!preg_match('/^#[a-f0-9]{6}$/i', $value)) {
                        Response::error("Cor inválida para o campo {$key}. Use formato #RRGGBB", 400);
                    }
                }
                
                // Validação para campos char(1)
                if ($key === 'tema_escuro_disponivel') {
                    if (!in_array($value, ['S', 'N'])) {
                        Response::error("Valor inválido para {$key}. Use 'S' ou 'N'", 400);
                    }
                }
                
                $updateData[$key] = $value;
            }
        }
        
        if (empty($updateData)) {
            Response::error('Nenhum campo válido para atualização', 400);
        }
        
        $updated = $this->themeModel->updateThemeConfig($storeId, $updateData);
        
        if ($updated) {
            $updatedTheme = $this->themeModel->getThemeConfig($storeId);
            Response::success($updatedTheme);
        } else {
            Response::error('Erro ao atualizar configurações de tema', 500);
        }
    }
}

// ========================================
// BannerController.php
// ========================================

namespace App\Controllers;

use App\Models\Banner;
use App\Utils\Response;
use App\Middleware\AuthMiddleware;
use App\Utils\Validator;

class BannerController {
    private $bannerModel;
    
    public function __construct() {
        $this->bannerModel = new Banner();
    }
    
    /**
     * Lista todos os banners
     */
    public function index() {
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $position = $_GET['position'] ?? null;
        $banners = $this->bannerModel->getAllBanners($storeId, $position);
        
        Response::success([
            'banners' => $banners,
            'total' => count($banners)
        ]);
    }
    
    /**
     * Obtém banner específico
     */
    public function show($bannerId) {
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $bannerId = Validator::sanitizeInt($bannerId);
        
        if (!$bannerId || $bannerId <= 0) {
            Response::error('ID do banner inválido', 400);
        }
        
        $banner = $this->bannerModel->getBannerById($storeId, $bannerId);
        
        if (!$banner) {
            Response::error('Banner não encontrado', 404);
        }
        
        Response::success($banner);
    }
    
    /**
     * Cria novo banner
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Método não permitido', 405);
        }
        
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('JSON inválido', 400);
        }
        
        // Validações obrigatórias
        if (empty($data['posicao'])) {
            Response::error('Posição do banner é obrigatória', 400);
        }
        
        // Validações de enum
        $validPositions = ['hero', 'secundario', 'lateral', 'footer'];
        if (!in_array($data['posicao'], $validPositions)) {
            Response::error('Posição inválida', 400);
        }
        
        // Campos permitidos
        $allowedFields = [
            'titulo', 'subtitulo', 'descricao', 'posicao', 'ordem', 
            'data_inicio', 'data_fim'
        ];
        
        $bannerData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $bannerData[$key] = $value;
            }
        }
        
        // Valores padrão
        $bannerData['ordem'] = $bannerData['ordem'] ?? 1;
        
        $bannerId = $this->bannerModel->createBanner($storeId, $bannerData);
        
        if ($bannerId) {
            $newBanner = $this->bannerModel->getBannerById($storeId, $bannerId);
            Response::success($newBanner, 201);
        } else {
            Response::error('Erro ao criar banner', 500);
        }
    }
    
    /**
     * Atualiza banner
     */
    public function update($bannerId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::error('Método não permitido', 405);
        }
        
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $bannerId = Validator::sanitizeInt($bannerId);
        
        if (!$bannerId || $bannerId <= 0) {
            Response::error('ID do banner inválido', 400);
        }
        
        // Verifica se banner existe
        $existingBanner = $this->bannerModel->getBannerById($storeId, $bannerId);
        if (!$existingBanner) {
            Response::error('Banner não encontrado', 404);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('JSON inválido', 400);
        }
        
        // Campos permitidos para atualização
        $allowedFields = [
            'titulo', 'subtitulo', 'descricao', 'posicao', 'ordem',
            'data_inicio', 'data_fim', 'ativo'
        ];
        
        $updateData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $updateData[$key] = $value;
            }
        }
        
        if (empty($updateData)) {
            Response::error('Nenhum campo válido para atualização', 400);
        }
        
        $updated = $this->bannerModel->updateBanner($storeId, $bannerId, $updateData);
        
        if ($updated) {
            $updatedBanner = $this->bannerModel->getBannerById($storeId, $bannerId);
            Response::success($updatedBanner);
        } else {
            Response::error('Erro ao atualizar banner', 500);
        }
    }
    
    /**
     * Remove banner
     */
    public function delete($bannerId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            Response::error('Método não permitido', 405);
        }
        
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $bannerId = Validator::sanitizeInt($bannerId);
        
        if (!$bannerId || $bannerId <= 0) {
            Response::error('ID do banner inválido', 400);
        }
        
        // Verifica se banner existe
        $existingBanner = $this->bannerModel->getBannerById($storeId, $bannerId);
        if (!$existingBanner) {
            Response::error('Banner não encontrado', 404);
        }
        
        $deleted = $this->bannerModel->deleteBanner($storeId, $bannerId);
        
        if ($deleted) {
            Response::success(['message' => 'Banner removido com sucesso']);
        } else {
            Response::error('Erro ao remover banner', 500);
        }
    }
}

// ========================================
// StaticPageController.php
// ========================================

namespace App\Controllers;

use App\Models\StaticPage;
use App\Utils\Response;
use App\Middleware\AuthMiddleware;
use App\Utils\Validator;

class StaticPageController {
    private $pageModel;
    
    public function __construct() {
        $this->pageModel = new StaticPage();
    }
    
    /**
     * Lista todas as páginas
     */
    public function index() {
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $includeInactive = isset($_GET['include_inactive']) && $_GET['include_inactive'] === 'true';
        $pages = $this->pageModel->getAllPages($storeId, !$includeInactive);
        
        Response::success([
            'pages' => $pages,
            'total' => count($pages)
        ]);
    }
    
    /**
     * Obtém página por tipo
     */
    public function getByType($type) {
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $type = Validator::sanitizeString($type);
        
        if (empty($type)) {
            Response::error('Tipo inválido', 400);
        }
        
        $page = $this->pageModel->getPageByType($storeId, $type);
        
        if (!$page) {
            Response::error('Página não encontrada', 404);
        }
        
        Response::success($page);
    }
    
    /**
     * Obtém página específica por ID
     */
    public function show($pageId) {
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $pageId = Validator::sanitizeInt($pageId);
        
        if (!$pageId || $pageId <= 0) {
            Response::error('ID da página inválido', 400);
        }
        
        $page = $this->pageModel->getPageById($storeId, $pageId);
        
        if (!$page) {
            Response::error('Página não encontrada', 404);
        }
        
        Response::success($page);
    }
    
    /**
     * Obtém páginas para menu
     */
    public function getMenuPages() {
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $pages = $this->pageModel->getMenuPages($storeId);
        
        Response::success([
            'menu_pages' => $pages,
            'total' => count($pages)
        ]);
    }
    
    /**
     * Obtém páginas para footer
     */
    public function getFooterPages() {
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $pages = $this->pageModel->getFooterPages($storeId);
        
        Response::success([
            'footer_pages' => $pages,
            'total' => count($pages)
        ]);
    }
    
    /**
     * Cria nova página
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Método não permitido', 405);
        }
        
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('JSON inválido', 400);
        }
        
        // Validações obrigatórias
        if (empty($data['titulo'])) {
            Response::error('Título é obrigatório', 400);
        }
        
        if (empty($data['conteudo'])) {
            Response::error('Conteúdo é obrigatório', 400);
        }
        
        // Campos permitidos
        $allowedFields = [
            'titulo', 'conteudo', 'resumo', 'tipo_pagina', 
            'exibir_menu', 'exibir_footer', 'ordem_menu'
        ];
        
        $pageData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $pageData[$key] = $value;
            }
        }
        
        // Valores padrão
        $pageData['exibir_menu'] = $pageData['exibir_menu'] ?? 'S';
        $pageData['exibir_footer'] = $pageData['exibir_footer'] ?? 'S';
        $pageData['tipo_pagina'] = $pageData['tipo_pagina'] ?? 'personalizada';
        $pageData['ordem_menu'] = $pageData['ordem_menu'] ?? 999;
        
        $pageId = $this->pageModel->createPage($storeId, $pageData);
        
        if ($pageId) {
            $newPage = $this->pageModel->getPageById($storeId, $pageId);
            Response::success($newPage, 201);
        } else {
            Response::error('Erro ao criar página', 500);
        }
    }
    
    /**
     * Atualiza página
     */
    public function update($pageId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::error('Método não permitido', 405);
        }
        
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $pageId = Validator::sanitizeInt($pageId);
        
        if (!$pageId || $pageId <= 0) {
            Response::error('ID da página inválido', 400);
        }
        
        // Verifica se página existe
        $existingPage = $this->pageModel->getPageById($storeId, $pageId);
        if (!$existingPage) {
            Response::error('Página não encontrada', 404);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('JSON inválido', 400);
        }
        
        // Campos permitidos para atualização
        $allowedFields = [
            'titulo', 'conteudo', 'resumo', 'tipo_pagina',
            'exibir_menu', 'exibir_footer', 'ordem_menu', 'ativo'
        ];
        
        $updateData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $updateData[$key] = $value;
            }
        }
        
        if (empty($updateData)) {
            Response::error('Nenhum campo válido para atualização', 400);
        }
        
        $updated = $this->pageModel->updatePage($storeId, $pageId, $updateData);
        
        if ($updated) {
            $updatedPage = $this->pageModel->getPageById($storeId, $pageId);
            Response::success($updatedPage);
        } else {
            Response::error('Erro ao atualizar página', 500);
        }
    }
    
    /**
     * Remove página
     */
    public function delete($pageId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            Response::error('Método não permitido', 405);
        }
        
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $pageId = Validator::sanitizeInt($pageId);
        
        if (!$pageId || $pageId <= 0) {
            Response::error('ID da página inválido', 400);
        }
        
        // Verifica se página existe
        $existingPage = $this->pageModel->getPageById($storeId, $pageId);
        if (!$existingPage) {
            Response::error('Página não encontrada', 404);
        }
        
        $deleted = $this->pageModel->deletePage($storeId, $pageId);
        
        if ($deleted) {
            Response::success(['message' => 'Página removida com sucesso']);
        } else {
            Response::error('Erro ao remover página', 500);
        }
    }
}

// ========================================
// SiteConfigurationController.php
// ========================================

namespace App\Controllers;

use App\Models\SiteConfiguration;
use App\Utils\Response;
use App\Middleware\AuthMiddleware;
use App\Utils\Validator;

class SiteConfigurationController {
    private $siteConfigModel;
    
    public function __construct() {
        $this->siteConfigModel = new SiteConfiguration();
    }
    
    /**
     * Obtém configurações do site
     */
    public function getSiteConfig() {
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $config = $this->siteConfigModel->getSiteConfig($storeId);
        
        if (!$config) {
            Response::error('Configurações do site não encontradas', 404);
        }
        
        Response::success($config);
    }
    
    /**
     * Atualiza configurações do site
     */
    public function updateSiteConfig() {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::error('Método não permitido', 405);
        }
        
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('JSON inválido', 400);
        }
        
        // Campos permitidos
        $allowedFields = [
            'titulo_site', 'slogan', 'descricao_site', 'meta_title_padrao',
            'meta_description_padrao', 'meta_keywords_padrao', 'carrinho_habilitado',
            'cadastro_clientes_habilitado', 'newsletter_habilitada', 'avaliacao_produtos_habilitada',
            'chat_online_habilitado', 'busca_avancada_habilitada', 'produtos_por_pagina',
            'exibir_preco_sem_estoque', 'exibir_produtos_sem_estoque', 'formato_preco',
            'horario_funcionamento', 'endereco_completo', 'telefones_adicionais',
            'emails_adicionais', 'google_analytics_id', 'facebook_pixel_id',
            'google_tag_manager_id'
        ];
        
        $updateData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                // Validação para campos char(1)
                if (in_array($key, [
                    'carrinho_habilitado', 'cadastro_clientes_habilitado', 'newsletter_habilitada',
                    'avaliacao_produtos_habilitada', 'chat_online_habilitado', 'busca_avancada_habilitada',
                    'exibir_preco_sem_estoque', 'exibir_produtos_sem_estoque'
                ])) {
                    if (!in_array($value, ['S', 'N'])) {
                        Response::error("Valor inválido para {$key}. Use 'S' ou 'N'", 400);
                    }
                }
                
                // Validação para produtos_por_pagina
                if ($key === 'produtos_por_pagina' && (!is_numeric($value) || $value < 1 || $value > 100)) {
                    Response::error('Produtos por página deve ser um número entre 1 e 100', 400);
                }
                
                $updateData[$key] = $value;
            }
        }
        
        if (empty($updateData)) {
            Response::error('Nenhum campo válido para atualização', 400);
        }
        
        $updated = $this->siteConfigModel->updateSiteConfig($storeId, $updateData);
        
        if ($updated) {
            $updatedConfig = $this->siteConfigModel->getSiteConfig($storeId);
            Response::success($updatedConfig);
        } else {
            Response::error('Erro ao atualizar configurações do site', 500);
        }
    }
}

// ========================================
// TestimonialController.php
// ========================================

namespace App\Controllers;

use App\Models\Testimonial;
use App\Utils\Response;
use App\Middleware\AuthMiddleware;
use App\Utils\Validator;

class TestimonialController {
    private $testimonialModel;
    
    public function __construct() {
        $this->testimonialModel = new Testimonial();
    }
    
    /**
     * Lista todos os depoimentos
     */
    public function index() {
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $homepage = isset($_GET['homepage']) && $_GET['homepage'] === 'true';
        $testimonials = $this->testimonialModel->getAllTestimonials($storeId, $homepage);
        
        Response::success([
            'testimonials' => $testimonials,
            'total' => count($testimonials)
        ]);
    }
    
    /**
     * Obtém depoimento específico
     */
    public function show($testimonialId) {
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $testimonialId = Validator::sanitizeInt($testimonialId);
        
        if (!$testimonialId || $testimonialId <= 0) {
            Response::error('ID do depoimento inválido', 400);
        }
        
        $testimonial = $this->testimonialModel->getTestimonialById($storeId, $testimonialId);
        
        if (!$testimonial) {
            Response::error('Depoimento não encontrado', 404);
        }
        
        Response::success($testimonial);
    }
    
    /**
     * Cria novo depoimento
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Método não permitido', 405);
        }
        
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('JSON inválido', 400);
        }
        
        // Validações obrigatórias
        if (empty($data['nome_cliente'])) {
            Response::error('Nome do cliente é obrigatório', 400);
        }
        
        if (empty($data['depoimento'])) {
            Response::error('Depoimento é obrigatório', 400);
        }
        
        // Campos permitidos
        $allowedFields = [
            'nome_cliente', 'cargo_empresa', 'foto_cliente', 'depoimento',
            'avaliacao', 'exibir_homepage', 'exibir_pagina_depoimentos', 'ordem'
        ];
        
        $testimonialData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $testimonialData[$key] = $value;
            }
        }
        
        // Valores padrão
        $testimonialData['avaliacao'] = $testimonialData['avaliacao'] ?? 5;
        $testimonialData['exibir_homepage'] = $testimonialData['exibir_homepage'] ?? 'S';
        $testimonialData['exibir_pagina_depoimentos'] = $testimonialData['exibir_pagina_depoimentos'] ?? 'S';
        $testimonialData['ordem'] = $testimonialData['ordem'] ?? 1;
        
        // Validação da avaliação
        if ($testimonialData['avaliacao'] < 1 || $testimonialData['avaliacao'] > 5) {
            Response::error('Avaliação deve ser entre 1 e 5', 400);
        }
        
        $testimonialId = $this->testimonialModel->createTestimonial($storeId, $testimonialData);
        
        if ($testimonialId) {
            $newTestimonial = $this->testimonialModel->getTestimonialById($storeId, $testimonialId);
            Response::success($newTestimonial, 201);
        } else {
            Response::error('Erro ao criar depoimento', 500);
        }
    }
    
    /**
     * Atualiza depoimento
     */
    public function update($testimonialId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::error('Método não permitido', 405);
        }
        
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $testimonialId = Validator::sanitizeInt($testimonialId);
        
        if (!$testimonialId || $testimonialId <= 0) {
            Response::error('ID do depoimento inválido', 400);
        }
        
        // Verifica se depoimento existe
        $existingTestimonial = $this->testimonialModel->getTestimonialById($storeId, $testimonialId);
        if (!$existingTestimonial) {
            Response::error('Depoimento não encontrado', 404);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('JSON inválido', 400);
        }
        
        // Campos permitidos para atualização
        $allowedFields = [
            'nome_cliente', 'cargo_empresa', 'foto_cliente', 'depoimento',
            'avaliacao', 'exibir_homepage', 'exibir_pagina_depoimentos', 'ordem', 'ativo'
        ];
        
        $updateData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                // Validação da avaliação
                if ($key === 'avaliacao' && ($value < 1 || $value > 5)) {
                    Response::error('Avaliação deve ser entre 1 e 5', 400);
                }
                
                $updateData[$key] = $value;
            }
        }
        
        if (empty($updateData)) {
            Response::error('Nenhum campo válido para atualização', 400);
        }
        
        $updated = $this->testimonialModel->updateTestimonial($storeId, $testimonialId, $updateData);
        
        if ($updated) {
            $updatedTestimonial = $this->testimonialModel->getTestimonialById($storeId, $testimonialId);
            Response::success($updatedTestimonial);
        } else {
            Response::error('Erro ao atualizar depoimento', 500);
        }
    }
    
    /**
     * Remove depoimento
     */
    public function delete($testimonialId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            Response::error('Método não permitido', 405);
        }
        
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $testimonialId = Validator::sanitizeInt($testimonialId);
        
        if (!$testimonialId || $testimonialId <= 0) {
            Response::error('ID do depoimento inválido', 400);
        }
        
        // Verifica se depoimento existe
        $existingTestimonial = $this->testimonialModel->getTestimonialById($storeId, $testimonialId);
        if (!$existingTestimonial) {
            Response::error('Depoimento não encontrado', 404);
        }
        
        $deleted = $this->testimonialModel->deleteTestimonial($storeId, $testimonialId);
        
        if ($deleted) {
            Response::success(['message' => 'Depoimento removido com sucesso']);
        } else {
            Response::error('Erro ao remover depoimento', 500);
        }
    }
}

// ========================================
// FAQController.php
// ========================================

namespace App\Controllers;

use App\Models\FAQ;
use App\Utils\Response;
use App\Middleware\AuthMiddleware;
use App\Utils\Validator;

class FAQController {
    private $faqModel;
    
    public function __construct() {
        $this->faqModel = new FAQ();
    }
    
    /**
     * Lista todas as perguntas
     */
    public function index() {
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $category = $_GET['category'] ?? null;
        $faqs = $this->faqModel->getAllFAQs($storeId, $category);
        
        Response::success([
            'faqs' => $faqs,
            'total' => count($faqs)
        ]);
    }
    
    /**
     * Obtém categorias de FAQ
     */
    public function getCategories() {
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $categories = $this->faqModel->getFAQCategories($storeId);
        
        Response::success([
            'categories' => $categories,
            'total' => count($categories)
        ]);
    }
    
    /**
     * Obtém pergunta específica
     */
    public function show($faqId) {
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $faqId = Validator::sanitizeInt($faqId);
        
        if (!$faqId || $faqId <= 0) {
            Response::error('ID da pergunta inválido', 400);
        }
        
        $faq = $this->faqModel->getFAQById($storeId, $faqId);
        
        if (!$faq) {
            Response::error('Pergunta não encontrada', 404);
        }
        
        Response::success($faq);
    }
    
    /**
     * Cria nova pergunta
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Método não permitido', 405);
        }
        
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('JSON inválido', 400);
        }
        
        // Validações obrigatórias
        if (empty($data['pergunta'])) {
            Response::error('Pergunta é obrigatória', 400);
        }
        
        if (empty($data['resposta'])) {
            Response::error('Resposta é obrigatória', 400);
        }
        
        // Campos permitidos
        $allowedFields = ['pergunta', 'resposta', 'categoria_faq', 'ordem'];
        
        $faqData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $faqData[$key] = $value;
            }
        }
        
        // Valores padrão
        $faqData['categoria_faq'] = $faqData['categoria_faq'] ?? 'geral';
        $faqData['ordem'] = $faqData['ordem'] ?? 1;
        
        $faqId = $this->faqModel->createFAQ($storeId, $faqData);
        
        if ($faqId) {
            $newFAQ = $this->faqModel->getFAQById($storeId, $faqId);
            Response::success($newFAQ, 201);
        } else {
            Response::error('Erro ao criar pergunta', 500);
        }
    }
    
    /**
     * Atualiza pergunta
     */
    public function update($faqId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::error('Método não permitido', 405);
        }
        
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $faqId = Validator::sanitizeInt($faqId);
        
        if (!$faqId || $faqId <= 0) {
            Response::error('ID da pergunta inválido', 400);
        }
        
        // Verifica se pergunta existe
        $existingFAQ = $this->faqModel->getFAQById($storeId, $faqId);
        if (!$existingFAQ) {
            Response::error('Pergunta não encontrada', 404);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('JSON inválido', 400);
        }
        
        // Campos permitidos para atualização
        $allowedFields = ['pergunta', 'resposta', 'categoria_faq', 'ordem', 'ativo'];
        
        $updateData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $updateData[$key] = $value;
            }
        }
        
        if (empty($updateData)) {
            Response::error('Nenhum campo válido para atualização', 400);
        }
        
        $updated = $this->faqModel->updateFAQ($storeId, $faqId, $updateData);
        
        if ($updated) {
            $updatedFAQ = $this->faqModel->getFAQById($storeId, $faqId);
            Response::success($updatedFAQ);
        } else {
            Response::error('Erro ao atualizar pergunta', 500);
        }
    }
    
    /**
     * Remove pergunta
     */
    public function delete($faqId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            Response::error('Método não permitido', 405);
        }
        
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $faqId = Validator::sanitizeInt($faqId);
        
        if (!$faqId || $faqId <= 0) {
            Response::error('ID da pergunta inválido', 400);
        }
        
        // Verifica se pergunta existe
        $existingFAQ = $this->faqModel->getFAQById($storeId, $faqId);
        if (!$existingFAQ) {
            Response::error('Pergunta não encontrada', 404);
        }
        
        $deleted = $this->faqModel->deleteFAQ($storeId, $faqId);
        
        if ($deleted) {
            Response::success(['message' => 'Pergunta removida com sucesso']);
        } else {
            Response::error('Erro ao remover pergunta', 500);
        }
    }
}

// ========================================
// CustomFormController.php
// ========================================

namespace App\Controllers;

use App\Models\CustomForm;
use App\Utils\Response;
use App\Middleware\AuthMiddleware;
use App\Utils\Validator;

class CustomFormController {
    private $formModel;
    
    public function __construct() {
        $this->formModel = new CustomForm();
    }
    
    /**
     * Lista todos os formulários
     */
    public function index() {
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $forms = $this->formModel->getAllForms($storeId);
        
        Response::success([
            'forms' => $forms,
            'total' => count($forms)
        ]);
    }
    
    /**
     * Obtém formulário específico por ID
     */
    public function show($formId) {
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $formId = Validator::sanitizeInt($formId);
        
        if (!$formId || $formId <= 0) {
            Response::error('ID do formulário inválido', 400);
        }
        
        $form = $this->formModel->getFormById($storeId, $formId);
        
        if (!$form) {
            Response::error('Formulário não encontrado', 404);
        }
        
        Response::success($form);
    }
    
    /**
     * Obtém formulário por tipo
     */
    public function getByType($type) {
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $type = Validator::sanitizeString($type);
        
        if (empty($type)) {
            Response::error('Tipo inválido', 400);
        }
        
        $form = $this->formModel->getFormByType($storeId, $type);
        
        if (!$form) {
            Response::error('Formulário não encontrado', 404);
        }
        
        Response::success($form);
    }
    
    /**
     * Cria novo formulário
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Método não permitido', 405);
        }
        
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('JSON inválido', 400);
        }
        
        // Validações obrigatórias
        if (empty($data['nome_formulario'])) {
            Response::error('Nome do formulário é obrigatório', 400);
        }
        
        if (empty($data['titulo'])) {
            Response::error('Título é obrigatório', 400);
        }
        
        if (empty($data['campos_json'])) {
            Response::error('Campos do formulário são obrigatórios', 400);
        }
        
        // Validação do JSON dos campos
        if (is_string($data['campos_json'])) {
            $campos = json_decode($data['campos_json'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Response::error('JSON dos campos inválido', 400);
            }
        }
        
        // Validação do tipo
        $validTypes = ['contato', 'orcamento', 'newsletter', 'personalizado'];
        if (isset($data['tipo']) && !in_array($data['tipo'], $validTypes)) {
            Response::error('Tipo de formulário inválido', 400);
        }
        
        // Campos permitidos
        $allowedFields = [
            'nome_formulario', 'titulo', 'descricao', 'tipo', 'campos_json',
            'email_destino', 'mensagem_sucesso', 'redirect_url',
            'exibir_como_popup', 'ativar_captcha'
        ];
        
        $formData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $formData[$key] = $value;
            }
        }
        
        // Valores padrão
        $formData['tipo'] = $formData['tipo'] ?? 'contato';
        $formData['mensagem_sucesso'] = $formData['mensagem_sucesso'] ?? 'Formulário enviado com sucesso!';
        $formData['exibir_como_popup'] = $formData['exibir_como_popup'] ?? 'N';
        $formData['ativar_captcha'] = $formData['ativar_captcha'] ?? 'S';
        
        $formId = $this->formModel->createForm($storeId, $formData);
        
        if ($formId) {
            $newForm = $this->formModel->getFormById($storeId, $formId);
            Response::success($newForm, 201);
        } else {
            Response::error('Erro ao criar formulário', 500);
        }
    }
    
    /**
     * Atualiza formulário
     */
    public function update($formId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::error('Método não permitido', 405);
        }
        
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $formId = Validator::sanitizeInt($formId);
        
        if (!$formId || $formId <= 0) {
            Response::error('ID do formulário inválido', 400);
        }
        
        // Verifica se formulário existe
        $existingForm = $this->formModel->getFormById($storeId, $formId);
        if (!$existingForm) {
            Response::error('Formulário não encontrado', 404);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('JSON inválido', 400);
        }
        
        // Validação do JSON dos campos se fornecido
        if (isset($data['campos_json']) && is_string($data['campos_json'])) {
            $campos = json_decode($data['campos_json'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Response::error('JSON dos campos inválido', 400);
            }
        }
        
        // Campos permitidos para atualização
        $allowedFields = [
            'nome_formulario', 'titulo', 'descricao', 'tipo', 'campos_json',
            'email_destino', 'mensagem_sucesso', 'redirect_url',
            'exibir_como_popup', 'ativar_captcha', 'ativo'
        ];
        
        $updateData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $updateData[$key] = $value;
            }
        }
        
        if (empty($updateData)) {
            Response::error('Nenhum campo válido para atualização', 400);
        }
        
        $updated = $this->formModel->updateForm($storeId, $formId, $updateData);
        
        if ($updated) {
            $updatedForm = $this->formModel->getFormById($storeId, $formId);
            Response::success($updatedForm);
        } else {
            Response::error('Erro ao atualizar formulário', 500);
        }
    }
    
    /**
     * Remove formulário
     */
    public function delete($formId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            Response::error('Método não permitido', 405);
        }
        
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $formId = Validator::sanitizeInt($formId);
        
        if (!$formId || $formId <= 0) {
            Response::error('ID do formulário inválido', 400);
        }
        
        // Verifica se formulário existe
        $existingForm = $this->formModel->getFormById($storeId, $formId);
        if (!$existingForm) {
            Response::error('Formulário não encontrado', 404);
        }
        
        $deleted = $this->formModel->deleteForm($storeId, $formId);
        
        if ($deleted) {
            Response::success(['message' => 'Formulário removido com sucesso']);
        } else {
            Response::error('Erro ao remover formulário', 500);
        }
    }
}

// ========================================
// NotificationController.php
// ========================================

namespace App\Controllers;

use App\Models\NotificationConfiguration;
use App\Utils\Response;
use App\Middleware\AuthMiddleware;
use App\Utils\Validator;

class NotificationController {
    private $notificationModel;
    
    public function __construct() {
        $this->notificationModel = new NotificationConfiguration();
    }
    
    /**
     * Obtém configurações de notificação
     */
    public function getNotificationConfig() {
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $config = $this->notificationModel->getNotificationConfig($storeId);
        
        if (!$config) {
            Response::error('Configurações de notificação não encontradas', 404);
        }
        
        Response::success($config);
    }
    
    /**
     * Atualiza configurações de notificação
     */
    public function updateNotificationConfig() {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::error('Método não permitido', 405);
        }
        
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('JSON inválido', 400);
        }
        
        // Campos permitidos
        $allowedFields = [
            'email_pedido_cliente', 'email_pedido_loja', 'email_pagamento_aprovado',
            'email_produto_enviado', 'email_produto_entregue', 'email_newsletter',
            'whatsapp_pedido_cliente', 'whatsapp_pedido_loja', 'whatsapp_link_direto',
            'template_whatsapp_pedido', 'mensagem_carrinho_abandonado', 'mensagem_frete_gratis'
        ];
        
        $updateData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                // Validação para campos char(1)
                if (in_array($key, [
                    'email_pedido_cliente', 'email_pedido_loja', 'email_pagamento_aprovado',
                    'email_produto_enviado', 'email_produto_entregue', 'email_newsletter',
                    'whatsapp_pedido_cliente', 'whatsapp_pedido_loja', 'whatsapp_link_direto'
                ])) {
                    if (!in_array($value, ['S', 'N'])) {
                        Response::error("Valor inválido para {$key}. Use 'S' ou 'N'", 400);
                    }
                }
                
                $updateData[$key] = $value;
            }
        }
        
        if (empty($updateData)) {
            Response::error('Nenhum campo válido para atualização', 400);
        }
        
        $updated = $this->notificationModel->updateNotificationConfig($storeId, $updateData);
        
        if ($updated) {
            $updatedConfig = $this->notificationModel->getNotificationConfig($storeId);
            Response::success($updatedConfig);
        } else {
            Response::error('Erro ao atualizar configurações de notificação', 500);
        }
    }
}

// ========================================
// CustomizationController.php (Controller Principal)
// ========================================

namespace App\Controllers;

use App\Models\CustomizationManager;
use App\Models\Store;
use App\Utils\Response;
use App\Middleware\AuthMiddleware;
use App\Utils\Validator;

class CustomizationController {
    private $customizationManager;
    
    public function __construct() {
        $this->customizationManager = new CustomizationManager();
    }
    
    /**
     * Obtém todas as configurações de customização (autenticado)
     */
    public function getAllConfigurations() {
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        try {
            $configurations = $this->customizationManager->getAllCustomizations($storeId);
            Response::success($configurations);
        } catch (\Exception $e) {
            error_log('Erro ao carregar configurações: ' . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }
    
    /**
     * Obtém configurações públicas (sem autenticação)
     */
    public function getPublicConfigurations() {
        // Obtém store_id pela URL da loja
        $requestUri = $_SERVER['REQUEST_URI'];
        $path = parse_url($requestUri, PHP_URL_PATH);
        $pathParts = explode('/', trim($path, '/'));
        
        // Espera formato: /{store_url}/public-config
        if (count($pathParts) < 2 || $pathParts[1] !== 'public-config') {
            Response::error('URL inválida para configurações públicas', 400);
        }
        
        if (!isset($pathParts[0]) || empty($pathParts[0])) {
            Response::error('URL da loja não especificada', 400);
        }
        
        $storeUrl = $pathParts[0];
        
        // Busca a loja pela URL
        $storeModel = new Store();
        $store = $storeModel->getStoreByUrl($storeUrl);
        
        if (!$store) {
            Response::error('Loja não encontrada ou inativa', 404);
        }
        
        $storeId = $store['id_loja'];
        
        try {
            // Obtém configurações públicas
            $publicConfigurations = $this->customizationManager->getPublicCustomizations($storeId);
            
            // Informações básicas da loja
            $storeInfo = [
                'id' => $store['id_loja'],
                'nome' => $store['nome_loja'],
                'descricao' => $store['descricao_loja'],
                'url' => $store['url_loja'],
                'logo' => $store['logo_loja'],
                'whatsapp' => $store['whatsapp_loja'],
                'telefone' => $store['telefone_loja'],
                'email' => $store['email_loja'],
                'facebook' => $store['facebook_loja'],
                'instagram' => $store['instagram_loja'],
                'tiktok' => $store['tiktok_loja']
            ];
            
            // Monta resposta final
            $response = array_merge(['store' => $storeInfo], $publicConfigurations);
            
            Response::success($response);
            
        } catch (\Exception $e) {
            error_log('Erro ao carregar configurações públicas: ' . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }
    
    /**
     * Obtém estatísticas de customização
     */
    public function getStats() {
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        try {
            $stats = $this->customizationManager->getCustomizationStats($storeId);
            Response::success($stats);
        } catch (\Exception $e) {
            error_log('Erro ao carregar estatísticas: ' . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }
    
    /**
     * Endpoint para verificar se todas as configurações estão definidas
     */
    public function checkSetup() {
        $payload = AuthMiddleware::verifyToken();
        $storeId = $payload['store_id'];
        
        try {
            $configurations = $this->customizationManager->getAllCustomizations($storeId);
            $stats = $this->customizationManager->getCustomizationStats($storeId);
            
            // Verifica se configurações básicas estão definidas
            $setupComplete = [
                'theme_configured' => !empty($configurations['theme']),
                'site_config_configured' => !empty($configurations['site_config']),
                'has_banners' => $stats['total_banners'] > 0,
                'has_pages' => $stats['total_pages'] > 0,
                'has_hero_banner' => $stats['banners_by_position']['hero'] > 0,
                'setup_percentage' => 0
            ];
            
            // Calcula porcentagem de configuração
            $totalChecks = count($setupComplete) - 1; // Remove setup_percentage
            $completedChecks = array_sum(array_filter($setupComplete, function($key) {
                return $key !== 'setup_percentage';
            }, ARRAY_FILTER_USE_KEY));
            
            $setupComplete['setup_percentage'] = round(($completedChecks / $totalChecks) * 100);
            
            Response::success([
                'setup_status' => $setupComplete,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            error_log('Erro ao verificar configuração: ' . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }
}

?>