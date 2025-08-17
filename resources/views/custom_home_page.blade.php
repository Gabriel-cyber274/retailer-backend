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

    <script src="https://js.paystack.co/v1/inline.js"></script>

    <style>
        .tag-image-container {
            width: 100%;
            height: 100px;
            /* border-radius: 50%; */
            overflow: hidden;
            border: 1px solid #dee2e6;
        }

        .tag-image-container img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover;
        }

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

        /* Tag selection styling */
        #tag-selection .form-check {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 8px 12px;
            margin-right: 8px;
            margin-bottom: 8px;
            border: 1px solid #dee2e6;
            width: 200px
        }

        #tag-selection .form-check-input {
            margin-top: 0.3em;
            margin-right: 0.5em;
        }

        #tag-selection .form-check-label {
            cursor: pointer;
            width: 100%;
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
                    {{-- <span class="text-sm text-gray-500">by {{ $user->name }}</span> --}}
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
                            data-product-description="{{ strtolower($retail->product->description) }}"
                            data-retail-id="{{ $retail->id }}">
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
                                    <button onclick="viewProduct({{ $retail->id }})" data-bs-toggle="modal"
                                        data-bs-target="#productModal"
                                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 px-3 rounded-lg transition-colors text-sm">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </button>
                                    @if ($retail->product->in_stock)
                                        <button onclick="addToCartById({{ $retail->id }})"
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
                                <label for="customerState" class="form-label">State <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="customerState" required
                                    onchange="updateDispatchFee()">
                                    <option value="">Select State</option>
                                    @foreach ($states as $state)
                                        <option value="{{ $state->id }}"
                                            data-percentage="{{ $state->dispatch_percentage }}">{{ $state->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    Please select your state.
                                </div>
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
        let searchTimeout = null;
        let currentSearchTerm = '';
        let isSearching = false;

        const paystackSecretKey = "{{ config('app.paystack_secret_key') }}";
        const paystackPublicKey = "{{ config('app.paystack_public_key') }}";
        const user_id = document.getElementById('user_id');

        // Store original products for filtering
        const originalProducts = {!! json_encode(
            $user->retails->map(function ($retail) {
                    $finalPrice = $retail->product->price + $retail->gain;
                    return [
                        'id' => $retail->id,
                        'name' => strtolower($retail->product->name),
                        'price' => $finalPrice,
                        'brand' => strtolower($retail->product->brand_name ?? ''),
                        'description' => strtolower($retail->product->description),
                        'product_code' => $retail->product->product_code ?? '',
                        'in_stock' => $retail->product->in_stock,
                        'product_image' => $retail->product->product_image ?? '',
                        'product' => [
                            'id' => $retail->product->id,
                            'name' => $retail->product->name,
                            'description' => $retail->product->description,
                            'price' => $retail->product->price,
                            'product_image' => $retail->product->product_image,
                            'brand_name' => $retail->product->brand_name,
                            'product_code' => $retail->product->product_code,
                            'in_stock' => $retail->product->in_stock,
                        ],
                        'gain' => $retail->gain,
                        'element' => null,
                        'categories' => $retail->product->categories
                            ? $retail->product->categories->pluck('name')->map(function ($category) {
                                    return strtolower($category);
                                })->toArray()
                            : [],
                        'tags' => $retail->product->tags
                            ? $retail->product->tags->map(function ($tag) {
                                    return [
                                        'id' => $tag->id,
                                        'name' => $tag->name,
                                        'description' => $tag->description,
                                        'tag_image' => $tag->tag_image,
                                        'tag_code' => $tag->tag_code,
                                    ];
                                })->toArray()
                            : [],
                    ];
                })->toArray(),
        ) !!};

        console.log(originalProducts, 'Products loaded');

        // After DOM is loaded, connect the elements to the products
        document.addEventListener('DOMContentLoaded', function() {
            const productCards = document.querySelectorAll('.product-card');
            productCards.forEach((card, index) => {
                if (originalProducts[index]) {
                    originalProducts[index].element = card;
                }
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
            currentSearchTerm = searchTerm.toLowerCase();
            const spinner = document.getElementById('search-spinner');

            if (searchTerm === '') {
                showAllProducts();
                applyPriceFilter();
                return;
            }

            isSearching = true;
            spinner.style.display = 'block';

            performClientSideSearch(currentSearchTerm);

            spinner.style.display = 'none';
            isSearching = false;
            updateResultsInfo();
        }

        function performClientSideSearch(searchStr) {
            let visibleCount = 0;

            originalProducts.forEach(product => {
                // Check if any of the product fields match the search term
                const isMatch = (
                    (product.name && product.name.includes(searchStr)) ||
                    (product.description && product.description.includes(searchStr)) ||
                    (product.brand && product.brand.includes(searchStr)) ||
                    (product.product_code && product.product_code.includes(searchStr)) ||
                    (product.categories && product.categories.some(category => category.includes(searchStr))) ||
                    (product.tags && product.tags.some(tag => tag.name.toLowerCase().includes(searchStr)))
                );

                if (product.element) {
                    if (isMatch) {
                        product.element.style.display = 'block';
                        visibleCount++;
                    } else {
                        product.element.style.display = 'none';
                    }
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
        }

        function showAllProducts() {
            originalProducts.forEach(product => {
                if (product.element) {
                    product.element.style.display = 'block';
                }
            });

            document.getElementById('no-results').style.display = 'none';
            document.getElementById('products-container').style.display = 'block';
        }

        function showNoResults() {
            document.getElementById('no-results').style.display = 'block';
            document.getElementById('products-container').style.display = 'none';
        }

        function applyPriceFilter() {
            const minPrice = parseFloat(document.getElementById('min-price').value) || 0;
            const maxPrice = parseFloat(document.getElementById('max-price').value) || Infinity;

            let visibleCount = 0;

            originalProducts.forEach(product => {
                if (!product.element) return;

                if (product.price >= minPrice && product.price <= maxPrice) {
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

        // Cart functionality
        function addToCart(retail) {
            const existingItem = cart.find(item => item.id === retail.id &&
                (!item.selectedTag || !retail.selectedTag || item.selectedTag?.id === retail.selectedTag?.id));

            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    ...retail,
                    quantity: 1,
                    finalPrice: parseFloat(retail.product.price) + parseFloat(retail.gain),
                    selectedTag: retail.selectedTag || null
                });
            }

            updateCartUI();
            showNotification('Product added to cart!', 'success');
        }

        function addToCartById(retailId) {
            const retail = originalProducts.find(p => p.id === retailId);
            if (retail) {
                addToCart(retail);
            }
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
            const totalPrice = cart.reduce((sum, item) => sum + ((parseFloat(item.gain) + parseFloat(item.product
                    .price)) *
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
                            ${item.selectedTag ? `<p class="text-xs text-gray-500">Tag: ${item.selectedTag.name}</p>` : ''}
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

        function viewProduct(retailId) {
            const retail = originalProducts.find(p => p.id === retailId);
            if (!retail) {
                console.error('Product not found');
                return;
            }

            currentRetail = retail;
            const modalTitle = document.getElementById('productModalLabel');
            const modalContent = document.getElementById('modal-content');

            modalTitle.textContent = retail.product.name;

            const finalPrice = (parseFloat(retail.gain) + parseFloat(retail.product.price));

            // Check if product has tags
            const hasTags = retail.tags && retail.tags.length > 0;

            let tagsHTML = '';
            if (hasTags) {
                tagsHTML = `
            <div class="mb-4">
    <label class="fw-medium mb-2">Select Tag:</label>
    <div class="d-flex flex-wrap gap-3" id="tag-selection">
        ${retail.tags.map(tag => `
                                                                                                                    <div class="form-check">
                                                                                                                        <input class="form-check-input" type="radio" name="productTag" 
                                                                                                                               id="tag-${tag.id}" value="${tag.id}">
                                                                                                                        <label class="form-check-label  align-items-center gap-2" for="tag-${tag.id}">
                                                                                                                            ${tag.tag_image ? `
                        <div class="tag-image-container">
                            <img src="${tag.tag_image}" alt="${tag.name}" 
                                 class=" border" 
                                 style="width: 28px; height: 28px; object-fit: cover;">
                        </div>
                    ` : ''}
                                                                                                                            <span>${tag.name}</span><br>
                                                                                                                            <small class="text-muted">${tag.description}</small>
                                                                                                                        </label>
                                                                                                                    </div>
                                                                                                                `).join('')}
    </div>
</div>
        `;
            }

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
                    ${tagsHTML}
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
                // Get selected tag if available
                let selectedTag = null;
                const tagRadio = document.querySelector('input[name="productTag"]:checked');
                if (tagRadio) {
                    const tagId = parseInt(tagRadio.value);
                    // Find the tag object from the current retail's tags
                    selectedTag = currentRetail.tags.find(tag => tag.id === tagId);
                }

                // Add tag information to the retail object
                const retailWithTag = {
                    ...currentRetail,
                    selectedTag: selectedTag
                };

                addToCart(retailWithTag);
                const modal = bootstrap.Modal.getInstance(document.getElementById('productModal'));
                modal.hide();
            }
        }

        function checkout() {
            if (cart.length === 0) {
                showNotification('Your cart is empty!', 'error');
                return;
            }

            populateCheckoutModal();
            const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
            checkoutModal.show();
        }

        function populateCheckoutModal() {
            const checkoutItemsList = document.getElementById('checkout-items-list');
            const checkoutTotal = document.getElementById('checkout-total');
            const dispatchTotal = document.getElementById('dispatch-total');

            // Calculate product total
            const productTotal = cart.reduce((sum, item) => {
                return sum + ((parseFloat(item.gain) + parseFloat(item.product.price)) * item.quantity);
            }, 0);

            // Initial dispatch fee (0 until state is selected)
            let dispatchFee = 0;
            dispatchTotal.textContent = `₦${dispatchFee.toLocaleString()}`;

            // Initial total (just product total)
            checkoutTotal.textContent = `₦${productTotal.toLocaleString()}`;

            // Populate items
            checkoutItemsList.innerHTML = cart.map(item => `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <span class="fw-medium">${item.product.name}</span>
                        ${item.selectedTag ? `<small class="text-muted d-block">Tag: ${item.selectedTag.name}</small>` : ''}
                        <small class="text-muted d-block">Qty: ${item.quantity}</small>
                    </div>
                    <span>₦${((parseFloat(item.gain) + parseFloat(item.product.price)) * item.quantity).toLocaleString()}</span>
                </div>
            `).join('');
        }

        function updateDispatchFee() {
            const stateSelect = document.getElementById('customerState');
            const selectedOption = stateSelect.options[stateSelect.selectedIndex];
            const dispatchPercentage = parseFloat(selectedOption.getAttribute('data-percentage')) || 0;

            // Calculate total product cost
            const productTotal = cart.reduce((sum, item) => {
                return sum + ((parseFloat(item.gain) + parseFloat(item.product.price)) * item.quantity);
            }, 0);

            // Calculate dispatch fee based on percentage
            const dispatchFee = (productTotal * dispatchPercentage) / 100;

            // Update UI
            document.getElementById('dispatch-total').textContent = `₦${dispatchFee.toLocaleString()}`;

            // Update total with new dispatch fee
            const total = productTotal + dispatchFee;
            document.getElementById('checkout-total').textContent = `₦${total.toLocaleString()}`;
        }

        function generateReference() {
            return 'PS_' + Date.now() + '_' + Math.floor(Math.random() * 1000000);
        }

        async function processOrder() {
            const form = document.getElementById('checkoutForm');

            // Get form data
            const customerName = document.getElementById('customerName').value.trim();
            const customerEmail = document.getElementById('customerEmail').value.trim();
            const customerPhone = document.getElementById('customerPhone').value.trim();
            const customerState = document.getElementById('customerState').value.trim();
            const customerAddress = document.getElementById('customerAddress').value.trim();

            // Reset previous validation states
            form.classList.remove('was-validated');
            let isValid = true;

            // Validate fields
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

            if (!customerState) {
                document.getElementById('customerState').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('customerState').classList.remove('is-invalid');
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
                // Get selected state and dispatch percentage
                const stateSelect = document.getElementById('customerState');
                const selectedOption = stateSelect.options[stateSelect.selectedIndex];
                const dispatchPercentage = parseFloat(selectedOption.getAttribute('data-percentage')) || 0;

                // Calculate totals
                const productTotal = cart.reduce((sum, item) => {
                    return sum + ((parseFloat(item.gain) + parseFloat(item.product.price)) * item.quantity);
                }, 0);

                const dispatchFee = (productTotal * dispatchPercentage) / 100;
                const total = productTotal + dispatchFee;

                // Save customer with state_id
                const customerPayload = {
                    user_id: user_id.textContent,
                    name: customerName,
                    email: customerEmail,
                    phone_no: customerPhone,
                    state_id: customerState,
                    address: customerAddress,
                };

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
                if (!customerResponse.ok) throw new Error(customerData.message || 'Failed to create customer');
                const customerId = customerData.customer?.id || customerData.id;

                // Paystack popup
                const paystackHandler = PaystackPop.setup({
                    key: paystackPublicKey,
                    email: customerEmail,
                    amount: Math.round(total * 100), // NGN to kobo
                    currency: 'NGN',
                    ref: generateReference(),
                    firstname: customerName.split(' ')[0],
                    lastname: customerName.split(' ')[1] || '',
                    metadata: {
                        custom_fields: [{
                                display_name: "Mobile Number",
                                variable_name: "mobile_number",
                                value: customerPhone
                            },
                            {
                                display_name: "Delivery Address",
                                variable_name: "delivery_address",
                                value: customerAddress
                            }
                        ]
                    },
                    callback: function(response) {
                        console.log("Payment complete!", response);
                        processPayment(response, customerId, form);
                    },
                    onClose: function() {
                        showNotification('Payment window was closed. Please try again.', 'warning');
                    }
                });

                paystackHandler.openIframe();

            } catch (error) {
                console.error('Checkout error:', error);
                showNotification(error.message || 'An error occurred during checkout.', 'error');
            }
        }

        async function processPayment(response, customerId, form) {
            try {
                // Get selected state
                const stateSelect = document.getElementById('customerState');
                const customerState = stateSelect.value;

                // Calculate totals
                const productTotal = cart.reduce((sum, item) => {
                    return sum + ((parseFloat(item.gain) + parseFloat(item.product.price)) * item.quantity);
                }, 0);

                const selectedOption = stateSelect.options[stateSelect.selectedIndex];
                const dispatchPercentage = parseFloat(selectedOption.getAttribute('data-percentage')) || 0;
                const dispatchFee = (productTotal * dispatchPercentage) / 100;
                const total = productTotal + dispatchFee;

                const selectedTags = cart
                    .filter(item => item.selectedTag)
                    .map(item => item);

                const note = selectedTags.map((item) => {
                    const productName = item.product.name;
                    const tagName = item.selectedTag.name;
                    const quantity = item.quantity;
                    return `${productName} - ${tagName} (${quantity})`;
                }).join(", ");


                const orderData = {
                    user_id: user_id.textContent,
                    quantity: cart.map(item => item.quantity),
                    amount: total,
                    address: document.getElementById('customerAddress').value.trim(),
                    product_id: cart.map(item => item.product.id),
                    retail_id: cart.map(item => item.id),
                    tag_id: cart.map(item => item.selectedTag ? item.selectedTag.id : null),
                    type: 'customer_purchase',
                    payment_method: 'paystack',
                    reference: response.reference,
                    customer_id: customerId,
                    state_id: customerState,
                    dispatch_fee: dispatchFee,
                    original_price: productTotal,
                    note: note
                };

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
                if (!orderResponse.ok) throw new Error(orderResult.message || 'Order failed');

                showNotification('Payment successful! Order placed successfully', 'success');
                cart = [];
                updateCartUI();

                const checkoutModal = bootstrap.Modal.getInstance(document.getElementById('checkoutModal'));
                if (checkoutModal) checkoutModal.hide();
                if (isCartOpen) toggleCart();

                form.reset();
                form.classList.remove('was-validated');

            } catch (error) {
                console.error('Order error:', error);
                showNotification(error.message || 'An error occurred while processing your order.', 'error');
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
