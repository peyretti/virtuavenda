<?php
// Carrega dados da loja se não estiver disponível
if (!isset($storeData)) {
    $storeData = getStoreData();
}
?>

<!-- Footer -->
<footer class="bg-gray-800 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Informações da Loja -->
            <div>
                <?php if (!empty($storeData['logo_loja'])): ?>
                    <img src="<?= getImageUrl($storeData['logo_loja']) ?>" 
                         alt="<?= htmlspecialchars($storeData['nome_loja']) ?>" 
                         class="h-8 w-auto mb-4 filter brightness-0 invert">
                <?php else: ?>
                    <h3 class="text-xl font-bold mb-4"><?= htmlspecialchars($storeData['nome_loja'] ?? 'VirtuaVenda') ?></h3>
                <?php endif; ?>
                
                <p class="text-gray-400 mb-4">
                    <?= htmlspecialchars($storeData['descricao_loja'] ?? 'Sua loja online de confiança com os melhores produtos e preços.') ?>
                </p>
                
                <!-- Redes Sociais -->
                <div class="flex space-x-4">
                    <?php if (!empty($storeData['facebook_loja'])): ?>
                        <a href="https://facebook.com/<?= htmlspecialchars($storeData['facebook_loja']) ?>" 
                           target="_blank" 
                           class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-facebook text-xl"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($storeData['instagram_loja'])): ?>
                        <a href="https://instagram.com/<?= htmlspecialchars($storeData['instagram_loja']) ?>" 
                           target="_blank" 
                           class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($storeData['whatsapp_loja'])): ?>
                        <a href="https://wa.me/55<?= preg_replace('/[^0-9]/', '', $storeData['whatsapp_loja']) ?>" 
                           target="_blank" 
                           class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-whatsapp text-xl"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($storeData['tiktok_loja'])): ?>
                        <a href="https://tiktok.com/@<?= htmlspecialchars($storeData['tiktok_loja']) ?>" 
                           target="_blank" 
                           class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-tiktok text-xl"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Links Rápidos -->
            <div>
                <h3 class="font-semibold mb-4">Links Rápidos</h3>
                <ul class="space-y-2">
                    <li><a href="/produtos.php" class="text-gray-400 hover:text-white transition-colors">Produtos</a></li>
                    <li><a href="/categorias.php" class="text-gray-400 hover:text-white transition-colors">Categorias</a></li>
                    <li><a href="/ofertas.php" class="text-gray-400 hover:text-white transition-colors">Ofertas</a></li>
                    <li><a href="/sobre.php" class="text-gray-400 hover:text-white transition-colors">Sobre Nós</a></li>
                    <li><a href="/blog.php" class="text-gray-400 hover:text-white transition-colors">Blog</a></li>
                </ul>
            </div>
            
            <!-- Atendimento -->
            <div>
                <h3 class="font-semibold mb-4">Atendimento</h3>
                <ul class="space-y-2">
                    <li><a href="/contato.php" class="text-gray-400 hover:text-white transition-colors">Contato</a></li>
                    <li><a href="/faq.php" class="text-gray-400 hover:text-white transition-colors">FAQ</a></li>
                    <li><a href="/trocas-devolucoes.php" class="text-gray-400 hover:text-white transition-colors">Trocas e Devoluções</a></li>
                    <li><a href="/politica-privacidade.php" class="text-gray-400 hover:text-white transition-colors">Política de Privacidade</a></li>
                    <li><a href="/termos-uso.php" class="text-gray-400 hover:text-white transition-colors">Termos de Uso</a></li>
                </ul>
            </div>
            
            <!-- Contato -->
            <div>
                <h3 class="font-semibold mb-4">Contato</h3>
                <div class="space-y-3 text-gray-400">
                    <?php if (!empty($storeData['telefone_loja'])): ?>
                        <div class="flex items-center">
                            <i class="fas fa-phone mr-3 text-primary-400"></i>
                            <a href="tel:<?= preg_replace('/[^0-9]/', '', $storeData['telefone_loja']) ?>" 
                               class="hover:text-white transition-colors">
                                <?= htmlspecialchars($storeData['telefone_loja']) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($storeData['whatsapp_loja'])): ?>
                        <div class="flex items-center">
                            <i class="fab fa-whatsapp mr-3 text-green-400"></i>
                            <a href="https://wa.me/55<?= preg_replace('/[^0-9]/', '', $storeData['whatsapp_loja']) ?>" 
                               target="_blank" 
                               class="hover:text-white transition-colors">
                                <?= htmlspecialchars($storeData['whatsapp_loja']) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($storeData['email_loja'])): ?>
                        <div class="flex items-center">
                            <i class="fas fa-envelope mr-3 text-primary-400"></i>
                            <a href="mailto:<?= htmlspecialchars($storeData['email_loja']) ?>" 
                               class="hover:text-white transition-colors">
                                <?= htmlspecialchars($storeData['email_loja']) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($storeData['endereco_loja'])): ?>
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt mr-3 text-red-400 mt-1"></i>
                            <div>
                                <?= htmlspecialchars($storeData['endereco_loja']) ?>
                                <?php if (!empty($storeData['numero_end_loja'])): ?>
                                    , <?= htmlspecialchars($storeData['numero_end_loja']) ?>
                                <?php endif; ?>
                                <?php if (!empty($storeData['bairro_loja'])): ?>
                                    <br><?= htmlspecialchars($storeData['bairro_loja']) ?>
                                <?php endif; ?>
                                <?php if (!empty($storeData['cidade_loja']) && !empty($storeData['uf_loja'])): ?>
                                    <br><?= htmlspecialchars($storeData['cidade_loja']) ?> - <?= htmlspecialchars($storeData['uf_loja']) ?>
                                <?php endif; ?>
                                <?php if (!empty($storeData['cep_loja'])): ?>
                                    <br>CEP: <?= htmlspecialchars($storeData['cep_loja']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Métodos de Pagamento -->
                <div class="mt-6">
                    <h4 class="font-semibold mb-3">Formas de Pagamento</h4>
                    <div class="flex flex-wrap gap-2">
                        <i class="fab fa-cc-visa text-2xl text-blue-600"></i>
                        <i class="fab fa-cc-mastercard text-2xl text-red-600"></i>
                        <i class="fas fa-credit-card text-2xl text-gray-400"></i>
                        <i class="fas fa-barcode text-2xl text-orange-500"></i>
                        <span class="text-xs bg-green-600 text-white px-2 py-1 rounded">PIX</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="border-t border-gray-700 mt-8 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center text-gray-400 text-sm">
                <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($storeData['nome_loja'] ?? 'VirtuaVenda') ?>. Todos os direitos reservados.</p>
                <p class="mt-2 md:mt-0">
                    Desenvolvido por <a href="https://virtuavenda.com" class="text-primary-400 hover:text-primary-300">VirtuaVenda</a>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Cart Sidebar -->
<div id="cart-sidebar" class="fixed inset-y-0 right-0 z-50 w-80 bg-white shadow-xl transform translate-x-full transition-transform duration-300">
    <div class="flex items-center justify-between p-4 border-b">
        <h3 class="text-lg font-semibold">Carrinho (<span id="cart-count-sidebar">0</span>)</h3>
        <button onclick="toggleCart()" class="p-2 text-gray-600 hover:text-gray-800">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>
    <div id="cart-items" class="flex-1 overflow-y-auto p-4 max-h-96">
        <div class="empty-cart text-center text-gray-500 mt-8">
            <i class="fas fa-shopping-cart text-4xl mb-4"></i>
            <p>Seu carrinho está vazio</p>
            <a href="/produtos.php" class="text-primary-600 hover:text-primary-700 text-sm">Continuar comprando</a>
        </div>
    </div>
    <div class="border-t p-4">
        <div class="flex justify-between items-center mb-4">
            <span class="font-semibold">Total:</span>
            <span id="cart-total" class="font-bold text-lg text-primary-600">R$ 0,00</span>
        </div>
        <div class="space-y-2">
            <a href="/carrinho.php" class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 rounded-lg font-semibold text-center transition-colors">
                Ver Carrinho
            </a>
            <a href="/checkout.php" class="block w-full bg-primary-600 hover:bg-primary-700 text-white py-2 rounded-lg font-semibold text-center transition-colors">
                Finalizar Compra
            </a>
        </div>
    </div>
</div>

<!-- Cart Overlay -->
<div id="cart-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" onclick="toggleCart()"></div>

<!-- WhatsApp Float Button -->
<?php if (!empty($storeData['whatsapp_loja']) && $storeData['ativa_botao_whats'] === 'S'): ?>
<div class="fixed bottom-6 right-6 z-40">
    <a href="https://wa.me/55<?= preg_replace('/[^0-9]/', '', $storeData['whatsapp_loja']) ?>?text=Olá! Vim do site e gostaria de mais informações." 
       target="_blank"
       class="bg-green-500 hover:bg-green-600 text-white rounded-full p-4 shadow-lg transition-all hover:scale-110 flex items-center justify-center">
        <i class="fab fa-whatsapp text-2xl"></i>
    </a>
</div>
<?php endif; ?>

<!-- Loading Overlay -->
<div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
    <div class="bg-white p-6 rounded-lg flex items-center space-x-4">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
        <span class="text-gray-700">Carregando...</span>
    </div>
</div>

<!-- Scripts JavaScript -->
<script src="./assets/js/cart.js"></script>
<script src="./assets/js/main.js"></script>

<!-- Scripts inline para funcionalidades básicas -->
<script>
// Variáveis globais
let cart = JSON.parse(localStorage.getItem('cart')) || [];
let wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
let isCartOpen = false;
let isMobileMenuOpen = false;

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    updateCartDisplay();
    updateWishlistDisplay();
    initEventListeners();
});

