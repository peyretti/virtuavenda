/**
 * VirtuaVenda - JavaScript Principal
 * Gerencia todas as funcionalidades do frontend
 */

// ==========================================
// CONFIGURAÇÕES GLOBAIS
// ==========================================

window.VirtuaVenda = {
    // Configurações carregadas do PHP
    config: window.storeConfig || {},
    
    // Estados globais
    cart: JSON.parse(localStorage.getItem('vv_cart')) || [],
    wishlist: JSON.parse(localStorage.getItem('vv_wishlist')) || [],
    
    // Estados da UI
    isCartOpen: false,
    isMobileMenuOpen: false,
    isLoading: false,
    
    // Cache
    cache: new Map(),
    
    // Configurações padrão
    defaults: {
        currency: 'BRL',
        locale: 'pt-BR',
        pageSize: 12,
        animationDuration: 300,
        notificationDuration: 3000
    }
};

// ==========================================
// INICIALIZAÇÃO
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    VirtuaVenda.init();
});

VirtuaVenda.init = function() {
    console.log('🚀 VirtuaVenda Frontend iniciando...');
    
    // Inicializar módulos
    this.initEventListeners();
    this.initComponents();
    this.initCart();
    this.initWishlist();
    this.initAnimations();
    this.initPWA();
    this.initSEO();
    
    console.log('✅ VirtuaVenda Frontend carregado!');
};

// ==========================================
// EVENT LISTENERS GLOBAIS
// ==========================================

VirtuaVenda.initEventListeners = function() {
    // Delegação de eventos para melhor performance
    document.addEventListener('click', this.handleGlobalClick.bind(this));
    document.addEventListener('submit', this.handleGlobalSubmit.bind(this));
    document.addEventListener('input', this.handleGlobalInput.bind(this));
    
    // Eventos de teclado
    document.addEventListener('keydown', this.handleKeyboard.bind(this));
    
    // Eventos de scroll
    window.addEventListener('scroll', this.throttle(this.handleScroll.bind(this), 100));
    
    // Eventos de resize
    window.addEventListener('resize', this.debounce(this.handleResize.bind(this), 250));
    
    // Eventos de rede
    window.addEventListener('online', this.handleOnline.bind(this));
    window.addEventListener('offline', this.handleOffline.bind(this));
    
    // Antes de sair da página
    window.addEventListener('beforeunload', this.handleBeforeUnload.bind(this));
};

VirtuaVenda.handleGlobalClick = function(e) {
    const target = e.target.closest('[data-action]');
    if (!target) return;
    
    const action = target.dataset.action;
    const data = { ...target.dataset };
    
    // Prevenir ação padrão para alguns elementos
    if (['add-to-cart', 'toggle-wishlist', 'toggle-cart', 'toggle-menu'].includes(action)) {
        e.preventDefault();
    }
    
    // Executar ação
    switch (action) {
        case 'add-to-cart':
            this.addToCart(data);
            break;
        case 'remove-from-cart':
            this.removeFromCart(data.productId);
            break;
        case 'update-cart-quantity':
            this.updateCartQuantity(data.productId, parseInt(data.quantity));
            break;
        case 'toggle-wishlist':
            this.toggleWishlist(data.productId);
            break;
        case 'toggle-cart':
            this.toggleCart();
            break;
        case 'toggle-mobile-menu':
            this.toggleMobileMenu();
            break;
        case 'toggle-mobile-categories':
            this.toggleMobileCategories();
            break;
        case 'toggle-user-menu':
            this.toggleUserMenu();
            break;
        case 'close-modal':
            this.closeModal(target.closest('.modal'));
            break;
        case 'quick-view':
            this.quickViewProduct(data.productId);
            break;
        case 'share-product':
            this.shareProduct(data);
            break;
        default:
            console.warn('Ação não reconhecida:', action);
    }
};

