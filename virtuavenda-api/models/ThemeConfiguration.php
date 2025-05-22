<?php
// ========================================
// ThemeConfiguration.php
// ========================================

namespace App\Models;

class ThemeConfiguration {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Obtém configurações de tema da loja
     */
    public function getThemeConfig($storeId) {
        $sql = "SELECT * FROM lojas_configuracoes_tema WHERE id_loja = ? AND ativo = 'S' LIMIT 1";
        return $this->db->fetchOne($sql, [$storeId]);
    }
    
    /**
     * Atualiza configurações de tema
     */
    public function updateThemeConfig($storeId, $data) {
        return $this->db->update(
            'lojas_configuracoes_tema',
            $data,
            'id_loja = ?',
            [$storeId]
        );
    }
    
    /**
     * Cria configurações de tema
     */
    public function createThemeConfig($storeId, $data = []) {
        $data['id_loja'] = $storeId;
        return $this->db->insert('lojas_configuracoes_tema', $data);
    }
}

// ========================================
// Banner.php
// ========================================

namespace App\Models;

class Banner {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Obtém todos os banners ativos da loja
     */
    public function getAllBanners($storeId, $position = null) {
        $sql = "SELECT * FROM lojas_banners 
                WHERE id_loja = ? AND ativo = 'S'";
        
        $params = [$storeId];
        
        if ($position) {
            $sql .= " AND posicao = ?";
            $params[] = $position;
        }
        
        // Verifica datas de exibição
        $sql .= " AND (data_inicio IS NULL OR data_inicio <= CURDATE())
                  AND (data_fim IS NULL OR data_fim >= CURDATE())
                  ORDER BY ordem ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Obtém banner por ID
     */
    public function getBannerById($storeId, $bannerId) {
        $sql = "SELECT * FROM lojas_banners 
                WHERE id_loja = ? AND id_banner = ? AND ativo = 'S' LIMIT 1";
        return $this->db->fetchOne($sql, [$storeId, $bannerId]);
    }
    
    /**
     * Cria novo banner
     */
    public function createBanner($storeId, $data) {
        $data['id_loja'] = $storeId;
        return $this->db->insert('lojas_banners', $data);
    }
    
    /**
     * Atualiza banner
     */
    public function updateBanner($storeId, $bannerId, $data) {
        return $this->db->update(
            'lojas_banners',
            $data,
            'id_loja = ? AND id_banner = ?',
            [$storeId, $bannerId]
        );
    }
    
    /**
     * Remove banner (soft delete)
     */
    public function deleteBanner($storeId, $bannerId) {
        return $this->updateBanner($storeId, $bannerId, ['ativo' => 'N']);
    }
    
    /**
     * Conta total de banners
     */
    public function countBanners($storeId, $position = null) {
        $sql = "SELECT COUNT(*) as total FROM lojas_banners WHERE id_loja = ? AND ativo = 'S'";
        $params = [$storeId];
        
        if ($position) {
            $sql .= " AND posicao = ?";
            $params[] = $position;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result ? (int) $result['total'] : 0;
    }
}

// ========================================
// StaticPage.php
// ========================================

namespace App\Models;

class StaticPage {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Obtém todas as páginas da loja
     */
    public function getAllPages($storeId, $activeOnly = true) {
        $sql = "SELECT * FROM lojas_paginas_estaticas WHERE id_loja = ?";
        $params = [$storeId];
        
        if ($activeOnly) {
            $sql .= " AND ativo = 'S'";
        }
        
        $sql .= " ORDER BY ordem_menu ASC, titulo ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Obtém página por tipo
     */
    public function getPageByType($storeId, $type) {
        $sql = "SELECT * FROM lojas_paginas_estaticas 
                WHERE id_loja = ? AND tipo_pagina = ? AND ativo = 'S' LIMIT 1";
        return $this->db->fetchOne($sql, [$storeId, $type]);
    }
    
    /**
     * Obtém página por ID
     */
    public function getPageById($storeId, $pageId) {
        $sql = "SELECT * FROM lojas_paginas_estaticas 
                WHERE id_loja = ? AND id_pagina = ? AND ativo = 'S' LIMIT 1";
        return $this->db->fetchOne($sql, [$storeId, $pageId]);
    }
    
    /**
     * Obtém páginas para o menu
     */
    public function getMenuPages($storeId) {
        $sql = "SELECT id_pagina, titulo, tipo_pagina FROM lojas_paginas_estaticas 
                WHERE id_loja = ? AND ativo = 'S' AND exibir_menu = 'S'
                ORDER BY ordem_menu ASC";
        return $this->db->fetchAll($sql, [$storeId]);
    }
    
    /**
     * Obtém páginas para o footer
     */
    public function getFooterPages($storeId) {
        $sql = "SELECT id_pagina, titulo, tipo_pagina FROM lojas_paginas_estaticas 
                WHERE id_loja = ? AND ativo = 'S' AND exibir_footer = 'S'
                ORDER BY ordem_menu ASC";
        return $this->db->fetchAll($sql, [$storeId]);
    }
    
    /**
     * Cria nova página
     */
    public function createPage($storeId, $data) {
        $data['id_loja'] = $storeId;
        return $this->db->insert('lojas_paginas_estaticas', $data);
    }
    
    /**
     * Atualiza página
     */
    public function updatePage($storeId, $pageId, $data) {
        return $this->db->update(
            'lojas_paginas_estaticas',
            $data,
            'id_loja = ? AND id_pagina = ?',
            [$storeId, $pageId]
        );
    }
    
    /**
     * Remove página (soft delete)
     */
    public function deletePage($storeId, $pageId) {
        return $this->updatePage($storeId, $pageId, ['ativo' => 'N']);
    }
    
    /**
     * Conta total de páginas
     */
    public function countPages($storeId) {
        $sql = "SELECT COUNT(*) as total FROM lojas_paginas_estaticas WHERE id_loja = ? AND ativo = 'S'";
        $result = $this->db->fetchOne($sql, [$storeId]);
        return $result ? (int) $result['total'] : 0;
    }
}

// ========================================
// SiteConfiguration.php
// ========================================

namespace App\Models;

class SiteConfiguration {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Obtém configurações do site
     */
    public function getSiteConfig($storeId) {
        $sql = "SELECT * FROM lojas_configuracoes_site WHERE id_loja = ? AND ativo = 'S' LIMIT 1";
        return $this->db->fetchOne($sql, [$storeId]);
    }
    
    /**
     * Atualiza configurações do site
     */
    public function updateSiteConfig($storeId, $data) {
        return $this->db->update(
            'lojas_configuracoes_site',
            $data,
            'id_loja = ?',
            [$storeId]
        );
    }
    
    /**
     * Cria configurações do site
     */
    public function createSiteConfig($storeId, $data = []) {
        $data['id_loja'] = $storeId;
        return $this->db->insert('lojas_configuracoes_site', $data);
    }
}

// ========================================
// NotificationConfiguration.php
// ========================================

namespace App\Models;

class NotificationConfiguration {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Obtém configurações de notificação
     */
    public function getNotificationConfig($storeId) {
        $sql = "SELECT * FROM lojas_configuracoes_notificacoes WHERE id_loja = ? AND ativo = 'S' LIMIT 1";
        return $this->db->fetchOne($sql, [$storeId]);
    }
    
    /**
     * Atualiza configurações de notificação
     */
    public function updateNotificationConfig($storeId, $data) {
        return $this->db->update(
            'lojas_configuracoes_notificacoes',
            $data,
            'id_loja = ?',
            [$storeId]
        );
    }
    
    /**
     * Cria configurações de notificação
     */
    public function createNotificationConfig($storeId, $data = []) {
        $data['id_loja'] = $storeId;
        return $this->db->insert('lojas_configuracoes_notificacoes', $data);
    }
}

// ========================================
// Testimonial.php
// ========================================

namespace App\Models;

class Testimonial {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Obtém todos os depoimentos da loja
     */
    public function getAllTestimonials($storeId, $homepage = false) {
        $sql = "SELECT * FROM lojas_depoimentos WHERE id_loja = ? AND ativo = 'S'";
        $params = [$storeId];
        
        if ($homepage) {
            $sql .= " AND exibir_homepage = 'S'";
        }
        
        $sql .= " ORDER BY ordem ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Obtém depoimento por ID
     */
    public function getTestimonialById($storeId, $testimonialId) {
        $sql = "SELECT * FROM lojas_depoimentos 
                WHERE id_loja = ? AND id_depoimento = ? AND ativo = 'S' LIMIT 1";
        return $this->db->fetchOne($sql, [$storeId, $testimonialId]);
    }
    
    /**
     * Cria novo depoimento
     */
    public function createTestimonial($storeId, $data) {
        $data['id_loja'] = $storeId;
        return $this->db->insert('lojas_depoimentos', $data);
    }
    
    /**
     * Atualiza depoimento
     */
    public function updateTestimonial($storeId, $testimonialId, $data) {
        return $this->db->update(
            'lojas_depoimentos',
            $data,
            'id_loja = ? AND id_depoimento = ?',
            [$storeId, $testimonialId]
        );
    }
    
    /**
     * Remove depoimento (soft delete)
     */
    public function deleteTestimonial($storeId, $testimonialId) {
        return $this->updateTestimonial($storeId, $testimonialId, ['ativo' => 'N']);
    }
    
    /**
     * Conta total de depoimentos
     */
    public function countTestimonials($storeId, $homepage = false) {
        $sql = "SELECT COUNT(*) as total FROM lojas_depoimentos WHERE id_loja = ? AND ativo = 'S'";
        $params = [$storeId];
        
        if ($homepage) {
            $sql .= " AND exibir_homepage = 'S'";
            $params[] = 'S';
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result ? (int) $result['total'] : 0;
    }
}

// ========================================
// FAQ.php
// ========================================

namespace App\Models;

class FAQ {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Obtém todas as perguntas da loja
     */
    public function getAllFAQs($storeId, $category = null) {
        $sql = "SELECT * FROM lojas_faq WHERE id_loja = ? AND ativo = 'S'";
        $params = [$storeId];
        
        if ($category) {
            $sql .= " AND categoria_faq = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY ordem ASC, pergunta ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Obtém categorias de FAQ
     */
    public function getFAQCategories($storeId) {
        $sql = "SELECT DISTINCT categoria_faq 
                FROM lojas_faq 
                WHERE id_loja = ? AND ativo = 'S'
                ORDER BY categoria_faq ASC";
        return $this->db->fetchAll($sql, [$storeId]);
    }
    
    /**
     * Obtém FAQ por ID
     */
    public function getFAQById($storeId, $faqId) {
        $sql = "SELECT * FROM lojas_faq 
                WHERE id_loja = ? AND id_faq = ? AND ativo = 'S' LIMIT 1";
        return $this->db->fetchOne($sql, [$storeId, $faqId]);
    }
    
    /**
     * Cria nova pergunta
     */
    public function createFAQ($storeId, $data) {
        $data['id_loja'] = $storeId;
        return $this->db->insert('lojas_faq', $data);
    }
    
    /**
     * Atualiza pergunta
     */
    public function updateFAQ($storeId, $faqId, $data) {
        return $this->db->update(
            'lojas_faq',
            $data,
            'id_loja = ? AND id_faq = ?',
            [$storeId, $faqId]
        );
    }
    
    /**
     * Remove pergunta (soft delete)
     */
    public function deleteFAQ($storeId, $faqId) {
        return $this->updateFAQ($storeId, $faqId, ['ativo' => 'N']);
    }
    
    /**
     * Conta total de FAQs
     */
    public function countFAQs($storeId, $category = null) {
        $sql = "SELECT COUNT(*) as total FROM lojas_faq WHERE id_loja = ? AND ativo = 'S'";
        $params = [$storeId];
        
        if ($category) {
            $sql .= " AND categoria_faq = ?";
            $params[] = $category;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result ? (int) $result['total'] : 0;
    }
}

// ========================================
// CustomForm.php
// ========================================

namespace App\Models;

class CustomForm {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Obtém todos os formulários da loja
     */
    public function getAllForms($storeId) {
        $sql = "SELECT * FROM lojas_formularios WHERE id_loja = ? AND ativo = 'S' ORDER BY nome_formulario ASC";
        return $this->db->fetchAll($sql, [$storeId]);
    }
    
    /**
     * Obtém formulário por ID
     */
    public function getFormById($storeId, $formId) {
        $sql = "SELECT * FROM lojas_formularios 
                WHERE id_loja = ? AND id_formulario = ? AND ativo = 'S' LIMIT 1";
        return $this->db->fetchOne($sql, [$storeId, $formId]);
    }
    
    /**
     * Obtém formulário por tipo
     */
    public function getFormByType($storeId, $type) {
        $sql = "SELECT * FROM lojas_formularios 
                WHERE id_loja = ? AND tipo = ? AND ativo = 'S' LIMIT 1";
        return $this->db->fetchOne($sql, [$storeId, $type]);
    }
    
    /**
     * Cria novo formulário
     */
    public function createForm($storeId, $data) {
        $data['id_loja'] = $storeId;
        return $this->db->insert('lojas_formularios', $data);
    }
    
    /**
     * Atualiza formulário
     */
    public function updateForm($storeId, $formId, $data) {
        return $this->db->update(
            'lojas_formularios',
            $data,
            'id_loja = ? AND id_formulario = ?',
            [$storeId, $formId]
        );
    }
    
    /**
     * Remove formulário (soft delete)
     */
    public function deleteForm($storeId, $formId) {
        return $this->updateForm($storeId, $formId, ['ativo' => 'N']);
    }
    
    /**
     * Conta total de formulários
     */
    public function countForms($storeId) {
        $sql = "SELECT COUNT(*) as total FROM lojas_formularios WHERE id_loja = ? AND ativo = 'S'";
        $result = $this->db->fetchOne($sql, [$storeId]);
        return $result ? (int) $result['total'] : 0;
    }
}

// ========================================
// CustomizationManager.php
// ========================================

namespace App\Models;

class CustomizationManager {
    private $themeModel;
    private $siteConfigModel;
    private $bannerModel;
    private $pageModel;
    private $testimonialModel;
    private $faqModel;
    private $formModel;
    private $notificationModel;
    
    public function __construct() {
        $this->themeModel = new ThemeConfiguration();
        $this->siteConfigModel = new SiteConfiguration();
        $this->bannerModel = new Banner();
        $this->pageModel = new StaticPage();
        $this->testimonialModel = new Testimonial();
        $this->faqModel = new FAQ();
        $this->formModel = new CustomForm();
        $this->notificationModel = new NotificationConfiguration();
    }
    
    /**
     * Obtém todas as configurações de customização de uma loja
     */
    public function getAllCustomizations($storeId) {
        return [
            'theme' => $this->themeModel->getThemeConfig($storeId),
            'site_config' => $this->siteConfigModel->getSiteConfig($storeId),
            'notification_config' => $this->notificationModel->getNotificationConfig($storeId),
            'banners' => [
                'hero' => $this->bannerModel->getAllBanners($storeId, 'hero'),
                'secundario' => $this->bannerModel->getAllBanners($storeId, 'secundario'),
                'lateral' => $this->bannerModel->getAllBanners($storeId, 'lateral'),
                'footer' => $this->bannerModel->getAllBanners($storeId, 'footer')
            ],
            'pages' => [
                'all' => $this->pageModel->getAllPages($storeId),
                'menu' => $this->pageModel->getMenuPages($storeId),
                'footer' => $this->pageModel->getFooterPages($storeId)
            ],
            'testimonials' => $this->testimonialModel->getAllTestimonials($storeId),
            'faq' => [
                'questions' => $this->faqModel->getAllFAQs($storeId),
                'categories' => $this->faqModel->getFAQCategories($storeId)
            ],
            'forms' => $this->formModel->getAllForms($storeId)
        ];
    }
    
    /**
     * Obtém configurações públicas (para frontend sem autenticação)
     */
    public function getPublicCustomizations($storeId) {
        $theme = $this->themeModel->getThemeConfig($storeId);
        $siteConfig = $this->siteConfigModel->getSiteConfig($storeId);
        
        // Remove campos sensíveis das configurações do site
        if ($siteConfig) {
            $publicSiteConfig = [
                'titulo_site' => $siteConfig['titulo_site'],
                'slogan' => $siteConfig['slogan'],
                'descricao_site' => $siteConfig['descricao_site'],
                'meta_title_padrao' => $siteConfig['meta_title_padrao'],
                'meta_description_padrao' => $siteConfig['meta_description_padrao'],
                'meta_keywords_padrao' => $siteConfig['meta_keywords_padrao'],
                'carrinho_habilitado' => $siteConfig['carrinho_habilitado'],
                'cadastro_clientes_habilitado' => $siteConfig['cadastro_clientes_habilitado'],
                'newsletter_habilitada' => $siteConfig['newsletter_habilitada'],
                'produtos_por_pagina' => $siteConfig['produtos_por_pagina'],
                'formato_preco' => $siteConfig['formato_preco'],
                'horario_funcionamento' => $siteConfig['horario_funcionamento'],
                'endereco_completo' => $siteConfig['endereco_completo']
            ];
        } else {
            $publicSiteConfig = null;
        }
        
        return [
            'theme' => $theme,
            'site_config' => $publicSiteConfig,
            'banners' => [
                'hero' => $this->bannerModel->getAllBanners($storeId, 'hero'),
                'secundario' => $this->bannerModel->getAllBanners($storeId, 'secundario'),
                'lateral' => $this->bannerModel->getAllBanners($storeId, 'lateral'),
                'footer' => $this->bannerModel->getAllBanners($storeId, 'footer')
            ],
            'menu_pages' => $this->pageModel->getMenuPages($storeId),
            'footer_pages' => $this->pageModel->getFooterPages($storeId),
            'testimonials' => $this->testimonialModel->getAllTestimonials($storeId, true), // Apenas homepage
            'faq_categories' => $this->faqModel->getFAQCategories($storeId)
        ];
    }
    
    /**
     * Obtém estatísticas de customização
     */
    public function getCustomizationStats($storeId) {
        return [
            'total_banners' => $this->bannerModel->countBanners($storeId),
            'total_pages' => $this->pageModel->countPages($storeId),
            'total_testimonials' => $this->testimonialModel->countTestimonials($storeId),
            'total_faq' => $this->faqModel->countFAQs($storeId),
            'total_forms' => $this->formModel->countForms($storeId),
            'banners_by_position' => [
                'hero' => $this->bannerModel->countBanners($storeId, 'hero'),
                'secundario' => $this->bannerModel->countBanners($storeId, 'secundario'),
                'lateral' => $this->bannerModel->countBanners($storeId, 'lateral'),
                'footer' => $this->bannerModel->countBanners($storeId, 'footer')
            ]
        ];
    }
}

?>