// Event Listeners
function initEventListeners() {
    // Botões de adicionar ao carrinho
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productData = {
                id: this.dataset.productId,
                name: this.dataset.productName,
                price: parseFloat(this.dataset.productPrice),
                image: this.dataset.productImage,
                quantity: 1
            };
            addToCart(productData);
        });
    });
    
    // Botões de wishlist
    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            toggleWishlist(productId);
        });
    });
}

// Funções do carrinho
function addToCart(product) {
    const existingItem = cart.find(item => item.id === product.id);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push(product);
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartDisplay();
    showNotification('Produto adicionado ao carrinho!', 'success');
    
    if (!isCartOpen) {
        toggleCart();
    }
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartDisplay();
}

function updateCartQuantity(productId, newQuantity) {
    if (newQuantity <= 0) {
        removeFromCart(productId);
    } else {
        const item = cart.find(item => item.id === productId);
        if (item) {
            item.quantity = newQuantity;
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartDisplay();
        }
    }
}

function updateCartDisplay() {
    const cartCount = document.getElementById('cart-count');
    const cartCountSidebar = document.getElementById('cart-count-sidebar');
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    // Atualizar contadores
    if (cartCount) cartCount.textContent = totalItems;
    if (cartCountSidebar) cartCountSidebar.textContent = totalItems;
    if (cartTotal) cartTotal.textContent = `R$ ${totalPrice.toFixed(2).replace('.', ',')}`;
    
    // Atualizar items do carrinho
    if (cartItems) {
        if (cart.length === 0) {
            cartItems.innerHTML = `
                <div class="empty-cart text-center text-gray-500 mt-8">
                    <i class="fas fa-shopping-cart text-4xl mb-4"></i>
                    <p>Seu carrinho está vazio</p>
                    <a href="/produtos.php" class="text-primary-600 hover:text-primary-700 text-sm">Continuar comprando</a>
                </div>
            `;
        } else {
            cartItems.innerHTML = cart.map(item => `
                <div class="flex items-center gap-3 mb-4 p-3 border rounded-lg">
                    <img src="${item.image}" alt="${item.name}" class="w-16 h-16 object-cover rounded">
                    <div class="flex-1">
                        <h4 class="font-semibold text-sm">${item.name}</h4>
                        <p class="text-primary-600 font-bold">R$ ${item.price.toFixed(2).replace('.', ',')}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <button onclick="updateCartQuantity('${item.id}', ${item.quantity - 1})" class="w-6 h-6 bg-gray-200 rounded text-xs hover:bg-gray-300">-</button>
                            <span class="text-sm">${item.quantity}</span>
                            <button onclick="updateCartQuantity('${item.id}', ${item.quantity + 1})" class="w-6 h-6 bg-gray-200 rounded text-xs hover:bg-gray-300">+</button>
                        </div>
                    </div>
                    <button onclick="removeFromCart('${item.id}')" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-trash text-sm"></i>
                    </button>
                </div>
            `).join('');
        }
    }
}