VirtuaVenda.handleGlobalSubmit = function(e) {
    const form = e.target;
    const action = form.dataset.action;
    
    if (!action) return;
    
    e.preventDefault();
    
    switch (action) {
        case 'newsletter':
            this.submitNewsletter(form);
            break;
        case 'contact':
            this.submitContact(form);
            break;
        case 'search':
            this.submitSearch(form);
            break;
        case 'login':
            this.submitLogin(form);
            break;
        case 'register':
            this.submitRegister(form);
            break;
        case 'checkout':
            this.submitCheckout(form);
            break;
        default:
            console.warn('Ação de formulário não reconhecida:', action);
    }
};

VirtuaVenda.handleGlobalInput = function(e) {
    const input = e.target;
    
    // Busca em tempo real
    if (input.dataset.search === 'realtime') {
        this.debounce(this.performRealtimeSearch.bind(this), 300)(input.value);
    }
    
    // Validação em tempo real
    if (input.dataset.validate) {
        this.validateField(input);
    }
    
    // Formatação automática
    if (input.dataset.format) {
        this.formatField(input);
    }
};

VirtuaVenda.handleKeyboard = function(e) {
    // ESC para fechar modais/menus
    if (e.key === 'Escape') {
        this.closeAllModals();
        this.closeMobileMenu();
        this.closeCart();
    }
    
    // Enter para busca rápida
    if (e.key === 'Enter' && e.target.matches('[data-search]')) {
        e.preventDefault();
        this.performSearch(e.target.value);
    }
};

VirtuaVenda.handleScroll = function() {
    const scrollY = window.scrollY;
    
    // Header fixo com efeito
    const header = document.querySelector('header');
    if (header) {
        header.classList.toggle('scrolled', scrollY > 100);
    }
    
    // Lazy loading de imagens
    this.lazyLoadImages();
    
    // Infinite scroll (se habilitado)
    if (this.isInfiniteScrollEnabled()) {
        this.checkInfiniteScroll();
    }
    
    // Scroll to top button
    const scrollTopBtn = document.querySelector('.scroll-to-top');
    if (scrollTopBtn) {
        scrollTopBtn.classList.toggle('visible', scrollY > 500);
    }
};

VirtuaVenda.handleResize = function() {
    // Fechar menus mobile em resize
    if (window.innerWidth > 768) {
        this.closeMobileMenu();
    }
    
    // Recalcular layouts se necessário
    this.recalculateLayouts();
};

VirtuaVenda.handleOnline = function() {
    this.showNotification('Conexão reestabelecida!', 'success');
    this.syncOfflineData();
};

VirtuaVenda.handleOffline = function() {
    this.showNotification('Você está offline. Algumas funcionalidades podem estar limitadas.', 'warning');
};

VirtuaVenda.handleBeforeUnload = function(e) {
    // Salvar dados importantes
    this.saveCartToStorage();
    this.saveWishlistToStorage();
    
    // Avisar sobre formulários não salvos
    const unsavedForms = document.querySelectorAll('form[data-unsaved="true"]');
    if (unsavedForms.length > 0) {
        e.preventDefault();
        e.returnValue = 'Você tem alterações não salvas. Deseja realmente sair?';
        return e.returnValue;
    }
};

// ==========================================
// GERENCIAMENTO DO CARRINHO
// ==========================================

VirtuaVenda.initCart = function() {
    this.updateCartDisplay();
    this.bindCartEvents();
};

VirtuaVenda.addToCart = function(productData) {
    // Validar dados do produto
    if (!this.validateProductData(productData)) {
        this.showNotification('Erro: dados do produto inválidos', 'error');
        return false;
    }
    
    const product = {
        id: productData.productId,
        name: productData.productName,
        price: parseFloat(productData.productPrice),
        image: productData.productImage,
        variations: productData.variations ? JSON.parse(productData.variations) : {},
        quantity: parseInt(productData.quantity) || 1
    };
    
    // Verificar se produto já existe no carrinho
    const existingItemIndex = this.cart.findIndex(item => 
        item.id === product.id && 
        JSON.stringify(item.variations) === JSON.stringify(product.variations)
    );
    
    if (existingItemIndex > -1) {
        this.cart[existingItemIndex].quantity += product.quantity;
    } else {
        this.cart.push(product);
    }
    
    this.saveCartToStorage();
    this.updateCartDisplay();
    this.showNotification(`${product.name} adicionado ao carrinho!`, 'success');
    
    // Abrir carrinho se não estiver aberto
    if (!this.isCartOpen) {
        setTimeout(() => this.toggleCart(), 500);
    }
    
    // Analytics
    this.trackEvent('add_to_cart', {
        item_id: product.id,
        item_name: product.name,
        value: product.price,
        quantity: product.quantity
    });
    
    return true;
};

