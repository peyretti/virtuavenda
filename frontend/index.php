<?php
session_start();

// Carrega configurações e funcionalidades da API
require_once 'config/api.php';

try {
    // Carrega dados da loja
    $storeData = getStoreData();
    $publicConfig = getPublicConfig();
    
    // Carrega produtos em destaque
    $featuredProducts = getProducts(1, 8); // Primeiros 8 produtos
    
    // Carrega categorias
    $categories = getCategories();
    
    // Configurações da página
    $pageTitle = $storeData['nome_loja'] ?? 'VirtuaVenda';
    $pageDescription = $storeData['descricao_loja'] ?? 'Sua loja online de confiança com os melhores produtos e preços.';
    $currentPage = 'home';
    
    // Configurações de tema
    $themeConfig = $publicConfig['theme'] ?? [];
    
} catch (Exception $e) {
    error_log('Erro ao carregar dados da homepage: ' . $e->getMessage());
    
    // Dados fallback em caso de erro
    $storeData = ['nome_loja' => 'VirtuaVenda'];
    $featuredProducts = ['products' => [], 'pagination' => []];
    $categories = [];
    $themeConfig = [];
}

// Inclui o header
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="gradient-bg text-white py-12 md:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-8 items-center">
            <div class="fade-in-up">
                <h1 class="text-3xl md:text-5xl font-bold mb-4">
                    <?php if (!empty($storeData['nome_loja'])): ?>
                        Bem-vindo à <?= htmlspecialchars($storeData['nome_loja']) ?>
                    <?php else: ?>
                        Encontre os melhores produtos com os melhores preços
                    <?php endif; ?>
                </h1>
                <p class="text-lg md:text-xl mb-6 opacity-90">
                    <?= htmlspecialchars($storeData['descricao_loja'] ?? 'Descubra nossa seleção exclusiva de produtos de qualidade com entrega rápida e segura.') ?>
                </p>
                <a href="/produtos.php" class="btn-primary inline-block text-white px-8 py-3 rounded-lg font-semibold text-lg shadow-lg hover:shadow-xl transition-all">
                    Ver Produtos
                </a>
            </div>
            <div class="hidden md:block">
                <img src="./placeholder.php?w=500&h=400&text=Hero+Image" alt="Hero Image" class="rounded-lg shadow-2xl">
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<?php if (!empty($categories)): ?>
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl md:text-3xl font-bold text-center mb-8 text-gray-800">Categorias Populares</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            <?php 
            $categoryIcons = [
                'fas fa-tshirt', 'fas fa-mobile-alt', 'fas fa-home', 'fas fa-dumbbell',
                'fas fa-gamepad', 'fas fa-book', 'fas fa-car', 'fas fa-utensils'
            ];
            $categoryColors = [
                'from-blue-500 to-purple-600',
                'from-green-500 to-teal-600', 
                'from-pink-500 to-rose-600',
                'from-orange-500 to-red-600',
                'from-indigo-500 to-purple-600',
                'from-yellow-500 to-orange-600',
                'from-teal-500 to-cyan-600',
                'from-red-500 to-pink-600'
            ];
            
            foreach (array_slice($categories, 0, 8) as $index => $category): 
                $icon = $categoryIcons[$index % count($categoryIcons)];
                $color = $categoryColors[$index % count($categoryColors)];
            ?>
                <a href="/categoria.php?id=<?= $category['id_produtos_categorias'] ?>" 
                   class="card-hover bg-gradient-to-br <?= $color ?> rounded-xl p-6 text-center text-white cursor-pointer">
                    <i class="<?= $icon ?> text-3xl mb-3"></i>
                    <h3 class="font-semibold"><?= htmlspecialchars($category['nome_categoria']) ?></h3>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Featured Products -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-800">Produtos em Destaque</h2>
            <a href="/produtos.php" class="text-primary-600 hover:text-primary-700 font-semibold">Ver todos</a>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php if (!empty($featuredProducts['products'])): ?>
                <?php foreach ($featuredProducts['products'] as $product): ?>
                    <div class="bg-white rounded-xl shadow-md card-hover overflow-hidden">
                        <div class="relative">
                            <a href="/produto.php?id=<?= $product['id_produtos'] ?>">
                                <img src="<?= getImageUrl($product['imagem']) ?>" 
                                     alt="<?= htmlspecialchars($product['nome_produto']) ?>" 
                                     class="w-full h-48 object-cover">
                            </a>
                            <button class="wishlist-btn absolute top-3 right-3 p-2 bg-white rounded-full shadow-md hover:bg-red-50 hover:text-red-500 transition-colors"
                                    data-product-id="<?= $product['id_produtos'] ?>">
                                <i class="fas fa-heart"></i>
                            </button>
                            
                            <?php if ($product['frete_gratis'] === 'S'): ?>
                                <span class="absolute top-3 left-3 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
                                    Frete Grátis
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-800 mb-2">
                                <a href="/produto.php?id=<?= $product['id_produtos'] ?>" class="hover:text-primary-600">
                                    <?= htmlspecialchars($product['nome_produto']) ?>
                                </a>
                            </h3>
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <?php if ($product['menor_valor'] !== $product['maior_valor']): ?>
                                        <span class="text-sm text-gray-600">A partir de</span><br>
                                        <span class="text-lg font-bold text-primary-600">R$ <?= $product['menor_valor'] ?></span>
                                    <?php else: ?>
                                        <span class="text-lg font-bold text-primary-600">R$ <?= $product['valor_produto'] ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    <span class="text-sm text-gray-600 ml-1">4.8</span>
                                </div>
                            </div>
                            <button class="add-to-cart-btn w-full bg-primary-600 hover:bg-primary-700 text-white py-2 rounded-lg font-semibold transition-colors" 
                                    data-product-id="<?= $product['id_produtos'] ?>"
                                    data-product-name="<?= htmlspecialchars($product['nome_produto']) ?>"
                                    data-product-price="<?= $product['valor_produto'] ?>"
                                    data-product-image="<?= getImageUrl($product['imagem']) ?>">
                                Adicionar ao Carrinho
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Produtos placeholder caso não tenha produtos -->
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-box-open text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-600">Nenhum produto encontrado no momento.</p>
                    <p class="text-sm text-gray-500">Volte em breve para conferir nossos produtos!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="bg-primary-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shipping-fast text-primary-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">Entrega Rápida</h3>
                <p class="text-gray-600">Receba seus produtos em até 3 dias úteis com nossa entrega expressa.</p>
            </div>
            <div class="text-center">
                <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">Compra Segura</h3>
                <p class="text-gray-600">Seus dados estão protegidos com nossa tecnologia de segurança avançada.</p>
            </div>
            <div class="text-center">
                <div class="bg-orange-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-headset text-orange-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">Suporte 24/7</h3>
                <p class="text-gray-600">Nossa equipe está sempre pronta para ajudar você quando precisar.</p>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-12 bg-primary-600 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl md:text-3xl font-bold mb-4">Fique por dentro das novidades</h2>
        <p class="text-lg opacity-90 mb-6">Cadastre-se e receba ofertas exclusivas e lançamentos em primeira mão!</p>
        
        <form method="POST" action="/newsletter.php" class="max-w-md mx-auto flex gap-3">
            <input type="email" 
                   name="email" 
                   placeholder="Seu melhor e-mail" 
                   required
                   class="flex-1 px-4 py-3 rounded-lg text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-white focus:outline-none">
            <button type="submit" 
                    class="bg-white text-primary-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                Cadastrar
            </button>
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<!-- Scripts específicos da homepage -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animações de entrada
    const elements = document.querySelectorAll('.fade-in-up');
    elements.forEach((element, index) => {
        element.style.animationDelay = `${index * 0.2}s`;
    });
    
    // Lazy loading para imagens de produtos
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.classList.remove('opacity-0');
                        img.classList.add('opacity-100');
                        observer.unobserve(img);
                    }
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
});
</script>

<style>
/* Estilos específicos da homepage */
.gradient-bg {
    background: linear-gradient(135deg, var(--color-primary, #667eea) 0%, #764ba2 100%);
}

.card-hover {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.btn-primary {
    background: linear-gradient(135deg, var(--color-primary, #3b82f6) 0%, #1d4ed8 100%);
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    transform: translateY(-1px);
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in-up {
    animation: fadeInUp 0.6s ease forwards;
}
</style>