// Funções da wishlist
function toggleWishlist(productId) {
    const index = wishlist.indexOf(productId);
    
    if (index > -1) {
        wishlist.splice(index, 1);
        showNotification('Produto removido da lista de desejos', 'info');
    } else {
        wishlist.push(productId);
        showNotification('Produto adicionado à lista de desejos', 'success');
    }
    
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
    updateWishlistDisplay();
}

function updateWishlistDisplay() {
    const wishlistCount = document.querySelector('.wishlist-count');
    
    if (wishlistCount) {
        if (wishlist.length > 0) {
            wishlistCount.textContent = wishlist.length;
            wishlistCount.classList.remove('hidden');
        } else {
            wishlistCount.classList.add('hidden');
        }
    }
    
    // Atualizar botões de wishlist
    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        const productId = btn.dataset.productId;
        const icon = btn.querySelector('i');
        
        if (wishlist.includes(productId)) {
            icon.classList.remove('far');
            icon.classList.add('fas', 'text-red-500');
        } else {
            icon.classList.remove('fas', 'text-red-500');
            icon.classList.add('far');
        }
    });
}

// Funções de interface
function toggleCart() {
    const cartSidebar = document.getElementById('cart-sidebar');
    const cartOverlay = document.getElementById('cart-overlay');
    
    isCartOpen = !isCartOpen;
    
    if (isCartOpen) {
        cartSidebar.classList.remove('translate-x-full');
        cartOverlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    } else {
        cartSidebar.classList.add('translate-x-full');
        cartOverlay.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileOverlay = document.getElementById('mobile-overlay');
    
    isMobileMenuOpen = !isMobileMenuOpen;
    
    if (isMobileMenuOpen) {
        mobileMenu.classList.add('open');
        mobileOverlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    } else {
        mobileMenu.classList.remove('open');
        mobileOverlay.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

function toggleMobileCategories() {
    const mobileCategories = document.getElementById('mobile-categories');
    const button = mobileCategories.previousElementSibling;
    const icon = button.querySelector('i');
    
    mobileCategories.classList.toggle('hidden');
    icon.classList.toggle('fa-chevron-down');
    icon.classList.toggle('fa-chevron-up');
}

function toggleUserMenu() {
    const userMenu = document.getElementById('user-menu');
    userMenu.classList.toggle('hidden');
}

// Sistema de notificações
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg text-white font-semibold transform translate-x-full transition-transform duration-300 ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        type === 'info' ? 'bg-blue-500' :
        'bg-gray-500'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Mostrar notificação
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Remover notificação
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Funções de loading
function showLoading() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.classList.remove('hidden');
        loadingOverlay.classList.add('flex');
    }
}

function hideLoading() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.classList.add('hidden');
        loadingOverlay.classList.remove('flex');
    }
}

