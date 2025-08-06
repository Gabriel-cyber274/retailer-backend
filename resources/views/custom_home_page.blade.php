<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->shop_name ?? 'Shop' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-card {
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .cart-slide {
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }

        .cart-slide.open {
            transform: translateX(0);
        }

        .backdrop {
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .backdrop.show {
            opacity: 1;
            visibility: visible;
        }

        /* Custom Bootstrap Modal Styling */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-title {
            font-weight: 700;
            color: #1f2937;
        }

        .btn-close {
            box-shadow: none;
        }

        /* Search and Filter Styling */
        .search-filter-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
        }

        .search-input {
            border: none;
            border-radius: 12px;
            padding: 12px 20px;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .search-input:focus {
            outline: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .filter-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 1.5rem;
            color: #374151;
        }

        .price-range-input {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px 12px;
            transition: border-color 0.3s ease;
        }

        .price-range-input:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f4f6;
            border-top: 2px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }
    </style>
</head>

<body class="bg-gray-50">
    <h1 style="display: none" id="user_id">{{ $user->id }}</h1>
    <!-- Header -->
    <header class="bg-white shadow-lg sticky top-0 z-40">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold text-gray-800">{{ $user->shop_name ?? 'Shop' }}</h1>
                    <span class="text-sm text-gray-500">by {{ $user->name }}</span>
                </div>
                <button onclick="toggleCart()"
                    class="relative bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    Cart (<span id="cart-count">0</span>)
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Search and Filter Section -->
        <div class="search-filter-section">
            <div class="row g-4 align-items-end">
                <div class="col-lg-6">
                    <h3 class="text-white mb-3">
                        <i class="fas fa-search me-2"></i>Find Products
                    </h3>
                    <div class="position-relative">
                        <input type="text" id="search-input" class="form-control search-input"
                            placeholder="Search products..." onkeyup="handleSearch(event)">
                        <div class="loading-spinner position-absolute" id="search-spinner"
                            style="top: 50%; right: 20px; transform: translateY(-50%);"></div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="filter-card">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-filter me-2"></i>Price Range
                        </h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label small">Min Price (₦)</label>
                                <input type="number" id="min-price" class="form-control price-range-input"
                                    placeholder="0" min="0" onchange="applyPriceFilter()">
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Max Price (₦)</label>
                                <input type="number" id="max-price" class="form-control price-range-input"
                                    placeholder="∞" min="0" onchange="applyPriceFilter()">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button onclick="clearFilters()" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i>Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Our Products</h2>
            <p class="text-gray-600">Discover our amazing collection of products</p>
            <div id="results-info" class="text-sm text-gray-500 mt-2"></div>
        </div>

        <!-- Products Grid -->
        <div id="products-container">
            @if ($user->retails->count() > 0)
                <div id="products-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach ($user->retails as $retail)
                        @php
                            $finalPrice = $retail->product->price + $retail->gain;
                        @endphp
                        <div class="product-card bg-white rounded-xl shadow-md overflow-hidden"
                            data-product-name="{{ strtolower($retail->product->name) }}"
                            data-product-price="{{ $finalPrice }}"
                            data-product-brand="{{ strtolower($retail->product->brand_name ?? '') }}"
                            data-product-description="{{ strtolower($retail->product->description) }}">
                            <div class="relative">
                                @if ($retail->product->product_image)
                                    <img src="{{ $retail->product->product_image }}" alt="{{ $retail->product->name }}"
                                        class="w-full h-48 object-cover">
                                @else
                                    <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                        <i class="fas fa-image text-4xl text-gray-400"></i>
                                    </div>
                                @endif
                                @if (!$retail->product->in_stock)
                                    <div class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-xs">
                                        Out of Stock
                                    </div>
                                @endif
                            </div>

                            <div class="p-4">
                                <h3 class="font-semibold text-lg text-gray-800 mb-2 line-clamp-2">
                                    {{ $retail->product->name }}</h3>
                                <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ $retail->product->description }}
                                </p>

                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <span
                                            class="text-2xl font-bold text-blue-600">₦{{ number_format($finalPrice, 2) }}</span>
                                    </div>
                                    @if ($retail->product->brand_name)
                                        <span
                                            class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $retail->product->brand_name }}</span>
                                    @endif
                                </div>

                                <div class="flex space-x-2">
                                    <button onclick="viewProduct({{ json_encode($retail) }})" data-bs-toggle="modal"
                                        data-bs-target="#productModal"
                                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 px-3 rounded-lg transition-colors text-sm">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </button>
                                    @if ($retail->product->in_stock)
                                        <button onclick="addToCart({{ json_encode($retail) }})"
                                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-3 rounded-lg transition-colors text-sm">
                                            <i class="fas fa-plus mr-1"></i> Add
                                        </button>
                                    @else
                                        <button disabled
                                            class="flex-1 bg-gray-300 text-gray-500 py-2 px-3 rounded-lg text-sm cursor-not-allowed">
                                            <i class="fas fa-ban mr-1"></i> Unavailable
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-16">
                    <i class="fas fa-store text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No products available</h3>
                    <p class="text-gray-500">This shop doesn't have any products yet.</p>
                </div>
            @endif
        </div>

        <!-- No Results Message -->
        <div id="no-results" class="no-results" style="display: none;">
            <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No products found</h3>
            <p class="text-gray-500">Try adjusting your search terms or price range.</p>
        </div>
    </main>

    <!-- Bootstrap Product Modal -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-4" id="productModalLabel">Product Details</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modal-content">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-4" id="checkoutModalLabel">
                        <i class="fas fa-credit-card me-2"></i>Checkout Information
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="checkoutForm" novalidate>
                        <div class="row g-4">
                            <!-- Order Summary -->
                            <div class="col-12">
                                <div class="bg-light p-3 rounded mb-4">
                                    <h6 class="fw-semibold mb-3">Order Summary</h6>
                                    <div id="checkout-items-list">
                                        <!-- Items will be populated by JavaScript -->
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Dispatch Fee:</span>
                                        <span id="dispatch-total">₦0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Total:</span>
                                        <span id="checkout-total">₦0.00</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer Information -->
                            <div class="col-12">
                                <h6 class="fw-semibold mb-3">Customer Information</h6>
                            </div>

                            <div class="col-md-6">
                                <label for="customerName" class="form-label">Full Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="customerName" required>
                                <div class="invalid-feedback">
                                    Please provide your full name.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="customerEmail" class="form-label">Email Address <span
                                        class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="customerEmail" required>
                                <div class="invalid-feedback">
                                    Please provide a valid email address.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="customerPhone" class="form-label">Phone Number <span
                                        class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="customerPhone" required>
                                <div class="invalid-feedback">
                                    Please provide your phone number.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="customerCity" class="form-label">City</label>
                                <input type="text" class="form-control" id="customerCity">
                            </div>

                            <div class="col-12">
                                <label for="customerAddress" class="form-label">Delivery Address <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" id="customerAddress" rows="3" required
                                    placeholder="Enter your complete delivery address"></textarea>
                                <div class="invalid-feedback">
                                    Please provide your delivery address.
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="orderNotes" class="form-label">Order Notes (Optional)</label>
                                <textarea class="form-control" id="orderNotes" rows="2"
                                    placeholder="Any special instructions for your order..."></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-success btn-lg" onclick="processOrder()">
                        <i class="fas fa-check me-2"></i>Place Order
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Sidebar -->
    <div id="cart-sidebar" class="cart-slide fixed right-0 top-0 h-full w-96 bg-white shadow-xl z-50 flex flex-col">
        <div class="p-4 border-b">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold">Shopping Cart</h3>
                <button onclick="toggleCart()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <div id="cart-items" class="flex-1 overflow-y-auto p-4">
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-shopping-cart text-4xl mb-4"></i>
                <p>Your cart is empty</p>
            </div>
        </div>

        <div id="cart-footer" class="p-4 border-t bg-gray-50" style="display: none;">
            <div class="mb-4">
                <div class="flex justify-between items-center text-lg font-semibold">
                    <span>Total:</span>
                    <span id="cart-total">₦0.00</span>
                </div>
            </div>
            <button onclick="checkout()"
                class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold transition-colors">
                <i class="fas fa-credit-card mr-2"></i> Checkout
            </button>
        </div>
    </div>

    <!-- Cart Backdrop -->
    <div id="cart-backdrop" style="background-color: rgb(0 0 0 / 70%) !important"
        class="backdrop fixed inset-0 bg-black bg-opacity-30 z-40" onclick="toggleCart()"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>
        let cart = [];
        let isCartOpen = false;
        let currentRetail = null;
        let originalProducts = [];
        let searchTimeout = null;
        let currentSearchTerm = '';
        let isSearching = false;

        const dispatchFee = 5000;


        const user_id = document.getElementById('user_id');



        // Store original products for filtering
        document.addEventListener('DOMContentLoaded', function() {
            const productCards = document.querySelectorAll('.product-card');
            productCards.forEach(card => {
                originalProducts.push({
                    element: card,
                    name: card.dataset.productName,
                    price: parseFloat(card.dataset.productPrice),
                    brand: card.dataset.productBrand,
                    description: card.dataset.productDescription
                });
            });
            updateResultsInfo();
        });

        // Search functionality
        function handleSearch(event) {
            const searchTerm = event.target.value.trim();

            if (event.key === 'Enter') {
                performSearch(searchTerm);
                return;
            }

            // Clear previous timeout
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }

            // Debounce search
            searchTimeout = setTimeout(() => {
                if (searchTerm !== currentSearchTerm) {
                    performSearch(searchTerm);
                }
            }, 500);
        }

        async function performSearch(searchTerm) {
            currentSearchTerm = searchTerm;
            const spinner = document.getElementById('search-spinner');
            const productsGrid = document.getElementById('products-grid');
            const noResults = document.getElementById('no-results');

            if (searchTerm === '') {
                // Show all products if search is empty
                showAllProducts();
                applyPriceFilter();
                return;
            }

            isSearching = true;
            spinner.style.display = 'block';


            performClientSideSearch(searchTerm);

            spinner.style.display = 'none';
            isSearching = false;
            updateResultsInfo();

        }

        function displaySearchResults(products) {
            const productsGrid = document.getElementById('products-grid');
            const noResults = document.getElementById('no-results');
            const productsContainer = document.getElementById('products-container');

            if (products.length === 0) {
                showNoResults();
                return;
            }

            // Hide original products
            const originalCards = document.querySelectorAll('.product-card');
            originalCards.forEach(card => card.style.display = 'none');

            // Clear and show search results
            productsGrid.innerHTML = '';
            noResults.style.display = 'none';
            productsContainer.style.display = 'block';

            products.forEach(retail => {
                const finalPrice = parseFloat(retail.product.price) + parseFloat(retail.gain);
                const productCard = createProductCard(retail, finalPrice);
                productsGrid.appendChild(productCard);
            });

            // Apply price filter to search results
            applyPriceFilter();
        }

        function createProductCard(retail, finalPrice) {
            const card = document.createElement('div');
            card.className = 'product-card bg-white rounded-xl shadow-md overflow-hidden';
            card.dataset.productPrice = finalPrice;

            card.innerHTML = `
                <div class="relative">
                    ${retail.product.product_image ? 
                        `<img src="${retail.product.product_image}" alt="${retail.product.name}" class="w-full h-48 object-cover">` :
                        `<div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                                                                                                                                                                                                                                                    <i class="fas fa-image text-4xl text-gray-400"></i>
                                                                                                                                                                                                                                                                </div>`
                    }
                    ${!retail.product.in_stock ? 
                        `<div class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-xs">Out of Stock</div>` : ''
                    }
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-lg text-gray-800 mb-2 line-clamp-2">${retail.product.name}</h3>
                    <p class="text-gray-600 text-sm mb-3 line-clamp-2">${retail.product.description}</p>
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <span class="text-2xl font-bold text-blue-600">₦${finalPrice.toLocaleString()}</span>
                        </div>
                        ${retail.product.brand_name ? 
                            `<span class="text-xs bg-gray-100 px-2 py-1 rounded">${retail.product.brand_name}</span>` : ''
                        }
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="viewProduct(${JSON.stringify(retail).replace(/"/g, '&quot;')})" data-bs-toggle="modal" data-bs-target="#productModal"
                            class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 px-3 rounded-lg transition-colors text-sm">
                            <i class="fas fa-eye mr-1"></i> View
                        </button>
                        ${retail.product.in_stock ?
                            `<button onclick="addToCart(${JSON.stringify(retail).replace(/"/g, '&quot;')})"
                                                                                                                                                                                                                                                                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-3 rounded-lg transition-colors text-sm">
                                                                                                                                                                                                                                                                        <i class="fas fa-plus mr-1"></i> Add
                                                                                                                                                                                                                                                                    </button>` :
                            `<button disabled class="flex-1 bg-gray-300 text-gray-500 py-2 px-3 rounded-lg text-sm cursor-not-allowed">
                                                                                                                                                                                                                                                                        <i class="fas fa-ban mr-1"></i> Unavailable
                                                                                                                                                                                                                                                                    </button>`
                        }
                    </div>
                </div>
            `;

            return card;
        }

        function performClientSideSearch(searchTerm) {
            const searchTermLower = searchTerm.toLowerCase();
            let visibleCount = 0;

            const isMatch = (product, searchTermLower) => {
                return (
                    product.name?.toLowerCase().includes(searchTermLower) ||
                    product.description?.toLowerCase().includes(searchTermLower) ||
                    product.product_code?.toLowerCase().includes(searchTermLower) ||
                    product.brand_name?.toLowerCase().includes(searchTermLower) ||
                    (product.tags || []).some(tag =>
                        tag.name?.toLowerCase().includes(searchTermLower)
                    ) ||
                    (product.categories || []).some(category =>
                        category.name?.toLowerCase().includes(searchTermLower)
                    )
                );
            };

            originalProducts.forEach(product => {
                if (isMatch(product, searchTermLower)) {
                    product.element.style.display = 'block';
                    visibleCount++;
                } else {
                    product.element.style.display = 'none';
                }
            });


            const noResults = document.getElementById('no-results');
            const productsContainer = document.getElementById('products-container');

            if (visibleCount === 0) {
                showNoResults();
            } else {
                noResults.style.display = 'none';
                productsContainer.style.display = 'block';
                applyPriceFilter();
            }
        }

        function showAllProducts() {
            originalProducts.forEach(product => {
                product.element.style.display = 'block';
            });

            // Clear search results if any
            const productsGrid = document.getElementById('products-grid');
            const searchResults = productsGrid.querySelectorAll('.product-card:not([data-product-name])');
            searchResults.forEach(card => card.remove());

            document.getElementById('no-results').style.display = 'none';
            document.getElementById('products-container').style.display = 'block';
        }

        function showNoResults() {
            document.getElementById('no-results').style.display = 'block';
            document.getElementById('products-container').style.display = 'none';
        }

        // Price filter functionality
        function applyPriceFilter() {
            const minPrice = parseFloat(document.getElementById('min-price').value) || 0;
            const maxPrice = parseFloat(document.getElementById('max-price').value) || Infinity;

            const allCards = document.querySelectorAll('.product-card');
            let visibleCount = 0;

            allCards.forEach(card => {
                if (card.style.display === 'none') return; // Skip already hidden cards

                const price = parseFloat(card.dataset.productPrice);

                if (price >= minPrice && price <= maxPrice) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            const noResults = document.getElementById('no-results');
            const productsContainer = document.getElementById('products-container');

            if (visibleCount === 0) {
                showNoResults();
            } else {
                noResults.style.display = 'none';
                productsContainer.style.display = 'block';
            }

            updateResultsInfo();
        }

        function clearFilters() {
            document.getElementById('min-price').value = '';
            document.getElementById('max-price').value = '';
            document.getElementById('search-input').value = '';
            currentSearchTerm = '';

            showAllProducts();
            updateResultsInfo();
        }

        function updateResultsInfo() {
            const resultsInfo = document.getElementById('results-info');
            const visibleCards = document.querySelectorAll(
                '.product-card[style*="block"], .product-card:not([style*="none"])');
            const visibleCount = Array.from(visibleCards).filter(card =>
                card.style.display !== 'none' &&
                card.offsetParent !== null
            ).length;

            const totalCount = originalProducts.length;

            if (currentSearchTerm) {
                resultsInfo.textContent = `Showing ${visibleCount} results for "${currentSearchTerm}"`;
            } else {
                resultsInfo.textContent = `Showing ${visibleCount} of ${totalCount} products`;
            }
        }

        // Cart functionality (keeping original functions)
        function addToCart(retail) {
            const existingItem = cart.find(item => item.id === retail.id);

            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    ...retail,
                    quantity: 1,
                    finalPrice: retail.product.price + retail.gain
                });
            }

            updateCartUI();
            showNotification('Product added to cart!', 'success');
        }

        function removeFromCart(retailId) {
            cart = cart.filter(item => item.id !== retailId);
            updateCartUI();
        }

        function updateQuantity(retailId, change) {
            const item = cart.find(item => item.id === retailId);
            if (item) {
                item.quantity += change;
                if (item.quantity <= 0) {
                    removeFromCart(retailId);
                } else {
                    updateCartUI();
                }
            }
        }

        function updateCartUI() {
            const cartCount = document.getElementById('cart-count');
            const cartItems = document.getElementById('cart-items');
            const cartFooter = document.getElementById('cart-footer');
            const cartTotal = document.getElementById('cart-total');

            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            const totalPrice = cart.reduce((sum, item) => sum + ((parseFloat(item.gain) + parseFloat(item.product.price)) *
                item.quantity), 0);

            cartCount.textContent = totalItems;

            if (cart.length === 0) {
                cartItems.innerHTML = `
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-shopping-cart text-4xl mb-4"></i>
                        <p>Your cart is empty</p>
                    </div>
                `;
                cartFooter.style.display = 'none';
            } else {
                cartItems.innerHTML = cart.map(item => `
                    <div class="flex items-center space-x-3 p-3 border-b">
                        <img src="${item.product.product_image ? item.product.product_image : '/placeholder.jpg'}" 
                             alt="${item.product.name}" class="w-12 h-12 object-cover rounded">
                        <div class="flex-1">
                            <h4 class="font-medium text-sm">${item.product.name}</h4>
                            <p class="text-blue-600 font-semibold">₦${(parseFloat(item.gain) + parseFloat(item.product.price)).toLocaleString()}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="updateQuantity(${item.id}, -1)" class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center text-xs">-</button>
                            <span class="text-sm font-medium">${item.quantity}</span>
                            <button onclick="updateQuantity(${item.id}, 1)" class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center text-xs">+</button>
                        </div>
                        <button onclick="removeFromCart(${item.id})" class="text-red-500 hover:text-red-700">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </div>
                `).join('');
                cartFooter.style.display = 'block';
                cartTotal.textContent = `₦${totalPrice.toLocaleString()}`;
            }
        }

        function toggleCart() {
            const sidebar = document.getElementById('cart-sidebar');
            const backdrop = document.getElementById('cart-backdrop');

            isCartOpen = !isCartOpen;

            if (isCartOpen) {
                sidebar.classList.add('open');
                backdrop.classList.add('show');
            } else {
                sidebar.classList.remove('open');
                backdrop.classList.remove('show');
            }
        }

        function viewProduct(retail) {
            currentRetail = retail; // Store for later use
            const modalTitle = document.getElementById('productModalLabel');
            const modalContent = document.getElementById('modal-content');

            modalTitle.textContent = retail.product.name;

            const finalPrice =
                (parseFloat(retail.gain) + parseFloat(retail.product.price));

            modalContent.innerHTML = `
                <div class="row g-4">
                    <div class="col-md-6">
                        <img src="${retail.product.product_image ? retail.product.product_image : '/placeholder.jpg'}" 
                             alt="${retail.product.name}" class="img-fluid rounded">
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-4">${retail.product.description}</p>
                        
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-medium">Price:</span>
                                <span class="fs-4 fw-bold text-primary">₦${finalPrice.toLocaleString()}</span>
                            </div>
                            ${retail.product.brand_name ? `
                                                                                                                                                                                                                                                                                                        <div class="d-flex justify-content-between mb-3">
                                                                                                                                                                                                                                                                                                            <span class="fw-medium">Brand:</span>
                                                                                                                                                                                                                                                                                                            <span>${retail.product.brand_name}</span>
                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                    ` : ''}
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-medium">Product Code:</span>
                                <span>${retail.product.product_code || 'N/A'}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-medium">Availability:</span>
                                <span class="badge ${retail.product.in_stock ? 'bg-success' : 'bg-danger'}">
                                    ${retail.product.in_stock ? 'In Stock' : 'Out of Stock'}
                                </span>
                            </div>
                        </div>
                        
                        ${retail.product.in_stock ? `
                                                                                                                                                                                                                                                                                                    <button onclick="addToCartFromModal()" class="btn btn-primary btn-lg w-100">
                                                                                                                                                                                                                                                                                                        <i class="fas fa-cart-plus me-2"></i> Add to Cart
                                                                                                                                                                                                                                                                                                    </button>
                                                                                                                                                                                                                                                                                                ` : `
                                                                                                                                                                                                                                                                                                    <button disabled class="btn btn-secondary btn-lg w-100">
                                                                                                                                                                                                                                                                                                        <i class="fas fa-ban me-2"></i> Out of Stock
                                                                                                                                                                                                                                                                                                    </button>
                                                                                                                                                                                                                                                                                                `}
                    </div>
                </div>
            `;
        }

        function addToCartFromModal() {
            if (currentRetail) {
                addToCart(currentRetail);
                // Close the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('productModal'));
                modal.hide();
            }
        }



        function checkout() {
            if (cart.length === 0) {
                showNotification('Your cart is empty!', 'error');
                return;
            }

            // Populate checkout modal with cart items
            populateCheckoutModal();

            // Show checkout modal
            const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
            checkoutModal.show();
        }

        function populateCheckoutModal() {
            const checkoutItemsList = document.getElementById('checkout-items-list');
            const checkoutTotal = document.getElementById('checkout-total');
            const dispatchTotal = document.getElementById('dispatch-total');



            const totalPrice = cart.reduce((sum, item) => sum + ((parseFloat(item.gain) + parseFloat(item.product.price)) *
                item.quantity), 0) + dispatchFee;


            // Populate items
            checkoutItemsList.innerHTML = cart.map(item => `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <span class="fw-medium">${item.product.name}</span>
                        <small class="text-muted d-block">Qty: ${item.quantity}</small>
                    </div>
                    <span>₦${((parseFloat(item.gain) + parseFloat(item.product.price)) * item.quantity).toLocaleString()}</span>
                </div>
            `).join('');

            dispatchTotal.textContent = `₦${dispatchFee.toLocaleString()}`;

            checkoutTotal.textContent = `₦${totalPrice.toLocaleString()}`;
        }

        async function processOrder() {
            const form = document.getElementById('checkoutForm');

            // Get form data
            const customerName = document.getElementById('customerName').value.trim();
            const customerEmail = document.getElementById('customerEmail').value.trim();
            const customerPhone = document.getElementById('customerPhone').value.trim();
            const customerCity = document.getElementById('customerCity').value.trim();
            const customerAddress = document.getElementById('customerAddress').value.trim();
            const orderNotes = document.getElementById('orderNotes').value.trim();

            // Reset previous validation states
            form.classList.remove('was-validated');

            // Validate required fields
            let isValid = true;

            if (!customerName) {
                document.getElementById('customerName').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('customerName').classList.remove('is-invalid');
            }

            if (!customerEmail || !isValidEmail(customerEmail)) {
                document.getElementById('customerEmail').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('customerEmail').classList.remove('is-invalid');
            }

            if (!customerPhone) {
                document.getElementById('customerPhone').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('customerPhone').classList.remove('is-invalid');
            }

            if (!customerAddress) {
                document.getElementById('customerAddress').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('customerAddress').classList.remove('is-invalid');
            }

            if (!isValid) {
                form.classList.add('was-validated');
                showNotification('Please fill in all required fields correctly.', 'error');
                return;
            }

            try {
                // Calculate total
                const total = cart.reduce((sum, item) => {
                    return sum + ((parseFloat(item.gain) + parseFloat(item.product.price)) * item.quantity);
                }, 0) + dispatchFee;

                // Prepare customer payload
                const customerPayload = {
                    user_id: user_id.textContent,
                    name: customerName,
                    email: customerEmail,
                    phone_no: customerPhone,
                    city: customerCity,
                    address: customerAddress,
                    note: orderNotes
                };

                // Save customer
                const customerResponse = await fetch('/api/customers', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                        'accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify(customerPayload)
                });

                const customerData = await customerResponse.json();

                if (!customerResponse.ok) {
                    throw new Error(customerData.message || 'Failed to create customer');
                }

                const customerId = customerData.customer?.id || customerData.id;

                // Prepare order payload
                const orderData = {
                    user_id: user_id.textContent,
                    quantity: cart.map(item => item.quantity),
                    amount: total,
                    address: customerAddress,
                    product_id: cart.map(item => item.product.id),
                    retail_id: cart.map(item => item.id),
                    type: 'customer_purchase',
                    payment_method: 'paystack',
                    reference: 'hshkjdkjd',
                    customer_id: customerId
                };

                // Send order
                const orderResponse = await fetch('/api/orders', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                        'accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify(orderData)
                });

                const orderResult = await orderResponse.json();

                if (!orderResponse.ok) {
                    throw new Error(orderResult.message || 'Order failed');
                }

                // Success
                showNotification(orderResult.message || 'Order placed successfully', 'success');

                // Clear cart
                cart = [];
                updateCartUI();

                // Close modal
                const checkoutModal = bootstrap.Modal.getInstance(document.getElementById('checkoutModal'));
                if (checkoutModal) checkoutModal.hide();

                if (isCartOpen) toggleCart();

                // Reset form
                form.reset();
                form.classList.remove('was-validated');

            } catch (error) {
                console.error('Order error:', error);
                showNotification(error.message || 'An error occurred.', 'error');
            }
        }


        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className =
                `position-fixed top-0 end-0 m-3 alert ${type === 'success' ? 'alert-success' : 'alert-danger'} alert-dismissible fade show`;
            notification.style.zIndex = '9999';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 4000);
        }
    </script>
</body>

</html>
