<?php
if (!isset($storeData)) {
    $storeData = getStoreData();
}
if (!isset($categories)) {
    $categories = getCategories();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? ($storeData['nome_loja'] ?? 'VirtuaVenda')) ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= htmlspecialchars($pageDescription ?? ($storeData['descricao_loja'] ?? 'Sua loja online de confiança')) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($pageKeywords ?? 'loja online, e-commerce, produtos') ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle ?? $storeData['nome_loja']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription ?? $storeData['descricao_loja']) ?>">
    <meta property="og:image" content="<?= getImageUrl($storeData['logo_loja'] ?? '') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Tailwind Config com Tema Dinâmico -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '<?= $themeConfig['primary_50'] ?? '#eff6ff' ?>',
                            100: '<?= $themeConfig['primary_100'] ?? '#dbeafe' ?>',
                            500: '<?= $themeConfig['primary_500'] ?? '#3b82f6' ?>',
                            600: '<?= $themeConfig['primary_600'] ?? '#2563eb' ?>',
                            700: '<?= $themeConfig['primary_700'] ?? '#1d4ed8' ?>',
                            800: '<?= $themeConfig['primary_800'] ?? '#1e40af' ?>',
                            900: '<?= $themeConfig['primary_900'] ?? '#1e3a8a' ?>',
                        },
                        secondary: {
                            50: '<?= $themeConfig['secondary_50'] ?? '#f8fafc' ?>',
                            500: '<?= $themeConfig['secondary_500'] ?? '#64748b' ?>',
                            600: '<?= $themeConfig['secondary_600'] ?? '#475569' ?>',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Header -->
    <header class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="/">
                        <?php if (!empty($storeData['logo_loja'])): ?>
                            <img src="<?= getImageUrl($storeData['logo_loja']) ?>" 
                                 alt="<?= htmlspecialchars($storeData['nome_loja']) ?>" 
                                 class="h-8 w-auto">
                        <?php else: ?>
                            <span class="text-xl font-bold text-primary-600">
                                <?= htmlspecialchars($storeData['nome_loja'] ?? 'VirtuaVenda') ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
                
                <!-- Search Bar (Desktop) -->
                <div class="hidden md:flex flex-1 max-w-lg mx-8">
                    <form method="GET" action="/busca.php" class="relative w-full">
                        <input type="text" 
                               name="q"
                               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                               placeholder="Buscar produtos..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <button type="submit" class="absolute left-3 top-3 text-gray-400 hover:text-primary-600">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center space-x-4">
                    <!-- Wishlist -->
                    <a href="/lista-desejos.php" class="hidden sm:block p-2 text-gray-600 hover:text-primary-600 relative">
                        <i class="fas fa-heart text-xl"></i>
                        <span class="wishlist-count absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
                    </a>
                    
                    <!-- Cart -->
                    <button class="p-2 text-gray-600 hover:text-primary-600 relative" onclick="toggleCart()">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <span id="cart-count" class="absolute -top-1 -right-1 bg-primary-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                    </button>
                    
                    <!-- User Menu -->
                    <div class="relative hidden sm:block">
                        <button class="p-2 text-gray-600 hover:text-primary-600" onclick="toggleUserMenu()">
                            <i class="fas fa-user text-xl"></i>
                        </button>
                        <div id="user-menu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg hidden">
                            <a href="/login.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Entrar</a>
                            <a href="/cadastro.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Cadastrar</a>
                        </div>
                    </div>
                    
                    <!-- Mobile Menu Toggle -->
                    <button class="md:hidden p-2 text-gray-600" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Search -->
            <div class="md:hidden pb-4">
                <form method="GET" action="/busca.php" class="relative">
                    <input type="text" 
                           name="q"
                           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                           placeholder="Buscar produtos..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <button type="submit" class="absolute left-3 top-3 text-gray-400">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Navigation (Desktop) -->
        <nav class="hidden md:block bg-primary-600 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex space-x-8">
                    <a href="/" class="py-3 px-1 hover:bg-primary-700 transition-colors <?= $currentPage === 'home' ? 'bg-primary-700' : '' ?>">
                        Início
                    </a>
                    <a href="/produtos.php" class="py-3 px-1 hover:bg-primary-700 transition-colors <?= $currentPage === 'products' ? 'bg-primary-700' : '' ?>">
                        Produtos
                    </a>
                    
                    <!-- Dropdown de Categorias -->
                    <div class="relative group">
                        <button class="py-3 px-1 hover:bg-primary-700 transition-colors">
                            Categorias <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </button>
                        <div class="absolute top-full left-0 bg-white shadow-lg rounded-md min-w-48 hidden group-hover:block">
                            <?php foreach ($categories as $category): ?>
                                <a href="/categoria.php?id=<?= $category['id_produtos_categorias'] ?>" 
                                   class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <?= htmlspecialchars($category['nome_categoria']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <a href="/ofertas.php" class="py-3 px-1 hover:bg-primary-700 transition-colors <?= $currentPage === 'offers' ? 'bg-primary-700' : '' ?>">
                        Ofertas
                    </a>
                    <a href="/contato.php" class="py-3 px-1 hover:bg-primary-700 transition-colors <?= $currentPage === 'contact' ? 'bg-primary-700' : '' ?>">
                        Contato
                    </a>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Mobile Navigation -->
    <div id="mobile-menu" class="mobile-menu fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl md:hidden">
        <div class="flex items-center justify-between p-4 border-b">
            <?php if (!empty($storeData['logo_loja'])): ?>
                <img src="<?= getImageUrl($storeData['logo_loja']) ?>" 
                     alt="<?= htmlspecialchars($storeData['nome_loja']) ?>" 
                     class="h-6 w-auto">
            <?php else: ?>
                <span class="text-lg font-bold text-primary-600">
                    <?= htmlspecialchars($storeData['nome_loja'] ?? 'VirtuaVenda') ?>
                </span>
            <?php endif; ?>
            <button onclick="toggleMobileMenu()" class="p-2 text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <nav class="p-4">
            <a href="/" class="block py-3 text-gray-700 hover:text-primary-600 border-b">Início</a>
            <a href="/produtos.php" class="block py-3 text-gray-700 hover:text-primary-600 border-b">Produtos</a>
            
            <!-- Categorias Mobile -->
            <div class="border-b">
                <button onclick="toggleMobileCategories()" class="w-full text-left py-3 text-gray-700 hover:text-primary-600">
                    Categorias <i class="fas fa-chevron-down float-right mt-1"></i>
                </button>
                <div id="mobile-categories" class="hidden pl-4">
                    <?php foreach ($categories as $category): ?>
                        <a href="/categoria.php?id=<?= $category['id_produtos_categorias'] ?>" 
                           class="block py-2 text-sm text-gray-600 hover:text-primary-600">
                            <?= htmlspecialchars($category['nome_categoria']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <a href="/ofertas.php" class="block py-3 text-gray-700 hover:text-primary-600 border-b">Ofertas</a>
            <a href="/contato.php" class="block py-3 text-gray-700 hover:text-primary-600 border-b">Contato</a>
            <a href="/lista-desejos.php" class="block py-3 text-gray-700 hover:text-primary-600 border-b">
                <i class="fas fa-heart mr-2"></i>Lista de Desejos
            </a>
            <a href="/login.php" class="block py-3 text-gray-700 hover:text-primary-600">
                <i class="fas fa-user mr-2"></i>Entrar / Cadastrar
            </a>
        </nav>
    </div>
    
    <!-- Overlays -->
    <div id="mobile-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden" onclick="toggleMobileMenu()"></div>
    
    <!-- Configurações JavaScript -->
    <script>
        // Configurações públicas da loja
        window.storeConfig = {
            name: <?= json_encode($storeData['nome_loja'] ?? 'VirtuaVenda') ?>,
            whatsapp: <?= json_encode($storeData['whatsapp_loja'] ?? '') ?>,
            email: <?= json_encode($storeData['email_loja'] ?? '') ?>,
            theme: {
                primary: '<?= $themeConfig['primary_500'] ?? '#3b82f6' ?>',
                secondary: '<?= $themeConfig['secondary_500'] ?? '#64748b' ?>'
            }
        };
        
        // URL base para requisições AJAX
        window.baseUrl = '<?= htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']) ?>';
    </script>