// Smooth scroll para links internos
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Fechar menus ao clicar fora
document.addEventListener('click', function(e) {
    // Fechar menu de usuário
    const userMenu = document.getElementById('user-menu');
    const userButton = userMenu?.previousElementSibling;
    
    if (userMenu && !userMenu.contains(e.target) && !userButton?.contains(e.target)) {
        userMenu.classList.add('hidden');
    }
});

// PWA - Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('./sw.js')
            .then(registration => {
                console.log('SW registered: ', registration);
            })
            .catch(registrationError => {
                console.log('SW registration failed: ', registrationError);
            });
    });
}

// Formulário de newsletter
const newsletterForm = document.querySelector('form[action="/newsletter.php"]');
if (newsletterForm) {
    newsletterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const email = formData.get('email');
        
        if (!email || !email.includes('@')) {
            showNotification('Por favor, insira um e-mail válido', 'error');
            return;
        }
        
        showLoading();
        
        fetch('/ajax/newsletter.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showNotification('E-mail cadastrado com sucesso!', 'success');
                this.reset();
            } else {
                showNotification(data.message || 'Erro ao cadastrar e-mail', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showNotification('Erro ao processar solicitação', 'error');
            console.error('Error:', error);
        });
    });
}
</script>

<!-- CSS adicional para mobile menu -->
<style>
.mobile-menu {
    transform: translateX(-100%);
    transition: transform 0.3s ease;
}

.mobile-menu.open {
    transform: translateX(0);
}

/* Animações personalizadas */
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.animate-pulse-custom {
    animation: pulse 2s infinite;
}

/* Scrollbar customizada */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}

/* Responsividade adicional */
@media (max-width: 640px) {
    .card-hover:hover {
        transform: none;
    }
}
</style>

</body>
</html>