<?php

namespace App\Routes;

use App\Controllers\ApiController;
use App\Controllers\AuthController;
use App\Controllers\StoreController;
use App\Controllers\CategoryController;
use App\Controllers\CouponController;
use App\Controllers\ParameterController;
use App\Controllers\ThemeController;
use App\Controllers\BannerController;
use App\Controllers\StaticPageController;
use App\Controllers\SiteConfigurationController;
use App\Controllers\TestimonialController;
use App\Controllers\FAQController;
use App\Controllers\CustomFormController;
use App\Controllers\NotificationController;
use App\Controllers\CustomizationController;
use App\Utils\Response;
use App\Config\Config;

class Api
{
    /**
     * Processa a requisição e roteia para o controlador e método apropriados
     */
    public static function route()
    {
        // Obtém a URL da requisição
        $requestUri = $_SERVER['REQUEST_URI'];
        $uri = parse_url($requestUri, PHP_URL_PATH);

        // Obtém o path base dinâmico
        $basePath = Config::getBasePath();

        // Remove o prefixo do caminho base se ele existir
        if (!empty($basePath) && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        // Garante que a URI comece com /
        $uri = '/' . ltrim($uri, '/');

        // Remove a parte /public/index.php se presente
        $uri = str_replace('/public/index.php', '', $uri);

        // Log para depuração (remova em produção)
        if (Config::getEnvironment() === 'development') {
            error_log("Base Path: " . $basePath);
            error_log("Original URI: " . $requestUri);
            error_log("Processed URI: " . $uri);
        }

        // Extrai as partes do caminho
        $pathParts = explode('/', trim($uri, '/'));

        // Se a URI estiver vazia, trata como rota raiz
        if (empty($pathParts[0])) {
            $controller = new ApiController();
            $controller->index();
            return;
        }

        // Roteamento simples
        switch ($pathParts[0]) {
            case 'health':
                $controller = new ApiController();
                $controller->healthCheck();
                return;

            case 'info':
                $controller = new ApiController();
                $controller->info();
                return;

            case 'debug':
                $controller = new ApiController();
                $controller->debug();
                return;

            case 'auth':
                if (isset($pathParts[1])) {
                    $controller = new AuthController();

                    switch ($pathParts[1]) {
                        case 'login':
                            $controller->login();
                            return;

                        case 'validate':
                            $controller->validateToken();
                            return;

                        case 'renew':
                            $controller->renewToken();
                            return;

                        case 'logout':
                            $controller->logout();
                            return;
                    }
                }
                break;

            case 'store':
                if (isset($pathParts[1])) {
                    $controller = new StoreController();

                    switch ($pathParts[1]) {
                        case 'info':
                            $controller->getInfo();
                            return;
                    }
                }
                break;

            case 'products':
                $controller = new \App\Controllers\ProductController();

                if (isset($pathParts[1])) {
                    switch ($pathParts[1]) {
                        case 'category':
                            if (isset($pathParts[2])) {
                                // /products/category/{id}
                                $controller->getByCategory();
                                return;
                            } else {
                                // /products/category (lista produtos por categoria via query string)
                                $controller->getByCategory();
                                return;
                            }

                        default:
                            // /products/{id} - buscar produto específico
                            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                                $controller->getById();
                                return;
                            } else {
                                Response::error('Método não permitido para este endpoint', 405);
                            }
                    }
                } else {
                    // /products - listar todos os produtos
                    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                        $controller->getAll();
                        return;
                    } else {
                        Response::error('Método não permitido para este endpoint', 405);
                    }
                }
                break;

            case 'categories':
                $controller = new CategoryController();

                // Se não há segundo parâmetro, lista todas ou busca por query string
                if (!isset($pathParts[1])) {
                    switch ($_SERVER['REQUEST_METHOD']) {
                        case 'GET':
                            $controller->index();
                            return;

                        case 'POST':
                            $controller->store();
                            return;
                    }
                } else {
                    // Se há um ID como segundo parâmetro
                    $categoryId = $pathParts[1];

                    switch ($_SERVER['REQUEST_METHOD']) {
                        case 'GET':
                            $controller->show($categoryId);
                            return;

                        case 'PUT':
                            $controller->update($categoryId);
                            return;

                        case 'DELETE':
                            $controller->delete($categoryId);
                            return;
                    }
                }
                break;

            case 'coupons':
                $controller = new CouponController();

                // Verifica se há subcaminhos específicos
                if (isset($pathParts[1])) {
                    switch ($pathParts[1]) {
                        case 'validate':
                            // POST /coupons/validate
                            $controller->validate();
                            return;

                        case 'code':
                            // GET /coupons/code?code=CODIGO
                            $controller->getByCode();
                            return;

                        case 'near-expiry':
                            // GET /coupons/near-expiry?days=7
                            $controller->nearExpiry();
                            return;

                        case 'stats':
                            // GET /coupons/stats
                            $controller->stats();
                            return;

                        default:
                            // Se não é uma rota especial, trata como ID
                            $couponId = $pathParts[1];

                            switch ($_SERVER['REQUEST_METHOD']) {
                                case 'GET':
                                    $controller->show($couponId);
                                    return;
                            }
                    }
                } else {
                    // /coupons - lista todos os cupons
                    switch ($_SERVER['REQUEST_METHOD']) {
                        case 'GET':
                            $controller->index();
                            return;
                    }
                }
                break;

            case 'parameters':
                $controller = new ParameterController();

                // Verifica se há subcaminhos específicos
                if (isset($pathParts[1])) {
                    switch ($pathParts[1]) {
                        case 'groups':
                            // GET /parameters/groups - lista todos os grupos
                            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                                $controller->getGroups();
                                return;
                            }
                            break;

                        case 'group':
                            // GET /parameters/group/{id} - parâmetros por grupo
                            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                                $controller->getByGroup();
                                return;
                            }
                            break;

                        case 'key':
                            // GET /parameters/key?group_id=X&key=Y - parâmetro específico
                            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                                $controller->getByKey();
                                return;
                            }
                            break;
                    }
                } else {
                    // GET /parameters?group_id=X - parâmetros por grupo via query string
                    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                        $controller->getByGroup();
                        return;
                    }
                }
                break;

            // ========================================
            // ROTAS DE PERSONALIZAÇÃO
            // ========================================

            case 'theme':
                $controller = new ThemeController();
                
                switch ($_SERVER['REQUEST_METHOD']) {
                    case 'GET':
                        $controller->getTheme();
                        return;
                        
                    case 'PUT':
                        $controller->updateTheme();
                        return;
                }
                break;

            case 'banners':
                $controller = new BannerController();

                if (!isset($pathParts[1])) {
                    switch ($_SERVER['REQUEST_METHOD']) {
                        case 'GET':
                            $controller->index();
                            return;

                        case 'POST':
                            $controller->store();
                            return;
                    }
                } else {
                    $bannerId = $pathParts[1];

                    switch ($_SERVER['REQUEST_METHOD']) {
                        case 'GET':
                            $controller->show($bannerId);
                            return;

                        case 'PUT':
                            $controller->update($bannerId);
                            return;

                        case 'DELETE':
                            $controller->delete($bannerId);
                            return;
                    }
                }
                break;

            case 'pages':
                $controller = new StaticPageController();

                if (isset($pathParts[1])) {
                    switch ($pathParts[1]) {
                        case 'menu':
                            // GET /pages/menu
                            $controller->getMenuPages();
                            return;

                        case 'footer':
                            // GET /pages/footer
                            $controller->getFooterPages();
                            return;

                        case 'type':
                            // GET /pages/type/{type}
                            if (isset($pathParts[2])) {
                                $controller->getByType($pathParts[2]);
                                return;
                            }
                            break;

                        default:
                            // /pages/{id}
                            $pageId = $pathParts[1];

                            switch ($_SERVER['REQUEST_METHOD']) {
                                case 'GET':
                                    $controller->show($pageId);
                                    return;

                                case 'PUT':
                                    $controller->update($pageId);
                                    return;

                                case 'DELETE':
                                    $controller->delete($pageId);
                                    return;
                            }
                    }
                } else {
                    // /pages
                    switch ($_SERVER['REQUEST_METHOD']) {
                        case 'GET':
                            $controller->index();
                            return;

                        case 'POST':
                            $controller->store();
                            return;
                    }
                }
                break;

            case 'site-config':
                $controller = new SiteConfigurationController();

                switch ($_SERVER['REQUEST_METHOD']) {
                    case 'GET':
                        $controller->getSiteConfig();
                        return;

                    case 'PUT':
                        $controller->updateSiteConfig();
                        return;
                }
                break;

            case 'testimonials':
                $controller = new TestimonialController();

                if (!isset($pathParts[1])) {
                    switch ($_SERVER['REQUEST_METHOD']) {
                        case 'GET':
                            $controller->index();
                            return;

                        case 'POST':
                            $controller->store();
                            return;
                    }
                } else {
                    $testimonialId = $pathParts[1];

                    switch ($_SERVER['REQUEST_METHOD']) {
                        case 'GET':
                            $controller->show($testimonialId);
                            return;

                        case 'PUT':
                            $controller->update($testimonialId);
                            return;

                        case 'DELETE':
                            $controller->delete($testimonialId);
                            return;
                    }
                }
                break;

            case 'faq':
                $controller = new FAQController();

                if (isset($pathParts[1])) {
                    switch ($pathParts[1]) {
                        case 'categories':
                            // GET /faq/categories
                            $controller->getCategories();
                            return;

                        default:
                            // /faq/{id}
                            $faqId = $pathParts[1];

                            switch ($_SERVER['REQUEST_METHOD']) {
                                case 'GET':
                                    $controller->show($faqId);
                                    return;

                                case 'PUT':
                                    $controller->update($faqId);
                                    return;

                                case 'DELETE':
                                    $controller->delete($faqId);
                                    return;
                            }
                    }
                } else {
                    // /faq
                    switch ($_SERVER['REQUEST_METHOD']) {
                        case 'GET':
                            $controller->index();
                            return;

                        case 'POST':
                            $controller->store();
                            return;
                    }
                }
                break;

            case 'forms':
                $controller = new CustomFormController();

                if (isset($pathParts[1])) {
                    switch ($pathParts[1]) {
                        case 'type':
                            // GET /forms/type/{type}
                            if (isset($pathParts[2])) {
                                $controller->getByType($pathParts[2]);
                                return;
                            }
                            break;

                        default:
                            // /forms/{id}
                            $formId = $pathParts[1];

                            switch ($_SERVER['REQUEST_METHOD']) {
                                case 'GET':
                                    $controller->show($formId);
                                    return;

                                case 'PUT':
                                    $controller->update($formId);
                                    return;

                                case 'DELETE':
                                    $controller->delete($formId);
                                    return;
                            }
                    }
                } else {
                    // /forms
                    switch ($_SERVER['REQUEST_METHOD']) {
                        case 'GET':
                            $controller->index();
                            return;

                        case 'POST':
                            $controller->store();
                            return;
                    }
                }
                break;

            case 'notifications':
                $controller = new NotificationController();

                switch ($_SERVER['REQUEST_METHOD']) {
                    case 'GET':
                        $controller->getNotificationConfig();
                        return;

                    case 'PUT':
                        $controller->updateNotificationConfig();
                        return;
                }
                break;

            case 'customization':
                $controller = new CustomizationController();

                if (isset($pathParts[1])) {
                    switch ($pathParts[1]) {
                        case 'all':
                            // GET /customization/all (com autenticação)
                            $controller->getAllConfigurations();
                            return;

                        case 'stats':
                            // GET /customization/stats
                            $controller->getStats();
                            return;

                        case 'setup':
                            // GET /customization/setup
                            $controller->checkSetup();
                            return;
                    }
                }
                break;

            // Rota pública para configurações (sem autenticação)
            case 'public-config':
                $controller = new CustomizationController();
                $controller->getPublicConfigurations();
                return;

            // Verificar se é uma URL de loja com configurações públicas
            default:
                // Se não é uma rota da API, pode ser uma rota de loja
                // Formato esperado: /{store_url}/public-config
                if (isset($pathParts[1]) && $pathParts[1] === 'public-config') {
                    $controller = new CustomizationController();
                    $controller->getPublicConfigurations();
                    return;
                }
                break;
        }

        // Se chegamos aqui, a rota não foi encontrada
        Response::error('Recurso não encontrado: ' . $uri, 404);
    }
}