VirtuaVenda.removeFromCart = function(productId, variations = {}) {
    const initialLength = this.cart.length;
    
    this.cart = this.cart.filter(item => 
        !(item.id === productId && JSON.stringify(item.variations) === JSON.stringify(variations))
    );
    
    if (this.cart.length < initialLength) {
        this.saveCartToStorage();
        this.updateCartDisplay();
        this.showNotification('Produto removido do carrinho', 'info');
        
        // Analytics
        this.trackEvent('remove_from_cart', { item_id: productId });
    }
};

VirtuaVenda.updateCartQuantity = function(productId, newQuantity, variations = {}) {
    if (newQuantity <= 0) {
        this.removeFromCart(productId, variations);
        return;
    }
    
    const item = this.cart.find(item => 
        item.id === productId && 
        JSON.stringify(item.variations) === JSON.stringify(variations)
    );
    
    if (item) {
        item.quantity = newQuantity;
        this.saveCartToStorage();
        this.updateCartDisplay();
    }
};

VirtuaVenda.clearCart = function() {
    this.cart = [];
    this.saveCartToStorage();
    this.updateCartDisplay();
    this.showNotification('Carrinho limpo', 'info');
};

VirtuaVenda.getCartTotal = function() {
    return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
};

VirtuaVenda.getCartItemCount = function() {
    return this.cart.reduce((count, item) => count + item.quantity, 0);
};

VirtuaVenda.updateCartDisplay = function() {
    const cartCount = document.getElementById('cart-count');
    const cartCountSidebar = document.getElementById('cart-count-sidebar');
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    
    const itemCount = this.getCartItemCount();
    const total = this.getCartTotal();
    
    // Atualizar contadores
    if (cartCount) {
        cartCount.textContent = itemCount;
        cartCount.classList.toggle('hidden', itemCount === 0);
    }
    
    if (cartCountSidebar) {
        cartCountSidebar.textContent = itemCount;
    }
    
    // Atualizar total
    if (cartTotal) {
        cartTotal.textContent = this.formatCurrency(total);
    }
    
    // Atualizar lista de itens
    if (cartItems) {
        if (this.cart.length === 0) {
            cartItems.innerHTML = this.getEmptyCartHTML();
        } else {
            cartItems.innerHTML = this.cart.map(item => this.getCartItemHTML(item)).join('');
        }
    }
    
    // Atualizar badge no favicon (se suportado)
    this.updateFaviconBadge(itemCount);
};

VirtuaVenda.getEmptyCartHTML = function() {
    return `
        <div class="empty-cart text-center text-gray-500 mt-8 p-6">
            <i class="fas fa-shopping-cart text-4xl mb-4 opacity-50"></i>
            <p class="text-lg mb-2">Seu carrinho está vazio</p>
            <p class="text-sm text-gray-400 mb-4">Adicione produtos e eles aparecerão aqui</p>
            <a href="/produtos.php" class="inline-block bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700 transition-colors">
                Continuar Comprando
            </a>
        </div>
    `;
};

