// Cashier POS System JavaScript

class CashierPOS {
    constructor() {
        this.cart = [];
        this.selectedCategory = 0;
        this.taxRate = 0.10; // 10% tax
        this.init();
    }

    async init() {
        this.attachEventListeners();
        await this.loadCategories();
        await this.loadProducts();
    }

    attachEventListeners() {
        document.getElementById('categoryTabs').addEventListener('click', (e) => {
            if (e.target.classList.contains('category-tab')) {
                this.selectCategory(e.target);
            }
        });

        document.getElementById('checkoutBtn').addEventListener('click', () => this.checkout());
        document.getElementById('clearCartBtn').addEventListener('click', () => this.clearCart());
    }

    async loadCategories() {
        try {
            const response = await fetch('/api/get_categories.php');
            const data = await response.json();
            
            if (!data.success) throw new Error(data.error);
            
            // Categories are already rendered server-side, just ensure functionality
            return data.categories;
        } catch (error) {
            console.error('Error loading categories:', error);
        }
    }

    async loadProducts(categoryId = 0) {
        try {
            const url = categoryId > 0 
                ? `../api/get_products.php?category_id=${categoryId}`
                : '../api/get_products.php';
            
            const response = await fetch(url);
            const data = await response.json();
            
            if (!data.success) throw new Error(data.error);
            
            this.renderProducts(data.products);
        } catch (error) {
            console.error('Error loading products:', error);
            document.getElementById('productsContainer').innerHTML = 
                '<p class="alert alert-error">Error loading products</p>';
        }
    }

    renderProducts(products) {
        const container = document.getElementById('productsContainer');
        
        if (products.length === 0) {
            container.innerHTML = '<p class="alert alert-info">No products available in this category</p>';
            return;
        }

        container.innerHTML = products.map(product => `
            <div class="product-card" onclick="pos.addToCart(${product.id}, '${this.escapeHtml(product.name)}', ${product.price})">
                <img src="${product.image_url || '../images/default-product.svg'}" alt="${this.escapeHtml(product.name)}" class="product-image" onerror="this.src='../images/default-product.svg'">
                <div class="product-details">
                    <div class="product-name" title="${this.escapeHtml(product.name)}">${this.escapeHtml(product.name)}</div>
                    <div class="product-price">${this.formatCurrency(product.price)}</div>
                </div>
            </div>
        `).join('');
    }

    selectCategory(tab) {
        // Update active tab
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        // Load products for category
        const categoryId = parseInt(tab.getAttribute('data-category-id'));
        this.selectedCategory = categoryId;
        this.loadProducts(categoryId);
    }

    addToCart(productId, productName, price) {
        const existingItem = this.cart.find(item => item.id === productId);

        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            this.cart.push({
                id: productId,
                name: productName,
                price: price,
                quantity: 1
            });
        }

        this.updateCartDisplay();
    }

    removeFromCart(productId) {
        this.cart = this.cart.filter(item => item.id !== productId);
        this.updateCartDisplay();
    }

    updateQuantity(productId, quantity) {
        const item = this.cart.find(item => item.id === productId);
        if (item) {
            if (quantity <= 0) {
                this.removeFromCart(productId);
            } else {
                item.quantity = quantity;
            }
            this.updateCartDisplay();
        }
    }

    updateCartDisplay() {
        const cartContent = document.getElementById('cartContent');

        if (this.cart.length === 0) {
            cartContent.innerHTML = '<div class="cart-empty">No items in cart</div>';
            document.getElementById('checkoutBtn').disabled = true;
        } else {
            cartContent.innerHTML = this.cart.map(item => `
                <div class="cart-item">
                    <div>
                        <div class="cart-item-name">${this.escapeHtml(item.name)}</div>
                        <div class="cart-item-controls">
                            <button class="qty-btn" onclick="pos.updateQuantity(${item.id}, ${item.quantity - 1})">−</button>
                            <div class="qty-display">${item.quantity}</div>
                            <button class="qty-btn" onclick="pos.updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary);">
                            ${this.formatCurrency(item.price)} each
                        </div>
                    </div>
                    <div class="cart-item-price">${this.formatCurrency(item.price * item.quantity)}</div>
                    <button class="btn btn-sm btn-danger" onclick="pos.removeFromCart(${item.id})" style="margin-left: 0.5rem;">×</button>
                </div>
            `).join('');
            document.getElementById('checkoutBtn').disabled = false;
        }

        this.updateTotals();
    }

    updateTotals() {
        const subtotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const tax = subtotal * this.taxRate;
        const total = subtotal + tax;

        document.getElementById('subtotalAmount').textContent = this.formatCurrency(subtotal);
        document.getElementById('taxAmount').textContent = this.formatCurrency(tax);
        document.getElementById('totalAmount').textContent = this.formatCurrency(total);
    }

    async checkout() {
        if (this.cart.length === 0) {
            alert('Please add items to the cart first');
            return;
        }

        // Store cart in session storage for checkout page
        sessionStorage.setItem('cartItems', JSON.stringify(this.cart));
        sessionStorage.setItem('cartSubtotal', (this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0)).toString());
        sessionStorage.setItem('cartTax', (this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0) * this.taxRate).toString());
        
        window.location.href = 'checkout.php';
    }

    clearCart() {
        if (confirm('Are you sure you want to clear the entire cart?')) {
            this.cart = [];
            this.updateCartDisplay();
        }
    }

    formatCurrency(amount) {
        return '$' + parseFloat(amount).toFixed(2);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize POS system when page loads
let pos;
document.addEventListener('DOMContentLoaded', () => {
    pos = new CashierPOS();
});