VirtuaVenda.getCartItemHTML = function(item) {
    const variationsText = Object.keys(item.variations).length > 0 
        ? `<p class="text-xs text-gray-500">${this.formatVariations(item.variations)}</p>`
        : '';
    
    return `
        <div class="cart-item flex items-center gap-3 mb-4 p-3 border border-gray-200 rounded-lg bg-white">
            <div class="item-image flex-shrink-0">
                <img src="${item.image}" alt="${item.name}" class="w-16 h-16 object-cover rounded-md">
            </div>
            <div class="item-details flex-1 min-w-0">
                <h4 class="font-semibold text-sm text-gray-800 truncate">${item.name}</h4>
                ${variationsText}
                <p class="text-primary-600 font-bold">${this.formatCurrency(item.price)}</p>
                <div class="quantity-controls flex items-center gap-2 mt-2">
                    <button 
                        data-action="update-cart-quantity" 
                        data-product-id="${item.id}" 
                        data-quantity="${item.quantity - 1}"
                        class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded text-sm transition-colors ${item.quantity <= 1 ? 'opacity-50 cursor-not-allowed' : ''}"
                        ${item.quantity <= 1 ? 'disabled' : ''}>
                        <i class="fas fa-minus"></i>
                    </button>
                    <span class="quantity text-sm font-medium w-8 text-center">${item.quantity}</span>
                    <button 
                        data-action="update-cart-quantity" 
                        data-product-id="${item.id}" 
                        data-quantity="${item.quantity + 1}"
                        class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded text-sm transition-colors">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="item-actions flex flex-col items-end gap-2">
                <button 
                    data-action="remove-from-cart" 
                    data-product-id="${item.id}"
                    class="text-red-500 hover:text-red-700 transition-colors p-1"
                    title="Remover item">
                    <i class="fas fa-trash text-sm"></i>
                </button>
                <p class="text-sm font-bold text-gray-800">${this.formatCurrency(item.price * item.quantity)}</p>
            </div>
        </div>
    `;
};

VirtuaVenda.saveCartToStorage = function() {
    try {
        localStorage.setItem('vv_cart', JSON.stringify(this.cart));
        localStorage.setItem('vv_cart_updated', Date.now().toString());
    } catch (e) {
        console.warn('Erro ao salvar carrinho:', e);
    }
};

VirtuaVenda.bindCartEvents = function() {
    // Eventos específicos do carrinho que precisam de tratamento especial
    document.addEventListener('change', (e) => {
        if (e.target.matches('.cart-item input[type="number"]')) {
            const productId = e.target.dataset.productId;
            const newQuantity = parseInt(e.target.value) || 1;
            this.updateCartQuantity(productId, newQuantity);
        }
    });
};

// ==========================================
// GERENCIAMENTO DA WISHLIST
// ==========================================

VirtuaVenda.initWishlist = function() {
    this.updateWishlistDisplay();
};

VirtuaVenda.toggleWishlist = function(productId) {
    const index = this.wishlist.indexOf(productId);
    
    if (index > -1) {
        this.wishlist.splice(index, 1);
        this.showNotification('Produto removido da lista de desejos', 'info');
        this.trackEvent('remove_from_wishlist', { item_id: productId });
    } else {
        this.wishlist.push(productId);
        this.showNotification('Produto adicionado à lista de desejos!', 'success');
        this.trackEvent('add_to_wishlist', { item_id: productId });
    }
    
    this.saveWishlistToStorage();
    this.updateWishlistDisplay();
};

VirtuaVenda.updateWishlistDisplay = function() {
    const wishlistCount = document.querySelector('.wishlist-count');
    
    // Atualizar contador
    if (wishlistCount) {
        if (this.wishlist.length > 0) {
            wishlistCount.textContent = this.wishlist.length;
            wishlistCount.classList.remove('hidden');
        } else {
            wishlistCount.classList.add('hidden');
        }
    }
    
    // Atualizar botões de wishlist
    document.querySelectorAll('[data-action="toggle-wishlist"]').forEach(btn => {
        const productId = btn.dataset.productId;
        const icon = btn.querySelector('i');
        const isInWishlist = this.wishlist.includes(productId);
        
        if (icon) {
            icon.className = isInWishlist 
                ? 'fas fa-heart text-red-500' 
                : 'far fa-heart';
        }
        
        btn.classList.toggle('active', isInWishlist);
        btn.title = isInWishlist 
            ? 'Remover da lista de desejos' 
            : 'Adicionar à lista de desejos';
    });
};

VirtuaVenda.saveWishlistToStorage = function() {
    try {
        localStorage.setItem('vv_wishlist', JSON.stringify(this.wishlist));
    } catch (e) {
        console.warn('Erro ao salvar wishlist:', e);
    }
};

// ==========================================
// INTERFACE E NAVEGAÇÃO
// ==========================================

VirtuaVenda.toggleCart = function() {
    const cartSidebar = document.getElementById('cart-sidebar');
    const cartOverlay = document.getElementById('cart-overlay');
    
    if (!cartSidebar) return;
    
    this.isCartOpen = !this.isCartOpen;
    
    if (this.isCartOpen) {
        cartSidebar.classList.remove('translate-x-full');
        if (cartOverlay) cartOverlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Focus no primeiro elemento focável
        const firstFocusable = cartSidebar.querySelector('button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (firstFocusable) firstFocusable.focus();
    } else {
        this.closeCart();
    }
};

VirtuaVenda.closeCart = function() {
    const cartSidebar = document.getElementById('cart-sidebar');
    const cartOverlay = document.getElementById('cart-overlay');
    
    if (cartSidebar) cartSidebar.classList.add('translate-x-full');
    if (cartOverlay) cartOverlay.classList.add('hidden');
    document.body.style.overflow = '';
    this.isCartOpen = false;
};

VirtuaVenda.toggleMobileMenu = function() {
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileOverlay = document.getElementById('mobile-overlay');
    
    if (!mobileMenu) return;
    
    this.isMobileMenuOpen = !this.isMobileMenuOpen;
    
    if (this.isMobileMenuOpen) {
        mobileMenu.classList.add('open');
        if (mobileOverlay) mobileOverlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    } else {
        this.closeMobileMenu();
    }
};

VirtuaVenda.closeMobileMenu = function() {
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileOverlay = document.getElementById('mobile-overlay');
    
    if (mobileMenu) mobileMenu.classList.remove('open');
    if (mobileOverlay) mobileOverlay.classList.add('hidden');
    document.body.style.overflow = '';
    this.isMobileMenuOpen = false;
};

VirtuaVenda.toggleMobileCategories = function() {
    const mobileCategories = document.getElementById('mobile-categories');
    const button = mobileCategories?.previousElementSibling;
    
    if (!mobileCategories || !button) return;
    
    const icon = button.querySelector('i');
    const isOpen = !mobileCategories.classList.contains('hidden');
    
    mobileCategories.classList.toggle('hidden');
    
    if (icon) {
        icon.classList.toggle('fa-chevron-down', isOpen);
        icon.classList.toggle('fa-chevron-up', !isOpen);
    }
};

VirtuaVenda.toggleUserMenu = function() {
    const userMenu = document.getElementById('user-menu');
    if (userMenu) {
        userMenu.classList.toggle('hidden');
    }
};

// ==========================================
// SYSTEM DE NOTIFICAÇÕES
// ==========================================

VirtuaVenda.showNotification = function(message, type = 'info', duration = null) {
    const notification = document.createElement('div');
    const id = 'notification-' + Date.now();
    
    notification.id = id;
    notification.className = `
        fixed top-4 right-4 z-50 p-4 rounded-lg text-white font-medium 
        transform translate-x-full transition-transform duration-300 shadow-lg
        ${this.getNotificationClasses(type)}
    `.replace(/\s+/g, ' ').trim();
    
    notification.innerHTML = `
        <div class="flex items-center gap-3">
            <i class="${this.getNotificationIcon(type)}"></i>
            <span class="flex-1">${message}</span>
            <button onclick="VirtuaVenda.closeNotification('${id}')" class="opacity-70 hover:opacity-100">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Mostrar notificação
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Auto-remover
    const autoRemoveTime = duration || this.defaults.notificationDuration;
    setTimeout(() => {
        this.closeNotification(id);
    }, autoRemoveTime);
    
    return id;
};

VirtuaVenda.closeNotification = function(id) {
    const notification = document.getElementById(id);
    if (!notification) return;
    
    notification.classList.add('translate-x-full');
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 300);
};

VirtuaVenda.getNotificationClasses = function(type) {
    const classes = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500',
        primary: 'bg-primary-500'
    };
    return classes[type] || classes.info;
};

VirtuaVenda.getNotificationIcon = function(type) {
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle',
        primary: 'fas fa-bell'
    };
    return icons[type] || icons.info;
};

// ==========================================
// UTILITÁRIOS
// ==========================================

VirtuaVenda.formatCurrency = function(value) {
    return new Intl.NumberFormat(this.defaults.locale, {
        style: 'currency',
        currency: this.defaults.currency
    }).format(value);
};

VirtuaVenda.formatVariations = function(variations) {
    return Object.entries(variations)
        .map(([key, value]) => `${key}: ${value}`)
        .join(', ');
};

VirtuaVenda.validateProductData = function(data) {
    return data.productId && data.productName && data.productPrice && !isNaN(data.productPrice);
};

VirtuaVenda.debounce = function(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

VirtuaVenda.throttle = function(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
};

VirtuaVenda.trackEvent = function(eventName, parameters = {}) {
    // Google Analytics 4
    if (typeof gtag !== 'undefined') {
        gtag('event', eventName, parameters);
    }
    
    // Facebook Pixel
    if (typeof fbq !== 'undefined') {
        fbq('track', eventName, parameters);
    }
    
    // Console log para desenvolvimento
    if (this.config.debug) {
        console.log('📊 Event tracked:', eventName, parameters);
    }
};

// ==========================================
// MÓDULOS ADICIONAIS (Para implementação futura)
// ==========================================

VirtuaVenda.initComponents = function() {
    // Inicializar componentes específicos
    console.log('🧩 Componentes inicializados');
};

VirtuaVenda.initAnimations = function() {
    // Animações e efeitos visuais
    console.log('✨ Animações inicializadas');
};

VirtuaVenda.initPWA = function() {
    // Progressive Web App features
    console.log('📱 PWA inicializado');
};

VirtuaVenda.initSEO = function() {
    // SEO e meta tags dinâmicas
    console.log('🔍 SEO inicializado');
};

VirtuaVenda.lazyLoadImages = function() {
    // Lazy loading de imagens
};

VirtuaVenda.updateFaviconBadge = function(count) {
    // Atualizar badge no favicon (se suportado)
};

// ==========================================
// PLACEHOLDERS PARA IMPLEMENTAÇÃO FUTURA
// ==========================================

VirtuaVenda.submitNewsletter = function(form) { console.log('Newsletter:', form); };
VirtuaVenda.submitContact = function(form) { console.log('Contact:', form); };
VirtuaVenda.submitSearch = function(form) { console.log('Search:', form); };
VirtuaVenda.performRealtimeSearch = function(query) { console.log('Realtime search:', query); };
VirtuaVenda.performSearch = function(query) { console.log('Search:', query); };
VirtuaVenda.validateField = function(field) { console.log('Validate:', field); };
VirtuaVenda.formatField = function(field) { console.log('Format:', field); };
VirtuaVenda.closeAllModals = function() { console.log('Close modals'); };
VirtuaVenda.closeModal = function(modal) { console.log('Close modal:', modal); };
VirtuaVenda.quickViewProduct = function(id) { console.log('Quick view:', id); };
VirtuaVenda.shareProduct = function(data) { console.log('Share:', data); };
VirtuaVenda.isInfiniteScrollEnabled = function() { return false; };
VirtuaVenda.checkInfiniteScroll = function() { };
VirtuaVenda.recalculateLayouts = function() { };
VirtuaVenda.syncOfflineData = function() { };

// Expor globalmente para debug
window.VV = VirtuaVenda;